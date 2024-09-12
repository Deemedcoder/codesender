import serial
import time
import traceback
from pysnmp.hlapi import *
import json
from datetime import datetime
import requests

server_url = "http://regardiot.com/Api/add_device_api"  # Full server URL

def read_from_serial(ser, retries=3, delay=1):
    for attempt in range(retries):
        try:
            if ser.in_waiting > 0:
                data = ser.readline().decode('utf-8').strip()
                if data:
                    return data
        except serial.SerialException as e:
            print(f"Serial exception on attempt {attempt + 1}: {e}")
        except OSError as e:
            print(f"OS error on attempt {attempt + 1}: {e}")
        except Exception as e:
            print(f"Unexpected error on attempt {attempt + 1}: {e}")
            traceback.print_exc()
        time.sleep(delay)
    raise RuntimeError("Failed to read from serial port after retries")

def write_to_serial(ser, message):
    try:
        ser.write(message.encode('utf-8'))
        print(f"Sent to Arduino: {message}")
    except Exception as e:
        print(f"Failed to send message to Arduino: {e}")

def initialize_serial():
    try:
        ser = serial.Serial(
            port='/dev/serial0',
            baudrate=9600,
            timeout=1,  # Increased timeout
            rtscts=False
        )
        if ser.is_open:
            print("Serial port opened successfully.")
            ser.flushInput()
        return ser
    except serial.SerialException as e:
        print(f"Serial port opening error: {e}")
        return None

# SNMP functions (same as before)
def getvalues():
    try:
        errorIndication, errorStatus, errorIndex, varBinds = next(
            getCmd(SnmpEngine(),
                   CommunityData('public', mpModel=1),
                   UdpTransportTarget(('192.168.1.219', 161)),
                   ContextData(),
                   ObjectType(ObjectIdentity('.1.3.6.1.4.1.318.1.1.1.3.2.1.0')),
                   ObjectType(ObjectIdentity('.1.3.6.1.4.1.318.1.1.1.4.2.1.0')),
                   ObjectType(ObjectIdentity('.1.3.6.1.4.1.318.1.1.1.2.2.3.0')),
                   ObjectType(ObjectIdentity('.1.3.6.1.4.1.318.1.1.1.3.2.4.0')),
                   ObjectType(ObjectIdentity('.1.3.6.1.4.1.318.1.1.1.4.2.2.0')),
                   ObjectType(ObjectIdentity('.1.3.6.1.4.1.318.1.1.1.4.1.4.0'))
                   ))

        if errorIndication:
            print(errorIndication)
            return [None] * 6  # Return a default value for failure case

        elif errorStatus:
            print('%s at %s' % (errorStatus.prettyPrint(),
                                errorIndex and varBinds[int(errorIndex) - 1][0] or '?'))
            return [None] * 6  # Return a default value for failure case

        else:
            return [varBinds[i][1].prettyPrint() for i in range(6)]

    except Exception as e:
        print(f"Exception in getvalues: {e}")
        return [None] * 6

def getvaluesone():
    try:
        errorIndication, errorStatus, errorIndex, varBinds = next(
            getCmd(SnmpEngine(),
                   CommunityData('public', mpModel=1),
                   UdpTransportTarget(('192.168.1.222', 161)),
                   ContextData(),
                   ObjectType(ObjectIdentity('.1.3.6.1.4.1.318.1.1.26.6.3.1.6.1')),
                   ObjectType(ObjectIdentity('.1.3.6.1.4.1.318.1.1.12.1.16.0')),
                   ObjectType(ObjectIdentity('.1.3.6.1.4.1.318.1.1.12.1.17.0')),
                   ObjectType(ObjectIdentity('.1.3.6.1.4.1.318.1.1.12.1.18.0'))
                   ))

        if errorIndication:
            print(errorIndication)
            return [None] * 4  # Return a default value for failure case

        elif errorStatus:
            print('%s at %s' % (errorStatus.prettyPrint(),
                                errorIndex and varBinds[int(errorIndex) - 1][0] or '?'))
            return [None] * 4  # Return a default value for failure case

        else:
            return [varBinds[i][1].prettyPrint() for i in range(4)]

    except Exception as e:
        print(f"Exception in getvaluesone: {e}")
        return [None] * 4

def write_data_with_timestamp(filename, data_lines):
    with open(filename, 'a') as file:  # Open the file in append mode
        for line in data_lines:
            timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            file.write(f"{timestamp} - {line}\n")

def main():
    ser = initialize_serial()
    if ser is None:
        print("Failed to initialize serial port.")
        return

    while True:
        try:
            # Send "Hello" to Arduino every 5 seconds
            
            write_to_serial(ser, "Hello")
            
            # Read the response from Arduino
            line = read_from_serial(ser)
            if line:
                print(f"Received data: {line}")

            upsvalue = getvalues()
            pduvalue = getvaluesone()

            # Handle None values by replacing them with '0'
            UpsVal = '_'.join(str(v) if v is not None else '0' for v in upsvalue)
            PduVal = '_'.join(str(v) if v is not None else '0' for v in pduvalue)

            try:
                data = json.loads(line)
                print(f"Parsed JSON data: {data}")

                detail = data.get('detail', 'Unknown')
                combined_detail = f"{detail}||UPS:{UpsVal}||IPDU:{PduVal}"
                json_data = {
                    "mac": data.get('mac', 'Unknown'),
                    "detail": combined_detail
                }
                data_lines = [combined_detail]
                write_data_with_timestamp('output.txt', data_lines)

                response = requests.post(server_url, json=json_data)

                if response.status_code == 200:
                    print("Data sent successfully!")
                    print(f"Server response: {response.text}")
                else:
                    print(f"Failed to send data. Status code: {response.status_code}")
                    print(f"Response: {response.text}")

            except json.JSONDecodeError:
                print("Received invalid JSON data")

            # Delay for 5 seconds before sending "Hello" again
            ser.flush()
            time.sleep(3)
            
        except RuntimeError as e:
            print(f"Runtime error: {e}")
            ser.close()
            time.sleep(5)  # Delay before attempting to reopen
            ser = initialize_serial()
        except serial.SerialException as e:
            print(f"Serial exception: {e}")
            ser.close()
            time.sleep(5)  # Delay before attempting to reopen
            ser = initialize_serial()
        except UnicodeDecodeError as e:
            print(f"Decode error: {e}")

    if ser and ser.is_open:
        ser.close()
        print("Serial port closed.")

if __name__ == "__main__":
    main()

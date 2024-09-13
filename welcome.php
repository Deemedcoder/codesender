<?php
// Read the newest (last) line from the text file
$filename = 'data.txt';
$data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Ensure the file has data
if (!$data || count($data) === 0) {
    die('The data file does not contain any data.');
}

// Get the newest (last) line
$newestLine = end($data);

// Remove the timestamp part (assumed to be before the first " - ")
$splitData = explode(' - ', $newestLine, 2);
if (count($splitData) < 2) {
    die('Invalid data format.');
}
$dataString = $splitData[1];

// Split the data string by "||" to get key-value pairs
$dataPairs = explode('||', $dataString);

// Prepare an associative array to store the parsed data
$data = [];
foreach ($dataPairs as $pair) {
    list($key, $value) = explode(':', $pair);
    $data[trim($key)] = trim($value);
}

// Handle the UPS and IPDU fields (they have multiple sub-values)
$upsData = explode('_', $data['UPS']);
$ipduData = explode('_', $data['IPDU']);

// Variables from parsed data
$temperature = $data['TEMP'];
$humidity = $data['HUM'];
$fireAlarm = $data['FIRESTATUS'];
$wld = $data['WLDSTATUS'];
$smoke = $data['SMOKESTATUS'];

// IN1-7 statuses
$inStatuses = [
    'IN1STATUS' => $data['IN1STATUS'],
    'IN2STATUS' => $data['IN2STATUS'],
    'IN3STATUS' => $data['IN3STATUS'],
    'IN4STATUS' => $data['IN4STATUS'],
    'IN5STATUS' => $data['IN5STATUS'],
    'IN6STATUS' => $data['IN6STATUS'],
    'IN7STATUS' => $data['IN7STATUS'],
];

// UPS values
$upsInputVoltage = $upsData[0];
$upsOutputVoltage = $upsData[1];
$upsRunTimeRemaining = $upsData[2];
$upsInputFrequency = $upsData[3];
$upsOutputFrequency = $upsData[4];
$upsInternalTemp = $upsData[5];

// IPDU values
$ipduValue1 = $ipduData[0];
$ipduValue2 = $ipduData[1];
$ipduValue3 = $ipduData[2];
$ipduValue4 = $ipduData[3];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="3">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Viewing Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }
        header {
            background-color: #6c757d;
            padding: 20px;
            text-align: center;
            color: white;
        }
        header img {
            width: 150px;
        }
        .container {
            margin-top: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }
        .card-body {
            padding: 20px;
        }
        .card h2 {
            font-size: 1.5rem;
        }
        .card p {
            font-size: 1.2rem;
            font-weight: bold;
        }
        .sidebar {
            background-color: #6c757d;
            min-height: 100vh;
            padding-top: 30px;
        }
        .sidebar a {
            color: white;
            display: block;
            padding: 15px;
            text-decoration: none;
            margin-bottom: 10px;
        }
        .sidebar a:hover {
            background-color: #343a40;
            border-radius: 10px;
        }
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<header>
    <img src="Logo-20240524044345.png" alt="Regard Network Solution Logo">
</header>

<div class="d-flex">
    <div class="sidebar">
        <a href="#">Dashboard</a>
        <a href="#">Temperature</a>
        <a href="#">Humidity</a>
        <a href="#">Fire Alarm</a>
        <a href="#">Water Leak</a>
        <a href="#">Smoke</a>
        <a href="#">UPS</a>
        <a href="#">IPDU</a>
    </div>

    <div class="container-fluid">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>Temperature</h2>
                        <p><?php echo htmlspecialchars($temperature); ?> °C</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>Humidity</h2>
                        <p><?php echo htmlspecialchars($humidity); ?> %</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>Fire Alarm</h2>
                        <p><?php echo htmlspecialchars($fireAlarm); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>Water Leak Detector</h2>
                        <p><?php echo htmlspecialchars($wld); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>Smoke Status</h2>
                        <p><?php echo htmlspecialchars($smoke); ?></p>
                    </div>
                </div>
            </div>

            <!-- Loop through IN statuses -->
            <?php foreach ($inStatuses as $label => $status): ?>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2><?php echo htmlspecialchars($label); ?></h2>
                        <p><?php echo htmlspecialchars($status); ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>UPS Input Voltage</h2>
                        <p><?php echo htmlspecialchars($upsInputVoltage); ?> V</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>UPS Output Voltage</h2>
                        <p><?php echo htmlspecialchars($upsOutputVoltage); ?> V</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>UPS Run Time Remaining</h2>
                        <p><?php echo htmlspecialchars($upsRunTimeRemaining); ?> ms</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>UPS Input Frequency</h2>
                        <p><?php echo htmlspecialchars($upsInputFrequency); ?> Hz</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>UPS Output Frequency</h2>
                        <p><?php echo htmlspecialchars($upsOutputFrequency); ?> Hz</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>UPS Internal Temperature</h2>
                        <p><?php echo htmlspecialchars($upsInternalTemp); ?> °C</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>IPDU Value 1</h2>
                        <p><?php echo htmlspecialchars($ipduValue1); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>IPDU Value 2</h2>
                        <p><?php echo htmlspecialchars($ipduValue2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>IPDU Value 3</h2>
                        <p><?php echo htmlspecialchars($ipduValue3); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h2>IPDU Value 4</h2>
                        <p><?php echo htmlspecialchars($ipduValue4); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

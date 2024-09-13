#!/bin/bash

# Update the package list
sudo apt update

# Install apache2
sudo apt install -y apache2

# Install pip (if not already installed)
sudo apt install -y python3-pip

# Install pyserial and pysnmp using pip
pip3 install pyserial pysnmp

# Output the versions installed for confirmation
echo "Installed versions:"
apache2 -v
pip3 show pyserial
pip3 show pysnmp

echo "Installation complete!"

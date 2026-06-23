#!/usr/bin/env bash
#ddev-generated

# Install DDEV certificate
mkcert -install

# Set up homeadditions if present
if [ -d /mnt/ddev_config/.homeadditions ]; then
    cp -r /mnt/ddev_config/.homeadditions/. $HOME/
fi

# Start KasmVNC server
sudo -u "$(whoami)" vncserver -fg -disableBasicAuth

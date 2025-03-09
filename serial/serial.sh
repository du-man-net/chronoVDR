#!/bin/bash
DISPLAY=:0
export DISPLAY
cd /var/www/html/chronoVDR/serial
/usr/bin/python3 /var/www/html/chronoVDR/serial/microbit.py &

#!/usr/bin/python
def calcCrc( msg ):
        "Vypocet checksumu"
        crc = 0;
        for letter in msg:     # First Example
           crc = crc ^ ord(letter)
        crc = format(crc, 'X')
        return crc;

def makeCommand( msg ):
        "Vytvorenie vety"
        final = b'$'+msg+'*'+calcCrc(msg)+'\n'
        return final;

import serial
import time
import sys
import glob

if len(sys.argv) == 1:
    print "give me a path to com"
    sys.exit()

port = sys.argv[1]

ports = glob.glob('/dev/tty[A-Za-z]*');
if port not in ports:
    print "No such port, or device not connected sorry"
    print "if device connected make sure www-data is in group dialout, or you place 666 on the path to usb device"
    sys.exit()

ser = serial.Serial(sys.argv[1], 115200)
ser.write(makeCommand("SGV"))
out = ser.readline()
print out

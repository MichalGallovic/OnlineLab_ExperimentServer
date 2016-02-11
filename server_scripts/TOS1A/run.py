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

ser = serial.Serial("/dev/ttyACM0", 115200)
ser.write(makeCommand('SSE'))
ser.write(makeCommand('SGV,50.00,50.00,50.00'))
#ser.write(makeCommand('SEE'))

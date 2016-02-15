#!/usr/bin/python
import serial
import time
import sys
import glob
import calendar
import string
import re
import os

def calcCrc( msg ):
    "Vypocet checksumu"
    crc = 0
    for letter in msg:
        crc = crc ^ ord(letter)
    crc = format(crc, 'X')
    return crc
	
def makeCommand( msg ):
        "Vytvorenie vety"
        final = b'$'+msg+'*'+calcCrc(msg)+'\n'
        return final

if len(sys.argv) < 4:
    print "3 arguments required: port filename time"
    sys.exit()

port = sys.argv[1]
ports = glob.glob('/dev/tty[A-Za-z]*');
if port not in ports:
    print "No such port, or device not connected sorry"
    print "if device connected make sure www-data is in group dialout, or you place 666 on the path to usb device"
    sys.exit()

# urcite by sa tu dala pridat este nejaka validacia :)

filename = "/home/vagrant/api/files/" + sys.argv[2]
now = calendar.timegm(time.gmtime())
end = now + int(float(sys.argv[3]))
readTimes = 0
ser = serial.Serial(port, 115200)

try:
    file = open(filename,"w+")
    file.close();
    while (now < end):
         file = open(filename, "a+")
         if(readTimes % 10 == 0):
             ser.close()
             ser = serial.Serial(port, 115200)
         ser.write(makeCommand("SGV"))
         out = ser.readline()
         out = out.replace("$","")
         out = re.sub(r"\*(\w|\r|\n)*","",out)
         out = out + "\n"
         file.write(out);
         file.close()
         now = calendar.timegm(time.gmtime())
         time.sleep(float(sys.argv[4])/1000.0);
         readTimes = readTimes + 1
    file.close()
    ser.close()
except:
    print "Could not create file"
    ser.close()
    sys.exit(0)

#!/usr/bin/python
import serial
import time
import sys
import glob
import calendar

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
ser = serial.Serial(port, 115200)


try:
    file = open(filename,"w+")
    file.close();
    while (now < end):
         file = open(filename, "a+")
	 ser.write(makeCommand("SGV"))
         out = ser.readline()
         file.write(out);
         file.close()
         now = calendar.timegm(time.gmtime())
         time.sleep(1)
    file.close()
except:
    print "Could not create file"
    sys.exit(0)

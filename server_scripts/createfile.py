#!/usr/bin/python
import serial
import time
import sys
import glob
import calendar

filename = "/home/vagrant/api/files/" + sys.argv[1]
print filename
try:
    file = open(filename,"w+")
    file.close()
    file = open(filename,'a+')
    for x in range(0 ,3):
        file.write("Test " + x);
    file.close()
except:
    print "Could not create file"
    sys.exit(0)

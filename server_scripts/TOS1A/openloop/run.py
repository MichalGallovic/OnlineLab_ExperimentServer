#!/usr/bin/python
import serial
import time
import argparse
import sys
import calendar

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

def getArguments():
    parser = argparse.ArgumentParser()
    parser.add_argument("--port")
    parser.add_argument("--input")
    parser.add_argument("--output")
    args = parser.parse_args()
    output = args.output
    port = args.port
    args = args.input
    args = args.split(",")
    args = [pair.replace(" ","") for pair in args]
    args_map = {}
    for arg in args:
       argument = arg.split(":")
       args_map[argument[0]] = argument[1]
    args_map["port"] = port
    args_map["output"] = output
    return args_map


def startReading(args):
    port = args["port"]
    ser = serial.Serial(port, 115200)
    readCmd = makeCommand('SGV')
    filePath = args["output"]
    now = calendar.timegm(time.gmtime())
    end = now + int(float(args["t_sim"]))
    readTimes = 0

    try:
        while (now < end):
            file = open(filePath, "a+")        
            ser.write(makeCommand("SGV"))
            out = ser.readline()
            file.write(out)
            # file.close()
            now = calendar.timegm(time.gmtime())
            # if(readTimes % 10 == 0):
            #     ser.close()

            time.sleep(float(args["s_rate"])/1000.0);
            file.close()
            # if(readTimes % 10 == 0):
            #     ser = serial.Serial(port, 115200)

            readTimes = readTimes + 1
        file.close()
        ser.close()
    except:
        print "Could not create file"
        ser.close()
        sys.exit(0)
    
def stopDevice(args):
    ser = serial.Serial(args["port"], 115200)
    ser.write(makeCommand('SEE'))
    ser.close()

def app(args):
    ser = serial.Serial(args["port"], 115200)
    ser.write(makeCommand('SSE'))
    command = "SGV," + args["c_lamp"] + "," + args["c_fan"] + "," + args["c_led"]
    ser.write(makeCommand(command))
    ser.close()
    startReading(args)
    stopDevice(args)
   
if __name__ == '__main__':
    args = getArguments()
    app(args)

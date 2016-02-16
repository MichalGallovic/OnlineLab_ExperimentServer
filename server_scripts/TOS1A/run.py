#!/usr/bin/python
import serial
import time
import argparse
import sys

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
    args = parser.parse_args()
    port = args.port
    args = args.input
    args = args.split(",")
    args = [pair.replace(" ","") for pair in args]
    args_map = {}
    for arg in args:
       argument = arg.split(":")
       args_map[argument[0]] = argument[1]
    args_map["port"] = port
    return args_map

def app(args):
    ser = serial.Serial(args["port"], 115200)
    ser.write(makeCommand('SSE'))
    command = "SGV," + args["c_lamp"] + "," + args["c_fan"] + "," + args["c_led"]
    ser.write(makeCommand(command))
   
if __name__ == '__main__':
    args = getArguments()
    app(args)

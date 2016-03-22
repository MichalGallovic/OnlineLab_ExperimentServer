#!/usr/bin/python
import time
import sys
import argparse
import string
import os
from mlabwrap import mlab

def app(args):
   currentDir = os.path.dirname(os.path.abspath(__file__))
   # Adding current working directory to matlab path
   # so it knows where to find other script
   # dependencies
   mlab.addpath(currentDir);
   mlab.load_system(currentDir + "/thermo");
   mlab.clear();
   P=args["P"]
   I=args["I"]
   D=args["D"]
   cfan=args["c_fan"]
   clamp=args["c_lamp"]
   cled=args["c_led"]
   ctrltyp=args["ctrltyp"]
   insw=args["in_sw"]
   outsw=args["out_sw"]
   tsim=args["t_sim"]
   ts=args["s_rate"]
   vstup=args["input"]
   scifun=args["scifun"]
   #mlab._set('output_path','/home/vagrant/api/files/matlab.txt');
   mlab._set('P', float(P));
   mlab._set('I', float(I));
   mlab._set('D', float(D));
   mlab._set('cfan', float(cfan));
   mlab._set('clamp', float(clamp));
   mlab._set('cled', float(cled));
   mlab._set('vstup', float(vstup));
   mlab._set('insw', float(insw)); #moznosti 1=lamp,2=led,3=fan
   if (int(outsw)==1 or int(outsw)==2):
      outsw=1; #1=ftemp
   elif (int(outsw)==3 or int(outsw)==4):
      outsw=2; #2=flight
   elif (int(outsw)==5 or int(outsw)==6):
      outsw=3; #3=frpm
   mlab._set('outsw', outsw);
   mlab._set('t_sim', float(tsim));
   mlab._set('fTt', 0.2); #filter time constant for temperature (0.05s - 10s)
   mlab._set('fTl', 0.2); #filter time constant for light intensity (0.05s - 10s)
   mlab._set('fTf', 0.2); #filter time constant for for angular velocity (0.1s - 10s)
   mlab._set('Umax', 100); #high input constraint
   mlab._set('Umin', 0); #low input constraint      
   hackport = args["port"] + "," +  args["output_path"]
   mlab._set('com',hackport); #port sustavy
   mlab._set('baud', 115200);
   mlab.run('/var/www/init.m');
   #mlab.delete(mlab.instrfind({'Port'},{com}));
   mlab._set('tempdps', 0); #zalozenie vystupnych premennych
   mlab._set('ftemp', 0);
   mlab._set('dtemp', 0);
   mlab._set('frpm', 0);
   mlab._set('drpm', 0);
   mlab._set('flight', 0);
   mlab._set('dlight', 0);
   mlab._set('t', 0);      
   if ctrltyp=="PID":
      mlab._set('ctrltyp', 2); #typ regulacie 2=PID
   elif ctrltyp=="OWN":
      mlab._set('ctrltyp', 3); #typ regulacie 3=own         
   elif ctrltyp=="NO":
      mlab._set('ctrltyp', 1); #typ regulacie 1=openloop	 
   mlab._set('Ts', float(ts)/1000) #perioda vzorkovania do 0.02      
   #mlab.set_param('thermo', 'SimulationCommand','start');
   mlab.sim('thermo');
   #mlab.sim('thermo','SimulationCommand','start');
   #mlab.sim('thermo','SimulationCommand','start');
   output=ctrltyp;
   return(str(output));

def getArguments():
   parser = argparse.ArgumentParser()
   parser.add_argument("--port")
   parser.add_argument("--output")
   parser.add_argument("--input")
   args = parser.parse_args()
   port = args.port
   outputPath = args.output
   args = args.input
   args = args.split(",")
   args = [pair.replace(" ","") for pair in args]
   args_map = {}
   for arg in args:
      argument = arg.split(":")
      args_map[argument[0]] = argument[1]
   args_map["port"] = port
   args_map["output_path"] = outputPath
   return args_map

if __name__ == '__main__':
   args = getArguments()
   app(args)

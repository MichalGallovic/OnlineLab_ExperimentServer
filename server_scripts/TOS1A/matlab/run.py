#!/usr/bin/python
import time
from mlabwrap import mlab

def app():
   mlab.load_system("/var/www/thermo");
   mlab.clear();
   P=0.8;
   I=2.95;
   D=0;
   cfan=40;
   clamp=30;
   cled=0;
   ctrltyp="NO";
   insw=1;
   outsw=3;
   tsim=10;
   ts=100;
   vstup=30;
   scifun="y1=u1";
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
   mlab._set('com','/dev/ttyACM0'); #port sustavy
   mlab._set('baud', 115200);
   mlab.run('/var/www/init.m');
   #mlab.delete(mlab.instrfind({'Port'},{com}));
   mlab.setenv('LD_LIBRARY_PATH', '/var/www');
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
  return "he"

if __name__ == '__main__':
   args = getArguments()
   app()

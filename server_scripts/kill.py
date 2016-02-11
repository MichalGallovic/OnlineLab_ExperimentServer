#!/usr/bin/python

import os
import sys
import signal

pid = sys.argv[1]
os.kill(int(float(pid)), signal.SIGTERM)

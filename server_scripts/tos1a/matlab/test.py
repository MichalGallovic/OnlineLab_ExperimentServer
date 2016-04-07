#!/usr/bin/python

import sys
import argparse
import string

parser = argparse.ArgumentParser()
parser.add_argument("--input")
args = parser.parse_args()
args = args.input
args = args.split(",")
args = [pair.replace(" ","") for pair in args]
args_map = {}
for arg in args:
    argument = arg.split(":")
    args_map[argument[0]] = argument[1]

print args_map["P"]

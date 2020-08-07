#!/bin/python
# LokiX Backend Loki updater
# Written by Hussien Yousef (alph4w0lf)

import sys
import subprocess
import os
import requests

# Global Variables
isFailed = False
error_msg = "success"

# Read token from STDIN
if len(sys.argv) < 2:
	print("Token is missing")
	exit()
print("[+] Token: "+sys.argv[1])

# Start Loki Update
print("[+] Starting loki updater.")
currentDir = os.path.dirname(os.path.realpath(__file__))
scriptDir = currentDir + "/loki"
os.chdir(scriptDir)

try:
	subprocess.Popen([scriptDir+"/loki-upgrader.py", "--nolog"]).wait()
except:
	print("[-] Issue while executing loki's updater script.")
	isFailed = True
	error_msg = "Update failed, please check that the server can reach the internet"

os.chdir(currentDir)

# GZ TAR the updated folder for agents to use
try:
	if not isFailed:
		print("[+] Compressing the updated loki package.")
		subprocess.Popen(["/bin/tar", "-czvf", "loki.tar.gz", "loki"]).wait()
except:
	print("[-] Failed to Compress loki package.")
	error_msg = "Failed to compress the updated loki package. Check file permissions."

# Tell LokiX platform the status of the update
print("[+] Posting update status to LokiX Platform.")
requests.post("https://127.0.0.1:8000/be/api/agent/update/status", json={'token': sys.argv[1], 'error': error_msg}, verify=False)

print("[+] Completed.")

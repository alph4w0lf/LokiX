<p align="center"><img src="https://github.com/alph4w0lf/LokiX/blob/master/lokix-banner.png" width="400"></p>

# LokiX Platform
LokiX Platform is a free open-source solution to help blue teams and threat hunters use "[Loki IOC Scanner](https://github.com/Neo23x0/Loki)" to sweep enterprise networks.

## Features
- Sweep thousands of endpoints concurrently.
- Silent execution.
- Agent deletes itself after completing its sweep.
- Centralized storage for Loki scan results.
- Centralized dashboard to track scans and view scan results.
- Loki update through the platform.
- Auto-highlight of key elements of the scan results.

## Supported Systems
#### Currently Supported
- Windows Vista (Need to install .NET Framework version 4.5)(Not Tested)
- Windows 7 (Successfully Tested)
- Windows 8 (Successfully Tested)
- Windows 10 (Successfully Tested)
- Windows Server 2008 (Successfully Tested)
- Windows Server 2012 (Successfully Tested)
- Windows Server 2016 (Not Tested)
- Windows Server 2019 (Not Tested)
#### Planned Support (Not available yet)
- Linux Operating Systems
- MacOS

## Installation Steps
#### STEP1: Import LokiX OVA
ToDo
#### STEP2: Configure a Static IP Address (Optional)
ToDo
#### STEP3: Firewall Rules (Optional)
ToDo
#### STEP4: Antivirus White-listing (Optional)
ToDo



## Usage Guide
#### Platform Admin Panel Access
- Access the platform through the Web Browser:
https://PLATFORM_IP_ADDRESS/
- Default Credentials:
admin@lokix.local
password
#### Updating Loki and its Signatures
ToDo
#### Download and Execute the Agent Sweeped Systems
ToDo


## Demo
ToDo

## Inner Workings
### LokiX Back-end
- PHP Laravel Framework (Web API)
- Python (Agent Upgrade Script)
- MySQL DBMS
### LokiX Front-end
- VueJs Framework (Template: @CreativeTim)
### LokiX Agent
- C# (.NET Framework 4.5)
- Loki IOC Scanner (@blahblah)




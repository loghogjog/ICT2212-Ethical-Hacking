# ICT2212-Ethical-Hacking
# To ssh into the Server
ssh -i “C:\<Directory-to-key>\ICT2212-AY26-T1-student8.pem” student8@18.117.115.161 
# Setting up on VS
1. Install Remote - SSH
2. Ctrl + Shift + p to open command palette
3. Select "Remote-SSH: Open SSH Configuration File" & select the first line
4. Copy paste this command below
Host ict2212
    HostName 18.117.115.161
    User student8
    IdentityFile "C:\Path to directory\ICT2212-AY26-T1-student8.pem"
5. Open command palette again and select "Remote-SSH: Connect to host" then "ict2212"
6. When the new window opens, select linux
7. Go to File > Open Folder and type in "/home/webuser/helpdesk" and hit Enter

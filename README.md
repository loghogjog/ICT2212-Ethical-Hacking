# ICT2212-Ethical-Hacking

## Local admin setup

Set `ADMIN_USERNAME` and `ADMIN_PASSWORD` in the environment before starting Docker Compose. On startup, the values create the administrator account if it does not already exist:

```powershell
$env:ADMIN_USERNAME = "helpdesk-admin"
$env:ADMIN_PASSWORD = "use-a-local-secret"
docker compose up -d --build
```

Public customers submit requests through `submit.php`; staff use `admin_login.php` to access the secured admin console.

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

# Setting up git 
(In the laptop terminal)
1. Ensure you have git by running git --version, if not install from git-scm.com (yall shld prob have it tho)
2. git config --global user.name "Your Name"
3. git config --global user.email "your@email.com"
4. Access token: In your web browser, on github.com: Settings → Developer settings → Personal access tokens → Tokens (classic) → Generate new token (classic) → tick the repo checkbox → Generate → copy the token and paste it somewhere temporary (Notes app)
5. cd ~/Desktop (or whichever folder you want to keep your project)
6. git clone https://github.com/loghogjog/ICT2212-Ethical-Hacking.git
7. cd ICT2212-Ethical-Hacking
8. When it asks: Username = your GitHub username, Password = paste the token from Step 4 (not your GitHub login password).
9. git config --global credential.helper store
10. git pull                        # FIRST: get everyone's latest

# --- edit your files in VS Code, save them ---
1. git add .           # stage all the files you changed at one go
2. git commit -m "summary of what you did"    # save locally
3. git pull                        # again, in case someone pushed while you worked. **Note you might need to resolve merge conflicts**
4. git push                        # send yours to GitHub

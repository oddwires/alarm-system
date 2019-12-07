#!/bin/bash
CurrentDir="$(pwd)"                                        # remember where we are
Parent=$(dirname $PWD)                                     # directory name up one level
GrandParent=$(dirname $Parent)                             # directory name up two levels
CurrentUsr=${GrandParent///home\/}                         # remember who we are

clear
tput setaf 2                                               # Green text
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 1                              *"
echo "*                                                                              *"
echo "*  Install Debian upgrades.                                                    *"
echo "*  ( this can take a long time to run )                                        *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
  tput setaf 9                                               # Reset to default text colour
  # Upgrades...
  sudo apt-get -y upgrade
  sudo apt-get install build-essential
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
tput setaf 2                                               # Green text
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 2                              *"
echo "*                                                                              *"
echo "*  Install Postfix Mail Transfer Agent.                                        *"
echo "*                                                                              *"
echo "*  This allows the alarm system to send email alerts for any alarm events.     *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
  tput setaf 9                                               # Reset to default text colour
  # Note: Haven't set the 'send as' account or password - that is handled by the alarm service
  # Mail Transfer Agent...
  sudo DEBIAN_FRONTEND=noninteractive apt-get install -y postfix mailutils  # mute the install dialogue
  # configure to use GMAIL relay server...
  sudo cp ./ConfigFiles/main.cf /etc/postfix/main.cf
  # customise the email to include the current hostname...
  sudo sed -i '/myhostname = */c\'"myhostname = $HOSTNAME" /etc/postfix/main.cf
  # install mail certificates...
  cat /etc/ssl/certs/thawte_Primary_Root_CA.pem | sudo tee -a /etc/postfix/cacert.pem
  sudo service postfix restart
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
tput setaf 2                                               # Green text
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 3                              *"
echo "*                                                                              *"
echo "*  Install I2C Tools.                                                          *"
echo "*                                                                              *"
echo "*  This allows the alarm system to use the I2C bus to communicate with the     *"
echo "*  custom circuit board. Commands are sent through this interface when using   *"
echo "*  the radio control power outlets.                                            *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
   tput setaf 9                                               # Reset to default text colour
   # Configure the I2C bus speed to 32K ( default is 100K and too fast for the PIC chip )
   sudo fdtput --type u /boot/bcm2709-rpi-2-b.dtb /soc/i2c@7e205000 clock-frequency 32000
   # install the I2C utilities...
   sudo apt-get install -y i2c-tools
   # set start up parameters ( requires reboot to take effect ) ...
  echo "dtparam=i2c_arm=on" >> /boot/config.txt
  # Device tree patch - this patch will most likely be included in the next Wheezy release, so will need to be removed at some point.
  # Details can be found here...  http://www.raspberrypi.org/forums/viewtopic.php?p=675658#p675658
  sudo cp alarm-system/i2c-baudrate-overlay.dtb /boot/overlays/
  # This line will also probably need updating...
  echo "dtoverlay=i2c-baudrate,i2c1_baudrate=32000" >> /boot/config.txt
  # End of Device tree patch.
  fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
tput setaf 2                                               # Green text
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 4                              *"
echo "*                                                                              *"
echo "*  Install Apache and PHP.                                                     *"
echo "*                                                                              *"
echo "*  This installs a web server to host the alarm system web app.                *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
   tput setaf 9                                               # Reset to default text colour
   # Apache install...
   sudo apt-get install -y apache2 php

   # edit the Apache2 default http web page...
   filename='/etc/apache2/sites-available/000-default.conf'                   # File to be edited
   oldstring='DocumentRoot /var/www/html'                                     # need to replace this string...
   newstring='DocumentRoot /var/www'                                          # ... with this one
   sudo sed -i -e "s@$oldstring@$newstring@g" "$filename"                     # do it.
   sudo service apache2 restart
fi
#read -n1 -r -p "Press any key to continue..." key

clear
tput setaf 2                                               # Green text
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 5                              *"
echo "*                                                                              *"
echo "*  Install alarm web page.                                                     *"
echo "*                                                                              *"
echo "*  This is the collection of web pages, PHP scripts and data files that        *"
echo "*  provide the alarm system web app interface.                                 *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
    tput setaf 9                                               # Reset to default text colour
    # Check for previous install...
    if [ "$(ls -A /var/www)" ]; then
      echo "Previous install found."
      echo "Removing previous install."
      sudo service alarm stop
      sudo rm -Rf /var/www/*
      echo "Previous install removed."
    fi
    # Check for default Apache web page....
    if [ -f /var/www/index.html ]; then
        echo "Removing default Apache web page."
        rm -f /var/www/index.html;
    fi

    echo "Creating web site..."
    sudo cp -vR ./WebPage/* /var/www/                           # Copy code to web page
    mkdir /var/www/logs
    mkdir /var/www/data
    mkdir /var/www/Scripts
    # set file and directory, owner and group ...
    sudo chown -R root /var/www/
    sudo chgrp -R www-data /var/www/
    # set folder permissions...
    chmod -R 770 /var/www/logs
    chmod -R 770 /var/www/data
    chmod -R 750 /var/www/themes
    chmod -R 750 /var/www/Scripts
    echo "Setting file permissions..."
    sudo find /var/www -type f -exec chmod 640 {} \;                        # change file permissions only
    sudo find /var/www/Scripts -type f -exec chmod 740 {} \;                # root can execute all files in this folder
    echo "Grant user pi access to the web folder..."
    usermod -aG www-data $CurrentUsr

    echo "Installing the alarm service..."
    # copy the scripts...
    sudo cp -vR ./Scripts/* /var/www/Scripts
    # create the new daemon...
    sudo cp ./Scripts/alarm /etc/init.d/
    chgrp root /etc/init.d/alarm
    # make alarm, daemon and helper scripts executable...
    sudo chmod +x /var/www/Scripts/*
    sudo chmod 740 /etc/init.d/alarm
    # make daemon autostart...
    sudo update-rc.d alarm defaults
    # enable the service...
    sudo systemctl enable alarm
    # Start it up...
    sudo service alarm start

    echo "Creating data directory structure..."
    mkdir /var/data
    mkdir /var/data/app-sensor
    mkdir /var/data/logs
    mkdir /var/data/app-data
    # set file and directory, owner and group ...
    sudo chgrp -R www-data /var/data/              # whole of the directory structure...
    sudo chgrp root /var/data/                     # except for this bit, www-data doesn't need access up here.
    # set folder permissions...
    chmod -R 770 /var/data/app-sensor
    chmod -R 770 /var/data/logs
    chmod -R 770 /var/data/app-data
   fi
#read -n1 -r -p "Press any key to continue..." key

clear
tput setaf 2                                               # Green text
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 6                              *"
echo "*                                                                              *"
echo "*  Create a certificate chain and configure Apache to use HTTPS.               *"
echo "*                                                                              *"
echo "*  This protects your system from man-in-the-middle attacks, and encrypts      *"
echo "*  all data transferred to and from the web app ensuring safe operation        *"
echo "*  over non-secured networks.                                                  *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
    IPaddress=$(hostname -I)
    HostName=$(hostname)
    echo "If you intend to open port 443 on your router to allow external access from the internet, you have probably also created a DDNS name for this installation."
    echo "For example 'myinstallation.no-ip.com'"
    echo
    echo "If you have, enter it now, so it can be added to the certificate."
    echo "Alternatively just press return to continue."
    read DDNSname

    tput setaf 9                                               # Reset to default text colour
    # Check for existing keychain...
    if [ "$(ls -A /var/ca)" ]; then
       echo "Existing keychain found."
       sudo rm -Rf /var/ca/
       echo "Existing keychain deleted."
       echo
    fi
    set -e
    sudo mkdir /var/ca
    cd /var/ca

    # Certificate chain creation based on code found at...
    # https://stackoverflow.com/questions/26759550/how-to-create-own-self-signed-root-certificate-and-intermediate-ca-to-be-importe
    # Note: We aren't using an intermediate certificate in the chain, just the root and the server.

    for C in `echo root-ca intermediate`; do
      mkdir $C
      cd $C
      mkdir certs crl newcerts private
      cd ..

      echo 1000 > $C/serial
      touch $C/index.txt $C/index.txt.attr

      echo '
    [ ca ]
    default_ca = CA_default
    [ CA_default ]
    dir            = '$C'                           # Where everything is kept
    certs          = $dir/certs                     # Where the issued certs are kept
    crl_dir        = $dir/crl                       # Where the issued crl are kept
    database       = $dir/index.txt                 # database index file.
    new_certs_dir  = $dir/newcerts                  # default place for new certs.
    certificate    = $dir/cacert.pem                # The CA certificate
    serial         = $dir/serial                    # The current serial number
    crl            = $dir/crl.pem                   # The current CRL
    private_key    = $dir/private/ca.key.pem        # The private key
    RANDFILE       = $dir/.rnd                      # private random number file
    nameopt        = default_ca
    certopt        = default_ca
    policy         = policy_match
    default_days   = 365
    default_md     = sha256

    [ policy_match ]
    countryName            = optional
    stateOrProvinceName    = optional
    organizationName       = optional
    organizationalUnitName = optional
    commonName             = supplied
    emailAddress           = optional

    [req]
    req_extensions = v3_req
    distinguished_name = req_distinguished_name

    [req_distinguished_name]

    [v3_req]
    basicConstraints = CA:TRUE
    subjectAltName = @alt_names
    [alt_names]
    IP.1 = '$IPaddress'
    DNS.1 = '$HostName'
    ' > $C/openssl.conf

    if [[ $DDNSname != '' ]]; then
       # if we have a DDNS name, then add it to the Certificate Subject Alternative Name
        echo "DNS.2 = "    $DDNSname >> $C/openssl.conf
        echo "DNS.3 = "    www.$DDNSname >> $C/openssl.conf
    fi
    done

    # Create V3.ext file...
    echo '
    authorityKeyIdentifier=keyid,issuer
    basicConstraints=CA:FALSE
    keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment
    subjectAltName = @alt_names

    [alt_names]
    IP.1 = '$IPaddress'
    DNS.1 = '$HostName'
    ' > /var/ca/v3.ext
    if [[ $DDNSname != '' ]]; then
       # if we have a DDNS name, then add it to the Certificate Subject Alternative Name
        echo "DNS.2 = "    $DDNSname >> /var/ca/v3.ext
        echo "DNS.3 = "    www.$DDNSname >> /var/ca/v3.ext
    fi

    sudo a2enmod ssl
    tput setaf 2                                               # Green text
    echo
    echo "############################################################################"
    echo "Creating root CA..."
    echo "############################################################################"
    echo
    tput setaf 9                                               # Reset to default text colour
    openssl genrsa -out root-ca/private/ca.key 2048
    openssl req -config root-ca/openssl.conf -new -x509 -days 3650 -key root-ca/private/ca.key -sha256 -extensions v3_req -out root-ca/certs/ca.crt -subj "/CN="$HOSTNAME" /O=oddwires.co.uk"

    tput setaf 2                                               # Green text
    echo
    echo "############################################################################"
    echo "Creating server certificate..."
    echo "############################################################################"
    echo
    tput setaf 9                                               # Reset to default text colour
    mkdir server

    openssl req -new -sha256 -nodes -out server/$HOSTNAME.request -newkey rsa:2048 -keyout server/Certificate.key -subj "/CN="$HOSTNAME" /O=oddwires.co.uk"
    openssl x509 -req -in server/$HOSTNAME.request -CA root-ca/certs/ca.crt -CAkey root-ca/private/ca.key -sha256 -CAcreateserial -out server/Certificate.crt -days 3650 -extfile v3.ext

#   openssl x509 -text -in /var/ca/server/Certificate.crt -noout              # DEBUG - print the certificate details

    sudo cp /var/ca/root-ca/certs/ca.crt /var/www/logs/$HOSTNAME.crt          # copy the certificate to location that can be
                                                                              # accessed over the network
    cd $CurrentDir                                                            # back to where we started

    # create the virtual site...
    sudo cp ./ConfigFiles/default-ssl.conf /etc/apache2/sites-available/
    sudo a2dissite 000-default.conf                                            # disable port 80 site
    sudo a2ensite default-ssl.conf                                             # enable web site
    sudo service apache2 restart                                               # load new certificate chain.
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
tput setaf 2                                               # Green text
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 7                              *"
echo "*                                                                              *"
echo "*  Install Samba.                                                              *"
echo "*                                                                              *"
echo "*  Samba provides  file services  that allow Windows devices to connect to     *"
echo "*  the alarm system over a LAN. This is useful for viewing and editing the     *"
echo "*  source code on a desktop or laptop.                                         *"
echo "*                                                                              *"
echo "*  Note: This installer will configure Samba for use with Windows 7 clients.   *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
  tput setaf 9                                               # Reset to default text colour
  # Samba install...
  sudo apt-get -y install samba
  # Samba common binaries...
  sudo apt-get -y install samba-common-bin

  # Configure Samaba for Windows 7 clients...
  filename='/etc/samba/smb.conf'
  sudo cp ./ConfigFiles/smb.conf $filename

  # Customise the config for this install...
  # Hostname...
  oldstring='parm1'                                                  # need to replace this string...
  newstring=$HOSTNAME                                                # ... with this one
  sudo sed -i -e "s@$oldstring@$newstring@g" "$filename"             # do it.

  # User name...
  oldstring='parm2'                                                  # need to replace this string...
  newstring="$CurrentUsr"                                            # ...with the current user
  sudo sed -i -e "s@$oldstring@$newstring@g" "$filename"             # do it

  # install directory...
  oldstring='parm3'                                                  # need to replace this string...
  newstring="$CurrentDir"                                            # path to install directory
  sudo sed -i -e "s@$oldstring@$newstring@g" "$filename"             # do it.

  # NODEjs directory...
  oldstring='parm4'                                                  # need to replace this string...
  parent=$(dirname $PWD)                                             # directory name up one level
  newstring="$parent"/HAP-NodeJS                                     # path to NODEjs repository
  sudo sed -i -e "s@$oldstring@$newstring@g" "$filename"             # do it.

  clear
  tput setaf 2                                               # Green text
  # add password for the current user user...
  echo " "
  echo "SAMBA needs to set a password for user "$CurrentUsr"."
  echo "This will enable access to the network shares from a Windows network."
  echo "Note: the file permissions will restrict this access to READ ONLY."
  echo "(tip - use the same password as your user ID)"
  echo ""
  tput setaf 9                                               # Reset to default text colour
  sudo smbpasswd -a $CurrentUsr
  # enable the password for the current user...
  sudo smbpasswd -e $CurrentUsr
  clear

  # add password for root...
  tput setaf 2                                               # Green text
  echo " "
  echo "SAMBA needs to set a password for the root user."
  echo "This will enable access to the network shares from a Windows network."
  echo "Note: the file permissions will permit FULL ACCESS."
  echo "(tip - use the same password as the root user ID)"
  echo ""
  tput setaf 9                                               # Reset to default text colour
  sudo smbpasswd -a root
  # enable the password for the root user...
  sudo smbpasswd -e root
  # restart the service...
  sudo service smbd restart
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
tput setaf 2                                               # Green text
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 8                              *"
echo "*                                                                              *"
echo "*  Install and configure Fail2Ban                                              *"
echo "*                                                                              *"
echo "*  Fail2Ban monitors the Apache event logs for unauthorised access attempts.   *"
echo "*  If 3 failed logon attempts occur within 5 minutes, a firewall rule is       *"
echo "*  created to block the IP address of the intruder.                            *"
echo "*  The alarm system process this as an alarm event, and will send an email     *"
echo "*  alert with the IP details of the intruder.                                  *"
echo "*  Simultaneously, the web interface throws up a skull and crossbones          *"
echo "*  graphic on the intruder device before blocking it.                          *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
  tput setaf 9                                               # Reset to default text colour
  # Install Fail2Ban
  sudo apt-get -y install fail2ban
  
  # create the local jail file...
  sudo cp ./ConfigFiles/jail.local /etc/fail2ban/
  sudo chown root /etc/fail2ban/jail.local
  sudo chgrp root /etc/fail2ban/jail.local
  
  # create the regex filter...
  sudo cp ./ConfigFiles/alarm-regex.conf /etc/fail2ban/filter.d/
  sudo chown root /etc/fail2ban/filter.d/alarm-regex.conf
  sudo chgrp root /etc/fail2ban/filter.d/alarm-regex.conf

  # Define additional actions to integrate Fail2Ban with the alarm service filter...
  sudo cp ./ConfigFiles/alarm-actions.conf /etc/fail2ban/action.d/
  sudo chown root /etc/fail2ban/action.d/alarm-actions.conf
  sudo chgrp root /etc/fail2ban/action.d/alarm-actions.conf

  # create the shell script to integrate with the alarm service...
  sudo cp ./Scripts/fail2ban_alarm.sh /etc/fail2ban/action.d/
  sudo chown root /etc/fail2ban/action.d/fail2ban_alarm.sh
  sudo chgrp root /etc/fail2ban/action.d/fail2ban_alarm.sh
  sudo chmod +x /etc/fail2ban/action.d/fail2ban_alarm.sh
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
tput setaf 2                                               # Green text
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 9                              *"
echo "*                                                                              *"
echo "*  Install and configure HomeKit Bridge                                        *"
echo "*                                                                              *"
echo "* This uses HAP-NodeJS, a Node.JS implementation of Apples HomeKit Accessory   *"
echo "* Server. This allows voice control of the RC power switches using Siri.       *"
echo "*                                                                              *"
echo "* Full project details on the GitHub https://github.com/KhaosT/HAP-NodeJS      *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
  tput setaf 9                                               # Reset to default text colour
  # install various pre requisites...
  cd ..                                         # install into the Downloads directory
  sudo apt-get install libavahi-compat-libdnssd-dev -y
  sudo apt-get install -y npm
  
  # Ideally these should be installed locally. But this causes issues with the ts-node executable
  # not being found. Its probably just a path issue, but I installed Globally as a workaround.
  sudo npm install -g ts-node
  sudo npm install -g typescript

  # install the HAP-NodeJS server...
  git clone git://github.com/KhaosT/HAP-NodeJS.git
   
  # because we've sudo'd the git clone, the directory structure will belong to root. This causes issues.
  # the first time the service runs, it creates a 'persists' folder, but assigns the wrong permissions.
  # the following 5 lines return the sudo cloned repo to a usable state...
  mkdir HAP-NodeJS/persist          # Create the folder now rather than letting the service
                                    # do it at first run.
  sudo chown -R pi ./HAP-NodeJS     # Belt and braces solution - force permissions from root to pi
  sudo chgrp -R pi ./HAP-NodeJS
  sudo chmod g+s ./HAP-NodeJS       # new folders + files will inherit directory group details
  sudo chmod u+s ./HAP-NodeJS       # new folders + files will inherit directory user details

  cd HAP-NodeJS/
  npm install
  npm install --only=dev
  
  cd $CurrentDir                                                     # back to where we started
  # create the new daemon, and make it auto-start...
  sudo cp ./Scripts/homebridge /etc/init.d/
  sudo chgrp root /etc/init.d/homebridge
  sudo chown root /etc/init.d/homebridge
  sudo chmod +x /etc/init.d/homebridge
  sudo update-rc.d homebridge defaults

  cd ..                             # back to Downloads directory
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
tput setaf 2                                               # Green text
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 10                             *"
echo "*                                                                              *"
echo "*  The alarm system has been installed.                                        *"
echo "*                                                                              *"
echo "*  The I2C bus has been reconfigured to operate at 32KHz, but requires a       *"
echo "*  reboot to take effect.                                                      *"
echo "*                                                                              *"
echo "*  Press any key to exit the installer and reboot the system.                  *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
tput setaf 9                                               # Reset to default text colour
echo
sudo reboot
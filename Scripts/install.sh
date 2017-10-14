#!/bin/bash
clear
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
  # Upgrades...
  sudo apt-get -y upgrade
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
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
echo "*  Note:                                                                       *"
echo "*    During the installation, a Dialog will display.                           *"
echo "*       First screen:  Tab down to the 'ok' button, then press return.         *"
echo "*       Second screen: Select 'server as Internet Site', then press return.    *"
echo "*       Third screen:  Enter the Hostname of your Raspberry Pi.                 *"
echo "*       ( Hostname is the same value as you have set in raspi-config utiliy )  *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
  # Note: Haven't set the 'send as' account or password - that is handled by the alarm service
  # Mail Transfer Agent...
  sudo apt-get install -y postfix mailutils
  # configure to use GMAIL relay server...
  sudo cp ./alarm-system/ConfigFiles/main.cf /etc/postfix/main.cf
  # customise the email to include the current hostname...
  sudo sed -i '/myhostname = */c\'"myhostname = $HOSTNAME" /etc/postfix/main.cf
  # install mail certificates...
  cat /etc/ssl/certs/thawte_Primary_Root_CA.pem | sudo tee -a /etc/postfix/cacert.pem
  sudo service postfix restart
fi
read -n1 -r -p "Press any key to continue..." key
echo " "

clear
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
   # Apache install...
   sudo apt-get install -y apache2 php5 libapache2-mod-php5

   # edit the Apache2 default http web page...
   filename='/etc/apache2/sites-available/000-default.conf'                   # File to be edited
   oldstring='DocumentRoot /var/www/html'                                     # need to replace this string...
   newstring='DocumentRoot /var/www'                                          # ... with this one
   sudo sed -i -e "s@$oldstring@$newstring@g" "$filename"                     # do it.
   sudo service apache2 restart
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 5                              *"
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
  # Samba install...
  sudo apt-get -y install samba
  # Samba common binaries...
  sudo apt-get -y install samba-common-bin

  # need to find the name of the current user...
  curdir=$(pwd)                                                      # remember where we are

  # Configure Samaba for Windows 7 clients...
  filename='/etc/samba/smb.conf'
  sudo cp ./alarm-system/ConfigFiles/smb.conf $filename

  oldstring='parm1'                                                  # need to replace this string...
  newstring=$HOSTNAME                                                # ... with this one
  sudo sed -i -e "s@$oldstring@$newstring@g" "$filename"             # do it.
  cd ..                                                              # up a level
  oldstring='parm2'                                                  # need to replace this string...
  newstring="${PWD##*/}"                                             #--- with the current user
  cd ${curdir}                                                       # and back to original directory
  sudo sed -i -e "s@$oldstring@$newstring@g" "$filename"             # do it.

  clear
  # add password for the current user user...
  echo " "
  echo "SAMBA needs to set a password for user "$newstring"."
  echo "This will enable access to the network shares from a Windows network."
  echo "Note: the file permissions will restrict this access to READ ONLY."
  echo "(tip - use the same password as your user ID)"
  echo ""
  sudo smbpasswd -a $newstring
  # enable the password for the current user...
  sudo smbpasswd -e $newstring
  clear
  
  # add password for root...
  echo " "
  echo "SAMBA needs to set a password for the root user."
  echo "This will enable access to the network shares from a Windows network."
  echo "Note: the file permissions will permit FULL ACCESS."
  echo "(tip - use the same password as the root user ID)"
  echo ""
  sudo smbpasswd -a root
  # enable the password for the root user...
  sudo smbpasswd -e root
  # restart the service...
  sudo service samba restart
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 6                              *"
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
  # Check for previous install...
  if [ "$(ls -A /var/www)" ]; then
    echo "Previous install found."
    echo "Removing previous install."
    sudo service alarm stop
    sudo rm -Rf /var/www/*
    echo "Previous install removed."
  else
    echo "Previous install not found."
  fi
  # Check for default Apache web page....
  if [ -f /var/www/index.html ]; then
      echo "Removing default Apache web page."
      rm -f /var/www/index.html;
  fi

  echo "Copying web site files..."
  sudo cp -vR ./alarm-system/. /var/www/                           # Copy code to web page
  rm -f /var/www/Scripts/install.sh                                # tidy up...
  rm -f /var/www/README.md
  rm -Rf /var/www/.git
  rm -f /var/www/.gitattributes
  rm -f /var/www/.gitignore
  rm -f /var/www/smb.conf
  rm -f /var/www/i2c-baudrate-overlay.dtb
  echo "Creating sub folders..."                                   # Git won't create an empty folder, so we have to do it here
  mkdir /var/www/logs
  mkdir /var/www/data
  # set file and folders, owner and group ...
  sudo chown -R root /var/www/
  sudo chgrp -R www-data /var/www/
  # set folder permissions...
  chmod -R 750 /var/www/logs
  chmod -R 770 /var/www/data
  chmod -R 750 /var/www/themes
  chmod -R 740 /var/www/Scripts
  # set file permissions...
  chmod 640 `find /var/www -type f`                                # change file permissions only
  chmod 740 `find /var/www/Scripts -type f`                        # root can execute.
fi
# give pi user read only access to web files...
   echo "Grant user pi access to the web folder..."
   usermod -aG www-data pi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 7                              *"
echo "*                                                                              *"
echo "*  Install alarm daemon.                                                       *"
echo "*                                                                              *"
echo "*  This is the background process that interfaces between the web app          *"
echo "*  interface and the custom circuit board.                                     *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
      # create the new daemon...
      sudo cp /var/www/Scripts/alarm /etc/init.d/
      chgrp root /etc/init.d/alarm

      # make alarm executable...
      sudo chmod +x /var/www/Scripts/alarm.sh

      # make daemon autostart...
      sudo update-rc.d alarm defaults  
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 8                              *"
echo "*                                                                              *"
echo "*  Create Self Signed Certificate and configure Apache secure data transfers   *"
echo "*  using TLS encryption. This encrypts all data transferred to and from the    *"
echo "*  web app, which allows safe operation over non-secured networks.             *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
#
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
   if [ "$(ls -A /etc/apache2/ssl)" ]; then
       sudo rm -f /etc/apache2/ssl/*                                          # previous directory found, so remove any old certificates
   else
      sudo mkdir /etc/apache2/ssl
   fi
   sudo a2enmod ssl

   # Create Self Signed Certificate...
   sudo openssl req -new -x509 -days 365 -nodes -out /etc/apache2/ssl/apache.pem -keyout /etc/apache2/ssl/apache.key
   sudo chmod 600 /etc/apache2/ssl/apache*
   sudo cp /etc/apache2/ssl/apache.pem /var/www/logs/                        # copy the certificate to location that can be accessed over the network
   
   # create the virtual site...
   sudo cp ./alarm-system/ConfigFiles/default-ssl.conf /etc/apache2/sites-available/
   sudo a2ensite default-ssl.conf                                             # enable web site
   sudo service apache2 reload
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 9                              *"
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
  # Install Fail2Ban
  sudo apt-get -y install fail2ban
  
  # create the local jail file...
  sudo cp alarm-system/ConfigFiles/jail.local /etc/fail2ban/
  sudo chown root /etc/fail2ban/jail.local
  sudo chgrp root /etc/fail2ban/jail.local
  
  # create the regex filter...
  sudo cp alarm-system/ConfigFiles/alarm-regex.conf /etc/fail2ban/filter.d/
  sudo chown root /etc/fail2ban/filter.d/alarm-regex.conf
  sudo chgrp root /etc/fail2ban/filter.d/alarm-regex.conf

  # Define additional actions to integrate Fail2Ban with the alarm service filter...
  sudo cp alarm-system/ConfigFiles/alarm-actions.conf /etc/fail2ban/action.d/
  sudo chown root /etc/fail2ban/action.d/alarm-actions.conf
  sudo chgrp root /etc/fail2ban/action.d/alarm-actions.conf

  # create the shell script to integrate with the alarm service...
  sudo cp alarm-system/Scripts/fail2ban_alarm.sh /etc/fail2ban/action.d/
  sudo chown root /etc/fail2ban/action.d/fail2ban_alarm.sh
  sudo chgrp root /etc/fail2ban/action.d/fail2ban_alarm.sh
  sudo chmod +x /etc/fail2ban/action.d/fail2ban_alarm.sh
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 10                             *"
echo "*                                                                              *"
echo "*  Install and configure HomeKit Bridge                                        *"
echo "*                                                                              *"
echo "* This uses HAP-NodeJS, a Node.js implementation of Apples HomeKit Accessory   *"
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
  # install various pre requisites...
  sudo apt-get install libavahi-compat-libdnssd-dev -y

  wget -qO- https://deb.nodesource.com/setup_8.x | sudo bash -
  sudo apt-get -y install nodejs

  # install the HAP-NodeJS server...
  git clone git://github.com/KhaosT/HAP-NodeJS.git
  cd HAP-NodeJS/
  npm rebuild
  sudo npm install buffer-shims --unsafe-perm
  sudo npm install curve25519-n2 --unsafe-perm
  sudo npm install debug --unsafe-perm
  sudo npm install ed25519 --unsafe-perm
  sudo npm install fast-srp-hap --unsafe-perm
  sudo npm install ip --unsafe-perm
  sudo npm install mdns --unsafe-perm
  sudo npm install node-persist --unsafe-perm
  cd ..
  
  # create the new daemon...
  sudo cp /home/pi/Downloads/alarm-system/Scripts/homebridge /etc/init.d/
  sudo chgrp root /etc/init.d/homebridge
  sudo chown root /etc/init.d/homebridge
  sudo chmod +x /etc/init.d/homebridge

  # make daemon autostart...
  sudo update-rc.d homebridge defaults
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*                                                                              *"
echo "*  oddwires.co.uk Alarm System installer: Stage 11                             *"
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
echo
sudo reboot

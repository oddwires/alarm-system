#!/bin/bash
clear
echo "********************************************************************************"
echo "*  oddwires.co.uk Alarm System installer: Stage 1                              *"
echo "*       Install Debian updates.                                                *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
  # Updates...
  sudo apt-get -y update
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*  oddwires.co.uk Alarm System installer: Stage 2                              *"
echo "*       Install Debian upgrades.                                               *"
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
echo "*  oddwires.co.uk Alarm System installer: Stage 3                              *"
echo "*       Install Mail Transfer Agent.                                           *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
  # Mail Transfer Agent...
  sudo apt-get install -y heirloom-mailx
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*  oddwires.co.uk Alarm System installer: Stage 4                              *"
echo "*       Install I2C Tools.                                                     *"
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
echo "*  oddwires.co.uk Alarm System installer: Stage 5                              *"
echo "*       Install Apache and PHP.                                                *"
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
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*  oddwires.co.uk Alarm System installer: Stage 6                              *"
echo "*       Install Samba.                                                         *"
echo "*                                                                              *"
echo "*  Samba provides file services that allow Windows devices to connect to       *"
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
echo "*  oddwires.co.uk Alarm System installer: Stage 7                              *"
echo "*       Install alarm web page.                                                *"
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
  chmod -R 750 /var/www/jQTouch
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
echo "*  oddwires.co.uk Alarm System installer: Stage 8                              *"
echo "*       Install alarm daemon.                                                  *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
      # Check for previous alarm daemon...
      if [ "$(ls -A /etc/init.d/alarm)" ]; then
         echo "Previous alarm daemon found."
         echo "Removing previous alarm daemon."
         sudo update-rc.d -f alarm remove
         echo "Previous daemon removed"
      fi

      # create the new daemon...
      sudo mv /var/www/Scripts/alarm /etc/init.d/
      chgrp root /etc/init.d/alarm

      # make daemon autostart...
      sudo update-rc.d alarm defaults
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*  oddwires.co.uk Alarm System installer: Stage 9                              *"
echo "*       Create Self Signed Certificate and configure                           *"
echo "*       Apache secure data transfers (TLS encryption).                         *"
echo "*                                                                              *"
echo "*  Press 'I'      to Install                                                   *"
echo "*        'S'      to Skip                                                      *"
echo "*        'Ctrl+C' to Quit the installer                                        *"
echo "*                                                                              *"
echo "********************************************************************************"
read -n1 -r key
echo
if [[ "$key" = "I" ]] || [[ "$key" = "i" ]]; then
  # Create Self Signed Certificate...
   sudo a2ensite default-ssl
   sudo a2enmod ssl

   if [ "$(ls -A /etc/apache2/ssl)" ]; then
       sudo rm -f /etc/apache2/ssl/*                                         # previous directory found, so remove any old certificates
   else
      sudo mkdir /etc/apache2/ssl
   fi

   sudo openssl req -x509 -nodes -days 1095 -newkey rsa:2048 -out /etc/apache2/ssl/server.crt -keyout /etc/apache2/ssl/server.key
   sudo chmod 600 /etc/apache2/ssl/server.key
   
   # edit the port configuration...
   filename='/etc/apache2/ports.conf'                                         # File to be edited
   oldstring='NameVirtualHost'                                                # need to replace this string...
   newstring='#NameVirtualHost'                                               # ... with this one
   sed -i -e "s@$oldstring@$newstring@g" "$filename"                          # do it.
   oldstring='Listen 80'                                                      # and need to replace this string...
   newstring='#Listen 80'           # ... with this one
   sed -i -e "s@$oldstring@$newstring@g" "$filename"                          # do it.
   
   # edit the virtual site file...
   filename='/etc/apache2/sites-enabled/default-ssl'
   oldstring='SSLCertificateFile    /etc/ssl/certs/ssl-cert-snakeoil.pem'     # need to replace this string...
   newstring='SSLCertificateFile    /etc/apache2/ssl/server.crt'              # ... with this one
   sed -i -e "s@$oldstring@$newstring@g" "$filename"                          # do it.
 	
   oldstring='SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key'   # need to replace this string...
   newstring='SSLCertificateKeyFile /etc/apache2/ssl/server.key'              # ... with this one
   sed -i -e "s@$oldstring@$newstring@g" "$filename"                          # do it.
   
   sudo service apache2 restart
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*  oddwires.co.uk Alarm System installer: Stage 10                             *"
echo "*       Install and configure Fail2Ban                                         *"
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
  sudo mv alarm-system/ConfigFiles/jail.local /etc/fail2ban/
  sudo chown root /etc/fail2ban/jail.local
  sudo chgrp root /etc/fail2ban/jail.local
  
  # create the regex filter...
  sudo mv alarm-system/ConfigFiles/alarm-regex.conf /etc/fail2ban/filter.d/
  sudo chown root /etc/fail2ban/filter.d/alarm-regex.conf
  sudo chgrp root /etc/fail2ban/filter.d/alarm-regex.conf

  # Define additional actions to integrate Fail2Ban with the alarm service filter...
  sudo mv alarm-system/ConfigFiles/alarm-actions.conf /etc/fail2ban/action.d/
  sudo chown root /etc/fail2ban/action.d/alarm-actions.conf
  sudo chgrp root /etc/fail2ban/action.d/alarm-actions.conf

  # create the shell script to integrate with the alarm service...
  sudo mv alarm-system/Scripts/fail2ban_alarm.sh /etc/fail2ban/action.d/
  sudo chown root /etc/fail2ban/action.d/fail2ban_alarm.sh
  sudo chgrp root /etc/fail2ban/action.d/fail2ban_alarm.sh
  sudo chmod +x /etc/fail2ban/action.d/fail2ban_alarm.sh
 
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*  oddwires.co.uk Alarm System installer: Stage 11                             *"
echo "*       Install and configure HomeKit Bridge                                   *"
echo "*                                                                              *"
echo "* This uses HAP-NodeJS, a Node.js implementation of Apples HomeKit Accessory   *"
echo "* Server. This allows the Radio Control switches to be controlled verbally     *"
echo "* using Siri.                                                                  *"
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
  sudo apt-get install libnss-mdns libavahi-compat-libdnssd-dev -y

  sudo apt-get install -y gcc-4.8 g++-4.8
  sudo update-alternatives --install /usr/bin/gcc gcc /usr/bin/gcc-4.6 20
  sudo update-alternatives --install /usr/bin/gcc gcc /usr/bin/gcc-4.8 50
  sudo update-alternatives --install /usr/bin/g++ g++ /usr/bin/g++-4.6 20
  sudo update-alternatives --install /usr/bin/g++ g++ /usr/bin/g++-4.8 50

  wget https://nodejs.org/dist/v4.2.1/node-v4.2.1-linux-armv6l.tar.gz 
  tar -xvf node-v4.2.1-linux-armv6l.tar.gz 
  cd node-v4.2.1-linux-armv6l
  sudo cp -R * /usr/local/
  cd ..

  git clone https://github.com/oddwires/HAP-NodeJS.git
  cd HAP-NodeJS/
  sudo npm install node-persist
  sudo npm install srp
  sudo npm install mdns
  sudo npm install ed25519
  sudo npm install curve25519
  sudo npm install debug
  sudo npm -g install forever
  cd ..
  sudo rm -f node-v4.2.1-linux-armv6l.tar.gz
  
  read -n1 -r -p "Press any key to continue..." key
  
  git clone https://github.com/nfarina/homebridge.git
  sudo cp alarm-system/ConfigFiles/package.json homebridge/package.json
  cd homebridge
  npm install
  cd ..
  sudo rm -f node-v4.2.1-linux-armv6l.tar.gz
  
  # create the new daemon...
  sudo mv /var/www/Scripts/homebridge /etc/init.d/
  chgrp root /etc/init.d/homebridge
  # make daemon autostart...
  sudo update-rc.d homebridge defaults
fi
#read -n1 -r -p "Press any key to continue..." key
echo " "

clear
echo "********************************************************************************"
echo "*  oddwires.co.uk Alarm System installer: Stage 12                             *"
echo "*       The alarm system has been installed.                                   *"
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

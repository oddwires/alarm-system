#!/bin/bash
#################################################################################################################################
#                                                                                                                               #
#     Oddwires alarm service.  Version 5.00                                                                                     #
#     Latest details and build instructions... http://oddwires.co.uk/?page_id=123                                               #
#     Latest code and issues...                https://github.com/oddwires/oddwires-alarm-system                                #
#                                                                                                                               #
#################################################################################################################################
#                                                                                                                               #
# Features:                                                                                                                     #
#     RasPi platform - compatible with Rev 1.0 and Rev 2.0 hardware.                                                            #
#     12 configurable alarm zones (cabled).                                                                                     #
#     3 alarm modes: Standby, Night mode, Day mode.                                                                             #
#     Up to 160 configurable automation channels (radio controlled).                                                            #
#     Up to 9 configurable thermostatic radiator valves (radio controlled).                                                     #
#     Industry standard 12 volt interface to alarm sensors, bell boxes and strobe.                                              #
#     Internet remote control using iPhone SE web app interface.                                                                #
#     Animated page transitions and user controls - look and feel of a native app.                                              #
#     eMail alerts for alarm events.                                                                                            #
#     Automatically detect changes to router IP address.                                                                        #
#     Scheduled tasks.                                                                                                          #
#     Security logs.                                                                                                            #
#     Blowfish hashed passwords.                                                                                                #
#     'Remember Me' - quick login by means of one time access code.                                                             #
#     Fail2Ban - detects multiple failed logons and modify firewall rules to block offending IP address.                        #
#     Homebridge - Siri voice control.                                                                                          #
#                                                                                                                               #
#################################################################################################################################

# Use set -x to enable debugging
# Use set +x to disable debugging

set -f                                                         # Globbing off - we need to handle * and over characters

# Default password is a BlowFish hash. The actual password is qwerty
DefaultPassword='$2y$07$2c0bfALZ4WxenSybt2ZobO76Hyc5ZVH82DVMdl8GNp5WCljh/iT7G'

# Define dynamic data arrays. These will hold the data that ultimately gets displayed on the web page.
# NOTE: Multi dimensional arrays aren't available in BASH, so arrays use chunks of multiple elements to simulate multi dimensional arrays.

# Array to store Remote Control data. Two dimensional array, 7 x N.
declare -a rcon=()
rcon_header=0; rcon_name=1; rcon_address=2; rcon_channel=3; rcon_status=4 rcon_alrm_action=5; rcon_HK_type=6

# Array to store Alarm zone configuration.                      9 elements per record defined as follows...
Type=0                                                          # Tamper / Alarm
Name=1                                                          # Zone name
DayMode=2                                                       # on / off
NightMode=3                                                     # on / off
Chimes=4                                                        # on / off
Circuit=5                                                       # Input circuit monitored by this zone
Triggered=6                                                     # true / false - alarm status of zone
PreviousValue=7                                                 # on / off - previous value of circuit
CurrentValue=8                                                  # on / off - current state of circuit
declare -a zcon=()                                              # array to store alarm zone configuration details

# Array to store user data.                                     
# Note: first element is always used to store the email account details. This means they don't get published in the status file.
# 4 x elements per record defined as...
declare -a user=()
user_name=0                                                     # Name ( logon ID)
user_pword=1                                                    # Password ( stored as hash )
user_email=2                                                    # email address
user_OTAC=3                                                     # One Time Access Code ( used for 'remember me' function at logon )

# Array to store CRON Job data.                                 8 elements per record defined as...
cron_headr=0                                                    # header (used as list separator)
cron_mins=1                                                     # minutes
cron_hours=2                                                    # hours
cron_dom=3                                                      # day of month
cron_mnth=4                                                     # month
cron_wday=5                                                     # weekday
cron_cmnd=6                                                     # command ( Day mode, Cron, rcon etc )
cron_status=7                                                   # on/off
declare -a cron=()                                              # array to store cron job details

# Array to store Radiator valve data.                           # 6 x elements per record:
rdtr_headr=0                                                    # header (used as list separator)
rdtr_name=1                                                     # name
rdtr_address=2                                                  # address
rdtr_status=3                                                   # Hi / Low
rdtr_hi=4                                                       # radiator temp high
rdtr_lo=5                                                       # radiator temp low
declare -a rdtr=()

# Define look up tables...
# Tasks are passed back as an index number. This array expands the number into a string to use in the cron job.
 declare -a cmnd=()
 
 declare -a AllPorts=('4' '18' '17' '23' '9' '25' '10' '11' '8' '7')          # All Broadcom GPIO port numbers

# List of anodes used by the 12 input circuits. There is no logic behind these - its just the way the PCB is constructed.
 declare -a anode=('4' '18' '17' '23' '4' '18' '17' '23' '4' '18' '17' '23')

# List of cathodes used by the 12 input circuits. There is no logic behind these - its just the way the PCB is constructed.
# ONLY THEY'RE NOT USED AS CATHODES ON THIS HARDWARE - THEY'RE JUST GPIO INPUTS, SO I PROBABLY NEED TO RENAME THIS VARIABLE
 declare -a cathode=('9' '9' '9' '9' '25' '25' '25' '25' '10' '10' '10' '10')

# GLOBAL variables used by Setup...
SETUP_routerIP=""; SETUP_localIP=""; SETUP_duration=""; SETUP_location=""; SETUP_diskused=""; SETUP_diskperc=""; SETUP_disktotal=""
SYSTEM_uptime=""; hardware="Unknown hardware"; running_on_RasPi="false"; memory="unknown"

# GLOBAL variables used by email...
EMAIL_server=""; EMAIL_port=""; EMAIL_sender=""; EMAIL_password=""

# GLOBAL variables used by alarm...
alarm="Set" ; mode="Standby"                 # Note: ${alarm} has 3 states: 'Set' 'Active !' and 'Timeout !'
changed=""                                   # flag to indicate either the state of an alarm circuit, or the configuration of an alarm
                                             # zone has changed. Either can cause an alarm zone to trigger and the alarm to activate.
# start defining functions ...

Homebridge_Export()
#################################################################################################################################
#                                                                                                                               #
# Function to export all currently configured devices to HomeKit accessories                                                    #
#                                                                                                                               #
#################################################################################################################################
{  
#  Types_File="/home/pi/Downloads/alarm-system/ConfigFiles/types.js"
   PathToHAPNodeJS=$(sudo find / -type d -name "HAP-NodeJS")                               # don't really know where the install has
   PathToConfigFiles=$(sudo find / -type d -name "ConfigFiles")                            # been run from so find one of the source files
   echo HAP-NodeJS install found at: $PathToHAPNodeJS
   echo Source config files found at: $PathToConfigFiles
   i=0 ; MAC_Count=0

   printf "Removing existing accessories...\n"
   AccessoriesPath=$PathToHAPNodeJS/accessories/
   find "$AccessoriesPath" -name * -type f -delete

   printf "Removing existing device pairing...\n"
   PersistPath=$PathToHAPNodeJS/persist/
   find "$PersistPath" -name * -type f -delete

# create an accessory file and customise the parameters for all defined alarm zones...   
   maxval=${#zcon[@]} ; (( maxval-- )) ; i=0                                                # bump down because the array starts at zero
   while [ $i -le "$maxval" ]; do
      ZoneName="${zcon[$i+Name]}"
      ZoneName="${ZoneName//[^[:ascii:]]/}"                                                 # stripping out any emojis
      ZoneName="${ZoneName%"${ZoneName##*[![:space:]]}"}"                                   # strip any trailing spaces left by removing emojis
      MAC_address=$(printf "fa:3c:ed:5a:1a:%02x\n" ${MAC_Count})
#     echo $MAC_address                                                                     # Diagnostic
      FileName=$PathToHAPNodeJS"/accessories/"${ZoneName}"_accessory.js"
      printf "Creating Homekit Contact sensor: %s\n" "${ZoneName}"
      cp $PathToConfigFiles"/Generic_ContactSensor.js" "${FileName}"
      oldstring='Parm1'                                                                     # need to replace this string...
      newstring=${ZoneName}                                                                 # ... with the zone name
      sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                     # do it.
      oldstring='Parm2'                                                                     # need to replace this string...
      newstring=${MAC_address}
      sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                     # do it.
      
      (( MAC_Count++ ))                                                                     # Bump the loop and MAC counters
      i=$(( i + 9 )) 
   done
  
# create an accessory file and customise the parameters for all defined power outlets...   
   maxval=${#rcon[@]} ; (( maxval-- )) ; Count=0 ; i=0                                     # bump down because the array starts at zero
   while [ $i -le "$maxval" ]; do
#     printf "rcon:%s:%s:%s:%s:%s:%s:%s\n" "${rcon[$i+rcon_header]}" "${rcon[$i+rcon_name]}" "${rcon[$i+rcon_address]}" \
#              "${rcon[$i+rcon_channel]}" "${rcon[$i+rcon_status]}" "${rcon[$i+rcon_alrm_action]}" "${rcon[$i+rcon_HK_type]}"
      AccName="${rcon[$i+rcon_name]}"
      AccName="${AccName//[^[:ascii:]]/}"                                                  # stripping out any emojis
      AccName="${AccName%"${AccName##*[![:space:]]}"}"                                     # strip any trailing spaces left by removing emojis
      FileName=$PathToHAPNodeJS"/accessories/"${AccName}"_accessory.js"
      MAC_address=$(printf "fa:3c:ed:5a:1a:%02x\n" ${MAC_Count})
      case "${rcon[$i+rcon_HK_type]}" in                                                   # Create default accessory file
          "Light")
             cp $PathToConfigFiles"/Generic_Light.js" "$FileName";;
          "Outlet")
             cp $PathToConfigFiles"/Generic_Outlet.js" "$FileName";;
          "Fan")
             cp $PathToConfigFiles"/Generic_Fan.js" "$FileName";;
      esac
      if [ "${rcon[$i+rcon_HK_type]}" != "None" ]; then
          printf "Creating Homekit power outlet: %s\n" "$AccName"
          # if we have copied a file we need to customise it...
          # Function strings to pass to alarm service
          oldstring='Parm1'                                                                # need to replace this string...
          newstring="${rcon[$i+rcon_name]}"                                                # ... with the accessory name
          sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                # do it.
          oldstring='Parm2'                                                                # need to replace this string...
          newstring=${MAC_address}
          sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                # do it.
          oldstring='Parm3'                                                                # need to replace this string...
          newstring='Siri:iPhone:rcon swtch:'$Count':On\\n'                                # command to switch accessory on       
          sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                # do it.
          oldstring='Parm4'                                                                # need to replace this string...
          newstring='Siri:iPhone:rcon swtch:'$Count':Off\\n'                               # command to switch accessory off
          sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                # do it.
          oldstring='Parm5'                                                                # need to replace this string...
          newstring=$(printf 'Handset %02d, Button %01d\n' "${rcon[i+rcon_address]}" \
                       "${rcon[$i+rcon_channel]}")                                         # configuration details
          sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                # do it.
      fi
      Count=$(( Count + 1 ))        
      (( MAC_Count++ ))                                                                    # Bump the loop and MAC counters
      i=$(( i + 7 )) 
   done

# create an accessory file and customise the parameters for all defined radiator...   
   maxval=${#rdtr[@]} ; (( maxval-- )) ; Count=0 ; i=0                                     # bump down because the array starts at zero
   while [ $i -le "$maxval" ]; do
#     printf "rdtr:%s:%s:%s:%s:%s:%s\n" "${rdtr[$i+rdtr_header]}" "${rdtr[$i+rdtr_name]}" "${rdtr[$i+rdtr_address]}" \
#                "${rdtr[$i+rdtr_status]}" "${rdtr[$i+rdtr_hi]}" "${rdtr[$i+rdtr_lo]}"
      FileName=$PathToHAPNodeJS"/accessories/"${rdtr[$i+rdtr_name]}" radiator_accessory.js"
      cp $PathToConfigFiles"/Generic_Radiator.js" "$FileName"
      MAC_address=$(printf "fa:3c:ed:5a:1a:%02x\n" ${MAC_Count})
#     if [ "${rdtr[$i+4]}" != "None" ]; then                                               # Oops ! - think I've lost some functionality
          printf "Creating Homekit radiator: %s radiator_accessory.js\n" "${rdtr[$i+rdtr_name]}"     # visual progress indicator
          # if we have copied a file we need to customise it...
          # Function strings to pass to alarm service
          oldstring='Parm1'                                                                # need to replace this string...
          newstring="${rdtr[$i+rdtr_name]}"                                                # ... with the accessory name
          sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                # do it.
          oldstring='Parm2'                                                                # need to replace this string...
          newstring=${MAC_address}
          sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                # do it.
          oldstring='Parm3'                                                                # need to replace this string...
          newstring='Siri:iPhone:rdtr swtch:'$Count':On\\n'                                # command to switch accessory on       
          sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                # do it.
          oldstring='Parm4'                                                                # need to replace this string...
          newstring='Siri:iPhone:rdtr swtch:'$Count':Off\\n'                               # command to switch accessory off
          sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                # do it.
          oldstring='Parm5'                                                                # need to replace Max temp...
          newstring="${rdtr[$i+rdtr_hi]}"                                                  # configuration details
          sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                # do it.
          oldstring='Parm6'                                                                # need to replace Min temp...
          newstring="${rdtr[$i+rdtr_lo]}"                                                  # configuration details
          sed -i -e "s@$oldstring@$newstring@g" "$FileName"                                # do it.
#     fi
      Count=$(( Count + 1 ))        
      (( MAC_Count++ ))                                                                    # Bump the loop and MAC counters
      i=$(( i + 6 )) 
   done

   cp $PathToConfigFiles"/types.js" $PathToHAPNodeJS"/accessories/types.js"
   sudo killall node                                                                       # ensure we get a clean start...
   printf "Export complete\nRestarting HAP-NodeJS\n"
   sudo service homebridge restart                                                         # restart Homebridge to pick up new accessories
}

WriteUsers()
#################################################################################################################################
#                                                                                                                               #
# Function to dump user credentials from memory to file.                                                                        #
#                                                                                                                               #
#################################################################################################################################
{ if [ -f /var/www/user.txt ]; then                                # clear out previous results
    rm /var/www/user.txt; fi
#   printf "email:%s:%s:-\n" "${EMAIL_password}" "${EMAIL_sender}"  >>/var/www/user.txt
    maxval=${#user[@]} ; (( maxval-- ))                            # bump down because the array starts at zero
    i=0
    while [ $i -le "$maxval" ]; do
       printf "%s:%s:%s:%s\n" "${user[$i+$user_name]}" "${user[$i+user_pword]}" "${user[$i+user_email]}" "${user[$i+user_OTAC]}" >>/var/www/user.txt
       i=$(( i + 4 )) 
    done 
    chgrp root /var/www/user.txt                                   # only visible to root
}

CreateTaskList()
#################################################################################################################################
#                                                                                                                               #
# Function to create a list of tasks currently defined on the device.                                                           #
# This is required to provide accurate descriptions on events in the log files and on the console.                              #
# The first four tasks are static as they allow the status of the alarm to be controlled.                                       #
# The remaining tasks are variable based on the Remote Control and Radiator channels currently defined.                         #
# Each Remote Control channel and radiator requires an 'on' task and an 'off' task associated with it.                          #
#                                                                                                                               #
#################################################################################################################################
{   cmnd+=('mode:Day mode' 'mode:Night mode' 'mode:Standby' 'Check ip' )
    maxval=$(( ${#rcon[@]} / 7 )) ; (( maxval-- ))                            # bump down because the array starts at zero
    i=0
    while [ $i -le "$maxval" ]; do
       cmnd+=("rcon swtch:$i")                                                # add the Remote Control channel name
       (( i++ )) 
    done
    maxval=$(( ${#rdtr[@]} / 5 )) ; (( maxval-- ))                            # bump down because the array starts at zero
    i=0
    while [ $i -le "$maxval" ]; do
       cmnd+=("rdtr swtch:$i")                                                # add the radiator channel name
       (( i++ )) 
    done
#   declare -p cmnd                                                           # DIAGNOSTIC - dump result to console
}

WriteCronJobs()
#################################################################################################################################
#                                                                                                                               #
# Function to load the CRONTAB with the contents of the CronJobs array.                                                         #
#                                                                                                                               #
#################################################################################################################################
{ if [ -f /var/www/cron.txt ]; then                                # clear out previous results
    rm /var/www/cron.txt; fi
    CreateTaskList                                                  # make sure list is current
    maxval=${#cron[@]} ; (( maxval-- ))                            # bump down because the array starts at zero
    i=0
    printf "" >>/var/www/cron.txt                                  # create blank file-  this allows the CRONTAB file to be
                                                                   # zeroed when no tasks have been defined
    while [ $i -le "$maxval" ]; do
       # got to handle different formats for the static and dynamic jobs...
       DeviceNum=${cron[$i+cron_cmnd]}                             # This is the position of the device in our list on the web page
       if (( DeviceNum < 4 )) ; then
       # First three devices have static names and don't need to specify on/off.
       printf "%s %s %s %s %s echo \"task:RasPi:%s\" >>/var/www/data/input.txt\n" \
                 "${cron[$i+cron_mins]}" "${cron[$i+cron_hours]}" "${cron[$i+cron_dom]}" "${cron[$i+cron_mnth]}" "${cron[$i+cron_wday]}" \
                 "${cmnd[$DeviceNum]}" >>/var/www/cron.txt
       else
       # Devices > 4 have variable names and need the additional on/off parameter
       printf "%s %s %s %s %s echo \"task:RasPi:%s:%s\" >>/var/www/data/input.txt\n" \
                 "${cron[$i+cron_mins]}" "${cron[$i+cron_hours]}" "${cron[$i+cron_dom]}" "${cron[$i+cron_mnth]}" "${cron[$i+cron_wday]}" \
                 "${cmnd[$DeviceNum]}" "${cron[$i+cron_status]}" >>/var/www/cron.txt
       fi
       i=$(( i + 8 )) 
    done
    crontab /var/www/cron.txt                                       # load the CRONTAB file
    rm /var/www/cron.txt                                            # tidy up
}

load_user_file()
#################################################################################################################################
#                                                                                                                               #
# Function to load user credentials from file to memory.                                                                        #
#                                                                                                                               #
#################################################################################################################################
{  unset user[@] ; userindex=0                                       # reset data arrays and pointers
   if [ -r /var/www/user.txt ]; then
       while read info; do
#        echo $info                                                  # DIAGNOSTIC
         OLD_IFS="$IFS"                                              # new mechanism
         IFS=":"                                                     # split the command on ':' - spaces are allowed
         user_array=( $info )
         IFS="$OLD_IFS"
         user[${userindex}]=${user_array[0]} ; (( userindex++ ))     # Name
         user[${userindex}]=${user_array[1]} ; (( userindex++ ))     # Password
         user[${userindex}]=${user_array[2]} ; (( userindex++ ))     # email
         user[${userindex}]=${user_array[3]} ; (( userindex++ ))     # One Time Access Code
    done < /var/www/user.txt
    fi
    # extract email account details from the password file...
    EMAIL_server=$(sudo cat /etc/postfix/sasl_passwd | awk -F':' '{print $1}')    # split on ':'
    tmp=$(sudo cat /etc/postfix/sasl_passwd | awk -F':' '{print $2}')             # split the split in ' '
    EMAIL_port=$(echo $tmp | awk -F' ' '{print $1}')
    EMAIL_sender=$(echo $tmp | awk -F' ' '{print $2}')
#   EMAIL_password=$(sudo cat /etc/postfix/sasl_passwd | awk -F':' '{print $3}')  # split on ':'

#   declare -p user                                                  # DIAGNOSTIC
}

CheckIP()
#################################################################################################################################
#                                                                                                                               #
# Subroutine uses an external site http://checkip.dyndns.com to obtain router details.                                          #
# This routing should not be called more then once every five minutes (300 seconds).                                            #
#                                                                                                                               #
# This routine started life just checking the Router IP, but has been extended to check all system informaion ( eg local IP,    #
#   available memory, available disk etc. )                                                                                     #
#                                                                                                                               #
#################################################################################################################################
{ 
  Current_routerIP=$(wget -q -O - checkip.dyndns.org|sed -e 's/.*Current IP Address: //' -e 's/<.*$//')
  if [[ $Current_routerIP != $SETUP_routerIP ]] ; then
     title="Alarm system: Router IP change"
     eMail "$title"
     SETUP_routerIP=${Current_routerIP}                                            # Update variable
     tmp=${CURRTIME}",task,raspi,New router IP = "${SETUP_routerIP}                # string for log
   else
     tmp=${CURRTIME}",task,raspi,Check router IP - no change"                      # string for log
   fi
   echo $tmp >> $LOGFILE                                                           # log the event
   echo $tmp                                                                       # copy to console
}

InitPorts()
#################################################################################################################################
#                                                                                                                               #
# BASH uses BCM GPIO numbers (the pin names on the Broadcom chip) to define the GPIO ports.                                     #
#                                                                                                                               #
#################################################################################################################################
{ # Define 10 GPIO ports
  for thisport in "${AllPorts[@]}"; do
   tmp="/sys/class/gpio/gpio"${thisport}                                          # Variable indirection
#  echo $tmp                                                                      # DIAGNOSTIC - $tmp becomes the full Broadcom name for the physical pin
   if [ -d $tmp ]; then                                                           # if some other process is using the port...
      echo ${thisport} > /sys/class/gpio/unexport                                 # ...grab it back
   fi
   echo ${thisport} > /sys/class/gpio/export                                      # now the port is free, grab it for our use
   done

  # Set 7 ports as outputs...
  echo "out" > /sys/class/gpio/gpio4/direction
  echo "out" > /sys/class/gpio/gpio17/direction
  echo "out" > /sys/class/gpio/gpio18/direction
  echo "out" > /sys/class/gpio/gpio23/direction
  echo "out" > /sys/class/gpio/gpio11/direction
  echo "out" > /sys/class/gpio/gpio8/direction
  echo "out" > /sys/class/gpio/gpio7/direction

  # Set 3 ports as inputs...
  echo "in" > /sys/class/gpio/gpio10/direction
  echo "in" > /sys/class/gpio/gpio9/direction
  echo "in" > /sys/class/gpio/gpio25/direction

  # Set outputs to inactive state...
  echo "0" > /sys/class/gpio/gpio4/value                       # LED Anode output   - inactive=low
  echo "0" > /sys/class/gpio/gpio17/value                      # LED Anode output   - inactive=low
  echo "0" > /sys/class/gpio/gpio18/value                      # LED Anode output   - inactive=low
  echo "0" > /sys/class/gpio/gpio23/value                      # LED Anode output   - inactive=low
  echo "0" > /sys/class/gpio/gpio11/value                      # Bell               - inactive=low
  echo "0" > /sys/class/gpio/gpio8/value                       # Strobe             - inactive=low
  echo "0" > /sys/class/gpio/gpio7/value                       # Sound bomb         - inactive=low

  # Load the I2C drivers...
  sudo modprobe i2c_bcm2708                                    # load drivers manually for this session
  sudo modprobe i2c-dev
  tmp=${CURRTIME}",alarm,raspi,I2C Bus initialised"
  echo $tmp >> $LOGFILE                                        # log the event
  echo $tmp                                                    # tell the user

  running_on_RasPi="true"                                      # flag to show we are on a pi with input ports 
                                                               # so alarm circuits should be scanned.
}

eMail()
#################################################################################################################################
#                                                                                                                               #
# Performs a few basic checks on the email credentials.                                                                         #
# If everything seems OK, a standard format email is sent out.                                                                  #
# $1 = Subject                                                                                                                  #
# Note: The Postfix Mail Transfer Agent is allows emails to be sent from the alarm service.                                     #
#       The configuration file has been hard coded to use Gmail SMTP as a relay.                                                #
#                                                                                                                               #
#################################################################################################################################
{  # Build the circulation list...
   circlist="";                                                                              # clear out variable
   maxval=${#user[@]} ; (( maxval-- )) ; i=0                                                 # bump down because the array starts at zero
   while [ $i -le "$maxval" ]; do
      circlist=${circlist}${user[$i+2]}","
      i=$(( i + 4 )) 
   done
   circlist=${circlist%?}                                                                    # remove the last character - its an extra ','

  # Quick and dirty test for valid email configuration....
  if [[ ${circlist} == "" ]] || [ ! -f /etc/postfix/sasl_passwd.db ]; then
     tmp=${CURRTIME}",alarm,raspi,email not sent - bad credentials or no circulation list."
     echo $tmp >> $LOGFILE                                                                   # log the event
     echo $tmp                                                                               # tell the user
  else
     # Falls through here if we have some kind of email configuration and some kind of circulation list
     # We still can't guarantee the email will go, but lets try anyway...
     # Build the message...
       msg='From:\t\t\t'$SETUP_location
       msg=$msg'\r\nEvent logged at:\t'${CURRTIME}
       msg=$msg'\n\nTriggered zones:\t'
       zones=''
       maxval=${#zcon[@]}; (( maxval-- )); i=0                                               # setup scan through all defined alarm zones
          while [ $i -le "$maxval" ]; do                                                     # only testing configured zones
                  if [[ ${zcon[$i+$Triggered]} == "true" ]];  then
                     zones=$zones${zcon[$i+$Name]}'\n\t\t\t' ;                               # Zone triggered, so add name
                  fi
                  i=$(( i + 9 ))
          done
      if [[ ${zones} == "" ]] ; then zones='None\n' ; fi                                     # default case - none triggered
      msg=$msg$zones
      msg=$msg'\nLocal IP:\t\t'https://${SETUP_localIP}
      msg=$msg'\n\nRouter IP:\t\t'https://${SETUP_routerIP}

      # Build the Postfix command string...
      tmp='echo -e "'$msg'" | mail -s "'$1'" $circlist'

      eval $tmp                                                           # send the email without echoing all the credentials to the screen
#     echo $tmp                                                           # DIAGNOSTIC - used to check MAILX command line is ok
      tmp=${CURRTIME}",alarm,raspi,"$1" - email sent"
      echo $tmp >> $LOGFILE                                                                  # log the event
      echo $tmp                                                                              # tell the user
  fi
}

load_status_file()
#################################################################################################################################
#                                                                                                                               #
# Routine to load configuration file to memory.                                                                                 #
#                                                                                                                               #
#################################################################################################################################
{ unset rcon[@] ; rconindex=0                                              # reset data arrays and pointers
  unset cron[@] ; cronindex=0
  unset zcon[@] ; zconindex=0
  unset rdtr[@] ; rdtrindex=0
  while read info; do
#       echo "info1 - "$info                                                # DIAGNOSTIC
        OLD_IFS="$IFS"                                                      # split the line
        IFS=:
        info_array=( $info )
#       declare -p info_array                                               # DIAGNOSTIC
        IFS="$OLD_IFS"
        case "${info_array[0]}" in                                          # Just looking at the first string
          "rcon")                                                           # Load Remote Control channel data
                rcon[${rconindex}]=${info_array[1]} ; (( rconindex++ ))
                rcon[${rconindex}]=${info_array[2]} ; (( rconindex++ ))
                rcon[${rconindex}]=${info_array[3]} ; (( rconindex++ ))
                rcon[${rconindex}]=${info_array[4]} ; (( rconindex++ ))
                rcon[${rconindex}]=${info_array[5]} ; (( rconindex++ ))
                rcon[${rconindex}]=${info_array[6]} ; (( rconindex++ ))
                rcon[${rconindex}]=${info_array[7]} ; (( rconindex++ )) ;;
          "zcon")                                                            # Load zone data...
                zcon[${zconindex}]=${info_array[1]} ; (( zconindex++ ))      # Type
                zcon[${zconindex}]=${info_array[2]} ; (( zconindex++ ))      # Name
                zcon[${zconindex}]=${info_array[3]} ; (( zconindex++ ))      # Day mode
                zcon[${zconindex}]=${info_array[4]} ; (( zconindex++ ))      # Night mode
                zcon[${zconindex}]=${info_array[5]} ; (( zconindex++ ))      # Chimes
                zcon[${zconindex}]=${info_array[6]} ; (( zconindex++ ))      # Circuit
                zcon[${zconindex}]="false"          ; (( zconindex++ ))      # Triggered
                zcon[${zconindex}]="0"              ; (( zconindex++ ))      # Previous value
                zcon[${zconindex}]="0"              ; (( zconindex++ )) ;;   # Current value
          "cron")
                cron[${cronindex}]=${info_array[1]} ; (( cronindex++ ))
                cron[${cronindex}]=${info_array[2]} ; (( cronindex++ ))
                cron[${cronindex}]=${info_array[3]} ; (( cronindex++ ))
                cron[${cronindex}]=${info_array[4]} ; (( cronindex++ ))
                cron[${cronindex}]=${info_array[5]} ; (( cronindex++ ))
                cron[${cronindex}]=${info_array[6]} ; (( cronindex++ ))
                cron[${cronindex}]=${info_array[7]} ; (( cronindex++ ))
                cron[${cronindex}]=${info_array[8]} ; (( cronindex++ )) ;;
          "rdtr")
                rdtr[${rdtrindex}]=${info_array[1]} ; (( rdtrindex++ ))
                rdtr[${rdtrindex}]=${info_array[2]} ; (( rdtrindex++ ))
                rdtr[${rdtrindex}]=${info_array[3]} ; (( rdtrindex++ ))
                rdtr[${rdtrindex}]=${info_array[4]} ; (( rdtrindex++ ))
                rdtr[${rdtrindex}]=${info_array[5]} ; (( rdtrindex++ ))
                rdtr[${rdtrindex}]=${info_array[6]} ; (( rdtrindex++ )) ;;
          "alarm")
            if [[ ${info_array[1]} = "duration" ]]; then
                SETUP_duration=${info_array[2]}
            fi ;;
          "setup")
                case "${info_array[1]}" in
                   "location")
                        SETUP_location=${info_array[2]};;
                   "routerIP")
                        SETUP_routerIP=${info_array[2]};;
                   "localIP")
                        SETUP_localIP=${info_array[2]};;
                esac;;
          "email")
                case "${info_array[1]}" in
                   "server")
                         EMAIL_server=${info_array[2]};;
                   "port")
                         EMAIL_port=${info_array[2]};;
                esac;;
        esac
  done <$1                                                                   # file name passed as parameter.
}

write_status_file()
#################################################################################################################################
#                                                                                                                               #
# This file is  used as a status flag by the web page.                                                                          #
# The web page reloads as soon as the file appears. So to prevent the web page from loading before the file has finished being  #
# written, it is created under a temporary name, and is only changed to the status file when all the data is complete.          #
#                                                                                                                               #
#################################################################################################################################
{ # Update the system info...
   SETUP_localIP=$(hostname -I)
   SETUP_diskused=$(df -h | grep root | awk '{print $3}')
   SETUP_diskperc=$(df -h | grep root | awk '{print $5}')
   SETUP_disktotal=$(df -h | grep root | awk '{print $2}')
   tmp=$(cat /proc/meminfo | grep MemTotal | awk '{print $2}')                     # in KB
   memory=$((tmp /1024))' MB'                                                      # convert to MB
   SYSTEM_uptime=$(uptime -p | cut -c 3-)

   echo "alarm:status:"$alarm >>/var/www/temp1.txt
   echo "alarm:mode:"$mode >>/var/www/temp1.txt
   echo "alarm:duration:"${SETUP_duration} >>/var/www/temp1.txt
   echo "setup:location:"${SETUP_location} >>/var/www/temp1.txt
   echo "setup:routerIP:"${SETUP_routerIP} >>/var/www/temp1.txt
   echo "setup:localIP:"${SETUP_localIP} >>/var/www/temp1.txt
   echo "setup:diskused:"${SETUP_diskused} >>/var/www/temp1.txt
   echo "setup:disk%:"${SETUP_diskperc} >>/var/www/temp1.txt
   echo "setup:disktotal:"${SETUP_disktotal} >>/var/www/temp1.txt
   echo "setup:memory:"${memory} >>/var/www/temp1.txt
   echo "setup:hardware:"${hardware} >>/var/www/temp1.txt
   echo "setup:uptime:"${SYSTEM_uptime} >>/var/www/temp1.txt
   echo "email:server:"${EMAIL_server} >>/var/www/temp1.txt
   echo "email:port:"${EMAIL_port} >>/var/www/temp1.txt
   echo "email:sender:"${EMAIL_sender} >>/var/www/temp1.txt

   # Write the Alarm Zone configuration...
   maxval=${#zcon[@]} ; (( maxval-- )) ; i=0                          # bump down because the array starts at zero
   while [ $i -le "$maxval" ]; do
      printf "zcon:%s:%s:%s:%s:%s:%s:%s:%s:%s\n" "${zcon[$i+$Type]}" "${zcon[$i+$Name]}" "${zcon[$i+$DayMode]}" \
                      "${zcon[$i+$NightMode]}" "${zcon[$i+$Chimes]}" "${zcon[$i+$Circuit]}" "${zcon[$i+$Triggered]}" \
                      "${zcon[$i+$PreviousValue]}" "${zcon[$i+$CurrentValue]}" >>/var/www/temp1.txt
      i=$(( i + 9 )) 
   done 

   # Write the Radio Control configuration...
   maxval=${#rcon[@]} ; (( maxval-- )) ; i=0                         # bump down because the array starts at zero
   while [ $i -le "$maxval" ]; do
      printf "rcon:%s:%s:%s:%s:%s:%s:%s\n" "${rcon[$i+rcon_header]}" "${rcon[$i+rcon_name]}" "${rcon[$i+rcon_address]}" \
                                           "${rcon[$i+rcon_channel]}" "${rcon[$i+rcon_status]}" "${rcon[$i+rcon_alrm_action]}" \
                                           "${rcon[$i+rcon_HK_type]}" >>/var/www/temp1.txt
      i=$(( i + 7 )) 
   done

   # Write the radiator configuration...
   maxval=${#rdtr[@]} ; (( maxval-- )) ; i=0                         # bump down because the array starts at zero
   while [ $i -le "$maxval" ]; do
      printf "rdtr:%s:%s:%s:%s:%s:%s\n" "${rdtr[$i+rdtr_headr]}" "${rdtr[$i+rdtr_name]}" "${rdtr[$i+rdtr_address]}" "${rdtr[$i+rdtr_status]}" \
                      "${rdtr[$i+rdtr_hi]}" "${rdtr[$i+rdtr_lo]}" >>/var/www/temp1.txt
      i=$(( i + 6 )) 
   done

   # Write the CRON jobs configuration...
   maxval=${#cron[@]} ; (( maxval-- )) ; i=0                         # bump down because the array starts at zero
   while [ $i -le "$maxval" ]; do
      printf "cron:%s:%s:%s:%s:%s:%s:%s:%s\n" "${cron[$i+cron_headr]}" "${cron[$i+cron_mins]}" "${cron[$i+cron_hours]}" \
                                           "${cron[$i+cron_dom]}" "${cron[$i+cron_mnth]}" "${cron[$i+cron_wday]}" \
                                           "${cron[$i+cron_cmnd]}" "${cron[$i+cron_status]}" >>/var/www/temp1.txt
      i=$(( i + 8 )) 
   done

   # Write the User configuration...
   maxval=${#user[@]} ; (( maxval-- )) ; i=4        # start at 4 because first 4 elements are the credentials for the 
                                                    # alarm system email account
   while [ $i -le "$maxval" ]; do
      printf "user:%s:%s\n" "${user[$i]}" "${user[$i+2]}" >>/var/www/temp1.txt  # don't print the password or the OTAC
      i=$(( i + 4 )) 
   done 
   mv /var/www/temp1.txt $1
}

alm_on()
#################################################################################################################################
#                                                                                                                               #
# Actions required when the alarm activates.                                                                                    #
#                                                                                                                               #
#################################################################################################################################
{ CURRTIME=`date "+%H:%M:%S"`                                                            # excel format
  tmp=${CURRTIME}",alarm,raspi,Alarm active"
  echo $tmp >> $LOGFILE                                                                  # log the event
  echo $tmp                                                                              # tell the user (like he needs to know!)

  tmp="5"                                                                                # default duration in seconds
  if [[ ${SETUP_duration} == "9 mins" ]]; then
     tmp=$((9 * 60))                                                                     # 9 minutes in seconds
  fi
  if [[ ${SETUP_duration} == "15 mins" ]]; then
     tmp=$((15 * 60))                                                                    # 15 minutes in seconds
  fi

  /var/www/Scripts/alarm_active.sh "${tmp}" &                                            # start alarm background process
  disown                                                                                 # suppress messages from shell

  title="Alarm system: ACTIVE"
  eMail "${title}"

  # scan through all the radio channels...
  maxval=${#rcon[@]} ; (( maxval-- ))                                                    # bump down because the array starts at zero
   i=0
   while [ $i -le "$maxval" ]; do                                                        # convert to upper case
       if [[ ${rcon[$i+3]^^} == "ON" ]]; then
          # if channel is configured to switch on during an alarm...
          echo 'alarm:raspi:rcon swtch:'$(( $i/7 ))':on' >> /var/www/data/input.txt
       fi
       if [[ ${rcon[$i+3]^^} == "OFF" ]]; then
          # if channel is configured to switch off during an alarm...
          echo 'alarm:raspi:rcon swtch:'$(( $i/7 ))':off' >> /var/www/data/input.txt
       fi
       i=$(( i + 7 ))
   done
}

check_for_alarm_condition()
#################################################################################################################################
#                                                                                                                               #
# Execution is directed here when an alarm zone has changed state. So this code decides what action, if any, to take.           #
#                                                                                                                               #
#################################################################################################################################
{  maxval=${#zcon[@]}; (( maxval-- )); i=0                                               # setup scan through all defined alarm zones
      while [ $i -le "$maxval" ]; do                                                     # only testing configured zones
      # test for tamper alarms....
      # Check zone is a tamper and circuit is active...
          if [[ ${zcon[$i+$Type]} = "tamper" ]] && \
             [[ ${zcon[$i+$CurrentValue]} = "0" ]]; then
                   zcon[$i+$Triggered]="true" ; alarm="Active !"                         # Zone triggered + alarm active
          fi
      # test for Day mode alarms....
      # Check zone is an alarm and circuit is active and zone is enabled in Day mode and the system is in Day mode...
          if [[ ${zcon[$i+$Type]} = "alarm" ]] && \
             [[ ${zcon[$i+$CurrentValue]} = "0" ]] && \
             [[ ${zcon[$i+$DayMode]} = "on" ]] && \
             [[ ${mode} = "Day mode" ]]
                     then zcon[$i+$Triggered]="true"; alarm='Active !'
          fi
      # test for Night mode alarms....
      # Check zone is an alarm and circuit is active and zone is enabled in Night mode and the system is in Night mode...
          if [[ ${zcon[$i+$Type]} = "alarm" ]] && \
             [[ ${zcon[$i+$CurrentValue]} = "0" ]] && \
             [[ ${zcon[$i+$NightMode]} = "on" ]] && \
             [[ ${mode} = "Night mode" ]]
                     then zcon[$i+$Triggered]="true"; alarm='Active !'
          fi
          i=$(( i + 9 ))
      done
      changed=""                                                                          # reset the flag

      if [[ "$alarm" = "Active !" ]] && [[ -z "$(pgrep alarm_active.sh)" ]]; then         # check sounder process NOT already running  ...
         # falls through here if we have an active alarm zone and the alarm has NOT already been triggered ...
         if [ -n "$(pgrep chimes_active.sh)" ]; then                                      # are the chimes sounding ? ...
             pkill -P $(pgrep chimes_active.sh)                                           # ... kill them as we need exclusive access to the sound bomb port
         fi
         alm_on                                                                           # Trigger / re-trigger (also updates status file)
      fi
}

check_for_chime_condition()
#################################################################################################################################
#                                                                                                                               #
# Execution is directed here when an alarm zone has changed state. So this code decides if the chimes need to sound.            #
#                                                                                                                               #
#################################################################################################################################
{   if [[ ${alarm} = "Set" ]] || [[ ${alarm} = "Timeout !" ]]; then           # if the alarm is already active, just cancel
      flag="false"
      maxval=${#zcon[@]}; (( maxval-- )); i=0                                 # setup scan through all defined alarm zones
      while [ $i -le "$maxval" ]; do                                          # only testing configured zones
      # test for Chimes....
      # Check zone is an alarm and circuit is currently active and circuit was previously inactive and zone has chimes enabled...
          if [[ ${zcon[$i+$Type]} = "alarm" ]] && \
             [[ ${zcon[$i+$CurrentValue]} = "0" ]] && \
             [[ ${zcon[$i+$PreviousValue]} = "1" ]] && \
             [[ ${zcon[$i+$Chimes]} = "on" ]]; then
                 flag="true"
          fi
          i=$(( i + 9 ))
      done
# if one or more of the scanned zones needs a chime, and the alarm isn't already using the port, and the chimes aren't already using the port...
      if [[ $flag = "true" ]] && \
         [[ -z "$(pgrep alarm_active.sh)" ]] && \
         [[ -z "$(pgrep chimes_active.sh)" ]] ; then
                 tmp=${CURRTIME}",alarm,raspi,door chime"
                 echo $tmp >> $LOGFILE                                         # log the event
                 echo $tmp                                                     # tell the user
                 /var/www/Scripts/chimes_active.sh "${tmp}" &                  # start alarm background process
                 disown                                                        # suppress messages from shell
      fi
    fi
}
 
alarm_diag()
#################################################################################################################################
#                                                                                                                               #
# Alarm Circuit diagnostic routine. Prints alarm data array to console.                                                         #
#                                                                                                                               #
#     ** Development use only **                                                                                                #
#    This routine slows down the scan of the alarm inputs, so should never be left running on a live system.                    #
#                                                                                                                               #
#################################################################################################################################
{     clear
      printf %s"-----|--------|--------------------|-----|-------|--------|---------|-------|---------|------|--------|------\n"
      printf "Zone | Type   | Name               | Day | Night | Chimes | Circuit | Anode | Cathode | Prev | Status | Trig\n"
      printf %s"-----|--------|--------------------|-----|-------|--------|---------|-------|---------|------|--------|------\n"
      maxval=${#zcon[@]}                                                                  # number of defined alarm zones
      (( maxval-- ))                                                                      # bump down because the array starts at zero
      i=0                                                                                 # array index 
      while [ $i -le "$maxval" ]; do                                                      # print all configured zones
          printf "%3s  | %6s | %18s | %3s | %5s | %6s | %7s | %5s | %7s | %4s | %6s | %s\n" $((i/9)) "${zcon[$i+$Type]}" "${zcon[$i+$Name]}" \
                           ${zcon[$i+$DayMode]} ${zcon[$i+$NightMode]} ${zcon[$i+$Chimes]} \
                           ${zcon[$i+$Circuit]} ${anode[${zcon[$i+$Circuit]}-1]} ${cathode[${zcon[$i+$Circuit]}-1]} \
                           ${zcon[$i+$PreviousValue]} ${zcon[$i+$CurrentValue]} ${zcon[$i+$Triggered]}
          i=$(( i + 9 ))
      done
      printf %s"-----|--------|--------------------|-----|-------|--------|---------|-------|---------|------|--------|------\n"
      printf "\n%s\n" "${changed}"
      sleep 0.5s
}

crontab_diag()
#################################################################################################################################
#                                                                                                                               #
# Crontab diagnostic routine. Prints crontab data array to console.                                                             #
#                                                                                                                               #
#     ** Development use only **                                                                                                #
#    This routine slows down the scan of the alarm inputs, so should never be left running on a live system.                    #
#                                                                                                                               #
#################################################################################################################################
{     clear
      printf %s"----|-------------------------------------|------|-------|---------|-------|---------|------------|-----\n"
      printf "Job | Header                              | Mins | Hours | MnthDay | Month | WeekDay | Command    | Num \n"
      printf %s"----|-------------------------------------|------|-------|---------|-------|---------|------------|-----\n"
      maxval=${#cron[@]}                                                                  # number of defined scheduled tasks
      (( maxval-- ))                                                                      # bump down because the array starts at zero
      i=0                                                                                 # array index 
      while [ $i -le "$maxval" ]; do                                                      # print all scheduled tasks
          printf "%-3s | %-35s |  %-3s |  %-4s |    %-4s |  %-4s |    %-4s | %-10s | %-3s \n" $((i/9)) "${cron[$i+cron_headr]}" "${cron[$i+cron_mins]}" "${cron[$i+cron_hours]}" \
                               "${cron[$i+cron_dom]}" "${cron[$i+cron_mnth]}" "${cron[$i+cron_wday]}" "${cron[$i+cron_cmnd]}" "${cron[$i+cron_status]}"
          i=$(( i + 8 ))
      done
      printf %s"----|-------------------------------------|------|-------|---------|-------|---------|------------|-----\n"
      sleep 0.5s
}

rcon_diag()
#################################################################################################################################
#                                                                                                                               #
# Radio Control diagnostic routine. Prints rcon data array to console.                                                          #
#                                                                                                                               #
#     ** Development use only **                                                                                                #
#    This routine slows down the scan of the alarm inputs, so should never be left running on a live system.                    #
#                                                                                                                               #
#################################################################################################################################
{     clear
      printf %s"------|-----------|----------------|---------|---------|--------|--------------|---------|\n"
      printf   "Array | Header    | Name           | Address | Channel | Status | Alarm action | HB type |\n"
      printf %s"------|-----------|----------------|---------|---------|--------|--------------|---------|\n"

      maxval=${#rcon[@]}                                                                  # number of defined Radio Control circuits
      (( maxval-- ))                                                                      # bump down because the array starts at zero
      i=0                                                                                 # array index 
      while [ $i -le "$maxval" ]; do                                                      # print all configured Radio Control circuits
          printf "%-6s|%-11s|%-16s|    %-5s|    %-5s|  %-6s|  %-12s| %-8s|\n" "$i" "${rcon[$i+rcon_header]}" \
                 "${rcon[$i+rcon_name]}" "${rcon[$i+rcon_address]}" "${rcon[$i+rcon_channel]}" "${rcon[$i+rcon_status]}" \
                 "${rcon[$i+rcon_alrm_action]}" "${rcon[$i+rcon_HK_type]}"
          i=$(( i + 7 )) 
      done
      printf %s"------|-----------|----------------|---------|---------|--------|--------------|---------|\n"
      sleep 0.5s
}

user_diag()
#################################################################################################################################
#                                                                                                                               #
# User account diagnostic routine. Prints user data array to console.                                                           #
#                                                                                                                               #
#     ** Development use only **                                                                                                #
#    This routine slows down the scan of the alarm inputs, so should never be left running on a live system.                    #
#                                                                                                                               #
#################################################################################################################################
{   clear
    printf %s"----|----------|--------------------------------------------------------------|------------------------|------------------------------------------\n"
      printf "Num | Name     | Password hash                                                | email                  | OTAC\n"
    printf %s"----|----------|--------------------------------------------------------------|------------------------|------------------------------------------\n"
      maxval=${#user[@]}                                                                  # number of defined user accounts
      (( maxval-- ))                                                                      # bump down because the array starts at zero
      i=0                                                                                 # array index 
      while [ $i -le "$maxval" ]; do                                                      # print all user data
          printf "%3s | %8s | %60s |%23s | %40s\n" $((i/4)) "${user[$i+user_name]}" "${user[$i+user_pword]}" "${user[$i+user_email]}" "${user[$i+user_OTAC]}"
          i=$(( i + 4 )) 
      done
    printf %s"----|----------|--------------------------------------------------------------|------------------------|------------------------------------------\n"
      sleep 0.5s
}

radiator_diag()
#################################################################################################################################
#                                                                                                                               #
# Radiator diagnostic routine. Prints user data array to console.                                                               #
#                                                                                                                               #
#     ** Development use only **                                                                                                #
#    This routine slows down the scan of the alarm inputs, so should never be left running on a live system.                    #
#                                                                                                                               #
#################################################################################################################################
{   clear
    printf %s"----|--------------|--------------|---------|--------|----------|---------\n"
      printf "Num | Header       | Name         | Address | Status | Hi value | Lo value\n"
    printf %s"----|--------------|--------------|---------|--------|----------|---------\n"
    maxval=${#rdtr[@]}                                                                  # number of defined Radiators
    (( maxval-- ))                                                                      # bump down because the array starts at zero
    i=0                                                                                 # array index 
    while [ $i -le "$maxval" ]; do                                                      # print all array data
        printf "%3s | %12s | %12s | %7s | %6s | %8s | %8s \n" $((i/6)) "${rdtr[$i+rdtr_hdr]}" "${rdtr[$i+rdtr_name]}" "${rdtr[$i+rdtr_address]}" \
                     "${rdtr[$i+rdtr_status]}" "${rdtr[$i+rdtr_hi]}" "${rdtr[$i+rdtr_lo]}"
        i=$(( i + 6 )) 
    done
    printf %s"----|--------------|--------------|---------|--------|----------|---------\n"
    sleep 0.5s
}

# ...end of function definitions.

#################################################################################################################################
#                                                                                                                               #
# Start initialising the machine...                                                                                             #
#                                                                                                                               #
#################################################################################################################################

CURRTIME=`date "+%H:%M:%S"`                                 # excel format
LOGFILE="/var/www/logs/"`date +%d-%m-%Y`".csv"              # name derived from date

# Check if we are on a RHEL virtual machine....
tmp=$(cat /proc/cpuinfo | grep 'model name' | awk '{print $4}')
if [[ "$tmp" = "QEMU" ]]; then hardware='QEMU virtual machine'; fi

# Now check if we are on any sort of Raspberry Pi....
tmp=$(cat /proc/cpuinfo | grep Revision | awk '{print $3}')
      case "${tmp}" in
           "0002" | "0003")
             hardware='Raspberry Pi Rev 1.0'
             InitPorts;;                                    # we are on a PI so initialise the ports
           "000d" | "000e" | "000f" | "0010")
             hardware='Raspberry Pi Rev 2.0'
             InitPorts;;                                    # we are on a PI so initialise the ports
           "a01041")
             hardware='Raspberry Pi 2'
             InitPorts;;                                    # we are on a PI so initialise the ports
           "a02082")
             hardware='Raspberry Pi 3'
             InitPorts;;                                    # we are on a PI so initialise the ports
       esac

tmp=${CURRTIME}",alarm,raspi,GPIO ports initialised for "${hardware}
echo $tmp >> $LOGFILE                                       # log the event
echo $tmp                                                   # tell the user

if [ -f /var/www/user.txt ]; then                           # if we have any users defined, load them to memory
  load_user_file
# echo 'User 0-'${lgns[0]}'-'${emails[0]}'-'${pwds[0]}      # DIAGNOSTIC
  tmp=${CURRTIME}",alarm,raspi,Loading user credentials."
  echo $tmp >> $LOGFILE                                     # log the event
  echo $tmp                                                 # tell the user
fi

if [ -f /var/www/data/status.txt ]; then                    # If we have the status from the previous session...
  load_status_file /var/www/data/status.txt                 # ...load it
  tmp=${CURRTIME}",alarm,raspi,Settings: Restoring last session."
  echo $tmp >> $LOGFILE                                     # log the event
  echo $tmp                                                 # tell the user
  title="System restart"                                    # Send email reporting the restart
  eMail "$title"
  CheckIP                                                   # can't call this until after we have loaded the old IP
elif [ -f /var/www/default.txt ]; then                      # Failing that, do we have user defaults...
  load_status_file /var/www/default.txt                     # ...load 'em
  tmp=${CURRTIME}",alarm,raspi,Settings: Loading user defaults"
  echo $tmp >> $LOGFILE                                     # log the event
  echo $tmp                                                 # tell the user
  title="System restart"                                    # Send email reporting the restart
  eMail "$title"
  CheckIP                                                   # can't call this until after we have loaded the old IP
else
  load_status_file /var/www/factory.txt                     # No session data, or user defaults available, so fail back to factory defaults.
                                                            # Note: No valid email credentials, so can't send email
  tmp=${CURRTIME}",alarm,raspi,Settings: Loading factory defaults"
  echo $tmp >> $LOGFILE                                     # log the event
  echo $tmp                                                 # tell the user
  CheckIP                                                   # We've got no idea what the old IP was, so this will try to send an email.
                                                            # but we don't have any existing email server details either, so will cause a  # warning.
fi
CreateTaskList                                              # assemble the list of all the task strings
WriteCronJobs                                               # synchronise cron jobs array with system CRONTAB file

write_status_file /var/www/data/status.txt                  # This shouldn't be needed in normal operation, but during dev work,
                                                            # a system crash will leave the system without a status file, which in
                                                            # turn means the web page won't load. So this ensures a system restart
                                                            # also restarts the web page.
if [ -f /var/www/data/input.txt ]; then                     # ... and while we are at it, the most likely reason for a system crash
  rm /var/www/data/input.txt                                # is an incorrectly formatted message from the web page, so nuke it.
fi

#################################################################################################################################
#                                                                                                                               #
# Check for any commands from the web page.                                                                                     #
# Commands are passed in the file /var/www/data/input.txt. The file is deleted as soon as the command is executed.              #
#                                                                                                                               #
#################################################################################################################################

while :
do
CURRTIME=`date "+%H:%M:%S"`                                                # excel format
LOGFILE="/var/www/logs/"`date +%d-%m-%Y`".csv"                             # name derived from date
     if [ -r /var/www/data/input.txt ];
        then
# So there can be a whacky timing issue here. If Homekit has Scenes configured, its possible we will get 10 or 20 commands
# arriving simultaneously. So we need to ensure the service doesn't read and lock the input file until Homekit has
# finished writing all the commands. Pausing for a tenth of a second seems to work ok, but give it 2 tenths for luck...
           sleep 0.2s 
           while read info
             do
#              echo $info                                                  # Diagnostic
               OLD_IFS="$IFS"                                              # new mechanism
               IFS=":"                                                     # split the command on ':' - spaces are allowed
               PARAMS=( $info )
               IFS="$OLD_IFS"
#              declare -p PARAMS                                           # DIAGNOSTIC - echo parameters being passed from web pages
               case "${PARAMS[2]}" in
                 "BannedIP")
                   tmp=${CURRTIME}",fail2ban,raspi,Banned IP:,"${PARAMS[3]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp
                   title="Banned IP: "${PARAMS[3]}
                   eMail "$title"
                   ;;                                                      # tell the user
                 "UnBannedIP")
                   tmp=${CURRTIME}",fail2ban,raspi,UnBanned IP:,"${PARAMS[3]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp;;                                             # tell the user
                 "logon")
                   user[${PARAMS[3]}*4+3]=${PARAMS[4]}                     # Update otac
                   WriteUsers                                              # and copy to file
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp;;                                             # tell the user
                 "failed logon" | "logoff")                                # either way - just log it
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp;;                                             # tell the user
                 "HomeBridge")
                 # handles all the HomeBridge options
                   if [ "${PARAMS[3]}" == "Export" ]; then 
                      tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}",HomeBridge export"
                      echo $tmp >> $LOGFILE                                   # log the event
                      echo $tmp                                               # tell the user                  
                      Homebridge_Export
                   fi
                   if [ "${PARAMS[3]}" == "Restart" ]; then 
                      tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}",HomeBridge restart"
                      echo $tmp >> $LOGFILE                                   # log the event
                      echo $tmp                                               # tell the user
                      printf "Restarting HAP-NodeJS\n"
                      sudo service homebridge restart
                   fi;;
                 "mode")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}
                   echo $tmp >> $LOGFILE                                      # log the event
                   echo $tmp                                                  # tell the user
                   if [[ $mode != ${PARAMS[3]} ]]; then
                   # falls through here if we need to change the alarm mode...
                       mode=${PARAMS[3]}                                       # set new mode
                       check_for_alarm_condition                               # check if this causes an alarm
                       sw1_old="1" ; sw2_old="1" ; sw3_old="1" ; sw4_old="1"   # reset zone states NB this can trigger
                       sw5_old="1" ; sw6_old="1" ; sw7_old="1" ; sw8_old="1"   # the alarm if any zone is open
#                      alarm_tests                                             # check if this causes an alarm
                       title="Alarm system: "${PARAMS[3]}
                       eMail "$title"
                   else
                   # falls through here if the alarm is already in the selected mode.
                       tmp='Alarm system already in '${PARAMS[3]}' - email suppressed.'
                       echo $tmp >> $LOGFILE                                   # log the event
                       echo $tmp                                               # tell the user
                   fi;;
                 "timeout")
                   # this command is created by a background task and not the web page
                   CURRTIME=`date "+%H:%M:%S"`                              # excel format
                   tmp=${CURRTIME}",alarm,raspi,Alarm timeout"
                   echo $tmp >> $LOGFILE                                    # log the event
                   echo $tmp                                                # tell the user (like he needs to know!)
                   rm -f /var/www/data/status.txt                           # normally done by the web page, but this time
                                                                            # has to be done through BASH
                   alarm="Timed out !"
                   echo "0" > /sys/class/gpio/gpio11/value                  # Set bell port inactive
                   echo "0" > /sys/class/gpio/gpio8/value                   # Set Strobe port inactive
                   echo "0" > /sys/class/gpio/gpio7/value                   # Sound Bomb inactive

                   check_for_alarm_condition                                # tamper zones can still cause a re-trigger
                   title="Alarm system: TIMEOUT"
                   eMail "$title";;
                 "app setup")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}","${PARAMS[4]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   SETUP_location=${PARAMS[3]}
                   SETUP_duration=${PARAMS[4]};;
                 "email setup")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}","${PARAMS[4]}","
                   tmp=$tmp${PARAMS[5]}",********"
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   # delete any old copies of the file...
                   [ -e /etc/postfix/sasl_passwd ] && rm /etc/postfix/sasl_passwd
                   printf "[%s]:%s    %s:%s" "${PARAMS[3]}" "${PARAMS[4]}" "${PARAMS[5]}" "${PARAMS[6]}" >>/etc/postfix/sasl_passwd
                   sudo chmod 400 /etc/postfix/sasl_passwd
                   sudo postmap /etc/postfix/sasl_passwd
#                  rm /etc/postfix/sasl_passwd                             # tidy up
                   sudo service postfix restart &
                   EMAIL_server=${PARAMS[3]}                               # update variables in RAM
                   EMAIL_port=${PARAMS[4]}
                   EMAIL_sender=${PARAMS[5]}                               # update value in memory
                   if [ "${PARAMS[6]}" != "********" ]; then               # has the password field changed ?
                      tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}",password changed"
                      echo $tmp >> $LOGFILE                                # log the event
                      echo $tmp                                            # tell the user
                      EMAIL_password=${PARAMS[6]}                          # ...update password in memory
                      user[1]=${PARAMS[6]}                                 # ...update password in array
                   fi ;;
                 "save user defaults")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   write_status_file /var/www/default.txt;;                # Save current user defaults to file
                 "load user defaults")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   load_status_file /var/www/default.txt                   # Load user defaults from file
                   CreateTaskList                                          # assemble the list of all the task strings
                   CheckIP                                                 # refresh hardware details
                   check_for_alarm_condition;;                             # check if this causes an alarm
                 "load factory defaults")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   load_status_file /var/www/factory.txt                   # Load factory defaults from file
                   CreateTaskList                                          # assemble the list of all the task strings
                   CheckIP                                                 # refresh hardware details
                   check_for_alarm_condition;;                             # check if this causes an alarm

#################################################################################################################################
#                                                                                                                               #
# Handle commands passed from the User admin web page.                                                                          #
#                                                                                                                               #
#################################################################################################################################
                 "user cfg")                                               # Edits existing, or adds new users...
                   userNum=$((${PARAMS[3]}+1))                             # bump userNum up - first 4 elements are reserved for the email account
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}","
                   tmp=$tmp${PARAMS[4]}","${PARAMS[5]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   if [[ "$((${#user[@]}/4))" -eq "${userNum}" ]]; then
                     # we are adding a new user, password hasn't been specified, so use default...
                     user[${userNum}*4+user_pword]=${DefaultPassword}      # Create and set password element
                     user[${userNum}*4+user_OTAC]='blah'                   # leaving a field blank will mess up the array size, so put in any old rubbish
                   fi
                   user[${userNum}*4]=${PARAMS[4]}                         # Update name
                   user[${userNum}*4+user_email]=${PARAMS[5]}              # Update email
                   WriteUsers                                              # write changes to disk
                   ;;
                 "user pwd")                                               # Change user password...
                   userNum=$((${PARAMS[3]}+1))                             # bump userNum up - first 4 elements are reserved for the email account
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}","
                   tmp=$tmp${PARAMS[4]}","${PARAMS[5]},"********"
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   user[${userNum}*4]=${PARAMS[4]}                         # Update name
                   user[${userNum}*4+user_email]=${PARAMS[5]}              # Update email
                   user[${userNum}*4+user_pword]=${PARAMS[6]}              # Update password
                   WriteUsers                                              # write changes to disk
                   ;;
                 "user del")                                               # Delete existing users...
                   userNum=$((${PARAMS[3]}+1))                             # bump userNum up - first 4 elements are reserved for the email account
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${user[$((${userNum}*4))]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   user=("${user[@]:0:$((${userNum}*4))}" "${user[@]:$(($((${userNum}*4)) + 4))}")
                   WriteUsers                                              # write changes to disk
                   ;;
                 "reset")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   if [ -n "$(pgrep alarm_active.sh)" ]; then              # is the alarm sounding ? ...
                     pkill -P $(pgrep alarm_active.sh)                     # ... kill it - and kill any pending timeout task and any sleep sub processes
                   fi
                   echo "0" > /sys/class/gpio/gpio11/value                 # Set bell port inactive
                   echo "0" > /sys/class/gpio/gpio8/value                  # Set Strobe port inactive
                   echo "0" > /sys/class/gpio/gpio7/value                  # Sound Bomb inactive

                   mode="Standby"
                   alarm="Set"                                             # clear any alarm condition
                   maxval=${#zcon[@]}                                      # number of defined alarm zones
                   (( maxval-- ))                                          # bump down because the array starts at zero
                   i=0                                                     # array index 
                   while [ $i -le "$maxval" ]; do
                      zcon[$i+Triggered]="false"                           # clear any triggered zones
                      zcon[$i+PreviousValue]="false"                       # reset zone states
                      i=$(( i + 9 ))
                   done
                                                                           # Note: tamper zones should still cause a trigger so...
                   check_for_alarm_condition                               # ...check if we have a re-trigger
                   title="Alarm system: Reset"
                   eMail "$title";;
                 "test bell")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}
                   echo $tmp >> $LOGFILE                                        # log the event
                   echo $tmp                                                    # tell the user
                   if [ ${running_on_RasPi} == "true" ]; then
                   # Only run if we are on a pi. This prevents non pi platforms from flooding the console with errors.
                      echo "1" > /sys/class/gpio/gpio11/value                   # Set bell port active
                      # set up background task to cancel the test in 4 seconds
                      ( sleep 4
                        echo "0" > /sys/class/gpio/gpio11/value                 # Set bell port inactive
                        break )&
                   fi;;
                 "test strobe")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}
                   echo $tmp >> $LOGFILE                                        # log the event
                   echo $tmp                                                    # tell the user
                   if [ ${running_on_RasPi} == "true" ]; then
                   # Only run if we are on a pi. This prevents non pi platforms from flooding the console with errors.
                      echo "1" > /sys/class/gpio/gpio8/value                    # Set strobe port active
                      # set up background task to cancel the test in 5 secs
                      ( sleep 5
                        echo "0" > /sys/class/gpio/gpio8/value                  # Set strobe port inactive
                        break )&
                   fi;;
                 "test sounder")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}
                   echo $tmp >> $LOGFILE                                        # log the event
                   echo $tmp                                                    # tell the user
                   if [ ${running_on_RasPi} == "true" ]; then
                   # Only run if we are on a pi. This prevents non pi platforms from flooding the console with errors.
                      echo "1" > /sys/class/gpio/gpio7/value                    # Sound Bomb active
                      ( sleep 1s
                        echo "0" > /sys/class/gpio/gpio7/value                  # Sound Bomb inactive
                        break )&
                   fi;;

#################################################################################################################################
#
# Handle commands passed from the Automation web page.
#
#################################################################################################################################
                 "rcon swtch")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${rcon[${PARAMS[3]}*7+rcon_name]}","${PARAMS[4]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   rcon[${PARAMS[3]}*7+rcon_status]="${PARAMS[4]}"         # set array element to new string value
                   if [ "${PARAMS[4]}" == "On" ]; then
                       # format the command string for 'on'...
#                      printf -v tmp ' -y 1 0x08 0x%02X 0x%X1 \n' ${rcon[${PARAMS[3]}*7+rcon_address]} ${rcon[${PARAMS[3]}*7+rcon_channel]}
                       printf -v tmp ' -y 1 0x08 0x01 0x%02X 0x%02X 0x01 i \n' ${rcon[${PARAMS[3]}*7+rcon_address]} ${rcon[${PARAMS[3]}*7+rcon_channel]}
                   else
                       # format the command string for 'off'...
#                      printf -v tmp ' -y 1 0x08 0x%02X 0x%X0 \n' ${rcon[${PARAMS[3]}*7+rcon_address]} ${rcon[${PARAMS[3]}*7+2]}
                       printf -v tmp ' -y 1 0x08 0x01 0x%02X 0x%02X 0x00 i \n' ${rcon[${PARAMS[3]}*7+rcon_address]} ${rcon[${PARAMS[3]}*7+rcon_channel]}
                   fi
#                  echo $tmp                                               # DEBUG - view the I2C command
                   if [ ${running_on_RasPi} == "true" ]; then
                   # Only send I2C commands if we are on a pi. This prevents non pi platforms from flooding the console with errors.
                       i2cset $tmp                                         # send I2C command to PIC chip
                       sleep 0.3s                                          # give the PIC time to complete the transmission
                   fi;;
                 "rcon del")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}
                   echo $tmp >> $LOGFILE                                                      # log the event
                   echo $tmp                                                                  # tell the user
                   rcon=("${rcon[@]:0:$((${PARAMS[3]}*7))}" "${rcon[@]:$(($((${PARAMS[3]}*7)) + 7))}")
                   CreateTaskList;;                                                           # assemble the list of all the task strings
                 "rcon cfg")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}",remote config,"${PARAMS[3]}","${PARAMS[4]}","${PARAMS[5]}","${PARAMS[6]}","${PARAMS[7]}","${PARAMS[9]}
                   echo $tmp >> $LOGFILE                                                      # log the event
                   echo $tmp                                                                  # tell the user
                   rcon[${PARAMS[3]}*7+rcon_header]="${PARAMS[4]}"
                   rcon[${PARAMS[3]}*7+rcon_name]="${PARAMS[5]}"
                   rcon[${PARAMS[3]}*7+rcon_address]="${PARAMS[6]}"
                   rcon[${PARAMS[3]}*7+rcon_channel]="${PARAMS[7]}"
                   rcon[${PARAMS[3]}*7+rcon_status]="Off"                                     # default state for new device
                   rcon[${PARAMS[3]}*7+rcon_alrm_action]="${PARAMS[8]}"
                   rcon[${PARAMS[3]}*7+rcon_HK_type]="${PARAMS[9]}"
                   CreateTaskList;;                                                           # assemble the list of all the task strings
                 "zcon cfg")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}","
                   tmp=$tmp${PARAMS[4]}","${PARAMS[5]}","${PARAMS[6]}","${PARAMS[7]}","${PARAMS[8]}","${PARAMS[9]}
                   echo $tmp >> $LOGFILE                                                      # log the event
                   echo $tmp                                                                  # tell the user
                   zcon[${PARAMS[3]}*9]="${PARAMS[4]}"                                        # set zone type
                   zcon[${PARAMS[3]}*9+$Name]="${PARAMS[5]}"                                  # set zone name
                   zcon[${PARAMS[3]}*9+$DayMode]="${PARAMS[6]}"                               # set zone Day mode
                   zcon[${PARAMS[3]}*9+$NightMode]="${PARAMS[7]}"                             # set zone Night mode
                   zcon[${PARAMS[3]}*9+$Chimes]="${PARAMS[8]}"                                # set zone Chimes
                   zcon[${PARAMS[3]}*9+$Circuit]="${PARAMS[9]}"                               # set zone Circuit
                   zcon[${PARAMS[3]}*9+$Triggered]="false"                                    # create triggered element
                   zcon[${PARAMS[3]}*9+$PreviousValue]="0"                                    # create previous value element (0=open)

                   # We are going to read the circuit state and check the resulting status of the zone NOW !
                   # This means if the zone has entered a triggered state, the web page will be updated accordingly.

                   zcon[${PARAMS[3]}*9+$PreviousValue]=${zcon[${PARAMS[3]}*9+$CurrentValue]}  # record previous state
                   anod='/sys/class/gpio/gpio'${anode[${zcon[${PARAMS[3]}*9+5]}-1]}'/value'
                   cath='/sys/class/gpio/gpio'${cathode[${zcon[${PARAMS[3]}*9+5]}-1]}'/value'
                   echo "1" > ${anod}                                                         # activate the anode
                   zcon[${PARAMS[3]}*9+$CurrentValue]=$(cat $cath)                            # read and record the value on the input
                   echo "0" > ${anod}                                                         # deactivate the anode
                   check_for_alarm_condition                                                  # check if new status triggers any zones
                   ;;                                   
                 "zcon del")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   zcon=("${zcon[@]:0:$((${PARAMS[3]}*9))}" "${zcon[@]:$(($((${PARAMS[3]}*9)) + 9))}")
                   ;;
                 "Check ip")
                   CheckIP;;                                               # pass to subroutine to sort out

#################################################################################################################################
#
# Handle commands passed from the Radiator web page.
#
#################################################################################################################################
                 "rdtr swtch")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${rdtr[${PARAMS[3]}*6+rdtr_name]}" \
                           "radiator","${PARAMS[4]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   rdtr[${PARAMS[3]}*6+rdtr_status]="${PARAMS[4]}"         # set array element to new string value
                   if [ "${PARAMS[4]}" == "On" ]; then
                       # format the command string for 'on'...
                       printf -v tmp ' -y 1 0x08 0x02 0x%02X 0x00 0x01 i \n' ${rdtr[${PARAMS[3]}*6+rdtr_address]}
                   else
                       # format the command string for 'off'...
                       printf -v tmp ' -y 1 0x08 0x02 0x%02X 0x00 0x00 i \n' ${rdtr[${PARAMS[3]}*6+rdtr_address]}
                   fi
#                  echo $tmp                                               # DEBUG - view the I2C command
                   if [ ${running_on_RasPi} == "true" ]; then
                   # Only send I2C commands if we are on a pi. This prevents non pi platforms from flooding the console with errors.
                       i2cset $tmp                                         # send I2C command to PIC chip
                       sleep 0.6s                                          # give the PIC time to complete the transmission
                                                                           # Note: radiators take twice as long as switches to complete.
                   fi;;
                "rdtr cfg")                                               # Edits existing, or adds new users...
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}","
                   tmp=$tmp${PARAMS[4]}","${PARAMS[5]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   rdtr[${PARAMS[3]}*6+rdtr_headr]=${PARAMS[4]}            # Update header
                   rdtr[${PARAMS[3]}*6+rdtr_name]=${PARAMS[5]}             # Update name
                   rdtr[${PARAMS[3]}*6+rdtr_address]=${PARAMS[6]}          # Update address
                   rdtr[${PARAMS[3]}*6+rdtr_status]="off"                  # Reset status
                   rdtr[${PARAMS[3]}*6+rdtr_hi]=${PARAMS[7]}               # Update high value
                   rdtr[${PARAMS[3]}*6+rdtr_lo]=${PARAMS[8]}               # Update low value
                   CreateTaskList;;                                        # assemble the list of all the task strings
                  "rdtr del")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   rdtr=("${rdtr[@]:0:$((${PARAMS[3]}*6))}" "${rdtr[@]:$(($((${PARAMS[3]}*6)) + 6))}")
                   ;;
                       
#################################################################################################################################
#
# Handle commands passed from the Schedule web page.
#
#################################################################################################################################

                 "delete task")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   cron=("${cron[@]:0:$((${PARAMS[3]}*8))}" "${cron[@]:$(($((${PARAMS[3]}*8)) + 8))}")
                   WriteCronJobs;;                                         # synchronise cron jobs array with system CRONTAB file
                 "edit cron")
                   tmp=${CURRTIME}","${PARAMS[0]}","${PARAMS[1]}","${PARAMS[2]}","${PARAMS[3]}","${PARAMS[4]}","${PARAMS[5]}","
                   tmp+="${PARAMS[6]}","${PARAMS[7]}","${PARAMS[8]}","${PARAMS[9]}","${PARAMS[10]}","${PARAMS[11]}"
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp                                               # tell the user
                   cron[${PARAMS[3]}*8+cron_headr]="${PARAMS[4]}"
                   cron[${PARAMS[3]}*8+cron_mins]="${PARAMS[5]}"
                   cron[${PARAMS[3]}*8+cron_hours]="${PARAMS[6]}"
                   cron[${PARAMS[3]}*8+cron_dom]="${PARAMS[7]}"
                   cron[${PARAMS[3]}*8+cron_mnth]="${PARAMS[8]}"
                   cron[${PARAMS[3]}*8+cron_wday]="${PARAMS[9]}"
                   cron[${PARAMS[3]}*8+cron_cmnd]="${PARAMS[10]}"
                   cron[${PARAMS[3]}*8+cron_status]="${PARAMS[11]}"
                   WriteCronJobs;;                                         # synchronise cron jobs array with system CRONTAB file
                *)
                   tmp=${CURRTIME}","${PARAMS[0]}",unknown command,"$info
                   echo $tmp >> $LOGFILE                                   # log the event
                   echo $tmp;;                                             # tell the user
               esac
               write_status_file /var/www/data/status.txt                  # ...then report back to the web page
             done </var/www/data/input.txt
         rm /var/www/data/input.txt
      fi

      if [ ${running_on_RasPi} == "true" ]; then
      # Only scan input ports if we are on a pi. This prevents non pi platforms from flooding the console with errors.

#################################################################################################################################
#                                                                                                                               #
# Read the status of the input circuits and flag any changes.                                                                   #
#                                                                                                                               #
# Scans all the circuits configured as alarm zones.                                                                             #
# Note:-                                                                                                                        #
# Input circuits are active low because...                                                                                      #
#     1) the door switch opens,                                                                                                 #
#     2) the alarm circuit breaks,                                                                                              #
#     3) the Opto Isolator LED turns off,                                                                                       #
#     4) the Opto Isolator photo transistor stops pulling the input high,                                                       #
#     5) a resistor pulls the input low.                                                                                        #
#                                                                                                                               #
#################################################################################################################################

           maxval=${#zcon[@]}                                                              # number of defined alarm zones
           (( maxval-- ))                                                                  # bump down because the array starts at zero
           i=0                                                                             # array index 
           while [ $i -le "$maxval" ]; do
               zcon[$i+$PreviousValue]=${zcon[$i+$CurrentValue]}                           # record previous state
               anod='/sys/class/gpio/gpio'${anode[${zcon[$i+5]}-1]}'/value'
               cath='/sys/class/gpio/gpio'${cathode[${zcon[$i+5]}-1]}'/value'
               echo "1" > ${anod}                                                          # activate the anode
               zcon[$i+$CurrentValue]=$(cat $cath)                                         # read and record the value on the input
               echo "0" > ${anod}                                                          # deactivate the anode
               if [[ "${zcon[$i+$PreviousValue]}" -ne "${zcon[$i+$CurrentValue]}" ]]; then
                  if [[ ${zcon[$i+$CurrentValue]} = "0" ]]; then
                     changed=${zcon[$i+$Name]}" open"
                  else
                     changed=${zcon[$i+$Name]}" closed"
                  fi
               fi
               i=$(( i + 9 ))
           done 

           if [ -n "$changed" ]; then                                                      # if we have a value for changed, then a circuit has changed
                                                                                           # since the last scan and we need to dig deeper...
              tmp=${CURRTIME}",alarm,raspi,"${changed}
              echo $tmp >> $LOGFILE                                                        # log the event
              echo $tmp                                                                    # tell the user
              check_for_alarm_condition                                                    # decide if new state of input circuits causes an alarm
              check_for_chime_condition                                                    # decide if new state of input circuits causes a chime
              write_status_file /var/www/data/status.txt
           fi
      fi

# The following 4 lines can be used to invoke the diagnostic routines. These show the contents of the data arrays in real time on the system console.
# Only one diagnostic should be run at a time. Do not leave any of the diagnostics running on a live device, as they slow down the operation
# of the code.

#     alarm_diag                                                                      # DIAGNOSTIC - always comment this line out on a live system
#     crontab_diag                                                                    # DIAGNOSTIC - always comment this line out on a live system
#     rcon_diag                                                                       # DIAGNOSTIC - always comment this line out on a live system
#     user_diag                                                                       # DIAGNOSTIC - always comment this line out on a live system
#     radiator_diag                                                                   # DIAGNOSTIC - always comment this line out on a live system

done

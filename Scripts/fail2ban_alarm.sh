#!/bin/bash
# Links Fail2Ban actions to Alarm System daemon
#
# Requires one argument, one of the following:
#  start
#  stop
#  ban
#  unban
#
# Optional second argument: IP for ban/unban

# Display usage information
function show_usage {
  echo "Usage: $0 action <ip>"
  echo "Where action is start, stop, ban, unban"
  echo "and ip is optional passed to ban, unban"
  exit
}

# Check for script arguments
if [ $# -lt 1 ]
then
  show_usage
fi

# Take action depending on argument
if [ "$1" = 'start' ]
then
  message='Fail2ban started.'
  echo $message
elif [ "$1" = 'stop' ]
then
  message='Fail2ban stopped.'
  echo $message
elif [ "$1" = 'ban' ]
then
  message=$([ "$2" != '' ] && echo "(fail2ban):(RasPi):BannedIP:$2" || echo 'Banned an IP.' )
  echo $message >> /var/www/data/input.txt
elif [ "$1" = 'unban' ]
then
  message=$([ "$2" != '' ] && echo "(fail2ban):(RasPi):UnBannedIP:$2" || echo 'Unbanned an IP.' )
  echo $message >> /var/www/data/input.txt
else
  show_usage
fi

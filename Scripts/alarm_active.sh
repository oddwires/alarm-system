#!/bin/sh
#################################################################################################################################
#                                                                                                                               #
# Background task started when the alarm activates.                                                                             #
# Running this as a separate task means it can be killed by a reset command. This prevents the scenario where an alarm          #
# condition has been manual reset - but the timeout email still arives a few minutes later.                                     #
#                                                                                                                               #
#################################################################################################################################

tmp=5                                                                                  # default case - ensures something will happen
tmp=$1                                                                                 # grab the command line argument
#echo "Alarm active - duration "$tmp" seconds"                                         # DIAGNOSTIC

echo "1" > /sys/class/gpio/gpio11/value                                                # Set bell port active
echo "1" > /sys/class/gpio/gpio8/value                                                 # Set strobe port active
echo "1" > /sys/class/gpio/gpio7/value                                                # Sound bomb on
 
sleep ${tmp}                                                                           # setup timeout job
echo "(alarm):(RasPi):timeout" >>/var/www/data/input.txt                               # send timeout command back to alarm service
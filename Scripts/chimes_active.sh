#!/bin/sh
#################################################################################################################################
#                                                                                                                               #
# Background task started when the chimes activate.                                                                             #
# Running this as a separate task means it can be killed by an alarm condition. This prevents the scenario where an alarm       #
# has triggered, but the Sound Bomb port gets set to an in-active state by the chimes task completing.                          #
#                                                                                                                               #
#################################################################################################################################

i=0
while [ $i -lt 6 ]; do
     echo "1" > /sys/class/gpio/gpio7/value                 # Sound Bomb active
     sleep .005s
     echo "0" > /sys/class/gpio/gpio7/value                 # Sound Bomb inactive
     sleep .05s
     i=$(($i+1))
done                                                        # run as background proce

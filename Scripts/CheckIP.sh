#!/bin/bash
#################################################################################################################################
#                                                                                                                               #
# Script to rattle down a list of http sites to find the current router external IP address.                                    #
# Not all sites will always respond immediately, so a timeout is used to prevent the script locking up.                         #
# The first site to respond causes the script to exit with the returned value.                                                  #
#                                                                                                                               #
# The list may require periodic maintenance, but all sites were responding on 26/12/2018.                                       #
#                                                                                                                               #
#################################################################################################################################

    timeout=2                             # seconds to wait for a reply before trying next server

    list_of_http_sites=(
        checkip.amazonaws.com
        alma.ch/myip.cgi
        api.infoip.io/ip
        api.ipify.org
        bot.whatismyipaddress.com
        canhazip.com
        icanhazip.com
        ident.me
        ipecho.net/plain
        ipinfo.io/ip
        ip.tyk.nu
        l2.io/ip
        smart-ip.net/myip
        tnx.nl/ip
        wgetip.com
        whatismyip.akamai.com
        ipv4.wtfismyip.com/text
    )

    for site in "${list_of_http_sites[@]}"; do
        ip=""
        printf "Contacting site: "$site"\n"
        ip=$(curl -s --max-time $timeout $site)
        if [ -n "$ip" ]; then
            printf "task:"$site":check ip:"$ip"\n" >> /var/www/data/input.txt
            exit                                                                # Success
        fi
    done
    printf "task:"$site":check ip:time out\n" >> /var/www/data/input.txt        # Fail

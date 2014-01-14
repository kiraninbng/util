#!/bin/bash

PORT=80
INTERVAL=1
DO_RDNS=0

while getopts "p:i:r" opt; do
    case "$opt" in
    p)  
		PORT=$OPTARG
        ;;
	i)	
		INTERVAL=$OPTARG
        ;;
	r)	DO_RDNS=1
        ;;
	h)	echo "Usage - ./hsws-check-port-connections.sh -p 80 -i 1 -r"
		;;
    esac
done

IP_DICT=
while [ 1 ]
do
	echo "Last updated on `date`.."
	netstat -an | grep -v unix | grep ESTABLISHED | tr -s ' ' | cut -d' ' -f5 | grep :*\.$PORT$ | sed "s/:*\.[0-9]*$//g" | sort | uniq -c | sed 0d |\
	while read ip
	do
		COUNT=$(echo $ip | awk '{print $1}')
		IP=$(echo $ip | awk '{print $2}')
		RDNS=
		if [ $DO_RDNS -eq 1 ]
		then
			RDNS=$(dig +noall +answer +short -x $IP);
			echo "$COUNT-------------$IP-------------$RDNS";
		else
			echo "$COUNT-------------$IP";
		fi
	done

	sleep $INTERVAL;
done
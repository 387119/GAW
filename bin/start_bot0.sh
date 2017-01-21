#!/bin/bash

USER=aurum911
while true
do
	echo "start bot0 on user $USER"
	php ../bots/bot0.php "$USER" >"../log/${USER}.log"
	echo "waiting 2 mins"
	sleep 120
	echo "restarting..."
done

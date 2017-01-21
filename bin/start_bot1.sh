#!/bin/bash

IFS=$'\n'

for U in $(echo "select user_name from bot1_work order by user_name;" | psql -d gaw -U gaw -P format=unaligned -P footer=off -P tuples_only=on gaw)
do
        ONLINESH=$(ps ax | grep php | grep "$U" | grep -v grep | wc -l)
        if [ "$ONLINESH" -eq "0" ]
	then
		php ../bots/bot1.php "$U" >>"../log/${U}.log" &
		sleep 5
	fi
done


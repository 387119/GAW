#!/bin/bash

DIR="/var/lib/dokuwiki/data/pages"
FF="../tmp/bot4.sql"
echo -n >$FF
IFS=$'\n'
for i in $(cat $DIR/bots/bot1_users_*.txt | grep "^|" | grep "<todo #" | awk -F'|' '{print $3}')
do
	USER=$i
	echo "insert into bot4 (user_name,status) values ('$USER',1) on conflict (user_name) do update set status=1 where bot4.status=0;" >>$FF
done
for i in $(cat $DIR/bots/bot1_users_*.txt | grep "^|" | grep "<todo>-" | awk -F'|' '{print $3}')
do
	USER=$i
	echo "insert into bot4 (user_name,status) values ('$USER',0) on conflict (user_name) do update set status=0 where bot4.status=1;" >>$FF
done
psql -d gaw -U gaw -f $FF

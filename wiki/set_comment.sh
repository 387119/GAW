#!/bin/bash

USER=$1
COMMENT=$2
OUT=$COMMENT

DIR="/var/lib/dokuwiki/data/pages"

#FILE=$(grep -l "|${USER}|" ${DIR}/bots/bot1_users_*.txt)
#cat users/vasjamba.txt | grep Cat | sed -e 's/RES_COUNT}}*|/RES_COUNT}}qwer|/'
#if [ -e "$FILE" ]
#then
X=$(grep -e "|${USER}|" ${DIR}/bots/bot1_users_*.txt | wc -l )
if [ "$X" -eq "0" ]
then
	echo "set_comment: user ${USER} not found to set comment '$OUT'"
	exit
fi
X=0
Y=0
while [ "$X" -eq "0" ]
do
	sed -i "s/^|{{fa>\(.*\)|${USER}|\(.*\)|.*|$/|{{fa>\1|${USER}|\2|${OUT}|/g" ${DIR}/bots/bot1_users_*.txt
	X=$(grep -e "|${USER}|.*|${OUT}|" ${DIR}/bots/bot1_users_*.txt | wc -l )
	Y=$(($Y + 1))
	echo $Y
	if [ "$Y" -eq "5" ]
	then
		echo "set_comment: cannot set comment '$OUT' for user ${USER}"
		exit
	fi
	sleep 1
done
	#sed -i "s/${USER}|\(.*\)RES_COUNT}}[0-9a-zа-я \/]*|/${USER}|\1RES_COUNT}}${OUT}|/" "$FILE"
#fi


#!/bin/bash

USER=$1
RES_COUNT=$2
RES_PERCENT=$3 
OUT="$RES_COUNT \/ $RES_PERCENT"

DIR="/var/lib/dokuwiki/data/pages"

#FILE=$(grep -l "|${USER}|" ${DIR}/.txt)
#cat users/vasjamba.txt | grep Cat | sed -e 's/RES_COUNT}}*|/RES_COUNT}}qwer|/'
#if [ -e "$FILE" ]
#then
	sed -i "s/${USER}|\(.*\)RES_COUNT}}[0-9a-zа-я \/]*|/${USER}|\1RES_COUNT}}${OUT}|/" ${DIR}/bots/bot1_users_*.txt

#fi


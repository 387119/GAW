#!/bin/bash

USER=$1
COMMENT=$2
OUT=$COMMENT

DIR="/var/lib/dokuwiki/data/pages"

FILE=$(grep -l "|${USER}|" ${DIR}/users/*.txt)
#cat users/vasjamba.txt | grep Cat | sed -e 's/RES_COUNT}}*|/RES_COUNT}}qwer|/'
if [ -e "$FILE" ]
then
	sed -i "s/^|{{fa>\(.*\)|${USER}|\(.*\)|.*|$/|{{fa>\1|${USER}|\2|${OUT}|/g" "$FILE"
	#sed -i "s/${USER}|\(.*\)RES_COUNT}}[0-9a-zа-я \/]*|/${USER}|\1RES_COUNT}}${OUT}|/" "$FILE"
fi


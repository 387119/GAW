#!/bin/bash

USER=$1

DIR="/var/lib/dokuwiki/data/pages"

FILE=$(grep -l "|${USER}|" ${DIR}/users/*.txt)
if [ -e "$FILE" ]
then
	sed -i "s/${USER}|\(.*\)RES_BOT1}}<todo.*<\/todo>|/${USER}|\1RES_BOT1}}<todo>-<\/todo>|/" "$FILE"
fi


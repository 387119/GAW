#!/bin/bash

USER=$1

DIR="/var/lib/dokuwiki/data/pages"
FILE=$(grep -l "|${USER}|" ${DIR}/users/*.txt)
if [ -e "$FILE" ]
then
	sed -i "s/${USER}|\(.*\)RES_BOT1}}<[^>]*>/${USER}|\1RES_BOT1}}<todo>/; s/${USER}|\(.*\)RES_BOT4}}<[^>]*>/${USER}|\1RES_BOT4}}<todo>/" "$FILE"
fi


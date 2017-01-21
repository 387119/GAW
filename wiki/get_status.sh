#!/bin/bash

USER=$1

DIR="/var/lib/dokuwiki/data/pages"

STATUS=$(grep "|${USER}|" ${DIR}/users/* | grep '=#33ee11' | wc -l)
if [ "$STATUS" -eq "0" ]
then
	echo "offline"
else
	echo "online"
fi


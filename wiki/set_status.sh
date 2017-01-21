#!/bin/bash

USER=$1
STATUS=$2 #online/offline

DIR="/var/lib/dokuwiki/data/pages"


FILE=$(grep -l "|${USER}|" ${DIR}/users/*.txt)

if [ -e "$FILE" ]
then
if [ "$STATUS" == "online" ]
then
	sed -i "s/fa>close?color=#cccccc}}|${USER}|/fa>circle?color=#33ee11}}|${USER}|/g; s/fa>circle?color=#cccccc}}|${USER}|/fa>circle?color=#33ee11}}|${USER}|/g" "$FILE"

else
	sed -i "s/fa>close?color=#cccccc}}|${USER}|/fa>circle?color=#cccccc}}|${USER}|/g; s/fa>circle?color=#33ee11}}|${USER}|/fa>circle?color=#cccccc}}|${USER}|/g" "$FILE"
fi
fi


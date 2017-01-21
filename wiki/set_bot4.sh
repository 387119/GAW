#!/bin/bash

USER=$1

DIR="/var/lib/dokuwiki/data/pages"
if [ "-$USER" != "-" ]
then
	sed -i "s/|${USER}|\(.*\)RES_BOT4}}<[^>]*>/|${USER}|\1RES_BOT4}}<todo>/" $DIR/bots/bot1_users_*.txt
fi


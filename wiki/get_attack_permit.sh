#!/bin/bash

USER_MAIN=$1
USER_ATTACKED=$2

DIR="/var/lib/dokuwiki/data/pages"
OWNER_MAIN=$(grep -e "|$USER_MAIN|" $DIR/users/*.txt | awk -F'|' '{print $7}' | head -1)
OWNER_ATTACKED=$(grep -e "|$USER_ATTACKED|" $DIR/users/*.txt | awk -F'|' '{print $7}' | head -1)

if [ "-$OWNER_MAIN" == "-$OWNER_ATTACKED" ]
then
	echo "permit"
else
	echo "reject"
fi


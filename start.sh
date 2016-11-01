#!/bin/bash

USER=$1

DIR="/var/lib/dokuwiki/data/pages"
for i in $(grep "<todo #" ${DIR}/users/*.txt | cut -d'|' -f 3 | sed -e 's/ /|/g')
do 
	USER=$(echo $i | sed -e 's/|/ /g')
	php ./gaw.php "$USER" >"debug/$USER" &
	./set_bot1.sh "$USER"
	sleep 10
done


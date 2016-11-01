#!/bin/bash

CHECK_ANYWAY=$1

DIR="/var/lib/dokuwiki/data/pages"
for i in $(grep "{{fa>" ${DIR}/users/*.txt | grep -v "{{fa>ban" | cut -d'|' -f 3 | sed -e 's/ /|/g')
do 
	USER=$(echo $i | sed -e 's/|/ /g')
	php ./take_presents.php "$USER" "$CHECK_ANYWAY" &>"debug1/$USER" &
	sleep 10
done


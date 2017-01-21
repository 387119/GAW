#!/bin/bash

DIR="/var/lib/dokuwiki/data/pages"
echo -n >../tmp/users.sql
IFS=$'\n'
for i in $(grep "^|" ${DIR}/users/* | awk -F'|' '{print $3"|"$6"|"$7}')
do
	USER=$(echo $i | cut -d'|' -f 1)
	TYPE=$(echo $i | cut -d'|' -f 2 | sed -e 's/ //g')
	[ -z "$TYPE" ] && TYPE='NULL'
	OWNER=$(echo $i | cut -d'|' -f 3| sed -e 's/ //g')
	[ -z "$OWNER" ] && OWNER='';
	echo "update users set type=$TYPE,owner='$OWNER' where user_name='$USER';" >>../tmp/users.sql
done
psql -d gaw -U gaw -f ../tmp/users.sql

#!/bin/bash

DIR="/var/lib/dokuwiki/data/pages"
echo -n > ../tmp/pwds.sql
for i in $(cat $DIR/gaw_logins.txt | grep "^|" | cut -d"|" -f 2,3)
do
	LOGIN=$(echo $i | cut -d'|' -f 1| sed -e 's/.*:\(.*\)]].*/\1/g')
	PASSWD=$(echo $i | cut -d'|' -f 2| sed -e "s/<decrypt>\(.*\)<\/decrypt>/\1/" | openssl enc -d -aes-256-cbc -a -k psw)
	echo "update accounts set password_hash='' where acccount='$LOGIN' and passwd!='$PASSWD' ;insert into accounts (acccount,passwd) values ('$LOGIN','$PASSWD') on conflict (acccount) do update set passwd='$PASSWD';" >> ../tmp/pwds.sql
done
psql -d gaw -U gaw -f ../tmp/pwds.sql

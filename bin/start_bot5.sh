#!/bin/bash

../bots/sync_mresttodb.sh
php ../bots/sync_mrestowiki.php
../bots/sync_pwdtodb.sh
../bots/sync_usertodb.sh

for i in $(echo "select acccount from accounts where password_hash is null;" | psql -d gaw -P format=unaligned -P footer=off -P tuples_only=on gaw)
do
	php ../bots/activate_newpwd.php "$i"
done

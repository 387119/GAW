#!/bin/bash
IFS=$'\n'
for i in $(echo "select user_name from users where acccount='android777';" | psql -U postgres postgres | tail -n +3 | head -n -2 | sed -e 's/^ //g')
do
	if [ ! -f "d/$i" ]
	then
		php test.php "$i" >"d/$i" &
		sleep 4
	fi
done


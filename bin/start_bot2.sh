#!/bin/bash

# запуск командиров с таймаутом (в рамках одного аккаунта)

# Рутинка, запускается для проверки на все акки, таймаут нужен 5 секунд для командирова одного и тогоже акка, остальные запускаем без таймаута
# Также запуск происходит только для тех акков которые числятся оффлайн

IFS=$'\n'
ACC="";
NS="";

start_users(){
unset IFS
for UAI in $*
do
	US=$(echo $UAI | sed -e 's/|/ /g')
	echo "$US"
	php ../bots/bot2.php "$US"  >>"../log/$US" &
	sleep 5
done

}
for i in $(echo "select user_name,acccount from users where user_name not in (select user_name from bot0 where online=true) order by acccount,user_name;" | psql -d gaw -U gaw -P format=unaligned -P footer=off -P tuples_only=on)
do
	U=$(echo $i | awk -F'|' '{print $1}')
        ONLINESH=$(ps ax | grep php | grep "$U" | grep -v grep | wc -l)
        if [ "$ONLINESH" -eq "0" ]
	then
		A=$(echo $i | awk -F'|' '{print $2}')
		if [ "-$A" != "-$ACC" ]
		then
			ACC=$A
			if [ "-$NS" != "-" ]
			then
				start_users $NS &
				NS=""
			fi
		fi
		UA=$(echo $U | sed -e 's/ /|/g')
		NS="$NS $UA"
	fi
done
start_users $NS &


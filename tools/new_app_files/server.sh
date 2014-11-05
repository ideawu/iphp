#!/bin/bash
cur_dir=`old=\`pwd\`; cd \`dirname $0\`; echo \`pwd\`; cd $old;`
prj=`basename $cur_dir`
prj_dir=/data/www/$prj

fpm=php-fpm
fpm_config=$prj_dir/app/config/php-fpm.conf
fpm_pidfile=/var/run/php-fpm/$prj-php-fpm.pid
nginx=/usr/sbin/nginx


start(){
	printf "starting php-fpm..."
	$fpm -y $fpm_config -g $fpm_pidfile
	if [ -f "$fpm_pidfile" ]; then
		echo ""
		echo "  started"
	else
		echo ""
		echo "  failed!"
	fi
}

stop(){
	printf "stopping php-fpm"
	for ((i=0; ; i++)); do
		if [ $((i%10)) = 0 ]; then
			printf "."
		fi
		if [ -f "$fpm_pidfile" ]; then
			kill `cat $fpm_pidfile`
		else
			echo ""
			echo "  stopped"
			break
		fi
		sleep 0.1;
	done
}

case "$1" in 
	'start')
		stop
		start
		;;
	'stop') 
		stop
		;;
	'restart')
		stop
		start
		$nginx -s reload
		;;
	*)
		echo "Usage: $0 {start|stop|restart}"
		exit 1  
		;;
esac

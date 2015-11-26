#!/bin/bash
cur_dir=`old=\`pwd\`; cd \`dirname $0\`; echo \`pwd\`; cd $old;`
prj=`basename $cur_dir | sed s/\.dep\..*$//`

fpm=php-fpm
fpm_config=$cur_dir/app/config/php-fpm.conf
fpm_pidfile=/var/run/php-fpm/$prj-php-fpm.pid
nginx=/usr/sbin/nginx

start_fpm(){
	printf "starting php-fpm..."
	$fpm -y $fpm_config -g $fpm_pidfile
	if [ -f "$fpm_pidfile" ]; then
		echo ""
		echo "  php-fpm started"
	else
		echo ""
		echo "  php-fpm failed!"
	fi
}

stop_fpm(){
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

ask_restart_fpm(){
	echo ""
	/bin/echo -n "restart php-fpm?(y/n)[n] "
	read yn
	if [ "$yn" = "y" ] ; then
		:
	else
		echo "skip php-fpm"
		return
	fi
	stop_fpm
	start_fpm
}

case "$1" in 
	'start')
		stop_fpm
		start_fpm
		;;
	'stop') 
		stop_fpm
		;;
	'restart')
		ask_restart_fpm
		$nginx -s reload
		;;
	*)
		echo "Usage: $0 {start|stop|restart}"
		exit 1  
		;;
esac

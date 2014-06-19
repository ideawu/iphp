#!/bin/bash
cur_dir=`old=\`pwd\`; cd \`dirname $0\`; echo \`pwd\`; cd $old;`
prj=`basename $cur_dir`
config=$cur_dir/app/config/php-fpm.conf
pidfile=/var/run/php-fpm/iphp-fpm.pid
fpm=php-fpm

start(){
	$fpm -y $config -g $pidfile
	if [ -f "$pidfile" ]; then
		echo "started."
	else
		echo "failed!"
	fi
}

stop(){
	echo -n "stopping"
	while [ 1 ]; do 
		if [ -f "$pidfile" ]; then
			echo -n "."
			kill `cat $pidfile`
		else
			echo " done."
			break
		fi
		sleep 0.5
	done
}

case "$1" in 
	'start')
		start
		;;      
	'stop') 
		stop
		;;      
	'restart')
		stop
		start
		;;      
	*)
		echo "Usage: $0 {start|stop}"
		exit 1  
		;;      
esac
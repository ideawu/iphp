#!/bin/bash
cur_dir=`old=\`pwd\`; cd \`dirname $0\`; echo \`pwd\`; cd $old;`
prj=`basename $cur_dir`
env=$1

if [ -z "$env" ]; then
	echo "Usage: $0 ENV"
	echo "    ENV: dev, online"
	exit 1
fi


prj_dir=/data/www/$prj
dep_dir=/data/deploy_www/$prj.dep.`date +%Y%m%d_%H%M%S`

fpm=php-fpm
fpm_config=$prj_dir/app/config/php-fpm.conf
fpm_pidfile=/var/run/php-fpm/$prj-php-fpm.pid
nginx=/usr/sbin/nginx


function deploy_dev()
{
	rm -f $prj_dir
	ln -sf $cur_dir $prj_dir
	ln -sf $cur_dir/app/config/config_${env}.php $cur_dir/app/config/config.php
	ln -sf $cur_dir/app/config/nginx_${env}.conf /etc/nginx/conf.d/$prj.conf
}

function deploy_online()
{
	mkdir -p /data/applogs/$prj
	chmod ugo+rwx /data/applogs /data/applogs/$prj

	mkdir -p /data/deploy_www
	chmod ugo+rx /data/deploy_www

	old_deploys=`ls /data/deploy_www/ | grep "^$prj\.dep\."`

	echo "copy files..."
	rsync -a --exclude '.*' $cur_dir/ $dep_dir/
	if [ $? -ne "0" ]; then
		echo "Failed to deploy project!"
		exit 1
	fi
	chmod ugo+rx $dep_dir

	echo "create links..."
	# 将项目软链到当前版本
	rm -f $prj_dir
	ln -sf $dep_dir $prj_dir

	ln -sf $prj_dir/app/config/config_${env}.php $prj_dir/app/config/config.php
	ln -sf $prj_dir/app/config/nginx_${env}.conf /etc/nginx/conf.d/$prj.conf

	# 备份旧的版本
	mkdir -p /data/deploy_www/backup
	for i in $old_deploys; do
		echo "backup old deploy: $i"
		mv /data/deploy_www/$i /data/deploy_www/backup
	done
}

start_fpm(){
	$fpm -y $fpm_config -g $fpm_pidfile
	if [ -f "$fpm_pidfile" ]; then
		echo "php-fpm started."
	else
		echo "php-fpm failed!"
	fi
}

stop_fpm(){
	echo -n "stopping php-fpm"
	while [ 1 ]; do 
		if [ -f "$fpm_pidfile" ]; then
			echo -n "."
			kill `cat $fpm_pidfile`
		else
			echo " done."
			break
		fi
		sleep 0.5
	done
}


echo ""
echo "#######################################"
echo "Project: $prj"
echo "Env    : $env"
echo ""

if [ "$env" = "online" ]; then
	deploy_online
else
	deploy_dev
fi

stop_fpm
start_fpm
$nginx -s reload


echo ""
echo "done."
echo "#######################################"
echo ""

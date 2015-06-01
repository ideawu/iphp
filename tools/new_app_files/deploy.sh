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


deploy_dev()
{
	mkdir -p /data/applogs/$prj
	chmod ugo+rwx /data/applogs /data/applogs/$prj

	rm -f $prj_dir
	ln -sf $cur_dir $prj_dir
	ln -sf $cur_dir/app/config/config_${env}.php $cur_dir/app/config/config.php
	ln -sf $cur_dir/app/config/nginx_${env}.conf /etc/nginx/conf.d/$prj.conf
}

deploy_online()
{
	mkdir -p /data/applogs/$prj
	chmod ugo+rwx /data/applogs /data/applogs/$prj

	mkdir -p /data/deploy_www
	chmod ugo+rx /data/deploy_www

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


sh $prj_dir/server.sh restart


echo ""
echo "done."
echo "#######################################"
echo ""

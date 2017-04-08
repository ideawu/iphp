#!/bin/bash
cur_dir=`old=\`pwd\`; cd \`dirname $0\`; echo \`pwd\`; cd $old;`
prj=`basename $cur_dir`
env=$1
iphp_dir=/data/lib/iphp
nginx_conf_dir=/etc/nginx/conf.d
prj_dir=/data/www/$prj
dep_dir=/data/deploy_www/$prj.dep.`date +%Y%m%d_%H%M%S`


if [ -z "$env" ]; then
	echo "Usage: $0 ENV"
	echo "    ENV: dev, online"
	exit 1
fi


deploy_dev()
{
	mkdir -p /data/applogs/$prj
	chmod ugo+rwx /data/applogs /data/applogs/$prj

	rm -f $prj_dir
	ln -sf $cur_dir $prj_dir
	ln -sf $cur_dir/app/config/config_${env}.php $cur_dir/app/config/config.php
	ln -sf $cur_dir/app/config/nginx_${env}.conf $nginx_conf_dir/$prj.conf
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
	ln -sf $dep_dir/app/config/config_${env}.php $dep_dir/app/config/config.php
	ln -sf $dep_dir/app/config/nginx_${env}.conf /etc/nginx/conf.d/$prj.conf
	# 将项目软链到当前版本
	tmp_link=${prj_dir}_tmp
	rm -f $tmp_link
	ln -sf $dep_dir $tmp_link
	mv -fT $tmp_link $prj_dir
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


# update assets.json
if [ "$env" = "online" ]; then
	cd $dep_dir
fi
php $iphp_dir/tools/assets_md5.php js css imgs static
if [ $? -eq 0 ]; then
	echo "update assets.json done."
else
	echo "update assets.json fail! please resolve it!"
fi
cd $cur_dir
# end update assets.json


sh $prj_dir/server.sh restart


echo ""
echo "done."
echo "#######################################"
echo ""

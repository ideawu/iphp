#!/bin/bash
cur_dir=`old=\`pwd\`; cd \`dirname $0\`; echo \`pwd\`; cd $old;`
prj=`basename $cur_dir`
env=$1

if [ -z "$env" ]; then
	echo "Usage: $0 ENV"
	echo "    ENV: dev, online"
	exit
fi

echo ""
echo "#######################################"
echo "Project: $prj"
echo ""
echo "deploy $env."

ln -sf $cur_dir/app/config/config_${env}.php $cur_dir/app/config/config.php
ln -sf $cur_dir/app/config/nginx_${env}.conf /etc/nginx/conf.d/$prj.conf

mkdir -p /data/applogs/$prj
chmod ugo+rwx /data/applogs /data/applogs/$prj

echo "#######################################"
echo ""

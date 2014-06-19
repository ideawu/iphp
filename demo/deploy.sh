#!/bin/bash
cur_dir=`old=\`pwd\`; cd \`dirname $0\`; echo \`pwd\`; cd $old;`
prj=`basename $cur_dir`
env=$1

if [ -z "$env" ]; then
	echo "Usage: $0 ENV"
	echo "    ENV: dev, online"
	exit
fi

echo "deploy $env."

ln -sf $cur_dir/app/config/config_${env}.php $cur_dir/app/config/config.php
#ln -sf $cur_dir/app/config/nginx.conf /usr/local/nginx/conf.d/$prj.conf

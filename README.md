# 项目文件组织(目录结构)

	app/
		classes/
		config/
		console/
		controllers/
		models/
		views/
	css/
	fonts/
	js/
	deploy.sh
	server.sh
	index.php

## 利用 tools/new_app.php 来创建新项目

在你想建立项目的目录底下, 执行:

	php <iphp_framework>/tools/new_app.php

这是执行效果:

	$APP['NAME']: myfirstapp
	$APP['DOMAIN']: localhost
	$APP['PHP_FPM.PORT']: 9000
	
	Generate app into: /Users/ideawu/Works/iphp/tmp/myfirstapp ...
	Done!

### 项目部署

进入你的项目源码目录, 然后执行

```
sh deploy.sh dev
sh server.sh restart
```

# Nginx 配置

__默认情况下每一个 App 是独立的域名, 部署完成后, 通过你创建项目时的域名进行访问. 如果 App 是作为别的域名下的某个路径, 那么需要进行下面的配置.__

在已配置了 PHP 的情况下, 你只需要简单添加下面的内容到你的 nginx.conf 里, 然后重启 nginx 即可.

	location / {
		try_files $uri $uri/ /index.php?$args;
	}

这个配置表示以 `/` 开头的请求都尝试交给 /index.php 处理, 如果请求不对应某个文件(可能产生 404)的话.

如果想把你的项目放在 `/my` 目录下, 那么配置文件就是这样:

	location /my/ {
		try_files $uri $uri/ /my/index.php?$args;
	}

不过, 基于安全等考虑, 你还需要配置更多的东西, 具体参考附带的 `demo/app/config/nginx.conf` 模板.

	location / {
		location ~ /(app|logs)/ {
			deny all;
		}
		location ~ \.(sh|sql|conf|key|crt|csr) {
			deny all;
		}
		location ~ \.(gif|png|jpg|ico|svg|css|js|ttf|woff|eot)$ {
		}
		try_files $uri $uri/ /index.php?$args;
	}

__注意: 如果你的 nginx.conf 没有配置 `index index.php;`, 请加上.__


# URL 路由

## 路由规则

	Project URL Path => Controller + View

路由规则用于将一个 URL 路径, 转变为某一个控制器类(Controoler)的某个实例方法(Action)的执行, 以及某一个视图文件的渲染(View). 这个 URL 路径是相对路径, 相对项目的首页, 如项目的首页是 `/my/`, 那么 `/my/a/b/c` 的 URL 路径便是 `a/b/c`.

### 例子

#### URL 路径

	a/b/c

#### 控制器(Controller)查找顺序

    a/b.php
    a/b/c.php
    a/b/c/index.php

#### 方法(Action)查找

	index() 或者 c()

#### 视图(View)查找顺序

    a/b/c.tpl.php
    a/b/c.tpl.php
    a/b/c/index.tpl.php

#### 模板(Layout)查找顺序

模板文件(Layout)用于定义网页的整体框架, 并在其中某个位置加入

	<?php _view(); ?>

表示将视图(View)的内容插入此处. 模板文件默认必须是 `layout.tpl.php`, iphp 首先在视图(View)文件所在的同目录查找该文件, 如果找不到则查找上一级, 直到 views 目录为止.

### 演示

| Path | Controller | Aaction | View | Full URL |
| ---- | ---- | ---- | ---- | ---- |
| about | about.php | AboutController#index | about.tpl.php | http://localhost/iphp/about |
| about/us | about.php | AboutController#us | about/us.tpl.php | http://localhost/iphp/about/us |
| about/job | about/job.php | JobController#index | about/job.tpl.php | http://localhost/iphp/about/job |
| api/test | api/test.php | TestController#index | None | http://localhost/iphp/api/test |



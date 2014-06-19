# 项目文件组织(目录结构)

	app/
		classes/
		config/
		controllers/
		models/
		views/
	css/
	fonts/
	js/
	deploy.sh
	server.sh
	index.php

# URL 路由

## 路由规则

	URL Path => Controller + View

路由规则用于将一个 URL, 转变为某一个控制器类(Controoler)的某个实例方法(Action)的执行, 以及某一个视图文件的渲染(View).

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


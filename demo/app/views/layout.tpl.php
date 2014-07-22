<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>iphp - A fast and simple PHP framework for web development</title>
	<meta name="description" content="iphp framework">
	<meta name="keywords" content="iphp framework">
	<link href="<?= _url('/css/bootstrap.min.css') ?>" rel="stylesheet">
	<link href="<?= _url('/css/main.css') ?>" rel="stylesheet">
	<script src="<?= _url('/js/jquery-1.9.1.min.js') ?>"></script>
	<script src="<?= _url('/js/bootstrap.min.js') ?>"></script>
</head>
<body>

<!-- Fixed navbar -->
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<a class="navbar-brand" href="http://www.ideawu.com/iphp/">iphp</a>
		</div>
		<ul class="nav navbar-nav">
			<li class="divider-vertical"></li>
			<li class="active">
				<a href="<?=_url('/')?>">
					<i class="glyphicon glyphicon-home"></i> 首页
				</a>
			</li>
			<li class="divider-vertical"></li>
			<li>
				<a href="https://github.com/ideawu/iphp">
					<i class="glyphicon glyphicon-share-alt"></i> GitHub
				</a>
			</li>
		</ul>
	</div>
</div>



<div class="container">
	<?php _view(); ?>
		
	<hr>
	<div class="footer">
		Copyright &copy; 2014 <a href="http://www.ideawu.net/">ideawu</a>. All rights reserved.
	</div>

</div>
<!-- /container -->

</body>
</html>

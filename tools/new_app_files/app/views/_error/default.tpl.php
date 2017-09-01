<!doctype html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?= htmlspecialchars($_e->getMessage()) ?></title>
	<style>body{font-size: 14px; font-family: monospace;}</style>
</head>
<body>

<h1 style="text-align: center;">
	<?php
		$msg = htmlspecialchars($_e->getMessage());
		if(strpos($msg, 'in SQL:') !== false || strpos($msg, 'db error') !== false || get_class($_e) == 'SSDBException'){
			Logger::error($_e);
			$msg = 'db error';
		}
		echo ($msg ? $msg : 'server error');
	?>
</h1>

<div>
<?php
if(App::$env == 'dev'){
	$ts = $_e->getTrace();
	$html = '';
	foreach($ts as $t){
		$html .= "{$t['file']}:{$t['line']} {$t['function']}()<br/>\n";
	}
	echo $html;
}
?>
</div>

<p style="margin-top: 20px;
padding-top: 10px;
border-top: 1px solid #ccc;
text-align: center;">iphp</p>

</body>
</html>

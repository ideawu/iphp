<?php
define('TEMPLATE_DIR', dirname(__FILE__) . '/new_app_files');

$APP = array(
);

read_conf('NAME');
if(!preg_match('/^[a-z0-9-\.]+$/i', $APP['NAME'])){
	echo "Error: Invalid APP.NAME\n";
	die();
}
read_conf('DOMAIN');
read_conf('PHP_FPM.PORT');

$ret = generate();
if($ret !== false){
	echo "Done!\n";
	echo "\n";
}



function generate(){
	global $APP;

	$app_dir = getcwd() . '/' . $APP['NAME'];
	if(file_exists($app_dir)){
		echo "\n";
		echo "Warnning: App path[$app_dir] already exists!\n";
		echo "Overwrite?(n/y): ";
		$line = strtolower(trim(fgets(STDIN)));
		if($line !== 'y'){
			die();
		}
	}
	echo "\n";
	echo "Generate app into: $app_dir ...\n";

	$template_files = scan_dir(TEMPLATE_DIR);
	foreach($template_files as $file){
		$src = TEMPLATE_DIR . '/' . $file;
		$dst = $app_dir . '/' . $file;
		if(is_dir($src)){
			$dst_dir = $dst;
		}else{
			$dst_dir = dirname($dst);
		}
		if(!file_exists($dst_dir)){
			mkdir($dst_dir, 0755, true);
		}
		if(is_dir($src)){
			continue;
		}
	
		#echo "$src => $dst\n";
		$ps = explode('.', $src);
		$ext = $ps[count($ps) - 1];
		if(in_array($ext, array('sh', 'txt', 'conf', 'php'))){
			$text = compile_file($src);
		}else{
			$text = file_get_contents($src);
		}
		file_put_contents($dst, $text);
	}

	copy(TEMPLATE_DIR . '/.gitignore', $app_dir . '/.gitignore');
}

function compile_file($file){
	global $APP;
	$text = file_get_contents($file);
	foreach($APP as $k=>$v){
		$text = str_replace("{\$APP['$k']}", $v, $text);
	}
	return $text;
}

function read_conf($k){
	global $APP;
	while(1){
		echo "\$APP['$k']: ";
		$line = fgets(STDIN);
		$line = trim($line);
		if(strlen($line)){
			$APP[$k] = $line;
			break;
		}
	}
}

function scan_dir($dir, $base=false){
	if($base === false){
		$base = $dir;
	}
	$files = scandir($dir);
	$tmp = array();
	foreach($files as $f){
		if($f == '.' || $f == '..'){
			continue;
		}
		if(strpos($f, '.') === 0){
			continue;
		}
		$f = $dir . '/' . $f;
		$tmp[] = ltrim(substr($f, strlen($base)), '/');
		if(is_dir($f)){
			$subs = scan_dir($f, $base);
			$tmp = array_merge($tmp, $subs);
		}
	}
	return $tmp;
}



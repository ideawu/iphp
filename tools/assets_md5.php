<?php
/*
	生成当前工作目录下的js、css文件md5映射表
	默认是当前目录下的js、css、static文件夹，可传入参数更改
	在工作目录下调用 php /data/lib/iphp/tools/resourece_md5.php dir1 dir2...
	md5.json生成在当前目录下
*/

$cwd = getcwd();
$md5_file_name = 'assets.json';
$static_dir = array('js', 'css', 'static', 'imgs');
$extnames = array('js', 'css', 'jpg', 'png', 'gif');

if ($argc > 1) {
	$static_dir = array_slice($argv, 1);
}

$files = walk_file($static_dir);

$results = array();
foreach($files as $file){
	$ext = pathinfo($file, PATHINFO_EXTENSION);
	if (in_array($ext, $extnames)) {
		$results[$file] = md5_file($file);
	}
}
file_put_contents($md5_file_name, pretty_json($results) . "\n");

function walk_file($path) {
	$res = array();
	if (is_array($path)) {
		foreach ($path as $p) {
			$res = array_merge($res, walk_file($p));
		}
	} elseif (is_string($path)) {
		if (file_exists($path)) {
			if (is_dir($path)) {
				$files = scandir($path);
				foreach($files as $file){
					if($file == '.' || $file == '..'){
						continue;
					}
					$file = $path . '/' . $file;
					$res = array_merge($res, walk_file($file));
				}
			} elseif (is_file($path)) {
				array_push($res, $path);
			}
		}
	}
	return $res;
}

function pretty_json ($json) {
	// php version >= 5.4
	if (defined('JSON_PRETTY_PRINT')) {
		if (is_string($json)) {
			$json_obj = json_decode($json);
		} else {
			$json_obj = $json;
		}
		return json_encode($json_obj, JSON_PRETTY_PRINT);
	} else {
		if (is_string($json)) {
			return $json;
		} else {
			return json_encode($json);
		}
	}
}


<?php
/*
	生成当前工作目录下的js、css文件md5映射表
	默认是当前目录下的js、css、static文件夹，可传入参数更改
	在工作目录下调用 php /data/lib/iphp/tools/resourece_md5.php dir1 dir2...
	md5.json生成在当前目录下
*/

$cwd = getcwd();
$md5_file_name = 'md5.json';
$static_dir = array('js', 'css', 'static');
$extnames = array('js', 'css');
$MD5 = array();

if ($argc > 1) {
	$static_dir = array_slice($argv, 1);
}

$files = walk_file($static_dir);

for ($i=0, $l=count($files); $i < $l; $i++) { 
	$extname = pathinfo($files[$i], PATHINFO_EXTENSION);
	if (in_array($extname, $extnames)) {
		$MD5[$files[$i]] = md5_file($files[$i]);
	}
}

file_put_contents('md5.json', pretty_json($MD5));
echo 'generated file ' . $cwd . "/md5.json !\n";

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
				for ($i=0, $l=count($files); $i < $l; $i++) { 
					if ($files[$i] == '.' || $files[$i] == '..') {
						array_splice($files, $i, 1);
						$i--;
						$l--;
					} else {
						$files[$i] = $path . '/' . $files[$i];
					}
				}
				$res = array_merge(walk_file($files));
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
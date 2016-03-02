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

walk_dir($static_dir, 'resolve_file');
file_put_contents('md5.json', json_format(json_encode($MD5)));
echo 'generated file ' . $cwd . "/md5.json !\n";

function walk_dir($path, $callback) {
	if (is_array($path)) {
		foreach ($path as $p) {
			walk_dir($p, $callback);
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
				walk_dir($files, $callback);
			} elseif (is_file($path)) {
				call_user_func($callback, $path);
			}
		}
	}
}

function resolve_file($file) {
	$extname = pathinfo($file, PATHINFO_EXTENSION);
	if (in_array($extname, $GLOBALS['extnames'])) {
		$GLOBALS['MD5'][$file] = md5_file($file);
	}
}

//pretty json,  PHP version >= 5.4 can use json_encode($json, JSON_PRETTY_PRINT);
function json_format($json) { 
	$tab = "  "; 
	$new_json = ""; 
	$indent_level = 0; 
	$in_string = false; 

	$json_obj = json_decode($json); 

	if($json_obj === false) 
		return false; 

	$json = json_encode($json_obj); 
	$len = strlen($json); 

	for($c = 0; $c < $len; $c++) { 
		$char = $json[$c]; 
		switch($char) { 
			case '{': 
			case '[': 
				if(!$in_string) { 
					$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1); 
					$indent_level++; 
				} 
				else { 
					$new_json .= $char; 
				} 
				break; 
			case '}': 
			case ']': 
				if(!$in_string) { 
					$indent_level--; 
					$new_json .= "\n" . str_repeat($tab, $indent_level) . $char; 
				} 
				else { 
					$new_json .= $char; 
				} 
				break; 
			case ',': 
				if(!$in_string) { 
					$new_json .= ",\n" . str_repeat($tab, $indent_level); 
				} else { 
					$new_json .= $char; 
				} 
				break; 
			case ':': 
				if(!$in_string) { 
					$new_json .= ": "; 
				} 
				else { 
					$new_json .= $char; 
				} 
				break; 
			case '"': 
				if($c > 0 && $json[$c-1] != '\\') { 
					$in_string = !$in_string; 
				} 
			default: 
				$new_json .= $char; 
				break;                    
		} 
	} 
	return $new_json; 
} 
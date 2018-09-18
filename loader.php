<?php
define('APP_TIME_START', microtime(true));
define('IPHP_PATH', dirname(__FILE__));
include_once(dirname(__FILE__) . '/functions.php');

mb_internal_encoding("UTF-8");

$AUTOLOAD_PATH =  array(
	dirname(__FILE__) . '/framework',
	APP_PATH . '/models',
	APP_PATH . '/classes',
);

spl_autoload_register(function ($cls){
	global $AUTOLOAD_PATH;
	foreach($AUTOLOAD_PATH as $dir){
		$file = $dir . '/' . $cls . '.php';
		if(file_exists($file)){
			require_once($file);
			return;
		}
	}
	// 有很多代码会使用 class_exists(), 需要和它们兼容, 所以不能在这里 throw
#	if(!class_exists($cls, false)){
#		throw new Exception("Class $cls not found!");
#	}
});

// init application
App::init();


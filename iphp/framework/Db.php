<?php
include(dirname(__FILE__) . '/Mysql.php');

class Db{
	private static $config = array();

	function __construct(){
		throw new Exception("Static class");
	}

	static function init($config=array()){
		self::$config = $config;
	}

	static function instance(){
		static $db = null;
		if($db === null){
			$db = new Mysql(self::$config);
		}
		return $db;
	}
	
	static function begin(){
		self::instance()->begin();
	}
	
	static function commit(){
		self::instance()->commit();
	}
	
	static function rollback(){
		self::instance()->rollback();
	}
}

<?php
include(dirname(__FILE__) . '/Mysql.php');

class Db{
	private static $config = array();

	public function __construct(){
		throw new Exception("Static class");
	}

	public function init($config=array()){
		self::$config = $config;
	}

	public static function instance(){
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

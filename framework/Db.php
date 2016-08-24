<?php
/*
Use for multiple-dbs:
1. config
	'db_my' => array()
2. php
	Db::use_db('my');
*/

/*
Db 类: 实现多个数据库的选择, 并以静态方法形式调用
iphp_DbInstance 类: 对应单个数据库, 同时实现读写分离
Mysql_i 类: 数据库连接(可建立只读连接), 所有对数据库的操作均通过此类
*/

// Db 类是为了方便使用 iphp_DbInstance, iphp_DbInstance 支持的所有实例方法,
// 均可作为本类的静态方法调用.
class Db{
	private static $app_config = array();
	private static $instances = array();
	private static $current_dbname = '';

	function __construct(){
		throw new Exception("Static class");
	}

	static function init($app_config=array()){
		self::$app_config = $app_config;
	}
	
	static function use_db($dbname=''){
		self::$current_dbname = $dbname;
	}

	static function instance(){
		$dbname = self::$current_dbname;
		$key = $dbname? "db_{$dbname}" : 'db';
		if(!isset(self::$app_config[$key])){
			_throw("no config for db: $dbname");
		}
		if(!isset(self::$instances[$dbname])){
			$instance = new iphp_DbInstance(self::$app_config[$key]);
			self::$instances[$dbname] = $instance;
		}
		return self::$instances[$dbname];
	}

	static function __callStatic($cmd, $params=array()){
		return call_user_func_array(array(self::instance(), $cmd), $params);
	}
	
	static function build_in_string($val){
		if(is_string($val)){
			$val = explode(',', $val);
		}else if(is_array($val)){
			//
		}else{
			$val = array($val);
		}
		$tmp = array();
		foreach($val as $p){
			$p = trim($p);
			if(!strlen($p)){
				continue;
			}
			$p = self::escape($p);
			$tmp[$p] = $p;
		}
		return "'" . join("', '", $tmp) . "'";
	}
}

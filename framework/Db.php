<?php
require_once(dirname(__FILE__) . '/Mysql.php');

class Db{
	private static $config = array();
	private static $readonly = false;
	private static $load_balance = false;

	function __construct(){
		throw new Exception("Static class");
	}

	static function init($config=array()){
		self::$config = $config;
	}
	
	static function load_balance($yesno=true){
		self::$load_balance = $yesno;
		self::$readonly = $yesno;
	}
	
	static function readonly($yesno=true){
		if(!$yesno){
			self::$load_balance = false;
		}
		self::$readonly = $yesno;
	}
	
	static function is_readonly(){
		return self::$readonly;
	}
	
	private static $readonly_vars = array();
	
	static function push_readonly($yesno){
		self::$readonly_vars[] = self::$readonly;
		self::readonly($yesno);
	}
	
	static function pop_readonly(){
		if(!self::$readonly_vars){
			throw new Exception("No vars to pop from readonly_vars!");
		}
		$yesno = array_pop(self::$readonly_vars);
		self::readonly($yesno);
		return $yesno;
	}

	static function instance(){
		if(self::$readonly){
			static $readonly_db = null;
			if($readonly_db === null){
				if(isset(self::$config['readonly_db'])){
					$readonly_db = new Mysql(self::$config['readonly_db']);
					$readonly_db->readonly = true;
				}
			}			
			if($readonly_db){
				return $readonly_db;
			}
		}
		static $db = null;
		if($db === null){
			$db = new Mysql(self::$config);
		}
		return $db;
	}
	
	static function query($sql){
		if(self::$load_balance){
			if(Mysql::is_write_operation($sql)){
				self::readonly(false);
			}
		}
		return self::instance()->query($sql);
	}
	
	static function get_num($sql){
		$result = self::query($sql);
		if($row = mysql_fetch_array($result)){
			return (int)$row[0];
		}else{
			return 0;
		}
	}
	
	static function update($sql){
		self::query($sql);
		return self::instance()->affected_rows();
	}
	
	static function escape($val){
		return self::instance()->escape($val);
	}
	
	static function escape_like_string($val){
		return self::instance()->escape_like_string($val);
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

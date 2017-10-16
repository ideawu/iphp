<?php
// 实现数据库的读写分离
// Mysql_i 所支持的全部实例方法, 均可作为本类的实例方法调用.
class iphp_DbInstance
{
	private $config = array();
	private $master = null;
	private $slave = null;

	function __construct($config=array()){
		$this->config = $config;
	}
	
	function load_balance($yesno=true){
		$this->load_balance = $yesno;
		$this->readonly = $yesno;
	}
	
	function readonly($yesno=true){
		$this->load_balance = false;
		$this->readonly = $yesno;
	}
	
	function is_readonly(){
		return $this->readonly;
	}
	
	private $readonly_vars = array();
	
	function push_readonly($yesno){
		$this->readonly_vars[] = $this->readonly;
		$this->readonly($yesno);
	}
	
	function pop_readonly(){
		if(!$this->readonly_vars){
			throw new Exception("No vars to pop from readonly_vars!");
		}
		$yesno = array_pop($this->readonly_vars);
		$this->readonly($yesno);
		return $yesno;
	}

	function connection(){
		if($this->readonly){
			if($this->slave === null){
				if(isset($this->config['readonly_db']) && $this->config['readonly_db']){
					$this->slave = new Mysql_i($this->config['readonly_db']);
					$this->slave->readonly = true;
				}
			}			
			if($this->slave){
				return $this->slave;
			}
		}
		if($this->master === null){
			$this->master = new Mysql_i($this->config);
		}
		return $this->master;
	}
	
	function query($sql){
		if($this->load_balance){
			if(Mysql_i::is_write_query($sql)){
				$this->readonly(false);
			}
		}
		return $this->connection()->query($sql);
	}
	
	function update($sql){
		$this->readonly(false);
		$this->query($sql);
		return $this->connection()->affected_rows();
	}
	
	function begin(){
		$this->readonly(false);
		return $this->connection()->begin();
	}
	
	function save_row($table, $attrs){
		$this->readonly(false);
		$ret = $this->connection()->save($table, $attrs);
		return $ret;
	}

	function update_row($table, $id, $attrs){
		$this->readonly(false);
		$attrs['id'] = $id;
		$ret = $this->connection()->update($table, $attrs);
		return $ret;
	}

	function delete_row($table, $id){
		$this->readonly(false);
		return $this->connection()->remove($table, $id);
	}

	function __call($cmd, $params=array()){
		return call_user_func_array(array($this->connection(), $cmd), $params);
	}
	
	function __get($name){
		if($name == 'query_count'){
			$ret = 0;
			if($this->master){
				$ret += $this->master->query_count;
			}
			if($this->slave){
				$ret += $this->slave->query_count;
			}
			return $ret;
		}else{
			return $this->$name;
		}
	}
}

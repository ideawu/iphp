<?php
/// @require Logger

class iphp_MW_Master
{
	public $link = null;
	private $mw = null;
	
	function __construct($mw){
		$this->mw = $mw;
	}

	function init($ip, $port){
		$this->link = new iphp_MW_Link();
		$this->link->connect($ip, $port);
		$this->link->send('role', 'master');
		$resp = $this->link->recv();
		if($resp['type'] == 'ok'){
			Logger::debug("master started");
		}
	}
	
	function add_job($job){
		$ret = $this->link->send('job', $job);
		if(!$ret){
			throw new Exception("a");
		}
	}

	function run($manager){
		$this->init($manager->link->ip, $manager->link->port);
		$this->mw->master();
	}
}

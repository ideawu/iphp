<?php
class iphp_MW_Worker
{
	public $id = '';
	public $link = null;
	private $mw = null;
	
	function __construct($mw){
		$this->mw = $mw;
	}

	function init($ip, $port){
		$this->link = new iphp_MW_Link();
		$this->link->connect($ip, $port);
		$this->link->send('role', 'worker');
		$resp = $this->link->recv();
		if(!$resp){
			throw new Exception("manager gone");
		}
		if($resp['type'] == 'ok'){
			//Logger::debug("worker[{$this->id}] started");
		}else{
			throw new Exception("bad response");
		}
	}

	function run($manager){
		$this->init($manager->link->ip, $manager->link->port);

		while(1){
			$req = $this->link->recv();
			if(!$req){
				break;
			}
			if($req['type'] == 'quit'){
				break;
			}
			
			$job = $req['data'];
			#Logger::debug("process job: " . json_encode($job));
			
			// process job...
			#sleep(mt_rand(1, 2));
			$ret = $this->mw->worker($job['data']);
			
			$result = array(
				'id' => $job['id'],
				'result' => $ret,
			);
			$ret = $this->link->send('result', $result);
			if(!$ret){
				Logger::debug("worker[{$this->id}] send result error.");
				break;
			}
		}
		//Logger::debug("worker[{$this->id}] quit");
	}
}

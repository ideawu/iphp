<?php
class iphp_MW_Worker
{
	public $id = '';
	public $link = null;
	private $mw = null;
	private $name = '';
	
	function __construct($mw){
		global $argv;
		$this->name = basename($argv[0]);
		$this->mw = $mw;
	}

	function init($ip, $port){
		$this->link = new iphp_MW_Link();
		if(!$this->link->connect($ip, $port)){
			throw new Exception("could not connect to manager");
		}
		$this->link->send('role', 'worker');
		$resp = $this->link->recv();
		if(!$resp){
			throw new Exception("manager gone");
		}
		if($resp['type'] == 'ok'){
			//Logger::debug("[{$this->name}] worker[{$this->id}] started");
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
				#Logger::debug("[{$this->name}] receive quit");
				break;
			}
			
			$job = $req['data'];
			#Logger::debug("[{$this->name}] process job: " . json_encode($job));
			
			// process job...
			#sleep(mt_rand(1, 2));
			$error = '';
			try{
				$ret = $this->mw->worker($job['data']);
			}catch(Exception $e){
				$ret = false;
				$error = $e->getMessage();
				Logger::error("[{$this->name}] worker throw exception: " . $e->getMessage());
			}
			
			$result = array(
				'id' => $job['id'],
				'time' => $job['time'],
				'error' => $error,
				'result' => $ret,
			);
			$ret = $this->link->send('result', $result);
			if(!$ret){
				Logger::debug("[{$this->name}] worker[{$this->id}] send result error.");
				break;
			}
		}
		//Logger::debug("[{$this->name}] worker[{$this->id}] quit");
	}
}

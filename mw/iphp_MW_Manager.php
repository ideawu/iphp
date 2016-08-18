<?php
/// @require Logger

class iphp_MW_Manager
{

	public $link = null;
	private $master_link = null;
	private $workers = array();
	private $job_id = 1;
	private $jobs = array();
	private $quit = false;

	function init(){
		declare (ticks = 1);
		pcntl_signal(SIGTERM, array($this, 'sig_term'));

		$this->link = new iphp_MW_Link();
		$this->link->listen('127.0.0.1', 0);
	}

	function run(){
		while(!$this->quit){
			declare (ticks = 1); // 告诉PHP编译器, 这里可以插入中断(signal)检查语句
			if($this->loop_once() === false){
				break;
			}
		}
		Logger::debug("quit");
	}

	function sig_term($sig){
		$this->quit = true;
	}
	
	private function dispatch_jobs(){
		foreach($this->workers as &$worker){
			if(!$this->jobs){
				break;
			}
			if($worker['job_pending'] == 0){
				$job = array_shift($this->jobs);
				$worker['job_pending'] ++;
				$worker['link']->send('job', $job);
			}
		}
	}

	private function loop_once(){
		$this->dispatch_jobs();

		$read = array();
		$write = array();
		$except = array();
		
		$read[] = $this->link->sock;
		
		if($this->master_link){
			// 如果还有任务未处理, 则不再生产新任务(忽略 master_link 的消息)
			if(!$this->jobs){
				$read[] = $this->master_link->sock;
			}
		}
		foreach($this->workers as $worker){
			$read[] = $worker['link']->sock;
		}
		
		$ret = @socket_select($read, $write, $except, 1);
		if($ret === false){
			return false;
		}
		#var_dump($read, $write, $except);

		foreach($read as $sock){
			if($sock == $this->link->sock){
				$this->proc_connect();
			}else if($sock == $this->master_link->sock){
				$this->proc_master();
			}else{
				$this->proc_worker($sock);
			}
		}
	}
	
	private function proc_master(){
		$req = $this->master_link->recv();
		if(!$req){
			Logger::debug("master closed");
			$this->master_link->close();
			$this->master_link = null;
			return;
		}
		if($req['type'] == 'job'){
			$data = $req['data'];
			$this->jobs[] = array(
				'id' => $this->job_id ++,
				'data' => $data,
			);
			#Logger::debug("new job");
		}
	}
	
	private function proc_worker($sock){
		foreach($this->workers as $index=>&$worker){
			if($worker['link']->sock == $sock){
				$req = $worker['link']->recv();
				if(!$req){
					Logger::debug("worker closed");
					unset($this->workers[$index]);
					break;
				}
				if($req['type'] == 'result'){
					$job = $req['data'];
					$worker['job_pending'] --;
					#Logger::debug("finish job: " . json_encode($job));
				}
				break;
			}
		}
	}
	
	private function proc_connect(){
		$link = $this->link->accept();
		$req = $link->recv();
		if($req['type'] == 'role'){
			if($req['data'] == 'master'){
				#Logger::debug("master connected from {$link->ip}:{$link->port}");
				$this->master_link = $link;
				$link->send('ok');
			}
			if($req['data'] == 'worker'){
				#Logger::debug("worker connected from {$link->ip}:{$link->port}");
				$this->workers[] = array(
					'link' => $link,
					'job_pending' => 0,
				);
				$link->send('ok');
			}
		}
	}
}

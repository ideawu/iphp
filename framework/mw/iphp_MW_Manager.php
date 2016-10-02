<?php
class iphp_MW_Manager
{
	public $link = null;
	private $name = '';
	private $master_link = null;
	private $master_wait = false;
	private $master_finished = false;
	private $workers = array();
	private $job_id = 1;
	private $jobs = array();
	private $quit = false;
	private $job_pending = 0;
	
	function __construct(){
		global $argv;
		$this->name = basename($argv[0]);
	}

	function init(){
		declare (ticks = 1);
		pcntl_signal(SIGTERM, array($this, 'sig_term'));
		pcntl_signal(SIGINT, array($this, 'sig_term'));

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
		foreach($this->workers as $worker){
			$worker['link']->send('quit');
			$worker['link']->close();
		}

		$this->link->close();

		if($this->master_link){
			$this->master_link->close();
		}
		//Logger::debug("[{$this->name}] manager quit");
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
	
	// 如果 300s 内 master/worker 都空闲, 则认为系统异常, 退出 
	private $max_idle_time = 300;
	private $last_active_time = 0;

	function set_max_idle_time($secs){
		$this->max_idle_time = $secs;
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
		
		#var_dump($read);
		$ret = @socket_select($read, $write, $except, 1, 200*1000);
		if($ret === false){
			return false;
		}
		
		// 异常空闲检测
		if($this->last_active_time === 0){
			$this->last_active_time = time();
		}
		if($ret === 0){ // timeout
			if(time() - $this->last_active_time > $this->max_idle_time){
				Logger::info("[{$this->name}] master/workers idle too long, force quit");
				return false;
			}
		}else{
			$this->last_active_time = time();
		}

		foreach($read as $sock){
			if($sock == $this->link->sock){
				$this->proc_connect();
			}else if($sock == $this->master_link->sock){
				$this->proc_master();
			}else{
				$this->proc_worker($sock);
			}
		}

		if($this->master_wait && $this->job_pending == 0){
			$this->master_wait = false;
			//Logger::debug("[{$this->name}] wait finish");
			$this->master_link->send('ok');
		}
		if($this->master_finished && $this->job_pending == 0){
			$this->quit = true;
		}
	}
	
	private function proc_master(){
		$ret = $this->master_link->read();
		if(!$ret){
			#Logger::debug("[{$this->name}] master closed");
			$this->master_link->close();
			$this->master_link = null;
			$this->master_finished = true;
			return;
		}
		
		while(1){
			$req = $this->master_link->recv(false);
			if(!$req){
				break;
			}
			if($req['type'] == 'job'){
				$data = $req['data'];
				$job = array(
					'id' => $this->job_id ++,
					'time' => sprintf('%.3f', microtime(1)),
					'data' => $data,
				);
				$this->jobs[] = $job;
				$this->job_pending ++;
				#Logger::debug("[{$this->name}] new job: " . json_encode($job));
			}else if($req['type'] == 'wait'){
				//Logger::debug("[{$this->name}] receive wait");
				$this->master_wait = true;
			}
		}
	}
	
	private function proc_worker($sock){
		foreach($this->workers as $index=>&$worker){
			if($worker['link']->sock == $sock){
				$this->proce_worker_one($index, $worker);
				break;
			}
		}
	}
	
	private function proce_worker_one($index, &$worker){
		$link = $worker['link'];
		$ret = $link->read();
		if(!$ret){
			//Logger::debug("[{$this->name}] worker closed");
			unset($this->workers[$index]);
			return;
		}
		
		while(1){
			$req = $link->recv(false);
			if(!$req){
				break;
			}
			if($req['type'] == 'result'){
				$job = $req['data'];
				$worker['job_pending'] --;
				$this->job_pending --;
				$use_time = sprintf('%.3f', microtime(1) - $job['time']);
				#Logger::debug("[{$this->name}] finish job: {$job['id']}, use_time: $use_time, result: " . json_encode($job['result']));
			}
		}
	}
	
	private function proc_connect(){
		$link = $this->link->accept();
		if(!$link){
			throw new Exception("accept failed");
		}
		$req = $link->recv();
		if($req['type'] == 'role'){
			if($req['data'] == 'master'){
				#Logger::debug("[{$this->name}] master connected from {$link->ip}:{$link->port}");
				$this->master_link = $link;
				$link->send('ok');
			}
			if($req['data'] == 'worker'){
				#Logger::debug("[{$this->name}] worker connected from {$link->ip}:{$link->port}");
				$this->workers[] = array(
					'link' => $link,
					'job_pending' => 0,
				);
				$link->send('ok');
			}
		}
	}
}

<?php
/// @require Logger

include_once(dirname(__FILE__) . '/mw/iphp_MW_Link.php');
include_once(dirname(__FILE__) . '/mw/iphp_MW_Master.php');
include_once(dirname(__FILE__) . '/mw/iphp_MW_Worker.php');
include_once(dirname(__FILE__) . '/mw/iphp_MW_Manager.php');

abstract class MasterWorker
{
	// 该方法负责生产任务, 然后调用 add_job 添加进队列中
	// 运行于一个单独的进程
	abstract function master();
	
	// 每当一个 job 需要被处理时, 该方法都会被调用一次
	// 运行于一个单独的进程
	abstract function worker($job);
	
	function set_num_workers($num){
		$this->num_workers = $num;
	}

	function add_job($job){
		$this->master->add_job($job);
	}
	
	// 等待全部已添加的任务处理完毕
	function wait(){
		$this->master->wait();
	}

	private $master = null;
	private $num_workers = 1;
	private $pids = array();
	
	function run(){
		$manager = new iphp_MW_Manager();
		$manager->init();
		
		$this->start_master($manager);
		usleep(100 * 1000);
		for($i=0; $i<$this->num_workers; $i++){
			$this->start_worker($manager, $i);
		}
		
		$manager->run();
		
		// 等待全部子进程结束
		$stime = microtime(1);
		while(pcntl_wait($status) > 0){
			usleep(10 * 1000);
			$wait_secs = microtime(1) - $stime;
			if($wait_secs > 10 || $wait_secs < -10){
				Logger::debug("wait to long, force to kill all processes");
				foreach($this->pids as $pid){
					posix_kill($pid, SIGKILL);
				}
				break;
			}
		}
	}
	
	private function start_master($manager){
		$pid = pcntl_fork();
		if($pid < 0){
			//
		}else if($pid > 0){
			$this->pids[] = $pid;
			#Logger::debug("fork child pid: $pid");
		}else{
			$this->master = new iphp_MW_Master($this);
			try{
				$this->master->run($manager);
			}catch(Exception $e){
				#Logger::debug($e->getMessage());
			}
			exit(0); // 显式的 exit 子进程
		}
	}

	private function start_worker($manager, $id){
		$pid = pcntl_fork();
		if($pid < 0){
			//
		}else if($pid > 0){
			$this->pids[] = $pid;
			#Logger::debug("fork child pid: $pid");
		}else{
			$worker = new iphp_MW_Worker($this);
			$worker->id = $id;
			try{
				$worker->run($manager);
			}catch(Exception $e){
				#Logger::debug($e->getMessage());
			}
			exit(0); // 显式的 exit 子进程
		}
	}
}

/*
### Usage:
include_once(dirname(__FILE__) . '/MasterWorker.php');

class MyMasterWorker extends MasterWorker
{
	function master(){
		for($i=0; $i<500; $i++){
			$this->add_job($i);
		}
	}

	function worker($job){
		sleep(1);
		// ...
		return true;
	}
}

$mw = new MyMasterWorker();
$mw->set_num_workers(2);
$mw->run();
*/

/*
实现方式:

多进程之间使用 socket 进行通信.
1. 首先启动 Manager, 其创建一个 tcp server, 监控于随机端口.
2. 启动 Worker
3. 启动 Master
4. Master 逻辑
	* 使用 socket_select 接受 Master 和 Worker 的连接, 连接成功后, 进行握手, 确定 socket 的角色(Master/Worker)
	* 接收 Master 发来的 job, 转发给空闲的 Worker, 如果所有 Worker 忙, 等到有一个空闲为止
	* 接收 Worker 发来的 result, 更新任务计数, Worker 状态等
	* 当所有 job 处理完毕后, Master 结束, 退出.
*/

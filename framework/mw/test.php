<?php
include_once(dirname(__FILE__) . '/../Logger.php');
Logger::init();

include_once(dirname(__FILE__) . '/../MasterWorker.php');

class MyMasterWorker extends MasterWorker
{
	function master(){
		for($i=0; $i<30; $i++){
			#Logger::debug("add job $i");
			$this->add_job($i);
			#$this->wait(); // 如果每添加一个任务便 wait 的话, 将无法实现并发!
			#Logger::debug("");
		}
		Logger::debug("master added all $i jobs");
		
		// 当需要在确保所有任务处理完毕后再做其它操作时, 才需要调用 wait
		$this->wait();
		// ...
		Logger::debug("all job done");
	}

	function worker($job){
		if(mt_rand(0, 10) == 0){
			throw new Exception('worker exception');
		}
		usleep(mt_rand(2, 6) * 100 * 1000);
		// ...
		if(function_exists('posix_getpid')){
			$pid = posix_getpid();
		}else{
			$pid = 0;
		}
		Logger::debug("[$pid] process job: " . json_encode($job));
		return true;
	}
}

$mw = new MyMasterWorker();
$mw->set_num_workers(3);
$mw->set_max_idle_time(2);
$mw->run();




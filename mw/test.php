<?php
include_once('/data/lib/iphp/framework/Logger.php');
Logger::init();

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
		Logger::debug("process job: " . json_encode($job));
		return true;
	}
}

$mw = new MyMasterWorker();
$mw->set_num_workers(2);
$mw->run();




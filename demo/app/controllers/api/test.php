<?php
class TestController extends Controller
{
	function init($ctx){
		$this->layout = false;
	}
	
	function index($ctx){
		$data = array(
			'a' => 1,
			'b' => 2,
			);
		echo json_encode($data);
	}
}

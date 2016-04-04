<?php
class AjaxController extends Controller
{
	public $is_ajax = true;
	public $layout = false;
	public $jp = '';

	function init($ctx){
		parent::init($ctx);
		
		$jp = trim($_GET['callback']);
		if(preg_match('/^[a-z0-9_]+$/i', $jp)){
			$this->jp = $jp;
		}
	}
}

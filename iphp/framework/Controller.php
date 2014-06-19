<?php
class Controller
{
	public $module = '';
	public $action = '';
	public $layout = '';
	public $view_path = array('views');

	function init($ctx){
	}
	
	function _view($m){
		_redirect($this->_view_url($m));
	}
	
	function _new_url(){
		return _url($this->module . '/create');
	}
	
	function _list_url(){
		return _url($this->module . '');
	}
	
	function _save_url(){
		return _url($this->module . '/save');
	}
	
	function _update_url(){
		return _url($this->module . '/update');
	}
	
	function _view_url($m){
		return _url($this->module . '/view', array('id'=>$m->id));
	}
	
	function _edit_url($m){
		return _url($this->module . '/edit', array('id'=>$m->id));
	}
}


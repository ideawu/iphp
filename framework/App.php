<?php
class App{
	static $env;
	static $context;
	static $controller;
	private static $finish = false;
	static $config = array();
	static $version = '';
	static $asset_md5 = array();
	static $base_url = null;

	static function host(){
		$host = $_SERVER['HTTP_HOST'];
		$port = $_SERVER['SERVER_PORT'];
		if(strpos($host, ':') === false && $port != 80 && $port != 443){
			$host .= ":{$port}";
		}
		return $host;
	}
	
	static function set_base_url($base_url){
		$base_url = rtrim($base_url, '/');
		self::$base_url = $base_url;
	}

	// 兼容老代码
	static function ajax_resp($code, $msg, $data=null){
		return iphp_Response::ajax($code, $msg, $data);
	}

	static function init(){
		static $inited = false;
		if($inited){
			return;
		}
		$inited = true;
		
		$md5_file = APP_PATH . '/../assets.json';
		if(file_exists($md5_file)){
			self::$asset_md5 = @json_decode(@file_get_contents($md5_file), true);
			if(!is_array(self::$asset_md5)){
				self::$asset_md5 = array();
			}
		}else{
			$version_file = APP_PATH . '/../version';
			if(file_exists($version_file)){
				self::$version = trim(@file_get_contents($version_file));
			}
		}
		
		$config_file = APP_PATH . '/config/config.php';
		if(!file_exists($config_file)){
			throw new Exception("No config file");
		}
		$config = include($config_file);

		self::$config = $config;
		self::$env = $config['env'];

		Logger::init($config['logger']);
		Db::init($config);

		if(get_magic_quotes_gpc()){
			foreach($_GET as $k=>$v){
				$_GET[$k] = Text::stripslashes($v);
			}
			foreach($_POST as $k=>$v){
				$_POST[$k] = Text::stripslashes($v);
			}
			foreach($_COOKIE as $k=>$v){
				$_COOKIE[$k] = Text::stripslashes($v);
			}
		}
		$_REQUEST = $_GET + $_POST + $_COOKIE;
	}
	
	static function run(){
		// before any exception
		self::$context = new iphp_Context();
		
		try{
			$data = self::_run();
		}catch(AppBreakException $e){
			return;
		}catch(AppRedirectException $e){
			$url = $e->getMessage();
			@header("Location: $url", true, $e->getCode());
			return;
		}catch(Exception $e){
			return iphp_Response::error($e);
		}
		
		if(App::$finish){
			return;
		}
		
		if(App::$controller && App::$controller->is_ajax){
			iphp_Response::ajax(1, '', $data);
		}else{
			iphp_Response::html();
		}
	}
	
	static function _run(){
		if(base_path() == 'index.php'){
			_redirect('');
		}

		ob_start();
		try{
			App::init();
		}catch(Exception $e){
			ob_clean();
			throw $e;
		}
		ob_clean();

		if(App::$finish){
			return null;
		}
		
		$route = iphp_Router::route();
		list($base, $controller, $action) = $route;
		App::$controller = $controller;

		$controller->init(App::$context);
		
		if(App::$finish){
			return null;
		}
		
		$ret = $controller->$action(App::$context);
		return $ret;
	}
	
	static function _break(){
		self::$finish = true;
		throw new AppBreakException();
	}
	
	static function _redirect($url, $params_or_http_code=array()){
		if(App::$controller){
			App::$controller->layout = false;
		}
		App::$finish = true;
		$http_code = 302;
		if(is_array($params_or_http_code)){
			$url = _url($url, $params_or_http_code);
		}else{
			$url = _url($url);
			$http_code = intval($params_or_http_code);
		}
		// 某些代码在 try-catch 里执行 _redirect, 所以要输出 header, 以让
		// 那些代码能工作
		@header("Location: $url", true, $http_code);
		throw new AppRedirectException($url, $http_code);
	}
	
	static function include_paths(){
		static $paths = array();
		if(!$paths){
			$path = base_path();
			if(strlen($path) == 0){
				$ps = array('index');
			}else{
				$ps = explode('/', $path);
			}
			$act = $ps[count($ps) - 1];
			if($act == 'new'){
				$act = 'create';
			}
			$paths[] = array(
				'base' => join('/', array_slice($ps, 0, -1)),
				'action' => $act,
			);
			$paths[] = array(
				'base' => join('/', $ps),
				'action' => 'index',
			);
			if($act != 'index'){
				$paths[] = array(
					'base' => join('/', $ps) . '/index',
					'action' => 'index',
				);
				$paths[] = array(
					'base' => ltrim(join('/', array_slice($ps, 0, -1)) . '/index', '/'),
					'action' => $act,
				);
			}
		}
		return $paths;
	}
}

class AppBreakException extends Exception
{
	function __construct($msg='', $code=1){
		parent::__construct($msg, $code);
	}
}

class AppRedirectException extends Exception
{
	function __construct($msg='', $code=302){
		parent::__construct($msg, $code);
	}
}

class App404Exception extends Exception
{
	function __construct($msg='404 - Not Found'){
		parent::__construct($msg, 404);
	}
}

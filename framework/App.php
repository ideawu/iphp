<?php
class App{
	static $env;
	static $context;
	static $controller;
	static $finish = false;
	static $config = array();
	
	static function init(){
		$config_file = APP_PATH . '/config/config.php';
		if(!file_exists($config_file)){
			throw new Exception("No config file");
		}
		$config = include($config_file);

		self::$config = $config;
		self::$env = $config['env'];
		self::$context = new stdClass();

		Logger::init($config['logger']);
		if($config['db']){
			Db::init($config['db']);
		}
	}
	
	static function run(){
		$code = 1;
		$msg = '';
		$data = null;

		App::init();
		try{
			$data = self::execute();
		}catch(AppBreakException $e){
			//
		}catch(Exception $e){
			if(App::$controller && App::$controller->is_ajax){
				$code = $e->getCode();
				$msg = $e->getMessage();
				if(!strlen($msg)){
					$msg = 'error';
				}
			}else{
				if($e->getCode() == 404){
					header('Content-Type: text/html; charset=utf-8', true, 404);
				}else{
					header('Content-Type: text/html; charset=utf-8', true, 500);
				}
				self::print_error($e);
				return;
			}
		}
		
		if(App::$controller && App::$controller->is_ajax){
			$resp = array(
				'code' => $code,
				'message' => $msg,
				'data' => $data,
			);
			if(defined('JSON_UNESCAPED_UNICODE')){
				$json = json_encode($resp, JSON_UNESCAPED_UNICODE);
			}else{
				$json = json_encode($resp);
			}
			if(App::$controller->jp){
				echo App::$controller->jp . '(';
			}
			echo $json;
			if(App::$controller->jp){
				echo ');';
			}
		}else{
			$layout = find_layout_file();
			if($layout){
				$params = array();
				foreach(App::$context as $k=>$v){
					$params[$k] = $v;
				}
				extract($params);
				include($layout);
			}else{
				_view();
			}
		}
	}

	private static function execute(){
		$route = route();
		list($base, $controller, $action) = $route;
		App::$controller = $controller;

		$controller->init(App::$context);
		if(self::$finish){
			return null;
		}
		$ret = $controller->$action(App::$context);
		return $ret;
	}
	
	static function _break(){
		self::$finish = true;
		throw new AppBreakException();
	}
	
	static function print_error($e){
		echo '<html><head>';
		echo '<meta charset="UTF-8">';
		echo "<title>" . $e->getMessage() . "</title>\n";
		echo "<style>body{font-size: 14px; font-family: monospace;}</style>\n";
		echo "</head><body>\n";
		echo "<h1 style=\"text-align: center;\">" . $e->getMessage() . "</h1>";
		if(self::$env == 'dev'){
			$ts = $e->getTrace();
			foreach($ts as $t){
				echo "{$t['file']}:{$t['line']} {$t['function']}()<br/>\n";
			}
		}
		echo '<p style="
			margin-top: 20px;
			padding-top: 10px;
			border-top: 1px solid #ccc;
			text-align: center;">iphp</p>';
		echo '</body></html>';
	}
}

class AppBreakException extends Exception
{
	function __construct($msg, $code=1){
		parent::__construct($msg, $code);
	}
}

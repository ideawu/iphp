<?php
class iphp_Response
{
	// view 的渲染结果先保存在此变量中
	private static $view_content = '';
	
	static function output(){
		echo self::$view_content;
	}

	static function ajax($code, $msg, $data=null){
		if($msg === null){
			$msg = 'error';
		}
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
		$jp = App::$controller->jp;
		if(!preg_match('/^[a-z0-9_]+$/i', $jp)){
			$jp = false;
		}
		if($jp){
			echo "$jp($json);";
		}else{
			echo $json;
		}
	}

	static function html(){
		list($__view, $__layout) = self::find_view_and_layout();
		if(!$__view){
			Logger::trace("No view for " . base_path());
		}else{
			Logger::trace("View $__view");
			$__params = App::$context->as_array();
			extract($__params);
			ob_start();
			include($__view);
			self::$view_content = ob_get_clean();
		}
		
		if($__layout){
			Logger::trace("Layout $__layout");
			$__params = App::$context->as_array();
			extract($__params);
			include($__layout);
		}else{
			if(App::$controller->layout !== false){
				Logger::error("No layout for " . base_path());
			}
			_view();
		}
	}

	static function error($e){
		if(!App::$controller){
			App::$controller = new Controller();
		}
			
		if(App::$controller && App::$controller->is_ajax){
			//
		}else{
			$code = $e->getCode() === 0? 200 : $e->getCode();
			if($code == 404){
				header('Content-Type: text/html; charset=utf-8', true, 404);
			}else if($code == 403){
				header('Content-Type: text/html; charset=utf-8', true, 403);
			}else if($code == 200){
				header('Content-Type: text/html; charset=utf-8', true, 200);
			}else{
				header('Content-Type: text/html; charset=utf-8', true, 500);
			}
		}
		
		$error_page = self::find_error_page($code);
		if($error_page !== false){
			$__params = App::$context->as_array();
			$__params['_e'] = $e;
			extract($__params);
			try{
				include($error_page);
			}catch(Exception $e){
				//
			}
			return;
		}

		if(App::$controller && App::$controller->is_ajax){
			$code = $e->getCode();
			$msg = $e->getMessage();
			self::ajax($code, $msg, null);
			return;
		}

		$msg = htmlspecialchars($e->getMessage());
		$html = '';
		$html .= '<html><head>';
		$html .= '<meta charset="UTF-8"/>';
		$html .= "<title>$msg</title>\n";
		$html .= "<style>body{font-size: 14px; font-family: monospace;}</style>\n";
		$html .= "</head><body>\n";
		$html .= "<h1 style=\"text-align: center;\">$msg</h1>";
		if(App::$env == 'dev'){
			$ts = $e->getTrace();
			foreach($ts as $t){
				$html .= "{$t['file']}:{$t['line']} {$t['function']}()<br/>\n";
			}
		}
		$html .= '<p style="
			margin-top: 20px;
			padding-top: 10px;
			border-top: 1px solid #ccc;
			text-align: center;">iphp</p>';
		$html .= '</body></html>';
		echo "$html\n";
	}

	private static function find_error_page($code){
		if(App::$controller && App::$controller->is_ajax){
			$pages = array('ajax');
		}else{
			$pages = array($code, 'default');
		}
		if(App::$controller){
			$view_path_list = App::$controller->view_path;
		}else{
			$view_path_list = array('views');
		}

		$path = base_path();
		foreach($view_path_list as $view_path){
			$ps = explode('/', $path);
			while(1){
				$base = join('/', $ps);
				if($ps){
					$dir = APP_PATH . "/$view_path/$base";
				}else{
					$dir = APP_PATH . "/$view_path";
				}

				foreach($pages as $page){
					$file = "$dir/_error/{$page}.tpl.php";
					#echo $file . "\n<br/>";
					if(file_exists($file)){
						return $file;
					}
				}
				
				if(!$ps){
					break;
				}
				array_pop($ps);
			}
		}
		return false;
	}

	private static function find_view_and_layout(){
		// 先找 view, 如果找到, 那么找离该 view 最近的 layout
		$view = self::find_view();
		$view_file = false;
		$layout_file = false;
		if($view){
			$view_path = $view[0];
			$view_file = $view[1];
			// 将 view 所在的目录加到前面, 优先查找
			array_unshift(App::$controller->view_path, $view_path);
		}
		$layout_file = self::find_layout();
		return array($view_file, $layout_file);
	}

	private static function find_view(){
		foreach(App::$controller->view_path as $view_path){
			foreach(App::include_paths() as $path){
				// 由 Controller 指定模板的名字
				if(App::$controller->_render_view){
					$action = App::$controller->_render_view;
				}else{
					$action = $path['action'];
				}
				$base = $path['base'];

				$dir = rtrim(APP_PATH . "/$view_path/$base", '/');
				if($action == 'index'){
					$file = $dir . '.tpl.php';
				}else{
					$file = $dir . "/$action.tpl.php";
				}
				#echo 'DIR: ' . $dir . "\n";
				#echo $file . "\n";
				if(file_exists($file)){
					return array($view_path, $file);
				}
			}
		}
		return false;
	}

	private static function find_layout(){
		if(App::$controller->layout === false){
			return false;
		}
		foreach(App::$controller->view_path as $view_path){
			$file = self::find_layout_file($view_path);
			if($file){
				return $file;
			}
		}
		return false;
	}
	
	private static function find_layout_file($view_path){
		$layout = 'layout';
		if(App::$controller->layout){
			$layout = App::$controller->layout;
		}
	
		$path = base_path();
		$ps = explode('/', $path);
		while(1){
			$base = join('/', $ps);
			$file = APP_PATH . "/$view_path/$base/$layout.tpl.php";
			if(file_exists($file)){
				return $file;
			}
			if(!$ps){
				break;
			}
			array_pop($ps);
		}
		return false;
	}

}

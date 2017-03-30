<?php
class iphp_Router
{
	static function route(){
		foreach(App::include_paths() as $path){
			$base = $path['base'];
			$action = $path['action'];
			$controller = self::load_controller($base, $action);
			if($controller){
				if(strpos($base, '/index') === strlen($base) - 6){
					$base = substr($base, 0, strlen($base) - 6);
				}
				$controller->module = ($base == 'index')? '' : $base;
				break;
			}
		}
		if(!$controller){
			$path = base_path();
			Logger::trace("No route for $path!");
			throw new App404Exception();
		}
		return array($base, $controller, $action);
	}

	private static function load_controller($base, $action){
		$dir = APP_PATH . '/controllers/' . $base;
		$file = $dir . '.php';
		#echo join(', ', array($base, $action, $file)) . "\n";
		if(file_exists($file)){
			include($file);
			$ps = explode('/', $base);
			$controller = ucfirst($ps[count($ps) - 1]);
			$cls = "{$controller}Controller";
			if(!class_exists($cls)){
				throw new Exception("Controller $cls not found!");
			}
			$ins = new $cls();
		
			$found = false;
			if(method_exists($ins, $action)){
				$ins->action = $action;
				$found = true;
			}
			if($found){
				Logger::trace("Controller: $file");
				return $ins;
			}
		}
		return false;
	}
}

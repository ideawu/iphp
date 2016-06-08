<?php
class Http
{
	static $error = '';
	static $connect_timeout = 5;
	static $request_timeout = 25;

	static function post($url, $data=array()){
		self::$error = '';
		if(is_array($data)){
			$data = http_build_query($data);
		}
		$ch = curl_init($url) ;
		curl_setopt($ch, CURLOPT_POST, 1) ;
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connect_timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::$request_timeout);
		$result = @curl_exec($ch) ;
		self::$error = curl_error($ch);
		curl_close($ch) ;
		return $result;
	}

	static function get($url, $data=null){
		self::$error = '';
		if(is_array($data)){
			$data = http_build_query($data);
			if(strpos($url, '?') === false){
				$url .= '?' . $data;
			}else{
				$url .= '&' . $data;
			}
		}
		$ch = curl_init($url) ;
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$connect_timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::$request_timeout);
		$result = @curl_exec($ch) ;
		self::$error = curl_error($ch);
		curl_close($ch) ;
		return $result;
	}
	
	// 根据 $data 长度自动选择 GET/POST
	static function request($url, $params=array()){
		$data = array();
		$total_len = 0;
		foreach($params as $k=>$v){
			$len = strlen($v);
			$total_len += $len;
			$is_post_data = false;
			if($len > 128 || $total_len > 1024){
				$is_post_data = true;
			}
			if($is_post_data){
				$data[$k] = $v;
				unset($params[$k]);
			}
		}
		
		$params = http_build_query($params);
		if(strpos($url, '?') === false){
			$url .= '?' . $data;
		}else{
			$url .= '&' . $data;
		}
		if($data){
			return self::get($url);
		}else{
			return self::post($url, $data);
		}
	}
}

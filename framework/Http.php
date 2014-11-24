<?php
class Http
{
	static $connect_timeout = 5;
	static $request_timeout = 25;

	static function post($url, $data=array()){
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
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$request_timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::$connect_timeout);
		$result = @curl_exec($ch) ;
		curl_close($ch) ;
		return $result;
	}

	static function get($url, $data=null){
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
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::$request_timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, self::$connect_timeout);
		$result = @curl_exec($ch) ;
		curl_close($ch) ;
		return $result;
	}
}

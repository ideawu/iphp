<?php
class iphp_MW_Link
{
	public $sock = null;
	public $ip = '127.0.0.1';
	public $port = -1;

	function listen($ip, $port){
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_bind($sock, $ip, $port);
		socket_listen($sock);
		socket_getsockname($sock, $addr, $port);

		$this->sock = $sock;
		$this->ip = $addr;
		$this->port = $port;
		#Logger::debug("server listen at {$this->ip}:{$this->port}");
		return true;
	}

	function connect($ip, $port){
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if(!@socket_connect($sock, $ip, $port)){
			return false;
		}

		$this->sock = $sock;
		$this->ip = $ip;
		$this->port = $port;
		#Logger::debug("connected to {$ip}:{$port}");
		return true;
	}

	function accept(){
		$sock = @socket_accept($this->sock);
		if(!$sock){
			return null;
		}
		socket_getpeername($sock, $addr, $port);

		$link = new iphp_MW_Link();
		$link->sock = $sock;
		$link->ip = $addr;
		$link->port = $port;
		return $link;
	}

	function close(){
		if($this->sock){
			@socket_shutdown($this->sock);
			socket_close($this->sock);
			$this->sock = null;
		}
	}

	private $recv_buf = '';

	function send($type, $data=null){
		$msg = array(
				'type' => $type,
				'data' => $data,
				);
		$buf = json_encode($msg) . "\n";
		$ret = @socket_write($this->sock, $buf);
		return $ret;
	}

	// 从网络中读数据
	function read(){
		$buf = @socket_read($this->sock, 8*1024);
		if($buf === false){
			return false;
		}
		$this->recv_buf .= $buf;
		return strlen($buf);
	}

	/**
	 * 尝试读取一个完整的报文, 返回
	 * false: 出错
	 * null: 报文未接收完毕, 应该继续调用 read()
	 * array: {type: '', data: mixed}
	 */
	function recv($block=true){
		while(1){
			$pos = strpos($this->recv_buf, "\n");
			if($pos === false){
				if($block){
					$ret = $this->read();
					if($ret === false){
						return false;
					}else if($ret === 0){ // TODO:
						return null;
					}
					continue;
				}else{
					return null;
				}
			}
			$pos += 1;
			$str = substr($this->recv_buf, 0, $pos);
			$this->recv_buf = substr($this->recv_buf, $pos);

			$ret = @json_decode($str, true);
			return $ret;
		}
	}
}

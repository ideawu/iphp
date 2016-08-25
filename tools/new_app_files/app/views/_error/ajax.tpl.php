<?php
$msg = htmlspecialchars($_e->getMessage());
if(strpos($msg, 'in SQL:') !== false || strpos($msg, 'db error') !== false){
	Logger::error($_e);
	$msg = 'db error';
}
iphp_Response::ajax($_e->getCode(), $msg);

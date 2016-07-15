<?php
$msg = htmlspecialchars($_e->getMessage());
if(strpos($msg, 'in SQL:') !== false || strpos($msg, 'db error') !== false){
	Logger::error($_e);
	$msg = 'db error';
}
App::ajax_resp($_e->getCode(), $msg);

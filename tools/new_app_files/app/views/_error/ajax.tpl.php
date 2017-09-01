<?php
$msg = htmlspecialchars($_e->getMessage());
if(strpos($msg, 'in SQL:') !== false || strpos($msg, 'db error') !== false || get_class($_e) == 'SSDBException'){
	Logger::error($_e);
	$msg = 'db error';
}
iphp_Response::ajax($_e->getCode(), $msg);

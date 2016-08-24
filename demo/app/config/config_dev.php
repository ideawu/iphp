<?php
define('ENV', 'dev');

return array(
	'env' => ENV,
	'logger' => array(
		'level' => 'all', // none/off|(LEVEL)
		'dump' => 'file', // none|html|file, 可用'|'组合
		'files' => array( // ALL|(LEVEL)
			'ALL'	=> dirname(__FILE__) . '/../../logs/' . date('Y-m') . '.log',
		),
	),
	'db' => array(
		'host' => 'localhost',
		'dbname' => 'db',
		'username' => 'u',
		'password' => '123456',
		'charset' => 'utf8',
		// call Db::readonly(true) to enable readonly_db
		'readonly_db' => array(
			'host' => '127.0.0.1',
			'dbname' => 'db',
			'username' => 'u2',
			'password' => '123456',
			'charset' => 'utf8',
		),
	),
	// usage: Db::use_db('my');
	//'db_my' => ...
);

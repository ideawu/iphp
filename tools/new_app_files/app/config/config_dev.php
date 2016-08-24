<?php
define('ENV', 'dev');

return array(
	'env' => ENV,
	'logger' => array(
		'level' => 'debug', // none/off|(LEVEL)
		'dump' => 'file', // none|html|file, 可用'|'组合
		'files' => array( // ALL|(LEVEL)
			'ALL'	=> "/data/applogs/{$APP['NAME']}/" . date('Y-m') . '.log',
		),
	),
	'db' => array(
		'host' => 'localhost',
		'dbname' => 'db',
		'username' => 'u',
		'password' => '123456',
		'charset' => 'utf8',
	),
	// usage: Db::use_db('my');
	//'db_my' => ...
);

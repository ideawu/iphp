<?php
define('ENV', 'online');

return array(
	'env' => ENV,
	'logger' => array(
		'level' => 'all', // none/off|(LEVEL)
		'dump' => 'file', // none|html|file, 可用'|'组合
		'files' => array( // ALL|(LEVEL)
			'ALL'	=> "/data/applogs/demo/" . date('Y-m-d') . '.log',
		),
	),
	'db' => array(
		'host' => 'localhost',
		'dbname' => 'db',
		'username' => 'u',
		'password' => 'p',
		'charset' => 'utf8',
	),
	// usage: Db::use_db('my');
	//'db_my' => ...
);

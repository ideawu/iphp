<?php
error_reporting(E_ALL & ~E_NOTICE);

define('APP_PATH', dirname(__FILE__) . '/app');
define('IPHP_PATH', '/data/lib/iphp');
require_once(IPHP_PATH . '/loader.php');

App::run();

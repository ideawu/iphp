<?php
error_reporting(E_ALL & ~E_NOTICE);
define('APP_PATH', dirname(__FILE__) . '/app');
require_once('/data/lib/iphp/loader.php');

App::run();

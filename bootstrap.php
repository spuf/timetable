<?php

header('Content-type: text/html; charset=utf-8');
mb_internal_encoding('utf-8');
date_default_timezone_set('Asia/Yekaterinburg');

spl_autoload_register(function ($class) {
	if ((class_exists($class)) || (strpos($class, 'PHPExcel') === 0)) {
		return false;
	}
	include "Classes/{$class}.php";
	return true;
});

$host = isset($_SERVER["DB1_HOST"]) ? 'host='.$_SERVER["DB1_HOST"] : 'unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock';
$host .= isset($_SERVER["DB1_PORT"]) ? ';port='.$_SERVER["DB1_PORT"] : '';
$user = isset($_SERVER["DB1_USER"]) ? $_SERVER["DB1_USER"] : 'root';
$pass = isset($_SERVER["DB1_PASS"]) ? $_SERVER["DB1_PASS"] : 'root';

DB::Connect('mysql:dbname=timetable;'.$host, $user, $pass);

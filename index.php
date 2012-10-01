<?php

include_once 'bootstrap.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'timetable';

include_once 'pages/init.php';

if (preg_match('/(?<page>[a-z]+)/', $page, $match)) {
	$path = "pages/{$match['page']}.php";
	if (file_exists($path)) {
		include_once $path;
	}
}

include_once 'pages/layout.php';

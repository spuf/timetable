<?php

require_once 'bootstrap.php';

$checker = new Checker();

function Check($force = false) {
	global $checker;
	$checker->CheckPage($force);
	$checker->CheckFiles($force);
}

if (!empty($_SERVER['REQUEST_URI'])) {
	die("Run by request is forbidden!");
}

while (true) {
	Check();
	sleep(60);
}

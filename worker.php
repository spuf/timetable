<?php

require_once 'bootstrap.php';

$checker = new Checker();

function Check() {
	global $checker;
	$checker->CheckPage();
	$checker->CheckFiles();
}

if (!empty($_SERVER['REQUEST_URI'])) {
	die("Run by request is forbidden!");
}

while (true) {
	Check();
	sleep(60);
}

<?php

require_once 'bootstrap.php';

$checker = new Checker();

function Check($force = false) {
	global $checker;
	if (Storage::Get('LastDrop', 0) + 60*60*(24+22+20) < time() || $force) {
		Storage::Set('LastDrop', time());
		// clean up
		DB::Query('DELETE FROM Files', array(), false);
		DB::Query('DELETE FROM Dates', array(), false);
		DB::Query('DELETE FROM Variables', array(), false);
		Storage::Set('LastDrop', time());
	}
	$checker->CheckPage($force);
	$checker->CheckFiles($force);
}

if (!empty($_SERVER['REQUEST_URI'])) {
	//Check(true);
	die("Run by request is forbidden!");
}

while (true) {
	Check();
	sleep(60);
}

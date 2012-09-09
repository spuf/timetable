<?php

require_once 'config.php';
require_once 'DB.php';

function Check() {
	$html = file_get_contents('http://www.hse.perm.ru/student/timetable/');
	$info = round(strlen($html) / 1024, 1) . " kb, md5(".md5($html).")";

	if (strpos($html, 'Расписание занятий') !== false) {
		$info .= ', with timetable';
	}

	$text = 'Now is '.date('H:i:s').'. Memory used '.meminfo(false).'. Page size '.$info.'.';
	DB::Query('INSERT INTO log(text) VALUES(:text)', array(':text' => $text), false);
}

while (true) {
	Check();
	sleep(60 * 15);
}

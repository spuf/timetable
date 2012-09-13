<?php

require_once 'bootstrap.php';

function Check() {
	//$page = new PageParser('http://www.hse.perm.ru/student/timetable/');
	$page = new PageParser('./files/page.html');
	$data = $page->Parse();

	$cache = array();
	$new = $page->GetDiff($cache);

	// save $data to cache

	$parsed = var_export($page->GetParsable(), true);
	$text = 'Now is '.date('H:i:s').'. Memory used '.Debug::MemInfo(false).'. Page size '.$page->Size().'. '.$parsed;
	Debug::Log($text);
	DB::Query('INSERT INTO log(text) VALUES(:text)', array(':text' => $text), false);
}

if (!empty($_SERVER['REQUEST_URI'])) {
	Check();
	die("Run by request");
}

while (true) {
	Check();
	sleep(60 * 15);
}

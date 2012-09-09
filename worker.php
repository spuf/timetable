<?php

require_once 'config.php';
require_once 'DB.php';

while (true) {

	$text = 'Now is '.date('H:i:s').' memory '.meminfo(true);

	DB::Query('INSERT INTO log(text) VALUES(:text)', array(':text' => $text), false);

	sleep(60 * 15);
}

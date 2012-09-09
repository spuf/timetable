<?php

require_once './DB.php';

while (false) {

	sleep(60 * 15);
}

//DB::Query('INSERT INTO log(text) VALUES(:text)', array(':text' => 'insert test'), false);
$data = DB::Query('SELECT * FROM log ORDER BY time DESC');
print "<pre>";
print_r($data);
print "</pre>";

function Check() {

}
<?php

include_once 'config.php';
include_once 'Parser.php';
include_once 'DB.php';

$inputFileName = './files/test.xls';

$parser = new Parser();
$parser->LoadFromURL($inputFileName);
$data = $parser->ToTimetableArray();
$parser->UnloadExcel();

$parser->PrintTimetableArray($data);

print "<hr>";

$data = DB::Query('SELECT * FROM log ORDER BY time DESC LIMIT 20');
print "<pre>";
print_r($data);
print "</pre>";

print meminfo();

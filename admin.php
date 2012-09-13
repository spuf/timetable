<?php

include_once 'bootstrap.php';

$inputFileName = './files/test.xls';

$parser = new Parser();
$parser->LoadFromURL($inputFileName);
$data = $parser->ToTimetableArray();
$parser->UnloadExcel();

$parser->PrintTimetableArray($data);

print "<hr>";

$data = DB::Query('SELECT * FROM log ORDER BY time DESC LIMIT 20');
Debug::Log($data);

print Debug::MemInfo();

<?php

include_once 'config.php';
include_once 'Parser.php';

$inputFileName = './files/test.xls';

$parser = new Parser();
$parser->LoadFromURL($inputFileName);
$data = $parser->ToTimetableArray();
$parser->UnloadExcel();

print "<pre>";
print_r($data);
print "</pre>";


print meminfo();

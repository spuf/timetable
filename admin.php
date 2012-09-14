<?php

include_once 'bootstrap.php';

$inputFileName = './files/test.xls';

$parser = new Parser();
$saver = new SQLSaver();


$parser->LoadFromURL($inputFileName);
$timetable = $parser->ToTimetableArray();
$parser->UnloadExcel();

DB::Query('DELETE FROM Pairs WHERE FileID = ?', array(1), false);
$saver->Save($timetable, 1);

//$data = DB::Query('SELECT * FROM log ORDER BY time DESC LIMIT 20');
//Debug::Log($data);

Debug::Log(Storage::Get('test'));
Storage::Set('test', '123');
Debug::Log(Storage::Get('test'));

print Debug::MemInfo();

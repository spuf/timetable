<?php

class Checker {

	var $page;
	var $parser;
	var $saver;

	function __construct() {
		$this->page = new PageParser();
		$this->parser = new Parser();
		$this->saver = new SQLSaver();
	}

	function CheckPage($force = false) {
		if (Storage::Get('LastCheck', 0) + 60*15 < time() || $force) {
			Storage::Set('LastCheck', time());

			if ($this->page->Load('http://www.hse.perm.ru/student/timetable/')) {
				$data = $this->page->Parse();

				$cache = Storage::Get('Cache', array());
				$new = $this->page->GetDiff($cache);
				Storage::Set('Cache', $data);

				foreach ($new as $item) {
					DB::Query('INSERT INTO Files (Title, Date, Link, Parsed) VALUES (:title, :date, :link, 0)', array(
						':title' => $item['name'],
						':date' => date(DB::DATETIME, strtotime($item['date'])),
						':link' => 'http://www.hse.perm.ru'.$item['link'],
					), false);
				}
			}
		}
	}

	function CheckFiles($force = false) {
		if (Storage::Get('LastQueue', 0) + 60 < time() || $force) {
			Storage::Set('LastQueue', time());

			$links = DB::Query('SELECT ID, Link FROM Files WHERE Parsed = 0 ORDER BY ID LIMIT 1');
			if (count($links) > 0) {
				$this->parser->LoadFromURL($links[0]['Link']);
				$timetable = $this->parser->ToTimetableArray();
				$this->parser->UnloadExcel();

				$this->saver->Save($timetable, $links[0]['ID']);

				DB::Query('UPDATE Files SET Parsed = 1 WHERE ID = :id', array(
					':id' => $links[0]['ID'],
				), false);
			}
		}
	}

}

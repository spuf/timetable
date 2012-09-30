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
					$count = DB::Query('SELECT ID FROM Files WHERE Title = :title AND Date = :date', array(
						':title' => $item['name'],
						':date' => date(DB::DATETIME, strtotime($item['date'])),
					));
					if (empty($count)) {
						DB::Query('INSERT INTO Files (Title, Date, Link, Parsed) VALUES (:title, :date, :link, 0)', array(
							':title' => $item['name'],
							':date' => date(DB::DATETIME, strtotime($item['date'])),
							':link' => 'http://www.hse.perm.ru'.$item['link'],
						), false);
					}
				}
			}
		}
	}

	function CheckFiles($force = false) {
		if (Storage::Get('LastQueue', 0) + 60 < time() || $force) {
			Storage::Set('LastQueue', time());

			$rows = DB::Query('SELECT ID, Link, Title FROM Files WHERE Parsed = 0 ORDER BY ID LIMIT 1');
			if (count($rows) > 0) {
				$row = $rows[0];
				$this->parser->LoadFromURL($row['Link']);
				$timetable = $this->parser->ToTimetableArray();
				$this->parser->UnloadExcel();

				$this->saver->Save($timetable, $row['ID']);

				DB::Query('UPDATE Files SET Parsed = 1 WHERE ID = :id', array(
					':id' => $row['ID'],
				), false);

				// clean up
				DB::Query('DELETE FROM Files WHERE Title = :title AND ID < :id', array(
					':title' => $row['Title'],
					':id' => $row['ID'],
				), false);
				DB::Query('DELETE FROM Dates WHERE ID NOT IN (SELECT DISTINCT DateID FROM Pairs)', array(), false);
				DB::Query('DELETE FROM Times WHERE ID NOT IN (SELECT DISTINCT TimeID FROM Pairs)', array(), false);
				DB::Query('DELETE FROM Styles WHERE ID NOT IN (SELECT DISTINCT StyleID FROM Pairs)', array(), false);

				// update cache
				Cache::Query(QueryLibrary::LatestFiles(), true);
			}
		}
	}

}

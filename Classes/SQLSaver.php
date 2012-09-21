<?php

class SQLSaver {

	var $groups;
	var $dates;
	var $times;
	var $styles;

	function Save(&$timetable, $fileId) {
		$this->LoadGroups();
		$this->LoadDates();
		$this->LoadTimes();
		$this->LoadStyles();

		foreach ($timetable as $group => $days) {
			$groupId = $this->GetGroup($group);
			foreach ($days as $date => $day) {
				$dateId = $this->GetDate($date, $day['dow']);
				if (is_array($day['pairs'])) {
					foreach ($day['pairs'] as $number => $pair) {
						$this->ExtractRoom($pair['title']);
						$this->ExtractTeacher($pair['title']);

						if (empty($pair['style']))
							$pair['style'] = '';
						$timeId = $this->GetTime($pair['time'], $number);
						$styleId = $this->GetStyle($pair['style']);
						$pairId = -1;
						DB::Query('
							INSERT INTO Pairs (FileID, GroupID, DateID, TimeID, Title, StyleID)
							VALUES(:file, :group, :date, :time, :title, :style)
						',array(
							':file' => $fileId,
							':group' => $groupId,
							':date' => $dateId,
							':time' => $timeId,
							':title' => $pair['title'],
							':style' => $styleId,
						), false, $pairId);
						if (!empty($pair['with']) && $pairId > -1) {
							foreach ($pair['with'] as $with) {
								$withGroup = $this->GetGroup($with);
								if ($withGroup != $groupId) {
									DB::Query('INSERT INTO Withs (PairID, GroupID) VALUES (:pair, :group)', array(
										':pair' => $pairId,
										':group' => $withGroup,
									), false);
								}
							}
						}
					}
				}
			}
		}
		//Debug::Log('Groups list', $this->groups);
		//Debug::Log('Dates list', $this->dates);
	}

	function LoadGroups() {
		$this->groups = DB::Query('SELECT * FROM Groups');
	}
	function GetGroup($name) {
		if (is_array($this->groups)) {
			foreach ($this->groups as $group) {
				if ($group['Title'] == $name) {
					return $group['ID'];
				}
			}
		}
		return $this->AddGroup($name);
	}
	function AddGroup($name) {
		if (!empty($name)) {
			DB::Query('INSERT INTO Groups (Title) VALUES (:title)', array(':title' => $name), false);
			$this->LoadGroups();
			return $this->GetGroup($name);
		}
		return null;
	}
	
	function LoadDates() {
		$this->dates = DB::Query('SELECT ID, Date FROM Dates');
	}
	function GetDate($data, $dow = null) {
		if (is_array($this->dates)) {
			foreach ($this->dates as $date) {
				if ($date['Date'] == $data) {
					return $date['ID'];
				}
			}
		}
		return $this->AddDate($data, $dow);
	}
	function AddDate($date, $dow) {
		if (!empty($date)) {
			DB::Query('INSERT INTO Dates (Date, Dow) VALUES (Date(:date), :dow)', array(':date' => $date, ':dow' => $dow), false);
			$this->LoadDates();
			return $this->GetDate($date);
		}
		return null;
	}
	
	function LoadTimes() {
		$this->times = DB::Query('SELECT ID, Time FROM Times');
	}
	function GetTime($data, $number = null) {
		if (is_array($this->times)) {
			foreach ($this->times as $time) {
				if ($time['Time'] == $data) {
					return $time['ID'];
				}
			}
		}
		return $this->AddTime($data, $number);
	}
	function AddTime($time, $number) {
		if (!empty($time)) {
			DB::Query('INSERT INTO Times (Time, Number) VALUES (:time, :number)', array(':time' => $time, ':number' => $number), false);
			$this->LoadTimes();
			return $this->GetTime($time);
		}
		return null;
	}

	function LoadStyles() {
		$this->styles = DB::Query('SELECT * FROM Styles');
	}
	function GetStyle($name) {
		if (is_array($this->styles)) {
			foreach ($this->styles as $style) {
				if ($style['Style'] == $name) {
					return $style['ID'];
				}
			}
		}
		return $this->AddStyle($name);
	}
	function AddStyle($name) {
		DB::Query('INSERT INTO Styles (Style) VALUES (:style)', array(':style' => $name), false);
		$this->LoadStyles();
		return $this->GetStyle($name);
	}

	function ExtractRoom($title) {
		if (preg_match_all('/\((?<number>\d+)\[(?<building>\d+)\]\)/', $title, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				if (DB::Query('SELECT * FROM Rooms WHERE Number = :number AND Building = :building', array(
					':number' => $match['number'],
					':building' => $match['building'],
				), false) == 0) {
					DB::Query('INSERT INTO Rooms (Number, Building) VALUES (:number, :building)', array(
						':number' => $match['number'],
						':building' => $match['building'],
					), false);
				}
			}
		}
	}

	function ExtractTeacher($title) {
		if (preg_match_all('/(?<name>\S+\s+\S\.\S\.)/misu', $title, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				if (DB::Query('SELECT * FROM Teachers WHERE Title = :name', array(
					':name' => $match['name'],
				), false) == 0) {
					DB::Query('INSERT INTO Teachers (Title) VALUES (:name)', array(
						':name' => $match['name'],
					), false);
				}
			}
		}
	}

}

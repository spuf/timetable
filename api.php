<?php

include_once 'bootstrap.php';

$api_ver = isset($_GET['api']) ? intval($_GET['api']) : 1; // legacy

$data = array();

switch ($api_ver) {
	case 1:
		$data = api_1($data);
		break;
	case 2:
		$data = api_2($data);
		break;
}

if (version_compare(PHP_VERSION, '5.3.3') >= 0)
	$data = json_encode($data, JSON_NUMERIC_CHECK);
else
	$data = json_encode($data);

if (isset($_GET['callback'])) {
	preg_match('/([a-z0-9_\-\$\.]+)/ui', $_GET['callback'], $callback);
	print $callback[1] . '(' . $data . ');';
}
else {
	print $data;
}

function api_1($data) {
	$data['version'] = '2.0';
	$data['error'] = 'Версия устарела.';
	$data['link'] = 'http://timetable.spuf.ru/';
	$data['timetable'] = array();
	return $data;
}

function api_2($data) {
	switch ($_GET['query']) {
		case 'groups':
			$data['groups'] = DB::Query('SELECT ID, Title FROM Groups ORDER BY Title');
			foreach ($data['groups'] as $key => $value) {
				$data['groups'][$key]['id'] = $value['ID'];
				$data['groups'][$key]['name'] = $value['Title'];
			}
			break;
		case 'latest':
			$groupId = (int)intval($_GET['group']);
			$groups = DB::Query('SELECT Title FROM Groups WHERE `ID` = '.$groupId.' ORDER BY Title');
			$data['group'] = count($groups) > 0 ? $groups[0]['Title'] : null;
			$timetable = array();
			$link = 'http://timetable.spuf.ru/';

			if ($data['group']) {
				$sql = DB::Query("
					SELECT t.Number, t.Time, p.Title, s.Style, DATE_FORMAT(d.Date, '%d.%m.%Y') as Date, d.Dow, f.Title as FileName, DATE_FORMAT(f.Date, '%H:%i %d.%m.%Y') as FileDate,
						(
						SELECT GROUP_CONCAT(w.GroupID SEPARATOR ',') FROM Withs w
						WHERE w.PairID = p.ID
						) as `With`
					FROM Pairs p
						JOIN Times t ON t.ID = p.TimeID
						JOIN Styles s ON s.ID = p.StyleID
						JOIN Dates d ON d.ID = p.DateID
						JOIN Files f ON f.ID = p.FileID
					WHERE p.GroupID = :group
						AND d.Date >= DATE(:now)
						AND d.Date <= DATE(:now + INTERVAL 4 DAY)
						AND p.FileID = (
							SELECT MAX(pi.FileID)
							FROM Pairs pi
								JOIN Dates di ON di.ID = pi.DateID
							WHERE pi.GroupID = p.GroupID
								AND pi.DateID = p.DateID
						)
					ORDER BY p.ID, t.Number
				", array(
					':group' => $groupId,
					':now' => date('Y-m-d', strtotime('+5 hours')),
				));

				$date = null;
				$days = 0;
				for($i = 0; $i < count($sql); $i++) {
					$pair = $sql[$i];
					if (!isset($timetable[$pair['Date']])) {
						$days += 1;
						if ($days > 2)
							break;
						$timetable[$pair['Date']] = array(
							'dow' => $pair['Dow'],
							'pairs' => array(),
						);
					}
					$style = array();
					if (!empty($pair['Style'])) {
						foreach (explode(';', trim($pair['Style'], ';')) as $item) {
							list($key, $value) = explode(':', $item);
							$style[$key] = $value;
						}
					}
					$timetable[$pair['Date']]['pairs'][$pair['Number']] = array(
						'time' => $pair['Time'],
						'title' => $pair['Title'],
						'style' => $style,
					);
					if (!empty($pair['With'])) {
						$with = explode(',', $pair['With']);
						if (count($with) > 0) {
							$timetable[$pair['Date']]['pairs'][$pair['Number']]['with']	= $with;
						}
					}
				}
			}
			else {
				$data['error'] = 'Группа не найдена в базе';
			}
			$data['link'] = $link;
			$data['timetable'] = $timetable;
			break;
		default:
			$data['error'] = 'Неверный запрос';
	}
	return $data;
}

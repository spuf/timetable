<?php

include_once 'bootstrap.php';

$maxage = 5;
header("Cache-Control: max-age=$maxage, public");
header("Expires: " . gmstrftime("%a, %d %b %Y %H:%M:%S GMT", time() + $maxage));

$api_ver = isset($_GET['api']) ? intval($_GET['api']) : 1; // legacy

$data = array();

switch ($api_ver) {
	case 1:
		$data = api_1($data);
		break;
	case 2:
		$data = api_2($data);
		break;
	case 3:
		$data = api_3($data);
		break;
}

if (version_compare(PHP_VERSION, '5.3.3') >= 0)
	$data = json_encode($data, JSON_NUMERIC_CHECK);
else
	$data = json_encode($data);

if (isset($_GET['pretty']))
	$data = prettyPrint($data);

if (isset($_GET['callback'])) {
	preg_match('/([a-z0-9_\-\$\.]+)/ui', $_GET['callback'], $callback);
	print $callback[1] . '(' . $data . ');';
} else {
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
				$data['groups'][$key] = array(
					'id' => $value['ID'],
					'name' => $value['Title'],
				);
			}
			break;
		case 'latest':
			$groupId = (int)intval($_GET['group']);
			$groups = DB::Query('SELECT Title FROM Groups WHERE `ID` = '.$groupId.' ORDER BY Title');
			$data['group'] = count($groups) > 0 ? $groups[0]['Title'] : null;
			$timetable = array();
			$link = 'http://timetable.spuf.ru/?page=timetable&file=now&group='.$groupId;

			if ($data['group']) {
				$sql = DB::Query("
					SELECT t.Number, t.Time, p.Title, s.Style, DATE_FORMAT(d.Date, '%d.%m.%Y') as Date, d.Dow, f.ID as FileCode, f.Title as FileName, DATE_FORMAT(f.Date, '%H:%i %d.%m.%Y') as FileDate,
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
					ORDER BY d.Date, t.Number
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

function api_3($data) {
	switch ($_GET['query']) {
		case 'groups':
			$data['groups'] = DB::Query('SELECT ID, Title FROM Groups ORDER BY Title');
			foreach ($data['groups'] as $key => $value) {
				$data['groups'][$key] = array(
					'id' => $value['ID'],
					'name' => $value['Title'],
				);
			}
			break;
		case 'latest':
			$groupId = isset($_GET['group']) ? intval($_GET['group']) : -1;
			$daysCount = isset($_GET['days']) ? intval($_GET['days']) : 2;
			if ($daysCount < 0)
				$daysCount = 0;
			if ($daysCount > 7)
				$daysCount = 7;
			$groups = DB::Query('SELECT Title FROM Groups WHERE `ID` = :id ORDER BY Title', array(':id' => $groupId));
			$data['group'] = count($groups) > 0 ? $groups[0]['Title'] : null;
			$data['link'] = 'http://timetable.spuf.ru/?page=timetable&file=now&group='.$groupId;
			$timetable = array();
			$checksum = '[spuf.ru]:';

			if ($groupId > -1 && $daysCount > 0) {
				$limit = $daysCount * 10;
				$sql = DB::Query("
					SELECT f.ID as FileID, DATE_FORMAT(d.Date, '%d.%m.%Y') as `Date`, d.Dow, t.Number, t.Time, p.Title, s.Style,
						(
						SELECT GROUP_CONCAT(wg.Title SEPARATOR ', ') FROM Withs w
						JOIN Groups wg ON wg.ID = w.GroupID
						WHERE w.PairID = p.ID
						) as `With`
					FROM Pairs p
						JOIN Times t ON t.ID = p.TimeID
						JOIN Styles s ON s.ID = p.StyleID
						JOIN Dates d ON d.ID = p.DateID
						JOIN Files f ON f.ID = p.FileID
					WHERE p.GroupID = :group
						AND d.Date >= DATE(:now)
						AND p.FileID = (
							SELECT MAX(pi.FileID)
							FROM Pairs pi
								JOIN Dates di ON di.ID = pi.DateID
							WHERE pi.GroupID = p.GroupID
								AND pi.DateID = p.DateID
						)
					ORDER BY d.Date, t.Number
					LIMIT {$limit}
				", array(
					':group' => $groupId,
					':now' => date('Y-m-d', strtotime('+4 hours')),
				));

				array_unshift($sql, array(
					'FileID' => 0,
					'Date' => '!',
					'Dow' => 'Всё сломалось',
					'Number' => 0,
					'Time' => ' ',
					'Title' => "Я бы починил эту штуку, ради красивой и милой девушки, но я бородат.",
					'Style' => '',
					'With' => '',
				));

				$date = null;
				$day = -1;

				for($i = 0; $i < count($sql); $i++) {
					$pair = $sql[$i];
					if ($date != $pair['Date']) {
						$date = $pair['Date'];
						$day += 1;
						if ($day >= $daysCount)
							break;
						$timetable[$day] = array(
							'date' => $pair['Date'],
							'dow' => $pair['Dow'],
							'pairs' => array(),
						);
					}
					if (empty($pair['With']))
						$pair['With'] = '';
					$timetable[$day]['pairs'][] = array(
						'number' => $pair['Number'],
						'time' => $pair['Time'],
						'title' => $pair['Title'],
						'style' => $pair['Style'],
						'with' => $pair['With'],
					);
					$checksum .= $pair['FileID'];
				}
			} else {
				$data['error'] = 'Группа не найдена в базе или количество дней меньше 1.';
			}
			$data['checksum'] = '[spuf.ru]:'.md5($checksum);
			$data['timetable'] = $timetable;
			break;
		default:
			$data['error'] = 'Неверный запрос';
	}
	return $data;
}

/**
 * Pretty-print JSON string
 *
 * Use 'indent' option to select indentation string - by default it's a tab
 *
 * @param string $json Original JSON string
 * @param array $options Encoding options
 * @return string
 */
function prettyPrint($json, $options = array())
{
	$tokens = preg_split('|([\{\}\]\[,])|', $json, -1, PREG_SPLIT_DELIM_CAPTURE);
	$result = "";
	$indent = 0;

	$ind = "\t";
	if (isset($options['indent'])) {
		$ind = $options['indent'];
	}

	$inLiteral = false;
	foreach ($tokens as $token) {
		if ($token == "") continue;

		$prefix = str_repeat($ind, $indent);
		if (!$inLiteral && ($token == "{" || $token == "[")) {
			$indent++;
			if ($result != "" && $result[strlen($result)-1] == "\n") {
				$result .= $prefix;
			}
			$result .= "$token\n";
		} elseif (!$inLiteral && ($token == "}" || $token == "]")) {
			$indent--;
			$prefix = str_repeat($ind, $indent);
			$result .= "\n$prefix$token";
		} elseif (!$inLiteral && $token == ",") {
			$result .= "$token\n";
		} else {
			$result .= ($inLiteral ?  '' : $prefix) . $token;

			// Count # of unescaped double-quotes in token, subtract # of
			// escaped double-quotes and if the result is odd then we are
			// inside a string literal
			if ((substr_count($token, "\"")-substr_count($token, "\\\"")) % 2 != 0) {
				$inLiteral = !$inLiteral;
			}
		}
	}
	return $result;
}

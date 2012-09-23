<?php

include_once 'bootstrap.php';

$groupId = isset($_COOKIE['group']) ? $_COOKIE['group'] : -1;
$groupId = isset($_GET['group']) ? $_GET['group'] : $groupId;
setcookie('group', $groupId, time() + 60*60*24*7*3);

$page = isset($_GET['page']) ? $_GET['page'] : 'timetable';

$navigation = <<<HTML
<ul class="nav">
	<li {active_timetable}><a href="?page=timetable">Расписание</a></li>
	<li {active_docs}><a href="?page=docs">Документы</a></li>
</ul>
HTML;

$navigation = str_replace("{active_$page}" ,'class="active"', $navigation);
$navigation = preg_replace("/\s{active_\w+}/" ,'', $navigation);

$sidebar = '';
$content = '';

if ($page == 'timetable') {
	$fileId = isset($_GET['file']) ? $_GET['file'] : 'now';

	$files = DB::Query('
		SELECT f.ID, f.Title
		FROM Files f
		WHERE f.ID = (
			SELECT MAX(i.ID) FROM Files i WHERE i.Parsed = 1 AND i.Title = f.Title
		)
		ORDER BY f.Title'
	);
	$items = '';
	foreach ($files as $file) {
		$active = $file['ID'] == $fileId ? 'class="active"' : '';
		$items .= "<li $active><a href='?page=timetable&file={$file['ID']}'>{$file['Title']}</a></li>";
	}
	$active_now = $fileId == 'now' ? 'class="active"' : '';
	$active_all = $fileId == 'all' ? 'class="active"' : '';
	$sidebar = <<<HTML
<div class="well sidebar-nav">
	<ul class="nav nav-list">
		<li $active_now><a href="?page=timetable&file=now">На ближайшие 7 дней</a></li>
		<li $active_all><a href="?page=timetable&file=all">На все грядущии дни</a></li>
		<li class="divider"></li>
		{$items}
	</ul>
</div>
HTML;

	$groups = '';
	$data = DB::Query('SELECT ID, Title FROM Groups ORDER BY Title');
	foreach ($data as $item) {
		$selected = $groupId == $item['ID'] ? 'selected="selected"' : '';
		$groups .= "<option value='{$item['ID']}' $selected>{$item['Title']}</option>";
	}

	$content = <<<HTML
<form action="?" method="get" class="form-inline">
<input type="hidden" name="page" value="timetable">
<input type="hidden" name="file" value="$fileId">
<label for="group">Группа:</label>
<select name="group" onchange="this.form.submit();"><option value="-1"></option>$groups</select>
<noscript><button type="submit" class="btn">Показать</button></noscript>
</form>
HTML;

	if ($fileId == 'now') {
		$timetable = DB::Query("
			SELECT t.Number, t.Time, p.Title, s.Style, DATE_FORMAT(d.Date, '%d.%m.%Y') as Date, d.Dow, f.Title as FileName, DATE_FORMAT(f.Date, '%H:%i %d.%m.%Y') as FileDate,
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
				AND d.Date >= DATE(NOW())
				AND d.Date <= DATE(NOW() + INTERVAL 6 DAY)
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
			)
		);
	} elseif ($fileId == 'all') {
		$timetable = DB::Query("
			SELECT t.Number, t.Time, p.Title, s.Style, DATE_FORMAT(d.Date, '%d.%m.%Y') as Date, d.Dow, f.Title as FileName, DATE_FORMAT(f.Date, '%H:%i %d.%m.%Y') as FileDate,
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
				AND d.Date >= DATE(NOW())
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
			)
		);
	} else {
		$timetable = DB::Query("
			SELECT t.Number, t.Time, p.Title, s.Style, DATE_FORMAT(d.Date, '%d.%m.%Y') as Date, d.Dow, f.Title as FileName, DATE_FORMAT(f.Date, '%H:%i %d.%m.%Y') as FileDate,
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
				AND p.FileID = :file
			ORDER BY d.Date, t.Number
			", array(
				':group' => $groupId,
				':file' => $fileId,
			)
		);
	}

	$date = null;
	if (count($timetable) > 0) {
		foreach ($timetable as $pair) {
			$title = nl2br(htmlentities($pair['Title'], ENT_QUOTES, 'utf-8'));
			if ($date != $pair['Date']) {
				$date = $pair['Date'];
				if (!is_null($date))
					$content .= "</table>";
				$content .= "<h4>{$pair['Dow']} ({$pair['Date']})</h4>";
				$content .= "<table class='table table-nonfluid table-bordered table-condensed'>";
			}
			$content .= "<tr><td class='center' width='80'>{$pair['Number']}<br><small class='muted'>{$pair['Time']}</small></td><td width='300'><div style='{$pair['Style']}'>{$title}</div><small class='muted'>{$pair['With']}</small></td></tr>";
		}
		$content .= "</table>";

	} else {
		$content .= "<p>Ничего</p>";
	}

} elseif ($page == 'docs') {
	$lastCheck = Storage::Get('LastCheck', 0);
	$lastCheck = $lastCheck > 0 ? date('H:i d.m.Y', $lastCheck) : 'Never';
	$sidebar = "<div class='well'>Последняя проверка новых файлов с расписанием была в $lastCheck.</div>";

	$docs = Storage::Get('Cache', array());
	$content = '';
	foreach ($docs as $category => $files) {
		if (count($files) > 0) {
			$content .= "<h4>$category</h4><ul>";
			foreach ($files as $file) {
				$content .= "<li><a href='http://www.hse.perm.ru{$file['link']}' rel='nofollow'>{$file['name']}</a> от {$file['date']}</li>";
			}
			$content .= "</ul>";
		}
	}
}

print <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Расписание ВШЭ (ПФ)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link href="assets/css/bootstrap.css" rel="stylesheet">
    <style type="text/css">
        body {
            padding-top: 60px;
            padding-bottom: 40px;
        }
        .sidebar-nav {
            padding: 9px 0;
        }
        .table-nonfluid {
		   width: auto;
		}
        .center {
        	text-align: center !important;
        }
        .table td {
         	vertical-align: middle;
        }
    </style>
    <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">

    <!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>

<body>

<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container-fluid">
            <a class="brand" href="?">Расписание ВШЭ (ПФ)</a>
            {$navigation}
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="span3">
			{$sidebar}
        </div>

        <div class="span9">
			{$content}
        </div>
    </div>

    <hr>

    <footer>
        <p>&copy; <a href="http://spuf.ru/" title="Арсений Разин">spuf.ru</a> | <a href="mobile.php">Мобильная версия</a></p>
    </footer>

</div>

<script src="assets/js/jquery-1.8.2.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>

</body>
</html>
HTML;

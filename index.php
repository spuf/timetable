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
	<li {active_apps}><a href="?page=apps">Приложения</a></li>
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
<div class="well sidebar-nav subnav">
	<ul class="nav nav-list">
		<li $active_now><a href="?page=timetable&file=now">На ближайшие 7 дней</a></li>
		<li $active_all><a href="?page=timetable&file=all">На все грядущие дни</a></li>
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
	$sidebar = <<<HTML
<div class="well sidebar-nav subnav">
	<ul class="nav nav-list">
		<li>Последняя проверка новых файлов с расписанием была в $lastCheck.</li>
	</ul>
</div>
HTML;

	$docs = Storage::Get('Cache', array());
	$content = '';
	if (count($docs) > 0) {
		foreach ($docs as $category => $files) {
			if (count($files) > 0) {
				$content .= "<h4>$category</h4><ul>";
				foreach ($files as $file) {
					$content .= "<li><a href='http://www.hse.perm.ru{$file['link']}' rel='nofollow'>{$file['name']}</a> от {$file['date']}</li>";
				}
				$content .= "</ul>";
			}
		}
	} else {
		$content .= "<p>Ничего</p>";
	}
} elseif ($page == 'apps') {
	$sidebar = <<<HTML
<div id="sidebar" class="well sidebar-nav subnav">
	<ul class="nav nav-list">
		<li><a href="#gadget">Гаджет для Windows 7</a></li>
		<li><a href="#android">Приложение для Android</a></li>
		<li><a href="#chrome">Расширение для Chrome</a></li>
	</ul>
</div>
HTML;

	$docs = Storage::Get('Cache', array());
	$content = <<<HTML
<section id="gadget">
	<h4>Гаджет для Windows 7</h4>
	<p><img src="externals/gadget.png" class="img-polaroid" alt="Screenshot"></p>
	<p>
		Microsoft с июля 2012 года отключила установку сторонних гаджетов:
		<a href="http://windows.microsoft.com/ru-RU/windows/downloads/personalize/gadgets" target="_blank" rel='nofollow'>http://windows.microsoft.com/ru-RU/windows/downloads/personalize/gadgets</a>.<br>
		Но гаджет можно установить: скачайте архив и распакуйте его в папку <code>C:\Program Files\Windows Sidebar\Gadgets</code>, затем правый клик на рабочем столе и "Гаджеты".
	</p>
	<p>
		Скачать: <a href="externals/timetable_gadget.zip">timetable_gadget.zip</a>
	</p>
</section>

<section id="android">
	<h4>Приложение для Android</h4>
	<p><img src="externals/android.png" class="img-polaroid" alt="Screenshot"></p>
	<p>
		<a href="https://play.google.com/store/apps/details?id=ru.spuf.timetable" target="_blank">Посмотреть в Google Play</a>
	</p>
</section>

<section id="chrome">
	<h4>Расширение для Chrome</h4>
	<p><img src="externals/extension.png" class="img-polaroid" alt="extScreenshotension"></p>
	<p>
		Чтобы установить расширение
	</p>
	<p>
		Скачать: <a href="externals/timetable_extension.crx">timetable_extension.crx</a>
	</p>
</section>
HTML;

}

print <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Расписание ВШЭ (ПФ)</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link href="assets/css/bootstrap.css" rel="stylesheet">
	<link href="assets/css/bootstrap-responsive.css" rel="stylesheet">
	<link href="styles.css" rel="stylesheet">

	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>

<body data-spy="scroll" data-target=".subnav">

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
		<p>&copy; <a href="http://spuf.ru/" title="Арсений Разин">spuf.ru</a></p>
	</footer>

</div>

<script src="assets/js/jquery-1.8.2.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
	$(function() {
		var \$window = $(window);
		$('.subnav').affix({
			offset: {
				top: function() {
					return \$window.width() <= 980 ? 60 : 0;
				},
				bottom: 150
			}
		});
	});
</script>
</body>
</html>
HTML;

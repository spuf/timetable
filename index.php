<?php

include_once 'bootstrap.php';

$groupId = isset($_COOKIE['group']) ? $_COOKIE['group'] : -1;
$groupId = isset($_GET['group']) ? $_GET['group'] : $groupId;
setcookie('group', $groupId, time() + 60*60*7*3);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Расписание ВШЭ (ПФ)</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
</head>
<body>
<div class="container">

<h1>Объявление</h1>
<p>
    Скоро будет ок дизайн.
</p>

<h2>Расписание</h2>
<?php

$lastCheck = Storage::Get('LastCheck', 0);
$lastCheck = $lastCheck > 0 ? date('H:i d.m.Y', $lastCheck) : 'Never';
print "<p>Последняя проверка новых файлов с расписанием: $lastCheck</p>";

$groups = '';
$data = DB::Query('SELECT ID, Title FROM Groups ORDER BY Title');
foreach ($data as $item) {
	$selected = $groupId == $item['ID'] ? 'selected="selected"' : '';
	$groups .= "<option value='{$item['ID']}' $selected>{$item['Title']}</option>";
}

print <<<HTML
<form action="?" method="get" class="form-inline">
<select name="group" class="input-small"><option value="-1"></option>$groups</select>
<button type="submit" class="btn">Показать</button>
</form>
HTML;

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
));

$date = null;
if (count($timetable) > 0) {
	foreach ($timetable as $pair) {
		$title = nl2br(htmlentities($pair['Title'], ENT_QUOTES, 'utf-8'));
		if ($date != $pair['Date']) {
			$date = $pair['Date'];
			if (!is_null($date))
				print "</table></td></tr></table>";
			print "<table><caption>({$pair['FileName']} от {$pair['FileDate']})</caption><tr valign='middle'><td align='center' width='100'>{$pair['Date']}<br>{$pair['Dow']}</td><td>";
			print "<table border=1>";
		}
		print "<tr valign='middle'><td align='center' width='80'>{$pair['Number']}<br><small>{$pair['Time']}</small></td><td width='300'><div style='{$pair['Style']}'>{$title}</div><small>{$pair['With']}</small></td></tr>";
	}
	print "</table></td></tr></table>";

} else {
	print "<p>Ничего</p>";
}

?>
<h2>Ссылочки</h2>
<ul>
    <li><a href="http://timetable-spuf.dotcloud.com/">Старый сайт</a></li>
</ul>

<p><small><a href="http://spuf.ru/" title="Арсений Разин">spuf.ru</a><small></p>
</div>
</body>
</html>

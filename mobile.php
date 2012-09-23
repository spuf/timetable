<?php

include_once 'bootstrap.php';

$groupId = isset($_COOKIE['group']) ? $_COOKIE['group'] : -1;
$groupId = isset($_GET['group']) ? $_GET['group'] : $groupId;
setcookie('group', $groupId, time() + 60*60*24*7*3);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Расписание ВШЭ (ПФ)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.min.css" />
    <script src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
    <script>
        $(document).bind("mobileinit", function(){
            $.extend($.mobile, {
                ajaxEnabled : false,
                ajaxFormsEnabled : false,
                ajaxLinksEnabled : false
        	});
        });
    </script>
    <script src="http://code.jquery.com/mobile/1.1.1/jquery.mobile-1.1.1.min.js"></script>
    <style type='text/css'>
        @media only screen and (min-width: 600px) {
            .ui-page {
                width: 600px !important;
                margin: 0 auto !important;
                position: relative !important;
                border-right: 5px #999 solid !important;
                border-left: 5px #999 solid !important;
            }
        }
    </style>
</head>
<body>

<div data-role="page" id="main">

    <div data-role="header">
        <h1>Расписание ВШЭ (ПФ)</h1>
    </div>

    <div data-role="content">
        <div class="content-primary">
<?php
$lastCheck = Storage::Get('LastCheck', 0);
$lastCheck = $lastCheck > 0 ? date('H:i d.m.Y', $lastCheck) : 'Never';
print "<p>Последняя проверка новых файлов с расписанием была в $lastCheck.</p>";

$groups = DB::Query('SELECT Title FROM Groups WHERE `ID` = :id ORDER BY Title', array(':id' => $groupId));
$groupTitle = count($groups) > 0 ? $groups[0]['Title'] : 'Выбрать группу';
print "<a href='#groups' data-role='button' data-transition='none'>{$groupTitle}</a>";

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
));

$date = null;
if (count($timetable) > 0) {
	foreach ($timetable as $pair) {
		$title = nl2br(htmlentities($pair['Title'], ENT_QUOTES, 'utf-8'));
		if ($date != $pair['Date']) {
			$date = $pair['Date'];
			if (!is_null($date))
				print "</ul>";
			print "<h2>{$pair['Dow']} ({$pair['Date']})</h2>";
			print '<ul data-role="listview" data-inset="true">';
		}
		print "<li><table><tr valign='middle'><td align='center' width='80'>{$pair['Number']}<br><small>{$pair['Time']}</small></td><td><div style='{$pair['Style']}'>{$title}</div><small>{$pair['With']}</small></td></tr></table></li>";
	}
	print "</ul>";

} else {
	print "<p>Расписания нет :(</p>";
}

?>
        </div>
    </div>

    <div data-role="footer">
        <h4><a href="index.php" data-role="button" data-transition="none">Полная версия</a></h4>
    </div>
</div>

<div data-role="page" id="groups">

    <div data-role="header">
        <h1>Расписание ВШЭ (ПФ)</h1>
    </div>

    <div data-role="content">
        <div class="content-primary">
			<h2>Выберите группу</h2>
			<ul data-role="listview" data-inset="true">
<?php
$data = DB::Query('SELECT ID, Title FROM Groups ORDER BY Title');
foreach ($data as $item) {
	print "<li><a href='?group={$item['ID']}' data-transition='none'>{$item['Title']}</a></li>";
}
?>
			</ul>
        </div>
    </div>

    <div data-role="footer">
        <h4><a href="index.php" data-role="button" data-transition="none">Полная версия</a></h4>
    </div>
</div>

</body>
</html>

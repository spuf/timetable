<?php

include_once 'bootstrap.php';

$groupId = isset($_GET['group']) ? $_GET['group'] : -1;
$dateId = isset($_GET['date']) ? $_GET['date'] : -1;

$groups = '';
$data = DB::Query('SELECT ID, Title FROM Groups ORDER BY Title');
foreach ($data as $item) {
	$selected = $groupId == $item['ID'] ? 'selected="selected"' : '';
	$groups .= "<option value='{$item['ID']}' $selected>{$item['Title']}</option>";
}

$dates = '';
$data = DB::Query('SELECT ID, Date, Dow FROM Dates ORDER BY Date');
foreach ($data as $item) {
	$selected = $dateId == $item['ID'] ? 'selected="selected"' : '';
	$dates .= "<option value='{$item['ID']}' $selected>{$item['Date']} - {$item['Dow']}</option>";
}

print <<<HTML
<form action="?" method="get">
<select name="group">$groups</select>
<select name="date">$dates</select>
<input type="submit" value="Show">
</form>
HTML;

$timetable = DB::Query('
	SELECT t.Number, t.Time, p.Title, s.Style
	FROM Pairs p
		JOIN Times t ON t.ID = p.TimeID
		JOIN Styles s ON s.ID = p.StyleID
	WHERE p.GroupID = :group
		AND p.DateID = :date
	ORDER BY t.Number
', array(
	':group' => $groupId,
	':date' => $dateId,
));

Debug::Log($timetable);

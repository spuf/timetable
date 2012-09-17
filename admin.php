<?php

include_once 'bootstrap.php';

$lastCheck = Storage::Get('LastCheck', 0);
$lastCheck = $lastCheck > 0 ? date('H:i d.m.Y', $lastCheck) : 'Never';
Debug::Log("Last check for new files: $lastCheck");

$groupId = isset($_GET['group']) ? $_GET['group'] : -1;

$groups = '';
$data = DB::Query('SELECT ID, Title FROM Groups ORDER BY Title');
foreach ($data as $item) {
	$selected = $groupId == $item['ID'] ? 'selected="selected"' : '';
	$groups .= "<option value='{$item['ID']}' $selected>{$item['Title']}</option>";
}

print <<<HTML
<form action="?" method="get">
<select name="group"><option value="-1"></option>$groups</select>
<input type="submit" value="Show">
</form>
HTML;

$timetable = DB::Query('
	SELECT t.Number, t.Time, p.Title, s.Style, DATE_FORMAT(d.Date, "%d.%m.%Y") as Date, d.Dow, f.Title as FileName, DATE_FORMAT(f.Date, "%H:%i %d.%m.%Y") as FileDate,
		(
		SELECT GROUP_CONCAT(wg.Title SEPARATOR ", ") FROM Withs w
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
		AND d.Date <= DATE(NOW() + INTERVAL 4 DAY)
		AND p.FileID = (
			SELECT MAX(pi.FileID)
			FROM Pairs pi
				JOIN Dates di ON di.ID = pi.DateID
			WHERE pi.GroupID = p.GroupID
				AND pi.DateID = p.DateID
		)
	ORDER BY p.ID, t.Number
', array(
	':group' => $groupId,
));

$date = null;
if (count($timetable) > 0) {
	foreach ($timetable as $pair) {
		$title = nl2br(htmlentities($pair['Title'], ENT_QUOTES, 'utf-8'));
		if ($date != $pair['Date']) {
			$date = $pair['Date'];
			if (!is_null($date))
				print "</table>";
			print "<p>{$pair['Date']} - {$pair['Dow']} - ({$pair['FileName']} от {$pair['FileDate']})</p>";
			print "<table border=1>";
		}
		print "<tr valign='middle'><td align='center'>{$pair['Number']}<br><small>{$pair['Time']}</small></td><td><div style='{$pair['Style']}'>{$title}</div><small>{$pair['With']}</small></td></tr>";
	}
	print "</table>";

} else {
	print "<p>Ничего</p>";
}


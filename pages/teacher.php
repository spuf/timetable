<?php

$teacherId = isset($_GET['teacher']) ? $_GET['teacher'] : -1;

$teachers = DB::Query('SELECT ID, Title FROM Teachers ORDER BY Title');

if ($teacherId == -1) {
	$columnSize = ceil(count($teachers) / 4);
	$content = '<div class="row-fluid"><div class="span3"><ul class="unstyled" style="margin-bottom: 0;">';
	for ($i = 0; $i < count($teachers); $i++) {
		if ($i > 0 && $i % $columnSize == 0)
			$content .= '</ul></div><div class="span3"><ul class="unstyled" style="margin-bottom: 0;">';
		$item = $teachers[$i];
		$content .= "<li><a href='?page=teacher&teacher={$item['ID']}'>{$item['Title']}</a></li>";
	}
	$content .= '</ul></div></div>';
} else {
	$options = '';
	foreach ($teachers as $item) {
		$selected = $teacherId == $item['ID'] ? 'selected="selected"' : '';
		$options .= "<option value='{$item['ID']}' $selected>{$item['Title']}</option>";
	}
	$content = <<<HTML
<form action="?" method="get" class="form-inline">
	<input type="hidden" name="page" value="teacher">
	<label for="teacher">Преподаватель:</label>
	<select name="teacher" onchange="this.form.submit();"><option value="-1"></option>$options</select>
	<noscript><button type="submit" class="btn">Показать</button></noscript>
</form>
HTML;
	$teacherRow = DB::Query('SELECT Title FROM Teachers WHERE `ID` = :id ORDER BY Title', array(':id' => $teacherId));
	$teacherName = count($teacherRow) > 0 ? $teacherRow[0]['Title'] : '---';

	$timetable = DB::Query(QueryLibrary::AllDaysForTeacher(), array(
			':teacher' => "%{$teacherName}%",
		)
	);

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
			$content .= "<tr><td class='center' width='80'>{$pair['Number']}<br><small class='muted'>{$pair['Time']}</small></td><td class='center' width='80'>{$pair['With']}</td><td width='300'><div style='{$pair['Style']}'>{$title}</div></td></tr>";
		}
		$content .= "</table>";

	} else {
		$content .= "<p>Ничего</p>";
	}
}
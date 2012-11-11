<?php

$roomId = isset($_GET['room']) ? $_GET['room'] : -1;

$rooms = DB::Query('SELECT ID, Building, Number FROM Rooms ORDER BY Building, Number');

if ($roomId == -1) {
	$content = "<div class='row-fluid'><div class='span3'><ul class='unstyled' style='margin-bottom: 0;'>\n";
	$building = -1;
	foreach ($rooms as $item) {
		if ($building != $item['Building']) {
			if ($building != -1)
				$content .= "</ul></div><div class='span3'><ul class='unstyled' style='margin-bottom: 0;'>\n";
			$building = $item['Building'];
			$content .= "<li class='nav-header'>Корпус {$item['Building']}</li>\n";
		}
		$content .= "<li><a href='?page=room&room={$item['ID']}'>{$item['Number']}[{$item['Building']}]</a></li>\n";
	}
	$content .= "</ul></div></div>\n";
} else {
	$options = '';
	$building = -1;
	foreach ($rooms as $item) {
		if ($building != $item['Building']) {
			if ($building != -1)
				$options .= "</optgroup>\n";
			$building = $item['Building'];
			$options .= "<optgroup label='Корпус {$item['Building']}'>\n";
		}
		$selected = $roomId == $item['ID'] ? 'selected="selected"' : '';
		$options .= "<option value='{$item['ID']}' $selected>{$item['Number']}[{$item['Building']}]</option>\n";
	}
	$options .= "</optgroup>\n";

	$content = <<<HTML
<form action="?" method="get" class="form-inline">
	<input type="hidden" name="page" value="room">
	<label for="room">Аудитория:</label>
	<select name="room" onchange="this.form.submit();"><option value="-1"></option>$options</select>
	<noscript><button type="submit" class="btn">Показать</button></noscript>
</form>
HTML;
	$roomRow = DB::Query('SELECT Building, Number FROM Rooms WHERE `ID` = :id ORDER BY Building, Number', array(':id' => $roomId));
	$roomName = count($roomRow) > 0 ? "{$roomRow[0]['Number']}[{$roomRow[0]['Building']}]" : '---';

	$replace = array(
		'А' => 'A',
		'В' => 'B',
		'С' => 'C',
		'Д' => 'D',
	);
	$roomNameRus = str_replace(array_values($replace), array_keys($replace), $roomName);
	$timetable = DB::Query(QueryLibrary::AllDaysForRoom(), array(
			':room' => "%{$roomName}%",
			':roomrus' => "%{$roomNameRus}%",
		)
	);

	$date = null;
	if (count($timetable) > 0) {
		foreach ($timetable as $pair) {
			$title = nl2br(htmlentities($pair['Title'], ENT_QUOTES, 'utf-8'));
			if ($date != $pair['Date']) {
				$date = $pair['Date'];
				if (!is_null($date))
					$content .= "</table>\n";
				$content .= "<h4>{$pair['Dow']} ({$pair['Date']})</h4>\n";
				$content .= "<table class='table table-nonfluid table-bordered table-condensed'>\n";
			}
			$content .= "<tr><td class='center' width='80'>{$pair['Number']}<br><small class='muted'>{$pair['Time']}</small></td><td class='center' width='80'>{$pair['With']}</td><td width='300'><div style='{$pair['Style']}'>{$title}</div></td></tr>\n";
		}
		$content .= "</table>\n";

	} else {
		$content .= "<p>Ничего</p>\n";
	}
}
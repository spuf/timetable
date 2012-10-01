<?php

$roomId = isset($_GET['room']) ? $_GET['room'] : -1;

$rooms = DB::Query('SELECT ID, Building, Number FROM Rooms ORDER BY Building, Number');

if ($roomId == -1) {
	$content = '<ul class="unstyled">';
	$building = -1;
	foreach ($rooms as $item) {
		if ($building != $item['Building']) {
			$building = $item['Building'];
			$content .= "<li class='nav-header'>Корпус {$item['Building']}</li>";
		}
		$content .= "<li><a href='?page=room&room={$item['ID']}'>{$item['Number']}[{$item['Building']}]</a></li>";
	}
	$content .= '</ul>';
} else {
	$options = '';
	$building = -1;
	foreach ($rooms as $item) {
		if ($building != $item['Building']) {
			if ($building != -1)
				$options .= "</optgroup>";
			$building = $item['Building'];
			$options .= "<optgroup label='Корпус {$item['Building']}'>";
		}
		$selected = $roomId == $item['ID'] ? 'selected="selected"' : '';
		$options .= "<option value='{$item['ID']}' $selected>{$item['Number']}[{$item['Building']}]</option>";
	}
	$options .= "</optgroup>";

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

	$timetable = DB::Query(QueryLibrary::AllDaysForTeacher(), array(
			':teacher' => "%{$roomName}%",
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
<?php

$fileId = isset($_GET['file']) ? $_GET['file'] : 'now';

$files = Cache::Query(QueryLibrary::LatestFiles());
$items = '';
foreach ($files as $file) {
	$active = $file['ID'] == $fileId ? 'class="active"' : '';
	$items .= "<li $active><a href='?page=timetable&file={$file['ID']}'>{$file['Title']}</a></li>\n";
}
$active_now = $fileId == 'now' ? 'class="active"' : '';
$active_all = $fileId == 'all' ? 'class="active"' : '';
$sidebarSize = 4;
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

if ($groupId == -1) {
	$content = "<ul class='unstyled'>\n";
	foreach ($groups as $item) {
		$content .= "<li><a href='?page=timetable&file={$fileId}&group={$item['ID']}'>{$item['Title']}</a></li>\n";
	}
	$content .= "</ul>\n";
} else {
	$options = '';
	foreach ($groups as $item) {
		$selected = $groupId == $item['ID'] ? 'selected="selected"' : '';
		$options .= "<option value='{$item['ID']}' $selected>{$item['Title']}</option>\n";
	}
	$content = <<<HTML
<form action="?" method="get" class="form-inline">
	<input type="hidden" name="page" value="timetable">
	<input type="hidden" name="file" value="$fileId">
	<label for="group">Группа:</label>
	<select name="group" onchange="this.form.submit();"><option value="-1"></option>$options</select>
	<noscript><button type="submit" class="btn">Показать</button></noscript>
</form>
HTML;

	if ($fileId == 'now') {
		$timetable = DB::Query(QueryLibrary::FewDays(6), array(
				':group' => $groupId,
			)
		);
	} elseif ($fileId == 'all') {
		$timetable = DB::Query(QueryLibrary::AllDays(), array(
				':group' => $groupId,
			)
		);
	} else {
		$timetable = DB::Query(QueryLibrary::DaysForFile(), array(
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
					$content .= "</table>\n";
				$content .= "<h4>{$pair['Dow']} ({$pair['Date']})</h4>\n";
				$content .= "<table class='table table-nonfluid table-bordered table-condensed'>\n";
			}
			$content .= "<tr><td class='center' width='80'>{$pair['Number']}<br><small class='muted'>{$pair['Time']}</small></td><td width='300'><div style='{$pair['Style']}'>{$title}</div><small class='muted'>{$pair['With']}</small></td></tr>\n";
		}
		$content .= "</table>\n";

	} else {
		$content .= "<p>Ничего</p>\n";
	}
}

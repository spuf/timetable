<?php

$content = '';

$rows = DB::Query("SELECT * FROM Log WHERE Message LIKE 'no %' ORDER BY Time");

if (empty($rows)) {
	$content .= "<p>Пусто</p>\n";
} else {
	$content .= "<table class='table'>\n";
	foreach ($rows as $row) {
		$var = htmlentities(print_r(unserialize($row['Variable']), true), ENT_QUOTES, 'utf-8');
		$content .= "<tr><td width=80>{$row['Time']}</td><td width=80>{$row['Message']}</td><td><pre>{$var}</pre></td></tr>\n";
	}
	$content .= "</table>\n";
}

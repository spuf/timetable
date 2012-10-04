<?php

$content = '';

$rows = DB::Query("SELECT * FROM Log WHERE Message LIKE 'no %' ORDER BY Time");

if (empty($rows)) {
	$content .= '<p>Пусто</p>';
} else {
	$content .= '<table class="table">';
	foreach ($rows as $row) {
		$content .= "<tr><td width=80>{$row['Time']}</td><td width=80>{$row['Message']}</td><td><pre>".htmlentities(print_r(unserialize($row['Variable']), true), ENT_QUOTES, 'utf-8')."</pre></td></tr>";
	}
	$content .= '</table>';
}

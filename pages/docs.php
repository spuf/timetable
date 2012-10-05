<?php

$lastCheck = Storage::Get('LastCheck', 0);
$lastCheck = $lastCheck > 0 ? date('H:i d.m.Y', $lastCheck) : 'Never';
$sidebar = <<<HTML
<div class="well sidebar-nav subnav">
	<ul class="nav nav-list">
		<li>Последняя проверка новых файлов с расписанием была в $lastCheck.</li>
	</ul>
</div>
HTML;

$docs = Storage::Get('Cache', array());
$content = '';
if (count($docs) > 0) {
	foreach ($docs as $category => $files) {
		if (count($files) > 0) {
			$content .= "<h4>$category</h4><ul>";
			foreach ($files as $file) {
				$ext = pathinfo($file['link'], PATHINFO_EXTENSION);
				$content .= "<li><a href='http://www.hse.perm.ru{$file['link']}'  download='{$file['name']} от {$file['date']}.{$ext}' rel='nofollow'>{$file['name']}</a> от {$file['date']}</li>";
			}
			$content .= "</ul>";
		}
	}
} else {
	$content .= "<p>Ничего</p>";
}
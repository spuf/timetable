<?php

$lastCheck = Storage::Get('LastCheck', 0);
$lastCheck = $lastCheck > 0 ? date('H:i d.m.Y', $lastCheck) : 'Never';
$sidebarSize = 3;
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
				$link = $file['link'];
				$google = "https://docs.google.com/viewer?url=".urlencode($link)."";
				$date = date('d.m.Y H:i', strtotime($file['date']));
				$content .= "
					<li>
					<a href='$link' download='{$file['name']} от {$file['date']}.{$ext}' title='Скачать' rel='nofollow' target='_blank'>{$file['name']}</a>
					<a href='$google' title='Открыть быстрый просмотр в новом окне' target='_blank'><i class='icon-eye-open'></i></a>
					от {$date}
					</li>
				";
			}
			$content .= "</ul>";
		}
	}
} else {
	$content .= "<p>Ничего</p>";
}
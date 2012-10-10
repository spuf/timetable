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
				$link = "http://www.hse.perm.ru{$file['link']}";
				$google = "https://docs.google.com/viewer?url=".urlencode($link)."";
				$preview = "<div class=\"preloader\"><img src=\"$google&a=bi&pagenumber=1\" alt=\"\"/></div>";
				$content .= "
					<li>
					<a href='$link'  download='{$file['name']} от {$file['date']}.{$ext}' title='Скачать' rel='nofollow' target='_blank'>{$file['name']}</a>
					<a href='$google' rel='preview' title='Открыть быстрый просмотр<br>в новом окне' data-content='$preview' target='_blank'><i class='icon-eye-open'></i></a>
					от {$file['date']}
					</li>
				";
			}
			$content .= "</ul>";
		}
	}
} else {
	$content .= "<p>Ничего</p>";
}
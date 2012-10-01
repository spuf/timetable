<?php

$groupId = isset($_COOKIE['group']) ? $_COOKIE['group'] : -1;
$groupId = isset($_GET['group']) ? $_GET['group'] : $groupId;
setcookie('group', $groupId, time() + 60*60*24*7*3);

$groups = DB::Query('SELECT ID, Title FROM Groups ORDER BY Title');

$navigation = <<<HTML
<ul class="nav">
	<li class="dropdown {active_class_timetable} {active_class_teacher} {active_class_room}">
		<a href="?page=timetable" class="dropdown-toggle" data-toggle="dropdown" data-target="#">Расписание <b class="caret"></b></a>
		<ul class="dropdown-menu">
			<li {active_timetable}><a href="?page=timetable">Студентов</a></li>
			<li {active_teacher}><a href="?page=teacher">Преподавателей</a></li>
			<li {active_room}><a href="?page=room">Аудиторий</a></li>
		</ul>
	</li>
	<li {active_docs}><a href="?page=docs">Документы</a></li>
	<li {active_apps}><a href="?page=apps">Приложения</a></li>
</ul>
HTML;

$navigation = str_replace("{active_class_$page}" ,'active', $navigation);
$navigation = str_replace("{active_$page}" ,'class="active"', $navigation);
$navigation = preg_replace("/\s{active_\w+}/" ,'', $navigation);

$groupRow = DB::Query('SELECT Title FROM Groups WHERE `ID` = :id ORDER BY Title', array(':id' => $groupId));
$groupName = count($groupRow) > 0 ? $groupRow[0]['Title'] : 'Выбрать группу';

$link = empty($_SERVER['QUERY_STRING']) ? '' : $_SERVER['QUERY_STRING'].'&';
$groupSelector = '<ul class="unstyled clearfix" style="list-style: none;">';
foreach ($groups as $item) {
	$groupSelector .= "<li style='float: left;width: 25%;line-height: 25px;'><a href='?{$link}group={$item['ID']}'>{$item['Title']}</a></li>";
}
$groupSelector .= '</ul>';

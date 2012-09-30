<?php

include_once 'bootstrap.php';

$navigation = <<<HTML
<ul class="nav">
	<li><a href="/?page=timetable">Расписание</a></li>
	<li><a href="/?page=docs">Документы</a></li>
	<li><a href="/?page=apps">Приложения</a></li>
</ul>
HTML;

$sidebar = '';
$content = '';

$teacherId = isset($_GET['teacher']) ? $_GET['teacher'] : -1;

$teachers = DB::Query('SELECT ID, Title FROM Teachers ORDER BY Title');

if ($teacherId == -1) {
	$content = '<ul class="unstyled">';
	foreach ($teachers as $item) {
		$content .= "<li><a href='?page=teacher&teacher={$item['ID']}'>{$item['Title']}</a></li>";
	}
	$content .= '</ul>';
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

print <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Расписание ВШЭ Пермь</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Расписание занятий Высшей школы экономики в Перми">
	
	<link href="assets/css/bootstrap.css" rel="stylesheet">
	<link href="assets/css/bootstrap-responsive.css" rel="stylesheet">
	<link href="styles.css" rel="stylesheet">

	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>

<body data-spy="scroll" data-target=".subnav">

<div class="navbar navbar-inverse navbar-fixed-top">
	<div class="navbar-inner">
		<div class="container-fluid">
			<a class="brand" href="/?">Расписание ВШЭ (ПФ)</a>
            {$navigation}
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="span3">
			{$sidebar}
		</div>

		<div class="span9">
			{$content}
		</div>
	</div>

	<hr>

	<footer>
		<p>&copy; <a href="http://spuf.ru/" title="Арсений Разин">spuf.ru</a></p>
	</footer>

</div>

<script src="assets/js/jquery-1.8.2.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script>
	$(function() {
		var \$window = $(window);
		$('.subnav').affix({
			offset: {
				top: function() {
					return \$window.width() <= 980 ? 60 : 0;
				},
				bottom: 150
			}
		});
	});
</script>
<!-- Yandex.Metrika counter --><script type="text/javascript">(function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter9714649 = new Ya.Metrika({id:9714649}); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="//mc.yandex.ru/watch/9714649" style="position:absolute; left:-9999px;" alt="" /></div></noscript><!-- /Yandex.Metrika counter -->
</body>
</html>
HTML;

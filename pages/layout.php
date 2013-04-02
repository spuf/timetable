<?php

if (empty($sidebar))
	$sidebar = '';
if (empty($content))
	$content = 'Не найдено.';
if (empty($sidebarSize))
	$sidebarSize = 0;

$sidebarSize = intval($sidebarSize);
$contentSize = 12 - $sidebarSize;

print <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Расписание ВШЭ Пермь</title>
	<meta name="viewport" content="width=980px">
	<meta name="description" content="Расписание занятий Высшей школы экономики в Перми">
	<meta name="apple-itunes-app" content="app-id=566225461"/>

	<link href="assets/css/bootstrap.css" rel="stylesheet">
	<link href="styles.css" rel="stylesheet">

	<!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>

<body>

<div class="navbar navbar-inverse navbar-static-top navbar-full">
	<div class="navbar-inner">
		<div class="container-fluid">
			<a class="brand" href="?">Расписание ВШЭ (ПФ)</a>
            {$navigation}
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="span{$sidebarSize}">
			{$sidebar}
		</div>

		<div class="span{$contentSize}">
			<div class="alert alert-error">
			  <strong>Всё сломалось!</strong> Я бы починил эту штуку, ради красивой и милой девушки, но я бородат.
			</div>
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
		$('body').on('touchstart.dropdown', '.dropdown-menu', function (e) { e.stopPropagation(); });
		$('a[title]').tooltip();
	});
</script>
<!-- Yandex.Metrika counter --><script type="text/javascript">(function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter9714649 = new Ya.Metrika({id:9714649}); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><noscript><div><img src="//mc.yandex.ru/watch/9714649" style="position:absolute; left:-9999px;" alt="" /></div></noscript><!-- /Yandex.Metrika counter -->
</body>
</html>
HTML;

<?php

if (empty($sidebar))
	$sidebar = '';
if (empty($content))
$content = 'Не найдено.';

print <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Расписание ВШЭ Пермь</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Расписание занятий Высшей школы экономики в Перми">
	<!-- meta name="apple-itunes-app" content="app-id=566225461"/ -->
	
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
			<a class="brand" href="?">Расписание ВШЭ (ПФ)</a>
			<p class="navbar-text pull-right">
            	<a href="#groupSelector" class="navbar-link group-link" data-toggle="modal">{$groupName}</a>
            </p>
            {$navigation}
		</div>
	</div>
</div>

<div class="modal hide fade" id="groupSelector" tabindex="-1" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3>Выбор группы</h3>
	</div>
	<div class="modal-body">
		{$groupSelector}
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal">Закрыть</button>
		<!--<button class="btn btn-primary">Save changes</button>-->
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

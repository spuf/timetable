<?php

$sidebar = <<<HTML
<div id="sidebar" class="well sidebar-nav subnav">
	<ul class="nav nav-list">
		<li><a href="#gadget">Гаджет для Windows 7</a></li>
		<li><a href="#android">Приложение для Android</a></li>
		<li><a href="#chrome">Расширение для Chrome</a></li>
	</ul>
</div>
HTML;

$docs = Storage::Get('Cache', array());
$content = <<<HTML
<section id="gadget">
	<h4>Гаджет для Windows 7</h4>
	<p><img src="externals/gadget.png" class="img-polaroid" alt="Screenshot"></p>
	<p>
		Microsoft с июля 2012 года отключила установку сторонних гаджетов:
		<a href="http://windows.microsoft.com/ru-RU/windows/downloads/personalize/gadgets" target="_blank" rel='nofollow'>http://windows.microsoft.com/ru-RU/windows/downloads/personalize/gadgets</a>.<br>
		Но гаджет можно установить: скачайте архив и распакуйте его в папку <code>C:\Program Files\Windows Sidebar\Gadgets</code>, затем правый клик на рабочем столе и открывайте <strong>Гаджеты</strong>.
	</p>
	<p>
		Скачать: <a href="externals/timetable_gadget.zip">timetable_gadget.zip</a>
	</p>
</section>

<section id="android">
	<h4>Приложение для Android</h4>
	<p><img src="externals/android.png" class="img-polaroid" alt="Screenshot"></p>
	<p>
		<a href="https://play.google.com/store/apps/details?id=ru.spuf.timetable" target="_blank">Посмотреть в Google Play</a>
	</p>
</section>

<section id="chrome">
	<h4>Расширение для Chrome</h4>
	<p><img src="externals/extension.png" class="img-polaroid" alt="extScreenshotension"></p>
	<p>
		Чтобы установить расширение: скачайте файл с раширением <strong>.crx</strong>, откройте вкладку с адресом <code>chrome://chrome/extensions/</code>
		и перетащите файл во вкладку.
	</p>
	<p>
		Скачать: <a href="externals/timetable_extension.crx">timetable_extension.crx</a>
	</p>
</section>
HTML;

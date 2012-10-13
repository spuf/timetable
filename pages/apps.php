<?php

$sidebar = <<<HTML
<div id="sidebar" class="well sidebar-nav subnav">
	<ul class="nav nav-list">
		<li><a href="#gadget">Гаджет для Windows 7</a></li>
		<li><a href="#android">Приложение для Android</a></li>
		<li><a href="#iphone">Приложение для iPhone</a></li>
		<li><a href="#chrome">Расширение для Chrome</a></li>
	</ul>
</div>
HTML;

$docs = Storage::Get('Cache', array());
$content = <<<HTML
<section id="gadget">
	<div class="row-fluid">
	<div class="span8">
		<h4>Гаджет для Windows 7</h4>
		<p>
			Microsoft с июля 2012 года отключила установку сторонних гаджетов:<br>
			<a href="http://windows.microsoft.com/ru-RU/windows/downloads/personalize/gadgets" target="_blank" rel='nofollow'>http://windows.microsoft.com/ru-RU/windows/downloads/personalize/gadgets</a>.<br>
		</p>
		<p>
			Но гаджет можно установить:
			<ol>
				<li>скачайте архив</li>
				<li>распакуйте его в папку:<br><code>C:\Program Files\Windows Sidebar\Gadgets</code></li>
				<li>правый клик на рабочем столе</li>
				<li>из меню открывайте <strong>Гаджеты</strong>.</li>
			</ol>
		</p>
		<p>
			Скачать: <a href="externals/timetable_gadget.zip">timetable_gadget.zip</a>
		</p>
	</div>
	<div class="span4">
		<p><img src="externals/gadget.png" class="img-polaroid" alt="Screenshot"></p>
	</div>
	</div>
</section>

<section id="android">
	<div class="row-fluid">
	<div class="span8">
		<h4>Приложение для Android</h4>
		<p>
			Приложение для мобильных устройств на Android.<br>
			Показывает расписание на несколько следующих дней для выбранной группы.
		</p>
		<p>
			<a href="https://play.google.com/store/apps/details?id=ru.spuf.timetable" target="_blank">Посмотреть в Google Play</a>
		</p>
	</div>
	<div class="span4">
		<p><img src="externals/android.png" class="img-polaroid" alt="Screenshot"></p>
	</div>
	</div>
</section>

<section id="iphone">
	<div class="row-fluid">
	<div class="span8">
		<h4>Приложение для iPhone</h4>
		<p>
			Приложение для мобильных устройств на iOS.<br>
			Показывает расписание на несколько следующих дней для выбранной группы.
		</p>
		<p>
			<a href="http://itunes.apple.com/us/app/raspisanie-vse/id566225461?l=ru&ls=1&mt=8" target="_blank">Посмотреть в App Store</a>
		</p>
	</div>
	<div class="span4">
		<p><img src="externals/iphone.png" class="img-polaroid" alt="Screenshot"></p>
	</div>
	</div>
</section>

<section id="chrome">
	<div class="row-fluid">
	<div class="span8">
		<h4>Расширение для Chrome</h4>
		<p>
			Чтобы установить расширение:
			<ol>
				<li>скачайте файл с раширением <strong>.crx</strong></li>
				<li>откройте вкладку с адресом <code>chrome://chrome/extensions/</code></li>
				<li>перетащите файл во вкладку.</li>
			</ol>
		</p>
		<p>
			Скачать: <a href="externals/timetable_extension.crx">timetable_extension.crx</a>
		</p>
	</div>
	<div class="span4">
		<p><img src="externals/extension.png" class="img-polaroid" alt="extScreenshotension"></p>
	</div>
	</div>
</section>
HTML;

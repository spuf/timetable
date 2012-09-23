$(function () {
	cache_update();
	setInterval(cache_update, 1000 * 60 * 60);
});
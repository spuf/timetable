$(function () {
    $('#update').click(function(event){
		event.preventDefault();
		$('#info').text('Обновляю...');
		cache_update(html_update);
	});

	if (!localStorage['cache']) {
		cache_update(html_update);
	} else {
		html_update();
	}
});
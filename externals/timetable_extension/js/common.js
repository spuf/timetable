function cache_update(callback) {
	var id = 0;
	try {
		id = parseInt(localStorage['group']);
	} catch (e) {
	}
	if (id > 0) {
		$.ajax({
			url: 'http://timetable.spuf.ru/api.php',
			data: {
				api: 2,
				query: 'latest',
				group: id
			},
			dataType: 'json',
			crossDomain: false,
			error: function () {
				chrome.browserAction.setBadgeText({text: "?"});
			},
			success: function (data) {
				var cache = JSON.stringify(data);
				if (localStorage['cache'] != cache) {
					chrome.browserAction.setBadgeText({text: "#"});
				}
				localStorage['cache'] = cache;
				if (typeof callback == "function") {
					callback();
				}
			}
		});
	} else {
		if (typeof callback == "function") {
			callback();
		}		
	}
}

function html_update() {
	$('#info').text('');
	var id = 0;
	try {
		id = parseInt(localStorage['group']);
	} catch (e) {
	}
	if (id > 0) {
		var data = JSON.parse(localStorage['cache']);
		$('#update').text('Обновить');
		if (data.error) {
			$('#info').text(data.error);
		} else {
			$('#info').text(data.group);
		}
		$('#link').attr('href', data.link);
		$('#content').empty();
		$.each(data.timetable, function (date, day) {
			var title = $('<div/>', {text: day.dow + ' '}).addClass('day')
				.append($('<small/>', {text: date}));
			var table = $('<table/>');
			$.each(day.pairs, function (number, pair) {
				table.append($('<tr/>').addClass('pair')
					.append(
					$('<td/>', {text: number}).addClass('number')
						.append($('<br/>'))
						.append($('<small/>', {text: pair.time}))
				)
					.append(
					$('<td/>', {html: pair.title.replace(/\n/g, '<br />')}).css('text-decoration', (pair.style ? pair.style['text-decoration'] : 'none'))
				)
				);
			});
			$('#content').append(title).append($('<div/>').addClass('pairs').append(table));
		});
	} else {
		$('#info').text('Настрой меня!');
	}
	chrome.browserAction.setBadgeText({text: ""});
}
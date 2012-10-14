$(document).ready(function () {
	var bg = $('#background').get(0);
	bg.removeObjects();
	var img = bg.addImageObject('img/header.png', 0, 0);
	img.width = 226;
	img.height = 20;

	System.Gadget.settingsUI = 'settings.htm';
	System.Gadget.onSettingsClosed = update;

	$('#update').click(function(event){
		event.preventDefault();
		update();
	});

	update();
	setInterval(update, 1000 * 60 * 60);
});

function update() {
	$('#info').text('');
	var id = 0;
	var days = 2;
	try {
		id = parseInt(System.Gadget.Settings.readString('group'));
		days = parseInt(System.Gadget.Settings.readString('days'));
	} catch(e) {}
    if (id > 0) {
        $.ajax({
            url: 'http://timetable.spuf.ru/api.php',
            data: {
				api: 3,
				query: 'latest',
				group: id,
				days: days
			},
            dataType: 'jsonp',
            crossDomain: true,
			beforeSend: function() {
				$('#update').text('Обновляю...');
			},
			error: function() {
				$('#update').text('Обновить');
				$('#info').text('Ошибка обновления');
			},
            success: function(data) {
				$('#update').text('Обновить');
                if (data.error) {
                    $('#info').text(data.error);
				} else {
					$('#info').text(data.group);
				}
				$('#link').attr('href', data.link);
                $('#content').empty();
                $.each(data.timetable, function(i, day){
					var title = $('<div/>', {text: day.dow + ' '}).addClass('day')
                        .append($('<small/>', {text: day.date}));
                    var table = $('<table/>');
                    $.each(day.pairs, function (i, pair) {
                        table.append($('<tr/>').addClass('pair')
                            .append(
                                $('<td/>', {text: pair.number}).addClass('number')
                                    .append($('<br/>'))
                                    .append($('<small/>', {text: pair.time}))
                            )
                            .append(
                                $('<td/>', {html: pair.title.replace(/\n/g, '<br />')})
                            )
                        );
                    });
                    $('#content').append(title).append($('<div/>').addClass('pairs').append(table));

					var bg = $('#background').get(0);
					bg.removeObjects();
					var img = bg.addImageObject('img/header.png', 0, 0);
					img.width = 226;
					img.height = 20;
					$('.day').each(function(){
						var pos = $(this).offset();
						var img = bg.addImageObject('img/day.png', pos.left, pos.top);
						img.width = 226;
						img.height = 18;
					});
					$('.pairs').each(function(){
						var pos = $(this).offset();
						var h = $(this).height();
						for(var i = 0; i < h-11; i++){
							var img = bg.addImageObject('img/line.png', pos.left, pos.top + i);
							img.width = 226;
							img.height = 1;
						}
						img = bg.addImageObject('img/pairs.png', pos.left, pos.top + h - 11);
						img.width = 226;
						img.height = 11;
					});
                });
            }
        });
    }else{
        $('#info').text('Настроек нет');
	}
}
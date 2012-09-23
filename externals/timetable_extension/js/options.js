$(function () {
    $.ajax({
        url: 'http://timetable.spuf.ru/api.php',
        data: {
            api: 2,
            query: 'groups'
        },
        dataType: 'json',
        crossDomain: false,
		error: function() {
			$('#select-container').empty().text('Ошибка соединения');
		},
        success: function(data) {
            var s = $('<select/>', {id: 'select'}).append($('<option/>', {value: 0, html: '&ndash; Выберите &ndash;'}));
            $.each(data.groups, function(i, group) {
                s.append($('<option/>', {value: group.id, text: group.name}));
            });
			$('#select-container').empty().append(s);
			$('#select').val(parseInt(localStorage['group']));
			$('#select').change(function () {
				localStorage['group'] = parseInt($('#select').val());
				localStorage.removeItem('cache');
			});
		}
    });
});
$(function(){
	
    $("#getReport").on("click", function(){
        $.ajax({
			url: '/report/detail_report_time_out/getList',
			type: 'POST',
			data:({
				year: $('#year :selected').val(),
				month: $('#month :selected').val()
			}),
			success:function(results) {
				$('#report').html(results);
			}
		});
		return false;
    });
	$(document).on("click", "#refresh_detail_report_item", function(){
		var element = $(this);
		$.ajax({
			url: '/report/detail_report_time_out/refresh_detail_report',
			type: 'POST',
			data:({
				oid : $(this).parent().attr('oid'),
				date : $(this).parent().attr('date')
			}),
			success:function(results) {
				element.text(results);
			}
		});
		return false;
    });
	$(document).on("click", "#refresh_detail_reports", function(){
		var elements = $(this).parent().parent().children('td');
		$.each(elements, function() {
			if ($(this).children('a').length) {
				var element = $(this);
				setTimeout(function(){
					$.ajax({
						url: '/report/detail_report_time_out/refresh_detail_report',
						type: 'POST',
						data:({
							oid : element.attr('oid'),
							date : element.attr('date')
						}),
						success:function(results) {
							element.children('a').text(results);
						}
					});
				}, 500);
			}
		});
		return false;
    });
	$(document).on("click", "#refresh_detail_reports_on_day", function(){
		var elements = $(this).parent().parent().parent().parent().children('tbody').children('tr'),
			date = $(this).parent().attr('date');
		$.each(elements, function() {
			if ($(this).children('[date="'+date+'"]').children('a').length) {
				var element = $(this).children('[date="'+date+'"]');
				setTimeout(function(){
					$.ajax({
						url: '/report/detail_report_time_out/refresh_detail_report',
						type: 'POST',
						data:({
							oid : element.attr('oid'),
							date : date
						}),
						success:function(results) {
							element.children('a').text(results);
						}
					});
				}, 500);
			}
		});
		return false;
    });
	var start = 0;
	check();
	$("#btnRight").on("click", function(){
        start++;
		check();
		return false;
    });
	$("#btnLeft").on("click", function(){
        start--;
		check();
		return false;
    });
	$('#year').change(function(){
		start = -1 * moment([moment().format('YYYY'), moment().format('M'), 1]).diff(moment([$('#year :selected').val(), $('#month :selected').val(), 1]), "month", true);
		console.log(start);
		check();
	});
	$('#month').change(function(){
		start = -1 * moment([moment().format('YYYY'), moment().format('M'), 1]).diff(moment([$('#year :selected').val(), $('#month :selected').val(), 1]), "month", true);
		console.log(start);
		check();
	});
	function check() {
		$('#year option').prop('selected', false);
		$('#month option').prop('selected', false);
		if ($('#year [value="' + moment().add({months:(start-1)}).format('YYYY') + '"]').length == 0) {
			$('#btnLeft').prop('disabled', true);
		} else {
			$('#btnLeft').prop('disabled', false);
		}
		$('#year [value="' + moment().add({months:start}).format('YYYY') + '"]').prop("selected", true);
		$('#month [value="' + moment().add({months:start}).format('M') + '"]').prop("selected", true);
		if (start == 0) {
			$('#btnRight').prop('disabled', true);
		} else {
			$('#btnRight').prop('disabled', false);
		}
	}
});
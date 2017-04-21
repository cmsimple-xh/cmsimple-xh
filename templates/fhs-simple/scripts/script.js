jQuery(document).ready(function ($) {
	mobNavHeight = function () {
		if ($(window).innerWidth() < 970) {
			var f = $('#footer').innerHeight();
			var h = $('#header').height() + 5;
			var w = $('#wrapper').height();
			var gesamtHoehe = (w + f);
			var myTop = (h);
			$('.nav_horizontal').css({
				'height': gesamtHoehe + 'px',
				'top': myTop + "px"
			});
			$(".nav_horizontal div ul li ul").css({
				'display': 'block'
			});
		} else {
			$('.nav_horizontal').css({
				'height': '40px',
				'top': "0px"
			});
			$('.nav_horizontal').show();
			$('.nav_horizontal ul li ul').css({
				'display': 'none'
			});
		}
	}
	mobNavHeight();
	$(window).resize(function () {
		mobNavHeight();
	});
	$('.burger').click(function () {
		$('.nav_horizontal').fadeToggle();
	});
	$(".nav_horizontal div ul li").hover(
		function () {
		if ($(window).innerWidth() > 971) {
			$(this).find('a').addClass('hover');
			$(this).find("ul:first").show();
		}
	},
		function () {
		if ($(window).innerWidth() > 971) {
			$(this).parent().find("ul").hide();
			$(this).find('a').removeClass('hover');
		}
	});
});

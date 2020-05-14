jQuery(document).ready(function ($) {
	var offset = 150;
	var duration = 600;
	jQuery(window).scroll(function () {
		if (jQuery(this).scrollTop() > offset) {
			jQuery('#topLink').fadeIn(duration);
		} else {
			jQuery('#topLink').fadeOut(duration);
		}
	});
	jQuery('#topLink').click(function (event) {
		event.preventDefault();
		jQuery('html, body').animate({
			scrollTop: 0
		}, duration);
		return false;
	})
	var cm = $('.burger');
	cm.on('click', function () {
		cm.toggleClass('cmenu');
		$('.nav_horizontal').toggleClass('open closed');
	});
	mobNavHeight = function () {
		if ($(window).innerWidth() <= 980) {
			var headerH = $('#header').innerHeight() + 5;
			var footerH = $('#footer').innerHeight() + 5;
			var wrapperH = $('#wrapper').innerHeight();
			var wrapperTop = $('#wrapper').offset().top;
			var navH = wrapperH + footerH;
			$('.nav_horizontal').css({
				'height': navH + 'px',
				'top': wrapperTop + 'px'
			});
			$('.burger').css({
				'display': 'block'
			});
		} else {
			$('.nav_horizontal').removeClass('open').addClass('closed');
			$('.burger').css({
				'display': 'none'
			});
			$('.burger').removeClass('cmenu');
		}
	}
	mobNavHeight();
	$(window).resize(function () {
		mobNavHeight();
	});
});

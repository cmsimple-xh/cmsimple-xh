$(document).ready(function () {

    $('.menu-btn a').click(function () {
        $('.overlay').fadeToggle(200);
        $(this).toggleClass('btn-open').toggleClass('btn-close');
		$("body").addClass('overlay-active');
    });

    $('.overlay').on('click', function () {
        $('.overlay').fadeToggle(200);
        $('.menu-btn a').toggleClass('btn-open').toggleClass('btn-close');
		$("body").removeClass('overlay-active');
	});

    $('.menu a').on('click', function () {
        $('.overlay').fadeToggle(200);
        $('.menu-btn a').toggleClass('btn-open').toggleClass('btn-close');
		$("body").removeClass('overlay-active');
    });

});
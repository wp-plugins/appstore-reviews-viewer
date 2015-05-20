jQuery(document).ready(function() {
	var speed = 10000;
	
	var slides = jQuery('.asrv_review');
	var slideIndex = -1;
	
	function showNextSlide() {
		++slideIndex;
		slides.eq(slideIndex % slides.length)
			.fadeIn(1000)
			.delay(speed)
			.fadeOut(1000, showNextSlide);
	}
	
	showNextSlide();
});
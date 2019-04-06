var clipboard = new ClipboardJS('.ann');

clipboard.on('success', function(e) {
    $('.copied').show();
		$('.copied').fadeOut(1000);
});
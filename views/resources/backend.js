jQuery(function($) {
	
	$('.cma-settings-tabs a').click(function() {
		var match = this.href.match(/\#tab\-([^\#]+)$/);
		$('#settings .settings-category.current').removeClass('current');
		$('#settings .settings-category-'+ match[1]).addClass('current');
		$('.cma-settings-tabs a.current').removeClass('current');
		$('.cma-settings-tabs a[href=#tab-'+ match[1] +']').addClass('current');
		this.blur();
	});
	if (location.hash.length > 0) {
		$('.cma-settings-tabs a[href='+ location.hash +']').click();
	} else {
		$('.cma-settings-tabs li:first-child a').click();
	}
	
	
});
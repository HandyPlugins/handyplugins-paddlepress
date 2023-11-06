const { jQuery } = window;

jQuery(document).ready(function ($) {
	let activeIndex = 0;
	if ($('.nav-tab-active').length) {
		activeIndex = $('.nav-tab-active').parent().index();
	}

	$('#pp-setting-tabs').tabs({ active: activeIndex });
	$('.pp-setting-tab').on('click', function () {
		$('.pp-setting-tab').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('#current_section').val($(this).attr('href'));
	});

	$('#enable_profitwell').on('change', function () {
		if (this.checked) {
			$('#profitwell_token_row').fadeIn('slow');
		} else {
			$('#profitwell_token_row').fadeOut('slow');
		}
	});


	$('#enable_software_licensing').on('change', function () {
		if (this.checked) {
			$('#ignore_local_host_url_row').fadeIn('slow');
		} else {
			$('#ignore_local_host_url_row').fadeOut('slow');
		}
	});
});

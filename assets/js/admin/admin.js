const { jQuery } = window;

jQuery(document).ready(function ($) {
	$('#pp-setting-tabs').tabs();
	$('.pp-setting-tab').on('click', function () {
		$('.pp-setting-tab').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
	});

	$('#enable_software_licensing').change(function () {
		if (this.checked) {
			$('#ignore_local_host_url_row').fadeIn('slow');
		} else {
			$('#ignore_local_host_url_row').fadeOut('slow');
		}
	});
});

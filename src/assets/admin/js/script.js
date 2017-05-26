//$.noConflict();
jQuery(document).ready(function ($) {

	// SWITCH
	//$('.uo_feature_button').click(function () {
	$('body').delegate('.uo_feature_button', 'click', function () {
		//$('#module_activated  span').html('');
		//$('#module_deactivated span').html('');
		$(this).toggleClass("uo_feature_activated uo_feature_deactivated");
		var checkbox = $(this).children("input[type='checkbox']");
		var parent = $(this).parent();
		var value = checkbox.val();
		//var id = checkbox.attr('id');
		var active = 0;
		var class_name = checkbox.attr('data-class');

		if ($(this).hasClass('uo_feature_activated')) {
			checkbox.prop('checked', true);
			active = 1;
		}
		if ($(this).hasClass('uo_feature_deactivated')) {
			checkbox.prop('checked', false);
			active = 0;
		}

		var data = {

			'action': 'activate_deactivate_module',
			'value': value,
			'active': active

		};
		//console.log(data);
		$.post(ajaxurl, data, function (response) {
			if ('success' === response) {
				//success goes here
				if (1 === active) {
					$('#module_activated span').html(class_name + ' Activated.').parent().fadeIn();
					var t = setTimeout(function () {
						$('#module_activated').fadeOut();
					}, 2500);
					parent.attr('data-active', 1);
				} else if (0 === active) {
					$('#module_deactivated span').html(class_name + ' Deactivated.').parent().fadeIn();
					var t = setTimeout(function () {
						$('#module_deactivated').fadeOut();
					}, 3000);
					parent.attr('data-active', 2);
				}
			} else {
				console.log(response);
			}

		});
	});


	// SIMPLE MODAL POPUP
	$.fn.extend({

		leanModal: function (options) {

			var defaults = {
				top: 100,
				overlay: 0.5,
				closeButton: '.modal_close'
			};

			var overlay = $("<div id='lean_overlay'></div>");

			$("body").append(overlay);

			options = $.extend(defaults, options);

			return this.each(function () {

				var o = options;

				$(this).click(function (e) {

					var modal_id = $(this).attr("href");

					var modal_overlay = $("#lean_overlay");

					modal_overlay.click(function () {
						close_modal(modal_id);
					});

					$(o.closeButton).click(function () {
						close_modal(modal_id);
					});

					modal_overlay.css({'display': 'block', opacity: 0});

					modal_overlay.fadeTo(200, o.overlay);

					$(modal_id).fadeTo(200, 1);

					var modal_width = $(modal_id).width();
					var window_height = $(window).height();
					var display = 'block';
					if (window_height > 900) {
						display = 'table';
					}
					$(modal_id).css({

						'display': display,
						'position': 'fixed',
						'opacity': 0,
						'z-index': 999,
						'left': 50 + '%',
						'margin-left': -(modal_width / 2) + "px",
						'top': '20%'

					});

					//$(modal_id).find('.uo-color-picker').wpColorPicker();

					e.preventDefault();

				});

			});

			function close_modal(modal_id) {

				$("#lean_overlay").fadeOut(200);

				$(modal_id).css({'display': 'none'});

			}

		}
	});

	$("a[rel*=leanModal]").leanModal();

	//Reset save options button if Options changed
	$('.uo_settings_options').on('change keyup', '.uo_settings_form_field', function () {
		var save_settings_button = $(this).closest('.uo_settings_options').find('.uo_save_settings');
		save_settings_button.html('Save Settings');
		save_settings_button.css('background', '#A9A9A9');
	});

	// SAVE SETTINGS
	$('.uo_save_settings').on('click', function (e) {
		e.preventDefault();
		$('.uo_settings_options').hide('fast');
		$('.sk-folding-cube').show('fast');

		var button = $(this);
		var settings_class = $(this).closest('.uo_settings').attr('id');
		var options = $(this).closest('.uo_settings_options').find('input, select, textarea').serializeArray();

		var data = {

			'action': 'settings_save',
			'class': settings_class,
			'options': options

		};
		//console.log(data);
		$.post(ajaxurl, data, function (response) {

			if ('success' === response) {
				$(button).html('Options Saved');
				$(button).css('background', '#238b23');
			} else {
				$(button).html('Error: Check Console');
				$(button).css('background', '#ac2525');
				//console.log(response);
			}

			//$('.sk-folding-cube').delay(1500).hide('slow');
			$('.sk-folding-cube').hide();
			//$('.uo_settings_options').delay(2000).show('slow');
			$('.uo_settings_options').show('fast');

			setTimeout(
				function () {
					$(button).html('Save Settings');
					$(button).removeAttr('style');
				}, 5000);

		});


	});

	// LOAD SETTINGS
	//$('.uo_settings_link').live('click', function(e) {
	$('#features').delegate('a', 'click', function (e) {
		//console.log($(this).attr('href'));
		var settings_class = $(this).attr('href');
		settings_class = settings_class.replace('#', '');
		var settings_container = $('#' + settings_class).find('.uo_settings_options');

		// Show Spinner
		$('.sk-folding-cube').show();
		//Hide Setting UI
		settings_container.hide();


		var data = {
			'action': 'settings_load',
			'class': settings_class
		};

		//console.log(data);
		$.post(ajaxurl, data, function (response) {
			//console.log(response);
			var saved_options = JSON.parse(response);

			$.each(saved_options, function (options_index, option) {
				var element = $('#' + settings_class).find('[name="' + option['name'] + '"]');
				var option_value = remove_slashes_from_strong(option['value']);
				//console.log(option_value);
				if (element.is('input[type="text"]')) {
					element.val(option_value);
				}

				if (element.is('textarea')) {
					element.val(option_value);
				}

				if (element.is('input[type="color"]')) {
					element.val(option_value);
				}

				if (element.is('input[type="checkbox"]')) {
					if ('on' === option_value) {
						element.prop("checked", true);
					} else {
						element.prop("checked", false);
					}
				}

				if (element.is('input[type="radio"]')) {

					$.each(element, function (radio_index, radio) {
						if (option_value === $(radio).val()) {
							$(radio).prop("checked", true);
						}
					});

				}

				if (element.is('select')) {
					element.val(option_value);
				}

			});
			// Hide Spinner
			//$('.sk-folding-cube').delay(1000).hide('slow');
			$('.sk-folding-cube').hide('fast');
			// Show Settings Hide
			//settings_container.delay(1000).show('slow')
			settings_container.show('fast');
			settings_container.find('.uo-color-picker').wpColorPicker();
		});
	});

	function remove_slashes_from_strong(string){
		return string.replace(new RegExp("\\\\", "g"), "");
	}

});

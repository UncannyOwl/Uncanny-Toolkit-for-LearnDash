//$.noConflict();
jQuery( document ).ready(function( $ ) {

	// SWITCH
	$( '.uo_feature_button')
		.click(
		function() {
			$( this ).toggleClass( "uo_feature_activated uo_feature_deactivated" );
			var checkbox = $(this).children("input[type='checkbox']");
			if( $(this).hasClass( 'uo_feature_activated' ) ){
				checkbox.prop('checked', true);
			}
			if( $(this).hasClass( 'uo_feature_deactivated' ) ){
				checkbox.prop('checked', false);
			}
		});



	// SIMPLE MODAL POPUP
	$.fn.extend({

		leanModal: function(options) {

			var defaults = {
				top: 100,
				overlay: 0.5,
				closeButton: '.modal_close'
			};

			var overlay = $("<div id='lean_overlay'></div>");

			$("body").append(overlay);

			options =  $.extend(defaults, options);

			return this.each(function() {

				var o = options;

				$(this).click(function(e) {

					var modal_id = $(this).attr("href");

					var modal_overlay = $("#lean_overlay");

					modal_overlay.click(function() {
						close_modal(modal_id);
					});

					$(o.closeButton).click(function() {
						close_modal(modal_id);
					});

					modal_overlay.css({ 'display' : 'block', opacity : 0 });

					modal_overlay.fadeTo(200,o.overlay);

					$(modal_id).fadeTo(200,1);

					var modal_width = $(modal_id).width();

					$(modal_id).css({

						'display' : 'block',
						'position' : 'fixed',
						'opacity' : 0,
						'z-index': 11000,
						'left' : 50 + '%',
						'margin-left' : -(modal_width/2) + "px",
						'top' : '20%'

					});

					e.preventDefault();

				});

			});

			function close_modal(modal_id){

				$("#lean_overlay").fadeOut(200);

				$(modal_id).css({ 'display' : 'none' });

			}

		}
	});

	$("a[rel*=leanModal]").leanModal();

	//Reset save options button if Options changed
	$('.uo_settings_options').on('change keyup', '.uo_settings_form_field', function(){
		var save_settings_button = $(this).closest('.uo_settings_options').find('.uo_save_settings');
		save_settings_button.html('Save Settings');
		save_settings_button.css('background','#A9A9A9');
	});

	// SAVE SETTINGS
	$('.uo_save_settings').on('click', function(e) {
		e.preventDefault();
		$('.uo_settings_options').hide('slow');
		$('.sk-folding-cube').delay(500).show('slow');

		var button = $(this);
		var settings_class = $(this).closest('.uo_settings').attr('id');
		var options = $(this).closest('.uo_settings_options').find('input, select').serializeArray();

		var data = {

			'action': 'settings_save',
			'class' : settings_class,
			'options': options

		};
		console.log(data);
		$.post(ajaxurl, data, function (response) {

			if( 'success' === response ){
				$(button).html('Options Saved');
				$(button).css('background','#29C129');
			}else{
				$(button).html('Error: Check Console');
				$(button).css('background','orange');
				console.log( response );
			}

			$('.sk-folding-cube').delay(1500).hide('slow');
			$('.uo_settings_options').delay(2000).show('slow');

		});

	});

	// LOAD SETTINGS
	$('.uo_settings_link').on('click', function(e) {

		var settings_class = $(this).attr('href');
		settings_class = settings_class.replace('#','');
		var settings_container = $('#'+settings_class).find('.uo_settings_options');

		// Show Spinner
		$('.sk-folding-cube').show();
		//Hide Setting UI
		settings_container.hide();


		var data = {
			'action': 'settings_load',
			'class' : settings_class
		};

		console.log(data);
		$.post( ajaxurl, data, function (response) {
			console.log(response);
			var saved_options = JSON.parse( response );

			$.each(saved_options, function( options_index, option ) {
				var element = $('#'+settings_class).find('[name="'+ option['name'] +'"]');

				if( element.is( 'input[type="text"]' ) ){
					element.val(option['value']);
				}

				if( element.is( 'input[type="checkbox"]' ) ){
					if( 'on' === option['value'] ){
						element.prop( "checked", true );
					}else{
						element.prop( "checked", false );
					}
				}

				if( element.is( 'input[type="radio"]' ) ){

					$.each(element, function( radio_index, radio ){
						if( option['value'] === $(radio).val() ){
							$(radio).prop( "checked", true );
						}
					});

				}

				if( element.is( 'select' ) ){
					element.val(option['value']);
				}

			});
			// Hide Spinner
			$('.sk-folding-cube').delay(1000).hide('slow');
			// Show Settings Hide
			settings_container.delay(1000).show('slow')
		});
	});



});

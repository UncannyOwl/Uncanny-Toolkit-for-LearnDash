//$.noConflict();
jQuery( document ).ready(function( $ ) {
	$( '.uo_feature_button_toggle').css('height', '25px' );
	 $( '.uo_feature_checkbox, .uo_feature_label').css({
		"position": "absolute",
		"left": "10000px"
		});

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
		})
});

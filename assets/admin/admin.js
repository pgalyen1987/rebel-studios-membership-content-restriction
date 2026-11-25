/* global jQuery, wp */
( function ( $ ) {
	$( function () {
		var frame;
		$( '#wcinv_logo_pick' ).on( 'click', function ( e ) {
			e.preventDefault();
			if ( frame ) { frame.open(); return; }
			frame = wp.media( { title: 'Select logo', multiple: false, library: { type: 'image' } } );
			frame.on( 'select', function () {
				var a = frame.state().get( 'selection' ).first().toJSON();
				$( '#wcinv_logo_id' ).val( a.id );
				var src = ( a.sizes && a.sizes.medium ) ? a.sizes.medium.url : a.url;
				$( '#wcinv_logo_preview' ).html( '<img src="' + src + '" style="max-width:180px;height:auto">' );
			} );
			frame.open();
		} );
		$( '#wcinv_logo_clear' ).on( 'click', function ( e ) {
			e.preventDefault();
			$( '#wcinv_logo_id' ).val( '' );
			$( '#wcinv_logo_preview' ).empty();
		} );
	} );
} )( jQuery );

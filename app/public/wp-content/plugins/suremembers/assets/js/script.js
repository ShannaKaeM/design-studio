( function ( $ ) {
	// Enable popup.
	function enablePopup( e ) {
		e.preventDefault();

		if ( ! $( 'body' ).children( '.suremember-login-container-popup' ).length ) {
			const popup = $( '.suremember-login-container-popup' );
			popup.find( 'p, br' ).remove();
			$( '.suremember-login-container-popup' ).appendTo( document.body );
		}
		$( '.suremember-login-container-popup' ).addClass( 'active' );
	}

	// Submit user login form.
	function loginSubmit( e ) {
		e.preventDefault();
		const form = $( this );
		const getSubmitBtn = $( '.suremember-user-form-submit' );
		getSubmitBtn.addClass( 'submit-loading' );
		const formData = new FormData( form[ 0 ] );
		$.ajax( {
			method: 'POST',
			url: suremembers_login.ajax_url,
			data: formData,
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,
			success( response ) {
				getSubmitBtn.removeClass( 'submit-loading' );
				if ( true === response.success ) {
					window.location.reload();
				} else if ( response?.data?.result ) {
					for ( const field in response.data.result ) {
						const heading = $( '.suremember-login-heading' );
						heading.after( `<span class='field-error'>${ response.data.result[ field ] }</span>` );
					}
				}
			},
		} );
	}

	function logoutSubmit( e ) {
		e.preventDefault();

		const button = $( this ),
			  processingText = button.data( 'processing' ),
			  nonce = button.data( 'nonce' );
		button.text( processingText );
		const formData = new FormData();
		formData.append( 'action', 'suremembers_user_logout' );
		formData.append( 'logout_nonce', nonce );
		$.ajax( {
			method: 'POST',
			url: suremembers_login.ajax_url,
			data: formData,
			dataType: 'json',
			cache: false,
			contentType: false,
			processData: false,
			success( response ) {
				if ( true === response.success ) {
					window.location.reload();
				} else if ( response?.data?.result ) {
					button.after( `<span class='field-error'>${ response?.data?.result?.message || 'Something went wrong. Please try again.' }</span>` );
				}
			},
		} );
	}

	// When click outside hide popup.
	$( document ).on( 'click', '.suremember-login-container-popup.active', function ( e ) {
		const inner = $( '.suremember-login-wrapper' );
		if ( ! inner.is( e.target ) && inner.has( e.target ).length === 0 ) {
			$( this ).removeClass( 'active' );
		}
	} );

	// Close by button.
	$( document ).on( 'click', '.suremember-login-wrapper-close', function() {
		$( '.suremember-login-container-popup' ).removeClass( 'active' );
	} );

	// Hide show password.
	function showHidePwd( event ) {
		event.preventDefault();
		const button = $( this );
		const input = button.siblings( 'input' );
		button.toggleClass( 'show-pwd' );
		if ( 'text' === input.attr( 'type' ) ) {
			input.attr( 'type', 'password' );
			button.find( 'span' ).removeClass( 'dashicons-hidden' );
			button.find( 'span' ).addClass( 'dashicons-visibility' );
		} else {
			input.attr( 'type', 'text' );
			button.find( 'span' ).removeClass( 'dashicons-visibility' );
			button.find( 'span' ).addClass( 'dashicons-hidden' );
		}
	}

	$( document ).on( 'submit', '.suremember-user-login-form', loginSubmit );
	$( document ).on( 'click', '.suremembers-logout-button', logoutSubmit );
	$( document ).on( 'click', '.suremembers-open-login-popup', enablePopup );
	$( document ).on( 'click', '.suremembers-hide-if-no-js', showHidePwd );
}( jQuery ) );

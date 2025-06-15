jQuery( document ).ready( function () {
	jQuery( '#suremembers_access_groups' ).select2( {
		minimumInputLength: 1,
		ajax: {
			url: ajaxurl,
			type: 'POST',
			delay: 250,
			data( params ) {
				const query = {
					security: suremembers_menu_items.security,
					action: 'queried_access_groups',
					search: params.term,
					exclude: suremembers_menu_items.user_access_groups,
				};
				return query;
			},
			processResults( response ) {
				return {
					results: response.data,
				};
			},
		},
	} );
} );
const afterDOMInitiated = ( cb ) => {
	if ( /comp|inter|loaded/.test( document.readyState ) ) {
		cb();
	} else {
		document.addEventListener( 'DOMContentLoaded', cb, false );
	}
};
( function ( $ ) {
	const userApp = {
		_log( message ) {
			console.log( message );
		},
		bind() {
			$( '.suremembers-user-actions' ).on( 'click', function ( e ) {
				e.preventDefault();
				const $this = $( this );
				const userData = {
					userID: $this.data( 'user' ),
					action: $this.data( 'action' ),
					access: $this.data( 'access' ),
				};

				const _ajax_nonce = $( '#suremembers-user-access-list' ).data( 'nonce' );

				$.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'suremembers_users_edit_actions',
						_ajax_nonce,
						data: userData,
					},
					beforeSend() {
						$this.addClass( 'suremembers-updating-inline' );
					},
				} ).done( function ( rp ) {
					if ( rp.success ) {
						userApp.refreshTable( $this.data( 'user' ) );
					}
					$this.removeClass( 'suremembers-updating-inline' );
				} );
			} );

			$( '#suremembers-add-access-group' ).off( 'click' ).on( 'click', function ( e ) {
				const $this = $( this );
				e.preventDefault();
				const selectIDs = $( '#suremembers_access_groups' ).val();
				const _ajax_nonce = $( '#suremembers-user-access-list' ).data( 'nonce' );
				$.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'suremembers_users_edit_add_access_groups',
						_ajax_nonce,
						accessIDs: selectIDs,
						userID: $this.data( 'user' ),
					},
					beforeSend() {
						$this.addClass( 'suremembers-updating-inline' );
					},
				} ).done( function ( response ) {
					if ( response.success ) {
						userApp.refreshTable( $this.data( 'user' ) );
						if ( response.data.added_ids.length > 0 ) {
							for ( let i = 0; i < response.data.added_ids.length; i++ ) {
								const id = response.data.added_ids[ i ];
								$( "#suremembers_access_groups option[value='" + id + "']" ).remove();
							}
							suremembers_menu_items.user_access_group_count = parseInt( suremembers_menu_items.user_access_group_count ) + parseInt( response.data.added_ids.length );
							userApp.refreshSelectWrapper();
							suremembers_menu_items.user_access_groups = [ ...suremembers_menu_items.user_access_groups, ...response.data.added_ids ];
						}
					} else if ( response.data?.message ) {
						alert( response.data.message );
					}
					$this.removeClass( 'suremembers-updating-inline' );
				} );
			} );

			$( '.suremembers-expire-date' ).on( 'change', function ( e ) {
				e.preventDefault();
				const $this = $( this );
				const userData = {
					userID: $this.data( 'user' ),
					date: $this.val(),
					access: $this.data( 'access' ),
				};

				const _ajax_nonce = $( '#suremembers-user-access-list' ).data( 'nonce' );

				$.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'suremembers_add_expire_date_to_user',
						_ajax_nonce,
						data: userData,
					},
					beforeSend() {
						$this.addClass( 'suremembers-updating-inline' );
					},
				} ).done( function ( ) {
					$this.removeClass( 'suremembers-updating-inline' );
				} );
			} );
		},
		refreshSelectWrapper() {
			if ( suremembers_menu_items.user_access_group_count === parseInt( suremembers_menu_items.published_access_groups_count ) ) {
				$( '#suremembers-add-access-group-select' ).hide();
			}
		},
		init() {
			this.bind();
		},
		getAccessListByID( id ) {
			const _ajax_nonce = $( '#suremembers-user-access-list' ).data( 'nonce' );
			return $.ajax( {
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'get_access_groups_by_id',
					_ajax_nonce,
					userID: id,
				},
			} ).done( function ( response ) {
				return response.success ? response?.data : [];
			} );
		},
		async refreshTable( user_id ) {
			const freshList = await userApp.getAccessListByID( user_id );
			if ( freshList?.data.access_groups.length > 0 ) {
				const template = wp.template( 'suremembers-users-access-group-row' );
				const $el = $( '#suremembers-user-access-list #the-list' );
				$el.html( template( { access_groups: freshList.data.access_groups } ) );
				userApp.bind();
			}
		},
	};

	afterDOMInitiated( () => {
		userApp.init();
	} );
}( jQuery ) );

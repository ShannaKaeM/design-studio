jQuery( window ).on( 'elementor:init', function () {
	'use strict';
	const SMB = {
		el: null,
		dropDownGroupIdsUnselected: [],
		selectedGroupIds: [],
		init( el ) {
			SMB.el = el;
			SMB.firstTime();
			SMB.Events();
			SMB.uiFunctionality();
		},
		uiFunctionality: () => {
			// Initial ui setup.
			const checkTheme = jQuery( '#elementor-editor-dark-mode-css' );
			const container = SMB.el.$el.find( '.suremembers-erb-controller' );
			let theme = 'light';
			if ( checkTheme.length ) {
				const checkAuto = checkTheme.attr( 'media' );
				if ( '(prefers-color-scheme: dark)' === checkAuto ) {
					const darkThemeMq = window.matchMedia(
						'(prefers-color-scheme: dark)'
					);
					if ( darkThemeMq.matches ) {
						theme = 'dark';
					}
				} else {
					theme = 'dark';
				}
			}
			if ( ! container.hasClass( theme ) ) {
				container.addClass( theme );
			}
			// Dynamic ui setup change by system.
			const system_theme = window.matchMedia(
				'(prefers-color-scheme: dark)'
			);
			system_theme.addEventListener( 'change', changeSystemTheme );
			function changeSystemTheme( e ) {
				container.removeClass( 'light dark' );
				const getUiTheme = e.matches ? 'dark' : 'light';
				container.addClass( getUiTheme );
			}
		},
		planRemoveEvent() {
			jQuery(
				'.suremembers-erb-controller .suremembers-remove-selected[data-id]'
			)
				.off()
				.click( SMB.removeAccessPlan );
		},
		Events: () => {
			jQuery( document ).on(
				'click',
				'.suremembers-erb-controller .suremembers-access-options[data-id]',
				SMB.addAccessPlan
			);
			jQuery( document ).on(
				'keyup',
				'.suremembers-erb-controller  .suremembers-erb-search-input',
				SMB.inputSearch
			);
			jQuery( document ).on(
				'click',
				'.suremembers-erb-controller .suremembers-search-container',
				SMB.focusOnContainer
			);
			jQuery( document ).mouseup( function ( e ) {
				const searchContainer = jQuery( '.suremembers-erb-controller' );
				if ( searchContainer.hasClass( 'focused' ) ) {
					if (
						! searchContainer.is( e.target ) &&
						searchContainer.has( e.target ).length === 0
					) {
						searchContainer.removeClass( 'focused' );
					}
				}
			} );
		},
		focusOnContainer() {
			const container = jQuery( this );
			const parentDiv = container
				.closest( '.suremembers-erb-controller' );
			if ( ! parentDiv.hasClass( 'focused' ) ) {
				parentDiv.addClass( 'focused' );
			}
			container.find( '.suremembers-erb-search-input' ).focus();
		},
		debounceTimer: null,
		inputSearch( e ) {
			const input = jQuery( this );
			const value = input.val();
			if (
				input.hasClass( 'remove-by-delete-button' ) &&
				8 === e.keyCode &&
				'' === value
			) {
				input.removeClass( 'remove-by-delete-button' );
			}
			if (
				! input.hasClass( 'remove-by-delete-button' ) &&
				'' === value &&
				8 === e.keyCode
			) {
				const getInput = input
					.prev( '.suremembers-access-saved' )
					.find( '[data-id]' );
				if ( getInput.length ) {
					SMB.removeFunctionality( getInput );
				}
			}

			const container = input.closest( '.suremembers-erb-controller' );

			if ( ! input.hasClass( 'remove-by-delete-button' ) ) {
				input.addClass( 'remove-by-delete-button' );
			}

			const excludeIds = SMB.el.getControlValue();
			clearTimeout( SMB.debounceTimer );
			if ( ( value && value.length >= 2 ) || '' === value ) {
				SMB.debounceTimer = setTimeout( () => {
					SMB.callAjxIds( value, excludeIds, '', false, container );
				}, 500 );
			}
		},
		removeAccessPlan() {
			const clickedElement = jQuery( this );
			SMB.removeFunctionality( clickedElement );
		},
		removeFunctionality: ( clickedElement ) => {
			let accessId = clickedElement.attr( 'data-id' );
			accessId = parseInt( accessId );
			const accessGroupSaved = [ ...SMB.el.getControlValue() ];
			const getIndex = accessGroupSaved.findIndex(
				( value ) => value === accessId
			);
			if ( getIndex >= 0 ) {
				accessGroupSaved.splice( getIndex, 1 );
				SMB.el.setValue( accessGroupSaved );
				const title = clickedElement.attr( 'data-title' );
				const createGroups = SMB.elementDropDown( title, accessId );

				const findContainer = clickedElement.closest(
					'.suremembers-erb-controller'
				);
				const searchContainer = findContainer.find(
					'.suremembers-search-result'
				);
				const getExistError = findContainer.find(
					'.suremembers-elementor-no-data-found'
				);
				if ( getExistError.length ) {
					getExistError.remove();
				}
				searchContainer.append( createGroups );
				clickedElement.parent( '.suremembers-access-saved' ).remove();
			}
		},
		elementSelectedAccessTitle: ( title, id ) => {
			const titleWithLength =
				title.length > 20 ? title.substring( 0, 20 ) + '...' : title;
			return `<span class="suremembers-access-saved">${ titleWithLength }<span class="suremembers-remove-selected dashicons dashicons-no-alt" data-id="${ id }" data-title="${ title }"></span></span>`;
		},
		elementDropDown: ( title, id ) => {
			return `<span class="suremembers-access-options" data-id="${ id }">${ title }</span>`;
		},
		addAccessPlan() {
			const clickedElement = jQuery( this );
			let accessId = clickedElement.attr( 'data-id' );
			accessId = parseInt( accessId );
			const accessGroupSaved = [ ...SMB.el.getControlValue() ];
			if ( accessGroupSaved.includes( accessId ) ) {
				return;
			}
			accessGroupSaved.push( accessId );
			SMB.el.setValue( accessGroupSaved );
			const accessTitle = clickedElement.html();
			const createSelectedGroups = SMB.elementSelectedAccessTitle(
				accessTitle,
				accessId
			);
			const container = clickedElement.closest(
				'.suremembers-erb-controller'
			);
			const findInput = container.find( '.suremembers-erb-search-input' );
			container.removeClass( 'focused' );
			jQuery( createSelectedGroups ).insertBefore( findInput );
			findInput.val( '' );
			SMB.planRemoveEvent();
			clickedElement.remove();
		},
		showSelectedGroup: ( savedIds = null ) => {
			if ( ! savedIds ) {
				savedIds = SMB.selectedGroupIds;
			}
			if ( ! savedIds || ! savedIds.length ) {
				return;
			}
			let createSelectedGroups = '';
			for ( let i = 0; i < savedIds.length; i++ ) {
				const element = savedIds[ i ];
				if ( element.id ) {
					createSelectedGroups += SMB.elementSelectedAccessTitle(
						element.title,
						element.id
					);
				}
			}
			if ( '' !== createSelectedGroups ) {
				jQuery( createSelectedGroups ).insertBefore(
					SMB.el.$el.find( '.suremembers-erb-search-input' )
				);
				SMB.planRemoveEvent();
			}
		},
		putInResultDropdown: () => {
			let createGroups = '';
			const savedIds = SMB.el.getControlValue();
			const accessIds = SMB.dropDownGroupIdsUnselected;
			for ( let i = 0; i < accessIds.length; i++ ) {
				const element = accessIds[ i ];
				if ( element.id && ! savedIds.includes( element.id ) ) {
					createGroups += SMB.elementDropDown(
						element.title,
						element.id
					);
				}
			}
			SMB.el.$el
				.find( '.suremembers-search-result' )
				.html( createGroups );
		},
		ajx: ( data_ ) => {
			const ajaxObj = {
				method: 'POST',
				url: suremembers_elementor.ajax_url,
				data: data_,
				dataType: 'json',
			};
			return jQuery.ajax( ajaxObj );
		},
		callAjxIds: (
			title = '',
			excludeIds = '',
			includeIds = '',
			putInSelectedOption = false,
			container = null
		) => {
			const data = {};
			data.action = 'suremembers_postmeta_search';
			data.elementor_security =
				suremembers_elementor.suremembers_erb_security;
			if ( excludeIds && excludeIds.length ) {
				data.selected_ids = excludeIds.toString();
			}
			if ( includeIds && includeIds.length ) {
				data.include_ids = includeIds.toString();
			}
			if ( title && '' !== title ) {
				data.search_title = title;
			}
			const putContent = SMB.ajx( data );
			putContent.success( function ( response ) {
				SMB.afterAjxResponse(
					response,
					putInSelectedOption,
					container
				);
			} );
		},
		afterAjxResponse: ( response, putInSelectedOption, container ) => {
			container = ! container ? SMB.el.$el : container;
			if ( ! response.success && response.data.message ) {
				container
					.find( '.suremembers-search-result' )
					.html(
						`<span class="suremembers-elementor-no-data-found">${ response.data.message }</span>`
					);
			} else if ( response.success && response.data.length ) {
				const getExistError = container.find(
					'.suremembers-elementor-no-data-found'
				);
				if ( getExistError.length ) {
					getExistError.remove();
				}

				if ( putInSelectedOption ) {
					const selectedGroup = [];
					const accessGroupSaved = SMB.el.getControlValue();
					if ( accessGroupSaved && accessGroupSaved.length ) {
						for ( let i = 0; i < response.data.length; i++ ) {
							const element = response.data[ i ];
							if ( accessGroupSaved.includes( element.id ) ) {
								selectedGroup.push( element );
							}
						}
						SMB.selectedGroupIds = selectedGroup;
						SMB.showSelectedGroup( selectedGroup );
					}
				}
				SMB.dropDownGroupIdsUnselected = response.data;
				SMB.putInResultDropdown();
			}
		},
		firstTime: () => {
			const { sure_member_access_groups } =
				suremembers_elementor;
			if (
				! sure_member_access_groups ||
				! sure_member_access_groups.length
			) {
				const anchor = SMB.el.$el.find(
					'.suremember-elementor-no-restriction-access'
				);
				anchor.css( 'display', 'block' );
				SMB.el.$el.find( '.suremembers-search-container' ).hide();
				// Because data not availbel.
				return;
			}
			const accessGroupSaved = [ ...SMB.el.getControlValue() ];
			if ( accessGroupSaved && accessGroupSaved.length ) {
				SMB.callAjxIds( '', '', accessGroupSaved, true );
			} else {
				SMB.dropDownGroupIdsUnselected = [
					...sure_member_access_groups,
				];
				SMB.putInResultDropdown();
			}
		},
	};

	elementor.addControlView(
		'suremembers_restrictions',
		elementor.modules.controls.BaseData.extend( {
			onReady() {
				SMB.init( this );
			},
		} )
	);
} );

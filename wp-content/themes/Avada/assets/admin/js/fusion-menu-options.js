/* global fusionMenuConfig */
var FusionDelay = ( function() {
	var timer = 0;

	return function( callback, ms ) {
		clearTimeout( timer );
		timer = setTimeout( callback, ms );
	};
}() );

function fusionIconPicker( value, id, container, search ) {
	var icons     = fusionMenuConfig.fontawesomeicons,
		output      = jQuery( '.fusion-icons-rendered' ).html(),
		oldIconName = '';

	if ( ! container.children().length ) {

		if ( 'undefined' !== typeof value && '' !== value ) {
			value = value.split( ' ' );

			// Legacy FontAwesome 4.x icon, so we need check if it needs to be updated.
			if ( 'undefined' === typeof value[1] ) {
				value[1] = 'fas';

				if ( 'undefined' !== typeof window['fusion-fontawesome-free-shims'] ) {
					oldIconName = value[0].substr( 3 );

					jQuery.each( window['fusion-fontawesome-free-shims'], function( i, shim ) {

							if ( shim[0] === oldIconName ) {

								// Update icon name.
								if ( null !== shim[2] ) {
									value[0] = 'fa-' + shim[2];
								}

								// Update icon subset.
								if ( null !== shim[1] ) {
									value[1] = shim[1];
								}
								return false;
							}
					} );

					// Update form field with new values.
					jQuery( container ).parent().find( '.fusion-iconpicker-input' ).attr( 'value', value[0] + ' ' + value[1] );
				}
			}
		}

		jQuery( container ).append( output );

		if ( 2 === value.length ) {
			jQuery( container ).find( '.' + value[0] + '.' + value[1] ).parent().addClass( 'selected-element' );
		}

	}

	// Icon Search bar
	jQuery( search ).on( 'change paste keyup', function() {
		var thisEl = jQuery( this );

		FusionDelay( function() {
			var options,
				fuse,
				result;

			if ( thisEl.val() ) {
				value = thisEl.val().toLowerCase();

				if ( 3 > value.length ) {
					return;
				}

				jQuery( container ).find( '.icon_preview' ).css( 'display', 'none' );
				options = {
					threshold: 0.2,
					location: 0,
					distance: 100,
					maxPatternLength: 32,
					minMatchCharLength: 3,
					keys: [
						'name',
						'keywords',
						'categories'
					]
				};
				fuse = new Fuse( fusionIconSearch, options );
				result = fuse.search( value );

				_.each( result, function( resultIcon ) {
					jQuery( container ).find( '.icon-fa-' + resultIcon.name ).css( 'display', 'inline-block' );
				} );

			} else {
				jQuery( container ).find( '.icon_preview' ).css( 'display', 'inline-block' );
			}
		}, 100 );
	} );

	// Iconpicker select/deselect handler.
	jQuery( container ).find( 'span' ).off();
	jQuery( container ).find( 'span' ).on( 'click', function( e ) {

		var fontName,
			subset = 'fas',
			$i     = jQuery( this ).find( 'i' );

		e.preventDefault();

		fontName = 'fa-' + jQuery( this ).find( 'i' ).attr( 'data-name' );

		if ( $i.hasClass( 'fab' ) ) {
			subset = 'fab';
		} else if ( $i.hasClass( 'far' ) ) {
			subset = 'far';
		} else if ( $i.hasClass( 'fal' ) ) {
			subset = 'fal';
		}

		if ( jQuery( this ).hasClass( 'selected-element' ) ) {
			jQuery( this ).find( 'i' ).parent().parent().find( '.selected-element' ).removeClass( 'selected-element' );
			jQuery( this ).find( 'i' ).parent().parent().parent().find( '.fusion-iconpicker-input' ).attr( 'value', '' ).trigger( 'change' );

		} else {
			jQuery( this ).find( 'i' ).parent().parent().find( '.selected-element' ).removeClass( 'selected-element' );
			jQuery( this ).find( 'i' ).parent().addClass( 'selected-element' );
			jQuery( this ).find( 'i' ).parent().parent().parent().find( '.fusion-iconpicker-input' ).attr( 'value', fontName + ' ' + subset ).trigger( 'change' );
		}
	} );

}

jQuery( window ).load( function() {

	var $wrapEl,
		itemWrapEl;

	if ( jQuery( 'body' ).hasClass( 'widgets-php' ) ) {
		$wrapEl =  jQuery( 'body' ).hasClass( 'widgets_access' ) ? jQuery( '.editwidget' ) : jQuery( '.widget-liquid-right' );
		itemWrapEl = '.widget-inside';
	} else {
		$wrapEl    = jQuery( '#post-body' );
		itemWrapEl = '.menu-item-settings';
	}

	// Backup holder in case of cancel.
	jQuery( 'body' ).append( '<div class="fusion-menu-clone" style="display:none !important"></div>' );

	// On open.
	$wrapEl.on( 'click', '.fusion-menu-option-trigger', function( event ) {
		var $value       = jQuery( this ).parent().find( '.fusion-iconpicker-input' ).val(),
			$id          = jQuery( this ).parent().find( '.fusion-iconpicker-input' ).attr( 'id' ),
			$container   = jQuery( this ).parent().find( '.icon_select_container' ),
			$search      = jQuery( this ).parent().find( '.fusion-icon-search' ),
			$options     = jQuery( this ).parent().find( '.fusion-options-holder' ),
			$holder      = jQuery( this ).parents( '.menu-item-settings' ),
			$modal       = jQuery( this ).parent().find( '.fusion-builder-modal-settings-container' ),
			$colorPicker = jQuery( this ).parent().find( '.fusion-builder-color-picker-hex' ),
			$clone;

		event.preventDefault();
		if ( 'undefined' !== typeof $id ) {
			fusionIconPicker( $value, $id, $container, $search );
		}

		jQuery( $holder ).addClass( 'fusion-active' );
		jQuery( this ).parent().find( '.fusion_builder_modal_overlay' ).show();
		jQuery( $modal ).show();
		jQuery( 'body' ).addClass( 'fusion_builder_no_scroll' );

		// Button set functionality.
		jQuery( $modal ).find( '.fusion-form-radio-button-set a' ).on( 'click', function( event ) {
			var $radiosetcontainer;

			event.preventDefault();
			$radiosetcontainer = jQuery( this ).parents( '.fusion-form-radio-button-set' );
			$radiosetcontainer.find( '.ui-state-active' ).removeClass( 'ui-state-active' );
			jQuery( this ).addClass( 'ui-state-active' );
			$radiosetcontainer.find( '.button-set-value' ).val( $radiosetcontainer.find( '.ui-state-active' ).data( 'value' ) ).trigger( 'change' );
		} );

		// Save as a backup.
		$clone = jQuery( $options ).clone( true, true );
		jQuery( '.fusion-menu-clone' ).append( $clone );

		// Select field functionality.
		jQuery( $modal ).find( '.fusion-builder-option select' ).selectWoo( {
			minimumResultsForSearch: 10,
			dropdownCssClass: 'avada-select2',
			width: '100%'
		} );
		if ( $colorPicker.length ) {
			$colorPicker.each( function() {
				jQuery( this ).wpColorPicker( {
					palettes: [ '#000000', '#ffffff', '#f44336', '#E91E63', '#03A9F4', '#00BCD4', '#8BC34A', '#FFEB3B', '#FFC107', '#FF9800', '#607D8B' ]
				} );
			} );
		}

		if ( '.widget-inside' === itemWrapEl  && 'auto' !== jQuery( this ).closest( '.widget' ).css( 'z-index' ) ) {
			jQuery( this ).closest( '.widget' ).css( 'z-index', '99999' );
		}

		$rangeSlider = jQuery( $modal ).find( '.fusion-builder-option.avada-range .fusion-slider-container' );

		if ( $rangeSlider.length ) {

			// Counter variable for sliders
			$i = 0;

			// Method for retreiving decimal places from step
			Number.prototype.countDecimals = function() {
				if ( Math.floor( this.valueOf() ) === this.valueOf() ) {
					return 0;
				}
				return this.toString().split( '.' )[1].length || 0;
			};

			// Each slider on page, determine settings and create slider
			$rangeSlider.each( function() {

				var $targetId     = jQuery( this ).data( 'id' ),
					$rangeInput   = jQuery( this ).prev( '.fusion-slider-input' ),
					$min          = jQuery( this ).data( 'min' ),
					$max          = jQuery( this ).data( 'max' ),
					$step         = jQuery( this ).data( 'step' ),
					$direction    = jQuery( this ).data( 'direction' ),
					$value        = $rangeInput.val(),
					$decimals     = $step.countDecimals(),
					$rangeDefault = ( jQuery( this ).parents( '.fusion-builder-option' ).find( '.fusion-range-default' ).length ) ? jQuery( this ).parents( '.fusion-builder-option' ).find( '.fusion-range-default' ) : false,
					$hiddenValue  = ( $rangeDefault ) ? jQuery( this ).parent().find( '.fusion-hidden-value' ) : false,
					$defaultValue = ( $rangeDefault ) ? jQuery( this ).parents( '.fusion-builder-option' ).find( '.fusion-range-default' ).data( 'default' ) : false;

				createSlider( $i, $targetId, $rangeInput, $min, $max, $step, $value, $decimals, $rangeDefault, $hiddenValue, $defaultValue, $direction );

				$i++;
			} );

		}

	} );

	// On cancel.
	$wrapEl.on( 'click', '.fusion-builder-modal-close', function( event ) {
		var $backup = jQuery( '.fusion-menu-clone' ).find( '.fusion-builder-modal-settings-container' ).hide();

		event.preventDefault();

		if ( '.widget-inside' === itemWrapEl  && 'auto' !== jQuery( this ).closest( '.widget' ).css( 'z-index' ) ) {
			jQuery( this ).closest( '.widget' ).css( 'z-index', '100' );
		}

		jQuery( '.fusion-builder-option .wp-color-picker' ).wpColorPicker( 'close' );
		jQuery( '.fusion-builder-option select.select2-hidden-accessible' ).selectWoo( 'destroy' );
		jQuery( '.fusion-active' ).removeClass( 'fusion-active' );
		jQuery( this ).parents( '.fusion-builder-modal-settings-container' ).replaceWith( $backup );
		jQuery( this ).parents( '.fusion-builder-modal-settings-container' ).hide();
		jQuery( '.fusion_builder_modal_overlay' ).hide();
		jQuery( 'body' ).removeClass( 'fusion_builder_no_scroll' );
		jQuery( '.fusion-menu-clone' ).html( '' );

	} );

	// On outside click.
	$wrapEl.on( 'click', itemWrapEl + ' .fusion_builder_modal_overlay', function( event ) {
		var $backup = jQuery( '.fusion-menu-clone' ).find( '.fusion-builder-modal-settings-container' ).hide();

		event.preventDefault();

		if ( '.widget-inside' === itemWrapEl  && 'auto' !== jQuery( this ).closest( '.widget' ).css( 'z-index' ) ) {
			jQuery( this ).closest( '.widget' ).css( 'z-index', '100' );
		}

		if ( 'undefined' !== typeof $rangeSlider && 0 < $rangeSlider.length ) {
			$rangeSlider.each( function() {
				this.noUiSlider.destroy();
			} );
		}
		jQuery( '.fusion-builder-option .wp-color-picker' ).wpColorPicker( 'close' );
		jQuery( '.fusion-builder-option select.select2-hidden-accessible' ).selectWoo( 'destroy' );
		jQuery( '.fusion-active' ).removeClass( 'fusion-active' );
		jQuery( this ).next().replaceWith( $backup );
		jQuery( this ).next().hide();
		jQuery( '.fusion_builder_modal_overlay' ).hide();
		jQuery( 'body' ).removeClass( 'fusion_builder_no_scroll' );
		jQuery( '.fusion-menu-clone' ).html( '' );
	} );

	// On save,
	$wrapEl.on( 'click', '.fusion-builder-modal-save', function( event ) {
		event.preventDefault();

		if ( '.widget-inside' === itemWrapEl  && 'auto' !== jQuery( this ).closest( '.widget' ).css( 'z-index' ) ) {
			jQuery( this ).closest( '.widget' ).css( 'z-index', '100' );
		}

		if ( 'undefined' !== typeof $rangeSlider && 0 < $rangeSlider.length ) {
			$rangeSlider.each( function() {
				this.noUiSlider.destroy();
			} );
		}
		jQuery( '.fusion-builder-option .wp-color-picker' ).wpColorPicker( 'close' );
		jQuery( '.fusion-builder-option select.select2-hidden-accessible' ).selectWoo( 'destroy' );
		jQuery( '.fusion-active' ).removeClass( 'fusion-active' );
		jQuery( this ).parents( '.fusion-builder-modal-settings-container' ).hide();
		jQuery( '.fusion_builder_modal_overlay' ).hide();
		jQuery( 'body' ).removeClass( 'fusion_builder_no_scroll' );
		jQuery( '.fusion-menu-clone' ).html( '' );
	} );


	function createSlider( $slide, $targetId, $rangeInput, $min, $max, $step, $value, $decimals, $rangeDefault, $hiddenValue, $defaultValue, $direction ) {

		// Create slider with values passed on in data attributes.
		var $slider = noUiSlider.create( $rangeSlider[ $slide ], {
				start: [ $value ],
				step: $step,
				direction: $direction,
				range: {
					'min': $min,
					'max': $max
				},
				format: wNumb( {
					decimals: $decimals
				} )
			} ),
			$notFirst = false;

		// Check if default is currently set.
		if ( $rangeDefault && '' === $hiddenValue.val() ) {
			$rangeDefault.parent().addClass( 'checked' );
		}

		// If this range has a default option then if checked set slider value to data-value.
		if ( $rangeDefault ) {
			$rangeDefault.on( 'click', function( e ) {
				e.preventDefault();
				$rangeSlider[$slide].noUiSlider.set( $defaultValue );
				$hiddenValue.val( '' );
				jQuery( this ).parent().addClass( 'checked' );

				// Specific for Widget modals.
				if ( '.widget-inside' === itemWrapEl ) {
					jQuery( this ).closest( '.widget' ).find( '.widget-control-save' ).prop( 'disabled', false );
				}
			} );
		}

		// On slider move, update input
		$slider.on( 'update', function( values, handle ) {
			if ( $rangeDefault && $notFirst ) {
				$rangeDefault.parent().removeClass( 'checked' );
				$hiddenValue.val( values[handle] );
			}
			$notFirst = true;
			jQuery( this.target ).closest( '.fusion-slider-container' ).prev().val( values[handle] );
			jQuery( '#' + $targetId ).trigger( 'change' );
			if ( jQuery( '#' + $targetId ).length ) {
				jQuery( '#' + $targetId ).trigger( 'fusion-changed' );
			} else {
				jQuery( '#slider' + $targetId ).trigger( 'fusion-changed' );
			}

			// Specific for Widget modals.
			if ( '.widget-inside' === itemWrapEl ) {
				jQuery( this.target ).closest( '.widget' ).find( '.widget-control-save' ).prop( 'disabled', false );

				if ( '0' === jQuery( this.target ).siblings( '.fusion-slider-input' ).val() ) {
					jQuery( this.target ).closest( '.fusion-options-holder' ).addClass( 'fusion-widget-no-border' );
				} else {
					jQuery( this.target ).closest( '.fusion-options-holder' ).removeClass( 'fusion-widget-no-border' );
				}
			}
		} );

		// On manual input change, update slider position
		$rangeInput.on( 'keyup', function( values, handle ) {
			if ( $rangeDefault ) {
				$rangeDefault.parent().removeClass( 'checked' );
				$hiddenValue.val( values[handle] );

				// Specific for Widget modals.
				if ( '.widget-inside' === itemWrapEl ) {
					jQuery( this ).closest( '.widget' ).find( '.widget-control-save' ).prop( 'disabled', false );
				}
			}

			if ( this.value !== $rangeSlider[$slide].noUiSlider.get() ) {
				$rangeSlider[$slide].noUiSlider.set( this.value );
			}
		} );
	}
} );


jQuery( document ).ready( function() {

	( function initIconPicker() {
			var icons  = fusionMenuConfig.fontawesomeicons,
				output  = '<div class="fusion-icons-rendered" style="height:0px; overflow:hidden;">',
				iconSubsets = {
					fab: 'Brands',
					far: 'Regular',
					fas: 'Solid',
					fal: 'Light'
				};

			_.each( icons, function( icon, key ) {

				_.each( icon[1], function( iconSubset ) {
					if ( -1 !== fusionMenuConfig.fontawesomesubsets.indexOf( iconSubset ) ) {
						output += '<span class="icon_preview icon-' + icon[0] + '" title="' + key + ' - ' + iconSubsets[ iconSubset ] + '"><i class="' + icon[0] + ' ' + iconSubset + '" data-name="' + icon[0].substr( 3 ) + '"></i></span>';
					}
				} );

			} );
			output += '</div>';
			jQuery( 'body' ).append( output );
		} () );

} );

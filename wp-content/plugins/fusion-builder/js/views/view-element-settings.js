/* global openShortcodeGenerator, FusionPageBuilderEvents, fusionAllElements, FusionPageBuilderApp, CodeMirror, fusionBuilderText, noUiSlider, wNumb, FusionPageBuilderViewManager, alert */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		FusionPageBuilder.ElementSettingsView = window.wp.Backbone.View.extend( {

			className: 'fusion_builder_module_settings',
			template: FusionPageBuilder.template( $( '#fusion-builder-block-module-settings-template' ).html() ),

			events: {
				'click #qt_element_content_fusion_shortcodes_text_mode': 'activateSCgenerator'
			},

			activateSCgenerator: function( event ) {
				openShortcodeGenerator( $( event.target ) );
			},

			initialize: function() {
				var functionName,
					params,
					processedParams;

				this.listenTo( FusionPageBuilderEvents, 'fusion-modal-view-removed', this.removeElement );

				// Manupulate model attributes via custom function if provided by the element
				if ( 'undefined' !== typeof fusionAllElements[ this.model.get( 'element_type' ) ].on_settings ) {

					functionName = fusionAllElements[ this.model.get( 'element_type' ) ].on_settings;

					if ( 'function' === typeof FusionPageBuilderApp[ functionName ] ) {
						params          = this.model.get( 'params' );
						processedParams = FusionPageBuilderApp[ functionName ]( params, this );

						this.model.set( 'params', processedParams );
					}
				}

			},

			render: function() {
				var $thisEl = this.$el,
					content = '',
					view,
					$contentTextarea,
					$contentTextareaOption,
					$colorPicker,
					$uploadButton,
					$iconPicker,
					$multiselect,
					$checkboxbuttonset,
					$radiobuttonset,
					$value,
					$id,
					$container,
					$search,
					viewCID,
					$checkboxsetcontainer,
					$radiosetcontainer,
					$visibility,
					$choice,
					$rangeSlider,
					$i,
					thisModel,
					$selectField,
					textareaID,
					allowGenerator = false,
					$dimensionField,
					codeBlockId,
					$codeBlock,
					codeElement,
					that = this,
					$textField,
					$placeholderText,
					$theContent,
					fixSettingsLvl = false,
					parentAtts,
					$linkButton,
					$dateTimePicker,
					$multipleImages,
					fetchIds = [],
					codeMirrorJSON,
					parentValues,
					codeBlockLang;

				thisModel = this.model;

				// Fix for deprecated 'settings_lvl' attribute
				if ( 'undefined' !== thisModel.attributes.params.settings_lvl && 'parent' === thisModel.attributes.params.settings_lvl ) {
					fixSettingsLvl = true;
					parentAtts = thisModel.attributes.params;
				}

				if ( 'undefined' !== typeof thisModel.get && 'undefined' !== typeof thisModel.get( 'allow_generator' ) && true === thisModel.get( 'allow_generator' ) ) {
					FusionPageBuilderApp.allowShortcodeGenerator = true;
				}

				// Set parentValues for dependencies on child.
				parentValues = ( 'undefined' !== typeof this.model.get && 'undefined' !== typeof this.model.get( 'parent_values' ) ) ? this.model.get( 'parent_values' ) : false;

				this.$el.html( this.template( { atts: this.model.attributes } ) );

				$textField         = this.$el.find( '[data-placeholder]' );
				$contentTextarea   = this.$el.find( '.fusion-editor-field' );
				$colorPicker       = this.$el.find( '.fusion-builder-color-picker-hex' );
				$uploadButton      = this.$el.find( '.fusion-builder-upload-button' );
				$iconPicker        = this.$el.find( '.fusion-iconpicker' );
				$multiselect       = this.$el.find( '.fusion-form-multiple-select' );
				$checkboxbuttonset = this.$el.find( '.fusion-form-checkbox-button-set' );
				$radiobuttonset    = this.$el.find( '.fusion-form-radio-button-set' );
				$rangeSlider       = this.$el.find( '.fusion-slider-container' );
				$selectField       = this.$el.find( '.fusion-select-field' );
				$dimensionField    = this.$el.find( '.single-builder-dimension' );
				$codeBlock         = this.$el.find( '.fusion-builder-code-block' );
				$linkButton        = this.$el.find( '.fusion-builder-link-button' );
				$dateTimePicker    = this.$el.find( '.fusion-datetime' );
				$multipleImages    = this.$el.find( '.fusion-multiple-image-container' );

				if ( $textField.length ) {
					$textField.on( 'focus', function( event ) {
						if ( jQuery( event.target ).data( 'placeholder' ) === jQuery( event.target ).val() ) {
							jQuery( event.target ).val( '' );
						}
					} );
				}

				if ( $linkButton.length ) {
					FusionPageBuilderApp.fusionBuilderActivateLinkSelector( $linkButton );
				}

				if ( $dateTimePicker.length ) {
					jQuery( $dateTimePicker ).fusiondatetimepicker( {
						format: 'yyyy-MM-dd hh:mm:ss'
					} );
				}

				if ( $colorPicker.length ) {
					$colorPicker.each( function() {
						var self          = $( this ),
							$defaultReset = self.parents( '.fusion-builder-option' ).find( '.fusion-builder-default-reset' ),
							parentValue   = 'undefined' !== typeof parentValues && 'undefined' !== typeof parentValues[ self.attr( 'id' ) ] ? parentValues[ self.attr( 'id' ) ] : false;

						// Picker with default.
						if ( $( this ).data( 'default' ) && $( this ).data( 'default' ).length ) {
							$( this ).wpColorPicker( {
								change: function( event, ui ) {
									that.colorChange( ui.color.toString(), self, $defaultReset, parentValue );
								},
								clear: function( event ) {
									that.colorClear( event, self, parentValue );
								}
							} );

							// Make it so the reset link also clears color.
							$defaultReset.on( 'click', 'a', function( event ) {
								event.preventDefault();
								that.colorClear( event, self, parentValue );
							} );

						// Picker without default.
						} else {
							$( this ).wpColorPicker( {

							} );
						}

						// For some reason non alpha are not triggered straight away.
						if ( true !== $( this ).data( 'alpha' ) ) {
							$( this ).wpColorPicker().change();
						}
					} );
				}

				if ( $multipleImages.length ) {
					$multipleImages.each( function() {
						var $multipleImageContainer = jQuery( this ),
							ids;

						$multipleImageContainer.html( '' );

						if ( 'string' !== typeof $multipleImageContainer.parent().find( '#image_ids' ).val() ) {
							return;
						}

						// Set the media dialog box state as 'gallery' if the element is gallery.
						ids = $multipleImageContainer.parent().find( '#image_ids' ).val().split( ',' );

						// Check which attachments exist.
						jQuery.each( ids, function( index, id ) {
							if ( '' !== id && 'NaN' !== id ) {

								// Doesn't exist need to fetch.
								if ( 'undefined' === typeof wp.media.attachment( id ).get( 'url' ) ) {
									fetchIds.push( id );
								}
							}
						} );

						// Fetch attachments if neccessary.
						if ( 0 < fetchIds.length ) {
							wp.media.query( { post__in: fetchIds, posts_per_page: fetchIds.length } ).more().then( function( response ) { // jshint ignore:line
								that.renderAttachments( ids, $multipleImageContainer );
							} );
						} else {
							that.renderAttachments( ids, $multipleImageContainer );
						}
					} );
				}
				if ( $codeBlock.length ) {
					$codeBlock.each( function() {
						if ( 'undefined' === typeof wp.CodeMirror ) {
							return;
						}
						codeBlockId   = $( this ).attr( 'id' );
						codeElement   = $thisEl.find( '#' + codeBlockId );
						codeBlockLang = $( this ).data( 'language' );

						// Get wp.CodeMirror object json.
						codeMirrorJSON = $thisEl.find( '.' + codeBlockId ).val();
						codeMirrorJSON = jQuery.parseJSON( codeMirrorJSON );
						codeMirrorJSON.lineNumbers = true;

						if ( 'undefined' !== typeof codeBlockLang && 'default' !== codeBlockLang ) {
							codeMirrorJSON.mode = 'text/' + codeBlockLang;
						}

						FusionPageBuilderApp.codeEditor = wp.CodeMirror.fromTextArea( codeElement[0], codeMirrorJSON );

						// Refresh editor after initialization
						setTimeout( function() {
							FusionPageBuilderApp.codeEditor.refresh();
							FusionPageBuilderApp.codeEditor.focus();
						}, 100 );

					} );
				}

				if ( $dimensionField.length ) {
					$dimensionField.each( function() {
						jQuery( this ).find( '.fusion-builder-dimension input' ).on( 'change paste keyup', function() {
							jQuery( this ).parents( '.single-builder-dimension' ).find( 'input[type="hidden"]' ).val(
								( ( jQuery( this ).parents( '.single-builder-dimension' ).find( 'div:nth-child(1) input' ).val().length ) ? jQuery( this ).parents( '.single-builder-dimension' ).find( 'div:nth-child(1) input' ).val() : '0px' ) + ' ' +
								( ( jQuery( this ).parents( '.single-builder-dimension' ).find( 'div:nth-child(2) input' ).val().length ) ? jQuery( this ).parents( '.single-builder-dimension' ).find( 'div:nth-child(2) input' ).val() : '0px' ) + ' ' +
								( ( jQuery( this ).parents( '.single-builder-dimension' ).find( 'div:nth-child(3) input' ).val().length ) ? jQuery( this ).parents( '.single-builder-dimension' ).find( 'div:nth-child(3) input' ).val() : '0px' ) + ' ' +
								( ( jQuery( this ).parents( '.single-builder-dimension' ).find( 'div:nth-child(4) input' ).val().length ) ? jQuery( this ).parents( '.single-builder-dimension' ).find( 'div:nth-child(4) input' ).val() : '0px' )
							);
						} );
					} );
				}

				if ( $selectField.length ) {
					$selectField.chosen( {
						width: '100%',
						disable_search_threshold: 10
					} );
				}

				if ( $uploadButton.length ) {
					FusionPageBuilderApp.FusionBuilderActivateUpload( $uploadButton );
				}

				if ( $iconPicker.length ) {
					$iconPicker.each( function() {
						var $picker = jQuery( this );

						$value     = $picker.find( '.fusion-iconpicker-input' ).val();
						$id        = $picker.find( '.fusion-iconpicker-input' ).attr( 'id' );
						$container = $picker.find( '.icon_select_container' );
						$search    = $picker.find( '.fusion-icon-search' );

						FusionPageBuilderApp.fusion_builder_iconpicker( $value, $id, $container, $search );
					} );
				}

				if ( $multiselect.length ) {

					$multiselect.each( function() {

						$placeholderText = fusionBuilderText.select_options_or_leave_blank_for_all;
						if ( -1 !== jQuery( this ).attr( 'id' ).indexOf( 'cat_slug' ) ) {
							$placeholderText = fusionBuilderText.select_categories_or_leave_blank_for_all;
						} else if ( -1 !== jQuery( this ).attr( 'id' ).indexOf( 'exclude_cats' ) ) {
							$placeholderText = fusionBuilderText.select_categories_or_leave_blank_for_none;
						}

						jQuery( this ).chosen( {
							width: '100%',
							placeholder_text_multiple: $placeholderText
						} );
					} );
				}

				if ( $checkboxbuttonset.length ) {

					// For the visibility option check if choice is no or yes then convert to new style
					$visibility = this.$el.find( '.fusion-form-checkbox-button-set.hide_on_mobile' );
					if ( $visibility.length ) {
						$choice = $visibility.find( '.button-set-value' ).val();
						if ( 'no' === $choice || '' === $choice ) {
							$visibility.find( 'a' ).addClass( 'ui-state-active' );
						}
						if ( 'yes' === $choice ) {
							$visibility.find( 'a:not([data-value="small-visibility"])' ).addClass( 'ui-state-active' );
						}
					}

					$checkboxbuttonset.find( 'a' ).on( 'click', function( e ) {
						e.preventDefault();
						$checkboxsetcontainer = jQuery( this ).parents( '.fusion-form-checkbox-button-set' );
						jQuery( this ).toggleClass( 'ui-state-active' );
						$checkboxsetcontainer.find( '.button-set-value' ).val( $checkboxsetcontainer.find( '.ui-state-active' ).map( function( _, el ) {
							return jQuery( el ).data( 'value' );
						} ).get() );
					} );
				}

				if ( $radiobuttonset.length ) {
					$radiobuttonset.find( 'a' ).on( 'click', function( e ) {
						e.preventDefault();
						$radiosetcontainer = jQuery( this ).parents( '.fusion-form-radio-button-set' );
						$radiosetcontainer.find( '.ui-state-active' ).removeClass( 'ui-state-active' );
						jQuery( this ).addClass( 'ui-state-active' );
						$radiosetcontainer.find( '.button-set-value' ).val( $radiosetcontainer.find( '.ui-state-active' ).data( 'value' ) ).trigger( 'change' );
					} );
				}

				function createSlider( $slide, $targetId, $rangeInput, $min, $max, $step, $value, $decimals, $rangeDefault, $hiddenValue, $defaultValue, $direction ) {

					// Create slider with values passed on in data attributes.
					var $slider = noUiSlider.create( $rangeSlider[$slide], {
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
						} );
					}

					// On slider move, update input
					$slider.on( 'update', function( values, handle ) {
						if ( $rangeDefault && $notFirst ) {
							$rangeDefault.parent().removeClass( 'checked' );
							$hiddenValue.val( values[handle] );
						}
						$notFirst = true;
						jQuery( this.target ).closest( '.fusion-slider-container' ).prev().val( values[handle] ).trigger( 'change' );
						$thisEl.find( '#' + $targetId ).trigger( 'change' );
					} );

					// On manual input change, update slider position.
					$rangeInput.on( 'blur', function( values, handle ) {

						// If slider already has value, do nothing.
						if ( this.value === $rangeSlider[$slide].noUiSlider.get() ) {
							return;
						}
						if ( $rangeDefault ) {
							$rangeDefault.parent().removeClass( 'checked' );
							$hiddenValue.val( values[handle] );
						}

						if ( $min <= this.value && $max >= this.value ) {
							$rangeSlider[$slide].noUiSlider.set( this.value );
						} else if ( $min > this.value ) {
							$rangeSlider[$slide].noUiSlider.set( $min );
						} else if ( $max < this.value ) {
							$rangeSlider[$slide].noUiSlider.set( $max );
						}

					} );
				}

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

						// Check if parent has another value set to override TO default.
						if ( 'undefined' !== typeof parentValues && 'undefined' !== typeof parentValues[ $targetId ] && $rangeDefault ) {

							//  Set default values to new value.
							jQuery( this ).parents( '.fusion-builder-option' ).find( '.fusion-range-default' ).data( 'default', parentValues[ $targetId ] );
							$defaultValue = parentValues[ $targetId ];

							// If no current value is set, also update $value as representation on load.
							if ( ! $hiddenValue || '' === $hiddenValue.val() ) {
								$value = $defaultValue;
							}
						}

						createSlider( $i, $targetId, $rangeInput, $min, $max, $step, $value, $decimals, $rangeDefault, $hiddenValue, $defaultValue, $direction );

						$i++;
					} );

				}

				// TODO: fix for WooCommerce element.
				if ( 'undefined' !== typeof this.model.get && 'fusion_woo_shortcodes' === this.model.get( 'element_type' ) ) {
					if ( true === FusionPageBuilderApp.shortcodeGenerator ) {
						this.$el.find( '#element_content' ).attr( 'id', 'generator_element_content' );
					}
				}

				// If there is tiny mce editor ( tinymce element option )
				if ( $contentTextarea.length ) {
					$contentTextareaOption = $contentTextarea.closest( '.fusion-builder-option' );

					// Multi element ( parent )
					if ( 'undefined' !== typeof this.model.get( 'multi' ) && 'multi_element_parent' === this.model.get( 'multi' ) ) {

						viewCID = FusionPageBuilderViewManager.generateCid();

						this.view_cid = viewCID;

						$contentTextareaOption.hide();

						$contentTextarea.attr( 'id', 'fusion_builder_content_main' );

						view = new FusionPageBuilder.MultiElementSortablesView( {
							model: this,
							el: this.$el.find( '.fusion-builder-option-advanced-module-settings' ),
							attributes: {
								cid: viewCID
							}
						} );

						FusionPageBuilderViewManager.addView( viewCID, view );

						$contentTextareaOption.before( view.render() );

						if ( '' !== $contentTextarea.html() ) {
							view.generateMultiElementChildSortables( $contentTextarea.html(), this.$el.find( '.fusion-builder-option-advanced-module-settings' ).data( 'element_type' ), fixSettingsLvl, parentAtts );
						}

					// Standard element
					} else {

						content = $contentTextarea.html();

						// Called from shortcode generator
						if ( true === FusionPageBuilderApp.shortcodeGenerator ) {

							// TODO: unique id ( multiple mce )
							if ( true === FusionPageBuilderApp.shortcodeGeneratorMultiElementChild ) {
								$contentTextarea.attr( 'id', 'generator_multi_child_content' );
							} else {
								$contentTextarea.attr( 'id', 'generator_element_content' );
							}

							textareaID = $contentTextarea.attr( 'id' );

							setTimeout( function() {

								$contentTextarea.wp_editor( content, textareaID );

								// If it is a placeholder, add an on focus listener.
								if ( jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
									window.tinyMCE.get( textareaID ).on( 'focus', function() {
										$theContent = window.tinyMCE.get( textareaID ).getContent();
										$theContent = jQuery( '<div/>' ).html( $theContent ).text();
										if ( $theContent === jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
											window.tinyMCE.get( textareaID ).setContent( '' );
										}
									} );
								}

							}, 100 );

						} else {

							textareaID = $contentTextarea.attr( 'id' );

							setTimeout( function() {

								if ( 'undefined' !== typeof thisModel.get( 'allow_generator' ) && true === thisModel.get( 'allow_generator' ) ) {
									allowGenerator = true;
								}

								$contentTextarea.wp_editor( content, textareaID, allowGenerator );

								// If it is a placeholder, add an on focus listener.
								if ( jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
									window.tinyMCE.get( textareaID ).on( 'focus', function() {
										$theContent = window.tinyMCE.get( textareaID ).getContent();
										$theContent = jQuery( '<div/>' ).html( $theContent ).text();
										if ( $theContent === jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
											window.tinyMCE.get( textareaID ).setContent( '' );
										}
									} );
								}

							}, 100 );

						}

					}

				}

				// Attachment upload alert.
				this.$el.find( '.uploadattachment .fusion-builder-upload-button' ).on( 'click', function() {
					alert( fusionBuilderText.to_add_images );
				} );

				setTimeout( function() {
					$thisEl.find( 'select, input, textarea, radio' ).filter( ':eq(0)' ).not( '[data-placeholder]' ).focus();
				}, 1 );

				// Range option preview
				FusionPageBuilderApp.rangeOptionPreview( this.$el );

				// Check option dependencies
				if ( 'undefined' !== typeof this.model && 'undefined' !== typeof this.model.get ) {
					FusionPageBuilderApp.checkOptionDependency( fusionAllElements[ this.model.get( 'element_type' ) ], this.$el, parentValues );
				}

				return this;

			},

			removeElement: function() {

				// Remove settings modal on save or close/cancel
				this.remove();
			},

			colorChange: function( value, self, defaultReset, customDefault ) {
				var defaultColor = ( customDefault ) ? customDefault : self.data( 'default' );

				if ( value === defaultColor ) {
					defaultReset.addClass( 'checked' );
				} else {
					defaultReset.removeClass( 'checked' );
				}

				if ( '' === value && null !== defaultColor ) {
					self.val( defaultColor );
					self.change();
					self.val( '' );
				}
			},

			colorClear: function( event, self, customDefault ) {
				var  defaultColor = ( customDefault ) ? customDefault : self.data( 'default' );

				if ( null !== defaultColor ) {
					self.val( defaultColor );
					self.change();
					self.val( '' );
					self.parent().parent().find( '.wp-color-result' ).css( 'background-color', defaultColor );
				}
			},

			renderAttachments: function( ids, $multipleImageContainer ) {
				var $imageHTML,
					attachment,
					imageSizes,
					thumbnail,
					image;

				if ( 0 < ids.length ) {
					jQuery.each( ids, function( index, id ) {
						if ( '' !== id && 'NaN' !== id ) {
							attachment = wp.media.attachment( id );
							imageSizes = attachment.get( 'sizes' );

							if ( 'undefined' !== typeof imageSizes['200'] ) {
								image = imageSizes['200'].url;
							} else if ( 'undefined' !== typeof imageSizes.thumbnail ) {
								image = imageSizes.thumbnail.url;
							} else {
								image = attachment.get( 'url' );
							}

							$imageHTML  = '<div class="fusion-multi-image" data-image-id="' + attachment.get( 'id' ) + '">';
							$imageHTML += '<img src="' + image + '"/>';
							$imageHTML += '<span class="fusion-multi-image-remove dashicons dashicons-no-alt"></span>';
							$imageHTML += '</div>';
							$multipleImageContainer.append( $imageHTML );
						}
					} );
				}
			}
		} );
	} );
} ( jQuery ) );

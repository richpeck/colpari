/**
 *
 * -----------------------------------------------------------
 *
 * Codestar Framework
 * A Lightweight and easy-to-use WordPress Options Framework
 *
 * Copyright 2015 Codestar <info@codestarlive.com>
 *
 * -----------------------------------------------------------
 *
 */
;
(function($, window, document, undefined) {
	'use strict';
	$.CSFRAMEWORK = $.CSFRAMEWORK || {};
	$.ULS = $.ULS || {};
	// caching selector
	var $cs_body = $('body');
	// caching variables
	var cs_is_rtl = $cs_body.hasClass('rtl');
	// ======================================================
	// CSFRAMEWORK TAB NAVIGATION
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_TAB_NAVIGATION = function() {
		return this.each(function() {
			var $this = $(this),
				$nav = $this.find('.cs-nav'),
				$reset = $this.find('.cs-reset'),
				$expand = $this.find('.cs-expand-all');
			$nav.find('ul:first a').on('click', function(e) {
				e.preventDefault();
				var $el = $(this),
					$next = $el.next(),
					$target = $el.data('section');
				if ($next.is('ul')) {
					$next.slideToggle('fast');
					$el.closest('li').toggleClass('cs-tab-active');
				} else {
					$('#cs-tab-' + $target).fadeIn('fast').siblings().hide();
					$nav.find('a').removeClass('cs-section-active');
					$el.addClass('cs-section-active');
					$reset.val($target);
				}
			});
			$expand.on('click', function(e) {
				e.preventDefault();
				$this.find('.cs-body').toggleClass('cs-show-all');
				$(this).find('.fa').toggleClass('fa-eye-slash').toggleClass('fa-eye');
			});
		});
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK DEPENDENCY
	// ------------------------------------------------------
	$.CSFRAMEWORK.DEPENDENCY = function(el, param) {
		// Access to jQuery and DOM versions of element
		var base = this;
		base.$el = $(el);
		base.el = el;
		base.init = function() {
			base.ruleset = $.deps.createRuleset();
			// required for shortcode attrs
			var cfg = {
				show: function(el) {
					el.removeClass('hidden');
				},
				hide: function(el) {
					el.addClass('hidden');
				},
				log: false,
				checkTargets: false
			};
			if (param !== undefined) {
				base.depSub();
			} else {
				base.depRoot();
			}
			$.deps.enable(base.$el, base.ruleset, cfg);
		};
		base.depRoot = function() {
			base.$el.each(function() {
				$(this).find('[data-controller]').each(function() {
					var $this = $(this),
						_controller = $this.data('controller').split('|'),
						_condition = $this.data('condition').split('|'),
						_value = $this.data('value').toString().split('|'),
						_rules = base.ruleset;
					$.each(_controller, function(index, element) {
						var value = _value[index] || '',
							condition = _condition[index] || _condition[0];
						_rules = _rules.createRule('[data-depend-id="' + element + '"]', condition, value);
						_rules.include($this);
					});
				});
			});
		};
		base.depSub = function() {
			base.$el.each(function() {
				$(this).find('[data-sub-controller]').each(function() {
					var $this = $(this),
						_controller = $this.data('sub-controller').split('|'),
						_condition = $this.data('sub-condition').split('|'),
						_value = $this.data('sub-value').toString().split('|'),
						_rules = base.ruleset;
					$.each(_controller, function(index, element) {
						var value = _value[index] || '',
							condition = _condition[index] || _condition[0];
						_rules = _rules.createRule('[data-sub-depend-id="' + element + '"]', condition, value);
						_rules.include($this);
					});
				});
			});
		};
		base.init();
	};
	$.fn.CSFRAMEWORK_DEPENDENCY = function(param) {
		return this.each(function() {
			new $.CSFRAMEWORK.DEPENDENCY(this, param);
		});
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK CHOSEN
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_CHOSEN = function() {
		return this.each(function() {
			// Added to render only visible fields, not in template groups
			var is_in_group_template = $(this).parents('.cs-group-template');
			if (!is_in_group_template.length){
				$(this).chosen({
					allow_single_deselect: true,
					disable_search_threshold: 15,
					// width: parseFloat($(this).actual('width') + 25) + 'px' // commented to use default input width
					width: 'calc'
				});
			}
		});
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK IMAGE SELECTOR
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_IMAGE_SELECTOR = function() {
		return this.each(function() {
			$(this).find('label').on('click', function() {
				$(this).siblings().find('input').prop('checked', false);
			});
		});
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK SORTER
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_SORTER = function() {
		return this.each(function() {
			var $this = $(this),
				$enabled = $this.find('.cs-enabled'),
				$disabled = $this.find('.cs-disabled');
			$enabled.sortable({
				connectWith: $disabled,
				placeholder: 'ui-sortable-placeholder',
				update: function(event, ui) {
					var $el = ui.item.find('input');
					if (ui.item.parent().hasClass('cs-enabled')) {
						$el.attr('name', $el.attr('name').replace('disabled', 'enabled'));
					} else {
						$el.attr('name', $el.attr('name').replace('enabled', 'disabled'));
					}
				}
			});
			// avoid conflict
			$disabled.sortable({
				connectWith: $enabled,
				placeholder: 'ui-sortable-placeholder'
			});
		});
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK MEDIA UPLOADER / UPLOAD
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_UPLOADER = function() {
		return this.each(function() {
			var $this = $(this),
				$add = $this.find('.cs-add'),
				$input = $this.find('input'),
				wp_media_frame;
			$add.on('click', function(e) {
				e.preventDefault();
				// Check if the `wp.media.gallery` API exists.
				if (typeof wp === 'undefined' || !wp.media || !wp.media.gallery) {
					return;
				}
				// If the media frame already exists, reopen it.
				if (wp_media_frame) {
					wp_media_frame.open();
					return;
				}
				// Create the media frame.
				wp_media_frame = wp.media({
					// Set the title of the modal.
					title: $add.data('frame-title'),
					// Tell the modal to show only images.
					library: {
						type: $add.data('upload-type')
					},
					// Customize the submit button.
					button: {
						// Set the text of the button.
						text: $add.data('insert-title'),
					}
				});
				// When an image is selected, run a callback.
				wp_media_frame.on('select', function() {
					// Grab the selected attachment.
					var attachment = wp_media_frame.state().get('selection').first();
					$input.val(attachment.attributes.url).trigger('change');
				});
				// Finally, open the modal.
				wp_media_frame.open();
			});
		});
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK IMAGE UPLOADER
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_IMAGE_UPLOADER = function() {
		return this.each(function() {
			var $this    = $(this),
				$add     = $this.find('.cs-add'),
				$preview = $this.find('.cs-image-preview'),
				$remove  = $this.find('.cs-remove'),
				$input   = $this.find('input'),
				$img     = $this.find('img'),
				wp_media_frame;
				
			$add.on('click', function(e) {
				e.preventDefault();
				// Check if the `wp.media.gallery` API exists.
				if (typeof wp === 'undefined' || !wp.media || !wp.media.gallery) {
					return;
				}
				// If the media frame already exists, reopen it.
				if (wp_media_frame) {
					wp_media_frame.open();
					return;
				}
				// Create the media frame.
				wp_media_frame = wp.media({
					// Set the title of the modal.
					title: $add.data('frame-title'),
					// Tell the modal to show only images.
					library: {
						type: 'image'
					},
					// Customize the submit button.
					button: {
						// Set the text of the button.
						text: $add.data('insert-title'),
					}
				});
				// When an image is selected, run a callback.
				wp_media_frame.on('select', function() {
					var attachment = wp_media_frame.state().get('selection').first().attributes;
					var thumbnail = (typeof attachment.sizes.thumbnail !== 'undefined') ? attachment.sizes.thumbnail.url : attachment.url;
					$preview.removeClass('hidden');
					$img.attr('src', thumbnail);
					$input.val(attachment.id).trigger('change');
				});
				// Finally, open the modal.
				wp_media_frame.open();
			});
			// Remove image
			$remove.on('click', function(e) {
				e.preventDefault();
				$input.val('').trigger('change');
				$preview.addClass('hidden');
			});
		});
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK IMAGE GALLERY
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_IMAGE_GALLERY = function() {
		return this.each(function() {
			var $this = $(this),
				$edit = $this.find('.cs-edit'),
				$remove = $this.find('.cs-remove'),
				$list = $this.find('ul'),
				$input = $this.find('input'),
				$img = $this.find('img'),
				wp_media_frame,
				wp_media_click;
			$this.on('click', '.cs-add, .cs-edit', function(e) {
				var $el = $(this),
					what = ($el.hasClass('cs-edit')) ? 'edit' : 'add',
					state = (what === 'edit') ? 'gallery-edit' : 'gallery-library';
				e.preventDefault();
				// Check if the `wp.media.gallery` API exists.
				if (typeof wp === 'undefined' || !wp.media || !wp.media.gallery) {
					return;
				}
				// If the media frame already exists, reopen it.
				//
				// Comentado para poder hacer que la galeria se actualice cada vez que se abre
				//
				// if (wp_media_frame) {
				// 	wp_media_frame.open();
				// 	wp_media_frame.setState(state);
				// 	return;
				// }

				// Create the media frame.
				wp_media_frame = wp.media({
					title: 'Select or Upload Media Of Your Chosen Persuasion',
					button: {
						text: 'Use this media'
					},
					library: {
						type: 'image'
					},
					frame: 'post',
					state: 'gallery',
					multiple: true
				});
				// Open the media frame.
				wp_media_frame.on('open', function() {
					var $input 	= $this.find('input');
					var ids 	= $input.val();

					if (ids) {
						var get_array = ids.split(',');
						var library = wp_media_frame.state('gallery-edit').get('library');
						wp_media_frame.setState(state);
						get_array.forEach(function(id) {
							var attachment = wp.media.attachment(id);
							library.add(attachment ? [attachment] : []);
						});
					}
				});
				// When an image is selected, run a callback.
				wp_media_frame.on('update', function() {
					var inner = '';
					var ids = [];
					var images = wp_media_frame.state().get('library');
					images.each(function(attachment) {
						var attributes = attachment.attributes;
						var thumbnail = (typeof attributes.sizes.thumbnail !== 'undefined') ? attributes.sizes.thumbnail.url : attributes.url;
						inner += '<li data-image-id="'+attributes.id+'"><img src="' + thumbnail + '"></li>';
						ids.push(attributes.id);
					});
					$input.val(ids).trigger('change');
					$list.html('').append(inner);
					$remove.removeClass('hidden');
					$edit.removeClass('hidden');
				});
				// Finally, open the modal.
				wp_media_frame.open();
				wp_media_click = what;
			});
			// Remove image
			$remove.on('click', function(e) {
				e.preventDefault();
				$list.html('');
				$input.val('').trigger('change');
				$remove.addClass('hidden');
				$edit.addClass('hidden');
			});



			// Sortable Funcionality
			// -------------------------------------------------------
			$list.sortable({
				helper: 'original',
				cursor: 'move',
				placeholder: 'widget-placeholder',
				stop: function(event, ui) {
					var parent 	= ui.item.parents('.cs-fieldset'),
						input 	= parent.children('input'),
						list 	= parent.children('ul'),
						ids 	= [];

					$('li',list).each(function(){
						ids.push($(this).data("imageId"));
					});

					// order = order.toString();
					input.val(ids).trigger('change');
					console.log(ids);
				}
			});
			$list.disableSelection();
		});
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK TYPOGRAPHY
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_TYPOGRAPHY = function() {
		return this.each(function() {
			var typography 				= $(this),
				family_select 			= typography.find('.cs-typo-family'),
				variants_select 		= typography.find('.cs-typo-variant'),
				typography_type 		= typography.find('.cs-typo-font'),
				typography_size			= typography.find('.cs-typo-size'),
				typography_height 		= typography.find('.cs-typo-height'),
				typography_spacing 		= typography.find('.cs-typo-spacing'),
				typography_align		= typography.find('.cs-typo-align'),
				typography_transform 	= typography.find('.cs-typo-transform'),
				typography_color 		= typography.find('.cs-typo-color');

			family_select.on('change', function() {
				var _this = $(this),
					_type = _this.find(':selected').data('type') || 'custom',
					_variants = _this.find(':selected').data('variants');
				if (variants_select.length) {
					variants_select.find('option').remove();
					$.each(_variants.split('|'), function(key, text) {
						variants_select.append('<option value="' + text + '">' + text + '</option>');
					});
					variants_select.find('option[value="regular"]').attr('selected', 'selected').trigger('chosen:updated');
				}
				typography_type.val(_type);
			});

			// Typography Advanced Live Preview
			// ---------------------------------------------
			var preview 		= $(".cs-typo-preview",typography),
				previewToggle	= $(".cs-typo-preview-toggle",preview),
				previewId		= $(preview).data("previewId"),
				currentFamily 	= $(this).find('.cs-typo-family').val();
			
			var livePreviewRefresh = function(){
				var preview_weight 		= variants_select.val(),
					preview_size		= typography_size.val(),
					preview_height		= typography_height.val(),
					preview_spacing		= typography_spacing.val(),
					preview_align 		= typography_align.val(),
					preview_transform	= typography_transform.val(),
					preview_color 		= typography_color.val();

				var style = {
					"--cs-typo-preview-weight":preview_weight,
					"--cs-typo-preview-size":preview_size+"px",
					"--cs-typo-preview-height":preview_height+"px",
					"--cs-typo-preview-spacing":preview_spacing+"px",
					"--cs-typo-preview-align":preview_align,
					"--cs-typo-preview-transform":preview_transform,
					"--cs-typo-preview-color":preview_color
				};
				setPreviewStyle("#"+$(preview).attr("id"),style);
			}

			// Update Preview
			// ------------------------------
			if (preview.length){
				$(preview).css("font-family", currentFamily);
				$('head').append('<link href="http://fonts.googleapis.com/css?family=' + currentFamily +'" class="'+previewId+'" rel="stylesheet" type="text/css" />').load();
				livePreviewRefresh();
			}

			family_select.on('change',function(){
				$('head').find("."+previewId).remove();
				var font = $(this).val();
				$(preview).css("font-family", font);
				$('head').append('<link href="http://fonts.googleapis.com/css?family=' + font +'" class="'+previewId+'" rel="stylesheet" type="text/css" />').load();
				livePreviewRefresh();
			});

			variants_select.on('change',function(){ livePreviewRefresh(); });
			typography_type.on('change',function(){ livePreviewRefresh(); });
			typography_size.on('change',function(){ livePreviewRefresh(); });
			typography_height.on('change',function(){ livePreviewRefresh(); });
			typography_align.on('change',function(){ livePreviewRefresh(); });
			typography_color.on('change',function(){ livePreviewRefresh(); });
			typography_spacing.on('change',function(){ livePreviewRefresh(); });
			typography_transform.on('change',function(){ livePreviewRefresh(); });

			// Toggle Preview BG Style
			// ------------------------------
			$(previewToggle).on("click",function(){
				$(preview).toggleClass("cs-typo-preview-toggle_dark");
			});



			//-----------------------------------------------------------------
			// HELPER FUNCTIONS
			//-----------------------------------------------------------------
			function setPreviewStyle( element, propertyObject ){
				var elem = document.querySelector(element).style;
				for (var property in propertyObject){
					elem.setProperty(property, propertyObject[property]);
				}
			}

			function removeStyle( element, propertyObject){
				var elem = document.querySelector(element).style;
				for (var property in propertyObject){
					elem.removeProperty(propertyObject[property]);
				}
			}
		});
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK GROUP
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_GROUP = function() {
		// return this.each(function() {
			var options = {
				accordion: true,
				sort: true
			};

			var _this = $(this),
				field_groups 	= $('.cs-groups',_this),
				accordion_group = $('.cs-accordion',_this);

			// Accordion Funcionality
			// -------------------------------------------------------
			if ((accordion_group.length) && (options.accordion)) {
				accordion_group.accordion({
					header: '> div > .cs-group-title',
					collapsible: true,
					active: false,
					animate: 350,
					heightStyle: 'content',
					icons: {
						'header': 'dashicons dashicons-arrow-down',
						'activeHeader': 'dashicons dashicons-arrow-up'
					},
					beforeActivate: function(event, ui) {
						$(ui.newPanel).CSFRAMEWORK_DEPENDENCY('sub');
					}
				});
			}


			// Sortable Funcionality
			// -------------------------------------------------------
			if (options.sort) {
				field_groups.sortable({
					axis: 'xy',
					handle: '.cs-group-title',
					helper: 'original',
					cursor: 'move',
					placeholder: 'widget-placeholder',
					start: function(event, ui) {
						var inside = ui.item.children('.cs-group-content');
						if (inside.css('display') === 'block') {
							inside.hide();
							field_groups.sortable('refreshPositions');
						}
					},
					stop: function(event, ui) {
						ui.item.children('.cs-group-title').triggerHandler('focusout');
						accordion_group.accordion({
							active: false
						});
						field_groups.sortable('refreshPositions');
					}
				});	
			}
			


			// Remove Button
			// -------------------------------------------------------
			field_groups.on('click', '.cs-remove-group', function(e) {
				e.preventDefault();
				$(this).closest('.cs-group').remove();
			});


			// Add Button
			// -------------------------------------------------------
			var i = 0;
			// _this.on('click','.cs-add-group', function(e) {
			$('.cs-add-group',_this).on('click',function(e){
				e.preventDefault();

				// Set new vars
				var btn 			= $(this),
					parent 			= btn.parent('.cs-field-group'),
					group_template 	= btn.siblings('.cs-group-template'),
					groups_wrapper	= btn.siblings('.cs-groups'),
					// group_index 	= group_template.attr("groupIndex"),
					group_index 	= group_template.attr("data-group-index"),
					group_index_new = (parseInt(group_index, 10) + 1);







				// Incrementar ID grupo
				// console.log("Nuevo Grupo ID: "+group_index);
				// group_template.data("groupIndex",group_index_new);
				group_template.attr("data-group-index",group_index_new);







				// Buscando Grupos
				var grupos = btn.parents('.cs-field-group');
				// console.log("Hay "+grupos.length+" niveles de anidamiento");

				var getgroupid = function(element,origin){
					// Comprobar que es un subgrupo
					var subgroup 	= element.parents('.cs-group'),
						id_act 		= 0,
						id_origin 	= 0;


					if (origin.length) {
						var parent 		= origin.parents('.cs-field-group'),
							origin 		= parent.length,
							id_origin 	= parent.parents('.cs-group').attr("data-group-index");
						if (origin == 1) {
							console.info("Origen Indeterminado");
						} else {
							console.log("ORIGEN LENGHT >> "+origin+" ID DE ORIGEN: "+id_origin);
						}
					}


					if (subgroup.length) {
						// Esto Está OK
						console.log("El field es parte de un grupo");
						id_act = element.closestDescendant('.cs-group-template').attr("data-group-index");
					} else {
						// Desarrollando!
						if (origin > 1){
							console.info("El field NO ESTA en un grupo, su ID será: "+id_origin);
							id_act = id_origin;
						} else {
							console.info("El field NO ESTA en un grupo. ID NORMAL");
							id_act = element.children('.cs-group-template').attr("data-group-index");
						}
					}

					// console.log("Actual: "+id_act+" Inicial: "+id_ini);
					return id_act;
				}

				// var id_actual = btn.parents(".cs-group").attr("data-group-index");

				// if (!id_actual) {
				// 	console.log("Primer Grupo ID: 0");
				// } else {
				// 	for (var i = 0; i < grupos.length; i++) {
				// 		console.log("ID Grupo actual: "+id_actual);
				// 	}
				// }

				var ids = [];
				for (var i = grupos.length - 1; i >= 0; i--) {
					// console.log(grupos[i]); // Sirve para mostrar en el inspector de elementos el grupo seleccionado

					var g 		= $(grupos[i]);
					var g_id 	= getgroupid(g,btn);

					ids.push(g_id);

					console.log("I: "+i+" ID:"+g_id);
				}

				

				// var newid = (laid) ? laid : group_index;
				// console.log("===================================");
				// console.log("GRUPOS ANIDADOS: "+grupos.length);
				// grupos.each(function(){
				// 	// var id = $(this).closest('.cs-group-template').data('groupIndex');
				// 	var g = $(this).closestDescendant('.cs-groups'),
				// 		sg = g.children(".cs-group");
				// 	// console.log(g);
				// 	console.log(">> Cantidad Grupos: "+sg.length);
				// });
				// console.log("===================================");

				group_template.find('> .cs-group-content > .cs-element > .cs-fieldset > input, > .cs-group-content > .cs-element > .cs-fieldset > select, > .cs-group-content > .cs-element > .cs-fieldset > textarea').each(function() {
					var i = 0;
					this.name = this.name.replace(/\[(\d+)\]/g, function(string, id) {
					// this.name = this.name.replace(/\[(\d+)\](?![\s\S]*\[(\d+)\])/, function(string, id) {
						// return '[' + (parseInt(id, 10) + 1) + ']';
						var newid = ids[i]; i++;
						return '['+newid+']';
					});
					console.log(this.name);
				});
				var cloned = group_template.clone().removeClass('hidden').removeClass('cs-group-template');

				cloned.appendTo(groups_wrapper);

				if ((accordion_group.length) && (options.accordion)) {
					groups_wrapper.accordion('refresh');
					groups_wrapper.accordion({
						active: cloned.index()
					});
				}

				field_groups.find('input, select, textarea').each(function() {
					this.name = this.name.replace('[_nonce]', '');
				});





				// run all field plugins
				cloned.CSFRAMEWORK_DEPENDENCY('sub');
				cloned.CSFRAMEWORK_RELOAD_PLUGINS();

				cloned.CSFRAMEWORK_GROUP();
				i++;
			});
		// });
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK RESET CONFIRM
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_CONFIRM = function() {
		return this.each(function() {
			$(this).on('click', function(e) {
				if (!confirm('Are you sure?')) {
					e.preventDefault();
				}
			});
		});
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK SAVE OPTIONS
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_SAVE = function() {
		return this.each(function() {
			var $this = $(this),
				$text = $this.data('save'),
				$value = $this.val(),
				$ajax = $('#cs-save-ajax');
			$(document).on('keydown', function(event) {
				if (event.ctrlKey || event.metaKey) {
					if (String.fromCharCode(event.which).toLowerCase() === 's') {
						event.preventDefault();
						$this.trigger('click');
					}
				}
			});
			$this.on('click', function(e) {
				if ($ajax.length) {
					if (typeof tinyMCE === 'object') {
						tinyMCE.triggerSave();
					}
					$this.prop('disabled', true).attr('value', $text);
					var serializedOptions = $('#csframework_form').serialize();
					$.post('options.php', serializedOptions).error(function() {
						alert('Error, Please try again.');
					}).success(function() {
						$this.prop('disabled', false).attr('value', $value);
						$ajax.hide().fadeIn().delay(250).fadeOut();
					});
					e.preventDefault();
				} else {
					$this.addClass('disabled').attr('value', $text);
				}
			});
		});
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK UI DIALOG OVERLAY HELPER
	// ------------------------------------------------------
	if (typeof $.widget !== 'undefined' && typeof $.ui !== 'undefined' && typeof $.ui.dialog !== 'undefined') {
		$.widget('ui.dialog', $.ui.dialog, {
			_createOverlay: function() {
				this._super();
				if (!this.options.modal) {
					return;
				}
				this._on(this.overlay, {
					click: 'close'
				});
			}
		});
	}
	// ======================================================
	// CSFRAMEWORK ICONS MANAGER
	// ------------------------------------------------------
	$.CSFRAMEWORK.ICONS_MANAGER = function() {
		var base = this,
			onload = true,
			$parent;
		base.init = function() {
			$cs_body.on('click', '.cs-icon-add', function(e) {
				e.preventDefault();
				var $this = $(this),
					$dialog = $('#cs-icon-dialog'),
					$load = $dialog.find('.cs-dialog-load'),
					$select = $dialog.find('.cs-dialog-select'),
					$insert = $dialog.find('.cs-dialog-insert'),
					$search = $dialog.find('.cs-icon-search');
				// set parent
				$parent = $this.closest('.cs-icon-select');
				// open dialog
				$dialog.dialog({
					width: 850,
					height: 700,
					modal: true,
					resizable: false,
					closeOnEscape: true,
					position: {
						my: 'center',
						at: 'center',
						of: window
					},
					open: function() {
						// fix scrolling
						$cs_body.addClass('cs-icon-scrolling');
						// fix button for VC
						$('.ui-dialog-titlebar-close').addClass('ui-button');
						// set viewpoint
						$(window).on('resize', function() {
							var height = $(window).height(),
								load_height = Math.floor(height - 237),
								set_height = Math.floor(height - 125);
							$dialog.dialog('option', 'height', set_height).parent().css('max-height', set_height);
							$dialog.css('overflow', 'auto');
							$load.css('height', load_height);
						}).resize();
					},
					close: function() {
						$cs_body.removeClass('cs-icon-scrolling');
					}
				});
				// load icons
				if (onload) {
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {
							action: 'cs-get-icons'
						},
						success: function(content) {
							$load.html(content);
							onload = false;
							$load.on('click', 'a', function(e) {
								e.preventDefault();
								var icon = $(this).data('icon');
								$parent.find('i').removeAttr('class').addClass(icon);
								$parent.find('input').val(icon).trigger('change');
								$parent.find('.cs-icon-preview').removeClass('hidden');
								$parent.find('.cs-icon-remove').removeClass('hidden');
								$dialog.dialog('close');
							});
							$search.keyup(function() {
								var value = $(this).val(),
									$icons = $load.find('a');
								$icons.each(function() {
									var $ico = $(this);
									if ($ico.data('icon').search(new RegExp(value, 'i')) < 0) {
										$ico.hide();
									} else {
										$ico.show();
									}
								});
							});
							$load.find('.cs-icon-tooltip').cstooltip({
								html: true,
								placement: 'top',
								container: 'body'
							});
							$load.accordion({
								collapsible: true,
								icons: {
									header: "dashicons dashicons-plus",
									activeHeader: "dashicons dashicons-minus"
								},
								heightStyle: "content"
							});
						}
					});
				}
			});
			$cs_body.on('click', '.cs-icon-remove', function(e) {
				e.preventDefault();
				var $this = $(this),
					$parent = $this.closest('.cs-icon-select');
				$parent.find('.cs-icon-preview').addClass('hidden');
				$parent.find('input').val('').trigger('change');
				$this.addClass('hidden');
			});
		};
		// run initializer
		base.init();
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK SHORTCODE MANAGER
	// ------------------------------------------------------
	$.CSFRAMEWORK.SHORTCODE_MANAGER = function() {
		var base = this,
			deploy_atts;
		base.init = function() {
			var $dialog = $('#cs-shortcode-dialog'),
				$insert = $dialog.find('.cs-dialog-insert'),
				$shortcodeload = $dialog.find('.cs-dialog-load'),
				$selector = $dialog.find('.cs-dialog-select'),
				shortcode_target = false,
				shortcode_name,
				shortcode_view,
				shortcode_clone,
				$shortcode_button,
				editor_id;
			$cs_body.on('click', '.cs-shortcode', function(e) {
				e.preventDefault();
				// init chosen
				$selector.CSFRAMEWORK_CHOSEN();
				$shortcode_button = $(this);
				shortcode_target = $shortcode_button.hasClass('cs-shortcode-textarea');
				editor_id = $shortcode_button.data('editor-id');
				$dialog.dialog({
					width: 850,
					height: 700,
					modal: true,
					resizable: false,
					closeOnEscape: true,
					position: {
						my: 'center',
						at: 'center',
						of: window
					},
					open: function() {
						// fix scrolling
						$cs_body.addClass('cs-shortcode-scrolling');
						// fix button for VC
						$('.ui-dialog-titlebar-close').addClass('ui-button');
						// set viewpoint
						$(window).on('resize', function() {
							var height = $(window).height(),
								load_height = Math.floor(height - 281),
								set_height = Math.floor(height - 125);
							$dialog.dialog('option', 'height', set_height).parent().css('max-height', set_height);
							$dialog.css('overflow', 'auto');
							$shortcodeload.css('height', load_height);
						}).resize();
					},
					close: function() {
						shortcode_target = false;
						$cs_body.removeClass('cs-shortcode-scrolling');
					}
				});
			});
			$selector.on('change', function() {
				var $elem_this = $(this);
				shortcode_name = $elem_this.val();
				shortcode_view = $elem_this.find(':selected').data('view');
				// check val
				if (shortcode_name.length) {
					$.ajax({
						type: 'POST',
						url: ajaxurl,
						data: {
							action: 'cs-get-shortcode',
							shortcode: shortcode_name
						},
						success: function(content) {
							$shortcodeload.html(content);
							$insert.parent().removeClass('hidden');
							shortcode_clone = $('.cs-shortcode-clone', $dialog).clone();
							$shortcodeload.CSFRAMEWORK_DEPENDENCY();
							$shortcodeload.CSFRAMEWORK_DEPENDENCY('sub');
							$shortcodeload.CSFRAMEWORK_RELOAD_PLUGINS();
						}
					});
				} else {
					$insert.parent().addClass('hidden');
					$shortcodeload.html('');
				}
			});
			$insert.on('click', function(e) {
				e.preventDefault();
				var send_to_shortcode = '',
					ruleAttr = 'data-atts',
					cloneAttr = 'data-clone-atts',
					cloneID = 'data-clone-id';
				switch (shortcode_view) {
					case 'contents':
						$('[' + ruleAttr + ']', '.cs-dialog-load').each(function() {
							var _this = $(this),
								_atts = _this.data('atts');
							send_to_shortcode += '[' + _atts + ']';
							send_to_shortcode += _this.val();
							send_to_shortcode += '[/' + _atts + ']';
						});
						break;
					case 'clone':
						send_to_shortcode += '[' + shortcode_name; // begin: main-shortcode
						// main-shortcode attributes
						$('[' + ruleAttr + ']', '.cs-dialog-load .cs-element:not(.hidden)').each(function() {
							var _this_main = $(this),
								_this_main_atts = _this_main.data('atts');
							console.log(_this_main_atts);
							send_to_shortcode += base.validate_atts(_this_main_atts, _this_main); // validate empty atts
						});
						send_to_shortcode += ']'; // end: main-shortcode attributes
						// multiple-shortcode each
						$('[' + cloneID + ']', '.cs-dialog-load').each(function() {
							var _this_clone = $(this),
								_clone_id = _this_clone.data('clone-id');
							send_to_shortcode += '[' + _clone_id; // begin: multiple-shortcode
							// multiple-shortcode attributes
							$('[' + cloneAttr + ']', _this_clone.find('.cs-element').not('.hidden')).each(function() {
								var _this_multiple = $(this),
									_atts_multiple = _this_multiple.data('clone-atts');
								// is not attr content, add shortcode attribute else write content and close shortcode tag
								if (_atts_multiple !== 'content') {
									send_to_shortcode += base.validate_atts(_atts_multiple, _this_multiple); // validate empty atts
								} else if (_atts_multiple === 'content') {
									send_to_shortcode += ']';
									send_to_shortcode += _this_multiple.val();
									send_to_shortcode += '[/' + _clone_id + '';
								}
							});
							send_to_shortcode += ']'; // end: multiple-shortcode
						});
						send_to_shortcode += '[/' + shortcode_name + ']'; // end: main-shortcode
						break;
					case 'clone_duplicate':
						// multiple-shortcode each
						$('[' + cloneID + ']', '.cs-dialog-load').each(function() {
							var _this_clone = $(this),
								_clone_id = _this_clone.data('clone-id');
							send_to_shortcode += '[' + _clone_id; // begin: multiple-shortcode
							// multiple-shortcode attributes
							$('[' + cloneAttr + ']', _this_clone.find('.cs-element').not('.hidden')).each(function() {
								var _this_multiple = $(this),
									_atts_multiple = _this_multiple.data('clone-atts');
								// is not attr content, add shortcode attribute else write content and close shortcode tag
								if (_atts_multiple !== 'content') {
									send_to_shortcode += base.validate_atts(_atts_multiple, _this_multiple); // validate empty atts
								} else if (_atts_multiple === 'content') {
									send_to_shortcode += ']';
									send_to_shortcode += _this_multiple.val();
									send_to_shortcode += '[/' + _clone_id + '';
								}
							});
							send_to_shortcode += ']'; // end: multiple-shortcode
						});
						break;
					default:
						send_to_shortcode += '[' + shortcode_name;
						$('[' + ruleAttr + ']', '.cs-dialog-load .cs-element:not(.hidden)').each(function() {
							var _this = $(this),
								_atts = _this.data('atts');
							// is not attr content, add shortcode attribute else write content and close shortcode tag
							if (_atts !== 'content') {
								send_to_shortcode += base.validate_atts(_atts, _this); // validate empty atts
							} else if (_atts === 'content') {
								send_to_shortcode += ']';
								send_to_shortcode += _this.val();
								send_to_shortcode += '[/' + shortcode_name + '';
							}
						});
						send_to_shortcode += ']';
						break;
				}
				if (shortcode_target) {
					var $textarea = $shortcode_button.next();
					$textarea.val(base.insertAtChars($textarea, send_to_shortcode)).trigger('change');
				} else {
					base.send_to_editor(send_to_shortcode, editor_id);
				}
				deploy_atts = null;
				$dialog.dialog('close');
			});
			// cloner button
			var cloned = 0;
			$dialog.on('click', '#shortcode-clone-button', function(e) {
				e.preventDefault();
				// clone from cache
				var cloned_el = shortcode_clone.clone().hide();
				cloned_el.find('input:radio').attr('name', '_nonce_' + cloned);
				$('.cs-shortcode-clone:last').after(cloned_el);
				// add - remove effects
				cloned_el.slideDown(100);
				cloned_el.find('.cs-remove-clone').show().on('click', function(e) {
					cloned_el.slideUp(100, function() {
						cloned_el.remove();
					});
					e.preventDefault();
				});
				// reloadPlugins
				cloned_el.CSFRAMEWORK_DEPENDENCY('sub');
				cloned_el.CSFRAMEWORK_RELOAD_PLUGINS();
				cloned++;
			});
		};
		base.validate_atts = function(_atts, _this) {
			var el_value;
			if (_this.data('check') !== undefined && deploy_atts === _atts) {
				return '';
			}
			deploy_atts = _atts;
			if (_this.closest('.pseudo-field').hasClass('hidden') === true) {
				return '';
			}
			if (_this.hasClass('pseudo') === true) {
				return '';
			}
			if (_this.is(':checkbox') || _this.is(':radio')) {
				el_value = _this.is(':checked') ? _this.val() : '';
			} else {
				el_value = _this.val();
			}
			if (_this.data('check') !== undefined) {
				el_value = _this.closest('.cs-element').find('input:checked').map(function() {
					return $(this).val();
				}).get();
			}
			if (el_value !== null && el_value !== undefined && el_value !== '' && el_value.length !== 0) {
				return ' ' + _atts + '="' + el_value + '"';
			}
			return '';
		};
		base.insertAtChars = function(_this, currentValue) {
			var obj = (typeof _this[0].name !== 'undefined') ? _this[0] : _this;
			if (obj.value.length && typeof obj.selectionStart !== 'undefined') {
				obj.focus();
				return obj.value.substring(0, obj.selectionStart) + currentValue + obj.value.substring(obj.selectionEnd, obj.value.length);
			} else {
				obj.focus();
				return currentValue;
			}
		};
		base.send_to_editor = function(html, editor_id) {
			var tinymce_editor;
			if (typeof tinymce !== 'undefined') {
				tinymce_editor = tinymce.get(editor_id);
			}
			if (tinymce_editor && !tinymce_editor.isHidden()) {
				tinymce_editor.execCommand('mceInsertContent', false, html);
			} else {
				var $editor = $('#' + editor_id);
				$editor.val(base.insertAtChars($editor, html)).trigger('change');
			}
		};
		// run initializer
		base.init();
	};
	// ======================================================
	// ======================================================
	// CSFRAMEWORK COLORPICKER
	// ------------------------------------------------------
	if (typeof Color === 'function') {
		// adding alpha support for Automattic Color.js toString function.
		Color.fn.toString = function() {
			// check for alpha
			if (this._alpha < 1) {
				return this.toCSS('rgba', this._alpha).replace(/\s+/g, '');
			}
			var hex = parseInt(this._color, 10).toString(16);
			if (this.error) {
				return '';
			}
			// maybe left pad it
			if (hex.length < 6) {
				for (var i = 6 - hex.length - 1; i >= 0; i--) {
					hex = '0' + hex;
				}
			}
			return '#' + hex;
		};
	}
	$.CSFRAMEWORK.PARSE_COLOR_VALUE = function(val) {
		var value = val.replace(/\s+/g, ''),
			alpha = (value.indexOf('rgba') !== -1) ? parseFloat(value.replace(/^.*,(.+)\)/, '$1') * 100) : 100,
			rgba = (alpha < 100) ? true : false;
		return {
			value: value,
			alpha: alpha,
			rgba: rgba
		};
	};
	$.fn.CSFRAMEWORK_COLORPICKER = function() {
		return this.each(function() {
			var $this = $(this);

			// check for user custom color palettes
			var picker_palettes = $this.data('colorpalettes');
			picker_palettes = (picker_palettes) ? picker_palettes.toString().split(",") : false;

			// check for rgba enabled/disable
			if ($this.data('rgba') !== false) {
				// parse value
				var picker = $.CSFRAMEWORK.PARSE_COLOR_VALUE($this.val());
				// wpColorPicker core
				$this.wpColorPicker({
					palettes: picker_palettes,
					// wpColorPicker: clear
					clear: function() {
						$this.trigger('keyup');
					},
					// wpColorPicker: change
					change: function(event, ui) {
						var ui_color_value = ui.color.toString();
						// update checkerboard background color
						$this.closest('.wp-picker-container').find('.cs-alpha-slider-offset').css('background-color', ui_color_value);
						$this.val(ui_color_value).trigger('change');
					},
					// wpColorPicker: create
					create: function() {
						// set variables for alpha slider
						var a8cIris = $this.data('a8cIris'),
							$container = $this.closest('.wp-picker-container'),
							// appending alpha wrapper
							$alpha_wrap = $('<div class="cs-alpha-wrap">' + '<div class="cs-alpha-slider"></div>' + '<div class="cs-alpha-slider-offset"></div>' + '<div class="cs-alpha-text"></div>' + '</div>').appendTo($container.find('.wp-picker-holder')),
							$alpha_slider = $alpha_wrap.find('.cs-alpha-slider'),
							$alpha_text = $alpha_wrap.find('.cs-alpha-text'),
							$alpha_offset = $alpha_wrap.find('.cs-alpha-slider-offset');
						// alpha slider
						$alpha_slider.slider({
							// slider: slide
							slide: function(event, ui) {
								var slide_value = parseFloat(ui.value / 100);
								// update iris data alpha && wpColorPicker color option && alpha text
								a8cIris._color._alpha = slide_value;
								$this.wpColorPicker('color', a8cIris._color.toString());
								$alpha_text.text((slide_value < 1 ? slide_value : ''));
							},
							// slider: create
							create: function() {
								var slide_value = parseFloat(picker.alpha / 100),
									alpha_text_value = slide_value < 1 ? slide_value : '';
								// update alpha text && checkerboard background color
								$alpha_text.text(alpha_text_value);
								$alpha_offset.css('background-color', picker.value);
								// wpColorPicker clear for update iris data alpha && alpha text && slider color option
								$container.on('click', '.wp-picker-clear', function() {
									a8cIris._color._alpha = 1;
									$alpha_text.text('');
									$alpha_slider.slider('option', 'value', 100).trigger('slide');
								});
								// wpColorPicker default button for update iris data alpha && alpha text && slider color option
								$container.on('click', '.wp-picker-default', function() {
									var default_picker = $.CSFRAMEWORK.PARSE_COLOR_VALUE($this.data('default-color')),
										default_value = parseFloat(default_picker.alpha / 100),
										default_text = default_value < 1 ? default_value : '';
									a8cIris._color._alpha = default_value;
									$alpha_text.text(default_text);
									$alpha_slider.slider('option', 'value', default_picker.alpha).trigger('slide');
								});
								// show alpha wrapper on click color picker button
								$container.on('click', '.wp-color-result', function() {
									$alpha_wrap.toggle();
								});
								// hide alpha wrapper on click body
								$cs_body.on('click.wpcolorpicker', function() {
									$alpha_wrap.hide();
								});
							},
							// slider: options
							value: picker.alpha,
							step: 1,
							min: 1,
							max: 100
						});
					}
				});
			} else {
				// wpColorPicker default picker
				$this.wpColorPicker({
					palettes: picker_palettes,
					clear: function() {
						$this.trigger('keyup');
					},
					change: function(event, ui) {
						$this.val(ui.color.toString()).trigger('change');
					}
				});
			}
		});
	};
	// ======================================================


	// ======================================================
	// Slider field by Castor Studio
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_SLIDER = function() {
		return this.each( function() {
			var dis 		= $( this ),
				input1 		= $('input[name$="[slider1]"]',dis),
				input2		= $('input[name$="[slider2]"]',dis),
				slider 		= $('.cs-slider > div.cs-slider-wrapper', dis ),
				data 		= $('.cs-slider',dis).data( 'sliderOptions' ),
				step 		= data.step || 1,
				min 		= data.min || 0,
				max 		= data.max || 100,
				round 		= data.round || false,
				tooltip 	= data.tooltip || false,
				handles 	= data.handles || false,
				has_input 	= data.input || false;

			var parseInteger = function(value){
				return parseFloat(parseFloat(value).toFixed(2));
			}

			var connect 	= (handles) ? [ false , true, false] : [ true , false ];
			var val 		= (handles) ? [ parseInteger(data.slider1) , parseInteger(data.slider2) ] : [ parseInteger(data.slider1) ];
			var tooltips	= (handles) ? ((tooltip) ? [ true, true ] : [ false, false ]) : ((tooltip) ? [ true ] : [ false ]);

			var slider = slider.get(0);
			noUiSlider.create(slider, {
				start: val,
				connect: connect,
				tooltips: tooltips,
				step: step,
				range: {
					'min': [  parseInteger( min ) ],
					'max': [ parseInteger( max ) ]
				}
			});

			slider.noUiSlider.on('update', function ( values, handle ) {
				var value = (round) ? Math.round(values[handle]) : values[handle];
				(handle ? input2 : input1).val( value );
			});

			input1.on("change",function(){
				var val1 = input1.val(),
					val2 = input2.val();

				var val 		= (handles) ? [ parseInteger(val1) , parseInteger(val2) ] : [ parseInteger(val1) ];
				updateSliderVal(val);
			});
			input2.on("change",function(){
				var val1 = input1.val(),
					val2 = input2.val();

				var val 		= (handles) ? [ parseInteger(val1) , parseInteger(val2) ] : [ parseInteger(val1) ];
				updateSliderVal(val);
			});

			function updateSliderVal(value) {
				slider.noUiSlider.updateOptions({
					start: value
				});
			}
		} );
	};
	// ======================================================


	// ======================================================
	// Easing Editor field by Castor Studio
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_EASINGEDITOR = function() {
		return this.each(function(){
			var $p1, $p2, $handle, $easingselector, $input, $inputType, $preview, $size, ctx;
			var self 	= this;

			ctx 			= $(".cs-easing-editor__bezierCurve",self).get(0).getContext("2d");
			$easingselector = $('.easingSelector',self);
			$input 			= $('input[name$="[easingSelector]"]',self);
			$inputType 		= $('input.easingSelectorType',self);
			$preview 		= $('.cs-easing-editor__preview',self).get(0);
			$size 			= 200;
			$p1				= $(".p1",self);
			$p2				= $(".p2",self);
			

			$(document).ready(function(){
				// Toggle Button
				// ------------------------------------------------------
				var btn_toggle 	= $('a.button[name$="[toggleEditor]"]',self),
					btn_icon 	= $("<span />",{ class: "dashicons dashicons-visibility"});

				btn_toggle.prepend(btn_icon);
				btn_toggle.on('click',function(){
					$('.cs-easing-editor__graph-outer-wrapper',self).slideToggle({
						start: function(){
							// $easingselector.trigger('change');

							var type 	= $('option',$easingselector).last().prop('selected'),
								// value 	= (type) ? getHandles(true) : $easingselector.val();
								value 	= $input.val();

							console.log(value);
							console.log($input.val());

							// Update Handles Positions
							updateHandles(value);	
							// Render Graph
							renderWrap(ctx);
						}
					});
					$('span',this).toggleClass('dashicons-hidden','dashicons-visibility');
				});


				// Easing Curve Graph - Dragable handles
				// - Draggable handles
				// - Easing Select box change event to update the graph
				// ------------------------------------------------------
				$(".p1, .p2",self).draggable({ 
					containment: 'parent',
					start: function(){
						setCustomEasing();
					},
					drag: function(event, ui) {
						renderWrap(ctx);
						setDemoValue('drag');
					},
					stop: function(){
						renderWrap(ctx);
						setTransitionFn();
						setDemoValue('drag');
					}
				});

				$easingselector.on('change', function(){
					var $this 	= $(this),
						value 	= $this.val();

					// Update Handles Positions
					updateHandles(value);

					// Render Graph
					renderWrap(ctx);
					setDemoValue();
				});


				// First Render Easing Curve Graph
				// ------------------------------------------------------
				renderWrap(ctx);
				setTransitionFn();				
				setDemoValue();
			});


			// HELPER FUNCTIONS
			// --------------------------------------------------------------------
			function setStyle( element, propertyObject ){
				var elem = element.style;
				for (var property in propertyObject){
					elem.setProperty(property, propertyObject[property]);
				}
			}
			function updateHandles(values){
				var values 	= values.split(",");

				$p1.css("left", values[0] * $size);
				$p1.css("top", 	(1 - values[1]) * $size);
				$p2.css("left", values[2] * $size);
				$p2.css("top", 	(1 - values[3]) * $size);
			}

			function getHandles(string){
				var handles = [],
					p1 		= $p1.position(),
					p2 		= $p2.position();

				if($.browser.mozilla) {
					var p1x = adjustValue( (p1.left) / $size);
					var p1y = adjustValue( 1 - (p1.top) / $size);
					var p2x = adjustValue( (p2.left) / $size);
					var p2y = adjustValue( 1 - (p2.top) / $size);
				} else {
					var p1x = adjustValue( (p1.top + 5) / $size);
					var p1y = adjustValue( 1 - (p1.left + 4) / $size);
					var p2x = adjustValue( (p2.top + 5) / $size);
					var p2y = adjustValue( 1 - (p2.left + 4) / $size);
				}

				handles.push(p1x);
				handles.push(p1y);
				handles.push(p2x);
				handles.push(p2y);

				if (string){
					handles = p1x +","+ p1y +","+ p2x +","+ p2y;
				}

				return handles;
			}

			function setCustomEasing(){
				$('option',$easingselector).last().prop('selected',true);
				$inputType.val('custom');
			}

			function setDemoValue(type) { 
				var value;
				if (type == 'drag') {
					value = getHandles();
					$inputType.val('custom');
				} else {
					value = $easingselector.val();
					$inputType.val('default');
				}
				$input.val(value);

				var style = {
					"--easingTypeAnimation":'cubic-bezier('+value+')'
				};
				setStyle($preview,style);
			}
			function setTransitionFn() {
				// console.log('Seteando estilo a la variable box');
			}

			// this just removes leading 0 and truncates values
			function adjustValue(val) {	
				val = val.toFixed(2);
				val = val.toString().replace("0.", ".").replace("1.00", "1").replace(".00", "0");
				return val;
			}
			
			function renderWrap(ctx) {
				var p1 = $p1.position(),
					p2 = $p2.position();
				
				render(ctx,
					{
						x: p1.left,
						y: p1.top
					}, 
					{
						x: p2.left,
						y: p2.top
					}
				);
			};

			function render(ctx, p1, p2) {
				var ctx = ctx;
				ctx.clearRect(0,0,$size,$size);
				
				ctx.setLineDash([]);
				ctx.beginPath();
				ctx.lineWidth = 3;
				ctx.strokeStyle = "#0073AA";
			    ctx.moveTo(0,$size);

				// p1 (x,y) p2 (x,y)
			    ctx.bezierCurveTo(p1.x,p1.y,p2.x,p2.y,$size,0);				
				ctx.stroke();
				ctx.closePath();
				
				ctx.setLineDash([4, 4]);
				ctx.beginPath();
				ctx.strokeStyle = "#444"; //"#e4e4e4" "#d6d6d6"
				ctx.lineWidth = 1;
				ctx.moveTo(0,$size);

				// p1 (x,y)
				ctx.lineTo(p1.x + 0,p1.y + 0);
				ctx.stroke(); 
				
				ctx.moveTo($size,0);

				// p2 (x,y)
				ctx.lineTo(p2.x + 0,p2.y + 0);
				ctx.stroke();
				ctx.closePath();
					
				if($.browser.mozilla) {
					$(".p1X", self).html( adjustValue( (p1.x) / $size) );
					$(".p1Y", self).html( adjustValue( 1 - (p1.y) / $size) );
					$(".p2X", self).html( adjustValue( (p2.x) / $size) );
					$(".p2Y", self).html( adjustValue( 1 - (p2.y) / $size) );
				} else {
					$(".p1X", self).html( adjustValue( (p1.x + 5) / $size) );
					$(".p1Y", self).html( adjustValue( 1 - (p1.y + 4) / $size) );
					$(".p2X", self).html( adjustValue( (p2.x + 5) / $size) );
					$(".p2Y", self).html( adjustValue( 1 - (p2.y + 4) / $size) );
				}
				
			}
		});
	};
	// ======================================================


	// ======================================================
	// CSFRAMEWORK TYPOGRAPHY ADVANCED
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_TYPOGRAPHY_ADVANCED = function() {
		return this.each(function() {
			var typography 				= $(this),
				family_select 			= typography.find('.cs-typo-family'),
				variants_select 		= typography.find('.cs-typo-variant'),
				typography_type 		= typography.find('.cs-typo-font'),
				typography_size			= typography.find('.cs-typo-size'),
				typography_height 		= typography.find('.cs-typo-height'),
				typography_spacing 		= typography.find('.cs-typo-spacing'),
				typography_align		= typography.find('.cs-typo-align'),
				typography_transform 	= typography.find('.cs-typo-transform'),
				typography_color 		= typography.find('.cs-typo-color');

			family_select.on('change', function() {
				var _this = $(this),
					_type = _this.find(':selected').data('type') || 'custom',
					_variants = _this.find(':selected').data('variants');
				if (variants_select.length) {
					variants_select.find('option').remove();
					$.each(_variants.split('|'), function(key, text) {
						variants_select.append('<option value="' + text + '">' + text + '</option>');
					});
					variants_select.find('option[value="regular"]').attr('selected', 'selected').trigger('chosen:updated');
				}
				typography_type.val(_type);
			});

			// Typography Advanced Live Preview
			// ---------------------------------------------
			var preview 		= $(".cs-typo-preview",typography),
				previewToggle	= $(".cs-typo-preview-toggle",preview),
				previewId		= $(preview).data("previewId"),
				currentFamily 	= $(this).find('.cs-typo-family').val();
			
			var livePreviewRefresh = function(){
				var preview_weight 		= variants_select.val(),
					preview_size		= typography_size.val(),
					preview_height		= typography_height.val(),
					preview_spacing		= typography_spacing.val(),
					preview_align 		= typography_align.val(),
					preview_transform	= typography_transform.val(),
					preview_color 		= typography_color.val();

				var style = {
					"--cs-typo-preview-weight":preview_weight,
					"--cs-typo-preview-size":preview_size+"px",
					"--cs-typo-preview-height":preview_height+"px",
					"--cs-typo-preview-spacing":preview_spacing+"px",
					"--cs-typo-preview-align":preview_align,
					"--cs-typo-preview-transform":preview_transform,
					"--cs-typo-preview-color":preview_color
				};
				setPreviewStyle("#"+$(preview).attr("id"),style);
			}

			// Update Preview
			// ------------------------------
			if (preview.length){
				$(preview).css("font-family", currentFamily);
				$('head').append('<link href="http://fonts.googleapis.com/css?family=' + currentFamily +'" class="'+previewId+'" rel="stylesheet" type="text/css" />').load();
				livePreviewRefresh();
			}

			family_select.on('change',function(){
				$('head').find("."+previewId).remove();
				var font = $(this).val();
				$(preview).css("font-family", font);
				$('head').append('<link href="http://fonts.googleapis.com/css?family=' + font +'" class="'+previewId+'" rel="stylesheet" type="text/css" />').load();
				livePreviewRefresh();
			});

			variants_select.on('change',function(){ livePreviewRefresh(); });
			typography_type.on('change',function(){ livePreviewRefresh(); });
			typography_size.on('change',function(){ livePreviewRefresh(); });
			typography_height.on('change',function(){ livePreviewRefresh(); });
			typography_align.on('change',function(){ livePreviewRefresh(); });
			typography_color.on('change',function(){ livePreviewRefresh(); });
			typography_spacing.on('change',function(){ livePreviewRefresh(); });
			typography_transform.on('change',function(){ livePreviewRefresh(); });

			// Toggle Preview BG Style
			// ------------------------------
			$(previewToggle).on("click",function(){
				$(preview).toggleClass("cs-typo-preview-toggle_dark");
			});



			//-----------------------------------------------------------------
			// HELPER FUNCTIONS
			//-----------------------------------------------------------------
			function setPreviewStyle( element, propertyObject ){
				var elem = document.querySelector(element).style;
				for (var property in propertyObject){
					elem.setProperty(property, propertyObject[property]);
				}
			}

			function removeStyle( element, propertyObject){
				var elem = document.querySelector(element).style;
				for (var property in propertyObject){
					elem.removeProperty(propertyObject[property]);
				}
			}
		});
	};
	// ======================================================


	// ======================================================
	// Accordion field by Castor Studio
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_ACCORDION = function() {
		return this.each(function(){
			var self = this;

			$(self).accordion({
				header: '.cs-accordion-title',
				collapsible: true,
				active: false,
				animate: 350,
				heightStyle: 'content',
				icons: {
					'header': 'dashicons dashicons-arrow-down',
					'activeHeader': 'dashicons dashicons-arrow-up'
				},
				beforeActivate: function(event, ui) {
					$(ui.newPanel).CSFRAMEWORK_DEPENDENCY('sub');
				}
			});
		});
	};
	// ======================================================


	// ======================================================
	// Angle field by Castor Studio
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_ANGLE = function() {
		return this.each( function() {
			var dis 		= $( this ),
				input 		= $('.cs-anglepicker input',dis),
				anglePicker = $('.cs-anglepicker > div.cs-anglepicker-wrapper > .anglepicker', dis ),
				data 		= $('.cs-anglepicker',dis).data( 'angleOptions' ),
				distance 	= data.distance || 1,
				delay 		= data.delay || 1,
				snap 		= data.snap || 1,
				min 		= data.min || 0,
				shiftSnap 	= data.shiftSnap || 15,
				clockwise 	= data.clockwise || false,
				value 		= data.value || 0;
			
			$(anglePicker).anglepicker({
				start: function(e, ui) {
			
				},
				change: function(e, ui) {
					$(input).val(ui.value);
				},
				stop: function(e, ui) {
			
				},
				distance: 	distance,
				delay: 		delay,
				snap: 		snap,
				min: 		min,
				shiftSnap: 	shiftSnap,
				clockwise: 	clockwise,
				value: 		value,
			});

			$(input).on('blur',function(){
				var value = $(input).val();
				$(anglePicker).anglepicker('value',value);
			});
		} );
	};
	// ======================================================


	// ======================================================
	// Code Editor field by Castor Studio
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_CODEEDITOR = function() {
		$('.cs-field-code_editor').each(function(index) {
			var $editorContainer = $( this ).find( '.code-editor-container' );
	
			// Get textarea to get/save data
			var $editorTextarea = $editorContainer.prev( 'textarea' );
	
			// Add ID to ace-editor-container
			$editorContainer.attr( 'id', 'aceeditor' + index );
	
			// Get theme and language
			var editorTheme = $editorContainer.data( 'theme' );
			var editorMode = $editorContainer.data( 'mode' );
	
			// Inicialize ACE editor
			var editor = ace.edit( 'aceeditor' + index );
	
			// Set editor settings
			editor.setTheme( 'ace/theme/' + editorTheme );
			editor.getSession().setMode( 'ace/mode/' + editorMode );
	
			editor.setOptions({
				enableBasicAutocompletion: true,
				enableSnippets: true,
				enableLiveAutocompletion: true
			});
	
			// Save data in textarea on ACE editor change
			editor.getSession().on( 'change', function () {
				$editorTextarea.val( editor.getSession().getValue() );
			});
	
			// Get data on load
			editor.getSession().setValue( $editorTextarea.val() );
		});
	};
	// ======================================================


	// ======================================================
	// CSFRAMEWORK Background Field by Castor Studio
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_BACKGROUND = function() {
		return this.each(function() {
			var $this = $(this),
				$add = $this.find('.cs-add'),
				$preview = $this.find('.cs-image-preview'),
				$remove = $this.find('.cs-remove'),
				$input = $this.find('input'),
				$img = $this.find('img'),
				wp_media_frame;
			$add.on('click', function(e) {
				e.preventDefault();
				// Check if the `wp.media.gallery` API exists.
				if (typeof wp === 'undefined' || !wp.media || !wp.media.gallery) {
					return;
				}
				// If the media frame already exists, reopen it.
				if (wp_media_frame) {
					wp_media_frame.open();
					return;
				}
				// Create the media frame.
				wp_media_frame = wp.media({
					// Set the title of the modal.
					title: $add.data('frame-title'),
					// Tell the modal to show only images.
					library: {
						type: 'image'
					},
					// Customize the submit button.
					button: {
						// Set the text of the button.
						text: $add.data('insert-title'),
					}
				});
				// When an image is selected, run a callback.
				wp_media_frame.on('select', function() {
					var attachment = wp_media_frame.state().get('selection').first().attributes;
					var thumbnail = (typeof attachment.sizes.thumbnail !== 'undefined') ? attachment.sizes.thumbnail.url : attachment.url;
					$preview.removeClass('hidden');
					$img.attr('src', thumbnail);
					$input.val(attachment.id).trigger('change');
				});
				// Finally, open the modal.
				wp_media_frame.open();
			});
			// Remove image
			$remove.on('click', function(e) {
				e.preventDefault();
				$input.val('').trigger('change');
				$preview.addClass('hidden');
			});
		});
	};
	// ======================================================















	// ======================================================
	// AutoComplete by Codevz
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_AUTOCOMPLETE = function() {
		return this.each( function() {
			var ac = $( this ),
				time = false,
				query = ac.data( 'query' );
			// Keyup input and send ajax
			$( '> input', ac ).on( 'keyup', function() {
				clearTimeout( time );
				var val = $( this ).val(),
					results = $( '.ajax_items', ac );
				if ( val.length < 2 ) {
					results.slideUp();
					$( '.fa-codevz', ac ).removeClass( 'fa-spinner fa-pulse' );
					return;
				}
				$( '.fa-codevz', ac ).addClass( 'fa-spinner fa-pulse' );
				time = setTimeout( function() {
					$.ajax( {
						type: "GET",
						url: ajaxurl,
						data: $.extend( query, { s: val } ),
						success: function( data ) {
							results.html( data ).slideDown();
							$( '.fa-codevz', ac ).removeClass( 'fa-spinner fa-pulse' );
						},
						error: function( xhr, status, error ) {
							results.html( '<div>' + error + '</div>' ).slideDown();
							$( '.fa-codevz', ac ).removeClass( 'fa-spinner fa-pulse' );
							console.log( xhr, status, error );
						}
					} );
				}, 1000 );
			} );
			// Choose item from ajax results
			$( '.ajax_items', ac ).on( 'click', 'div', function() {
				var id = $( this ).data( 'id' ),
					title = $( this ).html();
				if ( $( '.multiple', ac ).length ) {
					var target = 'append';
					var name = query.elm_name + '[]';
				} else {
					var target = 'html';
					var name = query.elm_name;
				}
				$( '> input', ac ).val( '' );
				$( '.ajax_items' ).slideUp();
				if ( $( '#' + id, ac ).length ) {
					return;
				}
				$( '.selected_items', ac )[ target ]( '<div id="' + id + '"><input name="' + name + '" value="' + id + '" /><span> ' + title + '<i class="fa fa-remove"></i></span></div>' );
			} );
			// Remove selected items
			$( '.selected_items', ac ).on( 'click', '.fa-remove', function() {
				$( this ).parent().parent().detach();
			} );
			$( '.cs-autocomplete, .ajax_items' ).on( 'click', function( e ) {
				e.stopPropagation();
			} );
			$( 'body' ).on( 'click', function( e ) {
				$( '.ajax_items' ).slideUp();
			} );
		} );
	};















	// ======================================================
	// ON WIDGET-ADDED RELOAD FRAMEWORK PLUGINS
	// ------------------------------------------------------
	$.CSFRAMEWORK.WIDGET_RELOAD_PLUGINS = function() {
		$(document).on('widget-added widget-updated', function(event, $widget) {
			$widget.CSFRAMEWORK_RELOAD_PLUGINS();
			$widget.CSFRAMEWORK_DEPENDENCY();
		});
	};
	// ======================================================
	// TOOLTIP HELPER
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_TOOLTIP = function() {
		return this.each(function() {
			var placement = (cs_is_rtl) ? 'right' : 'left';
			var placement = (cs_is_rtl) ? placement : (($(this).data('tooltipPlacement')) ? $(this).data('tooltipPlacement') : 'top' );
			$(this).cstooltip({
				html: true,
				placement: placement,
				container: 'body'
			});
		});
	};
	// ======================================================
	// RELOAD FRAMEWORK PLUGINS
	// ------------------------------------------------------
	$.fn.CSFRAMEWORK_RELOAD_PLUGINS = function() {
		return this.each(function() {
			$('.chosen', this).CSFRAMEWORK_CHOSEN();
			$('.cs-field-image-select', this).CSFRAMEWORK_IMAGE_SELECTOR();
			$('.cs-field-image', this).CSFRAMEWORK_IMAGE_UPLOADER();
			$('.cs-field-gallery', this).CSFRAMEWORK_IMAGE_GALLERY();
			$('.cs-field-sorter', this).CSFRAMEWORK_SORTER();
			$('.cs-field-upload', this).CSFRAMEWORK_UPLOADER();
			$('.cs-field-typography', this).CSFRAMEWORK_TYPOGRAPHY();
			$('.cs-field-color-picker', this).CSFRAMEWORK_COLORPICKER();
			$('.cs-help', this).CSFRAMEWORK_TOOLTIP();

			// ==============================
			// Unofficial Plugins
			// ------------------------------
			$('.cs-autocomplete', this).CSFRAMEWORK_AUTOCOMPLETE(); 			// by Codevz
			$('.cs-has-tooltip', this).CSFRAMEWORK_TOOLTIP();					// To add tooltip functionality on other fields
			$('.cs-field-slider', this).CSFRAMEWORK_SLIDER();					// Slider field by Castor Studio
			$('.cs-field-easing_editor', this).CSFRAMEWORK_EASINGEDITOR();		// Easing Editor field by Castor Studio
			$('.cs-field-typography_advanced', this).CSFRAMEWORK_TYPOGRAPHY_ADVANCED(); 	// Typography Advanced field by Castor Studio
			$('.cs-field-accordion', this).CSFRAMEWORK_ACCORDION();				// Accordion field by Castor Studio
			$('.cs-field-angle', this).CSFRAMEWORK_ANGLE();						// Angle field by Castor Studio
			$('.cs-field-code_editor', this).CSFRAMEWORK_CODEEDITOR();			// Code Editor field by Castor Studio
			$('.cs-field-background', this).CSFRAMEWORK_BACKGROUND();			// Background Editor field by Castor Studio
		});
	};
	// ======================================================
	// JQUERY DOCUMENT READY
	// ------------------------------------------------------
	$(document).ready(function() {
		$('.cs-framework').CSFRAMEWORK_TAB_NAVIGATION();
		$('.cs-reset-confirm, .cs-import-backup').CSFRAMEWORK_CONFIRM();
		$('.cs-content, .wp-customizer, .widget-content, .cs-taxonomy').CSFRAMEWORK_DEPENDENCY();
		$('.cs-field-group').CSFRAMEWORK_GROUP();
		$('.cs-save').CSFRAMEWORK_SAVE();
		$cs_body.CSFRAMEWORK_RELOAD_PLUGINS();
		$.CSFRAMEWORK.ICONS_MANAGER();
		$.CSFRAMEWORK.SHORTCODE_MANAGER();
		$.CSFRAMEWORK.WIDGET_RELOAD_PLUGINS();

		// ==============================
		// Unofficial Plugins
		// ------------------------------
		// $('.cs-field-slider').CSFRAMEWORK_SLIDER();
	});
})(jQuery, window, document);
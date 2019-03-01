(function( $, window, undefined ) {
	'use strict';

	var $document 	= $(document),
		$window 	= $(window),
		$body 		= $(document.body);

	window.ABBUA_SETTINGS = {};
	var ABBUA_SETTINGS = window.ABBUA_SETTINGS;

	ABBUA_SETTINGS.settings = {};
	var settings = ABBUA_SETTINGS.settings;

	ABBUA_SETTINGS.general = {
		fixedHeader: function(){
			if (!settings.fixedHeader){
				settings.fixedHeader = true;
				ABBUA_SETTINGS.sidebar.fixedBrand();
				ABBUA_SETTINGS.navbar.fixedTitle();
			}
		},
		unfixedHeader: function(){
			if (settings.fixedHeader){
				settings.fixedHeader = false;
				ABBUA_SETTINGS.sidebar.unfixedBrand();
				ABBUA_SETTINGS.navbar.unfixedTitle();
			}
		},
	}

	ABBUA_SETTINGS.sidebar = {
		init: function(){
			console.log("[>] ABBUA Admin: Sidebar Init");

			this.submenu();
			this.brand();
			this.position();
		},
		brand: function(){
			var sidebar 		= $('#adminmenuwrap'),
				brand_type		= settings.logo.type,
				brand_wrapper 	= $('<div />',{class: 'sidebar-brand-wrapper'}),
				brand_anchor	= $('<a />',{href: settings.logo.url}),
				brand_brand 	= $('<div />',{class: 'sidebar-brand_brand'}),
				brand_logo 		= $('<div />',{class: 'sidebar-brand_logo'}),
				brand_ico 		= $('<i />',{class: settings.logo.icon}),
				brand_icon 		= $('<div />',{class: 'sidebar-brand_icon'}).append(brand_ico),
				brand_title 	= $('<div />',{class: 'sidebar-brand_text'}).html(settings.logo.text);

			if (brand_type == 'image'){
				brand_brand.append(brand_logo);
			} else if (brand_type == 'text') {
				brand_brand.append(brand_icon).append(brand_title);
			}
			brand_anchor.append(brand_brand);
			brand_wrapper.append(brand_anchor);
			
			brand_wrapper.prependTo(sidebar);
			var brand_animate = setTimeout(function(){
				brand_brand.addClass('sidebar-brand_brand--visible');
			},0);
		},
		submenu: function(){
			var sidebar 	= $('#adminmenu'),
				menuClass 	= 'wp-has-submenu-expanded';

			$('li.wp-has-current-submenu',sidebar).addClass(menuClass);

			$('a.wp-has-submenu',sidebar).on('click',function(e){
				e.preventDefault();

				var parent 		= $(this).parents('li'),
					submenu 	= $('.wp-submenu',parent);

				if (settings.adminmenu.accordion){
					var target 	= parent,
						menus 	= $('li.wp-has-submenu-expanded',sidebar);
					
					$.each(menus,function(){
						var parent 	= $(this),
							submenu = $('.wp-submenu',parent);

						if (target[0] !== parent[0]){
							submenu.slideUp();
							parent.removeClass(menuClass);
						}
					});
				}

				if (!parent.hasClass(menuClass)){
					submenu.slideDown();
					parent.addClass(menuClass);
				} else {
					submenu.slideUp();
					parent.removeClass(menuClass);
				}
			})
		},
		position: function(){
			if (settings.adminmenu.brand_position == 'fixed'){
				this.fixedBrand();
			}
			if (settings.adminmenu.position == 'fixed'){
				this.fixedSidebar();
			}
		},
		fixedBrand: function(){
			$body = $('body');
			if (!settings.fixedBrand){
				settings.fixedBrand = true;
				$body.addClass('cs-abbua-sidebar-brand-fixed');
			}
		},
		unfixedBrand: function(){
			$body = $('body');
			if (settings.fixedBrand){
				settings.fixedBrand = false;
				$body.removeClass('cs-abbua-sidebar-brand-fixed');
			}
		},
		fixedSidebar: function(){
			$body = $('body');
			if (!settings.fixedSidebar){
				settings.fixedSidebar = true;
				$body.removeClass('cs-abbua-sidebar-brand-fixed').addClass('cs-abbua-sidebar-fixed');

				if (settings.adminmenu.scrollbar){
					$('#adminmenu').overlayScrollbars({
						scrollbars: {
							autoHide: 'leave'
						}
					});
				}
			}
		},
		unfixedSidebar: function(){
			$body = $('body');
			if (settings.fixedSidebar){
				settings.fixedSidebar = false;
				$('#adminmenu').overlayScrollbars().destroy();
				$body.removeClass('cs-abbua-sidebar-fixed');
			}
		}
	}


	ABBUA_SETTINGS.menuManager = {
		init: function(){
			console.log("[>] ABBUA Admin: MenuManager Init");

			this.iconPanel();
			this.menuToggle();
			this.menuDisplay();
			this.menuSortable();
			this.submenuSortable();
			this.menuSave();
			this.menuReset();
		},
		iconPanel: function(e) {
			$('.cs-menu-icon-panel_toggle').on('click',function(e) {
				console.log("click en el icono");
				e.stopPropagation();
				var panel 	= $(this).parent().find(".cs-menu-manager_icons-panel");

				panel.data('visibility','shown').show();
			});
			$(document).on('click', ".cs-menu-manager_icons-panel-icon", function() {
				var icon_new 	= $(this).attr("data-class"),
					parent 		= $(this).parent().parent(),
					main 		= parent.find(".cs-menu-icon-panel_toggle"),
					icon_old 	= main.attr("data-class");

				parent.find("input").attr("value", icon_new);
				parent.find("input").val(icon_new);
				
				main.removeClass(icon_old).addClass(icon_new);
				main.attr("data-class", icon_new);
				return false;
			});
	
			$(document).on('click', "body", function() {
				var icon_panel = $(".cs-menu-manager_icons-panel");

				$.each(icon_panel,function(){
					var icon_panel = $(this);

					if (icon_panel.data('visibility') == 'shown') {
						icon_panel.data('visibility','hidden').hide();
					}
				});

			});
		},
		menuToggle: function() {
			$('.cs-mm-action-toggle').click(function(e) {
				var el 			= $(this),
					editPanel 	= el.parents('.cs-menu-manager_item').children(".cs-menu-manager_item-edit-panel");

				if (!el.hasClass("cs-menu-expanded")) {
					el.addClass("cs-menu-expanded");
					editPanel.slideDown();
				} else {
					el.removeClass("cs-menu-expanded");
					editPanel.slideUp();
				}
			});
		},
		menuDisplay: function() {
			$('.cs-mm-action-display').click(function(e) {
				var el 			= $(this),
					icon 		= $('i',el),
					menuItem 	= el.parents('.cs-menu-manager_menu-wrapper').eq(0);

				if (el.hasClass("cs-menu-disabled")) {
					el.removeClass("cs-menu-disabled").addClass("cs-menu-enabled");
					icon.removeClass('fei-eye-off').addClass('fei-eye');
					menuItem.removeClass("disabled").addClass("enabled");
				} else if (el.hasClass("cs-menu-enabled")) {
					el.removeClass("cs-menu-enabled").addClass("cs-menu-disabled");
					icon.removeClass('fei-eye').addClass('fei-eye-off');
					menuItem.removeClass("enabled").addClass("disabled");
				}
			});
		},
		spinner: function(state){
			var spinner = $('.cs-abbua-spinner'),
				btns 	= $('.cs-mm-btn');
			
			if (state == 'show') {
				spinner.addClass('cs-abbua-spinner--visible');
				btns.attr('disabled','disabled');
			} else if (state == 'hide'){
				spinner.removeClass('cs-abbua-spinner--visible');
				btns.removeAttr('disabled');
			}
		},
		menuSave: function() {
			var instance = this;
			$('#cs-admin-menu_save').click(function(e) {
				instance.spinner('show');

				var neworder = "",
					newsuborder = "",
					menurename = "",
					submenurename = "",
					menudisable = "",
					submenudisable = "";

	
				$(".cs-admin-menu-item").each(function() {
					var id 		= $(this).attr("data-id"),
						menuid 	= $(this).attr("data-menu-id");

					neworder += menuid + "|";

					if ($(this).hasClass("disabled")) {
						menudisable += menuid + "|";
					}
				});
	
				$(".cs-admin-submenu-item").each(function() {
					var id 			= $(this).attr("data-id"),
						parentpage 	= $(this).attr("data-parent-page");

					newsuborder += parentpage + ":" + id + "|";

					if ($(this).hasClass("disabled")) {
						submenudisable += parentpage + ":" + id + "|";
					}
				});
	
				$(".cs-admin-menu-rename").each(function() {
					var id = $(this).attr("data-id");
					var sid = $(this).attr("data-menu-id");
					var val = $(this).attr("value");
					var icon = $(this).parent().parent().find(".cs-menu-icon").attr("value");
					menurename += id + ":" + sid + "∞∞[&%&]∞∞" + val + "∞∞[!%#%!]∞∞" + icon + "∞∞[#@!@#]∞∞";
				});
	
				$(".cs-admin-submenu-rename").each(function() {
					var id = $(this).attr("data-id");
					var parent = $(this).attr("data-parent-id");
					var parentpage = $(this).attr("data-parent-page");
					var val = $(this).attr("value");
					submenurename += parentpage + "∞∞[$(@)$]∞∞" + parent + ":" + id + "∞∞[&%&]∞∞" + val + "∞∞[#@!@#]∞∞";
				});
	
				var action = 'abbua_menu_save';
				var data = {
					nonce: 			abbua_admin.nonce, 	// Security nonce
					action: 		action,				// Ajax Action
					// Parameters
					neworder: 		neworder,
					newsuborder: 	newsuborder,
					menurename: 	menurename,
					submenurename: 	submenurename,
					menudisable: 	menudisable,
					submenudisable: submenudisable,
				};
	
				$.ajax({
					url: 	abbua_admin.ajax_url,
					type: 	'post',
					data: 	data,
					success : function( response ) {
						instance.spinner('hide');
						location.reload();
					}
				});
			});
		},
		menuReset: function() {
			var instance = this;
			$('#cs-admin-menu_reset').click(function(e) {
				instance.spinner('show');
				var action = 'abbua_menu_reset';
				var data = {
					nonce: 			abbua_admin.nonce, 	// Security nonce
					action: 		action,				// Ajax Action
				};
	
				$.ajax({
					url: 	abbua_admin.ajax_url,
					type: 	'post',
					data: 	data,
					success : function( response ) {
						instance.spinner('hide');
						location.reload();
					}
				});
			});
		},
		menuSortable: function(){
			if ($.isFunction($.fn.sortable)) {
				$("#cs-menu-manager").sortable({
					handle: ".cs-menu-manager_item-heading",
					placeholder: "ui-state-highlight",
				}).disableSelection();
			}
		},
		submenuSortable: function(){
			if ($.isFunction($.fn.sortable)) {
				$(".cs-menu-manager-submenu-wrapper").sortable({
					placeholder: "ui-state-highlight",
				}).disableSelection();
			}
		},
	}


	ABBUA_SETTINGS.topNavbar = {
		init: function(){
			console.log("[>] ABBUA Admin: TopNavbar Init");

			this.toolbar();
			this.submenu();
			this.position();
		},
		toolbar: function(){
			var navbar 			= $('.wrap > h1'),
				navbar_toolbar 	= $('<div />',{class: 'cs-abbua-header-toolbar'}),
				navbar_title 	= $('<div />',{class: 'cs-abbua-header-title'}),
				toolbar 		= $('#wp-toolbar'),
				site 			= $('#wp-admin-bar-site-name > .ab-item'),
				updates 		= $('#wp-admin-bar-updates > .ab-item'),
				comments 		= $('#wp-admin-bar-comments > .ab-item'),
				newcontent 		= $('#wp-admin-bar-new-content'),
				account 		= $('#wp-admin-bar-my-account');
			
			// WP Localize
			var navbar_site 		= settings.navbar.site,
				navbar_updates 		= settings.navbar.updates,
				navbar_comments 	= settings.navbar.comments,
				navbar_newcontent 	= settings.navbar.addnew,
				navbar_account 		= settings.navbar.profile;
			
			navbar.addClass('cs-abbua-header').wrapInner(navbar_title);

			if (navbar_site){
				var url 	= site.prop('href'),
					anchor 	= $('<a />',{class: 'cs-abbua-header-toolbar-item cs-abbua-header-toolbar-item_site',attr: {
						href: url
					}}),
					icon 	= $('<i />',{class: 'fei fei-globe'}).appendTo(anchor);
				anchor.prependTo(navbar_toolbar);
			}

			if (navbar_updates){
				var url 	= updates.prop('href'),
					title 	= $('.screen-reader-text',updates).text(),
					count 	= $('.ab-label',updates).text(),
					anchor 	= $('<a />',{class: 'cs-abbua-header-toolbar-item cs-abbua-header-toolbar-item_updates',attr: {
						href: url,
						title: title
					}}),
					icon 	= $('<i />',{class: 'fei fei-refresh-cw'}).appendTo(anchor),
					badge 	= $('<span />',{class: 'cs-badge',text: count}).appendTo(anchor);
				anchor.prependTo(navbar_toolbar);
			}

			if (navbar_comments){
				var url 	= comments.prop('href'),
					title 	= $('.screen-reader-text',comments).text(),
					count 	= $('.ab-label',comments).text(),
					anchor 	= $('<a />',{class: 'cs-abbua-header-toolbar-item cs-abbua-header-toolbar-item_comments',attr: {
						href: url,
						title: title
					}}),
					icon 	= $('<i />',{class: 'fei fei-message-circle'}).appendTo(anchor),
					badge 	= $('<span />',{class: 'cs-badge',text: count}).appendTo(anchor);
				anchor.prependTo(navbar_toolbar);
			}

			if (navbar_newcontent){
				var parent 	= $('.ab-item',newcontent).eq(0),
					title 	= $('.ab-label',parent).text(),
					anchor 	= $('<a />',{class: 'cs-abbua-dropdown cs-abbua-header-toolbar-item cs-abbua-header-toolbar-item_new',attr: {
						title: title
					}}),
					icon 	= $('<i />',{class: 'fei fei-plus-circle'}).appendTo(anchor),
					submenu = $('.ab-sub-wrapper > .ab-submenu',newcontent).clone(),
					new_submenu = $('<div />',{class: 'cs-abbua-header-toolbar-item_submenu',html: submenu}).appendTo(anchor);
				anchor.prependTo(navbar_toolbar);
			}

			if (navbar_account){
				var parent 	= $('.ab-sub-wrapper > .ab-submenu',account),
					title 	= $('.display-name',parent).text(),
					anchor 	= $('<a />',{class: 'cs-abbua-dropdown cs-abbua-header-toolbar-item cs-abbua-header-toolbar-item_account',attr: {
						title: title
					}}),
					img 	= $('#wp-admin-bar-user-info .avatar',parent),
					avatar 	= $('<div />',{class: 'cs-abbua-header-toolbar-item_avatar',html: img}).appendTo(anchor),
					submenu = $('.ab-sub-wrapper > .ab-submenu',account).clone(),
					new_submenu = $('<div />',{class: 'cs-abbua-header-toolbar-item_submenu',html: submenu}).appendTo(anchor);
				anchor.prependTo(navbar_toolbar);
			}

			navbar_toolbar.appendTo(navbar);
		},
		submenu: function(){
			var toolbar 	= $('.cs-abbua-header-toolbar'),
				dropdown 	= $('.cs-abbua-dropdown',toolbar);

			dropdown.on('click',function(e){
				var actualDropdown 	= $(this),
					submenu 		= $('.cs-abbua-header-toolbar-item_submenu',actualDropdown);
				actualDropdown.toggleClass('cs-submenu-visible');
			}).on('click','a',function(e){
				e.stopPropagation();
			});
		},
		position: function(){
			if (settings.navbar.position == 'fixed'){
				this.fixedTitle();
			}
		},
		fixedTitle: function(){
			$body = $('body');
			if (!settings.fixedTitle){
				settings.fixedTitle = true;
				$body.addClass('cs-abbua-fixed-title');
			}
		},
		unfixedTitle: function(){
			$body = $('body');
			if (settings.fixedTitle){
				settings.fixedTitle = false;
				$body.removeClass('cs-abbua-fixed-title');
			}
		},
	}
	
	
	ABBUA_SETTINGS.selectBox = function(){
		console.log("[>] ABBUA Admin: Selectbox Init");

		$('.tablenav select, #typeselector').select2({
			minimumResultsForSearch: -1
		});

		// DEPRECATED
		// Revisar en su reemplazo: MutationObserver
		$('body').on('DOMNodeInserted', 'select', function () {
			$(this).select2();
		});

		var selectTimeout = setTimeout(function(){
			$('.attachment-filters').select2({
				minimumResultsForSearch: -1
			});
			// $('#media-attachment-filters').select2({
			// 	minimumResultsForSearch: -1
			// });
			// $('#media-attachment-date-filters').select2({
			// 	minimumResultsForSearch: -1
			// });
		},0);

	}

	ABBUA_SETTINGS.userProfileSettings = function(){
		console.log("[>] ABBUA Admin: UserProfile Init");

		var adminTab 	= $('#cs-tab-user_profile'),
			allSwitch 	= $('.cs-field-switcher',adminTab),
			settings 	= $('.cs-field-checkbox',adminTab);
		
		$('input[type=checkbox]',settings).on('change',function(){
			var checks 	= $('input[type=checkbox]',settings),
				checked = $('input[type=checkbox]:checked',settings);
			
			if (checks.length == checked.length){
				$('input[type=checkbox]',allSwitch).trigger('click');
			} else {
				if ($('input[type=checkbox]:checked',allSwitch).length){
					$('input[type=checkbox]',allSwitch).trigger('click');
				}
			}
		});
	}


	$(document).ready(function() {
		ABBUA_SETTINGS.menuManager.init();
		ABBUA_SETTINGS.sidebar.init();
		ABBUA_SETTINGS.topNavbar.init();
		ABBUA_SETTINGS.selectBox();
		ABBUA_SETTINGS.userProfileSettings();

		var t = setTimeout(function(){
			window.wpResponsive.activate();
		},0);
    });

})( jQuery, window );
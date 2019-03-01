var tld_selectedElement;
var tld_deviceMode = "all";
var tld_imgField;
var tld_modifsMade = false;
var tld_newModifsMade = false;
var tld_initialStyles = new Array();
var tld_styles = new Array();
var tld_firstLoad = true;
var tld_previewUrl;
var tld_elementInitialized = false;
var tld_usedGoogleFonts = new Array();
var tld_nullStyle = 'background-color: rgba(0, 0, 0, 0); border-width: 0px; position: static; overflow: visible;';
var tld_editorCSS;

jQuery(document).ready(function () {
    tld_tdgn_init();
    jQuery(window).resize(tld_updateFrameSize);
});
function tld_initStyles(){
    tld_styles = new Array();
    tld_styles.push({
        device: 'all',
        elements: new Array()
    });
    tld_styles.push({
        device: 'desktop',
        elements: new Array()
    });
    tld_styles.push({
        device: 'desktopTablet',
        elements: new Array()
    });
    tld_styles.push({
        device: 'tablet',
        elements: new Array()
    });
    tld_styles.push({
        device: 'tabletPhone',
        elements: new Array()
    });
    tld_styles.push({
        device: 'phone',
        elements: new Array()
    });
    tld_modifsMade = false;
}
function tld_onOpen(){
    tld_previewUrl = lfb_data.websiteUrl+'/?lfb_action=preview&form='+lfb_currentFormID+'&rand='+Math.random();
    jQuery('#tld_tdgnFrame').attr('src',tld_previewUrl);
}
function tld_tdgn_init() {
    tld_initStyles();
    tld_tdgn_initMenu();
    jQuery('#tld_tdgn_applyModifsTo').change(tld_tdgn_applyModifsChange);
    jQuery('#tld_styleBackgroundType').change(tld_styleBackgroundTypeChange);
    jQuery('#tld_styleBackgroundType_color').change(tld_styleBackgroundType_colorChange);
    jQuery('#tld_styleBackgroundType_imageUrl').keyup(tld_styleBackgroundType_imageChange);
    jQuery('#tld_styleBackgroundType_imageUrl').change(tld_styleBackgroundType_imageChange);
    jQuery('#tld_styleBackgroundType_imageSize').change(tld_styleBackgroundType_imageChange);
    jQuery('#tld_style_borderColor').change(tld_style_borderColorChange);
    jQuery('#tld_style_borderStyle').change(tld_style_borderStyleChange);
    jQuery('#tld_style_widthType').change(tld_style_widthTypeChange);
    jQuery('#tld_style_heightType').change(tld_style_heightTypeChange);
    jQuery('#tld_tdgn_applyScope').change(tld_tdgn_applyScopeChange);
    jQuery('#tld_style_display').change(tld_style_displayChange);
    jQuery('#tld_style_position').change(tld_style_positionChange);
    jQuery('#tld_style_positionLeft').change(tld_style_positionLeftChange);
    jQuery('#tld_style_positionTop').change(tld_style_positionTopChange);
    jQuery('#tld_style_positionBottom').change(tld_style_positionBottomChange);
    jQuery('#tld_style_positionRight').change(tld_style_positionRightChange);
    jQuery('#tld_style_float').change(tld_style_floatChange);
    jQuery('#tld_style_clear').change(tld_style_clearChange);
    jQuery('#tld_style_paddingTypeBottom').change(tld_style_paddingTypeBottomChange);
    jQuery('#tld_style_paddingTypeTop').change(tld_style_paddingTypeTopChange);
    jQuery('#tld_style_paddingTypeLeft').change(tld_style_paddingTypeLeftChange);
    jQuery('#tld_style_paddingTypeRight').change(tld_style_paddingTypeRightChange);
    jQuery('#tld_style_marginTypeBottom').change(tld_style_marginTypeBottomChange);
    jQuery('#tld_style_marginTypeTop').change(tld_style_marginTypeTopChange);
    jQuery('#tld_style_marginTypeLeft').change(tld_style_marginTypeLeftChange);
    jQuery('#tld_style_marginTypeRight').change(tld_style_marginTypeRightChange);
    jQuery('#tld_style_fontType').change(tld_style_fontTypeChange);
    jQuery('#tld_style_fontFamily').change(tld_style_fontFamilyChange);
    jQuery('#tld_style_fontStyle').change(tld_style_fontStyleChange);
    jQuery('#tld_style_fontColor').change(tld_style_fontColorChange);
    jQuery('#tld_style_lineHeightType').change(tld_style_lineHeightTypeChange);
    jQuery('#tld_style_scrollX').change(tld_style_scrollXChange);
    jQuery('#tld_style_scrollY').change(tld_style_scrollYChange);
    jQuery('#tld_style_visibility').change(tld_style_visibilityChange);
    jQuery('#tld_style_shadowType').change(tld_style_shadowTypeChange);   
    jQuery('#tld_style_shadowColor').change(tld_style_shadowChange);   
    jQuery('#tld_style_textShadowColor').change(tld_style_textShadowChange); 
    jQuery('#tld_style_textAlign').change(tld_style_textAlignChange); 
    jQuery('#tld_stateSelect').change(tld_changeStateMode);     
        
    jQuery('#tld_styleBackgroundType_colorAlpha').on('slidechange', tld_styleBackgroundType_colorAlphaChange);
    jQuery('#tld_styleBackgroundType_colorAlpha').on('slide', tld_styleBackgroundType_colorAlphaChange);
    jQuery('#tld_style_borderSize').on('slidechange', tld_style_borderSizeChange);
    jQuery('#tld_style_borderSize').on('slide', tld_style_borderSizeChange);
    jQuery('#tld_style_borderSize').bind('tld_update',tld_style_borderSizeChange);
    jQuery('#tld_style_width').on('slide', tld_style_widthChange);
    jQuery('#tld_style_width').bind('tld_update',tld_style_widthChange);
    jQuery('#tld_style_widthFlex').on('slide', tld_style_widthFlexChange);
    jQuery('#tld_style_widthFlex').bind('tld_update',tld_style_widthFlexChange);
    jQuery('#tld_style_height').on('slide', tld_style_heightChange);
    jQuery('#tld_style_height').bind('tld_update',tld_style_heightChange);
    jQuery('#tld_style_heightFlex').on('slide', tld_style_heightFlexChange);
    jQuery('#tld_style_heightFlex').bind('tld_update',tld_style_heightFlexChange);
    jQuery('#tld_style_left').on('slide', tld_style_leftChange);
    jQuery('#tld_style_left').bind('tld_update',tld_style_leftChange);
    jQuery('#tld_style_right').on('slide', tld_style_rightChange);
    jQuery('#tld_style_right').bind('tld_update',tld_style_rightChange);
    jQuery('#tld_style_bottom').on('slide', tld_style_bottomChange);
    jQuery('#tld_style_bottom').bind('tld_update',tld_style_bottomChange);
    jQuery('#tld_style_top').on('slide', tld_style_topChange);
    jQuery('#tld_style_top').bind('tld_update',tld_style_topChange);
    jQuery('#tld_style_leftFlex').on('slide', tld_style_leftFlexChange);
    jQuery('#tld_style_leftFlex').bind('tld_update',tld_style_leftFlexChange);
    jQuery('#tld_style_rightFlex').on('slide', tld_style_rightFlexChange);
    jQuery('#tld_style_rightFlex').bind('tld_update',tld_style_rightFlexChange);
    jQuery('#tld_style_topFlex').on('slide', tld_style_topFlexChange);
    jQuery('#tld_style_topFlex').bind('tld_update',tld_style_topFlexChange);
    jQuery('#tld_style_bottomFlex').on('slide', tld_style_bottomFlexChange);
    jQuery('#tld_style_bottomFlex').bind('tld_update',tld_style_bottomFlexChange);
    jQuery('#tld_style_marginLeft').on('slide', tld_style_marginLeftChange);
    jQuery('#tld_style_marginLeft').bind('tld_update',tld_style_marginLeftChange);
    jQuery('#tld_style_marginRight').on('slide', tld_style_marginRightChange);
    jQuery('#tld_style_marginRight').bind('tld_update',tld_style_marginRightChange);
    jQuery('#tld_style_marginTop').on('slide', tld_style_marginTopChange);
    jQuery('#tld_style_marginTop').bind('tld_update',tld_style_marginTopChange);
    jQuery('#tld_style_marginBottom').on('slide', tld_style_marginBottomChange);
    jQuery('#tld_style_marginBottom').bind('tld_update',tld_style_marginBottomChange);
    jQuery('#tld_style_marginLeftFlex').on('slide', tld_style_marginLeftFlexChange);
    jQuery('#tld_style_marginLeftFlex').bind('tld_update',tld_style_marginLeftFlexChange);
    jQuery('#tld_style_marginRightFlex').on('slide', tld_style_marginRightFlexChange);
    jQuery('#tld_style_marginRightFlex').bind('tld_update',tld_style_marginRightFlexChange);
    jQuery('#tld_style_marginTopFlex').on('slide', tld_style_marginTopFlexChange);
    jQuery('#tld_style_marginTopFlex').bind('tld_update',tld_style_marginTopFlexChange);
    jQuery('#tld_style_marginBottomFlex').on('slide', tld_style_marginBottomFlexChange);
    jQuery('#tld_style_marginBottomFlex').bind('tld_update',tld_style_marginBottomFlexChange);
    jQuery('#tld_style_paddingLeft').on('slide', tld_style_paddingLeftChange);
    jQuery('#tld_style_paddingLeft').bind('tld_update',tld_style_paddingLeftChange);
    jQuery('#tld_style_paddingRight').on('slide', tld_style_paddingRightChange);
    jQuery('#tld_style_paddingRight').bind('tld_update',tld_style_paddingRightChange);
    jQuery('#tld_style_paddingTop').on('slide', tld_style_paddingTopChange);
    jQuery('#tld_style_paddingTop').bind('tld_update',tld_style_paddingTopChange);
    jQuery('#tld_style_paddingBottom').on('slide', tld_style_paddingBottomChange);
    jQuery('#tld_style_paddingBottom').bind('tld_update',tld_style_paddingBottomChange);
    jQuery('#tld_style_paddingLeftFlex').on('slide', tld_style_paddingLeftFlexChange);
    jQuery('#tld_style_paddingLeftFlex').bind('tld_update',tld_style_paddingLeftFlexChange);
    jQuery('#tld_style_paddingRightFlex').on('slide', tld_style_paddingRightFlexChange);
    jQuery('#tld_style_paddingRightFlex').bind('tld_update',tld_style_paddingRightFlexChange);
    jQuery('#tld_style_paddingTopFlex').on('slide', tld_style_paddingTopFlexChange);
    jQuery('#tld_style_paddingTopFlex').bind('tld_update',tld_style_paddingTopFlexChange);
    jQuery('#tld_style_paddingBottomFlex').on('slide', tld_style_paddingBottomFlexChange);
    jQuery('#tld_style_paddingBottomFlex').bind('tld_update',tld_style_paddingBottomFlexChange);
    jQuery('#tld_style_fontSize').on('slide', tld_style_fontSizeChange);    
    jQuery('#tld_style_fontSize').bind('tld_update',tld_style_fontSizeChange);
    jQuery('#tld_style_lineHeight').on('slide', tld_style_lineHeightChange);    
    jQuery('#tld_style_lineHeight').bind('tld_update',tld_style_lineHeightChange);
    jQuery('#tld_style_lineHeightFlex').on('slide', tld_style_lineHeightFlexChange);    
    jQuery('#tld_style_lineHeightFlex').bind('tld_update',tld_style_lineHeightFlexChange);
    jQuery('#tld_style_opacity').on('slide', tld_style_opacityChange);    
    jQuery('#tld_style_opacity').bind('tld_update',tld_style_opacityChange);
    jQuery('#tld_style_shadowSize').on('slide', tld_style_shadowChange);    
    jQuery('#tld_style_shadowSize').bind('tld_update',tld_style_shadowChange);
    jQuery('#tld_style_shadowX').on('slide', tld_style_shadowChange);    
    jQuery('#tld_style_shadowX').bind('tld_update',tld_style_shadowChange);
    jQuery('#tld_style_shadowY').on('slide', tld_style_shadowChange);    
    jQuery('#tld_style_shadowY').bind('tld_update',tld_style_shadowChange); 
    jQuery('#tld_style_shadowAlpha').on('slide', tld_style_shadowChange);    
    jQuery('#tld_style_shadowAlpha').bind('tld_update',tld_style_shadowChange); 
    jQuery('#tld_style_textShadowX').on('slide', tld_style_textShadowChange);    
    jQuery('#tld_style_textShadowX').bind('tld_update',tld_style_textShadowChange); 
    jQuery('#tld_style_textShadowY').on('slide', tld_style_textShadowChange);    
    jQuery('#tld_style_textShadowY').bind('tld_update',tld_style_textShadowChange); 
    jQuery('#tld_style_textShadowAlpha').on('slide', tld_style_textShadowChange);    
    jQuery('#tld_style_textShadowAlpha').bind('tld_update',tld_style_textShadowChange); 
    
    jQuery('#tld_style_borderRadiusTopLeft').on('slide', tld_style_borderRadiusChange);    
    jQuery('#tld_style_borderRadiusTopLeft').bind('tld_update',tld_style_borderRadiusChange);     
    jQuery('#tld_style_borderRadiusTopRight').on('slide', tld_style_borderRadiusChange);    
    jQuery('#tld_style_borderRadiusTopRight').bind('tld_update',tld_style_borderRadiusChange);     
    jQuery('#tld_style_borderRadiusBottomLeft').on('slide', tld_style_borderRadiusChange);    
    jQuery('#tld_style_borderRadiusBottomLeft').bind('tld_update',tld_style_borderRadiusChange);     
    jQuery('#tld_style_borderRadiusBottomRight').on('slide', tld_style_borderRadiusChange);    
    jQuery('#tld_style_borderRadiusBottomRight').bind('tld_update',tld_style_borderRadiusChange); 
    
    jQuery('#tld_tdgnFrame').on('load', function () {
        if(jQuery('#tld_tdgnFrame').attr('src')!= 'about:blank'){
            if(tld_firstLoad){
                tld_firstLoad = false;
            }    
            jQuery('#lfb_loader').delay(1000).fadeOut();
            tld_updateDomTree();
            tld_unselectElement();
            tld_changeDeviceMode('all');            
        }
        
    });

    jQuery('.wos_imageBtn').click(function () {
        tld_imgField = jQuery(this).prev('input');        
        lfb_formfield = jQuery(this).prev('input');
        tb_show('', 'media-upload.php?TB_iframe=true');
        return false;
    });
    if(jQuery('textarea#tld_editCssField').length>0){
        tld_editorCSS = CodeMirror.fromTextArea(jQuery('textarea#tld_editCssField').get(0),{
             mode:  "css",
             lineNumbers: true
         });   
     }

    jQuery('.tld_colorpick').each(function () {
        var $this = jQuery(this);
        if (jQuery(this).prev('.tld_colorPreview').length == 0) {
            jQuery(this).before('<div class="tld_colorPreview" style="background-color:#' + $this.val().substr(1, 7) + '"></div>');
        }
        jQuery(this).prev('.tld_colorPreview').click(function () {
            jQuery(this).next('.tld_colorpick').trigger('click');
        });
        jQuery(this).colpick({
            color: $this.val().substr(1, 7),
            onChange: function (hsb, hex, rgb, el, bySetColor) {
                var newColor = tld_hex2Rgba('#'+hex,1);
                if(jQuery(el).attr('id') == '#tld_styleBackgroundType_color'){
                    newColor = tld_hex2Rgba('#' + hex, jQuery('#tld_styleBackgroundType_colorAlpha').slider('value'));
                } else if(jQuery(el).attr('id') == '#tld_style_shadowColor'){
                    newColor = tld_hex2Rgba('#' + hex, jQuery('#tld_style_shadowAlpha').slider('value'));                    
                }
                        
                jQuery(el).prev('.tld_colorPreview').css({
                    backgroundColor: '#' + hex
                });
                jQuery(el).val(newColor);
                jQuery(el).trigger('change');
            },
            onSubmit: function (hsb, hex, rgb, el, bySetColor) {
                jQuery('.colpick.colpick_full').fadeOut();
            }
        });
        jQuery(this).change(function(){
            jQuery(this).prev('.tld_colorPreview').css({
               backgroundColor:  jQuery(this).val()
            });
        });
    });
    jQuery('.tld_sliderField').change(function () {
        var value = jQuery(this).val();
        if (value > jQuery(this).prev('.tld_slider').attr('max')) {
            value = jQuery(this).prev('.tld_slider').attr('max');
        }
        if (value < jQuery(this).prev('.tld_slider').attr('min')) {
            value = jQuery(this).prev('.tld_slider').attr('min');
        }
        jQuery(this).prev('.tld_slider').slider('value', parseInt(value));
        var _self = this;
    });
    jQuery('.tld_sliderField').keyup(tld_updateFromSliderField);
    jQuery('.tld_sliderField').mouseup(tld_updateFromSliderField);
    
    tld_fillFontSelect();
    tld_unselectElement();
}
function tld_updateFromSliderField(){
     var value = jQuery(this).val();
        if (value > parseInt(jQuery(this).prev('.tld_slider').attr('max'))) {
            value = parseInt(jQuery(this).prev('.tld_slider').attr('max'));
        }
        if (value < parseInt(jQuery(this).prev('.tld_slider').attr('min'))) {
            value = parseInt(jQuery(this).prev('.tld_slider').attr('min'));
        }
        jQuery(this).prev('.tld_slider').slider('value', parseInt(value));
        jQuery(this).prev('.tld_slider').trigger('tld_update');
}
function tld_fillFontSelect(){
    var fonts = ["ABeeZee","Abel","Abril Fatface","Aclonica","Acme","Actor","Adamina","Advent Pro","Aguafina Script","Akronim","Aladin","Aldrich","Alef","Alegreya","Alegreya SC","Alegreya Sans","Emilys Candy","Engagement","Englebert","Enriqueta","Erica One","Esteban","Euphoria Script","Ewert","Exo","Exo 2","Lato","League Script","Leckerli One","Ledger","Lekton","Lemon","Libre Baskerville","Life Savers","Lilita One","Lily Script One","Limelight","Linden Hill","Lobster","Lobster Two","Londrina Outline","Londrina Shadow","Londrina Sketch","Londrina Solid","Lora","Love Ya Like A Sister","Loved by the King","Lovers Quarrel","Luckiest Guy","Odor Mean Chey","Offside","Old Standard TT","Poly","Pompiere","Pontano Sans","Poppins","Port Lligat Sans","Port Lligat Slab","Pragati Narrow","Prata","Preahvihear","Press Start 2P","Princess Sofia","Prociono","Prosto One","Puritan","Purple Purse","Quando","Quantico","Quattrocento","Quattrocento Sans","Questrial","Quicksand","Quintessential","Qwigley","Racing Sans One","Radley","Rajdhani","Raleway","Raleway Dots","Ramabhadra","Ramaraja","Rambla","Rammetto One","Ranchers","Rancho","Ranga","Rationale","Ravi Prakash","Redressed","Reenie Beanie","Revalia","Rhodium Libre","Ribeye","Ribeye Marrow","Righteous","Risque","Roboto","Roboto Condensed","Roboto Mono","Roboto Slab"];
		
    jQuery.each(fonts,function(){
        jQuery('#tld_style_fontFamily')
         .append(jQuery("<option></option>")
         .attr("value", this)
         .text(this));
    });
    jQuery('#tld_style_fontFamily').selectpicker('refresh');
    
     
       
}
function tld_tdgn_initMenu() {
    var i = 0;
    jQuery('#tld_tdgnPanel .tld_tdgn_section').each(function () {
        jQuery(this).prepend('<a href="javascript:" onclick="tld_tdgn_toggleSection(this,true);" class="tld_tdgn_sectionTitle">' + jQuery(this).attr('data-title') + '</a>');
        jQuery(this).prepend('<a href="javascript:" onclick="tld_tdgn_toggleSection(this,true);" class="tld_tdgn_sectionArrow"><span class="glyphicon glyphicon-chevron-up"></span></a>');
        if (i > 0) {
            tld_tdgn_toggleSection(jQuery(this).find('.tld_tdgn_sectionArrow'),false);
        }
        i++;
    });
    jQuery('#tld_tdgnPanel .panel-heading>.panel-title>a').each(function () {
        jQuery(this).prepend('<span class="glyphicon glyphicon-chevron-down"></span>');
    });
    jQuery('#tld_tdgnPanel .panel-heading>.panel-title>a').click(function () {
        var title = this;
        setTimeout(function(){
            if (!jQuery(title).is('.collapsed')) {
                jQuery(title).find('span.glyphicon').removeClass('glyphicon-chevron-down');
                jQuery(title).find('span.glyphicon').addClass('glyphicon-chevron-up');
            } else {
                jQuery(title).find('span.glyphicon').removeClass('glyphicon-chevron-up');
                jQuery(title).find('span.glyphicon').addClass('glyphicon-chevron-down');
            }
        },300);
            var _self = jQuery(this).closest('.panel.panel-default');
            jQuery('#tld_tdgnPanel .panel.panel-default').each(function () {
                if (!jQuery(this).find('.panel-heading>.panel-title>a').is('.collapsed')) {
                    if (!jQuery(this).is(_self)) {
                        jQuery(this).find('.panel-heading span.glyphicon').removeClass('glyphicon-chevron-up');
                        jQuery(this).find('.panel-heading span.glyphicon').addClass('glyphicon-chevron-down');
                        jQuery(this).find('.panel-collapse').removeClass('in');
                    }
                }
            });
    });
    jQuery('#tld_tdgnContainer .tld_slider').each(function () {
        var min = parseInt(jQuery(this).attr('data-min'));
        if (min == 0) {
            min = 0;
        }
        var max = parseInt(jQuery(this).attr('data-max'));
        if (max == 0) {
            max = 30;
        }
        var step = 1;
        if (jQuery(this).attr('data-step') && jQuery(this).attr('data-step') != "undefined" && jQuery(this).attr('data-step') != '') {
            if (jQuery(this).attr('data-step').indexOf('.') >= 0) {
                step = parseFloat(jQuery(this).attr('data-step'));
            } else {
                step = parseInt(jQuery(this).attr('data-step'));
            }
        }
        var tooltip = jQuery('<div class="tooltip top" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner">' + min + '</div></div>').css({
            position: 'absolute',
            top: -55,
            left: -12,
            opacity: 1
        }).hide();
        jQuery(this).slider({
            min: parseInt(min),
            max: parseInt(max),
            value: parseInt(min),
            step: (step),
            orientation: "horizontal",
            change: function (event, ui) {
                tooltip.find('.tooltip-inner').html(ui.value);
                jQuery(this).next('.tld_sliderField').val(ui.value); 
            },
            slide: function (event, ui) {
                tooltip.find('.tooltip-inner').html(ui.value);
                tooltip.show();
                jQuery(this).next('.tld_sliderField').val(ui.value);
            },
            stop: function (event, ui) {
                tooltip.find('.tooltip-inner').html(ui.value);
                tooltip.hide(); 
                jQuery(this).next('.tld_sliderField').val(ui.value);
                jQuery(this).trigger('slide');
            }

        }).find(".ui-slider-handle").append(tooltip).hover(function () {
            tooltip.show();
        }, function () {
            tooltip.hide();
        });
        if (jQuery(this).next('.tld_sliderField').length > 0) {
            jQuery(this).next('.tld_sliderField').attr('min', min);
            jQuery(this).next('.tld_sliderField').attr('max', max);
            jQuery(this).next('.tld_sliderField').val(min);
        }
    });
     jQuery("#tld_tdgnContainer .tld_selectpicker").removeClass('form-control').selectpicker();
    
     jQuery('#tld_stateSelect').closest('.lfb_bootstrap-select').find('.btn.dropdown-toggle').addClass('btn-info');
     

}
function tld_tdgn_toggleSection(element,realClick) {
    if(realClick && !jQuery(element).closest('.tld_tdgn_section').find('.tld_tdgn_sectionArrow > span').is('.glyphicon-chevron-up')){
        jQuery('#tld_tdgnPanel .tld_tdgn_section:not(.tld_closed)').each(function(){
            if(!jQuery(this).attr('data-title') !=  jQuery(element).closest('.tld_tdgn_section').attr('data-title')){
               tld_tdgn_toggleSection(jQuery(this).find('.tld_tdgn_sectionTitle').get(0),false);
            }
        });
    }    
    if (jQuery(element).closest('.tld_tdgn_section').find('.tld_tdgn_sectionArrow > span').is('.glyphicon-chevron-up')) {
        jQuery(element).closest('.tld_tdgn_section').find('.tld_tdgn_sectionArrow > span').removeClass('glyphicon-chevron-up');
        jQuery(element).closest('.tld_tdgn_section').find('.tld_tdgn_sectionArrow > span').addClass('glyphicon-chevron-down');
        jQuery(element).closest('.tld_tdgn_section').find('.tld_tdgn_sectionBody,.tld_tdgn_sectionBar').slideUp();
        jQuery(element).closest('.tld_tdgn_section').addClass('tld_closed');
    } else {
        jQuery(element).closest('.tld_tdgn_section').find('.tld_tdgn_sectionArrow > span').removeClass('glyphicon-chevron-down');
        jQuery(element).closest('.tld_tdgn_section').find('.tld_tdgn_sectionArrow > span').addClass('glyphicon-chevron-up');
        jQuery(element).closest('.tld_tdgn_section').find('.tld_tdgn_sectionBody,.tld_tdgn_sectionBar').slideDown();
        jQuery(element).closest('.tld_tdgn_section').removeClass('tld_closed');
    }
}
function tld_tdgn_toggleTdgnPanel() {
    if (jQuery('#tld_tdgnPanelToggleBtn > span.glyphicon').is('.glyphicon-chevron-left')) {
        jQuery('#tld_tdgnPanelToggleBtn > span.glyphicon').removeClass('glyphicon-chevron-left');
        jQuery('#tld_tdgnPanelToggleBtn > span.glyphicon').addClass('glyphicon-chevron-right');
        jQuery('#tld_tdgnPanel #tld_tdgnPanelHeaderTitle').fadeOut(50);
        jQuery('#tld_tdgnPanel #tld_tdgnPanelHeader .fa').fadeOut(50);
        jQuery('#tld_tdgnPanel').addClass('tld_collapsed');
        jQuery('#tld_tdgnInspector').addClass('tld_panelFullWidth');
        jQuery('#tld_tdgnFrame').addClass('tld_panelFullWidth');

    } else {
        jQuery('#tld_tdgnPanelToggleBtn > span.glyphicon').removeClass('glyphicon-chevron-right');
        jQuery('#tld_tdgnPanelToggleBtn > span.glyphicon').addClass('glyphicon-chevron-left');
        setTimeout(function () {
            jQuery('#tld_tdgnPanel #tld_tdgnPanelHeaderTitle').fadeIn(150);
        jQuery('#tld_tdgnPanel #tld_tdgnPanelHeader .fa').fadeIn(100);
        }, 200);
        jQuery('#tld_tdgnPanel').removeClass('tld_collapsed');
        jQuery('#tld_tdgnInspector').removeClass('tld_panelFullWidth');
        jQuery('#tld_tdgnFrame').removeClass('tld_panelFullWidth');
        if(jQuery('#tld_tdgnFrame').get(0).contentWindow.tld_isModeSelection()){
            tld_stopSelectionElement();
        }
    }
    tld_updateFrameSize();        
}
function tld_toggleSavePanel() {    
    tld_confirmSaveStyles();
}
function tld_confirmSaveStyles(){
    lfb_showLoader();
     jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'tld_saveCSS',
            styles: tld_formatStylesBeforeSend(),
            formID: lfb_currentFormID,
            gfonts: tld_getGoogleFontsUsed()
        },
        success: function (rep) {
            jQuery('#tld_winSaveDialog').modal('hide');
            jQuery('#tld_winSaveDialog').fadeOut();
            var random = Math.floor((Math.random() * 10000) + 1);
            tld_notification(lfb_data.texts['modifsSaved'],'glyphicon glyphicon-info-sign',false,true);          
            jQuery('#tld_tdgnFrame').attr('src',tld_previewUrl+'&tmp='+random);
            tld_initStyles();
            tld_unselectElement();
        }
    });
}
function tld_confirmSaveStylesBeforeEdit(){
    
    lfb_showLoader();
     jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'tld_saveCSS',
            styles: tld_formatStylesBeforeSend(),
            formID: lfb_currentFormID,
            gfonts: tld_getGoogleFontsUsed()
        },
        success: function (rep) {
            var random = Math.floor((Math.random() * 10000) + 1);
            tld_notification(lfb_data.texts['modifsSaved'],'glyphicon glyphicon-info-sign',false,true);          
            jQuery('#tld_tdgnFrame').attr('src',tld_previewUrl+'&tmp='+random);
            tld_initStyles();
            tld_unselectElement();
            tld_editCSS();
        }
    });
}

function tld_tdgn_toggleInspector() {
    if (jQuery('#tld_tdgnInspectorToggleBtn > span.glyphicon').is('.glyphicon-chevron-down')) {
        jQuery('#tld_tdgnInspectorToggleBtn > span.glyphicon').removeClass('glyphicon-chevron-down');
        jQuery('#tld_tdgnInspectorToggleBtn > span.glyphicon').addClass('glyphicon-chevron-up');
        jQuery('#tld_tdgnFrame').animate({
           paddingBottom: 46 
        },250);
        jQuery('#tld_tdgnInspector').addClass('tld_collapsed');
    } else {
        jQuery('#tld_tdgnInspectorToggleBtn > span.glyphicon').removeClass('glyphicon-chevron-up');
        jQuery('#tld_tdgnInspectorToggleBtn > span.glyphicon').addClass('glyphicon-chevron-down');
        jQuery('#tld_tdgnFrame').animate({
           paddingBottom: 280 
        },250);
        jQuery('#tld_tdgnInspector').removeClass('tld_collapsed');
    }
}

function tld_tdgn_applyModifsChange() {
    if (jQuery('#tld_tdgn_applyModifsTo').val() == 'cssClasses') {
        jQuery('#tld_tdgn_applyToClasses').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_tdgn_applyToClasses').closest('.form-group').slideUp();
    }
}
function tdn_viewTreeItem(item) {
    
    tld_selectElement(jQuery('#tld_tdgnFrame').contents().find('#' + jQuery(item).attr('data-elementid')));
    tld_elementInitialized = false;
    tld_getElementStyles(jQuery('#tld_tdgnFrame').contents().find('#' + jQuery(item).attr('data-elementid'))[0]);
    setTimeout(function () {
        tld_elementInitialized = true;
    },250);
}
function tld_treed(o, element) {

    var openedClass = 'glyphicon-minus-sign';
    var closedClass = 'glyphicon-plus-sign';

    if (typeof o != 'undefined') {
        if (typeof o.openedClass != 'undefined') {
            openedClass = o.openedClass;
        }
        if (typeof o.closedClass != 'undefined') {
            closedClass = o.closedClass;
        }
    }
    var tree = jQuery(element);
    tree.addClass("tld_tree");
    tree.find('li').each(function () {
        var branch = jQuery(this);
        var margL = '';
        if (branch.children('ul').length > 0) {
            margL = '';
        }
        branch.prepend('<a href="javascript:" class="tld_treeEyeLink" onclick="tdn_viewTreeItem(jQuery(this).parent());" onmouseover="jQuery(this).addClass(\'tdn_hover\');"  onmouseout="jQuery(this).removeClass(\'tdn_hover\');"><span class="glyphicon glyphicon-eye-open" style="margin-left:8px;"></span></a>');

    });
    tree.find('li').has("ul").each(function () {
        var branch = jQuery(this); //li with children ul
         branch.prepend("<i class='indicator glyphicon " + closedClass + "'></i>");
        branch.addClass('branch');
        branch.on('click', function (e) {
            if (this == e.target && !jQuery(this).find('.tld_treeEyeLink').is('.tdn_hover')) {
                var icon = jQuery(this).children('i:first');
                icon.toggleClass(openedClass + " " + closedClass);
                jQuery(this).children().children('li').toggle();
                if(jQuery(this).children('ul').is('.tld_open')){
                    jQuery(this).children('ul').removeClass('tld_open');
                } else {
                    jQuery(this).children('ul').addClass('tld_open');
                }
            }
        })
        branch.children().children('li').toggle();
    });
    tree.find('.branch .indicator').each(function () {
        jQuery(this).on('click', function () {
            jQuery(this).closest('li').click();
        });
    });
    tree.find('.branch>a:not(.tld_treeEyeLink)').each(function () {
        jQuery(this).on('click', function (e) {
            jQuery(this).closest('li').click();
            e.preventDefault();
        });
    });
    tree.find('.branch>button').each(function () {
        jQuery(this).on('click', function (e) {
            jQuery(this).closest('li').click();
            e.preventDefault();
        });
    });
    tree.find('.branch>a.tld_treeEyeLink').each(function () {
        jQuery(this).on('click', function (e) {
            
            e.preventDefault();
        });
    });
}

function tld_expandTree(targetID){
    if(jQuery('#tld_tdgnInspector a[data-targetid="'+targetID+'"]').length>0){
        var $li = jQuery('#tld_tdgnInspector li[data-elementid="'+targetID+'"]');
        tld_expandTreeParent($li);
    }
}
function tld_expandTreeParent($element){
    if(!$element.children('ul').is('.tld_open')){
        $element.children('a[data-targetid]').trigger('click');       
    }
    if($element.closest('.tld_tree').length>0){
        tld_expandTreeParent($element.parent().closest('li'));    
    }
}

function tld_getPath(el,modeClasses,start) {
    var path = '';
    if (jQuery(el).length > 0 && typeof (jQuery(el).prop('tagName')) != "undefined") {
        if(jQuery(el).attr('id') != 'estimation_popup'){
        if ((start && modeClasses && (!jQuery(el).attr('id') || jQuery(el).attr('id') != 'wpe_panel')) || !jQuery(el).attr('id') || jQuery(el).attr('id').substr(0, 9) == 'ultimate-' || jQuery(el).attr('id').substr(0, 6) == 'ui-id-' || jQuery(el).attr('id').substr(0, 2) == 'dp') {
            if(!modeClasses){
                var target =  '>' + jQuery(el).prop('tagName') + ':nth-child(' + (jQuery(el).index() + 1) + ')';
                if(jQuery(el).is('.genSlide')){
                    target = '> [data-stepid="'+jQuery(el).attr('data-stepid')+'"]';
                } else if(jQuery(el).is('[data-itemid]')){
                    target = '> [data-itemid="'+jQuery(el).attr('data-itemid')+'"]';                    
                }
                path = target + path;
            } else {
                var classes = "";
                if(jQuery(el).attr('class') && jQuery(el).attr('class').length >0 && (start || !jQuery(el).is('.row'))){
                    classes = jQuery(el).attr('class');
                    if(start){
                        classes = jQuery('#tld_tdgn_applyToClasses').val();
                    }                    
                    var classNameToRemove = '';
                    jQuery.each(classes.split(' '),function(){
                       if(this.indexOf('lfb_itemContainer_') == 0){
                           classNameToRemove = this;
                       } 
                    });        
                    classes = '.'+classes.replace(/  /g, ' ');
                    classes = classes.replace(/ /g, '.');       
                    classes = classes.replace('lfb_activeStep','');
                    classes = classes.replace('col-md-2','');
                    classes = classes.replace('col-md-12','');        
                    classes = classes.replace('tld_edited','');
                    classes = classes.replace('tld_selectedElement','');
                    classes = classes.replace('tld_hasShadow','');
                    if(classNameToRemove != ''){          
                        classes = classes.replace(classNameToRemove,'');                        
                    }            
                    classes = classes.replace('..','.');
                    classes = classes.replace('..','.');
                    classes = classes.replace('..','.');
                    
                    if(classes.substr(classes.length-1,1)=='.'){
                        classes = classes.substr(0,classes.length-1);
                    }
                    if(classes.substr(classes.length-1,1)=='.'){
                        classes = classes.substr(0,classes.length-1);
                    }
                    if(start){
                         path = ' ' + classes + path;                           
                    }else {
                        path = ' ' + jQuery(el).prop('tagName') +classes + path;                           
                    } 
                }             
            }
            if(!jQuery(el).parent().is('#estimation_popup')){
                path = tld_getPath(jQuery(el).parent(),modeClasses,false) + path;
            }
        } else {
            path += '#' + jQuery(el).attr('id');
        }
    }
    }
    return path;
}

function tld_isHovered($element){
    return jQuery(tld_getPath($element,false,true) + ":hover").length > 0;
}
function tld_updateDomTree() {
    var index = 0;
    var domContent = tld_analyzeElement(jQuery('#tld_tdgnFrame').contents().find("#estimation_popup"), 0);
    jQuery('#tld_tdgnInspectorBody').html('<ul>' + domContent + '</ul>');
    tld_treed('undefined', jQuery('#tld_tdgnInspectorBody>ul').get(0));
}
function tld_analyzeElement(element, index) {
    index++;
    var classesString = '';
    var idString = '';
    var tmpID = element.attr('id');
    if (element.attr('id') && element.attr('id').length > 0) {
        idString = '#' + element.attr('id');
    } else {
        element.uniqueId();
        tmpID = element.attr('id');
    }
    element.attr('tldtreeinit',true);
    var rep = '<li data-index="' + (index) + '"  data-elementid="' + tmpID + '">';
    var elementName = tld_getElementName(element, false);
    var htmlContent = jQuery(element).text();
    if(jQuery(element).children().length ==0){
    if(htmlContent.length > 200){
        htmlContent = htmlContent.substr(0,200)+'...';
    }
    } else {
        htmlContent = '';
    }
    var html = '<span class="tld_htmlContent">'+htmlContent+'</span>';
    
    rep += '<a href="javascript:" data-targetid="'+element.attr('id')+'" style="padding-left:' + ((index * 6) + 20) + 'px;">' + elementName +'  '+html+ '</a>';
    if (element.children(':not(script)').length > 0) {
        rep += '<ul>';
        element.children(':not(script)').each(function () {
            rep += tld_analyzeElement(jQuery(this), index);
        });
        rep += '</ul>';
    }
    rep += '</li>';
    return rep;
}
function tld_getElementName(element, shortMode) {
    var elementName = '';
    var classesString = '';
    var idString = '';
    if (element.attr('id') && element.attr('id').length > 0 && element.attr('id').indexOf('ui-id-') != 0 && element.attr('id').indexOf('dp') != 0 && element.attr('id').indexOf('ultimate-')!=0) {
        idString = '#' + element.attr('id');
    }
    idString = '<span style="color: #f1c40f;">' + idString + '</span>';
    if (element.attr('class') && element.attr('class').length > 0) {
        jQuery.each(element.attr('class').split(' '), function () {
            if(this.indexOf('tld_')!=0){
                classesString += '.' + this;
            }
        });
    }
    var maxChar = 50;
    if (shortMode) {
        maxChar = 25;
    }
    if (classesString.length > maxChar) {
        classesString = classesString.substr(0, maxChar) + '...';
    }
    classesString = '<span style="color: #17a68a;">' + classesString + '</span>';
    if(element.prop("tagName") && element.prop("tagName") !== null){
        elementName = element.prop("tagName").toLowerCase() + idString + classesString;        
    } else {        
        elementName = idString + classesString;
    }
    return elementName;
}
function tld_selectElement(element) {    
    if(!jQuery(tld_selectedElement).is(jQuery(element))){
        jQuery('#tld_stateSelect').val('default').selectpicker('refresh');
    }    
    var elementName = tld_getElementName(element, true);
    jQuery('#tld_tdgnFrame').contents().find('.tld_selectedElement').removeClass('tld_selectedElement');
    element.addClass('tld_selectedElement');
    jQuery('#tld_tdgnFrame').get(0).contentWindow.tld_updateSelector();
    
    jQuery('#tld_tdgn_applyScope').val('all');
    jQuery('#tld_tdgn_selectedElement').html(elementName);
    
    jQuery('#tld_tdgnPanelBody > :not(#tld_tdgn_selectElementBtn)').slideDown();
    var classes = jQuery(element).attr('class');
    classes = classes.replace('tld_edited','');
    classes = classes.replace('tld_selectedElement','');
    classes = classes.replace('tld_hasShadow','');
    
    if(classes.substr(classes.length-2,2) == '  '){
        classes = classes.substr(0,classes.length-2);
    }
    jQuery('#tld_tdgn_applyToClasses').val(classes);
    jQuery('#tld_tdgnInspectorBody .tld_tree a.tld_active').removeClass('tld_active');
    jQuery('#tld_tdgnInspectorBody .tld_tree a[data-targetid="'+jQuery(element).attr('id')+'"]').addClass('tld_active');
    tld_expandTree(jQuery(element).attr('id'));
    if(jQuery('#tld_tdgnInspector').is('.tld_collapsed')){
    }
    jQuery('#tld_tdgnFrame').contents().find('#estimation_popup.wpe_bootstraped[data-form="' + lfb_currentFormID + '"]').animate({
        scrollTop: jQuery(element).offset().top-80
    },500);
    setTimeout(function(){        
          jQuery('#tld_tdgnInspectorBody').animate({
             scrollTop: jQuery('#tld_tdgnInspectorBody').scrollTop()+jQuery('#tld_tdgnInspectorBody .tld_tree a[data-targetid="'+jQuery(element).attr('id')+'"]').offset().top-jQuery('#tld_tdgnInspectorBody').offset().top -20
         },200);
    },200);
    tld_selectedElement = element;
    
    
    if(jQuery(tld_selectedElement).is('#estimation_popup') ||
            (jQuery(tld_selectedElement).closest('#lfb_summary').length > 0 || jQuery(tld_selectedElement).is('#lfb_summary'))){
        jQuery('#tdgn-style-margins').closest('.panel.panel-default').slideUp();
        jQuery('#tdgn-style-position').closest('.panel.panel-default').slideUp();
        jQuery('#tdgn-style-size').closest('.panel.panel-default').slideUp();
        jQuery('#tdgn-style-visibility').closest('.panel.panel-default').slideUp();
    } else {
        jQuery('#tdgn-style-margins').closest('.panel.panel-default').slideDown();
        jQuery('#tdgn-style-position').closest('.panel.panel-default').slideDown();
        jQuery('#tdgn-style-size').closest('.panel.panel-default').slideDown();
        jQuery('#tdgn-styapplyMle-visibility').closest('.panel.panel-default').slideDown();        
    }
}
function tld_unselectElement() {
    tld_selectedElement = null;    
    jQuery('#tld_stateSelect').val('default').selectpicker('refresh');
    jQuery('#tld_tdgnPanelBody > :not(#tld_tdgn_selectElementBtn)').slideUp();   
    if(!tld_firstLoad){
        jQuery('#tld_tdgnFrame').get(0).contentWindow.tld_unSelectElement();
    }
}
function tld_confirmStylesElement() {

}
function tld_updateFrameSize(){
    if(jQuery('#tld_tdgnFrame').is('.tld_viewTablet') || jQuery('#tld_tdgnFrame').is('.tld_viewMobile')){
        var frameWidth = 780;
        if(jQuery('#tld_tdgnFrame').is('.tld_viewMobile')){
            jQuery('#tld_tdgnFrame').css({
               width: 380 
            });
            frameWidth = 380;
         } else if(jQuery('#tld_tdgnFrame').is('.tld_viewTablet')){
            jQuery('#tld_tdgnFrame').css({
               width: 780 
            });
            
            
        
         } 
        if (jQuery('#tld_tdgnPanelToggleBtn > span.glyphicon').is('.glyphicon-chevron-left')) {
                jQuery('#tld_tdgnFrame').css({
                   left: jQuery(window).width()/2  -((frameWidth/2)-280/2)
                });
            }else {
                 jQuery('#tld_tdgnFrame').css({
                   left:  jQuery(window).width()/2-((frameWidth/2))
                });
            }

    } else {
         if (jQuery('#tld_tdgnPanelToggleBtn > span.glyphicon').is('.glyphicon-chevron-left')) {
         jQuery('#tld_tdgnFrame').css({
               width: '100%',
               left: 0
            });
        } else {
            jQuery('#tld_tdgnFrame').css({
               width: '100%',
               left: 0
           });
        }
    }
}
function tld_changeDeviceMode(mode) {
    var hasChanged = false;
    if (mode != tld_deviceMode) {
        hasChanged = true;
    }
    var devicesUsed = new Array();
    tld_deviceMode = mode;
    jQuery('#tld_tdgnFrame').removeClass('tld_viewTablet');
    jQuery('#tld_tdgnFrame').removeClass('tld_viewMobile');
    if (mode == 'tabletPhone' || mode == 'tablet') {
        jQuery('#tld_tdgnFrame').addClass('tld_viewTablet');
    } else if (mode == 'phone') {
        jQuery('#tld_tdgnFrame').addClass('tld_viewMobile');
    }
    setTimeout(function(){
    tld_updateFrameSize();
        
    },200);
    jQuery('a[data-devicebtn="all"]').removeClass('tld_active');
    jQuery('a[data-devicebtn="desktop"]').removeClass('tld_active');
    jQuery('a[data-devicebtn="desktopTablet"]').removeClass('tld_active');
    jQuery('a[data-devicebtn="tabletPhone"]').removeClass('tld_active');
    jQuery('a[data-devicebtn="tablet"]').removeClass('tld_active');
    jQuery('a[data-devicebtn="phone"]').removeClass('tld_active');

    if (mode == 'all') {
        jQuery('a[data-devicebtn="all"]').addClass('tld_active');
        devicesUsed.push('all');
    } else if (mode == 'desktop') {
        jQuery('a[data-devicebtn="desktop"]').addClass('tld_active');
        devicesUsed.push('all');
        devicesUsed.push('desktop');
    } else if (mode == 'desktopTablet') {
        jQuery('a[data-devicebtn="desktopTablet"]').addClass('tld_active');
        devicesUsed.push('all');
        devicesUsed.push('tablet');
        devicesUsed.push('desktop');
    } else if (mode == 'tabletPhone') {
        jQuery('a[data-devicebtn="tabletPhone"]').addClass('tld_active');
        devicesUsed.push('all');
        devicesUsed.push('phone');
        devicesUsed.push('tablet');
    } else if (mode == 'tablet') {
        jQuery('a[data-devicebtn="tablet"]').addClass('tld_active');
        devicesUsed.push('all');
        devicesUsed.push('tablet');
    } else if (mode == 'phone') {
        jQuery('a[data-devicebtn="phone"]').addClass('tld_active');
        devicesUsed.push('all');
        devicesUsed.push('phone');
    }
    
    jQuery('#tld_tdgnFrame').contents().find("body").find('*.tld_edited').each(function () {
        if (jQuery(this).is('[data-originalstyle]')) {
            jQuery(this).attr('style', jQuery(this).attr('data-originalstyle'));
        } else {
            jQuery(this).attr('style', '');
        }
    });
    jQuery.each(devicesUsed, function () {
        var dataDevice = tld_getElementsDataByDevice(this);
        if (dataDevice) {
            jQuery.each(dataDevice.elements, function () {
                    if(!jQuery(this.element).is(jQuery('#tld_tdgnFrame').contents().find(this.domSelector))){
                        this.element = jQuery('#tld_tdgnFrame').contents().find(this.domSelector);
                    }
                    if(jQuery('#tld_tdgnFrame').contents().find(this.domSelector).is('[data-originalstyle]')){
                        jQuery('#tld_tdgnFrame').contents().find(this.domSelector).attr('style', jQuery('#tld_tdgnFrame').contents().find(this.domSelector).attr('style')+';'+jQuery('#tld_tdgnFrame').contents().find(this.domSelector).attr('data-originalstyle'));
                    }
                    if(jQuery('#tld_stateSelect').val() == 'hover'){
                        jQuery('#tld_tdgnFrame').contents().find('#estimation_popup '+this.domSelector).attr('style', this.hoverStyle); 
                    } else if(jQuery('#tld_stateSelect').val() == 'focus'){
                        jQuery('#tld_tdgnFrame').contents().find('#estimation_popup '+this.domSelector).attr('style', this.focusStyle); 
                    }else {          
                        jQuery('#tld_tdgnFrame').contents().find('#estimation_popup '+this.domSelector).attr('style', this.style);      
                    }
            });
        }
    });
    if(tld_selectedElement != null && jQuery(tld_selectedElement).length >0){
        setTimeout(function () {
             if(tld_selectedElement != null && jQuery(tld_selectedElement).length >0){
                if(!jQuery('#tld_tdgnFrame').get(0).contentWindow.tld_isModeSelection()){
                    tld_selectElement(jQuery(tld_selectedElement));
                    tld_elementInitialized = false;
                    tld_getElementStyles(tld_selectedElement);
                    setTimeout(function () {
                        tld_elementInitialized = true;
                    },250);
                }
            }
            
        }, 500);
    }
}
function tld_prepareSelectElement() {   
    
    jQuery('#tld_tdgnFrame').contents().find("body").find('*.tld_edited').each(function () {
        if (jQuery(this).is('[data-originalstyle]')) {
            jQuery(this).attr('style', jQuery(this).attr('data-originalstyle'));
        } else {
            jQuery(this).attr('style', '');
        }
    });
    if(jQuery('#tld_tdgnFrame').contents().find("#estimation_popup").find('*:not([data-tldtreeinit="true"])').length >0){
        tld_updateDomTree();
    }    
    
    if (!jQuery('#tld_tdgnInspector').is('.tld_collapsed')) {
        tld_tdgn_toggleInspector();
    }
    if (!jQuery('#tld_tdgnPanel').is('.tld_collapsed')) {
        tld_tdgn_toggleTdgnPanel();
    }
    tld_notification('<div><p>' + lfb_data.texts['selectAnElement'] + '</p><p style="text-align:center;">' +
            '<a href="javascript:" onclick="tld_stopSelectionElement();" class="btn btn-warning"><span class="glyphicon glyphicon-stop"></span>' + lfb_data.texts['stopSelection'] + '</a>' +
            '</p></div>');
    tld_changeDeviceMode(tld_deviceMode);     
    jQuery('#tld_tdgnFrame').get(0).contentWindow.tld_changeSelectionMode(true);
}
function tld_stopSelectionElement() {
    tld_closeNotification();
    jQuery('#tld_tdgnFrame').get(0).contentWindow.tld_changeSelectionMode(false);
    if (jQuery('#tld_tdgnPanel').is('.tld_collapsed')) {
        tld_tdgn_toggleTdgnPanel();
    }
}
function tld_notification(text, iconCls, canBeClose, autoClose) {
    tld_closeNotification();
    if (!iconCls) {
        iconCls = 'glyphicon glyphicon-info-sign';
    }
    var notification = jQuery('<div class="tld_notification"></div>');
    notification.append('<div class="' + iconCls + '"></div>');
    notification.append('<div class="tld_notificationText">' + text + '</div>');
    if (canBeClose) {
        notification.append('<a href="javascript:" onclick="tld_closeNotification();"><span class="glyphicon glyphicon-remove"></span></a>');
    }
    if (autoClose) {
        setTimeout(tld_closeNotification, 7000);
    }
    jQuery('#lfb_bootstraped.lfb_bootstraped #tld_tdgnContainer').append(notification);
    notification.slideDown();
}
function tld_closeNotification() {
    if (jQuery('#lfb_bootstraped.lfb_bootstraped #tld_tdgnContainer >.tld_notification').length > 0) {
        var notification = jQuery('#lfb_bootstraped.lfb_bootstraped  #tld_tdgnContainer>.tld_notification').slideUp();
        setTimeout(function () {
            notification.remove();
        }, 500);
    }
}
function tld_itemSelected(element) {
    if (!jQuery(element).is('tld_edited')) {
        jQuery(element).attr('data-originalstyle', jQuery(element).attr('style'));
        jQuery(element).addClass('tld_edited');
    }
    tld_selectElement(jQuery(element));
    tld_stopSelectionElement();
    
    tld_elementInitialized = false;
    tld_getElementStyles(element);
    jQuery('.tld_tdgn_section:not([data-title="Selection"]):not(.tld_closed) .tld_tdgn_sectionArrow').each(function(){
        tld_tdgn_toggleSection(this,false);
    });
    if(jQuery('.tld_tdgn_section[data-title="Selection"].tld_closed .tld_tdgn_sectionArrow').length>0){
      tld_tdgn_toggleSection(jQuery('.tld_tdgn_section[data-title="Selection"].tld_closed .tld_tdgn_sectionArrow').get(0),false);
    }
    setTimeout(function () {
        tld_elementInitialized = true;
    },250);
}
function tld_initStyleComponent(style) {
    if (style == "background") {

    }
}
function tld_hex2Rgba(hex, alpha) {
    var c;
    if (/^#([A-Fa-f0-9]{3}){1,2}$/.test(hex)) {
        c = hex.substring(1).split('');
        if (c.length == 3) {
            c = [c[0], c[0], c[1], c[1], c[2], c[2]];
        }
        c = '0x' + c.join('');
        return 'rgba(' + [(c >> 16) & 255, (c >> 8) & 255, c & 255].join(',') + ',' + alpha + ')';
    }
}
function tld_rgb2hex(rgb) {
    rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
    return (rgb && rgb.length === 4) ? "#" +
            ("0" + parseInt(rgb[1], 10).toString(16)).slice(-2) +
            ("0" + parseInt(rgb[2], 10).toString(16)).slice(-2) +
            ("0" + parseInt(rgb[3], 10).toString(16)).slice(-2) : '';
}
function tld_getElementStyles(element) {
    var $element = jQuery(element);
    
        var alpha = 1;
        
    jQuery('#tld_tdgn_applyModifsTo').val('onlyThis');
    jQuery('#tld_styleBackgroundType_color').val('#2c3e50');
    jQuery('#tld_style_fontColor').val('#2c3e50');
    if ($element.css('background-image').indexOf('url(') != -1) {
        jQuery('#tld_styleBackgroundType').val('image');
        jQuery('#tld_styleBackgroundType_imageUrl').val($element.css('background-image').replace('url(','').replace(')',''));
    } else if ($element.css('background-color') != '' && $element.css('background-color') != 'rgba(0, 0, 0, 0)') {
        jQuery('#tld_styleBackgroundType_imageUrl').val('');
        var color = tld_rgb2hex($element.css('background-color'));
        if ($element.css('background-color').indexOf('rgba') == 0) {
            alpha = $element.css('background-color').substr($element.css('background-color').lastIndexOf(',') + 1, $element.css('background-color').lastIndexOf(')') - ($element.css('background-color').lastIndexOf(',') + 1));
        }
        if(color == '#'){
            color = '#2c3e50';
        }
        jQuery('#tld_styleBackgroundType_color').val(color);
        jQuery('#tld_styleBackgroundType_color').trigger('change');
        jQuery('#tld_styleBackgroundType').val('color');
    } else {
        jQuery('#tld_styleBackgroundType').val('');
    }
        jQuery('#tld_styleBackgroundType_colorAlpha').slider('value', alpha);
    jQuery('#tld_styleBackgroundType_imageSize').val($element.css('background-size'));
    jQuery('#tld_style_clear').val($element.css('clear'));
    jQuery('#tld_style_float').val($element.css('float'));
        
    var borderSize = 0;
    if (!isNaN(parseInt($element.css('border-width')))) {
        borderSize = parseInt($element.css('border-width'));
    }
    jQuery('#tld_style_borderSize').slider('value', borderSize);
    jQuery('#tld_style_width').slider('value', 0);
    jQuery('#tld_style_widthFlex').slider('value', 0);
    jQuery('#tld_style_height').slider('value', 0);
    jQuery('#tld_style_heightFlex').slider('value', 0);
        
    jQuery('#tld_style_left').slider('value', 0);
    jQuery('#tld_style_leftFlex').slider('value', 0);
    jQuery('#tld_style_right').slider('value', 0);
    jQuery('#tld_style_rightFlex').slider('value', 0);
    jQuery('#tld_style_top').slider('value', 0);
    jQuery('#tld_style_topFlex').slider('value', 0);
    jQuery('#tld_style_bottom').slider('value', 0);
    jQuery('#tld_style_bottomFlex').slider('value', 0);
    jQuery('#tld_style_marginLeft').slider('value', 0);
    jQuery('#tld_style_marginLeftFlex').slider('value', 0);
    jQuery('#tld_style_marginRight').slider('value', 0);
    jQuery('#tld_style_marginRightFlex').slider('value', 0);
    jQuery('#tld_style_marginTop').slider('value', 0);
    jQuery('#tld_style_marginTopFlex').slider('value', 0);
    jQuery('#tld_style_marginBottom').slider('value', 0);
    jQuery('#tld_style_marginBottomFlex').slider('value', 0);
    jQuery('#tld_style_borderRadiusTopLeft').slider('value', 0);
    jQuery('#tld_style_borderRadiusTopRight').slider('value', 0);
    jQuery('#tld_style_borderRadiusBottomLeft').slider('value', 0);
    jQuery('#tld_style_borderRadiusBottomLeft').slider('value', 0);
    
    if ($element.css('width').indexOf('%') > 0) {
        jQuery('#tld_style_widthType').val('flexible');
        jQuery('#tld_style_widthFlex').slider('value', parseInt($element.css('width')));
    }else if ($element.width() != $element.parent().width()) {
        jQuery('#tld_style_widthType').val('fixed');
        jQuery('#tld_style_width').slider('value', $element.width());
    } else {
        jQuery('#tld_style_widthType').val('auto');
    }
    if ($element.css('height').indexOf('%') > 0) {
        jQuery('#tld_style_heightType').val('flexible');
        jQuery('#tld_style_heightFlex').slider('value', parseInt($element.css('height')));
    } else if ($element.height() != $element.parent().height()) {
        jQuery('#tld_style_heightType').val('fixed');
        jQuery('#tld_style_height').slider('value', $element.height());
    } else {
        jQuery('#tld_style_heightType').val('auto');
    }
    jQuery('#tld_style_borderStyle').val($element.css('border-style'));
    jQuery('#tld_style_borderColor').val($element.css('border-color'));
    var display = $element.css('display');
    if (jQuery('#tld_style_display > option[value="' + display + '"]').length == 0) {
        display = 'inherit';
    }
    jQuery('#tld_style_display').val(display);
    jQuery('#tld_style_position').val($element.css('position'));
    if ($element.css('left') != 'auto') {
        jQuery('#tld_style_positionLeft').val('fixed');
    } else {
        jQuery('#tld_style_positionLeft').val('auto');
    }
    if ($element.css('right') != 'auto') {
        jQuery('#tld_style_positionRight').val('fixed');
    } else {
        jQuery('#tld_style_positionRight').val('auto');
    }
    if ($element.css('top') != 'auto') {
        jQuery('#tld_style_positionTop').val('fixed');
    } else {
        jQuery('#tld_style_positionTop').val('auto');
    }
    if ($element.css('bottom') != 'auto') {
        jQuery('#tld_style_positionBottom').val('fixed');
    } else {
        jQuery('#tld_style_positionBottom').val('auto');
    }
    var value = parseInt($element.css('left'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('left').indexOf('%') > 0) {
        jQuery('#tld_style_leftFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_left').slider('value', value);
    }

    value = parseInt($element.css('right'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('right').indexOf('%') > 0) {
        jQuery('#tld_style_rightFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_right').slider('value', value);
    }

    value = parseInt($element.css('top'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('top').indexOf('%') > 0) {
        jQuery('#tld_style_topFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_top').slider('value', value);
    }
    value = parseInt($element.css('bottom'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('bottom').indexOf('%') > 0) {
        jQuery('#tld_style_bottomFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_bottom').slider('value', value);
    }
        
    
    if ($element.css('margin-left').indexOf('px') > 0) {
        jQuery('#tld_style_marginTypeLeft').val('fixed');
    } else if ($element.css('margin-left').indexOf('%') > 0) {
        jQuery('#tld_style_marginTypeLeft').val('flexible');
    } else {
        jQuery('#tld_style_marginTypeLeft').val('auto');
    }
      if ($element.css('margin-right').indexOf('px') > 0) {
        jQuery('#tld_style_marginTypeRight').val('fixed');
    } else if ($element.css('margin-right').indexOf('%') > 0) {
        jQuery('#tld_style_marginTypeRight').val('flexible');
    } else {
        jQuery('#tld_style_marginTypeRight').val('auto');
    }
      if ($element.css('margin-top').indexOf('px') > 0) {
        jQuery('#tld_style_marginTypeTop').val('fixed');
    } else if ($element.css('margin-top').indexOf('%') > 0) {
        jQuery('#tld_style_marginTypeTop').val('flexible');
    } else {
        jQuery('#tld_style_marginTypeTop').val('auto');
    }
      if ($element.css('margin-bottom').indexOf('px') > 0) {
        jQuery('#tld_style_marginTypeBottom').val('fixed');
    } else if ($element.css('margin-bottom').indexOf('%') > 0) {
        jQuery('#tld_style_marginTypeBottom').val('flexible');
    } else {
        jQuery('#tld_style_marginTypeBottom').val('auto');
    }
    
    if ($element.css('padding-left').indexOf('px') > 0) {
        jQuery('#tld_style_paddingTypeLeft').val('fixed');
    } else if ($element.css('padding-left').indexOf('%') > 0) {
        jQuery('#tld_style_paddingTypeLeft').val('flexible');
    } else {
        jQuery('#tld_style_paddingTypeLeft').val('auto');
    }
      if ($element.css('padding-right').indexOf('px') > 0) {
        jQuery('#tld_style_paddingTypeRight').val('fixed');
    } else if ($element.css('padding-right').indexOf('%') > 0) {
        jQuery('#tld_style_paddingTypeRight').val('flexible');
    } else {
        jQuery('#tld_style_paddingTypeRight').val('auto');
    }
      if ($element.css('padding-top').indexOf('px') > 0) {
        jQuery('#tld_style_paddingTypeTop').val('fixed');
    } else if ($element.css('padding-top').indexOf('%') > 0) {
        jQuery('#tld_style_paddingTypeTop').val('flexible');
    } else {
        jQuery('#tld_style_paddingTypeTop').val('auto');
    }
      if ($element.css('padding-bottom').indexOf('px') > 0) {
        jQuery('#tld_style_paddingTypeBottom').val('fixed');
    } else if ($element.css('padding-bottom').indexOf('%') > 0) {
        jQuery('#tld_style_paddingTypeBottom').val('flexible');
    } else {
        jQuery('#tld_style_paddingTypeBottom').val('auto');
    }
    
    value = parseInt($element.css('padding-left'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('padding-left').indexOf('%') > 0) {
        jQuery('#tld_style_paddingLeftFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_paddingLeft').slider('value', value);
    }
    value = parseInt($element.css('padding-right'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('padding-right').indexOf('%') > 0) {
        jQuery('#tld_style_paddingRightFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_paddingRight').slider('value', value);
    }
    value = parseInt($element.css('padding-top'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('padding-top').indexOf('%') > 0) {
        jQuery('#tld_style_paddingTopFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_paddingTop').slider('value', value);
    }
    value = parseInt($element.css('padding-bottom'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('padding-bottom').indexOf('%') > 0) {
        jQuery('#tld_style_paddingBottomFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_paddingBottom').slider('value', value);
    }
    
    
    value = parseInt($element.css('margin-left'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('margin-left').indexOf('%') > 0) {
        jQuery('#tld_style_marginLeftFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_marginLeft').slider('value', value);
    }
    
    
    value = parseInt($element.css('margin-right'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('margin-right').indexOf('%') > 0) {
        jQuery('#tld_style_marginRightFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_marginRight').slider('value', value);
    }
    
    
    value = parseInt($element.css('margin-top'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('margin-top').indexOf('%') > 0) {
        jQuery('#tld_style_marginTopFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_marginTop').slider('value', value);
    }
    
    
    value = parseInt($element.css('margin-bottom'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('margin-bottom').indexOf('%') > 0) {
        jQuery('#tld_style_marginBottomFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_marginBottom').slider('value', value);
    }
    jQuery('#tld_style_fontFamily').val($element.css('font-family'));
    jQuery('#tld_style_fontColor').val($element.css('color'));
    jQuery('#tld_style_fontSize').slider('value',parseInt($element.css('font-size')));
    
    value = parseInt($element.css('line-height'));
    if (isNaN(value)) {
        value = 0;
    }
    if ($element.css('line-height').indexOf('%') > 0) {
        jQuery('#tld_style_lineHeightType').val('flexible');
        jQuery('#tld_style_lineHeightFlex').slider('value', value + '%');
    } else {
        jQuery('#tld_style_lineHeightType').val('fixed');
        jQuery('#tld_style_lineHeight').slider('value', value);
    }
    jQuery('#tld_style_lineHeight').slider('value',parseInt($element.css('line-height')));
    
    jQuery("#tld_style_fontStyle option[value='italic']").prop("selected", false);
    jQuery("#tld_style_fontStyle option[value='bold']").prop("selected", false);
    jQuery("#tld_style_fontStyle option[value='underline']").prop("selected", false);
    var style = 'none';
    if($element.css('font-style') == 'italic'){
        jQuery("#tld_style_fontStyle option[value='italic']").prop("selected", true);
    }
    if($element.css('font-weight') == 'bold'){
        jQuery("#tld_style_fontStyle option[value='bold']").prop("selected", true);
    }
    if($element.css('text-decoration') == 'underline'){
        jQuery("#tld_style_fontStyle option[value='underline']").prop("selected", true);
    }
    var shadow = "outside";
    var shadowX = 0;
    var shadowY = 0;
    var shadowSize = 0;
    var shadowAlpha = 1;
    var shadowColor = 'rgb(255,255,255)';
    var posIndex = 0;
    
    var shadowStyle = $element.css('box-shadow');
    if($element.css('box-shadow') == 'none' || !$element.is('.tld_hasShadow')){
        shadow = 'none';
    } else {
        if($element.css('box-shadow').indexOf('inset')>0){
            shadow = 'inside';
        } 
        shadowColor = shadowStyle.substr(0,shadowStyle.indexOf(')')+1);
        if (shadowColor.indexOf('rgba') == 0) {
            shadowAlpha = shadowColor.substr(shadowColor.lastIndexOf(',') + 1, shadowColor.lastIndexOf(')') - (shadowColor.lastIndexOf(',') + 1));
        }
        shadowX = shadowStyle.substr(shadowStyle.indexOf(')')+1,shadowStyle.indexOf('px')-shadowStyle.indexOf(')')-1);
        posIndex = shadowStyle.indexOf('px')+2;
        shadowY = shadowStyle.substr(posIndex,shadowStyle.indexOf('px',posIndex)-posIndex);
        posIndex = shadowStyle.indexOf('px',posIndex)+2;
        shadowSize = shadowStyle.substr(posIndex,shadowStyle.indexOf('px',posIndex)-posIndex);
    }
    
    jQuery('#tld_style_shadowType').val(shadow);   
    jQuery('#tld_style_shadowColor').val(shadowColor);  
    jQuery('#tld_style_shadowAlpha').slider('value',shadowAlpha);  
    
    
    //tld_style_textShadowX
    shadowColor = 'rgb(0,0,0)';
    shadowStyle = $element.css('text-shadow');
    if(shadowStyle == 'none'){
        jQuery('#tld_style_textShadowColor').val('rgba(0,0,0,0)');         
    } else {
        shadowColor = shadowStyle.substr(0,shadowStyle.indexOf(')')+1);
        shadowX = shadowStyle.substr(shadowStyle.indexOf(')')+1,shadowStyle.indexOf('px')-shadowStyle.indexOf(')')-1);
        posIndex = shadowStyle.indexOf('px')+2;
        shadowY = shadowStyle.substr(posIndex,shadowStyle.indexOf('px',posIndex)-posIndex);
    }
    shadowAlpha =0;
    if (shadowColor.indexOf('rgba') == 0) {
        shadowAlpha = shadowColor.substr(shadowColor.lastIndexOf(',') + 1, shadowColor.lastIndexOf(')') - (shadowColor.lastIndexOf(',') + 1));
    }
    jQuery('#tld_style_textShadowAlpha').slider('value',shadowAlpha); 
    jQuery('#tld_style_textShadowColor').val(shadowColor);  
        
    jQuery('#tld_style_scrollX').val($element.css('overflow-x'));    
    jQuery('#tld_style_scrollY').val($element.css('overflow-y'));   
    jQuery('#tld_style_visibility').val($element.css('visibility'));  
    if($element.css('text-align') == 'left' || $element.css('text-align') == 'right' || $element.css('text-align') == 'justify'){
        jQuery('#tld_style_textAlign').val($element.css('text-align'));           
    }  else {
        jQuery('#tld_style_textAlign').val('auto'); 
    }
    
    jQuery('#tld_style_opacity').slider('value', $element.css('opacity'));
    jQuery('#tld_style_shadowX').slider('value', shadowX);
    jQuery('#tld_style_shadowY').slider('value', shadowY);
    jQuery('#tld_style_shadowSize').slider('value', shadowSize);
    
    jQuery('#tld_style_textShadowX').slider('value', shadowX);
    jQuery('#tld_style_textShadowY').slider('value', shadowY);
    
    jQuery('#tld_style_borderRadiusTopLeft').slider('value', parseInt($element.css('border-top-left-radius')));
    jQuery('#tld_style_borderRadiusTopRight').slider('value', parseInt($element.css('border-top-right-radius')));
    jQuery('#tld_style_borderRadiusBottomLeft').slider('value', parseInt($element.css('border-bottom-left-radius')));
    jQuery('#tld_style_borderRadiusBottomRight').slider('value', parseInt($element.css('border-bottom-right-radius')));
    

    tld_style_widthTypeChange();
    tld_style_heightTypeChange();
    jQuery('#tld_tdgnContainer .tld_slider').trigger('change');    
    jQuery('#tld_tdgnContainer').find('input:not(.tld_sliderField),select').trigger('change');
    jQuery('#tld_tdgnContainer').find('select.tld_selectpicker').selectpicker('refresh');
    
}
function tld_tdgn_applyScopeChange() {
    if (jQuery('#tld_tdgn_applyScope').val() == 'container') {
        jQuery('#tld_tdgn_scopeContainerClass').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_tdgn_scopeContainerClass').closest('.form-group').slideUp();
    }
}
function tld_styleBackgroundTypeChange() {
    var elementsToClose = new Array();
    var selectedElement = '';
    if (jQuery('#tld_styleBackgroundType').val() == 'color') {
        jQuery('#tld_styleBackgroundType_imageUrl').val('');
        if(tld_elementInitialized){
            tld_selectedElement.css({
                backgroundImage: 'none'
            });
        }
        jQuery('#tld_styleBackgroundType_imageToggle').slideUp();
        jQuery('#tld_styleBackgroundType_colorToggle').slideDown();
    } else if (jQuery('#tld_styleBackgroundType').val() == 'image') {
        jQuery('#tld_styleBackgroundType_colorToggle').slideUp();
        jQuery('#tld_styleBackgroundType_imageToggle').slideDown();
         tld_selectedElement.css({
                backgroundColor: 'transparent'
            });
    } else {
        jQuery('#tld_styleBackgroundType_imageToggle').slideUp();
        jQuery('#tld_styleBackgroundType_colorToggle').slideUp();
        if(tld_elementInitialized){
            tld_selectedElement.css({
                backgroundImage: 'none',
                backgroundColor: 'transparent'
            });
        }
    }
}
function tld_styleBackgroundType_colorChange() {
    if (jQuery('#tld_styleBackgroundType').val() == 'color') {
         var newColor =jQuery('#tld_styleBackgroundType_color').val();
            if (jQuery('#tld_styleBackgroundType_color').val().indexOf('rgb')>-1) {
               newColor = tld_rgb2hex(jQuery('#tld_styleBackgroundType_color').val());
         }
         if (newColor == '') {
            newColor = 'transparent';
        }else {
         newColor = tld_hex2Rgba(newColor, jQuery('#tld_styleBackgroundType_colorAlpha').slider('value'));
     }
        if(tld_elementInitialized){
            tld_selectedElement.css({
                backgroundColor: newColor
            });
        }
    }
}
function tld_styleBackgroundType_colorAlphaChange() {
    if (jQuery('#tld_styleBackgroundType').val() == 'color') {
        if (jQuery('#tld_styleBackgroundType_color').val() != ''){
            var newColor =jQuery('#tld_styleBackgroundType_color').val();
            if (jQuery('#tld_styleBackgroundType_color').val().indexOf('rgb')>-1) {
               newColor = tld_rgb2hex(jQuery('#tld_styleBackgroundType_color').val());
         }
               newColor = tld_hex2Rgba(newColor, jQuery('#tld_styleBackgroundType_colorAlpha').slider('value'));
               jQuery('#tld_styleBackgroundType_color').val(newColor);
               jQuery('#tld_styleBackgroundType_color').trigger('change');
           
        }
    }
}
function tld_styleBackgroundType_imageChange() {
    
    if (jQuery('#tld_styleBackgroundType').val() == 'image') {
        if(jQuery('#tld_styleBackgroundType_imageUrl').val() != 'none'){
            var image = 'url(' + jQuery('#tld_styleBackgroundType_imageUrl').val() + ')';
            var size = jQuery('#tld_styleBackgroundType_imageSize').val();
            if (jQuery('#tld_styleBackgroundType_imageUrl').val()  == "" ) {
                image = 'none';
            }
            if(tld_elementInitialized){
                tld_selectedElement.css({
                    backgroundImage: image,
                    backgroundSize: size
                });
            }
        }
    }
}
function tld_style_borderColorChange() {
    var color = jQuery('#tld_style_borderColor').val();
    if (color == '') {
        color = 'transparent';
    }
        if(tld_elementInitialized){
    tld_selectedElement.css({
        borderColor: color
    });
        }
}
function tld_style_borderStyleChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        borderStyle: jQuery('#tld_style_borderStyle').val()
    });
        }
}
function tld_style_borderSizeChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        borderWidth: jQuery('#tld_style_borderSize').slider('value')
    });
        }
}
function tld_style_widthChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        width: jQuery('#tld_style_width').slider('value')
    });
        }
}
function tld_style_widthFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        width: jQuery('#tld_style_widthFlex').slider('value') + '%'
    });
        }
}
function tld_style_heightChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        height: jQuery('#tld_style_height').slider('value')
    });
        }
}
function tld_style_heightFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        height: jQuery('#tld_style_heightFlex').slider('value') + '%'
    });
        }
}
function tld_style_widthTypeChange() {
    var type = jQuery('#tld_style_widthType').val();
    if (type == 'fixed') {
        jQuery('#tld_style_width').closest('.form-group').slideDown();
        jQuery('#tld_style_widthFlex').closest('.form-group').slideUp();
    } else if (type == 'flexible') {
        jQuery('#tld_style_widthFlex').closest('.form-group').slideDown();
        jQuery('#tld_style_width').closest('.form-group').slideUp();
    } else {
        jQuery('#tld_style_width').closest('.form-group').slideUp();
        jQuery('#tld_style_widthFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
        tld_selectedElement.css({
           width: 'auto' 
        });
    }
    }
}
function tld_style_heightTypeChange() {
    var type = jQuery('#tld_style_heightType').val();
    if (type == 'fixed') {
        jQuery('#tld_style_height').closest('.form-group').slideDown();
        jQuery('#tld_style_heightFlex').closest('.form-group').slideUp();
    } else if (type == 'flexible') {
        jQuery('#tld_style_heightFlex').closest('.form-group').slideDown();
        jQuery('#tld_style_height').closest('.form-group').slideUp();
    } else {
        jQuery('#tld_style_height').closest('.form-group').slideUp();
        jQuery('#tld_style_heightFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
        tld_selectedElement.css({
           height: 'auto' 
        });
    }
    }
}
function tld_style_displayChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        display: jQuery('#tld_style_display').val()
    });
        }
}
function tld_style_positionChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        position: jQuery('#tld_style_position').val()
    });
        }
    if (jQuery('#tld_style_position').val() == 'static') {
        jQuery('#tld_style_positionLeft').val('auto');
        jQuery('#tld_style_positionLeft').closest('.form-group').slideUp();
        jQuery('#tld_style_positionLeft').closest('.form-group').slideUp();
        jQuery('#tld_style_left').closest('.form-group').slideUp();
        jQuery('#tld_style_leftFlex').closest('.form-group').slideUp();
        jQuery('#tld_style_positionRight').closest('.form-group').slideUp();
        jQuery('#tld_style_positionRight').val('auto');
        jQuery('#tld_style_right').closest('.form-group').slideUp();
        jQuery('#tld_style_rightFlex').closest('.form-group').slideUp();
        jQuery('#tld_style_positionTop').closest('.form-group').slideUp();
        jQuery('#tld_style_positionTop').val('auto');
        jQuery('#tld_style_top').closest('.form-group').slideUp();
        jQuery('#tld_style_topFlex').closest('.form-group').slideUp();
        jQuery('#tld_style_positionBottom').closest('.form-group').slideUp();
        jQuery('#tld_style_positionBottom').val('auto');
        jQuery('#tld_style_bottom').closest('.form-group').slideUp();
        jQuery('#tld_style_bottomFlex').closest('.form-group').slideUp();
    } else {
        jQuery('#tld_style_positionLeft').closest('.form-group').slideDown();
        jQuery('#tld_style_positionRight').closest('.form-group').slideDown();
        jQuery('#tld_style_positionTop').closest('.form-group').slideDown();
        jQuery('#tld_style_positionBottom').closest('.form-group').slideDown();
    }
    jQuery('#tld_style_positionBottom').trigger('change');
    jQuery('#tld_style_positionTop').trigger('change');
    jQuery('#tld_style_positionLeft').trigger('change');
    jQuery('#tld_style_positionRight').trigger('change');
}
function tld_style_positionLeftChange() {
    if (jQuery('#tld_style_positionLeft').val() == 'fixed') {
        jQuery('#tld_style_left').closest('.form-group').slideDown();
        jQuery('#tld_style_leftFlex').closest('.form-group').slideUp();
    } else if (jQuery('#tld_style_positionLeft').val() == 'flexible') {
        jQuery('#tld_style_left').closest('.form-group').slideUp();
        jQuery('#tld_style_leftFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_left').closest('.form-group').slideUp();
        jQuery('#tld_style_leftFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
         tld_selectedElement.css({
           left: 'auto' 
        });
    }
    }
}
function tld_style_positionRightChange() {
    if (jQuery('#tld_style_positionRight').val() == 'fixed') {
        jQuery('#tld_style_right').closest('.form-group').slideDown();
        jQuery('#tld_style_rightFlex').closest('.form-group').slideUp();
    } else if (jQuery('#tld_style_positionRight').val() == 'flexible') {
        jQuery('#tld_style_right').closest('.form-group').slideUp();
        jQuery('#tld_style_rightFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_right').closest('.form-group').slideUp();
        jQuery('#tld_style_rightFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
         tld_selectedElement.css({
           right: 'auto' 
        });
    }
    }
}
function tld_style_floatChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        float: jQuery('#tld_style_float').val()
    });
        }
}
function tld_style_clearChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        clear: jQuery('#tld_style_clear').val()
    });
        }
}

function tld_style_marginTypeLeftChange() {
    if (jQuery('#tld_style_marginTypeLeft').val() == 'fixed') {
        jQuery('#tld_style_marginLeft').closest('.form-group').slideDown();
        jQuery('#tld_style_marginLeftFlex').closest('.form-group').slideUp();
    } else if (jQuery('#tld_style_marginTypeLeft').val() == 'flexible') {
        jQuery('#tld_style_marginLeft').closest('.form-group').slideUp();
        jQuery('#tld_style_marginLeftFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_marginLeft').closest('.form-group').slideUp();
        jQuery('#tld_style_marginLeftFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
         tld_selectedElement.css({
           marginLeft: 'auto' 
        });
    }
    }
}
function tld_style_marginTypeRightChange() {
    if (jQuery('#tld_style_marginTypeRight').val() == 'fixed') {
        jQuery('#tld_style_marginRight').closest('.form-group').slideDown();
        jQuery('#tld_style_marginRightFlex').closest('.form-group').slideUp();
    } else if (jQuery('#tld_style_marginTypeRight').val() == 'flexible') {
        jQuery('#tld_style_marginRight').closest('.form-group').slideUp();
        jQuery('#tld_style_marginRightFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_marginRight').closest('.form-group').slideUp();
        jQuery('#tld_style_marginRightFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
         tld_selectedElement.css({
           marginRight: 'auto' 
        });
    }
    }
}
function tld_style_marginTypeTopChange() {
    if (jQuery('#tld_style_marginTypeTop').val() == 'fixed') {
        jQuery('#tld_style_marginTop').closest('.form-group').slideDown();
        jQuery('#tld_style_marginTopFlex').closest('.form-group').slideUp();
    } else if (jQuery('#tld_style_marginTypeTop').val() == 'flexible') {
        jQuery('#tld_style_marginTop').closest('.form-group').slideUp();
        jQuery('#tld_style_marginTopFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_marginTop').closest('.form-group').slideUp();
        jQuery('#tld_style_marginTopFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
         tld_selectedElement.css({
           marginTop: 'auto' 
        });
    }
    }
}
function tld_style_marginTypeBottomChange() {
    if (jQuery('#tld_style_marginTypeBottom').val() == 'fixed') {
        jQuery('#tld_style_marginBottom').closest('.form-group').slideDown();
        jQuery('#tld_style_marginBottomFlex').closest('.form-group').slideUp();
    } else if (jQuery('#tld_style_marginTypeBottom').val() == 'flexible') {
        jQuery('#tld_style_marginBottom').closest('.form-group').slideUp();
        jQuery('#tld_style_marginBottomFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_marginBottom').closest('.form-group').slideUp();
        jQuery('#tld_style_marginBottomFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
         tld_selectedElement.css({
           marginBottom: 'auto' 
        });
    }
    }
}
function tld_style_paddingTypeBottomChange(){
    if (jQuery('#tld_style_paddingTypeBottom').val() == 'fixed') {
        jQuery('#tld_style_paddingBottom').closest('.form-group').slideDown();
        jQuery('#tld_style_paddingBottomFlex').closest('.form-group').slideUp();
    } else if (jQuery('#tld_style_paddingTypeBottom').val() == 'flexible') {
        jQuery('#tld_style_paddingBottom').closest('.form-group').slideUp();
        jQuery('#tld_style_paddingBottomFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_paddingBottom').closest('.form-group').slideUp();
        jQuery('#tld_style_paddingBottomFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
         tld_selectedElement.css({
           paddingBottom: 'auto' 
        });
    }
    }
}
function tld_style_paddingTypeTopChange(){
    if (jQuery('#tld_style_paddingTypeTop').val() == 'fixed') {
        jQuery('#tld_style_paddingTop').closest('.form-group').slideDown();
        jQuery('#tld_style_paddingTopFlex').closest('.form-group').slideUp();
    } else if (jQuery('#tld_style_paddingTypeTop').val() == 'flexible') {
        jQuery('#tld_style_paddingTop').closest('.form-group').slideUp();
        jQuery('#tld_style_paddingTopFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_paddingTop').closest('.form-group').slideUp();
        jQuery('#tld_style_paddingTopFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
        tld_selectedElement.css({
           paddingTop: 'auto' 
        });
    }
    }
}

function tld_style_paddingTypeLeftChange(){
    if (jQuery('#tld_style_paddingTypeLeft').val() == 'fixed') {
        jQuery('#tld_style_paddingLeft').closest('.form-group').slideDown();
        jQuery('#tld_style_paddingLeftFlex').closest('.form-group').slideUp();
    } else if (jQuery('#tld_style_paddingTypeLeft').val() == 'flexible') {
        jQuery('#tld_style_paddingLeft').closest('.form-group').slideUp();
        jQuery('#tld_style_paddingLeftFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_paddingLeft').closest('.form-group').slideUp();
        jQuery('#tld_style_paddingLeftFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
        tld_selectedElement.css({
           paddingLeft: 'auto' 
        });
    }
    }
}

function tld_style_paddingTypeRightChange(){
    if (jQuery('#tld_style_paddingTypeRight').val() == 'fixed') {
        jQuery('#tld_style_paddingRight').closest('.form-group').slideDown();
        jQuery('#tld_style_paddingRightFlex').closest('.form-group').slideUp();
    } else if (jQuery('#tld_style_paddingTypeRight').val() == 'flexible') {
        jQuery('#tld_style_paddingRight').closest('.form-group').slideUp();
        jQuery('#tld_style_paddingRightFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_paddingRight').closest('.form-group').slideUp();
        jQuery('#tld_style_paddingRightFlex').closest('.form-group').slideUp();
        if(tld_elementInitialized){
        tld_selectedElement.css({
           paddingRight: 'auto' 
        });
    }
    }
}


function tld_style_positionTopChange() {
    if (jQuery('#tld_style_positionTop').val() == 'fixed') {
        jQuery('#tld_style_top').closest('.form-group').slideDown();
        jQuery('#tld_style_topFlex').closest('.form-group').slideUp();
    } else if (jQuery('#tld_style_positionTop').val() == 'flexible') {
        jQuery('#tld_style_top').closest('.form-group').slideUp();
        jQuery('#tld_style_topFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_top').closest('.form-group').slideUp();
        if(tld_elementInitialized){
        tld_selectedElement.css({
           top: 'auto' 
        });
    }
    }
}
function tld_style_positionBottomChange() {
    if (jQuery('#tld_style_positionBottom').val() == 'fixed') {
        jQuery('#tld_style_bottom').closest('.form-group').slideDown();
    } else if (jQuery('#tld_style_positionBottom').val() == 'flexible') {
        jQuery('#tld_style_bottom').closest('.form-group').slideUp();
        jQuery('#tld_style_bottomFlex').closest('.form-group').slideDown();
    } else {
        jQuery('#tld_style_bottom').closest('.form-group').slideUp();
        if(tld_elementInitialized){
        tld_selectedElement.css({
           bottom: 'auto' 
        });
        }
        }
}
function tld_style_leftChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        left: jQuery('#tld_style_left').slider('value')
    });
    }
}
function tld_style_rightChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        right: jQuery('#tld_style_right').slider('value')
    });
        }
}
function tld_style_topChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        top: jQuery('#tld_style_top').slider('value')
    });

        }
    }
function tld_style_bottomChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        bottom: jQuery('#tld_style_bottom').slider('value')
    });
}
}
function tld_style_leftFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        left: jQuery('#tld_style_leftFlex').slider('value') + '%'
    });
        }
}
function tld_style_rightFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        right: jQuery('#tld_style_rightFlex').slider('value') + '%'
    });
        }
}
function tld_style_topFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        top: jQuery('#tld_style_topFlex').slider('value') + '%'
    });
        }
}
function tld_style_bottomFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        bottom: jQuery('#tld_style_bottomFlex').slider('value') + '%'
    });
        }
}
function tld_style_marginLeftFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        marginLeft: jQuery('#tld_style_marginLeftFlex').slider('value') + '%'
    });
        }
}
function tld_style_marginRightFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        marginRight: jQuery('#tld_style_marginRightFlex').slider('value') + '%'
    });
        }
}
function tld_style_marginTopFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        marginTop: jQuery('#tld_style_marginTopFlex').slider('value') + '%'
    });
        }
}
function tld_style_marginBottomFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        marginBottom: jQuery('#tld_style_marginBottomFlex').slider('value') + '%'
    });
        }
}
function tld_style_marginLeftChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        marginLeft: jQuery('#tld_style_marginLeft').slider('value')
    });
        }
}
function tld_style_marginRightChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        marginRight: jQuery('#tld_style_marginRight').slider('value') 
    });
        }
}
function tld_style_marginTopChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        marginTop: jQuery('#tld_style_marginTop').slider('value')
    });
        }
}
function tld_style_marginBottomChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        marginBottom: jQuery('#tld_style_marginBottom').slider('value') 
    });
        }
}
function tld_style_paddingLeftChange(){
        if(tld_elementInitialized){
    tld_selectedElement.css({
        paddingLeft: jQuery('#tld_style_paddingLeft').slider('value') 
    });   
        } 
}
function tld_style_paddingRightChange(){
        if(tld_elementInitialized){
    tld_selectedElement.css({
        paddingRight: jQuery('#tld_style_paddingRight').slider('value') 
    });    
        }
}
function tld_style_paddingTopChange(){
        if(tld_elementInitialized){
    tld_selectedElement.css({
        paddingTop: jQuery('#tld_style_paddingTop').slider('value') 
    });    
        }
}
function tld_style_paddingBottomChange(){
        if(tld_elementInitialized){
    tld_selectedElement.css({
        paddingBottom: jQuery('#tld_style_paddingBottom').slider('value') 
    });   
        } 
}
function tld_style_paddingLeftFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        paddingLeft: jQuery('#tld_style_paddingLeftFlex').slider('value') + '%'
    });
        }
}
function tld_style_paddingRightFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        paddingRight: jQuery('#tld_style_paddingRightFlex').slider('value') + '%'
    });
        }
}
function tld_style_paddingTopFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        paddingTop: jQuery('#tld_style_paddingTopFlex').slider('value') + '%'
    });
        }
}
function tld_style_paddingBottomFlexChange() {
        if(tld_elementInitialized){
    tld_selectedElement.css({
        paddingBottom: jQuery('#tld_style_paddingBottomFlex').slider('value') + '%'
    });
        }
}

function tld_style_fontTypeChange(){
    if(jQuery('#tld_style_fontType').val() == 'google'){
        jQuery('#tld_style_fontFamily').next('.tld_fieldBtn').show();
        jQuery('#tld_style_fontFamily').addClass('tld_fieldHasBtn');
        
    tld_selectedElement.attr('data-googlefont','true');
    }else{
        jQuery('#tld_style_fontFamily').next('.tld_fieldBtn').hide();   
        tld_selectedElement.removeAttr('data-googlefont');     
        jQuery('#tld_style_fontFamily').removeClass('tld_fieldHasBtn');
    }    
}
function tld_style_fontFamilyChange(){
        if(tld_elementInitialized){
            tld_selectedElement.css({
                fontFamily: jQuery('#tld_style_fontFamily').val()
            }); 
            if(!jQuery('#tld_style_fontFamily option:selected').is('[data-default="true"]')){
                tld_selectedElement.attr('data-googlefont','true');
                jQuery('#tld_tdgnFrame').contents().find('head').append("<link href='https://fonts.googleapis.com/css?family=" + jQuery('#tld_style_fontFamily').val() + "' rel='stylesheet' type='text/css'>");
            } else {                
                tld_selectedElement.removeAttr('data-googlefont');     
            }
        }

}
function tld_style_fontSizeChange(){
        if(tld_elementInitialized){
            tld_selectedElement.css({
                fontSize: jQuery('#tld_style_fontSize').slider('value')
            }); 
        }
}
function tld_style_lineHeightChange(){
        if(tld_elementInitialized){
    tld_selectedElement.css({
        lineHeight: jQuery('#tld_style_lineHeight').slider('value')+'px'
    });     
        }
}
function tld_style_lineHeightFlexChange(){
        if(tld_elementInitialized){
    tld_selectedElement.css({
        lineHeight: jQuery('#tld_style_lineHeightFlex').slider('value')+'%'
    });     
        }
}
function tld_style_lineHeightTypeChange(){
    if(jQuery('#tld_style_lineHeightType').val() == 'flexible'){
        jQuery('#tld_style_lineHeightFlex').closest('.form-group').slideDown();
        jQuery('#tld_style_lineHeight').closest('.form-group').slideUp();
    } else {        
        jQuery('#tld_style_lineHeightFlex').closest('.form-group').slideUp();
        jQuery('#tld_style_lineHeight').closest('.form-group').slideDown();
    }
}
function tld_style_scrollXChange(){
        if(tld_elementInitialized){
     tld_selectedElement.css({
        overflowX: jQuery('#tld_style_scrollX').val()
    });  
        }
}
function tld_style_scrollYChange(){
        if(tld_elementInitialized){
     tld_selectedElement.css({
        overflowY: jQuery('#tld_style_scrollY').val()
    });  
        }
}
function tld_style_visibilityChange(){
        if(tld_elementInitialized){
     tld_selectedElement.css({
        visibility: jQuery('#tld_style_visibility').val()
    });  
        }
}
function tld_style_shadowTypeChange(){
    var type = jQuery('#tld_style_shadowType').val();
    if(type == 'none'){
        jQuery('#tld_style_shadowX').closest('.form-group').slideUp();
        jQuery('#tld_style_shadowY').closest('.form-group').slideUp();
        jQuery('#tld_style_shadowSize').closest('.form-group').slideUp();
        jQuery('#tld_style_shadowColor').closest('.form-group').slideUp();
        jQuery('#tld_style_shadowAlpha').closest('.form-group').slideUp();
        tld_selectedElement.removeClass('tld_hasShadow');
    }else if (type == 'outside') {
        jQuery('#tld_style_shadowX').closest('.form-group').slideDown();
        jQuery('#tld_style_shadowY').closest('.form-group').slideDown(); 
        jQuery('#tld_style_shadowSize').closest('.form-group').slideDown();
        jQuery('#tld_style_shadowColor').closest('.form-group').slideDown();  
        jQuery('#tld_style_shadowAlpha').closest('.form-group').slideDown();   
        if(!tld_selectedElement.is('.tld_hasShadow')){
            tld_selectedElement.addClass('tld_hasShadow');  
        }
    } else {
        jQuery('#tld_style_shadowX').closest('.form-group').slideDown();
        jQuery('#tld_style_shadowY').closest('.form-group').slideDown();
        jQuery('#tld_style_shadowSize').closest('.form-group').slideDown();
        jQuery('#tld_style_shadowColor').closest('.form-group').slideDown();
        jQuery('#tld_style_shadowAlpha').closest('.form-group').slideDown();
        if(!tld_selectedElement.is('.tld_hasShadow')){
            tld_selectedElement.addClass('tld_hasShadow');  
        }
    }
    tld_style_shadowChange();
}
function tld_style_shadowChange(){
    var type = jQuery('#tld_style_shadowType').val();    
    var shadowX = jQuery('#tld_style_shadowX').slider('value'); 
    var shadowY = jQuery('#tld_style_shadowY').slider('value');
    var size = jQuery('#tld_style_shadowSize').slider('value');
    var newColor =jQuery('#tld_style_shadowColor').val();
    if (jQuery('#tld_style_shadowColor').val().indexOf('rgb')>-1) {
       newColor = tld_rgb2hex(jQuery('#tld_style_shadowColor').val());
     }
       newColor = tld_hex2Rgba(newColor, jQuery('#tld_style_shadowAlpha').slider('value'));
    if(type == 'none'){ 
        if(tld_elementInitialized){
        tld_selectedElement.css({
           boxShadow: 'none'
       });  
   }
   } else {
       var inside = '';
       if(type == 'inside'){inside = 'inset';}
        if(tld_elementInitialized){
        tld_selectedElement.css({
           boxShadow: newColor + +shadowX+'px '+shadowY+'px '+size+'px '+ inside+' '
       }); 
   }
   }
}
function tld_style_borderRadiusChange(){
    var topLeft = jQuery('#tld_style_borderRadiusTopLeft').slider('value'); 
    var topRight = jQuery('#tld_style_borderRadiusTopRight').slider('value');
    var bottomLeft = jQuery('#tld_style_borderRadiusBottomLeft').slider('value'); 
    var bottomRight = jQuery('#tld_style_borderRadiusBottomRight').slider('value');
    if(tld_elementInitialized){
        tld_selectedElement.css({
            'border-top-left-radius':topLeft+'px',
            'border-top-right-radius':topRight+'px',
            'border-bottom-left-radius':bottomLeft+'px',
            'border-bottom-right-radius':bottomRight+'px'
        });     
    }
    
}
function tld_style_textShadowChange(){
    var shadowX = jQuery('#tld_style_textShadowX').slider('value'); 
    var shadowY = jQuery('#tld_style_textShadowY').slider('value');    
    var newColor =jQuery('#tld_style_textShadowColor').val();
    if (jQuery('#tld_style_textShadowColor').val().indexOf('rgb')>-1) {
       newColor = tld_rgb2hex(jQuery('#tld_style_textShadowColor').val());
    }
    newColor = tld_hex2Rgba(newColor, jQuery('#tld_style_textShadowAlpha').slider('value'));
               
    if(tld_elementInitialized){
        tld_selectedElement.css({
            textShadow: shadowX+'px '+shadowY+'px '+newColor
        });     
    }
}
function tld_style_textAlignChange(){
    if(tld_elementInitialized){
        tld_selectedElement.css({
            textAlign: jQuery('#tld_style_textAlign').val()
        });  
    }
}
function tld_style_opacityChange(){
    
        if(tld_elementInitialized){
     tld_selectedElement.css({
        opacity: jQuery('#tld_style_opacity').slider('value')
    });  
        }
}
function tld_style_fontStyleChange(){
    var styles = new Array();
    jQuery('#tld_style_fontStyle option:selected').each(function(){
        styles.push(jQuery(this).attr('value'));
    });
    
        if(tld_elementInitialized){
        tld_selectedElement.css({fontStyle: 'normal'});
        tld_selectedElement.css({fontWeight: 'normal'});
        tld_selectedElement.css({textDecoration: 'none'});
    if(jQuery.inArray('bold',styles)>-1){
        tld_selectedElement.css({fontWeight: 'bold'});
    } 
    if (jQuery.inArray('italic',styles)>-1){
        tld_selectedElement.css({fontStyle: 'italic'});       
    } 
    if (jQuery.inArray('underline',styles)>-1){
        tld_selectedElement.css({textDecoration: 'underline'});        
    }
        }
}
function tld_style_fontColorChange(){
        if(tld_elementInitialized){
     tld_selectedElement.css({
        color: jQuery('#tld_style_fontColor').val()
    }); 
        }
}

function tld_saveCurrentElement() {
    var existingStyle = tld_getElementData(tld_selectedElement, tld_deviceMode);
    var domSelector = tld_getPath(tld_selectedElement,false,true);
    if(tld_selectedElement.attr('style') && tld_selectedElement.attr('style') != ""){
        
    if (existingStyle && existingStyle.domSelector == domSelector) {
        if(jQuery('#tld_stateSelect').val() == 'default'){
            existingStyle.style = tld_selectedElement.attr('style');
        } else if(jQuery('#tld_stateSelect').val() == 'focus'){
            existingStyle.focusStyle = tld_selectedElement.attr('style');
        } else {
            existingStyle.hoverStyle = tld_selectedElement.attr('style');
        }
    } else {
        var dataDevice = tld_getElementsDataByDevice(tld_deviceMode);
        if (dataDevice) {
            var newStyle = tld_selectedElement.attr('style').replace(tld_selectedElement.attr('data-originalstyle'), '');
            if (jQuery('#tld_tdgn_applyModifsTo').val() == 'cssClasses') {
                var filledClasses = jQuery('#tld_tdgn_applyToClasses').val();
                if(filledClasses.substr(filledClasses.length-1)==' '){
                    filledClasses = filledClasses.substr(0,filledClasses.length-1);
                }
                if(filledClasses.substr(filledClasses.length-1)==' '){
                    filledClasses = filledClasses.substr(0,filledClasses.length-1);
                }          
                filledClasses = '.'+filledClasses;
                domSelector = tld_getPath(tld_selectedElement,true,true);
                
            }
            if (jQuery('#tld_tdgn_applyScope').val() == 'page') {
                var pageClass = '';
                jQuery.each(jQuery('#tld_tdgnFrame').contents().find('body').attr('class').split(' '), function () {
                    if (this.indexOf('page-id-') == 0) {
                        pageClass = 'body.' + this;
                    }
                });
                domSelector = pageClass + ' '+domSelector;
            } else if (jQuery('#tld_tdgn_applyScope').val() == 'container') {
                if (jQuery('#tld_tdgn_scopeContainerClass').val().length > 0) {
                    domSelector = '.' + jQuery('#tld_tdgn_scopeContainerClass').val() +' > '+domSelector;
                }
            }
            if(jQuery('#tld_stateSelect').val() == 'default'){
                dataDevice.elements.push({
                    element: tld_selectedElement.get(0),
                    domSelector: domSelector,
                    style: newStyle
                });     
            }else if(jQuery('#tld_stateSelect').val() == 'focus'){
                dataDevice.elements.push({
                    element: tld_selectedElement.get(0),
                    domSelector: domSelector,
                    focusStyle: newStyle
                });      
            } else {
                dataDevice.elements.push({
                    element: tld_selectedElement.get(0),
                    domSelector: domSelector,
                    hoverStyle: newStyle
                });                                
            }
        }
    }
        tld_notification(lfb_data.texts['stylesApplied'],'glyphicon glyphicon-info-sign',false,true);
        tld_modifsMade = true;
        jQuery('.tld_tdgn_section:not(.tld_closed)').each(function(){
         tld_tdgn_toggleSection(jQuery(this),false);
        });
        tld_changeDeviceMode(tld_deviceMode);
    }
}
function tld_getElementData(element, device) {
    var rep = false;
    jQuery.each(tld_styles, function () {
        if (this.device == device) {
            jQuery.each(this.elements, function () {
                if (this.element === element.get(0)) {
                    rep = this;
                }
            });
        }
    });
    return rep;
}
function tld_getElementsDataByDevice(device) {
    var rep = false;
    jQuery.each(tld_styles, function () {
        if (this.device == device) {
            rep = this;
        }
    });
    return rep;
}
function tld_getGoogleFontsUsed(){
    tld_usedGoogleFonts = new Array();
    jQuery('#tld_tdgnFrame').contents().find('[data-googlefont="true"]').each(function(){
        var font = jQuery(this).css('font-family');
        if(jQuery.inArray(font,tld_usedGoogleFonts)==-1){
            tld_usedGoogleFonts.push(font);
        }
    });
    var rep = '';
    jQuery.each(tld_usedGoogleFonts,function(){
        rep+= this+',';
    });
    return rep;
}
function tld_formatStylesBeforeSend(){
    var rep = jQuery.extend(true, {}, tld_styles);
    jQuery.each(rep,function(){
        jQuery.each(this.elements,function(){
            this.element = '';
        });
    });
    return JSON.stringify(rep);
}
function tld_exportCSS() {
    lfb_showLoader();
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'tld_exportCSS',
            styles: tld_formatStylesBeforeSend(),
            formID: lfb_currentFormID,
            gfonts: tld_getGoogleFontsUsed()
        },
        success: function (rep) {
            var win = window.open(lfb_data.exportUrl + rep, '_blank');
            win.focus();
            jQuery('#lfb_loader').fadeOut();
        }
    });
}
function tld_leave(){    
    if(tld_modifsMade){
        jQuery('#tld_winSaveDialog').modal('show');        
        jQuery('#tld_winSaveDialog').fadeIn();
    }else {
        lfb_closeFormDesigner();     
    }
}
function tld_leaveConfirm(){
        lfb_closeFormDesigner();        
}
function tld_changeStateMode(){
    if(tld_elementInitialized){
     tld_changeDeviceMode(tld_deviceMode);
    }
}
function tld_resetStyles(){
    jQuery('#tld_winResetStylesDialog').modal('show');
}
function tld_resetSessionStyles(){
    jQuery.each(tld_styles,function(){
       this.elements = new Array(); 
    });
    tld_changeDeviceMode(tld_deviceMode);
}
function tld_resetAllStyles(){
    lfb_showLoader();
    tld_resetSessionStyles();
     jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'tld_resetCSS',
            formID: lfb_currentFormID
        },
        success: function (rep) {
            var random = Math.floor((Math.random() * 10000) + 1);
            tld_notification(lfb_data.texts['modifsSaved'],'glyphicon glyphicon-info-sign',false,true);          
            jQuery('#tld_tdgnFrame').attr('src',tld_previewUrl+'&tmp='+random);
            tld_initStyles();
            tld_unselectElement();
            jQuery('#lfb_loader').fadeOut();
        }
    });
}
function tld_editCSS(){
    lfb_showLoader();
     jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'tld_getCSS',
            formID: lfb_currentFormID
        },
        success: function (rep) {
            tld_editorCSS.setValue(rep);
            setTimeout(function(){                
                tld_editorCSS.refresh();
            },300);
            jQuery('#tld_winEditCSSDialog').modal('show');
            jQuery('#lfb_loader').fadeOut();
            
        }
    });
}
function tld_saveEditedCSS(){
    lfb_showLoader();
     jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        data: {
            action: 'tld_saveEditedCSS',
            formID: lfb_currentFormID,
            css: tld_editorCSS.getValue()
        },
        success: function (rep) {
            var random = Math.floor((Math.random() * 10000) + 1);
            tld_notification(lfb_data.texts['modifsSaved'],'glyphicon glyphicon-info-sign',false,true);          
            jQuery('#tld_tdgnFrame').attr('src',tld_previewUrl+'&tmp='+random);
            tld_initStyles();
            tld_unselectElement();
            jQuery('#lfb_loader').fadeOut();            
        }
    });
}
function tld_openSaveBeforeEditDialog(){
    if(tld_modifsMade){
        jQuery('#tld_winSaveBeforeEditDialog').modal('show');
    } else {
        tld_editCSS();
    }
}
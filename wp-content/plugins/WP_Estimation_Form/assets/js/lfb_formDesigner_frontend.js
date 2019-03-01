var tld_selectionMode = false;
var tld_hoveredElement;

jQuery(document).ready(function(){
    setTimeout(function(){        
         jQuery('#lfb_bootstraped.lfb_bootstraped').find('*:not([data-tldinit="true"])').mouseover(tld_hoverElement);
         jQuery('#lfb_bootstraped.lfb_bootstraped').find('*:not([data-tldinit="true"])').mouseout(tld_mouseOutElement);
        jQuery('#lfb_bootstraped.lfb_bootstraped').find('*:not([data-tldinit="true"])').click(tld_selectElement).attr('data-tldinit','true');
        jQuery(document).ajaxComplete(function() {            
            jQuery('#lfb_bootstraped.lfb_bootstraped').find('*:not([data-tldinit="true"])').mouseover(tld_hoverElement);
            jQuery('#lfb_bootstraped.lfb_bootstraped').find('*:not([data-tldinit="true"])').mouseout(tld_mouseOutElement);
            jQuery('#lfb_bootstraped.lfb_bootstraped').find('*:not([data-tldinit="true"])').click(tld_selectElement).attr('data-tldinit','true');
        });
    },1000);
    setInterval(function(){
    if(!tld_selectionMode){
         jQuery('#lfb_bootstraped.lfb_bootstraped').find('*:not([data-tldinit="true"])').mouseover(tld_hoverElement);
         jQuery('#lfb_bootstraped.lfb_bootstraped').find('*:not([data-tldinit="true"])').mouseout(tld_mouseOutElement);
        jQuery('#lfb_bootstraped.lfb_bootstraped').find('*:not([data-tldinit="true"])').click(tld_selectElement).attr('data-tldinit','true');
    }
    },5000);
    if(tld_isIframe()){
        jQuery('#estimation_popup').addClass('tld_preview');
    }
});
function tld_getStyleSrc(formID){
    var rep = '';
     jQuery.each(wpe_forms, function () {
        var form = this;
        if(form.formID == formID){
            rep = form.formStylesSrc;
        }
    });
    return rep;
}
function tld_mouseOutElement(e){
    if(tld_selectionMode){
        var self = this;
        if(jQuery(this).is(tld_hoveredElement)){
           jQuery(self).removeClass('tld_selectedElement');
           tld_hoveredElement = null;
        }
    }
}
function tld_hoverElement(e){
    if(tld_selectionMode){
        var self = this;
       var hasChildrenHovered = false;
       if (jQuery(self).children().length > 0){
          jQuery(self).children().each(function(){
              if(tld_isHovered(this)){
                  hasChildrenHovered = true;
              }
          });
       } 
       if(!hasChildrenHovered){
           jQuery(self).addClass('tld_selectedElement');
            tld_hoveredElement = jQuery(self);
           jQuery('.tld_selectedElement').not(jQuery(self)).removeClass('tld_selectedElement');
       }
    }
}
function tld_selectElement(e){
    if(tld_selectionMode){
        e.preventDefault();
        var self = this;
        if(tld_hoveredElement != null){
            tld_hoveredElement.addClass('tld_hasShadow');                          
            if(tld_hoveredElement.css('box-shadow')=='none'){
                tld_hoveredElement.removeClass('tld_hasShadow');                                
            }
            window.parent.tld_itemSelected(tld_hoveredElement[0]);
        }
    }
}
function tld_unSelectElement(){
    jQuery('#tld_selector').fadeOut();
     jQuery('.tld_selectedElement').removeClass('tld_selectedElement');
     jQuery('.tld_hasShadow').removeClass('tld_hasShadow');
}

function tld_getPath(el) {
    var path = '';
    if (jQuery(el).length > 0 && typeof (jQuery(el).prop('tagName')) != "undefined") {
        if (!jQuery(el).attr('id')) {
            
            var target =  '>' + jQuery(el).prop('tagName') + ':nth-child(' + (jQuery(el).index() + 1) + ')';
            if(jQuery(el).is('.genSlide')){
                    target = '> [data-stepid="'+jQuery(el).attr('data-stepid')+'"]';
                } else if(jQuery(el).is('[data-itemid]')){
                    target = '> [data-itemid="'+jQuery(el).attr('data-itemid')+'"]';                    
                }
            path = target + path;
            path = tld_getPath(jQuery(el).parent()) + path;
           
        } else {
            path += '#' + jQuery(el).attr('id');
        }
    }
    if(path.indexOf('>') == 0){
        path = path.substr(1,path.length);
    }
    return path;
}
function tld_isHovered(element){
    return jQuery(tld_getPath(element) + ":hover").length > 0;
}
function tld_isIframe() {
    try {
        return window.self !== window.top;
    } catch (e) {
        return true;
    }
}
function tld_changeSelectionMode(mode){
    tld_selectionMode = mode;
}

function tld_isModeSelection(){
    return tld_selectionMode;
}
function tld_isAnyParentFixed($el, rep) {
    if (!rep) {
        var rep = false;
    }
    try {
        if ($el.parent().length > 0 && $el.parent().css('position') == "fixed") {
            rep = true;
        }
    } catch (e) {

    }

    if (!rep && $el.parent().length > 0) {
        rep = tld_isAnyParentFixed($el.parent(), rep);
    }
    return rep;
}

function tld_updateSelector(){
    if(jQuery('.tld_selectedElement').length==0){
        jQuery('#tld_selector').hide();
    }
}
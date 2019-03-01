var gaProperty = gaoptoutSettings.ua;
var disableStr = 'ga-disable-' + gaProperty;

if (document.cookie.indexOf(disableStr + '=true') > -1) {
    window[disableStr] = true;
}

if( typeof gaOptout === 'undefined') {
    function gaOptout() {
        document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
        window[disableStr] = true;
    }
}

jQuery(document).ready(function($){
    $('.gaoptout').on('click', function(e){
        e.preventDefault();

        gaOptout();

        alert( gaoptoutSettings.disabled );
    });
});
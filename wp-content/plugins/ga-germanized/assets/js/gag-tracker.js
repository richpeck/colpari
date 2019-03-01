var $trackOutboundLink_ga = function($url, $callback)
{
    ga('send', 'event', 'outbound', 'click', $url, {
        'transport': 'beacon',
        'hitCallback': $callback
    });
};

var $trackOutboundLink_gtag = function($url, $callback)
{
    gtag('event', 'click', {
        'event_category': 'outbound',
        'event_label': $url,
        'transport_type': 'beacon',
        'event_callback': $callback
    });
};

jQuery(document).ready(function($){

    if( gagTracker.link_tracking ) {

        $('a:not([href^="' + gagTracker.url + '"], [href^="#"])').on('click', function (e) {

            var $callback;
            var $url = $(this).attr('href');

            if( typeof $url === 'undefined' || $url === '') {
                return;
            }

            if( $(this).attr('target') !== '_blank' ) {

                e.preventDefault();

                $callback = function() {
                    window.location.href = $url;
                };

            }

            if (gagTracker.mode == 'ua') {
                $trackOutboundLink_ga($url, $callback);
            } else {
                $trackOutboundLink_gtag($url, $callback);
            }

        });

    } // gagTracker.link_tracking

});
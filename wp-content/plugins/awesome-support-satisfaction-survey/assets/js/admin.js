/*
 *
 * @package   Awesome Support: Satisfaction Survey
 * @author    Robert W. Kramer III for Awesome Support <support@getawesomesupport.com>
 * @license   GPL-2.0+
 * @link      https://getawesomesupport.com
 * @copyright 2016. Awesome Support
 *
 */

/**
 *  Countdown seconds value rendered by PHP for us to use here
 */
var counter = jQuery('#cron-countdown').data('seconds');

jQuery('#ss-cancel-btn').css('display', 'visible');


var interval = setInterval(function () {

    counter--;

    // Display cron scheduled event countdown.
    if (counter > 0) {

        var diff = countdown(counter * 1000, 0,
                countdown.DAYS | countdown.HOURS | countdown.MINUTES | countdown.SECONDS);

        jQuery('#cron-days').text(diff.days);
        jQuery('#cron-hours').text(diff.hours);
        jQuery('#cron-minutes').text(diff.minutes);
        jQuery('#cron-seconds').text(diff.seconds);

    } else {
        jQuery('#cron-countdown').text('Client submission pending.');
        jQuery('.wrapper_countdown').css('display', 'none');
        jQuery('#ss-cancel-btn').css('display', 'none');

        clearInterval(interval);
    }
}, 1000);


;

/**
 *
 * @param time string value like 23:00
 * @returns {string}
 */
function formatTime(time) {
    var timeFormat = ea_settings.time_format;

    if (typeof timeFormat === 'undefined') {
        return time;
    }

    var m = moment(time, ['HH:mm']);

    if (!m.isValid()) {
        return '--:--';
    }

    if (timeFormat === 'am-pm') {
        return m.format('h:mm A');
    }

    return m.format('HH:mm');
}

/**
 *
 * @param date
 */
function formatDate(date) {
    var dateFormat = ea_settings.date_format;

    if (typeof dateFormat === 'undefined') {
        return date;
    }

    var m = moment(date, ['YYYY-MM-DD']);

    if (!m.isValid()) {
        return '-';
    }

    return m.format(dateFormat);
}

function formatDateTime(datetime) {

    if (typeof datetime === 'undefined' || datetime.length < 10) {
        return datetime;
    }

    var parts = datetime.split(' ');

    if (parts.length !== 2) {
        return datetime;
    }

    return formatDate(parts[0]) + ' ' + formatTime(parts[1]);
}

_.mixin({
    formatTime:formatTime,
    formatDate:formatDate,
    formatDateTime:formatDateTime
});
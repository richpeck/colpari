<?php

namespace TeamBooking\Toolkit;

use TeamBooking\Cache;

defined('ABSPATH') or die('No script kiddies please!');

/**
 * Find and replace hooks in a string.
 *
 * Hooks must be in the form: [hook] or [hook]SOME TEXT[/hook]
 *
 * @param mixed $string    String with hooks
 * @param array $variables Hooks values
 *
 * @return string String with hooks replaced by values
 */
function findAndReplaceHooks($string, array $variables)
{
    // Enclosure hooks (WordPress 4.4.0+ only)
    $pattern = get_shortcode_regex(array('cancellation_link', 'decline_link', 'approve_link', 'pay_link', 'ics_link'));
    $string = preg_replace_callback("/$pattern/s", function ($matches) use ($variables) {
        if (isset($variables[ strtolower(trim($matches[2], '[]')) ])) {
            $link = $variables[ strtolower(trim($matches[2], '[]')) ];
            unset($variables[ strtolower(trim($matches[2], '[]')) ]);

            return '<a href="' . $link . '">' . $matches[5] . '</a>';
        }

        return $matches[0];
    }, $string);

    // Single hooks
    $regex = "/(\[.*?\])/";
    $return = preg_replace_callback($regex, function ($matches) use ($variables) {
        if (isset($variables[ strtolower(trim($matches[1], '[]')) ])) {
            return \TeamBooking\Actions\email_hook_replace(strtolower(trim($matches[1], '[]')), $variables);
        }

        return \TeamBooking\Actions\email_hook_replace($matches[1], $variables);
    }, $string);

    return $return;
}

/**
 * @param array $strings
 *
 * @return string
 */
function findAndReplicateParts(array $strings)
{
    $regex = '/(?s)' . TBK_EMAIL_REPEAT_DELIMITER_OPEN . '(.*?)' . TBK_EMAIL_REPEAT_DELIMITER_CLOSE . '/';
    $found = array();
    foreach ($strings as $string) {
        preg_match_all($regex, $string, $matches);
        foreach ($matches[1] as $key => $match) {
            if (isset($found[ $key ])) {
                $found[ $key ] .= $match;
            } else {
                $found[ $key ] = $match;
            }
        }
    }
    $count = 0;

    $return = preg_replace_callback($regex, function ($matches) use ($found, &$count) {
        $count++;

        if (isset($found[ $count - 1 ])) return $found[ $count - 1 ];

        return $matches[1];
    }, reset($strings));

    return $return;
}

/**
 * @param string $string
 *
 * @return string
 */
function stripRepeatDelimiters($string)
{
    return str_replace(array(TBK_EMAIL_REPEAT_DELIMITER_OPEN, TBK_EMAIL_REPEAT_DELIMITER_CLOSE), '', $string);
}

/**
 * Generates a random number
 *
 * Used in session ID for Google Calendar events caching
 *
 * @param int $length
 *
 * @return int
 */
function randomNumber($length)
{
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9);
    }

    return $result;
}

/**
 * String filter function
 *
 * Checks for magic quotes (by PHP server side, or WP own method)
 * If check is true, then stripslashes.
 *
 * It also does htmlentities() for convenience
 *
 * If $remove_special_chars is TRUE, then get rid of
 * spaces and special chars and lowercase all (mandatory for IDs)
 *
 * @param string $text
 *
 * @return string filtered text
 */
function filterInput($text, $remove_special_chars = FALSE)
{
    if (get_magic_quotes_gpc() || function_exists('wp_magic_quotes')) {
        $text = stripslashes($text);
    }
    $return = htmlentities($text, ENT_QUOTES, 'UTF-8');
    if ($remove_special_chars) {
        $return = str_replace(' ', '-', $return); // Replaces all spaces with hyphens.
        $return = preg_replace('/[^A-Za-z0-9\-\_]/', '', $return); // Removes special chars except underscores
        $return = preg_replace('/-+/', '-', $return); // Replaces multiple hyphens with single one.
        $return = strtolower($return); // Lowercase all
    }

    return $return;
}

function stripTags($text)
{
    $default_attr = array(
        'id'    => array(),
        'class' => array(),
        'title' => array(),
        'style' => array(),
    );
    $allowedtags = array(
        'a'      => array_merge(array(
            'href' => TRUE,
        ), $default_attr),
        'img'    => array_merge(array(
            'src'    => TRUE,
            'width'  => TRUE,
            'height' => TRUE,
        ), $default_attr),
        'style'  => array(),
        'b'      => array($default_attr),
        'code'   => array($default_attr),
        'em'     => array($default_attr),
        'i'      => array($default_attr),
        'strong' => array(),
        'ul'     => array($default_attr),
        'ol'     => array($default_attr),
        'li'     => array($default_attr),
        'div'    => array($default_attr),
        'span'   => array($default_attr),
        'table'  => array($default_attr),
        'td'     => array($default_attr),
        'th'     => array($default_attr),
        'tr'     => array($default_attr),
        'pre'    => array($default_attr),
    );

    return wp_kses($text, $allowedtags);
}

/**
 * String unfilter function
 *
 * Centralize any unfiltering need on fields
 * Must be used together with tbFilterInput
 *
 * @param string $text
 *
 * @return string un-filtered text
 */
function unfilterInput($text)
{
    return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
}

/**
 * http://www.php.net/manual/en/function.hexdec.php#99478
 *
 * Convert a hexa decimal color code to its RGB equivalent
 *
 * @param string  $hexStr         (hexadecimal color value)
 * @param boolean $returnAsString (if set true, returns the value separated by the separator character.
 *                                Otherwise returns associative array)
 * @param string  $seperator      (to separate RGB values. Applicable only if second parameter is true.)
 *
 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
 */
function hex2RGB($hexStr, $returnAsString = FALSE, $seperator = ',')
{
    $hexStr = preg_replace('/[^0-9A-Fa-f]/', '', $hexStr); // Gets a proper hex string
    $rgbArray = array();
    if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
        $colorVal = hexdec($hexStr);
        $rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
        $rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
        $rgbArray['blue'] = 0xFF & $colorVal;
    } elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
        $rgbArray['red'] = hexdec(str_repeat($hexStr[0], 2));
        $rgbArray['green'] = hexdec(str_repeat($hexStr[1], 2));
        $rgbArray['blue'] = hexdec(str_repeat($hexStr[2], 2));
    } else {
        return FALSE; //Invalid hex color code
    }

    return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
}

/**
 * @param      $color
 * @param bool $opacity
 *
 * @return string
 */
function hex2RGBa($color, $opacity = FALSE)
{
    $default = 'rgb(0,0,0)';

    //Return default if no color provided
    if (empty($color)) return $default;

    //Sanitize $color if "#" is provided
    if ($color[0] === '#') {
        $color = substr($color, 1);
    }

    //Check if color has 6 or 3 characters and get values
    if (strlen($color) == 6) {
        $hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
    } elseif (strlen($color) == 3) {
        $hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
    } else {
        return $default;
    }

    //Convert hexadec to rgb
    $rgb = array_map('hexdec', $hex);

    //Check if opacity is set(rgba or rgb)
    if ($opacity) {
        if (abs($opacity) > 1) $opacity = 1.0;
        $output = 'rgba(' . implode(',', $rgb) . ',' . $opacity . ')';
    } else {
        $output = 'rgb(' . implode(',', $rgb) . ')';
    }

    //Return rgb(a) color string
    return $output;
}

/**
 * Returns the correct offset of ANY GIVEN TIME,
 * taking DST into account.
 *
 * DST is available only when a proper WordPress timezone
 * is present.
 *
 * @param int $the_time
 *
 * @return int
 */
function getRightGmtOffset($the_time)
{
    $timezone_string = get_option('timezone_string');
    if (empty($timezone_string)) {
        $gmt_offset = get_option('gmt_offset');
    } else {
        $tz = new \DateTimeZone($timezone_string);
        $transition = $tz->getTransitions($the_time, $the_time);
        $gmt_offset = $transition[0]['offset'] / 3600;
    }

    return $gmt_offset;
}

/**
 * A list of almost all currencies
 *
 * @return array
 */
function getCurrencies($code = NULL)
{
    $currencies = array(
        'AED' => array(
            'label'   => 'United Arab Emirates Dirham',
            'format'  => 'after',
            'locale'  => 'ar_AE',
            'symbol'  => '&#x62f;&#x2e;&#x625;',
            'decimal' => TRUE
        ),
        'AFN' => array(
            'label'   => 'Afghan Afghani',
            'format'  => 'after',
            'locale'  => 'fa_AF',
            'symbol'  => '&#1547;',
            'decimal' => TRUE
        ),
        'ALL' => array(
            'label'   => 'Albanian Lek',
            'format'  => 'before',
            'locale'  => 'sq_AL',
            'symbol'  => 'Lek',
            'decimal' => TRUE
        ),
        'AMD' => array(
            'label'   => 'Armenian Dram',
            'format'  => 'before',
            'locale'  => 'hy_AM',
            'symbol'  => '&#1423;',
            'decimal' => TRUE
        ),
        'ANG' => array(
            'label'   => 'Netherlands Antillean Gulden',
            'format'  => 'before',
            'locale'  => 'nl_SX',
            'symbol'  => '&#402;',
            'decimal' => TRUE
        ),
        'AOA' => array(
            'label'   => 'Angolan Kwanza',
            'format'  => 'before',
            'locale'  => 'pt_AO',
            'symbol'  => 'Kz;',
            'decimal' => TRUE
        ),
        'ARS' => array(
            'label'   => 'Argentine Peso',
            'format'  => 'before',
            'locale'  => 'es_AR',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'AUD' => array(
            'label'   => 'Australian Dollar',
            'format'  => 'before',
            'locale'  => 'en_AU',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'AWG' => array(
            'label'   => 'Aruban Florin',
            'format'  => 'before',
            'locale'  => 'nl_AW',
            'symbol'  => '&#402;',
            'decimal' => TRUE
        ),
        'AZN' => array(
            'label'   => 'Azerbaijani Manat',
            'format'  => 'before',
            'locale'  => 'az_Latn_AZ',
            'symbol'  => '&#8380;',
            'decimal' => TRUE
        ),
        'BAM' => array(
            'label'  => 'Bosnia & Herzegovina Convertible Mark',
            'format' => 'before',
            'locale' => 'hr_BA',
            'symbol' => 'KM',
        ),
        'BBD' => array(
            'label'   => 'Barbadian Dollar',
            'format'  => 'before',
            'locale'  => 'en_BB',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'BDT' => array(
            'label'   => 'Bangladeshi Taka',
            'format'  => 'before',
            'locale'  => 'bn_BD',
            'symbol'  => '&#2547;',
            'decimal' => TRUE
        ),
        'BGN' => array(
            'label'   => 'Bulgarian Lev',
            'format'  => 'after',
            'locale'  => 'bg_BG',
            'symbol'  => 'лв',
            'decimal' => TRUE
        ),
        'BIF' => array(
            'label'   => 'Burundian Franc',
            'format'  => 'before',
            'locale'  => 'rn_BI',
            'symbol'  => 'FBu',
            'decimal' => FALSE
        ),
        'BMD' => array(
            'label'  => 'Bermudian Dollar',
            'format' => 'before',
            'locale' => 'en_BM',
            'symbol' => '$',
        ),
        'BND' => array(
            'label'   => 'Brunei Dollar',
            'format'  => 'before',
            'locale'  => 'ms_Latn_BN',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'BOB' => array(
            'label'   => 'Bolivian Boliviano',
            'format'  => 'before',
            'locale'  => 'es_BO',
            'symbol'  => 'Bs.',
            'decimal' => TRUE
        ),
        'BRL' => array(
            'label'   => 'Brazilian Real',
            'format'  => 'before',
            'locale'  => 'pt_BR',
            'symbol'  => 'R$',
            'decimal' => TRUE
        ),
        'BSD' => array(
            'label'   => 'Bahamian Dollar',
            'format'  => 'before',
            'locale'  => 'en_BS',
            'symbol'  => 'B$',
            'decimal' => TRUE
        ),
        'BWP' => array(
            'label'   => 'Botswana Pula',
            'format'  => 'before',
            'locale'  => 'en_BW',
            'symbol'  => 'P',
            'decimal' => TRUE
        ),
        'BZD' => array(
            'label'   => 'Belize Dollar',
            'format'  => 'before',
            'locale'  => 'en_BZ',
            'symbol'  => 'BZ$',
            'decimal' => TRUE
        ),
        'CAD' => array(
            'label'   => 'Canadian Dollar',
            'format'  => 'before',
            'locale'  => 'en_CA',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'CDF' => array(
            'label'   => 'Congolese Franc',
            'format'  => 'before',
            'locale'  => 'fr_CD',
            'symbol'  => 'FC',
            'decimal' => TRUE
        ),
        'CHF' => array(
            'label'   => 'Swiss Franc',
            'format'  => 'before',
            'locale'  => 'fr_CH',
            'symbol'  => 'Fr',
            'decimal' => TRUE
        ),
        'CLP' => array(
            'label'   => 'Chilean Peso',
            'format'  => 'before',
            'locale'  => 'es_CL',
            'symbol'  => '$',
            'decimal' => FALSE
        ),
        'CNY' => array(
            'label'   => 'Chinese Renminbi Yuan',
            'format'  => 'before',
            'locale'  => 'zh_Hans_CN',
            'symbol'  => '&#165;',
            'decimal' => TRUE
        ),
        'COP' => array(
            'label'   => 'Colombian Peso',
            'format'  => 'before',
            'locale'  => 'es_CO',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'CRC' => array(
            'label'   => 'Costa Rican Colón',
            'format'  => 'before',
            'locale'  => 'es_CR',
            'symbol'  => '&#8353;',
            'decimal' => TRUE
        ),
        'CVE' => array(
            'label'   => 'Cape Verdean Escudo',
            'format'  => 'before',
            'locale'  => 'pt_CV',
            'symbol'  => 'Esc',
            'decimal' => FALSE
        ),
        'CZK' => array(
            'label'   => 'Czech Koruna',
            'format'  => 'after',
            'locale'  => 'cs_CZ',
            'symbol'  => 'Kč',
            'decimal' => TRUE
        ),
        'DJF' => array(
            'label'   => 'Djiboutian Franc',
            'format'  => 'before',
            'locale'  => 'fr_DJ',
            'symbol'  => 'Fdj',
            'decimal' => FALSE
        ),
        'DKK' => array(
            'label'   => 'Danish Krone',
            'format'  => 'before',
            'locale'  => 'da_DK',
            'symbol'  => 'kr',
            'decimal' => TRUE
        ),
        'DOP' => array(
            'label'   => 'Dominican Peso',
            'format'  => 'before',
            'locale'  => 'es_DO',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'DZD' => array(
            'label'   => 'Algerian Dinar',
            'format'  => 'before',
            'locale'  => 'fr_DZ',
            'symbol'  => '&#1583;.&#1580;',
            'decimal' => TRUE
        ),
        'EGP' => array(
            'label'   => 'Egyptian Pound',
            'format'  => 'before',
            'locale'  => 'ar_EG',
            'symbol'  => 'E&pound;',
            'decimal' => TRUE
        ),
        'ETB' => array(
            'label'   => 'Ethiopian Birr',
            'format'  => 'before',
            'locale'  => 'so_ET',
            'symbol'  => 'Br',
            'decimal' => TRUE
        ),
        'EUR' => array(
            'label'   => 'Euro',
            'format'  => 'before',
            'locale'  => '',
            'symbol'  => '&euro;',
            'decimal' => TRUE
        ),
        'FJD' => array(
            'label'   => 'Fijian Dollar',
            'format'  => 'before',
            'locale'  => 'en_FJ',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'FKP' => array(
            'label'   => 'Falkland Islands Pound',
            'format'  => 'before',
            'locale'  => 'en_FK',
            'symbol'  => '&pound;',
            'decimal' => TRUE
        ),
        'GBP' => array(
            'label'   => 'British Pound',
            'format'  => 'before',
            'locale'  => 'en_UK',
            'symbol'  => '&pound;',
            'decimal' => TRUE
        ),
        'GEL' => array(
            'label'   => 'Georgian Lari',
            'format'  => 'before',
            'locale'  => 'ka_GE',
            'symbol'  => '&#4314;',
            'decimal' => TRUE
        ),
        'GIP' => array(
            'label'   => 'Gibraltar Pound',
            'format'  => 'before',
            'locale'  => 'en_GI',
            'symbol'  => '&pound;',
            'decimal' => TRUE
        ),
        'GMD' => array(
            'label'   => 'Gambian Dalasi',
            'format'  => 'before',
            'locale'  => 'en_GM',
            'symbol'  => 'D',
            'decimal' => TRUE
        ),
        'GNF' => array(
            'label'   => 'Guinean Franc',
            'format'  => 'before',
            'locale'  => 'fr_GN',
            'symbol'  => 'FG',
            'decimal' => FALSE
        ),
        'GTQ' => array(
            'label'   => 'Guatemalan Quetzal',
            'format'  => 'before',
            'locale'  => 'es_GT',
            'symbol'  => 'Q',
            'decimal' => TRUE
        ),
        'GYD' => array(
            'label'   => 'Guyanese Dollar',
            'format'  => 'before',
            'locale'  => 'en_GY',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'HKD' => array(
            'label'   => 'Hong Kong Dollar',
            'format'  => 'before',
            'locale'  => 'en_HK',
            'symbol'  => 'HK$',
            'decimal' => TRUE
        ),
        'HNL' => array(
            'label'   => 'Honduran Lempira',
            'format'  => 'before',
            'locale'  => 'es_HN',
            'symbol'  => 'L',
            'decimal' => TRUE
        ),
        'HRK' => array(
            'label'   => 'Croatian Kuna',
            'format'  => 'before',
            'locale'  => 'hr_HR',
            'symbol'  => 'kn',
            'decimal' => TRUE
        ),
        'HTG' => array(
            'label'   => 'Haitian Gourde',
            'format'  => 'before',
            'locale'  => 'fr_HT',
            'symbol'  => 'G',
            'decimal' => TRUE
        ),
        'HUF' => array(
            'label'   => 'Hungarian Forint',
            'format'  => 'before',
            'locale'  => 'hu_HU',
            'symbol'  => 'Ft',
            'decimal' => FALSE
        ),
        'IDR' => array(
            'label'   => 'Indonesian Rupiah',
            'format'  => 'before',
            'locale'  => 'id_ID',
            'symbol'  => 'Rp',
            'decimal' => TRUE
        ),
        'ILS' => array(
            'label'   => 'Israeli New Sheqel',
            'format'  => 'before',
            'locale'  => 'he_IL',
            'symbol'  => '&#8362;',
            'decimal' => TRUE
        ),
        'INR' => array(
            'label'   => 'Indian Rupee',
            'format'  => 'before',
            'locale'  => 'en_IN',
            'symbol'  => '&#8377;',
            'decimal' => TRUE
        ),
        'ISK' => array(
            'label'   => 'Icelandic Króna',
            'format'  => 'before',
            'locale'  => 'is_IS',
            'symbol'  => 'kr',
            'decimal' => FALSE
        ),
        'JMD' => array(
            'label'   => 'Jamaican Dollar',
            'format'  => 'before',
            'locale'  => 'en_JM',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'JPY' => array(
            'label'   => 'Japanese Yen',
            'format'  => 'before',
            'locale'  => 'ja_JP',
            'symbol'  => '&yen;',
            'decimal' => FALSE
        ),
        'KES' => array(
            'label'   => 'Kenyan Shilling',
            'format'  => 'before',
            'locale'  => 'en_KE',
            'symbol'  => 'KSh',
            'decimal' => TRUE
        ),
        'KGS' => array(
            'label'   => 'Kyrgyzstani Som',
            'format'  => 'before',
            'locale'  => 'ru_KG',
            'symbol'  => '&#1083;&#1074;',
            'decimal' => TRUE
        ),
        'KHR' => array(
            'label'   => 'Cambodian Riel',
            'format'  => 'before',
            'locale'  => 'km_KH',
            'symbol'  => '&#6107;',
            'decimal' => TRUE
        ),
        'KMF' => array(
            'label'   => 'Comorian Franc',
            'format'  => 'before',
            'locale'  => 'fr_KM',
            'symbol'  => 'CF',
            'decimal' => FALSE
        ),
        'KRW' => array(
            'label'   => 'South Korean Won',
            'format'  => 'before',
            'locale'  => 'ko_KR',
            'symbol'  => '&#8361;',
            'decimal' => FALSE
        ),
        'KYD' => array(
            'label'   => 'Cayman Islands Dollar',
            'format'  => 'before',
            'locale'  => 'en_KY',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'KZT' => array(
            'label'   => 'Kazakhstani Tenge',
            'format'  => 'before',
            'locale'  => 'ru_KZ',
            'symbol'  => '&#8376;',
            'decimal' => TRUE
        ),
        'LAK' => array(
            'label'   => 'Lao Kipa',
            'format'  => 'before',
            'locale'  => 'lo_LA',
            'symbol'  => '&#8365;',
            'decimal' => TRUE
        ),
        'LBP' => array(
            'label'   => 'Lebanese Pound',
            'format'  => 'after',
            'locale'  => 'ar_LB',
            'symbol'  => '&#1604;.&#1604;',
            'decimal' => TRUE
        ),
        'LKR' => array(
            'label'   => 'Sri Lankan Rupee',
            'format'  => 'before',
            'locale'  => 'si_LK',
            'symbol'  => '&#588;s',
            'decimal' => TRUE
        ),
        'LRD' => array(
            'label'   => 'Liberian Dollar',
            'format'  => 'before',
            'locale'  => 'en_LR',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'LSL' => array(
            'label'   => 'Lesotho Loti',
            'format'  => 'before',
            'locale'  => '',
            'symbol'  => 'L',
            'decimal' => TRUE
        ),
        'MAD' => array(
            'label'   => 'Moroccan Dirham',
            'format'  => 'before',
            'locale'  => 'ar_MA',
            'symbol'  => '&#1583;.&#1605;.',
            'decimal' => TRUE
        ),
        'MDL' => array(
            'label'   => 'Moldovan Leu',
            'format'  => 'before',
            'locale'  => 'ro_MD',
            'symbol'  => 'L',
            'decimal' => TRUE
        ),
        'MGA' => array(
            'label'   => 'Malagasy Ariary',
            'format'  => 'before',
            'locale'  => 'en_MG',
            'symbol'  => 'Ar',
            'decimal' => FALSE
        ),
        'MKD' => array(
            'label'   => 'Macedonian Denar',
            'format'  => 'before',
            'locale'  => 'mk_MK',
            'symbol'  => '&#1076;&#1077;&#1085;',
            'decimal' => TRUE
        ),
        'MNT' => array(
            'label'   => 'Mongolian Tögrög',
            'format'  => 'before',
            'locale'  => 'mn_Cyrl_MN',
            'symbol'  => '&#8366;',
            'decimal' => TRUE
        ),
        'MOP' => array(
            'label'   => 'Macanese Pataca',
            'format'  => 'before',
            'locale'  => 'pt_MO',
            'symbol'  => 'MOP$',
            'decimal' => TRUE
        ),
        'MRO' => array(
            'label'   => 'Mauritanian Ouguiya',
            'format'  => 'before',
            'locale'  => 'ar_MR',
            'symbol'  => 'UM',
            'decimal' => FALSE
        ),
        'MUR' => array(
            'label'   => 'Mauritian Rupee',
            'format'  => 'before',
            'locale'  => 'en_MU',
            'symbol'  => '&#588;s',
            'decimal' => TRUE
        ),
        'MVR' => array(
            'label'   => 'Maldivian Rufiyaa',
            'format'  => 'before',
            'locale'  => '',
            'symbol'  => 'Rf',
            'decimal' => TRUE
        ),
        'MWK' => array(
            'label'   => 'Malawian Kwacha',
            'format'  => 'before',
            'locale'  => 'en_MW',
            'symbol'  => 'MK',
            'decimal' => TRUE
        ),
        'MXN' => array(
            'label'   => 'Mexican Peso',
            'format'  => 'before',
            'locale'  => 'es_MX',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'MYR' => array(
            'label'   => 'Malaysian Ringgit',
            'format'  => 'before',
            'locale'  => 'ta_MY',
            'symbol'  => 'RM',
            'decimal' => TRUE
        ),
        'MZN' => array(
            'label'   => 'Mozambican Metical',
            'format'  => 'before',
            'locale'  => 'mgh_MZ',
            'symbol'  => 'MT',
            'decimal' => TRUE
        ),
        'NAD' => array(
            'label'   => 'Namibian Dollar',
            'format'  => 'before',
            'locale'  => 'naq_NA',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'NGN' => array(
            'label'   => 'Nigerian Naira',
            'format'  => 'before',
            'locale'  => 'en_NG',
            'symbol'  => '&#8358;',
            'decimal' => TRUE
        ),
        'NIO' => array(
            'label'   => 'Nicaraguan Córdoba',
            'format'  => 'before',
            'locale'  => 'es_NI',
            'symbol'  => 'C$',
            'decimal' => TRUE
        ),
        'NOK' => array(
            'label'   => 'Norwegian Krone',
            'format'  => 'before',
            'locale'  => 'se_NO',
            'symbol'  => 'kr',
            'decimal' => TRUE
        ),
        'NPR' => array(
            'label'   => 'Nepalese Rupee',
            'format'  => 'before',
            'locale'  => 'ne_NP',
            'symbol'  => 'N&#588;s',
            'decimal' => TRUE
        ),
        'NZD' => array(
            'label'   => 'New Zealand Dollar',
            'format'  => 'before',
            'locale'  => 'en_NZ',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'PAB' => array(
            'label'   => 'Panamanian Balboa',
            'format'  => 'before',
            'locale'  => 'es_PA',
            'symbol'  => 'B/.',
            'decimal' => TRUE
        ),
        'PEN' => array(
            'label'   => 'Peruvian Nuevo Sol',
            'format'  => 'before',
            'locale'  => 'es_PE',
            'symbol'  => 'S/.',
            'decimal' => TRUE
        ),
        'PGK' => array(
            'label'   => 'Papua New Guinean Kina',
            'format'  => 'before',
            'locale'  => 'en_PG',
            'symbol'  => 'K',
            'decimal' => TRUE
        ),
        'PHP' => array(
            'label'   => 'Philippine Peso',
            'format'  => 'before',
            'locale'  => 'en_PH',
            'symbol'  => '&#8369;',
            'decimal' => TRUE
        ),
        'PKR' => array(
            'label'   => 'Pakistani Rupee',
            'format'  => 'before',
            'locale'  => 'en_PK',
            'symbol'  => '&#588;s',
            'decimal' => TRUE
        ),
        'PLN' => array(
            'label'   => 'Polish Złoty',
            'format'  => 'before',
            'locale'  => 'pl_PL',
            'symbol'  => 'z&#322;',
            'decimal' => TRUE
        ),
        'PYG' => array(
            'label'   => 'Paraguayan Guaraní',
            'format'  => 'before',
            'locale'  => 'es_PY',
            'symbol'  => '&#8370;',
            'decimal' => FALSE
        ),
        'QAR' => array(
            'label'   => 'Qatari Riyal',
            'format'  => 'after',
            'locale'  => 'ar_QA',
            'symbol'  => '&#1585;.&#1602;',
            'decimal' => TRUE
        ),
        'RON' => array(
            'label'   => 'Romanian Leu',
            'format'  => 'before',
            'locale'  => 'ro_RO',
            'symbol'  => 'L',
            'decimal' => TRUE
        ),
        'RSD' => array(
            'label'   => 'Serbian Dinar',
            'format'  => 'before',
            'locale'  => 'sr_Latn_RS',
            'symbol'  => '&#1044;&#1080;&#1085;.',
            'decimal' => TRUE
        ),
        'RUB' => array(
            'label'   => 'Russian Ruble',
            'format'  => 'before',
            'locale'  => 'ru_RU',
            'symbol'  => '&#8381;',
            'decimal' => TRUE
        ),
        'RWF' => array(
            'label'   => 'Rwandan Franc',
            'format'  => 'before',
            'locale'  => 'en_RW',
            'symbol'  => 'RF',
            'decimal' => FALSE
        ),
        'SAR' => array(
            'label'   => 'Saudi Riyal',
            'format'  => 'after',
            'locale'  => 'ar_SA',
            'symbol'  => '&#1585;.&#1587;',
            'decimal' => TRUE
        ),
        'SBD' => array(
            'label'   => 'Solomon Islands Dollar',
            'format'  => 'before',
            'locale'  => 'en_SB',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'SCR' => array(
            'label'   => 'Seychellois Rupee',
            'format'  => 'before',
            'locale'  => 'fr_SC',
            'symbol'  => '&#588;s',
            'decimal' => TRUE
        ),
        'SEK' => array(
            'label'   => 'Swedish Krona',
            'format'  => 'after',
            'locale'  => 'sv_SE',
            'symbol'  => 'kr',
            'decimal' => TRUE
        ),
        'SGD' => array(
            'label'   => 'Singapore Dollar',
            'format'  => 'before',
            'locale'  => 'en_SG',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'SHP' => array(
            'label'   => 'Saint Helenian Pound',
            'format'  => 'before',
            'locale'  => 'en_SH',
            'symbol'  => '&pound;',
            'decimal' => TRUE
        ),
        'SLL' => array(
            'label'   => 'Sierra Leonean Leone',
            'format'  => 'before',
            'locale'  => 'en_SL',
            'symbol'  => 'Le',
            'decimal' => TRUE
        ),
        'SOS' => array(
            'label'   => 'Somali Shilling',
            'format'  => 'before',
            'locale'  => 'so_SO',
            'symbol'  => 'So. Sh.',
            'decimal' => TRUE
        ),
        'SRD' => array(
            'label'   => 'Surinamese Dollar',
            'format'  => 'before',
            'locale'  => 'nl_SR',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'STD' => array(
            'label'   => 'São Tomé and Príncipe Dobra',
            'format'  => 'before',
            'locale'  => 'pt_ST',
            'symbol'  => 'Db',
            'decimal' => TRUE
        ),
        'SZL' => array(
            'label'   => 'Swazi Lilangeni',
            'format'  => 'before',
            'locale'  => 'en_SZ',
            'symbol'  => 'L',
            'decimal' => TRUE
        ),
        'THB' => array(
            'label'   => 'Thai Baht',
            'format'  => 'before',
            'locale'  => 'th_TH',
            'symbol'  => '&#3647;',
            'decimal' => TRUE
        ),
        'TJS' => array(
            'label'   => 'Tajikistani Somoni',
            'format'  => 'before',
            'locale'  => 'tg_Cyrl_TJ',
            'symbol'  => 'SM',
            'decimal' => TRUE
        ),
        'TOP' => array(
            'label'   => 'Tongan Paʻanga',
            'format'  => 'before',
            'locale'  => 'en_TO',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'TRY' => array(
            'label'   => 'Turkish Lira',
            'format'  => 'before',
            'locale'  => 'tr_TR',
            'symbol'  => '&#8378;',
            'decimal' => TRUE
        ),
        'TTD' => array(
            'label'   => 'Trinidad and Tobago Dollar',
            'format'  => 'before',
            'locale'  => 'en_TT',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'TWD' => array(
            'label'   => 'New Taiwan Dollar',
            'format'  => 'before',
            'locale'  => 'zh_Hant_TW',
            'symbol'  => 'NT$',
            'decimal' => FALSE
        ),
        'TZS' => array(
            'label'   => 'Tanzanian Shilling',
            'format'  => 'before',
            'locale'  => 'en_TZ',
            'symbol'  => 'TSh',
            'decimal' => TRUE
        ),
        'UAH' => array(
            'label'   => 'Ukrainian Hryvnia',
            'format'  => 'before',
            'locale'  => 'uk_UA',
            'symbol'  => '&#8372;',
            'decimal' => TRUE
        ),
        'UGX' => array(
            'label'   => 'Ugandan Shilling',
            'format'  => 'before',
            'locale'  => 'en_UG',
            'symbol'  => 'USh',
            'decimal' => FALSE
        ),
        'USD' => array(
            'label'   => 'United States Dollar',
            'format'  => 'before',
            'locale'  => 'en_US',
            'symbol'  => '$',
            'decimal' => TRUE
        ),
        'UYU' => array(
            'label'   => 'Uruguayan Peso',
            'format'  => 'before',
            'locale'  => 'es_UY',
            'symbol'  => '$U',
            'decimal' => TRUE
        ),
        'UZS' => array(
            'label'   => 'Uzbekistani Som',
            'format'  => 'before',
            'locale'  => 'uz_Latn_UZ',
            'symbol'  => '&#1083;&#1074;',
            'decimal' => TRUE
        ),
        'VEF' => array(
            'label'   => 'Venezuelan Bolívar',
            'format'  => 'before',
            'locale'  => 'es_VE',
            'symbol'  => 'Bs',
            'decimal' => TRUE
        ),
        'VND' => array(
            'label'   => 'Vietnamese Đồng',
            'format'  => 'after',
            'locale'  => 'vi_VN',
            'symbol'  => '&#8363;',
            'decimal' => FALSE
        ),
        'VUV' => array(
            'label'   => 'Vanuatu Vatu',
            'format'  => 'after',
            'locale'  => 'en_VU',
            'symbol'  => 'VT',
            'decimal' => FALSE
        ),
        'WST' => array(
            'label'   => 'Samoan Tala',
            'format'  => 'before',
            'locale'  => 'en_WS',
            'symbol'  => 'WS$',
            'decimal' => TRUE
        ),
        'XAF' => array(
            'label'   => 'Central African Cfa Franc',
            'format'  => 'before',
            'locale'  => 'fr_CF',
            'symbol'  => 'CFA',
            'decimal' => FALSE
        ),
        'XCD' => array(
            'label'   => 'East Caribbean Dollar',
            'format'  => 'before',
            'locale'  => 'en_AI',
            'symbol'  => 'EC$',
            'decimal' => TRUE
        ),
        'XOF' => array(
            'label'   => 'West African Cfa Franc',
            'format'  => 'before',
            'locale'  => 'fr_BF',
            'symbol'  => 'CFA',
            'decimal' => FALSE
        ),
        'XPF' => array(
            'label'   => 'Cfp Franc',
            'format'  => 'before',
            'locale'  => 'fr_PF',
            'symbol'  => 'F',
            'decimal' => FALSE
        ),
        'YER' => array(
            'label'   => 'Yemeni Rial',
            'format'  => 'after',
            'locale'  => 'ar_YE',
            'symbol'  => '&#65020;',
            'decimal' => TRUE
        ),
        'ZAR' => array(
            'label'   => 'South African Rand',
            'format'  => 'before',
            'locale'  => 'en_LS',
            'symbol'  => 'R',
            'decimal' => TRUE
        ),
        'ZMW' => array(
            'label'   => 'Zambian Kwacha',
            'format'  => 'before',
            'locale'  => 'en_ZM',
            'symbol'  => 'ZMW',
            'decimal' => TRUE
        ),
    );
    if (NULL === $code) {
        return $currencies;
    } else {
        return $currencies[ $code ];
    }
}

/**
 * Returns the current time UTC in seconds
 *
 * @return int
 */
function getNowInSecondsUTC()
{
    $default = date_default_timezone_get();
    date_default_timezone_set('UTC');
    $return = time();
    date_default_timezone_set($default);

    return $return;
}

/**
 * If a manual GMT offset is selected on WordPress
 * general settings, in order to work with DST
 * TeamBooking must pick the first Timezone
 * with equal offset.
 *
 * Correct results are not guaranteed, so
 * it's best for the user to set the right
 * Timezone in WordPress general settings.
 *
 * @return string
 */
function approximateTimezone()
{
    $array = \DateTimeZone::listIdentifiers();
    $now = new \DateTime();
    $manual_offset = get_option('gmt_offset');
    $offset = ceil($manual_offset) * 3600 + ($manual_offset - ceil($manual_offset)) * 60 * 60;
    $return = FALSE;
    $approx = array();
    foreach ($array as $tz) {
        $tz_new = new \DateTimeZone($tz);
        $approx[ abs($tz_new->getOffset($now) - $offset) ] = $tz;
        if ($tz_new->getOffset($now) == $offset) {
            $return = $tz_new->getName();
            break;
        }
    }

    if (!$return) {
        $key = min(array_keys($approx));
        $return = $approx[ $key ];
    }

    return $return;
}

/**
 * Returns the site's local timezone object, or a defined timezone object
 *
 * @param bool $timezone_string
 *
 * @return \DateTimeZone
 */
function getTimezone($timezone_string = FALSE)
{
    if (!$timezone_string) {
        $timezone_string = get_option('timezone_string');
    }
    try {
        $timezone = new \DateTimeZone($timezone_string);
    } catch (\Exception $ex) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $trace = debug_backtrace();
            if (isset($trace[1])) {
                trigger_error("{$ex->getMessage()} attempted by {$trace[1]['function']} on line {$trace[1]['line']}. Approximating.");
            } else {
                trigger_error("{$ex->getMessage()}. Approximating.");
            }
        }
        $timezone = new \DateTimeZone(approximateTimezone());
    }

    return $timezone;
}

/**
 * Extracts a domain from a given URL
 *
 * @param string $url
 *
 * @return boolean/string
 */
function getDomainFromUrl($url)
{
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : '';
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
    }

    return FALSE;
}

/**
 * base64 encoding
 *
 * @param      $object
 * @param bool $encrypt
 * @param bool $maybe_cached_id
 *
 * @return string
 */
function objEncode($object, $encrypt = FALSE, $maybe_cached_id = NULL)
{
    if (NULL !== $maybe_cached_id && NULL !== Cache::get('encoded_' . $maybe_cached_id)) {
        return Cache::get('encoded_' . $maybe_cached_id);
    } else {
        if (!$encrypt || ($encrypted = encrypt(gzdeflate(maybe_serialize($object)))) === '') {
            $return = base64_encode(gzdeflate(maybe_serialize($object)));
        } else {
            $return = $encrypted;
        }
        if (NULL !== $maybe_cached_id) Cache::add($return, 'encoded_' . $maybe_cached_id);

        return $return;
    }
}

/**
 * base64 decoding
 *
 * @param string $encoded
 * @param bool   $decrypt
 *
 * @return mixed
 */
function objDecode($encoded, $decrypt = FALSE)
{
    if (!$decrypt || ($decrypted = decrypt($encoded)) === '') {
        return maybe_unserialize(gzinflate(base64_decode($encoded)));
    } else {
        return maybe_unserialize(gzinflate($decrypted));
    }
}

/**
 * @param string $string
 * @param string $method
 *
 * @return string
 */
function encrypt($string, $method = 'AES-256-CBC')
{
    if (!extension_loaded('openssl')) {
        // no openssl extension loaded
        if (defined('WP_DEBUG') && WP_DEBUG) {
            trigger_error("string can't be encrypted - no openssl extension loaded", E_USER_NOTICE);
        }

        return '';
    } else {
        $key = \TeamBooking\Functions\getSettings()->getSecretKey();
        $ivlen = openssl_cipher_iv_length('AES-256-CBC');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($string, $method, $key, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = TRUE);

        return base64_encode($iv . $hmac . $ciphertext_raw);
    }
}

/**
 * @param        $string
 * @param string $method
 *
 * @return mixed
 */
function decrypt($string, $method = 'AES-256-CBC')
{
    if (!extension_loaded('openssl')) {
        // no openssl extension loaded
        if (defined('WP_DEBUG') && WP_DEBUG) {
            trigger_error("string can't be decrypted - no openssl extension loaded", E_USER_NOTICE);
        }

        return '';
    } else {
        $key = \TeamBooking\Functions\getSettings()->getSecretKey();
        $c = base64_decode($string);
        $ivlen = openssl_cipher_iv_length('AES-256-CBC');
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $method, $key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = TRUE);
        if (\TeamBooking\Functions\tb_hash_equals($hmac, $calcmac)) {
            return $original_plaintext;
        } else {
            return '';
        }
    }
}

/**
 * URL safe base64 encoding
 *
 * @param $input
 *
 * @return string
 */
function base64UrlEncode($input)
{
    return strtr(base64_encode($input), '+/=', '-_,');
}

/**
 * URL safe base64 decoding
 *
 * @param string $input
 *
 * @return string
 */
function base64UrlDecode($input)
{
    return base64_decode(strtr($input, '-_,', '+/='));
}

/**
 * @param $string
 *
 * @return mixed
 */
function lookingForJSON($string)
{
    $regex = "/(\{.*?\})/s";
    preg_match($regex, $string, $matches);

    return $matches;
}

function debug()
{
    $trace = debug_backtrace();
    $rootPath = dirname(__DIR__);
    $file = str_replace($rootPath, '', $trace[0]['file']);
    $line = $trace[0]['line'];
    $var = $trace[0]['args'][0];
    $lineInfo = sprintf('<div><strong>%s</strong> (line <strong>%s</strong>)</div>', $file, $line);
    $debugInfo = sprintf('<pre>%s</pre>', print_r($var, TRUE));
    print_r($lineInfo . $debugInfo);
}

function printDebug($content)
{
    ob_start();
    var_dump($content);
    file_put_contents(TEAMBOOKING_PATH . 'dump.txt', ob_get_clean(), FILE_APPEND | LOCK_EX);
}

function getPattern($int, $hex)
{
    if ($int == 0) {
        // no pattern
        return FALSE;
    }

    $rgb = hex2RGB($hex);
    $brightness = sqrt(
        $rgb['red'] * $rgb['red'] * .299 +
        $rgb['green'] * $rgb['green'] * .587 +
        $rgb['blue'] * $rgb['blue'] * .114
    );
    $pattern_bright = '';
    $pattern_dark = '';
    switch ($int) {
        case 1:
            // diagonal stripes
            $pattern_dark = 'iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAAMUlEQVQYV2NkwA+MGfHIGwPlzuJSAJYEacamAC6JTQGKJLoCDElkBVglYQpwSoIUAABJpQc89jWkNQAAAABJRU5ErkJggg==';
            $pattern_bright = 'iVBORw0KGgoAAAANSUhEUgAAAAgAAAAICAYAAADED76LAAAANUlEQVQYV2NkwAP+//9vzIhLHizJyHgWqwKYJEgzhgJkSQwF6JIoCrBJwhXgkgQrwCcJUgAAs0cfOXd7VnAAAAAASUVORK5CYII=';
            break;
        case 2:
            // vertical stripes
            $pattern_dark = 'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAGCAYAAADgzO9IAAAAEElEQVQIW2NkwA6MGQdSAgBOigE5ywFb9QAAAABJRU5ErkJggg==';
            $pattern_bright = 'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAGCAYAAADgzO9IAAAAE0lEQVQIW2NkwAL+//9vzDiQEgDlKxMnFgWj7QAAAABJRU5ErkJggg==';
            break;
        case 3:
            // horizontal stripes
            $pattern_dark = 'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAGCAYAAADgzO9IAAAAEklEQVQIW2NkwAEY6SRhjM0eAAbSADpE2BypAAAAAElFTkSuQmCC';
            $pattern_bright = 'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAGCAYAAADgzO9IAAAAFklEQVQIW2NkwAEY6SHx//9/Y2z2AABLjQM3c7wntwAAAABJRU5ErkJggg==';
            break;
        case 4:
            // small dots
            $pattern_dark = 'iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAGElEQVQIW2NkYGAwZmBgOMsABYwwBvECAD26AQUATQ6nAAAAAElFTkSuQmCC';
            $pattern_bright = 'iVBORw0KGgoAAAANSUhEUgAAAAQAAAAECAYAAACp8Z5+AAAAGUlEQVQIW2P8//+/MSMj41kGKGCEMYgXAAADvQQFZLynHQAAAABJRU5ErkJggg==';
            break;
        case 5:
            // horizontal triangle wave
            $pattern_dark = 'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAGCAYAAADgzO9IAAAALUlEQVQIW2NkwAEYsYgbMzAwnEWXAAsyMDAYgyTgHKgg2BCYDpgk3GRsdoAlAW7SBTrmWe1iAAAAAElFTkSuQmCC';
            $pattern_bright = 'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAGCAYAAADgzO9IAAAALklEQVQIW2NkwAEY0cX///9vzMjIeBZFAiYIplE4jIxnYSaAdcAkkY3FsAMmCQC89xc3gbr6PgAAAABJRU5ErkJggg==';
            break;
        case 6:
            // vertical triangular wave
            $pattern_dark = 'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAGCAYAAADgzO9IAAAAK0lEQVQIW2NkQABjBgaGszAuI5SBIggSg0mA2Fh1wEyASyLrQJFEl4DrAACoegYHteWU+AAAAABJRU5ErkJggg==';
            $pattern_bright = 'iVBORw0KGgoAAAANSUhEUgAAAAYAAAAGCAYAAADgzO9IAAAAKklEQVQIW2NkgIL///8bMzIynoXxGUEMdEGQGFgCmyRcAl0SRQJZEqcOAPd9GAdSaieBAAAAAElFTkSuQmCC';
            break;
    }

    if ($brightness < 145) {
        return 'data:image/png;base64,' . $pattern_bright;
    } else {
        return 'data:image/png;base64,' . $pattern_dark;
    }
}

function applyPercentage($number, $percentage)
{
    $return = $number - $number * $percentage / 100;
    if ($return < 0) {
        return 0;
    } else {
        return $return;
    }
}

function wrapAjaxResponse($response)
{
    $l_delimiter = '!!TBK-START!!';
    $r_delimiter = '!!TBK-END!!';

    return $l_delimiter . $response . $r_delimiter;
}

function unwrapAjaxResponse($response)
{
    $l_delimiter = '!!TBK-START!!';
    $r_delimiter = '!!TBK-END!!';
    $l_pos = stripos($response, $l_delimiter);
    $l_str = substr($response, $l_pos);
    $l_str = substr($l_str, strlen($l_pos));
    $r_pos = stripos($l_str, $r_delimiter);

    return substr($l_str, 0, $r_pos);
}

/**
 * Try to handle ajax pollution when returning JSON
 * (https://wordpress.stackexchange.com/a/184238)
 *
 * @param array $response
 */
function ajaxJsonResponse(array $response)
{
    if (defined('DOING_AJAX') && DOING_AJAX) {
        if (defined('TBK_AJAX_DISCARD_ALL_BUFFERS') && TBK_AJAX_DISCARD_ALL_BUFFERS) {
            $bufferContents = array();
            while (1 < ob_get_level())
                $bufferContents[] = ob_get_clean(); // save for?
            if (!ob_get_level())
                ob_start();
        }
        if (0 < ($bufferLength = ob_get_length())) {
            $bufferContents = ob_end_clean();
            if (WP_DEBUG) {
                $response['phpBuffer'] = $bufferContents;
            }
            if (WP_DEBUG_LOG) {
                $bufferLogFile = TEAMBOOKING_PATH . 'debug_buffer.log';
                $bufferContents = date('m/d/Y h:i:s a', time()) . ':' . chr(10) . $bufferContents . chr(10) . chr(10);
                error_log($bufferLength . ' characters of unexpected output were generated while processing an AJAX request in "' . plugin_dir_path(__FILE__) . __FILE__ . '". They have been recorded in "' . $bufferLogFile . '".');
                file_put_contents($bufferLogFile, $bufferContents, FILE_APPEND);
            }
        }
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    exit;
}

/**
 * Generate secure token
 * (source https://gist.github.com/raveren/5555297)
 *
 * @param string $type
 * @param int    $length
 *
 * @return string
 */
function generateToken($type = 'alnum', $length = 32)
{
    switch ($type) {
        case 'alnum':
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'alpha':
            $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'hexdec':
            $pool = '0123456789abcdef';
            break;
        case 'numeric':
            $pool = '0123456789';
            break;
        case 'nozero':
            $pool = '123456789';
            break;
        case 'distinct':
            $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
            break;
        case 'alnum_upper':
            $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        default:
            $pool = (string)$type;
            break;
    }


    $crypto_rand_secure = function ($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int)($log / 8) + 1; // length in bytes
        $bits = (int)$log + 1; // length in bits
        $filter = (int)(1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);

        return $min + $rnd;
    };

    $token = '';
    $max = strlen($pool);
    for ($i = 0; $i < $length; $i++) {
        $token .= $pool[ $crypto_rand_secure(0, $max) ];
    }

    return $token;
}

function getGcalHexColor($code)
{
    switch ($code) {
        case 1:
            return '#AC725E';
        case 2:
            return '#D06B64';
        case 3:
            return '#F83A22';
        case 4:
            return '#FA573C';
        case 5:
            return '#FF7537';
        case 6:
            return '#FFAD46';
        case 7:
            return '#42D692';
        case 8:
            return '#16A765';
        case 9:
            return '#7BD148';
        case 10:
            return '#B3DC6C';
        case 11:
            return '#FBE983';
        case 12:
            return '#FAD165';
        case 13:
            return '#92E1C0';
        case 14:
            return '#9FE1E7';
        case 15:
            return '#9FC6E7';
        case 16:
            return '#4986E7';
        case 17:
            return '#9A9CFF';
        case 18:
            return '#B99AFF';
        case 19:
            return '#C2C2C2';
        case 20:
            return '#CABDBF';
        case 21:
            return '#CCA6AC';
        case 22:
            return '#F691B2';
        case 23:
            return '#CD74E6';
        case 24:
            return '#A47AE2';
    }

    return TRUE;
}

function stripslashes_deep($value)
{
    $value = is_array($value) ? array_map('TeamBooking\Toolkit\stripslashes_deep', $value) : stripslashes($value);

    return $value;
}

function validateDateFormat($date_string, $format, $return_format = FALSE)
{
    $d = \DateTime::createFromFormat($format, $date_string);
    if (!$return_format) {
        return $d && $d->format($format) === $date_string;
    } else {
        if ($d && $d->format($format) === $date_string) {
            return $d->format($return_format);
        } else {
            return FALSE;
        }
    }

}
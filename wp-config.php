<?php
/** Enable W3 Total Cache */
define('WP_CACHE', true); // Added by W3 Total Cache

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', getenv('DB_NAME'));

/** MySQL database username */
define('DB_USER', getenv('DB_USER'));

/** MySQL database password */
define('DB_PASSWORD', getenv('DB_PASS'));

	/** MySQL hostname */
define('DB_HOST', getenv('DB_HOST'));

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '$[ j-Noj&=Z8w(D(_*J6/sOHW^Zt<UQ$;x4}MNF/bnPT%,eh^Fb2fzyc)~,a4936');
define('SECURE_AUTH_KEY',  'l0f^A~*ewJ4d`fktmq3)Upg}%-eDI<G#,/{/cC-R{h]g5o{l]/Y=fy.:,G!B46O7');
define('LOGGED_IN_KEY',    'VmBYi%r?U;31?M~f.Hgj/dTiX8Epry64lX@E#EZ=K,:0FULCsUL,+~3tnmI.~D#v');
define('NONCE_KEY',        ']@5!i4&7[nGbvs_sR(i>I3:aY)s7~u{YTRB95SbxvKU+vhrPak}d|0y]1#,rVc^6');
define('AUTH_SALT',        '[wRUY|@?rNbC+a78ieungRh)+EWOwlMpe_W5k3Nr :S05I#G3XgQhmn]kDb,{%VA');
define('SECURE_AUTH_SALT', 't;`.:TdU9%=[p{gGDjY*q7PC?$?:-~6KHJVkp,c|D}h=qkR;:4^n @WBI)DymLf~');
define('LOGGED_IN_SALT',   'a< I*~`~v9[?6@=]0I*$<?uB{}yDA2Saj>,PAEBT+|WUhS=bc5EeI@-6Ffd..2.<');
define('NONCE_SALT',       'YPI6AJ_muTg:ZH)RSS#a<*V2e8rMub9T?yzYyX~<V!;e}mp<xQ@jQ@t3Tjs(Ja4Z');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'www_';

define( 'WP_HOME', 'https://www.colpari.com' );
define( 'WP_SITEURL', 'https://www.colpari.com' );

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
 
 // Enable WP_DEBUG mode
define( 'WP_DEBUG', false );

// Enable Debug logging to the /wp-content/debug.log file
define( 'WP_DEBUG_LOG', true );

// Disable display of errors and warnings 
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

// Use dev versions of core JS and CSS files (only needed if you are modifying these core files)
define( 'SCRIPT_DEBUG', true );

define('WP_ALLOW_MULTISITE', true);

define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', true);
define('DOMAIN_CURRENT_SITE', 'colpari.com');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);

define( 'WP_MEMORY_LIMIT', '512M' );

define( 'WP_MAX_MEMORY_LIMIT', '256M' );

define( 'SUNRISE', 'on' );

/* That's all, stop editing! Happy blogging. */
set_time_limit(600);
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

/** Here you set up the language */
//define('WPLANG', 'de_CH');

<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'db_d09d40f3' );

/** Database username */
define( 'DB_USER', 'user_e542536f' );

/** Database password */
define( 'DB_PASSWORD', 'pw_Cq0IcmVcBAxUBajR1n0nq3JWItN0jmvw
' );

/** Database hostname */
define( 'DB_HOST', 'db.fr-roub1.bengt.wasmernet.com' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'Sbm9L3;v@ZL&:sv&}$y]|8]mgt0AL(nO7&:J.)<MS%o9OR870_57Oi,rg8w{7s~m' );
define( 'SECURE_AUTH_KEY',   '[{./YOGx7Hj27cc|lab%SF>dp$4~-Wz@jkBR,yN*yOQ,+&s(sFmMzy#7lio>DnFH' );
define( 'LOGGED_IN_KEY',     '^zH:-fxYE5>~q1ijaI8iI|nL!|D4]5V%FHS?RC0Jf}j&9Xm>r@6m(zW@ )<W)ar=' );
define( 'NONCE_KEY',         'Tq.+f|h#~,XxInk~1azo*;%TnLax6vZmtxEpseSvH;N<0Op]n(FPFP_*N;{)GD{[' );
define( 'AUTH_SALT',         'Q+%f>Cj6(<>BA`2H@|p(bG `h*/t.~^ak4hFn;*0a(~.u*`=K}^S{ch@vl7(&bYd' );
define( 'SECURE_AUTH_SALT',  'fm^Y<=(/:lozJz5Eat_#:<Pfs@<}<unb GE*N1lQ^H6!*{6K40U)8!C>N9G>.jGM' );
define( 'LOGGED_IN_SALT',    '&6=Zi,3(c0sMRVXuK8}K^A!gv}0:AbT0$[N+11[C3CUYq8&[lw_R.#NZB/Y!U}i4' );
define( 'NONCE_SALT',        'LWwNQB?hg. S]5KEe4]#7e(6.HBcibj2bX@I&-0Qh$%o# @_H_wGVHy&(k0~ci2c' );
define( 'WP_CACHE_KEY_SALT', 'zbqPOBrKL+%HkYf3jcd6{TN:VWs~+$t|xMl?/y>U8Xr8+h#c2i(Uh`DMj148Rp&Q' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

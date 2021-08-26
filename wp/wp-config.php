<?php
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
define('DB_NAME', 'ibottles_wpdb');

/** MySQL database username */
define('DB_USER', 'ibottlMutC');

/** MySQL database password */
define('DB_PASSWORD', '36e3eecd48782274nWm6BalFfQFZLSTE6oU=');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

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
define('AUTH_KEY',         '[Qe@r{tjGy=o:tsJ]7(0^7WkP>yWk9HLrc0}ad>D+m @Cb%cOw)+|r2(`#>ue+Xh');
define('SECURE_AUTH_KEY',  '+Ejg$U86![$CggwV8+ZALVI-i4r(m(gE@tURx-_JoH4h7vkBuP4&_:x7c5JZ8xX?');
define('LOGGED_IN_KEY',    'b@Bj<@0C]Sh}OXxyDDJMvee67/sSLYBXh/0<r_pE?x,vE~#SPc=?!&l]`2t:.XO8');
define('NONCE_KEY',        '#S$&Vgo:uyl>:eeV{zE|MPA&{t(3[x,`{]F]ck:>]4DK;grrL]e=Z?c|)5Y.`y$*');
define('AUTH_SALT',        'BjP$f-ld;=F`hIFS!F4Mm7={w`uWZm4!.2[/^&To;prGZ`1~^%(fgvNF1gxrWM{.');
define('SECURE_AUTH_SALT', 'If=@RVQzg(g|R2JXxi62:srB6B$.dL$VORcNT*];=uGRiE2wa!Y9 Z;Mb.Gt])0s');
define('LOGGED_IN_SALT',   '&`m*z/<2Hm=R578D:zsD82L@eX`DFv@(+UEdqY&I]uGqg&?>?ra&<W4F&@+#erxS');
define('NONCE_SALT',       '+Bs}oF>ak=:NF_.l`L9#lMlK%jt*:md+WE1[ECzm-p8aM-A<Xb:&+DNO*4c}(<S@');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'ib_';

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
define('WP_DEBUG', true);



define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

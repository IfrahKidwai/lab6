<?php 
define('USE_FETCH_FOR_REQUESTS',true);
?><?php 
define('WP_HOME','https://playground.wordpress.net/scope:0.8429369222948047');
define('WP_SITEURL','https://playground.wordpress.net/scope:0.8429369222948047');
?><?php 
define('WP_DEBUG_LOG',true);
define('WP_DEBUG_DISPLAY',false);
?><?php define( 'CONCATENATE_SCRIPTS', false );
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
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'database_name_here' );

/** Database username */
define( 'DB_USER', 'username_here' );

/** Database password */
define( 'DB_PASSWORD', 'password_here' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY','vq&y^7s4mrNW%g-[jc#kM]e)@GtiQcV_7QyR!YX.');
define( 'SECURE_AUTH_KEY','s@tt!rjp7UJHc?onGQ7h3WC&)t2sV?#d?g,zf5V,');
define( 'LOGGED_IN_KEY','8$k7NvquM8qO8r(<[CFmSwZBGK<.]A&R0m%oK5Zr');
define( 'NONCE_KEY','EH2Z<fu37%t-T$5-$&1)TtWhM*XKvX23+gX23]FL');
define( 'AUTH_SALT','RD)6601]HI.ha/!Y_iY8pR5rNQ?jogLd&Y+GH#.Z');
define( 'SECURE_AUTH_SALT','<2Uf8+?egJV*zu?^Gb0,A[<Snf*d#yl3vak)OS)P');
define( 'LOGGED_IN_SALT','cUCM@<fhTOnaI3[!WEuSFFT+(S.aXviU#?E2+)l<');
define( 'NONCE_SALT','Bqa,hwl9JUQmLd]HQj,-%_pXC-2mzKn,unpCP9.v');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG',true);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

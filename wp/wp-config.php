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
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'nirojan1' );

/** Database hostname */
define( 'DB_HOST', '127.0.0.1' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '@$s}+43pE-8voCm(Hk/a@sWFr#T!@=[@0 gUX[`q=j79PyuDt&3UqTlRCZpW8&Kl' );
define( 'SECURE_AUTH_KEY',  '&>;~5GRKf)-N::mW@y+2U2Q]z0SG.C2gt`vKS+[xQ10aW3L2;k!{c=&WE`j6m*u!' );
define( 'LOGGED_IN_KEY',    'UPt]L/ 7`QAVAt 6>*VqSQ=r30(jjVuqu3Ti2KP.Juleacs>v7KN`}C]mlpkrFM:' );
define( 'NONCE_KEY',        '2=y8jKJjS&Cb@V{C#9+_]YtUjh=Z$r_{.6*)%z#0@Cd2I6v:CS7~R0},<Icv,-L!' );
define( 'AUTH_SALT',        '`#J[T?~d3!h^.1-}`|pNxyU^D^w:E7^hKFV }N/C2DwPf-b6AN]&UKf<7vM+jI~X' );
define( 'SECURE_AUTH_SALT', 'ib&<RH;/2EN2&G,7>(Y>Hq;S,:0#VGO> _G39zHKY1w6l`.8T~Cf|o}T]KD?7ju^' );
define( 'LOGGED_IN_SALT',   'TvTR/tp#yWhYp)rGTyitA.3|n+Xprk<)Q]!&Ssp&w.W}:gtHL=d8)SIq?<B{)NX2' );
define( 'NONCE_SALT',       '-=oRp2}W:]Q{iI-ruIzri&V1rQL)H!n:g~+w=JB2/!$?T`GW~4Hh~D//5 1jAuJA' );

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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

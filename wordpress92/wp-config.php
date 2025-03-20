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
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress92' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'nirojan1' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'UzY81?53MW.AT}cxTU|s_w-Wh2J1dB9Nc)#lZaDu2tc|5kB8K4+HwdGCqEaFzPTs' );
define( 'SECURE_AUTH_KEY',   'yN`W+7ZTjieEvH=KC,w$~**9KYFtZ`:xqXDzoShLsjn]hCYKDaA9D(es$?~@r6NV' );
define( 'LOGGED_IN_KEY',     'Y_jJ[k^=HFKh,`STx}M7f%QFD4|/q1gIQnY]Tg8jx}it$nxGUE1&4UAT%nOiUD#$' );
define( 'NONCE_KEY',         ':ZJc~5.`~LT UV[/`$3ixi3fc|Nu_4.5y5(*iDwHz2VLd:z;_iQU!lc&Q;WUlLH/' );
define( 'AUTH_SALT',         '{@|am@C<v(|h2D:Ry.(K6B|fvj3qVgmCKx7V96e3^GhM_DmhhvziZGk%|r)jL{i6' );
define( 'SECURE_AUTH_SALT',  '<kx{j3f=5cz sQ?0F6HX1Nim_B%CWVlxhrx5=iA.4a0j)HK#aa-Ru:v.e7<R,*M*' );
define( 'LOGGED_IN_SALT',    'k^#kFFEue@[OF%y]%RKK</8E~4ozD3d>G;-ym VmN8lNgYb6Ok4EUEe6*1dv5}y<' );
define( 'NONCE_SALT',        'pX`;<!U,zoG_ GE QIxgR2(Y~QPW3f5,sUUzBCd8LI?YzI1)lLnAx.N|9P5%H2.p' );
define( 'WP_CACHE_KEY_SALT', '3?1DMj-ma8+rR :y|u)w)0Olpxc@`izYo[3/Rh81`2h;)^~W{n99o@k4Q*WJ{q+C' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';




/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';

<?php

//define('WP_ALLOW_REPAIR', true);
define( 'WP_CACHE', true ) ;
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
define( 'DB_NAME', 'u517045011_BBLBu' );

/** MySQL database username */
define( 'DB_USER', 'u517045011_XG5yv' );

/** MySQL database password */
define( 'DB_PASSWORD', 'Ea3XBPhxlg' );

/** MySQL hostname */
define( 'DB_HOST', 'mysql' );

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
define( 'AUTH_KEY',          ',[x6WI%QTD=H%}n}FEM`bh,)>NJa8p8`$/n->c(|i$W=[7-sfh`f=P00$*x]eh<~' );
define( 'SECURE_AUTH_KEY',   '&u%AQ>=NHj;SnorfK]359dg8&:ne$!Z @T(D[X3+[gEuRI?>eX{4K3HLoaAEETx3' );
define( 'LOGGED_IN_KEY',     '0E2055s4+W@(!W Sp8+wh._;)<5g;f[*lK|}gu:XKM<TGOJBBwaD=B6Jzq@$W~3^' );
define( 'NONCE_KEY',         '>CpdWl|4+z6*c=}`Q+fxa[F>9k-Ji5V!TH6dspxp&qh.Y7%P1]&Ld+?HQEY4[>2d' );
define( 'AUTH_SALT',         'T(e-qW_JzZI+3[jmrT!Gb?;EUhL-Wnen+tH-/uI~{h_KF2/Jls$+dg?~HSwtn >X' );
define( 'SECURE_AUTH_SALT',  ',abN%mBFQeo@O|.]kaA4bu5W4I|H^rla{`)%5::Wm#r&XM<sVV/N}IY;FTX9A8Ys' );
define( 'LOGGED_IN_SALT',    ']-S@kdM~vA.sag4m8UllC,6O<D7:8`sJc-keK&8~N{<uJyb`5ZR|clRSV|nmQq-O' );
define( 'NONCE_SALT',        'r!kXk0>cawwEZ,Y`H]7iq9/TMF$(}NNth*!<)gYx*~Cbcvfo<#g&clfG@.b`}cxI' );
define( 'WP_CACHE_KEY_SALT', '4+?-T8<n7VQV u}y(KsM$I1Mkd>3Vqo(bfTH.{@MNC&e}wakc=%}N1;_uQG} 9#$' );

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

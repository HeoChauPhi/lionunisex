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

/**
 * Local configuration information.
 *
 * If you are working in a local/desktop development environment and want to
 * keep your config separate, we recommend using a 'wp-config-local.php' file,
 * which you should also make sure you .gitignore.
 */
if (file_exists(dirname(__FILE__) . '/wp-config-local.php') && !isset($_ENV['PANTHEON_ENVIRONMENT'])):
  # IMPORTANT: ensure your local config does not include wp-settings.php
  require_once(dirname(__FILE__) . '/wp-config-local.php');

else:
  /**
   * This block will be executed if you have NO wp-config-local.php and you
   * are NOT running on Pantheon. Insert alternate config here if necessary.
   *
   * If you are only running on Pantheon, you can ignore this block.
   */
  define('DB_NAME',          'database_name');
  define('DB_USER',          'database_username');
  define('DB_PASSWORD',      'database_password');
  define('DB_HOST',          'database_host');
  define('DB_CHARSET',       'utf8');
  define('DB_COLLATE',       '');
endif;

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'P8i$.rNO!g>w/Kl&)0/&YL}^czV}Q%0`=ZE^6nd,c5!~tx $jo>[u2-MeL1 e>DR' );
define( 'SECURE_AUTH_KEY',  'L|@CPS X5Os2tY~;oh^%Uf%O6x<>h^G+ZzHI3 hBvP2ZT4 Ut@!5Ja+4;#rbF6{3' );
define( 'LOGGED_IN_KEY',    '*-i[$`)V~!4^^+wpZ@gcaF9/F/pFrMy7akABdLB[h!-J1ysXB<j18=Of8Q)Kq+o2' );
define( 'NONCE_KEY',        '[4=fX1C_ha;({JWIN0SQk;4dsEXM}_/%%,kd4qWETM*@A0qh?7g^nk~gIJKN*o2g' );
define( 'AUTH_SALT',        'nq#bdG15hJCy:TlzIBo|D|Rt< zV5~kInp~Y<i26-*Xn=@x4E!{Jf9t$*(0^5AJj' );
define( 'SECURE_AUTH_SALT', '`r~>:m0*>M;zgBJ7Y_{x=?,lm{`.he|*>_]4E]s]mcg;.iy=BG$ptrV!]|P<|2K.' );
define( 'LOGGED_IN_SALT',   '|sdP=8Y|w4xg%/SM+A7~[&7zH]232OZgnP$;h/net6%ox4%d9%QUqHvuwS@rM_HX' );
define( 'NONCE_SALT',       '#p:O{8?Chw94P<pl>_qZ*.RP)?S5L@b:v!=-H~NKvg1J(i:3KqblmUD]u}~8{{A^' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

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
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );

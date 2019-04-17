<?php
//echo WPML_PLUGIN_PATH;
// Load ACF Pro plugin
// 1. customize ACF path
add_filter('acf/settings/path', 'ffw_acf_settings_path');
function ffw_acf_settings_path( $path ) {
  // update path
  $path = get_stylesheet_directory() . '/wp-libs/plugins/advanced-custom-fields/';

  return $path;
}
 
// 2. customize ACF dir
add_filter('acf/settings/dir', 'ffw_acf_settings_dir');
function ffw_acf_settings_dir( $dir ) {
  // update path
  $dir = get_stylesheet_directory_uri() . '/wp-libs/plugins/advanced-custom-fields/';

  return $dir; 
}
 
// 3. Hide ACF field group menu item
//add_filter('acf/settings/show_admin', '__return_false');

// 4. Include ACF
include_once( get_stylesheet_directory() . '/wp-libs/plugins/advanced-custom-fields/acf.php' );

// Load packages from Composer
if ( file_exists( __DIR__ . '/vendor/autoload.php')) {
  require_once( __DIR__  . '/vendor/autoload.php');
}

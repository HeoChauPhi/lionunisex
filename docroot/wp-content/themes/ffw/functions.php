<?php
/*
 *  Author: Framework | @Framework
 *  URL: wordfly.com | @wordfly
 *  Custom functions, support, custom post types and more.
 */

ini_set('log_errors','On');
ini_set('display_errors','On');
ini_set('error_reporting', E_ALL );
/*define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);*/

// WP libs include
require_once('wp-libs/wp-libs-include.php');

// Theme setting
require_once('init/theme-init.php');
require_once('init/theme-shortcode.php');
require_once('init/options/option.php');

/* Custom for theme */
//echo get_stylesheet_directory_uri();

if(!is_admin()) {
  // Add scripts
  function ct_libs_scripts() {
    wp_register_script('lib-slick', get_stylesheet_directory_uri() . '/dist/js/libs/slick.js', array('jquery'), '0.7.0', TRUE);
    wp_enqueue_script('lib-slick');

    wp_register_script('lib-matchHeight', get_stylesheet_directory_uri() . '/dist/js/libs/jquery.matchHeight-min.js', array('jquery'), '0.7.0', TRUE);
    wp_enqueue_script('lib-matchHeight');

    wp_register_script('lib-mousewheel', get_stylesheet_directory_uri() . '/dist/js/libs/jquery.mousewheel.pack.js', array('jquery'), '3.1.3', TRUE);
    wp_enqueue_script('lib-mousewheel');

    wp_register_script('lib-fancybox', get_stylesheet_directory_uri() . '/dist/js/libs/jquery.fancybox.min.js', array('jquery'), '3.3.1', TRUE);
    wp_enqueue_script('lib-fancybox');
    
    wp_register_script('lib-parallax', get_stylesheet_directory_uri() . '/dist/js/libs/parallax.min.js', array('jquery'), '1.5.0', TRUE);
    wp_enqueue_script('lib-parallax');

    wp_register_script('lib-google-build-map', get_stylesheet_directory_uri() . '/dist/js/libs/jquery.google-build-map.js', array('jquery'), '1.0.0', TRUE);
    wp_enqueue_script('lib-google-build-map');
    
    wp_register_script('jquery-ui', get_stylesheet_directory_uri() . '/dist/js/libs/jquery-ui.js', array('jquery'), '1.12.1', TRUE);
    wp_enqueue_script('jquery-ui');
    
    wp_register_script('lib-waypoints', get_stylesheet_directory_uri() . '/dist/js/libs/waypoints.min.js', array('jquery'), '1.6.2', TRUE);
    wp_enqueue_script('lib-waypoints');
    
    wp_register_script('lib-counterup', get_stylesheet_directory_uri() . '/dist/js/libs/jquery.counterup.min.js', array('jquery'), '1.0', TRUE);
    wp_enqueue_script('lib-counterup');

    wp_register_script('script', get_stylesheet_directory_uri() . '/dist/js/script.js', '1.0.0', TRUE);
    wp_localize_script( 'script', 'customAjax', array( 'ajaxurl' => admin_url('admin-ajax.php' )));
    wp_enqueue_script('script');
  }
  add_action('wp_print_scripts', 'ct_libs_scripts');

  // Add stylesheet
  function ct_styles() {
    $styles = get_stylesheet_directory_uri() . '/dist/css/styles.css';
    $user_styles = get_stylesheet_directory_uri() . '/dist/css/user-custom.css';
    
    wp_register_style('theme-style', $styles, array(), '1.0', 'all');
    wp_enqueue_style('theme-style');

    wp_register_style('user-style', $styles, array(), '1.0', 'all');
    wp_enqueue_style('user-style');
  }
  add_action('wp_enqueue_scripts', 'ct_styles');
}

// Add admin script
function ct_admin_scripts() {
  wp_register_script('lib-moment', get_stylesheet_directory_uri() . '/dist/js/admin-libs/moment.js', array('jquery'), '2.13.0');
  wp_enqueue_script('lib-moment');

  wp_register_script('lib-datetimepicker', get_stylesheet_directory_uri() . '/dist/js/admin-libs/bootstrap-datetimepicker.min.js', array('jquery'), '4.17.37');
  wp_enqueue_script('lib-datetimepicker');

  wp_register_script('admin-script', get_stylesheet_directory_uri() . '/dist/js/admin-script.js', array('jquery'), '1.0.0');
  wp_enqueue_script('admin-script');
}
add_action('admin_init', 'ct_admin_scripts');

// Add admin script
function ct_admin_styles() {
  wp_register_style('admin-style', get_stylesheet_directory_uri() . '/dist/css/admin.css', array(), '1.0', 'all');
  wp_enqueue_style('admin-style');
}
add_action('admin_init', 'ct_admin_styles');

/*
 *
 * Add custom post type
 *
 */
function ffw_create_custom_post_types() {
  // Hotel
  register_post_type( '{CUSTOM-POST-TYPE}', array(
    'labels' => array(
      'name'               => _x( '{CUSTOM-POST-TYPE}s', 'post type general name', 'ffw_theme' ),
      'singular_name'      => _x( '{CUSTOM-POST-TYPE}', 'post type singular name', 'ffw_theme' ),
      'menu_name'          => _x( '{CUSTOM-POST-TYPE}s', 'admin menu', 'ffw_theme' ),
      'name_admin_bar'     => _x( '{CUSTOM-POST-TYPE}', 'add new on admin bar', 'ffw_theme' ),
      'add_new'            => _x( 'Add New', '{CUSTOM-POST-TYPE}', 'ffw_theme' ),
      'add_new_item'       => __( 'Add New {CUSTOM-POST-TYPE}', 'ffw_theme' ),
      'new_item'           => __( 'New {CUSTOM-POST-TYPE}', 'ffw_theme' ),
      'edit_item'          => __( 'Edit {CUSTOM-POST-TYPE}', 'ffw_theme' ),
      'view_item'          => __( 'View {CUSTOM-POST-TYPE}', 'ffw_theme' ),
      'all_items'          => __( 'All {CUSTOM-POST-TYPE}s', 'ffw_theme' ),
      'search_items'       => __( 'Search {CUSTOM-POST-TYPE}s', 'ffw_theme' ),
      'parent_item_colon'  => __( 'Parent {CUSTOM-POST-TYPE}s:', 'ffw_theme' ),
      'not_found'          => __( 'No {CUSTOM-POST-TYPE}s found.', 'ffw_theme' ),
      'not_found_in_trash' => __( 'No {CUSTOM-POST-TYPE}s found in Trash.', 'ffw_theme' )
    ),
    'description'           => __( 'Description.', 'ffw_theme' ),
    'public'                => true,
    'publicly_queryable'    => true,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'query_var'             => true,
    'rewrite'               => array('slug' => '{CUSTOM-POST-TYPE}'),
    'has_archive'           => true,
    'hierarchical'          => false,
    'menu_position'         => 28,
    'supports'              => array( 'title', 'editor' ),
    'capabilities'          => array(
      // Meta capabilities

      'edit_post'               => "edit_{CUSTOM-POST-TYPE}",
      'read_post'               => "read_{CUSTOM-POST-TYPE}",
      'delete_post'             => "delete_{CUSTOM-POST-TYPE}",

      'edit_posts'              => "edit_{CUSTOM-POST-TYPE}s",
      'edit_others_posts'       => "edit_others_{CUSTOM-POST-TYPE}s",
      'publish_posts'           => "publish_{CUSTOM-POST-TYPE}s",
      'read_private_posts'      => "read_private_{CUSTOM-POST-TYPE}s",

      // Primitive capabilities used within map_meta_cap():

      'read'                    => "read",
      'delete_posts'            => "delete_{CUSTOM-POST-TYPE}s",
      'delete_private_posts'    => "delete_private_{CUSTOM-POST-TYPE}s",
      'delete_published_posts'  => "delete_published_{CUSTOM-POST-TYPE}s",
      'delete_others_posts'     => "delete_others_{CUSTOM-POST-TYPE}s",
      'edit_private_posts'      => "edit_private_{CUSTOM-POST-TYPE}s",
      'edit_published_posts'    => "edit_published_{CUSTOM-POST-TYPE}s",
      'create_posts'            => "edit_{CUSTOM-POST-TYPE}s",
    ),
    // as pointed out by iEmanuele, adding map_meta_cap will map the meta correctly 
    'map_meta_cap' => true
  ));
}
//add_action( 'init', 'ffw_create_custom_post_types' );

/*
 *
 * Custom Taxonomy
 *
 */
function ffw_create_custom_taxonomy() {
  $labels_subsite = array(
    'name' => __('{CUSTOM-TAXONOMIES}', 'ffw_theme'),
    'singular' => __('{CUSTOM-TAXONOMY}', 'ffw_theme'),
    'menu_name' => __('{CUSTOM-TAXONOMY}', 'ffw_theme')
  );
  $args_subsite = array(
    'labels'                     => $labels_subsite,
    'hierarchical'               => true,
    'public'                     => true,
    'show_ui'                    => true,
    'show_admin_column'          => true,
    'show_in_nav_menus'          => true,
    'show_tagcloud'              => true,
    'show_in_quick_edit'         => false,
  );
  register_taxonomy('{CUSTOM_TAXONOMY}', array('{CUSTOM-POST-TYPE}'), $args_subsite);
}
//add_action( 'init', 'ffw_create_custom_taxonomy', 0 );

/*
 *
 *
 * Custom for theme
 *
 */
// Remove Editor Field for Landing page
function ffw_remove_editor() {
  remove_post_type_support('page', 'editor');
  //remove_post_type_support('post', 'editor');
}
//add_action('admin_init', 'ffw_remove_editor');

// Add google API Key
add_action('acf/init', function() {
  $theme_options = get_option('ffw_board_settings');
  $google_api_key = $theme_options['ffw_google_api_key'];
  acf_update_setting('google_api_key', $google_api_key);
});

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

function timber_set_product( $post ) {
  global $product;

  if ( is_woocommerce() ) {
    $product = wc_get_product( $post->ID );
  }
}

<?php
/**
 * Plugin Name: Vietnam autoload District by FFW
 * Plugin URI: http://ffw.com/
 * Description: Auto load District when select city of Vietnam
 * Version: 1.0
 * Author: FFW
 * Author URI: http://ffw.com/
 * License: GPLv2
 */

// Add Custom billing fields
add_filter('woocommerce_billing_fields', 'ffw_autoload_vietnam_city_district_woocommerce_billing_fields');
function ffw_autoload_vietnam_city_district_woocommerce_billing_fields($fields) {

  $fields['billing_district'] = array(
    'label' => __('District', 'woocommerce'), // Add custom field label
    'placeholder' => _x('Your District', 'placeholder', 'woocommerce'), // Add custom field placeholder
    'required' => true, // if field is required or not
    'clear' => false, // add clear or not
    'type' => 'text', // add field type
    'class' => array('form-row-wide', 'billing-district')    // add class name
  );

  return $fields;
}

// Add admin script
function ffw_autoload_vietnam_city_district_scripts() {
  wp_register_script('jquery-ffw-avcd', plugin_dir_url( __FILE__ ) . 'access/js/jquery-ffw-avcd.js', array(), '0.1', false);
  wp_localize_script( 'jquery-ffw-avcd', 'ffw_avcd_ajax', array( 'ajaxurl' => admin_url('admin-ajax.php' )));
  wp_enqueue_script('jquery-ffw-avcd');
}
add_action('wp_print_scripts', 'ffw_autoload_vietnam_city_district_scripts');

// Ajax callback Sity selected
add_action( 'wp_ajax_autoloadCity', 'ffw_autoload_vietnam_city_callback' );
add_action( 'wp_ajax_nopriv_autoloadCity', 'ffw_autoload_vietnam_city_callback' );
function ffw_autoload_vietnam_city_callback() {
  $vietnam_city_json = file_get_contents( __DIR__ . "/dist/tinh_tp.json");
  $vietnam_city_data = json_decode($vietnam_city_json, true);


  $values = $_REQUEST;
  $content = '';
  $content .= '<select class="billing_city_select">';
  if ( !isset($values['current_city']) ) {
    $content .= '<option value="" selected> -- ' . __('Select') . ' -- </option>';
  } else {
    $content .= '<option value=""> -- ' . __('Select') . ' -- </option>';
  }

  foreach ($vietnam_city_data as $key => $city) {
    if ( $values['current_city'] == $city['name'] ) {
      $content .= '<option value="' . $city['slug'] . '" data-city-code="' . $city['code'] . '" selected>' . $city['name'] . '</option>';
    } else {
      $content .= '<option value="' . $city['slug'] . '" data-city-code="' . $city['code'] . '">' . $city['name'] . '</option>';
    }
  }

  $content .= '</select>';

  $result = json_encode(array('markup' => $content, 'city_data' => $vietnam_city_data));
  echo $result;
  wp_die();
}

// Ajax callback District selected
add_action( 'wp_ajax_autoloadDistrict', 'ffw_autoload_vietnam_district_callback' );
add_action( 'wp_ajax_nopriv_autoloadDistrict', 'ffw_autoload_vietnam_district_callback' );
function ffw_autoload_vietnam_district_callback() {
  $values = $_REQUEST;

  if (isset($values['city_code'])) {
    $vietnam_district_json = file_get_contents( __DIR__ . "/dist/quan-huyen/" . $values['city_code'] . ".json");
  } else {
    $vietnam_district_json = file_get_contents( __DIR__ . "/dist/quan_huyen.json");
  }
  $vietnam_district_data = json_decode($vietnam_district_json, true);

  $content = '';
  $content .= '<select class="billing_district_select">';
  $content .= '<option value="" selected> -- ' . __('Select') . ' -- </option>';

  foreach ($vietnam_district_data as $key => $district) {
    $content .= '<option value="' . $district['slug'] . '" data-district-code="' . $district['code'] . '">' . $district['name'] . '</option>';
  }

  $content .= '</select>';

  $result = json_encode(array('markup' => $content, 'district_data' => $vietnam_district_data));
  echo $result;
  wp_die();
}


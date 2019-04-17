<?php
/**
 * Plugin Name: Custom product fields by Epic
 * Plugin URI: http://epic.vn/
 * Description: Custom product fields by Epic
 * Version: 1.0
 * Author: FFW
 * Author URI: http://epic.vn/
 * License: GPLv2
 */

add_action( 'admin_init', 'product_custom_field_has_woocommerce' );
function product_custom_field_has_woocommerce() {
  if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
    add_action( 'admin_notices', 'product_custom_field_notice' );

    deactivate_plugins( plugin_basename( __FILE__ ) ); 

    if ( isset( $_GET['activate'] ) ) {
      unset( $_GET['activate'] );
    }
  }
}

function product_custom_field_notice(){
  ?><div class="error"><p>Sorry, but Child Plugin requires the Parent plugin to be installed and active.</p></div><?php
}

if ( ! class_exists( 'WooCommerce' ) ) {
  define('WOOCOMMERCE_USE_CSS', false);
  // Display Fields
  add_action( 'woocommerce_product_options_general_product_data', 'woo_add_custom_general_fields' );
  function woo_add_custom_general_fields() {
    global $woocommerce, $post; 
    echo '<div class="options_group">';
     
    // Product code
    woocommerce_wp_text_input( 
      array( 
        'id'          => '_product_code', 
        'label'       => __( 'Product code', 'woocommerce' ), 
        'placeholder' => '',
        'desc_tip'    => 'true',
        'description' => __( 'Enter the product code here.', 'woocommerce' ) 
      )
    );
     
    // Product Branch
    woocommerce_wp_text_input( 
      array( 
        'id'          => '_product_branch', 
        'label'       => __( 'Product branch', 'woocommerce' ), 
        'placeholder' => '',
        'desc_tip'    => 'true',
        'description' => __( 'Enter the product branch here.', 'woocommerce' ) 
      )
    );
     
    // Product Branch
    woocommerce_wp_text_input( 
      array( 
        'id'          => '_product_origin', 
        'label'       => __( 'Product Origin', 'woocommerce' ), 
        'placeholder' => '',
        'desc_tip'    => 'true',
        'description' => __( 'Enter the product origin here.', 'woocommerce' ) 
      )
    );
     
    // Product Branch
    woocommerce_wp_text_input( 
      array( 
        'id'          => '_product_guarantee', 
        'label'       => __( 'Product guarantee', 'woocommerce' ), 
        'placeholder' => '',
        'desc_tip'    => 'true',
        'description' => __( 'Enter the product guarantee here.', 'woocommerce' ) 
      )
    );

    echo '</div>';   
  }

  // Save Fields
  add_action( 'woocommerce_process_product_meta', 'woo_add_custom_general_fields_save' );
  function woo_add_custom_general_fields_save( $post_id ){

    // Product code
    $woocommerce_product_code = $_POST['_product_code'];
    if( !empty( $woocommerce_product_code ) )
        update_post_meta( $post_id, '_product_code', esc_attr( $woocommerce_product_code ) );

    // Product branch
    $woocommerce_product_branch = $_POST['_product_branch'];
    if( !empty( $woocommerce_product_branch ) )
        update_post_meta( $post_id, '_product_branch', esc_attr( $woocommerce_product_branch ) );

    // Product branch
    $woocommerce_product_origin = $_POST['_product_origin'];
    if( !empty( $woocommerce_product_origin ) )
        update_post_meta( $post_id, '_product_origin', esc_attr( $woocommerce_product_origin ) );

    // Product branch
    $woocommerce_product_guarantee = $_POST['_product_guarantee'];
    if( !empty( $woocommerce_product_guarantee ) )
        update_post_meta( $post_id, '_product_guarantee', esc_attr( $woocommerce_product_guarantee ) );
         
    /*// Number Field
    $woocommerce_number_field = $_POST['_number_field'];
    if( !empty( $woocommerce_number_field ) )
        update_post_meta( $post_id, '_number_field', esc_attr( $woocommerce_number_field ) );
         
    // Textarea
    $woocommerce_textarea = $_POST['_textarea'];
    if( !empty( $woocommerce_textarea ) )
        update_post_meta( $post_id, '_textarea', esc_html( $woocommerce_textarea ) );
         
    // Select
    $woocommerce_select = $_POST['_select'];
    if( !empty( $woocommerce_select ) )
        update_post_meta( $post_id, '_select', esc_attr( $woocommerce_select ) );
         
    // Checkbox
    $woocommerce_checkbox = isset( $_POST['_checkbox'] ) ? 'yes' : 'no';
    update_post_meta( $post_id, '_checkbox', $woocommerce_checkbox );
     
    // Custom Field
    $custom_field_type =  array( esc_attr( $_POST['_field_one'] ), esc_attr( $_POST['_field_two'] ) );
    update_post_meta( $post_id, '_custom_field_type', $custom_field_type );
     
    // Hidden Field
    $woocommerce_hidden_field = $_POST['_hidden_field'];
    if( !empty( $woocommerce_hidden_field ) )
        update_post_meta( $post_id, '_hidden_field', esc_attr( $woocommerce_hidden_field ) );
         
    // Product Field Type
    $product_field_type =  $_POST['product_field_type'];
    update_post_meta( $post_id, '_product_field_type_ids', $product_field_type );*/
  }

  function product_custom_field_display_fields() {
    ?>
    <div class="product-information">
      <div class="product-information-item product-code">
        <label><?php echo pll_e('product code'); ?>:</label>
        <?php echo get_post_meta(get_the_ID(), "_product_code", true); ?>
      </div>
      <div class="product-information-item product-branch">
        <label><?php echo pll_e('product branch'); ?>:</label>
        <?php echo get_post_meta(get_the_ID(), "_product_branch", true); ?>
      </div>
      <div class="product-information-item product-origin">
        <label><?php echo pll_e('product origin'); ?>:</label>
        <?php echo get_post_meta(get_the_ID(), "_product_origin", true); ?>
      </div>
      <div class="product-information-item product-guarantee">
        <label><?php echo pll_e('product guarantee'); ?>:</label>
        <?php echo get_post_meta(get_the_ID(), "_product_guarantee", true); ?>
      </div>
    </div>
    <?php
  }
  add_action( 'woocommerce_single_product_summary', 'product_custom_field_display_fields', 6 );

  function product_custom_field_call_for_price() {
    return '<label>' . pll__('price') . ':</label>' . ' ' . pll__('Contact');
  }
  add_filter( 'woocommerce_get_price_html', 'product_custom_field_call_for_price' );
  add_filter( 'woocommerce_cart_item_price', 'product_custom_field_call_for_price' );
}

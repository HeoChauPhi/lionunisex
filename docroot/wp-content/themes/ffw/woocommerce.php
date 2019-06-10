<?php

global $woocommerce;
$context            = Timber::context();
$context['sidebar'] = Timber::get_widgets( 'shop-sidebar' );

if (get_option('woocommerce_cart_redirect_after_add')=='yes') {
  $context['minicart_class'] = 'show-cart';
} else {
  $context['minicart_class'] = 'hide-cart';
}

if ( is_singular( 'product' ) ) {
  add_action( 'woocommerce_single_product_summary', 'product_display_fields_after_title', 6 );
  remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
  remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
  //remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
  remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
  //add_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 11);

  $context['post']    = Timber::get_post();
  $product            = wc_get_product( $context['post']->ID );
  $context['product'] = $product;

  // Get related products
  $related_limit               = wc_get_loop_prop( 'columns' );
  $related_ids                 = wc_get_related_products( $context['post']->id, $related_limit );
  $context['related_products'] =  Timber::get_posts( $related_ids );

  // Restore the context and loop back to the main query loop.
  wp_reset_postdata();

  Timber::render( 'templates/woo/single-product.twig', $context );
} else {
  remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart');
  remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
  remove_action( 'woocommerce_before_shop_loop' , 'woocommerce_result_count', 20 );
  $page = new TimberPost(get_option( 'woocommerce_shop_page_id' ));
  $posts = Timber::get_posts();
  $context['post'] = $page;
  $context['products'] = $posts;
  $context['title'] = $page->title;

  if ( is_product_category() ) {
    $queried_object = get_queried_object();
    $term_id = $queried_object->term_id;
    $context['category'] = get_term( $term_id, 'product_cat' );
    $context['title'] = single_term_title( '', false );
  }

  Timber::render( 'templates/woo/archive-product.twig', $context );
}

function product_display_fields_after_title() {
  global $product;
  if ($product->get_sku()) {
    echo '<div class="product-sku"><label>' . pll__('SKU') . ':</label> ' . $product->get_sku() . '</div>';
  }
}

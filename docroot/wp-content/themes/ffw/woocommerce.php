<?php
$context            = Timber::context();
$context['sidebar'] = Timber::get_widgets( 'shop-sidebar' );

if ( is_singular( 'product' ) ) {
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

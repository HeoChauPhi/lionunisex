<?php
add_shortcode('share_buttons', 'share_buttons_render');
function share_buttons_render($attrs) {
  extract(shortcode_atts (array(
    'print_icon' => 1
  ), $attrs));

  ob_start();
    $context                = Timber::get_context();
    $context['permalink']   = urlencode(get_the_permalink());
    $context['title']       = urlencode(get_the_title());
    $context['print_icon']  = $print_icon;
    try {
    Timber::render( array( 'social-detail.twig'), $context );
    } catch (Exception $e) {
      echo 'Could not find a twig file for Shortcode Name: social-detail.twig';
    }
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
  wp_reset_postdata();
}

// --> Disable term format shortcode
add_shortcode( 'customtax', 'create_customtax' );
function create_customtax($attrs) {
  extract(shortcode_atts (array(
    'tax_name' => ''
  ), $attrs));
  ob_start();
    taxvalue($tax = $tax_name); // This function in theme-init.php
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}

// WC Mini cart
add_shortcode( 'custom-mini-cart', 'create_custom_mini_cart' );
function create_custom_mini_cart($attrs) {
  extract(shortcode_atts (array(), $attrs));
  ob_start();
    $context = Timber::get_context();
    $context['cart_count'] = WC()->cart->get_cart_contents_count();

    Timber::render( array( 'woo/mini-cart.twig'), $context );
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}

// --> Custom online payment
add_shortcode( 'custom_online_payment', 'create_custom_online_payment' );
function create_custom_online_payment($attrs) {
  extract(shortcode_atts (array(
    'title' => ''
  ), $attrs));
  ob_start();
    $context = Timber::get_context();

    // Range online count
    $context['online_title'] = $title;
    $context['online_count'] = rand(1500,2000);

    $args = array(
      'post_type'       => 'product',
      'posts_per_page'  => -1,
      'post_status'     => 'publish',
    );
    $posts = Timber::get_posts($args);
    $context['products'] = $posts;

    Timber::render( array( 'woo/block-online-payment.twig'), $context );
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}

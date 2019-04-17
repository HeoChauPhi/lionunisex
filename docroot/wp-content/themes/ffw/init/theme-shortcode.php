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

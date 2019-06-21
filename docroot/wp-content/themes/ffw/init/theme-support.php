<?php
/*
**
** Enable Function
**
*/

/*
** Ajax call back
*/
// Image gallery show more call back
add_action( 'wp_ajax_showgallery', 'showgallery_callback' );
add_action( 'wp_ajax_nopriv_showgallery', 'showgallery_callback' );
function showgallery_callback() {
  $content = '';
  $values = $_REQUEST;

  $gallery_id     = (int) $values['component_id'] - 1;
  $images_list    = get_field( 'group_component', (int) $values['current_post_id'] )[$gallery_id]['images_gallery'];
  $component_id   = (int) $values['component_id'];
  $counter        = (int) $values['image_count'] + 6;

  foreach ($images_list as $key => $item) {
    if ( ((int) $values['image_count'] <= ((int) $key + 1)) && (((int) $key + 1) < $counter) ) {
      $content .= '<div class="image-gallery-item col-md-4"><div class="image-gallery-item-inner">';
      $content .= '<div class="image-gallery-media" style="background-image: url(' . $item['url'] . ');" title="' . $item['name'] . '"><img src="' . $item['url'] . '" alt></div>';
      $content .= '<a class="icon-fancybox" data-fancybox="image-gallery-component-' . $component_id . '" href="' . $item['url'] . '"><i class="fas fa-expand-arrows-alt"></i></a>';
      $content .= '</div></div>';
    }
  }

  $result = json_encode(array('markup' => $content, 'counter' => $counter));
  echo $result;
  wp_die();
}


// menu
add_theme_support( 'menus' );
add_action('init', 'ffw_menu');
function ffw_menu() {
  register_nav_menus(array (
    'main'          => 'Main Menu',
    'footer'        => 'Footer Menu'
  ));
}

// Theme support custom logo
add_action( 'after_setup_theme', 'ffw_setup' );
function ffw_setup() {
  add_theme_support( 'custom-logo', array(
    'flex-width' => true,
  ) );
}

// Theme support custom logo
add_theme_support( 'post-thumbnails' );

add_action( 'init', 'ffw_remove_default_field' );
function ffw_remove_default_field() {
  remove_post_type_support( 'page', 'thumbnail' );
}

// Unset URL from comment form
add_filter( 'comment_form_fields', 'ffw_move_comment_form_below' );
function ffw_move_comment_form_below( $fields ) {
    $comment_field = $fields['comment'];
    unset( $fields['comment'] );
    $fields['comment'] = $comment_field;
    return $fields;
}

// Set per page on each page
add_action( 'pre_get_posts',  'ffw_set_posts_per_page'  );
function ffw_set_posts_per_page( $query ) {
  global $wp_the_query;
  if ( (!is_admin()) && ( $query === $wp_the_query ) && ( $query->is_archive() ) && (!is_woocommerce()) ) {
    $query->set( 'posts_per_page', 1 );
  }
  return $query;
}

add_filter( 'body_class', 'ffw_body_class' );
function ffw_body_class( $classes ) {
  return $classes;
}

add_filter('upload_mimes', 'ffw_theme_support_files_type', 1, 1);
function ffw_theme_support_files_type($mime_types){
  $mime_types['svg'] = 'image/svg+xml';
  return $mime_types;
}


/*
**
** Support Widget Layout
**
*/


/* Add Dynamic Siderbar */
if (function_exists('register_sidebar')) {
  // Define Sidebar
  register_sidebar(array(
    'name' => __('Sidebar'),
    'description' => __('Description for this widget-area...'),
    'id' => 'sidebar-1',
    'before_widget' => '<div id="%1$s" class="%2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3 class="widget-title">',
    'after_title' => '</h3>'
  ));
  // Define Header block
  register_sidebar(array(
    'name' => __('Header block'),
    'description' => __('Description for this widget-area...'),
    'id' => 'header-block',
    'before_widget' => '<div id="%1$s" class="%2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3>',
    'after_title' => '</h3>'
  ));
  // Define Footer
  register_sidebar(array(
    'name' => __('Footer block'),
    'description' => __('Description for this widget-area...'),
    'id' => 'footer-block',
    'before_widget' => '<div id="%1$s" class="%2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h3>',
    'after_title' => '</h3>'
  ));
}

// Add class by slug to widget
add_filter('dynamic_sidebar_params', 'add_classes_to__widget'); 
function add_classes_to__widget($params){
  $sidebar_id = 'sidebar-1';
  $sidebars_widgets = wp_get_sidebars_widgets();
  $widget_ids = $sidebars_widgets[$sidebar_id]; 

  foreach ($widget_ids as $id) {
    $wdgtvar = 'widget_'._get_widget_id_base( $id );
    $idvar = _get_widget_id_base( $id );
    $instance = get_option( $wdgtvar );
    $idbs = str_replace( $idvar.'-', '', $id );
    if (isset($instance[$idbs]['attribute'])) {
      $title = str_replace(' ', '-', strtolower($instance[$idbs]['attribute']));
    }
    else {
      $title = str_replace(' ', '-', strtolower($instance[$idbs]['title']));
    }

    $classe_to_add = 'woo-attribute-' . $title . ' '; // make sure you leave a space at the end
    $classe_to_add = 'class="'.$classe_to_add;
    if ($params[0]['widget_id'] == $id) {
      $params[0]['before_widget'] = str_replace('class="', $classe_to_add, $params[0]['before_widget']);
    }
  }

  return $params;
}

// Theme support get widget ID
function ffw_get_widget_id($widget_instance) {
  if ($widget_instance->number=="__i__"){
    echo "<p><strong>Widget ID is</strong>: Pls save the widget first!</p>"   ;
  } else {
    echo "<p><strong>Widget ID is: </strong>" .$widget_instance->id. "</p>";
  }
}
add_action('in_widget_form', 'ffw_get_widget_id');

// Sidebar widget arena
add_action( 'widgets_init', 'ffw_create_sidebar_Widget' );
function ffw_create_sidebar_Widget() {
  register_widget('sidebar_Widget');
}

class sidebar_Widget extends WP_Widget {
  public function __construct() {
    $widget_ops = array(
      'classname' => 'sidebar_widget',
      'description' => __( 'Sidebar widget.', 'ffw_theme'),
      'customize_selective_refresh' => true,
    );
    $control_ops = array( 'width' => 400, 'height' => 350 );
    parent::__construct( 'sidebar_widget', __( 'Sidebar Widget', 'ffw_theme' ), $widget_ops, $control_ops );
  }

  public function widget( $args, $instance ) {
    $title    = apply_filters( 'widget_title', $instance['title'] );
    echo $args['before_widget'];
    if ( $title ) {
      echo $args['before_title'] . $title . $args['after_title'];
    }
    echo $args['after_widget'];
  }

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    return $instance;
  }

  function form( $instance ) {
    $title      = esc_attr( $instance['title'] );
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    <?php
  }
}

// Header widget arena
add_action( 'widgets_init', 'ffw_create_header_Widget' );
function ffw_create_header_Widget() {
  register_widget('header_Widget');
}

class header_Widget extends WP_Widget {
  public function __construct() {
    $widget_ops = array(
      'classname' => 'header_widget',
      'description' => __( 'Custom widget.', 'ffw_theme'),
      'customize_selective_refresh' => true,
    );
    $control_ops = array( 'width' => 400, 'height' => 350 );
    parent::__construct( 'header_widget', __( 'Header Widget', 'ffw_theme' ), $widget_ops, $control_ops );
  }

  public function widget( $args, $instance ) {
    $title    = apply_filters( 'widget_title', $instance['title'] );
    echo $args['before_widget'];
    if ( $title ) {
      echo $args['before_title'] . $title . $args['after_title'];
    }
    echo $args['after_widget'];
  }

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    return $instance;
  }

  function form( $instance ) {
    $title      = esc_attr( $instance['title'] );
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    <?php
  }
}

// Footer widget arena
add_action( 'widgets_init', 'ffw_create_footer_Widget' );
function ffw_create_footer_Widget() {
  register_widget('footer_Widget');
}

class footer_Widget extends WP_Widget {
  public function __construct() {
    $widget_ops = array(
      'classname' => 'footer_Widget',
      'description' => __( 'Custom widget.', 'ffw_theme'),
      'customize_selective_refresh' => true,
    );
    $control_ops = array( 'width' => 400, 'height' => 350 );
    parent::__construct( 'footer_Widget', __( 'Footer Widget', 'ffw_theme' ), $widget_ops, $control_ops );
  }

  public function widget( $args, $instance ) {
    $title    = apply_filters( 'widget_title', $instance['title'] );
    echo $args['before_widget'];
    if ( $title ) {
      echo $args['before_title'] . $title . $args['after_title'];
    }
    echo $args['after_widget'];
  }

  function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    return $instance;
  }

  function form( $instance ) {
    $title      = esc_attr( $instance['title'] );
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    <?php
  }
}

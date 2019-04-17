<?php
$timber = new \Timber\Timber();

use Timber\Timber;
use Timber\Menu;

// load the theme's framework
require_once dirname( __FILE__ ) . '/theme-support.php';

// Get custom function template with Timber
Timber::$dirname = array('templates', 'templates/blocks', 'templates/shortcode', 'templates/pages', 'templates/layouts', 'templates/views', 'templates/forms');

// Theme support woocommerce
function ffw_theme_add_woocommerce_support() {
    add_theme_support( 'woocommerce' );
}
add_action( 'after_setup_theme', 'ffw_theme_add_woocommerce_support' );

/**
 *
 * View Related Post by Taxonomy.
 * @param type $custom_cat String slug of vocabulary.
 * @param type $showpost Int number post want show.
 *
 * @return type $loop_custom Object for post.
 *
 */
function related($custom_cat, $showpost = -1) {
  global $post;
  $argss = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'ids');
  $terms = wp_get_post_terms( $post->ID, $custom_cat, $argss );
  $myposts = array(
    'showposts' => $showpost,
    'post_type' => 'any',
    'post__not_in' => array($post->ID),
    'tax_query' => array(
      array(
      'taxonomy' => $custom_cat,
      'field' => 'term_id',
      'terms' => $terms
      )
    )
  );
  $loop_custom = Timber::get_posts($myposts);
  return $loop_custom;
}

/**
 *
 * View Related Post by Custom Fields.
 * @param type $post_type String slug of Post Type.
 * @param type $custom_field String Slug of Custom field.
 * @param type $showpost Int like post_per_page.
 *
 * @return type $related Object for post.
 *
 */
function related_by_acf_ref($postID, $custom_field, $showpost = -1) {
  global $post;

  if ($postID) {
    $post_id = $postID;
  } else {
    $post_id = $post->ID;
  }

  $obj_field = get_field($custom_field, $post_id);
  $post_type = get_post_type($post_id);

  $args_return = array(
    'numberposts'   => $showpost,
    'post_type'     => $post_type,
    'post__not_in'  => array($post_id),
    'meta_query'    => array(
      'relation'    => 'OR',
      array(
        'key'       => $custom_field,
        'value'     => $obj_field->ID,
        'compare'   => '=',
      )
    )
  );
  $related = Timber::get_posts($args_return);

  return $related;
}


/**
 *
 * Disable Dynamic Sidebar.
 * @param type $name String slug of Dynamic Sidebar.
 *
 * @return Dynamic Sidebar.
 *
 */
function sidebar($name) {
  if ( is_active_sidebar( $name ) ) {
    dynamic_sidebar($name);
  }
  return;
}

/**
 *
 * Disable shortcode.
 * @param type $name String shortcode form.
 *
 * @return Shortcode.
 *
 */
function shortcode($name) {
  echo do_shortcode($name);
  return;
}

/**
 *
 * ACF in Widget.
 * @param type $name String Slug of ACF field.
 * @param type $widgetid String Slug of Widget.
 *
 * @return type $acffield String, Array Value of ACF field in Widget.
 *
 */
function acfwidget($name, $widgetid) {
  if (get_field($name, 'widget_'.$widgetid)) {
    $acffield = get_field($name, 'widget_'.$widgetid);
    $acffield_count = count($acffield);
    //print_r($acffield);

    if ( !empty( $acffield ) ) {
      $index = 1;
      foreach ($acffield as $field) {
        $layout = $field['acf_fc_layout'];
        
        switch ($layout) {
          default:
            $field['index'] = $index;

            if ( strpos($field['component_class'], 'col-') === false ) {
              $field['layout'] = 12 / $acffield_count;
            }
            //print_r($field);
            try {
              Timber::render($layout . '.twig', $field);
            } catch (Exception $e) {
              echo 'Could not find a twig file for layout type: ' . $layout . '<br>';
            }
            break;
        }
        $index++;
      }
    }
  }

  return;
}

/**
 *
 * ACF Object Return.
 * @param type $name String Slug of ACF field.
 * @param type $object String Slug of Widget.
 *
 * @return Object of ACF fields.
 *
 */
function acfobject($name, $object) {
  $field = get_field_object($name);
  $field_object = $field[$object];
  if (is_array($field_object)) {
    return $field_object;
  } else {
    echo $field_object;
  }
  return;
}

/**
 *
 * View List Taxonomy.
 * @param type $tax String Slug of Taxonomy.
 *
 * @return HTML Template for all term of taxonomy.
 *
 */
function taxvalue($tax) {
  $args = array(
    'parent' => 0,
    'orderby' => 'slug',
    'hide_empty' => false
  );

  $terms = get_terms( $tax, $args);
  if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
    echo '<ul class="listcat listcat-'.$tax.'">';
    foreach ( $terms as $term ) {
      $subterms1 = get_terms($tax, array('parent' => $term->term_id, 'orderby' => 'slug', 'hide_empty' => false));

      if (sizeof($subterms1) > 0) {
        echo '<li class="listcat-item"><a href="'.esc_url( get_term_link( $term ) ).'">' . $term->name . '</a>';

        // sub term 1
        echo '<ul class="subterm">';
          foreach ($subterms1 as $term) {
            $subterms2 = get_terms($tax, array('parent' => $term->term_id, 'orderby' => 'slug', 'hide_empty' => false));

            if (sizeof($subterms2) > 0) {
              echo '<li class="listcat-item has-subterm"><a href="'.esc_url( get_term_link( $term ) ).'">' . $term->name . '</a>';

              // sub term 2
              echo '<ul class="subterm">';
              foreach ($subterms2 as $term) {
                echo '<li class="listcat-item"><a href="'.esc_url( get_term_link( $term ) ).'">' . $term->name . '</a></li>';
              }
              echo '</ul></li>';
            } else {
              echo '<li class="listcat-item"><a href="'.esc_url( get_term_link( $term ) ).'">' . $term->name . '</a></li>';
            }
          }
        echo '</ul></li>';
      } else {
        echo '<li class="listcat-item"><a href="'.esc_url( get_term_link( $term ) ).'">' . $term->name . '</a></li>';
      }
    }
    echo '</ul>';
  }
}

/**
 *
 * Get Term name.
 * @param type $slug String Slug of Term.
 * @param type $tax String Slug of Taxonomy.
 *
 * @return type $term_name String Name of the term.
 *
 */
function get_term_name($slug, $tax){
  $term = get_term_by('slug', $slug, $tax);
  $term_name = array(
    array(
      'name' => $term->name,
      'slug' => $term->slug,
      'link' => esc_url( get_term_link( $term ) ),
    )
  );
  return $term_name;
}

/**
 *
 * Get Term By Post ID.
 * @param type $post_id Int ID of post.
 * @param type $tax_slug Sring Slug of Taxonomy.
 *
 * @return type $terms Array, Object all terms.
 *
 */
function get_post_terms($post_id, $tax_slug, $return = 'all'){
  $terms = wp_get_post_terms($post_id, $tax_slug, array( 'fields' => $return ));

  return $terms;
}

/**
 *
 * Get Avatar Author.
 * @param type $size String (150x150) Size of Image.
 *
 * @return type $avatar String URL for Avatar Author.
 *
 */
function avatar_author($size = '') {
  $avatar = get_avatar( get_the_author_meta( 'ID' ), $size );
  return $avatar;
}

/**
 *
 * Get Post Link.
 * @param type $id Int Post ID.
 *
 * @return type $post_link String URL for Post.
 *
 */
function get_post_link($id) {
  $post_link = get_post_permalink($id);
  return $post_link;
}

/**
 *
 * Custom ACF Field Object Post.
 * @param type $field Object fields ACF on flexible_content Function.
 * @param type $type String Slug of list_type field.
 * @param type $post_obj String Slug of object_post field.
 * @param type $post_type String Slug of Post Type need get.
 * @param type $taxonomy String Slug of Taxonomy need get.
 *
 * @return type $field Object push list Post object into $field.
 *
 */
function acf_return_item($field, $type = 0, $post_obj, $post_type, $taxonomy = '') {
  if($type) {
    $list_type = $field[$type];
  } else {
    $list_type == 0;
  }

  if($list_type == 0){ // Type = Custom
    $ids = array();
    $acf_post_obj = $field[$post_obj];
    foreach ($acf_post_obj as $value) {
      array_push($ids, $value->ID);
    }

    $posts = Timber::get_posts($ids);
    $field['return_items'] = $posts;

  } else { // Type = Latest
    $custom_post = explode(", ",$post_type);
    $args = array(
      'post_type'       => $custom_post,
      'posts_per_page'  => 5,
      'post_status'     => 'publish',
    );
    if (!empty($taxonomy) && $field[$taxonomy]) {
      $args['tax_query'] = array(
        'relation'  => 'AND',
        array(
          'taxonomy'  => $field[$taxonomy]->taxonomy,
          'field'     => 'term_id',
          'terms'     => array($field[$taxonomy]->term_id)
        ),
      );
    }

    $posts = Timber::get_posts($args);
    $field['return_items'] = $posts;
  }

  return $field;
}

/**
 *
 * ACF Flexible Content Fielld.
 * @param type $name String Slud ACF flexible_content field.
 *
 * @return Array all sub_fields in flexible_content field.
 *
 */
function flexible_content($name) {
  $fc_type = array();

  global $post;
  $fc = get_field( $name, $post->ID );
  $fc_ob = get_field_object( $name, $post->ID );

  if ( !empty( $fc ) ) {
    foreach ($fc as $key => $field) {
      $layout = $field['acf_fc_layout'];
      $fc_type[$layout] = array();
      $field['component_id'] = $key + 1;

      switch ($layout) {
        case 'test':
          print_r($field);

          try {
            Timber::render($layout . '.twig', $field);
          } catch (Exception $e) {
            echo 'Could not find a twig file for layout type: ' . $layout . '<br>';
          }
          break;

        case 'map_block':
          $theme_options = get_option('ffw_board_settings');
          $google_api_key = $theme_options['ffw_google_api_key'];

          $field['google_api_key'] = $google_api_key;

          try {
            Timber::render($layout . '.twig', $field);
          } catch (Exception $e) {
            echo 'Could not find a twig file for layout type: ' . $layout . '<br>';
          }
          break;
        default:
          //print_r($field);
          try {
            Timber::render($layout . '.twig', $field);
          } catch (Exception $e) {
            echo 'Could not find a twig file for layout type: ' . $layout . '<br>';
          }
      }
    }
  }

  return;
}

/**
 *
 * Preg Match URL.
 * @param type $field String URL Youtube, Vimeo.
 *
 * @return type $src.
 *
 */
function preg_match_url($field, $extend = '') {
  preg_match('/src="(.+?)"/', $field, $matches);
  $full_src = $matches[1];
  $src = explode("?", $full_src);

  if($extend) {
    $src = $src[0] . $extend;
  } else {
    $src = $src[0];
  }
  return $src;
}

/**
 *
 * Get ID from oEmbed.
 * @param type $url String HTML Iframe video.
 *
 * @return type $result String ID video from frame.
 *
 */
function get_id_embed($url) {
  $arrContextOptions=array(
    "ssl"=>array(
      "verify_peer"=>false,
      "verify_peer_name"=>false,
    ),
  ); 
  
  $video_url = preg_match_url($url);
  $parsed = parse_url($video_url);

  if (strpos($parsed['host'], 'youtube') !== false) {
    $pattern =
        '%^# Match any youtube URL
        (?:https?://)?  # Optional scheme. Either http or https
        (?:www\.)?      # Optional www subdomain
        (?:             # Group host alternatives
          youtu\.be/    # Either youtu.be,
        | youtube\.com  # or youtube.com
          (?:           # Group path alternatives
            /embed/     # Either /embed/
          | /v/         # or /v/
          | /watch\?v=  # or /watch\?v=
          )             # End path alternatives.
        )               # End host alternatives.
        ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
        $%x'
        ;

    $result       = preg_match($pattern, $video_url, $matches);

    $video_type   = 'youtube';
    $video_id     = $matches[1];
    $json_code    = file_get_contents('https://www.youtube.com/oembed?url=http://www.youtube.com/watch?v=' . $video_id . '&format=json', false, stream_context_create($arrContextOptions));
    $video_thumb  = json_decode($json_code)->thumbnail_url;
  } else {
    $pattern = '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([‌​0-9]{6,11})[?]?.*/';
    $result = preg_match($pattern, $video_url, $matches);

    $video_type   = 'vimeo';
    $video_id     = $matches[5];
    $json_code    = file_get_contents('http://vimeo.com/api/v2/video/' . $video_id . '.json', false, stream_context_create($arrContextOptions));
    $video_thumb  = json_decode($json_code)[0]->thumbnail_large;
  }

  if ($video_id) {
    return array(
      'video_type'  => $video_type,
      'video_id'    => $video_id,
      'video_thumb' => $video_thumb
    );
  }
  return false;
}

/**
 *
 * Get ID from Youtube URL.
 * @param type $url String Youtube URL.
 *
 * @return type $result String ID from Youtube URL.
 *
 */
function get_id_youtube($url) {
  $video_id = $url;
  $pattern =
        '%^# Match any youtube URL
        (?:https?://)?  # Optional scheme. Either http or https
        (?:www\.)?      # Optional www subdomain
        (?:             # Group host alternatives
          youtu\.be/    # Either youtu.be,
        | youtube\.com  # or youtube.com
          (?:           # Group path alternatives
            /embed/     # Either /embed/
          | /v/         # or /v/
          | /watch\?v=  # or /watch\?v=
          )             # End path alternatives.
        )               # End host alternatives.
        ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
        $%x'
        ;
  $result = preg_match($pattern, $video_id, $matches);
  if ($result) {
    return $matches[1];
  }
  return false;
}

/**
 *
 * Remove value from array by key.
 * @param type $arr Array need remove value.
 * @param type $key String of Key need remove.
 *
 * @return type $arr Array after unset.
 *
 */
function array_unset($arr, $key) {
  unset($arr[$key]);
  
  return $arr;
}

/**
 *
 * Convert String to Number
 * @param type $number String need convert.
 *
 * @return type $int number after convert.
 *
 */
function int($number) {  
  $int = (int)$number;

  return $int;
}

/**
 * My custom Twig functionality.
 *
 * @param Twig_Environment $twig
 * @return $twig
 */
add_filter( 'timber/twig', 'ffw_twig_functions');
function ffw_twig_functions( \Twig_Environment $twig ) {
  $twig->addFunction( new Twig_Function( 'pll_e', 'pll_e' ) );
  $twig->addFunction( new Twig_Function( 'pll_', 'pll_' ) );

  return $twig;
}

/**
 *
 * Add data value into Timber.
 *
 * @return type $data String, Object, Array Global value in Timber template.
 *
 */
add_filter('timber_context', 'ffw_twig_data');
function ffw_twig_data($data){
  // Theme setting
  $logo = get_template_directory_uri().'/dist/images/logo.png';
  $logo_scroll = $logo;
  $favicon = get_template_directory_uri().'/dist/images/favicon.ico';

  $data['site_logo_scroll'] = new TimberImage($logo_scroll);
  $data['site_favicon'] = new TimberImage($favicon);
  
  if (has_custom_logo()) {
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    $custom_logo_attachment = wp_get_attachment_image_src( $custom_logo_id , 'full' );
    $custom_logo = $custom_logo_attachment[0];

    $data['site_logo'] = new TimberImage($custom_logo);
  } else {
    $data['site_logo'] = new TimberImage($logo);
  }

  // menu
  $data['menu']['main'] = new TimberMenu('main');
  $data['menu']['footer'] = new TimberMenu('footer');

  // Dynamic Sidebar
  $widget_data_sidebar = get_option('widget_sidebar_Widget');
  $widget_data_header = get_option('widget_header_Widget');
  $widget_data_footer = get_option('widget_footer_Widget');
  $data['sidebar_widget'] = $widget_data_sidebar;
  $data['header_widget'] = $widget_data_header;
  $data['footer_widget'] = $widget_data_footer;

  // Theme option
  $theme_options                = get_option('ffw_board_settings');
  $google_api_key               = $theme_options['ffw_google_api_key'];

  $data['google_api_key']       = $google_api_key;

  // Get PPL Plugin
  if ( !empty($GLOBALS["polylang"]) ) {
    $data['current_lang'] = pll_current_language();
    $data['global_ppl'] = $GLOBALS["polylang"]->model->get_languages_list();

    foreach ($data['global_ppl'] as $key => $value) {
      if ( $value->slug == pll_current_language() ) {
        $data['current_lang_flag'] = $value->flag;
      }
    }
  }
  
  // $data['pll_e'] = pll_e();
  // $data['pll_'] = pll_();

  return $data;
}

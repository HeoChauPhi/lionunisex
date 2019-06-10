/*jslint browser: true*/
/*global $, jQuery, Modernizr, enquire, audiojs*/

(function($) {
  var iScrollPos = 0;

  // Swich when web loading on mobile or small device
  function mobileMenu() {
    var offset = $(this).offset();
    var rt = ($(window).width() - (offset.left + $(this).outerWidth()));

    if( $(this).hasClass('menu-responsive') ) {
      $(this).toggleClass('active');
      if ( $(this).hasClass('active') ) {
        $(this).css({
          'position': 'fixed',
          'top': offset.top,
          'right': rt
        });
      } else {
        $(this).css({
          'position': 'absolute',
          'top': 0,
          'right': 0
        });
      }
      $('.block-navigation').toggleClass('menu-show');
    }
  }

  // Back to Top
  function backToTopShow() {
    var height_show = $('.header-full').outerHeight(true);

    if ($(this).scrollTop() > height_show) {
      $('.js-back-top').addClass('btn-show');
    } else {
      $('.js-back-top').removeClass('btn-show');
    }
  }

  function backToTop() {
    $("html, body").animate({ scrollTop: 0 }, 600);
  }

  // Scroll Down
  function scrollDown() {
    var height_scroll = $('.header-full').outerHeight(true);

    $("html, body").animate({ scrollTop: height_scroll }, 600);
  }

  // Show & hide cart
  function showCart() {
    $(this).next('.dropdown-mini-cart').toggleClass('cart-show');
    return false;
  }

  function hideCart() {
    $(this).parents('.dropdown-mini-cart').removeClass('cart-show');
    return false;
  }

  // Counter up
  function counterUp() {
    $('.js-count-up').counterUp({
      delay: 5,
      time: 500
    });
  }

  /*
   * ================================
   * Animate block when scroll window
   * ================================
  */
  function blockAnimateScroll() {
    var $animation_elements = $('.animation-element');
    var $window = $(window);

    function check_if_in_view() {
      var window_height = $window.height();
      var window_top_position = $window.scrollTop();
      var window_bottom_position = (window_top_position + window_height);
     
      $.each($animation_elements, function() {
        var $element = $(this);
        var element_height = $element.outerHeight();
        var element_top_position = $element.offset().top;
        var element_bottom_position = (element_top_position + element_height);
        if ($element.data('animate')) {
          var animate_name = $element.data('animate');
        } else {
          var animate_name = 'fadeIn';
        }
     
        //check to see if this current container is within viewport
        if ((element_bottom_position >= window_top_position) &&
            (element_top_position <= window_bottom_position)) {
          $element.addClass('animated' + ' ' + animate_name);
        } else {
          $element.removeClass('animated' + ' ' +  animate_name);
        }
      });
    }

    $window.on('scroll resize', check_if_in_view);
    $window.trigger('scroll');
  }

  /*
   * ===============================================
   * Slick slider function.
   * @param $name type String Class wrapper of slide
   * @param $item type Number item to show 
   * ===============================================
  */
  function jcarousel_slider($name, $item) {
    if ($item < 4) {
      $($name).slick({
        adaptiveHeight: true,
        arrows: false,
        dots: false,
        fade: true,
        infinite: true,
        slidesToShow: 1,
        autoplay: true,
        autoplaySpeed: 3500,
      });
    } else {
      $($name).slick({
        infinite: true,
        slidesToShow: $item,
        slidesToScroll: 1,
        responsive: [
          {
            breakpoint: 1024,
            settings: {
              slidesToShow: 3
            }
          },
          {
            breakpoint: 892,
            settings: {
              slidesToShow: 2
            }
          },
          {
            breakpoint: 568,
            settings: {
              slidesToShow: 1
            }
          }
        ]
      });
    }
  }

  // Block Image gallery show more items
  function imageGalleryShowMore() {
    var this_element      = $(this);
    var data_gallery      = this_element.parent('.show-more').next('.data-hidden');
    var image_total       = data_gallery.find('input[name="totalimages"]').val();
    var image_count       = data_gallery.find('input[name="count"]').val();
    var current_post_id   = data_gallery.find('input[name="postid"]').val();
    var component_id      = data_gallery.find('input[name="componentid"]').val();
    
    $.ajax({
      type : "post",
      dataType : "json",
      url : customAjax.ajaxurl,
      data : {action: "showgallery", current_post_id: current_post_id, image_count: image_count, component_id: component_id},
      beforeSend: function() {
        this_element.parent('.show-more').after('<span class="ajax-loader"><span class="ajax-icon"></span></span>');
      },
      success: function(response) {
        this_element.parent('.show-more').prev('.image-result').append(response.markup);
        this_element.parent('.show-more').next('.ajax-loader').remove();
        this_element.parent('.show-more').next('.data-hidden').find('input[name="count"]').val(response.counter);
        if (parseInt(response.counter) > parseInt(image_total) + 1) {
          this_element.parent('.show-more').remove();
        }
      },
      error: function(response) {
      }
    });

    return false;
  }

  /* ==================================================================
   *
   * Loading Jquery
   *
   ================================================================== */
  $(document).ready(function() {
    // Call to function

    // Set time popup enable
    if(!$.cookie('banner_popup_cookie') && $('#popup-banner-content').length > 0) {
      $.fancybox.open({
        src: '#popup-banner-content',
        opts : {
          beforeClose : function( instance, current ) {
            var date = new Date();
            date.setTime(date.getTime() + (3600 * 1000));
            $.cookie('banner_popup_cookie', 'close', { expires: date, path: '/' });
          }
        }
      });
    }

    $('.menu-responsive').on('click', mobileMenu);
    $('.js-back-top').on('click', backToTop);
    $('.js-scroll-down').on('click', scrollDown);
    $('.js-show-cart').on('click', showCart);
    $('.mini-cart-close, .dropdown-mini-cart .layer-fixed').on('click', hideCart);
    $( ".js-accordion" ).accordion({
      heightStyle: "content",
      header: '> .accordion-item > .accordion-title'
    });
    $('.woocommerce-product-gallery__image > a').on('click', function() {
      $.fancybox.open( $('.woocommerce-product-gallery__image > a'), {
        touch: false,
        infobar: false
      });
      return false;
    });

    // Color hover filter
    $('.woo-attribute-color .woocommerce-widget-layered-nav-list__item > a').mouseenter(function() {
      var color_name = $(this).text();
      $(this).parents('.woo-attribute-color').find('> .widget-title').append('<span class="color-name-attribute">: ' + color_name + '</span>');
    })
    .mouseleave(function() {
      $('.color-name-attribute').remove();
    });
    
    jcarousel_slider('.user-payment-sider', 1);
    $('.block-gallery .images-gallery .show-more-link').on('click', imageGalleryShowMore);
  });

  $(window).scroll(function() {
    backToTopShow();
    //blockAnimateScroll()
  });

  $(window).load(function() {
    // Call to function
    blockAnimateScroll();
    if ( $('.woocommerce-notices-wrapper').find('.success-added-products').length ){
      $('.js-show-cart').trigger('click');
    }
  });

  $(window).resize(function() {
    // Call to function
  });

})(jQuery);
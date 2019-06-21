/*jslint browser: true*/
/*global $, jQuery, Modernizr, enquire, audiojs*/

(function($) {
  var iScrollPos = 0;

  /*
   * ===============================
   * Header fixed when scroll window
   * ===============================
  */
  function headerFixed() {
    var scroll_top = $(window).scrollTop();

    if (scroll_top > 0) {
      $('#header').addClass('header-fixed').css({'position': 'fixed', 'left': 0, 'right': 0});
    } else {
      $('#header').removeClass('header-fixed').css({'position': 'static'});
    }
  }

  function mainPadding() {
    var height_header = $('#header').outerHeight();

    $('main').css('padding-top', height_header);
  }

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

  // Show & hide login form
  function loginForm() {
    $(this).next('.user-login-content').slideToggle();
    return false;
  }

  // Show & hide filter
  function showFilter() {
    $('#sidebar').toggleClass('side-filter-show');
    return false;
  }

  function hideFilter() {
    $('#sidebar').removeClass('side-filter-show');
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

  // Block instagram show more items
  function imageInstagramShowMore() {
    var link_text_showmore = $(this).data('showmore');
    var link_text_showless = $(this).data('showless');
    $(this).toggleClass('link-show-less');
    $(this).parents('.images-gallery').find('.image-gallery-item').each(function() {
      if ( $(this).hasClass('item-show-more') ) {
        $(this).removeClass('item-show-more').addClass('item-show-less');
      } else if ( $(this).hasClass('item-show-less') ) {
        $(this).removeClass('item-show-less').addClass('item-show-more');
      }
    });
    
    if ( $(this).hasClass('link-show-less') ) {
      $(this).text(link_text_showless);
    } else {
      $(this).text(link_text_showmore);
    }
    return false;
  }

  // Custom user payment notice
  function randomIntFromInterval(min,max) {
    return Math.ceil(Math.random()*(max-min+1)+min);
  }

  function paymentNotice() {
    var leng_payments = $('.block-payment-popup').data('length');
    setInterval(function() {
      var user_id = randomIntFromInterval(100, 500);
      var random_item = Math.ceil(Math.random()*leng_payments) - 1;
      $('.block-payment-popup .user-payment-item').removeClass('payment-popup-show');
      $('.block-payment-popup .user-payment-item:eq(' + random_item + ')').addClass('payment-popup-show');
      $('.block-payment-popup .user-payment-item.payment-popup-show').find('.user-payment-id').text(user_id);
      setTimeout(function() {
        $('.block-payment-popup .user-payment-item').removeClass('payment-popup-show');
      }, 3000);
    }, 100000);
    $('.block-payment-popup .user-payment-item .close-popup-payment-popup').on('click', function() {
      $('.block-payment-popup .user-payment-item').removeClass('payment-popup-show');
      return false;
    });
  }

  // Remove woocommerce notice
  function wcNoticeRemove() {
    if ( $('.woocommerce-notices-wrapper *').length ) {
      $('.woocommerce-notices-wrapper').append('<span class="remove-wc-notice"><i class="fas fa-chevron-down"></i></span>');
    }
    if ( $('.woocommerce-form-coupon-toggle*').length ) {
      $('.woocommerce-form-coupon-toggle').append('<span class="remove-wc-notice"><i class="fas fa-chevron-down"></i></span>');
    }
    if ( $('.woocommerce-NoticeGroup *').length ) {
      $('.woocommerce-NoticeGroup').append('<span class="remove-wc-notice"><i class="fas fa-chevron-down"></i></span>');
    }

    $('.remove-wc-notice').on('click', function() {
      $(this).parent().toggleClass('wc-notice-hide');
    });
  }

  // WOOF custom
  function priceFilterToggle() {
    $('.woof_price_filter .woof_container_inner .widget_price_filter h4').append('<a href="javascript: void(0);" title="toggle" class="woof_front_toggle woof_front_toggle_closed" data-condition="closed">-</a>');
    $('.woof_price_filter .woof_container_inner .widget_price_filter > form').addClass('woof_block_html_items');
  }

  // Color product detail
  function selectToList($select) {
    $($select).each(function () {
      var $this = $(this),
          $name = $(this).attr('name'),
          $attr_name = $(this).data('attribute_name'),
          $list = $('<div class="form-color-wrapper"></div>');
      
      $this.find('option').each(function() {
        if ( $(this).hasClass('attached') ) {
          $list.append('<input id="' + $name + '-' + $(this).val() + '" class="color-attribute" type="radio" name="' + $name + '" value="' + $(this).val() + '" data-attribute_name="' + $attr_name + '" data-text="' + $(this).text() + '" /><label class="label-color-attribute" for="' + $name + '-' + $(this).val() + '" style="background-color: ' + $(this).val() + ';">' + $(this).text() + '</label>');
        }
      });

      $this.after( $list );
    });
      
    $('.color-attribute').change(function() {
      $($select).val($(this).val()).trigger("change");
      $('label.label-color-attribute').removeClass('label-color-attribute-checked');
      $('label[for="' + $(this).attr('id') + '"]').addClass('label-color-attribute-checked');
      $('table.variations .color-name-attribute').remove();
      $(this).parents('td.value').prev('td.label').find('label').append('<span class="color-name-attribute">: ' + $(this).data('text') + '</span>');
    });
  }

  // Cart and Checkout page
  function showCoupon() {
    $(this).parent('.coupon').toggleClass('coupon-show-form');
  }

  function cartPageAutoUpdate() {
    $('.woocommerce-cart-form .product-quantity input.qty').on('input', function() {
      setTimeout(function() {
        $('.woocommerce-cart-form .shop_table button[name="update_cart"]').trigger('click');
      }, 100);
    });
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
    $('.block-user-login .user-login-icon').on('click', loginForm);
    $('.mini-cart-close, .dropdown-mini-cart .layer-fixed').on('click', hideCart);
    $('.sidebar-mobile-filter').on('click', showFilter);
    $('.page-product-list #sidebar .WOOF_Widget .widget-title').on('click', hideFilter);
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
    $('.woo-attribute-color .woocommerce-widget-layered-nav-list__item > a, .woof_container_pa_color .woof_list_checkbox li .woof_checkbox_label').mouseenter(function() {
      var color_name = $(this).text();
      $(this).parents('.woo-attribute-color, .woof_container_pa_color').find('> .widget-title, .woof_container_inner > h4').append('<span class="color-name-attribute">: ' + color_name + '</span>');
    })
    .mouseleave(function() {
      $('.color-name-attribute').remove();
    });
    $('.woof_container_pa_color .woof_list_checkbox li').each(function() {
      var color_val = $(this).find('input[type="checkbox"]').attr('name');
      $(this).find('.woof_checkbox_label').css('background-color', color_val);
    });
    
    jcarousel_slider('.user-payment-sider', 1);
    $('.block-gallery .images-gallery .show-more-link').on('click', imageGalleryShowMore);
    $('.block-instagram .images-gallery .instagram-show-more-link').on('click', imageInstagramShowMore);

    $( ".price_slider" ).on( "slidechange", function( event, ui ) {
      $('.price_slider_amount button').trigger('click');
    });
    priceFilterToggle();

    // Product detail
    $('.reset_variations').on('click', function() {
      $('.color-attribute').prop('checked', false);
      $('table.variations .color-name-attribute').remove();
      $('label.label-color-attribute').removeClass('label-color-attribute-checked');
    });

    // Custom user payment notice
    paymentNotice();

    // Cart and Checkout page
    $('.woocommerce table.shop_table.cart .coupon label').on('click', showCoupon);

    // Cart page auto update
    cartPageAutoUpdate();
  });

  $(window).scroll(function() {
    backToTopShow();
    //blockAnimateScroll()
  });

  $(window).load(function() {
    // Call to function
    mainPadding();
    $('.woocommerce table.shop_table th.product-thumbnail').empty();
    $('.cart-form-title').clone().removeClass('hidden').appendTo('.woocommerce table.shop_table th.product-thumbnail');
    blockAnimateScroll();
    if ( $('.woocommerce-notices-wrapper').find('.success-added-products').length ){
      $('.js-show-cart').trigger('click');
      wcNoticeRemove();
    }
    selectToList('select#pa_color');
  });

  $(window).resize(function() {
    // Call to function
    mainPadding();
  });

  $( document ).ajaxStop(function() {
    // Call to function
    $('.cart-form-title').clone().removeClass('hidden').appendTo('.woocommerce table.shop_table th.product-thumbnail');
    wcNoticeRemove();
    // Cart page auto update
    cartPageAutoUpdate();
  });

})(jQuery);
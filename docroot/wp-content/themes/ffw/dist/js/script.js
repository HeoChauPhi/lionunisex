/*jslint browser: true*/
/*global $, jQuery, Modernizr, enquire, audiojs*/

(function($) {
  var iScrollPos = 0;

  // Swich when web loading on mobile or small device
  function mobileMenu() {
    if( $(this).hasClass('menu-responsive') ) {
      //$('.user-menu-responsive').removeClass('active');
      //$('.block-user-menu').removeClass('menu-show');
      $(this).toggleClass('active');
      $('.main-menu:not(.menu-scroll)').toggleClass('menu-show');
    }
    /*if( $(this).hasClass('user-menu-responsive') ) {
      $('.menu-responsive').removeClass('active');
      $('.main-menu').removeClass('menu-show');
      $(this).toggleClass('active');
      $('.block-user-menu').toggleClass('menu-show');
    }*/
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

  // Show cart
  function showCart() {
    $(this).toggleClass('active');
    $(this).next('.dropdown-mini-cart').slideToggle();
    return false;
  }

  // Counter up
  function counterUp() {
    $('.js-count-up').counterUp({
      delay: 5,
      time: 500
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
            console.log($.cookie('banner_popup_cookie'));
          }
        }
      });
    }

    //$('.js-toogle--menu').on('click', mobileMenu);
    $('.js-back-top').on('click', backToTop);
    $('.js-scroll-down').on('click', scrollDown);
    $('.js-show-cart').on('click', showCart);
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
  });

  $(window).scroll(function() {
    backToTopShow();
  });

  $(window).load(function() {
    // Call to function
  });

  $(window).resize(function() {
    // Call to function
  });

})(jQuery);
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
    //$('.js-toogle--menu').on('click', mobileMenu);
    $('.js-back-top').on('click', backToTop);
    $('.js-scroll-down').on('click', scrollDown);
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
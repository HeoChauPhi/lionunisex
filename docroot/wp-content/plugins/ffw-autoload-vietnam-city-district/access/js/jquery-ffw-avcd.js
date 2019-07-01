(function($) {
  function testFunction() {
    alert('ok select');
  }

  // Block Image gallery show more items
  function autoloadCity() {
    var current_city = $('#billing_city').val();
    $('#billing_city').attr('type', 'hidden');
    
    $.ajax({
      type : "post",
      dataType : "json",
      url : ffw_avcd_ajax.ajaxurl,
      data : {action: "autoloadCity", current_city: current_city},
      beforeSend: function() {
      },
      success: function(response) {
        $('#billing_city').after(response.markup);
        autoloadDistrictDefault();

        $('.billing_city_select').on('change', autoloadDistrict);

        //console.log(response.city_data);
      },
      error: function(response) {
        console.log(response);
      }
    });
  }

  function autoloadDistrict() {
    $('#billing_city').val($(this).find(':selected').text());
    $('#billing_district').val(null);
    var city_code = $(this).find(':selected').data('city-code');

    $.ajax({
      type : "post",
      dataType : "json",
      url : ffw_avcd_ajax.ajaxurl,
      data : {action: "autoloadDistrict", city_code: city_code},
      beforeSend: function() {
      },
      success: function(response) {
        $('.billing_district_select').remove();
        $('#billing_district').after(response.markup);
        $('.billing_district_select').on('change', function() {
          $('#billing_district').val($(this).find(':selected').text());
        });
      },
      error: function(response) {
        console.log(response);
      }
    });
  }

  function autoloadDistrictDefault() {
    $('#billing_district').attr('type', 'hidden');
    $('#billing_city').val($('.billing_city_select').find(':selected').text());
    var city_code = $('.billing_city_select').find(':selected').data('city-code');
    console.log(city_code);

    $.ajax({
      type : "post",
      dataType : "json",
      url : ffw_avcd_ajax.ajaxurl,
      data : {action: "autoloadDistrict", city_code: city_code},
      beforeSend: function() {
      },
      success: function(response) {
        $('#billing_district').after(response.markup);
        $('.billing_district_select').on('change', function() {
          $('#billing_district').val($(this).find(':selected').text());
        });
      },
      error: function(response) {
        console.log(response);
      }
    });
  }

  $(document).ready(function() {
    // Call to function
  });

  $(window).load(function() {
    // Call to function
    autoloadCity();
  });

  $(window).resize(function() {
    // Call to function
  });
})(jQuery);

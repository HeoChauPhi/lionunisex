/*jslint browser: true*/
/*global $, jQuery, Modernizr, enquire, audiojs*/

(function($) {
  // Social Field
  var ajax_load_field = function() {
    var parent = $(this).parents('.ajax_group');

    $.ajax({
      type : "post",
      dataType : "json",
      url : fieldsAjax.ajaxurl,
      data : {action: "ajaxloadfields", field_generate: '<p>this is field generate</p>'},
      beforeSend: function() {
      },
      success: function(response) {
      },
      error: function(response) {
      }
    });

    return false;
  };

  var addNewFields = function() {
    var parent = $(this).parents('.ajax_group');
    var item = parent.find('.ajax-list > .ajax-item');

    item.first().clone().appendTo(parent.find('.ajax-list'));

    var index_item = parent.find('.ajax-list > .ajax-item').length;
    if(index_item != 1) {
      parent.find('.ajax-action .ajax-remove').removeAttr('disabled');
    }

    return false;
  };

  var removeNewFields = function() {
    var parent = $(this).parents('.ajax_group');
    var item = parent.find('.ajax-list > .ajax-item');

    if(item.length > 1) {
      item.last().remove();
    }

    var index_item = parent.find('.ajax-list > .ajax-item').length;
    if(index_item == 1) {
      $(this).attr('disabled', 'disabled');
      return false;
    }

    return false;
  };
  // End Social Field

  // Media Upload
  function open_media_window() {
    if (this.window === undefined) {
      this.window = wp.media({
        title: 'Insert a media',
        multiple: false,
        button: {text: 'Insert'}
      });

      var self = this; // Needed to retrieve our variable in the anonymous function below
      this.window.on('select', function() {
        var first = self.window.state().get('selection').first().toJSON();
        //wp.media.editor.insert('[myshortcode id="' + first.id + '"]');
        $('.upload_media_url').val(first.url);
        $('.upload_media_thumb').attr('src', first.url);
        $('.banner-wrapper').removeClass('hidden');
      });
    }

    this.window.open();
    return false;
  }

  function remove_media_window() {
    $('.upload_media_url').val('');
    $('.upload_media_thumb').attr('src', '');
    $('.banner-wrapper').addClass('hidden');
  }

  $(document).ready(function() {
    // Call to function
    $('.ajax_group .ajax-add').on('click', addNewFields);
    $('.ajax_group .ajax-remove').on('click', removeNewFields);

    $('.upload_media_button').click(open_media_window);
    $('.remove_media_button').click(remove_media_window);
  });

  $(window).load(function() {
    // Call to function
  });

  $(window).resize(function() {
    // Call to function
  });
})(jQuery);

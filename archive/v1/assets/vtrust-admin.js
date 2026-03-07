/**
 * vtrust-admin.js
 */
// jQuery(document).ready(function($) {
//     $('#vtrust_enable_custom_styles').on('change', function() {
//         if ($(this).is(':checked')) {
//             $('.vtrust-styling-options-container').fadeIn();
//         } else {
//             $('.vtrust-styling-options-container').fadeOut();
//         }
//     });
// });

(function ($) {
  'use strict';

  vtrust.init = () => {
    $('.widget-toolbar .vt-spinner').hide();
    $('.widget-options').fadeIn();

    $('[id="vt-colour-mode"]').on('change', (event) => {
      const isChecked = $(event.target).closest('input[type="checkbox"]')[0].checked;
      vtrust.setDarkMode(isChecked);
    });

    $('[id="vt-override-styles"]').on('change', (event) => {
      const isChecked = $(event.target).closest('input[type="checkbox"]')[0].checked;
      vtrust.setCustomStyles(isChecked);
    });
  };

  vtrust.setDarkMode = (isEnabled) => {
    if (isEnabled) {
      $('.widget-container').addClass('widget-dark-background');
    } else {
      $('.widget-container').removeClass('widget-dark-background');
    }

    vtrust.setOptions();
  };

  vtrust.setCustomStyles = (isEnabled) => {
    if (isEnabled) {
      $('.vt-custom-styles').fadeIn();
    } else {
      $('.vt-custom-styles').fadeOut();
    }

    vtrust.setOptions();
  };

  vtrust.setOptions = () => {
    const container = $('.widget-options');
    const params = $(container).data('widget-options');

    if (params) {
      console.log('test = ' + $(container).find('#vt-override-styles').length);

      const request = {
        action: params.action,
        nonce: params.nonce,
        isStyleOverrideEnabled: $(container).find('#vt-override-styles')[0].checked,
        isDarkModeEnabled: $(container).find('#vt-colour-mode')[0].checked,
      };

      // console.log('request');
      // console.log(request);

      $.post(ajaxurl, request)
        .done((response) => {
          console.log('done');
          console.log(response);
        })
        .fail((fullResponse) => {
          console.log('fail');
          console.log(fullResponse);
        })
        .always(() => {
          console.log('always');
        });
    }
  };

  $(window).on('load', () => {
    // console.log('Verify Trust');
    vtrust.init();
  });
})(jQuery);

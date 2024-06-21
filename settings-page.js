jQuery(function ($) {
  // Initialize the Select2 fields, except for the theme field
  $(".sharethumb-settings-row:not(.theme) select.select2").select2({
    allowClear: true,
    placeholder: "--",
  });

  // Initialize the theme field, and keep track of it for showing/hiding the custom theme text field
  // Also, we'll show a sample image when the theme is selected
  const theme_select = $(".sharethumb-settings-row.theme select.select2").select2({
    allowClear: true,
    placeholder: "--",
  });
  theme_select.on('select2:clear', function (e) {
    showOrHideCustomThemeField();
    showThemeSample();
  });
  theme_select.on('select2:select', function (e) {
    showOrHideCustomThemeField();
    showThemeSample();
  });
  showOrHideCustomThemeField();
  showThemeSample();

  // Initialize Image field uploaders
  $("body").on("click", ".image-upload", function (e) {
    e.preventDefault();

    const button = $(this);
    const value_field = button.parent().find('[data-url-field-id]');
    const image_id = value_field.val();

    const custom_uploader = wp
      .media({
        title: "Insert image",
        library: {
          type: "image",
        },
        button: {
          text: "Use this image",
        },
        multiple: false,
      })
      .on("select", function () {
        const attachment = custom_uploader
          .state()
          .get("selection")
          .first()
          .toJSON();
        if (attachment) {
          const url = attachment.sizes.hasOwnProperty("thumbnail")
            ? attachment.sizes.thumbnail.url
            : attachment.url;
          button.html('<img src="' + url + '">');   // add image instead of "Upload Image"
          value_field.val(attachment.id);           // populate the hidden field with image ID

          const container = button.parent();
          const url_field_id = value_field.data('url-field-id');
          if(url_field_id) {
            let metatag_url = attachment.url;
            if(attachment.sizes.hasOwnProperty("large")) {
              metatag_url = attachment.sizes.large.url;
            } else if(attachment.sizes.hasOwnProperty("medium")) {
              metatag_url = attachment.sizes.medium.url;
            }
            $('#' + url_field_id).val(metatag_url);      
            container.addClass('has-image');
          } else {
            container.removeClass('has-image');
          }
        }
      })
      .on("open", function () { // already selected images
        if (image_id) {
          const selection = custom_uploader.state().get("selection");
          attachment = wp.media.attachment(image_id);
          attachment.fetch();
          selection.add(attachment ? [attachment] : []);
        }
      });

    custom_uploader.open();
  });

  // Remove the image & related URL field & update the display of the buttons
  $("body").on("click", ".image-remove", function (e) {
    e.preventDefault();
    const button = $(this);
    
    // empty the hidden field
    const url_field_id = button.data('url-field-id');
    if(url_field_id) {
      $('#' + url_field_id).val("");
    }

    button.prev().addClass("button").html("Upload image"); // replace the image with text
    button.parent().removeClass('has-image');
  });

    
  // Initialize the theme preview image
  $('#field-theme-preview').on("load", function() {
    $(this).css('display', 'block');
  });

  // Update the contrast ratio when the color fields are changed
  $('#field-dark_theme_bg_color').on("change", function() {
    updateContrastRatio('dark');
  });
  $('#field-dark_theme_font_color').on("change", function() {
    updateContrastRatio('dark');
  });
  $('#field-light_theme_bg_color').on("change", function() {
    updateContrastRatio('light');
  });
  $('#field-light_theme_font_color').on("change", function() {
    updateContrastRatio('light');
  });

  // Initialize the image field's display of the Remove Image button
  $('.sharethumb-settings-image input').each(function() {
    if($(this).val()) {
      $(this).parent().addClass('has-image');
    }
  });
  // Initialize the color contrast ratio messages
  updateContrastRatio('dark');
  updateContrastRatio('light');



  /* Helper Functions */
  function showOrHideCustomThemeField() {
    const selected_theme = theme_select.val();
    if(selected_theme == 'custom') {
      $(".sharethumb-settings-row.custom-theme").addClass('show');
    } else {
      $(".sharethumb-settings-row.custom-theme").removeClass('show');
    }
  }

  function showThemeSample() {
    const selected_theme = theme_select.select2('data');
    if(selected_theme[0].id) {
      // this img is hidden by default; below is an event listener that will show the image when it's loaded
      $('#field-theme-preview').attr('src', `${theme_url}/${selected_theme[0].id}/${domain}`);
    } else {
      $('#field-theme-preview').attr('src', '');
    }
  }
  function updateContrastRatio(type) {
    const bg_color_field = $(`#field-${type}_theme_bg_color`);
    const font_color_field = $(`#field-${type}_theme_font_color`);

    const bg_color = bg_color_field.val();
    const font_color = font_color_field.val();

    if(font_color && bg_color) {
      const ratio = getContrastRatio(font_color, bg_color);
      const message_field = bg_color_field.parent().find('.color-ratio-message');
      console.log(ratio, message_field);
      if(ratio && message_field) {
        if(ratio >= 4.5) {
          message_field.addClass('good-ratio');
          message_field.html(`Accessibilty Ratio: ${ratio.toFixed(2)}.`);
        } else {
          message_field.removeClass('good-ratio');
          message_field.html(`Accessibilty Ratio: ${ratio.toFixed(2)}. Should be at least 4.5.`);
        }
      }
    }
  }
  
  function getContrastRatio(color1, color2) {
    if (color1.length !== 7 || color2.length !== 7 || color1[0] !== '#' || color2[0] !== '#')
      return 0;
    
    const hexToRgb = (hex) => {
      const shorthandRegex = /^#?([a-f\d])([a-f\d])([a-f\d])$/i;
      hex = hex.replace(shorthandRegex, function (m, r, g, b) {
        return r + r + g + g + b + b;
      });
  
      const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
      return result
        ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16),
          }
        : null;
    };
  
    const rgb1 = hexToRgb(color1);
    const rgb2 = hexToRgb(color2);
  
    const l1 =
      0.2126 * Math.pow(rgb1.r / 255, 2.2) +
      0.7152 * Math.pow(rgb1.g / 255, 2.2) +
      0.0722 * Math.pow(rgb1.b / 255, 2.2);
    const l2 =
      0.2126 * Math.pow(rgb2.r / 255, 2.2) +
      0.7152 * Math.pow(rgb2.g / 255, 2.2) +
      0.0722 * Math.pow(rgb2.b / 255, 2.2);
  
    return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
  }
});
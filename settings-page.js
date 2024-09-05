jQuery(function ($) {
  // Indicates whether the preview image is currently being fetched, so that we don't send multiple requests at once
  let fetching_preview = false;

  // Initialize the Select2 fields
  initializeSelect2Field(getSelectorForContext('font', 'Font'));
  initializeSelect2Field(getSelectorForContext('highlight_font', 'Highlight Font'));
  initializeSelect2Field(getSelectorForContext('theme', 'Theme'));

  // Initialize the color contrast ratio messages
  updateContrastRatio('dark');
  updateContrastRatio('light');

  // Initialize the preview image
  fetchPreviewImage();

  // Initialize Image field uploaders
  $("body").on("click", ".image-upload", function (e) {
    e.preventDefault();
    handleImageUpload($(this));
  });

  // Remove the image & related URL field & update the display of the buttons
  $("body").on("click", ".image-remove", function (e) {
    e.preventDefault();
    handleImageRemove($(this));
  });

  // Initialize the theme preview image
  $('#field-theme-preview').on("load", function () {
    $(this).css('display', 'block');
  });

  // Update the contrast ratio when the color fields are changed
  $(getSelectorForContext('dark_theme_bg_color')).on("change", function () {
    updateContrastRatio('dark');
    fetchPreviewImage();
  });
  $(getSelectorForContext('dark_theme_font_color')).on("change", function () {
    updateContrastRatio('dark');
    fetchPreviewImage();
  });
  $(getSelectorForContext('light_theme_bg_color')).on("change", function () {
    updateContrastRatio('light');
    fetchPreviewImage();
  });
  $(getSelectorForContext('light_theme_font_color')).on("change", function () {
    updateContrastRatio('light');
    fetchPreviewImage();
  });
  $(getSelectorForContext('accent_color')).on("change", function () {
    fetchPreviewImage();
  });

  // Initialize the image field's display of the Remove Image button
  $('.sharethumb-image-container input').each(function () {
    if (parseInt($(this).val())) {
      $(this).parent().addClass('has-image');
    }
  });

  /* Helper Functions */
  function getGlobalFormValue(name, return_sibling_img_src = false) {
    const field = $(`#field-${name}`);
    if (field.is("input[type='checkbox']")) {
      return field.prop("checked") ? "on" : "off";
    } else {
      if (return_sibling_img_src) {
        return field.parent().find('img').attr('src');
      } else {
        return field.val();
      }
    }
  }

  function getOverrideFormValue(name, return_sibling_img_src = false) {
    const field = $(`#field-fsst_${name}`);
    if (field.is("input[type='checkbox']")) {
      return field.prop("checked") ? "on" : "off";
    } else {
      return field.val();
    }
  }

  function getFormValue(name, return_sibling_img_src = false) {
    let value = '';
    if (settings_context == 'global') {
      value = getGlobalFormValue(name, return_sibling_img_src);
    } else {
      value = getOverrideFormValue(name, return_sibling_img_src);
    }
    return value;
  }

  function getFormData() {
    const overrides = new FormData();

    const default_thumbnail_url = getFormValue('default_thumbnail_url', true);
    if (default_thumbnail_url) {
      overrides.append('default_thumbnail', default_thumbnail_url);
    }

    const logo_url = getFormValue('logo_url', true);
    if (logo_url) {
      overrides.append('logo', logo_url);
    }

    const icon_url = getFormValue('icon_url', true);
    if (icon_url) {
      overrides.append('icon', icon_url);
    }

    const font = getFormValue('font');
    if (font) {
      overrides.append('font', font);
    }

    const highlight_font = getFormValue('highlight_font');
    if (highlight_font) {
      overrides.append('highlight_font', highlight_font);
    }

    const theme = getFormValue('theme');
    if (theme) {
      overrides.append('theme', theme);
    }

    const light_theme_bg_color = getFormValue('light_theme_bg_color');
    if (light_theme_bg_color) {
      overrides.append('light_theme_bg_color', light_theme_bg_color);
    }

    const dark_theme_bg_color = getFormValue('dark_theme_bg_color');
    if (dark_theme_bg_color) {
      overrides.append('dark_theme_bg_color', dark_theme_bg_color);
    }

    const light_theme_font_color = getFormValue('light_theme_font_color');
    if (light_theme_font_color) {
      overrides.append('light_theme_font_color', light_theme_font_color);
    }

    const dark_theme_font_color = getFormValue('dark_theme_font_color');
    if (dark_theme_font_color) {
      overrides.append('dark_theme_font_color', dark_theme_font_color);
    }

    const accent_color = getFormValue('accent_color');
    if (accent_color) {
      overrides.append('accent_color', accent_color);
    }

    return overrides;
  }

  function fetchPreviewImage() {
    if (fetching_preview) return;
    fetching_preview = true;
    $('#field-theme-preview').attr('src', '');
    $('#field-theme-preview').attr('alt', 'Fetching Preview Image');

    const form_data = getFormData();

    fetch(image_preview_url, {
      method: "POST",
      body: form_data
    })
      .then(response => response.json())
      .then(data => {
        const field_theme_preview = $('#field-theme-preview');
        if (data.result) {
          const preview_image_src = 'data:image/png;base64,' + data.result;
          field_theme_preview.attr('src', `${preview_image_src}`);
          field_theme_preview.css('visible', 'visible');
          field_theme_preview.attr('alt', 'Preview Image');
        } else {
          field_theme_preview.attr('src', '');
          field_theme_preview.css('visible', 'hidden');
          field_theme_preview.attr('alt', '');
        }
        fetching_preview = false;
      })
      .catch(error => {
        $('#field-theme-preview').css('visible', 'hidden');
        $('#field-theme-preview').attr('alt', '');
        fetching_preview = false;
        console.log('error', error);
      });
  }

  function showOrHideCustomThemeField() {
    const selected_theme = theme_select.val();
    if (selected_theme == 'custom') {
      $(".sharethumb-settings-row.custom-theme").addClass('show');
    } else {
      $(".sharethumb-settings-row.custom-theme").removeClass('show');
    }
  }

  function updateContrastRatio(type) {
    if (settings_context == 'override') {
      type = `fsst_${type}`;
    }

    const bg_color_field = $(`#field-${type}_theme_bg_color`);
    const font_color_field = $(`#field-${type}_theme_font_color`);

    const bg_color = bg_color_field.val();
    const font_color = font_color_field.val();
    const message_field = bg_color_field.parent().find('.color-ratio-message');

    if (font_color && bg_color) {
      const ratio = getContrastRatio(font_color, bg_color);
      if (ratio && message_field) {
        if (ratio >= 4.5) {
          message_field.addClass('good-ratio');
          message_field.html(`Accessibilty Ratio: ${ratio.toFixed(2)}.`);
        } else {
          message_field.removeClass('good-ratio');
          message_field.html(`Accessibilty Ratio: ${ratio.toFixed(2)}. Should be at least 4.5.`);
        }
      }
    } else {
      message_field.html('');
      message_field.removeClass('good-ratio');
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

  function initializeSelect2Field(selector, placeholder = "--") {
    const select = $(selector).select2({
      allowClear: true,
      placeholder: placeholder
    });
    select.on('select2:clear', function (e) {
      fetchPreviewImage();
    });
    select.on('select2:select', function (e) {
      fetchPreviewImage();
    });
  }

  function getSelectorForContext(key) {
    if (settings_context == 'global') {
      return `#field-${key}`;
    } else {
      return `#field-fsst_${key}`;
    }
  }

  function handleImageUpload(button) {
    const value_field = button.parent().find('[data-url-field-id]');
    const image_id = value_field.val();

    const custom_uploader = wp
      .media({
        title: "Insert image",
        library: { type: "image" },
        button: { text: "Use this image" },
        multiple: false,
      })
      .on("select", function () {
        const attachment = custom_uploader
          .state()
          .get("selection")
          .first()
          .toJSON();
        if (attachment) {
          const url = attachment.sizes.hasOwnProperty("medium")
            ? attachment.sizes.medium.url
            : attachment.url;
          button.html('<img src="' + url + '">');   // add image instead of "Upload Image"
          value_field.val(attachment.id);           // populate the hidden field with image ID

          const container = button.parent();
          const url_field_id = value_field.data('url-field-id');
          if (url_field_id) {
            let metatag_url = attachment.url;
            if (attachment.sizes.hasOwnProperty("large")) {
              metatag_url = attachment.sizes.large.url;
            } else if (attachment.sizes.hasOwnProperty("medium")) {
              metatag_url = attachment.sizes.medium.url;
            } else {
              metatag_url = attachment.url;
            }
            $('#' + url_field_id).val(metatag_url);
          }
          container.addClass('has-image');
        }

        fetchPreviewImage();
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
  }

  function handleImageRemove(button) {
    const button_parent = button.parent();

    // empty the hidden field
    const url_field_id = button.data('url-field-id');
    if (url_field_id) {
      $('#' + url_field_id).val("");
    }

    button_parent.find('input').val("");
    button_parent.removeClass('has-image');
    button_parent.find('.image-upload').addClass("button").html("Upload image"); // replace the image with text
  };
});
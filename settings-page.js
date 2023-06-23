jQuery(function ($) {
  // Initialize the Select2 fields, except for the theme field
  $(".sharethumb-settings-row:not(.theme) select.select2").select2({
    allowClear: true,
    placeholder: "--",
  });

  // Initialize the theme field, and keep track of it for showing/hiding the custom theme text field
  const theme_select = $(".sharethumb-settings-row.theme select.select2").select2({
    allowClear: true,
    placeholder: "--",
  });

  function showOrHideCustomThemeField() {
    const selected_theme = theme_select.val();
    if(selected_theme == 'custom') {
      $(".sharethumb-settings-row.custom-theme").addClass('show');
    } else {
      $(".sharethumb-settings-row.custom-theme").removeClass('show');
    }
  }

  theme_select.on('select2:select', function (e) {
    showOrHideCustomThemeField();
  });
  showOrHideCustomThemeField();



  // Initialize Image fields
  $("body").on("click", ".image-upload", function (e) {
    e.preventDefault();
    const button = $(this);
    const image_id = button.next().next().val();

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
        // it also has "open" and "close" events
        const attachment = custom_uploader
          .state()
          .get("selection")
          .first()
          .toJSON();
        if (attachment) {
          const url = attachment.sizes.hasOwnProperty("thumbnail")
            ? attachment.sizes.thumbnail.url
            : attachment.url;
          button.html('<img src="' + url + '">'); // add image instead of "Upload Image"
          button.next().show(); // show "Remove image" link
          button.next().next().val(attachment.id); // Populate the hidden field with image ID

          const url_field_id = button.next().next().data('url-field-id');
          if(url_field_id) {
            let metatag_url = attachment.url;
            if(attachment.sizes.hasOwnProperty("large")) {
              metatag_url = attachment.sizes.large.url;
            } else if(attachment.sizes.hasOwnProperty("medium")) {
              metatag_url = attachment.sizes.medium.url;
            }
            $('#' + url_field_id).val(metatag_url);            
          }
        }
      });

    // already selected images
    custom_uploader.on("open", function () {
      if (image_id) {
        const selection = custom_uploader.state().get("selection");
        attachment = wp.media.attachment(image_id);
        attachment.fetch();
        selection.add(attachment ? [attachment] : []);
      }
    });

    custom_uploader.open();
  });

  // on remove button click
  $("body").on("click", ".image-remove", function (e) {
    e.preventDefault();
    const button = $(this);
    button.next().val(""); // emptying the hidden field
    button.hide().prev().addClass("button").html("Upload image"); // replace the image with text
  });
});
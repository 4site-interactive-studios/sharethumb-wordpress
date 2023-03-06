jQuery(function($) {
	// We want to ensure we validate the API key at least once.
	// This can happen if the api key input receives a blur event, or when saving the page.
	let api_key_validation_run = false;

	function fsst_clear_validation_result() {
		$('#validation-message').removeClass('validated');
		$('#validation-message').removeClass('unvalidated');
		$('#validation-message').text('');
	}
	function fsst_show_validation_result(result, message) {
		if(result) {
			$('#field-api_key').addClass('validated');
			$('#field-api_key').removeClass('unvalidated');
			$('#validation-message').addClass('validated');
			$('#validation-message').removeClass('unvalidated');
		} else {
			$('#field-api_key').addClass('unvalidated');
			$('#field-api_key').removeClass('validated');
			$('#validation-message').removeClass('validated');
			$('#validation-message').addClass('unvalidated');
		}

		$('#validation-message').text(message);
		$('#field-api_key').removeClass('validating');
	}

	function fsst_call_api(url, headers, body, callback) {
		$.ajax(url, {
			headers: headers,
			success: function(response) {
				callback(true, response);
			},
			error: function(response) {
				callback(false, response);
			}
		});
	}

	function fsst_validate_api_key(api_key, callback) {
		$(this).addClass('validating');
		fsst_clear_validation_result();
		fsst_call_api('https://og.sharethumb.app/validate-api-key', { 'sharethumb-api-key': api_key }, {}, function(result, response) {
			if(result && response && response.isValid) {
				fsst_show_validation_result(true, 'API Key Validation Result: Success');
				// update plan hidden value
				$('input[name=plan]').val(response.plan);
			} else if(response && response.responseJSON) {
				fsst_show_validation_result(false, 'API Key Validation Result: ' + response.responseJSON.message);
				// clear plan hidden value
				$('input[name=plan]').val('');
			}
			api_key_validation_run = true;
			$(this).removeClass('validating');
			if(callback) callback(result);
		});
	}

	$('body').on('click', '.image-upload', function(e) {
		e.preventDefault();
		const button = $(this);
		const image_id = button.next().next().val();

		const custom_uploader = wp.media({
			title: 'Insert image',
			library: {
				type : 'image'
			},
			button: {
				text: 'Use this image'
			},
			multiple: false
		}).on('select', function() { // it also has "open" and "close" events
			const attachment = custom_uploader.state().get('selection').first().toJSON();
			if(attachment) {
				const url = attachment.sizes.hasOwnProperty('thumbnail') ? attachment.sizes.thumbnail.url : attachment.url;
				button.html('<img src="' + url + '">'); // add image instead of "Upload Image"
				button.next().show(); // show "Remove image" link
				button.next().next().val(attachment.id); // Populate the hidden field with image ID				
			}
		});

		// already selected images
		custom_uploader.on('open', function() {
			if(image_id) {
				const selection = custom_uploader.state().get('selection');
				attachment = wp.media.attachment(image_id);
				attachment.fetch();
				selection.add(attachment ? [attachment] : []);
			}
		});

		custom_uploader.open();
	});

	// on remove button click
	$('body').on('click', '.image-remove', function(e){
		e.preventDefault();
		const button = $(this);
		button.next().val(''); // emptying the hidden field
		button.hide().prev().addClass('button').html('Upload image'); // replace the image with text
	});

	$('.configuration-wrapper select').select2({
		allowClear: true,
		placeholder: '--'
	});

	$('#field-api_key').blur(function(e) {
		fsst_validate_api_key($(this).val(), null);
	});

	$('#st-settings-form').submit(function(e) {
		// we need to verify the api key
		if(!api_key_validation_run) {
			e.preventDefault();
			fsst_validate_api_key($('#field-api_key').val(), function(result) {
				$('#st-settings-form').submit();
			});
		}
	});
});
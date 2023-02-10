jQuery(function($) {
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
			button.html('<img src="' + attachment.sizes.thumbnail.url + '">'); // add image instead of "Upload Image"
			button.next().show(); // show "Remove image" link
			button.next().next().val(attachment.id); // Populate the hidden field with image ID
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
});
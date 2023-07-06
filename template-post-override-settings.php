<?php 
	if(!defined('ABSPATH')) { exit; }
	global $post;
	$configuration = fsst_get_post_configuration($post->ID);
	wp_nonce_field('sharethumb_metabox', 'sharethumb_nonce'); 
?>
<div class='post-configuration-wrapper'>
	<?php if($post->ID): ?>
	<?php 
		$url = get_the_permalink($post->ID);
		$url = str_replace(['http://', 'https://'], '', $url);
		$url = trim($url, '/');
		$image_url = fsst_get_st_generated_image_url($url, true);
	?>
	<img src='<?php echo $image_url; ?>' class='st-generated-image' onerror="this.style.visibility='hidden'" />
	<?php endif; ?>

	<div class='sharethumb-settings-row instructions'>
		Fields are optional.  If left empty, will use the globally configured options.
		<?php if(empty($configuration['api_key_set'])): ?>
			<br><br>
			We can update your thumbs automatically if you <a href='/wp-admin/admin.php?page=sharethumb' target='_blank'>set an API key</a>.
		<?php endif; ?>
	</div>

	<div class='sharethumb-settings-row'>
		<?php echo fsst_get_overrides_image_field_html('Logo', 'logo', $configuration); ?>
	</div>

	<div class='sharethumb-settings-row'>
		<?php echo fsst_get_overrides_image_field_html('Icon', 'icon', $configuration); ?>
	</div>

	<div class='sharethumb-settings-row theme'>
		<?php echo fsst_get_overrides_select_field_html('Theme', 'theme', $configuration); ?>
	</div>

	<div class='sharethumb-settings-row custom-theme'>
		<?php echo fsst_get_overrides_text_field_html('Custom Theme', 'custom_theme', $configuration); ?>
	</div>

	<div class='sharethumb-settings-row'>
		<?php echo fsst_get_overrides_select_field_html('Font', 'font', $configuration); ?>
	</div>

	<div class='sharethumb-settings-row'>
		<?php echo fsst_get_overrides_color_picker_field_html('Font Color', 'font_color', $configuration); ?>
	</div>
	<div class='sharethumb-settings-row'>
		<?php echo fsst_get_overrides_color_picker_field_html('Background Color', 'background_color', $configuration); ?>
	</div>
	<div class='sharethumb-settings-row'>
		<?php echo fsst_get_overrides_color_picker_field_html('Accent Color', 'accent_color', $configuration); ?>
	</div>
	<div class='sharethumb-settings-row'>
		<?php echo fsst_get_overrides_color_picker_field_html('Secondary Color', 'secondary_color', $configuration); ?>
	</div>
</div>
<script>
	let save_post_timeout = null;

	function fsst_save_post_hook() {
		save_post_timeout = null;
		const img = document.querySelector('img.st-generated-image');
		if(img) {
			const src_parts = img.src.split('?');
			if(src_parts[0]) {
				const new_src = src_parts[0] + '?' + Math.floor(Math.random() * 10000);
				img.src = new_src;
			}
		}
	}

	wp.data.subscribe(function() {
		const is_saving_post = wp.data.select('core/editor').isSavingPost();
		const is_autosaving_post = wp.data.select('core/editor').isAutosavingPost();

		if(is_saving_post && !is_autosaving_post) {
			// We see a lot of save_post events -- we only want to save once, however
			if(save_post_timeout) {
				clearTimeout(save_post_timeout);
			}
			save_post_timeout = setTimeout(fsst_save_post_hook, 4000);
		}
	});
</script>
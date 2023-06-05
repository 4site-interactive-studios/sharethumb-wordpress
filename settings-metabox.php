<?php if(!defined('ABSPATH')) { exit; } ?>
<?php global $post; ?>
<?php $configuration = fsst_get_post_configuration($post->ID); ?>
<?php wp_nonce_field('sharethumb_metabox', 'sharethumb_nonce'); ?>
<div class='configuration-wrapper'>
	<?php if($post->ID): ?>
	<?php 
		$url = get_the_permalink($post->ID);
		$url = str_replace(['http://', 'https://'], '', $url);
		$url = trim($url, '/');
		$image_url = fsst_get_st_generated_image_url($url);
	?>
	<img src='<?php echo $image_url; ?>' class='st-generated-image' onerror="this.style.visibility='hidden'" />
	<?php endif; ?>

	<div class='full-width instructions'>
		Fields are optional.  If left empty, will use the globally configured options.
	</div>
	<div class='full-width one-column'>
		<?php echo fsst_get_image_field('Logo', 'logo', $configuration); ?>
		<?php echo fsst_get_image_field('Icon', 'icon', $configuration); ?>
	</div>

	<div class='full-width one-column'>
		<div class='theme-outer-wrapper' data-theme='<?php echo $configuration['theme']; ?>'>
			<?php echo fsst_get_select_field('Theme', 'theme', $configuration); ?>
			<?php echo fsst_get_text_field('Custom Theme', 'custom_theme', $configuration); ?>
		</div>
		<div class=''>
			<?php echo fsst_get_select_field('Font', 'font', $configuration); ?>
		</div>
	</div>

	<div class='full-width one-column'>
		<?php echo fsst_get_color_picker_field('Foreground', 'foreground', $configuration); ?>
		<?php echo fsst_get_color_picker_field('Background', 'background', $configuration); ?>
		<?php echo fsst_get_color_picker_field('Accent', 'accent', $configuration); ?>
		<?php echo fsst_get_color_picker_field('Secondary', 'secondary', $configuration); ?>
	</div>
</div>
<script>
<?php include 'settings-page.js'; ?>
</script>
<style>
<?php include 'settings-page.css'; ?>
</style>
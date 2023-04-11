<?php if(!defined('ABSPATH')) { exit; } ?>
<?php global $post; ?>
<?php $configuration = fsst_get_post_configuration($post->ID); ?>
<?php wp_nonce_field('sharethumb_metabox', 'sharethumb_nonce'); ?>
<div class='configuration-wrapper'>
	<div class='full-width instructions'>
		Fields are optional.  If left empty, will use the globally configured options.
	</div>
	<div class='full-width one-column'>
		<?php echo fsst_get_image_field('Logo', 'sharethumb_logo', $configuration); ?>
		<?php echo fsst_get_image_field('Icon', 'sharethumb_icon', $configuration); ?>
	</div>

	<div class='full-width one-column'>
		<div class='theme-outer-wrapper' data-theme='<?php echo $configuration['theme']; ?>'>
			<?php echo fsst_get_select_field('Theme', 'sharethumb_theme', $configuration); ?>
			<?php echo fsst_get_text_field('Custom Theme', 'sharethumb_custom_theme', $configuration); ?>
		</div>
		<div class=''>
			<?php echo fsst_get_select_field('Font', 'font', $configuration); ?>
		</div>
	</div>

	<div class='full-width one-column'>
		<?php echo fsst_get_color_picker_field('Foreground', 'sharethumb_foreground', $configuration); ?>
		<?php echo fsst_get_color_picker_field('Background', 'sharethumb_background', $configuration); ?>
		<?php echo fsst_get_color_picker_field('Accent', 'sharethumb_accent', $configuration); ?>
		<?php echo fsst_get_color_picker_field('Secondary', 'sharethumb_secondary', $configuration); ?>
	</div>
</div>
<script>
<?php include 'settings-page.js'; ?>
</script>
<style>
<?php include 'settings-page.css'; ?>
</style>
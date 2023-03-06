<?php if(!defined('ABSPATH')) { exit; } ?>
<?php $configuration = fsst_get_configuration(); ?>
<h1><?php echo get_admin_page_title(); ?></h1>
<form id='st-settings-form' method='POST' action='<?php echo admin_url('admin.php?page=' . $_GET['page']); ?>'>
	<div class='configuration-wrapper'>
		<div class='full-width'>
			<?php echo fsst_get_validation_result_field(); ?>
		</div>
		<div class='full-width two-column'>
			<?php echo fsst_get_text_field('API Key', 'api_key', $configuration); ?>
			<?php echo fsst_get_text_field('Domain Verification Code', 'dv_code', $configuration); ?>
		</div>
		<div class='full-width two-column'>
			<?php echo fsst_get_image_field('Logo', 'logo', $configuration); ?>
			<?php echo fsst_get_image_field('Icon', 'icon', $configuration); ?>
		</div>
		<div class='full-width two-column'>
			<?php echo fsst_get_select_field('Theme', 'theme', $configuration); ?>
			<?php echo fsst_get_select_field('Font', 'font', $configuration); ?>
		</div>
		<div class='full-width two-column'>
			<?php echo fsst_get_color_picker_field('Foreground', 'foreground', $configuration); ?>
			<?php echo fsst_get_color_picker_field('Background', 'background', $configuration); ?>
			<?php echo fsst_get_color_picker_field('Accent', 'accent', $configuration); ?>
			<?php echo fsst_get_color_picker_field('Secondary', 'secondary', $configuration); ?>
		</div>
		<div class='hidden'>
			<?php echo fsst_get_hidden_field('plan', $configuration); ?>
		</div>
		<div class='full-width'>
			<div class='input-wrapper'>
				<button type='submit'>Update</button>
			</div>
		</div>
	</div>
	<div class='notes-wrapper'>
		<ul>
			<li>Color picker JS borrowed from <a href='https://jscolor.com/' target='_blank'>jscolor.com</a></li>
			<li>Drop-down JS borrowed from <a href='https://select2.org/' target='_blank'>select2.org</a></li>
		</ul>
	</div>
</form>

<style>
#validation-message {
	padding: 20px;
}
#validation-message.validated {
	color: green;
}
#validation-message.unvalidated {
	color: red;
}
#field-api-key.validated {
	background-image: conic-gradient(from var(--border-angle), #fff, #fff 50%, #fff), conic-gradient(from var(--border-angle), transparent 20%, #00FF00, #00FF00);
}
#field-api-key.unvalidated {
	background-image: conic-gradient(from var(--border-angle), #fff, #fff 50%, #fff), conic-gradient(from var(--border-angle), transparent 20%, #FF0000, #FF0000);
}

#field-api_key {
	animation-play-state: paused;
}

#field-api_key.validating {
	--border-size: 1px;
	--border-angle: 0turn;
	background-image: conic-gradient(from var(--border-angle), #fff, #fff 50%, #fff), conic-gradient(from var(--border-angle), transparent 20%, #08f, #f03);
	background-size: calc(100% - (var(--border-size) * 2)) calc(100% - (var(--border-size) * 2)), cover;
	background-position: center center;
	background-repeat: no-repeat;

	animation: bg-spin 3s linear infinite;

}

@keyframes bg-spin {
	to {
		--border-angle: 1turn;
	}
}

@property --border-angle {
	syntax: "<angle>";
	inherits: true;
	initial-value: 0turn;
}
</style>
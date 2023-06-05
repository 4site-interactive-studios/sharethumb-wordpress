<?php if(!defined('ABSPATH')) { exit; } ?>
<?php $configuration = fsst_get_global_configuration(); ?>
<h1><?php echo get_admin_page_title(); ?></h1>
<form id='st-settings-form' method='POST' action='<?php echo admin_url('admin.php?page=' . $_GET['page']); ?>'>
	<div class='configuration-wrapper'>
		<div class='full-width'>
			<?php echo fsst_get_validation_result_field($update_message); ?>
		</div>
		<div class='full-width two-column'>
			<?php echo fsst_get_text_field('API Key', 'api_key', $configuration, "You can find the API Key on the settings page for your site at <a href='https://app.sharethumb.io/dashboard' target='_blank'>https://app.sharethumb.io/dashboard</a>."); ?>
			<?php echo fsst_get_text_field('Domain Verification Code', 'dv_code', $configuration, "You can find the Domain Validation code for your site at <a href='https://app.sharethumb.io/dashboard' target='_blank'>https://app.sharethumb.io/dashboard</a>."); ?>
		</div>
		<div class='full-width two-column'>
			<?php echo fsst_get_image_field('Logo', 'logo', $configuration); ?>
			<?php echo fsst_get_image_field('Icon', 'icon', $configuration); ?>
		</div>

		<div class='full-width two-column'>
			<div class='half-width one-column theme-outer-wrapper' data-theme='<?php echo $configuration['theme']; ?>'>
				<?php echo fsst_get_select_field('Theme', 'theme', $configuration); ?>
				<?php echo fsst_get_text_field('Custom Theme', 'custom_theme', $configuration); ?>
			</div>
			<div class='half-width one-column'>
				<?php echo fsst_get_select_field('Font', 'font', $configuration); ?>
			</div>
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

		<?php if(is_array($overridable_post_types) && count($overridable_post_types)): ?>
		<div class='full-width'>
			<div class='input-wrapper'>
				<label>Overridable Post Types</label>
				<div class='checkbox-wrapper'>
				<?php foreach($overridable_post_types as $key => $label): ?>
					<label>
						<input 
							type='checkbox' 
							name='enabled_post_types[]' 
							value='<?php echo $key; ?>'
							<?php if(in_array($key, $enabled_post_types)) echo 'checked'; ?>
						>
						<?php echo $label; ?>
					</label>
				<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<div class='full-width'>
			<div class='input-wrapper'>
				<button type='submit'>Update</button>
			</div>
		</div>
		<p>Have questions about ShareThumb? Please <a href='mailto:support@4sitestudios.com'>email us</a> and we will get back to you within 24 business hours.</p>
	</div>
	<div class='notes-wrapper'>
		<ul>
			<li>Color picker JS borrowed from <a href='https://jscolor.com/' target='_blank'>jscolor.com</a></li>
			<li>Drop-down JS borrowed from <a href='https://select2.org/' target='_blank'>select2.org</a></li>
		</ul>		
	</div>
</form>
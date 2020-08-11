<h1>Sunset Theme Options</h1>
<!--Display settings errors registered-->
<div style="margin-bottom: 20px;">
	<?php settings_errors() ?>
</div>
<!-- we don't insert any action because settings_fields generate for us the http reference of our custom admin page -->
<form method="post" action="options.php" class="sunset-general-form">
    <!-- settings_fields: Generate hidden input field for our form that wordpress use to manage our action inside our form -->
    <?php settings_fields( 'sunset-theme-support' ); ?>
    <!-- do_settings_sections: Prints out all settings sections -->
    <?php do_settings_sections( 'talal_sunset_theme' ); ?>
    <!-- Add Submit Button -->
    <?php submit_button(); ?>
</form>

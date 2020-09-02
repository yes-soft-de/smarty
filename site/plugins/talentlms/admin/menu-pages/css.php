<div class="wrap">
	<h2><i class="fa fa-check-square-o"></i>&nbsp;<?php _e('Edit CSS', 'talentlms'); ?></h2>
	
	<div id='action-message' class='<?php echo $action_status; ?> fade'>
		<p><?php echo $action_message ?></p>
	</div>		
	
	<h2><?php _e('Edit TalentLMS CSS', 'talentlms'); ?></h2>

	<div class="fileedit-sub">
		<div class="alignleft"><h3><?php _e('Editing', 'talentlms'); ?>: <span><strong><?php echo _TLMS_BASEURL_ . 'css/talentlms-style.css'; ?></strong></span></h3></div>
		<br class="clear">
	</div>	
		
	<form name="talentlms-css-form" method="post" action="<?php echo admin_url('admin.php?page=talentlms-css'); ?>">
		<input type="hidden" name="action" value="edit-css">
		<?php $css_file_content = file_get_contents(_TLMS_BASEURL_ . 'css/talentlms-style.css'); ?>
		<textarea cols="70" rows="25" name="tl-edit-css" id="tl-edit-css"><?php echo $css_file_content; ?></textarea>
        <p class="submit">
            <input class="button-primary" type="submit" name="Submit" value="<?php _e('Update', 'talentlms') ?>" />
        </p>	
	</form>
	
</div>
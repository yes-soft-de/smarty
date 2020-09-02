<div class="wrap">
	<h1><?php _e('Setup', 'talentlms'); ?></h1>

	<div id='action-message' class='<?php echo (isset($action_status)) ? $action_status : ''; ?> fade'>
		<p><?php echo (isset($action_message)) ? $action_message : '' ?></p>
	</div>

	<form name="talentlms-setup-form" method="post" action="<?php echo admin_url('admin.php?page=talentlms-setup'); ?>">
		<input type="hidden" name="action" value="tlms-setup">

		<table class="form-table">
			<tr>
				<th scope="row" class="form-field form-required <?php echo $domain_validation; ?>">
					<label for="tlms-domain"><?php _e("TalentLMS Domain", 'talentlms'); ?> <span class="description">(<?php _e("Required", 'talentlms'); ?>)</span>:</label>
				</th>
				<td class="form-field form-required <?php echo $domain_validation; ?>">
					<input id="tlms-domain" name="tlms-domain" style="width: 25em;" value="<?php echo get_option('tlms-domain'); ?>"/>
				</td>
			</tr>
			<tr>
				<th scope="row" class="form-field form-required <?php echo $api_validation; ?>">
					<label for="tlms-apikey"><?php _e("API Key", 'talentlms'); ?> <span class="description"><?php _e("(Required)", 'talentlms'); ?></span>:</label>
				</th>
				<td class="form-field form-required <?php echo $api_validation; ?>">
					<input id="tlms-apikey" name="tlms-apikey" style="width: 25em;" value="<?php echo get_option('tlms-apikey'); ?>"/>
				</td>
			</tr>
			<tr style="border-top: 1px dashed #c9c9c9">
				<th scope="row" class="form-field form-required <?php echo $enroll_user_validation; ?>">
					<label for="tlms-enroll-user-to-courses"><?php _e("Enroll user to courses", 'talentlms'); ?> <span class="description"><?php _e("(Required)", 'talentlms'); ?></span>:</label>
				</th>
				<td class="form-field form-required <?php echo $enroll_user_validation; ?>">
					<select name="tlms-enroll-user-to-courses">
						<option value="submission" <?php echo (get_option('tlms-enroll-user-to-courses') == 'submission')? 'selected="Selected"': ''; ?> ><?php _e("Upon order submission", 'talentlms'); ?></option>
						<option value="completion" <?php echo (get_option('tlms-enroll-user-to-courses') == 'completion')? 'selected="Selected"': ''; ?> ><?php _e("Upon order completion", 'talentlms'); ?></option>
					</select>
				</td>
			</tr>
		</table>

		<hr/>

		<p class="submit">
			<input class="button-primary" type="submit" name="Submit" value="<?php _e('Submit', 'talentlms') ?>"/>
		</p>
	</form>

</div>

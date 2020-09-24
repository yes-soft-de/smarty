<?php 
global $wpdb, $pmpro_msg, $pmpro_msgt, $pmpro_levels, $current_user, $pmpro_currency_symbol;
if(is_user_logged_in()){
	$course_id = url_to_postid($_SERVER['HTTP_REFERER']);
	if(is_numeric($course_id) && $course_id){
		$membership_levels = vibe_sanitize(get_post_meta($course_id,'vibe_pmpro_membership',false));

		if(isset($current_user->membership_level->ID) && is_array($membership_levels)){
			if(in_array($current_user->membership_level->ID,$membership_levels)){
				$course_duration = get_post_meta($course_id,'vibe_duration',true);
				$course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400,$course_id);
				$course_duration = time() + $course_duration * $course_duration_parameter;
				$user_id = get_current_user_id();
				update_user_meta($user_id,$course_id,$course_duration);
				echo '<p class="message success">'.__('Course Renewed','vibe').' <a href="'.get_permalink($course_id).'" class="link right">'.__('Back to course','vibe').'</a></p>';
			}
		}
	}
}

?>
<div class="pmpro_content"> 
<?php
if($pmpro_msg)
{
?>
<div class="pmpro_message <?php echo $pmpro_msgt?>"><?php echo $pmpro_msg?></div>
<?php
}
?>
<table id="pmpro_levels_table" class="pmpro_checkout">
<thead>
  <tr>
	<th><?php _e('Level', 'vibe');?></th>
	<th><?php _e('Price', 'vibe');?></th>	
	<th>&nbsp;</th>
  </tr>
</thead>
<tbody>
	<?php	
	$count = 0;
	foreach($pmpro_levels as $level)
	{
	  if(isset($current_user->membership_level->ID))
		  $current_level = ($current_user->membership_level->ID == $level->id);
	  else
		  $current_level = false;
	?>
	<tr class="<?php if($count++ % 2 == 0) { ?>odd<?php } ?><?php if($current_level == $level) { ?> active<?php } ?>">
		<td><?php echo $current_level ? "<strong>{$level->name}</strong>" : $level->name?></td>
		<td>
			<?php 
				if(pmpro_isLevelFree($level))
					$cost_text = '<strong>'.__('Free','vibe').'</strong>';
				else
					$cost_text = pmpro_getLevelCost($level, true, true); 
				$expiration_text = pmpro_getLevelExpiration($level);
				if(!empty($cost_text) && !empty($expiration_text))
					echo $cost_text . "<br />" . $expiration_text;
				elseif(!empty($cost_text))
					echo $cost_text;
				elseif(!empty($expiration_text))
					echo $expiration_text;
			?>
		</td>
		<td>
		<?php if(empty($current_user->membership_level->ID)) { ?>
			<a class="button" href="<?php echo pmpro_url("checkout", "?level=" . $level->id, "https")?>"><?php echo _x('Select', 'Choose a level from levels page', 'vibe');?></a>
		<?php } elseif ( !$current_level ) { ?>                	
			<a class="button" href="<?php echo pmpro_url("checkout", "?level=" . $level->id, "https")?>"><?php echo _x('Select', 'Choose a level from levels page', 'vibe');?></a>
		<?php } elseif($current_level) { ?>      
			
			<?php
				//if it's a one-time-payment level, offer a link to renew				
				if(!pmpro_isLevelRecurring($current_user->membership_level))
				{
				?>
					<a class="button primary" href="<?php echo pmpro_url("checkout", "?level=" . $level->id, "https")?>"><?php echo _x('Renew', 'Clicking to renew from levels page', 'vibe');?></a>
				<?php
				}
				else
				{
				?>
					<a class="disabled" href="<?php echo pmpro_url("account")?>"><?php _e('Your&nbsp;Level', 'vibe');?></a>
				<?php
				}
			?>
			
		<?php } ?>
		</td>
	</tr>
	<?php
	}
	?>
</tbody>
</table>
<nav id="nav-below" class="navigation" role="navigation">
	<div class="nav-previous alignleft">
		<?php if(!empty($current_user->membership_level->ID)) { ?>
			<a href="<?php echo pmpro_url("account")?>"><?php _e('&larr; Return to Your Account', 'vibe');?></a>
		<?php } else { ?>
			<a href="<?php echo home_url()?>"><?php _e('&larr; Return to Home', 'vibe');?></a>
		<?php } ?>
	</div>
</nav>
</div>

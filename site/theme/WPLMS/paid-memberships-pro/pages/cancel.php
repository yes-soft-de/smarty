<?php 
	global $pmpro_msg, $pmpro_msgt, $pmpro_confirm;

	if($pmpro_msg) 
	{
?>
	<div class="message pmpro_message <?php echo $pmpro_msgt?>"><p><?php echo $pmpro_msg?></p></div>
<?php
	}
?>

<?php if(!$pmpro_confirm) { ?>           

<p><?php _e('Are you sure you want to cancel your membership?', 'vibe');?></p>

<p>
	<a class="pmpro_yeslink yeslink button" href="<?php echo pmpro_url("cancel", "?confirm=true")?>"><?php _e('Yes, cancel my account', 'vibe');?></a>
	|
	<a class="pmpro_nolink nolink button primary" href="<?php echo pmpro_url("account")?>"><?php _e('No, keep my account', 'vibe');?></a>
</p>
<?php } else { ?>
	<p><a href="<?php echo get_home_url()?>"><?php _e('Click here to go to the home page.', 'vibe');?></a></p>
<?php } ?>
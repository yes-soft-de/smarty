<?php
/**
 * Template Name: Email Page
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if(empty($_GET['vars'])){
	$message = __('PAGE CANNOT BE ACCESSED : MISSING EMAIL VARS ','vibe');
    wp_die($message,$message,array('back_link'=>true));
}else{
	get_header(vibe_get_header());
	$vars = json_decode(stripslashes(urldecode($_GET['vars'])));

	$template = get_option('wplms_email_template');
	if(isset($vars->to) && $vars->subject){
		if(is_object($vars->args)){
			$args = get_object_vars($vars->args);
		}else{
			$args ='';
		}
		$template = bp_course_process_mail($vars->to,$vars->subject,$vars->message,$args);
		if(!empty($vars->to)){
			$template = str_replace('{{name}}',$vars->to[0],$template);
		}
		echo '<iframe id="email_template" style="width:100%;height:800px;border:none;padding:30px;">'.$template.'</iframe>';
		?>
		<script>
		jQuery(document).ready(function($){
			$('iframe')[0].contentDocument.write('<?php echo addslashes($template); ?>');
		});
		</script>
		<?php
		
	}else{
		wp_redirect(home_url(),'302');	
	}	
}

get_footer(vibe_get_footer());


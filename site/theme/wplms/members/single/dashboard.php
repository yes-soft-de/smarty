<?php

/**
 * WPLMS- DASHBOARD TEMPLATE
 */

if ( !defined( 'ABSPATH' ) ) exit;

if(!is_user_logged_in()){
	wp_redirect(home_url(),'302');
}

get_header( vibe_get_header() ); 

$profile_layout = vibe_get_customizer('profile_layout');

vibe_include_template("profile/top$profile_layout.php");  
?>
<div class="wplms-dashboard row">
	<?php do_action( 'bp_before_dashboard_body' ); ?>
	<?php
		if(current_user_can('edit_posts')){
			$sidebar = apply_filters('wplms_instructor_sidebar','instructor_sidebar');
            if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : endif; 
		}else{
            $sidebar = apply_filters('wplms_student_sidebar','student_sidebar');
            if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : endif; 
		}
	?>
	<?php do_action( 'bp_after_dashboard_body' ); ?>
</div>	<!-- .wplms-dashbaord -->
<?php

vibe_include_template("profile/bottom.php");  

get_footer( vibe_get_footer() );  						
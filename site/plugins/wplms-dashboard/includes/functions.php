<?php

function wplms_get_random_color($i=NULL){
$color_array = array(
		'#7266ba',
 		'#23b7e5',
 		'#f05050',
 		'#fad733',
 		'#27c24c',
 		'#fa7252'
	);
if(isset($i)){
	if(isset($color_array[$i]))
	return $color_array[$i];
}
$k = array_rand($color_array);
return $color_array[$k];
}

function wplms_dashboard_template() {

	if(!is_user_logged_in())
		wp_redirect(site_url());

	$template ='templates/dashboard';
	global $bp;
    if($bp->current_component == 'dashboard'){ 
		wp_enqueue_style( 'wplms-dashboard-css',  WPLMS_DASHBOARD_URL.'/css/wplms-dashboard.css',array(),WPLMS_DASHBOARD_VERSION);
		wp_enqueue_script( 'wplms-dashboard-js', WPLMS_DASHBOARD_URL.'/js/wplms-dashboard.js',array('jquery','jquery-ui-sortable'),WPLMS_DASHBOARD_VERSION);
		if ( is_active_widget( false, false, 'wplms_instructor_dash_stats', true ) || is_active_widget( false, false, 'wplms_dash_stats', true )) {
			wp_enqueue_script( 'wplms-sparkline', WPLMS_DASHBOARD_URL.'/js/jquery.sparkline.min.js',array('jquery'),true);
		}
		if ( is_active_widget( false, false, 'wplms_instructor_stats', true ) || is_active_widget( false, false, 'wplms_instructor_commission_stats', true )
			|| is_active_widget( false, false, 'wplms_student_stats', true )) {
			wp_enqueue_script( 'wplms-raphael',WPLMS_DASHBOARD_URL.'/js/raphael-min.js',array('jquery'),true);
      		wp_enqueue_script( 'wplms-morris',WPLMS_DASHBOARD_URL.'/js/morris.min.js',array('jquery'),true);
		}
		$translation_array = array(
			'earnings' => __('Earnings','wplms-dashboard'),
			'payout'=>__('Payout','wplms-dashboard'),
			'students'=>__('# Students','wplms-dashboard'),
			'saved'=>__('SAVED','wplms-dashboard'),
			'saving'=>__('SAVING ...','wplms-dashboard'),
			'select_recipients' => __('Select recipients...','wplms-dashboard'),
			'stats_calculated'=>__('Stats Calculated, reloading page ...','wplms-dashboard')
			);
		wp_localize_script( 'wplms-dashboard-js', 'wplms_dashboard_strings', $translation_array );
	}
	

	$located_template = apply_filters( 'bp_located_template', locate_template( $template , false ), $template );	
	if ( $located_template && $located_template !='' )	{
		bp_get_template_part( apply_filters( 'bp_load_template', $located_template ) );
	}else{
	    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/dashboard' ) );
	}
}


add_action('widgets_init','wplms_dashboard_setup_sidebars');
function wplms_dashboard_setup_sidebars(){
if(function_exists('register_sidebar')){
	register_sidebar( array(
		'name' => __('Student Sidebar','wplms-dashboard'),
		'id' => 'student_sidebar',
		'before_widget' => '<div id="%1$s" class="%2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="dash_widget_title">',
		'after_title' => '</h4>',
        'description'   => __('This is the dashboard sidebar for Students','wplms-dashboard')
	) );
	register_sidebar( array(
		'name' => __('Instructor Sidebar','wplms-dashboard'),
		'id' => 'instructor_sidebar',
		'before_widget' => '<div id="%1$s" class="%2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="dash_widget_title">',
		'after_title' => '</h4>',
        'description'   => __('This is the dashboard sidebar for Instructors','wplms-dashboard')
	) );
    }
}







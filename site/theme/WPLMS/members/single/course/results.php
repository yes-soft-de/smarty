<?php do_action( 'bp_before_course_results' ); ?>

<?php 
$user_id=get_current_user_id();

if(isset($_GET['action']) && !is_numeric($_GET['action'])){
		echo '<div id="message"><p>'.__('Invalid Results','vibe').'</p></div>';
}else{

	if(isset($_GET['action']) && $_GET['action']){ // Check Action
		$id = intval($_GET['action']);
		$post_type = get_post_type($id);
		do_action('wplms_get_'.$post_type.'_result',$id,$user_id);
	}else{ // Show all Results 
		do_action('wplms_get_user_results',$user_id);
	}

}
?>
<?php do_action( 'bp_after_course_results' ); ?>
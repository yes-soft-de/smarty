<?php
/**
 * The template for displaying Course members
 *
 * Override this template by copying it to yourtheme/course/single/members.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.1
 */
if ( !defined( 'ABSPATH' ) ) exit;
global $post;
$students=get_post_meta(get_the_ID(),'vibe_students',true);

$course_layout = vibe_get_customizer('course_layout');

if(empty($course_layout)){
?>
<div class="course_title">
	<h1><?php the_title(); ?></h1>
</div>
<?php
}
?>

<?php
$loop_number=vibe_get_option('loop_number');
if(!isset($loop_number)) $loop_number = 5;
if(function_exists('bp_course_get_course_students')){
	$course_students=bp_course_get_course_students();
	$students_undertaking = $course_students['students'];
	$max_page = ceil($course_students['max']/$loop_number);
}else{
	$students_undertaking=bp_course_get_students_undertaking();	
	$max_page = ceil(count($students_undertaking)/$loop_number);
}


if(count($students_undertaking) > 0 ){
	?>
	<h4 class="total_students"><?php _e('Total number of Students in course','vibe'); ?><span><?php echo $students; ?></span></h4>
	<form id="course_user_ajax_search_results" data-id="<?php echo get_the_ID(); ?>">
		<select id="course_status">
			<option value=""><?php _ex('Filter by Status','course-members-select','vibe'); ?></option>
			<?php
				$sorters = apply_filters('wplms_course_members_sorters',array(
					'recent'=> _x('Recently joined','course-members-select','vibe'),
					'alphabetical'=> _x('Alphabetical','course-members-select','vibe'),
					'toppers'=> _x('Top scorers','course-members-select','vibe'),
					));
				foreach($sorters as $sort => $label){
					echo '<option value="'.$sort.'">'.$label.'</option>';
				}
			?>
		</select>
		<span id="search_course_member">
			<input type="text" name="search" placeholder="<?php _e('Enter student name/email','vibe'); ?>">
		</span>
	 </form>
	<?php
	
	echo '<ul class="course_students">';
	foreach($students_undertaking as $student){

		if (function_exists('bp_get_profile_field_data')) {
		    $bp_name = bp_core_get_userlink( $student );
		    $sfield=vibe_get_option('student_field');
		    if(!isset($sfield) || $sfield =='')
		    	$sfield = 'Location';

		    $bp_location ='';
		    if(bp_is_active('xprofile'))
		    $bp_location = bp_get_profile_field_data('field='.$sfield.'&user_id='.$student);
		    
		    if ($bp_name) {
		    	echo '<li>';
		    	echo get_avatar($student);
		    	echo '<h6>'. $bp_name . '</h6>';
			    if ($bp_location) {
			    	echo '<span>'. $bp_location . '</span>';
			    }

			    echo '<div class="action">';
			    $check_meta = vibe_get_option('members_activity');
			    if(bp_is_active('friends') && $check_meta){
			    	if(function_exists('bp_add_friend_button')){
				    	bp_add_friend_button( $student );
				    }
			    }
			    echo '</div></li>';
		    }
		    
		}
	}
	echo '<li><div class="pagination"><div><div class="pag-count" id="course-member-count">'.sprintf(__('Viewing page %d of %d ','vibe'),1,$max_page).'</div>';
	echo '<div class="pagination-links"><span class="page-numbers current">'._x('1','pagination number course admin','vibe').'</span>';
	$f=$g=1;
	if($max_page > 1){
		for($i=2;$i<=$max_page;$i++ ){
			if($i == 2 || $i == $max_page){
				echo '<a class="page-numbers course_admin_paged">'.$i.'</a>';
			}else if($f && $i >= 3 && $i < $max_page){
				echo '<a class="page-numbers">...</a>'; 
				$f=0;
			}
		}
	}
	echo '</div></div></div></li>';
	echo '</ul>';
	wp_nonce_field('security'.get_the_ID(),'bulk_action');
}else{
	echo '<div class="message">'._x('No members found in this course.','No members notification in course - members','vibe').'</div>';
}

?>
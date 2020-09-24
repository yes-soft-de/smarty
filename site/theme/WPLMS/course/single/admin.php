<?php
/**
 * The template for displaying Course Admin section
 *
 * Override this template by copying it to yourtheme/course/single/admin.php
 *
 * @author 		VibeThemes
 * @package 	vibe-course-module/templates
 * @version     2.3
 */

if ( !defined( 'ABSPATH' ) ) exit;
$user_id=get_current_user_id();
$action = bp_current_action();

if(empty($action)){
  (!empty($_GET['action'])?$action=$_GET['action']:$action='');
}

$course_admin_tabs = apply_filters('wplms_course_admin_tabs',array(
	'admin'=> array('name'=> _x('Members','Course admin tab name','vibe'),'url'=>get_permalink(get_the_ID()).apply_filters('wplms_course_admin_slugs','?action=admin')),
	'submissions'=>array('name'=> _x('Submissions','Course admin tab name','vibe'),'url'=>get_permalink(get_the_ID()).apply_filters('wplms_course_admin_slugs','?action=admin&submissions')),
	'stats'=>array('name'=> _x('Stats','Course admin tab name','vibe'),'url'=>get_permalink(get_the_ID()).apply_filters('wplms_course_admin_slugs','?action=admin&stats')),
	));
?>
<div class="item-list-tabs no-ajax " id="subnav" role="navigation">
	<ul>
		<?php
			foreach($course_admin_tabs as $tab_action => $tab){
		?>
		<li class="course_sub_action <?php if(!empty($action) && $action == $tab_action){ echo 'current'; } ?>">
			<a id="course_<?php echo $tab_action; ?>" href="<?php echo $tab['url']; ?>"><?php echo $tab['name'];; ?></a>
		</li>
		<?php
		}
		?>
	</ul>
</div>
<div id="message" class="info vnotice">
  <?php do_action('bp_course_custom_notice_instructors'); ?>
</div>
<?php

if(isset($_GET['submissions']) || $action == 'submissions'){

locate_template( array( 'course/single/submissions.php'  ), true );

}else if(isset($_GET['stats'])  || $action == 'stats'){

	locate_template( array( 'course/single/stats.php'  ), true );
}else{

	$tab_content = apply_filters('wplms_course_admin_tab_content',0,$action);
	global $post;
	if(function_exists('bp_course_get_students_count')){
		$students=bp_course_get_students_count(get_the_ID());
	}else{
		$students=get_post_meta(get_the_ID(),'vibe_students',true);	
	}

$loop_number=vibe_get_option('loop_number');
if(!isset($loop_number)) $loop_number = 5;
	
?>	
	<h4 class="total_students"><?php _e('Total number of Students in course','vibe'); ?><span><?php echo $students; ?></span></h4>
	<?php

	if(function_exists('bp_course_get_course_students')){
		$course_students=apply_filters('bp_course_admin_before_course_students_list',bp_course_get_course_students(),get_the_ID());
		$students_undertaking = $course_students['students'];
		$max_page = ceil($course_students['max']/$loop_number);
	}else{
		$students_undertaking=apply_filters('bp_course_admin_before_course_students_list',bp_course_get_students_undertaking(),get_the_ID());	
		$max_page = ceil(count($students_undertaking)/$loop_number);
	}
	
	
	echo '<ul class="course_students">';
	if(count($students_undertaking)>0){
		foreach($students_undertaking as $student){

			if(function_exists('bp_course_admin_userview')){
				
				bp_course_admin_userview($student);

			}else{
				if (function_exists('bp_get_profile_field_data')) {
				    $bp_name = bp_core_get_userlink( $student );

				    $bp_location='';

				    $ifield = vibe_get_option('student_field');
					if(!isset($field) || $field =='')$field='Location';
				    if(bp_is_active('xprofile'))
				    $bp_location = bp_get_profile_field_data('field='.$field.'&user_id='.$student);
				    
				    if ($bp_name) {
				    	echo '<li id="s'.$student.'"><input type="checkbox" class="member" value="'.$student.'"/>';
				    	echo get_avatar($student);
				    	echo '<h6>'. $bp_name . '</h6><span>';
					    if ($bp_location) {
					    	echo $bp_location;
					    }
					    
					    if(function_exists('bp_course_user_time_left')){
					    	echo ' ( '; bp_course_user_time_left(array('course'=>get_the_ID(),'user'=>$student));
					    	echo ' ) ';
					    }

					    echo '</span>';
					    do_action('wplms_user_course_admin_member',$student,get_the_ID());
					    // PENDING AJAX SUBMISSIONS
					    echo '<ul> 
					    		<li><a class="tip reset_course_user" data-course="'.get_the_ID().'" data-user="'.$student.'" title="'.__('Reset Course for User','vibe').'"><i class="icon-reload"></i></a></li>
					    		<li><a class="tip course_stats_user" data-course="'.get_the_ID().'" data-user="'.$student.'" title="'.__('See Course stats for User','vibe').'"><i class="icon-bars"></i></a></li>';
						if(class_exists('WPLMS_tips')){
							$tips = WPLMS_tips::init();
	                        if(!empty($permalinks) && empty($tips->settings['revert_permalinks'])){
								$permalinks = Vibe_CustomTypes_Permalinks::init();		
							    if(empty($permalinks) || empty($permalinks->permalinks) || empty($permalinks->permalinks['activity_slug'])){
							    	$activity_slug = '/activity';
							    	$activity_slug = str_replace('/','',$activity_slug).'?';
							    }else{
							    	$activity_slug = $permalinks->permalinks['activity_slug'];
							    	$activity_slug = str_replace('/','',$activity_slug).'?';
							    }	
							}else{
								$activity_slug = '?action='.BP_ACTIVITY_SLUG.'&';
							}
						}				    		
					    
			    		echo '<li><a href="'.get_permalink().$activity_slug.'student='.$student.'" class="tip" title="'.__('See User Activity in Course','vibe').'"><i class="icon-atom"></i></a></li>
					    		<li><a class="tip remove_user_course" data-course="'.get_the_ID().'" data-user="'.$student.'" title="'.__('Remove User from this Course','vibe').'"><i class="icon-x"></i></a></li>
					    		'.do_action('wplms_course_admin_members_functions',$student,get_the_ID()).'
					    	  </ul>';
					    echo '</li>';
				    }
				}
			}
	}// End foreach
		}else{
			
			echo '<div id="message" class="notice"><p>'.__('No Students found.','vibe').'</p></div>';
		}
		if(count($students_undertaking)>0){

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
		}
		echo '</ul>';
		
		echo '<div class="course_bulk_actions">
				<strong>'.__('BULK ACTIONS','vibe').'</strong> ';

		do_action('wplms_course_admin_bulk_actions');
		wp_nonce_field('security'.get_the_ID(),'bulk_action');
		
		echo '</div>';
	
  }
?>
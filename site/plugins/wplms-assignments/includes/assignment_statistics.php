<?php

if (!defined('ABSPATH')) { exit; }

if (!class_exists('WPLMS_Assignment_Stats')){
    class WPLMS_Assignment_Stats{

	    public static $instance;
	    
	    var $schedule;

	    public static function init(){
	        if ( is_null( self::$instance ) )
	            self::$instance = new WPLMS_Assignment_Stats();
	        return self::$instance;
	    }

	    public function __construct(){
	    	add_filter('lms_stats_tabs',array($this,'add_tab'));

	    	add_action('lms_stats_tab_assignment_output',array($this,'assignment_stats'));

	    	add_filter('lms_stats_assignment_subtabs',array($this,'subtabs'));
	    	add_action('lms_stats_tab_assignment_subtab_overview_output',array($this,'assignment_stats'));

	    }


	    function add_tab($tabs){
	    	$tabs['assignment'] = _x('Assignments','Tab name in LMS stats','wplms-assignments');
	    	return $tabs;
	    }

	    function subtabs($subtabs){
	    	echo '<h2>'._x('Assignment Statistics','LMS statistics','wplms-assignments').'</h2>';
	    	$subtabs['assignment'] = array(
	    		'overview' => __('Overview','wplms-assignments'),
    		);

	    	return $subtabs;
	    }


	    function assignment_stats(){

	    	$stats = array(
	    		'count' => 0,
	    		'submissions' => 0,
	    		'average' => 0,
	    		);

	    	$num = 20;
			$paged=0;
			if($_REQUEST['paged'] && is_numeric($_REQUEST['paged'])){
				$paged=$_REQUEST['paged'];
			}
			$page_num=0;
			if(isset($_GET['paged']) && is_numeric($_GET['paged']) && $_GET['paged'])
					$page_num=($_GET['paged'])*$num;

	    	global $wpdb;
	    	if(current_user_can('manage_options')){
	    		
	    		$assignments_q = $wpdb->get_results("
	    			SELECT ID,post_title, comment_count 
	    			FROM {$wpdb->posts}
	    			WHERE post_type = 'wplms-assignment'
	    			AND post_status = 'publish'
	    			LIMIT 0,999
	    			");

	    	}else if(current_user_can('edit_posts')){
	    		$user_id = get_current_user_id();
	    		$assignments_q = $wpdb->get_results("
	    			SELECT ID,post_title,comment_count 
	    			FROM {$wpdb->posts}
	    			WHERE post_type = 'wplms-assignment'
	    			AND post_status = 'publish'
	    			AND post_author = $user_id
	    			LIMIT 0,999
	    			");
	    	}

	    	$assignments = array();
	    	if(!empty($assignments_q)){
	    		$stats['count'] = count($assignments_q);
	    		foreach($assignments_q as $assignment_q){
	    			$stats['submissions'] += $assignment_q->comment_count;
	    			$assignments[$assignment_q->ID] = array('title'=>$assignment_q->post_title);
	    		}


	    		$assignment_ids = array_keys($assignments);
	    		$assignment_ids = implode(',',$assignment_ids );
	    		$assignments_averages = $wpdb->get_results("
	    			SELECT comment_post_ID as assignment_id,COUNT(cm.meta_value) as submissions, AVG(cm.meta_value) as average
	    			FROM {$wpdb->comments} as c 
	    			LEFT JOIN {$wpdb->commentmeta} as cm 
	    			ON c.comment_id = cm.comment_ID 
	    			WHERE c.comment_post_ID IN ($assignment_ids) 
	    			AND cm.meta_key ='marks' 
	    			GROUP BY comment_post_ID LIMIT 0,999
	    			");

	    		if(!empty($assignments_averages)){
	    			foreach($assignments_averages as $avg){
	    				if(!empty($avg->submissions)){
	    					$assignments[$avg->assignment_id]['submissions'] = $avg->submissions;	
	    				}
	    				
	    				if(!empty($avg->average)){
	    					$marks = get_post_meta($avg->assignment_id,'vibe_assignment_marks',true);
	    				}
	    				
	    				$assignments[$avg->assignment_id]['average'] = round(100*$avg->average/$marks,2);
	    			}
	    		}
	    	}

	    	extract($stats);

	    	?>
	    	<div class="vibe-reports-wrap">
		    	<div class="vibe-reports-sidebar">
					<div class="postbox">
						<div class="inside">
							<h3><span><?php _ex('Total Assignments','LMS Stats','wplms-assignments'); ?></span></h3>
							<p class="stat"><?php echo $count; ?></p>
						</div>
					</div>
					<div class="postbox">
						<div class="inside">
							<h3><span><?php _ex('Total Assignment Submissions','LMS Stats','wplms-assignments'); ?></span></h3>
							<p class="stat"><?php echo $submissions; ?></p>
						</div>
					</div>
				</div>
				<div class="vibe-reports-main">
					<div class="postbox course_info">
						<h3><label>Assignment Title</label><span># Submissions</span><span>Average (%)</span></h3>
						<div class="inside">
							<ul>
							<?php
							if(!empty($assignments)){
								foreach($assignments as $assignment_id => $assignment){
									echo '<li><label>'.$assignment['title'].'</label><span>'.$assignment['submissions'].'</span><span>'.$assignment['average'].'</span>';
								}
							}else{
								echo 'N.A';
							}

							?>
							</ul>
							<?php
							if(isset($_GET['paged']) && $_GET['paged']){
								echo '<a href="?page=lms-stats&tab=assignment&subtab='.$_GET['subtab'].'&paged='.($_GET['paged']-1).'" class="button">&lsaquo; '.__('Prev','wplms-assignments').'</a>';
							}
							if($total == $num){
								if(isset($_GET['paged']) && $_GET['paged']){
									$paged =$_GET['paged'];
									echo '&nbsp;&nbsp;';
								}else{
									$paged = 0;
								}
								echo '<a href="?page=lms-stats&tab=assignment&subtab='.$_GET['subtab'].'&paged='.($paged+1).'" class="button">'.__('Next','wplms-assignments').' &rsaquo;</a>';
							}
							
							?>
						</div>
					</div>		
			</div>
		</div>
	    	<?php
	    	
	    }
	}	
}

add_action('admin_init',function(){
	WPLMS_Assignment_Stats::init();
});
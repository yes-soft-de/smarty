<?php

/**
 * This File contains the Course Template Tag functions.
 * These are very important functions used for majorly in the theme and addon plugins.
 * Any input/output to the database happens via these functions.
 *
 * In our course here, we've used a custom post type for storing and fetching our content. Though
 * the custom post type method is recommended, you can also create custom database tables for this
 * purpose. See bp-course-classes.php for more details.
 *
 */

 if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * This function generates the list of courses for the course loop.
 * Function is used by ajax calls for filtering and sorting the course directory.
 */

Class BP_Course_Template{

	private $instructor;
	private $post_type;
	private $curriculum;
	private $course_credits;


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new BP_Course_Template();
        return self::$instance;
    }

	private function __construct(){

		$this->curriculum = array();
	}

	function get_post_type($id){
		// cache checks
		if(isset($this->post_type[$id]))
			return $this->post_type[$id];

		$this->post_type[$id] = get_post_type($id);
		return $this->post_type[$id];
	}

	function get_instructor($args=NULL){

		$defaults = array(
			'instructor_id' => get_the_author_meta( 'ID' ),
			'post_id' => get_the_ID(),
			'field' => 'Expertise',
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if(isset($this->instructors[$post_id.'_'.$instructor_id])){
			return $this->instructors[$post_id.'_'.$instructor_id];
		}

		if(function_exists('vibe_get_option'))
			$field = vibe_get_option('instructor_field');
	
		$displayname = bp_core_get_user_displayname($instructor_id);
		$special='';
		if(bp_is_active('xprofile'))
		$special = bp_get_profile_field_data('field='.$field.'&user_id='.$instructor_id);

		$instructor = '<div class="instructor_course">
						<div class="item-avatar">'.bp_course_get_instructor_avatar(array('item_id'=>$instructor_id)).'</div>
						<h5 class="course_instructor"><a href="'.bp_core_get_user_domain($instructor_id) .'">'.$displayname.'<span>'.$special.'</span></a>
						</h5>';
		$instructor .= apply_filters('wplms_instructor_meta','',$instructor_id);				
		$instructor .='</div>';
		$this->instructors[$post_id.'_'.$instructor_id] = apply_filters('wplms_display_course_instructor',$instructor,$post_id);
		return $this->instructors[$post_id.'_'.$instructor_id];
	}

	function get_course_meta($course_id){
		
		if(isset($this->course_meta[$course_id]))
			return $this->course_meta[$course_id];

		$rating=get_post_meta($course_id,'average_rating',true);
		$count=get_post_meta($course_id,'rating_count',true);

		if(empty($rating)){
			$reviews_array=bp_course_get_course_reviews(array('id'=>$course_id));	
			if(is_array($reviews_array) && !empty($reviews_array)){
				$rating = $reviews_array['rating'];
				$count = $reviews_array['count'];
			}else{
				$rating = $count = 0;
			}
		}

		$meta ='';

		$meta .= bp_course_display_rating($rating);

		$meta .= '<strong>( '.(empty($count)?'0':$count).' '.__('REVIEWS','vibe').' )</strong> ';
		$students = get_post_meta($course_id,'vibe_students',true);
		
		if(!isset($students) && $students =''){$students=0;update_post_meta($course_id,'vibe_students',0);} // If students not set

		$meta .='<div class="students"> '.$students.' '.__('STUDENTS','vibe').'</div>';
		$this->course_meta[$course_id] = apply_filters('wplms_course_meta',$meta);
		return $this->course_meta[$course_id];
	}



	/* Curriculum
	*/
	function get_curriculum($course_id){
		if(empty($this->curriculum[$course_id])){
			$this->curriculum[$course_id]= get_post_meta($course_id,'vibe_course_curriculum',true);	
		}
		return $this->curriculum[$course_id];
	}

	/*
	GET COURSE CREDITS
	 */
	
	function get_course_credits($args){

		$defaults = array(
			'id' => get_the_ID(),
			'currency' => 'CREDITS',
			'bypass' => 0,
			'partial' => 0
			);
		
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if(isset($this->course_credits[$id]))
			return $this->course_credits[$id];

		$private = 0;
		$credits_html = '';
		$credits = array();
		if(!empty($bypass)){
			if(is_user_logged_in()){
				$user_id = get_current_user_id();
				$expire_check = get_user_meta($user_id,$id,true);
				if($expire > time()){
					return '';
				}
			}
		}

		$free_course = get_post_meta($id,'vibe_course_free',true);
		$apply_course = get_post_meta($id,'vibe_course_apply',true);

		if(vibe_validate($free_course)){
			$credits[] = '<strong>'.apply_filters('wplms_free_course_price',__('FREE','vibe')).'</strong>';
		}else if(vibe_validate($apply_course)){
			$credits[] = '<strong>'.apply_filters('wplms_course_application_label',__('Apply to enroll','vibe')).'</strong>';
		}else{
			$product_id = get_post_meta($id,'vibe_product',true);
			if(isset($product_id) && $product_id !='' && function_exists('wc_get_product')){ //WooCommerce installed
				$product = wc_get_product( $product_id );
				if(is_object($product)){
					$link = get_permalink($product_id);
					$check = vibe_get_option('direct_checkout');
        			if(isset($check) && $check)
        				$link .= '?redirect';
        			$price_html = str_replace('class="amount"','class="amount"',$product->get_price_html());
					$credits[$link] = '<strong>'.$price_html.'</strong>';
				}
			}
		
	    if ( in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

			$membership_ids = vibe_sanitize(get_post_meta($id,'vibe_pmpro_membership',false));
			if(isset($membership_ids) && is_Array($membership_ids) && count($membership_ids) && function_exists('pmpro_getAllLevels')){
			//$membership_id = min($membership_ids);
			$levels=pmpro_getAllLevels();
				foreach($levels as $level){
					if(in_array($level->id,$membership_ids)){
						$link = get_option('pmpro_levels_page_id');
						$link = get_permalink($link).'#'.$level->id;
						$credits[$link] = '<strong>'.$level->name.'</strong>';
					}
				}
		    }
		  }

			$course_credits = get_post_meta($id,'vibe_course_credits',true);
			if(isset($course_credits) && $course_credits != '' ){
				$credits[] = '<strong>'.$course_credits.'</strong>';
			}
		} // End Else

		$credits = apply_filters('wplms_course_credits_array',$credits,$id);

		if(count($credits) > 1 ){

			$credits_html .= '<div class="pricing_course">
    								<div class="result"><span>'.__('Price Options +','vibe').'</span></div>
								    <div class="drop">';
								    $first = 1;
									foreach($credits as $key => $credit){
										$credits_html .= '<label data-value="'.$key.'"><span class="font-text">'.$credit.'</span></label>';
										$first = 0;
									}
								        
					    $credits_html .= '</div>
								</div>';

		}else if(count($credits)){
			foreach($credits as $credit)
			$credits_html .= $credit;
			if(is_singular('course'))
				$credits_html .= '<i class="icon-wallet-money right"></i>';
		}

		$credits_html .= '';
		if($partial){
			$this->course_credits[$id] = apply_filters('wplms_course_partial_credits',$credits_html,$id);
		}else{
			$this->course_credits[$id] = apply_filters('wplms_course_credits',$credits_html,$id);
		}

		return $this->course_credits[$id];
	}

	/*
	GET Course UNDERTAKING STUDENTS
	 */
	function get_students_undertaking($course_id,$loop_number = null){

		if(isset($this->students_undertaking[$course_id])){
			return $this->students_undertaking[$course_id];
		}
		$course_members = array();

		if(empty($loop_number)){
			$loop_number = vibe_get_option('loop_number');
			if(!isset($loop_number)) $loop_number = 5;
		}

		$page_num = 0;
		if(!empty($_REQUEST['items_page'])){$page_num=$_REQUEST['items_page']-1;}
		global $wpdb;
		$cquery = $wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s ORDER BY meta_value ASC LIMIT %d, %d",'course_status'.$course_id,($page_num*$loop_number),$loop_number);
		$course_meta = $wpdb->get_results( $cquery, ARRAY_A);

		foreach($course_meta as $meta){
			if(is_numeric($meta['user_id']))  // META KEY is NUMERIC ONLY FOR USERIDS
				$course_members[] = $meta['user_id'];
		}

		$this->students_undertaking[$course_id] = $course_members;
		return $this->students_undertaking[$course_id];
	}

	function count_pursuing_students($course_id){

		if(isset($this->pursuing_student_count[$course_id]))
			return $this->pursuing_student_count[$course_id];

		global $wpdb,$post;
		$course_members = array();
		$number = apply_filters('bp_course_count_students_pursuing',0,$course_id);
		
		if(empty($number)){
			$number = $wpdb->get_var( $wpdb->prepare("select count(user_id) as number from {$wpdb->usermeta} where meta_key = %s",'course_status'.$course_id));
		}
		$this->pursuing_student_count[$course_id] = $number;
		return $this->pursuing_student_count[$course_id];
	}

	/*
	
	 */
	function get_user_course_count($user_id){

		if(isset($this->user_course_count[$user_id]))
			return $this->user_course_count[$user_id];

		global $wpdb;

		$c = $wpdb->get_var("SELECT count(p.ID) FROM {$wpdb->posts} as p LEFT JOIN {$wpdb->postmeta} as pm ON pm.post_id = p.ID WHERE pm.meta_key  = $user_id and p.post_status = 'publish' and p.post_type = 'course'");

		
		if(!isset($c) || !$c) $c = 0;
		$this->user_course_count[$user_id] = apply_filters( 'wplms_get_total_course_count', $c, $user_id );
		return $this->user_course_count[$user_id];
	}

	/*
	GET MARKED QUESTION ANSWER
	 */
	
	function get_answer_object($quiz_id,$question_id,$user_id){

		if(!empty($this->answer[$quiz_id.'_'.$question_id.'_'.$user_id])){
			return $this->answer[$quiz_id.'_'.$question_id.'_'.$user_id];
		}

		$comments_query = new WP_Comment_Query;
	    $comments = $comments_query->query( apply_filters('wplms_get_answer_object_meta_query',array(
	    	'post_id'=> $question_id,
	    	'user_id'=>$user_id,
	    	'number'=>1,
	    	'status'=>'approve',
	    	'meta_query'=>array(
	    		array(
	    			'key'=> 'quiz_id',
		    		'value'=> $quiz_id,
		    		'compare'=> '='
	    		)
    		),
	    	), $question_id,$user_id,$quiz_id)
	    ); 
	    /*
	    Fallback removed for older versions
	    */
	    if(empty($comments)){
	    	$comments = $comments_query->query( array(
	    	'post_id'=> $question_id,
	    	'user_id'=>$user_id,
	    	'number'=>1,
	    	'status'=>'approve',
	    	'meta_query'=>array(
	    		array(
	    			'key'=> 'quiz_id',
		    		'compare'=> 'NOT EXISTS'
	    		)
    		),
    		));
    		if(!empty($comments)){update_comment_meta($comments[0]->comment_ID,'quiz_id',$quiz_id);}
	    }
	    $comments = apply_filters('wplms_get_answer_object',$comments,$quiz_id,$question_id,$user_id);
	    if(isset($comments) && isset($comments[0])){
	    	$this->answer[$quiz_id.'_'.$question_id.'_'.$user_id] = $comments[0];
	    	return $comments[0];	
	    }else{
	    	return '';
	    }
	    
	}
	
	function marked_answer_id($quiz_id,$question_id,$user_id){
		$comment = $this->get_answer_object($quiz_id,$question_id,$user_id);
		if(empty($comment)){return;}else{
			return $comment->comment_ID;
		}
	}

	function get_marked_answer($quiz_id,$question_id,$user_id){
		$comment = $this->get_answer_object($quiz_id,$question_id,$user_id);
		if(empty($comment)){return;}
	    $comment->comment_content = stripslashes($comment->comment_content);
	  	$comment->comment_content = trim($comment->comment_content,',');
	  	return $comment->comment_content;
	}

	function update_answer_marks($quiz_id,$question_id,$user_id,$marks){
		$comment = $this->get_answer_object($quiz_id,$question_id,$user_id);
		update_comment_meta($comment->comment_ID,'marks',$marks);
		return;
	}

	function get_answer_marks($quiz_id,$question_id,$user_id){
		$comment = $this->get_answer_object($quiz_id,$question_id,$user_id);
		if(empty($comment)){return;}
		$marks = get_comment_meta($comment->comment_ID,'marks',true);
		return $marks;
	}

	/*
	DRIP FUNCTIONS
	 */
	function check_course_drip($user_id,$course_id){

		if(!empty($this->drip_enable[$course_id.'_'.$user_id])){
			return $this->drip_enable[$course_id.'_'.$user_id];
		}

		$enable = apply_filters('wplms_course_drip_switch',0,$course_id,$user_id);

		if(empty($enable)){
			$enable = get_post_meta($course_id,'vibe_course_drip',true);
		}

		if($enable != 'S'){
			$this->drip_enable[$course_id.'_'.$user_id] = false;
		}else{
			$this->drip_enable[$course_id.'_'.$user_id] = true;
		}

		return $this->drip_enable[$course_id.'_'.$user_id];
	}

	function get_total_course_drip_duration($user_id,$course_id){

		if(!empty($this->total_drip[$course_id.'_'.$user_id])){
			return $this->total_drip[$course_id.'_'.$user_id];
		}

		$total_duration = apply_filters('bp_course_get_total_drip_duration',0,$course_id,$user_id);
		if(empty($total_duration)){
			$drip_duration = get_post_meta($course_id,'vibe_course_drip_duration',true);
        	$drip_duration_parameter = apply_filters('vibe_drip_duration_parameter',86400,$course_id);

        	$this->total_drip[$course_id.'_'.$user_id] = $drip_duration*$drip_duration_parameter;
		}
		return $this->total_drip[$course_id.'_'.$user_id];
	}	

	function get_drip_access_time($unit_id,$user_id,$course_id=null){

		if(empty($course_id)){
			$time = get_post_meta($unit_id,$user_id,true);	//older versions
			return $time;
		}else{

			if(!empty($this->drip_access_time[$unit_id.'_'.$user_id.'_'.$course_id])){
				return $this->drip_access_time[$unit_id.'_'.$user_id.'_'.$course_id];
			}

			$origin = get_post_meta($course_id,'vibe_course_drip_origin',true);
			
			if(!empty($origin) && $origin == 'S'){

				$addon_duration = 0; // Add the duration time based on Unit Number to the Start course time base

				$start_date = bp_course_get_start_date($course_id,$user_id);
				if(empty($start_date)){
					$start_timestamp = get_user_meta($user_id,'start_course_'.$course_id,true);
					if(empty($start_timestamp)){
						if(function_exists('bp_is_active') && bp_is_active('activity')){
							//GET START DATE?TIME FROM ACTIVITY
							global $wpdb,$bp;
							$start_date = $wpdb->get_var("SELECT date_recorded FROM {$bp->activity->table_name} WHERE user_id = $user_id AND item_id = $course_id AND component = 'course' AND type = 'start_course' ORDER BY id DESC LIMIT 0,1");

							if(empty($start_date)){
								$start_timestamp = time();
								update_user_meta($user_id,'start_course_'.$course_id,time());
							}else{
								$start_timestamp = strtotime($start_date);
								update_user_meta($user_id,'start_course_'.$course_id,$start_timestamp);
							}
						}else{
							$start_timestamp = time();
							update_user_meta($user_id,'start_course_'.$course_id,time());
						}
					}
				}else{
					$start_timestamp = strtotime($start_date);
				}

				
				$curriculum = bp_course_get_curriculum_units($course_id);
				$key = array_search($unit_id,$curriculum);

				//CHECK IF DYNAMIC DURATION OR STATIC DURATION
				
				$dynamic = get_post_meta($course_id,'vibe_course_drip_duration_type',true);

				if(empty($dynamic) || $dynamic == 'H'){ // STATIC DRIP
					
					$drip_duration = get_post_meta($course_id,'vibe_course_drip_duration',true);
					$drip_duration_parameter = apply_filters('vibe_drip_duration_parameter',86400,$course_id);
					$section_drip = get_post_meta($course_id,'vibe_course_section_drip',true);
					if($section_drip == 'S'){
						$course_full_curriculum= bp_course_get_curriculum($course_id);
						$pre_section_key = 0;
						$post_section_key = 0;
						$is_unit_in_section_key = array_search($unit_id,$course_full_curriculum);

						foreach($course_full_curriculum as $i=>$c){
							#current_section start and end
							if(!empty($c) && !is_numeric($c) && empty($pre_section_key)){
								$pre_section_key = $i;
							}
							
							if(!empty($c) && !is_numeric($c) && !empty($pre_section_key) && empty($post_section_key)){
								$post_section_key = $i;
							}

							if((!empty($pre_section_key) && !empty($post_section_key))){
								if($is_unit_in_section_key > $post_section_key){
									$addon_duration +=$drip_duration*$drip_duration_parameter;
									$pre_section_key =$post_section_key;
									$post_section_key=0;
								}
							}

						}
					}else{
						foreach($curriculum as $i=>$c){
							if($i < $key && bp_course_get_post_type($c) == 'unit'){
								$addon_duration +=$drip_duration*$drip_duration_parameter;
							}
						}	
					}
					

				}else{
					
					foreach($curriculum as $i=>$c){
						if($i < $key && bp_course_get_post_type($c) == 'unit'){					
							$addon_duration +=  bp_course_get_unit_duration($c);

						}
					}
				}

				// ADDUP THE TWO TIMES
				$time = $start_timestamp + $addon_duration;

			}else{

				$time = get_user_meta($user_id,'start_unit_'.$unit_id.'_'.$course_id,'true');

				/* === THIS CODE WILL BE REMOVED IN LATER VERSIONS === */
				if(empty($time)){ //Content not updated
					$time = get_post_meta($unit_id,$user_id,true);
					if(!empty($time)){
						update_user_meta($user_id,'start_unit_'.$unit_id.'_'.$course_id,$time); // UPDATE DATA
					}
				}
				/* ==== BACKWARD COMPATIBILITY ==== */
			}
			$this->drip_access_time[$unit_id.'_'.$user_id.'_'.$course_id] = $time;
			return $this->drip_access_time[$unit_id.'_'.$user_id.'_'.$course_id];
		}
	}
}

function bp_course_has_items( $args = '' ) {
	global $bp, $items_template;

	// This keeps us from firing the query more than once
	if ( empty( $items_template ) ){
		/***
		 * Set the defaults for the parameters you are accepting via the "bp_get_course_has_items()"
		 * function call
		 */
		$defaults = array(
			'id' => 0,
			'date' 	=> date( 'Y-m-d H:i:s' ),
			'user' => 0,
			'slug' => '',
			'search_terms'    => '',
			'meta_query'      => '',
			'order'           => 'DESC',
			'orderby'         => '',
			'paged'            => 1,
			'per_page'        => 5,
		);

		$slug    = false;
		$type    = '';
		$user_id = 0;
		$order   = 'DESC';

		// Type
		// @todo What is $order? At some point it was removed incompletely?
		if ( bp_is_current_action( BP_COURSE_SLUG ) ) {
			if ( 'most-popular' == $order ) {
				$type = 'popular';
			} elseif ( 'alphabetically' == $order ) {
				$type = 'alphabetical';
			}
		} elseif ( isset( $bp->course->current_course->slug ) && $bp->course->current_course->slug ) {
			$type = 'single-course';
			$slug = $bp->course->current_course->slug;
		}

		/***
		 * This function will extract all the parameters passed in the string, and turn them into
		 * proper variables you can use in the code - $per_page, $max
		 */
		$r = wp_parse_args( $args, $defaults );

		extract( $r, EXTR_SKIP );
		global $post;
		if(is_singular('course') || (isset($post) && $post->post_type == 'course')){
			$r['id']=$post->ID;//get_the_ID();
		}else{

			if ( empty( $r['search_terms'] ) ) {
				if ( isset( $_REQUEST['course-filter-box'] ) && !empty( $_REQUEST['course-filter-box'] ) )
					$r['search_terms'] = $_REQUEST['course-filter-box'];
				elseif ( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ))
					$r['search_terms'] = $_REQUEST['s'];
				else
					$r['search_terms'] = '';
			}
			
			if(isset($r['filters'])){
				$filter = $r['filters'];
				switch($filter){
					case 'popular':
						$r['orderby'] = 'meta_value';
						$r['meta_key'] = 'vibe_students';
					break;
					case 'newest':
						$r['orderby'] = 'date';
					break;
					case 'rated':
						$r['orderby'] = 'meta_value';
						$r['meta_key'] = 'average_rating';
					break;
					case 'alphabetical':
						$r['orderby'] = 'title';
						$r['order'] = 'ASC';
					break;
				}
			}
			if(isset($r['scope'])){
				$uid=get_current_user_id();
				if($r['scope'] == 'instructor'){
					$r['instructor'] = $uid;
				}
				if($r['scope'] == 'personal'){
					$r['user'] = $uid;
				}
			}

			if(isset($r['extras'])){
				$extras = json_decode(stripslashes($r['extras']));
				$course_categories=array();
				$course_levels=array();
				$course_location=array();
				$type=array();
				if(is_array($extras)){
					foreach($extras as $extra){
						switch($extra->type){
							case 'course-cat':
								$course_categories[]=$extra->value;
							break;
							case 'free':
								$type=$extra->value;
							break;
							case 'level':
								$course_levels[]=$extra->value;
							break;
							case 'location':
								$course_location[]=$extra->value;
							break;
						}
					}
				}
				$r['tax_query']=array();
				if(count($course_categories)){
					$r['tax_query']['relation'] = 'AND';
					$r['tax_query'][]=array(
										'taxonomy' => 'course-cat',
										'terms'    => $course_categories,
										'field'    => 'slug',
									);
				}
				if($type){
					if(empty($r['meta_query'])){
						$r['meta_query']=array();
					}
					switch($type){
						case 'free':
						$r['meta_query']['relation'] = 'AND';
						$r['meta_query'][]=array(
							'key' => 'vibe_course_free',
							'value' => 'S',
							'compare'=>'='
						);
						break;
						case 'paid':
						$r['meta_query']['relation'] = 'AND';
						$r['meta_query'][]=array(
							'key' => 'vibe_course_free',
							'value' => 'H',
							'compare'=>'='
						);
						break;
					}
				}
				if(count($course_levels)){
					$r['tax_query']['relation'] = 'AND';
					$r['tax_query'][]=array(
											'taxonomy' => 'level',
											'field'    => 'slug',
											'terms'    => $course_levels,
										);
				}
				if(count($course_location)){
					$r['tax_query']['relation'] = 'AND';
					$r['tax_query'][]=array(
											'taxonomy' => 'location',
											'field'    => 'slug',
											'terms'    => $course_location,
										);
				}
			}

			if(isset( $_REQUEST['items_page'] ) && !empty( $_REQUEST['items_page'])){
				$r['paged'] = $_REQUEST['items_page'];
			}

			// User filtering
			if ( bp_displayed_user_id() )
				$user_id = bp_displayed_user_id();
		}
		$items_template = new BP_COURSE();

		$items_template->get( $r );
	}

	return $items_template->have_posts();
}

/***
 *
 */

function bp_course_the_item() {
	global $items_template;
	return $items_template->query->the_post();
}

	function bp_course_item_name() {
		echo bp_course_get_item_name();
	}
	/* Always provide a "get" function for each template tag, that will return, not echo. */
	function bp_course_get_item_name() {
		global $items_template;
		echo apply_filters( 'bp_course_get_item_name', $items_template->item->name ); // course: $items_template->item->name;
	}

	function bp_course_name(){
		echo bp_course_get_name();
	}

	function bp_course_get_name(){
		global $post;
		return $post->post_title;
	}

	function bp_course_get_ID(){
		global $post;
		return $post->ID;
	}

	function bp_course_type(){
		echo bp_course_get_type();
	}

	function bp_course_get_type(){
		global $post;
		$cats=get_the_terms( get_the_ID(), 'course-cat' );

		$cats_string='';
		if(isset($cats) && is_array($cats)){
		$i=0;
			foreach($cats as $cat){
			if($i > 0)
				$cats_string .=', ';
			$cats_string .='<a href="'.get_term_link( $cat->slug, 'course-cat' ).'">'.$cat->name.'</a>';
			$i++;
		}
	}
	return $cats_string;
}

if(!function_exists('bp_course_item_view')){
   function bp_course_item_view(){
   		global $post;
   		$filter = apply_filters('bp_course_single_item_view',0,$post);
   		if($filter){
   			return;
   		}
   		global $post;
   		$course_post_id = $post->ID;
   		$course_author= $post->post_author;
   		$course_classes = apply_filters('bp_course_single_item','course_single_item course_id_'.$post->ID.' course_status_'.$post->post_status.' course_author_'.$post->post_author,get_the_ID());
   		?>	
   		<li class="<?php echo $course_classes; ?>">
   			<div class="row">
   				<div class="col-md-4 col-sm-4">
					<div class="item-avatar" data-id="<?php echo get_the_ID(); ?>">
						<?php bp_course_avatar(); ?>
					</div>
				</div>
				<div class="col-md-8 col-sm-8">
					<div class="item">
						<div class="item-title"><?php bp_course_title(); if(get_post_status() != 'publish'){echo '<i> ( '.get_post_status().' ) </i>';} ?></div>
						<div class="item-meta"><?php bp_course_meta(); ?></div>
						<div class="item-desc"><?php bp_course_desc(); ?></div>
						<div class="item-credits">
							<?php 
								if(bp_is_my_profile()){
									the_course_button($course_post_id);
								}else{
									bp_course_credits(); 	
								}
							?>
						</div>
						<?php
						$enable_instructor = apply_filters('wplms_display_instructor',true,$post->ID);
                    	if($enable_instructor){
						?>
						<div class="item-instructor">
							<?php bp_course_instructor(array('instructor_id'=> $course_author)); ?>
						</div>
						<?php } ?>
						<div class="item-action"><?php bp_course_action() ?></div>
						<?php do_action( 'bp_directory_course_item' ); ?>
					</div>
				</div>
			</div>
		</li>	
   	<?php
   }
}

function bp_course_description(){
	echo bp_course_desc();
}

	function bp_course_get_students_count($course_id = NULL){
		if(empty($course_id))
			$course_id = get_the_ID();
		global $wpdb;
		$count = $wpdb->get_var($wpdb->prepare("SELECT count(user_id) FROM {$wpdb->usermeta} WHERE meta_key  = %s ",'course_status'.$course_id));
		$update_check = vibe_get_option('sync_student_count');
		
		if(!$count)
			$count =0;

		if(isset($update_check) && $update_check)
			update_post_meta($course_id,'vibe_students',$count);

		return $count;
	}
	/**
	 * Echo "Viewing x of y pages"
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 */
	function bp_course_pagination_count() {
		echo bp_course_get_pagination_count();
	}
	/**
	 * Return "Viewing x of y pages"
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 */
	function bp_course_get_pagination_count() {
		global $items_template;

		$pagination_count = sprintf( __( 'Viewing page %1$s of %2$s', 'vibe' ), $items_template->query->query_vars['paged'], $items_template->query->max_num_pages );

		return apply_filters( 'bp_course_get_pagination_count', $pagination_count );
	}

	/**
	 * Echo pagination links
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 */
	function bp_course_item_pagination() {
		echo bp_course_get_item_pagination();
	}
	/**
	 * return pagination links
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 */
	function bp_course_get_item_pagination() {
		global $items_template;
		//print_r($items_template);
		if(isset($_GET['items_page']) && is_numeric($_GET['items_page'])){
			
			//$items_template->paged == $items->max_num_pages;
		}
		
		return apply_filters( 'bp_course_get_item_pagination', $items_template->pag_links );
	}

	/**
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 */
	function bp_course_avatar( $args = array() ) {
		echo bp_course_get_avatar( $args );
	}

	/**
	 *
	 * @package BuddyPress_Course_Component
	 * @since 1.6
	 *
	 * @param mixed $args Accepts WP style arguments - either a string of URL params, or an array
	 * @return str The HTML for a user avatar
	 */
	function bp_course_get_avatar( $args = array() ) {

		$defaults = array(
		'id' => get_the_ID(),
		'size'  => 'full'
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		$thumb = '';
		if(has_post_thumbnail($id)){
			$thumb='<a href="'.get_permalink($id).'" title="'.the_title_attribute('echo=0').'">'.get_the_post_thumbnail($id,$size).'</a>';
		}else{
			$default_course_avatar = vibe_get_option('default_course_avatar');
			if(isset($default_course_avatar) && $default_course_avatar){
				$thumb='<a href="'.get_permalink($id).'" title="'.the_title_attribute('echo=0').'"><img src="'.$default_course_avatar.'" /></a>';
			}
		}
		
		return apply_filters('bp_course_get_avatar',$thumb);
	}

	if(!function_exists('bp_course_is_member')){
		function bp_course_is_member($course_id = null, $user_id = null){

			if(!is_user_logged_in() && empty($user_id))
				return false;

		  	
		  	if(empty($course_id)){
		  		global $post;
		  		if($post->post_type == BP_COURSE_CPT){
		  			$course_id = $post->ID;
		  		}else{
		  			return false;
		  		}
		  	}

		  	if(!isset($course_id) || !$course_id || !is_numeric($course_id))
		    	$course_id = get_the_ID();

		   	if(empty($user_id) && (current_user_can('manage_options') || current_user_can('edit_post',$course_id)))
		   		return true;

		   	if(empty($user_id)){
		   		$user_id = get_current_user_id();
		   	}

		  	$check = get_user_meta($user_id,$course_id,true);
		  	if(isset($check) && $check)
		    	return true;

		  	return false;
		}
	}

function bp_course_instructor_avatar( $args = array() ) {
	//echo bp_course_get_instructor_avatar( $args ); Function not OUTDATED
}

/**
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 *
 * @param mixed $args Accepts WP style arguments - either a string of URL params, or an array
 * @return str The HTML for a user avatar
 */
function bp_course_get_instructor_avatar( $args = array() ) {
	$defaults = array(
		'item_id' => get_the_author_meta( 'ID' ),
		'object'  => 'user'
	);

	$r = wp_parse_args( $args, $defaults );

	return apply_filters('wplms_display_course_instructor_avatar',bp_core_fetch_avatar( $r ),get_the_ID());
}

function bp_course_instructor( $args = array() ) {
	echo bp_course_get_instructor( $args );
}


function bp_course_get_instructor_avatar_url($instructor_id){
	
	if(function_exists('bp_core_fetch_avatar')){
		return bp_core_fetch_avatar(array('item_id' => $instructor_id, 'type' => 'thumb', 'width' => 128, 'height' => 128, 'html'=>false));
	}else{
		if(function_exists('vibe_get_option')){
			$url = vibe_get_option('default_avatar');
		}
		if(empty($url)){$url = VIBE_URL.'/assets/images/avatar.jpg';}
		return $url;
	}
	
}

function bp_course_get_instructor_sub($instructor_id){

	if(bp_is_active('xprofile'))
		$special = bp_get_profile_field_data('field='.$field.'&user_id='.$instructor_id);
}

/**
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 *
 * @param mixed $args Accepts WP style arguments - either a string of URL params, or an array
 * @return str The HTML for a user avatar
 */
function bp_course_get_instructor($args=NULL) {
	$bp_course_template = BP_Course_Template::init();
	return $bp_course_template->get_instructor($args);
}

function bp_course_get_instructor_description($args=NULL) {
	$defaults = array(
		'instructor_id' => get_the_author_meta( 'ID' ),
		'field' => 'About'
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if(function_exists('vibe_get_option'))
		$field = vibe_get_option('instructor_about');

	$desc='';
	if(bp_is_active('xprofile'))
	$desc = bp_get_profile_field_data('field='.$field.'&user_id='.$instructor_id);

	return do_shortcode($desc);
}
/**
 *
 * @package BuddyPress_Course_Component
 * @since 1.6
 */

function bp_course_title($args=NULL) {
	echo bp_course_get_course_title($args);
}
	
/* 
 *
 * We'll assemble the title out of the available information. This way, we can insert
 * fancy stuff link links, and secondary avatars.
 *
 * @package BuddyPress_Course_Component
 * @since 1.6
 */

function bp_course_get_course_title($args) {
	$defaults = array(
	'id' => get_the_ID()
	);
	$args= wp_parse_args( $args, $defaults );

	extract( $args, EXTR_SKIP );

	$title = '<a href="'. get_permalink($id) .'">';
	$title .= get_the_title($id);
	$title .= '</a>';
	return $title;
}

function bp_course_meta() { 
	echo bp_course_get_course_meta();
}
	
	/* 
	 *
	 * We'll assemble the title out of the available information. This way, we can insert
	 * fancy stuff link links, and secondary avatars.
	 *
	 * @package BuddyPress_Skeleton_Component
	 * @since 1.6
	 */

function bp_course_get_course_meta($course_id = NULL) {

	if(empty($course_id)){
		$course_id = get_the_ID();
	}
	$template = BP_Course_Template::init();
	return $template->get_course_meta($course_id);
}

if(!function_exists('bp_course_display_rating')){
	function bp_course_display_rating($reviews){
	  $meta = '<strong class="course-star-rating">
	    <i class="hide">'.$reviews.'</i>';
	    $reviews  = floatval($reviews);
      	if($reviews >= 0){
	        $width = ($reviews/5)*100;
	        $meta .='<small class="bp_blank_stars"><small style="width:'.$width.'%;" class="bp_filled_stars"></small></small>';
      	}
	  $meta .='</strong>';    
	  return apply_filters('bp_course_display_rating',$meta,$reviews);  
	}
}

/*
FORCE RECALCULATE COURSE REVIEW RATING AND COUNT
 */
function bp_course_get_course_reviews($args=NULL){

	$defaults=array(
		'id' =>get_the_ID(),
		);
	$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

	$args = array(
		'status' => 'approve',
		'post_id' => $id
		);
	$comments_query = new WP_Comment_Query;
	$comments = $comments_query->query( $args );

	// Comment Loop
	if ( $comments ) {
		$ratings =0;
		$count=0;
		$rating = array();
		foreach ( $comments as $comment ) {
			$rate = get_comment_meta( $comment->comment_ID, 'review_rating', true );
			if(isset($rate) && $rate !='')
				$rating[] = $rate;
		}

		$count = count($rating);

		if(!$count) $count=1;

		$ratings = round((array_sum($rating)/$count),1);
		
		update_post_meta($id,'average_rating',$ratings);
		update_post_meta($id,'rating_count',$count);

		$reviews = array('rating' => $rating,'count'=>$count);
		return $reviews;
	} else {
		return 0;
	}
}

function bp_course_desc() {
	echo bp_course_get_course_desc();
}
	
/* 
 */

function bp_course_get_course_desc() {
	global $post;
	$limit = apply_filters('excerpt_length',55);
	$desc = wp_trim_words(get_the_excerpt(), $limit);
	
	return apply_filters('the_content',$desc);
}	

function bp_course_action() {
	echo bp_course_get_course_action();
}
	
/* 
 */

function bp_course_get_course_action() {
	do_action('bp_course_get_course_action');
}

function bp_course_credits($args=NULL) {
	echo bp_course_get_course_credits($args);
}
	
/* 
 * Generates Pricing options for course. Used in Featured blocks, Course directory and Single Course page
 * @since 1.0
 */

if(!function_exists('bp_course_get_course_credits')){
	function bp_course_get_course_credits($args=NULL) {
		$template = BP_Course_Template::init();
		return $template->get_course_credits($args);
	}
}

add_filter('wplms_course_credits','wplms_check_private_course');
function wplms_check_private_course($label){ 
	if(strlen($label) < 2){
		return '<strong>'.apply_filters('wplms_private_course_label',__('PRIVATE','vibe')).'</strong>';
	}
	return $label;
}
/**
 * Is this page part of the course component?
 *
 * Having a special function just for this purpose makes our code more readable elsewhere, and also
 * allows us to place filter 'bp_is_course_component' for other components to interact with.
 *
 * @package BuddyPress_Course_Component
 * @since 1.6
 *
 * @uses bp_is_current_component()
 * @uses apply_filters() to allow this value to be filtered
 * @return bool True if it's the course component, false otherwise
 */
function bp_is_course_component() {
	$is_course_component = bp_is_current_component(BP_COURSE_SLUG);
	return apply_filters( 'bp_is_course_component', $is_course_component );
}

function bp_is_single_course(){
	global $bp;
	global $post;
	return is_singular('course');
}
/**
 * Echo the component's slug
 *
 * @package BuddyPress_Course_Component
 * @since 1.6
 */
function bp_course_slug() {
	echo bp_get_course_slug();
}

function bp_get_course_slug(){

	// Avoid PHP warnings, in case the value is not set for some reason
	$course_slug = isset( $bp->course->slug ) ? $bp->course->slug : BP_COURSE_SLUG;
	return apply_filters( 'bp_get_course_slug', $course_slug );
}

/**
 * Echo the component's root slug
 *
 * @package BuddyPress_Course_Component
 * @since 1.6
 */
function bp_course_root_slug() {
	echo bp_get_course_root_slug();
}
/**
 * Return the component's root slug
 *
 * Having a template function for this purpose is not absolutely necessary, but it helps to
 * avoid too-frequent direct calls to the $bp global.
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 *
 * @uses apply_filters() Filter 'bp_get_course_root_slug' to change the output
 * @return str $course_root_slug The slug from $bp->course->root_slug, if it exists
 */
function bp_get_course_root_slug() {
	global $bp;

	// Avoid PHP warnings, in case the value is not set for some reason
	$course_root_slug = isset( $bp->course->root_slug ) ? $bp->course->root_slug : '';
	return apply_filters( 'bp_get_course_root_slug', $course_root_slug );
}

if(!function_exists('bp_course_get_students_undertaking')){
	function bp_course_get_students_undertaking($course_id=NULL, $number=0){ // Modified function, counts total number of students
		global $wpdb,$post;
		if(!isset($course_id))
			$course_id = get_the_ID();
		$template = BP_Course_Template::init();
		return $template->get_students_undertaking($course_id,$number);
	}
}

function bp_course_get_course_students($course_id = null,$page_num = null,$loop_number = null){
	global $wpdb,$post;
		if(!isset($course_id))
			$course_id=get_the_ID();
		$course_members = array();

		if(!isset($loop_number)){
			$loop_number=vibe_get_option('loop_number');
			if(!isset($loop_number)){$loop_number = 5;}
		} 

		$page_num=0;
		$cquery=$wpdb->prepare("SELECT SQL_CALC_FOUND_ROWS DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s LIMIT %d, %d",'course_status'.$course_id,$page_num,$loop_number);
		$course_meta = $wpdb->get_results( $cquery, ARRAY_A);
		$num = $wpdb->get_var('SELECT FOUND_ROWS();');
		foreach($course_meta as $meta){
			if(is_numeric($meta['user_id']))  // META KEY is NUMERIC ONLY FOR USERIDS
				$course_members[] = $meta['user_id'];
		}

		$students = array('students'=>$course_members,'max'=>$num);
		return $students;
}

function bp_course_count_students_pursuing($course_id=NULL){
	if(empty($course_id))
		$course_id = get_the_ID();

	$template = BP_Course_Template::init();
	return $template->count_pursuing_students($course_id);
}

/*
LEGACY FUNCTION : NOT REQUIRED SINCE 2.2
 */
function bp_course_paginate_students_undertaking($course_id=NULL){
	global $wpdb,$post;
	if(!isset($course_id))
		$course_id=get_the_ID();

	$loop_number=vibe_get_option('loop_number');
	if(!isset($loop_number)) $loop_number = 5;

	$extra = '';
	if(isset($_GET['status']) && is_numeric($_GET['status'])){
		$extra = 'AND meta_value = '.$_GET['status'];
	}
	$page_num = 0;
	$query=$wpdb->prepare("SELECT count(user_id) FROM {$wpdb->usermeta} WHERE meta_key = %s ORDER BY meta_value ASC LIMIT %d, %d",'course_status'.$course_id,$page_num,$loop_number);

	if(isset($_GET['status']) && is_numeric($_GET['status'])){
		$query=$wpdb->prepare("SELECT count(user_id) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %d ORDER BY meta_value ASC LIMIT %d, %d",'course_status'.$course_id,$_GET['status'],$page_num,$loop_number);
	}

	$course_number = $wpdb->get_var($query);
	$max_page = ceil($course_number/$loop_number);


	$return  =	'<div class="pagination"><div><div class="pag-count" id="course-member-count">'.sprintf(__('Viewing page %d of %d ','vibe'),((isset($_GET['items_page']) && $_GET['items_page'])?$_GET['items_page']:1 ),$max_page).'</div>
					<div class="pagination-links">';
						$f=$g=1;
						for($i=1;$i<=$max_page;$i++ ){

							if(isset($_GET['items_page']) && is_numeric($_GET['items_page'])){
								if($_GET['items_page'] == $i){
									$return .= '<span class="page-numbers current">'.$i.'</span>';
								}else{
									if($i == 1 || $i == $max_page || ($_GET['items_page'] < 5 && $i < 5) || (($i <= ($_GET['items_page'] + 2)) && ($i >= ($_GET['items_page'] -2))))
									 	$return  .= '<a class="page-numbers" href="?'.(isset($_GET['action'])?'action='.$_GET['action'].'&':'').(isset($_GET['status'])?'status='.$_GET['status'].'&':'').'items_page='.$i.'">'.$i.'</a>';
									 else{
									 	if($f && ($i > ($_GET['items_page'] + 2))){
											$return  .= '<a class="page-numbers">...</a>'; 
											$f=0;
										}
										if($g && ($i <($_GET['items_page'] - 2))){
											$return  .= '<a class="page-numbers">...</a>'; 
											$g=0;
										}
									 }
								}
							}else{
								
								if($i==1)
									$return .= '<span class="page-numbers current">1</span>';
								else{
									if($i < 5 || $i > ($max_page-2))
										$return  .= '<a class="page-numbers" href="?'.(isset($_GET['action'])?'action='.$_GET['action'].'&':'').(isset($_GET['status'])?'status='.$_GET['status'].'&':'').'items_page='.$i.'">'.$i.'</a>';
									else{
										if($f){
											$return  .= '<a class="page-numbers">...</a>'; 
											$f=0;
										}
									}
								}
							}	
						}
						$return  .= '
					</div>
				</div><div>';

	return $return;
}

/**
 * Echo the total of all high-fives given to a particular user
 *
 * @package BuddyPress_Course_Component
 * @since 1.6
 */
function bp_course_total_course_count_for_user( $user_id = false ) {
	echo bp_course_get_total_course_count_for_user( $user_id = false );
}
/**
 * Return the total of all high-fives given to a particular user
 *
 * The most straightforward way to get a post count is to run a WP_Query. In your own plugin
 * you might consider storing data like this with update_option(), incrementing each time
 * a new item is published.
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 *
 * @return int
 */
function bp_course_get_total_course_count_for_user( $user_id = false ) {
	// If no explicit user id is passed, fall back on the loggedin user
	if ( !$user_id ) {
		$user_id = bp_loggedin_user_id();
	}

	if ( !$user_id ) {
		return 0;
	}

	$template = BP_Course_Template::init();
	return $template->get_user_course_count($user_id);
}

/*
*  GET COURSE CURRICULUM`
*/

if(!function_exists('bp_course_get_full_course_curriculum')){
	function bp_course_get_full_course_curriculum($course_id = NULL){
		$curriculum=array();
		global $post;
		if(empty($course_id) && $post->post_type == 'course')
			$course_id = $post->ID;

		if(!isset($course_id) || !is_numeric($course_id))
			return $curriculum;

		$course_items = bp_course_get_curriculum($course_id);
		if(!empty($course_items)){
			foreach($course_items as $key => $item){
				if(is_numeric($item)){
					$type = bp_course_get_post_type($item);
					$labels = $free_access = '';

					if($type == 'unit'){
						$free_access = get_post_meta($item,'vibe_free',true);
						$labels = (vibe_validate($free_access)?'<span class="free">'.__('FREE','vibe').'</span>':'');	
					} 
					
					$duration = get_post_meta($item,'vibe_duration',true);
					if( empty($duration) )
						$duration = 0;
					$duration_parameter = apply_filters("vibe_".$type."_duration_parameter",60,$item);
					$total_duration = $duration*$duration_parameter;
				 	$duration = '<span class="time"><i class="fa fa-clock-o"></i> '.(($duration >9998)?_x('Unlimited','Unlimited unit duration label','vibe'):(($total_duration >= 86400)?tofriendlytime($total_duration):gmdate("H:i:s",$total_duration))).'</span>';
					$curriculum_course_link = apply_filters('wplms_curriculum_course_link',0,$item,$course_id);
					$curriculum[] = array(
						'id'		=>  $item,
						'key'		=>	$key,
						'type'		=>	$type,
						'icon'		=>  (($type == 'unit')?get_post_meta($item,'vibe_type',true):'task'),
						'labels' 	=>  apply_filters('bp_course_curriculum_item_labels',$labels,$item,$type),
						'title'		=>	get_the_title($item),
						'link'		=>	(( vibe_validate($free_access) || ($post->post_author == get_current_user_id()) || current_user_can('manage_options') || $curriculum_course_link)? ( empty($curriculum_course_link)?get_permalink($item).'?id='.$course_id:$curriculum_course_link):''),	
						'duration' 	=>  $duration,
						'extras'	=>  apply_filters('course_curriculum_extras',0,$item)
					);	
				}else{
					$curriculum[] = array(
						'type'	=>	'section',
						'key'	=>	$key,
						'title'	=>	$item
					);	
				}
				
			}
		}

		return apply_filters('bp_course_get_full_course_curriculum',$curriculum,$course_id);
	}
}


/*
* QUIZ FUNCTIONS
*/
function bp_course_get_quiz_questions($quiz_id,$user_id){
	//Correcting an old mistake
	$template = BP_Course_Template::init();
	if(isset($template->quiz_questions)){
		if(isset($template->quiz_questions[$quiz_id][$user_id]) && !empty($template->quiz_questions[$quiz_id][$user_id]['ques'])){
			return $template->quiz_questions[$quiz_id][$user_id];
		}
	}
	$questions = get_user_meta($user_id,'quiz_questions'.$quiz_id,true);
	
	if(empty($questions) || (isset($questions['ques']) && empty($questions['ques'])) ){
		
		$questions = get_post_meta($quiz_id,'quiz_questions'.$user_id,true);

	    //if(empty($questions) || !is_array($questions)){ // Fallback for Older versions
	      //	$questions = get_post_meta($quiz_id,'vibe_quiz_questions',true);
	    //}
	    //ARCHITECHTURE UPDATE 2.3
	    if(!empty($questions)){ 
	    	update_user_meta($user_id,'quiz_questions'.$quiz_id,$questions);
	    }	    
    }
    $template->quiz_questions[$quiz_id][$user_id] = $questions;
  	return $questions;
}


/**
* GET QUIZ QUESTIONS
*
* @since 3.0.0
*/
function bp_course_update_quiz_questions($quiz_id,$user_id,$questions){
	update_user_meta($user_id,'quiz_questions'.$quiz_id,$questions);
	$template = BP_Course_Template::init();
	$template->quiz_questions[$quiz_id][$user_id] = $questions;
}

/**
* GET QUESTION
*
* @since 3.0.0
*/
//Remove cached on save/update/publish
add_action( 'save_post', function($post_id){
	$type = get_post_type($post_id);
	if($type == 'question'){
		delete_post_meta($post_id,'vibe_question_json');
	}
});



function bp_course_get_question_details($question_id,$force = null){

	$question = get_post_meta($question_id,'vibe_question_json',true);
	if(!empty($question) && empty($force)){
		return $question;
	}
	$question = array();
	$question['type'] = get_post_meta($question_id,'vibe_question_type',true);
	$question['hint'] = do_shortcode(get_post_meta($question_id,'vibe_question_hint',true));
	$question['explanation'] = do_shortcode(get_post_meta($question_id,'vibe_question_explanation',true));

	$content = get_post_field('post_content',$question_id);


	if( has_shortcode( $content, 'match' ) ) {
		preg_match_all("/(.*)\[match\](.*)\[\/match\](.*)/", $content, $content_array);
		if(!empty($content_array) && isset($content_array[2])){
			
			preg_match_all("/<li>(.*?)<\/li>/", $content_array[2][0], $s_items);
			if(!empty($s_items)){
				foreach($s_items[1] as $i=>$item){
					$shortcode_items[$i] = do_shortcode($item);
				}
				$question['content'] = array(
					'statement' => $content_array[1][0],
					'match' => $shortcode_items,
					'end' => $content_array[3][0],
				);	
			}
			
		}else{
			$question['content'] = $content;
		}
		
	} else if( has_shortcode( $content, 'fillblank' ) ) {
		
		$content_array = explode('[fillblank]',$content);
		if(!empty($content_array) ){
			foreach($content_array as $k=>$c){
				$content_array[$k] = apply_filters('the_content',$c);
			}
			$question['content'] = $content_array;
		}else{
			$question['content'] = $content;
		}

	} else if( has_shortcode( $content, 'select' ) ) {
		$content_array = preg_split("/\[.*?\]/", $content);
		if(!empty($content_array) ){
			foreach($content_array as $k=>$c){
				$content_array[$k] = apply_filters('the_content',$c);
			}
			$question['content'] = $content_array;
		}else{
			$question['content'] = $content;
		}
	}else{
		$question['content'] = apply_filters('the_content',$content);	
	}
	
	$question['options'] = get_post_meta($question_id,'vibe_question_options',true);
	$question['correct'] = get_post_meta($question_id,'vibe_question_answer',true);
	
	update_post_meta($question_id,'vibe_question_json',$question);
	return $question;
}


//Used in Quiz/Course retakes
function bp_course_remove_quiz_questions($quiz_id,$user_id){

	$questions = bp_course_get_quiz_questions($quiz_id,$user_id);
	
	if(!isset($questions) || empty($questions['ques']))
		return;

	$qs = implode(',',$questions['ques']);
	global $wpdb;


	$wpdb->query($wpdb->prepare("
		UPDATE {$wpdb->comments} as c 
		LEFT JOIN {$wpdb->commentmeta} as m
		ON c.comment_ID = m.comment_id
		SET c.comment_approved='trash' 
		WHERE c.user_id=%d
		AND m.meta_key = 'quiz_id'
		AND m.meta_value = %d
		AND c.comment_post_ID IN ($qs)",$user_id,$quiz_id));

	delete_user_meta($user_id,$quiz_id);
	delete_post_meta($quiz_id,'quiz_questions'.$user_id); 
	delete_user_meta($user_id,'quiz_questions'.$quiz_id,$questions);
	delete_post_meta($quiz_id,$user_id); // Optional validates that user can retake the quiz

	//clear cached object
	$template = BP_Course_Template::init();
	if(isset($template->quiz_questions) && isset($template->quiz_questions[$quiz_id]) && isset($template->quiz_questions[$quiz_id][$user_id])){
		unset($template->quiz_questions[$quiz_id][$user_id]);
	}
}

function bp_course_get_user_quiz_status($user_id,$quiz_id){
	$status = get_user_meta($user_id,'quiz_status'.$quiz_id,true);
	// empty : No QUIZ
	// 1 : Quiz Started
	// 2 : Continue Quiz
	// 3 : Submit quiz
	// 4 : Quiz evaluated
	if(!isset($status) || $status == ''){
		$value = get_post_meta($quiz_id,$user_id,true);
		if(!empty($value)){
			$value = 1;
			update_user_meta($user_id,'quiz_status'.$quiz_id,$value);
		}
		return $value;
	}
	return $status;
}

function bp_course_update_user_quiz_status($user_id,$quiz_id,$status){
	update_user_meta($user_id,'quiz_status'.$quiz_id,$status);
}

function bp_course_remove_user_quiz_status($user_id,$quiz_id){
	delete_user_meta($user_id,$quiz_id);
  	delete_post_meta($quiz_id,$user_id); // Optional validates that user can retake the quiz
	delete_user_meta($user_id,'quiz_status'.$quiz_id);
	delete_user_meta($user_id,'quiz_lock_'.$quiz_id);
	if(function_exists('bp_is_active') && bp_is_active('activity')){
		global $wpdb,$bp;
		$activity_id = $wpdb->get_var($wpdb->prepare( "
				SELECT m.activity_id
				FROM {$bp->activity->table_name} as a 
				LEFT JOIN {$bp->activity->table_name_meta} as m ON a.id = m.activity_id 
				WHERE m.meta_key = 'quiz_results' 
				AND a.secondary_item_id = %d 
				AND a.user_id = %d
				ORDER BY m.activity_id DESC LIMIT 0,1",$quiz_id,$user_id));
		if(is_numeric($activity_id)){
			//Remove
			bp_activity_delete_meta( $activity_id ); 
		}
	}
}

function bp_course_get_user_question_marks($quiz_id,$question_id,$user_id){
	$template = BP_Course_Template::init();
	return $template->get_answer_marks($quiz_id,$question_id,$user_id);
}

function bp_course_save_user_answer_marks($quiz_id,$question_id,$user_id,$marks){
	$template = BP_Course_Template::init();
	return $template->update_answer_marks($quiz_id,$question_id,$user_id,$marks);
}

function bp_course_get_question_marked_answer($quiz_id,$question_id,$user_id){
	$template = BP_Course_Template::init();
	return $template->get_marked_answer($quiz_id,$question_id,$user_id);
}

function bp_course_get_question_marked_answer_id($quiz_id,$question_id,$user_id){
	$template = BP_Course_Template::init();
	return $template->marked_answer_id($quiz_id,$question_id,$user_id);
}

function bp_course_reset_question_marked_answer($quiz_id,$question_id,$user_id){

	global $wpdb;
	$comment_id = $wpdb->get_var($wpdb->prepare("SELECT m.comment_id as comment_id FROM {$wpdb->commentmeta} as m LEFT JOIN {$wpdb->comments} as c ON c.comment_ID = m.comment_id WHERE c.comment_post_ID = %d AND c.user_id = %d AND c.comment_approved = 1 AND m.meta_key = %s AND m.meta_value = %d LIMIT 0,1",$question_id,$user_id,'quiz_id',$quiz_id));

	if(!empty($comment_id)){
		$wpdb->query("UPDATE {$wpdb->comments} SET comment_approved='0' WHERE comment_ID = $comment_id");
	}else{
		$flag = apply_filters('wplms_disable_question_reusability_fallback',0);
		if(empty($flag))
			$wpdb->query($wpdb->prepare("UPDATE {$wpdb->comments} SET comment_approved='0' WHERE comment_post_ID=%d AND user_id=%d",$question_id,$user_id));	
	}
}

function bp_course_save_question_quiz_answer($quiz_id,$question_id,$user_id,$answer){

	$question_answer_args = apply_filters('bp_course_save_question_quiz_answer',array(
        'comment_post_ID'=>$question_id,
        'user_id'=>$user_id,
        'comment_content'=>addslashes($_POST['answer']),
        'comment_date' => current_time('mysql'),
        'comment_approved' => 1,
    ));
    global $wpdb;
    $answer_id = $wpdb->get_var("SELECT m.comment_id FROM {$wpdb->comments} as c LEFT JOIN {$wpdb->commentmeta} as m ON c.comment_ID = m.comment_id WHERE c.user_id = $user_id AND c.comment_post_ID = $question_id AND m.meta_key = 'quiz_id' AND m.meta_value = $quiz_id");
    if(!empty($answer_id) && is_numeric($answer_id)){
         $question_answer_args['comment_ID'] = $answer_id;
         wp_update_comment($question_answer_args);
    }else{
        $comment_id = wp_insert_comment($question_answer_args);
        if(!is_wp_error($comment_id)){
            update_comment_meta($comment_id,'quiz_id',$quiz_id);
        }
    }
}

function bp_course_generate_user_result($quiz_id,$user_id,$user_result,$activity_id){
	global $wpdb,$bp;
	if(Empty($activity_id))
		return;
	bp_course_record_activity_meta(array('id'=>$activity_id,'meta_key'=>'quiz_results','meta_value'=>$user_result));
}
/*
* CURRICULUM FUNCTIONS
*/
if(!function_exists('bp_course_get_curriculum')){
	function bp_course_get_curriculum($course_id = NULL){
	
		$course_curriculum=array();
		if(!isset($course_id) || !is_numeric($course_id)){
			global $post;
			if($post->post_type == 'course'){
				$course_id = $post->ID;
			}else{
				return $course_curriculum;
			}
		}

		$course_curriculum = apply_filters('bp_course_get_course_curriculum',$course_curriculum,$course_id);
		if(empty($course_curriculum)){
			$bp_course_template = BP_Course_Template::init();
			$course_curriculum= $bp_course_template->get_curriculum($course_id);
		}

		return $course_curriculum;
	}	
}

function bp_course_get_curriculum_units($course_id=NULL){
	$units=array();
	if(!isset($course_id) || !is_numeric($course_id))
		return $units;

	$course_curriculum=bp_course_get_curriculum($course_id);
        
        if(isset($course_curriculum) && is_array($course_curriculum)){
        	foreach($course_curriculum as $key=>$curriculum){
            if(is_numeric($curriculum)){
                $units[]=$curriculum;
            }
          }
        }
    return $units;    
}


function bp_course_get_user_expiry_time($user_id,$course_id){

    $remaining_time = apply_filters('bp_course_get_user_expiry_time',0,$user_id,$course_id);
    if(empty($remaining_time)){
      $remaining_time = get_user_meta($user_id,$course_id,true);  
    }
    return $remaining_time;
}
   
function bp_course_update_user_expiry_time($user_id,$course_id,$time){

    $remaining_time = apply_filters('bp_course_update_user_expiry_time',$time,$user_id,$course_id);
    if(empty($remaining_time)){
      $remaining_time = update_user_meta($user_id,$course_id,$remaining_time);  
    }
    return $remaining_time;
}

function bp_course_reset_unit($user_id,$unit_id,$course_id){
	// Correct for OLDER VERSIONS as well
	delete_user_meta($user_id,$unit_id);
	delete_post_meta($unit_id,$user_id);
    delete_user_meta($user_id,'complete_unit_'.$unit_id.'_'.$course_id);
    delete_user_meta($user_id,'start_unit_'.$unit_id.'_'.$course_id);
}

function bp_course_get_post_type($id){
	$template = BP_Course_Template::init();
	return $template->get_post_type($id);
}

function bp_course_get_unit_duration($id){
	$unit_duration = (int)get_post_meta($id,'vibe_duration',true);
	$unit_duration_parameter = apply_filters('vibe_unit_duration_parameter',60,$id);
	return (int)$unit_duration*$unit_duration_parameter;
}

function bp_course_get_quiz_duration($id){
	$unit_duration = (int)get_post_meta($id,'vibe_duration',true);
	$unit_duration_parameter = (int)apply_filters('vibe_quiz_duration_parameter',60,$id);
	return $unit_duration*$unit_duration_parameter;
}


function bp_course_get_user_unit_completion_time($user_id,$unit_id,$course_id=null){

  if(!empty($course_id)){
  	if(bp_course_get_post_type($unit_id) == 'unit'){
    	$time = get_user_meta($user_id,'complete_unit_'.$unit_id.'_'.$course_id,true);
  	}else if(bp_course_get_post_type($unit_id) == 'quiz'){
  			$status = bp_course_get_user_quiz_status($user_id,$unit_id);
  			if(class_exists('WPLMS_tips') && !empty($status)){ //PASSING SCORE CHECK
  				$tips = WPLMS_tips::init();
  				if(isset($tips) && isset($tips->settings) && isset($tips->settings['quiz_passing_score'])){
  					$passing_score = get_post_meta($unit_id,'vibe_quiz_passing_score',true);
  					$score = get_post_meta($unit_id,$user_id,true);
  					if($score <= $passing_score)
  						return;
  				}
  			}
  			if(empty($status)){
  				return;
  			}else{
  				$time = get_user_meta($user_id,$unit_id,true);
  				return $time;
  			}
  		}
  	}
  	if(empty($time)){ // FALLBACK
    	$time = get_user_meta($user_id,$unit_id,true);  // Version 2.3 or less
    	if(!empty($time)){
    		bp_course_update_user_unit_completion_time($user_id,$unit_id,$course_id,$time);
    	}
  	}
  	return $time;
}


function bp_course_update_user_unit_completion_time($user_id,$unit_id,$course_id,$time){
  update_user_meta($user_id,'complete_unit_'.$unit_id.'_'.$course_id,$time);
  //update_user_meta($user_id,$unit_id,$time);
}

function bp_course_update_unit_user_access_time($unit_id,$user_id,$time,$course_id=null){
	if(empty($course_id)){
		update_post_meta($unit_id,$user_id,$time);
	}else{
		update_user_meta($user_id,'start_unit_'.$unit_id.'_'.$course_id,$time);		
	}
}

function bp_course_get_curriculum_quizes($course_id = NULL){
	$quizes=array();
	if(!isset($course_id) || !is_numeric($course_id))
		return $quizes;

	$course_curriculum=bp_course_get_curriculum($course_id);
        
    if(isset($course_curriculum) && is_array($course_curriculum)){
    	foreach($course_curriculum as $key=>$curriculum){
	        if(is_numeric($curriculum) && bp_course_get_post_type($curriculum) == 'quiz'){
	            $quizes[]=$curriculum;
	        }
      	}
    }
    return $quizes;  
}

function bp_course_check_unit_complete($unit_id=NULL,$user_id=NULL,$course_id=NULL){

	if(empty($unit_id) || empty($course_id))
		return false;
	if(!isset($user_id) || !$user_id)
		$user_id = get_current_user_id();

	$unit_check=0;
	$template = BP_Course_Template::init();
	$post_type = $template->get_post_type($unit_id);
	if($post_type == 'unit'){
		$unit_check=bp_course_get_user_unit_completion_time($user_id,$unit_id,$course_id);
		if(isset($unit_check) && $unit_check)
			return true;
		else
			return false;
	}else if($post_type == 'quiz'){
		$check = bp_course_check_quiz_complete($unit_id,$user_id,$course_id);
		return $check;
	}
}

function bp_course_check_quiz_complete($quiz_id=NULL,$user_id=NULL,$course_id=NULL){

	if(!isset($quiz_id) || !$quiz_id)
		return false;
	if(!isset($user_id) || !$user_id)
		$user_id = get_current_user_id();

	$quiz_check=get_post_meta($quiz_id,$user_id,true);
	if(isset($quiz_check) && $quiz_check !='')
		return true;
	else
		return false;
}

function bp_course_get_user_progress($user_id,$course_id){

	$progress = apply_filters('bp_course_get_user_progress',0,$course_id,$user_id);
	if(empty($progress))
		$progress = get_user_meta($user_id,'progress'.$course_id,true);
	return $progress;
}
/* == Update user progress function == */

function bp_course_update_user_progress($user_id,$course_id,$progress){
	
	$progress = apply_filters('bp_course_update_user_progress',$progress,$course_id,$user_id);
	if(isset($progress))
		update_user_meta($user_id,'progress'.$course_id,$progress);
}

/* ==== Course count function === */

function bp_course_total_course_count() {
	echo bp_course_get_total_course_count();
}



function bp_course_unit_quiz_complete_check($unit_id = NULL,$user_id){
	if(empty($unit_id))
		return false;
	
	if(empty($user_id))
		$user_id = get_current_user_id();

	if(empty($check)){
		$check = get_user_meta($user_id,$unit_id,true);
	}

	return (empty($check)?false:true);
}
/**
 * Return the total of all high-fives given to a particular user
 *
 * The most straightforward way to get a post count is to run a WP_Query. In your own plugin
 * you might consider storing data like this with update_option(), incrementing each time
 * a new item is published.
 *
 * @package BuddyPress_Skeleton_Component
 * @since 1.6
 *
 * @return int
 */
if(!function_exists('bp_course_get_total_course_count')){
function bp_course_get_total_course_count( ) {
	// If no explicit user id is passed, fall back on the loggedin user
	$c = wp_count_posts('course');
	$count_course = $c->publish;
	if(!isset($count_course)) $count_course =0;
	return apply_filters('bp_course_total_count',$count_course);
	}
}

function bp_is_course() {
	global $bp;
	if ( bp_is_course_component())
		return true;

	return false;
}

/**
 * Is the current page a single course's home page?
 *
 * URL will vary depending on which course tab is set to be the "home". By
 * default, it's the course's recent activity.
 *
 * @return bool True if the current page is a single course's home page.
 */
function bp_is_course_home() {

	if ( bp_is_course_single_item() && bp_is_course_component() && ( !bp_current_action() || bp_is_current_action( 'home' ) ) )
		return true;

	global $post;
	if(is_singular('course'))
		return true;

	return false;
}

function bp_is_course_single_item(){ // Global BP Fails Here **** ComeBack when BuddyPress Fixes This
	global $post,$bp;
	if(is_singular('course')){
		return true;
		$bp->is_single_item=true;
	}
	else
		return false;
}

/**
 * RETURN USER COURSE LIST
 *
 * @return COURSE ARRAY FPR USER
 */
function bp_course_get_user_courses($user_id,$status = NULL){
  	
  	if(!is_numeric($user_id))
    	return;

  	global $wpdb,$bp;

  	if(function_exists('bp_is_active') && bp_is_active('activity')){
  		if(!empty($status) && in_array($status,array('active','expired'))){
			$query = $wpdb->get_results($wpdb->prepare("
	  		SELECT posts.ID as id, IF(meta.meta_value > %d,'active','expired') as status
	      	FROM {$wpdb->posts} AS posts
	      	LEFT JOIN {$wpdb->usermeta} AS meta ON posts.ID = meta.meta_key
	      	WHERE   posts.post_type   = %s
	      	AND   posts.post_status   = %s
	      	AND   meta.user_id   = %d
	      	",time(),'course','publish',$user_id));
	      	
  		}else{
	  		$query = $wpdb->get_results($wpdb->prepare("
	  		SELECT item_id as id,type as status
	  		FROM {$bp->activity->table_name}
	  		WHERE user_id = %d
	  		AND type IN ('subscribe_course','start_course','submit_course','course_evaluated')
	  		ORDER BY date_recorded DESC
	  		",$user_id));
	  	}
	}else{
		// If any Third party plugin author is integrating !!
		$query = $wpdb->get_results($wpdb->prepare("
	    	SELECT posts.ID as id
	      FROM {$wpdb->posts} AS posts
	      LEFT JOIN {$wpdb->usermeta} AS meta ON posts.ID = meta.meta_key
	      WHERE   posts.post_type   = %s
	      AND   posts.post_status   = %s
	      AND   meta.user_id   = %d
	      ",'course','publish',$user_id));
	}

  	$courses =array();
  	if(isset($query) && is_array($query)){
    	foreach($query as $q){
    		if(!empty($status)){
    			if(isset($q->status) && $q->status == $status){
    				$courses[]=$q->id;			
    			}
    		}else{
    			$courses[]=$q->id;
    		}
    	}  
  	}
  	return $courses;
}

/**
 * Is the current page part of the course creation process?
 *
 * @return bool True if the current page is part of the course creation process.
 */
function bp_is_course_create() {
	if ( bp_is_course_component() && bp_is_current_action( 'create' ) )
		return true;

	return false;
}

/**
 * Is the current page part of a single course's admin screens?
 *
 * Eg http://example.com/courses/mycourse/admin/settings/.
 *
 * @return bool True if the current page is part of a single course's admin.
 */
function bp_is_course_admin_page() {
	if ( bp_is_course_single_item() && bp_is_course_component() && bp_is_current_action( 'admin' ) )
		return true;

	return false;
}

/**
 * Is the current page a course's activity page?
 *
 * @return True if the current page is a course's activity page.
 */
function bp_is_course_activity() {
	if ( bp_is_course_single_item() && bp_is_course_component() && bp_is_current_action( 'activity' ) )
		return true;

	return false;
}


function get_current_course_slug(){
	global $post;
	return $post->post_name;
}

/**
 * Is the current page a course's Members page?
 *
 * Eg http://example.com/courses/mycourse/members/.
 *
 * @return bool True if the current page is part of a course's Members page.
 */
function bp_is_course_members() {
	if ( bp_is_course_single_item() && bp_is_course_component() && bp_is_current_action( 'members' ) )
		return true;

	return false;
}

function bp_is_user_course() {
	if ( bp_is_user() && bp_is_course_component() )
		return true;

	return false;
}


function bp_course_creation_form_action() {
	echo bp_get_course_creation_form_action();
}

function bp_get_course_creation_form_action(){
	global $bp;

	if ( !bp_action_variable( 1 ) ) {
			$keys = array_keys( $bp->courses->course_creation_steps );
		if ( !$user_id ) {
			$bp->action_variables[1] = array_shift( $keys );
		}
	}

	return apply_filters( 'bp_get_course_creation_form_action', trailingslashit( bp_get_root_domain() . '/' . bp_get_courses_root_slug() . '/create/step/' . bp_action_variable( 1 ) ) );
}

/**
 *
 * @package WPLMS Course Status Functions
 * @since 1.8.4
 */
if(!function_exists('bp_course_get_user_course_status')){
	function bp_course_get_user_course_status($user_id,$course_id){
		// NEW COURSE STATUSES
		// 1 : START COURSE
		// 2 : CONTINUE COURSE
		// 3 : FINISH COURSE : COURSE UNDER EVALUATION
		// 4 : COURSE EVALUATED
		$course_status = get_user_meta($user_id,'course_status'.$course_id,true);
		if(empty($course_status) || !is_numeric($course_status)){
			$course_status=get_post_meta($course_id,$user_id,true); 
			if(is_numeric($course_status)){
				$course_status++;
				if($course_status > 3)
					$course_status = 4;
				$course_status = apply_filters('wplms_course_status',$course_status);
				update_user_meta($user_id,'course_status'.$course_id,$course_status);
			}
		}
		return $course_status;
	}
	function bp_course_update_user_course_status($user_id,$course_id,$status){
			// NEW COURSE STATUSES
			// 1 : START COURSE
			// 2 : CONTINUE COURSE
			// 3 : FINISH COURSE : COURSE UNDER EVALUATION
			// 4 : COURSE EVALUATED
			//update_post_meta($course_id,$user_id,$status); // Maintaining OLD COURSE STATUSES
			$status++;
			$status = apply_filters('wplms_course_status',$status);
			update_user_meta($user_id,'course_status'.$course_id,$status);
	}
}

/*========*/
function is_user_instructor(){
	
	if(!is_user_logged_in())
		return false;

		
	if(current_user_can('edit_posts'))
		return true;
	else
		return false;
}

function bp_course_get_instructor_course_count_for_user($id=NULL){
	if(!isset($id)){
		$id=bp_loggedin_user_id();
	}
		

	$count = '';
	$count = apply_filters('wplms_get_instructor_course_count',$count,$id);

	if(empty($count)){
		if(function_exists('count_user_posts_by_type')){
			return count_user_posts_by_type($id,BP_COURSE_SLUG);
		}else{
			return 0;
		}
	}else{
		return $count;
	}
}

function bp_is_my_profile_intructor(){
	
	if(current_user_can('edit_posts') && bp_is_my_profile() || (current_user_can('manage_options') && bp_is_user()))
		return true;
	else
		return false;
}

function is_instructor($id=NULL){
	
	if(!is_user_logged_in())
		return false;

	global $post;
	if(!isset($id)){
		$id= $post->ID;
	}
	$uid = bp_loggedin_user_id();
	$authors=array($post->post_author);
	$authors = apply_filters('wplms_course_instructors',$authors,$post->ID);
	if(in_array($uid,$authors) )
		return true;

	return false;
}

function bp_course_permalink( $course = false ) {
	echo bp_get_course_permalink( $course );
}

function bp_get_course_permalink( $course = false ) {
	global $post;

	$id = 0;
	if(isset($course) && $course){
		$id = $course;
	}else{
		if($post)
			$id = $post->ID;
	}
	
	if($id)
		return apply_filters( 'bp_get_course_permalink', get_permalink($id));
	return '';
}

function bp_course_admin_permalink( $course = false ) {
	echo bp_get_course_admin_permalink( $course );
}

function bp_get_course_admin_permalink( $course = false ) {
	global $post;

	if(isset($course) && $course){
	$id = $course;
	}else{
		$id=$post->ID;
	}

	return apply_filters( 'bp_get_course_admin_permalink', ( get_the_permalink($id). 'admin' ) );
}

function bp_course_check_course_complete($args=NULL){
	echo bp_get_course_check_course_complete($args);
}


function bp_get_course_check_course_complete($args=NULL){ // AUTO EVALUATION FOR COURSE
	global $post;
	$defaults = array(
		'id'=>$post->ID,
		'user_id'=>get_current_user_id()
		);

	$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

	$return ='<div class="course_finish">';

	do_action('bp_get_course_check_course_complete',$id,$user_id);
	$stop_finish_course_check = apply_filters('bp_get_course_check_course_complete_stop',0,$id,$user_id);

	if($stop_finish_course_check)
		return;

	$course_curriculum=bp_course_get_curriculum_units($id);
	if(isset($course_curriculum) && count($course_curriculum)){
		$flag =0;
		foreach($course_curriculum as $unit_id){
			//if(is_numeric($unit_id)){
				$unittaken = bp_course_check_unit_complete($unit_id,$user_id,$id);
				if(empty($unittaken) && bp_course_get_post_type($unit_id) == 'quiz'){
					$unittaken=get_user_meta($user_id,$unit_id,true);
				}
				if(!isset($unittaken) || !$unittaken){
					$flag=$unit_id;
					break;
				}
			//}
		}
		$flag = apply_filters('wplms_finish_course_check',$flag,$course_curriculum);
		if(!$flag){

			$course_id = $id;
			$auto_eval = get_post_meta($id,'vibe_course_auto_eval',true);
			

			if(vibe_validate($auto_eval)){

				// AUTO EVALUATION
				$curriculum=bp_course_get_curriculum_units($id);
				$total_marks=$student_marks=0;

				foreach($curriculum as $c){
					if(bp_course_get_post_type($c) == 'quiz'){
	          			$k=get_post_meta($c,$user_id,true);
						$student_marks += apply_filters('wplms_course_quiz_weightage',$k,$c,$course_id);
						$questions = bp_course_get_quiz_questions($c,$user_id);  
						$quiz_total_marks = array_sum($questions['marks']);
			      		$total_marks += apply_filters('wplms_course_quiz_weightage',$quiz_total_marks,$c,$course_id);
					}
				}
				

				// Apply Filters on Auto Evaluation
				$student_marks=apply_filters('wplms_course_student_marks',$student_marks,$id,$user_id);
				$total_marks=apply_filters('wplms_course_maximum_marks',$total_marks,$id,$user_id);

				if(!$total_marks){$total_marks=$student_marks=1; }// Avoid the Division by Zero Error

				$marks = round(($student_marks*100)/$total_marks);

				$return .='<div class="message updated"><p>'.__('COURSE EVALUATED ','vibe').'</p></div>';

				$badge_per = get_post_meta($id,'vibe_course_badge_percentage',true);

				$passing_cert = get_post_meta($id,'vibe_course_certificate',true); // Certificate Enable
				$passing_per = get_post_meta($id,'vibe_course_passing_percentage',true); // Certificate Passing Percentage

				//finish bit for student 1.8.4
				update_user_meta($user_id,'course_status'.$id,3);
				//end finish bit
				
    			
    			
    			$badge_filter = 0;
				if(isset($badge_per) && $badge_per && $marks >= $badge_per)
  					$badge_filter = 1;
  
  				$badge_filter = apply_filters('wplms_course_student_badge_check',$badge_filter,$course_id,$user_id,$marks,$badge_per);
			    if($badge_filter){  
			        $badges = array();
			        $badges= vibe_sanitize(get_user_meta($user_id,'badges',false));

			        if(isset($badges) && is_array($badges)){
			        	if(!in_array($id,$badges)){
			        		$badges[]=$id;
			        	}
			        }else{
			        	$badges=array($id);
			        }

			        update_user_meta($user_id,'badges',$badges);

			        $b=bp_get_course_badge($id);
            		$badge=wp_get_attachment_info($b); 
            		$size = apply_filters('bp_course_badge_thumbnail_size','thumbnail');
            		$badge_url=wp_get_attachment_image_src($b,$size);
            		if(isset($badge) && is_numeric($b))
			        	$return .='<div class="congrats_badge">'.__('Congratulations ! You\'ve earned the ','vibe').' <strong>'.get_post_meta($id,'vibe_course_badge_title',true).'</strong> '.__('Badge','vibe').'<a class="tip ajax-badge" data-course="'.get_the_title($id).'" title="'.get_post_meta($id,'vibe_course_badge_title',true).'"><img src="'.$badge_url[0].'" title="'.$badge['title'].'"/></a></div>';
			        

			        do_action('wplms_badge_earned',$id,$badges,$user_id,$badge_filter);
			    }
			    $passing_filter =0;
			    if(vibe_validate($passing_cert) && isset($passing_per) && $passing_per && $marks >= $passing_per)
			    	$passing_filter = 1;

			    $passing_filter = apply_filters('wplms_course_student_certificate_check',$passing_filter,$course_id,$user_id,$marks,$passing_per);
			    
			    if($passing_filter){
			        $pass = array();
			        $pass=vibe_sanitize(get_user_meta($user_id,'certificates',false));
			        
			        if(isset($pass) && is_array($pass)){
			        	if(!in_array($id,$pass)){
			        		$pass[]=$id;
			        	}
			        }else{
			        	$pass=array($id);
			        }

			        update_user_meta($user_id,'certificates',$pass);
			        $return .='<div class="congrats_certificate">'.__('Congratulations ! You\'ve successfully passed the course and earned the Course Completion Certificate !','vibe').'<a href="'.bp_get_course_certificate(array('user_id'=>$user_id,'course_id'=>$id)).'" class="ajax-certificate right '.apply_filters('bp_course_certificate_class','',array('course_id'=>$id,'user_id'=>$user_id)).'" data-user="'.$user_id.'" data-course="'.$id.'"><span>'.__('View Certificate','vibe').'</span></a></div>';
			        do_action('wplms_certificate_earned',$id,$pass,$user_id,$passing_filter);
			    }

			    update_post_meta( $id,$user_id,$marks);

			    $course_end_status = apply_filters('wplms_course_status',4);  
				update_user_meta( $user_id,'course_status'.$id,$course_end_status);//EXCEPTION	

			    $message = sprintf(__('You\'ve obtained %s in course %s ','vibe'),apply_filters('wplms_course_marks',$marks.'/100',$course_id),' <a href="'.get_permalink($id).'">'.get_the_title($id).'</a>'); 
			    $return .='<div class="congrats_message">'.$message.'</div>';
			    do_action('wplms_evaluate_course',$id,$marks,$user_id,1);
			    
			    do_action('wplms_submit_course',$post->ID,$user_id);

			}else{
				$return .='<div class="message" class="updated"><p>'.__('COURSE SUBMITTED FOR EVALUATION','vibe').'</p></div>';
				bp_course_update_user_course_status($user_id,$id,2);// 2 determines Course is Complete
				do_action('wplms_submit_course',$post->ID,$user_id);
			}
			
			// Show the Generic Course Submission
			$content=get_post_meta($id,'vibe_course_message',true);
			$return .=apply_filters('the_content',$content);
			$return = apply_filters('wplms_course_finished',$return);
		}else{
			$type=bp_course_get_post_type($flag);
			switch($type){
				case 'unit':
				$type= __('UNIT','vibe');
				break;
				case 'assignment':
				$type= __('ASSIGNMENT','vibe');
				break;
				case 'quiz':
				$type= __('QUIZ','vibe');
				break;
			}//Default for other customized options
			$message = __('PLEASE COMPLETE THE ','vibe').$type.' : '.get_the_title($flag);
			$return .='<div class="message"><p>'.apply_filters('wplms_unfinished_unit_quiz_message',$message,$flag).'</p></div>';
		}
	}else{
		$retun .=__('COURSE CURRICULUM NOT SET','vibe');
	}	
	$return .='</div>';
	return $return;
}

function bp_course_get_unit_course_id($unit_id){
	global $wpdb;
	$course_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key= 'vibe_course_curriculum' AND meta_value LIKE %s LIMIT 1;", "%{$unit_id}%" ) );
	if(empty($course_id))
		return false;

	return $course_id;
}

/* ======== My Instructor Function ========= */

function bp_course_is_my_instructor($instructor_id ,$user_id = NULL){

	if(empty($instructor_id) && !is_user_logged_in())
		return false;

	if(!function_exists('get_coauthors')){
		global $wpdb;
		$count = $wpdb->get_var($wpdb->prepare("
		    SELECT count(posts.ID) as count
		    FROM {$wpdb->posts} AS posts
		    LEFT JOIN {$wpdb->usermeta} AS meta ON posts.ID = meta.meta_key
		    WHERE   posts.post_type   = %s
		    AND   posts.post_status   = %s
		    AND posts.post_author = %d
		    AND   meta.user_id   = %d
		    ",'course','publish',$instructor_id,$user_id));
		if(empty($count)){
			return false;
		}
		return true;
	}

	// Oh NO ! YOU're using WP CoAuthors, the load and query increases.
	
	$courses = bp_course_get_user_courses(get_current_user_id());
	if(empty($courses))
		return false;

	if(!empty($courses)){
		foreach($courses as $course_id){

		}
	}

}
/*== DRIP FUNCTIONS === */

function bp_course_check_course_drip($course_id,$user_id=null){
	
	if(empty($user_id)){
		$user_id = get_current_user_id();
	}

	$template = BP_Course_Template::init();
	return $template->check_course_drip($user_id,$course_id);
}

function bp_course_get_total_course_drip_duration($course_id,$user_id=null){
	
	if(empty($user_id)){
		$user_id = get_current_user_id();
	}

	$template = BP_Course_Template::init();
	return $template->get_total_course_drip_duration($user_id,$course_id);
}

function bp_course_get_drip_access_time($unit_id,$user_id,$course_id=null){

	$template = BP_Course_Template::init();
	$time = $template->get_drip_access_time($unit_id,$user_id,$course_id);
	return $time;
}

function bp_course_update_drip_Access_time($unit_id,$user_id,$time,$course_id=null){
	if(empty($course_id)){
		update_post_meta($unit_id,$user_id,$time);
	}else{
		update_user_meta($user_id,'start_unit_'.$unit_id.'_'.$course_id,$time);	
	}
}


function bp_course_get_drip_status($course_id,$user_id,$unit_id){

	// Drip Feed Check    
	$return = array('status'=>0,'message'=>'');

    $drip_enable= bp_course_check_course_drip($course_id,$user_id);
    if($drip_enable){
        
        $total_drip_duration = bp_course_get_total_course_drip_duration($course_id,$user_id);

        $units = bp_course_get_curriculum_units($course_id);
        $unitkey = array_search($unit_id,$units);

        for($i=($unitkey-1);$i>=0;$i--){
            if(bp_course_get_post_type($units[$i]) == 'unit' ){
              $pre_unit_key = $i;
              break;
            }
        }

        $total_drip_duration = apply_filters('vibe_total_drip_duration',$total_drip_duration,$course_id,$unit_id,$units[$pre_unit_key]);
        
        
        if($unitkey == 0){ // Start of Course
            $pre_unit_time = bp_course_get_drip_access_time($units[$unitkey],$user_id,$course_id);
            if(!isset($pre_unit_time) || $pre_unit_time ==''){
                if(is_numeric($units[1])){
                    bp_course_update_drip_access_time($units[$unitkey],$user_id,time(),$course_id); 
                    //Parmas : Next Unit, Next timestamp, course_id, userid
                    do_action('wplms_start_unit',$units[$unitkey],$course_id,$user_id,$units[1],(time()+$total_drip_duration));
                    
                }
            }

        }else{
                //Continuation of Course
                $pre_unit_time=bp_course_get_drip_access_time($units[$pre_unit_key],$user_id,$course_id);
                if(!empty($pre_unit_time)){
                    
                      $value = $pre_unit_time + $total_drip_duration;
                      
                      $value = apply_filters('wplms_drip_value',$value,$units[$pre_unit_key],$course_id,$units[$unitkey],$units);

                      //print_r(date('l jS \of F Y h:i:s A',$value).' > '.date('l jS \of F Y h:i:s A',time()));
                     if($value > time()){
                            $element = apply_filters('wplms_drip_feed_element_in_message',__('Unit','vibe'),$course_id);
                            $return['status'] = 1;
                        	$return['message'] = '<div class="message"><p>'.sprintf(__('%s will be available in %s','vibe'),$element,tofriendlytime($value-time())).'</p></div>';
                        }else{
                            $pre_unit_time=bp_course_get_user_unit_completion_time($user_id,$units[$unitkey],$course_id);
                            if(!isset($pre_unit_time) || $pre_unit_time ==''){
                              bp_course_update_unit_user_access_time($units[$unitkey],$user_id,time(),$course_id);

                              //Parmas : Next Unit, Next timestamp, course_id, userid
                              do_action('wplms_start_unit',$units[$unitkey],$course_id,$user_id,$units[$unitkey+1],(time()+$total_drip_duration));
                            }
                        } 

                  }else{
                        if(isset($pre_unit_key)){
                          $completed = bp_course_get_user_unit_completion_time($user_id,$units[$pre_unit_key],$course_id);

                          if(!empty($completed)){
                              bp_course_update_unit_user_access_time($units[$pre_unit_key],$user_id,time(),$course_id);
                              $pre_unit_time = time();
                              $value = $pre_unit_time + $total_drip_duration;
                              $value = apply_filters('wplms_drip_value',$value,$units[$pre_unit_key],$course_id,$units[$unitkey],$units);
                              $element = apply_filters('wplms_drip_feed_element_in_message',__('Unit','vibe'),$course_id);

                                $return['status'] = 1;
                        		$return['message'] =  '<div class="message"><p>'.sprintf(__('%s will be available in %s','vibe'),$element,tofriendlytime($value-time())).'</p></div>';
                            }else{
                              $element = apply_filters('wplms_drip_feed_element_in_message',__('Unit','vibe'),$course_id);

                                $return['status'] = 1;
                        		$return['message'] =  '<div class="message"><p>'.sprintf(__('%s can not be accessed.','vibe'),$element).'</p></div>';
                            }
                      }else{
                        $element = apply_filters('wplms_drip_feed_element_in_message',__('Unit','vibe'),$course_id);

                        $return['status'] = 1;
                        $return['message'] = '<div class="message"><p>'.sprintf(__('%s can not be accessed.','vibe'),$element).'</p></div>';

                      }
                }    //Empty pre-unit time
        }
    }  // Drip enabled
    return $return;
}
/*
END DRIP FUNCTIONS
 */

function bp_course_get_course_applicants_count($course_id){
	global $wpdb;

	$count = $wpdb->get_var($wpdb->prepare("SELECT count(user_id) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %d",'apply_course'.$course_id,$course_id));
	return (empty($count)?0:$count);
}

function bp_course_get_max_students($course_id = NULL,$user_id = NULL){
	if(empty($course_id)){
		$course_id = get_the_ID();
	}
	if(is_user_logged_in()){
		$user_id = get_current_user_id();
	}
	$max_students = apply_filters('bp_course_get_max_students','',$course_id,$user_id);

	if(empty($max_students)){
		$max_students = get_post_meta($course_id,'vibe_max_students',true);
	}

	return $max_students;
}

function bp_course_get_start_date($course_id = NULL,$user_id = NULL){
	if(empty($course_id)){
		$course_id = get_the_ID();
	}
	if(is_user_logged_in()){
		$user_id = get_current_user_id();
	}
	$start_date = apply_filters('bp_course_get_start_date','',$course_id,$user_id);
	if(empty($start_date)){
		$start_date = get_post_meta($course_id,'vibe_start_date',true);
	}

	return $start_date;
}

function bp_course_get_course_duration($course_id = NULL,$user_id = NULL){

	if(empty($course_id)){
		$course_id = get_the_ID();
	}
	if(is_user_logged_in()){
		$user_id = get_current_user_id();
	}
	$duration = apply_filters('bp_course_get_course_duration','',$course_id,$user_id);
	
	if(empty($duration)){
		$set_duration = get_post_meta($course_id,'vibe_duration',true);
		$course_duration_parameter = get_post_meta($course_id,'vibe_course_duration_parameter',true);
		if(empty($course_duration_parameter)){
			$course_duration_parameter  = 86400;
		}
		$duration = (int)$set_duration*(int)$course_duration_parameter;
	}

	return $duration;
}

function bp_course_get_marks($user_id,$course_id){
	$marks = get_post_meta($course_id,$user_id,true);
	$marks = apply_filters('wplms_course_marks',$marks,$course_id);
}
/* === Plugins === */

function bp_course_is_plugin(){
    $action = bp_current_action();
    if(empty($action) && !empty($_GET['action'])){
        $action = $_GET['action'];
    }
    $check = apply_filters('bp_course_is_plugin_'.$action,false);
    return $check;
}

function bp_course_get_quiz_results_meta($quiz_id,$user_id,$activity_id = NULL){
	global $wpdb,$bp;
	$result=0;
	if(bp_is_active('activity')){
		if(!empty($activity_id)){
			$result = $wpdb->get_var($wpdb->prepare( "
	    							SELECT meta_value 
	    							FROM {$bp->activity->table_name_meta} 
	    							WHERE activity_id = %d
	    							AND meta_key = 'quiz_results'
									ORDER BY id DESC
									LIMIT 0,1
								" ,$activity_id));
		}else{
			$result = $wpdb->get_var($wpdb->prepare( "
				SELECT m.meta_value 
				FROM {$bp->activity->table_name} as a 
				LEFT JOIN {$bp->activity->table_name_meta} as m ON a.id = m.activity_id 
				WHERE m.meta_key = 'quiz_results' 
				AND a.secondary_item_id = $quiz_id 
				AND a.user_id = $user_id 
				ORDER BY a.id LIMIT 0,1"));
		}
	}

	return $result;
}


function bp_course_quiz_results($quiz_id,$user_id,$course=NULL){
	
	global $wpdb,$bp;
	if(function_exists('bp_is_active') && bp_is_active('activity')){
	    $activity_id = $wpdb->get_var($wpdb->prepare( "
	    							SELECT id 
	    							FROM {$bp->activity->table_name}
	    							WHERE secondary_item_id = %d
									AND type = 'quiz_evaluated'
									AND user_id = %d
									ORDER BY date_recorded DESC
									LIMIT 0,1
								" ,$quiz_id,$user_id));
	    if(!empty($activity_id)){
	    	$results = bp_course_get_quiz_results_meta($quiz_id,$user_id,$activity_id);
	    	$url = bp_activity_get_permalink($activity_id);
	    }
	}

	$sum=$total_sum= 0;$extra='';
	if(isset($course) && is_numeric($course) && !defined('DOING_AJAX')){
		$extra = '<a href="'.get_permalink($course).'" class="small_link">( &larr; '.__('BACK TO COURSE','vibe').' )</a>';	
	}

	echo '<div class="quiz_result"><h3 class="heading"><span>'.get_the_title($quiz_id).'</span>'.$extra;
	if(function_exists('social_sharing') && !empty($url)){
		echo social_sharing('top',$url);
	}
	echo '<strong class="right"><a class="print_results"><i class="icon-printer-1"></i></a></strong></h3>';

	$show_message_in_results = apply_filters('wplms_show_message_in_results',false);
	if($show_message_in_results){
		$message = get_post_meta($quiz_id,'vibe_quiz_message',true);
		if(isset($message) && strlen($message) > 3){
			echo apply_filters('the_content',$message);
			//here this action "wplms_after_quiz_message" is not called because this function already displaying results 
		}	
	}

	$flag = apply_filters('wplms_show_quiz_correct_answer',true,$quiz_id);
	$hide_result_details = apply_filters('wplms_hide_quiz_result_details',0,$quiz_id);
	if($hide_result_details){
		ob_start();
	}
	$all_questions_json = array();
	if(!empty($results) && !isset($_GET['force'])){
		$quiz_status = bp_course_get_user_quiz_status($user_id,$quiz_id);
		$results = unserialize($results);
		
		echo '<ul class="quiz_questions">';
		foreach($results as $question_id=> $question){
			if(!empty($question['content'])){

				//Save Question ID for question json used in retakes
				$all_questions_json[]=$question_id;
				//no apply filters on content coz apply filters was already applied while saving question content
				echo '<li>
					<div class="q">'.$question['content'].'</div>';

					if($question['type'] == 'survey'){
						echo '<strong>'.__('Marked Choice :','vibe').'</strong> '.apply_filters('question_the_content',$question['marked_answer']).'<span>'._x('Score','survery question score awarded','vibe').' : '.$question['marks'].'</span>';
						if(!empty($question['explaination']) && strlen($question['explaination']) > 5){
					 		echo '<a class="show_explaination tip" title="'.__('View explanation','vibe').'"></a>';
						}
						echo '</strong>';
						$sum +=intval($question['marks']);
						$total_sum += 0;
					}else{

						echo '<strong>'.__('Marked Answer :','vibe').'</strong> '.apply_filters('question_the_content',$question['marked_answer']);

						if(isset($question['correct_answer']) && $question['correct_answer'] !='' && isset($question['marks']) && $question['marks'] !='' && $flag){
							echo '<strong>'.__('Correct Answer : ','vibe').do_shortcode($question['correct_answer']).'<span>';
						 	if(!empty($question['explaination']) && strlen($question['explaination']) > 5){
						 		echo '<a class="show_explaination tip" title="'.__('View answer explanation','vibe').'"></a>';
							}
							echo '</span></strong>';
						}

						echo '<span> '.__('Total Marks :','vibe').' '.$question['max_marks'].'</span>';
						$total_sum +=$question['max_marks'];


						if(isset($question['marks']) ){
							if($question['marks'] > 0){
								echo '<span>'.__('MARKS OBTAINED','vibe').' <i class="icon-check"></i> '.$question['marks'].'</span>';
							}else{
								echo '<span>'.__('MARKS OBTAINED','vibe').' <i class="icon-x"></i> '.$question['marks'].'</span>';
							}
							$sum +=intval($question['marks']);
						}else{
							echo '<span>'.__('Marks Obtained','vibe').' <i class="icon-alarm"></i></span>';
						}
						if(!empty($question['explaination']) && strlen($question['explaination']) > 5 && $flag){
							echo '<div class="explaination">'.apply_filters('the_content',$question['explaination']).'</div>';
						}
					}

				echo '</li>';	
			}	
		}
		echo '</ul>';

	}else{

		$user_result = array();
		$questions = bp_course_get_quiz_questions($quiz_id,$user_id);
		
		$quiz_status = bp_course_get_user_quiz_status($user_id,$quiz_id);

		if(count($questions)){
			echo '<ul class="quiz_questions">';

			foreach($questions['ques'] as $key=>$question){
				if(isset($question) && is_numeric($question)){

					//Save Question ID for question json used in retakes
					$all_questions_json[]=$question;
					
					$q=get_post($question);
					$user_result[$question] = array('content'=>apply_filters('the_content',$q->post_content));
					echo '<li><div class="q">'.apply_filters('the_content',$q->post_content).'</div>';
				
					$user_marked_answer = bp_course_get_question_marked_answer($quiz_id,$question,$user_id);	

					$type = get_post_meta($question,'vibe_question_type',true);
					$user_result[$question]['type'] = $type;
					if($type == 'survey'){

						$options = get_post_meta($question,'vibe_question_options',true);
						if(!empty($options)){
							$user_result[$question]['marked_answer'] =apply_filters('the_content',$options[(intval($user_marked_answer)-1)]);	
						}else{
							$user_result[$question]['marked_answer'] = '';
						}
			
			        	$explaination = get_post_meta($question,'vibe_question_explaination',true);

						
						$marks = bp_course_get_user_question_marks($quiz_id,$question,$user_id);
						$user_result[$question]['marks'] = $marks;
						
						echo '<strong>'.__('Marked Choice :','vibe').'</strong> '.apply_filters('the_content',$user_marked_answer).'<span>'._x('Score','survery question score awarded','vibe').' : '.$marks.'</span>';
						if(!empty($question['explaination']) && strlen($question['explaination']) > 5){
							$user_result[$question]['explaination'] = apply_filters('the_content',$explaination);
					 		echo '<a class="show_explaination tip" title="'.__('View explanation','vibe').'"></a>';
						}
						echo '</strong>';
						
						$sum += intval($marks);
						$total_sum += 0;

					}else{

						echo '<strong>';_e('Marked Answer :','vibe');echo '</strong>';

						$correct_answer=get_post_meta($question,'vibe_question_answer',true);
						$marks=0;
						$type = get_post_meta($question,'vibe_question_type',true);
						$user_result[$question]['type'] = $type;
				    
					    switch($type){
					      	case 'truefalse': 
					      		$options = array( 0 => __('FALSE','vibe'),1 =>__('TRUE','vibe'));
					      		if(is_numeric($user_marked_answer)){
					      			$user_result[$question]['marked_answer'] = $options[$user_marked_answer];
					      		}else{$user_result[$question]['marked_answer']='';}
					        	

					        	if(isset($correct_answer) && $correct_answer !=''){
					        		$ans=$options[(intval($correct_answer))];
					        	}
					      	break;  	
					      	case 'single':
					      	case 'survey': 
				      			$options = vibe_sanitize(get_post_meta($question,'vibe_question_options',false));
					      		
					      		if(is_numeric($user_marked_answer)){
					      			$user_result[$question]['marked_answer'] =apply_filters('the_content',$options[(intval($user_marked_answer)-1)]); // Reseting for the array
					      		}else{$user_result[$question]['marked_answer']='';}
					      		
					        	if(isset($correct_answer) && $correct_answer !=''){
					        		$ans=$options[(intval($correct_answer)-1)];
					        	}
					      	break;  
					      	case 'sort': 
					      	case 'match': 
					      	case 'multiple': 
			              		$options = vibe_sanitize(get_post_meta($question,'vibe_question_options',false));
			              		$ans=explode(',',$user_marked_answer);

			              		foreach($ans as $an){
			                		$user_result[$question]['marked_answer'] .= apply_filters('the_content',$options[intval($an)-1]).' ';
			              		}

			              		$cans = explode(',',$correct_answer);
			              		$ans='';
			              		foreach($cans as $can){
			                		$ans .= $options[intval($can)-1].', ';
			              		}
			            	break;
			            	case 'select':
			            		$options = vibe_sanitize(get_post_meta($question,'vibe_question_options',false));
					      		
					      		$and = __('and','vibe');
					      		if(strpos($correct_answer, '|') !== false){
					      			$c_answers = explode('|',$correct_answer);
					      			foreach($c_answers as $i=>$ca){
					      				$c_answers[$i] = $options[(intval($ca)-1)];
					      			}
					      			$ans = implode(' '.$and.' ', $c_answers);
					      			if(strpos($user_marked_answer, '|') !== false){
					      				$um_answers = explode('|',$user_marked_answer);
						      			foreach($um_answers as $i=>$um){
						      				if($um !=''){
						      					$um_answers[$i] = $options[(intval($um)-1)];
						      				}
						      			}
						      			$user_result[$question]['marked_answer'] =implode(' '.$and.' ', $um_answers);
					      			}
					      		}else{
					      			$user_result[$question]['marked_answer'] =apply_filters('the_content',$options[(intval($user_marked_answer)-1)]); // Reseting for the array
					      			if(isset($correct_answer) && $correct_answer !=''){
						        		$ans=$options[(intval($correct_answer)-1)];
						        	}
					      		}
					        	
			            	break;
					      	case 'fillblank':
					      		$and = __('and','vibe');
					      		$user_marked_answer = rtrim($user_marked_answer,'|');
					      		$user_marked_answer = str_replace('|',' '.$and.' ',$user_marked_answer);
					      		$correct_answer = str_replace('|',' '.$and.' ',$correct_answer);
					      		$user_result[$question]['marked_answer'] = apply_filters('the_content',$user_marked_answer);
					        	$ans = $correct_answer;
					      	break;
					      	case 'smalltext': 
					        	$user_result[$question]['marked_answer'] = apply_filters('the_content',$user_marked_answer);
					        	$ans = $correct_answer;
					      	break;
					      	case 'largetext': 
					        	$user_result[$question]['marked_answer'] = apply_filters('the_content',$user_marked_answer);
					        	$ans = $correct_answer;
					      	break;
					      	default:
					      		$user_marked_answer = apply_filters('wplms_user_results_marked_answer',$user_marked_answer,$question,$quiz_id);
					      		$user_result[$question]['marked_answer'] = apply_filters('the_content',$user_marked_answer);
					      		$correct_answer = apply_filters('wplms_user_results_correct_answer',$correct_answer,$question,$quiz_id);
					        	$ans = $correct_answer;
					        	
					      	break;
						}//End switch

						echo $user_result[$question]['marked_answer'];
						$user_result[$question]['correct_answer']=apply_filters('the_content',$ans);
						
						$marks = '';
						if($quiz_status){
							$marks = bp_course_get_user_question_marks($quiz_id,$question,$user_id);	
						}
						

						if(isset($correct_answer) && $correct_answer !='' && isset($marks) && $marks !='' && $flag){
							$explaination = get_post_meta($question,'vibe_question_explaination',true);
							$user_result[$question]['explaination'] = apply_filters('the_content',$explaination);
							echo (($type != 'survey')?'<strong>'.__('Correct Answer :','vibe').'<span>'.apply_filters('the_content',$ans).' ':'<strong><span>');
							echo ((isset($explaination) && $explaination && strlen($explaination) > 5)?'<a class="show_explaination tip" title="'.__('View answer explanation','vibe').'"></a>':'').'</span></strong>';
						}

						$total_sum = $total_sum+intval($questions['marks'][$key]);
						$user_result[$question]['max_marks'] = $questions['marks'][$key];
						echo '<span> '.__('Total Marks :','vibe').' '.$questions['marks'][$key].'</span>';

						if(isset($marks) && $marks !=''){
							if($marks > 0){
								echo '<span>'.__('MARKS OBTAINED','vibe').' <i class="icon-check"></i> '.$marks.'</span>';
							}else{
								echo '<span>'.__('MARKS OBTAINED','vibe').' <i class="icon-x"></i> '.$marks.'</span>';
							}
							$user_result[$question]['marks']=$marks;
							$sum = $sum+intval($marks);
						}else{
							echo '<span>'.__('Marks Obtained','vibe').' <i class="icon-alarm"></i></span>';
						}

						if(isset($explaination) && $explaination && strlen($explaination) > 5 && $flag){
							echo '<div class="explaination">'.apply_filters('the_content',$explaination).'</div>';
						}
					} // End survey if
					echo '</li>';
				} // IF question check
			}// END FOR
			echo '</ul>';
		}// End count questions

		if($hide_result_details){
			ob_end_clean();
		}
		$user_result['user_marks'] = $sum;
		$user_result['total_marks'] = $total_sum;

		if(!empty($activity_id)){
			bp_course_generate_user_result($quiz_id,$user_id,$user_result,$activity_id);
		}
	} //End cached Meta check	
	  
	$template = BP_Course_Template::init();
	$template->all_questions_json = $all_questions_json;

	echo '<div id="total_marks">'.__('Total Marks','vibe').' '.($quiz_status?'<strong><span>'.$sum.'</span>'.(empty($total_sum)?'':' / '.$total_sum).'</strong>':'<strong><i class="icon-alarm"></i></strong>').' </div>';

	$remarks = bp_course_get_quiz_remarks($quiz_id,$user_id);
	if(!empty($remarks)){
		echo '<div class="quiz_remarks_in_result">'.do_shortcode($remarks).'</div>';	
	}
	
	echo '</div>';
}

function bp_course_quiz_retake_form($quiz_id,$user_id,$course=NULL){

	if(current_user_can('edit_posts')){
		$retakes = 9999;
	}else{
		$retakes=apply_filters('wplms_quiz_retake_count',get_post_meta($quiz_id,'vibe_quiz_retakes',true),$quiz_id,$course,$user_id);
	}

	if(isset($retakes) && $retakes){
		global $bp,$wpdb;
		$table_name=$bp->activity->table_name;

		$retake_count = (int)bp_course_fetch_user_quiz_retake_count($quiz_id,$user_id);
		
		if( ($retakes - $retake_count) > 0){
			echo '<form method="post" class="quiz_retake_form '.apply_filters('wplms_in_course_quiz','').'" action="'.get_permalink($quiz_id).'">';

			$template = BP_Course_Template::init();
			if(isset($template->all_questions_json)){
				echo '<input type="hidden" id="all_questions_json" value='.json_encode($template->all_questions_json).'>';
			}
			echo '<input type="submit" name="initiate_retake" value="'.__('RETAKE QUIZ','vibe').'" />';
			echo '<p id="retakes'.$retakes.'">'.__('Number of retakes ','vibe').' : <strong>'.$retake_count.__(' out of ','vibe').$retakes.'</strong></p>';
			wp_nonce_field('retake'.$user_id,'security');
			echo '</form>';
		}

		echo '<h4 id="prev_results"><a href="#">'.__('Previous Results for Quiz ','vibe').'</a></h4>';
		$quiz_results = $wpdb->get_results($wpdb->prepare( "
						SELECT activity.content FROM {$table_name} AS activity
						WHERE 	activity.component 	= 'course'
						AND 	( activity.type 	= 'quiz_evaluated' OR activity.type 	= 'evaluate_quiz' )
						AND 	user_id = %d
						AND 	( item_id = %d OR secondary_item_id = %d )
						ORDER BY date_recorded DESC
					" ,$user_id,$quiz_id,$quiz_id));

		if(count($quiz_results) > 0){
			echo '<ul class="prev_quiz_results">';
			foreach($quiz_results as $content){
				echo '<li>'.$content->content.'</li>';
				}
				echo '</ul>';
		}else{
			echo '<div id="message"><p>'.__('Results not Available','vibe').'</p></div>';
		}
	} // END Retakes
}


/* Quiz Remarks */

function bp_course_get_quiz_remarks($quiz_id,$user_id){
	$remarks = get_user_meta($user_id,'quiz_remarks'.$quiz_id,true);
	return $remarks;
}

function bp_course_set_quiz_remarks($quiz_id,$user_id,$remarks){
	update_user_meta($user_id,'quiz_remarks'.$quiz_id,$remarks);
}


<?php
/**
 * Filter functions for Course Module
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Course Module
 * @version     2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class bp_course_filters{

    public static $instance;

    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new bp_course_filters();
        return self::$instance;
    }

    private function __construct(){

		add_action('wp_ajax_course_filter', array($this,'course_filter'));
		add_action('wp_ajax_nopriv_course_filter', array($this,'course_filter'));
		add_action('bp_ajax_querystring', array($this,'filtering_instructor_custom'),20,2);
		add_filter('bp_ajax_querystring', array($this,'bp_course_ajax_querystring'),20,2);
		add_filter('wplms_course_product_id', array($this,'wplms_expired_course_product_id'),10,3);
		add_action('wplms_course_before_front_main', array($this,'wplms_renew_free_course'));


		add_filter('vibe_course_duration_parameter',array($this,'course_duration_filter'),99,2);
		add_filter('vibe_drip_duration_parameter',array($this,'drip_duration_filter'),99,2);
		add_filter('vibe_unit_duration_parameter',array($this,'unit_duration_filter'),99,2);
		add_filter('vibe_quiz_duration_parameter',array($this,'quiz_duration_filter'),99,2);
		add_filter('vibe_product_duration_parameter',array($this,'product_duration_filter'),99,2);
		add_filter('vibe_assignment_duration_parameter',array($this,'assignment_duration_filter'),99,2);

		add_filter('wplms_curriculum_course_lesson',array($this,'course_lesson_details'),10,3);
		add_filter('wplms_unit_classes',array($this,'wplms_incourse_quiz_stop_notes'),10,2);
		// Apply for Course button
		add_filter('wplms_take_this_course_button_label',array($this,'apply_course_button_label'),10,2);
		add_filter('wplms_private_course_button_label',array($this,'apply_course_button_label'),10,2);
		add_filter('wplms_course_product_id',array($this,'apply_course_button_link'),10,2);
		add_filter('wplms_private_course_button',array($this,'apply_course_button_link'),10,2);
		add_filter('wplms_course_details_widget',array($this,'hide_price'),10,2);
		add_filter('wplms_course_submission_tabs',array($this,'apply_course_submission_tab'),10,2);
        
        add_filter('wplms_drip_feed_element_in_message',array($this,'drip_feed_element'),10,2);
        add_filter('wplms_drip_value',array($this,'evaluate_course_drip'),99,4);
        add_filter('vibe_total_drip_duration',array($this,'total_drip_duration'),10,4);

        add_filter('the_content',array($this,'check_vc'),1);
        add_filter('bp_course_next_unit_access',array($this,'next_unit_access'),10,2);

        add_filter('bp_activity_user_can_delete',array($this,'student_restricted_activities'),10,2);
        
        add_filter('bp_get_course_certificate_url',array($this,'certificate_url'),10,3);
		add_filter('bp_course_certificate_class',array($this,'certificate_class'),10,2);
		//(isset($_GET['regenerate_certificate'])?'regenerate_certificate':'')
		add_filter('question_the_content',array($this,'execute_shortcode_in_questions'));
		add_filter('bp_get_profile_field_data',array($this,'check_for_array'),9,2);
		add_filter('get_comment',array($this,'avatar_on_comment_screen_for_units_fix'));
		
		//for news permalink 
		add_filter('wplms_course_nav_menu',array($this,'wplms_course_news_menu'));
		add_filter('wplms_course_locate_template',array($this,'wplms_course_news_template'),10,2);

		//wpml course count
		add_filter('bp_course_total_count',array($this,'wpml_course_count'));

		//Quiz Lock API SITE FILTER
		add_filter('bp_course_api_check_quiz_lock',array($this,'api_site_quiz_lock'),10,4);

		// Check unit/quiz access for the partially free course.
		add_filter('wplms_check_partial_free_course_access',array($this,'check_access_for_partial_free_course'),10,2);


		//course video 
		add_filter('bp_course_get_avatar',array($this,'video_thumbnail_html'));
		add_filter('wplms_featured_component_filter',array($this,'wplms_featured_component'),10,4);

    }

    function wplms_featured_component($custom_post_thumbnail,$custom_post_id,$cols,$style){
    	$course_video = get_post_meta($custom_post_id,'post_video',true);
		if(!empty($course_video)){
			$post_link = $course_video;
			$post_class = 'course_video_popup';
			$more_html = '<span class="icon_wrapper"></span>';
			$custom_post_thumbnail = '';
		    $default_image = vibe_get_option('default_course_avatar');
		    if(!in_array($cols,array('big','small','medium','mini','full'))){
		        switch($cols){
		          case '2':{ $cols = 'big';
		          break;}
		          case '3':{ $cols = 'medium';
		          break;}
		          case '4':{ $cols = 'medium';
		          break;}
		          case '5':{ $cols = 'small';
		          break;}
		          case '6':{ $cols = 'small';
		          break;}  
		          default:{ $cols = 'full';
		          break;}
		        }
		    }
		    
		    if(has_post_thumbnail($custom_post_id)){
		        $custom_post_thumbnail=  '<a class="'.$post_class.'" href="'.$post_link.'">'.get_the_post_thumbnail($custom_post_id,$cols).$more_html.'</a>';
		    }else if(isset($default_image) && $default_image){
		    	$custom_post_thumbnail=  '<a class="'.$post_class.'" href="'.$post_link.'">'.'<img src="'.$default_image.'" />'.$more_html.'</a>';
		    }
		        
		}               
	    return $custom_post_thumbnail; 
    }

    function video_thumbnail_html($thumb){
    	$defaults = array(
		'id' => get_the_ID(),
		'size'  => 'full'
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		$thumb = '';
		$post_link = get_permalink($id);
		$post_class = '';
		$more_html = '';
		$course_video = get_post_meta($id,'post_video',true);
		if(!empty($course_video)){
			$post_link = $course_video;
			$post_class = 'course_video_popup';
			$more_html = '<span class="icon_wrapper"></span>';
		}
		if(has_post_thumbnail($id)){
			$thumb='<a class="'.$post_class.'" href="'.$post_link.'" title="'.the_title_attribute('echo=0').'">'.get_the_post_thumbnail($id,$size).$more_html.'</a>';
		}else{
			$default_course_avatar = vibe_get_option('default_course_avatar');
			if(isset($default_course_avatar) && $default_course_avatar){
				$thumb='<a class="'.$post_class.'" href="'.$post_link.'" title="'.the_title_attribute('echo=0').'"><img src="'.$default_course_avatar.'" />'.$more_html.'</a>';
			}
		}
		return $thumb;
    }

    function check_access_for_partial_free_course($flag,$unit_id){

		if(isset($_POST['course_id'])){
		    $course_id = $_POST['course_id'];
		}else if(isset($_COOKIE['course'])){
		    $course_id = $_COOKIE['course'];
		}

		if( !isset($course_id) || !isset($unit_id) || $course_id == '' || $unit_id == '' )
			return $flag;
		//Check for free course
        $free = get_post_meta($course_id,'vibe_course_free',true);
        if( vibe_validate($free) )
            return $flag;
		//Check Partial free course setting.
		$partial_free_course = get_post_meta($course_id,'vibe_partial_free_course',true);
		if( !vibe_validate($partial_free_course) )
			return $flag;

		//Check if already purchased the course.
		$user_id = get_current_user_id();
		$check_partial_free_course_purchased = get_user_meta($user_id,'user_subscribed_to_partial_free_course_'.$course_id,true);
		if( $check_partial_free_course_purchased == 2 )
			return $flag;

		$course_curriculum = bp_course_get_curriculum($course_id);
		if( !empty($course_curriculum) ){
			$unit_accessible = array();
			foreach ($course_curriculum as $key => $value) {
				if($key != 0){
					if( !is_numeric($value) )
						break;

					$unit_accessible[] = $value;
				}
			}

			if( in_array($unit_id, $unit_accessible) )
				return 1;

			echo '<div class="main_unit_content unit_class">';
			echo __('Please purchase this course to continue with this course.','vibe');

			$pid = get_post_meta($course_id,'vibe_product',true);
			$pid = apply_filters('wplms_course_product_id',$pid,$course_id,0);

			if(is_numeric($pid) && bp_course_get_post_type($pid) == 'product'){
				$pid = get_permalink($pid);
				$check = vibe_get_option('direct_checkout');
				$check = intval($check);
				if( isset($check) &&  $check ){
					$pid .= '?redirect';
				}
			}
			$extra = '';
			echo '<a href="'.$pid.'" class="course_button full button">'.__('Purchase This Course','vibe').apply_filters('wplms_course_button_extra',$extra,$course_id).'</a>';
			echo '</div>';

			echo bp_course_credits(array('id'=>$course_id,'partial'=>1));

			return 0;
		}

		return $flag;
	}

    // API Site Quiz lock
    function api_site_quiz_lock($flag,$quiz_id,$user_id,$type){
    	$quiz_lock = bp_course_get_setting( 'quiz_lock', 'api','boolean' );
    	if($quiz_lock){
    		$check = get_user_meta($user_id,'quiz_lock_'.$quiz_id,true);
    		if(empty($check)){
    			$duration = bp_course_get_quiz_duration($quiz_id);
    			update_user_meta($user_id,'quiz_lock_'.$quiz_id,array('type'=>$type,'expires'=>time()+$duration));
    		}else{
    			if($check['type'] != $type && $check['expires'] < time()){
    				return false;
    			}
    		}
    	}

    	return $flag;
    }

    function wpml_course_count($count_course){
    	global $wpdb;
    	if(defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE){
    		if(function_exists('vibe_get_option')){
    			$hidden_courses = vibe_get_option('hide_courses');
    		}
    		if(!empty($hidden_courses)){
    			$h_courses = array();
    			foreach ($hidden_courses as $key => $value) {
    				$h_courses[] = "'".$value."'";
    			}
    			$h_courses = implode(",",$h_courses);
    			$h_courses = (empty($h_courses)?"":"AND ID NOT IN({$h_courses})");
    		}
    		global $wpdb;
    		$table = $wpdb->prefix.'icl_translations';
    	
    		$count = $wpdb->get_var($wpdb->prepare("
    		SELECT COUNT(DISTINCT(p.ID))
			FROM {$wpdb->posts} as p
			INNER JOIN {$table} as icl
			ON p.ID = icl.element_id AND p.post_type = 'course' AND icl.language_code = %s AND p.post_status = 'publish' $h_courses",ICL_LANGUAGE_CODE));
			if(empty($count))$count=0;
			$count_course = $count;
    	}
    	return $count_course;
    }

    function wplms_course_news_menu($links){

		if(function_exists('vibe_get_option')){
			$show_news = vibe_get_option('show_news');
			if(!empty($show_news)){
				if(class_exists('Vibe_CustomTypes_Permalinks')){
					$p = Vibe_CustomTypes_Permalinks::init();
		    		$permalinks = $p->permalinks;
		    		$slug = '';
					if(!empty($permalinks) && !empty($permalinks['news_slug'])){
						$slug = str_replace('/', '', $permalinks['news_slug']);
					}else{
						$slug = _x('news','news permalink in course','vibe');
					}
					$links['news'] = array(
	                        'id' => 'news',
	                        'label'=>__('News','vibe'),
	                        'action' => $slug,
	                        'link'=>bp_get_course_permalink(),
	                    );
				}
			}
		}
		return $links;
	}

	function wplms_course_news_template($template,$action){
		if(function_exists('vibe_get_option')){
			$show_news = vibe_get_option('show_news');
			if(!empty($show_news)){
		      	if($action == 'news'){ 
		          $template= array(get_template_directory('course/single/plugins.php'));
		      	}
		  	}
		}
      return $template;
    }

    function avatar_on_comment_screen_for_units_fix($comment){
	    if(is_admin()){
	      if(function_exists('get_current_screen')){
	        $hook = get_current_screen();
	        if(!empty($hook->base) && $hook->base == 'edit-comments'){
	          if(!empty($comment->comment_type)){
	            unset($comment->comment_type);
	          }
	          return $comment; 
	        }
	      }
	    }
	    return $comment;
	}

    function check_for_array($field,$args){
    	if(!empty($field) && is_array($field))
    		$field = implode($field,',');
    	return $field;
    }
    function execute_shortcode_in_questions($q){
    	return do_shortcode($q);
    }
    
    function certificate_url($url,$course_id,$user_id){

    	if(!empty($url) || empty($course_id) || empty($user_id) || isset($_GET['regenerate_certificate'])){

    		return $url;
    	}
    	

    	global $wpdb;

    	$att = $wpdb->get_row($wpdb->prepare("SELECT ID,post_author FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent = %d AND post_name = %s",$course_id,'certificate_'.$course_id.'_'.$user_id.''),ARRAY_A);
    	

    	if(!empty($att)){

    		//Certificate can be uploaded via Course instructor and User
	    	$course_author = get_post_field('post_author',$course_id);
	    	$authors=array($course_author);
			$certificate_authors = apply_filters('wplms_course_instructors',$authors,$course_id);
			$certificate_authors[] = $user_id;


    		$attachment_id = $att['ID'];

    		if( (in_array($att['post_author'],$certificate_authors) || user_can($att['post_author'],'manage_options'))){

	    		$attachment = wp_get_attachment_image_src($attachment_id,'full');
		    	$url = $attachment[0];
		    	if(!empty($url)){$this->certificate_url[$course_id.'-'.$user_id]=$url;}

	    	}
    	} 

    	return $url;
    }

    function certificate_class($class,$args){

    	extract($args);
    	if(empty($course_id) || empty($user_id))
    		return;

    	if(!empty($this->certificate_url[$course_id.'-'.$user_id])){
    		$class .=' certificate_image';
    	}

    	return $class;
    }

    function student_restricted_activities($flag, $activity){
    	
    	if(!current_user_can('edit_posts') && (isset($activity->component) && $activity->component == 'course')){ 
    	// Student user role
    		$restricted_activites = array('subscribe_course','start_course','submit_course','retake_course','reset_course','student_badge','student_certificate','review_course','course_code','renew_course','unit',
    			'unit_complete','unit_comment','start_quiz','submit_quiz','quiz_evaluated','retake_quiz','reset_quiz','assignment_started','assignment_submitted','evaluate_assignment','reset_assignment');	
    		if(in_array($activity->type,$restricted_activites)){
    			return false;
    		}
    	}

    	return $flag;
    }

	function next_unit_access($next_unit_access,$course_id){ 
		$next_unit_access = true;
		if(function_exists('vibe_get_option')){
			$nextunit_access = vibe_get_option('nextunit_access');	
			if(!empty($nextunit_access)){
				$next_unit_access = false;
			}
		}
		$nextunit_access = get_post_meta($course_id,'vibe_course_prev_unit_quiz_lock',true);

		if(!empty($nextunit_access)){
			if(function_exists('vibe_validate') && vibe_validate($nextunit_access)){
				$next_unit_access = false;
			}else{
				$next_unit_access = true;
			}
		}
		return $next_unit_access;
	}

	function check_vc($content){
	    if(class_exists('Vc_Base')){
	       global $post;
	       $course_status = vibe_get_option('start_course');
	       $force_include_pages = apply_filters('wplms_vc_force_include_pages',array($course_status));
	       $force_include_post_types = apply_filters('wplms_vc_force_include_custom_post_types',array('course','unit','quiz','question'));

	       if(in_array($post->ID,$force_include_pages) || in_array($post->post_type,$force_include_post_types)){

	            if(strpos($content,'vc_row')){
	                if(class_exists('WPBMap')){
	                    WPBMap::addAllMappedShortcodes(); 
	                    ob_start();

	                    echo "<link rel='stylesheet' id='swiftype-css' href='".vc_asset_url( 'css/js_composer.min.css' )."' type='text/css' media='all'/>";

		                echo "<script type='text/javascript' src='".vc_asset_url( 'js/dist/js_composer_front.min.js' )."'></script>";
		                echo "<script type='text/javascript' src='".vc_asset_url( 'lib/waypoints/waypoints.min.js' )."'></script>";
	                    $content .= ob_get_clean();    
	                }
	            }
	        }
	    }

   return $content;
   }

    function course_lesson_details($title,$unit_id,$course_id =null){ 
    	if(empty($course_id)){
    		global $post;
    		if($post->post_type == 'course'){
    			$course_id = get_the_ID();
    		}else{
    			return $title;
    		}
    	}
    	if(empty($this->check_course_lesson)){
    		$this->check_course_lesson = get_post_meta($course_id,'vibe_course_unit_content',true);
    		if(vibe_validate($this->check_course_lesson)){
    			add_action('wp_footer',array($this,'print_css_adjustments'));
    		}
    	}
    	
    	if(vibe_validate($this->check_course_lesson)){
    		$title .= ' <a class="curriculum_unit_popup link" data-id="'.$unit_id.'" data-course="'.$course_id.'">'._x('Details','Unit details link anchor for full unit content','vibe').'</a>';
    	}
    	return $title;
    }
    function print_css_adjustments(){ ?>
		<style>.course_curriculum .course_lesson>td{padding-bottom:24px;}.curriculum_unit_popup{position:absolute;}</style>
		<?php
	}

	function course_filter(){
		

		global $bp;
		$args=array('post_type' => BP_COURSE_CPT);
		if(isset($_POST['filter'])){
			$filter = $_POST['filter'];
			switch($filter){
				case 'popular':
					$args['orderby'] = 'meta_value_num';
					$args['meta_key'] = 'vibe_students';
				break;
				case 'newest':
					$args['orderby'] = 'date';
				break;
				case 'rated':
					$args['orderby'] = 'meta_value_num';
					$args['meta_key'] = 'average_rating';
				break;
				case 'alphabetical':
					$args['orderby'] = 'title';
					$args['order'] = 'ASC';
				break;
				case 'start_date':
					$args['orderby'] = 'meta_value';
					$args['meta_key'] = 'vibe_start_date';
					$args['meta_type'] = 'DATE';
					$args['order'] = 'ASC';
					if(empty($order['meta_query'])){
						$args['meta_query']=array(array(
						'key' => 'vibe_start_date',
						'value' => current_time('mysql'),
						'compare'=>'>='  
						));
					}
				break;
				case 'pursuing':
					if(is_user_logged_in() && function_exists('bp_course_get_user_courses')){
						$user_id = get_current_user_id();
						$courses = bp_course_get_user_courses($user_id,'start_course');
						$args['post__in'] = $courses;
					}
				break;
				case 'finished':
					if(is_user_logged_in()){
						$user_id = get_current_user_id();
						$courses = bp_course_get_user_courses($user_id,'course_evaluated');
						$args['post__in'] = $courses;
					}
				break;
				case 'active':
					if(is_user_logged_in()){
						$user_id = get_current_user_id();
						$courses = bp_course_get_user_courses($user_id,'active');
						$args['post__in'] = $courses;
					}
				break;
				case 'expired':
					if(is_user_logged_in()){
						$user_id = get_current_user_id();
						$courses = bp_course_get_user_courses($user_id,'expired');
						$args['post__in'] = $courses;
					}
				break;
				case 'draft':
					$args['post_status'] = 'draft';
				break;
				case 'pending':
					$args['post_status'] = 'pending';
				break;
				case 'published':
					$args['post_status'] = 'publish';
				break;
				default:
					$args['orderby'] = apply_filters('wplms_custom_order_in_course_filter','');
				break;
			}
		}

		if(isset($_POST['search_terms']) && $_POST['search_terms'])
			$args['search_terms'] = $_POST['search_terms'];

		if(isset($_POST['page']))
			$args['paged'] = $_POST['page'];

		if(isset($_POST['scope']) && $_POST['scope'] == 'personal'){
			$uid=get_current_user_id();
			if(empty($args['meta_query'])){
				$args['meta_query'] = array(
					'relation'=>'AND',
					array(
						'key' => $uid,
						'compare' => 'EXISTS'
					)
				);
			}else{
				$args['meta_query'][] = array(
					'key' => $uid,
					'compare' => 'EXISTS'
				);
			}
			
		}

		if(isset($_POST['scope']) && $_POST['scope'] == 'instructor'){
			$uid=get_current_user_id();
			$args['instructor'] = $uid;
		}

		if(isset($_POST['extras'])){

			$extras = json_decode(stripslashes($_POST['extras']));
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
						case 'offline':
							$offline=$extra->value;
						break;
						case 'instructor':
							$instructors[]=$extra->value;
						break;
						case 'level':
							$course_levels[]=$extra->value;
						break;
						case 'location':
							$course_location[]=$extra->value;
						break;
						case 'start_date':
							$start_date = $extra->value;;
						break;
						case 'end_date':
							$end_date = $extra->value;;
						break;
					}
				}
			}
			
			$args['tax_query']=array();
			if(count($course_categories)){
				$args['tax_query']['relation'] = 'AND';
				$args['tax_query'][]=array(
									'taxonomy' => 'course-cat',
									'terms'    => $course_categories,
									'field'    => 'slug',
								);
			}
			if(isset($instructors) && count($instructors)){
				$args['author__in']=$instructors;
			}
			if($type){
				switch($type){
					case 'free':
					$args['meta_query']['relation'] = 'AND';
					$args['meta_query'][]=array(
						'key' => 'vibe_course_free',
						'value' => 'S',
						'compare'=>'='
					);
					break;
					case 'paid':
					$args['meta_query']['relation'] = 'AND';
					$args['meta_query'][]=array(
						'key' => 'vibe_course_free',
						'value' => 'H',
						'compare'=>'='
					);
					break;
				}
			}

			if(isset($offline) && $offline){
				switch($offline){
					case 'S':
					$args['meta_query']['relation'] = 'AND';
					$args['meta_query'][]=array(
						'key' => 'vibe_course_offline',
						'value' => 'S',
						'compare'=>'='
					);
					break;
					case 'H':
					$args['meta_query']['relation'] = 'AND';
					$args['meta_query'][]=array(
						'key' => 'vibe_course_offline',
						'value' => 'S',
						'compare'=>'!='
					);
					break;
				}
			}
			if(!empty($start_date)){
				$args['meta_query']['relation'] = 'AND';
				$args['meta_query'][]=array(
					'key' => 'vibe_start_date',
					'value' => $start_date,
					'compare'=>'>='  
				);
			}
			if(!empty($end_date)){
				$args['meta_query']['relation'] = 'AND';
				$args['meta_query'][]=array(
					'key' => 'vibe_start_date',
					'value' => $end_date,
					'compare'=>'<='
				);
			}
			if(count($course_levels)){
				$args['tax_query']['relation'] = 'AND';
				$args['tax_query'][]=array(
										'taxonomy' => 'level',
										'field'    => 'slug',
										'terms'    => $course_levels,
									);
			}
			if(count($course_location)){
				$args['tax_query']['relation'] = 'AND';
				$args['tax_query'][]=array(
										'taxonomy' => 'location',
										'field'    => 'slug',
										'terms'    => $course_location,
									);
			}

		}

	$loop_number=vibe_get_option('loop_number');
	isset($loop_number)?$loop_number:$loop_number=5;

	$args['per_page'] = $loop_number;
	if(in_array($filter,array('pursuing','finished','expired','active')) && empty($args['post__in'])){
		echo '<div class="message">'._x('No courses found !','No courses in current filter','vibe').'</div>';
		die();
	}
	?>

	<?php do_action( 'bp_before_course_loop' ); ?>

	<?php 
	if ( bp_course_has_items( $args ) ) : ?>

		<div id="pag-top" class="pagination ">

			<div class="pag-count" id="course-dir-count-top">

				<?php bp_course_pagination_count(); ?>

			</div>

			<div class="pagination-links" id="course-dir-pag-top">

				<?php bp_course_item_pagination(); ?>

			</div>

		</div>

		<?php do_action( 'bp_before_directory_course_list' );
			$class = '';
			$cookie=urldecode($_POST['cookie']);
			if(stripos($cookie,'bp-course-list=grid')){
				$class='grid';
			}
		?>
		<ul id="course-list" class="item-list <?php echo apply_filters('wplms_course_directory_list',$class); ?>" role="main">

		<?php while ( bp_course_has_items() ) : bp_course_the_item(); ?>

				<?php 
				global $post;
				$cache_duration = vibe_get_option('cache_duration'); if(!isset($cache_duration)) $cache_duration=86400;
				if($cache_duration){
					$course_key= 'course_'.$post->ID;
					if(is_user_logged_in()){
						$user_id = get_current_user_id();
						$user_meta = get_user_meta($user_id,$post->ID,true);
						if(isset($user_meta)){
							$course_key= 'course_'.$user_id.'_'.get_the_ID();
						}
					}
					$result = wp_cache_get($course_key,'course_loop');
				}else{$result=false;}

				if ( false === $result) {
					ob_start();
					bp_course_item_view();
					$result = ob_get_clean();
				}
				if($cache_duration)
				wp_cache_set( $course_key,$result,'course_loop',$cache_duration);
				echo $result;
				?>

		<?php endwhile; ?>

		</ul>

		<?php do_action( 'bp_after_directory_course_list' ); ?>

		<div id="pag-bottom" class="pagination">

			<div class="pag-count" id="course-dir-count-bottom">

				<?php bp_course_pagination_count(); ?>

			</div>

			<div class="pagination-links" id="course-dir-pag-bottom">

				<?php bp_course_item_pagination(); ?>

			</div>

		</div>

	<?php else: ?>

		<div id="message" class="info">
			<p><?php _e( 'No Courses found.', 'vibe' ); ?></p>
		</div>

	<?php endif;  ?>


	<?php do_action( 'bp_after_course_loop' ); ?>
	<?php

		die();
	}

	function filtering_instructor_custom($args=false,$object=false){
	 	//list of users to exclude
	 	if($object!='members')//hide for members only
		 return $args;

		$qs = $args; 
		$args = wp_parse_args( $args ); 

		 if(!isset($args['scope']) || $args['scope'] != 'instructors')
		 	return $qs;
		 
		 $args=array('role' => 'Instructor','fields' => 'ID');
		 $users = new WP_User_Query($args);

		 $included_user = implode(',',$users->results);
		 //$included_user='1,2,3';//comma separated ids of users whom you want to exclude
		 
		 $args=wp_parse_args($qs);
		 
		 //check if we are searching  or we are listing friends?, do not exclude in this case
		 if(!empty($args['user_id'])||!empty($args['search_terms']))
		 return $qs;
		 
		 if(!empty($args['include']))
		 $args['include']=$args['include'].','.$included_user;
		 else
		 $args['include']=$included_user;

		 $qs=build_query($args);

		 return $qs;
	}

	function bp_course_ajax_querystring($string,$object){

		if(function_exists('vibe_get_option'))
			$loop_number=vibe_get_option('loop_number');
		
		if(!isset($loop_number) || !is_numeric($loop_number))
			$loop_number=5;

		$appended = '&per_page='.$loop_number;
		if($object == 'activity'){
			$appended = apply_filters('wplms_activity_loop',$appended);
			if(is_singular('course')){
				$appended .='&primary_id='.get_the_ID();
				?>
				<script>
				jQuery(document).ready(function($){
					$.cookie('bp-activity-course', <?php echo get_the_ID(); ?>, { expires: 1 ,path: '/'});
					<?php
					if(!empty($_REQUEST['student'])){ 
						$appended .='&user_id='.$_REQUEST['student'];
						?>
						$.cookie('bp-activity-student', <?php echo $_REQUEST['student']; ?>, { expires: 1 ,path: '/'});
						<?php
					}
					?>
				});
				</script>
				<?php
			}else if(is_page() || bp_is_group()){
				?>
				<script>
				jQuery(document).ready(function($){
					$.cookie('bp-activity-course', null, { path: '/' });
				});
				</script>
				<?php
			}
		}
		
		if(!empty($_POST) && !empty($_POST['cookie'])){

			preg_match("/[.+]?bp-activity-course...([0-9]*)/", $_POST['cookie'], $matches);
			
			if(!empty($matches) && !empty($matches[1]) && is_numeric($matches[1])){
				$post_type = get_post_field('post_type',$matches[1]);
				if($post_type == 'course'){
					$appended .='&primary_id='.$matches[1];

					preg_match("/[.+]?bp-activity-student...([0-9]*)/", $_POST['cookie'], $student_matches);
					if(!empty($student_matches) && !empty($student_matches[1]) && is_numeric($student_matches[1])){
						global $wpdb;
					    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE ID = %d", $student_matches[1]));
					    if($count){ 
					    	$appended .='&user_id='.$student_matches[1];
					    }
					}
				}
			}
		}

		$string .=$appended;

		if($object != BP_COURSE_SLUG)
			return $string;

		global $bp; 
		$course_activity = '';
		if(is_singular('course')){
			global $post;
			$course_activity .='&primary_id='.$post->ID;
			if(isset($_GET['student']) && $_GET['student'] && is_numeric($_GET['student']))
				$course_activity .= '&user_id='.$_GET['student'];

			$string .=$course_activity;
		}
		
		if(!empty($_COOKIE)){
			if(!empty($_COOKIE["bp-course-filter"]))
				$course_filters = $_COOKIE["bp-course-filter"];
			if(!empty($_COOKIE["bp-course-extras"]))
				$course_extras=$_COOKIE["bp-course-extras"];
			if(!empty($_COOKIE["bp-course-scope"]))
				$course_scope=$_COOKIE["bp-course-scope"];

			if(isset($course_filters)){
				$string.='&filters='.$course_filters;
			}

			if(isset($course_extras)){
				$string.='&extras='.$course_extras;
			}
			if(isset($course_scope)){
				$string.='&scope='.$course_scope;
			}
		}
		return $string;
	}

	function wplms_incourse_quiz_stop_notes($class,$id){
		if(bp_course_get_post_type($id) == 'quiz'){
			$class ='in_quiz stop_notes';
		}
		return $class;
	}

	function wplms_expired_course_product_id($pid,$course_id,$status){
		if($status == -1){ // Expired course
			$free = get_post_meta($course_id,'vibe_course_free',true);
			if(vibe_validate($free)){
				$pid = get_permalink($course_id).'?renew';
			}
		}
		return $pid;
	}

	function wplms_renew_free_course(){

		global $post; 
		if(!is_user_logged_in())
			return;

		$course_id = get_the_ID();
		$user_id = get_current_user_id();
		$expiry = get_user_meta($user_id,$course_id,true);
		
		if($expiry > time())
			return;

		$free = get_post_meta($course_id,'vibe_course_free',true);
		if(vibe_validate($free) && isset($_REQUEST['renew'])){
			$course_duration = get_post_meta($course_id,'vibe_duration',true);
			$course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400,$course_id);
			$new_expiry = time() + $course_duration*$course_duration_parameter;
			update_user_meta($user_id,$course_id,$new_expiry);
			
		    do_action('wplms_renew_course',$course_id,$user_id);
		}
	}

//  bp_course_get_item_pagination

	function course_duration_filter($duration,$course_id = NULL){
    	if(empty($course_id)){
    		global $post;
    		if(is_object($post))
    			$course_id = $post->ID;
    	}
    	
		$parameter = get_post_meta($course_id,'vibe_course_duration_parameter',true);
		if(!empty($parameter) && is_numeric($parameter))
			return $parameter;
	
    	return $duration;
    }

    function drip_duration_filter($duration,$course_id = NULL){
    	
    	if(empty($course_id)){
    		global $post;
    		if(is_object($post))
    			$course_id = $post->ID;
    	}

		$parameter = get_post_meta($course_id,'vibe_drip_duration_parameter',true);
		if(!empty($parameter) && is_numeric($parameter))
			return $parameter;
    	
    	return $duration;
    }

    function unit_duration_filter($duration,$unit_id = NULL){
    	
    	if(empty($unit_id)){
    		global $post;
    		if(is_object($post))
    			$unit_id = $post->ID;
    	}
   
		$parameter = get_post_meta($unit_id,'vibe_unit_duration_parameter',true);
		if(!empty($parameter) && is_numeric($parameter))
			return $parameter;
    	
    	return $duration;
    }

    function quiz_duration_filter($duration,$quiz_id = NULL){
    	
    	if(empty($quiz_id)){
    		global $post;
    		if(is_object($post))
    			$quiz_id = $post->ID;
    	}

		$parameter = get_post_meta($quiz_id,'vibe_quiz_duration_parameter',true);
		if(!empty($parameter) && is_numeric($parameter))
			return $parameter;
    	
    	return $duration;
    }

    function product_duration_filter($duration,$product_id = NULL){
    	
    	if(empty($product_id)){
    		global $post;
    		if(is_object($post))
    			$product_id = $post->ID;
    	}

		$parameter = get_post_meta($product_id,'vibe_product_duration_parameter',true);
		if(!empty($parameter) && is_numeric($parameter))
			return $parameter;
    	
    	return $duration;
    }

    function assignment_duration_filter($duration,$assignment_id = NULL){
    	if(empty($assignment_id)){
    		global $post;
    		if(is_object($post))
    			$assignment_id = $post->ID;
    	}
    	
    	$parameter = get_post_meta($assignment_id,'vibe_assignment_duration_parameter',true);
    	if(!empty($parameter) && is_numeric($parameter))
    		return $parameter;
    	
    	return $duration;
    }

    /* ==== Apply for Course === */
    function apply_course_button_label($label,$course_id){
    	if(empty($this->course_button) && empty($this->course_button[$course_id])){
    		$check = get_post_meta($course_id,'vibe_course_apply',true);
    		$this->course_button[$course_id] = $check;
    	}
    	if(vibe_validate($this->course_button[$course_id])){
    		
    		$label = _x('Apply for Course','Apply for Course label for course','vibe');
    		$user_id = get_current_user_id();
    		$check = get_user_meta($user_id,'apply_course'.$course_id,true);
    		
    		if( !empty($check) ){
    			$label = _x('Applied for Course','Apply for Course label for course','vibe');
    			add_action('wp_footer',array($this,'remove_apply_for_course_id'));
    		}
    	}
    	return $label;
    }

    function remove_apply_for_course_id(){
    	?>
    	<script>
			jQuery(document).ready(function($){
				$('body').find('#apply_course_button').removeAttr('id');
			});
		</script>
		<?php
    }

    function apply_course_button_link($link,$course_id){

    	if(empty($this->course_button) && empty($this->course_button[$course_id])){
    		$check = get_post_meta($course_id,'vibe_course_apply',true);
    		$this->course_button[$course_id] = $check;
    	}
    	if(vibe_validate($this->course_button[$course_id])){
    		if(!is_user_logged_in()){
    			$link = '?error=login';
    		}else{
    			$link = '#" id="apply_course_button" data-id="'.$course_id.'" data-security="'.wp_create_nonce('security'.$course_id);
    		}
    	}
    	return $link;
    }

    function hide_price($details,$course_id){
    	if(empty($this->course_button) && empty($this->course_button[$course_id])){
    		$check = get_post_meta($course_id,'vibe_course_apply',true);
    		$this->course_button[$course_id] = $check;
    	}
    	if(vibe_validate($this->course_button[$course_id])){
    		unset($details['price']);
    	}
    	return $details;
    }

    function apply_course_submission_tab($tabs,$course_id){
    	if(empty($this->course_button) && empty($this->course_button[$course_id])){
    		$check = get_post_meta($course_id,'vibe_course_apply',true);
    		$this->course_button[$course_id] = $check;
    	}
    	if(vibe_validate($this->course_button[$course_id])){
    		$tabs['applications'] = sprintf(_x('Applications <span>%d</span>','Apply for course applicants in Course - admin - Submissions','vibe'),bp_course_get_course_applicants_count($course_id));
    	}
    	return $tabs;
    }

    function drip_feed_element($element,$course_id){
    	$course_section_drip = get_post_meta($course_id,'vibe_course_section_drip',true);
    	if(function_exists('vibe_validate') && vibe_validate($course_section_drip)){
    		$element = __('Section','vibe');
    	}
    	return $element;
    }

    function evaluate_course_drip($value,$pre_unit_id,$course_id,$unit_id){

    	$course_section_drip = get_post_meta($course_id,'vibe_course_section_drip',true);
    	$course_drip_duration_type = get_post_meta($course_id,'vibe_course_drip_duration_type',true);

    	if(function_exists('vibe_validate') && vibe_validate($course_section_drip)){

			$user_id = get_current_user_id();
			$drip_duration = get_post_meta($course_id,'vibe_course_drip_duration',true);
			$drip_duration_parameter = apply_filters('vibe_drip_duration_parameter',86400,$course_id);
			$total_drip_duration = $drip_duration*$drip_duration_parameter;
			
			$curriculum= bp_course_get_curriculum($course_id); 
			if(is_array($curriculum)){
				$key = array_search($unit_id,$curriculum);
				if(!isset($key) || !$key)
					return $value;
				//GET Previous Two Sections
				$i=$key;
				
				while($i>=0){
					if(!is_numeric($curriculum[$i])){
						if(!isset($k2)){
							$k2 = $i;
						}else if(!isset($k1)){
							$k1 = $i;
						}
					}
					$i--;
				}
				
				//First section incomplete
				if(!isset($k2) || !isset($k1) || !$k2 || $k1 == $k2 || $k2<$k1)
					return 0;

				//Get first unit in previous section
				for($i=$k1;$i<=$k2;$i++){
					if(is_numeric($curriculum[$i]) && bp_course_get_post_type($curriculum[$i]) == 'unit') 
						break;
				}

				if($i == $k2){
					return 0; // section drip feed disabled if a section has all quizzes
				}

				$start_section_timestamp=bp_course_get_drip_access_time($curriculum[$i],$user_id,$course_id);
				if(empty($start_section_timestamp)){
					$start_section_timestamp = bp_course_get_user_unit_completion_time($user_id,$curriculum[$i],$course_id); // If access time not present check the unit completion time.
					if(empty($start_section_timestamp)){ // If completion time not present set the access time as current timestamp.
						$start_section_timestamp = time();
						bp_course_update_unit_user_access_time($curriculum[$i],$user_id,$start_section_timestamp,$course_id);
					}
				}

				if(vibe_validate($course_drip_duration_type)){
					$total_drip_duration = 0;
					for($i=$k1;$i<=$k2;$i++){
						if(is_numeric($curriculum[$i]) && bp_course_get_post_type($curriculum[$i]) == 'unit'){
							$unit_duration = get_post_meta($curriculum[$i],'vibe_duration',true);
							$unit_duration_parameter = apply_filters('vibe_unit_duration_parameter',60,$curriculum[$i]);
							$total_drip_duration += $unit_duration*$unit_duration_parameter; // Sum of all unit durations in a section
						}
					}
				}
				
				$value = $start_section_timestamp + $total_drip_duration;	
			}
		}
		return $value;
	}

	function total_drip_duration($value,$course_id,$unit_id,$pre_unit_id){
		$course_drip_duration_type = get_post_meta($course_id,'vibe_course_drip_duration_type',true);
		
		if(vibe_validate($course_drip_duration_type)){ //unit duration
			$unit_duration = get_post_meta($pre_unit_id,'vibe_duration',true);
			$unit_duration_parameter = apply_filters('vibe_unit_duration_parameter',60,$pre_unit_id);
			$value = $unit_duration*$unit_duration_parameter;
		}
		return $value;
    }
}

bp_course_filters::init();
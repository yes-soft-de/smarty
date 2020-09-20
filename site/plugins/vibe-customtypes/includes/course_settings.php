<?php

 if ( ! defined( 'ABSPATH' ) ) exit;

 class WPLMS_Course_Details{

 	protected $option = 'course_settings';
 	public $course_details_labels= array();
	public static $instance;
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Course_Details();
        return self::$instance;
    }

    public function __construct(){
    	/*
    	ADD NEW TAB IN LMS - SETTINGS
    	*/
    	add_filter('wplms_lms_commission_tabs',array($this,'add_course_details'));
    	// Tab Handle function
    	add_filter('lms_general_settings',array($this,'handle_course_details'),99);
    	add_filter('wplms_course_details_widget',array($this,'apply_course_details'),99);
    }

    function apply_course_details($settings){
    	$option = $this->get();
    	$option = $option['course_details'];
    	$course_details_labels = $this->course_details_labels;

    	if(!empty($option)){
    		$new_settings = array();
    		$course_details = $option['value'];
    		$detail_privacy = $option['privacy'];
			$custom_text = $option['text'];

    		foreach($course_details as $key => $value){

    			if($this->check_privacy($detail_privacy[$key])){
    				if(!isset($settings[$value])){
	    				if(!empty($course_details_labels[$value]['callback'])){
	    					if(method_exists($this,$course_details_labels[$value]['callback'])){
	    						$settings[$value] = $this->get_course_unit_number();
	    						$str = '';
	    						$str = $course_details_labels[$value]['callback'];
	    						$settings[$value] = $this->$str();
	    					}else if(function_exists($course_details_labels[$value]['callback'])){
	    						$settings[$value] = $course_details_labels[$value]['callback']();	
	    					}   					
	    				}
	    			}
	 
	    			$new_settings[$value]=$settings[$value];
	    			if(isset($custom_text[$key]) && $custom_text[$key] != ""){
						$new_settings[$value] = stripcslashes($custom_text[$key]);
					}		
	    			
	    		}
    		}

    		$settings = $new_settings;
    	}
    	return apply_filters('course_details_widget_array',$settings);
    }

    function check_privacy($privacy){

    	$privacy_options = $this->get_privacy_options();

    	$return = false;
    	if(function_exists('bp_course_version')){
    		$version = bp_course_version();	
    	}else{
    		return true;
    	}
    	
    	switch($privacy){

    		case 'all':
    			return true;
    		break;
    		case 'users':
    			if(is_user_logged_in())
    				return true;
    		break;
    		case 'course_users':
    			if(!is_user_logged_in())
    				return false;

    			if( version_compare($version, "2.5.2") >= 0  && wplms_user_course_check($user_id))
    				return true;
    		break;
    		case 'pursuing_users':
    			if(!is_user_logged_in())
    				return false;

    			if(version_compare($version, "2.5.2") >= 0  && wplms_user_course_active_check())
    				return true;
    		break;
    		case 'instructors':
    			if(current_user_can('edit_posts'))
    				return true;
    		break;
    		default:
    			$return = apply_filters('wplms_course_details_check_privacy',false,$privacy);
    		break;
    	}

    	return $return;
    }

    function add_course_details($settings){
    	if(!isset($_GET['tab']) || $_GET['tab'] == 'general'){
    		$settings['course_settings'] = _x('Course Settings','','vibe-customtypes');
    	}
    	return $settings;
    }

    function handle_course_details($settings){
    	
    	if(!isset($_GET['sub']) || $_GET['sub'] != 'course_settings')
    		return $settings;
    	
    	$settings = array();
    	$course_settings = apply_filters('course_settings_settings',array(
    			array(
					'label'=>__('Course Details','vibe-customtypes' ),
					'type'=> 'heading',
				),
				array(
					'label'=>__('Manage Course details','vibe-customtypes' ),
					'type'=> 'course_details',
				),
    		));

    	$this->handle_save();
    	$this->generate_form($course_settings);

    	return $settings;
    }

    function handle_save(){

		if(!isset($_POST['save_course_settings']) || !wp_verify_nonce($_POST['_wpnonce'],'vibe_course_settings')){return;}
    	if(isset($_POST['course_details'])){
    		$option = $this->get();
			$option['course_details']=$_POST['course_details'];
    		$this->put($option);
    		echo '<div id="message" class="updated is-dismissable timeout"><p>'._x('Settings Saved','save settings message','vibe-customtypes').'</p></div>';
    	}else{
    		$this->put(array());
    	}
    }

    function generate_form($settings){

    	$option = $this->get();
    	$course_details_labels = $this->course_details_labels;

    	echo '<form method="post">';
		wp_nonce_field('vibe_course_settings','_wpnonce');   
		echo '<table class="form-table">
				<tbody>';	
		foreach($settings as $setting ){
			echo '<tr valign="top" '.(empty($setting['class'])?'':'class="'.$setting['class'].'"').'>';
			switch($setting['type']){
				case 'heading':
					echo '<th scope="row" class="titledesc" colspan="2"><h3>'.$setting['label'].'</h3></th>';
				break;
				case 'course_details':
					echo '<td>';

					if(empty($option)){$option = array();}
					if(empty($option['course_details'])){
						$option['course_details'] = $more_details = array();
						foreach($course_details_labels as $k=>$v){
							if(empty($v['callback'])){
								$option['course_details']['value'][] = $k;
								$option['course_details']['privacy'][] = 'all';
								$option['course_details']['text'][] = '';
							}else{
								$more_details[$k] = $v;
							}
						}
					}else{
						foreach($course_details_labels as $k=>$v){
							if(!in_array($k,$option['course_details']['value'])){
								$more_details[$k] = $v;
							}
						}
					}
					
					$privacy_options = $this->get_privacy_options();
					echo '<input type="submit" name="add_new_course_detail" class="button" value="'._x('Add New Detail','Adds a new course detail in the course details widget','vibe-customtypes').'" />';

					if(isset($option['course_details'])){
						echo '<ul class="course_details_list">';
						$course_details = $option['course_details'];
						$details = $option['course_details']['value'];
						$custom_details = $option['course_details']['text'];
						foreach($details as $k => $detail){
							echo '<li class="detail_list"><span class="dashicons dashicons-menu"></span> &nbsp; <label>'.(isset($course_details_labels[$detail]['label'])? $course_details_labels[$detail]['label'] : $detail ).' &nbsp; [ ';

							echo (isset($course_details['privacy'][$k])?$privacy_options[$course_details['privacy'][$k]]:$privacy_options['all']).' ]</label>
							<input type="hidden" name="course_details[value][]" value="'.$detail.'" />
							<input type="hidden" name="course_details[text][]" value="'.$custom_details[$k].'" />
							<input type="hidden" name="course_details[privacy][]" class="privacy_select" value="'.(isset($course_details['privacy'][$k])?$course_details['privacy'][$k]:'all').'" />
							    <span class="dashicons dashicons-no"></span></li>';
						}
						if(isset($_POST['add_new_course_detail'])  && !empty($_POST['add_new_course_detail']) ){

							echo '<li class="detail_list"><span class="dashicons dashicons-menu"></span> &nbsp; ';
							echo'<ul>';
							echo'<li>';
							echo '<label>Label</label>';
							echo '<select  class="label_select" name="course_details[value][]">';
							echo '<option>Select</option>';
							echo '<option value="custom">Custom</option>';
							foreach($more_details as $key=>$detail){
								echo '<option value="'.$key.'">'.$detail['label'].'</option>';
							}

							echo '</select>';
							echo'</li>';
							echo'<li>';
							echo '<label>Privacy</label>';
							echo '<select class="privacy_select" name="course_details[privacy][]">';
							if(!empty($privacy_options)){
								foreach($privacy_options as $p=>$o){
									echo '<option value="'.$p.'">'.$o.'</option>';
								}
							}
							echo '</select>';
							echo'</li>';
							echo'</ul>';
							echo '<span class="dashicons dashicons-no"></span></li>';
						}
						echo '</ul>';
					}
					echo '</td>';
				break;
			}
			echo '</tr>';
		}
		echo '</tbody>';
		
		echo '</table><style>input.input_custom_icon {display:block !Important;margin-top:10px !Important; margin-left:55px!Important; width:350px;}input.input_custom {display:block !Important;margin-top:10px !Important; margin-left:55px!Important; width:350px;} input.input_custom_text {display:block !Important;margin-top:10px !Important;margin-left:55px!Important; width:350px } select.privacy_select {margin-left:12px!Important; width:350px;} select.label_select {margin-left:22px!Important;width:350px;}.hidden_input_custom_text{display:none;}.hidden_input_custom_icon{display:none;} .hidden_input_custom {display:none;}.detail_list{border:1px solid #eee; padding:8px 15px;background:#fff;max-width:80%;min-width:240px;}.detail_list .dashicons-no{float:right;color:red;} .button.save{background: #E8442F; text-shadow: none; box-shadow: none; border: none;}</style><script>
			jQuery(document).ready(function($){

				$(".course_details_list").sortable({
					"handle":".dashicons-menu",
					 axis: "y"
				});
				$(".dashicons-no").on("click",function(){
					$(this).parent().remove();
					$("input[name=\'save_course_settings\']").addClass("save");
					return false;
				});
			});
			</script>';
			echo '<input class="hidden_input_custom" placeholder="'._x('Custom Label','Adds a new course detail in the course details widget','vibe-customtypes').'" type="hidden" name=""/>';
		echo '<input class="hidden_input_custom_text" placeholder="'._x('Custom Text','Adds a new course detail in the course details widget','vibe-customtypes').'" type="hidden" name=""/>';	
		
			?>
		<script>
				jQuery(document).ready(function($){

					$('select[name="course_details[value][]"]').on("click",function(){
						var $this = $(this);
						var input_value_select = $this.val();
						console.log(input_value_select);
						if(input_value_select == 'custom' && $this.parent().find('input.input_custom').length==0){

							var clone_hidden_input_text = $("input.hidden_input_custom_text").clone();
							clone_hidden_input_text.removeClass('hidden_input_custom_text');
							clone_hidden_input_text.addClass('input_custom_text');
							clone_hidden_input_text.attr('type','text');
							clone_hidden_input_text.attr('name','course_details[text][]');
							$this.after(clone_hidden_input_text);

							var clone_hidden_input = $("input.hidden_input_custom").clone();
							clone_hidden_input.removeClass('hidden_input_custom');
							clone_hidden_input.addClass('input_custom');
							clone_hidden_input.attr('type','text');
							clone_hidden_input.attr('name','course_details[value][]');
							$this.after(clone_hidden_input);

							$this.attr('name','custom_select');
						}
						if(input_value_select != 'custom' && $this.parent().find('input.input_custom').length!=0){
							$this.parent().find('input.input_custom').remove();
							$this.parent().find('input.input_custom_text').remove();
							$this.attr('name','course_details[value][]');
						}
					});
					$('input[name="save_course_settings"]').on("click",function(){	
						$('input.input_custom_text').val( '<li>' + $('input.input_custom_text').val() + '</li>');
					});
				});
		</script><?php
		if(!empty($settings))
			echo '<input type="submit" name="save_course_settings" value="'.__('Save Settings','vibe-customtypes').'" class="button button-primary" /></form>';
    }

    function get(){

    	$this->course_details_labels = apply_filters('wplms_course_details_array',array(
						'price' => array(
							'label'=>_x('Price','label in details array','vibe-customtypes'),
							'callback'=> false,
							),
						'precourse' => array(
							'label'=>_x('Pre-requisite Courses','label in details array','vibe-customtypes'),
							'callback'=> false,
							),
						'starts_in' => array(
							'label'=>_x('Course Starts In','label in details array','vibe-customtypes'),
							'callback'=> 'get_course_starts_in',
							),
						'time' => array(
							'label'=>_x('Course Duration','label in details array','vibe-customtypes'),
							'callback'=> false,
							),
						'location' => array(
							'label'=>_x('* Course Location (if enabled)','label in details array','vibe-customtypes'),
							'callback'=> false,
							),
						'level' => array(
							'label'=>_x('* Course Level (if enabled)','label in details array','vibe-customtypes'),
							'callback'=> false,
							),
						'seats' => array(
							'label'=>_x('Course Seats (if set)','label in details array','vibe-customtypes'),
							'callback'=> false,
							),
						'badge' => array(
							'label'=>_x('Course Badge (if set)','label in details array','vibe-customtypes'),
							'callback'=> false,
							),
						'certificate' => array(
							'label'=>_x('Course Certificate (if set)','label in details array','vibe-customtypes'),
							'callback'=> false,
							),
						'unit_duration' => array(
							'label'=>_x('Total Unit Duration','label in details array','vibe-customtypes'),
							'callback'=> 'get_course_unit_durations',
							),
						'number_units' => array(
							'label'=> _x('Number of Units','label in details array','vibe-customtypes'),
							'callback'=> 'get_course_unit_number',
							),
						'number_sections' => array(
							'label'=> _x('Number of Sections','label in details array','vibe-customtypes'),
							'callback'=> 'get_course_section_number',
							),
						'number_quizes' => array(
							'label'=> _x('Number of Quizes','label in details array','vibe-customtypes'),
							'callback'=> 'get_course_quiz_number',
							),
						'number_students' => array(
							'label'=> _x('Number of Students','label in details array','vibe-customtypes'),
							'callback'=> 'get_course_student_number',
							),
						'average_rating' => array(
							'label'=> _x('Average Rating','label in details array','vibe-customtypes'),
							'callback'=> 'get_course_average_rating',
							)

					));

		if(function_exists('wplms_course_get_course_assignments')){
			$this->course_details_labels['number_assignments']= array(
							'label'=> _x('Number Of Assignments','label in details array','vibe-customtypes'),
							'callback'=> 'get_course_assignments_number',
							);
		}

		if(class_exists('WPLMS_Gift_Course_Class')){
			$this->course_details_labels['gift_course']= array(
							'label'=>_x('Gift this course','label in details array','vibe-customtypes'),
							'callback'=> false,
						);
		}

		if(class_exists('Wplms_Wishlist_Component')){
			$this->course_details_labels['wishlist']= array(
							'label'=>_x('Wishlist','label in details array','vibe-customtypes'),
							'callback'=> false,
						);
		}

		$this->course_details_labels = apply_filters('wplms_course_details_array',$this->course_details_labels);

    	$status = get_option($this->option);

    	return $status;
    }

    function get_privacy_options(){
    	$privacy_options = apply_filters('wplms_course_details_privacy_options',array(
						'all'			=>_x('All Users (logged in/logged out)','privacy option for course details','vibe-customtypes'),
						'users'			=>_x('Logged in Users (Students/Instructors)','privacy option for course details','vibe-customtypes'),
						'course_users'	=>_x(' All Course users','privacy option for course details','
							vibe-customtypes'),
						'pursuing_users'	=>_x(' All Course pursuing users (excludes finished/not started)','privacy option for course details','
							vibe-customtypes'),
						'instructors'	=>_x(' All instructors + Admins','privacy option for course details','vibe-customtypes'),
						));

    	return $privacy_options;
    }

    function put($option){
    	$status = update_option($this->option,$option);
    	return $status;
    }

 	// Number Of units avaiable and completed function starts
    public static function get_course_unit_number(){
    	$course_id = get_the_ID();
    	$course_curriculum = bp_course_get_curriculum($course_id);
    	$user_completed = $total_available = 0;
		if( !empty($course_curriculum) ){
			foreach($course_curriculum as $key => $item){
				if(is_numeric($item)){
					$post_type = get_post_type($item);
					if( $post_type == 'unit'){
						if( is_user_logged_in() ){
							$user_id = get_current_user_id();
							$check = bp_course_check_unit_complete($item,$user_id,$course_id);
							if($check){$user_completed++;}
						}
						
						$total_available++;
					}
				}
			}

			$return = '';
			if($total_available){
				$return = '<li>'._x("Number of Units","Course Detail Sidebar Number of Units","vibe-customtypes").'<i class="course_detail_span">'.$total_available.'</i></li>';
				if( is_user_logged_in() ){
					$return .= '<li>'._x("Units Completed","Course Detail Sidebar Number of Units Completed","vibe-customtypes").'<i class="course_detail_span">'.$user_completed.'</i></li>';
				}
			}

			return $return;
		}
	}

 	// Number Of Quizes avaiable and completed function starts
    public static function get_course_quiz_number(){
    	$course_id = get_the_ID();
    	$course_curriculum = bp_course_get_curriculum($course_id);
    	$user_completed = $total_available = 0;
		if( !empty($course_curriculum) ){
			foreach($course_curriculum as $key => $item){		
				if(is_numeric($item)){
					$post_type = get_post_type($item);
					if($post_type == 'quiz'){
						if( is_user_logged_in() ){
							$user_id = get_current_user_id();
							$check = bp_course_check_quiz_complete($item,$user_id,$course_id);
							if($check){$user_completed++;}
						}
						$total_available++;
					}
				}
			}
			$return = '<li>'._x("Number of Quizzes","Course Detail Sidebar Number of Quizzes","vibe-customtypes").'<i class="course_detail_span">'.$total_available.'</i></li>';
			if( is_user_logged_in() ){
				$return .= '<li>'._x("Quizzes Completed","Course Detail Sidebar Number of Quizzes Completed","vibe-customtypes").'<i class="course_detail_span">'.$user_completed.'</i></li>';
			}

			return $return;
		}
	}

	// Total Unit Duration Count function starts
    public static function get_course_unit_durations(){
		$course_id = get_the_ID();
    	$course_curriculum = bp_course_get_curriculum($course_id);
		if(!empty($course_curriculum)){
			$duration = 0;
			foreach($course_curriculum as $key => $item){		
				if(is_numeric($item)){ 
					$post_type = get_post_type($item);
					if( $post_type == 'unit' && function_exists('bp_course_get_unit_duration')){
						$duration += bp_course_get_unit_duration($item);
					}else if($post_type == 'quiz' && function_exists('bp_course_get_quiz_duration')){
						$duration += bp_course_get_quiz_duration($item);
					}
				}
			}

			if(function_exists('tofriendlytime')){
				$duration = apply_filters('wplms_cs_get_course_unit_durations',tofriendlytime($duration),$duration);
			}
			
			return '<li><strong class="tip" data-title="'._x("Total Unit + Quiz duration in this course","Course Detail Sidebar Unit Duration","vibe-customtypes").'">'.$duration.'</strong><i class="icon-clock-2"></i></li>';
		}
	}

	// Total number of sections function starts
    public static function get_course_section_number(){
    	$course_id = get_the_ID();
    	$number_sections = 0;
    	$course_curriculum = bp_course_get_curriculum($course_id);
        if(isset($course_curriculum) && is_array($course_curriculum)){
        	foreach($course_curriculum as $key => $curriculum){
            	if(!is_numeric($curriculum)){
              	  ++$number_sections;
            	}
          	}
        }
    	return '<li>'._x("Number of Sections","Course Detail Sidebar Number of Sections","vibe-customtypes").'<i class="course_detail_span">'.$number_sections.'</i></li>';   
    }

    // Total number of students function starts
    public static function get_course_student_number(){
    	$course_id = get_the_ID();
    	$students = get_post_meta($course_id,'vibe_students',true);
    	return '<li>'._x("Number of students","Course Detail Sidebar Number of students","vibe-customtypes").'<i class="course_detail_span">'.$students.'</i></li>';
    }

    // average rating function starts
    public static function get_course_average_rating(){
    	$course_id = get_the_ID();
	  	$rating = get_post_meta($course_id,'average_rating',true);
	  	if( empty($rating) ){
	  		return none;
	  	} 
	    else{
			return '<li>'._x("Average Rating","Course Detail Sidebar Average Rating","vibe-customtypes").'<i class="course_detail_span">'.$rating.'</i></li>';
		}
	}

	// Number Of assignments function starts
    public static function get_course_assignments_number(){
	    $course_id = get_the_ID();
	  	$no_of_assignments = 0;
		if(!function_exists('wplms_course_get_course_assignments'))
			return;

		$assignments = wplms_course_get_course_assignments($course_id);
        $no_of_assignments = count($assignments);  
        if(!empty($no_of_assignments)){
        	return '<li>'._x("Number Of Assignments","Course Detail Sidebar Average Rating","vibe-customtypes").'<i class="course_detail_span">'.$no_of_assignments.'</i></li>';	
        }
        return;
	}

	// Course Starts in funtion starts
	function get_course_starts_in(){
	    $course_id = get_the_ID();
	    if(function_exists('bp_course_get_start_date')){ 
	      $start_date = bp_course_get_start_date($course_id);
	    }else{
	      $start_date = get_post_meta($course_id,'vibe_start_date',true);  
	    }
	    $timestamp = strtotime( $start_date );
	    if(isset($start_date) &&  $timestamp  > time()){ 
	        $time_remaining = human_time_diff(time(),$timestamp);
	        return '<li>'._x("Starts In","Course Detail Sidebar Starts In","vibe-customtypes").'<i class="course_detail_span">'.$time_remaining.'</i></li>';
	    }
	    return ;
	}
}

WPLMS_Course_Details::init();

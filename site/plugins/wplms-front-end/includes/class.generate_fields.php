<?php

class WPLMS_Front_End_Fields {
	var $course_id;

	var $course_settings;
	var $course_product;

	var $prefix = 'vibe_';
	var $status;

	public static $instance;
    
    public static function init(){
    	wp_deregister_script( 'select2');
    	wp_deregister_style( 'select2');
        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Front_End_Fields;
        return self::$instance;
    }

	private function __construct(){
		$this->get();
		add_action('wplms_front_end_pricing_content',array($this,'pmpro_levels'),10,1);
		add_filter('wplms_course_creation_tabs',array($this,'active_components_check'),100);
		add_filter('wplms_course_creation_tabs',array($this,'course_pricing_models'));
	}

	function get(){
		$this->course_settings = vibe_meta_box_arrays('course');
		$this->course_product = vibe_meta_box_arrays('course_product');
		wplms_front_end_loadscripts();
		wp_enqueue_script( 'timepicker_box', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/js/jquery.timePicker.min.js', array( 'jquery' ) );
		if(isset($this->course_id) && is_numeric($this->course_id)){
            $this->status = get_post_status($this->course_id);
        }
	}



	function pmpro_levels($course_id = null){
		if ( in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) && function_exists('pmpro_getAllLevels')) { 
          $levels=pmpro_getAllLevels(); // Get all the PMPro Levels
          ?>
          <li class="course_membership"><strong><?php _e('Set Course Memberships','wplms-front-end' ); ?><span>
                  <select id="vibe_pmpro_membership" class="chosen" multiple>
                      <?php
                      if(isset($levels) && is_array($levels)){
                          foreach($levels as $level){
                              if(!is_Array($course_pricing['vibe_pmpro_membership']))
                                  $course_pricing['vibe_pmpro_membership'] = array();
                              
                          if(is_object($level))
                              echo '<option value="'.$level->id.'" '.(in_array($level->id,$course_pricing['vibe_pmpro_membership'])?'selected':'').'>'.$level->name.'</option>';
                          }
                      }
                      ?>
                  </select>
              </span>
              </strong>
          </li>
      <?php    
      }
	}
	function tabs(){
		$course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400,$this->course_id);
		$drip_duration_parameter = apply_filters('vibe_drip_duration_parameter',86400,$this->course_id);
		
		return apply_filters('wplms_course_creation_tabs',array(
			'create_course'=>array(
					'icon'=> 'book-open',
					'title'=> (isset($this->course_id)?__('EDIT COURSE','wplms-front-end' ):__('CREATE COURSE','wplms-front-end' )),
					'subtitle'=>  __('Start building a course','wplms-front-end' ),
					'fields'=> array(
						array(
							'label'=> __('Course title','wplms-front-end' ),
							'type'=> 'title',
							'id' => 'post_title',
							'from'=>'post',
							'value_type'=>'single',
							'style'=>'col-md-12',
							'default'=> __('ENTER A COURSE TITLE','wplms-front-end' ),
							'help'=> __('This is the title of the course which is displayed on top of every course','wplms-front-end' )
							),
						array(
							'label'=> __('Course Category','wplms-front-end' ),
							'type'=> 'taxonomy',
							'taxonomy'=> 'course-cat',
							'from'=>'taxonomy',
							'value_type'=>'single',
							'style'=>'col-md-12',
							'id' => 'course-cat',
							'default'=> __('Select a Course Category','wplms-front-end' ),
							),
						array(
							'label'=> __('Course Image','wplms-front-end' ),
							'type'=> 'featured_image',
							'level'=>'thumbnail',
							'value_type'=>'single',
							'upload_title'=>__('Upload a Course Image','wplms-front-end' ),
							'upload_button'=>__('Set as Course Image','wplms-front-end' ),
							'style'=>'col-md-3',
							'from'=>'post',
							'id' => 'post_thumbnail',
							'default'=> VIBE_URL.'/assets/images/add_image.png',
							),
						array(
							'label'=> __('Course Video','wplms-front-end' ),
							'type'=> 'featured_video',
							'level'=>'video',
							'value_type'=>'single',
							'upload_title'=>__('Upload a Video','wplms-front-end' ),
							'desc'=>__('Select or Upload a video','wplms-front-end' ),
							'upload_button'=>__('Set as Course Video','wplms-front-end' ),
							'style'=>'',
							'from'=>'post',
							'id' => 'post_video',
							'default'=> VIBE_URL.'/assets/images/add_image.png',
							),
						array(
							'label'=> __('Course Short Description','wplms-front-end' ),
							'type'=> 'textarea',
							'style'=>'col-md-9',
							'value_type'=>'single',
							'id' => 'post_excerpt',
							'from'=>'post',
							'extras' => '<a class="link toggle_vibe_post_content">'.__('Add full description','wplms-front-end').'</a>',
							'default'=> __('Enter a short description of the course.','wplms-front-end' ),
							),
						array(
							'label'=> __('Full Description','wplms-front-end' ),
							'type'=> 'editor',
							'style'=>'col-md-12',
							'value_type'=>'single',
							'id' => 'post_content',
							'from'=>'post',
							'noscript'=>true,
							'default'=> __('Enter full description of the course.','wplms-front-end' ),
							),
						array(
							'label'=>(isset($this->course_id)?__('SAVE COURSE','wplms-front-end' ):__('CREATE COURSE','wplms-front-end' )),
							'id'=>(isset($this->course_id)?'save_course_button':'create_course_button'),
							'type'=>'button'
							),
						),
				),
			'course_settings'=>array(
					'icon'=> 'settings-1',
					'title'=> __('SETTINGS','wplms-front-end' ),
					'subtitle'=>  __('Course settings','wplms-front-end' ),
					'fields'=>array(
						array(
							'label'=> __('Course duration','wplms-front-end' ),
							'text'=>__('Maximum Course Duration','wplms-front-end' ),
							'type'=> 'number',
							'style'=>'',
							'id' => 'vibe_duration',
							'from'=> 'meta',
							'extra' => '<span data-connect="vibe_course_duration_parameter">'.calculate_duration_time($course_duration_parameter).'</span>',
							'default'=>9999,
							'desc'=> sprintf(__('Enter the maximum duration for the course in %s. This is the maximum duration within which the student should complete the course. Use 9999 for unlimited access to course.','wplms-front-end' ),calculate_duration_time($course_duration_parameter)),
							),
						array(
							'label'=> __('Course duration parameter','wplms-front-end' ),
							'text'=>__('Set Course Duration parameter','wplms-front-end' ),
							'type'=> 'duration',
							'style'=>'',
							'id' => 'vibe_course_duration_parameter',
							'from'=> 'meta',
							'default'=> $course_duration_parameter,
							'desc'=> __('Set course duration parameter for this course.','wplms-front-end' ),
							),
						array(
							'label'=> __('Prerequisite Course','wplms-front-end' ),
							'text'=>__('Set a prerequisite Course','wplms-front-end' ),
							'type'=> 'selectmulticpt',
							'cpt'=> 'course',
							'style'=>'',
							'id' => 'vibe_pre_course',
							'placeholder'=> __('Enter first 4 letters of course for search','wplms-front-end' ),
							'from'=> 'meta',
							'desc'=> __('Pre-required course which the user needs to complete before subscribing to this course.','wplms-front-end' ),
							),
						array(
							'label'=> __('Previous Unit Completion Lock','wplms-front-end' ),
							'text'=>__('Previous Units/Quiz must be Complete before next unit/quiz access','wplms-front-end' ),
							'type'=> 'switch',
							'options'  => array('H'=>__('NO','wplms-front-end' ),'S'=>__('YES','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_prev_unit_quiz_lock',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Force previous unit access lock.','wplms-front-end' )
							),
						array(
							'label'=> __('Course Type','wplms-front-end' ),
							'text'=>__('Set Course Type','wplms-front-end' ),
							'type'=> 'switch',
							'options'  => array('H'=>__('ONLINE','wplms-front-end' ),'S'=>__('OFFLINE','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_offline',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Offline Courses can be filtered in the Course directory.','wplms-front-end' )
							),
						array(
							'label'=> __('Unit Content (Offline Courses)','wplms-front-end' ),
							'text'=>__('Full units in Curriculum','wplms-front-end' ),
							'type'=> 'switch',
							'options'  => array('H'=>__('HIDE','wplms-front-end' ),'S'=>__('SHOW','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_unit_content',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Full Unit content is available for users subscribed to the course, directly from Course curriculum. Recommended for Offline Courses.','wplms-front-end' )
							),
						array(
							'label'=> __('Course Button (Offline Courses)','wplms-front-end' ),
							'text'=>__('Hide Course Button after subscription','wplms-front-end' ),
							'type'=> 'switch',
							'options'  => array('H'=>__('NO','wplms-front-end' ),'S'=>__('YES','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_button',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Hide the Course button after user is subscribed to the Course.','wplms-front-end' )
							),
						array(
							'label'=> __('Course Progress (Offline Courses)','wplms-front-end' ),
							'text'=>__('Progress on Course home','wplms-front-end' ),
							'type'=> 'switch',
							'options'  => array('H'=>__('HIDE','wplms-front-end' ),'S'=>__('SHOW','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_progress',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Display Course progress on Course home page.','wplms-front-end' )
							),
						array(
							'label'=> __('Auto Progress (Offline Courses)','wplms-front-end' ),
							'text'=>__('Time based Course progress','wplms-front-end' ),
							'type'=> 'switch',
							'options'  => array('H'=>__('NO','wplms-front-end' ),'S'=>__('YES','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_auto_progress',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Automatically calculate progress based on Time elapsed in Course / Total course duration.','wplms-front-end' )
							),
						array(
							'label'=> __('Post Reivews (Offline Courses)','wplms-front-end' ),
							'text'=>__('Post Course reviews from Course Home','wplms-front-end' ),
							'type'=> 'switch',
							'options'  => array('H'=>__('NO','wplms-front-end' ),'S'=>__('YES','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_review',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Enable course subscribed students to post reviews from Course home.','wplms-front-end' )
							),
						array(
							'label'=> __('Course Evaluation','wplms-front-end' ),
							'text'=>__('Course Evaluation Mode','wplms-front-end' ),
							'type'=> 'switch',
							'options'  => array('H'=>__('MANUAL','wplms-front-end' ),'S'=>__('AUTOMATIC','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_auto_eval',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('User gets the course result instantly upon submission.','wplms-front-end' )
							),
						array(
							'label'=> __('Drip Feed','wplms-front-end' ),
							'text'=>__('Course Drip Feed','wplms-front-end' ),
							'type'=> 'conditionalswitch',
							'hide_nodes'=> array('vibe_course_section_drip','vibe_course_drip_origin','vibe_course_drip_duration','vibe_course_drip_duration_type','vibe_drip_duration_parameter'),
							'options'  => array('H'=>__('DISABLE','wplms-front-end' ),'S'=>__('ENABLE','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_drip',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Drip Feed courses, units are released one by one after certain duration of time.','wplms-front-end' ),
							),
						array(
							'label'=> __('Drip Feed Origin','wplms-front-end' ),
							'text'=>__('Drip Feed Origin','wplms-front-end' ),
							'type'=> 'switch',
							'options'  => array('H'=>__('PREVIOUS UNIT','wplms-front-end' ),'S'=>__('STARTING POINT','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_drip_origin', 
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Drip Feed origin, count time from Previous Unit Access Time (default) OR Course starting date/time (if start date not set) .','wplms-front-end' )
							),
						array(
							'label'=> __('Drip Feed Type','wplms-front-end' ),
							'text'=>__('Drip Feed Type','wplms-front-end' ),
							'type'=> 'switch',
							'options'  => array('H'=>__('UNIT','wplms-front-end' ),'S'=>__('SECTION','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_section_drip', 
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Drip Feed type, release units or sections.','wplms-front-end' )
							),
						array(
							'label'=> __('Drip Feed Duration Type','wplms-front-end' ),
							'text'=>__('Drip Feed Duration Type','wplms-front-end' ),
							'type'=> 'reverseconditionalswitch',
							'hide_nodes'=> array('vibe_course_drip_duration','vibe_drip_duration_parameter'),
							'options'  => array('H'=>__('STATIC','wplms-front-end' ),'S'=>__('UNIT DURATION','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_drip_duration_type',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Time gap between adjacent Units/Sections release.','wplms-front-end' )
							),
						array(
							'label'=> __('Drip Duration','wplms-front-end' ),
							'text'=>__('Set Duration between two successive Course elements','wplms-front-end' ),
							'type'=> 'number',
							'style'=>'',
							'id' => 'vibe_course_drip_duration',
							'from'=> 'meta',
							'extra' => '<span data-connect="vibe_drip_duration_parameter">'.calculate_duration_time($drip_duration_parameter).'</span>',
							'default'=>1,
							'desc'=> sprintf(__('Enter the drip duration for the course in %s. This is the duration after which the next unit/section unlocks for the user after viewing the previous unit/section.','wplms-front-end' ),calculate_duration_time($drip_duration_parameter)),
							),
						array(
							'label'=> __('Drip Feed duration parameter','wplms-front-end' ),
							'text'=>__('Set Drip Duration parameter','wplms-front-end' ),
							'type'=> 'duration',
							'style'=>'',
							'id' => 'vibe_drip_duration_parameter',
							'from'=> 'meta',
							'default'=> $drip_duration_parameter,
							'desc'=> __('Set course drip feed duration parameter for this course.','wplms-front-end' ),
							),
						array(
							'label'=> __('Course Certificate','wplms-front-end' ),
							'text'=>__('Course Certificate','wplms-front-end' ),
							'type'=> 'conditionalswitch',
							'hide_nodes'=> array('vibe_course_passing_percentage','vibe_certificate_template'),
							'options'  => array('H'=>__('DISABLE','wplms-front-end' ),'S'=>__('ENABLE','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_certificate',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Award Course completion Certificates to student on course completion.','wplms-front-end' ),
							),
						array(
							'label'=> __('Passing Percentage','wplms-front-end' ),
							'text'=>__('Set Certificate Percentage','wplms-front-end' ),
							'type'=> 'number',
							'style'=>'',
							'id' => 'vibe_course_passing_percentage',
							'from'=> 'meta',
							'extra' => __(' out of 100','wplms-front-end' ),
							'default'=> 40,
							'desc'=> __('Any user achieving more marks (weighted average of Quizzes/assignments in course) than this gets the Course certificate.','wplms-front-end' ),
							),
						array(
							'label'=> __('Certificate Template','wplms-front-end' ),
							'text'=>__('Select Certificate template','wplms-front-end' ),
							'type'=> 'selectcpt',
							'cpt'=> 'certificate',
							'style'=>'',
							'id' => 'vibe_certificate_template',
							'placeholder'=> __('Enter first 3 letters to search course template','wplms-front-end' ),
							'from'=> 'meta',
							'desc'=> __('Connect a custom Certificate template for this Course.','wplms-front-end' ),
							),
						array(
							'label'=> __('Course Badge','wplms-front-end' ),
							'text'=>__('Course Badge','wplms-front-end' ),
							'type'=> 'conditionalswitch',
							'hide_nodes'=> array('vibe_course_badge_percentage','vibe_course_badge_title','vibe_course_badge'),
							'options'  => array('H'=>__('DISABLE','wplms-front-end' ),'S'=>__('ENABLE','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_badge',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('Award Excellence badges to student on course completion.','wplms-front-end' ),
							),
						array(
							'label'=> __('Badge Percentage','wplms-front-end' ),
							'text'=>__('Set Excellence Badge Percentage','wplms-front-end' ),
							'type'=> 'number',
							'style'=>'',
							'id' => 'vibe_course_badge_percentage',
							'from'=> 'meta',
							'extra' => __(' out of 100','wplms-front-end' ),
							'default'=>75,
							'desc'=> __('Any user achieving more marks (weighted average of Quizzes/assignments in course) than this gets the Course Badge.','wplms-front-end' ),
							),
						array(
							'label'=> __('Badge Title','wplms-front-end' ),
							'text'=>__('Set Badge title','wplms-front-end' ),
							'type'=> 'text',
							'style'=>'',
							'id' => 'vibe_course_badge_title',
							'from'=> 'meta',
							'default'=>__('Course Badge Title','wplms-front-end' ),
							'desc'=> __('Course Badge Title','wplms-front-end' ),
							),
						array(
							'label'=> __('Course Badge','wplms-front-end' ),
							'text'=>__('Upload Course Badge','wplms-front-end' ),
							'type'=> 'media',
							'style'=>'',
							'title'=>__('Select or Upload a Course badge.','wplms-front-end' ),
							'button'=>__('Add Course badge.','wplms-front-end' ),
							'id' => 'vibe_course_badge',
							'default'=> VIBE_URL.'/images/add_image.png',
							'from'=> 'meta',
							'desc'=> __('Upload a course badge.','wplms-front-end' ),
							),
						array(
							'label'=> __('Course Retakes','wplms-front-end' ),
							'text'=>__('Student Course Retakes','wplms-front-end' ),
							'type'=> 'number',
							'style'=>'',
							'id' => 'vibe_course_retakes',
							'default'=> 0,
							'from'=> 'meta',
							'desc'=> __('Set number of times a student can re-take the course (0 to disable)','wplms-front-end' ),
							),
						array(
							'label'=> __('Maxium Seats in Course','wplms-front-end' ),
							'text'=>__('Maximum students that can join the Course','wplms-front-end' ),
							'type'=> 'number',
							'style'=>'',
							'id' => 'vibe_max_students',
							'default'=> 0,
							'from'=> 'meta',
							'desc'=> __('Maximum number of seats in course (blank to disable, 9999 for infinite)','wplms-front-end' ),
							),
						array(
							'label'=> __('Course Start Date','wplms-front-end' ),
							'text'=>__('Start date','wplms-front-end' ),
							'type'=> 'date',
							'style'=>'',
							'id' => 'vibe_start_date',
							'default'=> the_date('Y-m-d','','',false),
							'from'=> 'meta',
							'desc'=> __('Set a Course start date.','wplms-front-end' ),
							),
						array(
							'label'=> __('Course Instructions','wplms-front-end' ),
							'text'=>__('Add Course specific instructions','wplms-front-end' ),
							'type'=> 'editor',
							'noscript'=>true,
							'style'=>'',
							'id' => 'vibe_course_instructions',
							'from'=> 'meta',
							'desc'=> __('Course instructions are displayed when the user starts a course.','wplms-front-end' ),
							),
						array(
							'label'=> __('Course Completion Message','wplms-front-end' ),
							'text'=>__('Completion Message','wplms-front-end' ),
							'type'=> 'editor',
							'noscript'=>true,
							'style'=>'',
							'id' => 'vibe_course_message',
							'from'=> 'meta',
							'desc'=> __('Completion message is shown to the student when she finishes the course.','wplms-front-end' ),
							),
						array(
							'label'=>__('SAVE SETTINGS','wplms-front-end' ),
							'id'=>'save_course_settings_button',
							'type'=>'button'
							),
					),
				),
			'course_components'=>array(
					'icon'=> 'archive',
					'title'=> __('COMPONENTS','wplms-front-end' ),
					'subtitle'=>  __('Course settings','wplms-front-end' ),
					'fields'=>array(
						array(
							'label'=> __('Course Group','wplms-front-end' ),
							'text'=>__('Set a Course Group','wplms-front-end' ),
							'type'=> 'group',
							'style'=>'',
							'id' => 'vibe_group',
							'from'=> 'meta',
							'desc'=> __('Set a course specific group.','wplms-front-end' ),
							),
						array(
							'label'=> __('Course Forum','wplms-front-end' ),
							'text'=>__('Set a Course Forum','wplms-front-end' ),
							'type'=> 'forum',
							'style'=>'',
							'id' => 'vibe_forum',
							'from'=> 'meta',
							'desc'=> __('Set a course forum.','wplms-front-end' ),
							),
						
						array(
							'label'=>__('SAVE COMPONENTS','wplms-front-end' ),
							'id'=>'save_course_components_button',
							'type'=>'button'
							),
						)
					),
			'course_curriculum'=>array(
					'icon'=> 'file',
					'title'=> __('SET CURRICULUM','wplms-front-end' ),
					'subtitle'=>  __('Add Units and Quizzes','wplms-front-end' ),
					'fields'=>array(
						array(
							'label'=>__('Curriculum'),
							'id'=>'vibe_course_curriculum',
							'buttons'=> array('add_course_section'=>__('ADD SECTION','wplms-front-end' ),'add_course_unit'=>__('ADD UNIT','wplms-front-end' ),'add_course_quiz'=>__('ADD QUIZ','wplms-front-end' )),
							'type'=> 'curriculum',
							'style'=>'',
							'default'=>9999,
							'desc'=> __('Build Course curriculum','wplms-front-end' ),
							),
						),
				),
			'course_pricing' => array(
					'icon'=> 'tag',
					'title'=>  __('PRICING','wplms-front-end' ),
					'subtitle'=>  __('Set Price for Course','wplms-front-end' ),
					'fields'=>array(
						array(
							'label'=> __('Course Pricing','wplms-front-end' ),
							'type'=> 'heading',
							),
						array(
							'label'=> __('Free ','wplms-front-end' ),
							'text'=>__('Free Course','wplms-front-end' ),
							'type'=> 'switch',
							'options'  => array('H'=>__('No','wplms-front-end' ),'S'=>__('Yes','wplms-front-end' )),
							'style'=>'',
							'id' => 'vibe_course_free',
							'from'=> 'meta',
							'default'=>'H',
							'desc'=> __('By pass any purchase process.','wplms-front-end' )
							),
						array(
							'label'=> __('Product ','wplms-front-end' ),
							'text'=>__('Set a Course Product','wplms-front-end' ),
							'cpt'=>'product',
							'type'=> 'selectproduct',
							'style'=>'',
							'id' => 'vibe_product',
							'from'=> 'meta',
							'default'=>'',
							'desc'=> __('Connect a Product with this course.','wplms-front-end' )
							),
						array(
							'label'=>__('SAVE PRICING','wplms-front-end' ),
							'id'=>'save_pricing_button',
							'type'=>'button'
							),
						),
				),
			'course_live'=>array(
					'icon'=> 'glass',
					'title'=> (($this->status == 'publish')?__('MODIFY COURSE','wplms-front-end' ):__('PUBLISH COURSE','wplms-front-end' )),
					'subtitle'=>  (($this->status == 'publish')?__('Change Course status','wplms-front-end' ):__('Go Live !','wplms-front-end' )),
					'fields'=>array(
						array(
							'label'=> __('Take Offline','wplms-front-end' ),
							'id'=>'offline_course',
							'type'=> 'course_live',
							),
						array(
							'label'=> __('Save as Template','wplms-front-end' ),
							'id'=>'save_course_creation_template',
							'type'=> 'save_course_creation_template',
							),
						),
				)
			));
	}

	function settings(){
		foreach($this->course_settings as $key => $value){
			$this->generate_fields($value);
		}
	}
	function prepopulate(){

		if(is_numeric($this->course_id)){
			
		    $course_cats =wp_get_post_terms( $this->course_id, 'course-cat');
		    $course_cats[0]->term_id;

		    if(isset($linkage ) && $linkage ){
		        wp_get_post_terms( $this->course_id, 'linkage',array("fields" => "names"));
		    } 
		}
	}

	function create_tabs(){
		?>
		<div class="col-md-3 col-sm-4">
			<div id="course_creation_tabs" class="course-create-steps">
	            <ul <?php echo ((isset($this->course_id))?'class="islive"':'');?>>
	            	<?php
	            		$tabs = $this->tabs();
	            		foreach($tabs as $key => $tab){
	            			echo '<li class="'.$key.' '.(($key == 'create_course')?'active':'').'"><i class="icon-'.$tab['icon'].'"></i><a href="#'.$key.'">  '.$tab['title'].'<span>'.$tab['subtitle'].'</span></a></li>';
	            		}
	            	?>
	            </ul>
	        </div>
		<?php

		$extras = apply_filters('wplms_front_end_course_extras',array(
			array(
				'label'=> '<i class="fa fa-pencil"></i>',
				'title' => __('Edit in WP Admin','wplms-front-end'),
				'link' => get_edit_post_link($this->course_id),
				'condition' => (is_numeric($this->course_id)?1:0)
				),
			array(
				'label'=> '<i class="fa fa-object-ungroup"></i>',
				'title' => __('Course templates','wplms-front-end'),
				'link' => 'javascript:void(0)',
				'id'=> 'create_course_templates_popop_button',
				'condition' => 1,
				),
			array(
				'label'=> '<i class="icon-eye"></i>',
				'title' => __('View Course','wplms-front-end'),
				'link' => get_permalink($this->course_id),
				'condition' => (is_numeric($this->course_id)?1:0)
				),
			));
			 echo '<ul class="course_extras">';
			foreach($extras as $extra){
				if($extra['condition']){
					echo '<li><a href="'.$extra['link'].'" title="'.$extra['title'].'" id="'.((!empty($extra['id']))?$extra['id']:'').'">'.$extra['label'].'</a></li>';
				}
				
			}
			echo '</ul>';
		?>
		</div>
		<?php
	}

	function course_tab_module(){
		$tabs = $this->tabs();
		?>
		<div class="col-md-9 col-sm-8">   
            <div class="edit_course_content content">
            	<?php
            		foreach($tabs as $key => $tab){
            			echo '<div id="'.$key.'" '.(($key == 'create_course')?'class="active"':'').'>';
            			if(isset($tab['fields']) && is_array($tab['fields']) && count($tab['fields'])){
            				echo '<article class="container-fluid"><ul class="'.$key.'">';
            				foreach($tab['fields'] as $field){
            					echo '<li class="vibe_'.(isset($field['id'])?$field['id']:'').'">'; 
            					$this->generate_fields($field);
            					echo '</li>';
            				}
            				echo '</ul></article>';
            			}
            			echo '</div>';
            		}
            		wp_nonce_field('security','security');
            		echo '<input type="hidden" id="course_id" value="'.((get_post_type($_GET['action']) == 'course')?$_GET['action']:'').'" />';
            	?>
            </div>
        </div>
		<?php
	}

	function course_pricing_models($settings){
        $product_settings = vibe_meta_box_arrays('course_product');	

        unset($product_settings['vibe_course_free']);
        unset($product_settings['vibe_product']);
        foreach($product_settings as $key => $prd_set ){
        	if(empty($product_settings[$key]['text'])){
            	$product_settings[$key]['text']=$product_settings[$key]['label'];
            	if(!empty($this->course_id) && empty($prd_set['value'])){
            		$product_settings[$key]['value'] = get_post_meta($this->course_id,$prd_set['id'],true);	
            	}
        	}
        }


        if(in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'woocommerce/woocommerce.php'))){
             array_splice($settings['course_pricing']['fields'],3,0,$product_settings);
         }else{
             unset($settings['course_pricing']['fields'][2]);
             array_splice($settings['course_pricing']['fields'],2,0,$product_settings);
         }

        return $settings;
    }
    
	function generate_fields($field,$id = null){

	    if(!empty($id) && empty($this->course_id)){
	        $this->course_id = $id;
	    }

	    if(empty($id) && !empty($this->course_id)){
	        $id = $this->course_id;
	    }

	    $user_id=get_current_user_id();
	    if(!empty($id)){
	        $from ='';
	        if(isset($field['from'])){
	            $from = $field['from'];
	        }
	        switch($from){
	            case 'post':
	                $field['value'] = get_post_field($field['id'],$id);
	            break;
	            case 'taxonomy':
	                $terms    = wp_get_post_terms($id,$field['taxonomy']);
	                if(!empty($terms)){
	                    $field['value'] = $terms[0]->term_id;
	                }
	            break;
	            default:
	                if(isset($field['value_type']) && $field['value_type'] == 'array'){
	                    $single = false;
	                }else{
	                    $single = true;
	                }
	                if(empty($field['value']) && isset($field['id'])){
	                	$field['value'] = get_post_meta($id,$field['id'],$single);
	                	if($field['id'] == 'vibe_subscription1' ){
	                		$field['value'] = get_post_meta($id,'vibe_subscription',$single);
	                	}
	                	if($field['id'] == 'vibe_product_duration'){
	                		 $field['value'] = get_post_meta($id,'vibe_duration',$single);
	                	}
	                    
	                }
	            break;
	        }
	    }else{
	        if(!isset($field['value']))
	            $field['value'] = $field['default'];
	    }
	    
		$field_type ='';
		if(isset($field['type'])){
			$field_type = $field['type'];
		}
		switch($field_type){

			case 'heading':
				echo '<strong>'.$field['label'].'</strong>';
			break;
			case 'number': 
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">
					   <label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>'.(!empty($field['text'])?'<strong>'.$field['text'].'</strong>':''); 
				echo (!empty($field['text'])?'<div class="right">':'').'<input type="number" id="" class="small_box post_field '.(empty($field['text'])?'form_field':'').'" data-id="'.$field['id'].'" data-type="'.$field['type'].'" placeholder="'.(isset($field['default'])?$field['default']:'').'" value="'.$field['value'].'"/>'.(isset($field['extra'])?$field['extra']:'').'</div>';
				echo   (!empty($field['text'])?'</div>':'');
			break;
			case 'text':
				echo   '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo   '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				echo   (!empty($field['text'])?'<strong>'.$field['text'].'</strong>':''); 
				echo   '<input type="text" placeholder="'.(isset($field['default'])?$field['default']:'').'" data-id="'.$field['id'].'" data-type="'.$field['type'].'" class="mid_box post_field '.(empty($field['text'])?'form_field':'').'" value="'.$field['value'].'"/>'.(isset($field['extra'])?$field['extra']:'');
				echo   '</div>';
			break;
			case 'color':
				echo   '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo   '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				echo   (!empty($field['text'])?'<strong>'.$field['text'].'</strong>':''); 
				echo   '<input type="text" placeholder="'.(isset($field['default'])?$field['default']:'').'" data-id="'.$field['id'].'" data-type="'.$field['type'].'" class="mid_box post_field color_picker'.(empty($field['text'])?'form_field':'').'" value="'.$field['value'].'" />'.(isset($field['extra'])?$field['extra']:'');
				echo   '</div>';
			break;
			case 'title':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				echo '<input type="text"  class="megatext post_field" data-id="'.$field['id'].'" data-type="'.$field['type'].'" placeholder="'.$field['default'].'" value="'.(($field['default'] != $field['value'])?$field['value']:'').'" />';
				echo   '</div>';
			break;
			case 'taxonomy':
			echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				?>
				<ul id="<?php echo $field['id']; ?>" class="wplms-taxonomy">
                    <li>
                        <select id="<?php echo $field['id']; ?>-select" class="chosen post_field" <?php echo 'data-id="'.$field['id'].'" data-taxonomy="'.$field['taxonomy'].'" data-type="'.$field['type'].'"'; ?>>
                            <option value=""><?php echo $field['default']; ?></option>
                            <?php $new_tax = apply_filters('wplms_front_end_new_tax_cap',true,$field);if($new_tax) {?>
                            	<option value="new"><?php _e('Add new','wplms-front-end' ); ?></option>
                            <?php
                        	}
                            $terms = get_terms($field['taxonomy'],apply_filters('wplms_front_end_field_taxonomy_args',array('hide_empty' => false,'orderby'=>'name','order'=>'ASC'),$field));
                            if(isset($terms) && is_array($terms))
                            foreach($terms as $term){
                                $parenttermname='';
                                if($term->parent){
                                    $parentterm=get_term_by('id', $term->parent, $field['taxonomy'], 'ARRAY_A');
                                    $parenttermname = $parentterm['name'].' &rsaquo; ';
                                }
                                $selected = 0;

                                if(isset($_GET['action']) || isset($field['value'])){
                                	if($field['value'] == $term->term_id){
                                		$selected = 1;
                                	}
                                }
                                echo '<option value="'.$term->term_id.'" '.(($selected)?'selected="selected"':'').'>'.$parenttermname.$term->name.'</option>';
                            }
                            ?>
                        </select>
                    </li>
                    <li><input type="text" id="new_<?php echo str_replace('-','_',$field['id']); ?>" class="ccf_text post_field wplms-new-taxonomy" <?php echo 'data-id="'.$field['taxonomy'].'" data-type="'.$field['type'].'_new"'; ?> placeholder="<?php _e('Enter a new','wplms-front-end' ); ?>" /></li>
                </ul><br />
				<?php
				echo   '</div>';
				echo '<hr />';
			break;
			case 'featured_image':
			echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
                 ?>      
                <div id="<?php echo $field['id']; ?>" class="upload_image_button" data-input-name="<?php echo $field['id']; ?>" data-uploader-title="<?php echo $field['upload_title'];?>" data-uploader-button-text="<?php echo $field['upload_button'];?>" data-uploader-allow-multiple="false">
                    <?php 
                    if(isset($this->course_id) && has_post_thumbnail($this->course_id )){
                    	if($field['level'] == 'thumbnail'){
                    		echo get_the_post_thumbnail($this->course_id,'thumbnail');
                    		echo '<input type="hidden" value="'.get_post_thumbnail_id($this->course_id).'" class="post_field" data-id="'.$field['id'].'" data-type="'.$field['type'].'" />';
                    	}
                    }else{
                    ?>
                    <img src="<?php echo $field['default']; ?>" alt="course image" class="default" />
                    <?php
                    }
                    ?>
                </div>
                <?php
            echo   '</div>';    
			break;
			case 'featured_video':
			echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
                 ?>      
                
                <div class="add_url" style="
				    max-width: 200px;
				    font-size: 12px'">
					<div class="input-group add_url_text_wrapper">
					  <input type="text" id="<?php echo $field['id']; ?>" data-id="<?php echo $field['id']; ?>" class="post_field add_url_text form-control" placeholder="" value="<?php echo $field['value'] ?>">
					  <span class="input-group-addon"><i class="upload_video_button fa fa-upload"></i></span>
					</div>
	            </div>

                <script>
                	var media_uploader2;
					jQuery('.upload_video_button').on('click', function( event ){
					  
					    var button = jQuery( this );
					    if ( media_uploader2 ) {
					      media_uploader2.open();
					      return;
					    }
					    // Create the media uploader.
					    media_uploader2 = wp.media.frames.media_uploader = wp.media({
					        title: button.data( 'uploader-title' ),
					        // Tell the modal to show only images.
					        library: {
					            type: 'video',
					            query: false
					        },
					        button: {
					            text: button.data( 'uploader-button-text' ),
					        },
					        multiple: button.data( 'uploader-allow-multiple' )
					    });

					    // Create a callback when the uploader is called
					    media_uploader2.on( 'select', function() {
					        var selection = media_uploader2.state().get('selection'),
					            input_name = button.data( 'input-name' );
					            selection.map( function( attachment ) {
					            attachment = attachment.toJSON();
					            console.log(attachment);
					            var url_video='';
					            if(attachment &&  attachment.url !== undefined ){
					               url_video=attachment.url;
					            }else{
					            	altert('<?php echo _x("Unable to find url of selected video","","wplms-front-end");?>');
					            }
					            button.closest('.add_url_text_wrapper').find('.add_url_text').val(url_video);
					           
					         });

					    });
					    // Open the uploader
					    media_uploader2.open();
					  });
                </script>
                <style>
                	.vibe_post_video {
    					display: inline-block;
    				}
                </style>
                <?php

            echo   '</div>';    
			break;
			case 'media':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				echo (empty($field['text'])?'':'<strong>'.$field['text'].'</strong>').'
					<div class="upload_button">';
				if(is_numeric($field['value']))	{
					$attachment = wp_get_attachment_image_src($field['value']);
					if(empty($attachment)){
						echo '<a id="'.$field['id'].'" data-input-name="'.$field['id'].'" data-uploader-title="'.$field['title'].'" data-uploader-button-text="'.$field['button'].'"><i class="icon-image-photo-file-1"></i></a>';
					}else{
						$url = $attachment[0];	
						echo '<a id="'.$field['id'].'" data-input-name="'.$field['id'].'" data-uploader-title="'.$field['title'].'" data-uploader-button-text="'.$field['button'].'"><img src="'.$url.'" class="submission_thumb thumbnail" /><input type="hidden" value="'.$field['value'].'" data-id="'.$field['id'].'" data-type="'.$field['type'].'" /></a>';
					}
				}else{
					echo '<a id="'.$field['id'].'" data-input-name="'.$field['id'].'" data-uploader-title="'.$field['title'].'" data-uploader-button-text="'.$field['button'].'"><i class="icon-image-photo-file-1"></i></a>';
				}
				echo '<script>
					var media_uploader'.$field['id'].';
					jQuery("#'.$field['id'].'").on("click", function( event ){
					  
					    var button = jQuery( this );
					    if ( media_uploader'.$field['id'].' ) {
					      media_uploader'.$field['id'].'.open();
					      return;
					    }
					    // Create the media uploader.
					    media_uploader'.$field['id'].' = wp.media.frames.media_uploader'.$field['id'].' = wp.media({
					        title: button.attr( "data-uploader-title"),
					        library: {
					            type: "image",
					            query: false
					        },
					        button: {
					            text: button.attr("data-uploader-button-text"),
					        },
					    });

					    // Create a callback when the uploader is called
					    media_uploader'.$field['id'].'.on( "select", function() {
					        var selection = media_uploader'.$field['id'].'.state().get("selection"),
					            input_name = button.data( "input-name");
					            selection.map( function( attachment ) {
					            	attachment = attachment.toJSON(); console.log(attachment);
					            	button.html("<img src=\'"+attachment.url+"\' class=\'submission_thumb thumbnail\' /><input id=\'"+input_name +"\' class=\'form-control post_field\' name=\'"+input_name+"\' data-id=\''.$field['id'].'\' data-type=\''.$field['type'].'\' type=\'hidden\' value=\'"+attachment.id+"\' />");
					         	});
					    });
					    // Open the uploader
					    media_uploader'.$field['id'].'.open();
					  });
				</script>
				</div>';
			break;
			case 'textarea':
			echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				echo '<textarea id="'.$field['id'].'" class="post_field" data-id="'.$field['id'].'" data-type="'.$field['type'].'">'. $field['value'].'</textarea>'.(empty($field['extras'])?'':$field['extras']);
			echo   '</div>';	
			break;
			case 'editor':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'. $field['label'].'</label>';
				wp_editor($field['value'],$field['id'],array('editor_class'=>'post_field'));
				echo   '</div>';
				if(!isset($field['noscript'])){
				echo '<script>jQuery(document).ready(function($){
						tinyMCE.execCommand("mceRemoveEditor", true, "'.$field['id'].'");
                        tinyMCE.execCommand("mceAddEditor", false, "'.$field['id'].'"); 
                        quicktags({id : "element_content"});
                        tinyMCE.triggerSave();});
                    </script>';
                }
			break;
			case 'date':
			case 'calendar':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				echo (!empty($field['text'])?'<strong>'.$field['text'].'</strong><div class="right">':''); 
				echo '<input type="text" placeholder="'.$field['default'].'" value="'.$field['value'].'" data-id="'.$field['id'].'" class="mid_box date_box post_field '.(empty($field['text'])?'form_field':'').'" data-id="'.$field['id'].'" data-type="'.$field['type'].'"/>';
				echo (!empty($field['text'])?'</div>':'');
				echo   '<script>jQuery(document).ready(function(){
						jQuery( ".date_box" ).datepicker({
		                    dateFormat: "'.apply_filters('wplms_front_end_datepicker_format','yy-mm-dd').'",
		                    numberOfMonths: 1,
		                    showButtonPanel: true,
		                });});</script><style>.ui-datepicker{z-index:99 !important;}a.ui-state-default.ui-state-highlight {background: #70c989;color: #fff;}</style></div>';
			break;
			case 'time':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				echo (!empty($field['text'])?'<strong>'.$field['text'].'</strong><div class="right">':''); 
				echo '<input type="text" placeholder="'.$field['default'].'" value="'.$field['value'].'" data-id="'.$field['id'].'" class="mid_box time_box post_field '.(empty($field['text'])?'form_field':'').'" data-id="'.$field['id'].'" data-type="'.$field['type'].'"/>';
				echo (!empty($field['text'])?'</div>':'');
				echo   '<script>
				jQuery(document).ready(function(){
                 jQuery( ".time_box" ).each(function(){
                 jQuery(this).timePicker({
                      show24Hours: '.apply_filters('wplms_front_end_timepicker_format','false').',
                      separator:":",
                      step: 15
                  });
                });});</script></div>';
			break;
			case 'group':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				?>
				<input type="hidden" id="vibe_group" class="post_field" value="<?php echo $field['value']; ?>" <?php echo 'data-id="'.$field['id'].'" data-type="'.$field['type'].'" '; ?> />
				<?php
				echo '<span class="dashicons dashicons-dismiss clear_input" data-id="vibe_group"><div class="hide"><span>'.$field['text'].'</span></div></span>';
				echo '<h3>';
				if(empty($field['value']) || (isset($field['default']) && $field['value'] == $field['default'])){
					echo '<span>'.$field['text'].'</span>';
					$flag=0;
				}else{
					echo self::get_group_name($field['value']); 
					echo '<span><a id="edit_group" href="'.self::get_group_permalink($field['value']).'" target="_blank"><i>'.__('edit','wplms-front-end' ).'</i></a>&nbsp;<i>'.__('change','wplms-front-end' ).'</i></span>';
					$flag = 1;
				}
				echo '</h3>';
				?>
				<div id="change_group" class="row" <?php echo (($flag)?'style="display:none;"':''); ?>>
					<div class="col-md-6">
						<a class="more"><i class="icon-users"></i> <?php _e('Select Existing Group','wplms-front-end' ); ?></a>
						<div class="select_group_form">
							<select class="selectgroup" data-placeholder="<?php _e('Select a Group','wplms-front-end' ); ?>">
							</select>
							<a class="use_selected button"><?php _e('Set','wplms-front-end' ); ?></a>
						</div>
					</div>
					<div class="col-md-6">
						<a class="more"><i class="icon-user"></i> <?php _e('Create New Group','wplms-front-end' ); ?></a>
						<div class="new_group_form">
							<input type="text" class="form_field" id="vibe_group_name" name="name" placeholder="<?php _e('Group Name','wplms-front-end' ); ?>">
							<select id="vibe_group_privacy" class="form_field">
								<option value="public"><?php _e('Public','wplms-front-end' ); ?></option>
								<option value="private"><?php _e('Private','wplms-front-end' ); ?></option>
								<option value="hidden"><?php _e('Hidden','wplms-front-end' ); ?></option>
							</select>
							<textarea class="description" id="vibe_group_description" placeholder="<?php _e('Group Description','wplms-front-end' ); ?>"></textarea>
							<a class="button small" id="create_new_group"><?php _e('Create New Group','wplms-front-end' ); ?></a>
						</div>
					</div>
				</div>
               <?php
				echo   '</div>';
			break;
			case 'forum':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				?>
				<input type="hidden" id="vibe_forum" class="post_field" value="<?php echo $field['value']; ?>" <?php echo 'data-id="'.$field['id'].'" data-type="'.$field['type'].'"'; ?> />
				<?php
				echo '<span class="dashicons dashicons-dismiss clear_input" data-id="vibe_forum"><div class="hide"><span>'.$field['text'].'</span></div></span>';
				echo '<h3>';
				if(empty($field['value']) || (isset($field['default']) && $field['value'] == $field['default'])){
					echo '<span>'.$field['text'].'</span>';
					$flag=0;
				}else{
					echo get_the_title($field['value']); 
					echo '<span><a href="'.get_permalink($field['value']).'" target="_blank"><i>'.__('edit','wplms-front-end' ).'</i></a>&nbsp;<i>'.__('change','wplms-front-end' ).'</i></span>';
					$flag = 1;
				}
				echo '</h3>'; ?>
				<div id="change_forum" class="row" <?php echo (($flag)?'style="display:none;"':''); ?>>
					<div class="col-md-6">
						<a class="more"><i class="icon-users"></i> <?php _e('Select Existing Forum','wplms-front-end' ); ?></a>
						<div class="select_forum_form">
							<select class="selectforum" data-placeholder="<?php _e('Select a Forum','wplms-front-end' ); ?>">
							</select>
							<a class="use_selected button"><?php _e('Set','wplms-front-end' ); ?></a>
						</div>
					</div>
					<div class="col-md-6">
						<a class="more"><i class="icon-user"></i> <?php _e('Create New Forum','wplms-front-end' ); ?></a>
						<div class="new_forum_form">
							<input type="text" class="form_field" id="vibe_forum_name" name="name" placeholder="<?php _e('Forum Name','wplms-front-end' ); ?>">
							<select id="vibe_forum_privacy" class="form_field">
								<option value="publish"><?php _e('Public','wplms-front-end' ); ?></option>
								<option value="private"><?php _e('Private','wplms-front-end' ); ?></option>
								<option value="hidden"><?php _e('Hidden','wplms-front-end' ); ?></option>
							</select>
							<textarea class="description" id="vibe_forum_description" placeholder="<?php _e('Forum Description','wplms-front-end' ); ?>"></textarea>
							<a class="button small" id="create_new_forum"><?php _e('Create New Forum','wplms-front-end' ); ?></a>
						</div>
					</div>
				</div>
				<?php
			break;
			case 'yesno':
			case 'showhide':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label><strong>'.$field['text'].'</strong><div class="switch">';
				$i=0;
				foreach($field['options'] as $option){
					echo '<input type="radio" class="switch-input post_field '.$field['id'].'" name="'.$field['id'].'" data-id="'.$field['id'].'" data-type="'.$field['type'].'" value="'.$option['value'].'" id="'.$field['id'].$option['value'].'" ';checked($field['value'],$option['value']); echo '>';
	                   echo '<label for="'.$field['id'].$option['value'].'" class="switch-label switch-label-'.(!($i%2)?'off':'on').'">'.$option['label'].'</label>';$i++;
				}
	            echo '<span class="switch-selection"></span></div></div>';
			break;
			case 'switch':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label><strong>'.$field['text'].'</strong><div class="switch">';
				$i=0;
				if(empty($field['value'])){
					if(empty($field['default'])){
						if(!empty($field['std']))
							$field['value'] = $field['std'];
					}else
						$field['value'] = $field['default'];
				}
				foreach($field['options'] as $key=>$value){
					echo '<input type="radio" class="switch-input post_field '.$field['id'].'" name="'.$field['id'].'" data-id="'.$field['id'].'" data-type="'.$field['type'].'" value="'.$key.'" id="'.$field['id'].$key.'" ';checked($field['value'],$key); echo '>';
	                   echo '<label for="'.$field['id'].$key.'" class="switch-label switch-label-'.(!($i%2)?'off':'on').'">'.$value.'</label>';$i++;
				}
	            echo '<span class="switch-selection"></span></div></div>';
			break;
			case 'conditionalswitch':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label><strong>'.$field['text'].'</strong>';
				
				$i=0;
				if(empty($field['value'])){
					if(empty($field['default'])){
						if(!empty($field['std']))
							$field['value'] = $field['std'];
					}else
						$field['value'] = $field['default'];
				}

				echo '<div class="switch conditional">';

				foreach($field['options'] as $key=>$value){
					echo '<input type="radio" data-id="'.$field['id'].'" data-type="'.$field['type'].'" class="switch-input post_field conditional-switch '.$field['id'].'" data-id="'.$field['id'].'" data-type="'.$field['type'].'" name="'.$field['id'].'" value="'.$key.'" id="'.$field['id'].$key.'" ';checked($field['value'],$key); echo '>';
                    echo '<label for="'.$field['id'].$key.'" class="switch-label switch-label-'.(!($i%2)?'off':'on').'">'.$value.'</label>';
                    $i++;
				}
	            echo '<span class="switch-selection"></span></div></div>';
	            if(!empty($field['hide_nodes'])){
	            	echo '<script>
			            jQuery(document).ready(function($){
			            	$(".conditional-switch.'.$field['id'].'").each(function(){
			            		var $this=$(this);	
			            		var val = $(this).parent().find(".conditional-switch.'.$field['id'].':checked").val();
				            		if(val == "S" || val === "S" || val == "on" || val === "on" || val == "yes" || val === "yes"){
				            			
					        		var $this=$(this);
					        		$this.parent().addClass("active");';
				            		foreach($field['hide_nodes'] as $node){
				            			echo '$(".vibe_'.$node.'").show(200).addClass("conditional_display");';
					            	}
					        		echo '}		
				            	$this.on("click",function(){
				            		var val = $(this).parent().find(".conditional-switch.'.$field['id'].':checked").val();
				            		if(val == "S" || val === "S" || val == "on" || val === "on" || val == "yes" || val === "yes"){
				            			$this.parent().addClass("active");';
				            		foreach($field['hide_nodes'] as $node){
				            			echo '$(".vibe_'.$node.'").show(200).addClass("conditional_display");';
					            	}
					        		echo '}else{
					        			$this.parent().removeClass("active");';
					        				foreach($field['hide_nodes'] as $node){
						            			echo '$(".vibe_'.$node.'").hide(200).removeClass("conditional_display");';
							            	}
									echo '		            	
				            		}
				            	});
				            });
						});
		            </script><style>';

		            if(empty($field['value']) || $field['value'] != "S" || $field['value'] != "on" || $field['value'] != "yes" ){
		            	foreach($field['hide_nodes'] as $key => $node){
		            		if(!empty($key)){echo ',';};
		            		echo '.vibe_'.$node.'';
		            	}
		            	echo '{display:none !important;}';
		            }
		            echo '</style>';
		        }    	
			break;
			case 'reverseconditionalswitch':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label><strong>'.$field['text'].'</strong>
				<div class="switch conditional active">';
				$i=0;
				if(empty($field['value'])){
					if(empty($field['default'])){
						if(!empty($field['std']))
							$field['value'] = $field['std'];
					}else
						$field['value'] = $field['default'];
				}
				foreach($field['options'] as $key=>$value){
					echo '<input type="radio" data-id="'.$field['id'].'" data-type="'.$field['type'].'" class="switch-input post_field conditional-switch '.$field['id'].'" data-id="'.$field['id'].'" data-type="'.$field['type'].'" name="'.$field['id'].'" value="'.$key.'" id="'.$field['id'].$key.'" ';checked($field['value'],$key); echo '>';
                    echo '<label for="'.$field['id'].$key.'" class="switch-label switch-label-'.(!($i%2)?'off':'on').'">'.$value.'</label>';
                    $i++;
				}
	            echo '<span class="switch-selection"></span></div></div>';
	            if(!empty($field['hide_nodes'])){
	            	echo '<script>
			            jQuery(document).ready(function($){
			            	$(".conditional-switch.'.$field['id'].'").each(function(){	
			            		var $this=$(this);
				            	$(this).on("click",function(){
				            		var val = $(this).parent().find(".conditional-switch.'.$field['id'].':checked").val();
				            		if(val == "H" || val === "H" || val == "off" || val === "off" || val == "no" || val === "no"){';
				            		foreach($field['hide_nodes'] as $node){
				            			echo '$(".vibe_'.$node.'").show(200).addClass("conditional_display");$this.parent().addClass("active");';
					            	}
					        		echo '}else{';
					        				foreach($field['hide_nodes'] as $node){
						            			echo '$(".vibe_'.$node.'").hide(200).removeClass("conditional_display");
						            			$this.parent().removeClass("active");';
							            	}
									echo '		            	
				            		}
				            	});
				            });
						});
		            </script>';
		        }
			break;
			case 'select':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>'.(!empty($field['text'])?'<strong>'.$field['text'].'</strong>':''); 
				echo '<select class="post_field" data-id="'.$field['id'].'" data-type="'.$field['type'].'" >';
				if(!empty($field['options'])){
					foreach($field['options'] as $option){
						echo '<option value="'.$option['value'].'" '.(($field['value'] == $option['value'])?'selected="selected"':'').'>'.$option['label'].'</option>';
					}
				}
				echo '</select></div>';
			break;
			case 'duration':
		        echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>'.(!empty($field['text'])?'<strong>'.$field['text'].'</strong>':''); 
				echo '<select data-id="'.$field['id'].'" class="post_field" data-type="'.$field['type'].'" >';
				$field['options'] = array(
						array('value'=>1,'label'=>__('Seconds','wplms-front-end')),
						array('value'=>60,'label'=>__('Minutes','wplms-front-end')),
						array('value'=>3600,'label'=>__('Hours','wplms-front-end')),
						array('value'=>86400,'label'=>__('Days','wplms-front-end')),
						array('value'=>604800,'label'=>__('Weeks','wplms-front-end')),
						array('value'=>2592000,'label'=>__('Months','wplms-front-end')),
						array('value'=>31536000,'label'=>__('Years','wplms-front-end')),
					);
				if(!empty($field['options'])){
					foreach($field['options'] as $option){
						echo '<option value="'.$option['value'].'" '.(($field['value'] == $option['value'])?'selected="selected"':'').'>'.$option['label'].'</option>';
					}
				}
				echo '</select></div>';     
			break;
			case 'selectcpt':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>'.(!empty($field['text'])?'<strong>'.$field['text'].'</strong>':''); 
				if(empty($field['post_status'])){
					$field['post_status'] = 'publish';
				}else{
					$field['post_status'] = implode(',',$field['post_status']);
				}
				echo (!empty($field['text'])?'<div class="right">':'').'<select class="select2 selectcpt post_field" data-id="'.$field['id'].'" data-type="'.$field['type'].'" data-status="'.$field['post_status'].'" style="width: 100%;" data-placeholder="'.$field['placeholder'].'" data-cpt="'.(($field['cpt'])?$field['cpt']:(($field['post_type'])?$field['post_type']:'')).'">';
				if(!empty($field['value']) && $field['value'] != $field['default']){
					echo '<option value="'.$field['value'].'">'.get_the_title($field['value']).'</option>';
				}
				echo '</select></div>';
			break;

			case 'selectproduct':

				$product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400,$field['value']); 
				$product_fields= apply_filters('wplms_front_end_new_product',array(
					array(
					'label'=> __('Title','wplms-front-end' ),
					'placeholder'=>__('Product Title','wplms-front-end' ),
					'type'=> 'text',
					'style'=>'',
					'from'=>'post',
					'id' => 'post_title',
					'desc'=> __('Product title is useful to identify courses connected to this product.','wplms-front-end' ),
					),
					array(
					'label'=> __('Price','wplms-front-end' ),
					'text'=>__('Price','wplms-front-end' ),
					'type'=> 'text',
					'style' => 'col-md-6',
					'id' => '_regular_price',
					'extra' => get_woocommerce_currency(),
					'desc'=> __('Set price of the course','wplms-front-end' ),
					),
					array(
					'label'=> __('Sale','wplms-front-end' ),
					'text'=>__('Sale Price','wplms-front-end' ),
					'type'=> 'text',
					'style' => 'col-md-6',
					'id' => '_sale_price',
					'extra' => get_woocommerce_currency(),
					'desc'=> __('Blank if product not in sale','wplms-front-end' ),
					),
					array(
					'label'=> __('Subscription','wplms-front-end' ),
					'type'=> 'conditionalswitch',
					'text'=>__('Subscription Type','wplms-front-end' ),
					'hide_nodes'=> array('vibe_product_duration','vibe_product_duration_parameter'),
					'options'  => array('H'=>__('FULL DURATION','wplms-front-end' ),'S'=>__('LIMITED','wplms-front-end' )),
					'style'=>'',
					'default'=>'H',
					'id' => 'vibe_subscription',
					'desc'=> __('Set subscription type of product.','wplms-front-end' ),
					),
					array(
					'label'=> __('Subscription Duration','wplms-front-end' ),
					'type'=> 'text',
					'text' => __('Set subscription duration','wplms-front-end'),
					'style'=>'',
					'id' => 'vibe_product_duration',
					'from'=> 'meta',
					'default'=> __('Must not be 0','wplms-front-end'),
					),
					array(
					'label'=> __('Subscription Duration Parameter','wplms-front-end' ),
					'text' => __('Set subscription duration parameter','wplms-front-end'),
					'type'=> 'duration',
					'style'=>'',
					'id' => 'vibe_product_duration_parameter',
					'default' => $product_duration_parameter,
					'from'=> 'meta',
					),
				));

				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				if(!empty($field['value']) && $field['value'] != $field['default']){
					$product = wc_get_product($field['value']);
					echo '<span class="dashicons dashicons-dismiss clear_input" data-id="product_id"><div class="hide">'.$field['text'].'<span class="change_product">'.__('Set Product','wplms-front-end').'</span><input type="hidden" id="product_id" class="post_field" data-id="vibe_product" /></div></span>';
					echo '<h3>'.get_the_title($field['value']).'<span class="change_product">'.__('Change','wplms-front-end').'</span><span class="edit_product">'.__('Edit','wplms-front-end').'</span><strong class="price">'.(is_object($product)?$product->get_price_html():'').'</strong>
					<input type="hidden" id="product_id" class="post_field" data-id="vibe_product" value="'.$field['value'].'" /></h3>';
				}else{
					echo '<h3>'.$field['text'].'<span class="change_product">'.__('Set Product','wplms-front-end').'</span></h3>';
				}
				?>	

				<div id="change_product" class="field_wrapper row" <?php if(empty($field['value'])){echo 'style="display:block;"';}else{echo 'style="display:none;"';} ?>>
				<a class="hide_parent"><i class="icon-x"></i></a>		
					<div class="col-md-6">
						<a class="more"><i class="icon-users"></i> <?php _e('Select existing product','wplms-front-end' ); ?></a>
						<div class="select_product_form">
							<select class="selectcpt select2" data-cpt="product" data-placeholder="<?php _e('Search a product','wplms-front-end' ); ?>">
							</select>
							<a class="use_selected_product button"><?php _e('Set Product','wplms-front-end' ); ?></a>
						</div>
					</div>
					<div class="col-md-6">
						<a class="more"><i class="icon-user"></i> <?php _e('Create new product','wplms-front-end' ); ?></a>
						<div class="new_product_form">
							<?php
								
								foreach($product_fields as $product_field){
									echo '<div class="vibe_'.$product_field['id'].' '.(empty($product_field['style'])?'':$product_field['style']).'">';
										unset($product_field['style']);
										self::generate_fields($product_field);
									echo '</div>';	
								}
							?>
							<a class="button small" id="create_new_product"><?php _e('Create Product','wplms-front-end' ); ?></a>
						</div>
					</div>
				</div>
				<?php if(!empty($field['value'])){ ?>
				<div id="edit_product" class="field_wrapper">
					<a class="hide_parent"><i class="icon-x"></i></a>
					<?php
						foreach($product_fields as $product_field){
							echo '<div class="vibe_'.$product_field['id'].' '.(empty($product_field['style'])?'':$product_field['style']).'">';
								unset($product_field['style']);
								if($product_field['id'] == 'vibe_subscription'){
									$product_field['id'] = 'vibe_subscription1';
								}
								self::generate_fields($product_field,$field['value']);
							echo '</div>';	
						}
					?>
					<input type="hidden" class="post_field" data-id="ID" value="<?php echo $field['value']; ?>" />
					<a class="button small" id="edit_course_product"><?php _e('Edit Product','wplms-front-end' ); ?></a>
				</div>
				<?php
				}
			break;
			case 'multiselect':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label><strong>'.$field['text'].'</strong>';
				echo '<select class="chosen post_field" data-id="'.$field['id'].'" style="width: 100%;" multiple >';
				if(!empty($field['options'])){
					foreach($field['options'] as $key=>$option){
						if(!empty($option)){
							if(empty($field['value'])) $field['value'] =array();
							echo '<option value="'.$option['value'].'" '.(in_array($option['value'],$field['value'])?'selected="selected"':'').'>'.$option['label'].'</option>';
						}
					}
				}
				echo '</select></div>';
			break;
			case 'selectmulticpt':
				echo '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>'.(!empty($field['text'])?'<strong>'.$field['text'].'</strong>':'');
				if(empty($field['post_status'])){
					$field['post_status'] = 'publish';
				}else{
					$field['post_status'] = implode(',',$field['post_status']);
				}
				echo (!empty($field['text'])?'<div class="right" style="width:240px">':'').'<select class="select2 selectcpt post_field" data-id="'.$field['id'].'" data-type="'.$field['type'].'" data-status="'.$field['post_status'].'" style="width: 100%;" data-placeholder="'.$field['placeholder'].'" data-cpt="'.(($field['cpt'])?$field['cpt']:(($field['post_type'])?$field['post_type']:'')).'" multiple>';
				if(!empty($field['value']) && $field['value'] != $field['default']){
					if(is_array($field['value'])){
						foreach($field['value'] as $value){
							echo '<option value="'.$value.'" selected="selected">'.get_the_title($value).'</option>';
						}
					}
				}
				echo '</select></div>';
			break;
			case 'course_live':

            if(!empty($_GET['action'])){
                ?>
                    <strong class="heading"><?php _e('Modify Course','wplms-front-end' ); ?> <span><a href="<?php echo get_permalink($_GET['action']); ?>"><?php _e('Back to course','wplms-front-end' ); ?></a></span></strong>
                    <?php
                        if(get_post_type($_GET['action']) == 'course'){
                            $post_status = get_post_status($_GET['action']);   
                            if($post_status == 'publish'){
                            	$new_course_status = vibe_get_option('new_course_status');
                            	if(isset($new_course_status) && $new_course_status == 'publish')
	                                echo '<a id="offline_course" class="button big hero">'.__('TAKE OFFLINE','wplms-front-end' ).'</a>'; 	
	                            else
	                                echo '<a id="publish_course" class="button big hero">'.__('SEND FOR APPROVAL','wplms-front-end' ).'</a>';
                            	
                            }else{
                        		$new_course_status = vibe_get_option('new_course_status');

                        		$instructor_premium_course_check = apply_filters('instructor_premium_course_form',1);
				            	if($instructor_premium_course_check){
				                ?>
				                    <strong class="heading"><?php _e('Go Live','wplms-front-end' ); ?></strong>
				                    <?php   $new_course_status = vibe_get_option('new_course_status');
				                    if(current_user_can('manage_options') || (isset($new_course_status) && $new_course_status == 'publish'))
				                        echo '<a id="publish_course" class="button big hero">'.__('GO LIVE','wplms-front-end' ).'</a>';
				                    else
				                        echo '<a id="publish_course" class="button big hero">'.__('SEND FOR APPROVAL','wplms-front-end' ).'</a>';
				            	}
	                        }

                            $delete_enable = apply_filters('wplms_front_end_course_delete',0);
                            if($delete_enable){
                                echo '<hr /><a id="delete_course" class="button big full primary">'.__('DELETE COURSE','wplms-front-end' ).'</a>';
                                echo '<a class="link right showhide_indetails">'.__('SHOW OPTIONS','wplms-front-end' ).'</a>';
                                $delete_options = apply_filters('wplms_course_delete_options',array(
                                    'unit'=>array(
                                        'label'=>__('Delete Units','wplms-front-end' ),
                                        'post_type'=>'unit',
                                        'post_meta'=>'vibe_course_curriculum'
                                        ),
                                    'quiz'=>array(
                                        'label'=>__('Delete Quizzes','wplms-front-end' ),
                                        'post_type'=>'quiz',
                                        'post_meta'=>'vibe_course_curriculum'
                                        ),
                                    'assignment'=>array(
                                        'label'=>__('Delete Assignments','wplms-front-end' ),
                                        'post_type'=>'wplms-assignment',
                                        'post_meta'=>'vibe_assignment_course'
                                        ),
                                    'product'=>array(
                                        'label'=>__('Delete Product','wplms-front-end' ),
                                        'post_type'=>'product',
                                        'post_meta'=>'vibe_product'
                                        ),
                                    ));

                                echo '<div class="in_details"><ul class="clear">';
                                foreach($delete_options as $option){
                                    echo '<li><label>'.$option['label'].'</label><input class="delete_field right" type="checkbox" value="1" data-posttype="'.$option['post_type'].'"  data-meta="'.$option['post_meta'].'" /></li>';
                                }
                                echo '</ul></div>';
                            }
                        }else{
                    ?>
                    <?php   
                            echo '<p class="message">'.__('Course not set','wplms-front-end' ).'</p>';
                        }
                    ?>
                <?php
            }else{
            	$instructor_premium_course_check = apply_filters('instructor_premium_course_form',1);
            	if($instructor_premium_course_check){
                ?>
                    <strong class="heading"><?php _e('Go Live','wplms-front-end' ); ?></strong>
                    <?php   $new_course_status = vibe_get_option('new_course_status');
                    if(current_user_can('manage_options') || (isset($new_course_status) && $new_course_status == 'publish'))
                        echo '<a id="publish_course" class="button big hero">'.__('GO LIVE','wplms-front-end' ).'</a>';
                    else
                        echo '<a id="publish_course" class="button big hero">'.__('SEND FOR APPROVAL','wplms-front-end' ).'</a>';
            	}
            }

			break;
			case 'button':
				echo '<br class="clear" /><hr />';
				echo '<a id="'.$field['id'].'" class="button hero">'.$field['label'].'</a>';
			break;
			case 'small_button':
				echo '<a id="'.$field['id'].'" '.(empty($field['href'])?'':'href="'.$field['href'].'"').' '.(empty($field['data-id'])?'':'data-id="'.$field['data-id'].'"').' target="_blank" class="button">'.$field['label'].'</a>';
			break;
			case 'dynamic_taxonomy':
				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>'.(empty($field['text'])?'':'<strong>'.$field['text'].'</strong>');
				echo '<select class="select2 post_field" data-id="'.$field['id'].'" data-type="array" style="width: 100%;" multiple>';
				$terms = get_terms( $field['taxonomy'], array('hide_empty' => false,'orderby'=>'name','order'=>'ASC','fields' => 'id=>name') );
				if(empty($field['value'])){
					$field['value'] = array();
				}
				if(!empty($terms) && is_array($terms)){
					foreach ($terms as $key=>$term ){
						echo '<option value="' . $key . '" '.(in_array($key,$field['value'])?'SELECTED':'').'>' . $term . '</option>';
					}
				}
				echo '</select>';
			break; 
			case 'dynamic_quiz_questions':
				echo '<label>'.$field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>'.(empty($field['text'])?'':'<strong>'.$field['text'].'</strong>');
				$terms = get_terms($field['taxonomy']);
				$terms_array  = array();
				if(!empty($terms)){
					foreach($terms as $term){
						$terms_array[$term->term_id] = array('id'=>$term->term_id,'name'=>$term->name,'count'=>$term->count);
					}
				}
				echo '<br class="clear"><a class="button small primary add_dynamic_question_tag">'.__('ADD DYNAMIC QUESTION SECTION TAGS','wplms-front-end').'</a>';
				?>

				<div class="dynamic_quiz_tags_headings"><strong><?php _ex('Question Tags','dynamic quiz settings','wplms-front-end');?></strong><span><?php _ex('Number','dynamic quiz settings','wplms-front-end');?></span><span><?php _ex('per Marks','dynamic quiz settings','wplms-front-end');?></span></div>
				<ul id="<?php echo $field['id']; ?>" data-id="<?php echo $field['id']; ?>" class="repeatable post_field">
				<?php
				if(!empty($field['value'])){
						$tags = $numbers = array();
						if(!isset($field['value']['tags'])){
							global $post;
							$tags[0] = $field['value'];
							$numbers[0] = get_post_meta(get_the_ID(),'vibe_quiz_number_questions',true);
							$marks[0] = get_post_meta(get_the_ID(),'vibe_quiz_marks_per_question',true);
						}else{
							$tags = $field['value']['tags'];
							$numbers = $field['value']['numbers'];
							if(isset($field['value']['marks'])){
								$marks = $field['value']['marks'];	
							}else{
								$marks[0] = get_post_meta(get_the_ID(),'vibe_quiz_marks_per_question',true);
							}
							
						}

						foreach($tags as $i=>$tag){
							$tags_string = '';
							if(!is_array($tag)){
								$tag = unserialize($tag);
							}
							if(is_array($tag)){
								foreach($tag as $t){
									$tags_string.= '<span>'.$terms_array[$t]['name'].' ('.$terms_array[$t]['count'].')<input type="hidden" value="'.$terms_array[$t]['id'].'"></span> ,';	
								}
							}else{
								$tags_string.= '<span>'.$terms_array[$tag]['name'].' ('.$terms_array[$tag]['count'].')<input type="hidden" value="'.$terms_array[$tag]['id'].'"></span>';	
							}
							if(!isset($marks[$i]) && isset($marks[0])){$marks[$i]=$marks[0];}
							echo '<li><span class="dashicons dashicons-sort"></span> &nbsp;<strong style="display:inline-block;">'.$tags_string.'</strong> <input type="text" class="count" value="'.$numbers[$i].'"> &nbsp;  <input type="text" class="marks" value="'.(isset($marks[$i])?$marks[$i]:0).'"><a class="remove_tag" title="'.__('Remove','wplms-front-end' ).'"><span class="dashicons dashicons-no-alt"></span></a></li>';
						}
				}
				?>
				</ul>
				<div class="hidden_block hide">
					<span class="dashicons dashicons-sort"></span>
					<select data-id="<?php echo $field['id'].'[tags]'; ?>" multiple>
						<?php
							if(!empty($terms_array)){
								foreach($terms_array as $term){
									echo '<option value="'.$term['id'].'">'.$term['name'].' ('.$term['count'].') </option>';
								}
							}
						?>
					</select>	
					<input type="text" class="count" data-id="<?php echo $field['id'].'[numbers]'; ?>" value="0" />	
					<input type="text" class="marks" data-id="<?php echo $field['id'].'[marks]'; ?>" value="0" />	
					<a class="remove_tag" title="<?php _e('Remove','wplms-front-end' ); ?>"><span class="dashicons dashicons-no-alt"></span></a>				
				</div>
				<strong style="font-size:16px;margin:30px 30px 0 0;display:inline-block;"><span><?php _ex('Total Question Count','Total question count in quiz','wplms-front-end'); ?></span> &nbsp; <span id="total_question_count"><?php echo (isset($numbers)?array_sum($numbers):0); ?></span></strong>
				<strong style="font-size:16px;"><span><?php _ex('Total Marks','total marks label','wplms-front-end'); ?></span> &nbsp; <span id="total_question_marks"><?php 
					if(!empty($numbers)){
						foreach($numbers as $k=>$n){
							$total_marks = $total_marks + intval($n) * intval($marks[$k]);
						} 
					} 
					echo (isset($total_marks)?$total_marks:0);?></span></strong>
				<?php
			break;
			case 'repeatable_count':
				?>
	                <a class="button small primary add_repeatable_count_option"><?php _e('ADD OPTION','wplms-front-end'); ?></a>
	                <ul id="<?php echo $field['id']; ?>" data-id="<?php echo $field['id']; ?>" class="repeatable post_field">
	                    <?php

	                        if(isset($field['value']) && is_array($field['value'])){
	                            foreach($field['value'] as $key => $option){
	                                echo '<li><span>'.($key+1).'</span>
	                                			<input type="text" class="option very_large_box" value="'.$option.'" />
				                                <a class="rem"><i class="icon-x"></i></a>
	        	                        </li>';
	                            }
	                        }
	                    ?>
	                </ul>
	                <ul class="hidden">
	                    <li><span></span><input type="text" class="option very_large_box" /><a class="rem"><i class="icon-x"></i></a></li>
	                </ul>
				<?php
			break;
			case 'assignment':
				?>
				<?php
					if(!empty($field['value'])){
						foreach($field['value'] as $k=>$assignment_id){
							?>
							<div class="list-group-item assignment_block">
								<span class="dashicons dashicons-sort"></span>
								<strong class="title" data-id="<?php echo $assignment_id; ?>"><?php echo get_the_title($assignment_id); ?></strong>
								<input type="hidden" class="assignment_id" value="<?php echo $assignment_id; ?>" />
								<?php echo '<ul class="data_links">
                                    <li><a class="edit_sub" title="'.__('Edit','wplms-front-end' ).'"><span class="dashicons dashicons-edit"></span></a></li>
                                    <li><a class="preview_sub" title="'.__('Preview','wplms-front-end' ).'" target="_blank"><span class="dashicons dashicons-visibility"></span></a></li>
                                    <li><a class="remove_sub" title="'.__('Remove','wplms-front-end' ).'"><span class="dashicons dashicons-no-alt"></span></a></li>
                                    <li><a class="delete_sub" title="'.__('Delete','wplms-front-end' ).'"><span class="dashicons dashicons-trash"></span></a></li>
                                </ul>' ?>									
							</div>	
							<?php
						}
					}
				?>
				<div class="list-group-item hidden_block hide">
					<span class="dashicons dashicons-sort"></span>
					<strong class="title" data-id=""></strong>
					<input type="hidden" class="assignment_id" value="" />
					<?php echo '<ul class="data_links">
                                    <li><a class="edit_sub" title="'.__('Edit','wplms-front-end' ).'"><span class="dashicons dashicons-edit"></span></a></li>
                                    <li><a class="preview_sub" title="'.__('Preview','wplms-front-end' ).'" target="_blank"><span class="dashicons dashicons-visibility"></span></a></li>
                                    <li><a class="remove_sub" title="'.__('Remove','wplms-front-end' ).'"><span class="dashicons dashicons-no-alt"></span></a></li>
                                    <li><a class="delete_sub" title="'.__('Delete','wplms-front-end' ).'"><span class="dashicons dashicons-trash"></span></a></li>
                                </ul>' ?>							
				</div>
				<div class="list-group-item">
					<div class="add_cpt">
						<div class="col-md-6">
							<a class="more"><i class="icon-users"></i> <?php _e('Select existing assignment','wplms-front-end' ); ?></a>
							<div class="select_existing_cpt">
								<select class="selectcpt select2" data-cpt="wplms-assignment" data-placeholder="<?php _e('Search an assignment','wplms-front-end' ); ?>">
								</select>
								<a class="use_selected_assignment button"><?php _e('Set Assignment','wplms-front-end' ); ?></a>
							</div>
						</div>
						<div class="col-md-6">
							<a class="more"><i class="icon-user"></i> <?php _e('Create new Assignment','wplms-front-end' ); ?></a>
							<div class="new_cpt">
								<input type="text" class="form_field" id="vibe_assignment_title" name="name" placeholder="<?php _e('Reference title','wplms-front-end' ); ?>">
								<a class="button small" id="create_new_assignment"><?php _e('Create Assignment','wplms-front-end' ); ?></a>
							</div>
						</div>
					</div>
                </div>
				<?php			               	
				?>
				<?php
			break;
			case 'quiz_questions':
				?>
				<?php
					if(!empty($field['value']) && !empty($field['value']['ques'])){
						foreach($field['value']['ques'] as $k=>$question_id){
							$question_id = intval($question_id);
							?>
							<div class="list-group-item question_block">
								<span class="dashicons dashicons-sort"></span>
								<strong class="title" data-id="<?php echo $question_id; ?>"><?php echo get_the_title($question_id); ?></strong>
								<input type="hidden" class="question_id" value="<?php echo $question_id; ?>" />
								<?php echo '<ul class="data_links">
                                    <li><a class="edit_sub" title="'.__('Edit','wplms-front-end' ).'"><span class="dashicons dashicons-edit"></span></a></li>
                                    <li><a class="preview_sub" title="'.__('Preview','wplms-front-end' ).'" target="_blank"><span class="dashicons dashicons-visibility"></span></a></li>
                                    <li><a class="remove_sub" title="'.__('Remove','wplms-front-end' ).'"><span class="dashicons dashicons-no-alt"></span></a></li>
                                    <li><a class="delete_sub" title="'.__('Delete','wplms-front-end' ).'"><span class="dashicons dashicons-trash"></span></a></li>
                                </ul>' ?>
								<div class="right">
									<input type="number" class="small_box question_marks" value="<?php echo ((isset($field['value']) && is_array($field['value']) && isset($field['value']) && isset($field['value']['marks']) && isset($field['value']['marks'][$k]))?$field['value']['marks'][$k]:0); ?>" />
									<span><?php _e('Marks','wplms-front-end'); ?></span>
								</div>									
							</div>	
							<?php
						}
					}
				?>
				<div class="list-group-item hidden_block hide">
					<span class="dashicons dashicons-sort"></span>
					<strong class="title" data-id=""></strong>
					<input type="hidden" class="question_id" value="" />
					<?php echo '<ul class="data_links">
                        <li><a class="edit_sub" title="'.__('Edit','wplms-front-end' ).'"><span class="dashicons dashicons-edit"></span></a></li>
                        <li><a class="preview_sub" title="'.__('Preview','wplms-front-end' ).'" target="_blank"><span class="dashicons dashicons-visibility"></span></a></li>
                        <li><a class="remove_sub" title="'.__('Remove','wplms-front-end' ).'"><span class="dashicons dashicons-no-alt"></span></a></li>
                        <li><a class="delete_sub" title="'.__('Delete','wplms-front-end' ).'"><span class="dashicons dashicons-trash"></span></a></li>
                    </ul>' ?>
					<div class="right">
						<input type="number" class="small_box question_marks" value="0" />
						<span><?php _e('Marks','wplms-front-end'); ?></span>
					</div>									
				</div>
				<div class="list-group-item">
					<div class="add_cpt">
						<div class="col-md-6">
							<a class="more"><i class="icon-users"></i> <?php _e('Select existing question','wplms-front-end' ); ?></a>
							<div class="select_existing_cpt">
								<select class="selectcpt select2" data-cpt="question" data-placeholder="<?php _e('Search a question','wplms-front-end' ); ?>">
								</select>
								<a class="use_selected_question button"><?php _e('Set Question','wplms-front-end' ); ?></a>
							</div>
						</div>
						<div class="col-md-6">
							<a class="more"><i class="icon-user"></i> <?php _e('Create new Question','wplms-front-end' ); ?></a>
							<div class="new_cpt">
								<input type="text" class="form_field" id="vibe_question_title" name="name" placeholder="<?php _e('Reference title','wplms-front-end' ); ?>">
								<?php
									$field = array(
											'label'=> __('Question Tags','wplms-front-end' ),
											'type'=> 'taxonomy',
											'taxonomy'=> 'question-tag',
											'from'=>'taxonomy',
											'value_type'=>'single',
											'style'=>'',
											'id' => 'question-tag',
											'default'=> __('Select a Tag','wplms-front-end' ),
										);
									self::generate_fields($field);
								?>
								<?php
								$question_types = apply_filters('wplms_question_types',array(
						              array( 'label' =>__('True or False','wplms-front-end'),'value'=>'truefalse'),  
						              array( 'label' =>__('Multiple Choice','wplms-front-end'),'value'=>'single'),
						              array( 'label' =>__('Multiple Correct','wplms-front-end'),'value'=>'multiple'),
						              array( 'label' =>__('Sort Answers','wplms-front-end'),'value'=>'sort'),
						              array( 'label' =>__('Match Answers','wplms-front-end'),'value'=>'match'),
						              array( 'label' =>__('Fill in the Blank','wplms-front-end'),'value'=>'fillblank'),
						              array( 'label' =>__('Dropdown Select','wplms-front-end'),'value'=>'select'),
						              array( 'label' =>__('Small Text','wplms-front-end'),'value'=>'smalltext'),
						              array( 'label' =>__('Large Text','wplms-front-end'),'value'=>'largetext'),
						              array( 'label' =>__('Survey Type','wplms-front-end'),'value'=>'survey')
						            ));
								?>
								<select class="chosen" id="vibe_question_template">
								<?php
									foreach($question_types as $type){
										echo '<option value="'.$type['value'].'">'.$type['label'].'</option>';
									}
								?>
								</select>
								<a class="button small" id="create_new_question"><?php _e('Create question','wplms-front-end' ); ?></a>
							</div>
						</div>
					</div>
                </div>
				<?php			               	
				?>
				<?php
			break;
			case 'curriculum':
			?>
                <ul class="curriculum post_field">
                <?php 
                if(isset($_GET['action'])){
                    $curriculum = vibe_sanitize(get_post_meta($_GET['action'],'vibe_course_curriculum',false));
                   
                    if(isset($curriculum) && is_array($curriculum)){
                        foreach($curriculum as $kid){
                            if(is_numeric($kid)){
                                if(get_post_type($kid) == 'unit'){
                                    echo '<li><strong class="title" data-id="'.$kid.'"><i class="icon-file"></i> '.get_the_title($kid).'</strong>'.apply_filters('wplms_front_end_curriculum_edit_sub','
                                            <ul class="data_links">
                                                <li><a class="edit" title="'.__('Edit Unit','wplms-front-end' ).'"><span class="dashicons dashicons-edit"></span></a></li>
                                                <li><a class="preview" title="'.__('Preview','wplms-front-end' ).'" target="_blank"><span class="dashicons dashicons-visibility"></span></a></li>
                                                <li><a class="remove" title="'.__('Remove','wplms-front-end' ).'"><span class="dashicons dashicons-no-alt"></span></a></li>
                                                <li><a class="delete" title="'.__('Delete','wplms-front-end' ).'"><span class="dashicons dashicons-trash"></span></a></li>
                                            </ul>',$kid,'unit').'
                                        </li>';
                                }else{
                                    echo '<li><strong class="title" data-id="'.$kid.'"><i class="icon-task"></i> '.get_the_title($kid).'</strong>'.apply_filters('wplms_front_end_curriculum_edit_sub','
                                            <ul class="data_links">
                                                <li><a class="edit" title="'.__('Edit Quiz','wplms-front-end' ).'"><span class="dashicons dashicons-edit"></span></a></li>
                                                <li><a class="preview" title="'.__('Preview','wplms-front-end' ).'" target="_blank"><span class="dashicons dashicons-visibility"></span></a></li>
                                                <li><a class="remove" title="'.__('Remove','wplms-front-end' ).'"><span class="dashicons dashicons-no-alt"></span></a></li>
                                                <li><a class="delete" title="'.__('Delete','wplms-front-end' ).'"><span class="dashicons dashicons-trash"></span></a></li>
                                            </ul>',$kid,'quiz').'
                                          </li>';
                                }
                            }else{
                                echo '<li class="new_section"><strong>'.$kid.'</strong>
		                                <ul class="data_links">
		                                	<li>
		                                		<a class="remove" title="'.__('Remove','wplms-front-end' ).'"><span class="dashicons dashicons-no-alt"></span></a>
		                                	</li>
		                                </ul>
		                            </li>';
                            }
                        }
                    }
                }
                ?>
                </ul>
                <ul class="hide">
					<li><strong class="title" data-id=""><i class="icon-task"></i> <span></span></strong>
                        <ul class="data_links">
                            <li><a class="edit" title="<?php _e('Edit','wplms-front-end' ); ?>"><span class="dashicons dashicons-edit"></span></a></li>
                            <li><a class="preview" title="<?php _e('Preview','wplms-front-end' ); ?>" target="_blank"><span class="dashicons dashicons-visibility"></span></a></li>
                            <li><a class="remove" title="<?php _e('Remove','wplms-front-end' ); ?>"><span class="dashicons dashicons-no-alt"></span></a></li>
                            <li><a class="delete" title="<?php _e('Delete','wplms-front-end' ); ?>"><span class="dashicons dashicons-trash"></span></a></li>
                        </ul>
                    </li>
				</ul>
                <ul id="hidden_base">
                    <li class="new_section">
                        <input type="text" class="section " placeholder="<?php _e('Enter Section Title','wplms-front-end'); ?>" />
                        <a class="rem"><i class="icon-x"></i></a>
                    </li>
                    <li class="new_unit">
                    	<div class="add_cpt">
							<div class="col-md-6">
								<a class="more"><i class="icon-users"></i> <?php _e('Select existing unit','wplms-front-end' ); ?></a>
								<div class="select_existing_cpt">
									<select data-cpt="unit" data-placeholder="<?php _e('Search a unit','wplms-front-end' ); ?>">
									</select>
									<a class="use_selected_curriculum button"><?php _e('Set Unit','wplms-front-end' ); ?></a>
								</div>
							</div>
							<div class="col-md-6">
								<a class="more"><i class="icon-user"></i> <?php _e('Create new unit','wplms-front-end' ); ?></a>
								<div class="new_cpt">
									<input type="text" class="form_field vibe_curriculum_title" name="name" placeholder="<?php _e('Unit title','wplms-front-end' ); ?>">
									<input type="hidden" class="vibe_cpt" value="unit" />
									
									<a class="button small create_new_curriculum"><?php _e('Create Unit','wplms-front-end' ); ?></a>
								</div>
							</div>
						</div>	
						<a class="rem"><i class="icon-x"></i></a>
                    </li>
                    <li class="new_quiz">
                    	<div class="add_cpt">
							<div class="col-md-6">
								<a class="more"><i class="icon-users"></i> <?php _e('Select existing quiz','wplms-front-end' ); ?></a>
								<div class="select_existing_cpt">
									<select data-cpt="quiz" data-placeholder="<?php _e('Search a quiz','wplms-front-end' ); ?>">
									</select>
									<a class="use_selected_curriculum button"><?php _e('Set Quiz','wplms-front-end' ); ?></a>
								</div>
							</div>
							<div class="col-md-6">
								<a class="more"><i class="icon-user"></i> <?php _e('Create new quiz','wplms-front-end' ); ?></a>
								<div class="new_cpt">
									<input type="text" class="form_field vibe_curriculum_title" name="name" placeholder="<?php _e('Quiz title','wplms-front-end' ); ?>">
									<input type="hidden" class="vibe_cpt" value="quiz" />
									<a class="button small create_new_curriculum"><?php _e('Create Quiz','wplms-front-end' ); ?></a>
								</div>
							</div>
						</div>
						<a class="rem"><i class="icon-x"></i></a> 
                    </li>
                </ul>
                <div class="add_element">
                	<?php
                		if(!empty($field['buttons'])){
                			foreach($field['buttons'] as $key => $label){
								echo '<a id="'.$key.'" class="button primary">'.$label.'</a>';
							}
                		}
                	?>
                </div>
                <?php do_action('wplms_course_curriculum_front_end_generator',$_GET['action']);?>
                <a id="save_course_curriculum_button" class="button hero"><?php _e('SAVE CURRICULUM','wplms-front-end' ); ?></a>
			<?php
			break;
			case 'multiattachments':

                if(!empty($field['value'])){
					$attachments = $field['value'];
				}else{
					global $wpdb;
					$connected_attachments = array();
					if(!empty($post->ID)){
						$connected_attachments = $wpdb->get_results("SELECT ID from {$wpdb->posts} WHERE post_type = 'attachment' AND post_parent = $post->ID"); 
					}
					if(!empty($connected_attachments)){
						foreach($connected_attachments as $att){
							$attachments[] = $att->ID;
						}
					}
				}
				echo   '<div class="field_wrapper '.(isset($field['style'])?$field['style']:'').'">';
				echo   '<label>'. $field['label'].(empty($field['desc'])?'':'<a class="tip" title="'.$field['desc'].'"><i class="icon-question"></i></a>').'</label>';
				echo   (!empty($field['text'])?'<strong>'.$field['text'].'</strong>':''); 

				echo '<ul class="' . $field['id'] . '_attachments attachment_list post_field repeatable" data-id="' . $field['id'] . '">';
				if(!empty($attachments)){
					
					foreach($attachments as $attachment_id){
						echo '<li><i class="sort dashicons dashicons-move"></i>';
						echo '<strong>'.get_the_title($attachment_id).'</strong>';
						echo '<input type="hidden" name="' . $field['id'] . '[]" value="'.$attachment_id.'">';
						echo '<i class="remove_attachment dashicons dashicons-no"></i>';
						echo '</li>';
					}
				}
				echo '</ul>';
				?>
				<a class="<?php echo $field['id']; ?>_add_attachments button" data-add="<?php echo $field['id']; ?>_attachments"><?php _e( 'Add Attachments', 'wplms-front-end' ); ?></a>
				
				<script type="text/javascript">
					jQuery(document).ready(function($){

						// Uploading files
						var <?php echo $field['id']; ?>attachment_frame;
						var <?php echo $field['id']; ?>media = $('.<?php echo $field['id']; ?>_attachments');

						jQuery('.<?php echo $field['id']; ?>_add_attachments').on( 'click',function( event ) {

							var $el = $(this);
							var attachment_ids = $("input[name=\'<?php echo $field['id']; ?>\']").val();

							event.preventDefault();

							// If the media frame already exists, reopen it.
							if ( <?php echo $field['id']; ?>attachment_frame ) {
								<?php echo $field['id']; ?>attachment_frame.open();
								return;
							}

							// Create the media frame.
							<?php echo $field['id']; ?>attachment_frame = wp.media.frames.downloadable_file = wp.media({
								// Set the title of the modal.
								title: '<?php _e( 'Add Attachments', 'wplms-front-end' ); ?>',
								button: {
									text: '<?php _e( 'Add Attachment', 'wplms-front-end' ); ?>',
								},
								multiple: true
							});

							// When an image is selected, run a callback.
							<?php echo $field['id']; ?>attachment_frame.on( 'select', function() {

								var selection = <?php echo $field['id']; ?>attachment_frame.state().get('selection');

								selection.map( function( attachment ) {

									attachment = attachment.toJSON();
									console.log(attachment.title);

									if ( attachment.id ) {
										
										<?php echo $field['id']; ?>media.append('\
											<li><i class="sort dashicons dashicons-move"></i>\
												<strong>'+attachment.title+'</strong>\
												<input type="hidden" name="<?php echo $field['id']; ?>[]" value="' + attachment.id + '"/>\
												<i class="remove_attachment dashicons dashicons-no"></i>\
											</li>');
									}

								} );

							});

							// Finally, open the modal.
							<?php echo $field['id']; ?>attachment_frame.open();
						});

						// Image ordering
						<?php echo $field['id']; ?>media.sortable({
							items: 'li',
							handle:'.sort',
							cursor: 'move',
							scrollSensitivity:40,
							opacity: 0.65,
							start:function(event,ui){
								ui.item.css('background-color','#f6f6f6');
							},
							stop:function(event,ui){
								ui.item.removeAttr('style');
							},
						});

						// Remove images
						$('.attachment_list .remove_attachment').on( 'click', function() {
							$(this).parent('li').remove();
						} );

					});
				</script>
			<?php	
				echo (isset($field['extra'])?$field['extra']:'');
				echo   '</div>';
			break;
			case 'save_course_creation_template':
				$save_cc_tabs = $this->tabs();
				wp_localize_script( 'wplms-front-end-js', 'course_creation_template', $save_cc_tabs );
				echo '<a id="'.$field['id'].'" class="button big hero">'.$field['label'].'</a>';
				
			break;
			default:
				do_action('wplms_front_end_generate_fields_default',$field,$_GET['action']);
			break;
		}

	}

	function active_components_check($tabs){

		if(!function_exists('bp_is_active') || !bp_is_active('groups')){
			unset($tabs['course_components']['fields'][0]);
		}
		if ( !in_array( 'bbpress/bbpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			unset($tabs['course_components']['fields'][1]);
		}
		if(count($tabs['course_components']['fields']) <2){
			unset($tabs['course_components']);
		}
		$level = vibe_get_option('level');
		if(!empty($level)){
			$levels[] = array(
							'label'=> __('Course Level','wplms-front-end' ),
							'type'=> 'taxonomy',
							'taxonomy'=> 'level',
							'from'=>'taxonomy',
							'value_type'=>'single',
							'style'=>'col-md-12',
							'id' => 'level',
							'default'=> __('Select a Course Level','wplms-front-end' ),
							);
			array_splice( $tabs['create_course']['fields'],2, 0, $levels );
		}
		$location = vibe_get_option('location');
		if(!empty($location)){
			$locations[] = array(
							'label'=> __('Course Location','wplms-front-end' ),
							'type'=> 'taxonomy',
							'taxonomy'=> 'location',
							'from'=>'taxonomy',
							'value_type'=>'single',
							'style'=>'col-md-12',
							'id' => 'location',
							'default'=> __('Select a Course location','wplms-front-end' ),
							);
			array_splice( $tabs['create_course']['fields'],2, 0, $locations );
		}
		return $tabs;
	}

	function get_group_name($group_id){
		global $wpdb,$bp;
		$name = $wpdb->get_var($wpdb->prepare("SELECT name from {$bp->groups->table_name} WHERE id = %d",$group_id));
		return $name;
	}
	function get_group_permalink($group_id){
		global $wpdb,$bp;
		$pages = get_option('bp-pages');
		$link = get_permalink($pages['groups']);
		$slug = $wpdb->get_var($wpdb->prepare("SELECT slug from {$bp->groups->table_name} WHERE id = %d",$group_id));
		return $link.$slug;
	}	
}


add_shortcode('edit_course','wplms_edit_course_shortcode');

function wplms_edit_course_shortcode(){
	
	$fields = WPLMS_Front_End_Fields::init();

	if(isset($_GET['action']) && is_numeric($_GET['action']) && get_post_type($_GET['action']) == 'course'){
		$fields->course_id = $_GET['action'];
	}else{
		global $post;
		if($post->post_type == 'course'){
			$fields->course_id = $post->ID;	
		}
	}
	echo '<div id="create_course_wrapper">';
	do_action('wplms_before_create_course_header');
	$fields->create_tabs();
	$fields->course_tab_module();
	echo '</div>';

}
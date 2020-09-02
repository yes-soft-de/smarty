<?php
if ( !defined( 'ABSPATH' ) ) exit;

function vibe_meta_box_arrays($metabox){ // References added to Pick labels for Import/Export

	$prefix = 'vibe_';
	$sidebars=$GLOBALS['wp_registered_sidebars'];
	$sidebararray=array();
	foreach($sidebars as $sidebar){
		if(!in_array($sidebar['id'],array('student_sidebar','instructor_sidebar')))
	    	$sidebararray[]= array('label'=>$sidebar['name'],'value'=>$sidebar['id']);
	}
	$id = '';
	global $post;
	if(is_object($post)){
		$id = $post->ID;
	}
	$course_duration_parameter = apply_filters('vibe_course_duration_parameter',86400,$id);
	$drip_duration_parameter = apply_filters('vibe_drip_duration_parameter',86400,$id);
	$unit_duration_parameter = apply_filters('vibe_unit_duration_parameter',60,$id);
	$quiz_duration_parameter = apply_filters('vibe_quiz_duration_parameter',60,$id);
	$product_duration_parameter = apply_filters('vibe_product_duration_parameter',86400,$id);
	$assignment_duration_parameter = apply_filters('vibe_assignment_duration_parameter',86400,$id);

	switch($metabox){
		case 'post':
			$metabox_settings=array(
		 
		
		 $prefix.'subtitle'=>array( // Single checkbox
			'label'	=> __('Post Sub-Title','vibe-customtypes'), // <label>
			'desc'	=> __('Post Sub- Title.','vibe-customtypes'), // description
			'id'	=> $prefix.'subtitle', // field id and name
			'type'	=> 'textarea', // type of field
	        'std'   => ''
	                ), 

	     $prefix.'template'=>array( // Single checkbox
			'label'	=> __('Post Template','vibe-customtypes'), // <label>
			'desc'	=> __('Select a post template for showing content.','vibe-customtypes'), // description
			'id'	=> $prefix.'template', // field id and name
			'type'	=> 'select', // type of field
	        'options' => array(
	                    1=>array('label'=>__('Content on Left','vibe-customtypes'),'value'=>''),
	                    2=>array('label'=>__('Content on Right','vibe-customtypes'),'value'=>'right'),
	                    3=>array('label'=>__('Full Width','vibe-customtypes'),'value'=>'full'),
	        ),
	        'std'   => ''
		),
	    $prefix.'sidebar' => array( // Single checkbox
			'label'	=> __('Sidebar','vibe-customtypes'), // <label>
			'desc'	=> __('Select a Sidebar | Default : mainsidebar','vibe-customtypes'), // description
			'id'	=> $prefix.'sidebar', // field id and name
			'type'	=> 'select',
	                'options' => $sidebararray
	                ),
	    $prefix.'title'=>array( // Single checkbox
			'label'	=> __('Show Page Title','vibe-customtypes'), // <label>
			'desc'	=> __('Show Page/Post Title.','vibe-customtypes'), // description
			'id'	=> $prefix.'title', // field id and name
			'type'	=> 'showhide', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	                'std'   => 'S'
	                ),
	    $prefix.'author'=>array( // Single checkbox
			'label'	=> __('Show Author Information','vibe-customtypes'), // <label>
			'desc'	=> __('Author information below post content.','vibe-customtypes'), // description
			'id'	=> $prefix.'author', // field id and name
			'type'	=> 'showhide', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	                'std'   => 'H'
		),    
	     
	    $prefix.'breadcrumbs'=>array( // Single checkbox
			'label'	=> __('Show Breadcrumbs','vibe-customtypes'), // <label>
			'desc'	=> __('Show breadcrumbs.','vibe-customtypes'), // description
			'id'	=> $prefix.'breadcrumbs', // field id and name
			'type'	=> 'showhide', // type of field
			'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	                'std'   => 'S'
	            ),
	    $prefix.'prev_next'=>array( // Single checkbox
			'label'	=> __('Show Prev/Next Arrows','vibe-customtypes'), // <label>
			'desc'	=> __('Show previous/next links on top below the Subheader.','vibe-customtypes'), // description
			'id'	=> $prefix.'prev_next', // field id and name
			'type'	=> 'showhide', // type of field
	         'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	                'std'   => 'H'
			),
		);
		break;
		case 'page':
			$metabox_settings=array(
			
	        $prefix.'title' => array( // Single checkbox
			'label'	=> __('Show Page Title','vibe-customtypes'), // <label>
			'desc'	=> __('Show Page/Post Title.','vibe-customtypes'), // description
			'id'	=> $prefix.'title', // field id and name
			'type'	=> 'showhide', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	                'std'   => 'S'
	                ),


	        $prefix.'subtitle' => array( // Single checkbox
			'label'	=> __('Page Sub-Title','vibe-customtypes'), // <label>
			'desc'	=> __('Page Sub- Title.','vibe-customtypes'), // description
			'id'	=> $prefix.'subtitle', // field id and name
			'type'	=> 'textarea', // type of field
	        'std'   => ''
	                ),

	        $prefix.'breadcrumbs' => array( // Single checkbox
			'label'	=> __('Show Breadcrumbs','vibe-customtypes'), // <label>
			'desc'	=> __('Show breadcrumbs.','vibe-customtypes'), // description
			'id'	=> $prefix.'breadcrumbs', // field id and name
			'type'	=> 'showhide', // type of field
	         'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	                'std'   => 'S'
	            ),
	    $prefix.'sidebar' => array( // Single checkbox
			'label'	=> __('Sidebar','vibe-customtypes'), // <label>
			'desc'	=> __('Select Sidebar | Sidebar : mainsidebar','vibe-customtypes'), // description
			'id'	=> $prefix.'sidebar', // field id and name
			'type'	=> 'select',
	                'options' => $sidebararray
	                ),
	    );
		break;
		case 'course':
			$metabox_settings = array(  
		$prefix.'sidebar'=>array( // Single checkbox
			'label'	=> __('Sidebar','vibe-customtypes'), // <label>
			'desc'	=> __('Select a Sidebar | Default : mainsidebar','vibe-customtypes'), // description
			'id'	=> $prefix.'sidebar', // field id and name
			'type'	=> 'select',
	        'options' => $sidebararray,
	        'std'=>'coursesidebar'
	        ),
		$prefix.'duration'=>array( // Text Input
			'label'	=> __('Total Duration of Course','vibe-customtypes'), // <label>
			'desc'	=> sprintf(__('Duration of Course (in %s)','vibe-customtypes'),calculate_duration_time($course_duration_parameter)), // description
			'id'	=> $prefix.'duration', // field id and name
			'type'	=> 'number', // type of field
			'std'	=> 10,
		),
		$prefix.'course_duration_parameter'=>array( // Text Input
			'label'	=> __('Course Duration parameter','vibe-customtypes'), // <label>
			'desc'	=> __('Duration parameter','vibe-customtypes'), // description
			'id'	=> $prefix.'course_duration_parameter', // field id and name
			'type'	=> 'duration', // type of field
			'std'	=>$course_duration_parameter
		),
		$prefix.'students'=>array( // Text Input
			'label'	=> __('Total number of Students in Course','vibe-customtypes'), // <label>
			'desc'	=> __('Total number of Students who have taken this Course.','vibe-customtypes'), // description
			'id'	=> $prefix.'students', // field id and name
			'type'	=> 'number', // type of field
			'std'	=> 0,
		),
		$prefix.'course_prev_unit_quiz_lock'=>array( // Text Input
			'label'	=> __('Unit Completion Lock','vibe-customtypes'), // <label>
			'desc'	=> __('Previous Units/Quiz must be Complete before next unit/quiz access','vibe-customtypes'), // description
			'id'	=> $prefix.'course_prev_unit_quiz_lock', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),
		$prefix.'course_offline'=>array( // Text Input
			'label'	=> __('Offline Course','vibe-customtypes'), // <label>
			'desc'	=> __('Make this an Offline Course','vibe-customtypes'), // description
			'id'	=> $prefix.'course_offline', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),
		$prefix.'course_unit_content'=>array( // Text Input
			'label'	=> __('Show Unit content in Curriculum','vibe-customtypes'), // <label>
			'desc'	=> __('Display units content in Course Curriculum, unit content visible in curriculum. ( Recommended for Offline Courses )','vibe-customtypes'), // description
			'id'	=> $prefix.'course_unit_content', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),

		$prefix.'course_button'=>array( // Text Input
			'label'	=> __('Hide Course Button after subscription','vibe-customtypes'), // <label>
			'desc'	=> __('Hide Start Course/Continue Course button after Course is subscribed by user. ( Recommended for Offline Courses )','vibe-customtypes'), // description
			'id'	=> $prefix.'course_button', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),

		$prefix.'course_progress'=>array( // Text Input
			'label'	=> __('Display Course Progress on Course home','vibe-customtypes'), // <label>
			'desc'	=> __('Display User Course progress on Course page. ( Recommended for Offline Courses )','vibe-customtypes'), // description
			'id'	=> $prefix.'course_progress', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),

		$prefix.'course_auto_progress'=>array( // Text Input
			'label'	=> __('Time based Course Progress ','vibe-customtypes'), // <label>
			'desc'	=> __('Automatically generate course progress based on duration (number of months/weeks/days/hours) passed in course.( Recommended for Offline Courses )','vibe-customtypes'), // description
			'id'	=> $prefix.'course_auto_progress', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),

		$prefix.'course_review'=>array( // Text Input
			'label'	=> __('Post Course Reviews from Course Home','vibe-customtypes'), // <label>
			'desc'	=> __('Allow subscribed users to post Course reviews from Course home page. ( Recommended for Offline Courses )','vibe-customtypes'), // description
			'id'	=> $prefix.'course_review', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),

		$prefix.'course_auto_eval'=>array( // Text Input
			'label'	=> __('Auto Evaluation','vibe-customtypes'), // <label>
			'desc'	=> __('Evalute Courses based on Quizzes scores available in Course (* Requires at least 1 Quiz in course)','vibe-customtypes'), // description
			'id'	=> $prefix.'course_auto_eval', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),
		$prefix.'start_date'=>array( // Text Input
			'label'	=> __('Course Start Date','vibe-customtypes'), // <label>
			'desc'	=> __('Date from which Course Begins','vibe-customtypes'), // description
			'id'	=> $prefix.'start_date', // field id and name
			'type'	=> 'date', // type of field
		),
		$prefix.'max_students'=>array( // Text Input
			'label'	=> __('Maximum Students in Course','vibe-customtypes'), // <label>
			'desc'	=> __('Maximum number of students who can pursue the course at a time.','vibe-customtypes'), // description
			'id'	=> $prefix.'max_students', // field id and name
			'type'	=> 'number', // type of field
		),
		$prefix.'course_badge'=>array( // Text Input
			'label'	=> __('Excellence Badge','vibe-customtypes'), // <label>
			'desc'	=> __('Upload badge image which Students receive upon course completion','vibe-customtypes'), // description
			'id'	=> $prefix.'course_badge', // field id and name
			'type'	=> 'image' // type of field
		),

		$prefix.'course_badge_percentage'=>array( // Text Input
			'label'	=> __('Badge Percentage','vibe-customtypes'), // <label>
			'desc'	=> __('Badge is given to people passing above percentage (out of 100)','vibe-customtypes'), // description
			'id'	=> $prefix.'course_badge_percentage', // field id and name
			'type'	=> 'number' // type of field
		),

		$prefix.'course_badge_title'=>array( // Text Input
			'label'	=> __('Badge Title','vibe-customtypes'), // <label>
			'desc'	=> __('Title is shown on hovering the badge.','vibe-customtypes'), // description
			'id'	=> $prefix.'course_badge_title', // field id and name
			'type'	=> 'text' // type of field
		),

		$prefix.'course_certificate'=>array( // Text Input
			'label'	=> __('Completion Certificate','vibe-customtypes'), // <label>
			'desc'	=> __('Enable Certificate image which Students receive upon course completion (out of 100)','vibe-customtypes'), // description
			'id'	=> $prefix.'course_certificate', // field id and name
			'type'	=> 'showhide', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),

		$prefix.'certificate_template'=>array( // Text Input
			'label'	=> __('Certificate Template','vibe-customtypes'), // <label>
			'desc'	=> __('Select a Certificate Template','vibe-customtypes'), // description
			'id'	=> $prefix.'certificate_template', // field id and name
			'type'	=> 'selectcpt', // type of field
	        'post_type' => 'certificate'
		),

		$prefix.'course_passing_percentage'=>array( // Text Input
			'label'	=> __('Passing Percentage','vibe-customtypes'), // <label>
			'desc'	=> __('Course passing percentage, for completion certificate','vibe-customtypes'), // description
			'id'	=> $prefix.'course_passing_percentage', // field id and name
			'type'	=> 'number' // type of field
		),
		$prefix.'course_drip'=>array( // Text Input
			'label'	=> __('Drip Feed','vibe-customtypes'), // <label>
			'desc'	=> __('Enable Drip Feed course','vibe-customtypes'), // description
			'id'	=> $prefix.'course_drip', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),
		$prefix.'course_drip_origin'=>array( // Text Input
			'label'	=> __('Course Starting Time as Drip Feed Origin','vibe-customtypes'), // <label>
			'desc'	=> sprintf(__('Drip feed time calculation from Course starting date/time vs previous unit access date/time (default), %s tutorial %s','vibe-customtypes'),'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/course-drip-origin/ " target="_blank">','</a>'), // description
			'id'	=> $prefix.'course_drip_origin', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),
		$prefix.'course_section_drip'=>array( // Text Input
			'label'	=> __('Section Drip Feed','vibe-customtypes'), // <label>
			'desc'	=> __('Enable Section Drip Feed (default ) course','vibe-customtypes'), // description
			'id'	=> $prefix.'course_section_drip', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),
		$prefix.'course_drip_duration_type'=>array( // Text Input
			'label'	=> __('Drip Duration as Unit Duration','vibe-customtypes'), // <label>
			'desc'	=> __('Assume Drip duration same as Unit Duration. Duration between consecutive units.','vibe-customtypes'), // description
			'id'	=> $prefix.'course_drip_duration_type', // field id and name
			'type'	=> 'yesno', // type of field
	        'options' => array(
	          array('value' => 'H',
	                'label' =>__('Hide','vibe-customtypes')),
	          array('value' => 'S',
	                'label' =>__('Show','vibe-customtypes')),
	        ),
	        'std'   => 'H'
		),
		$prefix.'course_drip_duration'=>array( // Text Input
			'label'	=> __('Drip Feed Duration (Static)','vibe-customtypes'), // <label>
			'desc'	=> __('Static duration, if Drip duration not equals Unit duration. This is the duration between consecutive Drip feed units (in ','vibe-customtypes').calculate_duration_time($drip_duration_parameter).' )', // description
			'id'	=> $prefix.'course_drip_duration', // field id and name
			'type'	=> 'number', // type of field
		),
		$prefix.'drip_duration_parameter'=>array( // Text Input
			'label'	=> __('Drip Duration parameter','vibe-customtypes'), // <label>
			'desc'	=> __('Duration parameter','vibe-customtypes'), // description
			'id'	=> $prefix.'drip_duration_parameter', // field id and name
			'type'	=> 'duration', // type of field
			'std'	=>$drip_duration_parameter
		),
		$prefix.'course_curriculum'=>array( // Text Input
			'label'	=> __('Course Curriculum','vibe-customtypes'), // <label>
			'desc'	=> __('Set Course Curriculum, prepare units and quizzes before setting up curriculum','vibe-customtypes'), // description
			'id'	=> $prefix.'course_curriculum', // field id and name
			'post_type1' => 'unit',
			'post_type2' => 'quiz',
			'type'	=> 'curriculum' // type of field
		),
		$prefix.'pre_course'=>array( // Text Input
			'label'	=> __('Prerequisite Course','vibe-customtypes'), // <label>
			'desc'	=> __('Prerequisite courses for this course','vibe-customtypes'), // description
			'id'	=> $prefix.'pre_course', // field id and name
			'type'	=> 'selectmulticpt', // type of field
			'post_type' => 'course'
		), 
		$prefix.'course_retakes'=>array( // Text Input
			'label'	=> __('Course Retakes','vibe-customtypes'), // <label>
			'desc'	=> __('Set number of times a student can re-take the course (0 to disable)','vibe-customtypes'), // description
			'id'	=> $prefix.'course_retakes', // field id and name
			'type'	=> 'number',
			'std'   => 0 // type of field
		),
		$prefix.'forum'=>array( // Text Input
			'label'	=> __('Course Forum','vibe-customtypes'), // <label>
			'desc'	=> __('Connect Forum with Course.','vibe-customtypes'), // description
			'id'	=> $prefix.'forum', // field id and name
			'type'	=> 'selectcpt', // type of field
			'post_type' => 'forum',
			'std'=>0,
		),
		$prefix.'group'=>array( // Text Input
			'label'	=> __('Course Group','vibe-customtypes'), // <label>
			'desc'	=> __('Connect a Group with Course.','vibe-customtypes'), // description
			'id'	=> $prefix.'group', // field id and name
			'type'	=> 'groups', // type of field
		),
		$prefix.'course_instructions'=>array( // Text Input
			'label'	=> __('Course specific instructions','vibe-customtypes'), // <label>
			'desc'	=> __('Course specific instructions which would be shown in the Start course/Course status page','vibe-customtypes'), // description
			'id'	=> $prefix.'course_instructions', // field id and name
			'type'	=> 'editor', // type of field
			'std'	=> ''
		),
		$prefix.'course_message'=>array( // Text Input
			'label'	=> __('Course Completion Message','vibe-customtypes'), // <label>
			'desc'	=> __('This message is shown to users when they Finish submit the course','vibe-customtypes'), // description
			'id'	=> $prefix.'course_message', // field id and name
			'type'	=> 'editor', // type of field
			'std'	=> __('This message is shown to the user when she finishes the course.','vibe-customtypes')
		),

	);
		break;
		case 'course_product':
			$metabox_settings = array(
				$prefix.'course_free'=>array( // Text Input
					'label'	=> __('Free Course','vibe-customtypes'), // <label>
					'desc'	=> __('Set Course free for all Members','vibe-customtypes'), // description
					'id'	=> $prefix.'course_free', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
				$prefix.'partial_free_course'=>array( // Text Input
					'label'	=> __('Make First Section Free','vibe-customtypes'), // <label>
					'desc'	=> __('Allows users to start the course for free, but can only see first section for free.','vibe-customtypes'), // description
					'id'	=> $prefix.'partial_free_course', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('No','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Yes','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
				$prefix.'course_apply'=>array( // Text Input
					'label'	=> __('Apply for Course','vibe-customtypes'), // <label>
					'text'	=> __('Invite Student applications for Course','vibe-customtypes'),
					'desc'	=> __('Students are required to Apply for course and instructor would manually approve them to course. Do not enable "Free" course with this setting.','vibe-customtypes'), // description
					'id'	=> $prefix.'course_apply', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
			);

			if ( (in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php')) ) || function_exists('pmpro_getAllLevels')) {

				$level_array=array();
				$levels=pmpro_getAllLevels();
				foreach($levels as $level){
					$level_array[]= array('value' =>$level->id,'label'=>$level->name);
				}
				$metabox_settings[$prefix.'pmpro_membership'] =array(
						'label'	=> __('PMPro Membership','vibe-customtypes'), // <label>
						'desc'	=> __('Required Membership level for this course','vibe-customtypes'), // description
						'id'	=> $prefix.'pmpro_membership', // field id and name
						'type'	=> 'multiselect', // type of field
						'options' => $level_array,
					);
			}
			if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php'))) {
				$instructor_privacy = vibe_get_option('instructor_content_privacy');
				$flag=1;
			    if(isset($instructor_privacy) && $instructor_privacy && !current_user_can('manage_options')){
			    	$flag=0;
			    }
			    if($flag){
					$metabox_settings[$prefix.'product'] =array(
						'label'	=> __('Associated Product','vibe-customtypes'), // <label>
						'desc'	=> __('Associated Product with the Course.','vibe-customtypes'), // description
						'id'	=> $prefix.'product', // field id and name
						'type'	=> 'selectcpt', // type of field
						'post_type'=> 'product',
				        'std'   => ''
					);
				}
			}
		break;
		case 'unit':
			$unit_types = apply_filters('wplms_unit_types',array(
                      array( 'label' =>__('Video','vibe-customtypes'),'value'=>'play'),
                      array( 'label' =>__('Audio','vibe-customtypes'),'value'=>'music-file-1'),
                      array( 'label' =>__('Podcast','vibe-customtypes'),'value'=>'podcast'),
                      array( 'label' =>__('General','vibe-customtypes'),'value'=>'text-document'),
                    ));
			$metabox_settings = array(  
				$prefix.'subtitle'=>array( // Single checkbox
					'label'	=> __('Unit Description','vibe-customtypes'), // <label>
					'desc'	=> __('Small Description.','vibe-customtypes'), // description
					'id'	=> $prefix.'subtitle', // field id and name
					'type'	=> 'textarea', // type of field
			        'std'   => ''
			        ),
				$prefix.'type'=>array( // Text Input
					'label'	=> __('Unit Type','vibe-customtypes'), // <label>
					'desc'	=> __('Select Unit type from Video , Audio , Podcast, General , ','vibe-customtypes'), // description
					'id'	=> $prefix.'type', // field id and name
					'type'	=> 'select', // type of field
					'options' => $unit_types,
			        'std'   => 'text-document'
				),
				$prefix.'free'=>array( // Text Input
					'label'	=> __('Free Unit','vibe-customtypes'), // <label>
					'desc'	=> __('Set Free unit, viewable to all','vibe-customtypes'), // description
					'id'	=> $prefix.'free', // field id and name
					'type'	=> 'showhide', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
				$prefix.'duration'=>array( // Text Input
					'label'	=> __('Unit Duration','vibe-customtypes'), // <label>
					'desc'	=> __('Duration in ','vibe-customtypes').calculate_duration_time($unit_duration_parameter), // description
					'id'	=> $prefix.'duration', // field id and name
					'type'	=> 'number' // type of field
				),
				$prefix.'unit_duration_parameter'=>array( // Text Input
					'label'	=> __('Unit Duration parameter','vibe-customtypes'), // <label>
					'desc'	=> __('Unit Duration parameter','vibe-customtypes'), // description
					'id'	=> $prefix.'unit_duration_parameter', // field id and name
					'type'	=> 'duration', // type of field
					'std'	=>$unit_duration_parameter
				),
				$prefix.'forum'=>array( // Text Input
					'label'	=> __('Unit Forum','vibe-customtypes'), // <label>
					'desc'	=> __('Connect Forum with Unit.','vibe-customtypes'), // description
					'id'	=> $prefix.'forum', // field id and name
					'type'	=> 'selectcpt', // type of field
					'post_type' => 'forum',
					'std'=>0,
				),
				$prefix.'assignment'=>array( // Text Input
					'label'	=> __('Connect Assignments','vibe-customtypes'), // <label>
					'desc'	=> __('Select an Assignment which you can connect with this Unit','vibe-customtypes'), // description
					'id'	=> $prefix.'assignment', // field id and name
					'type'	=> 'selectmulticpt', // type of field
					'post_type' => 'wplms-assignment'
				),
				$prefix.'unit_attachments'=>array( // Text Input
					'label'	=> __('Attachments','vibe-customtypes'), // <label>
					'desc'	=> __('Display these attachments below units to be downloaded by students','vibe-customtypes'), // description
					'id'	=> $prefix.'unit_attachments', // field id and name
					'type'	=> 'multiattachments', // type of field
				),
			);
			
		break;
		case 'question':
			$question_types = apply_filters('wplms_question_types',array(
	              array( 'label' =>__('True or False','vibe-customtypes'),'value'=>'truefalse'),  
	              array( 'label' =>__('Multiple Choice','vibe-customtypes'),'value'=>'single'),
	              array( 'label' =>__('Multiple Correct','vibe-customtypes'),'value'=>'multiple'),
	              array( 'label' =>__('Sort Answers','vibe-customtypes'),'value'=>'sort'),
	              array( 'label' =>__('Match Answers','vibe-customtypes'),'value'=>'match'),
	              array( 'label' =>__('Fill in the Blank','vibe-customtypes'),'value'=>'fillblank'),
	              array( 'label' =>__('Dropdown Select','vibe-customtypes'),'value'=>'select'),
	              array( 'label' =>__('Small Text','vibe-customtypes'),'value'=>'smalltext'),
	              array( 'label' =>__('Large Text','vibe-customtypes'),'value'=>'largetext'),
	              array( 'label' =>__('Survey type','vibe-customtypes'),'value'=>'survey')
	            ));
			$metabox_settings = array(  
				$prefix.'question_type'=>array( // Text Input
					'label'	=> __('Question Type','vibe-customtypes'), // <label>
					'desc'	=> __('Select Question type, ','vibe-customtypes'), // description
					'id'	=> $prefix.'question_type', // field id and name
					'type'	=> 'select', // type of field
					'options' => $question_types,
			        'std'   => 'single'
				),
				$prefix.'question_options'=>array( // Text Input
					'label'	=> __('Question Options (For Single/Multiple/Sort/Match Question types)','vibe-customtypes'), // <label>
					'desc'	=> __('Single/Mutiple Choice question options','vibe-customtypes'), // description
					'id'	=> $prefix.'question_options', // field id and name
					'type'	=> 'repeatable_count' // type of field
				),
			    $prefix.'question_answer'=>array( // Text Input
					'label'	=> __('Correct Answer','vibe-customtypes'), // <label>
					'desc'	=> __('Enter (1 = True, 0 = false ) or Choice Number (1,2..) or comma saperated Choice numbers (1,2..) or Correct Answer for small text (All possible answers comma saperated) | 0 for No Answer or Manual Check','vibe-customtypes'), // description
					'id'	=> $prefix.'question_answer', // field id and name
					'type'	=> 'text', // type of field
					'std'	=> 0
				),
				$prefix.'question_hint'=>array( // Text Input
					'label'	=> __('Answer Hint','vibe-customtypes'), // <label>
					'desc'	=> __('Add a Hint/clue for the answer to show to student','vibe-customtypes'), // description
					'id'	=> $prefix.'question_hint', // field id and name
					'type'	=> 'textarea', // type of field
					'std'	=> ''
				),
				$prefix.'question_explaination'=>array( // Text Input
					'label'	=> __('Answer Explanation','vibe-customtypes'), // <label>
					'desc'	=> __('Add Answer explanation','vibe-customtypes'), // description
					'id'	=> $prefix.'question_explaination', // field id and name
					'type'	=> 'editor', // type of field
					'std'	=> ''
				),
			);
		break;
		case 'quiz':
			$metabox_settings = array(  
				$prefix.'subtitle'=>array( // Text Input
					'label'	=> __('Quiz Subtitle','vibe-customtypes'), // <label>
					'desc'	=> __('Quiz Subtitle.','vibe-customtypes'), // description
					'id'	=> $prefix.'subtitle', // field id and name
					'type'	=> 'text', // type of field
					'std'	=> ''
				),
		        $prefix.'quiz_course'=>array( // Text Input
					'label'	=> __('Connected Course','vibe-customtypes'), // <label>
					'id'	=> $prefix.'quiz_course', // field id and name
					'type'	=> 'selectcpt', // type of field
					'post_type' => 'course',
					'post_status'=>array('publish','draft'),
					'desc'=> __('Connecting a quiz with a course would force the quiz to be available to users who have taken the course.','vibe-customtypes'),
				),
				$prefix.'duration'=>array( // Text Input
					'label'	=> __('Quiz Duration','vibe-customtypes'), // <label>
					'desc'	=> __('Quiz duration in ','vibe-customtypes').calculate_duration_time($quiz_duration_parameter).__(' Enables Timer & auto submits on expire. 9999 to disable.','vibe-customtypes'), // description
					'id'	=> $prefix.'duration', // field id and name
					'type'	=> 'number', // type of field
					'std'	=> 0
				),
				$prefix.'quiz_duration_parameter'=>array( // Text Input
					'label'	=> __('Quiz Duration parameter','vibe-customtypes'), // <label>
					'desc'	=> __('Duration parameter','vibe-customtypes'), // description
					'id'	=> $prefix.'quiz_duration_parameter', // field id and name
					'type'	=> 'duration', // type of field
					'std'	=>$quiz_duration_parameter
				),
				$prefix.'quiz_auto_evaluate'=>array( // Text Input
					'label'	=> __('Auto Evaluate Results','vibe-customtypes'), // <label>
					'desc'	=> __('Evaluate results as soon as quiz is complete. (* No Large text questions ), Diable for manual evaluate','vibe-customtypes'), // description
					'id'	=> $prefix.'quiz_auto_evaluate', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				), 
				$prefix.'quiz_retakes'=>array( // Text Input
					'label'	=> __('Number of Extra Quiz Retakes','vibe-customtypes'), // <label>
					'desc'	=> __('Student can reset and start the quiz all over again. Number of Extra retakes a student can take.','vibe-customtypes'), // description
					'id'	=> $prefix.'quiz_retakes', // field id and name
					'type'	=> 'number', // type of field
			        'std'   => 0
				), 
				$prefix.'quiz_message'=>array( // Text Input
					'label'	=> __('Post Quiz Message','vibe-customtypes'), // <label>
					'desc'	=> __('This message is shown to users when they submit the quiz','vibe-customtypes'), // description
					'id'	=> $prefix.'quiz_message', // field id and name
					'type'	=> 'editor', // type of field
					'std'	=> 'Thank you for Submitting the Quiz. Check Results in your Profile.'
				),
				$prefix.'results_after_quiz_message'=>array( // Text Input
					'label'	=> __('Show results after submission','vibe-customtypes'), // <label>
					'desc'	=> __('This will show the quiz results right after submitting the quiz below quiz completion message.','vibe-customtypes'), // description
					'id'	=> $prefix.'results_after_quiz_message', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
				$prefix.'quiz_check_answer'=>array( // Text Input
					'label'	=> __('Add Check Answer Switch','vibe-customtypes'), // <label>
					'desc'	=> __('Instantly check answer answer when question is marked','vibe-customtypes'), // description
					'id'	=> $prefix.'quiz_check_answer', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
				$prefix.'quiz_dynamic'=>array( // Text Input
					'label'	=> __('Dynamic Quiz','vibe-customtypes'), // <label>
					'desc'	=> __('Dynamic quiz automatically selects questions.','vibe-customtypes'), // description
					'id'	=> $prefix.'quiz_dynamic', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
				$prefix.'quiz_tags'=>array( // Text Input
					'label'	=> __('Dynamic Quiz Question tags','vibe-customtypes'), // <label>
					'desc'	=> __('Select Question tags from where questions will be selected for the quiz.(required if dynamic enabled)','vibe-customtypes'), // description
					'id'	=> $prefix.'quiz_tags', // field id and name
					'type'	=> 'dynamic_quiz_questions', // type of field
					'taxonomy' => 'question-tag',
			        'std'   => 0
				),
				/*$prefix.'quiz_number_questions'=>array( // Text Input
					'label'	=> __('Number of Questions in Dynamic Quiz','vibe-customtypes'), // <label>
					'desc'	=> __('Enter the number of Questions in the dynamic quiz. (required if dynamic enabled).','vibe-customtypes'), // description
					'id'	=> $prefix.'quiz_number_questions', // field id and name
					'type'	=> 'number', // type of field
			        'std'   => 0
				),
				$prefix.'quiz_marks_per_question'=>array( // Text Input
					'label'	=> __('Marks per Question in Dynamic Quiz','vibe-customtypes'), // <label>
					'desc'	=> __('Enter the number of marks per Questions in the dynamic quiz. (required if dynamic enabled).','vibe-customtypes'), // description
					'id'	=> $prefix.'quiz_marks_per_question', // field id and name
					'type'	=> 'number', // type of field
			        'std'   => 0
				),*/
				$prefix.'quiz_random'=>array( // Text Input
					'label'	=> __('Randomize Quiz Questions','vibe-customtypes'), // <label>
					'desc'	=> __('Random Question sequence for every quiz','vibe-customtypes'), // description
					'id'	=> $prefix.'quiz_random', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
			    $prefix.'quiz_questions'=>array( // Text Input
					'label'	=> __('Quiz Questions','vibe-customtypes'), // <label>
					'desc'	=> __('Quiz questions for Static Quiz only','vibe-customtypes'), // description
					'id'	=> $prefix.'quiz_questions', // field id and name
					'type'	=> 'repeatable_selectcpt', // type of field
					'post_type' => 'question',
					'std'	=> 0
				),
			    
			);
		break;
		case 'testimonial':
			$metabox_settings = array(  
				array( // Text Input
					'label'	=> __('Author Name','vibe-customtypes'), // <label>
					'desc'	=> __('Enter the name of the testimonial author.','vibe-customtypes'), // description
					'id'	=> $prefix.'testimonial_author_name', // field id and name
					'type'	=> 'text' // type of field
				),
			        array( // Text Input
					'label'	=> __('Designation','vibe-customtypes'), // <label>
					'desc'	=> __('Enter the testimonial author\'s designation.','vibe-customtypes'), // description
					'id'	=> $prefix.'testimonial_author_designation', // field id and name
					'type'	=> 'text' // type of field
				),
			);
		break;
		case 'news':
			$metabox_settings = array(  
			  array( // Text Input
			    'label' => __('Share with students in Course','vibe-customtypes'), // <label>
			    'desc'  => __('Student having access to this courses will get the news','vibe-customtypes'), // description
			    'id'  => $prefix.'news_course', // field id and name
			    'type'  => 'selectcpt', // type of field
			    'post_type'=>'course'
			  ),
			  array( // Single checkbox
				'label' => __('Post Sub-Title','vibe-customtypes'), // <label>
				'desc'  => __('Post Sub- Title.','vibe-customtypes'), // description
				'id'  => $prefix.'subtitle', // field id and name
				'type'  => 'textarea', // type of field
			    'std'   => ''
			            ),
			);
		break;
		case 'product': //WooCommerce uses Old select2
			global $wpdb;
			$courses = array();
			$course_array = $wpdb->get_results("SELECT ID,post_title FROM {$wpdb->posts} WHERE post_type = 'course' AND post_status = 'publish' LIMIT 0,9999");
			if(!empty($course_array)){
				foreach($course_array as $course){
					$courses[] = array('label'=>$course->post_title,'value'=>$course->ID);
				}
			}
			$metabox_settings = array(  
				array( // Text Input
					'label'	=> __('Associated Courses','vibe-customtypes'), // <label>
					'desc'	=> __('Associated Courses with this product. Enables access to the course.','vibe-customtypes'), // description
					'id'	=> $prefix.'courses', // field id and name
					'type'	=> 'multiselect', // type of field
					'options'=> $courses
				),
			    array( // Text Input
					'label'	=> __('Subscription ','vibe-customtypes'), // <label>
					'desc'	=> __('Enable if Product is Subscription Type (Price per month)','vibe-customtypes'), // description
					'id'	=> $prefix.'subscription', // field id and name
					'type'	=> 'showhide', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			                'std'   => 'H'
				),
			    array( // Text Input
					'label'	=> __('Subscription Duration','vibe-customtypes'), // <label>
					'desc'	=> __('Duration for Subscription Products (in ','vibe-customtypes').calculate_duration_time($product_duration_parameter).')', // description
					'id'	=> $prefix.'duration', // field id and name
					'type'	=> 'number' // type of field
				),
				$prefix.'product_duration_parameter'=>array( // Text Input
					'label'	=> __('Product Duration parameter','vibe-customtypes'), // <label>
					'desc'	=> __('Duration parameter','vibe-customtypes'), // description
					'id'	=> $prefix.'product_duration_parameter', // field id and name
					'type'	=> 'duration', // type of field
					'std'	=>$product_duration_parameter
				),
			);
		break;
		case 'wplms-event':
			$metabox_settings=array(  
				array( // Single checkbox
					'label'	=> __('Event Sub-Title','vibe-customtypes'), // <label>
					'desc'	=> __('Event Sub-Title.','vibe-customtypes'), // description
					'id'	=> $prefix.'subtitle', // field id and name
					'type'	=> 'textarea', // type of field
			        'std'   => ''
			                ), 
				array( // Text Input
					'label'	=> __('Course','vibe-customtypes'), // <label>
					'desc'	=> __('Select Course for which the event is valid','vibe-customtypes'), // description
					'id'	=> $prefix.'event_course', // field id and name
					'type'	=> 'selectcpt', // type of field
					'post_type' => 'course'
				),
				array( // Text Input
					'label'	=> __('Connect an Assignment','vibe-customtypes'), // <label>
					'desc'	=> __('Select an Assignment which you can connect with this Event','vibe-customtypes'), // description
					'id'	=> $prefix.'assignment', // field id and name
					'type'	=> 'selectcpt', // type of field
					'post_type' => 'wplms-assignment'
				),
				array( // Text Input
					'label'	=> __('Event Icon','vibe-customtypes'), // <label>
					'desc'	=> __('Click on icon to  select an icon for the event','vibe-customtypes'), // description
					'id'	=> $prefix.'icon', // field id and name
					'type'	=> 'icon', // type of field
				),
				array( // Text Input
					'label'	=> __('Event Color','vibe-customtypes'), // <label>
					'desc'	=> __('Select color for Event','vibe-customtypes'), // description
					'id'	=> $prefix.'color', // field id and name
					'type'	=> 'color', // type of field
				),
				array( // Text Input
					'label'	=> __('Start Date','vibe-customtypes'), // <label>
					'desc'	=> __('Date from which Event Begins','vibe-customtypes'), // description
					'id'	=> $prefix.'start_date', // field id and name
					'type'	=> 'date', // type of field
				),
				array( // Text Input
					'label'	=> __('End Date','vibe-customtypes'), // <label>
					'desc'	=> __('Date on which Event ends.','vibe-customtypes'), // description
					'id'	=> $prefix.'end_date', // field id and name
					'type'	=> 'date', // type of field
				),
				array( // Text Input
					'label'	=> __('Start Time','vibe-customtypes'), // <label>
					'desc'	=> __('Date from which Event Begins','vibe-customtypes'), // description
					'id'	=> $prefix.'start_time', // field id and name
					'type'	=> 'time', // type of field
				),
				array( // Text Input
					'label'	=> __('End Time','vibe-customtypes'), // <label>
					'desc'	=> __('Date on which Event ends.','vibe-customtypes'), // description
					'id'	=> $prefix.'end_time', // field id and name
					'type'	=> 'time', // type of field
				),
				array( // Text Input
					'label'	=> __('Show Location','vibe-customtypes'), // <label>
					'desc'	=> __('Show Location and Google map with the event','vibe-customtypes'), // description
					'id'	=> $prefix.'show_location', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
			    array( // Text Input
					'label'	=> __('Location','vibe-customtypes'), // <label>
					'desc'	=> __('Location of event','vibe-customtypes'), // description
					'id'	=> $prefix.'location', // field id and name
					'type'	=> 'gmap' // type of field
				),
				array( // Text Input
					'label'	=> __('Additional Information','vibe-customtypes'), // <label>
					'desc'	=> __('Point wise Additional Information regarding the event','vibe-customtypes'), // description
					'id'	=> $prefix.'additional_info', // field id and name
					'type'	=> 'repeatable' // type of field
				),
				array( // Text Input
					'label'	=> __('More Information','vibe-customtypes'), // <label>
					'desc'	=> __('Supports HTML and shortcodes','vibe-customtypes'), // description
					'id'	=> $prefix.'more_info', // field id and name
					'type'	=> 'editor' // type of field
				),
				array( // Text Input
					'label'	=> __('All Day','vibe-customtypes'), // <label>
					'desc'	=> __('An all Day event','vibe-customtypes'), // description
					'id'	=> $prefix.'all_day', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
				array( // Text Input
					'label'	=> __('Private Event','vibe-customtypes'), // <label>
					'desc'	=> __('Only Invited participants can see the Event','vibe-customtypes'), // description
					'id'	=> $prefix.'private_event', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
			);
			
			

		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php'))) {
			$metabox_settings[] =array(
					'label'	=> __('Associated Product for Event Access','vibe-customtypes'), // <label>
					'desc'	=> __('Purchase of this product grants Event access to the member.','vibe-customtypes'), // description
					'id'	=> $prefix.'product', // field id and name
					'type'	=> 'selectcpt', // type of field
					'post_type'=> 'product',
			        'std'   => ''
				);
		}
		break;
		case 'payments':
			$metabox_settings = array(  
				array( // Text Input
					'label'	=> __('From','vibe-customtypes'), // <label>
					'desc'	=> __('Date on which Payment was done.','vibe-customtypes'), // description
					'id'	=> $prefix.'date_from', // field id and name
					'type'	=> 'text', // type of field
				),
				array( // Text Input
					'label'	=> __('To','vibe-customtypes'), // <label>
					'desc'	=> __('Date on which Payment was done.','vibe-customtypes'), // description
					'id'	=> $prefix.'date_to', // field id and name
					'type'	=> 'text', // type of field
				),
			    array( // Text Input
					'label'	=> __('Instructor and Commissions','vibe-customtypes'), // <label>
					'desc'	=> __('Instructor commissions','vibe-customtypes'), // description
					'id'	=> $prefix.'instructor_commissions', // field id and name
					'type'	=> 'payments' // type of field
				),
			);
		break;
		case 'certificate':
			$metabox_settings = array(  
				array( // Text Input
					'label'	=> __('Background Image/Pattern','vibe-customtypes'), // <label>
					'desc'	=> __('Add background image','vibe-customtypes'), // description
					'id'	=> $prefix.'background_image', // field id and name
					'type'	=> 'image', // type of field
				),
				array( // Text Input
					'label'	=> __('Enable Print & PDF','vibe-customtypes'), // <label>
					'desc'	=> __('Displays a Print and Download as PDF Button on top right corner of certificate','vibe-customtypes'), // description
					'id'	=> $prefix.'print', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>__('Hide','vibe-customtypes')),
			          array('value' => 'S',
			                'label' =>__('Show','vibe-customtypes')),
			        ),
			        'std'   => 'H'
				),
				array( // Text Input
					'label'	=> __('Certificate Width','vibe-customtypes'), // <label>
					'desc'	=> __('Add certificate width','vibe-customtypes'), // description
					'id'	=> $prefix.'certificate_width', // field id and name
					'type'	=> 'text', // type of field
				),
				array( // Text Input
					'label'	=> __('Certificate Height','vibe-customtypes'), // <label>
					'desc'	=> __('Add certificate height','vibe-customtypes'), // description
					'id'	=> $prefix.'certificate_height', // field id and name
					'type'	=> 'text', // type of field
				),
				array( // Text Input
					'label'	=> __('Custom Class','vibe-customtypes'), // <label>
					'desc'	=> __('Add Custom Class over Certificate container.','vibe-customtypes'), // description
					'id'	=> $prefix.'custom_class', // field id and name
					'type'	=> 'text', // type of field
				),
				array( // Text Input
					'label'	=> __('Custom CSS','vibe-customtypes'), // <label>
					'desc'	=> __('Add Custom CSS for Certificate.','vibe-customtypes'), // description
					'id'	=> $prefix.'custom_css', // field id and name
					'type'	=> 'textarea', // type of field
				),
				array( // Text Input
					'label'	=> __('NOTE:','vibe-customtypes'), // <label>
					'desc'	=> __(' USE FOLLOWING SHORTCODES TO DISPLAY RELEVANT DATA : <br />1. <strong>[certificate_student_name]</strong> : Displays Students Name<br />2. <strong>[certificate_course]</strong> : Displays Course Name<br />3. <strong>[certificate_student_marks]</strong> : Displays Students Marks in Course<br />4. <strong>[certificate_student_date]</strong>: Displays date on which Certificate was awarded to the Student<br />5. <strong>[certificate_student_email]</strong>: Displays registered email of the Student<br />6. <strong>[certificate_code]</strong>: Generates unique code for Student which can be validated from Certificate page.<br />7. <strong>[course_completion_date]</strong>: Displays course completion date from course activity.<br />8. <strong>[certificate_student_photo]</strong> : Displays Students pic<br />9. <strong>[certificate_course_duration]</strong> : Students time spent on course','vibe-customtypes'), // description
					'id'	=> $prefix.'note', // field id and name
					'type'	=> 'note', // type of field
				),
			);	
		break;
		case 'wplms-assignment':
			$max_upload = (int)(ini_get('upload_max_filesize'));
			$max_post = (int)(ini_get('post_max_size'));
			$memory_limit = (int)(ini_get('memory_limit'));
			$upload_mb = min($max_upload, $max_post, $memory_limit);
			$metabox_settings = array(  
				array( // Single checkbox
						'label'	=> __('Assignment Sub-Title','vibe-customtypes'), // <label>
						'desc'	=> __('Assignment Sub-Title.','vibe-customtypes'), // description
						'id'	=> $prefix.'subtitle', // field id and name
						'type'	=> 'textarea', // type of field
				        'std'   => ''
				                ), 
				array( // Single checkbox
						'label'	=> __('Sidebar','vibe-customtypes'), // <label>
						'desc'	=> __('Select a Sidebar | Default : mainsidebar','vibe-customtypes'), // description
						'id'	=> $prefix.'sidebar', // field id and name
						'type'	=> 'select',
				                'options' => $sidebararray
				                ),
				array( // Text Input
					'label'	=> __('Assignment Maximum Marks','vibe-customtypes'), // <label>
					'desc'	=> __('Set Maximum marks for the assignment','vibe-customtypes'), // description
					'id'	=> $prefix.'assignment_marks', // field id and name
					'type'	=> 'number', // type of field
					'std' => '10'
				),
				array( // Text Input
					'label'	=> sprintf(__('Assignment Maximum Time limit %s','vibe-customtypes'),'( '.calculate_duration_time($assignment_duration_parameter).' )'), // <label>
					'desc'	=> __('Set Maximum Time limit for Assignment ( in ','vibe-customtypes').calculate_duration_time($assignment_duration_parameter).' )', // description
					'id'	=> $prefix.'assignment_duration', // field id and name
					'type'	=> 'number', // type of field
					'std' => '10'
				),
				$prefix.'assignment_duration_parameter'=>array( // Text Input
					'label'	=> __('Assignment Duration parameter','vibe-customtypes'), // <label>
					'desc'	=> __('Duration parameter','vibe-customtypes'), // description
					'id'	=> $prefix.'assignment_duration_parameter', // field id and name
					'type'	=> 'duration', // type of field
					'std'	=>$assignment_duration_parameter
				),
				array( // Text Input
						'label'	=> __('Include in Course Evaluation','vibe-customtypes'), // <label>
						'desc'	=> __('Include assignment marks in Course Evaluation','vibe-customtypes'), // description
						'id'	=> $prefix.'assignment_evaluation', // field id and name
						'type'	=> 'yesno', // type of field
				        'options' => array(
				          array('value' => 'H',
				                'label' =>__('Hide','vibe-customtypes')),
				          array('value' => 'S',
				                'label' =>__('Show','vibe-customtypes')),
				        ),
				        'std'   => 'H'
					),
				array( // Text Input
						'label'	=> __('Include in Course','vibe-customtypes'), // <label>
						'desc'	=> __('Assignments marks will be shown/used in course evaluation','vibe-customtypes'), // description
						'id'	=> $prefix.'assignment_course', // field id and name
						'post_status'=>array('publish','draft'),
						'type'	=> 'selectcpt', // type of field
						'post_type' => 'course'
					),
				array( // Single checkbox
						'label'	=> __('Assignment Submissions','vibe-customtypes'), // <label>
						'desc'	=> __('Select type of assignment submissions','vibe-customtypes'), // description
						'id'	=> $prefix.'assignment_submission_type', // field id and name
						'type'	=> 'select', // type of field
				        'options' => array(
				                    1=>array('label'=>'Upload file','value'=>'upload'),
				                    2=>array('label'=>'Text Area','value'=>'textarea'),
				        ),
				        'std'   => ''
					),
				array( // Text Input
						'label'	=> __('Attachment Type','vibe-customtypes'), // <label>
						'desc'	=> __('Select valid attachment types ','vibe-customtypes'), // description
						'id'	=> $prefix.'attachment_type', // field id and name
						'type'	=> 'multiselect', // type of field
						'options' => array(
							array('value'=> 'JPG','label' =>'JPG'),
							array('value'=> 'GIF','label' =>'GIF'),
							array('value'=> 'PNG','label' =>'PNG'),
							array('value'=> 'PDF','label' =>'PDF'),
							array('value'=>'PSD','label'=>'PSD'),
							array('value'=> 'DOC','label' =>'DOC'),
							array('value'=> 'DOCX','label' => 'DOCX'),
							array('value'=> 'PPT','label' =>'PPT'),
							array('value'=> 'PPTX','label' => 'PPTX'),
							array('value'=> 'PPS','label' =>'PPS'),
							array('value'=> 'PPSX','label' => 'PPSX'),
							array('value'=> 'ODT','label' =>'ODT'),
							array('value'=> 'XLS','label' =>'XLS'),
							array('value'=> 'XLSX','label' => 'XLSX'),
							array('value'=> 'MP3','label' =>'MP3'),
							array('value'=> 'M4A','label' =>'M4A'),
							array('value'=> 'OGG','label' =>'OGG'),
							array('value'=> 'WAV','label' =>'WAV'),
							array('value'=> 'WMA','label' =>'WMA'),
							array('value'=> 'MP4','label' =>'MP4'),
							array('value'=> 'M4V','label' =>'M4V'),
							array('value'=> 'MOV','label' =>'MOV'),
							array('value'=> 'WMV','label' =>'WMV'),
							array('value'=> 'AVI','label' =>'AVI'),
							array('value'=> 'MPG','label' =>'MPG'),
							array('value'=> 'OGV','label' =>'OGV'),
							array('value'=> '3GP','label' =>'3GP'),
							array('value'=> '3G2','label' =>'3G2'),
							array('value'=> 'FLV','label' =>'FLV'),
							array('value'=> 'WEBM','label' =>'WEBM'),
							array('value'=> 'APK','label' =>'APK '),
							array('value'=> 'RAR','label' =>'RAR'),
							array('value'=> 'ZIP','label' =>'ZIP'),
				        ),
				        'std'   => 'single'
					),
					array( // Text Input
					'label'	=> __('Attachment Size (in MB)','vibe-customtypes'), // <label>
					'desc'	=> __('Set Maximum Attachment size for upload ( set less than ','vibe-customtypes' ).$upload_mb.' MB)', // description
					'id'	=> $prefix.'attachment_size', // field id and name
					'type'	=> 'number', // type of field
					'std' => '2'
					),

			);
		break;
		case 'popup':
			$metabox_settings = array(  
				array( // Text Input
				'label'	=> __('Width (in px)','vibe-customtypes'), // <label>
				'desc'	=> __('Set Maximum width of popup','vibe-customtypes' ), // description
				'id'	=> $prefix.'popup_width', // field id and name
				'type'	=> 'number', // type of field
				'std' => '480'
				),
				array( // Text Input
				'label'	=> __('Height (in px)','vibe-customtypes'), // <label>
				'desc'	=> __('Set Maximum height of popup ','vibe-customtypes' ), // description
				'id'	=> $prefix.'popup_height', // field id and name
				'type'	=> 'number', // type of field
				'std' => '600'
				),
				array( // Text Input
				'label'	=> __('Custom Class','vibe-customtypes'), // <label>
				'desc'	=> __('Add custom class to popup ','vibe-customtypes' ), // description
				'id'	=> $prefix.'popup_class', // field id and name
				'type'	=> 'text', // type of field
				'std' => ''
				),
				array( // Single checkbox
					'label'	=> __('Add Custom CSS','vibe-customtypes'), // <label>
					'desc'	=> __('Custom CSS for Popup','vibe-customtypes'), // description
					'id'	=> $prefix.'custom_css', // field id and name
					'type'	=> 'textarea', // type of field
			        'std'   => ''
		        ), 
			);
		break;
	}
	return apply_filters('wplms_'.$metabox.'_metabox',$metabox_settings);
}



Class Vibe_Custom_Meta_Boxes{
	public static $instance;
    
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Custom_Meta_Boxes();
        return self::$instance;
    }

    public function __construct(){

		add_action('init',array($this,'add_vibe_metaboxes'));
		add_action( 'add_meta_boxes', array($this,'add_vibe_editor' ));
		add_filter('manage_course_posts_columns', array($this,'featured_courses'));
		add_action('manage_course_posts_custom_column', array($this,'featured_course_star'), 10, 2);
		//add_action('admin_enqueue_Script')
		add_action('admin_enqueue_scripts',array($this,'print_featured_script'),10,1);
		add_action('wp_ajax_featured_course',array($this,'mark_featured_course'));
    }


	function add_vibe_metaboxes(){
		
		$prefix = 'vibe_';
		$post_metabox = vibe_meta_box_arrays('post');
		$page_metabox = vibe_meta_box_arrays('page');
		$course_metabox = vibe_meta_box_arrays('course');
		$course_product_metabox = vibe_meta_box_arrays('course_product');
		$unit_metabox = vibe_meta_box_arrays('unit');
		$question_metabox = vibe_meta_box_arrays('question');
		$quiz_metabox = vibe_meta_box_arrays('quiz');
		$testimonial_metabox = vibe_meta_box_arrays('testimonial');
		if(function_exists('vibe_get_option')){
			$show_news = vibe_get_option('show_news');
			if(!empty($show_news)){
				$news_metabox = vibe_meta_box_arrays('news');
			}
		}
		$product_metabox = vibe_meta_box_arrays('product');
		$wplms_events_metabox = vibe_meta_box_arrays('wplms-event');
		$payments_metabox = vibe_meta_box_arrays('payments');
		$certificate_metabox = vibe_meta_box_arrays('certificate');
		$wplms_assignments_metabox = vibe_meta_box_arrays('wplms-assignment');
		$wplms_popup_metabox = vibe_meta_box_arrays('popup');

		$dwqna_custom_metabox = array(  
			array( // Text Input
				'label'	=> __('Connected Course','vibe-customtypes'), // <label>
				'desc'	=> __('Connect this question to a course','vibe-customtypes'), // description
				'id'	=> $prefix.'question_course', // field id and name
				'type'	=> 'selectcpt', // type of field
				'post_type' => 'course'
			),
			array( // Text Input
				'label'	=> __('Connected Unit','vibe-customtypes'), // <label>
				'desc'	=> __('Connect this question to a Unit','vibe-customtypes'), // description
				'id'	=> $prefix.'question_unit', // field id and name
				'type'	=> 'selectcpt', // type of field
				'post_type' => 'unit'
			),  
		);
		

		$post_metabox = new custom_add_meta_box( 'post-settings', __('Post Settings','vibe-customtypes'), $post_metabox, 'post', true );
		$page_metabox = new custom_add_meta_box( 'page-settings', __('Page Settings','vibe-customtypes'), $page_metabox, 'page', true );

		$course_box = new custom_add_meta_box( 'page-settings', __('Course Settings','vibe-customtypes'), $course_metabox, 'course', true );

		$course_product = __('Course Product','vibe-customtypes');
		if(function_exists('pmpro_getAllLevels')){
			$course_product = __('Course Membership','vibe-customtypes');
		}
		$course_product_box = new custom_add_meta_box( 'post-settings', $course_product, $course_product_metabox, 'course', true );
		$unit_box = new custom_add_meta_box( 'page-settings', __('Unit Settings','vibe-customtypes'), $unit_metabox, 'unit', true );

		$question_box = new custom_add_meta_box( 'page-settings', __('Question Settings','vibe-customtypes'), $question_metabox, 'question', true );
		$quiz_box = new custom_add_meta_box( 'page-settings', __('Quiz Settings','vibe-customtypes'), $quiz_metabox, 'quiz', true );
		
		if(post_type_exists( 'dwqa-question' ))
			$dwqna_custom_box = new custom_add_meta_box( 'page-settings', __('Settings','vibe-customtypes'), $dwqna_custom_metabox, 'dwqa-question', false );

		$testimonial_box = new custom_add_meta_box( 'testimonial-info', __('Testimonial Author Information','vibe-customtypes'), $testimonial_metabox, 'testimonials', true );
		if(function_exists('vibe_get_option')){
			$show_news = vibe_get_option('show_news');
			if(!empty($show_news)){
				$news_box = new custom_add_meta_box( 'page-settings', __('News Settings','vibe-customtypes'), $news_metabox, 'news', true );
			}
		}
		$payments_metabox = new custom_add_meta_box( 'page-settings', __('Payments Settings','vibe-customtypes'), $payments_metabox, 'payments', true );
		$certificates_metabox = new custom_add_meta_box( 'page-settings', __('Certificate Template Settings','vibe-customtypes'), $certificate_metabox, 'certificate', true );
		$popup_metabox= new custom_add_meta_box( 'page-settings', __('Popup Settings','vibe-customtypes'), $wplms_popup_metabox, 'popups', true );
		

		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php'))) {
			$flag = apply_filters('wplms_woocommerce_enable_pricing',1);
			if($flag){
				$product_box = new custom_add_meta_box( 'page-settings', __('Product Course Settings','vibe-customtypes'), $product_metabox, 'product', true );
			}
		}

		if ( in_array( 'wplms-events/wplms-events.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$events_metabox = new custom_add_meta_box( 'page-settings', __('WPLMS Events Settings','vibe-customtypes'), $wplms_events_metabox, 'wplms-event', true );
		}

		
		if ( in_array( 'wplms-assignments/wplms-assignments.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			$eassignments_metabox = new custom_add_meta_box( 'page-settings', __('WPLMS Assignments Settings','vibe-customtypes'), $wplms_assignments_metabox, 'wplms-assignment', true );
		}
	}
	

	function add_vibe_editor(){
		$page_builder = WPLMS_Page_Builder::init();
	    add_meta_box( 'vibe-editor', __( 'Page Builder', 'vibe-customtypes' ),array($page_builder,'vibe_layout_editor') , 'page', 'normal', 'high' );
	}


	function featured_courses($defaults){
		$defaults['featured'] = '<span class="dashicons dashicons-star-filled"></span>';
		return $defaults;
	}

	function featured_course_star($column_name, $post_id){

		if ($column_name == 'featured') {
		    $featured = get_post_meta($post_id,'featured',true);
		    if (!empty($featured)) {
		        echo '<span class="dashicons dashicons-star-filled" data-id="'.$post_id.'"></span>';
		    }else{
		    	echo '<span class="dashicons dashicons-star-empty" data-id="'.$post_id.'"></span>';
		    }
		}
	}

	function print_featured_script($hook){
		if($hook == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'course'){
			wp_enqueue_script('jquery');
			add_action('admin_footer',function(){
				//Only admin can manage options
				if(!current_user_can('manage_options'))
					return;

			?>
			<style>.dashicons.dashicons-star-empty:hover{text-shadow:0 1px 5px rgba(0,0,0,0.5);}</style>
			<script>
				jQuery(document).ready(function($){
					$('.dashicons-star-empty,.dashicons-star-filled').on('click',function(event){
						event.preventDefault();
						var $this= $(this);
						var featured = 0;
						if($this.hasClass('dashicons-star-empty')){
							featured = 1;
							$this.removeClass('dashicons-star-empty').addClass('dashicons-star-filled');
						}else{
							featured = 0;
							$this.removeClass('dashicons-star-filled').addClass('dashicons-star-empty')
						}

						$.ajax({
                          type: "POST",
                          url: ajaxurl,
                          async: true,
                          data: { action: 'featured_course', 
                                  id:$this.attr('data-id'),
                                  featured: featured
                                },
                          cache: false,
						});
					});
				});
			</script>
			<?php
			});
		}
	}

	function mark_featured_course(){
		
		if ( !current_user_can('manage_options') || !is_numeric($_POST['id'])){
	        _e('Security check Failed. Contact Administrator.','vibe');
	        die();
	    }

	    update_post_meta($_POST['id'],'featured',intval($_POST['featured']));
	    do_action('update_featured_course',$_POST['id'],$_POST['featured']);
	    die();
	}
}


//add_action('admin_init',function(){
	Vibe_Custom_Meta_Boxes::init();
//});

if(!function_exists('attachment_getMaximumUploadFileSize')){
	function attachment_getMaximumUploadFileSize(){
	    $maxUpload      = (int)(ini_get('upload_max_filesize'));
	    $maxPost        = (int)(ini_get('post_max_size'));
	    $memoryLimit    = (int)(ini_get('memory_limit'));
	    return min($maxUpload, $maxPost, $memoryLimit);
	}
}



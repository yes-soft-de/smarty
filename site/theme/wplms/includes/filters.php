<?php
/**
 * FILTER functions for WPLMS
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     2.0
 */

if ( !defined( 'ABSPATH' ) ) exit;

class WPLMS_Filters{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Filters();

        return self::$instance;
    }

    private function __construct(){
    	
    	/* ==== BODY Loading Effect === */
    	add_filter('body_class',array($this,'loading_effect'));

		add_filter( 'woocommerce_output_related_products_args', array($this,'wplms_related_products_args') );
		add_filter('product_cat_class',array($this,'woocommerce_product_category_class'));
  		
		add_filter( 'bbp_after_get_the_content_parse_args', array($this,'bbp_enable_visual_editor' ));
		add_filter('wplms_sidebar',array($this,'wplms_sidebar_select'),10,2);

		/*==== WORDPRESS SEO COMPATIBILITY ======*/

		add_filter('wpseo_title',array($this,'remove_wpseo_from_buddypress'));
		add_filter('wpseo_pre_analysis_post_content',array($this,'vibe_page_builder_content'),10,2);
		/*==== No Gravatars === */
		add_filter( 'bp_core_fetch_avatar_no_grav', '__return_true' );
		add_filter( 'bp_core_default_avatar_user', array($this,'vibe_custom_avatar' ));


		/* ==== Redirect course cats === */
		add_filter('body_class',array($this,'add_directory_class'));
		/* == Student ACtivity ===*/
		add_filter('wplms_activity_loop',array($this,'wplms_student_activity'));

		add_filter('get_avatar',array($this,'change_avatar_css'));
		add_filter('widget_text', 'do_shortcode');
		add_filter( 'registration_redirect' , array($this,'vibe_registration_redirect') );
		
		/* == COURSE REVIEWS FIX when permalinks set to course directory===*/
		add_filter('comments_open',array($this,'manage_course_reviews'));

		//Remove Versions
		add_filter( 'script_loader_src', array($this,'_remove_script_version'), 15, 1 );
		add_filter( 'style_loader_src',array($this,'_remove_script_version'), 15, 1 );
		
		/* === HIDE COURSES === */
		add_filter('bp_course_total_count',array($this,'hidden_courses'));
		add_filter('wplms_carousel_course_filters',array($this,'wplms_exlude_courses_directroy'));
		add_filter('wplms_grid_course_filters',array($this,'wplms_exlude_courses_directroy'));
		add_filter('vibe_editor_filterable_type',array($this,'wplms_exlude_courses_directroy'));
		add_filter('bp_course_wplms_filters',array($this,'wplms_exlude_courses_directroy'));
		add_filter('vibe_related_courses',array($this,'wplms_exlude_courses_directroy'));
		add_filter('wplms_show_instructor_courses',array($this,'wplms_exlude_courses_from_inst_profile'));

		add_filter('pre_get_posts',array($this,'wplms_exlude_courses_from_directroy'));
		add_filter('pre_get_posts',array($this,'wplms_exclude_courses_authors'));

		add_filter( 'bp_before_xprofile_cover_image_settings_parse_args', array($this,'wplms_xprofile_cover_image'), 10, 1 );
		/*=== Certificate VALIDAITON === */

		add_filter('wplms_certificate_code_template_id',array($this,'wplms_get_template_id_from_certificate_code'));
		add_filter('wplms_certificate_code_user_id',array($this,'wplms_get_user_id_from_certificate_code'));
		add_filter('wplms_certificate_code_course_id',array($this,'wplms_get_course_id_from_certificate_code'));

		//Transparent Header
		add_filter('vibe_option_custom_sections',array($this,'default_background_image_option'));
		add_filter('wplms_post_metabox',array($this,'specific_title_background'));
		add_filter('wplms_course_metabox',array($this,'specific_title_background'));
		add_filter('wplms_unit_metabox',array($this,'specific_title_background'));
		add_filter('wplms_assignment_metabox',array($this,'specific_title_background'));
		add_filter('wplms_testimonial_metabox',array($this,'specific_title_background'));
		add_filter('wplms_quiz_metabox',array($this,'specific_title_background'));
		add_filter('wplms_question_metabox',array($this,'specific_title_background'));
		add_filter('wplms_news_metabox',array($this,'specific_title_background'));
		add_filter('wplms_page_metabox',array($this,'specific_title_background'));

		
		// Add columns Reference WooCommerce
		add_filter( 'manage_edit-course-cat_columns', array( $this, 'course_cat_columns' ) );
		add_filter( 'manage_course-cat_custom_column', array( $this, 'course_cat_column' ), 10, 3 );
		add_filter('get_terms_orderby',array($this,'course_cat_orderby'),10,3);
		add_filter('wplms_course_filters_course_cat',array($this,'course_cat_nav_orderby'),10);

		//Show visual composer design options
		add_filter('vc_settings_page_show_design_tabs',array($this,'display_design_options'));

		//Enable Shortcodes in BuddyPress profiles
		add_filter('bp_get_the_profile_field_value', 'do_shortcode');
		add_filter('bp_get_profile_field_data', 'do_shortcode');

		add_filter('woocommerce_order_items_meta_display',array($this,'woocommerce_commission_display'));
		/* ===== INSTRUCTOR PRIVACY ====== */
		add_filter('wplms_frontend_cpt_query',array($this,'wplms_instructor_privacy_filter'));
		add_filter('wplms_backend_cpt_query',array($this,'wplms_instructor_privacy_filter2')); // Modified to protect Product association
		add_action('pre_get_posts', array($this,'wplms_instructor_privacy_filter_attachments'));
		add_filter('bp_course_single_item_view',array($this,'restrict_draft_courses'),10);

		//Instructing courses, Course loop check
		add_filter('bp_course_single_item_view',array($this,'instructing_courses_fix'));

		//Site Lock New Home url fix
		add_filter('wplms_site_link',array($this,'site_lock_url_on_logo'),10,2);
		add_filter('wplms_site_lock_exclusions',array($this,'add_site_lock_url_on_logo'),10);

		//BBPRESS - BUDDYPRESS USER PROFILE - TOPICS PAGINATION FIX
		add_filter('bbp_after_has_topics_parse_args',array($this,'bbp_bp_user_topics_replies_pagination'));
		//BUDDYPRESS - CREATE GROUP BUTTON VISIBLE CONDITIONALY FIX
		add_filter('bp_user_can_create_groups',array($this,'conditionaly_bp_user_can_create_groups'),10,2);

		add_filter('wplms_enable_signup',array($this,'enable_signup'));
		add_filter('wplms_buddypress_registration_link',array($this,'registration_link'));
		
		//Transparent header options
		add_filter('wplms_title_bg',array($this,'transparent_title_css'));
		add_filter('wplms_title_color',array($this,'transparent_title_color'));

		//Search options
		add_filter('wplms-main-menu',array($this,'nav_search'),999);
    }
	

	function nav_search($args){
		$course_search = vibe_get_option('course_search');
		if($course_search == 2){
			ob_start();
			do_action('wplms_header_nav_search');
			$search = ob_get_clean();
			$args['items_wrap'] = '<ul id="%1$s" class="%2$s">%3$s<li class="menu_nav_search">'.$search.'</li></ul>';
		}
		if($course_search == 3){
			ob_start();
			do_action('wplms_header_nav_search');
			$search = ob_get_clean();
			$args['items_wrap'] = '<ul id="%1$s" class="%2$s"><li class="menu_nav_search">'.$search.'</li>%3$s</ul>';
		}
		return $args;
	}
    //Transparent header
    function transparent_title_css($bg){
    	$title_bg = vibe_get_option('title_bg');
    	if(!empty($title_bg) && strlen($title_bg) > 4){
    		 if(filter_var($title_bg, FILTER_VALIDATE_URL)){
    			return 'background:url('.$title_bg.') !important;';
    		}else{
    			return $title_bg;
    		}
    	}
    	return $bg;
    }
    function transparent_title_color($color){
    	$c = vibe_get_option('title_color');
    	if(!empty($c) && strlen($c) > 2){
    		return 'color:'.$c;
    	}
    	return $color;
    }
    //
    
    function wplms_exlude_courses_from_inst_profile($args){
    	if($args['post_type'] == 'course'){
	    	if(is_user_logged_in() && function_exists('bp_is_my_profile') && bp_is_my_profile()){
	    		return $args;
	    	}else{
	    		$excluded_courses=vibe_get_option('hide_courses');
	    		if(!empty($excluded_courses)){
	    			if(!empty($args['post__not_in'])){
			          $args['post__not_in'] = array_merge($args['post__not_in'], $excluded_courses);
			        }else{
			          $args['post__not_in'] = $excluded_courses;  
			        }
	    		}
	    		
	    	}
    	}
    	return $args;
    }
    

    /*
    	BODY  Loading Effect
    */
   
   	function loading_effect($class){
   		$page_loader = vibe_get_option('page_loader');
   		if(!empty($page_loader)){
   			$class[]='loading '.$page_loader;
   		}
   		return $class;
   	}
    /*
    SITE LOCK NEW HOME PAGE
    */
   	function site_lock_url_on_logo($link,$location=null){
    	
   		if(!empty($location) && $location == 'logo'){
   			$enable=vibe_get_option('site_lock');
   			if(!empty($enable)){
	   			$page=vibe_get_option('site_lock_home_page_url');
	   			if(!empty($page)){
	   				$link = get_permalink($page);	
	   			}
   			}
   		}
   		
   		return $link;
   	}
   
   	function add_site_lock_url_on_logo($pages){
   		$page=vibe_get_option('site_lock_home_page_url');
		$pages[]=$page;
		return $pages;
   	}
    /*
    Instructing courses
    */
    function instructing_courses_fix($return){
    	if(function_exists('bp_current_action') && bp_current_action() == 'instructor-courses'){
    		?>
    		<li class="course_single_item">
				<div class="row">
					<div class="col-md-4 col-sm-4">
						<div class="item-avatar instructor-course-avatar" data-id="<?php the_ID(); ?>">
							<?php bp_course_avatar(); ?>
						</div>
					</div>
					<div class="col-md-8 col-sm-8">

						<div class="item">
							<div class="item-title"><?php bp_course_title(); if(get_post_status() != 'publish'){echo '<i> ( '.get_post_status().' ) </i>';} ?></div>
							<div class="item-meta"><?php bp_course_meta(); ?></div>
							<div class="item-action-buttons">
								<?php bp_course_instructor_controls(); ?>
							</div>
							<?php do_action( 'bp_directory_instructing_course_item' ); ?>

						</div>
					</div>
				</div>
			</li>
    		<?php
    		return 1;
    	}

    	return $return;
    }
    /*
    * IMPLEMENTING INSTRUCTOR PRIVACY
    */
   
   	function wplms_instructor_privacy_filter($args=array()){
	    $instructor_privacy = vibe_get_option('instructor_content_privacy');
	    if(isset($instructor_privacy) && $instructor_privacy && !current_user_can('manage_options')){
	        global $current_user;
	        get_currentuserinfo();
	        $args['author'] = $current_user->ID;
	    }
	    return $args;
	}

	/*
	CUSTOM REGISTRATION PAGE
	 */
	function enable_signup($flag){
		if(function_exists('bp_get_signup_allowed')){
			if(bp_get_signup_allowed()){return 1;}
		}
		$custom_registration_page = vibe_get_option('custom_registration_page');
		if(!empty($custom_registration_page)){
			return 1;
		}

		return $flag;
	}

	function registration_link($link){
		$custom_registration_page = vibe_get_option('custom_registration_page');
		if(!empty($custom_registration_page)){
			return get_permalink($custom_registration_page);
		}

		return $link;
	}

	function wplms_instructor_privacy_filter2($args=array()){
	    $instructor_privacy = vibe_get_option('instructor_content_privacy');
	    if(isset($instructor_privacy) && $instructor_privacy && !current_user_can('manage_options')){
	        global $current_user;
	        get_currentuserinfo();
	        if($args['post_type'] != 'product')
	          $args['author'] = $current_user->ID;
	    }
	    return $args;
	}


	function wplms_instructor_privacy_filter_attachments($wp_query){

	  $instructor_privacy = vibe_get_option('instructor_content_privacy');
	  if(empty($instructor_privacy) || current_user_can('manage_options'))
	  return;

	  if ( $wp_query->query['post_type'] != 'attachment' || !current_user_can('edit_posts')) {
	  return;
	  }

	  $user_id = get_current_user_id();
	  $wp_query->set( 'author', $user_id );
	}

	function restrict_draft_courses($flag){
		if(is_user_logged_in()){
			global $post;
			if(current_user_can('edit_posts') && in_array($post->post_status,array('draft','pending')) ){
				$user_id = get_current_user_id();
				$instructors = array($post->post_author);
				$instructors = apply_filters('wplms_course_instructors',$instructors,$post->ID);
				if(!in_array($user_id,$instructors)){
					return 1;
				}
			}
		}

		return $flag;
	}
	/*
		COURSE REVIEWS FIX WHEN COURSE DIRECTORY PERMALINK ACTIVATE
	*/
	function manage_course_reviews($open,$post_id = null){
		if(get_post_type($post_id) == 'course' && isset($_POST['review'])){
			return true;
		}else{
			global $post;
			if($post->post_type == 'course' && $post->comment_status == 'open'){
				return true;
			}
		}
		return $open;
	}
	/*
	REMOVE VERSIONING IF REQUIRED
	 */
	function _remove_script_version( $src ){
		$enabled = vibe_get_option('disable_versioning');
		if(!empty($enabled)){
			$parts = explode( '?ver', $src );
			return $parts[0];
		}
		return $src;
	}
    /*
    * Show Design options in Visual composer
    */
    function display_design_options($x){
    	return true;
    }
    /*
    * DEFAULT ORDERBY IN COURSE CATEGORIES 
    */
    function course_cat_orderby($orderby,$args,$taxonomies){
    	if ( is_admin() || ('course-cat' != $taxonomies[0] || !empty($orderby)))
        	return $orderby;

        $orderby = 'term_group';
    	$args['order'] = 'DESC';

    	return $orderby;
    }

    function course_cat_nav_orderby($args){
    	if(empty($args['orderby'])){
           $args['orderby'] = 'term_group';
           $args['order'] = 'DESC';
       	}
    	return $args;
    }
    
    public function course_cat_columns( $columns ) {
		$new_columns          = array();
		$new_columns['cb']    = $columns['cb'];
		$new_columns['thumb'] = __( 'Image', 'vibe' );
		$new_columns['order'] = __( 'Order', 'vibe' );
		unset( $columns['cb'] );

		return array_merge( $new_columns, $columns );
	}

    public function course_cat_column( $columns, $column, $id ) {

		if ( 'thumb' == $column ) {

			$thumbnail_id = get_term_meta( $id, 'course_cat_thumbnail_id', true );

			if ( $thumbnail_id ) {
				$image = wp_get_attachment_thumb_url( $thumbnail_id );
			} else {
				$image = vibe_get_option('default_avatar');
				if(empty($image)){
					$image = VIBE_URL.'/assets/images/avatar.jpg';
				}
			}
			$image = str_replace( ' ', '%20', $image );
			
			$columns .= '<img src="' . esc_url( $image ) . '" alt="' . esc_attr__( 'Thumbnail', 'vibe' ) . '" class="wp-post-image" height="48" width="48" />';

		}

		if('order' == $column){
			$course_cat_order = get_term_meta($id,'course_cat_order',true);
			if(empty($course_cat_order))
				$course_cat_order = 0;
			$columns .= $course_cat_order;
		}
		return $columns;
	}

	/*
	COMMSSION Display fix in Emails
	 */
	function woocommerce_commission_display($output){
		$output=preg_replace("/(.​*)(commission[0-9]+\:\s?[0-9]+\.?[0-9]{0,9})(.*​)/", " ", $output);
		return  $output;
	}

	function add_directory_class($class){

		$redirect_course_cat_directory = vibe_get_option('redirect_course_cat_directory');
		if(!empty($redirect_course_cat_directory)){
			if(is_tax('course-cat') || is_tax('level') || is_tax('location')){
				if(empty($class)){$class=array();}
				$class[] .= 'directory course';
			}
		}
		return $class;
	}
	/*
	HIDE COURSES FEATURE
	*/

	function hidden_courses($count){
		$excluded_courses=vibe_get_option('hide_courses');
		if(!empty($excluded_courses)){
			$count = $count - count($excluded_courses);
		}
		return $count;
	}

	function wplms_exlude_courses_directroy($args){
	  if($args['post_type'] == 'course'){

	    if(isset($args['meta_query']) && is_array($args['meta_query']) && is_user_logged_in()){
	    	$user_id=get_current_user_id();
	       	foreach($args['meta_query'] as $query){
	         	if(isset($query) && is_array($query)){

	            	if($query['key'] == $user_id){
	              		return $args;
	            	}
	         	}
	       	}
	    }

	    $excluded_courses=vibe_get_option('hide_courses');
	      if(!empty($excluded_courses) && is_array($excluded_courses) && !(isset($args['author']) || isset($args['author_name']))){
	        if(!empty($args['post__not_in'])){
	          $args['post__not_in'] = array_merge($args['post__not_in'], $excluded_courses);
	        }else{
	          $args['post__not_in'] = $excluded_courses;  
	        }
	      } 
	  }
	  return $args;    
	}

	
	function wplms_exlude_courses_from_directroy($query) {
	  if ( !$query->is_admin && $query->is_main_query() && $query->tax_query) {
	    $excluded_courses=vibe_get_option('hide_courses');
	    $query->set('post__not_in', $excluded_courses );
	  }
	  return $query;
	}

	function wplms_exclude_courses_authors($query){
	  if ( !$query->is_admin && $query->is_author && $query->is_main_query()) {
	    $excluded_courses=vibe_get_option('hide_courses');
	    $query->set('post__not_in', $excluded_courses );
	  }
	  return $query;
	}

	/*
	SET DEAFAULT BACKGROUND IMAGE FOR TRANSPARENT HEADER
	 */
    function default_background_image_option($sections){
    	$header_style =  vibe_get_customizer('header_style');
    	if($header_style == 'transparent' || $header_style == 'generic'){
    		$sections[1]['fields'][] = array(
						'id' => 'title_bg',
						'type' => 'text_upload',
						'title' => __('Upload Title Background', 'vibe'), 
						'sub_desc' => __('Upload a background image for title', 'vibe'),
						'desc' => __('Upload title image.', 'vibe'),
                        'std' => VIBE_URL.'/assets/images/title_bg.jpg'
						);
    		$sections[1]['fields'][] = array(
						'id' => 'title_color',
						'type' => 'color',
						'title' => __('Title Color', 'vibe'), 
						'sub_desc' => __('Title text/link color', 'vibe'),
						'desc' => __('Set a Title text color.', 'vibe'),
                        'std' => '#ffffff'
						);
    	}

    	return $sections;
    }
    function specific_title_background($metabox){
    	$header_style =  vibe_get_customizer('header_style');
    	if($header_style == 'transparent' || $header_style == 'generic'){
	    	$metabox[]=array( // Text Input
						'label'	=> __('Title Background Image','vibe'), // <label>
						'desc'	=> __('Add title background image','vibe'), // description
						'id'	=> 'vibe_title_bg', // field id and name
						'type'	=> 'image', // type of field
					);
	    }
    	return $metabox;
    }

    function wplms_xprofile_cover_image( $settings = array() ) {
	    $settings['width']  = 1600;
	    $settings['height'] = 600;
	 
	    return $settings;
	}

    function remove_wpseo_from_buddypress($title){
    	global $bp,$post;
    	
    	if((function_exists('bp_is_directory') && bp_is_directory()) || ( !empty($this->bp_pages) && in_array($post->ID,$this->bp_pages))){
    		$id = vibe_get_bp_page_id(bp_current_component());
    		
			$title = get_post_meta($id,'_yoast_wpseo_title',true);
			if(!empty($title))	{
				return sprintf('%s - %s',$title,get_bloginfo('name'));
			}else{
    			$title = sprintf(_x('%s Directory - %s','Directory Title format','vibe'),ucfirst(bp_current_component()),get_bloginfo('name'));
    		}
    	}

    	if (function_exists('bp_is_user') && bp_is_user()){
    		$title = ucfirst(bp_get_displayed_user_fullname()).' - '.get_bloginfo('name');
    	}
    	if (function_exists('bp_is_group') && bp_is_group()){
    		$title = ucfirst(bp_get_current_group_name()).' - '.get_bloginfo('name');
    	}
    	return $title;
    }



    function get_directory_page_id($component){
    	if(empty($this->bp_pages)){
    		$this->bp_pages = get_option('bp-pages');	
    	}
    	
		if(isset($this->bp_pages[$component])){
			return $this->bp_pages[$component];
		}

    }

   
    function wplms_related_products_args( $args ) {
	  $args['posts_per_page'] = 3; 
	  $args['columns'] = 3;
	  return $args;
	}

	function woocommerce_product_category_class($classes){
		
		if(is_shop()){
			$classes[]='col-md-4 col-sm-6';
		}

		return $classes;
	}


	function bbp_enable_visual_editor( $args = array() ) {
	    $args['tinymce'] = true;
	    return $args;
	}

	function wplms_sidebar_select($sidebar,$id = NULL){
	  if(isset($id)){
	    $selected_sidebar=get_post_meta($id,'vibe_sidebar',true);  
	    if(isset($selected_sidebar) && $selected_sidebar){
	          $sidebar=$selected_sidebar; 
	    }
	  }
	  return $sidebar;
	}


	function vibe_page_builder_content($post_content,$post){
	  	if(get_post_type($post->ID) != 'page')
	    	return $post_content;

	  	$builder_enable = get_post_meta( $post->ID, '_enable_builder', true );
	  	if(!empty($builder_enable)){
		    $builder_layout = get_post_meta( $post->ID, '_builder_settings', true );
		    $add_content = get_post_meta( $post->ID, '_add_content', true );
		  
		        if ( isset($builder_layout) &&  isset($builder_layout['layout_shortcode']) && '' != $builder_layout['layout_shortcode'] && $add_content == 'no') { 
		          $content = $builder_layout['layout_shortcode'];
		        }
		        
		        if ( $builder_layout && '' != $builder_layout['layout_shortcode'] && $add_content == 'yes_top') {
		            $content = $post_content.$builder_layout['layout_shortcode'];
		        }
		        
		        if ( $builder_layout && '' != $builder_layout['layout_shortcode'] && $add_content == 'yes_below') {
		            $content = $builder_layout['layout_shortcode'].$post_content;
		        }
		    $post_content = $content;    
	  	}      
	    return $post_content;
	}

	function vibe_custom_avatar($avatar){
	  	global $bp;
	   	$avatar=vibe_get_option('default_avatar');
	   	if(!isset($avatar) || !$avatar || strlen($avatar)<5)
	    	$avatar = VIBE_URL.'/assets/images/avatar.jpg';
	   	return $avatar;
	}

	function wplms_student_activity($appended){
	  	$student_activity = vibe_get_option('student_activity');
	  	if(!current_user_can('edit_posts') && isset($student_activity) && $student_activity){
	    	$appended .='&user_id='.get_current_user_id();
	  	}
	    
	    return $appended;
	}
	

	function change_avatar_css($class) {
		$class = str_replace("class='avatar", "class='retina_avatar", $class) ;
	  	return $class;
	}

	function vibe_registration_redirect() {
    	$pageid=vibe_get_option('activation_redirect');
    	if($pageid == 'dashboard'){
    		if(defined('WPLMS_DASHBOARD_SLUG'))
    			$link = bp_loggedin_user_domain().WPLMS_DASHBOARD_SLUG;
    	}else if($pageid == 'profile'){
    		if(function_exists('bp_loggedin_user_domain'))
    			$link = bp_loggedin_user_domain();
    	}else if($pageid == 'mycourses'){
    		if(defined('BP_COURSE_SLUG'))
    			$course_link = trailingslashit( bp_loggedin_user_domain() . BP_COURSE_SLUG );
    	}else{
    		$link = get_permalink($pageid);
    	}
    	return $link;
	}

	/*== Certificate CODE Validation === */
	function wplms_get_template_id_from_certificate_code($code){
	  	$codes = explode('-',$code);
	  	$template = intval($codes[0]);
	  	$post_type = get_post_type($template);
	  	if(in_array($post_type,array('page','certificate'))){
	  		return $template;	
	  	}else
	  		return 0;	  	
	}

	function wplms_get_user_id_from_certificate_code($code){
	  	$codes = explode('-',$code);
	  	if(isset($codes[2]) && is_numeric($codes[2])){
	    	$user_id = intval($codes[2]);
	    	$user = get_userdata( $user_id );
			if ( $user === false ) {
				return 0;
			}
	    	return $user_id;
	  	}
	}

	function wplms_get_course_id_from_certificate_code($code){
	  	$codes = explode('-',$code);
	  	if(isset($codes[1]) && is_numeric($codes[1]) && get_post_type($codes[1]) == 'course'){
	    	$course_id = intval($codes[1]);
	    	if(get_post_type($course_id) == 'course')
	    		return $course_id;
	    	else
	    		return 0;
	  	}
	}

	/*
	BBPRESS - BUDDYPRESS USER TOPICS/REPLIES FIX
	 */
	function bbp_bp_user_topics_replies_pagination($args){
		global $bp;
		if(isset($bp) && isset($bp->canonical_stack) && isset($bp->canonical_stack['action_variables']) && is_array($bp->canonical_stack['action_variables']) && $bp->canonical_stack['action_variables'][0] == 'page'){
			$args['paged'] = $bp->canonical_stack['action_variables'][1];
		}
		return $args;
	}

	/*
	BUDDYPRESS - CONDITIONALY BP USER CAN CREATE GROUP FIX
	*/
	function conditionaly_bp_user_can_create_groups($can_create,$restricted){
		$capability=vibe_get_option('group_create');
		if(isset($capability)){
			switch($capability){
				case 2: 
					if(!current_user_can('edit_posts'))
						$can_create  = false;
				break;
				case 3:
					if(!current_user_can('manage_options'))
						$can_create  = false;
				break;
			}
		}

		return $can_create;
	}

}

WPLMS_Filters::init();

function vibe_get_directory_page($component){
	$wf = WPLMS_Filters::init();
	return $wf->get_directory_page_id($component);
}



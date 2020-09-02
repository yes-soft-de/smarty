<?php

 if ( ! defined( 'ABSPATH' ) ) exit;

 class WPLMS_Course_Menu{

 	protected $option = 'wplms_course_menus';
	public static $instance;
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Course_Menu();
        return self::$instance;
    }

    public function __construct(){
    	$this->custom_section = array();
    	$this->course_nav_menus = array();
    	add_filter('wplms_lms_commission_tabs',array($this,'add_course_menu_settings'));
    	add_filter('lms_general_settings',array($this,'generate_course_menu_form'));

    	add_filter('wplms_course_nav_menu',array($this,'get_course_nav_menu_array'),99);
    	add_action('wp_ajax_save_course_menus',array($this,'save_course_menus'));
    	add_action('wp_ajax_reset_course_menus',array($this,'reset_course_menus'));
    	//process menus
    	add_filter('wplms_course_nav_menu',array($this,'process_wplms_course_nav_menu'),99);

    	//restrict access
    	add_action('bp_template_redirect',array($this,'course_nav_access'),99);
    }

    function reset_course_menus(){
    	if ( !isset($_POST['security']) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') || !is_user_logged_in() || !current_user_can('edit_posts')){
                _e('Security check Failed. Contact Administrator.','vibe-customtypes');
                die();
        }
        delete_option($this->option);
        echo _x('Reset Done','','vibe-customtypes');
        die();
    }

    function course_nav_access(){
    	$saved_course_menus = get_option('wplms_course_menus');
    	if(empty($saved_course_menus))
    		return;
    	if(is_user_logged_in() && current_user_can('manage_options'))
    		return;
		if(!is_singular('course'))
			return;

		global $bp;		
		$permalinks = Vibe_CustomTypes_Permalinks::init();
		foreach($saved_course_menus as $key => $menu){
			if((isset($_GET['action']) && $_GET['action'] == $menu['id']) || bp_current_action() == $menu['id'] || (!empty( $bp->unfiltered_uri[2]) && ($bp->unfiltered_uri[2] == $menu['id'] || $bp->unfiltered_uri[2] == trim($permalinks->permalinks[$menu['id'].'_slug'],'/')))){
				if(!$this->check_privacy($menu['privacy']) || !$this->check_course_privacy($menu['course_privacy'])){
					global $post;
					wp_redirect(get_permalink($post->ID));
					exit;
				}
			}
		}
	}

    function bp_course_get_nav_menu(){
	   $defaults = array(
		    '' => 		array(
		                    'id' => 'home',
		                    'label'=>__('Home','vibe-customtypes'),
		                    'action' => '',
		                    'link'=>bp_get_course_permalink(),
		                ),
	      	
	      	'members' => array(
	                        'id'    => 'members',
	                        'label' =>__('Members','vibe-customtypes'),
	                        'can_view' => 1,
	                        'action'=> (empty($nav['members_slug'])?__('members','vibe-customtypes'):$nav['members_slug']),
	                        'link'  =>bp_get_course_permalink(),
	                    ),
	      );

	    if(bp_is_active('activity')){
	      $defaults['activity']= array(
	                'id'    => 'activity',
	                'label' =>__('Activity','vibe-customtypes'),
	                'can_view' => 1,
	                'action'=> (empty($nav['activity_slug'])?__('activity','vibe-customtypes'):$nav['activity_slug']),
	                'link'  =>bp_get_course_permalink(),
	            );
	    }
	    if(class_exists('WPLMS_tips')){
	    	$lms  = WPLMS_tips::init();
		    $lms_settings =  $lms->lms_settings['general'];
		    if(empty($lms_settings['course_curriculum_below_description'])){
		    	$defaults['curriculum'] = array(
		            'id'     => 'curriculum',
		            'label'  =>__('Curriculum','vibe-customtypes'),
		            'can_view' => 1,
		            'action' => (empty($nav['curriculum_slug'])?__('curriculum','vibe-customtypes'):$nav['curriculum_slug']),
		            'link'   => bp_get_course_permalink(),
		        );
		    }
        }

        if(function_exists('bp_is_active') && bp_is_active('groups')){

            $defaults['group'] = array(
                          'id' => 'group',
                          'label'=>__('Group','vibe-customtypes'),
                          'action' => 'group',
                          'can_view' => 1,
                          'external'=>true,
                      );
	    }
	    if ( in_array( 'bbpress/bbpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'bbpress/bbpress.php'))) {
	       
	          $defaults['forum'] = array(
	                        'id' => 'forum',
	                        'label'=>__('Forum','vibe-customtypes'),
	                        'action' => 'forum',
	                        'can_view' => 1,
	                        'external'=>true,
	                    );
	    }
	    
	  	return $defaults;
	}

    function get_course_nav_menu_array($nav){
    	if(is_admin() && isset($_GET['tab']) &&  $_GET['tab'] == 'general' && $_GET['sub'] == 'course_menu'){
    		$this->course_nav_menus = $nav;
    		$this->custom_section = get_option('custom_course_sections');
    		if(!empty($this->custom_section)){
    			foreach ($this->custom_section as $key => $custom_section) {
    				if(!array_key_exists($custom_section->slug,$this->course_nav_menus)){
    					$this->course_nav_menus[$custom_section->slug] = array(
	    						'id'=>$custom_section->slug,
	    						'label' => $custom_section->title,
    						);
    				}
    			}
    		}
	    	
    	}
    	return $nav;
    }

    function add_course_menu_settings($tabs){
    	if(!isset($_GET['tab']) || $_GET['tab'] == 'general'){
    		
	    	$tabs['course_menu'] = _x('Course Menu','configure Course menu in LMS - Settings','vibe-customtypes');
 		}
 		return $tabs;
    }

    function process_wplms_course_nav_menu($navs){
    	$saved_course_menus = get_option($this->option);
    	$final_course_menus = array();
    	if(empty($saved_course_menus))
    		return $navs;
    	$tabs_layout = vibe_get_option('tab_style_course_layout');
    	if(empty($tabs_layout)){
	    	//building home link
	    	foreach ($navs as $key => $value) {
	    		if($key === ''){
	    			$final_course_menus[''] = $value;
	    			break;
	    		}
	    	}

			foreach($saved_course_menus as $saved_course_menu){
				if(!empty($saved_course_menu) && array_key_exists($saved_course_menu['id'], $navs)){
					if($this->check_privacy($saved_course_menu['privacy']) && $this->check_course_privacy($saved_course_menu['course_privacy'])){
						$final_course_menus[$saved_course_menu['id']] = $navs[$saved_course_menu['id']];
					}
				}
			}
		}else{
			$tabs_menu_items = array();
			$tabs_menus = array('home','curriculum');
			$saved_custom_sections = get_option('custom_course_sections');
			if(!empty($saved_custom_sections)){
				foreach($saved_custom_sections as $key => $section){
					$tabs_menus[] = $section->slug;
				}
			}
			$tabs_menus[] = 'reviews';
			foreach($tabs_menus as $tab_menu){
				if(array_key_exists($tab_menu, $navs)){
					
						$final_course_menus[$tab_menu] = $navs[$tab_menu];
				}
			}

			foreach($saved_course_menus as $saved_course_menu){
				if(!empty($saved_course_menu) && array_key_exists($saved_course_menu['id'], $navs) && !array_key_exists($saved_course_menu['id'], $final_course_menus)){
					if($this->check_privacy($saved_course_menu['privacy']) && $this->check_course_privacy($saved_course_menu['course_privacy'])){
						$final_course_menus[$saved_course_menu['id']] = $navs[$saved_course_menu['id']];
					}
				}
			}
		}
		//print_r($final_course_menus);
		return $final_course_menus;
    }

    function save_course_menus(){
    	if ( !isset($_POST['security']) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') || !is_user_logged_in() || !current_user_can('edit_posts')){
                _e('Security check Failed. Contact Administrator.','vibe-customtypes');
                die();
        }
        $course_menus = stripcslashes($_POST['course_menus']);
        $course_menus = json_decode($course_menus,true);
        update_option($this->option,$course_menus);
        echo _x('Saved','','vibe-customtypes');
        die();
    }

 
    function generate_course_menu_form($settings){
    	if(!isset($_GET['sub']) || $_GET['sub'] != 'course_menu')
    		return $settings;
    	//to make wplms_course_nav_menu filter work 
    	ob_start();
    	bp_course_nav_menu();
    	ob_get_contents();
    	ob_get_clean();


    	if(in_array( 'badgeos/badgeos.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'badgeos/badgeos.php'))){
            wp_deregister_script('badgeos-select2');
            wp_dequeue_script('badgeos-select2');
            wp_deregister_script('select2');
            wp_dequeue_script('select2');
            wp_dequeue_style('badgeos-select2-css');
            wp_deregister_style('badgeos-select2-css');
        }
        wp_enqueue_script('course-menu-select2-js',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/js/select2.min.js');
		wp_enqueue_style('course-menu-select2-css',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/css/select2.min.css');
       	$translation_array=array(
            'saving'=> _x('Saving...','saving text in save section button','vibe-customtypes'),
            'reset_confirm' => _x('Are you sure you want to reset settings?' ,'','vibe-customtypes'),
            'reseting' => _x('Resetting...','','vibe-customtypes')
            );
        wp_localize_script( 'course-menu-select2-js', 'course_menu_strings', $translation_array );
        $saved_course_menus = get_option($this->option);
        $flag = 0;
        $this->course_nav_menus = $this->bp_course_get_nav_menu() + $this->course_nav_menus;
        if(!empty($saved_course_menus)){
        	$course_menus_array = $saved_course_menus;
        	$flag = 1;
        }else{
        	$course_menus_array = $this->course_nav_menus;
        }
        
        if(!empty($course_menus_array)){
        	echo '<ul class="course_menus">';
        	foreach ($course_menus_array as $key => $menu) {
        		if($key !== ''){
        			if($flag){
        				$id = $menu['id'];
        			}else{
        				$id = $key;
        			}
        			
        			echo '<li id="'.$id.'"><span class="cmhandle dashicons dashicons-menu"></span><h3>'.$menu['label'].'</h3>';
        			?>
		        	<div class="edit_privacy">
		        	<ul>
			        		<li>
			        		<label for="course_menu_privacy"><?php echo _x('Privacy','','vibe-customtypes')?></label>
			        		<select class="course_menu_privacy">
			        			<?php
			        			$privacies = $this->get_privacy_options();
			        			if(!empty($privacies)){
			        				foreach ($privacies as $key => $value) {
			        					echo '<option value="'.$key.'" '.((!empty($menu['privacy']) && $menu['privacy'] == $key)?'selected':'').'>'.$value.'</option>';
			        				}
			        			}
			        			?>
			        		</select>
			        		</li>
		        		<li>
		        		<label for="course_privacy"><?php echo _x('Visibility on Courses','','vibe-customtypes')?></label>
		        		<select name="course_privacy" class="course_privacy selectcoursecpt" data-cpt="course" data-placeholder="<?php _e('Select Courses','vibe-customtypes'); ?>" multiple>
		        			<?php
		        			if(!empty($menu['course_privacy']) && is_array($menu['course_privacy'])){
		        				foreach($menu['course_privacy'] as $course){
		        					echo '<option value="'.$course.'" selected="selected">'.get_the_title($course).'</option>';
		        				}
		        			}
		        			?>

		        		</select>
		        		<?php echo _x('OR','course menus','vibe-customtypes')?>
		        		<?php echo _x('All courses','course menus','vibe-customtypes')?>
		        		<input type="checkbox" class="all_courses" name="all_courses" value="1" <?php echo ((empty($flag) || (!empty($flag) && $menu['course_privacy'] == 1))?'checked="checked"':'') ?> > 
		        		</li>
		        	</ul>
		        	</div>
		        	<?php
        			echo '<small title="'._x('Edit privacy','','vibe-customtypes').'" class="edit_cm dashicons dashicons-edit"></small></li>';
        		}
        		
        	}

        	echo '</ul>';
        	wp_nonce_field('vibe_security','vibe_security');
        	echo '<button class="cmbutton save_course_menus big button-primary hero">'._x('Save setings','course menu','vibe-customtypes').'</button>
        		<button class="reset_course_menus button cmbutton">'._x('Reset setings','course menu','vibe-customtypes').'</button> ';
        }
        ?>
        <style>
        	ul.course_menus>li{
				padding:15px;
				width:85%;
				background:#fff;
    			position: relative;
    			border-radius: 2px;
			}
			ul.course_menus>li label{
				width:150px;
				display:inline-block;
			}
			ul.course_menus li h3{display: inline;}
			ul.course_menus>li>span{
				cursor: move; 
			    opacity: 0.7;
    			margin-right: 10px;
    		}
			.edit_privacy{
				display: none;
			    width: 100%;
			    padding: 15px 0;
			}
			.edit_cm{
				position: absolute;
			    top: 5px;
			    font-size: 18px;
			    right: 5px;
			    cursor:pointer;
			}
			button.cmbutton.save_course_menus{
			    margin-right: 15px;
			}
        </style>
        <script>
        jQuery(document).ready(function($){
        	$('.edit_cm').click(function(){
        		var $this = $(this);
        		$this.parent().find('.edit_privacy').toggle(300);
        	});

        	$('.reset_course_menus').click(function(){
        		if (confirm(course_menu_strings.reset_confirm)) {
        			$(this).text(course_menu_strings.reseting);
				     $.ajax({
			            type: "POST",
			            url: ajaxurl,
			            data: { action: 'reset_course_menus', 
			                    security: $('#vibe_security').val(),
			                  },
			            cache: false,
			            success: function (html) {
			            	if(html){
			            		location.reload();
			            	}
			            }
			        });

				} else {
				    return false;
				}
        	});
        	$( ".course_menus" ).sortable({'handle':'.cmhandle',axis: "y",containment:"parent"});

        	
        	$('select.course_privacy').each(function(){
        		var $selector = $(this);
        		var cpt = $selector.attr('data-cpt');
        		var placeholder = $selector.attr('data-placeholder');
        		$('select.course_privacy').select2({
		            minimumInputLength: 4,
		            placeholder: placeholder,
		            closeOnSelect: true,
		            allowClear: true,
		            ajax: {
		                url: ajaxurl,
		                type: "POST",
		                dataType: 'json',
		                delay: 250,
		                data: function(term){ 
		                        return  {   action: 'get_admin_select_cpt', 
		                                    security: jQuery('#vibe_security').val(),
		                                    cpt: cpt,
		                                    q: term,
		                                }
		                },
		                processResults: function (data) {
		                    return {
		                        results: data
		                    };
		                },       
		                cache:true  
		            },
		        });
        	});
        	
        	
	        $('.save_course_menus').click(function(){
	        	var $button = $(this);
	        	var default_text = $button.text();
	        	$button.text(course_menu_strings.saving);
	        	var course_menus_settings = [];
		        $('ul.course_menus>li').each(function(){
		        	var $this = $(this);
		        	var course_privacy = '';
		        	if($this.find('.all_courses:checked').val() != null){
		        		course_privacy = 1;
		        	}else{
		        		course_privacy = $this.find('.course_privacy').val();
		        	}
		        	var data = {
		        		id:$this.attr('id'),
		        		label:$this.find('h3').text(),
		        		privacy:$this.find('select.course_menu_privacy').val(),
		        		course_privacy:course_privacy,

		        	};
		        	course_menus_settings.push(data);
		        });
		        console.log(course_menus_settings);
		        $.ajax({
		            type: "POST",
		            url: ajaxurl,
		            data: { action: 'save_course_menus', 
		                    security: $('#vibe_security').val(),
		                    course_menus:JSON.stringify(course_menus_settings),
		                  },
		            cache: false,
		            success: function (html) {
		            	$button.text(html);
		            	setTimeout(function(){
		            		$button.text(default_text);
		            	},2500);
		            }
		        });
	        });
        	
        });
        </script>
        <?php
        return array();
    }

    function check_course_privacy($course_privacy){
    	$return = false;
    	global $post;
    	if(!empty($course_privacy) && !is_array($course_privacy) && is_numeric($course_privacy)){
    		$return =  true;
    	}else{
    		if(is_array($course_privacy) && in_array($post->ID,$course_privacy)){
    			$return =  true;
    		}
    	}
    	return $return;
    }
   
    
    function check_privacy($privacy){
    	$return = false;
    	if(function_exists('bp_course_version')){
    		$version = bp_course_version();	
    	}else{
    		return true;
    	}
    	
    	switch($privacy){

    		case 'everyone':
    			return true;
    		break;
    		case 'students':
    			if( is_user_logged_in())
    				return true;
    		break;
    		case 'course_students':
				$user_id = get_current_user_id();
				$course_id = get_the_ID();
				if(function_exists('wplms_user_course_check')){
					if(is_user_logged_in() && wplms_user_course_check($user_id,$course_id) || (is_user_logged_in() && current_user_can('edit_posts')))
					return true;
				}
			break;
			case 'active_course_students':
				$user_id = get_current_user_id();
				$course_id = get_the_ID();
				if(function_exists('wplms_user_course_active_check')){
					if(is_user_logged_in() && wplms_user_course_active_check($user_id,$course_id) || (is_user_logged_in() && current_user_can('edit_posts')))
					return true;
				}
			break;
    		case 'instructors':
    			if(is_user_logged_in() && current_user_can('edit_posts'))
    				return true;
    		break;
    		case 'admin':
    			if(is_user_logged_in() && current_user_can('manage_options'))
    				return true;
    		break;
    		default:
    			$return = apply_filters('wplms_course_details_check_privacy',false,$privacy);
    		break;
    	}

    	return $return;
    }


    //Function to get privacy Options Starts here
    function get_privacy_options(){
    	$privacy_options = apply_filters('wplms_course_menu_options',array(
						'everyone'=>_x('Everyone','privacy option for course menu','vibe-customtypes'),
						'students'=>_x('Students','privacy option for course menu','vibe-customtypes'),
						'course_students'=>_x('Course Students','privacy option for course menu','vibe-customtypes'),
						'active_course_students'=>_x('Active Course Students','privacy option for course menu','vibe-customtypes'),
						'instructors'=>_x('Instructors','privacy option for course menu','vibe-customtypes'),
						'admin'=>_x('Administrators','privacy option for course menu','vibe-customtypes')
						));

    	return $privacy_options;
    }
    
}
	
add_action('plugins_loaded',function(){
	WPLMS_Course_Menu::init();
},11);
?>
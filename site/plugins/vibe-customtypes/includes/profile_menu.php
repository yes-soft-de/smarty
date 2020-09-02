<?php

 if ( ! defined( 'ABSPATH' ) ) exit;

 class WPLMS_profile_menu{

 	protected $option = 'wplms_profile_menus';
	public static $instance;
    public static function init(){
        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_profile_menu();
        return self::$instance;
    }

    public function __construct(){
    	$this->custom_section = array();
    	$this->profile_nav_menus = array();
    	$this->profile_menus_array_childern =array();
    	add_filter('wplms_lms_commission_tabs',array($this,'add_profile_menu_settings'));
    	add_filter('lms_general_settings',array($this,'generate_profile_menu_form'));
    	add_action('wp_ajax_save_profile_menus',array($this,'save_profile_menus'));
    	add_action('wp_ajax_reset_profile_menus',array($this,'reset_profile_menus'));
    	add_action( 'bp_setup_nav', array($this,'get_profile_menu_object'),9999999 );


		add_action( 'bp_setup_nav', array($this,'bpcodex_change_notifications_nav_position' ),99999999 );

    }

    function bpcodex_change_notifications_nav_position() {
    	$saved_menus = get_option($this->option);
    	if(!empty($saved_menus)){

    		for ($i=(count($saved_menus)-1);$i>=0;$i--) {
    		buddypress()->members->nav->edit_nav( array(				// for parent
	        'position' => $i,
	    	), $saved_menus[$i]['slug'] );

    		foreach ($saved_menus[$i]['children'] as $key => $value) {     // fo children
    			buddypress()->members->nav->edit_nav( array(
			        'position' => $key,
			    ), $value['slug'], $saved_menus[$i]['slug']);
    		}
	    	 
    	}
    	}
    	
		    
	}

    function get_profile_menu_object(){
    	if(!function_exists('buddypress'))
    		return;
    	
        $temp_nav = array();
		  foreach (buddypress()->members->nav->get() as $key => $nav) 
		  	{
		  		if(strpos($key,'/'))
		  		{    
		  		   $get_position=strpos($key,'/');
		  		   $key=substr($key, 0 , $get_position ) ; 
				   $temp_nav[$key]['children'][]=$nav;
		        }
		  		else
		  		{
	  			   $temp_nav[$key]=array(
						'slug'=>$nav->slug,
						'name'=>$nav->name,
						'position'=>$nav->position,
						'default_subnav_slug'=>$nav->default_subnav_slug,
						
					);
		  		}

		  	}
			$this->profile_nav_menus = $temp_nav;
    }

    function reset_profile_menus(){
    	if ( !isset($_POST['security']) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') || !is_user_logged_in() || !current_user_can('edit_posts')){
                _e('Security check Failed. Contact Administrator.','vibe-customtypes');
                die();
        }
        delete_option($this->option);
        echo _x('Reset Done','','vibe-customtypes');
        die();
    }

    function add_profile_menu_settings($tabs){
    	if(!isset($_GET['tab']) || $_GET['tab'] == 'general'){
    		
	    	$tabs['profile_menu'] = _x('Profile Menu','configure Profile menu in LMS - Settings','vibe-customtypes');
 		}
 		return $tabs;
    }

    function save_profile_menus(){
    	if ( !isset($_POST['security']) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_security') || !is_user_logged_in() || !current_user_can('edit_posts')){
                _e('Security check Failed. Contact Administrator.','vibe-customtypes');
                die();
        }
        $profile_menus = stripcslashes($_POST['profile_menus']);
        $profile_menus = json_decode($profile_menus,true);
       
        update_option($this->option,$profile_menus);
        echo _x('Saved','','vibe-customtypes');
        die();
    }

 
    function generate_profile_menu_form($settings){
    	if(!isset($_GET['sub']) || $_GET['sub'] != 'profile_menu')
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
        wp_localize_script( 'course-menu-select2-js', 'profile_menu_strings', $translation_array );
        $saved_profile_menus = get_option($this->option);
        $flag = 0;
       
        if(!empty($saved_profile_menus)){
        	$profile_menus_array = $saved_profile_menus;
        	$flag = 1;
        }else{
        	
        	//default profile menus

        	$profile_menus_array =  $this->profile_nav_menus;
        }
        if(!empty($profile_menus_array)){
        	echo '<ul class="profile_menus">';
        	foreach ($profile_menus_array as $key => $menu) {
        		if($key !== ''){
        			echo '<li class="" id="'.$menu['slug'].'" data-position="'.$menu['position'].'" data-default_subnav_slug="'.$menu['default_subnav_slug'].'" >';
        			echo '<div class="menu_child" id="'.$menu['slug'].'" >
        			<ul class=" profile_menus_child" id="sortable" >';  

/////////////////////////////////////////////////////////////
						/** for nav child**/

		 if(!empty($menu['children'])){
		 		$i = 0;
				foreach ($menu['children'] as  $key1 => $data1){
					if(empty($i)){
						echo '<li class="li_child_view ui-state-disabled" id="'.$data1['slug'].'" position="'.$data1['position'].'">' .$data1['name']. '</li>';
					}else{
						echo '<li  class="li_child_view" id="'.$data1['slug'].'" position="'.$data1['position'].'">' .$data1['name']. '</li>';
					}
			 		$i++;
				}
			}
								
		echo '</ul> </div>';
//////////////////////////////////////////////////////////////////// 

		echo'<span class="cmhandle dashicons dashicons-menu"></span><h3>'.$menu['name'].''.'</h3><span class="menu_list dashicons  dashicons-plus"></span> </li>';


        			?>
		        	<?php
        		}
        		
        	}

        	echo '</ul>';
        	wp_nonce_field('vibe_security','vibe_security');    // 2nd id id and name  //1st is data which will hash
        	echo '<button class="cmbutton save_profile_menus big button-primary hero">'._x('Save setings','course menu','vibe-customtypes').'</button>
        		<button class="reset_profile_menus button cmbutton">'._x('Reset setings','course menu','vibe-customtypes').'</button> ';
        }
        ?>
        <style>
        	.li_child_view
        	{
        		border:1px solid;
        		background: #f1f1f1;
        		padding:5px;
        	}
             .menu_child{
             	display: none;

             }
        	ul.profile_menus>li{
				padding:15px;
				width:85%;
				background:#fff;
    			position: relative;
    			border-radius: 2px;
			}
			ul.profile_menus>li label{
				width:150px;
				display:inline-block;
			}
			ul.profile_menus li h3{display: inline;}
			ul.profile_menus>li>span{
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
			button.cmbutton.save_profile_menus{
			    margin-right: 15px;
			}
        </style>
        <script>
        jQuery(document).ready(function($){
        	$( ".profile_menus" ).sortable({'handle':'.cmhandle',axis: "y",containment:"parent"});
        	$('ul.profile_menus_child').each(function(){
        		var $this = $(this);
        		$this.sortable({axis: "y",containment:"parent",items: 'li:not(.ui-state-disabled)',});
        	});

        	

           $(".menu_list").click(function(){
           	var $this = $(this);
           	$this.parent().find('.menu_child').toggle(500);

           });

   

        	$('.reset_profile_menus').click(function(){
        		if (confirm(profile_menu_strings.reset_confirm)) {
        			$(this).text(profile_menu_strings.reseting);
				     $.ajax({
			            type: "POST",
			            url: ajaxurl,
			            data: { action: 'reset_profile_menus', 
			                    security: $('#vibe_security').val(),
			                  },
			            cache: false,
			            success: function (html) {
			            	
			            	if(html){
			            		// location.reload();
			            	}
			            }
			        });

				} else {
				    return false;
				}
        	});

        	

	        $('.save_profile_menus').click(function(){
	        	var $button = $(this);
	        	var default_text = $button.text();
	        	$button.text(profile_menu_strings.saving);
	        	var profile_menus_settings = [];  
	        	
	        	
		        $('ul.profile_menus>li').each(function(){    /// for child
		        	var children_data =[];
		        	$this = $(this);

		        	$this.find('ul').children().each(function(){
		        		children = {
		        			        content:  $(this).text(),
		        			        slug:  $(this).attr('id'),
		        			        position : $(this).attr('position'),
		        				 };
		        	    children_data.push(children);
		        		
		        	}

		        	);

		        	var data = {
		        		slug:$this.attr('id'),
		        		name:$this.find('h3').text(),
		        		position:$this.data('position'),
		        		default_subnav_slug:$this.data('default_subnav_slug'),
		        		children:children_data,
		        	};
		        	profile_menus_settings.push(data);
		        	children_data=[];
		        });

		        console.log(profile_menus_settings);
		        $.ajax({
		            type: "POST",
		            url: ajaxurl,
		            data: { action: 'save_profile_menus', 
		                    security: $('#vibe_security').val(),
		                    profile_menus:JSON.stringify(profile_menus_settings),
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
    	$privacy_options = apply_filters('wplms_profile_menu_options',array(
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
	WPLMS_profile_menu::init();
},11);
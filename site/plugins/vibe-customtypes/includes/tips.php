<?php
/**
 * TIPS from MUSettings - General section
 *
 * @class       WPLMS_tips
 * @author      VibeThemes
 * @category    Admin
 * @package     Vibe customtypes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPLMS_tips{

	var $settings;
	var $temp;

	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_tips();
        return self::$instance;
    }

	private function __construct(){
		
		$this->lms_settings = get_option('lms_settings');

		if(is_array($this->lms_settings) && isset($this->lms_settings['general'])){
			$this->settings = $this->lms_settings['general'];
			foreach($this->settings as $key=>$setting){
				switch($key){
					case 'guest_user_id':
					if(!empty($this->settings['guest_user_id'])){
						$user = get_userdata($this->settings['guest_user_id'] );
						if($user != false){
							add_filter('wplms_course_non_loggedin_user',array($this,'wplms_guest_popup_filter'));
							add_action('wp_ajax_nopriv_login_as_guest',array($this,'login_as_guest'));
							add_filter('bp_activity_before_save',array($this,'login_as_guest_remove_activity'),99,1);
							add_filter('bp_allow_update_user_quiz_retake_count',array($this,'bp_allow_update_user_quiz_retake_count'),10,4);
							add_filter('wplms_allow_course_progress_record',array($this,'wplms_allow_course_progress_record'),10,3);
							add_filter('wplms_allow_complete_unit',array($this,'wplms_allow_complete_unit'),10,4);
							add_filter('wplms_logged_in_top_menu',array($this,'remove_loggedin_menu'));
							add_filter('wplms_sidebarme_show_view_profile',array($this,'do_not_show_for_guest'));
							//redirect from checkout, profile 
							add_action('template_redirect',array($this,'log_user_out_at_checkout'));
							add_action('template_redirect',array($this,'do_not_allow_profile_but_results'),-1);
						}
					}
					case 'instructor_login_redirect': 
						if(!empty($this->settings['instructor_login_redirect'])){
							if($this->settings['instructor_login_redirect'] == 'same'){
								add_filter('wplms_login_widget_action',array($this,'wplms_login_same_page'));	
							}
							add_filter('login_redirect',array($this,'login_redirect'),100,3);
						}
					break;
					case 'student_login_redirect':
						if(!empty($this->settings['student_login_redirect'])){
							if($this->settings['student_login_redirect'] == 'same'){
								add_filter('wplms_login_widget_action',array($this,'wplms_login_same_page'));
							}
							add_filter('login_redirect',array($this,'login_redirect'),100,3);
						}
					break;
					case 'exclude_not_connected_quiz':
							add_filter('wplms_course_quiz_weightage',array($this,'not_connected_quiz_student_marks'),10,3);
					break;
					
					case 'enable_pre_required_on_evaluation': 
						add_filter('wplms_pre_course_check_status_filter',array($this,'wplms_pre_required_on_evaluation'));
					break;
					case 'hide_course_members':
						add_filter('wplms_course_nav_menu',array($this,'coursenav_remove_members'));
					break;
					case 'course_curriculum_below_description':
						add_filter('wplms_course_nav_menu',array($this,'coursenav_remove_curriculum'));
						add_action('wplms_after_course_description',array($this,'course_curriculum_below_description'));  
					break;
					case 'curriculum_accordion':	
						add_action('wp_footer',array($this,'course_creator_curriculum_accordion'));
					break;
					case 'course_curriculum_unit_description':
						add_action('wplms_curriculum_course_unit_details',array($this,'course_unit_details'),10,1);
						add_filter('wplms_curriculum_course_lesson',array($this,'unit_expander'),10,3);
					break;
					case 'admin_instructor':
						add_filter('wplms_show_admin_in_instructors',array($this,'hide_admin_in_instructor'));
					break;
					case 'unit_quiz_start_datetime':
						add_filter('wplms_unit_metabox',array($this,'show_unit_date_time_backend'));
						add_filter('wplms_front_end_unit_settings',array($this,'add_date_time_field'));
						add_action('wplms_front_end_unit_settings_form',array($this,'show_date_time_field'),10,1);
						add_action('wplms_front_end_save_unit_settings_extras',array($this,'save_unit_extra_settings'),10,1);
						add_filter('wplms_drip_value',array($this,'apply_unit_date_time_drip_feed'),999,4);
					break;
					case 'one_session_per_user':
						add_filter( 'authenticate',array($this,'one_session_per_user'), 30, 3 );
					break;
					case 'disable_ajax':
						add_action('wplms_course_start_after_timeline',array($this,'disable_ajax'),10,1);
						add_filter('wplms_get_course_unfinished_unit',array($this,'load_unit'));
						add_filter('wplms_get_course_unfinished_unit_key',array($this,'unit_key'),10,3);
					break;
					case 'course_codes':
						add_filter('wplms_course_product_metabox',array($this,'course_codes_setting'));
						add_filter('wplms_frontend_create_course_pricing',array($this,'wplms_front_end_course_codes'));
						add_action('wplms_front_end_pricing_content',array($this,'wplms_front_end_show_course_codes'),10,1);
						add_action('wplms_front_end_save_course_pricing',array($this,'wplms_front_end_save_course_codes'),10,1);
						add_action('template_redirect',array($this,'wplms_course_code_check'),1);
						add_action('wplms_course_before_front_main',array($this,'display_course_code_message'));
					break;
					case 'woocommerce_account':
						add_filter('wplms_logged_in_top_menu',array($this,'wplms_woocommerce_orders_link')); 
						add_filter('woocommerce_get_endpoint_url',array($this,'wplms_woocommerce_dashboard_endpoints'),10,4); 
					break;
					case 'wplms_course_delete':
						add_filter('wplms_front_end_course_delete',array($this,'enable_front_end_course_deletion'));
					break;
					case 'disable_autofree':
						add_filter('wplms_auto_subscribe',array($this,'disable_auto_subscribe'));
						add_filter('wplms_private_course_button',array($this,'manual_subscription'),10,2);
						add_filter('wplms_private_course_button_label',array($this,'free_label'),10,2);
						add_action('template_redirect',array($this,'subscribe_free_course'),8);
						add_action('wplms_course_product_id',array($this,'return_blank_for_free'),10,3);
					break;
					case 'user_progress_course_admin':
						add_action('wplms_user_course_admin_member',array($this,'show_progressbar_user'),10,2);
					break;
					case 'default_order':
						add_filter('wplms_course_drectory_default_order',array($this,'default_order'));
					break;
					case 'in_course_quiz':
						add_filter('wplms_in_course_quiz',array($this,'wplms_enable_incourse_quiz'));
					break;
					case 'in_course_quiz_paged':
						add_filter('wplms_incourse_quiz_per_page',array($this,'wplms_incourse_quiz_per_page'));
						add_action('wplms_unit_header',array($this,'wplms_quiz_check'),10,1);
					break;
					case 'show_message_instructor':
						add_filter('wplms_instructor_meta',array($this,'show_message_icon'),10,2);
					break;
					case 'instructor_signup_ninja_form_id':
						add_filter( 'ninja_forms_sub_table_row_actions',array($this,'wplms_ninja_forms_sub_table_row_actions_convert_to_instructor'), 40, 4 );
						add_action('wplms_ninja_forms_change_to_instructor_sub',array($this,'wplms_ninja_forms_change_to_instructor_sub'),10,3);
					break;
					case 'enable_student_menus': 
  						add_action( 'init',array($this,'register_student_menus'));
  						add_filter('wplms-mobile-menu',array($this,'student_mobile_menu'));
  						add_filter('wplms-main-menu',array($this,'student_main_menu'));
  						add_filter('wplms-top-menu',array($this,'student_top_menu'));
					break;
					case 'enable_instructor_menus':
						add_action( 'init',array($this,'register_instructor_menus'));
						add_filter('wplms-mobile-menu',array($this,'instructor_mobile_menu'),100);
  						add_filter('wplms-main-menu',array($this,'instructor_main_menu'),100);
  						add_filter('wplms-top-menu',array($this,'instructor_top_menu'),100);
					break;
					case 'enable_inst_create_course':
						add_action( 'bp_setup_nav',array($this,'create_course_setup_nav_instructor'));
					break;
					case 'enable_forum_privacy':
						add_filter('bbp_has_forums',array($this,'bpp_filter_forums_by_permissions'), 10, 2);
						add_action('bbp_template_redirect', array($this,'bpp_enforce_permissions'), 1);
						add_filter('bbp_after_has_search_results_parse_args',array($this,'bpp_enforce_search_results'),99);
					break;
					case 'remove_finished_course':
							add_filter('wplms_carousel_course_filters',array($this,'remove_finished_course'));
							add_filter('wplms_grid_course_filters',array($this,'remove_finished_course'));
							add_filter('vibe_editor_filterable_type',array($this,'remove_finished_course'));
							add_filter('bp_course_wplms_filters',array($this,'remove_finished_course'));
					break;
					case 'course_coming_soon':

						add_filter('wplms_auto_subscribe',array($this,'disable_free_course_allocation'),10,2);

						add_filter('wplms_course_product_metabox',array($this,'wplms_coming_soon_backend'));
						add_action('wplms_front_end_pricing_content',array($this,'wplms_coming_soon_front_end_pricing'),10,1);
						add_action('wplms_front_end_save_course_pricing',array($this,'save_coming_soon'),10,1);

						add_filter('wplms_course_product_id',array($this,'wplms_coming_soon_link'),10,2);
						add_filter('wplms_course_credits',array($this,'coming_soon_display'),10,2);
						add_filter('wplms_private_course_button_label',array($this,'coming_soon_display'),10,2);
						add_filter('wplms_take_this_course_button_label',array($this,'coming_soon_display'),10,2);
					break;
					case 'course_drip_section':
						add_filter('wplms_drip_value',array($this,'section_wise_drip'),9,4);
					break;
					case 'course_unit_drip_section':
						add_filter('wplms_drip_value',array($this,'unit_wise_drip'),9,4);
					break;
					case 'quiz_passing_score':
						add_filter('wplms_next_unit_access',array($this,'wplms_next_access'),10,2);
						add_filter('wplms_quiz_metabox',array($this,'wplms_quiz_passing_score'),10);
						add_filter('wplms_front_end_quiz_settings',array($this,'quiz_passing_score_control'));
						add_action('wplms_front_end_quiz_settings_action',array($this,'quiz_passing_score_setting'),10,1);
						add_filter('wplms_finish_course_check',array($this,'check_last_quiz_pass_fail'),10,2);
					break;
					case 'quiz_partial_marks':
						add_filter('bp_course_evaluate_question_partial_marking',array($this,'bp_enable_partial_marking_in_quiz'),10,1);
					break;
					case 'quiz_correct_answers':
						add_filter('wplms_show_quiz_correct_answer',array($this,'wplms_show_quiz_correct_answer'),10,2);
					break;
					case 'unit_comments':
						add_filter('wplms_unit_classes',array($this,'wplms_check_unit_comments_filter'));
						add_action('wplms_after_every_unit',array($this,'check_unit_comments_enabled'),9,1); 
					break;
					case 'quiz_negative_marking':
						add_filter('wplms_incorrect_quiz_answer',array($this,'negative_marks_per_question'),10,4);
						add_filter('wplms_quiz_metabox',array($this,'wplms_quiz_negative_marking'),10);
						add_filter('wplms_front_end_quiz_settings',array($this,'negative_marking_control'));
						add_action('wplms_front_end_quiz_settings_action',array($this,'negative_marking_setting'),10,1);
						add_action('wplms_after_question_options',array($this,'skip_question_switch'),10,2);
					break;
					case 'wplms_course_assignments':
						add_action('wplms_curriculum_course_unit_details',array($this,'show_assignments_in_units'),10,1);
					break;
					case 'members_default_order':
						add_filter('bp_ajax_querystring',array($this,'default_members_order'),20,2);
						add_filter('wplms_members_default_order',array($this,'set_default_members_order'));
					break;
					case 'submission_meta':
						add_action('wplms_assignment_submission_meta',array($this,'submission_meta'),10,2);
						add_action('wplms_quiz_submission_meta',array($this,'submission_meta'),10,2);
						add_action('wplms_course_submission_meta',array($this,'submission_meta'),10,2);
					break;
					case 'terms_conditions_in_registration':
						add_action('bp_signup_validate', array($this,'terms_conditions_validation'));
						add_action('bp_before_registration_submit_buttons', array($this,'show_terms_conditions'),1,1);  
					break;
					case 'course_external_link': 
						add_filter('wplms_course_product_metabox',array($this,'course_external_link_setting'));
						add_filter('wplms_course_non_loggedin_user',array($this,'course_external_link'),10,2);
						add_filter('wplms_course_product_id',array($this,'course_external_link'),10,2);
					break;
					case 'revert_permalinks':
						add_filter('wplms_course_nav_menu',array($this,'wplms_course_temporary_permalinks_fix'),99);
						add_filter('wplms_course_admin_slug',array($this,'wplms_course_admin_slug'));
						add_filter('body_class',array($this,'wplms_add_action_body_classes'));
					break;
					case 'vibe_display_course_members':
						add_filter('wplms_course_nav_menu',array($this,'course_nav_members'),99);
						add_action('bp_template_redirect',array($this,'course_nav_members_access'),99);
					break;
					case 'vibe_display_course_curriculum':
						add_filter('wplms_course_nav_menu',array($this,'course_nav_curriculum'),99);
						add_action('bp_template_redirect',array($this,'course_nav_curriculum_access'),99);
					break;
					case 'vibe_display_course_events':
						add_filter('wplms_course_nav_menu',array($this,'course_nav_events'),99);
						add_action('bp_template_redirect',array($this,'course_nav_events_access'),99);
					break;
					case 'vibe_display_course_activity':
						add_filter('wplms_course_nav_menu',array($this,'course_nav_activity'),99);
						add_action('bp_template_redirect',array($this,'course_nav_activity_access'),99);
					break;
					case 'vibe_display_course_drive':
						add_filter('wplms_course_nav_menu',array($this,'course_nav_drive'),99);
						add_action('bp_template_redirect',array($this,'course_nav_drive_access'),99);
					break;
					case 'course_students_quiz':
						add_filter('wplms_start_quiz_button',array($this,'check_course_student'),10,2);
					break;
					case 'wplms_force_admin_approval':
						/*========= INSTRUCTOR PERMISSION ================*/
				        add_action('wplms_front_end_save_course_pricing',array($this,'custom_force_admin_approval'),10,1);
				        add_action('wplms_front_end_save_course_components',array($this,'custom_force_admin_approval'),10,1);
				        add_action('wplms_front_end_save_course_settings',array($this,'custom_force_admin_approval'),10,1);
				        add_action('wplms_course_curriculum_updated',array($this,'custom_force_admin_approval'),10,1);
					break;
					case 'disable_certificate_screenshot':
						add_filter('wplms_certificate_class',array($this,'disable_certificate_screenshot'));
					break;
					case 'assign_free_courses':
						add_action('bp_core_activated_user',array($this,'wplms_activate_free_courses'),99); 
						add_action('user_register',array($this,'wplms_activate_free_courses'),99,1);
					break;
					case 'mark_unit_complete_when_next_unit':
						add_action('wp_footer',array($this,'mark_complete_when_next_unit'));
					break;
					case 'force_free_unit_access':
						add_filter('bp_course_get_full_course_curriculum',array($this,'remove_links'));
					break;
					case 'fix_course_menu_on_scroll':
						add_filter('wp_footer',array($this,'fix_course_menu_on_scroll'));
					break;
					case 'show_course_badge_certificate_popup_in_course_details':
						add_filter('wplms_show_badge_popup_in_course_details',array($this,'show_course_badge_popup_in_course_details'),9999,2);
						add_filter('wplms_show_certificate_popup_in_course_details',array($this,'show_course_certificate_popup_in_course_details'),9999,2);
					break;
					case 'open_popup_for_non_logged_users':
						add_filter('wplms_single_course_content_end',array($this,'open_popup_for_non_logged_users'));
					break;
					case 'open_popup_for_non_logged_users_free':
						add_filter('wplms_single_course_content_end',array($this,'open_popup_for_non_logged_users_free'));
					break;
					case 'calculate_course_duration_from_start_course':
						add_action('wplms_start_course',array($this,'calculate_course_duration_from_start_course'),10,2);
						add_action('wplms_the_course_button',array($this,'extend_course'),10,2);
					break;
					case 'finish_course_auto_trigger':
						add_action('wplms_before_start_course',array($this,'finish_course_auto_trigger'));
					break;
					case 'disable_instructor_display':
						add_filter('wplms_course_search_selects',array ($this,'wplms_instructor_display_in_search'));
						add_filter('wplms_display_instructor',array($this,'wplms_display_instructor'),11);
					break;
					case 'skip_course_status':
						add_filter('wplms_skip_course_status_page',array($this,'wplms_skip_course_status_page'));
					break;
					case 'show_unit_title_course_status':
						add_action('wp_footer',array($this,'show_unit_title_course_status'));
						add_action('wp_ajax_fetch_unit_title',array($this,'fetch_unit_title'));
						add_filter('request',array($this,'filter_course_unit'),10,1);
						add_filter('wplms_get_course_unfinished_unit',array($this,'load_unit'));
					break;
					case 'completed_unit_link_course_status':
						add_filter('wplms_curriculum_course_link',array($this,'wplms_curriculum_course_link'),10,3);
						add_action('template_redirect',array($this,'remove_unit_restriction_hook'),1);
					break;
					case 'randomize_question_options':
						add_filter('wp_footer',array($this,'wplms_randomize_question_options'),10,3);
					break;
					default:
						do_action('wplms_tips_default',$key);
					break;
				}
			}
		}
	}

	function wplms_guest_popup_filter($html){
		global $post;
		if(is_user_logged_in() || $post->post_type != 'course')
			return;
		$free = get_post_meta($post->ID,'vibe_course_free',true);
		if(!(function_exists('vibe_validate') && vibe_validate($free)))
			return;
		ob_start();
		wp_nonce_field('login_as_guest','login_as_guest');

		?>
		<a href="javascript:void();" class="link text-center login_as_guest"><?php echo _x('Or Pursue As Guest','','vibe-customtypes')?></a>
		<script>
			jQuery(document).ready(function($){
				
		        $('body').delegate('.login_as_guest','click',function(){
		        	$(this).append('<i class="fa fa-spinner fa-spin"></i>');
		        	jQuery.ajax({
				        type: "POST",
				        url: ajaxurl,
				        data: { action: 'login_as_guest', 
				                security: $('#login_as_guest').val(),
				              },
				        cache: false,
				        success: function (html) {
				          if(html == 'success'){
				          	window.location.reload();
				          }
				        }
				    });
		        });

		         $('body').delegate('.cancel_login_guest','click',function(){
		        	$.magnificPopup.close();
		        });
			});
		</script>
		<style>
		a.link.text-center.login_as_guest {
		    display: grid;
		    grid-template-columns: 1fr;
		    grid-auto-flow: column dense;
		}
		</style>
		<?php
		$login_link = ob_get_clean();
		return $html.$login_link;
	}

	function login_as_guest(){
		if (!isset($_POST) || !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'login_as_guest')){
	        _e('Security check Failed. Contact Administrator.','vibe-customtypes');
	        die();
   	    }

   	    $user = get_user_by('id', $this->settings['guest_user_id'] );
		$user = apply_filters( 'authenticate', $user, '', '' );
		if(!empty($user) && !is_wp_error($user)){
			$remember = apply_filters('wplms_guest_user_login_remember_me',0,$user);
			wp_set_current_user( $user->ID, $user->user_login );
			wp_set_auth_cookie( $user->ID,$remember );
			do_action( 'wp_login', $user->user_login,$user );
			echo 'success';
		}
   	    die();
	}

	function wplms_allow_complete_unit($flag,$user_id,$course_id,$unit_id){
		if($this->settings['guest_user_id'] == $user_id){
			$flag = 0;
		}
		return $flag;
	}

	function do_not_show_for_guest($bool){
		if(!is_user_logged_in())
			return $bool;
		if($this->settings['guest_user_id'] == get_current_user_id()){
			 $bool = 0;
		}
		return $bool;
	}

	function remove_loggedin_menu($menus){
		if(!is_user_logged_in())
			return $menus;
		if($this->settings['guest_user_id'] == get_current_user_id()){
			 $menus = array();
		}
		return $menus;
	}

	function wplms_allow_course_progress_record($flag,$user_id,$course_id){
		if($this->settings['guest_user_id'] == $user_id){
			$flag = 0;
		}
		return $flag;
	}

	function bp_allow_update_user_quiz_retake_count($flag,$quiz_id,$user_id,$value){
		$internal_flag = apply_filters('wplms_guest_user_allow_update_user_quiz_retake_count',1,$quiz_id,$user_id,$value);
		if($internal_flag && isset($user_id) && $this->settings['guest_user_id']==$user_id){
			$flag =  0;
			
		}
		return $flag;
	}

	function login_as_guest_remove_activity($activity){
		
		$flag = apply_filters('wplms_guest_user_activity_hide_sitewide',1,$activity);
		if($flag && isset($activity->user_id) && $this->settings['guest_user_id']==$activity->user_id){
			//to stop recording activities for guest user
			$activity->type = '';
			$activity->component = '';
			
		}
		return $activity;
	}	

	function log_user_out_at_checkout(){
		if(function_exists('is_checkout') && is_checkout()){
			if(is_user_logged_in() && $this->settings['guest_user_id'] == get_current_user_id()) {
				global $woocommerce;
			    $items = $woocommerce->cart->get_cart();
		        global $post;
		      	wp_logout();
		      	if(!empty($items)){
		      		foreach($items as $item => $values) { 
			            WC()->cart->add_to_cart( $values['data']->get_id() );
			        }
		      	}
		      	
		      	wp_redirect(get_permalink($post->ID));
		      	exit();
		   } 
		}
	}

	function do_not_allow_profile_but_results(){

		if(!is_user_logged_in())
			return;
		if(function_exists('bp_is_my_profile') &&  bp_is_my_profile() && $this->settings['guest_user_id'] == get_current_user_id()){
			if(bp_current_action() != 'course-results'){
				wp_die(_x('Your are a Guest. Guests do not have any profile','','vibe-customtypes'));
			}
			
		}
	}

	function wplms_curriculum_course_link($show_link,$unit_id,$course_id){
	
		if(!is_user_logged_in()){
			return $show_link;
		}

		$user_id = get_current_user_id();
		if(!bp_course_is_member($course_id, $user_id)){
			return $show_link;
		}

		if(bp_course_check_unit_complete($unit_id,$user_id,$course_id)){

			if(!empty($this->settings['show_unit_title_course_status'])){
				$link = get_permalink($course_id);
				$link .= get_post_field('post_name',$unit_id);
			}else{
				$link = get_permalink($unit_id);
			}
			
			return $link;
		}

		return $show_link;
	}

	function remove_unit_restriction_hook(){
	    if(!is_singular(array('unit')))
	      return;
  		$actions = WPLMS_Actions::init();
  		//check if show_unit_title_course_status is not enabled only then remove
  		if(empty($this->settings['show_unit_title_course_status'])){
  			remove_action('template_redirect',array($actions,'vibe_check_access_check'),11);
  		}
	}

	function wplms_randomize_question_options(){
		?>
		 <script>
		    (function(jQuery){
		        jQuery.fn.shuffle = function() {
		          var allElems = this.get(),
		              getRandom = function(max) {
		                  return Math.floor(Math.random() * max);
		              },
		              shuffled = jQuery.map(allElems, function(){
		                  var random = getRandom(allElems.length),
		                      randEl = jQuery(allElems[random]).clone(true)[0];
		                  allElems.splice(random, 1);
		                  return randEl;
		             });
		   
		          this.each(function(i){
		              jQuery(this).replaceWith(jQuery(shuffled[i]));
		          });
		          return jQuery(shuffled);
		        };
		    })(jQuery);

		    jQuery(document).ready(function(){
		      jQuery( 'body' ).delegate( '.in_quiz,#question', 'question_loaded',function(){
		          jQuery('ul.question_options.single').each(function(){
		            var $element = jQuery(this).find('li');
		            $element.shuffle();
		          });
		      });
		    });
		</script>
		<?php
	}

	function filter_course_unit($vars){

		if(empty($vars['course']) || strpos($vars['course'],'/') === false){
			return $vars;
		}
		$name = explode('/',$vars['course']);
		if(empty($name)) return $vars;
		global $wpdb;
		$course_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'course' and post_status = 'publish'",$name[0]));

		
		$unit_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND (post_type = 'unit' OR post_type='quiz') AND post_status='publish'",$name[1]));
		if(empty($course_id) || empty($unit_id) || !is_numeric($unit_id) || !is_numeric($course_id))
			return $vars;
		
		$take_course_page = vibe_get_option('take_course_page');
		echo '<form id="no_ajax_submit" method="post" action="'.get_permalink($take_course_page).'">';
		wp_nonce_field('security','hash');
		echo '<input type="hidden" name="load_unit" value="'.$unit_id.'" />';
		echo '<input type="hidden" name="no_ajax_course_id" value="'.$course_id.'" />
		</form>'; ?>
		<script>
		document.getElementById("no_ajax_submit").submit();
		</script>
		<?php
		die();
	}
	function fetch_unit_title(){

		
		if(is_numeric($_POST['course_id']) && is_numeric($_POST['id'])){
			$title = get_the_title($_POST['id']).' - '.get_the_title($_POST['course_id']);
			if(function_exists('htmlspecialchars_decode')){
				$title = htmlspecialchars_decode($title);
			}
			
			echo json_encode(array(
				'title'=> $title,
				'url'=> get_permalink($_POST['course_id']).get_post_field('post_name',$_POST['id']).'/',
				)); 
		}else{
			echo json_encode(array());
		}
		die();
	}
	function show_unit_title_course_status(){

		$page_id = vibe_get_option('take_course_page');

		if(is_page($page_id)){
		?>
			<script>
			jQuery(document).ready(function($){
				$('.unit_content').on('unit_traverse',function(){
					var html = $('.unit_content').html();
                	var unit=$($.parseHTML(html)).filter("#unit");
					var unit_id = unit.attr('data-unit');
					if(typeof unit_id != 'undefined' && unit_id != null){
						$.ajax({
		                  type: "POST",
		                  url: ajaxurl,
		                  dataType:'json',
		                  data: { action: 'fetch_unit_title', 
		                    course_id: $('#course_id').val(),
		                    id: unit_id
		                  },
		                  cache: false,
		                  success: function (json) {
		                  	if('title' in json){
		                  		document.title = json.title;
	                    		window.history.pushState("","", json.url);
		                  	}
		                  }
		                });
					}
				});
			});
			</script>
		<?php
		}
	}
	/*
	SKIP COURSE STATUS PAGE DESCRIPTION
	*/
	function wplms_skip_course_status_page($f){
		return true;
	}
	/*
	DISABLE INSTRUCTOR DISPLAY
	*/
	function wplms_display_instructor($ins){
		return false;
	}
	function wplms_instructor_display_in_search($string){
        $string = 'instructors=0&cats=1&location=1&level=1';
        return $string;
    }

	/*
	DISABLE CERTIFICATE SCREENSHOT
	 */
	function disable_certificate_screenshot($class){
		$class .=' stopscreenshot';
		return $class;
	}
	/*
	* FORCE Admin Approval for Instructors
	*/
	function custom_force_admin_approval($course_id){
        if(function_exists('vibe_get_option') && !current_user_can('manage_options')){
            $new_course_status = vibe_get_option('new_course_status');
            if(!empty($new_course_status)){
                wp_update_post(array('ID'=>$course_id,'post_status'=>'pending'));
            }
        }
    }

	function check_course_student($link,$quiz_id){
		$course_id = get_post_meta($quiz_id,'vibe_quiz_course',true);
		if(!empty($course_id) && is_user_logged_in() && !current_user_can('edit_post',$quiz_id)){
			$user_id = get_current_user_id();
			if(!bp_course_is_member($course_id)){
				return '<a href="'.get_permalink($course_id).'" class="button create-group-button full"> '.__('Take the Course to Start the Quiz','vibe-customtypes').'</a>';
			}
		}
		return $link;
	}
	/*
	*
	*/
	function remove_finished_course($args){
	  
	  	if($args['post_type'] != 'course' || !is_user_logged_in())
	  		return $args;
  		$user_id = get_current_user_id();
  		$courses = bp_course_get_user_courses($user_id);
  		$finished_courses = array();
  		foreach($courses as $course_id){
  			if(function_exists('bp_course_get_user_course_status')){
  				$finished = bp_course_get_user_course_status($user_id,$course_id);
  				if($finished >= 3){
  					$finished_courses[] = $course_id;
  				}
  			}
  		}
      	if(!empty($finished_courses) && is_array($finished_courses) && !isset($args['author'])){
      		if(!empty($args['post__not_in'])){
      			$args['post__not_in'] = array_merge($args['post__not_in'], $finished_courses);
      		}else{
      			$args['post__not_in'] = $finished_courses;	
      		}
      	} 

  		return $args;    
	}
	/*
	Course pre required will work on evaluation 
	*/
	function wplms_pre_required_on_evaluation($course_id){
		return 3;
	}
	/*
	* Course Nav Members visibility
	*/
	function course_nav_members_access(){
		
		if(!is_singular('course'))
			return;

		global $bp;		
		$permalinks = Vibe_CustomTypes_Permalinks::init();
		
		if((isset($_GET['action']) && $_GET['action'] == 'members') || bp_current_action() == 'members' || (!empty( $bp->unfiltered_uri[2]) && ($bp->unfiltered_uri[2] == 'members' || $bp->unfiltered_uri[2] == trim($permalinks->permalinks['members_slug'],'/')))){
			if(!$this->check_access($this->settings['vibe_display_course_members'])){
				global $post;
				wp_redirect(get_permalink($post->ID));
				exit;
			}
		}
	}
	/*
	* exclude quiz from evaluation which are not connected to course
	*/
	function not_connected_quiz_student_marks($marks,$quiz_id,$course_id){
		$connected = get_post_meta($quiz_id,'vibe_quiz_course',true);
		if(empty($connected)){
			return 0;
		}
		return $marks;
	}
	
	function course_nav_members($menu){
		if(!$this->check_access($this->settings['vibe_display_course_members'])){
			unset($menu['members']);
		}
		return $menu;
	}
	/*
	* Course Nav Curriculum visibility
	*/
	function course_nav_curriculum_access(){
		
		if(!is_singular('course'))
			return;

		global $bp;		
		$permalinks = Vibe_CustomTypes_Permalinks::init();
		
		if((isset($_GET['action']) && $_GET['action'] == 'curriculum') || bp_current_action() == 'curriculum' || ( !empty( $bp->unfiltered_uri[2]) && isset($bp->unfiltered_uri) && ($bp->unfiltered_uri[2] == 'members' || $bp->unfiltered_uri[2] == trim($permalinks->permalinks['curriculum_slug'],'/')))){
			if(!$this->check_access($this->settings['vibe_display_course_curriculum'])){
				global $post;
				wp_redirect(get_permalink($post->ID));
				exit;
			}
		}

		if(!is_user_logged_in() && isset($this->settings['course_curriculum_below_description'])){
		      if(!$this->check_access($this->settings['vibe_display_course_curriculum'])){
		        echo '<style>.course_curriculum {display:none;}</style>';
		        add_filter('bp_course_get_full_course_curriculum',function($curriculum){return false;},10,1);
		        
		      }
		}
	}
	
	function course_nav_curriculum($menu){
		if(!$this->check_access($this->settings['vibe_display_course_curriculum'])){
			unset($menu['curriculum']);
		}
		return $menu;
	}
	/*
	* Course Nav Events visibility
	*/
	function course_nav_events_access(){
		
		if(!is_singular('course'))
			return;

		global $bp;		
		$permalinks = Vibe_CustomTypes_Permalinks::init();
		
		if((isset($_GET['action']) && $_GET['action'] == 'events') || bp_current_action() == 'events' || (!empty($bp->unfiltered_uri) &&  isset($bp->unfiltered_uri[2]) && ($bp->unfiltered_uri[2] == 'events' || (!empty($permalinks->permalinks['events_slug']) && $bp->unfiltered_uri[2] == trim($permalinks->permalinks['events_slug'],'/'))))){
			if(!$this->check_access($this->settings['vibe_display_course_events'])){
				global $post;
				wp_redirect(get_permalink($post->ID));
				exit;
			}
		}
	}
	function course_nav_events($menu){
		if(!$this->check_access($this->settings['vibe_display_course_events'])){
			unset($menu['events']);
		}
		return $menu;
	}/*
	* Course Nav Drive visibility
	*/
	function course_nav_drive_access(){
		
		if(!is_singular('course'))
			return;

		global $bp;		
		$permalinks = Vibe_CustomTypes_Permalinks::init();
		
		if((isset($_GET['action']) && $_GET['action'] == 'drive') || bp_current_action() == 'drive' || (!empty( $bp->unfiltered_uri[2]) && ($bp->unfiltered_uri[2] == 'drive' || (!empty($permalinks->permalinks['drive_slug']) && $bp->unfiltered_uri[2] == trim($permalinks->permalinks['drive_slug'],'/'))))) {
			if(!$this->check_access($this->settings['vibe_display_course_drive'])){
				global $post;
				wp_redirect(get_permalink($post->ID));
				exit;
			}
		}
	}
	function course_nav_drive($menu){
		if(!$this->check_access($this->settings['vibe_display_course_drive'])){
			unset($menu['drive']);
		}
		return $menu;
	}/*
	* Course Nav Members visibility
	*/
	function course_nav_activity_access(){
		
		if(!is_singular('course'))
			return;

		global $bp;		
		$permalinks = Vibe_CustomTypes_Permalinks::init();
		
		if((isset($_GET['action']) && $_GET['action'] == 'activity') || bp_current_action() == 'activity' || (!empty( $bp->unfiltered_uri[2]) && ($bp->unfiltered_uri[2] == 'activity' || $bp->unfiltered_uri[2] == trim($permalinks->permalinks['activity_slug'],'/')))){
			if(!$this->check_access($this->settings['vibe_display_course_activity'])){
				global $post;
				wp_redirect(get_permalink($post->ID));
				exit;
			}
		}
	}
	function course_nav_activity($menu){
		if(!$this->check_access($this->settings['vibe_display_course_activity'])){
			unset($menu['activity']);
		}
		return $menu;
	}
	function check_access($var){
		switch($var){
			case 1:
				if(is_user_logged_in())
					return true;
			break;
			case 2:
				if((is_user_logged_in() && bp_course_is_member()) || current_user_can('manage_options')){
					return true;
				}else{
					$user_id = get_current_user_id();
					global $post;
					if(is_object($post)){
						if($post->post_type == 'course'){
							$authors = apply_filters('wplms_course_instructors',array($post->post_author),$post->ID);
							if(!in_array($user_id,$authors)){
								return false;
							}
						}
					}
				}
			break;
			case 3:
				if(is_user_logged_in() && current_user_can('edit_posts')){
					$user_id = get_current_user_id();
					global $post;
					if(is_object($post) && !current_user_can('manage_options')){
						if($post->post_type == 'course'){
							$authors = apply_filters('wplms_course_instructors',array($post->post_author),$post->ID);
							if(!in_array($user_id,$authors)){
								return false;
							}
						}
					}
					return true;
				}
			break;
			default:
				return true;
			break;
		}
		return false;
	}
	/*
	* REVERT PERMALINKS
	*/
	function wplms_course_temporary_permalinks_fix($nav){

	  	foreach($nav as $key=>$element){
	  		if(empty($nav[$key]['external']))
		  		$nav[$key]['link'] = '?action='.$key.'&';
	  	}
	  	
	  return $nav;
	}
	function wplms_course_admin_slug($slug){
		return '?action=admin&';
	}
	function wplms_add_action_body_classes($classes){
		if(is_singular('course')){
		    $action = '';
		    if(function_exists('bp_current_action')){
		      $action = bp_current_action();
		    }
		    if(empty( $action ) && !empty($_GET['action'])){
		      $action = $_GET['action'];
		    }
		    $classes[]= $action ;
		}
		return $classes;
	}
	/*
    * Checks for Terms and conditions check.
    */
	function terms_conditions_validation(){
		global $bp;
		if(empty($this->settings['terms_conditions_in_registration']))
			return;
        $custom_field = $_POST['terms_conditions'];
        
        if (empty($custom_field) || $custom_field == '') {
            $bp->signup->errors['terms_conditions'] = __('Please Check Terms & Conditions','vibe-customtypes');
        }
        return;
	}
	/*
    * Add Terms and Conditions in Registration page
    * select page in Musettings
    * Gets content from the page and displays on registration page
    */
	function show_terms_conditions(){
		if(empty($this->settings['terms_conditions_in_registration']))
			return;

		$page_id = $this->settings['terms_conditions_in_registration'];

		if(function_exists('icl_object_id')){
	        $page_id = icl_object_id($page_id, 'page', true);
	    }

        $content = get_post_field('post_content',$page_id);
		echo '<div class="terms_conditions_container">
            <h3><strong>'.get_the_title($page_id).'</strong></h3>
            <div class="terms-and-conditions-container">';
        echo apply_filters('the_content',$content);
        echo '</div>';
        do_action( 'bp_terms_conditions_errors' );
        echo '<input type="checkbox" name="terms_conditions" id="bph_field" value="1" /> <strong>'.__(' I agree to these Terms and Conditions','vibe-customtypes').'</strong>
        </div><style>.terms_conditions_container{clear:both;margin:20px 0}.terms-and-conditions-container{width:100%;margin:15px 0;border:1px solid rgba(0,0,0,0.05);height:120px;padding:10px;overflow-y:scroll;}</style>';    
	}
    /*
    * Disable Unit Comments
    * Per Paragraph notes and discussion
    * Simple Notes and Discussion
    */
    function wplms_check_unit_comments_filter($unit_class){
	  global $post;
	  if($post->comment_status != 'open'){
	    $unit_class .= ' stop_notes';
	  }
	  return $unit_class;
	}

	function check_unit_comments_enabled($unit_id){ 
	  $comment_status = get_post_field('comment_status',$unit_id);
	  if($comment_status != 'open'){
	      remove_action('wplms_after_every_unit','wplms_show_notes_discussion',10,1);
	  }
	}

	/*
    * Disable Ajax in Units
    * Visual Composer compatibility
    */
	function disable_ajax($course_id){
		$take_course_id = vibe_get_option('take_course_page');
		if(function_exists('icl_object_id')){
			$take_course_id = icl_object_id($take_course_id,'page',true);
		}
		$permalink = get_permalink($take_course_id);
		echo '<form id="no_ajax_submit" method="post" action="'.$permalink.'">
		<input type="hidden" name="no_ajax_course_id" value="'.$course_id.'" />
		</form>'; ?>
		<script>
		jQuery(document).ready(function($){
			$(".unit").click(function(event){
				event.preventDefault();
				var security = $("#hash").clone();
				$("#no_ajax_submit").append(security);
				var unit_id=$(this).attr('data-unit');
				$("#no_ajax_submit").append('<input type="hidden" name="load_unit" value="'+unit_id+'" />');
				$("#no_ajax_submit").submit();
				event.stopPropagation();
			});
		});
		</script>
		<?php
	}

	function load_unit($unit_id){ 
		if(empty($_POST['load_unit']))
			return $unit_id;

		$uid= $_POST['load_unit'];
		$course_id = $_POST['no_ajax_course_id'];
		if ( !isset($_POST['hash']) || !wp_verify_nonce($_POST['hash'],'security') || !is_numeric($uid) || !is_numeric($course_id)){
	        _e('Security check Failed. Contact Administrator.','vibe-customtypes');
	        die();
   	    }else{
   	    	$units = bp_course_get_curriculum_units($course_id);
  			if(in_array($uid,$units)){ 
   	    		return $uid;
   	    	}
   	    }
		return $unit_id;
	}

	function unit_key($key,$unit_id,$course_id){
		if($unit_id == $_POST['load_unit']){
			$units = bp_course_get_curriculum_units($course_id);
			$key = array_search($unit_id,$units);
			$key++;
		}
		return $key;
	}

	function submission_meta($user_id,$id){
		global $bp,$wpdb;

		$post_type = get_post_type($id);
		$meta_type = '';
		switch($post_type){
			case 'quiz':
				$meta_type = 'submit_quiz';
			break;
			case 'wplms-assignment':
				$meta_type = 'assignment_submit';
			break;
			case 'course':
				$meta_type = 'course_submit';
			break;
		}
		if(!empty($meta_type)){
			$meta = $wpdb->get_var($wpdb->prepare("SELECT date_recorded FROM {$bp->activity->table_name} WHERE user_id = %d AND type = %s AND item_id = %d ORDER BY date_recorded DESC LIMIT 0,1",$user_id,$meta_type,$id));
			if(!empty($meta)){
				echo human_time_diff(strtotime($meta),time());
			}
		}
	}
	function set_default_members_order($order){
		return $this->settings['members_default_order'];
	}
	function default_members_order($string,$object){
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
			if(!isset($_COOKIE['bp-members-filter'])) { // Filter cookie not set
				if ( bp_is_active( 'members' ) ){
					$string.='&type='.$this->settings['members_default_order'];
		    	}
		    }
    	}
		return $string;
	}
	function show_assignments_in_units($lesson){
		$assignments = vibe_sanitize(get_post_meta($lesson['id'],'vibe_assignment',false));
		if(isset($assignments) && is_array($assignments) && count($assignments)){
			foreach($assignments as $assignment){
				$duration = get_post_meta($assignment,'vibe_assignment_duration',true);
				$assignment_duration_parameter = apply_filters('vibe_assignment_duration_parameter',86400,$assignment);
				$cal_d = $duration*$assignment_duration_parameter;
				$days = floor($cal_d/86400);
				$hours = floor(($cal_d%86400)/24);
				$minutes = floor((($cal_d%86400)/24)/60);
				?>
				<tr class="course_lesson">
					<td class="curriculum-icon"><i class="fa fa-paperclip"></i></td>
					<td colspan="2"><?php echo get_the_title($assignment); ?></td>
					<td><span class="time"><i class="fa fa-clock-o"></i><?php echo ($duration > 9998 ? _x('UNLIMITED','vibe-customtypes','assignment duration in curriculum') : (empty($days)?'':$days.', ').(empty($hours)?'00':sprintf('%1$02d',$hours)).':'.(empty($minutes)?'00':sprintf('%1$02d',$minutes))); ?></span></td>
				</tr>
				<?php
			}
		}
	}

	function wplms_next_access($access,$quiz_id){
		$course_id = bp_course_get_unit_course_id($quiz_id);
		$nextunit_access = get_post_meta($course_id,'vibe_course_prev_unit_quiz_lock',true);
		if(get_post_type($quiz_id) == 'quiz' && vibe_validate($nextunit_access)){
			$user_id = get_current_user_id();
			$marks = get_post_meta($quiz_id,$user_id,true);
			$passing_marks = get_post_meta($quiz_id,'vibe_quiz_passing_score',true);
			
			if($marks < $passing_marks)
				return false;
		}
		return $access;
	}
	
	function wplms_quiz_passing_score($metabox){
		foreach($metabox as $key=>$value){
			if($key == 'vibe_quiz_questions'){
				$newmetabox['vibe_quiz_passing_score'] = array( // Text Input
					'label'	=> __('Quiz passing marks/score','vibe-customtypes'), // <label>
					'desc'	=> __('Passing marks/score for quiz. Combined with Prev.Unit/Quiz lock the user progress can be restricted.','vibe-customtypes'), // description
					'id'	=> 'vibe_quiz_passing_score', // field id and name
					'type'	=> 'text', // type of field
					'std'   => 0
				);
			}
			$newmetabox[$key] = $value;
		}
		return $newmetabox; 
	}

	function quiz_passing_score_setting($setting){
		?>
		<li><label><?php _e('SET QUIZ PASSING SCORE','vibe-customtypes'); ?></label>
            <input type="text" class="small_box vibe_extras" id="vibe_quiz_passing_score" value="<?php echo $setting['vibe_quiz_passing_score']; ?>" />
        </li>
		<?php
	}
	function quiz_passing_score_control($settings){
		$value = get_post_meta(get_the_ID(),'vibe_quiz_passing_score',true);
		 if(is_numeric($value))
		 	$settings['vibe_quiz_passing_score'] = $value;
		 else
		 	$settings['vibe_quiz_passing_score'] = 0;
		return $settings;
	}

	function check_last_quiz_pass_fail($flag,$course_curriculum){

		$last=end($course_curriculum);
		
		if(!is_numeric($last))
			return $flag;
		
		$user_id=get_current_user_id();
		$pass_marks = get_post_meta($last,'vibe_quiz_passing_score',true);
		$user_marks=get_post_meta($last,$user_id,true);
		if(get_post_type($last)=='quiz' && isset($pass_marks) && isset($user_marks) && $pass_marks>$user_marks ){
			return $last;
		}
		return $flag;
	}

	function bp_enable_partial_marking_in_quiz($flag){
		return 1;
	}

	function negative_marks_per_question($marks,$quiz_id,$user_answer,$question_id){
		if(is_numeric($quiz_id) && isset($user_answer)){
			$nmarks = get_post_meta($quiz_id,'vibe_quiz_negative_marks_per_question',true);
			if(isset($nmarks) && $nmarks)
				$marks = -1*$nmarks;
		}
		return $marks;
	}

	function negative_marking_setting($setting){
		?>
		<li><label><?php _e('NEGATIVE MARKS PER QUESTION','vibe-customtypes'); ?></label>
            <input type="text" class="small_box vibe_extras" id="vibe_quiz_negative_marks_per_question" value="<?php echo $setting['vibe_quiz_negative_marks_per_question']; ?>" />
        </li>
		<?php
	}
	function negative_marking_control($settings){
		$value = get_post_meta(get_the_ID(),'vibe_quiz_negative_marks_per_question',true);
		 if(is_numeric($value))
		 	$settings['vibe_quiz_negative_marks_per_question'] = $value;
		 else
		 	$settings['vibe_quiz_negative_marks_per_question'] = 0;
		return $settings;
	}

	function skip_question_switch($type,$question_id){
		echo '<a class="clear_question_marked_answer" data-id="'.$question_id.'" title="'.__('Clear marked answer','vibe-customtypes').'"><i class="fa fa-trash"></i></a>';
			?><script>
				jQuery('.clear_question_marked_answer').on('click',function(){
				var id = jQuery(this).attr("data-id");
		      	localStorage.removeItem(id);
		      	jQuery('.quiz_question[data-qid="'+id+'"]').parent().removeClass("done");
		      	jQuery('.question_options input[type=radio]:checked').removeAttr('checked');
		      	jQuery('.question_options input[type=checkbox]:checked').removeAttr('checked');
		      	jQuery('#question .form_field').val('');
		      	jQuery('.question.select select').val('');
		      	
		  	});
		  	</script>
	  	<?php
	}

	function wplms_quiz_negative_marking($metabox){
		foreach($metabox as $key=>$value){
			if($key == 'vibe_quiz_questions'){
				$newmetabox['vibe_quiz_negative_marks_per_question'] = array( // Text Input
					'label'	=> __('Negative Marks per Question','vibe-customtypes'), // <label>
					'desc'	=> __('Deduct marks for a Wrong answer.','vibe-customtypes'), // description
					'id'	=> 'vibe_quiz_negative_marks_per_question', // field id and name
					'type'	=> 'text', // type of field
					'std'   => 0
				);
			}
			$newmetabox[$key] = $value;
		}
		
		return $newmetabox;
	}
	function wplms_show_quiz_correct_answer($return,$quiz_id){ 
		$course_id = get_post_meta($quiz_id,'vibe_quiz_course',true);
		if(is_numeric($course_id)){
			$user_id = get_current_user_id();
			$course_status = bp_course_get_user_course_status($user_id,$course_id);

			if($course_status >= 3){
				return true;
			}else{
				return false;
			}
		}
		return $return;
	}
	function section_wise_drip($value,$pre_unit_id,$course_id,$unit_id){

		$curriculum = bp_course_get_curriculum($course_id);

		$user_id = get_current_user_id();
		$drip_duration = get_post_meta($course_id,'vibe_course_drip_duration',true);


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
				if(is_numeric($curriculum[$i]) && get_post_type($curriculum[$i]) == 'unit') 
					break;
			}

			if($i == $k2){
				return 0; // section drip feed disabled if a section has all quizzes
			}
			$start_section_timestamp = get_post_meta($curriculum[$i],$user_id,true);
			$drip_duration_parameter = apply_filters('vibe_drip_duration_parameter',86400);
            $value = $start_section_timestamp + $drip_duration*$drip_duration_parameter;
			
			
		}
		return $value;
	}

	function unit_wise_drip($value,$pre_unit_id,$course_id,$unit_id){
		$user_id = get_current_user_id();
		$duration = get_post_meta($pre_unit_id,'vibe_duration',true);
		$unit_duration_parameter = apply_filters('vibe_unit_duration_parameter',60,$pre_unit_id);
		$preunit_access_timestamp = get_post_meta($pre_unit_id,$user_id,true);
		if(!empty($preunit_access_timestamp)){
			$value = $preunit_access_timestamp+$duration*$unit_duration_parameter;
		}
		return $value;
	}

	function wplms_coming_soon_link($pid,$course_id){
		$coming_soon = get_post_meta($course_id,'vibe_coming_soon',true);
		if(vibe_validate($coming_soon)){
			return '#';
		}
		return $pid;
	}
	function coming_soon_display($credits,$course_id){
		$coming_soon = get_post_meta($course_id,'vibe_coming_soon',true);
		if(vibe_validate($coming_soon)){
			return '<strong><span class="coming_soon">'.__('COMING SOON','vibe-customtypes').'</span></strong>';
		}
		return $credits;
	}
	
	function disable_free_course_allocation($auto,$course_id){
		$coming_soon = get_post_meta($course_id,'vibe_coming_soon',true); 
		if(vibe_validate($coming_soon)){
			return false;
		}
		return $auto;
	}

	function wplms_coming_soon_backend($metabox){
		$metabox[] = array( // Text Input
					'label'	=> __('Coming soon Mode','vibe-customtypes'), // <label>
					'desc'	=> __('Enable Coming soon mode','vibe-customtypes'), // description
					'id'	=> 'vibe_coming_soon', // field id and name
					'type'	=> 'yesno', // type of field
			        'options' => array(
			          array('value' => 'H',
			                'label' =>'Hide'),
			          array('value' => 'S',
			                'label' =>'Show'),
			        ),
			        'std'   => 'H'
				);
		return $metabox;
	}
	function save_coming_soon($course_id){
		if(isset($_POST['vibe_coming_soon']) && in_array($_POST['vibe_coming_soon'],array('H','S')) && is_numeric($course_id)){
			update_post_meta($course_id,'vibe_coming_soon',$_POST['vibe_coming_soon']);
		}
	}

	function wplms_coming_soon_front_end_pricing($course_id){

		if(isset($course_id) && $course_id){
			$vibe_coming_soon = get_post_meta($course_id,'vibe_coming_soon',true);	
		}else{
			$vibe_coming_soon = 'H';
		}
		
		echo '<li>
                <h3>'.__('Coming Soon mode','vibe-customtypes').'<span>
                    <div class="switch coming_soon">
                            <input type="radio" class="switch-input vibe_coming_soon" name="vibe_coming_soon" value="H" id="disable_coming_soon" '; checked($vibe_coming_soon,'H'); echo '>
                            <label for="disable_coming_soon" class="switch-label switch-label-off">'.__('Disable','vibe-customtypes').'</label>
                            <input type="radio" class="switch-input vibe_coming_soon" name="vibe_coming_soon" value="S" id="enable_coming_soon" '; checked($vibe_coming_soon,'S'); echo '>
                            <label for="enable_coming_soon" class="switch-label switch-label-on">'.__('Enable','vibe-customtypes').'</label>
                            <span class="switch-selection"></span>
                          </div>
                </span></h3>
            </li>
            ';
	}

	function bpp_filter_forums_by_permissions($args = ''){
		
		if(current_user_can('edit_posts'))
			return $args;

		$bbp = bbpress();
	    // Setup possible post__not_in array
	    $post_stati[] = bbp_get_public_status_id();

	    // Check if user can read private forums
	    if (current_user_can('read_private_forums'))
	        $post_stati[] = bbp_get_private_status_id();

	    // Check if user can read hidden forums
	    if (current_user_can('read_hidden_forums'))
	        $post_stati[] = bbp_get_hidden_status_id();

	    // The default forum query for most circumstances
	    $meta_query = array(
	        'post_type' => bbp_get_forum_post_type(),
	        'post_parent' => bbp_is_forum_archive() ? 0 : bbp_get_forum_id(),
	        'post_status' => implode(',', $post_stati),
	        'posts_per_page' => get_option('_bbp_forums_per_page', 50),
	        'orderby' => 'menu_order',
	        'order' => 'ASC'
	    );

	    //Get an array of IDs which the current user has permissions to view
	    $allowed_forums = $this->bpp_get_restricted_forum_ids();

	    // The default forum query with allowed forum ids array added
	    $meta_query['post__not_in'] = $allowed_forums;

	    $bbp_f = bbp_parse_args($args, $meta_query, 'has_forums');

	    // Run the query
	    $bbp->forum_query = new WP_Query($bbp_f);

	    
	    return apply_filters('bpp_filter_forums_by_permissions', $bbp->forum_query->have_posts(), $bbp->forum_query);
	}

	function bpp_get_restricted_forum_ids(){
		
		global $wpdb;
		$forum_ids = array();
		//User courses
		if(is_user_logged_in()){
			$user_id = get_current_user_id();
			$query = apply_filters('bp_course_restrcited_forums_query',$wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = '%s' AND post_status = '%s' AND ID  NOT IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %d )",'course','publish',$user_id));
			
			$course_ids_array = $wpdb->get_results($query,ARRAY_A);
		}else{
			$course_ids_array = $wpdb->get_results($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s",'course','publish'),ARRAY_A);
		}

		if(is_array($course_ids_array)){
			foreach($course_ids_array as $course_id){
				if(isset($course_id['ID'])){
					$forum_id = get_post_meta($course_id['ID'],'vibe_forum',true);
					if(is_numeric($forum_id)){
						$forum_ids[] = $forum_id;
					}
				}
			}
			return $forum_ids;
		}else{
			return false;
		}
	}

	function bpp_get_permitted_subforums($forum_list){
			$filtered_forums = array();
			foreach ($forum_list as $forum) {
				$forum_id = $forum->ID;
				$permitted_forums = $this->bpp_get_permitted_forum_ids();
				if(in_array($forum_id,$permitted_forums))
				{
					array_push($filtered_forums,$forum);
				}
			}
			
			return (array) $filtered_forums;
	}
	function bpp_enforce_permissions(){
		
	    global $post;
	    // Bail if not viewing a single item or if user has caps
	    if (!is_singular() || bbp_is_user_keymaster() || current_user_can('read_hidden_forums') || bbp_is_forum_archive() || bp_is_my_profile())
	        return;

	    if (!$this->bpp_can_user_view_post($post->ID)) { 
	        if (!is_user_logged_in()) { 
	        	if(is_numeric($this->temp)){
	        		$link =get_permalink($this->temp).'?error=not-accessible';
	        		wp_redirect($link,'302');
	        		exit();
	        	}else{
	        		auth_redirect();
	        	}
	        }else {
	        	if(is_numeric($this->temp)){
	        		wp_redirect(get_permalink($this->temp).'?error=not-accessible','302');
	        		exit;
	        	}else{
	        		bbp_set_404();
	        	}
	        }
	    }

	}
	
	function bpp_enforce_search_results($query){
		if(!function_exists('bbp_get_reply_post_type') || (!function_exists('bbp_is_search') || !bbp_is_search()))
			return $query;

		if (is_admin() || bbp_is_user_keymaster() || current_user_can('read_hidden_forums'))
			return $query;
		$restricted_forums = $this->bpp_get_restricted_forum_ids();
		$query['post_parent__not_in']= $restricted_forums;
		$query['post__not_in']= $restricted_forums;
		$disable_replies = apply_filters('wplms_course_forum_privacy_disable_replies_in_search',1);
		if($disable_replies){
			$post_types = $query['post_type'];
			$key = array_search(bbp_get_reply_post_type(), $post_types);
			if($key !== false) {
				unset($post_types[$key]);
			}
			$query['post_type'] = $post_types;
		}
		return $query;
	}

	function bpp_can_user_view_post($post_id){
		global $wpdb;

		if(current_user_can('manage_options'))
			return true;

		$user_id = get_current_user_id();
		$parents = get_post_ancestors( $post_id );
		$id = ($parents) ? $parents[count($parents)-1]: $post_id;
		
		$course_id = $wpdb->get_var($wpdb->prepare("SELECT m.post_id as post_id FROM {$wpdb->posts} as p LEFT JOIN {$wpdb->postmeta} as m ON p.ID = m.post_id WHERE p.post_type = 'course' AND p.post_status = 'publish' and m.meta_key = %s AND m.meta_value = %d",'vibe_forum',$id));
		
		if(empty($course_id) || get_post_field('post_author',$course_id) == $user_id)
			return true;

		$post_type = get_post_type($course_id);
		if($post_type == 'course'){

		}else if($post_type == 'unit'){
			if(function_exists('bp_course_get_unit_course_id')){
            	$course_id = bp_course_get_unit_course_id($id);
          	}
		}else{
			$course_id = 0;
		}
		
		if(empty($course_id))
			return true;

		$this->temp = $course_id;
		
		$var = wplms_user_course_check($user_id,$course_id);
		return empty($var)?false:true;
	}
	
	function student_mobile_menu($args){
		if(is_user_logged_in()){
			$args['theme_location'] = 'student-mobile-menu';
		}
		return $args;
	}
	function student_main_menu($args){
		if(is_user_logged_in()){
			$args['theme_location'] = 'student-main-menu';
		}
		return $args;
	}
	function student_top_menu($args){
		if(is_user_logged_in()){
			$args['theme_location'] = 'student-top-menu';
		}
		return $args;
	}
	function instructor_mobile_menu($args){
		if(is_user_logged_in() && current_user_can('edit_posts')){
			$args['theme_location'] = 'instructor-mobile-menu';
		}
		return $args;
	}
	function instructor_main_menu($args){
		if(is_user_logged_in() && current_user_can('edit_posts')){
			$args['theme_location'] = 'instructor-main-menu';
		}
		return $args;
	}
	function instructor_top_menu($args){
		if(is_user_logged_in() && current_user_can('edit_posts')){
			$args['theme_location'] = 'instructor-top-menu';
		}
		return $args;
	}
    function register_student_menus() {
	    register_nav_menus(
    	    array(
	            'student-top-menu' => __( 'Top Menu for Students','vibe-customtypes' ),
	            'student-main-menu' => __( 'Main Menu for Students','vibe-customtypes' ),
	            'student-mobile-menu' => __( 'Mobile Menu for Students','vibe-customtypes' ),
	            )
        );
    }
    function register_instructor_menus() {
	    register_nav_menus(
    	    array(
	            'instructor-top-menu' => __( 'Top Menu for Instructor','vibe-customtypes' ),
	            'instructor-main-menu' => __( 'Main Menu for Instructor','vibe-customtypes' ),
	            'instructor-mobile-menu' => __( 'Mobile Menu for Instructor','vibe-customtypes' ),
	            )
        );
    }

    /* ===== Enable create course link in instrcutor profile ====== */
    function create_course_setup_nav_instructor(){
	    global $bp;
		$check =0;
	    if(bp_is_my_profile() && current_user_can('edit_posts')){
	       $check =1;
	       bp_core_new_nav_item( array(
	           'name' => __('Create a Course', 'vibe-customtypes' ),
	           'slug' => 'create-course',
	           'position' => 100,
	           'screen_function' => array($this,'wplms_create_course_redirect'),
	           'show_for_displayed_user' => $check
	         ) );
	     }  
	} 
	function wplms_create_course_redirect(){
	  $course_create = vibe_get_option('create_course');


	  if(function_exists('icl_object_id'))
            $course_create = icl_object_id($course_create, 'page', true);
            
	  if(is_numeric($course_create)){
	     wp_redirect(get_permalink($course_create),'301');
	     exit;
	  }
	}

	/* ===== NINJA FORMS FOR INSTRUCTOR SIGNUP ====== */

	function wplms_ninja_forms_sub_table_row_actions_convert_to_instructor( $row_actions, $data, $sub_id, $form_id ) {
		if ( !in_array( 'ninja-forms/ninja-forms.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
			return $row_actions;

		if(!isset($this->settings['instructor_signup_ninja_form_id']) || !is_numeric($this->settings['instructor_signup_ninja_form_id']))
			return $row_actions;
		
	    $ninja_instructor_form_id = $this->settings['instructor_signup_ninja_form_id'];
	    if(isset($ninja_instructor_form_id) && $ninja_instructor_form_id == $form_id){
	    	$row_actions['instructor'] = apply_filters('wplms_ninja_forms_change_to_instructor_sub','<span><a href="?post_status=all&post_type=nf_sub&action=-1&form_id='.$form_id.'&make_instructor" id="'.$sub_id.'" class="wplms_ninja_forms_convert_to_instructor_sub">'. __( 'Make Instructor', 'vibe-customtypes' ).'</a></span>',$sub_id,$form_id);
	    }
	  return $row_actions;

	}
	
	function wplms_ninja_forms_change_to_instructor_sub($link,$sub_id,$form_id){
		if(!isset($_GET['make_instructor']) && !isset($_GET['make_student'])){
	    	return	$link;	
    	}
		if ( !in_array( 'ninja-forms/ninja-forms.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
			return $link;
		if($this->settings['instructor_signup_ninja_form_id'] != $form_id){
			return $link;
		}

		if(isset($_GET['make_instructor'])){
	    	$role = 'instructor';
    	}
    	if(isset($_GET['make_student'])){
	    	$role = 'student';
    	}
	    $submission = Ninja_Forms()->sub( $sub_id );
	    
	    if(isset($submission) && is_array($submission->fields)){

		    foreach($submission->fields as $value){
		      $email=$value;
		       if(filter_var($email, FILTER_VALIDATE_EMAIL)){ 
		            $args = array(
		                  'search'         => $email,
		                  'search_columns' => array('user_email' ),
		                );
		            $user_query = new WP_User_Query( $args );
		            // User Loop
		            if ( ! empty( $user_query->results ) && count($user_query->results) == 1) { 
		              $user = $user_query->results[0];
		              $user_id = $user->ID;

		              if(!user_can($user->ID,'edit_posts')){
		                $user_id = wp_update_user( array( 'ID' => $user_id, 'role' => 'instructor' ) );
		                  if ( is_wp_error( $user_id ) ) {
		                    return '<span>'.__('There was some error','vibe-customtypes').'</span>';
		                  } else {
		                    return '<span><a href="?post_status=all&post_type=nf_sub&action=-1&form_id='.$form_id.'&make_student" id="'.$sub_id.'" class="wplms_ninja_forms_convert_to_instructor_sub">'. __( 'Make Student', 'vibe-customtypes' ).'</a></span>';
		                  }
		              }else{
		              	return '<span>'.__('Instructor','vibe-customtypes').'</span>';
		              }
		            }
		       }
		    }
		  }
	}
	/* Show Message and Mail icon below Instructor name */
	function show_message_icon($meta,$instructor_id){
		if(is_numeric($instructor_id) && is_user_logged_in()){ //removed  && is_singular('course')
			$meta .= '<ul class="instructor_meta">';
			if(is_user_logged_in()){
				$user_id = get_current_user_id();
				if($user_id != $instructor_id && function_exists('bp_get_messages_slug')){
					$link = wp_nonce_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username( $instructor_id ) );				
					$meta .= '<li><a href="'.$link.'" class="button tip" title="'.__('Send Message','vibe-customtypes').'"><i class="icon-email"></i></a></li>';
				}
			}
			$user_info = get_userdata($instructor_id);
			$meta .= '<li><a href="mailto:'.$user_info->user_email.'" class="button tip" title="'.__('Send Email','vibe-customtypes').'"><i class="icon-at-email"></i></a></li>';
			$meta .= '</ul>';
		}
		return $meta;
	}

	function wplms_enable_incourse_quiz($quiz_class){
		$quiz_class .=' start_quiz';
		return $quiz_class;
	}
	function wplms_quiz_check($unit_id){
		if(get_post_type($unit_id) == 'quiz'){
			echo '<input type="hidden" id="results_link" value="'.bp_loggedin_user_domain().BP_COURSE_SLUG.'/'.BP_COURSE_RESULTS_SLUG.'/?action='.$unit_id.'" />';
		}
	}
	function wplms_incourse_quiz_per_page($num){
		if(isset($this->settings['in_course_quiz_paged']) && is_numeric($this->settings['in_course_quiz_paged'])){
			return $this->settings['in_course_quiz_paged'];
		}
		return $num;
	}
	function show_progressbar_user($user_id,$course_id){
		$progress = get_user_meta($user_id,'progress'.$course_id,true);
		if(isset($progress) && is_numeric($progress)){
			echo '<div class="progress">
             <div class="bar animate stretchRight load" style="width: '.$progress.'%"><span>'.$progress.'%</span></div>
           </div>';
		}
	}

	function default_order($order){
		
		if(!isset($order['orderby']) || !empty($order['id']))
			return $order;

		if(empty($order['orderby'])){
			switch($this->settings['default_order']){
				case 'date':
					$order['orderby']=array('menu_order' => 'DESC', 'date' => 'DESC');
				break;
				case 'title':
					$order['orderby']=array('menu_order' => 'DESC', 'title' => 'ASC');
				break;
				case 'popular':
					$order['orderby']='meta_value_num';
					$order['meta_key']='vibe_students';
				break;
				case 'rated':
					$order['orderby']=array('menu_order' => 'DESC', 'meta_value' => 'DESC');
					$order['meta_key']='average_rating';
				break;
				case 'rand':
					$order['orderby']=array('menu_order' => 'DESC', 'rand' => 'DESC');
				break;
				case 'start_date':
					$order['orderby']=array('menu_order' => 'DESC', 'meta_value' => 'ASC');
					$order['meta_key']='vibe_start_date';
					$order['meta_type'] = 'DATE';
    				$order['order'] = 'ASC';
    				if(empty($order['meta_query'])){
    					$order['meta_query']=array(array(
							'key' => 'vibe_start_date',
							'value' => current_time('mysql'),
							'compare'=>'>='
						));	
    				}
    				
				break;
				default:
					$order = apply_filters('wplms_custom_order_in_course_directory',$order);
				break;
			}
		}
		return $order;
	}

	function subscribe_free_course(){
		global $post;
		if(isset($_GET['subscribe'])){
			//Free course check
			$free = get_post_meta($post->ID,'vibe_course_free',true);
			if(vibe_validate($free)){
				$user_id = get_current_user_id(); 
				$date = get_post_meta($post->ID,'vibe_start_date',true);
				if( empty($date) || (!empty($date) && strtotime($date) < current_time('timestamp')) ){
					add_action('wplms_course_before_front_main',array($this,'free_subscribed'));
					bp_course_add_user_to_course($user_id,$post->ID);
				}else{
					add_action('wplms_course_before_front_main',array($this,'free_not_subscribed'));	
				}
			}
		}
	}

	function free_subscribed(){
		echo '<div class="message success"><p>'.__('Congratulations ! You\'ve been subscribed to the course','vibe-customtypes').'</p></div>';
	}

	function free_not_subscribed(){
		echo '<div class="message"><p>'.__('Course not available for subscription.','vibe-customtypes').'</p></div>';
	}

	function return_blank_for_free($pid,$course_id,$status){
		$free = get_post_meta($course_id,'vibe_course_free',true);
		if(vibe_validate($free) ){ 
			if($status == -1){// Expired course
				$pid = '?renew';
			}else{
				return '';	
			}
		}
		return $pid;
	}
	function manual_subscription($link,$course_id){
		$free = get_post_meta($course_id,'vibe_course_free',true);
		if(vibe_validate($free)){
			$coming_soon = get_post_meta($course_id,'vibe_coming_soon',true);
			if(!vibe_validate($coming_soon)){
				$link = get_permalink($course_id).'?subscribe';
			}
		}
		return $link;
	}
	function free_label($label,$course_id){
		$free = get_post_meta($course_id,'vibe_course_free',true);
		if(vibe_validate($free)){
		 $label =apply_filters('wplms_take_this_course_button_label',__('TAKE THIS COURSE','vibe-customtypes'),$course_id).apply_filters('wplms_course_button_extra','',$course_id);
		}
		return $label;
	}
	function disable_auto_subscribe($flag){
		return 0;
	}
	function enable_front_end_course_deletion($flag){
		return 1;
	}
	function wplms_woocommerce_orders_link($loggedin_menu){
            if ( (in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php')) ) && is_user_logged_in()) {

            $myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );
            $link = trailingslashit( bp_loggedin_user_domain() . get_post_field('post_name',$myaccount_page_id) );

            	if ( isset($myaccount_page_id) && is_numeric($myaccount_page_id) ) {
	              $loggedin_menu['orders']=array(
	                          'icon' => 'icon-list',
	                          'label' => __('My Orders','vibe-customtypes'),
	                          'link' => $link
	                          );
	            }
	        }

            if ( ( in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php')) )&& is_user_logged_in()) {
            	$pmpro_account_page_id = get_option('pmpro_account_page_id');
            	if ( isset($pmpro_account_page_id ) && is_numeric($pmpro_account_page_id ) ) {
              	$loggedin_menu['membership']=array(
                          'icon' => 'icon-archive',
                          'label' => __('My Membership','vibe-customtypes'),
                          'link' =>get_permalink( $pmpro_account_page_id )
                          );
           		 }
           	}
		return  $loggedin_menu;
    }
    
    function wplms_woocommerce_dashboard_endpoints($url, $endpoint, $value, $permalink ){
    	if(empty($permalink)){
    		$account_pid= wc_get_page_id('myaccount');
			$permalink = get_permalink($account_pid);
			$url = $permalink.$endpoint;
		} 
		return $url;
	}

    function wplms_course_code_check(){
        $user_id=get_current_user_id();
       $course_id =get_the_ID();
       
        if(isset($_POST['submit_course_codes'])){
             if ( !isset($_POST['security_code']) || !wp_verify_nonce($_POST['security_code'],'security'.$user_id) ){
             	$this->course_code_message = '<p class="message">'.__('Security check Failed. Contact Administrator.','vibe-customtypes').'</p>';
             	return;
            }else{
                $code = $_POST['course_code'];
                $course_codes = get_post_meta($course_id,'vibe_course_codes',true);
                if(isset($code) && strlen($code)<2 || (strpos($code,'|') !== false)){
                    $this->course_code_message = '<p class="message">'.__('Code does not exist. Please check the code.','vibe-customtypes').'</p>';
                    return;
                }

                $x=preg_match("/(^|,)$code(\|([0-9]+)|(,|$))/", $course_codes, $matches);
                if(!$x){
                    $this->course_code_message =  '<p class="message">'.__('Code does not exist. Please check the code.','vibe-customtypes').'</p>';
                    return;
                }else{    
                    global $wpdb,$bp;
                    if(isset($matches[3]) && is_numeric($matches[3])){
                        $total_count = $matches[3];

                        $count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$bp->activity->table_name} WHERE component = %s AND type = %s AND content = %s AND item_id = %d",'course','course_code',$code,$course_id));
                        //Added on 1st feb'16, remove above line in April'16
                        $addon_count = $wpdb->get_var($wpdb->prepare("SELECT count(*) FROM {$bp->activity->table_name_meta} WHERE meta_key = %d AND meta_value = %s",$course_id,$code));
                        $count = $count + $addon_count;
                        if($count < $total_count){
                            if(!wplms_user_course_check($user_id,$course_id)){
                                do_action('wplms_course_code',$code,$course_id,$user_id);
                                bp_course_add_user_to_course($user_id,$course_id);
                                $this->course_code_message = '<p class="message success">'.__('Congratulations! You are now added to the course.','vibe-customtypes').'</p>';
                            }else{
                                $this->course_code_message = '<p class="message">'.__('User already in course.','vibe-customtypes').'</p>';
                            }
                        }else{
                            $this->course_code_message = '<p class="message">'.__('Maximum number of usage for course code exhausted','vibe-customtypes').'</p>';
                        }
                    }else{
                        if(!wplms_user_course_check($user_id,$course_id)){
                            do_action('wplms_course_code',$code,$course_id,$user_id);
                            bp_course_add_user_to_course($user_id,$course_id);
                            $this->course_code_message = '<p class="message success">'.__('Congratulations! You are now added to the course.','vibe-customtypes').'</p>';
                        }else{
                            $this->course_code_message = '<p class="message">'.__('User already in course.','vibe-customtypes').'</p>';
                        }
                    }
                }
            }
         }
    }
    function display_course_code_message(){
    	if(!empty($this->course_code_message)){
    		echo $this->course_code_message;
    	}
    }
    
	function wplms_front_end_save_course_codes($course_id){
		if($_POST['extras']){ 
			$extras = json_decode(stripslashes($_POST['extras']));
	        if(is_array($extras) && isset($extras))
	        foreach($extras as $c){
	           update_post_meta($course_id,$c->element,$c->value);
	        }
		}
	}
	function wplms_front_end_show_course_codes($course_id){
		$course_codes='';
		if(isset($_GET['action']) && is_numeric($_GET['action'])){
            $course_id = $_GET['action'];
            $course_codes = get_post_meta($course_id,'vibe_course_codes',true);
        }
		echo '<li class="course_membership"><h3>'.__('Course Codes','vibe-customtypes').'<span>
                  <textarea id="vibe_course_codes" class="vibe_extras" placeholder="'.__('Enter Course codes (XXX|2,YYY|4)','vibe-customtypes').'" >'.$course_codes.'</textarea>
              </span>
              </h3>
          </li>';
	}
	function wplms_front_end_course_codes($settings){
		$settings['vibe_course_codes']='';
		if(isset($_GET['action']) && is_numeric($_GET['action'])){
            $course_id = $_GET['action'];
            $settings['vibe_course_codes'] = get_post_meta($course_id,'vibe_course_codes',true);
        }
		return $settings;
	}
	function course_codes_setting($setting){
		$setting[]=array( // Text Input
					'label'	=> __('Set Course purchase codes','vibe-customtypes'), // <label>
					'desc'	=> __('Student can gain access to Course using course codes (multiple codes comma separated, usage count pipe saperate eg : xxx|2,yyy|4)','vibe-customtypes'), // description
					'id'	=> 'vibe_course_codes', // field id and name
					'type'	=> 'textarea', // type of field
				);
		return $setting;
	}
	function show_unit_date_time_backend($settings){
		$prefix='vibe_';
		$settings[]= array( // Text Input
					'label'	=> __('Access Date','vibe-customtypes'), // <label>
					'desc'	=> __('Date on which unit is accessible','vibe-customtypes'), // description
					'id'	=> $prefix.'access_date', // field id and name
					'type'	=> 'date', // type of field
				);
		$settings[]=array( // Text Input
					'label'	=> __('Access Time','vibe-customtypes'), // <label>
					'desc'	=> __('Time after which unit is accessible','vibe-customtypes'), // description
					'id'	=> $prefix.'access_time', // field id and name
					'type'	=> 'time', // type of field
				);
		return $settings;
	}
	function add_date_time_field($unit_settings){
		$unit_settings['vibe_access_date']='';
		$unit_settings['vibe_access_time']='';
		$vibe_access_date= get_post_meta(get_the_ID(),'vibe_access_date',true);
		$vibe_access_time= get_post_meta(get_the_ID(),'vibe_access_time',true);
		if(isset($vibe_access_date) && isset($vibe_access_time) && $vibe_access_date && $vibe_access_time){
			$unit_settings['vibe_access_date']=$vibe_access_date;
			$unit_settings['vibe_access_time']=$vibe_access_time;
		}
		return $unit_settings;
	}
	function show_date_time_field($unit_settings){
		wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery', 'jquery-ui-core' ) );
		wp_enqueue_script( 'timepicker_box', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/js/jquery.timePicker.min.js', array( 'jquery' ) );
		echo '<script>
		jQuery(document).ready(function(){
				jQuery( ".datepicker" ).datepicker({
                    dateFormat: "yy-mm-dd",
                    numberOfMonths: 1,
                    showButtonPanel: true,
                });
                 jQuery( ".timepicker" ).each(function(){
                 jQuery(this).timePicker({
                      show24Hours: false,
                      separator:":",
                      step: 15
                  });
                });});</script>
		     <li><label>'.__('Unit access date','vibe-customtypes').'</label>
                <h3>'.__('Access date','vibe-customtypes').'<span>
                <input type="text" class="datepicker vibe_extras" id="vibe_access_date" value="'.$unit_settings['vibe_access_date'].'" /> 
            </li><li><label>'.__('Unit access time','vibe-customtypes').'</label>
                <h3>'.__('Access time','vibe-customtypes').'<span>
                <input type="text" class="timepicker vibe_extras" id="vibe_access_time" value="'.$unit_settings['vibe_access_time'].'" /> 
            </li>';
	}
	function save_unit_extra_settings($unit_id){
		if($_POST['extras']){
			$extras = json_decode(stripslashes($_POST['extras']));
	        if(is_array($extras) && isset($extras))
	        foreach($extras as $c){
	           update_post_meta($unit_id,$c->element,$c->value);
	        }
		}
	} 

	function apply_unit_date_time_drip_feed($value,$pre_unit_id,$course_id,$unit_id){
		$vibe_access_date= get_post_meta($unit_id,'vibe_access_date',true);
		$vibe_access_time= get_post_meta($unit_id,'vibe_access_time',true);
		if(isset($vibe_access_date) && isset($vibe_access_time) && $vibe_access_date && $vibe_access_time){
			$value=strtotime($vibe_access_date.' '.$vibe_access_time);
			$value = $value -1*(current_time('timestamp') - time()); // Adjustment to UTC timestamp
		}
		return $value;
	}
	function custom_wplms_login_widget_action($url){
        return wp_login_url( get_permalink() );
	}  
	function login_redirect($redirect_url,$request_url,$user){
		global $bp;
		
		if(($user instanceof WP_User)){
			$site_lock_url = '';
			if(function_exists('vibe_get_option')){
				$check_site_lock = vibe_get_option('site_lock');
				if($check_site_lock == 1){
					$page_id = vibe_get_option('site_lock_home_page_url');
					$site_lock_url = get_permalink($page_id);
				}
			}
			if(!empty($site_lock_url)){
				$url = $site_lock_url;
			}else{
				$url = home_url();
			}
			
			$redirect_array = apply_filters('wplms_redirect_location',array(
					'home' => $url,
					'profile' => bp_core_get_user_domain($user->ID),
					'mycourses' => bp_core_get_user_domain($user->ID).'/'.BP_COURSE_SLUG,
					'instructing_courses' => bp_core_get_user_domain($user->ID).'/'.BP_COURSE_SLUG.'/'.BP_COURSE_INSTRUCTOR_SLUG,
					'dashboard' => bp_core_get_user_domain($user->ID).'/'.(defined('WPLMS_DASHBOARD_SLUG')?WPLMS_DASHBOARD_SLUG:'dashboard'),
					'same' => '',
					));
			
			$flag=0;
			if (isset($user->allcaps['edit_posts'])) {
				$redirect_url=$redirect_array[$this->settings['instructor_login_redirect']];
				if($this->settings['instructor_login_redirect'] == 'same')
					$redirect_url=$_REQUEST['redirect_to'];
			}else{
				$redirect_url=$redirect_array[$this->settings['student_login_redirect']];
				if($this->settings['student_login_redirect'] == 'same')
					$redirect_url=$_REQUEST['redirect_to'];
			}
		}
		if(empty($redirect_url))
			$redirect_url = home_url();

		return $redirect_url;
	}

	function wplms_login_same_page($url){
		if(strpos($url,'?') == false){
			$url .='?redirect_to='.urlencode($this->getCurrentUrl());
		}else{
			$url .='&redirect_to='.urlencode($this->getCurrentUrl());
		}
		return $url;
	}
	function getCurrentUrl() {
        $url  = isset( $_SERVER['HTTPS'] ) && 'on' === $_SERVER['HTTPS'] ? 'https' : 'http';
        $url .= '://' . $_SERVER['SERVER_NAME'];
        $url .= in_array( $_SERVER['SERVER_PORT'], array('80', '443') ) ? '' : ':' . $_SERVER['SERVER_PORT'];
        $url .= $_SERVER['REQUEST_URI'];
        return $url;
    }
	function hide_admin_in_instructor($flag){ 
		return 0;
	}

	function coursenav_remove_members($menu_array){
		unset($menu_array['members']);
        return $menu_array;
	}

	function coursenav_remove_curriculum($menu_array){
		unset($menu_array['curriculum']);
        return $menu_array;
	}
	function course_curriculum_below_description(){
		global $post;
		$id= get_the_ID();
		$class='';
		if(isset($this->settings['curriculum_accordion']))
			$class="accordion";
		?>
			<div class="course_curriculum <?php echo $class; ?>">
				<?php
					$file = get_stylesheet_directory() . '/course/single/curriculum.php';
					if(!file_exists($file)){
						$file = VIBE_PATH.'/course/single/curriculum.php';
					}
					include $file;
				?>
			</div>
		<?php
	}

	/*
		ACCORDION MODE FOR CURRICULUM IN COURSE CREATION
	*/
	function course_creator_curriculum_accordion(){

		$cc_id = vibe_get_option('create_course');
		if( function_exists('icl_object_id') ){
			$cc_id = icl_object_id( $cc_id, 'page', true );
		}
		if(!is_page($cc_id) || !current_user_can('edit_posts'))
			return;

		?>
		<script>
		jQuery(document).ready(function(){
			jQuery('ul.curriculum>li.new_section').click(function(event){
		        jQuery(this).toggleClass('displayed_elements');
		        jQuery(this).nextUntil('li.new_section','li').toggle(100);
		    });
		});
		</script>
		<style>
			ul.curriculum>.new_section{position:relative;}
			ul.curriculum>.new_section:after{position:absolute;content:"\f068";
			font-family:'fontawesome';right:40px;font-size:24px;color:#eee;}
			ul.curriculum>.new_section.displayed_elements:after{content:"\f067";}
		</style>
		<?php
	}

	function course_unit_details($lesson){ 
		
		if(empty($lesson['type']) || $lesson['type'] !='unit'){
			return;
		}

		$description = get_post_meta(intval($lesson['id']),'vibe_subtitle',true);
		if(strlen($description) > 2){
			?>
			<tr class="unit_description">
				<td colspan="4">
					<?php 
						if(!empty($description)){
							echo do_shortcode($description);
						}else{
							echo __('No Description Found','vibe-customtypes');
						}
					?>
				</td>
			</tr>
			<?php
		}
	}

	function unit_expander($title){
		
		$title.='<span class="unit_description_expander"></span>';
		return $title;
	}

	function one_session_per_user( $user, $username, $password ) { 
		
		if(isset($user->allcaps['edit_posts']) && $user->allcaps['edit_posts']){
			return $user;
		}
		$sessions = WP_Session_Tokens::get_instance( $user->ID );

    	$all_sessions = $sessions->get_all();
		if ( count($all_sessions) ) {
			$flag=0;
			$previous_login = get_user_meta($user->ID,'last_activity',true);
			if(isset($previous_login) && $previous_login){
				$threshold = apply_filters('wplms_login_threshold',1800);
				$difference = time()-strtotime($previous_login) - $threshold;
				if($difference <= 0){ // If the user Logged in within 30 Minutes
					$flag=1;
				}else{
					$token = wp_get_session_token();
					$sessions->destroy_others( $token );
				} 
			}else{
				$flag = 1;
			}
			if($flag)
				$user = new WP_Error('already_signed_in', __('<strong>ERROR</strong>: User already logged in.','vibe-customtypes'));
		}
	    return $user;
	}
	/*
	*	Add Course External link setting in Admin and front end interface
	* 
	 */
	function course_external_link_setting($settings){
		$settings['vibe_course_external_link'] =array( // Text Input
			'label'	=> __('External Course button link','vibe-customtypes'), // <label>
			'text'   => __('Connect Course button to an external page (Leave blank to skip)','vibe-customtypes'),
			'desc'	=> __('Enter the external page url. Leave blank or 0 to skip','vibe-customtypes'), // description
			'id'	=> 'vibe_course_external_link', // field id and name
			'type'	=> 'text', // type of field
	        'std'   => ''
		);
		return $settings;
	}
	/*
	* Process course external link, if found, replace current link with external link
	 */
	function course_external_link($link,$course_id){
		$external_link = get_post_meta($course_id,'vibe_course_external_link',true);
		if(!empty($external_link)){
			$link = $external_link;
		}
		return $link;
	}

	/*
	ASSIGN FREE COURSES TO USERS ON SIGNUP/REGISTER
	 */
	function wplms_activate_free_courses($user_id = NULL){
		if(empty($user_id)){
		   if(!is_user_logged_in())
		   return;
		 
		  $user_id = get_current_user_id();
		}
		 
      	$args = apply_filters('wplms_activate_free_courses',array(
        	'post_type' => 'course',
        	'post_per_page' => -1,
        	'meta_query'=>array(
    			array(
             		'key' => 'vibe_course_free',
             		'value' => 'S',
             		'compare' => '=',
             		'type' => 'CHAR'
            	)
        	)      
     	));
	   	
	   	$free_courses = new WP_Query($args);
	   	if($free_courses->have_posts()){
		    while($free_courses->have_posts()){
		        $free_courses->the_post();
	        	$course_id = get_the_ID();
	        	bp_course_add_user_to_course($user_id,$course_id);
	     	}
	   	}
		    wp_reset_postdata();
	}

	/*
	AUTO MARK COMPLETE UNIT WHEN PROCEEDING TO NEXT UNIT
	 */
	
	function mark_complete_when_next_unit(){
		if(function_exists('vibe_get_option')){
			$start_page_id = vibe_get_option('take_course_page');
			if(function_exists('icl_object_id')){
				$start_page_id = icl_object_id($start_page_id,'page', true);
			}
			if(is_page($start_page_id)){
				?>
				<script>
					jQuery(document).ready(function($){
						$('.unit_content').on('unit_traverse',function(event){
							var check_mark_complete = $('#mark-complete');
							var didAction = 0;
							if(!didAction){
								$('#mark-complete').css('display','none');
								console.log(' CHECK ');
								if(typeof check_mark_complete != 'undefined'){
									var unit_id = check_mark_complete.attr('data-unit');
									check_mark_complete.trigger('click');
									didAction = 1;
									$('.course_timeline').find('.unit[data-unit="+$unit_id+"]').addClass('done');
								}
							}
						});
					});
				</script>
				<?php
			}
		}		
	}

	/*
	Remove Links from Curriculum
	 */
	function remove_links($curriculum){
		
		if(!empty($curriculum) && !is_user_logged_in()){
			foreach($curriculum as $k=>$unit){
				if($unit['type'] == 'unit'){
					$curriculum[$k]['link'] ='';
				}
			}
		}

		return $curriculum;
	}

	/*
	Fix Course Menu On Scroll For C2, C3 and C5 layouts.
	*/
	function fix_course_menu_on_scroll(){
		if(!is_singular('course')){
			return;
		}

		?>
		  <style>
			  .menu_fixed{
			    position: fixed;
			    width: 100%;
			    z-index: 99;
			    bottom: 0;
			  }
			  .single-course .menu_fixed div.item-list-tabs#object-nav li .flexMenu-popup{
			    top: initial !important;
			    bottom: 100% !important;
			  }
		  </style>
		 
		  <script>
			  jQuery(document).ready(function($){
			    if($('body').hasClass('c2') || $('body').hasClass('c3') || $('body').hasClass('c5')){
			      $("#item-nav").each(function(){
			        var $this = $(this);
			        /*var bottom = $('footer').offset().top;*/
			        var height = $this.offset().top;
			        $(window).scroll(function(event){
			            var st = $(this).scrollTop();
			            if(st > height){
			              $this.addClass('menu_fixed fadeInUp load animated');
			              /*if(st >= bottom){
			                $this.removeClass('menu_fixed fadeInUp load animated');
			              }*/
			            }else{
			              $this.removeClass('menu_fixed fadeInUp load animated');
			            }
			         });
			      });
			    }
			  });
		  </script>
		<?php
	}

	/*
	Display course Badge in popup in course details section
	*/
	function show_course_badge_popup_in_course_details($html,$course_id){

		//For displaying Badge in popup
		$b = bp_get_course_badge($course_id);
		$badge = wp_get_attachment_info($b); 
		$size = apply_filters('bp_course_badge_thumbnail_size','thumbnail');
		$badge_url = wp_get_attachment_image_src($b,$size);

		$batdge_title = get_post_meta($course_id,'vibe_course_badge_title',true);

  		$html = '<li class="course_badge"><a class="tip ajax-badge" data-course="'.get_the_title($course_id).'" title="'.$batdge_title.'"><img style="display:none;" src="'.$badge_url[0].'" title="'.$badge['title'].'"/><i class="icon-award-stroke"></i> '.__('Course Badge','vibe-customtypes').'</a></li>';

  		return $html;
	}

	/*
	Display course Certificate in popup in course details section
	*/
	function show_course_certificate_popup_in_course_details($html,$course_id){

		//For displaying certificate in popup
		global $post;
		$author_id = $post->post_author;

		$certificate_template_id = get_post_meta($course_id,'vibe_certificate_template',true);
		if(!empty($certificate_template_id) && is_numeric($certificate_template_id)){
			$pid = $certificate_template_id;
		}else{
			if(function_exists('vibe_get_option')){
			    $pid = vibe_get_option('certificate_page');
			}
		}
		$certificate_url = get_permalink($pid).'?c='.$course_id.'&u='.$author_id;

		$html = '<li class="course_certificate"><a href="'.$certificate_url.'" class="ajax-certificate  regenerate_certificate" data-user="'.$author_id.'" data-course="'.$course_id.'"><i class="icon-certificate-file"></i>  '.__('Course Certificate','vibe-customtypes').'</a></li>';

		return $html;
	}

	/*
	Finish course button is automatically triggered
	*/
	function finish_course_auto_trigger(){
		add_action('wp_footer',function(){

			?>
		<script>
			jQuery(document).ready(function($){
				$('.unit_content').on('unit_traverse',function(){
				  var value= parseInt($('.course_progressbar').attr('data-value'));
				  if(value >= 100){
				    $('input[name="submit_course"]').trigger('click');
				  }else{

				     $('input[name="submit_course"]').addClass('hide');
				  }
				});
				$('.course_progressbar').on('increment',function(){
				  var value= parseInt($('.course_progressbar').attr('data-value'));
				  if(value >= 100){
				    $('input[name="submit_course"]').trigger('click');
				  }else{

				     $('input[name="submit_course"]').addClass('hide');
				  }
				});
				var value= parseInt($('.course_progressbar').attr('data-value'));
				  if(value >= 100){
				    $('input[name="submit_course"]').trigger('click');
				  }else{

				     $('input[name="submit_course"]').addClass('hide');
				  }
			});
		</script>
		<?php

		});
	}

	function open_popup_for_non_logged_users(){
		add_action('wp_footer',function(){
		if(is_user_logged_in()){
			return;
		}
		?>
		<script>
			jQuery(document).ready(function($){

				$('.course_button').off('click');
				$('.course_button').on('click',function(event){
					event.preventDefault();
					if($('header').hasClass('app')){

						if(jQuery('.global').hasClass('login_open')){
				          jQuery('.global').removeClass('login_open');
				        }else{
				         jQuery('.global').addClass('login_open');
				          jQuery('body').trigger('global_opened');
				        }
   						 event.stopPropagation();
					}else{
						$('.vbplogin').trigger('click');
						$('#login_modern_trigger').trigger('click');
					}
					
				});
			});
		</script>
		<?php

		});
	}

	function open_popup_for_non_logged_users_free(){
		if(is_user_logged_in()){
			return;
		}
		global $post;
		$check_free = 0;
		if(!empty($post->ID) && $post->post_type == 'course'){
			$check_free = get_post_meta($post->ID,'vibe_course_free',true);
		}
		if(empty($check_free) || (!empty($check_free) && function_exists('vibe_validate') && !vibe_validate($check_free)) )
			return;
		add_action('wp_footer',function(){
		?>
		<script>
			jQuery(document).ready(function($){

				$('.course_button').off('click');
				$('.course_button').on('click',function(event){
					event.preventDefault();
					if($('header').hasClass('app')){

						if(jQuery('.global').hasClass('login_open')){
				          jQuery('.global').removeClass('login_open');
				        }else{
				         jQuery('.global').addClass('login_open');
				          jQuery('body').trigger('global_opened');
				        }
   						 event.stopPropagation();
					}else{
						$('.vbplogin').trigger('click');
						$('#login_modern_trigger').trigger('click');
					}
					
				});
			});
		</script>
		<?php

		});
	}

	function calculate_course_duration_from_start_course($course_id,$user_id){
		global $bp,$wpdb,$bp;
		$time = $expiry = $seconds = 0;
		if( !function_exists('bp_is_active') || !bp_is_active('activity'))
			return;
		$time = $wpdb->get_var($wpdb->prepare( "
										SELECT activity.date_recorded FROM {$bp->activity->table_name} AS activity
										WHERE 	activity.component 	= 'course'
										AND 	activity.type 	= 'subscribe_course'
										AND 	user_id = %d
										AND 	item_id = %d
										ORDER BY date_recorded DESC
									" ,$user_id,$course_id));
		if($time && time() > $time){
			$seconds = time() - (strtotime($time));
			$expiry = bp_course_get_user_expiry_time($user_id,$course_id);
			$new_time = $expiry + $seconds;
			bp_course_update_user_expiry_time($user_id,$course_id,$new_time);
			update_user_meta($user_id,$course_id,$new_time);  
		}
	}

	function extend_course($course_id,$user_id){
		global $post;
		$user_id = get_current_user_id();
		$course_id = $post->ID;
		$status = bp_course_get_user_course_status($user_id,$course_id);
		$expiry = bp_course_get_user_expiry_time($user_id,$course_id);
		if($status == 1 && $expiry <= time()){ //USer is enrolled but has not started the course, hence extend his subscription
			$seconds = time() + 60 ;
			bp_course_update_user_expiry_time($user_id,$course_id,$seconds);
			update_user_meta($user_id,$course_id,$seconds);  
		}
	}

} // End of Class

add_action('plugins_loaded','wplms_tips_init');
function wplms_tips_init(){
	WPLMS_tips::init();
}


add_action( 'widgets_init', 'wplms_course_code_widget');
function wplms_course_code_widget(){
	register_widget('wplms_course_codes');
}

class wplms_course_codes extends WP_Widget {
 
	function __construct() {
	    $widget_ops = array( 'classname' => 'wplms_course_codes', 'description' => __('WPLMS Course codes widget', 'vibe-customtypes') );
	    $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wplms_course_codes' );
	    parent::__construct( 'wplms_course_codes', __('WPLMS Course Codes', 'vibe-customtypes'), $widget_ops, $control_ops );
  	}
        
    function widget( $args, $instance ) {
    	if(!is_singular(BP_COURSE_CPT) || !defined('BP_COURSE_CPT') || !is_user_logged_in())
    		return;

    	$user_id=get_current_user_id();
    	$course_id =get_the_ID();
    	$course_codes = get_post_meta($course_id,'vibe_course_codes',true);
    	
    	if(!isset($course_codes) || strlen($course_codes)<2)
    		return;

    	if(function_exists('bp_course_is_member') && isset($instance['hide_for_members'])){
    		if(bp_course_is_member($course_id,$user_id))
    			return;
    	}

    	extract( $args );
    	$title = apply_filters('widget_title', $instance['title'] );

    	echo $before_widget;
    	// Display the widget title 
    	if ( $title )
      		echo $before_title . $title . $after_title;
      	$placeholder = '';
      	echo '<form method="post">
      			<input type="text" name="course_code" class="form_field" placeholder="'.$placeholder.'"/>';
      			wp_nonce_field('security'.$user_id,'security_code');
      	echo '<input type="submit" name="submit_course_codes" value="'.__('Submit','vibe-customtypes').'"/></form>';
    	echo $after_widget;
    }
 
    /** @see WP_Widget::update -- do not rename this */
    function update($new_instance, $old_instance) {   
	    $instance = $old_instance;
	    $instance['title'] = strip_tags($new_instance['title']);
	    $instance['placeholder'] = $new_instance['placeholder'];
	    $instance['hide_for_members'] = $new_instance['hide_for_members'];
        return $instance;
    }
 
    /** @see WP_Widget::form -- do not rename this */
    function form($instance) {  
    $defaults = array( 
        'title'  => __('Enter Course code','vibe-customtypes'),
        'placeholder'  => __('Place holder text','vibe-customtypes'),
        'hide_for_members'=>0,
    );
    $instance = wp_parse_args( (array) $instance, $defaults );                 
    ?>
    <p> <?php _e('Title','vibe-customtypes'); ?> <input type="text" class="text" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" /></p>
    <p> <?php _e('Course Codes input box text','vibe-customtypes'); ?> <input type="text" class="text" name="<?php echo $this->get_field_name('placeholder'); ?>" value="<?php echo $instance['placeholder']; ?>" /></p>
    <p> <?php _e('Hide Course codes for Course members','vibe-customtypes'); ?> <input type="checkbox" name="<?php echo $this->get_field_name('hide_for_members'); ?>" value="1" /></p>
	<?php
    }

}

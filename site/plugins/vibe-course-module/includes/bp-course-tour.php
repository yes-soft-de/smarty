<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if(!class_exists('WPLMS_Course_Tour'))
{   
    class WPLMS_Course_Tour
    {
    	public static $instance;
        public static function init(){
            if ( is_null( self::$instance ) )
                self::$instance = new WPLMS_Course_Tour();
            return self::$instance;
        }

    	function __construct(){

    		$this->user_id = get_current_user_id();
    		$this->check_enqueued = 0;
    		$this->tours_enabled_default = '';

    		if(class_exists('WPLMS_tips')){
				$tips = WPLMS_tips::init();
				$this->tips = $tips->settings;
				if(!empty($tips) && !empty($tips->settings) && !empty($tips->settings['tours_enabled_default'])){
    				$this->tours_enabled_default = $tips->settings['tours_enabled_default'];
    			}
    		}
    		add_filter('lms_general_settings',array($this,'tours_settings'));
			add_action('init',array($this,'tours_init'),2);
		}

		function tours_init(){
			if(!empty($this->tips['enable_tours'])){
				add_action( 'wp_enqueue_scripts',array($this,'bp_course_add_tour_js'));
				add_action( 'wp_enqueue_scripts', array($this,'bp_course_add_tour_css'));
				add_action( 'wp_ajax_end_tour_for_user', array($this,'end_tour_for_user'));
				add_action( 'wp_ajax_start_tour_for_user', array($this,'start_tour_for_user'));
				add_filter('tour_conditions_array',array($this,'course_pursue_conditions'));

				add_action( 'bp_setup_nav', array($this,'tours_tab'));

				//course pursue tour 
				add_filter('bp_course_get_all_tours',array($this,'course_pursue_tour_array'));

				//
				add_action('wp_ajax_tour_next_step',array($this,'tour_next_step'));

				add_action('bp_course_get_all_tours',array($this,'control_admin_page_tours'),11);
			}
		}

		function control_admin_page_tours($all_tours){
			if(!is_user_logged_in())
				return $all_tours;
			$user_id = get_current_user_id();
			if(function_exists('bp_is_my_profile') && bp_is_my_profile()){
				return $all_tours;
			}
			$tours_ended = get_user_meta($user_id,'tours_ended',true);
			//if(!empty($this->tips) && !empty($this->tips['tours_enabled_default'])){
			    $admin_tours_array = array('adding_students','bulk_messages','assign_cert_badges','extend_subscription','course_status');
			    $i = 0;
			    foreach($admin_tours_array as $admin_tour){
			    	if(empty($tours_ended)){
			    		if($admin_tour != 'adding_students'){
			    			unset($all_tours[$admin_tour]);
			    		}
			    	}else{
		    			if(in_array($admin_tour,$tours_ended )){
		    				unset($all_tours[$admin_tour]);
		    			}else{
		    				if(!$i){
		    					$i = 1;
		    				}else{
		    					unset($all_tours[$admin_tour]);
		    				}
		    			}
		    		}
			    }
			    
			//}
			return $all_tours;
		}

		function course_pursue_tour_array($tours){
			if(!is_user_logged_in())
				return;
			global $post;
			$user_id = get_current_user_id();
			if(function_exists('vibe_get_option')){
				$take_id = vibe_get_option('take_course_page');
			}

			 $tours['course_pursue'] = array(
			                      'id'=>'course_pursue',
			                      'label' => _x('Course Pursue Tour',' tour label ','vibe'),
			                      'role'=>'student',
			                      'condition' => ((!empty($post->ID) && $post->ID == $take_id)?1:0),
			                      'steps' => 'pursue_course_tour', 
			                    );
			 $quiz_status = get_user_meta($user_id,$post->ID,true );
			 $quiz_condition = ((empty($quiz_status ) && ($post->post_type=='quiz'))?1:0);
			 $tours['quiz_pursue'] = array(
			                      'id'=>'quiz_pursue',
			                      'label' => _x('Quiz Pursue Tour',' tour label ','vibe'),
			                      'role'=>'student',
			                      'condition' =>  $quiz_condition,
			                      'steps' => 'quiz_pursue_tour', 
			                    );
			 /*$edit_course_page = vibe_get_option('create_course');
			 $edit_course_condition = ((!empty($edit_course_page ) && ($post->ID==$edit_course_page))?1:0);
			 $tours['create_course'] = array(
			                      'id'=>'create_course',
			                      'label' => _x('Create course Tour',' tour label ','vibe'),
			                      'role'=>'instructor',
			                      'condition' =>  $edit_course_condition,
			                      'steps' => 'create_course', 
			                    );*/

			 $admin_condition = 0;
			 if(function_exists('bp_current_action') && function_exists('vibe_get_option')){
			 	$action = bp_current_action();
			 	if(empty($action)){
			 		if(!empty($_GET['action'])){
			 			$action = $_GET['action'];
			 		}
			 	}
			 	if(!empty($action) && $action=='admin' && current_user_can('edit_posts')){
				 	$admin_condition = 1;
				}
				$adding_students_condition = 0;
				$inst_add_students = vibe_get_option('instructor_add_students');
			
				if(!empty($action) && $action=='admin' && current_user_can('edit_posts') && !empty($inst_add_students) || (current_user_can('manage_options') && !empty($action) && $action=='admin')){
				 	$adding_students_condition = 1;
				}
				$assign_cert_badges_condition = 0;
				$instructor_assign_badges = vibe_get_option('instructor_assign_badges');
			
				if(!empty($action) && $action=='admin' && current_user_can('edit_posts') && !empty($instructor_assign_badges) || (current_user_can('manage_options') && !empty($action) && $action=='admin')){
				 	$assign_cert_badges_condition = 1;
				}

				$instructor_change_status_condition = 0;
				$instructor_change_status = vibe_get_option('instructor_change_status');
			
				if(!empty($action) && $action=='admin' && current_user_can('edit_posts') && !empty($instructor_change_status) || (current_user_can('manage_options') && !empty($action) && $action=='admin')){
				 	$instructor_change_status_condition = 1;
				}
				//extend subscriotion
				$instructor_extend_subscription_condition = 0;
				$instructor_extend_subscription = vibe_get_option('instructor_extend_subscription');
			
				if(!empty($action) && $action=='admin' && current_user_can('edit_posts') && !empty($instructor_extend_subscription) || (current_user_can('manage_options') && !empty($action) && $action=='admin')){
				 	$instructor_extend_subscription_condition  = 1;
				}
			 
				$tours['adding_students'] = array(
				                      'id'=>'adding_students',
				                      'label' => _x('Adding students to course','Adding students tour label ','vibe'),
				                      'role'=>'instructor',
				                      'condition' => $adding_students_condition,
				                      'steps' => 'adding_students', 
				                    );
				$tours['bulk_messages'] = array(
				                      'id'=>'bulk_messages',
				                      'label' => _x('Sending bulk messages',' tour label ','vibe'),
				                      'role'=>'instructor',
				                      'condition' => $admin_condition,
				                      'steps' => 'bulk_messages', 
				                    );
				 
				$tours['assign_cert_badges'] = array(
				                      'id'=>'assign_cert_badges',
				                      'label' => _x('Assigning certificates and Badges',' tour label ','vibe'),
				                      'role'=>'instructor',
				                      'condition' => $assign_cert_badges_condition,
				                      'steps' => 'assign_cert_badges', 
				                    );
				$tours['extend_subscription'] = array(
				                      'id'=>'extend_subscription',
				                      'label' => _x('Extending subscriptions of students','Extending subscriptions tour label ','vibe'),
				                      'role'=>'instructor',
				                      'condition' => $instructor_extend_subscription_condition,
				                      'steps' => 'extend_subscription', 
				                    );
				$tours['course_status'] = array(
				                      'id'=>'course_status',
				                      'label' => _x('Changing course statuses of students','Changing course statuses tour label ','vibe'),
				                      'role'=>'instructor',
				                      'condition' => $instructor_change_status_condition,
				                      'steps' => 'course_status', 
				                    );
			}
			return  $tours;
		}

		function course_pursue_conditions($conditions){
			$course_id = '';
			if(isset($_POST['course_id'])){
				$course_id = $_POST['course_id'];
			
			}elseif(isset($_COOKIE['course'])){
	     	 	$course_id = $_COOKIE['course'];
	     	}
			
			return $conditions = array('course_id'=>$course_id);
		}

		function repeat_steps_if_next_is_unit($data){
			if(!empty($data)){
				if(!empty($data->current_unit_id) && get_post_type($data->current_unit_id) == 'unit'){
					return -3;
					
				}else{
					return array(
			            // 'selector' : this is the selector we will not make it available for translation
			            "selector"=>".unit_title",
			        	"title"=>"read title of the unit",
			        	"content"=>"got next unit",
			        	"backdrop"=>true,
			        	"backdropContainer"=>".unit_content#unit_content",
			        	"smartPlacement"=>"",
			        	"ajax"=>true,
			        	"reflex"=>true,
			        	'path' =>str_replace($domain,'',get_permalink($take_id)),
			        	"placement"=>"left",
			            'callback' => 'repeat_steps_if_next_is_unit',
			         );
				}	
			}
		}
		

		function tours_settings($settings){
			$settings[] = array(
				'label'=>__('Tours Settings','wplms-woo' ),
				'type'=> 'heading',
			);
			$settings[] = array(
		            'label' => __('Enable Tours', 'vibe'),
		            'name' => 'enable_tours',
		            'desc' => __('This will enable tour functiionality in site.', 'vibe'),
		            'type' => 'checkbox',
				);
			$settings[] = array(
		            'label' => __('Enable all tours by default for users', 'vibe'),
		            'name' => 'tours_enabled_default',
		            'desc' => __('Will enable all tours by default for all users', 'vibe'),
		            'type' => 'checkbox',
				);
			
			return $settings;
		}						

		function bp_course_add_tour_css(){
			wp_enqueue_style( 'bootstrap-tour-css', plugins_url( 'css/bootstrap-tour.min.css',__FILE__ ));
			add_action('wp_footer',function(){
				?>
				<style>
					.popover {
						border-radius:0;    
					}

					.popover-title{
					    background-color: <?php echo vibe_get_customizer('primary_bg'); ?>;
					    text-transform: uppercase;margin: -2px;
					    color: <?php echo vibe_get_customizer('primary_color'); ?>;
					    border-radius: 0;border:none;
					}
					.tour-backdrop{z-index:1101;}
					.popover-navigation .btn{    padding: 2px 8px;
					    line-height: 1.4rem;
					    font-size: 11px;
					    text-transform: uppercase;
					    letter-spacing: 1px;
					    font-weight: 600;
					    background: <?php echo vibe_get_customizer('primary_bg'); ?>;
					    color:<?php echo vibe_get_customizer('primary_color'); ?>;
					}
				</style>
				<?php
			});
		}

		function tours_tab(){
			bp_core_new_nav_item( array( 
			    'name' => __( 'Tours','vibe'), 
			    'slug' => 'tours', 
			    'screen_function' => array($this,'tours_screen'), 
			    'show_for_displayed_user' => false,
			    'item_css_id' => 'tours',
		        'default_subnav_slug' => 'home', 
		        'position' => 55,
			    ) 
			);
		}
		
		function bp_course_start_condition(){

			if(!is_user_logged_in())
				return;

			$user_id = get_current_user_id();

			$tours_started = get_user_meta($user_id,'tours_started',true);
			if(empty($tours_started)){
				$tours_started = array();
			}
		}
		function bp_course_add_tour_js(){
		
			$start_condition = apply_filters('bp_course_tours_start_condition',1);

			if(!$start_condition)
				return;

			$user_id = get_current_user_id();

			//Fetch User tours status


			$user_tours_started = get_user_meta($user_id,'tours_started',true);
			//check if the tour has been started here : 
			
			$tours = $this->all_tours_array();
			$flag = 0;

			$conditions = apply_filters('tour_conditions_array',array());

			if(!empty($tours)){
				foreach($tours as $key=>$tour){
					//check if user started the tour
					if(!empty($user_tours_started) && in_array($key,$user_tours_started) || !empty($this->tours_enabled_default)){
						$tour['condition'] = apply_filters('tours_enqueue_condition',$tour['condition'],$tour);
						if(!empty($tour) && $this->check_access($tour['role']) && $tour['condition']){
							$this->enqueue_scripts();
							wp_enqueue_script( 'tour_json', apply_filters('bp_course_default_tours',plugins_url( 'js/tours.json', __FILE__ )));
							wp_enqueue_script( 'tour-js', plugins_url( 'js/tours.js', __FILE__ ),array('wplms','tour_json') );
							$active_tours[] = array(
						        $key => $tour['label']
				        	);
							wp_localize_script( 'tour-js', 'active_tours', $active_tours );
							

							
						}
					}
				}	
			}
		}

		function check_access($role){
			if(!is_user_logged_in())
				return false;
			switch ($role) {
				case 'instructor':
					if(is_user_logged_in() && current_user_can('edit_posts'))
						return true;
					break;
				case 'student':
					if(is_user_logged_in())
						return true;
					break;
				case 'admin':
					if(is_user_logged_in() && current_user_can('manage_options'))
						return true;
				break;
				default:
					return false;
				break;
			}
		}

		function enqueue_scripts(){
			if(!is_user_logged_in())
				return;

			if($this->check_enqueued > 0)
				return; 

			$this->check_enqueued++;
			wp_enqueue_script( 'tour-js-main', plugins_url( 'js/bootstrap-tour.min.js', __FILE__ ),array('wplms') );
				//steps js file
			
			$user_id = get_current_user_id();
			$nonce = wp_create_nonce('vibe_tour_security'.$user_id,'vibe_tour_security');
			$default_script_array  = array(
									'end_tour' =>  _x('End tour','tour','vibe'),
									'security' => $nonce,
									);
			wp_localize_script('bp-course-js', 'tours_strings', $default_script_array );


		}

		function tours_screen() {
		    add_action( 'bp_template_title',array($this, 'tours_title' ));
		    add_action( 'bp_template_content', array($this,'tours_content') );
		    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
		}

		function tours_title() {
		   echo '<h3 class="heading"><span>'._x('My tours (Walkthrough)','my tours label','vibe').'</span></h3>';
		}

		function tours_content() {

		    $user_id = bp_displayed_user_id();
		    $user_tours = get_user_meta($user_id,'tours_ended',true);
		    $user_started_tours = get_user_meta($user_id,'tours_started',true);
		    wp_nonce_field('vibe_tour_security'.$this->user_id,'vibe_tour_security');
		    echo '<table id="user-tours" class="table table-hover">';
		    echo '<thead><tr><th>'._x('Tour','','vibe').'</th><th>'._x('Status','','vibe').'</th><th>'._x('Action','','vibe').'</th></tr></thead><tbody>';
		    $all_tours_array  = $this->all_tours_array();
			foreach ($all_tours_array as $key=>$all_tour) {
				if($this->check_access($all_tour['role'])){
					if(!empty($user_started_tours) && in_array( $key,$user_started_tours)){
						$status = _x('Ongoing','status lable tours','vibe');
					}elseif(!empty($user_tours) && in_array( $key,$user_tours)){
						$status = _x('Ended','status lable tours','vibe');
					}else{
						$status = _x('Not started Yet','status lable tours','vibe');
					}
					//start button
					if(!empty($user_started_tours) && in_array( $key,$user_started_tours)){
						$start_button = '<a data-action-point="'.$key.'" class="button">'._x('Started','','vibe').'</a>';
					}else{
						$start_button = '<a href="javascript:void(0)" data-action-point="'.$key.'" class="button start_tour">'._x('Start','','vibe').'</a>';
					}
					echo '<tr><td>'.$all_tour['label'].'</td><td>'.$status.'</td><td>'.$start_button.((!empty($user_tours) && in_array( $key,$user_tours))?'':'<a href="javascript:void(0)" data-action-point="'.$key.'" class="button end_tour">'._x('End Tour','','vibe').'</a>').'</td></tr>';
					
				}
				
				}
			echo '</tbody></table>';
				
		}


		function end_tour_for_user(){
			if ( !isset($_POST['vibe_tour_security'])  || !wp_verify_nonce($_POST['vibe_tour_security'],'vibe_tour_security'.$this->user_id) ){
                _e('Security check Failed. Contact Administrator.','vibe');
                die();
            }
            if(!empty($this->user_id) && !empty($_POST['tour_name'])){
            	$tours_ended = get_user_meta($this->user_id,'tours_ended',true);
            	if(empty($tours_ended)){
            		$tours_ended =array();
            	}
            	if(!in_array($_POST['tour_name'], $tours_ended)){
        			$tours_ended[] = $_POST['tour_name'];
        			update_user_meta($this->user_id,'tours_ended',$tours_ended);
            	}
        		
        		$tours_started = get_user_meta($this->user_id,'tours_started',true);
        		if(is_array($tours_started) && count($tours_started)){
            		foreach ($tours_started as $key => $value) {
            			if($value == $_POST['tour_name']){
            				unset($tours_started[$key]);
            				print_r($tours_started[$key]);
            			} 
            		}
            		update_user_meta($this->user_id,'tours_started',$tours_started);
            	}
            }
            
            die();
		}

		function start_tour_for_user(){
			if ( !isset($_POST['vibe_tour_security'])  || !wp_verify_nonce($_POST['vibe_tour_security'],'vibe_tour_security'.$this->user_id) ){
                _e('Security check Failed. Contact Administrator.','vibe');
                die();
            }
            $user_id = get_current_user_id();
            if(!empty($user_id) && !empty($_POST['tour_name'])){
            	$tours_ended = get_user_meta($user_id,'tours_ended',true);
            	$tours_started = get_user_meta($user_id,'tours_started',true);
            	if(empty($tours_started)){
            		$tours_started =array();
            	}
            	if(is_array($tours_ended) && count($tours_ended)){
            		
            		$searched_key = array_search( $_POST['tour_name'], $tours_ended );
					if($searched_key !== false){
						unset( $tours_ended[$searched_key] );
					}
            		update_user_meta($user_id,'tours_ended',$tours_ended);
            	}
            	if(!in_array($_POST['tour_name'], $tours_started)){
            		$tours_started[] = $_POST['tour_name'];	
            	}
            	
        		update_user_meta($user_id,'tours_started',$tours_started);
            	switch($_POST['tour_name']){
            		case 'course_pursue':
            			$data = array();
						$args = array('post_status'=>'publish','post_type'=>'course','posts_per_page'=> 999,'meta_key'=>$user_id,'meta_value'=>5,'meta_compare' => '<');
		            	$the_query = new WP_Query( $args );
						if ( $the_query->have_posts() ) : while ( $the_query->have_posts() ) : $the_query->the_post();

							$data[] = array(
								'id' => get_the_ID(),
								'value' => get_the_title(),
								'link' => get_permalink()
								);
						endwhile;
		                endif;
						wp_reset_postdata();
						$data = apply_filters('wplms_start_tour_items_select',$data,$_POST['tour_name'],$user_id);

						if(!empty($data)){
							
							echo '<h3 class="heading"><span>'._x('Select course for Tour','select tour course','vibe').'</span></h3>';
							echo '<select name="course_select" class="course_select">';
							foreach($data as $d){
   								echo '<option value="'.$d['link'].'" data-id="'.$d['id'].'">'.$d['value'].'</option>';
   							}
							echo '</select><a href="javascript:void(0)" class="start_course_tour button full">'._x('Select Course to start tour','button','vibe').'</a>';
						}else{
							echo '<div class="message">'._x('No courses found','no course found messsage in popup','vibe').'</div>';
						}
						die();
            		break;
            		case 'adding_students':
            		case 'bulk_messages':
            		case 'assign_cert_badges':
            		case 'extend_subscription':
            		case 'course_status':
            		if(class_exists('Vibe_CustomTypes_Permalinks')){
						$p = Vibe_CustomTypes_Permalinks::init();
				    	$permalinks = $p->permalinks;
				    }
            			$admin_permalink = $permalinks['admin_slug'];
					 	if(empty($admin_permalink)){
					 		$admin_permalink = '?action=admin';
					 	}
            			$data = array();
						$args = array('post_author'=>$user_id,'post_status'=>'publish','post_type'=>'course','posts_per_page'=> 999);
		            	$the_query = new WP_Query( $args );
						if ( $the_query->have_posts() ) : while ( $the_query->have_posts() ) : $the_query->the_post();

							$data[] = array(
								'id' => get_the_ID(),
								'value' => get_the_title(),
								'link' => get_permalink().str_replace('/', '', $admin_permalink)
								);
						endwhile;
		                endif;
						wp_reset_postdata();
						$data = apply_filters('wplms_start_tour_items_select',$data,$_POST['tour_name'],$user_id);

						if(!empty($data)){
							
							echo '<h3 class="heading"><span>'._x('Select course for Tour','select tour course','vibe').'</span></h3>';
							echo '<select name="course_select" class="course_select">';
							foreach($data as $d){
   								echo '<option value="'.$d['link'].'" data-id="'.$d['id'].'">'.$d['value'].'</option>';
   							}
							echo '</select><a href="javascript:void(0)" class="start_course_tour button full">'._x('Select Course to start tour','button','vibe').'</a>';
						}else{
							echo '<div class="message">'._x('No courses found','no course found messsage in popup','vibe').'</div>';
						}
						die();
            		break;
            		case 'create_course':
            			$data = array();
						$args = array('post_author'=>$user_id,'post_type'=>'course','posts_per_page'=> 999);
		            	$the_query = new WP_Query( $args );
		            	if(function_exists('vibe_get_option')){
		            		$edit_id = vibe_get_option('create_course');
		            	}
		            	$link = get_permalink($edit_id);
						if ( $the_query->have_posts() ) : while ( $the_query->have_posts() ) : $the_query->the_post();

							$data[] = array(
								'id' => get_the_ID(),
								'value' => get_the_title(),
								'link' => $link.'?action='.get_the_ID()
								);
						endwhile;
		                endif;
						wp_reset_postdata();
						$data = apply_filters('wplms_start_tour_items_select',$data,$_POST['tour_name'],$user_id);
						if(!empty($data)){
							echo '<h3 class="heading"><span>'._x('Select course for Tour','select tour course','vibe').'</span></h3>';
							echo '<select name="course_select" class="course_select">';
							foreach($data as $d){
   								echo '<option value="'.$d['link'].'" data-id="'.$d['id'].'">'.$d['value'].'</option>';
   							}
   							echo '</select><a href="javascript:void(0)" class="start_course_tour button full">'._x('Select Course to start tour','button','vibe').'</a>';
   						}else{
							echo '<div class="message">'._x('No courses found','no course found messsage in popup','vibe').'</div>';
						}
   						
            		break;
            		case 'quiz_pursue':
            			global $wpdb;
            			$courses = array();
						$args =array('post_status'=>'publish','post_type'=>'course','posts_per_page'=> 999,'meta_key'=>$user_id,'meta_value'=>5,'meta_compare' => '<');
		            	$the_query = new WP_Query( $args );
		            	if(function_exists('vibe_get_option')){
		            		$edit_id = vibe_get_option('create_course');
		            	}
		            	$link = get_permalink($edit_id);
						if ( $the_query->have_posts() ) : while ( $the_query->have_posts() ) : $the_query->the_post();

							$courses[] = get_the_ID();
								
						endwhile;
		                endif;
						wp_reset_postdata();
						$quizzes  = array();
						
						if(!empty($courses)){
							$coursess = implode(",",$courses);
							
							$course_quizzes = $wpdb->get_results($wpdb->prepare("
								SELECT p.ID 
								FROM {$wpdb->posts} as p 
								LEFT JOIN {$wpdb->postmeta} as pm 
								ON p.ID = pm.post_id AND pm.meta_key = %s
								WHERE p.post_type = %s
								AND (pm.meta_value IN ({$coursess}) OR pm.meta_value IS NULL)" ,
								'vibe_quiz_course','quiz'
							));
							
							if(!empty($course_quizzes)){
								foreach ($course_quizzes as $id) {
										$quizzes [] = $id->ID ;
									}
							}								
						}
						$quizzes = apply_filters('wplms_start_tour_items_select',$quizzes,$_POST['tour_name'],$user_id);
						if(!empty($quizzes)){
							echo '<h3 class="heading"><span>'._x('Select quiz for Tour','select tour quiz','vibe').'</span></h3>';
							echo '<select name="course_select" class="course_select">';
							foreach($quizzes as $d){
   								echo '<option value="'.get_permalink($d).'" data-id="'.$d.'">'.get_the_title($d).'</option>';
   							}
   							echo '</select><a href="javascript:void(0)" class="start_course_tour button full">'._x('Select quiz to start tour','button','vibe').'</a>';
   						}else{
							echo '<div class="message">'._x('No quizzes found','no course found messsage in popup','vibe').'</div>';
						}
            		break;
            		default:
            			do_action('bp_tour_select_'.$_POST['tour_name']);
            		break;
            	} 
            }
            die();
		}

		function all_tours_array(){
			$all_tours = array();
			global $post;
			if(function_exists('vibe_get_option')){
        		$edit_id = vibe_get_option('create_course');
	        	
	        	if($edit_id == $post->ID || (function_exists('bp_current_component') && bp_current_component() == 'tours' && bp_current_action() == 'home')){
	        		/*$all_tours['create_course'] = array(
												'id'=>'create_course',
												'label' => _x('Course Creation Tour','creation tour label ','vibe'),
												'role'=>'instructor',
												'condition' => 1,
												'steps' => 'create_course_tour', 
											); */
	        	}
			}
			return apply_filters('bp_course_get_all_tours',$all_tours);
		}

		function get_tour($tour){

			$all_tours = $this->all_tours_array();


			$steps = array();
			if(!empty($all_tours[$tour])){
				if(method_exists($this,$all_tours[$tour]['steps'])){
					$steps = $this->{$all_tours[$tour]['steps']}();
				}elseif(function_exists($tour['steps'])){
					$steps = $all_tours[$tour]['steps']();
				}
				
				if(!empty($steps) && is_array($steps) && count($steps)){
					$all_tours[$tour]['steps'] = $steps;
					return $all_tours[$tour];
				}
					
			}
			
		}

		function get_tour_step($name,$step,$conditions){
			$steps = array();
			$all_tours = $this->all_tours_array();
			if(!empty($all_tours[$name])){
				if(method_exists($this,$all_tours[$name]['steps'])){
					$steps = $this->{$all_tours[$name]['steps']}($step,$conditions);
				}elseif(function_exists($tour['steps'])){
					$steps = $all_tours[$name]['steps']($step,$conditions);
				}	
			}
			return $steps;
		}


		function process_steps($full_steps,$step_key=null){

			$steps = array();

			if(empty($step_key)){
				$tour = array();
				$break_point=2;

				foreach($full_steps as $step){
					if(!empty($break_point)){
						if(!empty($step['ajax'])){
							$break_point--;
							$steps[]=$step;	
						}else{
							$steps[]=$step;			
						}
					}
				}
			}else{
				$steps = $full_steps[$step_key];
			}

			if(!empty($steps) && empty($step_key)){
				foreach($steps as $i=>$step){
					$steps[$i] = $this->process_step($step);
				}
			}else{
				$steps = $this->process_step($steps);
			}
			return $steps;
		}


		function process_step($step){
			$processed_step = array();
			$defaults = array(
			 		'element' => '',
					'title' => '',
					'content' => '',
					'backdrop' => false,
					'backdropContainer'=>'',
					'smartPlacement'=>false,
					'orphan'=>false,
					'reflex'=>false,
					'path'=>'',
					'placement'=>'top',
					'need_click'=> false,
					'duration '=>false,
					'ajax' => false,
					'delay'=>false,
					'backdropPadding'=>15
			 	);
			
				$step = wp_parse_args( $step, $defaults );
				$processed_step = array(
					'element' => $step['selector'],
					'title' => $step['title'],
					'content' => $step['content'],
					'backdrop' => $step['backdrop'],
					'backdropContainer'=>$step['parent'],
					'smartPlacement'=>$step['smartplacement'],
					'reflex'=>$step['reflex'],
					'ajax' => $step['ajax'],
					'orphan'=>$step['orphan'],
					'path'=>$step['path'],
					'need_click'=>$step['need_click'],
					/*'next'=>$step['next'],
					'prev'=>$step['prev'],*/
					'placement'=>$step['placement'],
					'delay'=> $step['delay'],
					'backdropPadding'=> $step['backdropPadding'],
				);
					

			return apply_filters('bp_course_tour_process_step',$processed_step);
		}

		
		//Process next step
		function tour_next_step(){
			
			$tour_options =  json_decode(stripcslashes($_POST['tour']));
			$name = $tour_options->name;
			
			$step = ($_POST['step']+2)-1; //Steps start from 0
			
			$next_steps = $this->get_tour_step($name,$step,$tour_options->conditions);

			$final_json_step = $this->process_step($next_steps);

			echo json_encode($final_json_step);
			die();
		}
	}
	//add_action('init',function(){
		WPLMS_Course_Tour::init();
	//});
}
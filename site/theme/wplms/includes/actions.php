<?php
/**
 * Action functions for WPLMS
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


class WPLMS_Actions{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Actions();

        return self::$instance;
    }

    private function __construct(){
    	
		add_action('init',array($this,'wplms_removeHeadLinks'));

		add_action('wp_head',array($this,'add_loading_css'));
		add_action('wp_enqueue_scripts',array($this,'include_child_theme_styling'));

		add_action('template_redirect',array($this,'site_lock'),1);

		add_action( 'wp_ajax_reset_googlewebfonts',array($this,'reset_googlewebfonts' ));
          
		add_action( 'wp_ajax_import_data',array($this,'import_data' ));
		add_action('wplms_be_instructor_button',array($this,'wplms_be_instructor_button'));

		add_action( 'pre_get_posts', array($this,'course_search_results' ));

		add_action(	'template_redirect',array($this,'vibe_check_access_check'),11);

		add_action( 'template_redirect', array($this,'vibe_check_course_archive' ));
		add_action( 'template_redirect', array($this,'vibe_product_woocommerce_direct_checkout' ));
		add_action('woocommerce_order_item_name',array($this,'vibe_view_woocommerce_order_course_details'),2,100);
		
		add_action('woocommerce_share',array($this,'wplms_social_buttons_on_product'),1000);
		add_action('bp_core_activated_user',array($this,'vibe_redirect_after_registration'),99,3);

		// Course Actions 
		add_action('wplms_course_unit_meta',array($this,'vibe_custom_print_button'));
		add_action('wplms_course_start_after_time',array($this,'wplms_course_progressbar'),1,2);
		add_action('wp_ajax_record_course_progress',array($this,'wplms_course_progress_record'));

		/*=== Profile Layout 3 === */
		add_action('bp_before_member_body',array($this,'member_layout_3_before_item_tabs'));
		add_action('wplms_after_single_item_list_tabs',array($this,'member_layout_3_after_item_tabs'));
		add_action('bp_after_member_body',array($this,'member_layout_3_end_body'));

		add_action('wplms_before_single_group_item_list_tabs',array($this,'group_layout_3_before_item_tabs'));
		add_action('wplms_after_single_group_item_list_tabs',array($this,'group_layout_3_after_item_tabs'));
		add_action('bp_after_group_body',array($this,'group_layout_3_end_body'));

		if(class_exists('WPLMS_tips') && method_exists('WPLMS_tips', 'init')){
			$tips = WPLMS_tips::init(); // Use instead of get_option to avoid unnecessary sql call
			if(!empty($tips->settings) && !empty($tips->settings['woocommerce_account'])){
				/* ==== WooCommerce MY Orders ==== */
				if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )  || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php')) || class_exists('WooCommerce')) {
					add_action( 'bp_setup_nav', array($this,'woo_setup_nav' ));
					add_action( 'bp_init', array($this, 'woo_save_account_details' ) ,999);
					add_action('woocommerce_save_account_details',array($this,'woo_myaccount_page'));
					//Remove WooCommerce wrappers
					remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
					remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
				}

				if ( in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )  || (function_exists('is_plugin_active') && is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php'))) {
					add_action( 'bp_setup_nav', array($this,'pmpro_setup_nav' ));
				}
			}
		}
 
		add_action( 'course-cat_add_form_fields', array( $this, 'add_category_fields' ));
		add_action( 'course-cat_edit_form_fields', array( $this, 'edit_category_fields' ));
		add_action( 'created_term', array($this,'save_category_meta'), 10, 2 );
		add_action( 'edited_term', array($this,'save_category_meta'), 10, 2 );
		//Transparent Header
		add_action('wp_head',array($this,'transparent_header_title_background'),99);
		
		add_action('wplms_certificate_before_full_content',array($this,'transparent_header_fix'));
		add_action('wplms_before_start_course_content',array($this,'transparent_header_fix'));

		// RESTRICT ACCESS
		add_action('wplms_before_members_directory',array($this,'wplms_before_members_directory'));
		add_action('wplms_before_activity_directory',array($this,'wplms_before_activity_directory'));
		add_action('wplms_before_groups_directory',array($this,'wplms_before_groups_directory'));
		add_action('wplms_before_member_profile',array($this,'wplms_before_member_profile'));

		//Profile settings radio button fix
		add_action('bp_activity_screen_notification_settings',array($this,'wrap_radio'));

		// My Courses search and filter : Also check filter.php function 
		add_action('bp_before_member_course_content',array($this,'mycourses_search'));

		add_action('wplms_before_single_course',array($this,'check_404_in_course'));

		//Related courses
		add_action('wplms_single_course_content_end',array($this,'show_related'));

		//BP Error in settings
		add_action('bp_template_content',array($this,'show_bp_error'),1);

		// Disable Controls on course status
		add_action('course_action_points',array($this,'course_action_points'));

		//Add hidden field for Course category/level/location detection
		add_action('wplms_after_course_directory',array($this,'detect_cat_level_location'));

		// Ajax Registration and login form styles
		add_action('wp_ajax_nopriv_wplms_get_signon_security',array($this,'wplms_get_signon_security'));
		add_action('wp_ajax_nopriv_wplms_signon',array($this,'wplms_signon'));
		add_action( 'login_form', array( $this, 'enable_ajax_registration_login'));
		add_action( 'wp_ajax_nopriv_wplms_forgot_password',array($this,'wplms_forgot_password'));

		//Footer Search
		add_action('wp_footer',array($this,'search'));

		//Course Tab scroll
		add_action('bp_before_course_header',array($this,'wplms_course_tabs_supports'),99);
      
		add_action('login_head',array($this,'remove_ajax_reg_login_from_wp_login'),99);

		//right click disbale in course status page
		add_action('template_redirect',array($this,'check_contextmenu_course_status'));

		//Add google captcha on buddypress registration page
		add_action('bp_signup_validate', array($this,'google_captcha_validate'),1);
		add_action('bp_before_registration_submit_buttons', array($this,'show_google_captcha'),1,1);

		add_action( 'wp_ajax_switch_demo_homes',array($this,'switch_demo_homes' ));
		add_action( 'wp_ajax_switch_demo_layout',array($this,'switch_demo_layout' ));

		add_action('wplms_customizer_custom_css',array($this,'demo_import_fixes'),10,1);

		//XSS vulenrability reported in search
		add_action('template_redirect',function(){
			if(isset($_GET['s'])){
				$_GET['s'] = esc_attr($_GET['s']);
			}
		});
		add_action('wplms_header_nav_search',array($this,'nav_search'));
		//Ajax header reload
		add_action('wp_footer',array($this,'header_reload'));
		add_action('wp_ajax_header_reload',array($this,'ajax_header_reload'));
    }

    function header_reload(){
    	$header_reload = vibe_get_option('header_reload');
    	if(!empty($header_reload)){
    		if(is_user_logged_in()){
    		?>
    		<script>
    			jQuery(document).ready(function($){
    				if($('.vbplogin').length && !$('.vbplogin').hasClass('smallimg')){

    					$.ajax({
				          	type: "POST",
				          	url: ajaxurl,
				          	data: { action: 'header_reload',
				                  security:'<?php echo vibe_get_option('security_key'); ?>',
				                },
				          	cache: false,
				          	success: function (html) {
				            	if(html){
				            		jQuery('#vibe_bp_login').append(html);
				            		$('.vbplogin').html($(html).find('#bpavatar').html() + '<span>'+$(html).find('#username').text()+'</span>');
				            		$('.vbplogin').addClass('smallimg');
				            	}
				          	}
				        });
    				}
    			});
    		</script>
    		<?php
    		}
    	}
    }

    function ajax_header_reload(){

    	if ( !isset($_POST['security']) || $_POST['security'] != vibe_get_option('security_key') || !is_user_logged_in() || !function_exists('bp_loggedin_user_link')){
	       die();
	    }

    	do_action( 'bp_before_sidebar_me' ); ?>
		  <div id="sidebar-me">
			    <div id="bpavatar">
			      <?php bp_loggedin_user_avatar( 'type=full' ); ?>
			    </div>
			    <ul>
			      <li id="username"><a href="<?php bp_loggedin_user_link(); ?>"><?php bp_loggedin_user_fullname(); ?></a></li>
			      <?php do_action('wplms_header_top_login'); ?>
			      <li><a href="<?php echo bp_loggedin_user_domain() . BP_XPROFILE_SLUG ?>/" title="<?php _e('View profile','vibe'); ?>"><?php _e('View profile','vibe'); ?></a></li>
			      <li id="vbplogout"><a href="<?php echo wp_logout_url( get_permalink() ); ?>" id="destroy-sessions" rel="nofollow" class="logout" title="<?php _e( 'Log Out','vibe' ); ?>"><i class="icon-close-off-2"></i> <?php _e('LOGOUT','vibe'); ?></a></li>
			      <li id="admin_panel_icon"><?php if (current_user_can("edit_posts"))
			           echo '<a href="'.vibe_site_url() .'wp-admin/" title="'.__('Access admin panel','vibe').'"><i class="icon-settings-1"></i></a>'; ?>
			      </li>
			    </ul> 
			    <ul>
			<?php
			$loggedin_menu = array(
			  'courses'=>array(
			              'icon' => 'icon-book-open-1',
			              'label' => __('Courses','vibe'),
			              'link' => bp_loggedin_user_domain().BP_COURSE_SLUG
			              ),
			  'stats'=>array(
			              'icon' => 'icon-analytics-chart-graph',
			              'label' => __('Stats','vibe'),
			              'link' => bp_loggedin_user_domain().BP_COURSE_SLUG.'/'.BP_COURSE_STATS_SLUG
			              )
			  );
			if ( bp_is_active( 'messages' ) ){
			  $loggedin_menu['messages']=array(
			              'icon' => 'icon-letter-mail-1',
			              'label' => __('Inbox','vibe').(messages_get_unread_count()?' <span>' . messages_get_unread_count() . '</span>':''),
			              'link' => bp_loggedin_user_domain().BP_MESSAGES_SLUG
			              );
			}
			if ( bp_is_active( 'notifications' ) ){
			  $n=vbp_current_user_notification_count();
			  $loggedin_menu['notifications']=array(
			              'icon' => 'icon-exclamation',
			              'label' => __('Notifications','vibe').(($n)?' <span>'.$n.'</span>':''),
			              'link' => bp_loggedin_user_domain().BP_NOTIFICATIONS_SLUG
			              );
			}
			if ( bp_is_active( 'groups' ) ){
			  $loggedin_menu['groups']=array(
			              'icon' => 'icon-myspace-alt',
			              'label' => __('Groups','vibe'),
			              'link' => bp_loggedin_user_domain().BP_GROUPS_SLUG 
			              );
			}

			$loggedin_menu['settings']=array(
			              'icon' => 'icon-settings',
			              'label' => __('Settings','vibe'),
			              'link' => bp_loggedin_user_domain().BP_SETTINGS_SLUG
			              );
			$loggedin_menu = apply_filters('wplms_logged_in_top_menu',$loggedin_menu);
			foreach($loggedin_menu as $item){
			  echo '<li><a href="'.$item['link'].'"><i class="'.$item['icon'].'"></i>'.$item['label'].'</a></li>';
			}
			?>
		    </ul>
		  
		  <?php
		  do_action( 'bp_sidebar_me' ); ?>
		  </div>
		  <?php do_action( 'bp_after_sidebar_me' );

		  die();
    }

    function nav_search(){

    	$course_search = vibe_get_option('course_search');

    	if($course_search ==2 || $course_search ==3){

    		$args = apply_filters('wplms_course_nav_cats',array(
		        'taxonomy'=>'course-cat',
		        'hide_empty'=>false,
		        'orderby'    => $order,
		        'order' => $sort,
		        'hierarchial'=>1,
		      ));

    		$terms = get_terms($args);
    		echo '<div class="nav_search">
    		<form method="GET" action="'.home_url().'">';

    		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
			    echo '<select name="'.$args['taxonomy'].'" style="max-width:100px;"><option value="">'._x('All','all courses in course nav search in categories','vibe').'</option>';
			    foreach ( $terms as $term ) {
			        echo '<option value="'.$term->slug.'" '.(($_GET[$args['taxonomy']] == $term->slug)?'selected':'').'>' . $term->name . '</li>';
			    }
			    echo '</select>';
			}
    			
    			
    		echo '</select>
    			<input type="text" name="s" placeholder="'._x('Search courses..','search placeholder','vibe').'" value="'.$_GET['s'].'" />
    			<input type="hidden" name="post_type" value="course" />
    		</form>
    		</div>';

    		if(vibe_get_customizer('header_style') == 'univ'){
    			echo '<style>.menu_nav_search{    grid-column-end: span 2;}</style>';
    		}
    	}else{
    		echo '<a id="new_searchicon"><i class="fa fa-search"></i></a>';
    	}
    	
    }

    function switch_demo_layout(){
    	if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'switch_demo_layouts') || empty($_POST['demo'])){
	       _e('Security check Failed. Contact Administrator.','vibe');
	       die();
	    }
	    $dir = get_home_path() . '/wp-content/themes/wplms/setup/installer/content/';
	    $demo = $_POST['demo'];
		if ( is_dir( $dir ) ) {
			$json_url = $dir.$demo.'/options.json';
			$json = file_get_contents($json_url);
			$json = json_decode($json ,TRUE);
			if(!empty($json)){
				foreach ( $json as $option => $value ) {
					if($option == 'vibe_customizer'){
						$ops = get_option('vibe_customizer');
						foreach($value as $key => $val){
							if(strpos($key,"google_fonts") == false){
								$ops[$key] = $val;
							}
						}
						//handle fonts here please 

						update_option( $option, $ops );

						break;
					}
				}
				$existing_vibe_options = get_option('wplms');
				$existing_vibe_options['demo_switch'] = $demo;
				update_option('wplms',$existing_vibe_options);
			}
		}
		die();
    }

    function _download_slider_actions($url){

		$file_name = basename( $url );

		
		$upload_dir = wp_upload_dir();
		$full_path = $upload_dir['path'].'/'.$file_name;
		if(file_exists($full_path)){
			@unlink($full_path);
		}


		$upload = wp_upload_bits( $file_name, 0, '');


		if ( $upload['error'] ) { // File already imported
			@unlink( $upload['file'] );

			$upload = wp_upload_bits( $file_name, 0, '');

			if ( $upload['error'] ) {
				return $upload['file'];
			}
			//new WP_Error( 'upload_dir_error', $upload['error'] );
		}

		// we check if this file is uploaded locally in the source folder.
		$response = wp_remote_get( $url ,array('timeout' => 120));
		WP_Filesystem();
		global $wp_filesystem;
		$wp_filesystem->put_contents( $upload['file'], $response['body'] );
			
		if ( is_array( $response ) && ! empty( $response['body'] ) && $response['response']['code'] == '200' ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			$headers = $response['headers'];
			WP_Filesystem();
			global $wp_filesystem;
			$wp_filesystem->put_contents( $upload['file'], $response['body'] );
		} else {
			// required to download file failed.
			@unlink( $upload['file'] );

			return new WP_Error( 'import_file_error', esc_html__( 'Remote server did not respond' ) );
		}


		return $upload['file'];
	}


    function switch_demo_homes(){
    	if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'switch_demo_layouts') || empty($_POST['demo'])){
	       _e('Security check Failed. Contact Administrator.','vibe');
	       die();
	    }
	    //find home page layout in json file and import it 
	    $dir = get_home_path() . '/wp-content/themes/wplms/setup/installer/content/';
	    $style = $_POST['demo'];
		if ( is_dir( $dir ) ) {
			$json_url = $dir.$style.'/default.json';
			$json = file_get_contents($json_url);
			$json = json_decode($json ,TRUE);
			$json = $json['page'];
			if(!empty($json)){

				foreach ($json as $key => $page) {

					if(!empty($page) && !empty($page['post_name']) && $page['post_name'] == 'home'){
						$metas = $page['meta'];
						unset($page['meta']);
						unset($page['post_id']);
						unset($page['guid']);
						unset($page['post_date']);
						unset($page['post_date_gmt']);
						unset($page['terms']);
						$page['post_type'] = 'page';
						
						$homepage = wp_insert_post($page);
						if ( !(is_wp_error( $homepage )) && !empty($homepage) ) {
							//slider import
							$slider_array = array();
							$ls_slider_array = array();

							$url = 'https://wplms.io/demos/demodata/content/'.$style;
							if(in_array($style,array('demo1'))){
								$slider_array = array($url."/classicslider1.zip");
							}
							if(in_array($style,array('demo2'))){
								$slider_array = array($url."/search-form-hero2.zip",$url."/news-hero4.zip",$url."/about1.zip");
							}
							if(in_array($style,array('demo3'))){
								$slider_array = array($url."/highlight-showcase4.zip");
							}

							if(in_array($style,array('demo4'))){
								$slider_array = array($url."/homeslider.zip",$url."/categories.zip");
							}

							if(in_array($style,array('demo5'))){
								$slider_array = array($url."/demo5.zip");
							}

							if(in_array($style,array('demo6'))){
								$slider_array = array($url."/homeslider.zip");
							}

							if(in_array($style,array('demo7'))){
								$slider_array = array($url."/demo7.zip");
							}

							if(in_array($style,array('demo8'))){
								$slider_array = array($url."/demo8.zip");
							}
							if(in_array($style,array('demo9'))){
								$slider_array = array($url."/demo9.zip",$url."/demo9_parallax.zip");
							}
							if(in_array($style,array('demo10'))){
								$ls_slider_array = array($url."/demo10-2.zip");
							}
							if(in_array($style,array('default'))){
								$ls_slider_array = array($url."/lsslider.zip");
							}
					        
					        if(!empty($ls_slider_array)){
					        	include LS_ROOT_PATH.'/classes/class.ls.importutil.php';
					        	if(class_exists('LS_ImportUtil')){
					        		foreach($ls_slider_array as $url) {
						        		$filepath = $this->_download_slider_actions($url);
										$import = new LS_ImportUtil($filepath);
									}
					        	}
					        }

							if(class_exists('RevSlider') && !empty($slider_array)){
								$slider = new RevSlider();
								foreach($slider_array as $url){
									$filepath = $this->_download_slider_actions($url);
									$slider->importSliderFromPost(true,true,$filepath);  
								}	
							}
							foreach($metas as $key => $meta){
								//print_R($key);
								if(!empty($meta)){
									/*if($key == '_builder_settings'){
										//print_R($meta);
										$meta =serialize($meta);
										$meta = "'".mysql_real_escape_string($meta)."'";
										global $wpdb;

										$wpdb->query("INSERT INTO {$wpdb->postmeta} (post_id,meta_key,meta_value) VALUES ($homepage,'_builder_settings',$meta)");
									}else{
										update_post_meta($homepage, $key,$meta);
									}*/
									update_post_meta($homepage, $key,$meta);
								}
								
							}
							update_post_meta($homepage,"_add_content","no");
							update_option( 'page_on_front', $homepage );
							update_option( 'show_on_front', 'page' );
							update_option('wplms_site_style',$style);
							flush_rewrite_rules( true );
						}

					}
				}
			}
			
		}
	    die();
    }

    function show_google_captcha(){
    	
    	$google_captcha_public_key = vibe_get_option('google_captcha_public_key');
    	if(empty($google_captcha_public_key)){
    		return;
    	}

        if ( ! wp_script_is( 'google-recaptcha', 'enqueued' ) ) {
        	$wp_locale = get_locale();
        	$translate_captcha = apply_filters('translate_wplms_reg_form_captcha',1);
            if(!empty($wp_locale) && $translate_captcha){
                preg_match("/[a-z]*/", $wp_locale, $locale);
                wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js'.(!empty($locale[0])?'?hl='.$locale[0]:'') );
            }else{
                wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js' );
            }
        }
        echo '<div class="g-recaptcha" data-theme="clean" data-sitekey="'.$google_captcha_public_key.'" style="padding:15px 0;"></div>';
        ?>
        <script>
			jQuery(window).load(function(){
				
				var $= jQuery;
				if(typeof grecaptcha !== "undefined"){
					$("#signup_submit").addClass("disabled");

    				$("#signup_submit").on("click",function(event){
    					var $this = $(this);
    					response = grecaptcha.getResponse();
				        if(response.length == 0){
				        	$this.parent().find(".message").remove();
			            	$this.parent().append("<div class='message' style='margin-top:15px;'>"+vibe_shortcode_strings.captcha_mismatch+"</div>");
			            	$(".message").click(function(){$(this).hide(200);});
			            }else{
			            	$this.removeClass("disabled");
			            }
			            
    					if($(this).hasClass("disabled")){
    						event.preventDefault();
    					}
    				});

    			}
			});
    	</script>
    	<?php
    }

    function google_captcha_validate(){

    	$google_captcha_private_key = vibe_get_option('google_captcha_private_key');
    	if(empty($google_captcha_private_key)){
    		return;
    	}

    	$gresponse = $_POST['g-recaptcha-response'];
    	$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify',array(
    		'timeout'     => 30,
    		'method' => 'POST',
			'body' => array( 
    					'secret'   => $google_captcha_private_key,
    					'response' => $gresponse
					),
    		));

    	$api_response = json_decode( wp_remote_retrieve_body( $response ), true );

    	if(!$api_response['success']){
    		
    		if(is_array($api_respose['error-codes'])){
    			$api_respose['error-codes'] = $api_respose['error-codes'][0];
    		}

    		$message = '';
    		switch($api_respose['error-codes']){
    			case 'missing-input-secret':
    				$message = 'The secret parameter is missing.';
    			break;
    			case 'invalid-input-secret':
    				$message = 'The secret parameter is invalid or malformed.';
    			break;
    			case 'missing-input-response':
    				$message = 'The response parameter is missing.';
    			break;
    			case 'invalid-input-response':
    				$message = 'The response parameter is invalid or malformed.';
    			break;
    			case 'bad-request':
    				$message = 'The request is invalid or malformed.';
    			break;
    		}
    		wp_die($message,_x('Captcha validation failed','captcha mismatch','vibe'),array('response'=>200,'back_link'=>true));
    	}
    }
	
    function remove_ajax_reg_login_from_wp_login(){
    	$actions = WPLMS_Actions::init();
	    remove_action( 'login_form', array( $actions, 'enable_ajax_registration_login'));
    }

    function add_loading_css(){

    	$page_loader = vibe_get_option('page_loader');
   		if(!empty($page_loader) && !is_customize_preview()){
   			ob_start();
   			if($page_loader == 'pageloader1'){
	    	?>
	    	<style>
	    	body.loading .pusher:before{
				content:'';
				position:fixed;
				left:0;
				top:0;
				width:100%;
				height:100%;
				background:rgba(255,255,255,0.95);
				z-index:999;
			}

			body.loading.pageloader1 .global:before,
			body.loading.pageloader1 .global:after{
				content:'';
				position:fixed;
				left:50%;
				top:50%;
				margin:-20px 0 0 -20px;
				width:40px;
				height:40px;
				border-radius:50%;
				z-index:9999;
				border: 4px solid transparent;
				border-top-color:#009dd8;
			    z-index: 9999;
			    animation: rotate linear 1.5s infinite;
			}
			body.loading.pageloader1 .global:after{
				margin:-27px 0 0 -27px;
				width:54px;
				height:54px;
				border-top-color: transparent;
			    border-left-color: #009dd8;
			    animation: rotate linear 1s infinite;
			}
			
			@keyframes rotate {
			    0% {
			        transform: rotate(0deg);      
			    }
			    50% {
			        transform: rotate(180deg);
			    }
			    100% {
			        transform: rotate(360deg);
			    }
			}
			</style>
	    	<?php
	    	}else{

	    	?>
	    	<style>
	    	body.loading .pusher:before{
				content:'';
				position:fixed;
				left:0;
				top:0;
				width:100%;
				height:100%;
				background:rgba(255,255,255,0.95);
				z-index:999;
			}
	    	body.loading.pageloader2 .global:before,
			body.loading.pageloader2 .global:after{
				content:'';
				position:fixed;
				left:50%;
				top:50%;
				margin:-8px 0 0 -8px;
				width:15px;
				height:15px;
				border-radius:50%;
				z-index:9999;
				background:#009dd8;
			    z-index: 9999;
			    animation: flipzminus linear 1s infinite;
			}
			body.loading.pageloader2 .global:after{
			    animation: flipzplus linear 1s infinite;
			}
			@keyframes flipzminus {
			    0% {
			        transform: translateX(-30px);
			        opacity:1;
			    }
			    50% {
			        transform: translateX(0px);
			        opacity:0.5;
			    }
			    100% {
			        transform: translate(30px);
			        opacity:1;
			    }
			}
			@keyframes flipzplus {
			    0% {
			        transform: translate(30px);
			        opacity:1;
			    }
			    50% {
			        transform: translateX(0px);
			        opacity:0.5;
			    }
			    100% {
			        transform: translateX(-30px);
			        opacity:1;
			    }
			}
	    	</style>
	    	<?php
	    	}
	    	$css = ob_get_clean();
	        $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
	        $buffer = str_replace(': ', ':', $buffer);
	        $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
	        echo($buffer);
	    }
    }
	function check_404_in_course(){
	 	if(is_404()){
	   		$error404 = vibe_get_option('error404');
	   		if(isset($error404)){
	       		$page_id=  intval($error404);
	       		if(function_exists('icl_object_id')){
			        $page_id = icl_object_id($page_id, 'page', true);
			    }
	       		wp_redirect( get_permalink( $page_id ),301); 
	       		exit;
	   		}
	 	}
	}   

	function course_action_points(){

		$course_status_controls = vibe_get_option('course_status_controls');
		if(empty($course_status_controls)){
			return;
		}
		$action_controls = apply_filters('wplms_course_status_action_controls',array(
			'hide_timeline'=>array(
				'icon'=>'fa fa-exchange',
				'title'=>_x('Hide Timeline','vibe'),
				),
			'fullscreen'=>array(
				'icon'=>'fa fa-expand',
				'title'=>_x('Go fullscreen','vibe'),
				),
			));
		?>
		<div class="course_action_controls">
			<ul>
			<?php
				if(!empty($action_controls)){
					foreach($action_controls as $key => $control){
						?>
						<li class="<?php echo $key; ?>">
							<?php 
								if(!empty($control['html'])){echo $control['html'];}
								else{ ?>
								<a class="<?php echo $control['icon']; ?> action_control"></a>
							<?php } ?>
						</li>
						<?php
					}
				}
			?>
			</ul>
		</div>
		<?php
	}

    function mycourses_search(){
    	if ( bp_is_current_action( BP_COURSE_RESULTS_SLUG ) || bp_is_current_action( BP_COURSE_STATS_SLUG )/* || bp_is_current_action('instructor-courses')*/)
    		return;
    	?>
    	<div class="item-list-tabs" id="subnav" role="navigation">
		<ul>
			<?php do_action( 'bp_course_directory_course_types' ); ?>
			<li>
				<div class="dir-search" role="search">
					<?php bp_directory_course_search_form(); ?>
				</div><!-- #group-dir-search -->
			</li>
			<li class="switch_view">
				<div class="grid_list_wrapper">
					<a id="list_view" class="active"><i class="icon-list-1"></i></a>
					<a id="grid_view"><i class="icon-grid"></i></a>
				</div>
			</li>
			<li id="course-order-select" class="last filter">

				<label for="course-order-by"><?php _e( 'Order By:', 'vibe' ); ?></label>
				<select id="course-order-by">
					<?php
					?>
					<option value=""><?php _e( 'Select Order', 'vibe' ); ?></option>
					<?php
						if(bp_is_current_action('instructor-courses')){
							?>
							<option value="draft"><?php _e( 'Draft courses', 'vibe' ); ?></option>
							<option value="pending"><?php _e( 'Submitted for Approval', 'vibe' ); ?></option>
							<option value="published"><?php _e( 'Published Courses', 'vibe' ); ?></option>
							<?php
						}else{
							?>
							<option value="pursuing"><?php _ex( 'Pursuing courses','Course Status filter in Profile My courses section', 'vibe' ); ?></option>
							<option value="finished"><?php _ex( 'Finished Courses','Course Status filter in Profile My courses section','vibe' ); ?></option>
							<option value="active"><?php _ex( 'Active courses','Course Status filter in Profile My courses section','vibe' ); ?></option>
							<option value="expired"><?php _ex( 'Expired courses','Course Status filter in Profile My courses section','vibe' ); ?></option>
							<?php
						}
					?>
					<option value="newest"><?php _ex( 'Newly Published','filter in Profile My courses section','vibe' ); ?></option>
					<option value="alphabetical"><?php _ex( 'Alphabetical','filter in Profile My courses section', 'vibe' ); ?></option>
					<option value="start_date"><?php _ex( 'Start Date','filter in Profile My courses section', 'vibe' ); ?></option>
					<?php do_action( 'bp_course_directory_order_options' ); ?>
				</select>
			</li>
		</ul>
	</div>
    	<?php
    }
    function wrap_radio(){
    	?>
    	<script>
    		jQuery(document).ready(function($){
    			$('td.yes,td.no').each(function(){
    				var html = $(this).html();
    				$(this).html('<div class="radio">'+html+'</div>');
    			});
    		});
    	</script>
    	<?php
    }
    /*
    CSS BACKGROUND WHICH APPLIES WHEN TRANSPARENT HEADER IS ENABLED
     */
    function transparent_header_title_background(){ 
    	if(is_admin()){
    		return;
    	}
    	$header_style =  vibe_get_customizer('header_style');
    	if($header_style == 'transparent' || $header_style == 'generic'){ 
	    	if(is_page() || is_single() || (function_exists('bp_is_directory') &&  bp_is_directory()) || (function_exists('bp_current_component') &&  bp_current_component()) || is_archive() || is_search() || (is_home() && !is_front_page())){ 
	    		global $post,$bp;

	    		if(!is_archive() || bp_is_directory()){
	    			if(empty($post->ID)){
	    				$title_bg = get_post_meta($bp->pages->course->id,'vibe_title_bg',true);
	    			}else{
	    				$title_bg = get_post_meta($post->ID,'vibe_title_bg',true);
	    			}
	    		}
	    		
	    		if(is_numeric($title_bg)){
    				$bg = wp_get_attachment_image_src($title_bg,'full');
    				
    				if(!empty($bg) && !empty($bg[0]))
    					$title_bg = $bg[0];
    			}

    			if(empty($title_bg) || strlen($title_bg) < 5 ){
	    			$title_bg = vibe_get_option('title_bg');
	    			if(empty($title_bg)){
	    				$title_bg = VIBE_URL.'/assets/images/title_bg.jpg';
	    			}
	    		}
				
				$title_bg = apply_filters('wplms_title_bg','background:url('.$title_bg.') !important;');
	    		$title_color= apply_filters('wplms_title_color','color:#fff !important;');
	    		$title_link= apply_filters('wplms_link_color','color:#222 !important;');
				if(!empty($title_bg)){
	    		?>
	    		<style>.course_header,.group_header{
	    			<?php echo $title_bg; ?>
	    			}#title{<?php echo $title_bg; ?> padding-bottom:30px !important; background-size: cover;}

	    		#title.dark h1,#title.dark h5,#title.dark a:not(.button),#title.dark,#title.dark #item-admins h3,#item-header.dark #item-header-content .breadcrumbs li+li:before,#title.dark .breadcrumbs li+li:before,.group_header.dark div#item-header-content,.group_header.dark #item-header-content h3 a,.bbpress.dark .bbp-breadcrumb .bbp-breadcrumb-sep:after,#item-header.dark #item-admins h3,#item-header.dark #item-admins h5,#item-header.dark #item-admins h3 a,#item-header.dark #item-admins h5 a,
	    		#item-header.dark #item-header-content a,#item-header.dark #item-header-content{<?php echo $title_link; ?>}
	    		#title.light h1,#title.light h5,#title.light a:not(.button),#title.light,#title.light #item-admins h3,#item-header.light #item-header-content .breadcrumbs li+li:before,#item-header.light #item-admins h3,#item-header.light #item-admins h5,#item-header.light #item-admins h3 a,#item-header.light #item-admins h5 a,#title.light .breadcrumbs li+li:before,.group_header.light div#item-header-content,.group_header.light #item-header-content h3 a,.bbpress.light .bbp-breadcrumb .bbp-breadcrumb-sep:after,#item-header.light #item-header-content a,#item-header.light #item-header-content{
	    			<?php echo $title_color .'!important;'; ?>
    			}
    			.bp-user div#global .pusher .member_header div#item-header:not(.cover_image){
    				<?php echo $title_bg; ?>
    			}
	    		.group_header #item-header{background-color:transparent !important;}
	    	</style>
	    		<?php
	    		}
	    	}
    	}

		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
    }

    function transparent_header_fix(){
    	$header_style =  vibe_get_customizer('header_style');

    	if($header_style == 'transparent' || $header_style == 'generic'){ 
    		?>
    		<section id="title"></section>
    		<?php
    	}
    }
    function include_child_theme_styling(){
    	if (get_template_directory() !== get_stylesheet_directory()) {
	      	wp_enqueue_style('wplms_child_theme_style',get_stylesheet_uri(),'wplms-style');
	    }
    }


    function site_lock(){
    	$site_lock = vibe_get_option('site_lock');
    	$register_page_id = vibe_get_directory_page('register');
    	$activate_page_id = vibe_get_directory_page('activate');

    	$exlusions = apply_filters('wplms_site_lock_exclusions',array($register_page_id,$activate_page_id));
    	$bypass = apply_filters('wplms_bp_page_site_lock_bypass',1);
    	global $post;
    	if(!empty($site_lock) && !is_user_logged_in() && !is_front_page() && !in_Array($post->ID,$exlusions) && (bp_current_component()!='activate') && $bypass){
    		$url = apply_filters('wplms_site_lock_redirect_url',home_url());
    		wp_redirect( $url );
        	exit();
    	}
    }
    
	function wplms_removeHeadLinks(){
	  $xmlrpc = vibe_get_option('xmlrpc');
	  if(isset($xmlrpc) && $xmlrpc){
	    remove_action('wp_head', 'rsd_link');
	    remove_action('wp_head', 'wlwmanifest_link'); 
	    add_filter('xmlrpc_enabled','__return_false');
	  }

	  $style = get_option('wplms_site_style');
	  if($style == 'points_system'){
	  	add_filter('vibe_builder_thumb_styles',array($this,'custom_vibe_builder_thumb_styles'));
		add_filter('vibe_featured_thumbnail_style',array($this,'custom_vibe_featured_thumbnail_style'),1,3);
	  }
	  if($style == 'childone'){
	  	add_filter('vibe_builder_thumb_styles',array($this,'custom_vibe_builder_thumb_styles1'));  
		add_filter('vibe_featured_thumbnail_style',array($this,'custom_vibe_featured_thumbnail_style'),1,3);
	  }
	  if($style == 'one_instructor'){
	  	add_filter('vibe_builder_thumb_styles',array($this,'custom_vibe_builder_thumb_styles2'));  
		add_filter('vibe_featured_thumbnail_style',array($this,'custom_vibe_featured_thumbnail_style'),1,3);
	  }

	}

	function custom_vibe_builder_thumb_styles($styles){
		$styles['modern_block'] =  get_template_directory().'assets/images/thumb_modern.png';
		return $styles;
	}

	function custom_vibe_builder_thumb_styles1($styles){
		$styles['modern_block1'] =  get_template_directory().'assets/images/thumb_modern.png';
		return $styles;
	}

	function custom_vibe_builder_thumb_styles2($styles){
		$styles['modern_block2'] =  get_template_directory().'assets/images/thumb_modern.png';
		return $styles;
	}

	function custom_vibe_featured_thumbnail_style($thumbnail_html,$post,$style){

		if($style == 'modern_block'){ 
			$instructors = apply_filters('wplms_course_instructors',$post->post_author,$post->ID);
	        $thumbnail_html ='';
	        $thumbnail_html .= '<div class="block modern_course">';
	        $thumbnail_html .= '<div class="block_media">';
	        $thumbnail_html .= '<a href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail($post->ID,'medium').'</a>';
	        $thumbnail_html .= '</div>';
	        $thumbnail_html .= '<div class="block_content">';
	        $thumbnail_html .= '<h4 class="block_title"><a href="'.get_permalink($post->ID).'" title="'.$post->post_title.'">'.$post->post_title.'</a></h4>';
	        $thumbnail_html .= '<span>'.__('by ','vibe');
	        if(is_array($instructors) && count($instructors) > 1){
	        	 $thumbnail_html .= bp_core_get_user_displayname($post->post_author).' ( & '.(count($instructors)-1).' more )';
			}else{
				 $thumbnail_html .= bp_core_get_user_displayname($post->post_author);
			}
	        $thumbnail_html .= '</span>';
	        $thumbnail_html .= '<div class="course_meta">
	        <i class="icon-users"></i> '.get_post_meta($post->ID,'vibe_students',true).'
	        '.bp_course_get_course_credits().'
	        </div>';
	        $thumbnail_html .= '';
	        $thumbnail_html .= '</div></div>';
	    }

	    if($style == 'modern_block1'){
	    	$thumbnail_html ='';
	        $thumbnail_html .= '<div class="block modern_course">';
	        $thumbnail_html .= '<div class="block_media">';
	        $thumbnail_html .= '<a href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail($post->ID,'medium').'</a>';
	        $thumbnail_html .= '<a href="'.bp_core_get_user_domain($post->post_author) .'" class="course_block_instructor">'.bp_course_get_instructor_avatar().'</a>';
	        $thumbnail_html .= '</div>';
	        $thumbnail_html .= '<div class="block_content">';
	        $thumbnail_html .= '<h4 class="block_title"><a href="'.get_permalink($post->ID).'" title="'.$post->post_title.'">'.$post->post_title.'</a></h4>';
	        $thumbnail_html .= bp_course_get_type();
	        $thumbnail_html .= '</div></div>';
	    }

	    if($style == 'modern_block2'){ 
	        $thumbnail_html ='';
	        $thumbnail_html .= '<div class="block modern_course">';
	        $thumbnail_html .= '<div class="block_media">';
	        $thumbnail_html .= '<a href="'.get_permalink($post->ID).'">'.get_the_post_thumbnail($post->ID,'medium').'</a>';
	        $thumbnail_html .= '</div>';
	        $thumbnail_html .= '<div class="block_content">';
	        $thumbnail_html .= '<h4 class="block_title"><a href="'.get_permalink($post->ID).'" title="'.$post->post_title.'">'.$post->post_title.'</a></h4>';
	        $thumbnail_html .= '<span>';
	        $thumbnail_html .= get_the_term_list($post->ID,'course-cat','',',');
	        $thumbnail_html .= '</span>';
	        $thumbnail_html .= '<div class="course_meta">
	        <i class="icon-users"></i> '.get_post_meta($post->ID,'vibe_students',true).'
	        '.bp_course_get_course_credits().'
	        </div>';
	        $thumbnail_html .= '';
	        $thumbnail_html .= '</div></div>';
	    }

	    return $thumbnail_html;
	}

	function reset_googlewebfonts(){ 
      	echo "reselecting..";
      	$r = get_option('google_webfonts');
      	if(isset($r)){
          	delete_option('google_webfonts');
      	}
	  	die();
	}

	function import_data(){
		if(!current_user_can('manage_options'))
  			die();

		$name = stripslashes($_POST['name']);
		$code = base64_decode(trim($_POST['code'])); 
		if(is_string($code))
    		$code = unserialize ($code);
		
		$value = get_option($name);
		if(isset($value)){
      		update_option($name,$code);
		}else{
			echo "Error, Option does not exist !";
		}
		die();
	}


	function wplms_be_instructor_button(){
		$teacher_form = vibe_get_option('teacher_form');

		if(isset($teacher_form) && is_numeric($teacher_form)){
			echo '<a href="'.(isset($teacher_form)?get_permalink($teacher_form):'#').'" class="button create-group-button full">'. __( 'Become an Instructor', 'vibe' ).'</a>';  
		}
	}

	function course_search_results($query){

	  if(!$query->is_search() && !$query->is_main_query())
	    return $query;

	  if(isset($_GET['course-cat']))
	      $course_cat = $_GET['course-cat'];

	  if(isset($_GET['instructor']))
	      $instructor = $_GET['instructor'];  

	  if ( function_exists('get_coauthors')) {
	    if(isset($instructor) && $instructor !='*' && $instructor !='' && is_numeric($instructor)){
	      $instructor_name = strtolower(get_the_author_meta('user_login',$instructor)); 
	      //$query->set('author_name', $instructor_name);
	      $query->query['author_name']=$instructor_name;
	    }
	  }else{
	    if(isset($instructor) && $instructor !='*' && $instructor !=''){
	      $query->set('author', $instructor);
	    }
	  }

	  if(isset($course_cat) && $course_cat !='*' && $course_cat !=''){
	    $query->set('course-cat', $course_cat);
	  }
	  return $query;
	}


	function vibe_check_access_check(){ 

	    if(!is_singular(array('unit','question')))
	      return;

	    $flag=0;
	    global $post;

		$free=get_post_meta(get_the_ID(),'vibe_free',true);
   		if(vibe_validate($free) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && is_user_logged_in())){
	        	$flag=0;
	        	return;
	    }else
	    	$flag = 1;

	    if(current_user_can('edit_posts')){
	        $flag=0;
	        $instructor_privacy = vibe_get_option('instructor_content_privacy');
	        $user_id=get_current_user_id();
	        if(isset($instructor_privacy) && $instructor_privacy && !current_user_can('manage_options')){
	            if($user_id != $post->post_author)
	              $flag=1;
	        }
	    }

	    if($post->post_type == 'unit'){
	      	$post_type = __('UNITS','vibe');
	    }else if($post->post_type == 'question'){
	      	$post_type = __('QUESTIONS','vibe');
	    }

	    $message = sprintf(__('DIRECT ACCESS TO %s IS NOT ALLOWED','vibe'),$post_type);
	    $flag = apply_filters('wplms_direct_access_to'.$post->post_type,$flag,$post);
	    if($flag){
	        wp_die($message,$message,array('back_link'=>true));
	    }
	}

	
	function vibe_check_course_archive(){

	    if(is_post_type_archive('course') && !is_search()){
	        $pages=get_site_option('bp-pages');
	        if(is_array($pages) && isset($pages['course'])){
	          $all_courses = get_permalink($pages['course']);
	          wp_redirect($all_courses);
	          exit();
	        }
	    }
	}

	// Course functions
	function vibe_custom_print_button(){
		$print_html='<a href="#" class="print_unit"><i class="icon-printer-1"></i></a>';
		echo apply_filters('wplms_unit_print_button',$print_html);  
	}


	function wplms_course_progressbar($course_id,$unit_id){
	    $user_id=get_current_user_id();

	    
	    $percentage = bp_course_get_user_progress($user_id,$course_id);

	    $units = array();
	    if(function_exists('bp_course_get_curriculum_units'))
	    	$units = bp_course_get_curriculum_units($course_id);

	    $total_units = count($units);
	    if(empty($total_units))
	    	$total_units = 1;
	   	if(empty($percentage)){
   			$percentage = 0;
	  	}
	    
	    if($percentage > 100)
	      $percentage= 100;

	    $unit_increase = round(((1/$total_units)*100),2);

	    echo '<div class="progress course_progressbar" data-increase-unit="'.$unit_increase.'" data-value="'.$percentage.'">
	             <div class="bar animate cssanim stretchRight load" style="width: '.$percentage.'%;"><span>'.$percentage.'%</span></div>
	           </div>';

	}


	function wplms_course_progress_record(){
	    $course_id = $_POST['course_id'];
	    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($course_id) ){
	       _e('Security check Failed. Contact Administrator.','vibe');
	       die();
	    }

	    $course_progress = $_POST['progress'];
	    $user_id = get_current_user_id();
	    $progress='progress'.$course_id;
	    $flag = apply_filters('wplms_allow_course_progress_record',1,$user_id,$course_id);
	    if($flag){
	    	update_user_meta($user_id,$progress,$course_progress);
	    }
	    die();
	}
	// END course Functions	
	// 
	// 	DIRECT CHECKOUT
	// 		
	function vibe_product_woocommerce_direct_checkout(){

	  	if(in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php')) || function_exists('WC')){
	        $check=vibe_get_option('direct_checkout');
	        $check =intval($check);
	    	if(isset($check) &&  $check == 2){
	      		if( is_single() && get_post_type() == 'product' && isset($_GET['redirect'])){
	          		global $woocommerce;
	          		$found = false;
	          		$product_id = get_the_ID();
	          		$courses = vibe_sanitize(get_post_meta(get_the_ID(),'vibe_courses',false));
	          		if(isset($courses) && is_array($courses) && count($courses)){
	            		if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
	              			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
	                			$_product = $values['data'];
	                			if ( $_product->id == $product_id )
	                  				$found = true;
	              			}
	              			// if product not found, add it
	              			if ( ! $found )
	                			WC()->cart->add_to_cart( $product_id );
	                		$checkout_url = $woocommerce->cart->get_checkout_url();
	                		wp_redirect( $checkout_url);  
        				}else{
	              			// if no products in cart, add it
	              			WC()->cart->add_to_cart( $product_id );
	              			$checkout_url = $woocommerce->cart->get_checkout_url();
	              			wp_redirect( $checkout_url);  
	            		}
	            		exit();
	          		}
	      		}
	    	}
	    	if(isset($check) &&  $check == 3){ 
	      		if( is_single() && get_post_type() == 'product' && isset($_GET['redirect'])){ 
	          		global $woocommerce; 
	          		$found = false;
	          		$product_id = get_the_ID();
	          		$courses = vibe_sanitize(get_post_meta(get_the_ID(),'vibe_courses',false));
	          
	          		if(isset($courses) && is_array($courses) && count($courses)){
	            		if ( sizeof( WC()->cart->get_cart() ) > 0 ) {
	              			foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
	                		$_product = $values['data'];
	                			if ( $_product->id == $product_id )
	                  				$found = true;
	              			}
	              			// if product not found, add it
	              			if ( ! $found )
	                			WC()->cart->add_to_cart( $product_id );
	                		$cart_url = esc_url( wc_get_cart_url() ); 
	                		wp_redirect( $cart_url); 
	            		}else{
			              	WC()->cart->add_to_cart( $product_id );
			              	$cart_url = esc_url( wc_get_cart_url() ); 
			              	wp_redirect( $cart_url);
	            		}
	            		exit();
	          		}
	      		}
	    	}
	  	} // End if WooCommerce Installed
	}

	function vibe_view_woocommerce_order_course_details($html, $item ){
		$product_id=$item['item_meta']['_product_id'][0];
	  	if(empty($product_id)){
	  		$product_id = $item->get_product_id();
	  	}
	  	if(isset($product_id) && is_numeric($product_id)){
	      	$courses = get_post_meta($product_id,'vibe_courses',true);
	      	if(!empty($courses) && is_Array($courses)){
		        $html .= ' [ <i>'.__('COURSE : ','vibe');
	        	foreach($courses as $course){ 
	          		if(is_numeric($course)){ 
	           			$html .= '<a href="'.get_permalink($course).'"><strong><i>'.get_post_field('post_title',$course).'</i></strong></a> ';
	          		}
	        	}
	        	$html .=' </i> ]';
	      	}
	  	}
	  	return $html;

	}
	
	function wplms_social_buttons_on_product(){
	    echo do_shortcode('[social_buttons]');
	}


	function vibe_redirect_after_registration($user_id, $key, $user){
		
		$bp = buddypress();
		
		$bp->activation_complete = true;

		if(current_user_can('manage_options'))
			return;

		//do not redirect if doing ajax - @Buddydev - Brajesh Singh.
		if ( defined('DOING_AJAX') ) {
			return ;
		}

	    if ( is_multisite() )
	      $hashed_key = wp_hash( $key );
	    else
	      $hashed_key = wp_hash( $user_id );

	    if ( file_exists( BP_AVATAR_UPLOAD_PATH . '/avatars/signups/' . $hashed_key ) )
	      @rename( BP_AVATAR_UPLOAD_PATH . '/avatars/signups/' . $hashed_key, BP_AVATAR_UPLOAD_PATH . '/avatars/' . $user_id );

	     
	    
	    $pageid=vibe_get_option('activation_redirect');
	    if(empty($pageid)){
	   	  wp_set_auth_cookie( $user_id, true, false );
	      bp_core_add_message( __( 'Your account is now active!', 'vibe' ) );
	      bp_core_redirect( apply_filters ( 'wplms_registeration_redirect_url', bp_core_get_user_domain( $user_id ), $user_id ) );      
	    }else{
	    	wp_set_auth_cookie( $user_id, true, false );	
	    	if($pageid == 'dashboard'){
	    		if(defined('WPLMS_DASHBOARD_SLUG'))
	    			$link = bp_core_get_user_domain($user_id).WPLMS_DASHBOARD_SLUG;
	    	}else if($pageid == 'profile'){
	    		if(function_exists('bp_loggedin_user_domain'))
	    			$link = bp_core_get_user_domain($user_id);
	    	}else if($pageid == 'mycourses'){
	    		if(defined('BP_COURSE_SLUG'))
	    			$link = trailingslashit( bp_core_get_user_domain($user_id). BP_COURSE_SLUG );
	    	}else{
	    		$link = get_permalink($pageid);
	    	}
	      	bp_core_redirect( apply_filters ( 'wplms_registeration_redirect_url',$link, $user_id ) );      
	    }
	}

	/*=== Layout 3 ===*/
	function member_layout_3_before_item_tabs(){
		$layout = vibe_get_customizer('profile_layout');
		if($layout != 'p3')
			return;
		?>
			<div class="row">
				<div class="col-md-3">
		<?php
	}

	function member_layout_3_after_item_tabs(){
		$layout = vibe_get_customizer('profile_layout');
		if($layout != 'p3')
			return;
		?>
			</div>
			<div class="col-md-9">
		<?php
	}

	function member_layout_3_end_body(){
		$layout = vibe_get_customizer('profile_layout');
		if($layout != 'p3')
			return;
		?>
			</div>
		</div>
		<?php
	}

	function group_layout_3_before_item_tabs(){
		$layout = vibe_get_customizer('group_layout');
		if($layout != 'g3')
			return;
		?>
			<div class="row">
				<div class="col-md-3">
		<?php
	}
	function group_layout_3_after_item_tabs(){
		$layout = vibe_get_customizer('group_layout');
		if($layout != 'g3')
			return;
		?>
			</div>
			<div class="col-md-9">
		<?php
	}

	function group_layout_3_end_body(){
		$layout = vibe_get_customizer('profile_layout');
		if($layout != 'p3')
			return;
		?>
			</div>
		</div>
		<?php
	}


	function woo_setup_nav(){
		global $bp;
		$myaccount_pid = get_option('woocommerce_myaccount_page_id');

		if(is_numeric($myaccount_pid)){
			$slug = get_post_field('post_name',$myaccount_pid);
			bp_core_new_nav_item( array( 
	            'name' => __('My Orders', 'vibe' ), 
	            'slug' => $slug , 
	            'position' => 99,
	            'screen_function' => array($this,'woo_myaccount'), 
	            'default_subnav_slug' => '',
	            'show_for_displayed_user' => bp_is_my_profile(),
	            'default_subnav_slug'=> $slug
	      	) );


			$link = trailingslashit( bp_loggedin_user_domain() . $slug );

			bp_core_new_subnav_item( array(
				'name'            => __('My Orders', 'vibe' ), 
				'slug'            => $slug,
				'parent_slug'     => $slug,
				'parent_url'      => $link,
				'position'        => 10,
				'item_css_id'     => 'nav-' . $slug,
				'screen_function' => array( $this, 'woo_myaccount' ),
				'user_has_access' => bp_is_my_profile(),
				'no_access_url'   => home_url(),
			) );
			
			$endpoints = array(
				'edit-account' => get_option( 'woocommerce_myaccount_edit_account_endpoint', 'edit-account' ),
			);

			$i=20;
			foreach($endpoints as $key => $endpoint){
				switch ( $key ) {
					case 'edit-account' :
						$title = __( 'Edit Account Details', 'vibe' );
					break;
					default :
						$title = __( 'My Orders', 'vibe' );
					break;
				}
				$function = str_replace('-','_',$key);
				
				bp_core_new_subnav_item( array(
					'name'            => $title,
					'slug'            => $key,
					'parent_slug'     => $slug,
					'parent_url'      => $link,
					'position'        => $i,
					'item_css_id'     => 'nav-' . $key,
					'screen_function' => array( $this, $function ),
					'user_has_access' => bp_is_my_profile(),
					'no_access_url'   => home_url(),
				) );
				$i = $i+10;
			}
		}
	}
	function woo_myaccount() {

		if(!is_user_logged_in() || !function_exists('bp_is_my_profile') || !bp_is_my_profile())
			wp_redirect(home_url());

		$this->myaccount_pid = get_option('woocommerce_myaccount_page_id');
		add_action('bp_template_title',array($this,'woo_myaccount_title'));
		add_action('bp_template_content',array($this,'woo_myaccount_content'));
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );		
		exit;
	}
	
	function edit_account(){
		if(!is_user_logged_in() || !function_exists('bp_is_my_profile') || !bp_is_my_profile())
			wp_redirect(home_url());

		add_query_arg($bp->current_action);
		
		if(empty($this->myaccount_pid))
			$this->myaccount_pid = get_option('woocommerce_myaccount_page_id');


		add_action('bp_template_title',array($this,'woo_myaccount_edit_title'));
		add_action('bp_template_content',array($this,'woo_myaccount_edit_content'));
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
		exit;
	}

	function woo_myaccount_title(){
		echo '<h2>'.get_the_title($this->myaccount_pid).'</h2>';
	}

	function woo_myaccount_edit_title(){
		echo '<h2>'.__( 'Edit Account Details', 'vibe' ).'</h2>';
	}

	function woo_myaccount_content(){
		echo apply_filters('the_content',get_post_field('post_content',$this->myaccount_pid));
	}

	function woo_myaccount_edit_content(){
		ob_start();
		wc_get_template( 'myaccount/form-edit-account.php', array( 'user' => get_user_by( 'id', get_current_user_id() ) ) );
		$content = ob_get_clean();
		echo apply_filters('the_content',$content);
	}
	function woo_save_account_details(){
		if(isset($_POST)){
			if(class_exists('WC_Form_Handler'))
				WC_Form_Handler::save_account_details();
		}
	}

	function woo_myaccount_page(){
		$myaccount_pid = get_option('woocommerce_myaccount_page_id');
		if(is_numeric($myaccount_pid)){
			$slug = get_post_field('post_name',$myaccount_pid);
			$link = trailingslashit( bp_loggedin_user_domain() . $slug );
			wp_redirect($link);
			exit();
		}
	}

	/* === PMPRO ===== */
	function pmpro_setup_nav(){
		global $bp;
		if(empty($this->pmpro_account_pid))
			$this->pmpro_account_pid = get_option('pmpro_account_page_id');

		if(is_numeric($this->pmpro_account_pid)){
			$slug = get_post_field('post_name',$this->pmpro_account_pid);
			bp_core_new_nav_item( array( 
	            'name' => __('My Memberships', 'vibe' ), 
	            'slug' => $slug , 
	            'position' => 99,
	            'screen_function' => array($this,'pmpro_myaccount'), 
	            'default_subnav_slug' => '',
	            'show_for_displayed_user' => bp_is_my_profile(),
	            'default_subnav_slug'=> $slug
	      	) );


			$link = trailingslashit( bp_loggedin_user_domain() . $slug );

			bp_core_new_subnav_item( array(
				'name'            => __('My Memberships', 'vibe' ), 
				'slug'            => $slug,
				'parent_slug'     => $slug,
				'parent_url'      => $link,
				'position'        => 10,
				'item_css_id'     => 'nav-' . $slug,
				'screen_function' => array( $this, 'pmpro_myaccount' ),
				'user_has_access' => bp_is_my_profile(),
				'no_access_url'   => home_url(),
			) );
		}
	}
	function pmpro_myaccount() {

		if(!is_user_logged_in() || !function_exists('bp_is_my_profile') || !bp_is_my_profile())
			wp_redirect(home_url());
		
		if(empty($this->pmpro_account_pid))
			$this->pmpro_account_pid = get_option('pmpro_account_page_id');

		add_action('bp_template_title',array($this,'pmpro_myaccount_title'));
		add_action('bp_template_content',array($this,'pmpro_myaccount_content'));
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );		
		exit;
	}

	function pmpro_myaccount_title(){
		echo '<h2>'.get_the_title($this->pmpro_account_pid).'</h2>';
	}

	function pmpro_myaccount_content(){
		echo apply_filters('the_content',get_post_field('post_content',$this->pmpro_account_pid));
	}



    /*
    *	Add Course Category Featured thubmanils
    *	Use WP 4.4 Term meta for storing information
    * 	@reference : WooCommerce (GPLv2)
    */
    function add_category_fields(){
    	
    	$default = vibe_get_option('default_avatar');

    	?>
    	<div class="form-field">
    	<label><?php _e( 'Display Order', 'vibe' ); ?></label>
    	<input type="number" name="course_cat_order" id="course_cat_order" value="" />
    	</div>
    	<div class="form-field">
			<label><?php _e( 'Thumbnail', 'vibe' ); ?></label>
			<div id="course_cat_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( $default ); ?>" width="60px" height="60px" /></div>
			<div style="line-height: 60px;">
				<input type="hidden" id="course_cat_thumbnail_id" name="course_cat_thumbnail_id" />
				<button type="button" class="upload_image_button button"><?php _e( 'Upload/Add image', 'vibe' ); ?></button>
				<button type="button" class="remove_image_button button"><?php _e( 'Remove image', 'vibe' ); ?></button>
			</div>
			<script type="text/javascript">
				if ( ! jQuery( '#course_cat_thumbnail_id' ).val() ) {
					jQuery( '.remove_image_button' ).hide();
				}
				// Uploading files
				var file_frame;

				jQuery( document ).on( 'click', '.upload_image_button', function( event ) {
					event.preventDefault();
					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.downloadable_file = wp.media({
						title: '<?php _e( "Choose an image", "vibe" ); ?>',
						button: {
							text: '<?php _e( "Use image", "vibe" ); ?>'
						},
						multiple: false
					});
					file_frame.on( 'select', function() {
						var attachment = file_frame.state().get( 'selection' ).first().toJSON();
						jQuery( '#course_cat_thumbnail_id' ).val( attachment.id );
						if( attachment.sizes){
						    if(   attachment.sizes.thumbnail !== undefined  ) url_image=attachment.sizes.thumbnail.url; 
						    else if( attachment.sizes.medium !== undefined ) url_image=attachment.sizes.medium.url;
						    else url_image=attachment.sizes.full.url;
						}

						jQuery( '#course_cat_thumbnail' ).find( 'img' ).attr( 'src', url_image );
						
						jQuery( '.remove_image_button' ).show();
					});
					file_frame.open();
				});

				jQuery( document ).on( 'click', '.remove_image_button', function() {
					jQuery( '#course_cat_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( $default ); ?>' );
					jQuery( '#course_cat_thumbnail_id' ).val( '' );
					jQuery( '.remove_image_button' ).hide();
					return false;
				});

			</script>
			<div class="clear"></div>
		</div>
		<?php
    }
    /*
    *	Edit Course Category Featured thubmanils
    *	Use WP 4.4 Term meta for storing information
    * 	@reference : WooCommerce (GPLv2)
    */
    function edit_category_fields($term){


    	$thumbnail_id = absint( get_term_meta( $term->term_id, 'course_cat_thumbnail_id', true ) );
    	$order = get_term_meta( $term->term_id, 'course_cat_order', true ); 
		if ( $thumbnail_id ) {
			$image = wp_get_attachment_thumb_url( $thumbnail_id );
		} else {
			$default = vibe_get_option('default_avatar');
			$image = $default;
		}

    	?>
    	<tr class="form-field">
    		<th scope="row" valign="top"><label><?php _e( 'Display Order', 'vibe' ); ?></label></th>
			<td><input type="number" name="course_cat_order" id="course_cat_order" value="<?php echo (empty($order)?0:$order); ?>" /></td>
    	</tr>
    	<tr class="form-field">
			<th scope="row" valign="top"><label><?php _e( 'Thumbnail', 'vibe' ); ?></label></th>
			<td>
				<div id="course_cat_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url( $image ); ?>" width="60px" height="60px" /></div>
				<div style="line-height: 60px;">
					<input type="hidden" id="course_cat_thumbnail_id" name="course_cat_thumbnail_id" value="<?php echo $thumbnail_id; ?>" />
					<button type="button" class="upload_image_button button"><?php _e( 'Upload/Add image', 'vibe' ); ?></button>
					<button type="button" class="remove_image_button button"><?php _e( 'Remove image', 'vibe' ); ?></button>
				</div>
				<script type="text/javascript">

					// Only show the "remove image" button when needed
					if ( '0' === jQuery( '#course_cat_thumbnail_id' ).val() ) {
						jQuery( '.remove_image_button' ).hide();
					}

					// Uploading files
					var file_frame;

					jQuery( document ).on( 'click', '.upload_image_button', function( event ) {

						event.preventDefault();

						// If the media frame already exists, reopen it.
						if ( file_frame ) {
							file_frame.open();
							return;
						}

						// Create the media frame.
						file_frame = wp.media.frames.downloadable_file = wp.media({
							title: '<?php _e( "Choose an image", "vibe" ); ?>',
							button: {
								text: '<?php _e( "Use image", "vibe" ); ?>'
							},
							multiple: false
						});

						// When an image is selected, run a callback.
						file_frame.on( 'select', function() {
							var attachment = file_frame.state().get( 'selection' ).first().toJSON();

							jQuery( '#course_cat_thumbnail_id' ).val( attachment.id );

							if( attachment.sizes){
							    if(   attachment.sizes.thumbnail !== undefined  ) url_image=attachment.sizes.thumbnail.url; 
							    else if( attachment.sizes.medium !== undefined ) url_image=attachment.sizes.medium.url;
							    else url_image=attachment.sizes.full.url;
							}

							jQuery( '#course_cat_thumbnail' ).find( 'img' ).attr( 'src', url_image );
							jQuery( '.remove_image_button' ).show();
						});

						// Finally, open the modal.
						file_frame.open();
					});

					jQuery( document ).on( 'click', '.remove_image_button', function() {
						jQuery( '#course_cat_thumbnail' ).find( 'img' ).attr( 'src', '<?php echo esc_js( $image ); ?>' );
						jQuery( '#course_cat_thumbnail_id' ).val( '' );
						jQuery( '.remove_image_button' ).hide();
						return false;
					});

				</script>
				<div class="clear"></div>
			</td>
		</tr>
		<?php
    }


	function save_category_meta( $term_id, $tt_id ){
		global $wpdb;
	    if( isset( $_POST['course_cat_thumbnail_id'] )){
	        $thumb_id = intval( $_POST['course_cat_thumbnail_id'] );
	        update_term_meta( $term_id, 'course_cat_thumbnail_id', $thumb_id );
	    }
	    if( isset( $_POST['course_cat_order'] ) &&is_numeric($_POST['course_cat_order'])){
	        update_term_meta( $term_id, 'course_cat_order', $_POST['course_cat_order'] );
	        $wpdb->update($wpdb->terms, array('term_group' => $_POST['course_cat_order']), array('term_id'=>$term_id));
	    }
	}

	/*
	RESTRICTI DIRECTORY & PROFILE ACCESS
	*/

	function wplms_before_members_directory(){

	  $flag=1;
	  $members_view=vibe_get_option('members_view');

	  if(isset($members_view) && $members_view){
	    $flag=0;
	    switch($members_view){
	      case 1:
	        if(is_user_logged_in())$flag=1;
	      break;
	      case 2:
	        if(current_user_can('edit_posts'))$flag=1;
	      break;
	      case 3:
	        if(current_user_can('manage_options'))$flag=1;
	      break;
	    }
	  }

	  if(!$flag){
	    $id=vibe_get_option('members_redirect');
	    if(isset($id))
	      wp_redirect(get_permalink($id));
	  	else
	  		wp_redirect(home_url());
	    exit();
	  }
	}

	function wplms_before_activity_directory(){
		$flag=1;
		$activity_view=vibe_get_option('activity_view');

	  	if(isset($activity_view) && $activity_view){
		    $flag=0;
		    switch($activity_view){
		      case 1:
		        if(is_user_logged_in())$flag=1;
		      break;
		      case 2:
		        if(current_user_can('edit_posts'))$flag=1;
		      break;
		      case 3:
		        if(current_user_can('manage_options'))$flag=1;
		      break;
		    }
	  	}

	  	if(!$flag){
		    $id=vibe_get_option('activity_redirect');
		    if(isset($id)){
		      wp_redirect(get_permalink($id));
		    }else{
		    	wp_redirect(home_url());
		    }
		    exit();
	  	}
	}

	function wplms_before_groups_directory(){
		$flag=1;
		$group_view=vibe_get_option('group_view');

	  	if(isset($group_view) && $group_view){
		    $flag=0;
		    switch($group_view){
		      case 1:
		        if(is_user_logged_in())$flag=1;
		      break;
		      case 2:
		        if(current_user_can('edit_posts'))$flag=1;
		      break;
		      case 3:
		        if(current_user_can('manage_options'))$flag=1;
		      break;
		    }
	  	}

	  	if(!$flag){
		    $id=vibe_get_option('group_redirect');
		    if(isset($id)){
		      wp_redirect(get_permalink($id));
		    }else{
		    	wp_redirect(home_url());
		    }
		    exit();
	  	}
	}

	function wplms_before_member_profile(){

	  $flag=1;
	  $members_view=vibe_get_option('single_member_view');

	  if(isset($members_view) && $members_view){
	    $flag=0;
	    switch($members_view){
	      case 1:
	        if(is_user_logged_in())$flag=1;
	      break;
	      case 2:
	        if(current_user_can('edit_posts'))$flag=1;
	      break;
	      case 3:
	        if(current_user_can('manage_options'))$flag=1;
	      break;
	    }
	  }

	  if(!$flag && !bp_is_my_profile()){
	    $id=vibe_get_option('members_redirect');
	    if(isset($id))
	      wp_redirect(get_permalink($id));
	    exit();
	  }
	}

	/*
	Related Courses
	 */
	function show_related(){
		
		$related_courses = vibe_get_option('related_courses');
		if(empty($related_courses))
			return;
		$style = vibe_get_option('default_course_block_style');
		$terms = wp_get_post_terms(get_the_ID(),'course-cat');
		$categories = array();
		if(!empty($terms)){
			foreach($terms as $term)
			$categories[] = $term->term_id;
		}
		$args = apply_filters('vibe_related_courses',array(
			'post_type' => 'course',
			'posts_per_page'=>3,
			'post__not_in'=>array(get_the_ID()),
			'tax_query' => array(
					'relation' => 'OR',
					array(
						'taxonomy' => 'course-cat',
						'field'    => 'id',
						'terms'    => $categories,
					),
			),
			));
		$courses = new WP_Query($args);
		
		if($courses->have_posts()):
		?>
		<div class="related_courses">
		<h3 class="heading"><span><?php _e('Related Courses','vibe');?></span></h3>
		<?php
			
			?>
			<ul class="row">
			<?php	
			while($courses->have_posts()): $courses->the_post();
			global $post;
			echo '<li class="col-md-4">';

			if(empty($style))
				$style = 'course4';

			echo thumbnail_generator($post,$style,'medium');
			echo '</li>';
			endwhile;
			?>
			</ul>
		</div>
		<?php
			endif;
			wp_reset_postdata();
	}

	function get_course_unfinished_unit($course_id){
		
		if(!is_user_logged_in())
	    	return;

	  	$user_id = get_current_user_id();  

	  	if(isset($_COOKIE['course'])){
	      	$coursetaken=1;
	  	}else{
	      	$coursetaken=get_user_meta($user_id,$course_id,true);      
	  	}
	  	

	  	$course_curriculum = array();
	  	if(function_exists('bp_course_get_curriculum_units'))
	    	$course_curriculum=bp_course_get_curriculum_units($course_id);	

	  	$uid='';
	  	$key = $pre_unit_key = 0;
	  	if(isset($coursetaken) && $coursetaken){
	      	if(isset($course_curriculum) && is_array($course_curriculum) && count($course_curriculum)){
	        
	        	foreach($course_curriculum as $key => $uid){
	            	$unit_id = $uid; // Only number UIDS are unit_id
	            	//Check if User has taken the Unit
	            	if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
	                	$unittaken=bp_course_get_user_unit_completion_time($user_id,$uid,$course_id);//
	            	}else{
	                	$unittaken=bp_course_get_user_unit_completion_time($user_id,$uid);//
	            	}
					
	            	if(!isset($unittaken) || !$unittaken){
	              		break; // If not taken, we've found the last unfinished unit.
	            	}
	        	}

	      	}else{
	          	echo '<div class="error"><p>'.__('Course Curriculum Not Set','vibe').'</p></div>';
	          	return;
	      	}    
	  	}
	  	
	  	$units = $course_curriculum;
	  	$unit_id = apply_filters('wplms_get_course_unfinished_unit',$unit_id,$course_id);
	  	$key = apply_filters('wplms_get_course_unfinished_unit_key',$key,$unit_id,$course_id);
	  	$unitkey = $key; // USE FOR BACKUP


	  	$flag = apply_filters('wplms_skip_course_status_page',false,$course_id);
	  	if($flag && (isset($_POST['start_course']) || isset($_POST['continue_course'])) && $unitkey == 0){
	  		return $unit_id;
	  	}

	  	/*=======
	  	* NON_AJAX COURSE USECASE
	  	* PROVIDE ACCESS IF CURRENT UNIT IS COMPLETE.
	  	=======*/
	    if(function_exists('bp_course_check_unit_complete')){ 
	        if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
	            $x = bp_course_check_unit_complete($unit_id,$user_id,$course_id);            
	        }else{
	            $x = bp_course_check_unit_complete($unit_id,$user_id);
	        }
	    
	        if($x)
	           return $unit_id;
	    } //end function exists check
	    


	  	$flag=apply_filters('wplms_next_unit_access',true,$units[$pre_unit_key]);
	  	$drip_enable= apply_filters('wplms_course_drip_switch',get_post_meta($course_id,'vibe_course_drip',true),$course_id);


	  	if(vibe_validate($drip_enable)){


	  		// BY PASS 
	  		// DRIP FOR FIRST UNIT
	  		if($key == 0){ 
	  		//SET DRIP ACCESS TIME FOR FIRST UNIT
		  		if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
	            	$x=bp_course_get_drip_access_time($units[$key],$user_id,$course_id);
	        	}else{
	            	$x=bp_course_get_drip_access_time($units[$key],$user_id);
	        	}
	        	// SET DRIP TIME IF NOT EXISTS
	        	if(empty($x)){	
			  		if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
		            	bp_course_update_unit_user_access_time($units[$key],$user_id,time(),$course_id);
		        	}else{
		            	bp_course_update_unit_user_access_time($units[$key],$user_id,time());
		        	}	
		        }

		  		return $unit_id;
		  	}

	  		/*=======
		  	* NON_AJAX COURSE USECASE &  RANDOM UNIT ACCESS
		  	* GET CURRENT & PREVIOUS UNIT KEY
		  	=======*/
		    for($i=($key-1);$i>=0;$i--){
		    	if(function_exists('bp_course_check_unit_complete')){

		        	//CHECK IF PRE_UNIT MARKED COMPLETE
		        	//IF YES THEN RECALCULATE CURRENT UNIT AND PREV_UNIT
		            if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
		                $x = bp_course_check_unit_complete($units[$i],$user_id,$course_id);
		            }else{
		                $x = bp_course_check_unit_complete($units[$i],$user_id);
		            }
		            // ABOVE IS REQUIRED BECAUSE INSTRUCTOR CAN 
		            // MARK THE UNIT COMPLETE FROM THE BACKEND
		            if(!empty($x)){
		                $pre_unit_key = $i;
		                // IF PREVIOUS UNIT IS COMPLETE
		                // CHECK IF DRIP TIME EXISTS
		                if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
			            	$x=bp_course_get_drip_access_time($units[$i],$user_id,$course_id);
			        	}else{
			            	$x=bp_course_get_drip_access_time($units[$i],$user_id);
			        	}
			        	// SET DRIP TIME IF NOT EXISTS
			        	if(empty($x)){	
			        		if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
				            	bp_course_update_unit_user_access_time($units[$pre_unit_key],$user_id,time(),$course_id);
				        	}else{
				            	bp_course_update_unit_user_access_time($units[$pre_unit_key],$user_id,time());
				        	}	
			        	}
		                
		                
		                $unitkey = $pre_unit_key+1;
		                break;
		            }else{
		            	//IF NOT MARKED COMPELTE, 
		            	//CHECK IF PRE-UNIT DRIP ACCESS TIME EXISTS
		            	if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
			            	$x=bp_course_get_drip_access_time($units[$i],$user_id,$course_id);
			        	}else{
			            	$x=bp_course_get_drip_access_time($units[$i],$user_id);
			        	}

			        	if(!empty($x) && ($x < time())){ // NOT SET AS FUTURE FOR DRIP ORIGIN
			                $pre_unit_key = $i; // UNIT ACCESSED BUT NOT MARKED COMPLETE
			                $unitkey = $pre_unit_key+1;
			                break;
			            }
		            }
		        }
		    }//end for
			
			//Set the NEW KEY 
			if(!empty($unitkey)){
				$key = $unitkey;	
				$unit_id = $units[$key];
			}
			
			if(empty($pre_unit_key)){
				$pre_unit_key = 0;
			}
	
	      	$drip_duration_parameter = apply_filters('vibe_drip_duration_parameter',86400,$course_id);
	      	$drip_duration = get_post_meta($course_id,'vibe_course_drip_duration',true);
	      
	      	$total_drip_duration = apply_filters('vibe_total_drip_duration',($drip_duration*$drip_duration_parameter),$course_id,$unit_id,$units[$pre_unit_key]);

	      	$this->element = apply_filters('wplms_drip_feed_element_in_message',__('Unit','vibe'),$course_id);

	      	if($key > 0){

	        	if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
	            	$pre_unit_time=bp_course_get_drip_access_time($units[$pre_unit_key],$user_id,$course_id);
	        	}else{
	            	$pre_unit_time=bp_course_get_drip_access_time($units[$pre_unit_key],$user_id);
	        	}
	        	
	        	if(!empty($pre_unit_time)){
	          
	            	$value = $pre_unit_time + $total_drip_duration;
	            
	            	$value = apply_filters('wplms_drip_value',$value,$units[$pre_unit_key],$course_id,$units[$key],$units);
	            	
	            	if($value > time()){
	                	$flag=0;
	                	$this->value = $value;
	                	add_action('wplms_before_start_course_content',function(){
	                    	$remaining = tofriendlytime($this->value - time());
	                    	echo '<div class="container top30"><div class="row"><div class="col-md-9"><div class="message"><p>'.sprintf(__('Next %s will be available in %s','vibe'),$this->element,$remaining).'</p></div></div></div></div>';
	                	});
	              		return $units[$pre_unit_key];
	            	}else{

	                	if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
	                    	$cur_unit_time=bp_course_get_drip_access_time($units[$key],$user_id,$course_id);
	                	}else{
	                    	$cur_unit_time=bp_course_get_drip_access_time($units[$key],$user_id);
	                	}

	                	
	                	if(!isset($cur_unit_time) || $cur_unit_time ==''){

	                    	if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
	                        	bp_course_update_unit_user_access_time($units[$key],$user_id,time(),$course_id);
	                    	}else{
	                        	bp_course_update_unit_user_access_time($units[$key],$user_id,time());      
	                    	}

	                    	//Parmas : Next Unit, Next timestamp, course_id, userid
	                    	do_action('wplms_start_unit',$units[$key],$course_id,$user_id,$units[$key+1],(time()+$total_drip_duration));
	                	}
	                	
	                	return $units[$pre_unit_key];
	                	
	            	} 
	        	}else{

	            	if(isset($pre_unit_key )){

	                	if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
	                    	$completed = bp_course_get_user_unit_completion_time($user_id,$units[$pre_unit_key],$course_id);
	                	}else{
	                    	$completed = get_user_meta($user_id,$units[$pre_unit_key],true);
	                	}
	                
	                
	                	if(!empty($completed)){
	                    	if(defined('BP_COURSE_MOD_VERSION') && version_compare(BP_COURSE_MOD_VERSION,'2.3') >= 0){
	                        	bp_course_update_unit_user_access_time($units[$pre_unit_key],$user_id,time(),$course_id);  
	                    	}else{
	                        	bp_course_update_unit_user_access_time($units[$pre_unit_key],$user_id,time());  
	                    	}
	                    
	                    	$pre_unit_time = time();
	                    	$value = $pre_unit_time + $total_drip_duration;
	                    	$value = apply_filters('wplms_drip_value',$value,$units[$pre_unit_key],$course_id,$units[$key],$units);
	                    	
	                    	$this->value = $value-$pre_unit_time;
	                    	add_action('wplms_before_start_course_content',function(){
	                        	echo '<div class="container top30"><div class="row"><div class="col-md-9"><div class="message"><p>'.sprintf(__('Next %s will be available in %s','vibe'),$this->element,tofriendlytime($this->value)).'</p></div></div></div></div>';
	                    	});
	                   
	                    	return $units[$pre_unit_key];
	                	}else{
	                   		add_action('wplms_before_start_course_content',function(){
	                        	echo '<div class="container top30"><div class="row"><div class="col-md-9"><div class="message"><p>'.sprintf(__('Requested %s can not be accessed.','vibe'),$this->element).'</p></div></div></div></div>';
	                    	});
	                  
	                  		return $units[$pre_unit_key];
	                	}
	            	}else{
	            		add_action('wplms_before_start_course_content',function(){  
	                        echo '<div class="container top30"><div class="row"><div class="col-md-9"><div class="message"><p>'.sprintf(__('Requested %s can not be accessed.','vibe'),$this->element).'</p></div></div></div></div>';
	                    });
	                 
	                    return $units[$pre_unit_key];
	            	}
	            	die();
	        	} //Empty pre-unit time

	    	}
	    }  // End Drip Enable check

  
	  	if(isset($unit_id) && $flag && isset($key)){// Should Always be set 
		    if($key == 0){
		      	$unit_id =''; //Show course start if first unit has not been started
		    }else{
		      	$unit_id=$unit_id; // Last un finished unit
		    }
	  	}else{
		    if(isset($key) && $key > 0){ 
		       $unit_id=$units[($key-1)];
		    }else{
		      	$unit_id = '' ;
		    }
	  	} 
		return $unit_id;
	}

	function show_bp_error(){
		global $bp;
    	if(!empty($bp->template_message)){
        	echo '<div class="message '.$bp->template_message_type.'">'.$bp->template_message.'</div>';
    	}
	}

	/*
	DETECT COURSE CATEGORY / LEVEL / LOCATION REDIRECT 
	 */
	function detect_cat_level_location(){
		
		if(is_tax(array('course-cat','level','location'))){
			$tax = get_query_var( 'taxonomy' );
			$term = get_query_var( 'term' );
			echo '<input type="hidden" class="current-course-cat" data-cat="'.$tax.'" data-slug="'.$term.'" value="'.$term.'"/>';
		}
		
	}

	/*
	ENABLE AJAX BASED SIGNON
	 */

	function wplms_get_signon_security(){
		echo wp_create_nonce('wplms_signon');
		die();
	}
	function wplms_signon(){

		$response = array();
		if(empty($_POST['data']) || !isset($_POST['security'])){
			$response['error'] = _x('Missing data.','ajax signon message','vibe');
			$response['target'] = 'input[type="password"]';
			echo json_encode($response);
			die();
		}

		if(!isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_signon')){
			$response['error'] = _x('Missing security.','ajax signon message','vibe');
			echo json_encode($response);
			die();
		}

		$data = json_decode(stripslashes($_POST['data']));
		

		if(!username_exists($data->user)){
			if(!email_exists($data->user)){
				$response['error'] = _x('Invalid username/email.','ajax signon message','vibe');
				$response['target'] = 'input[type="text"]';
				echo json_encode($response);
				die();	
			}else{
				$user = get_user_by( 'email', $data->user );
			}
		}else{
			$user = get_user_by( 'login', $data->user );
		}

		
		$flag = apply_filters( 'authenticate',$user->data, $data->user, $data->pass);

		if(is_wp_error($flag)){
			$response['error'] = '';
			if(is_array($flag->errors)){
				foreach($flag->errors as $errors){
					if(is_array($errors)){
						foreach($errors as $error ){
							$response['error'] .= $error;
						}
					}else{
						$response['error'] .= $errors;
					}
				}
			}else{
				$response['error'] .= $flag->errors;
			}
			
			$response['target'] = 'input[type="password"]';
			echo json_encode($response);
			die();
		}
		if ( $user && wp_check_password( $data->pass, $user->data->user_pass, $user->data->ID)) {
			wp_set_current_user( $user->data->ID, $user->data->user_login );

			$remember = 0;
			if(isset($data->remember)){$remember = $data->remember;}

			wp_set_auth_cookie( $user->data->ID,$remember );
			
			$redirect_link = apply_filters('login_redirect','','',$user);

			$response['success']=$redirect_link;
		}else{
			$response['error'] = _x('Invalid password.','ajax signon message','vibe');
			$response['target'] = 'input[type="password"]';
		}

		
		echo json_encode($response);
		die();
	}
	/*
	Enable Ajax registration and Login
	 */
	
	function enable_ajax_registration_login(){

		$enable_ajax_registration_login = vibe_get_option('enable_ajax_registration_login');
		if(empty($enable_ajax_registration_login))
			return;
		if(is_page_template('login-page.php')){
			return;
		}
		if($enable_ajax_registration_login == 2){

			
			$forms = get_option('wplms_registration_forms');	
			
			if(!empty($forms)){
				$count = 0;

				foreach($forms as $name=>$form){
					if(isset($form['default'])){
						break;
					}else{
						$count++;
					}
				}	

				if($count < count($forms) && isset($name)){ // we have a default form
					?>
					<div id="wplms_custom_registration_form">
						<a class="back_to_login small">&lsaquo; <?php _ex('back to login','back to login in login panel','vibe'); ?></a>
						<?php
							echo do_shortcode('[wplms_registration_form name="'.$name.'"]');
						?>
					</div>
					<?php
				}
			}
		}
		?>
		<div id="wplms_forgot_password_form">
			<a class="back_to_login small">&lsaquo; <?php _ex('back to login','back to login in login panel','vibe'); ?></a>
			<form method="post">
				<input type="email" placeholder="<?php _ex('Enter registered email id','fotgot password email placeholder','vibe'); ?>" class="form_field" value="" />
				<button id="vbp_forgot_password" class="button" data-security="<?php echo wp_create_nonce('wplms_forgot_password'); ?>"><?php _ex('Get reset password link','forgot password form submit label','vibe'); ?></button>
			</form>
		</div>
		<?php
	}


	function wplms_forgot_password(){

		if(empty($_POST['email']) || !isset($_POST['security'])){
			_ex('Missing email.','forgot password email message','vibe');
			die();
		}

		if(!isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_forgot_password')){
			_ex('Missing security.','forgot password email message','vibe');
			die();
		}

		if(!email_exists($_POST['email'])){
			_ex('No registered user found with this mail id !','forgot password email message','vibe');
			die();
		}

		$user_data = get_user_by( 'email', trim( $_POST['email'] ) );
		// Redefining user_login ensures we return the right case in the email.
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;

		//Important WordPress hooks
		do_action( 'retreive_password', $user_login );

		do_action( 'retrieve_password', $user_login );
		
		$allow = apply_filters( 'allow_password_reset', true, $user_data->ID );
		if ( ! $allow ) {
			_e('Password reset is not allowed for this user','vibe');
			die();
		} elseif ( is_wp_error( $allow ) ) {
			_e('Password reset is not allowed.','vibe');
			die();
		}

		// Generate something random for a password reset key.
		$key = wp_generate_password( 20, false );

		do_action( 'retrieve_password_key', $user_login, $key );

		// Now insert the key, hashed, into the DB.
		if ( empty( $wp_hasher ) ) {
			require_once ABSPATH . WPINC . '/class-phpass.php';
			$wp_hasher = new PasswordHash( 8, true );
		}

		global $wpdb;
		$hashed = time() . ':' . $wp_hasher->HashPassword( $key );
		$wpdb->update( $wpdb->users, array( 'user_activation_key' => $hashed ), array( 'user_login' => $user_login ) );

		
		$email_type = $args['action'];
        $bpargs = array(
            'tokens' => array(
            	'user.username'=>$user_login,
            	'user.forgotpasswordlink'=> '<a href="'.network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login').'">'.network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login').'</a>',
            	),
        );
        

		if ( !bp_send_email( 'wplms_forgot_password',$user_email, $bpargs )){
			wp_die( __('The e-mail could not be sent.','vibe') . "<br />\n" . __('Possible reason: your host may have disabled the mail() function.','vibe') );
		}else{
			_ex('Please check your email for password recovery !','forgot password mail message','vibe');
		}

		die();
	}

	function search(){
		?>
        <div id="searchdiv">
            <form role="search" method="get" id="searchform" action="<?php echo home_url( '/' ); ?>">
                <input type="text" value="<?php the_search_query(); ?>" name="s" id="s" placeholder="<?php _e('Hit enter to search...','vibe'); ?>" />
                <?php 
                    $course_search=vibe_get_option('course_search');
                    if(isset($course_search) && $course_search)
                        echo '<input type="hidden" value="course" name="post_type" />';
                ?>
            </form>
            <span></span>
        </div>
		<?php
	}

	/*
	Tab Scrolling effect in Courses - Controlled by Options panel - Coruse manager
	 */
	
	function wplms_course_tabs_supports(){
		global $post;
    	$layouts = array('c5','c4','c3','c2','c6');
    	$layout = vibe_get_customizer('course_layout');
    	$tab_style_course_layout = vibe_get_option('tab_style_course_layout');
    	
    	if(!empty($layout) && in_array($layout,$layouts) && !empty($tab_style_course_layout)){

    		$this->wplms_course_tabs_tabs_array = apply_filters('course_tabs_array',array(
    			'home' => _x('home','custom tabs for tabbed layout','vibe'),
    			'curriculum' => _x('curriculum','custom tabs for tabbed layout','vibe')
    		));

			if($post->comment_status == 'open'){
				if(!empty($this->wplms_course_tabs_tabs_array) && is_array($this->wplms_course_tabs_tabs_array))
					$this->wplms_course_tabs_tabs_array['reviews'] = _x('reviews','custom tabs for tabbed layout','vibe');
			}

			add_filter('wplms_course_nav_menu',array($this,'wplms_course_tabs_link'),999);
			add_filter('vibe_course_permalinks',array($this,'add_wplms_course_tabs_in_saved_permalinks'));

			
			if(class_exists('WPLMS_tips')){
				$tips  = WPLMS_tips::init();
				remove_filter('wplms_course_nav_menu',array($tips,'coursenav_remove_curriculum'));
				remove_action('wplms_after_course_description',array($tips,'course_curriculum_below_description'));
			}

			add_action('wplms_after_course_description',array($this,'course_curriculum_below_description_wplms_course_tabs'));

			//style and scripts for wplms_course_tabs
			add_action('wp_footer',array($this,'wplms_wplms_course_tabs_stick_at_bottom'));
			
		}
    }

	function course_curriculum_below_description_wplms_course_tabs(){
		$class='';
		if(class_exists('Wplms_tips')){

			$tips = Wplms_tips::init();
			if(isset($tips->settings['curriculum_accordion']))
			$class="accordion";
		}
		
		?>
			<div id="course-curriculum">
				<div class="course_curriculum <?php echo $class; ?>">
					<?php
						$file = get_stylesheet_directory() . '/course/single/curriculum.php';
						if(!file_exists($file)){
							$file = VIBE_PATH.'/course/single/curriculum.php';
						}
						include $file;
					?>
				</div>
			</div>
		<?php
    }

    function add_wplms_course_tabs_in_saved_permalinks($permalinks){
    	
    	foreach ($this->wplms_course_tabs_tabs_array as $key => $tab) {
    		if(empty($permalinks[$key.'_slug']))
    		$permalinks[$key.'_slug'] = $key;
    	}
    	return $permalinks;
    }

    function wplms_course_tabs_link($nav){
		global $post;
		$tabs = $this->wplms_course_tabs_tabs_array;
		$temp = $nav;
		if(!empty($temp['curriculum']))
		unset($temp['curriculum']);
		unset($nav);
		foreach($tabs as $key => $tab){
			if(function_exists('bp_get_course_permalink')){
				unset($temp[$key]);
				$nav[$key] = array(
	                'id' => $key,
	                'label'=>$tab,
	                'action' => '#course-'.strtolower($key),
	                'link'=>bp_get_course_permalink(),
	            	);
			}
				
		}
		
		foreach ($temp as $key => $value) {
			if($key != '')
			$nav[$key] = $value;
		}
		return $nav;
	}

    function wplms_wplms_course_tabs_stick_at_bottom(){
    	if(function_exists('bp_current_action')){
    		$action = bp_current_action();
    	}
    	if(empty($action) && !empty($_GET['action'])){
    		$action = $_GET['action'];
    	}
    	global $post;

    	if(!empty($action) && $action != $post->post_name)
    		return;

    	?>
    		<style>
    		.single-course div#item-nav ul li.flexMenu-viewMore ul.flexMenu-popup{
			    overflow-y: auto;
			    z-index: 999999;
			}
			.single-course div#item-nav.fixed { z-index: 999999 !important;}
    		.single-course div#item-nav {
			    position: relative;
			}
    		.single-course div#item-nav.fixed ul li.flexMenu-viewMore ul.flexMenu-popup{
			    top:auto !important;
			    
			}
			.single-course div#item-nav:not(.fixed) ul li.flexMenu-viewMore ul.flexMenu-popup{
				bottom:auto !important;
			}
		  	.single-course div#item-nav.fixed {
		  		position:fixed;
			    bottom:0;
			    width:100%;
			    z-index:999;
			}
			body.single-course.c4 #item-nav.fixed {max-width: 100%;}
			ul.flexMenu-popup {box-shadow:0 0 5px rgba(0,0,0,0.2)}
			@media(max-width:640px){
				body.single-course #object-nav{width: calc(100% - 60px);}	
				body.single-course.c4 #item-nav.fixed {
				    width: calc(100% - 30px);
				    padding: 0;
				}
			}
			#scroll_to_course_button {
				position: absolute; right: 5px; top: 5px; margin: 0;z-index:99;
			}
		  	</style>
		 
		  	<script>
			  	jQuery(window).load(function($){
			  		$ = jQuery;
			  		var review_course = <?php echo (!empty($_POST['review_course'])?1:0)?>;
			  		var topMenuHeight = $('header').outerHeight(true);
			  		var windowWidth = $(window).width();
			  		var windowHeight = $(window).height();
			  		var fixed_course_menu = function(){
			  			var selector = $(".single-course div#item-nav");
			  			//selector.find('ul').flexMenu();
			  			selector.each(function(){

				        	var $this = $(this);
				        	var height = $this.offset().top;
				     		
				     		
				     		$('#item-nav').css('width',$this.width());
				     		
				     		$('#scrolltop').css('bottom',66);
		  				  	if(typeof $('.single-course div#item-nav.fixed ul li.flexMenu-viewMore ul.flexMenu-popup') !== 'undefined'){
	  				  			var flexmenuheight = $this.outerHeight(true);
	  				  			$this.append('<style>.single-course div#item-nav.fixed{transform: translate3d(0,0,0);}.single-course div#item-nav.fixed ul li.flexMenu-viewMore ul.flexMenu-popup{bottom:'+flexmenuheight+'px;}.flexMenu-popup{max-height:calc(75vh - '+topMenuHeight+'px);}#footerbottom{padding-bottom:'+(20+flexmenuheight)+'px;}</style>');
		  				  	}

				     		

					        $(window).scroll(function(event){
					            var st = $(this).scrollTop();
					            if(st > height){
					              $this.addClass('fixed');
					            }else{
					              	$this.removeClass('fixed');
					            }
					        });

					        if( $this.find('#scroll_to_course_button').length <= 0 && $(window).width() < 640){
				              	$this.append('<a href="#course-pricing" id="scroll_to_course_button" class="button small"><i class="fa fa-shopping-basket"></i></a>');
				            }
				    	});
			  		}
				    
				    fixed_course_menu();
				   
				  	
				  	// scroll basket
			  		$( 'body' ).delegate( '#scroll_to_course_button', 'click', function(event) {
			  			event.preventDefault();
			  			var topMenuHeight = $('header').outerHeight(true);
			  			var href = $(this).attr("href");
					   	var type = href.split('#');
						var hash2 = '';
						if(type.length > 1){
						  hash2 = type[1];
						}
						if(!$(".single-course div#item-nav").hasClass('fixed')){
							topMenuHeight += $(".single-course div#item-nav").outerHeight(true);
						}
					    var offsetTop = hash2 === "#" ? 0 : $('#'+hash2).offset().top-topMenuHeight+1;
					   	$('html, body').stop().animate({ 
					       scrollTop: offsetTop
					   	}, 800);
			  		});
				  		
				  	

				  	var scroll_tosection = function(){

				  		var selector = $(".single-course div#item-nav");
						top = Math.floor(top);
						var lastId;
						var topMenu = $(".single-course div#item-nav ul,.single-course div#object-nav ul"); 
						var topMenuHeight = 0;
						var menuItems = topMenu.find("a");
						 // Anchors corresponding to menu items
						var scrollItems = $.each(menuItems,function(){
									 		var type =$(this).attr("href").split('#');
									 		var hash = '';
											if(type.length > 1)
											  hash = type[1];
									       	var item = $(hash);
									       if (item.length) { return item; }
									     });

						var topMenuHeight = $('header').outerHeight(true);

						menuItems.click(function(event){

							if($(this).parent().hasClass('flexMenu-viewMore'))
								return false;
							selector.find('ul.flexMenu-popup').css('display','none');
						   	var href = $(this).attr("href");
						   	var type = href.split('#');
							var hash2 = '';
							if(type.length > 1){
							 	hash2 = type[1];
							  	if(hash2 != '' && $('#'+hash2).length>0){
							  		event.preventDefault();
									var offsetTop = hash2 === "#" ? 0 : $('#'+hash2).offset().top-topMenuHeight+1;
								    if(!selector.hasClass('fixed')){
										offsetTop = offsetTop - selector.outerHeight(true);
									}
								   	$('html, body').stop().animate({ 
								       scrollTop: offsetTop
								   	}, 800);
								   	$('.single-course div#item-nav ul li,.single-course div#object-nav ul li').each(function(){
										$(this).removeClass('current active');
								   	});
								   	$(this).parent().addClass("active current");
								}else{
									var new_location = type[0];
									window.location.href= new_location;
								}
							}
						    
						});

				  	};
					scroll_tosection();

					 $(window).on('resize',function(){
				    	fixed_course_menu();
				    });
				});  	
		  	</script>
    	<?php
    }


    function check_contextmenu_course_status(){

    	$status = vibe_get_option('disable_contextmenu_course_status');
    	if(empty($status)){
    		return;	
    	}
		$course_status = vibe_get_option('take_course_page');
		if(is_page($course_status)){
			add_filter('body_class',function($class){
			    echo ' oncontextmenu="return false" ';
			    return $class;
			},9999);
		}	
	}

	/*
	Fix styles and colors of demo import
	*/
	function demo_import_fixes($customizer){

		$style = vibe_get_site_style();
		if($style == 'demo5'){
		    $primary_bg = vibe_get_customizer('primary_bg');
		    ?>
		    header.standard nav .sub-menu li a:hover, 
		    header.standard nav .sub-menu li:hover a, 
		    header.standard nav>.menu>li.current-menu-item>a, 
		    header.standard nav>.menu>li.current_page_item>a,
		    header.standard nav>.menu>li:hover>a:after{
		      background:<?php echo $primary_bg;?>
		    }
		    header #searchform:after, nav>.menu>li:hover>a:before{
		      border-color:transparent transparent <?php echo $primary_bg;?> transparent !important;
		    }
		    header.standard{
		    border-bottom-color:<?php echo $primary_bg;?> !important;
		    }
			<?php  
		}

	}
}

WPLMS_Actions::init();

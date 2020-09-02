<?php
/**
 * Filters
 *
 * @class       VibeBP_Init
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VibeBP_Actions{


	public static $instance;
	public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Actions();
        return self::$instance;
    }

	private function __construct(){
		add_action( 'bp_setup_nav', array($this,'add_bp_nav'), 100 );
		add_action('wp_ajax_vibebp-sw',array($this,'install_sw'));
		add_action('wp_ajax_nopriv_vibebp-sw',array($this,'install_sw'));
		add_action('wp_footer',array($this,'synchronise_wp_logins'));
		add_action('wp_head',array($this,'pre_cache'),999999);
		add_action('wp_head',array($this,'apply_sw_version'),1);
		add_action('wp_ajax_generate_token',array($this,'generate_token'));
		add_action('template_redirect',array($this,'accessibility_settings'),1);
		add_action('wp_head',array($this,'login_detect_at_woocommerce_checkout'));
		add_action('wp_ajax_nopriv_vibebp_wc_login',array($this,'vibebp_wc_login'));
	}

	function accessibility_settings(){
		if(function_exists('bp_is_user') && bp_is_user()){
			$disable = vibebp_get_setting('public_profile','bp');
			$client_id = vibebp_get_setting('client_id');
			if($disable == 'on'){

				if(!empty($_GET) && !empty($_GET['client_id']) && $_GET['client_id'] == $client_id){
					//continue
				}else{
					global $wp_query;
				  $wp_query->set_404();
				  status_header( 404 );
				  get_template_part( 404 ); exit();
				}
			}
		}
		
		if(function_exists('bp_current_component') && bp_current_component() == 'members'){
			$disable = vibebp_get_setting('public_member_directory','bp');
			$client_id = vibebp_get_setting('client_id');
			if($disable == 'on'){

				if(!empty($_GET) && !empty($_GET['client_id']) && $_GET['client_id'] == $client_id){
					//continue
				}else{
					global $wp_query;
				  $wp_query->set_404();
				  status_header( 404 );
				  get_template_part( 404 ); exit();
				}
			}
		}


		if(function_exists('bp_current_component') && bp_current_component() == 'groups'){
			$disable = vibebp_get_setting('public_group_directory','bp');
			$client_id = vibebp_get_setting('client_id');
			if($disable == 'on'){

				if(!empty($_GET) && !empty($_GET['client_id']) && $_GET['client_id'] == $client_id){
					//continue
				}else{
					global $wp_query;
				  $wp_query->set_404();
				  status_header( 404 );
				  get_template_part( 404 ); exit();
				}
			}
		}
		
		if(function_exists('bp_current_component') && bp_current_component() == 'activity'){
			$disable = vibebp_get_setting('public_activity','bp');
			$client_id = vibebp_get_setting('client_id');
			if($disable == 'on'){

				if(!empty($_GET) && !empty($_GET['client_id']) && $_GET['client_id'] == $client_id){
					//continue
				}else{
					global $wp_query;
				  $wp_query->set_404();
				  status_header( 404 );
				  get_template_part( 404 ); exit();
				}
			}
		}


	}

	function add_bp_nav(){
		global $bp;
		bp_core_new_nav_item( array( 
	        'name' => __('Dashboard','vibebp'),
	        'slug' => 'dashboard', 
	        'item_css_id' => 'dashboard',
	        'screen_function' => array($this,'show_screen'),
	        'default_subnav_slug' => 'dashboard', 
	        'position' => 1,
	        'show_for_displayed_user'=>false,
	        'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
	    ) );

		if(function_exists('wc_get_account_menu_items')){
			$slug='shop';
			bp_core_new_nav_item( array( 
		        'name' => __('Shop','vibebp'),
		        'slug' => $slug, 
		        'item_css_id' => 'shop',
		        'screen_function' => array($this,'show_screen'),
		        'default_subnav_slug' => 'home', 
		        'position' => 55,
		        'show_for_displayed_user'=>false,
		        'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
		    ) );

			foreach ( wc_get_account_menu_items() as $endpoint => $label ){
				if(in_Array($endpoint,apply_filters('vibebp_supported_endpoints',array('orders','edit-address','orders')))){
			
					bp_core_new_subnav_item( array(
						'name' 		  => $label,
						'slug' 		  => $endpoint,
						'parent_slug' => $slug,
			        	'parent_url' => $bp->displayed_user->domain.$slug.'/',
						'screen_function' => array($this,'show_screen'),
						'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
					) );
				}
				
			}
		}


		if(vibebp_get_setting('bp_followers','bp')){
			
	    	$slug = 'followers';
			global $bp;
			//Add Appointments tab in profile menu
		    bp_core_new_nav_item( array( 
		        'name' => __('Followers','vibebp'),
		        'slug' => 'followers', 
		        'item_css_id' => 'followers',
		        'screen_function' => array($this,'show_screen'),
		        'default_subnav_slug' => 'home', 
		        'position' => 55,
		        'show_for_displayed_user'=>false,
		        'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
		    ) );
		    	    	

	    	bp_core_new_subnav_item( array(
				'name' 		  => __( 'Followers', 'vibebp' ),
				'slug' 		  => 'home',
				'parent_slug' => $slug,
	        	'parent_url' => $bp->displayed_user->domain.$slug.'/',
				'screen_function' => array($this,'show_screen'),
				'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
			) );
			bp_core_new_subnav_item( array(
				'name' 		  => __( 'Following', 'vibebp' ),
				'slug' 		  => _x('following','following slug in profile ','vibebp'),
				'parent_slug' => $slug,
	        	'parent_url' => $bp->displayed_user->domain.$slug.'/',
				'screen_function' => array($this,'show_screen'),
				'user_has_access' => (bp_is_my_profile() || current_user_can('manage_options'))
			) );
		}
			

		if(function_exists('pmpro_hasMembershipLevel')){
			$slug = 'memberships';
			bp_core_new_nav_item( array( 
	            'name' => __('My Memberships', 'vibe' ), 
	            'slug' => $slug , 
	            'position' => 99,
	            'screen_function' => array($this,'show_screen'), 
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
				'item_css_id'     => $slug,
				'screen_function' => array( $this, 'show_screen' ),
				'user_has_access' => bp_is_my_profile(),
				'no_access_url'   => home_url(),
			) );
		}
	}


    function getvw(){
    	
    	//read file
    	if(file_exists(plugin_dir_path(__FILE__).'../assets/js/service-worker.js') && !empty(vibebp_get_setting('offline_page','service_worker'))){
            
            $contents = file_get_contents(plugin_dir_path(__FILE__).'../assets/js/service-worker.js');
            //replace constants
            $version = vibebp_get_setting('version','service_worker') ;
            $contents = str_replace('[SW_VERSION]','"'.$version.'"',$contents);            
			$contents = str_replace('[PLUGIN_URL]',plugins_url('../',__FILE__),$contents);
			if(!vibebp_get_setting('firebase')){
				$contents = preg_replace('/\[FIREBASE\](.*)\[\/FIREBASE\]/is', ' ', $contents);
			}else{
				$contents = str_replace('[FIREBASE]','',$contents);
				$contents = str_replace('[/FIREBASE]','',$contents);
				$firebase_config = vibebp_get_setting('firebase_config');
				$contents = str_replace('[FIREBASE_OBJECT]',json_encode($firebase_config),$contents);
				
			}

			$get = wp_remote_get(get_permalink(vibebp_get_setting('offline_page','service_worker')).'?pre_cache=1');

			$scripts=  get_option('vibe_sw_scripts');
			$styles=  get_option('vibe_sw_styles');

			array_splice($scripts,count($scripts),0,$styles);

			$scripts[]=plugins_url('../assets/js/localforage.min.js',__FILE__);
			if(vibebp_get_setting('firebase_config')){
				$scripts[]=plugins_url('../assets/js/firebase-app.js',__FILE__);
				$scripts[]=plugins_url('../assets/js/firebase-messaging.js',__FILE__);
			}
			foreach($scripts as $k=>$script){
				$scripts[$k] = $script.'?v='.$version;
			}
			$scripts[]=plugins_url('../assets/fonts/vicon.woff',__FILE__);
			$scripts[]=plugins_url('../assets/fonts/vicon.svg',__FILE__);
			$scripts[]=plugins_url('../assets/fonts/vicon.eot',__FILE__);
			$scripts[]=plugins_url('../assets/fonts/vicon.ttf',__FILE__);
			$scripts[]=plugins_url('../assets/js/manifest.json',__FILE__);
			$scripts[]=plugins_url('../assets/vicons.css',__FILE__);
			
			
			$contents = str_replace('[STATIC_ASSETS]',json_encode($scripts),$contents);

			$image = vibebp_get_setting('default_image','service_worker');
			if(is_numeric($image)){
				$image = wp_get_attachment_image_src($image,'full');
				$image = $image[0];
			}else{
				$image = plugins_url('../assets/images/avatar.jpg',__FILE__);
			}
			$contents = str_replace('[DEFAULT_IMAGE]','"'.$image.'"', $contents);
			$pid = vibebp_get_setting('offline_page','service_worker');
			$contents = str_replace('[OFFLINE_URL]','"'.get_permalink($pid).'"', $contents);
        }else{
        	_e('File missing','vibebp');
        	die();
        }
    	

        
        return $contents;
    }

    function pre_cache(){
    	
    	if(vibebp_get_setting('service_workers')){
    		$pid = vibebp_get_setting('offline_page','service_worker');
    		if(is_page($pid)){
    			global $wp_scripts;global $wp_styles;
    			$scripts = $styles = [];
    			if(!empty($_GET['pre_cache'])){
		    		foreach( $wp_scripts->queue as $script ){
		    			$flag = apply_filters('vibebp_precache_script',true,$script);
		    			if($flag){
		    				$scripts[] =  $wp_scripts->registered[$script]->src;
		    			}
		    		}
		    		foreach( $wp_styles->queue as $style ){
		    			$flag = apply_filters('vibebp_precache_style',true,$script);
		    			if($flag){
				       		$styles[] =  $wp_styles->registered[$style]->src;
				       	}
		    		}
				    
		    		update_option('vibe_sw_scripts',$scripts);
					update_option('vibe_sw_styles',$styles);
				}
	    	}
    	}
    }

    function apply_sw_version(){
    	if(vibebp_get_setting('service_workers')){
    		$pid = vibebp_get_setting('offline_page','service_worker');
    		if(is_page($pid)){
    			global $wp_scripts;global $wp_styles;
    			foreach( $wp_scripts->queue as $script ){
					$flag = apply_filters('vibebp_precache_script',true,$script);
	    			if($flag){
						$wp_scripts->registered[$script]->ver=vibebp_get_setting('version','service_worker');
					}
				}
				
				foreach( $wp_styles->queue as $style ){
					$flag = apply_filters('vibebp_precache_style',true,$script);
	    			if($flag){
	    				$wp_styles->registered[$style]->ver=vibebp_get_setting('version','service_worker');
	    			}
				}
    		}
    	}
    }
    function generate_manifest($force=0){
    	//app_name  app_description app_icon app_screenshot offline_page pre-cached

    	$app_icon = vibebp_get_setting('app_icon','service_worker');
    	if(!empty($app_icon)){
    		$att = wp_get_attachment_image_src($app_icon,'full');
    		if(empty($att) || get_post_mime_type($app_icon) != 'image/png'){
    			echo json_encode(array('status'=>1,'message'=>__('App Icon not a png image.','vibebp')));
				die();
    		}
    		if($att[1] >= 512 && $att[2] >=512){


	    		$image = wp_get_image_editor( $att[0] );

	    		$upload_dir = wp_get_upload_dir();
	    		$path = $upload_dir['basedir'];
				if ( ! is_wp_error( $image ) ) {
					if($att[1] >512){
						$image->resize( 512, 512, true );	
						$saved[512] = $image->save( $path.'/icon-512x512.png' );
					}
				    $image->resize( 384, 384, true );
				    $saved[384]=$image->save( $path.'/icon-384x384.png' );
				    $image->resize( 192, 192, true );
				    $saved[192]=$image->save( $path.'/icon-192x192.png' );
				    $image->resize( 152, 152, true );
				    $saved[152]=$image->save( $path.'/icon-152x152.png' );
				    $image->resize( 144, 144, true );
				    $saved[144]=$image->save( $path.'/icon-144x144.png' );
				    $image->resize( 128, 128, true );
				    $saved[128]=$image->save( $path.'/icon-128x128.png' );
				    $image->resize( 96, 96, true );
				    $saved[96]=$image->save( $path.'/icon-96x96.png' );
				    $image->resize( 72, 72, true );
				    $saved[72]=$image->save( $path.'/icon-72x72.png' );
				}	
			}else{
				echo json_encode(array('status'=>1,'message'=>__('App Icon image size less than recommended dimensions','vibebp')));
				die();
			}
    	}else{
    		$upload_dir=array('url'=>plugins_url('../assets/images/icons',__FILE__));
    	}

    	$manifest = array(
    		"lang"=>get_locale(),
  			"name"=>vibebp_get_setting('app_name','service_worker'),
  			"short_name"=>vibebp_get_setting('app_short_name','service_worker'),
  			"theme_color"=>'#394c62',
			"background_color"=> "#fafbfc",
  			"display"=> "fullscreen",
  			"Scope"=> "/",
  			"start_url"=>get_permalink(vibebp_get_setting('offline_page','service_worker')),
  			"gcm_sender_id"=>"103953800507",
  			"icons"=> array(
  				array(
  					"src"=> $upload_dir['url']."/icon-72x72.png",
				    "sizes"=> "72x72",
				    "type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['url']."/icon-96x96.png",
			      	"sizes"=> "96x96",
			      	"type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['url']."/icon-128x128.png",
      				"sizes"=> "128x128",
      				"type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['url']."/icon-144x144.png",
      				"sizes"=> "144x144",
      				"type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['url']."/icon-152x152.png",
      				"sizes"=> "152x152",
      				"type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['url']."/icon-192x192.png",
				      "sizes"=> "192x192",
				      "type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['url']."/icon-384x384.png",
			      	"sizes"=> "384x384",
			      	"type"=> "image/png"
  				),
  				array(
  					"src"=> $upload_dir['url']."/icon-512x512.png",
      				"sizes"=> "512x512",
      				"type"=> "image/png"	
  				),
  			),
    	);

    	if(!file_exists(plugin_dir_path(__FILE__).'../assets/js/manifest.json') || $force){
            $myFile = plugin_dir_path(__FILE__).'../assets/js/manifest.json';
            $fh = fopen($myFile, 'w');
            fwrite($fh, json_encode($manifest)."\n");
            fclose($fh);
        }
        return;
    }

	function install_sw($force=0){
		
		if(vibebp_get_setting('service_workers')){
			
			if ( ! function_exists( 'get_home_path' ) ) {
	            include_once ABSPATH . '/wp-admin/includes/file.php';
	        }
        
			$site_root = get_home_path();				            
	        if(!file_exists($site_root.'/firebase-messaging-sw.js') || $force){
	            $myFile = $site_root."/firebase-messaging-sw.js";

	            $fh = fopen($myFile, 'w');
	            $firebase_sw = $this->getvw();
	            fwrite($fh, print_r($firebase_sw, true)."\n");
	            fclose($fh);
	        }
        }

        echo json_encode(array('status'=>1,'message'=>__('Successfully Generated','vibebp')));
        die();
	}

	function show_screen(){

	}

	function synchronise_wp_logins(){
		if(is_user_logged_in()){
			$sync_login = vibebp_get_setting('sync_login');

			if(!empty($sync_login)){
				wp_enqueue_script('localforage',plugins_url('../assets/js/localforage.min.js',__FILE__),array(),VIBEBP_VERSION,true);

				$init = VibeBP_Init::init();
				$current_user = wp_get_current_user();

				$blog_id = '';
                if(function_exists('get_current_blog_id')){
                    $blog_id = get_current_blog_id();
                }

				?><script>
					document.addEventListener('DOMContentLoaded',function(){ 
						
						localforage.getItem('bp_login_token').then(function(token){
							
							if(!token && typeof jQuery != 'undefined'){
								jQuery.ajax({
						          	type: "POST",
						          	url: ajaxurl,
						          	dataType: 'json',
						          	data: { 
						          			action: 'generate_token',
						                  	security:'<?php echo wp_create_nonce('security'); ?>',
						                  	email:'<?php echo $current_user->user_email; ?>'
						                },
						          	cache: false,
						          	success: function (r) {
						          		if(r.status){
						          			localforage.setItem('bp_login_token',r.token);
						          			const event = new Event('wp_login_sync');
						          			document.dispatchEvent(event);
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
	}

	function generate_token(){

		$id = email_exists($_POST['email']);
		if($id !== get_current_user_id()){
			$data = array(
	        	'status' => 0,
	        	'message'=>__('Email & UserID do not match.','vibebp')
	        );
	        echo json_encode($data);
	        die();
		}
		$secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false; 
		/** Valid credentials, the user exists create the according Token */
        $issuedAt = time();
        $notBefore = apply_filters( VIBEBP.'_token_expire_not_before', $issuedAt, $issuedAt);

        $duration = vibebp_get_setting('token_duration');
        if(empty($duration)){
        	$duration = DAY_IN_SECONDS * 7;
        }
        $expire = apply_filters( VIBEBP.'_token_expire', $issuedAt  + $duration, $issuedAt);

        $user = wp_get_current_user();
        
        $token = array(
            'iss' => get_bloginfo('url'),
            'iat' => $issuedAt,
            'nbf' => $notBefore,
            'exp' => $expire,
            'data' => apply_filters('vibebp_jwt_token_data',array(
                'user' => array(
                    'id' => $user->data->ID,
                    'username'=>$user->data->user_login,
                    'slug'=>$user->data->user_nicename,
                    'email'=>$user->data->user_email,
                    'avatar'=> (function_exists('bp_core_fetch_avatar')?bp_core_fetch_avatar(array(
                                    'item_id' => $user->data->ID,
                                    'object'  => 'user',
                                    'type'=>'full',
                                    'html'    => false
                                )):get_avatar($user->data->user_email,240)),
                    'displayname'=>$user->data->display_name,
                    'roles'=> $user->roles,
                    'caps'=> $user->allcaps,
                    'profile_link'=>vibebp_get_profile_link($user->data->user_nicename)
	                ),
	            )
            ),
        );
        bp_update_user_last_activity($user->data->ID);
        
        if(!class_exists('JWT')){
        	include_once 'core/JWT.php';
        }
        /** Let the user modify the token data before the sign. */
        $token = JWT::encode(apply_filters(VIBEBP.'jwt_auth_token_before_sign', $token, $user), $secret_key);
        /** The token is signed, now create the object with no sensible user data to the client*/
        $data = array(
        	'status' => 1,
            'token' => $token,
            'message'=>_x('Token generated','Token generated','vibebp')
        );
        echo json_encode($data);
        die();
	}


	function login_detect_at_woocommerce_checkout(){
		
		if(function_exists('wc_get_page_id') && is_page(wc_get_page_id('checkout')) && !is_user_logged_in()){
		?>
		<script>
			
			document.addEventListener('DOMContentLoaded',function(){
				
				if(typeof localforage == 'object'){
					localforage.getItem('bp_login_token').then(function(token){
						if(token){
						    xhr = new XMLHttpRequest();
							xhr.open('POST', ajaxurl);
							xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							xhr.onload = function() {
							    if (xhr.status === 200) {
							    	let check = JSON.parse(xhr.responseText);
							    	if(check.status){
							    		window.location.reload(true);
							    	}else{
							    		localforage.removeItem('bp_login_token');
							    	}
							    }
							};

							xhr.send(encodeURI('action=vibebp_wc_login&client_id=<?php echo vibebp_get_setting('client_id'); ?>&security=<?php echo wp_create_nonce('security'); ?>&token=' + token));
						}
					});
				}
			});	
		</script>
		<?php
		}
	}

	function vibebp_wc_login(){

		if($_POST['client_id'] != vibebp_get_setting('client_id')){
			print_r(json_encode(array('status'=>0,'message'=>'Invalid client')));
			die();
		}
		if(!wp_verify_nonce($_POST['security'],'security')){
			print_r(json_encode(array('status'=>0,'message'=>'Invalid security')));
			die();
		}
		$token = $_POST['token'];
		/** Get the Secret Key */
        $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;

        if(!class_exists('JWT')){
        	include_once 'core/JWT.php';
        }
        //Tougher Security
		$secret_key = apply_filters('vibebp_tougher_security',$secret_key);
        if (!$secret_key) {
            $data = array(
	        	'status' => 0,
	            'data' => 'vibebp_jwt_secret_key_missing',
	            'message'=>_x('Secret key missing','Secret key missing','vibebp')
	        );
	        print_r(json_decode($data));
	        die();
        }
        /** Try to decode the token */ /** Else return exception*/
        try {
            $expanded_token = JWT::decode($token, $secret_key, array('HS256'));
            $expanded_token = apply_filters('vibebp_validate_token',$expanded_token,$token);
            if($expanded_token){
	            $data = array(
		        	'status' => 1, 
		            'data' => $expanded_token,
		            'message'=>_x('Valid Token','Valid Token','vibebp')
		        );
		        //potential security threat if token is captured by another user.
		        if(!user_can($expanded_token->data->user->id,'manage_options') && email_exists($expanded_token->data->user->email)){
		        	//only works for non-admins
		        	wp_set_auth_cookie($expanded_token->data->user->id,false);
		        	print_r(json_encode(apply_filters(VIBEBP.'jwt_auth_token_validate_before_dispatch', $data)));
		        	die();
		        }else{
		        	print_r(json_encode(array('status'=>0,'message'=>'Invalid user')));
		        
		        }
		        
		        
	        }else{
	        	$data = array(
	        	'status' => 0,
	            'data' => 'jwt_auth_invalid_token',
		        );
		        print_r(json_encode($data));
		        die();
	        }
	        

        }catch (Exception $e) {
            $data = array(
	        	'status' => 0,
	            'data' => 'jwt_auth_invalid_token',
	            'message'=>$e->getMessage()
	        );
	        print_r(json_encode($data));
        }
		
		die();
	}
	
}

VibeBP_Actions::init();
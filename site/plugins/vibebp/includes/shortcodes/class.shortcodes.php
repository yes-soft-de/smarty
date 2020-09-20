<?php


/**
 * Customizer functions for vibeapp
 *
 * @author      VibeThemes
 * @category    Actions
 * @package     vibeapp
 * @version     1.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;


class VibeBp_Shortcodes{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBp_Shortcodes();

        return self::$instance;
    }

    private function __construct(){

    	add_action('template_redirect',array($this,'check_shortcode'),0);
    	add_shortcode('vibebp_login',array($this,'login_shortcode'));
    	add_shortcode('vibebp_profile',array($this,'profile'));
    	add_shortcode('vibeapp_carousel',array($this,'vibeapp_carousel'));

    }

    function check_shortcode(){

    	global $post;

    	if(!empty($post)){
    		
    		if(has_shortcode($post->post_content,'vibebp_login') || bp_is_user()){
    			add_filter('vibebp_enqueue_login_script',function($x){return true;});
    		}
    		if(has_shortcode($post->post_content,'vibebp_profile') || bp_is_user()){
    			if(vibebp_get_setting('service_workers')){

    				//Please respect code privacy. Small code but lot of effort. Respect orignal.
    				

    				add_action('wp_head',function(){


    					$app_title =vibebp_get_setting('app_name','service_worker');
	    				if(empty($app_title)){
	    					$app_title = 'WPLMS';
	    				}

	    				$theme_color =vibebp_get_setting('theme_color','service_worker');
	    				if(empty($theme_color)){
	    					$theme_color = '#3ecf8e';
	    				}
    					
    					//<!-- iOS  -->
    					if(file_exists($path.'/icon-80x80.png')){
    						echo '<link href="'.$upload_dir['baseurl'].'/icon-80x80.png" rel="apple-touch-icon">';
    					}else{
    						echo '<link href="'.plugins_url('../../assets/images/icons/icon-80x80.png',__FILE__).'" rel="apple-touch-icon">';
    					}

    					if(file_exists($path.'/icon-120x120.png')){
    						echo '<link href="'.$upload_dir['baseurl'].'/icon-120x120.png" rel="apple-touch-icon" sizes="120x120">';
    					}else{
    						echo '<link href="'.plugins_url('../../assets/images/icons/icon-120x120.png',__FILE__).'" rel="apple-touch-icon" sizes="120x120">';
    					}

    					if(file_exists($path.'/icon-152x152.png')){
    						echo '<link href="'.$upload_dir['baseurl'].'/icon-152x152.png" rel="apple-touch-icon" sizes="152x152">';
    					}else{
    						echo '<link href="'.plugins_url('../../assets/images/icons/icon-152x152.png',__FILE__).'" rel="apple-touch-icon" sizes="152x152">';
    					}

    					
	    				//<!-- Android  -->
	    				if(file_exists($path.'/icon-192x192.png')){
    						echo '<link href="'.$upload_dir['baseurl'].'/icon-192x192.png" rel="icon" sizes="192x192">';
    					}else{
    						echo '<link href="'.plugins_url('../../assets/images/icons/icon-192x192.png',__FILE__).'" rel="icon" sizes="192x192">';
    					}
    					if(file_exists($path.'/icon-128x128.png')){
    						echo '<link href="'.$upload_dir['baseurl'].'/icon-128x128.png" rel="icon" sizes="128x128">';
    					}else{
    						echo '<link href="'.plugins_url('../../assets/images/icons/icon-128x128.png',__FILE__).'" rel="icon" sizes="128x128">';
    					}

						//<!--  Microsoft  -->
						if(file_exists($path.'/icon-144x144.png')){
							echo '<meta name="msapplication-TileImage" content="'.$upload_dir['baseurl'].'/icon-144x144.png" />';
						}else{
							echo '<meta name="msapplication-TileImage" content="'.plugins_url('../../assets/images/icons/icon-144x144.png',__FILE__).'" />';
						}

						if(file_exists($path.'/icon-72x72.png')){
    						echo '<meta name="msapplication-square70x70logo" content="'.$upload_dir['baseurl'].'/icon-72x72.png" />';
    					}else{
    						echo '<meta name="msapplication-square70x70logo" content="'.plugins_url('../../assets/images/icons/icon-72x72.png',__FILE__).'" />';
    					}
						if(file_exists($path.'/icon-152x152.png')){
    						echo '<meta name="msapplication-square150x150logo" content="'.$upload_dir['baseurl'].'/icon-152x152.png" />';
    					}else{
    						echo '<meta name="msapplication-square150x150logo" content="'.plugins_url('../../assets/images/icons/icon-152x152.png',__FILE__).'" />';
    					}
    					if(file_exists($path.'/icon-384x384.png')){
    						echo '<meta name="msapplication-square310x310logo" content="'.$upload_dir['baseurl'].'/icon-384x384.png" />';
    					}else{
    						echo '<meta name="msapplication-square310x310logo" content="'.plugins_url('../../assets/images/icons/icon-384x384.png',__FILE__).'" />';
    					}

						//<!-- UC Browser  -->
						//<link href="images/icon-52x52.png" rel="apple-touch-icon-precomposed" sizes="57x57">
						//<link href="images/icon-72x72.png" rel="apple-touch-icon" sizes="72x72">


    					$splash_sizes = array(
    						'1242x2688'=>array(
    							'width'=>414,'height'=>896,'pixel-ratio'=>3,),
    						'828x1792'=>array(
    							'width'=>414,'height'=>896,'pixel-ratio'=>2),
    						'1125x2436'=>array(
    							'width'=>375,'height'=>812,'pixel-ratio'=>3),
    						'1242x2208'=>array(
    							'width'=>414,'height'=>736,'pixel-ratio'=>3),
    						'750x1334'=>array(
    							'width'=>375,'height'=>667,'pixel-ratio'=>2),
    						'2048x2732'=>array(
    							'width'=>1024,'height'=>1366,'pixel-ratio'=>2),
    						'166x2224'=>array(
    							'width'=>834,'height'=>1112,'pixel-ratio'=>2),
    						'1536x2048'=>array(
    							'width'=>168,'height'=>1024,'pixel-ratio'=>2),
    					);

    					foreach($splash_sizes as $ext=>$size){
    						if(file_exists($path.'/splash-'.$ext.'.png')){
    							echo '<link rel="apple-touch-startup-image" media="(device-width: '.$size['width'].'px) and (device-height: '.$size['height'].'px) and (-webkit-device-pixel-ratio: '.$size['pixel-ratio'].')"  href="'.$upload_dir['baseurl'].'/splash-'.$ext.'.png">';
    						}else{

    							echo '<link rel="apple-touch-startup-image" media="(device-width: '.$size['width'].'px) and (device-height: '.$size['height'].'px) and (-webkit-device-pixel-ratio: '.$size['pixel-ratio'].')"  href="'.plugins_url('../../assets/images/splash/splash-'.$ext.'.png',__FILE__).'.png">';
    						}
    						
    					}

    					$upload_dir = wp_get_upload_dir();
    					$path =$upload_dir['baseurl'];
    					echo '<link rel="manifest" href="'.plugins_url('../../assets/js/manifest.json',__FILE__).'"><meta name="theme-color" content="'.$theme_color.'"><meta name="theme-color" content="'.$theme_color.'"><meta name="msapplication-TileColor" content="'.$theme_color.'" /><meta name="msapplication-config" content="none"/><meta name="msapplication-navbutton-color" content="'.$theme_color.'">';

    					echo '<meta name="apple-mobile-web-app-title" content="'.$app_title.'"><meta name="apple-mobile-web-app-capable" content="yes"><meta name="apple-mobile-web-app-status-bar-style" content="black-translucent"><meta name="mobile-web-app-capable" content="yes">';

    					if(file_exists($path.'/icon-192x192.png')){
    						echo '<link rel="apple-touch-icon" href="'.$upload_dir['baseurl'].'/icon-192x192.png">';
    					}else{
    						echo '<link rel="apple-touch-icon" href="'.plugins_url('../../assets/images/icons/icon-192x192.png',__FILE__).'">';
    					}
    					

						if(file_exists($path.'/icon-144x144.png')){
    						echo '<link rel="apple-touch-icon" href="'.$upload_dir['baseurl'].'/icon-144x144.png">';
    					}else{
    						echo '<link rel="apple-touch-icon" href="'.plugins_url('../../assets/images/icons/icon-144x144.png',__FILE__).'">';
    					}
    				});

    				remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
				    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
				    remove_action( 'wp_print_styles', 'print_emoji_styles' );
				    remove_action( 'admin_print_styles', 'print_emoji_styles' );   
				    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
				    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );     
				    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

				    //To be decided -----***
				    //remove_action('wp_footer',array('Wplms_Wishlist_Init','append_span'));
				    //remove_action('wp_footer',array('Wplms_Wishlist_Init','append_collection'));

    				//add_action('wp_print_scripts', array($this,'remove_all_scripts'), 100);
					//add_action('wp_print_styles',  array($this,'remove_all_styles'), 100);
					// add_filter( 'style_loader_src', function ( $src ) {
					//     if ( strpos( $src, 'ver=' ) ){
					//         $src = remove_query_arg( 'ver', $src );
					//     }
					//     return $src;
					// }, 9999 );

					// add_filter( 'script_loader_src', function ( $src ) {
					//     if ( strpos( $src, 'ver=' ) )
					//         $src = remove_query_arg( 'ver', $src );
					//     return $src;
					// }, 9999 );
					// add_action('wp_footer',function(){
					// 	echo '<script>document.querySelector("#wpadminbar").remove();
					// 	document.querySelector("body").classList.remove("admin-bar");</script>';
					// },9999);
    			}

    			add_filter('vibebp_enqueue_profile_script',function($x){return true;});
    		}
    	}
    	
    }

    function login_shortcode($atts,$content=null){

    	$defaults = array(
    		'class'=>'',
    		'button'=>'',
    	);

    	$return = '';
    	
    	if(!empty($atts['button'])){
    		$return .='<span class="'.$atts['class'].' vibebp-login" type="popup">'.$content.'</span>';
    	}else{
    		$return .='<span class="vibebp-login" type="static">'.$content.'</span>';
    	}
    	return $return;
    }

    function profile($atts,$content=null){

    	$return ='<div id="vibebp_member"></div>';

    	add_action('wp_footer',function(){

    		if ( ! function_exists( 'get_home_path' ) ) {
	            include_once ABSPATH . '/wp-admin/includes/file.php';
	        }
        
			$site_root = get_home_path();				          

    		if(file_exists($site_root.'/firebase-messaging-sw.js')){
    			if(vibebp_get_setting('service_workers')){
    				$scope = '';
    				//$url = vibebp_get_setting('offline_page','service_worker');
    				$site_url = site_url();
    				//$scope = str_replace($site_url.'/','',$url);

					if($_SERVER["DOCUMENT_ROOT"] != $site_root){
						$scope =  str_replace($_SERVER["DOCUMENT_ROOT"],'', $site_root).$scope;
					}
					
			    	?>
			    	<script>if ('serviceWorker' in navigator && window.vibebp.api.sw_enabled) {

							navigator.serviceWorker.getRegistrations().then(registrations => {

							    let check = registrations.findIndex((item)=>{
							    	return (item.active.scriptURL == window.vibebp.api.sw && item.active.state == 'activated')
							    });
							    let sw_first = window.vibebp.api.sw.split('?v=');
							    let index = registrations.findIndex((i) => {return i.active.scriptURL.indexOf(sw_first[0]) > -1 });
							    if(index > -1 && registrations[index].active.scriptURL.indexOf(sw_first[1]) == -1){
							    	//unregister previous version

							    	registrations[registrations.findIndex(i => i.active.scriptURL.indexOf(sw_first[0]) > -1)].unregister();
							    	check = -1;
							    }
								//service worker registration to be called only once.
								if(check == -1){
								  	
								  	navigator.serviceWorker.register(window.vibebp.api.sw,{
								  		scope:'<?php echo $scope; ?>'
								  	}).then(function(registration) {
								      console.log('Vibebp ServiceWorker registration successful with scope: ', registration.scope);
								    }, function(err) {
								      console.log('Vibebp ServiceWorker registration failed: ', err);
								    });
							  	}else{
							  		console.log('Vibebp Service worker already registered & active.');
							  	}
						  	});
						}</script>
			    	<?php
		    	}else{
		    		unlink($site_root.'/firebase-messaging-sw.js');
	    			$delete_sw = 1; 
	    			//WP_Filesystem_Direct::delete($site_root.'/firebase-messaging-sw.js');

		    		?>
		    		<script>
		    			navigator.serviceWorker.getRegistrations().then(function(registrations) {
						 	for(let registration of registrations) {
						  		registration.unregister();
						  		<?php if($delete_sw){ ?>
						  		setTimeout(function(){
					              window.location.replace(window.location.href);
					            }, 3000);
						  		<?php } ?>
							} 
						});
						if ('caches' in window) {
						    caches.keys()
						      .then(function(keyList) {
						          return Promise.all(keyList.map(function(key) {
						              return caches.delete(key);
						          }));
						      })
						}
		    		</script>
		    		<?php
	    		}
	    	} 
	    });
	    wp_enqueue_script('jquery');
    	return $return;
    }


    function remove_all_scripts() {

		if(!vibebp_get_setting('service_workers'))
			return;

	    global $wp_scripts;
	    $queue = $wp_scripts->queue;
	    
	    //$wp_scripts->queue = vibebp_get_pwa_scripts(1);
	}


	function remove_all_styles() {
		if(!vibebp_get_setting('service_workers'))
			return;
	    global $wp_styles;

	    //$wp_styles->queue = vibebp_get_pwa_styles(1);

	}


    function vibebp_grid($atts){

    	$attributes_string = '';
    	$width = '268';
    	if(!empty($atts['gutter'])){
    		$attributes_string .= 'data-gutter="'.$atts['gutter'].'" ';
    	}
    	
    	$grid_control = [];
    	if(!empty($atts['grid_control'])){
    		
    		$grid_control = json_decode($atts['grid_control'],true);
    	
    		$atts['grid_number']  = count($grid_control['grid']);
    	}



    	$randclass = wp_generate_password(8,false,false);
    	$output .= '<div class="vibebp_grid '.$randclass.'" '.$attributes_string.'>';
    	if(!isset($atts['post_ids']) || strlen($atts['post_ids']) < 2){
        
	        if(isset($atts['term']) && isset($atts['taxonomy']) && $atts['term'] !='nothing_selected'){
	            
	            if(!empty($atts['taxonomy'])){ 
	                
	                if(strpos($atts['term'], ',') === false){
	                    $check=term_exists($atts['term'], $atts['taxonomy']);
	                    if($atts['term'] !='nothing_selected'){    
	                        if ($check == 0 || $check == null || !$check) {
	                            $error = new VibeAppErrors();
	                            $output .= $error->get_error('term_taxonomy_mismatch');
	                            $output .='</div>';
	                            return $output;
	                       } 
	                    }
	                }    
	                $check=is_object_in_taxonomy($atts['post_type'], $atts['taxonomy']);
	                if ($check == 0 || $check == null || !$check) {
	                    $error = new VibeAppErrors();
	                    $output .= $error->get_error('term_postype_mismatch');
	                    $output .='</div>';
	                    return $output;
	               }

	                $terms = $atts['term'];
	                if(strpos($terms,',') !== false){
	                    $terms = explode(',',$atts['term']);
	                } 
	            }

	            
	            $query_args=array( 'post_type' => $atts['post_type'],'posts_per_page' => $atts['grid_number']);
	            if(!empty($atts['taxonomy'])){ 
	              $query_args['tax_query'] = array(
	                  'relation' => 'AND',
	                  array(
	                      'taxonomy' => $atts['taxonomy'],
	                      'field'    => 'slug',
	                      'terms'    => $terms,
	                  ),
	              );
	            }
	        }else{
	           $query_args=array('post_type'=>$atts['post_type'], 'posts_per_page' => $atts['grid_number']);
	        }
	        
	        if($atts['post_type'] == 'course' && isset($atts['course_style'])){
	            switch($atts['course_style']){
	                case 'popular':
	                  $query_args['orderby'] = 'meta_value_num';
	                  $query_args['meta_key'] = 'vibe_students';
	                break;
	                case 'featured':
	                  if(empty($query_args['meta_query'])){
	                    $query_args['meta_query'] = array(
	                              array(
	                                  'key'     => 'featured',
	                                  'value'   => 1,
	                                  'compare' => '>='
	                              )
	                          );
	                  }else{
	                    $query_args['meta_query'][] = array(
	                                  'key'     => 'featured',
	                                  'value'   => 1,
	                                  'compare' => '>='
	                          );
	                  }
	                break;
	                case 'rated':
	                  $query_args['orderby'] = 'meta_value_num';
	                  $query_args['meta_key'] = 'average_rating';
	                break;
	                case 'reviews':
	                  $query_args['orderby'] = 'comment_count';
	                break;
	                case 'start_date':
	                  $query_args['orderby'] = 'meta_value';
	                  $query_args['meta_key'] = 'vibe_start_date';
	                  $query_args['meta_type'] = 'DATE';
	                  $query_args['order'] = 'ASC';
	                  $today = date('Y-m-d');
	                  if(empty($query_args['meta_query'])){
	                    $query_args['meta_query'] = array(
	                              array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '>='
	                              )
	                          );
	                  }else{
	                    $query_args['meta_query'][] = array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '>='
	                          );
	                  }
	                  
	                break;
	                case 'expired_start_date':
	                  $query_args['orderby'] = 'meta_value';
	                  $query_args['meta_key'] = 'vibe_start_date';
	                  $query_args['meta_type'] = 'DATE';
	                  $query_args['order'] = 'ASC';
	                  $today = date('Y-m-d');
	                  if(empty($query_args['meta_query'])){
	                    $query_args['meta_query'] = array(
	                              array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '<'
	                              )
	                          );
	                  }else{
	                    $query_args['meta_query'][] = array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '<'
	                          );
	                  }
	                  
	                break;
	                case 'random':
	                   $query_args['orderby'] = 'rand';
	                break;
	                case 'free':
	                 if(empty($query_args['meta_query'])){
	                  $query_args['meta_query'] =  array(
	                      array(
	                        'key'     => 'vibe_course_free',
	                        'value'   => 'S',
	                        'compare' => '=',
	                      ),
	                    );
	                }else{
	                  $query_args['meta_query'][] =  array(
	                        'key'     => 'vibe_course_free',
	                        'value'   => 'S',
	                        'compare' => '=',
	                    );
	                }
	                break;
	                default:
	                  $query_args['orderby'] = '';
	            }
	            if(empty($query_args['order']))
	              $query_args['order'] = 'DESC';

	            
	        }
	        $query_args =  apply_filters('vibebp_elementor_filters',$query_args);
	        $the_query = new WP_Query($query_args);

        }else{

          $cus_posts_ids=explode(",",$atts['post_ids']);
        	$query_args=array( 'post_type' => $atts['post_type'], 'post__in' => $cus_posts_ids , 'orderby' => 'post__in','posts_per_page'=>count($cus_posts_ids)); 
        	$query_args =  apply_filters('vibebp_elementor_filters',$query_args);
        	$the_query = new WP_Query($query_args);
        }
        if($atts['column_width'] < 311)
             $cols = 'small';
         
        if(($atts['column_width'] >= 311) && ($atts['column_width'] < 460))    
             $cols='medium';
         
        if(($atts['column_width'] >= 460) && ($atts['column_width'] < 769))    
             $cols='big';
         
        if($atts['column_width'] >= 769)    
             $cols='full';
         
        $style ='';
       	if( $the_query->have_posts() ) {
    		$style .= '<style>.'.$randclass.' {
						    width:100%;
						    display:grid;';




			if(!empty($atts['column_align_verticle'])){
				$style .= 'align-items:'.$atts['column_align_verticle'].';';
			}
			if(!empty($atts['column_align_horizontal'])){
				$style .= 'justify-content:'.$atts['column_align_horizontal'].';';
			}
				    
			$style .=	'grid-gap:'.$atts['gutter'].'px;
						}</style>';
	        //$row_logic = $atts['carousel_rows'];
	       // $row_logic = 0; 
			$i = 0;
	        while ( $the_query->have_posts() ) : $the_query->the_post();
	        global $post;
	        $style_string  ='';
	        if(!empty($grid_control['grid']) && !empty($grid_control['grid'][$i])){
	        	 $style_string .= 'grid-column:'.$grid_control['grid'][$i]['col'].';';
	        	 $style_string .= 'grid-row:'.$grid_control['grid'][$i]['row'].';';
	        }
	        if(!empty($atts['grid_width'])){
	        	$width = $atts['grid_width'];
	        	//$style_string .= 'width:'.$atts['grid_width'].'px;';
	        }
	       	$output .= '<div class="grid_item " style="'.$style_string.'">
	       	'.vibebp_render_block_from_style($post,$atts['featured_style'],$cols,$atts['carousel_excerpt_length']).'
	       	</div>';
	       	$i++;
	    	endwhile;
			
       	}else{
       		$output .= '<div class="vbp_message">'._x('No posts Found !','','vibeapp').'</div>';
       	}
       	$output .= '</div>'.$style;
       	wp_reset_postdata();
    	return $output;
    }

    function vibeapp_carousel($atts,$content){
    	$attributes_string = '';
    	$width = '268';
    	if(!empty($atts['auto_slide'])){
    		$attributes_string .= 'data-autoplay="1" ';
    	}
    	if(!empty($atts['column_width'])){
    		$attributes_string .= 'data-column_width="'.$atts['column_width'].'" ';
    		$width = $atts['column_width'];
    	}
    	if(!empty($atts['carousel_max'])){
    		$attributes_string .= 'data-carousel_max="'.$atts['carousel_max'].'" ';
    	}
    	if(!empty($atts['carousel_min'])){
    		$attributes_string .= 'data-carousel_min="'.$atts['carousel_min'].'" ';
    	}
    	
    	$output .= '<div class="glide" '.$attributes_string.'>';
    	if(!isset($atts['post_ids']) || strlen($atts['post_ids']) < 2){
        
	        if(isset($atts['term']) && isset($atts['taxonomy']) && $atts['term'] !='nothing_selected'){
	            
	            if(!empty($atts['taxonomy'])){ 
	                
	                if(strpos($atts['term'], ',') === false){
	                    $check=term_exists($atts['term'], $atts['taxonomy']);
	                    if($atts['term'] !='nothing_selected'){    
	                        if ($check == 0 || $check == null || !$check) {
	                            $error = new VibeAppErrors();
	                            $output .= $error->get_error('term_taxonomy_mismatch');
	                            $output .='</div>';
	                            return $output;
	                       } 
	                    }
	                }    
	                $check=is_object_in_taxonomy($atts['post_type'], $atts['taxonomy']);
	                if ($check == 0 || $check == null || !$check) {
	                    $error = new VibeAppErrors();
	                    $output .= $error->get_error('term_postype_mismatch');
	                    $output .='</div>';
	                    return $output;
	               }

	                $terms = $atts['term'];
	                if(strpos($terms,',') !== false){
	                    $terms = explode(',',$atts['term']);
	                } 
	            }

	            
	            $query_args=array( 'post_type' => $atts['post_type'],'posts_per_page' => $atts['carousel_number']);
	            if(!empty($atts['taxonomy'])){ 
	              $query_args['tax_query'] = array(
	                  'relation' => 'AND',
	                  array(
	                      'taxonomy' => $atts['taxonomy'],
	                      'field'    => 'slug',
	                      'terms'    => $terms,
	                  ),
	              );
	            }
	        }else{
	           $query_args=array('post_type'=>$atts['post_type'], 'posts_per_page' => $atts['carousel_number']);
	        }
	        
	        if($atts['post_type'] == 'course' && isset($atts['course_style'])){
	            switch($atts['course_style']){
	                case 'popular':
	                  $query_args['orderby'] = 'meta_value_num';
	                  $query_args['meta_key'] = 'vibe_students';
	                break;
	                case 'featured':
	                  if(empty($query_args['meta_query'])){
	                    $query_args['meta_query'] = array(
	                              array(
	                                  'key'     => 'featured',
	                                  'value'   => 1,
	                                  'compare' => '>='
	                              )
	                          );
	                  }else{
	                    $query_args['meta_query'][] = array(
	                                  'key'     => 'featured',
	                                  'value'   => 1,
	                                  'compare' => '>='
	                          );
	                  }
	                break;
	                case 'rated':
	                  $query_args['orderby'] = 'meta_value_num';
	                  $query_args['meta_key'] = 'average_rating';
	                break;
	                case 'reviews':
	                  $query_args['orderby'] = 'comment_count';
	                break;
	                case 'start_date':
	                  $query_args['orderby'] = 'meta_value';
	                  $query_args['meta_key'] = 'vibe_start_date';
	                  $query_args['meta_type'] = 'DATE';
	                  $query_args['order'] = 'ASC';
	                  $today = date('Y-m-d');
	                  if(empty($query_args['meta_query'])){
	                    $query_args['meta_query'] = array(
	                              array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '>='
	                              )
	                          );
	                  }else{
	                    $query_args['meta_query'][] = array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '>='
	                          );
	                  }
	                  
	                break;
	                case 'expired_start_date':
	                  $query_args['orderby'] = 'meta_value';
	                  $query_args['meta_key'] = 'vibe_start_date';
	                  $query_args['meta_type'] = 'DATE';
	                  $query_args['order'] = 'ASC';
	                  $today = date('Y-m-d');
	                  if(empty($query_args['meta_query'])){
	                    $query_args['meta_query'] = array(
	                              array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '<'
	                              )
	                          );
	                  }else{
	                    $query_args['meta_query'][] = array(
	                                  'key'     => 'vibe_start_date',
	                                  'value'   => $today,
	                                  'compare' => '<'
	                          );
	                  }
	                  
	                break;
	                case 'random':
	                   $query_args['orderby'] = 'rand';
	                break;
	                case 'free':
	                 if(empty($query_args['meta_query'])){
	                  $query_args['meta_query'] =  array(
	                      array(
	                        'key'     => 'vibe_course_free',
	                        'value'   => 'S',
	                        'compare' => '=',
	                      ),
	                    );
	                }else{
	                  $query_args['meta_query'][] =  array(
	                        'key'     => 'vibe_course_free',
	                        'value'   => 'S',
	                        'compare' => '=',
	                    );
	                }
	                break;
	                default:
	                  $query_args['orderby'] = '';
	            }
	            if(empty($query_args['order']))
	              $query_args['order'] = 'DESC';

	            $query_args =  apply_filters('wplms_carousel_course_filters',$query_args);
	        }
	        
	        $the_query = new WP_Query($query_args);

        }else{

          $cus_posts_ids=explode(",",$atts['post_ids']);
        	$query_args=array( 'post_type' => $atts['post_type'], 'post__in' => $cus_posts_ids , 'orderby' => 'post__in','posts_per_page'=>count($cus_posts_ids)); 
        	$the_query = new WP_Query($query_args);
        }
        if($atts['column_width'] < 311)
             $cols = 'small';
         
        if(($atts['column_width'] >= 311) && ($atts['column_width'] < 460))    
             $cols='medium';
         
        if(($atts['column_width'] >= 460) && ($atts['column_width'] < 769))    
             $cols='big';
         
        if($atts['column_width'] >= 769)    
             $cols='full';

        $randclass = wp_generate_password(8,false,false); 
        //$output .= '<style>.'.$randclass.'{width: '.$width.'px !important;}</style>';
       	if( $the_query->have_posts() ) {
          	$output .= '<div class="glide__track" data-glide-el="track">
    				<ul class="glide__slides">';
    		
	        //$row_logic = $atts['carousel_rows'];
	       // $row_logic = 0; 
    		$i = 0;
	        while ( $the_query->have_posts() ) : $the_query->the_post();
	        global $post;
	       	$output .= '<li class="glide__slide '.$randclass.'">
	       	'.vibebp_render_block_from_style($post,$atts['featured_style'],$cols,$atts['carousel_excerpt_length']).'
	       	</li>';
	       	$i++;
	    	endwhile;
	    	$output .= '</ul></div>';

	    	if(!empty($atts['show_controls'])){
	    		$output .= '<div class="glide__arrows" data-glide-el="controls">
				    <span class="glide__arrow glide__arrow--left" data-glide-dir="<"><i class="vicon-angle-left"></i></span>
				    <span class="glide__arrow glide__arrow--right" data-glide-dir=">"><i class="vicon-angle-right"></i></span>
				 </div>';
	    	}
			
	    	if(!empty($atts['show_controlnav'])){
				$output .= '<div class="glide__bullets" data-glide-el="controls[nav]">';
			    $count = 0;
			    while($count < $i){
			    	$output .= '<span class="glide__bullet" data-glide-dir="='.$count.'"></span>';
			    	$count++;
			    }
		    
		  		$output .= '</div>';
		  	}
       	}else{
       		$output .= _x('No posts Found !','','vibeapp');
       	}
       	$output .= '</div>';

    	return $output;
    }
    
}

VibeBp_Shortcodes::init();


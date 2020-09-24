<?php
/**
 * Register important functions for WPLMS
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     2.0
 */

if ( !defined( 'ABSPATH' ) ) exit;

class WPLMS_Register{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Register();

        return self::$instance;
    }

    private function __construct(){

        //Head Essentials
        add_action('wp_enqueue_scripts',array($this,'vibe_header_essentials'));

        // Image Sizes
        add_action( 'init', array($this,'add_image_sizes' ));
        add_filter( 'image_size_names_choose',array($this, 'wplms_custom_image_sizes' ));

        //User Roles
        add_action('init',array($this,'vibe_user_roles'));
        add_action( 'admin_init',array($this,'add_theme_caps'));

        //Admin bar
        add_action('after_setup_theme',array($this,'remove_admin_bar'));
        // WP Admin access
        add_action('current_screen',array($this,'wp_admin_access'));
        add_filter('wplms_front_end_course_extras',array($this,'hide_edit_in_admin'));
        //Remove WooCommerce Styling
        add_filter( 'woocommerce_enqueue_styles', '__return_false' );

        // Admin Styles
        add_action("admin_enqueue_scripts",array($this,"wplms_enqueue_admin"),10,1);

        // Styles and Scripts
        add_action('wp_enqueue_scripts',array($this,'wplms_enqueue_head'));
        add_action('wp_enqueue_scripts',array($this,'wplms_force_enqueue_head'));
        //ENQUEUE SCRIPTS TO FOOTER
        add_action('wp_footer',array($this,'wplms_enqueue_footer'));

        //Theme Custmoizer  
        add_action("wp_head","print_customizer_style",99);
         
        //Register Sidebars
        add_action('widgets_init',array($this,'wplms_register_sidebars')); 
        
        // Re-set Google Fonts
        add_action( 'admin_init',array($this,'storegoogle_webfonts' ));
        //Set Vibe Menus
        add_action( 'init',array($this,'register_vibe_menus' ));
    }

    /*============================================*/
    /*=====  RESPONSIVE | FAVICON | HTML5  =======*/
    /*============================================*/
    function vibe_header_essentials(){
        $favicon = vibe_get_option('favicon');
        if(!isset($favicon))
            $favicon = VIBE_URL.'/images/favicon.png';

        $credits = vibe_get_option('credits');
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">
              <meta name="author" content="'.(isset($credits)?$credits:'vibethemes').'">';

       if(!empty($favicon)){
          
          echo '<link rel="shortcut icon" href="'.$favicon.'" />
              <link rel="icon" type="image/png" href="'.$favicon.'">';
       }       
             

    }
    /*============================================*/
    /*=====  REGISTER CUSTOM IMAGE SIZES  ========*/
    /*============================================*/
    function add_image_sizes() {
      add_image_size('mini', 120, 120);
      add_image_size('small', 310, 9999);
      add_image_size('medium', 460, 9999);
      add_image_size('big', 768, 9999);
    }

    function wplms_custom_image_sizes( $sizes ) {
        $custom_sizes = array(
            'big' => 'Big Size',
            'small' => 'Small Size',
            'mini' => 'Extra small/Mini'
        );
        return array_merge( $sizes, $custom_sizes );
    }
    /*============================================*/
    /*===========  REMGISTER CUSTOM USER ROLES  ============*/
    /*============================================*/
    function vibe_user_roles(){
      $teacher_capability=array(
          'delete_posts'=> true,
          'delete_published_posts'=> true,
          'edit_posts'=> true,
          'manage_categories' => true,
          'edit_published_posts'=> true,
          'publish_posts'=> true,
          'read' => true,
          'upload_files'=> true,
          'unfiltered_html'=> true,
          'level_1' => true
          );
      $student_capability=array(
          'read'
          );
      
          add_role( 'student', __('Student','vibe'), $student_capability );
          add_role( 'instructor', __('Instructor','vibe'),$teacher_capability);      
    }
    function add_theme_caps() {
        // gets the author role
        $role = get_role( 'instructor' );
        $role->add_cap( 'unfiltered_html' ); 
    }
    
    /*============================================*/
    /*===========  REMOVE DEFAULT BUDDYPRESS ADMIN BAR  ============*/
    /*============================================*/
    function remove_admin_bar() {
      $val = vibe_get_option('hide_wp_admin_bar');
      if(!empty($val)){
        switch($val){
          case '1':
            if (!current_user_can('manage_options')) {
              add_filter( 'show_admin_bar', '__return_false',99);
            }                
          break;
          case '2':
            add_filter( 'show_admin_bar', '__return_false',99 ); 
          break;
          case '3':
          break;
          default:
            if (!current_user_can('edit_posts')) {
              add_filter( 'show_admin_bar', '__return_false',99 );
            }        
          break;
        }
      }
    }
    /*============================================*/
    /*===========  WP ADMIN ACCESS    ============*/
    /*============================================*/
    function wp_admin_access(){
        if((defined( 'DOING_AJAX' ) && DOING_AJAX) || (defined('IFRAME_REQUEST') && IFRAME_REQUEST))
          return;
        
        $val = vibe_get_option('wp_admin_access');
        $cap = apply_filters('wplms_admin_access_capabilities',array(1=>array('edit_posts'),2=>array('manage_options')));
        $flag = 1;
        if(!empty($val) && !empty($cap[$val])){
          foreach($cap[$val] as $value){
              if(!current_user_can($value)){ 
                  $flag=0;
                  break;
                }
            }
        }
        if(empty($flag)){
          wp_redirect(home_url());
          exit;
        }
    }

    function hide_edit_in_admin($args){
        $val = vibe_get_option('wp_admin_access');
        $cap = apply_filters('wplms_admin_access_capabilities',array(1=>array('edit_posts'),2=>array('manage_options')));
        $flag = 1;
        if(!empty($val) && !empty($cap[$val])){
          foreach($cap[$val] as $value){
              if(!current_user_can($value)){ 
                  $flag=0;
                  break;
                }
            }
        }

        if(empty($flag)){
          unset($args[0]);
        }

        return $args;
    }
    /*============================================*/
    /*=========  REGISTER ADMIN SCRIPTS  =========*/
    /*============================================*/
    function wplms_enqueue_admin($hook){ 
      
      if(in_array($hook,array('post.php','post-new.php','edit.php','toplevel_page_wplms_options','dashboard_page_wplms-install','lms_page_lms-settings','lms_page_lms-stats'))){
        $flag = 0;
        if(isset($_GET['post_type'])){
          if(in_array($_GET['post_type'],array('course','quiz','question','unit','wplms-assignment','news','ajde_events'))){
              $flag = 1;
          }
        } else if(isset($_GET['post'])){
          $post_type = get_post_type($_GET['post']);
          if(in_array($post_type,array('course','quiz','question','unit','wplms-assignment','news','ajde_events'))){
              $flag = 1;
          }
        }else{
          $flag = 1;
        }
        if($flag){
          wp_deregister_script('woocomposer-admin-script');  
          wp_deregister_script('select2');
          wp_enqueue_style( 'select2', VIBE_URL .'/assets/css/old_files/select2.min.css',array(),WPLMS_VERSION);
          wp_enqueue_script( 'select2', VIBE_URL .'/assets/js/old_files/select2.min.js',array(),WPLMS_VERSION);
          wp_enqueue_style( 'admin-css', VIBE_URL .'/assets/css/old_files/admin.css' ,array(),WPLMS_VERSION);
          wp_enqueue_script( 'admin-js', VIBE_URL .'/assets/js/old_files/vibe_admin.js',array('jquery','jquery-ui-datepicker'),WPLMS_VERSION);
        }
      }

      if($hook == 'nav-menus.php'){
          wp_enqueue_style( 'wplms-menu-css', VIBE_URL .'/includes/menu/css/admin_menu.css' ,array(),WPLMS_VERSION);
          wp_enqueue_script( 'wplms-menu-js', VIBE_URL .'/includes/menu/js/admin_vibe_menu.js',array(),WPLMS_VERSION);
      }

      if($hook == 'edit-tags.php' && $_GET['taxonomy'] == 'course-cat'){
        wp_enqueue_media();
      }
    }
    
    /*============================================*/
    /*========  REGISTER FRONT SCRIPTS  ==========*/
    /*============================================*/
    //ENQUEUE SCRIPTS TO HEAD
    function wplms_enqueue_head() {
        global $vibe_options;
        
        if( ! is_admin() ){


         /*=== Enqueing Google Web Fonts =====*/
         $font_string='';
         $google_fonts=vibe_get_option('google_fonts');
         if(!empty($google_fonts) && is_array($google_fonts)){
            $font_weights = array();
            $font_subsets = array();
            foreach($google_fonts as $font){

              $font_var = explode('-',$font);

              if(!empty($font_weights[$font_var[0]]) && is_array($font_weights[$font_var[0]]) && isset($font_var[1])){
                if(!in_array($font_var[1],$font_weights[$font_var[0]]))
                  $font_weights[$font_var[0]][] = $font_var[1];
              }else{
                if(isset($font_var[1]))
                  $font_weights[$font_var[0]] = array($font_var[1]);
              }
              if(isset($font_var[2]))
              $font_subsets[] = $font_var[2];
            }

            if(!empty($font_weights)){
              foreach($font_weights as $font_name => $font_weight){
                $strings[$font_name] = implode(',',$font_weight);
              }
            }

            if(isset($strings) && is_array($strings)){
              foreach($strings as $key => $str){
                if($key){
                  $key = str_replace(' ','+',$key);
                  $font_string .= $key.':'.$str.'|';
                }
              }
              $font_string = substr($font_string, 0, -1);
            }

            if(isset($font_subsets) && is_array($font_subsets)){
              $font_subsets = array_unique($font_subsets);
              if(!empty($font_subsets)){
                $font_string.='&subsets='.implode(',',$font_subsets);
              }  
            }
            
            if(!empty($font_string)){
              $query_args = apply_filters('vibe_font_query_args',array(
              'family' => $font_string
              ));
              wp_enqueue_style('google-webfonts',
              esc_url(add_query_arg($query_args, "//fonts.googleapis.com/css" )),
              array(), null);
            }

         } // End Google Fonts

          if ( in_array( 'bbpress/bbpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'bbpress/bbpress.php'))){
            wp_enqueue_style( 'bbpress-css', VIBE_URL .'/assets/css/bbpress.min.css',array(),WPLMS_VERSION);
          }          

         if ( is_rtl() ){
            wp_enqueue_style( 'rtl-css', VIBE_URL .'/assets/css/old_files/rtl.css',array('wplms-style'),WPLMS_VERSION);
          }

         if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'woocommerce/woocommerce.php')) || function_exists('WC')) {     
            wp_enqueue_style( 'woocommerce-css', VIBE_URL .'/assets/css/woocommerce.min.css',array(),WPLMS_VERSION );
          }
         
          if ( in_array( 'sfwd-lms/sfwd_lms.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )){
            wp_enqueue_style( 'learndash-css', VIBE_URL .'/css/old_files/learndash.css',array(),WPLMS_VERSION);
          }

          wp_deregister_style( 'font-awesome');

          wp_enqueue_style('wplms-style', VIBE_URL.'/assets/css/style.min.css',array(),WPLMS_VERSION);
          $theme_skin = vibe_get_customizer('theme_skin');
          if(!empty($theme_skin)){
            wp_enqueue_style($theme_skin, VIBE_URL.'/assets/css/'.$theme_skin.'.min.css',array(),WPLMS_VERSION);  
          }
        }
    } 
    function wplms_enqueue_footer() {
        if(!is_admin()){ 
          
          if(function_exists('bp_is_active')){
            wp_enqueue_script( 'buddypress-js', VIBE_URL .'/assets/js/old_files/buddypress.js',array('jquery'),WPLMS_VERSION);
          }


          $params = array(
              'accepted'            => __( 'Accepted', 'vibe' ),
              'close'               => __( 'Close', 'vibe' ),
              'comments'            => __( 'comments', 'vibe' ),
              'leave_group_confirm' => __( 'Are you sure you want to leave this group?', 'vibe' ),
              'mark_as_fav'          => __( 'Favorite', 'vibe' ),
              'my_favs'             => __( 'My Favorites', 'vibe' ),
              'rejected'            => __( 'Rejected', 'vibe' ),
              'remove_fav'          => __( 'Remove Favorite', 'vibe' ),
              'show_all'            => __( 'Show all', 'vibe' ),
              'show_all_comments'   => __( 'Show all comments for this thread', 'vibe' ),
              'show_x_comments'     => __( 'Show all %d comments', 'vibe' ),
              'unsaved_changes'     => __( 'Your profile has unsaved changes. If you leave the page, the changes will be lost.', 'vibe' ),
              'view'                => __( 'View', 'vibe' ),
              'too_short'           => __( 'Too short', 'vibe' ),
              'weak'                => __( 'Weak', 'vibe' ),
              'good'                => __( 'Good', 'vibe' ),
              'strong'              => __( 'Strong', 'vibe' ),
          );
          // localise
          wp_localize_script( 'buddypress-js', 'BP_DTheme', $params );
          
          wp_enqueue_script( 'wplms', VIBE_URL.'/assets/js/wplms.min.js',array(),WPLMS_VERSION);

          $wplms_strings = array(
          'more'=>__('More','vibe'),
          'view_more'=>__('View More','vibe'),
          'menu'=>__('Menu','vibe'),
          'wplms_woocommerce_validate' => __( 'Please fill in all the required fields (indicated by *)','vibe' ),
          'open_menu'=>__('Open/Close Menu','vibe'),
          'invalid_mail'=>__('Invalid email entered !','vibe'),
          'signon_security'=>wp_create_nonce('wplms_signon'),
          );
          wp_localize_script('wplms','wplms',$wplms_strings);
        }
    } 
    function wplms_force_enqueue_head(){
      $page_id = vibe_get_option('take_course_page');
      if(is_page($page_id) || is_singular('quiz')){
        wp_enqueue_style( 'wp-mediaelement' );
        wp_enqueue_script( 'wp-mediaelement' );
      }
    }

    /*============================================*/
    /*===========  REGISTER MENUS  ============*/
    /*============================================*/
    function register_vibe_menus() {
      register_nav_menus(
          array(
              'top-menu' => __( 'Top Menu','vibe' ),
              'main-menu' => __( 'Main Menu','vibe' ),
              'mobile-menu' => __( 'Mobile Menu','vibe' ),
              'footer-menu' => __( 'Footer Menu','vibe' )
             )
            );
      }

      /*============================================*/
      /*===========  REGISTER SIDEBARS  ============*/
      /*============================================*/
      function wplms_register_sidebars(){
      if(function_exists('register_sidebar')){     
          register_sidebar( array(
          'name' => 'MainSidebar',
          'id' => 'mainsidebar',
          'before_widget' => '<div id="%1$s" class="widget %2$s">',
          'after_widget' => '</div>',
          'before_title' => '<h4 class="widget_title"><span>',
          'after_title' => '</span></h4>',
              'description'   => __('This is the global default widget area/sidebar for pages, posts, categories, tags and archive pages','vibe')
        ) );
          register_sidebar( array(
              'name' => 'BBpress Sidebar',
              'id' => 'bbpress',
              'before_widget' => '<div class="widget">',
              'after_widget' => '</div>',
              'before_title' => '<h4 class="widget_title"><span>',
              'after_title' => '</span></h4>',
              'description'   => __('This is the default widget area/sidebar shown in course pages and free units','vibe')
          ) );
          register_sidebar( array(
              'name' => 'Course Sidebar',
              'id' => 'coursesidebar',
              'before_widget' => '<div class="widget">',
              'after_widget' => '</div>',
              'before_title' => '<h4 class="widget_title">',
              'after_title' => '</h4>',
              'description'   => __('This is the default widget area/sidebar shown in course pages and free units','vibe')
          ) );
          register_sidebar( array(
            'name' => 'SearchSidebar',
            'id' => 'searchsidebar',
            'before_widget' => '<div class="widget">',
            'after_widget' => '</div>',
            'before_title' => '<h4 class="widget_title"><span>',
            'after_title' => '</span></h4>',
                'description'   => __('This is the widget area/sidebar shown in search results page.','vibe')
          ) );

          register_sidebar( array(
              'name' => 'Shop',
              'id' => 'shopsidebar',
              'before_widget' => '<div class="widget">',
              'after_widget' => '</div>',
              'before_title' => '<h4 class="widget_title"><span>',
              'after_title' => '</span></h4>',
              'description'   => __('This is the widget area/sidebar shown in the shop page ','vibe')
          ) );
          
          register_sidebar( array(
              'name' => 'Product',
              'id' => 'productsidebar',
              'before_widget' => '<div class="widget">',
              'after_widget' => '</div>',
              'before_title' => '<h4 class="widget_title"><span>',
              'after_title' => '</span></h4>',
              'description'   => __('This is the default widget area/sidebar shown in single product pages and product categories','vibe')
          ) );

           register_sidebar( array(
              'name' => 'Buddypress',
              'id' => 'buddypress',
              'before_widget' => '<div class="widget">',
              'after_widget' => '</div>',
              'before_title' => '<h4 class="widget_title"><span>',
              'after_title' => '</span></h4>',
              'description'   => __('This is the default widget area/sidebar shown in buddypress pages like : All Activity, All Groups, All members, All courses, All blogs','vibe')
          ) );

           register_sidebar( array(
              'name' => 'Checkout',
              'id' => 'checkout',
              'before_widget' => '<div class="widget">',
              'after_widget' => '</div>',
              'before_title' => '<h4 class="widget_title"><span>',
              'after_title' => '</span></h4>',
              'description'   => __('This is the default widget area/sidebar shown in Checkout pages below coupons.','vibe')
          ) );

        $topspan=vibe_get_option('top_footer_columns');
        if(!isset($topspan)) {$topspan = 'col-md-3 col-sm-6';}
        register_sidebar( array(
            'name' => 'Top Footer Sidebar',
            'id' => 'topfootersidebar',
            'before_widget' => '<div id="%1$s" class="'.$topspan.'"><div class="footerwidget %2$s">',
            'after_widget' => '</div></div>',
            'before_title' => '<h4 class="footertitle"><span>',
            'after_title' => '</span></h4>',
            'description'   => __('Top Footer widget area / sidebar','vibe')
        ));

        $bottomspan=vibe_get_option('bottom_footer_columns');
        if(!isset($bottomspan)) {$bottomspan = 'col-md-4 col-md-4';}
        register_sidebar( array(
          'name' => 'Bottom Footer Sidebar',
          'id' => 'bottomfootersidebar',
          'before_widget' => '<div id="%1$s" class="'.$bottomspan.'"><div class="footerwidget %2$s">',
          'after_widget' => '</div></div>',
          'before_title' => '<h4 class="footertitle"><span>',
          'after_title' => '</span></h4>',
          'description'   => __('Bottom Footer widget area / sidebar','vibe')
        ));

           $sidebars=vibe_get_option('sidebars');
          if(isset($sidebars) && is_array($sidebars)){ 
              foreach($sidebars as $sidebar){ 
                  register_sidebar( array(
              'name' => $sidebar,
              'id' => $sidebar,
              'before_widget' => '<div class="widget"><div class="inside">',
              'after_widget' => '</div></div>',
              'before_title' => '<h4 class="widgettitle"><span>',
              'after_title' => '</span></h4>',
                  'description'   => __('Custom sidebar, created from Sidebar Manager','vibe')
            ) );
            }
          }
        }
    } 

    /*==== Reset Google Fonts ====*/
    function storegoogle_webfonts(){
        $google_webfonts=get_option('google_webfonts');
            if(!isset($google_webfonts) || $google_webfonts ==''){
                $api_key = vibe_get_option('google_fonts_api_key');
                if(isset($api_key) && $api_key){
                  //Call jSon format save in $fonts
                  $url='https://www.googleapis.com/webfonts/v1/webfonts?key='.$api_key;       
                  $fonts = wp_remote_retrieve_body( wp_remote_get($url));

                  add_option( 'google_webfonts', "$fonts",'', 'no');
                }else{
                  $url='http://api.vibethemes.com/fonts.php';       
                  $fonts = wp_remote_retrieve_body( wp_remote_get($url));
                  $fonts=(string)$fonts;
                  add_option( 'google_webfonts', "$fonts",'', 'no');
                }
            }
    } 
}

WPLMS_Register::init();


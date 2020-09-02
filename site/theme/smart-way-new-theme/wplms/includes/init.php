<?php
/**
 * Initialization functions for WPLMS
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     2.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;

class WPLMS_Init{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Init();

        return self::$instance;
    }

    private function __construct(){

        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'woocommerce' );
        add_theme_support( 'wc-product-gallery-lightbox' );
        

        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'buddypress' );
        add_theme_support( 'bp-default-responsive' );
        add_theme_support( 'html5', array( 'gallery', 'caption' ) );
        add_theme_support( 'post-formats', array( 'aside','image','quote','status','video','audio','chat','gallery' ) );

        add_post_type_support( 'course', 'front-end-editor' );
        add_post_type_support( 'unit', 'front-end-editor' );
        add_post_type_support( 'quiz', 'front-end-editor' );
        add_post_type_support( 'question', 'front-end-editor' );
        add_post_type_support( 'wplms-event', 'front-end-editor' );
        add_post_type_support( 'wplms-assignment', 'front-end-editor' );
        add_post_type_support( 'testimonials', 'front-end-editor' );
        add_post_type_support( 'popups', 'front-end-editor' );
        add_post_type_support( 'news', 'front-end-editor' );
        add_post_type_support( 'topic', 'front-end-editor' );
        add_post_type_support( 'reply', 'front-end-editor' );

        add_post_type_support( 'course', 'buddypress-activity' );
        add_post_type_support( 'unit', 'buddypress-activity' );
        add_post_type_support( 'quiz', 'buddypress-activity' );
        add_post_type_support( 'question', 'buddypress-activity' );
        add_post_type_support( 'wplms-event', 'buddypress-activity' );
        add_post_type_support( 'wplms-assignment', 'buddypress-activity' );
        add_post_type_support( 'news', 'buddypress-activity' );

        add_action( 'after_setup_theme', array($this,'translate_theme' ));
        add_filter('body_class',array($this,'body_class'));

        add_action( 'vc_before_init', array($this,'wplms_vcSetAsTheme' ));
        $defaults = array(
            'default-color'          => '',
            'default-image'          => '',
            'default-repeat'         => '',
            'default-position-x'     => '',
            'wp-head-callback'       => 'vibe_custom_background_cb',
            'admin-head-callback'    => '',
            'admin-preview-callback' => 'vibe_custom_background_cb'
        );
        add_theme_support( 'custom-background', $defaults );

        
        add_action('layerslider_ready', array($this,'my_layerslider_overrides'));
        add_filter('excerpt_length', array($this,'new_excerpt_length'));
        add_filter('get_the_excerpt',  array($this,'trim_excerpt'));
        add_filter( 'wp_title', array($this,'vibe_wp_title'), 10, 2 );
        add_action('admin_notices', array($this,'learndash_admin_notice'));
        add_action( 'login_enqueue_scripts',array($this, 'vibe_login_logo' ));
        add_action('init',array($this,'wplms_disable_layerslider_notification'));
        add_action('comment_post',array($this, 'wplms_allow_pres'));

        add_action('template_redirect',array($this,'before_directory'),1);
        add_filter('bp_user_can_create_groups',array($this,'create_access'));
        add_action( 'bp_setup_nav', array($this,'manage_access'), 99 );

        add_filter('bp_after_has_activities_parse_args',array($this,'course_activities'));

        add_action( 'bp_init', array($this,'activity_filter_options') );
        add_filter( 'bp_activity_set_course_scope_args', array($this,'filter_activity_scope'), 10, 2 );
        add_filter('bp_activity_custom_update',array($this,'bp_activity_course_update'),10,3);
        add_action('bp_activity_post_form_options',array($this,'activity_form_options'));

        add_filter('bp_course_wplms_filters',array($this,'course_category'));

        remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cart_totals', 10 );
        add_action( 'woocommerce_cart_totals', 'woocommerce_cart_totals', 10 );
    }

    function body_class($classes){

        $layout = self::option('layout');
        if(!empty($layout))
            $classes[] = $layout;

        $theme_style = $this->get_cutomizer('theme_style');
        if(!empty($theme_style)){
            if($theme_style == 'boxed'){
                $classes[] = 'boxed';
            }
        }
        $directory_layout = $this->get_cutomizer('directory_layout');
        if(!empty($directory_layout)){
            $classes[] = $this->customizer['directory_layout'];
        }

        $group_layout = $this->get_cutomizer('group_layout');
        if(!empty($group_layout)){
            $classes[] = $this->customizer['group_layout'];
        }
        $profile_layout = $this->get_cutomizer('profile_layout');
        if(!empty($profile_layout)){
            $classes[] = $this->customizer['profile_layout'];
        }
        $course_layout = $this->get_cutomizer('course_layout');
        if(!empty($course_layout)){
            $classes[] = $this->customizer['course_layout'];
        }

        $theme_skin = $this->get_cutomizer('theme_skin');
        if(!empty($theme_skin)){
            $classes[] = $this->customizer['theme_skin'];
        }

        if(!is_user_logged_in()){
            $classes[] = 'logged-out';
        }
        return $classes;
    }

    function my_layerslider_overrides() {
        $GLOBALS['lsAutoUpdateBox'] = false;
    }

    function wplms_vcSetAsTheme() {
        if(function_exists('vc_set_as_theme'))
            vc_set_as_theme();
    }

    function trim_excerpt($text) {
        $text = str_replace('[', '', $text);
         $text = str_replace(']', '', $text);
         return $text;
    }

    function new_excerpt_length($length) {
        $excerpt_length=vibe_get_option('excerpt_length');
        if(isset($excerpt_length) && $excerpt_length){
            return $excerpt_length;
        }else
            return 20;
    }
    

    function translate_theme() {
        $locale = get_locale();

        $locale_file = get_stylesheet_directory() . "/languages/";
        $template_file = get_template_directory() . "/languages/";
        $global_file = WP_LANG_DIR . "/themes/wplms/";

        // Loco translate fix
        if ( file_exists( WP_LANG_DIR."/themes/vibe-".$locale.'.mo' ) ) { 
            load_theme_textdomain( 'vibe', WP_LANG_DIR."/themes/vibe-".$locale.'.mo' );
        }else if ( file_exists( $global_file.$locale.'.mo' ) ) {
            load_theme_textdomain( 'vibe', $global_file );
        }else if ( file_exists( $locale_file.$locale.'.mo' ) ) { 
            load_theme_textdomain( 'vibe', $locale_file );
        }else {
            load_theme_textdomain( 'vibe', $template_file );
        }
    }

    function option($field,$compare = null){
        $this->option = wp_cache_get('vibe_option','settings');
        if ( false === $this->option ) {
            $this->option=get_option(THEME_SHORT_NAME);
            wp_cache_set('vibe_option', $this->option,'settings', DAY_IN_SECONDS);
        }

        $return = isset($this->option[$field])?$this->option[$field]:NULL;
        if(isset($return)){
            if(isset($compare)){
                if($compare === $return){
                    return true;
                }else
                    return false;
            }
            return $return;
        }else
            return NULL;

    } 

    function get_cutomizer($field){
        
        if(empty($this->customizer))  
            $this->customizer = get_option('vibe_customizer');

        if(isset($this->customizer[$field]))
            return $this->customizer[$field];
        else
            return '';
    }

    function get_site_style(){
        
        if(empty($this->site_style))  
            $this->site_style = get_option('wplms_site_style');

        if(isset($this->site_style))
            return $this->site_style;
        else
            return '';
    }

    function get_header(){
        
        if(empty($this->customizer))            
            $this->customizer = get_option('vibe_customizer');

        if(empty($this->customizer))
            return;
        if(isset($this->customizer['header_style']))
            return $this->customizer['header_style'];
        else
            return;
    }

    function get_footer(){
        if(empty($this->customizer))
            $this->customizer = get_option('vibe_customizer');

        if(empty($this->customizer))
            return;
        if(isset($this->customizer['footer_style']))
            return $this->customizer['footer_style'];
        else
            return;
    }

    function get_login_style(){
        if(empty($this->customizer))
            $this->customizer = get_option('vibe_customizer');

        if(empty($this->customizer) || !isset($this->customizer['login_style']))
            return;
        
        return $this->customizer['login_style'];

    }
    

    function get_container_class(){
        $this->customizer = get_option('vibe_customizer');
        if(empty($this->customizer))
            return 'container';

        if(!empty($this->customizer) && !empty($this->customizer['theme_style']) && $this->customizer['theme_style'] == 'fluid'){
            return 'container-fluid';
        }else{
            return 'container';
        }
    }


    function vibe_wp_title( $title, $sep ) {
        global $paged, $page;
     
        if ( is_feed() ) {
            return $title;
        } // end if
     
        // Add the site name.
        $title .= get_bloginfo( 'name' );
     
        // Add the site description for the home/front page.
        $site_description = get_bloginfo( 'description', 'display' );
        if ( $site_description && ( is_home() || is_front_page() ) ) {
            $title = "$title $sep $site_description";
        } // end if
     
        // Add a page number if necessary.
        if ( $paged >= 2 || $page >= 2 ) {
            $title = sprintf( __( 'Page %s', 'vibe' ), max( $paged, $page ) ) . " $sep $title";
        } // end if
     
        return $title;
     
    } // end mayer_wp_title

    function learndash_admin_notice(){

        if ( in_array( 'sfwd-lms/sfwd_lms.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) &&
            ( in_array('vibe-course-module/loader.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) )) 
            || in_array('vibe-customtypes/vibe-customtypes.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) )) )) {     
            echo '<div class="error">

                    <h3><strong>'.__('LearnDash Active. You may disable following plugins to avoid duplicate functionality in the setup','vibe').'</strong></h3>
                    <p>'.__('Go to WP Admin -> Plugins -> Installed Plugins','vibe').'</p>
                    <ol>
                        <li>'.__('Deactivate Vibe Custom Types','vibe').'</li>
                        <li>'.__('Deactivate Vibe Course Module','vibe').'</li>
                    </ol>
                </div>';
        }
    }

    function vibe_login_logo() {    //Copy this function to customize WP Admin login screen
        
        $url=vibe_get_option('logo');

        $light_color = '#333';
        $light_bg = '#fff';
        $dark_color = '#fff';
        $dark_bg = '#313b3d';
        $primary_bg = '#009dd8';
        $primary_color = '#fff';
        $customizer = array();
        $customizer=get_option('vibe_customizer');
        if(isset($customizer) && is_array($customizer) && count($customizer)){
            if(isset($customizer['login_dark'])){
                $dark_bg = $customizer['login_dark'];
            }
            if(isset($customizer['login_dark_color'])){
                $dark_color = $customizer['login_dark_color'];
            }
            if(isset($customizer['login_light'])){
                $light_bg = $customizer['login_light'];

            }
            if(isset($customizer['login_light_color'])){
                $light_color = $customizer['login_light_color'];
            }
            if(isset($customizer['primary_bg'])){
                $primary_bg = $customizer['primary_bg'];
            }
            if(isset($customizer['primary_color'])){
                $primary_color = $customizer['primary_color'];
            }
        }

        if(!isset($url) || $url == '' ){
            $url = get_stylesheet_directory_uri().'/assets/images/logo.png';
        }
        ?>
        <style type="text/css">
            <?php
            if(filter_var($url, FILTER_VALIDATE_URL)){
                ?>
                body.login div#login h1 a {
                    background-image: url(<?php echo $url; ?>);
                }
                <?php 
            }
            ?>
            .bp_social_connect{text-align:center;}
            .bp_social_connect>a{display:inline-block;float:none !important;text-decoration:none;}
            .bp_social_connect>a:before{content:'' !important;}
            .login h1 a{
                width:160px;
                background-size:100%;
            }
            html,body.login {
                background: <?php echo $dark_bg; ?>;
                }
            body:before{
                content:'';
                background:rgba(0,0,0,0.1);
                width:100%;
                height:10px;
                position:absolute;
                top:0;
                left:0;
            }    
            body.login form#loginform label{
                color: <?php echo $dark_color; ?>;
                font-size:11px;
                text-transform: uppercase;
                font-weight:600;
                opacity: 0.8;
            }
            body.login input[type=checkbox]:checked:before{color: <?php echo $dark_color; ?>;}
            body.login.wp-core-ui .button-primary{background:<?php echo $primary_bg; ?>;border:none;box-shadow:none;text-shadow:none;color:<?php echo $primary_color; ?>;}
            body.login form#loginform {
                background:<?php echo $light_bg; ?>;
                box-shadow:none;
                border-radius:2px;
                margin:0;
            }    
            body.login form#loginform label{color:<?php echo $light_color; ?>;;}
            body.login form#loginform .input,
            body.login form#loginform input[type=text], 
            body.login form#loginform input[type=checkbox]{
                background: <?php echo $dark_bg; ?>;
                border-color: rgba(255,255,255,0.1);
                border-radius: 2px;
                color:<?php echo $dark_color; ?>;
            }
            body.login #nav a, body.login #backtoblog a{
                color: <?php echo $dark_color; ?>;
                text-transform: uppercase;
                font-size: 11px;
                opacity: 0.8;
            }
            body.login #nav a:hover, body.login #backtoblog a:hover{color:<?php echo $primary_bg; ?>}
            div.error,body.login #login_error{border-radius:2px;}
            <?php
            $wp_login_screen = vibe_get_option('wp_login_screen');
            echo $wp_login_screen;
            ?>
        </style>
        <?php 
        }
    

        function wplms_disable_layerslider_notification(){
            if(defined('LS_PLUGIN_BASE')){
               if(!get_option('layerslider-authorized-site', null)) {
                    remove_action('after_plugin_row_'.LS_PLUGIN_BASE, 'layerslider_plugins_purchase_notice', 10, 3 );
                } 
            }
            if (class_exists('Vc_License')) { 
                $vc=new Vc_License;
                remove_action( 'admin_notices', array( $vc, 'adminNoticeLicenseActivation' ) );
            }
        }

        function wplms_allow_pres() {
            global $allowedtags;
            $allowedtags['pre'] = array('class'=>array());
            $allowedtags["ol"] = array();
            $allowedtags["ul"] = array();
            $allowedtags["li"] = array();
            $allowedtags["h2"] = array();
            $allowedtags["h3"] = array();
            $allowedtags["h4"] = array();
            $allowedtags["h5"] = array();
            $allowedtags["h6"] = array();
            $allowedtags["span"] = array( "style" => array() );
        }

    function bp_page_id($page){
        if(empty($this->bp_pages)){
            $this->bp_pages = get_option('bp-pages');
        }
        if(function_exists('icl_object_id')){
            $this->bp_pages[$page] = icl_object_id($this->bp_pages[$page], 'page', true);
        }
        return $this->bp_pages[$page];
    }

    function before_directory(){

        if(!is_page())
            return;

        if(empty($this->bp_pages)){
            $this->bp_pages = get_option('bp-pages');
        }
        global $post;

        if(empty($this->bp_pages)) // Avoid unnecessary warning.
            return;

        if(!in_array($post->ID,$this->bp_pages))
            return;

        $flag=1;
        $component = array_search($post->ID, $this->bp_pages);
        
        
        $check = $this->option($component."_view");
       
        if(!empty($check)){
            $flag = 0;
            switch($check){
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
            $id=$this->option($component."_redirect");
            if(!empty($id))
                wp_redirect(get_permalink($id));
            exit();
        }
    }
    
    function manage_access(){
        
        if(!bp_is_user() || bp_is_my_profile())
            return;

        $check = $this->option('single_member_controls');
        $flag = 0;
        switch($check){
            case 1:
                if(!is_user_logged_in()){$flag = 1;}
            break;
            case 2:
                if(!current_user_can('edit_posts')){$flag = 1;} 
            break;
            case 3:
                if(!current_user_can('manage_options')){$flag = 1;} 
            break;
        }

        if($flag){
            //Disable all components
            global $bp;
            $components = $bp->loaded_components;
            unset($components['profile']);
            foreach($components as $component => $enabled){
                bp_core_remove_nav_item( $component );        
            }
            add_action('wp_footer',array($this,'hide_bp_nav'));
        }
    }
    function hide_bp_nav(){
        ?>
        <style>.bp-user #item-nav,.bp-user .item-list-tabs#subnav{display:none !important;}</style>
        <?php
    }
    function create_access($can){

        $capability=$this->option('group_create');
        if(!empty($capability)){
            switch($capability){
                case 1: 
                    return true;
                break;
                case 2: 
                    return true;
                break;
                case 3:
                    return true;
                break;
            }
            return false;
        }

        return $can;
    }

    function activity_filter_options() {
        
        $dropdowns = apply_filters( 'vibe_projects_activity_filter_locations', array(
            'bp_activity_filter_options',
            'bp_member_activity_filter_options',
            'bp_course_activity_filter_options',
        ) );
        foreach( $dropdowns as $hook ) {
            add_action( $hook, array($this,'activity_filters' ));
        }
    }
    function activity_filters() {
        $activity_filters =array(
            'course_announcement' => __( 'Course Announcement', 'vibe' ),
            'course_news' => __( 'Course News', 'vibe' ),
            'subscribe_course' => __( 'Course subscribed', 'vibe' ),
            'start_course' => __( 'Course started', 'vibe' ),
            'submit_course' => __( 'Course submitted', 'vibe' ),
            'course_evaluated' => __( 'Course Evaluated', 'vibe' ),
            'reset_course' => __( 'Course Reset', 'vibe' ),
            'retake_course' => __( 'Course Retake', 'vibe' ),
            'student_badge' => __( 'Student Badge', 'vibe' ),
            'student_certificate' => __( 'Student Certificate', 'vibe' ),
            'review_course' => __( 'Course reviewed', 'vibe' ),
            'remove_from_course' => __( 'Student Removed', 'vibe' ),
            'course_code' => __( 'Course Code applied', 'vibe' ),
            'renew_course' => __( 'Course subscription renewed', 'vibe' ),
            'unit_complete' => __( 'Unit Completed', 'vibe' ),
            'unit_comment' => __( 'Unit comment added', 'vibe' ), 
            'start_quiz' => __( 'Start Quiz', 'vibe' ),
            'submit_quiz' => __( 'Submit Quiz', 'vibe' ),
            'quiz_evaluated' => __( 'Quiz evaluated', 'vibe' ),
            'assignment_started' => __( 'Start Assignment', 'vibe' ),
            'assignment_submitted' => __( 'Submit Assignment', 'vibe' ),
            'evaluate_assignment' => __( 'Assignment Evaluated', 'vibe' ),
        );


        foreach($activity_filters as $key => $filters){
            echo '<option value="'.$key.'">'.$filters.'</option>';
        }
    }

    /*
    * Load Project Activities on Project Activity tab
    * Requires Singular Project
    */
   
    function course_activities($args){
        if(is_singular('course')){
            $args['object'] = 'course';
            $args['primary_id'] = get_the_ID();
        }
        if(strpos($args['scope'],'course') !== false){ 
            if(strpos($args['scope'],'personal') !== false){
                preg_match("/course_personal_([0-9]+)/", $args['scope'],$matches); 
                $args['scope'] = 'personal';
                $args['object'] = 'course';
                $args['user_id'] = get_current_user_id();
                $args['primary_id'] = $matches[1];
            }else{
                preg_match("/course_([0-9]+)/", $args['scope'],$matches); 
                $args['scope'] = '';
                $args['object'] = 'course';
                $args['primary_id'] = $matches[1];
            }
        }

        return $args;
    }

    function filter_activity_scope( $retval = array(), $filter = array() ) {
        global $post;
        // Determine the user_id
        if ( ! empty( $filter['user_id'] ) ) {
            $user_id = $filter['user_id'];
        } else {
            $user_id = bp_displayed_user_id()
                ? bp_displayed_user_id()
                : bp_loggedin_user_id();
        }

        // Determine groups of user
        $groups = groups_get_user_groups( $user_id );
        if ( empty( $groups['groups'] ) ) {
            $groups = array( 'groups' => 0 );
        }

        // Should we show all items regardless of sitewide visibility?
        $show_hidden = array();
        if ( ! empty( $user_id ) && ( $user_id !== bp_loggedin_user_id() ) ) {
            $show_hidden = array(
                'column' => 'hide_sitewide',
                'value'  => 0
            );
        }

        $retval = array(
            'relation' => 'AND',
            array(
                'relation' => 'AND',
                array(
                    'column' => 'component',
                    'value'  => 'course'
                ), 
                array(
                    'column'  => 'item_id',
                    'compare' => 'IN',
                    'value'   => $post->ID
                ),
            ),
            $show_hidden,

            // overrides
            'override' => array(
                'filter'      => array( 'user_id' => 0 ),
                'show_hidden' => true
            ),
        );

        return $retval;
    }

    function bp_activity_course_update($object,$item_id,$content){
        global $bp;

        $activity_id =  bp_course_record_activity(array(
                'action' => sprintf(__('%s posted an update in the course %s','vibe'),bp_core_get_userlink($bp->loggedin_user->id),'<a href="'.get_permalink($item_id).'">'.get_the_title($item_id).'</a>'),
                'content' => $content,
                'primary_link' => get_permalink($item_id),
                'item_id' => $item_id,
                'type' => $object
            ));
        return $activity_id;
    }

    
    function activity_form_options(){
        if(!is_singular('course'))
            return;

        $activity_type = apply_filters('bp_course_activity_update',array(
            'course_update'=>__('Update','vibe'),
            'course_news'=>__('News','vibe'),
            'course_announcement'=>__('Announcement','vibe'),
            ));
        ?>
            <select id="whats-new-post-object">
                <?php
                    foreach($activity_type as $key => $option){
                        echo '<option value="'.$key.'">'.$option.'</option>';
                    }
                ?>
            </select>
            <input type="hidden" id="whats-new-post-in" name="whats-new-post-in" value="<?php the_ID(); ?>" />  
        <?php
    }



    function course_category($args){
        
        global $wp_query;
        if(is_tax()){
            $args['tax_query']=array();
            $args['tax_query']['relation'] = 'AND';
            $args['tax_query'][]=array(
                                'taxonomy' => 'course-cat',
                                'terms'    => array($wp_query->query_vars['course-cat']),
                                'field'    => 'slug',
                            );
        }
        return $args;
    }

    function better_comments($comment, $args, $depth) {
      $GLOBALS['comment'] = $comment;
     ?>
     <li <?php comment_class(); ?> id="li-comment-<?php comment_ID() ?>">
       <div id="comment-<?php comment_ID(); ?>" class="comment-body">
         <div class="comment-body-inner">
             <div class="comment-avatar">
               <?php echo get_avatar($comment, $size = '120', $default = ''); ?>
             </div><!-- END avatar -->
             <div class="comment-body-content">
               <div class="comment-meta">
                 <?php echo get_comment_author_link(); 
                       echo '<a href="'.htmlspecialchars( get_comment_link( $comment->comment_ID ) ) .'">'.sprintf(__('%1$s at %2$s','vibe'), get_comment_date(),  get_comment_time()).'</a>'; 
                       comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))); 
                       edit_comment_link(__('(Edit)','vibe'),'  ','');
                 ?>
               </div><!-- END comment-author vcard -->
               <?php if ($comment->comment_approved == '0') : ?>
                 <em><?php _e('Your comment is awaiting moderation.','vibe') ?></em>
                 <br />
               <?php endif; ?>
               <div class="comment-text">
               <?php comment_text(); ?>
               </div>
            </div> 
         </div>
       </div>
     </li>
     <?php
    }
}

WPLMS_Init::init();


function vibe_admin_url($url='/') {
    if (is_multisite()) {
        if  (is_super_admin())
            return network_admin_url($url);
    } else {
        return admin_url($url);
    }
}

function vibe_site_url($url='/',$location = null) {
    if (is_multisite()) {
        $link = home_url($url);
    } else {
        $link = site_url($url);
    }
    return apply_filters('wplms_site_link',$link,$location);
}

add_filter('wplms_logo_url','vibe_logo_url',10,2);
function vibe_logo_url($url='/',$location = null){
    
    $logo=vibe_get_option('logo'); 
    $alt = get_bloginfo('name');
    
    if(empty($logo)){
        $url = VIBE_URL.'/assets/images/logo.png';
    }else{
        $url = $logo;
    }


    if(!empty($location)){
        switch($location){
            case 'pagesidebar':
                // No changes
            break;
            case 'header':


                $header = vibe_get_customizer('header_style');
                $alt_logo=vibe_get_option('alt_logo');
                $mobile_logo = vibe_get_option('mobile_logo');

                if(!filter_var($url, FILTER_VALIDATE_URL)){
                    echo '<span id="header_logo">'.$url.'</span>';
                    if(!filter_var($alt_logo, FILTER_VALIDATE_URL)){
                        echo '<span id="header_alt_logo">'.$alt_logo.'</span>';
                    }else{
                        echo '<img id="header_alt_logo" alt="'.$alt.'" src="'.$alt_logo.'">';
                    }

                    if(!filter_var($mobile_logo, FILTER_VALIDATE_URL)){
                        echo '<span id="header_mobile_logo" class="hide">'.$mobile_logo.'</span>';
                    }else{
                        echo '<img id="header_mobile_logo" src="'.$mobile_logo.'" class="hide" alt="'.$alt.'">';
                    }
                    
                    return 0;
                }

                if(empty($alt_logo)){
                    $alt_logo = $url; 
                }
                if(empty($mobile_logo)){
                    $mobile_logo =$url; 
                }
                if(in_array($header,array('sleek','transparent','center','generic','app'))){
                    $url .='" id="header_logo" alt="'.$alt.'"><img id="header_mobile_logo" src="'.$mobile_logo.'" class="hide" alt="'.$alt.'"><img id="header_alt_logo" alt="'.$alt.'" src="'.$alt_logo;    
                }else{
                    if(!empty($alt_logo)) 
                        $url .= '" data-alt-logo="'.$alt_logo; 

                    if(!empty($mobile_logo)) 
                        $url .= '" id="header_logo" alt="'.$alt.'"><img id="header_mobile_logo" alt="'.$alt.'" src="'.$mobile_logo.'" class="hide';
                }
                
            break;
            case 'standard_header':
                $mobile_logo = vibe_get_option('mobile_logo');
                if(empty($mobile_logo)){
                    $mobile_logo = $url; 
                }
                 if(filter_var($mobile_logo, FILTER_VALIDATE_URL)){
                    $url .= '" id="header_logo" alt="'.$alt.'"><img id="header_mobile_logo" alt="'.$alt.'" src="'.$mobile_logo.'" class="hide';
                }else{
                    echo '<span id="header_mobile_logo" class="hide">'.$mobile_logo.'</span>';
                    return 0; 
                }
                    
            break;
            case 'footer':
                $footer_logo=vibe_get_option('footer_logo');
                if(!empty($footer_logo)) {
                    $url  = $footer_logo;
                }else{
                    echo $url;
                    return 0;
                }
            break;
            case 'headertop':
                $alt_logo=vibe_get_option('alt_logo');
                $mobile_logo = vibe_get_option('mobile_logo');

                if(filter_var($alt_logo, FILTER_VALIDATE_URL)){
                    $url = $alt_logo.'" id="header_logo" alt="'.$alt.'">';
                    if(filter_var($mobile_logo, FILTER_VALIDATE_URL)){
                        $url .='<img id="header_mobile_logo" alt="'.$alt.'" src="'.$mobile_logo.'" class="hide';
                    }else{
                        echo '<img src="'.$alt_logo.'" id="header_logo" alt="'.$alt.'"><span id="header_mobile_logo" class="hide">'.$mobile_logo.'</span>';
                        return 0;
                    }
                    
                }else{
                    echo '<span id="header_alt_logo" class="hide">'.$alt_logo.'</span>';
                    if(filter_var($mobile_logo, FILTER_VALIDATE_URL)){
                        echo '<img id="header_mobile_logo" alt="'.$alt.'" src="'.$mobile_logo.'" class="hide" />';
                    }else{
                        echo '<span id="header_mobile_logo" class="hide">'.$mobile_logo.'</span>';
                    }
                    return 0;
                }
            break;
        }
    }
    if(is_ssl()){
        if (substr($url, 0, 7) == "http://"){
            $url = str_replace('http','https',$url);
            $url = str_replace('httpss://','https://',$url); // Switch to remove unwanted changes
        }
    }
    return $url;
}


function count_user_posts_by_type( $userid, $post_type = 'post' ) {
    global $wpdb;

    $where = get_posts_by_author_sql( $post_type, true, $userid );
    $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} $where" );
    
    $count = apply_filters('wplms_count_user_posts_by_type', $count, $userid,$post_type );
    return $count;
}

if(!function_exists('vibe_get_option')){
    function vibe_get_option($field,$compare = NULL){    
        $wplms = WPLMS_Init::init();  
        return $wplms->option($field); 
    }
}

if(!function_exists('vibe_update_option')){
    function vibe_update_option($field,$value){    
        $wplms = get_option(THEME_SHORT_NAME);
        if(!empty($wplms)){
            $wplms[$field] = $value;
        }else{
            $wplms = array($field => $value);
        }
        update_option(THEME_SHORT_NAME,$wplms);        
        return; 
    }
}

if(!function_exists('vibe_get_customizer')){
    function vibe_get_customizer($field){
        $wplms = WPLMS_Init::init();  
        return $wplms->get_cutomizer($field); 
    }
}

if(!function_exists('vibe_get_site_style')){
    function vibe_get_site_style(){
        $wplms = WPLMS_Init::init();  
        return $wplms->get_site_style(); 
    }
}


if(!function_exists('vibe_update_customizer')){
    function vibe_update_customizer($field,$value){
        $wplms = WPLMS_Init::init();  
        if(!empty($wplms->customizer)){
            $wplms->customizer[$field] = $value;    
        }else{
            $wplms->customizer=array($field => $value);
        }
        
        update_option('vibe_customizer',$wplms->customizer);
        return; 
    }
}

if(!function_exists('vibe_get_container')){
function vibe_get_container(){
    $wplms = WPLMS_Init::init();  
    return $wplms->get_container_class(); 
    }
}
if(!function_exists('vibe_get_login_style')){
function vibe_get_login_style(){
    $wplms = WPLMS_Init::init();  
        return $wplms->get_login_style(); 
    }
}

if(!function_exists('vibe_get_header')){
function vibe_get_header(){
    $wplms = WPLMS_Init::init();  
    return $wplms->get_header(); 
    }
}

if(!function_exists('vibe_get_footer')){
function vibe_get_footer(){
    $wplms = WPLMS_Init::init();  
    return $wplms->get_footer(); 
    }
}

if(!function_exists('vibe_get_bp_page_id')){
    function vibe_get_bp_page_id($page){
        $wplms = WPLMS_Init::init();  
        return $wplms->bp_page_id($page);
    }
}

if(!function_exists('vibe_include_template')){
    function vibe_include_template( $path, $base = NULL ){

        $file_path = get_stylesheet_directory()."/templates/$path";

        if(!file_exists($file_path)){
            $file_path = get_template_directory()."/templates/$path";

            if(!file_exists($file_path)){

                if(!empty($base)){
                    

                    $custom_path = locate_template( array( $base ) );
                    if(!empty($custom_path))
                        load_template( $custom_path, true );
                    else
                        $file_path = get_template_directory()."/templates/$base";
                }
            }
        }
       
        if(file_exists($file_path)){
            include_once $file_path;     
        }
    }
}

if(!function_exists('vibe_load_template')){
    function vibe_load_template($path,$base = NULL){

        if(strpos($path, '.php') === false){
            $path .= '.php';
        }
        $file_path = get_stylesheet_directory()."/$path";
        if(!file_exists($file_path)){
            $file_path = get_template_directory()."/$path";
            if(!file_exists($file_path)){

                if(!empty($base)){
                    

                    $custom_path = locate_template( array( $base ) );
                    if(!empty($custom_path))
                        load_template( $custom_path, true );
                    else
                        $file_path = get_template_directory()."/$base";
                }
            }
        }
        
        if(file_exists($file_path)){
            include_once $file_path;     
        }
    }
}

    
if(!function_exists('getPostMeta')){
    function getPostMeta($postID,$count_key){
        $count = get_post_meta($postID, $count_key, true);
        if($count==''){
            delete_post_meta($postID, $count_key);
            add_post_meta($postID, $count_key, '0');
            return "0";
       }
       return $count;
    }
}


if(!function_exists('vibe_set_menu')){
    function vibe_set_menu(){
         echo '<p style="padding:20px 0 10px;color:#FFF;text-align:center;">'.__('Setup Menus in Admin Panel','vibe').'</p>';
    }
}





function vibe_custom_background_cb(){
    // $background is the saved custom image, or the default image.
    $background = set_url_scheme( get_background_image() );

    // $color is the saved custom color.
    // A default has to be specified in style.css. It will not be printed here.
    $color = get_theme_mod( 'background_color' );

    if ( ! $background && ! $color )
        return;

    $style = $color ? "background-color: #$color;" : '';

    if ( $background ) {
        $image = " background-image: url('$background');";

        $repeat = get_theme_mod( 'background_repeat', get_theme_support( 'custom-background', 'default-repeat' ) );
        if ( ! in_array( $repeat, array( 'no-repeat', 'repeat-x', 'repeat-y', 'repeat' ) ) )
            $repeat = 'repeat';
        $repeat = " background-repeat: $repeat;";

        $position = get_theme_mod( 'background_position_x', get_theme_support( 'custom-background', 'default-position-x' ) );
        if ( ! in_array( $position, array( 'center', 'right', 'left' ) ) )
            $position = 'left';
        $position = " background-position: top $position;";

        $attachment = get_theme_mod( 'background_attachment', get_theme_support( 'custom-background', 'default-attachment' ) );
        if ( ! in_array( $attachment, array( 'fixed', 'scroll' ) ) )
            $attachment = 'scroll';
        $attachment = " background-attachment: $attachment;";

        $style .= $image . $repeat . $position . $attachment;

        echo '
            <div id="background_fixed">
                <img src="'.$background.'" />
            </div>
            <style type="text/css">
                #background_fixed{position: fixed;top:0;left:0;width:200%;height:200%;}
            </style>
        ';
    }

}




// Auto plugin activation
require_once('plugin-activation.php');

add_action('tgmpa_register', 'register_required_plugins');

function register_required_plugins() {

    if ( in_array( 'sfwd-lms/sfwd_lms.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
        $force_activate = false;
    else
        $force_activate = true;

    $plugins = array(
        array(
            'name'                  => 'Buddypress', // The plugin name
            'slug'                  => 'buddypress', // The plugin slug (typically the folder name)
            'source'                => 'https://downloads.wordpress.org/plugin/buddypress.4.0.0.zip', // The plugin source
            'required'              => true, // If false, the plugin is only 'recommended' instead of required
            'version'               => '4.0.0', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => $force_activate, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
            'file'                  => 'buddypress/bp-loader.php',
        ),
        array(
            'name'                  => 'WooCommerce', // The plugin name
            'slug'                  => 'woocommerce', // The plugin slug (typically the folder name)
            'source'                => 'https://downloads.wordpress.org/plugin/woocommerce.3.5.1.zip', // The plugin source
            'required'              => false, // If false, the plugin is only 'recommended' instead of required
            'version'               => '3.5.1', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
            'file'                  => 'woocommerce/woocommerce.php',
        ),
        array(
            'name'                  => 'BBPress', // The plugin name
            'slug'                  => 'bbpress', // The plugin slug (typically the folder name)
            'source'                => 'https://downloads.wordpress.org/plugin/bbpress.2.5.14.zip', // The plugin source
            'required'              => false, // If false, the plugin is only 'recommended' instead of required
            'version'               => '2.5.14', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
            'file'                  => 'bbpress/bbpress.php',
        ),
        array(
            'name'                  => 'Buddydrive', // The plugin name
            'slug'                  => 'buddydrive', // The plugin slug (typically the folder name)
            'source'                => 'https://downloads.wordpress.org/plugin/buddydrive.2.0.0.zip', // The plugin source
            'required'              => false, // If false, the plugin is only 'recommended' instead of required
            'version'               => '2.0.0', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
            'file'                  => 'buddydrive/buddydrive.php',
        ),
        array(
            'name'                  => 'BP Social Connect', // The plugin name
            'slug'                  => 'bp-social-connect', // The plugin slug (typically the folder name)
            'source'                => 'https://downloads.wordpress.org/plugin/bp-social-connect.1.5.zip', // The plugin source
            'required'              => false, // If false, the plugin is only 'recommended' instead of required
            'version'               => '1.5.0', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
            'file'                  => 'bp-social-connect/bp-social-connect.php',
        ),
        array(
            'name'                  => 'Layer Slider', // The plugin name
            'slug'                  => 'LayerSlider', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/layersliderwp.zip', // The plugin source
            'required'              => false, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
            'file'                  => 'layerslider/layerslider.php',
        ),
        array(
            'name'                  => 'Revolution Slider', // The plugin name
            'slug'                  => 'revslider', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/revslider.zip', // The plugin source
            'required'              => false, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
            'file'                  => 'revslider/revslider.php',
        ),
        array(
            'name'                  => 'WP Visual Composer', // The plugin name
            'slug'                  => 'js_composer', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/js_composer.zip', // The plugin source
            'required'              => false, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
            'file'                  => 'js_composer/js_composer.php',
        ),
        array(
            'name'                  => 'EventON', // The plugin name
            'slug'                  => 'eventON', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/eventON.zip', // The plugin source
            'required'              => false, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
            'file'                  => 'eventON/eventon.php',
        ),
        array(
            'name'                  => 'WPLMS EventOn', // The plugin name
            'slug'                  => 'wplms-eventon', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/wplms-eventon.zip', // The plugin source
            'required'              => false, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
            'file'                  => 'wplms-eventon/wplms-eventon.php',
        ),
          array(
            'name'                  => 'Vibe Shortcodes', // The plugin name
            'slug'                  => 'vibe-shortcodes', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/vibe-shortcodes.zip', // The plugin source
            'required'              => true, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
        ),
        
          
        array(
            'name'                  => 'WPLMS Customizer', // The plugin name
            'slug'                  => 'wplms-customizer', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/wplms-customizer.zip', // The plugin source
            'required'              => false, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
        ),
    );
    
    if($force_activate){
        $plugins[]= array(
            'name'                  => 'Vibe Custom Types', // The plugin name
            'slug'                  => 'vibe-customtypes', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/vibe-customtypes.zip', // The plugin source
            'required'              => true, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
        );  
        $plugins[]= array(
            'name'                  => 'WPLMS Dashboard', // The plugin name
            'slug'                  => 'wplms-dashboard', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/wplms-dashboard.zip', // The plugin source
            'required'              => true, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
        );  
          $plugins[]=array(
            'name'                  => 'Vibe Course Module', // The plugin name
            'slug'                  => 'vibe-course-module', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/vibe-course-module.zip', // The plugin source
            'required'              => true, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => $force_activate, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
        );
        $plugins[]=array(
            'name'                  => 'WPLMS Front End', // The plugin name
            'slug'                  => 'wplms-front-end', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/wplms-front-end.zip', // The plugin source
            'required'              => false, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => $force_activate, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
        );
        $plugins[]=array(
            'name'                  => 'WPLMS Assignments', // The plugin name
            'slug'                  => 'wplms-assignments', // The plugin slug (typically the folder name)
            'source'                => VIBE_URL . '/plugins/wplms-assignments.zip', // The plugin source
            'required'              => true, // If false, the plugin is only 'recommended' instead of required
            'version'               => '', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
            'force_activation'      => false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
            'force_deactivation'    => true, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
            'external_url'          => '', // If set, overrides default API URL and points to an external URL
        );
    }

    $one_click_plugins  = get_option('wplms_one_click_required_plugins');

    if(!empty($one_click_plugins)){
        $plugins = array_merge($plugins,$one_click_plugins);
    }
    
    $plugins = apply_filters('wplms_required_plugins',$plugins);
    // Change this to your theme text domain, used for internationalising strings
    $theme_text_domain = 'vibe';

    /**
     * Array of configuration settings. Amend each line as needed.
     * If you want the default strings to be available under your own theme domain,
     * leave the strings uncommented.
     * Some of the strings are added into a sprintf, so see the comments at the
     * end of each line for what each argument will be.
     */
    $config = array(
        'domain'            =>'vibe',           // Text domain - likely want to be the same as your theme.
        'default_path'      => '',                          // Default absolute path to pre-packaged plugins
        'menu'              => 'install-required-plugins',  // Menu slug
        'has_notices'       => true,                        // Show admin notices or not
        'is_automatic'      => true,                        // Automatically activate plugins after installation or not
        'message'           => '',                          // Message to output right before the plugins table
        'strings'           => array(
            'page_title'                                => __( 'Install Required Plugins','vibe' ),
            'menu_title'                                => __( 'Install Plugins','vibe' ),
            'installing'                                => __( 'Installing Plugin: %s','vibe' ), // %1$s = plugin name
            'oops'                                      => __( 'Something went wrong with the plugin API.','vibe' ),
            'notice_can_install_required'               => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ), // %1$s = plugin name(s)
            'notice_can_install_recommended'            => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' ), // %1$s = plugin name(s)
            'notice_cannot_install'                     => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ), // %1$s = plugin name(s)
            'notice_can_activate_required'              => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
            'notice_can_activate_recommended'           => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), // %1$s = plugin name(s)
            'notice_cannot_activate'                    => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ), // %1$s = plugin name(s)
            'notice_ask_to_update'                      => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ), // %1$s = plugin name(s)
            'notice_cannot_update'                      => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ), // %1$s = plugin name(s)
            'install_link'                              => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
            'activate_link'                             => _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
            'return'                                    => __( 'Return to Required Plugins Installer','vibe' ),
            'plugin_activated'                          => __( 'Plugin activated successfully.','vibe' ),
            'complete'                                  => __( 'All plugins installed and activated successfully. %s','vibe' ), // %1$s = dashboard link
            'nag_type'                                  => 'updated' // Determines admin notice type - can only be 'updated' or 'error'
        )
    );

    tgmpa($plugins, $config);
}


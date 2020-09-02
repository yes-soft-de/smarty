<?php
/**
 * FILE: ajaxcalls.php 
 * Created on Oct 31, 2013 at 3:33:49 PM 
 * Author: Mr.Vibe 
 * Credits: www.VibeThemes.com
 * Project: WPLMS
 */

 if ( ! defined( 'ABSPATH' ) ) exit;

class Vibe_Shortcodes_Ajax_Calls{

    public static $instance;

    var $schedule;

    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Vibe_Shortcodes_Ajax_Calls();

        return self::$instance;
    }

    private function __construct(){
        add_action( 'wp_ajax_vibe_popup', array($this,'vibe_ajax_popup'));
        add_action( 'wp_ajax_nopriv_vibe_popup', array($this,'vibe_ajax_popup' ));
        
        //Ajax Handle Contact Form

        add_action('wp_ajax_vibe_form_submission', array($this,'vibe_form_submission'));
        add_action( 'wp_ajax_nopriv_vibe_form_submission', array($this,'vibe_form_submission' ));


        //Vibe Grid Infinite Scroll
        add_action( 'wp_ajax_grid_scroll', array($this,'vibe_grid_scroll' ));
        add_action( 'wp_ajax_nopriv_grid_scroll', array($this,'vibe_grid_scroll' ));

        // REGISTRATION FORMS
        add_action( 'wp_ajax_wplms_register_user', array($this,'wplms_register_user' ));
        add_action( 'wp_ajax_nopriv_wplms_register_user', array($this,'wplms_register_user' ));

    }

    function vibe_ajax_popup(){ 
        $id = stripslashes($_GET['id']);
        if(!is_numeric($id))
            die();

        $class = get_post_meta($id,'vibe_popup_class',true);
        $width = get_post_meta($id,'vibe_popup_width',true);
        $height = get_post_meta($id,'vibe_popup_height',true);

        $npopup = get_page($id);
        $post_content=apply_filters('the_content', $npopup->post_content);
        echo '<div class="popup_content '.$class.'" style="display:inline-block;width:'.$width.'px;max-height:'.$height.'px;">';
        echo '<style>.mfp-ajax-holder .mfp-content{max-width:'.$width.'px;} '.get_post_meta($id,'vibe_custom_css',true).'</style>';
        echo do_shortcode($post_content).'</div>';
        die();
    }

    function vibe_form_submission() {

        global $vibe_options;   

        $nonce = $_POST['security'];
        $to = stripslashes($_POST['to']);
        $subject = stripslashes($_POST['subject']);

        if ( ! wp_verify_nonce( $nonce, 'vibeform_security'.$to )){

            echo __("Unable to send message! Please try again later..","vibe-shortcodes");
            die();
        }

        /*if(seems_utf8($_POST['data'])){
            $data = json_decode(stripslashes(utf8_decode($_POST['data'])));
        }else{
            $data = json_decode(stripslashes($_POST['data']));
        }
        if(seems_utf8($_POST['label'])){
            $labels = json_decode(stripslashes(utf8_decode($_POST['label'])));
        }else{
            $labels = json_decode(stripslashes($_POST['label']));
        }*/
        $data = json_decode(stripslashes($_POST['data']));
        $labels = json_decode(stripslashes($_POST['label']));

        if(!isset($subject))
            $subject = __('Contact Form Submission','vibe-shortcodes');

        if(!isset($to)){
            $to = get_option('admin_email'); 
        }else if(strpos($to, ',')){
            $to = explode(',',$to);
        }

        for($i=1;$i<count($data);$i++){
            $message .= $labels[$i].' : '.$data[$i].' <br />';
            if (filter_var($data[$i], FILTER_VALIDATE_EMAIL)) {
                $reply_email=$data[$i];
            }
        }

        if( isset($_POST['attachment']) && !empty($_POST['attachment']) ){
            $attachment = $_POST['attachment'];
            $attachment_url = wp_get_attachment_url($attachment[1]);
            $message .= $attachment[0].' : '.$attachment_url.' <br />';
        }

        $bpargs = array(
            'tokens' => array('user.message' => $message),
        );
        $tax = bp_get_email_tax_type();
        $term = 'wplms_contact_form_email';
        if(!term_exists($term,$tax)){

            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "From:".get_bloginfo('name')."<$to>". "\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
            if(!isset($reply_email))
                $headers .= "Reply-To: ".$reply_email. "\r\n";

            $flag=wp_mail( $to, $subject, $message, $headers );
        }else{
            if(is_array($to)){
                foreach($to as $t){
                    $flag = bp_send_email( 'wplms_contact_form_email',$t, $bpargs );   
                }
            }else{
                $flag = bp_send_email( 'wplms_contact_form_email',$to, $bpargs );
            }
                
        }

        if ( $flag ) {
            echo "<span style='color:#0E7A00;'>".__("Message sent!","vibe-shortcodes")." </span>";
        }else{
            echo __("Unable to send message! Please try again later..","vibe-shortcodes");
        }

        die();
    }

    function vibe_grid_scroll(){ 
        $atts = json_decode(stripslashes($_POST['args']),true);
        $output ='';
        $paged = stripslashes($_POST['page']);
        $paged++;
            
        if(!isset($atts['post_ids']) || count($atts['post_ids']) > 0){
            if(isset($atts['term']) && isset($atts['taxonomy']) && $atts['term'] !='nothing_selected'){
               
            if(isset($atts['taxonomy']) && $atts['taxonomy']!=''){
                         if($atts['taxonomy'] == 'category'){
                             $atts['taxonomy']='category_name'; 
                             }
                          if($atts['taxonomy'] == 'tag'){
                             $atts['taxonomy']='tag_name'; 
                             }   
                     }
           
                          
          $query_args=array( 'post_type' => $atts['post_type'],$atts['taxonomy'] => $atts['term'],'post_status'=>'publish', 'posts_per_page' => $atts['grid_number'],'paged' => $paged);
          
        }else
           $query_args=array('post_type'=>$atts['post_type'],'post_status'=>'publish', 'posts_per_page' => $atts['grid_number'],'paged' => $paged);
        
        $style= '';
        if(isset($atts['masonry']) && $atts['masonry']){
            $style= 'style="width:'.$atts['column_width'].'px;"'; 
        }
        //taxonomy check for masonary infinte load
        if(isset($atts['taxonomy']) && $atts['taxonomy']!=''){
                if($atts['taxonomy'] == 'tag'){
                    $atts['taxonomy']='tag_name'; 
                }   
        }

        $terms = $atts['term'];
        if(strpos($terms,',') !== false){
            $terms = explode(',',$atts['term']);
        }
     
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

        //sorting check for masonary infinite load
        if($atts['post_type'] == 'course' && isset($atts['course_style'])){
            switch($atts['course_style']){
                case 'popular':
                  $query_args['orderby'] = 'meta_value_num';
                  $query_args['meta_key'] = 'vibe_students';
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
            }
        }
        
        $query_args =  apply_filters('wplms_grid_course_filters',$query_args);

        query_posts($query_args);
        while ( have_posts() ) : the_post();
        global $post;
        $output .= '<li '.(isset($atts['grid_columns'])?'class="'.$atts['grid_columns'].'"':'').' '.$style.'>';
        $output .= thumbnail_generator($post,$atts['featured_style'],$atts['grid_columns'],$atts['grid_excerpt_length'],$atts['grid_link'],$atts['grid_lightbox']);
        $output .= '</li>';
        
        endwhile;
        wp_reset_query();
        wp_reset_postdata();
        
        echo $output;
        }else{
            echo '0';
        }
        die();
    }

    /*
        USER REGISTRATION FORMS
    */
    function wplms_register_user(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bp_new_signup') || !isset($_POST['settings'])){
            echo '<div class="message">'.__('Security check Failed. Contact Administrator.','vibe-shortcodes').'</div>';
            die();
        }
        $flag = 0;
        $settings = json_decode(stripslashes($_POST['settings']));
        if(empty($settings)){
            $flag = 1; 
        }
        $name = sanitize_text_field($_POST['name']);
        if(empty($name)){
            echo '<div class="message_wrap"><div class="message">'._x('Invalid Submission, name missing','error message when name missing','vibe-shortcodes').'<span></span></div></div>';
                die();
        }
        $member_type = '';
        $wplms_user_bp_group = '';
        $form_settings = apply_filters('wplms_registration_form_settings',array(
                'hide_username' =>  __('Auto generate username from email','vibe-shortcodes'),
                'password_meter' =>  __('Show password meter','vibe-shortcodes'),
                'show_group_label' =>  __('Show Field group labels','vibe-shortcodes'),
                'google_captcha' => __('Google Captcha','vibe-shortcodes'),
                'auto_login'=> __('Register & Login simultaneously','vibe-shortcodes'),
                'skip_mail' =>  __('Skip Mail verification','vibe-shortcodes'),
                'default_role'=>'',
                'member_type'=>'',
                'wplms_user_bp_group'=>'',
        ));

        $user_args = $user_fields = $save_settings = array();

        if(empty($flag)){

            $all_form_settings = get_option('wplms_registration_forms');
            if(!empty($all_form_settings))
                $reg_form_settings = $all_form_settings[$name];
            $secured_array = array('default_role','hide_username','auto_login','skip_mail','member_type');

            if(!empty($reg_form_settings)){
                if(!empty($reg_form_settings['settings'])){
                    //member_types select dropdown check
                    if(!empty($reg_form_settings['settings']['member_type']) &&  $reg_form_settings['settings']['member_type'] == 'enable_user_member_types_select'){
                        foreach($secured_array as $key => $secured){
                            if($secured == 'member_type'){
                                unset($secured_array[$key]);
                            }
                        }
                    }
                    
                    foreach ($secured_array as $secured) {
                       if(!empty($reg_form_settings['settings'][$secured])){ 
                            foreach($settings as $key => $setting){
                                if($setting->id == $secured){
                                    unset($settings[$key]);
                                }
                            }
                            $default_array= array('id'=>$secured,'value'=>$reg_form_settings['settings'][$secured]);
                            $settings[] = (object) $default_array;
                       
                        }
                    }
                }
            }

            $settings2 = array();

            foreach($settings as $setting){

                if(!empty($setting->id)){
                    $settings2[] = $setting->id;
                    if($setting->id == 'signup_username'){
                        $user_args['user_login'] = $setting->value;
                    }else if($setting->id == 'signup_email'){
                        $user_args['user_email'] = $setting->value;
                    }else if($setting->id == 'signup_password'){
                        $user_args['user_pass'] = $setting->value;
                    }else{
                        if(strpos($setting->id,'field') !== false){

                            $f = explode('_',$setting->id);
                            $field_id = $f[1]; 
                            if(strpos($field_id, '[')){ //checkbox
                                $v = str_replace('[','',$field_id);
                                $v = str_replace(']','',$v);
                                $field_id = $v;
                                if(is_Array($user_fields[$field_id]['value'])){
                                    $user_fields[$field_id]['value'][] = $setting->value;
                                }else{
                                    $user_fields[$field_id] = array('value'=>array($setting->value));
                                }
                            }else{
                                if(is_numeric($field_id) && !isset($f[2])){
                                    $user_fields[$field_id] = array('value'=>$setting->value);
                                }else{
                                    if(in_array($f[2],array('day','month','year'))){
                                        $user_fields['field_' . $field_id . '_'.$f[2]] = $setting->value;
                                    }else{
                                        $user_fields[$field_id]['visibility']=$setting->value;    
                                    }
                                }
                            }
                           
                        }else{
                            if(isset($form_settings[$setting->id])){
                            
                                $form_settings[$setting->id] = 0; // use it for empty check 
                                if($setting->id=='default_role'){
                                    $save_settings[$setting->id]=$setting->value;
                                    $user_args['role'] = $setting->value;
                                }
                                if($setting->id=='member_type'){
                                    $save_settings[$setting->id]=$setting->value;
                                    $member_type=$setting->value;
                                }
                                if($setting->id=='wplms_user_bp_group'){
                                    if(in_array($setting->value,$reg_form_settings['settings']['wplms_user_bp_group']) || $reg_form_settings['settings']['wplms_user_bp_group'] === array('enable_user_select_group')){
                                        $save_settings[$setting->id]=$setting->value;
                                        $wplms_user_bp_group = $setting->value;
                                    }else{
                                        echo '<div class="message_wrap"><div class="message">'._x('Invalid Group selection','error message when group is not valid','vibe-shortcodes').'<span></span></div></div>';
                                        die();
                                    }
                                    
                                }
                            }
                            
                        }
                    }
                }
            }
            if(!in_array('wplms_user_bp_group', $settings2)){
                if(!empty($reg_form_settings['settings']['wplms_user_bp_group']) && is_array($reg_form_settings['settings']['wplms_user_bp_group']) && $reg_form_settings['settings']['wplms_user_bp_group'] !== array('enable_user_select_group') && count($reg_form_settings['settings']['wplms_user_bp_group'])==1){
                    $wplms_user_bp_group = $reg_form_settings['settings']['wplms_user_bp_group'][0];
                }
            }
        }
            $user_args = apply_filters('wplms_register_user_args',$user_args);
            
            /*
            RUN CONDITIONAL CHECKS
            */
            $check_filter = filter_var($user_args['user_email'], FILTER_VALIDATE_EMAIL); // PHP 5.3
            if(empty($user_args['user_email']) || empty($user_args['user_pass']) || empty($check_filter)){
                echo '<div class="message_wrap"><div class="message">'._x('Invalid Email/Password !','error message when registration form is empty','vibe-shortcodes').'<span></span></div></div>';
                die();
            }

            //Check if user exists
            if(!isset($user_args['user_email']) || email_exists($user_args['user_email'])){
                echo '<div class="message_wrap"><div class="message">'._x('Email already registered.','error message','vibe-shortcodes').'<span></span></div></div>';
                die();
            }

            //Check if user exists
            if(!isset($user_args['user_login'])){

                $user_args['user_login'] = $user_args['user_email'];
                if(email_exists($user_args['user_login'])){
                    echo '<div class="message_wrap"><div class="message">'._x('Username already registered.','error message','vibe-shortcodes').'<span></span></div></div>';
                    die();
                }
            }elseif (username_exists($user_args['user_login'])){
                echo '<div class="message_wrap"><div class="message">'._x('Username already registered.','error message','vibe-shortcodes').'<span></span></div></div>';
                die();
            }

            if(!empty($save_settings['google_captcha'])  && function_exists('vibe_get_option')){
               /* include_once 'classes/recaptchalib.php';
                $private_key = vibe_get_option('google_captcha_private_key');
                $objRecaptcha = new ReCaptcha($private_key);
                $response = $objRecaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], $_POST['captcha']);
                if(!isset($response->success) || 1 != $response->success){
                    echo __('Invalid Captcha field','wplms-mc');
                    die();
                }*/
            }
            
            $error_message = array();
            if ( bp_is_active( 'xprofile' ) ) {

            // Make sure hidden field is passed and populated.
                if ( isset($user_fields) ) {

                    // Let's compact any profile field info into an array.
                    $profile_field_ids = array_keys($user_fields);

                    // Loop through the posted fields formatting any datebox values then validate the field.
                    foreach ( (array) $profile_field_ids as $field_id ) {
                        
                        if ( !isset( $user_fields[$field_id] ) || !isset($user_fields[$field_id]['value']) ) {

                            //Date Handling
                            if ( !empty( $user_fields['field_' . $field_id . '_day'] ) && !empty( $user_fields['field_' . $field_id . '_month'] ) && !empty( $user_fields['field_' . $field_id . '_year'] ) ){

                                if(empty($user_fields[$field_id])){$user_fields[$field_id] = array();}
                                $user_fields[$field_id]['value'] = date( 'Y-m-d H:i:s', strtotime( $_POST['field_' . $field_id . '_day'] . $user_fields['field_' . $field_id . '_month'] . $user_fields['field_' . $field_id . '_year'] ) );

                            }
                            unset($user_fields['field_' . $field_id . '_day']);
                            unset($user_fields['field_' . $field_id . '_month']);
                            unset($user_fields['field_' . $field_id . '_year']);
                        }

                        
                        $field  = new BP_XProfile_Field( $field_id );
                         
                        // Create errors for required fields without values.
                        if ( xprofile_check_is_required_field( $field_id ) && empty($user_fields[$field_id]['value'])){
                            
                             $error_message[$field->id] = array('field_id'=>$field->id,'message'=>sprintf(__('%s is a required field', 'vibe-shortcodes' ),$field->name));
                        }else{
                            if (  !empty($user_fields[$field_id]['value']) && ! $field->type_obj->is_valid( $user_fields[$field_id]['value'] ) ) {
                                if(empty($error_message[$field->id])){
                                    $error_message[]= array('field_id'=>$field->id,'message'=>sprintf(__('%s is not of type %s','vibe-shortcodes'),$field->name,$field->type));
                                }else{
                                    $error_message[$field->id]['message'] .= ' , '.sprintf(__('%s is not of type %s','vibe-shortcodes'),$field->name,$field->type);
                                }
                            }
                        }
                    }

                    // This situation doesn't naturally occur so bounce to website root.
                }
            }
            

            if(!empty($error_message)){
                echo '<script>';
                foreach($error_message as $message){
                    ?>
                    jQuery(".bp-profile-field.field_<?php echo $message['field_id']; ?>").addClass("field_error");
                    jQuery(".bp-profile-field.field_<?php echo $message['field_id']; ?>").append("<div class='message error'><?php echo $message['message']; ?></div>");
                    <?php
                }
                echo '</script>';
                die();
            }
            /*
            FORM SETTINGS
            */
            if(empty($form_settings['hide_username'])){
                $user_args['user_login'] = $user_args['user_email'];
            }

            if(!empty($form_settings['skip_mail'])){
                $user_id = wp_insert_user($user_args);
                do_action('wplms_custom_registration_form_user_added',$user_id,$user_args,$settings);

                if ( ! is_wp_error( $user_id ) ) {
                    if(!empty($user_fields)){
                        foreach($user_fields as $field_id=>$val){
                            if(isset($val['value']))
                                xprofile_set_field_data( $field_id, $user_id, $val['value'] );
                            if(isset($val['visibility']))
                                xprofile_set_field_visibility_level( $field_id, $user_id, $val['visibility'] );
                        }
                    }
                    if(!empty($save_settings)){
                        foreach($save_settings as $s_id => $s_val){
                            update_user_meta($user_id,$s_id,$s_val);
                        }
                    }

                    if(!empty($member_type) && function_exists('bp_set_member_type')){
                        bp_set_member_type($user_id, $member_type );
                    }
                    if(function_exists('groups_join_group') && !empty($wplms_user_bp_group) && is_numeric($wplms_user_bp_group)){
                        groups_join_group($wplms_user_bp_group, $user_id );
                    }


                    echo '<div class="message success"><div class="message_content">'.__('Congratulations ! you have been successfully registered !','vibe-shortcodes').'<span></span></div></div>';
                }else{
                    echo '<div class="message_wrap"><div class="message">'.$user_id->get_error_message().'<span></span></div></div>';
                }
            }else{
                $usermeta = array();

                $usermeta['password'] = wp_hash_password( $user_args['user_pass'] );

                if(!empty($user_fields)){
                    foreach($user_fields as $field_id=>$val){
                        $usermeta['field_' . $field_id] = $val;
                    }
                }
                if(is_multisite()){
                    foreach($save_settings as $s_id => $s_val){
                        $usermeta['wplms_meta']=array('id'=>$s_id,'value'=>$s_val);
                    }
                }

                $user_id = bp_core_signup_user( $user_args['user_login'], $user_args['user_pass'], $user_args['user_email'], $usermeta );

                do_action('wplms_custom_registration_form_user_added',$user_id,$user_args,$settings);

                if(is_multisite()){
                    if (  is_wp_error( $user_id ) ) {
                        echo '<div class="message_wrap"><div class="message">'.$user_id->get_error_message().'<span></span></div></div>';
                    }else{
                        echo '<div class="message success"><div class="message_content">'.__('Congratulations ! you have been successfully registered, Please check your email to activate the account','vibe-shortcodes').'<span></span></div></div>';
                    }
                }else{
                    if(!empty($user_fields)){
                        foreach($user_fields as $field_id=>$val){
                            if(isset($val['value']))
                                xprofile_set_field_data( $field_id, $user_id, $val['value'] );
                            if(isset($val['visibility']))
                                xprofile_set_field_visibility_level( $field_id, $user_id, $val['visibility'] );
                        }
                    }
                    if ( ! is_wp_error( $user_id ) ) {
                        if(!empty($save_settings)){
                            foreach($save_settings as $s_id => $s_val){
                                update_user_meta($user_id,$s_id,$s_val);
                            }
                        }
                        if(!empty($member_type) && function_exists('bp_set_member_type')){
                            bp_set_member_type($user_id, $member_type );
                        }
                         if(function_exists('groups_join_group') && !empty($wplms_user_bp_group) && is_numeric($wplms_user_bp_group)){
                            groups_join_group($wplms_user_bp_group, $user_id );
                        }
                        echo '<div class="message success"><div class="message_content">'.__('Congratulations ! you have been successfully registered, Please check your email to activate the account','vibe-shortcodes').'<span></span></div></div>';

                    }else{
                        echo '<div class="message_wrap"><div class="message">'.$user_id->get_error_message().'<span></span></div></div>';
                    }
                }
            }

            if(empty($form_settings['auto_login']) && !empty($user_id) && ! is_wp_error( $user_id )){
               if(!is_wp_error($user_id)){
                    wp_set_current_user( $user_id, $user_args['user_login'] );
                    wp_set_auth_cookie( $user_id,1 );
                    do_action( 'wp_login', $user_args['user_login'], $user_args ); 
                    $user = wp_get_current_user();
                    $redirect_url = '';
                    if(function_exists('vibe_get_option')){
                        $pageid = vibe_get_option('activation_redirect');
                        if($pageid == 'dashboard'){
                            if(defined('WPLMS_DASHBOARD_SLUG')){
                                $redirect_url = bp_core_get_user_domain($user_id).WPLMS_DASHBOARD_SLUG;
                            }else{
                                $redirect_url = bp_core_get_user_domain($user_id).'dashboard';
                            }
                        }else if($pageid == 'profile'){
                            if(function_exists('bp_loggedin_user_domain'))
                                $redirect_url = bp_core_get_user_domain($user_id);
                        }else if($pageid == 'mycourses'){
                            if(defined('BP_COURSE_SLUG')){
                                $redirect_url = trailingslashit( bp_core_get_user_domain($user_id).BP_COURSE_SLUG );
                            }else{
                                $redirect_url = bp_core_get_user_domain($user_id).'course';   
                            }
                        }else if(is_numeric($pageid)){

                            if(function_exists('icl_object_id')){
                                $pageid = icl_object_id($pageid, 'page', true);
                            }
                            $redirect_url = get_permalink($pageid);
                        }
                    }
                    $redirect_url = apply_filters ( 'wplms_registeration_redirect_url',$redirect_url, $user_id );
                    
                    if(empty($redirect_url)){
                        echo '<script>location.reload();</script>';
                    }else{
                        echo '<script>window.location.href = "'.$redirect_url.'";</script>';    
                    }
                    
                }
            }

        die();
    }   // end function

}

Vibe_Shortcodes_Ajax_Calls::init();

/*
MISCELLANEOUS FUNCTIONS
 */
if(!function_exists('getPostMeta')){
    /// POST Views
    function getPostMeta($postID,$count_key = 'post_views_count'){
        $count = get_post_meta($postID, $count_key, true);

        if($count==''){
            delete_post_meta($postID, $count_key);
            add_post_meta($postID, $count_key, '0');
            return "0";
       }
       return $count;
    }
}

if(!function_exists('wp_get_attachment_info')){
    function wp_get_attachment_info( $attachment_id ) {

        $attachment = get_post( $attachment_id );
            if(isset($attachment)){
                return array(
                    'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
                    'caption' => $attachment->post_excerpt,
                    'description' => $attachment->post_content,
                    'href' => get_permalink( $attachment->ID ),
                    'src' => $attachment->guid,
                    'title' => $attachment->post_title
                );
           }
    }

}

if(!function_exists('animation_effects')){
    function animation_effects(){
        $animate=array(
                    ''=>'none',
                    'cssanim flash'=> 'Flash',
                    'zoom' => 'Zoom',
                    'scale' => 'Scale',
                    'slide' => 'Slide (Height)', 
                    'expand' => 'Expand (Width)',
                    'cssanim shake'=> 'Shake',
                    'cssanim bounce'=> 'Bounce',
                    'cssanim tada'=> 'Tada',
                    'cssanim swing'=> 'Swing',
                    'cssanim wobble'=> 'Flash',
                    'cssanim wiggle'=> 'Flash',
                    'cssanim pulse'=> 'Flash',
                    'cssanim flip'=> 'Flash',
                    'cssanim flipInX'=> 'Flip Left',
                    'cssanim flipInY'=> 'Flip Top',
                    'cssanim fadeIn'=> 'Fade',
                    'cssanim fadeInUp'=> 'Fade Up',
                    'cssanim fadeInDown'=> 'Fade Down',
                    'cssanim fadeInLeft'=> 'Fade Left',
                    'cssanim fadeInRight'=> 'Fade Right',
                    'cssanim fadeInUptBig'=> 'Fade Big Up',
                    'cssanim fadeInDownBig'=> 'Fade Big Down',
                    'cssanim fadeInLeftBig'=> 'Fade Big Left',
                    'cssanim fadeInRightBig'=> 'Fade Big Right',
                    'cssanim bounceInUp'=> 'Bounce Up',
                    'cssanim bounceInDown'=> 'Bounce Down',
                    'cssanim bounceInLeft'=> 'Bounce Left',
                    'cssanim bounceInRight'=> 'Bounce Right',
                    'cssanim rotateIn'=> 'Rotate',
                    'cssanim rotateInUpLeft'=> 'Rotate Up Left',
                    'cssanim rotateInUpRight'=> 'Rotate Up Right',
                    'cssanim rotateInDownLeft'=> 'Rotate Down Left',
                    'cssanim rotateInDownRight'=> 'Rotate Down Right',
                    'cssanim speedIn'=> 'Speed In',
                    'cssanim rollIn'=> 'Roll In',
                    'ltr'=> 'Left To Right',
                    'rtl' => 'Right to Left', 
                    'btt' => 'Bottom to Top',
                    'ttb'=>'Top to Bottom',
                    'smallspin'=> 'Small Spin',
                    'spin'=> 'Infinite Spin'
                );
        return $animate;
    }
}
?>
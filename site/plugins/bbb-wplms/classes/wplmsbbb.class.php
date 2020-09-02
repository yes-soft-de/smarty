<?php

/*$included = require_once( ABSPATH . PLUGINDIR .'/bigbluebutton/php/bbb_api.php' );
if(!$included )
    return;*/
if(!class_exists('Wplms_Bbb'))
{   
    class Wplms_Bbb  // We'll use this just to avoid function name conflicts 
    {
        public static $instance;
        public static function init(){
            if ( is_null( self::$instance ) )
                self::$instance = new Wplms_Bbb();
            return self::$instance;
        } 
        public function __construct(){   
            add_shortcode('wplms_bbb',array($this,'wplms_bbb_shortcode')); 
            add_action( 'media_buttons', array($this,'wplms_bbb_create_meeting'),100);
            add_action('media_upload_wplms_bbb_meetings',array($this,'media_bbb_create_meeting'));
            add_action('wp_ajax_select_users_bbb',array($this,'select_users_bbb'));
            add_action('wp_ajax_create_new_meeting',array($this,'create_new_meeting'));
            add_action('wp_ajax_edit_meeting',array($this,'edit_meeting'));
            add_action('wplms_bbb_meeting_created',array($this,'set_reminder_cron_jobs'));
            add_action('wplms_send_wplms_bbb_reminders',array($this,'wplms_send_wplms_bbb_reminders'),10,2);
            add_action('wp_ajax_fetch_meeting_iframe',array($this,'fetch_meeting_iframe'));
            add_action('wplms_bbb_user_meeting_logout',array($this,'user_meeting_logout'));
            add_action('wp_ajax_join_bbb_wplms_bbb_do_action',array($this,'join_bbb_wplms_bbb_do_action'));
            add_action('wplms_bbb_user_meeting_join',array($this,'record_join_meeting_activity'));
            add_action('wp_ajax_meeting_logout',array($this,'meeting_logout'));
            add_filter('bp_course_all_mails',array($this,'add_wplms_bbb_email'));
            add_action( 'bp_setup_nav', array($this,'wplms_bbb_meetings_tab'),5);
            add_filter('wplms_get_all_meetings',array($this,'apply_instructor_privacy'));
            //reset cron jobs on edit meeting
            add_action('wplms_bbb_meeting_edited',array($this,'reset_crons_on_edit'),10,3);
            add_action('wp_ajax_delete_wplms_bb_meeting',array($this,'delete_wplms_bb_meeting'));
            add_action('admin_print_scripts',array($this,'remove_badge_os_scripts_for_bbb'));
            $this->restrictions = apply_filters('bbb_restrictions_options',array(
                'logged_in'=>__('Logged in users','wplms-bbb'),
                'instructors'=>__('Instructors only','wplms-bbb'),
                'course_students'=>__('Course students','wplms-bbb'),
                'selected_users'=>__('Selected users only','wplms-bbb'),
                
            ));
            if( function_exists('bp_is_active') && bp_is_active( 'groups' ) ){
                add_action('wp_ajax_get_front_groups_bbb',array($this,'get_groups'));
                $this->restrictions['group'] = __('Group','wplms-bbb');
            }
            $this->offset = get_option('gmt_offset');
            $this->wplms_bbb_meetings = get_option('wplms_bbb_meetings');
            $this->open_in_new_tab = apply_filters('wplms_open_in_new_tab',1,$this->wplms_bbb_meetings);
        } // END public function __construct
        public function activate(){
        	// ADD Custom Code which you want to run when the plugin is activated
        }
        public function deactivate(){

                wp_clear_scheduled_hook('wplms_send_wplms_bbb_reminders');  
            
        }


        function get_groups(){
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bbb_meetings'.$user_id) ){
                echo 'Security check failed !';
                die();
            } 
            $q = $_POST['q'];

            if(function_exists('groups_get_group')){
                $vgroups =  groups_get_groups(array(
                'per_page'=>999,
                'search_terms'=>$q['term'],
               'user_id' => $user_id,
               'show_hidden'=>true,
                ));
                $return = array();
                foreach($vgroups['groups'] as $vgroup){
                    $return[] = array('id'=>$vgroup->id,'text'=>$vgroup->name);
                }
            }
            print_r(json_encode($return));
            die();
        }

        function remove_badge_os_scripts_for_bbb(){
            if(!empty($_GET['type']) && $_GET['type'] == 'wplms_bbb_meetings'){

                if(in_array( 'badgeos/badgeos.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active') && is_plugin_active( 'badgeos/badgeos.php'))){
                      wp_deregister_script('badgeos-select2');
                      wp_dequeue_script('badgeos-select2');
                     // wp_deregister_script('select2');
                      //wp_iframedequeue_script('select2');
                      wp_dequeue_style('badgeos-select2-css');
                      wp_deregister_style('badgeos-select2-css');
                }
            }
        }

        function apply_instructor_privacy($all_meetings){
            if(!is_user_logged_in() || (is_user_logged_in() && current_user_can('manage_options')))
                return $all_meetings;
            if(function_exists('vibe_get_option')){
                $inst_privacy_enabled = vibe_get_option('instructor_content_privacy');
                if(!empty($inst_privacy_enabled)){
                    $user_id = get_current_user_id();
                    foreach($all_meetings as $key => $meeting){
                        if($meeting['author'] != $user_id ){
                            unset($all_meetings[$key]);
                        }
                    }
                }
            }
            
            return $all_meetings;
        }

        function wplms_bbb_meetings_tab(){
            bp_core_new_nav_item( array( 
                'name' => __( 'Meetings','wplms-bbb'), 
                'slug' => 'bbbmeetings', 
                'screen_function' => array($this,'wplms_bbb_screen'), 
                'show_for_displayed_user' => false,
                'item_css_id' => 'bbbmeetings',
                'default_subnav_slug' => 'home', 
                'position' => 55,
                ) 
            );
        }

        function wplms_bbb_screen() {
            add_action( 'bp_template_title',array($this, 'wplms_bbb_title' ));
            add_action( 'bp_template_content', array($this,'wplms_bbb_content') );
            bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
        }

        function wplms_bbb_title() {
           echo '<h3 class="heading"><span>'._x('Meetings','','wplms-bbb').'</span></h3>';
        }

        function delete_wplms_bb_meeting(){
            if(!is_user_logged_in() || (is_user_logged_in() && !current_user_can('edit_posts'))){
                return;
            }
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bbb_meetings'.$user_id) ){
                echo 'Security check failed !';
                die();
            }
            if(empty($_POST['meeting_id'])){
                die();
            }
            if(empty($this->wplms_bbb_meetings))
                die();
            $new_meetings = $this->wplms_bbb_meetings;
            $meeting_id = sanitize_text_field($_POST['meeting_id']);
            $meeting = $this->get_meeting($meeting_id);
            if(empty($meeting) || empty($meeting['author']))
                die();
            $flag  = 0;
            if(function_exists('vibe_get_option')){
                $inst_privacy_enabled = vibe_get_option('instructor_content_privacy');
                if(empty($inst_privacy_enabled)){
                   $flag = 1; 
                }elseif(!empty($inst_privacy_enabled) && ($meeting['author'] == $user_id)){
                    $flag = 1;
                }
            }
            if($flag || current_user_can('manage_options')){
                unset($new_meetings[$meeting_id]);
                update_option('wplms_bbb_meetings',$new_meetings);
                global $wpdb;
                $table = $wpdb->prefix.'bigbluebutton';

                $wpdb->delete( 
                    $table, 
                    array( 'meetingID' => $meeting_id), 
                    array( '%s' ) 
                );
            }else{
                echo 'You are not allowed to delete this meeting';
            }
            die();
        }

        function wplms_bbb_content() {
            if(empty($this->wplms_bbb_meetings)){
                echo '<div class-"message">'._x('No meetings found','','wplms-bbb').'</div>';
                return;
            }
            if(!is_user_logged_in())
                return;
            $user_id = get_current_user_id();
            echo '<table id="user-tours" class="table table-hover">';
            echo '<thead><tr><th>'._x('Meeting name','','vibe').'</th><th>'._x('Privacy','','wplms-bbb').'</th><th>'._x('Status','','vibe').'</th><th>'._x('Action','','vibe').'</th></tr></thead><tbody>';
            foreach ($this->wplms_bbb_meetings as $meetng_id => $meeting) {
                $scope = $meeting['restrictions']['scope'];
                $flag = 0;
                $users = $this->users_from_restriction($meeting,1);
                if(in_array($user_id,$users)){
                    
                    $status = _x('NA','','wplms-bbb');
                    if(!empty($meeting['start_date']) && !empty($meeting['start_time'])){

                        $start_time = strtotime($meeting['start_date'].' '.$meeting['start_time']);
                        $start_time_actual = strtotime($meeting['start_date'].' '.$meeting['start_time']);
                        $offset = get_option('gmt_offset');
                        if($offset > 0){//means gmt offset is in positive
                            $start_time = $start_time - (abs($offset)*60*60);

                        }else{//means gmt offset is in negative
                            $start_time = $start_time + (abs($offset)*60*60);
                        }
                        $expiry_time = $start_time + ($meeting['duration']['duration']* $meeting['duration']['parameter']);
                        $expiry_time_actual = $start_time_actual + ($meeting['duration']['duration']* $meeting['duration']['parameter']);
                        $format = get_option( 'date_format' ).' '.get_option('time_format');;
                        $readable_time_start = date_i18n($format ,$start_time_actual);
                        $readable_time_expire =date_i18n($format , $expiry_time_actual); 
                        if(time() >= $start_time &&  time() <= $expiry_time ){
                            $status = _x('Ongoing','','wplms-bbb');
                        }elseif(time() <= $start_time){
                            
                            $status = sprintf(_x('To be started on %s','','wplms-bbb'), $readable_time_start );
                        }elseif(time() >= $expiry_time){
                            $status =sprintf( _x('Meeting over on %s','','wplms-bbb'),$readable_time_expire );;
                        }
                    }
                   
                    $restriction = _x('NA','','wplms-bbb');
                    if(!empty($meeting['restrictions'])){
                        if(!empty($meeting['restrictions']['scope']))
                        $restriction = $this->restrictions[$meeting['restrictions']['scope']];
                        if(!empty($meeting['restrictions']['data'])){
                            if($meeting['restrictions']['scope'] == 'course_students'){
                                foreach($meeting['restrictions']['data'] as $course){
                                  $restriction .= '<br><span>('.get_the_title($course).')</span>';  
                                }
                                
                            }elseif($meeting['restrictions']['scope'] == 'selected_users'){
                                $i=0;
                                $count = (count($meeting['restrictions']['data'])-2);
                                $restriction .= '<br><span>(';
                                foreach ($meeting['restrictions']['data'] as $value) {
                                    if($i < 2){
                                      $student = get_user_by('id',$value);
                                      $restriction .= $student->display_name;
                                    }
                                    if($i < 1){
                                        $restriction .= ',';
                                    }
                                    $i++;
                                }
                                if($count > 0){
                                    $restriction .= sprintf(_x(' and %s more','','wplms-bbb'),$count);
                                }
                                $restriction .= ')</span>';
                            }elseif($meeting['restrictions']['scope'] == 'group'){
                                foreach($meeting['restrictions']['data'] as $id){
                                    if(function_exists('groups_get_group')){
                                        $group = groups_get_group(esc_attr($id));
                                    }
                                  $restriction .= '<br><span>('.$group->name.')</span>';  
                                }
                            }
                        }
                    }
                    echo '<tr><td>'.$meeting['name'].'</td>';
                    echo '<td>'.$restriction.'</td>';
                    echo '<td>'.$status.'</td>';
                    echo '<td>'.do_shortcode('[wplms_bbb token="'.$meeting['id'].'" popup="1" size="1"]').'</td>';
                    echo '<tr>';
                }
            }
            echo '</tbody></table>';


        }

        function record_join_meeting_activity($meeting_id){

            if(!is_user_logged_in())
                return;
            if(empty($meeting_id))
                return;
            $user_id = get_current_user_id();
            $meeting = $this->get_meeting($meeting_id);
            global $wpdb;
            $table_name = $wpdb->prefix.'bp_'.'activity';
            $meta_table_name = $wpdb->prefix.'bp_'.'activity_meta';
            $offset = $this->offset;
            $utc_time = time();
            $start_time = strtotime($meeting['start_date'].' '.$meeting['start_time']);
            if($offset > 0){//means gmt offset is in positive
                $start_time = $start_time - (abs($offset)*60*60);
                $utc_time = $utc_time - (abs($offset)*60*60);
                

            }else{//means gmt offset is in negative
                $start_time = $start_time + (abs($offset)*60*60);
                $utc_time = $utc_time + (abs($offset)*60*60);
                
            }
            $meeting_expire_time = $start_time + ($meeting['duration']['duration']*$meeting['duration']['parameter']);
           
            if($meeting_expire_time <= time())
                return;
            $sql = "SELECT a.date_recorded as activity_time from {$table_name} as a 
            LEFT JOIN {$meta_table_name} as m 
            ON a.id = m.activity_id WHERE 
            a.user_id= {$user_id} 
            AND m.meta_value = '{$meeting_id}' 
            AND m.meta_key ='meeting_id_join' 
            ORDER BY a.id DESC LIMIT 0,1";
            $meta = $wpdb->get_row($sql);
  
            if(!empty($meta)){
                $activity_time = strtotime($meta->activity_time);
                if($activity_time  >= $start_time &&  $activity_time <= $meeting_expire_time ){
                    return;
                }
            } 
            if(!empty($meeting['course'])){
                //record activity show message //
                if(function_exists('bp_course_record_activity') && function_exists('bp_core_get_user_displayname')){

                   $activity_id = bp_course_record_activity(array(
                      'action' => sprintf(__('Student %s joins meeting %s','wplms-bbb'),bp_core_get_user_displayname($user_id),$meeting['name']),
                      'content' => sprintf(__('Student %s joined meeting %s','wplms-bbb'),bp_core_get_user_displayname($user_id),$meeting['name']),
                      'type' => 'meeting_joined',
                      'item_id' => $meeting['course'],
                      'primary_link'=>get_permalink($meeting['course']),
                      'secondary_item_id'=>$user_id
                    ));
                    bp_course_record_activity_meta(array(
                      'id' => $activity_id,
                      'meta_key' => 'meeting_id_join',
                      'meta_value' => $meeting_id
                    )); 
                } 
            }
        }

        function join_bbb_wplms_bbb_do_action(){
            if(!is_user_logged_in())
                die();
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'join_bbb_wplms_bbb_do_action') || empty($_POST['meeting_id'])){
                echo 'Security check failed !';
                die();
            }
            $meeting_id = sanitize_text_field($_POST['meeting_id']);
            do_action('wplms_bbb_user_meeting_join',  $meeting_id );
            die();
        }

        function meeting_logout(){
            
            if(!is_user_logged_in())
                die();
         
            $user_id = get_current_user_id();
             var_dump(wp_verify_nonce($_REQUEST['meeting_security'],'meeting_logout'.$user_id));
            if ( empty($_REQUEST['meeting_security']) || !wp_verify_nonce($_REQUEST['meeting_security'],'meeting_logout'.$user_id) || empty($_REQUEST['meeting'])){
                echo 'Security check failed !';
                die();
            }
            do_action('wplms_bbb_user_meeting_logout',$_REQUEST['meeting']);
            die();
            
        }

        function user_meeting_logout($meeting_id){
            if(!is_user_logged_in())
                return;
            $user_id = get_current_user_id();
            $meeting = $this->get_meeting($meeting_id);
            global $wpdb;
            $table_name = $wpdb->prefix.'bp_'.'activity';
            $meta_table_name = $wpdb->prefix.'bp_'.'activity_meta';
            $offset = $this->offset;
            $utc_time = time();
            $start_time = strtotime($meeting['start_date'].' '.$meeting['start_time']);
            if($offset > 0){//means gmt offset is in positive
                $start_time = $start_time - (abs($offset)*60*60);
                $utc_time = $utc_time - (abs($offset)*60*60);
                

            }else{//means gmt offset is in negative
                $start_time = $start_time + (abs($offset)*60*60);
                $utc_time = $utc_time + (abs($offset)*60*60);
                
            }
            $meeting_expire_time = $start_time + ($meeting['duration']['duration']*$meeting['duration']['parameter']);
           
            if($meeting_expire_time <= time())
                return;
            $sql = "SELECT a.date_recorded as activity_time from {$table_name} as a 
            LEFT JOIN {$meta_table_name} as m 
            ON a.id = m.activity_id WHERE 
            a.user_id= {$user_id} 
            AND m.meta_value = '{$meeting_id}' 
            AND m.meta_key ='meeting_id_logout' 
            ORDER BY a.id DESC LIMIT 0,1";
            $meta = $wpdb->get_row($sql);
  
            if(!empty($meta)){
                $activity_time = strtotime($meta->activity_time);
                if($activity_time  >= $start_time &&  $activity_time <= $meeting_expire_time ){
                    return;
                }
            } 
          
            if(!empty($meeting['course'])){
                //record activity show message //
                if(function_exists('bp_course_record_activity') && function_exists('bp_core_get_user_displayname')){
                   $activity_id = bp_course_record_activity(array(
                      'action' => sprintf(__('Student logs out from meeting %s','wplms-bbb'),bp_core_get_user_displayname($user_id),$meeting['name']),
                      'content' => sprintf(__('Student %s logs out from meeting %s','wplms-bbb'),bp_core_get_user_displayname($user_id),$meeting['name']),
                      'type' => 'meeting_logout',
                      'item_id' => $meeting['course'],
                      'primary_link'=>get_permalink($meeting['course']),
                      'secondary_item_id'=>$user_id
                    ));
                    bp_course_record_activity_meta(array(
                      'id' => $activity_id,
                      'meta_key' => 'meeting_id_logout',
                      'meta_value' => $meeting_id
                    )); 
                } 
            }
            echo _x('You logged out from meeting.','','wplms-bbb');
        }

        function add_wplms_bbb_email($bp_emails){

            $bp_emails['wplms_bbb_reminder']=array(
            'description'=> __('Meeting about to start','wplms-bbb'),
            'subject' =>  sprintf(__('%s meeting is about to start.','wplms-bbb'),'{{meeting.name}}'),
            'message' =>  sprintf(__('Meeting %s is about to start on %s','wplms-bbb'),'{{meeting.name}}','{{{site.name}}}')
            );
            return $bp_emails;
        }

        function set_reminder_cron_jobs($option_data){
            //set cron job for reminder
            if(empty($option_data['reminder']))
                return;
            if(!empty($option_data['start_date'])){
               $start_time = strtotime($option_data['start_date'].' '.$option_data['start_time']); 

                $timestamp = 0;
                $offset = $this->offset;
                $utc_time = time();
                $reminder = ($option_data['reminder']['duration']*$option_data['reminder']['parameter']);
                if($offset > 0){//means gmt offset is in positive
                    $start_time = $start_time - (abs($offset)*60*60);
                    $utc_time = $utc_time - (abs($offset)*60*60);
                    if($start_time > $reminder){
                       $timestamp = $start_time - $reminder;
                    }

                }else{//means gmt offset is in negative
                    $start_time = $start_time + (abs($offset)*60*60);
                    $utc_time = $utc_time + (abs($offset)*60*60);
                    if($start_time > $reminder){
                       $timestamp = $start_time - $reminder;
                    }
                }
                /*$timestamp = $start_time - $option_data['reminder'];
                $timestamp += (abs($offset)*60*60);*/
                if(  $timestamp  > $utc_time){
                    $users = array();
                    if(!empty($option_data['restrictions']['scope'])){

                        $users = $this->users_from_restriction($option_data);
                        
                    }
                    if(!empty($users)){
                        $args = array($option_data['name'], $users);
                        if(!empty($args) && count($args)){
                            wp_clear_scheduled_hook($timestamp,'wplms_send_wplms_bbb_reminders',$args);
                            if(!wp_next_scheduled('wplms_send_wplms_bbb_reminders',$args)){
                                wp_schedule_single_event($timestamp,'wplms_send_wplms_bbb_reminders',$args);
                            }
                        }
                    }
                }
            }

        }

        function reset_crons_on_edit($old_options_data,$option_data,$meeting_id){
            if(empty($meeting_id) || empty($option_data))
                return;
            if(!empty($option_data['start_date'])){
                $start_time = strtotime($option_data['start_date'].' '.$option_data['start_time']);
                $timestamp = 0;
                $offset = $this->offset;
                $utc_time = time();
                $reminder = ($option_data['reminder']['duration']*$option_data['reminder']['parameter']);
                if($offset > 0){//means gmt offset is in positive
                    $start_time = $start_time - (abs($offset)*60*60);
                    $utc_time = $utc_time - (abs($offset)*60*60);
                    if($start_time > $reminder){
                       $timestamp = $start_time - $reminder;
                    }

                }else{//means gmt offset is in negative
                    $start_time = $start_time + (abs($offset)*60*60);
                    $utc_time = $utc_time + (abs($offset)*60*60);
                    if($start_time > $reminder){
                       $timestamp = $start_time - $reminder;
                    }
                }
       
                if(  $timestamp  > $utc_time){
                    $users = array();
                    if(!empty($option_data['restrictions']['scope'])){
                        $old_users = $this->users_from_restriction($old_options_data);
                        $users = $this->users_from_restriction($option_data);
                        
                    }
                    if(!empty($old_users)){
                        $old_args = array($old_options_data['name'], $old_users);
                        wp_clear_scheduled_hook('wplms_send_wplms_bbb_reminders',$old_args);
                    }
                    
                    if(!empty($users)){
                        $args =  array($option_data['name'], $users);
                        if(!wp_next_scheduled('wplms_send_wplms_bbb_reminders',$args))
                            wp_schedule_single_event($timestamp,'wplms_send_wplms_bbb_reminders',$args);
                    }
                }
            }
        }

        function users_from_restriction($option_data,$check_access=0){
            if(empty($option_data['restrictions']['scope']))
                return;
            $users = array();
            switch($option_data['restrictions']['scope']){
                case 'course_students':
                    if(!empty($option_data['restrictions']['data']) &&  function_exists('bp_course_get_course_students')){
                        //there are no multiple courses its just course id saved in array loop will run only once
                        foreach ($option_data['restrictions']['data'] as $course) {
                            $course_students = bp_course_get_course_students($course,'',9999999);
                        }
                        if(!empty($course_students) && count($course_students)){
                            $users = $course_students['students'];
                        }
                    }
                break;
                case 'selected_users':
                    if(!empty($option_data['restrictions']['data']) && count($option_data['restrictions']['data'])){
                        $users = $option_data['restrictions']['data'];
                    }
                break;
                case 'instructors':
                    $no=999;
                    $args = apply_filters('wplms_allinstructors',array(
                                    'role' => 'instructor', // instructor
                                    'number' => $no, 
                                    'orderby' => 'post_count', 
                                    'order' => 'DESC' 
                                ));

                    $user_query = new WP_User_Query( $args );

                    $args = apply_filters('wplms_alladmins',array(
                                    'role' => 'administrator', // instructor
                                    'number' => $no, 
                                    'orderby' => 'post_count', 
                                    'order' => 'DESC' 
                                ));
                    $flag = apply_filters('wplms_show_admin_in_instructors',1);
                    if(isset($flag) && $flag)
                        $admin_query = new WP_User_Query( $args );

                    $instructors=array();
                    if ( isset($admin_query) && !empty( $admin_query->results ) ) {
                        foreach ( $admin_query->results as $user ) {
                            $instructors[]=$user->ID;
                        }
                    }
                    if (!empty( $user_query->results ) ) {
                        foreach ( $user_query->results as $user ) {
                            $instructors[]=$user->ID;
                        }
                    }
                    $users =$instructors;
                break;
                case 'logged_in':
                case 'everyone':
                    if(!$check_access){
                       $role_not_in = apply_filters('wplms_bbb_reminder_users_role_not_in',array());
                        $exclude = apply_filters('wplms_bbb_reminder_users_exclude',array());
                        $args = array(
                            'role__not_in' => $role_not_in,
                            'exclude'      => $exclude,
                         ); 
                        $role_not_in = apply_filters('wplms_bbb_reminder_users_role_not_in',array());
                        $exclude = apply_filters('wplms_bbb_reminder_users_exclude',array());
                        $args = array(
                            'role__not_in' => $role_not_in,
                            'exclude'      => $exclude,
                         ); 
                        $students = get_users( $args );
                        foreach ($students as $student) {
                             $users[] = $student->ID;
                        }
                    }else{
                        $users = apply_filters('wplms_bbb_logged_in',array(get_current_user_id()));
                    }
                break;
                case 'group':
                if( function_exists('bp_is_active') && bp_is_active( 'groups' ) ){
                    if(!empty($option_data['restrictions']['data'])){
                        $per_page = apply_filters('wplms_bbb_group_members_per_page',9999,$option_data);
                        //there are no multiple courses its just course id saved in array loop will run only once
                        foreach ($option_data['restrictions']['data'] as $group) {
                            $has_members_str = "group_id=" . $group.'&per_page='.$per_page.'&exclude_admins_mods=0';

                            if ( bp_group_has_members( $has_members_str ) ): 
                                
                            while ( bp_group_members() ) : bp_group_the_member();
                                $users[] = bp_get_group_member_id();
                            endwhile;
                            endif;
                        }
                    }
                }
                    
                break;
                default:
                    $users = apply_filters('wplms_bbb_logged_in',array(get_current_user_id()));
                break;
            }
            if(is_array($users)){
                $users[] = $option_data['author'];
            }
            
            return $users;
        }

        function wplms_send_wplms_bbb_reminders($meeting_name,$users){
            $bpargs = array(
                'tokens' => array('meeting.name' => $meeting_name),
            );
         
            foreach ($users as  $user) {
                $user = get_user_by('id',$user);
                bp_send_email( 'wplms_bbb_reminder',$user->user_email, $bpargs );  
            }
            wp_clear_scheduled_hook('wplms_send_wplms_bbb_reminders',array($meeting_name,$users));
        }

        function create_new_meeting(){
            if(!is_user_logged_in() || (is_user_logged_in() && !current_user_can('edit_posts'))){
                return;
            }
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bbb_meetings'.$user_id) ){
                echo 'Security check failed !';
                die();
            }
            $data = stripcslashes($_POST['data']);
            $data = json_decode($data);

            $processed_data = array();
            if(!empty($data) && defined('BIGBLUEBUTTON_VERSION') &&  class_exists('Bigbluebutton_Admin_Helper')){
                foreach ($data as $key => $value) {
                    if($value->field == 'mduration' || $value->field == 'mdurationparam' || $value->field == 'mdrduration' || $value->field == 'mdrdurationparam'  ){
                        if(is_numeric($value->value)){
                            $processed_data[$value->field] = $value->value;
                        }
                    }elseif($value->field == 'mrusers'){
                        if(is_array($value->value)){
                            foreach ($value->value as $k => $v) {
                                if(!is_numeric($v)){
                                    unset($value->value[$k]);
                                }
                            }
                            $processed_data[$value->field] = $value->value;
                        }
                    }elseif($value->field == 'mrcourses'){
                        if(is_numeric($value->value)){
                          $processed_data[$value->field] = $value->value;  
                        }
                    }else{
                        $processed_data[$value->field] = sanitize_text_field($value->value);
                    }
                }
                $data =  $processed_data;
                if($data[ 'attendeePW' ] == $data[ 'moderatorPW' ] ){
                    echo '<div class="message error notice"><p>'._x('Moderator password and attendee passwards cannot be same.','','wplms-bbb').'</p></div>';
                    die();
                }



                
                $moderator_code = $data[ 'moderatorPW' ] ;
                $viewer_code    = $data[ 'attendeePW' ] ;
                $recordable     = (empty($data['recorded'])?false:true);

                $wait_for_mod = (empty($data['waitForModerator'])?false:true);

                // Ensure neither code is empty.
                if ( '' == $moderator_code ) {
                    $moderator_code = Bigbluebutton_Admin_Helper::generate_random_code();
                }
                if ( '' == $viewer_code ) {
                    $viewer_code = Bigbluebutton_Admin_Helper::generate_random_code();
                }

                $args = array(
                    'post_author'           => $user_id,
                    'post_title'            => $data['meetingName'],
                    'post_status'           => 'publish',
                    'post_type'             => 'bbb-room',
                );

                $post_id = wp_insert_post($args);
                if(!empty($post_id)){
                   // Add room codes to postmeta data.
                    update_post_meta( $post_id, 'bbb-room-moderator-code', $moderator_code );
                    update_post_meta( $post_id, 'bbb-room-viewer-code', $viewer_code );

                    if ( ! get_post_meta( $post_id, 'bbb-room-meeting-id', true ) ) {
                        update_post_meta( $post_id, 'bbb-room-meeting-id', sha1( home_url() . Bigbluebutton_Admin_Helper::generate_random_code( 12 ) ) );
                    }
                    

                    // Update room recordable value.
                    update_post_meta( $post_id, 'bbb-room-recordable', ( $recordable ? 'true' : 'false' ) );
                    update_post_meta( $post_id, 'bbb-room-wait-for-moderator', ( $wait_for_mod ? 'true' : 'false' ) );
                    if(!empty($post_id) && !empty($data['meetingName'])){
                        //add this meeting in options table 
                        $option_data = array(
                                        'id' => $post_id,
                                        'name'=>$data['meetingName']
                        );
                        $option_data['author'] = $user_id;
                        if(!empty($data['mduration']) && !empty($data['mdurationparam'])){
                           $option_data['duration'] = array(
                                                        'duration'=>$data['mduration'],
                                                        'parameter'=>$data['mdurationparam']);
                        }
                        if(!empty($data['mrestriction'])){

                           $restrictions_array = array(
                                'scope' => $data['mrestriction'],
                            );
                           if(!empty($data['mrcourses']) && $data['mrestriction'] == 'course_students'){
                                $restrictions_array['data'] = array($data['mrcourses']);
                                $option_data['course'] = $data['mrcourses'];
                           }elseif(!empty($data['mrusers']) && $data['mrestriction'] == 'selected_users'){
                               $restrictions_array['data'] = $data['mrusers'];
                           }elseif(!empty($data['mrgroups']) && $data['mrestriction'] == 'group'){
                                $restrictions_array['data'] = array($data['mrgroups']);
                                $option_data['group'] = $data['mrgroups'];
                           }
                           $option_data['restrictions'] = $restrictions_array;
                        }
                        if(!empty($data['mdr'])){
                            if(!empty($data['mdrduration']) && !empty($data['mdrdurationparam'])){
                               $option_data['reminder'] = array(
                                                        'duration'=>$data['mdrduration'],
                                                        'parameter'=>$data['mdrdurationparam']);
                            }
                        }
                        if(!empty($data['m_date'])){
                            $option_data['start_date'] = $data['m_date'];
                        }
                        if(!empty($data['m_time'])){
                            $option_data['start_time'] = $data['m_time'];
                        }
                        $existing = $this->wplms_bbb_meetings;
                        if(empty($existing)){
                            $existing = array();
                        }

                        if(!empty($option_data) && is_array($option_data)){
                            $existing[$post_id] = $option_data;
                            update_option('wplms_bbb_meetings',$existing);
                            $out .= '<div class="updated">
                                    <p>
                                    <strong>'._x('Meeting Room Created','','wplms-bbb').'</strong>
                                    </p>
                                    </div>';
                            do_action('wplms_bbb_meeting_created',$option_data);
                        }

                    }
                }
            }
            echo $out;
            die();
        }

        function edit_meeting(){
            if(!is_user_logged_in() || (is_user_logged_in() && !current_user_can('edit_posts'))){
                die();
            }

            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || empty($_POST['meeting_id']) || !wp_verify_nonce($_POST['security'],'edit_meeting'.$user_id.$_POST['meeting_id']) ){
                echo 'Security check failed !';
                die();
            }
           
            $meeting_id = sanitize_text_field($_POST['meeting_id']);
            $meeting = $this->get_meeting($meeting_id);

            if(empty($meeting) || empty($meeting['author']))
                die();
            $flag  = 0;
            if(function_exists('vibe_get_option')){
                $inst_privacy_enabled = vibe_get_option('instructor_content_privacy');
                if(empty($inst_privacy_enabled)){
                   $flag = 1; 
                }elseif(!empty($inst_privacy_enabled) && ($meeting['author'] == $user_id)){
                    $flag = 1;
                }
            }
            if($flag || current_user_can('manage_options')){
                $data = stripcslashes($_POST['data']);
                $data = json_decode($data);

                $processed_data = array();
                if(!empty($data)){
                       foreach ($data as $key => $value) {
                        if($value->field == 'mduration' || $value->field == 'mdurationparam' || $value->field == 'mdrduration' || $value->field == 'mdrdurationparam'  ){
                            if(is_numeric($value->value)){
                                $processed_data[$value->field] = $value->value;
                            }
                        }elseif($value->field == 'mrusers'){
                            if(is_array($value->value)){
                                foreach ($value->value as $k => $v) {
                                    if(!is_numeric($v)){
                                        unset($value->value[$k]);
                                    }
                                }
                                $processed_data[$value->field] = $value->value;
                            }
                        }elseif($value->field == 'mrcourses'){
                            if(is_numeric($value->value)){
                              $processed_data[$value->field] = $value->value;  
                            }
                        }else{
                            $processed_data[$value->field] = sanitize_text_field($value->value);
                        }
                    } 
                }
                
                $data =  $processed_data; 
                if($data[ 'attendeePW' ] == $data[ 'moderatorPW' ] ){
                    echo '<div class="message error notice"><p>'._x('Moderator password and attendee passwards cannot be same.','','wplms-bbb').'</p></div>';
                    die();
                }
                $option_data = array();
                if(!empty($meeting['id']) && !empty($data['meetingName'])){
                    $option_data = array(
                                    'id' => $meeting['id'],
                                    'name'=>$data['meetingName']
                    );
                    if(!empty($meeting['author'])){
                        $option_data['author'] = $meeting['author'];
                    }else{
                        $option_data['author'] = $user_id; 
                    }
                    
                    if(!empty($data['mduration']) && !empty($data['mdurationparam'])){
                       $option_data['duration'] = array(
                                                    'duration'=>$data['mduration'],
                                                    'parameter'=>$data['mdurationparam']);
                    }
                    if(!empty($data['mrestriction'])){

                       $restrictions_array = array(
                            'scope' => $data['mrestriction'],
                        );
                       if(!empty($data['mrcourses']) && $data['mrestriction'] == 'course_students'){
                            $restrictions_array['data'] = array($data['mrcourses']);
                            $option_data['course'] = $data['mrcourses'];
                       }elseif(!empty($data['mrusers']) && $data['mrestriction'] == 'selected_users'){
                           $restrictions_array['data'] = $data['mrusers'];
                       }elseif(!empty($data['mrgroups']) && $data['mrestriction'] == 'group'){
                            $restrictions_array['data'] = array($data['mrgroups']);
                            $option_data['group'] = $data['mrgroups'];
                       }
                       $option_data['restrictions'] = $restrictions_array;
                    }


                    if(!empty($data['mdr'])){
                        if(!empty($data['mdrduration']) && !empty($data['mdrdurationparam'])){
                           $option_data['reminder'] = array(
                                                    'duration'=>$data['mdrduration'],
                                                    'parameter'=>$data['mdrdurationparam']);
                        }
                    }

                    
                    if(!empty($data['m_date'])){
                        $option_data['start_date'] = $data['m_date'];
                    }
                    if(!empty($data['m_time'])){
                        $option_data['start_time'] = $data['m_time'];
                    }


                    $existing = $this->wplms_bbb_meetings;
                    if(empty($existing)){
                        $existing = array();
                    }
                    if(!empty($option_data) && is_array($option_data)){
                        
                        $existing[$meeting_id] = $option_data;
                        update_option('wplms_bbb_meetings',$existing);


                        $moderator_code = $option_data[ 'moderatorPW' ] ;
                        $viewer_code    = $option_data[ 'attendeePW' ] ;
                        $recordable     = (empty($option_data['recorded'])?false:true);

                        $wait_for_mod = (empty($option_data['waitForModerator'])?false:true);

                        // Ensure neither code is empty.
                        if ( '' == $moderator_code ) {
                            $moderator_code = Bigbluebutton_Admin_Helper::generate_random_code();
                        }
                        if ( '' == $viewer_code ) {
                            $viewer_code = Bigbluebutton_Admin_Helper::generate_random_code();
                        }

                        $args = array(
                            'ID'           => $meeting_id,
                            'post_title'   => $data['meetingName'],
                        );

                        $post_id = wp_update_post($args);
                        if(!empty($post_id)){
                           // Add room codes to postmeta data.
                            update_post_meta( $post_id, 'bbb-room-moderator-code', $moderator_code );
                            update_post_meta( $post_id, 'bbb-room-viewer-code', $viewer_code );

                            if ( ! get_post_meta( $post_id, 'bbb-room-meeting-id', true ) ) {
                                update_post_meta( $post_id, 'bbb-room-meeting-id', sha1( home_url() . Bigbluebutton_Admin_Helper::generate_random_code( 12 ) ) );
                            }
                            

                            // Update room recordable value.
                            update_post_meta( $post_id, 'bbb-room-recordable', ( $recordable ? 'true' : 'false' ) );
                            update_post_meta( $post_id, 'bbb-room-wait-for-moderator', ( $wait_for_mod ? 'true' : 'false' ) );
                            echo '<div class="updated"><p><strong>'._x('Meeting Room Edited','','wplms-bbb').'</strong></p></div>';
                       
                            do_action('wplms_bbb_meeting_edited',$meeting,$option_data,$meeting_id);
                        }
                    }

                }
            }else{
                echo '<div class="message notice error">You cannnot edit this meeting...</div>';
            }
            
            die();
        }

        function select_users_bbb(){
            if(!is_user_logged_in() || (is_user_logged_in() && !current_user_can('edit_posts'))){
                return;
            }
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bbb_meetings'.$user_id) ){
                echo 'Security check failed !';
                die();
            }
            global $wpdb;
            $term= '';
            if(!empty($_POST['q']['term'])){
                $term = sanitize_text_field($_POST['q']['term']);
            }
            
            $q = "
                SELECT ID, display_name FROM {$wpdb->users} 
                WHERE (
                    user_login LIKE '%$term%'
                    OR user_nicename LIKE '%$term%'
                    OR user_email LIKE '%$term%' 
                    OR user_url LIKE '%$term%'
                    OR display_name LIKE '%$term%'
                    )";
            $users = $wpdb->get_results($q);

            $user_list = array();
              // Check for results
            if (!empty($users)) {
                foreach($users as $user){
                    $user_list[] = array(
                      'id'=>$user->ID,
                      'image'=>bp_core_fetch_avatar(array('item_id' => $user->ID, 'type' => 'thumb', 'width' => 32, 'height' => 32, 'html'=>false)),
                      'text'=>$user->display_name
                    );
                }
                echo json_encode($user_list);
            } else {
                echo json_encode(array('id'=>'','text'=>_x('No Users found !','No users found in Course - admin - add users area','wplms-bbb')));
            }
            die();
        }

        function wplms_bbb_create_meeting(){
            
            global $post;
            $ids = '';
            if(is_object($post) && !empty($post) && is_admin() ){
               if( get_post_type($post->ID) == 'unit')
                $ids = $post->ID;
            }
            ?>
            <script>
            jQuery(document).ready(function($){
                $('#course_curriculum').on('active',function(){
                    $('.wplms-bbb-button').on('click',function () {
                    var unit_id = $(this).closest('.element_overlay').find('#save_element_button').attr('data-id');
                        $(this).find('#meeting_info_meta').val(unit_id);
                        $('body').trigger('meeting_meta_added',[{"unit_id":unit_id}]);
                    });
                });
            });
            </script>
            <?php
            echo '<a href="'.admin_url('media-upload.php?type=wplms_bbb_meetings&TB_iframe=true&tab=all_meetings').'" class="thickbox wplms-bbb-button button">
             <div class="dashicons dashicons-format-status"></div> '._x('Meetings','','wplms-bbb').'<input type="hidden" id="meeting_info_meta" name="meeting_info_meta" value="'.$ids.'"></a>
             <script>
             jQuery(document).ready(function($){
                $("body").on("meeting_meta_added",function(e, data){
                    jQuery( "body").on( "thickbox:iframe:loaded", function() {
                        if(typeof data.unit_id !== "undefined"){
                            $("#TB_iframeContent").contents().find(".insert_meeting").attr("data-meta",data.unit_id);
                        }else{
                            $("#TB_iframeContent").contents().find(".insert_meeting").attr("data-meta",$("#meeting_info_meta").val());
                        }
                        
                    });  
                });
                
             });
             </script>';
        }

        function media_bbb_create_meeting(){

            if(isset($_GET['tab']) && $_GET['tab']=='create'){
                
                wp_iframe( array($this,"media_create_meeting_content" ));
            }
            elseif(isset($_GET['tab']) && $_GET['tab']=='edit' && isset($_GET['meeting'])){
                wp_iframe( array($this,"media_edit_meeting_content" ));
            }else{
                wp_iframe( array($this,"media_all_meetings_form" ));
            }
        }

        function bbb_tabs($tabs) {
            
            $newtab1 = array('create' => __('Add meeting','wplms-bbb'));
            $newtab2 = array('all_meetings' => __('All meetings','wplms-bbb'));
            if( isset($_GET['meeting'])){
             $newtab3 = array('edit' => __('Edit meeting','wplms-bbb'));
             return array_merge(   $newtab3, $newtab2,$newtab1);
            }
            return array_merge( $newtab2,$newtab1);
        }

        function print_tabs(){ 
            add_filter('media_upload_tabs', array($this,'bbb_tabs'));
            media_upload_header();
        }

        function wplms_bbb_shortcode($atts, $content = null){

            extract(shortcode_atts(array(
                    'token' => '',
                    'popup' => '',
                    'size' => null,
                    ), $atts));
            if(empty($token) || empty($this->wplms_bbb_meetings))
                return;
            $meeting = $this->get_meeting($token);
            if(empty($meeting ))
                return;
            $user_id = get_current_user_id();
            $users = $this->users_from_restriction( $meeting,1);
            if(!empty($users) && !in_array($user_id , $users))
                return;

            $return = '';

            if(!empty($meeting['start_date'])){
                $start_time = strtotime($meeting['start_date'].' '.$meeting['start_time']);
                $actual_start_time = $start_time = strtotime($meeting['start_date'].' '.$meeting['start_time']);
                $timestamp = 0;
                $offset = $this->offset;
                $utc_time = time();
                if($offset > 0){//means gmt offset is in positive
                    $start_time = $start_time - (abs($offset)*60*60);
                    $utc_time = $utc_time - (abs($offset)*60*60);

                }else{//means gmt offset is in negative
                    $start_time = $start_time + (abs($offset)*60*60);
                    $utc_time = $utc_time + (abs($offset)*60*60);
                }
                    
            }
            $meeting_expiry_time = $start_time + ($meeting['duration']['duration']*$meeting['duration']['parameter']);
             $meeting_expiry_time_gmt = $actual_start_time + ($meeting['duration']['duration']*$meeting['duration']['parameter']);
            if($meeting_expiry_time <= time()){
                $format = get_option( 'date_format' ).' '.get_option('time_format');;
                $display_expire_time = date_i18n($format ,$meeting_expiry_time_gmt );
                $return .='<div class="message">'. sprintf(_x('Meeting Expired on %s','meeting expired','wplms-bbb'),$display_expire_time).'</div><br>';
            }else{
                //$utc_time is current time adjusted with gmt
                if( $start_time < time()){ //show meeting iframe   
                    $return .= '<div class="bbb_meeting_wrapper '.$meeting['id'].'">';
                        $return .=  $this->meeting_form($meeting,$popup,$size);
                    $return .=  '</div>';

                }else{ //show conutown and do ajax call 
                    $return .=  '<div class="bbb_meeting_wrapper waiting '.$meeting['id'].'">'.do_shortcode('[countdown_timer event="'.$meeting['id'].'" seconds="'.( $start_time-time()).'" size="'.((!empty($size) && is_numeric($size))?$size:3).'"]').'</div>';
                 
                    $return .= "<script>
                        //ajax call for fetch meeting iframe open meeting
                        jQuery(document).ready(function($){
                            $('body').on('".$meeting['id']."',function(){
                                //make call fetch meeting
                                $.ajax({
                                    type: 'POST',
                                    url: ajaxurl,
                                    data: { action: 'fetch_meeting_iframe', 
                                            security: '".wp_create_nonce('bbb_user_meeting'.$user_id)."',
                                            popup:'".$popup."',
                                            meeting_id:'".$meeting['id']."',
                                          },
                                    cache: false,
                                    success: function (html) {
                                       console.log(html);
                                       $('.bbb_meeting_wrapper.".$meeting['id']."').html(html);
                                       $('body').trigger('wplms_bbb_button_loaded');
                                    }   
                                });
                            });
                        });
                        </script>";
                }
            }

           return $return; 
        }

        function meeting_form($meeting,$popup=null,$size=null){
            if(!is_user_logged_in())
                return;
            if(empty($meeting['id']))
                return;
            $user = wp_get_current_user();
            $password = '';
            $meeting_post = get_post($meeting['id']);
            $attendeepassword = get_post_meta($meeting['id'],'bbb-room-viewer-code',true);
            $moderatorpw = get_post_meta($meeting['id'],'bbb-room-moderator-code',true);
            $is_recorded = get_post_meta($meeting['id'],'bbb-room-recordable',true);
            $waitfor_moderator = get_post_meta($meeting['id'],'bbb-room-wait-for-moderator',true);
            $check_author_admin_for_meeting = 0;
            if($meeting_post->post_author == $user->ID || is_super_admin()){
                $check_author_admin_for_meeting = 1;
                $password = $moderatorpw;
            }else{
                $password = $attendeepassword;
            }
            
            /*global $wpdb, $wp_roles, $wp_version, $current_site, $current_user;
            $user_id = get_current_user_id();
            $dataSubmitted = true;
            $meetingExist = true;
            $url_val = get_option('bigbluebutton_url');
            $salt_val = get_option('bigbluebutton_salt');
            $permissions = get_option('bigbluebutton_permissions');

            $logouturl = admin_url('admin-ajax.php').'?action=meeting_logout&meeting='.$meeting['id'].'&meeting_security='.wp_create_nonce('meeting_logout'.$user_id);
            $meetingID = $meeting['id'];
            $role = null;
            if( $current_user->ID ) {
                $role = "unregistered";
                foreach($wp_roles->role_names as $_role => $Role) {
                    if (array_key_exists($_role, $current_user->caps)) {
                        $role = $_role;
                        break;
                    }
                }
            } else {
                $role = "anonymous";
            }
            $table_name = $wpdb->prefix . "bigbluebutton";
            $table_logs_name = $wpdb->prefix . "bigbluebutton_logs";
            $sql = "SELECT * FROM ".$table_name." WHERE meetingID = %s";
            $found = $wpdb->get_row(
                    $wpdb->prepare($sql, $meetingID)
            );*/
            /*if( $found ) {
                $found->meetingID = bigbluebutton_normalizeMeetingID($found->meetingID);

                if( !$current_user->ID ) {
                    $name = _x('Anonymous','','wplms-bbb');
                    
                    if( bigbluebutton_validate_defaultRole($role, 'none') ) {
                        $password = sanitize_text_field($_POST['pwd']);
                    } else {
                        $password = $permissions[$role]['defaultRole'] == 'none'? $found->moderatorPW: $found->attendeePW;
                    }
                        
                } else {
                    if( $current_user->display_name != '' ) {
                        $name = $current_user->display_name;
                    } else if( $current_user->user_firstname != '' || $current_user->user_lastname != '' ) {
                        $name = $current_user->user_firstname != ''? $current_user->user_firstname.' ': '';
                        $name .= $current_user->user_lastname != ''? $current_user->user_lastname.' ': '';
                    } else if( $current_user->user_login != '') {
                        $name = $current_user->user_login;
                    } else {
                        $name = $role;
                    }
                    if( bigbluebutton_validate_defaultRole($role, 'none') ) {
                        $password = sanitize_text_field($_POST['pwd']);
                    } else {
                        $password = $permissions[$role]['defaultRole'] == 'moderator'? $found->moderatorPW: $found->attendeePW;
                    }

                }

                //Extra parameters
                $recorded = $found->recorded;
                $welcome = (isset($args['welcome']))? html_entity_decode($args['welcome']): BIGBLUEBUTTON_STRING_WELCOME;
                if( $recorded ) $welcome .= BIGBLUEBUTTON_STRING_MEETING_RECORDED;
                $duration = 0;
                $voicebridge = (isset($args['voicebridge']))? html_entity_decode($args['voicebridge']): 0;
                

                //Metadata for tagging recordings
                $metadata = Array(
                        'meta_origin' => 'WordPress',
                        'meta_originversion' => $wp_version,
                        'meta_origintag' => 'wp_plugin-bigbluebutton '.BIGBLUEBUTTON_PLUGIN_VERSION,
                        'meta_originservername' => home_url(),
                        'meta_originservercommonname' => get_bloginfo('name'),
                        'meta_originurl' => $logouturl
                );
                //Call for creating meeting on the bigbluebutton server
                $response = BigBlueButton::createMeetingArray($name, $found->meetingID, $found->meetingName, $welcome, $found->moderatorPW, $found->attendeePW, $salt_val, $url_val, $logouturl, $recorded? 'true':'false', $duration, $voicebridge, $metadata );
                //Analyzes the bigbluebutton server's response
                if(!$response || $response['returncode'] == 'FAILED' ) {//If the server is unreachable, or an error occured
                    $out .= "Sorry an error occured while joining the meeting.";
                    return $out;
                     
                } else{ //The user can join the meeting, as it is valid
                    if( !isset($response['messageKey']) || $response['messageKey'] == '' ) {
                        // The meeting was just created, insert the create event to the log
                        $rows_affected = $wpdb->insert( $table_logs_name, array( 'meetingID' => $found->meetingID, 'recorded' => $found->recorded, 'timestamp' => time(), 'event' => 'Create' ) );
                    }
                    $bigbluebutton_joinURL = BigBlueButton::getJoinURL($found->meetingID, $name, $password, $salt_val, $url_val );
                    //If the meeting is already running or the moderator is trying to join or a viewer is trying to join and the
                    //do not wait for moderator option is set to false then the user is immediately redirected to the meeting
                 */   
            if (  $check_author_admin_for_meeting || !$waitfor_moderator  || Bigbluebutton_Api::is_meeting_running($meeting['id'])) {
                    //If the password submitted is correct then the user gets redirected
                
                $bigbluebutton_joinURL = Bigbluebutton_Api::get_join_meeting_url( $meeting['id'], (!empty($user->display_name)?$user->display_name:(!empty($user->user_login)?$user->user_login:$user->user_email)), $password, site_url() );
                
                if(!empty($bigbluebutton_joinURL)){
                    if(empty($popup)){
                        if(!$this->open_in_new_tab){
                            $out .=  do_shortcode('[iframe]'.$bigbluebutton_joinURL.'[/iframe]');
                            do_action('wplms_bbb_user_meeting_join',$meeting['id']);
                        }else{
                            $out .=  '<a class="button" target="_blank" href="'.$bigbluebutton_joinURL.'">'.__('Open Meeting','wplms-bbb').'</a>';
                            do_action('wplms_bbb_user_meeting_join',$meeting['id']);
                        }
                        
                    }else{
                        $out .= '<script>
                        jQuery(document).ready(function(){
                            jQuery(".wplms-bbb-meeting-popup").magnificPopup({type: "iframe",midclick:false});
                            jQuery("body").on("wplms_bbb_button_loaded",function(){
                                jQuery(".wplms-bbb-meeting-popup").magnificPopup({type: "iframe",midclick:false});
                            
                                jQuery("body").delegate(".wplms-bbb-meeting-popup","click",function(){
                                    jQuery.ajax({
                                        type: "POST",
                                        url: ajaxurl,
                                        async:true,
                                        data: { action: "join_bbb_wplms_bbb_do_action", 
                                                security: "'.wp_create_nonce('join_bbb_wplms_bbb_do_action').'",
                                                meeting_id:"'.$meeting['id'].'",
                                              },
                                        cache: false,
                                        
                                    });
                                });
                            });
                            
                        });
                        </script>' ;
                        if(!$this->open_in_new_tab){
                            $out .= '<a class="wplms-bbb-meeting-popup button" href="'.$bigbluebutton_joinURL.'">'.__('Open Meeting','wplms-bbb').'</a>';
                            do_action('wplms_bbb_user_meeting_join',$meeting['id']);
                        }else{
                            $out .=  '<a class="button" target="_blank" href="'.$bigbluebutton_joinURL.'">'.__('Open Meeting','wplms-bbb').'</a>';
                            do_action('wplms_bbb_user_meeting_join',$meeting['id']);
                        }
                        
                    }
                }
                return $out;
            }
            //If the viewer has the correct password, but the meeting has not yet started they have to wait 
            //for the moderator to start the meeting
            else {
                
                $out .= '<div class="message">'._x('Moderator has not joined the meeting yet!','','wplms-bbb').'</div>';
                return $out;
            }
                
            
        }

        function wplms_bigbluebutton_display_redirect_script($meeting,$bigbluebutton_joinURL, $meetingID, $meetingName, $name,$popup=null) {
            if(empty($popup)){
                  $embed =  do_shortcode('[iframe]'.$bigbluebutton_joinURL.'[/iframe]');
            }else{
                $embed  .= '<a class="wplms-bbb-meeting-popup button" href="'.$bigbluebutton_joinURL.'">'.__('Open Meeting','wplms-bbb').'</a>';
            }
            $out .= '
            <script type="text/javascript">';
            if(!empty($popup)){
            $out .= 'jQuery(document).ready(function(){
                        jQuery(".wplms-bbb-meeting-popup").magnificPopup({type: "iframe",midclick:false});
                        jQuery("body").on("wplms_bbb_button_loaded",function(){
                            jQuery(".wplms-bbb-meeting-popup").magnificPopup({type: "iframe",midclick:false});
                        });
                    });';
            }

            $out .= 'function wplms_bigbluebutton_ping() {
                    jQuery.ajax({
                        url : "'.plugins_url('bigbluebutton/php/broker.php?action=ping&meetingID='.urlencode($meetingID)).'",
                        async : true,
                        dataType : "xml",
                        success : function(xmlDoc) {
                            $xml = jQuery( xmlDoc ), $running = $xml.find( "running" );
                            if($running.text() == "true") {
                                var meeting_content = \''.$embed.'\';

                                jQuery("body").find(".'.$meeting['id'].'.bbb_meeting_wrapper").html(meeting_content);
                                clearInterval(wplms_bbb_interval);
                                jQuery.ajax({
                                    type: "POST",
                                    url: ajaxurl,
                                    async:true,
                                    data: { action: "join_bbb_wplms_bbb_do_action", 
                                            security: "'.wp_create_nonce('join_bbb_wplms_bbb_do_action').'",
                                            meeting_id:"'.$meeting['id'].'",
                                          },
                                    cache: false,
                                    
                                });
                                jQuery("body").trigger("wplms_bbb_button_loaded");
                            }
                        },
                        error : function(xmlHttpRequest, status, error) {
                            console.debug(xmlHttpRequest);
                        }
                    });
                }
                var wplms_bbb_interval = setInterval("wplms_bigbluebutton_ping()", 15000);
            </script>';

            $out .= '
            <table>
              <tbody>
                <tr>
                  <td>
                    '.sprintf(_x("Welcome %s!","","wplms-bbb"),$name).'<br /><br />
                    '.sprintf(_x("%s session has not been started yet.","","wplms-bbb"),$meetingName).'
                    <br /><br />
                    <div align="center"><img src="'.plugins_url('bigbluebutton/images/polling.gif').'" /></div><br />
                    '._x("(Meeting will be loaded automatically.)","","wplms-bbb").'
                    
                  </td>
                </tr>
              </tbody>
            </table>';

            return $out;
        }

        function fetch_meeting_iframe(){
            if(!is_user_logged_in())
                die();
            $user_id = get_current_user_id();
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'bbb_user_meeting'.$user_id) || empty($_POST['meeting_id'])){
                echo 'Security check failed !';
                die();
            }
            $meeting_id = sanitize_text_field($_POST['meeting_id']);
            $meeting = $this->get_meeting($meeting_id );
            $popup = 0;
            if(!empty($_POST['popup']) && is_numeric($_POST['popup'])){
                $popup = sanitize_text_field($_POST['popup']);
            }
            echo $this->meeting_form($meeting,$popup);
            die();
        }

        function print_js(){
            if(defined('VIBE_PLUGIN_URL')){
                if(!is_user_logged_in())
                    return;
                $user_id = get_current_user_id();
                wp_enqueue_script('customselect2-bbb',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/js/select2.min.js');
                wp_enqueue_style('customselect2-bbb',VIBE_PLUGIN_URL.'/vibe-customtypes/metaboxes/css/select2.min.css');
                wp_enqueue_style( 'wplms-bbb-css', plugins_url( '../css/wplms-bbb.css' , __FILE__ ));
                wp_enqueue_style( 'meta_box_css', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/css/meta_box.css');
               wp_enqueue_script('bbb-meetings', plugins_url( '../js/wplms-bbb.js' , __FILE__ ),array('jquery'));
                $translation_array = array( 
                'ajax_url' => admin_url( 'admin-ajax.php' ) ,
                'more_chars'=> __( 'Please enter more characters','wplms-bbb'),
                'security' => wp_create_nonce('bbb_meetings'.$user_id),
                'vibe_security'=>wp_create_nonce('vibe_security'),
                'creating' => _x('Adding...','','wplms-bbb'),
                'editing' => _x('Editing...','','wplms-bbb'),
                'deleting' => _x('Deleting...','','wplms-bbb'),
                'sure' => _x('Are you sure you want to delete this meeting?','','wplms-bbb'),
                'required_warning' => _x('Please fill all required fields','','wplms-bbb'),
                );
                wp_enqueue_script( 'jquery-ui-datepicker', array( 'jquery', 'jquery-ui-core' ) );
                wp_enqueue_script( 'timepicker_box', VIBE_PLUGIN_URL . '/vibe-customtypes/metaboxes/js/jquery.timePicker.min.js', array( 'jquery' ) );
                wp_localize_script('bbb-meetings', 'bbb_meetings_strings', $translation_array );
                echo '<script type="text/javascript">
                        jQuery(document).ready(function() {
                            jQuery("#m_date").datepicker({
                            dateFormat: \'yy-mm-dd\'});
                            jQuery("input.timepicker").timePicker({});
                        });
                        </script><style>.create_meeting {padding:15px;}.all_meetings{padding:15px;}.edit_meeting{padding:15px;}</style>';
            }
        }

        function print_create(){

            $this->print_js();
            $options = array(
                array('value' =>'1','label'=>__('Seconds','wplms-bbb')),
                array('value' =>'60','label'=>__('Minutes','wplms-bbb')),
                array('value' =>'3600','label'=>__('Hours','wplms-bbb')),
                array('value' =>'86400','label'=>__('Days','wplms-bbb')),
                array('value' =>'604800','label'=>__('Weeks','wplms-bbb')),
                array('value' =>'2592000','label'=>__('Months','wplms-bbb')),
            );
            
            ?>
            <form id="wplms_bbb_create_form" name="form1" method="post" action="">
                <!-- course id here-->

                <table class="form-table" style="    width: 90%;">
                    <tbody>
                        <tr><td><?php echo _x('Meeting Room Name:','','wplms-bbb') ;?> </td><td><input class="bbb_create_m_field required" type="text" name="meetingName" value="" size="20"></td></tr>
                        <tr><td><?php echo _x('Meeting time:','','wplms-bbb') ;?> </td><td><input type="text" class="datepicker bbb_create_m_field required" name="m_date" id="m_date" value="" size="30" /><input type="text" class="timepicker bbb_create_m_field required" name="m_time" id="m_time" value="" size="30" autocomplete="OFF"/></td></tr>


                        <tr><td><?php echo _x('Meeting duration:','','wplms-bbb') ;?> </td><td> 
                            <input class="bbb_create_m_field required" type="number" name="mduration">
                            <select class="bbb_create_m_field required" name="mdurationparam">
                                <?php 
                                foreach ( $options as $option )
                                    echo '<option' . selected( esc_attr( $meta ), $option['value'], false ) . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
                                ?>
                            </select>

                        </td></tr>
                        <tr><td><?php echo _x('Restrictions:','','wplms-bbb') ;?> </td><td>
                        
                        <select class="bbb_create_m_field" name="mrestriction" id="mrestriction">
                            <?php
                            foreach ( $this->restrictions as $key =>  $option )
                                echo '<option value="' . $key . '">' . $option . '</option>';
                            ?>
                        </select>
                        <div class="mrcourses_div">
                            <select name="mrcourses" style="width: 50%"  class="mrcourses selectcoursecpt bbb_create_m_field" data-cpt="course" data-placeholder="<?php _x('Select Courses','','wplms-bbb'); ?>">
                                
                            </select>
                        </div>
                        <div class="mrgroups_div">
                            <select name="mrgroups" style="width: 50%"  class="selectgroup mrgroups selectgroupscpt bbb_create_m_field" data-cpt="groups" data-placeholder="<?php _x('Select Groups','','wplms-bbb'); ?>">
                                
                            </select>
                        </div>
                        <div class="mrusers_div">
                            <select name="mrusers"  style="width: 100%;" class="selectusers_bbb bbb_create_m_field" data-placeholder="<?php echo __('Enter Student Usernames/Emails, separated by comma','wplms-bbb')?>" multiple>
                            </select>
                        </div>
                        </td></tr>

                        <tr><td><?php echo _x('Attendee Password: ','','wplms-bbb') ;?></td><td><input class="bbb_create_m_field" type="text" name="attendeePW" value="" size="20"></td></tr>
                        <tr><td><?php echo _x('Moderator Password: ','','wplms-bbb') ;?> </td><td><input class="bbb_create_m_field" type="text" name="moderatorPW" value="" size="20"></td></tr>
                        <tr><td><?php echo _x('Wait for moderator to start meeting:  ','','wplms-bbb') ;?></td><td><input type="checkbox" class="bbb_create_m_field required" name="waitForModerator" value="True" /></td></tr>
                        <tr><td><?php echo _x('Recorded meeting:  ','','wplms-bbb') ;?></td><td> <input class="bbb_create_m_field" type="checkbox" name="recorded" value="True" /></td></tr>
                        <tr><td><?php echo _x('Enable reminders:  ','','wplms-bbb') ;?><br>
                           <span>(<?php echo _x('It will send email reminder to each student before the meeting.So please choose carefully','','wplms-bbb') ;?>)<span> 
                        </td><td><input class="bbb_create_m_field" type="checkbox" name="mdr" value="True" /></td></tr>
                        <tr class="reminder_duration"><td><?php echo _x('Time before reminders will be sent:','','wplms-bbb') ;?> </td><td> 
                            <input class="bbb_create_m_field" type="number" name="mdrduration">
                            <select class="bbb_create_m_field" name="mdrdurationparam">
                                <?php 
                                foreach ( $options as $option )
                                    echo '<option' . selected( esc_attr( $meta ), $option['value'], false ) . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
                                ?>
                            </select>

                        </td></tr>

                        <tr class="submit"><td><input type="submit" name="SubmitCreate" class="button-primary" value="<?php echo _x('Add Meeting ','','wplms-bbb') ;?>" /></td></tr>

                    </tbody>
                </table>
            </form>
            <div class="bbb_create_meeting_message"></div>
            <?php
        }

        function print_edit(){
            if(!is_user_logged_in())
                return;
            $user_id =get_current_user_id();
            $this->print_js();
            $options = array(
                array('value' =>'1','label'=>__('Seconds','wplms-bbb')),
                array('value' =>'60','label'=>__('Minutes','wplms-bbb')),
                array('value' =>'3600','label'=>__('Hours','wplms-bbb')),
                array('value' =>'86400','label'=>__('Days','wplms-bbb')),
                array('value' =>'604800','label'=>__('Weeks','wplms-bbb')),
                array('value' =>'2592000','label'=>__('Months','wplms-bbb')),
            );
            if(!empty($_GET['meeting'])){
                $meeting = $this->get_meeting($_GET['meeting']);
                global $wpdb;
                $meetingid = $_GET['meeting'];
                $table= $wpdb->prefix.'bigbluebutton';
                if(is_numeric($_GET['meeting'])){

                    $meeting_post = array();
                    $attendeepassword = get_post_meta($meetingid,'bbb-room-viewer-code',true);
                    $moderatorpw = get_post_meta($meetingid,'bbb-room-moderator-code',true);
                    $is_recorded = get_post_meta($meetingid,'bbb-room-recordable',true);
                    $waitfor_moderator = get_post_meta($meetingid,'bbb-room-wait-for-moderator',true);

                    $meeting_post = (object)array(
                        'attendeePW'=>$attendeepassword,
                        'moderatorPW'=>$moderatorpw,
                        'waitForModerator'=>(!empty($waitfor_moderator)?true:false),
                        'recorded'=>(!empty($is_recorded)?true:false),
                    );

                    $meeting['post'] = $meeting_post;  
                }
               
            }
            ?>
            <form id="wplms_bbb_create_form" name="form1" method="post" action="">
                <input type="hidden" class="wplm_bbb_meeting_id" value="<?php echo $meeting['id'];?>">
                 <input type="hidden" id="wplms_bbb_edit_meeting_security" value="<?php echo  wp_create_nonce('edit_meeting'.$user_id.$meeting['id'])?>">
                <table class="form-table" style="    width: 90%;">
                    <tbody>
                        <tr><td><?php echo _x('Meeting Room Name:','','wplms-bbb') ;?> </td><td><input class="bbb_create_m_field required" type="text" required name="meetingName" value="<?php echo (!empty($meeting['name'])?esc_attr($meeting['name']):'') ?>" size="20"></td></tr>
                        <tr><td><?php echo _x('Meeting time:','','wplms-bbb') ;?> </td><td><input type="text" class="datepicker bbb_create_m_field required" name="m_date" id="m_date" value="<?php echo (!empty($meeting['start_date'])?esc_attr($meeting['start_date']):'') ?>" size="30" /><input type="text" class="timepicker bbb_create_m_field required" name="m_time" id="m_time" value="<?php echo (!empty($meeting['start_time'])?esc_attr($meeting['start_time']):'') ?>" size="30" autocomplete="OFF"/></td></tr>


                        <tr><td><?php echo _x('Meeting duration:','','wplms-bbb') ;?> </td><td> 
                            <input class="bbb_create_m_field required" type="number" name="mduration" value="<?php echo (!empty($meeting['duration']['duration'])?esc_attr($meeting['duration']['duration']):'') ?>">
                            <select class="bbb_create_m_field required" name="mdurationparam">
                                <?php 
                                foreach ( $options as $option )
                                    echo '<option' . selected( esc_attr( $meeting['duration']['parameter'] ), $option['value'], false ) . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
                                ?>
                            </select>

                        </td></tr>
                        <tr><td><?php echo _x('Restrictions:','','wplms-bbb') ;?> </td><td>
                        
                        <select class="bbb_create_m_field" name="mrestriction" id="mrestriction">
                            <?php
                            foreach ( $this->restrictions as $key =>  $option )
                                echo '<option ' . selected( esc_attr( $meeting['restrictions']['scope'] ), $key , false ) . ' value="' . $key . '">' . $option . '</option>';
                            $show_course_select =0;
                            $show_students_select =0;
                            if($meeting['restrictions']['scope'] == 'course_students' && !empty($meeting['restrictions']['data'][0]))
                            {
                                $show_course_select = 1; 
                            }elseif($meeting['restrictions']['scope'] == 'selected_users' && !empty($meeting['restrictions']['data'])){
                                $show_students_select = 1; 
                            }elseif($meeting['restrictions']['scope'] == 'group' && !empty($meeting['restrictions']['data'])){
                                $show_goups_select = 1; 
                            }
                            ?>
                        </select>
                        <div class="mrcourses_div <?php echo (!empty($show_course_select)?'show':'')?>">
                            <select  name="mrcourses" style="width: 50%"  class="mrcourses selectcoursecpt bbb_create_m_field" data-cpt="course" data-placeholder="<?php _x('Select Courses','','wplms-bbb'); ?>">
                            <?php 
                            if(!empty($show_course_select) &&  !empty($meeting['restrictions']['data'][0])){
                                echo '<option value="'.esc_attr($meeting['restrictions']['data'][0]).'" selected="selected">'.get_the_title($meeting['restrictions']['data'][0]).'</option>';
                            }
                            ?>
                            </select>
                        </div>
                        <div class="mrgroups_div <?php echo (!empty($show_goups_select)?'show':'')?>"">
                            <select name="mrgroups" style="width: 50%"  class="selectgroup mrgroups selectgroupscpt bbb_create_m_field" data-cpt="groups" data-placeholder="<?php _x('Select Groups','','wplms-bbb'); ?>">
                            <?php 
                            if(!empty($show_goups_select) &&  !empty($meeting['restrictions']['data'][0])){
                                if(function_exists('groups_get_group')){
                                    $group = groups_get_group(esc_attr($meeting['restrictions']['data'][0]));
                                }
                                echo '<option value="'.esc_attr($meeting['restrictions']['data'][0]).'" selected="selected">'.(!empty($group)?$group->name:'').'</option>';
                            }
                            ?>   
                            </select>
                        </div>

                        <div class="mrusers_div <?php echo (!empty($show_students_select)?'show':'')?>">
                            <select name="mrusers"  style="width: 100%;" class="selectusers_bbb bbb_create_m_field" data-placeholder="<?php echo __('Enter Student Usernames/Emails, separated by comma','wplms-bbb')?>" multiple>
                            <?php
                            if(!empty($show_students_select) &&  !empty($meeting['restrictions']['data'])){
                                foreach ($meeting['restrictions']['data'] as $userid) {
                                   $user =  get_user_by('id',$userid);
                                   echo '<option value="'.$userid.'" selected="selected">'.bp_core_fetch_avatar(array('item_id' => $userid, 'type' => 'thumb', 'width' => 32, 'height' => 32)).''.bp_core_get_user_displayname($userid).'</option>';
                                }
                            }
                            ?>

                            </select>
                        </div>
                        </td></tr>

                        <tr><td><?php echo _x('Attendee Password: ','','wplms-bbb') ;?></td><td><input class="bbb_create_m_field" type="text" name="attendeePW" value="<?php echo (!empty($meeting['post']->attendeePW)?esc_attr($meeting['post']->attendeePW):'') ?>" size="20"></td></tr>
                        <tr><td><?php echo _x('Moderator Password: ','','wplms-bbb') ;?> </td><td><input class="bbb_create_m_field" type="text" name="moderatorPW" value="<?php echo (!empty($meeting['post']->moderatorPW)?esc_attr($meeting['post']->moderatorPW):'') ?>" size="20"></td></tr>
                        <tr><td><?php echo _x('Wait for moderator to start meeting:  ','','wplms-bbb') ;?></td><td><input type="checkbox" class="bbb_create_m_field required" name="waitForModerator" value="True" <?php echo (!empty($meeting['post']->waitForModerator)?'checked="checked"':'') ?>/></td></tr>
                        <tr><td><?php echo _x('Recorded meeting:  ','','wplms-bbb') ;?></td><td> <input class="bbb_create_m_field" type="checkbox"  name="recorded" value="True" <?php echo (!empty($meeting['post']->recorded)?'checked="checked"':'') ?>/></td></tr>
                        <tr><td><?php echo _x('Enable reminders:  ','','wplms-bbb') ;?><br>
                           <span>(<?php echo _x('It will send email reminder to each student before the meeting.So please choose carefully','','wplms-bbb') ;?>)<span> 
                        </td><td><input class="bbb_create_m_field" type="checkbox" name="mdr" value="True" <?php echo (!empty($meeting['reminder']['duration'])?'checked="checked"':'') ?>/></td></tr>
                        <tr class="reminder_duration <?php echo (!empty($meeting['reminder']['duration'])?'show':'')?>"><td><?php echo _x('Time before reminders will be sent:','','wplms-bbb') ;?> </td><td> 
                            <input class="bbb_create_m_field" type="number" name="mdrduration" value="<?php echo (!empty($meeting['reminder']['duration'])?esc_attr($meeting['reminder']['duration']):'') ?>">
                            <select  class="bbb_create_m_field" name="mdrdurationparam" >
                                <?php 
                                foreach ( $options as $option )
                                    echo '<option' . selected( esc_attr( $meeting['reminder']['parameter'] ), $option['value'], false ) . ' value="' . $option['value'] . '">' . $option['label'] . '</option>';
                                ?>
                            </select>

                        </td></tr>

                        <tr class="submit"><td><input type="submit" name="SubmitCreate" class="button-primary" value="<?php echo _x('Edit Meeting ','','wplms-bbb') ;?>" /></td></tr>

                    </tbody>
                </table>
            </form>
            <div class="bbb_create_meeting_message"></div>
            <?php
        }

        function get_meeting($meeting_id=null){
            if(empty($this->wplms_bbb_meetings) || !is_array($this->wplms_bbb_meetings))
                return array();
            return apply_filters('wplms_get_bbb_meeting',$this->wplms_bbb_meetings[$meeting_id]);
        }

        function get_all_meetings(){
            if(empty($this->wplms_bbb_meetings) || !is_array($this->wplms_bbb_meetings))
                return array();
            return apply_filters('wplms_get_all_meetings',$this->wplms_bbb_meetings);
        }

        function all_meetings_content(){
            
            if(!is_user_logged_in() || (is_user_logged_in() && !current_user_can('edit_posts')))
                return;
            $this->print_js();
            $all_meetings = $this->get_all_meetings();
            if(!empty($all_meetings)){
                ?>

                <table class="wp-list-table widefat fixed striped"><thead><tr><th><?php echo _x('Meeting name','','wplms-bbb');?></th><th><?php echo _x('Meeting token','','wplms-bbb');?></th><th><?php echo _x('Start time','','wplms-bbb');?></th><th><?php echo _x('Privacy','','wplms-bbb');?></th><th><?php echo _x('Actions','','wplms-bbb');?></th></tr></thead><tbody>
                <?php
                foreach ($all_meetings as $key => $meeting) {
                    $time = _x('NA','','wplms-bbb');
                    if(!empty($meeting['start_date']) && !empty($meeting['start_time'])){

                        $time = strtotime($meeting['start_date'].' '.$meeting['start_time']);
                        $format = get_option( 'date_format' ).' '.get_option('time_format');;
                        $time = date_i18n($format ,$time);
                    }
                   
                    $restriction = _x('NA','','wplms-bbb');
                    if(!empty($meeting['restrictions'])){
                        if(!empty($meeting['restrictions']['scope']))
                        $restriction = $this->restrictions[$meeting['restrictions']['scope']];
                        if(!empty($meeting['restrictions']['data'])){
                            if($meeting['restrictions']['scope'] == 'course_students'){
                                foreach($meeting['restrictions']['data'] as $course){
                                  $restriction .= '<br><span>('.get_the_title($course).')</span>';  
                                }
                                
                            }elseif($meeting['restrictions']['scope'] == 'selected_users'){
                                $i=0;
                                $count = (count($meeting['restrictions']['data'])-2);
                                $restriction .= '<br><span>(';
                                foreach ($meeting['restrictions']['data'] as $value) {
                                    if($i < 2){
                                      $student = get_user_by('id',$value);
                                      $restriction .= $student->display_name;
                                    }
                                    if($i < 1){
                                        $restriction .= ',';
                                    }
                                    $i++;
                                }
                                if($count > 0){
                                    $restriction .= sprintf(_x(' and %s more','','wplms-bbb'),$count);
                                }
                                $restriction .= ')</span>';
                            }elseif($meeting['restrictions']['scope'] == 'group'){
                                foreach($meeting['restrictions']['data'] as $id){
                                    if(function_exists('groups_get_group')){
                                        $group = groups_get_group(esc_attr($id));
                                    }
                                  $restriction .= '<br><span>('.$group->name.')</span>';  
                                }
                            }
                        }
                    }

                    ?>
                        <tr>
                            <td><?php echo $meeting['name']?></td>
                            <td><?php echo $meeting['id']?></td>
                            <td><?php echo $time;?></td>
                            <td><?php echo $restriction;?></td>
                            <td>
                                <a href="javascript:void(0)" class="insert_meeting button" data-id="<?php echo $meeting['id']?>"><?php echo _x('Insert','','wplms-bbb');?></a>
                                <a href="<?php echo admin_url('media-upload.php?type=wplms_bbb_meetings&tab=edit&meeting='.$meeting['id']);?>" class="edit_meeting button" data-id="<?php echo $meeting['id']?>"><?php echo _x('Edit','','wplms-bbb');?></a>
                                <a href="javascript:void(0)" class="delete_meeting button"  data-id="<?php echo $meeting['id']?>"><?php echo _x('Delete','','wplms-bbb');?></a>
                            </td>
                        </tr>
                    <?php
                }
            }else{
                echo '<div class="message notice error"><p>'._x('No meetings found','','wplms-bbb').'</p></div>';
            }
            ?>

            </tbody></table>
            <?php
            
            
        }
        function media_create_meeting_content(){
           
            $this->print_tabs();
            echo '<div class="create_meeting">';
            echo '<h2>'.__('Add meeting','wplms-bbb').'</h2>';
            $this->print_create();
            echo "</div>";
        }

        function media_edit_meeting_content(){
           
            $this->print_tabs();
            echo '<div class="create_meeting">';
            echo '<h2>'.__('Edit meeting','wplms-bbb').'</h2>';
            $this->print_edit();
            echo "</div>";
        }

        function media_all_meetings_form(){
            $wplmsthis = Wplms_Bbb::init();
            $wplmsthis->print_tabs();
            echo '<div class="all_meetings">';
            echo '<h2>'.__('Meetings','wplms-bbb').'</h2>';
            $wplmsthis->all_meetings_content();
            echo '</div>';
        }

    }//class ends here/
}

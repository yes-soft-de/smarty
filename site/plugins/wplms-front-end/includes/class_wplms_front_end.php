<?php

class WPLMS_Front_End {
	
	const VERSION = '2.0';

	private static $instance;
	
	private function __construct(){   
        
		add_action('wplms_be_instructor_button', array( $this, 'create_course_button' ),5);

        add_action('template_redirect',array($this,'wplms_front_end_validate_action'));
        add_action('wplms_before_create_course_header',array($this,'wplms_before_create_course_page'),1);
        add_action('bp_course_options_nav',array($this,'wplms_edit_course_menu_link'));

        /* == OLD FRONT END AJAX CALLS ===*/
        add_action('wp_ajax_create_course',array($this,'create_course'));
        add_action('wp_ajax_save_course',array($this,'save_course'));
        add_action('wp_ajax_save_course_settings',array($this,'save_course_settings'));
        add_action('wp_ajax_create_unit',array($this,'create_unit'));
        add_action('wp_ajax_create_quiz',array($this,'create_quiz'));
        add_action('wp_ajax_delete_curriculum',array($this,'delete_curriculum'));
        add_action('wp_ajax_save_curriculum',array($this,'save_curriculum'));
        add_action('wp_ajax_save_pricing',array($this,'save_pricing'));
        add_action('wp_ajax_save_membership',array($this,'save_membership'));
        add_filter('wplms_create_course_settings',array($this,'wplms_create_course_settings'));
        add_filter('wplms_frontend_create_course_pricing',array($this,'wplms_frontend_create_course_pricing'));

        
        /* == Current Front end Editor compatible calls ==*/        
        add_filter('wplms_front_end_quiz_settings',array($this,'wplms_front_end_quiz_settings'));
        add_action('wplms_front_end_unit_controls',array($this,'wplms_front_end_unit_controls'));
        add_filter('wplms_front_end_unit_settings',array($this,'wplms_front_end_unit_settings'));
        add_action('wp_ajax_save_unit_settings',array($this,'save_unit_settings'));
        add_action('wplms_front_end_quiz_controls',array($this,'wplms_front_end_quiz_controls'));
        add_action('wplms_front_end_quiz_meta_controls',array($this,'wplms_front_end_quiz_meta_controls'));
        add_action('wp_ajax_create_question',array($this,'create_question'));
        add_action('wp_ajax_save_quiz_settings',array($this,'save_quiz_settings'));
        add_action('wp_ajax_delete_question',array($this,'delete_question'));
        add_action('wplms_front_end_question_controls',array($this,'wplms_front_end_question_controls'));
        add_filter('wplms_front_end_question_settings',array($this,'wplms_front_end_question_settings'));
        add_action('wp_ajax_save_question',array($this,'save_question'));
        add_action('wp_ajax_create_assignment',array($this,'create_assignment'));
        add_action('wplms_front_end_assignment_controls',array($this,'wplms_front_end_assignment_controls'));
        add_filter('wplms_front_end_assignment_settings',array($this,'wplms_front_end_assignment_settings'));
        add_action('wp_ajax_save_assignment_settings',array($this,'save_assignment_settings'));
        add_action('wplms_unit_end_front_end_controls',array($this,'wplms_unit_upload_zip_controls'));
        add_filter('wplms_front_end_pricing',array($this,'show_pricing'));
        //filter to change start course template for upload course type:
        add_filter('wplms_course_status_template',array($this,'change_for_upload_course'),10,2);
        add_filter('bp_course_get_course_curriculum',array($this,'handle_course_curriculum_not_set'),10,2);
        add_filter('wplms_course_nav_menu',array($this,'check_upload_course_type_for_curriculum_menu'),999999999);
        /* == Ajax calls used in Current Front end Editor ==*/
        add_action('wp_ajax_publish_course',array($this,'publish_course'));
        add_action('wp_ajax_offline_course',array($this,'offline_course'));
        add_action('wp_ajax_delete_course',array($this,'delete_course'));

        add_action('wplms_before_create_course_header',array($this,'put_json_for_selected_options'));
        add_action('wp_ajax_upload_course_package_call',array($this,'upload_course_package_call'));
        add_action('wp_ajax_upload_course_plupload',array($this,'upload_course_plupload'));
        

        //upload package ajax calls
        add_action('wp_ajax_set_course_package',array($this,'set_course_package'));
        add_action('wp_ajax_delete_course_package',array($this,'delete_course_package'));
        add_action('wp_ajax_remove_course_package',array($this,'remove_course_package'));
        add_action('bp_before_course_header',array($this,'remove_curriculum_below_course_desc'),100);
        //handle upload course finish check :
        add_action('template_redirect',array($this,'check_upload_course_complete'),1);



	}  
	
    public static function instance() {
        if ( ! self::$instance )
            self::$instance = new self;

        return self::$instance;
    }

    function check_upload_course_complete(){
      global $post;
      $course_id = $post->ID;
      if(isset($_POST['submit_course']) && isset($_POST['review']) && wp_verify_nonce($_POST['review'],get_the_ID())){ 
        if(!empty($course_id)){
          $upload_course = get_post_meta($course_id,'vibe_course_package',true);
          
          if(!empty($upload_course)) {
              add_filter('bp_get_course_check_course_complete_stop', function ($flag,$x,$y){
                return 1;
              },10,3);
             
              add_action('bp_get_course_check_course_complete',function($course_id,$user_id){
                  
                
                $id = $course_id;

                $flag = 0;
                $course_curriculum = array();
                $flag = apply_filters('wplms_finish_course_check_upload_course',$flag,$course_curriculum,$course_id);

                if(!$flag){

                  $course_id = $id;
                  $auto_eval = get_post_meta($id,'vibe_course_auto_eval',true);
                  

                  if(vibe_validate($auto_eval)){

                    // AUTO EVALUATION
                    
                    
                    do_action('wplms_submit_course',$post->ID,$user_id);
                    // Apply Filters on Auto Evaluation
                    $student_marks=apply_filters('wplms_course_student_marks',100,$id,$user_id);
                    $total_marks=apply_filters('wplms_course_maximum_marks',100,$id,$user_id);

                    if(!$total_marks){$total_marks=$student_marks=1; }// Avoid the Division by Zero Error

                    $marks = round(($student_marks*100)/$total_marks);

                    $return .='<div class="message" class="updated"><p>'.__('COURSE EVALUATED ','vibe').'</p></div>';

                    $badge_per = get_post_meta($id,'vibe_course_badge_percentage',true);

                    $passing_cert = get_post_meta($id,'vibe_course_certificate',true); // Certificate Enable
                    $passing_per = get_post_meta($id,'vibe_course_passing_percentage',true); // Certificate Passing Percentage

                    //finish bit for student 1.8.4
                    update_user_meta($user_id,'course_status'.$id,3);
                    //end finish bit
                    
                      do_action('wplms_evaluate_course',$id,$marks,$user_id,1);
                      
                      $badge_filter = 0;
                    if(isset($badge_per) && $badge_per && $marks >= $badge_per)
                        $badge_filter = 1;
              
                      $badge_filter = apply_filters('wplms_course_student_badge_check',$badge_filter,$course_id,$user_id,$marks,$badge_per);
                      if($badge_filter){  
                          $badges = array();
                          $badges= vibe_sanitize(get_user_meta($user_id,'badges',false));

                          if(isset($badges) && is_array($badges)){
                            if(!in_array($id,$badges)){
                              $badges[]=$id;
                            }
                          }else{
                            $badges=array($id);
                          }

                          update_user_meta($user_id,'badges',$badges);

                          $b=bp_get_course_badge($id);
                            $badge=wp_get_attachment_info($b); 
                            $size = apply_filters('bp_course_badge_thumbnail_size','thumbnail');
                            $badge_url=wp_get_attachment_image_src($b,$size);
                            if(isset($badge) && is_numeric($b))
                            $return .='<div class="congrats_badge">'.__('Congratulations ! You\'ve earned the ','vibe').' <strong>'.get_post_meta($id,'vibe_course_badge_title',true).'</strong> '.__('Badge','vibe').'<a class="tip ajax-badge" data-course="'.get_the_title($id).'" title="'.get_post_meta($id,'vibe_course_badge_title',true).'"><img src="'.$badge_url[0].'" title="'.$badge['title'].'"/></a></div>';
                          

                          do_action('wplms_badge_earned',$id,$badges,$user_id,$badge_filter);
                      }
                      $passing_filter =0;
                      if(vibe_validate($passing_cert) && isset($passing_per) && $passing_per && $marks >= $passing_per)
                        $passing_filter = 1;

                      $passing_filter = apply_filters('wplms_course_student_certificate_check',$passing_filter,$course_id,$user_id,$marks,$passing_per);
                      
                      if($passing_filter){
                          $pass = array();
                          $pass=vibe_sanitize(get_user_meta($user_id,'certificates',false));
                          
                          if(isset($pass) && is_array($pass)){
                            if(!in_array($id,$pass)){
                              $pass[]=$id;
                            }
                          }else{
                            $pass=array($id);
                          }

                          update_user_meta($user_id,'certificates',$pass);
                          $return .='<div class="congrats_certificate">'.__('Congratulations ! You\'ve successfully passed the course and earned the Course Completion Certificate !','vibe').'<a href="'.bp_get_course_certificate(array('user_id'=>$user_id,'course_id'=>$id)).'" class="ajax-certificate right '.apply_filters('bp_course_certificate_class','',array('course_id'=>$id,'user_id'=>$user_id)).'" data-user="'.$user_id.'" data-course="'.$id.'"><span>'.__('View Certificate','vibe').'</span></a></div>';
                          do_action('wplms_certificate_earned',$id,$pass,$user_id,$passing_filter);
                      }

                      update_post_meta( $id,$user_id,$marks);

                      $course_end_status = apply_filters('wplms_course_status',4);  
                    update_user_meta( $user_id,'course_status'.$id,$course_end_status);//EXCEPTION  

                      $message = sprintf(__('You\'ve obtained %s in course %s ','vibe'),apply_filters('wplms_course_marks',$marks.'/100',$course_id),' <a href="'.get_permalink($id).'">'.get_the_title($id).'</a>'); 
                      $return .='<div class="congrats_message">'.$message.'</div>';

                      

                  }else{
                    $return .='<div class="message" class="updated"><p>'.__('COURSE SUBMITTED FOR EVALUATION','vibe').'</p></div>';
                    bp_course_update_user_course_status($user_id,$id,2);// 2 determines Course is Complete
                    do_action('wplms_submit_course',$post->ID,$user_id);
                  }
                  
                  // Show the Generic Course Submission
                  $content=get_post_meta($id,'vibe_course_message',true);
                  $return .=apply_filters('the_content',$content);
                  $return = apply_filters('wplms_course_finished',$return);
                }else{
                  $type=bp_course_get_post_type($flag);
                  switch($type){
                    case 'unit':
                    $type= __('UNIT','vibe');
                    break;
                    case 'assignment':
                    $type= __('ASSIGNMENT','vibe');
                    break;
                    case 'quiz':
                    $type= __('QUIZ','vibe');
                    break;
                  }//Default for other customized options
                  $message = __('PLEASE COMPLETE THE ','vibe').$type.' : <a href="'.get_permalink($flag).'">'.get_the_title($flag).'</a>';
                  $return .='<div class="message"><p>'.apply_filters('wplms_unfinished_unit_quiz_message',$message,$flag).'</p></div>';
                }
                echo $return;
              },10,2);
          }
        }
      }
    }

    function remove_curriculum_below_course_desc(){
        global $post;
        if(empty($this->upload_course_type)){
            $upload_type = get_post_meta($post->ID,'vibe_course_package',true);
            $this->upload_course_type = $upload_type;
        }else{
            $upload_type = $this->upload_course_type;
        }

        if(!empty($upload_type )){
            if(class_exists('WPLMS_tips')){
                $tips = WPLMS_tips::init();
                remove_action('wplms_after_course_description',array($tips,'course_curriculum_below_description'));
            }
            if(class_exists('WPLMS_Actions')){
                $actions = WPLMS_Actions::init();
                remove_action('wplms_after_course_description',array($actions,'course_curriculum_below_description_wplms_course_tabs')); 
            }
        }
    }

    function check_upload_course_type_for_curriculum_menu($menus){
        $course_id = get_the_ID();
        if(empty($course_id) || !is_numeric($course_id)){
            return $menus;
        }
        if(empty($this->upload_course_type)){
            $upload_type = get_post_meta($course_id,'vibe_course_package',true);
            $this->upload_course_type = $upload_type;
        }else{
            $upload_type = $this->upload_course_type;
        }
        if(!empty($upload_type )){
            unset($menus['curriculum']); 
        }
        return $menus;
    }

    function handle_course_curriculum_not_set($course_curriculum,$course_id){
        if(is_page_template('start.php')){
            $upload_course = get_post_meta($course_id,'vibe_course_package',true);
            $this->upload_course_type = $upload_course;
            if(!empty($upload_course)) {
                return array(0);
            }
        }
        return $course_curriculum;
    }

    function set_course_package(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'course_package') || !is_user_logged_in()){
            echo 'security check failed contact admin';
            die();
        }
        $course_id = $_POST['course_id'];
        $package_name = sanitize_text_field($_POST['package_name']);
        $package_path = esc_url($_POST['package_path']);
        if(empty($package_path ) || empty($package_name) || !is_numeric($course_id))
            die();
        update_post_meta($course_id,'vibe_course_package',array(
            'name'=>$package_name,
            'path'=>$package_path,
            )
        );
        die();
    }

    function remove_course_package(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'course_package') || !is_user_logged_in()){
            echo 'security check failed contact admin';
            die();
        }
        $course_id = $_POST['course_id'];
        $package_name = sanitize_text_field($_POST['package_name']);
        $package_path = esc_url($_POST['package_path']);
        if(empty($package_name) || !is_numeric($course_id))
            die();
        delete_post_meta($course_id,'vibe_course_package');
        die();
    } 

    function delete_course_package(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'course_package') || !is_user_logged_in()){
            echo 'security check failed contact admin';
            die();
        }
        $course_id = $_POST['course_id'];
        $package_name = sanitize_text_field($_POST['package_name']);
        $package_path = esc_url($_POST['package_path']);
        if(empty($package_name) || !is_numeric($course_id))
            die();
        $course_package = get_post_meta($course_id,'vibe_course_package',true);
        if(!empty($course_package) && $course_package == $package_name){
            delete_post_meta($course_id,'vibe_course_package');
        }
        $dir = $this->getUploadsPath().$package_name;
        $this->rrmdir($dir);

        die();
    }

    function change_for_upload_course($template,$post_array){
        if(empty($post_array['course_id']))
            return $template;
        $course_id = $post_array['course_id'];
        $upload_path = get_post_meta($course_id,'vibe_course_package',true);
        if(!empty($upload_path)){
            $template = 'packaged';
        }
        return $template;
    }


    function upload_course_package_call(){
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'upload_course_package_call') || !is_user_logged_in()){
            die();
        }else{

            //pkoade tal lo yaha
            $user_id = get_current_user_id();
            $arr = array();
            $fileName = $_POST['name'];
            $upload_dir_base = wp_upload_dir();
            if(function_exists('is_dir') && !is_dir($upload_dir_base['basedir']."/temp_folder_course_packages/".$user_id)){
                if(function_exists('mkdir')) 
                    mkdir($upload_dir_base['basedir']."/temp_folder_course_packages/".$user_id, 0755, true) || chmod($upload_dir_base['basedir']."/temp_folder_course_packages/".$user_id, 0755);
            }
            $file = $upload_dir_base['basedir']."/temp_folder_course_packages/".$user_id."/".$fileName;
            $dir = explode(".",$fileName);
            $dir[0] = str_replace(" ","_",$dir[0]);

            $target = $this->getUploadsPath().$dir[0];
            $index = count($dir) -1;

            if (!isset($dir[$index]) || $dir[$index] != "zip"){
                $arr[0] = __('The Upload file must be zip archive','wplms-front-end');
            }else{
                while(file_exists($target)){
                    $r = rand(1,10);
                    $target .= $r;
                    $dir[0] .= $r;
                }
                if (!empty($file))
                    $arr = $this->extractZip($file,$target,$dir[0]);
                else
                    $arr[0] = __('File too big','wplms-front-end');
            }
            $course_id  = intval($_POST['course_id']);  
            if(!empty($course_id) && is_numeric($course_id) && !empty($arr[2]) && !empty($arr[3])){
                $package_meta = array('name'=>$arr[2],'path'=>$arr[1],'file'=>$arr[3]);
                update_post_meta($course_id,'vibe_course_package',$package_meta);
                unlink($file);
            }
            echo json_encode($arr);
            die();
        }
    }

    function getFile($dir){
        $myDirectory = opendir($dir);
        $fileArray = array();
        $myDirectory = opendir($dir);
        $fileArray = array();
        $file1 = '';
        // get each entry
        while($entryName = readdir($myDirectory)) {
          if ($entryName != "." && $entryName !=".."){
            $f = $this->getUploadsPath().$entryName;
            $fname = pathinfo ($f, PATHINFO_FILENAME);
            $ext = pathinfo ($f,PATHINFO_EXTENSION);

            if (in_array($ext,array('html','htm','mov','avi','mp4','mp3','txt'))){
              
              if(!empty($entryName)){
                if(strpos($fname, 'index') !== false || strpos($fname, 'story') !== false){
                    return $entryName;
                    break;
                }else{
                    $file1 = $entryName;
                }
              }
            }
          }
        }
        closedir($myDirectory);
        if(!empty($file1)){
          return $file1;
        }
        return false;
    }

    function getUploadsUrl(){
        $dir = wp_upload_dir();
        $privacy = vibe_get_option('instructor_content_privacy');
        if(isset($privacy) && $privacy){
            $user_id = get_current_user_id();
            return $dir['baseurl'] . '/private_package_uploads/'.$user_id.'/';
        }else{
            return $dir['baseurl'] . '/package_uploads/';
        }
    }

    function extractZip($fileName,$target,$dir){
            $arr = array();
         $zip = new ZipArchive;

         $res = $zip->open($fileName);
         if ($res === TRUE) {
            $zip->extractTo($target);
            $zip->close();
            $file = $this->getFile($target);
            if($file){
                 $arr[0] = 'uploaded'; 
                 $arr[1] = $this->getUploadsUrl().$dir."/".$file; 
                 $arr[2] = $dir;
                 $arr[3] =$file;
             }else{
                 $arr[0] = __('Please upload zip file, Index.html file not found in package ','wplms-front-end').$target.'----'.print_r($file);
                 $this->rrmdir($target);
             }
         }else{
            $arr[0] = __('Upload failed !','wplms-front-end');;
         }
         return  $arr;
    }

    function rrmdir($dir) {
        if (is_dir($dir)) {
         $objects = scandir($dir);
         foreach ($objects as $object) {
           if ($object != "." && $object != "..") {
             if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
           }
         }
         reset($objects);
         rmdir($dir);
        }
    }

    function getUploadsPath(){
        $dir = wp_upload_dir();
        $privacy = vibe_get_option('instructor_content_privacy');
        if(isset($privacy) && $privacy){
            $user_id = get_current_user_id();
            return $dir['basedir'] . '/private_package_uploads/'.$user_id.'/';
        }else{
            return $dir['basedir'] . '/package_uploads/';
        }
    }

    function upload_course_plupload(){
        check_ajax_referer('upload_course_plupload');
        if(!is_user_logged_in())
            die(__('user not logged in','wplms-front-end'));
        $user_id = get_current_user_id();
        if (empty($_FILES) || $_FILES['file']['error']) {
          die('{"OK": 0, "info": "Failed to move uploaded file."}');
        }

        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
         
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
        $upload_dir_base = wp_upload_dir();
        if(function_exists('is_dir') && !is_dir($upload_dir_base['basedir']."/temp_folder_course_packages/".$user_id)){
            if(function_exists('mkdir')) 
                mkdir($upload_dir_base['basedir']."/temp_folder_course_packages/".$user_id, 0755, true) || chmod($upload_dir_base['basedir']."/temp_folder_course_packages/".$user_id, 0755);
        }
        $filePath = $upload_dir_base['basedir']."/temp_folder_course_packages/".$user_id."/".$fileName;
         
        // Open temp file
        if($chunk == 0) $perm = "wb" ;
        else $perm = "ab";

        $out = @fopen("{$filePath}.part",$perm );

        if ($out) {
          // Read binary input stream and append it to temp file
          $in = @fopen($_FILES['file']['tmp_name'], "rb");
         
          if ($in) {
            while ($buff = fread($in, 4096)){
              fwrite($out, $buff);
            }
          } else
            die('{"OK": 0, "info": "Failed to open input stream."}');
         
          @fclose($in);
          @fclose($out);
         
          @unlink($_FILES['file']['tmp_name']);
        } else
          die('{"OK": 0, "info": "Failed to open output stream."}');
         
         
        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
          // Strip the temp .part suffix off
          rename("{$filePath}.part", $filePath);
        }
        die('{"OK": 1, "info": "Upload successful."}');
        exit;
    }

    
    function put_json_for_selected_options(){
        

        if(did_action('wplms_before_create_course_header') === 1)
            return;

        if(function_exists('vibe_get_customizer')){
            $primary_bg = vibe_get_customizer('primary_bg');
            $primary_color = vibe_get_customizer('primary_color');
        }

        wp_enqueue_script( 'course_upload-end-js', plugins_url( '../assets/js/course_upload.json' , __FILE__ ), array( 'bp-course-js','jquery-ui-core','jquery-ui-sortable','jquery-ui-slider','jquery-ui-datepicker','bp-confirm' ) ,'2.8.1');
        
        if(!empty($_GET['action'])){
            $course_id = $_GET['action'];
        } 
        $saved_templates = get_option('wplms_course_templates');
        $saved_templates = apply_filters('wplms_course_templates',$saved_templates,$course_id);
        if(!empty($saved_templates)){
             wp_localize_script('wplms-front-end-js', 'saved_course_templates',$saved_templates);   
        }
        if(!empty($course_id)){
            $existing_template_id = get_post_meta($course_id,'vibe_course_template',true);
            $existing_package = get_post_meta($course_id,'vibe_course_package',true);
        }
        if(!empty($existing_template_id)){
            wp_localize_script('wplms-front-end-js', 'course_existing_template_id',$existing_template_id);
        }
        $plupload_wrapper = '<div class="upload_course_wrapper">';
        
        $plupload_wrapper .= '<input type="hidden" id="course_package" value="'.wp_create_nonce('course_package').'">';
        wp_enqueue_style( 'media' );
        wp_enqueue_script('plupload-all');
        $plupload_wrapper .= '<div class="existing_course_package_wrapper">';
        if(!empty($existing_package) && !empty($existing_package['path'])){
            $plupload_wrapper .= '
            <div class="existing_course_package"><strong>'.$existing_package['name'].'</strong>
            <a title="'._x('Remove this package from course','','wplms-front-end').'" href="javascript:void(0)" class="button small remove_course_package right" data-package-name="'.$existing_package['name'].'" data-package-path="'.$existing_package['path'].'"><i class="fa fa-remove"></i></a>

            <a title="'._x('View this package','','wplms-front-end').'" href="'.$existing_package['path'].'" target="_blank" class="button small right"><i class="fa fa-eye"></i></a>
            </div>';
            
        }
        $plupload_wrapper .=  '</div>';
        $plupload_wrapper .= '<div class="wrap">
            <label for="wplms-front-end-upload">'._x('Upload new package','','wplms-front-end').'</label>
            <form enctype="multipart/form-data" method="post" class="wplms-front-end-upload">
                
                <div  class="plupload_error_notices message error notice notice-error is-dismissible"></div>
                <div id="plupload-upload-ui" class="hide-if-no-js">
                    <div id="drag-drop-area">
                        <div class="drag-drop-inside">
                            <p class="drag-drop-info">'._x('Upload zip','','wplms-front-end').'</p>
                            <p>'._x('or', '','wplms-front-end').'</p>
                            <p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="'._x('Select Files','','wplms-front-end').'" class="button" /></p>
                        </div>
                    </div>
                </div>

                <div class="pl_upload_course_progress"></div>
                <div class="warning_plupload">
                    <h3>'._x("Please do not close the window until process is completed","","wplms-front-end").'</h3>
                </div>       
            </form>
            </div>
        ';

        $dirs = array();
        $paths = $this->getUploadsPath();

        if(file_exists($paths)){

            $myDirectory = opendir($paths);
            
            $i=0;
            while($entryName = readdir($myDirectory)) {
                if ($entryName != "." && $entryName !=".." && is_dir($this->getUploadsPath().$entryName)):
                $dirs[$i]['dir'] = $entryName;
                $dirs[$i]['file'] = $this->getFile($this->getUploadsPath().$entryName);
                $i++;
                endif;
            }
            // close directory
            closedir($myDirectory);
        }

        if (count($dirs)>0){
            $uploadDirUrl=$this->getUploadsUrl();

            if(!empty($dirs)){
                $plupload_wrapper .=  '<div class="existing_packages_select_wrapper">
                <label for="existing_packages_select">'._x('Select from existing packages:','','wplms-front-end').'</label>
                <select class="chosen existing_packages_select">';
                foreach ($dirs as $i=>$dir){
                    extract($dir);
                    $package_name = str_replace("_"," " ,$dir);
                    $plupload_wrapper .=  '<option value="'.$uploadDirUrl.$dir."/".$file.'">'.$package_name.'</option>';
                }   
                $plupload_wrapper .=  '</select>
                <a title="'._x('Delete this package','','wplms-front-end').'" href="javascript:void(0)" class="button small remove_package right"><i class="fa fa-trash-o"></i></a>
                <a title="'._x('Set this as course package','','wplms-front-end').'" href="javascript:void(0)" class="button small use_package right"><i class="fa fa-sign-in"></i></a>
                <a title="'._x('View this package','','wplms-front-end').'" href="" target="_blank" class="view_package button small right"><i class="fa fa-eye"></i></a>
                </div>';

            }
        }
        $plupload_wrapper .= '</div>';//closing upload wrapper
        wp_localize_script('wplms-front-end-js', 'plupload_wrapper',$plupload_wrapper); 
        ?>
        <style>
            .pl_upload_course_progress{display:none;}
            div.pl_upload_course_progress.visible{display:block;}
            div.pl_upload_course_progress > div{
                text-align: right;
                padding: 10px;
            }div.pl_upload_course_progress > div > i{
                padding: 15px;
                color: green;
            }

            div.pl_upload_course_progress strong{width:240px;background:#fff;height:10px;border-radius:10px;display:block;float:right;clear:both;}
            div.pl_upload_course_progress span{
                display: block;
                width:0%;border-radius:10px;
                background: green;
                height:10px;
            }.warning_plupload{display:none;}
            .warning_plupload {
                text-align: center;
                background: rgba(0,0,0, 0.45);
                padding: 1px 10px 10px 10px;
                vertical-align: middle;
                width:85%;
                margin : 10px auto;
                border-radius:5px;
            }
            .warning_plupload h3{
                color: #FFF;
            }div.plupload_error_notices{
                display: none;
                margin:10px 2px ;
                padding:5px;
            }
            #create_course_wrapper #create_course_templates_wrapper .course_template.active{
                background:<?php echo $primary_bg;?>;
                color:<?php echo $primary_color;?>;
            }
            #create_course_wrapper #create_course_templates_wrapper .course_template.active h3{
                color:<?php echo $primary_color;?>;
            }
            #create_course_wrapper #create_course_templates_wrapper .course_template.active + span.delete_course_template:before{
                color:<?php echo $primary_color;?>;
            }
        </style>
        <?php
        if ( function_exists( 'ini_get' ) )
            $post_size = ini_get('post_max_size') ;
        $post_size = preg_replace('/[^0-9\.]/', '', $post_size);
        if($post_size != 1){
            $post_size = $post_size-1;
        }
        $post_size = intval($post_size);
       
        if ( function_exists( 'wp_max_upload_size') ){
            $size =  wp_max_upload_size() ; 
            $size = $size/1048576;
            $max_file_size = (intval($size)*1024).'kb' ; 
        }else{
            $max_file_size  = (32*1024).'kb';
        }
            
        $plupload_init = array(
            'runtimes'            => 'html5,silverlight,flash,html4',
            'chunk_size'          => (($post_size*1024)-100).'kb',
            'max_retries'         => 3,
            'browse_button'       => 'plupload-browse-button',
            'container'           => 'plupload-upload-ui',
            'drop_element'        => 'drag-drop-area',
            'multiple_queues'     => true,
            'max_file_size'       => $max_file_size,
            'filters'             => array( array( 'extensions' => 'zip' ) ),
            'url'                 => admin_url('admin-ajax.php'),
            'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
            'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
            
            'multipart'           => true,
            'urlstream_upload'    => true,

            // additional post data to send to our ajax hook
            'multipart_params'    => array(
              '_ajax_nonce' => wp_create_nonce('upload_course_plupload'),
              'action'      => 'upload_course_plupload', 
            ),
          );

        $plupload_init = apply_filters('plupload_init', $plupload_init);
        
        ?>
        <script type="text/javascript">

            jQuery(document).ready(function($){
                $('body').on('upload_course_trigger',function(){
                    if(jQuery.active != 0){
                        window.onbeforeunload = function (e) {
                            e = e || window.event;
                            // For IE and Firefox prior to version 4
                            if (e) {
                                e.returnValue = "<?php echo __('Make sure there is no ongoing upload process','wplms-front-end') ?>";
                            }
                            // For Safari
                            return "<?php echo __('Make sure there is no ongoing upload process','wplms-front-end') ?>";
                        };
                    }
                    
                    var max = 0;
                    var temp = <?php echo json_encode($plupload_init,JSON_UNESCAPED_SLASHES); ?>;
                    var uploader = '';
                    

                    uploader = new plupload.Uploader(temp);
                    // create the uploader and pass the config from above

                    // checks if browser supports drag and drop upload, makes some css adjustments if necessary
                    uploader.bind('Init', function(up){
                        var uploaddiv = $('#plupload-upload-ui');

                        if(up.features.dragdrop){
                          uploaddiv.addClass('drag-drop');
                            $('#drag-drop-area')
                              .bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
                              .bind('dragleave.wp-uploader, drop.wp-uploader', function(){ uploaddiv.removeClass('drag-over'); });

                        }else{
                          uploaddiv.removeClass('drag-drop');
                          $('#drag-drop-area').unbind('.wp-uploader');
                        }
                    });

                    uploader.init();
                      
                      // a file was added in the queue
                    uploader.bind('FilesAdded', function(up, files){

                        plupload.each(files, function(file){
                            var max = 0;
                            $('.pl_upload_course_progress').find('div').remove();
                            if (file.size > max && up.runtime != 'html5'){
                                
                                alert("<?php echo _x('File size exceeded the free space left on vimeo','alert message when no space on vimeo left','wplms-front-end');?>");
                                return false;
                            }else{
                                
                                 $('.pl_upload_course_progress').addClass('visible');
                                var clone = $('.pl_upload_course_progress').append('<div class="'+file.id+'">'+file.name+'<i></i><strong><span></span></strong></div>');
                                $('.pl_upload_course_progress').append(clone);
                                $('.warning_plupload').show(300);
                            }
                            
                        });

                        up.refresh();
                        up.start();
                    });
                    uploader.bind('Error', function(up, args) {
                        $('.plupload_error_notices').show();
                        $('.plupload_error_notices').html(args.message+' for '+args.file.name);
                        setTimeout(function(){
                            $('.plupload_error_notices').hide();
                        }, 3000);
                        up.refresh();
                        up.start();
                    });

                    uploader.bind('UploadProgress', function(up, file) {
                        
                        if(file.percent < 100 && file.percent >= 1){
                            $('.pl_upload_course_progress div.'+file.id+' strong span').css('width', (file.percent/2)+'%');
                            $('.pl_upload_course_progress div.'+file.id+' i').html( (file.percent/2)+'%');
                        }
                        /*$('.stop_vimeo_plupload_upload').on('click',function() {
                            if(confirm("<?php echo __('Are you sure you want to quit uploading?','wplms-front-end');?>")){
                                if(!($(this).hasClass('disabled'))){
                                    setTimeout(function(){
                                    $('.pl_upload_course_progress div.'+file.id).fadeOut(600);
                                    }, 1200);
                                    up.stop();
                                    up.destroy();
                                }
                            }else{
                                return false;
                            }
                        });*/
                        up.refresh();
                        up.start(); 
                    });
                      // a file was uploaded 
                    uploader.bind('FileUploaded', function(up, file, response) {
                        setTimeout(function(){
                            $('.pl_upload_course_progress div.'+file.id+' strong span').css('width', '62%');
                            $('.pl_upload_course_progress div.'+file.id+' i').html('62%');
                        }, 3000);
                        setTimeout(function(){
                            $('.pl_upload_course_progress div.'+file.id+' strong span').css('width', '76%');
                            $('.pl_upload_course_progress div.'+file.id+' i').html('76%');
                        }, 10000);
                        setTimeout(function(){
                            $('.pl_upload_course_progress div.'+file.id+' strong span').css('width', '82%');
                            $('.pl_upload_course_progress div.'+file.id+' i').html('82%');
                        }, 15000);
                        //$('.stop_vimeo_plupload_upload').addClass('disabled');
                         $.ajax({
                          type: "POST",
                          url: ajaxurl,
                          dataType:'json',
                          data: { action: 'upload_course_package_call', 
                                  security: '<?php echo wp_create_nonce("upload_course_package_call"); ?>',
                                  name:file.name,
                                  type:file.type,
                                  
                                  course_id:$('body').find('#course_id').val(),
                                  size:file.origSize,
                                  
                                },
                          cache: false,
                          success: function (html) {
                            if(html){
                                if(html == '0'){
                                    $('.pl_upload_course_progress div.'+file.id+' strong span').css('width', '0%');
                                    $('.pl_upload_course_progress div.'+file.id+' strong').html("<i class='error'><?php echo __("File type not allowed","wplms-front-end"); ?><i>");
                                    setTimeout(function(){
                                        $('.pl_upload_course_progress div.'+file.id).fadeOut(600);
                                    }, 1200);
                                    $('.warning_plupload').hide(300);
                                    return false;
                                }else{
                                    //$('.stop_vimeo_plupload_upload').removeClass('disabled');
                                    $('.pl_upload_course_progress div.'+file.id+' strong span').css('width', '100%');
                                    $('.pl_upload_course_progress div.'+file.id+' i').html('100%');
                                    
                                        setTimeout(function(){
                                          $('.pl_upload_course_progress div.'+file.id+' strong').fadeOut(500);
                                        }, 1200);
                                        if(typeof html[2] != 'undefined'){
                                           $('.pl_upload_course_progress div.'+file.id).html('<small class="message success">'+html[2]+' - '+wplms_front_end_messages.uploaded+'</small>');
                                           $('body').find('.existing_course_package_wrapper').html('');
                                        }else{
                                             $('.pl_upload_course_progress div.'+file.id).html('<small class="message error failed">'+html[0]+'</small>'); 
                                        }
                                        
                                        
                                        setTimeout(function(){
                                            if($('.pl_upload_course_progress strong').length < 1){
                                                $('.warning_plupload').hide(300);
                                            }
                                            }, 1750);
                                        
                                        
                                }   
                            }
                        
                          }
                        });
                    });
                });

            });   

        </script>
        <?php
    }

	function create_course_button(){
		if(function_exists('vibe_get_option')){
			$pageid = vibe_get_option('create_course');

			if(isset($pageid) && $pageid && current_user_can('edit_posts')){
                if(class_exists('WPLMS_Actions')){
                    $actions = WPLMS_Actions::init();
                    remove_action( 'wplms_be_instructor_button', array($actions,'wplms_be_instructor_button'));
                }
				echo '<a href="'.get_permalink($pageid).'" class="button create-group-button full">'. __( 'Create a Course', 'wplms-front-end' ).'</a>';
			}
		}
	}

    function wplms_before_create_course_page(){
        if(!current_user_can('edit_posts')){
            wp_die(__('COURSE CREATION ONLY ALLOWED FOR INSTRUCTORS & ADMINISTRATORS','wplms-front-end'));
        }
    }

    function wplms_front_end_validate_action(){
        global $post;
        $create_course = vibe_get_option('create_course');
        if(isset($_GET['action']) && is_page($create_course) && $post->ID == $create_course){
            if(is_numeric($_GET['action']) && (get_post_type($_GET['action']) == 'course')){
                if ( current_user_can('edit_post', $_GET['action']) ){
                    
                }else {
                    wp_die(__('Unable to edit Course, please contact Site Administrator','wplms-front-end'));
                }    
            }else{
                wp_die(__('Incorrect Action, please contact Site Administrator','wplms-front-end'));
            }
        }
    }

    function wplms_create_course_settings($course_settings){
        if(isset($_GET['action']) && is_numeric($_GET['action'])){
            $course_id = $_GET['action'];
            foreach($course_settings as $key => $value){
                $db=get_post_meta($course_id,$key,true);
                if(isset($db) && $db !='')
                    $course_settings[$key] = $db;
            }
            //Anamoly
            if($course_settings['vibe_course_badge'] == '' || $course_settings['vibe_course_badge'] == ' '){
                $course_settings['vibe_badge'] = 'H';
                $course_settings['vibe_course_badge'] = '';
            }  
        }
        return $course_settings;
    }

    function wplms_edit_course_menu_link($nav_menu){
        global $post;
        $pageid = vibe_get_option('create_course');
        if(function_exists('icl_object_id'))
            $pageid = icl_object_id($pageid, 'page', true);
        
        $user_id=get_current_user_id();

        $instructors = apply_filters('wplms_course_instructors',$post->post_author,$post->ID);
        if(!is_array($instructors)){
          $instructors = array($instructors);
        }
        $flag = apply_filters('wplms_allow_instructor_edit_course_as_co_author',1,$user_id,$instructors,$post->ID);
        if($flag && isset($pageid) && $pageid && (in_array($user_id,$instructors) || current_user_can('manage_options'))){

          echo '<li id="edit"><a href="'.get_permalink($pageid).'?action='.$post->ID.'">'.__( 'Edit Course', 'wplms-front-end' ).'</a></li>';
        }
    }

    function create_course(){
        $user_id= get_current_user_id();
        $title = $_POST['title'];
        $category = $_POST['category'];
        $newcategory = $_POST['newcategory'];
        $thumbnail_id = $_POST['thumbnail'];    
        $description = $_POST['description'];
        $courselinkage = $_POST['courselinkage'];
        $newcourselinkage = $_POST['newcourselinkage'];


        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_course'.$user_id) || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        $course_post = array(
            'post_status' => 'draft', 
            'post_type'  => 'course',
            'post_title' => $title,
            'post_excerpt' => $description,
            'post_content' => $description,
            'comment_status' => 'open'
            );

        if(is_numeric($category)){
            $course_post['tax_input'] = array('course-cat' => $category);
        }else if($category == 'new'){
            $term = term_exists($newcategory, 'course-cat');
            if ($term !== 0 && $term !== null) {
              $course_post['tax_input'] = array('course-cat' => $term['term_id']);
            }else{
                $new_term = wp_insert_term($newcategory,'course-cat');
                if (is_array($new_term)) {
                    $course_post['tax_input'] = array('course-cat' => $new_term['term_id']);
                }else{
                    _e('Unable to create a new Course Category. Contact Admin !','wplms-front-end');
                    die();
                }
            }
        }


        $post_id = wp_insert_post($course_post);

        if(is_numeric($post_id)){
            if(isset($thumbnail_id) && is_numeric($thumbnail_id))
                set_post_thumbnail($post_id,$thumbnail_id);

            //Linkage
            if(isset($courselinkage) && $courselinkage){
                $course_linkage = array($courselinakge);
                wp_set_post_terms( $post_id, $course_linkage, 'linkage' );
            }

            if($courselinkage == 'add_new'){
                $new_term = wp_insert_term($newcourselinkage,'linkage');
                if (is_array($new_term)) {
                    $course_linkage = array($newcourselinkage);
                    $check = wp_set_post_terms( $post_id, $course_linkage, 'linkage' );
                }
            }

            echo $post_id;    
        }else{
            _e('Unable to create course, contact admin !','wplms-front-end');
        }
        
        die();

    }
    function save_course(){
        $user_id= get_current_user_id();
        $course_id = $_POST['ID'];
        $title = $_POST['title'];
        $status = $_POST['status'];
        $category = $_POST['category'];
        $newcategory = $_POST['newcategory'];
        $thumbnail_id = $_POST['thumbnail'];    
        $description = $_POST['description'];
        $courselinkage = $_POST['courselinkage'];
        $newcourselinkage = $_POST['newcourselinkage'];

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_course'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }


        if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-front-end');
             die();
        }

        $the_post = get_post($course_id);
        if($the_post->post_author != $user_id && !current_user_can('manage_options')){ // Instructor and Admin check
            _e('Invalid Course Instructor','wplms-front-end');
             die();
        }

        $course_post = array(
            'ID' => $course_id, 
            'post_status' => $status,
            'post_title' => $title,
            'post_excerpt' => $description
            );
        
        $post_id = wp_update_post($course_post);
        echo $post_id;

        if(is_numeric($category)){
            wp_set_post_terms( $course_id, $category, 'course-cat');
        }else if($category == 'new'){
            $term = term_exists($newcategory, 'course-cat');
            if ($term !== 0 && $term !== null) {
               wp_set_post_terms( $course_id, $term['term_id'], 'course-cat');  
            }else{
                $new_term = wp_insert_term($newcategory,'course-cat');
                if (is_array($new_term)) {
                    wp_set_post_terms( $course_id, $new_term['term_id'], 'course-cat'); 
                }else{
                    _e('Unable to create a new Course Category. Contact Admin !','wplms-front-end');
                    die();
                }
            }
        }
       
    

        if(is_numeric($post_id) && $post_id){
            if(isset($thumbnail_id) && is_numeric($thumbnail_id))
                set_post_thumbnail($post_id,$thumbnail_id);

            //Linkage
            if(isset($courselinkage) && $courselinkage){
                $course_linkage = array($courselinkage);
                wp_set_post_terms( $post_id, $course_linkage, 'linkage' );
            }

            if($courselinkage == 'add_new'){
                $new_term = wp_insert_term($newcourselinkage,'linkage');
                if (is_array($new_term)) {
                    $course_linkage = array($newcourselinkage);
                    wp_set_post_terms( $post_id, $course_linkage, 'linkage' );
                }
            }

            echo $post_id;    
        }else{
            _e('Unable to create course, contact admin !','wplms-front-end');
        }
        
        die();
    }

    function save_course_settings(){

        $user_id= get_current_user_id();
        $course_id =$_POST['course_id'];
        $course_setting['vibe_course_auto_eval']=$_POST['vibe_course_auto_eval'];
        $course_setting['vibe_duration']=$_POST['vibe_duration'];
        $course_setting['vibe_pre_course']=$_POST['vibe_pre_course'];
        $course_setting['vibe_course_drip']=$_POST['vibe_course_drip'];
        $course_setting['vibe_course_drip_duration']=$_POST['vibe_course_drip_duration'];
        $course_setting['vibe_course_certificate']=$_POST['vibe_certificate'];
        $course_setting['vibe_course_passing_percentage']=$_POST['vibe_course_passing_percentage'];
        $course_setting['vibe_certificate_template']=$_POST['vibe_certificate_template'];
        $course_setting['vibe_badge']=$_POST['vibe_badge'];
        $course_setting['vibe_course_badge_percentage']=$_POST['vibe_course_badge_percentage'];
        $course_setting['vibe_course_badge_title'] = $_POST['vibe_course_badge_title'];
        $course_setting['vibe_course_badge']=$_POST['vibe_course_badge'];
        $course_setting['vibe_max_students']=$_POST['vibe_max_students'];
        $course_setting['vibe_start_date']=$_POST['vibe_start_date'];
        $course_setting['vibe_course_retakes']=$_POST['vibe_course_retakes'];
        $course_setting['vibe_group']=$_POST['vibe_group'];
        $course_setting['vibe_forum']=$_POST['vibe_forum'];
        $course_setting['vibe_course_instructions']=$_POST['vibe_course_instructions'];
        $course_setting['vibe_course_message']=$_POST['vibe_course_message'];
        
        $flag = 0; //Error Flag
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_course'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-front-end');
             die();
        }

        $the_post = get_post($course_id);
        if($the_post->post_author != $user_id && !current_user_can('manage_options')){
            _e('Invalid Course Instructor','wplms-front-end');
             die();
        }

        if($course_setting['vibe_badge'] == 'H'){
            $course_setting['vibe_course_badge']='';
        }
        foreach($course_setting as $key=>$value){
            $prev_val=get_post_meta($course_id,$key,true);
            if($prev_val != $value){
                update_post_meta($course_id,$key,$value);
            }   
        }

        if($course_setting['vibe_group'] == 'add_new' && !$flag){
            $the_course = get_post($course_id);
            $t=wp_get_attachment_image_src( get_post_thumbnail_id($course_id,'thumbnail') );
            $f=wp_get_attachment_image_src( get_post_thumbnail_id($course_id,'full') );

            $group_slug=$the_course->post_name;//groups_check_slug( sanitize_title( esc_attr( $the_course->post_name ) ) );
            $group_settings = array(
                'creator_id' => $user_id,
                'name' => $the_course->post_title,
                'slug' => $group_slug,
                'description' => $the_course->post_excerpt,
                'status' => 'private',
                'date_created' => current_time('mysql')
            );

            $group_settings = apply_filters('wplms_front_end_group_vars',$group_settings);
            if($course_setting['vibe_forum'] == 'add_group_forum'){
                $group_settings['enable_forum'] = 1;
            }
            
            
            global $bp;

            $new_group_id = groups_create_group( $group_settings);

            bp_core_avatar_handle_crop( array( 'object' => 'group', 'avatar_dir' => 'group-avatars', 'item_id' => $new_group_id, 'original_file' => $f[0], 'crop_x' => 0, 'crop_y' => 0, 'crop_w' => $f[1], 'crop_h' => $f[2] ) );

            groups_update_groupmeta( $new_group_id, 'total_member_count', 1 );
            groups_update_groupmeta( $new_group_id, 'last_activity', gmdate( "Y-m-d H:i:s" ) );
            update_post_meta($course_id,'vibe_group',$new_group_id);
            

            if($course_setting['vibe_forum'] == 'add_group_forum'){

                $forum_settings = array(
                        'post_title' => stripslashes( $the_course->post_title ),
                        'post_content' => stripslashes( $the_course->post_excerpt ),
                        'post_name' => $the_course->post_name,
                        'post_status' => 'private',
                        'post_type' => 'forum',
                        );
                $forum_settings=apply_filters('wplms_front_end_forum_vars',$forum_settings);
                $new_forum_id = wp_insert_post($forum_settings);

                //Linkage 
                $linkage = vibe_get_option('linkage');
                if(isset($linkage) && $linkage){
                     $course_linkage=wp_get_post_terms( $course_id, 'linkage',array("fields" => "names"));
                     if(isset($course_linkage) && is_array($course_linkage))
                     wp_set_post_terms( $new_forum_id, $course_linkage, 'linkage' );
                }
                groups_update_groupmeta( $new_group_id, 'forum_id', array($new_forum_id));
                update_post_meta($course_id,'vibe_forum',$new_forum_id);
            }
        }

        if($course_setting['vibe_forum'] == 'add_new' && !$flag){
            $forum_settings = array(
                        'post_title' => stripslashes( $the_post->post_title ),
                        'post_content' => stripslashes( $the_post->post_excerpt ),
                        'post_name' => $the_post->post_name,
                        'post_status' => 'private',
                        'post_type' => 'forum',
                        );
            
                $forum_settings=apply_filters('wplms_front_end_forum_vars',$forum_settings);
                $new_forum_id = wp_insert_post($forum_settings);
                update_post_meta($course_id,'vibe_forum',$new_forum_id);
        }

        if(isset($_POST['level']) && $_POST['level']){
            $level = $_POST['level'];
            if(is_numeric($level)){
            wp_set_post_terms( $course_id, $level, 'level');
            }
        }

        if($flag){
            echo $message;
        }else{
            echo $course_id;
            do_action('wplms_course_settings_updated',$course_id);
        }

        die();
    }


    function create_unit(){
        $user_id= get_current_user_id();
        $course_id =$_POST['course_id'];
        $unit_title = stripslashes($_POST['unit_title']); 

        if(!isset($unit_title) || count($unit_title) < 2 && $unit_title == ''){
            _e('Can not have a blank Unit ','wplms-front-end');
             die();
        }

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_course'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-front-end');
             die();
        }

        $the_post = get_post($course_id);
        if($the_post->post_author != $user_id && !current_user_can('manage_options')){
            _e('Invalid Course Instructor','wplms-front-end');
             die();
        }

        $unit_settings = array(
                        'post_title' => $unit_title,
                        'post_content' => $unit_title,
                        'post_status' => 'publish',
                        'post_type' => 'unit',
                        );
        $unit_settings=apply_filters('wplms_front_end_unit_vars',$unit_settings);
        $unit_id = wp_insert_post($unit_settings);  



        echo '<h3 class="title" data-id="'.$unit_id.'"><i class="icon-file"></i> '.$unit_title.'</h3>
                <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="'.get_permalink($unit_id).'edit/?id='.$course_id.'" target="_blank">'.__('Edit Unit','wplms-front-end').'</a></li>
                    <li><a href="'.get_permalink($unit_id).'" target="_blank">'.__('Preview Unit','wplms-front-end').'</a></li>
                    <li><a class="remove">'.__('Remove','wplms-front-end').'</a></li>
                    <li><a class="delete">'.__('Delete','wplms-front-end').'</a></li>
                </ul>
                </div>
            '; 

        //Linkage 
        $linkage = vibe_get_option('linkage');
        if(isset($linkage) && $linkage){
             $course_linkage=wp_get_post_terms( $course_id, 'linkage',array("fields" => "names"));
             if(isset($course_linkage) && is_array($course_linkage))
             wp_set_post_terms( $unit_id, $course_linkage, 'linkage' );
        }
        die();
    }
    function create_quiz(){
        $user_id= get_current_user_id();
        $course_id =$_POST['course_id'];
        $quiz_title = stripslashes($_POST['quiz_title']); 

        if(!isset($quiz_title) || count($quiz_title) < 2 && $quiz_title == ''){
            _e('Can not have a Blank Quiz','wplms-front-end');
             die();
        }

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_course'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-front-end');
             die();
        }

        $the_post = get_post($course_id);
        if($the_post->post_author != $user_id && !current_user_can('manage_options')){
            _e('Invalid Course Instructor','wplms-front-end');
             die();
        }

        $quiz_settings = array(
                        'post_title' => $quiz_title,
                        'post_content' => $quiz_title,
                        'post_status' => 'publish',
                        'post_type' => 'quiz',
                        );
        $quiz_settings=apply_filters('wplms_front_end_quiz_vars',$quiz_settings);
        $quiz_id = wp_insert_post($quiz_settings);  

        echo '<h3 class="title" data-id="'.$quiz_id.'"><i class="icon-task"></i> '.$quiz_title.'</h3>
                <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="'.get_permalink($quiz_id).'edit/?id='.$course_id.'" target="_blank" class="edit_quiz">'.__('Edit Quiz','wplms-front-end').'</a></li>
                    <li><a class="remove">'.__('Remove','wplms-front-end').'</a></li>
                    <li><a class="delete">'.__('Delete','wplms-front-end').'</a></li>
                </ul>
                </div>
            '; 

        //Linkage 
        $linkage = vibe_get_option('linkage');
        if(isset($linkage) && $linkage){
             $course_linkage=wp_get_post_terms( $course_id, 'linkage',array("fields" => "names"));
             if(isset($course_linkage) && is_array($course_linkage))
             wp_set_post_terms($quiz_id, $course_linkage, 'linkage' );
        }
        die();
    }
    function delete_curriculum(){
        $user_id= get_current_user_id();
        $course_id =$_POST['course_id'];
        $id = stripslashes($_POST['id']); 

        if(!isset($id) || !is_numeric($id) || $id == ''){
            _e('Can not delete','wplms-front-end').$id;
             die();
        }

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_course'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-front-end');
             die();
        }

        $the_post = get_post($id);
        if($the_post->post_author != $user_id && !current_user_can('manage_options')){
            _e('Instructor can not delete this unit/quiz','wplms-front-end');
             die();
        }

        $status=wp_trash_post($id);
        if($status){
            echo 1;
        }else{
            _e('Unable to delete','wplms-front-end');
        }
        die();
    }
    function save_curriculum(){
        $user_id= get_current_user_id();
        $course_id =$_POST['course_id'];
        
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_course'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-front-end');
             die();
        }

        $the_post = get_post($course_id);
        if($the_post->post_author != $user_id && !current_user_can('manage_options')){
            _e('Invalid Course Instructor','wplms-front-end');
             die();
        }

        $objcurriculum = json_decode(stripslashes($_POST['curriculum']));
        if(is_array($objcurriculum) && isset($objcurriculum))
        foreach($objcurriculum as $c){
            $curriculum[]=$c->id;
        }
        
       // $curriculum=array(serialize($curriculum)); // Backend Compatiblity
        update_post_meta($course_id,'vibe_course_curriculum',$curriculum);
        echo $course_id;
        do_action('wplms_course_curriculum_updated',$course_id,$curriculum);
        
        die();
    }

    function save_pricing(){
        $user_id= get_current_user_id();
        $course_id =$_POST['course_id'];
        
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_course'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-front-end');
             die();
        }

        $the_post = get_post($course_id);
        if($the_post->post_author != $user_id && !current_user_can('manage_options')){
            _e('Invalid Course Instructor','wplms-front-end');
             die();
        }

        $pricing = json_decode(stripslashes($_POST['pricing']));

        $vibe_course_free=$pricing->vibe_course_free;

        if($vibe_course_free == 'S' || $vibe_course_free === 'S'){
            update_post_meta($course_id,'vibe_course_free',$vibe_course_free);
            echo $course_id;
            die();
        }else if($vibe_course_free == 'H'){
            update_post_meta($course_id,'vibe_course_free',$vibe_course_free);
            echo 1;
        }

        if(isset($pricing->vibe_coming_soon) && $pricing->vibe_coming_soon == 'S'){
            update_post_meta($course_id,'vibe_coming_soon','S');
            echo $course_id;
            die();
        }else{
            update_post_meta($course_id,'vibe_coming_soon','H');
        }
        
        do_action('wplms_course_pricing_save',$course_id,$pricing);

        if(isset($pricing->vibe_product)){
            $vibe_product=$pricing->vibe_product;
            $vibe_product_price=$pricing->vibe_product_price;

            if(isset($pricing->vibe_subscription)){
                $vibe_subscription=$pricing->vibe_subscription;
                $vibe_duration=$pricing->vibe_duration;
            }
        }

        if(property_exists($pricing, 'vibe_pmpro_membership')){

            $vibe_pmpro_membership=$pricing->vibe_pmpro_membership;
            if(!count($vibe_pmpro_membership))
                $vibe_pmpro_membership=array();

            update_post_meta($course_id,'vibe_pmpro_membership',$vibe_pmpro_membership);
            do_action('wplms_course_pricing_membership_updated',$course_id,$vibe_pmpro_membership);
        }

        if(isset($vibe_product) && is_numeric($vibe_product)){
            update_post_meta($course_id,'vibe_product',$vibe_product);
            
            $products_meta = vibe_sanitize(get_post_meta($vibe_product,'vibe_courses',false));
            if(!isset($products_meta) || $products_meta == '' || count($products_meta) < 1)
                $products_meta = array();
                
            if(!in_array($course_id,$products_meta)){
                array_push($products_meta, $course_id);
            }
            update_post_meta($vibe_product,'vibe_courses',$products_meta);    
            echo $vibe_product;
            do_action('wplms_course_pricing_product_updated',$course_id,$vibe_product);
        }

        if(isset($vibe_product) && $vibe_product == 'none'){
            $pid=get_post_meta($course_id,'vibe_product',true);
             if(isset($pid) && is_numeric($pid)){
                delete_post_meta($course_id,'vibe_product');
                echo $pid;
                do_action('wplms_course_pricing_product_removed',$course_id,$pid);
            }
        }
        if(isset($vibe_product) && $vibe_product == 'add_new'){

            if(!is_numeric($vibe_product_price) || (!is_numeric($vibe_duration) && $vibe_subscription == 'S')){
                _e('Invalid Product specs','wplms-front-end');
                die();
            }
            $the_course = get_post($course_id);

            $product_settings = array(
            'post_status' => 'publish', 
            'post_type'  => 'product',
            'post_title' => $the_course->post_title,
            'post_excerpt' => $the_course->post_excerpt,
            'post_content' => $the_course->post_content,
            'comment_status' => 'open'
            );
            
            

            $product_settings = apply_filters('wplms_frontend_new_product',$product_settings);
            $product_id = wp_insert_post($product_settings);
            if(isset($product_id) && $product_id){

                $attach_id = get_post_meta($course_id, "_thumbnail_id", true);
                add_post_meta($post_id, '_thumbnail_id', $attach_id);
                wp_set_object_terms($product_id, 'simple', 'product_type');
                update_post_meta($product_id,'_price',$vibe_product_price);
                update_post_meta($product_id,'_regular_price',$vibe_product_price);
                update_post_meta($product_id,'_visibility','visible');
                update_post_meta($product_id,'_virtual','yes');
                update_post_meta($product_id,'_downloadable','yes');
                update_post_meta($product_id,'_sold_individually','yes');

                $courses = array($course_id);
                update_post_meta($product_id,'vibe_courses',$courses);
                update_post_meta($course_id,'vibe_product',$product_id);
                
                $thumbnail_id = get_post_thumbnail_id($course_id);
                set_post_thumbnail($product_id,$thumbnail_id);

                if($vibe_subscription == 'S'){
                    update_post_meta($product_id,'vibe_subscription','S');
                    update_post_meta($product_id,'vibe_duration',$vibe_duration);
                }
                echo $product_id;
                do_action('wplms_course_pricing_product_added',$course_id,$product_id);
                //Linkage 
                $linkage = vibe_get_option('linkage');
                if(isset($linkage) && $linkage){
                     $course_linkage=wp_get_post_terms( $course_id, 'linkage',array("fields" => "names"));
                     if(isset($course_linkage) && is_array($course_linkage))
                     wp_set_post_terms( $product_id, $course_linkage, 'linkage' );
                }
                die();
            }
            _e('Unable to create product and pricing for course','wplms-front-end');
        }

        do_action('wplms_front_end_save_course_pricing',$course_id);

        die();
    }

    function publish_course(){
        $user_id = get_current_user_id();
        $course_id = $_POST['course_id'];
        
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_course'.$user_id)){
             if(!wp_verify_nonce($_POST['security'],'security')  || !current_user_can('edit_posts')){
                _e('Security check Failed. Contact Administrator.','wplms-front-end'); 
                die();
            }
        }

        if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-front-end');
             die();
        }

        $the_post = get_post($course_id,'ARRAY_A'); // For futher use
        if($the_post['post_author'] != $user_id && !current_user_can('manage_options')){
            _e('Invalid Course Instructor','wplms-front-end');
             die();
        }

        $new_course_status = vibe_get_option('new_course_status');
        if(current_user_can('manage_options') || (isset($new_course_status) && $new_course_status == 'publish')){
            $the_post['post_status'] = 'publish';
        }else{
            $the_post['post_status'] = 'pending';
        }
        $the_post = apply_filters('wplms_frontend_course_update',$the_post);
        if(wp_update_post($the_post)){
            echo '<div id="message" class="success"><p>'.__('Course successfully updated.','wplms-front-end').'</p></div>';
            echo '<a href="'.get_permalink($course_id).'" class="button full">'.__('View Course','wplms-front-end').'</a>';
            do_action('wplms_course_go_live',$course_id,$the_post);
        }else{
           echo '<div id="message"><p>'.__('Unable to update Course, contact Site admin','wplms-front-end').'</p></div>'; 
        }
        
        die();
    }


    function offline_course(){
        $user_id= get_current_user_id();
        $course_id =$_POST['course_id'];
        
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_course'.$user_id)){ 
            if(!wp_verify_nonce($_POST['security'],'security')  || !current_user_can('edit_posts')){
                _e('Security check Failed. Contact Administrator.','wplms-front-end'); 
                die();
            }
        }

        if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-front-end');
             die();
        }
        
        $the_post = get_post($course_id,'ARRAY_A');
        $the_post['post_status'] = 'draft';
        if(wp_update_post($the_post)){
            echo '<div id="message" class="success"><p>'.__('Course Offline.','wplms-front-end').'</p></div>';
        }else{
           echo '<div id="message"><p>'.__('Unable to update Course, contact Site admin','wplms-front-end').'</p></div>'; 
        }
        die();
    }

    function delete_course(){
        global $wpdb;
        $user_id= get_current_user_id();
        $course_id =$_POST['course_id'];
        
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_course'.$user_id)){
            if(!wp_verify_nonce($_POST['security'],'security') || !current_user_can('edit_posts')){
                _e('Security check Failed. Contact Administrator.','wplms-front-end');
                die();
            }
        }

        if(!is_numeric($course_id) || get_post_type($course_id) != 'course'){
            _e('Invalid Course id, please edit a course','wplms-front-end');
             die();
        }
        $delete_flag = apply_filters('wplms_front_end_course_delete',0);
        if(!$delete_flag){
            _e('Course deletion not allowed','wplms-front-end');
             die();
        }

        $fields = json_decode(stripslashes($_POST['fields']));
        
        if(is_array($fields) && isset($fields)){
            foreach($fields as $key=>$c){
                if($c->post_meta == 'vibe_course_curriculum'){
                    $curriculum = vibe_sanitize(get_post_meta($course_id,$c->post_meta,false));
                    if(!is_array($curriculum) || !count($curriculum)){
                        echo '<div id="message"><p>'.__('Unable to delete curriclum','wplms-front-end').'</p></div>'; 
                        die();
                    }
                    
                    foreach($curriculum as $key=>$uid){
                        if(is_numeric($uid)){
                            $post_type = get_post_type($uid);
                            if($post_type == $c->post_type){
                                wp_trash_post($uid);
                            }
                        }
                    }
                }
                if($c->post_meta == 'vibe_assignment_course'){
                    $results = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s and meta_value = %d",'vibe_assignment_course',$course_id));
                    if(is_array($results) && count($results)){
                        foreach($results as $result){
                            if(isset($result->post_id) && is_numeric($result->post_id)){
                              wp_trash_post($result->post_id);
                            }
                        }
                    }
                }
                if($c->post_meta == 'vibe_product'){
                    $product_id = get_post_meta($course_id,'vibe_product',true);
                    if(!empty($product_id)){
                        wp_trash_post($product_id);
                    }
                }
            }
        }
        if(wp_trash_post($course_id)){

            echo '<div id="message" class="success"><p>'.__('Course Deleted.','wplms-front-end').'</p></div>';
            if(defined('BP_COURSE_SLUG') && defined('BP_COURSE_INSTRUCTOR_SLUG')){
                ?>
                <script>
                window.location.replace("<?php echo bp_loggedin_user_domain() . BP_COURSE_SLUG .'/'. BP_COURSE_INSTRUCTOR_SLUG; ?>");
                </script>
                <?php
            }
        }else{
           echo '<div id="message"><p>'.__('Unable to update Course, contact Site admin','wplms-front-end').'</p></div>'; 
        }
        die();
    }


    function wplms_frontend_create_course_pricing($course_pricing){

        if(isset($_GET['action']) && is_numeric($_GET['action'])){
            $course_id = $_GET['action'];
            $course_pricing['vibe_course_free'] = get_post_meta($course_id,'vibe_course_free',true);
            $course_pricing['vibe_product'] = get_post_meta($course_id,'vibe_product',true);
            if(isset($course_pricing['vibe_product']) && is_numeric($course_pricing['vibe_product'])){
                $course_pricing['vibe_subscription'] = get_post_meta($course_pricing['vibe_product'],'vibe_subscription',true);
                $course_pricing['vibe_duration'] = get_post_meta($course_pricing['vibe_product'],'vibe_duration',true);   
            }
            $course_pricing['vibe_pmpro_membership'] = vibe_sanitize(get_post_meta($course_id,'vibe_pmpro_membership',false));
            

        }
        return $course_pricing;
    }


    function wplms_front_end_unit_controls(){
        $unit_id=get_the_ID();

        if(!current_user_can('edit_posts'))
            return;

        $user_id = get_current_user_id();
        $unit_settings = array(
            'vibe_type' => 'text-document',
            'vibe_free' => 'H',
            'vibe_duration' => 2,
            'vibe_assignment' => array(),
            'vibe_forum' => ''
            );
        $unit_settings=apply_filters('wplms_front_end_unit_settings',$unit_settings);

        echo '<div class="wplms_front_end_wrapper">
              <h3 class="heading">'.__('Unit Settings','wplms-front-end').'</h3>';
        ?>
        <ul class="unit_settings">
            <li><label><?php _e('Unit Type','wplms-front-end'); ?></label>
                <h3><?php _e('Select Unit Type','wplms-front-end'); ?><span>
                <select id="vibe_type">
                    <?php
                    $unit_types = apply_filters('wplms_unit_types',array(
                      array( 'label' =>__('Video','wplms-front-end'),'value'=>'play'),
                      array( 'label' =>__('Audio','wplms-front-end'),'value'=>'music-file-1'),
                      array( 'label' =>__('Podcast','wplms-front-end'),'value'=>'podcast'),
                      array( 'label' =>__('General','wplms-front-end'),'value'=>'text-document'),
                    ));
                    foreach($unit_types as $unit){
                        echo '<option value="'.$unit['value'].'" '. selected($unit_settings['vibe_type'],$unit['value'],false).'>'.$unit['label'].'</option>';
                    }
                    ?>
                </select>    
                </span></h3></li>
            <li><label><?php _e('Free Unit','wplms-front-end'); ?></label>
                <h3><?php _e('Make Unit Free','wplms-front-end'); ?><span>
                    <div class="switch">
                        <input type="radio" class="switch-input vibe_free" name="vibe_free" value="H" id="free_no" <?php checked($unit_settings['vibe_free'],'H'); ?>>
                        <label for="free_no" class="switch-label switch-label-off"><?php _e('NO','wplms-front-end');?></label>
                        <input type="radio" class="switch-input vibe_free" name="vibe_free" value="S" id="free_yes" <?php checked($unit_settings['vibe_free'],'S'); ?>>
                        <label for="free_yes" class="switch-label switch-label-on"><?php _e('YES','wplms-front-end');?></label>
                        <span class="switch-selection"></span>
                      </div>
                    </span>
                </h3>
            </li>
            <li><label><?php _e('Unit Duration','wplms-front-end'); ?></label>
                <h3><?php _e('Duration of Unit','wplms-front-end'); ?><span>
                <input type="number" class="small_box" id="vibe_duration" value="<?php echo $unit_settings['vibe_duration']; ?>" /> <?php $unit_duration_parameter = apply_filters('vibe_unit_duration_parameter',60); echo calculate_duration_time($unit_duration_parameter); ?>
            </li>
            <?php
            do_action('wplms_front_end_unit_settings_form',$unit_settings);
            if ( in_array( 'wplms-assignments/wplms-assignments.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            ?>
            <li><label><?php _e('Unit assignments','wplms-front-end'); ?></label>
                <h3><?php _e('Connect Assignments','wplms-front-end'); ?></h3>
                <ul id="assignments_list">
                    <?php

                            $args= array(
                                'post_type'=> 'wplms-assignment',
                                'numberposts'=> -1
                                );
                                $args = apply_filters('wplms_frontend_cpt_query',$args);
                                $kposts=get_posts($args);

                    foreach($unit_settings['vibe_assignment'] as $assignment){
                        echo '<li data-id="'.$assignment.'"> 
                                <strong><i class="icon-text-document"></i>'.get_the_title($assignment).'</strong>';
                            echo '
                            <div class="btn-group">
                                <button type="button" class="btn btn-course dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="'.get_permalink($assignment).'edit/" target="_blank" class="edit_unit">'.__('Edit Assignment','wplms-front-end').'</a></li>
                                    <li><a href="'.get_permalink($assignment).'" target="_blank" class="edit_unit">'.__('Preview Assignment','wplms-front-end').'</a></li>
                                    <li><a class="remove">'.__('Remove','wplms-front-end').'</a></li>
                                    <li><a class="delete">'.__('Delete','wplms-front-end').'</a></li>
                                </ul>
                            </div>
                        </li>';
                    }

                    echo '<li class="hide"> 
                                <strong><select id="vibe_assignment">
                                    <option value="">'.__('None','wplms-front-end').'</option>';
                                    if(is_Array($kposts))
                                        foreach ( $kposts as $kpost ){
                                            echo '<option value="' . $kpost->ID . '">' . $kpost->post_title . '</option>';
                                        }
                            echo '</select></strong>
                            <div class="btn-group">
                                <button type="button" class="btn btn-course dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="" target="_blank" class="edit_unit">'.__('Edit Assignment','wplms-front-end').'</a></li>
                                    <li><a href="" target="_blank" class="preview_unit">'.__('Preview Assignment','wplms-front-end').'</a></li>
                                    <li><a class="remove">'.__('Remove','wplms-front-end').'</a></li>
                                    <li><a class="delete">'.__('Delete','wplms-front-end').'</a></li>
                                </ul>
                            </div>
                        </li>';

                    ?>    
                </ul>
                <hr />
                 <ul class="new_assignment_actions">
                    <li><a class="link add_existing_assignment"><?php _e('ADD EXISTING ASSIGNMENT','wplms-front-end'); ?></a></li>
                    <li><a class="link add_new_assignment"><?php _e('ADD NEW ASSIGNMENT','wplms-front-end'); ?></a></li>
                    <li><input type="text" name="new_assignment_title" class="new_assignment_title mid_box left" placeholder="<?php _e('Add the Assignment title','wplms-front-end'); ?>"/>
                        <div class="btn-group">
                          <button type="button" class="btn btn-course dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                          <ul class="dropdown-menu" role="menu">
                            <li><a class="publish"><?php _e('Publish','wplms-front-end'); ?></a></li>
                            <li><a class="new_remove"><?php _e('Remove','wplms-front-end'); ?></a></li>
                          </ul>
                        </div>
                    </li>
                </ul>
                <hr class="clear" />
            </li>
            <?php
             }
             if(isset($_GET['id'])){
            ?>
            <li><label><?php _e('Unit Forum','wplms-front-end'); ?></label>
                <h3><?php _e('Connect a Forum','wplms-front-end'); ?><span>
                <select id="vibe_forum" class="chosen">
                    <option value=""><?php _e('None','wplms-front-end'); ?></option>
                    <option value="add_group_child_forum"><?php _e('Add new child forum in Course forum','wplms-front-end'); ?></option>
                    <option value="add_new"><?php _e('Add new forum','wplms-front-end'); ?></option>
                    <?php
                        $args= array(
                        'post_type'=> 'forum',
                        'numberposts'=> -1
                        );
                        $args = apply_filters('wplms_frontend_cpt_query',$args);
                        $kposts=get_posts($args);
                        if(is_array($kposts))
                        foreach ( $kposts as $kpost ){
                            echo '<option value="' . $kpost->ID . '" '.selected($unit_settings['vibe_forum'],$kpost->ID).'>' . $kpost->post_title . '</option>';
                        }
                    ?>
                </select></span>
                </h3>                    
            </li>
            <?php
                }
            ?>
            <li>
                <a id="save_unit_settings" class="course_button button full" data-id="<?php echo get_the_ID(); ?>" data-course="<?php echo $_GET['id']; ?>"><?php _e('SAVE UNIT SETTINGS','wplms-front-end'); ?></a>
            </li>
        </ul> 
        </div>   
        <?php
        wp_nonce_field('save_unit'.$user_id,'security');
    }


    function wplms_front_end_unit_settings($unit_settings){
        global $post;
        $unit_id=$post->ID;
        foreach($unit_settings as $key=>$setting){

            if($key == 'vibe_assignment'){
                $setting = vibe_sanitize(get_post_meta($unit_id,$key,false));
            }else{
                $setting = get_post_meta($unit_id,$key,true);
            }

            if(isset($setting) && $setting)
                $unit_settings[$key]=$setting;

        }

        return $unit_settings;
    }

    function save_unit_settings(){
        $user_id = get_current_user_id();
        $course_id = $_POST['course_id'];
        $unit_id = $_POST['unit_id'];
        $vibe_type = $_POST['vibe_type'];
        $vibe_free = $_POST['vibe_free'];
        $vibe_duration = $_POST['vibe_duration'];

        if(isset($_POST['vibe_assignment'])){
            $vibe_assignment = $_POST['vibe_assignment'];    
        }

        if(isset($_POST['vibe_forum']))
            $vibe_forum = $_POST['vibe_forum'];   

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'save_unit'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        if((isset($_POST['vibe_forum']) && $_POST['vibe_forum']) && (!is_numeric($course_id) || get_post_type($course_id) != 'course')){
            _e('Invalid Course id, please edit a course','wplms-front-end');
             die();
        }

        if(!is_numeric($unit_id) || get_post_type($unit_id) != 'unit'){
            _e('Invalid Unit id, please edit a course','wplms-front-end');
             die();
        }

        $unit_post = get_post($unit_id,ARRAY_A);
        if($unit_post['post_author'] != $user_id && !current_user_can('manage_options')){
            _e('Invalid Unit Instructor','wplms-front-end');
             die();
        }

        $flag=1;
        
        update_post_meta($unit_id,'vibe_type',$vibe_type);
        update_post_meta($unit_id,'vibe_free',$vibe_free);
        update_post_meta($unit_id,'vibe_duration',$vibe_duration);
        
        if(isset($vibe_assignment) && $flag){
            $vibe_assignment = json_decode(stripslashes($vibe_assignment));
            $assignments = array();
            if(is_array($vibe_assignment)){
                foreach($vibe_assignment as $c){
                    $assignments[]= $c->id;
                }
            }
            

            if(is_array($assignments) && isset($assignments)){   
                update_post_meta($unit_id,'vibe_assignment',$assignments);
            }
        }  

        if(isset($vibe_forum) && $flag) 
            if(is_numeric($vibe_forum)){
                update_post_meta($unit_id,'vibe_forum',$vibe_forum);
            }
             
           
            if($vibe_forum == 'add_group_child_forum' && $flag){
               
                $group_id = get_post_meta($course_id,'vibe_group',true);
                if(isset($group_id) && is_numeric($groupd_id)){
                    $forum_id = groups_get_groupmeta( $group_id, 'forum_id');
                    if(is_array($forum_id))
                        $forum_id=$forum_id[0];
                }else{
                    $forum_id = get_post_meta($course_id,'vibe_forum',true);
                }
                if(isset($forum_id) && is_numeric($forum_id)){
                    $forum_settings = array(
                        'post_title' => stripslashes( $unit_post['post_title'] ),
                        'post_content' => stripslashes( $unit_post['post_excerpt'] ),
                        'post_name' => $unit_post['post_name'],
                        'post_parent' => $forum_id,
                        'post_status' => 'publish',
                        'post_type' => 'forum',
                        'comment_status' => 'closed'
                    );
                    $forum_settings=apply_filters('wplms_front_end_forum_vars',$forum_settings);
                    if(isset($forum_id) && is_numeric($forum_id))
                        $new_forum_id = wp_insert_post($forum_settings);
                    if(!update_post_meta($unit_id,'vibe_forum',$new_forum_id))
                        $flag=0;
                }
            }

            if($vibe_forum == 'add_new' && $flag){
                $forum_settings = array(
                        'post_title' => stripslashes( $unit_post['post_title'] ),
                        'post_content' => stripslashes( $unit_post['post_excerpt'] ),
                        'post_name' => $unit_post['post_name'],
                        'post_status' => 'publish',
                        'post_type' => 'forum',
                        'comment_status' => 'closed'
                    );
                $forum_settings=apply_filters('wplms_front_end_forum_vars',$forum_settings);
                $new_forum_id = wp_insert_post($forum_settings);
                if(!update_post_meta($unit_post->ID,'vibe_forum',$new_forum_id))
                    $flag=0;
            }

            do_action('wplms_front_end_save_unit_settings_extras',$unit_id,$flag);
            if($flag)
                 _e('Settings Saved','wplms-front-end');
            else
                _e('Unable to save settings','wplms-front-end');

            die();
    }

    function wplms_front_end_quiz_controls(){
        global $wp_query,$post;
        if((!isset($_GET['edit']) && !isset($wp_query->query_vars['edit'])) || !current_user_can('edit_posts'))
            return;

        $user_id = get_current_user_id();
        wp_nonce_field('create_quiz'.$user_id,'security');
        $course_id = get_post_meta($post->ID,'vibe_quiz_course',true);
        if(isset($course_id) && is_numeric($course_id)){
        }else{
            if(!isset($_GET['id']) || !is_numeric($_GET['id']))
                return;
            $course_id = $_GET['id'];
        }
            $quiz_dynamic = get_post_meta($post->ID,'vibe_quiz_dynamic',true);
            if(isset($quiz_dynamic) && $quiz_dynamic == 'S')
                return;
            ?>
            <div id="quiz_question_controls">
            <h3 class="heading"><?php _e('Manage Quiz Questions','wplms-front-end'); ?></h3>
            

            <a id="add_question" class="button primary small"><?php _e('ADD QUESTION','wplms-front-end'); ?></a>
            <ul id="questions">
            <?php
            $quiz_questions = vibe_sanitize(get_post_meta(get_the_ID(),'vibe_quiz_questions',false));
            if(isset($quiz_questions['ques']))
                $questions = $quiz_questions['ques'];
            if(isset($quiz_questions['marks']))
                $marks = $quiz_questions['marks'];
            
            if(isset($questions))
            foreach($questions as $key=>$question){
                echo '<li><strong>'.__('Question ','wplms-front-end').($key+1).' : '.get_the_title($question).'</strong>
                        <div class="btn-group">
                            <button type="button" class="btn btn-course dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="'.get_permalink($question).'edit/" target="_blank" class="edit_unit">'.__('Edit Question','wplms-front-end').'</a></li>
                                <li><a href="'.get_permalink($question).'" target="_blank" class="edit_unit">'.__('Preview Question','wplms-front-end').'</a></li>
                                <li><a class="remove">'.__('Remove','wplms-front-end').'</a></li>
                                <li><a class="delete">'.__('Delete','wplms-front-end').'</a></li>
                            </ul>
                        </div>
                        <span>'.__('MARKS : ','wplms-front-end').$marks[$key].'<input type="hidden" class="question_marks" value="'.$marks[$key].'" /></span>
                        <input type="hidden" class="question" value="'.$question.'" />
                    </li>';
            }
            ?>
            </ul>
            <ul id="hidden">
                <li class="new_question">
                    <select class="question">
                        <option value="none"><?php _e('None','wplms-front-end'); ?></option>
                        <option value="add_new"><?php _e('ADD A NEW QUESTION','wplms-front-end'); ?></option>
                        <?php
                            $args= array(
                            'post_type'=> 'question',
                            'numberposts'=> -1
                            );
                            $args = apply_filters('wplms_frontend_cpt_query',$args);
                            $kposts=get_posts($args);
                            if(is_array($kposts))
                            foreach ( $kposts as $kpost ){
                                echo '<option value="' . $kpost->ID . '">' . $kpost->post_title . '</option>';
                            }
                        ?>
                    </select>
                    <a class="rem right"><i class="icon-x"></i></a>
                    <span>
                        <?php _e('Marks : ','wplms-front-end'); ?><input type="number" class="small_box question_marks" value="0" />
                    </span>
                    <h3 class="new_q"><?php _e('Question Title : ','wplms-front-end'); ?><span>
                    <input type="text" class="question_title large_box" placeholder="<?php _e('Enter title for reference','wplms-front-end'); ?>"  />
                        <div class="btn-group">
                            <button type="button" class="btn btn-course dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a class="publish"><?php _e('Publish Question','wplms-front-end'); ?></a></li>
                                <li><a class="remove"><?php _e('Remove','wplms-front-end'); ?></a></li>
                            </ul>
                        </div>
                    </span></h3>
                </li>
            </ul>
            <a data-quiz="<?php echo get_the_ID(); ?>" class="save_quiz_settings button hero"><?php _e('SAVE QUIZ SETTINGS','wplms-front-end'); ?></a>
            </div>
            <?php
    }

    function wplms_front_end_quiz_meta_controls(){
        global $wp_query,$post;
        if((!isset($_GET['edit']) && !isset($wp_query->query_vars['edit'])) || !current_user_can('edit_posts'))
            return;
            $user_id = get_current_user_id();

            $course_id = get_post_meta($post->ID,'vibe_quiz_course',true);
            if(isset($course_id) && is_numeric($course_id)){

            }else{
                if(!isset($_GET['id']) || !is_numeric($_GET['id']))
                    return;

                $course_id = $_GET['id'];
            }


            $quiz_settings = array(
                'vibe_subtitle' =>__('Enter a Quiz sub-title','wplms-front-end'),
                'vibe_quiz_course' => $course_id,
                'vibe_duration' => 10,
                'vibe_quiz_auto_evaluate' =>'H',
                'vibe_quiz_dynamic' => 'H',
                'vibe_quiz_tags'=>array(),
                'vibe_quiz_number_questions'=>0,
                'vibe_quiz_marks_per_question'=>0,
                'vibe_quiz_retakes' => 0,
                'vibe_quiz_random' => 'H',
                'vibe_quiz_message' =>__('Enter a Quiz Completion message','wplms-front-end')
                );

            $quiz_settings = apply_filters('wplms_front_end_quiz_settings',$quiz_settings);
        ?>
       <div class="wplms_front_end_wrapper">
        <h3 class="heading"><?php _e('Quiz Settings','wplms-front-end'); ?></h3>
         <article class="live-edit" data-model="article" data-id="1" data-url="/articles">
         <ul class="settings_quiz">
         <li>
            <label><?php _e('QUIZ SUB-TITLE','wplms-front-end'); ?></label>
            <div id="vibe_subtitle" data-editable="true" data-name="content" data-max-length="250" data-text-options="true">
            <p><?php echo strip_tags($quiz_settings['vibe_subtitle'], '<br><br/>');; ?></p>
            </div>
        </li><li>    
            <label><?php _e('CONNECTED COURSE','wplms-front-end'); ?></label>
            <select class="chosen" id="vibe_quiz_course">
                <?php
                            $args= array(
                            'post_type'=> 'course',
                            'numberposts'=> -1
                            );
                            $args = apply_filters('wplms_frontend_cpt_query',$args);
                            $kposts=get_posts($args);
                            if(is_array($kposts))
                            foreach ( $kposts as $kpost ){
                                echo '<option value="' . $kpost->ID . '" '.selected($quiz_settings['vibe_quiz_course'],$kpost->ID).'>' . $kpost->post_title . '</option>';
                            }
                        ?>
            </select>
        </li><li><label><?php _e('QUIZ DURATION','wplms-front-end'); ?></label>
            <input type="number" class="small_box" id="vibe_duration" value="<?php echo $quiz_settings['vibe_duration']; ?>" /> <?php $quiz_duration_parameter = apply_filters('vibe_quiz_duration_parameter',60); echo calculate_duration_time($quiz_duration_parameter); ?>
        </li>
        <li><label><?php _e('QUIZ EVALUATION','wplms-front-end'); ?></label>
            <div class="switch">
            <input type="radio" class="switch-input vibe_quiz_auto_evaluate" name="vibe_quiz_auto_evaluate" value="S" id="quiz_auto_evaluate_on" <?php checked($quiz_settings['vibe_quiz_auto_evaluate'],'S'); ?>>
            <label for="quiz_auto_evaluate_on" class="switch-label switch-label-off"><?php _e('AUTO','wplms-front-end');?></label>
            <input type="radio" class="switch-input vibe_quiz_auto_evaluate" name="vibe_quiz_auto_evaluate" value="H" id="quiz_auto_evaluate_off" <?php checked($quiz_settings['vibe_quiz_auto_evaluate'],'H'); ?>>
            <label for="quiz_auto_evaluate_off" class="switch-label switch-label-on"><?php _e('MANUAL','wplms-front-end');?></label>
            <span class="switch-selection"></span>
            </div>
        </li>
        <li><label><?php _e('QUIZ QUESTIONS TYPE','wplms-front-end'); ?></label>
            <div class="switch">
                <input type="radio" class="switch-input vibe_quiz_dynamic" name="vibe_quiz_dynamic" value="S" id="quiz_dynamic_on" <?php checked($quiz_settings['vibe_quiz_dynamic'],'S'); ?>>
                <label for="quiz_dynamic_on" class="switch-label switch-label-off"><?php _e('DYNAMIC','wplms-front-end');?></label>
                <input type="radio" class="switch-input vibe_quiz_dynamic" name="vibe_quiz_dynamic" value="H" id="quiz_dynamic_off" <?php checked($quiz_settings['vibe_quiz_dynamic'],'H'); ?>>
                <label for="quiz_dynamic_off" class="switch-label switch-label-on"><?php _e('STATIC','wplms-front-end');?></label>
                <span class="switch-selection"></span>
            </div>
        </li> 
        <li class="dynamic <?php echo (($quiz_settings['vibe_quiz_dynamic']=='S')?'':'hide_it');?>"><label><?php _e('SELECT QUESTION TAGS','wplms-front-end'); ?></label>
              <select id="vibe_quiz_tags" class="chosen" multiple>
              <?php
                $terms = get_terms('question-tag', array('fields' => 'id=>name') );
                
                if(isset($terms) && is_array($terms))                                                        
                foreach ($terms as $key=>$term )
                    echo '<option value="' . $key . '" '.(in_array($key,$quiz_settings['vibe_quiz_tags'])?'SELECTED':'').'>' . $term . '</option>';
              ?>
              </select>
        </li>
        <li class="dynamic <?php echo (($quiz_settings['vibe_quiz_dynamic']=='S')?'':'hide_it');?>"><label><?php _e('NUMER OF QUESTIONS','wplms-front-end'); ?></label>
              <input type="number" class="small_box" id="vibe_quiz_number_questions" value="<?php echo $quiz_settings['vibe_quiz_number_questions']; ?>" />
        </li>
        <li class="dynamic <?php echo (($quiz_settings['vibe_quiz_dynamic']=='S')?'':'hide_it');?>"><label><?php _e('MARKS PER QUESTION','wplms-front-end'); ?></label>
              <input type="number" class="small_box" id="vibe_quiz_marks_per_question" value="<?php echo $quiz_settings['vibe_quiz_marks_per_question']; ?>" />
        </li>
        <li><label><?php _e('QUIZ RETAKES','wplms-front-end'); ?></label>
            <input type="number" id="vibe_quiz_retakes" class="small_box" value="<?php echo $quiz_settings['vibe_quiz_retakes']; ?>" /><?php _e(' Retakes','wplms-front-end'); ?>
        </li>
        <li><label><?php _e('QUESTIONS ORDER','wplms-front-end'); ?></label>
            <div class="switch">
                <input type="radio" class="switch-input vibe_quiz_random" name="vibe_quiz_random" value="S" id="quiz_random_on" <?php checked($quiz_settings['vibe_quiz_random'],'S'); ?>>
                <label for="quiz_random_on" class="switch-label switch-label-off"><?php _e('RANDOM','wplms-front-end');?></label>
                <input type="radio" class="switch-input vibe_quiz_random" name="vibe_quiz_random" value="H" id="quiz_random_off" <?php checked($quiz_settings['vibe_quiz_random'],'H'); ?>>
                <label for="quiz_random_off" class="switch-label switch-label-on"><?php _e('SEQUENTIAL','wplms-front-end');?></label>
                <span class="switch-selection"></span>
            </div>
        </li> 
        <li><label><?php _e('QUIZ COMPLETION MESSAGE','wplms-front-end'); ?></label>
            <div id="vibe_quiz_message" data-editable="true" data-name="content" data-max-length="500" data-text-options="true">
            <p><?php echo $quiz_settings['vibe_quiz_message']; ?></p>
            </div>
        </li>
        <?php 
            do_action('wplms_front_end_quiz_settings_action',$quiz_settings);
        ?>
        </ul>
        </article>
        <a data-quiz="<?php echo get_the_ID(); ?>" class="save_quiz_settings button hero"><?php _e('SAVE QUIZ SETTINGS','wplms-front-end'); ?></a>
        </div>
        <?php
    }

    function create_question(){
        $user_id= get_current_user_id();
        $question_title = stripcslashes($_POST['title']);
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_quiz'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        $question_settings = array(
                        'post_title' => $question_title,
                        'post_content' => sprintf(__('Add Content for %s','wplms-front-end'),$question_title),
                        'post_status' => 'publish',
                        'post_type' => 'question',
                        );
        $question_settings=apply_filters('wplms_front_end_question_vars',$question_settings);
        $question_id = wp_insert_post($question_settings);  

        echo '<strong>'.__('Question ','wplms-front-end').' : '.$question_title.'</strong>
                        <div class="btn-group right">
                            <button type="button" class="btn btn-course dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="'.get_permalink($question_id).'edit/" target="_blank" class="edit_unit">'.__('Edit Question','wplms-front-end').'</a></li>
                                <li><a class="remove">'.__('Remove','wplms-front-end').'</a></li>
                                <li><a class="delete">'.__('Delete','wplms-front-end').'</a></li>
                            </ul>
                        </div>
                        <span>'.__('MARKS : ','wplms-front-end').'<input type="number" class="question_marks small_box" value="0" /></span>
                        <input type="hidden" class="question" value="'.$question_id.'" />
                    ';

        //Linkage 
        $linkage = vibe_get_option('linkage');
        if(isset($linkage) && $linkage){
             $quiz_id = $_POST['quiz_id'];
             $quiz_linkage=wp_get_post_terms( $quiz_id, 'linkage',array("fields" => "names"));
             if(isset($quiz_linkage) && is_array($quiz_linkage))
             wp_set_post_terms( $question_id, $quiz_linkage, 'linkage' );
        }

        die();
    }
    function save_quiz_settings(){
        $user_id= get_current_user_id();
        $quiz_id = $_POST['quiz_id'];
        
        $vibe_subtitle= $_POST['vibe_subtitle'];
        $vibe_quiz_course= $_POST['vibe_quiz_course'];
        $vibe_duration= $_POST['vibe_duration'];
        $vibe_quiz_auto_evaluate= $_POST['vibe_quiz_auto_evaluate'];
        $vibe_quiz_retakes= $_POST['vibe_quiz_retakes'];
        $vibe_quiz_message= $_POST['vibe_quiz_message'];  
        $vibe_quiz_dynamic= $_POST['vibe_quiz_dynamic'];
        $vibe_quiz_tags= $_POST['vibe_quiz_tags'];
        $vibe_quiz_number_questions= $_POST['vibe_quiz_number_questions'];
        $vibe_quiz_random=$_POST['vibe_quiz_random'];
        $vibe_quiz_marks_per_question= $_POST['vibe_quiz_marks_per_question'];
        
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_quiz'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        if(!is_numeric($quiz_id)){
            _e('Invalid Quiz','wplms-front-end');
             die();
        }
        update_post_meta($quiz_id,'vibe_subtitle',$vibe_subtitle);
        update_post_meta($quiz_id,'vibe_quiz_course',$vibe_quiz_course);
        update_post_meta($quiz_id,'vibe_duration',$vibe_duration);
        update_post_meta($quiz_id,'vibe_quiz_auto_evaluate',$vibe_quiz_auto_evaluate);
        update_post_meta($quiz_id,'vibe_quiz_retakes',$vibe_quiz_retakes);
        update_post_meta($quiz_id,'vibe_quiz_message',$vibe_quiz_message);

        update_post_meta($quiz_id,'vibe_quiz_dynamic',$vibe_quiz_dynamic);
        update_post_meta($quiz_id,'vibe_quiz_tags',$vibe_quiz_tags);
        update_post_meta($quiz_id,'vibe_quiz_number_questions',$vibe_quiz_number_questions);
        update_post_meta($quiz_id,'vibe_quiz_marks_per_question',$vibe_quiz_marks_per_question);
        update_post_meta($quiz_id,'vibe_quiz_random',$vibe_quiz_random);

        $objquestions = json_decode(stripslashes($_POST['questions']));
        $questions = array();
        if(is_array($objquestions) && isset($objquestions))
        foreach($objquestions as $c){
            $questions['ques'][]= $c->ques;
            $questions['marks'][]= $c->marks;
        }
        
        update_post_meta($quiz_id,'vibe_quiz_questions',$questions);
        
        if(isset($_POST['extras'])){
            $obj = json_decode(stripslashes($_POST['extras']));
            $marks = array();
            if(is_array($obj) && isset($obj)){
                foreach($obj as $extra){
                    update_post_meta($quiz_id,$extra->element,$extra->value);
                }
            }
        }

        do_action('wplms_front_end_save_quiz_settings_extras',$quiz_id);

        _e('Quiz Settings saved','wplms-front-end');

        die();
    }   

    function wplms_front_end_quiz_settings($quiz_settings){
        $quiz_id = get_the_ID();
        foreach($quiz_settings as $key => $value){
            if($key == 'vibe_quiz_tags'){
                $value = vibe_sanitize(get_post_meta($quiz_id,$key,false));
            }else
                $value= get_post_meta($quiz_id,$key,true);

            if(isset($value) && $value)
                $quiz_settings[$key] = $value;
        }
        return $quiz_settings;
    }
    function delete_question(){
        $user_id= get_current_user_id();
        $id = stripslashes($_POST['id']); 

        if(!isset($id) || !is_numeric($id) && $id == ''){
            _e('Can not delete','wplms-front-end');
             die();
        }

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'create_quiz'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        $the_post = get_post($id);
        if($the_post->post_author != $user_id && !current_user_can('manage_options')){
            _e('Instructor can not delete this unit/quiz','wplms-front-end');
             die();
        }

        $status=wp_trash_post($id);
        if($status){
            echo 1;
        }else{
            _e('Unable to delete','wplms-front-end');
        }
        die();
    }

    function wplms_front_end_question_controls(){
        global $wp_query;
        $user_id = get_current_user_id();
        if((!isset($_GET['edit']) && !isset($wp_query->query_vars['edit'])) || !current_user_can('edit_posts'))
            return;

        $question_settings = array(
            'vibe_question_type' => 'single',
            'vibe_question_options' => '',
            'vibe_question_answer' => 0,
            'vibe_question_hint' => '',
            'vibe_question_explaination' => ''
            );
        $question_settings = apply_filters('wplms_front_end_question_settings',$question_settings);

        $question_types = apply_filters('wplms_question_types',array(
              array( 'label' => __('True or False','wplms-front-end'),'value'=>'truefalse'),  
              array( 'label' =>__('Multiple Choice','wplms-front-end'),'value'=>'single'),
              array( 'label' =>__('Multiple Correct','wplms-front-end'),'value'=>'multiple'),
              array( 'label' =>__('Sort Answers','wplms-front-end'),'value'=>'sort'),
              array( 'label' =>__('Match Answers','wplms-front-end'),'value'=>'match'),
              array( 'label' =>__('Fill in the Blank','wplms-front-end'),'value'=>'fillblank'),
              array( 'label' =>__('Dropdown Select','wplms-front-end'),'value'=>'select'),
              array( 'label' =>__('Small Text','wplms-front-end'),'value'=>'smalltext'),
              array( 'label' =>__('Large Text','wplms-front-end'),'value'=>'largetext')
            ));
        ?>
        <div class="wplms_front_end_wrapper">
        <h3 class="heading"><?php _e('QUESTION SETTINGS','wplms-front-end'); ?></h3>
        <ul class="question_settings">
            <li><h3><?php _e('Question Type','wplms-front-end'); ?><span>
            <select id="vibe_question_type" class="chosen">
                <?php
                    foreach($question_types as $question_type){
                        echo '<option value="'.$question_type['value'].'" '.selected($question_settings['vibe_question_type'],$question_type['value']).'>'.$question_type['label'].'</option>';
                    }
                ?>
            </select></span>
            </h3>
            </li>
            <li class="optionli">
                <a id="add_option" class="button small primary"><?php _e('ADD OPTION','wplms-front-end'); ?></a>
                <ul class="vibe_question_options <?php echo $question_settings['vibe_question_type']; ?>">
                    <?php
                        
                        if(isset($question_settings['vibe_question_answer'])){
                            $answer = explode(',',$question_settings['vibe_question_answer']);
                        }

                        if(isset($question_settings['vibe_question_options']) && is_array($question_settings['vibe_question_options'])){
                            foreach($question_settings['vibe_question_options'] as $key => $option){
                                echo '<li '.(in_array(($key+1),$answer)?'class="selected"':'').'><span class="tip" title="'.__('Click to select as Correct answer','wplms-front-end').'">'.($key+1).'</span><input type="text" class="option very_large_box" value="'.$option.'" /><a class="rem"><i class="icon-x"></i></a></li>';
                            }
                        }
                    ?>
                </ul>
                <ul class="hidden">
                    <li><span></span><input type="text" class="option very_large_box" /><a class="rem"><i class="icon-x"></i></a></li>
                </ul>
            </li>
            <li><h3><?php _e('Correct Answer','wplms-front-end'); ?></h3><input type="text" id="vibe_question_answer" class="very_large_box" value="<?php echo $question_settings['vibe_question_answer']; ?>" /></li>
            <li><h3><?php _e('Answer Hint','wplms-front-end'); ?></h3><input type="text" id="vibe_question_hint" class="very_large_box" value="<?php echo $question_settings['vibe_question_hint']; ?>" /></li>
            <li><h3><?php _e('Answer Explanation','wplms-front-end'); ?></h3>
                <article class="live-edit" data-model="article" data-id="1" data-url="/articles">
                    <div id="vibe_question_explaination" data-editable="true" data-name="content" data-max-length="350" data-text-options="true">
                        <?php echo $question_settings['vibe_question_explaination']; ?>
                    </div>
                </article>
            </li>
        </ul>
        <a id="save_question_settings" class="button hero"><?php _e('SAVE QUESTION SETTINGS','wplms-front-end'); ?></a>
        <input type="hidden" value="<?php echo get_the_ID(); ?>" id="question_id" />
        <?php
         wp_nonce_field('save_question'.$user_id,'security');
         ?>
         </div>
         <?php
    }

    function wplms_front_end_question_settings($question_settings){

        foreach($question_settings as $key => $value){
            if($key == 'vibe_question_options')
                $question_settings[$key] = vibe_sanitize(get_post_meta(get_the_ID(),$key,false));
            else
                $question_settings[$key] = get_post_meta(get_the_ID(),$key,true);
        }

        return $question_settings;
    }
    function save_question(){
        $qid=$_POST['id'];
        
        $question_settings = array(
            'vibe_question_type' => 'single',
            'vibe_question_options' => '',
            'vibe_question_answer' => 0,
            'vibe_question_hint' => '',
            'vibe_question_explaination' => ''
            );
        $question_settings = apply_filters('wplms_front_end_question_settings',$question_settings);

        $vibe_question_options = array();
        $user_id = get_current_user_id();
        if(!isset($qid) || !is_numeric($qid) && $qid == ''){
            _e('Unable to save, incorrect question','wplms-front-end');
             die();
        }

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'save_question'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        foreach($question_settings as $key => $value){ //print_r($key.' = '.$value.' != '.$_POST[$key]);
            if($value !== $_POST[$key] && $_POST[$key] != ''){
                if($key != 'vibe_question_options'){
                    update_post_meta($qid,$key,$_POST[$key]);
                }
            }
        }

        $objcurriculum = json_decode(stripslashes($_POST['vibe_question_options']));
        if(is_array($objcurriculum) && isset($objcurriculum))
        foreach($objcurriculum as $c){
            $vibe_question_options[]=$c->option;
        }  
        
        update_post_meta($qid,'vibe_question_options',$vibe_question_options);
        
        _e('Question settings successfully saved','wplms-front-end');
        die();
    }

    function create_assignment(){
        $user_id = get_current_user_id();
        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'save_unit'.$user_id)  || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }
        $unit_id = $_POST['unit_id'];
        $title = $_POST['title'];

        if(!isset($unit_id) || !is_numeric($unit_id) || $unit_id == '' || $title == '' || strlen($title) < 5 ){
            _e('Unable to save, incorrect unit','wplms-front-end');
             die();
        }

        $assignment_settings = array(
                        'post_title' => $title,
                        'post_content' => $title,
                        'post_status' => 'publish',
                        'post_type' => 'wplms-assignment',
                        );
        $assignment_settings=apply_filters('wplms_front_end_assignment_vars',$assignment_settings);
        $assignment_id = wp_insert_post($assignment_settings);  

        $assignment_array = array(
            'id'=>$assignment_id,
            'title' => $title,
            'link' => get_permalink($assignment_id)
            );
        echo json_encode($assignment_array);
        die();
    }

    function wplms_front_end_assignment_controls(){
        $user_id = get_current_user_id();

        global $wp_query;
        
        if((!isset($_GET['edit']) && !isset($wp_query->query_vars['edit'])) || !current_user_can('edit_posts'))
            return;

        $assignment_settings = array(
            'vibe_subtitle' => 'Enter a Subtitle',
            'vibe_assignment_marks' => 0,
            'vibe_assignment_duration' => 0,
            'vibe_assignment_evaluation' => 'H',
            'vibe_assignment_course' => '',
            'vibe_assignment_submission_type' => 'upload',
            'vibe_attachment_type' =>'',
            'vibe_attachment_size' =>''
            );
        $assignment_settings = apply_filters('wplms_front_end_assignment_settings',$assignment_settings);

        ?>
        <div class="wplms_front_end_wrapper">
        <h3 class="heading"><?php _e('ASSIGNMENT SETTINGS','wplms-front-end'); ?></h3>
         <article class="live-edit" data-model="article" data-id="1" data-url="/articles">
         <ul class="assignment_settings">
         <li>
            <label><?php _e('ASSIGNMENT SUB-TITLE','wplms-front-end'); ?></label>
            <div id="vibe_subtitle" data-editable="true" data-name="content" data-max-length="250" data-text-options="true">
            <p><?php echo $assignment_settings['vibe_subtitle']; ?></p>
            </div>
        </li>
        <li>
            <label><?php _e('ASSIGNMENT MARKS','wplms-front-end'); ?></label>
            <h3><?php _e('Assignment Maximum Marks','wplms-front-end'); ?></h3><input type="number" class="small_box" id="vibe_assignment_marks" value="<?php echo $assignment_settings['vibe_assignment_marks']; ?>" /> <?php _e('MARKS','wplms-front-end'); ?>
        </li>
        <li>
            <label><?php _e('ASSIGNMENT DURATION','wplms-front-end'); ?></label>
            <h3><?php _e('Enter Assignment Duration','wplms-front-end'); ?></h3><input type="number" class="small_box" id="vibe_assignment_duration" value="<?php echo $assignment_settings['vibe_assignment_duration']; ?>" /> <?php $assignment_duration_parameter = apply_filters('vibe_assignment_duration_parameter',86400); echo calculate_duration_time($assignment_duration_parameter); ?>
        </li>
         <li><label><?php _e('ASSIGNMENT EVALUATION','wplms-front-end'); ?></label>
            <h3><?php _e('Include in Course Evaluation','wplms-front-end'); ?></h3>
            <div class="switch">
            <input type="radio" class="switch-input vibe_assignment_evaluation" name="vibe_assignment_evaluation" value="S" id="vibe_assignment_evaluate_on" <?php checked($assignment_settings['vibe_assignment_evaluation'],'S'); ?>>
            <label for="vibe_assignment_evaluate_on" class="switch-label switch-label-off"><?php _e('YES','wplms-front-end');?></label>
            <input type="radio" class="switch-input vibe_assignment_evaluation" name="vibe_assignment_evaluation" value="H" id="vibe_assignment_evaluate_off" <?php checked($assignment_settings['vibe_assignment_evaluation'],'H'); ?>>
            <label for="vibe_assignment_evaluate_off" class="switch-label switch-label-on"><?php _e('NO','wplms-front-end');?></label>
            <span class="switch-selection"></span>
            </div>
        </li>
        <li id="assignment_course" <?php echo (($assignment_settings['vibe_assignment_evaluation'] == 'H')?'class="hide"':'');?>>
            <label><?php _e('ASSIGNMENT COURSE','wplms-front-end'); ?></label>
            <h3><?php _e('Select Assignment Course','wplms-front-end'); ?></h3>
             <select id="vibe_assignment_course" class="chosen">
                        <option value=""><?php _e('None','wplms-front-end'); ?></option>
                        <?php
                            $args= array(
                            'post_type'=> 'course',
                            'numberposts'=> -1
                            );
                            $args = apply_filters('wplms_frontend_cpt_query',$args);
                            $kposts=get_posts($args);
                            if(is_Array($kposts))
                            foreach ( $kposts as $kpost ){
                                echo '<option value="' . $kpost->ID . '" '.selected($assignment_settings['vibe_assignment_course'],$kpost->ID).'>' . $kpost->post_title . '</option>';
                            }
                        ?>
                </select>
        </li>
        <li><h3><?php _e('ASSIGNMENT SUBMISISON TYPE','wplms-front-end'); ?></h3>
            <select id="vibe_assignment_submission_type" class="chosen">
                <option value="upload" <?php selected($assignment_settings['vibe_assignment_submission_type'],'upload'); ?>><?php _e('UPLOAD','wplms-front-end'); ?></option>
                <option value="textarea" <?php selected($assignment_settings['vibe_assignment_submission_type'],'textarea'); ?>><?php _e('TEXTAREA','wplms-front-end'); ?></option>
            </select>
        </li>
        <li id="attachment_type"><h3><?php _e('ATTACHMENT TYPE','wplms-front-end'); ?></h3>
            <select id="vibe_attachment_type" class="chosen" multiple>
                <?php
                $attachment_types =array(
                array('value'=> 'JPG','label' =>'JPG'),
                array('value'=> 'GIF','label' =>'GIF'),
                array('value'=> 'PNG','label' =>'PNG'),
                array('value'=> 'PDF','label' =>'PDF'),
                array('value'=> 'DOC','label' =>'DOC'),
                array('value'=> 'DOCX','label' => 'DOCX'),
                array('value'=> 'PPT','label' =>'PPT'),
                array('value'=> 'PPTX','label' => 'PPTX'),
                array('value'=> 'PPS','label' =>'PPS'),
                array('value'=> 'PPSX','label' => 'PPSX'),
                array('value'=> 'ODT','label' =>'ODT'),
                array('value'=> 'XLS','label' =>'XLS'),
                array('value'=> 'XLSX','label' => 'XLSX'),
                array('value'=> 'MP3','label' =>'MP3'),
                array('value'=> 'M4A','label' =>'M4A'),
                array('value'=> 'OGG','label' =>'OGG'),
                array('value'=> 'WAV','label' =>'WAV'),
                array('value'=> 'WMA','label' =>'WMA'),
                array('value'=> 'MP4','label' =>'MP4'),
                array('value'=> 'M4V','label' =>'M4V'),
                array('value'=> 'MOV','label' =>'MOV'),
                array('value'=> 'WMV','label' =>'WMV'),
                array('value'=> 'AVI','label' =>'AVI'),
                array('value'=> 'MPG','label' =>'MPG'),
                array('value'=> 'OGV','label' =>'OGV'),
                array('value'=> '3GP','label' =>'3GP'),
                array('value'=> '3G2','label' =>'3G2'),
                array('value'=> 'FLV','label' =>'FLV'),
                array('value'=> 'WEBM','label' =>'WEBM'),
                array('value'=> 'APK','label' =>'APK '),
                array('value'=> 'RAR','label' =>'RAR'),
                array('value'=> 'ZIP','label' =>'ZIP')
                );
                
                foreach($attachment_types as $attachment_type){
                    echo '<option value="'.$attachment_type['value'].'" '.((is_array($assignment_settings['vibe_attachment_type']) && in_array($attachment_type['value'],$assignment_settings['vibe_attachment_type']))?'selected':'').'>'.$attachment_type['label'].'</option>';
                }
                ?>
            </select>
        </li>
        <li>
            <label><?php _e('Attachment Size','wplms-front-end'); ?></label>
            <h3><?php _e('Maximum attachment size','wplms-front-end'); ?></h3><input type="number" class="small_box" id="vibe_attachment_size" value="<?php echo $assignment_settings['vibe_attachment_size']; ?>" /> <?php _e(' MBs','wplms-front-end'); ?>
        </li>
        </ul></article>
        <a id="save_assignment_settings" class="course_button button full"><?php _e('SAVE SETTINGS','wplms-front-end'); ?></a>
        <input type="hidden" value="<?php echo get_the_ID(); ?>" id="assignment_id" />
        <?php
         wp_nonce_field('save-assignment-settings'.$user_id,'assignment_security');
         ?>
         </div>
         <?php
    }

    function wplms_front_end_assignment_settings($assignment_settings){
        $id = get_the_ID();
        if(is_array($assignment_settings))
        foreach($assignment_settings as $key=>$settings){
            if($key == 'vibe_attachment_type'){
                $val = get_post_meta($id,$key,false);
                if(isset($val) && is_array($val))
                    $assignment_settings[$key] = vibe_sanitize($val);
            }else{
                $val = get_post_meta($id,$key,true);
                if(isset($val) && $val){
                    $assignment_settings[$key] = $val;
                }
            }
        }
        return $assignment_settings;
    }
    function save_assignment_settings(){
        $user_id = get_current_user_id();

        $assignment_id = $_POST['assignment_id'];
        $assignment_settings['vibe_subtitle'] = $_POST['vibe_subtitle'];
        $assignment_settings['vibe_assignment_marks'] = $_POST['vibe_assignment_marks'];
        $assignment_settings['vibe_assignment_duration'] = $_POST['vibe_assignment_duration'];
        $assignment_settings['vibe_assignment_evaluation'] = $_POST['vibe_assignment_evaluation'];
        $assignment_settings['vibe_assignment_course'] = $_POST['vibe_assignment_course'];
        $assignment_settings['vibe_assignment_submission_type'] = $_POST['vibe_assignment_submission_type'];
        $assignment_settings['vibe_attachment_type'] = json_decode(stripslashes($_POST['vibe_attachment_type']));
        $assignment_settings['vibe_attachment_size'] = $_POST['vibe_attachment_size'];
        

        if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'save-assignment-settings'.$user_id) || !current_user_can('edit_posts')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        if(!is_numeric($assignment_id)){
            _e('Invalid details','wplms-front-end');
            die();
        }

        $the_post = get_post($assignment_id);
        if($the_post->post_author != $user_id && !current_user_can('manage_options')){ // Instructor and Admin check
            _e('Invalid Assignment author','wplms-front-end');
             die();
        }


        foreach($assignment_settings as $key => $setting){
            update_post_meta($assignment_id,$key,$setting);
        }

        _e('SETTINGS SAVED','wplms-front-end');

        die();
    }


    function wplms_unit_upload_zip_controls(){
        global $wp_query;
        if(!isset($_GET['edit']) && !isset($wp_query->query_vars['edit']))
            return;

        if(current_user_can('edit_posts'))
            echo '<a href="#" id="upload_zip_button" class="button primary small" data-admin-url="'.admin_url().'">'.__('ADD PACKAGE','wplms-front-end').'</a>';
    }
    function calculate_duration_time($seconds) {
    switch($seconds){
        case 1: $return = __('Seconds','wplms-front-end');break;
        case 60: $return = __('Minutes','wplms-front-end');break;
        case 3600: $return = __('Hours','wplms-front-end');break;
        case 86400: $return = __('Days','wplms-front-end');break;
        case 604800: $return = __('Weeks','wplms-front-end');break;
        case 2592000: $return = __('Months','wplms-front-end');break;
        case 31104000: $return = __('Years','wplms-front-end');break;
        default:
        $return = apply_filters('vibe_calculation_duration_default',$return,$seconds);
        break;
    }
    return $return;
    } 

    function show_pricing($true){
        if(in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'woocommerce/woocommerce.php')))
            return 1;
        if(in_array( 'paid-memberships-pro/paid-memberships-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'paid-memberships-pro/paid-memberships-pro.php')))
            return 1;
        if(in_array( 'mycred/mycred.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'mycred/mycred.php')))
            return 1;
        return $true;
    }

}	

WPLMS_Front_End::instance();



<?php

if (!defined('ABSPATH')) { exit; }

if (!class_exists('WPLMS_Assignments')){
    class WPLMS_Assignments{

    public static $instance;
    
    var $schedule;

    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Assignments();

        return self::$instance;
    }

        private $adminCheckboxes;
        private $adminPrefix    = 'assignmentAttachment';
        private $key            = 'attachment';
        private $settings;


        public function __construct(){ 
            $this->plupload_assignment_e_d = apply_filters('wplms_assignments_disable_pluplod_filter','on');
           
            $this->settings = $this->getSavedSettings();
            $this->defineConstants();
            add_action('plugins_loaded', array($this, 'loaded'));
            add_action('init', array($this, 'initialise'));
            add_action('admin_init', array($this, 'adminInit'));
            add_action('wplms_student_course_reset',array($this,'reset_assignments'),10,2);
            add_action('wplms_before_single_assignment',array($this,'wplms_assignments_before_single_assignment'));
            //add_action('wplms_assignment_after_content',array($this,'assignment_result'));

            add_filter('wplms_course_submission_tabs',array($this,'assignment_submission_tab'),10,2);
            add_action('wplms_course_submission_assignment_tab_content',array($this,'get_assignment_submissions'),10,1);
            add_action('wp_ajax_fetch_assignment_submissions',array($this,'fetch_assignment_submissions'));

            add_action('wplms_get_wplms-assignment_result',array($this,'get_assignment_result'),10,2);
            add_action('wplms_get_user_results',array($this,'get_assignment_results'),20,1);
            add_action('pre_get_posts',array($this,'get_assignments_archive'));
            add_action('wplms_course_student_stats',array($this,'wplms_course_student_stats'),10,3);
         
            add_action('wp_ajax_evaluate_assignment',array($this,'evaluate_assignment'));

            //FORCE APPROVE ASSIGNMENT SUBMISSIONS
            add_filter( 'pre_comment_approved', array($this,'approve_submissions'));
            //pl upload handlers 
            add_action('wp_ajax_wplms_assignment_plupload',array($this,'wplms_assignment_plupload'));
            add_action('wp_ajax_insert_assignment_attachment',array($this,'insert_assignment_attachment'));
            add_action('wp_ajax_remove_attachment_plupload',array($this,'remove_attachment_plupload'));
            add_action('wp_ajax_check_file_exists_plupload',array($this,'check_file_exists_plupload'));
            add_action('wp_ajax_delete_file_exists_plupload',array($this,'delete_file_exists_plupload'));
            add_action('wp_ajax_wplms_assignment_plupload_remarks',array($this,'wplms_assignment_plupload_remarks'));

        }

        function approve_submissions( $approved ){ 
            if(isset($_POST) && isset($_POST['comment_post_ID']) && is_numeric($_POST['comment_post_ID'])){
              $post_type = get_post_type($_POST['comment_post_ID']);
              if( $post_type == 'wplms-assignment'){ 
                return is_user_logged_in() ? 1 : $approved; 
              } 
            }
            return $approved; 
        }

        function evaluate_assignment(){

            $assignment_id = intval($_POST['id']);
            $user_id=intval($_POST['user']);

           if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'vibe_assignment') ){
                echo '<p>'.__('Security check failed !','wplms-assignments').'</p>';
                die();
            }

            if ( !isset($user_id) || !is_numeric($user_id)){
                echo '<p>'.__(' Incorrect User selected.','wplms-assignments').'</p>';
                die();
            }

            if ( !is_numeric($assignment_id) || get_post_type($assignment_id) != 'wplms-assignment'){
                echo '<p>'.__(' Incorrect Assignment','wplms-assignments').'</p>';
                die();
            }

            $assignment_post=get_post($assignment_id);
            
            echo '<div class="assignment_content">';
            echo apply_filters('the_content',$assignment_post->post_content);
            echo '<h3 class="heading">'.__('SUBMISSION','wplms-assignments').'</h3>';

            $answers=get_comments(array(
              'post_id' => $assignment_id,
              'status' => 'approve',
              'number' => 1,
              'user_id' => $user_id
              ));

            //$type=get_post_meta($assignment_id,'vibe_assignment_submission_type',true);

            if(isset($answers) && is_array($answers) && count($answers)){
                $answer = end($answers);
                echo nl2br($answer->comment_content);
                $attachment_id=get_comment_meta($answer->comment_ID, 'attachmentId',true);
                if(!empty($attachment_id)){
                  if(is_array($attachment_id)){
                    foreach($attachment_id as $attachid){
                      echo '<div class="download_attachment"><a href="'.wp_get_attachment_url($attachid).'" target="_blank"><i class="icon-download-3"></i> '.__('Download Attachment','wplms-assignments').'</a></div>';
                    }
                  }else{
                    echo '<div class="download_attachment"><a href="'.wp_get_attachment_url($attachment_id).'" target="_blank"><i class="icon-download-3"></i> '.__('Download Attachment','wplms-assignments').'</a></div>';
                  }
                }
            }
            echo '</div>';


            $max_marks = get_post_meta($assignment_id,'vibe_assignment_marks',true);
            echo '<h4>'.sprintf(_x('%s submission by %s','Assignment submission by User','wplms-assignments'),get_the_title($assignment_id),bp_core_get_user_displayname($user_id)).'</h4>';
            echo '<div class="marks_form">
            <input type="number" value="" class="form_field" id="assignment_marks" placeholder="'.__('Enter marks out of ','wplms-assignments').$max_marks.'" />
            <label>'.__('REMARKS (if any)','wplms-assignments').'</label>';
            echo '<textarea id="remarks_message"></textarea>';
 
            ?>
            <!--plupload form here -->
            <label><?php echo __('Attach File','wplms-assignments')?></label>
            <?php
            $required = ATT_REQ ? ' <span class="required">*</span>' : '';
            echo '<p class="comment-form-url comment-form-attachment">'.
                    '<label for="attachment"><small class="attachmentRules">&nbsp;&nbsp;('.__('Allowed file types','wplms-assignments').': <strong>'. $this->displayAllowedFileTypes($assignment_id) .'</strong>, '.__('maximum file size','wplms-assignments').': <strong>'. $this->getmaxium_upload_file_size($assignment_id) .__('MB(s)','wplms-assignments').'.</strong></small></label>'.
                '</p>';
            ?>
            <div  class="plupload_error_notices notice notice-error is-dismissible"></div>
              <div id="plupload-upload-ui" class="hide-if-no-js">
                  <div id="drag-drop-area">
                      <div class="drag-drop-inside">
                          <p class="drag-drop-info"><?php _e('Drop files here','wplms-assignments'); ?></p>
                          <p><?php _ex('or', 'Uploader: Drop files here - or - Select Files','wplms-assignments'); ?></p>
                          <p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="<?php _e('Select Files','wplms-assignments'); ?>" class="button" /></p>
                      </div>
                  </div>
              </div>

              <div class="pl_assignment_progress">
              </div>
              <div class="warning_plupload">
                  <h3><?php echo __("Please do not close the window until process is completed","wplms-assignments") ?></h3>
              </div>
              
              <?php
                  if ( function_exists( 'ini_get' ) )
                      $post_size = ini_get('post_max_size') ;
                  $post_size = preg_replace('/[^0-9\.]/', '', $post_size);
                  $post_size = intval($post_size);
                  if($post_size != 1){
                      $post_size = $post_size-1;
                  }
                  
                 
               $plupload_init = array(
                  'runtimes'            => 'html5,silverlight,flash,html4',
                  'chunk_size'          =>  (($post_size*1024) - 100).'kb',
                  'max_retries'         => 3,
                  'browse_button'       => 'plupload-browse-button',
                  'container'           => 'plupload-upload-ui',
                  'drop_element'        => 'drag-drop-area',
                  'multiple_queues'     => false,
                  'multi_selection'     => false,
                  'max_file_size'       => ($this->getmaxium_upload_file_size($assignment_id) * 1024).'kb',
                  'filters'             => array( array( 'extensions' => implode( ',', $this->getAllowedFileExtensions($assignment_id)) ) ),
                  'url'                 => admin_url('admin-ajax.php'),
                  'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
                  'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
                  
                  'multipart'           => true,
                  'urlstream_upload'    => true,

                  // additional post data to send to our ajax hook
                  'multipart_params'    => array(
                    '_ajax_nonce' => wp_create_nonce('wplms_assignment_plupload_remarks'),
                    'action'      => 'wplms_assignment_plupload_remarks', 
                    'user'        => $user_id,
                    'assignment_id'=> $assignment_id,
                  ),
                );

              $plupload_init = apply_filters('plupload_init', $plupload_init);
              
              ?>
              
              <script type="text/javascript">
                jQuery(document).ready(function($){
                  $('.tab-pane#assignment').one('evaluate_assignment_loaded',function(){

                      var temp = <?php echo json_encode($plupload_init,JSON_UNESCAPED_SLASHES); ?>;
                      if(temp.multipart_params.s3_bucket == ''){
                          temp.multipart_params.s3_bucket= jQuery('body').find('form.wplms-s3-upload #wplms_s3_bucket').val();
                      }
                      // create the uploader and pass the config from above
                      var uploader = new plupload.Uploader(temp);
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
                      
                      var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
                      plupload.each(files, function(file){
                        if (file.name.match(/\s/g)){
                            alert('<?php echo __('There is a space in file name .Please remove space from your filename and try to re-upload','wplms-assignments');?>');
                            return false;
                        }
                          if (file.size > max && up.runtime != 'html5'){
                              console.log('call "upload_to_amazon" not sent');
                          }else{
                               $('.pl_assignment_progress').addClass('visible');
                              var clone = $('.pl_assignment_progress').append('<div class="'+file.id+'">'+file.name+'<i></i><strong><span></span></strong></div>');
                              $('.pl_assignment_progress').append(clone);
                              $('.warning_plupload').show(300);
                          }
                         /* $.ajax({
                              type: "POST",
                              url: ajaxurl,
                              data: { action: 'check_file_exists_plupload', 
                                        name: file.name,
                                        _ajax_nonce: "<?php echo wp_create_nonce('wplms_check_file_exists_plupload'); ?>"
                                  },
                              cache: false,
                              success: function (html) {
                                  if(html == '1'){
                                      pakode = 1;
                                      up.stop();
                                      up.refresh();
                                      $('.pl_assignment_progress div.'+file.id).html("<div class='message animate tada load has-error text-danger file_exists'><b>("+file.name+")</b><?php echo __('  already exists. Please rename the file and try again.','wplms-assignments') ?></div>");
                                      $('.warning_plupload').hide(300);
                                      setTimeout(function(){
                                          $('.pl_assignment_progress div.'+file.id).fadeOut(600);
                                          $('.pl_assignment_progress div.'+file.id).remove();
                                          
                                      }, 10000);
                                  }else{

                                  }
                              }
                          });*/
                          //stop attachment if file exists
                         /* $('.stop_s3_plupload_upload').on('click',function() {
                              if(confirm("<?php echo __('Are you sure you want to quit uploading?','wplms-assignments');?>")){
                                  if(!($(this).hasClass('disabled'))){
                                      setTimeout(function(){
                                      $('.pl_assignment_progress div.'+file.id).fadeOut(600);
                                      }, 1200);
                                      up.stop();
                                      up.destroy();
                                  }
                              }else{
                                  return false;
                              }
                          });*/
                      });

                      up.refresh();
                      up.start();
                  });
                  uploader.bind('Error', function(up, args) {
                      $('.plupload_error_notices').show();
                      $('.plupload_error_notices').html('<div class="message text-danger danger tada animate load">'+args.message+' for '+args.file.name+'</div>');
                      setTimeout(function(){
                          $('.plupload_error_notices').hide();
                      }, 5000);
                      up.refresh();
                      up.start();
                  });

                  uploader.bind('UploadProgress', function(up, file) {
                      
                      if(file.percent < 100 && file.percent >= 1){
                          $('.pl_assignment_progress div.'+file.id+' strong span').css('width', (file.percent)+'%');
                          $('.pl_assignment_progress div.'+file.id+' i').html( (file.percent)+'%');
                      }
                      
                      up.refresh();
                      up.start(); 
                  });
                    // a file was uploaded 
                  uploader.bind('FileUploaded', function(up, file, response) {
                      
                      //$('.stop_s3_plupload_upload').addClass('disabled');
                       $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: { action: 'insert_assignment_attachment', 
                                security: '<?php echo wp_create_nonce("wplms_assignment_plupload_final"); ?>',
                                assignment_id:'<?php echo $assignment_id; ?>',
                                name:file.name,
                                context : 'remarks',
                                user : <?php echo $user_id; ?>,
                                type:file.type,
                                size:file.origSize,
                                
                              },
                        cache: false,
                        success: function (html) {
                          if(html){
                              if(html == '0'){
                                  $('.pl_assignment_progress div.'+file.id+' strong span').css('width', '0%');
                                  $('.pl_assignment_progress div.'+file.id+' strong').html("<i class='error'><?php echo __("File type not allowed","wplms-assignments"); ?><i>");
                                  setTimeout(function(){
                                      $('.pl_assignment_progress div.'+file.id).fadeOut(600);
                                      $('.pl_assignment_progress div.'+file.id).remove();
                                  }, 2500);
                                  $('.warning_plupload').hide(300);
                                  return false;
                              }else{
                               
                                  $('.pl_assignment_progress div.'+file.id+' strong span').css('width', '100%');
                                  $('.pl_assignment_progress div.'+file.id+' i').html('100%');
                                  
                                      setTimeout(function(){
                                        $('.pl_assignment_progress div.'+file.id+' strong').fadeOut(500);
                                      }, 1200);
                                      
                                      $('.pl_assignment_progress div.'+file.id).parent().parent().find('textarea#remarks_message').val($('.pl_assignment_progress div.'+file.id).parent().parent().find('textarea#remarks_message').val() + '   ' + html);
                                      $('.pl_assignment_progress div.'+file.id).remove();
                                      setTimeout(function(){
                                          if($('.pl_assignment_progress strong').length < 1){
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
            echo '<a id="give_assignment_marks" class="button small" data-ans-id="'.$answer->comment_ID.'">'.__('GIVE MARKS','wplms-assignments').'</a></div>';
            die();
        }

        function wplms_assignment_plupload_remarks(){
            check_ajax_referer('wplms_assignment_plupload_remarks');
            if(!is_user_logged_in())
                die('user not logged in');

            $user_id = (!empty($_POST['user'])? $_POST['user'] : get_current_user_id());
            
            if (empty($_FILES) || $_FILES['file']['error']) {
              die('{"OK": 0, "info": "Failed to move uploaded file."}');
            }

            $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
            $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
            $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
            
            $upload_dir_base = wp_upload_dir();
            $assignment_id = $_POST['assignment_id'];
            $folderPath = $upload_dir_base['basedir']."/wplms_assignments_folder/".$user_id.'/'.$assignment_id.'/remarks';
            if(function_exists('is_dir') && !is_dir($folderPath)){
                if(function_exists('mkdir')) 
                    mkdir($folderPath, 0755, true) || chmod($folderPath, 0755);
            }


            $filePath = $folderPath."/$fileName";
             /*if(function_exists('file_exists') && file_exists($filePath)){
                echo __(' Chunks upload error ','wplms-assignments'). $fileName.__(' already exists.Please rename your file and try again ','wplms-assignments');
                die();
             }*/
            // Open temp file
            if($chunk == 0) $perm = "wb" ;
            else $perm = "ab";

            $out = @fopen("{$filePath}.part",$perm );

            if ($out) {
              // Read binary input stream and append it to temp file
              $in = @fopen($_FILES['file']['tmp_name'], "rb");
             
              if ($in) {
                while ($buff = fread($in, 4096))
                  fwrite($out, $buff);
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

        
        /* Assignment Stats in Course - Admin - User - Stats */

        function wplms_course_student_stats($curriculum,$course_id,$user_id=null){
            $assignments = $this->get_course_assignments($course_id);
            if(is_array($assignments) && count($assignments)){
                $curriculum .= '<li><h5>'._x('Assignments','assignments connected to the course, Course - admin - user - stats','wplms-assignments').'</h5></li>';
               foreach($assignments as $assignment){
           
                    $marks = get_post_meta($assignment->post_id,$user_id,true);
                    if(is_numeric($marks)){
                      $curriculum .= '<li><span data-id="'.$assignment->post_id.'" class="done"></span> '.get_the_title($assignment->post_id).' <strong>'.(($marks)?_x('Marks Obtained : ',' marks obtained in assignment result','wplms-assignments').$marks:__('Under Evaluation','wplms-assignments')).'</strong></li>';
                    }else{
                      $curriculum .= '<li><span data-id="'.$assignment->post_id.'"></span> '.get_the_title($assignment->post_id).'</li>';
                    }
                    
                }
            }
            return $curriculum;
        }


        /* End Stats - HK */

        function get_assignments_archive($query){

            if(!$query->is_archive() || !$query->is_main_query() || is_admin()){
                return $query;
            }

            if($query->get('post_type') != 'wplms-assignment'){
                return $query;
            }

            if(!current_user_can('manage_options')){
                $user_id = get_current_user_id();
                $query->set( 'meta_query', array(
                                   array(
                                         'key' => $user_id,
                                         'compare' => 'EXISTS',
                                        )
                                   )
                              );
            }
        }

        function assignment_submission_tab($tabs,$course_id){
            $tabs['assignment'] = sprintf(_x('Assignment Submissions <span>%d</span>','Assignment Submissions in course/admin/submissions','wplms-assignments'),self::get_assignment_submission_count($course_id));
            return $tabs;
        }

        function get_assignment_submission_count($course_id){
            global $wpdb;
            $assignments = $this->get_course_assignments($course_id);
            
            if(empty($assignments)){
                return 0;
            }
            $assignment_ids = array();
            foreach($assignments as $assignment_id){
                $assignment_ids[] = $assignment_id->post_id;
            }
    
            $assignment_ids = implode(',',$assignment_ids);
            $count = $wpdb->get_var("SELECT count(DISTINCT meta_key) FROM {$wpdb->postmeta} WHERE meta_value LIKE '0' AND meta_key REGEXP '^[0-9]+$' AND post_id IN ($assignment_ids)");
            return (empty($count)?0:$count);
        }

        function get_assignment_submissions($course_id){
            global $wpdb;
            $assignments = $this->get_course_assignments($course_id);

            if(!empty($assignments)){
                foreach($assignments as $assignment_id){
                    $assignment_ids[] = $assignment_id->post_id;
                }
                $count_array = array();
                $assignment_ids = implode(',',$assignment_ids);
                $submissions = $wpdb->get_results("SELECT count(DISTINCT meta_key) as count, post_id FROM {$wpdb->postmeta} WHERE meta_value LIKE '0' AND meta_key REGEXP '^[0-9]+$' AND post_id IN ($assignment_ids) GROUP BY post_id");
                if(!empty($submissions)){
                    foreach($submissions as $submission){
                        $count_array[$submission->post_id]=$submission->count;
                    }
                }
                ?>
                <div class="submissions_form">
                    <select id="fetch_assignment">
                    <?php
                    foreach($assignments as $assignments_id){
                        ?>
                        <option value="<?php echo $assignments_id->post_id; ?>"><?php echo get_the_title($assignments_id->post_id); ?> (<?php echo (empty($count_array[$assignments_id->post_id])?0:$count_array[$assignments_id->post_id]); ?>)</option>
                        <?php   
                    }
                    ?>
                    </select>
                    <select id="fetch_assignment_status">
                        <option value="0"><?php echo _x('Pending evaluation','assignment status','wplms-assignments') ?></option>
                        <option value="1"><?php echo _x('Evaluation complete','assignment status','wplms-assignments') ?></option>
                    </select>
                    <?php wp_nonce_field('assignment_submissions','assignment_submissions'); ?>
                    <a id="fetch_assignment_submissions" class="button"><?php echo _x('Get','get assignment submissions button','wplms-assignments'); ?></a>
                </div>
                <script>
                    jQuery(document).ready(function($){
                        $('#fetch_assignment_submissions').on('click',function(){
                            var parent = $(this).parent();
                            $('.quiz_students').remove();
                            $('.message').remove();
                            var $this = $(this);
                            $this.append('<i class="fa fa-spinner"></i>');
                            $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: { action: 'fetch_assignment_submissions', 
                                        security: $('#assignment_submissions').val(),
                                        assignment_id:$('#fetch_assignment').val(),
                                        status:$('#fetch_assignment_status').val(),
                                        },
                                cache: false,
                                success: function (html) {
                                    $('ul.assignment_students').remove();
                                    parent.after(html);
                                    $this.find('.fa').remove();
                                    $(' #assignment').trigger('loaded');
                                }
                            });
                        });
                    });
                </script>
                <?php
            }else{
                ?>
                <div class="message">
                    <p><?php echo _x('No assignments found !','No assignments in course, error on course submissions','wplms-assignments'); ?></p>
                </div>
                <?php
            }
    
        }

        function fetch_assignment_submissions(){

            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'assignment_submissions') || !is_numeric($_POST['assignment_id'])){
             _e('Security check Failed. Contact Administrator.','wplms-assignments');
             die();
            }
            $assignment_id = $_POST['assignment_id'];
            global $wpdb;
            if($_POST['status'] == 1){
                $assignment_submissions = $wpdb->get_results($wpdb->prepare ("SELECT DISTINCT c.user_id as user_id,p.post_id as assignment_id FROM {$wpdb->postmeta} as p LEFT JOIN {$wpdb->comments} as c ON p.post_id = c.comment_post_ID WHERE CAST(c.user_id as UNSIGNED) = CAST(p.meta_key as UNSIGNED) AND c.comment_approved='1' AND CAST(p.meta_value as UNSIGNED) != 0 && p.post_id = %d LIMIT 0,999",$assignment_id), ARRAY_A);    
            }else{
                $assignment_submissions = $wpdb->get_results($wpdb->prepare ("SELECT DISTINCT c.user_id as user_id,c.comment_post_ID as assignment_id FROM {$wpdb->comments} as c WHERE c.comment_post_ID = %d AND c.comment_approved='1' AND NOT EXISTS (SELECT * FROM {$wpdb->postmeta} as p WHERE p.post_id = %d  AND p.meta_value > '0' AND p.meta_key = c.user_id ) LIMIT 0,999",$assignment_id,$assignment_id), ARRAY_A);  
            }
            

            if(count($assignment_submissions)){
                echo '<ul class="assignment_students">';
                foreach($assignment_submissions as $assignment_submission ){
                    if(is_numeric($assignment_submission['user_id'])){
                    $member_id=$assignment_submission['user_id'];
                    $assignment_id=$assignment_submission['assignment_id'];
                    $bp_name = bp_core_get_userlink( $member_id );

                    if(!isset($student_field))
                        $student_field='Location';

                    $profile_data = 'field='.$student_field.'&user_id='.$member_id;
                    
                    $bp_location ='';
                    if(bp_is_active('xprofile'))
                    $bp_location = bp_get_profile_field_data($profile_data);

                    echo '<li id="as'.$member_id.'">';
                    echo get_avatar($member_id);
                    echo '<h6>'. $bp_name . '</h6>';
                    echo '<span>';
                    if ($bp_location) {
                        echo $bp_location ;
                    }
                    do_action('wplms_assignment_submission_meta',$member_id,$assignment_id);
                    echo '</span>';
                    // PENDING AJAX SUBMISSIONS
                    echo '<ul> 
                            <li><a class="tip reset_assignment_user" data-assignment="'.$assignment_id.'" data-user="'.$member_id.'" title="'.__('Reset Assignment for User','wplms-assignments').'"><i class="icon-reload"></i></a></li>
                            <li><a class="tip evaluate_assignment_user" data-assignment="'.$assignment_id.'" data-user="'.$member_id.'" title="'.__('Evaluate Assignment : ','wplms-assignments').get_the_title($assignment_id).'"><i class="icon-check-clipboard-1"></i></a></li>
                          </ul>';
                    echo '</li>';
                    }
                }
                echo '</ul>';
                
            }else{
                echo '<div class="error message"><p>'.__('No submissions found !','wplms-assignments').'</p></div>';
            }

            wp_nonce_field('vibe_assignment','asecurity');

            die();
        }

        function get_course_assignments($course_id){

              global $wpdb;
              $this->assignments = $wpdb->get_results($wpdb->prepare("SELECT m.post_id as post_id,p.post_title as title FROM {$wpdb->postmeta} as m LEFT JOIN {$wpdb->posts} as p on p.ID = m.post_id WHERE m.meta_key = %s and m.meta_value = %d  AND p.post_type = 'wplms-assignment' ORDER BY p.post_title",'vibe_assignment_course',$course_id));

            return $this->assignments;
        }

        function reset_assignments($course_id,$user_id){
            global $wpdb;
            //get all assignments connected to the course_id
            $results = $wpdb->get_results($wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s and meta_value LIKE %s","vibe_assignment_course","%$course_id%"),ARRAY_A);
            if(is_array($results) && count($results)){
                foreach($results as $result){
                    delete_user_meta($user_id,$result['post_id']);
                    delete_post_meta($result['post_id'],$user_id);
                    $wpdb->query($wpdb->prepare("UPDATE $wpdb->comments SET comment_approved='trash' WHERE comment_post_ID=%d AND user_id=%d",$result['post_id'],$user_id));
                }    
            }
        }

        function wplms_assignments_before_single_assignment(){
           if(!is_user_logged_in())
             return;

             global $post;
             $user_id = get_current_user_id();
             if(wplms_assignment_answer_posted()){
               $submitted = get_post_meta($post->ID,$user_id,true);
               if(empty($submitted)){
                   if(update_post_meta($post->ID,$user_id,0)){
                       do_action('wplms_submit_assignment',$post->ID,$user_id);
                      return;
                   }
               }
             }
        }

        function get_assignment_results($user_id){
            
            //ASSIGNMENTS   
            $paged = 1;
            $per_page = 2;
            if(function_exists('vibe_get_option')){
                $per_page = vibe_get_option('loop_number');  
            }
            $the_assignment=new WP_QUERY(array(
                'post_type'=>'wplms-assignment',
                'paged' => $paged,
                'posts_per_page'=>$per_page,
                'meta_query'=>array(
                    array(
                        'key' => $user_id,
                        'compare' => 'EXISTS'
                        ),
                    ),
                ));
            if($the_assignment->have_posts()){
                
            ?>
            <h3 class="heading"><?php _e('Assignment Results','wplms-assignments'); ?></h3>
            <div class="user_results">
                <ul class="quiz_results">   
                <?php
                while($the_assignment->have_posts()) : $the_assignment->the_post();
                global $post;
                    $this->results_item($post,$user_id);
                endwhile;
                ?>
                </ul>
                <?php
                if($the_assignment->max_num_pages>1){
                    ?>
                    <div class="pagination no-ajax">
                        <div class="pag-count">
                            <?php echo sprintf(__('Viewing %d out of %d','vibe'),$paged,$the_assignment->max_num_pages) ?>
                        </div>
                        <div class="pagination-links">
                            <?php
                            for($i=1;$i<=$the_assignment->max_num_pages;$i++){
                                if(($paged==$i)){
                                    ?>
                                    <span class="page-numbers current"><?php echo $i;?></span>
                                    <?php
                                }else{
                                    ?>
                                    <a class="get_results_pagination" data-type="wplms-assignment"><?php echo $i;?></a>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                    </div>
                <?php }
                ?>
            </div>
            <?php 
                wp_nonce_field('security','security');
                wp_reset_query();

            }// End Assignment -> Have posts
        }

        function results_item($post,$user_id){

            $value = get_post_meta($post->ID,$user_id,true);
            $max = get_post_meta($post->ID,'vibe_assignment_marks',true);
            ?>
            <li><i class="icon-task"></i>
                <a href="?action=<?php echo $post->ID; ?>"><?php echo $post->post_title; ?></a>
                <span><?php 
                if($value > 0){
                    echo '<i class="icon-check"></i> '.__('Results Available','vibe');
                }else{
                    echo '<i class="icon-alarm"></i> '.__('Results Awaited','vibe');
                }
                ?></span>
                <span><?php
                global $wpdb,$bp;
                $assignment_activity_date = $wpdb->get_var($wpdb->prepare( "
                    SELECT date_recorded FROM {$bp->activity->table_name} AS activity
                    WHERE activity.component = 'course'
                    AND activity.type = 'assignment_submitted'
                    AND user_id = %d
                    AND ( item_id = %d OR secondary_item_id = %d )
                    ORDER BY date_recorded DESC
                    LIMIT 0,1
                    " ,$user_id,$post->ID,$post->ID));
                if(!empty($assignment_activity_date)){
                    $time = strtotime($assignment_activity_date);

                    echo '<i class="icon-clock"></i> '.sprintf(_x('Submitted %s','assignment submission time','vibe'),human_time_diff($time,time()));
                    ?></span>
                    <?php
                }
                if($value > 0)
                    echo '<span><strong>'.$value.' / '.$max.'</strong></span>';
                ?>
            </li>
            <?php
        }
        function get_assignment_result($assignment_id,$user_id){

            $assignment_post=get_post($assignment_id);
            $assignment_marks = get_post_meta($assignment_id,$user_id,true);
            $total_assignment_marks = get_post_meta($assignment_id,'vibe_assignment_marks',true);

            echo '<div class="assignment_content">';
            echo '<h3 class="heading">'.get_the_title($id).'</h3>';
            echo apply_filters('the_content',$assignment_post->post_content);

            echo '<h3 class="heading">'.__('My Submission','vibe').'</h3>';
            $answers=get_comments(array(
              'post_id' => $assignment_id,
              'status' => 'approve',
              'number' => 1,
              'user_id' => $user_id
              ));
            if(isset($answers) && is_array($answers) && count($answers)){
                $answer = end($answers);
                echo $answer->comment_content;
                $attachment_id=get_comment_meta($answer->comment_ID, 'attachmentId',true);
                if(!empty($attachment_id) && $attachment_id){
                  if(is_array($attachment_id)){
                    foreach($attachment_id as $attachid){
                      echo '<div class="download_attachment"><a href="'.wp_get_attachment_url($attachid).'" target="_blank"><i class="icon-download-3"></i> '.__('Download Attachment','wplms-assignments').'</a></div>';
                    }
                  }else{
                    echo '<div class="download_attachment"><a href="'.wp_get_attachment_url($attachment_id).'" target="_blank"><i class="icon-download-3"></i> '.__('Download Attachment','wplms-assignments').'</a></div>';
                  }
                }
            }
            global $wpdb,$bp;
            $table_name=$bp->activity->table_name;
            $meta_table_name=$bp->activity->table_name_meta;
            $remarkmessage = $wpdb->get_results($wpdb->prepare( "
                                        SELECT meta_value FROM {$meta_table_name} as meta
                                        WHERE meta_key = 'remarks'
                                        AND meta.activity_id IN (SELECT activity.id FROM {$table_name} AS activity
                                        WHERE   activity.component  = 'course'
                                        AND     activity.type   = 'evaluate_assignment'
                                        AND     item_id = %d
                                        AND     secondary_item_id = %d
                                        ORDER BY date_recorded DESC)
                                    " ,$assignment_id,$user_id));
            $remarks=$remarkmessage[0]->meta_value;
            if(isset($remarks)){
                echo '<a href="'.trailingslashit( bp_loggedin_user_domain() . $bp->messages->slug . '/view/' . $remarks ).'" class="button right small">'.__('See Instructor Remarks','vibe').'</a><span class="clearfix"></span>';
            }
            
            echo '<div id="total_marks">'.__('Marks Obtained','vibe').' <strong><span>'.$assignment_marks.'</span> / '.$total_assignment_marks.'</strong> </div>';
            echo '</div>';
        }
        /******************* Inits, innit :D *******************/

        /**
         * Loaded, check request
         */

        public function loaded()
        {
            // check to delete att
            if(isset($_GET['deleteAtt']) && ($_GET['deleteAtt'] == '1')){
                if((isset($_GET['c'])) && is_numeric($_GET['c'])){
                    WPLMS_Assignments::deleteAttachment($_GET['c']);
                    delete_comment_meta($_GET['c'], 'attachmentId');
                    add_action('admin_notices',array($this, 'mynotice'));
                }
            }
        }

       function mynotice(){
            echo "<div class='updated'><p>".__('Assignment Attachment deleted.','wplms-assignments')."</p></div>";
        }
        /**
         * Classic init
         */

        public function initialise(){
            
            if(!$this->checkRequirements()){ return; }
            $this->checkformagain = 0;
            add_filter('preprocess_comment',        array($this, 'checkAttachment'));
            add_action('comment_form_top',          array($this, 'displayBeforeForm'));
            add_action('comment_form_before_fields',array($this, 'displayFormAttBefore'));
            add_action('comment_form_logged_in_after',array($this, 'displayFormAtt'));
            add_filter('comment_text',              array($this, 'displayAttachment'));
            add_action('comment_post',              array($this, 'saveAttachment'));
            add_filter('upload_mimes',              array($this, 'getAllowedUploadMimes'));
            add_action('delete_comment',            array($this, 'deleteAttachment'));
            add_filter('comment_notification_text', array($this, 'notificationText'), 10, 2);
        }


        /**
         * Admin init
         */

        public function adminInit()
        {
            $this->setUserNag();
            add_filter('comment_row_actions', array($this, 'addCommentActionLinks'), 10, 2);
        }


        /*************** Plugins admin settings ****************/

        /**
         * Get's admin settings page variables
         *
         * @return mixed
         */

        public function getSettings() {
            $this->settings = $this->getAllowedFileExtensions();
        }


        private function getSavedSettings(){ 
            $this->settings = $this->getAllowedFileExtensions();
        }


        /**
         * Define plugin constatns
         */

        private function defineConstants(){
            
            if(!defined('ATT_REQ'))
                define('ATT_REQ',   TRUE );

            define('ATT_BIND',  TRUE );
            define('ATT_DEL',   TRUE );
            define('ATT_LINK',  TRUE );
            define('ATT_THUMB',  TRUE );
            define('ATT_PLAY',  TRUE );
            define('ATT_POS',   'before' );
            define('ATT_APOS',  'before');
            define('ATT_TITLE', __('Upload Assignment','wplms-assignments'));
            if ( ! defined( 'ATT_MAX' ) )
                define('ATT_MAX',  $this->getmaxium_upload_file_size());    
        }


        /**
         * For image thumb dropdown.
         *
         * @return mixed
         */

        private function getRegisteredImageSizes()
        {
            foreach(get_intermediate_image_sizes() as $size){
                $arr[$size] = ucfirst($size);
            };
            return $arr;
        }

        function getmaxium_upload_file_size($post_id = null){
            if(empty($post_id)){
             global $post;
             if(isset($post) && is_object($post) && isset($post->ID))
                $post_id = $post->ID;
            }
            $upload_size = 1024;
            $max_upload = (int)(ini_get('upload_max_filesize'));
            $max_post = (int)(ini_get('post_max_size'));
            $memory_limit = (int)(ini_get('memory_limit'));
            $upload_mb = min($max_upload, $max_post, $memory_limit);
            $attachment_size=get_post_meta($post_id,'vibe_attachment_size',true); 
            
            if(isset($attachment_size) && is_numeric($attachment_size)){
                if($attachment_size > $upload_mb && empty($this->plupload_assignment_e_d )){
                    $upload_size=$upload_mb;
                }else{
                    $upload_size=$attachment_size;
                }
                
            }

            return $upload_size;
        }
        /**
         * If there's a place to set up those mime types,
         * it's here.
         *
         * @return array
         */

        private function getMimeTypes()
        {
            return apply_filters('wplms_assignments_upload_mimes_array',array(
                'JPG' => array(
                                'image/jpeg',
                                'image/jpg',
                                'image/jp_',
                                'application/jpg',
                                'application/x-jpg',
                                'image/pjpeg',
                                'image/pipeg',
                                'image/vnd.swiftview-jpeg',
                                'image/x-xbitmap'),
                'GIF' => array(
                                'image/gif',
                                'image/x-xbitmap',
                                'image/gi_'),
                'PNG' => array(
                                'image/png',
                                'application/png',
                                'application/x-png'),
                'DOCX'=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'RAR'=> 'application/x-rar',
                'ZIP' => array(
                                'application/zip',
                                'application/x-zip',
                                'application/x-zip-compressed',
                                'application/x-compress',
                                'application/x-compressed',
                                'multipart/x-zip'),
                'DOC' => array(
                                'application/msword',
                                'application/doc',
                                'application/text',
                                'application/vnd.msword',
                                'application/vnd.ms-word',
                                'application/winword',
                                'application/word',
                                'application/x-msw6',
                                'application/x-msword'),
                'PDF' => array(
                                'application/pdf',
                                'application/x-pdf',
                                'application/acrobat',
                                'applications/vnd.pdf',
                                'text/pdf',
                                'text/x-pdf'),
                'PPT' => array(
                                'application/vnd.ms-powerpoint',
                                'application/mspowerpoint',
                                'application/ms-powerpoint',
                                'application/mspowerpnt',
                                'application/vnd-mspowerpoint',
                                'application/powerpoint',
                                'application/x-powerpoint',
                                'application/x-m'),
                'PPTX'=> 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'PPS' => 'application/vnd.ms-powerpoint',
                'PPSX'=> 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
                'PSD' => array('application/octet-stream',
                                'image/vnd.adobe.photoshop'
                                ),
                'ODT' => array(
                                'application/vnd.oasis.opendocument.text',
                                'application/x-vnd.oasis.opendocument.text'),
                'XLS' => array(
                                'application/vnd.ms-excel',
                                'application/msexcel',
                                'application/x-msexcel',
                                'application/x-ms-excel',
                                'application/vnd.ms-excel',
                                'application/x-excel',
                                'application/x-dos_ms_excel',
                                'application/xls'),
                'XLSX'=> array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                          'application/vnd.ms-excel'),
                'MP3' => array(
                                'audio/mpeg',
                                'audio/x-mpeg',
                                'audio/mp3',
                                'audio/x-mp3',
                                'audio/mpeg3',
                                'audio/x-mpeg3',
                                'audio/mpg',
                                'audio/x-mpg',
                                'audio/x-mpegaudio'),
                'M4A' => array(
                                'audio/mp4a-latm',
                                'audio/m4a',
                                'audio/mp4'),
                'OGG' => array(
                                'audio/ogg',
                                'application/ogg'),
                'WAV' => array(
                                'audio/wav',
                                'audio/x-wav',
                                'audio/wave',
                                'audio/x-pn-wav'),
                'WMA' => 'audio/x-ms-wma',
                'MP4' => array(
                                'video/mp4v-es',
                                'audio/mp4',
                                'video/mp4'),
                'M4V' => array(
                                'video/mp4',
                                'video/x-m4v'),
                'MOV' => array(
                                'video/quicktime',
                                'video/x-quicktime',
                                'image/mov',
                                'audio/aiff',
                                'audio/x-midi',
                                'audio/x-wav',
                                'video/avi'),
                'WMV' => 'video/x-ms-wmv',
                'AVI' => array(
                                'video/avi',
                                'video/msvideo',
                                'video/x-msvideo',
                                'image/avi',
                                'video/xmpg2',
                                'application/x-troff-msvideo',
                                'audio/aiff',
                                'audio/avi'),
                'MPG' => array(
                                'video/avi',
                                'video/mpeg',
                                'video/mpg',
                                'video/x-mpg',
                                'video/mpeg2',
                                'application/x-pn-mpg',
                                'video/x-mpeg',
                                'video/x-mpeg2a',
                                'audio/mpeg',
                                'audio/x-mpeg',
                                'image/mpg'),
                'OGV' => 'video/ogg',
                '3GP' => array(
                                'audio/3gpp',
                                'video/3gpp'),
                '3G2' => array(
                                'video/3gpp2',
                                'audio/3gpp2'),
                'FLV' => 'video/x-flv',
                'WEBM'=> 'video/webm',
                'APK' => 'application/vnd.android.package-archive',
            ));
        }


        /**
         * Gets allowed file types extensions
         *
         * @return array
         */
        
        public function getAllowedFileExtensions($post_id=null){
            if(empty($post_id) && !isset($_POST['comment_post_ID'])){
                global $post;
                if(isset($post) && is_object($post)){
                  $post_id = $post->ID;  
                }else{
                  return;
                }
            }

            $return = array();
            $pluginFileTypes = $this->getMimeTypes();

            if(isset($_POST['comment_post_ID'])){
                $assignment_id = $_POST['comment_post_ID'];
            }
            
            if(empty($assignment_id)){
                $assignment_id = $post_id;
            }
            $attachment_type=get_post_meta($assignment_id,'vibe_attachment_type',true);
            if(is_array($attachment_type) && in_array('JPG',$attachment_type)){
                $attachment_type[]='JPEG';
            }
            if(empty($attachment_type)){
              $attachment_type=array('JPEG');
            }
            return $attachment_type;
        }


        /**
         * Gets allowed file types for attachment check.
         *
         * @return array
         */

        public function getAllowedMimeTypes($post_id=null)
        {   
            if(empty($post_id)){
                global $post;
                $post_id = $post->ID;
            }
            $return = array();
            $pluginFileTypes = $this->getMimeTypes();
            $ext=$this->getAllowedFileExtensions($post_id);
            foreach($ext as $key){
                if(array_key_exists($key, $pluginFileTypes)){
                    if(!function_exists('finfo_file') || !function_exists('mime_content_type')){
                        if(($key ==  'DOCX') || ($key == 'DOC') || ($key == 'PDF') ||
                            ($key == 'ZIP') || ($key == 'RAR')){
                            $return[] = 'application/octet-stream';
                        }
                    }
                    if(is_array($pluginFileTypes[$key])){
                        foreach($pluginFileTypes[$key] as $fileType){
                            $return[] = $fileType;
                        }
                    } else {
                        $return[] = $pluginFileTypes[$key];
                    }
                }
            }
            return $return;
        }

        function _mime_content_type($filename) {

            /**
            *    mimetype
            *    Returns a file mimetype. Note that it is a true mimetype fetch, using php and OS methods. It will NOT
            *    revert to a guessed mimetype based on the file extension if it can't find the type.
            *    In that case, it will return false
            **/
            if (!file_exists($filename) || !is_readable($filename)) return false;
            if(class_exists('finfo')){
                $result = new finfo();
                if (is_resource($result) === true) {
                    return $result->file($filename, FILEINFO_MIME_TYPE);
                }
            }
            
             // Trying finfo
             if (function_exists('finfo_open')) {
               $finfo = finfo_open(FILEINFO_MIME);
               $mimeType = finfo_file($finfo, $filename);
               finfo_close($finfo);
               // Mimetype can come in text/plain; charset=us-ascii form
               if (strpos($mimeType, ';')) list($mimeType,) = explode(';', $mimeType);
               return $mimeType;
             }
            
             // Trying mime_content_type
             if (function_exists('mime_content_type')) {
               return mime_content_type($filename);
             }
            

             // Trying to get mimetype from images
             $imageData = getimagesize($filename);
             if (!empty($imageData['mime'])) {
               return $imageData['mime'];
             }
             // Trying exec
             if (function_exists('exec')) {
               $mimeType = exec("/usr/bin/file -i -b $filename");
               if(strpos($mimeType,';')){
                 $mimeTypes = explode(';',$mimeType);
                 return $mimeTypes[0];
               }
               if (!empty($mimeType)) return $mimeType;
             }
            return false;
        }

        /**
         * This one actually will need explaining, it's hard
         *
         * @param array $existing
         * @return array
         */

        public function getAllowedUploadMimes($existing = array())
        {
            // we get mime types and saved file types
            $return = array();
            $pluginFileTypes = $this->getMimeTypes();
            if(is_array($this->settings))
            foreach($this->settings as $key ){
                // list thru them and if it's allowed and not in list, we added there,
                // in reality, I'm thinking about removing the wp ones, and all mines,
                // since wordpress mime types are very limited, we can do better guys
                // cuase it sucks, and doesn't have enough mime types, actually let's
                // just do it ...
                if(array_key_exists($key, $pluginFileTypes)){
                    $keyCheck = strtolower($key);
                    // here we would have checked, if mime type is already there,
                    // but we want strong list of mime types, so we just add it all.
                    if(is_array($pluginFileTypes[$key])){
                        foreach($pluginFileTypes[$key] as $fileType){
                            $keyHacked = preg_replace("/[^0-9a-zA-Z ]/", "", $fileType);
                            $return[$keyCheck . '|' . $keyCheck . '_' . $keyHacked] = $fileType;
                        }
                    } else {
                        $return[$keyCheck] = $pluginFileTypes[$key];
                    }
                }
            }
            return array_merge($return, $existing);
        }


        /*
         * For error info, and form upload info.
         */

        public function displayAllowedFileTypes($post_id=null)
        {   
            $fileTypesString = '';
            $filetypes = $this->getAllowedFileExtensions($post_id);
            if(isset($filetypes) && is_Array($filetypes))
            foreach($filetypes as $value){
                $fileTypesString .= $value . ', ';
            }

            return substr($fileTypesString, 0, -2);
        }


        /**
         * For attachment display, get's image mime types
         *
         * @return array
         */

        public function getImageMimeTypes()
        {
            return array(
                'image/jpeg',
                'image/jpg',
                'image/jp_',
                'application/jpg',
                'application/x-jpg',
                'image/pjpeg',
                'image/pipeg',
                'image/vnd.swiftview-jpeg',
                'image/x-xbitmap',
                'image/gif',
                'image/x-xbitmap',
                'image/gi_',
                'image/png',
                'application/png',
                'application/x-png'
            );
        }


        /**
         * For attachment display, get's audio mime types
         *
         * @return array
         */
        // TODO: only check ones audio player can play?

        public function getAudioMimeTypes()
        {
            return array(
                'audio/mpeg',
                'audio/x-mpeg',
                'audio/mp3',
                'audio/x-mp3',
                'audio/mpeg3',
                'audio/x-mpeg3',
                'audio/mpg',
                'audio/x-mpg',
                'audio/x-mpegaudio',
                'audio/mp4a-latm',
                'audio/ogg',
                'application/ogg',
                'audio/wav',
                'audio/x-wav',
                'audio/wave',
                'audio/x-pn-wav',
                'audio/x-ms-wma'
            );
        }


        /**
         * For attachment display, get's audio mime types
         *
         * @return array
         */

        public function getVideoMimeTypes()
        {
            return array(
                'video/mp4v-es',
                'audio/mp4',
                'video/mp4',
                'video/x-m4v',
                'video/quicktime',
                'video/x-quicktime',
                'image/mov',
                'audio/aiff',
                'audio/x-midi',
                'audio/x-wav',
                'video/avi',
                'video/x-ms-wmv',
                'video/avi',
                'video/msvideo',
                'video/x-msvideo',
                'image/avi',
                'video/xmpg2',
                'application/x-troff-msvideo',
                'audio/aiff',
                'audio/avi',
                'video/avi',
                'video/mpeg',
                'video/mpg',
                'video/x-mpg',
                'video/mpeg2',
                'application/x-pn-mpg',
                'video/x-mpeg',
                'video/x-mpeg2a',
                'audio/mpeg',
                'audio/x-mpeg',
                'image/mpg',
                'video/ogg',
                'audio/3gpp',
                'video/3gpp',
                'video/3gpp2',
                'audio/3gpp2',
                'video/x-flv',
                'video/webm',
            );
        }


        /**
         * This way we sort of fake our "enctype" in, since there's not ohter hook
         * that would allow us to put it there naturally, and no, we won't use JS for that
         * since that's rubbish and not bullet-proof. Yes, this creates empty form on page,
         * but who cares, it works and does the trick.
         */

        public function displayBeforeForm(){
            if(get_post_type() != WPLMS_ASSIGNMENTS_CPT)
                return;
            if(empty($this->plupload_assignment_e_d))
            echo '</form><form action="'.site_url( '/wp-comments-post.php' ).'" method="post" enctype="multipart/form-data" id="attachmentForm" class="comment-form" novalidate>';
        }

        /*
        plupload functions 
        */

        function saveAttachment($commentId)
        {
            if(get_post_type($_POST['comment_post_ID']) != WPLMS_ASSIGNMENTS_CPT)    
                return;
            
            $assignmenttype = get_post_meta($_POST['comment_post_ID'],'vibe_assignment_submission_type',true);

            if($assignmenttype != 'upload')
                return;
            if(!empty($this->plupload_assignment_e_d)){
                $attachIds =array();
                $files = $_POST['attachment_ids'];
                if(is_Array($files))
                foreach($files as $file){
                    $attachIds[]=$file;
                }
                $savedassignment=get_comment($commentId);
                update_post_meta($savedassignment->comment_post_ID,$comment_post_ID->user_id,0);
                update_comment_meta($commentId, 'attachmentId', $attachIds);
            }else{
               $files = $this->reArrayFiles($_FILES['attachment']);
               $savedassignment=get_comment($commentId);
                if(is_Array($files))
                foreach($files as $file){
                    if($file['size'] > 0){
                        $_FILES = array ('attachment' => $file);
                        $fileInfo = pathinfo($_FILES['attachment']['name']);
                        $fileExtension = strtolower($fileInfo['extension']);
                        $fileType = $this->_mime_content_type($_FILES['attachment']['tmp_name']);
                        if(in_array($fileType, $this->getAllowedMimeTypes($savedassignment->comment_post_ID)) && in_array(strtoupper($fileExtension), $this->getAllowedFileExtensions($savedassignment->comment_post_ID)) ){
                          $bindId = ATT_BIND ? $_POST['comment_post_ID'] : 0; // TRUE
                          $attachId = $this->insertAttachment('attachment', $bindId);
                        }else{
                          unset($_FILES);
                          wp_die('<strong>'.__('ERROR:','wplms-assignments').'</strong> '.__('File you upload must be valid file type','wplms-assignments').' <strong>('. $this->displayAllowedFileTypes() .')</strong>'.__(', and under ','wplms-assignments'). $this->getmaxium_upload_file_size($savedassignment->comment_post_ID) .__('MB(s)!','wplms-assignments'));
                        }

                        

                        $attachIds[]=$attachId;
                        unset($_FILES);
                    }
                }
                
                update_post_meta($savedassignment->comment_post_ID,$comment_post_ID->user_id,0);
                update_comment_meta($commentId, 'attachmentId', $attachIds); 
            }
            
        }
        function wplms_assignment_plupload(){

            check_ajax_referer('wplms_assignment_plupload');
            if(!is_user_logged_in())
                die('user not logged in');

            $user_id = get_current_user_id();
            
            if (empty($_FILES) || $_FILES['file']['error']) {
              die('{"OK": 0, "info": "Failed to move uploaded file."}');
            }

            $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
            $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
            $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : $_FILES["file"]["name"];
            
            $upload_dir_base = wp_upload_dir();
            $assignment_id = $_POST['assignment_id'];
            $folderPath = $upload_dir_base['basedir']."/wplms_assignments_folder/".$user_id.'/'.$assignment_id;
            if(function_exists('is_dir') && !is_dir($folderPath)){
                if(function_exists('mkdir')) 
                    mkdir($folderPath, 0755, true) || chmod($folderPath, 0755);
            }


            $filePath = $folderPath."/$fileName";
             /*if(function_exists('file_exists') && file_exists($filePath)){
                echo __(' Chunks upload error ','wplms-assignments'). $fileName.__(' already exists.Please rename your file and try again ','wplms-assignments');
                die();
             }*/
            // Open temp file
            if($chunk == 0) $perm = "wb" ;
            else $perm = "ab";

            $out = @fopen("{$filePath}.part",$perm );

            if ($out) {
              // Read binary input stream and append it to temp file
              $in = @fopen($_FILES['file']['tmp_name'], "rb");
             
              if ($in) {
                while ($buff = fread($in, 4096))
                  fwrite($out, $buff);
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

        function insert_assignment_attachment(){
            if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'wplms_assignment_plupload_final') || !is_user_logged_in()){
                
                $error =__('Security check failed contact administrator','wplms-assignments');
                wp_die($error);
            }else{
                if(!empty($_POST['user'])){
                  $user_id =  $_POST['user'];
                }else{
                  $user_id = get_current_user_id();
                }
                
                $filename = $_POST['name'];
                
                $upload_dir_base = wp_upload_dir();
                $assignment_id = $_POST['assignment_id'];
                if(!empty($_POST['user']) && !empty($_POST['context']) && $_POST['context'] == 'remarks'){
                    $folderPath = $upload_dir_base['basedir']."/wplms_assignments_folder/".$user_id.'/'.$assignment_id.'/remarks';
                }else{
                    $folderPath = $upload_dir_base['basedir']."/wplms_assignments_folder/".$user_id.'/'.$assignment_id;   
                }
                
                $filePath = $folderPath.'/'.$filename;
                if(get_post_type($_POST['assignment_id']) != WPLMS_ASSIGNMENTS_CPT){
                    unlink($filePath);
                    die();  
                }
                   

                $assignmenttype = get_post_meta($_POST['assignment_id'],'vibe_assignment_submission_type',true);

                if($assignmenttype != 'upload'){
                    unlink($filePath);
                    die(); 
                }
                
                if(!empty($_POST['name'])){
                    $name = basename($_POST['name']);
                        if($_POST['size'] > 0){
                            $fileInfo = pathinfo($filePath);
                            $fileExtension = strtolower($fileInfo['extension']);
                            $fileType = $this->_mime_content_type($filePath);
    
                            if (!in_array($fileType, $this->getAllowedMimeTypes($assignment_id)) || !in_array(strtoupper($fileExtension), $this->getAllowedFileExtensions($assignment_id)) || $_POST['size'] > ($this->getmaxium_upload_file_size($assignment_id) * 1048576)) { // file size from admin
                                unlink($filePath);
                                echo '0';
                            }else{
                                
                                $attachment = array(
                                    'guid'           => $filePath, 
                                    'post_mime_type' => $_POST['type'],
                                    'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filePath ) ),
                                    'post_content'   => '',
                                    'post_status'    => 'inherit'
                                );
                             
                                if(!empty($_POST['assignment_id'])){
                                  $attachment_id = wp_insert_attachment($attachment,$filePath,$_POST['assignment_id']);  
                                }else{
                                    $attachment_id ='';
                                }
                                
                               
                                if(!empty($attachment_id )  ){
                                  require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
                                  require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
                                  require_once(ABSPATH . 'wp-admin' . '/includes/media.php');
                                    if(!empty($_POST['context']) && $_POST['context'] == 'remarks' && !empty($_POST['user'])){
                                        wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $filePath ) );
                                        echo wp_get_attachment_url($attachment_id);
                                          
                                    }else{
                                        wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $filePath ) );
                                        echo "<input class='attachment_ids' name='attachment_ids[]' type='hidden' value='".$attachment_id."'>";
                                        echo $_POST['name'].'<a class="delete_attachment" data-id="'. $attachment_id .'" data-security="'.wp_create_nonce('user'.$attachment_id.$user_id).'"></a>';
                                    }
                                   
                                } 
                            }
                        }
                    }
            }
            die();
        }
        function remove_attachment_plupload(){
            if(!is_user_logged_in())
            return;
            if(!empty($_POST['user']) && !empty($_POST['context']) && $_POST['context'] == 'remarks'){
                $user_id = $_POST['user'];  
            }else{
                $user_id = get_current_user_id();  
            }
            
            if(!empty($_POST) && isset($_POST['security']) && wp_verify_nonce($_POST['security'],'user'.$_POST['id'].$user_id)){
                $id= intval($_POST['id']);
                if(get_post_type($id) != 'attachment'){
                    echo __('Invalid ID','wplms-assignments');
                    die();
                }
                $removed = wp_delete_attachment( $id);
                if(!empty($removed )){
                    echo __('Attachment removed!','wplms-assignments');    
                }else{
                  echo __('Unable to remove previous submissions','wplms-assignments');  
                }
            
            }else
            echo __('Unable to remove previous submissions','wplms-assignments');

            die();
        }
        function check_file_exists_plupload(){
            check_ajax_referer('wplms_check_file_exists_plupload');
            if(!is_user_logged_in())
                die('user not logged in');
            if(empty($_POST['name']))
                die();
            $fileName = $_POST['name'];
            $user_id = get_current_user_id();
            $upload_dir_base = wp_upload_dir();
            $filePath = $upload_dir_base['basedir']."/wplms_assignments_folder/".$user_id."/$fileName";
            if(function_exists('file_exists') && file_exists($filePath)){
                echo '1';
                unlink($filePath.'.part');
                die();
            }else{
                echo '0';
                die();
            }
            die();
        }
        function delete_file_exists_plupload(){
             check_ajax_referer('wplms_delete_file_exists_plupload');
            if(!is_user_logged_in())
                die('user not logged in');
            if(empty($_POST['name']))
                die();
        }
        /*
         * Display form upload field.
         */

        public function displayFormAttBefore()  { 
            if(get_post_type() != WPLMS_ASSIGNMENTS_CPT)
                return; 
            if(ATT_POS == 'before'){ $this->displayFormAtt(); } 
        }

        public function displayFormAtt(){  

            if(get_post_type() != WPLMS_ASSIGNMENTS_CPT)
                return;

            global $post;
            $assignment_id =  $post->ID;
            $assignment_submission_type=get_post_meta($post->ID,'vibe_assignment_submission_type',true);
            if(!isset($assignment_submission_type) || $assignment_submission_type != 'upload')
                return;
            
            $user_id = get_current_user_id();
            $answers=get_comments(array(
              'post_id' => $post->ID,
              'status' => 'approve',
              'number'=>1,
              'user_id' => $user_id
              ));
            if(isset($answers) && is_array($answers) && count($answers)){
                $answer = end($answers);
                $content = $answer->comment_content;
            }else{
                $content='';
            }

            $required = ATT_REQ ? ' <span class="required">*</span>' : '';
            echo '<p class="comment-form-url comment-form-attachment">'.
                    '<label for="attachment">' . ATT_TITLE . $required .'<small class="attachmentRules">&nbsp;&nbsp;('.__('Allowed file types','wplms-assignments').': <strong>'. $this->displayAllowedFileTypes() .'</strong>, '.__('maximum file size','wplms-assignments').': <strong>'. $this->getmaxium_upload_file_size() .__('MB(s)','wplms-assignments').'.</strong></small></label>'.
                '</p>';

            if(isset($answers) && is_array($answers) && count($answers)){
                    $attachmentIDs = get_comment_meta($answer->comment_ID,'attachmentId',true);
                    echo '<p class="assignment_submission"><strong>'.__('RECENTLY UPLOADED','wplms-assignments').' : </strong>';
                if(is_array($attachmentIDs)){
                    foreach($attachmentIDs as $attachmentID){
                        $attachmentName = basename(get_attached_file($attachmentID));
                        $att=wp_get_attachment_url($attachmentID);
                        echo '<br><a class="attachmentLink" target="_blank" href="'.$att.'">'.$attachmentName.'</a>';
                    }
                }else{
                    $attachmentID = $attachmentIDs;
                    // if comma saperated the foreach
                    $attachmentName = basename(get_attached_file($attachmentID));
                    $att=wp_get_attachment_url($attachmentID);
                    echo '<a class="attachmentLink" target="_blank" href="'.$att.'">'.$attachmentName.'</a>';
                }
                echo '<a id="clear_previous_submissions" data-id="'.get_the_ID().'" data-security="'.wp_create_nonce('user'.$user_id).'"><i class="icon-x"></i></a></p>';
            }
            if(!empty($this->plupload_assignment_e_d)){
                if($this->checkformagain == 0){
                    wp_enqueue_script('plupload');
                    ?>
                            <div  class="plupload_error_notices notice notice-error is-dismissible"></div>
                            <div id="plupload-upload-ui" class="hide-if-no-js">
                                <div id="drag-drop-area">
                                    <div class="drag-drop-inside">
                                        <p class="drag-drop-info"><?php _e('Drop files here','wplms-assignments'); ?></p>
                                        <p><?php _ex('or', 'Uploader: Drop files here - or - Select Files','wplms-assignments'); ?></p>
                                        <p class="drag-drop-buttons"><input id="plupload-browse-button" type="button" value="<?php _e('Select Files','wplms-assignments'); ?>" class="button" /></p>
                                    </div>
                                </div>
                            </div>

                            <div class="pl_assignment_progress">
                            </div>
                            <div class="warning_plupload">
                                <h3><?php echo __("Please do not close the window until process is completed","wplms-assignments") ?></h3>
                            </div>
                            
                            <?php
                                if ( function_exists( 'ini_get' ) )
                                    $post_size = ini_get('post_max_size') ;
                                $post_size = preg_replace('/[^0-9\.]/', '', $post_size);
                                $post_size = intval($post_size);
                                if($post_size != 1){
                                    $post_size = $post_size-1;
                                }
                                
                               
                             $plupload_init = array(
                                'runtimes'            => 'html5,silverlight,flash,html4',
                                'chunk_size'          =>  (($post_size*1024) - 100).'kb',
                                'max_retries'         => 3,
                                'browse_button'       => 'plupload-browse-button',
                                'container'           => 'plupload-upload-ui',
                                'drop_element'        => 'drag-drop-area',
                                'multiple_queues'     => false,
                                'multi_selection'     => false,
                                'max_file_size'       => ($this->getmaxium_upload_file_size() * 1024).'kb',
                                'filters'             => array( array( 'extensions' => implode( ',', $this->getAllowedFileExtensions()) ) ),
                                'url'                 => admin_url('admin-ajax.php'),
                                'flash_swf_url'       => includes_url('js/plupload/plupload.flash.swf'),
                                'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
                                
                                'multipart'           => true,
                                'urlstream_upload'    => true,

                                // additional post data to send to our ajax hook
                                'multipart_params'    => array(
                                  '_ajax_nonce' => wp_create_nonce('wplms_assignment_plupload'),
                                  'action'      => 'wplms_assignment_plupload', 
                                  'assignment_id'=> $assignment_id
                                  //'s3_bucket'   => (empty($buckets) ? $this->bucket : ''  ),       // the ajax action name
                                ),
                              );

                            $plupload_init = apply_filters('plupload_init', $plupload_init);
                            
                            ?>

                            <script type="text/javascript">

                                jQuery(document).ready(function($){
                                    $( 'body' ).delegate( '.delete_attachment', "click", function(event){
                                        event.preventDefault();
                                        var $this = $(this);
                                        $.confirm({
                                            text: wplms_assignment_messages.remove_attachment,
                                            confirm: function() {
                                                $.ajax({
                                                    type: "POST",
                                                    url: ajaxurl,
                                                    data: { action: 'remove_attachment_plupload', 
                                                              id: $this.attr('data-id'),
                                                              security: $this.attr('data-security')
                                                        },
                                                    cache: false,
                                                    success: function (html) {
                                                        /*$this.find('i.fa').remove();*/
                                                        $this.html(html);
                                                        $this.parent().remove();
                                                       /* setTimeout(function(){location.reload();}, 3000);*/
                                                    }
                                                });
                                            },
                                            cancel: function() {
                                                $this.find('i.fa').remove();
                                            },
                                            confirmButton: vibe_course_module_strings.confirm,
                                            cancelButton: vibe_course_module_strings.cancel
                                        });
                                    });

                                    var temp = <?php echo json_encode($plupload_init,JSON_UNESCAPED_SLASHES); ?>;
                                    if(temp.multipart_params.s3_bucket == ''){
                                        temp.multipart_params.s3_bucket= jQuery('body').find('form.wplms-s3-upload #wplms_s3_bucket').val();
                                    }
                                    // create the uploader and pass the config from above
                                    var uploader = new plupload.Uploader(temp);
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
                                    
                                    var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
                                    plupload.each(files, function(file){
                                        if (file.size > max && up.runtime != 'html5'){
                                            console.log('call "upload_to_amazon" not sent');
                                        }else{
                                             $('.pl_assignment_progress').addClass('visible');
                                            var clone = $('.pl_assignment_progress').append('<div class="'+file.id+'">'+file.name+'<i></i><strong><span></span></strong></div>');
                                            $('.pl_assignment_progress').append(clone);
                                            $('.warning_plupload').show(300);
                                        }
                                       /* $.ajax({
                                            type: "POST",
                                            url: ajaxurl,
                                            data: { action: 'check_file_exists_plupload', 
                                                      name: file.name,
                                                      _ajax_nonce: "<?php echo wp_create_nonce('wplms_check_file_exists_plupload'); ?>"
                                                },
                                            cache: false,
                                            success: function (html) {
                                                if(html == '1'){
                                                    pakode = 1;
                                                    up.stop();
                                                    up.refresh();
                                                    $('.pl_assignment_progress div.'+file.id).html("<div class='message animate tada load has-error text-danger file_exists'><b>("+file.name+")</b><?php echo __('  already exists. Please rename the file and try again.','wplms-assignments') ?></div>");
                                                    $('.warning_plupload').hide(300);
                                                    setTimeout(function(){
                                                        $('.pl_assignment_progress div.'+file.id).fadeOut(600);
                                                        $('.pl_assignment_progress div.'+file.id).remove();
                                                        
                                                    }, 10000);
                                                }else{

                                                }
                                            }
                                        });*/
                                        //stop attachment if file exists
                                       /* $('.stop_s3_plupload_upload').on('click',function() {
                                            if(confirm("<?php echo __('Are you sure you want to quit uploading?','wplms-assignments');?>")){
                                                if(!($(this).hasClass('disabled'))){
                                                    setTimeout(function(){
                                                    $('.pl_assignment_progress div.'+file.id).fadeOut(600);
                                                    }, 1200);
                                                    up.stop();
                                                    up.destroy();
                                                }
                                            }else{
                                                return false;
                                            }
                                        });*/
                                    });

                                    up.refresh();
                                    up.start();
                                });
                                uploader.bind('Error', function(up, args) {
                                    $('.plupload_error_notices').show();
                                    $('.plupload_error_notices').html('<div class="message text-danger danger tada animate load">'+args.message+' for '+args.file.name+'</div>');
                                    setTimeout(function(){
                                        $('.plupload_error_notices').hide();
                                    }, 5000);
                                    up.refresh();
                                    up.start();
                                });

                                uploader.bind('UploadProgress', function(up, file) {
                                    
                                    if(file.percent < 100 && file.percent >= 1){
                                        $('.pl_assignment_progress div.'+file.id+' strong span').css('width', (file.percent)+'%');
                                        $('.pl_assignment_progress div.'+file.id+' i').html( (file.percent)+'%');
                                    }
                                    
                                    up.refresh();
                                    up.start(); 
                                });
                                  // a file was uploaded 
                                uploader.bind('FileUploaded', function(up, file, response) {
                                    
                                    //$('.stop_s3_plupload_upload').addClass('disabled');
                                     $.ajax({
                                      type: "POST",
                                      url: ajaxurl,
                                      data: { action: 'insert_assignment_attachment', 
                                              security: '<?php echo wp_create_nonce("wplms_assignment_plupload_final"); ?>',
                                              assignment_id:'<?php global $post; echo $post->ID; ?>',
                                              name:file.name,
                                              type:file.type,
                                              size:file.origSize,
                                              
                                            },
                                      cache: false,
                                      success: function (html) {
                                        if(html){
                                            if(html == '0'){
                                                $('.pl_assignment_progress div.'+file.id+' strong span').css('width', '0%');
                                                $('.pl_assignment_progress div.'+file.id+' strong').html("<i class='error'><?php echo __("File type not allowed","wplms-assignments"); ?><i>");
                                                setTimeout(function(){
                                                    $('.pl_assignment_progress div.'+file.id).fadeOut(600);
                                                    $('.pl_assignment_progress div.'+file.id).remove();
                                                }, 2500);
                                                $('.warning_plupload').hide(300);
                                                return false;
                                            }else{
                                             
                                                $('.pl_assignment_progress div.'+file.id+' strong span').css('width', '100%');
                                                $('.pl_assignment_progress div.'+file.id+' i').html('100%');
                                                
                                                    setTimeout(function(){
                                                      $('.pl_assignment_progress div.'+file.id+' strong').fadeOut(500);
                                                    }, 1200);
                                                    
                                                    $('.pl_assignment_progress div.'+file.id).html(html);
                                                    setTimeout(function(){
                                                        if($('.pl_assignment_progress strong').length < 1){
                                                            $('.warning_plupload').hide(300);
                                                        }
                                                        }, 1750);
                                            }   
                                        }
                                        
                                      }
                                    });
                                });
                            });   
            
                            </script>
                          <?php
                          }
         
                           $this->checkformagain = 1;
            }else{
               

                $extensions = $this->getAllowedFileExtensions();
                
                if(is_array($extensions)){
                    $extensions = implode('|',$extensions);
                    echo '<p class="comment-form-url comment-form-attachment">
                        <input id="attachment" name="attachment[]" type="file" class="multi" accept="'.$extensions.'"/>
                      </p>';    
                }
            }

        }

        /**
         * Rearrange $_Files array
         *
         * @param $data
         * @return mixed
         */
        function reArrayFiles(&$file_post) {

            $file_ary = array();
            if(is_array($file_post)){
                $file_count = count($file_post['name']);
                $file_keys = array_keys($file_post);

                for ($i=0; $i<$file_count; $i++) {
                    foreach ($file_keys as $key) {
                        $file_ary[$i][$key] = $file_post[$key][$i];
                    }
                }

                return $file_ary;
            }
        }
        /**
         * Checks attachment, size, and type and throws error if something goes wrong.
         *
         * @param $data
         * @return mixed
         */

        public function checkAttachment($data){   

            if(get_post_type($data['comment_post_ID']) != WPLMS_ASSIGNMENTS_CPT)
                return $data;

            $assignmenttype = get_post_meta($data['comment_post_ID'],'vibe_assignment_submission_type',true);

            if($assignmenttype != 'upload')
                return $data;

            if(!empty($_FILES)){
                $files = $this->reArrayFiles($_FILES['attachment']);
                if(is_array($files))
                foreach($files as $file){
                    if($file['size'] > 0 && $file['error'] == 0){
                        $fileInfo = pathinfo($file['name']);
                        $fileExtension = strtolower($fileInfo['extension']);
                        $fileType = $this->_mime_content_type($file['tmp_name']); // custom function

                        if (!in_array($fileType, $this->getAllowedMimeTypes()) || !in_array(strtoupper($fileExtension), $this->getAllowedFileExtensions()) || $file['size'] > ($this->getmaxium_upload_file_size($data['comment_post_ID']) * 1048576)) { // file size from admin
                            wp_die('<strong>'.__('ERROR:','wplms-assignments').'</strong> '.__('File you upload must be valid file type','wplms-assignments').' <strong>('. $this->displayAllowedFileTypes() .')</strong>'.__(', and under ','wplms-assignments'). $this->getmaxium_upload_file_size($data['comment_post_ID']) .__('MB(s)!','wplms-assignments'));
                        }

                    } elseif (ATT_REQ && $file['error'] == 4) {
                        wp_die('<strong>'.__('ERROR:','wplms-assignments').'</strong> '.__('Please upload an Attachment.','wplms-assignments'));
                    } elseif($file['error'] == 1) {
                        wp_die('<strong>'.__('ERROR:','wplms-assignments').'</strong> '.__('The uploaded file exceeds the upload_max_filesize directive in php.ini.','wplms-assignments'));
                    } elseif($file['error'] == 2) {
                        wp_die('<strong>'.__('ERROR:','wplms-assignments').'</strong> '.__('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.','wplms-assignments'));
                    } elseif($file['error'] == 3) {
                        wp_die('<strong>'.__('ERROR:','wplms-assignments').'</strong> '.__('The uploaded file was only partially uploaded. Please try again later.','wplms-assignments'));
                    } elseif($file['error'] == 6) {
                        wp_die('<strong>'.__('ERROR:','wplms-assignments').'</strong> '.__('Missing a temporary folder.','wplms-assignments'));
                    } elseif($file['error'] == 7) {
                        wp_die('<strong>'.__('ERROR:','wplms-assignments').'</strong> '.__('Failed to write file to disk.','wplms-assignments'));
                    } elseif($file['error'] == 7) {
                        wp_die('<strong>'.__('ERROR:','wplms-assignments').'</strong> '.__('A PHP extension stopped the file upload.','wplms-assignments'));
                    }
                }
            }elseif(!empty($this->plupload_assignment_e_d) && empty($_POST['attachment_ids'])){
              wp_die('<strong>'.__('ERROR:','wplms-assignments').'</strong> '.__('Please upload an Attachment.','wplms-assignments'));
            }
            
            return $data;
        }


        /**
         * Notification email message
         *
         * @param $notify_message
         * @param $comment_id
         * @return string
         */

        public function notificationText($notify_message,  $comment_id)
        {
            if(WPLMS_Assignments::hasAttachment($comment_id)){
                $attachmentIds = get_comment_meta($comment_id, 'attachmentId', TRUE);
                if(is_Array($attachmentIds)){
                    foreach($attachmentIds as $attachmentId){
                        $attachmentName = basename(get_attached_file($attachmentId));
                        $notify_message .= __('Attachment:','wplms-assignments') . "\r\n" .  $attachmentName . "\r\n\r\n";
                    }
                }else{
                    $attachmentId = $attachmentIds;
                    $attachmentName = basename(get_attached_file($attachmentId));
                    $notify_message .= __('Attachment:','wplms-assignments') . "\r\n" .  $attachmentName . "\r\n\r\n";
                }
            }
            return $notify_message;
        }


        /**
         * Inserts file attachment from your comment to wordpress
         * media library, assigned to post.
         *
         * @param $fileHandler
         * @param $postId
         * @return mixed
         */
            

        public function insertAttachment($fileHandler, $postId)
        {
            require_once(ABSPATH . "wp-admin" . '/includes/image.php');
            require_once(ABSPATH . "wp-admin" . '/includes/file.php');
            require_once(ABSPATH . "wp-admin" . '/includes/media.php');

            
            return media_handle_upload($fileHandler, $postId);
        }


        /**
         * Save attachment to db, with all sizes etc. Assigned
         * to post, or not.
         *
         * @param $commentId
         */

       /* public function saveAttachment($commentId)
        {
            if(get_post_type($_POST['comment_post_ID']) != WPLMS_ASSIGNMENTS_CPT)    
                return;
            
            $assignmenttype = get_post_meta($_POST['comment_post_ID'],'vibe_assignment_submission_type',true);

            if($assignmenttype != 'upload')
                return;

            $files = $this->reArrayFiles($_FILES['attachment']);
            if(is_Array($files))
            foreach($files as $file){
                if($file['size'] > 0){
                    $_FILES = array ('attachment' => $file); 
                    $bindId = ATT_BIND ? $_POST['comment_post_ID'] : 0; // TRUE
                    $attachId = $this->insertAttachment('attachment', $bindId);
                    $attachIds[]=$attachId;
                    unset($_FILES);
                }
            }
            $savedassignment=get_comment($commentId);
            update_post_meta($savedassignment->comment_post_ID,$comment_post_ID->user_id,0);
            update_comment_meta($commentId, 'attachmentId', $attachIds);
        }*/


        /**
         * Displays attachment in comment, according to
         * position selected in settings, and according to way selected in admin.
         *
         * @param $comment
         * @return string
         */

        public function displayAttachment($comment)
        {
            $attachmentIds = get_comment_meta(get_comment_ID(), 'attachmentId', TRUE);
            if(!is_array($attachmentIds)){
                $attachmentIds = array($attachmentIds);
            }
            foreach($attachmentIds as $attachmentId){
                if(is_numeric($attachmentId) && !empty($attachmentId)){

                    // atachement info
                    $attachmentLink = wp_get_attachment_url($attachmentId);
                    $attachmentMeta = wp_get_attachment_metadata($attachmentId);
                    $attachmentName = basename(get_attached_file($attachmentId));
                    $attachmentType = get_post_mime_type($attachmentId);
                    $attachmentRel  = '';
                    $contentInner = '';
                    // let's do wrapper html
                    $contentBefore  = '<div class="attachmentFile"><p>' . $this->settings[$this->adminPrefix . 'ThumbTitle'] . ' ';
                    $contentAfter   = '</p><div class="clear clearfix"></div></div>';

                    // admin behaves differently
                    if(is_admin()){
                        $contentInner = $attachmentName;
                    } else {
                        // shall we do image thumbnail or not?
                        if(ATT_THUMB && in_array($attachmentType, $this->getImageMimeTypes()) && !is_admin()){
                            $attachmentRel = 'rel="lightbox"';
                            $contentInner = wp_get_attachment_image($attachmentId, 'thumb');
                            // audio player?
                        } elseif (ATT_PLAY && in_array($attachmentType, $this->getAudioMimeTypes())){
                            if(shortcode_exists('audio')){
                                $contentInner = do_shortcode('[audio src="'. $attachmentLink .'"]');
                            } else {
                                $contentInner = $attachmentName;
                            }
                            // video player?
                        } elseif (ATT_PLAY && in_array($attachmentType, $this->getVideoMimeTypes())){
                            if(shortcode_exists('video')){
                                $contentInner .= do_shortcode('[video src="'. $attachmentLink .'"]');
                            } else {
                                $contentInner = $attachmentName;
                            }
                            // rest ..
                        } else {
                            $contentInner = '&nbsp;<strong>' . $attachmentName . '</strong>';
                        }
                    }

                    // attachment link, if it's not video / audio
                    if(is_admin()){
                        $contentInnerFinal = '<a '.$attachmentRel.' class="attachmentLink" target="_blank" href="'. $attachmentLink .'" title="'.__('Download','wplms-assignments').': '. $attachmentName .'">';
                            $contentInnerFinal .= $contentInner;
                        $contentInnerFinal .= '</a>';
                    } else {
                        if((ATT_LINK) && !in_array($attachmentType, $this->getAudioMimeTypes()) && !in_array($attachmentType, $this->getVideoMimeTypes())){
                            $contentInnerFinal = '<a '.$attachmentRel.' class="attachmentLink" target="_blank" href="'. $attachmentLink .'" title="Download: '. $attachmentName .'">';
                                $contentInnerFinal .= $contentInner;
                            $contentInnerFinal .= '</a>';
                        } else {
                            $contentInnerFinal = $contentInner;
                        }
                    }

                    // bring a sellotape, this needs taping together
                    $contentInsert = $contentBefore . $contentInnerFinal . $contentAfter;

                    // attachment comment position
                    if(ATT_APOS == 'before' && !is_admin()){
                        $comment = $contentInsert . $comment;
                    } elseif(ATT_APOS == 'after' || is_admin()) {
                        $comment .= $contentInsert;
                    }
                }
            }
            return $comment;
        }


        /**
         * This deletes attachment after comment deletition.
         *
         * @param $commentId
         */

        public function deleteAttachment($commentId)
        {
            $attachmentId = get_comment_meta($commentId, 'attachmentId', TRUE);
            if(is_numeric($attachmentId) && !empty($attachmentId) && ATT_DEL){
                wp_delete_attachment($attachmentId, TRUE);
            }
        }


        /**
         * Has attachment
         *
         * @param $commentId
         * @return bool
         */

        public static function hasAttachment($commentId)
        {
            $attachmentId = get_comment_meta($commentId, 'attachmentId', TRUE);
            if(is_numeric($attachmentId) && !empty($attachmentId)){
                return true;
            }
            return false;
        }


        /*************** Admin Settings Functions **************/

        /**
         * Comment Action links
         *
         * @param $actions
         * @param $comment
         * @return array
         */

        public function addCommentActionLinks($actions, $comment)
        {
            if(WPLMS_Assignments::hasAttachment($comment->comment_ID)){
                $url = $_SERVER["SCRIPT_NAME"] . "?c=$comment->comment_ID&deleteAtt=1";
                $actions['deleteAtt'] = "<a href='$url' title='".esc_attr__('Delete Attachment','wplms-assignments')."'>".__('Delete Attachment','wplms-assignments').'</a>';
            }
            return $actions;
        }


        /***************** Plugin basic weapons ****************/

        /**
         * Let's check Wordpress version, and PHP version and tell those
         * guys whats needed to upgrade, if anything.
         *
         * @return bool
         */

        private function checkRequirements()
        {
            if (!function_exists('mime_content_type') && !function_exists('finfo_file') && version_compare(PHP_VERSION, '5.3.0') < 0){
                add_action('admin_notices', array($this, 'displayFunctionMissingNotice'));
                return TRUE;
            }
            return TRUE;
        }


        /**
         * Notify use about missing needed functions, and less security caused by that, let them hide nag of course.
         */

        public function displayFunctionMissingNotice()
        {
            $currentUser = wp_get_current_user();
            if (!get_user_meta($currentUser->ID, 'AssignmentAttachmentIgnoreNag') && current_user_can('install_plugins')){
                $this->displayAdminError((sprintf(
                    __('Regarding WPLMS Assignments Upload Assignment Functionality : It seems like your PHP installation is missing "mime_content_type" or "finfo_file" functions which are crucial '.
                    'for detecting file types of uploaded attachments. Please update your PHP installation OR be very careful with allowed file types, so '.
                    'intruders won\'t be able to upload dangerous code to your website! | <a href="%1$s">Hide Notice</a>','wplms-assignments'), '?AssignmentAttachmentIgnoreNag=1')), 'updated');
            }
        }


        /**
         * Save user nag if set, if they want to hide the message above.
         */

        private function setUserNag()
        {
            $currentUser = wp_get_current_user();
            if (isset($_GET['AssignmentAttachmentIgnoreNag']) && '1' == $_GET['AssignmentAttachmentIgnoreNag'] && current_user_can('install_plugins')){
                update_user_meta($currentUser->ID, 'AssignmentAttachmentIgnoreNag', 'true', true);
            }
        }


        /**
         * Admin error helper
         *
         * @param $error
         */

        private function displayAdminError($error, $class="error") { echo '<div id="message" class="'. $class .'"><p><strong>' . $error . '</strong></p></div>';  }


        function activate(){
            flush_rewrite_rules(false );
        }

        function deactivate(){
            flush_rewrite_rules(false );
        }

        function assignment_result(){
            if(!is_user_logged_in())
                return;
            $user_id = get_current_user_id();
            global $post;
            $expiry=get_user_meta($user_id,$post->ID,true);
            if($expiry < time()){
                $verify = get_post_meta($post->ID,$user_id,true);
                if(!is_numeric($verify)){
                    update_post_meta($post->ID,$user_id,2);
                }
            }

        }
        protected function __clone(){}

    }
}


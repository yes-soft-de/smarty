<?php

/**
 * FILE: notes-discussions.php 
 * Author: Mr.Vibe 
 * Credits: www.VibeThemes.com
 * Project: WPLMS
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class vibe_notes_discussions{
  
  private $version = '2.0';

  public function __construct(){
    add_action('wplms_before_notes_discussion',array($this,'wplms_before_notes_discussion'));
    add_filter('wplms_notes_dicussion_args',array($this,'wplms_notes_dicussion_args'));
    add_action('wp_ajax_load_more_notes',array($this,'wplms_load_more_notes'));
    add_action('wp_ajax_wplms_all',array($this,'wplms_all_notes_discussions'));
    add_action('wp_ajax_wplms_all_public_discussions',array($this,'wplms_all_public_discussions'));
    add_action('wp_ajax_wplms_instructor_unit_notes',array($this,'wplms_instructor_unit_notes'));
    add_action('wp_ajax_wplms_instructor_unit_discussions',array($this,'wplms_instructor_unit_discussions'));
    add_action('wp_ajax_wplms_my_notes_private',array($this,'wplms_my_notes_private'));
    add_action('wp_ajax_wplms_my_notes_public',array($this,'wplms_my_notes_public'));
    add_action('wp_ajax_get_unit_comment_count',array($this,'wplms_get_unit_comment_count'));
    add_action('wp_ajax_public_user_comment',array($this,'wplms_unit_public_user_comment'));
    add_action('wp_ajax_instructor_reply_user_comment',array($this,'wplms_instructor_reply_user_comment'));
    add_action('wp_ajax_private_user_comment',array($this,'wplms_unit_private_user_comment'));
    add_action('wp_ajax_remove_user_comment',array($this,'wplms_unit_remove_user_comment'));
    add_action('wp_ajax_edit_user_comment',array($this,'wplms_unit_edit_user_comment'));
    add_action('wp_ajax_unit_section_comments',array($this,'wplms_get_unit_section_comments'));
    add_action('wp_ajax_post_unit_comment',array($this,'wplms_post_unit_comment'));
    add_action('wp_ajax_get_user_reply',array($this,'wplms_get_user_reply'));
  }

  function wplms_before_notes_discussion(){
    /*if(!is_user_logged_id()){
      wp_redurect(site_url());
      exit();
    }*/
  }

  function wplms_notes_dicussion_args($args){
      if(!current_user_can('edit_posts')){
          $args['user_id']=get_current_user_id();

      }else{
        if(!current_user_can('manage_options')){
          $args['post_author']=get_current_user_id();
        }
      }
      if($_REQUEST['unit_id'] && is_numeric($_REQUEST['unit_id']) && !isset($_REQUEST['section'])){
          $args['post_id'] = $_REQUEST['unit_id'];
          if(!current_user_can('edit_posts')){
            global $wpdb;
            if(!is_user_logged_in()){
              $q = $wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key LIKE %s",'unit'.$args['post_id'].'%public');
            }else{
              $q = $wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE (meta_key LIKE %s OR meta_key LIKE %s)",'unit'.$args['post_id'].'_'.$args['user_id'],'unit'.$args['post_id'].'%public');  
            }

            $comment_ids = $wpdb->get_results($q,ARRAY_A);
            if(is_array($comment_ids)){
              $args['comment__in'] = array();
              foreach($comment_ids as $comment_id){
                 $args['comment__in'][] = $comment_id['comment_id'];
              }
            }
            unset($args['user_id']);
          }
      }
      if($_REQUEST['section'] && $_REQUEST['unit_id']){
        $section = $_REQUEST['section'];
        $unit_id = $_REQUEST['unit_id'];
        $user_id =get_current_user_id();
        $args['post_id'] = $_REQUEST['unit_id'];
        if(strlen($section) < 5 && is_numeric($unit_id)){
          if(current_user_can('edit_posts')){
            global $wpdb;
            if(!is_user_logged_in()){
              $q = $wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key LIKE %s AND meta_value = %s",'unit'.$args['post_id'].'%public',$section);
            }else{
              $q = $wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key LIKE %s AND meta_value = %s",'unit'.$args['post_id'].'%',$section);
            }

            $comment_ids = $wpdb->get_results($q,ARRAY_A);
            if(is_array($comment_ids)){
              $args['comment__in'] = array();
              foreach($comment_ids as $comment_id){
                 $args['comment__in'][] = $comment_id['comment_id'];
              }
             
            }
          }else{
            global $wpdb;
            if(is_user_logged_in()){
              $q = $wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key LIKE %s  AND meta_value = %s",'unit'.$args['post_id'].'%public',$section);
            }else{
              $q = $wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE (meta_key LIKE %s OR meta_key LIKE %s) AND meta_value = %s",'unit'.$args['post_id'].'_'.$args['user_id'],'unit'.$args['post_id'].'%public',$section);
            }
            $comment_ids = $wpdb->get_results($q,ARRAY_A);
            if(is_array($comment_ids)){
              $args['comment__in'] = array();
              foreach($comment_ids as $comment_id){
                 $args['comment__in'][] = $comment_id['comment_id'];
              }
            }
            unset($args['user_id']);
          }
        }
      }

      return $args;
  }

  function wplms_load_more_notes(){
   if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') ){
    _e('Security check Failed. Contact Administrator.','vibe');
       die();
    }
    $args = json_decode(stripcslashes($_POST['json']),true);
    if(!isset($args['offset']))
      $args['offset']=0;

    $args['offset'] +=$args['number'];
    $current_user_id=get_current_user_id();
    $comments_query = new WP_Comment_Query;
    $comments = $comments_query->query( $args );
    // Comment Loop
          if ( $comments ) {
            echo '<div id="new_notes_query">'.json_encode($args).'</div>';
            foreach ( $comments as $comment ) {
              ?>
              <li class="loaded <?php echo $comment->comment_type.' '.(($comment->comment_parent)?'parent':''); ?>"><div class="<?php echo $comment->comment_type; ?>" data-id="<?php echo $comment->comment_ID; ?>">
                <?php
                $author_id = $comment->user_id;
                echo get_avatar($author_id).' <a href="'.bp_core_get_user_domain($author_id).'" class="unit_comment_author"> '.bp_core_get_user_displayname( $author_id) .'</a><span class="right">'.__('UNIT','vibe').' : '.$comment->post_title.'</span>';
                ?>
                <div class="unit_comment_content"><?php echo $comment->comment_content; ?></div>
                <?php 
                  if($current_user_id == $author_id || current_user_can('edit_posts')){
                ?>
                <ul class="actions">
                    <li><a class="tip edit_unit_comment" title="<?php _e('Edit','vibe'); ?>"><i class="icon-pen-alt2"></i></a></li>
                    <?php
                      if($comment->comment_type == 'note'){
                    ?>
                    <li><a class="tip public_unit_comment" title="<?php _e('Make Public','vibe'); ?>"><i class="icon-fontawesome-webfont-3"></i></a></li>
                    <?php
                    }else{
                    ?>
                    <li><a class="tip private_unit_comment" title="<?php _e('Make Private','vibe'); ?>"><i class="icon-fontawesome-webfont-4"></i></a></li>
                    <?php
                    }
                    ?>
                    <?php
                      global $wpdb;
                      $replies = $wpdb->get_results($wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_value = %d",$comment->comment_ID),ARRAY_A);
                      if(isset($replies) && is_array($replies))
                        if(is_array($replies[0]) && is_numeric($replies[0]['comment_id']))
                          $replystr= '<li><a class="tip reply_unit_comment meta_info" data-meta="'.$replies[0]['comment_id'].'" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';

                      if(!isset($replystr))
                        echo '<li><a class="tip reply_unit_comment" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';
                      else
                        echo $replystr;
                    ?>
                    <li><a class="tip instructor_reply_unit_comment" title="<?php _e('Request Instructor reply','vibe'); ?>"><i class="icon-forward-2"></i></a></li>
                    <li><a class="tip remove_unit_comment" title="<?php _e('Remove','vibe'); ?>"><i class="icon-cross"></i></a></li>
                </ul>
                <?php
                  }else{
                    ?>
                    <ul class="actions">
                      <?php
                        global $wpdb;
                        $replies = $wpdb->get_results($wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_value = %d",$comment->comment_ID),ARRAY_A);
                        if(isset($replies) && is_array($replies))
                          if(is_array($replies[0]) && is_numeric($replies[0]['comment_id']))
                            $replystr= '<li><a class="tip reply_unit_comment meta_info" data-meta="'.$replies[0]['comment_id'].'" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';

                        if(!isset($replystr))
                          echo '<li><a class="tip reply_unit_comment" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';
                        else
                          echo $replystr;
                      ?>
                      <li><a class="tip instructor_reply_unit_comment" title="<?php _e('Request Instructor reply','vibe'); ?>"><i class="icon-forward-2"></i></a></li>
                    </ul>  
                    <?php
                  }
                ?>
                </div>
              </li>
              <?php
            
            }
            
          }else{
            echo 1;
          }
    die();
  }
  function wplms_all_notes_discussions(){
    $this->wplms_ajax_notes_discussion('all');
    die();
  }
  function wplms_all_public_discussions(){
    $this->wplms_ajax_notes_discussion('all_public');
    die();
  }
  function wplms_instructor_unit_notes(){
    $this->wplms_ajax_notes_discussion('unit_notes');
    die();
  }
  function wplms_instructor_unit_discussions(){
    $this->wplms_ajax_notes_discussion('unit_discussions');
    die();
  }
  function wplms_my_notes_private(){
    $this->wplms_ajax_notes_discussion('my_notes');
    die();
  }
  function wplms_my_notes_public(){
    $this->wplms_ajax_notes_discussion('my_discussion');
    die();
  }
  function wplms_ajax_notes_discussion($name){
    $args = array();
    $user_id = get_current_user_id();
    $number = vibe_get_option('loop_number');
    if(!is_numeric($number))
      $number = 5;
    switch($name){
      case 'all':
          $args = apply_filters('wplms_notes_dicussion_args',array(
            'number'              => $number,
            'post_status'         => 'publish',
            'post_type'           => 'unit',
            'status'              => 'approve',
          ));
      break;
      case 'all_public':
        $args = apply_filters('wplms_notes_dicussion_args',array(
            'number'              => $number,
            'post_status'         => 'publish',
            'post_type'           => 'unit',
            'status'              => 'approve',
            'type'                => 'public'
          ));
      break;
      case 'unit_notes':
        $user_id =get_current_user_id();
        $args = apply_filters('wplms_notes_dicussion_args',array(
            'number'              => $number,
            'post_status'         => 'publish',
            'post_type'           => 'unit',
            'status'              => 'approve',
            'post_author'         => $user_id,
            'type'                => 'note'
          ));
      break;
      case 'unit_discussions':
        $user_id =get_current_user_id();
        $args = apply_filters('wplms_notes_dicussion_args',array(
            'number'              => $number,
            'post_status'         => 'publish',
            'post_type'           => 'unit',
            'status'              => 'approve',
            'post_author'         => $user_id,
            'type'                => 'public'
          ));
      break;
      case 'my_notes':
        $user_id =get_current_user_id();
        $args = apply_filters('wplms_notes_dicussion_args',array(
            'number'              => $number,
            'post_status'         => 'publish',
            'post_type'           => 'unit',
            'status'              => 'approve',
            'type'                => 'note',
            'user_id'             => $user_id
          ));
        $args['user_id'] =$user_id;
      break;
      case 'my_discussion':
        $args = apply_filters('wplms_notes_dicussion_args',array(
            'number'              => $number,
            'post_status'         => 'publish',
            'post_type'           => 'unit',
            'status'              => 'approve',
            'type'                => 'public',
            'user_id'             => $user_id
          ));
        $args['user_id'] =$user_id;
      break;
    }

    ?>
    <div id="notes_query"><?php echo json_encode($args); ?></div>
      <div id="notes_discussions">
        <?php
          $comments_query = new WP_Comment_Query;
          $comments = $comments_query->query( $args );
          $this->comments_loop($comments);
          ?>
      </div>
    <?php
  }

  public function comments_loop($comments){
    // Comment Loop 

          if ( $comments ) {
            echo '<ul class="notes_list">';
            foreach ( $comments as $comment ) {   //print_r($comment);
              if($comment->comment_type != 'creply'){
              ?>
              <li class="loaded <?php echo $comment->comment_type.' '.(($comment->comment_parent)?'parent':''); ?>"><div class="note" data-id="<?php echo $comment->comment_ID; ?>">
                <div class="user-avatar">
                  <?php
                    $current_user_id = get_current_user_id();
                    $author_id = $comment->user_id;
                    echo get_avatar($author_id);
                  ?>
                </div>
                <div class="unit_comment_content">
                  <?php echo '<a href="'.bp_core_get_user_domain($author_id).'" class="unit_comment_author"> '.bp_core_get_user_displayname( $author_id) .'</a><span class="right"><span>'.__('UNIT','vibe').' : '.$comment->post_title.'</span><br /><i class="icon-clock"></i>&nbsp;'.human_time_diff(strtotime($comment->comment_date),current_time('timestamp')).'</span>'; ?>
                  <div class="note_content"><?php echo $comment->comment_content; ?></div>
                </div>
                <div class="note_actions">
                      <?php 
                        if($current_user_id == $author_id || current_user_can('edit_posts')){
                      ?>
                      <ul class="actions">
                          <li><a class="tip edit_unit_comment" title="<?php _e('Edit','vibe'); ?>"><i class="icon-pen-alt2"></i></a></li>
                          <?php
                            if($comment->comment_type == 'note'){
                          ?>
                          <li><a class="tip public_unit_comment" title="<?php _e('Make Public','vibe'); ?>"><i class="icon-fontawesome-webfont-3"></i></a></li>
                          <?php
                          }else{
                          ?>
                          <li><a class="tip private_unit_comment" title="<?php _e('Make Private','vibe'); ?>"><i class="icon-fontawesome-webfont-4"></i></a></li>
                          <?php
                          }
                          ?>
                          <?php
                            global $wpdb; $replystr ='';
                            $replies = $wpdb->get_results($wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_value = %d",$comment->comment_ID),ARRAY_A);
                            if(isset($replies) && is_array($replies))
                              if(is_array($replies[0]) && is_numeric($replies[0]['comment_id']))
                                $replystr= '<li><a class="tip reply_unit_comment meta_info" data-meta="'.$replies[0]['comment_id'].'" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';

                            if(!isset($replystr))
                              echo '<li><a class="tip reply_unit_comment" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';
                            else
                              echo $replystr;
                          ?>
                          <li><a class="tip instructor_reply_unit_comment" title="<?php _e('Request Instructor reply','vibe'); ?>"><i class="icon-forward-2"></i></a></li>
                          <li><a class="tip remove_unit_comment" title="<?php _e('Remove','vibe'); ?>"><i class="icon-cross"></i></a></li>
                      </ul>
                      <?php
                        }else{
                          ?>
                          <ul class="actions">
                          <?php
                            global $wpdb;
                            $replies = $wpdb->get_results($wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_value = %d",$comment->comment_ID),ARRAY_A);
                            if(isset($replies) && is_array($replies))
                              if(is_array($replies[0]) && is_numeric($replies[0]['comment_id']))
                                $replystr= '<li><a class="tip reply_unit_comment meta_info" data-meta="'.$replies[0]['comment_id'].'" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';

                            if(!isset($replystr))
                              echo '<li><a class="tip reply_unit_comment" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';
                            else
                              echo $replystr;
                          ?>
                            <li><a class="tip instructor_reply_unit_comment" title="<?php _e('Request Instructor reply','vibe'); ?>"><i class="icon-forward-2"></i></a></li>
                          </ul>  
                          <?php
                        }
                      ?>
                    </div>
                </li>
              <?php
              }
            }
            echo '</ul><div class="load-more"><a id="load_more_notes">'. __('Load More','vibe').'</a></div>';
          } else {
            echo '<div class="message"><p>'.__('No comments found','vibe').'</p></div>';
          }
          wp_nonce_field('security','hash');
          ?>
          <div class="comment-form" style="display:none">
              <?php
              echo get_avatar($user_id); echo ' <span>'.__('YOU','vibe').'</span>';
              ?>
              <article class="live-edit" data-model="article" data-id="1" data-url="/articles">
                  <div class="new_side_comment" data-editable="true" data-name="content" data-text-options="true">
                  <?php _e('Add your Comment','vibe'); ?>
                  </div>
              </article>
              <ul class="actions">
                  <li><a class="post_unit_comment tip" title="<?php _e('Post','vibe'); ?>"><i class="icon-fontawesome-webfont-4"></i></a></li>
                  <li><a class="remove_side_comment tip" title="<?php _e('Remove','vibe'); ?>"><i class="icon-cross"></i></a></li>
              </ul>
          </div>

          <?php
  }

  
  function wplms_get_unit_comment_count(){
    $unit_id= $_POST['unit_id'];
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($unit_id)){
       _e('Security check Failed. Contact Administrator.','vibe');
       die();
    }
    global $wpdb;
    $user_id = get_current_user_id();
    if(current_user_can('edit_posts')){
      $query =$wpdb->prepare("SELECT meta_value as id,count(meta_value) as count FROM {$wpdb->commentmeta} WHERE meta_key LIKE %s GROUP BY meta_value", 'unit'.$unit_id.'_%');
    }else{
      $query =$wpdb->prepare("SELECT meta_value as id,count(meta_value) as count FROM {$wpdb->commentmeta} WHERE (meta_key = %s OR meta_key LIKE %s) GROUP BY meta_value", 'unit'.$unit_id.'_'.$user_id,'unit'.$unit_id.'%public');
    }
    $results = $wpdb->get_results($query,ARRAY_A);
    echo json_encode($results);
    die();  
  }

  function wplms_post_unit_comment(){
    
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') ){
       _e('Security check Failed. Contact Administrator.','vibe');
       die();
    }

    $unit_id= $_POST['unit_id'];
    $course_id = $_POST['course_id'];
    $reply=$_POST['reply'];
    $content=$_POST['content'];
    $section=$_POST['section'];
    if(!is_user_logged_in() || !is_numeric($course_id) || !is_numeric($unit_id) || !is_numeric($reply)){
      _e('Not Allowed','vibe');
      die();
    }

    $user_id = get_current_user_id();
    $comment_data = array(
      'comment_post_ID'=>$unit_id,
      'comment_content' => $content,
      'comment_type' => 'note',
      'user_id' => $user_id,
      'comment_approved' => 1,
      );
    if(is_numeric($reply) && $reply){
      $comment_data['comment_parent'] = $reply;
      $comment_data['comment_type'] = 'creply';
    }

    $comment_id =wp_insert_comment($comment_data);
    if(is_numeric($comment_id)){
      if(is_numeric($reply) && $reply){
        add_comment_meta($comment_id,'reply_'.$unit_id.'_'.$section,$comment_data['comment_parent']);
      }else{
        add_comment_meta($comment_id,'unit'.$unit_id.'_'.$user_id,$section);
      }
      echo $comment_id;
      do_action('wplms_course_unit_comment',$unit_id,$user_id,$comment_id);
    }else
      _e('Unable to post','vibe');
    die();
  }

  function wplms_get_unit_section_comments(){
    $unit_id=$_POST['unit_id'];
    $section=$_POST['section'];
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($unit_id)){
       _e('Security check Failed. Contact Administrator.','vibe');
       die();
    }
    $num = $_POST['num'];
    if(!$num || !is_numeric($num) || $num  < 10)
      $num = 10;
    else
      $num= $num*2;

    $num = apply_filters('wplms_unit_comments_per_section',$num);
    global $wpdb;
    $user_id = get_current_user_id();

    if(current_user_can('edit_posts')){
      $query = $wpdb->prepare("
      SELECT rel.meta_value,comments.comment_ID,comments.comment_date, comments.user_id,comments.comment_content,comments.comment_type
      FROM {$wpdb->comments} as comments
      LEFT JOIN {$wpdb->commentmeta} AS rel ON comments.comment_ID = rel.comment_id
      WHERE   comments.comment_post_ID = %d
      AND   comments.comment_ID IN (SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key LIKE %s )
      ORDER BY comments.comment_date DESC
      LIMIT 0,%d
      ",$unit_id,'unit'.$unit_id.'_%',$num);
    }else{
      $query = $wpdb->prepare("
      SELECT rel.meta_value,comments.comment_ID,comments.comment_date, comments.user_id,comments.comment_content,comments.comment_type
      FROM {$wpdb->comments} as comments
      LEFT JOIN {$wpdb->commentmeta} AS rel ON comments.comment_ID = rel.comment_id
      WHERE   comments.comment_post_ID = %d
      AND   comments.comment_ID IN (SELECT comment_id FROM {$wpdb->commentmeta} WHERE (meta_key = %s OR meta_key LIKE %s) )
      ORDER BY comments.comment_date DESC
      LIMIT 0,%d
      ",$unit_id,'unit'.$unit_id.'_'.$user_id,'unit'.$unit_id.'%public',$num);
    }

    $query = apply_filters('wplms_unit_comments_query',$query,$unit_id);

    $results = $wpdb->get_results($query,ARRAY_A);
    if(isset($results) && is_array($results) ){
      $json_array = array();
      $i=0;
      foreach($results as $result){
          $avatar=get_avatar($result['user_id']);
          preg_match( '#src=["|\'](.+)["|\']#Uuis', $avatar, $matches );
          $seconds_span = time()-strtotime($result['comment_date']);

          $json_array[$i][$result['meta_value']]=array(
            'ID' => $result['comment_ID'],
            'content' => $result['comment_content'],
            'time'=> human_time_diff(strtotime($result['comment_date']),current_time('timestamp')),
            'type'=>$result['comment_type'],
            'author'=>Array(
              'user_id' => $result['user_id'],  
              'img'=> $matches[1],
              'name'=>bp_core_get_user_displayname($result['user_id']),
              'link'=>bp_core_get_user_domain($result['user_id'])
              ),
          );

          if(($result['user_id'] == $user_id) || (current_user_can('edit_posts'))){
            $json_array[$i][$result['meta_value']]['controls']=array(
              'edit_unit_comment'=>1,
              'instructor_reply_unit_comment'=>1,
            );
            if($result['comment_type'] == 'public'){
              $json_array[$i][$result['meta_value']]['controls']['private_unit_comment']=1;
            }else{
              $json_array[$i][$result['meta_value']]['controls']['public_unit_comment']=1;
            }
            $json_array[$i][$result['meta_value']]['controls']['reply_unit_comment']=1;
            $json_array[$i][$result['meta_value']]['controls']['popup_unit_comment']=1;
            $json_array[$i][$result['meta_value']]['controls']['remove_unit_comment']=1;
          }else{
            $json_array[$i][$result['meta_value']]['controls']=array(
              'reply_unit_comment'=>1,
              'instructor_reply_unit_comment'=>1, 
            );
          }
          global $wpdb;
          $replies = $wpdb->get_results($wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s and meta_value = %d",'reply_'.$unit_id.'_'.$_POST['section'],$result['comment_ID']),ARRAY_A);
          if(isset($replies) && is_array($replies)){
            if(is_array($replies[0]) && is_numeric($replies[0]['comment_id']) && $replies[0]['comment_id']){
              $json_array[$i][$result['meta_value']]['controls']['reply_unit_comment'] = $replies[0]['comment_id'];
            }
          }
          $i++;
        }
      $json_array=array_reverse($json_array);
      echo json_encode($json_array);
    }
    die();
  }

  function wplms_unit_edit_user_comment(){
    $id=$_POST['id'];
    $content=$_POST['content'];
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($id)){
       _e('Security check Failed. Contact Administrator.','vibe');
       die();
    }
    $commentarr = array();
    $commentarr['comment_ID'] = $id;
    $commentarr['comment_content'] = $content;
    if(current_user_can('edit_posts')){
        wp_update_comment( $commentarr );
    }else{
      $user_id = get_current_user_id();
      $comment = get_comment($id,ARRAY_A);
      if($comment['user_id'] == $user_id){
        wp_update_comment( $commentarr );
      }
    }
    die();
  }

  function wplms_unit_remove_user_comment(){
    $id=$_POST['id'];
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($id)){
       _e('Security check Failed. Contact Administrator.','vibe');
       die();
    }

    if(current_user_can('edit_posts')){
      wp_delete_comment($id);
      global $wpdb;
      $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->commentmeta} WHERE comment_id = %d",$id));
    }else{
      $user_id = get_current_user_id();
      $comment = get_comment($id,ARRAY_A);
      if($comment['user_id'] == $user_id){
        wp_delete_comment($id);
        global $wpdb;
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->commentmeta} WHERE comment_id = %d AND meta_key LIKE %s",$id,'%unit%'.$user_id.'%'));
      }
    }
    die();
  }

  function wplms_unit_private_user_comment(){
    $id=$_POST['id'];
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($id)){
       _e('Security check Failed. Contact Administrator.','vibe');
       die();
    }

    if(current_user_can('edit_posts')){
      global $wpdb;
      $wpdb->query($wpdb->prepare("UPDATE {$wpdb->comments} SET comment_type=%s WHERE comment_ID=%d",'note',$id));
      $wpdb->query($wpdb->prepare("UPDATE {$wpdb->commentmeta} SET meta_key=replace(meta_key,%s,%s) WHERE comment_ID=%d",'_public','',$id));
    }else{
      $user_id = get_current_user_id();
      $comment = get_comment($id,ARRAY_A);
      if($comment['user_id'] == $user_id){
        global $wpdb;
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->comments} SET comment_type=%s WHERE comment_ID=%d",'public',$id));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->commentmeta} SET meta_key=replace(meta_key,%s,%s) WHERE comment_ID=%d",'_public','',$id));
      }
    }
    die();
  }

  function wplms_instructor_reply_user_comment(){
    $id=$_POST['id'];
    $course_id=$_POST['course_id'];

    $message=$_POST['message'];
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($id)){
       _e('Security check Failed. Contact Administrator.','vibe');
       die();
    }
    if(bp_is_active('messages')){
      $user_id = get_current_user_id();
      $instructor_ids = apply_filters('wplms_course_instructors',get_post_field('post_author',$id),$id);

      if(!is_array($instructor_ids))
        $instructor_ids=array($instructor_ids);

      $message .=' <a href="'.get_permalink($id).'?id='.$course_id.'">'.get_the_title($id).'</a>';
      foreach($instructor_ids as $instructor_id){

        messages_new_message( array('sender_id' => $user_id, 'subject' => sprintf(__('Instructor reply requested for unit %s paragraph %s','vibe'),get_the_title($id),$_POST['section']), 'content' => $message,   'recipients' => $instructor_id ) );
        echo 'balle';
      }
    }
    die();
  }
 
  function wplms_unit_public_user_comment(){
    $id=$_POST['id'];
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($id)){
       _e('Security check Failed. Contact Administrator.','vibe');
       die();
    }
 
    if(current_user_can('edit_posts')){
      global $wpdb;
      $wpdb->query($wpdb->prepare("UPDATE {$wpdb->comments} SET comment_type=%s WHERE comment_ID=%d",'public',$id));
      $wpdb->query($wpdb->prepare("UPDATE {$wpdb->commentmeta} SET meta_key=CONCAT(meta_key,%s) WHERE comment_ID=%d",'_public',$id));
    }else{
      $user_id = get_current_user_id();
      $comment = get_comment($id,ARRAY_A);
      if($comment['user_id'] == $user_id){
        global $wpdb;
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->comments} SET comment_type=%s WHERE comment_ID=%d",'public',$id));
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->commentmeta} SET meta_key=CONCAT(meta_key,%s) WHERE comment_ID=%d",'_public',$id));
      }
    }
    die();
  }

  function wplms_get_user_reply(){
    $id = $_POST['id'];
    if ( !isset($_POST['security']) || !wp_verify_nonce($_POST['security'],'security') || !is_numeric($id)){
       _e('Security check Failed. Contact Administrator.','vibe');
       die();
    }

    $comment= get_comment($id);
    ?>
      <li class="loaded <?php echo $comment->comment_type.' '.(($comment->comment_parent)?'parent':''); ?>">
      <div class="<?php echo $comment->comment_type; ?>" data-id="<?php echo $comment->comment_ID; ?>">
        <?php
        $author_id = $comment->user_id;
        $current_user_id = get_current_user_id();
        echo get_avatar($author_id).' <a href="'.bp_core_get_user_domain($author_id).'" class="unit_comment_author"> '.bp_core_get_user_displayname( $author_id) .'</a>';
        ?>
        <div class="unit_comment_content"><?php echo $comment->comment_content; ?></div>
        <?php 
          if($current_user_id == $author_id || current_user_can('edit_posts')){
        ?>
        <ul class="actions">
            <li><a class="tip edit_unit_comment" title="<?php _e('Edit','vibe'); ?>"><i class="icon-pen-alt2"></i></a></li>
            <?php
              if($comment->comment_type == 'note'){
            ?>
            <li><a class="tip public_unit_comment" title="<?php _e('Make Public','vibe'); ?>"><i class="icon-fontawesome-webfont-3"></i></a></li>
            <?php
            }else{
            ?>
            <li><a class="tip private_unit_comment" title="<?php _e('Make Private','vibe'); ?>"><i class="icon-fontawesome-webfont-4"></i></a></li>
            <?php
            }
            ?>
            <?php
              global $wpdb;
              $replies = $wpdb->get_results($wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_value = %d",$comment->comment_ID),ARRAY_A);
              if(isset($replies) && is_array($replies)){
                if(is_array($replies[0]) && is_numeric($replies[0]['comment_id'])){
                  $replystr= '<li><a class="tip reply_unit_comment meta_info" data-meta="'.$replies[0]['comment_id'].'" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';
                }
              }  
              if(!isset($replystr))
                echo '<li><a class="tip reply_unit_comment" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';
              else
                echo $replystr;
            ?>
            <li><a class="tip instructor_reply_unit_comment" title="<?php _e('Request Instructor reply','vibe'); ?>"><i class="icon-forward-2"></i></a></li>
            <li><a class="tip remove_unit_comment" title="<?php _e('Remove','vibe'); ?>"><i class="icon-cross"></i></a></li>
        </ul>
        <?php
          }else{
            ?>
            <ul class="actions">
              <?php
                global $wpdb;
                $replies = $wpdb->get_results($wpdb->prepare("SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_value = %d",$comment->comment_ID),ARRAY_A);
                if(isset($replies) && is_array($replies)){
                  if(is_array($replies[0]) && is_numeric($replies[0]['comment_id'])){
                    $replystr= '<li><a class="tip reply_unit_comment meta_info" data-meta="'.$replies[0]['comment_id'].'" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';
                  }
                }

                if(!isset($replystr))
                  echo '<li><a class="tip reply_unit_comment" title="'.__('Reply','vibe').'"><i class="icon-curved-arrow"></i></a></li>';
                else
                  echo $replystr;
              ?>
              <li><a class="tip instructor_reply_unit_comment" title="<?php _e('Request Instructor reply','vibe'); ?>"><i class="icon-forward-2"></i></a></li>
            </ul>  
            <?php
          }
        ?>
        </div>
      </li>
      <?php
    die();
  }
}

new vibe_notes_discussions();

/* ==== END NOTES & DISCUSSION =======*/

<?php

if ( ! defined( 'ABSPATH' ) ) exit;
global $post;

if($post->comment_status == 'open'){

  $user_id = get_current_user_id();
  if(is_user_logged_in()):

    global $post;

    $user_id = get_current_user_id();
    

    if(bp_course_is_member($post->ID,$user_id)){
     
      
    $answers=get_comments(array(
      'post_id' => $post->ID,
      'status' => 'approve',
      'user_id' => $user_id
      ));
    if(isset($answers) && is_array($answers) && count($answers)){
        $answer = end($answers);
        $content = $answer->comment_content;
    }else{ 
        $content='';
    }

    $fields =  array(
        'author' => '<p><label class="comment-form-author clearfix">'.__( 'Name','vibe' ) . ( $req ? '<span class="required">*</span>' : '' ) . '</label> ' . '<input class="form_field" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" /></p>',
        'email'  => '<p><label class="comment-form-email clearfix">'.__( 'Email','vibe' ) .  ( $req ? '<span class="required">*</span>' : '' ) . '</label> ' .          '<input id="email" class="form_field" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '"/></p>',
        'url'   => '<p><label class="comment-form-url clearfix">'. __( 'Website','vibe' ) . '</label>' . '<input id="url" name="url" type="text" class="form_field" value="' . esc_attr( $commenter['comment_author_url'] ) . '"/></p>',
         );
        
   $comment_field='<p>' . '<textarea id="comment" name="comment" class="form_field" rows="8" ">'.$content.'</textarea></p>';

   if ( isset($_POST['review']) && wp_verify_nonce($_POST['review'],get_the_ID()) ):

    comment_form(array('fields'=>$fields,'comment_field'=>$comment_field,'label_submit' => __('Post Review','vibe'),'title_reply'=> '<span>'.__('Write a Review','vibe').'</span>','logged_in_as'=>'','comment_notes_after'=>'' ));
    echo '<div id="comment-status" data-quesid="'.$post->ID.'"></div><script>jQuery(document).ready(function($){$("#submit").hide();$("#comment").on("keyup",function(){if($("#comment").val().length){$("#submit").show(100);}else{$("#submit").hide(100);}});});</script>';
    endif;
  }
    ?>
  <?php
    endif;

  if(!isset($_POST['review'])){

  ?>
    <h3 class="heading"><span><?php _e('Course Reviews','vibe'); ?></span></h3>
    <div class="review_breakup">
      <?php
      
      $average_rating = get_post_meta(get_the_ID(),'average_rating',true);
      $count = get_post_meta(get_the_ID(),'rating_count',true);
      $breakup = wplms_get_rating_breakup();
      $ratings = array(1=>0,2=>0,3=>0,4=>0,5=>0);
      foreach($breakup as $value){
         $ratings[$value->val] = intval($value->count);
      }
      ?>
      <div class="col-md-6">
        <div class="rating_snapshot">
          <h2><?php echo (($average_rating)?$average_rating:__('N.A','vibe')); ?></h2>
          <?php
          echo '<div class="modern-star-rating">';
                  if(function_exists('bp_course_display_rating')){
                     echo bp_course_display_rating($average_rating);
                  }else{
                    for($i=1;$i<=5;$i++){
                      if($average_rating >= 1){
                        echo '<span class="fa fa-star"></span>';
                      }elseif(($average_rating < 1 ) && ($average_rating >= 0.3 ) ){
                        echo '<span class="fa fa-star-half-o"></span>';
                      }else{
                        echo '<span class="fa fa-star-o"></span>';
                      }
                      $average_rating--;
                    }
                }
              echo '</div>';
              echo '<span>'.$count.' '.__('ratings','vibe').'</span>';
          ?>
        </div>
      </div>
      <div class="col-md-6">
        <ul class="rating_breakup">
        <?php
          $ratings=array_reverse($ratings,true);
          foreach($ratings as $k => $rating){
            if(empty($count)){$count=1;}
            echo '<li><span>'.$k.' '.__('stars','vibe').'</span><strong><span style="width:'.(100*($rating/$count)).'%">'.$rating.'</span></strong></li>';
          }
        ?>
        </ul>
      </div>
    </div>
    <div class="show_course_reviews">
        <?php
        if (get_comments_number()==0) {
          echo '<div id="message" class="notice"><p>';_e('No Reviews found for this course.','vibe');echo '</p></div>';
        }else{
        ?>

        <ol class="reviewlist commentlist"> 
        <?php 
              wp_list_comments(array(
                 'type'        =>'comment',
                 'avatar_size' =>120,
                 'callback'    => 'wplms_course_reviews'
                 )
               ); 
              paginate_comments_links( array('prev_text' => '&laquo;', 'next_text' => '&raquo;') )
          ?>  
        </ol> 
    <?php
      }
    ?>
    </div>
  <?php
    $course_review = get_post_meta(get_the_ID(),'vibe_course_review',true);
    if(is_user_logged_in() && vibe_validate($course_review) && bp_course_is_member($post->ID,$user_id)){
      comment_form(array('fields'=>$fields,'comment_field'=>$comment_field,'label_submit' => __('Post Review','vibe'),'title_reply'=> '<span>'.__('Post Review','vibe').'</span>','logged_in_as'=>'','comment_notes_after'=>'' ));
    }
  }


}else{
  if(isset($_POST['review']))
    echo '<div class="message">'.__('Reviews disabled for course','vibe').'</div>';
}
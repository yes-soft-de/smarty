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

class WPLMS_Course_Reviews{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new WPLMS_Course_Reviews();

        return self::$instance;
    }

    private function __construct(){
      add_action('comment_post',array($this,'calculate_ratings'),99,1);
      add_action('edit_comment',array($this,'calculate_ratings'),99,1);
      add_action('delete_comment',array($this,'calculate_ratings'),99,1);
      
      add_action('transition_comment_status',array($this,'comment_approved'),99,3);
      add_action( 'comment_form_logged_in_after',array($this, 'additional_fields' ),10,2);
      add_action( 'comment_form_after_fields',array($this, 'additional_fields' ));
      add_action( 'comment_post', array($this,'save_comment_meta_data' ));

      add_action( 'add_meta_boxes_comment',array($this, 'extend_comment_add_meta_box' ));
      add_action( 'edit_comment', array($this,'extend_comment_edit_metafields') );

      add_action('pre_comment_on_post',array($this,'update_course_review'),10,1);
    }


    function additional_fields ($commenter = NULL,$username = NULL) {
      global $post;
      $review = $title = $rating = '';
      if(!empty($username)){
        global $wpdb;
        $user_id = $wpdb->get_var("SELECT ID FROM {$wpdb->users} WHERE display_name ='$username'");
        
        $reviews=get_comments(array(
          'post_id' => $post->ID,
          'status' => 'approve',
          'number'=> 1,
          'user_id' => $user_id
          ));
        if(!empty($reviews)){
            $review = end($reviews);
            $title =  get_comment_meta( $review->comment_ID, 'review_title',true);
            $rating = get_comment_meta( $review->comment_ID, 'review_rating',true);
        }
        
        
      }
      global $post;
      if($post->post_type == 'course' && (function_exists('bp_current_component') && bp_current_component() == 'course')){
      
          echo '<p class="comment-form-title">'.
          '<label for="review_title">' . __( 'Review Title','vibe' ) . '</label>'.
          '<input id="review_title" name="review_title" class="form_field" type="text" size="30" value="'.$title.'" tabindex="5" /></p>';

          echo '<p class="comment-form-rating">'.
          '<label for="rating">'. __('Review Rating','vibe') . '<span class="required">*</span></label>
          <span class="commentratingbox">';
          
          for( $i=1; $i <= 5; $i++ )
          echo '<input type="radio" name="review_rating" class="rating" '.(($rating == $i)?'checked':'').' value="'. $i .'"/>';

          echo'</span></p>';
          if(!empty($review) && !empty($review->comment_ID))
            echo '<input type="hidden" name="comment_ID" value="'.$review->comment_ID.'" />';
          ?>
          <script>
          jQuery(document).ready(function($){
              $('#submit').on('click',function(event){
                  var $this = $(this);
                  if($('.rating:checked').length){
                    if($('#comment').val().length){ 
                      $('#commentform').submit();
                    }else{
                      event.preventDefault();
                      $this.after('<div class="message" style="margin-top:20px;"><?php _e("Please enter review content !","vibe"); ?></div>');
                        setTimeout(function(){ 
                            $('.message').fadeOut(200).remove();
                        }, 3000);
                    }
                  }else{
                    event.preventDefault();
                    $this.after('<div class="message" style="margin-top:20px;"><?php _e("Please add review rating !","vibe"); ?></div>');
                      setTimeout(function(){ 
                      $('.message').fadeOut(200).remove();
                      }, 3000);
                  }
              });
          });
          </script>
          <?php
      }

    }

    function save_comment_meta_data( $comment_id ) {

      if(get_post_type($_POST['comment_post_ID']) != 'course')    
                      return;

        if ( ( isset( $_POST['review_title'] ) ) && ( $_POST['review_title'] != '') ){
          $title = wp_filter_nohtml_kses($_POST['review_title']);
          add_comment_meta( $comment_id, 'review_title', $title );
        }

        if ( ( isset( $_POST['review_rating'] ) ) && ( $_POST['review_rating'] != '') ){
          $rating = wp_filter_nohtml_kses($_POST['review_rating']);
          add_comment_meta( $comment_id, 'review_rating', $rating );
        }
        $course_id = $_POST['comment_post_ID'];
        do_action('wplms_course_review',$course_id,$rating,$title);
    }


    // Add the filter to check if the comment meta data has been filled or not



    //Add an edit option in comment edit screen  


    function extend_comment_add_meta_box() {
        add_meta_box( 'title', __( 'Review Details','vibe' ), array($this,'extend_comment_meta_box'), 'comment', 'normal', 'high' );
    }
     
    function extend_comment_meta_box ( $comment ) {

        if(get_post_type($comment->comment_post_ID) != 'course')  
          return;

        $title = get_comment_meta( $comment->comment_ID, 'review_title', true );
        $rating = get_comment_meta( $comment->comment_ID, 'review_rating', true );
        wp_nonce_field( 'extend_comment_update', 'extend_comment_update', false );
        ?>
        <p>
            <label for="title"><?php _e( 'Review Title','vibe' ); ?></label>
            <input type="text" name="review_title" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
        </p>
        <p>
            <label for="rating"><?php _e( 'Rating ','vibe' ); ?></label>
          <span class="commentratingbox">
          <?php for( $i=1; $i <= 5; $i++ ) {
            echo '<span class="commentrating"><input type="radio" name="review_rating" id="rating" value="'. $i .'"';
            if ( $rating == $i ) echo ' checked="checked"';
            echo ' />'. $i .' </span>'; 
            }
          ?>
          </span>
        </p>
        <?php
    }

    // Update comment meta data from comment edit screen 


    function extend_comment_edit_metafields( $comment_id ) {

      if(get_post_type($_POST['comment_post_ID']) != 'course')    
                    return;

      if( ! isset( $_POST['extend_comment_update'] ) || ! wp_verify_nonce( $_POST['extend_comment_update'], 'extend_comment_update' ) ) return;
     
      if ( ( isset( $_POST['review_title'] ) ) && ( $_POST['review_title'] != '') ):
      $title = wp_filter_nohtml_kses($_POST['review_title']);
      update_comment_meta( $comment_id, 'review_title', $title );
      else :
      delete_comment_meta( $comment_id, 'review_title');
      endif;

      if ( ( isset( $_POST['review_rating'] ) ) && ( $_POST['review_rating'] != '') ):
      $rating = wp_filter_nohtml_kses($_POST['review_rating']);
      update_comment_meta( $comment_id, 'review_rating', $rating );
      else :
      delete_comment_meta( $comment_id, 'review_rating');
      endif;
      
    }

    // Add the comment meta (saved earlier) to the comment text 
    // You can also output the comment meta values directly in comments template  

    function comment_approved($new_status, $old_status, $comment_object) {
      if($old_status != $new_status) {
          if($new_status == 'approved') {
             if(get_post_type($comment_object->comment_post_ID) == 'course'){
                  if(function_exists('bp_course_get_course_reviews')){
                      $calculate_reviews=bp_course_get_course_reviews('id='.$comment_object->comment_post_ID);
                  }
              }
          }
      }
  }

    function calculate_ratings($comment_id) {
      $comment_object =get_comment($comment_id);
      if(get_post_type($comment_object->comment_post_ID) == 'course'){
         
          if(function_exists('bp_course_get_course_reviews')){
              $calculate_reviews=bp_course_get_course_reviews('id='.$comment_object->comment_post_ID);
          }
      }
    }


    function update_course_review($comment_post_ID){
        if(!empty($_POST['comment_ID'])){
            if(get_post_type($comment_post_ID) == 'course'){

              $args = array(
                'comment_ID'=>$_POST['comment_ID'],
                'comment_content'=>$_POST['comment']
                );
              
              wp_update_comment($args);
              
              if ( !empty( $_POST['review_title'] ) ){
                $title = wp_filter_nohtml_kses($_POST['review_title']);
                update_comment_meta( $_POST['comment_ID'], 'review_title', $title );     
              }
              
              if ( !empty( $_POST['review_rating'] )){

                  $rating = wp_filter_nohtml_kses($_POST['review_rating']);
                  update_comment_meta( $_POST['comment_ID'], 'review_rating', $rating );
                  $reviews = bp_course_get_course_reviews('id='.$comment_post_ID);
              }

              wp_safe_redirect( get_permalink($comment_post_ID));
              exit;
            }
        }
    }
}

WPLMS_Course_Reviews::init();
// COMMENTS RATING


// DISPLAY COURSE REVIEWS

function wplms_course_reviews($comment, $args, $depth) {
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
               <?php
              $commenttitle = get_comment_meta( $comment->comment_ID, 'review_title', true );
              $commentrating = get_comment_meta( $comment->comment_ID, 'review_rating', true );
              echo '<h3>'.$commenttitle.'</h3>';
              echo '<div class="course-star-rating">';
              if(function_exists('bp_course_display_rating')){
                 echo bp_course_display_rating($commentrating);
              }else{
                for($i=1;$i<=5;$i++){
                  if($commentrating >= 1){
                    echo '<span class="fill"></span>';
                  }elseif(($commentrating < 1 ) && ($commentrating >= 0.3 ) ){
                    echo '<span class="half"></span>';
                  }else{
                    echo '<span></span>';
                  }
                  $commentrating--;
                }
              }
              echo '</div>';
              comment_text(); ?>
               </div>
            </div> 
         </div>
       </div>
     </li>
     <?php
   
}


function wplms_get_rating_breakup($id = null){
      if(empty($id))
        $id = get_the_ID();

      global $wpdb;
      $breakup = $wpdb->get_results($wpdb->prepare("SELECT meta_value as val,count(meta_value) as count FROM {$wpdb->commentmeta} WHERE meta_key = %s AND comment_id IN (SELECT comment_ID FROM {$wpdb->comments} WHERE comment_post_ID = %d AND comment_approved = 1) GROUP BY meta_value LIMIT 0,999",'review_rating',$id));

      return $breakup;
    }
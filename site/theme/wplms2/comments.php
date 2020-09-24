<?php 
global $post; 
if ( ! defined( 'ABSPATH' ) ) exit;
if($post->comment_status != 'closed'):
?>
<div id="comments">
    <a href="<?php echo get_post_comments_feed_link(' '); ?>" class="comments_rss"><i class="icon-rss-1"></i></a>
    <h3 class="comments-title"><?php comments_number('0','1','%'); echo __(' responses on "','vibe'); echo $post->post_title.'"'; ?></h3>
   		<ol class="commentlist"> 
   		<?php 
            $wplms = WPLMS_Init::init();
            
          
            wp_list_comments(array(
              'type'        =>'comment',
              'avatar_size' =>120,
              'callback'    => array($wplms,'better_comments')
              ));  
            paginate_comments_links( array('prev_text' => '&laquo;', 'next_text' => '&raquo;') );
        ?>	
   		</ol>	
      <?php
                        
$fields =  array(
        'author' => '<p>' . '<input class="form_field" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" placeholder="'.__( 'Name','vibe' ) . ( $req ? '*' : '' ) . '"/></p>',
        'email'  => '<p>' .          '<input id="email" class="form_field" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" placeholder="'.__( 'Email','vibe' ) .  ( $req ? '*' : '' ) . '"/></p>',
        'url'   => '<p>' . '<input id="url" name="url" type="text" class="form_field" value="' . esc_attr( $commenter['comment_author_url'] ) . '" placeholder="'. __( 'Website','vibe' ) . '"/></p>',
         );

$comment_field='<p>' . '<textarea id="comment" name="comment" class="form_field" rows="8" " placeholder="'. __( 'Comment','vibe' ) . '"></textarea></p>';

comment_form(array('fields'=>$fields,'comment_field'=>$comment_field,'title_reply'=> '<span>'.__('Leave a Message','vibe').'</span>'));
                ?>
</div>
<?php
endif;

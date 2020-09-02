<?php

$course_id = get_the_id();
$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$num = get_option('posts_per_page');
$args = array(
	'post_type' => 'news',
	'paged' => $paged,
	'post_per_page'=> $num,
  'post_status' => 'publish',
	'meta_query'=> array(
		array(
            'meta_key' => 'vibe_news_course',
            'compare' => '=',
            'value' => $course_id,
            'type' => 'numeric'
            ),
          )
	);
$news = new WP_Query($args);
global $wp_query;

echo '<h3 class="heading">'.__('Course News','vibe').'</h3>';
if(current_user_can('manage_options')){
  $key = 1;
}else if(current_user_can('edit_posts')){
  $user_id = get_current_user_id();
  $instructors = array();
  if(function_exists('get_coauthors')){
    $coauthors = get_coauthors($course_id);
    if(isset($coauthors)){
      $i = 1;
      foreach($coauthors as $k => $inst){
        $instructors[$i] = $inst->ID;
        $i++;
      }
    }
  }else{
    $instructors[] = get_the_author_meta( 'ID' );
  }
  $key = array_search($user_id,$instructors);
  if(in_array($user_id, $instructors)) $key = 1;
}else{
  $key = 0;
}

if($key){
  echo '<a class="create_news_front_end button primary-button" data-text="'.__('Cancel','vibe').'">'.__('Add News','vibe').'</a>';
  ?>
  <div id="create_news" class="hide" data-id="<?php echo $course_id; ?>">
    <div class="container-fluid">
      <div class="row">
        <ul>
          <li>
            <label><?php echo __('News Title','vibe'); ?></label>
            <input type="text" class="news_title form_field" placeholder="<?php echo __('Title','vibe'); ?>" />
          </li>
          <li>
            <label><?php echo __('News Sub-Title','vibe'); ?></label>
            <textarea class="news_sub_title form_field" name="vibe_subtitle" id="vibe_subtitle" placeholder="<?php echo __('Sub Title','vibe'); ?>"></textarea>
          </li>
          <li>
            <label><?php echo __('News Format','vibe'); ?></label>
            <select class="news_format form_field">
              <option value="post-format-0" class="post-format-standard"><?php echo __('Standard','vibe'); ?></option>
              <option value="post-format-aside" class="post-format-aside"><?php echo __('Aside','vibe'); ?></option>
              <option value="post-format-image" class="post-format-image"><?php echo __('Image','vibe'); ?></option>
              <option value="post-format-quote" class="post-format-quote"><?php echo __('Quote','vibe'); ?></option>
              <option value="post-format-status" class="post-format-status"><?php echo __('Status','vibe'); ?></option>
              <option value="post-format-video" class="post-format-video"><?php echo __('Video','vibe'); ?></option>
              <option value="post-format-audio" class="post-format-audio"><?php echo __('Audio','vibe'); ?></option>
              <option value="post-format-chat" class="post-format-chat"><?php echo __('Chat','vibe'); ?></option>
              <option value="post-format-gallery" class="post-format-gallery"><?php echo __('Gallery','vibe'); ?></option>
            </select>
          </li>
          <li>
            <label><?php echo __('News Content','vibe'); ?></label>
            <?php 
              wp_editor('','news_content',array('editor_class'=>'news_content')); 
            ?>
          </li>
        </ul>
        <a id="save_news_front_end" class="button primary-button" data-id=""><?php echo __('Add News','vibe'); ?></a>
        <a class="cancel_news_front_end button primary-button"><?php echo __('Cancel','vibe'); ?></a>
        <?php wp_nonce_field('front_end_news_vibe_'.$course_id,'news_security'); ?>
      </div>
    </div>
  </div>
  <?php
}

if($news->have_posts()){
	echo '<ul>';
  $wp_query=$news;
	while($news->have_posts()){
		$news->the_post();
		$format=get_post_format(get_the_ID());
          if(!isset($format) || !$format)
            $format = 'standard';

          echo '<li>';
          echo '<div class="'.$format.'-block news"><span class="right">'.sprintf('%02d', get_the_time('j')).' '.get_the_time('M').'\''.get_the_time('y').'</span>
                  <h4><a href="'.get_permalink().'">'.get_the_title().'</a></h4>';
            echo '<div class="news_thumb"><a href="'.get_permalink().'">'.get_the_post_thumbnail().'</a></div>';
                  the_excerpt();
            if($key){
              echo '<a class="edit_news_front_end link" data-id="'.$course_id.'" data-news="'.get_the_ID().'">'.__('Edit','vibe').'</a>';
              echo '<a class="delete_news_front_end link" data-id="'.$course_id.'" data-news="'.get_the_ID().'" data-text="'.__('Are You sure, you want to delete this news','vibe').'">'.__('Delete','vibe').'</a>';
            }
            echo '<a href="'.get_permalink().'" class="right link">'.__('Read More','vibe').'</a><ul class="tags">'.get_the_term_list(get_the_ID(),'news-tag','<li>','</li><li>','</li>').'</ul>
            </div></li>';
	}
	echo '</ul>';
   pagination();
}else{
	echo '<div class="message error">'.__('No news available for Course','vibe').'</div>';
}
wp_reset_postdata();
wp_reset_query();
?>
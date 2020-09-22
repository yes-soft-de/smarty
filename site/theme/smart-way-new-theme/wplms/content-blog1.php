<?php
/**
 * BLOG 1 Style Content Block
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>
<div class="blogpost_style1 <?php echo get_post_format(); ?>">
    <?php if(has_post_thumbnail(get_the_ID())){ ?>
         <div class="featured">
            <a href="<?php echo get_permalink() ?>"><?php echo get_the_post_thumbnail(get_the_ID(),'full'); ?></a>
        </div>
    <?php } ?>
       
        <div class="excerpt">
            <h3><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></h3>
            <div class="blogpost_meta"><?php 
            echo get_the_time('M j,y'); 
            echo get_the_category_list('',''); 
            echo sprintf(_x('by %s','by post author','vibe'),'<a href="'.get_author_posts_url( get_the_author_meta( 'ID' ) ).'">'.get_the_author_meta( 'display_name' ).'</a>'); ?></div>
            <p><?php echo get_the_excerpt(); ?></p>
            <a href="<?php echo get_permalink(); ?>" class="link"><?php echo __('Read More','vibe'); ?></a>
        </div>
</div>
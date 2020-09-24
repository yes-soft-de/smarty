<?php
/**
 * BLOG 3 Style Content Block
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>
<div class="blogpost_style3 <?php echo get_post_format(); ?>">
    <div class="row">
         <div class="featured col-md-6">
            <a href="<?php echo get_permalink() ?>">
            <?php if(has_post_thumbnail(get_the_ID())){ ?>
                <?php echo get_the_post_thumbnail(get_the_ID(),'medium'); ?>
            <?php }else{
                $image = vibe_get_option('default_course_avatar');
                ?>
                    <img src="<?php echo $image; ?>" />
                <?php
                } 
            $name = get_the_author_meta( 'display_name' );
            ?>
            </a>
        </div>
        <div class="excerpt col-md-6">
            <?php echo get_the_category_list(); ?>
            <h3><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></h3>
            <p><?php echo get_the_excerpt(); ?><a href="<?php echo get_permalink(); ?>" class="link"><?php echo __('Read More','vibe'); ?></a></p>
            <?php
            echo '<a href="'.get_author_posts_url( get_the_author_meta( 'ID' ) ).'" 
                title="'.$name.'" class="blogpost_author">'.((function_exists('bp_core_fetch_avatar'))?bp_core_fetch_avatar(array(
                    'item_id' => get_the_author_meta( 'ID' ),
                    'object'  => 'user'
                )):'').'<strong>'.$name.'<span class="blogpost_style3_date">'.get_the_time(get_option( 'date_format' )).'</span></strong></a>';
            ?>
        </div>
    </div>
</div>
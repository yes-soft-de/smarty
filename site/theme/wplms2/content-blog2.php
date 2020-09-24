<?php
/**
 * BLOG 2 Style Content Block
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="blogpost_style2 <?php echo get_post_format(); ?>">
    <?php echo get_the_category_list('',''); ?>
    <h3><a href="<?php echo get_permalink(); ?>"><?php echo get_the_title(); ?></a></h3>
    <span class="blogpost_style2_date"><?php echo get_the_time('M j,y'); ?></span>
    <?php if(has_post_thumbnail(get_the_ID())){ ?>
         <div class="featured">
            <a href="<?php echo get_permalink() ?>"><?php echo get_the_post_thumbnail(get_the_ID(),'full'); ?></a>
            <?php
            $name = get_the_author_meta( 'display_name' );
            echo '<a href="'.get_author_posts_url( get_the_author_meta( 'ID' ) ).'" 
            title="'.$name.'" class="blogpost_author">'.((function_exists('bp_core_fetch_avatar'))?bp_core_fetch_avatar(array(
                    'item_id' => get_the_author_meta( 'ID' ),
                    'object'  => 'user'
                )):$name).'</a>';
            ?>
        </div>
    <?php } ?>
       
        <div class="excerpt">
            <p><?php echo get_the_excerpt(); ?></p>
            <a href="<?php echo get_permalink(); ?>" class="link"><?php echo __('Read More','vibe'); ?></a>
        </div>
</div>
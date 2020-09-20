<?php

if ( !defined( 'ABSPATH' ) ) exit;

get_header(vibe_get_header());

if ( have_posts() ) : while ( have_posts() ) : the_post();


$title=get_post_meta(get_the_ID(),'vibe_title',true);

if(!isset($title) || !$title || (vibe_validate($title))){

?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="pagetitle">
                    <?php 
                        $breadcrumbs=get_post_meta(get_the_ID(),'vibe_breadcrumbs',true);
                        if(!isset($breadcrumbs) || !$breadcrumbs || vibe_validate($breadcrumbs)){
                            vibe_breadcrumbs();
                        }   
                    ?>
                    <h1><?php the_title(); ?></h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
}

?>
<section id="content">
    <div class="<?php echo vibe_get_container(); ?>">
        
        <div class="row">
            <?php
                $template = get_post_meta(get_the_ID(),'vibe_template',true);
                if($template == 'right'){
                    echo '<div class="col-md-9 col-sm-8 right">';
                }else if($template == 'full'){
                    echo '<div class="col-md-12">';
                }else{
                    echo '<div class="col-md-9 col-sm-8 ">';
                }

            ?>
                <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="content">
                    <?php if(has_post_thumbnail()){ ?>
                    <div class="featured">
                        <?php the_post_thumbnail(get_the_ID(),'full'); ?>
                    </div>
                    <?php
                    }
                    the_content();
                     ?>
                     <div class="tags">
                    <?php echo '<div class="indate"><i class="icon-clock"></i> ';the_modified_date();echo '</div>';the_tags('<ul><li>','</li><li>','</li></ul>'); ?>
                    <?php wp_link_pages('before=<div class="page-links"><ul>&link_before=<li>&link_after=</li>&after=</ul></div>'); ?>
                        <div class="social_sharing">
                            <?php 
                             if(function_exists('social_sharing'))
                                echo social_sharing(); 
                            ?>   
                        </div>
                    </div>
                </div>
                <?php
                        $prenex=get_post_meta(get_the_ID(),'vibe_prev_next',true);
                        if(vibe_validate($prenex)){
                    ?>
                    <div class="prev_next_links">
                        <ul class="prev_next">
                            <?php 
                            $prev_post = get_previous_post();
                            $next_post = get_next_post();
                            echo '<li>';
                            if(!empty($prev_post))
                            echo '<a href="'.get_permalink($prev_post->ID).'" class="prev"><strong>'.get_the_post_thumbnail($prev_post->ID,'thumbnail').'<span>'.$prev_post->post_title.'</span></strong></a>';

                            echo '<li>';
                            if(!empty($next_post))
                            echo '<a href="'.get_permalink($next_post->ID).'" class="next"><strong>'.get_the_post_thumbnail($next_post->ID,'thumbnail').'<span>'.$next_post->post_title.'</span></strong></a>';
                            echo '</li>';
                            ?>
                        </ul>    
                    </div>
                    <?php
                        }
                    ?>
                </div>
                
                <?php
                    $author = getPostMeta($post->ID,'vibe_author',true);
                    if(vibe_validate($author)){
                        if ( function_exists('get_coauthors')){
                            $coauthors = get_coauthors( $id );
                            if(isset($coauthors)){
                                foreach($coauthors as $k=>$inst){

                                    $instructor_id = $inst->ID;
                                    $displayname = $inst->display_name;
                                    $author_posts_url = get_author_posts_url( $instructor_id );
                                    $instructing_courses=apply_filters('wplms_instructing_courses_endpoint','instructing-courses');
                                    $description = get_user_meta($instructor_id,'description',true);
                                    $website = $inst->user_url;
                                    ?>
                                    <div class="postauthor">
                                        <div class="auth_image">
                                            <?php
                                                echo get_avatar( $instructor_id, '160');
                                             ?>
                                        </div>
                                        <div class="author_info">
                                            <a href="<?php echo $author_posts_url; ?>" class="readmore link"><?php _e('Posts','vibe'); ?></a><a class="readmore">&nbsp;|&nbsp;</a><a href="<?php echo $author_posts_url.$instructing_courses; ?>" class="readmore link"><?php _e('Courses','vibe'); ?></a>
                                            <h6><?php echo $displayname; ?></h6>
                                            <div class="author_desc">
                                                <p>
                                                    <?php echo $description; ?>
                                                </p>
                                                <p class="website"><?php _e('Website','vibe');?> : <a href="<?php  echo $website; ?>" target="_blank"><?php echo $website ;?></a></p>
                                                <?php
                                                    vibe_author_social_icons($instructor_id);
                                                ?>  
                                            </div>     
                                        </div>    
                                    </div>
                                    <?php
                                }
                            }
                        }else{
                            ?>
                            <div class="postauthor">
                                <div class="auth_image">
                                    <?php
                                        echo get_avatar( get_the_author_meta('email'), '160');
                                        $instructing_courses=apply_filters('wplms_instructing_courses_endpoint','instructing-courses');
                                     ?>
                                </div>
                                <div class="author_info">
                                    <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" class="readmore link"><?php _e('Posts','vibe'); ?></a><a class="readmore">&nbsp;|&nbsp;</a><a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ).$instructing_courses; ?>" class="readmore link"><?php _e('Courses','vibe'); ?></a>
                                    <h6><?php the_author(); ?></h6>
                                    <div class="author_desc">
                                        <p>
                                            <?php  the_author_meta( 'description' );?>
                                        </p>
                                        <p class="website"><?php _e('Website','vibe');?> : <a href="<?php  the_author_meta( 'url' );?>" target="_blank"><?php  the_author_meta( 'url' );?></a></p>
                                        <?php
                                            $author_id=  get_the_author_meta('ID');
                                            vibe_author_social_icons($author_id);
                                        ?>  
                                    </div>     
                                </div>    
                            </div>
                            <?php
                        }
                    } 
                
                comments_template();
                endwhile;
                endif;
                ?>
            </div>
            <?php
             if($template != 'full'){
            ?>
            <div class="col-md-3 col-sm-3">
                <div class="sidebar">
                    <?php
                    $sidebar = apply_filters('wplms_sidebar','mainsidebar',get_the_ID());
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php
                }
            ?>
        </div>
    </div>
</section>
<?php
get_footer(vibe_get_footer());
?>
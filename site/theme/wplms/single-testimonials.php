<?php

if ( !defined( 'ABSPATH' ) ) exit;

get_header(vibe_get_header());
if ( have_posts() ) : while ( have_posts() ) : the_post();


?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-9 col-sm-8">
                <div class="pagetitle">
                    <h1><?php the_title(); ?></h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
             <div class="col-md-3 col-sm-4">
                <?php vibe_breadcrumbs(); ?>
            </div>
        </div>
    </div>
</section>

<section id="content">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-9 col-sm-8">
                <div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="content">
                    <div class="single-testimonial">
                    <?php
                        the_content();
                     ?>
                    </div>
                    <div class="testimonial-author">
                        <?php
                            if(has_post_thumbnail()){
                                echo get_the_post_thumbnail();    
                            }else{
                                echo get_avatar( 'email@example.com', 96 );
                            }
                            
                            echo '<h4>'.get_post_meta(get_the_ID(),'vibe_testimonial_author_name',true).'
                            <span>'.get_post_meta(get_the_ID(),'vibe_testimonial_author_designation',true).'</span></h4>';
                        ?>
                    </div>
                </div>
                </div>
                <?php
                endwhile;
                endif;
                ?>
            </div>
            <div class="col-md-3 col-sm-3">
                <div class="sidebar">
                    <?php
                    $sidebar = apply_filters('wplms_sidebar','mainsidebar',get_the_ID());
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
get_footer(vibe_get_footer());
?>
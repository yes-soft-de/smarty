<?php
get_header(vibe_get_header());
if ( have_posts() ) : while ( have_posts() ) : the_post();

$title=get_post_meta(get_the_ID(),'vibe_title',true);
if(vibe_validate($title)){

?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="container">
        <div class="row">
            <div class="col-md-9 col-sm-8">
                <div class="pagetitle">
                    <h1><?php the_title(); ?></h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
             <div class="col-md-3 col-sm-4">
                 <?php
                    $breadcrumbs=get_post_meta(get_the_ID(),'vibe_breadcrumbs',true);
                    if(vibe_validate($breadcrumbs)){
                        vibe_breadcrumbs();
                    }    
                ?>
            </div>
        </div>
    </div>
</section>
<?php
}

?>
<section id="content">
    <div class="container">
        
        <div class="row">
            <div class="col-md-10 col-sm-10 col-md-offset-1 col-sm-offset-1">
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
                    <?php the_tags('<ul><li>','</li><li>','</li></ul>'); ?>
                    <?php wp_link_pages('before=<div class="page-links"><ul>&link_before=<li>&link_after=</li>&after=</ul></div>'); ?>
                    </div>
                </div>
                <?php
                        $prenex=get_post_meta(get_the_ID(),'vibe_prev_next',true);
                        if(vibe_validate($prenex)){
                    ?>
                    <div class="prev_next_links">
                        <ul class="prev_next">
                            <?php 
                            echo '<li>';previous_post_link('<strong class="prev">%link</strong>'); 
                            echo '</li><li> | </li><li>';
                            next_post_link('<strong class="next">%link</strong>');
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
                if(vibe_validate($author)){?>
                <div class="postauthor">
                    <div class="auth_image">
                        <?php
                            echo get_avatar( get_the_author_meta('email'), '80');
                         ?>
                    </div>
                    <div class="author_info">
                        <a href="<?php echo get_author_posts_url( get_the_author_meta( 'ID' ) ); ?>" class="readmore link">Courses from <?php the_author_meta( 'display_name' ); ?></a>
                        <h6><?php the_author_meta( 'display_name' ); ?></h6>
                        <div class="author_desc">
                             <?php  the_author_meta( 'description' );?>

                             <p class="website">Website : <a href="<?php  the_author_meta( 'url' );?>" target="_blank"><?php  the_author_meta( 'url' );?></a></p>
                                     <?php
                            $author_id=  get_the_author_meta('ID');
                            vibe_author_social_icons($author_id);
                        ?>  
                            
                        </div>     
                    </div>    
                </div>
                <?php
                }
                
                comments_template();
                endwhile;
                endif;
                ?>
            </div>
        </div>
    </div>
</section>
</div>

<?php
get_footer(vibe_get_footer());
?>
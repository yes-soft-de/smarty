<?php
    get_header(vibe_get_header());
    global $wp_query;
    $total_results = $wp_query->found_posts; 
?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="pagetitle">
                    <?php vibe_breadcrumbs(); ?>  
                    <h1><?php _e('Search Results for "', 'vibe'); the_search_query(); ?>"</h1>
                    <h5><?php echo $total_results.__(' results found','vibe');  ?></h5>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="content">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="scontent">
            <?php 
                $select_boxes = apply_filters('wplms_course_search_selects','instructors=1&cats=1&location=1&level=1');
               echo the_widget('BP_Course_Search_Widget',$select_boxes,array()); 
            ?>
        </div>
        <?php
            do_action('wplms_course_sidebar_hook');
        ?>
        <div class="search_results">
            <?php
                $style = vibe_get_option('default_course_block_style');
                if(empty($style )) $style = 'course';
                if ( have_posts() ) : while ( have_posts() ) : the_post();
                //if($post->post_type == 'course'){
                    if(function_exists('thumbnail_generator')){
                        echo '<div class="col-md-3 clear4">'.thumbnail_generator($post,$style,'medium',0,0,0).'</div>';
                    }else{
                        if(function_exists('vibe_get_option')){
                            $default_archive = vibe_get_option('default_archive');
                            if(!empty($default_archive)){
                                get_template_part('content',$default_archive);
                            }else{
                               get_template_part('content','default');
                            }
                        }
                    }
                //}   
                endwhile;
                else:
                    echo '<h3>'.__('Sorry, No results found.','vibe').'</h3>';
                endif;
                pagination();
                wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
<?php
get_footer(vibe_get_footer());
?>
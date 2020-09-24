<?php

if (isset($_GET["post_type"]) && $_GET["post_type"] == 'course'){ 
    if(file_exists(get_stylesheet_directory(). '/search-incourse.php')){
        load_template(get_stylesheet_directory() . '/search-incourse.php'); 
        exit();
    }
    if(file_exists(get_template_directory(). '/search-incourse.php')){
        load_template(get_template_directory() . '/search-incourse.php'); 
        exit();
    }
}

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
        <div class="col-md-9 col-sm-8">
            <div class="content">
                <?php
                    if ( have_posts() ) : while ( have_posts() ) : the_post();

                    $categories = get_the_category();
                    $cats='<ul>';
                    if($categories){
                        foreach($categories as $category) {
                            $cats .= '<li><a href="'.get_category_link( $category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s","vibe" ), $category->cat_name ) ) . '">'.$category->cat_name.'</a></li>';
                        }
                    }
                    $cats .='</ul>';
                    
                    if(function_exists('vibe_get_option')){
                        $default_archive = vibe_get_option('default_archive');
                        if(!empty($default_archive)){
                            get_template_part('content',$default_archive);
                        }else{
                           get_template_part('content','default');
                        }
                    }
                    endwhile;
                    else:
                        echo '<h3>'.__('Sorry, No results found.','vibe').'</h3>';
                    endif;
                    pagination();
                ?>
            </div>
        </div>
        <div class="col-md-3 col-sm-4">
            <div class="sidebar">
                <?php
                    $sidebar = apply_filters('wplms_sidebar','searchsidebar');
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
                    <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
get_footer(vibe_get_footer());
?>
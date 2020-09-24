<?php
get_header(vibe_get_header());
global $wp_query;
$curauth = $wp_query->get_queried_object();

?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
             <div class="col-md-9 col-sm-8">
                <div class="pagetitle">
                    <h1><?php _e('All posts by ','vibe'); echo $curauth->display_name;?> </h1>
                    <h5><?php 
                        if(function_exists('bp_course_get_instructor_description') && function_exists('bp_core_get_user_domain'))
                            echo bp_course_get_instructor_description('instructor_id='.$curauth->ID);
                        else
                            echo $curauth->description;
                        ?></h5>
                </div>
            </div>
            <div class="col-md-3 col-sm-4">
            <?php if(function_exists('bp_core_get_user_domain')){?>
                <a class="button create-group-button full" href="<?php echo bp_core_get_user_domain( get_the_author_meta('ID')); ?>"><?php echo sprintf(__('%s profile','vibe'),bp_core_get_user_displayname(get_the_author_meta('ID'))); ?></a>
                    <?php
                        }
                   ?>
            </div>
        </div>
    </div>
</section>
<section id="content">
	<div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
    		<div class="col-md-9 col-sm-8">
    			<div class="content">
    				<?php
                        if ( have_posts() ) : while ( have_posts() ) : the_post();

                        $categories = get_the_category();
                        $cats='<ul>';
                        if($categories){
                            foreach($categories as $category) {
                                $cats .= '<li><a href="'.get_category_link( $category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s","vibe"  ), $category->name ) ) . '">'.$category->cat_name.'</a></li>';
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
                        endif;
                        pagination();
                    ?>
    			</div>
    		</div>
    		<div class="col-md-3 col-sm-4">
    			<div class="sidebar">
                    <?php
                    $sidebar = apply_filters('wplms_sidebar','mainsidebar');
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
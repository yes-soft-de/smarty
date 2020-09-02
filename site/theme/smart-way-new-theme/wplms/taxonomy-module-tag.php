<?php


if(!is_user_logged_in()){
    wp_die(__('Access restricted.','vibe'));    
}

get_header(vibe_get_header());

$user_id = get_current_user_id();
global $wp_query;
$term = $wp_query->get_queried_object();
$title = $term->name;

?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
	<div class="<?php echo vibe_get_container(); ?>">
		<div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="pagetitle center">
                    <h1><?php _e('Module Tag','vibe'); echo ' "'; single_cat_title(); echo '" '; ?></h1>
                    <h5><?php echo do_shortcode(category_description()); ?></h5>
                </div>
            </div>
        </div>
	</div>
</section>
<section id="content">
	<div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
    		<div class="col-md-10 col-md-offset-1">
    			<div class="content">
    				<?php
                        if ( get_query_var('paged') ) {
                          $paged = get_query_var('paged');
                        } elseif ( get_query_var('page') ) {
                          $paged = get_query_var('page');
                        } else {
                          $paged = 1;
                        }

                        $args = array(
                            'post_type'=>'unit',
                            'page'=>$paged,
                            'tax_query'=>array(
                                array(
                                    'taxonomy' => 'module-tag',
                                    'field'    => 'slug',
                                    'terms'    => $term->name,
                                ),
                            )
                        );

                        $the_query = new WP_Query($args);
                        if ( $the_query->have_posts() ) : while ( $the_query->have_posts() ) : $the_query->the_post();
                        $uid = get_the_ID();
                        
                        if(function_exists('bp_course_get_unit_course_id'))
                            $course_id = bp_course_get_unit_course_id($uid);
                         
                         ?>
                         <div class="unit_block">
                         
                         <h3 class="heading"><?php the_title(); ?><span><?php if(!empty($course_id)){ ?><a href="<?php echo get_permalink($course_id); ?>"><?php _e('Course','vibe'); echo ' : '; ?><?php echo get_the_title($course_id); ?></a><?php } ?></span></h3>
                         <?php the_excerpt(); ?>
                         </div>
                         <?php
                        endwhile;
                        else:
                            ?>
                        <div class="message"><p><?php _e('No Units found in module tag','vibe'); ?></p></div>
                        <?php
                        endif;
                        wp_reset_postdata();
                        pagination();
                    ?>
    			</div>
    		</div>
        </div>
	</div>
</section>

<?php
get_footer(vibe_get_footer());
?>
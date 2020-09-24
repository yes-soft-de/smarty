<?php
/**
 * Template Name: Blog
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(vibe_get_header());
$page_id = get_the_ID();

$title=get_post_meta(get_the_ID(),'vibe_title',true);
if(vibe_validate($title) || empty($title)){
?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="pagetitle">
                    <?php
                        $breadcrumbs=get_post_meta(get_the_ID(),'vibe_breadcrumbs',true);
                        if(vibe_validate($breadcrumbs) || empty($breadcrumbs))
                            vibe_breadcrumbs(); 
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
		<div class="col-md-9 col-sm-8">
			<div class="content">
				<?php
                    
                    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1; 
                    
                    query_posts(array('post_type'=>'post','per_page'=>5,'paged' => $paged));
                    
                    if ( have_posts() ) : while ( have_posts() ) : the_post();

                    $categories = get_the_category();
                    $cats='<ul>';
                    if($categories){
                        foreach($categories as $category) {
                            $cats .= '<li><a href="'.get_category_link( $category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s" ,"vibe" ), $category->name ) ) . '">'.$category->cat_name.'</a></li>';
                        }
                    }
                    $cats .='</ul>';
                        
                   
                   get_template_part('content','default');
                        
                    endwhile;
                    endif;
                    wp_reset_postdata();
                    pagination();
                ?>
			</div>
		</div>
		<div class="col-md-3 col-sm-4">
			<div class="sidebar">
                <?php
                    $sidebar = apply_filters('wplms_sidebar','mainsidebar',$page_id);
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
<?php

if ( !defined( 'ABSPATH' ) ) exit;

get_header(vibe_get_header());
?>
<section id="title" class="title">
	<div class="<?php echo vibe_get_container(); ?>">
		<div class="row">
            <div class="col-md-12">
                <div class="pagetitle">
                    <h1><?php echo get_bloginfo('name'); ?></h1>
                    <h5><?php  echo get_bloginfo('description'); ?></h5>
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
                            $cats .= '<li><a href="'.get_category_link( $category->term_id ).'" title="' . esc_attr( sprintf( __( "View all posts in %s" ,"vibe" ), $category->cat_name ) ) . '">'.$category->cat_name.'</a></li>';
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
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar('mainsidebar') ) : ?>
                <?php endif; ?>
			</div>
		</div>
	</div>
</section>

<?php
get_footer(vibe_get_footer());
?>
<?php	
/*
 *	The template for displaying event categoroes 
 *
 *	Override this template by coping it to ../yourtheme/eventon/ folder
 
 *	@Author: AJDE
 *	@EventON
 *	@version: 0.1
 */
	
	
if ( !defined( 'ABSPATH' ) ) exit;
global $eventon;

if(!class_exists('evo_sinevent') && !empty($eventon->version) && version_compare($eventon->version, '2.4')<0)
wp_die('Please update your eventon plugin to latest version','vibe');

get_header(vibe_get_header());

$tax = get_query_var( 'taxonomy' );
$term = get_query_var( 'term' );

$term = get_term_by( 'slug', $term, $tax );


$tax_name = $eventon->frontend->get_localized_event_tax_names_by_slug($tax);

do_action('eventon_before_main_content');
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
                    <h1><?php   single_cat_title(); ?></h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="content">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="content">
					<div class="hentry">
						<header class="entry-header ">
							<h1 class="entry-title"><?php echo $tax_name.': '.single_cat_title( '', false ); ?></h1>

							<?php if ( category_description() ) : // Show an optional category description ?>
							<div class="entry-meta"><?php echo category_description(); ?></div>
							<?php endif; ?>
						</header><!-- .archive-header -->
						
						<div class='eventon entry-content'>
						<?php 
							echo do_shortcode('[add_eventon_list number_of_months="4" '.$tax.'='.$term->term_id.']');
						?>
						</div>
					</div>
					<?php	do_action('eventon_after_main_content'); ?>
				</div>
			</div>
        </div>
    </div>
</section>


<?php
get_footer(vibe_get_footer());
?>
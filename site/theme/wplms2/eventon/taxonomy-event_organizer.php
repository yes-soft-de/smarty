<?php	
/*
 *	The template for displaying event categoroes - event organizer 
 *
 *	Override this template by coping it to ../yourtheme/eventon/ folder
 
 *	@Author: AJDE
 *	@EventON
 *	@version: 0.1
 */	
	
if ( !defined( 'ABSPATH' ) ) exit;
global $eventon;

if(!class_exists('evo_sinevent') && !empty($eventon->version) && version_compare($eventon->version, '2.4.9')<0)
wp_die('Please update your eventon plugin to latest version','vibe');

get_header(vibe_get_header());

$tax = get_query_var( 'taxonomy' );
$term = get_query_var( 'term' );

$term = get_term_by( 'slug', $term, $tax );

do_action('eventon_before_main_content');

//$term_meta = get_option( "taxonomy_".$term->term_id );
$term_meta = evo_get_term_meta( 'event_organizer',$term->term_id );

// organizer image
	$img_url = false;
	if(!empty($term_meta['evo_org_img'])){
		$img_url = wp_get_attachment_image_src($term_meta['evo_org_img'],'full');
		$img_url = $img_url[0];
	}

$organizer_link_a = (!empty($term_meta['evcal_org_exlink']))? '<a target="_blank" href="'.$term_meta['evcal_org_exlink'].'">': false;
$organizer_link_b = ($organizer_link_a)? '</a>':false;
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
					<div id="content" class='evo_location_card evo_organizer_card'>
						<div class="hentry">
							<div class='eventon entry-content'>
								<div class="evo_location_tax" style='background-image:url(<?php echo $img_url;?>)'>
									<?php if($img_url):?><div class="location_circle" style='background-image:url(<?php echo $img_url;?>)'></div><?php endif;?>
									<h2 class="organizer_name"><span><?php echo $organizer_link_a.$term->name.$organizer_link_b;?></span></h2>
									<div class='organizer_description'><?php echo category_description();?><p class='contactinfo'><?php echo $term_meta['evcal_org_contact'];?></p>
										<?php
											echo (!empty($term_meta['evcal_org_address']))? '<p>'.$term_meta['evcal_org_address'].'</p>':null; 
										?>
									</div>				
								</div>	
								<?php if( !empty($term_meta['evcal_org_address']) ):?><div id='evo_locationcard_gmap' class="evo_location_map" data-address='<?php echo stripslashes($term_meta['evcal_org_address']);?>' data-latlng='' data-location_type='add'data-zoom='16'></div><?php endif;?>		
								<h3 class="location_subtitle"><?php evo_lang_e('Events by this organizer');?></h3>
							
							<?php 
								echo do_shortcode('[add_eventon_list number_of_months="5" '.$tax.'='.$term->term_id.' hide_mult_occur="no" hide_empty_months="yes"]');
							?>
							</div>
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
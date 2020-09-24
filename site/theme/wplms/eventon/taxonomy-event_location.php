<?php	
/*
 *	The template for displaying event categoroes - event location 
 *	d
 * 	In order to customize this archive page template
 *	Override this template by coping it to ../yourtheme/eventon/ folder
 
 *	@Author: AJDE
 *	@EventON
 *	@version: 2.4.7
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

$term_meta = evo_get_term_meta( 'event_location',$term->term_id );
//$term_meta = get_option( "taxonomy_".$term->term_id );

// location image
$img_url = false;
if(!empty($term_meta['evo_loc_img'])){
	$img_url = wp_get_attachment_image_src($term_meta['evo_loc_img'],'full');
	$img_url = $img_url[0];
}

//location address
$location_address = $location_latlan = false;
$location_type = 'add';

	$location_latlan = (!empty($term_meta['location_lat']) && $term_meta['location_lon'])?
		$term_meta['location_lat'].','.$term_meta['location_lon']:false;

if(empty($term_meta['location_address'])){
	if($location_latlan){
		$location_type ='latlng';
		$location_address = true;
	}
}else{
	$location_address = stripslashes($term_meta['location_address']);
}

		
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
					<div id="content" class='evo_location_card'>
						<div class="hentry">
							<div class='eventon entry-content'>
								<div class="evo_location_tax" style='background-image:url(<?php echo $img_url;?>)'>
									<?php if($img_url):?><div class="location_circle" style='background-image:url(<?php echo $img_url;?>)'></div><?php endif;?>
									<h2 class="location_name"><span><?php echo $term->name;?></span></h2>
									<?php if($location_type=='add'):?><p class="location_address"><span><i class='fa fa-map-marker'></i> <?php echo $location_address;?></span></p><?php endif;?>
									<div class='location_description'><?php echo category_description();?></div>
								</div>
								<?php if($location_address):?><div id='evo_locationcard_gmap' class="evo_location_map" data-address='<?php echo $location_address;?>' data-latlng='<?php echo $location_latlan;?>' data-location_type='<?php echo $location_type;?>'data-zoom='16'></div><?php endif;?>
								<h3 class="location_subtitle"><?php evo_lang_e('Events at this location');?></h3>
							
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
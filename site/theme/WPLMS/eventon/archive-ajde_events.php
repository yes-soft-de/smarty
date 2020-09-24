<?php   
/*
 *  The template for displaying events calendar on "/events" url slug
 *
 *  Override this template by coping it to yourtheme/eventon/archive-ajde_events.php
 *
 *  @Author: AJDE
 *  @EventON
 *  @version: 0.1
 */

if ( !defined( 'ABSPATH' ) ) exit;

global $eventon;
if(!class_exists('evo_sinevent') && !empty($eventon->version) && version_compare($eventon->version, '2.4.9')<0)
wp_die('Please update your eventon plugin to latest version','vibe');  
    
get_header(vibe_get_header());
?>
<section id="title">
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
                    <h1><?php  post_type_archive_title(); ?></h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$evOpt = evo_get_options('1');

$archive_page_id = evo_get_event_page_id($evOpt);
?>
<section id="content">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-9 col-sm-8">
                <div class="content">
                <?php
                // check whether archieve post id passed
                if($archive_page_id){
                    $archive_page  = get_page($archive_page_id);    
                    echo "<div class='wrapper'>";
                    echo apply_filters('the_content', $archive_page->post_content);
                    echo "</div>";

                }else{
                    echo "<p>ERROR: Please select a event archive page in eventON Settings > Events Paging > Select Events Page</p>";
                }
                ?>
                </div>
            </div>
            <div class="col-md-3 col-sm-4">
                <div class="sidebar">
                    <?php
                    global $wp_registered_sidebars;
                    $sidebar = (in_array('evose_sidebar', $wp_registered_sidebars)? 'evose_sidebar' : 'main_sidebar');
                    $sidebar = apply_filters('wplms_sidebar','evose_sidebar');
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
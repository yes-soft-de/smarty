<?php
/**
 * Template Name: All Instructors
 */
if ( ! defined( 'ABSPATH' ) ) exit;
get_header(vibe_get_header());


$no=999;
$args = apply_filters('wplms_allinstructors',array(
                'role' => 'instructor', // instructor
    			'number' => $no, 
                'orderby' => 'post_count', 
                'order' => 'DESC' 
    		));

$user_query = new WP_User_Query( $args );

$args = apply_filters('wplms_alladmins',array(
                'role' => 'administrator', // admin
                'number' => $no, 
                'orderby' => 'post_count', 
                'order' => 'DESC' 
            ));
$flag = apply_filters('wplms_show_admin_in_instructors',1);
if(isset($flag) && $flag)
    $admin_query = new WP_User_Query( $args );

$instructors=array();
if ( isset($admin_query) && !empty( $admin_query->results ) ) {
    foreach ( $admin_query->results as $user ) {
        $instructors[]=$user->ID;
    }
}

if ( !empty( $user_query->results ) ) {
    foreach ( $user_query->results as $user ) {
        $instructors[]=$user->ID;
    }
}

$instructors=array_unique($instructors);

$ifield = vibe_get_option('instructor_field');
if(!isset($ifield) || $ifield =='')$ifield='Speciality';

$title=get_post_meta(get_the_ID(),'vibe_title',true);
if(vibe_validate($title)){
?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="pagetitle">
                    <?php
                        $breadcrumbs=get_post_meta(get_the_ID(),'vibe_breadcrumbs',true);
                        if(vibe_validate($breadcrumbs)){
                         vibe_breadcrumbs();
                        }
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
            <div class="content padding_adjusted">
                <?php
                    if(isset($instructors) && is_array($instructors) && count($instructors)){
                        foreach($instructors as $instructor){

			             ?>
			             	<div class="col-md-4 col-sm-4 clear3">
			             		<div class="instructor">
									<?php echo bp_core_fetch_avatar( array( 'item_id' => $instructor,'type'=>'full', 'html' => true ) ); ?>
									<span><?php 
                                    if(bp_is_active('xprofile'))
                                    echo bp_get_profile_field_data( 'field='.$ifield.'&user_id=' .$instructor ); 
                                    ?></span>
									<h3><?php echo bp_core_get_userlink( $instructor ); ?></h3>
									<strong><a href="<?php echo get_author_posts_url(  $instructor ).'instructing-courses/'; ?>"><?php _e('Courses by Instructor ','vibe'); echo  '<span>'.count_user_posts_by_type($instructor,'course').'</span></a>'; ?></strong>
								</div>
			             		<?php
			             			
			             		?>
			             	</div>

			             <?php
				        }
				    }else {
					 echo '<div id="message"><p>'.__('No Instructors found.','vibe').'</p></div>';
					}
                 ?>
            </div>
        </div>
    </div>
</section>
<?php
get_footer(vibe_get_footer());

?>
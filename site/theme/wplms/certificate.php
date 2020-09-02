<?php
/**
 * Template Name: Certificate
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$code = $_REQUEST['certificate_code'];

if(isset($code)){

  $user_id = apply_filters('wplms_certificate_code_user_id',$code);
  $course_id = apply_filters('wplms_certificate_code_course_id',$code);
  $template = apply_filters('wplms_certificate_code_template_id',$code);

  if(strlen($code)<2 || empty($user_id) || empty($course_id) || empty($template)){
    wp_die(__('INVALID CERTIFICATE CODE','vibe'),__('INVALID CERTIFICATE CODE','vibe'));
  }
  
}else{
  $user_id = $_REQUEST['u'];
  $course_id = $_REQUEST['c'];
}

do_action('wplms_validate_certificate',$user_id,$course_id);

$url = apply_filters('bp_get_course_certificate_url',0,$course_id,$user_id);

if(strpos($url, '.jpeg')){
  wp_redirect($url);
  exit;
}

if(isset($code)){
  if($template !== get_the_ID()){
    $location = get_permalink($template).'?c='.$course_id.'&u='.$user_id;
    wp_redirect($location);
    exit();
  }
}

get_header(vibe_get_header());
if ( have_posts() ) : while ( have_posts() ) : the_post();

$certificate_class = apply_filters('wplms_certificate_class','');
do_action('wplms_certificate_before_full_content');

?>
<section id="certificate" <?php echo (empty($certificate_class)?'':'class="'.$certificate_class.'"'); ?>>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <?php do_action('wplms_certificate_before_content'); ?>
                <div class="extra_buttons">
                    <?php do_action('wplms_certificate_extra_buttons');
                        echo '<a href="#" class="certificate_close"><i class="fa fa-times"></i></a>';
                        echo '<a href="#" class="certificate_print"><i class="fa fa-print"></i></a>';
                        echo '<a href="#" class="certificate_pdf"><i class="fa fa-file-pdf-o"></i></a>';
                        echo '<a href="#" class="certificate_download"><i class="fa fa-download"></i></a>';
                    ?>    
                </div>
                <div class="certificate certificate_content" data-width="800" data-height="640">
                  <br /><br />
                   	<h1><?php echo apply_filters('wplms_certificate_heading',__('CERTIFICATE OF COMPLETION','vibe')); ?></h1>

                   	<h6><?php echo apply_filters('wplms_certificate_sub_heading',__('Awarded to','vibe')); ?></h6>

                    <?php do_action('wplms_certificate_before_name'); ?>

                   	<h2><?php echo bp_core_get_user_displayname($user_id); ?> 

                    <?php do_action('wplms_certificate_after_name'); ?>

                   	<span><?php echo xprofile_get_field_data( 'Location', $user_id ); ?></span></h2>

                   	<span><?php echo apply_filters('wplms_certificate_before_course_title',__('for successful completion of course','vibe')); ?></span>
                   	<h3><?php echo get_the_title($course_id); ?></h3>
                    <?php do_action('wplms_certificate_after_content'); ?>
                    <br /><br />
                </div>
            </div>
        </div>
    </div>
</section>
<?php

do_action('wplms_certificate_after_full_content');

endwhile;
endif;

get_footer(vibe_get_footer());
?>
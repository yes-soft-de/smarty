<?php

if ( !defined( 'ABSPATH' ) ) exit;

get_header(vibe_get_header());
if ( have_posts() ) : while ( have_posts() ) : the_post();

$print=get_post_meta($post->ID,'vibe_print',true);
$course=get_post_meta($post->ID,'vibe_event_course',true);

$private_event=get_post_meta($post->ID,'vibe_private_event',true);

$icon_class=get_post_meta($post->ID,'vibe_icon',true);
$color=get_post_meta($post->ID,'vibe_color',true);
$start_date=get_post_meta($post->ID,'vibe_start_date',true);
$end_date=get_post_meta($post->ID,'vibe_end_date',true);
$show_location=get_post_meta($post->ID,'vibe_show_location',true);
$location=vibe_sanitize(get_post_meta($post->ID,'vibe_location',false));



$additional_info=vibe_sanitize(get_post_meta($post->ID,'vibe_additional_info',false));
$more_info=get_post_meta($post->ID,'vibe_more_info',true);

$send_invitation = get_post_meta($post->ID,'vibe_send_invitation',true);

$access_flag=1;
if(vibe_validate($private_event)){
    $access_flag=0;
}


do_action('wplms_before_single_event');
?>
<section id="title">
    <?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-9 col-sm-8">
                <div class="pagetitle">
                    <span><?php echo '<a href="'.get_permalink($course).'">'.get_the_title($course).'</a>'; ?></span>
                    <h1><i class="<?php echo $icon_class; ?>" style="color:<?php echo $color; ?>"></i>
                        <?php the_title(); ?>
                    </h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
             <div class="col-md-3 col-sm-4">
                <?php
                do_action('wplms_single_event_messages');
                get_currentuserinfo();

                $check_student=get_post_meta($course,$current_user->ID,true);

                if (($post->post_author == $current_user->ID) || current_user_can('manage_options')){   
                   if(isset($send_invitation) && $send_invitation){
                        echo '<a href="#" id="send_event_invitation" class="button full" data-course="'.$course.'" data-event-id="'.get_The_ID().'">'.__('RE-INVITE STUDENTS','vibe').'<span>'.$send_invitation.'</span></a>';
                    }else{
                        echo '<a href="#" id="send_event_invitation" class="button full" data-course="'.$course.'" data-event-id="'.get_The_ID().'">'.__('SEND INVITATION','vibe').'</a>';
                    }
                    echo '<a href="#" id="send_event_reminder" class="button full" data-course="'.$course.'" data-event-id="'.get_The_ID().'">'.__('SEND EMAIL REMINDER','vibe').'</a>';
                    // INVITATION FORM
                    echo '<form method="post" id="invitation_form"><div class="invitation_box">
                        <input type="text" class="form_field" name="invitation_subject" placeholder="'.__('SUBJECT','vibe').'" />
                        <textarea class="form_field" name="invitation_message">'.__('INVITATION MESSAGE','vibe').'</textarea>
                        </div>';
                     wp_nonce_field('vibe'.$course_id,'security');
                    echo '<input type="submit" name="send_invitations" class="button primary" value="'.__('SEND INVITATIONS','vibe').'" /></form>';

                    // REMINDER FORM
                    echo '<form method="post" id="reminder_form"><div class="reminder_box">
                        <input type="text" class="form_field" name="reminder_subject" placeholder="'.__('SUBJECT','vibe').'" />
                        <textarea class="form_field" name="reminder_message">'.__('REMINDER MESSAGE','vibe').'</textarea>
                        </div>';
                     wp_nonce_field('vibe'.$course_id,'security');
                    echo '<input type="submit" name="send_reminder" class="button primary" value="'.__('SEND REMINDER','vibe').'" />
                    <p>'.__('Reminders are emails sent to Students who have accepted the Event invitation.','vibe').'</p></form>';
                }else if(isset($check_student) && $check_student){
                    $check_invite = apply_filters('wplms_events_invite_buttons',get_post_meta(get_the_ID(),$current_user->ID,true));
                    if(isset($check_invite) && $check_invite){
                         vibe_breadcrumbs();
                    }else{
                        $nonce = wp_create_nonce('vibe_'.$post->ID.$current_user->ID);

                       echo '<a href="'.get_permalink().'?security='.$nonce.'&accept" id="accept" class="button full">'.__('ACCEPT INVITE','vibe').'</a>';
                        echo '<a href="'.get_permalink().'?security='.$nonce.'&reject" id="reject" class="button primary full">'.__('REJECT INVITE','vibe').'</a>';
                    }
                }else{
                    vibe_breadcrumbs();
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
                    <?php

                    echo apply_filters('wplms_event_access_thumbnail',thumbnail_generator($post,'event_card'));
                    ?>
                    <div class="content">
                        <?php do_action('wplms_event_before_content'); ?>
                        <div class="extra_buttons">
                            <?php do_action('wplms_event_extra_buttons');

                            if(vibe_validate($print)) 
                                echo '<a href="#" class="certificate_print"><i class="icon-printer-1"></i></a>';
                       
                            ?>
                        </div>
                        
                        <?php
                        if(apply_filters('wplms_event_access_flag',$access_flag)){
                        ?>
                        <div class="featured">
                            <?php 
                            the_post_thumbnail();
                            ?>
                        </div>
                        <?php    
                            the_content(); 
                        ?>
                        <?php
                        if(vibe_validate($show_location)){
                           $map_zoom=vibe_get_option('map_zoom');
                      echo '<h3 class="heading">'.__('Event Location','vibe').'</h3>';
                      echo '<div class="vibe_gmap">
                                <script>
                                function initialize() {
                                  var myLatlng = new google.maps.LatLng('.$location['latitude'].','.$location['longitude'].');
                                  var mapOptions = {
                                    zoom: '.(isset($map_zoom)?$map_zoom:15).',
                                    center: myLatlng,
                                    mapTypeId: google.maps.MapTypeId.ROADMAP
                                  }
                                  var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

                                  var marker = new google.maps.Marker({
                                      position: myLatlng,
                                      map: map,
                                      title: "'.get_the_title().'"
                                  });
                                }

                                google.maps.event.addDomListener(window, "load", initialize);

                            </script> ';                        
                          
                        echo '<div id="map-canvas"></div>
                        </div>';
                        }
                    }
                        ?>
                         <?php do_action('wplms_event_after_content',get_the_ID()); ?>
                    </div>
                <?php
                
                endwhile;
                endif;

                ?>
            </div>
            <div class="col-md-3 col-sm-4">
                <?php
                if(apply_filters('wplms_event_access_flag',$access_flag)){
                    if(isset($additional_info) && is_array($additional_info)){
                        ?>
                        <div class="widget additional_info">
                            <h3 class="heading"><?php _e('Additional Information','vibe'); ?></h3>
                            <ul>
                            <?php
                            foreach($additional_info as $info){
                                echo '<li>'.html_entity_decode($info).'</li>';
                            }
                            ?>
                            </ul>
                        </div>
                        <?php
                    }
                    if(isset($more_info) && $more_info !=''){
                        echo '<div class="widget more_info">';
                        echo do_shortcode($more_info);
                        echo '</div>';
                    }
                }   
                $sidebar=getPostMeta($post->ID,'vibe_sidebar');
                ((isset($sidebar) && $sidebar)?$sidebar:$sidebar='coursesidebar');
                if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
               <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php
do_action('wplms_after_event');
?>
<?php
get_footer(vibe_get_footer());
?>
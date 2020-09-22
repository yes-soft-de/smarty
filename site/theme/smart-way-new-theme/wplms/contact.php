<?php

/**
 * Template Name: Contact Page
 */

if ( ! defined( 'ABSPATH' ) ) exit;
get_header(vibe_get_header());

$contact_ll=vibe_get_option('contact_ll');
$contact_style = vibe_get_option('contact_style');
$map_zoom = vibe_get_option('map_zoom');
if(have_posts()):while(have_posts()):the_post();
?>
<section class="stripe" id="contactcanvas">
    <div id="map-canvas"></div>
</section>
<section id="content">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="content">
                    <?php
                        the_content();
                     ?>
                </div>
                <?php
                
                endwhile;
                endif;
                ?>
            </div>
        </div>
    </div>
</section>
<?php
  $protocol = is_ssl() ? 'https' : 'http';
  $google_apikey_contact = vibe_get_option('google_apikey_contact');
  $src = '//maps.googleapis.com/maps/api/js';
  if(!empty($google_apikey_contact)){
    $src = '//maps.googleapis.com/maps/api/js?key='.$google_apikey_contact;
  }
  
?>
<script type="text/javascript" src="<?php echo $src; ?>"></script>
<script type='text/javascript'>
  var map;
       
           function initialize() {
          
              var point = new google.maps.LatLng(<?php if(isset($contact_ll)){echo $contact_ll; }else {echo '43.730325,7.422155'; }; ?>);
              var centrePoint = new google.maps.LatLng(<?php if(isset($contact_ll)){echo $contact_ll; }else {echo '43.730325,7.422155'; }; ?>);
          
              var myOptions = {
                 center: centrePoint,
                 zoom: <?php if(isset($map_zoom)){echo $map_zoom; }else {echo '17'; }; ?>,
                 mapTypeId: google.maps.MapTypeId.<?php if(isset($contact_style)) {echo $contact_style;}else{echo 'SATELLITE';} ?>,
                 disableDefaultUI:true,
                 scrollwheel:false,
                 zoomControl: true,
                 zoomControlOptions: {
                     style: google.maps.ZoomControlStyle.LARGE,
                     position: google.maps.ControlPosition.RIGHT_CENTER
                 }
              };
          
              map = new google.maps.Map(document.getElementById('map-canvas'), myOptions);

              var image = new google.maps.MarkerImage(
                 '<?php echo VIBE_URL; ?>/assets/images/marker.png',
                 
                 new google.maps.Size(51,32),
                 new google.maps.Point(0,0),
                 new google.maps.Point(25.5,32)
              );


              var shape = {
                coord: [25,0,28,1,30,2,31,3,33,4,33,5,34,6,35,7,36,8,36,9,36,10,37,11,37,12,37,13,37,14,37,15,37,16,37,17,37,18,37,19,37,20,37,21,37,22,37,23,36,24,36,25,36,26,35,27,35,28,34,29,34,30,33,31,33,32,32,33,32,34,31,35,31,36,30,37,30,38,29,39,29,40,28,41,27,42,27,43,26,44,26,45,25,46,24,47,24,48,23,49,23,50,22,51,21,52,16,52,15,51,14,50,14,49,13,48,13,47,12,46,11,45,11,44,10,43,10,42,9,41,8,40,8,39,7,38,7,37,6,36,6,35,5,34,5,33,4,32,4,31,3,30,3,29,2,28,2,27,1,26,1,25,1,24,0,23,0,22,0,21,0,20,0,19,0,18,0,17,0,16,0,15,0,14,0,13,0,12,0,11,1,10,1,9,1,8,2,7,3,6,4,5,4,4,6,3,7,2,9,1,12,0,25,0],
                type: 'poly'
              };

              var marker = new google.maps.Marker({
                draggable: false,
                raiseOnDrag: false,
                animation: google.maps.Animation.DROP,
                icon: image,
                shape: shape,
                map: map,
                position: point
              });
          
              google.maps.event.addListener(marker, 'click', toggleBounce);
          
              function toggleBounce() {
                if (marker.getAnimation() != null) {
                  marker.setAnimation(null);
                } else {
                  marker.setAnimation(google.maps.Animation.BOUNCE);
                }   
              }
           }
       
           //google.maps.event.addDomListener(window, 'load', initialize);
           setTimeout(initialize, 2000);
</script>
<?php
get_footer(vibe_get_footer());
?>
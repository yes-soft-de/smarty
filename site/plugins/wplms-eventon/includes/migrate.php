<?php
/**
 * Migrate WPLMS Events to EventOn Events
 *
 * @author    VibeThemes
 * @category  Admin
 * @package   WPLMS-eventon/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
  exit;
}


class Wplms_EventOn_Migrate{

  public static $instance;
    
    public static function init(){
      
        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_EventOn_Migrate();
        return self::$instance;
    }

  private function __construct(){ 
    add_action('admin_init',array($this,'process_action'));
    add_action( 'admin_notices', array($this,'migrate_notice') );
    add_filter('wplms_required_plugins',array($this,'remove_wplms_events'));
  }


  function process_action(){
    if(empty($_POST))
      return;

    if(!isset($_POST['wplms_eventon_security'])){
          return;
        }
    if ( !isset($_POST['wplms_eventon_security']) || !wp_verify_nonce($_POST['wplms_eventon_security'],'wplms_eventon_security')  || !current_user_can('manage_options')){
             _e('Security check Failed. Contact Administrator.','wplms-front-end');
             die();
        }

        if(isset($_POST['dismiss'])){
          update_option('wplms_eventon_migrate_notice',1);
          return;
        }

        if(isset($_POST['deactivate'])){
          deactivate_plugins('wplms-events/wplms-events.php');
          update_option('wplms_eventon_migrate_notice',3);
          return;
        }

        if(!isset($_POST['migrate_wplms_events']))
          return;

        global $wpdb;
        
        $events = $wpdb->get_results("
              SELECT meta_id,post_id,meta_key,meta_value
              FROM {$wpdb->postmeta} 
              WHERE post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type = 'wplms-event')
              AND meta_key IN ('vibe_subtitle','vibe_all_day','vibe_color','vibe_start_date','vibe_start_time','vibe_end_date',
                'vibe_end_time','vibe_show_location','vibe_location','vibe_event_course')");

       foreach($events as $event){
         switch($event->meta_key){
          case 'vibe_subtitle':
            update_post_meta($event->post_id,'evcal_subtitle',$event->meta_value);
          break;
          case 'vibe_all_day':
            if($event->meta_value == 'S'){
              $meta_value = 'on';
            }else{
              $meta_value = 'off';
            }
            update_post_meta($event->post_id,'evcal_allday',$meta_value);
          break;
          case 'vibe_color':
            $meta_value = str_replace('#','',$event->meta_value);
            update_post_meta($event->post_id,'evcal_event_color',$meta_value);
          break;
          case 'vibe_start_date':
            $this->start_datetime[$event->post_id] = $event->meta_value;
          break;
          case 'vibe_start_time':
            $this->start_datetime[$event->post_id] .= ' '. $event->meta_value;
            $evcal_srow = strtotime($this->start_datetime[$event->post_id]);
            update_post_meta($event->post_id,'evcal_srow',$evcal_srow);
          break;
          case 'vibe_end_date':
            $this->end_datetime[$event->post_id] = $event->meta_value;
          break;
          case 'vibe_end_time':
            $this->end_datetime[$event->post_id] .= ' '. $event->meta_value;
            $evcal_erow = strtotime($this->end_datetime[$event->post_id]);
            update_post_meta($event->post_id,'evcal_erow',$evcal_erow);
          break;
          case 'vibe_show_location':
            if($event->meta_value == 'S'){
              $meta_value = 'on';
            }else{
              $meta_value = 'off';
            }
            update_post_meta($event->post_id,'evcal_gmap_gen',$meta_value);
          break;
          case 'vibe_location':
            if(!empty($event->meta_value)){
              $meta_value = unserialize($event->meta_value);
              update_post_meta($event->post_id,'evcal_lat',$meta_value['latitude']);
              update_post_meta($event->post_id,'evcal_lon',$meta_value['longitude']);
              $address = $meta_value['staddress'].' '.$meta_value['city'].' '.$meta_value['state'].' '.$meta_value['country'].' - '.$meta_value['pincode'];
              update_post_meta($event->post_id,'evcal_location',$address);
            }
          break;
          case 'vibe_event_course':
            update_post_meta($event->post_id,'wplms_ev_course',$event->meta_value);
          break;
         }
       }
    $wpdb->query("UPDATE {$wpdb->posts} SET post_type = 'ajde_events' WHERE post_type = 'wplms-event'");
    update_option('wplms_eventon_migrate_notice',2);
  }

  function migrate_notice(){

    if (!in_array( 'wplms-events/wplms-events.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {  
      return;
    }
    $notice = get_option('wplms_eventon_migrate_notice');

    if(empty($notice) && current_user_can('manage_options')){
      if ( in_array( 'eventON/eventon.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {     
              $class = "update-nag";

              $count_events = wp_count_posts( 'wplms-event' )->publish+wp_count_posts( 'wplms-event' )->draft;
              if(empty($count_events)){
                update_option('wplms_eventon_migrate_notice',2);
              }else{

                  $message = sprintf(__('Migrate all WPLMS Events to EventOn Events %s Start migration %s','wplms-eventon'),'<input type="submit" name="migrate_wplms_events" class="button-primary button" value="','">');
                  echo '<div class="'.$class.'" style="position:relative;width:90%;"><form method="post"> <p>'.$message.'</p>
                      <button type="submit" name="dismiss" class="notice-dismiss"><span class="screen-reader-text"></span></button>';
                      wp_nonce_field('wplms_eventon_security','wplms_eventon_security');
                  echo '</form></div>'; 
                
              }
          }
      
    }

    switch($notice){
      case 2:
        $class = "updated";
        $message = __('Migration Complete ! All WPLMS Events and settings have now been migrated to EventOn Events. Check WP Admin - Events section','wplms-eventon');
            echo '<div class="'.$class.'" style="position:relative;width:90%;"><form method="post"><p>'.$message.'</p>
                <button type="submit" name="deactivate" class="button">'.__('Deactivate WPLMS Events Plugin','wplms-eventon').'</button><p></p>
                <button type="submit" name="dismiss" class="notice-dismiss"><span class="screen-reader-text"></span></button>';
                wp_nonce_field('wplms_eventon_security','wplms_eventon_security');
        echo '</form></div>'; 
      break;
      case 3:
        $class = "updated";
        $message = __('WPLMS Events deactivated. Create new events with EventOn plugin.','wplms-eventon');
            echo '<div class="'.$class.'" style="position:relative;width:90%;"><form method="post"><p>'.$message.'</p>
                <button type="submit" name="dismiss" class="notice-dismiss"><span class="screen-reader-text"></span></button>';
                wp_nonce_field('wplms_eventon_security','wplms_eventon_security');
        echo '</form></div>'; 
      break;
      case 1:
      break;
    }
  }

  function remove_wplms_events($plugins){
    unset($plugins[9]);
    return $plugins;
  }
}

Wplms_EventOn_Migrate::init();
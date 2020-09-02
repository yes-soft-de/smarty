<?php
if(!class_exists('Wplms_bbb_Meetings_tab_Mods'))
{
    if ( bp_is_active( 'groups' ) ) :
        class Wplms_bbb_Meetings_tab_Mods extends BP_Group_Extension {
            /**
             * Your __construct() method will contain configuration options for 
             * your extension, and will pass them to parent::init()
             */
            function __construct() {
                $args = array(
                    'slug' => apply_filters('wplms_bbb_meetings_slug','bbb-meetings'),
                    'name' =>  __('Meetings','wplms-bbb'),
                    'access' => apply_filters('wplms_bbb_meetings_authority','member'),
                );
                parent::init( $args );
            }
         
            /**
             * display() contains the markup that will be displayed on the main 
             * plugin tab
             */
            function display( $group_id = NULL ) {
                $group_id = bp_get_group_id();
                $bbb = Wplms_Bbb::init();
                $user_id = get_current_user_id();
                echo '<table id="user-tours" class="table table-hover">';
                echo '<thead><tr><th>'._x('Meeting name','','wplms-bbb').'</th><th>'._x('Status','','vibe').'</th><th>'._x('Action','','vibe').'</th></tr></thead><tbody>';
                foreach ($bbb->wplms_bbb_meetings as $meetng_id => $meeting) {
                    $scope = $meeting['restrictions']['scope'];
                    $flag = 0;
                    $users = $bbb->users_from_restriction($meeting,1);
                    
                    if(in_array($user_id,$users) && in_array($group_id ,$meeting['restrictions']['data'])){
                        
                        $status = _x('NA','','wplms-bbb');
                        if(!empty($meeting['start_date']) && !empty($meeting['start_time'])){

                            $start_time = strtotime($meeting['start_date'].' '.$meeting['start_time']);
                            $start_time_actual = strtotime($meeting['start_date'].' '.$meeting['start_time']);
                            $offset = get_option('gmt_offset');
                            if($offset > 0){//means gmt offset is in positive
                                $start_time = $start_time - (abs($offset)*60*60);

                            }else{//means gmt offset is in negative
                                $start_time = $start_time + (abs($offset)*60*60);
                            }
                            $expiry_time = $start_time + ($meeting['duration']['duration']* $meeting['duration']['parameter']);
                            $expiry_time_actual = $start_time_actual + ($meeting['duration']['duration']* $meeting['duration']['parameter']);
                            $format = get_option( 'date_format' ).' '.get_option('time_format');;
                            $readable_time_start = date_i18n($format ,$start_time_actual);
                            $readable_time_expire =date_i18n($format , $expiry_time_actual); 
                            if(time() >= $start_time &&  time() <= $expiry_time ){
                                $status = _x('Ongoing','','wplms-bbb');
                            }elseif(time() <= $start_time){
                                
                                $status = sprintf(_x('To be started on %s','','wplms-bbb'), $readable_time_start );
                            }elseif(time() >= $expiry_time){
                                $status =sprintf( _x('Meeting over on %s','','wplms-bbb'),$readable_time_expire );;
                            }
                        }
                       
                       
                        echo '<tr><td>'.$meeting['name'].'</td>';
                        echo '<td>'.$status.'</td>';
                        echo '<td>'.do_shortcode('[wplms_bbb token="'.$meeting['id'].'" popup="1" size="1"]').'</td>';
                        echo '<tr>';
                    }
                }
                echo '</tbody></table>';
            }
         
           
        }
        $for_mods = apply_filters('wplms_bbb_meetings_authority_for_mods',true);
        if($for_mods){
            bp_register_group_extension( 'Wplms_bbb_Meetings_tab_Mods' );
        }
    
    endif; 
}


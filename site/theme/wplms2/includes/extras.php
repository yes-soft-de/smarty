<?php
/**
 * Initialization functions for WPLMS
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     Initialization
 * @version     2.0
 */


if ( ! defined( 'ABSPATH' ) ) exit;

class vibe_extras{

    public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new vibe_extras();

        return self::$instance;
    }

    private function __construct(){
        /*==== AUTHOR PROFILE FIELDS =====*/        
        add_action( 'show_user_profile',array($this,'vibe_social_profile_fields' ));
        add_action( 'edit_user_profile', array($this,'vibe_social_profile_fields'));
        add_action( 'personal_options_update',array($this,'save_vibe_social_profile_fields') );
        add_action( 'edit_user_profile_update',array($this,'save_vibe_social_profile_fields') );
        add_filter('manage_users_columns', array($this,'vibe_add_user_id_column'));
        add_action('manage_users_custom_column',  array($this,'vibe_show_user_id_column_content'), 10, 3);
        add_filter('manage_pages_columns', array($this,'wplms_posts_columns_id'), 5);
        add_action('manage_pages_custom_column',array($this,'wplms_posts_custom_id_columns'), 5, 2);
        add_filter('manage_posts_columns',array($this, 'wplms_posts_columns_id'), 5);
        add_action('manage_posts_custom_column', array($this,'wplms_posts_custom_id_columns'), 5, 2);
        add_filter('wplms_course_front_details',array($this,'wplms_custom_social_sharing_links'));
    }

    /* == Social icons in WP Admin panel Author interface ===*/
    function vibe_social_profile_fields( $user ) { 
        static $run;
        // Function has already run
        if ( $run !== null )
            return;
        $run = 'performed';
           
         ?>
        <h3><?php _e('Author Social profile information','vibe'); ?></h3>
        <a id="add_social_info" class="button button-primary"><?php _e('Add Social Information','vibe'); ?></a>
        <table id="socialtable" class="form-table">
           <?php
           
                $social=get_the_author_meta('social',$user->ID);
                if(isset($social) && is_array($social)){
                $i=0;
                $icons=$social['icon'];
                $urls=$social['link'];
                foreach($icons as $value){
                    ?>
                    <tr>
                <th>
                    <select name="social[icon][<?php echo $i; ?>]"><?php
                    echo'<option value="twitter" '.(($value =='twitter')?'selected="selected"':'').'>Twitter</option>
                        <option value="facebook" '.(($value =='facebook')?'selected="selected"':'').'>Facebook</option>
                        <option value="github" '.(($value =='github')?'selected="selected"':'').'>Github</option>
                        <option value="pinterest" '.(($value =='pinterest')?'selected="selected"':'').'>Pinterest</option>
                        <option value="pinboard" '.(($value =='pinboard')?'selected="selected"':'').'>pinboard</option>    
                        <option value="google-plus" '.(($value =='google-plus')?'selected="selected"':'').'>Google Plus</option>
                        <option value="google" '.(($value =='google')?'selected="selected"':'').'>Google</option>
                        <option value="vk" '.(($value =='vk')?'selected="selected"':'').'>vKontakte</option>
                        <option value="gmail" '.(($value =='gmail')?'selected="selected"':'').'>Gmail</option>
                        <option value="tumblr" '.(($value =='tumblr')?'selected="selected"':'').'>Tumblr</option>
                        <option value="foursquare" '.(($value =='foursquare')?'selected="selected"':'').'>Foursquare</option>    
                        <option value="linkedin" '.(($value =='linkedin')?'selected="selected"':'').'>Linkedin</option>
                        <option value="dribbble"'.(($value =='dribbble')?'selected="selected"':'').'>Dribbble</option>
                        <option value="forrst"'.(($value =='forrst')?'selected="selected"':'').'>Forrst</option>    
                        <option value="stumbleupon" '.(($value =='stumbleupon')?'selected="selected"':'').'>Stumbleupon</option>
                        <option value="digg" '.(($value =='digg')?'selected="selected"':'').'>Digg</option>     
                        <option value="flickr" '.(($value =='flickr')?'selected="selected"':'').'>flickr</option>      
                        <option value="disqus" '.(($value =='disqus')?'selected="selected"':'').'>disqus</option> 
                        <option value="yahoo" '.(($value =='yahoo')?'selected="selected"':'').'>yahoo</option> 
                        <option value="rss-1" '.(($value =='rss-1')?'selected="selected"':'').'>rss</option> 
                        <option value="chrome" '.(($value =='chrome')?'selected="selected"':'').'>chrome</option>         
                        <option value="lastfm" '.(($value =='lastfm')?'selected="selected"':'').'>LastFM</option>
                        <option value="delicious" '.(($value =='delicious')?'selected="selected"':'').'>Delicious</option>
                        <option value="reddit" '.(($value =='reddit')?'selected="selected"':'').'>Reddit</option>     
                        <option value="blogger" '.(($value =='blogger')?'selected="selected"':'').'>Blogger</option>     
                        <option value="spotify" '.(($value =='spotify')?'selected="selected"':'').'>Spotify</option>
                        <option value="instagram" '.(($value =='instagram')?'selected="selected"':'').'>Instagram</option>
                        <option value="flattr" '.(($value =='flattr')?'selected="selected"':'').'>Flattr</option>
                        <option value="skype" '.(($value =='skype')?'selected="selected"':'').'>Skype</option>
                        <option value="dropbox" '.(($value =='dropbox')?'selected="selected"':'').'>Dropbox</option>
                        <option value="evernote" '.(($value =='evernote')?'selected="selected"':'').'>Evernote</option>
                        <option value="paypal" '.(($value =='paypal')?'selected="selected"':'').'>Paypal</option>
                        <option value="soundcloud" '.(($value =='soundcloud')?'selected="selected"':'').'>SoundCloud</option>
                        <option value="xing" '.(($value =='xing')?'selected="selected"':'').'>Xing</option>       
                        <option value="behance"'.(($value =='behance')?'selected="selected"':'').'>Behance</option>
                        <option value="smashing" '.(($value =='smashing')?'selected="selected"':'').'>Smashing Magazine</option>
                        <option value="bitcoin" '.(($value =='bitcoin')?'selected="selected"':'').'>Bitcoin</option>    
                        <option value="w3c" '.(($value =='w3c')?'selected="selected"':'').'>W3C</option>  
                        <option value="html5" '.(($value =='html5')?'selected="selected"':'').'>HTML 5</option> 
                        <option value="wordpress" '.(($value =='wordpress')?'selected="selected"':'').'>wordpress</option>     
                        <option value="android" '.(($value =='android')?'selected="selected"':'').'>Android</option> 
                        <option value="appstore" '.(($value =='appstore')?'selected="selected"':'').'>Appstore</option> 
                        <option value="macstore" '.(($value =='macstore')?'selected="selected"':'').'>Macstore</option>     
                        <option value="podcast" '.(($value =='podcast')?'selected="selected"':'').'>Podcast</option> 
                        <option value="amazon" '.(($value =='amazon')?'selected="selected"':'').'>Amazon</option>     
                        <option value="ebay" '.(($value =='ebay')?'selected="selected"':'').'>eBay</option>     
                        <option value="googleplay" '.(($value =='googleplay')?'selected="selected"':'').'>Google play</option>     
                        <option value="itunes" '.(($value =='itunes')?'selected="selected"':'').'>itunes</option>     
                        <option value="songkick" '.(($value =='songkick')?'selected="selected"':'').'>songkick</option>     
                        <option value="plurk" '.(($value =='plurk')?'selected="selected"':'').'>plurk</option>     
                        <option value="steam" '.(($value =='steam')?'selected="selected"':'').'>steam</option>     
                        <option value="wikipedia" '.(($value =='wikipedia')?'selected="selected"':'').'>Wikipedia</option> 
                        <option value="lanyrd" '.(($value =='lanyrd')?'selected="selected"':'').'>Lanyrd</option> 
                        <option value="fivehundredpx" '.(($value =='fivehundredpx')?'selected="selected"':'').'>Fivehundredpx</option> 
                        <option value="gowalla" '.(($value =='gowalla')?'selected="selected"':'').'>gowalla</option> 
                        <option value="klout" '.(($value =='klout')?'selected="selected"':'').'>klout</option> 
                        <option value="viadeo" '.(($value =='viadeo')?'selected="selected"':'').'>viadeo</option> 
                        <option value="meetup" '.(($value =='meetup')?'selected="selected"':'').'>meetup</option> 
                        <option value="vk" '.(($value =='vk')?'selected="selected"':'').'>vk</option>    
                        <option value="ninetyninedesigns" '.(($value =='ninetyninedesigns')?'selected="selected"':'').'>99 Designs</option>       
                        <option value="sina-weibo" '.(($value =='sina-weibo')?'selected="selected"':'').'>Sina weibo</option>
                        <option value="openid" '.(($value =='openid')?'selected="selected"':'').'>openid</option>
                        <option value="posterous" '.(($value =='posterous')?'selected="selected"':'').'>posterous</option>
                        <option value="yelp" '.(($value =='yelp')?'selected="selected"':'').'>yelp</option>
                        <option value="scribd" '.(($value =='scribd')?'selected="selected"':'').'>scribd</option>'; ?>
                    </select>
                </th>
                <td>
                    <input type="text" name="social[link][<?php echo $i; ?>]" id="author_url" value="<?php echo $urls[$i]; ?>" class="regular-text" />
                     <a href="javascript:void(0);" class="author-social-remove"><i class="dashicons dashicons-no"></i></a>
                    <br />
                    <span class="description"><?php _e('Enter URL for social icon.','vibe'); ?></span>
                </td>
            </tr>
            <?php
                    $i++;
                }
                }  
           ?>
            <tr style="display:none;">
                <th>
                    <select rel-name="social[icon][]">
                        <option value="twitter">Twitter</option>
                        <option value="facebook">Facebook</option>
                        <option value="github">Github</option>
                        <option value="pinterest">Pinterest</option>
                        <option value="pinboard">pinboard</option>    
                        <option value="google-plus">Google Plus</option>
                        <option value="google">Google</option>
                        <option value="vk">vKontakte</option>
                        <option value="gmail">Gmail</option>
                        <option value="tumblr">Tumblr</option>
                        <option value="foursquare">Foursquare</option>    
                        <option value="linkedin">Linkedin</option>
                        <option value="dribbble">Dribbble</option>
                        <option value="forrst">Forrst</option>    
                        <option value="stumbleupon">Stumbleupon</option>
                        <option value="digg">Digg</option>     
                        <option value="flickr">flickr</option>      
                        <option value="disqus">disqus</option> 
                        <option value="yahoo">yahoo</option> 
                        <option value="rss">rss</option> 
                        <option value="chrome">chrome</option>         
                        <option value="lastfm">LastFM</option>
                        <option value="delicious">Delicious</option>
                        <option value="reddit">Reddit</option>     
                        <option value="blogger">Blogger</option>     
                        <option value="spotify">Spotify</option>
                        <option value="instagram">Instagram</option>
                        <option value="flattr">Flattr</option>
                        <option value="skype">Skype</option>
                        <option value="dropbox">Dropbox</option>
                        <option value="evernote">Evernote</option>
                        <option value="paypal">Paypal</option>
                        <option value="soundcloud">SoundCloud</option>
                        <option value="xing">Xing</option>       
                        <option value="behance">Behance</option>
                        <option value="smashing">Smashing Magazine</option>
                        <option value="bitcoin">Bitcoin</option>    
                        <option value="w3c">W3C</option>  
                        <option value="html5">HTML 5</option> 
                        <option value="wordpress">wordpress</option>     
                        <option value="android">Android</option> 
                        <option value="appstore">Appstore</option> 
                        <option value="macstore">Macstore</option>     
                        <option value="podcast">Podcast</option> 
                        <option value="amazon">Amazon</option>     
                        <option value="ebay">eBay</option>     
                        <option value="googleplay">Google play</option>     
                        <option value="itunes">itunes</option>     
                        <option value="songkick">songkick</option>     
                        <option value="plurk">plurk</option>     
                        <option value="steam">steam</option>     
                        <option value="wikipedia">Wikipedia</option> 
                        <option value="lanyrd">Lanyrd</option> 
                        <option value="fivehundredpx">Fivehundredpx</option> 
                        <option value="gowalla">gowalla</option> 
                        <option value="klout">klout</option> 
                        <option value="viadeo">viadeo</option> 
                        <option value="meetup">meetup</option> 
                        <option value="vk">vk</option>    
                        <option value="ninetyninedesigns">99 Designs</option>       
                        <option value="sina-weibo">Sina weibo</option>
                        <option value="openid">openid</option>
                        <option value="posterous">posterous</option>
                        <option value="yelp">yelp</option>
                        <option value="scribd">scribd</option>
                    </select>
                </th>
                <td>
                    <input type="text" rel-name="social[link][]" id="author_url" value="" class="regular-text" />
                     <a href="javascript:void(0);" class="author-social-remove"><i class="dashicons dashicons-no"></i></a>
                    <br />
                    <span class="description"><?php _e('Enter URL for social icon.','vibe'); ?></span>
                </td>
            </tr>
        </table>
        <?php }

        function save_vibe_social_profile_fields( $user_id ) {

            if ( !current_user_can( 'edit_user', $user_id ) )
                return false;
            /* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
          if(isset($_POST['social']))
               update_user_meta( $user_id, 'social', $_POST['social'] );
        }


        function vibe_author_social_icons($id){
            $social = get_the_author_meta('social',$id);
            if(isset($social) && is_array($social)){
            echo '<ul class="social">';
            
            $icons='fa fa-'.$social['icon'];
            $urls=$social['link'];
            $i=0;
            if(isset($icons) && is_array($icons)){
            foreach($icons as $icon){
                echo '<li><a href="'.$urls[$i].'" class="tip" title="'.__('Share on ','vibe').' '.$icon.'"><i class="icon-'.$icon.'"></i></a></li>';
                $i++;
                }
            }
            echo '</ul>';
            }
        }
    /* === End Social icons ===*/

    function vibe_add_user_id_column($columns) {

        $columns['user_status'] = __('Status','vibe');
        $columns['user_id'] = __('User ID','vibe');
        return $columns;

    }
    function vibe_show_user_id_column_content($value, $column_name, $user_id) {

        if ( 'user_status' == $column_name ){

            $user = get_userdata( $user_id );
            $current_userId = get_current_user_ID();
            $last_activity = get_user_meta($user_id,'last_activity',true);
            $threshold = apply_filters('wplms_login_threshold',1800);
            $last_active_time = strtotime($last_activity);
            $difference = time() - $last_active_time - $threshold;

            if(empty($last_activity)){
                $last_activity = _x('N.A.','When user has not even once logged in the website','vibe');
            }else{
                $date_format = get_option('date_format');
                $last_activity = date($date_format,$last_active_time);
            }

            if($difference<=0 || $current_userId == $user_id){
                return '<span class="user_online"> '.__('Online','vibe').'</span>';
            }else{
                return '<span class="user_offline"> '.__('Offline','vibe').'</span><br><span>'.__('Since: ','vibe').$last_activity.'</span>';
            }
        }

        if ( 'user_id' == $column_name )
            return $user_id;
        return $value;

    }

    function social_sharing_links(){

        $social_sharing = array(
            'Facebook' => 'http://www.facebook.com/share.php?u=[URL]',
            'Twitter' => 'http://twitter.com/share?url=[URL]',
            'VK'=>'http://vk.com/share.php?url=[URL]',
            'Digg' => 'http://www.digg.com/submit?phase=2&url=[URL]&title=[TITLE]',
            'Pinterest' => 'http://pinterest.com/pin/create/button/?url=[URL]',
            'Stumbleupon' => 'http://www.stumbleupon.com/submit?url=[URL]&title=[TITLE]',
            'Delicious' => 'http://del.icio.us/post?url=[URL]&title=[TITLE]]&notes=[DESCRIPTION]',
            'Google plus' => 'https://plus.google.com/share?url=[URL]',
            'GoogleBuzz' => 'http://www.google.com/reader/link?title=[TITLE]&url=[URL]',
            'LinkedIn' => 'http://www.linkedin.com/shareArticle?mini=true&url=[URL]&title=[TITLE]&source=[DOMAIN]',
            'SlashDot' => 'http://slashdot.org/bookmark.pl?url=[URL]&title=[TITLE]',
            'Technorati' => 'http://technorati.com/faves?add=[URL]&title=[TITLE]',
            'Posterous' => 'http://posterous.com/share?linkto=[URL]',
            'Tumblr' => 'http://www.tumblr.com/share?v=3&u=[URL]&t=[TITLE]',
            'Reddit' => 'http://www.reddit.com/submit?url=[URL]&title=[TITLE]',
            'GoogleBookmarks' => 'http://www.google.com/bookmarks/mark?op=edit&bkmk=[URL]&title=[TITLE]&annotation=[DESCRIPTION]',
            'NewsVine' => 'http://www.newsvine.com/_tools/seed&save?u=[URL]&h=[TITLE]',
            'PingFm' => 'http://ping.fm/ref/?link=[URL]&title=[TITLE]&body=[DESCRIPTION]',
            'Evernote' => 'http://www.evernote.com/clip.action?url=[URL]&title=[TITLE]',
            'FriendFeed' => 'http://www.friendfeed.com/share?url=[URL]&title=[TITLE]'
        );

        return $social_sharing;
    }

    //Social Sharing Function
    public function social_sharing($tip_direction='top',$url = null){

        $social_share = vibe_get_option('social_share');
        $social_icons_type= vibe_get_option('social_icons_type');
        $output='';
        if(isset($social_share) && is_array($social_share)){
            $output ='<ul class="socialicons '.$social_icons_type.'">';
            $social_sharing = $this->social_sharing_links();
            
            foreach($social_share as $social){
                 global $post;
                 $title = get_the_title(); 

                 if(empty($url))
                    $url = get_permalink(); 

                 $description = strip_tags(get_the_excerpt()); 
                 $domain = get_site_url(); 
                /*=== Preparing Sharing Link ====*/
                
                 $social_sharing[$social] = str_replace('[TITLE]',$title,$social_sharing[$social]);
                 $social_sharing[$social] = str_replace('[URL]',$url,$social_sharing[$social]);
                 $social_sharing[$social] = str_replace('[DESCRIPTION]',$description,$social_sharing[$social]);
                 $social_sharing[$social] = str_replace('[DOMAIN]',$domain,$social_sharing[$social]);
                 
                 $tip='';
                /*=== END Preparing Sharing Link ====*/
                $show_social_tooltip = vibe_get_option('show_social_tooltip');
                if(isset($show_social_tooltip) && $show_social_tooltip)
                    $tip='data-placement="'.$tip_direction.'" title="'.__('Share on ','vibe').$social.'"';
               
                $output .='<li>';
                $output .= '<a href="'.$social_sharing[$social].'" '.$tip.' target="_blank" class="'.strtolower($social).((isset($show_social_tooltip) && $show_social_tooltip)?' tip':'').'"><i class="fa fa-'.strtolower($social).'"></i></a>';
                $output .='</li>';
            }
            $output .= '</ul>';
        }
        return $output;
    }

    function wplms_posts_columns_id($defaults){
        $defaults['wplms_id'] = __('ID','vibe');
        return $defaults;
    }
    function wplms_posts_custom_id_columns($column_name, $id){
        if($column_name == 'wplms_id'){
            echo $id;
        }
    }
    

    function wplms_custom_social_sharing_links($return){
       $sharing = '<div class="course_sharing">'.$this->social_sharing().'</div>';
       return $return.$sharing;
    }
}

vibe_extras::init();

if(!function_exists('social_sharing')){
    function social_sharing($tip_direction='top',$url = null){
        
        $vibe_extras = vibe_extras::init(); 
        return $vibe_extras->social_sharing($tip_direction,$url);
    }
}
if(!function_exists('vibe_author_social_icons')){
    function vibe_author_social_icons($id){
        $vibe_extras = vibe_extras::init();
        $vibe_extras ->vibe_author_social_icons($id);
    }
}
if(!function_exists('social_sharing_links')){
    function social_sharing_links(){
        $vibe_extras = vibe_extras::init();
        return $vibe_extras->social_sharing_links();
    } 
}



<?php
/**
 * Register Scripts
 *
 * @class       VibeBP_Register
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VibeBP_Register{


    public static $instance;
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new VibeBP_Register();
        return self::$instance;
    }

    private function __construct(){

        add_action('init',array($this,'register_menus'));
        add_action('wp_enqueue_scripts',array($this,'enqueue_head'),11);

    }

    function register_menus(){
        register_nav_menus(
            array(
                'loggedin' => __( 'LoggedIn DropDown Menu','vibebp' ),
                'profile' => __( 'Profile Menu','vibebp' )
            )
        );
    }

    function get_vibebp(){

        $blog_id = '';
        if(function_exists('get_current_blog_id')){
            $blog_id = get_current_blog_id();
        }

        $firebase_config =vibebp_get_setting('firebase_config');
        if(!empty($firebase_config) && is_serialized($firebase_config)){
            $firebase_config = json_encode(unserialize($firebase_config)); 
        }


        $vibebp= array(
            'style'=>'medium',
            'user_id'=>bp_displayed_user_id(),
            'api'=>array(
                'url'=> apply_filters('vibebp_rest_api',get_rest_url($blog_id,Vibe_BP_API_NAMESPACE)),
                'sw_enabled'=>vibebp_get_setting('service_workers'),
                'sw'=>site_url().'/firebase-messaging-sw.js?v='.vibebp_get_setting('version','service_worker'),
                'endpoints'=>array(
                    'activity'      => Vibe_BP_API_ACTIVITY_TYPE,
                    'members'       => Vibe_BP_API_MEMBERS_TYPE,
                    'groups'        => Vibe_BP_API_GROUPS_TYPE,
                    'friends'       => 'friends',
                    'notifications' => Vibe_BP_API_NOTIFICATIONS_TYPE,
                    'messages'      => Vibe_BP_API_MESSAGES_TYPE,
                    'settings'      => Vibe_BP_API_SETTINGS_TYPE,
                    'xprofile'      => Vibe_BP_API_XPROFILE_TYPE
                ),
            ),
            'settings'=>array(
                'timestamp'=>time(),
                'client_id'=>vibebp_get_setting('client_id'),
                'security'=>vibebp_get_api_security(),
                'upload_limit'=>wp_max_upload_size(),
                'google_maps_api_key'=>vibebp_get_setting('google_maps_api_key'),
                'firebase_config'=>$firebase_config,
                'auth'=>array(
                    'google'=>vibebp_get_setting('firebase_google_auth'),
                    'facebook'=>vibebp_get_setting('firebase_facebook_auth'),
                    'twitter'=>vibebp_get_setting('firebase_twitter_auth'),
                    'github'=>vibebp_get_setting('firebase_github_auth'),
                    'apple'=>vibebp_get_setting('firebase_apple_auth')
                ),
                'map_marker'=>plugins_url('../assets/images/marker.png',__FILE__),
                'followers' => vibebp_get_setting('bp_followers','bp'),
                'likes' => vibebp_get_setting('bp_likes','bp'),
                'profile_page'=>vibebp_get_setting('offline_page','service_worker'),
                'enable_registrations'=>apply_filters('vibebp_enable_registration',true),
                'profile_settings'=>array() //array of arrays each array(key=>'CLASS ID TO INITIALIZE REACT COMPONENT','value'=>'xx')
            ),
            
            'translations'=>array(
                'hello'=> _x('Hello','dashboard','vibebp'),
                'online'=> _x('Online','api','vibebp'),
                'offline'=> _x('You are no longer connected to internet.','api','vibebp'),
                'empty_dashboard' => _x('Empty dashboard.','api','vibebp'),
                'facebook' => _x('Sign In with Facebook','api','vibebp'),
                'twitter' => _x('Sign In with Twitter','api','vibebp'),
                'google' => _x('Sign In with Google','api','vibebp'),
                'github' => _x('Sign In with Github','api','vibebp'),
                'apple' => _x('Sign In with  Apple','api','vibebp'),
                'login_heading'=> vibebp_get_setting('login_heading'),
                'login_message'=>vibebp_get_setting('login_message'),
                'email_login'=>_x('Login with Email',' login','vibebp'),
                'no_account'=>_x('No account ?',' login','vibebp'),
                'create_one'=>_x('Create one',' login','vibebp'),
                'create_account'=>_x('Create Account',' login','vibebp'),
                'login_terms'=> stripslashes(vibebp_get_setting('login_terms')),
                'signin_email_heading'=> stripslashes(vibebp_get_setting('signin_email_heading')),
                'signin_email_description'=>stripslashes(vibebp_get_setting('signin_email_description')),
                'email'=>_x('Email','login','vibebp'),
                'password'=>_x('Password','login','vibebp'),
                'all_signin_options'=>_x('All sign in options','login','vibebp'),
                'signin'=>_x('Sign In','login','vibebp'),
                'forgotpassword'=>_x('Forgot Password','login','vibebp'),
                'password_recovery_email'=>_x('Send password recovery email','login','vibebp'),
                'missing_subject'=>_x('Missing subject','message error','vibebp'), 
                'missing_recipients'=>_x('Missing recipients','message error','vibebp'), 
                'missing_content'=>_x('Missing message content','message error','vibebp'),
                'light_mode'=>_x('Light mode','profile','vibebp'),
                'dark_mode'=>_x('Dark mode','profile','vibebp'),
                'register_account_heading'=>stripslashes(vibebp_get_setting('register_account_heading')),
                'register_account_description'=>stripslashes(vibebp_get_setting('register_account_description')),
                'account_already'=>_x('Already have an account ? ','login','vibebp'),
                'likes'=>_x('Likes','login','vibebp'),
                'liked'=>_x('Liked','login','vibebp'),
                'followers'=>_x('Followers','login','vibebp'),
                'following'=>_x('Following','login','vibebp'),
                'follow_members'=>_x('Follow more members','login','vibebp'),
                'profile'=>_x('Profile ','login','vibebp'),
                'logout'=>_x('Logout ','login','vibebp'),
                'more'=>_x('Load more.. ','login','vibebp'),
                'years'=>_x('years','login','vibebp'),
                'year'=>_x('year ','login','vibebp'),
                'months'=>_x('months','login','vibebp'),
                'month'=>_x('month','login','vibebp'),
                'weeks'=>_x('weeks','login','vibebp'),
                'week'=>_x('week','login','vibebp'),
                'days'=>_x('days','login','vibebp'),
                'day'=>_x('day','login','vibebp'),
                'hours'=>_x('hours','login','vibebp'),
                'hour'=>_x('hour','login','vibebp'),
                'minutes'=>_x('minutes','login','vibebp'),
                'minute'=>_x('minute','login','vibebp'),
                'seconds'=>_x('seconds','login','vibebp'),
                'second'=>_x('second','login','vibebp'),
                'no_activity_found'=>_x('No activity found !','login','vibebp'),
                'whats_new'=>_x('Whats New','login','vibebp'),
                'post_update'=>_x('Post update','login','vibebp'),
                'select_component'=>_x('Select component','login','vibebp'),
                'justnow'=>_x('Just now','login','vibebp'),
                'cancel'=>_x('Cancel','login','vibebp'),
                'owner'=>_x('Owner','login','vibebp'),
                'date'=>_x('Date','login','vibebp'),
                'apply'=>_x('Apply','login','vibebp'),
                'type_message'=>_x('Type Message','login','vibebp'),
                'drag_to_refresh'=>_x('Drag to refresh','drag','vibebp'),
                'selectaction'=>_x('Select Action','login','vibebp'),
                'no_notifications_found'=>_x('No notifications found !','login','vibebp'),
                'sender'=>_x('Sender','login','vibebp'),
                'no_messages_found'=>_x('No messages found !','login','vibebp'),
                'no_groups_found'=>_x('No groups found !','login','vibebp'),
                'new_message'=>_x('New Message','login','vibebp'),
                'send_notice'=>_x('Send Notice','login','vibebp'),
                'labels'=>_x('Labels','login','vibebp'),
                'add_new'=>_x('Add New','login','vibebp'),
                'search_text'=>_x('Search ...','login','vibebp'),
                'recipients'=>_x('Recipients ...','login','vibebp'),
                'subject'=>_x('Subject','login','vibebp'),
                'message'=>_x('Message','login','vibebp'),
                'attachments'=>_x('Attachment','login','vibebp'),
                'send_message'=>_x('Send Message','login','vibebp'),
                'search_member'=>_x('Search Member','login','vibebp'),
                'add_label'=>_x('Add Label','login','vibebp'),
                'remove_label'=>_x('Remove Label','login','vibebp'),
                'select_image'=>_x('Upload File','login','vibebp'),
                'group_name'=>_x('Group Name','login','vibebp'),
                'group_description'=>_x('Group Description','login','vibebp'),
                'group_status'=>_x('Group Status','login','vibebp'),
                'group_type'=>_x('Group Type','login','vibebp'),
                'invite_members'=>_x('Invite members','login','vibebp'),
                'add_members'=>_x('Add members','login','vibebp'),
                'create_group'=>_x('Create Group','login','vibebp'),
                'group_invitations'=>_x('Group Invite Permissions','login','vibebp'),
                'image_size_error'=>sprintf(_x('Image size should be less than upload limit %s','login','vibebp'),'( '.floor(wp_max_upload_size()/1024).' kb )'),
                'admin'=>_x('Admin','login','vibebp'),
                'mod'=>_x('Mod','login','vibebp'),
                'select_option'=>_x('Select Option','login','vibebp'),
                'accept_invite'=>_x('Accept Invite','login','vibebp'),
                'cancel_invite'=>_x('Cancel Invite','login','vibebp'),
                'no_friends_found'=>_x('No Friends found !','login','vibebp'),
                'requester'=>_x('Requested','login','vibebp'),
                'requestee'=>_x('Requests','login','vibebp'),
                'no_requests_found'=>_x('No Requests found !','login','vibebp'),
                'add_friend'=>_x('Add Friend','login','vibebp'),
                'send_friend_request'=>_x('Send friend request','login','vibebp'),
                'account_email'=>_x('Account Email','login','vibebp'),
                'confirm_old_password'=>_x('Confirm Old Password','login','vibebp'),  
                'change_password'=>_x('Change Password','login','vibebp'), 
                'change_email' => _x('Change Email','','vibebp'),
                'repeat_new_password'=>_x('Repeat Password','login','vibebp'), 
                'save_changes'=>_x('Save Changes','login','vibebp'),
                'send_email_notice'=>_x('Send email notice','login','vibebp'),
                'visibility_settings'=>_x('Profile Field Visibility Settings','login','vibebp'),
                'export_data_settings'=>_x('Export data settings','login','vibebp'),
                'download_data'=>__( 'Download personal data', 'vibebp' ),
                'new_data'=>__('Request new data export', 'vibebp' ),
                'request_data'=>__('Request personal data export', 'vibebp'),
                'update_image'=>__('Update Image', 'vibebp'),
                'change_image'=>__('Change Image', 'vibebp'),
                'address'=>__('Address', 'vibebp'),
                'city'=>__('City', 'vibebp'),
                'country'=>__('Country', 'vibebp'),
                'country'=>__('ZipCode', 'vibebp'),
                'no_followers'=>__('No followers found !', 'vibebp'),
                'no_following'=>__('You are not following anyone !', 'vibebp'),
                'set_icon'=>__('Set Icon', 'vibebp'),
                'submit'=>__('Submit', 'vibebp'),
                'topic_title'=>__('Topic Title', 'vibebp'),
                'select_forum'=>__('Select a forum', 'vibebp'),
                'topic_content'=>__('Write content in topic', 'vibebp'),
                'subscribed'=>__('Subscribed', 'vibebp'),
                'subscribe'=>__('Subscribe', 'vibebp'),
                'no_orders'=>__('No Orders', 'vibebp'),
                'coupons_applied'=>__('Coupons Applied', 'vibebp'),
                'shipping_rates'=>__('Shipping Rates', 'vibebp'),
                'fees'=>__('Fees', 'vibebp'),
                'select_order_status'=>__('Select Order Status', 'vibebp'),
                'order_number'=>__('Order Number', 'vibebp'),
                'order_date'=>__('Order Date', 'vibebp'), 
                'order_status'=>__('Order Status', 'vibebp'), 
                'order_quantity'=>__('Order Quantity', 'vibebp'), 
                'order_amount'=>__('Order Total', 'vibebp'),
                'order_payment_method'=>__('Payment Method', 'vibebp'),
                'item_name'=>__('Item Name', 'vibebp'),
                'item_total'=>__('Item Total', 'vibebp'),
                'billing_address'=>__('Billing Address', 'vibebp'),
                'shipping_address'=>__('Shipping Address', 'vibebp'),
                'no_downloads'=>__('No Downloads found !', 'vibebp'),
                'download'=>__('Download', 'vibebp'),
                'access_expires'=>__('Access Expires', 'vibebp'),
                'product_name'=>__('Product Name', 'vibebp'),
                'remaining_downloads'=>__('Remaining Downloads', 'vibebp'),
                'allMedia' =>__('All Media', 'vibebp'),
                'uploaded_media'=>__('Uploads', 'vibebp'),
                'select_media'=>__('Select Media', 'vibebp'), 
                'upload_media'=>__('Upload file', 'vibebp'),
                'embed_media'=>__('Embed file', 'vibebp'),
                'choose_column_type'=>__('Choose Columns', 'vibebp'),
                'type_here'=>__('Type here...', 'vibebp'),
                'no_media'=>__('No media found!', 'vibebp'),
                'preview'=>__('Preview', 'vibebp'),
                'select_widget'=>__('Select Widget', 'vibebp'),
                'missing_data'=>__('Missing data', 'vibebp'),
                'invalid_email'=>__('Invalid email id.', 'vibebp'),
                'password_too_short'=>__('Too short password.', 'vibebp'),
                'enter_emabed_name'=>__('Enter Embed Name', 'vibebp'),
                'enter_embed_url'=>__('Enter Embed Url', 'vibebp'),
                'embed'=>__('Embed', 'vibebp'),
                'hide_panel'=>__('Hide Panel', 'vibebp'),
                'show_panel'=>__('Show Panel', 'vibebp'),
            ),
        );
        
        $vibebp['components'] = array(

            'dashboard'=>array(
                    'sidebar'=>apply_filters('vibebp_member_dashboard','vibebp-dashboard',$vibebp['user_id']),
                    'widgets'=>apply_filters('vibebp_member_dashboard_widgets',array())
                ),
            'profile'=>array(
                'label'=>__('Profile','vibebp'),
            )
        );
        $social_icons = apply_filters('vibebp_social_icons',array(
            array(
                'icon'=>'vicon vicon-flickr',
                'label'=>__('Flickr','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-instagram',
                'label'=>__('Instagram','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-google',
                'label'=>__('Google','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-github',
                'label'=>__('Github','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-facebook',
                'label'=>__('Facebook','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-dropbox',
                'label'=>__('Dropbox','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-dribbble',
                'label'=>__('Dribbble','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-apple',
                'label'=>__('Apple','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-yahoo',
                'label'=>__('Yahoo','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-trello',
                'label'=>__('Trello','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-stack-overflow',
                'label'=>__('Stack-overflow','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-soundcloud',
                'label'=>__('Soundcloud','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-sharethis',
                'label'=>__('Sharethis','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-reddit',
                'label'=>__('Reddit','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-microsoft',
                'label'=>__('Microsoft','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-linux',
                'label'=>__('Linux','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-jsfiddle',
                'label'=>__('Jsfiddle','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-joomla',
                'label'=>__('Joomla','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-html5',
                'label'=>__('Html5','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-css3',
                'label'=>__('Css3','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-drupal',
                'label'=>__('Drupal','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-wordpress',
                'label'=>__('Wordpress','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-tumblr',
                'label'=>__('Tumblr','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-skype',
                'label'=>__('Skype','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-youtube',
                'label'=>__('Youtube','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-vimeo',
                'label'=>__('Vimeo','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-twitter',
                'label'=>__('Twitter','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-linkedin',
                'label'=>__('Linkedin','vibebp')
            ),
            array(
                'icon'=>'vicon vicon-pinterest',
                'label'=>__('Pinterest','vibebp')
            )
        ));
        $vibebp['social_icons']=$social_icons;

        if(vibebp_get_setting('firebase_config')){
            $url = site_url();
            $parsedUrl = parse_url($url);
            $vibebp['components']['firebase'] = array(
                'root'=>(isset($parsedUrl['path'])?strstr($url, $parsedUrl['path'], true):$url),
                'tabs'=>Array(
                    array('tab'=>'notes','label'=>__('Notes','vibebp'),'icon'=>'vicon vicon-notepad'),
                    array('tab'=>'notifications','label'=>__('Notifications','vibebp'),'icon'=>'vicon vicon-bell'),
                    array('tab'=>'chat','label'=>__('Chat','vibebp'),'icon'=>'vicon vicon-comments'),
                )
            );
        }   
        global $post;
        if((function_exists('bp_is_user') && bp_is_user()) || (!empty($post) &&  !empty($post->post_content) && has_shortcode($post->post_content,'vibebp_profile'))){
            //$vibebp['user_id'] = bp_displayed_user_id();
            $nav = get_option('bp_setup_nav');
            if(empty($nav) && !empty(bp_get_nav_menu_items())){
                $nav = bp_get_nav_menu_items();
            }

            foreach($nav as $key => $item){
                $name = strip_tags($item->name);
                if(is_numeric(substr($name, -1,1))){
                    $end = strrpos($name, ' ');
                    $name = substr($name, 0,$end);
                    $count = substr($name, $end);
                    $nav[$key]->count = $count;
                }
                $nav[$key]->name = $name;
                if(in_array("current-menu-parent",$item->class) ){
                    unset($nav[$key]->class[array_search("current-menu-parent",$item->class)]);
                }
                if(in_array("current-menu-item",$item->class)){
                    unset($nav[$key]->class[array_search("current-menu-item",$item->class)]);
                }

                

                if($item->parent === 0){
                   // if(in_Array($item->css_id,array('activity','profile','')
                }
            }
            array_unshift($nav, array(
                'css_id'=>'dashboard',
                'name'=>__('Dashboard','vibebp'),
                'parent'=>0,
                'class'=>["menu-parent","current-menu-parent"]
            ));

            $vibebp['nav'] = $nav;

            if(bp_is_active('xprofile')){
                $vibebp['components']['xprofile']=array(
                    'visibility'=>array(
                        'public'=>__('Public','vibebp'),
                        'loggedin'=>__('All members','vibebp'),
                        'friends'=>__('Friends','vibebp'),
                        'adminsonly'=>__('Only me','vibebp'),
                    ),
                    'countries'=>vibebp_get_countries()
                );
            }
            if(bp_is_active('activity')){
                $vibebp['components']['activity']=array(
                    'label'=>__('Activity','vibebp'),
                    'post'=>array(
                        'components'=>array(
                            '' => __('Personal','vibebp'),
                            'groups' => __('Groups','vibebp'),
                        ),
                    ),
                    'sorters'=>array(
                        '-1'=>_x('Everything','login','vibebp'),
                        'activity_update'=>_x('Updates','login','vibebp'),
                        'friendship_accepted,friendship_created'=>_x('Friendships','login','vibebp'),
                        'created_group'=>_x('New Groups','login','vibebp'),
                        'joined_group'=>_x('Group Memberships','login','vibebp'),
                        'group_details_updated'=>_x('Group Updates','login','vibebp'),
                        
                    ),
                );
            }
            
            if(bp_is_active('notifications')){
                $vibebp['components']['notifications']=array(
                    'label'=>__('Notifications','vibebp'),
                    'sorters'=>array(
                        'DESC'=>_x('Newest First','login','vibebp'),
                        'ASC'=>_x('Oldest First','login','vibebp'),
                    ),
                    'actions'=>Array(
                        'read'=>_x('Read','login','vibebp'),
                        'unread'=>_x('Unread','login','vibebp'),
                        'delete'=>_x('Delete','login','vibebp'),
                    )
                );
            }


            if(bp_is_active('messages')){
                $vibebp['components']['messages']=array(
                    'label'=>__('Messages','vibebp'),
                    'sorters'=>array(
                        'DESC'=>_x('Newest First','login','vibebp'),
                        'ASC'=>_x('Oldest First','login','vibebp'),
                    ),
                    'actions'=>Array(
                        'read'=>_x('Mark Read','login','vibebp'),
                        'unread'=>_x('Mark Unread','login','vibebp'),
                        'delete'=>_x('Delete','login','vibebp'),
                        'star'=>_x('Add Star','login','vibebp'),
                        'unstar'=>_x('Remove Star','login','vibebp'),
                    )
                );
            }

            if(bp_is_active('friends')){
                $vibebp['components']['friends']=array(
                    'label'=>__('Friends','vibebp'),
                    'sorters'=>array(
                        'active'=>_x('Active','login','vibebp'),
                        'alphabetical'=>_x('Alphabetical','login','vibebp'),
                        'newest'=>_x('Newest','login','vibebp'),
                    ),
                    'requests_sorter'=>array(
                        'DESC'=>_x('Newest first','login','vibebp'),
                        'ASC'=>_x('Earliest first','login','vibebp'),
                    ),
                    'actions'=>Array(
                        'read'=>_x('Mark Read','login','vibebp'),
                        'unread'=>_x('Mark Unread','login','vibebp'),
                        'delete'=>_x('Delete','login','vibebp'),
                        'star'=>_x('Add Star','login','vibebp'),
                        'unstar'=>_x('Remove Star','login','vibebp'),
                    )
                );
            }
            
            if(bp_is_active('groups')){

                $vibebp['components']['activity']['post']['components']['groups']=__('Groups','vibebp');
                $vibebp['components']['groups']=array(
                    'label'=>__('Groups','vibebp'),
                    'sorters'=>array(
                        'active'=>_x('Last Active','login','vibebp'),
                        'popular'=>_x('Most Members','login','vibebp'),
                        'newest'=>_x('Newly Created','login','vibebp'),
                        'alphabetical'=>_x('Alphabetical','login','vibebp'),
                    ),
                );

                
              
                if(function_exists('bp_groups_get_group_types')){
                    $vibebp['components']['groups']['type'] =bp_groups_get_group_types();    
                }
                
                $vibebp['components']['groups']['status'] = array(
                    'public'=>_x('Public','login','vibebp'),
                    'private'=>_x('Private','login','vibebp'),
                    'hidden'=>_x('Hidden','login','vibebp'),
                );
                $vibebp['components']['groups']['invite_type'] = array(
                    'pending'=>_x('Pending Invites','login','vibebp'),
                    'accepted'=>_x('Accepted invites','login','vibebp'),
                );
                $vibebp['components']['groups']['invite_sort'] = array(
                    'DESC'=>_x('Recently invited','login','vibebp'),
                    'ASC'=>_x('Last Invited','login','vibebp'),
                );
                $vibebp['components']['groups']['membertypes'] = array(
                    ''=>_x('All Members','login','vibebp'),
                    'mod'=>_x('Moderators','login','vibebp'),
                    'admin'=>_x('Administrators','login','vibebp'),
                    'banned'=>_x('Banned Users','login','vibebp'),
                    'invited'=>_x('Invited Users','login','vibebp'),
                );

                
                $vibebp['components']['groups']['invite_status'] = array(
                    'members'=>_x('All Group Members','login','vibebp'),
                    'mods'=>_x('Group Moderators and Administrators','login','vibebp'),
                    'admins'=>_x('Group Administrators only','login','vibebp'),
                );

                $vibebp['components']['groups']['nav'] = array(
                    'activty'=>_x('Activity','login','vibebp'),
                    'members'=>_x('Members','login','vibebp'),
                    'manage'=>_x('Manage','login','vibebp'),
                );
            }

            if(vibebp_get_setting('bp_followers','bp')){
                $vibebp['components']['followers']=array(
                    'label'=>__('Followers','vibebp'),
                    'sorters'=>array(
                        'active'=>_x('Last Active','login','vibebp'),
                        'popular'=>_x('Most Members','login','vibebp'),
                        'newest'=>_x('Newly Created','login','vibebp'),
                        'alphabetical'=>_x('Alphabetical','login','vibebp'),
                    ),
                );
            }



            if(function_exists('WC')){
                $vibebp['components']['shop']=array(
                    'label'=>__('Shop','vibebp'),
                    'order_type'=>array(
                        'shop_order_refund'=>__('Refunds','vibebp')
                    ),
                    'order_statuses'=>wc_get_order_statuses(),
                );
            }
            $vibebp['components']=apply_filters('vibebp_active_compontents',$vibebp['components']);
        }
        return apply_filters('vibebp_vars',$vibebp);
    }

    function enqueue_head(){
        

        if(!vibebp_get_setting('global_login') && !apply_filters('vibebp_enqueue_login_script',false))
            return;

        wp_enqueue_script('wp-element');
        wp_enqueue_script('wp-data');
        
        wp_enqueue_script('localforage',plugins_url('../assets/js/localforage.min.js',__FILE__),array(),VIBEBP_VERSION,true);
        wp_enqueue_style('vicons',plugins_url('../assets/vicons.css',__FILE__),array(),VIBEBP_VERSION);
        wp_enqueue_script('tus',plugins_url('../assets/js/tus.min.js',__FILE__),array(),VIBEBP_VERSION,true);
        
        wp_enqueue_script('cropprjs',plugins_url('../assets/js/croppr.min.js',__FILE__),array(),VIBEBP_VERSION,true);

        wp_enqueue_script('vibebplogin',plugins_url('../assets/js/login.js',__FILE__),array('wp-element','wp-data','localforage'),VIBEBP_VERSION);

        wp_enqueue_style('vibebp_main',plugins_url('../assets/css/front.css',__FILE__),array(),VIBEBP_VERSION);

        if(vibebp_get_setting('firebase_config')){

            wp_enqueue_script('firebase',plugins_url('../assets/js/firebase-app.js',__FILE__),array(),VIBEBP_VERSION);
            wp_enqueue_script('firebase-auth',plugins_url('../assets/js/firebase-auth.js',__FILE__),array(),VIBEBP_VERSION);
            wp_enqueue_script('firebase-database',plugins_url('../assets/js/firebase-database.js',__FILE__),array(),VIBEBP_VERSION);
            wp_enqueue_script('firebase-messaging',plugins_url('../assets/js/firebase-messaging.js',__FILE__),array(),VIBEBP_VERSION);

            wp_enqueue_script('vibebp_live',plugins_url('../assets/js/live.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);

            $blog_id = '';
            if(function_exists('get_current_blog_id')){
                $blog_id = get_current_blog_id();
            }

            wp_localize_script('vibebp_live','vibelive',array(
                'api'=>array(
                    'url'=> apply_filters('vibebp_rest_api',get_rest_url($blog_id,Vibe_BP_API_NAMESPACE)),
                ),
                'settings'=>array(
                    'upload_limit'=>wp_max_upload_size(),
                ),
                'translations'=>array(
                    'add_new_note'=>__('Add new Note', 'vibebp'),
                    'set_reminders'=>__('Set Reminders', 'vibebp'),
                    'add_note'=>__('Add Note', 'vibebp'),
                    'edit_note'=>__('Edit Note', 'vibebp'),
                    'no_notifications'=>__('No more notifications !', 'vibebp'),
                    'mark_all_read'=>__('Mark all read', 'vibebp'),
                    'delete_all'=>__('Delete all', 'vibebp'),
                    'no_members_online'=>__('No members online.', 'vibebp'),
                    'send'=>__('Send', 'vibebp'),
                    'cancel'=>__('Cancel', 'vibebp'),
                    'mychats'=>__('MyChats', 'vibebp'),
                    'online'=>__('Online', 'vibebp'),
                    'start_new_chat'=>__('Start new chat', 'vibebp'),
                    'back_to_all_chats'=>__('Back to all chats', 'vibebp'),
                    'exit_chat'=>__('Exit Chat', 'vibebp'),
                    'add_new_message'=>__('Add new message', 'vibebp'),
                    'invited'=>__('Invited', 'vibebp'),
                    'is_typing'=>__('is typing', 'vibebp'),
                    'members_online'=>__(' Members Online !', 'vibebp'),
                    'select_attachment'=>__('Select Attachment', 'vibebp'),
                    'attachment_size_error'=>sprintf(_x('Attachment size should be less than upload limit %s','login','vibebp'),'( '.floor(wp_max_upload_size()/1024).' kb )'),
                )
            ));
        }
        wp_localize_script('vibebplogin','vibebp',$this->get_vibebp());

        
        
        

        if(function_exists('bp_is_user') && bp_is_user() || apply_filters('vibebp_enqueue_profile_script',false)){
            //wp_dequeue_script('jquery');

            wp_enqueue_script('flatpickr',plugins_url('../assets/js/flatpickr.min.js',__FILE__),array(),VIBEBP_VERSION,true);
            wp_enqueue_script('colorpickr',plugins_url('../assets/js/vanilla-picker.min.js',__FILE__),array(),VIBEBP_VERSION,true);
            wp_enqueue_script('plyr',plugins_url('../assets/js/plyr.js',__FILE__),array(),VIBEBP_VERSION,true);
            wp_enqueue_script('vibebpprofile',plugins_url('../assets/js/profile.js',__FILE__),array('wp-element','wp-data'),VIBEBP_VERSION,true);
            wp_enqueue_style('vibebp_profile_libs',plugins_url('../assets/css/profile.css',__FILE__),array(),VIBEBP_VERSION);

            wp_enqueue_style('vicons',plugins_url('../assets/vicons.css',__FILE__),array(),VIBEBP_VERSION);
            wp_enqueue_style('plyr',plugins_url('../assets/css/plyr.css',__FILE__),array(),VIBEBP_VERSION);
            wp_enqueue_script('vibe_editor',plugins_url('../assets/js/editor.js',__FILE__),array(),VIBEBP_VERSION,true);

            wp_localize_script('vibe_editor','vibeEditor',array(
                'media_order'=>array(
                    'recent'=>__('Recently uploaded','vibebp'),
                    'alphabetical'=>__('Alphabetical','vibebp'),
                ),
                'embed_types'=>array(
                    'image'=> __('image','vibebp'),
                    'video'=> __('Video','vibebp'),
                    'audio'=> __('Audio','vibebp'),
                    'file'=> __('File','vibebp')
                ),
                'shortcodes' => array(
                    array(
                        'key'=> 'note',
                        'title'=> __('Note','vibebp'),
                        'icon'=> 'vicon vicon-notepad',
                        'attributes'=>array(
                            array(
                                'field_type'=> 'number',
                                'output'=> 'style',
                                'label'=> __('Margin','vibebp'),
                                'parameter'=> 'margin',
                                'suffix'=> 'px',
                                'default'=> 0
                            ),
                            array(
                                'field_type'=> 'number',
                                'output'=> 'style',
                                'label'=> __('Padding','vibebp'),
                                'parameter'=> 'padding',
                                'suffix'=> 'px',
                                'default'=> 0
                            ),
                            array(
                                'field_type'=> 'color',
                                'output'=> 'style',
                                'label'=> __('Background','vibebp'),
                                'parameter'=> 'background',
                                'default'=> ''
                            ),
                            array(
                                'field_type'=> 'color',
                                'output'=> 'style',
                                'label'=> __('Color','vibebp'),
                                'parameter'=> 'color',
                                'default'=> ''
                            )
                        )
                    ),
                    array(
                        'key'=> 'tab',
                        'title'=> __('Tab','vibebp'),
                        'icon'=> 'vicon vicon-layout-tab',
                        'tabs'=> []  // [{title:'',content:'editordata}]
                    ),
                    array(
                        'key'=> 'accordion',
                        'title'=> __('Accordion','vibebp'),
                        'icon'=> 'vicon vicon-layout-accordion-merged',
                        'accordions'=> []  // [{title:'',content:'editordata}]
                    ),
                    array(
                        'key'=> 'vibe_form',
                        'title'=> __('Forms','vibebp'),
                        'icon'=> 'vicon vicon-layout-accordion-merged',
                        'forms'=> [] // [{id,content,..}]
                    )
                ),
                'translations'=>array(
                    'add_tab'=> __('Add Tab','vibebp'),
                    'add_accordion_tab'=> __('Add Accordion Tab','vibebp'),
                    'enter_title'=> __('Enter Title...','vibebp'),
                    'search'=> __('Search...','vibebp'),
                    'view_form'=> __('View Form','vibebp'),
                    'advance_elements'=> __('Advance Elements','vibebp'),
                )
            ));
            wp_enqueue_style('vibe_editor',plugins_url('../assets/css/editor.css',__FILE__),array(),VIBEBP_VERSION);
        }
    }
}
VibeBP_Register::init();

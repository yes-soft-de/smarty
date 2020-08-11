<?php
/*
Plugin Name:  Broadcast Live Video - HTML5 Live Streaming
Plugin URI: https://videowhisper.com/?p=WordPress+Live+Streaming
Description: <strong>Broadcast Live Video / HTML5 Live Streaming : HTML5, WebRTC, HLS, RTSP, RTMP</strong> solution powers a turnkey live streaming channels site including web based webcam broadcasting app and player with chat, support for external apps, 24/7 RTSP ip cameras, WebRTC, video playlist scheduler, video archiving & vod, HLS & MPEG-DASH delivery for mobile including AJAX chat, membership and access control, pay per view channels and tips/gifts for broadcasters. <a href='https://videowhisper.com/tickets_submit.php?topic=Live-Streaming'>Contact Support</a> | <a href='admin.php?page=live-streaming&tab=setup'>Setup</a> 
Version: 5.4.66
Author: VideoWhisper.com
Author URI: https://videowhisper.com/
Contributors: videowhisper, VideoWhisper.com, BroadcastLiveVideo.com
Text Domain: live-streaming
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once(plugin_dir_path( __FILE__ ) .'/inc/options.php');
require_once(plugin_dir_path( __FILE__ ) .'/inc/requirements.php');
require_once(plugin_dir_path( __FILE__ ) .'/inc/iptv.php');

use VideoWhisper\LiveStreaming;


if (!class_exists("VWliveStreaming"))
{
	class VWliveStreaming {

		use VideoWhisper\LiveStreaming\Options;
		use VideoWhisper\LiveStreaming\Requirements;
		use VideoWhisper\LiveStreaming\IPTV;

		public function __construct()
		{
		}

		public function VWliveStreaming() { //constructor
			self::__construct();

		}

		static function install() {
			// do not generate any output here

			VWliveStreaming::channel_post();
			flush_rewrite_rules();
		}

		function settings_link($links) {
			$settings_link = '<a href="admin.php?page=live-streaming">'.__("Settings").'</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		function init()
		{
			//setup post
			VWliveStreaming::channel_post();

			//prevent wp from adding <p> that breaks JS
			remove_filter ('the_content',  'wpautop');

			//move wpautop filter to BEFORE shortcode is processed
			add_filter( 'the_content', 'wpautop' , 1);

			//then clean AFTER shortcode
			add_filter( 'the_content', 'shortcode_unautop', 100 );

			self::setupSchedule();

		}


		function plugins_loaded()
		{

			//update user active

			//user access update (updates with 10s precision)
			if (is_user_logged_in())
			{
				$ztime = time();
				$userID = get_current_user_id();
				
				//this user's access time
				$accessTime = intval(get_user_meta($userID, 'accessTime', true));
				if ($ztime - $accessTime > 10) update_user_meta($userID, 'accessTime', $ztime);
				
				//any user access time
				$userAccessTime = intval(get_option('userAccessTime', 0));
				if ($ztime - $accessTime > 10) update_option('userAccessTime', $ztime);
			}

			$plugin = plugin_basename(__FILE__);
			add_filter("plugin_action_links_$plugin",  array('VWliveStreaming','settings_link') );

			//translations
			load_plugin_textdomain('live-streaming', false, dirname(plugin_basename(__FILE__)) .'/languages');


			//widget
			wp_register_sidebar_widget('liveStreamingWidget','VideoWhisper Streaming', array('VWliveStreaming', 'widget') );

			//channel page
			add_filter('the_title', array('VWliveStreaming','the_title'));
			add_filter('the_content', array('VWliveStreaming','channel_page'));
			add_filter('query_vars', array('VWliveStreaming','query_vars'));
			add_filter('pre_get_posts', array('VWliveStreaming','pre_get_posts'));

			//admin channels
			add_filter('manage_channel_posts_columns', array( 'VWliveStreaming', 'columns_head_channel') , 10);
			add_filter( 'manage_edit-channel_sortable_columns', array('VWliveStreaming', 'columns_register_sortable') );
			add_action('manage_channel_posts_custom_column', array( 'VWliveStreaming', 'columns_content_channel') , 10, 2);
			add_filter( 'request', array('VWliveStreaming', 'duration_column_orderby') );

			//shortcodes
			add_shortcode('videowhisper_categories', array( 'VWliveStreaming', 'videowhisper_categories'));

			add_shortcode('videowhisper_channel_user',array( 'VWliveStreaming', 'videowhisper_channel_user'));

			add_shortcode('videowhisper_stream_setup', array( 'VWliveStreaming', 'videowhisper_stream_setup'));


			add_shortcode('videowhisper_livesnapshots', array( 'VWliveStreaming', 'shortcode_livesnapshots'));
			add_shortcode('videowhisper_broadcast', array( 'VWliveStreaming', 'videowhisper_broadcast'));

			add_shortcode('videowhisper_external', array( 'VWliveStreaming', 'videowhisper_external'));
			add_shortcode('videowhisper_external_broadcast', array( 'VWliveStreaming', 'videowhisper_external_broadcast'));
			add_shortcode('videowhisper_external_playback', array( 'VWliveStreaming', 'videowhisper_external_playback'));

			add_shortcode('videowhisper_watch', array( 'VWliveStreaming', 'videowhisper_watch'));
			add_shortcode('videowhisper_video', array( 'VWliveStreaming', 'videowhisper_video'));

			add_shortcode('videowhisper_hls', array( 'VWliveStreaming', 'videowhisper_hls'));
			add_shortcode('videowhisper_mpeg', array( 'VWliveStreaming', 'videowhisper_mpeg'));

			add_shortcode('videowhisper_channel_manage',array( 'VWliveStreaming', 'videowhisper_channel_manage'));
			add_shortcode('videowhisper_channels',array( 'VWliveStreaming', 'videowhisper_channels'));

			add_shortcode('videowhisper_webrtc_broadcast', array( 'VWliveStreaming', 'videowhisper_webrtc_broadcast'));
			add_shortcode('videowhisper_webrtc_playback', array( 'VWliveStreaming', 'videowhisper_webrtc_playback'));

			add_shortcode('videowhisper_htmlchat_playback', array( 'VWliveStreaming', 'videowhisper_htmlchat_playback'));


			add_action( 'before_delete_post',  array( 'VWliveStreaming','before_delete_post') );

			//notify admin about requirements
			if( current_user_can( 'administrator' ) ) self::requirements_plugins_loaded();

			//ajax

			//categories
			add_action( 'wp_ajax_vwls_categories', array('VWliveStreaming','vwls_categories'));
			add_action( 'wp_ajax_nopriv_vwls_categories', array('VWliveStreaming','vwls_categories'));

			//ip camera / re-stream setup
			add_action( 'wp_ajax_vwls_stream_setup', array('VWliveStreaming','vwls_stream_setup'));
			add_action( 'wp_ajax_nopriv_vwls_stream_setup', array('VWliveStreaming','vwls_stream_setup'));

			add_action( 'wp_ajax_vwls_playlist', array('VWliveStreaming','vwls_playlist') );
			add_action( 'wp_ajax_nopriv_vwls_playlist', array('VWliveStreaming','vwls_playlist'));

			add_action( 'wp_ajax_vwls_trans', array('VWliveStreaming','vwls_trans') );
			add_action( 'wp_ajax_nopriv_vwls_trans', array('VWliveStreaming','vwls_trans'));

			add_action( 'wp_ajax_vwls_broadcast', array('VWliveStreaming','vwls_broadcast'));

			add_action( 'wp_ajax_vwls', array('VWliveStreaming','vwls_calls'));
			add_action( 'wp_ajax_nopriv_vwls', array('VWliveStreaming','vwls_calls'));

			add_action( 'wp_ajax_vwls_channels', array('VWliveStreaming','vwls_channels'));
			add_action( 'wp_ajax_nopriv_vwls_channels', array('VWliveStreaming','vwls_channels'));


			add_action( 'wp_ajax_vwls_htmlchat', array('VWliveStreaming','wp_ajax_vwls_htmlchat') );
			add_action( 'wp_ajax_nopriv_vwls_htmlchat', array('VWliveStreaming','wp_ajax_vwls_htmlchat') );



			//jquery for ajax
			add_action( 'wp_enqueue_scripts', array('VWliveStreaming','wp_enqueue_scripts') );

			//update page if not exists or deleted
			$page_id = get_option("vwls_page_manage");
			$page_id2 = get_option("vwls_page_channels");

			//check db and update if necessary
			$vw_db_version = "5.3.17";

			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_sessions";
			$table_viewers = $wpdb->prefix . "vw_lwsessions";
			$table_channels = $wpdb->prefix . "vw_lsrooms";


			$table_chatlog = $wpdb->prefix . "vw_vwls_chatlog";

			$installed_ver = get_option( "vwls_db_version" );

			if( $installed_ver != $vw_db_version )
			{

				//echo "---$installed_ver != $vw_db_version---";

				$wpdb->flush();

				$sql = "DROP TABLE IF EXISTS `$table_sessions`;
		CREATE TABLE `$table_sessions` (
		  `id` int(11) NOT NULL auto_increment,
		  `session` varchar(64) NOT NULL,
		  `username` varchar(64) NOT NULL,
		  `uid` int(11) NOT NULL,
		  `room` varchar(64) NOT NULL,
		  `message` text NOT NULL,
		  `sdate` int(11) NOT NULL,
		  `edate` int(11) NOT NULL,
		  `status` tinyint(4) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `status` (`status`),
		  KEY `type` (`type`),
		  KEY `uid` (`uid`),
		  KEY `room` (`room`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Video Whisper: Broadcaster Sessions - 2009@videowhisper.com' AUTO_INCREMENT=1 ;

		DROP TABLE IF EXISTS `$table_viewers`;
		CREATE TABLE `$table_viewers` (
		  `id` int(11) NOT NULL auto_increment,
		  `session` varchar(64) NOT NULL,
		  `username` varchar(64) NOT NULL,
		  `uid` int(11) NOT NULL,
		  `room` varchar(64) NOT NULL,
		  `rid` int(11) NOT NULL,
		  `rsdate` int(11) NOT NULL,
		  `redate` int(11) NOT NULL,
		  `roptions` text NOT NULL,
		  `rmode` tinyint(4) NOT NULL,
		  `message` text NOT NULL,
		  `ip` text NOT NULL,
		  `sdate` int(11) NOT NULL,
		  `edate` int(11) NOT NULL,
		  `status` tinyint(4) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `status` (`status`),
		  KEY `type` (`type`),
		  KEY `rid` (`rid`),
		  KEY `uid` (`uid`),
		  KEY `rmode` (`rmode`),
		  KEY `rsdate` (`rsdate`),
		  KEY `redate` (`redate`),
		  KEY `sdate` (`sdate`),
		  KEY `edate` (`edate`),
		  KEY `room` (`room`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Video Whisper: Sessions - 2015@videowhisper.com' AUTO_INCREMENT=1 ;


		DROP TABLE IF EXISTS `$table_channels`;
		CREATE TABLE `$table_channels` (
		  `id` int(11) NOT NULL auto_increment,
		  `name` varchar(64) NOT NULL,
		  `owner` int(11) NOT NULL,
		  `sdate` int(11) NOT NULL,
		  `edate` int(11) NOT NULL,
		  `btime` int(11) NOT NULL,
		  `wtime` int(11) NOT NULL,
		  `rdate` int(11) NOT NULL,
		  `status` tinyint(4) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  `options` TEXT,
		  PRIMARY KEY  (`id`),
		  KEY `name` (`name`),
		  KEY `status` (`status`),
		  KEY `type` (`type`),
		  KEY `owner` (`owner`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Video Whisper: Rooms - 2014@videowhisper.com' AUTO_INCREMENT=1 ;

		DROP TABLE IF EXISTS `$table_chatlog`;
		CREATE TABLE `$table_chatlog` (
		  `id` int(11) unsigned NOT NULL auto_increment,
		  `username` varchar(64) NOT NULL,
		  `room` varchar(64) NOT NULL,
		  `message` text NOT NULL,
		  `mdate` int(11) NOT NULL,
		  `type` tinyint(4) NOT NULL,
		  `meta` TEXT,
		  `user_id` int(11) unsigned NOT NULL,
		  PRIMARY KEY  (`id`),
		  KEY `room` (`room`),
		  KEY `mdate` (`mdate`),
		  KEY `type` (`type`),
		  KEY `user_id` (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Video Whisper: Chat Logs - 2018@videowhisper.com' AUTO_INCREMENT=1;

		";

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);

				if (!$installed_ver) add_option("vwls_db_version", $vw_db_version);
				else update_option( "vwls_db_version", $vw_db_version );

				$wpdb->flush();
			}


		}
		/*
		function delTree($dir) {
			$files = array_diff(scandir($dir), array('.','..'));
			foreach ($files as $file) {
				(is_dir("$dir/$file")) ? VWliveStreaming::delTree("$dir/$file") : unlink("$dir/$file");
			}
			return rmdir($dir);
		}
*/

		function before_delete_post($postID)
		{
			$options = get_option('VWliveStreamingOptions');
			if (get_post_type( $postID ) != $options['custom_post']) return;

			$post = get_post( $postID );


			//delete from room table
			$room = sanitize_file_name($post->post_title);

			global $wpdb;
			$table_channels = $wpdb->prefix . "vw_lsrooms";
			$sql = "DELETE FROM $table_channels where name='$room'";

			$wpdb->query($sql);




		}


		function login_headerurl($url) {

			return get_bloginfo( "url" ) . "/";
		}

		function login_enqueue_scripts() {

			$options = get_option('VWliveStreamingOptions');

			if ($options['loginLogo'])
			{
?>
    <style type="text/css">
         #login h1 a, .login h1 a  {
            background-image: url(<?php echo $options['loginLogo']; ?>);
			background-size: 200px 68px;
			width: 200px;
			height: 68px;
        }
    </style>
  	<?php
			}
			/*			else
			{
?>
	    <style type="text/css">
        #login h1 a, .login h1 a {
            background-image: url(<?php echo get_stylesheet_directory_uri(); ?>/images/site-login-logo.png);
            padding-bottom: 30px;
        }
    </style>
	    <?php

			}*/

		}



		//! set fc

		//string contains any term for list (ie. banning)
		function containsAny($name, $list)
		{
			$items = explode(',', $list);
			foreach ($items as $item) if (stristr($name, trim($item))) return $item;

				return 0;
		}


		//if any key matches any listing
		function inList($keys, $data)
		{
			if (!$keys) return 0;
			if (!$data) return 0;
			if (strtolower(trim($data)) == 'all') return 1;
			if (strtolower(trim($data)) == 'none') return 0;

			$list=explode(",", strtolower(trim($data)));
			if (in_array('all', $list)) return 1;

			foreach ($keys as $key)
				foreach ($list as $listing)
					if ( strtolower(trim($key)) == trim($listing) ) return 1;

					return 0;
		}

		//! room fc
		static function roomURL($room)
		{

			$options = get_option('VWliveStreamingOptions');

			if ($options['channelUrl'] == 'post')
			{
				global $wpdb;

				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($room) . "' and post_type='channel' LIMIT 0,1" );

				if ($postID) return get_post_permalink($postID);
			}

			if ($options['channelUrl'] == 'full') return site_url('/fullchannel/' . urlencode($room));

			return plugin_dir_url(__FILE__) . 'ls/channel.php?n=' . urlencode(sanitize_file_name($room));

		}

		function count_user_posts_by_type( $userid, $post_type = 'channel' )
		{
			global $wpdb;
			$where = get_posts_by_author_sql( $post_type, true, $userid );
			$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );
			return apply_filters( 'get_usernumposts', $count, $userid );
		}


		//! Channel Validation

		static function channelInvalid( $channel, $broadcast =false)
		{
			//check if online channel is invalid for any reason

			if (!function_exists('fm'))
			{

				function fm($t, $item = null)
				{
					$img = '';

					if ($item)
					{
						$options = get_option('VWliveStreamingOptions');
						$dir = $options['uploadsPath']. "/_thumbs";
						$age = VWliveStreaming::format_age(time() -  $item->edate);
						$thumbFilename = "$dir/" . $item->name . ".jpg";

						$noCache = '';
						if ($age=='LIVE') $noCache='?'.((time()/10)%100);

						if (file_exists($thumbFilename)) $img = '<IMG ALIGN="RIGHT" src="' . VWliveStreaming::path2url($thumbFilename) . $noCache .'" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px"><br style="clear:both">';
					}

					//format message
					return  '<div class="w-actionbox color_alternate">'. $t . $img . '</div><br>';
				}
			}

			$channel = sanitize_file_name($channel);
			if (!$channel) return fm('No channel name!');

			global $wpdb;
			$table_channels = $wpdb->prefix . "vw_lsrooms";

			$sql = "SELECT * FROM $table_channels where name='$channel'";
			$channelR = $wpdb->get_row($sql);

			if (!$channelR) if ($broadcast) return; //first broadcast
				else return ; //always show //return fm('Channel was not found! Live channel is only accessible after broadcast.', $channelR);

				$options = get_option('VWliveStreamingOptions');

			if ($channelR->type >=2) //premium
				{
				$poptions = VWliveStreaming::channelOptions($channelR->type, $options);

				$maximumBroadcastTime =  60 * $poptions['pBroadcastTime'];
				$maximumWatchTime =  60 * $poptions['pWatchTime'];

				$canWatch = $poptions['canWatchPremium'];
				$watchList = $poptions['watchListPremium'];
			}
			else
			{
				$maximumBroadcastTime =  60 * $options['broadcastTime'];
				$maximumWatchTime =  60 * $options['watchTime'];

				$canWatch = $options['canWatch'];
				$watchList = $options['watchList'];
			}

			if (!$broadcast)
			{
				if ($maximumWatchTime) if ($channelR->wtime >= $maximumWatchTime) return fm('Channel watch time exceeded for current period! Higher broadcaster membership is required to stream more.', $channelR);

			}
			else if ($maximumBroadcastTime) if ($channelR->btime >= $maximumBroadcastTime) return fm('Channel broadcast time exceeded for current period! Higher broadcaster membership is required to stream more.');


					//user access validation

					$current_user = wp_get_current_user();


				if ($current_user->ID != 0) //logged in
					{
					//access keys
					$userkeys = $current_user->roles;
					$userkeys[] = $current_user->ID;
					$userkeys[] = $current_user->user_email;
					$userkeys[] = $current_user->user_login;
				}
			else $userkeys[] = 'Guest';

			//global access settings
			switch ($canWatch)
			{
			case "members":
				if (!$current_user->ID) return fm('Only registered members can access this channel!');
				break;

			case "list";
				if (!$current_user->ID || !VWliveStreaming::inList($userkeys, $watchList))
					return fm('Access restricted by global access list!');
				break;
			}



			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $channel . "' and post_type='channel' LIMIT 0,1" );

			if ($postID)    //post validations
				{
				//accessPassword
				if (post_password_required($postID)) return fm('Access to channel is restricted by password!');

				// channel access list
				$accessList = get_post_meta($postID, 'vw_accessList', true);
				if ($accessList) if (!VWliveStreaming::inList($userkeys, $accessList)) return fm('Access restricted by channel access list!');
					//playlist active or ip camera
					$playlistActive = get_post_meta( $postID, 'vw_playlistActive', true );
				$ipCamera = get_post_meta( $postID, 'vw_ipCamera', true );
			}

			if (!$broadcast)  if (!VWliveStreaming::userPaidAccess($current_user->ID, $postID)) return fm('Access restricted: channel access needs to be purchased!');


				if (!$broadcast) if (!$options['alwaysWatch']) if (!$playlistActive && !$ipCamera)
							if (time() - $channelR->edate > 45)
							{
								$age = VWliveStreaming::format_age(time() -  $channelR->edate);

								$htmlCode ='This channel is currently offline. ';

								$eventCode = VWliveStreaming::eventInfo($postID);

								if ($eventCode)
									$eventCode = 'Come back and reload page when event starts!' . $eventCode;
								else $eventCode .= ' Try again later! Time offline: ' . $age;

								return fm($htmlCode . $eventCode, $channelR );
							}

						//valid then
						return ;

		}

		function getCurrentURL()
		{
			/*
			$currentURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
			$currentURL .= $_SERVER["SERVER_NAME"];

			if($_SERVER["SERVER_PORT"] != "80" && $_SERVER["SERVER_PORT"] != "443")
			{
				$currentURL .= ":".$_SERVER["SERVER_PORT"];
			}

			$uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);

			$currentURL .= $uri_parts[0];

			return $currentURL;
			*/
			global $wp;
			return home_url(add_query_arg(array(),$wp->request));
		}


		static function vsvVideoURL($video_teaser, $options = null)
		{
			if (!$video_teaser) return '';

			if (!$options) $options = get_option('VWliveStreamingOptions');
			$streamPath = '';

			//use conversion if available
			$videoAdaptive = get_post_meta($video_teaser, 'video-adaptive', true);
			if ($videoAdaptive) $videoAlts = $videoAdaptive;
			else $videoAlts = array();

			foreach (array('high', 'mobile') as $frm)
				if (array_key_exists($frm, $videoAlts))
					if ($alt = $videoAlts[$frm])
						if (file_exists($alt['file']))
						{
							$ext = pathinfo($alt['file'], PATHINFO_EXTENSION);
							if ($options['hls_vod']) $streamPath = self::path2stream($alt['file']);
							else $streamPath = self::path2url($alt['file']);
							break;
						};

				//user original
				if (!$streamPath)
				{
					$videoPath = get_post_meta($video_teaser, 'video-source-file', true);
					$ext = pathinfo($videoPath, PATHINFO_EXTENSION);

					if (in_array($ext, array('flv','mp4','m4v')))
					{
						//use source if compatible
						if ($options['hls_vod']) $streamPath = self::path2stream($videoPath);
						else $streamPath = self::path2url($videoPath);
					}
				}

			if ($options['hls_vod']) $streamURL = $options['hls_vod'] . '_definst_/' . $streamPath .'/manifest.mpd';
			else $streamURL = $streamPath;


			return $streamURL;
		}
		
		//! Shortcodes



		static function loginRequiredWarning()
		{
			return __('Login required: Please login first or register an account if you do not have one!', 'live-streaming') .
				'<BR><a class="ui button" href="' . wp_login_url() . '">' . __('Login', 'live-streaming') . '</a>  <a class="ui button" href="' . wp_registration_url() . '">' . __('Register', 'live-streaming') . '</a>';
		}

		function videowhisper_channel_user()
		{
			//automatically creates a user channel (if missing) and displays broadcasting interface

			//can user create room?
			$options = get_option('VWliveStreamingOptions');

			$canBroadcast = $options['canBroadcast'];
			$broadcastList = $options['broadcastList'];
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

			$loggedin=0;

			$current_user = wp_get_current_user();

			if ($current_user->$userName) $username = $current_user->$userName;

			//access keys
			$userkeys = $current_user->roles;
			$userkeys[] = $current_user->user_login;
			$userkeys[] = $current_user->ID;
			$userkeys[] = $current_user->user_email;

			switch ($canBroadcast)
			{
			case "members":
				if ($username) $loggedin=1;
				else $htmlCode .= VWliveStreaming::loginRequiredWarning();
				break;
			case "list";
				if ($username)
					if (VWliveStreaming::inList($userkeys, $broadcastList)) $loggedin=1;
					else $htmlCode .= "<a href=\"/\">$username, you are not allowed to setup rooms.</a>";
					else $htmlCode .= VWliveStreaming::loginRequiredWarning();
					break;
			}

			if (!$loggedin)
			{
				$htmlCode .='<p>' . __('This displays a broadcasting channel for registered members that have this feature enabled.', 'live-streaming') . '</p>';
				return $htmlCode;
			}



			//channel with same name as $username

			global $wpdb;
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE `post_title` = '$username' AND `post_type`='".$options['custom_post']."' LIMIT 0,1" ); //same name

			if (!$postID)
			{
				$post = array(
					'post_name'      => $username,
					'post_title'     => $username,
					'post_author'    => $current_user->ID,
					'post_type'      => $options['custom_post'],
					'post_status'    => 'publish',
				);

				$postID = wp_insert_post($post);
			}

			$channel = get_post( $postID );
			if (!$channel) return "Error: Channel #$postID does not exist!";

			if ($channel->post_author != $current_user->ID) return 'Error: Channel ' . $channel->post_title . ' exists but belongs to different user '.  $channel->post_author .'!';

			// display broadcasting channel

			return do_shortcode('[videowhisper_broadcast channel="' . $channel->post_title . '"]');

		}

		function videowhisper_categories($atts)
		{

			$options = get_option('VWliveStreamingOptions');

			$atts = shortcode_atts(
				array(
					'selected' => '',
				), $atts, 'videowhisper_categories');
				
				$selected = intval($atts['selected']);
				$parent = 0;

if ($options['subcategory'] == 'wordpress') return wp_dropdown_categories('show_count=0&echo=0&class=ui+dropdown&name=newcategory&hide_empty=0&hierarchical=1&selected=' . $selected);
			
				if ($selected)
				{
				$category = get_category($selected);
				if ($category) $parent = $category->parent;
				
				if ($category) $htmlCode .= '<div class="ui label">'.  $category ->name . '</div>';
				}
				
				
$htmlCode .= ' <div class="two fields">';
    
			$htmlCode .= '<div class="field">
<div id="mainCategoryDropdown" class="ui fluid search selection dropdown ajax">
  <input type="hidden" name="mainCategory">
  <i class="dropdown icon"></i>
  <div class="default text">Select Main Category</div>
  <div class="menu"></div>
 </div>
 </div>';
 
 			$htmlCode .= '<div class="field">
 <div id="newcategory" class="ui fluid search selection dropdown ajax">
  <input type="hidden" name="newcategory">
  <i class="dropdown icon"></i>
  <div class="default text">Select Category</div>
  <div class="menu"></div>
 </div>
 </div>';

$htmlCode .= '
 <a id="reloadCategories" class="ui icon button" data-tooltip="' . __('Reload', 'live-streaming') . '">
  <i class="redo icon"></i>
</a>';

$htmlCode .= '</div>';

			$admin_ajax = admin_url() . 'admin-ajax.php?action=vwls_categories';

			$htmlCode .= <<<HTMLCODE
<script>

var mainCategoryValue = '$parent';

	jQuery(document).ready(function () {


function loadCategories()
{  
jQuery.post( "$admin_ajax&parent=0", function( data ) 
	{	
	
	 jQuery('#mainCategoryDropdown').dropdown({
		values: JSON.parse(data),
	  	onChange: function(value, text, choice) 
	  	{
	
	mainCategoryValue = value;
	
	console.log('mainCategoryDropdown action', value, text, choice);
	jQuery.post( "$admin_ajax&sub=1&parent=" + value, function( data ) 
		{
		 jQuery('#newcategory').dropdown({values: JSON.parse(data)});
		 jQuery('#newcategory').dropdown('set selected', '$selected');
		});
		
		}
		//action
	  });
		  	
	jQuery('#mainCategoryDropdown').dropdown('set selected', '$parent');  
	//post	 
	});
}

jQuery('#reloadCategories').click(
	function()
	{
		mainCategoryValue = '$parent';	
		loadCategories();		
	}
);


loadCategories();

//ready
});

</script>
HTMLCODE;


			return $htmlCode;
		}

		function vwls_categories() //list channels
			{
			ob_clean();

			$options = get_option('VWliveStreamingOptions');

			$parent = intval($_GET['parent']);
			$sub = intval($_GET['sub']);

			$args = array(
				'orderby' => 'name',
				'order' => 'ASC',
				'hide_empty' => false,
				'parent' => $parent
			);

			$categories = get_categories($args);
			
			$res = array();
			
			if (!$parent && $options['subcategory'] == 'all' && !$sub) $res[] = array('name' => __('Main Categories', 'live-streaming') , 'value' => 0);
			
			if ($parent && $options['subcategory'] == 'all' && $sub) 
			{
				$category = get_category($parent);
				$res[] = array('name' => $category->name  . ' *', 'value' => $parent);
			}


			foreach ($categories as $category) 
			{
			$subcategories = get_categories( array('parent' => $category->term_id, 'hide_empty' => false) );
			if ($parent || count($subcategories) || $options['subcategory'] == 'all') $res[] = array('name' => $category->name . ($parent?'':' (' . count($subcategories) .')') , 'value' => $category->term_id);
			}

		//		echo json_encode(array('success' => true, 'results'=> $res));
		echo json_encode($res);
		

			exit;

		}





		function videowhisper_channel_manage()
		{
			//can user create room?
			$options = get_option('VWliveStreamingOptions');

			$maxChannels = $options['maxChannels'];

			$canBroadcast = $options['canBroadcast'];
			$broadcastList = $options['broadcastList'];
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

			$loggedin=0;

			$current_user = wp_get_current_user();

			if ($current_user->$userName) $username = $current_user->$userName;

			//access keys
			$userkeys = $current_user->roles;
			$userkeys[] = $current_user->user_login;
			$userkeys[] = $current_user->ID;
			$userkeys[] = $current_user->user_email;

			switch ($canBroadcast)
			{
			case "members":
				if ($username) $loggedin=1;
				else $htmlCode .= VWliveStreaming::loginRequiredWarning();
				break;
			case "list";
				if ($username)
					if (VWliveStreaming::inList($userkeys, $broadcastList)) $loggedin=1;
					else $htmlCode .= "<a href=\"/\">$username, you are not allowed to setup rooms.</a>";
					else $htmlCode .= VWliveStreaming::loginRequiredWarning();
					break;
			}

			if (!$loggedin)
			{
				$htmlCode .='<p>' . __('This pages allows creating and managing broadcasting channels for registered members that have this feature enabled.', 'live-streaming') . '</p>';
				return $htmlCode;
			}


			//premium options
			$poptions = VWliveStreaming::premiumOptions($userkeys, $options);
			if ($poptions['pMaxChannels']) $maxChannels = $poptions['pMaxChannels'];


			$this_page    =   VWliveStreaming::getCurrentURL();
			$channels_count = VWliveStreaming::count_user_posts_by_type($current_user->ID, $options['custom_post']);



			$deleteChannel = intval($_GET['deleteChannel']);
			if ($deleteChannel)
			{

				$post = get_post($deleteChannel);

				$htmlCode .= '<div class="ui segment">';
				$htmlCode .= '<div class="ui header">' . $post->post_title . '</div>';

				if ($_GET['confirmDelete'])
				{
					wp_trash_post($deleteChannel);
					$htmlCode .=  __('Channel was removed. Admins may delete this content after review.', 'live-streaming') ;
				}
				else
				{
					$htmlCode .=  __('This will remove channel from public access. Only administrator can completely delete channels. Associated data may be needed for report review, moderation purposes.', 'live-streaming') ;

					$htmlCode .= '<br><a class="ui button red" href="' . add_query_arg( array('deleteChannel'=> $deleteChannel, 'confirmDelete' => 1), $this_page) . '">' . __('Confirm Removal', 'live-streaming') . '</a>';
				}

				$htmlCode .=  '</div>';

			}


			//! save channel
			$postID = $_POST['editPost']; //-1 for new

			if ($postID) //create or update
				{

				$name = sanitize_file_name($_POST['newname']);

				global $wpdb;
				$existID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE `ID` <> $postID AND `post_title` = '$name' AND `post_type`='".$options['custom_post']."' LIMIT 0,1" ); //same name, diff than postID


				if ($postID <= 0 && $channels_count >= $maxChannels)
					$htmlCode .= "<div class='error'>" . __('Could not create the new channel', 'live-streaming') . ': Maximum '. $options['maxChannels']." channels allowed per user!</div>";
				elseif ($existID) $htmlCode .= "<div class='error'>" . __('Could not create the new channel', 'live-streaming') . ": A channel post with name '".$name."' already exists. Please use a different channel name!</div>";
				else
				{
					//$name = preg_replace("/[^\s\w]+/", '', $name);

					if ($_POST['ipCamera']) if (!strstr($name,'.stream')) $name .= '.stream';

						$comments = sanitize_file_name($_POST['newcomments']);

					//accessPassword
					$accessPassword ='';
					if (VWliveStreaming::inList($userkeys, $options['accessPassword']))
					{
						$accessPassword = sanitize_text_field($_POST['accessPassword']);
					}


					$post = array(
						'post_content'   => sanitize_text_field($_POST['description']),
						'post_name'      => $name,
						'post_title'     => $name,
						'post_author'    => $current_user->ID,
						'post_type'      => $options['custom_post'],
						'post_status'    => 'publish',
						'comment_status' => $comments,
						'post_password' => $accessPassword
					);

					$category = (int) $_POST['newcategory'];

					if ($postID>0)
					{
						$channel = get_post( $postID );
						if ($channel->post_author == $current_user->ID) $post['ID'] = $postID; //update
						else return "<div class='error'>Not allowed!</div>";
						$htmlCode .= "<div class='update'>Channel $name was updated!</div>";
					}
					else $htmlCode .= "<div class='update'>Channel $name was created!</div>";

					$postID = wp_insert_post($post);
					if ($postID) wp_set_post_categories($postID, array($category));

					$channels_count = VWliveStreaming::count_user_posts_by_type($current_user->ID, $options['custom_post']);


					//roomTags
					if (VWliveStreaming::inList($userkeys, $options['roomTags']))
					{
						$roomTags = sanitize_text_field($_POST['roomTags']);
						wp_set_post_tags( $postID, $roomTags, false);
					}


					//uploadPicture
					if (VWliveStreaming::inList($userkeys, $options['uploadPicture']))
					{

						if ($filename = $_FILES['uploadPicture']['tmp_name'])
						{

							$ext = strtolower(pathinfo($_FILES['uploadPicture']['name'], PATHINFO_EXTENSION));
							$allowed = array('jpg','jpeg','png','gif');
							if (!in_array($ext,$allowed)) return 'Unsupported file extension!';

							list($width, $height) = getimagesize($filename);

							if ($width && $height)
							{

								//delete previous image(s)
								VWliveStreaming::delete_associated_media($postID, true);

								//$htmlCode .= 'Generating thumb... ';
								$thumbWidth = $options['thumbWidth'];
								$thumbHeight = $options['thumbHeight'];

								$src = imagecreatefromstring(file_get_contents($filename));
								$tmp = imagecreatetruecolor($thumbWidth, $thumbHeight);

								$dir = $options['uploadsPath']. "/_pictures";
								if (!file_exists($dir)) mkdir($dir);

								$room_name = sanitize_file_name($channel->post_title);
								$thumbFilename = "$dir/$room_name.jpg";
								imagecopyresampled($tmp, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
								imagejpeg($tmp, $thumbFilename, 95);

								//detect tiny images without info
								if (filesize($thumbFilename)>5000) $picType = 1;
								else $picType = 2;

								//update post meta
								if ($postID)
								{
									update_post_meta($postID, 'hasPicture', $picType);
									update_post_meta($postID, 'hasSnapshot', 1); //so it gets listed
									update_post_meta($postID, 'edate', time() - 60);
								}

								//$htmlCode .= ' Updating picture... ' . $thumbFilename;

								//update post image
								if (!function_exists('wp_generate_attachment_metadata')) require ( ABSPATH . 'wp-admin/includes/image.php' );

								$wp_filetype = wp_check_filetype(basename($thumbFilename), null );

								$attachment = array(
									'guid' => $thumbFilename,
									'post_mime_type' => $wp_filetype['type'],
									'post_title' => $room_name,
									'post_content' => '',
									'post_status' => 'inherit'
								);

								$attach_id = wp_insert_attachment( $attachment, $thumbFilename, $postID );
								set_post_thumbnail($postID, $attach_id);




								//update post imaga data
								$attach_data = wp_generate_attachment_metadata( $attach_id, $thumbFilename );
								wp_update_attachment_metadata( $attach_id, $attach_data );


							}
						}

						$showImage = sanitize_file_name($_POST['showImage']);
						update_post_meta($postID, 'showImage', $showImage);

					}

					if (VWliveStreaming::inList($userkeys, $options['eventDetails']))
					{
						update_post_meta($postID, 'eventTitle', sanitize_text_field($_POST['eventTitle']));
						update_post_meta($postID, 'eventStart', sanitize_text_field($_POST['eventStart']));
						update_post_meta($postID, 'eventEnd', sanitize_text_field($_POST['eventEnd']));
						update_post_meta($postID, 'eventStartTime', sanitize_text_field($_POST['eventStartTime']));
						update_post_meta($postID, 'eventEndTime', sanitize_text_field($_POST['eventEndTime']));
						update_post_meta($postID, 'eventDescription', sanitize_text_field($_POST['eventDescription']));
					}

					//disable sidebar for themes that support this
					update_post_meta($postID, 'disableSidebar', true);

					//transcode
					if (VWliveStreaming::inList($userkeys, $options['transcode']))
						update_post_meta($postID, 'vw_transcode', '1');
					else update_post_meta($postID, 'vw_transcode', '0');


					//logoHide
					if (VWliveStreaming::inList($userkeys, $options['logoHide']))
						update_post_meta($postID, 'vw_logo', 'hide');
					else update_post_meta($postID, 'vw_logo', 'global');

					//logoCustom
					if (VWliveStreaming::inList($userkeys, $options['logoCustom']))
					{
						$logoImage = sanitize_text_field($_POST['logoImage']);
						update_post_meta($postID, 'vw_logoImage', $logoImage);

						$logoLink = sanitize_text_field($_POST['logoLink']);
						update_post_meta($postID, 'vw_logoLink', $logoLink);

						update_post_meta($postID, 'vw_logo', 'custom');
					}

					//adsHide
					if (VWliveStreaming::inList($userkeys, $options['adsHide']))
						update_post_meta($postID, 'vw_ads', 'hide');
					else update_post_meta($postID, 'vw_ads', 'global');


					//adsCustom
					if (VWliveStreaming::inList($userkeys, $options['adsCustom']))
					{
						$logoImage = sanitize_text_field($_POST['adsServer']);
						update_post_meta($postID, 'vw_adsServer', $logoImage);

						update_post_meta($postID, 'vw_ads', 'custom');
					}

					//ipCameras
					if (VWliveStreaming::inList($userkeys, $options['ipCameras']))
					{
						if (file_exists($options['streamsPath']))
						{
							$ipCamera = sanitize_text_field($_POST['ipCamera']);


							if ($ipCamera)
							{
								list($protocol) = explode(':', $ipCamera);
								if (!in_array($protocol, array('rtsp','udp','rtmp','rtmps','wowz','wowzs', 'http', 'https')))
								{
									$htmlCode .= "<BR>Address format not supported ($protocol). Address should use one of these protocols: rtsp://, udp://, rtmp://, rtmps://, wowz://, wowzs://, http://, https:// .";
									$ipCamera = '';

								}
							}

							if ($ipCamera)  if (!strstr($name,'.stream'))
								{
									$htmlCode .= "<BR>Channel name must end in .stream when re-streaming!";
									$ipCamera = '';
								}

							$file = $options['streamsPath'] . '/' . $name;

							if ($ipCamera)
							{

								$myfile = fopen($file, "w");
								if ($myfile)
								{
									fwrite($myfile, $ipCamera);
									fclose($myfile);
									$htmlCode .= '<BR>Stream file created/updated:<br>' . $name . ' = ' . $ipCamera;
								}
								else
								{
									$htmlCode .= '<BR>Could not write file: '. $file;
									$ipCamera = '';
								}

							}
							else
							{
								if (file_exists($file))
								{
									unlink($file);
									$htmlCode .= '<BR>Stream file removed: '. $file;
								}
							}

							update_post_meta($postID, 'vw_ipCamera', $ipCamera);
							if ($ipCamera)
							{
								update_post_meta( $postID, 'stream-protocol', $protocol );
								update_post_meta( $postID, 'stream-type', 'restream' );
								update_post_meta( $postID, 'stream-mode', 'stream' );
							}

						}
						else
						{
							$htmlCode .= '<BR>Stream file could not be setup. Streams folder not found: '. $options['streamsPath'];
						}
					}
					else update_post_meta($postID, 'vw_ipCamera', '');

					//schedulePlaylists
					if (!$options['playlists'] || !VWliveStreaming::inList($userkeys, $options['schedulePlaylists']))
						update_post_meta($postID, 'vw_playlistActive', '');


					//permission lists: access, chat, write, participants, private
					foreach (array('access','chat','write','participants','privateChat') as $field)
						if (VWliveStreaming::inList($userkeys, $options[$field .'List']))
						{
							$value = sanitize_text_field($_POST[$field . 'List']);
							update_post_meta($postID, 'vw_'.$field.'List', $value);
						}


					//accessPrice
					if (VWliveStreaming::inList($userkeys, $options['accessPrice']))
					{
						$accessPrice = round($_POST['accessPrice'],2);
						update_post_meta($postID, 'vw_accessPrice', $accessPrice);

						$mCa = array(
							'status'       => 'enabled',
							'price'        => $accessPrice,
							'button_label' => 'Buy Access Now', // default button label
							'expire'       => 0 // default no expire
						);

						if ($options['mycred'] && $accessPrice) update_post_meta($postID, 'myCRED_sell_content', $mCa);
						else delete_post_meta($postID, 'myCRED_sell_content');

					}

				}

			}

			//! Playlist Edit
			if ( (int) $editPlaylist = $_GET['editPlaylist'])
			{

				$channel = get_post( $editPlaylist );
				if (!$channel)
				{
					return "Channel not found!";
				}

				if ($channel->post_author != $current_user->ID)
				{
					return "Access not permitted (different channel owner)!";
				}

				$stream = sanitize_file_name($channel->post_title);

				wp_enqueue_script( 'jquery');
				wp_enqueue_script( 'jquery-ui-core');
				wp_enqueue_script( 'jquery-ui-widget');
				wp_enqueue_script( 'jquery-ui-dialog');

				//wp_enqueue_script( 'jquery-ui-datepicker');



				//css
				wp_enqueue_style( 'jtable-green', plugin_dir_url(  __FILE__ ) . '/scripts/jtable/themes/lightcolor/green/jtable.min.css');

				wp_enqueue_style( 'jtable-flick', plugin_dir_url(  __FILE__ ) . '/scripts/jtable/themes/flick/jquery-ui.min.css');

				//js
				wp_enqueue_script( 'jquery-ui-jtable', plugin_dir_url(  __FILE__ ) . '/scripts/jtable/jquery.jtable.min.js', array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-dialog'));

				// wp_enqueue_script( 'jtable', plugin_dir_url(  __FILE__ ) . '/scripts/jtable/jquery.jtable.js', array('jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-dialog'));

				$ajaxurl = admin_url() . 'admin-ajax.php?action=vwls_playlist&channel=' . $editPlaylist;


				$htmlCode .= '<h3>Playlist Scheduler: ' .$channel->post_title.'</h3>';

				$currentDate = date('Y-m-j h:i:s');

				if ($_POST['updatePlaylist'])
				{
					update_post_meta( $editPlaylist, 'vw_playlistActive', $playlistActive = (int) $_POST['playlistActive']);
					VWliveStreaming::updatePlaylist($stream, $playlistActive);
					update_post_meta( $editPlaylist, 'vw_playlistUpdated', time());

					update_post_meta( $editPlaylist, 'stream-type', 'playlist');
					update_post_meta( $editPlaylist, 'stream-protocol', 'rtmp');
				}

				//playlistActive
				$value = get_post_meta( $editPlaylist, 'vw_playlistActive', true );

				$activeCode .= '<select id="playlistActive" name="playlistActive">';
				$activeCode .= '<option value="0" ' . (!$value ? 'selected' : '') . '>Inactive</option>';
				$activeCode .= '<option value="1" ' . ($value ? 'selected' : '') . '>Active</option>';
				$activeCode .= '</select>';

				$value = get_post_meta( $editPlaylist, 'vw_playlistUpdated', true );
				$playlistUpdated = date('Y-m-j h:i:s', (int) $value);

				$value = get_post_meta( $editPlaylist, 'vw_playlistLoaded', true );
				$playlistLoaded = date('Y-m-j h:i:s', (int) $value);


				$playlistPage = add_query_arg(array('editPlaylist'=>$editPlaylist), $this_page);

				$videosImg =  plugin_dir_url( __FILE__ ) . 'scripts/jtable/themes/lightcolor/edit.png';

				$channelURL = add_query_arg(array('flash-view'=>''), get_permalink($channel->ID));

				//! jTable
				$htmlCode .= <<<HTMLCODE
<form method="post" action="$playlistPage" name="adminForm" class="w-actionbox">
Playlist Status: $activeCode
<input class="videowhisperButtonLS g-btn type_primary" type="submit" name="button" id="button" value="Update" />
<input type="hidden" name="updatePlaylist" id="updatePlaylist" value="$editPlaylist" />
<BR>After editing playlist contents, update it to apply changes. Last Updated: $playlistUpdated
<BR>Playlist is loaded with web application (on access) and reloaded if necessary when users access <a href='$channelURL'>watch interface</a> (last time reloaded:  $playlistLoaded).
</form>
<BR>
First create a Schedule (Add new record), then Edit Videos (Add new record under Videos):
	<div id="PlaylistTableContainer" style="width: 600px;"></div>
	<script type="text/javascript">

		jQuery(document).ready(function () {

		    //Prepare jTable
			jQuery('#PlaylistTableContainer').jtable({
				title: 'Playlist Contents for Channel',
				defaultSorting: 'Order ASC',
				toolbar: {hoverAnimation: false},
				actions: {
					listAction: '$ajaxurl&task=list',
					createAction: '$ajaxurl&task=create',
					updateAction: '$ajaxurl&task=update',
					deleteAction: '$ajaxurl&task=delete'
				},
				fields: {
					Id: {
						key: true,
						create: false,
						edit: false,
						list: false,
					},
					//CHILD TABLE DEFINITION
					Videos: {
                    title: 'Videos',
                    sorting: false,
                    edit: false,
                    create: false,
                    display: function (playlist) {
                        //Create an image that will be used to open child table
                        var vButton = jQuery('<IMG src="$videosImg" /><I>Edit Videos</I>');
                        //Open child table when user clicks the image
                        vButton.click(function () {
                            jQuery('#PlaylistTableContainer').jtable('openChildTable',
                                    vButton.closest('tr'),
                                    {
                                        title: 'Videos for Schedule ' + playlist.record.Scheduled,
                                        actions: {
                                            listAction: '$ajaxurl&task=videolist&item=' + playlist.record.Id,
                                            deleteAction: '$ajaxurl&task=videoremove&item=' + playlist.record.Id,
                                            updateAction: '$ajaxurl&task=videoupdate',
                                            createAction: '$ajaxurl&task=videoadd'
                                        },
                                        fields: {
                                            ItemId: {
                                                type: 'hidden',
                                                defaultValue: playlist.record.Id
                                            },
                                            Id: {
                                                key: true,
                                                create: false,
                                                edit: false,
                                                list: false
                                            },
											Video: {
												title: 'Video',
												options: '$ajaxurl&task=source',
												sorting: false
											},
											Start: {
												title: 'Start',
												defaultValue: '0',
											},
											Length: {
												title: 'Length',
												defaultValue: '-1',
											},
											Order: {
												title: 'Order',
												defaultValue: '0',
											},
	                                    }
                                    }, function (data) { //opened handler
                                        data.childTable.jtable('load');
                                    });
                        });
                        //Return image to show on the person row
                        return vButton;
                    }

                    },
					Scheduled: {
						title: 'Scheduled',
						defaultValue: '$currentDate',
						sorting: false
					},
					Repeat: {
						title: 'Repeat',
						type: 'checkbox',
						defaultValue: '0',
						values: { '0' : 'Disabled', '1' : 'Enabled' },
						sorting: false
					},
					Order: {
						title: 'Order',
						defaultValue: '0',
					}
				}
			});

			//Load item list from server
			jQuery('#PlaylistTableContainer').jtable('load');
		});
	</script>
	<STYLE>
	.ui-front
	{
		z-index: 1000;
	}
	</STYLE>

HTMLCODE;

				$htmlCode .= '<BR>Schedule playlist items as: Year-Month-Day Hours:Minutes:Seconds. In example, current server time: ' . date('Y-m-j h:i:s');
				if (date_default_timezone_get()) {
					$htmlCode .= '<BR>If the schedule time is in the past, each video is loaded in order and immediately replaces the previous video for the stream. Repeat will cause that videos to repeat in loop. Scheduling must be based on server timezone: ' . date_default_timezone_get() . '<br />';
				}
			}




			//! list channels
			if (!$_GET['editChannel'] && !$_GET['editPlaylist'] && !$_GET['reStream'] && !$_GET['offlineVideo'])
			{

				$args = array(
					'author'           => $current_user->ID,
					'orderby'          => 'post_date',
					'order'            => 'DESC',
					'post_type'        => $options['custom_post'],
					'posts_per_page'   => 20,
					'offset'           => 0,
				);

				$channels = get_posts( $args );


				$htmlCode .= apply_filters("vw_ls_manage_channels_head", '');
				$htmlCode .= "<h3>My Channels ($channels_count/$maxChannels)</h3>";


				//New Buttons
				if ($channels_count < $maxChannels)
				{
					$htmlCode .= '<a href="'. add_query_arg( 'editChannel', -1, $this_page).'" class="ui primary button"> <i class="icon plus"></i> Setup New Channel</a>';

					if (VWliveStreaming::inList($userkeys, $options['ipCameras']))
					{
						if ($options['ipcams']) $htmlCode .= '<a href="'. add_query_arg( 'reStream', -1, $this_page).'" class="ui primary button"> <i class="icon plus"></i> Setup IP Camera / Stream</a>';

						if ($options['iptv']) $htmlCode .= '<a href="'. add_query_arg( array('reStream' => '-1', 'h' => 'iptv'), $this_page).'" class="ui primary button"> <i class="icon plus"></i> Setup IPTV Stream</a>';
					}

				}

				if (count($channels))
				{
					
					//thumb
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

	//is_plugin_active
	include_once(ABSPATH . 'wp-admin/includes/plugin.php');
	
					global $wpdb;
					$table_channels = $wpdb->prefix . "vw_lsrooms";


					$htmlCode .= '<table style="overflow:auto;">';

					foreach ($channels as $channel)
					{
						$postID = $channel->ID;

						$stream = sanitize_file_name(get_the_title($postID));

						//update room
						//setup/update channel, premium & time reset

						$room = $stream;
						$ztime = time();

						if ($poptions) //premium room
							{
							$rtype = 1 + $poptions['level'];
							$maximumBroadcastTime =  60 * $poptions['pBroadcastTime'];
							$maximumWatchTime =  60 * $poptions['pWatchTime'];

							// $camBandwidth=$options['pCamBandwidth'];
							// $camMaxBandwidth=$options['pCamMaxBandwidth'];
							// if (!$options['pLogo']) $options['overLogo']=$options['overLink']='';

						}else
						{
							$rtype=1;
							//$camBandwidth=$options['camBandwidth'];
							//$camMaxBandwidth=$options['camMaxBandwidth'];

							$maximumBroadcastTime =  60 * $options['broadcastTime'];
							$maximumWatchTime =  60 * $options['watchTime'];
						}


						$sql = "SELECT * FROM $table_channels where name='$room'";
						$channelR = $wpdb->get_row($sql);

						if (!$channelR)
							$sql="INSERT INTO `$table_channels` ( `owner`, `name`, `sdate`, `edate`, `rdate`,`status`, `type`) VALUES ('".$current_user->ID."', '$room', $ztime, $ztime, $ztime, 0, $rtype)";
						elseif ($options['timeReset'] && $channelR->rdate < $ztime - $options['timeReset']*24*3600) //time to reset in days
							$sql="UPDATE `$table_channels` set type=$rtype, rdate=$ztime, wtime=0, btime=0 where name='$room'";
						else
							$sql="UPDATE `$table_channels` set type=$rtype where name='$room'";

						$wpdb->query($sql);



						if ($stream)
							if (self::timeTo($stream . '/updateThumb', 300, $options))  //not too often
								{
								//update thumb
								$dir = $options['uploadsPath']. "/_snapshots";
								$thumbFilename = "$dir/$stream.jpg";

								//ip camera or playlist : update snapshot
								if (get_post_meta( $postID, 'vw_ipCamera', true ) || get_post_meta( $postID, 'vw_playlistActive', true ))
								{
									self::streamSnapshot($stream, true, $postID);
									//$htmlCode .= 'Updating IP Cam Snapshot: ' . $stream;
								}


								//only if snapshot exists but missing post thumb (not uploaded or generated previously)
								if ( file_exists($thumbFilename) && !get_post_thumbnail_id( $postID ))
								{
									if ( !get_post_thumbnail_id( $postID ) ) //insert
										{
										$wp_filetype = wp_check_filetype(basename($thumbFilename), null );

										$attachment = array(
											'guid' => $thumbFilename,
											'post_mime_type' => $wp_filetype['type'],
											'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $thumbFilename, ".jpg" ) ),
											'post_content' => '',
											'post_status' => 'inherit'
										);

										$attach_id = wp_insert_attachment( $attachment, $thumbFilename, $postID );
										set_post_thumbnail($postID, $attach_id);
									}
									else //update
										{
										$attach_id = get_post_thumbnail_id($postID );
										$thumbFilename = get_attached_file($attach_id);
									}

									//cleanup any relics
									if ($postID && $attach_id) VWliveStreaming::delete_associated_media($postID, false, $attach_id);

									//update
									$attach_data = wp_generate_attachment_metadata( $attach_id, $thumbFilename );
									wp_update_attachment_metadata( $attach_id, $attach_data );
								}
							}

						//snapshot
						$dir = $options['uploadsPath']. "/_snapshots";
						$thumbFilename = "$dir/$stream.jpg";

						$showImage=get_post_meta( $postID, 'showImage', true );

						if (!file_exists($thumbFilename) || $showImage =='all') //show thumb instead
							{
							$attach_id = get_post_thumbnail_id($postID );
							if ($attach_id) $thumbFilename = get_attached_file($attach_id);
						}

						$noCache = '';
						if ($age=='LIVE') $noCache='?'.((time()/10)%100);
						if (file_exists($thumbFilename) && !strstr($thumbFilename, '/.jpg') ) $thumbCode = '<IMG src="' . VWliveStreaming::path2url($thumbFilename) . $noCache .'" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px">';
						else $thumbCode = '<IMG SRC="' . plugin_dir_url(__FILE__). 'screenshot-3.jpg" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px">';

						//channel url
						$url = get_permalink($postID);


						//display channel management
						$htmlCode .= '<tr><td><a href="' . $url . '"><h4>' . $channel->post_title . '</h4><div class="ui bordered rounded image">' .  $thumbCode . '</div></a>';


						//Features Info


						//transcode quick update (if settigns changed for role)
						$vw_transcode = get_post_meta( $postID, 'vw_transcode', true );
						if (VWliveStreaming::inList($userkeys, $options['transcode'])) $new_vw_transcode = 1;
						else $new_vw_transcode =0;
						if ($vw_transcode != $new_vw_transcode) update_post_meta($postID, 'vw_transcode', $new_vw_transcode);

						//info under channel snapshot
						$htmlCode .= '
<div class="ui ' . $options['interfaceClass'] .' message">';

						$edate = intval(get_post_meta( $postID, 'edate', true ));
						$htmlCode .= '<br> ' . __('Last Broadcast', 'live-streaming') . ': ' . ($edate ? date(DATE_RFC2822, $edate) : 'Never.');

						$periodCode = '';
						if ($options['timeReset']) $periodCode = ' ' . sprintf(__('each %d days', 'live-streaming'), $options['timeReset']);


						if ($channelR) $htmlCode .= '<br>' . __('Total Broadcast Time', 'live-streaming') . ': ' . VWliveStreaming::format_time($channelR->btime) . ' / ' . ($maximumBroadcastTime?VWliveStreaming::format_time($maximumBroadcastTime) . $periodCode : __('Unlimited', 'live-streaming') ) .  '<br>' . __('Total Watch Time', 'live-streaming') . ': ' . VWliveStreaming::format_time($channelR->wtime) . ' / ' . ($maximumWatchTime?VWliveStreaming::format_time($maximumWatchTime) . $periodCode :  __('Unlimited', 'live-streaming') );

						$htmlCode .= '<br> ' . __('Level', 'live-treaming') . ': ' . ($channelR->type>1?'Premium '. ($channelR->type-1):'Regular '. $channelR->type);


						if ($options['transcoding']) $htmlCode .= '<br> ' . __('Transcoding', 'live-streaming') . ': ' . ($vw_transcode?'Enabled':'Disabled');

						$htmlCode .= '<br> ' . __('Logo', 'live-streaming') . ': ' . get_post_meta( $postID, 'vw_logo', true );
						$htmlCode .= '<br> ' . __('Ads', 'live-streaming') . ': ' . get_post_meta( $postID, 'vw_ads', true );

						$htmlCode .= '<br>' . __('Stream Type', 'live-streaming') . ': ' . get_post_meta($postID, 'stream-type', true);

						if (get_post_meta( $postID, 'vw_ipCamera', true )) $htmlCode .= '<br>' . __('IP Camera', 'live-streaming');

						if (get_post_meta( $postID, 'restreamPaused', true )) $htmlCode .= '<br>' . __('ReStream Paused', 'live-streaming');


						if (get_post_meta( $postID, 'vw_playlistActive', true )) $htmlCode .= '<br>' . __('Playlist Scheduled', 'live-streaming');


						foreach (array('access','chat','write','participants','privateChat') as $field)
							if ($value = get_post_meta($postID, 'vw_'.$field.'List', true))
								$htmlCode .= '<br>' . ucwords($field) . ': ' . $value;
							$htmlCode .= '</div>';

						$htmlCode .= '<div>' . apply_filters("vw_ls_manage_channel", '', $channel->ID) . '</div>';


						$htmlCode .= '</td>';
						$htmlCode .= '<td width="210px; text-align=left">';


						//semantic ui
						VWliveStreaming::enqueueUI();

						$htmlCode .=  '
<div class="ui ' . $options['interfaceClass'] .' vertical menu">
      <a class="item" href="' . add_query_arg( 'editChannel', $channel->ID, $this_page) . '">' . __('Setup', 'live-streaming') . '</a>
      <a class="item" href="' . add_query_arg( 'deleteChannel', $channel->ID, $this_page) . '">' . __('Delete', 'live-streaming') . '</a>
</div>

<div class="ui ' . $options['interfaceClass'] .' vertical menu">
 <div class="item header">' . __('Broadcast', 'live-streaming') . '</div>
      <a class="ui red item" href="' . add_query_arg(array('broadcast'=>''), get_permalink($channel->ID)) . '">' . __('Web Broadcast (Auto)', 'live-streaming') . '</a>
      <a class="item" href="' . add_query_arg(array('flash-broadcast'=>''), get_permalink($channel->ID)) . '">' . __('Advanced (PC Flash)', 'live-streaming') . '</a>
      ';

						if ($options['webrtc']) $htmlCode .= '
      <a class="item" href="' . add_query_arg(array('webrtc-broadcast'=>''), get_permalink($channel->ID)) . '">' . __('WebRTC (HTML5)', 'live-streaming') . '</a>';

						if ($options['externalKeys']) $htmlCode .= '
      <a class="item" href="' . add_query_arg(array('external-broadcast'=>''), get_permalink($channel->ID)) . '">' . __('External Encoders (Apps)', 'live-streaming') . '</a>';

						if ($options['playlists']) if (VWliveStreaming::inList($userkeys, $options['schedulePlaylists'])) $htmlCode .= '
      <a class="item" href="' . add_query_arg( 'editPlaylist', $channel->ID, $this_page) . '">' . __('Playlist', 'live-streaming') . '</a>';

							if (VWliveStreaming::inList($userkeys, $options['ipCameras']))
							{
								if ($options['ipcams']) $htmlCode .= '<a href="'. add_query_arg( 'reStream', $channel->ID, $this_page).'" class="item">' . __('IP Cam / Stream', 'live-streaming') . '</a>';
								if ($options['iptv']) $htmlCode .= '<a href="'. add_query_arg( array('reStream' => $channel->ID, 'h' => 'iptv'), $this_page).'" class=item">' . __('IPTV / Pull', 'live-streaming') . '</a>';
							}
	
	if (function_exists('is_plugin_active') && is_plugin_active('video-share-vod/video-share-vod.php')) 
	$htmlCode .= '<a class="item" href="' . add_query_arg( 'offlineVideo', $channel->ID, $this_page) . '">' . __('Offline Video', 'live-streaming') . '</a>';

						$htmlCode .=  '
</div>

<div class="ui ' . $options['interfaceClass'] .' vertical menu">
 <div class="item header">' . __('Playback', 'live-streaming') . '</div>
	  <a class="item green" href="' . get_permalink($channel->ID) . '">Web View (Auto)</a>
      <a class="item" href="' . add_query_arg(array('flash-view'=>''), get_permalink($channel->ID)) . '">' . __('Advanced (PC Flash)', 'live-streaming') . '</a>';

						if ( $options['transcoding'] || $options['webrtc'] ) $htmlCode .= '
      <a class="item" href="' . add_query_arg(array('html5-view'=>''), get_permalink($channel->ID)) . '">' . __('Web View (HTML5)', 'live-streaming') . '</a>';


						$htmlCode .= '
      <a class="item" href="' . add_query_arg(array('video'=>''), get_permalink($channel->ID)) . '">' . __('Only Video (Auto)', 'live-streaming') . '</a>
      <a class="item" href="' . add_query_arg(array('flash-video'=>''), get_permalink($channel->ID)) . '">' . __('Video (PC Flash)', 'live-streaming') . '</a>
      ';

						if ($options['transcoding']) if (VWliveStreaming::inList($userkeys, $options['transcode'])) $htmlCode .= '
      <a class="item" href="' . add_query_arg(array('hls'=>''), get_permalink($channel->ID)) . '">' . __('Video HLS (iOS/Safari)', 'live-streaming') . '</a>
      <a class="item" href="' . add_query_arg(array('mpeg'=>''), get_permalink($channel->ID)) . '">' . __('MPEG DASH (Android/Chrome)', 'live-streaming') . '</a>';

							if ($options['webrtc']) $htmlCode .= '
      <a class="item" href="' . add_query_arg(array('webrtc-playback'=>''), get_permalink($channel->ID)) . '">' . __('Video WebRTC (HTML5)', 'live-streaming') . '</a>';

							if ($options['externalKeys']) $htmlCode .= '
      <a class="item" href="' . add_query_arg(array('external-playback'=>''), get_permalink($channel->ID)) . '">' . __('Other Players, Embed', 'live-streaming') . '</a>';

							$htmlCode .=  '
</div>

<style>
.ui > .item {
  display: block !important;
}
</style>
';


						/*
						$htmlCode .= '<BR><BR><a class="videowhisperButtonLS g-btn type_red" href="' . add_query_arg(array('broadcast'=>''), get_permalink($channel->ID)) . '">Broadcast</a>';
						if ($options['webrtc']) $htmlCode .= '<BR> <a class="videowhisperButtonLS g-btn type_red" href="' . add_query_arg(array('webrtc-broadcast'=>''), get_permalink($channel->ID)) . '">WebRTC Broadcast</a>';

						if ($options['externalKeys']) $htmlCode .= '<BR> <a class="videowhisperButtonLS g-btn type_pink" href="' . add_query_arg(array('external'=>''), get_permalink($channel->ID)) . '">External Apps</a>';
						$htmlCode .= '<BR> <a class="videowhisperButtonLS g-btn type_green" href="' . get_permalink($channel->ID) . '">Chat &amp; Video</a>';
						$htmlCode .= '<BR> <a class="videowhisperButtonLS g-btn type_green" href="' . add_query_arg(array('video'=>''), get_permalink($channel->ID)) . '">Video Only</a>';
						if ($options['webrtc']) $htmlCode .= '<BR> <a class="videowhisperButtonLS g-btn type_green" href="' . add_query_arg(array('webrtc-playback'=>''), get_permalink($channel->ID)) . '">WebRTC Playback</a>';


						$htmlCode .= '<BR> <a class="videowhisperButtonLS g-btn type_yellow" href="' . add_query_arg( 'editChannel', $channel->ID, $this_page) . '">Setup</a>';

						if ($options['playlists'])
							if (VWliveStreaming::inList($userkeys, $options['schedulePlaylists']))
								$htmlCode .= '<BR> <a class="videowhisperButtonLS g-btn type_yellow" href="' . add_query_arg( 'editPlaylist', $channel->ID, $this_page) . '">Playlist</a>';
*/


						$htmlCode .= '</td></tr>';
						//filter under channel

					}
					$htmlCode .= '</table>';


				}
				else
					$htmlCode .= "<div class='warning'>You don't have any channels, yet!</div>";

				$htmlCode .= apply_filters("vw_ls_manage_channels_foot", '');
			}

			
			//offlineVideo
			$offlineVideo = intval($_GET['offlineVideo']);
			
			if ($_GET['assignVideo']=='offline_video') $offlineVideo = intval($_GET['postID']);

			if ($offlineVideo)
			{
				
				if (shortcode_exists('videowhisper_postvideo_assign'))
						{
							$htmlCode .= '<div class="ui ' . $options['interfaceClass'] . ' segment">';

							$htmlCode .=  '<h3 class="ui header">' . __('Offline Video', 'ppv-live-webcams') .' #' . $offlineVideo. '</H3>';
							$htmlCode .= do_shortcode("[videowhisper_postvideo_assign post_id=\"$offlineVideo\" meta=\"offline_video\"]");

							$htmlCode .= '<p>' . __('Offline video plays in html5 player when channel is offline.', 'ppv-live-webcams') . '</p</div>';

						}
					
						
				//c

				//if ($offline_video) $addCode .= '<div class="item"><h3 class="ui ' . $options['interfaceClass'] . ' header">' . __('Current Offline Video', 'ppv-live-webcams') . '</h3> <div class="ui ' . $options['interfaceClass'] .' segment" style="min-width:320px">' . do_shortcode('[videowhisper_player video="' .$offline_video. '"]' . '</div></div>');
			}
			
			//! Setup IP Camera / Re-Stream
			$reStream = intval($_GET['reStream']);
			

			if ($reStream) $htmlCode .= do_shortcode('[videowhisper_stream_setup channel_id="' . $reStream . '"]');
			//! Edit Channel Form

			//setup
			$editPost = intval($_GET['editChannel']);

			if ($editPost)
			{

				$newCat = -1;

				if ($editPost > 0)
				{
					$channel = get_post( $editPost );
					if ($channel->post_author != $current_user->ID) return "<div class='ui error segment'>Not allowed (different owner)!</div>";

					$newDescription = $channel->post_content;
					$newName = $channel->post_title;
					$newComments = $channel->comment_status;

					$cats = wp_get_post_categories( $editPost);
					if (count($cats)) $newCat = array_pop($cats);
				}

				if ($editPost<1)
				{
					$editPost = -1;

					$newTitle = 'New';

					$newName = sanitize_file_name($username);
					if ($channels_count) $newName .= '_' . base_convert(time()-1225000000,10,36);
					$nameField = 'text';
					$newNameL = '';
				}
				else
				{
					$nameField = 'hidden';
					$newNameL = $newName;
				}

				//semantic ui
				VWliveStreaming::enqueueUI();


				$commentsCode = '';
				$commentsCode .= '<select class="ui dropdown" id="newcomments" name="newcomments">';
				$commentsCode .= '<option value="closed" ' . ($newComments=='closed'?'selected':'') . '>' . __('Closed', 'live-streaming') . '</option>';
				$commentsCode .= '<option value="open" ' . ($newComments=='open'?'selected':'') . '>' . __('Open', 'live-streaming') . '</option>';
				$commentsCode .= '</select>';


				$categories = do_shortcode('[videowhisper_categories selected="' . $newCat . '"]');

				//$categories = wp_dropdown_categories('show_count=0&echo=0&class=ui+dropdown&name=newcategory&hide_empty=0&hierarchical=1&selected=' . $newCat);

				//! channel features
				$extraRows = '';

				//roomTags
				if (VWliveStreaming::inList($userkeys, $options['roomTags']))
				{
					if ($editPost)  $tags = wp_get_post_tags($editPost, array( 'fields' => 'names' ));
					//var_dump($tags);
					$value = '';

					if ( ! empty( $tags ) ) if ( ! is_wp_error( $tags ) )
							foreach( $tags as $tag )  $value .= ($value?', ':'') . $tag;

							$extraRows .= '<tr><td>' . __('Tags', 'live-streaming') . '</td><td><textarea rows=2 cols="80" name="roomTags" id="roomTags">' . $value . '</textarea><BR>' . __('Tags separated by comma.', 'live-streaming') . '</td></tr>';
				}



				//accessPassword
				if (VWliveStreaming::inList($userkeys, $options['accessPassword']))
				{
					if ($editPost) $value = $channel->post_password;
					else $value = '';

					$extraRows .= '<tr><td>' . __('Access Password', 'live-streaming') . '</td><td><input size=16 name="accessPassword" id="accessPassword" value="' . $value . '"><BR>' . __('Password to protect channel. Leave blank to not require password.', 'live-streaming') . '</td></tr>';
				}

				//permission lists
				$permInfo = array(
					'access'=>__('Can access channel.', 'live-streaming'),
					'chat'=>__('Can view public chat.', 'live-streaming'),
					'write'=>__('Can write in public chat.', 'live-streaming'),
					'participants'=>__('Can view participants list.', 'live-streaming'),
					'privateChat'=>__('Can initiate private chat with users from participants list.', 'live-streaming')
				);

				foreach (array('access','chat','write','participants','privateChat') as $field)
					if (VWliveStreaming::inList($userkeys, $options[$field . 'List']))
					{
						if ($editPost) $value = get_post_meta( $editPost, 'vw_'.$field.'List', true );
						else $value = '';

						$extraRows .= '<tr><td>'.ucwords($field).' List</td><td><textarea rows=2 cols=60 name="'.$field.'List" id="'.$field.'List">' . $value . '</textarea><BR>' .$permInfo[$field]. ' ' . __('Define user list as roles, logins, emails separated by comma. Leave empty to allow everybody or set None to disable.', 'live-streaming') . '</td></tr>';
					}

				//accessPrice
				if (VWliveStreaming::inList($userkeys, $options['accessPrice']))
				{
					if ($editPost>0) $value = get_post_meta( $editPost, 'vw_accessPrice', true );
					else $value = '0.00';

					$extraRows .= '<tr><td>' . __('Access Price', 'live-streaming') . '</td><td><input size=5 name="accessPrice" id="accessPrice" value="' . $value . '"><BR>' . __('Channel access price. Leave 0 for free access.', 'live-streaming') . '</td></tr>';
				}

				//logoCustom
				if (VWliveStreaming::inList($userkeys, $options['logoCustom']))
				{
					if ($editPost>0) $value = get_post_meta( $editPost, 'vw_logoImage', true );
					else $value =  $options['overLogo'];

					$extraRows .= '<tr><td>' . __('Logo Image', 'live-streaming') . '</td><td><input size=64 name="logoImage" id="logoImage" value="' . $value . '"><BR>' . __('Channel floating logo URL (preferably a transparent PNG image). Leave blank to hide.', 'live-streaming') . '</td></tr>';
					if ($editPost>0) $value = get_post_meta( $editPost, 'vw_logoLink', true );
					else $value = $options['overLink'];

					$extraRows .= '<tr><td>Logo Link</td><td><input size=64 name="logoLink" id="logoImage" value="' . $value . '"><BR>' . __('URL to open on logo click.', 'live-streaming') . '</td></tr>';
				}


				//ipCameras
				if (VWliveStreaming::inList($userkeys, $options['ipCameras']))
				{
					if ($editPost>0) $value = get_post_meta( $editPost, 'vw_ipCamera', true );
					else $value = '';

					$extraRows .= '<tr><td>' . __('IP Camera Stream', 'live-streaming') . '</td><td><input size=64 name="ipCamera" id="ipCamera" value="' . $value . '"><BR>Insert address exactly as it works in <a target="_blank" href="http://www.videolan.org/vlc/index.html">VLC</a> or other player. For increased playback support, H264 video with AAC audio encoded streams should be used. Address should use one of these protocols: rtsp://, udp://, rtmp://, rtmps://, wowz://, wowzs://, http://, https:// .</td></tr>';
				}



				//adsCustom
				if (VWliveStreaming::inList($userkeys, $options['adsCustom']))
				{
					if ($editPost>0) $value = get_post_meta( $editPost, 'vw_adsServer', true );
					else $value = $options['adServer'];

					$extraRows .= '<tr><td>Ads Server</td><td><input size=64 name="adsServer" id="adsServer" value="' . $value . '"><BR>See <a href="http://www.adinchat.com" target="_blank"><U><b>AD in Chat</b></U></a> compatible ad management server. Leave blank to disable.</td></tr>';
				}

				//uploadPicture
				if (VWliveStreaming::inList($userkeys, $options['uploadPicture']))
				{

					$extraRows .= '<tr><td>' . __('Picture', 'live-streaming') . '</td><td><input class="ui button" type="file" name="uploadPicture" id="uploadPicture"><BR>' . __('Update channel picture.', 'live-streaming') . '</td></tr>';


					$value=get_post_meta( $editPost, 'showImage', true );

					$extraRows .= '<tr><td>' . __('Show Picture', 'live-streaming') . '</td><td><select class="ui dropdown" name="showImage" id="showImage">';
					$extraRows .= '<option value="event" '.($value=='event'?'selected':'').'>' . __('Event Info', 'live-streaming') . '</option>';
					$extraRows .= '<option value="all" '.($value=='all'?'selected':'').'>' . __('Everywhere', 'live-streaming') . '</option>';
					$extraRows .= '<option value="no" '.($value=='no'?'selected':'').'>' . __('No', 'live-streaming') . '</option>';
					$extraRows .= '</select><BR>' . __('Configure picture display.', 'live-streaming') . '</td></tr>';
				}

				if (VWliveStreaming::inList($userkeys, $options['eventDetails']))
				{
					if ($editPost>0) $value = get_post_meta( $editPost, 'eventTitle', true );

					$extraRows .= '<tr><td>' . __('Event Title', 'live-streaming') . '</td><td><input size=64 name="eventTitle" id="eventTitle" value="' . $value . '"></td></tr>';

					if ($editPost>0) $value = get_post_meta( $editPost, 'eventStart', true );
					if ($editPost>0) $valueTime = get_post_meta( $editPost, 'eventStartTime', true );
					$extraRows .= '<tr><td>Event Start</td><td>' . __('Date', 'live-streaming') . ': <input size=32 name="eventStart" id="eventStart" value="' . $value . '"> ' . __('Time', 'live-streaming') . ': <input size=32 name="eventStartTime" id="eventStartTime" value="' . $valueTime . '"></td></tr>';

					if ($editPost>0) $value = get_post_meta( $editPost, 'eventEnd', true );
					if ($editPost>0) $valueTime = get_post_meta( $editPost, 'eventEndTime', true );
					$extraRows .= '<tr><td>' . __('Event End', 'live-streaming') . '</td><td>' . __('Date', 'live-streaming') . ': <input size=32 name="eventEnd" id="eventEnd" value="' . $value . '"> ' . __('Time', 'live-streaming') . ': <input size=32 name="eventEndTime" id="eventEndTime" value="' . $valueTime . '"></td></tr>';

					if ($editPost>0) $value = get_post_meta( $editPost, 'eventDescription', true );
					$extraRows .= '<tr><td>' . __('Event Description', 'live-streaming') . '</td><td><textarea rows=3 cols=60 name="eventDescription" id="eventDescription">' . $value . '</textarea><br>' . __('Event details also show when channel is offline or inaccessible.', 'live-streaming') . '</td></tr>';

				}


				$formTitle = __('Setup Channel', 'live-streaming') .' ' . $newTitle;
				$formRows = '<tr><td>' . __('Description', 'live-streaming') . '</td><td><textarea rows=3 cols=60 name="description" id="description">' . $newDescription .  '</textarea></td></tr>
<tr><td>' . __('Category', 'live-streaming') . '</td><td>' . $categories . '</td></tr>
<tr><td>' . __('Comments', 'live-streaming') . '</td><td>' . $commentsCode . '</td></tr>';

				$formButton = '<tr><td></td><td><input class="ui button primary" type="submit" name="button" id="button" value="' . __('Setup', 'live-streaming') . '" /></td></tr>';

				if ($editPost > 0 || $channels_count < $maxChannels)
					$htmlCode .= <<<HTMLCODE
<script language="JavaScript">
		function censorName()
			{
				document.adminForm.room.value = document.adminForm.room.value.replace(/^[\s]+|[\s]+$/g, '');
				document.adminForm.room.value = document.adminForm.room.value.replace(/[^0-9a-zA-Z_\-]+/g, '-')
				document.adminForm.room.value = document.adminForm.room.value.replace(/\-+/g, '-');
				document.adminForm.room.value = document.adminForm.room.value.replace(/^\-+|\-+$/g, '');
				if (document.adminForm.room.value.length>0) return true;
				else
				{
				alert("A channel name is required!");
				return false;
				}
			}
</script>

<div class="ui form">
<form method="post" enctype="multipart/form-data"  action="$this_page" name="adminForm" class="w-actionbox">
<h3>$formTitle</h3>
<table class="ui celled table selectable">
<tr><td>Name</td><td><input name="newname" type="$nameField" id="newname" value="$newName" size="20" maxlength="64" onChange="censorName()"/>$newNameL <input class="ui button small" type="submit" name="button" id="button" value="Setup" /></td></tr>
$formRows
$extraRows
$formButton
</table>
<input type="hidden" name="editPost" id="editPost" value="$editPost" />
</form>
</div>

<script>


jQuery(document).ready(function(){
		jQuery(".ui.dropdown").not(".ajax").dropdown();
});

</script>
HTMLCODE;
			}

			$htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

			return $htmlCode;

		}


		static function enqueueUI()
		{
			//semantic ui
			wp_enqueue_script( 'jquery' );
			
			//semantic
			//wp_enqueue_style( 'semantic', plugin_dir_url(  __FILE__ ) . '/scripts/semantic/semantic.min.css');
			//wp_enqueue_script( 'semantic', plugin_dir_url(  __FILE__ ) . '/scripts/semantic/semantic.min.js', array('jquery'));

			//fomantic
			wp_enqueue_style( 'semantic', 'https://cdn.jsdelivr.net/npm/fomantic-ui@2.8.2/dist/semantic.min.css');
			wp_enqueue_script( 'semantic', 'https://cdn.jsdelivr.net/npm/fomantic-ui@2.8.2/dist/semantic.min.js', array('jquery'));

		}

		function videowhisper_channels($atts)
		{
			$options = get_option('VWliveStreamingOptions');

			$atts = shortcode_atts(
				array(
					'per_page' => $options['perPage'],
					'ban' => '0',
					'perrow' => '',
					'order_by' => 'edate',
					'category_id' => '',
					'tags' => '',
					'name' => '',
					'select_category' => '1',
					'select_tags' => '1',
					'select_name' => '1',
					'select_order' => '1',
					'select_page' => '1',
					'include_css' => '1',
					'url_vars' => '1',
					'url_vars_fixed' => '1',
					'id' => ''
				), $atts, 'videowhisper_channels');

			$id = $atts['id'];
			if (!$id) $id = uniqid();

			if ($atts['url_vars'])
			{
				$cid = (int) $_GET['cid'];
				if ($cid)
				{
					$atts['category_id'] = $cid;
					if ($atts['url_vars_fixed']) $atts['select_category'] = '0';
				}
			}

			//semantic ui : listings
			VWliveStreaming::enqueueUI();



			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwls_channels&pp=' . $atts['per_page']. '&pr=' . $atts['perrow'] . '&ob=' . $atts['order_by'] . '&cat=' . $atts['category_id'] . '&sc=' . $atts['select_category'] . '&sn=' . $atts['select_name'] .  '&sg=' . $atts['select_tags'] . '&so=' . $atts['select_order'] . '&sp=' . $atts['select_page']. '&id=' .$id . '&tags=' . urlencode($atts['tags']) . '&name=' . urlencode($atts['name']);

			if ($atts['ban']) $ajaxurl .= '&ban=' . $atts['ban'];

			$htmlCode = <<<HTMLCODE
<script>
var aurl$id = '$ajaxurl';
var \$j = jQuery.noConflict();
var loader$id;

	function loadChannels$id(message){

	if (message)
	if (message.length > 0)
	{
	  \$j("#videowhisperChannels$id").html(message);
	}

		if (loader$id) loader$id.abort();

		loader$id = \$j.ajax({
			url: aurl$id,
			success: function(data) {
				\$j("#videowhisperChannels$id").html(data);
				jQuery(".ui.dropdown").dropdown();
				jQuery(".ui.rating.readonly").rating("disable");
			}
		});
	}

jQuery(document).ready(function(){
	loadChannels$id();
	setInterval("loadChannels$id()", 10000);
});

</script>

<div id="videowhisperChannels$id">

<div class="ui active inline text large loader">Loading channels...</div>

</div>
HTMLCODE;

			$htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

			return $htmlCode;
		}

		static function flash_warn()
		{

			$flashWarning = __('Using the Flash web based interface requires <a rel="nofollow" target="_flash" href="https://get.adobe.com/flashplayer/">latest Flash plugin</a> and <a rel="nofollow" target="_flash" href="https://helpx.adobe.com/flash-player.html">activating plugin in your browser</a>. Flash apps are recommended on PC for best latency and most advanced features.', 'live-streaming');

			$htmlCode = <<<HTMLCODE

<div id="flashWarning"></div>

<script>
var hasFlash = ((typeof navigator.plugins != "undefined" && typeof navigator.plugins["Shockwave Flash"] == "object") || (window.ActiveXObject && (new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) != false));

var flashWarn = '<small>$flashWarning</small>'

if (!hasFlash) document.getElementById("flashWarning").innerHTML = flashWarn;</script>
HTMLCODE;


			return $htmlCode;
		}

		static function flash_watch($stream, $width='100%', $height='100%')
		{
			$stream = sanitize_file_name($stream);

			$streamLabel = preg_replace('/[^A-Za-z0-9\-\_]/', '', $stream);

			$swfurl = plugin_dir_url(__FILE__) . "ls/live_watch.swf?ssl=1&n=" . urlencode($stream);
			$swfurl .= "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vwls&task=');
			$swfurl .= '&extension='.urlencode('_none_');
			$swfurl .= '&ws_res=' . urlencode( plugin_dir_url(__FILE__) . 'ls/');

			$bgcolor="#333333";

			$htmlCode = <<<HTMLCODE
<div id="videowhisper_container_$streamLabel" style="overflow:auto">
<object id="videowhisper_watch_$streamLabel" width="$width" height="$height" type="application/x-shockwave-flash" data="$swfurl">
<param name="movie" value="$swfurl"></param><param bgcolor="$bgcolor"><param name="scale" value="noscale" /> </param><param name="salign" value="lt"></param><param name="allowFullScreen"
value="true"></param><param name="allowscriptaccess" value="always"></param>
</object>
</div>
HTMLCODE;

			$htmlCode .= VWliveStreaming::flash_warn();

			return $htmlCode ;
		}


		function videowhisper_watch($atts)
		{
			$stream = '';

			$options = get_option('VWliveStreamingOptions');


			if (is_single())
				if (get_post_type( get_the_ID() ) ==  $options['custom_post'] ) $stream = get_the_title(get_the_ID());

				$atts = shortcode_atts(array(
						'channel' => $stream,
						'width' => '100%',
						'height' => '100%',
						'flash' => '0',
						'html5' => 'auto'
					), $atts, 'videowhisper_watch');

			if (!$stream) $stream = $atts['channel']; //parameter channel="name"
			if (!$stream) $stream = $_GET['n'];
			$stream = sanitize_file_name($stream);

			if (!$stream)
			{
				return "Watch Error: Missing channel name!";
			}

			//used by flash container
			$width=$atts['width']; if (!$width) $width = "100%";
			$height=$atts['height']; if (!$height) $height = "100%";

			global $wpdb ;
			$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $stream . '\' and post_type=\'' . $options['custom_post'] . '\' LIMIT 0,1' );

			//handle paused restreams
			$vw_ipCamera = get_post_meta( $postID, 'vw_ipCamera', true );
			if ($vw_ipCamera) self::restreamPause($postID, $stream, $options);

			$streamProtocol = get_post_meta($postID, 'stream-protocol', true); // rtsp/rtmp
			$streamType = get_post_meta($postID, 'stream-type', true); // stream-type: flash/webrtc/restream/playlist
			$streamMode =  get_post_meta($postID, 'stream-mode', true); // direct/safari_pc

			/*
			if ($iOS || $Safari) return $htmlCode.'<!--H5-HLS-->'. do_shortcode("[videowhisper_hls channel=\"$stream\" width=\"$width\" height=\"$height\" webstatus=\"1\"]");
			else return $htmlCode.'<!--H5-MPEG-->'. do_shortcode("[videowhisper_mpeg channel=\"$stream\" width=\"$width\" height=\"$height\" webstatus=\"1\"]");
			*/

			//detect for video interface
			/*
			if ( ($Android && in_array($options['detect_mpeg'], array('android', 'all'))) || (!$iOS && in_array($options['detect_mpeg'], array('all'))) || (!$iOS && !$Safari && in_array($options['detect_mpeg'], array('nonsafari'))) )
				return $htmlCode .'<!--MPEG-->'. do_shortcode("[videowhisper_mpeg channel=\"$stream\" width=\"$width\" height=\"$height\" webstatus=\"1\"]");

			if ( (($Android||$iOS) && in_array($options['detect_hls'], array('mobile','safari', 'all'))) || ($iOS && $options['detect_hls'] == 'ios') || ($Safari && in_array($options['detect_hls'], array('safari', 'all'))) ) return $htmlCode .'<!--HLS-->'. do_shortcode("[videowhisper_hls channel=\"$stream\" width=\"$width\" height=\"$height\" webstatus=\"1\"]");
			*/

			if (!$atts['flash'])
			{
				//HLS if iOS/Android detected
				$agent = $_SERVER['HTTP_USER_AGENT'];
				$Android = stripos($agent,"Android");
				$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));
				$Safari = (strstr($agent,"Safari") && !strstr($agent,"Chrome"));
				$Firefox = stripos($agent,"Firefox");

				$htmlCode .= "<!--VideoWhisper-Agent-Watch:$agent|A:$Android|I:$iOS|S:$Safari|F:$Firefox-->";

				$showHTML5 = 0;

				//always
				if ($atts['html5'] == 'always') $showHTML5 = 1;

				//adaptive
				if ($options['transcoding']>=3 && $streamType != 'flash') $showHTML5 = 1;
				//if ($options['webrtc']>=3  && $streamType=='webrtc' && $streamMode!='safari_pc') $showHTML5 = 1; //safari_pc does not work directly w. h264
				if ($options['webrtc']>=3  && $streamType=='webrtc') $showHTML5 = 1; //safari_pc does not work directly

				//preferred transcoded playback
				if ($options['transcoding'] >= 4) $showHTML5 = 1;
				if ($options['webrtc'] >= 4) $showHTML5 = 1;				

				if ( ($Android && in_array($options['detect_mpeg'], array('android', 'all'))) || (!$iOS && in_array($options['detect_mpeg'], array('all'))) || (!$iOS && !$Safari && in_array($options['detect_mpeg'], array('nonsafari'))) ) $showHTML5 = 1;

				if ( (($Android||$iOS) && in_array($options['detect_hls'], array('mobile','safari', 'all'))) || ($iOS && $options['detect_hls'] == 'ios') || ($Safari && in_array($options['detect_hls'], array('safari', 'all'))) ) $showHTML5 = 1;

				if ($showHTML5) return $htmlCode.'<!--H5-HLS-->' . do_shortcode("[videowhisper_htmlchat_playback channel=\"$stream\"]");

			} else $htmlCode .= "<!--VideoWhisper-Watch:Flash-->";


			//show flash_watch

			$options = get_option('VWliveStreamingOptions');
			$watchStyle = html_entity_decode($options['watchStyle']);

			$streamLabel = preg_replace('/[^A-Za-z0-9\-\_]/', '', $stream);


			$afterCode = <<<HTMLCODE
<br style="clear:both" />

<style type="text/css">
<!--

#videowhisper_container_$streamLabel
{
$watchStyle
}

-->
</style>

HTMLCODE;

			//Available HTML5
			if ($options['transcoding'] >= 2)
			{
				if ($postID) $afterCode .= '<p><a class="ui button secondary" href="' . add_query_arg(array('html5-view'=>''), get_permalink($postID)) . '">' . __('Try HTML5 View', 'live-streaming') . '</a></p>';
			}


			return VWliveStreaming::flash_watch($stream, $width, $height) . $afterCode ;

		}

		static function transcodeStreamWebRTC($stream, $postID, $options = null, $detect=2)
		{
			//transcode for WebRTC usage: RTMP/RTSP as necessary

			if (!$stream) return;
			if (!$options)  $options = get_option('VWliveStreamingOptions');

			if (!$options['webrtc']) return;

			if (!VWliveStreaming::timeTo($stream . '/transcodeCheckWebRTC-Flood', 3, $options)) return; //prevent duplicate checks

			// check every 59s
			$tooSoon = 0;
			if (!VWliveStreaming::timeTo($stream . '/transcodeCheckWebRTC', 59, $options)) $tooSoon = 1;

			$sourceProtocol = get_post_meta($postID, 'stream-protocol', true); //rtmp/rtsp
			$sourceType = get_post_meta($postID, 'stream-type', true); // stream-type: flash/webrtc/restream/playlist

			if (!$sourceProtocol) $sourceProtocol = 'rtmp'; //assuming plain wowza stream

			if ($sourceProtocol == 'rtmp') //source available as RTMP (flash, external)
				{
				if (!$options['transcodeRTC']) return $stream; //webrtc transcoding disabled: return original stream

				//RTMP to RTSP (h264/opus)
				$stream_webrtc = $stream . '_webrtc';

				if ($tooSoon) return $stream_webrtc;


				//detect transcoding process - cancel if already started
				$cmd = "ps aux | grep '/$stream_webrtc -i rtmp'";
				exec($cmd, $output, $returnvalue);

				$transcoding = 0;
				foreach ($output as $line)
					if (strstr($line, "ffmpeg"))
					{
						$transcoding = 1;
						break;
					}

				//rtmp keys
				if ($options['externalKeysTranscoder'])
				{
					$keyView = md5('vw' . $options['webKey']. $postID);
					$rtmpAddressView = $options['rtmp_server'] . '?'. urlencode('ffmpegWebRTC_' . $stream_webrtc) .'&'. urlencode($stream) .'&'. $keyView . '&0&videowhisper';

				}
				else
				{
					$rtmpAddress = $options['rtmp_server'];
					$rtmpAddressView = $options['rtmp_server'];
				}

				$userID = get_post_field( 'post_author', $postID );

				//$keyBroadcast = md5('vw' . $options['webKey'] . $userID . $postID);
				// $streamQuery = $stream_webrtc . '?channel_id=' . $postID . '&userID=' . $userID . '&key=' . urlencode($keyBroadcast) . '&transcoding=1';

				$streamQuery = VWliveStreaming::webrtcStreamQuery($userID, $postID, 1, $stream_webrtc, $options, 1);


				//paths
				$uploadsPath = $options['uploadsPath'];
				if (!file_exists($uploadsPath)) mkdir($uploadsPath);
				$upath = $uploadsPath . "/$stream/";
				if (!file_exists($upath)) mkdir($upath);


				if (!$transcoding) //transcode to rtsp
					{

					//start transcoding process
					$log_file =  $upath . "transcode_rtmp-webrtc.log";

					$cmd = $options['ffmpegPath'] .' ' .  $options['ffmpegTranscodeRTC'] .
						" -threads 1 -f rtsp \"" . $options['rtsp_server_publish'] . '/'. $streamQuery .
						"\" -i \"" . $rtmpAddressView ."/". $stream . "\" >&$log_file & ";


					//echo $cmd;
					exec($cmd, $output, $returnvalue);
					exec("echo '$cmd' >> $log_file.cmd", $output, $returnvalue);

					update_post_meta( $postID, 'stream-webrtc',  $stream_webrtc );
				}
				else update_post_meta($postID, 'transcoding-webrtc', time());

			}


			if ($sourceProtocol == 'rtsp' && $sourceType != 'restream') //source available as RTSP (WebRTC) and is not a restream (handled from rtmp)
				{

				if (!$options['transcodeFromRTC']) return $stream; //from webrtc transcoding disabled: return original stream

				//RTSP to HLS/RTMP (h264/aac)
				$stream_hls = 'i_' . $stream;

				if ($tooSoon) return $stream_hls;


				//detect transcoding process - cancel if already started
				$cmd = "ps aux | grep '/$stream_hls -i rtsp'";
				exec($cmd, $output, $returnvalue);

				$transcoding = 0;
				foreach ($output as $line)
					if (strstr($line, "ffmpeg"))
					{
						$transcoding = 1;
						break;
					}

				//rtmp keys
				if ($options['externalKeysTranscoder'])
				{
					$channel = get_post($postID);
					$userID = $channel->post_author;

					$key = md5('vw' . $options['webKey'] . $userID . $postID);
					$rtmpAddress = $options['rtmp_server'] . '?'. urlencode($stream_hls) .'&'. urlencode($stream) .'&'. $key . '&1&' . $userID . '&videowhisper';
				}
				else
				{
					$rtmpAddress = $options['rtmp_server'];
					$rtmpAddressView = $options['rtmp_server'];
				}

				//paths
				$uploadsPath = $options['uploadsPath'];
				if (!file_exists($uploadsPath)) mkdir($uploadsPath);
				$upath = $uploadsPath . "/$stream/";
				if (!file_exists($upath)) mkdir($upath);

				if (!$transcoding) //transcode to rtmp
					{

					if ($detect == 2 || ($detect == 1 && !$videoCodec))
					{

						//detect webrtc stream info
						$log_file =  $upath . "streaminfo-webrtc.log";

						$cmd = 'timeout -s KILL 3 ' . $options['ffmpegPath'] .' -y -i "' . $options['rtsp_server'] . '/' . $stream . '" 2>&1 ';
						$info = shell_exec($cmd);

						//video
						if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Video: (?P<videocodec>.*)/',$info,$matches))
							preg_match('/Could not find codec parameters \(Video: (?P<videocodec>.*)/',$info,$matches);
						list($videoCodec) = explode(' ',$matches[1]);
						if ($videoCodec && $postID) update_post_meta( $postID, 'stream-codec-video', strtolower($videoCodec) );

						//audio
						$matches = array();
						if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Audio: (?P<audiocodec>.*)/',$info,$matches))
							preg_match('/Could not find codec parameters \(Audio: (?P<audiocodec>.*)/',$info,$matches);

						list($audioCodec) = explode(' ',$matches[1]);
						$audioCodec = trim($audioCodec, " ,.\t\n\r\0\x0B");
						if ($audioCodec && $postID) update_post_meta( $postID, 'stream-codec-audio', strtolower($audioCodec) );
						if (($videoCodec || $audioCodec) && $postID) update_post_meta( $postID, 'stream-codec-detect', time() );

						exec("echo '". "$stream|$stream_hls|$stream_webrtc|$transcodeEnabled|$detect|$videoCodec|$audioCodec" ."' >> $log_file", $output, $returnvalue);
						exec("echo \"". addslashes($info)."\" >> $log_file", $output, $returnvalue);

						//
					}


					//start transcoding process
					$log_file =  $upath . "transcode_webrtc-hls.log";

					if ($videoCodec && $audioCodec) //if incomplete, transcode later
						{
						//convert command
						$cmd = $options['ffmpegPath'] .' ' .  $options['ffmpegTranscode'] . " -threads 1 -f flv \"" .
							$rtmpAddress . "/". $stream_hls . "\" -i \"" . $options['rtsp_server'] ."/". $stream . "\" >&$log_file & ";

						//echo $cmd;
						exec($cmd, $output, $returnvalue);
						exec("echo '$cmd' >> $log_file.cmd", $output, $returnvalue);

						update_post_meta( $postID, 'stream-hls',  $stream_hls );
					}
					else exec("echo 'Stream incomplete. Will check again later... ' >> $log_file", $output, $returnvalue);

				}
				else update_post_meta($postID, 'transcoding-hls', time());

			}

		}

		static function responsiveStream($default, $postID, $player = 'flash')
		{
			if (!$postID) return $default;

			$sourceProtocol = get_post_meta($postID, 'stream-protocol', true);

			if ($player == 'flash')
				if ($sourceProtocol == 'rtsp') //may require transcoding
					{
					$transcode = 0;

					$videoCodec = get_post_meta($postID, 'stream-codec-video', true);
					$audioCodec = get_post_meta($postID, 'stream-codec-audio', true);

					if (!in_array($videoCodec, array('h264')) ) $transcode =1;
					if (!in_array($audioCodec, array('aac','speex')) ) $transcode =1;

					if (!$transcode) return $default;

					$stream_hls = get_post_meta( $postID, 'stream-hls',  true );
					if ($stream_hls) return $stream_hls;
				}

			return $default;
		}

		static function transcodeStream($stream, $required=0, $detect=2, $convert=1)
		{
			//$stream = room name
			//$detect: 0 = no, 1 = auto, 2 = always (update)
			//$convert: 0 = no, 1 = auto , 2 = always

			if (!$stream) return;

			$options = get_option('VWliveStreamingOptions');


			//is it a post channel?
			global $wpdb;
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );


			//is feature enabled?
			if ($postID)
			{
				$sourceProtocol = get_post_meta($postID, 'stream-protocol', true);
				$sourceType = get_post_meta($postID, 'stream-type', true); // stream-type: flash/external/webrtc/restream/playlist
				$stream_hls = get_post_meta($postID, 'stream-hls', true);

				$transcodeEnabled = get_post_meta($postID, 'vw_transcode', true);
				$videoCodec = get_post_meta($postID, 'stream-codec-video', true);

				$reStream = get_post_meta( $postID, 'vw_ipCamera', true );

			}
			else
			{
				if ($options['anyChannels'] || $options['userChannels']) $transcodeEnabled = 1;
			}

			if (in_array($sourceProtocol, array('http', 'https'))) $stream_hls = $stream; // as is for http streams


			if ( !$options['transcodingAuto'] && $convert != 2) return $stream_hls; //disabled

			//direct delivery for restream/external/playlist : do not transcode
			if (($reStream && !$options['transcodeReStreams']) || ($sourceType == 'external' && !$options['transcodeExternal']) || $sourceType == 'playlist')
			{
				update_post_meta( $postID, 'stream-hls', $stream );

				return $stream;
			}


			if (!VWliveStreaming::timeTo($stream . '/transcodeCheckRTMP-Flood', 3, $options)) return $stream_hls; //prevent duplicate checks

			// check every 59s
			if (!$required)
				if (!VWliveStreaming::timeTo($stream . '/transcodeCheckRTMP', 59, $options)) return $stream_hls;


				//also transcode for webrtc if enabled
				if ($options['webrtc']) VWliveStreaming::transcodeStreamWebRTC($stream, $postID, $options);


				//rtsp is from webrtc or restream (restream are also handled by media server)
				if ($sourceProtocol == 'rtsp' && $sourceType != 'restream') return $stream_hls; //transcoded by transcodeStreamWebRTC() - use that

				if (in_array($sourceProtocol, array('http', 'https'))) return $stream; //return http streams as is

				//HLS
				//Doing RTMP to HLS/RTMP (H264/AAC)

				$stream_hls = 'i_'. $stream; //transcoded stream

			//detect transcoding process - cancel if already started
			$cmd = "ps aux | grep '/$stream_hls -i rtmp'";
			exec($cmd, $output, $returnvalue);
			//var_dump($output);

			$transcoding = 0;
			foreach ($output as $line)
				if (strstr($line, "ffmpeg"))
				{
					$transcoding = 1;
					break;
				}

			if ($transcoding) return $stream_hls; //already transcoding - nothing to do

			//rtmp keys
			if ($options['externalKeysTranscoder'])
			{
				$userID = get_post_field( 'post_author', $postID );

				$key = md5('vw' . $options['webKey'] . $userID . $postID);

				$keyView = md5('vw' . $options['webKey']. $postID);

				//?session&room&key&broadcaster&broadcasterid
				$rtmpAddress = $options['rtmp_server'] . '?'. urlencode($stream_hls) .'&'. urlencode($stream) .'&'. $key . '&1&' . $userID . '&videowhisper';
				$rtmpAddressView = $options['rtmp_server'] . '?'. urlencode('ffmpegView_' . $stream) .'&'. urlencode($stream) .'&'. $keyView . '&0&videowhisper';
				$rtmpAddressViewI = $options['rtmp_server'] . '?'. urlencode('ffmpegInfo_' . $stream) .'&'. urlencode($stream) .'&'. $keyView . '&0&videowhisper';

			}
			else
			{
				$rtmpAddress = $options['rtmp_server'];
				$rtmpAddressView = $options['rtmp_server'];
			}

			//paths
			$uploadsPath = $options['uploadsPath'];
			if (!file_exists($uploadsPath)) mkdir($uploadsPath);

			$upath = $uploadsPath . "/$stream/";
			if (!file_exists($upath)) mkdir($upath);


			//detect codecs - do transcoding only if necessary
			if ($detect == 2 || ($detect == 1 && !$videoCodec))
			{

				$log_file =  $upath . "streaminfo-rtmp.log";

				$cmd = 'timeout -s KILL 3 ' . $options['ffmpegPath'] .' -y -i "' . $rtmpAddressViewI .'/'. $stream . '" 2>&1 ';
				$info = shell_exec($cmd);

				//video
				if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Video: (?P<videocodec>.*)/',$info,$matches))
					preg_match('/Could not find codec parameters \(Video: (?P<videocodec>.*)/',$info,$matches);
				list($videoCodec) = explode(' ',$matches[1]);
				if ($videoCodec && $postID) update_post_meta( $postID, 'stream-codec-video', strtolower($videoCodec) );

				//audio
				$matches = array();
				if (!preg_match('/Stream #(?:[0-9\.]+)(?:.*)\: Audio: (?P<audiocodec>.*)/',$info,$matches))
					preg_match('/Could not find codec parameters \(Audio: (?P<audiocodec>.*)/',$info,$matches);

				list($audioCodec) = explode(' ',$matches[1]);
				$audioCodec = trim($audioCodec, " ,.\t\n\r\0\x0B");
				if ($audioCodec && $postID) update_post_meta( $postID, 'stream-codec-audio', strtolower($audioCodec) );

				if (($videoCodec || $audioCodec) && $postID) update_post_meta( $postID, 'stream-codec-detect', time() );

				exec("echo '". "$stream|$stream_hls|$transcodeEnabled|$required|$detect|$convert|$videoCodec|$audioCodec" ."' >> $log_file", $output, $returnvalue);

				exec("echo \"". addslashes($info)."\" >> $log_file", $output, $returnvalue);

				exec("echo '$cmd' >> $log_file.cmd", $output, $returnvalue);

				$lastLog = $options['uploadsPath'] . '/lastLog-streamInfo.txt';
				self::varSave($lastLog, [ 'file'=>$log_file, 'cmd' => $cmd, 'return' => $returnvalue, 'output0' => $output[0], 'time' =>time()] );

			}


			//do any conversions after detection
			if ($convert)
			{


				if ($postID)
				{

					if (!$videoCodec) $videoCodec = get_post_meta($postID, 'stream-codec-video', true);
					if (!$audioCodec) $audioCodec = get_post_meta($postID, 'stream-codec-audio', true);

					//valid mp4 for html5 playback?
					if (($videoCodec == 'h264') && ($audioCodec == 'aac')) $isMP4 =1;
					else $isMP4 = 0;

					if ($isMP4 && $convert == 1)
					{
						update_post_meta( $postID, 'stream-hls', $stream ); //stream is good for hls (when broadcast directly AAC with OBS)
						return $stream; //present format is fine - no conversion required
					}

				}

				if (!$transcodeEnabled) return ''; //transcoding disabled


				//start transcoding process
				$log_file =  $upath . "transcode-rtmp.log";

				if  ($videoCodec && $audioCodec) //if incomplete, transcode later
					{

					//convert command
					$cmd = $options['ffmpegPath'] .' ' .  $options['ffmpegTranscode'] . " -threads 1 -f flv \"" .
						$rtmpAddress . "/". $stream_hls . "\" -i \"" . $rtmpAddressView ."/". $stream . "\" >&$log_file & ";

					//log and executed cmd
					exec("echo '" . date(DATE_RFC2822) . "|$convert|$transcodeEnabled|$stream|$stream_hls|$postID|$isMP4:: $cmd' >> $log_file.cmd", $output, $returnvalue);
					exec($cmd, $output, $returnvalue);

					$lastLog = $options['uploadsPath'] . '/lastLog-streamTranscode.txt';
					self::varSave($lastLog, [ 'file'=>$log_file, 'cmd' => $cmd, 'return' => $returnvalue, 'output0' => $output[0], 'time' =>time()] );


					//$cmd = "ps aux | grep '/i_$stream -i rtmp'";
					//exec($cmd, $output, $returnvalue);

					update_post_meta( $postID, 'stream-hls', $stream_hls );

				}
				else exec("echo 'Stream incomplete. Will check again later... ' >> $log_file", $output, $returnvalue);



				return $stream_hls;
			}


		}

		function videowhisper_hls($atts)
		{
			$stream = '';

			$options = get_option('VWliveStreamingOptions');

			if (is_single())
				if (get_post_type( $postID = get_the_ID() ) == $options['custom_post']) $stream = get_the_title($postID);

				$atts = shortcode_atts(array('channel' => $stream, 'width' => '480px', 'height' => '360px', 'silent' => '0'), $atts, 'videowhisper_hls');

			if (!$stream) $stream = $atts['channel']; //parameter channel="name"
			if (!$stream) $stream = $_GET['n'];

			$stream = sanitize_file_name($stream);

			$width=$atts['width']; if (!$width) $width = "480px";
			$height=$atts['height']; if (!$height) $height = "360px";

			if (!$stream)
			{
				return "Watch HLS Error: Missing channel name!";
			}

			//get channel id $postID
			global $wpdb;
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );

			//auto transcoding (on request)
			if ($options['transcodingAuto'])
			{
				$streamName = VWliveStreaming::transcodeStream($stream, 1); //require transcoding name
			}

			//get compatible stream
			$sourceProtocol = get_post_meta($postID, 'stream-protocol', true);
			if ($sourceProtocol == 'rtsp') //requires transcoding
				{
				$stream_hls = get_post_meta( $postID, 'stream-hls',  true );
				if ($stream_hls) $streamName = $stream_hls;
			}

			if (!$streamName) $streamName = $stream;


			if ($streamName)
			{
				$streamURL = $options['httpstreamer'] . $streamName . '/playlist.m3u8';


				$dir = $options['uploadsPath']. "/_thumbs";
				$thumbFilename = "$dir/" . $stream . ".jpg";
				$thumbUrl =  VWliveStreaming::path2url($thumbFilename) . '?nocache='.((time()/10)%100);

				$codecAudio = get_post_meta($postID, 'stream-codec-audio', true);
				$codecVideo = get_post_meta($postID, 'stream-codec-video', true);
				
				
				$edate = get_post_meta($postID, 'edate', true);
				if (time() - $edate > 60) //offline: show offline video if set
				{
					$offline_video = get_post_meta($postID, 'offline_video', true);
					if ($offline_video) $videoURL = self::vsvVideoURL($offline_video, $options);
					if ($videoURL) $streamURL = $videoURL;
				}


				$htmlCode = <<<HTMLCODE
<!--HLS:$postID:p=$sourceProtocol:s=$stream:sh=$stream_hls:cv=$codecVideo:ca=$codecAudio:e=$edate:vof=$offline_video-->
<video id="videowhisper_hls_$stream" class="videowhisper_htmlvideo" width="$width" height="$height" autobuffer autoplay playsinline controls poster="$thumbUrl">
 <source src="$streamURL" type='video/mp4'>
    <div class="fallback" style="display:none">
        <IMG SRC="$thumbUrl">
	    <p>You must have a HTML5 capable browser with HLS support (Ex. Safari) to open this live stream: $streamURL</p>
	</div>
</video>
<span id="sdpDataTag"></span>

<script>

var myVideo = document.getElementById("videowhisper_hls_$stream");
var videoLoaded = false;

myVideo.addEventListener('loadeddata', function() {
	videoLoaded = true;
	if (myVideo.paused)
	{
	   var playPromise = myVideo.play();
	   playPromise.then(function() {
	    // Automatic playback started!
	  }).catch(function(error) {
		 
	  jQuery("#sdpDataTag").html('<br><button class="ui button compact red" onclick="myVideo.play();jQuery(\'#sdpDataTag\').html(\'\')"><i class="play icon"></i> Tap to Play</button> <small><br>' + error + '</small>');	
	  console.log('Warning: Could not autoplay $stream:', error);
	  });
    }

}, false);

  setInterval(function()
  {
	  if (!videoLoaded) if (myVideo.paused)
	  {
	   myVideo.load();
	   console.log('Warning: HLS $stream not loaded. Trying to reload...', myVideo.currentTime, myVideo.paused);
      }

   }, 3500);

</script>
HTMLCODE;
			}
			else $htmlCode = 'HLS format is not available and can not be transcoded for stream: '. $stream;

			if ($options['transcodingWarning']>=2 & !$atts['silent']) $htmlCode .= '<p class="info"><small>HLS Playback: for iOS and Safari. Enable sound from controls. Transcoding and HTTP based delivery technology involve high latency and availability delay (may take dozens of seconds for transcoder to start stream to become available, after broadcast starts).</small></p>';

			return $htmlCode;
		}

		function videowhisper_mpeg($atts)
		{

			$stream = '';

			$options = get_option('VWliveStreamingOptions');

			if (is_single())
				if (get_post_type( $postID = get_the_ID() ) == $options['custom_post']) $stream = get_the_title($postID);

				$atts = shortcode_atts(array('channel' => $stream, 'width' => '480px', 'height' => '360px','silent' => '0'), $atts, 'videowhisper_mpeg');


			if (!$stream) $stream = $atts['channel']; //parameter channel="name"
			if (!$stream) $stream = $_GET['n'];

			$width=$atts['width']; if (!$width) $width = "480px";
			$height=$atts['height']; if (!$height) $height = "360px";

			if (!$stream)
			{
				return "Watch MPEG Dash Error: Missing channel name!";
			}

			//get channel id $postID
			global $wpdb;
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );

			//auto transcoding
			if ($options['transcodingAuto'])
			{
				$streamName = VWliveStreaming::transcodeStream($stream, 1); //require transcoding name
			}

			//get compatible stream
			$sourceProtocol = get_post_meta($postID, 'stream-protocol', true);
			if ($sourceProtocol == 'rtsp') //requires transcoding
				{
				$stream_hls = get_post_meta( $postID, 'stream-hls',  true );
				if ($stream_hls) $streamName = $stream_hls;
			}

			if (!$streamName) $streamName = $stream;

			if ($streamName)
			{
				$streamURL = $options['httpstreamer'] . $streamName .'/manifest.mpd';


				$dir = $options['uploadsPath']. "/_thumbs";
				$thumbFilename = "$dir/" . $stream . ".jpg";
				$thumbUrl =  VWliveStreaming::path2url($thumbFilename) . '?nocache='.((time()/10)%100);;


				//if (strstr($streamURL,'http://')) wp_enqueue_script('dashjs', 'http://cdn.dashjs.org/latest/dash.all.min.js');
				//else wp_enqueue_script('dashjs', 'https://cdn.dashjs.org/latest/dash.all.min.js');
				
				//Shaka Player https://github.com/google/shaka-player 
				//scripts/shaka-player/shaka-player.compiled.min.js
				//https://cdnjs.cloudflare.com/ajax/libs/shaka-player/3.0.1/shaka-player.compiled.min.js

				if (strstr($streamURL,'http://')) wp_enqueue_script('dashjs', 'http://cdnjs.cloudflare.com/ajax/libs/shaka-player/3.0.1/shaka-player.compiled.min.js');
				else wp_enqueue_script('dashjs', 'https://cdnjs.cloudflare.com/ajax/libs/shaka-player/3.0.1/shaka-player.compiled.min.js');

				$codecVideo = get_post_meta($postID, 'stream-codec-video', true);
				$codecAudio = get_post_meta($postID, 'stream-codec-audio', true);

				$offline = 0;
				$edate = get_post_meta($postID, 'edate', true);
				if (time() - $edate > 60) //offline: show offline video if set
				{
					$offline_video = get_post_meta($postID, 'offline_video', true);
					if ($offline_video) 
					{
						$videoURL = self::vsvVideoURL($offline_video, $options);
						$offline = 1;

					}
					
					if ($videoURL) $streamURL = $videoURL;
				}
				
				$htmlCode = <<<HTMLCODE
<!--MPEG:$postID:$sourceProtocol:$stream:stream_hls=$stream_hls:$codecVideo:$codecAudio:$streamURL:offlineVideo=$videoURL-->
<video id="videowhisper_mpeg_$stream" class="videowhisper_htmlvideo" width="$width" height="$height" data-dashjs-player autobuffer autoplay playsinline controls="true" poster="$thumbUrl" src="$streamURL">
    <div class="fallback" style="display:none">
    <IMG SRC="$thumbUrl">
	    <p>HTML5 MPEG Dash capable browser (i.e. Chrome) is required to open this live stream: $streamURL</p>
	</div>
</video>
<span id="sdpDataTag"></span>
<script>
var manifestUri = '$streamURL';
var offline = $offline;

var myVideo = document.getElementById("videowhisper_mpeg_$stream");

//console.log('shaka', myVideo);

function initApp() {
	
  if (!offline) 
  {
  shaka.polyfill.installAll();
  
    if (shaka.Player.isBrowserSupported()) 
    {
    initPlayer();
     } else {
    console.error('MPEG-DASH Shaka Player: Browser not supported!');
  }
  }

//autoplay check/button
myVideo = document.getElementById("videowhisper_mpeg_$stream");
var videoLoaded = false;

myVideo.addEventListener('loadeddata', function() {
	
	console.log('video loadeddata', myVideo.paused, myVideo.currentTime);

	videoLoaded = true;
	if (myVideo.paused)
	{
	   var playPromise = myVideo.play();
	   playPromise.then(function() {
	   // Automatic playback started!
	   console.log('Automatic playback started?!');
	  }).catch(function(error) {
	  jQuery("#sdpDataTag").html('<br><button class="ui button compact red" onclick="myVideo.play();jQuery(\'#sdpDataTag\').html(\'\')"><i class="play icon"></i> Tap to Play</button><br><small>' + error+ '</small>');			  
	  console.log('Warning: Could not autoplay $stream:', error);
	  });
    }

}, false);

  setInterval(function()
  {
	  if (!videoLoaded) if (myVideo.paused)
	  {
	   myVideo.load();
	   console.log('Warning: MPEG $stream not loaded. Trying to reload...', myVideo.currentTime, myVideo.paused);
      }

   }, 3500);


 
}

function initPlayer() {
  var video = document.getElementById('videowhisper_mpeg_$stream');
  var player = new shaka.Player(video);
  window.player = player;
  player.addEventListener('error', onErrorEvent);
  player.load(manifestUri).then(function() {
	  //success
  }).catch(onError);
}

function onErrorEvent(event) {
  onError(event.detail);
}

function onError(error) {
  console.error('MPEG-DASH Player: Error code', error.code, 'object', error);
}

document.addEventListener('DOMContentLoaded', initApp);
</script>
HTMLCODE;
			}
			else $htmlCode = '<div class="warning">MPEG Dash format is not currently available for this stream: '. $stream.'</div>';

			if ($options['transcodingWarning']>=2 && !$atts['silent']) $htmlCode .= '<p><small>MPEG-DASH Playback: For Android and Chrome. Autoplay starts muted to prevent pausing by browser: enable sound from controls. Transcoding and HTTP based delivery technology involve high latency and availability delay (may take dozens of seconds for transcoder to start stream to become available, after broadcast starts).</small></p>';

			return $htmlCode;
		}



		function flash_video($stream, $width = "100%", $height = '360px', $streamName = '')
		{

			$stream = sanitize_file_name($stream);

			if (!$streamName) $streamName = $stream;

			$swfurl = plugin_dir_url(__FILE__) . "ls/live_video.swf?ssl=1&n=" . urlencode($stream). '&s=' . urlencode($streamName);
			$swfurl .= "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vwls&task=');
			$swfurl .= '&extension='.urlencode('_none_');
			$swfurl .= '&ws_res=' . urlencode( plugin_dir_url(__FILE__) . 'ls/');

			$bgcolor="#333333";

			$htmlCode = <<<HTMLCODE
<div id="videowhisper_container_$stream" style="overflow:auto">
<object id="videowhisper_video_$stream" width="$width" height="$height" type="application/x-shockwave-flash" data="$swfurl">
<param name="movie" value="$swfurl"></param><param bgcolor="$bgcolor"><param name="scale" value="noscale" /> </param><param name="salign" value="lt"></param><param name="allowFullScreen"
value="true"></param><param name="allowscriptaccess" value="always"></param>
</object>
</div>
HTMLCODE;

			$htmlCode .= VWliveStreaming::flash_warn();

			return $htmlCode;

		}

		function videowhisper_video($atts)
		{
			$stream = '';

			$options = get_option('VWliveStreamingOptions');

			if (is_single())
				if (get_post_type( $postID = get_the_ID() ) == $options['custom_post']) $stream = get_the_title($postID);

				$atts = shortcode_atts(array(
						'channel' => $stream,
						'width' => '480px',
						'height' => '360px',
						'flash' => '0',
						'html5' => 'auto',
						'silent' => '0'
					), $atts, 'videowhisper_video');

			if (!$stream) $stream = $atts['channel']; //parameter channel="name"
			if (!$stream) $stream = $_GET['n'];

			$stream = sanitize_file_name($stream);

			$width=$atts['width']; if (!$width) $width = "100%";
			$height=$atts['height'];
			if (!$height)  $height = '360px';

			if (!$stream)
			{
				return "Watch Video Error: Missing channel name!";

			}

			//channel post id
			global $wpdb;
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );


			//handle paused restreams
			$vw_ipCamera = get_post_meta( $postID, 'vw_ipCamera', true );
			if ($vw_ipCamera) self::restreamPause($postID, $stream, $options);


			if (!$atts['flash']) //html5
				{

				//source info
				$streamProtocol = get_post_meta($postID, 'stream-protocol', true); // rtsp/rtmp
				$streamType = get_post_meta($postID, 'stream-type', true); // stream-type: flash/webrtc/restream/playlist
				$streamMode =  get_post_meta($postID, 'stream-mode', true); // direct/safari_pc

				$directWebRTC = 0;
				//if ($streamProtocol == 'rtsp' && $streamMode =='direct') $directWebRTC = 1; //preferred html5 for low latency /safari_pc on h264
				if ($streamProtocol == 'rtsp' && !$vw_ipCamera) $directWebRTC = 1; //preferred html5 for low latency


				//HLS if iOS/Android detected
				$agent = $_SERVER['HTTP_USER_AGENT'];
				$Android = stripos($agent,"Android");
				$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));
				$Safari = (strstr($agent,"Safari") && !strstr($agent,"Chrome"));

				//offline: regular h5 playaback - no webrtc
				$isOffline=0;
				$edate = get_post_meta($postID, 'edate', true);
				if (time() - $edate > 60)  $isOffline = 1;
				
				if ($options['webStatus'] == 'disabled') $isOffline = 0; //without session control can't know when offline


				$htmlCode .= "<!--VideoWhisper-Stream:offline=$isOffline:protocol=$streamProtocol|type=$streamType|mode=$streamMode|directWebRTC:$directWebRTC|Agent:$agent|A:$Android|I:$iOS|S:$Safari|F:$Firefox-->";

				$silent = $atts['silent'];


	
				$showHTML5 = 0;
				if ($options['transcoding'] >= 3 || $options['webrtc'] >= 3) $showHTML5 = 1; //html5 preferred

				//always
				if ($atts['html5'] == 'always' || $showHTML5)
				{
						if ($directWebRTC && !$isOffline) return $htmlCode.'<!--H5-WebRTC-->'. do_shortcode("[videowhisper_webrtc_playback channel=\"$stream\" width=\"$width\" height=\"$height\" silent=\"$silent\" webstatus=\"1\"]");

					if ($iOS || $Safari) return $htmlCode.'<!--H5-HLS-->'. do_shortcode("[videowhisper_hls channel=\"$stream\" width=\"$width\" height=\"$height\" silent=\"$silent\" webstatus=\"1\"]");
					else return $htmlCode.'<!--H5-MPEG-->'. do_shortcode("[videowhisper_mpeg channel=\"$stream\" width=\"$width\" height=\"$height\" silent=\"$silent\" webstatus=\"1\"]");
				}

				if ($directWebRTC && !$isOffline) return $htmlCode.'<!--H5-WebRTC-->'. do_shortcode("[videowhisper_webrtc_playback channel=\"$stream\" width=\"$width\" height=\"$height\" silent=\"$silent\" webstatus=\"1\"]");

				//detect delivery mode for video interface
				if ( ($Android && in_array($options['detect_mpeg'], array('android', 'all'))) || (!$iOS && in_array($options['detect_mpeg'], array('all'))) || (!$iOS && !$Safari && in_array($options['detect_mpeg'], array('nonsafari'))) )
					return $htmlCode .'<!--MPEG-->'. do_shortcode("[videowhisper_mpeg channel=\"$stream\" width=\"$width\" height=\"$height\" silent=\"$silent\" webstatus=\"1\"]");

				if ( (($Android||$iOS) && in_array($options['detect_hls'], array('mobile','safari', 'all'))) || ($iOS && $options['detect_hls'] == 'ios') || ($Safari && in_array($options['detect_hls'], array('safari', 'all'))) ) return $htmlCode .'<!--HLS-->'. do_shortcode("[videowhisper_hls channel=\"$stream\" width=\"$width\" height=\"$height\" silent=\"$silent\" webstatus=\"1\"]");
			}

			//flash
			$afterCode = <<<HTMLCODE
<br style="clear:both" />

<style type="text/css">
<!--

#videowhisper_container_$stream
{
position: relative;
width: $width;
height: $height;
border: solid 1px #999;
}

-->
</style>
HTMLCODE;



			//default stream to play
			$streamName = $stream;

			if ($postID)
			{
				//get compatible stream
				$streamName = VWliveStreaming::responsiveStream($streamName, $postID, 'flash');

			}

			return VWliveStreaming::flash_video($stream, $width, $height, $streamName) . $afterCode;

		}

		//! WebRTC


		function videowhisper_webrtc_broadcast($atts)
		{
			$stream = '';

			if (!is_user_logged_in()) return "<div class='error'>" . __('Broadcasting not allowed: Only logged in users can broadcast!', 'live-streaming') . '</div>';

			$options = get_option('VWliveStreamingOptions');

			//username used with application
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

			$current_user = wp_get_current_user();
			if ($current_user->$userName) $username = sanitize_file_name($current_user->$userName);

			$postID = 0;
			if ($options['postChannels']) //1. channel post
				{
				if (is_single())
				{
					$postID = get_the_ID();
					if (get_post_type( $postID ) ==  $options['custom_post'] ) $stream = get_the_title($postID);
					else $postID = 0;

				}
			}

			$atts = shortcode_atts(array(
					'channel' => $stream,
					'channel_id' => $postID,
				), $atts, 'videowhisper_webrtc_broadcast');

			$postID = $atts['channel_id'];

			if ($stream && !$postID)
			{
				global $wpdb;
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
			}

			if (!$stream) $stream = $atts['channel']; //2. shortcode param

			if ($options['anyChannels']) if (!$stream) $stream = $_GET['n']; //3. GET param

				if ($options['userChannels']) if (!$stream) $stream = $username; //4. username

					$stream = sanitize_file_name($stream);

				if (!$stream) return "<div class='error'>Can't load WebRTC broadcasting interface: Missing channel name!</div>";

				if ($postID>0 && $options['postChannels'])
				{
					$channel = get_post( $postID );
					if ($channel->post_author != $current_user->ID) return "<div class='error'>Only owner can broadcast his channel (#$postID)!</div>";
				}

			//$keyBroadcast = md5('vw' . $options['webKey'] . $current_user->ID  . $postID);
			//$streamQuery = $stream . '?channel_id=' . $postID . '&userID=' . urlencode($current_user->ID ) . '&key=' . urlencode($keyBroadcast);

			$streamQuery = VWliveStreaming::webrtcStreamQuery($current_user->ID, $postID, 1, $stream, $options);

			//$clientIP = VWliveStreaming::get_ip_address();
			//VWliveStreaming::webSessionSave($stream, 1, 'webrtc-broadcast', $clientIP); //pre-approve session for rtmp check

			//detect browser
			$agent = $_SERVER['HTTP_USER_AGENT'];
			$Android = stripos($agent,"Android");
			$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));
			$Safari = (strstr($agent,"Safari") && !strstr($agent,"Chrome"));
			$Firefox = stripos($agent,"Firefox");

			//publishing WebRTC - save info
			update_post_meta($postID, 'stream-protocol', 'rtsp');
			update_post_meta($postID, 'stream-type', 'webrtc');

			if (!$iOS && $Safari) update_post_meta($postID, 'stream-mode', 'safari_pc'); //safari on pc encoding profile issues
			else update_post_meta($postID, 'stream-mode', 'direct');

			VWliveStreaming::enqueueUI();

			wp_enqueue_script( 'webrtc-adapter', plugin_dir_url(  __FILE__ ) . 'scripts/adapter.js', array('jquery'));
			wp_enqueue_script( 'videowhisper-webrtc-broadcast', plugin_dir_url(  __FILE__ ) . 'scripts/vwrtc-publish.js', array('jquery', 'webrtc-adapter'));


			$wsURLWebRTC = $options['wsURLWebRTC'];
			$applicationWebRTC = $options['applicationWebRTC'];

			$videoCodec = $options['webrtcVideoCodec']; //42e01f
			$audioCodec = $options['webrtcAudioCodec']; //opus

			$videoBitrate = (int) $options['webrtcVideoBitrate'];
			if (!$videoBitrate) $videoBitrate = 400; //400 max for tcp with Wowza

			$htmlCode .= "<!--WebRTC_Broadcast|$agent|i:$iOS|a:$Android|Sa:$Safari|Ff:$Firefox-->";

			$interfaceClass = $options['interfaceClass'];

			$broadcastCode = <<<HTMLCODE
		<div class="videowhisper-webrtc-camera">
		<video id="localVideo" class="videowhisper_htmlvideo" autoplay playsinline muted style="widht:640px;height:480px;"></video>
		</div>

		<div class="ui segment form $interfaceClass">
			<span id="sdpDataTag">Connecting...</span>

<hr class="divider" />
	 <div class="field">
        <input type=button class="ui button compact $interfaceClass" id="buttonBroadcast" value="Broadcast" /> <span id="buttonDataTag"></span>
    </div>

    <div class="field ">
        <label for="videoSource">Video Source </label><select class="ui dropdown $interfaceClass" id="videoSource"></select>
    </div>

    <div class="field ">
        <label for="videoResolution">Video Quality </label><select class="ui dropdown $interfaceClass" id="videoResolution"></select>
    </div>

	 <div class="field ">
        <label for="audioSource">Audio Source </label><select class="ui dropdown $interfaceClass" id="audioSource"></select>
    </div>

    		</div>


		<script type="text/javascript">

			var userAgent = navigator.userAgent;
		    var wsURL = "$wsURLWebRTC";
			var streamInfo = {applicationName:"$applicationWebRTC", streamName:"$streamQuery", sessionId:"[empty]"};
			var userData = {param1:"value1","videowhisper":"webrtc-broadcast"};
			var videoBitrateMax = $videoBitrate;
			var audioBitrate = 64;
			var videoFrameRate = "29.97";
			var videoChoice = "$videoCodec";
			var audioChoice = "$audioCodec";

		jQuery( document ).ready(function() {
 		browserReady();
 		jQuery(".ui.dropdown").dropdown();

});
		</script>
HTMLCODE;


			//AJAX Chat for WebRTC broadcasting

			//htmlchat ui
			//css
			wp_enqueue_style( 'jScrollPane', plugin_dir_url(__FILE__) .'/htmlchat/js/jScrollPane/jScrollPane.css');
			wp_enqueue_style( 'htmlchat', plugin_dir_url(__FILE__) .'/htmlchat/css/chat-broadcast.css');

			//js
			wp_enqueue_script("jquery");
			wp_enqueue_script( 'jScrollPane-mousewheel', plugin_dir_url(  __FILE__ ) . '/htmlchat/js/jScrollPane/jquery.mousewheel.js');
			wp_enqueue_script( 'jScrollPane', plugin_dir_url(  __FILE__ ) . '/htmlchat/js/jScrollPane/jScrollPane.min.js');
			wp_enqueue_script( 'htmlchat', plugin_dir_url(  __FILE__ ) . '/htmlchat/js/script.js', array('jquery','jScrollPane'));

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwls_htmlchat&room=' . urlencode(sanitize_file_name($stream));

			$loginCode = '<a href="' . wp_login_url() . '">Login is required to chat!</a>';
			$buttonSFx = plugin_dir_url(__FILE__) . 'ls/templates/live/message.mp3';
			$tipsSFx = plugin_dir_url(__FILE__) . 'ls/templates/live/tips/';

			$interfaceClass = $options['interfaceClass'];


			if ($options['tips'])
			{

				// broacaster: only balance

				$tipbuttonCodes = '<p>Viewers can send you tips. Balance will update shortly after receiving a tip.</p>';


				$tipsCode =<<<TIPSCODE
<div id="tips" class="ui $interfaceClass segment form">
<div class="inline fields">

<div class="ui label olive large $interfaceClass">
  <i class="money bill alternate icon large"></i>Balance: <span id="balanceAmount" class="inline"> - </span>
</div>

$tipbuttonCodes
</div>
</div>
TIPSCODE;
			}



			$htmlCode .= <<<HTMLCODE
<div id="videochatContainer">
<!--Room:$stream-->
<div id="streamContainer">
$broadcastCode
</div>

<div id="chatContainer">

    <div id="chatUsers" class="ui segment $interfaceClass"></div>

    <div id="chatLineHolder"></div>

    <div id="chatBottomBar" class="ui segment $interfaceClass">
    	<div class="tip"></div>

        <form id="loginForm" method="post" action="" class="ui form $interfaceClass">
$loginCode
		</form>

        <form id="submitForm" method="post" action="" class="ui form $interfaceClass">
            <input id="chatText" name="chatText" class="rounded" maxlength="255" />
            <input id="submitButton" type="submit" class="ui button" value="Submit" />
        </form>

    </div>
</div>
</div>
$tipsCode

<script>
var vwChatAjax= '$ajaxurl';
var vwChatButtonSFx =  '$buttonSFx';
var vwChatTipsSFx =  '$tipsSFx';
</script>

HTMLCODE;

			if ($options['transcodingWarning']>=1) $htmlCode .= '<p class="ui segment"><small>Warning: WebRTC will play directly where possible, depending on settings and viewer device. If transcoding is needed for playback, it may take up to a couple of minutes for transcoder to start and WebRTC published stream to become available for RTMP and HLS/MPEG DASH playback.
<BR>For advanced features use advanced web broadcasting interface available in PC browser with Flash plugin.</small></p>' .
					'<p><a class="ui button secondary" href="' . add_query_arg(array('flash-broadcast'=>''), get_permalink($postID)) . '">Try Advanced Flash Broadcast (PC)</a></p>';

			return $htmlCode;


		}

		function videowhisper_webrtc_playback($atts)
		{
			$stream = '';
			$postID = 0;
			$options = get_option('VWliveStreamingOptions');

			if (is_single())
			{
				$postID = get_the_ID();
				if (get_post_type( $postID ) ==  $options['custom_post'] ) $stream = get_the_title($postID);
				else $postID = 0;
			}

			if (!$postID)
			{
				global $wpdb;
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
			}

			$atts = shortcode_atts(array(
					'channel' => $stream,
					'width' => '480px',
					'height' => '360px',
					'channel_id' => $postID,
					'silent' => 0,
				), $atts, 'videowhisper_webrtc_playback');

			if (!$stream) $stream = $atts['channel']; //parameter channel="name"
			if (!$stream) $stream = $_GET['n'];

			$stream = sanitize_file_name($stream);

			$width=$atts['width']; if (!$width) $width = "100%";
			$height=$atts['height']; if (!$height)  $height = '360px';

			if (!$stream)
			{
				return "WebRTC Playback Error: Missing channel name!";
			}

			$userID = 0;
			if (is_user_logged_in())
			{
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
				$current_user = wp_get_current_user();
				if ($current_user->$userName) $username = sanitize_file_name($current_user->$userName);
				$userID = $current_user->ID;
			}

			$postID = $atts['channel_id'];
			if (!$postID)
			{
				global $wpdb;
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
			}




			//detect browser
			$agent = $_SERVER['HTTP_USER_AGENT'];
			$Android = stripos($agent,"Android");
			$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));
			$Safari = (strstr($agent,"Safari") && !strstr($agent,"Chrome"));
			$Firefox = stripos($agent,"Firefox");


			$codeMuted = '';
			if ($Safari)
			{
				//$codeMuted = 'muted';
			}

			//WebRTC playback: detect source type and transcode if necessary
			$sourceProtocol = get_post_meta($postID, 'stream-protocol', true);

			if ($sourceProtocol == 'rtsp') $stream_webrtc = $stream;
			else $stream_webrtc = VWliveStreaming::transcodeStreamWebRTC($stream, $postID, $options);

			//$keyPlayback = md5('vw' . $options['webKey']. $postID . $userID);
			//$streamQuery = $stream_webrtc . '?channel_id=' . $postID . '&userID=' . urlencode($userID) . '&key=' . urlencode($keyPlayback);

			$streamQuery = VWliveStreaming::webrtcStreamQuery($current_user->ID, $postID, 0, $stream_webrtc, $options);

			//$clientIP = VWliveStreaming::get_ip_address();
			//VWliveStreaming::webSessionSave($stream_webrtc, 0, 'webrtc-playback', $clientIP); //approve session for rtmp check


			wp_enqueue_script( 'jquery');
			wp_enqueue_script( 'webrtc-adapter', plugin_dir_url(  __FILE__ ) . 'scripts/adapter.js', array('jquery'));
			wp_enqueue_script( 'videowhisper-webrtc-playback', plugin_dir_url(  __FILE__ ) . 'scripts/vwrtc-playback.js', array('jquery', 'webrtc-adapter'));

			$wsURLWebRTC = $options['wsURLWebRTC'];
			$applicationWebRTC = $options['applicationWebRTC'];


/*
				$edate = get_post_meta($postID, 'edate', true);
				if (time() - $edate > 60) //offline: show offline video if set
				{
					$offline_video = get_post_meta($postID, 'offline_video', true);
					if ($offline_video) $videoURL = self::vsvVideoURL($offline_video, $options);
					if ($videoURL) $streamURL = $videoURL;
					if ($streamURL) $srcCode = 'src="' . $streamURL . '"';
				}
*/
				
			$htmlCode .= <<<HTMLCODE
		<div class="videowhisper-webrtc-video">
		<!--$postID|vof:$offline_video|vu:$videoURL-->
		<video id="remoteVideo" class="videowhisper_htmlvideo" autoplay playsinline controls $codeMuted style="width:$width; height:$height" $srcCode></video>
		<!--$sourceProtocol:$stream_webrtc-->
		</div>

		<span id="sdpDataTag"></span>
    
		<script type="text/javascript">

			var videoBitrate = 600;
			var audioBitrate = 64;
			var videoFrameRate = "29.97";
			var videoChoice = "$videoCodec";
			var audioChoice = "$audioCodec";

			var userAgent = navigator.userAgent;
		    var wsURL = "$wsURLWebRTC";
			var streamInfo = {applicationName:"$applicationWebRTC", streamName:"$streamQuery", sessionId:"[empty]"};
			var userData = {param1:"value1","videowhisper":"webrtc-playback"};

		jQuery( document ).ready(function() {
 		browserReady();
});

		</script>
HTMLCODE;

			if (!$atts['silent'])
			{
				if ($Safari)
				{
					$htmlCode .=  "WebRTC playback is not currently fully supported for Safari (may take longer to start) if not broadcasting with Chrome. If live video does not play or freezes, try opening this URL in Chrome or Firefox!<BR>Additionally playback is muted to allow auto play: enable audio from player controls.";
				}
			}
			return $htmlCode;
		}



		function videowhisper_htmlchat_playback($atts)
		{
			//! playback with html5 video and ajax chat

			$stream = '';
			$options = get_option('VWliveStreamingOptions');

			if (is_single())
			{
				$postID = get_the_ID();
				if (get_post_type( $postID ) ==  $options['custom_post'] ) $stream = get_the_title($postID);
				else $postID = 0;
			}

			if (!$postID)
			{
				global $wpdb;
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
			}

			$atts = shortcode_atts(array(
					'channel' => $stream,
					'post_id'=> $postID,
					'videowidth' => '480px',
					'videoheight' => '360px'
				), $atts, 'videowhisper_htmlchat_playback');

			if (!$stream) $stream = $atts['channel']; //parameter channel="name"
			if (!$stream) $stream = $_GET['room'];
			if (!$stream) $stream = $_GET['n'];

			$stream = sanitize_file_name($stream);

			$room = $stream;

			if ($atts['post_id']) $postID = intval($atts['post_id']);
			/*
			if (!$postID)
			{
				global $wpdb;

				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );

			}
*/
			$videowidth=$atts['videowidth']; if (!$videowidth) $videowidth = "480px";
			$videoheight=$atts['videoheight']; if (!$videoheight)  $videoheight = '360px';

			if (!$room)
			{
				return "HTML AJAX Chat Error: Missing room name!";
			}

			//ui
			VWliveStreaming::enqueueUI();

			//AJAS Chat for viewers

			//htmlchat ui
			//css
			wp_enqueue_style( 'jScrollPane', plugin_dir_url(__FILE__) .'/htmlchat/js/jScrollPane/jScrollPane.css');
			wp_enqueue_style( 'htmlchat', plugin_dir_url(__FILE__) .'/htmlchat/css/chat-watch.css');

			//js
			wp_enqueue_script("jquery");
			wp_enqueue_script( 'jScrollPane-mousewheel', plugin_dir_url(  __FILE__ ) . '/htmlchat/js/jScrollPane/jquery.mousewheel.js');
			wp_enqueue_script( 'jScrollPane', plugin_dir_url(  __FILE__ ) . '/htmlchat/js/jScrollPane/jScrollPane.min.js');
			wp_enqueue_script( 'htmlchat', plugin_dir_url(  __FILE__ ) . '/htmlchat/js/script.js', array('jquery','jScrollPane'));

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwls_htmlchat&room=' . urlencode($room);


			$videoCode = do_shortcode('[videowhisper_video channel="'.$room.'" width="640px" height="480px" silent="1" html5="always"]');

			$loginCode = '<a class="ui button" href="' . wp_login_url() . '">Login is required to chat!</a>';

			$buttonSFx = plugin_dir_url(__FILE__) . 'ls/templates/live/message.mp3';
			$tipsSFx = plugin_dir_url(__FILE__) . 'ls/templates/live/tips/';


				$interfaceClass = $options['interfaceClass'];


			if ($options['tips'])
			{

				//tip options
				$tipOptions = stripslashes($options['tipOptions']);
				if ($tipOptions)
				{
					$p = xml_parser_create();
					xml_parse_into_struct($p, trim($tipOptions), $vals, $index);
					$error = xml_get_error_code($p);
					xml_parser_free($p);

					if (is_array($vals)) foreach ($vals as $tKey=>$tip)
							if ($tip['tag'] == 'TIP')
							{
								//var_dump($tip['attributes']);
								$amount = intval($tip['attributes']['AMOUNT']);
								if (!$amount) $amount = 1;
								$label = $tip['attributes']['LABEL'];
								if (!$label) $label = '$1 Tip';
								$note = $tip['attributes']['NOTE'];
								if (!$note) $label = 'Tip';
								$sound = $tip['attributes']['SOUND'];
								if (!$sound) $sound = 'coins1.mp3';
								$image = $tip['attributes']['IMAGE'];
								if (!$image) $image = 'gift1.png';


								$imageURL = $tipsSFx . $image;

								$tipbuttonCodes .=<<<TBCODE
	<div class="tipButton ui labeled button small $interfaceClass" tabindex="0" amount="$amount" label="$label" note="$note" sound="$sound" image="$image" data-title="Gift $amount!" data-content="Send $amount as gift!">
  
  <div class="ui button">
    <img class="mini image avatar" src="$imageURL"> $label
  </div>
  
  <a class="ui basic label small">
    $amount
  </a>
  
</div>
TBCODE;
							}
				}

				//
				$balanceURL = '#';
				if ($options['balancePage']) $balanceURL = get_permalink( $options['balancePage']);



				$tipsCode =<<<TIPSCODE
<div id="tips" class="ui $interfaceClass segment form">
<div class="inline fields">

<a href="$balanceURL" target="_balance" class="ui label olive large $interfaceClass">
  <i class="money bill alternate icon large"></i>Balance: <span id="balanceAmount" class="inline"> - </span>
</a>

$tipbuttonCodes
</div>
</div>
TIPSCODE;
			}



			$htmlCode =<<<HTMLCODE
<div id="videochatContainer">
<!--$room-->
<div id="streamContainer">
$videoCode
</div>

<div id="chatContainer">
    <div id="chatUsers" class="ui segment $interfaceClass"></div>

    <div id="chatLineHolder"></div>

    <div id="chatBottomBar" class="ui segment $interfaceClass">

    	<div class="tip"></div>

        <form id="loginForm" method="post" action="" class="ui form $interfaceClass">
$loginCode
		</form>

        <form id="submitForm" method="post" action="" class="ui form $interfaceClass">
            <input id="chatText" name="chatText" class="rounded" maxlength="255" />
            <input id="submitButton" id="submit" type="submit" class="ui button" value="Submit" />
        </form>

    </div>

</div>
</div>
$tipsCode

<script>
var vwChatAjax= '$ajaxurl';
var vwChatButtonSFx =  '$buttonSFx';
var vwChatTipsSFx =  '$tipsSFx';

var \$jQ = jQuery.noConflict();
\$jQ(document).ready(function(){
\$jQ('.tipButton').popup();
});

</script>
HTMLCODE;

			if ($options['transcodingWarning']>=2) $htmlCode .= '<p><small>HTML5 Video Stream Playback: <b>Tap to Play</b> as autoplay is not possible in some browsers. Transcoding and HTTP based delivery technology involve extra latency and availability delay related to processing video stream. If stream is live but not showing, please wait or reload page if it does not start in few seconds.</small></p>';

			if ($options['transcodingWarning']>=1) 
				if ($options['transcoding']) if ($options['transcoding']< 4) $htmlCode .= '<p><a class="ui button secondary" href="' . add_query_arg(array('flash-view'=>''), get_permalink($postID)) . '">Try Flash View (PC)</a></p>';

				return $htmlCode;

		}

		//
		static function rtmp_address($userID, $postID, $broadcaster, $session, $room)
		{

			//?session&room&key&broadcaster&broadcasterid

			$options = get_option('VWliveStreamingOptions');


			if ($broadcaster)
			{
				$key = md5('vw' . $options['webKey'] . $userID . $postID);
				return $options['rtmp_server'] . '?'. urlencode($session) .'&'. urlencode($room) .'&'. $key . '&1&' . $userID . '&videowhisper';
			}
			else
			{
				$keyView = md5('vw' . $options['webKey']. $postID);
				return $options['rtmp_server'] . '?'. urlencode('-name-') .'&'. urlencode($room) .'&'. $keyView . '&0' . '&videowhisper';
			}

			return $options['rtmp_server'];

		}

		static function webrtcStreamQuery($userID, $postID, $broadcaster, $stream_webrtc, $options = null, $transcoding = 0)
		{

			if (!$options) $options = get_option('VWliveStreamingOptions');
			$clientIP = VWliveStreaming::get_ip_address();

			if ($broadcaster)
			{
				$key = md5('vw' . $options['webKey'] . $userID . $postID);

			}
			else
			{
				$key = md5('vw' . $options['webKey']. $postID );
			}

			$streamQuery = $stream_webrtc . '?channel_id=' . $postID . '&userID=' . urlencode($userID) . '&key=' . urlencode($key) . '&ip=' . urlencode($clientIP) . '&transcoding=' . $transcoding ;
			return $streamQuery;

		}


		function videowhisper_external($atts)
		{

			if (!is_user_logged_in()) return "<div class='error'>Only logged in users can broadcast!</div>";

			$options = get_option('VWliveStreamingOptions');

			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

			//username
			$current_user = wp_get_current_user();

			if ($current_user->$userName) $username=sanitize_file_name($current_user->$userName);

			$postID = 0;
			if ($options['postChannels']) //1. channel post
				{

				$postID = get_the_ID();
				if (is_single())
					if (get_post_type( $postID ) ==  $options['custom_post']) $stream = get_the_title($postID);

			}

			$atts = shortcode_atts(
				array('channel' => $stream,
				),
				$atts, 'videowhisper_external');


			if (!$stream) $stream = $atts['channel']; //2. shortcode param

			if ($options['anyChannels']) if (!$stream) $stream = $_GET['n']; //3. GET param

				if ($options['userChannels']) if (!$stream) $stream = $username; //4. username

					$stream = sanitize_file_name($stream);

				if (!$stream) return "<div class='error'>Can't load broadcasting details: Missing channel name!</div>";

				if ($postID>0 && $options['postChannels'])
				{
					$channel = get_post( $postID );
					if ($channel->post_author != $current_user->ID) return "<div class='error'>Only owner can broadcast (#$postID)!</div>";
				}

			$rtmpAddress = VWliveStreaming::rtmp_address($current_user->ID, $postID, true, $stream, $stream);
			$rtmpAddressView = VWliveStreaming::rtmp_address($current_user->ID, $postID, false, $stream, $stream);

			$codeWatch = htmlspecialchars(do_shortcode("[videowhisper_watch channel=\"$stream\"]"));
			$roomLink = VWliveStreaming::roomURL($stream);

			$application = substr(strrchr($rtmpAddress, '/'),1);

			$adrp1 = explode('://', $rtmpAddress);
			$adrp2 = explode('/', $adrp1[1]);
			$adrp3 = explode(':', $adrp2[0]);

			$server = $adrp3[0];
			$port = $adrp3[1]; if (!$port) $port = 1935;

			$htmlCode = <<<HTMLCODE
<h3>Broadcast Video</h3>
<div class="ui segment info w-actionbox color_alternate">
<P>After reviewing your encoder setting fields, retrieve settings you need from strings below.</P>
<p>RTMP Address / URL (full address, contains server, port if different than default 1935, application, parameters):
<div class="ui action input">
  <input type="text" value="$rtmpAddress">
  <button class="ui teal right labeled icon button">
    <i class="copy icon"></i>
    Copy
  </button>
</div>
</p>
<p>Application (contains application name and parameters):<BR><I>$application</I></p>
<p>Stream Name / Key (name of channel):<BR><I>$stream</I></p>
<p>Server:<BR><I>$server</I></p>
<p>Port:<BR><I>$port</I></p>
<p>Stream Address (RTMP Address with Stream Name):<BR><I>$rtmpAddress/$stream</I></p>
</div>
<p>Use specs above to broadcast channel '$stream' using external applications (GoCoder iOS/Android app, OBS Open Broadcaster Software, XSplit, Adobe Flash Media Live Encoder, Wirecast).<br>Keep your secret broadcasting rtmp address safe as anyone having it may broadcast to your channel. As external encoders don't comunicate with site scripts, externally broadcast channel shows as online only if RTMP Session Control is enabled.</p>

<p>Copy and paste strings: For mobile encoders send the strings above in an email or notes sharing app. In GoCoder copy and paste each string and save settings before switching between apps to get next string.</p>
<p>Warning: If advanced session control is enabled you can't connect at same time with web broadcasting interface and external encoder (duplicate named session will be refused by server). Connect with external encoder using details above and participate in chat with Watch interface.</p>

<h3>Playback Video</h3>
<div class="ui segment info w-actionbox color_alternate">
<p>RTMP Address / URL (full address, contains server, port if different than default 1935, application, parameters):<BR><I>$rtmpAddressView</I></p>
<p>Stream Name:<BR><I>$stream</I></p>
<p>Stream Address (RTMP Address with Stream Name, for players that require these settings in 1 string):<BR><I>$rtmpAddressView/$stream</I></p>
</div>
<p>Use specs above to setup playback using 3rd party RTMP players (Strobe, JwPlayer, FlowPlayer), restreaming servers or apps.</p>
<h3>Chat &amp; Video Embed</h3>
<div class="ui segment info w-actionbox color_alternate">
<p><I>$codeWatch</I></p>
</div>
HTMLCODE;

			return   $htmlCode;

		}


		function videowhisper_external_playback($atts)
		{

			if (!is_user_logged_in()) return "<div class='error'>Only logged in users can access info!</div>";

			$options = get_option('VWliveStreamingOptions');

			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

			//username
			$current_user = wp_get_current_user();

			if ($current_user->$userName) $username=sanitize_file_name($current_user->$userName);

			$postID = 0;
			if ($options['postChannels']) //1. channel post
				{

				$postID = get_the_ID();
				if (is_single())
					if (get_post_type( $postID ) ==  $options['custom_post']) $stream = get_the_title($postID);

			}

			$atts = shortcode_atts(
				array('channel' => $stream,
				),
				$atts, 'videowhisper_external_playback');


			if (!$stream) $stream = $atts['channel']; //2. shortcode param

			if ($options['anyChannels']) if (!$stream) $stream = $_GET['n']; //3. GET param

				if ($options['userChannels']) if (!$stream) $stream = $username; //4. username

					$stream = sanitize_file_name($stream);

				if (!$stream) return "<div class='error'>Can't load channel details: Missing channel name!</div>";

				if ($postID>0 && $options['postChannels'])
				{
					$channel = get_post( $postID );
					if ($channel->post_author != $current_user->ID) return "<div class='error'>Only owner can access channel (#$postID)!</div>";
				}

			$rtmpAddress = VWliveStreaming::rtmp_address($current_user->ID, $postID, true, $stream, $stream);
			$rtmpAddressView = VWliveStreaming::rtmp_address($current_user->ID, $postID, false, $stream, $stream);

			$codeWatch = htmlspecialchars(do_shortcode("[videowhisper_watch channel=\"$stream\"]"));
			$roomLink = VWliveStreaming::roomURL($stream);

			$application = substr(strrchr($rtmpAddress, '/'),1);

			$adrp1 = explode('://', $rtmpAddress);
			$adrp2 = explode('/', $adrp1[1]);
			$adrp3 = explode(':', $adrp2[0]);

			$server = $adrp3[0];
			$port = $adrp3[1]; if (!$port) $port = 1935;

			$htmlCode = <<<HTMLCODE
<h3>Playback Video</h3>
<div class="ui segment info w-actionbox color_alternate">
<p>RTMP Address / URL (full address, contains server, port if different than default 1935, application, parameters):<BR><I>$rtmpAddressView</I></p>
<p>Stream Name:<BR><I>$stream</I></p>
<p>Stream Address (RTMP Address with Stream Name, for players that require these settings in 1 string):<BR><I>$rtmpAddressView/$stream</I></p>
</div>
<p>Use specs above to setup playback using 3rd party RTMP players (Strobe, JwPlayer, FlowPlayer), restreaming servers or apps.</p>
<h3>Chat &amp; Video Embed</h3>
<div class="ui segment info w-actionbox color_alternate">
<p><I>$codeWatch</I></p>
</div>
HTMLCODE;

			VWliveStreaming::enqueueUI();

			return   $htmlCode;

		}

		function videowhisper_external_broadcast($atts)
		{

			if (!is_user_logged_in()) return '<div class="error"">' . __('Only logged in users can broadcast!', 'live-streaming') . '</div>';

			$options = get_option('VWliveStreamingOptions');

			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

			//username
			$current_user = wp_get_current_user();

			if ($current_user->$userName) $username=sanitize_file_name($current_user->$userName);

			$postID = 0;
			if ($options['postChannels']) //1. channel post
				{

				$postID = get_the_ID();
				if (is_single())
					if (get_post_type( $postID ) ==  $options['custom_post']) $stream = get_the_title($postID);

			}

			$atts = shortcode_atts(
				array('channel' => $stream,
				),
				$atts, 'videowhisper_external_broadcast');


			if (!$stream) $stream = $atts['channel']; //2. shortcode param

			if ($options['anyChannels']) if (!$stream) $stream = $_GET['n']; //3. GET param

				if ($options['userChannels']) if (!$stream) $stream = $username; //4. username

					$stream = sanitize_file_name($stream);

				if (!$stream) return "<div class='error'>Can't load broadcasting details: Missing channel name!</div>";

				if ($postID>0 && $options['postChannels'])
				{
					$channel = get_post( $postID );
					if ($channel->post_author != $current_user->ID) return "<div class='error'>Only owner can broadcast (#$postID)!</div>";
				}

			$rtmpAddress = VWliveStreaming::rtmp_address($current_user->ID, $postID, true, $stream, $stream);

			$roomLink = VWliveStreaming::roomURL($stream);

			$application = substr(strrchr($rtmpAddress, '/'),1);

			$adrp1 = explode('://', $rtmpAddress);
			$adrp2 = explode('/', $adrp1[1]);
			$adrp3 = explode(':', $adrp2[0]);

			$server = $adrp3[0];
			$port = $adrp3[1]; if (!$port) $port = 1935;

		/// $videoBitrate
		$bitrateCode = '';
				$sessionsVars = self::varLoad($options['uploadsPath']. '/sessionsApp');
						if (is_array($sessionsVars)) 
						{
							if (array_key_exists( 'limitClientRateIn', $sessionsVars) ) 
							{
								$limitClientRateIn = intval($sessionsVars['limitClientRateIn']) * 8 / 1000;
								
								if ($limitClientRateIn) 
								{
										$videoBitrate = $limitClientRateIn - 100;
										
										$bitrateCode .= '
<div class="ui segment">Maximum Video Bitrate<br>
<div class="ui action input">
  <input type="text" class="copyInput" value="'.$videoBitrate.'">
  <button class="ui teal right labeled icon button copyButton">
    <i class="copy icon"></i>
    Copy
  </button>
</div>
<small>
<br>Use this value or lower for video bitrate, depending on resolution. A static background and less motion requires less bitrate than movies, sports, games.
<br>For OBS Settings: Output > Streaming > Video Bitrate. 
<br>Warning: Trying to broadcast higher bitrate than allowed by streaming server will result in disconnects/failures.
</small>
</div>
										';
										
										$bitrateCode .= '
<div class="ui segment">Maximum Audio Bitrate<br>
<div class="ui action input">
  <input type="text" class="copyInput" value="96">
  <button class="ui teal right labeled icon button copyButton">
    <i class="copy icon"></i>
    Copy
  </button>
</div>
<small>
<br>Use this value or lower for audio bitrate. If you want to use higher Audio Bitrate, lower Video Bitrate to compensate for higher audio. 
<br>For OBS Settings: Output > Streaming > Audio Bitrate. 
<br>Warning: Trying to broadcast higher bitrate than allowed by streaming server will result in disconnects/failures.	
</small>									
</div>
';
										
								}
							}
						}
						
			$htmlCode = <<<HTMLCODE
<h3>Broadcast Video</h3>
<div class="ui segment info w-actionbox color_alternate">
<P>After reviewing your encoder setting fields, retrieve settings you need from strings below.</P>

<div class="ui segment">RTMP Address / OBS Stream URL (full streaming address, contains: server, port if different than default 1935, application and control parameters, key)
<div class="ui action input fluid">
  <input type="text" class="copyInput" value="$rtmpAddress">
  <button class="ui teal right labeled icon button copyButton">
    <i class="copy icon"></i>
    Copy
  </button>
</div>
</div>

<div class="ui segment">Stream Name / OBS Stream Key (name of channel)<br>
<div class="ui action input">
  <input type="text" class="copyInput" value="$stream">
  <button class="ui teal right labeled icon button copyButton">
    <i class="copy icon"></i>
    Copy
  </button>
</div>
</div>

$bitrateCode

Some broadcasting applications may require streaming details separated in different settings:
<div class="ui segment">Server<br>
<div class="ui action input">
  <input type="text" class="copyInput" value="$server">
  <button class="ui teal right labeled icon button copyButton">
    <i class="copy icon"></i>
    Copy
  </button>
</div>
</div>

<div class="ui segment">Port<br>
<div class="ui action input">
  <input type="text" class="copyInput" value="$port">
  <button class="ui teal right labeled icon button copyButton">
    <i class="copy icon"></i>
    Copy
  </button>
</div>
</div>

<div class="ui segment">Application (contains application name and control parameters, key)
<div class="ui action input fluid">
  <input type="text" class="copyInput" value="$application">
  <button class="ui teal right labeled icon button copyButton">
    <i class="copy icon"></i>
    Copy
  </button>
</div>
</div>

<div class="ui segment">Stream Address (contains RTMP Address with Stream Name, everything in one setting)
<div class="ui action input fluid">
  <input type="text" class="copyInput" value="$rtmpAddress/$stream">
  <button class="ui teal right labeled icon button copyButton">
    <i class="copy icon"></i>
    Copy
  </button>
</div>
</div>

<p>Use specs above to broadcast channel '<a href="$roomLink">$stream</a>' using external applications (<a href="https://itunes.apple.com/us/app/wowza-gocoder/id640338185?mt=8">GoCoder iOS</a>/<a href="https://play.google.com/store/apps/details?id=com.wowza.gocoder&hl=en">Android app</a>, <a href="https://obsproject.com">OBS Open Broadcaster Software</a>, XSplit, Adobe Flash Media Live Encoder, Wirecast).<br>Keep your secret broadcasting rtmp address safe as anyone having it may broadcast to your channel. As external encoders don't comunicate with site scripts, externally broadcast channel shows as online only if <a href="https://videowhisper.com/?p=RTMP-Session-Control">RTMP Session Control</a> is configured on streaming server.</p>

<p>Copy and paste strings: For mobile encoders send the strings above in an email or notes sharing app. In GoCoder copy and paste each string and save settings before switching between apps to get next string.</p>
<p>Warning: If advanced session control is enabled you can't connect at same time with web broadcasting interface and external encoder (duplicate named session will be refused by server). Connect with external encoder using details above and if you want to participate in chat you can do that from website with Watch interface.</p>

<SCRIPT>
var popupTimer;

function delayPopup(popup) {
    popupTimer = setTimeout(function() { $(popup).popup('hide') }, 4200);
}

jQuery(document).ready(function () {
    jQuery('.copyButton').click(function (){
        clearTimeout(popupTimer);

        var input = jQuery(this).closest('div').find('.copyInput');

        /* Select the text field */
        input.select();

        /* Copy the text inside the text field */
        document.execCommand("copy");

		console.log('Copy');

        $(this)
            .popup({
                title    : 'Successfully copied to clipboard!',
                content  : 'You can now paste this content.',
                on: 'manual',
                exclusive: true
            })
            .popup('show')
        ;

        // Hide popup after 5 seconds
        delayPopup(this);


    });

});
</SCRIPT>
HTMLCODE;

			VWliveStreaming::enqueueUI();

			return   $htmlCode;

		}




		function videowhisper_broadcast($atts)
		{
			$stream = '';

			if (!is_user_logged_in())
				return "<div class='error'>" . __('Broadcasting not allowed: Only logged in users can broadcast!', 'live-streaming') . '</div>'
					. '<BR>'. VWliveStreaming::loginRequiredWarning();

			$options = get_option('VWliveStreamingOptions');

			//username used with application
			$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

			$current_user = wp_get_current_user();

			if ($current_user->$userName) $username=sanitize_file_name($current_user->$userName);

			$postID = 0;
			if ($options['postChannels']) //1. channel post
				{
				$postID = get_the_ID();
				if (is_single())
					if (get_post_type( $postID ) ==  $options['custom_post'] ) $stream = get_the_title($postID);
			}

			$atts = shortcode_atts(
				array('channel' => $stream,
					'flash' => '0',
				),
				$atts, 'videowhisper_broadcast');


			if (!$stream) $stream = $atts['channel']; //2. shortcode param

			if ($options['anyChannels']) if (!$stream) $stream = $_GET['n']; //3. GET param

				if ($options['userChannels']) if (!$stream) $stream = $username; //4. username

					$stream = sanitize_file_name($stream);

				if (!$stream) return "<div class='error'>Can't load broadcasting interface: Missing channel name!</div>";

				//get post ID
				global $wpdb ;
			$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $stream . '\' and post_type=\'' . $options['custom_post'] . '\' LIMIT 0,1' );

			if ($postID>0 && $options['postChannels'])
			{
				$channel = get_post( $postID );
				if ($channel->post_author != $current_user->ID) return "<div class='error'>Only owner can broadcast his channel ($stream #$postID)!</div>";
			}



			if (!$atts['flash'])
			{

				//HLS if iOS/Android detected
				$agent = $_SERVER['HTTP_USER_AGENT'];
				$Android = stripos($agent,"Android");
				$iOS = ( strstr($agent,'iPhone') || strstr($agent,'iPod') || strstr($agent,'iPad'));
				$Safari = (strstr($agent,"Safari") && !strstr($agent,"Chrome"));
				$Firefox = stripos($agent,"Firefox");

				$htmlCode .= "<!--VideoWhisper-Broadcast-Agent:$agent|A:$Android|I:$iOS|S:$Safari|F:$Firefox-->";

				$showHTML5 = 0;

				if ($Android || $iOS) $showHTML5 = 1;
				if ($options['webrtc'] >= 4) $showHTML5 = 1; //preferred

				if ($showHTML5)
				{
					$htmlCode .= do_shortcode( '[videowhisper_webrtc_broadcast channel="' . $stream . '"]');
					return $htmlCode;
				}
			}

			//flash web broadcast

			$swfurl = plugin_dir_url(__FILE__) . "ls/live_broadcast.swf?ssl=1&room=" . urlencode($stream);
			$swfurl .= "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vwls&task=');
			$swfurl .= '&extension='.urlencode('_none_');
			$swfurl .= '&ws_res=' . urlencode( plugin_dir_url(__FILE__) . 'ls/');

			$bgcolor="#333333";

			$htmlCode = <<<HTMLCODE
<div id="videowhisper_container">
<object width="100%" height="100%" type="application/x-shockwave-flash" data="$swfurl">
<param name="movie" value="$swfurl"></param><param bgcolor="$bgcolor"><param name="scale" value="noscale" /> </param><param name="salign" value="lt"></param><param name="allowFullScreen"
value="true"></param><param name="allowscriptaccess" value="always"></param>
</object>
</div>

<br style="clear:both" />

<style type="text/css">
<!--

#videowhisper_container
{
width: 100%;
height: 500px;
min-height: 500px;
border: solid 3px #999;
overflow: auto;
}

-->
</style>
HTMLCODE;

			$htmlCode .=  VWliveStreaming::flash_warn();

			if ($options['webrtc'] >= 2)
			{
				if ($postID) $htmlCode .= '<p><a class="ui button secondary" href="' . add_query_arg(array('webrtc-broadcast'=>''), get_permalink($postID)) . '">' . __('Try HTML5 WebRTC Broadcast', 'live-streaming') . '</a></p>';
			}

			if (!$options['transcoding']) return $htmlCode; //done

			//transcoding interface
			if ($stream)
			{

				//access keys
				if ($current_user)
				{
					$userkeys = $current_user->roles;
					$userkeys[] = $current_user->user_login;
					$userkeys[] = $current_user->ID;
					$userkeys[] = $current_user->user_email;
					$userkeys[] = $current_user->display_name;
				}

				$admin_ajax = admin_url() . 'admin-ajax.php';

				if (VWliveStreaming::inList($userkeys, $options['transcode'])) //transcode feature enabled
					if ($options['transcoding']) if ($options['transcodingManual'])
							$htmlCode .= <<<HTMLCODE
<div id="vwinfo">
Stream Transcoding<BR>
<a href='#' class="button" id="transcoderon">ENABLE</a>
<a href='#' class="button" id="transcoderoff">DISABLE</a>
<div id="videowhisperTranscoder">A stream must be broadcast for transcoder to start. Activate to make stream available for iOS HLS.</div>
<p align="right">(<a href="javascript:void(0)" onClick="vwinfo.style.display='none';">hide</a>)</p>
</div>

<style type="text/css">
<!--

#vwinfo
{
	float: right;
	width: 25%;
	position: absolute;
	bottom: 10px;
	right: 10px;
	text-align:left;
	font-size: 14px;
	padding: 10px;
	margin: 10px;
	background-color: #666;
	border: 1px dotted #AAA;
	z-index: 1;

	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#999', endColorstr='#666'); /* for IE */
	background: -webkit-gradient(linear, left top, left bottom, from(#999), to(#666)); /* for webkit browsers */
	background: -moz-linear-gradient(top,  #999,  #666); /* for firefox 3.6+ */

	box-shadow: 2px 2px 2px #333;


	-moz-border-radius: 9px;
	border-radius: 9px;
}

#vwinfo > a {
	color: #F77;
	text-decoration: none;
}

#vwinfo > .button {
	-moz-box-shadow:inset 0px 1px 0px 0px #f5978e;
	-webkit-box-shadow:inset 0px 1px 0px 0px #f5978e;
	box-shadow:inset 0px 1px 0px 0px #f5978e;
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #db4f48), color-stop(1, #944038) );
	background:-moz-linear-gradient( center top, #db4f48 5%, #944038 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#db4f48', endColorstr='#944038');
	background-color:#db4f48;
	border:1px solid #d02718;
	display:inline-block;
	color:#ffffff;
	font-family:Verdana;
	font-size:12px;
	font-weight:normal;
	font-style:normal;
	text-decoration:none;
	text-align:center;
	text-shadow:1px 1px 0px #810e05;
	padding: 5px;
	margin: 2px;
}
#vwinfo > .button:hover {
	background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #944038), color-stop(1, #db4f48) );
	background:-moz-linear-gradient( center top, #944038 5%, #db4f48 100% );
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#944038', endColorstr='#db4f48');
	background-color:#944038;
}

-->
</style>

<script type="text/javascript">
	var \$j = jQuery.noConflict();
	var loaderTranscoder;
	var transcodingOn = false;


	\$j.ajaxSetup ({
		cache: false
	});
	var ajax_load = "Loading...";

	\$j("#transcoderon").click(function(){
	transcodingOn = true;
	if (loaderTranscoder) if (loaderTranscoder.abort === 'function') loaderTranscoder.abort();
	loaderTranscoder = \$j("#videowhisperTranscoder").html(ajax_load).load("$admin_ajax?action=vwls_trans&task=enable&stream=$stream");
	});

	\$j("#transcoderoff").click(function(){
	transcodingOn = false;
	if (loaderTranscoder) if (loaderTranscoder.abort === 'function') loaderTranscoder.abort();
	loaderTranscoder = \$j("#videowhisperTranscoder").html(ajax_load).load("$admin_ajax?action=vwls_trans&task=close&stream=$stream");
	});
</script>
HTMLCODE;
			}

			return $htmlCode ;
		}



		static function path2url($file, $Protocol='http://')
		{
			$url = $Protocol.$_SERVER['HTTP_HOST'];


			//on godaddy hosting uploads is in different folder like /var/www/clients/ ..
			$upload_dir = wp_upload_dir();
			if (strstr($file, $upload_dir['basedir']))
				return  $upload_dir['baseurl'] . str_replace($upload_dir['basedir'], '', $file);

			if (strstr($file, $_SERVER['DOCUMENT_ROOT']))
				return  $url . str_replace($_SERVER['DOCUMENT_ROOT'], '', $file);


			return $url . $file;
		}


		static function format_time($t,$f=':') // t = seconds, f = separator
			{
			return sprintf("%02d%s%02d%s%02d", floor($t/3600), $f, ($t/60)%60, $f, $t%60);
		}

		static function format_age($t)
		{
			if ($t<180) return __('LIVE', 'live-streaming'); //3 min
			if ($t + 3 > time()) return __('Never', 'live-streaming');
			return sprintf("%d%s%d%s%d%s", floor($t/86400), 'd ', ($t/3600)%24,'h ', ($t/60)%60,'m');
		}


		//! Watcher (viewer) Online Status for App + AJAX chat, not Flash
		static function updateOnline($username, $room, $postID = 0, $type = 2, $current_user = '', $options ='')
		{
			//$type: 1 = flash full, 2 = html5 chat, 3 = flash video, 4 = html5 video, 5 = voyeur flash, 6 = voyeur html5

			if (!$room && !$postID) return; //no room, no update

			$s = $u = $username;
			$r = $room;
			$ztime = time();

			if (!$options) $options = get_option('VWliveStreamingOptions');

			if (!$current_user) $current_user = wp_get_current_user();

			$uid = 0;
			if ($current_user) $uid = $current_user->ID;

			global $wpdb ;
			$table_sessions = $wpdb->prefix . "vw_lwsessions";
			$table_channels = $wpdb->prefix . "vw_lsrooms";

			if (!$postID)
				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_title = \'' . $room . '\' and post_type=\''.$options['custom_post'] . '\' LIMIT 0,1' );


			//create or update session
			//status: 0 current, 1 closed, 2 billed

			$sqlS = "SELECT * FROM `$table_sessions` WHERE session='$s' AND status='0' AND room='$room' AND type='$type' AND rmode='$rmode' LIMIT 1";
			$session = $wpdb->get_row($sqlS);

			if (!$session)
			{

				if ($ztime - $redate > $options['onlineExpiration1']) $rsdate = 0; //broadcaster offline
				else $rsdate = $redate; //broadcaster online: mark room start date

				$clientIP = VWliveStreaming::get_ip_address();

				$sql="INSERT INTO `$table_sessions` ( `session`, `username`, `uid`, `room`, `rid`, `roptions`, `rsdate`, `redate`, `rmode`, `message`, `sdate`, `edate`, `status`, `type`, `ip`) VALUES ('$s', '$u', '$uid', '$r', '$postID', '$roptions', '$rsdate', '$redate', '$rmode', '$m', '$ztime', '$ztime', 0, $type, '$clientIP')";
				$wpdb->query($sql);
				$session = $wpdb->get_row($sqlS);
			}
			else
			{
				$id = $session->id;

				//broadcaster was offline and came online: update room start time (rsdate)
				if ($session->rsdate == 0 && $redate > $session->sdate) $rsdate = $redate;
				else $rsdate = $session->rsdate; //keep unchanged (0 or start time)

				$sql="UPDATE `$table_sessions` set edate='$ztime', rsdate='$rsdate', redate='$redate', roptions = '$roptions' WHERE id='$id' LIMIT 1";
				$wpdb->query($sql);
			}


			//also update view time (based on original session)

			$sqlC = "SELECT * FROM $table_channels WHERE name='" . $session->room . "' LIMIT 0,1";
			$channel = $wpdb->get_row($sqlC);


			//calculate time in ms based on previous request
			$lastTime =  $session->edate * 1000;
			$currentTime = $ztime * 1000;

			//update room time
			$expTime = $options['onlineExpiration0']+30;

			$dS = floor(($currentTime-$lastTime)/1000);
			if ($dS > $expTime || $dS<0) $disconnect = "Web server out of sync ($dS > $expTime)!"; //Updates should be faster than 3 minutes; fraud attempt?

			$channel->wtime += $dS;

			//update
			$sql="UPDATE `$table_channels` set wtime = " . $channel->wtime . " where id = '" . $channel->id. "'";
			$wpdb->query($sql);

			//update post
			if ($postID)
			{
				update_post_meta($postID, 'wtime', $channel->wtime);
			}

			//update user watch time, disconnect if exceeded limit

			if ($current_user) $user = $current_user;
			else $user = get_user_by('login', $u);

			if ($user)
				if (self::updateUserWatchtime($user, $dS, $options))
					$disconnect = urlencode('User watch time limit exceeded!');


				if (!$disconnect)
				{
					//update access time
					if (is_user_logged_in())
					{
						update_post_meta($postID, 'accessedUser', $ztime);
					}
					update_post_meta($postID, 'accessed', $ztime);
				}

			return $disconnect;

		}
		//! AJAX
		static function wp_enqueue_scripts()
		{
			wp_enqueue_script("jquery");
		}


		//! AJAX HTML Chat

		function wp_ajax_vwls_htmlchat()
		{

			$options = get_option('VWliveStreamingOptions');
			//output clean
			ob_clean();

			// Handling the supported tasks:

			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_lwsessions"; //viewers
			$table_chatlog = $wpdb->prefix . "vw_vwls_chatlog";


			$room = sanitize_file_name($_GET['room']);
			$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $room . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );
			if (!$postID) throw new Exception('HTML Chat: Channel not found: ' . $room);

			$post = get_post( $postID );

			//user
			$username = '';
			$user_id = 0;
			$isBroadcaster = 0;

			if (is_user_logged_in())
			{
				$current_user = wp_get_current_user();

				if (isset($current_user) )
				{
					$user_id = $current_user->ID;

					$userName =  $options['userName'];
					if (!$userName) $userName='user_nicename';
					if ($current_user->$userName) $username = urlencode(sanitize_file_name($current_user->$userName));

					$isBroadcaster =  ($user_id == $post->post_author);

					if ($isBroadcaster) $username = $room;

				}
			}else
			{
				if ($_COOKIE['htmlchat_username']) $username = $_COOKIE['htmlchat_username'];
				else
				{
					$username =  'Guest_' . base_convert(time()%36 * rand(0,36*36),10,36);
					setcookie('htmlchat_username', $username);
				}
			}

			$ztime = time();

			switch($_GET['task']){


				//tips
			case 'getBalance':

				$balance = 0;
				if ($user_id) $balance = self::balance($user_id, false, $options);

				$response = array(
					'balance' => $balance
				);

				break;


			case 'sendTip':


				if (!isset($current_user))
					if (!$options['htmlchatVisitorWrite']) throw new Exception('You are not logged in!');

					if ($options['tipCooldown'])
					{
						$lastTip = get_user_meta($current_user->ID, 'vwTipLast', true);
						if ($lastTip + $options['tipCooldown'] > time()) throw new Exception('Already sent tip recently!');
					}

				$label = $message = sanitize_text_field($_POST['label']);
				$amount = intval($_POST['amount']);
				$note = sanitize_text_field($_POST['note']);
				$sound = sanitize_text_field($_POST['sound']);
				$image = sanitize_text_field($_POST['image']);

				$meta = array();
				$meta['sound'] = $sound;
				$meta['image'] = $image;
				$metaS = serialize($meta);

				if (!$message) $error = 'No message!';

				if ($error)
				{
					$response = array(
						'status'    => 0,
						'insertID'    => 'error',
						'success' => 0,
						'error' => $error
					);

				}
				else
				{
					$message = preg_replace('/([^\s]{12})(?=[^\s])/', '$1'.'<wbr>', $message); //break long words <wbr>:Word Break Opportunity
					$message = "<I>$message</I>"; //mark system message for tip

					$sql="INSERT INTO `$table_chatlog` ( `username`, `room`, `message`, `mdate`, `type`, `meta`, `user_id`) VALUES ('$username', '$room', '$message', $ztime, '2', '$metaS', '$user_id')";
					$wpdb->query($sql);

					$response = array(
						'status'    => 1,
						'insertID'    => $wpdb->insert_id
					);

					//also update chat log file
					if ($message)
					{

						$message = strip_tags($message,'<p><a><img><font><b><i><u>');

						$message = date("F j, Y, g:i a", $ztime) . " <b>$username</b>: $message";

						//generate same private room folder for both users
						if ($private)
						{
							if ($private > $session) $proom=$session ."_". $private;
							else $proom=$private ."_". $session;
						}

						$dir=$options['uploadsPath'];
						if (!file_exists($dir)) mkdir($dir);

						$dir.="/$room";
						if (!file_exists($dir)) mkdir($dir);

						if ($proom)
						{
							$dir.="/$proom";
							if (!file_exists($dir)) mkdir($dir);
						}

						$day=date("y-M-j",time());

						$dfile = fopen($dir."/Log$day.html","a");
						fputs($dfile,$message."<BR>");
						fclose($dfile);
					}

					//tip

					$balance = self::balance($current_user->ID);

					$response['success'] = true;
					$response['balancePrevious'] = $balance;
					$response['postID'] = $postID;
					$response['userID'] = $current_user->ID;
					$response['amount'] = $amount;

					if ($amount > $balance)
					{
						$response['success'] = false;
						$response['error'] = 'Tip amount greater than balance!';
						$response['balance'] = $balance;
					}
					else
					{

						$ztime = time();

						$tipInfo = "$label: $note";

						//client cost
						$paid = number_format($amount, 2, '.', '');
						VWliveStreaming::transaction('channel_tip', $current_user->ID, - $paid, 'Tip for <a href="' . VWliveStreaming::roomURL($room) . '">' . $room.'</a>. (' .$tipInfo.')' , $ztime);
						$response['paid'] = $paid;

						//performer earning
						$received = number_format($amount * $options['tipRatio'], 2, '.', '');
						VWliveStreaming::transaction('channel_tip_earning', $post->post_author, $received , 'Tip from ' . $username .' ('.$tipInfo.')', $ztime);

						//save last tip time
						update_user_meta($current_user->ID, 'vwTipLast', time());

						$response['broadcaster'] = $post->post_author;
						$response['received'] = $received;

						//update balance and report
						$response['balance'] = self::balance($current_user->ID);

					}
				}


				break;


				//chat
			case 'checkLogged':
				$response = array('logged' => false);

				if (isset($current_user) )
				{
					$response['logged'] = true;

					$response['loggedAs'] = array(
						'name'        => $username,
						'avatar'    => get_avatar_url($current_user->ID),
						'userID' => $current_user->ID
					);

				}
				elseif ($options['htmlchatVisitorWrite'])
				{
					$response['logged'] = true;

					$response['loggedAs'] = array(
						'name'        => $username,
					);
				}

				$disconnected = self::updateOnline($username, $room, $postID, 2, $current_user, $options);

				if ($disconnected)
				{
					$response['disconnect'] = $disconnected;
					$response['logged'] = false;
				}

				break;

			case 'submitChat':
				//$response = Chat::submitChat();

				if (!isset($current_user))
					if (!$options['htmlchatVisitorWrite']) throw new Exception('You are not logged in!');
					else
					{
						//visitor
					}

				$message = sanitize_text_field($_POST['chatText']);
				$message = preg_replace('/([^\s]{12})(?=[^\s])/', '$1'.'<wbr>', $message); //break long words <wbr>:Word Break Opportunity

				$sql="INSERT INTO `$table_chatlog` ( `username`, `room`, `message`, `mdate`, `type`, `user_id`) VALUES ('$username', '$room', '$message', $ztime, '2', '$user_id')";
				$wpdb->query($sql);

				$response = array(
					'status'    => 1,
					'insertID'    => $wpdb->insert_id
				);

				//also update chat log file
				if ($message)
				{

					$message = strip_tags($message,'<p><a><img><font><b><i><u>');

					$message = date("F j, Y, g:i a", $ztime) . " <b>$username</b>: $message";

					//generate same private room folder for both users
					if ($private)
					{
						if ($private > $session) $proom=$session ."_". $private;
						else $proom=$private ."_". $session;
					}

					$dir=$options['uploadsPath'];
					if (!file_exists($dir)) mkdir($dir);

					$dir.="/$room";
					if (!file_exists($dir)) mkdir($dir);

					if ($proom)
					{
						$dir.="/$proom";
						if (!file_exists($dir)) mkdir($dir);
					}

					$day=date("y-M-j",time());

					$dfile = fopen($dir."/Log$day.html","a");
					fputs($dfile,$message."<BR>");
					fclose($dfile);
				}

				break;


			case 'getUsers':

				//old session cleanup

				//close sessions
				$closeTime = time() - $options['onlineExpiration0']; // > client statusInterval
				$sql="UPDATE `$table_sessions` SET status = 1 WHERE status = 0 AND edate < $closeTime";
				$wpdb->query($sql);

				$users = array();

				//type 5,6 voyeur: do not show
				$sql = "SELECT * FROM `$table_sessions` where room='$room' and status='0' AND type < 5 ORDER by sdate ASC";
				$userRows = $wpdb->get_results($sql);

				if ($wpdb->num_rows>0)
					foreach ($userRows as $userRow)
					{
						$user = [];

						$user_id = $userRow->uid;
						$user['name'] = $userRow->session;
						$user['id'] = $user_id;

						//avatar
						if (!$user_id)
						{
							$wpUser = get_user_by('login', $userRow->session);
							$user_id = $wpUser->ID;
						}

						if ($user_id)
							if ($options['userPicture'] == 'avatar' || ($options['userPicture'] == 'avatar_broadcaster' && $isBroadcaster) )
								$user['avatar'] = get_avatar_url($user_id );

							$users [] = $user;
					}
				$response = array(
					'users' => $users,
					'total' => count($userRows)
				);
				break;

			case 'getChats':
				$disconnect =  self::updateOnline($username, $room, $postID, 2, $current_user, $options);

				if (!$disconnect)
				{
					//clean old chat logs
					$closeTime = time() - 900; //only keep for 15min
					$sql="DELETE FROM `$table_chatlog` WHERE mdate < $closeTime";
					$wpdb->query($sql);

					//retrieve only messages since user came online
					$sdate = 0;
					if ($session) $sdate = $session->sdate;


					$chats = array();

					$lastID = (int) $_GET['lastID'];

					$sql = "SELECT * FROM `$table_chatlog` WHERE room='$room' AND id > $lastID AND mdate > $sdate ORDER BY mdate DESC LIMIT 0,20";
					$sql = "SELECT * FROM ($sql) items ORDER BY mdate ASC";

					$chatRows = $wpdb->get_results($sql);


					if ($wpdb->num_rows>0) foreach ($chatRows as $chatRow)
						{
							$chat = [];

							if ($chatRow->meta)
							{
								$meta = unserialize($chatRow->meta);

								if ($meta['sound']) $chat['sound'] = $meta['sound'];
								if ($meta['image']) $chat['image'] = $meta['image'];

							}

							$chat['id'] = $chatRow->id;
							$chat['author'] = $chatRow->username;
							$chat['text'] = $chatRow->message;

							$chat['time'] =  array(
								'hours'        => gmdate('H',$chatRow->mdate),
								'minutes'    => gmdate('i',$chatRow->mdate)
							);

							$uid  = $chatRow->user_id;
							if (!$uid)
							{
								$wpUser = get_user_by($userName, $userRow->session);
								if (!$wpUser) $wpUser = get_user_by('login', $chatRow->username);
								$uid = $wpUser->ID;
							}

							$chat['avatar'] = get_avatar_url($uid);

							$chats[] = $chat;
						}

					$response = array('chats' => $chats);
				}
				else
				{
					$response = array('chats' => array(), 'disconnect' => $disconnect);

				}

				break;

			default:
				$response = array('error' => 'HTML Chat: Task not defined!');
				;
			}

			echo json_encode($response);

			die();
		}


		//! channels list ajax handler

		function vwls_channels() //list channels
			{
			//ajax called

			//channel meta:
			//edate s
			//btime s
			//wtime s
			//viewers n
			//maxViewers n
			//maxDate s
			//hasSnapshot 1

			$options = get_option('VWliveStreamingOptions');

			//widget id
			$id = sanitize_file_name($_GET['id']);

			//pagination
			$perPage = (int) $_GET['pp'];
			if (!$perPage) $perPage = $options['perPage'];

			$page = (int) $_GET['p'];
			$offset = $page * $perPage;

			$perRow = (int) $_GET['pr'];

			//admin side
			$ban = (int) $_GET['ban'];

			//
			$category = (int) $_GET['cat'];

			//order
			$order_by = sanitize_file_name($_GET['ob']);
			if (!$order_by) $order_by = $options['order_by'];
			if (!$order_by) $order_by = 'edate';

			//options
			$selectCategory = (int) $_GET['sc'];
			$selectOrder = (int) $_GET['so'];
			$selectPage = (int) $_GET['sp'];

			$selectName = (int) $_GET['sn'];
			$selectTags = (int) $_GET['sg'];

			//tags,name search
			$tags = sanitize_text_field($_GET['tags']);
			$name = sanitize_file_name($_GET['name']);
			if ($name == 'undefined') $name = '';
			if ($tags == 'undefined') $tags = '';

			//output clean
			ob_clean();

			//thumbs dir
			$dir = $options['uploadsPath']. "/_thumbs";

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwls_channels&pp=' . $perPage .  '&pr=' .$perRow. '&sc=' . $selectCategory . '&sn=' . $selectName .  '&sg=' . $selectTags . '&so=' . $selectOrder . '&sp=' . $selectPage .  '&id=' . $id . '&tags=' . urlencode($tags) . '&name=' . urlencode($name);
			if ($ban) $ajaxurl .= '&ban=' . $ban; //admin side

			if ($options['postChannels']) //channel posts enabled
				{

				//! header option controls
				$ajaxurlP = $ajaxurl . '&p='.$page;
				$ajaxurlPC = $ajaxurl . '&cat=' . $category ;
				$ajaxurlPO = $ajaxurl . '&ob='. $order_by;
				$ajaxurlCO = $ajaxurl . '&cat=' . $category . '&ob='.$order_by ;

				$htmlCode .= '<div class="ui ' . $options['interfaceClass'] .' small equal width form" style="z-index: 20;"><div class="inline fields">';

				if ($selectCategory)
				{
					$htmlCode .= '<div class="field">' . wp_dropdown_categories('echo=0&name=category' . $id . '&hide_empty=0&class=ui+dropdown&hierarchical=1&show_option_all=' . __('All', 'live-streaming') . '&selected=' . $category).'</div>';
					$htmlCode .= '<script>var category' . $id . ' = document.getElementById("category' . $id . '"); 			category' . $id . '.onchange = function(){aurl' . $id . '=\'' . $ajaxurlPO.'&cat=\'+ this.value; loadChannels' . $id . '(\'<div class="ui active inline text large loader">' . __('Loading Category', 'live-streaming') . '...</div>\')}
			</script>';
				}

				if ($selectOrder)
				{
					$htmlCode .= ' <div class="field"><select class="ui dropdown" id="order_by' . $id . '" name="order_by' . $id . '" onchange="aurl' . $id . '=\'' . $ajaxurlPC.'&ob=\'+ this.value; loadChannels' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>' . __('Ordering channels', 'live-streaming') . '...</div>\')">';
					$htmlCode .= '<option value="">' . __('Order By', 'live-streaming') . ':</option>';

					$htmlCode .= '<option value="post_date"' . ($order_by == 'post_date'?' selected':'') . '>' . __('Creation Date', 'live-streaming') . '</option>';

					$htmlCode .= '<option value="edate"' . ($order_by == 'edate'?' selected':'') . '>' . __('Broadcast Recently', 'live-streaming') . '</option>';

					$htmlCode .= '<option value="viewers"' . ($order_by == 'viewers'?' selected':'') . '>' . __('Current Viewers', 'live-streaming') . '</option>';

					$htmlCode .= '<option value="maxViewers"' . ($order_by == 'maxViewers'?' selected':'') . '>' . __('Maximum Viewers', 'live-streaming') . '</option>';


					if ($options['rateStarReview'])
					{
						$htmlCode .= '<option value="rateStarReview_rating"' . ($order_by == 'rateStarReview_rating'?' selected':'') . '>' . __('Rating', 'live-streaming') . '</option>';
						$htmlCode .= '<option value="rateStarReview_ratingNumber"' . ($order_by == 'rateStarReview_ratingNumber'?' selected':'') . '>' . __('Most Rated', 'live-streaming') . '</option>';
						$htmlCode .= '<option value="rateStarReview_ratingPoints"' . ($order_by == 'rateStarReview_ratingPoints'?' selected':'') . '>' . __('Rate Popularity', 'live-streaming') . '</option>';

					}

					$htmlCode .= '<option value="rand"' . ($order_by == 'rand'?' selected':'') . '>' . __('Random', 'live-streaming') . '</option>';

					$htmlCode .= '</select></div>';

				}

				if ($selectTags || $selectName)
				{

					$htmlCode .= '<div class="field"></div>'; //separator
					$htmlCode .= '<div class="field"></div>'; //separator

					if ($selectTags)
					{
						$htmlCode .= '<div class="field" data-tooltip="Tags, Comma Separated"><div class="ui left icon input"><i class="tags icon"></i><INPUT class="videowhisperInput" type="text" size="12" name="tags" id="tags" placeholder="' . __('Tags', 'live-streaming')  . '" value="' .htmlspecialchars($tags). '">
					</div></div>';
					}

					if ($selectName)
					{
						$htmlCode .= '<div class="field"><div class="ui left corner labeled input"><INPUT class="videowhisperInput" type="text" size="12" name="name" id="name" placeholder="' . __('Name', 'live-streaming')  . '" value="' .htmlspecialchars($name). '">
  <div class="ui left corner label">
    <i class="asterisk icon"></i>
  </div>
					</div></div>';
					}

					//search button
					$htmlCode .= '<div class="field" data-tooltip="Search by Tags and/or Name"><button class="ui fluid icon button"  type="submit" name="submit" id="submitSearch" value="' . __('Search', 'live-streaming') . '" onclick="aurl' . $id . '=\'' . $ajaxurlCO .'&tags=\' + document.getElementById(\'tags\').value +\'&name=\' + document.getElementById(\'name\').value; loadChannels' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>Searching Channels...</div>\')"><i class="search icon"></i></button></div>';

				}

				$htmlCode .= '</div></div>';


				//! query args
				$args=array(
					'post_type' => 'channel',
					'post_status' => 'publish',
					'posts_per_page' => $perPage,
					'offset'           => $offset,
					'order'            => 'DESC',
					'meta_query' => array(
						array( 'key' => 'hasSnapshot', 'value' => '1'),
					)
				);

				switch ($order_by)
				{
				case 'post_date':
					$args['orderby'] = 'post_date';
					break;

				case 'rand':
					$args['orderby'] = 'rand';
					break;

				default:
					$args['orderby'] = 'meta_value_num';
					$args['meta_key'] = $order_by;
					break;
				}

				if ($category)  $args['category'] = $category;

				if ($tags)
				{
					$tagList = explode(',', $tags);
					foreach ($tagList as $key=>$value) $tagList[$key] = trim($tagList[$key] );

					$args['tax_query'] = array(
						array(
							'taxonomy'  => 'post_tag',
							'field'     => 'slug',
							'operator' => 'AND',
							'terms'     => $tagList
						)
					);
				}

				if ($name)
				{
					$args['s'] = $name;
				}


				$postslist = get_posts( $args );

				//! list channels
				if (count($postslist)>0)
				{
					//echo '<div class="ui grid">';

					$k = 0;
					foreach ( $postslist as $item )
					{
						if ($perRow) if ($k) if ($k % $perRow == 0) $htmlCode .= '<br>';

								$edate =  get_post_meta($item->ID, 'edate', true);
							$age = VWliveStreaming::format_age(time() -  $edate);
						$name = sanitize_file_name($item->post_title);

						if ($ban) $banLink = '<a class = "button" href="admin.php?page=live-streaming-live&ban=' . urlencode( $name ) . '">Ban This Channel</a><br>';

						$htmlCode .= '<div class="videowhisperChannel">';
						$htmlCode .= '<div class="videowhisperTitle">' . $name  . '</div>';
						$htmlCode .= '<div class="videowhisperTime">' . $banLink . $age . '</div>';

						$ratingCode = '';
						if ($options['rateStarReview'])
						{
							$rating = get_post_meta($item->ID, 'rateStarReview_rating', true);
							$max = 5;
							if ($rating > 0) $ratingCode = '<div class="ui star rating readonly" data-rating="' . round($rating * $max) . '" data-max-rating="' . $max . '"></div>'; // . number_format($rating * $max,1)  . ' / ' . $max
							$htmlCode .= '<div class="videowhisperChannelRating">' . $ratingCode . '</div>';
						}


						$thumbFilename = "$dir/" . $name . ".jpg";
						$url = VWliveStreaming::roomURL($name);

						$noCache = '';
						if ($age=='LIVE') $noCache='?'.((time()/10)%100);

						$showImage=get_post_meta( $item->ID, 'showImage', true );

						if (!file_exists($thumbFilename) || $showImage =='all') //show thumb instead
							{
							$attach_id = get_post_thumbnail_id($item->ID );
							if ($attach_id) $thumbFilename = get_attached_file($attach_id);
						}

						if (file_exists($thumbFilename) && !strstr($thumbFilename, '/.jpg')) $htmlCode .= '<a href="' . $url . '"><IMG src="' . VWliveStreaming::path2url($thumbFilename) . $noCache .'" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px"></a>';
						else $htmlCode .= '<a href="' . $url . '"><IMG SRC="' . plugin_dir_url(__FILE__). 'screenshot-3.jpg" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px"></a>';


						$htmlCode .= "</div>";

					}

					//echo '</div>';
				}
				else $htmlCode .= __('No channels match current selection. Channels get listed after being broadcast or configured as events, with snapshot/picture.', 'live-streaming');

				//! pagination
				if ($selectPage)
				{
					$htmlCode .= '<div class="ui ' . $options['interfaceClass'] .' form"><div class="inline fields">';
					if ($page>0) $htmlCode .= ' <a class="ui labeled icon button" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlCO.'&p='.($page-1). '\'; loadChannels' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>' . __('Loading previous page', 'live-streaming') . '...</div>\');"><i class="left arrow icon"></i> ' . __('Previous', 'live-streaming') . '</a> ';

					if (count($postslist) == $perPage) $htmlCode .= ' <a class="ui right labeled icon button" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlCO.'&p='.($page+1). '\'; loadChannels' . $id . '(\'<div class=\\\'ui active inline text large loader\\\'>' . __('Loading next page', 'live-streaming') . '...</div>\');"> ' . __('Next', 'live-streaming') . ' <i class="right arrow icon"></i></a> ';

					$htmlCode .= '</div></div>';

				}


			}
			else // channel post disabled - check db --
				{
				global $wpdb;
				$table_channels = $wpdb->prefix . "vw_lsrooms";

				$items =  $wpdb->get_results("SELECT * FROM `$table_channels` WHERE status=1 ORDER BY edate DESC LIMIT $offset, ". $perPage);
				if ($items) foreach ($items as $item)
					{
						$age = VWliveStreaming::format_age(time() -  $item->edate);

						if ($ban) $banLink = '<a class = "button" href="admin.php?page=live-streaming-live&ban=' . urlencode( $item->name ) . '">Ban This Channel</a><br>';

						$htmlCode .= '<div class="videowhisperChannel">';
						$htmlCode .= '<div class="videowhisperTitle">' . $item->name  . '</div>';
						$htmlCode .= '<div class="videowhisperTime">' . $banLink . $age . '</div>';

						$thumbFilename = "$dir/" . $item->name . ".jpg";

						$url = VWliveStreaming::roomURL($item->name);

						$noCache = '';
						if ($age==__('LIVE', 'live-streaming')) $noCache='?'.((time()/10)%100);

						if (file_exists($thumbFilename)) $htmlCode .= '<a href="' . $url . '"><IMG src="' . VWliveStreaming::path2url($thumbFilename) . $noCache .'" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px"></a>';
						else $htmlCode .= '<a href="' . $url . '"><IMG SRC="' . plugin_dir_url(__FILE__). 'screenshot-3.jpg" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px"></a>';
						$htmlCode .= "</div>";
					}

				//pagination
				if ($selectPage)
				{
					$htmlCode .= "<BR>";
					if ($page>0) $htmlCode .= ' <a class="ui labeled icon button" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlCO.'&p='.($page-1). '\'; loadChannels' . $id . '(\'' . __('Loading previous page', 'live-streaming') . '...\');">><i class="left arrow icon"></i> ' . __('Previous', 'live-streaming') . '</a> ';

					if (count($items) == $perPage) $htmlCode .= ' <a class="ui right labeled icon button" href="JavaScript: void()" onclick="aurl' . $id . '=\'' . $ajaxurlCO.'&p='.($page+1). '\'; loadChannels' . $id . '(\'' . __('Loading next page', 'live-streaming') . '...\');">' . __('Next', 'live-streaming') . '  <i class="right arrow icon"></i></a> ';
				}
			}


			echo $htmlCode;

			die;
		}

		//! broadcast ajax handler
		function vwls_broadcast() //dedicated broadcasting page
			{
			ob_clean();
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>VideoWhisper Live Broadcast</title>
</head>
<body bgcolor="<?php echo $bgcolor?>">
<style type="text/css">
<!--
BODY
{
	padding-right: 6px;
	margin: 0px;
	background: #333;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 12px;
	color: #EEE;
}
-->
</style>
<?php
			include(plugin_dir_path( __FILE__ ) . "ls/flash_detect.php");

			echo do_shortcode('[videowhisper_broadcast]');

			die;
		}

		static function fixPath($p) {

			//adds ending slash if missing

			//    $p=str_replace('\\','/',trim($p));
			return (substr($p,-1)!='/') ? $p.='/' : $p;
		}


		static function varSave($path, $var)
		{
			file_put_contents($path, serialize($var));
		}

		static function varLoad($path)
		{
			if (!file_exists($path)) return false;

			return unserialize(file_get_contents($path));
		}

		static function updatePlaylist($stream, $active = true)
		{
			//updates playlist for channel $stream in global playlist
			if (!$stream) return;

			$options = get_option('VWliveStreamingOptions');

			$uploadsPath = $options['uploadsPath'];
			if (!file_exists($uploadsPath)) mkdir($uploadsPath);
			$playlistPathGlobal = $uploadsPath . '/playlist_global.txt';
			if (!file_exists($playlistPathGlobal)) VWliveStreaming::varSave($playlistPathGlobal, array());

			$upath = $uploadsPath . "/$stream/";
			if (!file_exists($upath)) mkdir($upath);
			$playlistPath = $upath . 'playlist.txt';
			if (!file_exists($playlistPath)) VWliveStreaming::varSave($playlistPath, array());

			$playlistGlobal = VWliveStreaming::varLoad($playlistPathGlobal);
			$playlist = VWliveStreaming::varLoad($playlistPath);

			if ($active) $playlistGlobal[$stream] = $playlist;
			else unset($playlistGlobal[$stream]);

			VWliveStreaming::varSave($playlistPathGlobal, $playlistGlobal);

			VWliveStreaming::updatePlaylistSMIL();
		}

		function updatePlaylistSMIL()
		{
			$options = get_option('VWliveStreamingOptions');

			//! update Playlist SMIL
			$streamsPath = VWliveStreaming::fixPath($options['streamsPath']);
			$smilPath = $streamsPath . 'playlist.smil';

			$smilCode .= <<<HTMLCODE
<smil>
    <head>
    </head>
    <body>

HTMLCODE;

			if ($options['playlists'])
			{

				$uploadsPath = $options['uploadsPath'];
				if (!file_exists($uploadsPath)) mkdir($uploadsPath);
				$playlistPathGlobal = $uploadsPath . '/playlist_global.txt';
				if (!file_exists($playlistPathGlobal)) VWliveStreaming::varSave($playlistPathGlobal, array());
				$playlistGlobal = VWliveStreaming::varLoad($playlistPathGlobal);


				$streams = array_keys($playlistGlobal);
				foreach ($streams as $stream)
					$smilCode .= '<stream name="' . $stream . '"></stream>
				';

				foreach ($streams as $stream)
					foreach ($playlistGlobal[$stream] as $item)
					{
						$smilCode .= '
        <playlist name="' . $stream . $item['Id'] . '" playOnStream="' . $stream . '" repeat="'. ($item['Repeat']?'true':'false') .'" scheduled="' . $item['Scheduled']. '">';

						if ($item['Videos']) if (is_array($item['Videos'])) foreach ($item['Videos'] as $video)
									$smilCode .= '
		<video src="'. $video['Video'] . '" start="' . $video['Start'] . '" length="' . $video['Length'] . '"/>';

								$smilCode .= '
		</playlist>';
					}
			}
			$smilCode .= <<<HTMLCODE

    </body>
</smil>
HTMLCODE;

			file_put_contents($smilPath, $smilCode);
		}


		function path2stream($path, $withExtension=true, $withPrefix=true)
		{
			$options = get_option( 'VWliveStreamingOptions' );

			$stream = substr($path, strlen($options['streamsPath']));
			if ($stream[0]=='/') $stream = substr($stream, 1);

			if ($withPrefix)
			{
				$ext = pathinfo($stream, PATHINFO_EXTENSION);
				$prefix = $ext . ':';
			}else $prefix = '';

			if (!file_exists($options['streamsPath'] . '/' . $stream)) return '';
			elseif ($withExtension) return $prefix.$stream;
			else return $prefix.pathinfo($stream, PATHINFO_FILENAME);
		}

		//! Playlist AJAX handler

		function vwls_playlist()
		{
			ob_clean();

			$postID = (int) $_GET['channel'];

			if (!$postID)
			{
				echo "No channel ID provided!";
				die;
			}

			$channel = get_post( $postID );
			if (!$channel)
			{
				echo "Channel not found!";
				die;
			}

			$current_user = wp_get_current_user();

			if ($channel->post_author != $current_user->ID)
			{
				echo "Access not permitted (different channel owner)!";
				die;
			}

			$stream = sanitize_file_name($channel->post_title);

			$options = get_option('VWliveStreamingOptions');

			$uploadsPath = $options['uploadsPath'];
			if (!file_exists($uploadsPath)) mkdir($uploadsPath);

			$upath = $uploadsPath . "/$stream/";
			if (!file_exists($upath)) mkdir($upath);

			$playlistPath = $upath . 'playlist.txt';

			if (!file_exists($playlistPath)) VWliveStreaming::varSave($playlistPath, array());

			switch ($_GET['task'])
			{
			case 'list':
				$rows = VWliveStreaming::varLoad($playlistPath);



				//sort rows by order
				if (count($rows))
				{
					//sort
					function cmp_by_order($a, $b) {

						if ($a['Order'] == $b['Order']) return 0;
						return ($a['Order'] < $b['Order']) ? -1 : 1;
					}

					usort($rows,  'cmp_by_order'); //sort

					//update Ids to match keys (order)
					$updated = 0;
					foreach ($rows as $key => $value)
						if ($rows[$key]['Id'] != $key)
						{
							$rows[$key]['Id'] = $key;
							$updated = 1;
						}
					if ($updated) VWliveStreaming::varSave($playlistPath, $rows);

				}


				//Return result to jTable
				$jTableResult = array();
				$jTableResult['Result'] = "OK";
				$jTableResult['Records'] = $rows;
				print json_encode($jTableResult);

				break;

			case 'videolist':
				$ItemId = (int) $_GET['item'];
				$jTableResult = array();

				$playlist = VWliveStreaming::varLoad($playlistPath);

				if ($schedule = $playlist[$ItemId])
				{
					if (!$schedule['Videos']) $schedule['Videos'] = array();

					//sort videos



					//sort rows by order
					if (count($schedule['Videos']))
					{

						//sort
						function cmp_by_order($a, $b) {

							if ($a['Order'] == $b['Order']) return 0;
							return ($a['Order'] < $b['Order']) ? -1 : 1;
						}

						usort($schedule['Videos'],  'cmp_by_order'); //sort

						//update Ids to match keys (order)
						$updated = 0;
						foreach ($schedule['Videos'] as $key => $value)
							if ($schedule['Videos'][$key]['Id'] != $key)
							{
								$schedule['Videos'][$key]['Id'] = $key;
								$updated = 1;
							}

						$playlist[$ItemId] = $schedule;
						if ($updated) VWliveStreaming::varSave($playlistPath, $playlist);

					}

					$jTableResult['Records'] = $schedule['Videos'];
					$jTableResult['Result'] = "OK";
				}
				else
				{
					$jTableResult['Result'] = "ERROR";
					$jTableResult['Message'] = "Schedule $ItemId not found!";
				}

				print json_encode($jTableResult);
				break;

			case 'videoupdate':
				//delete then add new

				$playlist = VWliveStreaming::varLoad($playlistPath);
				$ItemId = (int) $_POST['ItemId'];
				$Id = (int) $_POST['Id'];

				$jTableResult = array();
				if ($playlist[$ItemId])
				{

					//find and remove record with that Id
					foreach ($playlist[$ItemId]['Videos'] as $key => $value)
						if ($value['Id'] == $Id)
						{
							unset($playlist[$ItemId]['Videos'][$key]);
							break;
						}

					VWliveStreaming::varSave($playlistPath,$playlist);
				}

			case 'videoadd':
				$playlist = VWliveStreaming::varLoad($playlistPath);
				$ItemId = (int) $_POST['ItemId'];

				$jTableResult = array();
				if ($schedule = $playlist[$ItemId])
				{
					if (!$schedule['Videos']) $schedule['Videos'] = array();

					$maxOrder = 0; $maxId = 0;
					foreach ($schedule['Videos'] as $item)
					{
						if ($item['Order'] > $maxOrder) $maxOrder = $item['Order'];
						if ($item['Id'] > $maxId) $maxId = $item['Id'];
					}

					$item = array();
					$item['Video'] = sanitize_text_field($_POST['Video']);
					$item['Id'] = (int) $_POST['Id'];
					$item['Order'] = (int) $_POST['Order'];
					$item['Start'] = (int) $_POST['Start'];
					$item['Length'] = (int) $_POST['Length'];

					if (!$item['Order']) $item['Order'] = $maxOrder + 1;
					if (!$item['Id']) $item['Id'] = $maxId + 1;

					$playlist[$ItemId]['Videos'][] = $item;

					VWliveStreaming::varSave($playlistPath,$playlist);

					$jTableResult['Result'] = "OK";
					$jTableResult['Record'] = $item;
				}
				else
				{
					$jTableResult['Result'] = "ERROR";
					$jTableResult['Message'] = "Schedule $ItemId not found!";
				}

				//Return result to jTable
				print json_encode($jTableResult);

				break;

			case 'videoremove':
				$playlist = VWliveStreaming::varLoad($playlistPath);
				$ItemId = (int) $_GET['item'];
				$Id = (int) $_POST['Id'];

				$jTableResult = array();
				if ($schedule = $playlist[$ItemId])
				{

					//find and remove record with that Id
					foreach ($playlist[$ItemId]['Videos'] as $key => $value)
						if ($value['Id'] == $Id)
						{
							unset($playlist[$ItemId]['Videos'][$key]);
							break;
						}

					VWliveStreaming::varSave($playlistPath,$playlist);

					$jTableResult['Result'] = "OK";
					$jTableResult['Remaining'] = $playlist[$ItemId]['Videos'];
				}
				else
				{
					$jTableResult['Result'] = "ERROR";
					$jTableResult['Message'] = "Schedule $ItemId not found!";
				}

				//Return result to jTable
				print json_encode($jTableResult);

				break;

			case 'source':

				//retrieve videos owned by user (from all channels)

				//query
				$args=array(
					'post_type' =>  $options['custom_post_video'],
					'author'        =>  $current_user->ID,
					'orderby'       =>  'post_date',
					'order'            => 'DESC',
				);

				$postslist = get_posts( $args );
				$rows = array();

				if (count($postslist)>0)
				{
					foreach ( $postslist as $item )
					{
						$row = array();
						$row['DisplayText'] = $item->post_title;

						$video_id = $item->ID;

						//retrieve video stream
						$streamPath = '';
						$videoPath = get_post_meta($video_id, 'video-source-file', true);
						$ext = pathinfo($videoPath, PATHINFO_EXTENSION);

						//use conversion if available
						$videoAdaptive = get_post_meta($video_id, 'video-adaptive', true);
						if ($videoAdaptive) $videoAlts = $videoAdaptive;
						else $videoAlts = array();

						foreach (array('high', 'mobile') as $frm)
							if ($alt = $videoAlts[$frm])
								if (file_exists($alt['file']))
								{
									$ext = pathinfo($alt['file'], PATHINFO_EXTENSION);
									$streamPath = VWliveStreaming::path2stream($alt['file']);
									break;
								};

						//user original
						if (!$streamPath)
							if (in_array($ext, array('flv','mp4','m4v')))
							{
								//use source if compatible
								$streamPath = VWliveStreaming::path2stream($videoPath);
							}

						$row['Value'] = $streamPath;
						$rows[] = $row;
					}
				}
				//Return result to jTable
				$jTableResult = array();
				$jTableResult['Result'] = "OK";
				$jTableResult['Options'] = $rows;
				print json_encode($jTableResult);

				break;

			case 'update':
				//delete then create new
				$Id = (int) $_POST['Id'];

				$playlist = VWliveStreaming::varLoad($playlistPath);
				if (!is_array($playlist)) $playlist = array();

				foreach ($playlist as $key => $value)
					if ($value['Id'] == $Id)
					{
						unset($playlist[$key]);
						break;
					}

				VWliveStreaming::varSave($playlistPath,$playlist);

			case 'create':

				$playlist = VWliveStreaming::varLoad($playlistPath);
				if (!is_array($playlist)) $playlist = array();

				$maxOrder = 0; $maxId = 0;
				foreach ($playlist as $item)
				{
					if ($item['Order'] > $maxOrder) $maxOrder = $item['Order'];
					if ($item['Id'] > $maxId) $maxId = $item['Id'];
				}

				$item = array();
				$item['Id'] = (int) $_POST['Id'];
				$item['Video'] = sanitize_text_field($_POST['Video']);
				$item['Repeat'] = (int) $_POST['Repeat'];
				$item['Scheduled'] = sanitize_text_field($_POST['Scheduled']);
				$item['Order'] = (int) $_POST['Order'];
				if (!$item['Order']) $item['Order'] = $maxOrder + 1;
				if (!$item['Id']) $item['Id'] = $maxId + 1;
				if (!$item['Scheduled']) $item['Scheduled']  = date('Y-m-j h:i:s');

				$playlist[$item['Id']] = $item;

				VWliveStreaming::varSave($playlistPath, $playlist);

				//Return result to jTable
				$jTableResult = array();
				$jTableResult['Result'] = "OK";
				$jTableResult['Record'] = $item;
				print json_encode($jTableResult);
				break;

			case 'delete':
				$Id = (int) $_POST['Id'];

				$playlist = VWliveStreaming::varLoad($playlistPath);
				if (!is_array($playlist)) $playlist = array();

				foreach ($playlist as $key => $value)
					if ($value['Id'] == $Id)
					{
						unset($playlist[$key]);
						break;
					}

				VWliveStreaming::varSave($playlistPath, $playlist);

				//Return result to jTable
				$jTableResult = array();
				$jTableResult['Result'] = "OK";
				print json_encode($jTableResult);
				break;

			default:
				echo 'Action not supported!';
			}

			die;

		}

		//! manual transcoding ajax handler

		function vwls_trans()
		{

			ob_clean();

			$stream = sanitize_file_name($_GET['stream']);

			if (!$stream)
			{
				echo "No stream name provided!";
				return;
			}

			$options = get_option('VWliveStreamingOptions');

			$uploadsPath = $options['uploadsPath'];
			if (!file_exists($uploadsPath)) mkdir($uploadsPath);

			$upath = $uploadsPath . "/$stream/";
			if (!file_exists($upath)) mkdir($upath);

			$rtmp_server=$options['rtmp_server'];

			switch ($_GET['task'])
			{
			case 'enable':

				if ( !is_user_logged_in() )
				{
					echo "Not authorised!";
					exit;
				}

				$cmd = "ps aux | grep '/i_$stream -i rtmp'";
				exec($cmd, $output, $returnvalue);
				//var_dump($output);

				$admin_ajax = admin_url() . 'admin-ajax.php';

				$transcoding = 0;

				foreach ($output as $line) if (strstr($line, "ffmpeg"))
					{
						$columns = preg_split('/\s+/',$line);
						echo "Transcoder is currently Active (".$columns[1]." CPU: ".$columns[2]." Mem: ".$columns[3].")";
						$transcoding = 1;
					}

				if ($transcoding)
				{
					echo '<script>

				setTimeout(\' if (loaderTranscoder) if (loaderTranscoder.abort === \\\'function\\\') loaderTranscoder.abort(); if (transcodingOn) loaderTranscoder = $j("#videowhisperTranscoder").html(ajax_load).load("'.$admin_ajax.'?action=vwls_trans&task=enable&stream='.$stream.'");\', 120000 );

				</script>';
				}

				if (!$transcoding)
				{

					$current_user = wp_get_current_user();


					global $wpdb;
					$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );

					if ($options['externalKeysTranscoder'])
					{
						$key = md5('vw' . $options['webKey'] . $current_user->ID . $postID);

						$keyView = md5('vw' . $options['webKey']. $postID);

						//?session&room&key&broadcaster&broadcasterid
						$rtmpAddress = $options['rtmp_server'] . '?'. urlencode('i_' . $stream) .'&'. urlencode($stream) .'&'. $key . '&1&' . $current_user->ID . '&videowhisper';
						$rtmpAddressView = $options['rtmp_server'] . '?'. urlencode('ffmpegTrans_' . $stream) .'&'. urlencode($stream) .'&'. $keyView . '&0&videowhisper';

						//VWliveStreaming::webSessionSave("/i_". $stream, 1);
					}
					else
					{
						$rtmpAddress = $options['rtmp_server'];
						$rtmpAddressView = $options['rtmp_server'];
					}

					echo "Transcoding process currently not active for '$stream'.<BR>";
					$log_file =  $upath . "videowhisper_transcode.log";


					exec("tail -n 1 $log_file", $output1, $returnvalue);
					echo "Logs: ". substr($output1[0],0,100) . " ...<br>";

					//-vcodec copy
					$cmd = $options['ffmpegPath'] .' ' .  $options['ffmpegTranscode'] . " -threads 1 -f flv \"" .
						$rtmpAddress . "/i_". $stream . "\" -i \"" . $rtmpAddressView ."/". $stream . "\" >&$log_file & ";


					//echo $cmd;
					exec($cmd, $output, $returnvalue);
					exec("echo '$cmd' >> $log_file.cmd", $output, $returnvalue);

					$cmd = "ps aux | grep '/i_$stream -i rtmp'";
					exec($cmd, $output, $returnvalue);
					//var_dump($output);

					foreach ($output as $line) if (strstr($line, "ffmpeg"))
						{
							$columns = preg_split('/\s+/',$line);
							echo "Launching transcoder process #".$columns[1]." ...";
						}


					echo '<script>

				setTimeout(\' if (loaderTranscoder) if (loaderTranscoder.abort === \\\'function\\\') loaderTranscoder.abort(); if (transcodingOn) loaderTranscoder = $j("#videowhisperTranscoder").html(ajax_load).load("'.$admin_ajax.'?action=vwls_trans&task=enable&stream='.$stream.'");\', 120000 );

				</script>';

				}

				$admin_ajax = admin_url() . 'admin-ajax.php';

				echo "<BR><a target='_blank' href='".$admin_ajax . "?action=vwls_trans&task=html5&stream=$stream'> Preview </a> (open in Safari)";
				break;


			case 'close':
				if ( !is_user_logged_in() )
				{
					echo "Not authorised!";
					exit;
				}

				$cmd = "ps aux | grep '/i_$stream -i rtmp'";
				exec($cmd, $output, $returnvalue);
				//var_dump($output);

				$transcoding = 0;
				foreach ($output as $line) if (strstr($line, "ffmpeg"))
					{
						$columns = preg_split('/\s+/',$line);
						$cmd = "kill -9 " . $columns[1];
						exec($cmd, $output, $returnvalue);
						echo "<BR>Closing #".$columns[1]." CPU: ".$columns[2]." Mem: ".$columns[3];
						$transcoding = 1;
					}

				if (!$transcoding)
				{
					echo "Transcoder not found for '$stream'! Nothing to close.";
				}

				break;


			case "html5";
?>
<p>iOS live stream link (open with Safari or test with VLC): <a href="<?php echo $options['httpstreamer']?>i_<?php echo $stream?>/playlist.m3u8"><br />
  <?php echo $stream?> Video</a></p>


<p>HTML5 live video embed below should be accessible <u>only in <B>Safari</B> browser</u> (PC or iOS):</p>
<?php
				echo do_shortcode('[videowhisper_hls channel="'.$stream.'"]');
?>
<p> Due to HTTP based live streaming technology limitations, video can have 15s or more latency. Use a browser with flash support for faster interactions based on RTMP. </p>
<p>Most devices other than iOS, support regular flash playback for live streams.</p>

<style type="text/css">
<!--
BODY
{
	margin:0px;
	background: #333;
	font-family: Arial, Helvetica, sans-serif;
	font-size: 14px;
	color: #EEE;
	padding: 20px;
}

a {
	color: #F77;
	text-decoration: none;
}
-->
</style>
<?php

				break;
			}
			die;
		}



		function shortcode_livesnapshots()
		{

			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_sessions";
			$table_viewers = $wpdb->prefix . "vw_lwsessions";

			$root_url = get_bloginfo( "url" ) . "/";

			//clean recordings
			VWliveStreaming::cleanSessions(0);
			VWliveStreaming::cleanSessions(1);


			$items =  $wpdb->get_results("SELECT * FROM `$table_sessions` where status='1'");

			$livesnapshotsCode .=  "<div>Live Channels";
			if ($items) foreach ($items as $item)
				{
					$count =  $wpdb->get_results("SELECT count(*) as no FROM `$table_viewers` where status='1' and room='".$item->room."'");


					$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $item->room . "' and post_type='channel' LIMIT 0,1" );
					if ($postID) $url = get_post_permalink($postID);
					else $url = plugin_dir_url(__FILE__) . 'ls/channel.php?n=' . urlencode($item->name);


					$urli = $root_url . "wp-content/plugins/videowhisper-live-streaming-integration/ls/snapshots/".urlencode($item->room). ".jpg";
					if (!file_exists("wp-content/plugins/videowhisper-live-streaming-integration/ls/snapshots/".urlencode($item->room). ".jpg")) $urli = $root_url .
							"wp-content/plugins/videowhisper-live-streaming-integration/ls/snapshots/no_video.png";

					$livesnapshotsCode .= "<div style='border: 1px dotted #390; width: 240px; padding: 1px'><a href='$urlc'><IMG width='240px' SRC='$urli'><div ><B>".$item->room."</B>
(".($count[0]->no+1).") ".($item->message?": ".$item->message:"") ."</div></a></div>";
				}
			else  $livesnapshotsCode .= "<div>No broadcasters online.</div>";

			$livesnapshotsCode .=  "</div> ";

			$options = get_option('VWliveStreamingOptions');
			$state = 'block' ;
			if (!$options['videowhisper']) $state = 'none';
			$livesnapshotsCode .= '<div id="VideoWhisper" style="display: ' . $state . ';"><p>Powered by VideoWhisper <a href="https://broadcastlivevideo.com">Broadcast Live Video: HTML5 Live Streaming
Turnkey Site Platform</a>.</p></div>';

			echo $livesnapshotsCode;
		}

		//! Widget

		function widget($args) {
			extract($args);
			echo $before_widget;
			echo $before_title;?>Live Streaming<?php echo $after_title;
			VWliveStreaming::widgetContent();
			echo $after_widget;
		}

		function widgetContent()
		{
			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_sessions";
			$table_viewers = $wpdb->prefix . "vw_lwsessions";

			$root_url = get_bloginfo( "url" ) . "/";

			//clean recordings
			VWliveStreaming::cleanSessions(0);
			VWliveStreaming::cleanSessions(1);

			$items =  $wpdb->get_results("SELECT * FROM `$table_sessions` where status='1'");

			echo "<ul>";
			if ($items) foreach ($items as $item)
				{
					$count =  $wpdb->get_results("SELECT count(id) as no FROM `$table_viewers` where status='1' and room='".$item->room."'");

					$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $item->room . "' and post_type='channel' LIMIT 0,1" );
					if ($postID) $url = get_post_permalink($postID);
					else $url = plugin_dir_url(__FILE__) . 'ls/channel.php?n=' . urlencode($item->name);


					echo "<li><a href='" . $url . "'><B>".$item->room."</B>
(".($count[0]->no+1).") ".($item->message?": ".$item->message:"") ."</a></li>";
				}
			else echo "<li>No broadcasters online.</li>";
			echo "</ul>";

			$options = get_option('VWliveStreamingOptions');

			if ($options['userChannels']||$options['anyChannels'])
				if (is_user_logged_in())
				{
					$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

					$current_user = wp_get_current_user();

					if ($current_user->$userName) $username = $current_user->$userName;
					$username = sanitize_file_name($username);
					?><a href="<?php echo plugin_dir_url(__FILE__); ?>ls/?n=<?php echo $username ?>"><img src="<?php echo plugin_dir_url(__FILE__);
					?>ls/templates/live/i_webcam.png" align="absmiddle" border="0">Video Broadcast</a>
	<?php
				}

			$state = 'block' ;
			if (!$options['videowhisper']) $state = 'none';
			echo '<div id="VideoWhisper" style="display: ' . $state . ';"><p>Powered by VideoWhisper <a href="https://broadcastlivevideo.com">Broadcast Live Video - HTML5 Live Streaming
Turnkey Site Platform</a>.</p></div>';
		}


		function delete_associated_media($id, $unlink=false, $except=0) {

			$htmlCode .= "Removing... ";

			$media = get_children(array(
					'post_parent' => $id,
					'post_type' => 'attachment'
				));
			if (empty($media)) return $htmlCode;

			foreach ($media as $file) {

				if ($except) if ($file->ID == $except) break;

					if ($unlink)
					{
						$filename = get_attached_file($file->ID);
						$htmlCode .=  " Removing $filename #" . $file->ID;
						if (file_exists($filename)) unlink($filename);
					}

				wp_delete_attachment($file->ID);
			}

			return $htmlCode;
		}


		//! Channel Post

		static function the_title($title) {
			$title = esc_attr($title);
			$findthese = array(
				'#Protected:#',
				'#Private:#'
			);
			$replacewith = array(
				'', // What to replace "Protected:" with
				'' // What to replace "Private:" with
			);
			$title = preg_replace($findthese, $replacewith, $title);
			return $title;
		}


		static function channel_page($content)
		{

			$options = get_option('VWliveStreamingOptions');

			if (!$options['postChannels']) return $content;

			if (!is_single()) return $content;
			$postID = get_the_ID() ;

			if (get_post_type( $postID ) != $options['custom_post']) return $content;

			//   global $wpdb;
			//   $stream = $wpdb->get_var( "SELECT post_name FROM $wpdb->posts WHERE ID = '" . $postID . "' and post_type='channel' LIMIT 0,1" );


			$stream = sanitize_file_name(get_the_title($postID));

			global $wp_query;

			$showBroadcastInterface = 0;
			if ($options['broadcasterRedirect']
				&& !array_key_exists( 'broadcast' , $wp_query->query_vars )
				&& !array_key_exists( 'flash-broadcast' , $wp_query->query_vars )
				&& !array_key_exists( 'flash-view' , $wp_query->query_vars )
				&& !array_key_exists( 'flash-video' , $wp_query->query_vars )
				&& !array_key_exists( 'webrtc-broadcast' , $wp_query->query_vars )
				&& !array_key_exists( 'webrtc-playback' , $wp_query->query_vars )
				&& !array_key_exists( 'external' , $wp_query->query_vars )
				&& !array_key_exists( 'external-broadcast' , $wp_query->query_vars )
				&& !array_key_exists( 'external-playback' , $wp_query->query_vars )
				&& !array_key_exists( 'hls' , $wp_query->query_vars )
				&& !array_key_exists( 'mpeg' , $wp_query->query_vars )
				&& !array_key_exists( 'html5-view' , $wp_query->query_vars ) )  //don't redirect from broadcast page or specific interfaces
				{
				$user = wp_get_current_user();
				if ( $user->exists()) //loggedin
					{
					$post = get_post($postID);

					if ($user->ID == $post->post_author) //owner
						{
						if ($options['broadcasterRedirect'] == 'broadcast') $showBroadcastInterface = 1;

						if ($options['broadcasterRedirect'] == 'dashboard')
						{
							$url = get_permalink(get_option("vwls_page_manage"));
							$string = '<script type="text/javascript">';
							$string .= 'window.location = "' . $url . '"';
							$string .= '</script>';

							return $string;
						}
					}
				}
			}

			$offline = '';

			if( array_key_exists( 'broadcast' , $wp_query->query_vars ) || $showBroadcastInterface )
			{
				if (! $addCode = $offline = VWliveStreaming::channelInvalid($stream, true))
					$addCode = '[videowhisper_broadcast]';
				$showBroadcastInterface = 1;
			}
			elseif( array_key_exists( 'webrtc-broadcast' , $wp_query->query_vars ) )
			{
				if (! $addCode = $offline = VWliveStreaming::channelInvalid($stream, true))
					$addCode = '[videowhisper_webrtc_broadcast]';
				$showBroadcastInterface = 1;
			}
			elseif( array_key_exists( 'flash-broadcast' , $wp_query->query_vars ) )
			{
				if (! $addCode = $offline = VWliveStreaming::channelInvalid($stream, true))
					$addCode = '[videowhisper_broadcast flash="1"]';
				$showBroadcastInterface = 1;
			}
			elseif( array_key_exists( 'flash-view' , $wp_query->query_vars ) )
			{
				if (! $addCode = $offline = VWliveStreaming::channelInvalid($stream))
					$addCode = '[videowhisper_watch flash="1"]';
			}
			elseif( array_key_exists( 'video' , $wp_query->query_vars ) )
			{
				if (! $addCode = $offline = VWliveStreaming::channelInvalid($stream))
					$addCode = '[videowhisper_video]';
			}
			elseif( array_key_exists( 'flash-video' , $wp_query->query_vars ) )
			{
				if (! $addCode = $offline = VWliveStreaming::channelInvalid($stream))
					$addCode = '[videowhisper_video flash="1"]';
			}
			elseif( array_key_exists( 'hls' , $wp_query->query_vars ) )
			{
				if (! $addCode = $offline = VWliveStreaming::channelInvalid($stream))
					$addCode = '[videowhisper_hls]';
			}
			elseif( array_key_exists( 'mpeg' , $wp_query->query_vars ) )
			{
				if (! $addCode = $offline = VWliveStreaming::channelInvalid($stream))
					$addCode = '[videowhisper_mpeg]';
			}
			elseif( array_key_exists( 'webrtc-playback' , $wp_query->query_vars ) )
			{
				if (! $addCode = $offline = VWliveStreaming::channelInvalid($stream))
					$addCode = '[videowhisper_webrtc_playback]';
			}
			elseif( array_key_exists( 'html5-view' , $wp_query->query_vars ) )
			{
				if (! $addCode = $offline = VWliveStreaming::channelInvalid($stream))
					$addCode = '[videowhisper_htmlchat_playback]';
			}
			elseif( array_key_exists( 'external' , $wp_query->query_vars ) )
			{
				$addCode = '[videowhisper_external]';
				$content = '';
			}
			elseif( array_key_exists( 'external-broadcast' , $wp_query->query_vars ) )
			{
				$addCode = '[videowhisper_external_broadcast]';
				$content = '';
			}
			elseif( array_key_exists( 'external-playback' , $wp_query->query_vars ) )
			{
				$addCode = '[videowhisper_external_playback]';
				$content = '';
			}
			else
				{ //default
				if (! $addCode = $offline = VWliveStreaming::channelInvalid($stream))
					if ($options['viewerInterface']=='video') $addCode = '[videowhisper_video]';
					else $addCode = '[videowhisper_watch]';
			}

			//ip camera or playlist: update snapshot on access
			$vw_ipCamera = get_post_meta( $postID, 'vw_ipCamera', true );

			if ( $vw_ipCamera || get_post_meta( $postID, 'vw_playlistActive', true ))
			{
				self::streamSnapshot($stream, true, $postID);

			}

			//other data
			if (    !array_key_exists( 'external' , $wp_query->query_vars )
				&& !array_key_exists( 'external-broadcast' , $wp_query->query_vars )
				&& !array_key_exists( 'external-playback' , $wp_query->query_vars )
			)
			{





				if ($stream)
					if (VWliveStreaming::timeTo($stream . '/updateThumb', 300, $options))
					{
						//set thumb
						$dir = $options['uploadsPath']. "/_snapshots";
						$thumbFilename = "$dir/$stream.jpg";

						$attach_id = get_post_thumbnail_id($postID);

						//update post thumb  if file exists and missing post thumb
						if ( file_exists($thumbFilename) && !get_post_thumbnail_id( $postID ))
						{
							$wp_filetype = wp_check_filetype(basename($thumbFilename), null );

							$attachment = array(
								'guid' => $thumbFilename,
								'post_mime_type' => $wp_filetype['type'],
								'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $thumbFilename, ".jpg" ) ),
								'post_content' => '',
								'post_status' => 'inherit'
							);

							$attach_id = wp_insert_attachment( $attachment, $thumbFilename, $postID );
							set_post_thumbnail($postID, $attach_id);

							require_once( ABSPATH . 'wp-admin/includes/image.php' );
							$attach_data = wp_generate_attachment_metadata( $attach_id, $thumbFilename );
							wp_update_attachment_metadata( $attach_id, $attach_data );
						}

						//clean other media
						if ($postID && $attach_id) VWliveStreaming::delete_associated_media($postID, false, $attach_id);
					}

				//update access time for visitor/user

				$ztime = time();

				//user access update (updates with 10s precision): last time when a registered user accessed this content
				if (is_user_logged_in())
				{
					$accessedUser = intval(get_post_meta($postID, 'accessedUser', true));
					if ($ztime - $accessedUser > 10) update_post_meta($postID, 'accessedUser', $ztime);
				}

				//anybody accessed including visitors, 20s precision
				$accessed = intval(get_post_meta($postID, 'accessed', true));
				if ($ztime - $accessed > 20) update_post_meta($postID, 'accessed', $ztime);


				//handle paused restreams
				if ($vw_ipCamera) self::restreamPause($postID, $stream, $options);


				//meta info
				$edate = get_post_meta($postID, 'edate', true);
				if ($edate) $metaCode .= '<div class="item">' . __('Last Broadcast','live-streaming') . ': ' . VWliveStreaming::format_age($ztime - $edate) . '</div>';

				//viewers
				$maxViewers =  get_post_meta($postID, 'maxViewers', true);
				if (!is_array($maxViewers)) if ($maxViewers>0)
					{
						$metaCode .= '<div class="item">';

						$maxDate = (int) get_post_meta($postID, 'maxDate', true);
						$metaCode .= ' ' . __('Maximum viewers','live-streaming') . ': ' . $maxViewers;
						if ($maxDate) $metaCode .= ' on ' . date("F j, Y, g:i a", $maxDate);

						$metaCode .= '</div>';
					}

				//watch time
				$wtime = get_post_meta($postID, 'wtime', true);
				if ($wtime) $metaCode .= '<div class="item">' .  __('Total Watch Time', 'live-streaming') . ': ' . VWliveStreaming::format_time($wtime) . '</div>';


				if ($metaCode)  $addCode .= '<div class="ui ' . $options['interfaceClass'] .' segment">' . $metaCode . '</div>';

				if (!$offline) $addCode .= VWliveStreaming::eventInfo($postID);

				//! show reviews
				if ($options['rateStarReview'])
				{
					//tab : reviews
					if (shortcode_exists("videowhisper_review"))
						$aftercode .= '<h3>' . __('My Review', 'live-streaming') . '</h3>' . do_shortcode('[videowhisper_review content_type="channel" post_id="' . $postID . '" content_id="' . $postID . '"]' );
					else $aftercode .= 'Warning: shortcodes missing. Plugin <a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> should be installed and enabled or feature disabled.';

					if (shortcode_exists("videowhisper_reviews"))
						$aftercode .= '<h3>' . __('Reviews', 'live-streaming') . '</h3>' . do_shortcode('[videowhisper_reviews post_id="' . $postID . '"]' );

				}
			}


			return $addCode . $content . $aftercode;
		}

		function eventInfo($postID)
		{
			$eventTitle = get_post_meta($postID, 'eventTitle', true);
			if ($eventTitle)
			{
				$eventStart = get_post_meta($postID, 'eventStart', true);
				$eventEnd = get_post_meta($postID, 'eventEnd', true);
				$eventStartTime = get_post_meta($postID, 'eventStartTime', true);
				$eventEndTime = get_post_meta($postID, 'eventEndTime', true);

				$eventDescription= get_post_meta($postID, 'eventDescription', true);

				$showImage=get_post_meta( $postID, 'showImage', true );
				if ($showImage == 'event' || $showImage == 'all')
				{
					//get post thumb
					$attach_id = get_post_thumbnail_id($postID);
					if ($attach_id) $thumbFilename = get_attached_file($attach_id);

					if (file_exists($thumbFilename)) $snapshot = VWliveStreaming::path2url($thumbFilename);

					$eventCode .= '<IMG style="padding: 10px" SRC="'.$snapshot.'" ALIGN="LEFT">';

				}
				$eventCode .= '<BR>';
				$eventCode .= '<H3>' . $eventTitle. '</H3>';
				if ($eventStart||$eventStartTime) $eventCode .= 'Starts: '.$eventStart . ' ' . $eventStartTime;
				if ($eventEnd||$eventEndTime) $eventCode .= '<BR>Ends: '.$eventEnd . ' ' . $eventEndTime ;
				if ($eventDescription) $eventCode .= '<p>'.$eventDescription.'</p>';
				$eventCode .= '<BR style="clear:both">';
			} else return '';

			return $eventCode;

		}
		public static function pre_get_posts($query)
		{

			//add channels to post listings
			if(is_category() || is_tag())
			{
				$query_type = get_query_var('post_type');

				if($query_type)
					if (is_array($query_type))
					{
						if (in_array('post',$query_type) && !in_array('channel',$query_type))
							$query_type[] = 'channel';
						$query->set('post_type', $query_type);
					}
				//else  //default
				// $query_type = array('post', 'channel');

			}

			return $query;
		}

		function columns_head_channel($defaults) {
			$defaults['featured_image'] = 'Snapshot';
			$defaults['edate'] = 'Last Online';

			return $defaults;
		}

		function columns_register_sortable( $columns ) {
			$columns['edate'] = 'edate';

			return $columns;
		}


		function columns_content_channel($column_name, $post_id)
		{

			if ($column_name == 'featured_image')
			{

				global $wpdb;
				$postName = $wpdb->get_var( "SELECT post_title FROM $wpdb->posts WHERE ID = '" . $post_id . "' and post_type='channel' LIMIT 0,1" );

				if ($postName)
				{
					$options = get_option('VWliveStreamingOptions');
					$dir = $options['uploadsPath']. "/_thumbs";
					$thumbFilename = "$dir/" . $postName . ".jpg";

					$url = VWliveStreaming::roomURL($postName);

					if (file_exists($thumbFilename)) echo '<a href="' . $url . '"><IMG src="' . VWliveStreaming::path2url($thumbFilename) .'" width="' . $options['thumbWidth'] . 'px" height="' . $options['thumbHeight'] . 'px"></a>';

				}



			}

			if ($column_name == 'edate')
			{
				$edate = get_post_meta($post_id, 'edate', true);
				if ($edate)
				{
					echo ' ' . VWliveStreaming::format_age(time() - $edate);

				}


			}

		}

		public static function duration_column_orderby( $vars ) {
			if ( isset( $vars['orderby'] ) && 'edate' == $vars['orderby'] ) {
				$vars = array_merge( $vars, array(
						'meta_key' => 'edate',
						'orderby' => 'meta_value_num'
					) );
			}

			return $vars;
		}


		public static function query_vars( $query_vars ){
			// array of recognized query vars
			$query_vars[] = 'broadcast';
			$query_vars[] = 'flash-broadcast';
			$query_vars[] = 'flash-view';
			$query_vars[] = 'flash-video';
			$query_vars[] = 'video';
			$query_vars[] = 'hls';
			$query_vars[] = 'mpeg';
			$query_vars[] = 'webrtc-broadcast';
			$query_vars[] = 'webrtc-playback';
			$query_vars[] = 'html5-view';
			$query_vars[] = 'external';
			$query_vars[] = 'external-broadcast';
			$query_vars[] = 'external-playback';
			$query_vars[] = 'vwls_eula';
			$query_vars[] = 'vwls_crossdomain';
			$query_vars[] = 'vwls_fullchannel';

			return $query_vars;
		}

		function parse_request( &$wp )
		{
			if ( array_key_exists( 'vwls_eula', $wp->query_vars ) ) {
				$options = get_option('VWliveStreamingOptions');
				echo html_entity_decode(stripslashes($options['eula_txt']));
				exit();
			}

			if ( array_key_exists( 'vwls_crossdomain', $wp->query_vars ) ) {
				$options = get_option('VWliveStreamingOptions');
				echo html_entity_decode(stripslashes($options['crossdomain_xml']));
				exit();
			}

			if ( array_key_exists( 'vwls_fullchannel', $wp->query_vars ) ) {

				$stream = sanitize_file_name($wp->query_vars['vwls_fullchannel']);

				if (!$stream)
				{
					echo "No channel name provided!";
					exit;

				}

				echo '<title>' . $stream . '</title>
<body style="margin:0; padding:0; width:100%; height:100%">
';
				echo VWliveStreaming::flash_watch($stream);

				exit();
			}

		}

		// Register Custom Post Type
		function channel_post() {

			$options = get_option('VWliveStreamingOptions');
			if (!$options['postChannels']) return;

			//only if missing
			if (post_type_exists($options['custom_post'])) return;

			$labels = array(
				'name'                => _x( 'Channels', 'Post Type General Name', 'live-streaming' ),
				'singular_name'       => _x( 'Channel', 'Post Type Singular Name', 'live-streaming' ),
				'menu_name'           => __( 'Channels', 'live-streaming' ),
				'parent_item_colon'   => __( 'Parent Channel', 'live-streaming' ) . ':',
				'all_items'           => __( 'All Channels', 'live-streaming' ),
				'view_item'           => __( 'View Channel', 'live-streaming' ),
				'add_new_item'        => __( 'Add New Channel', 'live-streaming' ),
				'add_new'             => __( 'New Channel', 'live-streaming' ),
				'edit_item'           => __( 'Edit Channel', 'live-streaming' ),
				'update_item'         => __( 'Update Channel', 'live-streaming' ),
				'search_items'        => __( 'Search Channels', 'live-streaming' ),
				'not_found'           => __( 'No Channels found', 'live-streaming' ),
				'not_found_in_trash'  => __( 'No Channels found in Trash', 'live-streaming' ),
			);
			$args = array(
				'label'               => __( 'channel', 'live-streaming' ),
				'description'         => __( 'Video Channels', 'live-streaming' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields', 'page-attributes', ),
				'taxonomies'          => array( 'category', 'post_tag' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'menu_icon' => 'dashicons-video-alt',
				'capability_type'     => 'post',
				'capabilities' => array(
					'create_posts' => 'do_not_allow', // false < WP 4.5
					'edit_posts'   => 'edit_posts',
					'edit_post'   => 'edit_post',
					'edit_other_posts'   => 'edit_other_posts',
					'delete_post'        => 'delete_post',

				),
				'map_meta_cap' => true, // Set to `false`, if users are not allowed to edit/delete existing posts
			);
			register_post_type( $options['custom_post'], $args );

			add_rewrite_endpoint( 'broadcast', EP_ALL );
			add_rewrite_endpoint( 'flash-broadcast', EP_ALL );
			add_rewrite_endpoint( 'flash-view', EP_ALL );
			add_rewrite_endpoint( 'flash-video', EP_ALL );
			add_rewrite_endpoint( 'video', EP_ALL );
			add_rewrite_endpoint( 'hls', EP_ALL );
			add_rewrite_endpoint( 'mpeg', EP_ALL );
			add_rewrite_endpoint( 'external', EP_ALL );
			add_rewrite_endpoint( 'external-broadcast', EP_ALL );
			add_rewrite_endpoint( 'external-playback', EP_ALL );
			add_rewrite_endpoint( 'webrtc-broadcast', EP_ALL );
			add_rewrite_endpoint( 'webrtc-playback', EP_ALL );
			add_rewrite_endpoint( 'html5-view', EP_ALL );

			add_rewrite_rule( 'eula.txt$', 'index.php?vwls_eula=1', 'top' );
			add_rewrite_rule( 'crossdomain.xml$', 'index.php?vwls_crossdomain=1', 'top' );
			add_rewrite_rule( '^fullchannel/([\w]*)?', 'index.php?vwls_fullchannel=$matches[1]', 'top' );


			//flush_rewrite_rules();

		}



		//! Billing Integration: MyCred, TeraWallet (WooWallet)

		static function balances($userID, $options = null)
		{
			//get html code listing balances
			if (!$options) $options = get_option('VWliveStreamingOptions');
			if (!$options['walletMulti']) return ''; //disabled

			$balances = self::walletBalances($userID, '', $options);

			$walletTransfer = sanitize_text_field( $_GET['walletTransfer'] );

			global $wp;
			foreach ($balances as $key=>$value)
			{
				$htmlCode .= '<br>'. $key . ': ' . $value;

				if ($options['walletMulti'] == 2 && $walletTransfer != $key && $options['wallet'] != $key && $value>0) $htmlCode .= ' <a class="ui button compact tiny" href=' . add_query_arg(array('walletTransfer'=>$key),$wp->request) . ' data-tooltip="Transfer to Active Balance">Transfer</a>';

				if ($walletTransfer == $key || ($value>0 && $options['walletMulti'] == 3 && $options['wallet'] != $key))
				{
					self::walletTransfer($key, $options['wallet'], get_current_user_id(), $options);
					$htmlCode .= ' Transferred to active balance.';
				}

			}


			return $htmlCode;
		}

		static function walletBalances($userID, $view = 'view', $options = null)
		{
			$balances = array();
			if (!$userID) return $balances;

			//woowallet
			if ($GLOBALS['woo_wallet'])
			{
				$wooWallet = $GLOBALS['woo_wallet'];
				$balances['WooWallet'] = $wooWallet->wallet->get_wallet_balance( $userID, $view);
			}

			//mycred
			if (function_exists( 'mycred_get_users_balance')) $balances['MyCred'] = mycred_get_users_balance($userID);

			return  $balances;
		}


		static function walletTransfer($source, $destination, $userID, $options = null)
		{
			//transfer balance from a wallet to another wallet

			if ($source == $destination) return;

			if (!$options) $options = get_option('VWliveStreamingOptions');

			$balances = self::walletBalances($userID, '', $options);

			if ($balances[$source] > 0)
			{
				self::walletTransaction($destination, $balances[$source], $userID, "Wallet balance transfer from $source to $destination.", 'wallet_transfer');
				self::walletTransaction($source, - $balances[$source], $userID, "Wallet balance transfer from $source to $destination.", 'wallet_transfer');
			}

		}

		static function walletTransaction($wallet, $amount, $user_id, $entry, $ref, $ref_id = null, $data = null)
		{
			//transactions on all supported wallets
			//$wallet : MyCred/WooWallet

			if ($amount == 0) return; //no transaction

			//mycred
			if ($wallet == 'MyCred')
				if ($amount>0)
				{
					if (function_exists('mycred_add')) mycred_add($ref, $user_id, $amount, $entry, $ref_id, $data);
				}
			else
			{
				if (function_exists('mycred_subtract')) mycred_subtract( $ref, $user_id, $amount, $entry, $ref_id, $data );
			}

			//woowallet
			if ($wallet == 'WooWallet')
				if ($GLOBALS['woo_wallet'])
				{
					$wooWallet = $GLOBALS['woo_wallet'];

					if ($amount>0)
					{
						$wooWallet->wallet->credit( $user_id, $amount, $entry );
					}
					else
					{
						$wooWallet->wallet->debit( $user_id, -$amount, $entry );
					}

				}

		}

		static function balance($userID, $live = false, $options = null)
		{
			//get current user balance (as value)
			// $live also estimates active (incomplete) session costs for client

			if (!$userID) return 0;

			if (!$options) $options = get_option('VWliveStreamingOptions');

			$balance = 0;

			$balances = self::walletBalances($userID, '', $options);

			if ($options['wallet'])
				if (array_key_exists($options['wallet'], $balances)) $balance = $balances[$options['wallet']];

				if ($live)
				{
					$updated = get_user_meta($userID, 'vw_ppv_tempt', true);

					if (time() - $updated < 15) //updated recently: use that estimation
						$temp = get_user_meta($userID, 'vw_ppv_temp', true);
					else $temp = self::billSessions($userID, 0, false); //estimate charges for current sessions

					$balance = $balance - $temp; //deduct temporary charge
				}

			return $balance;
		}

		static function transaction($ref = "live_streaming", $user_id = 1, $amount = 0, $entry = "Live Streaming transaction.", $ref_id = null, $data = null, $options = null)
		{
			//ref = explanation ex. ppv_client_payment
			//entry = explanation ex. PPV client payment in room.
			//utils: ref_id (int|string|array) , data (int|string|array|object)

			if ($amount == 0) return; //nothing


			if (!$options) $options = get_option('VWliveStreamingOptions');

			//active wallet
			if ($options['wallet']) $wallet = $options['wallet'];
			if (!$wallet) $wallet = 'MyCred';
			if (!function_exists('mycred_add')) if ($GLOBALS['woo_wallet']) $wallet = 'WooWallet';


				self::walletTransaction($wallet, $amount, $user_id, $entry, $ref, $ref_id, $data);
		}



		static function userPaidAccess($userID, $postID)
		{
			//checks if user has access to content that may be fore sale

			if (!class_exists( 'myCRED_Sell_Content_Module' ) ) return true; //sell content disabled

			$meta = get_post_meta($postID, 'myCRED_sell_content', true);

			if (!$meta) return true; // not for sale
			if (!$meta['price']) return true; //or no price

			if (!$userID) return false; //not logged in: did not purchase

			//check transaction log
			global $wpdb;

			$table_sessionsC = $wpdb->prefix . "myCRED_log";
			$isBuyer = $wpdb->get_col( $sql = "SELECT user_id FROM {$table_sessionsC} WHERE user_id={$userID} AND ref = 'buy_content' AND ref_id = {$postID} AND creds < 0" );
			if (!$isBuyer) return false; //did not purchase
			else return true;
		}

		//! Admin


		function admin_init()
		{
			add_meta_box(
				'vwls-nav-menus',
				'Channel Categories',
				array('VWliveStreaming', 'nav_menus'),
				'nav-menus',
				'side',
				'default');
		}

		function nav_menus()
		{

			//$object, $taxonomy

			global $nav_menu_selected_id;
			$taxonomy_name = 'category';

			// Paginate browsing for large numbers of objects.
			$per_page = 50;
			$pagenum = isset( $_REQUEST[$taxonomy_name . '-tab'] ) && isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1;
			$offset = 0 < $pagenum ? $per_page * ( $pagenum - 1 ) : 0;

			$args = array(
				'child_of' => 0,
				'exclude' => '',
				'hide_empty' => false,
				'hierarchical' => 1,
				'include' => '',
				'number' => $per_page,
				'offset' => $offset,
				'order' => 'ASC',
				'orderby' => 'name',
				'pad_counts' => false,
			);

			$terms = get_terms( $taxonomy_name, $args );

			if ( ! $terms || is_wp_error($terms) ) {
				echo '<p>' . __( 'No items.' ) . '</p>';
				return;
			}

			$num_pages = ceil( wp_count_terms( $taxonomy_name , array_merge( $args, array('number' => '', 'offset' => '') ) ) / $per_page );

			$page_links = paginate_links( array(
					'base' => add_query_arg(
						array(
							$taxonomy_name . '-tab' => 'all',
							'paged' => '%#%',
							'item-type' => 'taxonomy',
							'item-object' => $taxonomy_name,
						)
					),
					'format' => '',
					'prev_text' => __('&laquo;'),
					'next_text' => __('&raquo;'),
					'total' => $num_pages,
					'current' => $pagenum
				));

			$db_fields = false;
			if ( is_taxonomy_hierarchical( $taxonomy_name ) ) {
				$db_fields = array( 'parent' => 'parent', 'id' => 'term_id' );
			}

			$walker = new Walker_Nav_Menu_Checklist( $db_fields );

			$current_tab = 'most-used';
			if ( isset( $_REQUEST[$taxonomy_name . '-tab'] ) && in_array( $_REQUEST[$taxonomy_name . '-tab'], array('all', 'most-used', 'search') ) ) {
				$current_tab = $_REQUEST[$taxonomy_name . '-tab'];
			}

			if ( ! empty( $_REQUEST['quick-search-taxonomy-' . $taxonomy_name] ) ) {
				$current_tab = 'search';
			}

			$removed_args = array(
				'action',
				'customlink-tab',
				'edit-menu-item',
				'menu-item',
				'page-tab',
				'_wpnonce',
			);

?>
	<div id="taxonomy-<?php echo $taxonomy_name; ?>" class="taxonomydiv">
		<ul id="taxonomy-<?php echo $taxonomy_name; ?>-tabs" class="taxonomy-tabs add-menu-item-tabs">
			<li <?php echo ( 'most-used' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a class="nav-tab-link" data-type="tabs-panel-<?php echo esc_attr( $taxonomy_name ); ?>-pop" href="<?php if ( $nav_menu_selected_id ) echo esc_url(add_query_arg($taxonomy_name . '-tab', 'most-used', remove_query_arg($removed_args))); ?>#tabs-panel-<?php echo $taxonomy_name; ?>-pop">
					<?php _e( 'Most Used' ); ?>
				</a>
			</li>
			<li <?php echo ( 'all' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a class="nav-tab-link" data-type="tabs-panel-<?php echo esc_attr( $taxonomy_name ); ?>-all" href="<?php if ( $nav_menu_selected_id ) echo esc_url(add_query_arg($taxonomy_name . '-tab', 'all', remove_query_arg($removed_args))); ?>#tabs-panel-<?php echo $taxonomy_name; ?>-all">
					<?php _e( 'View All' ); ?>
				</a>
			</li>
			<li <?php echo ( 'search' == $current_tab ? ' class="tabs"' : '' ); ?>>
				<a class="nav-tab-link" data-type="tabs-panel-search-taxonomy-<?php echo esc_attr( $taxonomy_name ); ?>" href="<?php if ( $nav_menu_selected_id ) echo esc_url(add_query_arg($taxonomy_name . '-tab', 'search', remove_query_arg($removed_args))); ?>#tabs-panel-search-taxonomy-<?php echo $taxonomy_name; ?>">
					<?php _e( 'Search' ); ?>
				</a>
			</li>
		</ul><!-- .taxonomy-tabs -->

		<div id="tabs-panel-<?php echo $taxonomy_name; ?>-pop" class="tabs-panel <?php
			echo ( 'most-used' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
			?>">
			<ul id="<?php echo $taxonomy_name; ?>checklist-pop" class="categorychecklist form-no-clear" >
				<?php
			$popular_terms = get_terms( $taxonomy_name, array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 10, 'hierarchical' => false ) );
			$args['walker'] = $walker;
			echo walk_nav_menu_tree( array_map(array('VWliveStreaming', 'nav_menu_item'), $popular_terms), 0, (object) $args );
?>
			</ul>
		</div><!-- /.tabs-panel -->

		<div id="tabs-panel-<?php echo $taxonomy_name; ?>-all" class="tabs-panel tabs-panel-view-all <?php
			echo ( 'all' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
			?>">
			<?php if ( ! empty( $page_links ) ) : ?>
				<div class="add-menu-item-pagelinks">
					<?php echo $page_links; ?>
				</div>
			<?php endif; ?>
			<ul id="<?php echo $taxonomy_name; ?>checklist" data-wp-lists="list:<?php echo $taxonomy_name?>" class="categorychecklist form-no-clear">
				<?php
			$args['walker'] = $walker;
			echo walk_nav_menu_tree( array_map(array('VWliveStreaming', 'nav_menu_item'), $terms), 0, (object) $args );
?>
			</ul>
			<?php if ( ! empty( $page_links ) ) : ?>
				<div class="add-menu-item-pagelinks">
					<?php echo $page_links; ?>
				</div>
			<?php endif; ?>
		</div><!-- /.tabs-panel -->

		<div class="tabs-panel <?php
			echo ( 'search' == $current_tab ? 'tabs-panel-active' : 'tabs-panel-inactive' );
			?>" id="tabs-panel-search-taxonomy-<?php echo $taxonomy_name; ?>">
			<?php
			if ( isset( $_REQUEST['quick-search-taxonomy-' . $taxonomy_name] ) ) {
				$searched = esc_attr( $_REQUEST['quick-search-taxonomy-' . $taxonomy_name] );
				$search_results = get_terms( $taxonomy_name, array( 'name__like' => $searched, 'fields' => 'all', 'orderby' => 'count', 'order' => 'DESC', 'hierarchical' => false ) );
			} else {
				$searched = '';
				$search_results = array();
			}
?>
			<p class="quick-search-wrap">
				<input type="search" class="quick-search input-with-default-title" title="<?php esc_attr_e('Search'); ?>" value="<?php echo $searched; ?>" name="quick-search-taxonomy-<?php echo $taxonomy_name; ?>" />
				<span class="spinner"></span>
				<?php submit_button( __( 'Search' ), 'button-small quick-search-submit button-secondary hide-if-js', 'submit', false, array( 'id' => 'submit-quick-search-taxonomy-' . $taxonomy_name ) ); ?>
			</p>

			<ul id="<?php echo $taxonomy_name; ?>-search-checklist" data-wp-lists="list:<?php echo $taxonomy_name?>" class="categorychecklist form-no-clear">
			<?php if ( ! empty( $search_results ) && ! is_wp_error( $search_results ) ) : ?>
				<?php
				$args['walker'] = $walker;
			echo walk_nav_menu_tree( array_map(array('VWliveStreaming', 'nav_menu_item'), $search_results), 0, (object) $args );
?>
			<?php elseif ( is_wp_error( $search_results ) ) : ?>
				<li><?php echo $search_results->get_error_message(); ?></li>
			<?php elseif ( ! empty( $searched ) ) : ?>
				<li><?php _e('No results found.'); ?></li>
			<?php endif; ?>
			</ul>
		</div><!-- /.tabs-panel -->

		<p class="button-controls">
			<span class="list-controls">
				<a href="<?php
			echo esc_url(add_query_arg(
					array(
						$taxonomy_name . '-tab' => 'all',
						'selectall' => 1,
					),
					remove_query_arg($removed_args)
				));
			?>#taxonomy-<?php echo $taxonomy_name; ?>" class="select-all"><?php _e('Select All'); ?></a>
			</span>

			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-taxonomy-menu-item" id="<?php echo esc_attr( 'submit-taxonomy-' . $taxonomy_name ); ?>" />
				<span class="spinner"></span>
			</span>
		</p>

	</div><!-- /.taxonomydiv -->
	        <?php
		}


		function single_template($single_template)
		{

			if (!is_single())  return $single_template;

			$options = get_option('VWliveStreamingOptions');
			//if (!$options['custom_post']) $options['custom_post'] = 'channel';

			$postID = get_the_ID();

			if ( get_post_type( $postID ) != $options['custom_post']) return $single_template;

			if ($options['postTemplate'] == '+plugin')
			{
				$single_template_new = dirname( __FILE__ ) . '/template-channel.php';
				if (file_exists($single_template_new)) return $single_template_new;
			}


			$single_template_new = get_stylesheet_directory() . '/' . $options['postTemplate'];

			if (file_exists($single_template_new)) return $single_template_new;
			else return $single_template;
		}

		function nav_menu_item( $menu_item )
		{

			$menu_item->ID = $menu_item->term_id;
			$menu_item->db_id = 0;
			$menu_item->menu_item_parent = 0;
			$menu_item->object_id = (int) $menu_item->term_id;
			$menu_item->post_parent = (int) $menu_item->parent;
			$menu_item->type = 'custom';

			$object = get_taxonomy( $menu_item->taxonomy );
			$menu_item->object = $object->name;
			$menu_item->type_label = $object->labels->singular_name;

			$menu_item->title = $menu_item->name;

			$options = get_option('VWliveStreamingOptions');
			if ($options['disablePageC']=='0')
			{
				$page_id = get_option("vwls_page_channels");
				$permalink = get_permalink( $page_id);
				$menu_item->url = add_query_arg(array('cid' => $menu_item->object_id, 'category' => $menu_item->name), $permalink);
			} else $menu_item->url = get_term_link( $menu_item, $menu_item->taxonomy ) . '?channels=1' ;

			$menu_item->target = '';
			$menu_item->attr_title = '';
			$menu_item->description = get_term_field( 'description', $menu_item->term_id, $menu_item->taxonomy );
			$menu_item->classes = array();
			$menu_item->xfn = '';

			/**
			 * @param object $menu_item The menu item object.
			 */
			return $menu_item;
		}


		static function getDirectorySize($path)
		{
			$totalsize = 0;
			$totalcount = 0;
			$dircount = 0;

			if (!file_exists($path))
			{
				$total['size'] = $totalsize;
				$total['count'] = $totalcount;
				$total['dircount'] = $dircount;
				return $total;
			}

			if ($handle = opendir($path))
			{
				while (false !== ($file = readdir($handle)))
				{
					$nextpath = $path . '/' . $file;
					if ($file != '.' && $file != '..' && !is_link($nextpath))
					{
						if (is_dir($nextpath))
						{
							$dircount++;
							$result = VWliveStreaming::getDirectorySize($nextpath);
							$totalsize += $result['size'];
							$totalcount += $result['count'];
							$dircount += $result['dircount'];
						}
						elseif (is_file($nextpath))
						{
							$totalsize += filesize($nextpath);
							$totalcount++;
						}
					}
				}
			}
			closedir($handle);
			$total['size'] = $totalsize;
			$total['count'] = $totalcount;
			$total['dircount'] = $dircount;
			return $total;
		}

		static function sizeFormat($size)
		{
			//echo $size;
			if($size<1024)
			{
				return $size." bytes";
			}
			else if($size<(1024*1024))
				{
					$size=round($size/1024,2);
					return $size." KB";
				}
			else if($size<(1024*1024*1024))
				{
					$size=round($size/(1024*1024),2);
					return $size." MB";
				}
			else
			{
				$size=round($size/(1024*1024*1024),2);
				return $size." GB";
			}

		}

		//if any element from array1 in array2
		static function any_in_array($array1, $array2)
		{
			foreach ($array1 as $value) if (in_array($value,$array2)) return true;
				return false;
		}

		function admin_bar_menu($wp_admin_bar)
		{
			if (!is_user_logged_in()) return;

			$options = get_option('VWliveStreamingOptions');

			if( current_user_can('editor') || current_user_can('administrator') ) {

				$menu_id = 'videowhisper-livestreaming';

				$wp_admin_bar->add_node( array(
						'id'     => $menu_id,
						'title' => 'BroadcastLiveVideo',
						'href'  => admin_url('admin.php?page=live-streaming')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-live',
						'title' => __('Live & Ban', 'live-streaming'),
						'href'  => admin_url('admin.php?page=live-streaming-live')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-posts',
						'title' => __('Channel Posts', 'live-streaming'),
						'href'  => admin_url('edit.php?post_type=' . $options['custom_post'])
					) );


				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-statistics',
						'title' => __('Statistics', 'live-streaming'),
						'href'  => admin_url('admin.php?page=live-streaming-stats')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-settings',
						'title' => __('Settings', 'live-streaming'),
						'href'  => admin_url('admin.php?page=live-streaming')
					) );

				$wp_admin_bar->add_node( array(
						'parent' => $menu_id,
						'id'     => $menu_id . '-docs',
						'title' => __('Documentation', 'live-streaming'),
						'href'  => admin_url('admin.php?page=live-streaming-docs')
					) );
			}

			$user_id = get_current_user_id();
			$current_user = wp_get_current_user();

			if ($vwls_page_manage = get_option("vwls_page_manage"))
				if (get_post_status( $vwls_page_manage)) //exists
					if ( $options['canBroadcast'] == 'members' || VWliveStreaming::any_in_array( array( $options['broadcastList'], 'administrator', 'super admin'), $current_user->roles))
						$wp_admin_bar->add_node(array(
								'parent' => 'my-account-with-avatar',
								'id'     => 'vwls_page_manage',
								'title' => __('Broadcast Live', 'live-streaming') ,
								'href'  =>  get_permalink($vwls_page_manage),
							));

					if ($vwls_page_channels = get_option("vwls_page_channels"))
						if (get_post_status( $vwls_page_channels)) //exists
							if ( $options['canWatch'] == 'members' || $options['canWatch'] == 'all' || VWliveStreaming::any_in_array( array( $options['watchList'], 'administrator', 'super admin'), $current_user->roles))
								$wp_admin_bar->add_node(array(
										'parent' => 'my-account-with-avatar',
										'id'     => 'vwls_page_channels',
										'title' => __('Browse Channels', 'live-streaming') ,
										'href'  =>  get_permalink($vwls_page_channels),
									));

							//broadcast channels
							$args = array(
								'author'           => $user_id,
								'orderby'          => 'post_date',
								'order'            => 'DESC',
								'post_type'        => $options['custom_post'],
								'posts_per_page'   => 20,
								'offset'           => 0,
							);

						$channels = get_posts( $args );

					if (!count($channels)) return;

					foreach ($channels as $channel)
					{
						$args = array(
							'parent' => 'my-account-with-avatar',
							'id'     => 'videowhisper_' . $channel->post_name,
							'title' => __('Broadcast', 'live-streaming') . ' ' . $channel->post_title,
							'href'  => add_query_arg(array('broadcast'=>''), get_permalink($channel->ID)),
						);
						$wp_admin_bar->add_node( $args );
					}


		}

		function admin_menu() {

			add_menu_page('Live Streaming', 'Live Streaming', 'manage_options', 'live-streaming', array('VWliveStreaming', 'settingsPage'), 'dashicons-video-alt',82);
			add_submenu_page("live-streaming", "Live Streaming", "Settings", 'manage_options', "live-streaming", array('VWliveStreaming', 'settingsPage'));
			add_submenu_page("live-streaming", "Live Streaming", "Statistics", 'manage_options', "live-streaming-stats", array('VWliveStreaming', 'adminStats'));
			add_submenu_page("live-streaming", "Live Streaming", "Live & Ban", 'manage_options', "live-streaming-live", array('VWliveStreaming', 'adminLive'));
			add_submenu_page("live-streaming", "Live Streaming", "Documentation", 'manage_options', "live-streaming-docs", array('VWliveStreaming', 'adminDocs'));

			//hide add submenu
			global $submenu;
			unset($submenu['edit.php?post_type=channel'][10]);
		}


		//! cron
		function cron_schedules( $schedules ) {
			$schedules['min10'] = array(
				'interval' => 600,
				'display' => __( 'Once every 10 minutes' )
			);
			return $schedules;
		}


		static function setupSchedule() {
			if ( ! wp_next_scheduled( 'cron_10min_event') )
			{
				wp_schedule_event( time(), 'min10', 'cron_10min_event');
			}

		}

		function cron_10min_event()
		{
			//called each 10 min or more

			$options = get_option('VWliveStreamingOptions');

			if (!$options['restreamPause']) return;

			//if (!self::timeTo('/cron_10min', 600, $options)) return; //too fast

			//ip camera or re-streams

			$meta_query = array(
				'relation' => 'AND', // Optional, defaults to "AND"
				array(
					'key'     => 'vw_ipCamera',
					'value'   => '',
					'compare' => '!='
				),
				array(
					'key'     => 'vw_ipCamera',
					'compare' => 'EXISTS'
				)
			);

			$args=array(
				'post_type' =>  $options['custom_post'],
				'numberposts' => -1,
				'orderby'       =>  'post_date',
				'order'            => 'DESC',
				'meta_query' => $meta_query

			);

			$posts = get_posts( $args );

			if (is_array($posts)) if (count($posts))
					foreach ($posts as $post)
					{
						self::restreamPause($post->ID, $post->post_title, $options);

						$restreamPaused = get_post_meta($post->ID, 'restreamPaused', true);
						if (!$restreamPaused) self::streamSnapshot($post->post_title, true, $post->ID);
					}


		}



		function admin_head() {
			if( get_post_type() != 'channel') return;

			//hide add button
			echo '<style type="text/css">
    #favorite-actions {display:none;}
    .add-new-h2{display:none;}
    .tablenav{display:none;}
    </style>';
		}


		static function humanSize($value)
		{
			if ($value > 1000000000000) return number_format($value/1000000000000, 2) . 't';
			if ($value > 1000000000) return number_format($value/1000000000, 2) . 'g';
			if ($value > 1000000) return number_format($value/1000000, 2) . 'm';
			if ($value > 1000) return number_format($value/1000, 2) . 'k';
			return $value;
		}
		
		
		function adminStats()
		{
			$options = get_option('VWliveStreamingOptions');

?>
	<h3>Channel Status, Statistics</h3>
<?php

			if ($_GET['regenerateThumbs'])
			{
				$dir=$options['uploadsPath'];
				$dir .= "/_snapshots";
				echo '<div class="info">Regenerating thumbs for listed channels.</div>';
			}

			//RTMP Session Control
			if (in_array($options['webStatus'], array('enabled', 'strict', 'auto')))
				if (file_exists($path = $options['uploadsPath']. '/_rtmpStatus.txt'))
				{
					$url = VWliveStreaming::path2url($path);
					echo '+ RTMP Session Info Detected: <a target=_blank href="'.$url.'">last status request</a> ' . date("D M j G:i:s T Y", filemtime($path)) ;
					
					echo '<h4>Last App Instance Info</h4>';
						$sessionsVars = self::varLoad($options['uploadsPath']. '/sessionsApp');
						if (is_array($sessionsVars)) 
						{
							if (array_key_exists( 'appInstanceInfo', $sessionsVars) ) echo 'Last App Instance: ' . $sessionsVars['appInstanceInfo'];
					
							ksort($sessionsVars);
							
							echo '<h5>Streaming Host Limits</h5>';
							foreach ($sessionsVars as $key=>$value) if (substr($key,0,5) == 'limit') echo "$key: $value" . (strstr(strtolower($key),'rate') && !strstr(strtolower($key),'disconnect') ?'bytes = '. self::humanSize(8 * $value) . 'bits':'') . '<br>' ;


							echo '<h5>All Parameters</h5><small>';
							foreach ($sessionsVars as $key=>$value) echo "$key: $value" . (strstr(strtolower($key),'rate') && !strstr(strtolower($key),'disconnect') ?' = '. self::humanSize(8 * $value):'') . '; ' ;
							echo '</small>';
							}
				}
			else echo '+ Warning: RTMP Session Control info was not detected. Without this broadcasts external to VideoWhisper apps will not show online and will not generate snapshots. Also all transcoding and thumb generation processes will have longer latency.';

			if ($options['transcoding'])
			{
				$processUser = get_current_user();
				$processUid = getmyuid();

				echo "<h4>FFmpeg</h4> + FFMPEG transcoding and snapshot retrieval processes currently run by account '$processUser' (#$processUid). Transcoding starts some time after stream is published for VideoWhisper web apps or when RTMP Session Control is enabled.<BR>";

				$cmd = "ps aux | grep 'ffmpeg'";
				exec($cmd, $output, $returnvalue);
				//var_dump($output);

				$transcoders = 0;
				foreach ($output as $line) if (strstr($line, "ffmpeg"))
					{
						$columns = preg_split('/\s+/',$line);
						if (($processUser == $columns[0] || $processUid == $columns[0]) && (!in_array($columns[10],array('sh','grep'))))
						{

							echo " - Process #".$columns[1]." CPU: ".$columns[2]." Mem: ".$columns[3].' Start: '.$columns[8].' CPU Time: '.$columns[9]. ' Cmd: ';
							for ($n=10; $n<24; $n++) echo $columns[$n].' ';

							if ($_GET['kill']== $columns[1])
							{
								$kcmd = 'kill -KILL ' . $columns[1];
								exec($kcmd, $koutput, $kreturnvalue);
								echo ' <B>Killing process...</B>';
							}
							else echo ' <a href="admin.php?page=live-streaming&tab=hls&kill='.$columns[1].'">Kill</a>';

							echo '<br>';
							$transcoders++;
						}
					}

				if (!$transcoders) echo 'No live transcoding/snapshot processes detected.';
				else echo '<BR>Total processes for transcoding/snapshot: ' . $transcoders;
			}

			//start with a cleanup for viewers and broadcasters
			self::cleanSessions(0);
			self::cleanSessions(1);


			//list channels

			$typeLabels = array(1 => 'Flash',2 =>  'External',3 =>  'WebRTC');

			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_sessions";
			$table_viewers = $wpdb->prefix . "vw_lwsessions";
			$table_channels = $wpdb->prefix . "vw_lsrooms";

			$items =  $wpdb->get_results("SELECT * FROM `$table_channels` ORDER BY edate DESC LIMIT 0, 200");

			echo "<h4>Channel Activity</h4> <table class='wp-list-table widefat'><thead><tr><th>Channel</th><th>Last Access</th><th>Broadcast Time</th><th>Watch Time</th><th>Last Reset</th><th>Type</th><th>Logs</th></tr></thead>";


			if ($items) foreach ($items as $item)
				{
					echo "<tr class='alternate'><th>".$item->name;

					if ($_GET['regenerateThumbs'])
					{
						//
						$stream=$item->name;
						$filename = "$dir/$stream.jpg";

						if (file_exists($filename))
						{
							//generate thumb
							$thumbWidth = $options['thumbWidth'];
							$thumbHeight = $options['thumbHeight'];

							$src = imagecreatefromjpeg($filename);
							list($width, $height) = getimagesize($filename);
							$tmp = imagecreatetruecolor($thumbWidth, $thumbHeight);

							$dir = $options['uploadsPath']. "/_thumbs";
							if (!file_exists($dir)) mkdir($dir);

							$thumbFilename = "$dir/$stream.jpg";
							imagecopyresampled($tmp, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
							imagejpeg($tmp, $thumbFilename, 95);

							$sql="UPDATE `$table_channels` set status='1' WHERE name ='$stream'";
							$wpdb->query($sql);


						} else
						{
							echo "<div class='warning'>Snapshot missing!</div>";
							$sql="UPDATE `$table_channels` set status='0' WHERE name ='$stream'";
							$wpdb->query($sql);

						}
					}

					global $wpdb;
					$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $item->name . "' and post_type='channel' LIMIT 0,1" );

					if (!$options['anyChannels'] && !$options['userChannels'])
					{
						if (!$postID)
						{
							$wpdb->query( "DELETE FROM `$table_channels` WHERE name ='".$item->name."'");
							echo "<br>DELETED: No channel post.";
						}
					}

					if ($postID) echo ' <A target="_viewchannel" href="'. get_permalink( $postID).'">View</A>';

					if ($item->type >=2) //premium
						{
						$poptions = VWliveStreaming::channelOptions($item->type, $options);

						$maximumBroadcastTime =  60 * $poptions['pBroadcastTime'];
						$maximumWatchTime =  60 * $poptions['pWatchTime'];

						$canWatch = $poptions['canWatchPremium'];
						$watchList = $poptions['watchListPremium'];
					}
					else
					{
						$maximumBroadcastTime =  60 * $options['broadcastTime'];
						$maximumWatchTime =  60 * $options['watchTime'];

						$canWatch = $options['canWatch'];
						$watchList = $options['watchList'];
					}

					if (($item->wtime > $maximumWatchTime && $maximumWatchTime) || ($item->btime > $maximumBroadcastTime && $maximumBroadcastTime)) $warnCode = 'Warning: Channel '.$item->name.' consumed allocated time!';
					else $warnCode = '';

					echo "</th><td>". VWliveStreaming::format_age(time() - $item->edate)."</td><td>". VWliveStreaming::format_time($item->btime) . ' / ' . ($maximumBroadcastTime?VWliveStreaming::format_time($maximumBroadcastTime):'unlimited') . "</td><td>". VWliveStreaming::format_time($item->wtime) . ' / ' . ($maximumWatchTime?VWliveStreaming::format_time($maximumWatchTime):'unlimited') ."</td><td>" . VWliveStreaming::format_age(time() - $item->rdate)."</td><td>".($item->type>1?"Premium " . ($item->type-1) :"Standard")."</td>";

					//channel text logs
					$upload_c = VWliveStreaming::getDirectorySize($options['uploadsPath'] . '/'.$item->name);
					$upload_size = VWliveStreaming::sizeFormat($upload_c['size']);
					$logsurl = VWliveStreaming::path2url($options['uploadsPath'] . '/'.$item->name);

					echo '<td>'."<a target='_blank' href='$logsurl'>$upload_size ($upload_c[count] files)</a>".'</td></tr>';
					if ($warnCode) echo '<tr><td colspan="7">' . $warnCode . '</td></tr>';


					$broadcasting = $wpdb->get_results("SELECT * FROM `$table_sessions` WHERE room = '".$item->name."' ORDER BY edate DESC LIMIT 0, 100");
					if ($broadcasting)
						foreach ($broadcasting as $broadcaster)
						{
							$typeLabel = $broadcaster->type;
							if (array_key_exists($broadcaster->type , $typeLabels)) $typeLabel = $typeLabels[$broadcaster->type ];




							echo "<tr><td colspan='7'> - " . $broadcaster->username . " Session Type: " . $typeLabel . " Status: " . $broadcaster->status . " Started: " . VWliveStreaming::format_age(time() -$broadcaster->sdate). "  Broadcaster updated: " . VWliveStreaming::format_age(time() -$broadcaster->edate). "</td></tr>";
						}

					if ($postID)
					{
						$videoCodec = get_post_meta($postID, 'stream-codec-video', true);
						if (!$videoCodec) $videoCodec ='';

						$streamProtocol = get_post_meta($postID, 'stream-protocol', true);
						if (!$streamProtocol) $streamProtocol ='';

						$codecDetection = get_post_meta($postID, 'stream-codec-detect', true);
						$codecAge = 'Never';
						if ($codecDetection) $codecAge = VWliveStreaming::format_age(time() - $codecDetection);

						$streamUpdated = get_post_meta($postID, 'stream-updated', true);
						if ($streamUpdated) $updatedAge = VWliveStreaming::format_age(time() - $streamUpdated);


						if ($videoCodec || $streamProtocol)
						{
							echo '<tr><td colspan="7">';
							if ($videoCodec) echo 'Video Codec: ' . $videoCodec . ' Audio Codec: ' . get_post_meta($postID, 'stream-codec-audio', true) .' Detected: ' . $codecAge . ' HLS: ' . get_post_meta($postID, 'stream-hls', true);
							if ($webRTCmode = get_post_meta($postID, 'stream-mode', true)  ) echo ' WebRTC: ' . $webRTCmode;

							if ($streamProtocol) echo ' Protocol: '. $streamProtocol  . ' Type: ' . get_post_meta($postID, 'stream-type', true) . ' Broadcast session updated: '. $updatedAge ;
							echo ' </td></tr>';
						}
					}

					//

				}
			echo "</table>";
?>
<p>This page shows latest accessed channels (maximum 200).</p>

<p>+ External players and encoders (if enabled) are not monitored or controlled by this plugin, unless special <a href="https://videowhisper.com/?p=RTMP-Session-Control">rtmp side session control</a> is available.</p>

<p>+  Configure streaming time limitations from these sections:
	<br> - <a href="admin.php?page=live-streaming&tab=broadcaster">Broadcaster Settings</a>
	<br> - <a href="admin.php?page=live-streaming&tab=premium">Membership Levels</a>
</p>

                <?php

			//channel text logs
			$upload_c = VWliveStreaming::getDirectorySize($options['uploadsPath'] );
			$upload_size = VWliveStreaming::sizeFormat($upload_c['size']);
			$logsurl = VWliveStreaming::path2url($options['uploadsPath']);

			echo '<p> + Total temporary file usage (logs, snapshots, session info): '." <a target='_blank' href='$logsurl'>$upload_size (in $upload_c[count] files and $upload_c[dircount] folders)</a>".'</p>';

		}

		function adminLive()
		{
			$options = get_option('VWliveStreamingOptions');

			$ban = sanitize_file_name($_GET['ban']);

			if ($ban)
			{
?>
<h3>Banning Channel</h3>
<?php
				global $wpdb;

				//delete post
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $ban . "' and post_type='channel' LIMIT 0,1" );
				if (!$postID) echo "<br>Channel post '$ban' not found!";
				else
				{
					wp_delete_post($postID, true);
					echo "<br>Channel post '$ban' was deleted.";
				}

				//delete room
				$table_sessions = $wpdb->prefix . "vw_lsrooms";
				$sql="DELETE FROM `$table_sessions` WHERE name = '$ban'";
				$wpdb->query($sql);
				echo "<br>Channel room '$ban' was deleted.";

				//ban
				$options['bannedNames'] .= ($options['bannedNames']?',':'') . $ban;
				update_option('VWliveStreamingOptions', $options);
				echo '<br>Current ban list: ' . $options['bannedNames'] . ' <a href="admin.php?page=live-streaming&tab=broadcaster" class="button button-primary">Edit</a>';
			}

			//broadcast link if allowed by settings
			if ($options['userChannels']||$options['anyChannels'])
			{

				$root_url = get_bloginfo( "url" ) . "/";
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

				$current_user = wp_get_current_user();

				if ($current_user->$userName) $username = $current_user->$userName;
				$username = sanitize_file_name($username);

				$broadcast_url = admin_url() . 'admin-ajax.php?action=vwls_broadcast&n=';

?>

<h3>Channel '<?php echo $username; ?>': Go Live</h3>
<ul>
<li>
<a href="<?php echo $broadcast_url . urlencode($username); ?>"><img src="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_webcam.png"
align="absmiddle" border="0">Start Broadcasting</a>
</li>
<li>
<a href="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/channel.php?n=<?php echo $username; ?>"><img src="<?php echo $root_url;
				?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_uvideo.png" align="absmiddle" border="0">View Channel</a>
</li>
</ul>
<p>To allow users to broadcast from frontend (as configured in settings), <a href='widgets.php'>enable the widget</a> and/or channel posts and frontend management page.
<br>On some templates/setups you also need to add the page to site menu.
</p>
<?php
			}
?>
<h3>Recent Channels</h3>
<?php

			echo do_shortcode('[videowhisper_channels ban="1" per_page="24"]');

		}

		function adminDocs()
		{
?>
<h2>Broadcast Live Video - Live Streaming by VideoWhisper.com</h2>

This solution involves special streaming hosting services for live streaming and interactions: See hosting requirements and option pages from <a href="admin.php?page=live-streaming&tab=support">Support Resources</a> section.

<h3>Quick Setup Tutorial</h3>
<ol>
<li>Install and activate the VideoWhisper Broadcast Live Video - Live Streaming Integration plugin from WP backend. </li>
<li>From <a href="admin.php?page=live-streaming&tab=import">Live Streaming > Settings : Import</a> import streaming server settings as provided by VideoWhisper or manually edit settings in appropriate sections.</li>
<li>From <a href="options-permalink.php">Settings > Permalinks</a> enable a SEO friendly structure (ex. Post name)</li>
<li>From <a href="nav-menus.php">Appearance > Menus</a> add Channels and Broadcast Live pages to main site menu.
</li>
<li>Optional: Install and enable a <a href="admin.php?page=live-streaming&tab=billing">billing plugin</a> to allow owners to sell channel access</li>
<li>Optional: Install and enable the <a href="https://videosharevod.com/">VideoShareVOD</a> plugin to enable video broadcast archive import, video publishing, management.</li>
<li>Setup <a href="edit-tags.php?taxonomy=category&post_type=channel">channel categories</a>, common to site content.</li>

<li><a href="https://broadcastlivevideo.com/customize">Customize</a>
</ol>


<h3>BroadcastLiveVideo Installation URLs</h3>

	- Users can setup their channels and start broadcast from Broadcast Live page:
	<br><?php echo get_permalink(get_option("vwls_page_manage"))?>
	<br>Try broadcasting as described at https://broadcastlivevideo.com/broadcast-html5-webrtc-to-mobile-hls/ and https://broadcastlivevideo.com/broadcast-with-obs-or-other-external-encoder/ .
	<br>
	<br>- After broadcasting, channels show in Channels list:
	<br><?php echo get_permalink(get_option("vwls_page_channels"))?>
	<br>
	<br>- Configure your site logos (after uploading):
	<br><?php echo admin_url('admin.php?page=live-streaming&tab=appearance')?>

	<br>- Customize further as described at:
	<br>https://broadcastlivevideo.com/customize

	<br>- Contact VideoWhisper for clarifications or custom development:
	<br>https://videowhisper.com/tickets_submit.php
	<br>

<br>- To prevent spam registrations use a captcha plugin (get a key from google) and user verification plugin to automatically verify users by email confirmation. 
<br>https://wordpress.org/plugins/search/captcha/
<br>https://www.google.com/recaptcha/admin/create#list
<br>https://wordpress.org/plugins/search/user-verification/
<br>

<br>- Also setup a special email account from CPanel and a SMTP plugin to make sure users receive notification emails.
<br>Use a WP SMTP mailing plugin and setup a real email account from your hosting backend (setup an email from CPanel) or external (Gmail or other provider), to send emails using SSL and all verifications. This should reduce incidents where users don’t find registration emails due to spam filter triggering. Also instruct users to check their spam folders if they don’t find registration emails.
<br>https://wordpress.org/plugins/search/smtp/


<h3>Customize with Premium Plugins / Addons</h3>
<ul>
	<LI><a href="http://themeforest.net/popular_item/by_category?category=wordpress&ref=videowhisper">Premium Themes</a> Professional WordPress themes.</LI>
	<LI><a href="https://woocommerce.com/?aff=18336&cid=1980980">WooCommerce</a> Free shopping cart plugin, supports multiple free and premium gateways with TeraWallet/WooWallet plugin and various premium eCommerce plugins.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-memberships/?aff=18336&cid=1980980">WooCommerce Memberships</a> Setup paid membership as products. Leveraged with Subscriptions plugin allows membership subscriptions.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-subscriptions/?aff=18336&cid=1980980">WooCommerce Subscriptions</a> Setup subscription products, content. Leverages Membership plugin to setup membership subscriptions.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-bookings/?aff=18336&cid=1980980">WooCommerce Bookings</a> Let your customers book reservations, appointments on their own.</LI>

	<LI><a href="https://woocommerce.com/products/follow-up-emails/?aff=18336&cid=1980980">WooCommerce Follow Up</a> Follow Up by emails and twitter automatically, drip campaigns.</LI>

	<LI><a href="https://updraftplus.com/?afref=924">Updraft Plus</a> Automated WordPress backup plugin. Free for local storage. For production sites external backups are recommended (premium).</LI>
</ul>


<h3>ShortCodes</h3>
<ul>
  <li><h4>[videowhisper_watch channel=&quot;Channel Name&quot; width=&quot;100%&quot; height=&quot;100%&quot; flash=&quot;0&quot;]</h4>
    Displays watch interface with video and discussion. If iOS is detected it shows HLS instead. Container style can be configured from plugin settings. Auto detection unless flash="1" forced.</li>

  <li><h4>[videowhisper_htmlchat_playback channel=&quot;Channel Name&quot; 	post_id=&quot;Channel Post ID&quot; videowidth=&quot;480px&quot; videoheight=&quot;360px&quot;]</h4>
	  Displays html chat with HTML5 live stream.</li>

  <li><h4>[videowhisper_video channel=&quot;Channel Name&quot; width=&quot;480px&quot; height=&quot;360px&quot; html5=&quot;auto&quot;]</h4>
  Displays video only interface. Depending on device and settings will show HTML5. Set html5=&quot;always&quot; to force html5 video.</li>

  <li><h4>[videowhisper_hls channel=&quot;Channel Name&quot; width=&quot;480px&quot; height=&quot;360px&quot;]</h4>
  Displays HTML5 HLS (HTTP Live Streaming) video interface. Shows istead of watch and video interfaces if iOS is detected. Stream must be published in compatible format (H264,AAC) or transcoding must be enabled and active for stream to show.</li>

 <li><h4>[videowhisper_mpeg channel=&quot;Channel Name&quot; width=&quot;480px&quot; height=&quot;360px&quot;]</h4>
  Displays HTML5 MPEG DASH video interface. Shows instead of watch and video interfaces if Android is detected. Stream must be published in compatible format (H264,AAC) or transcoding must be enabled and active for stream to show.</li>

  <li><h4>[videowhisper_webrtc_playback channel=&quot;Channel Name&quot; width=&quot;480px&quot; height=&quot;360px&quot;]</h4>
  Displays WebRTC video playback interface.</li>

  <li>
    <h4>[videowhisper_broadcast channel=&quot;Channel Name&quot; flash=&quot;0&quot;]</h4>
    Shows broadcasting interface. If not provided, channel name is detected depending on settings, post type, user. Only owner can access for channel posts. Auto detection unless flash="1" forced.
   </li



  <li>
    <h4>[videowhisper_channel_user]</h4>
	Displays broadcasting interface for a channel with same name as user. Creates channel automatically if not existing. For single channel per user setups.
   </li>

  <li>
    <h4>[videowhisper_webrtc_broadcast channel=&quot;Channel Name&quot;]</h4>
    Shows WebRTC broadcasting interface. If not provided, channel name is detected depending on settings, post type, user. Only owner can access for channel posts.
   </li>

    <li>
    <h4>[videowhisper_external channel=&quot;Channel Name&quot;] [videowhisper_external_broadcast channel=&quot;Channel Name&quot;][videowhisper_external_playback channel=&quot;Channel Name&quot;]</h4>
    Shows settings for broadcasting/playback with external applications. Channel name is detected depending on settings, post type, user. Only owner can access for channel posts.
   </li>
     <li>
	     <h4>[videowhisper_channels per_page="8" perrow="" order_by="edate" category_id="" select_category="1" 'select_tags="1" select_name="1" select_order="1" select_page="1" include_css="1" ban="0" id=""]</h4>
	     Lists channels with snapshots, ordered by most recent online and with pagination.
     </li>
     <li>
	     <h4>[videowhisper_livesnapshots]</h4>
	     Older shortcode for backward compatibility. Displays full size snapshots of online channels. No pagination.
     </li>
     <li>
     <h4>
     [videowhisper_channel_manage]
     </h4>
	     Displays channel management page.
     </li>
</ul>
<h3>Documentation, Support, Customizations</h3>
<ul>
<li>Home Page and Documentation: <a href="https://videowhisper.com/?p=WordPress+Live+Streaming">VideoWhisper - WordPress Live Streaming</a></li>
<li>WordPress Plugin Page: <a href="https://wordpress.org/plugins/videowhisper-live-streaming-integration/">VideoWhisper Live Streaming Integration</a></li>
<li>Contact Page: <a href="https://videowhisper.com/tickets_submit.php">Contact VideoWhisper</a></li>
</ul>
<p>After ordering solution and setting up existing editions, VideoWhisper.com developers can customize these for additional fees depending on exact requirements.</p>
  <?php
		}


		//! Channel Features List

		static function roomFeatures()
		{
			return array(
				'roomTags' => array(
					'name'=>'Room Tags',
					'description' =>'Can specify room tags.',
					'installed' => 1,
					'default' => 'All'),
				'accessPassword' => array(
					'name'=>'Access Password',
					'description' =>'Can specify a password to protect channel access.',
					'installed' => 1,
					'default' => 'Super Admin, Administrator, Editor'),
				'accessList' => array(
					'name'=>'Access List',
					'description' =>'Channel owner can specify list of user logins, roles, emails that can access the channel.',
					'installed' => 1,
					'default' => 'None'),
				'accessPrice' => array(
					'name'=>'Access Price',
					'description' =>'Can setup a price per channel. Requires myCRED plugin installed and integration enabled from Billing.',
					'type' => 'number',
					'installed' => 1,
					'default' => 'None'),
				'chatList' => array(
					'name'=>'Chat List',
					'description' =>'Channel owner can specify list of user logins, roles, emails that can access the public chat.',
					'installed' => 1,
					'default' => 'None'),
				'writeList' => array(
					'name'=>'Chat Write List',
					'description' =>'Channel owner can specify list of user logins, roles, emails that can write in public chat.',
					'installed' => 1,
					'default' => 'None'),
				'participantsList' => array(
					'name'=>'Participants List',
					'description' =>'Channel owner can specify list of user logins, roles, emails that can view participants list.',
					'installed' => 1,
					'default' => 'None'),
				'privateChatList' => array(
					'name'=>'Private Chat List',
					'description' =>'Channel owner can specify list of user logins, roles, emails that can initiate private chat.',
					'installed' => 1,
					'default' => 'None'),
				'uploadPicture' => array(
					'name'=>'Upload Picture',
					'description' =>'Upload channel picture.',
					'installed' => 1,
					'default' => 'Super Admin, Administrator, Editor, Subscriber'),
				'eventDetails' => array(
					'name'=>'Event Details',
					'description' =>'Specify event title, start, end, description to show when show is offline.',
					'installed' => 1,
					'default' => 'Super Admin, Administrator, Editor, Subscriber'),
				'logoHide' => array(
					'name'=>'Hide Logo',
					'description' =>'Hides logo from channel.',
					'installed' => 1,
					'default' => 'Super Admin, Administrator, Editor'),
				'logoCustom' => array(
					'name'=>'Custom Logo',
					'description' =>'Can setup a custom logo. Overrides hide logo feature.',
					'installed' => 1,
					'default' => 'Super Admin, Administrator'),
				'adsHide' => array(
					'name'=>'Hide Ads',
					'description' =>'Hides ads from channel.',
					'installed' => 1,
					'default' => 'Super Admin, Administrator, Editor'),
				'ipCameras' => array(
					'name'=>'IP Cameras',
					'description' =>'Can configure re-streaming, including for IP cameras.',
					'installed' => 1,
					'default' => 'None'),
				'schedulePlaylists' => array(
					'name'=>'Playlist Scheduler',
					'description' =>'Can schedule channel playlist from VideoShareVOD videos.',
					'installed' => 1,
					'default' => 'None'),
				'adsCustom' => array(
					'name'=>'Custom Ads',
					'description' =>'Can setup a custom ad server. Overrides hide ads feature.',
					'installed' => 1,
					'default' => 'None'),
				'transcode' => array(
					'name'=>'Transcode',
					'description' =>'Enable transcoding for user channels.',
					'installed' => 1,
					'default' => 'Super Admin, Administrator, Editor'),
				'privateList' => array(
					'name'=>'Private Channels',
					'description' =>'Hide channels from public listings. Can be accessed by channel links.',
					'installed' => 0),
				'privateChat' => array(
					'name'=>'Private Chat',
					'description' =>'Disable chat from site watch interface.',
					'installed' => 0),
				'privateVideos' => array(
					'name'=>'Private Videos',
					'description' =>'Channel videos do not show in public listings. Only show on channel page.',
					'installed' => 0),
				'hiddenVideos' => array(
					'name'=>'Hidden Videos',
					'description' =>'Channel videos do not show in public or channel listings. Only owner can browse.',
					'installed' => 0),
			);
		}


		//! App Calls / integration, auxiliary

		static function editParameters($default = '', $update = array(), $remove = array())
		{
			//adjust parameters string by update(add)/remove

			parse_str(substr($default,1), $params);

			//remove

			if (count($update)) foreach ($params as $key => $value)
					if (in_array($key, $update)) unset($params[$key]);

					if (count($remove)) foreach ($params as $key => $value)
							if (in_array($key, $remove)) unset($params[$key]);


							//add updated
							if (count($update)) foreach ($update as $key => $value) $params[$key] = $value;

								return '&' . http_build_query($params);
		}



		static function webSessionSave($username, $canKick=0, $debug = '0', $ip = '')
		{
			//generates a session file record for rtmp login check

			$username = sanitize_file_name($username);

			if ($username)
			{
				$options = get_option('VWliveStreamingOptions');
				$webKey = $options['webKey'];
				$ztime = time();

				$ztime=time();
				$info = 'VideoWhisper=1&login=1&webKey='. urlencode($webKey) . '&start=' . $ztime . '&ip=' . urlencode($ip) . '&canKick=' . $canKick . '&debug=' . urlencode($debug);

				$dir=$options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);
				@chmod($dir, 0777);
				$dir.="/_sessions";
				if (!file_exists($dir)) mkdir($dir);
				@chmod($dir, 0777);

				$dfile = fopen($dir."/$username","w");
				fputs($dfile,$info);
				fclose($dfile);
			}

		}

		static function sessionUpdate($username='', $room='', $broadcaster=0, $type=1, $strict=1, $updated=1)
		{
			//update session in mysql

			//type 1=http, 2=rtmp, 3=webrtc
			//strict = create new if not that type
			//updated = return updated session unless missing (otherwise return old for delta calculations)


			if (!$username) return;
			$ztime = time();

			global $wpdb;
			if ($broadcaster) $table_sessions = $wpdb->prefix . "vw_sessions";
			else $table_sessions = $wpdb->prefix . "vw_lwsessions";

			$cnd = '';
			if ($strict) $cnd = " AND `type`='$type'";

			//online broadcasting session
			$sqlS = "SELECT * FROM $table_sessions where session='$username' and status='1' $cnd ORDER BY edate DESC LIMIT 0,1";
			$session = $wpdb->get_row($sqlS);

			if (!$session)
				$sql="INSERT INTO `$table_sessions` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`) VALUES ('$username', '$username', '$room', '', $ztime, $ztime, 1, $type)";
			else $sql="UPDATE `$table_sessions` set edate=$ztime, room='$room', username='$username' where id ='".$session->id."'";
			$wpdb->query($sql);


			if ($broadcaster)
			{
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $room . "' and post_type='channel' LIMIT 0,1" );
				if ($postID) update_post_meta($postID, 'edate', $ztime);
			}

			VWliveStreaming::cleanSessions($broadcaster);

			if ($updated || !$session) $session = $wpdb->get_row($sqlS);

			return $session;
		}

		static function cleanSessions($broadcaster=0)
		{

			$options = get_option('VWliveStreamingOptions');

			if (!VWliveStreaming::timeTo('cleanSessions'.$broadcaster, 25, $options)) return;

			$ztime = time();
			global $wpdb;

			if ($broadcaster) $table_sessions = $wpdb->prefix . "vw_sessions";
			else $table_sessions = $wpdb->prefix . "vw_lwsessions";

			if (!$options['onlineExpiration' . $broadcaster]) $options['onlineExpiration' . $broadcaster] = 310;
			$exptime=$ztime-$options['onlineExpiration' . $broadcaster];
			$sql="DELETE FROM `$table_sessions` WHERE edate < $exptime";
			$wpdb->query($sql);

		}

		static function streamSnapshot($stream, $ipcam = false, $postID = 0)
		{
			$stream = sanitize_file_name($stream);
			if (strstr($stream,'.php')) return;
			if (!$stream) return;

			$options = get_option('VWliveStreamingOptions');

			$dir = $options['uploadsPath'];
			if (!file_exists($dir)) mkdir($dir);
			$dir .= "/_snapshots";
			if (!file_exists($dir)) mkdir($dir);

			if (!file_exists($dir))
			{
				$error = error_get_last();
				echo 'Error - Folder does not exist and could not be created: ' . $dir . ' - '.  $error['message'];
			}

			$filename = "$dir/$stream.jpg";
			if (file_exists($filename)) if (time()-filemtime($filename) < 15) return; //do not update if fresh (15s)

				//get channel id $postID
				global $wpdb;
			if (!$postID) $postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );

			$restreamPaused = get_post_meta($postID, 'restreamPaused', true);
			if ($restreamPaused) return; //no snapshot while paused

			//get primary stream source (rtmp/rtsp)
			$sourceProtocol = get_post_meta($postID, 'stream-protocol', true);
			$streamType = get_post_meta($postID, 'stream-type', true);
			$streamAddress = get_post_meta($postID, 'vw_ipCamera', true);


			$log_file = $filename . '.txt';
			$lastLog = $options['uploadsPath'] . '/lastLog-streamSnapshot.txt';

			if ($streamType == 'restream' && $streamAddress)
			{
				//retrieve from main source
				$cmdP = '';

				$cmdT = '';

				//movie streams start with blank screens
				if (strstr($streamAddress, '.mp4') || strstr($streamAddress, '.mov') || strstr($streamAddress, 'mp4:')) $cmdT = '-ss 00:00:02';

				if ($sourceProtocol == 'rtsp') $cmdP = '-rtsp_transport tcp'; //use tcp for rtsp
				$cmd = $options['ffmpegSnapshotTimeout'] . ' ' . $options['ffmpegPath'] ." -y -frames 1 \"$filename\" $cmdP $cmdT -i \"" . $streamAddress . "\" >& $log_file  " . $options['ffmpegSnapshotBackground'];

			}
			elseif ($sourceProtocol == 'rtsp')
			{
				$streamQuery = self::webrtcStreamQuery($userID, $postID, 0, $stream, $options, 1);

				//usually webrtc
				$cmd = $options['ffmpegSnapshotTimeout'] . ' ' . $options['ffmpegPath'] . " -y  -f image2 -vframes 1 \"$filename\" -i \"" . $options['rtsp_server'] ."/". $streamQuery . "\" >& $log_file " . $options['ffmpegSnapshotBackground'];
			}
			else
			{
				if ($options['externalKeysTranscoder'])
				{
					$keyView = md5('vw' . $options['webKey']. $postID);
					$rtmpAddressView = $options['rtmp_server'] . '?'. urlencode('ffmpegSnap_' . $stream) .'&'. urlencode($stream) .'&'. $keyView . '&0&videowhisper';
				}
				else $rtmpAddressView = $options['rtmp_server'];

				$cmd = $options['ffmpegSnapshotTimeout'] . ' ' . $options['ffmpegPath'] . " -y -f image2 -vframes 1 \"$filename\" -i \"" . $rtmpAddressView ."/". $stream . "\" >& $log_file " . $options['ffmpegSnapshotBackground'];
			}


			//escape
			//$cmd = escapeshellcmd($cmd);

			//echo $cmd;
			exec($cmd, $output, $returnvalue);
			exec("echo 'Command: $cmd Return: $returnvalue Output[0]: " . $output[0] . "'  >> $log_file.cmd", $output, $returnvalue);

			self::varSave($lastLog, [ 'file'=>$log_file, 'cmd' => $cmd, 'return' => $returnvalue, 'output0' => $output[0], 'time' =>time()] );

			//failed
			if (!file_exists($filename)) return;

			//may be old snapshot!!! maybe compare date with edate or store thumb date / check later
			$thumbTime = get_post_meta($postID, 'thumbTime', true);
			$fileTime = filemtime($filename);
			if ($fileTime <= $thumbTime) return; //old file, already processed

			//if snapshot successful (from stream) update edate
			$ztime = time();
			update_post_meta($postID, 'edate', $ztime);
			update_post_meta($postID, 'vw_lastSnapshot', $filename);

			if ($ipcam)
			{
				//also update current number of viewers
				self::updateViewers($postID, $stream, $options);
			}

			//generate thumb
			$thumbWidth = $options['thumbWidth'];
			$thumbHeight = $options['thumbHeight'];

			$src = imagecreatefromjpeg($filename);
			list($width, $height) = getimagesize($filename);
			$tmp = imagecreatetruecolor($thumbWidth, $thumbHeight);

			$dir = $options['uploadsPath']. "/_thumbs";
			if (!file_exists($dir)) mkdir($dir);

			$thumbFilename = "$dir/$stream.jpg";
			imagecopyresampled($tmp, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
			imagejpeg($tmp, $thumbFilename, 95);

			//update room status to 1 or 2
			$table_channels = $wpdb->prefix . "vw_lsrooms";

			//detect tiny images without info
			if (filesize($thumbFilename)>5000) $picType = 1;
			else $picType = 2;

			//table
			$sql="UPDATE `$table_channels` set status='$picType', edate='$ztime' where name ='$stream'";
			$wpdb->query($sql);


			//update post meta
			update_post_meta($postID, 'hasSnapshot', $picType);
			update_post_meta($postID, 'thumbTime', $ztime);

		}

		function rtmpSnapshot($session)
		{

			self::streamSnapshot($session->session);
		}

		function premiumOptions($userkeys, $options)
		{

			$premiumLev = unserialize($options['premiumLevels']);

			if ($options['premiumLevelsNumber'])
				for ($i= ($options['premiumLevelsNumber']-1) ; $i >= 0 ; $i--)
				if ($premiumLev[$i]['premiumList'])
					if (VWliveStreaming::inList($userkeys, $premiumLev[$i]['premiumList'])) return $premiumLev[$i];

					//not found
					return false;
		}

		function channelOptions($type, $options)
		{
			$premiumLev = unserialize($options['premiumLevels']);

			$i = $type-2;
			if ($premiumLev[$i]) return $premiumLev[$i];

			//regular channel
			return $options;
		}

		/*
		function premiumLevel($userkeys, $options)
		{

			$premiumLev = unserialize($options['premiumLevels']);

			if ($options['premiumLevelsNumber'])
				for ($i=$options['premiumLevelsNumber'] - 1 ; $i >= 0 ; $i--)
				if ($premiumLev[$i]['premiumList'])
					if (!VWliveStreaming::inList($userkeys, $premiumLev[$i]['premiumList'])) return ($i+1);

			return 0;
		}
*/

		//! Online user functions
		static function updateViewers($postID, $room, $options)
		{

			if (!$room) $room = 'room_' . $postID;
			if (!self::timeTo($room . '/updateViewers', 59, $options)) return;

			if (!$options) $options = get_option('VWliveStreamingOptions');


			self::cleanSessions(1);

			//update viewers
			$ztime = time();

			global $wpdb;
			$table_viewers = $wpdb->prefix . "vw_lwsessions";
			$viewers =  $wpdb->get_var($sql = "SELECT count(id) AS no FROM `$table_viewers` WHERE status='1' AND rid='" . $postID . "'");

			update_post_meta($postID, 'viewers', $viewers);
			update_post_meta($postID, 'viewersUpdate', $ztime);

			$maxViewers = get_post_meta($postID, 'maxViewers', true);
			if ($viewers >= $maxViewers)
			{
				update_post_meta($postID, 'maxViewers', $viewers);
				update_post_meta($postID, 'maxDate', $ztime);
			}


			$lastLog = $options['uploadsPath'] . '/lastLog-updateViewers.txt';
			self::varSave($lastLog, [ 'sql'=>$sql, 'viewers' => $viewers, 'maxViewers'=>$maxViewers, 'date' => $ztime, 'postID' => $postID, 'room' =>$room] );
		}

		function timeTo($action, $expire = 60, $options='')
		{
			//if $action was already done in last $expire, return false

			if (!$options) $options = get_option('VWliveStreamingOptions');

			$cleanNow = false;


			$ztime = time();

			$lastClean = 0;
			$lastCleanFile = $options['uploadsPath'] . '/' . $action . '.txt';

			if (!file_exists($options['uploadsPath'])) mkdir($options['uploadsPath']);

			if (!file_exists($dir = dirname($lastCleanFile))) mkdir($dir);
			elseif (file_exists($lastCleanFile)) $lastClean = file_get_contents($lastCleanFile);

			if (!$lastClean) $cleanNow = true;
			else if ($ztime - $lastClean > $expire) $cleanNow = true;

				if ($cleanNow)
					file_put_contents($lastCleanFile, $ztime);


				return $cleanNow;

		}



		static function userWatchLimit($user, $options)
		{
			$userLimit = $options['userWatchLimitDefault'];

			if (is_array($options['userWatchLimits']))
				foreach ($options['userWatchLimits'] as $role => $limit)
					if (in_array(strtolower($role), $user->roles))
					{
						if (!$limit) //unlimited
							{
							$userLimit = 0;
							break; //no more search
						}

						if ($limit > $userLimit) $userLimit = $limit; //upgrade limit (best applies)

					}

				return $userLimit;
		}

		static function updateUserWatchtime($user, $dS, $options)
		{
			if (!$user) return;
			if (!$user->ID) return;

			if (!$options) $options = get_option('VWliveStreamingOptions');

			//update watch time
			//check if new interval
			$lastUpdate = get_user_meta( $user->ID, 'vwls_watch_update', true );

			if ($lastUpdate < time() - $options['userWatchInterval']) //older that interval refresh
				{
				update_user_meta($user->ID, 'vwls_watch_update', time());
				update_user_meta($user->ID, 'vwls_watch', $dS);
				$currentWatch = $dS;

			}else
			{
				$currentWatch = get_user_meta( $user->ID, 'vwls_watch', true );
				$currentWatch += $dS;
				update_user_meta($user->ID, 'vwls_watch', $currentWatch);
			}

			$userLimit = VWliveStreaming::userWatchLimit($user, $options);

			if (!$userLimit) return; //unlimited

			if ($currentWatch > $userLimit) return 1; //return 1 if exceeded

			return; //limit not reached


		}


		static function userParameters($user, $config)
		{
			if (!$user) return;
			if (!$user->ID) return;

			$parameters = array();

			if (is_array($config))
				foreach ($config as $parameter => $roleValue)
					foreach ($roleValue as $role => $value)
						if (in_array(strtolower($role), $user->roles)) $parameters[$parameter] = $value;

						return $parameters;

		}

		static function rexit($output)
		{
			echo $output;
			exit;
		}

		/**
		 * Retrieves the best guess of the client's actual IP address.
		 * Takes into account numerous HTTP proxy headers due to variations
		 * in how different ISPs handle IP addresses in headers between hops.
		 */
		function get_ip_address() {
			$ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
			foreach ($ip_keys as $key) {
				if (array_key_exists($key, $_SERVER) === true) {
					foreach (explode(',', $_SERVER[$key]) as $ip) {
						// trim for safety measures
						$ip = trim($ip);
						// attempt to validate IP
						if (VWliveStreaming::validate_ip($ip)) {
							return $ip;
						}
					}
				}
			}
			return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
		}

		/**
		 * Ensures an ip address is both a valid IP and does not fall within
		 * a private network range.
		 */
		static function validate_ip($ip)
		{
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
				return false;
			}
			return true;
		}


		static function currentUserSession($room)
		{

			if (!is_user_logged_in()) return 0;
			if (!$room) return 0;
			if ($room == 'null') return 0;

			global $current_user;
			get_currentuserinfo();

			$options = get_option('VWliveStreamingOptions');

			$userfield = $options['userName'];
			$username1 = $current_user->$userfield;

			global $wpdb;
			$table_sessions = $wpdb->prefix . "vw_vwls_sessions";

			$sql = "SELECT * FROM `$table_sessions` WHERE session='$username1' AND room='$room' AND status='1' LIMIT 1";
			$session = $wpdb->get_row($sql);

			return $session;

		}

		//! Ajax App Calls

		static function vwls_calls()
		{
			function sanV(&$var, $file=1, $html=1, $mysql=1) //sanitize variable depending on use
				{
				if (!$var) return;

				if (get_magic_quotes_gpc()) $var = stripslashes($var);

				if ($file) $var = sanitize_file_name($var);

				if ($html&&!$file)
				{
					$var=strip_tags($var);
				}

				if ($mysql&&!$file)
				{
					$forbidden=array("'", "\"", "´", "`", "\\", "%");
					foreach ($forbidden as $search)  $var=str_replace($search,"",$var);

					$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
					$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
					$var = str_replace($search, $replace, $var);

				}
			}

			global $wpdb;
			global $current_user;

			ob_clean();

			switch ($_GET['task'])
			{
				//! vw_snapshots
			case 'vw_snapshots':
				$options = get_option('VWliveStreamingOptions');

				$dir=$options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);
				$dir .= "/_snapshots";
				if (!file_exists($dir)) mkdir($dir);

				//get jpg bytearray
				$jpg = $GLOBALS["HTTP_RAW_POST_DATA"];
				if (!$jpg) $jpg = file_get_contents("php://input");

				if ($jpg)
				{
					$stream = $_GET['name'];
					sanV($stream);
					if (strstr($stream,'.php')) exit;
					if (!$stream) exit;

					// save file
					$filename = "$dir/$stream.jpg";
					$fp=fopen($filename ,"w");
					if ($fp)
					{
						fwrite($fp,$jpg);
						fclose($fp);
					}

					//generate thumb
					$thumbWidth = $options['thumbWidth'];
					$thumbHeight = $options['thumbHeight'];

					$src = imagecreatefromjpeg($filename);
					list($width, $height) = getimagesize($filename);
					$tmp = imagecreatetruecolor($thumbWidth, $thumbHeight);

					$dir = $options['uploadsPath']. "/_thumbs";
					if (!file_exists($dir)) mkdir($dir);

					$thumbFilename = "$dir/$stream.jpg";
					imagecopyresampled($tmp, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
					imagejpeg($tmp, $thumbFilename, 95);

					//update room status to 1 or 2
					$table_channels = $wpdb->prefix . "vw_lsrooms";

					//detect tiny images without info
					$snapSize = filesize($thumbFilename);
					if ($snapSize>2000) $picType = 1;
					else $picType = 2;

					$ztime = time();

					$sql="UPDATE `$table_channels` set status='$picType', edate='$ztime' where name ='$stream'";
					$wpdb->query($sql);

					//update post meta
					$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($stream) . "' and post_type='channel' LIMIT 0,1" );
					if ($postID)
					{
						update_post_meta($postID, 'hasSnapshot', $picType);
						update_post_meta($postID, 'edate', $ztime);

					}

				}else echo 'missingJpgData=1&';

				?>loadstatus=1&snapSize=<?php echo urlencode($snapSize);
				break;

				//! lb_logout
			case 'lb_logout':
				wp_redirect( get_home_url() .'?msg='. urlencode($_GET['message']) );
				break;

				//! vw_logout
			case 'vw_logout':
				?>loggedout=1<?php
				break;

				//! vw_extregister
			case 'vw_extregister':

				$options = get_option('VWliveStreamingOptions');

				$user_name = base64_decode($_GET['u']);
				$password =  base64_decode($_GET['p']);
				$user_email = base64_decode($_GET['e']);
				if (!$_GET['videowhisper']) exit;

				$msg = '';

				$user_name = sanitize_file_name($user_name);

				$loggedin=0;
				if (username_exists($user_name)) $msg .= __('Username is not available. Choose another!');
				if (email_exists($user_email)) $msg .= __('Email is already registered.');

				if (!is_email( $user_email )) $msg .= __('Email is not valid.');


				if ($msg=='' && $user_name && $user_email && $password)
				{
					$user_id = wp_create_user( $user_name, $password, $user_email );
					$loggedin = 1;

					//create channel
					$post = array(
						'post_content'   => sanitize_text_field($_POST['description']),
						'post_name'      => $user_name,
						'post_title'     => $user_name,
						'post_author'    => $user_id,
						'post_type'      => $options['custom_post'],
						'post_status'    => 'publish',
					);

					$postID = wp_insert_post($post);

					$msg .= __('Username and channel created', 'live-streaming'). ': ' . $user_name ;
				} else $msg .= __('Could not register account.', 'live-streaming');

				?>firstParameter=fix&msg=<?php echo urlencode($msg); ?>&loggedin=<?php echo $loggedin;?><?php

				break;

				//! vw_extlogin
			case 'vw_extlogin':

				//external login GET u=user, p=password

				$options = get_option('VWliveStreamingOptions');
				$rtmp_server = $options['rtmp_server'];
				$rtmp_amf = $options['rtmp_amf'];
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

				$camRes = explode('x',$options['camResolutionMobile']);

				$canBroadcast = $options['canBroadcast'];
				$broadcastList = $options['broadcastList'];

				$tokenKey = $options['tokenKey'];
				$webKey = $options['webKey'];

				$loggedin=0;
				$msg="";

				$creds = array();
				$creds['user_login'] = base64_decode($_GET['u']);
				$creds['user_password'] = base64_decode($_GET['p']);
				$creds['remember'] = true;
				if (!$_GET['videowhisper']) exit;


				remove_all_actions('wp_login'); //disable redirects or other output
				$current_user = wp_signon( $creds, false );

				if( is_wp_error($current_user))
				{
					$msg = urlencode($current_user->get_error_message()) ;
					$debug = $msg;
				}
				else
				{
					//logged in
				}

				$current_user = wp_get_current_user();


				//username
				if ($current_user->$userName) $username=urlencode($current_user->$userName);
				sanV($username);


				if ($username)
				{
					switch ($canBroadcast)
					{

					case "members":
						$loggedin=1;
						break;

					case "list";
						if (VWliveStreaming::inList($username, $broadcastList)) $loggedin=1;
						else $msg .= urlencode('<a href="' . wp_login_url() . '">' . __('You are not in the broadcasters list', 'ppv-live-webcams') . '.</a> - ' . $username);
						break;
					}

				}else $msg .= urlencode("Login required to broadcast.");

				if ($loggedin)
				{

					$args = array(
						'author'           => $current_user->ID,
						'orderby'          => 'post_date',
						'order'            => 'DESC',
						'post_type'        => $options['custom_post'],
						'posts_per_page'   => 20,
						'offset'           => 0,
					);

					$channels = get_posts( $args );
					if (count($channels))
					{

						foreach ($channels as $channel)
						{
							$username = $room = sanitize_file_name(get_the_title($channel->ID));
							$rtmp_server = VWliveStreaming::rtmp_address($current_user->ID, $channel->ID, true, $room, $room);
							break;
						}

						$canKick = 1;
						$clientIP = VWliveStreaming::get_ip_address();
						VWliveStreaming::webSessionSave($username, $canKick, 'vw_extlogin', $clientIP);
						VWliveStreaming::sessionUpdate($username, $room, 1, 2, 1);
					}
					else
					{
						$msg .= urlencode("You do not have a channel to broadcast.");
						$loggedin = 0;
					}


				}



				?>firstParameter=fix&server=<?php echo urlencode($rtmp_server); ?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&room=<?php echo $room?>&welcome=Welcome!&username=<?php echo $username?>&userlabel=<?php echo $userlabel?>&overLogo=<?php echo urlencode($options['overLogo'])?>&overLink=<?php echo urlencode($options['overLink'])?>&camWidth=<?php echo $camRes[0];?>&camHeight=<?php echo $camRes[1];?>&camFPS=<?php echo
				$options['camFPSMobile']?>&camBandwidth=<?php echo $options['camBandwidthMobile']?>&videoCodec=<?php echo $options['videoCodecMobile']?>&codecProfile=<?php echo $options['codecProfileMobile']?>&codecLevel=<?php echo
				$options['codecLevelMobile']?>&soundCodec=<?php echo $options['soundCodecMobile']?>&soundQuality=<?php echo $options['soundQualityMobile']?>&micRate=<?php echo
				$options['micRateMobile']?>&userType=3&msg=<?php echo $msg?>&loggedin=<?php echo $loggedin?>&loadstatus=1&debug=<?php echo $debug?><?php
				break;


				//! vw_extchat
			case 'vw_extchat':
				$options = get_option('VWliveStreamingOptions');

				$updated = $_POST['t'];
				$room = $_POST['r'];

				//do not allow uploads to other folders
				sanV($room);
				sanV($updated);

				if (!$room) exit;
				if ($room == 'null') exit;

				$session =  VWliveStreaming::currentUserSession($room);
				if ($session) $updated = max($session->sdate, $updated); //since user session started

				$table_chatlog = $wpdb->prefix . "vw_vwls_chatlog";

				//clean old chat logs
				$closeTime = time() - 86400; //keep for 24h
				$sql="DELETE FROM `$table_chatlog` WHERE mdate < $closeTime";
				$wpdb->query($sql);

				$chatText ='';

				$sql = "SELECT * FROM `$table_chatlog` WHERE room='$room' AND type ='2' AND mdate > $updated ORDER BY mdate DESC LIMIT 0,20";
				$sql = "SELECT * FROM ($sql) items ORDER BY mdate ASC";

				$chatRows = $wpdb->get_results($sql);

				if ($wpdb->num_rows>0) foreach ($chatRows as $chatRow)
						$chatText .= ($chatText?'<BR>':'') .'<font color="#77777">'. date('H:i:s',$chatRow->mdate) . '</font> <B>' . $chatRow->username .'</B>: '. $chatRow->message;

					?>chatText=<?php echo urlencode($chatText)?>&updateTime=<?php echo time()?><?php
					break;

			case 'vv_login':

				//! vv_login - live_video.swf
				//live_video.swf - plain video interface login

				$options = get_option('VWliveStreamingOptions');
				$rtmp_server = $options['rtmp_server'];
				$rtmp_amf = $options['rtmp_amf'];
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
				$canWatch = $options['canWatch'];
				$watchList = $options['watchList'];

				$tokenKey = $options['tokenKey'];
				$serverRTMFP = $options['serverRTMFP'];
				$p2pGroup = $options['p2pGroup'];
				$supportRTMP = $options['supportRTMP'];
				$supportP2P = $options['supportP2P'];
				$alwaysRTMP = $options['alwaysRTMP'];
				$alwaysP2P = $options['alwaysP2P'];
				$disableBandwidthDetection = $options['disableBandwidthDetection'];

				$current_user = wp_get_current_user();

				$loggedin=0;
				$msg="";
				$visitor=0;

				//username
				if ($current_user->$userName) $username=urlencode($current_user->$userName);
				$username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

				//access keys
				if ($current_user)
				{
					$userkeys = $current_user->roles;
					$userkeys[] = $current_user->user_login;
					$userkeys[] = $current_user->ID;
					$userkeys[] = $current_user->user_email;
				}

				$roomName=$_GET['room_name'];
				sanV($roomName);
				if ($username==$roomName) $username.="_".rand(10,99);//allow viewing own room - session names must be different

				//check room
				global $wpdb;
				$table_channels = $wpdb->prefix . "vw_lsrooms";
				$wpdb->flush();

				$sql = "SELECT * FROM $table_channels where name='$roomName'";
				$channel = $wpdb->get_row($sql);
				// $wpdb->query($sql);

				if (!$channel)
				{
					$msg = urlencode("Channel $roomName not found. Owner must broadcast first first!");
				}
				else
				{

					if ($channel->type>=2) //premium
						{

						$poptions = VWliveStreaming::channelOptions($channel->type, $options);

						$canWatch = $poptions['canWatchPremium'];
						$watchList = $poptions['watchListPremium'];
						$msgp = urlencode(" This is a premium channel.");
					}

					switch ($canWatch)
					{
					case "all":
						$loggedin=1;
						if (!$username)
						{
							$username="VW".base_convert((time()-1224350000).rand(0,10),10,36);
							$visitor=1; //ask for username
						}
						break;
					case "members":
						if ($username) $loggedin=1;
						else $msg=urlencode('<a href="' . wp_login_url() . '>' . __('Please login first or register an account if you do not have one! Click here to return to website.', 'ppv-live-webcams') . '</a>') . $msgp;
						break;
					case "list";
						if ($username)
							if (VWliveStreaming::inList($userkeys, $watchList)) $loggedin=1;
							else $msg=urlencode('<a href="' . wp_login_url() . '">' . __('You are not in the broadcasters list', 'ppv-live-webcams') . '.</a> - ' . $username) . $msgp;
							else $msg=urlencode('<a href="' . wp_login_url() . '">' . __('Please login first or register an account if you do not have one! Click here to return to website.', 'ppv-live-webcams') . '</a>') . $msgp;
							break;
					}

					//channel post

					if ($loggedin) $postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $roomName . "' and post_type='channel' LIMIT 0,1" );

					if ($postID)
					{
						$accessList = get_post_meta($postID, 'vw_accessList', true);
						if ($accessList) if (!VWliveStreaming::inList($userkeys, $accessList))
							{
								$loggedin = 0;
								$msg .= urlencode("<a href=\"/\">You are not in channel access list.</a>");
							}

						$vw_logo = get_post_meta( $postID, 'vw_logo', true );
						if (!$vw_logo) $vw_logo = 'global';

						switch ($vw_logo)
						{
						case 'global':
							$overLogo = $options['overLogo'];
							$overLink = $options['overLink'];
							break;

						case 'hide':
							$overLogo = '';
							$overLink = '';
							break;

						case 'custom':
							$overLogo = get_post_meta( $postID, 'vw_logoImage', true );
							$overLink = get_post_meta( $postID, 'vw_logoLink', true );
							break;
						}
					}
					else
					{
						$overLogo = $options['overLogo'];
						$overLink = $options['overLink'];
					}



				}



				$s = $username;
				$u = $username;
				$r = $roomName;
				$m = '';
				if ($loggedin) VWliveStreaming::sessionUpdate($u, $r, 0, 1, 1);

				$userType=0;

				$clientIP = VWliveStreaming::get_ip_address();
				if ($loggedin) VWliveStreaming::webSessionSave($username, 0, 'vv_login', $clientIP); //approve session for rtmp check

				$parameters = html_entity_decode($options['parameters']);

				?>firstParameter=fix&server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php echo
				$p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo
				$disableBandwidthDetection?>&username=<?php echo $username?>&userType=<?php echo $userType?>&msg=<?php echo $msg?>&loggedin=<?php echo
				$loggedin?>&visitor=<?php echo $visitor?>&overLogo=<?php echo urlencode($overLogo)?>&overLink=<?php echo
				urlencode($overLink); echo $parameters; ?>&loadstatus=1&debug=<?php echo $debug;  ?><?php
				break;

			case 'css':
				$options = get_option('VWliveStreamingOptions');
				echo html_entity_decode(stripslashes($options['cssCode']));
				break;

			case 'vs_login':
				//! vs_login - live_watch.swf

				//vs_login.php controls watch interface (video & chat & user list) login

				$options = get_option('VWliveStreamingOptions');
				$rtmp_server = $options['rtmp_server'];
				$rtmp_amf = $options['rtmp_amf'];
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
				$canWatch = $options['canWatch'];
				$watchList = $options['watchList'];

				$tokenKey = $options['tokenKey'];
				$serverRTMFP = $options['serverRTMFP'];
				$p2pGroup = $options['p2pGroup'];
				$supportRTMP = $options['supportRTMP'];
				$supportP2P = $options['supportP2P'];
				$alwaysRTMP = $options['alwaysRTMP'];
				$alwaysP2P = $options['alwaysP2P'];
				$disableBandwidthDetection = $options['disableBandwidthDetection'];

				$sendTip = $options['tips'];


				$current_user = wp_get_current_user();

				$loggedin=0;
				$msg="";
				$visitor=0;

				//username
				if ($current_user->$userName) $username=urlencode($current_user->$userName);
				$username=preg_replace("/[^0-9a-zA-Z]/","-",$username);

				//access keys
				if ($current_user)
				{
					$userkeys = $current_user->roles;
					$userkeys[] = $current_user->user_login;
					$userkeys[] = $current_user->ID;
					$userkeys[] = $current_user->user_email;
					$userkeys[] = $current_user->display_name;
				}

				$roomName=$_GET['room_name'];
				sanV($roomName);

				if ($username==$roomName) $username.="_".rand(10,99);//allow viewing own room - session names must be different

				$ztime=time();

				//check room existene
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $roomName . "' and post_type='channel' LIMIT 0,1" );

				global $wpdb;
				$table_channels = $wpdb->prefix . "vw_lsrooms";
				$wpdb->flush();

				$sql = "SELECT * FROM $table_channels where name='$roomName'";
				$channel = $wpdb->get_row($sql);
				$wpdb->query($sql);


				if ($postID && !$channel)
				{
					$post = get_post($postID);
					$post_author = $post->post_author;
					$ztime = time();

					$sql="INSERT INTO `$table_channels` ( `owner`, `name`, `sdate`, `edate`, `rdate`,`status`, `type`) VALUES ('$post_author', '$roomName', $ztime, $ztime, $ztime, 0, $rtype)";
				}


				if (!$channel && !$postID)
				{
					$msg = urlencode("Channel $roomName not found!");
				}
				else
				{

					if ($channel)
						if ($channel->type>=2) //premium
							{
							$poptions = VWliveStreaming::channelOptions($channel->type, $options);

							$canWatch = $poptions['canWatchPremium'];
							$watchList = $poptions['watchListPremium'];
							$msgp = urlencode(" This is a premium channel.");
						}


					switch ($canWatch)
					{
					case "all":
						$loggedin=1;
						if (!$username)
						{
							$username="VW".base_convert((time()-1224350000).rand(0,10),10,36);
							$visitor=1; //ask for username
							$sendTip=0;
						}
						break;
					case "members":
						if ($username) $loggedin=1;
						else $msg=urlencode('<a href="' . wp_login_url() . '>' . __('Please login first or register an account if you do not have one! Click here to return to website.', 'ppv-live-webcams') . '</a>') . $msgp;
						break;
					case "list";
						if ($username)
							if (VWliveStreaming::inList($userkeys, $watchList)) $loggedin=1;
							else $msg=urlencode('<a href="' . wp_login_url() . '">' . __('You are not in the broadcasters list', 'ppv-live-webcams') . '.</a> - ' . $username) . $msgp;
							else $msg=urlencode('<a href="' . wp_login_url() . '">' . __('Please login first or register an account if you do not have one! Click here to return to website.', 'ppv-live-webcams') . '</a>') . $msgp;
							break;
					}

					//channel features

					$disableChat = 0;
					$disableUsers = 0;
					$writeText = 1;
					$privateTextchat = 1;


					if ($postID)
					{
						$accessList = get_post_meta($postID, 'vw_accessList', true);
						if ($accessList) if (!VWliveStreaming::inList($userkeys, $accessList))
							{
								$loggedin = 0;
								$msg .= urlencode("<a href=\"/\">You are not in channel access list.</a>");
							}

						//reload playlist if updated
						$reloadPlaylist = 0;

						if ($loggedin)
						{
							$playlistActive = get_post_meta( $postID, 'vw_playlistActive', true );
							$playlistLoaded = get_post_meta( $postID, 'vw_playlistLoaded', true );

							//activated or loaded and inactive
							if ($playlistActive || $playlistLoaded)
							{
								$streamsPath = VWliveStreaming::fixPath($options['streamsPath']);
								$smilPath = $streamsPath . 'playlist.smil';

								if (filemtime($smilPath) > $playlistLoaded)
									if (VWliveStreaming::timeTo($roomName . '/playlistReload', 5, $options))
									{
										$reloadPlaylist = 1;
										update_post_meta( $postID, 'vw_playlistLoaded', time() );

									}
							}
						}

						//other permissions
						foreach (array('chat','write','participants','privateChat') as $field)
						{
							$value = get_post_meta($postID, 'vw_'.$field.'List', true);
							if ($value) if (!VWliveStreaming::inList($userkeys, $value))
									switch ($field)
									{
									case 'chat':
										$disableChat = 1;
										break;

									case 'write':
										$writeText = 0;
										break;

									case 'participants':
										$disableUsers = 1;
										break;

									case 'privateChat':
										$privateTextchat = 0;
										break;
									}
						}


						$vw_logo = get_post_meta( $postID, 'vw_logo', true );
						if (!$vw_logo) $vw_logo = 'global';

						switch ($vw_logo)
						{
						case 'global':
							$overLogo = $options['overLogo'];
							$overLink = $options['overLink'];
							break;

						case 'hide':
							$overLogo = '';
							$overLink = '';
							break;

						case 'custom':
							$overLogo = get_post_meta( $postID, 'vw_logoImage', true );
							$overLink = get_post_meta( $postID, 'vw_logoLink', true );
							break;
						}

						$vw_ads = get_post_meta( $postID, 'vw_ads', true );
						if (!$vw_ads) $vw_ads = 'global';

						switch ($vw_ads)
						{
						case 'global':
							$adsServer =$options['adServer'];
							break;

						case 'hide':
							$adsServer = '';

							break;

						case 'custom':
							$adsServer = get_post_meta( $postID, 'vw_adsServer', true );
							break;
						}

					}
					else
					{
						$overLogo = $options['overLogo'];
						$overLink = $options['overLink'];
						$adsServer =$options['adServer'];
					}


				}

				if ($loggedin)
				{
					//user picture and  profile link
					if ($current_user->ID > 0)
					{
						if ($options['userPicture'] == 'avatar') $userPicture = urlencode(get_avatar_url($current_user->ID, array('size' => 150) ));
						if ($options['profilePrefix']) $userLink = urlencode($options['profilePrefix'] . $username);
					}

				}

				//default stream to play
				$streamName = $roomName;

				//upgrade to compatible stream
				$streamName = VWliveStreaming::responsiveStream($streamName, $postID, 'flash');

				$s = $username;
				$u = $username;
				$m = '';
				$r = $roomName;
				if ($loggedin) VWliveStreaming::sessionUpdate($u, $r, 0, 1, 1);


				$userType=0;
				$canKick = 0;

				$clientIP = VWliveStreaming::get_ip_address();
				if ($loggedin) VWliveStreaming::webSessionSave($username, 0, 'vs_login', $clientIP); //approve session for rtmp check

				//replace bad words or expressions
				$filterRegex=urlencode("(?i)(fuck|cunt)(?-i)");
				$filterReplace=urlencode(" ** ");

				if (!$welcome) $welcome="Welcome on <B>".$roomName."</B> live streaming channel!";

				$parameters = html_entity_decode($options['parameters']);
				$layoutCode = html_entity_decode($options['layoutCode']);

				//user notifications
				if ($current_user) if ($current_user->ID)
					{

						$watchRoleParameters = VWliveStreaming::userParameters($current_user, $options['watchRoleParameters']);
						$parametersCode = VWliveStreaming::editParameters($parametersCode, $watchRoleParameters);

						if ($sendTip)
						{
							$balance = self::balance($current_user->ID);

							if ($balance>0) $welcome.= '<BR>* You can send tips. Your starting balance is: ' . $balance;
							else
							{
								$welcome.= '<BR>* You can not send tips because you do not have any credits.';
								$sendTip = 0;
							}
						}

						if ($options['userWatchLimit'])
						{
							$userWatchTime = get_user_meta( $current_user->ID, 'vwls_watch', true );
							if ($userWatchTime) $welcome.= '<BR>* You watched ' . number_format($userWatchTime/60,2) . ' minutes since ' . date("F j, Y, g:i a", get_user_meta( $current_user->ID, 'vwls_watch_update', true )) .'.';

						}
					}

				$parametersCode ='&disableChat=' . $disableChat . '&disableUsers=' . $disableUsers . '&writeText=<' . $writeText . '&privateTextchat=' . $privateTextchat . '&overLogo=' . urlencode($overLogo) . '&overLink=' . urlencode($overLink) . '&layoutCode=' . urlencode($layoutCode) . '&filterRegex=' . $filterRegex . '&filterReplace=' .$filterReplace . '&ws_ads=' . urlencode($adsServer) . '&sendTip=' . $sendTip . '&reloadPlaylist=' . $reloadPlaylist . '&loaderImage=' . urlencode($options['loaderImage']) . '&adsInterval=' . $options['adsInterval'].'&streamName=' . urlencode($streamName). $parameters;

				if ($current_user) if ($current_user->ID)
					{

						$watchRoleParameters = VWliveStreaming::userParameters($current_user, $options['watchRoleParameters']);
						$parametersCode = VWliveStreaming::editParameters($parametersCode, $watchRoleParameters);
					}

				?>firstParameter=fix&server=<?php echo $rtmp_server?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php echo
				$p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo
				$disableBandwidthDetection?>&welcome=<?php echo urlencode($welcome)?>&username=<?php echo $username?>&userType=<?php echo $userType?>&userPicture=<?php echo $userPicture?>&userLink=<?php echo $userLink?>&msg=<?php echo $msg?>&loggedin=<?php
				echo $loggedin?>&visitor=<?php echo $visitor;  echo $parametersCode; ?>&loadstatus=1<?php
				break;


			case 'tips':
				$options = get_option('VWliveStreamingOptions');

				echo html_entity_decode(stripslashes($options['tipOptions']));
				break;

			case 'tip':
				$room_name = sanitize_file_name($_POST['r']);
				$caller = sanitize_file_name($_POST['s']);
				$target = sanitize_file_name($_POST['t']);

				$username = sanitize_file_name($_POST['u']);
				$private = sanitize_file_name($_POST['p']);
				$amount = floatval($_POST['a']);
				$label = sanitize_text_field($_POST['l']);
				$message = sanitize_text_field($_POST['m']);

				$sound = sanitize_file_name($_POST['snd']);

				$options = get_option('VWliveStreamingOptions');

				$postID = $wpdb->get_var( $sql = 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_name = \'' . $room_name . '\' and post_type=\'' . $options['custom_post']. '\' LIMIT 0,1' );

				if (!$postID) VWliveStreaming::rexit('success=0&failed=RoomNotFound-' . urlencode($room_name));
				$post = get_post( $postID );

				$current_user = wp_get_current_user();


				$balance = self::balance($current_user->ID);
				if ($amount > $balance) VWliveStreaming::rexit('success=0&failed=NotEnoughFunds-' . $balance);

				$ztime = time();

				//client cost
				$paid = number_format($amount, 2, '.', '');
				VWliveStreaming::transaction('ppv_tip', $current_user->ID, - $paid, 'Tip for <a href="' . VWliveStreaming::roomURL($room_name) . '">' . $room_name.'</a>. (' .$label.')' , $ztime);

				//performer earning
				$received = number_format($amount * $options['tipRatio'], 2, '.', '');
				VWliveStreaming::transaction('ppv_tip_earning', $post->post_author, $received , 'Tip from ' . $caller .' ('.$label.')', $ztime);

				//update balance and report
				$balance = self::balance($current_user->ID);

				$ownMessage = 'After tip, your balance is: ' . $balance;

				if ($sound) $soundCode = "sound://$sound;;";
				$publicMessage = $soundCode. '<B>Tip from ' . $username . '</B>: ' . $label . " ($paid)";

				$privateMessage = '<B>' . $username . ' (Tip '.$paid.')</B>: ' . $message;

				echo 'success=1&amount=' . $paid . '&balance=' . $balance. '&sound=' .urlencode($sound) . '&privateMessage=' .urlencode($privateMessage). '&publicMessage=' .urlencode($publicMessage) . '&ownMessage=' .urlencode($ownMessage);

				break;

			case 'vc_login':
				//! vc_login - live_broadcast.swf
				$options = get_option('VWliveStreamingOptions');

				$rtmp_server = $options['rtmp_server'];
				$rtmp_amf = $options['rtmp_amf'];
				$canBroadcast = $options['canBroadcast'];
				$broadcastList = $options['broadcastList'];

				$tokenKey = $options['tokenKey'];
				$webKey = $options['webKey'];

				$serverRTMFP = $options['serverRTMFP'];
				$p2pGroup = $options['p2pGroup'];
				$supportRTMP = $options['supportRTMP'];
				$supportP2P = $options['supportP2P'];
				$alwaysRTMP = $options['alwaysRTMP'];
				$alwaysP2P = $options['alwaysP2P'];
				$disableBandwidthDetection = $options['disableBandwidthDetection'];

				$camRes = explode('x',$options['camResolution']);

				$current_user = wp_get_current_user();

				$loggedin=0;
				$msg="";

				//username
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
				if ($current_user->$userName) $username=urlencode($current_user->$userName);
				sanV($username);


				//broadcaster room
				$userlabel="";
				$room_name=$_GET['room_name'];
				sanV($room_name);

				if ($room_name&&$room_name!=$username)
				{
					$userlabel=$username;
					$username=$room_name;
					$room=$room_name;
				}

				if (!$room) $room = $username;

				//access keys
				if ($current_user)
				{
					$userkeys = $current_user->roles;
					$userkeys[] = $current_user->user_login;
					$userkeys[] = $current_user->ID;
					$userkeys[] = $current_user->user_email;
					$userkeys[] = $current_user->display_name;
				}

				switch ($canBroadcast)
				{
				case "members":
					if ($username) $loggedin=1;
					else $msg=urlencode('<a href="' . wp_login_url() . '>' . __('Please login first or register an account if you do not have one! Click here to return to website.', 'ppv-live-webcams') . '</a>');
					break;
				case "list";
					if ($username)
						if (VWliveStreaming::inList($userkeys, $broadcastList)) $loggedin=1;
						else $msg=urlencode('<a href="' . wp_login_url() . '">' . __('You are not in the broadcasters list', 'ppv-live-webcams') . '.</a> - ' . $username);
						else $msg=urlencode('<a href="' . wp_login_url() . '">' . __('Please login first or register an account if you do not have one! Click here to return to website.', 'ppv-live-webcams') . '</a>');
						break;
				}

				//channel features
				if ($loggedin) $postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $room . "' and post_type='" . $options['custom_post'] . "' LIMIT 0,1" );

				if ($postID)
				{
					$post_author_id = get_post_field( 'post_author', $postID );
					if ($current_user->ID != $post_author_id)
					{
						$loggedin = 0;
						$msg=urlencode('Room has different owner: ' . $room);
					}

					$vw_logo = get_post_meta( $postID, 'vw_logo', true );
					if (!$vw_logo) $vw_logo = 'global';

					switch ($vw_logo)
					{
					case 'global':
						$overLogo = $options['overLogo'];
						$overLink = $options['overLink'];
						break;

					case 'hide':
						$overLogo = '';
						$overLink = '';
						break;

					case 'custom':
						$overLogo = get_post_meta( $postID, 'vw_logoImage', true );
						$overLink = get_post_meta( $postID, 'vw_logoLink', true );
						break;
					}
				}
				else
				{
					$overLogo = $options['overLogo'];
					$overLink = $options['overLink'];
				}


				$debug = "$postID-$vw_logo";

				if (!$room)
				{
					$loggedin=0;
					$msg=urlencode("<a href=\"/\">Can't enter: Room missing!</a>");
				}

				if (!$username)
				{
					$loggedin=0;
					$msg=urlencode("<a href=\"/\">Can't enter: Username missing!</a>");
				}


				//channel name
				if ($loggedin)
				{
					global $wpdb;
					$table_channels = $wpdb->prefix . "vw_lsrooms";

					$wpdb->flush();
					$ztime=time();

					//setup/update channel, premium & time reset

					$poptions = VWliveStreaming::premiumOptions($userkeys, $options);

					if ($poptions) //premium room
						{
						$rtype = 1 + $poptions['level'];
						$camBandwidth = $poptions['pCamBandwidth'];
						$camMaxBandwidth = $poptions['pCamMaxBandwidth'];
						//if (!$options['pLogo']) $options['overLogo']=$options['overLink']='';
					}else
					{
						$rtype=1;
						$camBandwidth=$options['camBandwidth'];
						$camMaxBandwidth=$options['camMaxBandwidth'];
					}

					$sql = "SELECT * FROM $table_channels where name='$room'";
					$channel = $wpdb->get_row($sql);

					if (!$channel)
						$sql="INSERT INTO `$table_channels` ( `owner`, `name`, `sdate`, `edate`, `rdate`,`status`, `type`) VALUES ('$post_author_id', '$room', $ztime, $ztime, $ztime, 0, $rtype)";
					elseif ($options['timeReset'] && $channel->rdate < $ztime - $options['timeReset']*24*3600) //time to reset in days
						$sql="UPDATE `$table_channels` set edate=$ztime, type=$rtype, rdate=$ztime, wtime=0, btime=0 where name='$room'";
					else
						$sql="UPDATE `$table_channels` set edate=$ztime, type=$rtype where name='$room'";

					$wpdb->query($sql);
				}


				if ($loggedin) VWliveStreaming::sessionUpdate($username, $room, 1, 1, 1);

				$clientIP = VWliveStreaming::get_ip_address();
				if ($loggedin) VWliveStreaming::webSessionSave($username, 1, 'vc_login', $clientIP); //approve session for rtmp check

				if ($loggedin && $postID)
				{
					update_post_meta($postID, 'stream-protocol', 'rtmp');
					update_post_meta($postID, 'stream-type', 'flash');
				}

				$uploadsPath = $options['uploadsPath'];
				if (!$uploadsPath) { $upload_dir = wp_upload_dir(); $uploadsPath = $upload_dir['basedir'] . '/vwls'; }

				$day = date("y-M-j",time());
				$chatlog_url = VWliveStreaming::path2url($uploadsPath."/$room/Log$day.html");

				$swfurlp = "&prefix=" . urlencode(admin_url() . 'admin-ajax.php?action=vwls&task=');
				$swfurlp .= '&extension='.urlencode('_none_');
				$swfurlp .= '&ws_res=' . urlencode( plugin_dir_url(__FILE__) . 'ls/');


				$linkcode= VWliveStreaming::roomURL($username);

				$imagecode=VWliveStreaming::path2url($uploadsPath."/_snapshots/".urlencode($username).".jpg");

				if (!$options['noEmbeds'])
				{
					$base = plugin_dir_url(__FILE__) . "ls/";
					$swfurl= plugin_dir_url(__FILE__) . "ls/live_watch.swf?ssl=1&n=".urlencode($username) . $swfurlp;
					$swfurl2=plugin_dir_url(__FILE__) . "ls/live_video.swf?ssl=1&n=".urlencode($username) . $swfurlp;

					$embedcode = VWliveStreaming::flash_watch($username);
					$embedvcode = VWliveStreaming::flash_video($username);
				}

				if ($options['externalKeys']) $rtmp_server = VWliveStreaming::rtmp_address($current_user->ID, $postID, true, $stream, $stream);


				$chatlog="The transcript log of this chat is available at <U><A HREF=\"$chatlog_url\" TARGET=\"_blank\">$chatlog_url</A></U>.";
				if (!$welcome) $welcome="Welcome to broadcasting interface for channel '$room'! . $chatlog";

				$parameters = html_entity_decode($options['parametersBroadcaster']);

				if ($options['manualArchiving'])
				{
					$manualArchivingStart = $options['manualArchiving'] . '&action=startRecording&streamname=' . urlencode($username);
					$manualArchivingStop = $options['manualArchiving'] . '&action=stopRecording&streamname=' . urlencode($username);
				}
				if ($current_user->ID > 0)
				{
					$userPicture = urlencode(get_the_post_thumbnail_url($postID));
					if ($options['profilePrefixChannel']) $userLink = urlencode($options['profilePrefixChannel'] . $username);
					else $userLink = $linkcode;
				}

				$layoutCodeBroadcaster = html_entity_decode($options['layoutCodeBroadcaster']);


				//warn if HTTPS missing
				if(empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
					$welcome.= '<br><B>Warning: HTTPS not detected. Some browsers like Chrome will not permit webcam access when accessing without SSL!</B>';


				?>firstParameter=fix&server=<?php echo urlencode($rtmp_server)?>&serverAMF=<?php echo $rtmp_amf?>&tokenKey=<?php echo $tokenKey?>&serverRTMFP=<?php echo urlencode($serverRTMFP)?>&p2pGroup=<?php
				echo $p2pGroup?>&supportRTMP=<?php echo $supportRTMP?>&supportP2P=<?php echo $supportP2P?>&alwaysRTMP=<?php echo $alwaysRTMP?>&alwaysP2P=<?php echo $alwaysP2P?>&disableBandwidthDetection=<?php echo
				$disableBandwidthDetection?>&room=<?php echo $username?>&welcome=<?php echo urlencode($welcome); ?>&username=<?php echo $username?>&userlabel=<?php echo $userlabel?>&userPicture=<?php echo $userPicture?>&userLink=<?php echo $userLink?>&overLogo=<?php echo
				urlencode($overLogo)?>&overLink=<?php echo urlencode($overLink)?>&userType=3&webserver=&msg=<?php echo $msg?>&loggedin=<?php echo $loggedin?>&linkcode=<?php echo
				urlencode($linkcode)?>&embedcode=<?php echo urlencode($embedcode)?>&embedvcode=<?php echo urlencode($embedvcode)?>&imagecode=<?php echo
				urlencode($imagecode)?>&camWidth=<?php echo $camRes[0];?>&camHeight=<?php echo $camRes[1];?>&camFPS=<?php echo
				$options['camFPS']?>&camBandwidth=<?php echo $camBandwidth?>&videoCodec=<?php echo $options['videoCodec']?>&codecProfile=<?php echo $options['codecProfile']?>&codecLevel=<?php echo
				$options['codecLevel']?>&soundCodec=<?php echo $options['soundCodec']?>&soundQuality=<?php echo $options['soundQuality']?>&micRate=<?php echo
				$options['micRate']?>&camMaxBandwidth=<?php echo
				$camMaxBandwidth?>&manualArchivingStart=<?php echo urlencode($manualArchivingStart)?>&manualArchivingStop=<?php echo urlencode($manualArchivingStop)?>&onlyVideo=<?php echo $options['onlyVideo']?>&loaderImage=<?php echo  urlencode($options['loaderImage'])?>&noEmbeds=<?php echo $options['noEmbeds'];  echo $parameters; ?>&layoutCode=<?php echo urlencode($layoutCodeBroadcaster)?>&loadstatus=1&debug=<?php echo $debug; ?><?php
				break;

				//! vc_chatlog
			case 'vc_chatlog':

				//Public and private chat logs
				$private=$_POST['private']; //private chat username, blank if public chat
				$username=$_POST['u'];
				$session=$_POST['s'];
				$room=$_POST['r'];
				$message=$_POST['msg'];
				$time=$_POST['msgtime'];

				//do not allow uploads to other folders
				sanV($room);
				sanV($private);
				sanV($session);
				if (!$room) exit;

				$message = strip_tags($message,'<p><a><img><font><b><i><u>');

				//generate same private room folder for both users
				if ($private)
				{
					if ($private>$session) $proom=$session ."_". $private; else $proom=$private ."_". $session;
				}

				$options = get_option('VWliveStreamingOptions');
				$dir=$options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);
				@chmod($dir, 0777);
				$dir.="/$room";
				if (!file_exists($dir)) mkdir($dir);
				@chmod($dir, 0777);
				if ($proom) $dir.="/$proom";
				if (!file_exists($dir)) mkdir($dir);
				@chmod($dir, 0777);

				$day=date("y-M-j",time());

				$dfile = fopen($dir."/Log$day.html","a");
				fputs($dfile,$message."<BR>");
				fclose($dfile);


				//update html chat log
				$pos = strpos($message,': ')+1;
				$message = substr($message, $pos); //message without username

				if ($message)
				{
					$table_chatlog = $wpdb->prefix . "vw_vwls_chatlog";
					$ztime = time();

					$sql="INSERT INTO `$table_chatlog` ( `username`, `room`, `message`, `mdate`, `type`) VALUES ('$username', '$room', '$message', $ztime, '1')";
					$wpdb->query($sql);
				}

				?>loadstatus=1<?php
				break;

			case 'v_status':
				//watch and video interface

				/*
POST Variables:
u=Username
s=Session, usually same as username
r=Room
ct=session time (in milliseconds)
lt=last session time received from this script in (milliseconds)
*/

				$cam=$_POST['cam'];
				$mic=$_POST['mic'];

				$timeUsed=$currentTime=$_POST['ct'];
				$lastTime=$_POST['lt'];

				$s=$_POST['s'];
				$u=$_POST['u'];
				$r=$_POST['r'];
				$m=$_POST['m'];

				//sanitize variables
				sanV($s);
				sanV($u);
				sanV($r);
				sanV($m,0, 0);

				$timeUsed = (int) $timeUsed;
				$currentTime = (int) $currentTime;
				$lastTime = (int) $lastTime;

				//exit if no valid session name or room name
				if (!$s) exit;
				if (!$r) exit;


				global $wpdb;
				$table_sessions = $wpdb->prefix . "vw_lwsessions";
				$table_channels = $wpdb->prefix . "vw_lsrooms";
				$wpdb->flush();

				$ztime=time();

				//room info
				$sql = "SELECT * FROM $table_channels where name='$r'";
				$channel = $wpdb->get_row($sql);
				$wpdb->query($sql);

				if (!$channel) $disconnect = urlencode("Channel $r not found!");
				else
				{
					$ztime=time();

					//update viewer online
					$sql = "SELECT * FROM $table_sessions where session='$s' and status='1'";
					$session = $wpdb->get_row($sql);
					if (!$session)
					{
						$sql="INSERT INTO `$table_sessions` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`) VALUES ('$s', '$u', '$r', '$m', $ztime, $ztime, 1, 1)";
						$wpdb->query($sql);
						$session = $wpdb->get_row($sql);
					}
					else
					{
						$sql="UPDATE `$table_sessions` set edate=$ztime, room='$r', username='$u', message='$m' where session='$s' and status='1' and `type`='1'";
						$wpdb->query($sql);
					}

					VWliveStreaming::cleanSessions(0);

					//room usage
					// options in minutes
					// mysql in s
					// flash in ms (minimise latency errors)

					$options = get_option('VWliveStreamingOptions');

					if ($channel->type>=2) //premium
						{
						$poptions = VWliveStreaming::channelOptions($channel->type, $options);

						$maximumBroadcastTime =  60 * $poptions['pBroadcastTime'];
						$maximumWatchTime =  60 * $poptions['pWatchTime'];
					}
					else
					{
						$maximumBroadcastTime =  60 * $options['broadcastTime'];
						$maximumWatchTime =  60 * $options['watchTime'];
					}

					$maximumSessionTime = $maximumWatchTime;


					//update time
					$expTime = $options['onlineExpiration0']+60;
					$dS = floor(($currentTime-$lastTime)/1000);

					if ($dS > $expTime || $dS<0) $disconnect = urlencode("Web server out of sync compared to online expiration setting: $dS/$expTime"); //Updates should be faster; fraud attempt?
					else
					{
						$channel->wtime += $dS;
						$timeUsed = $channel->wtime * 1000;

						if ($maximumBroadcastTime && $maximumBroadcastTime < $channel->btime ) $disconnect = urlencode("Allocated broadcasting time ended!");
						if ($maximumWatchTime && $maximumWatchTime < $channel->wtime ) $disconnect = urlencode("Allocated watch time ended!");

						$maximumSessionTime *=1000;

						//update
						$sql="UPDATE `$table_channels` set wtime = " . $channel->wtime . " where name='$r'";
						$wpdb->query($sql);

						//update post
						$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $r . "' and post_type='".$options['custom_post']."' LIMIT 0,1" );
						if ($postID)
						{
							update_post_meta($postID, 'wtime', $channel->wtime);
						}

						//update user watch time, disconnect if exceeded limit
						$current_user = wp_get_current_user();
						if ($current_user)
							if (VWliveStreaming::updateUserWatchtime($current_user, $dS, $options))
								$disconnect = urlencode('Your user watch time limit was exceeded!');

					}



				}

				?>timeTotal=<?php echo $maximumSessionTime?>&timeUsed=<?php echo $timeUsed?>&lastTime=<?php echo $currentTime?>&disconnect=<?php echo $disconnect?>&dS=<?php echo $dS?>&loadstatus=1<?php
				break;

				//! rtmp_status
			case 'rtmp_status':

				$options = get_option('VWliveStreamingOptions');

				//allow such requests only if feature is enabled (by default is not)
				if (!in_array($options['webStatus'], array('enabled', 'strict'))) VWliveStreaming::rexit('denied=webStatusNotEnabled-' . $options['webStatus']);

				//allow only status updates from configured server IP
				if ($options['rtmp_restrict_ip'])
				{
					$allowedIPs = explode(',', $options['rtmp_restrict_ip']);
					$requestIP = VWliveStreaming::get_ip_address();

					$found = 0;
					foreach ($allowedIPs as $allowedIP)
						if ($requestIP == trim($allowedIP)) $found = 1;

						if (!$found) VWliveStreaming::rexit('denied=NotFromAllowedIP-' . $requestIP);

				} else VWliveStreaming::rexit('denied=StatusServerIPnotConfigured');


				//self::requirementUpdate('rtmp_status', 1);
				self::requirementMet('rtmp_status');

				//start logging
				$dir = $options['uploadsPath'];
				$filename1 = $dir ."/_rtmpStatus.txt";
				$dfile = fopen($filename1,"w");

				fputs($dfile,'VideoWhisper Log for RTMP Session Control'. "\r\n");
				fputs($dfile,"Server Date: ". "\r\n" . date("D M j G:i:s T Y"). "\r\n" );
				fputs($dfile, '$_POST:'. "\r\n" . serialize($_POST));

				//start with a cleanup for viewers and broadcasters
				self::cleanSessions(0);
				self::cleanSessions(1);

				//sessions table
				global $wpdb;
				$table_channels = $wpdb->prefix . "vw_lsrooms";

				$wpdb->flush();
				$ztime=time();

				$controlUsers = array();
				$controlSessions = array();



				//rtpsessions - WebRTC
				$rtpsessiondata = stripslashes($_POST['rtpsessions']);

				if (version_compare(phpversion(), '7.0', '<')) $rtpsessions = unserialize($rtpsessiondata);  //request is from trusted server
				else $rtpsessions = unserialize($rtpsessiondata, array());


				$webrtc_test = 0;
				if (is_array($rtpsessions))
					foreach ($rtpsessions as $rtpsession)
					{

						$disconnect = "";

						if (!$options['webrtc']) $disconnect = 'WebRTC is disabled.';

						$stream = $rtpsession['streamName'];
						$streamQuery = array();

						if ($rtpsession['streamQuery'])
						{

							parse_str($rtpsession['streamQuery'], $streamQuery);

							if ($userID = (int) $streamQuery['userID'] )
							{
								$user = get_userdata($userID);

								$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
								if ($user->$userName) $username = urlencode($user->$userName);
							}
						}

						if ($channel_id = (int) $streamQuery['channel_id'] )
						{
							$post = get_post($channel_id);

						} else $disconnect = 'No channel ID.';

						$transcoding = $streamQuery['transcoding']; // just a transcoding

						//WebRTC session vars

						$r = $stream;
						$u = $username;

						if ($rtpsession['streamPublish'] == 'true' && $userID && !$disconnect && !$transcoding) //WebRTC broadcaster session
							{
							$s = $stream;
							$m = 'WebRTC Broadcaster';

							//webrtc broadcast test
							if (!$webrtc_test)
							{
								self::requirementMet('webrtc_test');
								$webrtc_test = 1;
							}

							$keyBroadcast = md5('vw' . $options['webKey'] . $userID  . $channel_id);
							if ($streamQuery['key'] != $keyBroadcast) $disconnect = 'WebRTC broadcast key mismatch.';

							if (!$post) $disconnect = 'Channel post not found.';
							elseif ($post->post_author != $userID) $disconnect = 'Only channel owner can broadcast.';

							if (!$disconnect)
							{

								//sessionUpdate($username='', $room='', $broadcaster=0, $type=1, $strict=1);
								$session = VWliveStreaming::sessionUpdate($s, $r, 1, 3, 1, 0);

								/*
								//user online
								$table_sessions = $wpdb->prefix . "vw_sessions";

								$sqlS = "SELECT * FROM $table_sessions WHERE session='$s' AND status='1' ORDER BY type DESC, edate DESC LIMIT 0,1";
								$session = $wpdb->get_row($sqlS);

								if (!$session)
								{
									$sql="INSERT INTO `$table_sessions` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`) VALUES ('$s', '$u', '$r', '$m', $ztime, $ztime, 1, 2)";
									$wpdb->query($sql);
									$session = $wpdb->get_row($sqlS);
								}

								//update session
								$sql="UPDATE `$table_sessions` set edate=$ztime where id='".$session->id."'";
								$wpdb->query($sql);
								*/

								//generate external snapshot for external broadcaster ??
								VWliveStreaming::rtmpSnapshot($session);

								$sqlC = "SELECT * FROM $table_channels WHERE name='" . $r . "' LIMIT 0,1";
								$channel = $wpdb->get_row($sqlC);

								if ($ban =  VWliveStreaming::containsAny($channel->name, $options['bannedNames'])) $disconnect = "Room banned ($ban)!";

								//calculate time in ms based on previous request
								$lastTime =  $session->edate * 1000;
								$currentTime = $ztime * 1000;
								$btime = intval($channel->btime);

								//update time
								$expTime = $options['onlineExpiration1'] + 60;
								$dS = floor(($currentTime-$lastTime)/1000);
								//if ($dS > $expTime || $dS<0) $disconnect = "Web server out of sync for webrtc broadcaster ($dS > $expTime) !"; //Updates should be faster; fraud attempt?

								$btime += $dS;

								//update room
								$sql="UPDATE `$table_channels` set edate=$ztime, btime = " . $btime . " where id = '" . $channel->id. "'";
								$wpdb->query($sql);

								//update post
								$postID = $channel_id;

								if ($postID)
								{
									update_post_meta($postID, 'edate', $ztime);
									update_post_meta($postID, 'btime', $btime);

									update_post_meta($postID, 'stream-protocol', 'rtsp');
									update_post_meta($postID, 'stream-type', 'webrtc');

									self::updateViewers($postID, $r, $options);
								}

								//transcode stream (from RTSP)
								if (!$disconnect) if ($options['transcodingAuto']>=2) VWliveStreaming::transcodeStreamWebRTC($session->room, $postID, $options);

									// room usage
									// options in minutes
									// mysql in s
									// flash in ms (minimise latency errors)

									if ($channel->type>=2) //premium
										{
										$poptions = VWliveStreaming::channelOptions($channel->type, $options);

										$maximumBroadcastTime =  60 * $poptions['pBroadcastTime'];
										$maximumWatchTime =  60 * $poptions['pWatchTime'];
									}
								else
								{
									$maximumBroadcastTime =  60 * $options['broadcastTime'];
									$maximumWatchTime =  60 * $options['watchTime'];
								}

								$maximumSessionTime = $maximumBroadcastTime; //broadcaster

								$timeUsed = $channel->btime * 1000;

								if ($maximumBroadcastTime && $maximumBroadcastTime < $btime ) $disconnect = "Allocated broadcasting time ended!";
								if ($maximumWatchTime && $maximumWatchTime < $channel->wtime ) $disconnect = "Allocated watch time ended!";

								$maximumSessionTime *=1000;
							}


							//end WebRTC broadcaster session
						}

						if ($rtpsession['streamPlay'] == 'true' && !$disconnect)  //webRTC playback
							{

							$s = $username .'_'. $stream;

							//sessionUpdate($username='', $room='', $broadcaster=0, $type=1,  $strict=1, $updated=1);
							$session = VWliveStreaming::sessionUpdate($s, $r, 0, 3, 1, 0);

							/*
							$table_sessions = $wpdb->prefix . "vw_lwsessions";

							//update viewer online
							$sqlS = "SELECT * FROM $table_sessions WHERE session='$s' AND status='1' ORDER BY type DESC, edate DESC LIMIT 0,1";

							$session = $wpdb->get_row($sqlS);
							if (!$session) //insert external viewer type=2
								{
								$sql="INSERT INTO `$table_sessions` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`) VALUES ('$s', '$u', '$r', '', $ztime, $ztime, 1, 2)";
								$wpdb->query($sql);
								$session = $wpdb->get_row($sqlS);
							};

								$sql="UPDATE `$table_sessions` set edate=$ztime where id='".$session->id."'";
								$wpdb->query($sql);
							*/


							if ($session->type >= '2') //external viewer session: update here
								{

								$sqlC = "SELECT * FROM $table_channels WHERE name='" . $session->room . "' LIMIT 0,1";
								$channel = $wpdb->get_row($sqlC);


								//calculate time in ms based on previous request
								$lastTime =  $session->edate * 1000;
								$currentTime = $ztime * 1000;
								$wtime = intval($channel->wtime);

								//update room time
								$expTime = $options['onlineExpiration0']+40;

								$dS = floor(($currentTime-$lastTime)/1000);
								if ($dS > $expTime || $dS<0) $disconnect = "Web server out of sync ($dS > $expTime)!"; //Updates should be faster than 3 minutes; disconnected and returned on same session?

								$wtime += $dS;

								//update
								$sql="UPDATE `$table_channels` set wtime = " . $wtime . " where id = '" . $channel->id. "'";
								$wpdb->query($sql);

								//update post
								$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $r . "' and post_type='channel' LIMIT 0,1" );
								if ($postID)
								{
									update_post_meta($postID, 'wtime', $wtime);
								}

								//update user watch time, disconnect if exceeded limit
								$user = get_user_by('login', $u);
								if ($user)
									if (VWliveStreaming::updateUserWatchtime($user, $dS, $options))
										$disconnect = urlencode('User watch time limit exceeded!');


							}
							// room usage
							// options in minutes
							// mysql in s
							// flash in ms (minimise latency errors)

							if ($channel->type>=2) //premium
								{
								$poptions = VWliveStreaming::channelOptions($channel->type, $options);

								$maximumBroadcastTime =  60 * $poptions['pBroadcastTime'];
								$maximumWatchTime =  60 * $poptions['pWatchTime'];
							}
							else
							{
								$maximumBroadcastTime =  60 * $options['broadcastTime'];
								$maximumWatchTime =  60 * $options['watchTime'];
							}

							$maximumSessionTime = $maximumWatchTime;

							$timeUsed = $channel->wtime * 1000;

							if ($maximumBroadcastTime && $maximumBroadcastTime < $channel->btime ) $disconnect = "Allocated broadcasting time ended!";
							if ($maximumWatchTime && $maximumWatchTime < $wtime ) $disconnect = "Allocated watch time ended!";

							$maximumSessionTime *=1000;

							//end WebRTC playback
						}


						$controlSession['disconnect'] = $disconnect;

						$controlSession['session'] = $s;
						$controlSession['dS'] = intval($dS);
						$controlSession['type'] = $session->type;
						$controlSession['room'] = $r;
						$controlSession['username'] = $u;

						//$controlSession['query'] = $rtpsession['streamQuery'];

						$controlSessions[$rtpsession['sessionId']] = $controlSession;

						//end  foreach ($rtpsessions as $rtpsession)
					}

				$controlSessionsS = serialize($controlSessions);

				//debug update
				fputs($dfile,"\r\nControl RTP Sessions: " . "\r\n" . $controlSessionsS);



				//users - RTMP clients
				$userdata = stripslashes($_POST['users']);

				if (version_compare(phpversion(), '7.0', '<'))
					$users = unserialize($userdata);  //request is from trusted server
				else $users = unserialize($userdata, array());


				$rtmp_test = 0;

				if (is_array($users))
					foreach ($users as $user)
					{

						//$rooms = explode(',',$user['rooms']); $r = $rooms[0];
						$r = $user['rooms'];
						$s = $user['session'];
						$u = $user['username'];

						$ztime=time();
						$disconnect = "";

						if ($ban =  VWliveStreaming::containsAny($s, $options['bannedNames'])) $disconnect = "Name banned ($s,$ban)!";

						//kill snap/info sessions
						if ($options['ffmpegTimeout'])
							if ($user['runSeconds']) if ($user['runSeconds']>$options['ffmpegTimeout'])
									if ( in_array(substr($user['session'],0,11), array('ffmpegSnap_', 'ffmpegInfo_')) )
									{
										$disconnect = 'FFMPEG timeout.';
									}

								if ($user['role'] == '1') //channel broadcaster
									{

									//an user is connected on rtmp: works
									if (!$rtmp_test)
									{
										self::requirementMet('rtmp_test');
										$rtmp_test = 1;
									}

									if (!$r) $r = $s; //use session as room if missing in older rtmp side

									//sessionUpdate($username='', $room='', $broadcaster=0, $type=1, $strict=1, $updated=1);
									$session = VWliveStreaming::sessionUpdate($s, $r, 1, 2, 0, 0); //not strict in case this is existing flash user

									/*
									//user online
									$table_sessions = $wpdb->prefix . "vw_sessions";
									$sqlS = "SELECT * FROM $table_sessions WHERE session='$s' AND status='1' ORDER BY type DESC, edate DESC LIMIT 0,1";
									$session = $wpdb->get_row($sqlS);

									if (!$session) //insert as external type=2
										{
										$sql="INSERT INTO `$table_sessions` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`) VALUES ('$s', '$u', '$r', '$m', $ztime, $ztime, 1, 2)";
										$wpdb->query($sql);
										$session = $wpdb->get_row($sqlS);
									}
//

										//update session
										$sql="UPDATE `$table_sessions` set edate=$ztime where id='".$session->id."'";
										$wpdb->query($sql);
 */

									if ($session->type >= 2) //external broadcaster: update here, otherwise updates on calls from flash app
										{
										//generate external snapshot for external broadcaster
										VWliveStreaming::rtmpSnapshot($session);

										$sqlC = "SELECT * FROM $table_channels WHERE name='" . $session->room . "' LIMIT 0,1";
										$channel = $wpdb->get_row($sqlC);


										if ($ban =  VWliveStreaming::containsAny($channel->name,$options['bannedNames'])) $disconnect = "Room banned ($ban)!";

										//calculate time in ms based on previous request
										$lastTime =  $session->edate * 1000;
										$currentTime = $ztime * 1000;

										//update time
										$expTime = $options['onlineExpiration1'] + 60;
										$dS = floor(($currentTime-$lastTime)/1000);
										//if ($dS > $expTime || $dS<0) $disconnect = "Web server out of sync for rtmp broadcaster ($dS > $expTime) !"; //Updates should be faster; fraud attempt?

										$channel->btime += $dS;

										//update room
										$sql="UPDATE `$table_channels` set edate=$ztime, btime = " . $channel->btime . " where id = '" . $channel->id. "'";
										$wpdb->query($sql);

										//update post
										$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $session->room . "' and post_type='" . $options['custom_post']. "' LIMIT 0,1" );

										//detect transcoding to avoid altering source info
										$transcoding = 0;
										$stream_webrtc = $session->room . '_webrtc';
										$stream_hls = 'i_' . $session->room;
										if ($s == $stream_hls || $s == $stream_webrtc) $transcoding = 1;

										if ($postID && !$transcoding)
										{
											update_post_meta($postID, 'edate', $ztime);
											update_post_meta($postID, 'btime', $channel->btime);

											update_post_meta($postID, 'stream-protocol', 'rtmp');
											update_post_meta($postID, 'stream-type', 'external');
											update_post_meta($postID, 'stream-updated', $ztime);

											self::updateViewers($postID, $session->room, $options);
										}

										//transcode stream (from RTMP)
										if (!$disconnect) if ($options['transcodingAuto']>=2) VWliveStreaming::transcodeStream($session->room);
									}

									// room usage
									// options in minutes
									// mysql in s
									// flash in ms (minimise latency errors)

									if ($channel->type>=2) //premium
										{
										$poptions = VWliveStreaming::channelOptions($channel->type, $options);

										$maximumBroadcastTime =  60 * $poptions['pBroadcastTime'];
										$maximumWatchTime =  60 * $poptions['pWatchTime'];
									}
									else
									{
										$maximumBroadcastTime =  60 * $options['broadcastTime'];
										$maximumWatchTime =  60 * $options['watchTime'];
									}

									$maximumSessionTime = $maximumBroadcastTime; //broadcaster

									$timeUsed = $channel->btime * 1000;

									if ($maximumBroadcastTime && $maximumBroadcastTime < $channel->btime ) $disconnect = "Allocated broadcasting time ended!";
									if ($maximumWatchTime && $maximumWatchTime < $channel->wtime ) $disconnect = "Allocated watch time ended!";

									$maximumSessionTime *=1000;


								}
							else //subscriber viewer
								{


								//sessionUpdate($username='', $room='', $broadcaster=0, $type=1,  $strict=1, $updated=1);
								$session = VWliveStreaming::sessionUpdate($s, $r, 0, 2, 0, 0); //not strict in case this is existing flash user


								/*
								$table_sessions = $wpdb->prefix . "vw_lwsessions";

								//update viewer online
								$sqlS = "SELECT * FROM $table_sessions WHERE session='$s' AND status='1' ORDER BY type DESC, edate DESC LIMIT 0,1";

								$session = $wpdb->get_row($sqlS);
								if (!$session) //insert external viewer type=2
									{
									$sql="INSERT INTO `$table_sessions` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`) VALUES ('$s', '$u', '$r', '', $ztime, $ztime, 1, 2)";
									$wpdb->query($sql);
									$session = $wpdb->get_row($sqlS);
								};

									$sql="UPDATE `$table_sessions` set edate=$ztime where id='".$session->id."'";
									$wpdb->query($sql);
								*/


								if ($session->type >= '2') //external viewer session: update here
									{

									$sqlC = "SELECT * FROM $table_channels WHERE name='" . $session->room . "' LIMIT 0,1";
									$channel = $wpdb->get_row($sqlC);


									//calculate time in ms based on previous request
									$lastTime =  $session->edate * 1000;
									$currentTime = $ztime * 1000;

									//update room time
									$expTime = $options['onlineExpiration0']+30;

									$dS = floor(($currentTime-$lastTime)/1000);
									if ($dS > $expTime || $dS<0) $disconnect = "Web server out of sync ($dS > $expTime)!"; //Updates should be faster than 3 minutes; fraud attempt?

									$channel->wtime += $dS;

									//update
									$sql="UPDATE `$table_channels` set wtime = " . $channel->wtime . " where id = '" . $channel->id. "'";
									$wpdb->query($sql);

									//update post
									$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $r . "' and post_type='channel' LIMIT 0,1" );
									if ($postID)
									{
										update_post_meta($postID, 'wtime', $channel->wtime);
									}

									//update user watch time, disconnect if exceeded limit
									$user = get_user_by('login', $u);
									if ($user)
										if (VWliveStreaming::updateUserWatchtime($user, $dS, $options))
											$disconnect = urlencode('User watch time limit exceeded!');


								}
								// room usage
								// options in minutes
								// mysql in s
								// flash in ms (minimise latency errors)

								if ($channel->type>=2) //premium
									{
									$poptions = VWliveStreaming::channelOptions($channel->type, $options);

									$maximumBroadcastTime =  60 * $poptions['pBroadcastTime'];
									$maximumWatchTime =  60 * $poptions['pWatchTime'];
								}
								else
								{
									$maximumBroadcastTime =  60 * $options['broadcastTime'];
									$maximumWatchTime =  60 * $options['watchTime'];
								}

								$maximumSessionTime = $maximumWatchTime;

								$timeUsed = $channel->wtime * 1000;

								if ($maximumBroadcastTime && $maximumBroadcastTime < $channel->btime ) $disconnect = "Allocated broadcasting time ended!";
								if ($maximumWatchTime && $maximumWatchTime < $channel->wtime ) $disconnect = "Allocated watch time ended!";

								$maximumSessionTime *=1000;


							}

						$controlUser['disconnect'] = $disconnect;

						$controlUser['session'] = $s;
						$controlUser['dS'] = intval($dS);
						$controlUser['type'] = $session->type;
						$controlUser['room'] = $session->room;
						$controlUser['username'] = $session->username;

						$controlUsers[$user['session']] = $controlUser;

					}

				$controlUsersS = serialize($controlUsers);

				//fputs($dfile,"\r\n rtpsessiondata: " . $rtpsessiondata );
				//    fputs($dfile,"\r\n rtpsessions rebuild 3: " . serialize(unserialize($rtpsessiondata, array())) );

				//fputs($dfile,"\r\n rtpsessions: " . serialize($rtpsessions) );

				fputs($dfile,"\r\nControl RTMP Users: ". "\r\n" . $controlUsersS);
				fclose($dfile);

				$appStats = stripslashes($_POST['aS']);
				file_put_contents($options['uploadsPath'] . '/sessionsApp', $appStats);
				
				echo 'VideoWhisper=1&usersCount='.count($users)."&controlUsers=$controlUsersS&controlSessions=$controlSessionsS";
				// rtmp_status end
				break;


				//! rtmp_logout
			case 'rtmp_logout':

				//rtmp server notifies client disconnect here
				$session = $_GET['s'];
				sanV($session);
				if (!$session) exit;

				$options = get_option('VWliveStreamingOptions');
				$dir=$options['uploadsPath'];

				echo "logout=";
				$filename1 = $dir ."/_sessions/$session";

				if (file_exists($filename1))
				{
					echo unlink($filename1); //remove session file
				}
				?><?php
				break;
				//! rtmp_login
			case 'rtmp_login':

				//when external app connects to streaming server, it will call this to confirm and then accept/reject
				//rtmp server should check login like rtmp_login.php?s=$session&p[]=$username&p[]=$room&p[]=$key&p[]=$broadcaster&p[]=$broadcasterID&p[]=$IP
				//p[] = params sent with rtmp address (key, channel)

				$session = $_GET['s'];
				sanV($session);
				if (!$session) exit;

				$p =  $_GET['p'];

				if (count($p))
				{
					$username = $p[0]; //or sessionID
					$room = $channel = $p[1];
					$key = $p[2];
					$broadcaster = ($p[3] === 'true' || $p[3] === '1');
					$broadcasterID = $p[4];
				}

				$ip = '';
				if (count($p)>=5)  $ip = $p[5]; //ip detected from streaming server


				$postID = 0;
				$ztime = time();

				$options = get_option('VWliveStreamingOptions');

				global $wpdb;
				$wpdb->flush();
				$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . sanitize_file_name($channel) . "' and post_type='" . $options['custom_post']. "' LIMIT 0,1" );

				//verify owner
				$invalid = 0;

				if ($broadcaster)
				{
					$post_author_id = get_post_field( 'post_author', $postID );
					if ( $broadcasterID != $post_author_id)
					{
						$invalid = 1; //owner mismatch
					}
				}

				$verified = 0;

				//rtmp key login for external apps: only for external apps is validated based on secret key, local app sessions should be already validated
				if (!$invalid)
					if ($broadcaster=='1') //external broadcaster
						{
						$validKey = md5('vw' . $options['webKey'] . $broadcasterID . $postID);

						if ($key == $validKey)
						{
							$verified = 1;

							VWliveStreaming::webSessionSave($session, 1, 'rtmp_login_broadcaster', $ip);

							//setup/update channel in sql
							global $wpdb;
							$table_channels = $wpdb->prefix . "vw_lsrooms";
							$wpdb->flush();

							$sql = "SELECT * FROM $table_channels where name='$room'";
							$channelR = $wpdb->get_row($sql);

							if (!$channelR)
								$sql="INSERT INTO `$table_channels` ( `owner`, `name`, `sdate`, `edate`, `rdate`,`status`, `type`) VALUES ('$broadcasterID', '$room', $ztime, $ztime, $ztime, 0, 1)";
							elseif ($options['timeReset'] && $channelR->rdate < $ztime - $options['timeReset']*24*3600) //time to reset in days
								$sql="UPDATE `$table_channels` set edate=$ztime, type=1, rdate=$ztime, wtime=0, btime=0 where name='$room'";
							else
								$sql="UPDATE `$table_channels` set edate=$ztime where name='$room'";

							$wpdb->query($sql);

							//VWliveStreaming::sessionUpdate($username, $room, 1, 2, 1);


							//detect transcoding to not alter source info
							$transcoding = 0;
							$stream_webrtc = $room . '_webrtc';
							$stream_hls = 'i_' . $room;
							if ($username == $stream_hls || $username == $stream_webrtc) $transcoding = 1;

							if ($postID && !$transcoding)
							{
								update_post_meta($postID, 'stream-protocol', 'rtmp');
								update_post_meta($postID, 'stream-type', 'external');
								update_post_meta($postID, 'stream-updated', $ztime);
							}

						}

					}
				elseif ($broadcaster=='0') //external watcher
					{
					$validKeyView = md5('vw' . $options['webKey']. $postID);
					if ($key == $validKeyView)
					{
						$verified = 1;

						VWliveStreaming::webSessionSave($session, 0, 'rtmp_login_viewer', $ip);
						//VWliveStreaming::sessionUpdate($username, $room, 0, 2, 1);
					}
					//VWliveStreaming::webSessionSave('error-'.$session, 0, "$channel-$session-$key-$postID-$validKeyView-".sanitize_file_name($channel) );

				}

				//after previously validaded session (above or by local apps login), returning result saved

				//validate web login to streaming server
				$dir = $options['uploadsPath'];
				$filename1 = $dir ."/_sessions/$session";
				if (file_exists($filename1)) //web login present
					{
					echo implode('', file($filename1));
					if ($broadcaster) echo '&role=' . $broadcaster;
				}
				else
				{
					echo "VideoWhisper=1&login=0";
				}

				//also update RTMP server IP in settings after authentication
				if ($verified)
				{


					if (in_array($options['webStatus'], array('auto','enabled'))) //in strict mode does not add IPs
						{
						$ip = VWliveStreaming::get_ip_address();;
						if (!strstr($options['rtmp_restrict_ip'], $ip))  //add ip only if missing
							{
							$options['rtmp_restrict_ip'] .= ($options['rtmp_restrict_ip']?',':'') . $ip;
							$updateOptions=1;
							echo '&rtmp_restrict_ip=' . $options['rtmp_restrict_ip'];
						}
					}


					//also enable webStatus if on auto (now secure with IP restriction enabled)
					if ($options['webStatus'] == 'auto')
					{
						$options['webStatus'] = 'enabled';
						$updateOptions=1;
						echo '&webStatus=' . $options['webStatus'];
					}

					if ($updateOptions) update_option('VWliveStreamingOptions', $options);

				}


				?><?php
				break;

			case 'lb_status':
				//! lb_status
				/*
Broadcaster status updates.

POST Variables:
u=Username
s=Session, usually same as username
r=Room
ct=session time (in milliseconds)
lt=last session time received from this script in (milliseconds)
cam, mic = 0 none, 1 disabled, 2 enabled
*/

				$cam=$_POST['cam'];
				$mic=$_POST['mic'];

				$timeUsed=$currentTime=$_POST['ct'];
				$lastTime=$_POST['lt'];

				$s=$_POST['s'];
				$u=$_POST['u'];
				$r=$_POST['r'];
				$m=$_POST['m'];

				//sanitize variables
				sanV($s);
				sanV($u);
				sanV($r);
				sanV($m,0);

				$timeUsed = (int) $timeUsed;
				$currentTime = (int) $currentTime;
				$lastTime = (int) $lastTime;

				//exit if no valid session name or room name
				if (!$s) exit;
				if (!$r) exit;

				//only registered users can broadcast
				if (!is_user_logged_in()) exit;

				//web status active after rtmp connection
				self::requirementMet('rtmp_test');


				$table_sessions = $wpdb->prefix . "vw_sessions";
				$table_channels = $wpdb->prefix . "vw_lsrooms";
				$wpdb->flush();

				$ztime=time();

				//room info
				$sql = "SELECT * FROM $table_channels where name='$r'";
				$channel = $wpdb->get_row($sql);
				$wpdb->query($sql);

				if (!$channel) $disconnect = urlencode("Channel $r not found!");
				else
				{
					//user online
					$sql = "SELECT * FROM $table_sessions where session='$s' AND status='1' AND `type`='1'";
					$session = $wpdb->get_row($sql);
					if (!$session)
					{
						$sql="INSERT INTO `$table_sessions` ( `session`, `username`, `room`, `message`, `sdate`, `edate`, `status`, `type`) VALUES ('$s', '$u', '$r', '$m', $ztime, $ztime, 1, 1)";
						$wpdb->query($sql);
					}
					else
					{
						$sql="UPDATE `$table_sessions` set edate=$ztime, room='$r', username='$u', message='$m' where session='$s' AND status='1' AND `type`='1'";
						$wpdb->query($sql);
					}

					VWliveStreaming::cleanSessions(1);

					//room usage
					// options in minutes
					// mysql in s
					// flash in ms (minimise latency errors)

					$options = get_option('VWliveStreamingOptions');
					if ($ban =  VWliveStreaming::containsAny($s, $options['bannedNames'])) $disconnect = "Name banned ($s, $ban)!";
					if ($ban =  VWliveStreaming::containsAny($r, $options['bannedNames'])) $disconnect = "Room banned ($r, $ban)!";

					if ($channel->type>=2) //premium
						{
						$poptions = VWliveStreaming::channelOptions($channel->type, $options);

						$maximumBroadcastTime =  60 * $poptions['pBroadcastTime'];
						$maximumWatchTime =  60 * $poptions['pWatchTime'];
					}
					else
					{
						$maximumBroadcastTime =  60 * $options['broadcastTime'];
						$maximumWatchTime =  60 * $options['watchTime'];
					}

					$maximumSessionTime = $maximumBroadcastTime; //broadcaster

					//update time
					$expTime = $options['onlineExpiration1']+30;
					$dS = floor(($currentTime-$lastTime)/1000);

					if ($dS>$expTime || $dS<0) $disconnect = urlencode("Web server out of sync! ($dS>$expTime)" ); //Updates should be faster than 3 minutes; fraud attempt?
					else
					{
						$channel->btime += $dS;
						$timeUsed = $channel->btime * 1000;

						if ($maximumBroadcastTime && $maximumBroadcastTime < $channel->btime ) $disconnect = urlencode("Allocated broadcasting time ended!");
						if ($maximumWatchTime && $maximumWatchTime < $channel->wtime ) $disconnect = urlencode("Allocated watch time ended!");

						$maximumSessionTime *=1000;

						//update
						$sql="UPDATE `$table_channels` set edate=$ztime, btime = " . $channel->btime . " where name='$r'";
						$wpdb->query($sql);

						//transcode if necessary
						if (!$disconnect) if ($options['transcodingAuto']>=2) VWliveStreaming::transcodeStream($r);

							//update post
							$postID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_title = '" . $r . "' and post_type='" . $options['custom_post'] .  "' LIMIT 0,1" );
						if ($postID)
						{
							update_post_meta($postID, 'edate', $ztime);
							update_post_meta($postID, 'btime', $channel->btime);

							self::updateViewers($postID, $r, $options);

							update_post_meta($postID, 'stream-protocol', 'rtmp');
							update_post_meta($postID, 'stream-type', 'flash');
						}

					}

				}


				?>timeTotal=<?php echo $maximumSessionTime?>&timeUsed=<?php echo $timeUsed?>&lastTime=<?php echo $currentTime?>&disconnect=<?php echo $disconnect?>&loadstatus=1<?php
				break;
				//! translation
			case 'translation':
				?> <translations>
<?php
				$options = get_option('VWliveStreamingOptions');
				echo html_entity_decode(stripslashes($options['translationCode']));
?>
</translations><?php
				break;
				//! ads
			case 'ads':

				/* Sample local ads serving script ; Or use http://adinchat.com compatible ads server to setup http://adinchat.com/v/your-campaign-id

POST Variables:
u=Username
s=Session, usually same as username
r=Room
ct=session time (in milliseconds)
lt=last session time received (from web status script)

*/

				$room=$_POST[r];
				$session=$_POST[s];
				$username=$_POST[u];

				$currentTime=$_POST[ct];
				$lastTime=$_POST[lt];

				$ztime=time();

				$options = get_option('VWliveStreamingOptions');

				global $wpdb;
				$table_channels = $wpdb->prefix . "vw_lsrooms";

				$sql = "SELECT * FROM $table_channels where name='$room'";
				$channel = $wpdb->get_row($sql);
				// $wpdb->query($sql);

				if ($channel)
					if ($channel->type>=2)
					{
						$ad = '';
						$debug='premiumChannel';
					}
				else $ad = urlencode(html_entity_decode(stripslashes($options['adsCode'])));
				else $debug='noChannel';


				?>x=1&ad=<?php echo $ad; ?>&loadstatus=1<?php echo '&debug=' . $debug;
				break;
			} //end case
			die();
		}
	}

}

//instantiate
if (class_exists("VWliveStreaming")) {
	$liveStreaming = new VWliveStreaming();
}

//Actions and Filters
if (isset($liveStreaming)) {

	register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
	register_activation_hook( __FILE__, array(&$liveStreaming, 'install' ) );

	add_action( 'init', array(&$liveStreaming, 'init'));
	add_action( 'parse_request', array(&$liveStreaming, 'parse_request'));

	add_action("plugins_loaded", array(&$liveStreaming, 'plugins_loaded'));
	add_action('admin_menu', array(&$liveStreaming, 'admin_menu'));

	add_action( 'admin_bar_menu', array(&$liveStreaming, 'admin_bar_menu'),100 );

	add_action('admin_head', array(&$liveStreaming, 'admin_head'));
	add_action( 'admin_init', array(&$liveStreaming, 'admin_init'));

	add_action( 'login_enqueue_scripts', array('VWliveStreaming','login_enqueue_scripts') );
	add_filter( 'login_headerurl', array('VWliveStreaming','login_headerurl'));

	//cron
	add_filter( 'cron_schedules', array(&$liveStreaming,'cron_schedules'));
	add_action( 'cron_10min_event', array(&$liveStreaming, 'cron_10min_event' ) );

	/* Only load code that needs BuddyPress to run once BP is loaded and initialized. */
	function liveStreamingBP_init()
	{
		if (class_exists('BP_Group_Extension')) require( dirname( __FILE__ ) . '/bp.php' );
	}

	add_action( 'bp_init', 'liveStreamingBP_init' );

	add_filter( "single_template", array(&$liveStreaming,'single_template') );

}
?>
<?php

namespace VideoWhisper\LiveStreaming;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

trait Options {
//define and edit settings

		
		static function getOptions()
		{
			$options = get_option('VWliveStreamingOptions');
			if (!$options) $options =  self::adminOptionsDefault();
			
			return $options;
		}
		
		//! Settings
		
		static function adminOptionsDefault()
		{
			$root_url = get_bloginfo( "url" ) . "/";
			$upload_dir = wp_upload_dir();

			return array(
				
				'restreamClean' => 1,
				'subcategory' => 'all',
				
				'interfaceClass' => '',
				'wallet' =>'MyCred',
				'walletMulti'=>'2',

				'balancePage' => '',
				'rateStarReview' => '1',

				'viewerInterface' => 'chat', //video/chat
				'htmlchatVisitorWrite' => '0',

				'userName' => 'user_nicename',
				'userPicture' => 'avatar',
				'profilePrefix' => $root_url . 'author/',
				'profilePrefixChannel' => $root_url . 'channel/',
				'loginLogo' => dirname(plugin_dir_url(__FILE__)) .'/login-logo.png',

				'postChannels' => '1',
				'userChannels' => '0',
				'anyChannels' => '0',

				'custom_post' => 'channel',
				'custom_post_video' => 'video',

				'postTemplate' => '+plugin',
				'channelUrl' => 'post',

				'disablePage' => '0',
				'disablePageC' => '0',
				'thumbWidth' => '240',
				'thumbHeight' => '180',
				'perPage' =>'6',

				'postName' => 'custom',


				'rtmp_server' => 'rtmp://localhost/videowhisper',

				'rtmp_restrict_ip'=>'',
				'webStatus'=> 'auto',

				'rtmp_amf' => 'AMF3',
				'httpstreamer' => 'https://[your-server]:1935/videowhisper-x/',
				'rtsp_server' => 'rtsp://[your-server]/videowhisper-x', //access WebRTC stream with sound from here
				'rtsp_server_publish' => 'rtsp://[user:password@][your-server]/videowhisper-x', //publish WebRTC stream here
				'ffmpegPath' => '/usr/local/bin/ffmpeg',
				'ffmpegSnapshotTimeout' => 'timeout -s KILL 5 ',
				'ffmpegSnapshotBackground' => '& ',
				'ffmpegConfiguration' => '1',
				'ffmpegTranscode' => '-c:v copy -c:a libfdk_aac -b:a 96',
				
				'transcodeRTC' => '0',
				'transcodeFromRTC' => '0',				
				'ffmpegTranscodeRTC' => '-c:v copy -c:a libopus', //transcode for RTC like ffmpeg -re -i source -acodec opus -vcodec libx264 -vprofile baseline -f rtsp rtsp://<wowza-instance>/rtsp-to-webrtc/my-stream

				'ffmpegTimeout' => '60',

				'streamsPath' => '/home/account/public_html/streams',
				'ipcamera_registration' => '0', //allows frontend registration for IP camera streams
				'transcodeReStreams' => '0',

				'restreamPause' => 1,
				'restreamTimeout' => 600,
				'restreamAccessedUser' => 1,
				'restreamAccessed' => 0,
				'restreamActiveOwner' => 1,
				'restreamActiveUser' => 0,

				'webrtc' =>'0', //enable webrtc
				'wsURLWebRTC' => 'wss://[wowza-server-with-ssl]:[port]/webrtc-session.json', // Wowza WebRTC WebSocket URL (wss with SSL certificate)
				'applicationWebRTC' => '[application-name]', // Wowza Application Name (configured or WebRTC usage)

				'webrtcVideoCodec' =>'VP8',
				'webrtcAudioCodec' =>'opus',

				'webrtcVideoBitrate' => 1000,

				'iptv' =>'0',
				'ipcams' =>'0',
				
				'playlists' =>'0',

				'canBroadcast' => 'members',
				'broadcastList' => 'Super Admin, Administrator, Editor, Author',
				'maxChannels' => '3',
				
				'externalKeys' => '1',
				'externalKeysTranscoder' => '1',
				'transcodeExternal' => '0',
			
				'rtmpStatus' => '0',


				'canWatch' => 'all',
				'watchList' => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber',
				'onlyVideo' => '0',
				'noEmbeds' => '0',

				'userWatchLimit' => '0',
				'userWatchInterval' => '2592000',
				'userWatchLimitDefault' => '108000',
				'userWatchLimits' => '',
				'userWatchLimitsConfig' => 'Administrator = 0
Super Admin = 0
Editor = 72000
Subscriber = 36000',
				'watchRoleParameters' => '',
				'watchRoleParametersConfig' =>'[disableChat]
Administrator = 0
Editor = 0

[disableUsers]
Administrator = 0
Editor = 0

[disableVideo]
Administrator = 0
Editor = 0

[writeText]
Administrator = 1
Editor = 1

[privateTextchat]
Administrator = 1
Editor = 1
				',

				'broadcasterRedirect' => '0',

				'premiumList' => 'Super Admin, Administrator, Editor, Author',
				'canWatchPremium' => 'all',
				'watchListPremium' => 'Super Admin, Administrator, Editor, Author, Contributor, Subscriber',

				'premiumLevelsNumber' =>'1',
				'premiumLevels' =>'',

				// 'pLogo' => '1',
				'broadcastTime' => '600',
				'watchTime' => '3000',
				'pBroadcastTime' => '0',
				'pWatchTime' => '0',
				'timeReset' => '30',
				'bannedNames' => 'bann1, bann2',

				'camResolution' => '640x480',
				'camFPS' => '15',

				'camBandwidth' => '75000',
				'camMaxBandwidth' => '75000',
				'pCamBandwidth' => '100000',
				'pCamMaxBandwidth' => '125000',

				'transcoding' => '0',
				'transcodingAuto' => '2',
				'transcodingManual' => '0',
				'transcodingWarning' => '2',

				'detect_hls' => 'ios',
				'detect_mpeg' => 'android',

				'videoCodec'=>'H264',
				'codecProfile' => 'baseline',
				'codecLevel' => '3.1',

				'soundCodec'=> 'Nellymoser',
				'soundQuality' => '9',
				'micRate' => '22',

				//! mobile settings
				'camResolutionMobile' => '480x360',
				'camFPSMobile' => '15',

				'camBandwidthMobile' => '40000',

				'videoCodecMobile'=>'H263',
				'codecProfileMobile' => 'baseline',
				'codecLevelMobile' => '3.1',

				'soundCodecMobile'=> 'Speex',
				'soundQualityMobile' => '9',
				'micRateMobile' => '22',
				//mobile:end


				'onlineExpiration0' =>'310',
				'onlineExpiration1' =>'40',
				'parameters' => '&bufferLive=1&bufferFull=1&showCredit=1&disconnectOnTimeout=1&offlineMessage=Channel+Offline&disableVideo=0&fillWindow=0&adsTimeout=15000&externalInterval=17000&statusInterval=59000&loaderProgress=1',
				'parametersBroadcaster' => '&bufferLive=2&bufferFull=2&showCamSettings=1&advancedCamSettings=1&configureSource=1&generateSnapshots=1&snapshotsTime=60000&room_limit=500&showTimer=1&showCredit=1&disconnectOnTimeout=1&externalInterval=11000&statusInterval=29000&loaderProgress=1&selectCam=1&selectMic=1',
				'layoutCode' => 'id=0&label=Video&x=10&y=45&width=325&height=298&resize=true&move=true; id=1&label=Chat&x=340&y=45&width=293&height=298&resize=true&move=true; id=2&label=Users&x=638&y=45&width=172&height=298&resize=true&move=true',
				'layoutCodeBroadcaster' => 'id=0&label=Webcam&x=10&y=40&width=242&height=235&resize=true&move=true; id=1&label=Chat&x=260&y=40&width=340&height=235&resize=true&move=true; id=2&label=Users&x=610&y=40&width=180&height=235&resize=true&move=true',
				'watchStyle' => 'width: 100%;
height: 400px;
border: solid 3px #999;',

				'overLogo' => $root_url .'wp-content/plugins/videowhisper-live-streaming-integration/ls/logo.png',
				'loaderImage' => '',

				'overLink' => 'https://videowhisper.com',
				'adServer' => 'ads',
				'adsInterval' => '20000',
				'adsCode' => '<B>Sample Ad</B><BR>Edit ads from plugin settings. Also edit  Ads Interval in milliseconds (0 to disable ad calls).  Also see <a href="http://www.adinchat.com" target="_blank"><U><B>AD in Chat</B></U></a> compatible ad management server for setting up ad rotation. Ads do not show on premium channels.',

				'cssCode' =>'title {
    font-family: Arial, Helvetica, _sans;
    font-size: 11;
    font-weight: bold;
    color: #FFFFFF;
    letter-spacing: 1;
    text-decoration: none;
}

story {
    font-family: Verdana, Arial, Helvetica, _sans;
    font-size: 14;
    font-weight: normal;
    color: #FFFFFF;
}',
				'translationCode' => '<t text="Video is Disabled" translation="Video is Disabled"/>
<t text="Bold" translation="Bold"/>
<t text="Sound is Enabled" translation="Sound is Enabled"/>
<t text="Publish a video stream using the settings below without any spaces." translation="Publish a video stream using the settings below without any spaces."/>
<t text="Click Preview for Streaming Settings" translation="Click Preview for Streaming Settings"/>
<t text="DVD NTSC" translation="DVD NTSC"/>
<t text="DVD PAL" translation="DVD PAL"/>
<t text="Video Source" translation="Video Source"/>
<t text="Send" translation="Send"/>
<t text="Cinema" translation="Cinema"/>
<t text="Update Show Title" translation="Update Show Title"/>
<t text="Public Channel: Click to Copy" translation="Public Channel: Click to Copy"/>
<t text="Channel Link" translation="Channel Link"/>
<t text="Kick" translation="Kick"/>
<t text="Embed Channel HTML Code" translation="Embed Channel HTML Code"/>
<t text="Open In Browser" translation="Open In Browser"/>
<t text="Embed Video HTML Code" translation="Embed Video HTML Code"/>
<t text="Snapshot Image Link" translation="Snapshot Image Link"/>
<t text="SD" translation="SD"/>
<t text="External Encoder" translation="External Encoder"/>
<t text="Source" translation="Source"/>
<t text="Very Low" translation="Very Low"/>
<t text="Low" translation="Low"/>
<t text="HDTV" translation="HDTV"/>
<t text="Webcam" translation="Webcam"/>
<t text="Resolution" translation="Resolution"/>
<t text="Emoticons" translation="Emoticons"/>
<t text="HDCAM" translation="HDCAM"/>
<t text="FullHD" translation="FullHD"/>
<t text="Preview Shows as Compressed" translation="Preview Shows as Compressed"/>
<t text="Rate" translation="Rate"/>
<t text="Very Good" translation="Very Good"/>
<t text="Preview Shows as Captured" translation="Preview Shows as Captured"/>
<t text="Framerate" translation="Framerate"/>
<t text="High" translation="High"/>
<t text="Toggle Preview Compression" translation="Toggle Preview Compression"/>
<t text="Latency" translation="Latency"/>
<t text="CD" translation="CD"/>
<t text="Your connection performance:" translation="Your connection performance:"/>
<t text="Small Delay" translation="Small Delay"/>
<t text="Sound Effects" translation="Sound Effects"/>
<t text="Username" translation="Nickname"/>
<t text="Medium Delay" translation="Medium Delay"/>
<t text="Toggle Microphone" translation="Toggle Microphone"/>
<t text="Video is Enabled" translation="Video is Enabled"/>
<t text="Radio" translation="Radio"/>
<t text="Talk" translation="Talk"/>
<t text="Viewers" translation="Viewers"/>
<t text="Toggle External Encoder" translation="Toggle External Encoder"/>
<t text="Sound is Disabled" translation="Sound is Disabled"/>
<t text="Sound Fx" translation="Sound Effects"/>
<t text="Good" translation="Good"/>
<t text="Toggle Webcam" translation="Toggle Webcam"/>
<t text="Bandwidth" translation="Bandwidth"/>
<t text="Underline" translation="Underline"/>
<t text="Select Microphone Device" translation="Select Microphone Device"/>
<t text="Italic" translation="Italic"/>
<t text="Select Webcam Device" translation="Select Webcam Device"/>
<t text="Big Delay" translation="Big Delay"/>
<t text="Excellent" translation="Excellent"/>
<t text="Apply Settings" translation="Apply Settings"/>
<t text="Very High" translation="Very High"/>',

				'customCSS' => <<<HTMLCODE
<style type="text/css">

/* Theme Fixes */
.site-inner {
max-width: 100%;
}

.ui > .item {
  display: block !important;
}


/* Listings */
.videowhisperChannel
{
position: relative;
display:inline-block;

	border:1px solid #aaa;
	background-color:#777;
	padding: 0px;
	margin: 2px;

	width: 240px;
    height: 180px;
}

.videowhisperChannel:hover {
	border:1px solid #fff;
}

.videowhisperChannel IMG
{
padding: 0px;
margin: 0px;
border: 0px;
}

.videowhisperTitle
{
position: absolute;
top:5px;
left:5px;
font-size: 20px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperTime
{
position: absolute;
bottom:5px;
left:5px;
font-size: 15px;
color: #FFF;
text-shadow:1px 1px 1px #333;
}

.videowhisperChannelRating
{
position: absolute;
bottom: 5px;
right:5px;
font-size: 15px;
color: #FFF;
text-shadow:1px 1px 1px #333;
z-index: 10;
}

</style>

HTMLCODE
				,
				'uploadsPath' => $upload_dir['basedir'] . '/vwls',

				'tokenKey' => 'VideoWhisper',
				'webKey' => 'VideoWhisper',
				'manualArchiving' => '',

				'serverRTMFP' => 'rtmfp://stratus.adobe.com/f1533cc06e4de4b56399b10d-1a624022ff71/',
				'p2pGroup' => 'VideoWhisper',
				'supportRTMP' => '1',
				'supportP2P' => '0',
				'alwaysRTMP' => '1',
				'alwaysP2P' => '0',
				'alwaysWatch' => '1',
				'disableBandwidthDetection' => '1',
				'mycred' => '1',
				'tips' => 1,
				'tipRatio' => '0.90',
				'tipOptions' => '<tips>
<tip amount="1" label="1$ Tip" note="Like!" sound="coins1.mp3" image="gift1.png"/>
<tip amount="2" label="2$ Tip" note="Big Like!" sound="coins2.mp3" image="gift2.png"/>
<tip amount="5" label="5$ Gift" note="Great!" sound="coins2.mp3" image="gift3.png"/>
<tip amount="10" label="10$ Gift" note="Excellent!" sound="register.mp3" image="gift4.png"/>
<tip amount="20" label="20$ Gift" note="Ultimate!" sound="register.mp3" image="gift5.png"/>
</tips>',
				'tipCooldown'=> '15',

				'eula_txt' =>'The following Terms of Use (the "Terms") is a binding agreement between you, either an individual subscriber, customer, member, or user of at least 18 years of age or a single entity ("you", or collectively "Users") and owners of this application, service site and networks that allow for the distribution and reception of video, audio, chat and other content (the "Service").

By accessing the Service and/or by clicking "I agree", you agree to be bound by these Terms of Use. You hereby represent and warrant to us that you are at least eighteen (18) years of age or and otherwise capable of entering into and performing legal agreements, and that you agree to be bound by the following Terms and Conditions. If you use the Service on behalf of a business, you hereby represent to us that you have the authority to bind that business and your acceptance of these Terms of Use will be treated as acceptance by that business. In that event, "you" and "your" will refer to that business in these Terms of Use.

Prohibited Conduct

The Services may include interactive areas or services (" Interactive Areas ") in which you or other users may create, post or store content, messages, materials, data, information, text, music, sound, photos, video, graphics, applications, code or other items or materials on the Services ("User Content" and collectively with Broadcaster Content, " Content "). You are solely responsible for your use of such Interactive Areas and use them at your own risk. BY USING THE SERVICE, INCLUDING THE INTERACTIVE AREAS, YOU AGREE NOT TO violate any law, contract, intellectual property or other third-party right or commit a tort, and that you are solely responsible for your conduct while on the Service. You agree that you will abide by these Terms of Service and will not:

use the Service for any purposes other than to disseminate or receive original or appropriately licensed content and/or to access the Service as such services are offered by us;

rent, lease, loan, sell, resell, sublicense, distribute or otherwise transfer the licenses granted herein;

post, upload, or distribute any defamatory, libelous, or inaccurate Content;

impersonate any person or entity, falsely claim an affiliation with any person or entity, or access the Service accounts of others without permission, forge another persons digital signature, misrepresent the source, identity, or content of information transmitted via the Service, or perform any other similar fraudulent activity;

delete the copyright or other proprietary rights notices on the Service or Content;

make unsolicited offers, advertisements, proposals, or send junk mail or spam to other Users of the Service, including, without limitation, unsolicited advertising, promotional materials, or other solicitation material, bulk mailing of commercial advertising, chain mail, informational announcements, charity requests, petitions for signatures, or any of the foregoing related to promotional giveaways (such as raffles and contests), and other similar activities;

harvest or collect the email addresses or other contact information of other users from the Service for the purpose of sending spam or other commercial messages;

use the Service for any illegal purpose, or in violation of any local, state, national, or international law, including, without limitation, laws governing intellectual property and other proprietary rights, and data protection and privacy;

defame, harass, abuse, threaten or defraud Users of the Service, or collect, or attempt to collect, personal information about Users or third parties without their consent;

remove, circumvent, disable, damage or otherwise interfere with security-related features of the Service or Content, features that prevent or restrict use or copying of any content accessible through the Service, or features that enforce limitations on the use of the Service or Content;

reverse engineer, decompile, disassemble or otherwise attempt to discover the source code of the Service or any part thereof, except and only to the extent that such activity is expressly permitted by applicable law notwithstanding this limitation;

modify, adapt, translate or create derivative works based upon the Service or any part thereof, except and only to the extent that such activity is expressly permitted by applicable law notwithstanding this limitation;

intentionally interfere with or damage operation of the Service or any user enjoyment of them, by any means, including uploading or otherwise disseminating viruses, adware, spyware, worms, or other malicious code;

relay email from a third party mail servers without the permission of that third party;

use any robot, spider, scraper, crawler or other automated means to access the Service for any purpose or bypass any measures we may use to prevent or restrict access to the Service;

manipulate identifiers in order to disguise the origin of any Content transmitted through the Service;

interfere with or disrupt the Service or servers or networks connected to the Service, or disobey any requirements, procedures, policies or regulations of networks connected to the Service;use the Service in any manner that could interfere with, disrupt, negatively affect or inhibit other users from fully enjoying the Service, or that could damage, disable, overburden or impair the functioning of the Service in any manner;

use or attempt to use another user account without authorization from such user and us;

attempt to circumvent any content filtering techniques we employ, or attempt to access any service or area of the Service that you are not authorized to access; or

attempt to indicate in any manner that you have a relationship with us or that we have endorsed you or any products or services for any purpose.

Further, BY USING THE SERVICE, INCLUDING THE INTERACTIVE AREAS YOU AGREE NOT TO post, upload to, transmit, distribute, store, create or otherwise publish through the Service any of the following:

Content that would constitute, encourage or provide instructions for a criminal offense, violate the rights of any party, or that would otherwise create liability or violate any local, state, national or international law or regulation;

Content that may infringe any patent, trademark, trade secret, copyright or other intellectual or proprietary right of any party. By posting any Content, you represent and warrant that you have the lawful right to distribute and reproduce such Content;

Content that is unlawful, libelous, defamatory, obscene, pornographic, indecent, lewd, suggestive, harassing, threatening, invasive of privacy or publicity rights, abusive, inflammatory, fraudulent or otherwise objectionable;

Content that impersonates any person or entity or otherwise misrepresents your affiliation with a person or entity;

private information of any third party, including, without limitation, addresses, phone numbers, email addresses, Social Security numbers and credit card numbers;

viruses, corrupted data or other harmful, disruptive or destructive files; and

Content that, in the sole judgment of Service moderators, is objectionable or which restricts or inhibits any other person from using or enjoying the Interactive Areas or the Service, or which may expose us or our users to any harm or liability of any type.

Service takes no responsibility and assumes no liability for any Content posted, stored or uploaded by you or any third party, or for any loss or damage thereto, nor is liable for any mistakes, defamation, slander, libel, omissions, falsehoods, obscenity, pornography or profanity you may encounter. Your use of the Service is at your own risk. Enforcement of the user content or conduct rules set forth in these Terms of Service is solely at Service discretion, and failure to enforce such rules in some instances does not constitute a waiver of our right to enforce such rules in other instances. In addition, these rules do not create any private right of action on the part of any third party or any reasonable expectation that the Service will not contain any content that is prohibited by such rules. As a provider of interactive services, Service is not liable for any statements, representations or Content provided by our users in any public forum, personal home page or other Interactive Area. Service does not endorse any Content or any opinion, recommendation or advice expressed therein, and Service expressly disclaims any and all liability in connection with Content. Although Service has no obligation to screen, edit or monitor any of the Content posted in any Interactive Area, Service reserves the right, and has absolute discretion, to remove, screen or edit any Content posted or stored on the Service at any time and for any reason without notice, and you are solely responsible for creating backup copies of and replacing any Content you post or store on the Service at your sole cost and expense. Any use of the Interactive Areas or other portions of the Service in violation of the foregoing violates these Terms and may result in, among other things, termination or suspension of your rights to use the Interactive Areas and/or the Service.
',
				'crossdomain_xml' =>'<cross-domain-policy>
<allow-access-from domain="*"/>
<site-control permitted-cross-domain-policies="master-only"/>
</cross-domain-policy>',
				'videowhisper' => 0
			);

		}

		static function setupOptions()
		{

			$adminOptions = self::adminOptionsDefault();

			$features = self::roomFeatures();
			foreach ($features as $key=>$feature) if ($feature['installed'])  $adminOptions[$key] = $feature['default'];

				$options = get_option('VWliveStreamingOptions');
			if (!empty($options)) {
				foreach ($options as $key => $option)
					$adminOptions[$key] = $option;
			}
			update_option('VWliveStreamingOptions', $adminOptions);


			return $adminOptions;
		}


		function settingsPage()
		{
			$options = self::setupOptions();
			$optionsDefault = self::adminOptionsDefault();

			if (isset($_POST)) if (!empty($_POST))
				{

					$nonce = $_REQUEST['_wpnonce'];
					if ( ! wp_verify_nonce( $nonce, 'vwsec' ) )
					{
						echo 'Invalid nonce!';
						exit;
					}


					foreach ($options as $key => $value)
						if (isset($_POST[$key]))
							if (!in_array($key, array('tipOptions', 'customCSS', 'userWatchLimitsConfig', 'watchRoleParametersConfig')))  //some settings are processed individually
								$options[$key] = sanitize_textarea_field($_POST[$key]);
							else $options[$key] = $_POST[$key];

							//config parsing
							if (isset($_POST['userWatchLimitsConfig']))
								$options['userWatchLimits'] = parse_ini_string(sanitize_textarea_field($_POST['userWatchLimitsConfig']));

							if (isset($_POST['watchRoleParametersConfig']))
								$options['watchRoleParameters'] = parse_ini_string(sanitize_textarea_field($_POST['watchRoleParametersConfig']), true);


							update_option('VWliveStreamingOptions', $options);
				}

			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'setup';
?>


<div class="wrap">
<h2>Broadcast Live Video - Live Streaming by VideoWhisper.com</h2>

<h2 class="nav-tab-wrapper">
	<a href="admin.php?page=live-streaming&tab=server" class="nav-tab <?php echo $active_tab=='server'?'nav-tab-active':'';?>">Server / Streaming</a>
    <a href="admin.php?page=live-streaming&tab=hls" class="nav-tab <?php echo $active_tab=='hls'?'nav-tab-active':'';?>">FFMPEG / Transcoding</a>
    <a href="admin.php?page=live-streaming&tab=webrtc" class="nav-tab <?php echo $active_tab=='webrtc'?'nav-tab-active':'';?>">WebRTC</a>
    	
		<a href="admin.php?page=live-streaming&tab=pages" class="nav-tab <?php echo $active_tab=='pages'?'nav-tab-active':'';?>">Pages</a>

	<a href="admin.php?page=live-streaming&tab=general" class="nav-tab <?php echo $active_tab=='general'?'nav-tab-active':'';?>">Integration</a>
		<a href="admin.php?page=live-streaming&tab=appearance" class="nav-tab <?php echo $active_tab=='appearance'?'nav-tab-active':'';?>">Appearance</a>

    <a href="admin.php?page=live-streaming&tab=broadcaster" class="nav-tab <?php echo $active_tab=='broadcaster'?'nav-tab-active':'';?>">Broadcaster</a>
    <a href="admin.php?page=live-streaming&tab=broadcast-flash" class="nav-tab <?php echo $active_tab=='broadcast-flash'?'nav-tab-active':'';?>">Broadcast Web</a>
    <a href="admin.php?page=live-streaming&tab=premium" class="nav-tab <?php echo $active_tab=='premium'?'nav-tab-active':'';?>">Membership Levels</a>
    <a href="admin.php?page=live-streaming&tab=features" class="nav-tab <?php echo $active_tab=='features'?'nav-tab-active':'';?>">Channel Features</a>
  
     <a href="admin.php?page=live-streaming&tab=external" class="nav-tab <?php echo $active_tab=='external'?'nav-tab-active':'';?>">External Encoders</a>
     <a href="admin.php?page=live-streaming&tab=iptv" class="nav-tab <?php echo $active_tab=='iptv'?'nav-tab-active':'';?>">IPTV / Pull</a>  
     <a href="admin.php?page=live-streaming&tab=stream" class="nav-tab <?php echo $active_tab=='stream'?'nav-tab-active':'';?>">IP Cam / Streams</a>
    <a href="admin.php?page=live-streaming&tab=playlists" class="nav-tab <?php echo $active_tab=='playlists'?'nav-tab-active':'';?>">Playlists Scheduler</a>
    
    <a href="admin.php?page=live-streaming&tab=watcher" class="nav-tab <?php echo $active_tab=='watcher'?'nav-tab-active':'';?>">Watch Players</a>
    <a href="admin.php?page=live-streaming&tab=watch-limit" class="nav-tab <?php echo $active_tab=='watch-limit'?'nav-tab-active':'';?>">Watch Limit</a>
    <a href="admin.php?page=live-streaming&tab=watch-params" class="nav-tab <?php echo $active_tab=='watch-params'?'nav-tab-active':'';?>">Watch Params</a>
    <a href="admin.php?page=live-streaming&tab=billing" class="nav-tab <?php echo $active_tab=='billing'?'nav-tab-active':'';?>">Billing</a>
    <a href="admin.php?page=live-streaming&tab=tips" class="nav-tab <?php echo $active_tab=='tips'?'nav-tab-active':'';?>">Tips</a>

    <a href="admin.php?page=live-streaming&tab=app" class="nav-tab <?php echo $active_tab=='app'?'nav-tab-active':'';?>">Custom App</a>

 	<a href="admin.php?page=live-streaming&tab=translate" class="nav-tab <?php echo $active_tab=='translate'?'nav-tab-active':'';?>">Translate</a>

	<a href="admin.php?page=live-streaming&tab=import" class="nav-tab <?php echo $active_tab=='import'?'nav-tab-active':'';?>">Import Settings</a>
  	<a href="admin.php?page=live-streaming&tab=reset" class="nav-tab <?php echo $active_tab=='reset'?'nav-tab-active':'';?>"><?php _e('Resets','live-streaming'); ?></a>
    <a href="admin.php?page=live-streaming&tab=troubleshooting" class="nav-tab <?php echo $active_tab=='troubleshooting'?'nav-tab-active':'';?>">Requirements & Troubleshooting</a>
    
    <a href="admin.php?page=live-streaming&tab=support" class="nav-tab <?php echo $active_tab=='support'?'nav-tab-active':'';?>">Support</a>

    <a href="admin.php?page=live-streaming&tab=setup" class="nav-tab <?php echo $active_tab=='setup'?'nav-tab-active':'';?>">Setup</a>

</h2>

<form method="post" action="<?php echo wp_nonce_url($_SERVER["REQUEST_URI"], 'vwsec'); ?>">

<?php
	
			switch ($active_tab)
			{

			case 'import':
?>
<h3><?php _e('Import Options','live-streaming'); ?></h3>
Import/Export plugin settings and options.
<?php
	if ($importConfig = $_POST['importConfig'])
	{
		echo '<br>Importing: ' ;
		$optionsImport = parse_ini_string(stripslashes($importConfig), false);
		//var_dump($optionsImport);		
		
		foreach ($optionsImport as $key => $value) 
		{
			echo "<br> - $key = $value";
			$options[$key] = $value;
		}
		update_option('VWliveStreamingOptions', $options);
	}
?>
<h4>Import Plugin Settings</h4>
<textarea name="importConfig" id="importConfig" cols="120" rows="12"></textarea>
<br>Quick fill settings as option = "value".

<h4>Export Current Plugin Settings</h4>
<textarea readonly cols="120" rows="10">[Plugin Settings]<?php
foreach ($options as $key => $value) echo "\n$key = " . '"'. htmlentities(stripslashes($value)) . '"';
?></textarea>

<h4>Export Default Plugin Settings</h4>
<textarea readonly cols="120" rows="10">[Plugin Settings]<?php
foreach ($optionsDefault as $key => $value) echo "\n$key = " . '"'. htmlentities(stripslashes($value)) . '"';
?></textarea>

<h5>Warning: Saving will set settings provided in Import Plugin Settings box.</h5>
<?php

			break;
			
						
			case 'pages';
?>
<h3><?php _e('Setup Pages','live-streaming'); ?></h3>

<?php			
			if ($_POST['submit'])
			{
			echo 'Saving pages setup...';
			$page_id = get_option("vwls_page_manage");
			if ($page_id != '-1' && $options['disablePage']!='0') self::deletePages();

			$page_idC = get_option("vwls_page_channels");
			if ($page_idC != '-1' && $options['disablePageC']!='0') self::deletePages();
			
			self::updatePages();
			}

submit_button( __('Setup Pages','live-streaming') );
?>
Use this to setup pages on your site. Pages with main feature shortcodes are required to: broadcast live channels, access channels. After setting up these pages you should add the feature pages to site menus for users to access.
A sample VideoWhisper menu will also be added when adding pages: can be configured to show in a menu section depending on theme.
<br>You can manage these anytime from backend: <a href="edit.php?post_type=page">pages</a> and <a href="nav-menus.php">menus</a>.
<BR><?php echo self::requirementRender('setup_pages') ?>

<h4>Page for Management</h4>
<p>Add channel management page (Page ID <a href='post.php?post=<?php echo get_option("vwls_page_manage"); ?>&action=edit'><?php echo get_option("vwls_page_manage"); ?></a>) with shortcode [videowhisper_channel_manage]</p>
<select name="disablePage" id="disablePage">
  <option value="0" <?php echo $options['disablePage']=='0'?"selected":""?>>Yes</option>
  <option value="1" <?php echo $options['disablePage']=='1'?"selected":""?>>No</option>
</select>


<h4>Page for Channels</h4>
<p>Add channel list page (Page ID <a href='post.php?post=<?php echo get_option("vwls_page_channels"); ?>&action=edit'><?php echo get_option("vwls_page_channels"); ?></a>) with shortcode [videowhisper_channels]</p>
<select name="disablePageC" id="disablePageC">
  <option value="0" <?php echo $options['disablePageC']=='0'?"selected":""?>>Yes</option>
  <option value="1" <?php echo $options['disablePageC']=='1'?"selected":""?>>No</option>
</select>


<h4>Manage Balance Page</h4>
<select name="balancePage" id="balancePage">
<?php

				$args = array(
					'sort_order' => 'asc',
					'sort_column' => 'post_title',
					'hierarchical' => 1,
					'post_type' => 'page',
					'post_status' => 'publish'
				);
				$sPages = get_pages($args);
				foreach ($sPages as $sPage) echo '<option value="' . $sPage->ID . '" '. ($options['balancePage'] == ($sPage->ID) ?"selected":"") .'>' . $sPage->post_title . '</option>' . "\r\n";
?>
</select>
<br>Page linked from balance section, usually a page where registered users can buy credits. Recommended: My Wallet (created by Paid Membership & Content Plugin)
<?php
			
			break;
				
				case 'setup':
?>
<h3><?php _e('Setup Overview','live-streaming'); ?></h3>


 + Before setting up, make sure you have necessary <b>hosting requirements, for live video streaming</b>. This plugin has <a href="https://videowhisper.com/?p=Requirements" title="Live Streaming Requirements" target="_requirements">requirements</a> beyond regular WordPress hosting specifications and needs specific live streaming services and video tools. Recommended hosting with all streaming capabilities and video tools required for this solution: <a href="https://webrtchost.com/hosting-plans/#Complete-Hosting">Turnkey Complete Streaming & Web Hosting</a> from WebRTC Host by Videowhisper.
<br> + This plugin is designed to setup a turnkey live streaming site, changing major WP blog features. Set it up on a development environment as it can alter functionality of existing sites. To be able to revert changes, before setting up, make a recovering backup using <a target="_backup" href="https://updraftplus.com/?afref=924">Updraft Plus</a> or other backup tool.
<br> + To setup this plugin see <a href="admin.php?page=live-streaming-docs">Backend Documentation</a>, the project page <a href="https://broadcastlivevideo.com/setup-tutorial/" target="_documentation">BroadcastLiveVideo Setup Tutorial</a> and requirements checkpoints list on this page.
<br>If not sure about how to proceed or need clarifications, <a href="https://videowhisper.com/tickets_submit.php">contact plugin developers</a>. 

<p><a class="button primary" href="admin.php?page=live-streaming-docs">Backend Setup Tutorial & Documentation</a></p>

<h3><?php _e('Setup Checkpoints','live-streaming'); ?></h3>

This section lists main requirements and checkpoints for setting up and using this solution. 
<?php
	
	// self::requirementUpdate('setup', '1');
	
	//handle item skips
	$unskip = sanitize_file_name( $_GET['unskip']);
	if ($unskip) self::requirementUpdate($unskip, 0, 'skip');
	
	$skip = sanitize_file_name( $_GET['skip']);
	if ($skip) self::requirementUpdate($skip, 1, 'skip');
	
	$check = sanitize_file_name( $_GET['check']);
	if ($check) self::requirementUpdate($check, 0);

	$done = sanitize_file_name( $_GET['done']);
	if ($done) self::requirementUpdate($done, 1);
	
	//accessed setup page: easy
	self::requirementMet('setup');
	
	//list requirements
	$requirements = self::requirementsGet();
	
	$rDone = 0;


	foreach ($requirements as $label => $requirement) 
	{
		$html = self::requirementRender($label, 'overview', $requirement);
		
	$status = self::requirementStatus($requirement);
	$skip = self::requirementStatus($requirement, 'skip'); 
	
	
	if ($status) {$htmlDone .= $html; $rDone++;}
	elseif ($skip) $htmlSkip .= $html;
	else $htmlPending .= $html;
	}

		if ($htmlPending) echo '<h4>To Do:</h4>' . $htmlPending;
		if ($htmlSkip) echo '<h4>Skipped:</h4>' . $htmlSkip;		
		if ($htmlDone) echo '<h4>Done ('.$rDone.'):</h4>' . $htmlDone;
?>
* These requirements are updated with checks and checkpoints from certain pages, sections, scripts. Certain requirements may take longer to update (in example session control updates when there are live streams and streaming server calls the web server to notify). When plugin upgrades include more checks to assist in reviewing setup, these will initially show as required until checkpoint.
<?php	
	//var_dump($requirements);	
		break;

		case 'translate':
?>

<h3>Translations</h3>
Translate solution in different language.

Software is composed of applications and integration code (plugin) that shows features on WP pages.
<h4>Translation Code for Chat Application</h4>
Flash application texts can be translated from this section.
<?php
				$translationCode = stripslashes($options['translationCode']);

				$options['translationCode'] = htmlentities(stripslashes($options['translationCode']));
?>
<br><textarea name="translationCode" id="translationCode" cols="100" rows="5"><?php echo $options['translationCode']?></textarea>
<br>Generate by writing and sending "/videowhisper translation" in chat (contains xml tags with text and translation attributes). Texts are added to list only after being shown once in interface. If any texts don't show up in generated list you can manually add new entries for these. Same translation file is used for all interfaces so setting should cumulate all translation texts.
As translations are configured using XML, any strings containing special chars should be <a target="_xmlencoder" href="http://coderstoolbox.net/string/#!encoding=xml&action=encode&charset=us_ascii">XML Encoded</a>.
Default:<br><textarea readonly cols="100" rows="4"><?php echo $optionsDefault['translationCode']?></textarea>

<h4>Translation for Solution Features, Pages</h4>

Some translations for plugin are available in "languages" plugin folder and you can edit/adjust or add new languages using a translation plugin like <a href="https://wordpress.org/plugins/loco-translate/">Loco Translate</a> : From Loco Translate > Plugins > Broadcast Live Video - Live Streaming you can edit existing languages or add new languages.
<br>You can also start with an automated translator application like Poedit, translate more texts with Google Translate and at the end have a human translator make final adjustments. You can contact VideoWhisper support and provide links to new translation files if you want these included in future plugin updates.

<BR>Some customizable labels and features can be translated from plugin settings.


<?php
				break;


			case 'troubleshooting':
?>
<h3><?php _e('Troubleshooting Requirements','live-streaming'); ?></h3>
This section includes some tests, reporting and logs for troubleshooting various requirements.
<?php

				//$pluginInfo = get_plugin_data(__FILE__);
				//echo "<BR>Plugin Name: " . $pluginInfo['Name'];
				//echo "<BR>Plugin Version: " . $pluginInfo['Version'];

echo "<h4>Web Host</h4>";
echo "Web Name: " . $_SERVER['SERVER_NAME'];
echo "<br>Web IP: " . $_SERVER['SERVER_ADDR'];
echo "<br>Site Path: " . $_SERVER['DOCUMENT_ROOT'];
echo "<br>Server Hostname: " . gethostname();
echo "<br>Server OS: " . php_uname();
echo "<br>Web Server: " . $_SERVER['SERVER_SOFTWARE'];
echo "<br>Connection: " . $_SERVER['HTTP_CONNECTION'];
echo "<br>Client IP: " . $_SERVER['REMOTE_ADDR'];
echo "<br>Client Browser: " . $_SERVER['HTTP_USER_AGENT'];
    

echo "<h4>FFMPEG</h4>";

				echo "exec: ";
				if(function_exists('exec'))
				{
					echo "function is enabled";

					if(exec('echo EXEC') == 'EXEC')
					{
						echo ' and works';
						$fexec =1;
					}
					else echo ' <b>but does not work</b>';

				}else echo '<b>function is not enabled</b><BR>PHP function "exec" is required to run FFMPEG. Current hosting settings are not compatible with this functionality.';

				echo '<br>PHP script owner: ' . get_current_user() . ' #'. getmyuid();
				echo '<br>Process effective owner: ' . posix_getpwuid(posix_geteuid())['name'] . ' #'. posix_geteuid();


				echo '<br>exec("whoami"): ';
				$cmd = "whoami";
				$output="";
				exec($cmd, $output, $returnvalue);
				foreach ($output as $outp) echo $outp;

				$cmd = $options['ffmpegPath'] . ' -version 2>&1';
				// $cmd ='timeout -s KILL 3 ' . $options['ffmpegPath'] . ' -version';

				echo "<br><BR>FFMPEG ($cmd): ";

				$output="";
				exec($cmd, $output, $returnvalue);
				$ffmpeg = 0;
				if ($returnvalue == 127)  echo "<b>Warning: not detected: $cmd</b>";
				else
				{
					echo "found";

					if ($returnvalue != 126)
					{
						echo ' / Output:<br><textarea readonly cols="120" rows="4">';
						echo join("\n", $output);
						echo '</textarea>';
						$ffmpeg =1 ;
					}else
						echo ' but is NOT executable by current user: ' . $processUser;
				}

				echo "<br>FFMPEG is a video tool required on web hosting for video stream snapshots, analysis (detecting codecs), transcoding. Usually not available on budget web hosting and available on premium video hosting.";


if ($ffmpeg)
{
				$cmd =$options['ffmpegPath'] . ' -codecs 2>&1';
				exec($cmd, $output, $returnvalue);
				
						echo "<br><br> + Codec libraries ($cmd):";
						echo ' / Output:<br><textarea readonly cols="120" rows="4">';
						echo join("\n", $output);
						echo '</textarea>';
						
				//detect codecs
				$hlsAudioCodec = ''; //hlsAudioCodec
				if ($output) if (count($output))
					{
						foreach (array('h264', 'vp6','speex', 'nellymoser', 'aacplus', 'vo_aacenc', 'faac', 'fdk_aac', 'vp8', 'vp9', 'opus') as $cod)
						{
							$det=0; $outd="";
							echo "<BR>$cod : ";
							foreach ($output as $outp) if (strstr($outp,$cod)) { $det=1; $outd=$outp; };

							if ($det) echo "detected ($outd)";
							elseif (in_array($cod,array('aacplus', 'vo_aacenc', 'faac', 'fdk_aac'))) echo "lib$cod is missing but other aac codec may be available";
							else echo "<b>missing: configure and install FFMPEG with lib$cod if you don't have another library for that codec</b>";

							if ($det && in_array($cod,array('aacplus', 'vo_aacenc', 'faac', 'fdk_aac')))  $hlsAudioCodec = 'lib'. $cod;
						}
					}
?>
<BR>You need only 1 AAC codec for transcoding to AAC. Depending on <a href="https://trac.ffmpeg.org/wiki/Encode/AAC#libfaac">AAC library available on your system</a> you may need to update transcoding parameters. Latest FFMPEG also includes a native encoder (aac).
<?php

			    $cmd =$options['ffmpegPath'] . ' -protocols 2>&1';
				exec($cmd, $output, $returnvalue);
				
						echo "<br><br> + Codecs & Protocols ($cmd):";
						echo ' / Output:<br><textarea readonly cols="120" rows="4">';
						echo join("\n", $output);
						echo '</textarea>';



//image handling test

				$src = plugin_dir_path( dirname(__FILE__)) . 'screenshot-5.png';
				$dest =  $options['uploadsPath']. '/ffmpeg-test.png';

				$cmd = $options['ffmpegPath'] . " -y -i '$src' -vf scale=320:-1 '$dest' 2>&1";


				flush();

				echo "<br><BR> + FFMPEG Image Resize Test ($cmd): ";

				$output="";
				exec($cmd, $output, $returnvalue);

				if ($returnvalue == 127)  echo "<br>Warning: not detected ($returnvalue): $cmd :". $output[0];
				else
				{
					if ($returnvalue != 126)
					{
						echo 'Output:<br><textarea readonly cols="120" rows="4">';
						echo join("\n", $output);
						echo '</textarea>';
					}else
						echo ' but is NOT executable by current user. ';
				}

				echo "<br>Output ($dest):";
				if (file_exists($dest)) echo 'found <a href='. self::path2url($dest) .' target="_blank">Open</a>';
				else echo 'not found (Failed): review ffmpeg configuration and process/file ownership/permissions';
}


				echo "<h4>FFMPEG Logs</h4>Logs from last operations attempted, for troubleshooting. Make sure FFMPEG is functional and scripts can write the log files. Then try features that should call this functionality.";
				
				$lastLog = $options['uploadsPath'] . '/lastLog-streamSnapshot.txt';
				echo "<h5>FFMPEG Stream Snapshot</h5>  $lastLog : ";
				if (!file_exists($lastLog)) echo 'Not found, yet!';
				else
				{
					$log = self::varLoad($lastLog);
					echo '<br>Time: ' . date(DATE_RFC2822, $log['time']);
					echo '<br>Command: ' . $log['cmd'];
					echo '<br>Return: ' . $log['return'];
					echo '<br>Output[0]: ' . $log['output0'];
					echo '<br>File: ' . $log['file'];
					if (!file_exists($log['file'])) echo ' Log file not found!';
					else echo '<br><textarea readonly cols="100" rows="4">' . htmlspecialchars( file_get_contents( $log['file'] ) ). '</textarea>';
				}

				$lastLog = $options['uploadsPath'] . '/lastLog-streamInfo.txt';
				echo "<h5>FFMPEG Stream Info</h5>  $lastLog : ";
				if (!file_exists($lastLog)) echo 'Not found, yet!';
				else
				{
					$log = self::varLoad($lastLog);
					echo '<br>Time: ' . date(DATE_RFC2822, $log['time']);
					echo '<br>Command: ' . $log['cmd'];
					echo '<br>Return: ' . $log['return'];
					echo '<br>Output[0]: ' . $log['output0'];
					echo '<br>File: ' . $log['file'];
					if (!file_exists($log['file'])) echo ' Log file not found!';
					else echo '<br><textarea readonly cols="100" rows="4">' . htmlspecialchars( file_get_contents( $log['file'] ) ). '</textarea>';
				}

				$lastLog = $options['uploadsPath'] . '/lastLog-iptvStart.txt';
				echo "<h5>IPTV Stream Start</h5>  $lastLog : ";
				if (!file_exists($lastLog)) echo 'Not found, yet!';
				else
				{
					$log = self::varLoad($lastLog);
					echo '<br>Time: ' . date(DATE_RFC2822, $log['time']);
					echo '<br>Command: ' . $log['cmd'];
					echo '<br>Return: ' . $log['return'];
					echo '<br>Output[0]: ' . $log['output0'];
					echo '<br>File: ' . $log['file'];
					if (!file_exists($log['file'])) echo ' Log file not found!';
					else echo '<br><textarea readonly cols="100" rows="4">' . htmlspecialchars( file_get_contents( $log['file'] ) ). '</textarea>';
				}


				$lastLog = $options['uploadsPath'] . '/lastLog-streamTranscode.txt';
				echo "<h5>FFMPEG Stream Transcode</h5>  $lastLog : ";
				if (!file_exists($lastLog)) echo 'Not found, yet!';
				else
				{
					$log = self::varLoad($lastLog);
					echo '<br>Time: ' . date(DATE_RFC2822, $log['time']);
					echo '<br>Command: ' . $log['cmd'];
					echo '<br>Return: ' . $log['return'];
					echo '<br>Output[0]: ' . $log['output0'];
					echo '<br>File: ' . $log['file'];
					if (!file_exists($log['file'])) echo ' Log file not found!';
					else echo '<br><textarea readonly cols="100" rows="4">' . htmlspecialchars( file_get_contents( $log['file'] ) ). '</textarea>';
				}
								
				$lastLog = $options['uploadsPath'] . '/lastLog-streamSetup.txt';
				echo "<h5>FFMPEG Stream Setup</h5>  $lastLog : ";
				if (!file_exists($lastLog)) echo 'Not found, yet!';
				else
				{
					$log = self::varLoad($lastLog);
					echo '<br>Time: ' . date(DATE_RFC2822, $log['time']);
					echo '<br>Command: ' . $log['cmd'];
					echo '<br>Return: ' . $log['return'];
					echo '<br>Output[0]: ' . $log['output0'];
					echo '<br>File: ' . $log['file'];
					if (!file_exists($log['file'])) echo ' Log file not found!';
					else echo '<br><textarea readonly cols="100" rows="4">' . htmlspecialchars( file_get_contents( $log['file'] ) ). '</textarea>';
				}


				break;
			case 'reset':
?>
<h3><?php _e('Reset Options','live-streaming'); ?></h3>
This resets some options to defaults. Useful when upgrading plugin and new defaults are available for new features and for fixing broken installations.
<?php

				$confirm = $_GET['confirm'];



				if ($confirm) echo '<h4>Resetting...</h4>';
				else echo '<p><A class="button" href="'.get_permalink().'admin.php?page=live-streaming&tab=reset&confirm=1">Yes, Reset These Settings!</A></p>';

				$resetOptions = array('customCSS', 'custom_post', 'supportP2P', 'alwaysP2P');

				foreach ($resetOptions as $opt)
				{
					echo '<BR> - ' . $opt;
					if ($confirm) $options[$opt] = $optionsDefault[$opt];
				}

				if ($confirm)  update_option('VWliveStreamingOptions', $options);

				$installed_ver = get_option( "vwls_db_version" );

				echo "<h4>DB Version</h4>" . $installed_ver;
				break;


			case 'watch-params':
				$options['watchRoleParametersConfig'] = htmlentities(stripslashes($options['watchRoleParametersConfig']));

?>
<h3>Watch Parameters: Advanced Configuration by Role</h3>
This permits advanced configuration for watch interface parameters based on user role.
<br>For more details about available parameters and possible values see <a href="https://videowhisper.com/?p=php+live+streaming#integrate">PHP Live Streaming documentation</a>.

<h4>Watch Role Parameters Configuration</h4>
<textarea name="watchRoleParametersConfig" id="watchRoleParametersConfig" cols="100" rows="5"><?php echo $options['watchRoleParametersConfig']?></textarea>
<BR>This overwrites parameters defined as permissions by channel owner.
Default:<br><textarea readonly cols="100" rows="4"><?php echo $optionsDefault['watchRoleParametersConfig']?></textarea>

<BR>Parsed configuration (should be an array of arrays):
<?php

				echo '<br><textarea readonly cols="100" rows="4">';
				var_dump($options['watchRoleParameters']);
				echo '</textarea>';


				if (!$current_user) $current_user = wp_get_current_user();
				echo '<h4>Testing</h4>Your role(s): '; var_dump($current_user->roles);

				echo '<BR>Role Parameters: ';
				var_dump(self::userParameters($current_user, $options['watchRoleParameters']));

				break;

			case 'watch-limit':
				$options['userWatchLimitsConfig'] = htmlentities(stripslashes($options['userWatchLimitsConfig']));

?>
<h3>Watch Limit</h3>
Limit watch time per user (by keeping track for each user of total watch time on site).
<br>Works for Flash app for PC browser RTMP based clients. RTMP Session Control also enables for external RTMP apps and HTML5 WebRTC sessions. Does not monitor HTTP based mobile streaming (HLS / MPEG DASH).
<br>Only works for registered users as this records info as user metas. Does not work for site visitors: you should disable visitor access when using this.
<br>User watch time in Flash app is updated based on <a href="admin.php?page=live-streaming&tab=watcher">statusInterval parameter</a>. If configured at 60000 (ms), will update once per minute. Warning: A low value can highly impact web server load when multiple users are online. Recommended interval is 1-5 minutes depending on average content length and acceptable grace time.

<h4>Enable Watch Limit per User</h4>
<select name="userWatchLimit" id="userWatchLimit">
  <option value="1" <?php echo $options['userWatchLimit']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['userWatchLimit']?"":"selected"?>>No</option>
</select>

<h4>Limit Interval</h4>
<input name="userWatchInterval" type="text" id="userWatchInterval" size="12" maxlength="32" value="<?php echo $options['userWatchInterval']?>"/>s
<BR>Specify interval for limits in seconds (in example 2592000 = 1 month, Default: <?php echo $optionsDefault['userWatchInterval']?>).

<h4>Default Limit</h4>
<input name="userWatchLimitDefault" type="text" id="userWatchLimitDefault" size="12" maxlength="32" value="<?php echo $options['userWatchLimitDefault']?>"/>s
<br>Default limit for user in seconds (Ex: 108000 = 30h, Default: <?php echo $optionsDefault['userWatchLimitDefault']?>).

<h4>User Watch Limits Configuration</h4>
<textarea name="userWatchLimitsConfig" id="userWatchLimitsConfig" cols="100" rows="5"><?php echo $options['userWatchLimitsConfig']?></textarea>
<BR>Assign limit in hours, by role, one per line. Set 0 for unlimited.
Default:<br><textarea readonly cols="100" rows="4"><?php echo $optionsDefault['userWatchLimitsConfig']?></textarea>

<BR>Parsed configuration (should be an array):<BR>
<?php

				var_dump($options['userWatchLimits']);

				if (!$current_user) $current_user = wp_get_current_user();
				echo '<h4>Testing</h4>Your role(s): '; var_dump($current_user->roles);
				echo '<BR>Your Watch Time: ' . get_user_meta( $current_user->ID, 'vwls_watch', true ) . 's';
				echo '<BR>Since: ' .date("F j, Y, g:i a", get_user_meta( $current_user->ID, 'vwls_watch_update', true ));
				echo '<BR>Your Limit: ' . $limit = self::userWatchLimit($current_user, $options)?$limit.'s':'unlimited';

				break;

case 'iptv':

?>
<h3>IPTV / Pull Streams - Under Development</h3>
The IPTV system can be used to publish external (existing) streams as channels on this system. Source streams can be pulled from IPTV, IP Cameras, other streaming servers or platforms.
<h4>Active IPTV Streams</h4>
<?php
	
			$iptvActive = $options['uploadsPath'] . '/iptvActive.txt';

		$streamsActive = self::varLoad($iptvActive);
		if (!is_array($streamsActive)) $streamsActive = array();
		
		if (count($streamsActive))
		foreach($streamsActive as $postID => $pid)
		{
			echo ' - ';

			$post = get_post($postID);
			if ($post)
			{  
			echo $post->post_title;
			echo " ($pid)";
			}
			else 
			{
				echo "Post #$postID not found. Deleted? Removing...";
				
				//clean and update
				unset($streamsActive[$postID]);
				self::varSave($iptvActive, $streamsActive);
			}
			
			echo '<br>';
		}
		else echo 'No IPTV active streams.';
?>

<h4>IPTV</h4>
<select name="iptv" id="iptv">
  <option value="0" <?php echo $options['iptv']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['iptv']=='1'?"selected":""?>>Yes</option>
</select>

<h4>Registration on Cam/Stream Setup</h4>
<select name="ipcamera_registration" id="ipcamera_registration">
  <option value="0" <?php echo $options['ipcamera_registration']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['ipcamera_registration']=='1'?"selected":""?>>Yes</option>
</select>
<br>Allows visitors to quickly register after testing a streaming address. Not recommended.


<?php		
break;	
			case 'stream':
?>

<h3>Stream Files / Re-Streaming / IP Camera Streams</h3>
This functionality requires web and streaming services on same server (host) when using stream configuration files for Wowza SE.
Can also use IPTV re-streaming system.
<?php
				if ($removeStream = intval($_GET['removeStream']))
				{
					echo '<h4>Removing Stream</h4>';

					$roomPost = get_post($removeStream);
					if (!$roomPost) echo 'Not found: #'.$removeStream;
					else
					{
						$stream = $roomPost->post_title;
						echo 'Room: ' . $stream;

						$streamFile = $options['streamsPath'] .'/'. $stream;

						if (file_exists($streamFile))
						{
							$ftime = filemtime($streamFile);
							echo '<br>Found file date: ' . date(DATE_RFC2822, $ftime);
							unlink($streamFile);
							echo '<br>Removed: ' . $streamFile;
						}else echo '<br>Stream file not found: ' . $streamFile;

						update_post_meta( $roomPost->ID, 'vw_ipCamera', '' );
						echo '<br>Removed channel re-streaming configuration.';

					}
				}
?>



<h4>Stream Channels</h4>
Channels configured for re-streaming:
	<?php

$addresses = array();

				$ztime = time();

				//query
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
				
				echo '<table><tr><th>Channel</th><th>Owner</th><th>Remove</th><th>Address</th><th>Paused</th><th>Accessed</th><th>Accessed by an user</th><th>Owner active</th><th>Broadcast</th><th>Thumb</th></tr>';

				if (is_array($posts)) if (count($posts))
						foreach ($posts as $post)
						{
							echo '<tr ' .(++$k%2?'class="alternate"':''). '>';
							//update status
							self::restreamPause($post->ID, $post->post_title, $options);

							$edate = intval(get_post_meta( $post->ID, 'edate', true ));
							$thumbTime = intval(get_post_meta( $post->ID, 'thumbTime', true ));
							
							$vw_ipCamera = get_post_meta( $post->ID, 'vw_ipCamera', true );
							$restreamPaused = get_post_meta( $post->ID, 'restreamPaused', true );

							// access time
							$accessedUser = intval(get_post_meta($post->ID, 'accessedUser', true));
							$accessed = intval(get_post_meta($post->ID, 'accessed', true));
							
							//author site access time
							$userID = get_post_field( 'post_author', $post->ID );
							$user = get_userdata($userID);
							
							$accessTime = intval(get_user_meta($userID, 'accessTime', true));

							echo '<TH><a href="'. get_permalink(  $post->ID ).'" target="_channel">' . $post->post_title . '</a></TH>';
							echo '<td>' . $user->user_login . '</td>';					
							echo '<TD><a class="secondary button" href="admin.php?page=live-streaming&tab=stream&removeStream='. $post->ID . '">Remove</a></TD><TD><small>' . htmlspecialchars($vw_ipCamera) . '</small></TD>';
							echo '<td>' . ($restreamPaused?'Yes':'No') . '</td>';
							echo '<td>' . self::format_age($ztime - $accessed) . '</td>';					
							echo '<td>' . self::format_age($ztime - $accessedUser) . '</td>';
							echo '<td>' . self::format_age($ztime - $accessTime) . '</td>';

							echo '<td>' . self::format_age($ztime - $edate) . '</td>';
							echo '<td>' . self::format_age($ztime - $thumbTime) . '</td>';
							
							echo '</tr>';
							
							$addresses[] = $vw_ipCamera;

						}else echo '<tr><td colspan=6>No channels with streams.<td></tr>';
						
							echo '</table>';
?>
<h4>Stream Files (Active Configurations)</h4>
Stream files in configured streams folder:
<?php
					echo $options['streamsPath'];

$removeFile = sanitize_text_field(base64_decode($_GET['removeFile']));
if ($removeFile)
{
	echo '<br>Remove: ' . $removeFile;
	
	if (substr($removeFile, 0, strlen($options['streamsPath'])) == $options['streamsPath'])
	{
		if (file_exists($removeFile)) unlink($removeFile);
		else echo ' NOT FOUND!';
	}
	else echo ' BAD PATH!';
}
				$files = array();
				foreach (glob($options['streamsPath'] . "/*.stream") as $file) {
					$files[] = $file;
	
				}

				if (count($files)) foreach ($files as $file) 
				{
					$address = file_get_contents($file);
					echo '<BR>' . $file . ' : ' . htmlspecialchars($address);
					echo ' <a class="secondary button" href="admin.php?page=live-streaming&tab=stream&removeFile='. urlencode(base64_encode($file)) . '">Remove</a>';
			
					if (!in_array($address, $addresses)) 
					{
						echo ' * NOT assigned to any channel!';
						if ($options['restreamClean']) 
						{
							unlink($file);
							echo ' - CLEANED';
						}
					}
				}
					else echo '<br>No stream files detected in configured folder.';

?>
<h4>IP Cams</h4>
<select name="ipcams" id="ipcams">
  <option value="0" <?php echo $options['ipcams']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['ipcams']=='1'?"selected":""?>>Yes</option>
</select>
<br>Enable users to setup IP cams / re-streams from broadcast live interface (depending on permissions).


<h4>Auto Pause</h4>
Pause re-streaming while not needed, to reduce bandwidth usage / server load.
<br>
<select name="restreamPause" id="restreamPause">
  <option value="0" <?php echo $options['restreamPause']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['restreamPause']=='1'?"selected":""?>>Yes</option>
</select>
<h4>Resume</h4>
Restreaming updates done by WP cron that also updates snapshots.
<?php
					echo '<BR>Next automated check (WP Cron, 10 min or more depending on site activity): in ' . ( wp_next_scheduled( 'cron_10min_event') - time()) . 's';
?>

<h5>Activity Timeout</h5>
<input name="restreamTimeout" type="text" id="restreamTimeout" size="16" maxlength="32" value="<?php echo $options['restreamTimeout']?>"/>s

<br>Resume if any of these occurred during timeout period:

<h5>Resume On Channel Access</h5>
<select name="restreamAccessed" id="restreamAccessed">
  <option value="0" <?php echo $options['restreamAccessed']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['restreamAccessed']=='1'?"selected":""?>>Yes</option>
</select> Any access (visitor or registered user) will resume stream. When streams should be accessible by anybody. Warning: This can be triggered often by crawlers, bots.

<h5>Resume On Channel Access by Registered User</h5>
<select name="restreamAccessedUser" id="restreamAccessedUser">
  <option value="0" <?php echo $options['restreamAccessedUser']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['restreamAccessedUser']=='1'?"selected":""?>>Yes</option>
</select> Registered user access will resume stream. When service is used by site members (IPTV site).

<h5>Resume On Owner Active</h5>
<select name="restreamActiveOwner" id="restreamActiveOwner">
  <option value="0" <?php echo $options['restreamActiveOwner']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['restreamActiveOwner']=='1'?"selected":""?>>Yes</option>
</select> Channel owner active on site will resume streams. When service is used by owner (IP camera monitoring site).

<h5>Resume On Any User Active</h5>
<select name="restreamActiveUser" id="restreamActiveUser">
  <option value="0" <?php echo $options['restreamActiveUser']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['restreamActiveUser']=='1'?"selected":""?>>Yes</option>
</select>ANY registered user active on site will resume ALL streams. When there are few streams and site is used by few users that can check all streams.

<h4>Transcode Re-Streams</h4>
<select name="transcodeReStreams" id="transcodeReStreams">
  <option value="0" <?php echo $options['transcodeReStreams']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodeReStreams']=='1'?"selected":""?>>Yes</option>
</select>
<br>Incoming streams should be encoded with H264 & AAC for playback without transcoding. Default: No
<br>Warning: Transcoding involves extra latency, extra delay for stream to become available in new version and high server processing load (cpu & memory).


<h4>Registration on Cam/Stream Setup</h4>
<select name="ipcamera_registration" id="ipcamera_registration">
  <option value="0" <?php echo $options['ipcamera_registration']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['ipcamera_registration']=='1'?"selected":""?>>Yes</option>
</select>
<br>Allows visitors to quickly register after testing a streaming address. Not recommended.


<h4>Remove Orphan Stream Files</h4>
<select name="restreamClean" id="restreamClean">
  <option value="0" <?php echo $options['restreamClean']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['restreamClean']=='1'?"selected":""?>>Yes</option>
</select>
<br>Remove .stream files not assigned to any channel.

<h4>Streams Path</h4>
<input name="streamsPath" type="text" id="streamsPath" size="100" maxlength="256" value="<?php echo $options['streamsPath']?>"/>

<BR>Path to .stream files monitored by streaming server for restreaming.
<BR>Such functionality requires a server with latest Wowza Streaming Engine, web and rtmp on same sever, <a href='https://www.wowza.com/forums/content.php?39-How-to-re-stream-video-from-an-IP-camera-(RTSP-RTP-re-streaming)#config_xml'>specific setup</a>. Streaming server loads configuration from web files, connects to IP camera stream or video file, loads stream and delivers in format suitable for web publishing.
<BR>This functionality is available with <a href="https://webrtchost.com/hosting-plans/#Complete-Hosting" target="_vwhost">VideoWhisper Complete Hosting plans</a> and servers, when hosting both web and rtmp on same plan/server so web scripts can access streaming configuration files.
If custom ports are used, server firewall must be configured to allow connections.

<BR> <?php
				echo $options['streamsPath'] . ' : ';
				if (file_exists($options['streamsPath']))
				{
					echo 'Found. ';
					if (is_writable($options['streamsPath'])) echo 'Writable. (OK)';
					else echo 'NOT writable.';
				}
				else echo '<b>NOT found!</b>';


				break;



			case 'playlists':
?>

<h3>Playlist Scheduler Settings</h3>
This section is for configuring settings related to SMIL playlists. Playlist can be used to schedule videos to play as a live stream (on a channel).
Playlist support can be configured on <a href='https://www.wowza.com/forums/content.php?145-How-to-schedule-streaming-with-Wowza-Streaming-Engine-(StreamPublisher)#installation'>Wowza Streaming Engine</a> and requires web and rtmp on same servers (so web scripts can write playlists).

<h4>Video Share VOD</h4>
<?php
				if (is_plugin_active('video-share-vod/video-share-vod.php'))
				{
					echo 'Detected.';
					$optionsVSV = get_option('VWvideoShareOptions');
					$custom_post_video = $optionsVSV['custom_post'];

					echo ' Post type name: ' . $optionsVSV['custom_post'];

				} else echo 'Not detected. Please install, activate and configure <a target="_blank" href="https://wordpress.org/plugins/video-share-vod/">Video Share VOD</a>!';

?>

<h4>Video Post Type Name</h4>
<input name="custom_post_video" type="text" id="custom_post_video" size="16" maxlength="32" value="<?php echo $options['custom_post_video']?>"/>
<br>Should be same as Video Share VOD post type name. Ex: video


<h4>Enable Playlists</h4>
<select name="playlists" id="playlists">
  <option value="1" <?php echo $options['playlists']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['playlists']?"":"selected"?>>No</option>
</select>
<BR>Allows users to schedule playlists. Feature also needs to be enabled for channels owners from <a href='admin.php?page=live-streaming&tab=features'>Channel Features</a> : Playlist Scheduler .
<BR>This feature requires Wowza Streaming Engine and <a href="https://www.wowza.com/forums/content.php?145-How-to-schedule-streaming-with-Wowza-Streaming-Engine-(StreamPublisher)#installation">specific setup</a>: for VideoWhisper managed <a href="https://videowhisper.com/?p=wowza+media+server+hosting">hosting plans</a> and <a href="https://videowhisper.com/?p=Dedicated+Servers">servers</a> submit a support request for setting this up.

<?php
				if ($disablePlaylist = intval($_GET['disablePlaylist']))
				{
					echo '<h4>Disabling Playlists</h4>';

					$roomPost = get_post($disablePlaylist);
					if (!$roomPost) echo 'Not found: '.$disablePlaylist;
					else
					{
						$stream = $roomPost->post_title;
						self::updatePlaylist($stream, 0);
						update_post_meta( $roomPost->ID, 'vw_playlistUpdated', time());
						update_post_meta( $roomPost->ID, 'vw_playlistActive', '0');

						echo 'Room: ' . $roomPost->post_title . ' Performer Stream: ' . $stream;
					}
				}
?>


<h4>Streams Path</h4>
<input name="streamsPath" type="text" id="streamsPath" size="100" maxlength="256" value="<?php echo $options['streamsPath']?>"/>
<BR>Used for .smil playlists (should be same as streams path configured in VideoShareVOD for RTMP delivery).
<BR> <?php
				echo $options['streamsPath'] . ' : ';
				if (file_exists($options['streamsPath']))
				{
					echo 'Found. ';
					if (is_writable($options['streamsPath'])) echo 'Writable. (OK)';
					else echo 'NOT writable.';
				}
				else echo '<b>NOT found!</b>';

				// update when saving
				if (isset($_POST['playlists']))
				{
					echo '<BR><BR>SMIL updated on settings save.';
					self::updatePlaylistSMIL();
				}

				$streamsPath = self::fixPath($options['streamsPath']);
				$smilPath = $streamsPath . 'playlist.smil';

				if (file_exists($smilPath))
				{
					echo '<br><br>Playlist found: ' . $smilPath;
					$smil = file_get_contents($smilPath);
					echo '<br><textarea readonly cols="100" rows="10">' .htmlentities($smil). '</textarea>';
				}

?>
<h4>Active Playlists</h4>
Currently scheduled playlists:
	<?php
				//query
				$args=array(
					'post_type' =>  $options['custom_post'],
					'orderby'       =>  'post_date',
					'order'            => 'DESC',
					'meta_key'   => 'vw_playlistActive',
					'meta_value' => '1',
				);

				$posts = get_posts( $args );

				if (is_array($posts)) if (count($posts))
						foreach ($posts as $post)
						{
							echo '<br> - ' . $post->post_title . ' <a href="admin.php?page=live-streaming&tab=playlists&disablePlaylist='. $post->ID . '">Disable</a>';
						}else echo 'No active playlists scheduled.';

					break;


			case 'app':
				$options['eula_txt'] = htmlentities(stripslashes($options['eula_txt']));
				$options['crossdomain_xml'] = htmlentities(stripslashes($options['crossdomain_xml']));

				$eula_url = site_url() . '/eula.txt';
				$crossdomain_url = site_url() . '/crossdomain.xml';

				//TEST: wp-admin/admin-ajax.php?action=vwls&task=vw_extlogin&videowhisper=1
?>
<h3>Application Settings</h3>
<p>This section is for configuring settings related to custom remote apps (iOS/Android/Desktop) that can be used in combination with this web based solution. Such apps can be <a href="https://videowhisper.com/?p=iPhone-iPad-Apps">custom made</a> for each site. Broadcasting camera as RTMP from mobile devices is only possible with mobile apps, due to mobile browser limitations.</p>

<h4>Default Webcam Resolution</h4>
<select name="camResolutionMobile" id="camResolutionMobile">
<?php
				foreach (array('160x120','240x180','320x240','426x240','480x360', '640x360', '640x480', '720x480', '720x576', '854x480', '1280x720', '1440x1080', '1920x1080') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['camResolutionMobile']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>
 <br>Higher resolution will require <a target="_blank" href="https://videochat-scripts.com/recommended-h264-video-bitrate-based-on-resolution/">higher bandwidth</a> to avoid visible blocking and quality loss (ex. 1Mbps required for 640x360). Webcam capture resolution should be similar to video size in player/watch interface (capturing higher resolution will require more resources without visible quality improvement and lower will display pixelation when zoomed in player).

<h4>Webcam Frames Per Second</h4>
<select name="camFPSMobile" id="camFPSMobile">
<?php
				foreach (array('1','8','10','12','15','29','30','60') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['camFPSMobile']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>

<h4>Video Stream Bandwidth</h4>
<input name="camBandwidthMobile" type="text" id="camBandwidthMobile" size="7" maxlength="7" value="<?php echo $options['camBandwidthMobile']?>"/> (bytes/s)
<br>This sets size of video stream (without audio) and therefore the video quality.
<br>Total stream size should be less than maximum broadcaster upload speed (multiply by 8 to get bps, ex. 50000b/s requires connection higher than 400kbps).
<br>Do a speed test from broadcaster computer to a location near your streaming (rtmp) server using a tool like <a href="http://www.speedtest.net" target="_blank">SpeedTest.net</a> . Drag and zoom to a server in contry/state where you host (Ex: central US if you host with VideoWhisper) and select it. The upload speed is the maximum data you'll be able to broadcast.

<?php
				/*

<h4>Video Codec</h4>
<select name="videoCodecMobile" id="videoCodecMobile">
  <option value="H264" <?php echo $options['videoCodecMobile']=='H264'?"selected":""?>>H264</option>
  <option value="H263" <?php echo $options['videoCodecMobile']=='H263'?"selected":""?>>H263</option>
</select>
<BR>Mobile apps don't currently support H264 (due to Adobe Air limitations).


<h4>H264 Video Codec Profile</h4>
<select name="codecProfileMobile" id="codecProfileMobile">
  <option value="main" <?php echo $options['codecProfileMobile']=='main'?"selected":""?>>main</option>
  <option value="baseline" <?php echo $options['codecProfileMobile']=='baseline'?"selected":""?>>baseline</option>
</select>
<br>Recommended: Baseline

<h4>H264 Video Codec Level</h4>
<select name="codecLevelMobile" id="codecLevelMobile">
<?php
				foreach (array('1', '1b', '1.1', '1.2', '1.3', '2', '2.1', '2.2', '3', '3.1', '3.2', '4', '4.1', '4.2', '5', '5.1') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['codecLevelMobile']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>
<br>Recommended: 3.1

<h4>Sound Codec</h4>
<select name="soundCodecMobile" id="soundCodecMobile">
  <option value="Speex" <?php echo $options['soundCodecMobile']=='Speex'?"selected":""?>>Speex</option>
  <option value="Nellymoser" <?php echo $options['soundCodecMobile']=='Nellymoser'?"selected":""?>>Nellymoser</option>
</select>
<BR>Speex is recommended for voice audio.
<BR>Current web codecs used by Flash plugin are not currently supported by iOS. For delivery to iOS, audio should be transcoded to AAC (HE-AAC or AAC-LC up to 48 kHz, stereo audio).

<h4>Speex Sound Quality</h4>
<select name="soundQualityMobile" id="soundQualityMobile">
<?php
				foreach (array('0', '1','2','3','4','5','6','7','8','9','10') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['soundQualityMobile']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>
 <br>Higher quality requires more <a href="http://www.videochat-scripts.com/speex-vs-nellymoser-bandwidth/" target="_blank" >bandwidth</a>.
<br>Speex quality 9 requires 34.2kbps and generates 4275 b/s transfer. Quality 10 requires 42.2 kbps.

<h4>Nellymoser Sound Rate</h4>
<select name="micRateMobile" id="micRateMobile">
<?php
				foreach (array('5', '8', '11', '22','44') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['micRateMobile']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>
<br>Higher quality requires more <a href="http://www.videochat-scripts.com/speex-vs-nellymoser-bandwidth/" target="_blank" >bandwidth</a>.
<br>NellyMoser rate 22 requires 44.1kbps and generates 5512b/s transfer. Rate 44 requires 88.2 kbps.

*/
?>

<h4><?php _e('End User License Agreement','vw2wvc'); ?></h4>
<textarea name="eula_txt" id="eula_txt" cols="100" rows="8"><?php echo $options['eula_txt']?></textarea>
<br>Users are required to accept this agreement before registering from app.
<br>After updating permalinks (<a href="options-permalink.php">Save Changes on Permalinks page</a>) this should become available as <a href="<?php echo $eula_url ?>"><?php echo $eula_url ?></a>.
<br>This works if file doesn't already exist. You can also create the file for faster serving.

<h4><?php _e('Cross Domain Policy','vw2wvc'); ?></h4>
<textarea name="crossdomain_xml" id="crossdomain_xml" cols="100" rows="4"><?php echo $options['crossdomain_xml']?></textarea>
<br>This is required for applications to access interface and scripts on site.
<br>After updating permalinks (<a href="options-permalink.php">Save Changes on Permalinks page</a>) this should become available as <a href="<?php echo $crossdomain_url ?>"><?php echo $crossdomain_url ?></a>.
<br>This works if file doesn't already exist. You can also create the file for faster serving.
<?php

				break;

			case 'support':
				//! Support
	
	self::requirementMet('resources');
				
?>
<h3>Support Resources</h3>
This section contains links to multiple support resources, including hosting requirements, software documentation, developer contact, addon plugin suggestions.

<p><a href="https://videowhisper.com/tickets_submit.php" class="button primary" >Contact VideoWhisper</a></p>


<h3>Hosting Requirements</h3>
<UL>
<LI><a href="https://videowhisper.com/?p=Requirements">Hosting Requirements</a> This advanced software requires web hosting and streaming hosting.</LI>
<LI><a href="https://videowhisper.com/?p=RTMP+Hosting">Estimate Hosting Needs</a> Evaluate hosting needs: volume and features.</LI>
<LI><a href="http://hostrtmp.com/compare/">Compare Hosting Options</a> Hosting options starting from $9/month.</LI>
<LI><a href="admin.php?page=live-streaming&tab=setup">Setup & Requirements Overview</a> Local setup overview.</LI>
<LI><a href="admin.php?page=live-streaming&tab=troubleshooting">Requirements Troubleshooting</a> Local troubleshooting.</LI>
</UL>
<h3>Software Documentation</h3>
<UL>
<LI><a href="admin.php?page=live-streaming-docs">Backend Documentation</a> Local backend page, includes tutorial with local links to configure main features, menus, pages.</LI>
<LI><a href="http://broadcastlivevideo.com/setup-tutorial/">BroadcastLiveVideo Tutorial</a> Setup a turnkey live video broadcasting site.</LI>
<LI><a href="https://videowhisper.com/?p=wordpress+live+streaming">VideoWhisper Plugin Homepage</a> Plugin and application documentation.</LI>
</UL>

<a name="plugins"></a>

<h3>Available Integrations and Recommended Plugins</h3>
<ul>
<li><a href="https://wordpress.org/plugins/video-share-vod/" title="Video Share / Video On Demand">Video Share VOD</a> plugin, integrated for video archive support, publishing HTML5 videos. For more details see <a href="https://videosharevod.com" title="Video Share / Video On Demand">Video Share VOD</a> turnkey solution homepage.</li>
<li> <a href="https://wordpress.org/plugins/rate-star-review/" title="Rate Star Review - AJAX Reviews for Content with Star Ratings">Rate Star Review – AJAX Reviews for Content with Star Ratings</a> plugin, integrated for channel reviews and ratings.</li>
<li><a href="https://wordpress.org/plugins/paid-membership/" title="Paid Membership">Paid Membership & Content</a> plugin, for managing membership with tokens, control access to pages by membership, selling content.</li>
<li><a href="https://wordpress.org/plugins/mycred/">myCRED</a> and/or <a href="https://wordpress.org/plugins/woo-wallet/">WooCommerce TeraWallet</a>, integrated for tips.  Configure as described in Tips settings tab.</li>
<li><a href="https://wordpress.org/plugins/wp-super-cache/">WP Super Cache</a> (configured to not cache for known users or GET parameters, great for protecting against bot or crawlers eating up site resources)</li>
<li><a href="https://wordpress.org/plugins/wordfence/">WordFence</a> plugin with firewall. Configure to protect by limiting failed login attempts, bot attacks / flood request, scan for malware or vulnerabilities.</li>
<li>HTTPS redirection plugin like <a href="https://wordpress.org/plugins/really-simple-ssl/">Really Simple SSL</a>&nbsp;, if you have a SSL certificate and HTTPS configured (as on VideoWhisper plans). HTTPS is required to broadcast webcam, in latest browsers like Chrome. If you also use HTTP urls (not recommended), disable “Auto replace mixed content” option to avoid breaking external HTTP urls (like HLS).</li>
<li>A SMTP mailing plugin like <a href="https://wordpress.org/plugins/easy-wp-smtp/">Easy WP SMTP</a> and setup a real email account from your hosting backend (setup an email from CPanel) or external (Gmail or other provider), to send emails using SSL and all verifications. This should reduce incidents where users don’t find registration emails due to spam filter triggering. Also instruct users to check their spam folders if they don’t find registration emails. To prevent spam, an <a href="https://wordpress.org/plugins/search/user-verification/">user verification plugin</a> can be used.</li>
	<li>For basic search engine indexing, make sure your site does not discourage search engine bots from Settings &gt; Reading   (discourage search bots box should not be checked).
Then install a plugin like <a href="https://wordpress.org/plugins/google-sitemap-generator/">Google XML Sitemaps</a> for search engines to quickly find main site pages.</li>
 	<li>For sites with adult content, an <a href="https://wordpress.org/plugins/tags/age-verification/">age verification / confirmation plugin</a> should be deployed. Such sites should also include a page with details for 18 U.S.C. 2257 compliance. For other suggestions related to adult sites, see <a href="https://paidvideochat.com/adult-videochat-business-setup/">Adult Videochat Business Setup</a>.</li>
<li><a href="https://updraftplus.com/?afref=924">Updraft Plus</a> – Automated WordPress backup plugin. Free for local storage.

<h3>Premium Plugins / Addons</h3>
<ul>
	<LI><a href="http://themeforest.net/popular_item/by_category?category=wordpress&ref=videowhisper">Premium Themes</a> Professional WordPress themes.</LI>
	<LI><a href="https://woocommerce.com/?aff=18336&cid=1980980">WooCommerce</a> Free shopping cart plugin, supports multiple free and premium gateways with TeraWallet/WooWallet plugin and various premium eCommerce plugins.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-memberships/?aff=18336&cid=1980980">WooCommerce Memberships</a> Setup paid membership as products. Leveraged with Subscriptions plugin allows membership subscriptions.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-subscriptions/?aff=18336&cid=1980980">WooCommerce Subscriptions</a> Setup subscription products, content. Leverages Membership plugin to setup membership subscriptions.</LI>

	<LI><a href="https://woocommerce.com/products/woocommerce-bookings/?aff=18336&cid=1980980">WooCommerce Bookings</a> Let your customers book reservations, appointments on their own.</LI>

	<LI><a href="https://woocommerce.com/products/follow-up-emails/?aff=18336&cid=1980980">WooCommerce Follow Up</a> Follow Up by emails and twitter automatically, drip campaigns.</LI>

	<LI><a href="https://updraftplus.com/?afref=924">Updraft Plus</a> Automated WordPress backup plugin. Free for local storage. For production sites external backups are recommended (premium).</LI>
</ul>


<h3>Contact and Feedback</h3>
<a href="https://videowhisper.com/tickets_submit.php">Submit a Ticket</a> with your questions, inquiries and VideoWhisper support staff will try to address these as soon as possible.
<br>Although the free license does not include any services (as installation and troubleshooting), VideoWhisper staff can clarify requirements, features, installation steps or suggest additional services like customisations, hosting you may need for your project.

<h3>Review and Discuss</h3>
You can publicly <a href="https://wordpress.org/support/view/plugin-reviews/videowhisper-live-streaming-integration">review this WP plugin</a> on the official WordPress site (after <a href="https://wordpress.org/support/register.php">registering</a>). You can describe how you use it and mention your site for visibility. You can also post on the <a href="https://wordpress.org/support/plugin/videowhisper-live-streaming-integration">WP support forums</a> - these are not monitored by support so use a <a href="https://videowhisper.com/tickets_submit.php">ticket</a> if you want to contact VideoWhisper.
<BR>If you like this plugin and decide to order a commercial license or other services from <a href="http://videowhisper.com/">VideoWhisper</a>, use this coupon code for 5% discount: giveme5


<h3>News and Updates</h3>
You can also get connected with VideoWhisper and follow updates using <a href="http://twitter.com/videowhisper"> Twitter </a>, <a href="http://www.facebook.com/pages/VideoWhisper/121234178858"> Facebook </a>. Warning: Social media is not monitored so if you want to contact support always contact from our site.


				<?php
				break;
case 'appearance':
 
				$options['customCSS'] = htmlentities(stripslashes($options['customCSS']));
				$options['cssCode'] = htmlentities(stripslashes($options['cssCode']));

	self::requirementMet('appearance');				
?>
<h3>Appearance</h3>
Customize appearance, styling, listings.

<h4>Registration and Login Logo</h4>
<input name="loginLogo" type="text" id="loginLogo" size="100" maxlength="200" value="<?php echo $options['loginLogo']?>"/>
<br>Logo image to show on registration & login form, replacing default WordPress logo for a turnkey site. Leave blank to disable. Recommended size: 200x68.
<?php echo $options['loginLogo']?"<BR><img src='".$options['loginLogo']."'>":'';?>

<h4>Interface Class(es)</h4>
<input name="interfaceClass" type="text" id="interfaceClass" size="30" maxlength="128" value="<?php echo $options['interfaceClass']?>"/>
<br>Extra class to apply to interface (using Semantic UI). Use inverted when theme uses a dark mode (a dark background with white text) or for contrast. Ex: inverted
<br>Some common Semantic UI classes: inverted = dark mode or contrast, basic = no formatting, secondary/tertiary = greys, red/orange/yellow/olive/green/teal/blue/violet/purple/pink/brown/grey/black = colors . Multiple classes can be combined, divided by space. Ex: inverted, basic pink, secondary green, secondary basic

<h4>Floating Logo / Watermark</h4>
<input name="overLogo" type="text" id="overLogo" size="80" maxlength="256" value="<?php echo $options['overLogo']?>"/>
<br>Recommended: A  small transparent PNG image of icon size (48x48 or similar) so it does not cover a lot of the live video.
<?php echo $options['overLogo']?"<BR><img src='".$options['overLogo']."'>":'';?>

<h4>Logo Link</h4>
<input name="overLink" type="text" id="overLink" size="80" maxlength="256" value="<?php echo $options['overLink']?>"/>

<h4>Flash App Loader Image</h4>
<input name="loaderImage" type="text" id="loaderImage" size="80" maxlength="256" value="<?php echo $options['loaderImage']?>"/>
<br>Ex: <?php echo $root_url .'wp-content/plugins/videowhisper-live-streaming-integration/ls/loader.png'; ?>
<br>Shows while flash app is loading. Leave blank to disable.
<?php echo $options['loaderImage']?"<BR><img src='".$options['loaderImage']."'>":'';?>


<h4>Custom CSS</h4>
<textarea name="customCSS" id="customCSS" cols="100" rows="5"><?php echo $options['customCSS']?></textarea>
<BR>Used in elements added by this plugin. Include &lt;style type=&quot;text/css&quot;&gt; &lt;/style&gt; container.
Default:<br><textarea readonly cols="100" rows="4"><?php echo $optionsDefault['customCSS']?></textarea>


<h4>App CSS</h4>
<textarea name="cssCode" id="cssCode" cols="100" rows="5"><?php echo $options['cssCode']?></textarea>
<BR>Some texts from flash application can be styled (title, story).
Default:<br><textarea readonly cols="100" rows="4"><?php echo $optionsDefault['cssCode']?></textarea>

<h4>Channel Thumb Width</h4>
<input name="thumbWidth" type="text" id="thumbWidth" size="4" maxlength="4" value="<?php echo $options['thumbWidth']?>"/>

<h4>Channel Thumb Height</h4>
<input name="thumbHeight" type="text" id="thumbHeight" size="4" maxlength="4" value="<?php echo $options['thumbHeight']?>"/>
<BR><a href="admin.php?page=live-streaming&tab=stats&regenerateThumbs=1">Regenerate Thumbs</a>

<h4>Default Channels Per Page</h4>
<input name="perPage" type="text" id="perPage" size="3" maxlength="3" value="<?php echo $options['perPage']?>"/>
<br>You can configure more options on listing page with shortcode parameters as <a href="admin.php?page=live-streaming-docs">documented</a>.

<?php submit_button(); ?>

<p> + <strong>Theme</strong>: Get a <a href="http://themeforest.net/popular_item/by_category?category=wordpress&amp;ref=videowhisper">professional WordPress theme</a> to skin site, change design.<br>
A theme with wide content area (preferably full page width) should be used so videochat interface can use most of the space.<br>
Also plugin hooks into WP registration to implement a role selector: a theme that manages registration in a different custom page should be compatible with WP hooks to show the role option, unless you manage roles in a different way.<br>
Tutorial: <a href="https://en.support.wordpress.com/themes/uploading-setting-up-custom-themes/">Upload and Setup Custom WP Theme</a><br>
Sample themes: <a href="http://themeforest.net/item/jupiter-multipurpose-responsive-theme/5177775?ref=videowhisper">Jupiter</a>, <a href="http://themeforest.net/item/impreza-retina-responsive-wordpress-theme/6434280?ref=videowhisper">Impreza</a>, <a href="http://themeforest.net/item/elision-retina-multipurpose-wordpress-theme/6382990?ref=videowhisper">Elision</a>, <a href="http://themeforest.net/item/sweet-date-more-than-a-wordpress-dating-theme/4994573?ref=videowhisper">Sweet Date 4U</a>, <a href="https://themeforest.net/item/aeroland-responsive-app-landing-and-website-wordpress-theme/23314522?ref=videowhisper">AeroLand </a>. Most premium themes should work fine, these are just some we deployed in some projects.</p>

<p> + <strong>Logo</strong>: You can start from a <a href="http://graphicriver.net/search?utf8=%E2%9C%93&amp;order_by=sales&amp;term=video&amp;page=1&amp;category=logo-templates&amp;ref=videowhisper">professional logo template</a>. Logos can be configured from plugin settings, Integration tab and by default load from images in own installation.</p>

<p> + <strong>Design/Interface adjustments</strong>:
After selecting a theme to start from, that can be customized by a web designer experienced with WP themes. A WP designer can also create a custom theme (that meets WP coding requirements and standards).
Solution specific CSS (like for listings and user dashboards) can be edited in plugin backend.
Content on videochat page is generated by shortcodes from multiple plugins: videochat, profile fields, videos, pictures, ratings. There are multiple settings and CSS. Shortcodes are documented in plugin backend and can be added to pages, posts, templates.
Flash videochat skin graphics can be edited by replacing interface images in a templates folder as described in plugin backend. Videochat application layout and functional parameters can be edited in plugin settings.
HTML5 interface elements can customized by extra CSS. A lot of core styling is done with Semantic UI.
VideoWhisper developers can add additional options, settings to ease up customizations, for additional fees depending on exact customization requirements.
</p>
<?php
break;
			case 'general':

				$broadcast_url = admin_url() . 'admin-ajax.php?action=vwls_broadcast&n=';
				$root_url = get_bloginfo( "url" ) . "/";



				$current_user = wp_get_current_user();
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';

				if ($current_user->$userName) $username = $current_user->$userName;
				$username = sanitize_file_name($username);


				$options['translationCode'] = htmlentities(stripslashes($options['translationCode']));
				$options['adsCode'] = htmlentities(stripslashes($options['adsCode']));


				$current_user = wp_get_current_user();

?>
<h3>General Integration Settings</h3>
Settings for integration with WordPress framework and other plugins, services.

<h4>Channel Category Mode</h4>
<select name="subcategory" id="subcategory">
  <option value="all" <?php echo $options['subcategory']=='all'?"selected":""?>>2 Selectors: All</option>
  <option value="subcategories" <?php echo $options['subcategory']=='subcategories'?"selected":""?>>2 Selectors: Only Subcategories</option>  
  <option value="wordpress" <?php echo $options['subcategory']=='wordpress'?"selected":""?>>1 Selector: All</option>
</select>
<br>Enable only subcategories to disable channels from being assigned to main categories. There must be categories with subcategories defined. 
Using 2 selectors allows users to select main category and then subcategory in 2 steps.

<h4>Username</h4>
<select name="userName" id="userName">
  <option value="display_name" <?php echo $options['userName']=='display_name'?"selected":""?>>Display Name (<?php echo $current_user->display_name;?>)</option>
  <option value="user_login" <?php echo $options['userName']=='user_login'?"selected":""?>>Login (<?php echo $current_user->user_login;?>)</option>
  <option value="user_nicename" <?php echo $options['userName']=='user_nicename'?"selected":""?>>Nicename (<?php echo $current_user->user_nicename;?>)</option>
  <option value="ID" <?php echo $options['userName']=='ID'?"selected":""?>>ID (<?php echo $current_user->ID;?>)</option>
</select>
<br>Your username with current settings:
<?php
				$userName =  $options['userName']; if (!$userName) $userName='user_nicename';
				echo $username = $current_user->$userName;
					
?>

<h4>User Profile Link</h4>
<input name="profilePrefix" type="text" id="profilePrefix" size="100" maxlength="200" value="<?php echo $options['profilePrefix']?>"/>
<BR>Specify a url prefix for listing user profile.
 Default:<br><textarea readonly cols="100" rows="1"><?php echo $optionsDefault['profilePrefix']?></textarea>

<h4>Channel Profile Link</h4>
<input name="profilePrefixChannel" type="text" id="profilePrefixChannel" size="100" maxlength="200" value="<?php echo $options['profilePrefixChannel']?>"/>
<BR>Specify a url prefix for listing channel profile (a broadcaster can have multiple channels). If blank will link to default channel page.
 Default:<br><textarea readonly cols="100" rows="1"><?php echo $optionsDefault['profilePrefixChannel']?></textarea>

<h4>User Picture</h4>
<select name="userPicture" id="userPicture">
  <option value="0" <?php echo !$options['userPicture']?"selected":""?>>Disabled</option>
  <option value="avatar" <?php echo $options['userPicture']=='avatar'?"selected":""?>>WordPress Avatar</option>
   <option value="avatar_broadcaster" <?php echo $options['userPicture']=='avatar_broadcaster'?"selected":""?>>WP Avatar Broadcaster Only</option>
</select>
<BR>In advanced app broadcaster will have channel thumbnail (snapshot) as avatar. WP Avatar Broadcaster only shows broadcaster avatar in HTML chat and no avatars for viewers.

<br>Test: Your avatar as provided by get_avatar_url() WP function:
<br><IMG SRC="<?php echo get_avatar_url(get_current_user_id()); ?>" />



<h4>Channel Page Layout URL</h4>
<select name="channelUrl" id="channelUrl">
  <option value="post" <?php echo $options['channelUrl']=='post'?"selected":""?>>Post (Theme)</option>
  <option value="full" <?php echo $options['channelUrl']=='full'?"selected":""?>>Full Page</option>
</select>
<br>URL where to show channels from listings (implemented in listings).

<h4>Post Channels</h4>
<select name="postChannels" id="postChannels">
  <option value="1" <?php echo $options['postChannels']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['postChannels']?"":"selected"?>>No</option>
</select>
<BR>Enables special post types (channels) and static urls for easy access to broadcast, watch and preview video.
<BR>This is required by other features like frontend channel management.
<BR><?php echo $root_url; ?>channel/chanel-name/broadcast
<BR><?php echo $root_url; ?>channel/chanel-name/
<BR><?php echo $root_url; ?>channel/chanel-name/video
<BR><?php echo $root_url; ?>channel/chanel-name/hls - Video must be transcoded to HLS format for iOS or published directly in such format with external encoder.
<BR><?php echo $root_url; ?>channel/chanel-name/external - Shows rtmp settings to use with external applications (if supported).

<h4>Post Template Filename</h4>
<input name="postTemplate" type="text" id="postTemplate" size="20" maxlength="64" value="<?php echo $options['postTemplate']?>"/>
<br>Template file located in current theme folder, that should be used to render channel post page. Ex: page.php, single.php
<br><?php
				if ($options['postTemplate'] != '+plugin')
				{
					$single_template = get_stylesheet_directory() . '/' . $options['postTemplate'];
					echo $single_template . ' : ';
					if (file_exists($single_template)) echo 'Found.';
					else echo 'Not Found! Use another theme file!';
				}
?>
<br>Set "+plugin" to use a template provided by this plugin, instead of theme templates.

<h4>User Channels</h4>
<select name="userChannels" id="userChannels">
  <option value="1" <?php echo $options['userChannels']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['userChannels']?"":"selected"?>>No</option>
</select>
<BR>Enables users to start channel with own name by accessing a common static broadcasting link. Legacy feature. Recommended: No
<BR><a href="<?php echo $broadcast_url; ?>"><img src="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_webcam.png" align="absmiddle"
border="0"><?php echo $broadcast_url; ?></a>

<h4>Custom Channels</h4>
<select name="anyChannels" id="anyChannels">
  <option value="1" <?php echo $options['anyChannels']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['anyChannels']?"":"selected"?>>No</option>
</select>
<BR>Enables users to start channel by passing any channel name in link. Legacy feature. Recommended: No
<BR><a href="<?php echo $broadcast_url . urlencode($username); ?>"><img src="<?php echo $root_url; ?>wp-content/plugins/videowhisper-live-streaming-integration/ls/templates/live/i_webcam.png"
align="absmiddle" border="0"><?php echo $broadcast_url . urlencode($username); ?></a>


<h4>Chat Advertising Server</h4>
<input name="adServer" type="text" id="adServer" size="80" maxlength="256" value="<?php echo $options['adServer']?>"/>
<br>Use 'ads' for local content. See <a href="http://www.adinchat.com" target="_blank"><U><b>AD in Chat</b></U></a> compatible ad management server. This can be controlled by channel owners based on features setup.

<h4>Chat Advertising Interval</h4>
<input name="adsInterval" type="text" id="adsInterval" size="6" maxlength="6" value="<?php echo $options['adsInterval']?>"/>
<BR>Setup adsInterval in milliseconds (0 to disable ad calls).

<h4>Chat Advertising Content</h4>
<textarea name="adsCode" id="adsCode" cols="64" rows="8"><?php echo $options['adsCode']?></textarea>
<br>Shows from time to time in chat, if internal 'ads' server is enabled.

<h4><a target="_plugin" href="https://wordpress.org/plugins/rate-star-review/">Rate Star Review</a> - Enable Star Reviews</h4>
<?php
				if (is_plugin_active('rate-star-review/rate-star-review.php')) echo 'Detected:  <a href="admin.php?page=rate-star-review">Configure</a>'; else echo 'Not detected. Please install and activate Rate Star Review by VideoWhisper.com from <a href="plugin-install.php?s=videowhisper+rate+star+review&tab=search&type=term">Plugins > Add New</a>!';
?>
<BR><select name="rateStarReview" id="rateStarReview">
  <option value="0" <?php echo $options['rateStarReview']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['rateStarReview']?"selected":""?>>Yes</option>
</select>
<br>Enables Rate Star Review integration. Shows star ratings on listings and review form, reviews on item pages.

<h4>Show VideoWhisper Powered by</h4>
<select name="videowhisper" id="videowhisper">
  <option value="0" <?php echo $options['videowhisper']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['videowhisper']?"selected":""?>>Yes</option>
</select>

<?php
				break;

			case 'webrtc':

				/*
	//?? profile-level-id=64C029

	 "avc1.66.30": {profile:"Baseline", level:3.0, max_bit_rate:10000}
	 //iOS friendly variation (iOS 3.0-3.1.2)
	 "avc1.42001e": {profile:"Baseline", level:3.0, max_bit_rate:10000} ,
	 "avc1.42001f": {profile:"Baseline", level:3.1, max_bit_rate:14000}
	 //other variations ,
	 "avc1.77.30": {profile:"Main", level:3.0, max_bit_rate:10000}
	 //iOS friendly variation (iOS 3.0-3.1.2) ,
	 "avc1.4d001e": {profile:"Main", level:3.0, max_bit_rate:10000} ,
	 "avc1.4d001f": {profile:"Main", level:3.1, max_bit_rate:14000} ,
	 "avc1.4d0028": {profile:"Main", level:4.0, max_bit_rate:20000} ,
	 "avc1.64001f": {profile:"High", level:3.1, max_bit_rate:17500} ,
	 "avc1.640028": {profile:"High", level:4.0, max_bit_rate:25000} ,
	 "avc1.640029": {profile:"High", level:4.1, max_bit_rate:62500}
*/
			$webrtcDisabled = self::requirementDisabled('wsURLWebRTC_configure');
			if ($webrtcDisabled) $options['webrtc'] = 0;	
?>
<h3>WebRTC</h3>
WebRTC can be used to broadcast and playback live video in HTML5 browsers. Solution can use Wowza SE as relay for WebRTC streams. WebRTC is a new real time video communication technology under development and with specific requirements and limitations. Warning: Enabling this without proper server configuration will make streaming functionality unavailable. Also see: <a href="https://videochat-scripts.com/troubleshoot-html5-and-webrtc-streaming-in-videowhisper/">Troubleshoot HTML5 Live Streaming Quality</a>

<h4>WebRTC</h4>
<select name="webrtc" id="webrtc" <?php echo $webrtcDisabled ?>>
  <option value="0" <?php echo $options['webrtc']?"":"selected"?>>Disabled</option>
  <option value="1" <?php echo $options['webrtc']=='1'?"selected":""?>>Enabled</option>
  <option value="2" <?php echo $options['webrtc']=='2'?"selected":""?>>Available</option>
  <option value="3" <?php echo $options['webrtc']=='3'?"selected":""?>>Adaptive</option>
  <option value="4" <?php echo $options['webrtc']=='4'?"selected":""?>>Preferred</option>
</select>
<BR>Enable after configuring WebRTC settings.
<BR>Showing WebRTC published channels as live and snapshots requires RTMP Session Control feature. Warning: Web Status must be enabled, configured and Auto requires accessing with flash applications once to configure server restriction.
<BR>Web Broadcasting: Enabled shows this option for iOS/Android (Auto). Available will show option for broadcast under Flash interface. Adaptive will use depending on source. If Preferred will be used instead of Flash, in Auto mode.

<h4>Relay WebRTC WebSocket URL</h4>
<input name="wsURLWebRTC" type="text" id="wsURLWebRTC" size="100" maxlength="256" value="<?php echo $options['wsURLWebRTC']?>"/>
<BR><?php echo self::requirementRender('wsURLWebRTC_configure') ?>
<BR>Relay WebRTC WebSocket URL (wss with SSL certificate). Formatted as wss://[server-with-ssl]:[port]/webrtc-session.json .
<BR>Requires latest Wowza Streaming Engine server configured for WebRTC support and with a SSL certificate. Such setup is available with <a href="http://videowhisper.com/?p=Wowza+Media+Server+Hosting#features" target="_vwhost">VideoWhisper Wowza Turnkey Managed Hosting</a>.

<h4>Relay WebRTC Application</h4>
<input name="applicationWebRTC" type="text" id="applicationWebRTC" size="100" maxlength="256" value="<?php echo $options['applicationWebRTC']?>"/>
<BR>Relay Application Name (configured or WebRTC usage). Ex: videowhisper-webrtc
<BR>Server and application must match RTMP server settings, for streams to be available across protocols. Streams published with WebRTC can be played directly in HTML5 browsers or transcoded using advanced Flash player watch interface / plain live video, in browsers that support that.

<h4>RTSP Playback Address</h4>
<input name="rtsp_server" type="text" id="rtsp_server" size="100" maxlength="256" value="<?php echo $options['rtsp_server']?>"/>
<BR>For retrieving WebRTC streams. Ex: rtsp://[your-server]/videowhisper-x
<BR>Access WebRTC (RTSP) stream for snapshots, transcoding for RTMP/HLS/MPEGDASH playback.

<h4>RTSP Publish Address</h4>
<input name="rtsp_server_publish" type="text" id="rtsp_server_publish" size="100" maxlength="256" value="<?php echo $options['rtsp_server_publish']?>"/>
<BR>For publishing WebRTC streams. Usually requires publishing credentials (for Wowza configured in conf/publish.password). Ex: rtsp://[user:password@][your-server]/videowhisper-x

<h4>Video Codec</h4>
<select name="webrtcVideoCodec" id="webrtcVideoCodec">
  <option value="42e01f" <?php echo $options['webrtcVideoCodec']=='42e01f'?"selected":""?>>H.264 Profile 42e01f</option>
  <option value="VP8" <?php echo $options['webrtcVideoCodec']=='VP8'?"selected":""?>>VP8</option>
 <!--

     <option value="VP8" <?php echo $options['webrtcVideoCodec']=='VP8'?"selected":""?>>VP8</option>
  <option value="VP9" <?php echo $options['webrtcVideoCodec']=='VP9'?"selected":""?>>VP9</option>

  <option value="420010" <?php echo $options['webrtcVideoCodec']=='420010'?"selected":""?>>H.264 420010</option>
  <option value="420029" <?php echo $options['webrtcVideoCodec']=='420029'?"selected":""?>>H.264 420029</option>

  -->
</select>
<br>Safari supports VP8 from version 12.1 for iOS & PC and H264 in older versions. Because Safari uses hardware encoding for H264, profile may not be suitable for playback without transcoding, depending on device: VP8 is recommended when broadcasting with latest Safari. H264 can also playback directly in HLS, MPEG, Flash without additional transcoding (only audio is transcoded). Using hardware encoding (when functional) involves lower device resource usage and longer battery life.

<h4>Maximum Video Bitrate</h4>
<?php
						$sessionsVars = self::varLoad($options['uploadsPath']. '/sessionsApp');
						if (is_array($sessionsVars)) 
						{
							if (array_key_exists( 'limitClientRateIn', $sessionsVars) ) 
							{
								$limitClientRateIn = intval($sessionsVars['limitClientRateIn']) * 8 / 1000;
								
								echo 'Detected hosting client upload limit: ' . ($limitClientRateIn?$limitClientRateIn.'kbps': 'unlimited') . '<br>';
								
								$maxVideoBitrate = $limitClientRateIn - 100;
								if ($options['webrtcAudioBitrate']>90) $maxVideoBitrate = $limitClientRateIn - $options['webrtcAudioBitrate'] - 10;
								
								if ($limitClientRateIn) if ($options['webrtcVideoBitrate'] > $maxVideoBitrate)
								{
									echo '<b>Warning: Adjust bitrate to prevent disconnect / failure.<br>Video bitrate should be 100kbps lower than total upload so it fits with audio and data added. Save to apply!</b><br>';
									$options['webrtcVideoBitrate'] =  $maxVideoBitrate;
								}
							}
						}					
?>
<input name="webrtcVideoBitrate" type="text" id="webrtcVideoBitrate" size="10" maxlength="16" value="<?php echo $options['webrtcVideoBitrate']?>"/>
<BR>Maximum video bitrate. Ex: 800. Max 400 for TCP.
<br>If streaming hosting upload is limited, video bitrate should be 100kbps lower than total upload so it fits with audio and data added. Trying to broadcast higher will result in disconnect/failure.

<h4>Audio Codec</h4>
<select name="webrtcAudioCodec" id="webrtcAudioCodec">
  <option value="opus" <?php echo $options['webrtcAudioCodec']=='opus'?"selected":""?>>Opus</option>
  <option value="vorbis" <?php echo $options['webrtcAudioCodec']=='vorbis'?"selected":""?>>Vorbis</option>
</select>
<BR>Used with 64 audio bitrate.

<h4>Transcode streams to WebRTC</h4>
<select name="transcodeRTC" id="transcodeRTC">
  <option value="0" <?php echo $options['transcodeRTC']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodeRTC']=='1'?"selected":""?>>Yes</option>
</select>
<br>Make streams from other sources available for WebRTC playback. Involves processing resources (high CPU & memory load). 

<h4>FFMPEG Transcoding Parameters for WebRTC Playback (H264 + Opus)</h4>
<input name="ffmpegTranscodeRTC" type="text" id="ffmpegTranscodeRTC" size="100" maxlength="256" value="<?php echo $options['ffmpegTranscodeRTC']?>"/>
<BR>This should convert RTMP stream to H264 baseline restricted video and Opus audio, compatible with most WebRTC supporting browsers.
<br>For most browsers including Chrome, Safari, Firefox: -c:v libx264 -profile:v baseline -level 3.0 -c:a libopus -tune zerolatency
<br>For some browsers like Chrome, Firefox, not Safari, when broadcasting H264 baseline from flash client video can play as is: -c:v copy -c:a libopus
<br>Default: <?php echo $optionsDefault['ffmpegTranscodeRTC']?>

<h4>Transcode streams From WebRTC</h4>
<select name="transcodeFromRTC" id="transcodeFromRTC">
  <option value="0" <?php echo $options['transcodeFromRTC']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodeFromRTC']=='1'?"selected":""?>>Yes</option>
</select>
<br>Make streams from WebRTC available for HLS/MPEG/RTMP playback. Involves processing resources (high CPU & memory load). 
<br>Transcoding is required for archiving WebRTC streams (H264&AAC streams starting with "i_" can be imported).

<h4>WebRTC Implementation</h4>
WebRTC streaming is done trough media server, as relay, for reliability and scalability needed for these solutions.
Conventional out-of-the-box WebRTC solutions require each client to establish and maintain separate connections with every other participant in a complicated network where the bandwidth load increases exponentially as each additional participant is added. For P2P, streaming broadcasters need server grade connections to live stream to multiple users and using a regular home ADSL connection (that has has higher download and bigger upload) causes real issues. These solutions use the powerful streaming server as WebRTC node to overcome scalability and reliability limitations. Solution combines WebRTC HTML5 streaming with relay server streaming for a production ready setup.

<h4>Current Implementation Support and Limitations</h4>
As WebRTC is a new technology under development, implementation support varies depending on browsers and settings. These may change with solution development and technology improvements. Here is current status:
<UL>
	<LI>Chrome: Functional on Android and PC. Supports broadcast and playback. Stream broadcast with Chrome is available in most HTML5 browsers, including Safari as direct WebRTC.</LI>
	<LI>Firefox: Functional, supports broadcasting and playback over UDP. </LI>
	<LI>Other supported browsers: Brave, Tor.</LI>
	<LI>Safari: Functional on iOS. On PC, stream broadcast from Safari may be encoded with high profile setting so transcoding is required.<LI>
	<LI>Transcoding from WebRTC: Video and audio published with WebRTC is available in RTMP/HLS/MPEGDASH after transcoding, with some latency and availability delay.</LI>
	<LI>Transcoding from RTMP: Video and audio published with RTMP is available for WebRTC playback after transcoding, with some latency and availability delay.</LI>
</UL>
Implementation Limitations:
<UL>
	<LI>Advanced interactions specific to VideoWhisper Flash apps (like kick, tips) are not available, yet.</LI>
	<LI>Different chat system show messages with some external update delays between flash and html chat. Users list do not sync (external htmlchat users don't show in flash application).</LI>
</UL>

<?php
				break;

			case 'hls':
						
?>
<h3>Transcoding, HTML5, HLS, MPEG DASH</h3>
Configure transcoding and HTML5 based HLS, MPEG DASH delivery. HTTP Live Streaming is a great option for streaming to mobile browsers. Transcoding is required to convert between specific encoding formats required by HTML5 HLS, MPEG, WebRTC or Flash.
<BR>Special Requirements: This functionality requires FFMPEG with necessary codecs on web host and publishing trough Wowza Streaming Engine server to deliver transcoded streams as HLS.
<BR>Recommended Hosting: <a href="https://webrtchost.com/hosting-plans/#Complete-Hosting" target="_vwhost">VideoWhisper Turnkey Complete Managed Hosting</a> - turnkey rtmp address, configuration for archiving, transcoding streams, delivery to mobiles as HLS, playlists scheduler, IP cameras, advanced external encoder support.

<h4>Clarifications on Transcoding</h4>
Flash and RTMP camera streaming applications are not supported in mobile browsers. Special solutions are required for mobile users to implement support for this type of features (<A href="https://videowhisper.com/?p=iPhone-iPad-Apps">read more</a>) including transcoding the streams to HTML5 formats.
<BR>Plain streaming is possible in mobile browser with HTML5 as HLS (HTTP Live Streaming), MPEG-DASH, WebRTC depending on browsers.
<BR>Broadcasting from mobile is possible with generic RTMP mobile encoders like Wowza GoCoder for <a href="https://itunes.apple.com/us/app/wowza-gocoder/id640338185?mt=8">iOS</a> / <a href="https://play.google.com/store/apps/details?id=com.wowza.gocoder&hl=en">Android</a> that can be used to publish plain stream (no chat or interactions) and <a href="admin.php?page=live-streaming&tab=webrtc">HTML5 WebRTC</a>. Generic encoders require user to copy and paste rtmp address, channel name, settings and also <a href="https://videowhisper.com/?p=RTMP-Session-Control">RTMP Session Control</a> (included with VideoWhisper recommended Wowza hosting) to show external published streams as active channels on site.
<BR>For advanced interactions and easy usage/access with site credentials login, <a href="https://videowhisper.com/?p=iPhone-iPad-Apps#apps">custom apps can be developed</a> and then <a href="admin.php?page=live-streaming&tab=app">configured from this plugin</a>.



<h3>Detection: FFMPEG & Codecs</h3>
<?php

				echo "FFMPEG: ";
				// $cmd ='timeout -s KILL 3 ' . $options['ffmpegPath'] . ' -version';
				$cmd = $options['ffmpegPath'] . ' -version';

				$output="";
				exec($cmd, $output, $returnvalue);
				if ($returnvalue == 127)  
				{
					echo "<b>Warning: not detected: $cmd</b>";
					self::requirementUpdate('ffmpeg',0);		
				}
				else
				{
					echo "found";

					if ($returnvalue != 126)
					{
						echo '<BR>' . $output[0];
						echo '<BR>' . $output[1];
						
						self::requirementUpdate('ffmpeg',1);
					}else
					{
						echo ' but is NOT executable by current user: ' . $processUser;
						self::requirementUpdate('ffmpeg',0);	
					}	
				}

?>
<BR><?php echo self::requirementRender('ffmpeg') ?>
<?php
				$cmd =$options['ffmpegPath'] . ' -codecs';
				exec($cmd, $output, $returnvalue);

				//detect codecs
				$hlsAudioCodec = ''; //hlsAudioCodec
				if ($output) if (count($output))
					{
						echo "<br>Codec libraries:";
						foreach (array('h264', 'vp6','speex', 'nellymoser', 'aacplus', 'vo_aacenc', 'faac', 'fdk_aac', 'vp8', 'vp9', 'opus') as $cod)
						{
							$det=0; $outd="";
							echo "<BR>$cod : ";
							foreach ($output as $outp) if (strstr($outp,$cod)) { $det=1; $outd=$outp; };

							if ($det) echo "detected ($outd)";
							elseif (in_array($cod,array('aacplus', 'vo_aacenc', 'faac', 'fdk_aac'))) echo "lib$cod is missing but other aac codec may be available";
							else echo "<b>missing: configure and install FFMPEG with lib$cod if you don't have another library for that codec</b>";

							if ($det && in_array($cod,array('aacplus', 'vo_aacenc', 'faac', 'fdk_aac')))  $hlsAudioCodec = 'lib'. $cod;
						}
					}
?>
<BR>You need only 1 AAC codec for transcoding to AAC. Depending on <a href="https://trac.ffmpeg.org/wiki/Encode/AAC#libfaac">AAC library available on your system</a> you may need to update transcoding parameters. Latest FFMPEG also includes a native encoder (aac).


<?php
			$ffmpegDisabled = self::requirementDisabled('ffmpeg');
			if ($ffmpegDisabled) $options['transcoding'] = 0;				
?>

<h4>Enable HTML5 Transcoding</h4>
<select name="transcoding" id="transcoding" <?php echo $ffmpegDisabled ?>>
  <option value="0" <?php echo $options['transcoding']?"":"selected"?>>Disabled</option>
  <option value="1" <?php echo $options['transcoding'] == 1 ?"selected":""?>>Enabled</option>
  <option value="2" <?php echo $options['transcoding'] == 2 ?"selected":""?>>Available</option>
  <option value="3" <?php echo $options['transcoding'] == 3 ?"selected":""?>>Adaptive</option>
  <option value="4" <?php echo $options['transcoding'] == 4 ?"selected":""?>>Preferred</option>
</select>
<BR>This enables account level transcoding based on FFMPEG (if requirements are present). <BR>Transcoding is required for re-encoding live streams broadcast using web client to new re-encoded streams accessible by mobile HTML5 browsers using HLS / MPEG DASH. This requires high server processing power for each stream.
<BR>Transcoding is also required when converting streams between RTMP and WebRTC.
<BR>HLS support is also required on RTMP server and this is usually available with <a href="https://videowhisper.com/?p=Wowza+Media+Server+Hosting">Wowza Hosting</a> .
<BR>Account level transcoding is not required when stream is already broadcast with external encoders in appropriate formats (H264, AAC with supported settings) or using Wowza Transcoder Addon (usually on dedicated servers).
<BR>HTML5 Playback: If transcoding is enabled will be played on mobiles (Auto). If Available will be also shown to PC users as option. Adaptive will try to show interface depending on source. If Preferred will be used instead of Flash, in Auto mode.


<h4>Live Transcoding</h4>
<?php

				$processUser = get_current_user();
				$processUid = getmyuid();

				echo "This section shows FFMPEG transcoding and snapshot retrieval processes currently run by account '$processUser' (#$processUid). Transcoding starts some time after stream is published for VideoWhisper web apps or when RTMP Session Control is enabled.<BR>";

				$cmd = "ps aux | grep 'ffmpeg'";
				exec($cmd, $output, $returnvalue);
				//var_dump($output);

				$transcoders = 0;
				foreach ($output as $line) if (strstr($line, "ffmpeg"))
					{
						$columns = preg_split('/\s+/',$line);
						if (($processUser == $columns[0] || $processUid == $columns[0]) && (!in_array($columns[10],array('sh','grep'))))
						{

							echo " + Process #".$columns[1]." CPU: ".$columns[2]." Mem: ".$columns[3].' Start: '.$columns[8].' CPU Time: '.$columns[9]. ' Cmd: ';
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

?>


<h4>FFMPEG Path</h4>
<input name="ffmpegPath" type="text" id="ffmpegPath" size="100" maxlength="256" value="<?php echo $options['ffmpegPath']?>"/>
<BR>Path to latest FFMPEG. Required for transcoding of web based streams, generating snapshots for external broadcasting applications (requires <a href="https://videowhisper.com/?p=RTMP-Session-Control">rtmp session control</a> to notify plugin about these streams).
Default: <?php echo $optionsDefault['ffmpegPath']?>


<h4>FFMPEG Codec Configuration</h4>
<select name="ffmpegConfiguration" id="ffmpegConfiguration">
  <option value="0" <?php echo $options['ffmpegConfiguration']?"":"selected"?>>Manual</option>
  <option value="1" <?php echo $options['ffmpegConfiguration'] == 1 ?"selected":""?>>Auto</option>
</select>
<BR>Auto will configure based on detected AAC codec libraries (recommended). Requires saving settings to apply.

<?php
				$hlsAudioCodecReadOnly = '';

				if ($options['ffmpegConfiguration'])
				{
					if (!$hlsAudioCodec) $hlsAudioCodec = 'aac';
					$options['ffmpegTranscode'] = "-c:v copy -c:a $hlsAudioCodec -b:a 96k";
					
					if ($options['webrtcVideoCodec'] != '42e01f') 
					{
						$options['ffmpegTranscode'] = " -c:v libx264 -profile:v baseline -level 3.0 -c:a $hlsAudioCodec -b:a 96k -tune zerolatency";
						echo '<br>Warning: As WebRTC is not configured to use H264, video also needs to be transcoded. This requires high hosting processing resources which may result in slower site speed or failed requests. A hosting plan with high processing resources (CPU & memory) is required for video transcoding.';
					}
					
					$hlsAudioCodecReadOnly = 'readonly';
				}
?>

<h4>FFMPEG Transcoding Parameters for HLS / MPEG-DASH / Flash Playback (H264 + AAC)</h4>
<input name="ffmpegTranscode" type="text" id="ffmpegTranscode" size="100" maxlength="256" value="<?php echo $options['ffmpegTranscode']?>" <?php echo $hlsAudioCodecReadOnly ?>/>
<BR>For lower server load and higher performance, web clients should be configured to broadcast video already suitable for target device (H.264 Baseline 3.1 for most iOS devices) so only audio needs to be encoded.

<BR>Ex.(transcode audio using latest FFMPEG with libfdk_aac): -c:v copy -c:a libfdk_aac -b:a 96k
<BR>Ex.(transcode audio using latest FFMPEG with native aac): -c:v copy -c:a aac -b:a 96k
<BR>Ex.(transcode video+audio in latest FFMPEG with libfdk_aac): -c:v libx264 -profile:v baseline -level 3.0 -c:a libfdk_aac -b:a 96k -tune zerolatency
<BR>Ex.(transcode audio using older FFMPEG with libfaac): -vcodec copy -acodec libfaac -ac 2 -ar 22050 -ab 96k
<BR>Ex.(transcode video+audio using older FFMPEG): -vcodec libx264 -s 480x360 -r 15 -vb 512k -x264opts vbv-maxrate=364:qpmin=4:ref=4 -coder 0 -bf 0 -analyzeduration 0 -level 3.1 -g 30 -maxrate 768k -acodec libfaac -ac 2 -ar 22050 -ab 96k
<BR>For advanced settings see <a href="https://developer.apple.com/library/ios/technotes/tn2224/_index.html#//apple_ref/doc/uid/DTS40009745-CH1-SETTINGSFILES">iOS HLS Supported Codecs<a> and <a href="https://trac.ffmpeg.org/wiki/Encode/AAC">FFMPEG AAC Encoding Guide</a>.

<h4>HTTP Streaming Base URL</h4>
This is used for accessing transcoded streams on HLS playback. Usually available with <a href="https://videowhisper.com/?p=Wowza+Media+Server+Hosting">Wowza Hosting</a> .<br>
<input name="httpstreamer" type="text" id="httpstreamer" size="100" maxlength="256" value="<?php echo $options['httpstreamer']?>"/>
<BR>External players and encoders (if enabled) are not monitored or controlled by this plugin, unless special <a href="https://videowhisper.com/?p=RTMP-Session-Control">rtmp side session control</a> is available.
<BR>Application folder must match rtmp application (ex: videowhisper-x)
<BR>Ex: https://[your-server]:1935/videowhisper-x/ works when publishing to rtmp://[your-server]/videowhisper-x
<BR>HTTPS Recommended: Some browsers will require a SSL certificate for MPEG DASH / HLS streaming and show warnings/errors if using mixed or unsecure urls.

<h4>Transcode streams to WebRTC</h4>
<select name="transcodeRTC" id="transcodeRTC">
  <option value="0" <?php echo $options['transcodeRTC']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodeRTC']=='1'?"selected":""?>>Yes</option>
</select>
<br>Make streams from other sources available for WebRTC playback. Involves processing resources (high CPU & memory load). 

<?php
				$hlsAudioCodecReadOnly = '';

				if ($options['ffmpegConfiguration'])
				{
					$options['ffmpegTranscodeRTC'] = "-c:v copy -c:a libopus";
						$hlsAudioCodecReadOnly = 'readonly';

				}

?>
<h4>FFMPEG Transcoding Parameters for WebRTC Playback (H264+Opus)</h4>
<input name="ffmpegTranscodeRTC" type="text" id="ffmpegTranscodeRTC" size="100" maxlength="256" value="<?php echo $options['ffmpegTranscodeRTC']?>" <?php echo $hlsAudioCodecReadOnly ?>/>
<BR>This should convert RTMP stream to H264 baseline restricted and Opus, compatible with most browsers. Video tracks encoded with -c:v libx264 -profile:v baseline -level 3.0 can be used as is on some browers. Default WebRTC profile for H264 is 42e01f.
<br>For most browsers including Chrome, Safari, Firefox: -c:v libx264 -profile:v baseline -level 3.0 -c:a libopus -tune zerolatency
<br>For some browsers like Chrome, Firefox, not Safari, when broadcasting H264 baseline from flash client: -c:v copy -c:a libopus
<br>Default: <?php echo $optionsDefault['ffmpegTranscodeRTC']?>

<h4>Transcode streams From WebRTC</h4>
<select name="transcodeFromRTC" id="transcodeFromRTC">
  <option value="0" <?php echo $options['transcodeFromRTC']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodeFromRTC']=='1'?"selected":""?>>Yes</option>
</select>
<br>Make streams from WebRTC available for HLS/MPEG/RTMP playback. Involves processing resources (high CPU & memory load). 


<h4>Auto Transcoding</h4>
<select name="transcodingAuto" id="transcodingAuto">
  <option value="0" <?php echo $options['transcodingAuto']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodingAuto']=='1'?"selected":""?>>On Request</option>
  <option value="2" <?php echo $options['transcodingAuto']=='2'?"selected":""?>>Always</option>
</select>
<BR>On Request starts transcoder when HLS / MPEG DASH is requested (by a mobile user) and Always when broadcast occurs. As HLS latency is usually several seconds, first viewer may not be able to access stream when using On Request.
<BR>Always will also check transcoding status from time to time (when broadcaster updates status). For external broadcasters (desktop/mobile), <a href="https://videowhisper.com/?p=RTMP-Session-Control#configure">RTMP Session Control</a> is required to activate web transcoding.
<BR>Auto transcoding will work only if channel post <a href="admin.php?page=live-streaming&tab=features">Transcode Feature</a> is enabled.

<h4>Manual Transcoding</h4>
<select name="transcodingManual" id="transcodingManual">
  <option value="0" <?php echo $options['transcodingManual']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodingManual']=='1'?"selected":""?>>Yes</option>
</select>
<BR>Shows transcoding panel to broadcaster for manually toggling transcoding at runtime (for use when automated transcoding is disabled).


<h4>Transcoding Warning</h4>
<select name="transcodingWarning" id="transcodingWarning">
  <option value="0" <?php echo $options['transcodingWarning']?"":"selected"?>>Disabled</option>
  <option value="1" <?php echo $options['transcodingWarning']=='1'?"selected":""?>>Broadcaster</option>
  <option value="2" <?php echo $options['transcodingWarning']=='2'?"selected":""?>>Broadcaster and Viewers</option>
</select>
<BR>Warn users about latency and delay related to the extra operation of transcoding the stream and HLS delivery. Recommended while testing and for setups with multiple streaming options, for users to select optimal broadcast/delivery combination available (WebRTC, RTMP).

<h4>Transcode Re-Streams</h4>
<select name="transcodeReStreams" id="transcodeReStreams">
  <option value="0" <?php echo $options['transcodeReStreams']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodeReStreams']=='1'?"selected":""?>>Yes</option>
</select>
<br>Incoming streams should be encoded with H264 & AAC for playback without transcoding. Default: No

<h4>MPEG-Dash Device Target</h4>
<select name="detect_mpeg" id="detect_mpeg">
  <option value="" <?php echo $options['detect_mpeg']?"":"selected"?>>None</option>
  <option value="android" <?php echo $options['detect_mpeg']=='android'?"selected":""?>>Android</option>
  <option value="nonsafari" <?php echo $options['detect_mpeg']=='nonsafari'?"selected":""?>>Except Safari</option>
  <option value="all" <?php echo $options['detect_mpeg']=='all'?"selected":""?>>Android & PC</option>
</select>
<BR>Show MPEG Dash for certain types of devices. Most browsers will require HTTPS.

<h4>HLS Device Target</h4>
<select name="detect_hls" id="detect_hls">
  <option value="" <?php echo $options['detect_hls']?"":"selected"?>>None</option>
  <option value="ios" <?php echo $options['detect_hls']=='ios'?"selected":""?>>iOS</option>
  <option value="mobile" <?php echo $options['detect_hls']=='mobile'?"selected":""?>>iOS & Android</option>
  <option value="safari" <?php echo $options['detect_hls']=='safari'?"selected":""?>>iOS & PC Safari</option>
  <option value="all" <?php echo $options['detect_hls']=='all'?"selected":""?>>Mobile & PC Safari</option>
</select>
<BR>Show HLS for certain types of devices. Does not overwrite MPEG Dash if enabled. Mobile covers iOS & Android.

<h4>FFMPEG RTMP Timeout</h4>
<input name="ffmpegTimeout" type="text" id="ffmpegTimeout" size="5" maxlength="20" value="<?php echo $options['ffmpegTimeout']?>"/>s
<BR>Disconnect quick ffmpeg connections for stream info or snapshots after this timeout. Implemented by RTMP Session control.

<h4>FFMPEG Snapshot Background Command</h4>
<input name="ffmpegSnapshotBackground" type="text" id="ffmpegSnapshotBackground" size="20" maxlength="256" value="<?php echo $options['ffmpegSnapshotBackground']?>"/>
<br>Snapshot command background command. Leave blank to wait for completion (not send in background), which will result in script delay. Default: <?php echo $optionsDefault['ffmpegSnapshotBackground']?>

<h4>FFMPEG Snapshot Timeout Command</h4>
<input name="ffmpegSnapshotTimeout" type="text" id="ffmpegSnapshotTimeout" size="20" maxlength="256" value="<?php echo $options['ffmpegSnapshotTimeout']?>"/>
<br>Snapshot command timeout command. Leave blank to remove timeout. Default: <?php echo $optionsDefault['ffmpegSnapshotTimeout']?>


<h4>Support RTMP Streaming</h4>
<select name="supportRTMP" id="supportRTMP">
  <option value="0" <?php echo $options['supportRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportRTMP']?"selected":""?>>Yes</option>
</select>
<BR>Recommended: Yes. Streaming trough the relay RTMP server is most reliable and compulsory for some features like HLS, external player delivery.

<h4>Always do RTMP Streaming</h4>
<p>Enable this if you want all streams to be published to server, no matter if there are registered subscribers or not (in example if you're using server side video archiving and need all streams
published for recording).</p>
<select name="alwaysRTMP" id="alwaysRTMP">
  <option value="0" <?php echo $options['alwaysRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysRTMP']?"selected":""?>>Yes</option>
</select>
<BR>Recommended: Yes. Warning: Disabling this can disable HLS delivery and increase starting latency for streams. This should be available as backup streaming solution even if P2P is used (in specific conditions).


<?php
				break;

				/*
case 'ipcamera':
?>
<h3>IP Camera / Re-Streaming Settings</h3>
Configuring different streaming server settings is useful when you don't want to archive these streams.
<?php
break;
*/

	case 'external':
?>
<h3>External Encoder/App Settings</h3>
Users can broadcast using external RTMP encoding applications (<a href="https://obsproject.com">OBS Open Broadcaster Software</a>, <a href="https://itunes.apple.com/us/app/wowza-gocoder/id640338185?mt=8">GoCoder iOS</a>/<a href="https://play.google.com/store/apps/details?id=com.wowza.gocoder&hl=en">Android app</a>, XSplit, Adobe Flash Media Live Encoder, Wirecast).
<br>External players and encoders (if enabled) are not monitored or controlled by this plugin, unless special <a href="https://videowhisper.com/?p=RTMP-Session-Control">rtmp side session control</a> is available.
 
 <h4>External Application Addresses</h4>
<select name="externalKeys" id="externalKeys">
  <option value="0" <?php echo $options['externalKeys']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['externalKeys']?"selected":""?>>Yes</option>
</select>
<BR>Shows "External Apps" button for each channel in Broadcast Live section. Channel owners will receive access to their secret publishing and playback addresses for each channel.
<BR>Enables external application support by inserting authentication info (username, channel name, key for broadcasting/watching) directly in RTMP address. RTMP server will pass these parameters to webLogin scripts for direct authentication without website access. This feature requires special RTMP side support for managing these parameters.
<br>Advanced external app session control requires <a href="https://videowhisper.com/?p=RTMP-Session-Control">rtmp side session control</a> setup.
 <BR><?php echo self::requirementRender('rtmp_status') ?>

 <h4>External Transcoder Keys</h4>
<select name="externalKeysTranscoder" id="externalKeysTranscoder">
  <option value="0" <?php echo $options['externalKeysTranscoder']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['externalKeysTranscoder']?"selected":""?>>Yes</option>
</select>
<BR>Direct authentication parameters will be used for transcoder, external stream thumbnails in case webLogin is enabled. RTMP server will pass these parameters to webLogin scripts for direct authentication without website access. Without this FFMPEG requests would be denied by streaming server as unauthorized.

<h4>External Encoder Transcoding</h4>
<select name="transcodeExternal" id="transcodeExternal">
  <option value="0" <?php echo $options['transcodeExternal']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['transcodeExternal']=='1'?"selected":""?>>Yes</option>
</select>
<BR>Only enable if external streams (from OBS, Wirecast, GoCoder) don't come encoded as H264 & AAC.
<br>Warning: Transcoding involves extra latency, extra delay for stream to become available in new version and high server processing load (cpu & memory).


<?php
	break;
	
			case 'server':

?>
<h3>Server Settings</h3>
Configure server settings for RTMP (live interactions and streaming) and HTTP (web scripts).
<br>To run this, make sure your hosting environment meets all <a href="https://videowhisper.com/?p=Requirements" target="_vwrequirements">requirements</a>.
<BR>Recommended hosting: <a href="https://webrtchost.com/hosting-plans/#Complete-Hosting" target="_blank">Complete Turnkey Streaming Hosting</A> - turnkey settings for RTMP/WebRTC/HLS/RTSP, session control, playlists scheduler, IP cameras, advanced external encoder support.

<h4>RTMP Address</h4>
<input name="rtmp_server" type="text" id="rtmp_server" size="100" maxlength="256" value="<?php echo $options['rtmp_server']?>"/>
<BR><?php echo self::requirementRender('rtmp_server_configure') ?>

<BR>If you have a supported RTMP streaming server but don't have a videowhisper rtmp address yet (from a managed rtmp host), go to <a href="https://videowhisper.com/?p=RTMP+Applications" target="_blank">RTMP Application Setup</a> for installation details.
<BR>A public accessible rtmp hosting server is required with custom videowhisper rtmp side. Ex: rtmp://your-server/videowhisper
<BR>The custom VideoWhisper rtmp side functionality is compulsory as it manages advanced functionality like chat, online user lists, interactions, webcam/microphone status, advanced session control.

<h4>Streams Path (IP Camera Streams /  Playlists)</h4>
<input name="streamsPath" type="text" id="streamsPath" size="100" maxlength="256" value="<?php echo $options['streamsPath']?>"/>
<BR>Path to .stream files monitored by streaming server for restreaming.
<BR>Such functionality requires latest Wowza Streaming Engine, web and rtmp on same sever, <a href='https://www.wowza.com/forums/content.php?39-How-to-re-stream-video-from-an-IP-camera-(RTSP-RTP-re-streaming)#config_xml'>specific setup</a>. Streaming server loads configuration from web files, connects to IP camera stream or video file, loads stream and delivers in format suitable for web publishing.
<BR>This functionality is available with <a href="https://webrtchost.com/hosting-plans/#Complete-Hosting" target="_vwhost">VideoWhisper Complete Hosting plans</a> and servers, when hosting both web and rtmp on same plan/server so web scripts can access streaming configuration files.
If custom ports are used, server firewall must be configured to allow connections.
<BR>Can be same as streams path configured in VideoShareVOD.
<BR> <?php
				echo $options['streamsPath'] . ' : ';
				if (file_exists($options['streamsPath']))
				{
					echo 'Found. ';
					if (is_writable($options['streamsPath'])) echo 'Writable. (OK)';
					else echo 'NOT writable.';
				}
				else echo '<b>NOT found!</b>';
?>

<h4>Disable Bandwidth Detection</h4>
<p>Required on some rtmp servers that don't support bandwidth detection and return a Connection.Call.Fail error.</p>
<select name="disableBandwidthDetection" id="disableBandwidthDetection">
  <option value="0" <?php echo $options['disableBandwidthDetection']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['disableBandwidthDetection']?"selected":""?>>Yes</option>
</select>

<h4>Token Key</h4>
<input name="tokenKey" type="text" id="tokenKey" size="32" maxlength="64" value="<?php echo $options['tokenKey']?>"/>
<BR>A <a href="https://videowhisper.com/?p=RTMP+Applications#settings">secure token</a> can be used with Wowza Media Server.


<h4>Web Key, Web Login/Status, Session Control</h4>
<input name="webKey" type="text" id="webKey" size="32" maxlength="64" value="<?php echo $options['webKey']?>"/>
<BR>A web key can be used for <a href="https://videochat-scripts.com/videowhisper-rtmp-web-authetication-check/">VideoWhisper RTMP Web Session Check</a>. Configure as documented on <a href="https://videowhisper.com/?p=RTMP-Session-Control#configure">RTMP Session Control Configuration</a>. Application.xml settings in &lt;Root&gt;&lt;Application&gt;&lt;Properties&gt; :<br>

<textarea readonly cols="100" rows="4">
<?php
				$admin_ajax = admin_url() . 'admin-ajax.php';
				$webLogin = htmlentities($admin_ajax."?action=vwls&task=rtmp_login&s=");
				$webLogout = htmlentities($admin_ajax."?action=vwls&task=rtmp_logout&s=");
				$webStatus = htmlentities($admin_ajax."?action=vwls&task=rtmp_status");

				echo  htmlspecialchars("<!-- VideoWhisper.com: RTMP Session Control https://videowhisper.com/?p=rtmp-session-control -->
<Property>
<Name>acceptPlayers</Name>
<Value>true</Value>
</Property>
<Property>
<Name>webLogin</Name>
<Value>$webLogin</Value>
</Property>
<Property>
<Name>webKey</Name>
<Value>".$options['webKey']."</Value>
</Property>
<Property>
<Name>webLogout</Name>
<Value>$webLogout</Value>
</Property>
<Property>
<Name>webStatus</Name>
<Value>$webStatus</Value>
</Property>
")
?>
</textarea>
<BR><?php echo self::requirementRender('rtmp_status') ?>

<BR>Session Control license: webStatus will not work on 3rd party servers without a full mode license for RTMP side (channel online status will not update). Test if functional by monitoring if external broadcast remains LIVE and session control is detected in <a href="admin.php?page=live-streaming-stats">Statistics</a>.
<BR>Broadcaster can't connect at same time from web broadcasting interface and external encoder with session control (as session name will be rejected as duplicate).
<BR>Benefits of using <a href="https://videowhisper.com/?p=RTMP-Session-Control">RTMP Session Control</a>: advanced support for external encoders like OBS (shows channels as live on site, generates snapshots, usage stats, transcoding), protect rtmp address from external usage (broadcast and playback require the secret keys associated with active site channels), faster availability and updates for transcoding/snapshots.
<BR>Certain services or firewalls like Cloudflare will reject access of streaming server for web requests. Make sure configured web requests can be called by streaming server.
<br>Locked Streaming Settings: Session Control locks advanced features and security to a single installation. Other applications, plugins will not be able to use same streaming settings and will get rejected without the proper keys provided by this installation. Using multiple plugins / interfaces for similar features is confusing for users so it is recommended to use only one solution. A different solution can use a different live streaming setup or hosting plan.

<h4>Web Status, Session Control</h4>
<select name="webStatus" id="webStatus">
  <option value="auto" <?php echo $options['webStatus']=='auto'?"selected":""?>>Auto</option>
  <option value="enabled" <?php echo $options['webStatus']=='enabled'?"selected":""?>>Enabled</option>
  <option value="strict" <?php echo $options['webStatus']=='strict'?"selected":""?>>Strict</option>
  <option value="disabled" <?php echo $options['webStatus']=='disabled'?"selected":""?>>Disabled</option>
</select>
<BR>Auto will automatically enable first time webLogin successful authentication occurs for a broadcaster. Will also configure the server IP restriction.
<br>In Strict mode additional IPs can't be added by webLogin authorisation (not recommended as streaming server may have multiple IPs).
<br>Set Disabled to make sure WebRTC streams are displayed when session control does not work (otherwise it will show HLS teaser when offline). 

<h4>Web Status Server IP Restriction</h4>
<input name="rtmp_restrict_ip" type="text" id="rtmp_restrict_ip" size="100" maxlength="512" value="<?php echo $options['rtmp_restrict_ip']?>"/>
<BR>Allow status updates only from configured IP(s). If not defined will configure automatically when first successful webLogin authorisation occurs for a broadcaster. Web status will not work if this is empty or not configured right.
<BR>Some streaming servers use different IPs. All must be added as comma separated values.
<?php

				if (in_array($options['webStatus'], array('enabled', 'strict', 'auto')))
					if (file_exists($path = $options['uploadsPath']. '/_rtmpStatus.txt'))
					{
						$url = self::path2url($path);
						echo 'Found: <a target=_blank href="'.$url.'">last status request</a> ' . date("D M j G:i:s T Y", filemtime($path)) ;
					}
?>
<!--
<h4>Session Status</h4>
<select name="rtmpStatus" id="rtmpStatus">
  <option value="0" <?php echo $options['rtmpStatus']=='0'?"":"selected"?>>Auto</option>
  <option value="1" <?php echo $options['rtmpStatus']=='1'?"selected":""?>>RTMP</option>
</select>
<BR>Session status allows monitoring and controlling online users sessions.
<BR>Auto: Will monitor web sessions based on requests from HTTP clients (VideoWhisper web applications) and other clients by RTMP.
<BR>RTMP: Will monitor all clients by RTMP, including web clients. Web monitoring is disabled.
-->


<h4>On Demand Archiving</h4>
<input name="manualArchiving" type="text" id="manualArchiving" size="100" maxlength="200" value="<?php echo $options['manualArchiving']?>"/>
<BR>URL to control archiving by web. Leave blank to disable. Sample setting: http://[username]:[password]@[wowza-ip-address]:8086/livestreamrecord?app=videowhisper
<BR>On demand archiving can be enabled on Wowza server as documented at https://www.wowza.com/forums/content.php?123-How-to-record-live-streams-(HTTPLiveStreamRecord) . Also requires crossdomain.xml on Wowza web space.


<h4>Always do RTMP Streaming</h4>
<p>Enable this if you want all streams to be published to server, no matter if there are registered subscribers or not (in example if you're using server side video archiving and need all streams
published for recording).</p>
<select name="alwaysRTMP" id="alwaysRTMP">
  <option value="0" <?php echo $options['alwaysRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysRTMP']?"selected":""?>>Yes</option>
</select>
<BR>Recommended: Yes. Warning: Disabling this can disable HLS delivery and increase starting latency for streams. This should be available as backup streaming solution even if P2P is used (in specific conditions).

<!--
<h4>RTMFP Address</h4>
<p> Get your own independent RTMFP address by registering for a free <a href="https://www.adobe.com/cfusion/entitlement/index.cfm?e=cirrus" target="_blank">Adobe Cirrus developer key</a>. This is
required for P2P support.</p>
<input name="serverRTMFP" type="text" id="serverRTMFP" size="80" maxlength="256" value="<?php echo $options['serverRTMFP']?>"/>
<h4>P2P Group</h4>
<input name="p2pGroup" type="text" id="p2pGroup" size="32" maxlength="64" value="<?php echo $options['p2pGroup']?>"/>
<h4>Support RTMP Streaming</h4>
<select name="supportRTMP" id="supportRTMP">
  <option value="0" <?php echo $options['supportRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportRTMP']?"selected":""?>>Yes</option>
</select>
<BR>Recommended: Yes. Streaming trough the relay RTMP server is most reliable and compulsory for some features like HLS, external player delivery.

<h4>Support P2P RTMFP Streaming</h4>
<select name="supportP2P" id="supportP2P">
  <option value="0" <?php echo $options['supportP2P']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['supportP2P']?"selected":""?>>Yes</option>
</select>
<BR>Recommended: No. Warning: P2P is not reliable for most users with regular home connections (most users with regular connections will not be able to broadcast or watch video if that's enabled). P2P is great for users with server grade connections (public IP, high upload) or users in same network.
<BR>Warning: Streaming only P2P over RTMFP disables archiving, transcoding and HTML5 delivery (HLS MPEG WebRTC) as streams no longer go trough server.

<h4>Always do P2P RTMFP Streaming</h4>
<select name="alwaysP2P" id="alwaysP2P">
  <option value="0" <?php echo $options['alwaysP2P']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysP2P']?"selected":""?>>Yes</option>
</select>
<BR>Recommended: No.
-->

<h4>Uploads Path</h4>
<p>Path where logs and snapshots will be uploaded. Make sure you use a location outside plugin folder to avoid losing logs on updates and plugin uninstallation.</p>
<input name="uploadsPath" type="text" id="uploadsPath" size="80" maxlength="256" value="<?php echo $options['uploadsPath']?>"/>
<?php
				if (!file_exists($options['uploadsPath'])) echo '<br><b>Warning: Folder does not exist. If this warning persists after first access check path permissions:</b> ' . $options['uploadsPath'];
				if (!strstr($options['uploadsPath'], get_home_path() )) echo '<br><b>Warning: Uploaded files may not be accessible by web (path is not within WP installation path).</b>';

				echo '<br>WordPress Path: ' . get_home_path();
				echo '<br>WordPress URL: ' . get_site_url();
?>
<br>wp_upload_dir()['basedir'] : <?php $wud= wp_upload_dir(); echo $wud['basedir'] ?>
<br>$_SERVER['DOCUMENT_ROOT'] : <?php echo $_SERVER['DOCUMENT_ROOT'] ?>

<h4>Show Channel Watch when Offline</h4>
<p>Display channel watch interface even if channel is not detected as broadcasting.</p>
<select name="alwaysWatch" id="alwaysWatch">
  <option value="0" <?php echo $options['alwaysWatch']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysWatch']?"selected":""?>>Yes</option>
</select>
<br>Useful when broadcasting with external apps and <a href="https://videowhisper.com/?p=RTMP-Session-Control">rtmp side session control</a> is not available. Also set Session Control: Disabled if that does not work, to show WebRTC player even when not detected as online. 
<br>Watch interface always shows for channels that stream from IP cameras or playlists (not affected by this setting).
<BR>Warning: Enabling this disables event details, that show on channel page while channel is offline. Disabling this, requires broadcast to be started before viewers come to page.
<?php
				break;

			case 'broadcaster':
				$options['parametersBroadcaster'] = htmlentities(stripslashes($options['parametersBroadcaster']));
				$options['layoutCodeBroadcaster'] = htmlentities(stripslashes($options['layoutCodeBroadcaster']));

?>
<h3>Video Broadcasting</h3>
Options for video broadcasting.
<h4>Who can broadcast video channels</h4>
<select name="canBroadcast" id="canBroadcast">
  <option value="members" <?php echo $options['canBroadcast']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $options['canBroadcast']=='list'?"selected":""?>>Members in List</option>
</select>
<br>These users will be able to use broadcasting interface for managing channels (Broadcast Live) and have access to rtmp address keys for using external applications, if enabled.

<h4>Members allowed to broadcast video (comma separated user names, roles, emails, IDs)</h4>
<textarea name="broadcastList" cols="64" rows="4" id="broadcastList"><?php echo $options['broadcastList']?>
</textarea>


<h4>Maximum Number of Broadcasting Channels (per User)</h4>
<input name="maxChannels" type="text" id="maxChannels" size="2" maxlength="4" value="<?php echo $options['maxChannels']?>"/>
<BR>Maximum channels users are allowed to create from frontend if channel posts are enabled.


<h4>Maximum Broadcating Time (0 = unlimited)</h4>
<input name="broadcastTime" type="text" id="broadcastTime" size="7" maxlength="7" value="<?php echo $options['broadcastTime']?>"/> (minutes/period)

<h4>Maximum Channel Watch Time (total cumulated view time, 0 = unlimited)</h4>
<input name="watchTime" type="text" id="watchTime" size="10" maxlength="10" value="<?php echo $options['watchTime']?>"/> (minutes/period)

<h4>Usage Period Reset (0 = never)</h4>
<input name="timeReset" type="text" id="timeReset" size="4" maxlength="4" value="<?php echo $options['timeReset']?>"/> (days)

<h4>Banned Words in Names</h4>
<textarea name="bannedNames" cols="64" rows="4" id="bannedNames"><?php echo $options['bannedNames']?>
</textarea>
<br>Users trying to broadcast channels using these words will be disconnected.


<h4>Redirect broadcaster from own channel page</h4>
<select name="broadcasterRedirect" id="broadcasterRedirect">
  <option value="0" <?php echo $options['broadcasterRedirect']?"":"selected"?>>No</option>
  <option value="dashboard" <?php echo $options['broadcasterRedirect']=='dashboard'?"selected":""?>>Broadcast Live Dashboard</option>
  <option value="broadcast" <?php echo $options['broadcasterRedirect']=='broadcast'?"selected":""?>>Broadcast Channel</option>
</select>
<BR>Redirect broadcaster when accessing own channel page to dashboard or broadcasting interface instead of watch/video interface. Does not redirect when accessing specific interfaces with parameter like hls, mpeg, webrtc.

<?php
				break;

			case 'broadcast-flash':
				$options['parametersBroadcaster'] = htmlentities(stripslashes($options['parametersBroadcaster']));
				$options['layoutCodeBroadcaster'] = htmlentities(stripslashes($options['layoutCodeBroadcaster']));

?>
<h3>Advanced Flash Web Broadcasting Interface</h3>
Settings for the advanced web based broadcasting interface (VideoWhisper Flash based application for PC browser). These settings do not apply for external apps or HTML5 alternatives.
<h4>Default Webcam Resolution</h4>
<select name="camResolution" id="camResolution">
<?php
				foreach (array('160x120','320x240','426x240','480x360', '640x360', '640x480', '720x480', '720x576', '854x480', '1280x720', '1440x1080', '1920x1080') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['camResolution']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>
 <br>Higher resolution will require <a target="_blank" href="https://videochat-scripts.com/recommended-h264-video-bitrate-based-on-resolution/">higher bandwidth</a> to avoid visible blocking and quality loss (ex. 1Mbps required for 640x360) .Webcam capture resolution should be same as video size in player/watch interface.

<h4>Default Webcam Frames Per Second</h4>
<select name="camFPS" id="camFPS">
<?php
				foreach (array('1','8','10','12','15','29','30','60') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['camFPS']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>


<h4>Video Stream Bandwidth</h4>
<input name="camBandwidth" type="text" id="camBandwidth" size="7" maxlength="7" value="<?php echo $options['camBandwidth']?>"/> (bytes/s)
<br>This sets size of video stream (without audio) and therefore the video quality.
<br>Total stream size should be less than maximum broadcaster upload speed (multiply by 8 to get bps, ex. 50000b/s requires connection higher than 400kbps).
<br>Do a speed test from broadcaster computer to a location near your streaming (rtmp) server using a tool like <a href="http://www.speedtest.net" target="_blank">SpeedTest.net</a> . Drag and zoom to a server in contry/state where you host (Ex: central US if you host with VideoWhisper) and select it. The upload speed is the maximum data you'll be able to broadcast.

<h4>Maximum Video Stream Bandwidth (at runtime)</h4>
<input name="camMaxBandwidth" type="text" id="camMaxBandwidth" size="7" maxlength="7" value="<?php echo $options['camMaxBandwidth']?>"/> (bytes/s)

<h4>Video Codec</h4>
<select name="videoCodec" id="videoCodec">
  <option value="H264" <?php echo $options['videoCodec']=='H264'?"selected":""?>>H264</option>
  <option value="H263" <?php echo $options['videoCodec']=='H263'?"selected":""?>>H263</option>
</select>
<BR>H264 provides better quality at same bandwidth but may not be supported by older RTMP server versions (ex. Red5).
<BR>When publishing to iOS with HLS, for lower server load and higher performance, web clients should be configured to broadcast video suitable for target device (H.264 Baseline 3.1) so only audio needs to be encoded.


<h4>H264 Video Codec Profile</h4>
<select name="codecProfile" id="codecProfile">
  <option value="baseline" <?php echo $options['codecProfile']=='baseline'?"selected":""?>>baseline</option>
  <option value="main" <?php echo $options['codecProfile']=='main'?"selected":""?>>main</option>
  <option value="high" <?php echo $options['codecProfile']=='high'?"selected":""?>>high</option>
</select>
<br>Recommended: Baseline

<h4>H264 Video Codec Level</h4>
<select name="codecLevel" id="codecLevel">
<?php
				foreach (array('1', '1b', '1.1', '1.2', '1.3', '2', '2.1', '2.2', '3', '3.1', '3.2', '4', '4.1', '4.2', '5', '5.1') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['codecLevel']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>
<br>Recommended: 3.1

<h4>Sound Codec</h4>
<select name="soundCodec" id="soundCodec">
  <option value="Speex" <?php echo $options['soundCodec']=='Speex'?"selected":""?>>Speex</option>
  <option value="Nellymoser" <?php echo $options['soundCodec']=='Nellymoser'?"selected":""?>>Nellymoser</option>
</select>
<BR>Speex is recommended for voice audio.
<BR>Current web codecs used by Flash plugin are not currently supported by iOS. For delivery to iOS, audio should be transcoded to AAC (HE-AAC or AAC-LC up to 48 kHz, stereo audio).

<h4>Speex Sound Quality</h4>
<select name="soundQuality" id="soundQuality">
<?php
				foreach (array('0', '1','2','3','4','5','6','7','8','9','10') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['soundQuality']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>
 <br>Higher quality requires more <a href="https://videochat-scripts.com/speex-vs-nellymoser-bandwidth/" target="_blank" >bandwidth</a>.
<br>Speex quality 9 requires 34.2kbps and generates 4275 b/s transfer. Quality 10 requires 42.2 kbps.

<h4>Nellymoser Sound Rate</h4>
<select name="micRate" id="micRate">
<?php
				foreach (array('5', '8', '11', '22','44') as $optItm)
				{
?>
  <option value="<?php echo $optItm;?>" <?php echo $options['micRate']==$optItm?"selected":""?>> <?php echo $optItm;?> </option>
  <?php
				}
?>
 </select>
<br>Higher quality requires more <a href="https://videochat-scripts.com/speex-vs-nellymoser-bandwidth/" target="_blank" >bandwidth</a>.
<br>NellyMoser rate 22 requires 44.1kbps and generates  5512b/s transfer. Rate 44 requires 88.2 kbps.


<h4>Disable Embed/Link Codes</h4>
<select name="noEmbeds" id="noEmbeds">
  <option value="0" <?php echo $options['noEmbeds']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['noEmbeds']?"selected":""?>>Yes</option>
</select>
<h4>Show only Video</h4>
<select name="onlyVideo" id="onlyVideo">
  <option value="0" <?php echo $options['onlyVideo']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['onlyVideo']?"selected":""?>>Yes</option>
</select>
<BR>Disable all interactive elements (show title, users list, chat). Only plain video broadcasting is possible. During troubleshooting/development this should be disabled to get additional info from chat box.

<h4>Custom Layout Code for Broadcaster Interface</h4>
<textarea name="layoutCodeBroadcaster" id="layoutCodeBroadcaster" cols="100" rows="4"><?php echo $options['layoutCodeBroadcaster']?></textarea>
<br>Generate by writing and sending "/videowhisper layout" in chat, in broadcasting interface. Contains panel positions, sizes, move and resize toggles. Copy and paste code here.
 Default:<br><textarea readonly cols="100" rows="4"><?php echo $optionsDefault['layoutCodeBroadcaster']?></textarea>
<br>All products load interface (skins, icons, sounds) from files (jpg, png, mp3) in the templates folder. After setting up existing editions, VideoWhisper developers can customize features and options for additional fees, on request, depending on exact requirements.

<h4>Parameters for Broadcaster Interface</h4>
<textarea name="parametersBroadcaster" id="parametersBroadcaster" cols="64" rows="8"><?php echo $options['parametersBroadcaster']?></textarea>
<br>For more details see <a href="https://videowhisper.com/?p=php+live+streaming#integrate">PHP Live Streaming documentation</a>.
<br>When using HTML AJAX chat, use short i.e. externalInterval=11000 to update application chat with messages from external chat.
<br>Ex: &snapshotsTime=60000&room_limit=500&externalInterval=360000&statusInterval=30000
 Default:<br><textarea readonly cols="100" rows="4"><?php echo $optionsDefault['parametersBroadcaster']?></textarea>

<h4>Online Expiration Broadcaster</h4>
<p>How long to consider broadcaster online if no web status update occurs.</p>
<input name="onlineExpiration1" type="text" id="onlineExpiration1" size="5" maxlength="6" value="<?php echo $options['onlineExpiration1']?>"/>s
<br>Should be 10s higher than maximum statusInterval (ms) configured in parameters. A higher statusInterval decreases web server load caused by status updates.
<br>If lower than statusInterval that can cause web server online session sync errors and online users showing offline.

<?php
				break;

				// ! Premium channels
			case 'premium':
?>
<h3>Premium Membership Levels and Channels</h3>
Options for membership levels and premium channels. Premium channels can have higher usage limitations, special settings and features that can be defined here.
Use in combination with <a href='admin.php?page=live-streaming&tab=features'>Channel Features</a> to define specific capabilities depending on role.

<h4>Number of Premium Levels</h4>
<input name="premiumLevelsNumber" type="text" id="premiumLevelsNumber" size="7" maxlength="7" value="<?php echo $options['premiumLevelsNumber']?>"/>
<br>Number of premium membership levels.

<?php

				$premiumLev = unserialize($options['premiumLevels']);

				for ($i=0; $i < $options['premiumLevelsNumber']; $i++)
				{

					$premiumLev[$i]['level'] = $i+1;

					foreach (array('premiumList','canWatchPremium','watchListPremium','pBroadcastTime','pWatchTime','pCamBandwidth','pCamMaxBandwidth', 'pMaxChannels') as $varName)
					{
						if (isset($_POST[$varName . $i])) $premiumLev[$i][$varName] = $_POST[$varName . $i];
						if (!isset($premiumLev[$i][$varName])) $premiumLev[$i][$varName] = $options[$varName]; //default from options
					}
?>

<h3>Premium Level <?php echo ($i+1); ?></h3>

<h4>Members that broadcast premium channels (Premium members: comma separated user names, roles, emails, IDs)</h4>
<textarea name="premiumList<?php echo $i ?>" cols="64" rows="4" id="premiumList<?php echo $i ?>"><?php echo $premiumLev[$i]['premiumList']?>
</textarea>
<br>Highest level match is selected.
<br>Warning: Certain plugins may implement roles that have a different label than role name. Ex: s2member_level1

<h4>Who can watch premium channels</h4>
<select name="canWatchPremium<?php echo $i ?>" id="canWatchPremium<?php echo $i ?>">
  <option value="all" <?php echo $premiumLev[$i]['canWatchPremium']=='all'?"selected":""?>>Anybody</option>
  <option value="members" <?php echo $premiumLev[$i]['canWatchPremium']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $premiumLev[$i]['canWatchPremium']=='list'?"selected":""?>>Members in List</option>
</select>

<h4>Members allowed to watch premium channels (comma separated usernames, roles, emails, IDs)</h4>
<textarea name="watchListPremium<?php echo $i ?>" cols="64" rows="4" id="watchListPremium<?php echo $i ?>"><?php echo $premiumLev[$i]['watchListPremium']?>
</textarea>

<h4>Maximum Number of channels</h4>
<input name="pMaxChannels<?php echo $i ?>" type="text" id="pMaxChannels<?php echo $i ?>" size="7" maxlength="7" value="<?php echo $premiumLev[$i]['pMaxChannels']?>"/> channels
<br>How many channels can user of this level create. Leave blank or 0 to use default (<?php echo $optionsDefault['maxChannels'] ?>).  Only limits creation of new channels: Reducing this does not delete/disable existing channels.

<h4>Maximum Broadcasting Time per Channel</h4>
<input name="pBroadcastTime<?php echo $i ?>" type="text" id="pBroadcastTime<?php echo $i ?>" size="7" maxlength="7" value="<?php echo $premiumLev[$i]['pBroadcastTime']?>"/> (minutes/period)
<br>0 = unlimited

<h4>Maximum Channel Watch Time per Channel</h4>
<input name="pWatchTime<?php echo $i ?>" type="text" id="pWatchTime<?php echo $i ?>" size="10" maxlength="10" value="<?php echo $premiumLev[$i]['pWatchTime']?>"/> (minutes/period)
<br>Total cumulated view time. 0 = unlimited

<h4>Video Stream Bandwidth</h4>
<input name="pCamBandwidth<?php echo $i ?>" type="text" id="pCamBandwidth<?php echo $i ?>" size="7" maxlength="7" value="<?php echo $premiumLev[$i]['pCamBandwidth']?>"/> (bytes/s)
<br>Default stream size for web broadcasting interface.

<h4>Maximum Video Stream Bandwidth (at runtime)</h4>
<input name="pCamMaxBandwidth<?php echo $i ?>" type="text" id="pCamMaxBandwidth<?php echo $i ?>" size="7" maxlength="7" value="<?php echo $premiumLev[$i]['pCamMaxBandwidth']?>"/> (bytes/s)
<br>Maximum stream size for web broadcasting interface.
<?php
				}



				$options['premiumLevels'] = serialize($premiumLev);
				update_option('VWliveStreamingOptions', $options);

				/*
<h4>Show Floating Logo/Watermark</h4>
<select name="pLogo" id="pLogo">
  <option value="0" <?php echo $options['pLogo']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['pLogo']?"selected":""?>>Yes</option>
</select>

<h4>Always do RTMP Streaming (required for Transcoding)</h4>
<p>Enable this if you want all streams to be published to server, no matter if there are registered subscribers or not. Stream on server is required for transcoding to start.</p>
<select name="alwaysRTMP" id="alwaysRTMP">
  <option value="0" <?php echo $options['alwaysRTMP']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['alwaysRTMP']?"selected":""?>>Yes</option>
</select>
*/
?>

<h3>Common Settings</h3>

<h4>Usage Period Reset</h4>
<input name="timeReset" type="text" id="timeReset" size="4" maxlength="4" value="<?php echo $options['timeReset']?>"/> (days)
<br>Same as for regular channels. 0 = never
<?php
				break;
			case 'features':

				//! Channel Features
?>
<h3>Channel Features</h3>
Enable channel features, accessible by owner (broadcaster).
<br>Specify comma separated list of user roles, emails, logins able to setup these features for their channels.
<br>Use All to enable for everybody and None or blank to disable.
<?php

				$features = self::roomFeatures();

				foreach ($features as $key=>$feature) if ($feature['installed'])
					{
						echo '<h3>' . $feature['name'] . '</h3>';
						echo '<textarea name="'.$key.'" cols="64" rows="2" id="'.$key.'">' . trim($options[$key]) . '</textarea>';
						echo '<br>' . $feature['description'];
					}


				break;

			case 'watcher':
				$options['parameters'] = htmlentities(stripslashes($options['parameters']));
				$options['layoutCode'] = htmlentities(stripslashes($options['layoutCode']));
				$options['watchStyle'] = htmlentities(stripslashes($options['watchStyle']));


?>
<h3>Video Watch / Viewer</h3>
Settings for video subscribers that watch the live channels using the advanced watch video & chat or plain video interface (VideoWhisper Flash based applications for PC browsers). These settings do not apply for external apps or HTML5 alternatives (HLS, MPEG-DASH, WebRTC).

<h4>Who can watch video</h4>
<select name="canWatch" id="canWatch">
  <option value="all" <?php echo $options['canWatch']=='all'?"selected":""?>>Anybody</option>
  <option value="members" <?php echo $options['canWatch']=='members'?"selected":""?>>All Members</option>
  <option value="list" <?php echo $options['canWatch']=='list'?"selected":""?>>Members in List</option>
</select>
<h4>Members allowed to watch video (comma separated usernames, roles, IDs)</h4>
<textarea name="watchList" cols="100" rows="4" id="watchList"><?php echo $options['watchList']?>
</textarea>

<h4>Default Viewer Interface</h4>
<select name="viewerInterface" id="viewerInterface">
  <option value="chat" <?php echo $options['viewerInterface']=='chat'?"":"selected"?>>Video + Chat</option>
  <option value="video" <?php echo $options['viewerInterface']=='video'?"selected":""?>>Only Video</option>
</select>
<br>Show interactive watch interface (video, chat, user list, tips) or just video.
<br>For simplified interface in HTML5, disable Transcoding Warnings from <a href="admin.php?page=live-streaming&tab=hls">HTML5 Transcoding</a> tab.

<h4>HTML Chat Visitor Writing</h4>
<select name="htmlchatVisitorWrite" id="htmlchatVisitorWrite">
  <option value="0" <?php echo $options['htmlchatVisitorWrite']?"":"selected"?>>No</option>
  <option value="1" <?php echo $options['htmlchatVisitorWrite']?"selected":""?>>Yes</option>
</select>
<br>Allow visitors to write in HTML chat. Not recommended as that may result in message abuse.

<h3>Flash Video Watch / Viewer</h3>
These settings apply to VideoWhisper Flash player apps, for PC Flash plugin.

<h4>Parameters for Watch and Video Interfaces</h4>
<textarea name="parameters" id="parameters" cols="100" rows="4"><?php echo $options['parameters']?></textarea>
<br>For more details see <a href="https://videowhisper.com/?p=php+live+streaming#integrate">PHP Live Streaming documentation</a>.
<br>When using HTML AJAX chat, use short i.e. externalInterval=17000 to update application chat with messages from external chat.
<br>Ex: &externalInterval=360000&statusInterval=30000
 Default:<br><textarea readonly cols="100" rows="4"><?php echo $optionsDefault['parameters']?></textarea>
<br>Warning: Some parameters are controlled by plugin integration (user and room name, chat and participants panel) and should not be defined here again.

<h4>Online Expiration Viewers</h4>
<p>How long to consider viewer online if no web status update occurs.</p>
<input name="onlineExpiration0" type="text" id="onlineExpiration0" size="5" maxlength="6" value="<?php echo $options['onlineExpiration0']?>"/>s
<br>Should be 10s higher than maximum statusInterval (ms) configured in parameters. A higher statusInterval decreases web server load caused by status updates.

<h4>Custom Layout Code for Watch Interface</h4>
<textarea name="layoutCode" id="layoutCode" cols="100" rows="4"><?php echo $options['layoutCode']?></textarea>
<br>Generate by writing and sending "/videowhisper layout" in chat (contains panel positions, sizes, move and resize toggles). Copy and paste code here.
 Default:<br><textarea readonly cols="100" rows="4"><?php echo $optionsDefault['layoutCode']?></textarea>

<h4>Container Style</h4>
<textarea name="watchStyle" id="watchStyle" cols="100" rows="4"><?php echo $options['watchStyle']?></textarea>
<br>Ex: width:100%; min-height:400px;
 Default:<br><textarea readonly cols="100" rows="4"><?php echo $optionsDefault['watchStyle']?></textarea>

<?php
				break;

			case 'billing':
?>
<h3>Billing Settings</h3>
This solution can use a credits/tokens wallet for tips to broadcasters (and showing balance).

<h4>Active Wallet</h4>
<select name="wallet" id="wallet">
  <option value="MyCred" <?php echo $options['wallet']=='MyCred'?"selected":""?>>MyCred</option>
  <option value="WooWallet" <?php echo $options['wallet']=='WooWallet'?"selected":""?>>WooWallet</option>
</select>
<BR>Select wallet to use with solution.

<h4>Multi Wallet</h4>
<select name="walletMulti" id="walletMulti">
  <option value="0" <?php echo $options['walletMulti']=='0'?"selected":""?>>Disabled</option>
  <option value="1" <?php echo $options['walletMulti']=='1'?"selected":""?>>Show</option>
  <option value="2" <?php echo $options['walletMulti']=='2'?"selected":""?>>Manual</option>
  <option value="3" <?php echo $options['walletMulti']=='3'?"selected":""?>>Auto</option>
</select>
<BR>Show will display balances for available wallets, manual will allow transferring to active wallet, auto will automatically transfer all to active wallet.
<?php

				submit_button();
?>

<h3>TeraWallet (WooWallet WooCommerce Wallet</h3>
<?php
				if (is_plugin_active('woo-wallet/woo-wallet.php'))
				{
					echo 'WooWallet Plugin Detected';

					if ($GLOBALS['woo_wallet'])
					{
						$wooWallet = $GLOBALS['woo_wallet'];

						if ($wooWallet->wallet)
						{
							echo '<br>Testing balance: You have: ' .  $wooWallet->wallet->get_wallet_balance( get_current_user_id() );

?>
	<ul>
		<li><a class="secondary button" href="admin.php?page=woo-wallet">User Credits History & Adjust</a></li>
		<li><a class="secondary button" href="users.php">User List with Balance</a></li>
	</ul>
					<?php
						}
						else echo 'Error: WooWallet->wallet not ready! Make sure <a href="https://woocommerce.com/?aff=18336&cid=1980980" target="_woocommerce">WooCommerce</a> is also installed and active. <a href="plugin-install.php">Plugins > Add New Plugin</a>';

					}else echo 'Error: woo_wallet not found!';


				}
				else echo 'Not detected. Please install and activate <a target="_plugin" href="https://wordpress.org/plugins/woo-wallet/">WooCommerce Wallet</a> from <a href="plugin-install.php">Plugins > Add New</a>!';

				?><br>
WooCommerce Wallet plugin is based on <a href="https://woocommerce.com/?aff=18336&cid=1980980" target="_woocommerce">WooCommerce</a> plugin and allows customers to store their money in a digital wallet. The customers can add money to their wallet using various payment methods set by the admin, available in WooCommerce. The customers can also use the wallet money for purchasing products from the WooCommerce store.
<br> + Configure WooCommerce payment gateways from <a target="_gateways" href="admin.php?page=wc-settings&tab=checkout">WooCommerce > Settings, Payments tab</a>.
<br> + Enable payment gateways from <a target="_gateways" href="admin.php?page=woo-wallet-settings">Woo Wallet Settings</a>.
<br> + Setup a page for users to buy credits with shortcode [woo-wallet]. My Wallet section is also available in WooCommerce My Account page (/my-account).

<h4>WooCommerce Memberships, Subscriptions and Conversion Tools</h4>
<ul>
	<LI><a href="https://woocommerce.com/products/woocommerce-memberships/?aff=18336&cid=1980980">WooCommerce Memberships</a> Setup paid membership as products. Leveraged with Subscriptions plugin allows membership subscriptions.</LI>
	<LI><a href="https://woocommerce.com/products/woocommerce-subscriptions/?aff=18336&cid=1980980">WooCommerce Subscriptions</a> Setup subscription products, content. Leverages Membership plugin to setup membership subscriptions.</LI>
	<LI><a href="https://woocommerce.com/products/follow-up-emails/?aff=18336&cid=1980980">WooCommerce Follow Up</a> Follow Up by emails and twitter automatically, drip campaigns.</LI>
	<LI><a href="https://woocommerce.com/products/woocommerce-bookings/?aff=18336&cid=1980980">WooCommerce Bookings</a> Let your customers book reservations, appointments on their own.</LI>
</ul>


<h3>myCRED Wallet (MyCred)</h3>

<h4>1) myCRED</h4>
<?php
				if (is_plugin_active('mycred/mycred.php')) echo 'MyCred Plugin Detected'; else echo 'Not detected. Please install and activate <a target="_mycred" href="https://wordpress.org/plugins/mycred/">myCRED</a> from <a href="plugin-install.php">Plugins > Add New</a>!';

				if (function_exists( 'mycred_get_users_balance'))
				{
					$balance = mycred_get_users_balance(get_current_user_id());

					echo '<br>Testing MyCred balance: You have ' . $balance  .' '. htmlspecialchars($options['currencyLong']) . '. ';

					if (!strlen($balance)) echo 'Warning: No balance detected! Unless this account is excluded, there should be a MyCred balance. MyCred plugin may not be configured/enabled correctly.';
?>
	<ul>
		<li><a class="secondary button" href="admin.php?page=mycred">Transactions Log</a></li>
		<li><a class="secondary button" href="users.php">User Credits History & Adjust</a></li>
	</ul>
					<?php
				}
?>
<a target="_mycred" href="https://wordpress.org/plugins/mycred/">myCRED</a> is a stand alone adaptive points management system that lets you award / charge your users for interacting with your WordPress powered website. The Buy Content add-on allows you to sell any publicly available post types, including webcam posts created by this plugin. You can select to either charge users to view the content or pay the post's author either the whole sum or a percentage.

	<br> + After installing and enabling myCRED, activate these <a href="admin.php?page=mycred-addons">addons</a>: buyCRED, Sell Content are required and optionally Notifications, Statistics or other addons, as desired for project.

	<br> + Configure in <a href="admin.php?page=mycred-settings ">Core Setting > Format > Decimals</a> at least 2 decimals to record fractional token usage. With 0 decimals, any transactions under 1 token will not be recorded.




<h4>2) myCRED buyCRED Module</h4>
 <?php
				if (class_exists( 'myCRED_buyCRED_Module' ) )
				{
					echo 'Detected';
?>
	<ul>
		<li><a class="secondary button" href="edit.php?post_type=buycred_payment">Pending Payments</a></li>
		<li><a class="secondary button" href="admin.php?page=mycred-purchases-mycred_default">Purchase Log</a> - If you enable BuyCred separate log for purchases.</li>
		<li><a class="secondary button" href="edit-comments.php">Troubleshooting Logs</a> - MyCred logs troubleshooting information as comments.</li>
	</ul>
					<?php
				} else echo 'Not detected. Please install and activate myCRED with <a href="admin.php?page=mycred-addons">buyCRED addon</a>!';
?>

<p> + myCRED <a href="admin.php?page=mycred-addons">buyCRED addon</a> should be enabled and at least 1 <a href="admin.php?page=mycred-gateways">payment gateway</a> configured, for users to be able to buy credits.
<br> + Setup a page for users to buy credits with shortcode <a target="mycred" href="http://codex.mycred.me/shortcodes/mycred_buy_form/">[mycred_buy_form]</a> or use <a href="https://wordpress.org/plugins/paid-membership/">Paid Membership & Content</a> - My Wallet page (that can manage multi wallet MyCred, TeraWallet).
<br> + "Thank You Page", "Cancellation Page" should be configured from <a href="admin.php?page=mycred-settings">buyCred settings</a>.</p>
<p>Troubleshooting: If you experience issues with IPN tests, check recent access logs (recent Visitors from CPanel) to identify exact requests from billing site, right after doing a test.</p>


<h4>3) myCRED Sell Content Module</h4>
 <?php
				if (class_exists( 'myCRED_Sell_Content_Module' ) ) echo 'Detected'; else echo 'Not detected. Please install and activate myCRED with <a href="admin.php?page=mycred-addons">Sell Content addon</a>!';
?>
<p>
myCRED <a href="admin.php?page=mycred-addons">Sell Content addon</a> should be enabled as it's required to enable certain stat shortcodes. Optionally select "<?php echo ucwords($options['custom_post'])?>" - I Manually Select as Post Types you want to sell in <a href="admin.php?page=mycred-settings">Sell Content settings tab</a> so access to webcams can be sold from backend. You can also configure payout to content author from there (Profit Share) and expiration, if necessary.
<?php
				break;

			case 'tips':
				//! Pay Per View Settings


?>
<h3>Tips</h3>
Allows viewers to send tips from watch interface. Requires <a href="admin.php?page=live-streaming&tab=billing">billing setup</a>.

<h4>Enable Tips</h4>
<select name="tips" id="tips">
  <option value="1" <?php echo $options['tips']?"selected":""?>>Yes</option>
  <option value="0" <?php echo $options['tips']?"":"selected"?>>No</option>
</select>
<br>Allows clients to tip performers. Tips feature is implemented both in Flash and HTML chat interface.

<h4>Tip Options</h4>
<?php
				$tipOptions = stripslashes($options['tipOptions']);
				$options['tipOptions'] = htmlentities(stripslashes($options['tipOptions']));
?>
<textarea name="tipOptions" id="tipOptions" cols="100" rows="8"><?php echo $options['tipOptions']?></textarea>
<br>List of tip options as XML. Sounds and images must be deployed in ls/templates/live/tips folder.
 Default:<br><textarea readonly cols="100" rows="4"><?php echo $optionsDefault['tipOptions']?></textarea>

<br>Tips data parsed:
<?php

				if ($tipOptions)
				{
					$p = xml_parser_create();
					xml_parse_into_struct($p, trim($tipOptions), $vals, $index);
					$error = xml_get_error_code($p);
					xml_parser_free($p);

					if ($error) echo '<br>Error:' . xml_error_string($error);

					if (is_array($vals)) foreach ($vals as $tKey=>$tip)
							if ($tip['tag'] == 'TIP')
							{
								echo '<br>- ';
								var_dump($tip['attributes']);
							}

				}
?>

<h4>Broadcaster Earning Ratio</h4>
<input name="tipRatio" type="text" id="tipRatio" size="10" maxlength="16" value="<?php echo $options['tipRatio']?>"/>
<br>Performer receives this ratio from client tip.
<br>Ex: 0.9; Set 0 to disable (performer receives nothing). Set 1 for performer to get full amount paid by client.

<h4>Client Tip Cooldown</h4>
<input name="tipCooldown" type="text" id="tipCooldown" size="10" maxlength="16" value="<?php echo $options['tipCooldown']?>"/>s
<BR>A minimum time client has to wait before sending a new tip. This prevents accidental multi tipping and overspending. Set 0 to disable (not recommended).

<h4>Manage Balance Page</h4>
<select name="balancePage" id="balancePage">
<?php

				$args = array(
					'sort_order' => 'asc',
					'sort_column' => 'post_title',
					'hierarchical' => 1,
					'post_type' => 'page',
					'post_status' => 'publish'
				);
				$sPages = get_pages($args);
				foreach ($sPages as $sPage) echo '<option value="' . $sPage->ID . '" '. ($options['balancePage'] == ($sPage->ID) ?"selected":"") .'>' . $sPage->post_title . '</option>' . "\r\n";
?>
</select>
<br>Page linked from balance section, usually a page where registered users can buy credits.

<?php submit_button(); ?>

<a name="brave"></a>

<h3>Receive Tips and Site Contributions in Crypto</h3>
<a href="https://brave.com/bro242">Brave</a> is a special build of the popular Chrome browser, focused on privacy & speed & ad blocking and already used by millions. Users get airdrops and rewards from ads they are willing to watch and content creators (publishers) like site owners get tips and automated revenue from visitors. This is done in $BAT and can be converted to other cryptocurrencies like Bitcoin or withdrawn in USD, EUR.
<br>Additionally, with Brave you can easily test if certain site features are disabled by privacy features, cookie restrictions or common ad blocking rules. 
	<p>How to receive contributions and tips for your site:
	<br>+ Get the <a href="https://brave.com/bro242">Brave Browser</a>. You will get a browser wallet, airdrops and get to see how tips and contributions work.
	<br>+ Join <a href="https://creators.brave.com/">Brave Creators Publisher Program</a> and add your site(s) as channels. If you have an established site, you may have automated contributions or tips already available from site users that accessed using Brave. Your site(s) will show with a Verified Publisher badge in Brave browser and users know they can send you tips directly.
	<br>+ You can setup and connect an Uphold wallet to receive your earnings and be able to withdraw to bank account or different wallet. You can select to receive your deposits in various currencies and cryptocurrencies (USD, EUR, BAT, BTC, ETH and many more).
</p>

	<?php
				break;

			}

			if (!in_array($active_tab, array('setup', 'live','stats', 'shortcodes', 'support', 'reset', 'troubleshooting', 'billing', 'tips', 'appearance')) ) submit_button(); ?>

</form>
</div>

<style>
.vwInfo
{
background-color: #fffffa;
padding: 8px;
margin: 8px;
border-radius: 4px;	
display:block;
border: #999 1px solid;
box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}
</style>
	
	 <?php
		}

		static function updatePages()
		{

//			if (!$page_id || $page_id == "-1" || !$page_id2 || $page_id2 == "-1")  add_action('wp_loaded', array('VWliveStreaming','updatePages'));


			$options = get_option('VWliveStreamingOptions');

			if ($options['disablePage']=='0' || $options['disablePageC']=='0')
			{
				//create a menu to add pages
				$menu_name = 'VideoWhisper';
				$menu_exists = wp_get_nav_menu_object( $menu_name );

				if (!$menu_exists) $menu_id = wp_create_nav_menu($menu_name);
				else $menu_id = $menu_exists->term_id;
			}



			//if not disabled create
			if ($options['disablePage']=='0')
			{
				global $user_ID;
				$page = array();
				$page['post_type']    = 'page';
				$page['post_content'] = '[videowhisper_channel_manage]';
				$page['post_parent']  = 0;
				$page['post_author']  = $user_ID;
				$page['post_status']  = 'publish';
				$page['post_title']   = 'Broadcast Live';
				$page['comment_status'] = 'closed';

				$page_id = get_option("vwls_page_manage");
				if ($page_id>0) $page['ID'] = $page_id;

				$pageid = wp_insert_post ($page);
				update_option( "vwls_page_manage", $pageid);

				$link = get_permalink( $pageid);

				if ($menu_id && $pageid) wp_update_nav_menu_item($menu_id, 0, array(
							'menu-item-title' =>  'Broadcast Live',
							'menu-item-url' => $link,
							'menu-item-status' => 'publish'));

			}

			if ($options['disablePageC']=='0')
			{
				global $user_ID;
				$page = array();
				$page['post_type']    = 'page';
				$page['post_content'] = '[videowhisper_channels]';
				$page['post_parent']  = 0;
				$page['post_author']  = $user_ID;
				$page['post_status']  = 'publish';
				$page['post_title']   = 'Channels';
				$page['comment_status'] = 'closed';

				$page_id = get_option("vwls_page_channels");
				if ($page_id>0) $page['ID'] = $page_id;

				$pageid = wp_insert_post ($page);
				update_option( "vwls_page_channels", $pageid);

				$link = get_permalink( $pageid);

				if ($menu_id && $pageid) wp_update_nav_menu_item($menu_id, 0, array(
							'menu-item-title' =>  'Channels',
							'menu-item-url' => $link,
							'menu-item-status' => 'publish'));
			}

		}

		static function deletePages()
		{
			$options = get_option('VWliveStreamingOptions');

			if ($options['disablePage'])
			{
				$page_id = get_option("vwls_page_manage");
				if ($page_id > 0)
				{
					wp_delete_post($page_id);
					update_option( "vwls_page_manage", -1);
				}
			}

			if ($options['disablePageC'])
			{
				$page_id = get_option("vwls_page_channels");
				if ($page_id > 0)
				{
					wp_delete_post($page_id);
					update_option( "vwls_page_channels", -1);
				}
			}

		}

	
 }
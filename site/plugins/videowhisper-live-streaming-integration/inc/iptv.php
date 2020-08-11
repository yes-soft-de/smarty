<?php
namespace VideoWhisper\LiveStreaming;

//ini_set('display_errors', 1);
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

trait IPTV {
	//restream sources (IPTV, IPCams)


//IPTV re-streaming: handles ffmpeg restreaming processes

	static function streamProtocols($handler = 'ffmpeg')
	{
		//wowza
		if ($handler == 'wowza') return array('rtsp','udp','rtmp','rtmps','wowz','wowzs');

		//ffmpeg (default)
		return array('rtsp','rtp', 'srtp','udp','tcp', 'rtmp', 'rtmps', 'rtmpt','rtmpts','rtmpe','rtmpte', 'mmsh', 'mmst', 'http', 'https', 'tls');
	}

	static function streamStart($postID, $options = null)
	{
		if (!$postID) return;
		if (!$options) $options = self::getOptions();

		$address = get_post_meta( $postID, 'vw_ipCamera',true );
		if (!$address) return;

		list($addressProtocol) = explode(':',strtolower($address));
		if (!in_array($addressProtocol, self::streamProtocols() )) return;

		$post = get_post($postID);
		$stream = $post->post_title;


		//publishing rtmp keys on this server
		if ($options['externalKeysTranscoder'])
		{
			$userID = $post->post_author;

			$key = md5('vw' . $options['webKey'] . $userID . $postID);
			$rtmpAddress = $options['rtmp_server'] . '?'. urlencode($stream_hls) .'&'. urlencode($stream) .'&'. $key . '&1&' . $userID . '&videowhisper';
		}
		else
		{
			$rtmpAddress = $options['rtmp_server'];
		}

		//paths
		$uploadsPath = $options['uploadsPath'];
		if (!file_exists($uploadsPath)) mkdir($uploadsPath);
		
		$roomPath = $uploadsPath . "/$stream/";
		if (!file_exists($roomPath)) mkdir($roomPath);

		$log_file =  $roomPath . "iptvStart.log";

		$cmd = $options['ffmpegPath'] .' ' . " -threads 1 -codec copy -bsf:v h264_mp4toannexb -bsf:a aac_adtstoasc -f flv \"" .
			$rtmpAddress . "/". $stream . "\" -re -stream_loop -1 -i \"" . $address . "\" >&$log_file & ";

//-codec copy -bsf:v h264_mp4toannexb -bsf:a aac_adtstoasc
// /usr/local/bin/ffmpeg -threads 1 -codec copy -bsf:v h264_mp4toannexb -bsf:a aac_adtstoasc -f flv "rtmp://videonow.live/videonow-xarchive?&Santorini-Parkour&1731175304590cf984d3b6f2fbcde576&1&1&videowhisper/Santorini-Parkour" -re -stream_loop -1 -i "https://bitdash-a.akamaihd.net/content/MI201109210084_1/m3u8s/f08e80da-bf1d-4e3d-8899-f0f6155f6efa.m3u8"

		//log and executed cmd
		exec("echo '" . date(DATE_RFC2822) . ":: $cmd' >> $log_file.cmd", $output, $returnvalue);
		
		$pid = exec($cmd, $output, $returnvalue);

		$lastLog = $options['uploadsPath'] . '/lastLog-iptvStart.txt';
		self::varSave($lastLog, [ 'pid' => $pid, 'file'=>$log_file, 'cmd' => $cmd, 'return' => $returnvalue, 'output0' => $output[0], 'time' =>time()] );

		update_post_meta($postID, 'stream-protocol', 'rtmp'); //optimize?				
		update_post_meta($postID, 'stream-mode', 'iptv');

		update_post_meta($postID, 'iptvPid', $pid);
		update_post_meta($postID, 'iptvStart', time());
		update_post_meta($postID, 'iptvLive', 1);
		
		
		//update active streams list
		$iptvActive = $options['uploadsPath'] . '/iptvActive.txt';
		$streamsActive = self::varLoad($iptvActive);
		if (!is_array($streamsActive)) $streamsActive = array();
		$streamsActive[$postID] = $pid;
		self::varSave($iptvActive, $streamsActive);
	}

	static function streamStop($postID, $options = null)
	{
		if (!$postID) return;
		if (!$options) $options = self::getOptions();
		
		$pid = get_post_meta( $postID, 'iptvPid', true );
		if (!$pid) return;
		
		$cmd = 'kill -KILL ' . $pid;
		
		update_post_meta($postID, 'iptvStop', time());
		update_post_meta($postID, 'iptvLive', 0);
		update_post_meta($postID, 'iptvPid', 0); //no longer needed to run	
		
		
		//update active streams list
		$iptvActive = $options['uploadsPath'] . '/iptvActive.txt';
		$streamsActive = self::varLoad($iptvActive);
		if (!is_array($streamsActive)) $streamsActive = array();
		if (array_key_exists($postID, $streamsActive)) 
		{
			unset($streamsActive[$postID]);
			self::varSave($iptvActive, $streamsActive);	
		}
	}

	static function streamRunning($postID, $options = null)
	{
		//check if running
		if (!$postID) return;
		if (!$options) $options = self::getOptions();
		
		$pid = get_post_meta( $postID, 'iptvPid', true );
		$live = get_post_meta( $postID, 'iptvLive', true );
		
		if (!$pid && $live) 
		{
			update_post_meta($postID, 'iptvLive', 0);
			return;
		}
		
		if ($pid) 
		if (file_exists( "/proc/$pid" ))
		{
			update_post_meta($postID, 'iptvRunning', time());
			return 1;
		}
		else {
			update_post_meta($postID, 'iptvLive', 0);
			return 0;
		}
	}
	
	static function streamMonitor($postID, $options = null)
	{
		//restart if process died
		$pid = get_post_meta( $postID, 'iptvPid', true );		
		if (!$pid) return;
		
		if (!self::streamRunning($postID, $options)) self::streamStart($postID, $options);
	}


		static function restreamPause($postID, $stream, $options)
		{

			if (!self::timeTo($stream . '/restreamPause' . $postID, 3, $options)) return; //already checked recently (prevent several calls on same request)

			if ($options['restreamPause']) $paused = 1;
			else $paused = 0;

			//updates restream Status
			$activeTime = time() - $options['restreamTimeout']-1;


			if ($paused && $options['restreamAccessedUser'])
			{
				//access time
				$accessedUser = get_post_meta($postID, 'accessedUser', true);
				if ($accessedUser > $activeTime) $paused = 0;
			}

			if ($paused && $options['restreamAccessed'])
			{

				$accessed = get_post_meta($postID, 'accessed', true);
				if ($accesse> $activeTime) $paused = 0;
			}

			if ($paused && $options['restreamActiveOwner'])
			{
				//author site access time
				$userID = get_post_field( 'post_author', $postID );
				$accessTime = get_user_meta($userID, 'accessTime', true);

				if ($accessTime > $activeTime) $paused = 0;
			}
			
		
			if ($paused && $options['restreamActiveUser'])
			{	
				$userAccessTime = intval(get_option('userAccessTime', 0));
				if ($userAccessTime > $activeTime) $paused = 0;
			}


			$streamMode = get_post_meta( $postID, 'stream-mode', true );

			if ($streamMode == 'iptv')
			{
				//handle iptv
						$running = self::streamRunning($postID, $options);
						
						if ($paused && $running) self::streamStop($postID, $options);
						
						if (!$paused) if (!$running) self::streamStart($postID, $options);

			}
			else //wowza restream handling
			{
				$streamFile = $options['streamsPath'] .'/'. $stream;
	
				if ($paused)
				{
					//disable
					if (file_exists($streamFile))
					{
						unlink($streamFile);
					}
	
				}
				else
				{
					//enable
					if (!file_exists($streamFile))
					{
						$vw_ipCamera = get_post_meta( $postID, 'vw_ipCamera', true );
	
						$myfile = fopen($streamFile, "w");
						if ($myfile)
						{
							fwrite($myfile, $vw_ipCamera);
							fclose($myfile);
						}
					}
	
				}
			}

			update_post_meta( $postID, 'restreamPaused', $paused );
		}



//IPTV/IPCam Setup UX

	function videowhisper_stream_setup($atts)
		{
			//Shortcode: Setup IPTV / IPCamera ajax
			
			$options = self::getOptions();

			$atts = shortcode_atts(
				array(
					'include_css' => '1',
					'channel_id' => '-1',
					'handler' => 'wowza', // iptv/wowza
					'id' => ''
				),
				$atts, 'videowhisper_stream_setup');
			$id = $atts['id'];
			if (!$id) $id = uniqid();
			
			$handler = $atts['handler'];
			if ($_GET['h']) $handler = sanitize_file_name( $_GET['h']);

			$postID = intval($atts['channel_id']);
			if ($postID<0) $postID = 0;

			$current_user = wp_get_current_user();

			$address = 'rtsp://[user:password]@public-IP-or-Domain[:port]/[stream-path]';
			$channelTitle = 'New';

			$addButton = 'Setup Stream';

			if ($postID && $current_user)
			{

				$channel = get_post( $postID);

				if ($channel)
					if ($channel->post_author == $current_user->ID)
					{
						$address = get_post_meta( $postID, 'vw_ipCamera',true );
						$channelTitle = $channel->post_title;
						$addButton = 'Update Stream Address';

					}
				else
				{
					$postID = 0;
					$htmlCode .= 'Only owner can edit existing channel address.';
				}
			}

			$streamInfoCode = '<H4>IPTV / IP Camera - Stream Access Requirements</H4>
<UL>
<LI>You will need the stream address of your IP camera or IPTV channel. Insert address exactly as it works in <a target="_blank" href="http://www.videolan.org/vlc/index.html">VLC</a> (File > Open Network) or other player. Test before submitting. </LI>
<LI>Address should start with one of these supported protocols: rtsp://, rtmp://, udp://, rtmps://, wowz://, wowzs:// .</LI>
<LI>For increased playback support, H264 video with AAC audio encoded streams should be configured if possible, from IP Camera / Streaming source settings. </LI>
<LI>Depending on source encoding, streaming may require transcoding to play in different players. Without transcoding, some streams may play in Flash players but not as HTML5 HLS or MPEG-DASH or may have interruptions or missing sound. <a href="https://videowhisper.com/tickets_submit.php">Contact Technical Support</a> and provide exact stream address for evaluation.</LI>
<LI>For IP cameras, you can find RTSP address in its documentation: ask the camera provider or <a target="_blank" rel="nofollow" href="http://www.soleratec.com/support/rtsp/rtsp_listing">find it online</a>.</LI>
<LI>Username and password of IP camera / stream needs to be specified if needed to access that stream.</LI>
<LI>Port needs to be specified if different from default for that protocol. Non standard ports (other than than 554 RTSP, 1935 RTMP) may be rejected by firewall. Contact site/server administrator if you need to use special.</LI>
<LI>If device/stream does not have a public address, your local network administrator will need to <a target="_blank" rel="nofollow" href="https://portforward.com/">Forward Camera Port trough Router</a>.</LI>
<LI>If your network is not publicly accessible (your ISP did not allocate a static public IP), your local network administrator may need to setup <a target="_blank" rel="nofollow"  href="http://www.howtogeek.com/66438/how-to-easily-access-your-home-network-from-anywhere-with-ddns/">Dynamic DNS</a> for external access.</LI>
</UL>';

			$imgCode = '';
			if ($postID)
			{
				$snapshotPath = get_post_meta($postID, 'vw_lastSnapshot', true);
				if ($snapshotPath) $imgCode = '<p>Last Snapshot:<br><IMG class="ui rounded image big" SRC="' . self::path2url($snapshotPath) . '"></p>';
			}


			self::enqueueUI();

			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwls_stream_setup&channel=' . $postID .'&h='.$handler .'&id='.$id;

			$htmlCode = <<<HTMLCODE
<H4>Setup IPTV / IP Camera - Existing Stream : $channelTitle</H4>
<!-- $handler -->			
<div id="videowhisperResponse$id">
Stream Address <input name="address" type="text" id="address" value="$address" size="80" maxlength="250"/>
<BR><input class="ui button" type="submit" name="button" id="button" value="$addButton" onClick="loadResponse$id('<div class=\'ui active inline text large loader\'>Trying to connect. Please wait...</div>', '$ajaxurl&address=' + escape(document.getElementById('address').value))"/>
$imgCode
</div>

<script type="text/javascript">
var \$j = jQuery.noConflict();
var loader$id;

	function loadResponse$id(message, request_url){

	if (message)
	if (message.length > 0)
	{
	  \$j("#videowhisperResponse$id").html(message);
	}
		if (loader$id) loader$id.abort();

		loader$id = \$j.ajax({
			url: request_url,
			success: function(data) {
				\$j("#videowhisperResponse$id").html(data);
			}
		});
	}
</script>
$streamInfoCode
HTMLCODE;

			if ($atts['include_css']) $htmlCode .= html_entity_decode(stripslashes($options['customCSS']));

			return $htmlCode;
		}



	//! AJAX IPTV / IP Camera - Stream Setup
		function vwls_stream_setup()
		{
			$options = get_option('VWliveStreamingOptions');

			ob_clean();


			function respond($msg, $request ='')
			{
				echo $msg;
				die;
			}

			$id = sanitize_text_field($_GET['id']);
			$postID = intval($_GET['channel']);
			$handler = sanitize_text_field($_GET['h']);

			$devMode = 0;
			if (!is_user_logged_in()) $postID = 0;
			else
			{
				$current_user = wp_get_current_user();
				if (user_can( $current_user, 'administrator' )) 
				{
					$devMode = 1;
				}
			}


			$ajaxurl = admin_url() . 'admin-ajax.php?action=vwls_stream_setup&channel=' . $postID .'&h=' . $handler . '&id='.$id;

			$address = sanitize_text_field($_GET['address']);

			$label = sanitize_file_name($_GET['label']);
			$username = sanitize_file_name($_GET['username']);
			$email = sanitize_email($_GET['email']);

			if ($postID) //editing existing channel
				{
				$current_user = wp_get_current_user();
				$channel = get_post( $postID);

				if (!$channel) $error .= ($error?'<br>':'') . 'Channel not found!';
				elseif ($channel->post_author != $current_user->ID)
				{
					$postID = 0;
					$error .= ($error?'<br>':'') . 'Only owner can edit existing channel address.';
				}
			}

			if (!$label) //first step: check address
				{

				if (!$address)
				{
					$error = 'A stream address is required';

				}
				else
				{

					//protocol
					list($addressProtocol) = explode(':',strtolower($address));
					if (!in_array($addressProtocol, array('rtsp','udp','rtmp','rtmps','wowz','wowzs', 'http', 'https')))
					{
						$error .= ($error?'<br>':'') . "Address protocol not supported ($addressProtocol). Address should use one of these protocols: rtsp://, udp://, rtmp://, rtmps://, wowz://, wowzs://";

					}

					//demo
					if (strstr($address,'[') || strstr($address,'stream-path'))
					{
						$error .= ($error?'<br>':'') . 'Address should not contain special characters or sample path provided as demo. You need your own address to test. Insert address exactly as it works in <a target="_blank" rel="nofollow" href="http://www.videolan.org/vlc/index.html">VLC</a> or other player.';
					}

					//local
					if (strstr($address,'192.168.') || strstr($address, 'localhost'))
					{
						$error .= ($error?'<br>':'') . 'Address host should point to a publicly accessible device (a <a target="_blank"  href="https://www.iplocation.net/public-vs-private-ip-address">public IP</a> or domain). When address points to a local (intranet) address (192.168..) or localhost, stream is not accessible from internet.';

					}


				}


				$retryCode = <<<HTMLCODE
<BR>Stream Address <input name="address" type="text" id="address" value="$address" size="80" maxlength="250"/>
<BR><input type="submit" name="button" id="button" value="Try Stream" onClick="loadResponse$id('<div class=\'ui active inline text large loader\'>Trying to connect. Please wait...</div>', '$ajaxurl&address=' + escape(document.getElementById('address').value))"/>
$streamCode
HTMLCODE;

				if ($error) respond($error . $retryCode);

				//try to retrieve a snapshot

				$dir = $options['uploadsPath'];
				if (!file_exists($dir)) mkdir($dir);
				$dir .= "/_setup";
				if (!file_exists($dir)) mkdir($dir);

				if (!file_exists($dir))
				{
					$error = error_get_last();
					respond('Error - Folder does not exist and could not be created: ' . $dir . ' - '.  $error['message']);
				}

				$filename = "$dir/$id.jpg";
				$log_file = $filename . '.txt';
				$log_file_cmd = $filename . '-cmd.txt';


				$cmdP = '';
				$cmdT = '';

				//movie streams start with blank screens
				if (strstr($address, '.mp4') || strstr($address, '.mov') || strstr($address, 'mp4:')) $cmdT = '-ss 00:00:02';

				if ($addressProtocol == 'rtsp') $cmdP = '-rtsp_transport tcp'; //use tcp for rtsp

				$cmd = $options['ffmpegSnapshotTimeout'] . ' ' . $options['ffmpegPath'] ." -y -frames 1 \"$filename\" $cmdP $cmdT -i \"" . $address . "\" >&$log_file  ";

				//echo $cmd;
				exec($cmd, $output, $returnvalue);
				exec("echo '$cmd' >> $log_file_cmd", $output, $returnvalue);

				$lastLog = $options['uploadsPath'] . '/lastLog-streamSetup.txt';
				self::varSave($lastLog, [ 'file'=>$log_file, 'cmd' => $cmd, 'return' => $returnvalue, 'output0' => $output[0], 'time' =>time()] );

				$devInfo ='';
				if ($devMode) $devInfo = "[Admin Dev Info: $cmd]";
				
				//failed
				if (!file_exists($filename)) respond('Snapshot could not be retrieved from '.$addressProtocol.': ' . $address .$devInfo. $retryCode);



				$previewSrc = self::path2url($filename);
				$imgCode = '<IMG class="ui rounded image big" SRC="'.$previewSrc.'">';

				$infoCode = 'IP Camera/Stream is accessible: you can setup this channel stream.';

				if (!is_user_logged_in())
					if ($options['ipcamera_registration']) //inline registration
						{
						$regCode .= <<<HTMLCODE
<BR>Also provide an username and email to quickly setup an account for managing your camera securely.
<BR>Username<input name="username" type="text" id="username" value="" size="32" maxlength="64"/>
<BR>Email<input name="email" type="text" id="email" value="" size="64" maxlength="64"/>
HTMLCODE;
						$extraGET = "+ '&username=' + escape(document.getElementById('username').value)+ '&email=' + escape(document.getElementById('email').value)";

					}
				else
				{
					$addCode .= $infoCode;
					$addCode .= self::loginRequiredWarning();
					respond($addCode . $imgCode );
				}

				$addButton = 'Add Stream Channel';


				$channelTitle ='';
				if ($channel)
				{
					$channelTitle = $channel->post_title;
					
					$warnSuffix = '';
					if ($handler == 'wowza')
					{
					//re-stream channel ends in .stream
					$suffix = '.stream';
					$suffixLen = strlen($suffix);
					if (substr($channelTitle,-$suffixLen, $suffixLen) != $suffix) $channelTitle .= $suffix;
					$warnSuffix = '<BR>Stream channels end in ".stream" (will be added if missing) for this type of re-streaming.';
					}

					$addButton = 'Update Channel';
				}


				$addCode .= <<<HTMLCODE
$infoCode
<BR>Channel Label
<input name="label" type="text" id="label" value="$channelTitle" size="32" maxlength="64"/>
$warnSuffix
$regCode
<input name="address" type="hidden" id="address" value="$address"/>
<BR><input class="ui button" type="submit" name="button" id="button" value="$addButton" onClick="loadResponse$id('<div class=\'ui active inline text large loader\'>Trying to connect. Please wait...</div>', '$ajaxurl&address=' + escape(document.getElementById('address').value) + '&label=' + escape(document.getElementById('label').value) $extraGET )"/>
<BR><BR>
HTMLCODE;
				respond($addCode . $imgCode );
			}
			else //second step : add camera
				{


				if (is_user_logged_in()) //logged in
					{
					$current_user = wp_get_current_user();
					$userID = $current_user->ID;
				}
				else //register new user
					{
					if (!$email || !$username) respond('You must provide a valid username and email to register and setup your camera.');

					$userID = register_new_user($username, $password);
					if( is_wp_error( $userID ) ) respond('Registration failed:' . $userID->get_error_message());
				}


				if ($handler == 'wowza')
				{
				//re-stream channel ends in .stream
				$suffix = '.stream';
				$suffixLen = strlen($suffix);
				if (substr($label,-$suffixLen, $suffixLen) != $suffix) $label .= $suffix;
				}
				


				//setup new channel post
				$post = array(
					'post_name'      => $label,
					'post_title'     => $label,
					'post_author'    => $userID,
					'post_type'      => $options['custom_post'],
					'post_status'    => 'publish',
				);
				
				global $wpdb;
				$existID = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE `post_title` = '$label' AND `post_type`='".$options['custom_post']."' LIMIT 0,1" ); //same name, diff than postID

				if ($existID) if ($postID != $existID) respond('A different channel with this name already exists:' . $label);

				if ($postID)
				{
					$post['ID'] = $postID;
					wp_update_post($post);
				}
				else 
				{
					$postID = wp_insert_post($post);
				}

				//add to channels table if missing
				$table_channels = $wpdb->prefix . "vw_lsrooms";

				$sql = "SELECT * FROM $table_channels where name='$label'";
				$channel = $wpdb->get_row($sql);

				if ($channel) if ($channel->owner != $userID) respond('Channel name already used by different owner:' . $label);

				$ztime = time();
				$username = $current_user->user_login;
				if (!$channel)
				{
					$sql="INSERT INTO `$table_channels` ( `owner`, `name`, `sdate`, `edate`, `rdate`,`status`, `type`) VALUES ('$userID', '$label', $ztime, $ztime, $ztime, 0, $rtype)";
					$htmlCode .= 'Channel was created: ' . $label;
				}

				//copy snapshot
				$dir = $options['uploadsPath'];
				$dir .= "/_setup";
				$filename = "$dir/$id.jpg";
				$timestamp = filemtime($filename);

				$dir = $options['uploadsPath'];
				$dir .= "/$label";
				if (!file_exists($dir)) mkdir($dir);

				$dir .= "/snapshots";
				if (!file_exists($dir)) mkdir($dir);

				$snapshot = "$dir/$timestamp.jpg";
				copy($filename, $snapshot);
				update_post_meta($postID, 'vw_lastSnapshot', $snapshot);

				$url = get_permalink($postID);

				//setup/run stream
				$streamReady = false;
				
				if ($handler == 'wowza')
				{
				
					if (file_exists($options['streamsPath']))
					{

					if ($address) if (!strstr($label,'.stream'))
						{
							$htmlCode .= "<BR>Channel name must end in .stream when re-streaming!";
							$address = '';
						}

					$file = $options['streamsPath'] . '/' . $label;

					if ($address)
					{

						$myfile = fopen($file, "w");
						if ($myfile)
						{
							fwrite($myfile, $address);
							fclose($myfile);
							$htmlCode .= '<BR>Stream file setup:<br>' . $label . ' = ' . $address;
						}
						else
						{
							$htmlCode .= '<BR>Could not write file: '. $file;
							$address = '';
						}

					}


					if (in_array($sourceProtocol, array('http', 'https'))) update_post_meta($postID, 'stream-hls', $label); //http restreaming as is

					list($addressProtocol) = explode(':', $address);
					update_post_meta($postID, 'stream-protocol', $addressProtocol); //source required for transcoding
					update_post_meta($postID, 'stream-mode', 'stream');
					
					$streamReady = 1;


				}
				else
				{
					$htmlCode .= '<BR>Stream file could not be setup. Streams folder not found: '. $options['streamsPath'];
				}
			}
			else
			{
				//iptv: ffmpeg
				
				self::streamStart($postID, $options);
				
				$htmlCode .= '<BR>Stream started.';

				$streamReady = 1;

			}

			if ($streamReady)
			{

					update_post_meta($postID, 'vw_ipCamera', $address);
					update_post_meta($postID, 'stream-type', 'restream');
					
					update_post_meta($postID, 'edate', time()); //detected and setup: ready to go live
					self::streamSnapshot($label, true, $postID); //update channel snapshot
					
					
							$htmlCode .= '<br><a href="' . get_permalink( $postID). '" class="ui button">Watch Channel</a>';
			}
			
				respond($htmlCode );

			}
			//output end
			die;
		}


}
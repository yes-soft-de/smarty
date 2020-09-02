<?php
/*
Credits: http://www.whiletrue.it/
*/


if ( !defined( 'ABSPATH' ) ) exit;

        
        
/**
 * ReallySimpleTwitterWidget Class
 */
class ReallySimpleTwitterWidget extends WP_Widget {
	private /** @type {string} */ $languagePath;

    /** constructor */
    function __construct() {
		$this->languagePath = basename(dirname(__FILE__)).'/languages';
        load_plugin_textdomain('vibe', 'false', $this->languagePath);

		$this->options = array(
			array(
				'name'	=> 'title',
				'label'	=> __( 'Title', 'vibe' ),
				'type'	=> 'text'
			),
                        array(
				'name'	=> 'style',
				'label'	=> __( 'Select Twitter Style', 'vibe' ),
				'type'	=> 'select',
                                'options' => array('default','horizontal'),
			),
			array(
				'name'	=> 'link_title',
				'label'	=> __( 'Link above Title with Twitter user', 'vibe' ),
				'type'	=> 'checkbox'
			), 
			array(
				'name'	=> 'consumer_key',	
                                'label'	=> __('Consumer Key','vibe').'<small><br />'.__('Create your Twitter Application', 'vibe' ).' <a href="https://dev.twitter.com/apps" target="_blank">'.__('here', 'vibe' ).'</a></small>',
				'type'	=> 'text',	
                                'default' => ''			
                            ),
			array(
				'name'	=> 'consumer_secret',	
                                'label'	=> __('Consumer Secret','vibe'),
				'type'	=> 'text',	
                                'default' => ''			
                            ),
			array(
				'name'	=> 'access_token',	
                                'label'	=> __('Access Token','vibe'),
				'type'	=> 'text',	
                                'default' => ''			
                            ),
			array(
				'name'	=> 'access_token_secret',	
                                'label'	=> __('Access Token Secret','vibe'),
				'type'	=> 'text',	
                                'default' => ''			
                            ),
			array(
				'name'	=> 'username',
				'label'	=> __( 'Twitter Username', 'vibe' ),
				'type'	=> 'text'
			),
			array(
				'name'	=> 'num',
				'label'	=> __( 'Show # of Tweets', 'vibe' ),
				'type'	=> 'text'
			),
			array(
				'name'	=> 'update',
				'label'	=> __( 'Show timestamps', 'vibe' ),
				'type'	=> 'checkbox'
			),
			array(
				'name'	=> 'hyperlinks',
				'label'	=> __( 'Find and show hyperlinks', 'vibe' ),
				'type'	=> 'checkbox'
			),
			array(
				'name'	=> 'twitter_users',
				'label'	=> __( 'Find Replies in Tweets', 'vibe' ),
				'type'	=> 'checkbox'
			),
			array(
				'name'	=> 'link_target_blank',
				'label'	=> __( 'Create links on new window / tab', 'vibe' ),
				'type'	=> 'checkbox'
			)
		);
		$widget_ops = array( 'classname' => 'vibe-twitter-widget', 'description' => __( 'Vibe Twitter Widget', 'vibe' ) );
		parent::__construct( 'vibe_twitter_widget', __( 'Vibe Twitter Widget','vibe' ), $widget_ops);
    }

    
protected function really_simple_twitter_messages($options) {
	
		// CHECK OPTIONS

		if ($options['username'] == '') {
			return __('Twitter username is not configured','vibe');
		} 
		if (!is_numeric($options['num']) or $options['num']<=0) {
			return __('Number of tweets is not valid','vibe');
		}
		if ($options['consumer_key'] == '' or $options['consumer_secret'] == '' or $options['access_token'] == '' or $options['access_token_secret'] == '') {
			return __('Twitter Authentication data is incomplete','vibe');
		} 

		if (!class_exists('Codebird')) {
			require ('twitter_class.php');
		}
		Codebird::setConsumerKey($options['consumer_key'], $options['consumer_secret']); 
		$this->cb = Codebird::getInstance();	
		$this->cb->setToken($options['access_token'], $options['access_token_secret']);
		
		// From Codebird documentation: For API methods returning multiple data (like statuses/home_timeline), you should cast the reply to array
		$this->cb->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);

		// SET THE NUMBER OF ITEMS TO RETRIEVE - IF "SKIP TEXT" IS ACTIVE, GET MORE ITEMS
		$max_items_to_retrieve = $options['num'];
		if ((isset($options['skip_text']) && $options['skip_text']!='') || (isset($options['skip_replies']) && $options['skip_replies']) || isset($options['skip_retweets'])) {
			$max_items_to_retrieve *= 3;
		}
		// TWITTER API GIVES MAX 200 TWEETS PER REQUEST
		if ($max_items_to_retrieve>200) {
			$max_items_to_retrieve = 200;
		}
		
		if(isset($options['skip_text']))
			$transient_name = 'twitter_data_'.$options['username'].$options['skip_text'].'_'.$options['num'];
		else
			$transient_name = 'twitter_data_'.$options['username'].'_'.$options['num'];

		if (isset($options['erase_cached_data']) && $options['erase_cached_data']) {
			$this->debug($options, '<!-- '.__('Fetching data from Twitter','vibe').'... -->');
			$this->debug($options, '<!-- '.__('Erase cached data option enabled','vibe').'... -->');
			delete_transient($transient_name);
			delete_transient($transient_name.'_status');
			delete_option($transient_name.'_valid');
			
			try {
				$twitter_data =  $this->cb->statuses_userTimeline(array(
							'screen_name'=>$options['username'], 
							'count'=>$max_items_to_retrieve,
							'exclude_replies'=>$options['skip_replies'],
							'include_rts'=>(!$options['skip_retweets'])
					));
			} catch (Exception $e) { return __('Error retrieving tweets','vibe'); }

			if (isset($twitter_data['errors'])) {
				$this->debug($options, __('Twitter data error:','vibe').' '.$twitter_data['errors'][0]['message'].'<br />');
			}
		} else {
	
			// USE TRANSIENT DATA, TO MINIMIZE REQUESTS TO THE TWITTER FEED
	
			$timeout = 10 * 60; //10m
			$error_timeout = 5 * 60; //5m
    
			$twitter_data = get_transient($transient_name);
			$twitter_status = get_transient($transient_name.'_status');
    
			// Twitter Status
			if(!$twitter_status || !$twitter_data) {
				try {
					$twitter_status = $this->cb->application_rateLimitStatus();
					set_transient($transient_name."_status", $twitter_status, $error_timeout);
				} catch (Exception $e) { 
					$this->debug($options, __('Error retrieving twitter rate limit','vibe').'<br />');
				}
			}
    
			// Tweets

			if (empty($twitter_data) or count($twitter_data)<1 or isset($twitter_data['errors'])) {
				$calls_limit   = (int)$twitter_status['resources']['statuses']['/statuses/user_timeline']['limit'];
				$remaining     = (int)$twitter_status['resources']['statuses']['/statuses/user_timeline']['remaining'];
				$reset_seconds = (int)$twitter_status['resources']['statuses']['/statuses/user_timeline']['reset']-time();

				$this->debug($options, '<!-- '.__('Fetching data from Twitter','vibe').'... -->');
				$this->debug($options, '<!-- '.__('Requested items','vibe').' : '.$max_items_to_retrieve.' -->');
				$this->debug($options, '<!-- '.__('API calls left','vibe').' : '.$remaining.' of '.$calls_limit.' -->');
				$this->debug($options, '<!-- '.__('Seconds until reset','vibe').' : '.$reset_seconds.' -->');

				if($remaining <= 7 and $reset_seconds >0) {
				    $timeout       = $reset_seconds;
				    $error_timeout = $reset_seconds;
				}

				try {
					$twitter_data =  $this->cb->statuses_userTimeline(array(
							'screen_name'=>$options['username'], 
							'count'=>$max_items_to_retrieve, 
							'exclude_replies'=>1,
							'include_rts'=>0
						));
				} catch (Exception $e) { return __('Error retrieving tweets','vibe'); }

				if(!isset($twitter_data['errors']) and (count($twitter_data) >= 1) ) {
				    set_transient($transient_name, $twitter_data, $timeout);
				    update_option($transient_name."_valid", $twitter_data);
				} else {
				    set_transient($transient_name, $twitter_data, $error_timeout);	// Wait 5 minutes before retry
					if (isset($twitter_data['errors'])) {
						$this->debug($options, __('Twitter data error:','vibe').' '.$twitter_data['errors'][0]['message'].'<br />');
					}
				}
			} else {
				$this->debug($options, '<!-- '.__('Using cached Twitter data','vibe').'... -->');

				if(isset($twitter_data['errors'])) {
					$this->debug($options, __('Twitter cache error:','vibe').' '.$twitter_data['errors'][0]['message'].'<br />');
				}
			}
    
			if (empty($twitter_data) and false === ($twitter_data = get_option($transient_name."_valid"))) {
			    return __('No public tweets','vibe');
			}

			if (isset($twitter_data['errors'])) {
				// STORE ERROR FOR DISPLAY
				$twitter_error = $twitter_data['errors'];
			    if(false === ($twitter_data = get_option($transient_name."_valid"))) {
					$debug = ($options['debug']) ? '<br /><i>Debug info:</i> ['.$twitter_error[0]['code'].'] '.$twitter_error[0]['message'].' - username: "'.$options['username'].'"' : '';
				    return __('Unable to get tweets','vibe').$debug;
				}
			}
		}


		if (empty($twitter_data) or count($twitter_data)<1) {
		    return __('No public tweets','vibe');
		}
		$link_target = ($options['link_target_blank']) ? ' target="_blank" ' : '';
		
                
                        $image= '<div class="author"><i class="icon-twitter"></i>';
                        
             if($options['style'] == 'horizontal')
                $out = '<div class="tweets"><div class="tweet_icon">'.$image.'</div><div class="twitter_carousel flexslider light loading">
	  		 	 		  <ul class="slides">'; 
            else
            $out = '<div class="twitter_carousel flexslider light loading">
	  		 	 		  <ul class="slides">';

        
		$i = 0;
		foreach($twitter_data as $message) {

			// CHECK THE NUMBER OF ITEMS SHOWN
			if ($i>=$options['num']) {
				break;
			}

			$msg = $message['text'];
			
			if ($msg=='') {
				continue;
			}
			if (isset($options['skip_text']) && $options['skip_text']!='' && strpos($msg, $options['skip_text'])!==false) {
				continue;
			}
			if(isset($options['encode_utf8']) && $options['encode_utf8']) $msg = utf8_encode($msg);
				
			
			if($options['style'] == 'horizontal')
                            $out .= '<li><div class="tweet"><p>';
                         else
                             $out .= '<li><div class="twitter_item clearfix"><p>';
                
			// TODO: LINK
			if (isset($options['thumbnail']) && $options['thumbnail'] && $message['user']['profile_image_url_https']!='') {
				$out .= '<img src="'.$message['user']['profile_image_url_https'].'" />';
			}
			if (isset($options['hyperlinks']) && $options['hyperlinks']) {
				if (isset($options['replace_link_text']) && $options['replace_link_text']!='') {
					// match protocol://address/path/file.extension?some=variable&another=asf%
					$msg = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\" ".$link_target." title=\"$1\">".$options['replace_link_text']."</a>", $msg);
					// match www.something.domain/path/file.extension?some=variable&another=asf%
					$msg = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\" ".$link_target." title=\"$1\">".$options['replace_link_text']."</a>", $msg);    
				} else {
					// match protocol://address/path/file.extension?some=variable&another=asf%
					$msg = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\" ".$link_target.">$1</a>", $msg);
					// match www.something.domain/path/file.extension?some=variable&another=asf%
					$msg = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\" ".$link_target.">$1</a>", $msg);    
				}
				// match name@address
				$msg = preg_replace('/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i',"<a href=\"mailto://$1\" class=\"twitter-link\" ".$link_target.">$1</a>", $msg);
				//NEW mach #trendingtopics
				//$msg = preg_replace('/#([\w\pL-.,:>]+)/iu', '<a href="http://twitter.com/#!/search/\1" class="twitter-link">#\1</a>', $msg);
				//NEWER mach #trendingtopics
				$msg = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://twitter.com/#!/search/%23\2" class="twitter-link" '.$link_target.'>#\2</a>', $msg);
			}
			if ($options['twitter_users'])  { 
				$msg = preg_replace('/([\.|\,|\:|\¡|\¿|\>|\{|\(]?)@{1}(\w*)([\.|\,|\:|\!|\?|\>|\}|\)]?)\s/i', "$1<a href=\"http://twitter.com/$2\" class=\"twitter-user\" ".$link_target.">@$2</a>$3 ", $msg);
			}
          					
			$link = 'http://twitter.com/#!/'.$options['username'].'/status/'.$message['id_str'];
			if(isset($options['linked']) && $options['linked'] == 'all')  { 
				$msg = '<a href="'.$link.'" class="twitter-link" '.$link_target.'>'.$msg.'</a>';  // Puts a link to the status of each tweet 
			} else if (isset($options['linked']) && $options['linked'] != '') {
				$msg = $msg . ' <a href="'.$link.'" class="twitter-link" '.$link_target.'>'.$options['linked'].'</a>'; // Puts a link to the status of each tweet
			} 
			$out .= $msg;
		
			if($options['update']) {				
				$time = strtotime($message['created_at']);
				$h_time = ( ( abs( time() - $time) ) < 86400 ) ? sprintf( __('%s ago', 'vibe'), human_time_diff( $time )) : date(__('M d', 'vibe'), $time);
				$out .= '<span>,</span> <span class="twitter-timestamp" title="' . date(__('Y/m/d H:i', 'vibe'), $time) . '">' . $h_time . '</span>';
			}          
                  
			 
			$out .= '</p>';
                if($options['style'] != 'horizontal')
                    $out .=$image;
                $out .='</div></li>';
		$i++;
	}
	$out .= '</ul></div>';
	
         if($options['style'] == 'horizontal')
             $out .= '</div>';
	
		return $out;
	}
        
  protected function debug ($options, $text) {
		if (isset($options['debug']) && $options['debug']) {
			echo $text."\n";
		}
	}
        
    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;  
		if ( $title ) {
			if ( $instance['link_title'] === true ) {
				$link_target = ($instance['link_target_blank']) ? ' target="_blank" ' : '';
				echo $before_title . '<a href="http://twitter.com/' . $instance['username'] . '" class="twitter_title_link" '.$link_target.'>'. $title . '</a>' . $after_title;
			} else {
				echo $before_title . $title . $after_title;
			}
		}
                
		echo $this->really_simple_twitter_messages($instance);
		echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
		$instance = $old_instance;
		
		foreach ($this->options as $val) {
			if ($val['type']=='text') {
				$instance[$val['name']] = strip_tags($new_instance[$val['name']]);
			} else if ($val['type']=='checkbox') {
				$instance[$val['name']] = ($new_instance[$val['name']]=='on') ? true : false;
			}else if ($val['type']=='select') {
				$instance[$val['name']] = strip_tags($new_instance[$val['name']]);
			}
		}
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
		if (empty($instance)) {
			$instance['title']			= __( 'Last Tweets', 'vibe' );
			$instance['username']		= '';
                        $instance['style']		= 'default';
			$instance['num']			= '5';
			$instance['update']			= true;
			$instance['linked']			= '#';
			$instance['hyperlinks'] 	= true;
			$instance['twitter_users']	= true;
			$instance['skip_text']		= '';
			$instance['encode_utf8']	= false;
			$instance['debug']			= false;
		}					
	/*
	ReallySimpleTwitterWidget Object ( [languagePath:ReallySimpleTwitterWidget:private] => widgets/languages [id_base] => reallysimpletwitterwidget [name] => VibeCom Twitter Widget [widget_options] => Array ( [classname] => widget_reallysimpletwitterwidget ) [control_options] => Array ( [id_base] => reallysimpletwitterwidget ) [number] => 2 [id] => reallysimpletwitterwidget-2 [updated] => [options] => Array ( [0] => Array ( [name] => title [label] => Title [type] => text ) [1] => Array ( [name] => title_icon [label] => Show Twitter icon on title [type] => checkbox ) [2] => Array ( [name] => link_title [label] => Link above Title with Twitter user [type] => checkbox ) [3] => Array ( [type] => separator ) [4] => Array ( [name] => username [label] => Twitter Username [type] => text ) [5] => Array ( [name] => num [label] => Show # of Tweets [type] => text ) [6] => Array ( [name] => linked [label] => Show this linked text for each Tweet [type] => text ) [7] => Array ( [name] => skip_text [label] => Skip tweets containing this text [type] => text ) [8] => Array ( [type] => separator ) [9] => Array ( [name] => link_user [label] => Link below tweets with Twitter user [type] => checkbox ) [10] => Array ( [name] => link_user_text [label] => Text for link below tweets [type] => text ) [11] => Array ( [name] => update [label] => Show timestamps [type] => checkbox ) [12] => Array ( [name] => hyperlinks [label] => Find and show hyperlinks [type] => checkbox ) [13] => Array ( [name] => twitter_users [label] => Find Replies in Tweets [type] => checkbox ) [14] => Array ( [name] => link_target_blank [label] => Create links on new window / tab [type] => checkbox ) ) [option_name] => widget_reallysimpletwitterwidget )
	Title
	
	*/
	
		foreach ($this->options as $val) { 
			$label = '<label>'.$val['label'].'</label>';
			if ($val['type']=='separator') {
				echo '<hr />';
			} else if ($val['type']=='text') {
				echo '<p>'.$label.'<br />';
				echo '<input class="widefat" id="'.$this->get_field_id($val['name']).'" name="'.$this->get_field_name($val['name']).'" type="text" value="'.esc_attr($instance[$val['name']]).'" /></p>';
			} else if ($val['type']=='checkbox') {
				$checked = ($instance[$val['name']]) ? 'checked="checked"' : '';
				echo '<input id="'.$this->get_field_id($val['name']).'" name="'.$this->get_field_name($val['name']).'" type="checkbox" '.$checked.' /> '.$label.'<br />';
			}else if ($val['type']=='select') {
                                echo '<p>'.$label.'<br />';
				$selected = ($instance[$val['name']]) ? 'checked="checked"' : '';
				echo '<select id="'.$this->get_field_id($val['name']).'" name="'.$this->get_field_name($val['name']).'">';
                                        foreach($val['options'] as $option ){
                                            echo '<option value="'.$option.'"  '.(($instance[$val['name']] == $option)? 'SELECTED':'').'>'.$option.'</option>';
                                        }
                                echo      '</select><br />';
			}
		}
	}

} // class ReallySimpleTwitterWidget

// register ReallySimpleTwitterWidget widget
add_action('widgets_init', function(){ return register_widget("ReallySimpleTwitterWidget");});
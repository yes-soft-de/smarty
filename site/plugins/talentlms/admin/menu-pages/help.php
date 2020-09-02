<?php

	if ($screen_id == $tlms_dashboard) {
		get_current_screen()->add_help_tab( array(
			'id'		=> 'about',
			'title'		=> __('About TalentLMS', 'talentlms'),
			'content'	=>
				'<p>' . '<strong>' . __('TalentLMS', 'talentlms') . '</strong>' . __(' a super-easy, cloud-based learning platform to train your people and customers', 'talentlms') . '</p>' .
				'<p>' . '<strong>' . __('ShortCodes', 'talentlms') . '</strong>' . '</p>' .
				'<ul>' .
					'<li>' . '<strong>[talentlms-courses]</strong> ' . __('Shortcode for listing your TalentLMS courses.', 'talentlms') . '</li>' .
					//'<li>' . '<strong>[talentlms-signup]</strong> ' . __('Shortcode for a signup to TalentLMS form.', 'talentlms') . '</li>' .
					//'<li>' . '<strong>[talentlms-forgot-credentials]</strong> ' . __('Shortcode for a forgot your TalentLMS username/password form', 'talentlms') . '</li>' .
					//'<li>' . '<strong>[talentlms-login]</strong> ' . __('Shortcode for a login to TalentLMS form', 'talentlms') . '</li>' .
				'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'		=> 'screen-content',
			'title'		=> __('Screen Content', 'talentlms'),
			'content'	=>
				'<p>' . __('TalentLMS Setup', 'talentlms') . '</p>' .
				'<ul>' .
					'<li>' . '<strong>Setup</strong> ' . __('Setup your TalentLMS domain and API key to get your plugin started.', 'talentlms') . '</li>' .
					'<li>' . '<strong>Integrations</strong> ' . __('Integrate your TalentLMS WordPress plugin with other popular WordPress plugins', 'talentlms') . '</li>' .
					//'<li>' . '<strong>Options</strong> ' . __('Choose the options to customize your plugin\'s appearance and behavior', 'talentlms') . '</li>' .
					//'<li>' . '<strong>Sync</strong> ' . __('Sync your TalentLMS and WordPress users and content', 'talentlms') . '</li>' .
					//'<li>' . '<strong>CSS</strong> ' . __('Edit the plugin\'s CSS rules to best match your WordPress theme\'s look and feel', 'talentlms') . '</li>' .
					'<li>' . '<strong>Shortcodes</strong> ' . __('A coprehensive list of all WordPress TalentLMS plugin\'s shortcodes', 'talentlms') . '</li>' .
					'<li>' . '<strong>Help</strong> ' . __('Details about the plugin and any help you might need.', 'talentlms') . '</li>' .
				'</ul>'
		) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('For more information', 'talentlms') . ':</strong></p>' .
			'<p>' . __('<a href="http://www.talentlms.com/" target="_blank">TalentLMS</a>') . '</p>' .
			'<p>' . __('<a href="http://support.talentlms.com/" target="_blank">Support</a>') . '</p>'
		);		
	}

	if ($screen_id == $tlms_setup) {
		get_current_screen()->add_help_tab( array(
			'id'		=> 'about',
			'title'		=> __('About TalentLMS', 'talentlms'),
			'content'	=>
				'<p>' . '<strong>' . __('TalentLMS', 'talentlms') . '</strong>' . __(' a super-easy, cloud-based learning platform to train your people and customers', 'talentlms') . '</p>' .
				'<p>' . '<strong>' . __('ShortCodes', 'talentlms') . '</strong>' . '</p>' .
				'<ul>' .
					'<li>' . '<strong>[talentlms-courses]</strong> ' . __('Shortcode for listing your TalentLMS courses.', 'talentlms') . '</li>' .
//					'<li>' . '<strong>[talentlms-signup]</strong> ' . __('Shortcode for a signup to TalentLMS form.', 'talentlms') . '</li>' .
//					'<li>' . '<strong>[talentlms-forgot-credentials]</strong> ' . __('Shortcode for a forgot your TalentLMS username/password form', 'talentlms') . '</li>' .
//					'<li>' . '<strong>[talentlms-login]</strong> ' . __('Shortcode for a login to TalentLMS form', 'talentlms') . '</li>' .
				'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'		=> 'screen-content',
			'title'		=> __('Screen Content', 'talentlms'),
			'content'	=>
				'<p>' . __('TalentLMS Setup') . ':</p>' .
				'<ul>' .
					'<li>' . __('TalentLMS Domain: Fill in your TalentLMS domain. A valid TalentLMS domain for the plugin would be like: <pre>&lt;your_domain&gt;.talentlms.com</pre> Do not include the prefix http(s)://', 'talentlms') . '</li>' .
					'<li>' . __('API Key: Fill in your TalentLMS API key. You can find this in your TalentLMS  Home / Account & Settings > Security. Click on <i>Enable the API</i> and copy paste your API key.', 'talentlms') . '</li>' .
				'</ul>'
		) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __('For more information', 'talentlms') . ':</strong></p>' .
			'<p>' . __('<a href="http://www.talentlms.com/" target="_blank">TalentLMS</a>') . '</p>' .
			'<p>' . __('<a href="http://support.talentlms.com/" target="_blank">Support</a>') . '</p>'
		);		
	}
	

    if($screen_id == $tlms_integrations) {
        get_current_screen()->add_help_tab( array(
            'id'		=> 'about',
            'title'		=> __('About TalentLMS', 'talentlms'),
            'content'	=>
                '<p>' . '<strong>' . __('TalentLMS', 'talentlms') . '</strong>' . __(' a super-easy, cloud-based learning platform to train your people and customers', 'talentlms') . '</p>'
        ) );
        get_current_screen()->add_help_tab(array(
            'id'		=> 'screen-content',
            'title'		=> __('TalentLMS Integrations', 'talentlms'),
            'content'	=>
                '<p>' . __('WooCommerce', 'talentlms') . ':</p>' .
                '<ul>' .
                '<li>' . '<strong>'. __('Refresh course list', 'talentlms').':</strong> '. __('Click this option in case some of your TalentLMS courses do not appear in the list', 'talentlms') . '</li>' .
                '<li>' . '<strong>'. __('Integrate', 'talentlms') . ':</strong> '. __('Choose your TalentLMS courses you want to integrate as WooCommerce products. All TalentLMS categories will be integrated by default. In case you need to integrate courses that have been already integrated choose the option "Re-sync"', 'talentlms') . '</li>' .
                '</ul>'
        ));
        get_current_screen()->set_help_sidebar(
            '<p><strong>' . __('For more information:') . '</strong></p>' .
            '<p>' . __('<a href="http://www.talentlms.com/" target="_blank">TalentLMS</a>') . '</p>' .
            '<p>' . __('<a href="http://support.talentlms.com/" target="_blank">Support</a>') . '</p>'
        );
    }

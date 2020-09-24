<?php

if(!class_exists('VIBE_Options')){
	require_once( dirname( __FILE__ ) . '/options/options.php' );
}

/*
 * 
 * Custom function for filtering the sections array given by theme, good for child themes to override or add to the sections.
 * Simply include this function in the child themes functions.php file.
 *
 * NOTE: the defined constansts for urls, and dir will NOT be available at this point in a child theme, so you must use
 * get_template_directory_uri() if you want to use any of the built in icons
 *
 */
function add_another_section($sections){
	
	//$sections = array();
	$sections[] = array(
				'title' => __('A Section added by hook', 'vibe'),
				'desc' => '<p class="description">'.__('This is a section created by adding a filter to the sections array, great to allow child themes, to add/remove sections from the options.', 'vibe').'</p>',
				//all the glyphicons are included in the options folder, so you can hook into them, or link to your own custom ones.
				//You dont have to though, leave it blank for default.
				'icon' => trailingslashit(get_template_directory_uri()).'options/img/glyphicons/glyphicons_062_attach.png',
				//Lets leave this as a blank section, no options just some intro text set above.
				'fields' => array()
				);
	
	return $sections;
	
}//function


/*
 * 
 * Custom function for filtering the args array given by theme, good for child themes to override or add to the args array.
 *
 */
function change_framework_args($args){
	
	//$args['dev_mode'] = false;
	
	return $args;
	
}

/*
 * This is the meat of creating the optons page
 *
 * Override some of the default values, uncomment the args and change the values
 * - no $args are required, but there there to be over ridden if needed.
 *
 *
 */

function setup_framework_options(){
$args = array();
global $vibe_options;

      $vibe_options = get_option(THEME_SHORT_NAME);  //Initialize Vibeoptions
//Set it to dev mode to view the class settings/info in the form - default is false
$args['dev_mode'] = false;

//google api key MUST BE DEFINED IF YOU WANT TO USE GOOGLE WEBFONTS
//$args['google_api_key'] = '***';

//Remove the default stylesheet? make sure you enqueue another one all the page will look whack!
//$args['stylesheet_override'] = true;

//Add HTML before the form
$args['intro_text'] = '';

//Setup custom links in the footer for share icons
$args['share_icons']['twitter'] = array(
										'link' => 'http://twitter.com/vibethemes',
										'title' => __('Folow me on Twitter','vibe'), 
										'img' => VIBE_OPTIONS_URL.'img/ico-twitter.png'
										);
$args['share_icons']['facebook'] = array(
										'link' => 'http://facebook.com/vibethemes',
										'title' => __('Be our Fan on Facebook','vibe'), 
										'img' => VIBE_OPTIONS_URL.'img/ico-facebook.png'
										);
$args['share_icons']['gplus'] = array(
										'link' => 'https://plus.google.com/107421230631579548079',
										'title' => __('Follow us on Google Plus','vibe'), 
										'img' => VIBE_OPTIONS_URL.'img/ico-g+.png'
										);
$args['share_icons']['rss'] = array(
										'link' => 'feed://themeforest.net/feeds/users/VibeThemes',
										'title' => __('Latest News from VibeThemes','vibe'), 
										'img' => VIBE_OPTIONS_URL.'img/ico-rss.png'
										);

//Choose to disable the import/export feature
//$args['show_import_export'] = false;

//Choose a custom option name for your theme options, the default is the theme name in lowercase with spaces replaced by underscores
$args['opt_name'] = THEME_SHORT_NAME;

//Custom menu icon
//$args['menu_icon'] = '';

//Custom menu title for options page - default is "Options"
$args['menu_title'] = __(THEME_FULL_NAME, 'vibe');

//Custom Page Title for options page - default is "Options"
$args['page_title'] = __('Vibe Options Panel v 2.0', 'vibe');

//Custom page slug for options page (wp-admin/themes.php?page=***) - default is "vibe_theme_options"
$args['page_slug'] = THEME_SHORT_NAME.'_options';

//Custom page capability - default is set to "manage_options"
$args['page_cap'] = 'manage_options';

//page type - "menu" (adds a top menu section) or "submenu" (adds a submenu) - default is set to "menu"
//$args['page_type'] = 'submenu';
//$args['page_parent'] = 'themes.php';
$social_links=array();
if(function_exists('social_sharing_links')){
$social_links= social_sharing_links();
foreach($social_links as $link => $value){
    $social_links[$link]=$link;
 }
}


//custom page location - default 100 - must be unique or will override other items
$args['page_position'] = 62;

$args['help_tabs'][] = array(
							'id' => 'vibe-opts-1',
							'title' => __('Support', 'vibe'),
							'content' => '<p>'.__('We provide support via three mediums (in priority)','vibe').':
                                                            <ul><li><a href="http://vibethemes.com/documentation/wplms" target="_blank">'.THEME_FULL_NAME.' VibeThemes Forums</a></li><li>'.__('Support Email: VibeThemes@gmail.com', 'vibe').'</li><li>'.__('ThemeForest Item Comments','vibe').'</li></ul>
                                                            </p>',
							);
$args['help_tabs'][] = array(
							'id' => 'vibe-opts-2',
							'title' => __('Documentation & Links', 'vibe'),
							'content' => '<ul><li><a href="http://vibethemes.com/documentation/wplms/forums/" target="_blank">'.THEME_FULL_NAME.' Support Panel</a></li>
	                                          <li><a href="http://vibethemes.com/documentation/wplms/knowledge-base/one-click-setup-wplms-demo1/" target="_blank">'.THEME_FULL_NAME.' Theme Setup</a></li>
	                                          <li><a href="http://vibethemes.com/documentation/wplms/article-categories/faqs/" target="_blank">'.THEME_FULL_NAME.' Common FAQs/Issues</a></li>  
	                                          <li><a href="http://vibethemes.com/documentation/wplms/article-categories/tips-tricks/" target="_blank">'.THEME_FULL_NAME.' Tips and Tricks</a></li>
	                                          <li><a href="http://vibethemes.com/documentation/wplms/forums/forum/general/feature-request/" target="_blank">'.THEME_FULL_NAME.' Feature Requests</a></li>    
	                                          <li><a href="http://vibethemes.com/documentation/wplms/article-categories/update-log/" target="_blank">'.THEME_FULL_NAME.' Update Log</a></li>    
	                                          <li><a href="https://www.youtube.com/watch?v=A0RsQvmDuSM&list=PL8n4TGA_rwD_5jqsgXIxXOk1H6ar-SVCV" target="_blank">'.THEME_FULL_NAME.' Video Guide</a></li>
	                                      </ul>
                                                            ',
							);


//Set the Help Sidebar for the options page - no sidebar by default										
$args['help_sidebar'] = '<p>For Support/Help and Docuementation open <strong><a href="http://vibethemes.com/documentation/wplms/">'.THEME_FULL_NAME.' forums</a></strong>'.__('Or email us at','vibe').' <a href="mailto:vibethemes@gmail.com">vibethemes@gmail.com</a>. </p>';



$sections = array();

$sections[] = array(
				'title' => __('Getting Started', 'vibe'),
				'desc' => '<p class="description">'.__('Welcome to '.THEME_FULL_NAME.' Theme Options panel. ','vibe').'</p>
                                    <ol>
                                        <li>'.__('See Theme documentation : ','vibe').'<a href="http://vibethemes.com/envato/wplms/documentation/" class="button">Official WPLMS Documentation</a></li> 
                                        <li>'.__('Setup in One Click ','vibe').' <a href="http://vibethemes.com/documentation/wplms/knowledge-base/wplms-one-click-setup/"> '.__('Setup WPLMS','vibe').'</a><small>'.'</small></li> 
                                        <li>'.__('Setup in One Click with New demos','vibe').' <a href="http://vibethemes.com/documentation/wplms/knowledge-base/one-click-setup-wplms-demo1/" class="button button-primary"> '.__('Setup WPLMS','vibe').'</a><small>'.'</small></li> 
                                        <li>'.__('Facing issues? Create a support thread. ','vibe').' <a href="http://vibethemes.com/documentation/wplms/forums/" class="button" target="_blank">'.__('Full Theme Guide','vibe').'</a></li> 
                                        <li>'.__('How to Update? Facing Issues while updating?','vibe').' <a href="http://vibethemes.com/documentation/wplms/knowledge-base/auto-updating-wplms/">Auto updating WPLMS.</a></li>     
                                    </ol>
                                    
                                    </p>',
				//all the glyphicons are included in the options folder, so you can hook into them, or link to your own custom ones.
				//You dont have to though, leave it blank for default.
				'icon' => 'menu',
                                'fields' => array(
                                    array(
						'id' => 'notice',
						'type' => 'divide',
                        'desc' => __('Details required for Auto-Update','vibe')
						),
                         array(
						'id' => 'token',
						'type' => 'token',
						'title' => __('Login to Envato / Themeforest for Updates', 'vibe'), 
						'title_alt'=>__('Updates Active. Click to re-confirm.', 'vibe'), 
						'sub_desc' => __('Login to generate token for automatic updates', 'vibe'),
                                                'std' => ''
						),           
                                    array(
						'id' => 'username',
						'type' => 'text',
						'title' => __('Enter Your Themeforest Username', 'vibe'), 
						'sub_desc' => __('Required for Automatic Upgrades.', 'vibe'),
                                                'std' => ''
						),
                        /*array(
						'id' => 'apikey',
						'type' => 'password',
						'title' => __('Enter Your Themeforest API KEY', 'vibe'), 
						'sub_desc' => __('Please Enter your API Key.Required for Automatic Upgrades.', 'vibe'),
                                                'desc' => __('Whats an API KEY? Where can I find one?','vibe').' : <a href="http://themeforest.net/help/api" target="_blank">Get all your Anwers here</a> or use our Support Forums',
                                                'std' => ''
						),*/
                                    )
                                );


$sections[] = array(
				'icon' => 'admin-generic',
				'title' => __('Header', 'vibe'),
				'desc' => '<p class="description">'.__('Header settings','vibe').'..</p>',
				'fields' => array(
                    
                       array(
						'id' => 'logo',
						'type' => 'text_upload',
						'title' => __('Upload Logo', 'vibe'), 
						'sub_desc' => __('Upload your logo', 'vibe'),
						'desc' => sprintf(__('This Logo is shown in header. NOT ABLE TO UPLOAD ? %s REFER TIP %s', 'vibe'),'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/logo-and-favicon-not-uploading/" target="_blank">','</a>'),
                        'std' => VIBE_URL.'/assets/images/logo.png'
						),
                       array(
						'id' => 'mobile_logo',
						'type' => 'text_upload',
						'title' => __('Upload Logo for Mobile', 'vibe'), 
						'sub_desc' => __('Upload a logo for mobile viewport', 'vibe'),
						'desc' => sprintf(__('This Logo is shown in header on Mobile devices, less than 768px wide. NOT ABLE TO UPLOAD ? %s REFER TIP %s', 'vibe'),'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/logo-and-favicon-not-uploading/" target="_blank">','</a>'),
                        'std' => VIBE_URL.'/assets/images/logo.png'
						),
                       array(
						'id' => 'alt_logo',
						'type' => 'text_upload',
						'title' => __('Upload Alternate Logo', 'vibe'), 
						'sub_desc' => __('Alternate logo', 'vibe'),
						'desc' => sprintf(__('This Logo is shown in header when it becomes fixed. Or in the Header top area. Defaults to logo.NOT ABLE TO UPLOAD ? %s REFER TIP %s', 'vibe'),'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/logo-and-favicon-not-uploading/" target="_blank">','</a>'),
                        'std' => VIBE_URL.'/assets/images/logo.png'
						),
                        array(
						'id' => 'favicon',
						'type' => 'upload',
						'title' => __('Upload Favicon', 'vibe'), 
						'sub_desc' => __('Upload 16x16px Favicon', 'vibe'),
						'desc' => sprintf(__('Upload 16x16px Favicon.NOT ABLE TO UPLOAD ? %s REFER TIP %s', 'vibe'),'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/logo-and-favicon-not-uploading/" target="_blank">','</a>'),
                        'std' => VIBE_URL.'/assets/images/favicon.png'
						),
                         array(
							'id' => 'header_fix',
							'type' => 'button_set',
							'title' => __('Fix Top Header on Scroll', 'vibe'), 
							'sub_desc' => __('Fix header on top of screen' , 'vibe'),
							'desc' => __('header is fixed to top as user scrolls down.', 'vibe'),
							'options' => array('0' => __('Static','vibe'),'1' => __('Fixed on Scroll','vibe')),//Must provide key => value pairs for radio options
						'std' => '0'
						),  
						array(
							'id' => 'course_search',
	                        'title' => __('Navigation Search', 'vibe'),
	                        'sub_desc' => __('Force the header search to search only in courses', 'vibe'),
	                        'type' => 'button_set',
							'options' => array('0' => __('No','vibe'),'1'=>__('Course Search','vibe'),'2'=>__('Course Category Search after menu','vibe'),'3'=>__('Course Category Search before menu','vibe')),//Must provide key => value pairs for radio options
							'std' => '0'
						),   
						array(
							'id' => 'headertop_content',
							'type' => 'editor',
							'title' => __('Header Top Content (Optional)', 'vibe'), 
							'sub_desc' => __('* Standard headers', 'vibe'),
							'desc' => __('Optional content, only required in Header variations', 'vibe'),
	                        'std' => 'Call us Today ! 11223344 / 45678 | info@yourdomain.com'
						),
						array(
							'id' => 'header_content',
							'type' => 'editor',
							'title' => __('Header Content (Optional)', 'vibe'), 
							'sub_desc' => __('Required in "some" header variations', 'vibe'),
							'desc' => __('Optional content, only required in Header variations', 'vibe'),
	                        'std' => ''
						),
						array(
							'id' => 'header_reload',
	                        'title' => __('Enabl ajax header reload', 'vibe'),
	                        'sub_desc' => __('When your site is heavily cached, header is reloaded after page load based on user logged in status.', 'vibe'),
	                        'type' => 'button_set',
							'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),//Must provide key => value pairs for radio options
							'std' => '0'
						), 
					)
				);

$sections[] = array(
				'icon' => 'feedback',
				'title' => __('Sidebar Manager', 'vibe'),
				'desc' => '<p class="description">'.__('Generate more sidebars dynamically and use them in various layouts','vibe').'..</p>',
				'fields' => array(
					 array(
						'id' => 'sidebars',
						'type' => 'multi_text',
                        'title' => __('Create New sidebars ', 'vibe'),
                        'sub_desc' => __('Dynamically generate sidebars', 'vibe'),
                        'desc' => __('Use these sidebars in various layouts. DO NOT ADD ANY SPECIAL CHARACTERS in Sidebar name', 'vibe')
						),	
					array(
							'id' => 'events_sidebar',
							'type' => 'sidebarselect',
							'title' => __('All Events page Sidebar', 'vibe'), 
							'sub_desc' => __('Select All events page sidebar', 'vibe'),
							'desc' => __('Select a sidebar for all events page', 'vibe'),
							'std' => 'mainsidebar'
						),	
					 array(
						'id' => 'sidebars_widgets',
						'type' => 'import_export',
                        'title' => __('Import/Export Sidebar settings ', 'vibe'),
                        'sub_desc' => __('Import/Export sidebars settings', 'vibe'),
                        'desc' => __('Use import/export functionality to import/export your Sidebar settings like Widgets included in sidebars.', 'vibe')
						),
					array(
						'id' => 'widgets_settings',
						'type' => 'widgets_import_export',
                        'title' => __('Import/Export Widget settings ', 'vibe'),
                        'sub_desc' => __('Import/Export widgets settings', 'vibe'),
                        'desc' => __('Use import/export functionality to import/export your widget settings.', 'vibe')
						)		
					)
				);

$sections[] = array(
				'icon' => 'groups',
				'title' => __('Buddypress', 'vibe'),
				'desc' => '<p class="description">'.__('BuddyPress settings and Variables','vibe').'..</p>',
				'fields' => array(
					array(
						'id' => 'default_avatar',
						'type' => 'upload',
						'title' => __('Upload BuddyPress default member avatar', 'vibe'), 
						'sub_desc' => __('BuddyPress default members avatar', 'vibe'),
						'desc' => sprintf(__('This avatar is shown for members who have not uploaded any custom avatar.NOT ABLE TO UPLOAD ? %s REFER TIP %s', 'vibe'),'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/logo-and-favicon-not-uploading/" target="_blank">','</a>'),
                        'std' => VIBE_URL.'/assets/images/avatar.jpg'
						),

					array(
						'id' => 'hide_wp_admin_bar',
                        'title' => __('Hide WP Admin bar for', 'vibe'),
                        'sub_desc' => __('Hide the top WP admin bar', 'vibe'),
                        'desc' => sprintf(__('WP Admin bar is hidden for user types %s Tutorial %s', 'vibe'),'<a href="https://www.youtube.com/watch?v=I_NkIlf7cUY" target="_blank">','</a>'),
                        'type' => 'button_set',
						'options' => array('' => __('Students only','vibe'),'1'=>__('Students & Instructors','vibe'),'2'=>__('Everyone','vibe')),
						'std' => ''
						),
					array(
						'id' => 'wp_admin_access',
                        'title' => __('WP Admin area access', 'vibe'),
                        'sub_desc' => __('Restrict WP Admin area access', 'vibe'),
                        'desc' => __('WP Admin area is restricted for', 'vibe'),
                        'type' => 'button_set',
						'options' => array('' => __('All','vibe'),'1'=>__('Instructors & Administrators only','vibe'),'2'=>__('Administrators only','vibe')),
						'std' => ''
						),
					array(
						'id' => 'loop_number',
                        'title' => __('Buddypress Items Per Page', 'vibe'),
                        'sub_desc' => __('number of items shown per page', 'vibe'),
                        'desc' => __('Number of Buddypress items (Courses,Members,Groups,Forums,Blogs etc..)', 'vibe'),
                        'type' => 'text',
						'std' => '5'
						),
					array(
						'id' => 'members_view',
                        'title' => __('All Members directory View', 'vibe'),
                        'sub_desc' => __('All members pages can be viewed by:', 'vibe'),
                        'desc' => __('Profile viewability : All {Non-Loggedin}, Members{Loggedin Members},Teachers {Teachers, Admins,Editors}', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('All','vibe'),'1'=>__('Members only','vibe'),'2' => __('Teachers only','vibe'),'3' => __('Admins only','vibe')),//Must provide key => value pairs for radio options
						'std' => '0'
						),
					array(
						'id' => 'single_member_view',
                        'title' => __('Single Member Profile View', 'vibe'),
                        'sub_desc' => __('Single members profile can be viewed by:', 'vibe'),
                        'desc' => __('Profile viewability : All {Non-Loggedin}, Members{Loggedin Members},Teachers {Teachers, Admins,Editors}', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('All','vibe'),'1'=>__('Members only','vibe'),'2' => __('Teachers only','vibe'),'3' => __('Admins only','vibe')),//Must provide key => value pairs for radio options
						'std' => '0'
						),
					array(
						'id' => 'single_member_controls',
                        'title' => __('Single Member Profile Tabs visibility', 'vibe'),
                        'sub_desc' => __('Hide single member profile menu for member types', 'vibe'),
                        'desc' => __('Member Menu tabs like "Profile", "Activity","Groups" .. can be hidden', 'vibe'),
                        'type' => 'button_set',
						'options' => array(''=>__('Everyone','vibe'),'1'=>__('Members only','vibe'),'2' => __('Teachers only','vibe'),'3' => __('Admins only','vibe')),//Must provide key => value pairs for radio options
						'std' => '0'
						),
					array(
						'id' => 'members_redirect',
						'type' => 'pages_select',
                        'title' => __('All Members no-access redirect Page', 'vibe'),
                        'sub_desc' => __('User is redirected to this page on error.', 'vibe'),
                        'desc' => __('In case Members view access is denied to the user, user is redirected to this page.','vibe')
						),
					array(
						'id' => 'activity_view',
                        'title' => __('Activity directory View', 'vibe'),
                        'sub_desc' => __('Activity can be viewed by :', 'vibe'),
                        'desc' => __('Activity viewability : All {Non-Loggedin}, Members{Loggedin Members},Teachers {Teachers, Admins,Editors}', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('All','vibe'),'1'=>__('Members only','vibe'),'2' => __('Teachers only','vibe'),'3' => __('Admins only','vibe')),//Must provide key => value pairs for radio options
						'std' => '0'
						),
					array(
						'id' => 'student_activity',
                        'title' => __('Restrict Student activity view', 'vibe'),
                        'sub_desc' => __('Restrict student view of activity', 'vibe'),
                        'desc' => __('Student can view only her activity', 'vibe'),
                        'type' => 'button_set',
						'options' => array('' => __('All Activity','vibe'),'1'=>__('Student Activity','vibe')),//Must provide key => value pairs for radio options
						'std' => ''
						),		
					array(
						'id' => 'activity_redirect',
						'type' => 'pages_select',
                        'title' => __('Activity no-access redirect Page', 'vibe'),
                        'sub_desc' => __('User is redirected to this page on error.', 'vibe'),
                        'desc' => __('In case Activity view access is denied to the user, user is redirected to this page.','vibe')
						),
					array(
						'id' => 'group_view',
                        'title' => __('Group directory View', 'vibe'),
                        'sub_desc' => __('All Groups can be viewed by :', 'vibe'),
                        'desc' => __('Group directory viewability : All {Non-Loggedin}, Members{Loggedin Members},Teachers {Teachers, Admins,Editors}', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('All','vibe'),'1'=>__('Members only','vibe'),'2' => __('Teachers only','vibe'),'3' => __('Admins only','vibe')),//Must provide key => value pairs for radio options
						'std' => '0'
						),	
					array(
						'id' => 'group_redirect',
						'type' => 'pages_select',
                        'title' => __('Group directory no-access redirect Page', 'vibe'),
                        'sub_desc' => __('User is redirected to this page on error.', 'vibe'),
                        'desc' => __('In case Groups view access is denied to the user, user is redirected to this page.','vibe')
						),
					array(
						'id' => 'group_create',
                        'title' => __('Create Groups', 'vibe'),
                        'sub_desc' => __('Groups can be created by :', 'vibe'),
                        'desc' => __('Group creation : Members{Loggedin Members},Teachers {Teachers, Admins,Editors}', 'vibe'),
                        'type' => 'button_set',
						'options' => array('1'=>__('Members only','vibe'),'2' => __('Teachers only','vibe'),'3' => __('Admins only','vibe')),//Must provide key => value pairs for radio options
						'std' => '1'
						),	
					array(
						'id' => 'blog_create',
                        'title' => __('Create Blog (multisite)', 'vibe'),
                        'sub_desc' => __('Blogs can be created by :', 'vibe'),
                        'desc' => sprintf(__('Blog creation : Members{Loggedin Members},Teachers {Teachers, Admins,Editors}, %s screenshot %s', 'vibe'),'<a href="http://prntscr.com/cldri1" target="_blank">','</a>'),
                        'type' => 'button_set',
						'options' => array('1'=>__('Members only','vibe'),'2' => __('Teachers only','vibe'),'3' => __('Admins only','vibe')),//Must provide key => value pairs for radio options
						'std' => '1'
						),	
					array(
						'id' => 'members_activity',
                        'title' => __('Show Members Meta info', 'vibe'),
                        'sub_desc' => __('Members meta-info is shown below the name', 'vibe'),
                        'desc' => __('Members activity, Friendship , Message button is shown in Single & members directory.', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),//Must provide key => value pairs for radio options
						'std' => '0'
						),
					array(
						'id' => 'activity_tab',
                        'title' => __('Profile Activity Tab', 'vibe'),
                        'sub_desc' => __('Single Profile activity can be viewed by :', 'vibe'),
                        'desc' => __('Activity viewability : All {Non-Loggedin}, Members{Loggedin Members},Teachers {Teachers, Admins,Editors}', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('All','vibe'),'1'=>__('Members only','vibe'),'2' => __('Teachers only','vibe'),'3' => __('Admins only','vibe')),//Must provide key => value pairs for radio options
						'std' => '0'
						),	

					array(
						'id' => 'groups_tab',
                        'title' => __('Profile Group View', 'vibe'),
                        'sub_desc' => __('Single Profile Groups can be viewed by :', 'vibe'),
                        'desc' => __('Group viewability : All {Non-Loggedin}, Members{Loggedin Members},Teachers {Teachers, Admins,Editors}', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('All','vibe'),'1'=>__('Members only','vibe'),'2' => __('Teachers only','vibe'),'3' => __('Admins only','vibe')),//Must provide key => value pairs for radio options
						'std' => '0'
						),	
					array(
						'id' => 'forums_tab',
                        'title' => __('Profile Forums View', 'vibe'),
                        'sub_desc' => __('Single Profile Forums can be viewed by :', 'vibe'),
                        'desc' => __('Group viewability : All {Non-Loggedin}, Members{Loggedin Members},Teachers {Teachers, Admins,Editors}', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('All','vibe'),'1'=>__('Members only','vibe'),'2' => __('Teachers only','vibe'),'3' => __('Admins only','vibe')),//Must provide key => value pairs for radio options
						'std' => '0'
						),
					array(
						'id' => 'activation_redirect',
						'type' => 'pages_select',
						'options'=> array('dashboard'=>__('Dashboard','vibe'),'profile'=>__('Profile','vibe'),'mycourses'=>__('My Courses','vibe')),
                        'title' => __('Redirect Page on User Activation', 'vibe'),
                        'sub_desc' => __('User is redirected to this page on activating her account.', 'vibe'),
                        'desc' => __('After registering and activating the account the user is redirected to this page.','vibe')
						),
					array(
						'id' => 'enable_groups_join_button',
						'type' => 'button_set',
                        'title' => __('Enable Join Group/Request Membership button', 'vibe'),
                        'sub_desc' => __('Button is shown in Groups directory', 'vibe'),
                        'desc' => __('Join Group for Public groups and Request Membership button for private groups','vibe'),
                        'options' => array(0 => __('Disable','vibe'),1=>__('Enable','vibe'))
						),
					
					array(
						'id' => 'student_field',
						'type' => 'text',
                        'title' => __('Student Field', 'vibe'),
                        'sub_desc' => __('Enter the name of the Student Field to show below the name.', 'vibe'),
                        'std'=>'Location'
						),
					array(
						'id' => 'instructor_field_group',
						'type' => 'text',
                        'title' => __('Instructor Field Group', 'vibe'),
                        'sub_desc' => __('Enter the name of the Instructor Field Group you want to hide from Students from viewing in Profile -> edit (* No Required fields & Case senstitive).', 'vibe'),
                        'std'=>'Instructor'
						),
                    array(
						'id' => 'instructor_field',
						'type' => 'text',
                        'title' => __('Instructor Field', 'vibe'),
                        'sub_desc' => __('Enter the name of the Instructor Field to show below the name.', 'vibe'),
                        'std'=>'Speciality'
						),
                    array(
						'id' => 'social_field_group',
						'type' => 'text',
                        'title' => __('Social Profiles Field Group', 'vibe'),
                        'sub_desc' => sprintf(__('Add social profiles in user profiles. %s refer tutorial %s', 'vibe'),'<a href=" http://vibethemes.com/documentation/wplms/knowledge-base/social-profile-field-group/ ">','</a>'),
                        'std'=>'Social Profiles'
						),
					array(
						'id' => 'instructor_paypal_field',
						'type' => 'text',
                        'title' => __('Instructor Paypal Field', 'vibe'),
                        'sub_desc' => __('Enter "Field Name" for Instructor PayPal ID', 'vibe'),
                        'desc' => sprintf(__('Set a custom profile field for Commission payouts, %s tutorial %s','vibe'),'<a href="https://www.youtube.com/watch?v=TeVJs0dw-Os" target="_blank">','</a>'),
                        'std'=>''
						),
                    array(
						'id' => 'instructor_about',
						'type' => 'text',
                        'title' => __('Instructor Description Field', 'vibe'),
                        'sub_desc' => __('Instructor Description is picked from this field in the Instructor Widget', 'vibe'),
                        'std'=>'About'
						),	
                    array(
						'id' => 'enable_ajax_registration_login',
						'type' => 'button_set',
                        'title' => __('Enable ajax registration & login', 'vibe'),
                        'sub_desc' => __('Registration & Forgot password forms work inside the popup form. ', 'vibe'),
                        'desc' => __('Registration & Forgot password forms open inside login form.','vibe').'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/ajax-login-and-registration-in-wplms">more</a>',
                        'options' => array('' => __('Disable','vibe'),1=>__('Enable Login only','vibe'),2=>__('Enable Login and registration','vibe'))
						),
					array(
						'id' => 'custom_registration_page',
						'type' => 'pages_select',
                        'title' => __('Custom Registration page', 'vibe'),
                        'sub_desc' => __('Overrides the default registration page from buddypress', 'vibe'),
						),								
					)
				);


$sections[] = array(
				'icon' => 'welcome-learn-more',
				'title' => __('Course Manager', 'vibe'),
				'desc' => '<p class="description">'.__('Manage Course Options from here.','vibe').'..</p>',
				'fields' => array(
					 	array(
						'id' => 'take_course_page',
						'type' => 'pages_select',
                        'title' => __('Take This Course Page', 'vibe'),
                        'sub_desc' => __('A Page with Start Course Page Template', 'vibe'),
						),
						array(
						'id' => 'create_course',
						'type' => 'pages_select',
                        'title' => __('Connect Edit Course Page', 'vibe'),
                        'sub_desc' => __('A Page with "Create Content" Page Template OR a page with shortcode [edit_course]', 'vibe'),
						),
						array(
						'id' => 'unit_comments',
						'type' => 'pages_select',
                        'title' => __('Notes & Discussion Page', 'vibe'),
                        'sub_desc' => __('A Page with "Notes & Discussion" Page Template', 'vibe'),
						),
						array(
						'id' => 'sync_student_count',
                        'title' => __('Maintain accurate Student Count', 'vibe'),
                        'sub_desc' => __('Maintains accurate student count for course', 'vibe'),
                        'desc' => __('The Number of Student in Course count gets verified everytime user visits the Course - admin section', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'tab_style_course_layout',
                        'title' => __('Tab style course layout', 'vibe'),
                        'sub_desc' => __('Changes the course tab style', 'vibe'),
                        'desc' => __('Changes course tab style like that of udemy', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'disable_contextmenu_course_status',
                        'title' => __('Disable right click in Course status page', 'vibe'),
                        'sub_desc' => __('Disable right click in Course status page', 'vibe'),
                        'desc' => __('disable right click for content copy in course status page.', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array( 
						'id' => 'course_status_template',
                        'title' => __('Course status templates', 'vibe'),
                        'sub_desc' => __('Change course status page templates', 'vibe'),
                        'desc' => sprintf(__('Course status page templates, %s tutorial %s', 'vibe'),'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/course-status-templates/" target="_blank">','</a>'),
                        'type' => 'button_set',
						'options' => array('default'=>__('Default','vibe'),'fullscreen' => __('Full Screen','vibe')),
						'std' => ''
						),
						/*array( // To be added in 2.5
						'id' => 'course_status_controls',
                        'title' => __('Show ControlPanel course status page', 'vibe'),
                        'sub_desc' => __('Display controls on course status page', 'vibe'),
                        'desc' => sprintf(__('Course status controls, like hide/show, minimize, fullscreen,chat etc.. %s refer tutorial %s', 'vibe'),'<a href="" target="_blank">','</a>'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),*/
						array(
						'id' => 'new_course_status',
                        'title' => __('Admin Approval for Course', 'vibe'),
                        'sub_desc' => __('Force instructors for Admin approval for new course', 'vibe'),
                        'desc' => __('Force Courses created by Instructors are first sent to Administrator for Approval.', 'vibe'),
                        'type' => 'button_set',
						'options' => array('pending' => __('Yes, require approval','vibe'),'publish'=>__('No, allow publish','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'nextunit_access',
                        'title' => __('Unit Locking', 'vibe'),
                        'sub_desc' => __('Set access to next units based on previous unit status', 'vibe'),
                        'desc' => __('Force users to complete previous units and quiz evalution before viewing the next units', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('Free Access','vibe'),'1'=>__('Force prev Unit/Quiz Complete','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'unit_media_lock',
                        'title' => __('Unit Media Lock', 'vibe'),
                        'sub_desc' => __('Hide unit completion button unless the Media(Audio/Video) is complete', 'vibe'),
                        'desc' => __('Force users to view/listen the Media, Audio/Video in units before marking the unit as complete', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'assignment_locking',
                        'title' => __('Assignment Locking', 'vibe'),
                        'sub_desc' => __('Force users to finish assignments to complete units', 'vibe'),
                        'desc' => __('Mark complete button for a unit will appear only after student has finished the unit assignment', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('Do not lock','vibe'),'1'=>__('Lock Unit Completion','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'course_progressbar',
                        'title' => __('Show Course Progressbar (always active)', 'vibe'),
                        'sub_desc' => __('Show course progress bar above course timeline', 'vibe'),
                        'desc' => __('Course Progress bar is shown in Course curriculum page', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'instructor_add_students',
                        'title' => __('Instructors can Add Students', 'vibe'),
                        'sub_desc' => __('Enable Instructors to be able to add students', 'vibe'),
                        'desc' => __('A Bulk Action is added in Course -> Admin -> Members which enables Instructos to add students to the course', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'instructor_change_status',
                        'title' => __('Instructors can Manage Student Course status ', 'vibe'),
                        'sub_desc' => __('Enable Instructors to be change Students course status', 'vibe'),
                        'desc' => __('A Bulk Action is added in Course -> Admin -> Members which enables Instructos to manage students course status in the course', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'instructor_assign_badges',
                        'title' => __('Instructors can Assign/Remove Certificates & Badges', 'vibe'),
                        'sub_desc' => __('Enable Instructors to be able to assign Certificates & Badges to Students', 'vibe'),
                        'desc' => __('A Bulk Action is added in Course -> Admin -> Members which enables Instructos to assign Certificates & Badges to Students for the course', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'instructor_extend_subscription',
                        'title' => __('Instructors can Extend subscription', 'vibe'),
                        'sub_desc' => __('Enable Instructors to extend subscriptions of Students', 'vibe'),
                        'desc' => __('A Bulk Action is added in Course -> Admin -> Members which enables Instructos to extend subscriptions of Students for the course', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'instructor_content_privacy',
                        'title' => __('Force Instructor Content Privacy', 'vibe'),
                        'sub_desc' => __('Select boxes show only instructor units/quizzes/questions', 'vibe'),
                        'desc' => __('Instructors can view titles but can not open content', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'stats_visibility',
                        'title' => __('Leaderboard/Stats Visibility', 'vibe'),
                        'sub_desc' => __('Stats & Leaderboard visible to', 'vibe'),
                        'desc' => __('Stats displaying average, maxmium, minimum and top 10 students for a module', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('All','vibe'),'1'=>__('Students','vibe'),'2'=>__('All Instructors','vibe'),'3'=>__('Module Instructor & Administrators','vibe')),
						'std' => '0'
						),
                        array(
						'id' => 'teacher_form',
						'type' => 'pages_select',
                        'title' => __('Become an Instructor Page', 'vibe'),
                        'sub_desc' => __('A Page with become a teacher form.', 'vibe'),
						),
                        array(
						'id' => 'certificate_page',
						'type' => 'pages_select',
                        'title' => __('Fallback Certificate Page', 'vibe'),
                        'sub_desc' => __('A Page with certificate page template, Fallback to courses with no Certificate Templates.', 'vibe'),
						),
						array(
						'id' => 'default_course_avatar',
						'type' => 'upload',
						'title' => __('Course default avatar', 'vibe'), 
						'sub_desc' => __('Default avatar for courses', 'vibe'),
						'desc' => sprintf(__('This avatar is shown for courses which do not have any avatar. NOT ABLE TO UPLOAD ? %s REFER TIP %s', 'vibe'),'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/logo-and-favicon-not-uploading/" target="_blank">','</a>'),
                        'std' => ''
						),
						array(
						'id' => 'hide_courses',
						'type' => 'posts_multi_select',
                        'title' => __('Hide Courses from Directory', 'vibe'),
                        'sub_desc' => __('Hide courses from directory & pages, only accessible via direct link', 'vibe'),
                        'args' => 'post_type=course',
                        'class' => 'chosen',
                        'std'=>''
						),
						array(
						'id' => 'course_duration_display_parameter',
						'type' => 'select',
                        'title' => __('Course Duration parameter', 'vibe'),
                        'sub_desc' => __('Set course duration parameter', 'vibe'),
                        'desc' => __('Course duration parameter for display purpose','vibe'),
                        'options' => array(
                        	0 => __('Automatic','vibe'),
                        	1 => __('Seconds','vibe'),
                        	60 => __('Minutes','vibe'),
                        	3600 => __('Hours','vibe'),
                        	86400 => __('Days','vibe'),
                        	604800 => __('Weeks','vibe'),
                        	2592000 => __('Months','vibe'),
                        	31536000 => __('Years','vibe'),
                        	),
                        'std' => 0
						),
						array(
						'id' => 'finished_course_access',
                        'title' => __('Finished Course Access', 'vibe'),
                        'sub_desc' => __('Allow students to view Finished courses', 'vibe'),
                        'desc' => __('Make finished courses secure area viewable to students', 'vibe'),
                        'type' => 'button_set',
						'options' => array('' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => ''
						),
						array(
						'id' => 'notes_style',
                        'title' => __('Notes and Discussion styles', 'vibe'),
                        'sub_desc' => __('Set style for Notes & Discussion template', 'vibe'),
                        'desc' => __('Display notes & Discussions', 'vibe'),
                        'type' => 'button_set',
						'options' => array('' => __('Per paragraph in Unit','vibe'),'1'=>__('Per Unit','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'show_news',
                        'title' => __('Display News', 'vibe'),
                        'sub_desc' => __('Display News section in courses', 'vibe'),
                        'desc' => __('Display News section in courses.', 'vibe'),
                        'type' => 'button_set',
						'options' => array('' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'level',
                        'title' => __('Enable Levels', 'vibe'),
                        'sub_desc' => __('Enables Level taxonomy', 'vibe'),
                        'desc' => __('Enables Level taxonomy in Courses and search page ', 'vibe'),
                        'type' => 'button_set',
						'options' => array('' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => ''
						),
						array(
						'id' => 'location',
                        'title' => __('Enable Course Location', 'vibe'),
                        'sub_desc' => __('Location taxonomy for Courses', 'vibe'),
                        'desc' => sprintf(__('Location taxonomy for Courses in Course filters and Search %s Tutorial %s', 'vibe'),'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/course-location-taxonomy/">','</a>'),
                        'type' => 'button_set',
						'options' => array('' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => ''
						),
						array(
						'id' => 'linkage',
                        'title' => __('Enable Linkage', 'vibe'),
                        'sub_desc' => __('Connect Course, Units, Quiz, Questions with linkage taxonomy', 'vibe'),
                        'desc' => __('Shorten the list of units, quizzes, courses shown in dropdowns', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
						array(
						'id' => 'redirect_course_cat_directory',
                        'title' => __('Course Category/Level/Location redirect', 'vibe'),
                        'sub_desc' => __('Redirect to Course directory', 'vibe'),
                        'desc' => sprintf(__('User redirects to Directory with category activated when she selects course category %s refer tutorial %s', 'vibe'),'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/course-category-redirect-to-course-directory/" target="_blank" class="link">','</a>'),
                        'type' => 'button_set',
						'options' => array('' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => ''
						),

						array(
						'id' => 'default_course_block_style',
                        'title' => __('Default course block style', 'vibe'),
                        'sub_desc' => __('Set a default block style for courses', 'vibe'),
                        'desc' => __('default course block style', 'vibe'),
                        'type' => 'radio_img',
						'options' => array(             
	                                        'course' => array('Default' => __('Four Columns','vibe'), 'img' => plugins_url('vibe-customtypes/metaboxes/library/images/thumb_2.png')),
											'course2' => array('Default' => __('Four Columns','vibe'), 'img' => plugins_url('vibe-customtypes/metaboxes/library/images/thumb_8.png')),
											'course3' => array('Default' => __('Four Columns','vibe'), 'img' => plugins_url('vibe-customtypes/metaboxes/library/images/thumb_8.jpg')),
											'course4' => array('Default' => __('Four Columns','vibe'), 'img' => plugins_url('vibe-customtypes/metaboxes/library/images/thumb_9.jpg')),
											'course5' => array('Default' => __('Four Columns','vibe'), 'img' => plugins_url('vibe-customtypes/metaboxes/library/images/thumb_10.jpg')),
											'generic' => array('Default' => __('Four Columns','vibe'), 'img' => plugins_url('vibe-customtypes/metaboxes/library/images/generic.jpg')),
											'simple' => array('Default' => __('Four Columns','vibe'), 'img' => plugins_url('vibe-customtypes/metaboxes/library/images/simple.jpg')),
	                            ),//Must provide key => value(array:title|img) pairs for radio options
						'std' => '4'
						),
						array(
						'id' => 'related_courses',
                        'title' => __('Show related courses at the end of single course', 'vibe'),
                        'sub_desc' => __('related courses by category, instructor & tags', 'vibe'),
                        'desc' => __('Related courses are displayed based on category & instructor', 'vibe'),
                        'type' => 'button_set',
						'options' => array('0' => __('No','vibe'),'1'=>__('Yes','vibe')),
						'std' => '0'
						),
					)
				);

$sections[] = array(
				'icon' => 'editor-spellcheck',
				'title' => __('Fonts Manager', 'vibe'),
				'desc' => '<p class="description">'.__('Manage Fonts to be used in the Site. Fonts selected here will be available in Theme customizer font family select options.','vibe').'..</p>',
				'fields' => array(
					array(
						'id' => 'google_fonts_api_key',
						'type' => 'text',
                        'title' => __('Enter Google fonts API key', 'vibe'),
                        'sub_desc' => __('Required to fetch a list of Fonts from Google', 'vibe').'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/google-api-key-for-font-manager-and-usage/">Learn how to get Google fonts API</a>'
						),
					 array(
						'id' => 'google_fonts',
						'type' => 'google_webfonts_multi_select',
                        'title' => __('Select Fonts for Live Theme Editor ', 'vibe'),
                        'sub_desc' => __('Select Fonts and setup fonts in Live Editor', 'vibe'),
                        'desc' => __('Use these sample layouts in PageBuilder.', 'vibe')
						),
                        array(
						'id' => 'custom_fonts',
						'type' => 'multi_text',
                        'title' => __('Custom Fonts (Enter CSS Font Family name)', 'vibe'),
                        'sub_desc' => __(' Custom Fonts are added to Theme Customizer Font List.. ', 'vibe').'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/add-custom-fonts-in-wplms/">Learn how to add custom fonts</a>'
						)
					 				
					)
				);


$sections[] = array(
				'icon' => 'visibility',
				'title' => __('Customizer', 'vibe'),
				'desc' => '<p class="description">'.__('Import/Export customizer settings. Customize your theme using ','vibe').' <a href="'.get_admin_url().'customize.php" class="button">'.__('WP Theme Customizer','vibe').'</a></p>',
				'fields' => array(
                     array(
						'id' => 'vibe_customizer',
						'type' => 'import_export',
                        'title' => __('Import/Export Customizer settings ', 'vibe'),
                        'sub_desc' => __('Import/Export customizer settings', 'vibe'),
                        'desc' => __('Use import/export functionality to import/export your customizer settings.', 'vibe')
						)			
					)
				);

$sections[] = array(
				'icon' => 'editor-kitchensink',
				'title' => __('PageBuilder Manager', 'vibe'),
				'desc' => '<p class="description">'.__('Manage PageBuilder saved layouts and Import/Export pagebuilder Saved layouts','vibe').'</p>',
				'fields' => array(
					array(
						'id' => 'sample_layouts',
						'type' => 'pagebuilder_layouts',
                        'title' => __('Manage Sample Layouts ', 'vibe'),
                        'sub_desc' => __('Delete Sample Layouts', 'vibe'),
                        'desc' => __('Use these sample layouts in PageBuilder.', 'vibe')
						),
                    array(
						'id' => 'vibe_builder_sample_layouts',
						'type' => 'import_export',
                        'title' => __('Import/Export Sample Layouts ', 'vibe'),
                        'sub_desc' => __('Import/Export existing Layouts', 'vibe'),
                        'desc' => __('Use import/export functionality to save your layouts.', 'vibe')
						)
					 				
					)
				);

$sections[] = array(
				'icon' => 'editor-insertmore',
				'title' => __('Footer ', 'vibe'),
				'desc' => '<p class="description">'.__('Setup footer settings','vibe').'..</p>',
				'fields' => array( 
						
					 	array(
							'id' => 'top_footer_columns',
							'type' => 'radio_img',
							'title' => __('Top Footer Sidebar Columns', 'vibe'), 
							'sub_desc' => __('Footer Columns', 'vibe'),
							'options' => array(             
	                                        'col-md-3 col-sm-6' => array('title' => __('Four Columns','vibe'), 'img' => VIBE_OPTIONS_URL.'img/footer-1.png'),
											'col-md-4 col-sm-4' => array('title' => __('Three Columns','vibe'), 'img' => VIBE_OPTIONS_URL.'img/footer-2.png'),    
											'col-md-6 col-sm-6' => array('title' => __('Two Columns','vibe'), 'img' => VIBE_OPTIONS_URL.'img/footer-3.png'),
	                                        'col-md-12' => array('title' => __('One Columns','vibe'), 'img' => VIBE_OPTIONS_URL.'img/footer-4.png'),
	                            ),//Must provide key => value(array:title|img) pairs for radio options
							'std' => '4'
						),
                        array(
							'id' => 'bottom_footer_columns',
							'type' => 'radio_img',
							'title' => __('Bottom Footer Sidebar Columns', 'vibe'), 
							'sub_desc' => __('Footer Columns', 'vibe'),
							'options' => array(             
	                                        'col-md-3 col-sm-6' => array('title' => __('Four Columns','vibe'), 'img' => VIBE_OPTIONS_URL.'img/footer-1.png'),
											'col-md-4 col-sm-4' => array('title' => __('Three Columns','vibe'), 'img' => VIBE_OPTIONS_URL.'img/footer-2.png'),    
											'col-md-6 col-sm-6' => array('title' => __('Two Columns','vibe'), 'img' => VIBE_OPTIONS_URL.'img/footer-3.png'),
	                                        'col-md-12' => array('title' => __('One Columns','vibe'), 'img' => VIBE_OPTIONS_URL.'img/footer-4.png'),
	                            ),//Must provide key => value(array:title|img) pairs for radio options
							'std' => '4'
						),  
                        array(
						'id' => 'footer_logo',
						'type' => 'upload',
						'title' => __('Upload Footer Logo', 'vibe'), 
						'sub_desc' => __('Displayed in footer', 'vibe'),
						'desc' => sprintf(__('This Logo is shown in footer bottom area. Defaults to logo. NOT ABLE TO UPLOAD ? %s REFER TIP %s', 'vibe'),'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/logo-and-favicon-not-uploading/" target="_blank">','</a>'),
                        'std' => VIBE_URL.'/assets/images/logo.png'
						),
                        array(
							'id' => 'copyright',
							'type' => 'editor',
							'title' => __('Copyright Text', 'vibe'), 
							'sub_desc' => __('Enter copyrighted text', 'vibe'),
							'desc' => __('Also supports shotcodes.', 'vibe'),
	                        'std' => 'Template Design © <a href="http://www.vibethemes.com" title="VibeCom">VibeThemes</a>. All rights reserved.'
						),
                        array(
						'id' => 'footerbottom_right',
                        'title' => __('Footer Bottom', 'vibe'),
                        'sub_desc' => __('Select an option for FooterBottom', 'vibe'),
                        'desc' => __('Select Footer Bottom style, set Social icon links in Social tab', 'vibe'),
                        'type' => 'button_set',
						'options' => array('' => __('Show Footer Menu','vibe'),'1'=>__('Show Social Icons','vibe')),
						'std' => ''
						),             
                        array(
							'id' => 'google_analytics',
							'type' => 'textarea',
							'title' => __('Google Analytics Code', 'vibe'), 
							'sub_desc' => __('Google Analytics account', 'vibe'),
							'desc' => __('Please enter full code with javascript tags.', 'vibe'),
						),

					 				
					)
				);
$sections[] = array(
				'icon' => 'twitter',
				'title' => __('Social Information', 'vibe'),
				'desc' => '<p class="description">'.__('All Social media settings','vibe').'..</p>',
				'fields' => array(
					   
                        array(
						'id' => 'social_icons',
						'type' => 'multi_social',
                        'title' => __('Add Social Media Icons ', 'vibe'),
                        'sub_desc' => __('Dynamically add social media icons', 'vibe'),
                        'desc' => __('Add your Full URL in social media.', 'vibe')
						),
                        array(
						'id' => 'social_icons_type',
						'type' => 'button_set',
						'title' => __('Social Icons Type', 'vibe'), 
						'sub_desc' => __('Social Icons Theme', 'vibe'),
						'options' => array('' => __('Minimal','vibe'),'round' => __('Round','vibe'),'square' => __('Square','vibe'),'round color' => __('Round Colored','vibe'),'square color' => __('Square Colored','vibe')),
						'std' => ''
						),
                        array(
						'id' => 'show_social_tooltip',
						'type' => 'button_set',
						'title' => __('Show Tooltip on Social Icons', 'vibe'), 
						'options' => array(1 => __('Yes','vibe'),0 => __('No','vibe')),
						'std' => 1
						),     
                        array(
						'id' => 'social_share',
						'type' => 'multi_select',
                        'title' => __('Social Sharing buttons', 'vibe'),
                        'sub_desc' => __('Show in-built sharing buttons in the theme', 'vibe'),
                        'desc' => __('Adds Social media sharing buttons in single Courses etc.', 'vibe'),
                        'options' => $social_links
						),
					)
				);
$sections[] = array(
				'icon' => 'welcome-view-site',
				'title' => __('TinCan/xAPI', 'vibe'),
				'desc' =>'<p class="description">'. __('TinCan and LRS settings for WPLMS', 'vibe').'</p>',
				'fields' => array(
                        array(
						'id' => 'tincan',
						'type' => 'button_set',
						'title' => __('Enable TinCan recording', 'vibe'), 
						'sub_desc' => __('Record TinCan/XAPI statements in External LRS', 'vibe'),
						'desc' => __('Store all activity in an External LRS, which other XAPI compatible LMSes can read.', 'vibe'),
						'options' => array(0 => __('Disable','vibe'),1 => __('Enable','vibe')),
						'std' => 0
						),	
						array(
						'id' => 'tincan_endpoint',
                        'title' => __('TinCan API EndPoint', 'vibe'),
                        'sub_desc' => __('Add a TinCan API endpoint', 'vibe'),
                        'desc' => __('Add Endpoint to track details in external LRS.', 'vibe'),
                        'type' => 'text',
						'std' => '0'
						),
						array(
						'id' => 'tincan_user',
                        'title' => __('LRS User name', 'vibe'),
                        'sub_desc' => __('TinCan compatible LRS authentication', 'vibe'),
                        'desc' => __('Enter Username for external LRS authentication.', 'vibe'),
                        'type' => 'text',
						'std' => '0'
						),
						array(
						'id' => 'tincan_pass',
                        'title' => __('LRS Password', 'vibe'),
                        'sub_desc' => __('TinCan compatible LRS authentication', 'vibe'),
                        'desc' => __('Enter Password for external LRS authentication.', 'vibe'),
                        'type' => 'password',
						'std' => '0'
						),
					)
				);
$sections[] = array(
				'icon' => 'location',
				'title' => __('Miscellaneous', 'vibe'),
				'desc' =>'<p class="description">'. __('Miscellaneous settings used in the theme.', 'vibe').'</p>',
				'fields' => array(
                        array(
						'id' => 'page_loader',
						'type' => 'button_set',
						'title' => __('Page Loader', 'vibe'), 
						'sub_desc' => __('Add a Page loader on site, everything is hidden till the page loads', 'vibe'),
						'desc' => sprintf(__('A loading icon will be displayed to the user, till the whole page loads. %s', 'vibe'),'<a href="https://www.youtube.com/watch?v=pwxYL080oFQ" target="_blank">tutorial</a>'),
						'options' => array('' => __('Disable','vibe'),'pageloader1' => __('Enable, Style 1','vibe'),'pageloader2' => __('Enable, Style 2','vibe')),
						'std' => ''
						),                 
						array(
						'id' => 'site_lock',
						'type' => 'button_set',
						'title' => __('Site Lock', 'vibe'), 
						'sub_desc' => __('Lock entire site, only logged in users can view the site', 'vibe'),
						'desc' => sprintf(__('All the links require login. Only home page is accessible to non-logged in users. %s', 'vibe'),'<a href="https://www.youtube.com/watch?v=pwxYL080oFQ" target="_blank">tutorial</a>'),
						'options' => array('' => __('No','vibe'),'1' => __('Yes','vibe')),
						'std' => ''
						), 
						array(
						'id' => 'site_lock_home_page_url',
						'type' => 'pages_select',
						'title' => __(' Site lock Logo link page', 'vibe'), 
						'sub_desc' => __('Url on header logo and footer logo when site lock is enabled', 'vibe'),
						'desc' => sprintf(__('Select page which you want to redirect users to when a user clicks on the site logo.','vibe')),
						),
						array(
						'id' => 'security_key',
						'type' => 'text',
						'title' => __('Unique Security Key', 'vibe'), 
						'sub_desc' => __('Security key for every site. Longer keys are good', 'vibe'),
						'desc' => __('Unique key to avoid (logged in) users from bypassing the system.', 'vibe'),
						'std' => wp_generate_password()
						),
						array(
						'id' => 'default_archive',
						'type' => 'button_set',
						'title' => __('Default Archive style', 'vibe'), 
						'sub_desc' => __('Set the default archive style from default, blog1, blog 2, blog 3', 'vibe'),
						'options' => array('' => __('Default','vibe'),'blog1' => __('Blog 1','vibe'),'blog2' => __('Blog 2','vibe'),'blog3' => __('Blog 3','vibe')),
						'std' => ''
						), 
						array(
						'id' => 'excerpt_length',
						'type' => 'text',
						'title' => __('Default Excerpt Length', 'vibe'), 
						'sub_desc' => __('Excerpt length in number of Words.', 'vibe'),
						'std' => '20'
						),
						array(
						'id' => 'page_comments',
						'type' => 'button_set',
						'title' => __('Comments in pages', 'vibe'), 
						'sub_desc' => __('Enable comments in Pages (Disabled by default)','vibe'),
						'desc' => __('Users can post comments in pages.', 'vibe'),
						'options' => array('' => __('Disable','vibe'),1 => __('Enable','vibe')),
						'std' => 0
						),
						array(
						'id' => 'instructor_commission',
						'type' => 'number',
						'title' => __('Default Instructor Commission', 'vibe'), 
						'sub_desc' => __('Insructor commission per sale of course/product (enter 0 to disable)', 'vibe'),
						'std' => '70'
						),
						array(
						'id' => 'direct_checkout',
						'type' => 'button_set',
						'title' => __('Direct Checkout', 'vibe'), 
						'sub_desc' => __('Requires WooCommerce installed','vibe'),
						'desc' => __('User is redirected to the checkout page.', 'vibe'),
						'options' => array(2 => __('Skip Product & Cart page','vibe'),3 => __('Skip Product page','vibe'),1 => __('Skip Cart','vibe'),0 => __('Disable','vibe')),
						'std' => 0
						),
                        array(
						'id' => 'thankyou_redirect',
						'type' => 'button_set',
						'title' => __('Redirect to Course Page on Order completion', 'vibe'), 
						'sub_desc' => __('Only if the order contains one product with one course', 'vibe'),
						'desc' => __('If you\'re forcing the direct checkout, and your products have one course per product then switching this on would send users directly to the course page.', 'vibe'),
						'options' => array(0 => __('Disable','vibe'),1 => __('Enable','vibe')),
						'std' => 0
						),   
						array(
						'id' => 'remove_woo_fields',
						'type' => 'button_set',
						'title' => __('Remove Extra Checkout Fields', 'vibe'), 
						'sub_desc' => __('Recommended if you\'re only selling courses/virtual products','vibe'),
						'desc' => __('Removes following fields in WooCommerce Checkout : Billing Company/Address,State/Town, Pincode, Phone etc.', 'vibe'),
						'options' => array(0 => __('No','vibe'),1 => __('Yes','vibe')),
						'std' => 0
						),   
						array(
							'id' => 'cache_duration',
							'type' => 'number',
							'title' => __('Cache Duration', 'vibe'), 
							'sub_desc' => __('in seconds (0 to disable)', 'vibe'),
							'desc' => __('Small cache duration could impact adversely. High for stable websites.', 'vibe'),
	                        'std' => '0'
						),	
						array(
						'id' => 'google_captcha_public_key',
						'type' => 'text',
						'title' => __('Google Captcha Public Key', 'vibe'), 
						'sub_desc' => __('Enter Google captcha public key', 'vibe').'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/google-captcha-public-and-private-keys/">'.__('How to get an Public Key ?','vibe').'</a>',
						'std' => ''
						),
						array(
						'id' => 'google_captcha_private_key',
						'type' => 'text',
						'title' => __('Google Captcha Private Key', 'vibe'), 
						'sub_desc' => __('Enter Google captcha private key', 'vibe').'<a href="hhttp://vibethemes.com/documentation/wplms/knowledge-base/google-captcha-public-and-private-keys/">'.__('How to get an Private Key ?','vibe').'</a>',
						'std' => ''
						),
						array(
						'id' => 'google_apikey_contact',
						'type' => 'text',
						'title' => __('Google Maps API Key', 'vibe'), 
						'sub_desc' => __('Google Maps require an API key to function', 'vibe').'<a href="http://vibethemes.com/documentation/wplms/knowledge-base/how-to-get-google-api-key-for-google-maps/">'.__('How to get an API Key ?','vibe').'</a>',
						'std' => ''
						),
                       array(
						'id' => 'contact_ll',
						'type' => 'text',
						'title' => __('Contact Page Latitude and Longitude values', 'vibe'), 
						'sub_desc' => __('Grab the latitude and Longitude values from .', 'vibe').'<a href="http://www.mapcoordinates.net/en">'.__('Link','vibe').'</a>',
						'std' => '43.730325,7.422155'
						),
                       array(
						'id' => 'contact_style',
						'type' => 'button_set',
						'title' => __('Contact Page Map Style', 'vibe'), 
						'sub_desc' => __('Select the map style on contact page.', 'vibe'),
						'desc' => __('Content area is the container in which all content is located.', 'vibe'),
						'options' => array('SATELLITE' => __('Satellite View','vibe'),'ROADMAP' => __('Road map','vibe')),
						'std' => 'SATELLITE'
						),
						array(
							'id' => 'map_zoom',
							'type' => 'text',
							'title' => __('Google Map Zoom Level', 'vibe'), 
							'sub_desc' => __('Enter the zoom level in Google maps', 'vibe'),
							'desc' => __('Zoom Levels 0 - 19', 'vibe'),
							'std' => 17
						), 
                        array(
							'id' => 'error404',
							'type' => 'pages_select',
							'title' => __('Select 404 Page', 'vibe'), 
							'sub_desc' => __('This page is shown when page not found on your site.', 'vibe'),
							'desc' => __('User redirected to this page when page not found.', 'vibe'),
						), 
						array(
							'id' => 'xmlrpc',
							'type' => 'button_set',
							'title' => __('Disable XMLRPC/RSD/WLWManifest', 'vibe'), 
							'sub_desc' => __('Remove security vulnerabilities', 'vibe'),
							'desc' => __('Removes vulnerabilities at expense of ability to login via remote apps.', 'vibe'),
							'options' => array('' => __('No','vibe'),1 => __('Yes','vibe')),
							'std' => ''
						),
						array(
							'id' => 'disable_versioning',
							'type' => 'button_set',
							'title' => __('Disable Versions in Scripts/Styles', 'vibe'), 
							'sub_desc' => __('Helps in caching of resources', 'vibe'),
							'desc' => __('Improves page speed score', 'vibe'),
							'options' => array('' => __('No','vibe'),1 => __('Yes','vibe')),
							'std' => ''
						),
						array(
							'id' => 'wp_login_screen',
							'type' => 'textarea',
							'title' => __('Custom CSS for WP Login Screen', 'vibe'), 
							'sub_desc' => __('Add custom CSS', 'vibe'),
							'desc' => __('Custom CSS for WP Login screen.', 'vibe'),
						),
						array(
							'id' => 'credits',
							'type' => 'text',
							'title' => __('Author & Credits', 'vibe'), 
							'sub_desc' => __('Credits and Author of the Website', 'vibe'),
							'desc' => __('Changes the reference to Author {VibeThemes}', 'vibe'),
	                        'std' => 'VibeThemes'
						),
                      )
                    );
$sections[] = array(
				'icon' => 'desktop',
				'title' => __('Demo Switcher', 'vibe'),
				'desc' => '<p class="description">'.__('Swicth between diffrent demos','vibe').'..</p>',
				'fields' => array(
                    
                       array(
						'id' => 'demo_switch',
						'type' => 'demo_switcher',
                        'options' => array(             
	                                        'default' => array('Default' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/default.jpg')),
	                                        'demo10' => array('Default' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/demo10.jpg')),
	                                        'demo9' => array('Default' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/demo9.jpg')),
											'demo1' => array('Demo1' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/demo1.jpg')),
											'demo2' => array('Default' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/demo2.jpg')),
											'demo3' => array('Default' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/demo3.jpg')),
											'demo4' => array('Default' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/demo4.jpg')),
											'demo5' => array('Default' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/demo5.jpg')),
											'demo6' => array('Default' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/demo6.jpg')),
											'demo7' => array('Default' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/demo7.jpg')),
											'demo8' => array('Default' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/demo8.jpg')),
											'oneinstructor' => array('One Instructor' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/oneinstructor.jpg')),
											'onecourse' => array('One Course' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/onecourse.jpg')),
											'points_system' => array('Points System' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/points_system.jpg')),
											'childone' => array('Points System' => __('Four Columns','vibe'), 'img' => esc_url(get_template_directory_uri() .'/setup/installer/images/childone.jpg')),
											

											
											),
	                    ),//Must provide key => value(array:title|img) pairs for radio options
				),
			);


	$tabs = array();
	
			
	if (function_exists('wp_get_theme')){
		$theme_data = wp_get_theme();
		$theme_uri = $theme_data->get('ThemeURI');
		$description = $theme_data->get('Description');
		$author = $theme_data->get('Author');
		$version = $theme_data->get('Version');
		$tags = $theme_data->get('Tags');
	}else{
		$theme_data = get_theme_data(trailingslashit(get_stylesheet_directory()).'style.css');
		$theme_uri = $theme_data['URI'];
		$description = $theme_data['Description'];
		$author = $theme_data['Author'];
		$version = $theme_data['Version'];
		$tags = $theme_data['Tags'];
	}	

	$theme_info = '<div class="vibe-opts-section-desc">';
	$theme_info .= '<p class="vibe-opts-theme-data description theme-uri"><strong>Theme URL:</strong> <a href="'.$theme_uri.'" target="_blank">'.$theme_uri.'</a></p>';
	$theme_info .= '<p class="vibe-opts-theme-data description theme-author"><strong>Author:</strong>'.$author.'</p>';
	$theme_info .= '<p class="vibe-opts-theme-data description theme-version"><strong>Version:</strong> '.$version.'</p>';
	$theme_info .= '<p class="vibe-opts-theme-data description theme-description">'.$description.'</p>';
	$theme_info .= '<p class="vibe-opts-theme-data description theme-tags"><strong>Tags:</strong> '.implode(', ', $tags).'</p>';
	$theme_info .= '</div>';



	$tabs['theme_info'] = array(
					'icon' => 'info-sign',
					'title' => __('Theme Information', 'vibe'),
					'content' => $theme_info
					);
	/*
	if(file_exists(trailingslashit(get_stylesheet_directory()).'README.html')){
		$tabs['theme_docs'] = array(
						'icon' => 'book',
						'title' => __('Documentation', 'vibe'),
						'content' => nl2br(file_get_contents(trailingslashit(get_stylesheet_directory()).'README.html'))
						);
	}*///if

	global $VIBE_Options;
	$sections = apply_filters('vibe_option_custom_sections',$sections);
	$VIBE_Options = new VIBE_Options($sections, $args, $tabs);
	wp_cache_delete('vibe_option','settings');
	
}//function
add_action('init', 'setup_framework_options', 0);

/*
 * 
 * Custom function for the callback referenced above
 *
 */
function my_custom_field($field, $value){
	print_r($field);
	print_r($value);

}//function

/*
 * 
 * Custom function for the callback validation referenced above
 *
 */
function validate_callback_function($field, $value, $existing_value){
	
	$error = false;
	$value =  'just testing';
	
	$return['value'] = $value;
	if($error == true){
		$return['error'] = $field;
	}
	return $return;
	
}//function


?>
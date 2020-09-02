<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WPLMS_Admin_Welcome {

	private $plugin;
	public $major_version = WPLMS_VERSION;
	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {

		// Bail if user cannot moderate
		if ( ! current_user_can( 'manage_options' ) )
			return;
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
		add_action('wp_ajax_wplms_demo_data_download_install_activate_plugin',array($this,'wplms_demo_data_download_install_activate_plugin'));

	}

	/**
	 * Add admin menus/screens
	 *
	 * @access public
	 * @return void
	 */
	public function admin_menus() {	

		$welcome_page_name  = __( 'Install WPLMS', 'vibe' );
		$welcome_page_title = __( 'Welcome to WPLMS', 'vibe' );
		if(!$this->check_installed()){
			$page = add_dashboard_page( $welcome_page_title, $welcome_page_name, 'manage_options', 'wplms-install', array( $this, 'install_screen' ) );
			add_action( 'admin_print_styles-'. $page, array( $this, 'admin_css' ) );
		}else{
			$about_page_name = __( 'About WPLMS', 'vibe' );
			add_dashboard_page( $welcome_page_title, $about_page_name, 'manage_options', 'wplms-about', array( $this, 'about_screen' ) );
		}
		if ( empty( $_GET['page'] ) ) {
			return;
		}

		$welcome_page_name  = __( 'About WPLMS', 'vibe' );
		$welcome_page_title = __( 'Welcome to WPLMS', 'vibe' );
		switch ( $_GET['page'] ) {
			case 'wplms-install' :
				$page = add_dashboard_page( 'Install WPLMS', 'Install WPLMS', 'manage_options', 'wplms-install', array( $this, 'install_screen' ) );
				add_action( 'admin_print_styles-'. $page, array( $this, 'admin_css' ) );
			break;
			case 'wplms-about' :
				$page = add_dashboard_page( $welcome_page_title, $welcome_page_name, 'manage_options', 'wplms-about', array( $this, 'about_screen' ) );
				add_action( 'admin_print_styles-'. $page, array( $this, 'admin_css' ) );
			break;
			case 'wplms-system' :
				$page = add_dashboard_page( $welcome_page_title, $welcome_page_name, 'manage_options', 'wplms-system', array( $this, 'system_screen' ) );
				add_action( 'admin_print_styles-'. $page, array( $this, 'admin_css' ) );
			break;
			case 'wplms-changelog' :
				$page = add_dashboard_page( $welcome_page_title, $welcome_page_name, 'manage_options', 'wplms-changelog', array( $this, 'changelog_screen' ) );
				add_action( 'admin_print_styles-'. $page, array( $this, 'admin_css' ) );
			break;
		}
	}

	/**
	 * admin_css function.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_css() {
		wp_enqueue_style( 'vibe-activation', VIBE_URL.'/assets/css/old_files/activation.css');
	}

	/**
	 * Add styles just for this page, and remove dashboard page links.
	 *
	 * @access public
	 * @return void
	 */
	public function admin_head() {
		if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'wplms-about'){
			remove_submenu_page( 'index.php', 'wplms-about' );		
		}
		
		remove_submenu_page( 'index.php', 'wplms-system' );
		remove_submenu_page( 'index.php', 'wplms-changelog' );

		?>
		<style type="text/css">
			/*<![CDATA[*/
			.wplms-wrap .wplms-badge {
				<?php echo is_rtl() ? 'left' : 'right'; ?>: 0;
			}
			.wplms-wrap .feature-rest div {
				float:<?php echo is_rtl() ? 'right':'left' ; ?>;
			}
			.wplms-wrap .feature-rest div.last-feature {
				padding-<?php echo is_rtl() ? 'right' : 'left'; ?>: 50px !important;
				padding-<?php echo is_rtl() ? 'left' : 'right'; ?>: 0;
			}
			.three-col > div{
				float:<?php echo is_rtl() ? 'right':'left' ; ?>;
			}
			/*]]>*/
		</style>
		<?php
	}

	/**
	 * Into text/links shown on all about pages.
	 *
	 * @access private
	 * @return void
	 */
	private function intro() {

		// Flush after upgrades
		if ( ! empty( $_GET['wplms-updated'] ) || ! empty( $_GET['wplms-installed'] ) )
			flush_rewrite_rules();
		?>
		<h1><?php printf( __( 'Welcome to WPLMS %s', 'vibe' ), $this->major_version ); ?></h1>

		<div class="about-text wplms-about-text">
			<?php
				if ( ! empty( $_GET['wplms-installed'] ) )
					$message = __( 'Thanks, all done!', 'vibe' );
				elseif ( ! empty( $_GET['wplms-updated'] ) )
					$message = __( 'Thank you for updating to the latest version!', 'vibe' );
				else
					$message = __( 'Thanks for installing!', 'vibe' );

				printf( __( '%s WPLMS is the best Learning Management platform for WordPress. The latest version %s now contains following features.', 'vibe' ), $message, $this->major_version );
			?>
		</div>

		<div class="wplms-badge"><img src="<?php echo 'https://0.s3.envato.com/files/80339740/themeforest_thumbnail.png'; ?>" /></div>

		<p class="wplms-actions">
			<a href="<?php echo admin_url('admin.php?page=wplms_options'); ?>" class="button button-primary"><?php _e( 'Settings', 'vibe' ); ?></a>
			<a href="<?php echo esc_url( 'http://vibethemes.com/documentation/wplms/article-categories/tips-tricks/'); ?>" class="docs button"><?php _e( 'FAQs', 'vibe' ); ?></a>
			<a href="<?php echo esc_url( 'http://vibethemes.com/envato/wplms/documentation/'); ?>" class="docs button"><?php _e( 'Docs', 'vibe' ); ?></a>
			<a href="<?php echo esc_url( 'http://vibethemes.com/documentation/wplms/knowledge-base/'); ?>" class="button"><?php _e( 'Customization Tips', 'vibe' ); ?></a>
			<a href="<?php echo esc_url( 'http://vibethemes.com/documentation/wplms/'); ?>" class="button"><?php _e( 'Support system', 'vibe' ); ?></a>
		</p>

		<h2 class="nav-tab-wrapper">
			<?php
				if(!$this->check_installed()){
					?>
					<a class="nav-tab <?php if ( $_GET['page'] == 'wplms-install' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wplms-install' ), 'index.php' ) ) ); ?>">
						<?php _e( "Installation and Setup", 'vibe' ); ?>
					</a>
					<?php
				}
			?>
			
			<a class="nav-tab <?php if ( $_GET['page'] == 'wplms-about' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wplms-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'vibe' ); ?>
			</a>
			<a class="nav-tab <?php if ( $_GET['page'] == 'wplms-system' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wplms-system' ), 'index.php' ) ) ); ?>">
			<?php
			 _e( 'System Status', 'vibe' ); 
				?>
			</a><a class="nav-tab <?php if ( $_GET['page'] == 'wplms-changelog' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wplms-changelog' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Changelog', 'vibe' ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Output the install screen.
	 */
	public function install_screen() {
		?>
		<div class="wrap wplms-wrap about-wrap">

			<?php $this->intro(); ?>

			<div class="changelog">
				<div class="wplms-feature feature-rest feature-section col two-col">
					<div class="col-1">
						<h4><?php _e( 'One Click installation and Setup', 'vibe' ); ?></h4>
						<p><?php _e( 'You can now install WPLMS in one single click, select the setup procedure from the options given in the right. You just need to Click the button once and everything would be automatically installed and setup, after which your WPLMS Site would be ready to use and configure.', 'vibe' ); ?></p>
					</div>
					<div class="col-2"><?php
						if ( in_array( 'wordpress-importer/wordpress-importer.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) || (function_exists('is_plugin_active_for_network') && is_plugin_active_for_network( 'wordpress-importer/wordpress-importer.php'))) {
							echo '<p style="padding:15px;background-color: #FFF6BF;border: 1px solid #ffd324;border-radius: 2px;color: #817134;">'.__('Please deactivate WordPress importer plugin to avoid conflicts with Vibe one click installer.','vibe').'</p>';
						}else{
							if (is_plugin_active('buddypress/bp-loader.php') && is_plugin_active('vibe-course-module/loader.php') && is_plugin_active('vibe-customtypes/vibe-customtypes.php')) { 
						?>
						<strong class="install_buttons">

							<a class="button button-primary button-hero sample_data_install" data-file="theme_data"><?php _e('Setup Theme without Sample Data','vibe'); ?></a>
							<span><?php _e('OR','vibe'); ?> </span>
							<?php
								$disabled=0;
								$plugin_flag=1;
								$one_click_plugins  = get_option('wplms_one_click_required_plugins');

								//allow plugin to handle plugins flag
								$plugin_flag=apply_filters('wplms_setup_plugins',$plugin_flag);

								if(function_exists('is_plugin_active') && $plugin_flag){
									
									if(is_plugin_active('LayerSlider/layerslider.php') && is_plugin_active('wplms-assignments/wplms-assignments.php') && is_plugin_active('wplms-front-end/wplms-front-end.php') && is_plugin_active('woocommerce/woocommerce.php') && is_plugin_active('revslider/revslider.php')){
										$plugin_flag=0;
									}

									if(!empty($one_click_plugins) && empty($plugin_flag)){
										
										if(is_plugin_active($one_click_plugins[0]['file']) && $one_click_plugins[0]['slug'] != 'default'){
											$plugin_flag=0;
										}else{
											$plugin_flag=1;
										}
									}
								}
								

								$demo_plugins = $this->get_demo_plugins();

								$theme = wp_get_theme();
								$name = $theme->get( 'Name' );
								if(in_array($name,array('WPLMS Blank ChildTheme','WPLMS'))){
									echo '<div style="text-align:center;width:100%;">Select Demo : <select id="one_click_installer_install_demo_select">';
									foreach($demo_plugins as $key=>$demo){
										if(function_exists('is_plugin_active')){
											if($one_click_plugins[0]['slug'] == $demo['slug']){
												echo '<option value="'.$key.'" selected>'.$demo['label'].'</option>';	
											}else{
												echo '<option value="'.$key.'">'.$demo['label'].'</option>';	
											}
										}else{
											echo '<option value="'.$key.'">'.$demo['label'].'</option>';	
										}
										
									}
									echo '</select></div>';
								}else{
									echo "<strong style='display:block'>Child Theme detected.</strong>";
								}

								
								
								if (!$plugin_flag) { 

									?>

									<a class="button button-primary button-hero sample_data_install with_data <?php echo (($disabled)?'disabled':''); ?>" data-file="sampledata"><?php _e('Setup Theme with Sample Data [for blank installs]','vibe'); ?><?php echo (($disabled)?'<span>'.__('Please enable all the plugins','vibe').'</span>':''); ?></a>
								<?php }else{
									echo '<p style="padding:15px;background-color: #FFF6BF;border: 1px solid #ffd324;border-radius: 2px;color: #817134;display: inline-block;">'.sprintf(__('Install all plugins for Full Sample Data import % slink %s','vibe'),'<a href="'.admin_url('themes.php?page=install-required-plugins').'">','</a>').'</p>';
								 }
							?>
							<a class="link" href="<?php echo admin_url( 'themes.php?page=wplms-setup' ); ?>" >&larr; Back to Theme Setup Wizard</a>
						</strong>
						<?php
						}else{
							echo '<p style="padding:15px;background-color: #FFF6BF;border: 1px solid #ffd324;border-radius: 2px;color: #817134;">'.__('Please activate all/required plugins bundled with the theme.','vibe').'</p><a href="'.admin_url('themes.php?page=install-required-plugins').'" class="button button-primary">'.__('Install & Activate plugins','vibe').'</a>';
						}
						echo '<span id="loading"><i class="sphere"></i></span>';
					}
					?>
					</div>
					<script>
						var demo_plugins = <?php if(!empty($demo_plugins)){echo json_encode($demo_plugins);}else{echo '[]';}?>;
						jQuery(document).ready(function($){
							
							$('#one_click_installer_install_demo_select').on('change',function(event){
								var value = $('#one_click_installer_install_demo_select').val();
								if(!demo_plugins[event.target.value].installed){
									$('.sample_data_install.with_data').addClass('disabled');

										$.ajax({
							              	type: "POST",
							              	url: ajaxurl,
							              	data: { 
							              			action: 'wplms_demo_data_download_install_activate_plugin', 
							              			plugin: demo_plugins[event.target.value],
							              			security:"<?php echo wp_create_nonce('wplms'); ?>"
						                        },
							              	cache: false,
							              	success: function (html) {
							              		location.reload();
							              	}
								      	});
								}
								
							});
						});
					</script>
				</div>
			</div>
			<div class="changelog about-integrations">
				<h3><?php _e( 'Video Tutorial of Installing theme', 'vibe' ); ?></h3>
				<iframe width="100%" height="480" src="http://www.youtube.com/embed/<?php echo apply_filters('wplms_one_click_setup_video','ygsyaLFZnhs');?>" frameborder="0" allowfullscreen></iframe>
			</div>
			<div class="changelog">
				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'Setup Issues', 'vibe' ); ?></h4>
						<p><?php _e( 'Facing issues while setting up? Try out suggested links below.', 'vibe' ); ?></p>
						<p><a href="http://vibethemes.com/envato/wplms/documentation/quick-installation-guide.html#pre-setup"><?php _e( 'WPLMS Pre-Setup settings', 'vibe' ); ?></a><br />
						<a href="http://vibethemes.com/envato/wplms/documentation/quick-installation-guide.html"><?php _e( 'WPLMS manual installation & setup', 'vibe' ); ?></a><br />
						<a href="<?php echo admin_url( 'index.php?page=wplms-system' ); ?>">
						<?php _e( 'WPLMS System Status', 'vibe' ); ?>
						</a><br /> 
						<a href="http://vibethemes.com/envato/wplms/documentation/quick-installation-guide.html#setup-issues"><?php _e( 'WPLMS FAQs', 'vibe' ); ?></a></p>
					</div>
					<div>
						<h4><?php _e( 'Popular Setup issues', 'vibe' ); ?></h4>
						<p><?php _e( 'Following is the list of most frequent setup issues faced by users', 'vibe' ); ?></p>
						<p><a href="http://vibethemes.com/documentation/wplms/knowledge-base/server-500-error-blank-white-page-after-activating-the-theme-or-one-of-its-plugins/"><?php _e( 'White screen when installed', 'vibe' ); ?></a><br />
						<a href="http://vibethemes.com/documentation/wplms/article-categories/faqs/"><?php _e( 'Course pages not opening', 'vibe' ); ?></a><br />
						<a href="http://vibethemes.com/documentation/wplms/article-categories/faqs/"><?php _e( 'Getting 404 pages', 'vibe' ); ?></a><br />
						<a href="http://vibethemes.com/documentation/wplms/article-categories/faqs/"><?php _e( 'Start Course/Take this Course not working', 'vibe' ); ?></a><br />
						</p>
					</div>
					<div class="last-feature">
						<h4><?php _e( 'Other popular issues', 'vibe' ); ?></h4>
						<p><?php _e( 'Following is the list of most frequent issues faced by users', 'vibe' ); ?></p>
						<p><a href="http://vibethemes.com/documentation/wplms/article-categories/faqs/"><?php _e( 'Quizzes not saving answers', 'vibe' ); ?></a><br />
						<a href="http://vibethemes.com/documentation/wplms/article-categories/faqs/"><?php _e( 'Visual composer not updating', 'vibe' ); ?></a><br />
						<a href="http://vibethemes.com/documentation/wplms/article-categories/faqs/"><?php _e( 'Getting 404 page on editing course', 'vibe' ); ?></a><br />
						<a href="http://vibethemes.com/documentation/wplms/article-categories/faqs/"><?php _e( 'Theme customizer not working', 'vibe' ); ?></a><br />
						</p>
					</div>
				</div>
			</div>
			<div class="return-to-dashboard">
				<a href="http://vibethemes.com/documentation/wplms/forums/"><?php _e( 'Unable to setup ? Get help on our Support forums.', 'vibe' ); ?></a>
			</div>
		</div>
		<?php
	}

	function get_demo_plugins(){
		return apply_filters('get_demo_plugins_array',array(
				'default'=>array(
					'label'=>'Default',
					'slug'=>'default',
					'download_link'=>'',
					'installed'=>0,
				),
				'demo1'=>array(
					'label'=>'Demo 1',
					'slug'=>'wplms_demo1',
					'file'=>'wplms_demo1/wplms_demo1.php',
					'download_link'=>'http://wplms.io/demos/demodata/plugins/wplms_demo1.zip',
					'installed'=>(file_exists(VIBE_PATH.'/../../plugins/wplms_demo1/')?(is_plugin_active('wplms_demo1/wplms_demo1.php')?1:0):0)
				),
				'demo2'=>array(
					'label'=>'Demo 2',
					'slug'=>'wplms_demo2',
					'file'=>'wplms_demo2/wplms_demo2.php',
					'download_link'=>'http://wplms.io/demos/demodata/plugins/wplms_demo2.zip',
					'installed'=>(file_exists(VIBE_PATH.'/../../plugins/wplms_demo2/')?(is_plugin_active('wplms_demo2/wplms_demo2.php')?1:0):0)
				),
				'demo3'=>array(
					'label'=>'Demo 3',
					'slug'=>'wplms_demo3',
					'file'=>'wplms_demo3/wplms_demo3.php',
					'download_link'=>'http://wplms.io/demos/demodata/plugins/wplms_demo3.zip',
					'installed'=>(file_exists(VIBE_PATH.'/../../plugins/wplms_demo3/')?(is_plugin_active('wplms_demo3/wplms_demo3.php')?1:0):0)
				),
				'demo4'=>array(
					'label'=>'Demo 4',
					'slug'=>'wplms_demo4',
					'file'=>'wplms_demo4/wplms_demo4.php',
					'download_link'=>'http://wplms.io/demos/demodata/plugins/wplms_demo4.zip',
					'installed'=>(file_exists(VIBE_PATH.'/../../plugins/wplms_demo4/')?(is_plugin_active('wplms_demo4/wplms_demo4.php')?1:0):0)
				),
				'demo5'=>array(
					'label'=>'Demo 5',
					'slug'=>'wplms_demo5',
					'file'=>'wplms_demo5/wplms_demo5.php',
					'download_link'=>'http://wplms.io/demos/demodata/plugins/wplms_demo5.zip',
					'installed'=>(file_exists(VIBE_PATH.'/../../plugins/wplms_demo5/')?(is_plugin_active('wplms_demo5/wplms_demo5.php')?1:0):0)
				),
				'demo6'=>array(
					'label'=>'Demo 6',
					'slug'=>'wplms_demo6',
					'file'=>'wplms_demo6/wplms_demo6.php',
					'download_link'=>'http://wplms.io/demos/demodata/plugins/wplms_demo6.zip',
					'installed'=>(file_exists(VIBE_PATH.'/../../plugins/wplms_demo6/')?(is_plugin_active('wplms_demo6/wplms_demo6.php')?1:0):0)
				),
				'demo7'=>array(
					'label'=>'Demo 7',
					'slug'=>'wplms_demo7',
					'file'=>'wplms_demo7/wplms_demo7.php',
					'download_link'=>'http://wplms.io/demos/demodata/plugins/wplms_demo7.zip',
					'installed'=>(file_exists(VIBE_PATH.'/../../plugins/wplms_demo7/')?(is_plugin_active('wplms_demo7/wplms_demo7.php')?1:0):0)
				),
				'demo8'=>array(
					'label'=>'Demo 8',
					'slug'=>'wplms_demo8',
					'file'=>'wplms_demo8/wplms_demo8.php',
					'download_link'=>'http://wplms.io/demos/demodata/plugins/wplms_demo8.zip',
					'installed'=>(file_exists(VIBE_PATH.'/../../plugins/wplms_demo8/')?(is_plugin_active('wplms_demo8/wplms_demo8.php')?1:0):0)
				),
				'demo9'=>array(
					'label'=>'Demo 9',
					'slug'=>'wplms_demo9',
					'file'=>'wplms_demo9/wplms_demo9.php',
					'download_link'=>'http://wplms.io/demos/demodata/plugins/wplms_demo9.zip',
					'installed'=>(file_exists(VIBE_PATH.'/../../plugins/wplms_demo9/')?(is_plugin_active('wplms_demo9/wplms_demo9.php')?1:0):0)
				),
				'demo10'=>array(
					'label'=>'Demo 10',
					'slug'=>'wplms_demo10',
					'file'=>'wplms_demo10/wplms_demo10.php',
					'download_link'=>'http://wplms.io/demos/demodata/plugins/wplms_demo10.zip',
					'installed'=>(file_exists(VIBE_PATH.'/../../plugins/wplms_demo10/')?(is_plugin_active('wplms_demo10/wplms_demo10.php')?1:0):0)
				),
			));
	}

	function wplms_demo_data_download_install_activate_plugin(){

		
		if(!current_user_can('manage_options')){
			echo 'Unable to install sample data';
			die();
		}
		
		$plugin=$_POST['plugin'];
		

		$demo_plugins =$this->get_demo_plugins();

		foreach ($demo_plugins as $key => $value) {
			if(function_exists('deactivate_plugins')){
				print_r('Deactivating plugins ->'+$value['slug']);
				deactivate_plugins($value['file']);
			}
		}
		

		if($plugin['slug'] != 'default'){
			$plugins = array(
				array(
		            'name'                  => $plugin['label'], // The plugin name
		            'slug'                  => $plugin['slug'], // The plugin slug (typically the folder name)
		            'source'                => $plugin['download_link'], // The plugin source
		            'external_url'          => $plugin['download_link'], // If set, overrides default API URL and points to an external URL
		            'required'				=>true,
		            'file'                  => $plugin['file'],
		        ),
			);

			update_option('wplms_one_click_required_plugins',$plugins);
		}else{
			update_option('wplms_one_click_required_plugins',array());
		}
		
		
		die();
	}

	
	/**
	 * Output the about screen.
	 */
	public function about_screen() {
		?>
		<div class="wrap wplms-wrap about-wrap">

			<?php $this->intro(); ?>

			<div class="changelog">
				<div class="wplms-feature feature-rest feature-section col two-col">
					<div class="col-1">
						<h4>Features & Maintenance Update</h4>
						<p>Maintenance update with minor bug fixes and improvements.</p>
						<a href="https://wplms.io/support/knowledge-base/3-8/" class="button"><?php _e( 'Check update log ', 'vibe' ); ?></a>
					</div>
					<div class="col-2">
						<h4>Wplms Push Notifications</h4>
						<p>An addon to send live push notification to your users .</p>
						<a href="https://wplms.io/downloads/wplms-push-notifications/" class="button"><?php _e( 'Check', 'vibe' ); ?></a>
					</div>
				</div>
			</div>
			<div class="changelog about-integrations">
				<h3>What's new in WPLMS<span style="float:right;"><a href="https://www.youtube.com/playlist?list=PL8n4TGA_rwD_5jqsgXIxXOk1H6ar-SVCV" class="button button-primary" target="_blank"><?php _e('WPLMS Video Playlist','vibe'); ?></a></span></h3>
				
				<div class="wplms-feature feature-section col three-col">
					<div>
						<h4>Course video</h4>
						<p style="max-height: 320px; overflow: hidden;">
							<img src="<?php echo VIBE_URL.'/setup/data/uploads/new/course_video.gif' ?>" alt="new header in WPLMS">
						</p>
						<a href="https://wplms.io/support/knowledge-base/course-video/" class="button" target="_blank">Tutorial</a>
					</div>

					<div>
						<h4>Guest User</h4>
						<p style="max-height: 320px; overflow: hidden;">
							<img src="https://i1.wp.com/wplms.io/support/wp-content/uploads/2018/11/c07198e9c7ec716060c694631c285a23.gif" alt="category based search">
						</p>
						<a href="https://wplms.io/support/knowledge-base/guest-user/" class="button" target="_blank">Tutorial</a>
						
					</div>

					<div>
						<h4>Direct links to course units from curriculum</h4>
						<p style="max-height: 320px; overflow: hidden;">
							<img src="<?php echo VIBE_URL.'/setup/data/uploads/new/direct-unit-link.png' ?>" alt="improved leaderboards">
						</p>
						<a href="https://www.youtube.com/watch?v=IPHqByxQF0I&feature=youtu.be" class="button" target="_blank">Video</a>
					</div>
					
					
				</div>
				<div class="wplms-feature feature-section col three-col">
					<div>
						<h4>Wplms push notifications </h4>
						<p style="max-height: 320px; overflow: hidden;">
							<img src="<?php echo VIBE_URL.'/setup/data/uploads/new/push_notifications.png' ?>" alt="push notifications">
						</p>
						<a href="https://wplms.io/downloads/wplms-push-notifications/" class="button-primary" target="_blank">Buy Now</a>
						<a href="https://wplms.io/support/knowledge-base/wplms-push-notifications/" class="button" target="_blank">Tutorial</a>
						
					</div>

					<div>
						<h4>Ajax Header reload</h4>
						<p style="max-height: 320px; overflow: hidden;">
							Page Caching enabled ? No problem, enable Ajax header re-load in WP Admin - WPLMS - Header. The login verification happens after page load and your users will show logged in all the time.
						</p>
						<a href="https://wplms.io/support/knowledge-base/ajax-header-re-load/" class="button" target="_blank">Tutorial</a>
						
					</div>

					<div>
						<h4>New Course Template 6</h4>
						<p style="max-height: 320px; overflow: hidden;">
							<img src="<?php echo VIBE_URL.'/setup/data/uploads/new/course_layout6.png' ?>" alt="category based search">
						</p>
						
					</div>
				</div>
				
			</div>
			<div class="changelog">
				<div class="feature-section col three-col">
					<div>
						<h4>WPLMS Push Notifications is Now Avaialble</h4>
						<p>WPLMS Chat is now live.</p>
						<a href="https://wplms.io/downloads/wplms-chat/" class="button" target="_blank">Download link</a>
					</div>

					<div>
						<h4>WPLMS Chat is Now Avaialble</h4>
						<p>WPLMS Chat is now live.</p>
						<a href="https://wplms.io/downloads/wplms-chat/" class="button" target="_blank">Download link</a>
					</div>
					
					<div class="last-feature">
						<h4>Migrate From Learnpress, Sensei, LearnDash, CleverCourse, WP Courseware,Academy theme to WPLMS.</h4>
						<p>Migrate all courses, units, sections, quizzes and content from different education plugins to WPLMS in 1 single click.</p>
						<a href="<?php echo admin_url('admin.php?page=lms-settings&tab=addons'); ?>" class="button">Tutorial</a>
					</div>
				</div>
				<div class="feature-section col three-col">
					
					<div>
						<h4>H5p Integration</h4>
						<p>Now embed H5p content in wplms units,quizzes and intgrate h5p marking to wplms .</p>
						<a href="https://wordpress.org/plugins/wplms-h5p-plugin/"><?php _e( 'Check documentation','vibe'); ?></a>
					</div>
					<div>
						<h4><?php _e( 'Updated Documentation ', 'vibe' ); ?></h4>
						<p><?php _e( 'Documentation has been updated. We\'ve added new functions & shortcodes list and developer documentation. For most updated documentation we recommend checking the online documentation doc.', 'vibe' ); ?></p>
						<a href="http://vibethemes.com/envato/wplms/documentation/"><?php _e( 'Check documentation','vibe'); ?></a>
					</div>
					<div class="last-feature">
						<h4><?php _e( 'Translation Collaboration', 'vibe' ); ?></h4>
						<p><?php _e( 'It is really difficult to maintain translations in a versatible project as WPLMS. Therefore we ask our users to email us translation files at vibethemes@gmail.com with subject "WPLMS - Translation Files".', 'vibe' ); ?></p>
						<a href="http://vibethemes.com/documentation/wplms/knowledge-base/2-5/"><?php _e( 'Check Translation files status','vibe'); ?></a>
					</div>
				</div>
			</div>
			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wplms_options' ), 'admin.php' ) ) ); ?>"><?php _e( 'Go to WPLMS Options panel', 'vibe' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Output the system.
	 */
	public function system_screen() {
		?>
		<div class="wrap wplms-wrap about-wrap">

			<?php $this->intro(); ?>
			<table class="wplms_status_table widefat" cellspacing="0" id="status">
				<thead>
					<tr>
						<th colspan="2"><h4><?php _e( 'Environment', 'vibe' ); ?></h4></th>
					</tr>
				</thead>

				<tbody>
					<tr>
						<td><?php _e( 'Home URL', 'vibe' ); ?>:</td>
						<td><?php echo home_url(); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Site URL', 'vibe' ); ?>:</td>
						<td><?php echo site_url(); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Version', 'vibe' ); ?>:</td>
						<td><?php bloginfo('version'); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Multisite Enabled', 'vibe' ); ?>:</td>
						<td><?php if ( is_multisite() ) echo __( 'Yes', 'vibe' ); else echo __( 'No', 'vibe' ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Web Server Info', 'vibe' ); ?>:</td>
						<td><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'PHP Version', 'vibe' ); ?>:</td>
						<td><?php if ( function_exists( 'phpversion' ) ) echo esc_html( phpversion() ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'MySQL Version', 'vibe' ); ?>:</td>
						<td>
							<?php
							/** @global wpdb $wpdb */
							global $wpdb;
							echo $wpdb->db_version();
							?>
						</td>
					</tr>
					<tr>
						<td><?php _e( 'WP Active Plugins', 'vibe' ); ?>:</td>
						<td><?php echo count( (array) get_option( 'active_plugins' ) ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Memory Limit', 'vibe' ); ?>:</td>
						<td><?php
							$memory = $this->wplms_let_to_num( WP_MEMORY_LIMIT );
							if ( $memory < 134217728 ) {
								echo '<mark class="error">' . sprintf( __( '%s - We recommend setting memory to at least 128MB. See: <a href="%s">Increasing memory allocated to PHP</a>', 'vibe' ), size_format( $memory ), 'http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP' ) . '</mark>';
							} else {
								echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
							}
						?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Debug Mode', 'vibe' ); ?>:</td>
						<td><?php if ( defined('WP_DEBUG') && WP_DEBUG ) echo '<mark class="yes">' . __( 'Yes', 'vibe' ) . '</mark>'; else echo '<mark class="no">' . __( 'No', 'vibe' ) . '</mark>'; ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Language', 'vibe' ); ?>:</td>
						<td><?php echo get_locale(); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'WP Max Upload Size', 'vibe' ); ?>:</td>
						<td><?php echo size_format( wp_max_upload_size() ); ?></td>
					</tr>
					<?php if ( function_exists( 'ini_get' ) ) : ?>
						<tr>
							<td><?php _e('PHP Post Max Size', 'vibe' ); ?>:</td>
							<td><?php echo size_format($this->wplms_let_to_num( ini_get('post_max_size') ) ); ?></td>
						</tr>
						<tr>
							<td><?php _e('PHP Time Limit', 'vibe' ); ?>:</td>
							<td><?php echo ini_get('max_execution_time'); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'PHP Max Input Vars', 'vibe' ); ?>:</td>
							<td><?php echo ini_get('max_input_vars'); ?></td>
						</tr>
						<tr>
							<td><?php _e( 'SUHOSIN Installed', 'vibe' ); ?>:</td>
							<td><?php echo extension_loaded( 'suhosin' ) ? __( 'Yes', 'vibe' ) : __( 'No', 'vibe' ); ?></td>
						</tr>
					<?php endif; ?>
					<tr>
						<td><?php _e( 'Default Timezone', 'vibe' ); ?>:</td>
						<td><?php
							$default_timezone = date_default_timezone_get();
							if ( 'UTC' !== $default_timezone ) {
								echo '<mark class="error">' . sprintf( __( 'Default timezone is %s - it should be UTC', 'vibe' ), $default_timezone ) . '</mark>';
							} else {
								echo '<mark class="yes">' . sprintf( __( 'Default timezone is %s', 'vibe' ), $default_timezone ) . '</mark>';
							} ?>
						</td>
					</tr>
				</tbody>


				<thead>
					<tr>
						<th colspan="2"><h4><?php _e( 'Settings', 'vibe' ); ?></h4></th>
					</tr>
				</thead>

				<thead>
					<tr>
						<th colspan="2"><?php _e( 'WPLMS Pages', 'vibe' ); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php
						$check_pages = array(
							_x( 'All Course page', 'Page setting', 'vibe' ) => array(
									'option' => 'bp-pages.course'
								),
							_x( 'Take this course page', 'Page setting', 'vibe' ) => array(
									'option' => 'take_course_page',
									'shortcode' => '[' . apply_filters( 'vibe_cart_shortcode_tag', 'vibe_cart' ) . ']'
								),
							_x( 'Create Course Page', 'Page setting', 'vibe' ) => array(
									'option' => 'create_course',
								),
							_x( 'Notes & Discussion Page', 'Page setting', 'vibe' ) => array(
									'option' => 'unit_comments',
								),
							_x( 'Default Certificate Page', 'Page setting', 'vibe' ) => array(
									'option' => 'certificate_page',
								)
						);

						$alt = 1;

						foreach ( $check_pages as $page_name => $values ) {

							if ( $alt == 1 ) echo '<tr>'; else echo '<tr>';

							echo '<td>' . esc_html( $page_name ) . ':</td><td>';

							$error = false;

							switch($values['option']){
								case 'bp-pages.course':
									$pages=get_option('bp-pages');
									if(isset($pages) && is_array($pages) && isset($pages['course']))
										$page_id=$pages['course'];
								break;
								default:
									$page_id = vibe_get_option($values['option']);
								break;
							}
							// Page ID check
							if ( ! isset($page_id ) ){
								echo '<mark class="error">' . __( 'Page not set', 'vibe' ) . '</mark>';
								$error = true;
							} else {
								$error = false;
							}

							if ( ! $error ) echo '<mark class="yes">#' . absint( $page_id ) . ' - ' . str_replace( home_url(), '', get_permalink( $page_id ) ) . '</mark>';

							echo '</td></tr>';

							$alt = $alt * -1;
						}
					?>
				</tbody>

				<thead>
					<tr>
						<th colspan="2"><h4><?php _e( 'Templates', 'vibe' ); ?></h4></th>
					</tr>
				</thead>

				<tbody>
					<?php

						$template_paths = apply_filters( 'bp_course_load_template_filter', array( 'vibe' => BP_COURSE_MOD_PLUGIN_DIR . '/includes/templates/' ) );
						$scanned_files  = array();
						$found_files    = array();

						foreach ( $template_paths as $plugin_name => $template_path ) {
							$scanned_files[ $plugin_name ] = $this->scan_template_files( $template_path );
						}

						foreach ( $scanned_files as $plugin_name => $files ) {
							foreach ( $files as $file ) {
								if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
									$theme_file = get_stylesheet_directory() . '/' . $file;
								} elseif ( file_exists( get_stylesheet_directory() . '/vibe/' . $file ) ) {
									$theme_file = get_stylesheet_directory() . '/vibe/' . $file;
								} elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
									$theme_file = get_template_directory() . '/' . $file;
								} elseif( file_exists( get_template_directory() . '/vibe/' . $file ) ) {
									$theme_file = get_template_directory() . '/vibe/' . $file;
								} else {
									$theme_file = false;
								}

								if ( $theme_file ) {
									$core_version  = $this->get_file_version( BP_COURSE_MOD_PLUGIN_DIR . '/includes/templates/' . $file );
									$theme_version = $this->get_file_version( $theme_file );

									if ( $core_version && ( empty( $theme_version ) || version_compare( $theme_version, $core_version, '<' ) ) ) {
										$found_files[ $plugin_name ][] = sprintf( __( '<code>%s</code> version <strong style="color:red">%s</strong> is out of date. The core version is %s', 'vibe' ), str_replace( WP_CONTENT_DIR . '/themes/', '', $theme_file ), $theme_version ? $theme_version : '-', $core_version );
									} else {
										$found_files[ $plugin_name ][] = sprintf( '<code>%s</code>'.$core_version.' - '.$theme_version, str_replace( WP_CONTENT_DIR . '/themes/', '', $theme_file ) );
									}
								}
							}
						}

						if ( $found_files ) {
							foreach ( $found_files as $plugin_name => $found_plugin_files ) {
								?>
								<tr>
									<td><?php _e( 'Template Overrides', 'vibe' ); ?> (<?php echo $plugin_name; ?>):</td>
									<td><?php echo implode( ', <br/>', $found_plugin_files ); ?></td>
								</tr>
								<?php
							}
						} else {
							?>
							<tr>
								<td><?php _e( 'Template Overrides', 'vibe' ); ?>:</td>
								<td><?php _e( 'No overrides present in theme.', 'vibe' ); ?></td>
							</tr>
							<?php
						}
					?>
				</tbody>

			</table>
		</div>
		<?php
	}

	/**
	 * Output the changelog screen
	 */
	public function changelog_screen() {
		?>
		<div class="wrap wplms-wrap about-wrap">

			<?php $this->intro(); ?>
			<div class="changelog-description">
			<p><?php printf( __( 'Full Changelog of WPLMS Theme', 'vibe' ), 'vibe' ); ?></p>

			<?php
				$file = VIBE_PATH.'/changelog.txt';
				$myfile = fopen($file, "r") or die("Unable to open file!".$file);
				while(!feof($myfile)) {
					$string = fgets($myfile);
					if(strpos($string, '* version') === 0){
						echo '<br />---------------------- * * * ----------------------<br /><br />';
					}
				  echo $string . "<br>";
				}
				fclose($myfile);
			?>
			</div>
		</div>
		<?php
	}

	function scan_template_files( $template_path ) {
		
		$files         = scandir( $template_path );
		$result        = array();
		if ( $files ) {
			foreach ( $files as $key => $value ) {
				if ( ! in_array( $value, array( ".",".." ) ) ) {
					if ( is_dir( $template_path . DIRECTORY_SEPARATOR . $value ) ) {
						$sub_files = self::scan_template_files( $template_path . DIRECTORY_SEPARATOR . $value );
						foreach ( $sub_files as $sub_file ) {
							$result[] = $value . DIRECTORY_SEPARATOR . $sub_file;
						}
					} else {
						$result[] = $value;
					}
				}
			}
		}
		return $result;
	}
	function get_file_version( $file ) {
		// We don't need to write to the file, so just open for reading.
		$fp = fopen( $file, 'r' );

		// Pull only the first 8kiB of the file in.
		$file_data = fread( $fp, 8192 );

		// PHP will close file handle, but we are good citizens.
		fclose( $fp );

		// Make sure we catch CR-only line endings.
		$file_data = str_replace( "\r", "\n", $file_data );
		$version   = '';

		if ( preg_match( '/^[ \t\/*#@]*' . preg_quote( '@version', '/' ) . '(.*)$/mi', $file_data, $match ) && $match[1] )
			$version = _cleanup_header_comment( $match[1] );

		return $version ;
	} 
	/**
	 * Sends user to the welcome page on first activation
	 */
	public function welcome() {
		// Bail if no activation redirect transient is set
	    if ( ! get_transient( '_wplms_activation_redirect' ) ) {
			return;
	    }

		// Delete the redirect transient
		delete_transient( '_wplms_activation_redirect' );
		// Bail if activating from network, or bulk, or within an iFrame
		if ( is_network_admin() || defined( 'IFRAME_REQUEST' ) ) {
			return;
		}

		if(!$this->check_installed()){
			wp_redirect( admin_url( 'themes.php?page=wplms-setup' ) );
		}else{
			
			wp_redirect( admin_url( 'index.php?page=wplms-about' ) );
				
		}
		exit;
	}
	function wplms_let_to_num( $size ) {
		$l   = substr( $size, -1 );
		$ret = substr( $size, 0, -1 );
		switch ( strtoupper( $l ) ) {
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;
		}
		return $ret;
	}
	function check_installed(){
		$check_options_panel = get_option(THEME_SHORT_NAME);
		if(!isset($check_options_panel) || empty($check_options_panel))
			return false;

		$take_course_page = vibe_get_option('take_course_page');
		if(!isset($take_course_page) || !is_numeric($take_course_page) || empty($take_course_page))
			return false;

		return true;
	}
}

add_action('init','wplms_welcome_user');
function wplms_welcome_user(){
	new WPLMS_Admin_Welcome();	
}

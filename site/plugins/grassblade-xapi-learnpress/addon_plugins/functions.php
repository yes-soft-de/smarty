<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'WP_Plugin_Install_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-plugin-install-list-table.php' );
}

class grassblade_addons extends WP_Plugin_Install_List_Table {
		public $premium_plugins = null;
		public $last_checked = "";
		public $installed_plugins = array();

	function __construct() {
		add_action( 'admin_menu', array($this,'addon_plugins_menu'), 12);

		if(is_admin() && !empty($_GET["page"]) && $_GET["page"] == "grassblade-addons")
			$this->addons_page_run();
	}
	function addons_page_run() {
		add_filter("learn-press/admin-default-scripts", '__return_false', 100);

		if(empty($_GET["force-check"]))
			add_filter("pre_http_request", array($this, "block_requests"), 10, 3);
	}

	/**
	 *
	 * Add Addon Plugins to the menu.
	 *
	 */

	function addon_plugins_menu() {
		global $submenu;
		if(empty( $submenu[ "grassblade-lrs-settings" ] ) || !in_array( 'grassblade-addons', wp_list_pluck( $submenu[ "grassblade-lrs-settings" ], 2)) )
		add_submenu_page("grassblade-lrs-settings", __("Add-ons", "grassblade"), __("Add-ons", "grassblade"),'manage_options','grassblade-addons', array($this, 'addon_plugins_menupage') );
	}
	function full_plugin_path($plugin_path = "") {

		$plugin_basename = array(
			str_replace('/', '\\', plugin_basename(__FILE__)),
			str_replace('\\', '/', plugin_basename(__FILE__)),
		);

		$plugin_file_path = str_replace($plugin_basename, array("",""), realpath(plugin_dir_path(__FILE__).basename(__FILE__)));
		$full_plugin_path = realpath($plugin_file_path.$plugin_path);

		if(!file_exists($full_plugin_path))
			return '';
		else
			return $full_plugin_path;
	}
	function add_version( $action_links, $plugin) {

		if(!empty($plugin["plugin"])) 
			$plugin_path = $plugin["plugin"];
		else if(!empty($plugin["slug"]))
			$plugin_path = $this->installed_plugin_path($plugin["slug"]);


		if(!empty($plugin_path))
		{
			$full_plugin_path = $this->full_plugin_path( $plugin_path );

			if(empty($full_plugin_path))
				return $action_links;
			
			$plugin_data = get_plugin_data( $full_plugin_path );

			if(!empty($plugin_data) && !empty($plugin_data['Version']))
			$action_links[] = "<small class='current_version' title='".__("Installed Version", "grassblade")."'>v".$plugin_data['Version']."</small>";
		}

		if(!empty($plugin["version"]))
			$action_links[] = "<small class='new_version'>v".$plugin['version']."</small>";


		return $action_links;
	}
	function installed_plugin_path($slug) {
		
		if(empty($this->installed_plugins))
			return "";

		foreach ($this->installed_plugins as $plugin) {
			if($plugin->slug == $slug && !empty($plugin->plugin)) {
				return $plugin->plugin;
			}
		}
		return "";
	}
	function action_links( $action_links, $plugin) {
		if(empty($action_links) || (count($action_links) == 1) && strpos($action_links[0], "tab=plugin-information")) { //No action links or only More Details link.
			array_unshift($action_links, $this->button_install_active_activate($plugin));
		}

	 	if(!empty($action_links))
		foreach ($action_links as $key => $value) {
			if(strpos($value, "action=install-plugin"))
			{
				$action_links[$key] = $this->button_install_active_activate($plugin);
			}

			if(strpos($value, "tab=plugin-information"))
			{
				$link = !empty($plugin["more_details"])? $plugin["more_details"]:$plugin["product_url"];
				$action_links[$key] = '<a href="'.$link.'" class="thickbox open-plugin-details-modal"  target="_blank">'.__("More Details").'</a>';
			}
		}

		return $action_links;
	}
	function button_install_active_activate($plugin) {

		if(!empty($plugin["plugin"]))
		{
			$full_plugin_path = $this->full_plugin_path($plugin["plugin"]);

			if(!empty($full_plugin_path))
			{
				if(is_plugin_active($plugin["plugin"]))
					$status = "active";
				else
					$status = "activate";
			}
			else
				$status = "install-now";
		}
		else
			$status = "install-now";


		if($plugin["slug"] == "grassblade_lrs") {
			if(function_exists("grassblade_settings")) {
				$grassblade_settings = grassblade_settings();
				$endpoint = $grassblade_settings["endpoint"];
			}
			else
				$endpoint = get_option("grassblade_tincan_endpoint");

			if(!empty($endpoint) && (strpos($endpoint, "gblrs.com/") !== false || strpos($endpoint, "/grassblade-lrs/")))
				$status = "active";
		}

		switch ($status) {
			case 'active':
				return '<button type="button" class="button button-disabled" disabled="disabled">'.__("Active").'</button>';
				break;	
			case 'activate':
					$activation_link = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $plugin["plugin"] ), 'activate-plugin_' . $plugin["plugin"] );
					return '<a href="'.$activation_link.'" class="button activate-now" aria-label="'.$plugin["name"].'">'.__("Activate").'</a>';
				break;
			default:
				return '<a class="install-now button" href="'.$plugin["product_url"].'" target="_blank">'.__("Install Now").'</a>';
				break;
		}

	}
	function block_requests($pre, $r, $url) {

		if(strpos($url, "nextsoftwaresolutions.com") || strpos($url, "api.wordpress.org/"))
			return false; //Allow requests from nextsoftwaresolutions and to api.wordpress.org

		return new WP_Error( 'http_request_failed', __( 'GrassBlade has blocked requests through HTTP.' ) );
	}
	function get_plugin($type, $slug) {
		if(empty($this->premium_plugins[$type]))
			return array();

		foreach ($premium_plugins as $value) {
			if(!empty($value["slug"]) && $value["slug"] == $slug)
				return $value;
		}
		return array();
	}
	function addon_plugins_menupage() {
	
		//must check that the user has the required capability 
	    if (!current_user_can('manage_options'))
	    {
	      wp_die( __('You do not have sufficient permissions to access this page.') );
	    }
		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		add_filter( 'plugin_install_action_links', array($this, "action_links"), 10, 2 );
		add_filter( 'plugin_install_action_links', array($this, "add_version"), 12, 2 );
		$this->grassblade_premium_plugins();

	    ?>
	    <style type="text/css">
	    	.premium_plugins .num-ratings, .lms_plugins .num-ratings {
	    		display: none;
	    	}
	    	.premium_plugins .column-downloaded, .lms_plugins .column-downloaded {
	    		display: none;
	    	}
	    	.wrap {
	    		clear: both;
	    	}
	    	.button.check-again {
	    		vertical-align: middle;
	    	}
	    	.current_version {
	    		background: green;
			    padding: 4px 8px;
			    border-radius: 5px;
			    color: white;
			    font-weight: 400;
	    	}
	    	#wpbody-content .error {
	    		display: none;
	    	}
	    	.gb_spinner {
	    		background: url(<?php echo plugins_url( 'spinner.gif', __FILE__); ?>);
	    		width: 20px;
			    height: 20px;
			    display: inline-block;
			    text-align: center;
			    vertical-align: middle;
			    margin-right: 5px;
	    	}
	    	small.new_version {
				background: #f7831d;
				color: white;
				font-size: 0.7em;
				padding: 2px 5px;
				border-radius: 4px;
			}
	    </style>
	    <script type="text/javascript">
			jQuery(document).ready(function() { 
				jQuery(".wp-list-table.widefat.premium_plugins .name a, .wp-list-table.widefat.lms_plugins .name a").each(function(i,v){
					jQuery(this).attr("href", jQuery(this).closest(".plugin-card").find(".authors a").attr("href") );
					jQuery(this).attr("target", "_blank");
				});
				
				jQuery(".plugin-card").each(function(i, v) {
				   jQuery(v).find(".new_version").appendTo(jQuery(v).find(".name h3"));
				});

				jQuery(".activate-now, .free_plugins .install-now, .update-now").on("click", function(e) {
					e.preventDefault();
					grassblade_activate(this);
					return false;
				});
				
				function grassblade_activate(context) {
					jQuery(context).addClass("disabled");
					jQuery(context).prepend("<i class='gb_spinner'></i>");

					jQuery.get(jQuery(context).attr("href"), function(data) {
						window.location.reload();
					});
				}
			});
	    </script>
	    <div>
	    <div class="wrap">
			<h2>
				<img style="top: 6px; position: relative;" src="<?php echo plugins_url('img/icon_30x30.png', (dirname(__FILE__))); ?>"/>
				<?php _e("GrassBlade Add-ons", "grassblade"); ?>
			</h2>
			<br>
		</div>
		<div class="wrap <?php echo basename(dirname(__FILE__, 2)); ?>">
		    <span class="last_checked"><?php $time = $this->last_checked + get_option( 'gmt_offset' ) * HOUR_IN_SECONDS; printf( __( 'Last checked on %1$s at %2$s.' ), date_i18n( __( 'F j, Y' ), $time ), date_i18n( __( 'g:i a' ), $time ) ); ?></span> <a class="button check-again" href="<?php echo admin_url("admin.php?page=grassblade-addons&force-check=1"); ?>"><?php _e("Check Again"); ?></a>
			<h3><?php _e("Premium Add-ons", "grassblade"); ?></h3>
			<div  class="wp-list-table widefat premium_plugins">
				<div id="the-list">
					<?php 
						$this->items = $this->grassblade_premium_plugins("premium"); 
						parent::display_rows(); 
					?>
				</div>
			</div>
		</div>
		<div class="wrap">
		<br>
			<h3><?php _e("LMS Integrations ", "grassblade"); ?></h3>
			<div style="position: relative; top: -15px"><?php __("(You need only ONE of these)", "grassblade"); ?></div>

			<div  class="wp-list-table widefat lms_plugins">
				<div id="the-list">
					<?php 
						$this->items = $this->grassblade_premium_plugins("lms"); 
						parent::display_rows(); 
					?>
				</div>
			</div>
		</div>
		<?php
		remove_filter( 'plugin_install_action_links', array($this, "action_links"), 10, 2 );
		?>
	    <div class="wrap">
		<br>

			<h3><?php _e("Free Add-ons", "grassblade"); ?></h3>
			<div  class="wp-list-table widefat free_plugins">
				<div id="the-list">
					<?php 
						$this->get_grassblade_addon_plugins();
						parent::display_rows(); 
					?>
				</div>
			</div>
			
		</div>
	    <?php
	} 

	function get_grassblade_addon_plugins(){

		include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );

		$paged = $this->get_pagenum();

		if(!empty($this->installed_plugins))
		$installed_plugins = $this->installed_plugins;
		else
		$installed_plugins = $this->installed_plugins = $this->get_installed_plugins();

		$grassblade_free_addons = get_option("grassblade_free_addons");

		if(!empty($_GET["force-check"]) || empty($grassblade_free_addons) || !is_array($grassblade_free_addons["addons"]) || empty($grassblade_free_addons["time"]) || $grassblade_free_addons["time"] < time() - 86400) {

			$args = array(
				'page' => $paged,
				'per_page' => 30,
				'fields' => array(
					'last_updated' => true,
					'icons' => true,
					'active_installs' => true,
				),

				// Send the locale and installed plugin slugs to the API so it can provide context-sensitive results.
				'locale' => get_user_locale(),
				'installed_plugins' => array_keys( $installed_plugins ),
			);

			$args['author'] = sanitize_title_with_dashes( 'liveaspankaj' );

			$api = plugins_api( 'query_plugins', $args );

			if ( is_wp_error( $api ) ) {
				$this->error = $api;
				return;
			}

			$grassblade_free_addons = array(
							"time" 		=> time(),
							"addons" 	=> $api->plugins, 
						);
			

			update_option("grassblade_free_addons", $grassblade_free_addons);
		}

		$grassblade_plugins = $grassblade_free_addons["addons"];


		if ( $this->orderby ) {
			uasort( $grassblade_plugins, array( $this, 'order_callback' ) );
		}

		$this->set_pagination_args( array(
			'total_items' => count($grassblade_plugins), 
			'per_page' => 30,
		) );

		if ( isset( $api->info['groups'] ) ) {
			$this->groups = $api->info['groups'];
		}

		if ( $installed_plugins ) {
			$js_plugins = array_fill_keys(
				array( 'all', 'search', 'active', 'inactive', 'recently_activated', 'mustuse', 'dropins' ),
				array()
			);

			$js_plugins['all'] = array_values( wp_list_pluck( $installed_plugins, 'plugin' ) );
			$upgrade_plugins   = wp_filter_object_list( $installed_plugins, array( 'upgrade' => true ), 'and', 'plugin' );

			if ( $upgrade_plugins ) {
				$js_plugins['upgrade'] = array_values( $upgrade_plugins );
			}

			wp_localize_script( 'updates', '_wpUpdatesItemCounts', array(
				'plugins' => $js_plugins,
				'totals'  => wp_get_update_data(),
			) );
		}

		$this->items = $grassblade_plugins;
	}

	function grassblade_premium_plugins($type = "premium") {
		if(!is_array($this->premium_plugins)) {
			$grassblade_addons = get_option("grassblade_addons");

			if(empty($_GET["force-check"])) {
				if(!empty($grassblade_addons["time"]))
				$this->last_checked = $grassblade_addons["time"];

				if(!empty($grassblade_addons) && !empty($grassblade_addons["time"]) && $grassblade_addons["time"] > time() - 86400 ) {
					if(!empty($grassblade_addons["addons"]) && ( empty($grassblade_addons["error_count"]) || $grassblade_addons["error_count"] < 5 ) ) {
						$this->premium_plugins = $grassblade_addons["addons"];
					}
					else if( !empty($grassblade_addons["count"]) && $grassblade_addons["count"] > 5 ) {
						$this->premium_plugins = array();
					}
				}
			}

			if(!is_array($this->premium_plugins)) {
				$url = "https://license.nextsoftwaresolutions.com/premium_plugins/list.php";
				
				if(!empty($this->installed_plugins))
				$installed_plugins = $this->installed_plugins;
				else
				$installed_plugins = $this->installed_plugins = $this->get_installed_plugins();

				$args = array(
						'locale' => get_user_locale(),
						'installed_plugins' => array_keys( $installed_plugins ),
					);
				$plugins = wp_remote_post($url, $args);

				if( is_wp_error( $plugins ) ) {
					$msg = $plugins->get_error_message();
					echo $msg;
					$count = empty($grassblade_addons["count"])? 1:(intVal($grassblade_addons["count"])+1);

					if(empty($grassblade_addons["error_time"]))
					$grassblade_addons["error_time"] = time();

					$grassblade_addons["error_count"] = empty($grassblade_addons["error_count"])? 1:$grassblade_addons["error_count"]+1;

					$grassblade_addons["error_msg"] = $msg;

					update_option("grassblade_addons", $grassblade_addons);
				
					if(!empty($grassblade_addons["time"]))
					$this->last_checked = $grassblade_addons["time"];

					$this->premium_plugins = !empty( $grassblade_addons["addons"] )?  $grassblade_addons["addons"]:array();
					return $this->premium_plugins;
				}

				if(empty($plugins["response"]) || empty($plugins["response"]["code"]) || $plugins["response"]["code"] != 200 || empty($plugins["body"]) || !is_string($plugins["body"])) {

					if(empty($grassblade_addons["error_time"]))
					$grassblade_addons["error_time"] = time();

					$grassblade_addons["error_count"] = empty($grassblade_addons["error_count"])? 1:$grassblade_addons["error_count"]+1;

					$grassblade_addons["error_msg"] = "bad response";

					update_option("grassblade_addons", $grassblade_addons);

					if(!empty($grassblade_addons["time"]))
					$this->last_checked = $grassblade_addons["time"];

					$this->premium_plugins = !empty( $grassblade_addons["addons"] )?  $grassblade_addons["addons"]:array();
					return $this->premium_plugins;
				}

				$plugins = json_decode($plugins["body"], true);
				$plugins = $this->sanitize( $plugins );
				$premium_plugins = array();

				if(!empty($plugins) && is_array($plugins))
				foreach ($plugins as $key => $value) {
			
					if(!empty($value["type"])) {
						$premium_plugins[$value["type"]] = empty($premium_plugins[$value["type"]])? array():$premium_plugins[$value["type"]];
						$premium_plugins[$value["type"]][] = $value;
					}
				}
				$this->premium_plugins = $premium_plugins;
				$this->last_checked = time();
				$grassblade_addons = array(
								"time" => time(),
								"addons" => $premium_plugins
							);
				update_option("grassblade_addons", $grassblade_addons);
			}
		}

		if(empty($this->premium_plugins[$type]))
			return array();
		else
			return $this->premium_plugins[$type];
	}

	function sanitize($array) {
		if(!empty($array) && ( is_array($array) || is_object($array) ) ) {
			foreach ($array as $key => $value) {
				if(is_array($array))
				$array[$key] = $this->sanitize($value);
				else
				if(is_object($array))
				$array->{$key} = $this->sanitize($value);
			}
		}
		else { //Not array
			$array = strip_tags( $array, "<a><b>" );
			$array = str_ireplace(array("onclick", "onload"), array("on click","on load"), $array);
		}
		return $array;
	}
} // end of class 

new grassblade_addons();

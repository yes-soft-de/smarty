<?php

function tlms_registerAdministrationPages() {
	global $tlms_menu, $tlms_dashboard, $tlms_setup, $tlms_integrations;

	$tlms_menu  = add_menu_page(__('TalentLMS', 'talentlms'), __('TalentLMS', 'talentlms'), 'manage_options', 'talentlms', 'tlms_adminPanel');
	$tlms_dashboard = add_submenu_page('talentlms', __('Dashboard', 'talentlms'), __('Dashboard', 'talentlms'), 'manage_options', 'talentlms', 'tlms_adminPanel');
	$tlms_setup = add_submenu_page('talentlms', __('Setup', 'talentlms'), __('Setup', 'talentlms'), 'manage_options', 'talentlms-setup', 'tlms_setupPage');
	$tlms_integrations = add_submenu_page('talentlms', __('Integrations', 'talentlms'), __('Integrations', 'talentlms'), 'manage_options', 'talentlms-integrations', 'tlms_integrationsPage');
}
add_action('admin_menu', 'tlms_registerAdministrationPages');

function tlms_enqueueAdminScripts() {
	wp_register_script('tlms-admin', _TLMS_BASEURL_ . 'admin/js/tlms-admin.js', false, _TLMS_VERSION_);
	wp_register_style('tlms-admin', _TLMS_BASEURL_. 'admin/css/tlms-admin.css', false, _TLMS_VERSION_);
	wp_register_style('tlms-font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');

	$translations_array = array(
		'progress_message' => __('Please wait while syncing..', 'talentlms'),
		'success_message' => __('The operation completed successfully', 'talentlms'),
		'select_all_message' => __('Select all', 'talentlms'),
		'unselect_all_message' => __('Unselect all', 'talentlms'),
	);

	wp_localize_script( 'tlms-admin', 'translations', $translations_array );
	wp_enqueue_script('tlms-admin');
	wp_enqueue_style('tlms-admin');
	wp_enqueue_style('tlms-font-awesome');

	wp_enqueue_style( 'tlms-datatables-css', _TLMS_BASEURL_ . '/resources/DataTables-1.10.15/media/css/jquery.dataTables.css');
	wp_enqueue_script( 'tlms-datatables-js', _TLMS_BASEURL_. '/resources/DataTables-1.10.15/media/js/jquery.dataTables.js');


}
add_action("admin_enqueue_scripts", 'tlms_enqueueAdminScripts');

// add contextual help
function tlms_contextualHelp($contextual_help, $screen_id, $screen) {
	global $tlms_menu, $tlms_dashboard, $tlms_setup, $tlms_integrations;
	include 'menu-pages/help.php';
}
add_filter('contextual_help', 'tlms_contextualHelp', 10, 3);

/*
 * Pages
 *
 * ******************************************/

function tlms_adminPanel() {
	include 'menu-pages/dashboard.php';
}

function tlms_setupPage () {

	$action_status = $action_message = $enroll_user_validation = $automatically_complete_orders = $api_validation = $domain_validation = '';
	if (isset($_POST['action']) && $_POST['action'] == "tlms-setup") {

		if ($_POST['tlms-domain'] && $_POST['tlms-apikey'] && $_POST['tlms-enroll-user-to-courses']) {

			// we accept the domain only, without the protocol
			if (strtolower(substr($_POST['tlms-domain'], 0, 4 )) === "http"){
				$action_status = "error";
				$domain_validation = 'form-invalid';
				$action_message = __('Invalid TalentLMS Domain', 'talentlms') . "<br />";
			}
			else if (strlen($_POST['tlms-apikey']) != 30){ // TalentLMS API key is exactly 30 characters
				$action_status = "error";
				$api_validation = 'form-invalid';
				$action_message = __('Invalid TalentLMS API key', 'talentlms') . "<br />";
			}
			else{
				update_option('tlms-domain', $_POST['tlms-domain']);
				update_option('tlms-apikey', $_POST['tlms-apikey']);
				update_option('tlms-enroll-user-to-courses', $_POST['tlms-enroll-user-to-courses']);
				if(isset($_POST['tlms-automtically-complete-orders'])){
					update_option('tlms-automtically-complete-orders', $_POST['tlms-automtically-complete-orders']);
				}
				$action_status = "updated";
				$action_message = __('Details edited successfully', 'talentlms');
			}
			
		} else {
			$action_status = "error";

			if (!$_POST['tlms-domain']) {
				$domain_validation = 'form-invalid';
				$action_message = __('TalentLMS Domain required', 'talentlms') . "<br />";
				update_option('tlms-domain', '');
			}

			if (!$_POST['tlms-apikey']) {
				$api_validation = 'form-invalid';
				$action_message .= __('TalentLMS API key required', 'talentlms') . "<br />";
				update_option('tlms-apikey', '');
			}
		}
	}

	include 'menu-pages/setup.php';
}

function tlms_integrationsPage () {

	
	$courses = tlms_selectCourses();

	if(isset($_POST['tlms_products']) && $_POST['tlms_products']) {
		tlms_addProductCategories();

		foreach ($_POST['tlms_products'] as $course_id) {
			if(! tlms_productExists($course_id)) {
				tlms_addProduct($course_id, $courses);
			}
		}

		$action_status = "updated";
		$action_message = __('Operation completed successfuly', 'talentlms');
	}

	if(isset($_POST['action']) && $_POST['action'] == 'tlms-fetch-courses'){//refresh courses
		tlms_getCourses(true );
		wp_redirect(admin_url('admin.php?page=talentlms-integrations'));
	}
	include 'menu-pages/integrations.php';
}

function tlms_resyncCourse() {
	global $wpdb;
	$limit = TalentLMS_Siteinfo::getRateLimit();
	if (!empty($_POST['course_id']) && (empty($limit['remaining']) || $limit['remaining'] > 4)) { // we are gonna make at least 3 api calls, so we have to be prepared.
		$product_ID = $wpdb->get_var("SELECT product_id FROM ".TLMS_PRODUCTS_TABLE." WHERE course_id = ".$_POST['course_id']);

		if($product_ID) {
			$wpdb->query("DELETE FROM " . TLMS_PRODUCTS_TABLE . " WHERE course_id = " . $_POST['course_id']);
			$wpdb->query("DELETE FROM " . TLMS_COURSES_TABLE . " WHERE id = " . $_POST['course_id']);
			$wpdb->query("DELETE FROM wp_posts WHERE ID = ".$product_ID);

			tlms_getCourses($force = true);
			tlms_getCategories(true);
			tlms_addProduct($_POST['course_id'], tlms_selectCourses());
		}
		echo json_encode(array('api_limitation' => 'none'));
	}
	else{
		echo json_encode(array('api_limitation' => 'Api usage resets at '.$limit['formatted_reset']));
	}

	wp_die();
}
add_action('wp_ajax_tlms_resynch', 'tlms_resyncCourse');

function cssPage() {
	if ($_POST['action'] == 'edit-css') {
		file_put_contents(_TLMS_BASEPATH_ . '/css/talentlms-style.css', stripslashes($_POST['tl-edit-css']));
		$action_status = "updated";
		$action_message = __('Details edited successfully', 'talentlms');
	}
	include( _TLMS_BASEPATH_ . '/admin/menu-pages/css.php' );

}


if ((!get_option('tlms-domain') && !get_option('tlms-apikey')) && (empty($_POST['tlms-domain']) && empty($_POST['tlms-apikey']))) {
	function talentlms_warning() {
		echo "<div id='talentlms-warning' class='error fade'><p><strong>" . __('You need to specify a TalentLMS domain and a TalentLMS API key.', 'talentlms') . "</strong> " . sprintf(__('You must <a href="%1$s">enter your domain and API key</a> for it to work.', 'talentlms'), "admin.php?page=talentlms-setup") . "</p></div>";
	}

	add_action('admin_notices', 'talentlms_warning');
} else {
	try{
		TalentLMS::setDomain(get_option('tlms-domain'));
		TalentLMS::setApiKey(get_option('tlms-apikey'));

		tlms_getCourses();
		tlms_getCategories();

	} catch(Exception $e) {
		if ($e instanceof TalentLMS_ApiError) {
			echo "<div class='alert alert-error'>";
			echo $e -> getMessage();
			echo "</div>";
			tlms_recordLog($e -> getMessage());
		}
	}
}

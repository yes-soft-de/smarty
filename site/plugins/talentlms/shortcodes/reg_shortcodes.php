<?php
/**
 * Fix for Hearders already sent
 * checkout: https://tommcfarlin.com/wp_redirect-headers-already-sent/
 */
function app_output_buffer() {
	ob_start();
}
add_action('init', 'app_output_buffer');

function talentlms_course_list($atts) {

	wp_enqueue_style('tlms-font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css');
	wp_enqueue_style( 'tlms-datatables-css', _TLMS_BASEURL_ . '/resources/DataTables-1.10.15/media/css/jquery.dataTables.css');
	wp_enqueue_script( 'tlms-datatables-js', _TLMS_BASEURL_. '/resources/DataTables-1.10.15/media/js/jquery.dataTables.js');
	wp_enqueue_style('talentlms', _TLMS_BASEURL_ . 'css/talentlms.css', false, '1.0');


	$categories = tlms_selectCategories();
	$courses = tlms_selectCourses();

	//ob_start();
	include (_TLMS_BASEPATH_ . '/shortcodes/talentlms_courses.php');
	//$output = ob_get_clean();
	//return $output;

}

add_shortcode('talentlms-courses', 'talentlms_course_list');


function talentlms_signup($atts) {
	$custom_fields = tl_get_custom_fields();

	if ($_POST['tl-signup-post']) {

		$post = true;

		if (!$_POST['first-name']) {
			$first_name_error = __('First Name is mandatory', 'talentlms');
			$first_name_error_class = 'tl-singup-error';
			$post = false;
		}
		if (!$_POST['last-name']) {
			$last_name_error = __('Last Name is mandatory', 'talentlms');
			$last_name_error_class = 'tl-singup-error';
			$post = false;
		}
		if (!$_POST['email']) {
			$email_error = __('Email is mandatory', 'talentlms');
			$email_error_class = 'tl-singup-error';
			$post = false;
		}
		if (!$_POST['login']) {
			$login_error = __('Username is mandatory', 'talentlms');
			$login_error_class = 'tl-singup-error';
			$post = false;
		}
		if (!$_POST['password']) {
			$password_error = __('Password is mandatory', 'talentlms');
			$password_error_class = 'tl-singup-error';
			$post = false;
		}
		if (is_array($custom_fields)) {
			foreach ($custom_fields as $key => $custom_field) {
				if ($custom_field['mandatory'] == 'yes' && !$_POST[$custom_field['key']]) {
					$custom_fields[$key]['error'] = $custom_field['name'] . " " . __('is mandatory', 'talentlms');
					$custom_fields[$key]['error_class'] = 'tl-singup-error';
					$post = false;
				}
			}
		}

		if($post) {
			try {
				$signup_arguments = array('first_name' => $_POST['first-name'], 'last_name' => $_POST['last-name'], 'email' => $_POST['email'], 'login' => $_POST['login'], 'password' => $_POST['password']);
				if (is_array($custom_fields)) {
					foreach ($custom_fields as $custom_field) {
						$signup_arguments[$custom_field['key']] = $_POST[$custom_field['key']];
					}
				}
				$newUser = TalentLMS_User::signup($signup_arguments);
				$tl_signup_failed = false;
			} catch (Exception $e){
				if ($e instanceof TalentLMS_ApiError) {
					$tl_signup_failed = true;
					$tl_signup_fail_message = $e -> getMessage();
					tlms_recordLog($e -> getMessage());
				}
			}

			if (get_option('tl-signup-sync') && !$tl_signup_failed) {
				$new_wp_user_id = wp_insert_user(array('user_login' => $_POST['login'], 'user_pass' => $_POST['password'], 'user_email' => $_POST['email'], 'first_name' => $_POST['first-name'], 'last_name' => $_POST['last-name']));
				if (is_array($custom_fields)) {
					foreach($custom_fields as $custom_field) {
						update_user_meta($new_wp_user_id, $custom_field['key'], $_POST[$custom_field['key']]);
					}
				}
				if (is_wp_error($new_wp_user_id)) {
					$tl_signup_fail_message .= $new_wp_user_id->get_error_message();
				}
			}

			if(!$tl_signup_failed) {
				if (get_option('tl-signup-redirect') == 'talentlms') {
					$login = TalentLMS_User::login(array('login' => $_POST['login'], 'password' => $_POST['password'], 'logout_redirect' => (get_option('tl-logoutfromTL') == 'wordpress') ? get_bloginfo('wpurl') : 'http://'.get_option('tlms-domain')));
					wp_redirect(tl_talentlms_url($login['login_key']));
				} else {
					$login = TalentLMS_User::login(array('login' => $_POST['login'], 'password' => $_POST['password'], 'logout_redirect' => (get_option('tl-logoutfromTL') == 'wordpress') ? get_bloginfo('wpurl') : 'http://'.get_option('tlms-domain')));
					session_start();
					$_SESSION['talentlms_user_id'] = $login['user_id'];
					$_SESSION['talentlms_user_login'] = $_POST['login'];
					$_SESSION['talentlms_user_pass'] = $_POST['password'];
					$creds = array();
					$creds['user_login'] = $_SESSION['talentlms_user_login'];
					$creds['user_password'] = $_SESSION['talentlms_user_pass'];
					$wpuser = wp_signon($creds, false);
					wp_redirect(admin_url('admin.php?page=talentlms-subscriber'));
				}
			}
		}

	}

	ob_start();
	include (_TLMS_BASEPATH_ . '/shortcodes/talentlms_signup.php');
	$output = ob_get_clean();
	return $output;
}
//add_shortcode('talentlms-signup', 'talentlms_signup');

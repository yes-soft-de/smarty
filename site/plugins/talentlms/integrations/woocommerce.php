<?php
if(get_option('tlms-woocommerce-active')){

	/*
    function tlms_processCustomer($customer_id, $new_customer_data, $password_generated){

		if(!empty($_POST['account_username'])){
			$username = $_POST['account_username'];
		}
		else{
			$username = explode("@", $_POST['billing_email']);
			$username = $username[0];
		}

		$signup_arguments = array(
			'first_name' => sanitize_text_field($_POST['billing_first_name']),
			'last_name' => sanitize_text_field($_POST['billing_last_name']),
			'email' => sanitize_email($_POST['billing_email']),
			//'login' => sanitize_user($username),
			'login' => sanitize_email($_POST['billing_email']),
			'password' => $_POST['account_password']
		);

		try{
			$custom_fields = TalentLMS_User::getCustomRegistrationFields();
			if(!empty($custom_fields)){
				foreach($custom_fields as $custom_field){
					if($custom_field['mandatory'] == 'yes'){
						switch($custom_field['type']){
							case 'text':
								$signup_arguments[$custom_field['key']] = " ";
								break;
							case 'dropdown':
								$options = explode(';', $custom_field['dropdown_values']);
								$signup_arguments[$custom_field['key']] = $options[0];
								break;
							case 'date':
								$signup_arguments[$custom_field['key']] = " ";
								break;
						}
					}
				}
			}

            wc_add_notice('aoua', 'error');

			if(tlms_isTalentLMSCourseInCart() && !empty($enroll_user_to_courses) && $enroll_user_to_courses == 'submission'){
				$newUser = TalentLMS_User::signup($signup_arguments);
			}
		}
		catch(Exception $e){
			tlms_recordLog($e->getMessage());
			wc_add_notice(__($e->getMessage()), 'error');
		}
	}
	add_action('woocommerce_created_customer', 'tlms_processCustomer', 10, 3);
	*/

	// enroll user to courses: setup is "upon submission" and order's payment option is "bank transfer", "cheque" or "cash on delivery"
    function tlms_processExistingCustomer($order_id){ //tlms_recordLog('enter_woocommerce_checkout_order_processed');

        $enroll_user_to_courses = get_option('tlms-enroll-user-to-courses');
        if(!tlms_isOrderCompletedInPast($order_id) && tlms_orderHasTalentLMSCourseItem($order_id) && tlms_orderHasLatePaymentMethod($order_id) && !empty($enroll_user_to_courses) && $enroll_user_to_courses == 'submission') { //tlms_recordLog('execute_woocommerce_checkout_order_processed');

            tlms_enrollUserToCoursesByOrderId($order_id);
        }
	}
	add_action('woocommerce_checkout_order_processed', 'tlms_processExistingCustomer', 1, 1);

    // enroll user to courses: setup is "upon submission" and order's payment option is "payment gateway (stripe, paypal, etc)" and transaction returned "success"
    function tlms_woocommerce_payment_complete($order_id){ //tlms_recordLog('enter_woocommerce_payment_complete');

        $enroll_user_to_courses = get_option('tlms-enroll-user-to-courses');
        if(!tlms_isOrderCompletedInPast($order_id) && tlms_orderHasTalentLMSCourseItem($order_id) && !empty($enroll_user_to_courses) && $enroll_user_to_courses == 'submission') { //tlms_recordLog('excecute_woocommerce_payment_complete');

            tlms_enrollUserToCoursesByOrderId($order_id);
        }
    }
    add_action( 'woocommerce_payment_complete', 'tlms_woocommerce_payment_complete' );

    // enroll user to courses: setup is "upon completion" and order's status changed to "completed" (in most cases manually by eshop manager)
    function tlms_processWooComOrder($order_id){ //tlms_recordLog('enter_woocommerce_order_status_completed');

        $enroll_user_to_courses = get_option('tlms-enroll-user-to-courses');
		if(!tlms_isOrderCompletedInPast($order_id) && tlms_orderHasTalentLMSCourseItem($order_id) && !empty($enroll_user_to_courses) && $enroll_user_to_courses == 'completion') { //tlms_recordLog('execute_woocommerce_order_status_completed');

            tlms_enrollUserToCoursesByOrderId($order_id);
		}
	}
	add_action('woocommerce_order_status_completed', 'tlms_processWooComOrder', 10, 1);


	// for when a user changes his password
	function tmls_customerChangedPassword($user){
		try{
			$tlmsUser = TalentLMS_User::retrieve(array('email' => $_POST['account_email']));
			TalentLMS_User::edit(array('user_id' => $tlmsUser['id'], 'password' => $_POST['password_1']));
		}
		catch(Exception $e){
			tlms_recordLog($e->getMessage());
			wc_add_notice(__($e->getMessage()), 'error');
		}
	}

	add_action('woocommerce_save_account_details', 'tmls_customerChangedPassword', 10, 1);

	function tmls_customerResetPassword($user, $pass){
		try{
			$tlmsUser = TalentLMS_User::retrieve(array('email' => $user->data->user_email));
			TalentLMS_User::edit(array('user_id' => $tlmsUser['id'], 'password' => $_POST['password_1']));
		}
		catch(Exception $e){
			tlms_recordLog($e->getMessage());
			wc_add_notice(__($e->getMessage()), 'error');
		}
	}

	add_action('password_reset', 'tmls_customerResetPassword', 10, 2);

	// for when deleting a product from woocommerce
	function tlms_wooCommerceProductDeleted($post_id){
		global $post_type;
		if($post_type != 'product'){
			return;
		}

		tlms_deleteProduct($post_id);
	}

	add_action('before_delete_post', 'tlms_wooCommerceProductDeleted');
	/*
	function action_woocommerce_thankyou($order_get_id){

		$enroll_user_to_courses = get_option('tlms-enroll-user-to-courses');
		if(!empty($enroll_user_to_courses) && $enroll_user_to_courses == 'submission'){
			$order = new WC_Order($order_get_id);

			foreach($order->get_items() as $items){
				$result = get_post_meta($items['product_id'], '_talentlms_course_id');

				try{
					TalentLMS_Course::addUser(array('course_id' => $result[0], 'user_email' => get_userdata($order->user_id)->user_email));
				}
				catch(Exception $e){
					tlms_recordLog($e->getMessage());
					wc_add_notice(__($e->getMessage()), 'error');
				}
			}
		}
	}

	add_action('woocommerce_thankyou', 'action_woocommerce_thankyou', 10, 1);
	*/

	function action_woocommerce_order_item_meta_end($item_id, $item, $order, $flag){

		$tlms_gotocourse = wc_get_order_item_meta($item_id, 'tlms_go-to-course', $single = true);
		if(!empty($tlms_gotocourse)){

			echo '<br/><a href="'.$tlms_gotocourse['goto_url'].'" class="button" target="_blank" >'.__('Navigate on course page', 'talentlms').'</a>';
		}
	}

	add_action('woocommerce_order_item_meta_end', 'action_woocommerce_order_item_meta_end', 10, 4);

	function filter_woocommerce_is_sold_individually($sold_individually, $product){

		return ( !empty(get_post_meta($product->get_id(), '_talentlms_course_id')) ) ? true : $sold_individually;
	}

	add_filter('woocommerce_is_sold_individually', 'filter_woocommerce_is_sold_individually', 10, 2);
}

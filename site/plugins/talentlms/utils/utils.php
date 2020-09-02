<?php
if(!function_exists('tlms_pr')){
	function tlms_pr($var){
		echo "<pre>";
		print_r($var);
		echo "</pre>";
	}
}

if(!function_exists('tlms_pre')){
	function tlms_pre($var){
		echo "<pre>";
		print_r($var);
		echo "</pre>";
		exit;
	}
}

if(!function_exists('tlms_vd')){
	function tlms_vd($var){
		echo "<pre>";
		var_dump($var);
		echo "</pre>";
	}
}

if(!function_exists('tlms_limitWords')){
	function tlms_limitWords($string, $limit){
		if($limit){
			$words = explode(" ", $string);

			return implode(" ", array_splice($words, 0, $limit));
		}
		else{
			return $string;
		}
	}
}

if(!function_exists('tlms_limitSentence')){
	function tlms_limitSentence($string, $limit){
		$sentences = explode(".", $string);

		return implode(".", array_splice($sentences, 0, $limit));
	}
}

if(!function_exists('tlms_isValidDomain')){
	function tlms_isValidDomain($domain){
		return preg_match("/^[a-z0-9-\.]{1,100}\w+$/", $domain) AND (strpos($domain, 'talentlms.com') !== false);
	}
}

if(!function_exists('tlms_isApiKey')){
	function tlms_isApiKey($apiKey){
		if(strlen($apiKey) == 30){
			return true;
		}

		return false;
	}
}

if(!function_exists('tlms_getDateFormat')){
	function tlms_getDateFormat($no_sec = false){
		$site_info = tlms_getTalentLMSSiteInfo();
		$date_format = $site_info['date_format'];

		switch($date_format){
			case 'DDMMYYYY':
				if($no_sec){
					$format = 'd/m/Y';
				}
				else{
					$format = 'd/m/Y, H:i:s';
				}
				break;
			case 'MMDDYYYY':
				if($no_sec){
					$format = 'm/d/Y';
				}
				else{
					$format = 'm/d/Y, H:i:s';
				}
				break;
			case 'YYYYMMDD':
				if($no_sec){
					$format = 'Y/m/d';
				}
				else{
					$format = 'Y/m/d, H:i:s';
				}
				break;
		}

		return $format;
	}
}

if(!function_exists('tlms_getCourses')){
	function tlms_getCourses($force = false){
		global $wpdb;
		if($force){
			$wpdb->query('TRUNCATE TABLE '.TLMS_COURSES_TABLE);
		}

		$result = $wpdb->get_results("SELECT * FROM ".TLMS_COURSES_TABLE);
		if(empty($result)){
			$apiCourses = TalentLMS_Course::all();

			foreach($apiCourses as $course){
				$wpdb->insert(TLMS_COURSES_TABLE, array(
					'id' => $course['id'],
					'name' => $course['name'],
					'course_code' => $course['code'],
					'category_id' => $course['category_id'],
					'description' => $course['description'],
					'price' => esc_sql(filter_var(html_entity_decode($course['price']), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION)),
					'status' => $course['status'],
					'creation_date' => DateTime::createFromFormat(tlms_getDateFormat(), $course['creation_date'])->getTimestamp(),
					'last_update_on' => DateTime::createFromFormat(tlms_getDateFormat(), $course['last_update_on'])->getTimestamp(),
					'hide_catalog' => $course['hide_from_catalog'],
					'shared' => $course['shared'],
					'shared_url' => $course['shared_url'],
					'avatar' => $course['avatar'],
					'big_avatar' => $course['big_avatar'],
					'certification' => $course['certification'],
					'certification_duration' => $course['certification_duration']
				));
			}
		}
	}
}

if(!function_exists('tlms_getCourse')){
	function tlms_getCourse($course_id){
		$apiCourse = TalentLMS_Course::retrieve($course_id);

		return $apiCourse;
	}
}

if(!function_exists('tlms_getCategories')){
	function tlms_getCategories($force = false){
		global $wpdb;

		if($force){
			$wpdb->query("TRUNCATE TABLE ".TLMS_CATEGORIES_TABLE);
		}

		$result = $wpdb->get_results("SELECT * FROM ".TLMS_CATEGORIES_TABLE);
		if(empty($result)){
			$apiCategories = TalentLMS_Category::all();
			foreach($apiCategories as $category){
				$wpdb->insert(TLMS_CATEGORIES_TABLE, array(
					'id' => $category['id'],
					'name' => $category['name'],
					'price' => $category['price'],
					'parent_id' => (!empty($category['parent_id'])) ? $category['parent_id'] : ''
				));
			}
		}
	}
}

if(!function_exists('tlms_selectCourses')){
	function tlms_selectCourses(){
		global $wpdb;
		// snom 5
		$sql = "SELECT c.*, cat.name as category_name FROM ".TLMS_COURSES_TABLE." c LEFT JOIN ".TLMS_CATEGORIES_TABLE
			." cat ON c.category_id=cat.id WHERE c.status = 'active' AND c.hide_catalog = '0'";
		$results = $wpdb->get_results($sql);
		foreach($results as $res){
			$courses[$res->id] = $res;
		}

		return $courses;
	}
}

if(!function_exists('tlms_selectCourse')){
	function tlms_selectCourse($course_id){
		global $wpdb;
		$results = $wpdb->get_row("SELECT * FROM ".TLMS_COURSES_TABLE." WHERE id = ".$course_id);

		return $results;
	}
}

if(!function_exists('tlms_selectCategories')){
	function tlms_selectCategories($where = false, $order = false){
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM ".TLMS_CATEGORIES_TABLE);

		return $results;
	}
}

if(!function_exists('tlms_selectProductCategories')){
	function tlms_selectProductCategories(){
		global $wpdb;
		$results = $wpdb->get_results("SELECT * FROM ".TLMS_PRODUCTS_CATEGORIES_TABLE);

		return $results;
	}
}

if(!function_exists('tlms_addProduct')){
	function tlms_addProduct($course_id, $courses){
		global $wpdb;

		$categories = tlms_selectProductCategories();

		$post = array(
			'post_author' => wp_get_current_user()->ID,
			'post_content' => $courses[$course_id]->description,
			'post_status' => "publish",
			'post_title' => $courses[$course_id]->name,
			'post_parent' => '',
			'post_type' => "product",
		);

		$product_id = wp_insert_post($post);

		wp_set_object_terms($product_id, $courses[$course_id]->category_name, 'product_cat');
		wp_set_object_terms($product_id, 'simple', 'product_type');

		$price = filter_var(html_entity_decode($courses[$course_id]->price), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

		update_post_meta($product_id, '_visibility', 'visible');
		update_post_meta($product_id, '_stock_status', 'instock');
		update_post_meta($product_id, 'total_sales', '0');
		update_post_meta($product_id, '_downloadable', 'no');
		update_post_meta($product_id, '_virtual', 'yes');
		update_post_meta($product_id, '_purchase_note', "");
		update_post_meta($product_id, '_featured', "no");
		update_post_meta($product_id, '_weight', "");
		update_post_meta($product_id, '_length', "");
		update_post_meta($product_id, '_width', "");
		update_post_meta($product_id, '_height', "");
		update_post_meta($product_id, '_sku', "");
		update_post_meta($product_id, '_product_attributes', array());
		update_post_meta($product_id, '_sale_price_dates_from', "");
		update_post_meta($product_id, '_sale_price_dates_to', "");
		update_post_meta($product_id, '_price', $price);
		update_post_meta($product_id, '_regular_price', $price);
		update_post_meta($product_id, '_sale_price', $price);
		update_post_meta($product_id, '_sold_individually', "");
		update_post_meta($product_id, '_manage_stock', "no");
		update_post_meta($product_id, '_backorders', "no");
		update_post_meta($product_id, '_stock', "");
		update_post_meta($product_id, '_talentlms_course_id', $course_id);

		require_once(ABSPATH.'wp-admin/includes/file.php');
		require_once(ABSPATH.'wp-admin/includes/media.php');
		require_once(ABSPATH.'wp-admin/includes/image.php');

		$thumbs_url = $courses[$course_id]->big_avatar;

		$tmp = download_url($thumbs_url);

		preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG)/', $thumbs_url, $matches);
		$file_array['name'] = basename($matches[0]);
		$file_array['tmp_name'] = $tmp;

		if(is_wp_error($tmp)){
			@unlink($file_array['tmp_name']);
			$file_array['tmp_name'] = '';
			//$logtxt .= "Error: download_url error - $tmp\n";
		}
		else{
			//$logtxt .= "download_url: $tmp\n";
		}

		$thumbid = media_handle_sideload($file_array, $product_id, $courses[$course_id]->name);
		if(is_wp_error($thumbid)){
			@unlink($file_array['tmp_name']);
			$file_array['tmp_name'] = '';
		}

		set_post_thumbnail($product_id, $thumbid);

		$wpdb->insert(TLMS_PRODUCTS_TABLE, array(
			'product_id' => $product_id,
			'course_id' => $course_id
		));
	}
}

if(!function_exists('tlms_deleteProduct')){
	function tlms_deleteProduct($product_id){
		global $wpdb;
		$wpdb->delete(TLMS_PRODUCTS_TABLE, array('product_id' => $product_id));
	}
}

if(!function_exists('tlms_productExists')){
	function tlms_productExists($course_id){
		global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM ".TLMS_PRODUCTS_TABLE." WHERE course_id = ".$course_id);
		if(!empty($result)){
			return true;
		}

		return false;
	}
}

if(!function_exists('tlms_addProductCategories')){
	function tlms_addProductCategories(){
		global $wpdb;

		$categories = tlms_selectCategories();

		foreach($categories as $category){
			if(!tlms_productCategoryExists($category->id)){
				$wp_category_id = wp_insert_category(array(
														 'cat_name' => $category->name,
														 'category_nicename' => strtolower($category->name),
														 'taxonomy' => 'product_cat'));

				$wpdb->insert(TLMS_PRODUCTS_CATEGORIES_TABLE, array(
					'tlms_categories_ID' => $category->id,
					'woo_categories_ID' => $wp_category_id
				));
			}
		}
	}
}

if(!function_exists('tlms_productCategoryExists')){
	function tlms_productCategoryExists($category_id){
		global $wpdb;
		$result = $wpdb->get_row("SELECT * FROM ".TLMS_PRODUCTS_CATEGORIES_TABLE." WHERE tlms_categories_ID = ".$category_id);
		if(!empty($result)){
			return true;
		}

		return false;
	}
}

if(!function_exists('tlms_getTalentLMSSiteInfo')){
	function tlms_getTalentLMSSiteInfo(){
		try{
			$site_info = TalentLMS_Siteinfo::get();
		}
		catch(Exception $e){
			tlms_recordLog($e->getMessage());

			return $e;
		}

		return $site_info;
	}
}

if(!function_exists('tlms_getCustomFields')){
	function tlms_getCustomFields(){
		try{
			$custom_fields = TalentLMS_User::getCustomRegistrationFields();
		}
		catch(Exception $e){
			tlms_recordLog($e->getMessage());

			return $e;
		}

		return $custom_fields;
	}
}

if(!function_exists('tlms_getTalentLMSURL')){
	function tlms_getTalentLMSURL($url){
		if(get_option('tlms-domain-map')){
			return str_replace(get_option('tlms-domain'), get_option('tlms-domain-map'), $url);
		}
		else{
			return $url;
		}
	}
}

if(!function_exists('tlms_getLoginKey')){
	function tlms_getLoginKey($url){
		$arr = explode('key:', $url);
		$login_key = ',key:'.$arr[1];

		return $login_key;
	}
}

if(!function_exists('tlms_currentPageURL')){
	function tlms_currentPageURL(){
		$pageURL = 'http';
		if(isset($_SERVER["HTTPS"])){
			if($_SERVER["HTTPS"] == "on"){
				$pageURL .= "s";
			}
		}
		$pageURL .= "://";
		if($_SERVER["SERVER_PORT"] != "80"){
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		}
		else{
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}

		return $pageURL;
	}
}

if(!function_exists('tlms_getUnitIconClass')){
	function tlms_getUnitIconClass($unit_type){
		$iconClass = '';
		switch($unit_type){
			case 'Unit':
				$iconClass = 'fa fa-check';
				break;
			case 'Document':
				$iconClass = 'fa fa-desktop';
				break;
			case 'Video':
				$iconClass = 'fa fa-film';
				break;
			case 'Scorm':
				$iconClass = 'fa fa-book';
				break;
			case 'Webpage':
				$iconClass = 'fa fa-bookmark-o';
				break;
			case 'Test':
				$iconClass = 'fa fa-edit';
				break;
			case 'Survey':
				break;
			case 'Audio':
				$iconClass = 'fa fa-file-audio-o';
				break;
			case 'Flash':
				$iconClass = 'fa fa-asterisk';
				break;
			case 'IFrame' :
				$iconClass = 'fa fa-bookmark';
				break;
			case 'Assignment':
				$iconClass = 'fa fa-calendar-o';
				break;
			case 'Section':
				break;
			case 'Content':
				$iconClass = 'fa fa-bookmark-o';
				break;
			case 'SCORM | TinCan':
				$iconClass = 'fa fa-book';
				break;
		}

		return $iconClass;
	}
}
if(!function_exists('tlms_orderHasLatePaymentMethod')){
    function tlms_orderHasLatePaymentMethod($order_id){

        $order = wc_get_order($order_id); //tlms_recordLog('payment_method: ' . $order->get_payment_method());

        return in_array($order->get_payment_method(), array('bacs', 'cheque', 'cod'));
    }
}
if(!function_exists('tlms_orderHasTalentLMSCourseItem')){
    function tlms_orderHasTalentLMSCourseItem($order_id){

        $order = wc_get_order($order_id);
        $order_items = $order->get_items();
        if($order_items){
            foreach($order_items as $item){
                if(!empty(get_post_meta($item['product_id'], '_talentlms_course_id'))){

                    return true;
                }
            }
        }

        return false;
    }
}
if(!function_exists('tlms_isTalentLMSCourseInCart')){
	function tlms_isTalentLMSCourseInCart(){
		global $woocommerce;

		$items = $woocommerce->cart->get_cart();
		$tmls_courses = array();
		foreach($items as $item => $values){
			$tlms_course_id = get_post_meta($values['product_id'], '_talentlms_course_id', true);
			if(!empty($tlms_course_id)){
				$tmls_courses[] = $tlms_course_id;
			}
		}

		return (empty($tmls_courses)) ? false : true;
	}
}

if(!function_exists('tlms_enrollUserToCoursesByOrderId')){
    function tlms_enrollUserToCoursesByOrderId($order_id){

        $order = wc_get_order($order_id);
        $user = tlms_getUserByOrder($order);

        try{
            $retrieved_user = TalentLMS_User::retrieve(array('email' => $user->user_email));
            $retrieved_user_exists = true;
        }
        catch(Exception $e){
            tlms_recordLog($e->getMessage());
            $retrieved_user_exists = false;
        }

        if(!$retrieved_user_exists){
            try{
                TalentLMS_User::signup(tlms_buildSignUpArgumentsByUser($user));
            }
            catch (Exception $e) {
                tlms_recordLog($e->getMessage());
            }
        }

        try{
            foreach($order->get_items() as $item){

                if(!empty($product_tlms_course = get_post_meta($item['product_id'], '_talentlms_course_id'))){ // isTalentLMSCourseInCart

                    $enrolled_course = TalentLMS_Course::addUser(array('course_id' => $product_tlms_course[0], 'user_email' => $user->user_email));
                    wc_add_order_item_meta($item->get_id(), 'tlms_go-to-course', TalentLMS_Course::gotoCourse(array('course_id' => $product_tlms_course[0], 'user_id' => $enrolled_course[0]['user_id'])));
                }
            }
        }
        catch(Exception $e){
            tlms_recordLog($e->getMessage());
        }
    }
}
if(!function_exists('tlms_buildSignUpArgumentsByUser')){
    function tlms_buildSignUpArgumentsByUser($user){

        $signup_arguments = array();
        $signup_arguments['first_name'] = sanitize_text_field($user->user_firstname);
        $signup_arguments['last_name'] = sanitize_text_field($user->user_lastname);
        $signup_arguments['email'] = sanitize_email($user->user_email);
        $signup_arguments['login'] = sanitize_user(preg_replace('/\s+/', '', $user->user_login));
        $signup_arguments['password'] = $user->user_password;

        try{
            if(!empty($custom_fields = TalentLMS_User::getCustomRegistrationFields())){
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
        }
        catch(Exception $e){
            tlms_recordLog($e->getMessage());
        }

        return $signup_arguments;
    }
}

if(!function_exists('tlms_getUserByOrder')){
    function tlms_getUserByOrder($order){

        $user = new stdClass();
        $user->user_firstname = $order->get_billing_first_name();
        $user->user_lastname = $order->get_billing_last_name();

        if( $existing_user = $order->get_user() ){ //existing or just created

            $user->user_email = $existing_user->data->user_email;
            $user->user_login = $existing_user->data->user_login;
            $user->user_password = !empty($_POST['account_password']) ? substr($_POST['account_password'], 0, 20) : tlms_passgen();
        }
        else { //guest user

            $user->user_email = $order->get_billing_email();
            $user->user_login = $user->user_firstname.'.'.$user->user_lastname;
            $user->user_password = tlms_passgen();
        }

        return $user;
    }
}

if(!function_exists('tlms_recordLog')){
	function tlms_recordLog($message){
		$logFile = _TLMS_BASEPATH_.'/errorLog.txt';

		$time = date("F jS Y, H:i", time() + 25200);
		$logOutput = "#$time: $message\r\n";

		$fp = fopen($logFile, "a");
		$write = fputs($fp, $logOutput);
		fclose($fp);
	}
}

if(!function_exists('tlms_passgen')){
    function tlms_passgen($length = 8){

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}

if(!function_exists('tlms_getCourseIdByProduct')){
	function tlms_getCourseIdByProduct($product_id){

		if(empty($product_id)){
			return;
		}

		global $wpdb;

		$products_courses = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT * FROM ".TLMS_PRODUCTS_TABLE."
				WHERE `product_id` = %d
				",
				$product_id
			),
			ARRAY_A
		);

		return $products_courses[0]['course_id'];
	}
}
if(!function_exists('tlms_isOrderCompletedInPast')){
	function tlms_isOrderCompletedInPast($order_id){

		if(empty($order_id)){
			return;
		}

		global $wpdb;

		$completed_statuses_in_past = $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT * FROM ".$wpdb->comments."
				WHERE `comment_post_ID` = %d
				AND `comment_content` LIKE %s
				",
				$order_id,
				"%to Completed."
			),
			ARRAY_A
		);

		return (empty($completed_statuses_in_past)) ? false : true;
	}
}
if(!function_exists('tlms_getCourseUrl')){
	function tlms_getCourseUrl($course_id){

		$course_url = '';
		$tlms_domain = get_option('tlms-domain');
		if(!empty($tlms_domain)){

			$course_url = '//'.$tlms_domain.'/learner/courseinfo/id:'.$course_id;
		}

		return $course_url;
	}
}
if(!function_exists('tlms_deleteWoocomerceProducts')){
	function tlms_deleteWoocomerceProducts(){

		global $wpdb;
		$products = $wpdb->get_results("SELECT * FROM ".TLMS_PRODUCTS_TABLE);
		if(!empty($products)){
			foreach($products as $product){
				tlms_deleteWoocomerceProduct($product->product_id, false);
			}
		}

		return false;
	}
}
if(!function_exists('tlms_deleteWoocomerceProduct')){

	function tlms_deleteWoocomerceProduct($id, $force = FALSE){

		$product = wc_get_product($id);

		if(empty($product)){
			return new WP_Error(999, sprintf(__('No %s is associated with #%d', 'woocommerce'), 'product', $id));
		}

		if($force){
			if($product->is_type('variable')){
				foreach($product->get_children() as $child_id){
					$child = wc_get_product($child_id);
					$child->delete(true);
				}
			}
			else if($product->is_type('grouped')){
				foreach($product->get_children() as $child_id){
					$child = wc_get_product($child_id);
					$child->set_parent_id(0);
					$child->save();
				}
			}

			$product->delete(true);
			$result = $product->get_id() > 0 ? false : true;
		}
		else{
			$product->delete();
			$result = 'trash' === $product->get_status();
		}

		if(!$result){
			return new WP_Error(999, sprintf(__('This %s cannot be deleted', 'woocommerce'), 'product'));
		}

		// Delete parent product transients.
		if($parent_id = wp_get_post_parent_id($id)){
			wc_delete_product_transients($parent_id);
		}

		return true;
	}
}


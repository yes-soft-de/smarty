<?php

 if ( ! defined( 'ABSPATH' ) ) exit;
class wplms_import{

	function generate_form(){
		echo '	<h3>'.__('IMPORT SETTINGS','vibe-customtypes').'</h3>
			<form method="post" enctype="multipart/form-data">
			    '.sprintf(__('Select File to upload [ Maximum upload size %s MB(s) ]','vibe-customtypes'),$this->getmaxium_upload_file_size()).'<br />
			    <input type="file" name="upfile" id="fileToUpload"><br />
			    <input type="submit" value="'.__('Upload File','vibe-customtypes').'" name="import" class="button-primary">';
			    wp_nonce_field('wplms_import'.get_current_user_id(),'security');
			echo '</form>';
	}

	function process_upload(){

		try {
		    
		    // Undefined | Multiple Files | $_FILES Corruption Attack
		    // If this request falls under any of them, treat it invalid.
		    if (
		        !isset($_FILES['upfile']['error']) ||
		        is_array($_FILES['upfile']['error'])
		    ) {
		        throw new RuntimeException(__('Invalid parameters.','vibe-customtypes'));
		    }

		    // Check $_FILES['upfile']['error'] value.
		    switch ($_FILES['upfile']['error']) {
		        case UPLOAD_ERR_OK:
		            break;
		        case UPLOAD_ERR_NO_FILE:
		            throw new RuntimeException(__('No file sent.','vibe-customtypes'));
		        case UPLOAD_ERR_INI_SIZE:
		        case UPLOAD_ERR_FORM_SIZE:
		            throw new RuntimeException(__('Exceeded filesize limit.','vibe-customtypes'));
		        default:
		            throw new RuntimeException(__('Unknown errors.','vibe-customtypes'));
		    }

		    // You should also check filesize here. 
		    if ($_FILES['upfile']['size'] > 1000000) {
		        throw new RuntimeException(__('Exceeded filesize limit.','vibe-customtypes'));
		    }

		    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
		    // Check MIME Type by yourself.
		    $finfo = new finfo(FILEINFO_MIME_TYPE);
		    if (false === $ext = array_search(
		        $finfo->file($_FILES['upfile']['tmp_name']),
		        array(
		            'text/plain','text/csv','text/tsv','text/html'
		        ),
		        true
		    )) {
		        throw new RuntimeException(__('Invalid file format.','vibe-customtypes'));
		    }

		    if($this->process_csv($_FILES['upfile']['tmp_name'])){
		    	echo '<span class="file-success">'.__('Import Complete','vibe-customtypes').'</span>';
		    }else{
		    	echo '<span class="file-error">'.__('Import Failed','vibe-customtypes').'</span>';
		    };

		} catch (RuntimeException $e) {
		    echo '<span class="file-error">'.$e->getMessage().'</span>';
		}
	}
	function display_filesize($filesize){
    
	    if(is_numeric($filesize)){
	    $decr = 1024; $step = 0;
	    $prefix = array('Byte','KB','MB','GB','TB','PB');
	        
	    while(($filesize / $decr) > 0.9){
	        $filesize = $filesize / $decr;
	        $step++;
	    } 
	    return round($filesize,2).' '.$prefix[$step];
	    } else {

	    return 'NaN';
	    }
    
	}

	function process_csv($file){
		global $wpdb;
		$id_map=array();
		if (($handle = fopen($file, "r")) !== FALSE) {
		    while ( ($data = fgetcsv($handle,1000,",") ) !== FALSE ) {
		    	$table = $data[0];
				$id = $data[1];
				switch($table){
					case $wpdb->prefix.'posts':
						$field=$data[2];
						$value = $data[4];
						$args=array();
						$args[$field]=$value;

						//Check if ID exists in wp_posts
						if(!isset($id_map[$id]) && get_post_status($id)){
							$id_map[$id] = $id;
						}

						if(isset($id_map[$id])){
							$args['ID']=$id_map[$id];
							wp_update_post($args);
						}else{

							$args=array(
								'post_content'=>'Content',
								'post_title'=>'content',
								'post_status'=>'publish'
								);
							$args[$field]=$value;
							$new_id = wp_insert_post($args);
							$id_map[$id] = $new_id;
						}
					break;
				}
		    }
		    fclose($handle);
		}
		if (($handle = fopen($file, "r")) !== FALSE) {
		    while ( ($data = fgetcsv($handle,1000,",") ) !== FALSE ) {

				$table = $data[0];
				$id = $data[1];
				switch($table){
					case $wpdb->prefix.'postmeta':
						$field=$data[2];
						$value = $data[4];
						if(isset($id_map[$id])){
							$id=$id_map[$id];
						}
						if(is_numeric($field)){
							if(isset($id_map[$field])){
								$field=$id_map[$field];
							}
						}
						$value = $this->parse_value($value,$field,$id_map);
						update_post_meta($id,$field,$value);

					break;
					case $wpdb->prefix.'comments':
						$field=$data[2];
						$value = $data[4];
						$args=array();
						$value = $this->parse_value($value,$field,$id_map);
						$args[$field]=$value;

						if(isset($id_map[$id])){
							$args['comment_ID']=$id_map[$id];
							wp_update_comment($args);
						}else{
							$new_id = wp_insert_comment($args);
							$id_map[$id] = $new_id;
						}
					break;
					case $wpdb->prefix.'commentmeta':
						$field=$data[2];
						$value = $data[4];
						if(isset($id_map[$id])){
							$id=$id_map[$id];
						}
						update_comment_meta($id,$field,$value);
					break;
					case $wpdb->prefix.'terms':
						$taxonomy = $data[2];
						$value = $data[4];
						if(isset($id_map[$id])){
							$id=$id_map[$id];
						}
						if(strpos($value,',')){
							$terms = explode(',',$value);
							foreach($terms as $term){
								$term_id = term_exists($value,$taxonomy);
								if ($term_id !== 0 && $term_id !== null) { 
									if(!in_array($taxonomy,array('course-cat','assignment-type')) ){ // Check for hierarchial
										wp_set_post_terms( $id, $value, $taxonomy, true);
									}else{
										$new_term = get_term_by('name',$value,$taxonomy);
										wp_set_post_terms( $id, $new_term->term_id, $taxonomy, true);
									}
								}else{ 
									if(!in_array($taxonomy,array('course-cat','assignment-type')) ){ // Check for hierarchial
										$new_term=wp_insert_term( $value, $taxonomy);
										wp_set_post_terms( $id, $value, $taxonomy, true );
									}else{
										$new_term=wp_insert_term( $value, $taxonomy);
										wp_set_post_terms( $id, $new_term, $taxonomy, true );
									}
									
								}
							}
						}else{
							$term_id = term_exists($value,$taxonomy);
							if ($term_id !== 0 && $term_id !== null) { 
								if(!in_array($taxonomy,array('course-cat','assignment-type')) ){ // Check for hierarchial
									wp_set_post_terms( $id, $value, $taxonomy, true);
								}else{
									$new_term = get_term_by('name',$value,$taxonomy);
									wp_set_post_terms( $id, $new_term->term_id, $taxonomy, true);
								}
							}else{ 
								if(!in_array($taxonomy,array('course-cat','assignment-type')) ){ // Check for hierarchial
									$new_term=wp_insert_term( $value, $taxonomy);
									wp_set_post_terms( $id, $value, $taxonomy, true );
								}else{
									$new_term=wp_insert_term( $value, $taxonomy);
									wp_set_post_terms( $id, $new_term, $taxonomy, true );
								}
								
							}
						}
						
					break;
					case $wpdb->prefix.'user':
						$email=$data[3];
						$login = $data[4];
						if(!isset($id_map[$data[1]])){ // User mapping already exists means user already exists
							$exists = email_exists($email);
							if($exists){
								$id_map[$data[1]] = $exists; // Map new user via Email
							}else{
								if(username_exists($login))
									$login.=rand(1,99);

								$default_pass = apply_filters('wplms_user_pass',$login);
								$userdata = array(
								    'user_login'  =>  $login,
								    'user_email'  =>  $email,
								    'user_pass'   =>  $default_pass  // When creating an user, `user_pass` is expected.
								);
								$user_id = wp_insert_user( $userdata ) ;
								if(is_numeric($user_id)){
									$id_map[$data[1]] = $user_id; // Add to mapping
								}else{
									echo '<p class="file_error">'.__('Unable to Create user','vibe-customtypes').' '.$login.'( '.$email.' )'.'</p>';
								}
							}
						}
					break;
					case $wpdb->prefix.'usermeta':

						$id = $data[1];
						$user_id = $data[3];
						if(isset($id_map[$id]))
							$id = $id_map[$id];
						if(isset($id_map[$user_id]))
							$user_id = $id_map[$user_id];

						$value = $data[4];
						if(!is_numeric($value)){
							if ( preg_match ( '/([0-9]+)+.+(months|weeks|days|hours|minutes)/', $value, $matches ) ){
							    if(is_numeric($matches[1])){
							    	$time = time();
							    	switch($matches[2]){
							    		case 'months':
							    			$time +=$matches[1]*30*86400;
							    		break;
							    		case 'weeks':
							    			$time +=$matches[1]*604800;
							    		break;
							    		case 'hours':
							    			$time +=$matches[1]*3600;
							    		break;
							    		case 'minutes':
							    			$time +=$matches[1]*60;
							    		break;
							    		default:
							    			$time +=$matches[1]*86400;
							    		break;
							    	}
							    	update_user_meta($user_id,$id,$time);
							    }
							}else{
								$value = $this->parse_value($value,$field,$id_map);
								update_user_meta($user_id,$id,$value);
							}
						}else{
							update_user_meta($user_id,$id,$value);
						}
					break;
					case $wpdb->prefix.'user_profile':
						$user_id = $data[1];
						$field = $data[3];
						$value = $data[4];
						if(isset($id_map[$user_id]))
							$user_id = $id_map[$user_id];

						xprofile_set_field_data($field,$user_id,$value);
					break;
				}
			}
		    fclose($handle);
		}
		return 1;
	}
	function getmaxium_upload_file_size(){
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));
        $upload_mb = min($max_upload, $max_post, $memory_limit);
        return $upload_mb;
    }

    function parse_value($value,$field,$id_map){
    	if(strpos($value,'|')){
    		$value_array = explode('|',$value);
    		foreach($value_array as $k => $item){
    			if(strpos($item,',')){
    				if($field == 'vibe_quiz_questions'){
    					$exploded =explode(',',$item);
    					if($k ==0){
    						foreach($exploded as $k => $explode){
    							if(is_numeric($explode) && isset($id_map[$explode]))
    								$exploded[$k] = $id_map[$explode];
    						}
    						$value_array['ques']=$exploded;
    					}
    					if($k==1)
    						$value_array['marks']=$exploded;
    				}else{
    					$value_array[$k]=$item;
    				}
    			}else if(is_numeric($item)){
    				if(isset($id_map[$item]))
    					$value_array[$k]=$id_map[$item];
    			}
    		}
    		$value = $value_array;
    	}

    	$array_map_fields = array(
    		'comment_post_ID'
    	);
    	if( (is_numeric($value)) && ( in_array($field,$array_map_fields) || is_numeric($field) ) ){
    		if(isset($id_map[$value])){
    			$value = $id_map[$value];
    		}
    	}
    	return $value;
    }
}
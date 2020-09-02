<?php

 if ( ! defined( 'ABSPATH' ) ) exit;
class wplms_export{

	var $csv;
	var $fields;
	function __construct(){
		$this->fields = array(
			'posttype'=>array(
				'label'=> __('Select Module','vibe-customtypes'),
				'type' => 'select',
				'options' => array(
					'course'=>__('Course','vibe-customtypes'),
					'unit'=>__('Unit','vibe-customtypes'),
					'quiz'=>__('Quiz','vibe-customtypes'),
					'question'=>__('Question','vibe-customtypes'),
					'wplms-assignment'=>__('Assignment','vibe-customtypes')
					),
				'default' => 'course',
				'desc'=>__('Select a WPLMS Module Type','vibe-customtypes')
				),
			'taxonomy'=>array(
				'label'=> __('Set Taxonomy','vibe-customtypes'),
				'type' => 'text',
				'placeholder' => __('All','vibe-customtypes'),
				'default'=>'',
				'desc'=>__('Optional','vibe-customtypes')
				),
			'taxonomy_term'=>array(
				'label'=> __('Set Taxonomy Term','vibe-customtypes'),
				'type' => 'text',
				'placeholder' => __('All','vibe-customtypes'),
				'default'=>'',
				'desc'=>__('Optional','vibe-customtypes')
				),
			'specific'=>array(
				'label'=> __('Specific Module/s Ids','vibe-customtypes'),
				'type' => 'text',
				'placeholder' => __('All','vibe-customtypes'),
				'default'=>'',
				'desc'=>__('Optional, Enter comma saperated modules for more than one module','vibe-customtypes')
				),
			'content'=>array(
				'label'=> __('Export content','vibe-customtypes'),
				'type' => 'checkbox',
				'default'=>0,
				'desc'=>__('Export content of the module','vibe-customtypes')
				),
			'data'=>array(
				'label'=> __('Export Settings','vibe-customtypes'),
				'type' => 'checkbox',
				'default'=>0,
				'desc'=>__('Export module settings','vibe-customtypes')
				),
			'taxonomies'=>array(
				'label'=> __('Export Taxonomies','vibe-customtypes'),
				'type' => 'checkbox',
				'default'=>0,
				'desc'=>__('Optional taxonomies and relationships','vibe-customtypes')
				),
			'reviews'=>array(
				'label'=> __('Export Comments','vibe-customtypes'),
				'type' => 'checkbox',
				'default'=>0,
				'desc'=>__('Exports reviews for courses, Answers for Questions and Assignments','vibe-customtypes')
				),
			'module'=>array(
				'label'=> __('Export connected modules','vibe-customtypes'),
				'type' => 'checkbox',
				'default'=>0,
				'desc'=>__('Export connected modules, like units,quizzes,assignments with courses, questions with quizzes etc.','vibe-customtypes')
				),
			'user'=>array(
				'label'=> __('Export Users','vibe-customtypes'),
				'type' => 'checkbox',
				'default'=>0,
				'desc'=>__('Export user profiles','vibe-customtypes')
				),
			'user_data'=>array(
				'label'=> __('Export Connected User data','vibe-customtypes'),
				'type' => 'checkbox',
				'default'=>0,
				'desc'=>__('Export user statuses for module/sub-modules','vibe-customtypes')
				),
			'start'=>array(
				'label'=> __('Start Point','vibe-customtypes'),
				'type' => 'number',
				'placeholder'=>'',
				'default' => 0,
				'desc'=>__('Starting Key count for module, recommended for larger exports','vibe-customtypes')
				),
			'number'=>array(
				'label'=> __('Number of Modules','vibe-customtypes'),
				'type' => 'number',
				'placeholder'=>'',
				'default'=>1,
				'desc'=>__('Maximum number of modules to import','vibe-customtypes')
				),

			);
	}

	public function generate_report(){
		$this->build_csv();
		return $this->build_file();
	}

	public function generate_form($url=NULL){

		echo '<form class="import-export-form" method="post"><ul class="lms-settings">';
		foreach($this->fields as $key => $field){
			switch($field['type']){
				case 'text':
					echo '<li><label>'.$field['label'].'</label>&nbsp;<input type="text" name="'.$key.'" value="'.(isset($_POST[$key])?$_POST[$key]:$field['default']).'" placeholder="'.$field['placeholder'].'" />
					<span>'.$field['desc'].'</span></li>';
				break;
				case 'number':
					echo '<li><label>'.$field['label'].'</label>&nbsp;<input type="number" name="'.$key.'" value="'.(isset($_POST[$key])?$_POST[$key]:$field['default']).'" placeholder="'.$field['placeholder'].'" />
					<span>'.$field['desc'].'</span></li>';
				break;
				case 'checkbox':
					echo '<li><label>'.$field['label'].'</label>&nbsp;<input type="checkbox" name="'.$key.'" '.(isset($_POST[$key])?'checked':'').'/>
					<span>'.$field['desc'].'</span></li>';
				break;
				case 'select':
					echo '<li><label>'.$field['label'].'</label>&nbsp;<select name="'.$key.'">';
					foreach($field['options'] as $k=>$option){
						echo '<option value="'.$k.'" '.(($k == $_POST[$key])?'selected':'').'>'.$option.'</option>';
					}
					echo '</select><span>'.$field['desc'].'</span></li>';
				break;

			}
		}
		echo '<input type="submit" name="export" class="button-primary button" value="'.__('Generate Export File','vibe-customtypes').'" />';
		if(isset($_POST['export'])){
			echo '&nbsp;<a href="'.$url.'" target="_blank" class="button-primary button">'.__('Download Export File','vibe-customtypes').'</a>';
		}
			
		wp_nonce_field('wplms_export'.get_current_user_id(),'security');
		echo '</form>';
	}

	function build_csv(){

		foreach($this->fields as $key => $field){
			$defaults[$key]=$field['default'];
			if(isset($_POST[$key]))
				$defaults[$key]=$_POST[$key];
		}
		extract( $defaults, EXTR_SKIP );
        
        global $wpdb;
        
        if(is_numeric($specific)){
        	$query_args = array('post_type' => $posttype,'p'=>$specific);
        }else{
        	$query_args = array(
	    	'post_type' => $posttype,
	    	'offset' => $start,
	    	'posts_per_page' => $number,
	    	);
	        if($taxonomy){	
	        	if($taxonomy_term){
	        		$terms = explode(',',$taxonomy_term);
	        		$query_args['tax_query']=array(
	        			array(
	        				'taxonomy' => $taxonomy,
	        				'field'=>'slug',
	        				'terms' => $terms
	        				)
	        			);
	        	}
	        }	
        }

       	$results  = new WP_Query($query_args);
       	if($results->have_posts()){
       		while($results->have_posts()){
       			$results->the_post();global $post;

       			if($content){
       				$this->csv[]=array($wpdb->prefix.'posts',$post->ID,'post_type',__('Type','vibe-customtypes'),$post->post_type);
       				$this->csv[]=array($wpdb->prefix.'posts',$post->ID,'post_title',__('Title','vibe-customtypes'),$post->post_title);
       				$this->csv[]=array($wpdb->prefix.'posts',$post->ID,'post_date',__('Date','vibe-customtypes'),$post->post_date);
       				$this->csv[]=array($wpdb->prefix.'posts',$post->ID,'post_content',__('Content','vibe-customtypes'),$post->post_content);
       				$this->csv[]=array($wpdb->prefix.'posts',$post->ID,'post_author',__('Author ID','vibe-customtypes'),$post->post_author);
       				$this->csv[]=array($wpdb->prefix.'posts',$post->ID,'post_excerpt',__('Excerpt','vibe-customtypes'),$post->post_excerpt);
       				$this->csv[]=array($wpdb->prefix.'posts',$post->ID,'post_status',__('Status','vibe-customtypes'),$post->post_status);
       				$this->csv[]=array($wpdb->prefix.'posts',$post->ID,'post_name',__('Slug','vibe-customtypes'),$post->post_name);
       				$this->csv[]=array($wpdb->prefix.'posts',$post->ID,'comment_count',__('Review/Note/Answer Count','vibe-customtypes'),$post->comment_count);
       			}

       			if($data)
       				$this->get_connected_data($post->ID,$post->post_type);

       			if($taxonomies)
       				$this->get_taxonomy($post->ID,$post->post_type);

       			if($user){
       				$this->get_users($post->ID,$post->post_type);
       			}

       			if($reviews)
       				$this->get_comments($post->ID,$post->post_type);

       			if($user_data)
   					$this->get_user_data($post->ID,$post->post_type);
       			if($module)
       				$this->get_modules($post->ID,$post->post_type,$module,$data,$reviews,$user_data);
       		}
       	}
       	wp_reset_postdata();
	}

	public function get_module_data($id){
		global $wpdb;
    	$result  = get_post($id);

		$this->csv[]=array($wpdb->prefix.'posts',$result->ID,'post_type',__('Type','vibe-customtypes'),$result->post_type);
		$this->csv[]=array($wpdb->prefix.'posts',$result->ID,'post_title',__('Title','vibe-customtypes'),$result->post_title);
		$this->csv[]=array($wpdb->prefix.'posts',$result->ID,'post_date',__('Date','vibe-customtypes'),$result->post_date);
		$this->csv[]=array($wpdb->prefix.'posts',$result->ID,'post_content',__('Content','vibe-customtypes'),$result->post_content);
		$this->csv[]=array($wpdb->prefix.'posts',$result->ID,'post_author',__('Author ID','vibe-customtypes'),$result->post_author);
		$this->csv[]=array($wpdb->prefix.'posts',$result->ID,'post_excerpt',__('Excerpt','vibe-customtypes'),$result->post_excerpt);
		$this->csv[]=array($wpdb->prefix.'posts',$result->ID,'post_status',__('Status','vibe-customtypes'),$result->post_status);
		$this->csv[]=array($wpdb->prefix.'posts',$result->ID,'post_name',__('Slug','vibe-customtypes'),$result->post_name);
		$this->csv[]=array($wpdb->prefix.'posts',$result->ID,'comment_count',__('Review/Note/Answer Count','vibe-customtypes'),$result->comment_count);

	}

	function get_taxonomy($post_id,$post_type){
		global $wpdb;
		switch($post_type){
			case 'course':
				$taxonomies=array('linkage','level','course-cat');
			break;
			case 'unit':
				$taxonomies=array('linkage','level','module-tag');
			break;
			case 'question':
				$taxonomies=array('linkage','level','question-tag');
			break;
			case 'wplms-assignment':
				$taxonomies=array('linkage','level','assignment-type');
			break;
			default:
			 	return;
			break;
		}

		foreach($taxonomies as $taxonomy){
			$terms = wp_get_post_terms( $post_id, $taxonomy);
			if(isset($terms) && is_array($terms)){
				foreach($terms as $term){
					$terms_array[]=$term->name;
				}
				if(is_array($terms_array) && count($terms_array))
					$this->csv[]=array($wpdb->prefix.'terms',$post_id,$taxonomy,__('Terms','vibe-customtypes'),implode(',',$terms_array));
			}
		}
	}

	function get_users($id,$post_type=NULL){
		 global $wpdb;
		$results=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->usermeta} WHERE meta_key = %d AND meta_value REGEXP '^[0-9]+$'",$id),ARRAY_A);
		foreach($results as $result){
			$users = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->users} WHERE ID = %d",$result['user_id']),ARRAY_A);
			if(count($users)){ // Checks if Integer is user ID
				foreach($users as $user){
					$this->csv[]=array($wpdb->prefix.'user',$result['user_id'],__('User Details','vibe-customtypes'),$user['user_email'],$user['user_login']);
				}
				$this->get_user_xprofile_data($result['user_id']);
			}
		}
	}

	function get_user_xprofile_data($user_id){
		global $wpdb;
		$table1=$wpdb->prefix.'bp_xprofile_data';
		$table2=$wpdb->prefix.'bp_xprofile_fields';
		$results=$wpdb->get_results($wpdb->prepare("
			SELECT fields.name as name,data.value as value
		    FROM $table1 as data
		    LEFT JOIN $table2 AS fields ON data.field_id = fields.id
		    WHERE 	data.user_id 	= %d
			",$user_id),ARRAY_A);

		if(isset($results) && is_array($results) && count($results)){
			foreach($results as $result){
				$this->csv[]=array($wpdb->prefix.'user_profile',$user_id,'field',$result['name'],$result['value']);
			}
		}
	}

	function build_file(){
		$dir = wp_upload_dir();
		$file_name = 'export_'.$_POST['posttype'].'_'.count($this->csv).'.csv';
		$filepath = $dir['basedir'] . '/export/';
		if(!file_exists($filepath))
			mkdir($filepath,0755);

		$file = $filepath.$file_name;
		if(file_exists($file))
			unlink($file);

		if (($handle = fopen($file, "w")) !== FALSE) {
			if(is_array($this->csv))
				foreach($this->csv as $fields)
		    		fputcsv($handle, $fields);  
		}

		fclose($handle);
		$filepath = $dir['baseurl']. '/export/'.$file_name;
		return $filepath;
	}

	public function get_connected_data($id,$post_type=NULL){
		global $wpdb;
		$results=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key NOT REGEXP '^[0-9]+$'",$id),ARRAY_A);
		foreach($results as $result){
			$data = @unserialize($result['meta_value']);
				if ($data !== false) {
				} else {
				    $data=$result['meta_value'];
				}

			if(is_array($data))	{
				foreach($data as $key=>$d){
					if(is_array($d)){
						$data[$key]=implode(',',$d);		
					}
				}
				$comma_saperated=implode('|',$data);
				$key_label = '';
				$label=vibe_meta_box_arrays($post_type);
				$key_label=$label[$result['meta_key']]['label'];	
				$this->csv[]=array($wpdb->prefix.'postmeta',$result['post_id'],$result['meta_key'],$key_label,$comma_saperated);
			}else{
				$key_label = '';
				$label=vibe_meta_box_arrays($post_type);
				if(is_array($label) && isset($label[$result['meta_key']]['label'])){
					$key_label=$label[$result['meta_key']]['label'];	
				}else{
					$label = str_replace('_',' ',$result['meta_key']);
					$key_label=ucfirst($label);
				}
				$this->csv[]=array(
				$wpdb->prefix.'postmeta',$result['post_id'],$result['meta_key'],$key_label,$data
				);
			}
		}
	}

	function get_comments($id){
		global $wpdb;
		$args = array('post_id'=>$id,'status'=>'approve');
		$comments_query = new WP_Comment_Query;
		$comments = $comments_query->query( $args );
		// Comment Loop
		if ( $comments ) {
			foreach ( $comments as $comment ) {
				$this->csv[]=array($wpdb->prefix.'comments',$comment->comment_ID,'comment_post_ID',__('Connected Post','vibe-customtypes'),$comment->comment_post_ID);
				$this->csv[]=array($wpdb->prefix.'comments',$comment->comment_ID,'comment_type',__('Type','vibe-customtypes'),$comment->comment_type);
				$this->csv[]=array($wpdb->prefix.'comments',$comment->comment_ID,'comment_content',__('Content','vibe-customtypes'),$comment->comment_content);
				$this->csv[]=array($wpdb->prefix.'comments',$comment->comment_ID,'comment_date',__('Date','vibe-customtypes'),$comment->comment_date);
				$this->csv[]=array($wpdb->prefix.'comments',$comment->comment_ID,'comment_parent',__('Parent ID','vibe-customtypes'),$comment->comment_parent);
				$this->csv[]=array($wpdb->prefix.'comments',$comment->comment_ID,'user_id',__('Author ID','vibe-customtypes'),$comment->user_id);
				$this->csv[]=array($wpdb->prefix.'comments',$comment->comment_ID,'comment_author',__('Author Name','vibe-customtypes'),$comment->comment_author);
				$this->csv[]=array($wpdb->prefix.'comments',$comment->comment_ID,'comment_author_email',__('Author Email','vibe-customtypes'),$comment->comment_author_email);

				$results=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->commentmeta} WHERE comment_id = %d",$comment->comment_ID),ARRAY_A);
				foreach($results as $result){
					$label = str_replace('_',' ',$result['meta_key']);
					$key_label=ucfirst($label);
					$this->csv[]=array($wpdb->prefix.'commentmeta',$result['comment_id'],$result['meta_key'],$key_label,$result['meta_value']);
				}

			}
		} 
	}

	public function get_modules($id,$post_type,$module,$data,$reviews,$user_data){
		$prefix = 'vibe_';
		switch($post_type){
			case 'course':
				$curriculum = vibe_sanitize(get_post_meta($id,$prefix.'course_curriculum',false));
				foreach($curriculum as $unit){
					if(is_numeric($unit)){
						$this->get_module_data($unit,'unit');
						$this->get_connected_data($unit,'unit');
					}
				}
				if($reviews){
					foreach($curriculum as $unit){
					if(is_numeric($unit)){
						$this->get_comments($unit);
					}}
				}
				if($user_data){
					foreach($curriculum as $unit){
					if(is_numeric($unit)){
						$this->get_user_data($unit);
					}}
				}

				if($module){
					foreach($curriculum as $unit){
					if(is_numeric($unit)){
						$post_type = get_post_type($unit);
						$this->get_modules($unit,$post_type,$module,$data,$reviews,$user_data);
					}}
				}
			break;
			case 'quiz':
				$quiz_questions = vibe_sanitize(get_post_meta($id,$prefix.'quiz_questions',false));
				if(is_array($quiz_questions['ques'])){
					foreach($quiz_questions['ques'] as $ques){
						if(is_numeric($ques)){
							$this->get_module_data($ques);	
							$this->get_connected_data($ques,'question');	
						}
					}
					if($reviews){
						foreach($quiz_questions['ques'] as $ques){
							if(is_numeric($ques)){
								$this->get_comments($ques);
							}
						}
					}
					if($user_data){
						foreach($quiz_questions['ques'] as $ques){
							if(is_numeric($ques)){
								$this->get_user_data($ques);
							}
						}
					}
								
				}
			break;
			case 'unit':
				$assignments = vibe_sanitize(get_post_meta($id,$prefix.'assignment',false));
				if(is_array($assignments)){
					foreach($assignments as $assignment){
						if(is_numeric($assignment)){
							$this->get_module_data($assignment);
							$this->get_connected_data($assignment,'wplms-assignment');	
						}
					}
					if($reviews){
						foreach($assignments as $assignment){
						if(is_numeric($assignment)){
							$this->get_comments($assignment);
						}}
					}
					if($user_data){
						foreach($assignments as $assignment){
						if(is_numeric($assignment)){
							$this->get_user_data($assignment);
						}}
					}
				}
			break;
		}
	}

	function get_user_data($id,$post_type=NULL){
		global $wpdb;
		$users=$wpdb->get_results($wpdb->prepare("SELECT user_id,meta_value FROM {$wpdb->usermeta} WHERE  meta_key = %d AND meta_value REGEXP '^[0-9]+$'",$id),ARRAY_A);
		if(isset($users) && is_array($users) && count($users)){
			foreach($users as $user){
				if(isset($user['user_id'])){
					$this->csv[]=array($wpdb->prefix.'usermeta',$id,__('User Timestamp','vibe-customtypes'),$user['user_id'],$user['meta_value']);
					$user_status=get_post_meta($id,$user['user_id'],true);
					if(isset($user_status) && $user_status !=''){
						$this->csv[]=array($wpdb->prefix.'postmeta',$id,$user['user_id'],__('User status','vibe-customtypes'),$user_status);
						if($post_type == 'course'){
							$course_status=get_user_meta($user['user_id'],'course_status'.$id,true);
							$this->csv[]=array($wpdb->prefix.'usermeta','course_status'.$id,'COURSE STATUS',$user['user_id'],$course_status);
						}
					}
				}
			}
		}
	}
}


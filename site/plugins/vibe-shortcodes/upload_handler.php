<?php

 if ( ! defined( 'ABSPATH' ) ) exit;
class WPLMS_ZIP_UPLOAD_HANDLER{

	function __construct(){
		add_action( 'wp_ajax_zip_upload', array($this,'wp_ajax_zip_upload' ));
		add_action( 'wp_ajax_del_dir', array($this,'wp_ajax_del_dir' ));
		add_action('media_upload_upload',array($this,'media_upload_upload'));
		add_action( 'media_buttons', array($this,'wp_zip_upload_media_button'),100,1);
		add_action('wp_enqueue_scripts', array($this,'add_media_upload_scripts'));
	}


	function wp_zip_upload_media_button($editor_id) {
		$editor_id = apply_filters('wplms_upload_zip_button',$editor_id);
		if($editor_id){
			echo '<a href="'.admin_url('media-upload.php?type=upload&TB_iframe=true&tab=upload').'" class="thickbox button">
	  		 <div class="dashicons dashicons-upload"></div> '.__('Upload ZIP','vibe-shortcodes').'</a>';
		}
	}

	function add_media_upload_scripts() {
	    if ( is_admin() ) {
	         return;
	       }
	    if(isset($_GET['edit']))   
	    wp_enqueue_media();   
	}

	function media_upload_upload(){ 

		if(isset($_GET['tab']) && $_GET['tab']=='zip'){
		wp_iframe( "media_upload_zip_content" );
		}
		else{
			wp_iframe( "media_upload_zip_form" );
		}
	}
	function zip_tabs($tabs) {
		$newtab1 = array('zip' => __('Upload Library','vibe-shortcodes'));
		$newtab2 = array('upload' => __('Upload File','vibe-shortcodes'));
		return array_merge( $newtab2,$newtab1);
	}

	function print_tabs(){ 
		add_filter('media_upload_tabs', array($this,'zip_tabs'));
		media_upload_header();
	}

	function print_page_navi($num_records){
					//$num_records;	#holds total number of record
					$page_size;		#holds how many items per page
					$page;			#holds the curent page index
					$num_pages; 	#holds the total number of pages
					$page_size = 10;
					#get the page index
					if (empty($_GET['npage']) || !is_numeric($_GET['npage'])){$page = 1;
					}else{$page = $_GET['npage'];}
					
					#caluculate number of pages to display
					if(($num_records%$page_size)){
						$num_pages = (floor($num_records/$page_size) + 1);
					}else{
						$num_pages = (floor($num_records/$page_size));
					}
			
					if ($num_pages != 1)
					{
						for ($i = 1; $i <= $num_pages; ++$i)
						{
							if ($i == $page){
								echo "$i";	
							}else{
								echo "<a href=\"media-upload.php?type=upload&tab=zip&npage=$i\">$i</a>";
				
							}
							if($i != $num_pages)
							{
								echo " | ";
							}
						}
					}
			
					#calculate boundaries for limit query
					$upper_bound = (($page_size * ($page-1)) + $page_size);/*$page_size;*/
					$lower_bound = ($page_size * ($page-1));
					$bound=array($lower_bound,$upper_bound,);
					return $bound;
	}



	function print_detail_form($tab="upload", $file_url="", $dirname=""){
		?>
		<div class="package_upload_detail_form">
			<label><?php _e('Iframe source','vibe-shortcodes'); ?> : </label> 
			<input type="text" size="80"  class="package_upload_file_url" value="<?php echo $file_url; ?>" />
			<input type="hidden" size="40"  class="package_upload_dir_name" value="<?php echo $dirname; ?>" />
			<?php 
			if($tab=='upload'){ 
			 echo '<input type="hidden" class="package_uploads_file_name" value="" size="20" />';
			}
			?>		
			<hr />
			<label><?php _e('Maximum height of iFrame','vibe-shortcodes'); ?> : </label> <input type="text" name="package_upload_max_height" class="package_upload_max_height" value="800">
			<input type="button" class="button package_upload_insert_into_post"  value="<?php _e('Insert Into Post','vibe-shortcodes'); ?>" /> 
		</div>
	<?php
	}




	function printInsertForm(){

		$dirs = $this->getDirs();
		if (count($dirs)>0){
		$this->print_js("zip");
		$uploadDirUrl=$this->getUploadsUrl();
		 //START PAGIGNATION
		 ?>
		 <div class="upload_dirs_navigation"> 
		 <?php  $bound= $this->print_page_navi(count($dirs)); // print the pagignation and return upper and lower bound ?>
		 </div>
		 <?php
		 
		  $lower_bound=$bound[0];
		  $upper_bound=$bound[1]; 

		  if($upper_bound>count($dirs))
		  	$upper_bound = count($dirs);
		  echo '<span>'.sprintf(__('Showing Content %d - %d from %d','vibe-shortcodes'),$lower_bound,$upper_bound,count($dirs)).'</span>';
		  //$dirs = array_slice($dirs, $lower_bound, $upper_bound);
		  $dirs = array_slice($dirs, $lower_bound, 10);
		  //END PAGIGNATION
		 	
			echo "<table class='widefat'>";
				foreach ($dirs as $i=>$dir):
					extract($dir);
					$package_name = str_replace("_"," " ,$dir);
					echo '<tr '.(($i%2)?'class="alternate"':'').'>
							<td>';
							echo '<strong>'.$package_name.'</strong>';
							echo '<span class="package_upload_controls">';
							echo '<span class="package_uploads_show_button"><i class="dashicons dashicons-plus"></i></span> | ';
							echo '<span class="package_uploads_delete_dir"><i class="dashicons dashicons-no"></i></span>';
							echo '</span>';
							$this->print_detail_form("zip" , $uploadDirUrl.$dir."/".$file, $dir);
							echo '
							</td>
						 </tr>';

				endforeach;
			echo "</table>";
		
		}else{
		echo __('No packages available','vibe-shortcodes');
		}
		
	}

	function getUploadsPath(){
		$dir = wp_upload_dir();
		$privacy = vibe_get_option('instructor_content_privacy');
		if(isset($privacy) && $privacy){
			$user_id = get_current_user_id();
			return $dir['basedir'] . '/private_package_uploads/'.$user_id.'/';
		}else{
			return $dir['basedir'] . '/package_uploads/';
		}
	}
	function getUploadsUrl(){
		$dir = wp_upload_dir();
		$privacy = vibe_get_option('instructor_content_privacy');
		if(isset($privacy) && $privacy){
			$user_id = get_current_user_id();
			return $dir['baseurl'] . '/private_package_uploads/'.$user_id.'/';
		}else{
			return $dir['baseurl'] . '/package_uploads/';
		}
	}

	function getDirs(){
		$paths = $this->getUploadsPath();
		if(file_exists($paths)){
			$myDirectory = opendir($paths);
			$dirArray = array();
			$i=0;
			while($entryName = readdir($myDirectory)) {
				if ($entryName != "." && $entryName !=".." && is_dir($this->getUploadsPath().$entryName)):
				$dirArray[$i]['dir'] = $entryName;
				$dirArray[$i]['file'] = $this->getFile($this->getUploadsPath().$entryName);
				$i++;
				endif;
			}
			// close directory
			closedir($myDirectory);
			return $dirArray;
		}
		return array();
	}

	function getFile($dir){
        $myDirectory = opendir($dir);
        $fileArray = array();
        $myDirectory = opendir($dir);
        $fileArray = array();
        $file1 = '';
        // get each entry
        while($entryName = readdir($myDirectory)) {
          if ($entryName != "." && $entryName !=".."){
            $f = $this->getUploadsPath().$entryName;
            $fname = pathinfo ($f, PATHINFO_FILENAME);
            $ext = pathinfo ($f,PATHINFO_EXTENSION);

            if (in_array($ext,array('html','htm','mov','avi','mp4','mp3','txt'))){
              
              if(!empty($entryName)){
                if(strpos($fname, 'index') !== false || strpos($fname, 'story') !== false){
                  	return $entryName;
                  	break;
                }else{
                  	$file1 = $entryName;
                }
              }
            }
          }
        }
        closedir($myDirectory);
        if(!empty($file1)){
          return $file1;
        }
        return false;
    }

	function print_js(){ 
		wp_enqueue_script('package_uploads',VIBE_PLUGIN_URL.'/vibe-shortcodes/js/package_uploads.js',array('jquery'));
		$translation_array = array( 
			'upload_zip_error'=> __( 'Please upload a Zip package with index.html file','vibe-shortcodes' ), 
			'no_data'=> __( 'No data found!','vibe-shortcodes' ), 
			'remove_package'=> __( 'Are you sure you want to remove this package?','vibe-shortcodes' ), 
			);
		wp_localize_script( 'package_uploads', 'package_upload_strings', $translation_array );
		return;
	}

	function print_upload(){
		$this->print_js();
		?>
		<form enctype="multipart/form-data" id="zip_upload_form" action="admin-ajax.php" method="POST">
		<input type="hidden" name="action" value="zip_upload" />
		<input type="hidden" name="MAX_FILE_SIZE" value="1000000000" />
		<table style="margin-left:-15px;">
		<tr><td>
		<strong><?php _e('Choose a file to upload','vibe-shortcodes'); ?></strong></td><td> <input name="uploadedfile"  id="uploadedfile" type="file" /></td></tr>
		<tr><td>&nbsp;</td><td><input type="submit" value="<?php _e('Upload File','vibe-shortcodes'); ?>" class="button" /></td></tr>
		</table>
		</form>
		<p><i><?php _e('Please choose a .zip package file','vibe-shortcodes'); ?></i></p>
		<img id="media_loading" style='display:none;' src= "<?php echo VIBE_PLUGIN_URL . '/vibe-shortcodes/images/loading.gif' ;?>" /><br />
		<?php $this->print_detail_form();?>
		<p/>
		<?php
	}



	function wp_ajax_del_dir(){
		$dir = $this->getUploadsPath().$_POST['dir'];
		$this->rrmdir($dir);
		die();
	}
	function wp_ajax_zip_upload(){
		$arr = array();
		
		$file = $_FILES['uploadedfile']['tmp_name'];
		$dir = explode(".",$_FILES['uploadedfile']['name']);
		$dir[0] = str_replace(" ","_",$dir[0]);
		$target = $this->getUploadsPath().$dir[0];
		$index = count($dir) -1;

		if (!isset($dir[$index]) || $dir[$index] != "zip")
			$arr[0] = __('The Upload file must be zip archive','vibe-shortcodes');
		else{
			while(file_exists($target)){
				$r = rand(1,10);
				$target .= $r;
				$dir[0] .= $r;
			}
			if (!empty($file))
				$arr = $this->extractZip($file,$target,$dir[0]);
			else
				$arr[0] = __('File too big','vibe-shortcodes');
		}
			echo json_encode($arr);
		die();
	}

	function extractZip($fileName,$target,$dir){
	 		$arr = array();
	 	 $zip = new ZipArchive;
	     $res = $zip->open($fileName);
	     if ($res === TRUE) {
	         $zip->extractTo($target);
	         $zip->close();
			 $file = $this->getFile($target);
			 ;
			if($file){
				 $arr[0] = 'uploaded'; 
				 $arr[1] = $this->getUploadsUrl().$dir."/".$file; 
				 $arr[2] = $dir;
				 $arr[3] =$file;
			 }else{
				 $arr[0] = __('Please upload zip file, Index.html file not found in package','vibe-shortcodes').$target.print_r($file);
				 $this->rrmdir($target);
			 }
	     }else{
			$arr[0] = __('Upload failed !','vibe-shortcodes');;
	     }
		 return  $arr;
	}

	function rrmdir($dir) {
		if (is_dir($dir)) {
		 $objects = scandir($dir);
		 foreach ($objects as $object) {
		   if ($object != "." && $object != "..") {
		     if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
		   }
		 }
		 reset($objects);
		 rmdir($dir);
		}
	} 

}


function media_upload_zip_form(){
	$wplmsthis = new WPLMS_ZIP_UPLOAD_HANDLER;
	$wplmsthis->print_tabs();
	echo '<div class="upload_directory">';
	echo '<h2>'.__('Upload File','vibe-shortcodes').'</h2>';
	$wplmsthis->print_upload();
	echo "</div>";
}

function media_upload_zip_content(){
	$wplmsthis = new WPLMS_ZIP_UPLOAD_HANDLER;
	$wplmsthis->print_tabs();
	echo '<div class="upload_directory">';
	echo '<h2>'.__('Upload Library','vibe-shortcodes').'</h2>';
	$wplmsthis->printInsertForm();
	echo '</div>';
}

?>
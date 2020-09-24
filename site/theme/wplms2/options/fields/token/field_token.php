<?php
class VIBE_Options_token extends VIBE_Options{	
	
	/**
	 * Field Constructor.
	 *
	 * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
	 *
	 * @since VIBE_Options 1.0
	*/
	function __construct($field = array(), $value ='', $parent = ''){
		
		parent::__construct($parent->sections, $parent->args, $parent->extra_tabs);
		$this->field = $field;
		$this->value = $value;
		
		
		
	}//function
	
	
	
	/**
	 * Field Render Function.
	 *
	 * Takes the vars and outputs the HTML for the field in the settings
	 *
	 * @since VIBE_Options 1.0
	*/
	function render(){
		
		$class = (isset($this->field['class']))?$this->field['class']:'button-primary';
		

		$username ='';
		if(function_exists('vibe_get_option')){
			$username = vibe_get_option('username');
		}
		$security ='';
		if(function_exists('vibe_get_option')){
			$security = vibe_get_option('security');
		}

		$token = get_option('envato_token');
		


		echo '<a href="https://wplms.io/envato/?callback_url='.urlencode(admin_url('admin.php?page=wplms_options&tab=0')).'&security='.$security.( empty($username)?'':'&username='.$username).'" id="envato_auth_link" name="'.$this->args['opt_name'].'['.$this->field['id'].']" rows="6"  class="'.$class.'" style="padding: 15px; display: grid; align-items: center; height: auto; font-size: 20px; justify-items: center; width: auto;">'.(empty($token)?$this->field['title']:$this->field['title_alt']).'</a>';
			
		echo (isset($this->field['desc']) && !empty($this->field['desc']))?'<br/><br/><span class="description">'.$this->field['desc'].'</span>':'';

		?>
		<script>
			jQuery(document).ready(function($){
				$('#username').on('change',function(){
					var link = 'https://wplms.io/envato/?callback_url=<?php echo urlencode(admin_url('admin.php?page=wplms_options&tab=0')); ?>'; 
					$('#envato_auth_link').attr('href',link+'&username='+$(this).val());
				});
			});
		</script>
		<?php
		
		
	}//function
	
	
	
	/**
	 * Enqueue Function.
	 *
	 * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
	 *
	 * @since VIBE_Options 1.0
	*/
	function enqueue(){
		
		
		
		
	}//function
	
}//class
?>
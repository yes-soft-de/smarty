jQuery(document).ready(function(){

	jQuery('.field_text_upload_switch input[type="checkbox"]').change(function(){
				
		console.log(this.checked+'<--');
				var $this = jQuery(this).parent().parent();
				var textarea = $this.find('.field_text_upload_textarea textarea');
				var img = $this.find('.field_text_upload_img input');

				if(this.checked){
					
					$this.parent().find('.field_text_upload_textarea').show(200);
					textarea.attr("name",textarea.attr("rel-name"));
					$this.parent().find('.field_text_upload_img').hide(200);
					img.attr("rel-name",img.attr("name"));
					img.attr("name","");

				}else{
					$this.parent().find('.field_text_upload_textarea').hide(200);
					textarea.attr("rel-name",textarea.attr("name"));
					textarea.attr("name","");

					$this.parent().find('.field_text_upload_img').show(200);
					img.attr("name",img.attr("rel-name"));

				}; 
		});
	});
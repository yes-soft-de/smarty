<?php

// loads the shortcodes class, wordpress is loaded with it
require_once( 'shortcodes.class.php' );

// get popup type
$popup = trim( $_GET['popup'] );
$shortcode = new vibe_shortcodes( $popup );

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
</head>
<body>
<div id="vibe-popup">

	<div id="vibe-shortcode-wrap">
		
		<div id="vibe-sc-form-wrap">
		
			<div id="vibe-sc-form-head">
			
				<?php echo $shortcode->popup_title; ?>
			
			</div>
			<form method="post" id="vibe-sc-form">
			
				<table id="vibe-sc-form-table">
				
					<?php echo $shortcode->output; ?>
					
					<tbody>
						<tr class="form-row">
							<?php if( ! $shortcode->has_child ) : ?><td class="label">&nbsp;</td><?php endif; ?>
							<td class="field"><a href="#" class="vibe-save vibe-insert"><?php _e('Insert Shortcode','vibe-shortcodes'); ?></a></td>							
						</tr>
					</tbody>
				
				</table>
				<!-- /#vibe-sc-form-table -->
				
			</form>
			<!-- /#vibe-sc-form -->
		
		</div>
		<!-- /#vibe-sc-form-wrap -->
		
		<div class="clear"></div>
		
	</div>
	<!-- /#vibe-shortcode-wrap -->

</div>
<!-- /#vibe-popup -->

</body>
</html>
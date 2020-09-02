<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/js/bootstrap.min.js"></script>

<div class="wrap">
	<div class="tl-admin-content">

		<div class="tl-admin-options">
	
			<h2><?php _e('Welcome! Let\'s integrate TalentLMS with WordPress', 'talentlms'); ?></h2>
	
			<br />
			<br />
		
			<div class="tl-admin-options-grid">
				<a href="<?php echo admin_url('admin.php?page=talentlms-setup'); ?>">
					<div class="tl-admin-option">
						<i class="fa fa-cog fa-3x"></i>
						<h3><?php _e('Setup', 'talentlms'); ?></h3>
						<p><?php _e('Connect TalentLMS with your WordPress site', 'talentlms'); ?></p>
					</div>
				</a>

                <a href="<?php echo admin_url('admin.php?page=talentlms-integrations'); ?>">
                    <div class="tl-admin-option">
                        <i class="fa fa-check-square-o fa-3x"></i>
                        <h3><?php _e('Integrations', 'talentlms'); ?></h3>
                        <p><?php _e('Integrate your plugin with other WP plugins', 'talentlms'); ?></p>
                    </div>
                </a>

<!--                <a href="--><?php //echo admin_url('admin.php?page=talentlms-css'); ?><!--">-->
<!--				<div class="tl-admin-option">-->
<!--					<i class="fa fa-check-square-o fa-3x"></i>-->
<!--					<h3>--><?php //_e('CSS', 'talentlms'); ?><!--</h3>-->
<!--					<p>--><?php //_e('Customize the plugin appearance', 'talentlms'); ?><!--</p>-->
<!--				</div>-->
<!--				</a>-->

                <a href="javascript:void(0);">
				<div class="tl-admin-option" data-toggle="modal" data-target="#shortcodesModal">
					<i class="fa fa-code fa-3x"></i>
					<h3><?php _e('Shortcodes', 'talentlms'); ?></h3>
					<p><?php _e('Shortcodes to use with your WordPress site.', 'talentlms'); ?></p>
				</div>
				</a>
				<!-- snom 7-->
				<a href="<?php echo admin_url('widgets.php'); ?>">
				<div class="tl-admin-option">
					<i class="fa fa-cogs fa-3x"></i>
					<h3><?php _e('Widgets', 'talentlms'); ?></h3>
					<p><?php _e('Insert TalentLMS widget in any registered sidebar of your site.', 'talentlms'); ?></p>
				</div>
				</a>

                <a href="javascript:void(0);">
				<div class="tl-admin-option" data-toggle="modal" data-target="#helpModal">
					<i class="fa fa-question-circle fa-3x"></i>
					<h3><?php _e('Help', 'talentlms'); ?></h3>
					<p><?php _e('Instructions and best practices', 'talentlms'); ?></p>
				</div>
				</a>
			</div>
		</div><!-- tl-admin-options -->
		
		
		<div class="tl-admin-footer">
		</div><!-- .tl-admin-footer -->	
	
		<div class="modal" id ="shortcodesModal" aria-labelledby="modal-label" tabindex="0">
			<span id="modal-label" class="screen-reader-text"><?php _e('Press Esc to close.', 'talentlms'); ?></span>
			<a href="#" class="close" data-dismiss="modal">&times; <span class="screen-reader-text"><?php _e('Close modal window', 'talentlms'); ?></span></a>
			<div class="content-container ">
				<div class="content">
					<h2>ShortCodes</h2>
					<p><?php _e('Here is a list of all available shortcodes with the TalentLMS WordPress plugin. Use these shortcodes in any WordPress posts or pages', 'talentlms'); ?></p>
					<ul>
						<li>
							<p><strong>[talentlms-courses]</strong>&nbsp;<?php _e('Shortcode for listing your TalentLMS courses.', 'talentlms'); ?></p>
						</li>
<!--    					<li>-->
<!--    						<p><strong>[talentlms-signup]</strong>&nbsp;--><?php //_e('Shortcode for outputing a signup to TalentLMS form.', 'talentlms'); ?><!--</p>-->
<!--    					</li>-->
<!--    					<li>-->
<!--    						<p><strong>[talentlms-forgot-credentials]</strong>&nbsp;--><?php //_e('Shortcode for a forgot your TalentLMS username/password form', 'talentlms'); ?><!--</p>-->
<!--    					</li>-->
<!--    					<li>-->
<!--    						<p><strong>[talentlms-login]</strong>&nbsp;--><?php //_e('Shortcode for a login to TalentLMS form', 'talentlms'); ?><!--</p>-->
<!--    					</li>-->
					</ul>
					
				</div>
			</div>
			<footer>
				<ul>
					<li>
						<span class="activate">
						<a class="button-primary" href="#" data-dismiss="modal">Close</a>
						</span>
					</li>
				</ul>
			</footer>		
		</div>
		
		<div class="modal" id ="helpModal" aria-labelledby="modal-label" tabindex="0">
			<span id="modal-label" class="screen-reader-text"><?php _e('Press Esc to close.', 'talentlms'); ?></span>
			<a href="#" class="close" data-dismiss="modal">&times; <span class="screen-reader-text"><?php _e('Close modal window', 'talentlms'); ?></span></a>
			<div class="content-container ">
				<div class="content">
					<h2><?php _e('Help', 'talentlms');?></h2>
					<p><strong>TalentLMS</strong><?php _e(' is a super-easy, cloud-based learning platform to train your people and customers. This WordPress plugin is a tool you can use to diplay your TalentLMS content in WordPress.', 'talentlms')?></p>
					<p><?php _e('For more information', 'talentlms');?>:</p>
					<ul>
						<li>
							<p><strong>TalentLMS:</strong>&nbsp;<a href="http://www.talentlms.com/" target="_blank">www.talentlms.com</a></p>
						</li>
						<li>
							<p><strong>TalentLMS blog:</strong>&nbsp;<a href="http://www.talentlms.com/blog" target="_blank">blog.talentlms.com</a></p>
						</li>						
						<li>
							<p><strong>Support:</strong>&nbsp;<a href="http://support.talentlms.com/" target="_blank">support.talentlms.com</a></p>
						</li>
						<li>
							<p><strong>Contact:</strong>&nbsp;<a href="mailto: support@talentlms.com" target="_blank">support@talentlms.com</a> or use our <a href="http://www.talentlms.com/contact" target="_blank"> contact form</a></p>
						</li>
					</ul>
				</div>
			</div>
			<footer>
				<ul>
					<li>
						<span class="activate">
						<a class="button-primary" href="#" data-dismiss="modal">Close</a>
						</span>
					</li>
				</ul>
			</footer>		
		</div>		

 
	</div><!-- .tl-admin-content -->
		
</div><!-- .wrap -->
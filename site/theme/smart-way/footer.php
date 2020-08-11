<?php
    /*
        This is the template for the footer
        @package sunsettheme
    */
?>
				<div class="footer">
					<div class="container">
						<div class="row">
							<div class="col-12 col-sm-6 col-md-3">
								<h2>Smart Y</h2>
								<p>Lorem ipsum dolor sit amet</p>
							</div>
							<div class="col-12 col-sm-6 col-md-3">
								<h5 style="margin-bottom: 0;">menu</h5>
<!--								<hr class="line-">-->
								<ul class="list-unstyled">
									<?php smart_way_position_custom_nav(); ?>
								</ul>
							</div>
							<div class="col-12 col-sm-6 col-md-3">
								<h5>contact us</h5>
<!--								<hr>-->
								<p><i class="fa fa-envelope-o fa-fw"></i> Contact@lop.com</p>
								<p><i class="fa fa-phone fa-fw"></i> 856-546-987-456</p>
							</div>
							<div class="col-12 col-sm-6 col-md-3">
								<h5>follow us</h5>
<!--								<hr>-->
								<div class="row social-media-icon">
									<div class="col"><a class="icon-link" href="#"><i class="fa fa-twitter fa-lg"></i></a></div>
									<div class="col"><a class="icon-link" href="#"><i class="fa fa-facebook-square fa-lg"></i></a></div>
									<div class="col"><a class="icon-link" href="#"><i class="fa fa-instagram fa-lg"></i></a></div>
									<div class="col"><a class="icon-link" href="#"><i class="fa fa-youtube fa-lg"></i></a></div>
								</div>
							</div>
						</div>
					</div>
				</div>
        <footer class="smart-way-footer text-center">
          &copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>
	        By <a href="<?php echo get_site_url(); ?>">Yes Soft Team</a>.
        </footer>
        <?php wp_footer(); ?>
	</body>
</html>

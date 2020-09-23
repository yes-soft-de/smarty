<?php /* Template Name: New And Events */ ?>
<?php get_header() ?>
<!-- This is the standard html code that we will find in every standard wordpress theme -->
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

	<div class="news-events">

		<!-- Banner -->
		<div class="banner">
			<div class="container">
				<h1>News & Events</h1>
				<p>lets go</p>
			</div>
		</div>
		<!-- ./Banner -->

		<!-- News -->
		<div class="container">
			<h2 class="main-title">
				News
				<span>Stay uptodate with us</span>
			</h2>

	
			<?php $catquery = new WP_Query( 'cat=56&posts_per_page=5' ); ?>
			<?php
	                $x=1;
	                 
	                ?>
			<div class="sort-block">

				<?php while($catquery->have_posts()) : $catquery->the_post(); ?>
	             
				<div class="sub-news-block">
					<a href="<?php the_permalink() ?>" rel="bookmark" class="zoom-img">
						<img src="<?php echo get_template_directory_uri() . '/assets/images/inner-peace-meditation.jpg' ?>" class="img-fluid">
					</a>
					<div class="news-info">
						<h3 class="title">
							<span><?php echo $x.'-' ?></span>
							<a href="<?php the_permalink() ?>" rel="bookmark">
							<?php the_title(); ?>
							</a>
						</h3>
					<?php the_content(); ?>
						<a href="<?php the_permalink() ?>" rel="bookmark" class="more">
							Read More <i class="fa fa-arrow-right" aria-hidden="true"></i>
						</a>
						<span class="date">
						    	<svg height="512" viewBox="0 0 128 128" width="512" xmlns="http://www.w3.org/2000/svg"><g><path d="m121.693 14.088h-22.429v-7.781a1.75 1.75 0 0 0 -1.75-1.75h-7.462a1.749 1.749 0 0 0 -1.75 1.75v7.781h-18.821v-7.781a1.749 1.749 0 0 0 -1.75-1.75h-7.462a1.749 1.749 0 0 0 -1.75 1.75v7.781h-18.819v-7.781a1.749 1.749 0 0 0 -1.75-1.75h-7.464a1.75 1.75 0 0 0 -1.75 1.75v7.781h-22.429a1.749 1.749 0 0 0 -1.75 1.75v105.855a1.749 1.749 0 0 0 1.75 1.75h115.386a1.749 1.749 0 0 0 1.75-1.75v-105.855a1.749 1.749 0 0 0 -1.75-1.75zm-29.893-6.031h3.962v15.562h-3.962zm-29.783 0h3.962v15.562h-3.96zm-29.783 0h3.966v15.562h-3.964zm-24.177 9.531h20.679v7.781a1.75 1.75 0 0 0 1.75 1.75h7.462a1.749 1.749 0 0 0 1.75-1.75v-7.781h18.821v7.781a1.749 1.749 0 0 0 1.75 1.75h7.462a1.749 1.749 0 0 0 1.75-1.75v-7.781h18.819v7.781a1.749 1.749 0 0 0 1.75 1.75h7.462a1.75 1.75 0 0 0 1.75-1.75v-7.781h20.679v20.025h-111.884zm0 102.355v-78.83h111.886v78.83z"/><path d="m27.66 49.015a8.033 8.033 0 1 0 8.033 8.033 8.041 8.041 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.537 4.537 0 0 1 -4.533 4.533z"/><path d="m51.887 49.015a8.033 8.033 0 1 0 8.033 8.033 8.042 8.042 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.538 4.538 0 0 1 -4.533 4.533z"/><path d="m76.113 49.015a8.033 8.033 0 1 0 8.033 8.033 8.042 8.042 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.537 4.537 0 0 1 -4.533 4.533z"/><path d="m100.34 49.015a8.033 8.033 0 1 0 8.032 8.033 8.041 8.041 0 0 0 -8.032-8.033zm0 12.566a4.533 4.533 0 1 1 4.532-4.533 4.537 4.537 0 0 1 -4.532 4.533z"/><path d="m27.66 72.5a8.034 8.034 0 1 0 8.033 8.033 8.042 8.042 0 0 0 -8.033-8.033zm0 12.567a4.534 4.534 0 1 1 4.533-4.534 4.538 4.538 0 0 1 -4.533 4.529z"/><path d="m51.887 72.5a8.034 8.034 0 1 0 8.033 8.033 8.043 8.043 0 0 0 -8.033-8.033zm0 12.567a4.534 4.534 0 1 1 4.533-4.534 4.539 4.539 0 0 1 -4.533 4.529z"/><path d="m76.113 72.5a8.034 8.034 0 1 0 8.033 8.033 8.043 8.043 0 0 0 -8.033-8.033zm0 12.567a4.534 4.534 0 1 1 4.533-4.534 4.538 4.538 0 0 1 -4.533 4.529z"/><path d="m100.34 72.5a8.034 8.034 0 1 0 8.032 8.033 8.042 8.042 0 0 0 -8.032-8.033zm0 12.567a4.534 4.534 0 1 1 4.532-4.534 4.538 4.538 0 0 1 -4.532 4.529z"/><path d="m27.66 95.976a8.033 8.033 0 1 0 8.033 8.033 8.041 8.041 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.537 4.537 0 0 1 -4.533 4.533z"/><path d="m51.887 95.976a8.033 8.033 0 1 0 8.033 8.033 8.042 8.042 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.538 4.538 0 0 1 -4.533 4.533z"/><path d="m76.113 95.976a8.033 8.033 0 1 0 8.033 8.033 8.042 8.042 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.537 4.537 0 0 1 -4.533 4.533z"/><path d="m100.34 95.976a8.033 8.033 0 1 0 8.032 8.033 8.041 8.041 0 0 0 -8.032-8.033zm0 12.566a4.533 4.533 0 1 1 4.532-4.533 4.537 4.537 0 0 1 -4.532 4.533z"/></g></svg>
                          <?php echo get_the_date();?>
						</span>
					</div>
				</div>
				<?php $x++;endwhile; ?> 
				
			</div>	
			
			<?php wp_reset_postdata(); ?>

		</div>
		<!-- ./News -->

		<!-- Events -->
		<div class="container">
			<h2 class="main-title">
				Events
				<span>Join us</span>
			</h2>

			<?php $catquery = new WP_Query( 'cat=116&posts_per_page=5' ); ?>
			
			<div class="sort-block">

				<?php while($catquery->have_posts()) : $catquery->the_post(); ?>
	
				<div class="sub-event-block row">
					<div class="col-md-8 col-xs-12">
						<div class="row">
							<div class="col-sm-4 col-xs-12">
								<h2 class="title">
									<a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?>
									</a>
								</h2>
							</div>
							<div class="col-sm-8 col-xs-12">
								<p class="para">
									<?php the_content(); ?>
								</p>
								<span class="number">
									33 participate
								</span>
							</div>
							<div class="col-xs-12">
								<ul>
									<li>
										<svg height="512" viewBox="0 0 128 128" width="512" xmlns="http://www.w3.org/2000/svg"><g><path d="m121.693 14.088h-22.429v-7.781a1.75 1.75 0 0 0 -1.75-1.75h-7.462a1.749 1.749 0 0 0 -1.75 1.75v7.781h-18.821v-7.781a1.749 1.749 0 0 0 -1.75-1.75h-7.462a1.749 1.749 0 0 0 -1.75 1.75v7.781h-18.819v-7.781a1.749 1.749 0 0 0 -1.75-1.75h-7.464a1.75 1.75 0 0 0 -1.75 1.75v7.781h-22.429a1.749 1.749 0 0 0 -1.75 1.75v105.855a1.749 1.749 0 0 0 1.75 1.75h115.386a1.749 1.749 0 0 0 1.75-1.75v-105.855a1.749 1.749 0 0 0 -1.75-1.75zm-29.893-6.031h3.962v15.562h-3.962zm-29.783 0h3.962v15.562h-3.96zm-29.783 0h3.966v15.562h-3.964zm-24.177 9.531h20.679v7.781a1.75 1.75 0 0 0 1.75 1.75h7.462a1.749 1.749 0 0 0 1.75-1.75v-7.781h18.821v7.781a1.749 1.749 0 0 0 1.75 1.75h7.462a1.749 1.749 0 0 0 1.75-1.75v-7.781h18.819v7.781a1.749 1.749 0 0 0 1.75 1.75h7.462a1.75 1.75 0 0 0 1.75-1.75v-7.781h20.679v20.025h-111.884zm0 102.355v-78.83h111.886v78.83z"/><path d="m27.66 49.015a8.033 8.033 0 1 0 8.033 8.033 8.041 8.041 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.537 4.537 0 0 1 -4.533 4.533z"/><path d="m51.887 49.015a8.033 8.033 0 1 0 8.033 8.033 8.042 8.042 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.538 4.538 0 0 1 -4.533 4.533z"/><path d="m76.113 49.015a8.033 8.033 0 1 0 8.033 8.033 8.042 8.042 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.537 4.537 0 0 1 -4.533 4.533z"/><path d="m100.34 49.015a8.033 8.033 0 1 0 8.032 8.033 8.041 8.041 0 0 0 -8.032-8.033zm0 12.566a4.533 4.533 0 1 1 4.532-4.533 4.537 4.537 0 0 1 -4.532 4.533z"/><path d="m27.66 72.5a8.034 8.034 0 1 0 8.033 8.033 8.042 8.042 0 0 0 -8.033-8.033zm0 12.567a4.534 4.534 0 1 1 4.533-4.534 4.538 4.538 0 0 1 -4.533 4.529z"/><path d="m51.887 72.5a8.034 8.034 0 1 0 8.033 8.033 8.043 8.043 0 0 0 -8.033-8.033zm0 12.567a4.534 4.534 0 1 1 4.533-4.534 4.539 4.539 0 0 1 -4.533 4.529z"/><path d="m76.113 72.5a8.034 8.034 0 1 0 8.033 8.033 8.043 8.043 0 0 0 -8.033-8.033zm0 12.567a4.534 4.534 0 1 1 4.533-4.534 4.538 4.538 0 0 1 -4.533 4.529z"/><path d="m100.34 72.5a8.034 8.034 0 1 0 8.032 8.033 8.042 8.042 0 0 0 -8.032-8.033zm0 12.567a4.534 4.534 0 1 1 4.532-4.534 4.538 4.538 0 0 1 -4.532 4.529z"/><path d="m27.66 95.976a8.033 8.033 0 1 0 8.033 8.033 8.041 8.041 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.537 4.537 0 0 1 -4.533 4.533z"/><path d="m51.887 95.976a8.033 8.033 0 1 0 8.033 8.033 8.042 8.042 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.538 4.538 0 0 1 -4.533 4.533z"/><path d="m76.113 95.976a8.033 8.033 0 1 0 8.033 8.033 8.042 8.042 0 0 0 -8.033-8.033zm0 12.566a4.533 4.533 0 1 1 4.533-4.533 4.537 4.537 0 0 1 -4.533 4.533z"/><path d="m100.34 95.976a8.033 8.033 0 1 0 8.032 8.033 8.041 8.041 0 0 0 -8.032-8.033zm0 12.566a4.533 4.533 0 1 1 4.532-4.533 4.537 4.537 0 0 1 -4.532 4.533z"/></g></svg>
										<?php the_date()?>
									</li>
									<li>
										<svg id="Capa_1" enable-background="new 0 0 443.294 443.294" height="512" viewBox="0 0 443.294 443.294" width="512" xmlns="http://www.w3.org/2000/svg"><path d="m221.647 0c-122.214 0-221.647 99.433-221.647 221.647s99.433 221.647 221.647 221.647 221.647-99.433 221.647-221.647-99.433-221.647-221.647-221.647zm0 415.588c-106.941 0-193.941-87-193.941-193.941s87-193.941 193.941-193.941 193.941 87 193.941 193.941-87 193.941-193.941 193.941z"/><path d="m235.5 83.118h-27.706v144.265l87.176 87.176 19.589-19.589-79.059-79.059z"/></svg>
										8:00 pm
									</li>
									<li>
										<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
											viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
											<g>
												<g>
													<path d="M256,0C156.748,0,76,80.748,76,180c0,33.534,9.289,66.26,26.869,94.652l142.885,230.257
														c2.737,4.411,7.559,7.091,12.745,7.091c0.04,0,0.079,0,0.119,0c5.231-0.041,10.063-2.804,12.75-7.292L410.611,272.22
														C427.221,244.428,436,212.539,436,180C436,80.748,355.252,0,256,0z M384.866,256.818L258.272,468.186l-129.905-209.34
														C113.734,235.214,105.8,207.95,105.8,180c0-82.71,67.49-150.2,150.2-150.2S406.1,97.29,406.1,180
														C406.1,207.121,398.689,233.688,384.866,256.818z"/>
												</g>
											</g>
											<g>
												<g>
													<path d="M256,90c-49.626,0-90,40.374-90,90c0,49.309,39.717,90,90,90c50.903,0,90-41.233,90-90C346,130.374,305.626,90,256,90z
														M256,240.2c-33.257,0-60.2-27.033-60.2-60.2c0-33.084,27.116-60.2,60.2-60.2s60.1,27.116,60.1,60.2
														C316.1,212.683,289.784,240.2,256,240.2z"/>
												</g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
											<g>
											</g>
										</svg>
									<?php echo get_post_meta($post->ID, 'pass', true); ?>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<div class="col-md-4 col-xs-12">
						<a href="<?php the_permalink() ?>" rel="bookmark" class="zoom-img">
							<img src="<?php echo get_template_directory_uri() . '/assets/images/inner-peace-meditation.jpg' ?>" class="img-fluid">
						</a> 
					</div>
				</div>
				

				<?php endwhile; ?> 
				
			</div>	
			
			<?php wp_reset_postdata(); ?>

		</div>
		<!-- ./Events -->


	</div>
		
	</main>
</div><!--#primary-->

<?php get_footer() ?>

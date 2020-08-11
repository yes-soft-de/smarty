<?php

	// Bring The Header
	get_header();
	echo '<div class="container">';

	echo '<h1 class="author-header text-center mt-4 text-secondary">';
			the_author_meta('nickname');
		echo ' Page</h1>';
		// Start Author Section
		echo '<div class="author-page">';
			echo '<div class="row">';
				echo '<div class="col-md-3">';
				echo '</div>';
				echo '<div class="col-md-9">';
					echo '<ul class="list-unstyled">';
						echo '<li>First Name : ';
							the_author_meta('first_name'); // print first name
						echo '</li>';
						echo '<li>Last Name : ';
							the_author_meta('last_name'); // Print last name
						echo '</li>';
						echo '<li>Nick Name : ';
							the_author_meta('nickname'); // Print Nick Name
						echo '</li>';
					echo '</ul>';
					echo '<hr>';
					echo '<p>';
						if (get_the_author_meta('description')) {
							the_author_meta('description');
						} else {
							echo 'There Is No Biography';
						}
					echo '</p>';
				echo '</div>';
			echo '</div>'; // End Row Div
		echo '</div>';
		// End Author Section




	echo '</div>'; // End Container
	// Bring The Footer
	get_footer();	

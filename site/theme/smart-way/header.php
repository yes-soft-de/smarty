<?php
	/*
	    This is the template for the header
        @package sunsettheme
    */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="<?php bloginfo( 'description' );?>">
        <title><?php bloginfo( 'name' ); wp_title(); ?></title>
        <link rel="shortcut icon" href="<?php echo get_template_directory_uri() . '/img/sunset-icon.png'; ?>">
		<link rel="profile" href="http://gmpg.org/xfn/11">
        <!-- Check If The pingback is enable or not in the backend and check if the page is single post or single page-->
		<?php if( is_singular() && pings_open( get_queried_object() ) ): ?>
			<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
		<?php endif; ?>
		<?php wp_head(); ?>

        <?php
            // Get The Css from Admin Page (custom css) That We Created
            $custom_css = esc_attr( get_option( 'sunset_css' ) );
            if( !empty( $custom_css ) ):
                echo '<style>' . $custom_css . '</style>';
            endif;
		?>
	</head>
    <body <?php body_class(); ?>>
      <nav class="navbar navbar-expand-lg custom-navbar">
        <div class="container">
          <a class="navbar-brand" href="<?php echo get_site_url(); ?>">
            <img class="responsive-element rounded" src="<?php echo get_template_directory_uri() . '/img/Logo.png' ?>" alt="logo" loading="lazy">
          </a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse navbar-sunset" id="navbarSupportedContent">
              <?php smart_way_position_custom_nav(); ?>
          </div>
        </div>
      </nav>
      <!--End NavBar-->

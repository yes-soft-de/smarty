<?php
/*
    This is the template for the Sidebar
      @package smartway
  */

// Security Line, Check If The sidebar is active
if ( ! is_active_sidebar('smart-way-sidebar' ) ) {
	return;
}
?>

<aside id="secondary" class="widget-area" role="complementary">
	<?php dynamic_sidebar( 'smart-way-sidebar' ); ?>
</aside>

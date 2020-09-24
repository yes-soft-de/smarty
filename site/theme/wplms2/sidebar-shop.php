<?php
if ( is_shop() ) :
?>
<div class="shopsidebar">
    <?php
    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar('shop') ) : ?>
   <?php endif; ?>
</div>
<?php
else:
?>
<div class="productsidebar">
    <?php
    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar('product') ) : ?>
   <?php endif; ?>
</div>

<?php
endif;
?>
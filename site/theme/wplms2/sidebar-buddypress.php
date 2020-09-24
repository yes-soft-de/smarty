<?php
if (  bp_is_my_profile() ) :
?>
<div class="buddysidebar">
    <?php
    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar('buddypress-profile') ) : ?>
   <?php endif; ?>
</div>
<?php
else:
?>
<div class="buddysidebar">
    <?php
    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar('buddypress') ) : ?>
   <?php endif; ?>
</div>

<?php
endif;
?>
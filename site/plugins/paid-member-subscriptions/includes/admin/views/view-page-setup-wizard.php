<?php
if ( ! defined( 'ABSPATH' ) ) exit;

set_current_screen();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta name="viewport" content="width=device-width" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php esc_html_e( 'Setup Wizard -> Paid Member Subscriptions', 'paid-member-subscriptions' ); ?></title>
    <?php
        wp_enqueue_style( 'colors' );
        do_action( 'admin_enqueue_scripts' );
        do_action( 'admin_print_styles' );
        do_action( 'admin_head' );
    ?>
</head>
<body class="pms-custom-page wp-admin wp-core-ui">
    <div class="pms-setup-wrap">

        <img src="<?php echo PMS_PLUGIN_DIR_URL; ?>assets/images/pms-banner.png" alt="Paid Member Subscriptions" style="object-type:cover;height:100%;width:100%;"/>

        <ul class="pms-setup-steps">
            <?php foreach( $this->steps as $step => $label ) :
                //if current step index is greater than the loop step index, we know that the loop step is completed
                $completed = array_search( $this->step, array_keys( $this->steps ), true ) > array_search( $step, array_keys( $this->steps ), true );

                if( $this->step === $step ) : ?>
                    <li class="active"><?php echo esc_html( $label ); ?></li>
                <?php elseif( $completed ) : ?>
                    <li class="active">
                        <a href="<?php echo esc_url( add_query_arg( 'step', $step ) ); ?>"><?php echo esc_html( $label ); ?></a>
                    </li>
                <?php else : ?>
                    <li><?php echo esc_html( $label ); ?></li>
                <?php endif;
            endforeach; ?>
        </ul>

        <div class="pms-setup-content">
            <?php include_once 'setup-wizard/view-tab-' . $this->step . '.php'; ?>
        </div>
    </div>

    <div class="pms-setup-skip">
        <div class="pms-setup-skip__action">
            <a href="<?php echo admin_url(); ?>"><?php _e( 'Skip Setup', 'paid-member-subscriptions' ); ?></a>
        </div>
    </div>

    <?php do_action( 'wp_footer' ); ?>
    <script type="text/javascript">
        var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>'
    </script>
</body>
</html>

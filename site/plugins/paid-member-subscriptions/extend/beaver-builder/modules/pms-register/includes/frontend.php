<?php
    if ( !empty( $settings->subscription_plans ) && is_array( $settings->subscription_plans ) )
        $plans = 'subscription_plans="'.implode( ',', $settings->subscription_plans ).'"';
    else if ( !empty( $settings->subscription_plans ) )
        $plans = 'subscription_plans="' . $settings->subscription_plans .'"';
    else
        $plans = '';

    echo do_shortcode( '[pms-register '.$plans.' selected="'.$settings->selected_subscription.'" plans_position="'.$settings->plans_position.'"]' );
?>

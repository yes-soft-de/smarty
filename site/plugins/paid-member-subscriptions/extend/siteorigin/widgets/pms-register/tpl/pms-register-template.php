<?php

    if ( !empty( $instance['subscription_plans'] ) && is_array( $instance['subscription_plans'] ))
        $plans = 'subscription_plans="'.implode( ',', $instance['subscription_plans'] ).'"';
    else if ( !empty( $instance['subscription_plans'] ) )
        $plans = 'subscription_plans="' . $instance['subscription_plans'] .'"';
    else
        $plans = '';

    echo do_shortcode( '[pms-register '.$plans.' selected="'.$instance['selected_plan'].'" plans_position="'.$instance['plans_position'].'"]' );

?>

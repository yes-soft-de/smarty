<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/*
 * HTML output for the reports admin page
 */
?>

<div class="wrap">

    <h1><?php echo $this->page_title; ?></h1>

    <h2 class="nav-tab-wrapper">
        <a href="<?php echo admin_url( 'admin.php?page=pms-reports-page' ); ?>" class="nav-tab <?php echo $active_tab == 'pms-reports-page' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Reports', 'paid-member-subscriptions' ); ?></a>
        <a href="<?php echo admin_url( 'admin.php?page=pms-export-page' ); ?>"  class="nav-tab <?php echo $active_tab == 'pms-export-page' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Export', 'paid-member-subscriptions' ); ?></a>
        <?php do_action( 'pms_reports_tab' ); ?>
    </h2>

    <div id="dashboard-widgets-wrap">
        <div class="metabox-holder">
            <div id="post-body">
                <div id="post-body-content">


                    <div class="postbox pms-export">
                        <h3><span><?php _e( 'Members Export', 'paid-member-subscriptions' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV with your user subscriptions (an user with multiple subscriptions will have a record for each individual one).', 'paid-member-subscriptions' ); ?></p>
                            <form id="pms-export" class="pms-export-form " method="post">
                                <?php wp_nonce_field( 'pms_ajax_export', 'pms_ajax_export' ); ?>
                                <input type="hidden" name="pms-export-class" value="PMS_Batch_Export_Members"/>
                                <p>
                                <?php

                                $subscription_plans = pms_get_subscription_plans( false );
                                echo '<select name="pms-filter-subscription-plan" class="pms-export-filter">';
                                echo '<option value="0">' . __( 'All Subscriptions', 'paid-member-subscriptions' ) . '</option>';

                                foreach( $subscription_plans as $subscription_plan )
                                    echo '<option value="' . $subscription_plan->id . '">' . $subscription_plan->name . '</option>';
                                echo '</select> ';
                                _e('Choose the Subscription to export members from', 'paid-member-subscriptions');
                                ?>
                                </p>

                                <p>
                                    <select name="pms-filter-member-status" class="pms-export-filter">
                                        <option value="0"><?php _e( 'All Members', 'paid-member-subscriptions' ); ?></option>
                                        <option value="active"><?php _e( 'Active', 'paid-member-subscriptions' ); ?></option>
                                        <option value="canceled"><?php _e( 'Canceled', 'paid-member-subscriptions' ); ?></option>
                                        <option value="expired"><?php _e( 'Expired', 'paid-member-subscriptions' ); ?></option>
                                        <option value="pending"><?php _e( 'Pending', 'paid-member-subscriptions' ); ?></option>
                                    </select>

                                    <?php _e( 'Choose the current subscription status', 'paid-member-subscriptions' ); ?>
                                </p>

                                <div id="pms-add-meta-key-wrap">
                                    <div id="pms-add-meta-key-container">

                                        <?php
                                        $pms_export_meta = get_user_meta(get_current_user_id(), 'pms_export_meta', true);
                                        $pms_export_meta = (empty($pms_export_meta)) ? [] : $pms_export_meta;

                                        foreach($pms_export_meta as $key => $value){
                                            ?>

                                            <div class="pms-add-meta-key-row">
                                                <label><?php _e( 'Column title', 'paid-member-subscriptions' ); ?><br/><input type="text" name="pms-filter-user-meta-title[]" value="<?php echo $value; ?>"></label>
                                                <label>
                                                    <?php _e( 'User meta key', 'paid-member-subscriptions' ); ?><br>
                                                    <select name="pms-filter-user-meta[]" class="pms-export-filter pms-chosen">
                                                        <option value="0"><?php _e( '...Choose', 'paid-member-subscriptions' ); ?></option>
                                                        <?php
                                                        foreach (PMS_Submenu_Page_Export::get_all_user_meta_keys() as $umeta_key){
                                                            $selected = selected( $key, $umeta_key['meta_key'] );
                                                            echo "<option {$selected} value='{$umeta_key['meta_key']}'>{$umeta_key['meta_key']}</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </label>
                                                <label class="pms-remove-meta-from-export"><span class="dashicons dashicons-no"></span></label>
                                            </div>

                                            <?php
                                        }
                                        ?>

                                    </div>
                                    <template id="pms-add-meta-row-tpl">
                                    <div class="pms-add-meta-key-row">
                                        <label><?php _e( 'Column title', 'paid-member-subscriptions' ); ?><br/><input type="text" name="pms-filter-user-meta-title[]"></label>
                                        <label>
                                            <?php _e( 'User meta key', 'paid-member-subscriptions' ); ?><br>
                                            <select name="pms-filter-user-meta[]" class="pms-export-filter pms-chosen">
                                                <option value="0"><?php _e( '...Choose', 'paid-member-subscriptions' ); ?></option>
                                            <?php
                                            foreach (PMS_Submenu_Page_Export::get_all_user_meta_keys() as $umeta_key){
                                                echo "<option value='{$umeta_key['meta_key']}'>{$umeta_key['meta_key']}</option>";
                                            }
                                            ?>
                                            </select>
                                        </label>
                                        <label class="pms-remove-meta-from-export"><span class="dashicons dashicons-no"></span></label>
                                    </div>
                                    </template>

                                    <a href="#" class="button-secondary" id="pms-add-meta-button" title="Adds another column to the export containing the information found inside a particular user meta key">Add User Meta Column</a>
                                </div>

                                <span>
									<input type="submit" value="<?php _e( 'Generate CSV', 'paid-member-subscriptions' ); ?>"
                                           class="button-primary"/>
									<span class="spinner"></span>
								</span>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->


                </div><!-- .post-body-content -->
            </div><!-- .post-body -->
        </div><!-- .metabox-holder -->

        <div class="metabox-holder">
            <div id="post-body">
                <div id="post-body-content">


                    <div class="postbox pms-export">
                        <h3><span><?php _e( 'Payments Export', 'paid-member-subscriptions' ); ?></span></h3>
                        <div class="inside">
                            <p><?php _e( 'Download a CSV with your payments.', 'paid-member-subscriptions' ); ?></p>
                            <form id="pms-export" class="pms-export-form " method="post">
                                <?php wp_nonce_field( 'pms_ajax_export', 'pms_ajax_export' ); ?>
                                <input type="hidden" name="pms-export-class" value="PMS_Batch_Export_Payments"/>
                                <p>
                                    <?php
                                    echo '<select name="pms-filter-payment-status" class="pms-export-filter">';
                                    echo '<option value="0">' . __( 'All Payments', 'paid-member-subscriptions' ) . '</option>';
                                    echo '<option value="completed">' . __( 'Completed', 'paid-member-subscriptions' ) . '</option>';
                                    echo '<option value="pending">' . __( 'Pending', 'paid-member-subscriptions' ) . '</option>';
                                    echo '<option value="refunded">' . __( 'Refunded', 'paid-member-subscriptions' ) . '</option>';
                                    echo '</select> ';
                                    _e('Choose the payment status', 'paid-member-subscriptions');
                                    ?>
                                </p>

                                <p style="display: inline-block; min-width: 200px">
                                    <label for="pms-filter-date-start"><?php _e('Start Date', 'paid-member-subscriptions'); ?></label><br/>
                                    <input name="pms-filter-start-date" type="date" class="pms-export-filter">
                                </p>
                                <p style="display: inline-block; min-width: 200px">
                                    <label for="pms-filter-end-date"><?php _e('End Date', 'paid-member-subscriptions'); ?></label><br/>
                                    <input name="pms-filter-end-date" type="date" class="pms-export-filter">
                                </p>
                                <p>
                                    <?php _e( 'Leave dates empty for an export of all payments.', 'paid-member-subscriptions' ); ?>
                                </p>
                                <p>
									<input type="submit" value="<?php _e( 'Generate CSV', 'paid-member-subscriptions' ); ?>" class="button-primary"/>
									<span class="spinner"></span>
								</p>
                            </form>
                        </div><!-- .inside -->
                    </div><!-- .postbox -->


                </div><!-- .post-body-content -->
            </div><!-- .post-body -->
        </div><!-- .metabox-holder -->

    </div><!-- #dashboard-widgets-wrap -->

    <?php do_action( 'pms_export_page_bottom' ); ?>

</div>

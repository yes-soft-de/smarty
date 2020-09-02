<div class="wrap">

	<?php if(get_option('tlms-woocommerce-active') ) : ?>


        <h1><?php _e('Integrations', 'talentlms'); ?></h1>

        <div id='action-message' class='<?php echo (isset($action_status))? $action_status : ''; ?> fade'>
            <p><?php echo (isset($action_message)) ? $action_message : '' ?></p>
        </div>


        <h2>WooCommerce</h2>

        <!-- form to post to get new courses -->
        <form action="<?php echo admin_url('admin.php?page=talentlms-integrations'); ?>" method="POST">
            <input type="hidden" name="action" value="tlms-fetch-courses">
            <input type="submit" class="btn button-primary" value="<?php _e('Refresh course list', 'talentlms'); ?>">
        </form>

        <br />

        <form action="<?php echo admin_url('admin.php?page=talentlms-integrations'); ?>" method="POST" name="form">

            <table id="tlms-integrations-table" class="wp-list-table widefat fixed striped" cellspacing="0">
                <thead>
                <tr>
                    <th class="manage-column column-title" scope="col" width="150">
						<?php _e('Course', 'talentlms'); ?>
                    </th>
                    <th class="manage-column check-column" scope="col" style="text-align: center; padding: 10px 0;">
                        <a id="tlms-integrate-all" href="javascript:void(0);"><?php _e('Select All', 'talentlms');?></a>
                    </th>
                </tr>
                </thead>
                <tbody>
				<?php foreach ($courses as $course) : ?>
                    <?php if(!$course->hide_catalog && $course->status == 'active') : ?>
                    <tr>
                        <td class="column-title">
							<?php echo $course->name?>

                            <div class="row-actions">
                                <span class="inline hide-if-no-js">
                                    <a class="tlms-reset-course" data-course-id="<?php echo $course->id; ?>" href="#"><?php _e('Re-Sync', 'talentlms'); ?></a>
                                </span>
                            </div>

                        </td>
                        <td class="check-column" style="text-align: center">
                            <input type='checkbox' class="tlms-products" name="tlms_products[]" value="<?php echo $course->id; ?>" <?php echo (tlms_productExists($course->id)) ? 'checked' : ''; ?>/>
                        </td>
                    </tr>
                    <?php endif;?>
				<?php endforeach;?>
                </tbody>
            </table>

            <p class="submit">
                <input class="button button-primary" type="submit" value="<?php _e('Integrate', 'talentlms')?>" title="<?php _e('add selected courses as woocommerce products', 'talentlms')?>">
            </p>

        </form>

	<?php else: ?>

        <div id='action-message' class='error fade'>
            <p><?php _e('WooCommerce is not activated please activate the plugin to use the integrations page', 'talentlms'); ?></p>
        </div>


	<?php endif; ?>
</div>



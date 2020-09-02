<?php if($tl_signup_failed):?>
    <div class="alert alert-error">
        <strong><?php _e('Something is wrong!');?></strong> <?php echo $tl_signup_fail_message; ?>
    </div>
<?php endif;?>

<form role="form" id="tl-singup-form" method="post" action="<?php echo get_page_link(get_option('tl_signup_page_id'));?>">
	<input type="hidden" name="tl-signup-post" value="1"/>

    <div class="tl-form-group <?php echo $first_name_error_class; ?>">
        <label for="first-name"><?php _e('First Name', 'talentlms'); ?></label>
        <input class="tl-form-control" type="text" id="first-name" name="first-name" value="<?php echo $_POST['first-name']; ?>"/>
        <span class="tl-help-inline"><?php echo $first_name_error; ?></span>
    </div>

    <div class="tl-form-group <?php echo $last_name_error_class; ?>">
        <label for="last-name"><?php _e('Last Name', 'talentlms'); ?></label>
        <input class="tl-form-control" type="text" id="last-name" name="last-name" value="<?php echo $_POST['last-name']; ?>"/>
        <span class="tl-help-inline"><?php echo $last_name_error; ?></span>
    </div>

    <div class="tl-form-group <?php echo $email_error_class; ?>">
        <label for="email"><?php _e('Email', 'talentlms'); ?></label>
        <input class="tl-form-control" type="text" id="email" name="email" value="<?php echo $_POST['email']; ?>"/>
        <span class="tl-help-inline"><?php echo $email_error; ?></span>
    </div>

    <hr />

    <div class="tl-form-group <?php echo $login_error_class; ?>">
        <label for="login"><?php _e('Login', 'talentlms'); ?></label>
        <input class="tl-form-control" type="text" id="login" name="login" value="<?php echo $_POST['login']; ?>"/>
        <span class="tl-help-inline"><?php echo $login_error; ?></span>
    </div>

    <div class="tl-form-group <?php echo $password_error_class; ?>">
        <label for="password"><?php _e('Password', 'talentlms'); ?></label>
        <input class="tl-form-control" type="password" id="password" name="password" value="<?php echo $_POST['password']; ?>"/>
        <span class="tl-help-inline"><?php echo $password_error; ?></span>
    </div>

    <?php if (is_array($custom_fields)) :?>
        <hr />
        <?php foreach ($custom_fields as $custom_field) : ?>
            <?php if($custom_field['type'] == 'text') :?>
                <div class="tl-form-group <?php echo $custom_field['error_class']; ?>">
                    <label for="<?php echo $custom_field['key'];?>"><?php echo $custom_field['name']; ?></label>
                    <input class="tl-form-control" type="text" id="<?php echo $custom_field['key'];?>" name="<?php echo $custom_field['key'];?>" value="<?php echo $_POST[$custom_field['key']];?>"/>
                    <span class="tl-help-inline"><?php echo $custom_field['error']; ?></span>
                </div>
            <?php elseif ($custom_field['type'] == 'dropdown') :?>
                <?php
                    $dropdown_values = explode(';', $custom_field['dropdown_values']);
                    foreach ($dropdown_values as $value) {
                        if (preg_match('/\[(.*?)\]/', $value, $match)) {
                            $default_value = $match[1];
                            $value = $default_value;
                        }
                        $options[$value] = $value;
                    }
                ?>

                <div class="tl-form-group">
                    <label for="<?php echo $custom_field['key'];?>"><?php echo $custom_field['name']; ?></label>
                    <select class="tl-form-control" id="<?php echo $custom_field['key']; ?>" name="<?php echo $custom_field['key'];?>">
                        <?php foreach ($options as $key => $option):?>
                            <option value="<?php echo trim($key)?>" <?php echo ($default_value == $option) ? "selected='selected'" : ''; ?>><?php echo trim($option)?></option>
                        <?php unset($options); endforeach;?>
                    </select>
                    <span class="tl-help-inline"><?php echo $custom_field['error']; ?></span>
                </div>

            <?php elseif ($custom_field['type'] == 'checkbox') :?>

                <div class="tl-form-group">
                    <label for="<?php echo $custom_field['key'];?>"><?php echo $custom_field['name']; ?></label>
                    <?php if (trim($custom_field['checkbox_status']) == 'on') : ?>
                        <input type="checkbox" id="<?php echo $custom_field['key']; ?>" name="<?php echo $custom_field['key']; ?>" checked='checked' value="<?php echo $custom_field['checkbox_status']; ?>" />
                    <?php else: ?>
                        <input type="checkbox" id="<?php echo $custom_field['key']; ?>" name="<?php echo $custom_field['key']; ?>" value="<?php echo $custom_field['checkbox_status']; ?>" />
                    <?php endif;?>

                    <span class="tl-help-inline"><?php echo $custom_field['error']; ?></span>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <input class="btn" type="submit" value="<?php _e('Create account', 'talentlms'); ?>"/>

</form>
<?php
   
  if ( is_user_logged_in() && function_exists('bp_loggedin_user_avatar')) :
    do_action( 'bp_before_sidebar_me' ); ?>
    <div id="sidebar-me">
      <div id="bpavatar">
        <?php bp_loggedin_user_avatar( 'type=full' ); 
        $show_view_profile = apply_filters('wplms_sidebarme_show_view_profile',1);
        ?>
      </div>
      <ul>
        <li id="username"><a href="<?php bp_loggedin_user_link(); ?>"><?php bp_loggedin_user_fullname(); ?></a></li>
        <?php do_action('wplms_header_top_login'); ?>
        <?php if($show_view_profile){?>
          <li><a href="<?php echo bp_loggedin_user_domain() . BP_XPROFILE_SLUG ?>/" title="<?php _e('View profile','vibe'); ?>"><?php _e('View profile','vibe'); ?></a></li>
        <?php } ?>
        <li id="vbplogout"><a href="<?php echo wp_logout_url( get_permalink() ); ?>" id="destroy-sessions" rel="nofollow" class="logout" title="<?php _e( 'Log Out','vibe' ); ?>"><i class="icon-close-off-2"></i> <?php _e('LOGOUT','vibe'); ?></a></li>
        <li id="admin_panel_icon"><?php if (current_user_can("edit_posts"))
             echo '<a href="'.vibe_site_url() .'wp-admin/" title="'.__('Access admin panel','vibe').'"><i class="icon-settings-1"></i></a>'; ?>
        </li>
      </ul> 
      <ul>
    <?php
    $loggedin_menu = array(
      'courses'=>array(
                  'icon' => 'icon-book-open-1',
                  'label' => __('Courses','vibe'),
                  'link' => bp_loggedin_user_domain().BP_COURSE_SLUG
                  ),
      'stats'=>array(
                  'icon' => 'icon-analytics-chart-graph',
                  'label' => __('Stats','vibe'),
                  'link' => bp_loggedin_user_domain().BP_COURSE_SLUG.'/'.BP_COURSE_STATS_SLUG
                  )
      );
    if ( bp_is_active( 'messages' ) ){
      $loggedin_menu['messages']=array(
                  'icon' => 'icon-letter-mail-1',
                  'label' => __('Inbox','vibe').(messages_get_unread_count()?' <span>' . messages_get_unread_count() . '</span>':''),
                  'link' => bp_loggedin_user_domain().BP_MESSAGES_SLUG
                  );
    }
    if ( bp_is_active( 'notifications' ) ){  
      $n=vbp_current_user_notification_count();
      $loggedin_menu['notifications']=array(
                  'icon' => 'icon-exclamation',
                  'label' => __('Notifications','vibe').(($n)?' <span>'.$n.'</span>':''),
                  'link' => bp_loggedin_user_domain().BP_NOTIFICATIONS_SLUG
                  );
    }
    if ( bp_is_active( 'groups' ) ){
      $loggedin_menu['groups']=array(
                  'icon' => 'icon-myspace-alt',
                  'label' => __('Groups','vibe'),
                  'link' => bp_loggedin_user_domain().BP_GROUPS_SLUG 
                  );
    }
    
    $loggedin_menu['settings']=array(
                  'icon' => 'icon-settings',
                  'label' => __('Settings','vibe'),
                  'link' => bp_loggedin_user_domain().BP_SETTINGS_SLUG
                  );
    $loggedin_menu = apply_filters('wplms_logged_in_top_menu',$loggedin_menu);
    foreach($loggedin_menu as $item){
      echo '<li><a href="'.$item['link'].'"><i class="'.$item['icon'].'"></i>'.$item['label'].'</a></li>';
    }
    ?>
      </ul>
    
    <?php
    do_action( 'bp_sidebar_me' ); ?>
    </div>
    <?php do_action( 'bp_after_sidebar_me' );
  
  /***** If the user is not logged in, show the log form and account creation link *****/
  
  else :
    if(!isset($user_login))$user_login=''; 
    do_action( 'bp_before_sidebar_login_form' ); ?>

    
    <form name="login-form" id="vbp-login-form" class="standard-form" action="<?php echo apply_filters('wplms_login_widget_action',site_url( 'wp-login.php', 'login_post' )); ?>" method="post">
      <div class="inside_login_form">
        <label><?php _e( 'Username', 'vibe' ); ?><br />
        <input type="text" name="log" id="side-user-login" class="input" tabindex="1" value="<?php echo esc_attr( stripslashes( $user_login ) ); ?>" /></label>
  
        <label><?php _e( 'Password', 'vibe' ); ?> <a href="<?php echo wp_lostpassword_url(); ?>" tabindex="5" class="tip vbpforgot" title="<?php _e('Forgot Password','vibe'); ?>"><i class="icon-question"></i></a><br />
        <input type="password" tabindex="2" name="pwd" id="sidebar-user-pass" class="input" value="" /></label>
  
        <div class="checkbox small">
            <input type="checkbox" name="sidebar-rememberme" id="sidebar-rememberme" value="forever" /><label for="sidebar-rememberme"><?php _e( 'Remember Me', 'vibe' ); ?></label>
        </div>
  
        <?php do_action( 'bp_sidebar_login_form' ); ?>
        
        <input type="submit" name="user-submit" id="sidebar-wp-submit" data-security="<?php echo wp_create_nonce('wplms_signon'); ?>" value="<?php _e( 'Log In','vibe' ); ?>" tabindex="100" />
        <input type="hidden" name="user-cookie" value="1" />
        <?php
            $enable_signup = apply_filters('wplms_enable_signup',0);
            if ( $enable_signup ) : 
                $registration_link = apply_filters('wplms_buddypress_registration_link',site_url( BP_REGISTER_SLUG . '/' ));
                printf( '<a href="%s" class="vbpregister" title="'.__('Create an account','vibe').'" tabindex="5" >'.__( 'Sign Up','vibe' ).'</a> ', $registration_link );
            endif; 
            do_action( 'login_form' ); //BruteProtect FIX 
        ?>
      </div>    
    </form>
    
    <?php do_action( 'bp_after_sidebar_login_form' );
  endif;

<?php do_action( 'bp_before_member_messages_loop' ); ?>

<?php if ( bp_has_message_threads( bp_ajax_querystring( 'messages' ) ) ) : ?>

	<div class="pagination no-ajax" id="user-pag">

		<div class="pag-count" id="messages-dir-count">
			<?php bp_messages_pagination_count(); ?>
		</div>

		<div class="pagination-links" id="messages-dir-pag">
			<?php bp_messages_pagination(); ?>
		</div>

	</div><!-- .pagination -->

	<?php do_action( 'bp_after_member_messages_pagination' ); ?>

	<?php do_action( 'bp_before_member_messages_threads'   ); ?>

	<table id="message-threads" class="messages-notices">
		<thead>
				<tr>
					<th></th>	
					<th scope="col" class="thread-count">
					</th>
					<?php if ( bp_is_active( 'messages', 'star' ) ) : ?>
					<th scope="col" class="thread-star"><span class="message-action-star"><span class="icon"></span> <span class="screen-reader-text"><?php _e( 'Star', 'vibe' ); ?></span></span></th>
					<?php endif; ?>
					<th scope="col" class="thread-from"><?php _e( 'From', 'vibe' ); ?></th>
					<th></th>
					<th scope="col" class="thread-info"><?php _e( 'Subject', 'vibe' ); ?></th>

					<?php

					/**
					 * Fires inside the messages box table header to add a new column.
					 *
					 * This is to primarily add a <th> cell to the messages box table header. Use
					 * the related 'bp_messages_inbox_list_item' hook to add a <td> cell.
					 *
					 * @since BuddyPress (2.3.0)
					 */
					do_action( 'bp_messages_inbox_list_header' ); ?>

					<th scope="col" class="thread-options"><?php _e( 'Actions', 'vibe' ); ?></th>
				</tr>
			</thead>
		<?php while ( bp_message_threads() ) : bp_message_thread(); ?>

			<tr id="m-<?php bp_message_thread_id(); ?>" class="<?php bp_message_css_class(); ?><?php if ( bp_message_thread_has_unread() ) : ?> unread<?php else: ?> read<?php endif; ?>">
				<td width="1%" class="thread-select">
					<input type="checkbox" name="message_ids[]" value="<?php bp_message_thread_id(); ?>" />
				</td>
				<td width="1%" class="thread-count">
					<span class="unread-count"><?php bp_message_thread_unread_count(); ?></span>
				</td>
				<?php if ( bp_is_active( 'messages', 'star' ) ) : ?>
					<td width="1%" class="thread-star">
						<?php bp_the_message_star_action_link( array( 'thread_id' => bp_get_message_thread_id() ) ); ?>
					</td>
				<?php endif; ?>
				<td width="1%" class="thread-avatar"><?php bp_message_thread_avatar(); ?></td>

				<?php if ( 'sentbox' != bp_current_action() ) : ?>
					<td width="30%" class="thread-from">
						<?php _e( 'From:', 'vibe' ); ?> <?php bp_message_thread_from(); ?><br />
						<span class="activity"><?php bp_message_thread_last_post_date(); ?></span>
					</td>
				<?php else: ?>
					<td width="30%" class="thread-from">
						<?php _e( 'To:', 'vibe' ); ?> <?php bp_message_thread_to(); ?><br />
						<span class="activity"><?php bp_message_thread_last_post_date(); ?></span>
					</td>
				<?php endif; ?>

				<td width="50%" class="thread-info">
					<p><a href="<?php bp_message_thread_view_link(); ?>" title="<?php _e( "View Message", "buddypress" ); ?>"><?php bp_message_thread_subject(); ?></a></p>
					<p class="thread-excerpt"><?php bp_message_thread_excerpt(); ?></p>
				</td>

				<?php do_action( 'bp_messages_inbox_list_item' ); ?>

				<td width="1%" class="thread-options">
					<a class="ahref confirm" href="<?php bp_message_thread_delete_link(); ?>" title="<?php _e( "Delete Message", "buddypress" ); ?>"><?php _e( 'Delete', 'vibe' ); ?></a> &nbsp;
				</td>
			</tr>

		<?php endwhile; ?>
	</table><!-- #message-threads -->

	<div class="messages-options-nav">
		<?php bp_messages_options(); ?>
	</div><!-- .messages-options-nav -->

	<?php do_action( 'bp_after_member_messages_threads' ); ?>

	<?php do_action( 'bp_after_member_messages_options' ); ?>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'Sorry, no messages were found.', 'vibe' ); ?></p>
	</div>

<?php endif;?>

<?php do_action( 'bp_after_member_messages_loop' ); ?>

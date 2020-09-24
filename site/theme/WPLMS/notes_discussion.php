<?php
/**
 * Template Name: Notes and Discussion
 */


do_action('wplms_before_notes_discussion');

get_header(vibe_get_header());

$user_id = get_current_user_id();  

//postid , user_id, status, comment_type and pagination
/*array(
	'author_email'        => '',
	'author__in'          => '',
	'author__not_in'      => '', 
	'ID'                  => '',
	'karma'               => '',
	'number'              => '',
	'offset'              => '',
	'orderby'             => '',
	'order'               => 'DESC',
	'parent'              => '',
	'post_author__in'     => '',
	'post_author__not_in' => '',
	'post_id'             => 0,
	'post_author'         => '',
	'post_name'           => '',
	'post_parent'         => '',
	'post_status'         => 'publish',
	'post_type'           => 'unit',
	'status'              => 'approve',
	'type'                => '',
	'user_id'             => $user_id,
	'search'              => '',
	'count'               => false,
	'meta_key'            => '',
	'meta_value'          => '',
	'meta_query'          => ''
)
*/
$number = vibe_get_option('loop_number');
if(!is_numeric($number))
	$number = 5;
$args = apply_filters('wplms_notes_dicussion_args',array(
	'number'              => $number,
	'post_status'         => 'publish',
	'post_type'           => 'unit',
	'status'              => 'approve',
));
?>
<section id="title">
	<?php do_action('wplms_before_title'); ?>
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-12">
                <div class="pagetitle">
                	<?php
                    $breadcrumbs=get_post_meta(get_the_ID(),'vibe_breadcrumbs',true);
                    if(vibe_validate($breadcrumbs) || empty($breadcrumbs))
                        vibe_breadcrumbs(); 
                	?>
                    <h1><?php the_title(); ?></h1>
                    <?php the_sub_title(); ?>
                </div>
            </div>
        </div>
    </div>
</section>
<section id="content">
    <div class="<?php echo vibe_get_container(); ?>">
        <div class="row">
            <div class="col-md-9 col-sm-8">
            	<form action="" method="post" id="notes-discussion-form">
					<div class="note-tabs">
						<ul>
							<li id="wplms_all"class="selected"><a href=""><?php _e('All Notes & Discussions','vibe'); ?></a></li>
							<?php $selected = 1;
							if(current_user_can('manage_options')){
								$selected =0;
								?>
								<li id="wplms_all_public_discussions"><a href=""><?php _e('All Discussions','vibe'); ?></a></li>
								<?php
							}
							if(current_user_can('edit_posts')){
								?>
								<li id="wplms_instructor_unit_notes"><a href=""><?php _e('Unit Notes','vibe'); ?></a></li>
								<li id="wplms_instructor_unit_discussions"><a href=""><?php _e('Unit Discussions','vibe'); ?></a></li>
								<?php
								$selected =0;
							}
							?>
							<li id="wplms_my_notes_public"><a href=""><?php _e('My Discussions','vibe'); ?></a></li>
							<li id="wplms_my_notes_private"><a href=""><?php _e('My Notes','vibe'); ?></a></li>
						</ul>
					</div><!-- .item-list-tabs -->
				</form>

                <div class="content">
                	<div id="notes_query"><?php echo json_encode($args); ?></div>
                	<div id="notes_discussions">
                    <?php
                    if(is_user_logged_in() || (!is_user_logged_in() && count($args['comment__in']))){
                    	$comments_query = new WP_Comment_Query;
						$comments = $comments_query->query( $args );

						// Comment Loop
						$vibe_notes_discussions= new vibe_notes_discussions();
						$vibe_notes_discussions->comments_loop($comments);
                    }else{
                    	?>
                    	<div class="message"><?php _e('No public comments found !','vibe') ?></div>
                    	<?php
                    }
					
					?>
					</div>
                </div>
            </div>
            <div class="col-md-3 col-sm-4">
                <div class="sidebar">
                    <?php
                    $sidebar = apply_filters('wplms_sidebar','mainsidebar',get_the_ID());
                    if ( !function_exists('dynamic_sidebar')|| !dynamic_sidebar($sidebar) ) : ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
get_footer(vibe_get_footer());
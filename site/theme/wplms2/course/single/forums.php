<?php
if ( !defined( 'ABSPATH' ) ) exit;
echo '<div id="course_forums">
		<h3 class="heading">'.__('Course Forums','vibe').'</h3>';
$forum_id=get_post_meta(get_the_ID(),'vibe_forum',true);
if(isset($forum_id) && $forum_id){
	if(is_numeric($forum_id) && get_post_type($forum_id) == 'forum'){
		$shortcode = '[bbp-single-forum id='.$forum_id.']';	
	}
	if(isset($_GET['forum'])){
		$forum_id = $_GET['forum'];
		if(is_numeric($forum_id) && get_post_type($forum_id) == 'forum'){
			$shortcode = '[bbp-single-forum id='.$forum_id.']';	
		}
	}
}

echo '<div id="search">'.do_shortcode('[bbp-search]').'
</div>';
echo do_shortcode($shortcode);

echo '</div>';
?>
<script>
	jQuery(document).ready(function($){
		$('body').delegate('#course_forums a','click',function(event){
			event.preventDefault();
			var link = $(this).attr('href');
			console.log(link);
			$.get(link,function(data){
				var data = $(data).find('#bbpress-forums');
				$('#course_forums').html(data);
			});
		});
	});
</script>
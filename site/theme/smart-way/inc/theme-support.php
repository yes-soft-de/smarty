<?php
/*
	=============================
		THEME SUPPORT OPTIONS
	=============================
*/

$options = get_option( 'post_formats' );
// The Format That Builtin Wordpress
$formats = array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' );
$output = array();
foreach ( $formats as $format ) {
	$output[] = ( @$options[$format] == 1 ? $format : '' );
}
// Check If The Array For Post Format Is Empty
if ( !empty( $options ) ) {
	add_theme_support( 'post-formats', $output );
}

// Fetch Custom Header Option And Add It To The Theme Support
$header = get_option( 'custom_header' );
if ( @$header == 1 ) {
    add_theme_support( 'custom-header' );
}

// Fetch Custom Background Option And Add It To The Theme Support
$background = get_option( 'custom_background' );
if ( @$background == 1 ) {
	add_theme_support( 'custom-background' );
}

// add thumbnail option to our post
add_theme_support( 'post-thumbnails' );
add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption') );

// Activate Navigation Menu
function smart_way_register_nav_menu() {
	register_nav_menu( 'primary', 'Header Navigation Menu' );
}
add_action( 'after_setup_theme', 'smart_way_register_nav_menu' );


/*
** Function To Display The Navigation Bar
** Add By @Talal
** wp_nav_menu()
*/
function smart_way_position_custom_nav()
{
	wp_nav_menu( array(
		'theme_location' => 'primary',
		'menu_class' => 'navbar-nav ml-auto',
		'container' => false,
		'depth' => 2,
		'walker' => new wp_bootstrap_navwalker() // we change to underscore[ _ ] to accept it in object
	) );
}

/*
** Function To Create Pagination Number
** Add By @Talal
**
*/
function smart_way_pagination_number()
{
	global $wp_query; // Make WP_Query Global
	$all_page_number = $wp_query->max_num_pages; // Get All Posts
	$current_page_number = max(1, get_query_var('paged')); // Get Current Page
	// Check If There Is One Page Or More
	if ( $all_page_number > 1 ) {
		return paginate_links( array(
			'base'               => get_pagenum_link() . '%_%',
			'format'             => '?paged=%#%',
			'current'            => $current_page_number,
			'total'              => $all_page_number,
			'prev_text'          => '« Prev',
			'next_text'          => 'Next »'
		) );
	}
}


// Activate Custom Sidebar
function smart_way_sidebar_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Smart Way Sidebar', 'smartway'),
		'id'            => 'smart-way-sidebar',
		'description'   => 'Dynamic Right Sidebar',
		'before_widget' => '<section id="%1$s" class="sunset-widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="sunset-widget-title">',
		'after_title'   => '</h2>'
	) );
}
add_action( 'widgets_init', 'smart_way_sidebar_init' );


/*** Blog Loop Custom Functions***/
function sunset_posted_meta() {
	// we write this function here because it related to post format
	$posted_on = human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) );
	$categories = get_the_category();       // Fetch All Categories
	$separator = ', ';                      // Separator between categories
	$output = '';
	$i = 1;
	if ( ! empty( $categories ) ):
		foreach ($categories as $category):
			// Check if there is one category to delete the separator or more than one category to print it
			if ( $i > 1 ): $output .= $separator; endif;
			// esc_attr( 'View All Posts in%s', $category->name ): will print $category->name instead of '%s'
			$output .= '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" alt="' . esc_attr( 'View All Posts in%s', $category->name ) . '">' . esc_html( $category->name ) . '</a>';
			$i++;
		endforeach;
	endif;
	return '<span class="posted-on">'.__('Posted').' <a href="' . esc_url( get_permalink() ) . '">'. $posted_on . '</a> Ago</span> / <span class="posted-in">' . $output . '</span>';
}

function sunset_posted_footer( $onlyComments = false ) {
	// we write this function here because it related to post format
	// get The Comments number | we don't have to insert the postId because the parent function (sunset_posted_footer) inside Blog loop so the postId automatically insert
	$comments_num = get_comments_number();
	// Check if the comment is open
	if( comments_open() ){
		if( $comments_num == 0 ){
			// __(string) is similar to _e(string) which is like echo, safe way to print string inside php code
			$comments = __('No Comments');
		} elseif ( $comments_num > 1 ){
			$comments= $comments_num . __(' Comments');
		} else {
			$comments = __('1 Comment');
		}
		$comments = '<a class="comments-link small text-caps" href="' . get_comments_link() . '">'. $comments .' <span class="sunset-icon sunset-comment"></span></a>';
	} else {
		$comments = __('Comments are closed');
	}

	if ( $onlyComments ) {
		return $comments;
	}
	// get_the_tag_list: don't need to insert last argument because the parent function (sunset_posted_footer) inside Blog loop so the id automatically insert
	return '<div class="post-footer-container">
				<div class="row">
					<div class="col-12 col-sm-6">' . get_the_tag_list('<div class="tags-list"><span class="sunset-icon sunset-tag"></span>', ' ', '</div>') . '</div>
					<div class="col-xs-12 col-sm-6 text-right">'. $comments .'</div>
				</div>
			</div>';
}


// Function to fetch our attachment image
function sunset_get_attachment( $attachmentNumber = 1 ) {
	/*
	 * IMPORTANT NOTE: Before Getting All Attachment
	 * 1- Make sure the images are attached to that post, not just inserted into the post body.
	 * 2- "If a media file is uploaded within the edit screen, it will automatically be attached to the >current post being edited.
	 *     If it is uploaded via the Media Add New SubPanel or Media Library >SubPanel it will be unattached, but may
	 *     become attached to a post when it is inserted into >post. There also is an option on the Media Library SubPanel
	 *     to attach unattached media >items."
	 * */
    // Declare the variable to avoid printing an error if there isn't image to display
	$output = '';
	// Check If there Is a thumbnail image And there is one thumbnail(which refer to image post type)
	if( has_post_thumbnail() && $attachmentNumber == 1 ):
        // Get The Thumbnail Url
		$output = wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ) );
	else: // Check if there is any image inside the post
		// fetch the image inside the post
		$attachments = get_posts( array(
		    // wordpress store every thing as a postType
			'post_type' => 'attachment',                // fetch the attachment only
			'posts_per_page' => $attachmentNumber,      // fetch number of attachments we defined
			'post_parent' => get_the_ID(),               // fetch the post parent
        ) );
		// check if the attachments that we bring is empty or not and check if is one attachment
		if( $attachments  && $attachmentNumber == 1 ):
            foreach ( $attachments as $attachment ):
				// Fetch the attachment url For this attachment id
				$output = wp_get_attachment_url( $attachment->ID );
			endforeach;
		elseif( $attachments && $attachmentNumber > 1 ): // check for multiply attachments(which refer to gallery post type)
            $output = $attachments;
		endif;
		// Reset The Post Data at the end of fetching what every we need
		wp_reset_postdata();
	endif;
	return $output;
}



// Function To Fetch Our Media(audio, video, ...)
function sunset_get_embedded_media( $type = array() ) {
    /**
     * do_shortcode: give the ability to access a specific shortcode or specific filter from wordpress
     * explain: we are inside the post loop so ($post) variable is working and storing all the information that the post has
     * so ex : the_ID = $post->ID, the_title = $post->post_title ....
     */
    $content = do_shortcode( apply_filters( 'the_content', get_the_content() ) );
    /* retrieve our embedded media,
     * audio: for in case a user upload audio file inside content editor of wordpress
     * iframe: because wordpress style a specific shortcode or third part of an url as an iframe
     */
    $embed = get_media_embedded_in_content( $content, $type );
    /*
     * ?visual=true : in video Type Could case an error, so it recommended to use it with audio type only
     * true: if set true will check also for 'audio' type(if string or not) inside $type variable
     * */
    if ( in_array('audio', $type, true ) ):
        /*
         * embed[0]: we take the first item inside our audio blog which should be the audio(start the blog with audio not with text)
         * visual=true : is use to make the picture for audio with full width and height,
         * so visual=false : make the picture 100% height and width is responsive
         * */
        $output = str_replace( '?visual=true', '?visual=false', $embed[0] );
    else:
        $output = $embed[0];
    endif;
    return $output;
}

// Function to Get All Slide Images
function sunset_get_bs_slides( $attachments ) {
    if ( !empty( $attachments ) ):
        $output = array();
        $count = count($attachments) - 1;
        for( $i = 0; $i <= $count; $i++ ):
            $active = ( $i == 0 ? ' active' : '' );
            $n = ( $i == $count ? 0 : $i + 1);
            $nextImg = wp_get_attachment_thumb_url( $attachments[$n]->ID );
            $p = ( $i == 0 ? $count : $i - 1 );
            $prevImg = wp_get_attachment_thumb_url( $attachments[$p]->ID );
            // store every thing inside an array with name to ease get all variable using name we defined
            $output[$i] = array(
                'class_active'  => $active,
                'url'           => wp_get_attachment_thumb_url( $attachments[$i]->ID ),
                'next_img'      => $nextImg,
                'prev_img'      => $prevImg,
                'caption'       => $attachments[$i]->post_excerpt
            );
        endfor;
        return $output;
    endif;
}


function sunset_grab_url() {
    /*
     * Wordpress don't have any function to retrieve whatever link inside the post or content so to do that we will use Regular Expression from php
     * // : to Contain our Regular Expression Pattern | <a : to start search for <a   | \s : for make space | [^>]: to avoid search for close tag
     * ['"] : to search for ' or "  | () : for grouping
     * (.+?) : . to search for everything excepts the line break | + : to match one or more | ? : avoid to match too many characters
     * */
    if ( ! preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"]/i', get_the_content(), $link ) ) {
        return false;
    }
    return esc_url_raw( $link[1] );
}

// Function To Grab Full Url to Prevent Anu Issue If There Is sub domain or any '/anything' in our domain
function sunset_grab_current_url() {
    // Check If the Https Is Active Or Http
    $http = ( isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://' );
    $referer = $http . $_SERVER['HTTP_HOST'];
    return $referer . $_SERVER['REQUEST_URI'];
}



// Function To Print Our Comments Navigation Section
function sunset_get_post_comments_navigation() {
		// Check if there is comments pages which mean we have pagination
		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ):
			require get_template_directory() . '/inc/template/sunset-comments-nav.php';
		endif;
}


// Initialize global Mobile Detect
function mobileDetectGlobal() {
	global $detect;
	$detect = new Mobile_Detect;
}
add_action('after_setup_theme', 'mobileDetectGlobal');



	/*
	** Function To Make Excerpt From Our Post Content
	** Add By @Talal
	** filter -> excerpt_length
	*/
	function smart_way_custom_excerpt($excerpt) {
		if (has_excerpt()) {
			$excerpt = wp_trim_words(get_the_excerpt(), apply_filters('excerpt_length', 55));
		}
		return $excerpt;
	}

	add_filter('the_excerpt', 'smart_way_custom_excerpt', 999);
	/*
	** Function To Edit The Extend Dot Filter
	** Add By @Talal
	** filter -> excerpt_more
	*/
	function smart_way_post_content_extend_dot_filter()
	{
		return '...';
	}
	add_filter( 'excerpt_more', 'smart_way_post_content_extend_dot_filter' );



//	add_action('wp_head', 'show_template');
//	function show_template() {
//		global $template;
//		echo basename($template);
//	}


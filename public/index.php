<?php // Silence is golden

add_action( 'login_enqueue_scripts', 'via_wpsetup_login_css' );						// Style the login page
add_action( 'widgets_init', 'via_wpsetup_remove_recent_comments_style' );			// Remove injected CSS for recent comments widget
add_action( 'wp_footer', 'via_wpsetup_usersnap' );									// Add Usersnap feedback tool to footer
add_action( 'wp_enqueue_scripts', 'via_wpsetup_admin_toolbar' );					// Style the frontend admin toolbar

add_filter( 'style_loader_src', 'via_wpsetup_remove_wp_ver_css_js', 9999 );			// Remove WP version from css
add_filter( 'script_loader_src', 'via_wpsetup_remove_wp_ver_css_js', 9999 );		// Remove WP version from scripts
add_filter( 'post_thumbnail_html', 'via_wpsetup_remove_img_dimensions', 10 );		// Filter out hard-coded width, height attributes on all images in WordPress. - https://gist.github.com/4557917 - for more information
add_filter( 'get_avatar','via_wpsetup_remove_img_dimensions', 10 );					// Filter out hard-coded width, height attributes on all images in WordPress. - https://gist.github.com/4557917 - for more information
add_filter( 'gallery_style', 'via_wpsetup_gallery_style' );							// Clean up gallery output in wp, remove injected CSS
add_filter( 'login_headerurl', 'via_wpsetup_login_url' );							// Changes admin logo link from wordpress.org to the site url
add_filter( 'login_headertext', 'via_wpsetup_login_title' );						// Changing the alt text on the logo to show your site name
add_filter( 'the_generator', 'via_wpsetup_rss_version' );							// Remove WP version from RSS
add_filter( 'login_display_language_dropdown', '__return_false' );					// Remove the language filter from the login screen

remove_action( 'wp_head', 'feed_links_extra', 3 );									// category feeds
remove_action( 'wp_head', 'feed_links', 2 );										// post and comment feeds
remove_action( 'wp_head', 'rsd_link' );												// EditURI link
remove_action( 'wp_head', 'wlwmanifest_link' );										// windows live writer
remove_action( 'wp_head', 'index_rel_link' );										// index link
remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );							// previous link
remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );							// start link
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );				// links for adjacent posts
remove_action( 'wp_head', 'wp_generator' );											// WP version
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );						// Remove emoji scripts
remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );							// Remove WP api
remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );					// Remove WP oembed
remove_action( 'wp_print_styles', 'print_emoji_styles' );							// Remove emoji styles

function via_wpsetup_remove_wp_ver_css_js( $src ) {
    // Remove WP version from css and js
	if ( strpos( $src, 'ver=' ) )
		$src = remove_query_arg( 'ver', $src );
	return $src;
}

function via_wpsetup_remove_img_dimensions($html) {
	if (preg_match('/<img[^>]+>/ims', $html, $matches)) { // Loop through all <img> tags
		foreach ($matches as $match) {
			$clean = preg_replace('/(width|height)=["\'\d%\s]+/ims', "", $match); // Replace all occurences of width/height
			$html = str_replace($match, $clean, $html); // Replace with result within html
		}
	}
	return $html;
}

function via_wpsetup_gallery_style($css) {
	return preg_replace("!<style type='text/css'>(.*?)</style>!s", '', $css);
}

function via_wpsetup_rss_version() {
	return '';
}

function via_wpsetup_remove_recent_comments_style() {
	global $wp_widget_factory;
	if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
		remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
	}
}

function via_wpsetup_login_css() {
	wp_enqueue_style( 'login-styles', plugins_url( 'public/css/via-wpsetup-login.css', basename( dirname( __FILE__, 2 ) ) . '/' . basename( dirname( __FILE__ ) ) ) );
}

function via_wpsetup_login_url() {
	return home_url();
}

function via_wpsetup_login_title() {
	return get_option('blogname');
}

function via_wpsetup_usersnap() {
	// Get usersnap_enabled option and usersnap_identifier option
	$options = get_option( 'via_foundations' );
	$usersnap_enabled = isset( $options['usersnap_enabled'] ) ? $options['usersnap_enabled'] : false;
	$usersnap_apikey = isset( $options['usersnap_apikey'] ) ? $options['usersnap_apikey'] : '';
	// If usersnap is enabled and identifier is set
	if ( current_user_can( 'manage_options' ) && is_user_logged_in() && $usersnap_enabled && !empty( $usersnap_apikey ) ) {
	?>
	<script>
		window.onUsersnapLoad = function(api) { api.init({
			user: {
				userId: '<?php echo esc_js( get_current_user_id() ); ?>',
				email: '<?php echo esc_js( wp_get_current_user()->user_email ); ?>'
			}
		}); };
		var script = document.createElement('script');
		script.defer = 1;
		script.src = 'https://widget.usersnap.com/global/load/<?php echo esc_js( $usersnap_apikey ); ?>?onload=onUsersnapLoad';
		document.getElementsByTagName('head')[0].appendChild(script);
	</script>
	<?php
	}
}

function via_wpsetup_admin_toolbar() {
	wp_enqueue_style( 'admin-bar-color', admin_url('/css/colors/modern/colors.min.css'), array('admin-bar') );
}
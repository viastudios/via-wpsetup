<?php

/**
 * Plugin Name:       Foundations
 * Description:       Sets Wordpress up in a clean and presentable state.
 * Version:           1.0.0
 * Author:            Via Studios
 * Author URI:        https://viastudios.co.uk/
 * Text Domain:       via-wpsetup
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'via_wpsetup_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-via-wpsetup-activator.php
 */
function activate_via_wpsetup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-via-wpsetup-activator.php';
	via_wpsetup_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-via-wpsetup-deactivator.php
 */
function deactivate_via_wpsetup() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-via-wpsetup-deactivator.php';
	via_wpsetup_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_via_wpsetup' );
register_deactivation_hook( __FILE__, 'deactivate_via_wpsetup' );

/**
 * OK, now we're ready,
 * Get to work
 */

add_action( 'login_head', 'via_wpsetup_login_css' );								// Style the login page
add_action( 'wp_dashboard_setup', 'via_wpsetup_remove_dashboard_widgets' );			// Remove default widgets from dashboard
add_action( 'wp_before_admin_bar_render', 'via_wpsetup_remove_admin_bar_links' );	// Remove item(s) from admin bar

add_filter( 'the_generator', 'via_wpsetup_rss_version' );							// Remove WP version from RSS
add_filter( 'admin_footer_text', 'via_wpsetup_admin_footer' );						// We did this, let them know
add_filter( 'login_headerurl', 'via_wpsetup_admin_login_url' );						// Changes admin logo link from wordpress.org to the site url
add_filter( 'login_headertitle', 'via_wpsetup_admin_login_title' );					// Changing the alt text on the logo to show your site name
add_filter( 'gettext', 'via_wpsetup_remove_howdy', 10, 3 );							// Change howdy to make it look more professional
add_filter( 'wp_head', 'via_wpsetup_remove_wp_widget_recent_comments_style', 1 );	// Remove injected css for recent comments widget
add_filter( 'style_loader_src', 'via_wpsetup_remove_wp_ver_css_js', 9999 );			// Remove WP version from css
add_filter( 'script_loader_src', 'via_wpsetup_remove_wp_ver_css_js', 9999 );		// Remove WP version from scripts
add_filter( 'post_thumbnail_html', 'via_wpsetup_remove_img_dimensions', 10 );		// Filter out hard-coded width, height attributes on all images in WordPress. - https://gist.github.com/4557917 - for more information
add_filter( 'get_avatar','via_wpsetup_remove_img_dimensions', 10 );					// Filter out hard-coded width, height attributes on all images in WordPress. - https://gist.github.com/4557917 - for more information
add_filter( 'gallery_style', 'via_wpsetup_gallery_style' );							// Clean up gallery output in wp, remove injected CSS

remove_filter( 'wp_head', 'via_wpsetup_remove_recent_comments_style', 1 );			// Remove injected CSS for recent comments widget
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


function via_wpsetup_rss_version() {
	return '';
}

function via_wpsetup_remove_wp_ver_css_js( $src ) {
	if ( strpos( $src, 'ver=' ) )
		$src = remove_query_arg( 'ver', $src );
	return $src;
}

function via_wpsetup_remove_dashboard_widgets() {
	global $wp_meta_boxes;
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}

function via_wpsetup_remove_howdy($translated, $text, $domain) {
	if (!is_admin() || 'default' != $domain)
		return $translated;
	if (false !== strpos($translated, 'Howdy'))
		return str_replace('Howdy', 'Welcome', $translated);
	return $translated;
}

function via_wpsetup_remove_admin_bar_links() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wp-logo'); // Remove Wordpress Logo From Admin Bar
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

function via_wpsetup_remove_recent_comments_style() {
	global $wp_widget_factory;
	if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
		remove_action('wp_head', array($wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style'));
	}
}

function via_wpsetup_remove_wp_widget_recent_comments_style() { // remove injected CSS for recent comments widget
	if ( has_filter('wp_head', 'wp_widget_recent_comments_style') ) {
		remove_filter('wp_head', 'wp_widget_recent_comments_style' );
	}
}

function via_wpsetup_login_css() {
	echo '<link rel="stylesheet" href="' . plugins_url( 'admin/css/via-wpsetup-admin.css', __FILE__ ) . '">'; // I couldn't get wp_enqueue_style to work :(
}

function via_wpsetup_admin_footer() {
	echo '<span id="footer-thankyou">Developed by <a href="//www.viastudios.co.uk/" target="_blank">Via Studios</a></span>. Built using <a href="http://upstatement.com/timber/" target="_blank">Timber</a> and <a href="http://sass-lang.com" target="_blank">Sass</a>.';
}

function via_wpsetup_admin_login_url() {
	return home_url();
}

function via_wpsetup_admin_login_title() {
	return get_option('blogname');
}
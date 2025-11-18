<?php
// Admin area actions
add_action( 'admin_head', 'via_wpsetup_admin_css' );								    	// Style the admin area
add_action( 'admin_init', 'via_wpsetup_admin_defaults', 1 );						    	// Hide core update nags and set colour scheme
add_action( 'wp_dashboard_setup', 'via_wpsetup_dashboard' );					        	// Remove default widgets from dashboard
add_action( 'admin_footer', ['Via_Foundation_Settings', 'output_intercom_script'] );    	// Output Intercom script in admin footer

// Admin area filters
add_filter( 'upload_mimes', 'cc_mime_types' );												// Include alternate MIME types i.e SVG in the media uploader
add_filter( 'admin_footer_text', 'via_wpsetup_admin_footer' );						    	// We did this, let them know
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'via_wpsetup_settings' );	// Add settings link on plugins page

function via_wpsetup_admin_css() {
    // Register and enqueue custom admin stylesheet
	wp_register_style( 'custom-admin-styles', plugins_url( 'admin/css/via-wpsetup-admin.css', __FILE__ ) );
	wp_enqueue_style( 'custom-admin-styles' );
}

function via_wpsetup_admin_defaults() {
    // Hide the update nag for non-admins
	if ( ! current_user_can( 'update_core' ) ) {
		remove_action( 'admin_notices', 'update_nag', 3 );
	}

	// Hide the "Admin Color Scheme" picker on profile screens
	remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' ); // hides the UI

	// Always use the chosen scheme for all users
	add_filter( 'get_user_option_admin_color', function ( $result, $option, $user ) {
		$forced = 'modern';
		return $forced;
	}, 10, 3 );
}

function via_wpsetup_dashboard() {
    // Remove unwanted dashboard widgets
	global $wp_meta_boxes;
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}

function via_wpsetup_admin_footer() {
    // Add our custom footer message in the admin area
	return '<span id="footer-thankyou">Developed by <a href="//www.viastudios.co.uk/" target="_blank">Via Studios</a></span>. Built using <a href="http://upstatement.com/timber/" target="_blank">Timber</a> and <a href="http://sass-lang.com" target="_blank">Sass</a>.';
}

function cc_mime_types($mimes) {
	$mimes['svg'] = 'image/svg+xml';
	return $mimes;
}

function via_wpsetup_settings( $links ) {
    $settings_url = admin_url( 'options-general.php?page=foundations' );
    $settings_link = '<a href="' . esc_url( $settings_url ) . '">Settings</a>';
    array_unshift( $links, $settings_link ); // put the link first
    return $links;
}
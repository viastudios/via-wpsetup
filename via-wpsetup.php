<?php

/**
 * Plugin Name:       Foundations
 * Plugin URI:        https://github.com/viastudios/via-wpsetup/
 * Description:       Sets Wordpress up in a clean and presentable state.
 * Version:           2.0.2
 * Author:            Via Studios
 * Author URI:        https://viastudios.co.uk/
 * Text Domain:       via-wpsetup
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Look for the version number and update if necessary
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
if( ! class_exists( 'Github_Updater' ) ){
	include_once( plugin_dir_path( __FILE__ ) . 'updater.php' );
}

$updater = new Github_Updater( __FILE__ );
$updater->set_username( 'viastudios' );
$updater->set_repository( 'via-wpsetup' );
$updater->initialize();

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

if ( is_admin() ) {
	require_once plugin_dir_path( __FILE__ ) . 'admin/inc/class-via-intercom-settings.php';
	require_once plugin_dir_path( __FILE__ ) . 'admin/index.php';
} else {
	require_once plugin_dir_path( __FILE__ ) . 'public/index.php';
}

// Apply to front and back end
add_action( 'admin_bar_menu', 'via_wpsetup_admin_bar', 9992 );	// Remove item(s) from admin bar

function via_wpsetup_admin_bar($wp_admin_bar) {
	// Remove Wordpress Logo From Admin Bar
	$wp_admin_bar->remove_menu('wp-logo'); 

	// Update Howdy Message
	$my_account = $wp_admin_bar->get_node( 'my-account' ); 
	if ( $my_account ) {
		$newtitle = str_replace( 'Howdy,', 'Welcome', $my_account->title );
		$wp_admin_bar->add_node( array(
			'id'    => 'my-account',
			'title' => $newtitle,
		) );
	}
}
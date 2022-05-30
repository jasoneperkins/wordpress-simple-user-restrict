<?php
/**
 * Child functions and definitions.
 */

// Restricts access to site folders and files.
// Redirects unauthenticated users to the WordPress login page
add_action('template_redirect', function() {
	$loggedIn		= is_user_logged_in();	// True if user is logged in.
	$whiteList		= array(
		'xx.xxx.xxx.xxx',
		'xxxx:xxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx',
	); // Array of IP addresses allowed whether user is logged in or not.
	$whiteListed	= in_array($_SERVER['REMOTE_ADDR'], $whiteList); // True if user's IP address is in the list of whitelisted addresses.
	$authenticated	= $loggedIn || $whiteListed; // True if user is allowed to view folders and files (logged in or whitelisted).
	
	// Allow AJAX processes.
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	// Redirect unauthenticated users to the WordPress login page.
	if ( !$authenticated ) {
		wp_redirect( esc_url( wp_login_url() ), 307 );
	}
});
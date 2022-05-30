<?php
/**
 * Child functions and definitions.
 */

// Restricts access to site folders and files.
// Redirects unauthenticated users to the WordPress login page
add_action('template_redirect', function() {
	$loggedIn		= is_user_logged_in();	// True if user is logged in.
	$authenticated	= $loggedIn;		// True if user is allowed to view folders and files.
	
	// Allow AJAX processes.
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	// Redirect unauthenticated users to the WordPress login page.
	if ( !$authenticated ) {
		wp_redirect( esc_url( wp_login_url() ), 307 );
	}

});
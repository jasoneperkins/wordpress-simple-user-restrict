<?php

/**
 * Plugin Name:       Simple User Restrict
 * Description:       Lock access to website assets behind login, with option to whitelist user IP addresses.
 * Version:           0.3
 * Author:            Jason E Perkins
 * Author URI:        https://jasoneperkins.com/
 */

// Restricts access to site folders and files.
// Redirects unauthenticated users to the WordPress login page

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class SimpleUserRestrictPlugin
{
    function __construct()
    {
        add_action('admin_menu', array($this, 'menuTab'));
        add_action('admin_init', array($this, 'settingsRegister'));
        add_action('template_redirect', array($this, 'authUser'));
    }

    function mainPageAssets()
    {
        wp_enqueue_style('filterAdminCss', plugin_dir_url(__FILE__) . 'styles.css');
    }

    function menuTab()
    {
        $mainPageHook = add_menu_page('Simple User Restrict', 'User Restrictions', 'manage_options', 'simple-user-restrict', array($this, 'surPage'), 'dashicons-groups', 71);
        add_submenu_page('simple-user-restrict', 'Restriction Settings', 'Settings', 'manage_options', 'simple-user-restrict', array($this, 'surPage'));
        add_action("load-{$mainPageHook}", array($this, 'mainPageAssets'));
    }

    function settingsRegister()
    {
        add_settings_section('sur_section01', NULL, NULL, 'simple-user-restrict');

        add_settings_field('sur_activate', 'Activate Simple User Restrict', array($this, activateHTML), 'simple-user-restrict', 'sur_section01');
        register_setting('sur_group', 'sur_activate', array('type' => 'string', 'sanitize_callback' => 'sanitize_text_field', 'default' => '0'));

        add_settings_field('sur_whitelist', 'Enter comma-separated IP Addresses to Whitelist', array($this, whitelistHTML), 'simple-user-restrict', 'sur_section01');
        register_setting('sur_group', 'sur_whitelist', array('type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field', 'default' => ''));
    }

    // HTML for plugin options page
    function surPage()
    { ?>
        <div class="wrap">
            <h1>Hello, world!</h1>
            <form action="options.php" method="POST">
                <?php
                settings_fields('sur_group');
                do_settings_sections('simple-user-restrict');
                submit_button();
                ?>
            </form>
        </div>
    <?php  }

    // HTML for plugin activation checkbox
    function activateHTML()
    { ?>
        <input type="checkbox" name="sur_activate" value="1" <?php checked(get_option('sur_activate')) ?>>
    <?php  }

    // HTML for IP Whitelist textarea
    function whitelistHTML()
    { ?>
        <div class="sur_whitelist__flex-container">
            <textarea name="sur_whitelist" id="sur_whitelist" placeholder="xx.xxx.xxx.xxx, xxxx:xxx:xxxx:xxxx:xxxx:xxxx:xxxx:xxxx"><?php echo esc_textarea(get_option('sur_whitelist')) ?></textarea>
        </div>
<?php  }

    function authUser()
    {
        $activated      = get_option(sur_activate);
        $loggedIn        = is_user_logged_in();    // True if user is logged in.
        $whiteList        = array_map('trim', explode(',', get_option('sur_whitelist'))); // Array of IP addresses allowed whether logged in or not.
        $whiteListed    = in_array($_SERVER['REMOTE_ADDR'], $whiteList); // True if user's IP address is in the list of whitelisted addresses.
        $authenticated    = $loggedIn || $whiteListed; // True if user is allowed to view folders and files (logged in or whitelisted).

        // Allow AJAX processes.
        if (is_admin() || wp_doing_ajax()) {
            return;
        }

        // Redirect unauthenticated users to the WordPress login page.
        if ($activated && !$authenticated) {
            wp_redirect(esc_url(wp_login_url()), 307);
            exit;
        }
    }
}

$simpleUserRestrictPlugin = new SimpleUserRestrictPlugin();

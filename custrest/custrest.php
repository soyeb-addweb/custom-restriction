<?php
/*
Plugin Name: Custom Page/Post Restriction (custrest)
Description: Restrict access to selected post types/pages for non-logged-in users with global and per-post overrides.
Version: 1.0.0
Author: Your Name
Text Domain: custrest
Domain Path: /languages
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'CUSTREST_VERSION', '1.0.0' );
define( 'CUSTREST_DIR', plugin_dir_path( __FILE__ ) );
define( 'CUSTREST_URL', plugin_dir_url( __FILE__ ) );

define( 'CUSTREST_OPTION_KEY', 'custrest_settings' );

define( 'CUSTREST_TEXT_DOMAIN', 'custrest' );

// Load plugin files
add_action( 'plugins_loaded', 'custrest_load_plugin' );
function custrest_load_plugin() {
    // Admin
    if ( is_admin() ) {
        require_once CUSTREST_DIR . 'admin/settings.php';
        require_once CUSTREST_DIR . 'admin/meta-box.php';
    }
    // Frontend
    require_once CUSTREST_DIR . 'includes/restriction.php';
    require_once CUSTREST_DIR . 'includes/helpers.php';
}
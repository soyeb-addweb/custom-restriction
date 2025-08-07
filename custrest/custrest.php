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

register_activation_hook( __FILE__, 'custrest_create_logs_table' );
function custrest_create_logs_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'custrest_logs';
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        post_id bigint(20) unsigned NOT NULL,
        user_id bigint(20) unsigned NOT NULL,
        ip varchar(100) DEFAULT '' NOT NULL,
        ua varchar(255) DEFAULT '' NOT NULL,
        reason varchar(50) DEFAULT '' NOT NULL,
        blocked_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY post_id (post_id),
        KEY user_id (user_id),
        KEY reason (reason),
        KEY blocked_at (blocked_at)
    ) $charset_collate;";
    dbDelta( $sql );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::add_command( 'custrest', 'Custrest_CLI_Command' );
    class Custrest_CLI_Command {
        /**
         * Check restriction status for a post.
         *
         * ## OPTIONS
         *
         * <post_id>
         * : The post ID to check.
         *
         * ## EXAMPLES
         *
         *     wp custrest status 123
         */
        public function status( $args, $assoc_args ) {
            $post_id = intval( $args[0] );
            if ( ! get_post( $post_id ) ) {
                WP_CLI::error( 'Post not found.' );
            }
            require_once CUSTREST_DIR . 'includes/helpers.php';
            $restricted = custrest_is_post_restricted( $post_id );
            WP_CLI::success( 'Post ' . $post_id . ' is ' . ( $restricted ? 'RESTRICTED' : 'PUBLIC' ) . '.' );
        }
    }
}
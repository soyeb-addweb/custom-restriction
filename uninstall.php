<?php
// Uninstall routine for custrest plugin

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Delete plugin options
delete_option( 'custrest_settings' );

global $wpdb;
// Delete all post meta for override
delete_post_meta_by_key( '_custrest_override' );
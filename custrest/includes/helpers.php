<?php
// Helper functions for custrest plugin

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get plugin settings with caching (transient).
 */
function custrest_get_settings() {
    $settings = get_transient( 'custrest_settings_cache' );
    if ( false === $settings ) {
        $settings = get_option( CUSTREST_OPTION_KEY, array() );
        set_transient( 'custrest_settings_cache', $settings, 5 * MINUTE_IN_SECONDS );
    }
    return $settings;
}

/**
 * Clear settings cache on update.
 */
add_action( 'update_option_' . CUSTREST_OPTION_KEY, function() {
    delete_transient( 'custrest_settings_cache' );
}, 10, 0 );

/**
 * Filters and actions for extensibility:
 * - 'custrest_is_restricted' (bool $restricted, int $post_id, string $post_type)
 * - 'custrest_redirect_url' (string $url, int $post_id, string $post_type)
 */
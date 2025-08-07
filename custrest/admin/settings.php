<?php
// Admin settings for custrest plugin

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'admin_menu', 'custrest_admin_menu' );
add_action( 'admin_init', 'custrest_register_settings' );
add_filter( 'manage_page_posts_columns', 'custrest_add_restriction_column' );
add_filter( 'manage_post_posts_columns', 'custrest_add_restriction_column' );
add_action( 'manage_page_posts_custom_column', 'custrest_show_restriction_column', 10, 2 );
add_action( 'manage_post_posts_custom_column', 'custrest_show_restriction_column', 10, 2 );
add_action( 'admin_notices', 'custrest_settings_updated_notice' );
add_filter( 'bulk_actions-edit-page', 'custrest_register_bulk_actions' );
add_filter( 'bulk_actions-edit-post', 'custrest_register_bulk_actions' );
add_filter( 'handle_bulk_actions-edit-page', 'custrest_handle_bulk_actions', 10, 3 );
add_filter( 'handle_bulk_actions-edit-post', 'custrest_handle_bulk_actions', 10, 3 );
add_action( 'admin_notices', 'custrest_bulk_action_notice' );

function custrest_admin_menu() {
    add_options_page(
        __( 'Restrict Access Settings', 'custrest' ),
        __( 'Restrict Access', 'custrest' ),
        'manage_options',
        'custrest-settings',
        'custrest_settings_page'
    );
}

function custrest_register_settings() {
    register_setting( 'custrest_settings_group', CUSTREST_OPTION_KEY, 'custrest_sanitize_settings' );

    add_settings_section(
        'custrest_main_section',
        __( 'Restriction Settings', 'custrest' ),
        '__return_false',
        'custrest-settings'
    );

    add_settings_field(
        'custrest_post_types',
        __( 'Post Types to Restrict', 'custrest' ),
        'custrest_post_types_field',
        'custrest-settings',
        'custrest_main_section'
    );

    add_settings_field(
        'custrest_ignore_pages',
        __( 'Ignore Pages/Posts', 'custrest' ),
        'custrest_ignore_pages_field',
        'custrest-settings',
        'custrest_main_section'
    );

    add_settings_field(
        'custrest_redirect_url',
        __( 'Redirect URL if not logged in', 'custrest' ),
        'custrest_redirect_url_field',
        'custrest-settings',
        'custrest_main_section'
    );
}

function custrest_post_types_field() {
    $options = get_option( CUSTREST_OPTION_KEY );
    $selected = isset( $options['post_types'] ) ? (array) $options['post_types'] : array();
    $post_types = get_post_types( array( 'public' => true ), 'objects' );
    echo '<fieldset aria-labelledby="custrest_post_types_label">';
    echo '<legend id="custrest_post_types_label" class="screen-reader-text">' . esc_html__( 'Post Types to Restrict', 'custrest' ) . '</legend>';
    foreach ( $post_types as $pt ) {
        printf(
            '<label style="margin-right:16px;"><input type="checkbox" name="%s[post_types][]" value="%s" %s aria-checked="%s" /> %s</label>',
            esc_attr( CUSTREST_OPTION_KEY ),
            esc_attr( $pt->name ),
            checked( in_array( $pt->name, $selected ), true, false ),
            checked( in_array( $pt->name, $selected ), true, false ),
            esc_html( $pt->labels->singular_name )
        );
    }
    echo '</fieldset>';
}

function custrest_ignore_pages_field() {
    $options = get_option( CUSTREST_OPTION_KEY );
    $ignored = isset( $options['ignore_pages'] ) ? (array) $options['ignore_pages'] : array();
    $args = array(
        'post_type'      => array('page', 'post'),
        'posts_per_page' => 100,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    );
    $posts = get_posts( $args );
    echo '<label for="custrest_ignore_pages_select" class="screen-reader-text">' . esc_html__( 'Ignore Pages/Posts', 'custrest' ) . '</label>';
    echo '<select id="custrest_ignore_pages_select" name="' . esc_attr( CUSTREST_OPTION_KEY ) . '[ignore_pages][]" multiple style="width:100%;max-width:400px;min-height:100px;" aria-describedby="custrest_ignore_pages_desc">';
    foreach ( $posts as $p ) {
        printf(
            '<option value="%d" %s>%s (%s)</option>',
            esc_attr( $p->ID ),
            selected( in_array( $p->ID, $ignored ), true, false ),
            esc_html( $p->post_title ),
            esc_html( ucfirst( $p->post_type ) )
        );
    }
    echo '</select>';
    echo '<p id="custrest_ignore_pages_desc" class="description">' . __( 'Selected pages/posts will never be restricted, regardless of global or per-post settings.', 'custrest' ) . '</p>';
}

function custrest_redirect_url_field() {
    $options = get_option( CUSTREST_OPTION_KEY );
    $url = isset( $options['redirect_url'] ) ? esc_url( $options['redirect_url'] ) : '';
    printf(
        '<label for="custrest_redirect_url_input" class="screen-reader-text">%s</label><input type="url" id="custrest_redirect_url_input" name="%s[redirect_url]" value="%s" class="regular-text" placeholder="%s" style="max-width:400px;" aria-describedby="custrest_redirect_url_desc" />',
        esc_html__( 'Redirect URL if not logged in', 'custrest' ),
        esc_attr( CUSTREST_OPTION_KEY ),
        $url,
        esc_attr( home_url( '/wp-login.php' ) )
    );
    echo '<p id="custrest_redirect_url_desc" class="description">' . __( 'Leave blank to use the default WordPress login page.', 'custrest' ) . '</p>';
}

function custrest_settings_page() {
    ?>
    <div class="wrap">
        <h1 style="margin-bottom:24px;"><?php _e( 'Restrict Access Settings', 'custrest' ); ?></h1>
        <form method="post" action="options.php" style="background:#fff;padding:24px 32px 16px 32px;border-radius:8px;max-width:700px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
            <?php
            settings_fields( 'custrest_settings_group' );
            do_settings_sections( 'custrest-settings' );
            submit_button( __( 'Save Settings', 'custrest' ) );
            ?>
        </form>
        <style>
            .form-table th { width: 220px; }
            .form-table td { vertical-align: middle; }
        </style>
        <?php custrest_restriction_summary_table(); ?>
    </div>
    <?php
}

function custrest_restriction_summary_table() {
    $options = get_option( CUSTREST_OPTION_KEY );
    $restricted_types = isset( $options['post_types'] ) ? (array) $options['post_types'] : array();
    $ignore_pages = isset( $options['ignore_pages'] ) ? (array) $options['ignore_pages'] : array();
    $args = array(
        'post_type'      => array('page', 'post'),
        'posts_per_page' => 20,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC',
    );
    $posts = get_posts( $args );
    if ( empty( $posts ) ) return;
    echo '<h2 style="margin-top:40px;">' . __( 'Restriction Status Overview', 'custrest' ) . '</h2>';
    echo '<table class="widefat striped" style="max-width:700px;margin-top:10px;">';
    echo '<thead><tr><th scope="col">' . __( 'Title', 'custrest' ) . '</th><th scope="col">' . __( 'Type', 'custrest' ) . '</th><th scope="col">' . __( 'Restriction', 'custrest' ) . '</th></tr></thead><tbody>';
    foreach ( $posts as $p ) {
        $override = get_post_meta( $p->ID, '_custrest_override', true );
        $post_type = $p->post_type;
        $status = 'inherit';
        if ( in_array( $p->ID, $ignore_pages, true ) ) {
            $status = 'ignored';
        } elseif ( $override === 'force' ) {
            $status = 'restricted';
        } elseif ( $override === 'disable' ) {
            $status = 'public';
        } elseif ( in_array( $post_type, $restricted_types, true ) ) {
            $status = 'restricted';
        } else {
            $status = 'public';
        }
        $status_labels = array(
            'restricted' => __( 'Restricted', 'custrest' ),
            'public'     => __( 'Public', 'custrest' ),
            'inherit'    => __( 'Inherit', 'custrest' ),
            'ignored'    => __( 'Ignored', 'custrest' ),
        );
        $status_colors = array(
            'restricted' => '#b91c1c', // dark red
            'public'     => '#166534', // dark green
            'inherit'    => '#1e40af', // dark blue
            'ignored'    => '#b45309', // dark yellow
        );
        echo '<tr>';
        echo '<td>' . esc_html( $p->post_title ) . '</td>';
        echo '<td>' . esc_html( ucfirst( $post_type ) ) . '</td>';
        echo '<td><span style="display:inline-block;padding:2px 8px;border-radius:4px;background:' . esc_attr( $status_colors[$status] ) . ';color:#fff;font-size:13px;">' . esc_html( $status_labels[$status] ) . '</span></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
    echo '<p class="description">' . __( 'Showing up to 20 most recent pages/posts. Use the meta box in the editor for per-item override.', 'custrest' ) . '</p>';
}

function custrest_sanitize_settings( $input ) {
    $output = array();
    $output['post_types'] = isset( $input['post_types'] ) ? array_map( 'sanitize_text_field', (array) $input['post_types'] ) : array();
    $output['redirect_url'] = isset( $input['redirect_url'] ) ? esc_url_raw( $input['redirect_url'] ) : '';
    $output['ignore_pages'] = isset( $input['ignore_pages'] ) ? array_map( 'intval', (array) $input['ignore_pages'] ) : array();
    return $output;
}

function custrest_add_restriction_column( $columns ) {
    $columns['custrest_restriction'] = __( 'Restriction', 'custrest' );
    return $columns;
}

function custrest_show_restriction_column( $column, $post_id ) {
    if ( $column !== 'custrest_restriction' ) return;
    $options = get_option( CUSTREST_OPTION_KEY );
    $restricted_types = isset( $options['post_types'] ) ? (array) $options['post_types'] : array();
    $ignore_pages = isset( $options['ignore_pages'] ) ? (array) $options['ignore_pages'] : array();
    $override = get_post_meta( $post_id, '_custrest_override', true );
    $post_type = get_post_type( $post_id );
    $status = 'inherit';
    if ( in_array( $post_id, $ignore_pages, true ) ) {
        $status = 'ignored';
    } elseif ( $override === 'force' ) {
        $status = 'restricted';
    } elseif ( $override === 'disable' ) {
        $status = 'public';
    } elseif ( in_array( $post_type, $restricted_types, true ) ) {
        $status = 'restricted';
    } else {
        $status = 'public';
    }
    $status_labels = array(
        'restricted' => __( 'Restricted', 'custrest' ),
        'public'     => __( 'Public', 'custrest' ),
        'inherit'    => __( 'Inherit', 'custrest' ),
        'ignored'    => __( 'Ignored', 'custrest' ),
    );
    $status_colors = array(
        'restricted' => '#b91c1c',
        'public'     => '#166534',
        'inherit'    => '#1e40af',
        'ignored'    => '#b45309',
    );
    echo '<span style="display:inline-block;padding:2px 8px;border-radius:4px;background:' . esc_attr( $status_colors[$status] ) . ';color:#fff;font-size:13px;">' . esc_html( $status_labels[$status] ) . '</span>';
}

function custrest_settings_updated_notice() {
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'custrest-settings' && isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Restrict Access settings updated.', 'custrest' ) . '</p></div>';
    }
}

function custrest_register_bulk_actions( $bulk_actions ) {
    $bulk_actions['custrest_set_restriction'] = __( 'Set Restriction (Login Required)', 'custrest' );
    $bulk_actions['custrest_clear_restriction'] = __( 'Clear Restriction (Always Public)', 'custrest' );
    $bulk_actions['custrest_ignore_restriction'] = __( 'Ignore Restriction (Never Restricted)', 'custrest' );
    return $bulk_actions;
}

function custrest_handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {
    $count = 0;
    $options = get_option( CUSTREST_OPTION_KEY );
    $ignore_pages = isset( $options['ignore_pages'] ) ? (array) $options['ignore_pages'] : array();
    if ( $doaction === 'custrest_set_restriction' ) {
        foreach ( $post_ids as $post_id ) {
            update_post_meta( $post_id, '_custrest_override', 'force' );
            $ignore_pages = array_diff( $ignore_pages, array( $post_id ) );
            $count++;
        }
        $options['ignore_pages'] = $ignore_pages;
        update_option( CUSTREST_OPTION_KEY, $options );
        $redirect_to = add_query_arg( 'custrest_bulk_set', $count, $redirect_to );
    }
    if ( $doaction === 'custrest_clear_restriction' ) {
        foreach ( $post_ids as $post_id ) {
            update_post_meta( $post_id, '_custrest_override', 'disable' );
            $ignore_pages = array_diff( $ignore_pages, array( $post_id ) );
            $count++;
        }
        $options['ignore_pages'] = $ignore_pages;
        update_option( CUSTREST_OPTION_KEY, $options );
        $redirect_to = add_query_arg( 'custrest_bulk_clear', $count, $redirect_to );
    }
    if ( $doaction === 'custrest_ignore_restriction' ) {
        foreach ( $post_ids as $post_id ) {
            delete_post_meta( $post_id, '_custrest_override' );
            if ( ! in_array( $post_id, $ignore_pages, true ) ) {
                $ignore_pages[] = $post_id;
            }
            $count++;
        }
        $options['ignore_pages'] = $ignore_pages;
        update_option( CUSTREST_OPTION_KEY, $options );
        $redirect_to = add_query_arg( 'custrest_bulk_ignore', $count, $redirect_to );
    }
    return $redirect_to;
}

function custrest_bulk_action_notice() {
    if ( ! empty( $_REQUEST['custrest_bulk_set'] ) ) {
        printf( '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Restriction set for %d items.', 'custrest' ) . '</p></div>', intval( $_REQUEST['custrest_bulk_set'] ) );
    }
    if ( ! empty( $_REQUEST['custrest_bulk_clear'] ) ) {
        printf( '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Restriction cleared for %d items.', 'custrest' ) . '</p></div>', intval( $_REQUEST['custrest_bulk_clear'] ) );
    }
    if ( ! empty( $_REQUEST['custrest_bulk_ignore'] ) ) {
        printf( '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Restriction ignored for %d items.', 'custrest' ) . '</p></div>', intval( $_REQUEST['custrest_bulk_ignore'] ) );
    }
}
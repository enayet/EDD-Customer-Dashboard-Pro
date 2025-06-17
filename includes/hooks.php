<?php
/**
 * WordPress Hooks for EDD Customer Dashboard Pro
 * All WordPress actions and filters used by the plugin
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Core Plugin Hooks
 */

// Plugin initialization
add_action( 'plugins_loaded', 'edd_dashboard_pro_load_textdomain' );
add_action( 'init', 'edd_dashboard_pro_init_query_vars' );
add_action( 'template_redirect', 'edd_dashboard_pro_template_redirect' );

// Assets
add_action( 'wp_enqueue_scripts', 'edd_dashboard_pro_enqueue_scripts' );
add_action( 'admin_enqueue_scripts', 'edd_dashboard_pro_admin_enqueue_scripts' );

// Dashboard functionality
add_action( 'wp_head', 'edd_dashboard_pro_add_meta_tags' );
add_filter( 'body_class', 'edd_dashboard_pro_body_class' );
add_filter( 'the_title', 'edd_dashboard_pro_filter_title', 10, 2 );

// User management
add_action( 'wp_login', 'edd_dashboard_pro_user_login', 10, 2 );
add_action( 'wp_logout', 'edd_dashboard_pro_user_logout' );
add_action( 'user_register', 'edd_dashboard_pro_user_register' );

// EDD integration
add_action( 'edd_complete_purchase', 'edd_dashboard_pro_complete_purchase' );
add_action( 'edd_payment_complete', 'edd_dashboard_pro_payment_complete' );
add_filter( 'edd_download_file', 'edd_dashboard_pro_track_download', 10, 3 );

// Download tracking
add_action( 'edd_file_download', 'edd_dashboard_pro_log_file_download', 10, 5 );
add_filter( 'edd_file_download_has_access', 'edd_dashboard_pro_check_download_access', 10, 4 );

// Wishlist hooks
add_action( 'wp_ajax_edd_add_to_wishlist', 'edd_dashboard_pro_ajax_add_to_wishlist' );
add_action( 'wp_ajax_edd_remove_from_wishlist', 'edd_dashboard_pro_ajax_remove_from_wishlist' );

// License management (if EDD Software Licensing is active)
add_action( 'edd_sl_license_activated', 'edd_dashboard_pro_license_activated', 10, 3 );
add_action( 'edd_sl_license_deactivated', 'edd_dashboard_pro_license_deactivated', 10, 3 );

// Admin hooks
add_action( 'admin_menu', 'edd_dashboard_pro_admin_menu' );
add_action( 'admin_init', 'edd_dashboard_pro_admin_init' );
add_action( 'admin_notices', 'edd_dashboard_pro_admin_notices' );

// Cleanup hooks
add_action( 'edd_dashboard_pro_daily_cleanup', 'edd_dashboard_pro_daily_cleanup_task' );
add_action( 'wp_scheduled_delete', 'edd_dashboard_pro_cleanup_expired_data' );

/**
 * Hook Implementations
 */

/**
 * Load plugin text domain
 */
function edd_dashboard_pro_load_textdomain() {
    load_plugin_textdomain(
        'edd-customer-dashboard-pro',
        false,
        dirname( plugin_basename( EDD_DASHBOARD_PRO_PLUGIN_FILE ) ) . '/languages/'
    );
}

/**
 * Initialize query vars
 */
function edd_dashboard_pro_init_query_vars() {
    add_rewrite_rule(
        '^customer-dashboard/?$',
        'index.php?edd-dashboard=1',
        'top'
    );
    
    add_rewrite_rule(
        '^customer-dashboard/([^/]+)/?$',
        'index.php?edd-dashboard=1&dashboard-section=$matches[1]',
        'top'
    );
}

/**
 * Handle template redirect for dashboard pages
 */
function edd_dashboard_pro_template_redirect() {
    if ( get_query_var( 'edd-dashboard' ) ) {
        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            $redirect_to_login = EDD_Dashboard_Pro()->get_option( 'require_login_redirect', true );
            if ( $redirect_to_login ) {
                wp_redirect( wp_login_url( get_permalink() ) );
                exit;
            }
        }

        // Check if user has dashboard access
        if ( ! EDD_Dashboard_Pro_Security::can_access_dashboard() ) {
            wp_redirect( home_url() );
            exit;
        }

        // Load dashboard template
        $template_loader = new EDD_Dashboard_Pro_Template_Loader();
        
        // Get section if specified
        $section = get_query_var( 'dashboard-section' );
        
        if ( $section ) {
            echo $template_loader->load_section( $section );
        } else {
            echo $template_loader->load_dashboard();
        }
        
        exit;
    }
}

/**
 * Enqueue frontend scripts and styles
 */
function edd_dashboard_pro_enqueue_scripts() {
    // Only load on dashboard pages or pages with shortcodes
    if ( ! edd_dashboard_pro_should_load_assets() ) {
        return;
    }

    // Base styles
    wp_enqueue_style(
        'edd-dashboard-pro',
        EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/css/dashboard-base.css',
        array(),
        EDD_DASHBOARD_PRO_VERSION
    );

    // Responsive styles
    wp_enqueue_style(
        'edd-dashboard-pro-responsive',
        EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/css/responsive.css',
        array( 'edd-dashboard-pro' ),
        EDD_DASHBOARD_PRO_VERSION
    );

    // Core JavaScript
    wp_enqueue_script(
        'edd-dashboard-pro',
        EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/js/dashboard-core.js',
        array( 'jquery' ),
        EDD_DASHBOARD_PRO_VERSION,
        true
    );

    // AJAX handlers
    wp_enqueue_script(
        'edd-dashboard-pro-ajax',
        EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/js/ajax-handlers.js',
        array( 'edd-dashboard-pro' ),
        EDD_DASHBOARD_PRO_VERSION,
        true
    );

    // Mobile optimizations
    if ( wp_is_mobile() ) {
        wp_enqueue_script(
            'edd-dashboard-pro-mobile',
            EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/js/mobile-optimizations.js',
            array( 'edd-dashboard-pro' ),
            EDD_DASHBOARD_PRO_VERSION,
            true
        );
    }

    // Localize scripts
    wp_localize_script(
        'edd-dashboard-pro',
        'eddDashboardPro',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => EDD_Dashboard_Pro_Security::create_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_AJAX ),
            'currentUser' => get_current_user_id(),
            'settings' => array(
                'enableWishlist' => EDD_Dashboard_Pro()->get_option( 'enable_wishlist', true ),
                'enableAnalytics' => EDD_Dashboard_Pro()->get_option( 'enable_analytics', true ),
                'itemsPerPage' => EDD_Dashboard_Pro()->get_option( 'items_per_page', 10 ),
                'dateFormat' => get_option( 'date_format' )
            ),
            'strings' => array(
                'loading' => esc_html__( 'Loading...', 'edd-customer-dashboard-pro' ),
                'error' => esc_html__( 'An error occurred. Please try again.', 'edd-customer-dashboard-pro' ),
                'success' => esc_html__( 'Success!', 'edd-customer-dashboard-pro' ),
                'confirm' => esc_html__( 'Are you sure?', 'edd-customer-dashboard-pro' ),
                'downloadPreparing' => esc_html__( 'Preparing download...', 'edd-customer-dashboard-pro' ),
                'downloadReady' => esc_html__( 'Download ready!', 'edd-customer-dashboard-pro' ),
                'addedToWishlist' => esc_html__( 'Added to wishlist!', 'edd-customer-dashboard-pro' ),
                'removedFromWishlist' => esc_html__( 'Removed from wishlist!', 'edd-customer-dashboard-pro' ),
                'licenseActivated' => esc_html__( 'License activated successfully!', 'edd-customer-dashboard-pro' ),
                'licenseDeactivated' => esc_html__( 'License deactivated successfully!', 'edd-customer-dashboard-pro' ),
                'copied' => esc_html__( 'Copied to clipboard!', 'edd-customer-dashboard-pro' ),
                'refreshing' => esc_html__( 'Refreshing data...', 'edd-customer-dashboard-pro' ),
                'noMoreItems' => esc_html__( 'No more items to load.', 'edd-customer-dashboard-pro' )
            )
        )
    );

    // Add custom CSS if provided
    $custom_css = EDD_Dashboard_Pro()->get_option( 'custom_css', '' );
    if ( ! empty( $custom_css ) ) {
        wp_add_inline_style( 'edd-dashboard-pro', $custom_css );
    }
}

/**
 * Enqueue admin scripts and styles
 */
function edd_dashboard_pro_admin_enqueue_scripts( $hook ) {
    // Only load on plugin admin pages
    if ( strpos( $hook, 'edd-settings' ) === false && strpos( $hook, 'edd-dashboard-pro' ) === false ) {
        return;
    }

    wp_enqueue_style(
        'edd-dashboard-pro-admin',
        EDD_DASHBOARD_PRO_PLUGIN_URL . 'admin/assets/css/admin-styles.css',
        array(),
        EDD_DASHBOARD_PRO_VERSION
    );

    wp_enqueue_script(
        'edd-dashboard-pro-admin',
        EDD_DASHBOARD_PRO_PLUGIN_URL . 'admin/assets/js/admin-scripts.js',
        array( 'jquery' ),
        EDD_DASHBOARD_PRO_VERSION,
        true
    );

    wp_localize_script(
        'edd-dashboard-pro-admin',
        'eddDashboardProAdmin',
        array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'edd_dashboard_pro_admin_nonce' ),
            'strings' => array(
                'confirmReset' => esc_html__( 'Are you sure you want to reset all settings to defaults?', 'edd-customer-dashboard-pro' ),
                'confirmClearCache' => esc_html__( 'Are you sure you want to clear all caches?', 'edd-customer-dashboard-pro' ),
                'settingsExported' => esc_html__( 'Settings exported successfully!', 'edd-customer-dashboard-pro' ),
                'settingsImported' => esc_html__( 'Settings imported successfully!', 'edd-customer-dashboard-pro' ),
                'cacheCleared' => esc_html__( 'Cache cleared successfully!', 'edd-customer-dashboard-pro' )
            )
        )
    );
}

/**
 * Add meta tags to dashboard pages
 */
function edd_dashboard_pro_add_meta_tags() {
    if ( ! edd_dashboard_pro_is_dashboard_page() ) {
        return;
    }

    echo '<meta name="robots" content="noindex, nofollow">' . "\n";
    echo '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">' . "\n";
}

/**
 * Add body classes for dashboard pages
 *
 * @param array $classes Existing body classes
 * @return array
 */
function edd_dashboard_pro_body_class( $classes ) {
    if ( edd_dashboard_pro_is_dashboard_page() ) {
        $classes[] = 'edd-dashboard-page';
        
        $template = EDD_Dashboard_Pro()->get_option( 'template', 'default' );
        $classes[] = 'edd-dashboard-template-' . sanitize_html_class( $template );
        
        if ( wp_is_mobile() ) {
            $classes[] = 'edd-dashboard-mobile';
        }
    }

    return $classes;
}

/**
 * Filter page title for dashboard pages
 *
 * @param string $title Page title
 * @param int $post_id Post ID
 * @return string
 */
function edd_dashboard_pro_filter_title( $title, $post_id = null ) {
    if ( edd_dashboard_pro_is_dashboard_page() && in_the_loop() && is_main_query() ) {
        $dashboard_title = EDD_Dashboard_Pro()->get_option( 'dashboard_title', '' );
        if ( ! empty( $dashboard_title ) ) {
            return $dashboard_title;
        }
    }

    return $title;
}

/**
 * Handle user login
 *
 * @param string $user_login Username
 * @param WP_User $user User object
 */
function edd_dashboard_pro_user_login( $user_login, $user ) {
    // Clear any cached data for this user
    delete_user_meta( $user->ID, '_edd_dashboard_pro_download_count' );
    
    // Update last login time
    update_user_meta( $user->ID, '_edd_dashboard_pro_last_login', current_time( 'mysql' ) );
}

/**
 * Handle user logout
 */
function edd_dashboard_pro_user_logout() {
    $user_id = get_current_user_id();
    if ( $user_id ) {
        update_user_meta( $user_id, '_edd_dashboard_pro_last_logout', current_time( 'mysql' ) );
    }
}

/**
 * Handle user registration
 *
 * @param int $user_id User ID
 */
function edd_dashboard_pro_user_register( $user_id ) {
    // Initialize empty wishlist
    update_user_meta( $user_id, '_edd_dashboard_pro_wishlist', array() );
    
    // Set registration date
    update_user_meta( $user_id, '_edd_dashboard_pro_registered', current_time( 'mysql' ) );
}

/**
 * Handle purchase completion
 *
 * @param int $payment_id Payment ID
 */
function edd_dashboard_pro_complete_purchase( $payment_id ) {
    $payment = edd_get_payment( $payment_id );
    
    if ( ! $payment ) {
        return;
    }

    // Clear cached customer data
    delete_user_meta( $payment->user_id, '_edd_dashboard_pro_download_count' );
    delete_user_meta( $payment->user_id, '_edd_dashboard_pro_stats_cache' );
    
    // Update purchase notification flag
    update_user_meta( $payment->user_id, '_edd_dashboard_pro_has_new_purchase', true );
}

/**
 * Handle payment completion
 *
 * @param int $payment_id Payment ID
 */
function edd_dashboard_pro_payment_complete( $payment_id ) {
    // Refresh customer stats cache
    $payment = edd_get_payment( $payment_id );
    
    if ( $payment && $payment->user_id ) {
        $customer_data = edd_dashboard_pro_get_current_customer();
        if ( $customer_data ) {
            edd_dashboard_pro_get_customer_stats( $customer_data['id'] );
        }
    }
}

/**
 * Track file downloads
 *
 * @param string $file_url File URL
 * @param int $download_id Download ID
 * @param int $payment_id Payment ID
 * @return string
 */
function edd_dashboard_pro_track_download( $file_url, $download_id, $payment_id ) {
    $user_id = get_current_user_id();
    
    if ( $user_id ) {
        // Increment download count
        $current_count = get_user_meta( $user_id, '_edd_dashboard_pro_download_count', true );
        update_user_meta( $user_id, '_edd_dashboard_pro_download_count', (int) $current_count + 1 );
        
        // Update last download time
        update_user_meta( $user_id, '_edd_dashboard_pro_last_download', current_time( 'mysql' ) );
        
        // Clear stats cache
        delete_user_meta( $user_id, '_edd_dashboard_pro_stats_cache' );
    }

    return $file_url;
}

/**
 * Log file downloads
 *
 * @param int $download_id Download ID
 * @param string $file_id File ID
 * @param array $user_info User info
 * @param int $payment_id Payment ID
 * @param array $args Additional arguments
 */
function edd_dashboard_pro_log_file_download( $download_id, $file_id, $user_info, $payment_id, $args ) {
    $user_id = $user_info['id'] ?? 0;
    
    if ( ! $user_id ) {
        return;
    }

    // Store download history
    $download_history = get_user_meta( $user_id, '_edd_dashboard_pro_download_history', true );
    if ( ! is_array( $download_history ) ) {
        $download_history = array();
    }

    $download_entry = array(
        'download_id' => $download_id,
        'file_id' => $file_id,
        'payment_id' => $payment_id,
        'date' => current_time( 'mysql' ),
        'ip' => EDD_Dashboard_Pro_Security::get_client_ip()
    );

    array_unshift( $download_history, $download_entry );

    // Keep only last 100 downloads
    if ( count( $download_history ) > 100 ) {
        $download_history = array_slice( $download_history, 0, 100 );
    }

    update_user_meta( $user_id, '_edd_dashboard_pro_download_history', $download_history );
}

/**
 * Check download access
 *
 * @param bool $has_access Whether user has access
 * @param int $payment_id Payment ID
 * @param array $args Download arguments
 * @param int $download_id Download ID
 * @return bool
 */
function edd_dashboard_pro_check_download_access( $has_access, $payment_id, $args, $download_id ) {
    if ( ! $has_access ) {
        return false;
    }

    $user_id = get_current_user_id();
    
    if ( ! $user_id ) {
        return false;
    }

    // Additional security check
    return EDD_Dashboard_Pro_Security::can_download_file( $payment_id, $download_id, $user_id );
}

/**
 * Handle AJAX add to wishlist
 */
function edd_dashboard_pro_ajax_add_to_wishlist() {
    // This is handled by the AJAX handlers class
    $ajax_handler = new EDD_Dashboard_Pro_Ajax_Handlers();
    $ajax_handler->handle_add_to_wishlist();
}

/**
 * Handle AJAX remove from wishlist
 */
function edd_dashboard_pro_ajax_remove_from_wishlist() {
    // This is handled by the AJAX handlers class
    $ajax_handler = new EDD_Dashboard_Pro_Ajax_Handlers();
    $ajax_handler->handle_remove_from_wishlist();
}

/**
 * Handle license activation
 *
 * @param int $license_id License ID
 * @param string $site_url Site URL
 * @param int $user_id User ID
 */
function edd_dashboard_pro_license_activated( $license_id, $site_url, $user_id ) {
    // Clear license cache
    delete_user_meta( $user_id, '_edd_dashboard_pro_license_cache' );
    
    // Update stats cache
    delete_user_meta( $user_id, '_edd_dashboard_pro_stats_cache' );
    
    // Log event
    do_action( 'edd_dashboard_pro_license_event', 'activated', $license_id, $site_url, $user_id );
}

/**
 * Handle license deactivation
 *
 * @param int $license_id License ID
 * @param string $site_url Site URL
 * @param int $user_id User ID
 */
function edd_dashboard_pro_license_deactivated( $license_id, $site_url, $user_id ) {
    // Clear license cache
    delete_user_meta( $user_id, '_edd_dashboard_pro_license_cache' );
    
    // Update stats cache
    delete_user_meta( $user_id, '_edd_dashboard_pro_stats_cache' );
    
    // Log event
    do_action( 'edd_dashboard_pro_license_event', 'deactivated', $license_id, $site_url, $user_id );
}

/**
 * Add admin menu
 */
function edd_dashboard_pro_admin_menu() {
    if ( class_exists( 'EDD_Dashboard_Pro_Admin_Menu' ) ) {
        $admin_menu = new EDD_Dashboard_Pro_Admin_Menu();
    }
}

/**
 * Admin initialization
 */
function edd_dashboard_pro_admin_init() {
    // Check for plugin updates
    if ( current_user_can( 'manage_options' ) ) {
        edd_dashboard_pro_check_plugin_updates();
    }
    
    // Handle admin actions
    edd_dashboard_pro_handle_admin_actions();
}

/**
 * Show admin notices
 */
function edd_dashboard_pro_admin_notices() {
    // Check if EDD is active
    if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
        ?>
        <div class="notice notice-error">
            <p>
                <?php 
                printf(
                    /* translators: %s: Easy Digital Downloads plugin name */
                    esc_html__( 'EDD Customer Dashboard Pro requires %s to be installed and active.', 'edd-customer-dashboard-pro' ),
                    '<strong>Easy Digital Downloads</strong>'
                );
                ?>
            </p>
        </div>
        <?php
        return;
    }

    // Show activation notice
    if ( get_option( 'edd_dashboard_pro_show_activation_notice' ) ) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <?php esc_html_e( 'EDD Customer Dashboard Pro has been activated successfully!', 'edd-customer-dashboard-pro' ); ?>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=edd_dashboard_pro' ) ); ?>">
                    <?php esc_html_e( 'Configure Settings', 'edd-customer-dashboard-pro' ); ?>
                </a>
            </p>
        </div>
        <?php
        delete_option( 'edd_dashboard_pro_show_activation_notice' );
    }

    // Show update notices
    edd_dashboard_pro_show_update_notices();
}

/**
 * Daily cleanup task
 */
function edd_dashboard_pro_daily_cleanup_task() {
    // Clean up expired transients
    edd_dashboard_pro_cleanup_expired_transients();
    
    // Clean up old logs
    edd_dashboard_pro_cleanup_old_logs();
    
    // Optimize database tables if needed
    edd_dashboard_pro_optimize_database();
}

/**
 * Cleanup expired data
 */
function edd_dashboard_pro_cleanup_expired_data() {
    global $wpdb;
    
    // Clean up expired download URLs
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_edd_dashboard_download_%' 
         AND option_value < UNIX_TIMESTAMP()"
    );
    
    // Clean up old security logs
    EDD_Dashboard_Pro_Security::cleanup_security_logs();
}

/**
 * Helper Functions
 */

/**
 * Check if current page should load dashboard assets
 *
 * @return bool
 */
function edd_dashboard_pro_should_load_assets() {
    global $post;
    
    // Dashboard pages
    if ( get_query_var( 'edd-dashboard' ) ) {
        return true;
    }
    
    // Pages with shortcodes
    if ( $post && has_shortcode( $post->post_content, 'edd_customer_dashboard_pro' ) ) {
        return true;
    }
    
    if ( $post && has_shortcode( $post->post_content, 'edd_dashboard_pro' ) ) {
        return true;
    }
    
    // Designated dashboard page
    $dashboard_page = EDD_Dashboard_Pro()->get_option( 'dashboard_page' );
    if ( $dashboard_page && is_page( $dashboard_page ) ) {
        return true;
    }
    
    return false;
}

/**
 * Check if current page is a dashboard page
 *
 * @return bool
 */
function edd_dashboard_pro_is_dashboard_page() {
    return edd_dashboard_pro_should_load_assets();
}

/**
 * Check for plugin updates
 */
function edd_dashboard_pro_check_plugin_updates() {
    $last_check = get_option( 'edd_dashboard_pro_last_update_check', 0 );
    $check_interval = 24 * HOUR_IN_SECONDS; // Check daily
    
    if ( ( time() - $last_check ) > $check_interval ) {
        // Perform update check logic here
        update_option( 'edd_dashboard_pro_last_update_check', time() );
    }
}

/**
 * Handle admin actions
 */
function edd_dashboard_pro_handle_admin_actions() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Handle settings reset
    if ( isset( $_POST['edd_dashboard_pro_reset_settings'] ) ) {
        if ( wp_verify_nonce( $_POST['_wpnonce'], 'edd_dashboard_pro_reset_settings' ) ) {
            $settings = new EDD_Dashboard_Pro_Admin_Settings();
            $settings->reset_settings();
            
            add_settings_error(
                'edd_dashboard_pro_settings',
                'settings_reset',
                esc_html__( 'Settings have been reset to defaults.', 'edd-customer-dashboard-pro' ),
                'updated'
            );
        }
    }

    // Handle cache clear
    if ( isset( $_POST['edd_dashboard_pro_clear_cache'] ) ) {
        if ( wp_verify_nonce( $_POST['_wpnonce'], 'edd_dashboard_pro_clear_cache' ) ) {
            $settings = new EDD_Dashboard_Pro_Admin_Settings();
            $settings->clear_caches();
            
            add_settings_error(
                'edd_dashboard_pro_settings',
                'cache_cleared',
                esc_html__( 'Cache has been cleared successfully.', 'edd-customer-dashboard-pro' ),
                'updated'
            );
        }
    }
}

/**
 * Show update notices
 */
function edd_dashboard_pro_show_update_notices() {
    $notices = get_option( 'edd_dashboard_pro_admin_notices', array() );
    
    foreach ( $notices as $notice_id => $notice ) {
        $class = isset( $notice['type'] ) ? 'notice-' . $notice['type'] : 'notice-info';
        $dismissible = isset( $notice['dismissible'] ) && $notice['dismissible'] ? 'is-dismissible' : '';
        
        printf(
            '<div class="notice %s %s"><p>%s</p></div>',
            esc_attr( $class ),
            esc_attr( $dismissible ),
            wp_kses_post( $notice['message'] )
        );
    }
    
    // Clear notices after displaying
    delete_option( 'edd_dashboard_pro_admin_notices' );
}

/**
 * Cleanup expired transients
 */
function edd_dashboard_pro_cleanup_expired_transients() {
    global $wpdb;
    
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_timeout_edd_dashboard_pro_%' 
         AND option_value < UNIX_TIMESTAMP()"
    );
    
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE '_transient_edd_dashboard_pro_%' 
         AND option_name NOT IN (
             SELECT CONCAT('_transient_', SUBSTRING(option_name, 20))
             FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_timeout_edd_dashboard_pro_%'
         )"
    );
}

/**
 * Cleanup old logs
 */
function edd_dashboard_pro_cleanup_old_logs() {
    // Clean up logs older than 30 days
    $cutoff_date = date( 'Y-m-d H:i:s', strtotime( '-30 days' ) );
    
    // Security logs
    $security_logs = get_option( 'edd_dashboard_pro_security_logs', array() );
    $security_logs = array_filter( $security_logs, function( $log ) use ( $cutoff_date ) {
        return isset( $log['timestamp'] ) && $log['timestamp'] > $cutoff_date;
    } );
    update_option( 'edd_dashboard_pro_security_logs', array_values( $security_logs ) );
    
    // AJAX logs
    $ajax_logs = get_transient( 'edd_dashboard_pro_ajax_logs' );
    if ( is_array( $ajax_logs ) ) {
        $ajax_logs = array_filter( $ajax_logs, function( $log ) use ( $cutoff_date ) {
            return isset( $log['timestamp'] ) && $log['timestamp'] > $cutoff_date;
        } );
        set_transient( 'edd_dashboard_pro_ajax_logs', array_values( $ajax_logs ), 7 * DAY_IN_SECONDS );
    }
}

/**
 * Optimize database tables
 */
function edd_dashboard_pro_optimize_database() {
    global $wpdb;
    
    // Only run optimization weekly
    $last_optimization = get_option( 'edd_dashboard_pro_last_db_optimization', 0 );
    if ( ( time() - $last_optimization ) < ( 7 * DAY_IN_SECONDS ) ) {
        return;
    }
    
    // Optimize relevant tables
    $tables = array(
        $wpdb->options,
        $wpdb->usermeta,
        $wpdb->posts,
        $wpdb->postmeta
    );
    
    foreach ( $tables as $table ) {
        $wpdb->query( "OPTIMIZE TABLE {$table}" );
    }
    
    update_option( 'edd_dashboard_pro_last_db_optimization', time() );
}

/**
 * Get dashboard URL
 *
 * @param string $section Optional section
 * @return string
 */
function edd_dashboard_pro_get_dashboard_url( $section = '' ) {
    $dashboard_page = EDD_Dashboard_Pro()->get_option( 'dashboard_page' );
    
    if ( $dashboard_page ) {
        $url = get_permalink( $dashboard_page );
        if ( $section ) {
            $url = add_query_arg( 'section', $section, $url );
        }
        return $url;
    }
    
    $url = home_url( '/customer-dashboard/' );
    if ( $section ) {
        $url .= $section . '/';
    }
    
    return $url;
}

/**
 * Add query vars
 *
 * @param array $vars Query vars
 * @return array
 */
function edd_dashboard_pro_add_query_vars( $vars ) {
    $vars[] = 'edd-dashboard';
    $vars[] = 'dashboard-section';
    return $vars;
}
add_filter( 'query_vars', 'edd_dashboard_pro_add_query_vars' );

/**
 * Schedule events on activation
 */
function edd_dashboard_pro_schedule_events() {
    if ( ! wp_next_scheduled( 'edd_dashboard_pro_daily_cleanup' ) ) {
        wp_schedule_event( time(), 'daily', 'edd_dashboard_pro_daily_cleanup' );
    }
}

/**
 * Clear scheduled events on deactivation
 */
function edd_dashboard_pro_clear_scheduled_events() {
    wp_clear_scheduled_hook( 'edd_dashboard_pro_daily_cleanup' );
}

// Schedule events on plugin activation
register_activation_hook( EDD_DASHBOARD_PRO_PLUGIN_FILE, 'edd_dashboard_pro_schedule_events' );

// Clear events on plugin deactivation
register_deactivation_hook( EDD_DASHBOARD_PRO_PLUGIN_FILE, 'edd_dashboard_pro_clear_scheduled_events' );
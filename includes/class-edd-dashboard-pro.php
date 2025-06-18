<?php
/**
 * Main EDD Dashboard Pro Class
 * Core functionality and initialization
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Main EDD Dashboard Pro Class
 */
class EDD_Dashboard_Pro {

    /**
     * The single instance of the class
     *
     * @var EDD_Dashboard_Pro
     */
    private static $instance = null;

    /**
     * Plugin options
     *
     * @var array
     */
    private $options = array();

    /**
     * Template loader instance
     *
     * @var EDD_Dashboard_Pro_Template_Loader
     */
    public $template_loader;

    /**
     * Security instance
     *
     * @var EDD_Dashboard_Pro_Security
     */
    public $security;

    /**
     * Get the single instance of the class
     *
     * @return EDD_Dashboard_Pro
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_options();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_action( 'wp_head', array( $this, 'add_frontend_data' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'template_redirect' ) );
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Initialize template loader
        if ( class_exists( 'EDD_Dashboard_Pro_Template_Loader' ) ) {
            $this->template_loader = new EDD_Dashboard_Pro_Template_Loader();
        }

        // Initialize security
        if ( class_exists( 'EDD_Dashboard_Pro_Security' ) ) {
            $this->security = new EDD_Dashboard_Pro_Security();
        }

        // Create database tables if needed
        $this->maybe_create_tables();

        // Schedule cron jobs
        $this->schedule_cron_jobs();

        do_action( 'edd_dashboard_pro_init' );
    }

    /**
     * Load plugin options
     */
    private function load_options() {
        $this->options = get_option( 'edd_dashboard_pro_settings', array() );
        
        // Set default options
        $defaults = array(
            'dashboard_page' => 0,
            'template' => 'default',
            'enable_wishlist' => true,
            'enable_analytics' => true,
            'enable_download_tracking' => true,
            'enable_license_management' => true,
            'enable_support' => false,
            'items_per_page' => 10,
            'download_limit_display' => true,
            'license_key_display' => true,
            'security_level' => 'medium',
            'cache_duration' => 3600,
            'custom_css' => ''
        );

        $this->options = wp_parse_args( $this->options, $defaults );
    }

    /**
     * Get plugin option
     *
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get_option( $key, $default = null ) {
        if ( isset( $this->options[ $key ] ) ) {
            return $this->options[ $key ];
        }
        return $default;
    }

    /**
     * Update plugin option
     *
     * @param string $key Option key
     * @param mixed $value Option value
     * @return bool
     */
    public function update_option( $key, $value ) {
        $this->options[ $key ] = $value;
        return update_option( 'edd_dashboard_pro_settings', $this->options );
    }

    /**
     * Get all plugin options
     *
     * @return array
     */
    public function get_options() {
        return $this->options;
    }

    /**
     * Update multiple options
     *
     * @param array $options Options array
     * @return bool
     */
    public function update_options( $options ) {
        $this->options = array_merge( $this->options, $options );
        return update_option( 'edd_dashboard_pro_settings', $this->options );
    }

    /**
     * Add query vars for dashboard
     *
     * @param array $vars Query vars
     * @return array
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'edd-dashboard';
        $vars[] = 'edd-dashboard-section';
        $vars[] = 'edd-dashboard-action';
        return $vars;
    }

    /**
     * Handle template redirects
     */
    public function template_redirect() {
        if ( get_query_var( 'edd-dashboard' ) ) {
            $this->load_dashboard_template();
        }
    }

    /**
     * Load dashboard template
     */
    private function load_dashboard_template() {
        if ( ! is_user_logged_in() ) {
            $login_url = wp_login_url( $this->get_dashboard_url() );
            wp_redirect( $login_url );
            exit;
        }

        if ( $this->template_loader ) {
            $this->template_loader->load_dashboard();
            exit;
        }
    }

    /**
     * Get dashboard URL
     *
     * @return string
     */
    public function get_dashboard_url() {
        $dashboard_page = $this->get_option( 'dashboard_page' );
        
        if ( $dashboard_page && get_post_status( $dashboard_page ) === 'publish' ) {
            return get_permalink( $dashboard_page );
        }
        
        return add_query_arg( 'edd-dashboard', '1', home_url() );
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Only load on dashboard pages or if shortcode is present
        if ( ! $this->should_load_frontend_assets() ) {
            return;
        }

        // Main dashboard CSS
        wp_enqueue_style(
            'edd-dashboard-pro-frontend',
            EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            EDD_DASHBOARD_PRO_VERSION
        );

        // Main dashboard JS
        wp_enqueue_script(
            'edd-dashboard-pro-frontend',
            EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/js/frontend.js',
            array( 'jquery' ),
            EDD_DASHBOARD_PRO_VERSION,
            true
        );

        // Localize script with data
        wp_localize_script( 'edd-dashboard-pro-frontend', 'eddDashboardPro', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'edd_dashboard_pro_nonce' ),
            'currentUser' => get_current_user_id(),
            'dashboardUrl' => $this->get_dashboard_url(),
            'strings' => array(
                'loading' => esc_html__( 'Loading...', 'edd-customer-dashboard-pro' ),
                'error' => esc_html__( 'An error occurred. Please try again.', 'edd-customer-dashboard-pro' ),
                'success' => esc_html__( 'Success!', 'edd-customer-dashboard-pro' ),
                'confirm' => esc_html__( 'Are you sure?', 'edd-customer-dashboard-pro' ),
                'added_to_wishlist' => esc_html__( 'Added to wishlist', 'edd-customer-dashboard-pro' ),
                'removed_from_wishlist' => esc_html__( 'Removed from wishlist', 'edd-customer-dashboard-pro' )
            )
        ) );

        // Add custom CSS if available
        $custom_css = $this->get_option( 'custom_css' );
        if ( ! empty( $custom_css ) ) {
            wp_add_inline_style( 'edd-dashboard-pro-frontend', $custom_css );
        }

        // Enqueue template-specific assets
        if ( $this->template_loader ) {
            $this->template_loader->enqueue_template_assets();
        }
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on plugin admin pages
        if ( ! $this->is_plugin_admin_page( $hook ) ) {
            return;
        }

        // Admin CSS
        wp_enqueue_style(
            'edd-dashboard-pro-admin',
            EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            EDD_DASHBOARD_PRO_VERSION
        );

        // Admin JS
        wp_enqueue_script(
            'edd-dashboard-pro-admin',
            EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            EDD_DASHBOARD_PRO_VERSION,
            true
        );

        // Localize admin script
        wp_localize_script( 'edd-dashboard-pro-admin', 'eddDashboardProAdmin', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'edd_dashboard_pro_admin_nonce' ),
            'strings' => array(
                'loading' => esc_html__( 'Loading...', 'edd-customer-dashboard-pro' ),
                'saved' => esc_html__( 'Settings saved!', 'edd-customer-dashboard-pro' ),
                'error' => esc_html__( 'Error saving settings.', 'edd-customer-dashboard-pro' )
            )
        ) );
    }

    /**
     * Add frontend data to head
     */
    public function add_frontend_data() {
        if ( ! $this->should_load_frontend_assets() ) {
            return;
        }

        ?>
        <script type="text/javascript">
            var eddDashboardProData = {
                version: '<?php echo esc_js( EDD_DASHBOARD_PRO_VERSION ); ?>',
                pluginUrl: '<?php echo esc_js( EDD_DASHBOARD_PRO_PLUGIN_URL ); ?>',
                template: '<?php echo esc_js( $this->get_option( 'template', 'default' ) ); ?>',
                features: {
                    wishlist: <?php echo $this->get_option( 'enable_wishlist', true ) ? 'true' : 'false'; ?>,
                    analytics: <?php echo $this->get_option( 'enable_analytics', true ) ? 'true' : 'false'; ?>,
                    downloadTracking: <?php echo $this->get_option( 'enable_download_tracking', true ) ? 'true' : 'false'; ?>,
                    licenseManagement: <?php echo $this->get_option( 'enable_license_management', true ) ? 'true' : 'false'; ?>
                }
            };
        </script>
        <?php
    }

    /**
     * Check if we should load frontend assets
     *
     * @return bool
     */
    private function should_load_frontend_assets() {
        global $post;

        // Check if it's the dashboard page
        $dashboard_page = $this->get_option( 'dashboard_page' );
        if ( $dashboard_page && is_page( $dashboard_page ) ) {
            return true;
        }

        // Check for dashboard query var
        if ( get_query_var( 'edd-dashboard' ) ) {
            return true;
        }

        // Check if current post contains dashboard shortcode
        if ( $post && has_shortcode( $post->post_content, 'edd_customer_dashboard_pro' ) ) {
            return true;
        }

        if ( $post && has_shortcode( $post->post_content, 'edd_dashboard_pro' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Check if current admin page is a plugin page
     *
     * @param string $hook Current admin page hook
     * @return bool
     */
    private function is_plugin_admin_page( $hook ) {
        $plugin_pages = array(
            'download_page_edd-settings',
            'toplevel_page_edd-dashboard-pro'
        );

        return in_array( $hook, $plugin_pages );
    }

    /**
     * Maybe create database tables
     */
    private function maybe_create_tables() {
        $version = get_option( 'edd_dashboard_pro_db_version', '0' );
        
        if ( version_compare( $version, EDD_DASHBOARD_PRO_VERSION, '<' ) ) {
            $this->create_tables();
            update_option( 'edd_dashboard_pro_db_version', EDD_DASHBOARD_PRO_VERSION );
        }
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Wishlist table
        $wishlist_table = $wpdb->prefix . 'edd_dashboard_pro_wishlist';
        $sql = "CREATE TABLE $wishlist_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            product_id bigint(20) NOT NULL,
            date_added datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_product (user_id, product_id),
            KEY user_id (user_id),
            KEY product_id (product_id)
        ) $charset_collate;";

        // Download tracking table
        $tracking_table = $wpdb->prefix . 'edd_dashboard_pro_download_log';
        $sql .= "CREATE TABLE $tracking_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            payment_id bigint(20) NOT NULL,
            download_id bigint(20) NOT NULL,
            file_id int(11) NOT NULL DEFAULT 0,
            ip_address varchar(45) NOT NULL,
            user_agent text,
            date_downloaded datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY payment_id (payment_id),
            KEY download_id (download_id),
            KEY date_downloaded (date_downloaded)
        ) $charset_collate;";

        // Customer analytics table
        $analytics_table = $wpdb->prefix . 'edd_dashboard_pro_analytics';
        $sql .= "CREATE TABLE $analytics_table (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            metric_name varchar(100) NOT NULL,
            metric_value longtext,
            date_recorded date NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY user_metric_date (user_id, metric_name, date_recorded),
            KEY user_id (user_id),
            KEY metric_name (metric_name),
            KEY date_recorded (date_recorded)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    /**
     * Schedule cron jobs
     */
    private function schedule_cron_jobs() {
        if ( ! wp_next_scheduled( 'edd_dashboard_pro_daily_cleanup' ) ) {
            wp_schedule_event( time(), 'daily', 'edd_dashboard_pro_daily_cleanup' );
        }

        if ( ! wp_next_scheduled( 'edd_dashboard_pro_analytics_update' ) ) {
            wp_schedule_event( time(), 'hourly', 'edd_dashboard_pro_analytics_update' );
        }
    }

    /**
     * Get user data for dashboard
     *
     * @param int $user_id User ID (optional)
     * @return array
     */
    public function get_user_dashboard_data( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return array();
        }

        // Cache key
        $cache_key = 'edd_dashboard_pro_user_data_' . $user_id;
        $cached_data = get_transient( $cache_key );

        if ( false !== $cached_data ) {
            return $cached_data;
        }

        $data = array(
            'user_info' => $this->get_user_info( $user_id ),
            'purchases' => $this->get_user_purchases( $user_id ),
            'downloads' => $this->get_user_downloads( $user_id ),
            'statistics' => $this->get_user_statistics( $user_id ),
            'wishlist' => $this->get_user_wishlist( $user_id ),
            'licenses' => $this->get_user_licenses( $user_id )
        );

        // Cache for 1 hour
        set_transient( $cache_key, $data, $this->get_option( 'cache_duration', 3600 ) );

        return $data;
    }

    /**
     * Get user info
     *
     * @param int $user_id User ID
     * @return array
     */
    private function get_user_info( $user_id ) {
        $user = get_userdata( $user_id );
        
        if ( ! $user ) {
            return array();
        }

        return array(
            'id' => $user->ID,
            'username' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'registered' => $user->user_registered,
            'avatar_url' => get_avatar_url( $user_id )
        );
    }

    /**
     * Get user purchases
     *
     * @param int $user_id User ID
     * @return array
     */
    private function get_user_purchases( $user_id ) {
        $purchases = edd_get_users_purchases( $user_id, -1, false );
        $formatted_purchases = array();

        if ( $purchases ) {
            foreach ( $purchases as $purchase ) {
                $payment = new EDD_Payment( $purchase->ID );
                
                $formatted_purchases[] = array(
                    'id' => $payment->ID,
                    'number' => $payment->number,
                    'date' => $payment->date,
                    'total' => $payment->total,
                    'status' => $payment->status,
                    'downloads' => $payment->downloads,
                    'receipt_url' => edd_get_success_page_uri( '?payment_key=' . $payment->key )
                );
            }
        }

        return $formatted_purchases;
    }

    /**
     * Get user downloads
     *
     * @param int $user_id User ID
     * @return array
     */
    private function get_user_downloads( $user_id ) {
        $downloads = edd_get_users_purchased_products( $user_id );
        $formatted_downloads = array();

        if ( $downloads ) {
            foreach ( $downloads as $download ) {
                $download_data = array(
                    'id' => $download->ID,
                    'title' => get_the_title( $download->ID ),
                    'permalink' => get_permalink( $download->ID ),
                    'download_files' => edd_get_download_files( $download->ID ),
                    'purchase_count' => $this->get_user_download_purchase_count( $user_id, $download->ID ),
                    'download_count' => $this->get_user_download_count( $user_id, $download->ID ),
                    'last_downloaded' => $this->get_user_last_download_date( $user_id, $download->ID )
                );

                $formatted_downloads[] = $download_data;
            }
        }

        return $formatted_downloads;
    }

    /**
     * Get user statistics
     *
     * @param int $user_id User ID
     * @return array
     */
    private function get_user_statistics( $user_id ) {
        $stats = array(
            'total_purchases' => edd_count_purchases_of_customer( $user_id ),
            'total_spent' => edd_purchase_total_of_user( $user_id ),
            'total_downloads' => count( edd_get_users_purchased_products( $user_id ) ),
            'download_count' => $this->get_total_user_downloads( $user_id ),
            'account_age' => $this->get_user_account_age( $user_id ),
            'last_purchase' => $this->get_user_last_purchase_date( $user_id )
        );

        return $stats;
    }

    /**
     * Get user wishlist
     *
     * @param int $user_id User ID
     * @return array
     */
    private function get_user_wishlist( $user_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'edd_dashboard_pro_wishlist';
        
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT product_id, date_added FROM $table_name WHERE user_id = %d ORDER BY date_added DESC",
            $user_id
        ) );

        $wishlist = array();
        
        if ( $results ) {
            foreach ( $results as $item ) {
                $product = get_post( $item->product_id );
                
                if ( $product && $product->post_status === 'publish' ) {
                    $wishlist[] = array(
                        'id' => $item->product_id,
                        'title' => $product->post_title,
                        'permalink' => get_permalink( $item->product_id ),
                        'price' => edd_get_download_price( $item->product_id ),
                        'date_added' => $item->date_added
                    );
                }
            }
        }

        return $wishlist;
    }

    /**
     * Get user licenses (if EDD Software Licensing is active)
     *
     * @param int $user_id User ID
     * @return array
     */
    private function get_user_licenses( $user_id ) {
        if ( ! class_exists( 'EDD_Software_Licensing' ) ) {
            return array();
        }

        $licenses = edd_software_licensing()->get_license_keys_of_user( $user_id );
        $formatted_licenses = array();

        if ( $licenses ) {
            foreach ( $licenses as $license ) {
                $license_data = edd_software_licensing()->get_license( $license->ID );
                
                $formatted_licenses[] = array(
                    'id' => $license->ID,
                    'key' => $license_data->license_key,
                    'status' => $license_data->status,
                    'download_id' => $license_data->download_id,
                    'download_name' => get_the_title( $license_data->download_id ),
                    'activation_limit' => $license_data->activation_limit,
                    'activation_count' => $license_data->activation_count,
                    'expiration' => $license_data->expiration,
                    'sites' => edd_software_licensing()->get_sites( $license->ID )
                );
            }
        }

        return $formatted_licenses;
    }

    /**
     * Helper methods for statistics
     */

    /**
     * Get user download purchase count
     *
     * @param int $user_id User ID
     * @param int $download_id Download ID
     * @return int
     */
    private function get_user_download_purchase_count( $user_id, $download_id ) {
        $purchases = edd_get_users_purchases( $user_id, -1, false );
        $count = 0;

        if ( $purchases ) {
            foreach ( $purchases as $purchase ) {
                $payment = new EDD_Payment( $purchase->ID );
                
                if ( $payment->has_download( $download_id ) ) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get user download count
     *
     * @param int $user_id User ID
     * @param int $download_id Download ID
     * @return int
     */
    private function get_user_download_count( $user_id, $download_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'edd_dashboard_pro_download_log';
        
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND download_id = %d",
            $user_id,
            $download_id
        ) );
    }

    /**
     * Get user last download date
     *
     * @param int $user_id User ID
     * @param int $download_id Download ID
     * @return string|null
     */
    private function get_user_last_download_date( $user_id, $download_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'edd_dashboard_pro_download_log';
        
        return $wpdb->get_var( $wpdb->prepare(
            "SELECT date_downloaded FROM $table_name 
             WHERE user_id = %d AND download_id = %d 
             ORDER BY date_downloaded DESC LIMIT 1",
            $user_id,
            $download_id
        ) );
    }

    /**
     * Get total user downloads
     *
     * @param int $user_id User ID
     * @return int
     */
    private function get_total_user_downloads( $user_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'edd_dashboard_pro_download_log';
        
        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d",
            $user_id
        ) );
    }

    /**
     * Get user account age in days
     *
     * @param int $user_id User ID
     * @return int
     */
    private function get_user_account_age( $user_id ) {
        $user = get_userdata( $user_id );
        
        if ( ! $user ) {
            return 0;
        }

        $registered = strtotime( $user->user_registered );
        $now = time();
        
        return floor( ( $now - $registered ) / DAY_IN_SECONDS );
    }

    /**
     * Get user last purchase date
     *
     * @param int $user_id User ID
     * @return string|null
     */
    private function get_user_last_purchase_date( $user_id ) {
        $purchases = edd_get_users_purchases( $user_id, 1, false );
        
        if ( $purchases ) {
            $payment = new EDD_Payment( $purchases[0]->ID );
            return $payment->date;
        }

        return null;
    }

    /**
     * Clear user cache
     *
     * @param int $user_id User ID
     */
    public function clear_user_cache( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        delete_transient( 'edd_dashboard_pro_user_data_' . $user_id );
    }

    /**
     * Plugin cleanup on deactivation
     */
    public function cleanup() {
        // Clear scheduled crons
        wp_clear_scheduled_hook( 'edd_dashboard_pro_daily_cleanup' );
        wp_clear_scheduled_hook( 'edd_dashboard_pro_analytics_update' );

        // Clear transients
        global $wpdb;
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_edd_dashboard_pro_%' 
             OR option_name LIKE '_transient_timeout_edd_dashboard_pro_%'"
        );
    }
}

/**
 * Get the main EDD Dashboard Pro instance
 *
 * @return EDD_Dashboard_Pro
 */
function EDD_Dashboard_Pro() {
    return EDD_Dashboard_Pro::instance();
}
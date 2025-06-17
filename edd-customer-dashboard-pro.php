<?php
/**
 * Plugin Name: EDD Customer Dashboard Pro
 * Plugin URI: https://codecanyon.net/
 * Description: Transform your EDD customer experience with a modern, feature-rich dashboard. Includes purchase history, license management, download tracking, wishlist, and analytics.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: edd-customer-dashboard-pro
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * EDD Customer Dashboard Pro is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'EDD_DASHBOARD_PRO_VERSION', '1.0.0' );
define( 'EDD_DASHBOARD_PRO_PLUGIN_FILE', __FILE__ );
define( 'EDD_DASHBOARD_PRO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EDD_DASHBOARD_PRO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EDD_DASHBOARD_PRO_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'EDD_DASHBOARD_PRO_TEXT_DOMAIN', 'edd-customer-dashboard-pro' );

/**
 * Main EDD Customer Dashboard Pro Class
 */
final class EDD_Customer_Dashboard_Pro {

    /**
     * The single instance of the class
     *
     * @var EDD_Customer_Dashboard_Pro
     */
    private static $instance = null;

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = EDD_DASHBOARD_PRO_VERSION;

    /**
     * Get the single instance of the class
     *
     * @return EDD_Customer_Dashboard_Pro
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
    }

    /**
     * Hook into actions and filters
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ), 0 );
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        
        // Check if EDD is active
        add_action( 'admin_init', array( $this, 'check_edd_dependency' ) );
        
        // Plugin activation/deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Check if EDD is active before initializing
        if ( ! $this->is_edd_active() ) {
            return;
        }

        $this->load_includes();
        $this->init_classes();
        
        do_action( 'edd_dashboard_pro_loaded' );
    }

    /**
     * Load plugin text domain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain( 
            EDD_DASHBOARD_PRO_TEXT_DOMAIN, 
            false, 
            dirname( EDD_DASHBOARD_PRO_PLUGIN_BASENAME ) . '/languages/' 
        );
    }

    /**
     * Check if Easy Digital Downloads is active
     *
     * @return bool
     */
    public function is_edd_active() {
        return class_exists( 'Easy_Digital_Downloads' );
    }

    /**
     * Check EDD dependency and show admin notice if not found
     */
    public function check_edd_dependency() {
        if ( ! $this->is_edd_active() ) {
            add_action( 'admin_notices', array( $this, 'edd_missing_notice' ) );
            deactivate_plugins( EDD_DASHBOARD_PRO_PLUGIN_BASENAME );
        }
    }

    /**
     * Show admin notice when EDD is missing
     */
    public function edd_missing_notice() {
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
    }

    /**
     * Load required files
     */
    private function load_includes() {
        $includes = array(
            'includes/functions.php',
            'includes/hooks.php',
            'includes/class-security.php',
            'includes/class-dashboard-template-loader.php',
            'includes/class-dashboard-shortcodes.php',
            'includes/class-ajax-handlers.php',
            'includes/class-admin-settings.php'
        );

        foreach ( $includes as $file ) {
            $file_path = EDD_DASHBOARD_PRO_PLUGIN_DIR . $file;
            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            }
        }

        // Load admin files only in admin
        if ( is_admin() ) {
            require_once EDD_DASHBOARD_PRO_PLUGIN_DIR . 'admin/class-admin-menu.php';
            require_once EDD_DASHBOARD_PRO_PLUGIN_DIR . 'admin/class-settings-page.php';
        }
    }

    /**
     * Initialize plugin classes
     */
    private function init_classes() {
        // Security class
        if ( class_exists( 'EDD_Dashboard_Pro_Security' ) ) {
            new EDD_Dashboard_Pro_Security();
        }

        // Template loader
        if ( class_exists( 'EDD_Dashboard_Pro_Template_Loader' ) ) {
            new EDD_Dashboard_Pro_Template_Loader();
        }

        // Shortcodes
        if ( class_exists( 'EDD_Dashboard_Pro_Shortcodes' ) ) {
            new EDD_Dashboard_Pro_Shortcodes();
        }

        // AJAX handlers
        if ( class_exists( 'EDD_Dashboard_Pro_Ajax_Handlers' ) ) {
            new EDD_Dashboard_Pro_Ajax_Handlers();
        }

        // Admin settings
        if ( class_exists( 'EDD_Dashboard_Pro_Admin_Settings' ) ) {
            new EDD_Dashboard_Pro_Admin_Settings();
        }

        // Admin menu (only in admin)
        if ( is_admin() && class_exists( 'EDD_Dashboard_Pro_Admin_Menu' ) ) {
            new EDD_Dashboard_Pro_Admin_Menu();
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Check if EDD is active
        if ( ! $this->is_edd_active() ) {
            deactivate_plugins( EDD_DASHBOARD_PRO_PLUGIN_BASENAME );
            wp_die(
                esc_html__( 'EDD Customer Dashboard Pro requires Easy Digital Downloads to be installed and active.', 'edd-customer-dashboard-pro' ),
                esc_html__( 'Plugin Activation Error', 'edd-customer-dashboard-pro' ),
                array( 'back_link' => true )
            );
        }

        // Set default options
        $default_options = array(
            'template' => 'default',
            'enable_wishlist' => true,
            'enable_analytics' => true,
            'enable_support' => true,
            'download_limit_display' => true,
            'license_key_display' => true
        );

        $existing_options = get_option( 'edd_dashboard_pro_settings', array() );
        $options = array_merge( $default_options, $existing_options );
        update_option( 'edd_dashboard_pro_settings', $options );

        // Set activation flag
        update_option( 'edd_dashboard_pro_activated', true );

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up temporary data if needed
        delete_option( 'edd_dashboard_pro_activated' );
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Get plugin option
     *
     * @param string $key Option key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get_option( $key, $default = null ) {
        $options = get_option( 'edd_dashboard_pro_settings', array() );
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }

    /**
     * Update plugin option
     *
     * @param string $key Option key
     * @param mixed $value Option value
     * @return bool
     */
    public function update_option( $key, $value ) {
        $options = get_option( 'edd_dashboard_pro_settings', array() );
        $options[ $key ] = $value;
        return update_option( 'edd_dashboard_pro_settings', $options );
    }

    /**
     * Magic getter for backward compatibility
     *
     * @param string $key
     * @return mixed
     */
    public function __get( $key ) {
        if ( 'version' === $key ) {
            return $this->version;
        }
        return null;
    }
}

/**
 * Returns the main instance of EDD Customer Dashboard Pro
 *
 * @return EDD_Customer_Dashboard_Pro
 */
function EDD_Dashboard_Pro() {
    return EDD_Customer_Dashboard_Pro::instance();
}

// Initialize the plugin
EDD_Dashboard_Pro();
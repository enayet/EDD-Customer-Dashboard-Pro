<?php
/**
 * Admin Settings Class for EDD Customer Dashboard Pro
 * Handles plugin settings and configuration
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EDD Dashboard Pro Admin Settings Class
 */
class EDD_Dashboard_Pro_Admin_Settings {

    /**
     * Settings sections
     *
     * @var array
     */
    private $sections = array();

    /**
     * Settings fields
     *
     * @var array
     */
    private $fields = array();

    /**
     * Option name
     *
     * @var string
     */
    private $option_name = 'edd_dashboard_pro_settings';

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->setup_sections();
        $this->setup_fields();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_filter( 'edd_settings_sections_extensions', array( $this, 'add_settings_section' ) );
        add_filter( 'edd_settings_extensions', array( $this, 'add_settings_fields' ) );
        add_action( 'edd_settings_tab_top_extensions_edd_dashboard_pro', array( $this, 'output_section_header' ) );
    }

    /**
     * Setup settings sections
     */
    private function setup_sections() {
        $this->sections = array(
            'general' => array(
                'title' => esc_html__( 'General Settings', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Configure basic dashboard settings.', 'edd-customer-dashboard-pro' )
            ),
            'template' => array(
                'title' => esc_html__( 'Template Settings', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Choose and customize dashboard templates.', 'edd-customer-dashboard-pro' )
            ),
            'features' => array(
                'title' => esc_html__( 'Feature Settings', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Enable or disable specific dashboard features.', 'edd-customer-dashboard-pro' )
            ),
            'security' => array(
                'title' => esc_html__( 'Security Settings', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Configure security and access control.', 'edd-customer-dashboard-pro' )
            ),
            'advanced' => array(
                'title' => esc_html__( 'Advanced Settings', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Advanced configuration options.', 'edd-customer-dashboard-pro' )
            )
        );
    }

    /**
     * Setup settings fields
     */
    private function setup_fields() {
        $this->fields = array(
            // General Settings
            'dashboard_page' => array(
                'section' => 'general',
                'type' => 'select',
                'title' => esc_html__( 'Dashboard Page', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Select the page where the customer dashboard will be displayed.', 'edd-customer-dashboard-pro' ),
                'options' => $this->get_pages_options(),
                'default' => ''
            ),
            'dashboard_title' => array(
                'section' => 'general',
                'type' => 'text',
                'title' => esc_html__( 'Dashboard Title', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'The title displayed on the customer dashboard.', 'edd-customer-dashboard-pro' ),
                'default' => esc_html__( 'Customer Dashboard', 'edd-customer-dashboard-pro' ),
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'items_per_page' => array(
                'section' => 'general',
                'type' => 'number',
                'title' => esc_html__( 'Items Per Page', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Number of items to display per page in purchase history.', 'edd-customer-dashboard-pro' ),
                'default' => 10,
                'min' => 1,
                'max' => 100,
                'sanitize_callback' => 'absint'
            ),

            // Template Settings
            'template' => array(
                'section' => 'template',
                'type' => 'select',
                'title' => esc_html__( 'Dashboard Template', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Choose the template for the customer dashboard.', 'edd-customer-dashboard-pro' ),
                'options' => $this->get_template_options(),
                'default' => 'default'
            ),
            'custom_css' => array(
                'section' => 'template',
                'type' => 'textarea',
                'title' => esc_html__( 'Custom CSS', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Add custom CSS to customize the dashboard appearance.', 'edd-customer-dashboard-pro' ),
                'default' => '',
                'rows' => 10,
                'sanitize_callback' => array( $this, 'sanitize_css' )
            ),
            'show_welcome_message' => array(
                'section' => 'template',
                'type' => 'checkbox',
                'title' => esc_html__( 'Show Welcome Message', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Display a welcome message on the dashboard.', 'edd-customer-dashboard-pro' ),
                'default' => true
            ),

            // Feature Settings
            'enable_wishlist' => array(
                'section' => 'features',
                'type' => 'checkbox',
                'title' => esc_html__( 'Enable Wishlist', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Allow customers to add products to their wishlist.', 'edd-customer-dashboard-pro' ),
                'default' => true
            ),
            'enable_analytics' => array(
                'section' => 'features',
                'type' => 'checkbox',
                'title' => esc_html__( 'Enable Analytics', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Show purchase analytics and statistics to customers.', 'edd-customer-dashboard-pro' ),
                'default' => true
            ),
            'enable_support' => array(
                'section' => 'features',
                'type' => 'checkbox',
                'title' => esc_html__( 'Enable Support Center', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Display support options in the dashboard.', 'edd-customer-dashboard-pro' ),
                'default' => true
            ),
            'download_limit_display' => array(
                'section' => 'features',
                'type' => 'checkbox',
                'title' => esc_html__( 'Show Download Limits', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Display download limits and usage to customers.', 'edd-customer-dashboard-pro' ),
                'default' => true
            ),
            'license_key_display' => array(
                'section' => 'features',
                'type' => 'checkbox',
                'title' => esc_html__( 'Show License Keys', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Display license keys in the dashboard (requires EDD Software Licensing).', 'edd-customer-dashboard-pro' ),
                'default' => true
            ),
            'enable_referrals' => array(
                'section' => 'features',
                'type' => 'checkbox',
                'title' => esc_html__( 'Enable Referral Tracking', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Show referral earnings and tracking (requires compatible referral plugin).', 'edd-customer-dashboard-pro' ),
                'default' => false
            ),

            // Security Settings
            'require_login_redirect' => array(
                'section' => 'security',
                'type' => 'checkbox',
                'title' => esc_html__( 'Require Login Redirect', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Automatically redirect non-logged-in users to login page.', 'edd-customer-dashboard-pro' ),
                'default' => true
            ),
            'download_rate_limit' => array(
                'section' => 'security',
                'type' => 'number',
                'title' => esc_html__( 'Download Rate Limit', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Maximum downloads per hour per user (0 for unlimited).', 'edd-customer-dashboard-pro' ),
                'default' => 30,
                'min' => 0,
                'max' => 1000,
                'sanitize_callback' => 'absint'
            ),
            'ajax_rate_limit' => array(
                'section' => 'security',
                'type' => 'number',
                'title' => esc_html__( 'AJAX Rate Limit', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Maximum AJAX requests per hour per user.', 'edd-customer-dashboard-pro' ),
                'default' => 100,
                'min' => 10,
                'max' => 1000,
                'sanitize_callback' => 'absint'
            ),
            'enable_security_logging' => array(
                'section' => 'security',
                'type' => 'checkbox',
                'title' => esc_html__( 'Enable Security Logging', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Log security events for audit purposes.', 'edd-customer-dashboard-pro' ),
                'default' => true
            ),

            // Advanced Settings
            'cache_customer_data' => array(
                'section' => 'advanced',
                'type' => 'checkbox',
                'title' => esc_html__( 'Cache Customer Data', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Cache customer data to improve performance.', 'edd-customer-dashboard-pro' ),
                'default' => true
            ),
            'cache_duration' => array(
                'section' => 'advanced',
                'type' => 'number',
                'title' => esc_html__( 'Cache Duration (minutes)', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'How long to cache customer data.', 'edd-customer-dashboard-pro' ),
                'default' => 30,
                'min' => 1,
                'max' => 1440,
                'sanitize_callback' => 'absint'
            ),
            'enable_ajax_logging' => array(
                'section' => 'advanced',
                'type' => 'checkbox',
                'title' => esc_html__( 'Enable AJAX Logging', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Log AJAX requests for debugging (not recommended for production).', 'edd-customer-dashboard-pro' ),
                'default' => false
            ),
            'debug_mode' => array(
                'section' => 'advanced',
                'type' => 'checkbox',
                'title' => esc_html__( 'Debug Mode', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Enable debug mode for troubleshooting.', 'edd-customer-dashboard-pro' ),
                'default' => false
            ),
            'uninstall_data' => array(
                'section' => 'advanced',
                'type' => 'checkbox',
                'title' => esc_html__( 'Remove Data on Uninstall', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Remove all plugin data when uninstalling.', 'edd-customer-dashboard-pro' ),
                'default' => false
            )
        );
    }

    /**
     * Register settings with WordPress
     */
    public function register_settings() {
        register_setting(
            'edd_settings',
            $this->option_name,
            array(
                'sanitize_callback' => array( $this, 'sanitize_settings' )
            )
        );
    }

    /**
     * Add settings section to EDD
     *
     * @param array $sections Existing sections
     * @return array
     */
    public function add_settings_section( $sections ) {
        $sections['edd_dashboard_pro'] = esc_html__( 'Customer Dashboard Pro', 'edd-customer-dashboard-pro' );
        return $sections;
    }

    /**
     * Add settings fields to EDD
     *
     * @param array $settings Existing settings
     * @return array
     */
    public function add_settings_fields( $settings ) {
        $dashboard_settings = array();

        foreach ( $this->fields as $field_id => $field ) {
            $field_config = array(
                'id' => $field_id,
                'name' => $field['title'],
                'desc' => $field['description'],
                'type' => $field['type'],
                'std' => $field['default'] ?? ''
            );

            // Add field-specific configurations
            switch ( $field['type'] ) {
                case 'select':
                    $field_config['options'] = $field['options'] ?? array();
                    break;
                
                case 'number':
                    $field_config['min'] = $field['min'] ?? 0;
                    $field_config['max'] = $field['max'] ?? 999999;
                    $field_config['step'] = $field['step'] ?? 1;
                    break;
                
                case 'textarea':
                    $field_config['size'] = 'large';
                    if ( isset( $field['rows'] ) ) {
                        $field_config['rows'] = $field['rows'];
                    }
                    break;
            }

            // Add section header if this is the first field in a section
            if ( $this->is_first_field_in_section( $field_id, $field['section'] ) ) {
                $section_info = $this->sections[ $field['section'] ] ?? array();
                $dashboard_settings[ $field['section'] . '_header' ] = array(
                    'id' => $field['section'] . '_header',
                    'name' => '<h3>' . ( $section_info['title'] ?? '' ) . '</h3>',
                    'desc' => $section_info['description'] ?? '',
                    'type' => 'header'
                );
            }

            $dashboard_settings[ $field_id ] = $field_config;
        }

        $settings['edd_dashboard_pro'] = apply_filters( 'edd_dashboard_pro_settings', $dashboard_settings );

        return $settings;
    }

    /**
     * Output section header
     */
    public function output_section_header() {
        ?>
        <div class="edd-dashboard-pro-settings-header">
            <h2><?php esc_html_e( 'Customer Dashboard Pro Settings', 'edd-customer-dashboard-pro' ); ?></h2>
            <p><?php esc_html_e( 'Configure your customer dashboard settings below.', 'edd-customer-dashboard-pro' ); ?></p>
        </div>
        <?php
    }

    /**
     * Get pages options for dropdown
     *
     * @return array
     */
    private function get_pages_options() {
        $pages = get_pages( array(
            'post_status' => 'publish',
            'sort_column' => 'post_title'
        ) );

        $options = array( '' => esc_html__( 'Select a page...', 'edd-customer-dashboard-pro' ) );

        foreach ( $pages as $page ) {
            $options[ $page->ID ] = esc_html( $page->post_title );
        }

        return $options;
    }

    /**
     * Get template options
     *
     * @return array
     */
    private function get_template_options() {
        $template_loader = new EDD_Dashboard_Pro_Template_Loader();
        $templates = $template_loader->get_templates();

        $options = array();

        foreach ( $templates as $template_key => $template_data ) {
            $options[ $template_key ] = esc_html( $template_data['name'] );
        }

        if ( empty( $options ) ) {
            $options['default'] = esc_html__( 'Default Template', 'edd-customer-dashboard-pro' );
        }

        return $options;
    }

    /**
     * Check if this is the first field in a section
     *
     * @param string $field_id Field ID
     * @param string $section Section name
     * @return bool
     */
    private function is_first_field_in_section( $field_id, $section ) {
        foreach ( $this->fields as $id => $field ) {
            if ( $field['section'] === $section ) {
                return $id === $field_id;
            }
        }
        return false;
    }

    /**
     * Sanitize settings
     *
     * @param array $input Input values
     * @return array
     */
    public function sanitize_settings( $input ) {
        $sanitized = array();

        foreach ( $this->fields as $field_id => $field ) {
            if ( ! isset( $input[ $field_id ] ) ) {
                continue;
            }

            $value = $input[ $field_id ];

            // Apply field-specific sanitization
            if ( isset( $field['sanitize_callback'] ) && is_callable( $field['sanitize_callback'] ) ) {
                $value = call_user_func( $field['sanitize_callback'], $value );
            } else {
                // Default sanitization based on field type
                switch ( $field['type'] ) {
                    case 'text':
                        $value = sanitize_text_field( $value );
                        break;
                    
                    case 'textarea':
                        $value = sanitize_textarea_field( $value );
                        break;
                    
                    case 'email':
                        $value = sanitize_email( $value );
                        break;
                    
                    case 'url':
                        $value = esc_url_raw( $value );
                        break;
                    
                    case 'number':
                        $value = absint( $value );
                        if ( isset( $field['min'] ) ) {
                            $value = max( $field['min'], $value );
                        }
                        if ( isset( $field['max'] ) ) {
                            $value = min( $field['max'], $value );
                        }
                        break;
                    
                    case 'checkbox':
                        $value = ! empty( $value );
                        break;
                    
                    case 'select':
                        $options = $field['options'] ?? array();
                        if ( ! array_key_exists( $value, $options ) ) {
                            $value = $field['default'] ?? '';
                        }
                        break;
                    
                    default:
                        $value = sanitize_text_field( $value );
                }
            }

            $sanitized[ $field_id ] = $value;
        }

        return $sanitized;
    }

    /**
     * Sanitize CSS
     *
     * @param string $css CSS content
     * @return string
     */
    public function sanitize_css( $css ) {
        // Basic CSS sanitization - strip dangerous functions
        $css = wp_strip_all_tags( $css );
        
        // Remove potentially dangerous CSS functions
        $dangerous_functions = array(
            'expression',
            'javascript:',
            'vbscript:',
            'onload',
            'onerror',
            'onclick'
        );
        
        foreach ( $dangerous_functions as $function ) {
            $css = str_ireplace( $function, '', $css );
        }
        
        return $css;
    }

    /**
     * Get setting value
     *
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get_setting( $key, $default = null ) {
        $settings = get_option( $this->option_name, array() );
        
        if ( isset( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }
        
        // Return field default if no custom default provided
        if ( $default === null && isset( $this->fields[ $key ]['default'] ) ) {
            return $this->fields[ $key ]['default'];
        }
        
        return $default;
    }

    /**
     * Update setting value
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public function update_setting( $key, $value ) {
        $settings = get_option( $this->option_name, array() );
        $settings[ $key ] = $value;
        
        return update_option( $this->option_name, $settings );
    }

    /**
     * Get all settings
     *
     * @return array
     */
    public function get_all_settings() {
        $settings = get_option( $this->option_name, array() );
        
        // Merge with defaults
        foreach ( $this->fields as $field_id => $field ) {
            if ( ! isset( $settings[ $field_id ] ) && isset( $field['default'] ) ) {
                $settings[ $field_id ] = $field['default'];
            }
        }
        
        return $settings;
    }

    /**
     * Reset settings to defaults
     *
     * @return bool
     */
    public function reset_settings() {
        $defaults = array();
        
        foreach ( $this->fields as $field_id => $field ) {
            if ( isset( $field['default'] ) ) {
                $defaults[ $field_id ] = $field['default'];
            }
        }
        
        return update_option( $this->option_name, $defaults );
    }

    /**
     * Export settings
     *
     * @return array
     */
    public function export_settings() {
        $settings = $this->get_all_settings();
        
        return array(
            'version' => EDD_DASHBOARD_PRO_VERSION,
            'export_date' => current_time( 'mysql' ),
            'settings' => $settings
        );
    }

    /**
     * Import settings
     *
     * @param array $import_data Import data
     * @return bool|WP_Error
     */
    public function import_settings( $import_data ) {
        if ( ! is_array( $import_data ) || ! isset( $import_data['settings'] ) ) {
            return new WP_Error( 'invalid_data', esc_html__( 'Invalid import data format.', 'edd-customer-dashboard-pro' ) );
        }
        
        // Validate settings against known fields
        $settings = array();
        foreach ( $import_data['settings'] as $key => $value ) {
            if ( isset( $this->fields[ $key ] ) ) {
                $settings[ $key ] = $value;
            }
        }
        
        // Sanitize imported settings
        $sanitized_settings = $this->sanitize_settings( $settings );
        
        return update_option( $this->option_name, $sanitized_settings );
    }

    /**
     * Validate plugin dependencies
     *
     * @return array
     */
    public function validate_dependencies() {
        $status = array();
        
        // Check EDD
        $status['edd'] = array(
            'name' => 'Easy Digital Downloads',
            'active' => class_exists( 'Easy_Digital_Downloads' ),
            'required' => true,
            'message' => class_exists( 'Easy_Digital_Downloads' ) 
                ? esc_html__( 'Active', 'edd-customer-dashboard-pro' )
                : esc_html__( 'Required - Please install and activate Easy Digital Downloads', 'edd-customer-dashboard-pro' )
        );
        
        // Check EDD Software Licensing
        $status['edd_sl'] = array(
            'name' => 'EDD Software Licensing',
            'active' => class_exists( 'EDD_Software_Licensing' ),
            'required' => false,
            'message' => class_exists( 'EDD_Software_Licensing' ) 
                ? esc_html__( 'Active - License management features available', 'edd-customer-dashboard-pro' )
                : esc_html__( 'Optional - Install for license management features', 'edd-customer-dashboard-pro' )
        );
        
        // Check PHP version
        $min_php = '7.4';
        $current_php = PHP_VERSION;
        $status['php'] = array(
            'name' => 'PHP Version',
            'active' => version_compare( $current_php, $min_php, '>=' ),
            'required' => true,
            'message' => version_compare( $current_php, $min_php, '>=' )
                ? sprintf( esc_html__( 'Current: %s (Good)', 'edd-customer-dashboard-pro' ), $current_php )
                : sprintf( esc_html__( 'Current: %s - Minimum required: %s', 'edd-customer-dashboard-pro' ), $current_php, $min_php )
        );
        
        // Check WordPress version
        $min_wp = '5.0';
        $current_wp = get_bloginfo( 'version' );
        $status['wordpress'] = array(
            'name' => 'WordPress Version',
            'active' => version_compare( $current_wp, $min_wp, '>=' ),
            'required' => true,
            'message' => version_compare( $current_wp, $min_wp, '>=' )
                ? sprintf( esc_html__( 'Current: %s (Good)', 'edd-customer-dashboard-pro' ), $current_wp )
                : sprintf( esc_html__( 'Current: %s - Minimum required: %s', 'edd-customer-dashboard-pro' ), $current_wp, $min_wp )
        );
        
        return $status;
    }

    /**
     * Get system information
     *
     * @return array
     */
    public function get_system_info() {
        $info = array();
        
        // Plugin info
        $info['plugin'] = array(
            'version' => EDD_DASHBOARD_PRO_VERSION,
            'path' => EDD_DASHBOARD_PRO_PLUGIN_DIR,
            'url' => EDD_DASHBOARD_PRO_PLUGIN_URL
        );
        
        // WordPress info
        $info['wordpress'] = array(
            'version' => get_bloginfo( 'version' ),
            'multisite' => is_multisite(),
            'memory_limit' => ini_get( 'memory_limit' ),
            'debug_mode' => defined( 'WP_DEBUG' ) && WP_DEBUG
        );
        
        // Server info
        $info['server'] = array(
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'max_execution_time' => ini_get( 'max_execution_time' ),
            'max_input_vars' => ini_get( 'max_input_vars' ),
            'post_max_size' => ini_get( 'post_max_size' ),
            'upload_max_filesize' => ini_get( 'upload_max_filesize' )
        );
        
        // Active plugins
        $info['plugins'] = array();
        $active_plugins = get_option( 'active_plugins', array() );
        foreach ( $active_plugins as $plugin ) {
            $plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
            $info['plugins'][ $plugin ] = array(
                'name' => $plugin_data['Name'],
                'version' => $plugin_data['Version']
            );
        }
        
        // Theme info
        $theme = wp_get_theme();
        $info['theme'] = array(
            'name' => $theme->get( 'Name' ),
            'version' => $theme->get( 'Version' ),
            'template' => $theme->get_template()
        );
        
        return $info;
    }

    /**
     * Clear plugin caches
     *
     * @return bool
     */
    public function clear_caches() {
        // Clear transients
        global $wpdb;
        
        $transients_cleared = $wpdb->query(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_edd_dashboard_pro_%' 
             OR option_name LIKE '_transient_timeout_edd_dashboard_pro_%'"
        );
        
        // Clear user meta caches
        $user_meta_cleared = $wpdb->query(
            "DELETE FROM {$wpdb->usermeta} 
             WHERE meta_key LIKE '_edd_dashboard_pro_cache_%'"
        );
        
        // Clear object cache if available
        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
        }
        
        return true;
    }

    /**
     * Generate settings page documentation
     *
     * @return array
     */
    public function get_settings_documentation() {
        $docs = array();
        
        foreach ( $this->sections as $section_key => $section ) {
            $docs[ $section_key ] = array(
                'title' => $section['title'],
                'description' => $section['description'],
                'fields' => array()
            );
            
            foreach ( $this->fields as $field_key => $field ) {
                if ( $field['section'] === $section_key ) {
                    $docs[ $section_key ]['fields'][ $field_key ] = array(
                        'title' => $field['title'],
                        'description' => $field['description'],
                        'type' => $field['type'],
                        'default' => $field['default'] ?? null
                    );
                }
            }
        }
        
        return $docs;
    }

    /**
     * Get plugin health status
     *
     * @return array
     */
    public function get_health_status() {
        $status = array(
            'overall' => 'good',
            'checks' => array()
        );
        
        // Check dependencies
        $dependencies = $this->validate_dependencies();
        foreach ( $dependencies as $dep_key => $dep ) {
            $status['checks'][ 'dependency_' . $dep_key ] = array(
                'label' => $dep['name'],
                'status' => $dep['active'] ? 'good' : ( $dep['required'] ? 'critical' : 'recommended' ),
                'description' => $dep['message']
            );
            
            if ( $dep['required'] && ! $dep['active'] ) {
                $status['overall'] = 'critical';
            }
        }
        
        // Check file permissions
        $upload_dir = wp_upload_dir();
        $status['checks']['file_permissions'] = array(
            'label' => esc_html__( 'File Permissions', 'edd-customer-dashboard-pro' ),
            'status' => is_writable( $upload_dir['basedir'] ) ? 'good' : 'critical',
            'description' => is_writable( $upload_dir['basedir'] ) 
                ? esc_html__( 'Upload directory is writable', 'edd-customer-dashboard-pro' )
                : esc_html__( 'Upload directory is not writable', 'edd-customer-dashboard-pro' )
        );
        
        // Check database
        global $wpdb;
        $db_check = $wpdb->get_var( "SELECT 1" );
        $status['checks']['database'] = array(
            'label' => esc_html__( 'Database Connection', 'edd-customer-dashboard-pro' ),
            'status' => $db_check ? 'good' : 'critical',
            'description' => $db_check 
                ? esc_html__( 'Database connection is working', 'edd-customer-dashboard-pro' )
                : esc_html__( 'Database connection failed', 'edd-customer-dashboard-pro' )
        );
        
        // Check template files
        $template_loader = new EDD_Dashboard_Pro_Template_Loader();
        $templates = $template_loader->get_templates();
        $status['checks']['templates'] = array(
            'label' => esc_html__( 'Template Files', 'edd-customer-dashboard-pro' ),
            'status' => ! empty( $templates ) ? 'good' : 'critical',
            'description' => ! empty( $templates )
                ? sprintf( esc_html__( '%d template(s) found', 'edd-customer-dashboard-pro' ), count( $templates ) )
                : esc_html__( 'No templates found', 'edd-customer-dashboard-pro' )
        );
        
        return $status;
    }

    /**
     * Get field configuration
     *
     * @param string $field_id Field ID
     * @return array|null
     */
    public function get_field_config( $field_id ) {
        return $this->fields[ $field_id ] ?? null;
    }

    /**
     * Get section configuration
     *
     * @param string $section_id Section ID
     * @return array|null
     */
    public function get_section_config( $section_id ) {
        return $this->sections[ $section_id ] ?? null;
    }

    /**
     * Add custom field type
     *
     * @param string $field_id Field ID
     * @param array $field_config Field configuration
     * @return bool
     */
    public function add_custom_field( $field_id, $field_config ) {
        if ( isset( $this->fields[ $field_id ] ) ) {
            return false; // Field already exists
        }
        
        $this->fields[ $field_id ] = $field_config;
        return true;
    }

    /**
     * Remove field
     *
     * @param string $field_id Field ID
     * @return bool
     */
    public function remove_field( $field_id ) {
        if ( ! isset( $this->fields[ $field_id ] ) ) {
            return false;
        }
        
        unset( $this->fields[ $field_id ] );
        return true;
    }
}
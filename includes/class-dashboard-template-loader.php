<?php
/**
 * Template Loader Class for EDD Customer Dashboard Pro
 * Handles template loading, registration, and switching
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EDD Dashboard Pro Template Loader Class
 */
class EDD_Dashboard_Pro_Template_Loader {

    /**
     * Available templates
     *
     * @var array
     */
    private $templates = array();

    /**
     * Current template
     *
     * @var string
     */
    private $current_template = 'default';

    /**
     * Template base path
     *
     * @var string
     */
    private $template_path;

    /**
     * Constructor
     */
    public function __construct() {
        $this->template_path = EDD_DASHBOARD_PRO_PLUGIN_DIR . 'templates/';
        $this->init_hooks();
        $this->load_templates();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_template_assets' ) );
        add_filter( 'edd_dashboard_pro_template_vars', array( $this, 'add_template_vars' ) );
        add_action( 'wp_head', array( $this, 'add_template_meta' ) );
    }

    /**
     * Load and register available templates
     */
    private function load_templates() {
        $template_dirs = glob( $this->template_path . '*', GLOB_ONLYDIR );
        
        foreach ( $template_dirs as $template_dir ) {
            $template_name = basename( $template_dir );
            $template_json = $template_dir . '/template.json';
            
            if ( file_exists( $template_json ) ) {
                $template_data = json_decode( file_get_contents( $template_json ), true );
                
                if ( $template_data && $this->validate_template( $template_name, $template_data ) ) {
                    $this->templates[ $template_name ] = array_merge( $template_data, array(
                        'path' => $template_dir,
                        'url' => str_replace( EDD_DASHBOARD_PRO_PLUGIN_DIR, EDD_DASHBOARD_PRO_PLUGIN_URL, $template_dir )
                    ) );
                }
            }
        }

        // Set current template
        $this->current_template = EDD_Dashboard_Pro()->get_option( 'template', 'default' );
        
        // Fallback to default if selected template doesn't exist
        if ( ! isset( $this->templates[ $this->current_template ] ) ) {
            $this->current_template = 'default';
        }
    }

    /**
     * Validate template structure and data
     *
     * @param string $template_name Template name
     * @param array $template_data Template data from JSON
     * @return bool
     */
    private function validate_template( $template_name, $template_data ) {
        $required_fields = array( 'name', 'version', 'description' );
        
        foreach ( $required_fields as $field ) {
            if ( ! isset( $template_data[ $field ] ) ) {
                return false;
            }
        }

        // Check if required template files exist
        $template_dir = $this->template_path . $template_name;
        $required_files = array( 'dashboard.php' );
        
        foreach ( $required_files as $file ) {
            if ( ! file_exists( $template_dir . '/' . $file ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all available templates
     *
     * @return array
     */
    public function get_templates() {
        return $this->templates;
    }

    /**
     * Get current template data
     *
     * @return array|null
     */
    public function get_current_template() {
        return isset( $this->templates[ $this->current_template ] ) ? $this->templates[ $this->current_template ] : null;
    }

    /**
     * Set current template
     *
     * @param string $template_name Template name
     * @return bool
     */
    public function set_current_template( $template_name ) {
        if ( isset( $this->templates[ $template_name ] ) ) {
            $this->current_template = $template_name;
            EDD_Dashboard_Pro()->update_option( 'template', $template_name );
            return true;
        }
        return false;
    }

    /**
     * Load dashboard template
     *
     * @param array $args Template arguments
     * @return string
     */
    public function load_dashboard( $args = array() ) {
        if ( ! $this->current_template || ! isset( $this->templates[ $this->current_template ] ) ) {
            return $this->load_fallback_template( $args );
        }

        $template_data = $this->templates[ $this->current_template ];
        $template_file = $template_data['path'] . '/dashboard.php';

        if ( ! file_exists( $template_file ) ) {
            return $this->load_fallback_template( $args );
        }

        // Prepare template variables
        $template_vars = $this->prepare_template_vars( $args );
        
        // Extract variables for template use
        extract( $template_vars ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

        // Start output buffering
        ob_start();
        
        try {
            include $template_file;
        } catch ( Exception $e ) {
            ob_end_clean();
            return $this->load_fallback_template( $args );
        }
        
        return ob_get_clean();
    }

    /**
     * Load template section
     *
     * @param string $section Section name
     * @param array $args Section arguments
     * @return string
     */
    public function load_section( $section, $args = array() ) {
        if ( ! $this->current_template || ! isset( $this->templates[ $this->current_template ] ) ) {
            return '';
        }

        $template_data = $this->templates[ $this->current_template ];
        $section_file = $template_data['path'] . '/sections/' . sanitize_file_name( $section ) . '.php';

        if ( ! file_exists( $section_file ) ) {
            return '';
        }

        // Prepare template variables
        $template_vars = $this->prepare_template_vars( $args );
        
        // Extract variables for template use
        extract( $template_vars ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

        // Start output buffering
        ob_start();
        
        try {
            include $section_file;
        } catch ( Exception $e ) {
            ob_end_clean();
            return '';
        }
        
        return ob_get_clean();
    }

    /**
     * Prepare template variables
     *
     * @param array $args Additional arguments
     * @return array
     */
    private function prepare_template_vars( $args = array() ) {
        // Get customer data
        $customer_data = edd_dashboard_pro_get_current_customer();
        $stats = array();
        $purchases = array();
        $wishlist = array();
        $download_history = array();
        $analytics = array();

        if ( $customer_data ) {
            $stats = edd_dashboard_pro_get_customer_stats( $customer_data['id'] );
            $purchases = edd_dashboard_pro_get_customer_purchases( $customer_data['id'], 10 );
            $wishlist = edd_dashboard_pro_get_wishlist();
            $download_history = edd_dashboard_pro_get_download_history( 0, 10 );
            $analytics = edd_dashboard_pro_get_purchase_analytics();
        }

        $template_vars = array(
            'customer' => $customer_data,
            'stats' => $stats,
            'purchases' => $purchases,
            'wishlist' => $wishlist,
            'download_history' => $download_history,
            'analytics' => $analytics,
            'current_user' => wp_get_current_user(),
            'template_data' => $this->get_current_template(),
            'nonces' => $this->get_nonces(),
            'settings' => $this->get_template_settings(),
            'urls' => $this->get_urls(),
            'is_logged_in' => is_user_logged_in(),
            'can_access' => EDD_Dashboard_Pro_Security::can_access_dashboard()
        );

        // Merge with additional arguments
        $template_vars = array_merge( $template_vars, $args );

        // Apply filters for customization
        return apply_filters( 'edd_dashboard_pro_template_vars', $template_vars, $this->current_template );
    }

    /**
     * Get security nonces for template
     *
     * @return array
     */
    private function get_nonces() {
        return array(
            'dashboard' => EDD_Dashboard_Pro_Security::create_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_DASHBOARD ),
            'download' => EDD_Dashboard_Pro_Security::create_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_DOWNLOAD ),
            'wishlist' => EDD_Dashboard_Pro_Security::create_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_WISHLIST ),
            'license' => EDD_Dashboard_Pro_Security::create_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_LICENSE ),
            'ajax' => EDD_Dashboard_Pro_Security::create_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_AJAX )
        );
    }

    /**
     * Get template settings
     *
     * @return array
     */
    private function get_template_settings() {
        return array(
            'enable_wishlist' => EDD_Dashboard_Pro()->get_option( 'enable_wishlist', true ),
            'enable_analytics' => EDD_Dashboard_Pro()->get_option( 'enable_analytics', true ),
            'enable_support' => EDD_Dashboard_Pro()->get_option( 'enable_support', true ),
            'show_download_limits' => EDD_Dashboard_Pro()->get_option( 'download_limit_display', true ),
            'show_license_keys' => EDD_Dashboard_Pro()->get_option( 'license_key_display', true ),
            'items_per_page' => EDD_Dashboard_Pro()->get_option( 'items_per_page', 10 ),
            'date_format' => get_option( 'date_format' ),
            'currency_symbol' => edd_currency_symbol(),
            'currency_position' => edd_get_option( 'currency_position', 'before' )
        );
    }

    /**
     * Get useful URLs for templates
     *
     * @return array
     */
    private function get_urls() {
        return array(
            'dashboard' => $this->get_dashboard_url(),
            'shop' => edd_get_checkout_uri(),
            'account' => edd_get_user_verification_url(),
            'logout' => wp_logout_url( home_url() ),
            'ajax' => admin_url( 'admin-ajax.php' ),
            'assets' => $this->get_template_asset_url()
        );
    }

    /**
     * Get dashboard URL
     *
     * @return string
     */
    public function get_dashboard_url() {
        $dashboard_page = EDD_Dashboard_Pro()->get_option( 'dashboard_page' );
        
        if ( $dashboard_page ) {
            return get_permalink( $dashboard_page );
        }
        
        return add_query_arg( 'edd-dashboard', '1', home_url() );
    }

    /**
     * Get template asset URL
     *
     * @param string $file File name (optional)
     * @return string
     */
    public function get_template_asset_url( $file = '' ) {
        $template_data = $this->get_current_template();
        
        if ( ! $template_data ) {
            return EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/';
        }
        
        $url = $template_data['url'] . '/';
        
        if ( $file ) {
            $url .= sanitize_file_name( $file );
        }
        
        return $url;
    }

    /**
     * Enqueue template assets
     */
    public function enqueue_template_assets() {
        // Only load on dashboard pages
        if ( ! $this->is_dashboard_page() ) {
            return;
        }

        $template_data = $this->get_current_template();
        
        if ( ! $template_data ) {
            return;
        }

        $template_url = $template_data['url'];
        $version = $template_data['version'];

        // Enqueue template CSS
        $css_file = $template_data['path'] . '/style.css';
        if ( file_exists( $css_file ) ) {
            wp_enqueue_style(
                'edd-dashboard-pro-template-' . $this->current_template,
                $template_url . '/style.css',
                array(),
                $version
            );
        }

        // Enqueue template JavaScript
        $js_file = $template_data['path'] . '/script.js';
        if ( file_exists( $js_file ) ) {
            wp_enqueue_script(
                'edd-dashboard-pro-template-' . $this->current_template,
                $template_url . '/script.js',
                array( 'jquery' ),
                $version,
                true
            );

            // Localize script with data
            wp_localize_script(
                'edd-dashboard-pro-template-' . $this->current_template,
                'eddDashboardPro',
                array(
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'nonce' => EDD_Dashboard_Pro_Security::create_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_AJAX ),
                    'strings' => array(
                        'loading' => esc_html__( 'Loading...', 'edd-customer-dashboard-pro' ),
                        'error' => esc_html__( 'An error occurred. Please try again.', 'edd-customer-dashboard-pro' ),
                        'success' => esc_html__( 'Success!', 'edd-customer-dashboard-pro' ),
                        'confirm' => esc_html__( 'Are you sure?', 'edd-customer-dashboard-pro' ),
                        'copied' => esc_html__( 'Copied to clipboard!', 'edd-customer-dashboard-pro' )
                    ),
                    'settings' => $this->get_template_settings()
                )
            );
        }

        // Enqueue base dashboard styles
        wp_enqueue_style(
            'edd-dashboard-pro-base',
            EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/css/dashboard-base.css',
            array(),
            EDD_DASHBOARD_PRO_VERSION
        );

        // Enqueue core dashboard script
        wp_enqueue_script(
            'edd-dashboard-pro-core',
            EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/js/dashboard-core.js',
            array( 'jquery' ),
            EDD_DASHBOARD_PRO_VERSION,
            true
        );
    }

    /**
     * Add template meta tags
     */
    public function add_template_meta() {
        if ( ! $this->is_dashboard_page() ) {
            return;
        }

        $template_data = $this->get_current_template();
        
        if ( $template_data ) {
            echo '<meta name="edd-dashboard-template" content="' . esc_attr( $this->current_template ) . '">' . "\n";
            echo '<meta name="edd-dashboard-template-version" content="' . esc_attr( $template_data['version'] ) . '">' . "\n";
        }
    }

    /**
     * Add template-specific variables to template vars
     *
     * @param array $vars Existing template vars
     * @return array
     */
    public function add_template_vars( $vars ) {
        $vars['template_loader'] = $this;
        return $vars;
    }

    /**
     * Load fallback template
     *
     * @param array $args Template arguments
     * @return string
     */
    private function load_fallback_template( $args = array() ) {
        $fallback = '<div class="edd-dashboard-pro-error">';
        $fallback .= '<h3>' . esc_html__( 'Dashboard Unavailable', 'edd-customer-dashboard-pro' ) . '</h3>';
        $fallback .= '<p>' . esc_html__( 'Sorry, the dashboard template could not be loaded. Please contact support.', 'edd-customer-dashboard-pro' ) . '</p>';
        $fallback .= '</div>';
        
        return $fallback;
    }

    /**
     * Check if current page is dashboard page
     *
     * @return bool
     */
    private function is_dashboard_page() {
        global $post;
        
        // Check for dashboard shortcode
        if ( $post && has_shortcode( $post->post_content, 'edd_customer_dashboard_pro' ) ) {
            return true;
        }
        
        // Check for dashboard query var
        if ( get_query_var( 'edd-dashboard' ) ) {
            return true;
        }
        
        // Check if this is the designated dashboard page
        $dashboard_page = EDD_Dashboard_Pro()->get_option( 'dashboard_page' );
        if ( $dashboard_page && is_page( $dashboard_page ) ) {
            return true;
        }
        
        return false;
    }

    /**
     * Get template path for inclusion
     *
     * @param string $template_name Template name
     * @param string $file File name
     * @return string|false
     */
    public function get_template_path( $template_name = null, $file = 'dashboard.php' ) {
        if ( ! $template_name ) {
            $template_name = $this->current_template;
        }
        
        if ( ! isset( $this->templates[ $template_name ] ) ) {
            return false;
        }
        
        $file_path = $this->templates[ $template_name ]['path'] . '/' . sanitize_file_name( $file );
        
        return file_exists( $file_path ) ? $file_path : false;
    }

    /**
     * Check if template supports feature
     *
     * @param string $feature Feature name
     * @param string $template_name Template name (optional)
     * @return bool
     */
    public function template_supports( $feature, $template_name = null ) {
        if ( ! $template_name ) {
            $template_name = $this->current_template;
        }
        
        if ( ! isset( $this->templates[ $template_name ] ) ) {
            return false;
        }
        
        $template_data = $this->templates[ $template_name ];
        
        if ( isset( $template_data['supports'] ) && is_array( $template_data['supports'] ) ) {
            return in_array( $feature, $template_data['supports'] );
        }
        
        return false;
    }

    /**
     * Get template info for admin display
     *
     * @param string $template_name Template name
     * @return array|null
     */
    public function get_template_info( $template_name ) {
        if ( ! isset( $this->templates[ $template_name ] ) ) {
            return null;
        }
        
        $template_data = $this->templates[ $template_name ];
        
        return array(
            'name' => $template_data['name'],
            'description' => $template_data['description'],
            'version' => $template_data['version'],
            'author' => isset( $template_data['author'] ) ? $template_data['author'] : '',
            'screenshot' => isset( $template_data['screenshot'] ) ? $template_data['url'] . '/' . $template_data['screenshot'] : '',
            'supports' => isset( $template_data['supports'] ) ? $template_data['supports'] : array()
        );
    }
}
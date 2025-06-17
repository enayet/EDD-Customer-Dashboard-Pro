<?php
/**
 * Settings Page Class for EDD Customer Dashboard Pro
 * Handles the rendering and management of settings pages
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EDD Dashboard Pro Settings Page Class
 */
class EDD_Dashboard_Pro_Settings_Page {

    /**
     * Settings instance
     *
     * @var EDD_Dashboard_Pro_Admin_Settings
     */
    private $settings;

    /**
     * Current page
     *
     * @var string
     */
    private $current_page;

    /**
     * Current section
     *
     * @var string
     */
    private $current_section;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settings = new EDD_Dashboard_Pro_Admin_Settings();
        $this->current_page = $this->get_current_page();
        $this->current_section = $this->get_current_section();
        
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'admin_init', array( $this, 'register_settings_fields' ) );
        add_action( 'admin_post_edd_dashboard_pro_save_settings', array( $this, 'handle_settings_save' ) );
        add_action( 'wp_ajax_edd_dashboard_pro_test_template', array( $this, 'test_template_ajax' ) );
        add_action( 'wp_ajax_edd_dashboard_pro_reset_section', array( $this, 'reset_section_ajax' ) );
    }

    /**
     * Render settings page content
     *
     * @param string $page Page identifier
     */
    public function render_page( $page = 'general' ) {
        $this->current_page = $page;
        
        // Check permissions
        if ( ! current_user_can( 'manage_shop_settings' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'edd-customer-dashboard-pro' ) );
        }

        ?>
        <div class="wrap edd-dashboard-pro-settings">
            <?php $this->render_page_header(); ?>
            <?php $this->render_page_navigation(); ?>
            <?php $this->render_page_content(); ?>
        </div>
        <?php
    }

    /**
     * Render page header
     */
    private function render_page_header() {
        ?>
        <div class="settings-page-header">
            <h1>
                <?php esc_html_e( 'Customer Dashboard Pro Settings', 'edd-customer-dashboard-pro' ); ?>
                <span class="settings-version">v<?php echo esc_html( EDD_DASHBOARD_PRO_VERSION ); ?></span>
            </h1>
            <p class="settings-description">
                <?php esc_html_e( 'Configure your customer dashboard settings to provide the best experience for your customers.', 'edd-customer-dashboard-pro' ); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render page navigation
     */
    private function render_page_navigation() {
        $sections = $this->get_settings_sections();
        ?>
        <nav class="settings-nav-wrapper">
            <ul class="settings-nav">
                <?php foreach ( $sections as $section_id => $section ) : ?>
                    <li class="settings-nav-item">
                        <a href="<?php echo esc_url( $this->get_section_url( $section_id ) ); ?>" 
                           class="settings-nav-link <?php echo $this->current_section === $section_id ? 'active' : ''; ?>">
                            <?php if ( isset( $section['icon'] ) ) : ?>
                                <span class="dashicons dashicons-<?php echo esc_attr( $section['icon'] ); ?>"></span>
                            <?php endif; ?>
                            <?php echo esc_html( $section['title'] ); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php
    }

    /**
     * Render page content
     */
    private function render_page_content() {
        ?>
        <div class="settings-content-wrapper">
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="settings-form">
                <?php
                wp_nonce_field( 'edd_dashboard_pro_save_settings', '_wpnonce' );
                
                // Hidden fields
                echo '<input type="hidden" name="action" value="edd_dashboard_pro_save_settings">';
                echo '<input type="hidden" name="section" value="' . esc_attr( $this->current_section ) . '">';
                
                // Render section content
                $this->render_section_content( $this->current_section );
                ?>
                
                <div class="settings-form-footer">
                    <?php submit_button( esc_html__( 'Save Settings', 'edd-customer-dashboard-pro' ), 'primary', 'submit', false ); ?>
                    
                    <button type="button" class="button button-secondary reset-section" 
                            data-section="<?php echo esc_attr( $this->current_section ); ?>">
                        <?php esc_html_e( 'Reset Section', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                    
                    <?php if ( $this->current_section === 'template' ) : ?>
                        <button type="button" class="button button-secondary test-template">
                            <?php esc_html_e( 'Preview Template', 'edd-customer-dashboard-pro' ); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
            
            <?php $this->render_section_sidebar(); ?>
        </div>
        <?php
    }

    /**
     * Render section content
     *
     * @param string $section Section identifier
     */
    private function render_section_content( $section ) {
        $sections = $this->get_settings_sections();
        
        if ( ! isset( $sections[ $section ] ) ) {
            $section = 'general';
        }

        $section_data = $sections[ $section ];
        
        ?>
        <div class="settings-section" data-section="<?php echo esc_attr( $section ); ?>">
            <div class="settings-section-header">
                <h2><?php echo esc_html( $section_data['title'] ); ?></h2>
                <?php if ( isset( $section_data['description'] ) ) : ?>
                    <p class="settings-section-description"><?php echo esc_html( $section_data['description'] ); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="settings-section-content">
                <?php
                switch ( $section ) {
                    case 'general':
                        $this->render_general_section();
                        break;
                    case 'template':
                        $this->render_template_section();
                        break;
                    case 'features':
                        $this->render_features_section();
                        break;
                    case 'security':
                        $this->render_security_section();
                        break;
                    case 'advanced':
                        $this->render_advanced_section();
                        break;
                    default:
                        $this->render_general_section();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render general settings section
     */
    private function render_general_section() {
        $settings = $this->settings->get_all_settings();
        ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="dashboard_page"><?php esc_html_e( 'Dashboard Page', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <select name="dashboard_page" id="dashboard_page" class="regular-text">
                            <option value=""><?php esc_html_e( 'Select a page...', 'edd-customer-dashboard-pro' ); ?></option>
                            <?php
                            $pages = get_pages( array( 'post_status' => 'publish' ) );
                            foreach ( $pages as $page ) {
                                printf(
                                    '<option value="%d" %s>%s</option>',
                                    esc_attr( $page->ID ),
                                    selected( $settings['dashboard_page'] ?? '', $page->ID, false ),
                                    esc_html( $page->post_title )
                                );
                            }
                            ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e( 'Select the page where the customer dashboard will be displayed. Add the [edd_customer_dashboard_pro] shortcode to this page.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                        
                        <?php if ( empty( $settings['dashboard_page'] ) ) : ?>
                            <div class="settings-quick-action">
                                <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>" 
                                   class="button button-secondary" target="_blank">
                                    <?php esc_html_e( 'Create New Page', 'edd-customer-dashboard-pro' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="dashboard_title"><?php esc_html_e( 'Dashboard Title', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="dashboard_title" id="dashboard_title" 
                               value="<?php echo esc_attr( $settings['dashboard_title'] ?? esc_html__( 'Customer Dashboard', 'edd-customer-dashboard-pro' ) ); ?>" 
                               class="regular-text">
                        <p class="description">
                            <?php esc_html_e( 'The title displayed on the customer dashboard page.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="items_per_page"><?php esc_html_e( 'Items Per Page', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="number" name="items_per_page" id="items_per_page" 
                               value="<?php echo esc_attr( $settings['items_per_page'] ?? 10 ); ?>" 
                               min="1" max="100" class="small-text">
                        <p class="description">
                            <?php esc_html_e( 'Number of items to display per page in purchase history and other lists.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="show_welcome_message"><?php esc_html_e( 'Welcome Message', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <label for="show_welcome_message">
                            <input type="checkbox" name="show_welcome_message" id="show_welcome_message" 
                                   value="1" <?php checked( $settings['show_welcome_message'] ?? true ); ?>>
                            <?php esc_html_e( 'Display welcome message on dashboard', 'edd-customer-dashboard-pro' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Show a personalized welcome message when customers access their dashboard.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render template settings section
     */
    private function render_template_section() {
        $settings = $this->settings->get_all_settings();
        $template_loader = new EDD_Dashboard_Pro_Template_Loader();
        $templates = $template_loader->get_templates();
        $current_template = $template_loader->get_current_template();
        ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="template"><?php esc_html_e( 'Dashboard Template', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <div class="template-selector">
                            <?php foreach ( $templates as $template_key => $template_data ) : ?>
                                <div class="template-option">
                                    <label>
                                        <input type="radio" name="template" value="<?php echo esc_attr( $template_key ); ?>" 
                                               <?php checked( $settings['template'] ?? 'default', $template_key ); ?>>
                                        <div class="template-preview">
                                            <?php if ( isset( $template_data['screenshot'] ) && $template_data['screenshot'] ) : ?>
                                                <img src="<?php echo esc_url( $template_data['screenshot'] ); ?>" 
                                                     alt="<?php echo esc_attr( $template_data['name'] ); ?>">
                                            <?php else : ?>
                                                <div class="template-placeholder">
                                                    <span class="dashicons dashicons-admin-appearance"></span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="template-info">
                                                <strong><?php echo esc_html( $template_data['name'] ); ?></strong>
                                                <span class="template-version">v<?php echo esc_html( $template_data['version'] ); ?></span>
                                                <p class="template-description"><?php echo esc_html( $template_data['description'] ); ?></p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ( $current_template ) : ?>
                            <div class="current-template-info">
                                <p><strong><?php esc_html_e( 'Active Template:', 'edd-customer-dashboard-pro' ); ?></strong> 
                                   <?php echo esc_html( $current_template['name'] ); ?> v<?php echo esc_html( $current_template['version'] ); ?></p>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="custom_css"><?php esc_html_e( 'Custom CSS', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <textarea name="custom_css" id="custom_css" rows="10" cols="50" class="large-text code"><?php 
                            echo esc_textarea( $settings['custom_css'] ?? '' ); 
                        ?></textarea>
                        <p class="description">
                            <?php esc_html_e( 'Add custom CSS to customize the dashboard appearance. This CSS will be loaded after the template styles.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                        
                        <div class="css-helper">
                            <details>
                                <summary><?php esc_html_e( 'CSS Examples', 'edd-customer-dashboard-pro' ); ?></summary>
                                <div class="css-examples">
                                    <h4><?php esc_html_e( 'Common Customizations:', 'edd-customer-dashboard-pro' ); ?></h4>
                                    <pre><code>/* Change primary color */
.edd-customer-dashboard-pro .btn {
    background: #your-color;
}

/* Customize stats cards */
.stat-card {
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Hide specific sections */
.dashboard-section.analytics {
    display: none;
}</code></pre>
                                </div>
                            </details>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render features settings section
     */
    private function render_features_section() {
        $settings = $this->settings->get_all_settings();
        ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Dashboard Features', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'Dashboard Features', 'edd-customer-dashboard-pro' ); ?></legend>
                            
                            <label for="enable_wishlist">
                                <input type="checkbox" name="enable_wishlist" id="enable_wishlist" 
                                       value="1" <?php checked( $settings['enable_wishlist'] ?? true ); ?>>
                                <?php esc_html_e( 'Enable Wishlist', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Allow customers to add products to their wishlist for future purchases.', 'edd-customer-dashboard-pro' ); ?></p>
                            <br>
                            
                            <label for="enable_analytics">
                                <input type="checkbox" name="enable_analytics" id="enable_analytics" 
                                       value="1" <?php checked( $settings['enable_analytics'] ?? true ); ?>>
                                <?php esc_html_e( 'Enable Analytics', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Show purchase analytics and statistics to customers.', 'edd-customer-dashboard-pro' ); ?></p>
                            <br>
                            
                            <label for="enable_support">
                                <input type="checkbox" name="enable_support" id="enable_support" 
                                       value="1" <?php checked( $settings['enable_support'] ?? true ); ?>>
                                <?php esc_html_e( 'Enable Support Center', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Display support options and help center in the dashboard.', 'edd-customer-dashboard-pro' ); ?></p>
                            <br>
                            
                            <label for="enable_referrals">
                                <input type="checkbox" name="enable_referrals" id="enable_referrals" 
                                       value="1" <?php checked( $settings['enable_referrals'] ?? false ); ?>>
                                <?php esc_html_e( 'Enable Referral Tracking', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Show referral earnings and tracking (requires compatible referral plugin).', 'edd-customer-dashboard-pro' ); ?></p>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e( 'Display Options', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'Display Options', 'edd-customer-dashboard-pro' ); ?></legend>
                            
                            <label for="download_limit_display">
                                <input type="checkbox" name="download_limit_display" id="download_limit_display" 
                                       value="1" <?php checked( $settings['download_limit_display'] ?? true ); ?>>
                                <?php esc_html_e( 'Show Download Limits', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Display download limits and usage information to customers.', 'edd-customer-dashboard-pro' ); ?></p>
                            <br>
                            
                            <label for="license_key_display">
                                <input type="checkbox" name="license_key_display" id="license_key_display" 
                                       value="1" <?php checked( $settings['license_key_display'] ?? true ); ?>>
                                <?php esc_html_e( 'Show License Keys', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Display license keys in the dashboard (requires EDD Software Licensing).', 'edd-customer-dashboard-pro' ); ?>
                                <?php if ( ! class_exists( 'EDD_Software_Licensing' ) ) : ?>
                                    <span class="description-warning"><?php esc_html_e( '⚠️ EDD Software Licensing not detected.', 'edd-customer-dashboard-pro' ); ?></span>
                                <?php endif; ?>
                            </p>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render security settings section
     */
    private function render_security_section() {
        $settings = $this->settings->get_all_settings();
        ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Access Control', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <label for="require_login_redirect">
                            <input type="checkbox" name="require_login_redirect" id="require_login_redirect" 
                                   value="1" <?php checked( $settings['require_login_redirect'] ?? true ); ?>>
                            <?php esc_html_e( 'Redirect to login page', 'edd-customer-dashboard-pro' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Automatically redirect non-logged-in users to the login page when accessing the dashboard.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="download_rate_limit"><?php esc_html_e( 'Download Rate Limit', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="number" name="download_rate_limit" id="download_rate_limit" 
                               value="<?php echo esc_attr( $settings['download_rate_limit'] ?? 30 ); ?>" 
                               min="0" max="1000" class="small-text">
                        <span><?php esc_html_e( 'downloads per hour per user', 'edd-customer-dashboard-pro' ); ?></span>
                        <p class="description">
                            <?php esc_html_e( 'Maximum number of file downloads allowed per user per hour. Set to 0 for unlimited.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="ajax_rate_limit"><?php esc_html_e( 'AJAX Rate Limit', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="number" name="ajax_rate_limit" id="ajax_rate_limit" 
                               value="<?php echo esc_attr( $settings['ajax_rate_limit'] ?? 100 ); ?>" 
                               min="10" max="1000" class="small-text">
                        <span><?php esc_html_e( 'requests per hour per user', 'edd-customer-dashboard-pro' ); ?></span>
                        <p class="description">
                            <?php esc_html_e( 'Maximum number of AJAX requests allowed per user per hour to prevent abuse.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e( 'Security Logging', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <label for="enable_security_logging">
                            <input type="checkbox" name="enable_security_logging" id="enable_security_logging" 
                                   value="1" <?php checked( $settings['enable_security_logging'] ?? true ); ?>>
                            <?php esc_html_e( 'Enable security event logging', 'edd-customer-dashboard-pro' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Log security events such as login attempts, download requests, and access violations for audit purposes.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                        
                        <?php if ( $settings['enable_security_logging'] ?? true ) : ?>
                            <div class="security-log-info">
                                <p><strong><?php esc_html_e( 'Security Log Status:', 'edd-customer-dashboard-pro' ); ?></strong></p>
                                <?php
                                $log_entries = get_option( 'edd_dashboard_pro_security_logs', array() );
                                $log_count = count( $log_entries );
                                ?>
                                <p><?php printf( esc_html__( '%d security events logged', 'edd-customer-dashboard-pro' ), $log_count ); ?></p>
                                
                                <?php if ( $log_count > 0 ) : ?>
                                    <button type="button" class="button button-secondary view-security-logs">
                                        <?php esc_html_e( 'View Recent Logs', 'edd-customer-dashboard-pro' ); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render advanced settings section
     */
    private function render_advanced_section() {
        $settings = $this->settings->get_all_settings();
        ?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Performance', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'Performance Settings', 'edd-customer-dashboard-pro' ); ?></legend>
                            
                            <label for="cache_customer_data">
                                <input type="checkbox" name="cache_customer_data" id="cache_customer_data" 
                                       value="1" <?php checked( $settings['cache_customer_data'] ?? true ); ?>>
                                <?php esc_html_e( 'Cache customer data', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Cache customer data to improve dashboard loading speed.', 'edd-customer-dashboard-pro' ); ?></p>
                        </fieldset>
                    </td>
                </tr>
                
                <tr class="cache-duration-row" <?php echo ! ( $settings['cache_customer_data'] ?? true ) ? 'style="display: none;"' : ''; ?>>
                    <th scope="row">
                        <label for="cache_duration"><?php esc_html_e( 'Cache Duration', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cache_duration" id="cache_duration" 
                               value="<?php echo esc_attr( $settings['cache_duration'] ?? 30 ); ?>" 
                               min="1" max="1440" class="small-text">
                        <span><?php esc_html_e( 'minutes', 'edd-customer-dashboard-pro' ); ?></span>
                        <p class="description">
                            <?php esc_html_e( 'How long to cache customer data. Shorter durations show more recent data but may impact performance.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e( 'Debugging', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'Debugging Options', 'edd-customer-dashboard-pro' ); ?></legend>
                            
                            <label for="debug_mode">
                                <input type="checkbox" name="debug_mode" id="debug_mode" 
                                       value="1" <?php checked( $settings['debug_mode'] ?? false ); ?>>
                                <?php esc_html_e( 'Enable debug mode', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Enable debug mode for troubleshooting. Not recommended for production sites.', 'edd-customer-dashboard-pro' ); ?></p>
                            <br>
                            
                            <label for="enable_ajax_logging">
                                <input type="checkbox" name="enable_ajax_logging" id="enable_ajax_logging" 
                                       value="1" <?php checked( $settings['enable_ajax_logging'] ?? false ); ?>>
                                <?php esc_html_e( 'Enable AJAX logging', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( 'Log AJAX requests for debugging purposes. Only enable when troubleshooting issues.', 'edd-customer-dashboard-pro' ); ?></p>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e( 'Data Management', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <label for="uninstall_data">
                            <input type="checkbox" name="uninstall_data" id="uninstall_data" 
                                   value="1" <?php checked( $settings['uninstall_data'] ?? false ); ?>>
                            <?php esc_html_e( 'Remove all data when uninstalling plugin', 'edd-customer-dashboard-pro' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Warning: This will permanently delete all plugin settings, customer data, and logs when the plugin is uninstalled.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e( 'System Information', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <div class="system-info-summary">
                            <?php
                            $system_info = $this->settings->get_system_info();
                            $health_status = $this->settings->get_health_status();
                            ?>
                            <p><strong><?php esc_html_e( 'Plugin Version:', 'edd-customer-dashboard-pro' ); ?></strong> <?php echo esc_html( $system_info['plugin']['version'] ); ?></p>
                            <p><strong><?php esc_html_e( 'WordPress Version:', 'edd-customer-dashboard-pro' ); ?></strong> <?php echo esc_html( $system_info['wordpress']['version'] ); ?></p>
                            <p><strong><?php esc_html_e( 'PHP Version:', 'edd-customer-dashboard-pro' ); ?></strong> <?php echo esc_html( $system_info['server']['php_version'] ); ?></p>
                            <p><strong><?php esc_html_e( 'Health Status:', 'edd-customer-dashboard-pro' ); ?></strong> 
                               <span class="health-status health-<?php echo esc_attr( $health_status['overall'] ); ?>">
                                   <?php echo esc_html( ucfirst( $health_status['overall'] ) ); ?>
                               </span>
                            </p>
                            
                            <p>
                                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-dashboard-pro-status' ) ); ?>" 
                                   class="button button-secondary">
                                    <?php esc_html_e( 'View Full System Status', 'edd-customer-dashboard-pro' ); ?>
                                </a>
                            </p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }

    /**
     * Render section sidebar
     */
    private function render_section_sidebar() {
        ?>
        <div class="settings-sidebar">
            <?php
            switch ( $this->current_section ) {
                case 'general':
                    $this->render_general_sidebar();
                    break;
                case 'template':
                    $this->render_template_sidebar();
                    break;
                case 'features':
                    $this->render_features_sidebar();
                    break;
                case 'security':
                    $this->render_security_sidebar();
                    break;
                case 'advanced':
                    $this->render_advanced_sidebar();
                    break;
            }
            ?>
            
            <!-- Common Sidebar Content -->
            <div class="sidebar-widget">
                <h3><?php esc_html_e( 'Quick Actions', 'edd-customer-dashboard-pro' ); ?></h3>
                <div class="sidebar-actions">
                    <a href="<?php echo esc_url( $this->get_dashboard_preview_url() ); ?>" 
                       class="button button-secondary" target="_blank">
                        <?php esc_html_e( 'Preview Dashboard', 'edd-customer-dashboard-pro' ); ?>
                    </a>
                    
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-dashboard-pro-templates' ) ); ?>" 
                       class="button button-secondary">
                        <?php esc_html_e( 'Manage Templates', 'edd-customer-dashboard-pro' ); ?>
                    </a>
                    
                    <button type="button" class="button button-secondary clear-cache-btn">
                        <?php esc_html_e( 'Clear Cache', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                </div>
            </div>
            
            <div class="sidebar-widget">
                <h3><?php esc_html_e( 'Documentation', 'edd-customer-dashboard-pro' ); ?></h3>
                <div class="sidebar-links">
                    <a href="#" target="_blank" class="sidebar-link">
                        <span class="dashicons dashicons-book"></span>
                        <?php esc_html_e( 'Setup Guide', 'edd-customer-dashboard-pro' ); ?>
                    </a>
                    <a href="#" target="_blank" class="sidebar-link">
                        <span class="dashicons dashicons-video-alt3"></span>
                        <?php esc_html_e( 'Video Tutorials', 'edd-customer-dashboard-pro' ); ?>
                    </a>
                    <a href="#" target="_blank" class="sidebar-link">
                        <span class="dashicons dashicons-sos"></span>
                        <?php esc_html_e( 'Get Support', 'edd-customer-dashboard-pro' ); ?>
                    </a>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render general section sidebar
     */
    private function render_general_sidebar() {
        ?>
        <div class="sidebar-widget">
            <h3><?php esc_html_e( 'Setup Checklist', 'edd-customer-dashboard-pro' ); ?></h3>
            <div class="setup-checklist">
                <?php
                $checklist_items = array(
                    'dashboard_page' => esc_html__( 'Dashboard page selected', 'edd-customer-dashboard-pro' ),
                    'shortcode_added' => esc_html__( 'Shortcode added to page', 'edd-customer-dashboard-pro' ),
                    'template_configured' => esc_html__( 'Template configured', 'edd-customer-dashboard-pro' ),
                    'features_enabled' => esc_html__( 'Features enabled', 'edd-customer-dashboard-pro' )
                );
                
                foreach ( $checklist_items as $item_key => $item_label ) {
                    $is_completed = $this->is_setup_step_completed( $item_key );
                    ?>
                    <div class="checklist-item <?php echo $is_completed ? 'completed' : 'pending'; ?>">
                        <span class="dashicons dashicons-<?php echo $is_completed ? 'yes-alt' : 'minus'; ?>"></span>
                        <?php echo esc_html( $item_label ); ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        
        <div class="sidebar-widget">
            <h3><?php esc_html_e( 'Shortcode Reference', 'edd-customer-dashboard-pro' ); ?></h3>
            <div class="shortcode-reference">
                <div class="shortcode-item">
                    <code>[edd_customer_dashboard_pro]</code>
                    <p><?php esc_html_e( 'Complete dashboard', 'edd-customer-dashboard-pro' ); ?></p>
                </div>
                <div class="shortcode-item">
                    <code>[edd_customer_stats]</code>
                    <p><?php esc_html_e( 'Statistics only', 'edd-customer-dashboard-pro' ); ?></p>
                </div>
                <div class="shortcode-item">
                    <code>[edd_customer_purchases]</code>
                    <p><?php esc_html_e( 'Purchase history', 'edd-customer-dashboard-pro' ); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render template section sidebar
     */
    private function render_template_sidebar() {
        $template_loader = new EDD_Dashboard_Pro_Template_Loader();
        $current_template = $template_loader->get_current_template();
        ?>
        <div class="sidebar-widget">
            <h3><?php esc_html_e( 'Template Info', 'edd-customer-dashboard-pro' ); ?></h3>
            <?php if ( $current_template ) : ?>
                <div class="template-info">
                    <p><strong><?php esc_html_e( 'Active Template:', 'edd-customer-dashboard-pro' ); ?></strong></p>
                    <p><?php echo esc_html( $current_template['name'] ); ?></p>
                    <p><strong><?php esc_html_e( 'Version:', 'edd-customer-dashboard-pro' ); ?></strong> <?php echo esc_html( $current_template['version'] ); ?></p>
                    <p><strong><?php esc_html_e( 'Description:', 'edd-customer-dashboard-pro' ); ?></strong></p>
                    <p><?php echo esc_html( $current_template['description'] ); ?></p>
                    
                    <?php if ( isset( $current_template['author'] ) ) : ?>
                        <p><strong><?php esc_html_e( 'Author:', 'edd-customer-dashboard-pro' ); ?></strong> <?php echo esc_html( $current_template['author'] ); ?></p>
                    <?php endif; ?>
                </div>
            <?php else : ?>
                <p><?php esc_html_e( 'No template information available.', 'edd-customer-dashboard-pro' ); ?></p>
            <?php endif; ?>
        </div>
        
        <div class="sidebar-widget">
            <h3><?php esc_html_e( 'Template Development', 'edd-customer-dashboard-pro' ); ?></h3>
            <div class="sidebar-links">
                <a href="#" target="_blank" class="sidebar-link">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <?php esc_html_e( 'Template Development Guide', 'edd-customer-dashboard-pro' ); ?>
                </a>
                <a href="#" target="_blank" class="sidebar-link">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e( 'Download Template Kit', 'edd-customer-dashboard-pro' ); ?>
                </a>
            </div>
        </div>
        <?php
    }

    /**
     * Render features section sidebar
     */
    private function render_features_sidebar() {
        ?>
        <div class="sidebar-widget">
            <h3><?php esc_html_e( 'Feature Status', 'edd-customer-dashboard-pro' ); ?></h3>
            <div class="feature-status">
                <?php
                $features = array(
                    'wishlist' => array(
                        'name' => esc_html__( 'Wishlist', 'edd-customer-dashboard-pro' ),
                        'enabled' => $this->settings->get_setting( 'enable_wishlist', true ),
                        'dependency' => true
                    ),
                    'analytics' => array(
                        'name' => esc_html__( 'Analytics', 'edd-customer-dashboard-pro' ),
                        'enabled' => $this->settings->get_setting( 'enable_analytics', true ),
                        'dependency' => true
                    ),
                    'licensing' => array(
                        'name' => esc_html__( 'License Management', 'edd-customer-dashboard-pro' ),
                        'enabled' => $this->settings->get_setting( 'license_key_display', true ),
                        'dependency' => class_exists( 'EDD_Software_Licensing' )
                    ),
                    'support' => array(
                        'name' => esc_html__( 'Support Center', 'edd-customer-dashboard-pro' ),
                        'enabled' => $this->settings->get_setting( 'enable_support', true ),
                        'dependency' => true
                    )
                );
                
                foreach ( $features as $feature_key => $feature ) {
                    $status_class = '';
                    $status_icon = 'minus';
                    
                    if ( $feature['enabled'] && $feature['dependency'] ) {
                        $status_class = 'enabled';
                        $status_icon = 'yes-alt';
                    } elseif ( $feature['enabled'] && ! $feature['dependency'] ) {
                        $status_class = 'dependency-missing';
                        $status_icon = 'warning';
                    } else {
                        $status_class = 'disabled';
                        $status_icon = 'minus';
                    }
                    ?>
                    <div class="feature-status-item <?php echo esc_attr( $status_class ); ?>">
                        <span class="dashicons dashicons-<?php echo esc_attr( $status_icon ); ?>"></span>
                        <span class="feature-name"><?php echo esc_html( $feature['name'] ); ?></span>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        
        <div class="sidebar-widget">
            <h3><?php esc_html_e( 'Recommended Extensions', 'edd-customer-dashboard-pro' ); ?></h3>
            <div class="recommended-extensions">
                <?php if ( ! class_exists( 'EDD_Software_Licensing' ) ) : ?>
                    <div class="extension-recommendation">
                        <h4><?php esc_html_e( 'EDD Software Licensing', 'edd-customer-dashboard-pro' ); ?></h4>
                        <p><?php esc_html_e( 'Enable license key management and site activation features.', 'edd-customer-dashboard-pro' ); ?></p>
                        <a href="https://easydigitaldownloads.com/downloads/software-licensing/" target="_blank" class="button button-secondary">
                            <?php esc_html_e( 'Learn More', 'edd-customer-dashboard-pro' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render security section sidebar
     */
    private function render_security_sidebar() {
        ?>
        <div class="sidebar-widget">
            <h3><?php esc_html_e( 'Security Status', 'edd-customer-dashboard-pro' ); ?></h3>
            <div class="security-status">
                <?php
                $security_checks = array(
                    'ssl' => array(
                        'name' => esc_html__( 'SSL Certificate', 'edd-customer-dashboard-pro' ),
                        'status' => is_ssl(),
                        'critical' => true
                    ),
                    'wp_version' => array(
                        'name' => esc_html__( 'WordPress Version', 'edd-customer-dashboard-pro' ),
                        'status' => version_compare( get_bloginfo( 'version' ), '5.0', '>=' ),
                        'critical' => true
                    ),
                    'php_version' => array(
                        'name' => esc_html__( 'PHP Version', 'edd-customer-dashboard-pro' ),
                        'status' => version_compare( PHP_VERSION, '7.4', '>=' ),
                        'critical' => true
                    ),
                    'file_permissions' => array(
                        'name' => esc_html__( 'File Permissions', 'edd-customer-dashboard-pro' ),
                        'status' => is_writable( wp_upload_dir()['basedir'] ),
                        'critical' => false
                    )
                );
                
                foreach ( $security_checks as $check_key => $check ) {
                    $status_class = $check['status'] ? 'secure' : ( $check['critical'] ? 'critical' : 'warning' );
                    $status_icon = $check['status'] ? 'yes-alt' : 'warning';
                    ?>
                    <div class="security-check-item <?php echo esc_attr( $status_class ); ?>">
                        <span class="dashicons dashicons-<?php echo esc_attr( $status_icon ); ?>"></span>
                        <span class="check-name"><?php echo esc_html( $check['name'] ); ?></span>
                    </div>
                    <?php
                }
                ?>
                
                <div class="security-actions">
                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-dashboard-pro-status' ) ); ?>" 
                       class="button button-secondary">
                        <?php esc_html_e( 'View Security Report', 'edd-customer-dashboard-pro' ); ?>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="sidebar-widget">
            <h3><?php esc_html_e( 'Security Tips', 'edd-customer-dashboard-pro' ); ?></h3>
            <div class="security-tips">
                <ul>
                    <li><?php esc_html_e( 'Use strong, unique passwords', 'edd-customer-dashboard-pro' ); ?></li>
                    <li><?php esc_html_e( 'Enable two-factor authentication', 'edd-customer-dashboard-pro' ); ?></li>
                    <li><?php esc_html_e( 'Keep WordPress and plugins updated', 'edd-customer-dashboard-pro' ); ?></li>
                    <li><?php esc_html_e( 'Regular security audits', 'edd-customer-dashboard-pro' ); ?></li>
                    <li><?php esc_html_e( 'Monitor security logs', 'edd-customer-dashboard-pro' ); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Render advanced section sidebar
     */
    private function render_advanced_sidebar() {
        ?>
        <div class="sidebar-widget">
            <h3><?php esc_html_e( 'Performance Metrics', 'edd-customer-dashboard-pro' ); ?></h3>
            <div class="performance-metrics">
                <?php
                $cache_size = $this->get_cache_size();
                $active_users = $this->get_active_users_count();
                ?>
                <div class="metric-item">
                    <span class="metric-label"><?php esc_html_e( 'Cache Size:', 'edd-customer-dashboard-pro' ); ?></span>
                    <span class="metric-value"><?php echo esc_html( size_format( $cache_size ) ); ?></span>
                </div>
                <div class="metric-item">
                    <span class="metric-label"><?php esc_html_e( 'Active Users (24h):', 'edd-customer-dashboard-pro' ); ?></span>
                    <span class="metric-value"><?php echo esc_html( number_format_i18n( $active_users ) ); ?></span>
                </div>
                <div class="metric-item">
                    <span class="metric-label"><?php esc_html_e( 'Memory Usage:', 'edd-customer-dashboard-pro' ); ?></span>
                    <span class="metric-value"><?php echo esc_html( size_format( memory_get_peak_usage( true ) ) ); ?></span>
                </div>
            </div>
        </div>
        
        <div class="sidebar-widget">
            <h3><?php esc_html_e( 'Maintenance Tools', 'edd-customer-dashboard-pro' ); ?></h3>
            <div class="maintenance-tools">
                <button type="button" class="button button-secondary cleanup-logs-btn">
                    <?php esc_html_e( 'Cleanup Old Logs', 'edd-customer-dashboard-pro' ); ?>
                </button>
                <button type="button" class="button button-secondary optimize-db-btn">
                    <?php esc_html_e( 'Optimize Database', 'edd-customer-dashboard-pro' ); ?>
                </button>
                <button type="button" class="button button-secondary export-settings-btn">
                    <?php esc_html_e( 'Export Settings', 'edd-customer-dashboard-pro' ); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Handle settings save
     */
    public function handle_settings_save() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'edd_dashboard_pro_save_settings' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_shop_settings' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions.', 'edd-customer-dashboard-pro' ) );
        }

        $section = sanitize_text_field( $_POST['section'] ?? 'general' );
        $settings = $this->settings->get_all_settings();

        // Process section-specific settings
        switch ( $section ) {
            case 'general':
                $settings['dashboard_page'] = absint( $_POST['dashboard_page'] ?? 0 );
                $settings['dashboard_title'] = sanitize_text_field( $_POST['dashboard_title'] ?? '' );
                $settings['items_per_page'] = max( 1, min( 100, absint( $_POST['items_per_page'] ?? 10 ) ) );
                $settings['show_welcome_message'] = ! empty( $_POST['show_welcome_message'] );
                break;

            case 'template':
                $settings['template'] = sanitize_key( $_POST['template'] ?? 'default' );
                $settings['custom_css'] = $this->settings->sanitize_css( $_POST['custom_css'] ?? '' );
                break;

            case 'features':
                $settings['enable_wishlist'] = ! empty( $_POST['enable_wishlist'] );
                $settings['enable_analytics'] = ! empty( $_POST['enable_analytics'] );
                $settings['enable_support'] = ! empty( $_POST['enable_support'] );
                $settings['enable_referrals'] = ! empty( $_POST['enable_referrals'] );
                $settings['download_limit_display'] = ! empty( $_POST['download_limit_display'] );
                $settings['license_key_display'] = ! empty( $_POST['license_key_display'] );
                break;

            case 'security':
                $settings['require_login_redirect'] = ! empty( $_POST['require_login_redirect'] );
                $settings['download_rate_limit'] = max( 0, min( 1000, absint( $_POST['download_rate_limit'] ?? 30 ) ) );
                $settings['ajax_rate_limit'] = max( 10, min( 1000, absint( $_POST['ajax_rate_limit'] ?? 100 ) ) );
                $settings['enable_security_logging'] = ! empty( $_POST['enable_security_logging'] );
                break;

            case 'advanced':
                $settings['cache_customer_data'] = ! empty( $_POST['cache_customer_data'] );
                $settings['cache_duration'] = max( 1, min( 1440, absint( $_POST['cache_duration'] ?? 30 ) ) );
                $settings['debug_mode'] = ! empty( $_POST['debug_mode'] );
                $settings['enable_ajax_logging'] = ! empty( $_POST['enable_ajax_logging'] );
                $settings['uninstall_data'] = ! empty( $_POST['uninstall_data'] );
                break;
        }

        // Save settings
        $result = update_option( 'edd_dashboard_pro_settings', $settings );

        // Clear relevant caches
        if ( $section === 'template' ) {
            $this->settings->clear_caches();
        }

        // Redirect back with success message
        $redirect_url = add_query_arg(
            array(
                'page' => 'edd-settings',
                'tab' => 'extensions',
                'section' => 'edd_dashboard_pro',
                'settings-updated' => 'true'
            ),
            admin_url( 'edit.php?post_type=download' )
        );

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Test template AJAX handler
     */
    public function test_template_ajax() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'edd_dashboard_pro_admin_nonce' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_shop_settings' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'edd-customer-dashboard-pro' ) ) );
        }

        $template = sanitize_key( $_POST['template'] ?? '' );
        
        if ( empty( $template ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'No template specified.', 'edd-customer-dashboard-pro' ) ) );
        }

        $template_loader = new EDD_Dashboard_Pro_Template_Loader();
        $template_info = $template_loader->get_template_info( $template );

        if ( ! $template_info ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Template not found.', 'edd-customer-dashboard-pro' ) ) );
        }

        // Generate preview URL
        $dashboard_page = $this->settings->get_setting( 'dashboard_page' );
        
        if ( $dashboard_page ) {
            $preview_url = add_query_arg( 'preview_template', $template, get_permalink( $dashboard_page ) );
        } else {
            $preview_url = home_url( '?edd-dashboard=1&preview_template=' . $template );
        }

        wp_send_json_success( array(
            'preview_url' => $preview_url,
            'template_info' => $template_info
        ) );
    }

    /**
     * Reset section AJAX handler
     */
    public function reset_section_ajax() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'edd_dashboard_pro_admin_nonce' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ) ) );
        }

        // Check permissions
        if ( ! current_user_can( 'manage_shop_settings' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'edd-customer-dashboard-pro' ) ) );
        }

        $section = sanitize_key( $_POST['section'] ?? '' );
        
        if ( empty( $section ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'No section specified.', 'edd-customer-dashboard-pro' ) ) );
        }

        // Reset section to defaults
        $settings = $this->settings->get_all_settings();
        $field_configs = $this->get_section_fields( $section );

        foreach ( $field_configs as $field_id => $field_config ) {
            if ( isset( $field_config['default'] ) ) {
                $settings[ $field_id ] = $field_config['default'];
            }
        }

        // Save updated settings
        update_option( 'edd_dashboard_pro_settings', $settings );

        wp_send_json_success( array(
            'message' => esc_html__( 'Section reset to defaults successfully!', 'edd-customer-dashboard-pro' )
        ) );
    }

    /**
     * Register settings fields
     */
    public function register_settings_fields() {
        // Add custom field validation
        add_filter( 'pre_update_option_edd_dashboard_pro_settings', array( $this, 'validate_settings' ), 10, 2 );
    }

    /**
     * Validate settings before saving
     *
     * @param mixed $value New value
     * @param mixed $old_value Old value
     * @return mixed
     */
    public function validate_settings( $value, $old_value ) {
        if ( ! is_array( $value ) ) {
            return $old_value;
        }

        // Validate dashboard page exists
        if ( isset( $value['dashboard_page'] ) && ! empty( $value['dashboard_page'] ) ) {
            $page = get_post( $value['dashboard_page'] );
            if ( ! $page || $page->post_status !== 'publish' ) {
                $value['dashboard_page'] = $old_value['dashboard_page'] ?? '';
                add_settings_error(
                    'edd_dashboard_pro_settings',
                    'invalid_dashboard_page',
                    esc_html__( 'Invalid dashboard page selected. Please choose a published page.', 'edd-customer-dashboard-pro' ),
                    'error'
                );
            }
        }

        // Validate template exists
        if ( isset( $value['template'] ) ) {
            $template_loader = new EDD_Dashboard_Pro_Template_Loader();
            $templates = $template_loader->get_templates();
            
            if ( ! isset( $templates[ $value['template'] ] ) ) {
                $value['template'] = $old_value['template'] ?? 'default';
                add_settings_error(
                    'edd_dashboard_pro_settings',
                    'invalid_template',
                    esc_html__( 'Invalid template selected. Reverting to previous template.', 'edd-customer-dashboard-pro' ),
                    'error'
                );
            }
        }

        // Validate numeric values
        $numeric_fields = array(
            'items_per_page' => array( 'min' => 1, 'max' => 100 ),
            'download_rate_limit' => array( 'min' => 0, 'max' => 1000 ),
            'ajax_rate_limit' => array( 'min' => 10, 'max' => 1000 ),
            'cache_duration' => array( 'min' => 1, 'max' => 1440 )
        );

        foreach ( $numeric_fields as $field => $limits ) {
            if ( isset( $value[ $field ] ) ) {
                $val = absint( $value[ $field ] );
                if ( $val < $limits['min'] || $val > $limits['max'] ) {
                    $value[ $field ] = $old_value[ $field ] ?? $limits['min'];
                    add_settings_error(
                        'edd_dashboard_pro_settings',
                        'invalid_' . $field,
                        sprintf(
                            /* translators: %1$s: field name, %2$d: min value, %3$d: max value */
                            esc_html__( '%1$s must be between %2$d and %3$d.', 'edd-customer-dashboard-pro' ),
                            ucwords( str_replace( '_', ' ', $field ) ),
                            $limits['min'],
                            $limits['max']
                        ),
                        'error'
                    );
                }
            }
        }

        // Sanitize custom CSS
        if ( isset( $value['custom_css'] ) ) {
            $value['custom_css'] = $this->settings->sanitize_css( $value['custom_css'] );
        }

        return $value;
    }

    /**
     * Get current page
     *
     * @return string
     */
    private function get_current_page() {
        return sanitize_text_field( $_GET['page'] ?? 'general' );
    }

    /**
     * Get current section
     *
     * @return string
     */
    private function get_current_section() {
        $section = sanitize_text_field( $_GET['section'] ?? 'general' );
        $valid_sections = array_keys( $this->get_settings_sections() );
        
        return in_array( $section, $valid_sections, true ) ? $section : 'general';
    }

    /**
     * Get settings sections
     *
     * @return array
     */
    private function get_settings_sections() {
        return array(
            'general' => array(
                'title' => esc_html__( 'General', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Basic dashboard configuration and display settings.', 'edd-customer-dashboard-pro' ),
                'icon' => 'admin-generic'
            ),
            'template' => array(
                'title' => esc_html__( 'Template', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Choose and customize dashboard templates and appearance.', 'edd-customer-dashboard-pro' ),
                'icon' => 'admin-appearance'
            ),
            'features' => array(
                'title' => esc_html__( 'Features', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Enable or disable specific dashboard features and functionality.', 'edd-customer-dashboard-pro' ),
                'icon' => 'admin-plugins'
            ),
            'security' => array(
                'title' => esc_html__( 'Security', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Configure security settings and access controls.', 'edd-customer-dashboard-pro' ),
                'icon' => 'lock'
            ),
            'advanced' => array(
                'title' => esc_html__( 'Advanced', 'edd-customer-dashboard-pro' ),
                'description' => esc_html__( 'Advanced configuration options and performance settings.', 'edd-customer-dashboard-pro' ),
                'icon' => 'admin-tools'
            )
        );
    }

    /**
     * Get section URL
     *
     * @param string $section Section identifier
     * @return string
     */
    private function get_section_url( $section ) {
        return add_query_arg(
            array(
                'post_type' => 'download',
                'page' => 'edd-settings',
                'tab' => 'extensions',
                'section' => 'edd_dashboard_pro',
                'settings_section' => $section
            ),
            admin_url( 'edit.php' )
        );
    }

    /**
     * Get dashboard preview URL
     *
     * @return string
     */
    private function get_dashboard_preview_url() {
        $dashboard_page = $this->settings->get_setting( 'dashboard_page' );
        
        if ( $dashboard_page ) {
            return get_permalink( $dashboard_page );
        }
        
        return home_url( '?edd-dashboard=1' );
    }

    /**
     * Check if setup step is completed
     *
     * @param string $step Step identifier
     * @return bool
     */
    private function is_setup_step_completed( $step ) {
        switch ( $step ) {
            case 'dashboard_page':
                return ! empty( $this->settings->get_setting( 'dashboard_page' ) );
            
            case 'shortcode_added':
                $dashboard_page = $this->settings->get_setting( 'dashboard_page' );
                if ( $dashboard_page ) {
                    $page = get_post( $dashboard_page );
                    return $page && has_shortcode( $page->post_content, 'edd_customer_dashboard_pro' );
                }
                return false;
            
            case 'template_configured':
                return ! empty( $this->settings->get_setting( 'template' ) );
            
            case 'features_enabled':
                return $this->settings->get_setting( 'enable_wishlist', true ) || 
                       $this->settings->get_setting( 'enable_analytics', true );
            
            default:
                return false;
        }
    }

    /**
     * Get section fields
     *
     * @param string $section Section identifier
     * @return array
     */
    private function get_section_fields( $section ) {
        $all_fields = $this->settings->get_all_settings();
        $section_fields = array();

        // Define which fields belong to which sections
        $field_sections = array(
            'general' => array( 'dashboard_page', 'dashboard_title', 'items_per_page', 'show_welcome_message' ),
            'template' => array( 'template', 'custom_css' ),
            'features' => array( 'enable_wishlist', 'enable_analytics', 'enable_support', 'enable_referrals', 'download_limit_display', 'license_key_display' ),
            'security' => array( 'require_login_redirect', 'download_rate_limit', 'ajax_rate_limit', 'enable_security_logging' ),
            'advanced' => array( 'cache_customer_data', 'cache_duration', 'debug_mode', 'enable_ajax_logging', 'uninstall_data' )
        );

        if ( isset( $field_sections[ $section ] ) ) {
            foreach ( $field_sections[ $section ] as $field ) {
                if ( isset( $all_fields[ $field ] ) ) {
                    $section_fields[ $field ] = $all_fields[ $field ];
                }
            }
        }

        return $section_fields;
    }

    /**
     * Get cache size
     *
     * @return int Cache size in bytes
     */
    private function get_cache_size() {
        global $wpdb;

        $cache_size = $wpdb->get_var(
            "SELECT SUM(LENGTH(option_value)) 
             FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_edd_dashboard_pro_%'"
        );

        return (int) $cache_size;
    }

    /**
     * Get active users count
     *
     * @return int Number of active users in last 24 hours
     */
    private function get_active_users_count() {
        global $wpdb;

        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) 
             FROM {$wpdb->usermeta} 
             WHERE meta_key = '_edd_dashboard_pro_last_login' 
             AND meta_value >= %s",
            date( 'Y-m-d H:i:s', strtotime( '-24 hours' ) )
        ) );

        return (int) $count;
    }

    /**
     * Add inline styles for settings page
     */
    public function add_inline_styles() {
        ?>
        <style>
        .edd-dashboard-pro-settings {
            max-width: 1200px;
        }
        
        .settings-page-header {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .settings-page-header h1 {
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .settings-version {
            background: #667eea;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: normal;
        }
        
        .settings-nav-wrapper {
            margin-bottom: 20px;
        }
        
        .settings-nav {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 5px;
            background: #fff;
            border-radius: 8px;
            padding: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .settings-nav-item {
            margin: 0;
        }
        
        .settings-nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            text-decoration: none;
            color: #666;
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .settings-nav-link:hover {
            background: #f8f9fa;
            color: #333;
        }
        
        .settings-nav-link.active {
            background: #667eea;
            color: white;
        }
        
        .settings-content-wrapper {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
        }
        
        .settings-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .settings-section-header h2 {
            margin-top: 0;
            color: #333;
        }
        
        .settings-section-description {
            color: #666;
            margin-bottom: 20px;
        }
        
        .settings-form-footer {
            border-top: 1px solid #eee;
            padding-top: 20px;
            margin-top: 30px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .settings-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .sidebar-widget {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .sidebar-widget h3 {
            margin: 0;
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            border-radius: 8px 8px 0 0;
            font-size: 14px;
            font-weight: 600;
        }
        
        .sidebar-widget .inside {
            padding: 20px;
        }
        
        .sidebar-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 15px 20px;
        }
        
        .sidebar-links {
            padding: 15px 20px;
        }
        
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 0;
            text-decoration: none;
            color: #666;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .sidebar-link:last-child {
            border-bottom: none;
        }
        
        .sidebar-link:hover {
            color: #667eea;
        }
        
        .template-selector {
            display: grid;
            gap: 15px;
        }
        
        .template-option {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.2s ease;
        }
        
        .template-option:hover {
            border-color: #667eea;
        }
        
        .template-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        
        .template-option input[type="radio"]:checked + .template-preview {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .template-preview {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 6px;
        }
        
        .template-preview img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .template-placeholder {
            width: 80px;
            height: 60px;
            background: #f0f0f0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }
        
        .template-info strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .template-version {
            color: #666;
            font-size: 12px;
            margin-left: 8px;
        }
        
        .template-description {
            color: #666;
            font-size: 13px;
            margin: 5px 0 0 0;
        }
        
        .css-helper {
            margin-top: 10px;
        }
        
        .css-examples {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .css-examples pre {
            background: #fff;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 12px;
        }
        
        .setup-checklist {
            padding: 15px 20px;
        }
        
        .checklist-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .checklist-item:last-child {
            border-bottom: none;
        }
        
        .checklist-item.completed {
            color: #2d7d32;
        }
        
        .checklist-item.pending {
            color: #666;
        }
        
        .shortcode-reference {
            padding: 15px 20px;
        }
        
        .shortcode-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .shortcode-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .shortcode-item code {
            display: block;
            background: #f8f9fa;
            padding: 5px 8px;
            border-radius: 4px;
            margin-bottom: 5px;
            font-size: 12px;
        }
        
        .feature-status {
            padding: 15px 20px;
        }
        
        .feature-status-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
        }
        
        .feature-status-item.enabled {
            color: #2d7d32;
        }
        
        .feature-status-item.disabled {
            color: #999;
        }
        
        .feature-status-item.dependency-missing {
            color: #f57c00;
        }
        
        .security-status {
            padding: 15px 20px;
        }
        
        .security-check-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
        }
        
        .security-check-item.secure {
            color: #2d7d32;
        }
        
        .security-check-item.warning {
            color: #f57c00;
        }
        
        .security-check-item.critical {
            color: #d32f2f;
        }
        
        .performance-metrics {
            padding: 15px 20px;
        }
        
        .metric-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .metric-item:last-child {
            border-bottom: none;
        }
        
        .metric-label {
            color: #666;
            font-size: 13px;
        }
        
        .metric-value {
            font-weight: 600;
            color: #333;
        }
        
        .maintenance-tools {
            padding: 15px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .health-status.health-good {
            color: #2d7d32;
        }
        
        .health-status.health-warning {
            color: #f57c00;
        }
        
        .health-status.health-critical {
            color: #d32f2f;
        }
        
        .description-warning {
            color: #f57c00;
            font-weight: 600;
        }
        
        .current-template-info {
            background: #f8f9ff;
            border: 1px solid #667eea;
            border-radius: 4px;
            padding: 10px;
            margin-top: 15px;
        }
        
        .security-actions {
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
            margin-top: 15px;
        }
        
        .security-tips ul {
            padding-left: 20px;
            margin: 0;
        }
        
        .security-tips li {
            padding: 4px 0;
            color: #666;
        }
        
        .system-info-summary {
            padding: 15px 20px;
        }
        
        .system-info-summary p {
            margin: 8px 0;
            color: #666;
        }
        
        .cache-duration-row {
            transition: opacity 0.2s ease;
        }
        
        .cache-duration-row.hidden {
            opacity: 0.5;
            pointer-events: none;
        }
        
        @media (max-width: 782px) {
            .settings-content-wrapper {
                grid-template-columns: 1fr;
            }
            
            .settings-nav {
                flex-direction: column;
            }
            
            .settings-form-footer {
                flex-direction: column;
                align-items: stretch;
            }
            
            .template-preview {
                flex-direction: column;
                text-align: center;
            }
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // Handle cache toggle
            $('#cache_customer_data').on('change', function() {
                $('.cache-duration-row').toggle(this.checked);
            });
            
            // Handle template preview
            $('.test-template').on('click', function() {
                var template = $('input[name="template"]:checked').val();
                if (template) {
                    $.post(ajaxurl, {
                        action: 'edd_dashboard_pro_test_template',
                        template: template,
                        nonce: eddDashboardProAdmin.nonce
                    }, function(response) {
                        if (response.success) {
                            window.open(response.data.preview_url, '_blank');
                        } else {
                            alert(response.data.message);
                        }
                    });
                }
            });
            
            // Handle section reset
            $('.reset-section').on('click', function() {
                if (confirm(eddDashboardProAdmin.strings.confirmReset)) {
                    var section = $(this).data('section');
                    $.post(ajaxurl, {
                        action: 'edd_dashboard_pro_reset_section',
                        section: section,
                        nonce: eddDashboardProAdmin.nonce
                    }, function(response) {
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.data.message);
                        }
                    });
                }
            });
            
            // Handle maintenance tools
            $('.clear-cache-btn').on('click', function() {
                if (confirm(eddDashboardProAdmin.strings.confirmClearCache)) {
                    // Implement cache clear functionality
                    $(this).text(eddDashboardProAdmin.strings.processing);
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Initialize settings page
     */
    public function init() {
        add_action( 'admin_head', array( $this, 'add_inline_styles' ) );
    }
}

// Initialize if we're in the admin
if ( is_admin() ) {
    $edd_dashboard_pro_settings_page = new EDD_Dashboard_Pro_Settings_Page();
    $edd_dashboard_pro_settings_page->init();
}
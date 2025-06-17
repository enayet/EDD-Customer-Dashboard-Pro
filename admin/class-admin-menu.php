<?php
/**
 * Admin Menu Class for EDD Customer Dashboard Pro
 * Handles admin menu creation and page routing
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EDD Dashboard Pro Admin Menu Class
 */
class EDD_Dashboard_Pro_Admin_Menu {

    /**
     * Menu slug
     *
     * @var string
     */
    private $menu_slug = 'edd-dashboard-pro';

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'handle_admin_actions' ) );
        add_filter( 'plugin_action_links_' . EDD_DASHBOARD_PRO_PLUGIN_BASENAME, array( $this, 'add_plugin_action_links' ) );
        add_filter( 'plugin_row_meta', array( $this, 'add_plugin_row_meta' ), 10, 2 );
        add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );
        add_action( 'wp_ajax_edd_dashboard_pro_dismiss_notice', array( $this, 'dismiss_notice' ) );
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Add submenu under Downloads
        add_submenu_page(
            'edit.php?post_type=download',
            esc_html__( 'Dashboard Pro', 'edd-customer-dashboard-pro' ),
            esc_html__( 'Dashboard Pro', 'edd-customer-dashboard-pro' ),
            'manage_shop_settings',
            $this->menu_slug,
            array( $this, 'render_admin_page' )
        );

        // Add submenu under Downloads for Templates
        add_submenu_page(
            'edit.php?post_type=download',
            esc_html__( 'Dashboard Templates', 'edd-customer-dashboard-pro' ),
            esc_html__( 'Dashboard Templates', 'edd-customer-dashboard-pro' ),
            'manage_shop_settings',
            $this->menu_slug . '-templates',
            array( $this, 'render_templates_page' )
        );

        // Add submenu for System Status
        add_submenu_page(
            'edit.php?post_type=download',
            esc_html__( 'Dashboard System Status', 'edd-customer-dashboard-pro' ),
            esc_html__( 'System Status', 'edd-customer-dashboard-pro' ),
            'manage_shop_settings',
            $this->menu_slug . '-status',
            array( $this, 'render_status_page' )
        );
    }

    /**
     * Render main admin page
     */
    public function render_admin_page() {
        $active_tab = $this->get_active_tab();
        ?>
        <div class="wrap edd-dashboard-pro-admin">
            <h1>
                <?php esc_html_e( 'Customer Dashboard Pro', 'edd-customer-dashboard-pro' ); ?>
                <span class="title-count theme-count"><?php echo esc_html( EDD_DASHBOARD_PRO_VERSION ); ?></span>
            </h1>
            
            <nav class="nav-tab-wrapper wp-clearfix">
                <a href="<?php echo esc_url( $this->get_tab_url( 'overview' ) ); ?>" 
                   class="nav-tab <?php echo $active_tab === 'overview' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Overview', 'edd-customer-dashboard-pro' ); ?>
                </a>
                <a href="<?php echo esc_url( $this->get_tab_url( 'settings' ) ); ?>" 
                   class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Settings', 'edd-customer-dashboard-pro' ); ?>
                </a>
                <a href="<?php echo esc_url( $this->get_tab_url( 'tools' ) ); ?>" 
                   class="nav-tab <?php echo $active_tab === 'tools' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Tools', 'edd-customer-dashboard-pro' ); ?>
                </a>
                <a href="<?php echo esc_url( $this->get_tab_url( 'help' ) ); ?>" 
                   class="nav-tab <?php echo $active_tab === 'help' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Help', 'edd-customer-dashboard-pro' ); ?>
                </a>
            </nav>

            <div class="tab-content">
                <?php
                switch ( $active_tab ) {
                    case 'settings':
                        $this->render_settings_tab();
                        break;
                    case 'tools':
                        $this->render_tools_tab();
                        break;
                    case 'help':
                        $this->render_help_tab();
                        break;
                    case 'overview':
                    default:
                        $this->render_overview_tab();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render templates page
     */
    public function render_templates_page() {
        $template_loader = new EDD_Dashboard_Pro_Template_Loader();
        $templates = $template_loader->get_templates();
        $current_template = $template_loader->get_current_template();
        ?>
        <div class="wrap edd-dashboard-pro-templates">
            <h1><?php esc_html_e( 'Dashboard Templates', 'edd-customer-dashboard-pro' ); ?></h1>
            
            <div class="theme-browser">
                <div class="themes wp-clearfix">
                    <?php foreach ( $templates as $template_key => $template_data ) : ?>
                        <div class="theme <?php echo $current_template && $current_template['name'] === $template_data['name'] ? 'active' : ''; ?>">
                            <div class="theme-screenshot">
                                <?php if ( isset( $template_data['screenshot'] ) && $template_data['screenshot'] ) : ?>
                                    <img src="<?php echo esc_url( $template_data['screenshot'] ); ?>" alt="<?php echo esc_attr( $template_data['name'] ); ?>">
                                <?php else : ?>
                                    <div class="no-screenshot">
                                        <span class="dashicons dashicons-admin-appearance"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="theme-author"><?php echo esc_html( $template_data['author'] ?? esc_html__( 'Unknown', 'edd-customer-dashboard-pro' ) ); ?></div>
                            
                            <div class="theme-id-container">
                                <h2 class="theme-name">
                                    <?php echo esc_html( $template_data['name'] ); ?>
                                    <?php if ( $current_template && $current_template['name'] === $template_data['name'] ) : ?>
                                        <span class="current-label"><?php esc_html_e( 'Active', 'edd-customer-dashboard-pro' ); ?></span>
                                    <?php endif; ?>
                                </h2>
                                
                                <div class="theme-actions">
                                    <?php if ( $current_template && $current_template['name'] === $template_data['name'] ) : ?>
                                        <a class="button button-primary" href="<?php echo esc_url( $this->get_customize_url( $template_key ) ); ?>">
                                            <?php esc_html_e( 'Customize', 'edd-customer-dashboard-pro' ); ?>
                                        </a>
                                    <?php else : ?>
                                        <a class="button button-secondary activate" 
                                           href="<?php echo esc_url( $this->get_activate_template_url( $template_key ) ); ?>">
                                            <?php esc_html_e( 'Activate', 'edd-customer-dashboard-pro' ); ?>
                                        </a>
                                    <?php endif; ?>
                                    <a class="button" href="<?php echo esc_url( $this->get_preview_url( $template_key ) ); ?>" target="_blank">
                                        <?php esc_html_e( 'Preview', 'edd-customer-dashboard-pro' ); ?>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="theme-version">
                                <?php 
                                printf( 
                                    /* translators: %s: Template version */
                                    esc_html__( 'Version %s', 'edd-customer-dashboard-pro' ), 
                                    esc_html( $template_data['version'] ) 
                                ); 
                                ?>
                            </div>
                            
                            <div class="theme-description">
                                <p><?php echo esc_html( $template_data['description'] ); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php if ( empty( $templates ) ) : ?>
                <div class="no-themes">
                    <h2><?php esc_html_e( 'No templates found', 'edd-customer-dashboard-pro' ); ?></h2>
                    <p><?php esc_html_e( 'There are no dashboard templates available. Please check your installation.', 'edd-customer-dashboard-pro' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render system status page
     */
    public function render_status_page() {
        $settings = new EDD_Dashboard_Pro_Admin_Settings();
        $health_status = $settings->get_health_status();
        $system_info = $settings->get_system_info();
        $dependencies = $settings->validate_dependencies();
        ?>
        <div class="wrap edd-dashboard-pro-status">
            <h1><?php esc_html_e( 'System Status', 'edd-customer-dashboard-pro' ); ?></h1>
            
            <div class="health-check-wrapper">
                <h2><?php esc_html_e( 'Health Status', 'edd-customer-dashboard-pro' ); ?></h2>
                
                <div class="health-check-status status-<?php echo esc_attr( $health_status['overall'] ); ?>">
                    <span class="dashicons dashicons-<?php echo $health_status['overall'] === 'good' ? 'yes-alt' : 'warning'; ?>"></span>
                    <span class="status-text">
                        <?php 
                        switch ( $health_status['overall'] ) {
                            case 'good':
                                esc_html_e( 'Good', 'edd-customer-dashboard-pro' );
                                break;
                            case 'recommended':
                                esc_html_e( 'Should be improved', 'edd-customer-dashboard-pro' );
                                break;
                            case 'critical':
                                esc_html_e( 'Critical issues', 'edd-customer-dashboard-pro' );
                                break;
                        }
                        ?>
                    </span>
                </div>
                
                <div class="health-check-details">
                    <?php foreach ( $health_status['checks'] as $check_id => $check ) : ?>
                        <div class="health-check-item status-<?php echo esc_attr( $check['status'] ); ?>">
                            <span class="dashicons dashicons-<?php echo $check['status'] === 'good' ? 'yes-alt' : 'warning'; ?>"></span>
                            <div class="check-details">
                                <strong><?php echo esc_html( $check['label'] ); ?></strong>
                                <p><?php echo esc_html( $check['description'] ); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="system-info-wrapper">
                <h2><?php esc_html_e( 'System Information', 'edd-customer-dashboard-pro' ); ?></h2>
                
                <table class="widefat system-info-table">
                    <tbody>
                        <tr>
                            <th colspan="2"><strong><?php esc_html_e( 'Plugin Information', 'edd-customer-dashboard-pro' ); ?></strong></th>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Version', 'edd-customer-dashboard-pro' ); ?></td>
                            <td><?php echo esc_html( $system_info['plugin']['version'] ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Plugin Path', 'edd-customer-dashboard-pro' ); ?></td>
                            <td><code><?php echo esc_html( $system_info['plugin']['path'] ); ?></code></td>
                        </tr>
                        
                        <tr>
                            <th colspan="2"><strong><?php esc_html_e( 'WordPress Environment', 'edd-customer-dashboard-pro' ); ?></strong></th>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'WordPress Version', 'edd-customer-dashboard-pro' ); ?></td>
                            <td><?php echo esc_html( $system_info['wordpress']['version'] ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Multisite', 'edd-customer-dashboard-pro' ); ?></td>
                            <td><?php echo $system_info['wordpress']['multisite'] ? esc_html__( 'Yes', 'edd-customer-dashboard-pro' ) : esc_html__( 'No', 'edd-customer-dashboard-pro' ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Memory Limit', 'edd-customer-dashboard-pro' ); ?></td>
                            <td><?php echo esc_html( $system_info['wordpress']['memory_limit'] ); ?></td>
                        </tr>
                        
                        <tr>
                            <th colspan="2"><strong><?php esc_html_e( 'Server Environment', 'edd-customer-dashboard-pro' ); ?></strong></th>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'PHP Version', 'edd-customer-dashboard-pro' ); ?></td>
                            <td><?php echo esc_html( $system_info['server']['php_version'] ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Server Software', 'edd-customer-dashboard-pro' ); ?></td>
                            <td><?php echo esc_html( $system_info['server']['server_software'] ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Max Execution Time', 'edd-customer-dashboard-pro' ); ?></td>
                            <td><?php echo esc_html( $system_info['server']['max_execution_time'] ); ?> seconds</td>
                        </tr>
                        
                        <tr>
                            <th colspan="2"><strong><?php esc_html_e( 'Active Theme', 'edd-customer-dashboard-pro' ); ?></strong></th>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Theme Name', 'edd-customer-dashboard-pro' ); ?></td>
                            <td><?php echo esc_html( $system_info['theme']['name'] ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Theme Version', 'edd-customer-dashboard-pro' ); ?></td>
                            <td><?php echo esc_html( $system_info['theme']['version'] ); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="system-info-actions">
                    <button type="button" class="button" id="copy-system-info">
                        <?php esc_html_e( 'Copy System Info', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                    <textarea id="system-info-textarea" style="display: none;"><?php echo esc_textarea( $this->format_system_info_for_copy( $system_info ) ); ?></textarea>
                </div>
            </div>
            
            <div class="dependencies-wrapper">
                <h2><?php esc_html_e( 'Dependencies', 'edd-customer-dashboard-pro' ); ?></h2>
                
                <table class="widefat dependencies-table">
                    <tbody>
                        <?php foreach ( $dependencies as $dep_key => $dependency ) : ?>
                            <tr class="dependency-<?php echo esc_attr( $dep_key ); ?> status-<?php echo $dependency['active'] ? 'active' : 'inactive'; ?>">
                                <td class="dependency-status">
                                    <span class="dashicons dashicons-<?php echo $dependency['active'] ? 'yes-alt' : 'warning'; ?>"></span>
                                </td>
                                <td class="dependency-name">
                                    <strong><?php echo esc_html( $dependency['name'] ); ?></strong>
                                    <?php if ( $dependency['required'] ) : ?>
                                        <span class="required-label"><?php esc_html_e( '(Required)', 'edd-customer-dashboard-pro' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="dependency-message">
                                    <?php echo esc_html( $dependency['message'] ); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <script>
        document.getElementById('copy-system-info').addEventListener('click', function() {
            var textarea = document.getElementById('system-info-textarea');
            textarea.style.display = 'block';
            textarea.select();
            document.execCommand('copy');
            textarea.style.display = 'none';
            
            this.textContent = '<?php esc_html_e( 'Copied!', 'edd-customer-dashboard-pro' ); ?>';
            setTimeout(() => {
                this.textContent = '<?php esc_html_e( 'Copy System Info', 'edd-customer-dashboard-pro' ); ?>';
            }, 2000);
        });
        </script>
        <?php
    }

    /**
     * Render overview tab
     */
    private function render_overview_tab() {
        $stats = $this->get_dashboard_stats();
        ?>
        <div class="overview-tab">
            <div class="dashboard-widgets-wrap">
                <div class="dashboard-widgets metabox-holder">
                    <div class="postbox-container-1">
                        <div class="meta-box-sortables">
                            <!-- Quick Stats Widget -->
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="hndle"><?php esc_html_e( 'Quick Stats', 'edd-customer-dashboard-pro' ); ?></h2>
                                </div>
                                <div class="inside">
                                    <div class="main">
                                        <ul class="edd-dashboard-stats">
                                            <li>
                                                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-customers' ) ); ?>">
                                                    <strong><?php echo esc_html( number_format_i18n( $stats['total_customers'] ) ); ?></strong>
                                                    <?php esc_html_e( 'Total Customers', 'edd-customer-dashboard-pro' ); ?>
                                                </a>
                                            </li>
                                            <li>
                                                <strong><?php echo esc_html( number_format_i18n( $stats['customers_with_dashboards'] ) ); ?></strong>
                                                <?php esc_html_e( 'Using Dashboard', 'edd-customer-dashboard-pro' ); ?>
                                            </li>
                                            <li>
                                                <strong><?php echo esc_html( number_format_i18n( $stats['total_downloads_today'] ) ); ?></strong>
                                                <?php esc_html_e( 'Downloads Today', 'edd-customer-dashboard-pro' ); ?>
                                            </li>
                                            <li>
                                                <strong><?php echo esc_html( number_format_i18n( $stats['active_wishlists'] ) ); ?></strong>
                                                <?php esc_html_e( 'Active Wishlists', 'edd-customer-dashboard-pro' ); ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Quick Actions Widget -->
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="hndle"><?php esc_html_e( 'Quick Actions', 'edd-customer-dashboard-pro' ); ?></h2>
                                </div>
                                <div class="inside">
                                    <div class="main">
                                        <p>
                                            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=edd_dashboard_pro' ) ); ?>" class="button button-primary">
                                                <?php esc_html_e( 'Configure Settings', 'edd-customer-dashboard-pro' ); ?>
                                            </a>
                                        </p>
                                        <p>
                                            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-dashboard-pro-templates' ) ); ?>" class="button">
                                                <?php esc_html_e( 'Manage Templates', 'edd-customer-dashboard-pro' ); ?>
                                            </a>
                                        </p>
                                        <p>
                                            <a href="<?php echo esc_url( $this->get_dashboard_preview_url() ); ?>" class="button" target="_blank">
                                                <?php esc_html_e( 'Preview Dashboard', 'edd-customer-dashboard-pro' ); ?>
                                            </a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="postbox-container-2">
                        <div class="meta-box-sortables">
                            <!-- Getting Started Widget -->
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="hndle"><?php esc_html_e( 'Getting Started', 'edd-customer-dashboard-pro' ); ?></h2>
                                </div>
                                <div class="inside">
                                    <div class="main">
                                        <h4><?php esc_html_e( 'Setup Steps', 'edd-customer-dashboard-pro' ); ?></h4>
                                        <ol class="setup-steps">
                                            <li class="<?php echo $this->is_step_completed( 'page_created' ) ? 'completed' : 'pending'; ?>">
                                                <span class="step-status"></span>
                                                <?php esc_html_e( 'Create a dashboard page', 'edd-customer-dashboard-pro' ); ?>
                                                <?php if ( ! $this->is_step_completed( 'page_created' ) ) : ?>
                                                    <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>" class="button-link">
                                                        <?php esc_html_e( 'Create Page', 'edd-customer-dashboard-pro' ); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </li>
                                            <li class="<?php echo $this->is_step_completed( 'shortcode_added' ) ? 'completed' : 'pending'; ?>">
                                                <span class="step-status"></span>
                                                <?php esc_html_e( 'Add the dashboard shortcode', 'edd-customer-dashboard-pro' ); ?>
                                                <code>[edd_customer_dashboard_pro]</code>
                                            </li>
                                            <li class="<?php echo $this->is_step_completed( 'settings_configured' ) ? 'completed' : 'pending'; ?>">
                                                <span class="step-status"></span>
                                                <?php esc_html_e( 'Configure plugin settings', 'edd-customer-dashboard-pro' ); ?>
                                                <?php if ( ! $this->is_step_completed( 'settings_configured' ) ) : ?>
                                                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=edd_dashboard_pro' ) ); ?>" class="button-link">
                                                        <?php esc_html_e( 'Configure', 'edd-customer-dashboard-pro' ); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </li>
                                            <li class="<?php echo $this->is_step_completed( 'template_selected' ) ? 'completed' : 'pending'; ?>">
                                                <span class="step-status"></span>
                                                <?php esc_html_e( 'Choose a dashboard template', 'edd-customer-dashboard-pro' ); ?>
                                                <?php if ( ! $this->is_step_completed( 'template_selected' ) ) : ?>
                                                    <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-dashboard-pro-templates' ) ); ?>" class="button-link">
                                                        <?php esc_html_e( 'Choose Template', 'edd-customer-dashboard-pro' ); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </li>
                                        </ol>
                                        
                                        <?php if ( $this->all_steps_completed() ) : ?>
                                            <div class="setup-complete">
                                                <span class="dashicons dashicons-yes-alt"></span>
                                                <strong><?php esc_html_e( 'Setup Complete!', 'edd-customer-dashboard-pro' ); ?></strong>
                                                <p><?php esc_html_e( 'Your customer dashboard is ready to use.', 'edd-customer-dashboard-pro' ); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recent Activity Widget -->
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="hndle"><?php esc_html_e( 'Recent Activity', 'edd-customer-dashboard-pro' ); ?></h2>
                                </div>
                                <div class="inside">
                                    <div class="main">
                                        <?php $this->render_recent_activity(); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render settings tab
     */
    private function render_settings_tab() {
        ?>
        <div class="settings-tab">
            <p><?php esc_html_e( 'Plugin settings are managed through the EDD Settings page.', 'edd-customer-dashboard-pro' ); ?></p>
            
            <p>
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=edd_dashboard_pro' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Go to Settings', 'edd-customer-dashboard-pro' ); ?>
                </a>
            </p>
            
            <h3><?php esc_html_e( 'Quick Settings Overview', 'edd-customer-dashboard-pro' ); ?></h3>
            
            <table class="widefat settings-overview-table">
                <tbody>
                    <?php $this->render_settings_overview(); ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * Render tools tab
     */
    private function render_tools_tab() {
        ?>
        <div class="tools-tab">
            <div class="tool-box">
                <h3><?php esc_html_e( 'Cache Management', 'edd-customer-dashboard-pro' ); ?></h3>
                <p><?php esc_html_e( 'Clear all cached dashboard data to force refresh.', 'edd-customer-dashboard-pro' ); ?></p>
                <form method="post" action="">
                    <?php wp_nonce_field( 'edd_dashboard_pro_clear_cache', '_wpnonce' ); ?>
                    <input type="hidden" name="edd_dashboard_pro_action" value="clear_cache">
                    <button type="submit" class="button" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to clear all caches?', 'edd-customer-dashboard-pro' ); ?>')">
                        <?php esc_html_e( 'Clear Cache', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                </form>
            </div>
            
            <div class="tool-box">
                <h3><?php esc_html_e( 'Reset Settings', 'edd-customer-dashboard-pro' ); ?></h3>
                <p><?php esc_html_e( 'Reset all plugin settings to their default values.', 'edd-customer-dashboard-pro' ); ?></p>
                <form method="post" action="">
                    <?php wp_nonce_field( 'edd_dashboard_pro_reset_settings', '_wpnonce' ); ?>
                    <input type="hidden" name="edd_dashboard_pro_action" value="reset_settings">
                    <button type="submit" class="button button-secondary" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset all settings? This cannot be undone.', 'edd-customer-dashboard-pro' ); ?>')">
                        <?php esc_html_e( 'Reset Settings', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                </form>
            </div>
            
            <div class="tool-box">
                <h3><?php esc_html_e( 'Export/Import Settings', 'edd-customer-dashboard-pro' ); ?></h3>
                <p><?php esc_html_e( 'Export your current settings or import settings from another installation.', 'edd-customer-dashboard-pro' ); ?></p>
                
                <h4><?php esc_html_e( 'Export Settings', 'edd-customer-dashboard-pro' ); ?></h4>
                <form method="post" action="">
                    <?php wp_nonce_field( 'edd_dashboard_pro_export_settings', '_wpnonce' ); ?>
                    <input type="hidden" name="edd_dashboard_pro_action" value="export_settings">
                    <button type="submit" class="button">
                        <?php esc_html_e( 'Export Settings', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                </form>
                
                <h4><?php esc_html_e( 'Import Settings', 'edd-customer-dashboard-pro' ); ?></h4>
                <form method="post" action="" enctype="multipart/form-data">
                    <?php wp_nonce_field( 'edd_dashboard_pro_import_settings', '_wpnonce' ); ?>
                    <input type="hidden" name="edd_dashboard_pro_action" value="import_settings">
                    <input type="file" name="settings_file" accept=".json" required>
                    <button type="submit" class="button">
                        <?php esc_html_e( 'Import Settings', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                </form>
            </div>
            
            <div class="tool-box">
                <h3><?php esc_html_e( 'Database Cleanup', 'edd-customer-dashboard-pro' ); ?></h3>
                <p><?php esc_html_e( 'Clean up expired data and optimize database tables.', 'edd-customer-dashboard-pro' ); ?></p>
                <form method="post" action="">
                    <?php wp_nonce_field( 'edd_dashboard_pro_cleanup_database', '_wpnonce' ); ?>
                    <input type="hidden" name="edd_dashboard_pro_action" value="cleanup_database">
                    <button type="submit" class="button">
                        <?php esc_html_e( 'Cleanup Database', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render help tab
     */
    private function render_help_tab() {
        ?>
        <div class="help-tab">
            <div class="help-section">
                <h3><?php esc_html_e( 'Getting Help', 'edd-customer-dashboard-pro' ); ?></h3>
                <p><?php esc_html_e( 'Need assistance with Customer Dashboard Pro? Here are some resources to help you:', 'edd-customer-dashboard-pro' ); ?></p>
                
                <ul class="help-links">
                    <li>
                        <a href="#" target="_blank" class="dashicons-before dashicons-book">
                            <?php esc_html_e( 'Documentation', 'edd-customer-dashboard-pro' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" target="_blank" class="dashicons-before dashicons-sos">
                            <?php esc_html_e( 'Support Forum', 'edd-customer-dashboard-pro' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" target="_blank" class="dashicons-before dashicons-video-alt3">
                            <?php esc_html_e( 'Video Tutorials', 'edd-customer-dashboard-pro' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" target="_blank" class="dashicons-before dashicons-email">
                            <?php esc_html_e( 'Contact Support', 'edd-customer-dashboard-pro' ); ?>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="help-section">
                <h3><?php esc_html_e( 'Shortcodes', 'edd-customer-dashboard-pro' ); ?></h3>
                <p><?php esc_html_e( 'Use these shortcodes to display dashboard components:', 'edd-customer-dashboard-pro' ); ?></p>
                
                <table class="widefat shortcodes-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Shortcode', 'edd-customer-dashboard-pro' ); ?></th>
                            <th><?php esc_html_e( 'Description', 'edd-customer-dashboard-pro' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>[edd_customer_dashboard_pro]</code></td>
                            <td><?php esc_html_e( 'Display the complete customer dashboard', 'edd-customer-dashboard-pro' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>[edd_customer_stats]</code></td>
                            <td><?php esc_html_e( 'Display customer statistics only', 'edd-customer-dashboard-pro' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>[edd_customer_purchases]</code></td>
                            <td><?php esc_html_e( 'Display purchase history only', 'edd-customer-dashboard-pro' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>[edd_customer_downloads]</code></td>
                            <td><?php esc_html_e( 'Display download history only', 'edd-customer-dashboard-pro' ); ?></td>
                        </tr>
                        <tr>
                            <td><code>[edd_customer_wishlist]</code></td>
                            <td><?php esc_html_e( 'Display customer wishlist only', 'edd-customer-dashboard-pro' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="help-section">
                <h3><?php esc_html_e( 'Troubleshooting', 'edd-customer-dashboard-pro' ); ?></h3>
                <div class="troubleshooting-steps">
                    <details>
                        <summary><?php esc_html_e( 'Dashboard not displaying correctly', 'edd-customer-dashboard-pro' ); ?></summary>
                        <ol>
                            <li><?php esc_html_e( 'Check if the shortcode is added to the page correctly', 'edd-customer-dashboard-pro' ); ?></li>
                            <li><?php esc_html_e( 'Verify that the user is logged in and has made purchases', 'edd-customer-dashboard-pro' ); ?></li>
                            <li><?php esc_html_e( 'Clear the plugin cache from the Tools tab', 'edd-customer-dashboard-pro' ); ?></li>
                            <li><?php esc_html_e( 'Check for theme or plugin conflicts', 'edd-customer-dashboard-pro' ); ?></li>
                        </ol>
                    </details>
                    
                    <details>
                        <summary><?php esc_html_e( 'Download links not working', 'edd-customer-dashboard-pro' ); ?></summary>
                        <ol>
                            <li><?php esc_html_e( 'Ensure file download permissions are correctly set in EDD', 'edd-customer-dashboard-pro' ); ?></li>
                            <li><?php esc_html_e( 'Check if download limits are configured properly', 'edd-customer-dashboard-pro' ); ?></li>
                            <li><?php esc_html_e( 'Verify that the payment status is complete', 'edd-customer-dashboard-pro' ); ?></li>
                        </ol>
                    </details>
                    
                    <details>
                        <summary><?php esc_html_e( 'License management not showing', 'edd-customer-dashboard-pro' ); ?></summary>
                        <ol>
                            <li><?php esc_html_e( 'Install and activate EDD Software Licensing extension', 'edd-customer-dashboard-pro' ); ?></li>
                            <li><?php esc_html_e( 'Configure products to use licensing', 'edd-customer-dashboard-pro' ); ?></li>
                            <li><?php esc_html_e( 'Enable license key display in plugin settings', 'edd-customer-dashboard-pro' ); ?></li>
                        </ol>
                    </details>
                </div>
            </div>
            
            <div class="help-section">
                <h3><?php esc_html_e( 'System Requirements', 'edd-customer-dashboard-pro' ); ?></h3>
                <ul class="system-requirements">
                    <li><?php esc_html_e( 'WordPress 5.0 or higher', 'edd-customer-dashboard-pro' ); ?></li>
                    <li><?php esc_html_e( 'PHP 7.4 or higher', 'edd-customer-dashboard-pro' ); ?></li>
                    <li><?php esc_html_e( 'Easy Digital Downloads 3.0 or higher', 'edd-customer-dashboard-pro' ); ?></li>
                    <li><?php esc_html_e( 'MySQL 5.6 or higher', 'edd-customer-dashboard-pro' ); ?></li>
                </ul>
            </div>
        </div>
        <?php
    }

    /**
     * Handle admin actions
     */
    public function handle_admin_actions() {
        if ( ! current_user_can( 'manage_shop_settings' ) ) {
            return;
        }

        if ( ! isset( $_POST['edd_dashboard_pro_action'] ) ) {
            return;
        }

        $action = sanitize_text_field( wp_unslash( $_POST['edd_dashboard_pro_action'] ) );

        switch ( $action ) {
            case 'clear_cache':
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'edd_dashboard_pro_clear_cache' ) ) {
                    $this->handle_clear_cache();
                }
                break;

            case 'reset_settings':
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'edd_dashboard_pro_reset_settings' ) ) {
                    $this->handle_reset_settings();
                }
                break;

            case 'export_settings':
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'edd_dashboard_pro_export_settings' ) ) {
                    $this->handle_export_settings();
                }
                break;

            case 'import_settings':
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'edd_dashboard_pro_import_settings' ) ) {
                    $this->handle_import_settings();
                }
                break;

            case 'cleanup_database':
                if ( wp_verify_nonce( $_POST['_wpnonce'], 'edd_dashboard_pro_cleanup_database' ) ) {
                    $this->handle_cleanup_database();
                }
                break;

            case 'activate_template':
                if ( wp_verify_nonce( $_GET['_wpnonce'], 'edd_dashboard_pro_activate_template' ) ) {
                    $this->handle_activate_template();
                }
                break;
        }
    }

    /**
     * Handle clear cache action
     */
    private function handle_clear_cache() {
        $settings = new EDD_Dashboard_Pro_Admin_Settings();
        $settings->clear_caches();
        
        $this->add_admin_notice( 
            esc_html__( 'Cache cleared successfully!', 'edd-customer-dashboard-pro' ), 
            'success' 
        );
    }

    /**
     * Handle reset settings action
     */
    private function handle_reset_settings() {
        $settings = new EDD_Dashboard_Pro_Admin_Settings();
        $settings->reset_settings();
        
        $this->add_admin_notice( 
            esc_html__( 'Settings have been reset to defaults.', 'edd-customer-dashboard-pro' ), 
            'success' 
        );
    }

    /**
     * Handle export settings action
     */
    private function handle_export_settings() {
        $settings = new EDD_Dashboard_Pro_Admin_Settings();
        $export_data = $settings->export_settings();
        
        $filename = 'edd-dashboard-pro-settings-' . date( 'Y-m-d' ) . '.json';
        
        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Expires: 0' );
        
        echo wp_json_encode( $export_data );
        exit;
    }

    /**
     * Handle import settings action
     */
    private function handle_import_settings() {
        if ( ! isset( $_FILES['settings_file'] ) || $_FILES['settings_file']['error'] !== UPLOAD_ERR_OK ) {
            $this->add_admin_notice( 
                esc_html__( 'Please select a valid settings file.', 'edd-customer-dashboard-pro' ), 
                'error' 
            );
            return;
        }

        $file_content = file_get_contents( $_FILES['settings_file']['tmp_name'] );
        $import_data = json_decode( $file_content, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            $this->add_admin_notice( 
                esc_html__( 'Invalid JSON file format.', 'edd-customer-dashboard-pro' ), 
                'error' 
            );
            return;
        }

        $settings = new EDD_Dashboard_Pro_Admin_Settings();
        $result = $settings->import_settings( $import_data );

        if ( is_wp_error( $result ) ) {
            $this->add_admin_notice( $result->get_error_message(), 'error' );
        } else {
            $this->add_admin_notice( 
                esc_html__( 'Settings imported successfully!', 'edd-customer-dashboard-pro' ), 
                'success' 
            );
        }
    }

    /**
     * Handle cleanup database action
     */
    private function handle_cleanup_database() {
        // Run cleanup tasks
        edd_dashboard_pro_cleanup_expired_transients();
        edd_dashboard_pro_cleanup_old_logs();
        edd_dashboard_pro_optimize_database();
        
        $this->add_admin_notice( 
            esc_html__( 'Database cleanup completed successfully!', 'edd-customer-dashboard-pro' ), 
            'success' 
        );
    }

    /**
     * Handle activate template action
     */
    private function handle_activate_template() {
        $template = sanitize_text_field( $_GET['template'] ?? '' );
        
        if ( empty( $template ) ) {
            $this->add_admin_notice( 
                esc_html__( 'Invalid template specified.', 'edd-customer-dashboard-pro' ), 
                'error' 
            );
            return;
        }

        $template_loader = new EDD_Dashboard_Pro_Template_Loader();
        $result = $template_loader->set_current_template( $template );

        if ( $result ) {
            $this->add_admin_notice( 
                esc_html__( 'Template activated successfully!', 'edd-customer-dashboard-pro' ), 
                'success' 
            );
        } else {
            $this->add_admin_notice( 
                esc_html__( 'Failed to activate template.', 'edd-customer-dashboard-pro' ), 
                'error' 
            );
        }
    }

    /**
     * Add plugin action links
     *
     * @param array $links Existing links
     * @return array
     */
    public function add_plugin_action_links( $links ) {
        $action_links = array(
            'settings' => '<a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=edd_dashboard_pro' ) ) . '">' . esc_html__( 'Settings', 'edd-customer-dashboard-pro' ) . '</a>',
            'dashboard' => '<a href="' . esc_url( admin_url( 'edit.php?post_type=download&page=edd-dashboard-pro' ) ) . '">' . esc_html__( 'Dashboard', 'edd-customer-dashboard-pro' ) . '</a>',
        );

        return array_merge( $action_links, $links );
    }

    /**
     * Add plugin row meta
     *
     * @param array $links Existing links
     * @param string $file Plugin file
     * @return array
     */
    public function add_plugin_row_meta( $links, $file ) {
        if ( EDD_DASHBOARD_PRO_PLUGIN_BASENAME === $file ) {
            $row_meta = array(
                'docs' => '<a href="#" target="_blank">' . esc_html__( 'Documentation', 'edd-customer-dashboard-pro' ) . '</a>',
                'support' => '<a href="#" target="_blank">' . esc_html__( 'Support', 'edd-customer-dashboard-pro' ) . '</a>',
            );

            return array_merge( $links, $row_meta );
        }

        return $links;
    }

    /**
     * Show admin notices
     */
    public function show_admin_notices() {
        $notices = get_option( 'edd_dashboard_pro_admin_notices', array() );

        foreach ( $notices as $notice_id => $notice ) {
            $class = 'notice notice-' . esc_attr( $notice['type'] );
            if ( $notice['dismissible'] ?? false ) {
                $class .= ' is-dismissible';
            }
            ?>
            <div class="<?php echo esc_attr( $class ); ?>" data-notice-id="<?php echo esc_attr( $notice_id ); ?>">
                <p><?php echo wp_kses_post( $notice['message'] ); ?></p>
            </div>
            <?php
        }

        // Clear notices after displaying
        delete_option( 'edd_dashboard_pro_admin_notices' );
    }

    /**
     * Handle notice dismissal
     */
    public function dismiss_notice() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'edd_dashboard_pro_dismiss_notice' ) ) {
            wp_die( 'Security check failed' );
        }

        $notice_id = sanitize_text_field( $_POST['notice_id'] );
        $dismissed_notices = get_option( 'edd_dashboard_pro_dismissed_notices', array() );
        $dismissed_notices[] = $notice_id;
        update_option( 'edd_dashboard_pro_dismissed_notices', $dismissed_notices );

        wp_send_json_success();
    }

    /**
     * Get active tab
     *
     * @return string
     */
    private function get_active_tab() {
        return sanitize_text_field( $_GET['tab'] ?? 'overview' );
    }

    /**
     * Get tab URL
     *
     * @param string $tab Tab name
     * @return string
     */
    private function get_tab_url( $tab ) {
        return add_query_arg( 
            array( 'tab' => $tab ), 
            admin_url( 'edit.php?post_type=download&page=' . $this->menu_slug ) 
        );
    }

    /**
     * Get customize URL
     *
     * @param string $template Template key
     * @return string
     */
    private function get_customize_url( $template ) {
        return add_query_arg( 
            array( 'template' => $template ), 
            admin_url( 'edit.php?post_type=download&page=edd-settings&tab=extensions&section=edd_dashboard_pro' ) 
        );
    }

    /**
     * Get activate template URL
     *
     * @param string $template Template key
     * @return string
     */
    private function get_activate_template_url( $template ) {
        return wp_nonce_url( 
            add_query_arg( 
                array( 
                    'edd_dashboard_pro_action' => 'activate_template',
                    'template' => $template 
                ), 
                admin_url( 'edit.php?post_type=download&page=edd-dashboard-pro-templates' ) 
            ), 
            'edd_dashboard_pro_activate_template' 
        );
    }

    /**
     * Get preview URL
     *
     * @param string $template Template key
     * @return string
     */
    private function get_preview_url( $template ) {
        $dashboard_page = EDD_Dashboard_Pro()->get_option( 'dashboard_page' );
        
        if ( $dashboard_page ) {
            return add_query_arg( 'preview_template', $template, get_permalink( $dashboard_page ) );
        }
        
        return home_url( '?edd-dashboard=1&preview_template=' . $template );
    }

    /**
     * Get dashboard preview URL
     *
     * @return string
     */
    private function get_dashboard_preview_url() {
        $dashboard_page = EDD_Dashboard_Pro()->get_option( 'dashboard_page' );
        
        if ( $dashboard_page ) {
            return get_permalink( $dashboard_page );
        }
        
        return home_url( '?edd-dashboard=1' );
    }

    /**
     * Get dashboard statistics
     *
     * @return array
     */
    private function get_dashboard_stats() {
        global $wpdb;

        $stats = array();

        // Total customers
        $stats['total_customers'] = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}edd_customers" );

        // Customers using dashboard (have accessed it)
        $stats['customers_with_dashboards'] = $wpdb->get_var(
            "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} 
             WHERE meta_key = '_edd_dashboard_pro_last_login'"
        );

        // Downloads today
        $stats['total_downloads_today'] = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}edd_logs 
             WHERE type = 'file_download' 
             AND date >= %s",
            date( 'Y-m-d 00:00:00' )
        ) );

        // Active wishlists
        $stats['active_wishlists'] = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE meta_key = '_edd_dashboard_pro_wishlist' 
             AND meta_value != '' 
             AND meta_value != 'a:0:{}'"
        );

        return $stats;
    }

    /**
     * Check if setup step is completed
     *
     * @param string $step Step name
     * @return bool
     */
    private function is_step_completed( $step ) {
        switch ( $step ) {
            case 'page_created':
                return ! empty( EDD_Dashboard_Pro()->get_option( 'dashboard_page' ) );
            
            case 'shortcode_added':
                $dashboard_page = EDD_Dashboard_Pro()->get_option( 'dashboard_page' );
                if ( $dashboard_page ) {
                    $page = get_post( $dashboard_page );
                    return $page && has_shortcode( $page->post_content, 'edd_customer_dashboard_pro' );
                }
                return false;
            
            case 'settings_configured':
                return ! empty( EDD_Dashboard_Pro()->get_option( 'template' ) );
            
            case 'template_selected':
                $template = EDD_Dashboard_Pro()->get_option( 'template', 'default' );
                return $template !== 'default' || $template === 'default';
            
            default:
                return false;
        }
    }

    /**
     * Check if all setup steps are completed
     *
     * @return bool
     */
    private function all_steps_completed() {
        $steps = array( 'page_created', 'shortcode_added', 'settings_configured', 'template_selected' );
        
        foreach ( $steps as $step ) {
            if ( ! $this->is_step_completed( $step ) ) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Render recent activity
     */
    private function render_recent_activity() {
        global $wpdb;

        // Get recent downloads
        $recent_downloads = $wpdb->get_results( $wpdb->prepare(
            "SELECT l.*, p.post_title as product_name, u.display_name as user_name
             FROM {$wpdb->prefix}edd_logs l
             LEFT JOIN {$wpdb->posts} p ON l.object_id = p.ID
             LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID
             WHERE l.type = 'file_download'
             ORDER BY l.date DESC
             LIMIT %d",
            10
        ) );

        if ( $recent_downloads ) {
            echo '<h4>' . esc_html__( 'Recent Downloads', 'edd-customer-dashboard-pro' ) . '</h4>';
            echo '<ul class="recent-activity-list">';
            
            foreach ( $recent_downloads as $download ) {
                printf(
                    '<li><strong>%s</strong> downloaded <em>%s</em> <span class="activity-time">%s</span></li>',
                    esc_html( $download->user_name ?: __( 'Guest', 'edd-customer-dashboard-pro' ) ),
                    esc_html( $download->product_name ?: __( 'Unknown Product', 'edd-customer-dashboard-pro' ) ),
                    esc_html( human_time_diff( strtotime( $download->date ), current_time( 'timestamp' ) ) . ' ago' )
                );
            }
            
            echo '</ul>';
        } else {
            echo '<p>' . esc_html__( 'No recent activity.', 'edd-customer-dashboard-pro' ) . '</p>';
        }
    }

    /**
     * Render settings overview
     */
    private function render_settings_overview() {
        $settings = new EDD_Dashboard_Pro_Admin_Settings();
        $current_settings = $settings->get_all_settings();

        $overview_settings = array(
            'dashboard_page' => __( 'Dashboard Page', 'edd-customer-dashboard-pro' ),
            'template' => __( 'Active Template', 'edd-customer-dashboard-pro' ),
            'enable_wishlist' => __( 'Wishlist Enabled', 'edd-customer-dashboard-pro' ),
            'enable_analytics' => __( 'Analytics Enabled', 'edd-customer-dashboard-pro' ),
            'require_login_redirect' => __( 'Login Redirect', 'edd-customer-dashboard-pro' ),
        );

        foreach ( $overview_settings as $setting_key => $setting_label ) {
            $value = $current_settings[ $setting_key ] ?? '';
            
            if ( $setting_key === 'dashboard_page' && $value ) {
                $page = get_post( $value );
                $value = $page ? $page->post_title : __( 'Page not found', 'edd-customer-dashboard-pro' );
            } elseif ( is_bool( $value ) || in_array( $value, array( '1', '0', 'true', 'false' ) ) ) {
                $value = $value ? __( 'Yes', 'edd-customer-dashboard-pro' ) : __( 'No', 'edd-customer-dashboard-pro' );
            }
            
            echo '<tr>';
            echo '<td><strong>' . esc_html( $setting_label ) . '</strong></td>';
            echo '<td>' . esc_html( $value ?: __( 'Not set', 'edd-customer-dashboard-pro' ) ) . '</td>';
            echo '</tr>';
        }
    }

    /**
     * Format system info for copying
     *
     * @param array $system_info System information
     * @return string
     */
    private function format_system_info_for_copy( $system_info ) {
        $output = "=== EDD Customer Dashboard Pro System Information ===\n\n";
        
        // Plugin Information
        $output .= "-- Plugin Information --\n";
        $output .= "Version: " . $system_info['plugin']['version'] . "\n";
        $output .= "Plugin Path: " . $system_info['plugin']['path'] . "\n";
        $output .= "Plugin URL: " . $system_info['plugin']['url'] . "\n\n";
        
        // WordPress Environment
        $output .= "-- WordPress Environment --\n";
        $output .= "WordPress Version: " . $system_info['wordpress']['version'] . "\n";
        $output .= "Multisite: " . ( $system_info['wordpress']['multisite'] ? 'Yes' : 'No' ) . "\n";
        $output .= "Memory Limit: " . $system_info['wordpress']['memory_limit'] . "\n";
        $output .= "Debug Mode: " . ( $system_info['wordpress']['debug_mode'] ? 'Yes' : 'No' ) . "\n\n";
        
        // Server Environment
        $output .= "-- Server Environment --\n";
        $output .= "PHP Version: " . $system_info['server']['php_version'] . "\n";
        $output .= "Server Software: " . $system_info['server']['server_software'] . "\n";
        $output .= "Max Execution Time: " . $system_info['server']['max_execution_time'] . " seconds\n";
        $output .= "Max Input Vars: " . $system_info['server']['max_input_vars'] . "\n";
        $output .= "Post Max Size: " . $system_info['server']['post_max_size'] . "\n";
        $output .= "Upload Max Filesize: " . $system_info['server']['upload_max_filesize'] . "\n\n";
        
        // Active Theme
        $output .= "-- Active Theme --\n";
        $output .= "Theme Name: " . $system_info['theme']['name'] . "\n";
        $output .= "Theme Version: " . $system_info['theme']['version'] . "\n";
        $output .= "Template: " . $system_info['theme']['template'] . "\n\n";
        
        // Active Plugins
        $output .= "-- Active Plugins --\n";
        foreach ( $system_info['plugins'] as $plugin_file => $plugin_data ) {
            $output .= $plugin_data['name'] . " v" . $plugin_data['version'] . "\n";
        }
        
        return $output;
    }

    /**
     * Add admin notice
     *
     * @param string $message Notice message
     * @param string $type Notice type (success, error, warning, info)
     * @param bool $dismissible Whether notice is dismissible
     */
    private function add_admin_notice( $message, $type = 'info', $dismissible = true ) {
        $notices = get_option( 'edd_dashboard_pro_admin_notices', array() );
        
        $notice_id = 'notice_' . time() . '_' . wp_rand( 1000, 9999 );
        
        $notices[ $notice_id ] = array(
            'message' => $message,
            'type' => $type,
            'dismissible' => $dismissible,
            'timestamp' => current_time( 'mysql' )
        );
        
        update_option( 'edd_dashboard_pro_admin_notices', $notices );
    }

    /**
     * Get menu capability
     *
     * @return string
     */
    private function get_menu_capability() {
        return apply_filters( 'edd_dashboard_pro_menu_capability', 'manage_shop_settings' );
    }

    /**
     * Check if current user can access admin pages
     *
     * @return bool
     */
    private function can_access_admin_pages() {
        return current_user_can( $this->get_menu_capability() );
    }

    /**
     * Render admin page wrapper
     *
     * @param string $title Page title
     * @param callable $content_callback Content callback
     */
    private function render_admin_page_wrapper( $title, $content_callback ) {
        if ( ! $this->can_access_admin_pages() ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'edd-customer-dashboard-pro' ) );
        }
        
        echo '<div class="wrap edd-dashboard-pro-admin">';
        echo '<h1>' . esc_html( $title ) . '</h1>';
        
        if ( is_callable( $content_callback ) ) {
            call_user_func( $content_callback );
        }
        
        echo '</div>';
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     */
    public function enqueue_admin_assets( $hook ) {
        // Only load on our admin pages
        if ( strpos( $hook, 'edd-dashboard-pro' ) === false ) {
            return;
        }

        wp_enqueue_style(
            'edd-dashboard-pro-admin',
            EDD_DASHBOARD_PRO_PLUGIN_URL . 'admin/assets/css/admin-styles.css',
            array( 'wp-admin' ),
            EDD_DASHBOARD_PRO_VERSION
        );

        wp_enqueue_script(
            'edd-dashboard-pro-admin',
            EDD_DASHBOARD_PRO_PLUGIN_URL . 'admin/assets/js/admin-scripts.js',
            array( 'jquery', 'wp-util' ),
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
                    'confirmReset' => esc_html__( 'Are you sure you want to reset all settings? This cannot be undone.', 'edd-customer-dashboard-pro' ),
                    'confirmClearCache' => esc_html__( 'Are you sure you want to clear all caches?', 'edd-customer-dashboard-pro' ),
                    'confirmCleanup' => esc_html__( 'Are you sure you want to cleanup the database?', 'edd-customer-dashboard-pro' ),
                    'processing' => esc_html__( 'Processing...', 'edd-customer-dashboard-pro' ),
                    'error' => esc_html__( 'An error occurred. Please try again.', 'edd-customer-dashboard-pro' ),
                    'success' => esc_html__( 'Operation completed successfully!', 'edd-customer-dashboard-pro' ),
                    'copied' => esc_html__( 'Copied to clipboard!', 'edd-customer-dashboard-pro' )
                )
            )
        );
    }

    /**
     * Add contextual help
     *
     * @param string $contextual_help Current help content
     * @param string $screen_id Current screen ID
     * @param WP_Screen $screen Current screen object
     * @return string
     */
    public function add_contextual_help( $contextual_help, $screen_id, $screen ) {
        if ( strpos( $screen_id, 'edd-dashboard-pro' ) === false ) {
            return $contextual_help;
        }

        $screen->add_help_tab( array(
            'id' => 'edd-dashboard-pro-overview',
            'title' => esc_html__( 'Overview', 'edd-customer-dashboard-pro' ),
            'content' => '<p>' . esc_html__( 'The Customer Dashboard Pro provides a modern, feature-rich dashboard for your EDD customers to manage their purchases, downloads, licenses, and account details.', 'edd-customer-dashboard-pro' ) . '</p>'
        ) );

        $screen->add_help_tab( array(
            'id' => 'edd-dashboard-pro-setup',
            'title' => esc_html__( 'Setup', 'edd-customer-dashboard-pro' ),
            'content' => '<p>' . esc_html__( 'To get started, create a new page and add the [edd_customer_dashboard_pro] shortcode. Then configure the plugin settings and choose a template.', 'edd-customer-dashboard-pro' ) . '</p>'
        ) );

        $screen->add_help_tab( array(
            'id' => 'edd-dashboard-pro-shortcodes',
            'title' => esc_html__( 'Shortcodes', 'edd-customer-dashboard-pro' ),
            'content' => '<p>' . esc_html__( 'Available shortcodes:', 'edd-customer-dashboard-pro' ) . '</p>' .
                         '<ul>' .
                         '<li><code>[edd_customer_dashboard_pro]</code> - ' . esc_html__( 'Complete dashboard', 'edd-customer-dashboard-pro' ) . '</li>' .
                         '<li><code>[edd_customer_stats]</code> - ' . esc_html__( 'Customer statistics only', 'edd-customer-dashboard-pro' ) . '</li>' .
                         '<li><code>[edd_customer_purchases]</code> - ' . esc_html__( 'Purchase history only', 'edd-customer-dashboard-pro' ) . '</li>' .
                         '</ul>'
        ) );

        $screen->set_help_sidebar(
            '<p><strong>' . esc_html__( 'For more information:', 'edd-customer-dashboard-pro' ) . '</strong></p>' .
            '<p><a href="#" target="_blank">' . esc_html__( 'Documentation', 'edd-customer-dashboard-pro' ) . '</a></p>' .
            '<p><a href="#" target="_blank">' . esc_html__( 'Support Forum', 'edd-customer-dashboard-pro' ) . '</a></p>'
        );

        return $contextual_help;
    }

    /**
     * Register admin hooks
     */
    public function register_admin_hooks() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
        add_filter( 'contextual_help', array( $this, 'add_contextual_help' ), 10, 3 );
    }

    /**
     * Get dashboard usage statistics
     *
     * @return array
     */
    private function get_dashboard_usage_stats() {
        global $wpdb;

        $stats = array();

        // Most popular template
        $template_usage = $wpdb->get_results(
            "SELECT meta_value as template, COUNT(*) as count 
             FROM {$wpdb->options} 
             WHERE option_name = 'edd_dashboard_pro_settings' 
             GROUP BY meta_value 
             ORDER BY count DESC 
             LIMIT 1"
        );

        $stats['popular_template'] = $template_usage ? $template_usage[0]->template : 'default';

        // Average wishlist size
        $avg_wishlist_size = $wpdb->get_var(
            "SELECT AVG(LENGTH(meta_value) - LENGTH(REPLACE(meta_value, 'i:', ''))) / LENGTH('i:') 
             FROM {$wpdb->usermeta} 
             WHERE meta_key = '_edd_dashboard_pro_wishlist' 
             AND meta_value != '' 
             AND meta_value != 'a:0:{}'"
        );

        $stats['avg_wishlist_size'] = round( $avg_wishlist_size ?: 0, 1 );

        // Most downloaded products via dashboard
        $popular_downloads = $wpdb->get_results( $wpdb->prepare(
            "SELECT p.post_title, COUNT(*) as download_count 
             FROM {$wpdb->prefix}edd_logs l
             LEFT JOIN {$wpdb->posts} p ON l.object_id = p.ID
             WHERE l.type = 'file_download' 
             AND l.date >= %s
             GROUP BY l.object_id 
             ORDER BY download_count DESC 
             LIMIT 5",
            date( 'Y-m-d', strtotime( '-30 days' ) )
        ) );

        $stats['popular_downloads'] = $popular_downloads;

        return $stats;
    }

    /**
     * Render dashboard usage analytics widget
     */
    private function render_usage_analytics_widget() {
        $stats = $this->get_dashboard_usage_stats();
        ?>
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle"><?php esc_html_e( 'Usage Analytics', 'edd-customer-dashboard-pro' ); ?></h2>
            </div>
            <div class="inside">
                <div class="main">
                    <p><strong><?php esc_html_e( 'Most Popular Template:', 'edd-customer-dashboard-pro' ); ?></strong> <?php echo esc_html( ucfirst( $stats['popular_template'] ) ); ?></p>
                    <p><strong><?php esc_html_e( 'Average Wishlist Size:', 'edd-customer-dashboard-pro' ); ?></strong> <?php echo esc_html( $stats['avg_wishlist_size'] ); ?> items</p>
                    
                    <?php if ( ! empty( $stats['popular_downloads'] ) ) : ?>
                        <h4><?php esc_html_e( 'Most Downloaded (Last 30 Days)', 'edd-customer-dashboard-pro' ); ?></h4>
                        <ol class="popular-downloads-list">
                            <?php foreach ( $stats['popular_downloads'] as $download ) : ?>
                                <li><?php echo esc_html( $download->post_title ); ?> <span class="download-count">(<?php echo esc_html( $download->download_count ); ?>)</span></li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Check plugin health and show warnings
     */
    private function check_plugin_health() {
        $health_issues = array();

        // Check if EDD is active
        if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {
            $health_issues[] = array(
                'type' => 'error',
                'message' => esc_html__( 'Easy Digital Downloads is not active. The Customer Dashboard Pro requires EDD to function.', 'edd-customer-dashboard-pro' )
            );
        }

        // Check PHP version
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            $health_issues[] = array(
                'type' => 'warning',
                'message' => sprintf( 
                    esc_html__( 'Your PHP version (%s) is below the recommended version (7.4+). Consider upgrading for better performance and security.', 'edd-customer-dashboard-pro' ),
                    PHP_VERSION
                )
            );
        }

        // Check if dashboard page is set
        $dashboard_page = EDD_Dashboard_Pro()->get_option( 'dashboard_page' );
        if ( empty( $dashboard_page ) ) {
            $health_issues[] = array(
                'type' => 'warning',
                'message' => esc_html__( 'No dashboard page has been selected. Please choose a page in the plugin settings.', 'edd-customer-dashboard-pro' )
            );
        }

        // Check write permissions
        $upload_dir = wp_upload_dir();
        if ( ! is_writable( $upload_dir['basedir'] ) ) {
            $health_issues[] = array(
                'type' => 'error',
                'message' => esc_html__( 'Upload directory is not writable. This may prevent file downloads from working properly.', 'edd-customer-dashboard-pro' )
            );
        }

        return $health_issues;
    }

    /**
     * Display health issues as admin notices
     */
    public function display_health_issues() {
        $health_issues = $this->check_plugin_health();

        foreach ( $health_issues as $issue ) {
            $this->add_admin_notice( $issue['message'], $issue['type'] );
        }
    }

    /**
     * Initialize admin menu with health checks
     */
    public function init_with_health_checks() {
        $this->init_hooks();
        $this->register_admin_hooks();
        
        // Run health checks on admin pages
        if ( is_admin() ) {
            add_action( 'admin_init', array( $this, 'display_health_issues' ) );
        }
    }

    /**
     * Get plugin status for display
     *
     * @return array
     */
    public function get_plugin_status() {
        $status = array(
            'version' => EDD_DASHBOARD_PRO_VERSION,
            'edd_active' => class_exists( 'Easy_Digital_Downloads' ),
            'edd_sl_active' => class_exists( 'EDD_Software_Licensing' ),
            'dashboard_page_set' => ! empty( EDD_Dashboard_Pro()->get_option( 'dashboard_page' ) ),
            'template_selected' => ! empty( EDD_Dashboard_Pro()->get_option( 'template' ) ),
            'health_score' => $this->calculate_health_score()
        );

        return $status;
    }

    /**
     * Calculate plugin health score
     *
     * @return int Health score (0-100)
     */
    private function calculate_health_score() {
        $score = 100;
        $health_issues = $this->check_plugin_health();

        foreach ( $health_issues as $issue ) {
            if ( $issue['type'] === 'error' ) {
                $score -= 25;
            } elseif ( $issue['type'] === 'warning' ) {
                $score -= 10;
            }
        }

        return max( 0, $score );
    }

    /**
     * Render plugin status widget for overview
     */
    private function render_plugin_status_widget() {
        $status = $this->get_plugin_status();
        $health_class = '';
        
        if ( $status['health_score'] >= 80 ) {
            $health_class = 'good';
        } elseif ( $status['health_score'] >= 60 ) {
            $health_class = 'warning';
        } else {
            $health_class = 'critical';
        }
        ?>
        <div class="postbox">
            <div class="postbox-header">
                <h2 class="hndle"><?php esc_html_e( 'Plugin Status', 'edd-customer-dashboard-pro' ); ?></h2>
            </div>
            <div class="inside">
                <div class="main">
                    <div class="plugin-health-score health-<?php echo esc_attr( $health_class ); ?>">
                        <span class="health-score-number"><?php echo esc_html( $status['health_score'] ); ?>%</span>
                        <span class="health-score-label"><?php esc_html_e( 'Health Score', 'edd-customer-dashboard-pro' ); ?></span>
                    </div>
                    
                    <ul class="plugin-status-list">
                        <li class="status-item <?php echo $status['edd_active'] ? 'status-good' : 'status-error'; ?>">
                            <span class="dashicons dashicons-<?php echo $status['edd_active'] ? 'yes-alt' : 'warning'; ?>"></span>
                            <?php esc_html_e( 'Easy Digital Downloads', 'edd-customer-dashboard-pro' ); ?>
                        </li>
                        <li class="status-item <?php echo $status['edd_sl_active'] ? 'status-good' : 'status-warning'; ?>">
                            <span class="dashicons dashicons-<?php echo $status['edd_sl_active'] ? 'yes-alt' : 'minus'; ?>"></span>
                            <?php esc_html_e( 'EDD Software Licensing', 'edd-customer-dashboard-pro' ); ?>
                        </li>
                        <li class="status-item <?php echo $status['dashboard_page_set'] ? 'status-good' : 'status-warning'; ?>">
                            <span class="dashicons dashicons-<?php echo $status['dashboard_page_set'] ? 'yes-alt' : 'warning'; ?>"></span>
                            <?php esc_html_e( 'Dashboard Page Set', 'edd-customer-dashboard-pro' ); ?>
                        </li>
                        <li class="status-item <?php echo $status['template_selected'] ? 'status-good' : 'status-warning'; ?>">
                            <span class="dashicons dashicons-<?php echo $status['template_selected'] ? 'yes-alt' : 'warning'; ?>"></span>
                            <?php esc_html_e( 'Template Selected', 'edd-customer-dashboard-pro' ); ?>
                        </li>
                    </ul>
                    
                    <?php if ( $status['health_score'] < 100 ) : ?>
                        <p>
                            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-dashboard-pro-status' ) ); ?>" class="button">
                                <?php esc_html_e( 'View Details', 'edd-customer-dashboard-pro' ); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
}
<?php
/**
 * Advanced Settings View
 * Template for the advanced settings tab
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current settings
$settings = EDD_Dashboard_Pro()->admin_settings->get_all_settings();
$system_info = EDD_Dashboard_Pro()->admin_settings->get_system_info();
$health_status = EDD_Dashboard_Pro()->admin_settings->get_health_status();
?>

<div class="settings-advanced-tab">
    <div class="settings-section">
        <h3><?php esc_html_e( 'Performance Settings', 'edd-customer-dashboard-pro' ); ?></h3>
        <p class="description">
            <?php esc_html_e( 'Configure caching and performance optimization settings.', 'edd-customer-dashboard-pro' ); ?>
        </p>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Caching', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'Caching Options', 'edd-customer-dashboard-pro' ); ?></legend>
                            
                            <label for="cache_customer_data">
                                <input type="checkbox" name="cache_customer_data" id="cache_customer_data" 
                                       value="1" <?php checked( $settings['cache_customer_data'] ?? true ); ?>>
                                <?php esc_html_e( 'Cache customer data', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Cache customer data to improve dashboard loading speed. Recommended for sites with many customers.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
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
                        <span class="description-text"><?php esc_html_e( 'minutes', 'edd-customer-dashboard-pro' ); ?></span>
                        <p class="description">
                            <?php esc_html_e( 'How long to cache customer data. Shorter durations show more recent data but may impact performance.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                        
                        <div class="cache-stats">
                            <?php
                            $cache_size = $this->get_cache_size();
                            $cache_hits = get_option( 'edd_dashboard_pro_cache_hits', 0 );
                            $cache_misses = get_option( 'edd_dashboard_pro_cache_misses', 0 );
                            $hit_ratio = $cache_hits + $cache_misses > 0 ? round( ( $cache_hits / ( $cache_hits + $cache_misses ) ) * 100, 1 ) : 0;
                            ?>
                            <div class="cache-stat-grid">
                                <div class="cache-stat-item">
                                    <span class="stat-label"><?php esc_html_e( 'Cache Size:', 'edd-customer-dashboard-pro' ); ?></span>
                                    <span class="stat-value"><?php echo esc_html( size_format( $cache_size ) ); ?></span>
                                </div>
                                <div class="cache-stat-item">
                                    <span class="stat-label"><?php esc_html_e( 'Hit Ratio:', 'edd-customer-dashboard-pro' ); ?></span>
                                    <span class="stat-value"><?php echo esc_html( $hit_ratio ); ?>%</span>
                                </div>
                            </div>
                            
                            <button type="button" class="button button-secondary clear-cache-btn">
                                <?php esc_html_e( 'Clear Cache Now', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e( 'AJAX Loading', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'AJAX Loading Options', 'edd-customer-dashboard-pro' ); ?></legend>
                            
                            <label for="enable_ajax_loading">
                                <input type="checkbox" name="enable_ajax_loading" id="enable_ajax_loading" 
                                       value="1" <?php checked( $settings['enable_ajax_loading'] ?? true ); ?>>
                                <?php esc_html_e( 'Enable AJAX content loading', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Load dashboard sections dynamically via AJAX for faster initial page loads.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
                            
                            <label for="lazy_load_images">
                                <input type="checkbox" name="lazy_load_images" id="lazy_load_images" 
                                       value="1" <?php checked( $settings['lazy_load_images'] ?? true ); ?>>
                                <?php esc_html_e( 'Lazy load images', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Load product images only when they become visible in the viewport.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="settings-section">
        <h3><?php esc_html_e( 'Security Settings', 'edd-customer-dashboard-pro' ); ?></h3>
        <p class="description">
            <?php esc_html_e( 'Configure security measures and access controls for the dashboard.', 'edd-customer-dashboard-pro' ); ?>
        </p>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Access Control', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <label for="require_login_redirect">
                            <input type="checkbox" name="require_login_redirect" id="require_login_redirect" 
                                   value="1" <?php checked( $settings['require_login_redirect'] ?? true ); ?>>
                            <?php esc_html_e( 'Redirect to login page for non-logged-in users', 'edd-customer-dashboard-pro' ); ?>
                        </label>
                        <p class="description">
                            <?php esc_html_e( 'Automatically redirect visitors to the login page when accessing the dashboard without being logged in.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="session_timeout"><?php esc_html_e( 'Session Timeout', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="number" name="session_timeout" id="session_timeout" 
                               value="<?php echo esc_attr( $settings['session_timeout'] ?? 60 ); ?>" 
                               min="15" max="480" class="small-text">
                        <span class="description-text"><?php esc_html_e( 'minutes', 'edd-customer-dashboard-pro' ); ?></span>
                        <p class="description">
                            <?php esc_html_e( 'Automatically log out inactive users after the specified time. Set to 0 to disable.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="download_rate_limit"><?php esc_html_e( 'Download Rate Limit', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="number" name="download_rate_limit" id="download_rate_limit" 
                               value="<?php echo esc_attr( $settings['download_rate_limit'] ?? 10 ); ?>" 
                               min="0" max="100" class="small-text">
                        <span class="description-text"><?php esc_html_e( 'downloads per hour', 'edd-customer-dashboard-pro' ); ?></span>
                        <p class="description">
                            <?php esc_html_e( 'Maximum number of file downloads allowed per user per hour. Set to 0 for unlimited.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e( 'Security Logging', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'Security Logging Options', 'edd-customer-dashboard-pro' ); ?></legend>
                            
                            <label for="enable_security_logging">
                                <input type="checkbox" name="enable_security_logging" id="enable_security_logging" 
                                       value="1" <?php checked( $settings['enable_security_logging'] ?? true ); ?>>
                                <?php esc_html_e( 'Enable security event logging', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Log security events such as login attempts, download requests, and access violations.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
                            
                            <label for="log_failed_logins">
                                <input type="checkbox" name="log_failed_logins" id="log_failed_logins" 
                                       value="1" <?php checked( $settings['log_failed_logins'] ?? true ); ?>>
                                <?php esc_html_e( 'Log failed login attempts', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Track failed login attempts for security monitoring.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
                        </fieldset>
                        
                        <?php if ( $settings['enable_security_logging'] ?? true ) : ?>
                            <div class="security-log-viewer">
                                <h4><?php esc_html_e( 'Recent Security Events', 'edd-customer-dashboard-pro' ); ?></h4>
                                <?php $this->render_security_log_table(); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="settings-section">
        <h3><?php esc_html_e( 'Debugging & Development', 'edd-customer-dashboard-pro' ); ?></h3>
        <p class="description">
            <?php esc_html_e( 'Enable debugging features for troubleshooting and development purposes.', 'edd-customer-dashboard-pro' ); ?>
        </p>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Debug Mode', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'Debug Options', 'edd-customer-dashboard-pro' ); ?></legend>
                            
                            <label for="debug_mode">
                                <input type="checkbox" name="debug_mode" id="debug_mode" 
                                       value="1" <?php checked( $settings['debug_mode'] ?? false ); ?>>
                                <?php esc_html_e( 'Enable debug mode', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Enable debug mode for detailed error logging and troubleshooting. Not recommended for production sites.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
                            
                            <label for="enable_query_logging">
                                <input type="checkbox" name="enable_query_logging" id="enable_query_logging" 
                                       value="1" <?php checked( $settings['enable_query_logging'] ?? false ); ?>>
                                <?php esc_html_e( 'Log database queries', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Log all database queries for performance analysis. Only enable when troubleshooting.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
                            
                            <label for="show_debug_info">
                                <input type="checkbox" name="show_debug_info" id="show_debug_info" 
                                       value="1" <?php checked( $settings['show_debug_info'] ?? false ); ?>>
                                <?php esc_html_e( 'Show debug information to administrators', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Display debug information panel for administrators on the frontend dashboard.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
                        </fieldset>
                        
                        <?php if ( $settings['debug_mode'] ?? false ) : ?>
                            <div class="debug-warning">
                                <div class="notice notice-warning inline">
                                    <p>
                                        <span class="dashicons dashicons-warning"></span>
                                        <strong><?php esc_html_e( 'Warning:', 'edd-customer-dashboard-pro' ); ?></strong>
                                        <?php esc_html_e( 'Debug mode is currently enabled. This may impact performance and expose sensitive information. Disable for production sites.', 'edd-customer-dashboard-pro' ); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                
                <tr class="debug-options" <?php echo ! ( $settings['debug_mode'] ?? false ) ? 'style="display: none;"' : ''; ?>>
                    <th scope="row">
                        <label for="debug_level"><?php esc_html_e( 'Debug Level', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <select name="debug_level" id="debug_level" class="regular-text">
                            <?php
                            $debug_levels = array(
                                'basic' => esc_html__( 'Basic (Errors only)', 'edd-customer-dashboard-pro' ),
                                'detailed' => esc_html__( 'Detailed (Errors + Warnings)', 'edd-customer-dashboard-pro' ),
                                'verbose' => esc_html__( 'Verbose (All events)', 'edd-customer-dashboard-pro' )
                            );
                            
                            $selected_level = $settings['debug_level'] ?? 'basic';
                            
                            foreach ( $debug_levels as $level => $label ) {
                                printf(
                                    '<option value="%s" %s>%s</option>',
                                    esc_attr( $level ),
                                    selected( $selected_level, $level, false ),
                                    esc_html( $label )
                                );
                            }
                            ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e( 'Choose the level of detail for debug logging.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                        
                        <div class="debug-actions">
                            <button type="button" class="button button-secondary view-debug-log">
                                <?php esc_html_e( 'View Debug Log', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                            <button type="button" class="button button-secondary clear-debug-log">
                                <?php esc_html_e( 'Clear Debug Log', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                            <button type="button" class="button button-secondary download-debug-log">
                                <?php esc_html_e( 'Download Log File', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="settings-section">
        <h3><?php esc_html_e( 'Data Management', 'edd-customer-dashboard-pro' ); ?></h3>
        <p class="description">
            <?php esc_html_e( 'Configure data retention, cleanup, and uninstall options.', 'edd-customer-dashboard-pro' ); ?>
        </p>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="data_retention_days"><?php esc_html_e( 'Data Retention Period', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="number" name="data_retention_days" id="data_retention_days" 
                               value="<?php echo esc_attr( $settings['data_retention_days'] ?? 365 ); ?>" 
                               min="30" max="3650" class="small-text">
                        <span class="description-text"><?php esc_html_e( 'days', 'edd-customer-dashboard-pro' ); ?></span>
                        <p class="description">
                            <?php esc_html_e( 'How long to retain log data and temporary files. Older data will be automatically cleaned up.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e( 'Automatic Cleanup', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'Automatic Cleanup Options', 'edd-customer-dashboard-pro' ); ?></legend>
                            
                            <label for="auto_cleanup_logs">
                                <input type="checkbox" name="auto_cleanup_logs" id="auto_cleanup_logs" 
                                       value="1" <?php checked( $settings['auto_cleanup_logs'] ?? true ); ?>>
                                <?php esc_html_e( 'Automatically clean up old log files', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Automatically remove log files older than the retention period.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
                            
                            <label for="auto_cleanup_cache">
                                <input type="checkbox" name="auto_cleanup_cache" id="auto_cleanup_cache" 
                                       value="1" <?php checked( $settings['auto_cleanup_cache'] ?? true ); ?>>
                                <?php esc_html_e( 'Automatically clean up expired cache', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Automatically remove expired cache files and transients.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
                        </fieldset>
                        
                        <div class="cleanup-actions">
                            <button type="button" class="button button-secondary manual-cleanup">
                                <?php esc_html_e( 'Run Cleanup Now', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                            <span class="cleanup-status"></span>
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e( 'Uninstall Options', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <label for="uninstall_remove_data">
                            <input type="checkbox" name="uninstall_remove_data" id="uninstall_remove_data" 
                                   value="1" <?php checked( $settings['uninstall_remove_data'] ?? false ); ?>>
                            <?php esc_html_e( 'Remove all plugin data when uninstalling', 'edd-customer-dashboard-pro' ); ?>
                        </label>
                        <p class="description">
                            <strong><?php esc_html_e( 'Warning:', 'edd-customer-dashboard-pro' ); ?></strong>
                            <?php esc_html_e( 'This will permanently delete all plugin settings, customer data, logs, and customizations when the plugin is uninstalled. This action cannot be undone.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                        
                        <?php if ( $settings['uninstall_remove_data'] ?? false ) : ?>
                            <div class="uninstall-warning">
                                <div class="notice notice-error inline">
                                    <p>
                                        <span class="dashicons dashicons-warning"></span>
                                        <strong><?php esc_html_e( 'Data Removal Enabled:', 'edd-customer-dashboard-pro' ); ?></strong>
                                        <?php esc_html_e( 'All plugin data will be permanently deleted if the plugin is uninstalled.', 'edd-customer-dashboard-pro' ); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="settings-section">
        <h3><?php esc_html_e( 'System Information', 'edd-customer-dashboard-pro' ); ?></h3>
        <p class="description">
            <?php esc_html_e( 'View system information and health status for troubleshooting and support.', 'edd-customer-dashboard-pro' ); ?>
        </p>
        
        <div class="system-info-grid">
            <div class="system-info-card">
                <h4><?php esc_html_e( 'Plugin Information', 'edd-customer-dashboard-pro' ); ?></h4>
                <div class="info-items">
                    <div class="info-item">
                        <span class="info-label"><?php esc_html_e( 'Version:', 'edd-customer-dashboard-pro' ); ?></span>
                        <span class="info-value"><?php echo esc_html( EDD_DASHBOARD_PRO_VERSION ); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?php esc_html_e( 'Database Version:', 'edd-customer-dashboard-pro' ); ?></span>
                        <span class="info-value"><?php echo esc_html( get_option( 'edd_dashboard_pro_db_version', '1.0.0' ) ); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?php esc_html_e( 'Install Date:', 'edd-customer-dashboard-pro' ); ?></span>
                        <span class="info-value"><?php echo esc_html( date_i18n( get_option( 'date_format' ), get_option( 'edd_dashboard_pro_install_date', time() ) ) ); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="system-info-card">
                <h4><?php esc_html_e( 'WordPress Environment', 'edd-customer-dashboard-pro' ); ?></h4>
                <div class="info-items">
                    <div class="info-item">
                        <span class="info-label"><?php esc_html_e( 'WordPress:', 'edd-customer-dashboard-pro' ); ?></span>
                        <span class="info-value"><?php echo esc_html( $system_info['wordpress']['version'] ); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?php esc_html_e( 'PHP:', 'edd-customer-dashboard-pro' ); ?></span>
                        <span class="info-value"><?php echo esc_html( $system_info['server']['php_version'] ); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label"><?php esc_html_e( 'Memory Limit:', 'edd-customer-dashboard-pro' ); ?></span>
                        <span class="info-value"><?php echo esc_html( $system_info['wordpress']['memory_limit'] ); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="system-info-card">
                <h4><?php esc_html_e( 'Health Status', 'edd-customer-dashboard-pro' ); ?></h4>
                <div class="health-status-display">
                    <div class="health-score health-<?php echo esc_attr( $health_status['overall'] ); ?>">
                        <span class="health-percentage"><?php echo esc_html( $health_status['score'] ?? 100 ); ?>%</span>
                        <span class="health-label"><?php echo esc_html( ucfirst( $health_status['overall'] ) ); ?></span>
                    </div>
                    
                    <div class="health-checks">
                        <?php if ( isset( $health_status['checks'] ) ) : ?>
                            <?php foreach ( array_slice( $health_status['checks'], 0, 3 ) as $check ) : ?>
                                <div class="health-check-item">
                                    <span class="dashicons dashicons-<?php echo $check['status'] === 'good' ? 'yes-alt' : 'warning'; ?>"></span>
                                    <span><?php echo esc_html( $check['label'] ); ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="system-actions">
            <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-dashboard-pro-status' ) ); ?>" 
               class="button button-secondary">
                <?php esc_html_e( 'View Full System Status', 'edd-customer-dashboard-pro' ); ?>
            </a>
            
            <button type="button" class="button button-secondary copy-system-info">
                <?php esc_html_e( 'Copy System Info', 'edd-customer-dashboard-pro' ); ?>
            </button>
            
            <button type="button" class="button button-secondary export-settings">
                <?php esc_html_e( 'Export Settings', 'edd-customer-dashboard-pro' ); ?>
            </button>
            
            <div class="import-settings">
                <input type="file" id="import-file" accept=".json" style="display: none;">
                <button type="button" class="button button-secondary import-settings-btn">
                    <?php esc_html_e( 'Import Settings', 'edd-customer-dashboard-pro' ); ?>
                </button>
            </div>
        </div>
        
        <textarea id="system-info-textarea" style="display: none;" readonly><?php 
            echo esc_textarea( $this->format_system_info_for_copy( $system_info ) ); 
        ?></textarea>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle cache duration visibility
    $('#cache_customer_data').on('change', function() {
        $('.cache-duration-row').toggle(this.checked);
    });
    
    // Toggle debug options visibility
    $('#debug_mode').on('change', function() {
        $('.debug-options').toggle(this.checked);
        
        if (this.checked) {
            $('.debug-warning').show();
        } else {
            $('.debug-warning').hide();
        }
    });
    
    // Clear cache
    $('.clear-cache-btn').on('click', function() {
        var $button = $(this);
        var originalText = $button.text();
        
        $button.text('<?php esc_html_e( 'Clearing...', 'edd-customer-dashboard-pro' ); ?>').prop('disabled', true);
        
        $.post(ajaxurl, {
            action: 'edd_dashboard_pro_clear_cache',
            nonce: eddDashboardProAdmin.nonce
        }, function(response) {
            if (response.success) {
                $button.text('<?php esc_html_e( 'Cache Cleared!', 'edd-customer-dashboard-pro' ); ?>');
                location.reload();
            } else {
                alert(response.data.message || '<?php esc_html_e( 'Failed to clear cache.', 'edd-customer-dashboard-pro' ); ?>');
                $button.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Manual cleanup
    $('.manual-cleanup').on('click', function() {
        var $button = $(this);
        var $status = $('.cleanup-status');
        var originalText = $button.text();
        
        $button.text('<?php esc_html_e( 'Running Cleanup...', 'edd-customer-dashboard-pro' ); ?>').prop('disabled', true);
        $status.text('');
        
        $.post(ajaxurl, {
            action: 'edd_dashboard_pro_manual_cleanup',
            nonce: eddDashboardProAdmin.nonce
        }, function(response) {
            if (response.success) {
                $status.html('<span class="success"><?php esc_html_e( 'Cleanup completed successfully.', 'edd-customer-dashboard-pro' ); ?></span>');
            } else {
                $status.html('<span class="error">' + (response.data.message || '<?php esc_html_e( 'Cleanup failed.', 'edd-customer-dashboard-pro' ); ?>') + '</span>');
            }
            
            $button.text(originalText).prop('disabled', false);
        });
    });
    
    // Debug log actions
    $('.view-debug-log').on('click', function() {
        window.open(ajaxurl + '?action=edd_dashboard_pro_view_debug_log&nonce=' + eddDashboardProAdmin.nonce, '_blank');
    });
    
    $('.clear-debug-log').on('click', function() {
        if (confirm('<?php esc_html_e( 'Are you sure you want to clear the debug log?', 'edd-customer-dashboard-pro' ); ?>')) {
            var $button = $(this);
            var originalText = $button.text();
            
            $button.text('<?php esc_html_e( 'Clearing...', 'edd-customer-dashboard-pro' ); ?>').prop('disabled', true);
            
            $.post(ajaxurl, {
                action: 'edd_dashboard_pro_clear_debug_log',
                nonce: eddDashboardProAdmin.nonce
            }, function(response) {
                if (response.success) {
                    alert('<?php esc_html_e( 'Debug log cleared successfully.', 'edd-customer-dashboard-pro' ); ?>');
                } else {
                    alert(response.data.message || '<?php esc_html_e( 'Failed to clear debug log.', 'edd-customer-dashboard-pro' ); ?>');
                }
                
                $button.text(originalText).prop('disabled', false);
            });
        }
    });
    
    $('.download-debug-log').on('click', function() {
        window.location.href = ajaxurl + '?action=edd_dashboard_pro_download_debug_log&nonce=' + eddDashboardProAdmin.nonce;
    });
    
    // Copy system info
    $('.copy-system-info').on('click', function() {
        var $textarea = $('#system-info-textarea');
        var $button = $(this);
        var originalText = $button.text();
        
        $textarea.show().select();
        document.execCommand('copy');
        $textarea.hide();
        
        $button.text('<?php esc_html_e( 'Copied!', 'edd-customer-dashboard-pro' ); ?>');
        
        setTimeout(function() {
            $button.text(originalText);
        }, 2000);
    });
    
    // Export settings
    $('.export-settings').on('click', function() {
        window.location.href = ajaxurl + '?action=edd_dashboard_pro_export_settings&nonce=' + eddDashboardProAdmin.nonce;
    });
    
    // Import settings
    $('.import-settings-btn').on('click', function() {
        $('#import-file').click();
    });
    
    $('#import-file').on('change', function() {
        var file = this.files[0];
        if (file) {
            if (file.type !== 'application/json' && !file.name.endsWith('.json')) {
                alert('<?php esc_html_e( 'Please select a valid JSON file.', 'edd-customer-dashboard-pro' ); ?>');
                return;
            }
            
            if (confirm('<?php esc_html_e( 'Are you sure you want to import these settings? This will overwrite your current configuration.', 'edd-customer-dashboard-pro' ); ?>')) {
                var formData = new FormData();
                formData.append('action', 'edd_dashboard_pro_import_settings');
                formData.append('nonce', eddDashboardProAdmin.nonce);
                formData.append('settings_file', file);
                
                var $button = $('.import-settings-btn');
                var originalText = $button.text();
                
                $button.text('<?php esc_html_e( 'Importing...', 'edd-customer-dashboard-pro' ); ?>').prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            alert('<?php esc_html_e( 'Settings imported successfully!', 'edd-customer-dashboard-pro' ); ?>');
                            location.reload();
                        } else {
                            alert(response.data.message || '<?php esc_html_e( 'Failed to import settings.', 'edd-customer-dashboard-pro' ); ?>');
                        }
                    },
                    error: function() {
                        alert('<?php esc_html_e( 'Import failed. Please try again.', 'edd-customer-dashboard-pro' ); ?>');
                    },
                    complete: function() {
                        $button.text(originalText).prop('disabled', false);
                        $('#import-file').val('');
                    }
                });
            }
        }
    });
    
    // Session timeout warning
    var sessionTimeout = parseInt('<?php echo esc_js( $settings['session_timeout'] ?? 60 ); ?>');
    if (sessionTimeout > 0) {
        var warningTime = (sessionTimeout - 5) * 60 * 1000; // 5 minutes before timeout
        
        setTimeout(function() {
            if (confirm('<?php esc_html_e( 'Your session will expire in 5 minutes. Do you want to extend it?', 'edd-customer-dashboard-pro' ); ?>')) {
                $.post(ajaxurl, {
                    action: 'edd_dashboard_pro_extend_session',
                    nonce: eddDashboardProAdmin.nonce
                });
            }
        }, warningTime);
    }
    
    // Auto-save settings warning
    var unsavedChanges = false;
    
    $('input, select, textarea').on('change', function() {
        unsavedChanges = true;
    });
    
    $('form').on('submit', function() {
        unsavedChanges = false;
    });
    
    $(window).on('beforeunload', function() {
        if (unsavedChanges) {
            return '<?php esc_html_e( 'You have unsaved changes. Are you sure you want to leave?', 'edd-customer-dashboard-pro' ); ?>';
        }
    });
});
</script>

<style>
.settings-advanced-tab .settings-section {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.settings-advanced-tab .settings-section h3 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #333;
    font-size: 16px;
}

.cache-stats {
    background: #f8f9fa;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
    padding: 15px;
    margin-top: 10px;
}

.cache-stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.cache-stat-item {
    display: flex;
    flex-direction: column;
    text-align: center;
}

.stat-label {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.security-log-viewer {
    background: #f8f9fa;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
    padding: 15px;
    margin-top: 15px;
    max-height: 300px;
    overflow-y: auto;
}

.security-log-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.security-log-table th,
.security-log-table td {
    padding: 8px;
    text-align: left;
    border-bottom: 1px solid #e1e1e1;
}

.security-log-table th {
    background: #f0f0f0;
    font-weight: 600;
}

.debug-warning,
.uninstall-warning {
    margin-top: 10px;
}

.debug-actions,
.cleanup-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.cleanup-status .success {
    color: #2d7d32;
    font-weight: 600;
}

.cleanup-status .error {
    color: #d32f2f;
    font-weight: 600;
}

.system-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.system-info-card {
    background: #f8f9fa;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
}

.system-info-card h4 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 14px;
    font-weight: 600;
}

.info-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px 0;
    border-bottom: 1px solid #e1e1e1;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-size: 13px;
    color: #666;
}

.info-value {
    font-size: 13px;
    font-weight: 600;
    color: #333;
}

.health-status-display {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.health-score {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 15px;
    border-radius: 8px;
    background: #f0f0f0;
}

.health-score.health-good {
    background: #e8f5e8;
    color: #2d7d32;
}

.health-score.health-warning {
    background: #fff3e0;
    color: #f57c00;
}

.health-score.health-critical {
    background: #ffebee;
    color: #d32f2f;
}

.health-percentage {
    font-size: 24px;
    font-weight: 700;
    line-height: 1;
}

.health-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 5px;
}

.health-checks {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.health-check-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #666;
}

.health-check-item .dashicons {
    font-size: 16px;
}

.system-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
    padding-top: 20px;
    border-top: 1px solid #e1e1e1;
}

.import-settings {
    position: relative;
}

.cache-duration-row,
.debug-options {
    transition: opacity 0.3s ease;
}

.description-text {
    color: #666;
    margin-left: 5px;
    font-style: italic;
}

@media (max-width: 768px) {
    .system-info-grid {
        grid-template-columns: 1fr;
    }
    
    .cache-stat-grid {
        grid-template-columns: 1fr;
    }
    
    .debug-actions,
    .cleanup-actions,
    .system-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .health-status-display {
        align-items: center;
    }
}

/* Loading states */
.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Tooltips for system info */
.info-item[title] {
    cursor: help;
}

/* Form validation styles */
.form-invalid {
    border-color: #d32f2f !important;
    box-shadow: 0 0 0 1px #d32f2f;
}

.validation-error {
    color: #d32f2f;
    font-size: 12px;
    margin-top: 5px;
}

/* Progress indicators */
.progress-bar {
    width: 100%;
    height: 4px;
    background: #f0f0f0;
    border-radius: 2px;
    overflow: hidden;
    margin-top: 10px;
}

.progress-fill {
    height: 100%;
    background: #667eea;
    transition: width 0.3s ease;
}

/* Collapsible sections */
.collapsible-header {
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 0;
    border-bottom: 1px solid #e1e1e1;
}

.collapsible-header .dashicons {
    transition: transform 0.2s ease;
}

.collapsible-header.collapsed .dashicons {
    transform: rotate(-90deg);
}

.collapsible-content {
    padding-top: 15px;
}
</style>

<?php
// Helper method to render security log table
if ( ! function_exists( 'render_security_log_table' ) ) {
    function render_security_log_table() {
        $logs = get_option( 'edd_dashboard_pro_security_logs', array() );
        $logs = array_slice( array_reverse( $logs ), 0, 10 ); // Show last 10 entries
        
        if ( empty( $logs ) ) {
            echo '<p>' . esc_html__( 'No security events logged yet.', 'edd-customer-dashboard-pro' ) . '</p>';
            return;
        }
        ?>
        <table class="security-log-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Date', 'edd-customer-dashboard-pro' ); ?></th>
                    <th><?php esc_html_e( 'Event', 'edd-customer-dashboard-pro' ); ?></th>
                    <th><?php esc_html_e( 'User', 'edd-customer-dashboard-pro' ); ?></th>
                    <th><?php esc_html_e( 'IP Address', 'edd-customer-dashboard-pro' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $logs as $log ) : ?>
                    <tr>
                        <td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $log['date'] ) ) ); ?></td>
                        <td><?php echo esc_html( $log['event'] ); ?></td>
                        <td><?php echo esc_html( $log['user'] ?? esc_html__( 'Guest', 'edd-customer-dashboard-pro' ) ); ?></td>
                        <td><?php echo esc_html( $log['ip'] ?? '-' ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    }
}
?>
<?php
/**
 * General Settings View
 * Template for the general settings tab
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current settings
$settings = EDD_Dashboard_Pro()->admin_settings->get_all_settings();
?>

<div class="settings-general-tab">
    <div class="settings-section">
        <h3><?php esc_html_e( 'Basic Configuration', 'edd-customer-dashboard-pro' ); ?></h3>
        <p class="description">
            <?php esc_html_e( 'Configure the basic settings for your customer dashboard.', 'edd-customer-dashboard-pro' ); ?>
        </p>
        
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
                            $selected_page = $settings['dashboard_page'] ?? '';
                            
                            foreach ( $pages as $page ) {
                                printf(
                                    '<option value="%d" %s>%s</option>',
                                    esc_attr( $page->ID ),
                                    selected( $selected_page, $page->ID, false ),
                                    esc_html( $page->post_title )
                                );
                            }
                            ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e( 'Select the page where the customer dashboard will be displayed. Add the [edd_customer_dashboard_pro] shortcode to this page.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                        
                        <?php if ( empty( $selected_page ) ) : ?>
                            <div class="settings-notice notice-info">
                                <p>
                                    <span class="dashicons dashicons-info"></span>
                                    <?php esc_html_e( 'No dashboard page selected. ', 'edd-customer-dashboard-pro' ); ?>
                                    <a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=page' ) ); ?>" 
                                       class="button button-small" target="_blank">
                                        <?php esc_html_e( 'Create New Page', 'edd-customer-dashboard-pro' ); ?>
                                    </a>
                                </p>
                            </div>
                        <?php else : ?>
                            <?php 
                            $page = get_post( $selected_page );
                            if ( $page && ! has_shortcode( $page->post_content, 'edd_customer_dashboard_pro' ) ) : ?>
                                <div class="settings-notice notice-warning">
                                    <p>
                                        <span class="dashicons dashicons-warning"></span>
                                        <?php esc_html_e( 'The selected page does not contain the dashboard shortcode. ', 'edd-customer-dashboard-pro' ); ?>
                                        <a href="<?php echo esc_url( get_edit_post_link( $selected_page ) ); ?>" 
                                           class="button button-small" target="_blank">
                                            <?php esc_html_e( 'Edit Page', 'edd-customer-dashboard-pro' ); ?>
                                        </a>
                                    </p>
                                </div>
                            <?php endif; ?>
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
                        <label for="dashboard_subtitle"><?php esc_html_e( 'Dashboard Subtitle', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="dashboard_subtitle" id="dashboard_subtitle" 
                               value="<?php echo esc_attr( $settings['dashboard_subtitle'] ?? esc_html__( 'Manage your account and downloads', 'edd-customer-dashboard-pro' ) ); ?>" 
                               class="regular-text">
                        <p class="description">
                            <?php esc_html_e( 'Optional subtitle displayed below the main title.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="settings-section">
        <h3><?php esc_html_e( 'Display Settings', 'edd-customer-dashboard-pro' ); ?></h3>
        <p class="description">
            <?php esc_html_e( 'Control how content is displayed on the dashboard.', 'edd-customer-dashboard-pro' ); ?>
        </p>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="items_per_page"><?php esc_html_e( 'Items Per Page', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="number" name="items_per_page" id="items_per_page" 
                               value="<?php echo esc_attr( $settings['items_per_page'] ?? 10 ); ?>" 
                               min="1" max="100" class="small-text">
                        <span class="description-text"><?php esc_html_e( 'items per page', 'edd-customer-dashboard-pro' ); ?></span>
                        <p class="description">
                            <?php esc_html_e( 'Number of items to display per page in purchase history and other lists.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="date_format"><?php esc_html_e( 'Date Format', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <select name="date_format" id="date_format" class="regular-text">
                            <?php
                            $date_formats = array(
                                'F j, Y' => date( 'F j, Y' ), // March 10, 2023
                                'Y-m-d' => date( 'Y-m-d' ),   // 2023-03-10
                                'm/d/Y' => date( 'm/d/Y' ),   // 03/10/2023
                                'd/m/Y' => date( 'd/m/Y' ),   // 10/03/2023
                                'j F Y' => date( 'j F Y' ),   // 10 March 2023
                            );
                            
                            $selected_format = $settings['date_format'] ?? 'F j, Y';
                            
                            foreach ( $date_formats as $format => $example ) {
                                printf(
                                    '<option value="%s" %s>%s (%s)</option>',
                                    esc_attr( $format ),
                                    selected( $selected_format, $format, false ),
                                    esc_html( $example ),
                                    esc_html( $format )
                                );
                            }
                            ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e( 'Choose how dates are displayed throughout the dashboard.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e( 'Welcome Message', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'Welcome Message Settings', 'edd-customer-dashboard-pro' ); ?></legend>
                            
                            <label for="show_welcome_message">
                                <input type="checkbox" name="show_welcome_message" id="show_welcome_message" 
                                       value="1" <?php checked( $settings['show_welcome_message'] ?? true ); ?>>
                                <?php esc_html_e( 'Display welcome message on dashboard', 'edd-customer-dashboard-pro' ); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e( 'Show a personalized welcome message when customers access their dashboard.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
                        </fieldset>
                    </td>
                </tr>
                
                <tr class="welcome-message-options" <?php echo ! ( $settings['show_welcome_message'] ?? true ) ? 'style="display: none;"' : ''; ?>>
                    <th scope="row">
                        <label for="welcome_message_text"><?php esc_html_e( 'Welcome Message Text', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <textarea name="welcome_message_text" id="welcome_message_text" 
                                  rows="3" cols="50" class="large-text"><?php 
                            echo esc_textarea( $settings['welcome_message_text'] ?? esc_html__( 'Welcome back, {customer_name}! Here\'s your account overview.', 'edd-customer-dashboard-pro' ) ); 
                        ?></textarea>
                        <p class="description">
                            <?php esc_html_e( 'Customize the welcome message. Use {customer_name} to display the customer\'s name.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                        <div class="welcome-message-preview">
                            <strong><?php esc_html_e( 'Preview:', 'edd-customer-dashboard-pro' ); ?></strong>
                            <div class="preview-content">
                                <?php 
                                $preview_text = $settings['welcome_message_text'] ?? esc_html__( 'Welcome back, {customer_name}! Here\'s your account overview.', 'edd-customer-dashboard-pro' );
                                echo esc_html( str_replace( '{customer_name}', 'John Doe', $preview_text ) );
                                ?>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="settings-section">
        <h3><?php esc_html_e( 'Navigation Settings', 'edd-customer-dashboard-pro' ); ?></h3>
        <p class="description">
            <?php esc_html_e( 'Configure dashboard navigation and menu options.', 'edd-customer-dashboard-pro' ); ?>
        </p>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Navigation Style', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><?php esc_html_e( 'Navigation Style', 'edd-customer-dashboard-pro' ); ?></legend>
                            
                            <?php
                            $nav_styles = array(
                                'tabs' => esc_html__( 'Horizontal Tabs', 'edd-customer-dashboard-pro' ),
                                'sidebar' => esc_html__( 'Sidebar Menu', 'edd-customer-dashboard-pro' ),
                                'dropdown' => esc_html__( 'Dropdown Menu', 'edd-customer-dashboard-pro' )
                            );
                            
                            $selected_nav = $settings['navigation_style'] ?? 'tabs';
                            
                            foreach ( $nav_styles as $style => $label ) {
                                printf(
                                    '<label><input type="radio" name="navigation_style" value="%s" %s> %s</label><br>',
                                    esc_attr( $style ),
                                    checked( $selected_nav, $style, false ),
                                    esc_html( $label )
                                );
                            }
                            ?>
                            <p class="description">
                                <?php esc_html_e( 'Choose how the dashboard navigation is displayed.', 'edd-customer-dashboard-pro' ); ?>
                            </p>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e( 'Default Section', 'edd-customer-dashboard-pro' ); ?></th>
                    <td>
                        <select name="default_section" id="default_section" class="regular-text">
                            <?php
                            $sections = array(
                                'overview' => esc_html__( 'Overview', 'edd-customer-dashboard-pro' ),
                                'purchases' => esc_html__( 'Purchase History', 'edd-customer-dashboard-pro' ),
                                'downloads' => esc_html__( 'Downloads', 'edd-customer-dashboard-pro' ),
                                'account' => esc_html__( 'Account Details', 'edd-customer-dashboard-pro' ),
                                'wishlist' => esc_html__( 'Wishlist', 'edd-customer-dashboard-pro' )
                            );
                            
                            $default_section = $settings['default_section'] ?? 'overview';
                            
                            foreach ( $sections as $section => $label ) {
                                printf(
                                    '<option value="%s" %s>%s</option>',
                                    esc_attr( $section ),
                                    selected( $default_section, $section, false ),
                                    esc_html( $label )
                                );
                            }
                            ?>
                        </select>
                        <p class="description">
                            <?php esc_html_e( 'Select which section is displayed when customers first visit their dashboard.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Toggle welcome message options
    $('#show_welcome_message').on('change', function() {
        $('.welcome-message-options').toggle(this.checked);
    });
    
    // Update welcome message preview
    $('#welcome_message_text').on('input', function() {
        var text = $(this).val();
        var preview = text.replace('{customer_name}', 'John Doe');
        $('.preview-content').text(preview);
    });
    
    // Dashboard page validation
    $('#dashboard_page').on('change', function() {
        var pageId = $(this).val();
        if (pageId) {
            // Check if shortcode exists (this would need an AJAX call in real implementation)
            // For now, we'll just show a notice
            $('.settings-notice').remove();
            
            // Add notice about adding shortcode if not present
            var notice = $('<div class="settings-notice notice-info"><p><span class="dashicons dashicons-info"></span> ' +
                         'Make sure to add the [edd_customer_dashboard_pro] shortcode to the selected page.</p></div>');
            $(this).closest('td').append(notice);
        }
    });
});
</script>

<style>
.settings-general-tab .settings-section {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.settings-general-tab .settings-section h3 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #333;
    font-size: 16px;
}

.settings-general-tab .settings-section > .description {
    margin-bottom: 20px;
    color: #666;
}

.settings-notice {
    display: inline-block;
    padding: 8px 12px;
    margin-top: 8px;
    border-left: 4px solid #0073aa;
    background: #f7f7f7;
    border-radius: 0 4px 4px 0;
}

.settings-notice.notice-warning {
    border-left-color: #ffb900;
}

.settings-notice.notice-info {
    border-left-color: #00a0d2;
}

.settings-notice .dashicons {
    margin-right: 5px;
    vertical-align: middle;
}

.welcome-message-preview {
    margin-top: 10px;
    padding: 10px;
    background: #f8f9fa;
    border: 1px solid #e1e1e1;
    border-radius: 4px;
}

.welcome-message-preview .preview-content {
    margin-top: 5px;
    font-style: italic;
    color: #555;
}

.description-text {
    color: #666;
    margin-left: 5px;
}

.welcome-message-options {
    transition: opacity 0.3s ease;
}

.form-table th {
    width: 200px;
}

.regular-text {
    width: 300px;
}

.large-text {
    width: 100%;
    max-width: 500px;
}

fieldset label {
    display: block;
    margin-bottom: 8px;
}

fieldset label input[type="radio"] {
    margin-right: 5px;
}
</style>
<?php
/**
 * Template Settings View
 * Template for the template settings tab
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current settings
$settings = EDD_Dashboard_Pro()->admin_settings->get_all_settings();
$template_loader = new EDD_Dashboard_Pro_Template_Loader();
$templates = $template_loader->get_templates();
$current_template = $template_loader->get_current_template();
?>

<div class="settings-templates-tab">
    <div class="settings-section">
        <h3><?php esc_html_e( 'Template Selection', 'edd-customer-dashboard-pro' ); ?></h3>
        <p class="description">
            <?php esc_html_e( 'Choose a template that matches your site\'s design and branding.', 'edd-customer-dashboard-pro' ); ?>
        </p>
        
        <div class="template-selector-grid">
            <?php foreach ( $templates as $template_key => $template_data ) : ?>
                <div class="template-option-card">
                    <label for="template_<?php echo esc_attr( $template_key ); ?>">
                        <input type="radio" name="template" id="template_<?php echo esc_attr( $template_key ); ?>" 
                               value="<?php echo esc_attr( $template_key ); ?>" 
                               <?php checked( $settings['template'] ?? 'default', $template_key ); ?>>
                        
                        <div class="template-preview-card">
                            <div class="template-screenshot">
                                <?php if ( isset( $template_data['screenshot'] ) && $template_data['screenshot'] ) : ?>
                                    <img src="<?php echo esc_url( $template_data['screenshot'] ); ?>" 
                                         alt="<?php echo esc_attr( $template_data['name'] ); ?>">
                                <?php else : ?>
                                    <div class="template-placeholder">
                                        <span class="dashicons dashicons-admin-appearance"></span>
                                        <span class="placeholder-text"><?php esc_html_e( 'No Preview', 'edd-customer-dashboard-pro' ); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ( ( $settings['template'] ?? 'default' ) === $template_key ) : ?>
                                    <div class="template-active-badge">
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php esc_html_e( 'Active', 'edd-customer-dashboard-pro' ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="template-info">
                                <h4 class="template-name"><?php echo esc_html( $template_data['name'] ); ?></h4>
                                <p class="template-version">
                                    <?php printf( esc_html__( 'Version %s', 'edd-customer-dashboard-pro' ), esc_html( $template_data['version'] ) ); ?>
                                </p>
                                <p class="template-description"><?php echo esc_html( $template_data['description'] ); ?></p>
                                
                                <?php if ( isset( $template_data['author'] ) ) : ?>
                                    <p class="template-author">
                                        <?php printf( esc_html__( 'By %s', 'edd-customer-dashboard-pro' ), esc_html( $template_data['author'] ) ); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ( isset( $template_data['tags'] ) && is_array( $template_data['tags'] ) ) : ?>
                                    <div class="template-tags">
                                        <?php foreach ( $template_data['tags'] as $tag ) : ?>
                                            <span class="template-tag"><?php echo esc_html( $tag ); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="template-actions">
                                <button type="button" class="button button-secondary preview-template" 
                                        data-template="<?php echo esc_attr( $template_key ); ?>">
                                    <?php esc_html_e( 'Preview', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                                
                                <?php if ( isset( $template_data['demo_url'] ) ) : ?>
                                    <a href="<?php echo esc_url( $template_data['demo_url'] ); ?>" 
                                       class="button button-secondary" target="_blank">
                                        <?php esc_html_e( 'Live Demo', 'edd-customer-dashboard-pro' ); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if ( empty( $templates ) ) : ?>
            <div class="no-templates-notice">
                <div class="notice notice-warning inline">
                    <p>
                        <span class="dashicons dashicons-warning"></span>
                        <?php esc_html_e( 'No templates found. Please check your plugin installation.', 'edd-customer-dashboard-pro' ); ?>
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="settings-section">
        <h3><?php esc_html_e( 'Template Customization', 'edd-customer-dashboard-pro' ); ?></h3>
        <p class="description">
            <?php esc_html_e( 'Customize the appearance of your selected template with custom colors and CSS.', 'edd-customer-dashboard-pro' ); ?>
        </p>
        
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="primary_color"><?php esc_html_e( 'Primary Color', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="color" name="primary_color" id="primary_color" 
                               value="<?php echo esc_attr( $settings['primary_color'] ?? '#667eea' ); ?>" 
                               class="color-picker">
                        <input type="text" name="primary_color_text" id="primary_color_text" 
                               value="<?php echo esc_attr( $settings['primary_color'] ?? '#667eea' ); ?>" 
                               class="regular-text color-text" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
                        <p class="description">
                            <?php esc_html_e( 'Choose the primary color for buttons, links, and accents.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="secondary_color"><?php esc_html_e( 'Secondary Color', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="color" name="secondary_color" id="secondary_color" 
                               value="<?php echo esc_attr( $settings['secondary_color'] ?? '#f8f9fa' ); ?>" 
                               class="color-picker">
                        <input type="text" name="secondary_color_text" id="secondary_color_text" 
                               value="<?php echo esc_attr( $settings['secondary_color'] ?? '#f8f9fa' ); ?>" 
                               class="regular-text color-text" maxlength="7" pattern="^#[0-9A-Fa-f]{6}$">
                        <p class="description">
                            <?php esc_html_e( 'Choose the secondary color for backgrounds and borders.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="border_radius"><?php esc_html_e( 'Border Radius', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <input type="range" name="border_radius" id="border_radius" 
                               min="0" max="20" step="1"
                               value="<?php echo esc_attr( $settings['border_radius'] ?? 8 ); ?>" 
                               class="border-radius-slider">
                        <span class="range-value"><?php echo esc_html( $settings['border_radius'] ?? 8 ); ?>px</span>
                        <p class="description">
                            <?php esc_html_e( 'Adjust the roundness of corners for cards and buttons.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="custom_css"><?php esc_html_e( 'Custom CSS', 'edd-customer-dashboard-pro' ); ?></label>
                    </th>
                    <td>
                        <textarea name="custom_css" id="custom_css" rows="15" cols="50" class="large-text code"><?php 
                            echo esc_textarea( $settings['custom_css'] ?? '' ); 
                        ?></textarea>
                        <p class="description">
                            <?php esc_html_e( 'Add custom CSS to further customize the dashboard appearance. This CSS will be loaded after the template styles.', 'edd-customer-dashboard-pro' ); ?>
                        </p>
                        
                        <div class="css-helper">
                            <button type="button" class="button button-secondary toggle-css-help">
                                <?php esc_html_e( 'Show CSS Examples', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                            
                            <div class="css-examples" style="display: none;">
                                <h4><?php esc_html_e( 'Common Customizations:', 'edd-customer-dashboard-pro' ); ?></h4>
                                <div class="css-example-tabs">
                                    <button type="button" class="css-tab-btn active" data-tab="colors">
                                        <?php esc_html_e( 'Colors', 'edd-customer-dashboard-pro' ); ?>
                                    </button>
                                    <button type="button" class="css-tab-btn" data-tab="layout">
                                        <?php esc_html_e(
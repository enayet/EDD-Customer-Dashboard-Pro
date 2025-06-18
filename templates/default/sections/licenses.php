<?php
/**
 * License Management Section Template
 * 
 * Displays license keys and activation management for EDD Software Licensing
 *
 * @package EDD_Customer_Dashboard_Pro
 * @template default
 * @section licenses
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if EDD Software Licensing is active
if ( ! class_exists( 'EDD_Software_Licensing' ) ) {
    ?>
    <div class="edd-content-section" id="edd-section-licenses">
        <div class="edd-empty-state">
            <div class="edd-empty-icon">🔑</div>
            <h3><?php esc_html_e( 'License Management Unavailable', 'edd-customer-dashboard-pro' ); ?></h3>
            <p><?php esc_html_e( 'License management requires the EDD Software Licensing extension to be installed and activated.', 'edd-customer-dashboard-pro' ); ?></p>
        </div>
    </div>
    <?php
    return;
}

// Get customer licenses
$licenses = edd_software_licensing()->get_licenses_of_user( $current_user->ID );
?>

<!-- License Management Section -->
<div class="edd-content-section" id="edd-section-licenses" role="tabpanel" aria-labelledby="edd-tab-licenses">
    
    <div class="edd-section-header">
        <h2 class="edd-section-title"><?php esc_html_e( 'License Management', 'edd-customer-dashboard-pro' ); ?></h2>
        
        <?php if ( $settings['show_license_filters'] ?? true ) : ?>
            <div class="edd-section-filters">
                <div class="edd-filter-group">
                    <label for="license-status-filter"><?php esc_html_e( 'Status:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="license-status-filter" class="edd-filter-select">
                        <option value=""><?php esc_html_e( 'All Statuses', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="active"><?php esc_html_e( 'Active', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="inactive"><?php esc_html_e( 'Inactive', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="expired"><?php esc_html_e( 'Expired', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="disabled"><?php esc_html_e( 'Disabled', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
                
                <div class="edd-filter-group">
                    <label for="license-product-filter"><?php esc_html_e( 'Product:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="license-product-filter" class="edd-filter-select">
                        <option value=""><?php esc_html_e( 'All Products', 'edd-customer-dashboard-pro' ); ?></option>
                        <?php
                        $licensed_products = array();
                        if ( $licenses ) {
                            foreach ( $licenses as $license ) {
                                $product_id = $license->download_id;
                                if ( ! isset( $licensed_products[ $product_id ] ) ) {
                                    $licensed_products[ $product_id ] = get_the_title( $product_id );
                                }
                            }
                        }
                        foreach ( $licensed_products as $product_id => $product_name ) :
                        ?>
                            <option value="<?php echo esc_attr( $product_id ); ?>">
                                <?php echo esc_html( $product_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="button" class="edd-btn edd-btn-secondary edd-btn-small edd-apply-license-filters">
                    <?php esc_html_e( 'Apply Filters', 'edd-customer-dashboard-pro' ); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( $licenses ) : ?>
        
        <!-- License Summary -->
        <div class="edd-license-summary">
            <?php
            $license_stats = array(
                'total' => 0,
                'active' => 0,
                'expired' => 0,
                'inactive' => 0,
                'total_sites' => 0
            );
            
            foreach ( $licenses as $license ) {
                $license_stats['total']++;
                $license_stats[ $license->status ]++;
                $license_stats['total_sites'] += edd_software_licensing()->get_site_count( $license->ID );
            }
            ?>
            
            <div class="edd-summary-item">
                <strong><?php esc_html_e( 'Total Licenses:', 'edd-customer-dashboard-pro' ); ?></strong>
                <?php echo esc_html( number_format_i18n( $license_stats['total'] ) ); ?>
            </div>
            
            <div class="edd-summary-item">
                <strong><?php esc_html_e( 'Active Licenses:', 'edd-customer-dashboard-pro' ); ?></strong>
                <span class="edd-status-active"><?php echo esc_html( number_format_i18n( $license_stats['active'] ) ); ?></span>
            </div>
            
            <?php if ( $license_stats['expired'] > 0 ) : ?>
                <div class="edd-summary-item">
                    <strong><?php esc_html_e( 'Expired Licenses:', 'edd-customer-dashboard-pro' ); ?></strong>
                    <span class="edd-status-expired"><?php echo esc_html( number_format_i18n( $license_stats['expired'] ) ); ?></span>
                </div>
            <?php endif; ?>
            
            <div class="edd-summary-item">
                <strong><?php esc_html_e( 'Total Activations:', 'edd-customer-dashboard-pro' ); ?></strong>
                <?php echo esc_html( number_format_i18n( $license_stats['total_sites'] ) ); ?>
            </div>
        </div>

        <!-- License List -->
        <div class="edd-license-list">
            <?php foreach ( $licenses as $license ) : 
                $product_title = get_the_title( $license->download_id );
                $activation_count = edd_software_licensing()->get_site_count( $license->ID );
                $activation_limit = edd_software_licensing()->get_license_limit( $license->download_id, $license->ID );
                $sites = edd_software_licensing()->get_sites( $license->ID );
                $is_expired = $license->status === 'expired';
                $is_lifetime = $license->expiration === 'lifetime';
            ?>
                <div class="edd-license-item" data-license-id="<?php echo esc_attr( $license->ID ); ?>">
                    
                    <!-- License Header -->
                    <div class="edd-license-header">
                        <div class="edd-license-product-info">
                            <h3 class="edd-license-product">
                                <?php echo esc_html( $product_title ); ?>
                            </h3>
                            
                            <div class="edd-license-meta-quick">
                                <span class="edd-purchase-date">
                                    📅 <?php echo esc_html( date_i18n( $settings['date_format'], strtotime( $license->date_created ) ) ); ?>
                                </span>
                                
                                <?php if ( ! $is_lifetime ) : ?>
                                    <span class="edd-license-expires <?php echo $is_expired ? 'expired' : ''; ?>">
                                        ⏰ <?php 
                                        if ( $is_expired ) {
                                            printf(
                                                /* translators: %s: Expiration date */
                                                esc_html__( 'Expired: %s', 'edd-customer-dashboard-pro' ),
                                                esc_html( date_i18n( $settings['date_format'], strtotime( $license->expiration ) ) )
                                            );
                                        } else {
                                            printf(
                                                /* translators: %s: Expiration date */
                                                esc_html__( 'Expires: %s', 'edd-customer-dashboard-pro' ),
                                                esc_html( date_i18n( $settings['date_format'], strtotime( $license->expiration ) ) )
                                            );
                                        }
                                        ?>
                                    </span>
                                <?php else : ?>
                                    <span class="edd-license-lifetime">
                                        ♾️ <?php esc_html_e( 'Lifetime License', 'edd-customer-dashboard-pro' ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="edd-license-status-wrapper">
                            <span class="edd-status-badge edd-status-<?php echo esc_attr( $license->status ); ?>">
                                <?php echo esc_html( ucfirst( $license->status ) ); ?>
                            </span>
                            
                            <?php if ( $is_expired ) : ?>
                                <span class="edd-status-icon expired" title="<?php esc_attr_e( 'License Expired', 'edd-customer-dashboard-pro' ); ?>">⚠️</span>
                            <?php elseif ( $license->status === 'active' ) : ?>
                                <span class="edd-status-icon active" title="<?php esc_attr_e( 'License Active', 'edd-customer-dashboard-pro' ); ?>">✅</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- License Details -->
                    <div class="edd-license-details">
                        
                        <!-- License Key -->
                        <div class="edd-license-key-section">
                            <label class="edd-license-label"><?php esc_html_e( 'License Key:', 'edd-customer-dashboard-pro' ); ?></label>
                            <div class="edd-license-key-wrapper">
                                <div class="edd-license-key" 
                                     title="<?php esc_attr_e( 'Click to copy license key', 'edd-customer-dashboard-pro' ); ?>"
                                     data-license="<?php echo esc_attr( $license->license_key ); ?>">
                                    <?php echo esc_html( $license->license_key ); ?>
                                </div>
                                <button type="button" 
                                        class="edd-btn edd-btn-secondary edd-btn-small edd-copy-license"
                                        data-license="<?php echo esc_attr( $license->license_key ); ?>"
                                        title="<?php esc_attr_e( 'Copy license key', 'edd-customer-dashboard-pro' ); ?>">
                                    📋 <?php esc_html_e( 'Copy', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                            </div>
                        </div>
                        
                        <!-- License Info Grid -->
                        <div class="edd-license-info-grid">
                            <div class="edd-license-meta-item">
                                <strong><?php esc_html_e( 'Purchase Date:', 'edd-customer-dashboard-pro' ); ?></strong>
                                <span><?php echo esc_html( date_i18n( $settings['date_format'], strtotime( $license->date_created ) ) ); ?></span>
                            </div>
                            
                            <?php if ( ! $is_lifetime ) : ?>
                                <div class="edd-license-meta-item">
                                    <strong><?php esc_html_e( 'Expires:', 'edd-customer-dashboard-pro' ); ?></strong>
                                    <span class="<?php echo $is_expired ? 'expired' : ''; ?>">
                                        <?php echo esc_html( date_i18n( $settings['date_format'], strtotime( $license->expiration ) ) ); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="edd-license-meta-item">
                                <strong><?php esc_html_e( 'Activations:', 'edd-customer-dashboard-pro' ); ?></strong>
                                <span>
                                    <?php
                                    if ( $activation_limit == 0 ) {
                                        printf(
                                            /* translators: %d: number of activations */
                                            esc_html__( '%d of unlimited sites', 'edd-customer-dashboard-pro' ),
                                            esc_html( $activation_count )
                                        );
                                    } else {
                                        printf(
                                            /* translators: %1$d: used activations, %2$d: total activations */
                                            esc_html__( '%1$d of %2$d sites', 'edd-customer-dashboard-pro' ),
                                            esc_html( $activation_count ),
                                            esc_html( $activation_limit )
                                        );
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <div class="edd-license-meta-item">
                                <strong><?php esc_html_e( 'License Type:', 'edd-customer-dashboard-pro' ); ?></strong>
                                <span>
                                    <?php 
                                    if ( $is_lifetime ) {
                                        esc_html_e( 'Lifetime', 'edd-customer-dashboard-pro' );
                                    } else {
                                        esc_html_e( 'Standard', 'edd-customer-dashboard-pro' );
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Site Management -->
                        <?php if ( $license->status === 'active' || $license->status === 'inactive' ) : ?>
                            <div class="edd-site-management">
                                <h4 class="edd-site-management-title">
                                    <?php esc_html_e( 'Site Management', 'edd-customer-dashboard-pro' ); ?>
                                    <span class="edd-activation-count">
                                        (<?php echo esc_html( $activation_count ); ?>
                                        <?php if ( $activation_limit > 0 ) : ?>
                                            /<?php echo esc_html( $activation_limit ); ?>
                                        <?php endif; ?>)
                                    </span>
                                </h4>
                                
                                <?php if ( $sites ) : ?>
                                    <div class="edd-activated-sites">
                                        <h5 class="edd-sites-subtitle"><?php esc_html_e( 'Active Sites:', 'edd-customer-dashboard-pro' ); ?></h5>
                                        <?php foreach ( $sites as $site ) : ?>
                                            <div class="edd-site-item">
                                                <div class="edd-site-info">
                                                    <span class="edd-site-url" title="<?php echo esc_attr( $site->site_name ); ?>">
                                                        🌐 <?php echo esc_html( $site->site_name ); ?>
                                                    </span>
                                                    <span class="edd-site-activated">
                                                        📅 <?php 
                                                        printf(
                                                            /* translators: %s: Activation date */
                                                            esc_html__( 'Activated: %s', 'edd-customer-dashboard-pro' ),
                                                            esc_html( date_i18n( $settings['date_format'], strtotime( $site->date_created ) ) )
                                                        );
                                                        ?>
                                                    </span>
                                                </div>
                                                
                                                <div class="edd-site-actions">
                                                    <button type="button" 
                                                            class="edd-btn edd-btn-warning edd-btn-small edd-deactivate-license"
                                                            data-license-id="<?php echo esc_attr( $license->ID ); ?>"
                                                            data-site-url="<?php echo esc_attr( $site->site_name ); ?>"
                                                            data-nonce="<?php echo esc_attr( $nonces['license'] ); ?>"
                                                            title="<?php esc_attr_e( 'Deactivate license for this site', 'edd-customer-dashboard-pro' ); ?>">
                                                        🔓 <?php esc_html_e( 'Deactivate', 'edd-customer-dashboard-pro' ); ?>
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Add New Site -->
                                <?php if ( $activation_limit == 0 || $activation_count < $activation_limit ) : ?>
                                    <div class="edd-add-site-section">
                                        <h5 class="edd-add-site-title"><?php esc_html_e( 'Activate License on New Site:', 'edd-customer-dashboard-pro' ); ?></h5>
                                        <div class="edd-site-input-group">
                                            <input type="url" 
                                                   class="edd-site-url-input" 
                                                   placeholder="<?php esc_attr_e( 'Enter your site URL (e.g., https://example.com)', 'edd-customer-dashboard-pro' ); ?>"
                                                   aria-label="<?php esc_attr_e( 'Site URL for license activation', 'edd-customer-dashboard-pro' ); ?>">
                                            <button type="button" 
                                                    class="edd-btn edd-btn-success edd-activate-license"
                                                    data-license-id="<?php echo esc_attr( $license->ID ); ?>"
                                                    data-nonce="<?php echo esc_attr( $nonces['license'] ); ?>"
                                                    title="<?php esc_attr_e( 'Activate license for this site', 'edd-customer-dashboard-pro' ); ?>">
                                                ✅ <?php esc_html_e( 'Activate', 'edd-customer-dashboard-pro' ); ?>
                                            </button>
                                        </div>
                                        <p class="edd-activation-help">
                                            <?php esc_html_e( 'Enter the full URL of your website where you want to use this license.', 'edd-customer-dashboard-pro' ); ?>
                                        </p>
                                    </div>
                                <?php else : ?>
                                    <div class="edd-license-limit-reached">
                                        <p class="edd-limit-message">
                                            ⚠️ <?php esc_html_e( 'License activation limit reached. Deactivate a site to activate on a new one.', 'edd-customer-dashboard-pro' ); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- License Actions -->
                        <div class="edd-license-actions">
                            
                            <!-- Renewal Actions -->
                            <?php if ( $is_expired ) : ?>
                                <button type="button" 
                                        class="edd-btn edd-btn-primary edd-renew-license"
                                        data-license-id="<?php echo esc_attr( $license->ID ); ?>"
                                        data-download-id="<?php echo esc_attr( $license->download_id ); ?>"
                                        title="<?php esc_attr_e( 'Renew this license', 'edd-customer-dashboard-pro' ); ?>">
                                    🔄 <?php esc_html_e( 'Renew License', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                            <?php endif; ?>
                            
                            <!-- Upgrade Actions -->
                            <button type="button" 
                                    class="edd-btn edd-btn-secondary edd-view-upgrades"
                                    data-download-id="<?php echo esc_attr( $license->download_id ); ?>"
                                    title="<?php esc_attr_e( 'View available upgrades', 'edd-customer-dashboard-pro' ); ?>">
                                ⬆️ <?php esc_html_e( 'View Upgrades', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                            
                            <!-- Invoice Link -->
                            <?php if ( $license->payment_id ) : ?>
                                <a href="<?php echo esc_url( edd_get_success_page_uri( '?payment_key=' . edd_get_payment_key( $license->payment_id ) ) ); ?>" 
                                   class="edd-btn edd-btn-secondary" 
                                   target="_blank"
                                   title="<?php esc_attr_e( 'View original invoice', 'edd-customer-dashboard-pro' ); ?>">
                                    📄 <?php esc_html_e( 'View Invoice', 'edd-customer-dashboard-pro' ); ?>
                                </a>
                            <?php endif; ?>
                            
                            <!-- Support Actions -->
                            <?php if ( $settings['enable_support'] ) : ?>
                                <button type="button" 
                                        class="edd-btn edd-btn-secondary edd-contact-support"
                                        data-license-id="<?php echo esc_attr( $license->ID ); ?>"
                                        data-download-id="<?php echo esc_attr( $license->download_id ); ?>"
                                        title="<?php esc_attr_e( 'Get support for this license', 'edd-customer-dashboard-pro' ); ?>">
                                    💬 <?php esc_html_e( 'Support', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                            <?php endif; ?>
                            
                            <!-- Download Product -->
                            <button type="button" 
                                    class="edd-btn edd-btn-secondary edd-download-product"
                                    data-download-id="<?php echo esc_attr( $license->download_id ); ?>"
                                    data-payment-id="<?php echo esc_attr( $license->payment_id ); ?>"
                                    title="<?php esc_attr_e( 'Download this product', 'edd-customer-dashboard-pro' ); ?>">
                                🔽 <?php esc_html_e( 'Download', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Bulk Actions -->
        <?php if ( $settings['enable_bulk_actions'] ?? false ) : ?>
            <div class="edd-license-bulk-actions">
                <h3 class="edd-bulk-actions-title"><?php esc_html_e( 'Bulk Actions', 'edd-customer-dashboard-pro' ); ?></h3>
                
                <div class="edd-bulk-actions-controls">
                    <label class="edd-select-all-licenses">
                        <input type="checkbox" class="edd-select-all-licenses-checkbox">
                        <?php esc_html_e( 'Select All Licenses', 'edd-customer-dashboard-pro' ); ?>
                    </label>
                    
                    <select class="edd-bulk-action-select">
                        <option value=""><?php esc_html_e( 'Select Action...', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="deactivate_all"><?php esc_html_e( 'Deactivate All Sites', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="check_status"><?php esc_html_e( 'Check Status', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="export_keys"><?php esc_html_e( 'Export License Keys', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                    
                    <button type="button" 
                            class="edd-btn edd-btn-secondary edd-apply-bulk-action"
                            data-nonce="<?php echo esc_attr( $nonces['license'] ); ?>">
                        <?php esc_html_e( 'Apply', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- License Management Help -->
        <?php if ( $settings['show_license_help'] ?? true ) : ?>
            <div class="edd-license-help">
                <h3 class="edd-help-title"><?php esc_html_e( 'License Management Help', 'edd-customer-dashboard-pro' ); ?></h3>
                
                <div class="edd-help-grid">
                    <div class="edd-help-item">
                        <h4><?php esc_html_e( 'How to Use Your License', 'edd-customer-dashboard-pro' ); ?></h4>
                        <ol>
                            <li><?php esc_html_e( 'Copy your license key using the copy button', 'edd-customer-dashboard-pro' ); ?></li>
                            <li><?php esc_html_e( 'Enter the license key in your software settings', 'edd-customer-dashboard-pro' ); ?></li>
                            <li><?php esc_html_e( 'Activate the license to receive updates', 'edd-customer-dashboard-pro' ); ?></li>
                        </ol>
                    </div>
                    
                    <div class="edd-help-item">
                        <h4><?php esc_html_e( 'License Status Guide', 'edd-customer-dashboard-pro' ); ?></h4>
                        <ul>
                            <li><span class="edd-status-badge edd-status-active">Active</span> - License is valid and activated</li>
                            <li><span class="edd-status-badge edd-status-inactive">Inactive</span> - License not yet activated</li>
                            <li><span class="edd-status-badge edd-status-expired">Expired</span> - License needs renewal</li>
                            <li><span class="edd-status-badge edd-status-disabled">Disabled</span> - License manually disabled</li>
                        </ul>
                    </div>
                    
                    <div class="edd-help-item">
                        <h4><?php esc_html_e( 'Troubleshooting', 'edd-customer-dashboard-pro' ); ?></h4>
                        <ul>
                            <li><?php esc_html_e( 'License not activating? Check your site URL format', 'edd-customer-dashboard-pro' ); ?></li>
                            <li><?php esc_html_e( 'Out of activations? Deactivate unused sites first', 'edd-customer-dashboard-pro' ); ?></li>
                            <li><?php esc_html_e( 'License expired? Renew to continue receiving updates', 'edd-customer-dashboard-pro' ); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <!-- Empty State -->
        <div class="edd-empty-state">
            <div class="edd-empty-icon">🔑</div>
            <h3><?php esc_html_e( 'No licenses found', 'edd-customer-dashboard-pro' ); ?></h3>
            <p><?php esc_html_e( 'You don\'t have any software licenses yet. Purchase licensed products to manage your licenses here.', 'edd-customer-dashboard-pro' ); ?></p>
            <div class="edd-empty-actions">
                <a href="<?php echo esc_url( $urls['shop'] ?? edd_get_checkout_uri() ); ?>" 
                   class="edd-btn edd-btn-primary">
                    🛒 <?php esc_html_e( 'Browse Licensed Products', 'edd-customer-dashboard-pro' ); ?>
                </a>
                
                <button type="button" 
                        class="edd-btn edd-btn-secondary edd-nav-tab" 
                        data-section="purchases">
                    📦 <?php esc_html_e( 'View Purchases', 'edd-customer-dashboard-pro' ); ?>
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- License Activation Modal -->
<div id="edd-license-activation-modal" class="edd-modal" style="display: none;">
    <div class="edd-modal-content">
        <div class="edd-modal-header">
            <h3><?php esc_html_e( 'License Activation Details', 'edd-customer-dashboard-pro' ); ?></h3>
            <button type="button" class="edd-modal-close">&times;</button>
        </div>
        <div class="edd-modal-body">
            <div class="edd-activation-form">
                <div class="edd-form-group">
                    <label for="activation-site-url"><?php esc_html_e( 'Site URL:', 'edd-customer-dashboard-pro' ); ?></label>
                    <input type="url" 
                           id="activation-site-url" 
                           class="edd-form-control" 
                           placeholder="https://example.com"
                           required>
                    <p class="edd-field-description">
                        <?php esc_html_e( 'Enter the complete URL where you want to activate this license.', 'edd-customer-dashboard-pro' ); ?>
                    </p>
                </div>
                
                <div class="edd-form-group">
                    <label for="activation-site-name"><?php esc_html_e( 'Site Name (Optional):', 'edd-customer-dashboard-pro' ); ?></label>
                    <input type="text" 
                           id="activation-site-name" 
                           class="edd-form-control" 
                           placeholder="<?php esc_attr_e( 'My Awesome Website', 'edd-customer-dashboard-pro' ); ?>">
                    <p class="edd-field-description">
                        <?php esc_html_e( 'A friendly name to help you identify this activation.', 'edd-customer-dashboard-pro' ); ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="edd-modal-footer">
            <button type="button" class="edd-btn edd-btn-secondary edd-modal-close">
                <?php esc_html_e( 'Cancel', 'edd-customer-dashboard-pro' ); ?>
            </button>
            <button type="button" class="edd-btn edd-btn-primary edd-confirm-activation">
                <?php esc_html_e( 'Activate License', 'edd-customer-dashboard-pro' ); ?>
            </button>
        </div>
    </div>
</div>

<?php
// Hook for additional license content
do_action( 'edd_dashboard_pro_after_licenses_section', $licenses, $settings );
?>
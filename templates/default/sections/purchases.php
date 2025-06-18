<?php
/**
 * Purchases Section Template
 * 
 * Displays customer purchase history with detailed order information
 *
 * @package EDD_Customer_Dashboard_Pro
 * @template default
 * @section purchases
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!-- Purchases Section -->
<div class="edd-content-section active" id="edd-section-purchases" role="tabpanel" aria-labelledby="edd-tab-purchases">
    
    <div class="edd-section-header">
        <h2 class="edd-section-title"><?php esc_html_e( 'Your Orders & Purchases', 'edd-customer-dashboard-pro' ); ?></h2>
        
        <?php if ( $settings['show_purchase_filters'] ?? true ) : ?>
            <div class="edd-section-filters">
                <div class="edd-filter-group">
                    <label for="purchase-status-filter"><?php esc_html_e( 'Status:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="purchase-status-filter" class="edd-filter-select">
                        <option value=""><?php esc_html_e( 'All Statuses', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="completed"><?php esc_html_e( 'Completed', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="pending"><?php esc_html_e( 'Pending', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="processing"><?php esc_html_e( 'Processing', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="refunded"><?php esc_html_e( 'Refunded', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
                
                <div class="edd-filter-group">
                    <label for="purchase-date-filter"><?php esc_html_e( 'Period:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="purchase-date-filter" class="edd-filter-select">
                        <option value=""><?php esc_html_e( 'All Time', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="last_30_days"><?php esc_html_e( 'Last 30 Days', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="last_90_days"><?php esc_html_e( 'Last 90 Days', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="last_year"><?php esc_html_e( 'Last Year', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
                
                <button type="button" class="edd-btn edd-btn-secondary edd-btn-small edd-apply-filters">
                    <?php esc_html_e( 'Apply Filters', 'edd-customer-dashboard-pro' ); ?>
                </button>
                
                <button type="button" class="edd-btn edd-btn-secondary edd-btn-small edd-clear-filters">
                    <?php esc_html_e( 'Clear', 'edd-customer-dashboard-pro' ); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
    
    <?php if ( ! empty( $purchases ) ) : ?>
        <div class="edd-purchase-summary">
            <div class="edd-summary-item">
                <strong><?php esc_html_e( 'Total Orders:', 'edd-customer-dashboard-pro' ); ?></strong>
                <?php echo esc_html( number_format_i18n( count( $purchases ) ) ); ?>
            </div>
            
            <div class="edd-summary-item">
                <strong><?php esc_html_e( 'Total Spent:', 'edd-customer-dashboard-pro' ); ?></strong>
                <?php 
                $total_spent = array_sum( array_column( $purchases, 'total' ) );
                echo esc_html( edd_dashboard_pro_format_price( $total_spent ) );
                ?>
            </div>
            
            <?php if ( $settings['show_download_count'] ?? true ) : ?>
                <div class="edd-summary-item">
                    <strong><?php esc_html_e( 'Total Products:', 'edd-customer-dashboard-pro' ); ?></strong>
                    <?php 
                    $total_products = 0;
                    foreach ( $purchases as $purchase ) {
                        $total_products += count( $purchase['products'] ?? array() );
                    }
                    echo esc_html( number_format_i18n( $total_products ) );
                    ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="edd-purchase-list">
            <?php foreach ( $purchases as $purchase ) : ?>
                <div class="edd-purchase-item" data-payment-id="<?php echo esc_attr( $purchase['id'] ); ?>">
                    
                    <!-- Purchase Header -->
                    <div class="edd-purchase-header">
                        <div class="edd-order-info">
                            <div class="edd-order-number-wrapper">
                                <span class="edd-order-number">
                                    <?php 
                                    printf( 
                                        /* translators: %d: Order number */
                                        esc_html__( 'Order #%d', 'edd-customer-dashboard-pro' ), 
                                        esc_html( $purchase['id'] ) 
                                    ); 
                                    ?>
                                </span>
                                
                                <?php if ( ! empty( $purchase['payment_key'] ) ) : ?>
                                    <span class="edd-payment-key" title="<?php esc_attr_e( 'Payment Key', 'edd-customer-dashboard-pro' ); ?>">
                                        <?php echo esc_html( substr( $purchase['payment_key'], 0, 8 ) . '...' ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="edd-order-meta">
                                <span class="edd-order-date" title="<?php esc_attr_e( 'Purchase Date', 'edd-customer-dashboard-pro' ); ?>">
                                    📅 <?php echo esc_html( date_i18n( $settings['date_format'], strtotime( $purchase['date'] ) ) ); ?>
                                </span>
                                
                                <span class="edd-order-total" title="<?php esc_attr_e( 'Order Total', 'edd-customer-dashboard-pro' ); ?>">
                                    💰 <?php echo esc_html( edd_dashboard_pro_format_price( $purchase['total'], $purchase['currency'] ) ); ?>
                                </span>
                                
                                <?php if ( ! empty( $purchase['gateway'] ) ) : ?>
                                    <span class="edd-payment-method" title="<?php esc_attr_e( 'Payment Method', 'edd-customer-dashboard-pro' ); ?>">
                                        💳 <?php echo esc_html( edd_get_gateway_admin_label( $purchase['gateway'] ) ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="edd-order-status">
                            <span class="edd-status-badge edd-status-<?php echo esc_attr( sanitize_html_class( $purchase['status'] ) ); ?>">
                                <?php echo esc_html( edd_get_payment_status_label( $purchase['status'] ) ); ?>
                            </span>
                            
                            <?php if ( $purchase['status'] === 'completed' ) : ?>
                                <span class="edd-status-icon" title="<?php esc_attr_e( 'Order Completed', 'edd-customer-dashboard-pro' ); ?>">✅</span>
                            <?php elseif ( $purchase['status'] === 'pending' ) : ?>
                                <span class="edd-status-icon" title="<?php esc_attr_e( 'Payment Pending', 'edd-customer-dashboard-pro' ); ?>">⏳</span>
                            <?php elseif ( $purchase['status'] === 'refunded' ) : ?>
                                <span class="edd-status-icon" title="<?php esc_attr_e( 'Order Refunded', 'edd-customer-dashboard-pro' ); ?>">↩️</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Order Products -->
                    <?php if ( ! empty( $purchase['products'] ) ) : ?>
                        <div class="edd-order-products">
                            <?php foreach ( $purchase['products'] as $product ) : ?>
                                <div class="edd-product-row">
                                    <div class="edd-product-info">
                                        <div class="edd-product-thumbnail">
                                            <?php if ( ! empty( $product['thumbnail'] ) ) : ?>
                                                <img src="<?php echo esc_url( $product['thumbnail'] ); ?>" 
                                                     alt="<?php echo esc_attr( $product['name'] ); ?>"
                                                     class="edd-product-thumb">
                                            <?php else : ?>
                                                <div class="edd-product-thumb-placeholder">📦</div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="edd-product-details">
                                            <h4 class="edd-product-name">
                                                <?php if ( ! empty( $product['permalink'] ) ) : ?>
                                                    <a href="<?php echo esc_url( $product['permalink'] ); ?>" target="_blank">
                                                        <?php echo esc_html( $product['name'] ); ?>
                                                    </a>
                                                <?php else : ?>
                                                    <?php echo esc_html( $product['name'] ); ?>
                                                <?php endif; ?>
                                            </h4>
                                            
                                            <div class="edd-product-meta">
                                                <?php if ( isset( $product['price'] ) ) : ?>
                                                    <span class="edd-product-price">
                                                        <?php echo esc_html( edd_dashboard_pro_format_price( $product['price'] ) ); ?>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ( isset( $product['quantity'] ) && $product['quantity'] > 1 ) : ?>
                                                    <span class="edd-product-quantity">
                                                        <?php 
                                                        printf(
                                                            /* translators: %d: Product quantity */
                                                            esc_html__( 'Qty: %d', 'edd-customer-dashboard-pro' ),
                                                            esc_html( $product['quantity'] )
                                                        );
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ( isset( $product['version'] ) ) : ?>
                                                    <span class="edd-product-version">
                                                        <?php 
                                                        printf( 
                                                            /* translators: %s: Product version */
                                                            esc_html__( 'Version: %s', 'edd-customer-dashboard-pro' ), 
                                                            esc_html( $product['version'] ) 
                                                        ); 
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if ( ! empty( $product['category'] ) ) : ?>
                                                    <span class="edd-product-category">
                                                        🏷️ <?php echo esc_html( $product['category'] ); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Download Actions -->
                                    <div class="edd-product-actions">
                                        <?php if ( $purchase['status'] === 'completed' && ! empty( $product['download_files'] ) ) : ?>
                                            <div class="edd-download-buttons">
                                                <?php foreach ( $product['download_files'] as $file ) : ?>
                                                    <button type="button" 
                                                            class="edd-btn edd-btn-download edd-btn-small" 
                                                            data-payment-id="<?php echo esc_attr( $purchase['id'] ); ?>"
                                                            data-download-id="<?php echo esc_attr( $product['id'] ); ?>"
                                                            data-file-key="<?php echo esc_attr( $file['id'] ); ?>"
                                                            data-nonce="<?php echo esc_attr( $nonces['download'] ); ?>"
                                                            title="<?php esc_attr_e( 'Download file', 'edd-customer-dashboard-pro' ); ?>">
                                                        🔽 <?php echo esc_html( $file['name'] ); ?>
                                                    </button>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php elseif ( $purchase['status'] !== 'completed' ) : ?>
                                            <div class="edd-download-unavailable">
                                                <span class="edd-unavailable-message">
                                                    <?php esc_html_e( 'Downloads available after payment completion', 'edd-customer-dashboard-pro' ); ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ( $settings['enable_wishlist'] && $purchase['status'] === 'completed' ) : ?>
                                            <button type="button" 
                                                    class="edd-btn edd-btn-secondary edd-btn-small edd-add-to-wishlist"
                                                    data-download-id="<?php echo esc_attr( $product['id'] ); ?>"
                                                    data-nonce="<?php echo esc_attr( $nonces['wishlist'] ); ?>"
                                                    title="<?php esc_attr_e( 'Add to wishlist', 'edd-customer-dashboard-pro' ); ?>">
                                                ❤️ <?php esc_html_e( 'Add to Wishlist', 'edd-customer-dashboard-pro' ); ?>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- License Information -->
                                <?php if ( $settings['show_license_keys'] && ! empty( $product['license_key'] ) ) : ?>
                                    <div class="edd-license-info">
                                        <div class="edd-license-header">
                                            <strong><?php esc_html_e( 'License Key:', 'edd-customer-dashboard-pro' ); ?></strong>
                                        </div>
                                        <div class="edd-license-key-wrapper">
                                            <div class="edd-license-key" 
                                                 title="<?php esc_attr_e( 'Click to copy license key', 'edd-customer-dashboard-pro' ); ?>"
                                                 data-license="<?php echo esc_attr( $product['license_key'] ); ?>">
                                                <?php echo esc_html( $product['license_key'] ); ?>
                                            </div>
                                            <button type="button" 
                                                    class="edd-btn edd-btn-secondary edd-btn-small edd-copy-license"
                                                    data-license="<?php echo esc_attr( $product['license_key'] ); ?>"
                                                    title="<?php esc_attr_e( 'Copy license key', 'edd-customer-dashboard-pro' ); ?>">
                                                📋
                                            </button>
                                        </div>
                                        
                                        <?php if ( ! empty( $product['license_status'] ) ) : ?>
                                            <div class="edd-license-status">
                                                <span class="edd-license-status-badge edd-license-<?php echo esc_attr( $product['license_status'] ); ?>">
                                                    <?php echo esc_html( ucfirst( $product['license_status'] ) ); ?>
                                                </span>
                                                
                                                <?php if ( ! empty( $product['license_expires'] ) ) : ?>
                                                    <span class="edd-license-expires">
                                                        <?php 
                                                        printf(
                                                            /* translators: %s: License expiration date */
                                                            esc_html__( 'Expires: %s', 'edd-customer-dashboard-pro' ),
                                                            esc_html( date_i18n( $settings['date_format'], strtotime( $product['license_expires'] ) ) )
                                                        );
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Order Actions -->
                    <div class="edd-order-actions">
                        <button type="button" 
                                class="edd-btn edd-btn-secondary edd-btn-small edd-view-receipt" 
                                data-payment-id="<?php echo esc_attr( $purchase['id'] ); ?>"
                                title="<?php esc_attr_e( 'View detailed receipt', 'edd-customer-dashboard-pro' ); ?>">
                            📄 <?php esc_html_e( 'View Receipt', 'edd-customer-dashboard-pro' ); ?>
                        </button>
                        
                        <?php if ( ! empty( $purchase['receipt_url'] ) ) : ?>
                            <a href="<?php echo esc_url( $purchase['receipt_url'] ); ?>" 
                               class="edd-btn edd-btn-secondary edd-btn-small" 
                               target="_blank"
                               title="<?php esc_attr_e( 'View order details in new window', 'edd-customer-dashboard-pro' ); ?>">
                                📋 <?php esc_html_e( 'Order Details', 'edd-customer-dashboard-pro' ); ?>
                            </a>
                        <?php endif; ?>
                        
                        <?php if ( $settings['enable_support'] ) : ?>
                            <button type="button" 
                                    class="edd-btn edd-btn-secondary edd-btn-small edd-contact-support" 
                                    data-payment-id="<?php echo esc_attr( $purchase['id'] ); ?>"
                                    title="<?php esc_attr_e( 'Get support for this order', 'edd-customer-dashboard-pro' ); ?>">
                                💬 <?php esc_html_e( 'Support', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        <?php endif; ?>
                        
                        <?php if ( $purchase['status'] === 'completed' && $settings['enable_reorder'] ?? false ) : ?>
                            <button type="button" 
                                    class="edd-btn edd-btn-primary edd-btn-small edd-reorder" 
                                    data-payment-id="<?php echo esc_attr( $purchase['id'] ); ?>"
                                    data-nonce="<?php echo esc_attr( $nonces['ajax'] ); ?>"
                                    title="<?php esc_attr_e( 'Purchase these items again', 'edd-customer-dashboard-pro' ); ?>">
                                🔄 <?php esc_html_e( 'Reorder', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        <?php endif; ?>
                        
                        <?php if ( $settings['enable_reviews'] ?? false ) : ?>
                            <button type="button" 
                                    class="edd-btn edd-btn-secondary edd-btn-small edd-review-order" 
                                    data-payment-id="<?php echo esc_attr( $purchase['id'] ); ?>"
                                    title="<?php esc_attr_e( 'Leave a review', 'edd-customer-dashboard-pro' ); ?>">
                                ⭐ <?php esc_html_e( 'Review', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Load More Button -->
        <?php if ( count( $purchases ) >= ( $settings['items_per_page'] ?? 10 ) ) : ?>
            <div class="edd-load-more-wrapper">
                <button type="button" 
                        class="edd-btn edd-btn-secondary edd-load-more-purchases" 
                        data-offset="<?php echo esc_attr( count( $purchases ) ); ?>"
                        data-nonce="<?php echo esc_attr( $nonces['ajax'] ); ?>">
                    <?php esc_html_e( 'Load More Purchases', 'edd-customer-dashboard-pro' ); ?>
                    <span class="edd-loading-spinner" style="display: none;">🔄</span>
                </button>
            </div>
        <?php endif; ?>
        
    <?php else : ?>
        <!-- Empty State -->
        <div class="edd-empty-state">
            <div class="edd-empty-icon">📦</div>
            <h3><?php esc_html_e( 'No purchases yet', 'edd-customer-dashboard-pro' ); ?></h3>
            <p><?php esc_html_e( "You haven't made any purchases yet. Browse our products to get started!", 'edd-customer-dashboard-pro' ); ?></p>
            <div class="edd-empty-actions">
                <a href="<?php echo esc_url( $urls['shop'] ?? edd_get_checkout_uri() ); ?>" 
                   class="edd-btn edd-btn-primary">
                    🛒 <?php esc_html_e( 'Browse Products', 'edd-customer-dashboard-pro' ); ?>
                </a>
                
                <?php if ( $settings['enable_wishlist'] ) : ?>
                    <button type="button" 
                            class="edd-btn edd-btn-secondary edd-nav-tab" 
                            data-section="wishlist">
                        ❤️ <?php esc_html_e( 'View Wishlist', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Hook for additional purchases content
do_action( 'edd_dashboard_pro_after_purchases_section', $purchases, $settings );
?>
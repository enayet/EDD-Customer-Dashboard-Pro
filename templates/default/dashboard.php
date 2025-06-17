<?php
/**
 * Default Dashboard Template for EDD Customer Dashboard Pro
 * 
 * This template displays the complete customer dashboard with all sections
 *
 * @package EDD_Customer_Dashboard_Pro
 * @template default
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if user can access dashboard
if ( ! $can_access ) {
    ?>
    <div class="edd-dashboard-pro-error">
        <h3><?php esc_html_e( 'Access Denied', 'edd-customer-dashboard-pro' ); ?></h3>
        <p><?php esc_html_e( 'You need to make at least one purchase to access the customer dashboard.', 'edd-customer-dashboard-pro' ); ?></p>
        <p><a href="<?php echo esc_url( edd_get_checkout_uri() ); ?>" class="edd-button"><?php esc_html_e( 'Browse Products', 'edd-customer-dashboard-pro' ); ?></a></p>
    </div>
    <?php
    return;
}

// Check if user is logged in
if ( ! $is_logged_in ) {
    ?>
    <div class="edd-dashboard-pro-login-required">
        <h3><?php esc_html_e( 'Login Required', 'edd-customer-dashboard-pro' ); ?></h3>
        <p><?php esc_html_e( 'Please log in to view your dashboard.', 'edd-customer-dashboard-pro' ); ?></p>
        <p><a href="<?php echo esc_url( wp_login_url( get_permalink() ) ); ?>" class="edd-button"><?php esc_html_e( 'Log In', 'edd-customer-dashboard-pro' ); ?></a></p>
    </div>
    <?php
    return;
}

// Main dashboard content
?>
<div class="edd-customer-dashboard-pro-wrapper" data-template="default">
    
    <!-- Dashboard Header -->
    <?php if ( $settings['show_welcome_message'] ?? true ) : ?>
        <div class="edd-dashboard-header">
            <div class="edd-welcome-section">
                <div class="edd-welcome-text">
                    <h1>
                        <?php 
                        printf( 
                            /* translators: %s: Customer name */
                            esc_html__( 'Welcome back, %s!', 'edd-customer-dashboard-pro' ), 
                            esc_html( $customer['name'] ?? $current_user->display_name ) 
                        ); 
                        ?>
                    </h1>
                    <p><?php esc_html_e( 'Manage your purchases, downloads, and account settings', 'edd-customer-dashboard-pro' ); ?></p>
                </div>
                <div class="edd-user-avatar">
                    <?php 
                    $avatar = get_avatar( $current_user->ID, 80 );
                    if ( $avatar ) {
                        echo $avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar is safe
                    } else {
                        $initials = '';
                        $name_parts = explode( ' ', $customer['name'] ?? $current_user->display_name );
                        foreach ( $name_parts as $part ) {
                            $initials .= strtoupper( substr( $part, 0, 1 ) );
                        }
                        echo '<div class="edd-avatar-fallback">' . esc_html( substr( $initials, 0, 2 ) ) . '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Stats Overview -->
    <div class="edd-stats-grid">
        <div class="edd-stat-card">
            <div class="edd-stat-icon edd-stat-purchases">📦</div>
            <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $stats['total_purchases'] ?? 0 ) ); ?></div>
            <div class="edd-stat-label"><?php esc_html_e( 'Total Purchases', 'edd-customer-dashboard-pro' ); ?></div>
        </div>
        <div class="edd-stat-card">
            <div class="edd-stat-icon edd-stat-downloads">⬇️</div>
            <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $stats['total_downloads'] ?? 0 ) ); ?></div>
            <div class="edd-stat-label"><?php esc_html_e( 'Downloads', 'edd-customer-dashboard-pro' ); ?></div>
        </div>
        <?php if ( $settings['show_license_keys'] && class_exists( 'EDD_Software_Licensing' ) ) : ?>
            <div class="edd-stat-card">
                <div class="edd-stat-icon edd-stat-licenses">🔑</div>
                <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $stats['active_licenses'] ?? 0 ) ); ?></div>
                <div class="edd-stat-label"><?php esc_html_e( 'Active Licenses', 'edd-customer-dashboard-pro' ); ?></div>
            </div>
        <?php endif; ?>
        <?php if ( $settings['enable_wishlist'] ) : ?>
            <div class="edd-stat-card">
                <div class="edd-stat-icon edd-stat-wishlist">❤️</div>
                <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $stats['wishlist_items'] ?? 0 ) ); ?></div>
                <div class="edd-stat-label"><?php esc_html_e( 'Wishlist Items', 'edd-customer-dashboard-pro' ); ?></div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Navigation Tabs -->
    <div class="edd-dashboard-nav">
        <div class="edd-nav-tabs">
            <a href="#" class="edd-nav-tab active" data-section="purchases">
                📦 <?php esc_html_e( 'Purchases', 'edd-customer-dashboard-pro' ); ?>
            </a>
            <a href="#" class="edd-nav-tab" data-section="downloads">
                ⬇️ <?php esc_html_e( 'Downloads', 'edd-customer-dashboard-pro' ); ?>
            </a>
            <?php if ( $settings['show_license_keys'] && class_exists( 'EDD_Software_Licensing' ) ) : ?>
                <a href="#" class="edd-nav-tab" data-section="licenses">
                    🔑 <?php esc_html_e( 'Licenses', 'edd-customer-dashboard-pro' ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $settings['enable_wishlist'] ) : ?>
                <a href="#" class="edd-nav-tab" data-section="wishlist">
                    ❤️ <?php esc_html_e( 'Wishlist', 'edd-customer-dashboard-pro' ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $settings['enable_analytics'] ) : ?>
                <a href="#" class="edd-nav-tab" data-section="analytics">
                    📊 <?php esc_html_e( 'Analytics', 'edd-customer-dashboard-pro' ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $settings['enable_support'] ) : ?>
                <a href="#" class="edd-nav-tab" data-section="support">
                    💬 <?php esc_html_e( 'Support', 'edd-customer-dashboard-pro' ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="edd-dashboard-content">
        
        <!-- Purchases Section -->
        <div class="edd-content-section active" id="edd-section-purchases">
            <h2 class="edd-section-title"><?php esc_html_e( 'Your Orders & Purchases', 'edd-customer-dashboard-pro' ); ?></h2>
            
            <?php if ( ! empty( $purchases ) ) : ?>
                <div class="edd-purchase-list">
                    <?php foreach ( $purchases as $purchase ) : ?>
                        <div class="edd-purchase-item" data-payment-id="<?php echo esc_attr( $purchase['id'] ); ?>">
                            <div class="edd-purchase-header">
                                <div class="edd-order-info">
                                    <div class="edd-order-meta">
                                        <span class="edd-order-number">
                                            <?php 
                                            printf( 
                                                /* translators: %d: Order number */
                                                esc_html__( 'Order #%d', 'edd-customer-dashboard-pro' ), 
                                                esc_html( $purchase['id'] ) 
                                            ); 
                                            ?>
                                        </span>
                                        <span class="edd-order-date">
                                            <?php echo esc_html( date_i18n( $settings['date_format'], strtotime( $purchase['date'] ) ) ); ?>
                                        </span>
                                        <span class="edd-order-total">
                                            <?php echo esc_html( edd_dashboard_pro_format_price( $purchase['total'], $purchase['currency'] ) ); ?>
                                        </span>
                                    </div>
                                </div>
                                <span class="edd-status-badge edd-status-<?php echo esc_attr( sanitize_html_class( $purchase['status'] ) ); ?>">
                                    <?php echo esc_html( ucfirst( $purchase['status'] ) ); ?>
                                </span>
                            </div>
                            
                            <?php if ( ! empty( $purchase['products'] ) ) : ?>
                                <div class="edd-order-products">
                                    <?php foreach ( $purchase['products'] as $product ) : ?>
                                        <div class="edd-product-row">
                                            <div class="edd-product-details">
                                                <strong class="edd-product-name"><?php echo esc_html( $product['name'] ); ?></strong>
                                                <?php if ( isset( $product['version'] ) ) : ?>
                                                    <div class="edd-product-meta">
                                                        <?php 
                                                        printf( 
                                                            /* translators: %s: Product version */
                                                            esc_html__( 'Version: %s', 'edd-customer-dashboard-pro' ), 
                                                            esc_html( $product['version'] ) 
                                                        ); 
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="edd-product-actions">
                                                <?php if ( ! empty( $product['download_files'] ) ) : ?>
                                                    <?php foreach ( $product['download_files'] as $file ) : ?>
                                                        <button type="button" 
                                                                class="edd-btn edd-btn-download" 
                                                                data-payment-id="<?php echo esc_attr( $purchase['id'] ); ?>"
                                                                data-download-id="<?php echo esc_attr( $product['id'] ); ?>"
                                                                data-file-key="<?php echo esc_attr( $file['id'] ); ?>"
                                                                data-nonce="<?php echo esc_attr( $nonces['download'] ); ?>">
                                                            🔽 <?php echo esc_html( $file['name'] ); ?>
                                                        </button>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <?php if ( $settings['show_license_keys'] && ! empty( $product['license_key'] ) ) : ?>
                                            <div class="edd-license-info">
                                                <div class="edd-license-key" title="<?php esc_attr_e( 'Click to copy', 'edd-customer-dashboard-pro' ); ?>">
                                                    <?php echo esc_html( $product['license_key'] ); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="edd-order-actions">
                                <button type="button" class="edd-btn edd-btn-secondary edd-view-receipt" 
                                        data-payment-id="<?php echo esc_attr( $purchase['id'] ); ?>">
                                    📄 <?php esc_html_e( 'View Receipt', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                                
                                <?php if ( ! empty( $purchase['receipt_url'] ) ) : ?>
                                    <a href="<?php echo esc_url( $purchase['receipt_url'] ); ?>" 
                                       class="edd-btn edd-btn-secondary" target="_blank">
                                        📋 <?php esc_html_e( 'Order Details', 'edd-customer-dashboard-pro' ); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ( $settings['enable_support'] ) : ?>
                                    <button type="button" class="edd-btn edd-btn-secondary edd-contact-support" 
                                            data-payment-id="<?php echo esc_attr( $purchase['id'] ); ?>">
                                        💬 <?php esc_html_e( 'Support', 'edd-customer-dashboard-pro' ); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Load More Button -->
                <?php if ( count( $purchases ) >= ( $settings['items_per_page'] ?? 10 ) ) : ?>
                    <div class="edd-load-more-wrapper">
                        <button type="button" class="edd-btn edd-btn-secondary edd-load-more-purchases" 
                                data-offset="<?php echo esc_attr( count( $purchases ) ); ?>"
                                data-nonce="<?php echo esc_attr( $nonces['ajax'] ); ?>">
                            <?php esc_html_e( 'Load More Purchases', 'edd-customer-dashboard-pro' ); ?>
                        </button>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="edd-empty-state">
                    <div class="edd-empty-icon">📦</div>
                    <h3><?php esc_html_e( 'No purchases yet', 'edd-customer-dashboard-pro' ); ?></h3>
                    <p><?php esc_html_e( "You haven't made any purchases yet.", 'edd-customer-dashboard-pro' ); ?></p>
                    <a href="<?php echo esc_url( edd_get_checkout_uri() ); ?>" class="edd-btn edd-btn-primary">
                        <?php esc_html_e( 'Browse Products', 'edd-customer-dashboard-pro' ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Downloads Section -->
        <div class="edd-content-section" id="edd-section-downloads">
            <h2 class="edd-section-title"><?php esc_html_e( 'Download History', 'edd-customer-dashboard-pro' ); ?></h2>
            
            <?php if ( ! empty( $download_history ) ) : ?>
                <div class="edd-download-list">
                    <?php foreach ( $download_history as $download ) : ?>
                        <div class="edd-download-item">
                            <div class="edd-download-header">
                                <div class="edd-download-name"><?php echo esc_html( $download['product_name'] ); ?></div>
                                <div class="edd-download-date">
                                    <?php 
                                    printf( 
                                        /* translators: %s: Download date */
                                        esc_html__( 'Downloaded: %s', 'edd-customer-dashboard-pro' ), 
                                        esc_html( date_i18n( $settings['date_format'], strtotime( $download['date'] ) ) ) 
                                    ); 
                                    ?>
                                </div>
                            </div>
                            
                            <?php if ( $settings['show_download_limits'] ) : ?>
                                <div class="edd-download-limits">
                                    <?php
                                    $limits = edd_dashboard_pro_get_download_limits( 
                                        $download['payment_id'] ?? 0, 
                                        $download['download_id'], 
                                        $current_user->ID 
                                    );
                                    
                                    if ( $limits['limit'] > 0 ) {
                                        printf(
                                            /* translators: %1$d: used downloads, %2$d: total downloads */
                                            esc_html__( 'Downloads: %1$d of %2$d', 'edd-customer-dashboard-pro' ),
                                            esc_html( $limits['used'] ),
                                            esc_html( $limits['limit'] )
                                        );
                                    } else {
                                        esc_html_e( 'Downloads: Unlimited', 'edd-customer-dashboard-pro' );
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="edd-empty-state">
                    <div class="edd-empty-icon">⬇️</div>
                    <h3><?php esc_html_e( 'No downloads yet', 'edd-customer-dashboard-pro' ); ?></h3>
                    <p><?php esc_html_e( 'Your download history will appear here.', 'edd-customer-dashboard-pro' ); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Licenses Section -->
        <?php if ( $settings['show_license_keys'] && class_exists( 'EDD_Software_Licensing' ) ) : ?>
            <div class="edd-content-section" id="edd-section-licenses">
                <h2 class="edd-section-title"><?php esc_html_e( 'License Management', 'edd-customer-dashboard-pro' ); ?></h2>
                
                <?php 
                $licenses = edd_software_licensing()->get_licenses_of_user( $current_user->ID );
                if ( $licenses ) : 
                ?>
                    <div class="edd-license-list">
                        <?php foreach ( $licenses as $license ) : ?>
                            <div class="edd-license-item">
                                <div class="edd-license-header">
                                    <div class="edd-license-product">
                                        <?php echo esc_html( get_the_title( $license->download_id ) ); ?>
                                    </div>
                                    <span class="edd-status-badge edd-status-<?php echo esc_attr( $license->status ); ?>">
                                        <?php echo esc_html( ucfirst( $license->status ) ); ?>
                                    </span>
                                </div>
                                
                                <div class="edd-license-details">
                                    <div class="edd-license-key" title="<?php esc_attr_e( 'Click to copy', 'edd-customer-dashboard-pro' ); ?>">
                                        <?php echo esc_html( $license->license_key ); ?>
                                    </div>
                                    
                                    <div class="edd-license-meta">
                                        <div class="edd-license-meta-item">
                                            <strong><?php esc_html_e( 'Purchase Date:', 'edd-customer-dashboard-pro' ); ?></strong>
                                            <?php echo esc_html( date_i18n( $settings['date_format'], strtotime( $license->date_created ) ) ); ?>
                                        </div>
                                        
                                        <?php if ( $license->expiration ) : ?>
                                            <div class="edd-license-meta-item">
                                                <strong><?php esc_html_e( 'Expires:', 'edd-customer-dashboard-pro' ); ?></strong>
                                                <?php echo esc_html( date_i18n( $settings['date_format'], strtotime( $license->expiration ) ) ); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="edd-license-meta-item">
                                            <strong><?php esc_html_e( 'Activations:', 'edd-customer-dashboard-pro' ); ?></strong>
                                            <?php
                                            $activation_count = edd_software_licensing()->get_site_count( $license->ID );
                                            $activation_limit = edd_software_licensing()->get_license_limit( $license->download_id, $license->ID );
                                            
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
                                        </div>
                                    </div>
                                    
                                    <?php if ( $license->status === 'active' ) : ?>
                                        <div class="edd-site-management">
                                            <h4><?php esc_html_e( 'Manage Sites', 'edd-customer-dashboard-pro' ); ?></h4>
                                            
                                            <?php
                                            $sites = edd_software_licensing()->get_sites( $license->ID );
                                            if ( $sites ) :
                                            ?>
                                                <div class="edd-activated-sites">
                                                    <?php foreach ( $sites as $site ) : ?>
                                                        <div class="edd-site-item">
                                                            <span class="edd-site-url"><?php echo esc_html( $site->site_name ); ?></span>
                                                            <button type="button" 
                                                                    class="edd-btn edd-btn-secondary edd-btn-small edd-deactivate-license"
                                                                    data-license-id="<?php echo esc_attr( $license->ID ); ?>"
                                                                    data-site-url="<?php echo esc_attr( $site->site_name ); ?>"
                                                                    data-nonce="<?php echo esc_attr( $nonces['license'] ); ?>">
                                                                🔓 <?php esc_html_e( 'Deactivate', 'edd-customer-dashboard-pro' ); ?>
                                                            </button>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ( $activation_limit == 0 || $activation_count < $activation_limit ) : ?>
                                                <div class="edd-site-input-group">
                                                    <input type="url" 
                                                           class="edd-site-url-input" 
                                                           placeholder="<?php esc_attr_e( 'Enter your site URL (e.g., https://example.com)', 'edd-customer-dashboard-pro' ); ?>">
                                                    <button type="button" 
                                                            class="edd-btn edd-btn-success edd-activate-license"
                                                            data-license-id="<?php echo esc_attr( $license->ID ); ?>"
                                                            data-nonce="<?php echo esc_attr( $nonces['license'] ); ?>">
                                                        ✅ <?php esc_html_e( 'Activate', 'edd-customer-dashboard-pro' ); ?>
                                                    </button>
                                                </div>
                                            <?php else : ?>
                                                <p class="edd-license-limit-reached">
                                                    <?php esc_html_e( 'License limit reached. Deactivate a site to activate on a new one.', 'edd-customer-dashboard-pro' ); ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="edd-license-actions">
                                        <?php if ( $license->status === 'expired' ) : ?>
                                            <button type="button" class="edd-btn edd-btn-warning">
                                                🔄 <?php esc_html_e( 'Renew License', 'edd-customer-dashboard-pro' ); ?>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button type="button" class="edd-btn edd-btn-secondary">
                                            ⬆️ <?php esc_html_e( 'View Upgrades', 'edd-customer-dashboard-pro' ); ?>
                                        </button>
                                        
                                        <a href="<?php echo esc_url( edd_get_success_page_uri( '?payment_key=' . edd_get_payment_key( $license->payment_id ) ) ); ?>" 
                                           class="edd-btn edd-btn-secondary" target="_blank">
                                            📄 <?php esc_html_e( 'View Invoice', 'edd-customer-dashboard-pro' ); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="edd-empty-state">
                        <div class="edd-empty-icon">🔑</div>
                        <h3><?php esc_html_e( 'No licenses found', 'edd-customer-dashboard-pro' ); ?></h3>
                        <p><?php esc_html_e( 'Your software licenses will appear here.', 'edd-customer-dashboard-pro' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Wishlist Section -->
        <?php if ( $settings['enable_wishlist'] ) : ?>
            <div class="edd-content-section" id="edd-section-wishlist">
                <h2 class="edd-section-title"><?php esc_html_e( 'Your Wishlist', 'edd-customer-dashboard-pro' ); ?></h2>
                
                <?php if ( ! empty( $wishlist ) ) : ?>
                    <div class="edd-wishlist-grid">
                        <?php foreach ( $wishlist as $item ) : ?>
                            <div class="edd-wishlist-item" data-download-id="<?php echo esc_attr( $item['id'] ); ?>">
                                <?php if ( $item['thumbnail'] ) : ?>
                                    <div class="edd-wishlist-image">
                                        <img src="<?php echo esc_url( $item['thumbnail'] ); ?>" 
                                             alt="<?php echo esc_attr( $item['title'] ); ?>">
                                    </div>
                                <?php else : ?>
                                    <div class="edd-product-image">🎁</div>
                                <?php endif; ?>
                                
                                <div class="edd-wishlist-details">
                                    <h3 class="edd-wishlist-title">
                                        <a href="<?php echo esc_url( $item['permalink'] ); ?>">
                                            <?php echo esc_html( $item['title'] ); ?>
                                        </a>
                                    </h3>
                                    <div class="edd-wishlist-price">
                                        <?php echo esc_html( edd_dashboard_pro_format_price( $item['price'] ) ); ?>
                                    </div>
                                    
                                    <div class="edd-wishlist-actions">
                                        <a href="<?php echo esc_url( add_query_arg( array( 'edd_action' => 'add_to_cart', 'download_id' => $item['id'] ), edd_get_checkout_uri() ) ); ?>" 
                                           class="edd-btn edd-btn-primary">
                                            🛒 <?php esc_html_e( 'Add to Cart', 'edd-customer-dashboard-pro' ); ?>
                                        </a>
                                        
                                        <button type="button" 
                                                class="edd-btn edd-btn-secondary edd-remove-from-wishlist"
                                                data-download-id="<?php echo esc_attr( $item['id'] ); ?>"
                                                data-nonce="<?php echo esc_attr( $nonces['wishlist'] ); ?>">
                                            ❌ <?php esc_html_e( 'Remove', 'edd-customer-dashboard-pro' ); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="edd-empty-state">
                        <div class="edd-empty-icon">❤️</div>
                        <h3><?php esc_html_e( 'Your wishlist is empty', 'edd-customer-dashboard-pro' ); ?></h3>
                        <p><?php esc_html_e( 'Add products to your wishlist to save them for later.', 'edd-customer-dashboard-pro' ); ?></p>
                        <a href="<?php echo esc_url( edd_get_checkout_uri() ); ?>" class="edd-btn edd-btn-primary">
                            <?php esc_html_e( 'Browse Products', 'edd-customer-dashboard-pro' ); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Analytics Section -->
        <?php if ( $settings['enable_analytics'] ) : ?>
            <div class="edd-content-section" id="edd-section-analytics">
                <h2 class="edd-section-title"><?php esc_html_e( 'Purchase Analytics', 'edd-customer-dashboard-pro' ); ?></h2>
                
                <?php if ( ! empty( $analytics ) ) : ?>
                    <div class="edd-analytics-grid">
                        <div class="edd-stat-card">
                            <div class="edd-stat-icon edd-stat-money">💰</div>
                            <div class="edd-stat-number">
                                <?php echo esc_html( edd_dashboard_pro_format_price( $analytics['total_spent'] ?? 0 ) ); ?>
                            </div>
                            <div class="edd-stat-label"><?php esc_html_e( 'Total Spent', 'edd-customer-dashboard-pro' ); ?></div>
                        </div>
                        
                        <div class="edd-stat-card">
                            <div class="edd-stat-icon edd-stat-average">📈</div>
                            <div class="edd-stat-number">
                                <?php echo esc_html( edd_dashboard_pro_format_price( $analytics['avg_order_value'] ?? 0 ) ); ?>
                            </div>
                            <div class="edd-stat-label"><?php esc_html_e( 'Avg Order Value', 'edd-customer-dashboard-pro' ); ?></div>
                        </div>
                        
                        <div class="edd-stat-card">
                            <div class="edd-stat-icon edd-stat-ratio">⬇️</div>
                            <div class="edd-stat-number">
                                <?php echo esc_html( number_format( $analytics['downloads_per_purchase'] ?? 0, 1 ) ); ?>
                            </div>
                            <div class="edd-stat-label"><?php esc_html_e( 'Downloads/Purchase', 'edd-customer-dashboard-pro' ); ?></div>
                        </div>
                        
                        <div class="edd-stat-card">
                            <div class="edd-stat-icon edd-stat-time">📅</div>
                            <div class="edd-stat-number">
                                <?php echo esc_html( number_format_i18n( $analytics['customer_since_days'] ?? 0 ) ); ?>
                            </div>
                            <div class="edd-stat-label"><?php esc_html_e( 'Days as Customer', 'edd-customer-dashboard-pro' ); ?></div>
                        </div>
                    </div>
                    
                    <div class="edd-analytics-details">
                        <div class="edd-analytics-item">
                            <h4><?php esc_html_e( 'Customer Since', 'edd-customer-dashboard-pro' ); ?></h4>
                            <p>
                                <?php 
                                if ( isset( $analytics['first_purchase_date'] ) ) {
                                    echo esc_html( date_i18n( $settings['date_format'], strtotime( $analytics['first_purchase_date'] ) ) );
                                } else {
                                    esc_html_e( 'No purchases yet', 'edd-customer-dashboard-pro' );
                                }
                                ?>
                            </p>
                        </div>
                        
                        <div class="edd-analytics-item">
                            <h4><?php esc_html_e( 'Total Purchases', 'edd-customer-dashboard-pro' ); ?></h4>
                            <p><?php echo esc_html( number_format_i18n( $analytics['total_purchases'] ?? 0 ) ); ?></p>
                        </div>
                        
                        <div class="edd-analytics-item">
                            <h4><?php esc_html_e( 'Total Downloads', 'edd-customer-dashboard-pro' ); ?></h4>
                            <p><?php echo esc_html( number_format_i18n( $stats['total_downloads'] ?? 0 ) ); ?></p>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="edd-empty-state">
                        <div class="edd-empty-icon">📊</div>
                        <h3><?php esc_html_e( 'Analytics Coming Soon', 'edd-customer-dashboard-pro' ); ?></h3>
                        <p><?php esc_html_e( 'Detailed charts and insights about your purchase history will appear here.', 'edd-customer-dashboard-pro' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Support Section -->
        <?php if ( $settings['enable_support'] ) : ?>
            <div class="edd-content-section" id="edd-section-support">
                <h2 class="edd-section-title"><?php esc_html_e( 'Support Center', 'edd-customer-dashboard-pro' ); ?></h2>
                
                <div class="edd-support-options">
                    <div class="edd-support-grid">
                        <div class="edd-support-card">
                            <div class="edd-support-icon">📚</div>
                            <h3><?php esc_html_e( 'Documentation', 'edd-customer-dashboard-pro' ); ?></h3>
                            <p><?php esc_html_e( 'Browse our comprehensive documentation and guides.', 'edd-customer-dashboard-pro' ); ?></p>
                            <a href="#" class="edd-btn edd-btn-secondary" target="_blank">
                                <?php esc_html_e( 'View Docs', 'edd-customer-dashboard-pro' ); ?>
                            </a>
                        </div>
                        
                        <div class="edd-support-card">
                            <div class="edd-support-icon">🎥</div>
                            <h3><?php esc_html_e( 'Video Tutorials', 'edd-customer-dashboard-pro' ); ?></h3>
                            <p><?php esc_html_e( 'Watch step-by-step video tutorials and walkthroughs.', 'edd-customer-dashboard-pro' ); ?></p>
                            <a href="#" class="edd-btn edd-btn-secondary" target="_blank">
                                <?php esc_html_e( 'Watch Videos', 'edd-customer-dashboard-pro' ); ?>
                            </a>
                        </div>
                        
                        <div class="edd-support-card">
                            <div class="edd-support-icon">💬</div>
                            <h3><?php esc_html_e( 'Contact Support', 'edd-customer-dashboard-pro' ); ?></h3>
                            <p><?php esc_html_e( 'Get help from our support team for technical issues.', 'edd-customer-dashboard-pro' ); ?></p>
                            <button type="button" class="edd-btn edd-btn-primary edd-contact-support">
                                <?php esc_html_e( 'Create Ticket', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        </div>
                        
                        <div class="edd-support-card">
                            <div class="edd-support-icon">❓</div>
                            <h3><?php esc_html_e( 'FAQ', 'edd-customer-dashboard-pro' ); ?></h3>
                            <p><?php esc_html_e( 'Find answers to frequently asked questions.', 'edd-customer-dashboard-pro' ); ?></p>
                            <a href="#" class="edd-btn edd-btn-secondary" target="_blank">
                                <?php esc_html_e( 'View FAQ', 'edd-customer-dashboard-pro' ); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Support Tickets (if support system is integrated) -->
                <?php
                $recent_tickets = apply_filters( 'edd_dashboard_pro_recent_support_tickets', array(), $current_user->ID );
                if ( ! empty( $recent_tickets ) ) :
                ?>
                    <div class="edd-recent-tickets">
                        <h3><?php esc_html_e( 'Recent Support Tickets', 'edd-customer-dashboard-pro' ); ?></h3>
                        <div class="edd-ticket-list">
                            <?php foreach ( $recent_tickets as $ticket ) : ?>
                                <div class="edd-ticket-item">
                                    <div class="edd-ticket-header">
                                        <span class="edd-ticket-subject"><?php echo esc_html( $ticket['subject'] ); ?></span>
                                        <span class="edd-ticket-status edd-status-<?php echo esc_attr( sanitize_html_class( $ticket['status'] ) ); ?>">
                                            <?php echo esc_html( ucfirst( $ticket['status'] ) ); ?>
                                        </span>
                                    </div>
                                    <div class="edd-ticket-meta">
                                        <span class="edd-ticket-date">
                                            <?php echo esc_html( date_i18n( $settings['date_format'], strtotime( $ticket['date'] ) ) ); ?>
                                        </span>
                                        <a href="<?php echo esc_url( $ticket['url'] ); ?>" class="edd-ticket-link">
                                            <?php esc_html_e( 'View Ticket', 'edd-customer-dashboard-pro' ); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Dashboard Footer -->
    <div class="edd-dashboard-footer">
        <div class="edd-dashboard-footer-content">
            <div class="edd-footer-links">
                <a href="<?php echo esc_url( $urls['account'] ); ?>"><?php esc_html_e( 'Account Settings', 'edd-customer-dashboard-pro' ); ?></a>
                <a href="<?php echo esc_url( $urls['shop'] ); ?>"><?php esc_html_e( 'Browse Products', 'edd-customer-dashboard-pro' ); ?></a>
                <?php if ( $settings['enable_support'] ) : ?>
                    <a href="#" class="edd-contact-support"><?php esc_html_e( 'Contact Support', 'edd-customer-dashboard-pro' ); ?></a>
                <?php endif; ?>
                <a href="<?php echo esc_url( $urls['logout'] ); ?>"><?php esc_html_e( 'Logout', 'edd-customer-dashboard-pro' ); ?></a>
            </div>
            
            <div class="edd-footer-info">
                <p>
                    <?php 
                    printf(
                        /* translators: %s: Site name */
                        esc_html__( '© %s. All rights reserved.', 'edd-customer-dashboard-pro' ),
                        esc_html( get_bloginfo( 'name' ) )
                    );
                    ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Hidden elements for functionality -->
    <div class="edd-dashboard-hidden">
        <!-- Nonces for AJAX requests -->
        <input type="hidden" id="edd-ajax-nonce" value="<?php echo esc_attr( $nonces['ajax'] ); ?>">
        <input type="hidden" id="edd-download-nonce" value="<?php echo esc_attr( $nonces['download'] ); ?>">
        <input type="hidden" id="edd-wishlist-nonce" value="<?php echo esc_attr( $nonces['wishlist'] ); ?>">
        <input type="hidden" id="edd-license-nonce" value="<?php echo esc_attr( $nonces['license'] ); ?>">
        
        <!-- AJAX URL -->
        <input type="hidden" id="edd-ajax-url" value="<?php echo esc_url( $urls['ajax'] ); ?>">
        
        <!-- Settings -->
        <input type="hidden" id="edd-items-per-page" value="<?php echo esc_attr( $settings['items_per_page'] ?? 10 ); ?>">
        
        <!-- User ID -->
        <input type="hidden" id="edd-current-user-id" value="<?php echo esc_attr( $current_user->ID ); ?>">
    </div>

    <!-- Loading overlay -->
    <div class="edd-loading-overlay" style="display: none;">
        <div class="edd-loading-spinner">
            <div class="edd-spinner"></div>
            <p><?php esc_html_e( 'Loading...', 'edd-customer-dashboard-pro' ); ?></p>
        </div>
    </div>

</div>

<!-- Modal for purchase details -->
<div id="edd-purchase-details-modal" class="edd-modal" style="display: none;">
    <div class="edd-modal-content">
        <div class="edd-modal-header">
            <h3><?php esc_html_e( 'Order Details', 'edd-customer-dashboard-pro' ); ?></h3>
            <button type="button" class="edd-modal-close">&times;</button>
        </div>
        <div class="edd-modal-body">
            <!-- Content will be loaded via AJAX -->
        </div>
        <div class="edd-modal-footer">
            <button type="button" class="edd-btn edd-btn-secondary edd-modal-close">
                <?php esc_html_e( 'Close', 'edd-customer-dashboard-pro' ); ?>
            </button>
        </div>
    </div>
</div>

<!-- Modal for support ticket creation -->
<?php if ( $settings['enable_support'] ) : ?>
<div id="edd-support-modal" class="edd-modal" style="display: none;">
    <div class="edd-modal-content">
        <div class="edd-modal-header">
            <h3><?php esc_html_e( 'Contact Support', 'edd-customer-dashboard-pro' ); ?></h3>
            <button type="button" class="edd-modal-close">&times;</button>
        </div>
        <div class="edd-modal-body">
            <form id="edd-support-form" class="edd-support-form">
                <div class="edd-form-group">
                    <label for="support-subject"><?php esc_html_e( 'Subject', 'edd-customer-dashboard-pro' ); ?></label>
                    <input type="text" id="support-subject" name="subject" class="edd-form-control" required>
                </div>
                
                <div class="edd-form-group">
                    <label for="support-category"><?php esc_html_e( 'Category', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="support-category" name="category" class="edd-form-control">
                        <option value="general"><?php esc_html_e( 'General Support', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="technical"><?php esc_html_e( 'Technical Issue', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="billing"><?php esc_html_e( 'Billing Question', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="feature"><?php esc_html_e( 'Feature Request', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
                
                <div class="edd-form-group">
                    <label for="support-priority"><?php esc_html_e( 'Priority', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="support-priority" name="priority" class="edd-form-control">
                        <option value="low"><?php esc_html_e( 'Low', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="normal" selected><?php esc_html_e( 'Normal', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="high"><?php esc_html_e( 'High', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="urgent"><?php esc_html_e( 'Urgent', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
                
                <div class="edd-form-group">
                    <label for="support-message"><?php esc_html_e( 'Message', 'edd-customer-dashboard-pro' ); ?></label>
                    <textarea id="support-message" name="message" rows="6" class="edd-form-control" required 
                              placeholder="<?php esc_attr_e( 'Please describe your issue or question in detail...', 'edd-customer-dashboard-pro' ); ?>"></textarea>
                </div>
                
                <input type="hidden" name="customer_email" value="<?php echo esc_attr( $customer['email'] ?? $current_user->user_email ); ?>">
                <input type="hidden" name="customer_name" value="<?php echo esc_attr( $customer['name'] ?? $current_user->display_name ); ?>">
                <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonces['ajax'] ); ?>">
            </form>
        </div>
        <div class="edd-modal-footer">
            <button type="button" class="edd-btn edd-btn-secondary edd-modal-close">
                <?php esc_html_e( 'Cancel', 'edd-customer-dashboard-pro' ); ?>
            </button>
            <button type="submit" form="edd-support-form" class="edd-btn edd-btn-primary">
                <?php esc_html_e( 'Submit Ticket', 'edd-customer-dashboard-pro' ); ?>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<script type="text/javascript">
// Initialize dashboard functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.eddDashboardPro !== 'undefined') {
        window.eddDashboardPro.init();
    }
});
</script>

<?php
// Allow customization hooks
do_action( 'edd_dashboard_pro_after_template', $template_data, $customer );

// End of template - ensure clean output
?>
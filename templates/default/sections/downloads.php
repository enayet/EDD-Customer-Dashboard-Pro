<?php
/**
 * Downloads Section Template
 * 
 * Displays available downloads and download history
 *
 * @package EDD_Customer_Dashboard_Pro
 * @template default
 * @section downloads
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<!-- Downloads Section -->
<div class="edd-content-section" id="edd-section-downloads" role="tabpanel" aria-labelledby="edd-tab-downloads">
    
    <div class="edd-section-header">
        <h2 class="edd-section-title"><?php esc_html_e( 'Your Downloads', 'edd-customer-dashboard-pro' ); ?></h2>
        
        <?php if ( $settings['show_download_filters'] ?? true ) : ?>
            <div class="edd-section-filters">
                <div class="edd-filter-group">
                    <label for="download-type-filter"><?php esc_html_e( 'Type:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="download-type-filter" class="edd-filter-select">
                        <option value=""><?php esc_html_e( 'All Downloads', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="available"><?php esc_html_e( 'Available', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="downloaded"><?php esc_html_e( 'Downloaded', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="expired"><?php esc_html_e( 'Expired', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
                
                <div class="edd-filter-group">
                    <label for="download-category-filter"><?php esc_html_e( 'Category:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="download-category-filter" class="edd-filter-select">
                        <option value=""><?php esc_html_e( 'All Categories', 'edd-customer-dashboard-pro' ); ?></option>
                        <?php
                        $download_categories = get_terms( array(
                            'taxonomy' => 'download_category',
                            'hide_empty' => true
                        ) );
                        foreach ( $download_categories as $category ) :
                        ?>
                            <option value="<?php echo esc_attr( $category->slug ); ?>">
                                <?php echo esc_html( $category->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="button" class="edd-btn edd-btn-secondary edd-btn-small edd-apply-download-filters">
                    <?php esc_html_e( 'Apply Filters', 'edd-customer-dashboard-pro' ); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( ! empty( $available_downloads ) ) : ?>
        
        <!-- Download Summary -->
        <div class="edd-download-summary">
            <div class="edd-summary-item">
                <strong><?php esc_html_e( 'Available Downloads:', 'edd-customer-dashboard-pro' ); ?></strong>
                <?php echo esc_html( number_format_i18n( count( $available_downloads ) ) ); ?>
            </div>
            
            <div class="edd-summary-item">
                <strong><?php esc_html_e( 'Total Downloads:', 'edd-customer-dashboard-pro' ); ?></strong>
                <?php echo esc_html( number_format_i18n( $stats['total_downloads'] ?? 0 ) ); ?>
            </div>
            
            <?php if ( ! empty( $stats['last_download_date'] ) ) : ?>
                <div class="edd-summary-item">
                    <strong><?php esc_html_e( 'Last Downloaded:', 'edd-customer-dashboard-pro' ); ?></strong>
                    <?php echo esc_html( date_i18n( $settings['date_format'], strtotime( $stats['last_download_date'] ) ) ); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Available Downloads -->
        <div class="edd-downloads-section">
            <h3 class="edd-subsection-title">
                <?php esc_html_e( 'Available Downloads', 'edd-customer-dashboard-pro' ); ?>
                <span class="edd-count">(<?php echo esc_html( count( $available_downloads ) ); ?>)</span>
            </h3>
            
            <div class="edd-download-grid">
                <?php foreach ( $available_downloads as $download ) : ?>
                    <div class="edd-download-card" data-download-id="<?php echo esc_attr( $download['id'] ); ?>">
                        
                        <!-- Download Header -->
                        <div class="edd-download-header">
                            <div class="edd-download-thumbnail">
                                <?php if ( ! empty( $download['thumbnail'] ) ) : ?>
                                    <img src="<?php echo esc_url( $download['thumbnail'] ); ?>" 
                                         alt="<?php echo esc_attr( $download['name'] ); ?>"
                                         class="edd-download-thumb">
                                <?php else : ?>
                                    <div class="edd-download-thumb-placeholder">
                                        <?php echo esc_html( $download['file_type_icon'] ?? '📄' ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="edd-download-info">
                                <h4 class="edd-download-name">
                                    <?php if ( ! empty( $download['permalink'] ) ) : ?>
                                        <a href="<?php echo esc_url( $download['permalink'] ); ?>" target="_blank">
                                            <?php echo esc_html( $download['name'] ); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo esc_html( $download['name'] ); ?>
                                    <?php endif; ?>
                                </h4>
                                
                                <div class="edd-download-meta">
                                    <?php if ( ! empty( $download['purchase_date'] ) ) : ?>
                                        <span class="edd-purchase-date">
                                            📅 <?php 
                                            printf(
                                                /* translators: %s: Purchase date */
                                                esc_html__( 'Purchased: %s', 'edd-customer-dashboard-pro' ),
                                                esc_html( date_i18n( $settings['date_format'], strtotime( $download['purchase_date'] ) ) )
                                            );
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ( ! empty( $download['version'] ) ) : ?>
                                        <span class="edd-download-version">
                                            🏷️ <?php 
                                            printf(
                                                /* translators: %s: Version number */
                                                esc_html__( 'Version: %s', 'edd-customer-dashboard-pro' ),
                                                esc_html( $download['version'] )
                                            );
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ( ! empty( $download['size'] ) ) : ?>
                                        <span class="edd-download-size">
                                            💾 <?php echo esc_html( $download['size'] ); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Download Limits -->
                        <?php if ( $settings['show_download_limits'] && ! empty( $download['limits'] ) ) : ?>
                            <div class="edd-download-limits">
                                <div class="edd-limit-info">
                                    <?php if ( $download['limits']['limit'] > 0 ) : ?>
                                        <div class="edd-limit-bar">
                                            <div class="edd-limit-progress" 
                                                 style="width: <?php echo esc_attr( ( $download['limits']['used'] / $download['limits']['limit'] ) * 100 ); ?>%"></div>
                                        </div>
                                        <span class="edd-limit-text">
                                            <?php 
                                            printf(
                                                /* translators: %1$d: used downloads, %2$d: total allowed downloads */
                                                esc_html__( '%1$d of %2$d downloads used', 'edd-customer-dashboard-pro' ),
                                                esc_html( $download['limits']['used'] ),
                                                esc_html( $download['limits']['limit'] )
                                            );
                                            ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="edd-unlimited-downloads">
                                            ♾️ <?php esc_html_e( 'Unlimited downloads', 'edd-customer-dashboard-pro' ); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ( ! empty( $download['limits']['expires'] ) ) : ?>
                                    <div class="edd-download-expires">
                                        ⏰ <?php 
                                        printf(
                                            /* translators: %s: Expiration date */
                                            esc_html__( 'Expires: %s', 'edd-customer-dashboard-pro' ),
                                            esc_html( date_i18n( $settings['date_format'], strtotime( $download['limits']['expires'] ) ) )
                                        );
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Download Files -->
                        <?php if ( ! empty( $download['files'] ) ) : ?>
                            <div class="edd-download-files">
                                <?php foreach ( $download['files'] as $file ) : ?>
                                    <div class="edd-file-item">
                                        <div class="edd-file-info">
                                            <span class="edd-file-name">
                                                <?php echo esc_html( $file['name'] ); ?>
                                            </span>
                                            
                                            <?php if ( ! empty( $file['size'] ) ) : ?>
                                                <span class="edd-file-size">
                                                    (<?php echo esc_html( $file['size'] ); ?>)
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="edd-file-actions">
                                            <?php if ( $download['can_download'] ) : ?>
                                                <button type="button" 
                                                        class="edd-btn edd-btn-download edd-btn-small" 
                                                        data-payment-id="<?php echo esc_attr( $download['payment_id'] ); ?>"
                                                        data-download-id="<?php echo esc_attr( $download['id'] ); ?>"
                                                        data-file-key="<?php echo esc_attr( $file['id'] ); ?>"
                                                        data-nonce="<?php echo esc_attr( $nonces['download'] ); ?>"
                                                        title="<?php esc_attr_e( 'Download this file', 'edd-customer-dashboard-pro' ); ?>">
                                                    🔽 <?php esc_html_e( 'Download', 'edd-customer-dashboard-pro' ); ?>
                                                </button>
                                            <?php else : ?>
                                                <span class="edd-download-unavailable">
                                                    <?php esc_html_e( 'Download limit reached', 'edd-customer-dashboard-pro' ); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Download Actions -->
                        <div class="edd-download-actions">
                            <?php if ( $download['can_download'] && ! empty( $download['files'] ) ) : ?>
                                <button type="button" 
                                        class="edd-btn edd-btn-primary edd-btn-small edd-download-all" 
                                        data-payment-id="<?php echo esc_attr( $download['payment_id'] ); ?>"
                                        data-download-id="<?php echo esc_attr( $download['id'] ); ?>"
                                        data-nonce="<?php echo esc_attr( $nonces['download'] ); ?>"
                                        title="<?php esc_attr_e( 'Download all files as ZIP', 'edd-customer-dashboard-pro' ); ?>">
                                    📦 <?php esc_html_e( 'Download All', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ( $settings['enable_wishlist'] ) : ?>
                                <button type="button" 
                                        class="edd-btn edd-btn-secondary edd-btn-small edd-add-to-wishlist"
                                        data-download-id="<?php echo esc_attr( $download['id'] ); ?>"
                                        data-nonce="<?php echo esc_attr( $nonces['wishlist'] ); ?>"
                                        title="<?php esc_attr_e( 'Add to wishlist', 'edd-customer-dashboard-pro' ); ?>">
                                    ❤️ <?php esc_html_e( 'Wishlist', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ( $settings['enable_support'] ) : ?>
                                <button type="button" 
                                        class="edd-btn edd-btn-secondary edd-btn-small edd-contact-support"
                                        data-download-id="<?php echo esc_attr( $download['id'] ); ?>"
                                        data-payment-id="<?php echo esc_attr( $download['payment_id'] ); ?>"
                                        title="<?php esc_attr_e( 'Get support for this download', 'edd-customer-dashboard-pro' ); ?>">
                                    💬 <?php esc_html_e( 'Support', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Download History -->
        <?php if ( $settings['show_download_history'] ?? true ) : ?>
            <div class="edd-download-history-section">
                <h3 class="edd-subsection-title">
                    <?php esc_html_e( 'Download History', 'edd-customer-dashboard-pro' ); ?>
                    <?php if ( ! empty( $download_history ) ) : ?>
                        <span class="edd-count">(<?php echo esc_html( count( $download_history ) ); ?>)</span>
                    <?php endif; ?>
                </h3>
                
                <?php if ( ! empty( $download_history ) ) : ?>
                    <div class="edd-download-history-list">
                        <?php foreach ( array_slice( $download_history, 0, 10 ) as $history_item ) : ?>
                            <div class="edd-history-item">
                                <div class="edd-history-info">
                                    <div class="edd-history-icon">
                                        <?php echo esc_html( $history_item['file_type_icon'] ?? '📄' ); ?>
                                    </div>
                                    
                                    <div class="edd-history-details">
                                        <div class="edd-history-product">
                                            <strong><?php echo esc_html( $history_item['product_name'] ); ?></strong>
                                        </div>
                                        
                                        <div class="edd-history-file">
                                            <?php echo esc_html( $history_item['file_name'] ); ?>
                                            <?php if ( ! empty( $history_item['file_size'] ) ) : ?>
                                                <span class="edd-file-size">(<?php echo esc_html( $history_item['file_size'] ); ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="edd-history-meta">
                                            <span class="edd-download-date">
                                                📅 <?php echo esc_html( date_i18n( $settings['date_format'] . ' ' . get_option( 'time_format' ), strtotime( $history_item['date'] ) ) ); ?>
                                            </span>
                                            
                                            <span class="edd-download-ip">
                                                🌐 <?php echo esc_html( $history_item['ip_address'] ?? __( 'Unknown', 'edd-customer-dashboard-pro' ) ); ?>
                                            </span>
                                            
                                            <?php if ( ! empty( $history_item['user_agent'] ) ) : ?>
                                                <span class="edd-download-device" title="<?php echo esc_attr( $history_item['user_agent'] ); ?>">
                                                    💻 <?php echo esc_html( $history_item['device_type'] ?? __( 'Unknown Device', 'edd-customer-dashboard-pro' ) ); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="edd-history-actions">
                                    <?php if ( $history_item['can_redownload'] ?? true ) : ?>
                                        <button type="button" 
                                                class="edd-btn edd-btn-secondary edd-btn-small edd-redownload" 
                                                data-payment-id="<?php echo esc_attr( $history_item['payment_id'] ); ?>"
                                                data-download-id="<?php echo esc_attr( $history_item['download_id'] ); ?>"
                                                data-file-key="<?php echo esc_attr( $history_item['file_id'] ); ?>"
                                                data-nonce="<?php echo esc_attr( $nonces['download'] ); ?>"
                                                title="<?php esc_attr_e( 'Download this file again', 'edd-customer-dashboard-pro' ); ?>">
                                            🔄 <?php esc_html_e( 'Download Again', 'edd-customer-dashboard-pro' ); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ( count( $download_history ) > 10 ) : ?>
                        <div class="edd-load-more-wrapper">
                            <button type="button" 
                                    class="edd-btn edd-btn-secondary edd-load-more-history" 
                                    data-offset="10"
                                    data-nonce="<?php echo esc_attr( $nonces['ajax'] ); ?>">
                                <?php esc_html_e( 'Load More History', 'edd-customer-dashboard-pro' ); ?>
                                <span class="edd-loading-spinner" style="display: none;">🔄</span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                <?php else : ?>
                    <div class="edd-empty-history">
                        <p><?php esc_html_e( 'No download history available.', 'edd-customer-dashboard-pro' ); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Download Statistics -->
        <?php if ( $settings['show_download_stats'] ?? false ) : ?>
            <div class="edd-download-stats-section">
                <h3 class="edd-subsection-title"><?php esc_html_e( 'Download Statistics', 'edd-customer-dashboard-pro' ); ?></h3>
                
                <div class="edd-stats-grid">
                    <div class="edd-stat-item">
                        <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $stats['total_downloads'] ?? 0 ) ); ?></div>
                        <div class="edd-stat-label"><?php esc_html_e( 'Total Downloads', 'edd-customer-dashboard-pro' ); ?></div>
                    </div>
                    
                    <div class="edd-stat-item">
                        <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $stats['unique_downloads'] ?? 0 ) ); ?></div>
                        <div class="edd-stat-label"><?php esc_html_e( 'Unique Files', 'edd-customer-dashboard-pro' ); ?></div>
                    </div>
                    
                    <div class="edd-stat-item">
                        <div class="edd-stat-number"><?php echo esc_html( $stats['total_download_size'] ?? '0 MB' ); ?></div>
                        <div class="edd-stat-label"><?php esc_html_e( 'Total Downloaded', 'edd-customer-dashboard-pro' ); ?></div>
                    </div>
                    
                    <?php if ( ! empty( $stats['most_downloaded'] ) ) : ?>
                        <div class="edd-stat-item">
                            <div class="edd-stat-number"><?php echo esc_html( $stats['most_downloaded']['name'] ); ?></div>
                            <div class="edd-stat-label"><?php esc_html_e( 'Most Downloaded', 'edd-customer-dashboard-pro' ); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <!-- Empty State -->
        <div class="edd-empty-state">
            <div class="edd-empty-icon">⬇️</div>
            <h3><?php esc_html_e( 'No downloads available', 'edd-customer-dashboard-pro' ); ?></h3>
            <p><?php esc_html_e( 'You don\'t have any downloads available yet. Purchase products to access your downloads here.', 'edd-customer-dashboard-pro' ); ?></p>
            <div class="edd-empty-actions">
                <a href="<?php echo esc_url( $urls['shop'] ?? edd_get_checkout_uri() ); ?>" 
                   class="edd-btn edd-btn-primary">
                    🛒 <?php esc_html_e( 'Browse Products', 'edd-customer-dashboard-pro' ); ?>
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

<?php
// Hook for additional downloads content
do_action( 'edd_dashboard_pro_after_downloads_section', $available_downloads, $download_history, $settings );
?>
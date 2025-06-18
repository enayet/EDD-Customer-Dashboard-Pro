<?php
/**
 * Wishlist Section Template
 * 
 * Displays customer's saved products and wishlist management
 *
 * @package EDD_Customer_Dashboard_Pro
 * @template default
 * @section wishlist
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if wishlist is enabled
if ( ! ( $settings['enable_wishlist'] ?? true ) ) {
    ?>
    <div class="edd-content-section" id="edd-section-wishlist">
        <div class="edd-empty-state">
            <div class="edd-empty-icon">❤️</div>
            <h3><?php esc_html_e( 'Wishlist Disabled', 'edd-customer-dashboard-pro' ); ?></h3>
            <p><?php esc_html_e( 'The wishlist feature is currently disabled.', 'edd-customer-dashboard-pro' ); ?></p>
        </div>
    </div>
    <?php
    return;
}
?>

<!-- Wishlist Section -->
<div class="edd-content-section" id="edd-section-wishlist" role="tabpanel" aria-labelledby="edd-tab-wishlist">
    
    <div class="edd-section-header">
        <h2 class="edd-section-title"><?php esc_html_e( 'Your Wishlist', 'edd-customer-dashboard-pro' ); ?></h2>
        
        <?php if ( $settings['show_wishlist_filters'] ?? true ) : ?>
            <div class="edd-section-filters">
                <div class="edd-filter-group">
                    <label for="wishlist-category-filter"><?php esc_html_e( 'Category:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="wishlist-category-filter" class="edd-filter-select">
                        <option value=""><?php esc_html_e( 'All Categories', 'edd-customer-dashboard-pro' ); ?></option>
                        <?php
                        $wishlist_categories = get_terms( array(
                            'taxonomy' => 'download_category',
                            'hide_empty' => false
                        ) );
                        foreach ( $wishlist_categories as $category ) :
                        ?>
                            <option value="<?php echo esc_attr( $category->slug ); ?>">
                                <?php echo esc_html( $category->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="edd-filter-group">
                    <label for="wishlist-price-filter"><?php esc_html_e( 'Price Range:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="wishlist-price-filter" class="edd-filter-select">
                        <option value=""><?php esc_html_e( 'All Prices', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="free"><?php esc_html_e( 'Free', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="0-25"><?php esc_html_e( '$0 - $25', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="25-50"><?php esc_html_e( '$25 - $50', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="50-100"><?php esc_html_e( '$50 - $100', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="100+"><?php esc_html_e( '$100+', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
                
                <div class="edd-filter-group">
                    <label for="wishlist-sort-filter"><?php esc_html_e( 'Sort by:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="wishlist-sort-filter" class="edd-filter-select">
                        <option value="date_added"><?php esc_html_e( 'Date Added', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="title"><?php esc_html_e( 'Product Name', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="price_low"><?php esc_html_e( 'Price: Low to High', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="price_high"><?php esc_html_e( 'Price: High to Low', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
                
                <button type="button" class="edd-btn edd-btn-secondary edd-btn-small edd-apply-wishlist-filters">
                    <?php esc_html_e( 'Apply Filters', 'edd-customer-dashboard-pro' ); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( ! empty( $wishlist ) ) : ?>
        
        <!-- Wishlist Summary -->
        <div class="edd-wishlist-summary">
            <div class="edd-summary-item">
                <strong><?php esc_html_e( 'Items in Wishlist:', 'edd-customer-dashboard-pro' ); ?></strong>
                <?php echo esc_html( number_format_i18n( count( $wishlist ) ) ); ?>
            </div>
            
            <div class="edd-summary-item">
                <strong><?php esc_html_e( 'Total Value:', 'edd-customer-dashboard-pro' ); ?></strong>
                <?php 
                $total_value = array_sum( array_column( $wishlist, 'price' ) );
                echo esc_html( edd_dashboard_pro_format_price( $total_value ) );
                ?>
            </div>
            
            <?php if ( ! empty( $stats['wishlist_added_this_month'] ) ) : ?>
                <div class="edd-summary-item">
                    <strong><?php esc_html_e( 'Added This Month:', 'edd-customer-dashboard-pro' ); ?></strong>
                    <?php echo esc_html( number_format_i18n( $stats['wishlist_added_this_month'] ) ); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Wishlist Actions -->
        <div class="edd-wishlist-actions">
            <button type="button" 
                    class="edd-btn edd-btn-primary edd-add-all-to-cart"
                    data-nonce="<?php echo esc_attr( $nonces['wishlist'] ); ?>"
                    title="<?php esc_attr_e( 'Add all wishlist items to cart', 'edd-customer-dashboard-pro' ); ?>">
                🛒 <?php esc_html_e( 'Add All to Cart', 'edd-customer-dashboard-pro' ); ?>
            </button>
            
            <button type="button" 
                    class="edd-btn edd-btn-secondary edd-clear-wishlist"
                    data-nonce="<?php echo esc_attr( $nonces['wishlist'] ); ?>"
                    title="<?php esc_attr_e( 'Remove all items from wishlist', 'edd-customer-dashboard-pro' ); ?>">
                🗑️ <?php esc_html_e( 'Clear Wishlist', 'edd-customer-dashboard-pro' ); ?>
            </button>
            
            <?php if ( $settings['enable_wishlist_sharing'] ?? false ) : ?>
                <button type="button" 
                        class="edd-btn edd-btn-secondary edd-share-wishlist"
                        data-nonce="<?php echo esc_attr( $nonces['wishlist'] ); ?>"
                        title="<?php esc_attr_e( 'Share your wishlist', 'edd-customer-dashboard-pro' ); ?>">
                    📤 <?php esc_html_e( 'Share Wishlist', 'edd-customer-dashboard-pro' ); ?>
                </button>
            <?php endif; ?>
        </div>

        <!-- Wishlist Grid -->
        <div class="edd-wishlist-grid">
            <?php foreach ( $wishlist as $item ) : ?>
                <div class="edd-wishlist-item" data-download-id="<?php echo esc_attr( $item['id'] ); ?>">
                    
                    <!-- Product Image -->
                    <div class="edd-wishlist-image-wrapper">
                        <?php if ( $item['thumbnail'] ) : ?>
                            <div class="edd-wishlist-image">
                                <img src="<?php echo esc_url( $item['thumbnail'] ); ?>" 
                                     alt="<?php echo esc_attr( $item['title'] ); ?>"
                                     loading="lazy">
                            </div>
                        <?php else : ?>
                            <div class="edd-product-image-placeholder">
                                🎁
                            </div>
                        <?php endif; ?>
                        
                        <!-- Quick Action Overlay -->
                        <div class="edd-wishlist-overlay">
                            <button type="button" 
                                    class="edd-btn edd-btn-primary edd-btn-small edd-quick-add-to-cart"
                                    data-download-id="<?php echo esc_attr( $item['id'] ); ?>"
                                    title="<?php esc_attr_e( 'Quick add to cart', 'edd-customer-dashboard-pro' ); ?>">
                                🛒
                            </button>
                            
                            <button type="button" 
                                    class="edd-btn edd-btn-secondary edd-btn-small edd-quick-remove"
                                    data-download-id="<?php echo esc_attr( $item['id'] ); ?>"
                                    data-nonce="<?php echo esc_attr( $nonces['wishlist'] ); ?>"
                                    title="<?php esc_attr_e( 'Remove from wishlist', 'edd-customer-dashboard-pro' ); ?>">
                                ❌
                            </button>
                        </div>
                        
                        <!-- Sale Badge -->
                        <?php if ( ! empty( $item['on_sale'] ) ) : ?>
                            <div class="edd-sale-badge">
                                🔥 <?php esc_html_e( 'Sale', 'edd-customer-dashboard-pro' ); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- New Badge -->
                        <?php if ( ! empty( $item['is_new'] ) ) : ?>
                            <div class="edd-new-badge">
                                ✨ <?php esc_html_e( 'New', 'edd-customer-dashboard-pro' ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Product Details -->
                    <div class="edd-wishlist-details">
                        <h3 class="edd-wishlist-title">
                            <a href="<?php echo esc_url( $item['permalink'] ); ?>" target="_blank">
                                <?php echo esc_html( $item['title'] ); ?>
                            </a>
                        </h3>
                        
                        <!-- Product Meta -->
                        <div class="edd-wishlist-meta">
                            <?php if ( ! empty( $item['category'] ) ) : ?>
                                <span class="edd-product-category">
                                    🏷️ <?php echo esc_html( $item['category'] ); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ( ! empty( $item['rating'] ) ) : ?>
                                <span class="edd-product-rating">
                                    ⭐ <?php echo esc_html( number_format( $item['rating'], 1 ) ); ?>
                                    <?php if ( ! empty( $item['review_count'] ) ) : ?>
                                        (<?php echo esc_html( number_format_i18n( $item['review_count'] ) ); ?>)
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                            
                            <span class="edd-date-added">
                                📅 <?php 
                                printf(
                                    /* translators: %s: Date added to wishlist */
                                    esc_html__( 'Added: %s', 'edd-customer-dashboard-pro' ),
                                    esc_html( date_i18n( $settings['date_format'], strtotime( $item['date_added'] ) ) )
                                );
                                ?>
                            </span>
                        </div>
                        
                        <!-- Product Description -->
                        <?php if ( ! empty( $item['excerpt'] ) && ( $settings['show_wishlist_excerpts'] ?? true ) ) : ?>
                            <div class="edd-wishlist-excerpt">
                                <?php echo wp_kses_post( wp_trim_words( $item['excerpt'], 20 ) ); ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Price Display -->
                        <div class="edd-wishlist-price-wrapper">
                            <?php if ( $item['price'] > 0 ) : ?>
                                <div class="edd-wishlist-price">
                                    <?php if ( ! empty( $item['original_price'] ) && $item['original_price'] > $item['price'] ) : ?>
                                        <span class="edd-original-price">
                                            <?php echo esc_html( edd_dashboard_pro_format_price( $item['original_price'] ) ); ?>
                                        </span>
                                        <span class="edd-sale-price">
                                            <?php echo esc_html( edd_dashboard_pro_format_price( $item['price'] ) ); ?>
                                        </span>
                                        <span class="edd-discount-percent">
                                            <?php 
                                            $discount = ( ( $item['original_price'] - $item['price'] ) / $item['original_price'] ) * 100;
                                            printf( 
                                                /* translators: %d: Discount percentage */
                                                esc_html__( '-%d%%', 'edd-customer-dashboard-pro' ), 
                                                round( $discount ) 
                                            );
                                            ?>
                                        </span>
                                    <?php else : ?>
                                        <span class="edd-regular-price">
                                            <?php echo esc_html( edd_dashboard_pro_format_price( $item['price'] ) ); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php else : ?>
                                <div class="edd-wishlist-price edd-free-price">
                                    <?php esc_html_e( 'Free', 'edd-customer-dashboard-pro' ); ?>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Price Alert -->
                            <?php if ( $settings['enable_price_alerts'] ?? false ) : ?>
                                <button type="button" 
                                        class="edd-btn edd-btn-secondary edd-btn-small edd-price-alert"
                                        data-download-id="<?php echo esc_attr( $item['id'] ); ?>"
                                        data-current-price="<?php echo esc_attr( $item['price'] ); ?>"
                                        title="<?php esc_attr_e( 'Get notified of price changes', 'edd-customer-dashboard-pro' ); ?>">
                                    🔔 <?php esc_html_e( 'Price Alert', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Availability Status -->
                        <div class="edd-availability-status">
                            <?php if ( $item['is_available'] ) : ?>
                                <span class="edd-in-stock">
                                    ✅ <?php esc_html_e( 'Available', 'edd-customer-dashboard-pro' ); ?>
                                </span>
                            <?php else : ?>
                                <span class="edd-out-of-stock">
                                    ❌ <?php esc_html_e( 'Unavailable', 'edd-customer-dashboard-pro' ); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Actions -->
                        <div class="edd-wishlist-actions">
                            <?php if ( $item['is_available'] ) : ?>
                                <a href="<?php echo esc_url( add_query_arg( array( 'edd_action' => 'add_to_cart', 'download_id' => $item['id'] ), edd_get_checkout_uri() ) ); ?>" 
                                   class="edd-btn edd-btn-primary edd-add-to-cart">
                                    🛒 <?php esc_html_e( 'Add to Cart', 'edd-customer-dashboard-pro' ); ?>
                                </a>
                            <?php else : ?>
                                <button type="button" class="edd-btn edd-btn-disabled" disabled>
                                    🚫 <?php esc_html_e( 'Unavailable', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                            <?php endif; ?>
                            
                            <a href="<?php echo esc_url( $item['permalink'] ); ?>" 
                               class="edd-btn edd-btn-secondary" 
                               target="_blank">
                                👁️ <?php esc_html_e( 'View Details', 'edd-customer-dashboard-pro' ); ?>
                            </a>
                            
                            <button type="button" 
                                    class="edd-btn edd-btn-secondary edd-remove-from-wishlist"
                                    data-download-id="<?php echo esc_attr( $item['id'] ); ?>"
                                    data-nonce="<?php echo esc_attr( $nonces['wishlist'] ); ?>"
                                    title="<?php esc_attr_e( 'Remove from wishlist', 'edd-customer-dashboard-pro' ); ?>">
                                ❌ <?php esc_html_e( 'Remove', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        </div>
                        
                        <!-- Stock Alert -->
                        <?php if ( ! $item['is_available'] && ( $settings['enable_stock_alerts'] ?? false ) ) : ?>
                            <div class="edd-stock-alert">
                                <button type="button" 
                                        class="edd-btn edd-btn-secondary edd-btn-small edd-notify-when-available"
                                        data-download-id="<?php echo esc_attr( $item['id'] ); ?>"
                                        data-nonce="<?php echo esc_attr( $nonces['wishlist'] ); ?>">
                                    🔔 <?php esc_html_e( 'Notify When Available', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Load More -->
        <?php if ( count( $wishlist ) >= ( $settings['wishlist_items_per_page'] ?? 12 ) ) : ?>
            <div class="edd-load-more-wrapper">
                <button type="button" 
                        class="edd-btn edd-btn-secondary edd-load-more-wishlist" 
                        data-offset="<?php echo esc_attr( count( $wishlist ) ); ?>"
                        data-nonce="<?php echo esc_attr( $nonces['ajax'] ); ?>">
                    <?php esc_html_e( 'Load More Items', 'edd-customer-dashboard-pro' ); ?>
                    <span class="edd-loading-spinner" style="display: none;">🔄</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Wishlist Statistics -->
        <?php if ( $settings['show_wishlist_stats'] ?? false ) : ?>
            <div class="edd-wishlist-stats">
                <h3 class="edd-stats-title"><?php esc_html_e( 'Wishlist Statistics', 'edd-customer-dashboard-pro' ); ?></h3>
                
                <div class="edd-stats-grid">
                    <div class="edd-stat-item">
                        <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( count( $wishlist ) ) ); ?></div>
                        <div class="edd-stat-label"><?php esc_html_e( 'Total Items', 'edd-customer-dashboard-pro' ); ?></div>
                    </div>
                    
                    <div class="edd-stat-item">
                        <div class="edd-stat-number"><?php echo esc_html( edd_dashboard_pro_format_price( $total_value ) ); ?></div>
                        <div class="edd-stat-label"><?php esc_html_e( 'Total Value', 'edd-customer-dashboard-pro' ); ?></div>
                    </div>
                    
                    <div class="edd-stat-item">
                        <div class="edd-stat-number">
                            <?php 
                            $avg_price = count( $wishlist ) > 0 ? $total_value / count( $wishlist ) : 0;
                            echo esc_html( edd_dashboard_pro_format_price( $avg_price ) );
                            ?>
                        </div>
                        <div class="edd-stat-label"><?php esc_html_e( 'Average Price', 'edd-customer-dashboard-pro' ); ?></div>
                    </div>
                    
                    <div class="edd-stat-item">
                        <div class="edd-stat-number">
                            <?php 
                            $free_items = count( array_filter( $wishlist, function( $item ) {
                                return $item['price'] == 0;
                            } ) );
                            echo esc_html( number_format_i18n( $free_items ) );
                            ?>
                        </div>
                        <div class="edd-stat-label"><?php esc_html_e( 'Free Items', 'edd-customer-dashboard-pro' ); ?></div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <!-- Empty Wishlist State -->
        <div class="edd-empty-state">
            <div class="edd-empty-icon">❤️</div>
            <h3><?php esc_html_e( 'Your wishlist is empty', 'edd-customer-dashboard-pro' ); ?></h3>
            <p><?php esc_html_e( 'Save products you love to your wishlist and purchase them later!', 'edd-customer-dashboard-pro' ); ?></p>
            
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
            
            <!-- Wishlist Tips -->
            <div class="edd-wishlist-tips">
                <h4><?php esc_html_e( 'How to use your wishlist:', 'edd-customer-dashboard-pro' ); ?></h4>
                <ul>
                    <li><?php esc_html_e( 'Click the heart icon ❤️ on any product page', 'edd-customer-dashboard-pro' ); ?></li>
                    <li><?php esc_html_e( 'Save items for later purchase', 'edd-customer-dashboard-pro' ); ?></li>
                    <li><?php esc_html_e( 'Get notified of price changes (if enabled)', 'edd-customer-dashboard-pro' ); ?></li>
                    <li><?php esc_html_e( 'Add multiple items to cart at once', 'edd-customer-dashboard-pro' ); ?></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Wishlist Share Modal -->
<?php if ( $settings['enable_wishlist_sharing'] ?? false ) : ?>
<div id="edd-wishlist-share-modal" class="edd-modal" style="display: none;">
    <div class="edd-modal-content">
        <div class="edd-modal-header">
            <h3><?php esc_html_e( 'Share Your Wishlist', 'edd-customer-dashboard-pro' ); ?></h3>
            <button type="button" class="edd-modal-close">&times;</button>
        </div>
        <div class="edd-modal-body">
            <div class="edd-share-options">
                <div class="edd-share-link">
                    <label for="wishlist-share-url"><?php esc_html_e( 'Shareable Link:', 'edd-customer-dashboard-pro' ); ?></label>
                    <div class="edd-input-group">
                        <input type="url" 
                               id="wishlist-share-url" 
                               class="edd-form-control" 
                               readonly
                               value="">
                        <button type="button" 
                                class="edd-btn edd-btn-secondary edd-copy-share-link">
                            📋 <?php esc_html_e( 'Copy', 'edd-customer-dashboard-pro' ); ?>
                        </button>
                    </div>
                </div>
                
                <div class="edd-social-share">
                    <h4><?php esc_html_e( 'Share on Social Media:', 'edd-customer-dashboard-pro' ); ?></h4>
                    <div class="edd-social-buttons">
                        <button type="button" class="edd-btn edd-btn-social edd-share-facebook">
                            📘 <?php esc_html_e( 'Facebook', 'edd-customer-dashboard-pro' ); ?>
                        </button>
                        <button type="button" class="edd-btn edd-btn-social edd-share-twitter">
                            🐦 <?php esc_html_e( 'Twitter', 'edd-customer-dashboard-pro' ); ?>
                        </button>
                        <button type="button" class="edd-btn edd-btn-social edd-share-email">
                            📧 <?php esc_html_e( 'Email', 'edd-customer-dashboard-pro' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="edd-modal-footer">
            <button type="button" class="edd-btn edd-btn-secondary edd-modal-close">
                <?php esc_html_e( 'Close', 'edd-customer-dashboard-pro' ); ?>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Price Alert Modal -->
<?php if ( $settings['enable_price_alerts'] ?? false ) : ?>
<div id="edd-price-alert-modal" class="edd-modal" style="display: none;">
    <div class="edd-modal-content">
        <div class="edd-modal-header">
            <h3><?php esc_html_e( 'Set Price Alert', 'edd-customer-dashboard-pro' ); ?></h3>
            <button type="button" class="edd-modal-close">&times;</button>
        </div>
        <div class="edd-modal-body">
            <form id="edd-price-alert-form" class="edd-price-alert-form">
                <div class="edd-form-group">
                    <label for="alert-threshold"><?php esc_html_e( 'Notify me when price drops below:', 'edd-customer-dashboard-pro' ); ?></label>
                    <input type="number" 
                           id="alert-threshold" 
                           name="threshold" 
                           class="edd-form-control" 
                           step="0.01" 
                           min="0"
                           required>
                </div>
                
                <div class="edd-form-group">
                    <label for="alert-email"><?php esc_html_e( 'Email address:', 'edd-customer-dashboard-pro' ); ?></label>
                    <input type="email" 
                           id="alert-email" 
                           name="email" 
                           class="edd-form-control" 
                           value="<?php echo esc_attr( $current_user->user_email ); ?>"
                           required>
                </div>
                
                <input type="hidden" name="download_id" value="">
                <input type="hidden" name="nonce" value="<?php echo esc_attr( $nonces['wishlist'] ); ?>">
            </form>
        </div>
        <div class="edd-modal-footer">
            <button type="button" class="edd-btn edd-btn-secondary edd-modal-close">
                <?php esc_html_e( 'Cancel', 'edd-customer-dashboard-pro' ); ?>
            </button>
            <button type="submit" form="edd-price-alert-form" class="edd-btn edd-btn-primary">
                <?php esc_html_e( 'Set Alert', 'edd-customer-dashboard-pro' ); ?>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// Hook for additional wishlist content
do_action( 'edd_dashboard_pro_after_wishlist_section', $wishlist, $settings );
?>
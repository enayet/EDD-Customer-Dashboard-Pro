<?php
/**
 * Analytics Section Template
 * 
 * Displays customer analytics, charts, and purchase insights
 *
 * @package EDD_Customer_Dashboard_Pro
 * @template default
 * @section analytics
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if analytics is enabled
if ( ! ( $settings['enable_analytics'] ?? true ) ) {
    ?>
    <div class="edd-content-section" id="edd-section-analytics">
        <div class="edd-empty-state">
            <div class="edd-empty-icon">📊</div>
            <h3><?php esc_html_e( 'Analytics Disabled', 'edd-customer-dashboard-pro' ); ?></h3>
            <p><?php esc_html_e( 'Analytics features are currently disabled.', 'edd-customer-dashboard-pro' ); ?></p>
        </div>
    </div>
    <?php
    return;
}
?>

<!-- Analytics Section -->
<div class="edd-content-section" id="edd-section-analytics" role="tabpanel" aria-labelledby="edd-tab-analytics">
    
    <div class="edd-section-header">
        <h2 class="edd-section-title"><?php esc_html_e( 'Purchase Analytics', 'edd-customer-dashboard-pro' ); ?></h2>
        
        <?php if ( $settings['show_analytics_filters'] ?? true ) : ?>
            <div class="edd-section-filters">
                <div class="edd-filter-group">
                    <label for="analytics-period-filter"><?php esc_html_e( 'Time Period:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="analytics-period-filter" class="edd-filter-select">
                        <option value="all_time"><?php esc_html_e( 'All Time', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="last_30_days"><?php esc_html_e( 'Last 30 Days', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="last_90_days"><?php esc_html_e( 'Last 90 Days', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="last_year" selected><?php esc_html_e( 'Last Year', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="this_year"><?php esc_html_e( 'This Year', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
                
                <div class="edd-filter-group">
                    <label for="analytics-chart-type"><?php esc_html_e( 'Chart Type:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select id="analytics-chart-type" class="edd-filter-select">
                        <option value="line"><?php esc_html_e( 'Line Chart', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="bar"><?php esc_html_e( 'Bar Chart', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="area"><?php esc_html_e( 'Area Chart', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
                
                <button type="button" class="edd-btn edd-btn-secondary edd-btn-small edd-update-analytics">
                    🔄 <?php esc_html_e( 'Update Charts', 'edd-customer-dashboard-pro' ); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( ! empty( $analytics ) ) : ?>
        
        <!-- Analytics Overview -->
        <div class="edd-analytics-overview">
            <div class="edd-analytics-grid">
                
                <!-- Total Spent -->
                <div class="edd-analytics-card edd-total-spent">
                    <div class="edd-analytics-icon">💰</div>
                    <div class="edd-analytics-content">
                        <div class="edd-analytics-number">
                            <?php echo esc_html( edd_dashboard_pro_format_price( $analytics['total_spent'] ?? 0 ) ); ?>
                        </div>
                        <div class="edd-analytics-label"><?php esc_html_e( 'Total Spent', 'edd-customer-dashboard-pro' ); ?></div>
                        <?php if ( isset( $analytics['spent_change'] ) ) : ?>
                            <div class="edd-analytics-change <?php echo $analytics['spent_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php 
                                echo $analytics['spent_change'] >= 0 ? '↗️' : '↘️';
                                printf( 
                                    /* translators: %s: Change percentage */
                                    esc_html__( '%s%% vs last period', 'edd-customer-dashboard-pro' ), 
                                    esc_html( abs( $analytics['spent_change'] ) )
                                );
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Average Order Value -->
                <div class="edd-analytics-card edd-avg-order">
                    <div class="edd-analytics-icon">📈</div>
                    <div class="edd-analytics-content">
                        <div class="edd-analytics-number">
                            <?php echo esc_html( edd_dashboard_pro_format_price( $analytics['avg_order_value'] ?? 0 ) ); ?>
                        </div>
                        <div class="edd-analytics-label"><?php esc_html_e( 'Avg Order Value', 'edd-customer-dashboard-pro' ); ?></div>
                        <?php if ( isset( $analytics['aov_change'] ) ) : ?>
                            <div class="edd-analytics-change <?php echo $analytics['aov_change'] >= 0 ? 'positive' : 'negative'; ?>">
                                <?php 
                                echo $analytics['aov_change'] >= 0 ? '↗️' : '↘️';
                                printf( 
                                    /* translators: %s: Change percentage */
                                    esc_html__( '%s%% vs last period', 'edd-customer-dashboard-pro' ), 
                                    esc_html( abs( $analytics['aov_change'] ) )
                                );
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Downloads Per Purchase -->
                <div class="edd-analytics-card edd-downloads-ratio">
                    <div class="edd-analytics-icon">⬇️</div>
                    <div class="edd-analytics-content">
                        <div class="edd-analytics-number">
                            <?php echo esc_html( number_format( $analytics['downloads_per_purchase'] ?? 0, 1 ) ); ?>
                        </div>
                        <div class="edd-analytics-label"><?php esc_html_e( 'Downloads/Purchase', 'edd-customer-dashboard-pro' ); ?></div>
                        <div class="edd-analytics-meta">
                            <?php 
                            printf(
                                /* translators: %1$d: Total downloads, %2$d: Total purchases */
                                esc_html__( '%1$d downloads from %2$d purchases', 'edd-customer-dashboard-pro' ),
                                esc_html( $analytics['total_downloads'] ?? 0 ),
                                esc_html( $analytics['total_purchases'] ?? 0 )
                            );
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Customer Lifetime -->
                <div class="edd-analytics-card edd-customer-lifetime">
                    <div class="edd-analytics-icon">📅</div>
                    <div class="edd-analytics-content">
                        <div class="edd-analytics-number">
                            <?php echo esc_html( number_format_i18n( $analytics['customer_since_days'] ?? 0 ) ); ?>
                        </div>
                        <div class="edd-analytics-label"><?php esc_html_e( 'Days as Customer', 'edd-customer-dashboard-pro' ); ?></div>
                        <div class="edd-analytics-meta">
                            <?php 
                            if ( isset( $analytics['first_purchase_date'] ) ) {
                                printf(
                                    /* translators: %s: First purchase date */
                                    esc_html__( 'Since %s', 'edd-customer-dashboard-pro' ),
                                    esc_html( date_i18n( $settings['date_format'], strtotime( $analytics['first_purchase_date'] ) ) )
                                );
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Trends Chart -->
        <div class="edd-analytics-chart-section">
            <h3 class="edd-chart-title"><?php esc_html_e( 'Purchase Trends', 'edd-customer-dashboard-pro' ); ?></h3>
            <div class="edd-chart-container">
                <canvas id="edd-purchase-trends-chart" width="400" height="200"></canvas>
            </div>
            <div class="edd-chart-legend">
                <div class="edd-legend-item">
                    <span class="edd-legend-color edd-purchases-color"></span>
                    <span class="edd-legend-label"><?php esc_html_e( 'Purchases', 'edd-customer-dashboard-pro' ); ?></span>
                </div>
                <div class="edd-legend-item">
                    <span class="edd-legend-color edd-spending-color"></span>
                    <span class="edd-legend-label"><?php esc_html_e( 'Spending', 'edd-customer-dashboard-pro' ); ?></span>
                </div>
            </div>
        </div>

        <!-- Category Breakdown -->
        <?php if ( ! empty( $analytics['category_breakdown'] ) ) : ?>
            <div class="edd-analytics-categories">
                <h3 class="edd-categories-title"><?php esc_html_e( 'Purchases by Category', 'edd-customer-dashboard-pro' ); ?></h3>
                
                <div class="edd-category-chart-container">
                    <canvas id="edd-category-chart" width="300" height="300"></canvas>
                </div>
                
                <div class="edd-category-breakdown">
                    <?php foreach ( $analytics['category_breakdown'] as $category => $data ) : ?>
                        <div class="edd-category-item">
                            <div class="edd-category-info">
                                <span class="edd-category-name"><?php echo esc_html( $category ); ?></span>
                                <span class="edd-category-count">
                                    <?php 
                                    printf(
                                        /* translators: %d: Number of purchases */
                                        esc_html__( '%d purchases', 'edd-customer-dashboard-pro' ),
                                        esc_html( $data['count'] )
                                    );
                                    ?>
                                </span>
                            </div>
                            <div class="edd-category-amount">
                                <?php echo esc_html( edd_dashboard_pro_format_price( $data['total'] ) ); ?>
                            </div>
                            <div class="edd-category-percentage">
                                <?php echo esc_html( number_format( $data['percentage'], 1 ) ); ?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Monthly Activity -->
        <div class="edd-analytics-monthly">
            <h3 class="edd-monthly-title"><?php esc_html_e( 'Monthly Activity', 'edd-customer-dashboard-pro' ); ?></h3>
            
            <div class="edd-monthly-grid">
                <?php if ( ! empty( $analytics['monthly_data'] ) ) : ?>
                    <?php foreach ( array_slice( $analytics['monthly_data'], -12 ) as $month => $data ) : ?>
                        <div class="edd-monthly-item">
                            <div class="edd-month-header">
                                <span class="edd-month-name"><?php echo esc_html( date_i18n( 'M Y', strtotime( $month . '-01' ) ) ); ?></span>
                            </div>
                            <div class="edd-month-stats">
                                <div class="edd-month-purchases">
                                    <strong><?php echo esc_html( number_format_i18n( $data['purchases'] ?? 0 ) ); ?></strong>
                                    <span><?php esc_html_e( 'purchases', 'edd-customer-dashboard-pro' ); ?></span>
                                </div>
                                <div class="edd-month-spending">
                                    <strong><?php echo esc_html( edd_dashboard_pro_format_price( $data['total'] ?? 0 ) ); ?></strong>
                                    <span><?php esc_html_e( 'spent', 'edd-customer-dashboard-pro' ); ?></span>
                                </div>
                                <div class="edd-month-downloads">
                                    <strong><?php echo esc_html( number_format_i18n( $data['downloads'] ?? 0 ) ); ?></strong>
                                    <span><?php esc_html_e( 'downloads', 'edd-customer-dashboard-pro' ); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Products -->
        <?php if ( ! empty( $analytics['top_products'] ) ) : ?>
            <div class="edd-analytics-top-products">
                <h3 class="edd-top-products-title"><?php esc_html_e( 'Your Most Purchased Products', 'edd-customer-dashboard-pro' ); ?></h3>
                
                <div class="edd-top-products-list">
                    <?php foreach ( array_slice( $analytics['top_products'], 0, 5 ) as $index => $product ) : ?>
                        <div class="edd-top-product-item">
                            <div class="edd-product-rank">
                                <span class="edd-rank-number"><?php echo esc_html( $index + 1 ); ?></span>
                            </div>
                            
                            <div class="edd-product-info">
                                <div class="edd-product-thumbnail">
                                    <?php if ( ! empty( $product['thumbnail'] ) ) : ?>
                                        <img src="<?php echo esc_url( $product['thumbnail'] ); ?>" 
                                             alt="<?php echo esc_attr( $product['name'] ); ?>"
                                             loading="lazy">
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
                                    
                                    <div class="edd-product-stats">
                                        <span class="edd-purchase-count">
                                            <?php 
                                            printf(
                                                /* translators: %d: Number of times purchased */
                                                esc_html__( 'Purchased %d times', 'edd-customer-dashboard-pro' ),
                                                esc_html( $product['purchase_count'] )
                                            );
                                            ?>
                                        </span>
                                        
                                        <span class="edd-total-spent">
                                            <?php 
                                            printf(
                                                /* translators: %s: Total amount spent */
                                                esc_html__( 'Total: %s', 'edd-customer-dashboard-pro' ),
                                                esc_html( edd_dashboard_pro_format_price( $product['total_spent'] ) )
                                            );
                                            ?>
                                        </span>
                                        
                                        <span class="edd-last-purchased">
                                            <?php 
                                            printf(
                                                /* translators: %s: Last purchase date */
                                                esc_html__( 'Last: %s', 'edd-customer-dashboard-pro' ),
                                                esc_html( date_i18n( $settings['date_format'], strtotime( $product['last_purchased'] ) ) )
                                            );
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="edd-product-actions">
                                <?php if ( $settings['enable_wishlist'] ) : ?>
                                    <button type="button" 
                                            class="edd-btn edd-btn-secondary edd-btn-small edd-add-to-wishlist"
                                            data-download-id="<?php echo esc_attr( $product['id'] ); ?>"
                                            data-nonce="<?php echo esc_attr( $nonces['wishlist'] ); ?>">
                                        ❤️
                                    </button>
                                <?php endif; ?>
                                
                                <a href="<?php echo esc_url( add_query_arg( array( 'edd_action' => 'add_to_cart', 'download_id' => $product['id'] ), edd_get_checkout_uri() ) ); ?>" 
                                   class="edd-btn edd-btn-primary edd-btn-small">
                                    🔄 <?php esc_html_e( 'Buy Again', 'edd-customer-dashboard-pro' ); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Download Activity -->
        <div class="edd-analytics-downloads">
            <h3 class="edd-downloads-title"><?php esc_html_e( 'Download Activity', 'edd-customer-dashboard-pro' ); ?></h3>
            
            <div class="edd-download-stats-grid">
                <div class="edd-download-stat">
                    <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $analytics['total_downloads'] ?? 0 ) ); ?></div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Total Downloads', 'edd-customer-dashboard-pro' ); ?></div>
                </div>
                
                <div class="edd-download-stat">
                    <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $analytics['unique_files'] ?? 0 ) ); ?></div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Unique Files', 'edd-customer-dashboard-pro' ); ?></div>
                </div>
                
                <div class="edd-download-stat">
                    <div class="edd-stat-number"><?php echo esc_html( $analytics['total_download_size'] ?? '0 MB' ); ?></div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Data Downloaded', 'edd-customer-dashboard-pro' ); ?></div>
                </div>
                
                <div class="edd-download-stat">
                    <div class="edd-stat-number">
                        <?php 
                        if ( ! empty( $analytics['last_download_date'] ) ) {
                            echo esc_html( human_time_diff( strtotime( $analytics['last_download_date'] ), current_time( 'timestamp' ) ) );
                        } else {
                            esc_html_e( 'Never', 'edd-customer-dashboard-pro' );
                        }
                        ?>
                    </div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Last Download', 'edd-customer-dashboard-pro' ); ?></div>
                </div>
            </div>
            
            <?php if ( ! empty( $analytics['download_frequency'] ) ) : ?>
                <div class="edd-download-frequency">
                    <h4><?php esc_html_e( 'Download Frequency', 'edd-customer-dashboard-pro' ); ?></h4>
                    <div class="edd-frequency-chart-container">
                        <canvas id="edd-download-frequency-chart" width="400" height="200"></canvas>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Customer Insights -->
        <div class="edd-analytics-insights">
            <h3 class="edd-insights-title"><?php esc_html_e( 'Your Shopping Insights', 'edd-customer-dashboard-pro' ); ?></h3>
            
            <div class="edd-insights-grid">
                <div class="edd-insight-card">
                    <div class="edd-insight-icon">🛒</div>
                    <div class="edd-insight-content">
                        <h4><?php esc_html_e( 'Shopping Behavior', 'edd-customer-dashboard-pro' ); ?></h4>
                        <p>
                            <?php 
                            if ( ( $analytics['avg_order_value'] ?? 0 ) > 50 ) {
                                esc_html_e( 'You prefer premium products and make thoughtful purchases.', 'edd-customer-dashboard-pro' );
                            } elseif ( ( $analytics['total_purchases'] ?? 0 ) > 10 ) {
                                esc_html_e( 'You\'re a frequent shopper who loves discovering new products.', 'edd-customer-dashboard-pro' );
                            } else {
                                esc_html_e( 'You make selective purchases and choose quality over quantity.', 'edd-customer-dashboard-pro' );
                            }
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="edd-insight-card">
                    <div class="edd-insight-icon">📅</div>
                    <div class="edd-insight-content">
                        <h4><?php esc_html_e( 'Purchase Pattern', 'edd-customer-dashboard-pro' ); ?></h4>
                        <p>
                            <?php 
                            $days_between_purchases = $analytics['avg_days_between_purchases'] ?? 0;
                            if ( $days_between_purchases < 30 ) {
                                esc_html_e( 'You\'re an active customer with regular purchases.', 'edd-customer-dashboard-pro' );
                            } elseif ( $days_between_purchases < 90 ) {
                                esc_html_e( 'You make seasonal purchases and take time to evaluate products.', 'edd-customer-dashboard-pro' );
                            } else {
                                esc_html_e( 'You make occasional, well-considered purchases.', 'edd-customer-dashboard-pro' );
                            }
                            ?>
                        </p>
                    </div>
                </div>
                
                <div class="edd-insight-card">
                    <div class="edd-insight-icon">⬇️</div>
                    <div class="edd-insight-content">
                        <h4><?php esc_html_e( 'Download Habits', 'edd-customer-dashboard-pro' ); ?></h4>
                        <p>
                            <?php 
                            $download_ratio = $analytics['downloads_per_purchase'] ?? 0;
                            if ( $download_ratio > 3 ) {
                                esc_html_e( 'You actively use your purchases and download files frequently.', 'edd-customer-dashboard-pro' );
                            } elseif ( $download_ratio > 1 ) {
                                esc_html_e( 'You download your files regularly after purchase.', 'edd-customer-dashboard-pro' );
                            } else {
                                esc_html_e( 'You tend to download files once and keep them organized.', 'edd-customer-dashboard-pro' );
                            }
                            ?>
                        </p>
                    </div>
                </div>
                
                <?php if ( ! empty( $analytics['favorite_category'] ) ) : ?>
                    <div class="edd-insight-card">
                        <div class="edd-insight-icon">🏷️</div>
                        <div class="edd-insight-content">
                            <h4><?php esc_html_e( 'Favorite Category', 'edd-customer-dashboard-pro' ); ?></h4>
                            <p>
                                <?php 
                                printf(
                                    /* translators: %s: Favorite product category */
                                    esc_html__( 'You love %s products and have great taste!', 'edd-customer-dashboard-pro' ),
                                    esc_html( $analytics['favorite_category'] )
                                );
                                ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Export Options -->
        <?php if ( $settings['enable_data_export'] ?? false ) : ?>
            <div class="edd-analytics-export">
                <h3 class="edd-export-title"><?php esc_html_e( 'Export Your Data', 'edd-customer-dashboard-pro' ); ?></h3>
                
                <div class="edd-export-options">
                    <button type="button" 
                            class="edd-btn edd-btn-secondary edd-export-purchases"
                            data-nonce="<?php echo esc_attr( $nonces['ajax'] ); ?>">
                        📊 <?php esc_html_e( 'Export Purchase Data (CSV)', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                    
                    <button type="button" 
                            class="edd-btn edd-btn-secondary edd-export-analytics"
                            data-nonce="<?php echo esc_attr( $nonces['ajax'] ); ?>">
                        📈 <?php esc_html_e( 'Export Analytics Report (PDF)', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                    
                    <button type="button" 
                            class="edd-btn edd-btn-secondary edd-export-downloads"
                            data-nonce="<?php echo esc_attr( $nonces['ajax'] ); ?>">
                        ⬇️ <?php esc_html_e( 'Export Download History (CSV)', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <!-- Empty Analytics State -->
        <div class="edd-empty-state">
            <div class="edd-empty-icon">📊</div>
            <h3><?php esc_html_e( 'No analytics data available', 'edd-customer-dashboard-pro' ); ?></h3>
            <p><?php esc_html_e( 'Make some purchases to see your detailed analytics and insights here!', 'edd-customer-dashboard-pro' ); ?></p>
            
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
            
            <!-- Analytics Preview -->
            <div class="edd-analytics-preview">
                <h4><?php esc_html_e( 'What you\'ll see here:', 'edd-customer-dashboard-pro' ); ?></h4>
                <ul>
                    <li>📈 <?php esc_html_e( 'Purchase trends and spending patterns', 'edd-customer-dashboard-pro' ); ?></li>
                    <li>📊 <?php esc_html_e( 'Category breakdown and preferences', 'edd-customer-dashboard-pro' ); ?></li>
                    <li>⬇️ <?php esc_html_e( 'Download activity and usage stats', 'edd-customer-dashboard-pro' ); ?></li>
                    <li>🎯 <?php esc_html_e( 'Personalized shopping insights', 'edd-customer-dashboard-pro' ); ?></li>
                    <li>📋 <?php esc_html_e( 'Export options for your data', 'edd-customer-dashboard-pro' ); ?></li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Chart Data for JavaScript -->
<script type="text/javascript">
window.eddAnalyticsData = {
    purchaseTrends: <?php echo wp_json_encode( $analytics['purchase_trends'] ?? array() ); ?>,
    categoryBreakdown: <?php echo wp_json_encode( $analytics['category_breakdown'] ?? array() ); ?>,
    monthlyData: <?php echo wp_json_encode( $analytics['monthly_data'] ?? array() ); ?>,
    downloadFrequency: <?php echo wp_json_encode( $analytics['download_frequency'] ?? array() ); ?>,
    chartColors: {
        primary: 'rgba(102, 126, 234, 0.8)',
        secondary: 'rgba(118, 75, 162, 0.8)',
        success: 'rgba(67, 233, 123, 0.8)',
        warning: 'rgba(250, 112, 154, 0.8)',
        info: 'rgba(79, 172, 254, 0.8)'
    },
    settings: {
        currency: '<?php echo esc_js( edd_get_currency() ); ?>',
        dateFormat: '<?php echo esc_js( $settings['date_format'] ?? 'M j, Y' ); ?>',
        animations: <?php echo wp_json_encode( $settings['enable_chart_animations'] ?? true ); ?>
    }
};

// Initialize charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.eddDashboardCharts !== 'undefined') {
        window.eddDashboardCharts.init();
    }
});
</script>

<?php
// Hook for additional analytics content
do_action( 'edd_dashboard_pro_after_analytics_section', $analytics, $settings );
?>
<?php
/**
 * Statistics Overview Section Template
 * 
 * Displays key statistics cards for the customer dashboard
 *
 * @package EDD_Customer_Dashboard_Pro
 * @template default
 * @section stats
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if stats should be displayed
if ( ! ( $settings['show_stats'] ?? true ) ) {
    return;
}
?>

<!-- Stats Overview -->
<div class="edd-stats-section">
    <div class="edd-stats-grid">
        
        <!-- Total Purchases -->
        <div class="edd-stat-card edd-stat-purchases-card">
            <div class="edd-stat-icon edd-stat-purchases">📦</div>
            <div class="edd-stat-content">
                <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $stats['total_purchases'] ?? 0 ) ); ?></div>
                <div class="edd-stat-label"><?php esc_html_e( 'Total Purchases', 'edd-customer-dashboard-pro' ); ?></div>
                <?php if ( isset( $stats['purchases_change'] ) && $stats['purchases_change'] !== 0 ) : ?>
                    <div class="edd-stat-change <?php echo $stats['purchases_change'] > 0 ? 'positive' : 'negative'; ?>">
                        <?php 
                        echo $stats['purchases_change'] > 0 ? '↗️' : '↘️';
                        printf( 
                            /* translators: %d: Change amount */
                            esc_html__( '%d this month', 'edd-customer-dashboard-pro' ), 
                            abs( $stats['purchases_change'] ) 
                        );
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Total Downloads -->
        <div class="edd-stat-card edd-stat-downloads-card">
            <div class="edd-stat-icon edd-stat-downloads">⬇️</div>
            <div class="edd-stat-content">
                <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $stats['total_downloads'] ?? 0 ) ); ?></div>
                <div class="edd-stat-label"><?php esc_html_e( 'Downloads', 'edd-customer-dashboard-pro' ); ?></div>
                <?php if ( isset( $stats['last_download_date'] ) && $stats['last_download_date'] ) : ?>
                    <div class="edd-stat-meta">
                        <?php 
                        printf(
                            /* translators: %s: Last download date */
                            esc_html__( 'Last: %s', 'edd-customer-dashboard-pro' ),
                            esc_html( date_i18n( $settings['date_format'], strtotime( $stats['last_download_date'] ) ) )
                        );
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- License Keys (if EDD Software Licensing is active) -->
        <?php if ( $settings['show_license_keys'] && class_exists( 'EDD_Software_Licensing' ) ) : ?>
            <div class="edd-stat-card edd-stat-licenses-card">
                <div class="edd-stat-icon edd-stat-licenses">🔑</div>
                <div class="edd-stat-content">
                    <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $stats['active_licenses'] ?? 0 ) ); ?></div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Active Licenses', 'edd-customer-dashboard-pro' ); ?></div>
                    <?php if ( isset( $stats['expired_licenses'] ) && $stats['expired_licenses'] > 0 ) : ?>
                        <div class="edd-stat-warning">
                            <?php 
                            printf(
                                /* translators: %d: Number of expired licenses */
                                esc_html__( '%d expired', 'edd-customer-dashboard-pro' ),
                                esc_html( $stats['expired_licenses'] )
                            );
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Wishlist Items (if enabled) -->
        <?php if ( $settings['enable_wishlist'] ) : ?>
            <div class="edd-stat-card edd-stat-wishlist-card">
                <div class="edd-stat-icon edd-stat-wishlist">❤️</div>
                <div class="edd-stat-content">
                    <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $stats['wishlist_items'] ?? 0 ) ); ?></div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Wishlist Items', 'edd-customer-dashboard-pro' ); ?></div>
                    <?php if ( ( $stats['wishlist_items'] ?? 0 ) > 0 ) : ?>
                        <div class="edd-stat-action">
                            <button type="button" class="edd-stat-action-btn" data-section="wishlist">
                                <?php esc_html_e( 'View All', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Total Spent (if enabled) -->
        <?php if ( $settings['show_total_spent'] ?? true ) : ?>
            <div class="edd-stat-card edd-stat-money-card">
                <div class="edd-stat-icon edd-stat-money">💰</div>
                <div class="edd-stat-content">
                    <div class="edd-stat-number">
                        <?php echo esc_html( edd_dashboard_pro_format_price( $stats['total_spent'] ?? 0 ) ); ?>
                    </div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Total Spent', 'edd-customer-dashboard-pro' ); ?></div>
                    <?php if ( isset( $stats['avg_order_value'] ) ) : ?>
                        <div class="edd-stat-meta">
                            <?php 
                            printf(
                                /* translators: %s: Average order value */
                                esc_html__( 'Avg: %s', 'edd-customer-dashboard-pro' ),
                                esc_html( edd_dashboard_pro_format_price( $stats['avg_order_value'] ) )
                            );
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Referrals (if EDD Referrals is active) -->
        <?php if ( $settings['enable_referrals'] && function_exists( 'edd_get_referral_stats' ) ) : ?>
            <div class="edd-stat-card edd-stat-referrals-card">
                <div class="edd-stat-icon edd-stat-referrals">👥</div>
                <div class="edd-stat-content">
                    <div class="edd-stat-number"><?php echo esc_html( number_format_i18n( $stats['referral_count'] ?? 0 ) ); ?></div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Referrals', 'edd-customer-dashboard-pro' ); ?></div>
                    <?php if ( isset( $stats['referral_earnings'] ) && $stats['referral_earnings'] > 0 ) : ?>
                        <div class="edd-stat-meta">
                            <?php 
                            printf(
                                /* translators: %s: Referral earnings */
                                esc_html__( 'Earned: %s', 'edd-customer-dashboard-pro' ),
                                esc_html( edd_dashboard_pro_format_price( $stats['referral_earnings'] ) )
                            );
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <?php if ( $settings['show_stats_refresh'] ?? true ) : ?>
        <div class="edd-stats-actions">
            <button type="button" class="edd-btn edd-btn-secondary edd-btn-small edd-refresh-data" 
                    data-nonce="<?php echo esc_attr( $nonces['ajax'] ); ?>">
                🔄 <?php esc_html_e( 'Refresh Stats', 'edd-customer-dashboard-pro' ); ?>
            </button>
            <span class="edd-stats-updated">
                <?php 
                printf(
                    /* translators: %s: Last updated time */
                    esc_html__( 'Last updated: %s', 'edd-customer-dashboard-pro' ),
                    esc_html( date_i18n( get_option( 'time_format' ), current_time( 'timestamp' ) ) )
                );
                ?>
            </span>
        </div>
    <?php endif; ?>
</div>

<?php
// Hook for additional stats content
do_action( 'edd_dashboard_pro_after_stats', $stats, $settings );
?>
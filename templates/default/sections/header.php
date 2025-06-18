<?php
/**
 * Dashboard Header Section Template
 * 
 * Displays the welcome message, user avatar, and quick actions
 *
 * @package EDD_Customer_Dashboard_Pro
 * @template default
 * @section header
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if header should be displayed
if ( ! ( $settings['show_welcome_message'] ?? true ) ) {
    return;
}
?>

<!-- Dashboard Header -->
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
            
            <?php if ( $settings['show_last_login'] ?? false ) : ?>
                <div class="edd-last-login">
                    <?php 
                    $last_login = get_user_meta( $current_user->ID, 'edd_dashboard_last_login', true );
                    if ( $last_login ) {
                        printf(
                            /* translators: %s: Last login date */
                            esc_html__( 'Last login: %s', 'edd-customer-dashboard-pro' ),
                            esc_html( date_i18n( $settings['date_format'], strtotime( $last_login ) ) )
                        );
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="edd-user-info">
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
            
            <?php if ( $settings['show_quick_actions'] ?? true ) : ?>
                <div class="edd-quick-actions">
                    <a href="<?php echo esc_url( $urls['account'] ); ?>" class="edd-btn edd-btn-secondary edd-btn-small">
                        ⚙️ <?php esc_html_e( 'Settings', 'edd-customer-dashboard-pro' ); ?>
                    </a>
                    <a href="<?php echo esc_url( $urls['shop'] ); ?>" class="edd-btn edd-btn-primary edd-btn-small">
                        🛒 <?php esc_html_e( 'Shop', 'edd-customer-dashboard-pro' ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ( $settings['show_customer_since'] ?? true ) : ?>
        <div class="edd-customer-since">
            <div class="edd-customer-badge">
                <span class="edd-badge-icon">🌟</span>
                <span class="edd-badge-text">
                    <?php
                    $customer_since = $customer['date_created'] ?? '';
                    if ( $customer_since ) {
                        printf(
                            /* translators: %s: Customer since date */
                            esc_html__( 'Customer since %s', 'edd-customer-dashboard-pro' ),
                            esc_html( date_i18n( 'M Y', strtotime( $customer_since ) ) )
                        );
                    } else {
                        esc_html_e( 'Valued Customer', 'edd-customer-dashboard-pro' );
                    }
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Hook for additional header content
do_action( 'edd_dashboard_pro_after_header', $customer, $settings );
?>
<?php
/**
 * Dashboard Navigation Section Template
 * 
 * Displays the tab-based navigation for different dashboard sections
 *
 * @package EDD_Customer_Dashboard_Pro
 * @template default
 * @section navigation
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get active sections based on settings
$active_sections = array();

// Always include purchases
$active_sections['purchases'] = array(
    'icon' => '📦',
    'label' => __( 'Purchases', 'edd-customer-dashboard-pro' ),
    'description' => __( 'View your order history', 'edd-customer-dashboard-pro' ),
    'count' => $stats['total_purchases'] ?? 0
);

// Downloads section
if ( $settings['show_downloads_section'] ?? true ) {
    $active_sections['downloads'] = array(
        'icon' => '⬇️',
        'label' => __( 'Downloads', 'edd-customer-dashboard-pro' ),
        'description' => __( 'Access your files', 'edd-customer-dashboard-pro' ),
        'count' => $stats['available_downloads'] ?? 0
    );
}

// License management (if EDD Software Licensing is active)
if ( $settings['show_license_keys'] && class_exists( 'EDD_Software_Licensing' ) ) {
    $active_sections['licenses'] = array(
        'icon' => '🔑',
        'label' => __( 'Licenses', 'edd-customer-dashboard-pro' ),
        'description' => __( 'Manage your licenses', 'edd-customer-dashboard-pro' ),
        'count' => $stats['total_licenses'] ?? 0
    );
}

// Wishlist (if enabled)
if ( $settings['enable_wishlist'] ) {
    $active_sections['wishlist'] = array(
        'icon' => '❤️',
        'label' => __( 'Wishlist', 'edd-customer-dashboard-pro' ),
        'description' => __( 'Your saved items', 'edd-customer-dashboard-pro' ),
        'count' => $stats['wishlist_items'] ?? 0
    );
}

// Analytics (if enabled)
if ( $settings['enable_analytics'] ) {
    $active_sections['analytics'] = array(
        'icon' => '📊',
        'label' => __( 'Analytics', 'edd-customer-dashboard-pro' ),
        'description' => __( 'View your statistics', 'edd-customer-dashboard-pro' ),
        'count' => null
    );
}

// Referrals (if EDD Referrals is active)
if ( $settings['enable_referrals'] && function_exists( 'edd_get_referral_stats' ) ) {
    $active_sections['referrals'] = array(
        'icon' => '👥',
        'label' => __( 'Referrals', 'edd-customer-dashboard-pro' ),
        'description' => __( 'Track your referrals', 'edd-customer-dashboard-pro' ),
        'count' => $stats['referral_count'] ?? 0
    );
}

// Support (if enabled)
if ( $settings['enable_support'] ) {
    $active_sections['support'] = array(
        'icon' => '💬',
        'label' => __( 'Support', 'edd-customer-dashboard-pro' ),
        'description' => __( 'Get help and support', 'edd-customer-dashboard-pro' ),
        'count' => $stats['open_tickets'] ?? null
    );
}

// Filter sections
$active_sections = apply_filters( 'edd_dashboard_pro_navigation_sections', $active_sections, $settings );

// Get current section from URL or default to purchases
$current_section = sanitize_key( $_GET['section'] ?? 'purchases' );
if ( ! array_key_exists( $current_section, $active_sections ) ) {
    $current_section = 'purchases';
}
?>

<!-- Dashboard Navigation -->
<div class="edd-dashboard-nav">
    <div class="edd-nav-wrapper">
        
        <?php if ( $settings['show_nav_title'] ?? true ) : ?>
            <div class="edd-nav-header">
                <h3 class="edd-nav-title"><?php esc_html_e( 'Dashboard Sections', 'edd-customer-dashboard-pro' ); ?></h3>
                <?php if ( $settings['show_section_descriptions'] ?? false ) : ?>
                    <p class="edd-nav-description">
                        <?php esc_html_e( 'Navigate through different sections of your customer dashboard', 'edd-customer-dashboard-pro' ); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="edd-nav-tabs" role="tablist">
            <?php foreach ( $active_sections as $section_key => $section_data ) : ?>
                <a href="#edd-section-<?php echo esc_attr( $section_key ); ?>" 
                   class="edd-nav-tab <?php echo $current_section === $section_key ? 'active' : ''; ?>" 
                   data-section="<?php echo esc_attr( $section_key ); ?>"
                   role="tab"
                   aria-selected="<?php echo $current_section === $section_key ? 'true' : 'false'; ?>"
                   aria-controls="edd-section-<?php echo esc_attr( $section_key ); ?>"
                   tabindex="<?php echo $current_section === $section_key ? '0' : '-1'; ?>">
                   
                    <span class="edd-tab-icon"><?php echo esc_html( $section_data['icon'] ); ?></span>
                    
                    <span class="edd-tab-content">
                        <span class="edd-tab-label"><?php echo esc_html( $section_data['label'] ); ?></span>
                        
                        <?php if ( $settings['show_section_descriptions'] ?? false ) : ?>
                            <span class="edd-tab-description"><?php echo esc_html( $section_data['description'] ); ?></span>
                        <?php endif; ?>
                        
                        <?php if ( $settings['show_section_counts'] ?? true && $section_data['count'] !== null ) : ?>
                            <span class="edd-tab-count">
                                <?php echo esc_html( number_format_i18n( $section_data['count'] ) ); ?>
                            </span>
                        <?php endif; ?>
                    </span>
                    
                    <?php if ( $section_key === 'support' && ( $stats['open_tickets'] ?? 0 ) > 0 ) : ?>
                        <span class="edd-notification-badge" title="<?php esc_attr_e( 'Open support tickets', 'edd-customer-dashboard-pro' ); ?>">
                            <?php echo esc_html( $stats['open_tickets'] ); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ( $section_key === 'licenses' && ( $stats['expired_licenses'] ?? 0 ) > 0 ) : ?>
                        <span class="edd-warning-badge" title="<?php esc_attr_e( 'Expired licenses', 'edd-customer-dashboard-pro' ); ?>">
                            ⚠️
                        </span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ( $settings['show_mobile_dropdown'] ?? true ) : ?>
            <!-- Mobile dropdown navigation -->
            <div class="edd-mobile-nav">
                <select class="edd-mobile-nav-select" aria-label="<?php esc_attr_e( 'Navigate to section', 'edd-customer-dashboard-pro' ); ?>">
                    <?php foreach ( $active_sections as $section_key => $section_data ) : ?>
                        <option value="<?php echo esc_attr( $section_key ); ?>" 
                                <?php selected( $current_section, $section_key ); ?>>
                            <?php 
                            echo esc_html( $section_data['icon'] . ' ' . $section_data['label'] );
                            if ( $section_data['count'] !== null ) {
                                echo ' (' . esc_html( number_format_i18n( $section_data['count'] ) ) . ')';
                            }
                            ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <?php if ( $settings['show_search_bar'] ?? false ) : ?>
            <!-- Quick search bar -->
            <div class="edd-nav-search">
                <input type="search" 
                       class="edd-search-input" 
                       placeholder="<?php esc_attr_e( 'Search your purchases...', 'edd-customer-dashboard-pro' ); ?>"
                       aria-label="<?php esc_attr_e( 'Search dashboard content', 'edd-customer-dashboard-pro' ); ?>">
                <button type="button" class="edd-search-btn" aria-label="<?php esc_attr_e( 'Search', 'edd-customer-dashboard-pro' ); ?>">
                    🔍
                </button>
            </div>
        <?php endif; ?>

        <?php if ( $settings['show_quick_filters'] ?? false ) : ?>
            <!-- Quick filters -->
            <div class="edd-nav-filters">
                <div class="edd-filter-group">
                    <label class="edd-filter-label"><?php esc_html_e( 'Show:', 'edd-customer-dashboard-pro' ); ?></label>
                    <select class="edd-quick-filter" data-filter="status">
                        <option value=""><?php esc_html_e( 'All Items', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="recent"><?php esc_html_e( 'Recent', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="active"><?php esc_html_e( 'Active', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="expired"><?php esc_html_e( 'Expired', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <?php if ( $settings['show_breadcrumbs'] ?? false ) : ?>
        <!-- Breadcrumb navigation -->
        <div class="edd-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb navigation', 'edd-customer-dashboard-pro' ); ?>">
            <a href="<?php echo esc_url( home_url() ); ?>"><?php esc_html_e( 'Home', 'edd-customer-dashboard-pro' ); ?></a>
            <span class="edd-breadcrumb-separator">›</span>
            <a href="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'Dashboard', 'edd-customer-dashboard-pro' ); ?></a>
            <span class="edd-breadcrumb-separator">›</span>
            <span class="edd-breadcrumb-current"><?php echo esc_html( $active_sections[ $current_section ]['label'] ?? __( 'Current Section', 'edd-customer-dashboard-pro' ) ); ?></span>
        </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
// Set current section for JavaScript
window.eddDashboardCurrentSection = '<?php echo esc_js( $current_section ); ?>';
</script>

<?php
// Hook for additional navigation content
do_action( 'edd_dashboard_pro_after_navigation', $active_sections, $current_section, $settings );
?>
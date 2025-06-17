<?php
/**
 * Shortcodes Class for EDD Customer Dashboard Pro
 * Handles shortcode registration and rendering
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EDD Dashboard Pro Shortcodes Class
 */
class EDD_Dashboard_Pro_Shortcodes {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
        $this->register_shortcodes();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
        add_filter( 'the_content', array( $this, 'maybe_redirect_to_login' ) );
    }

    /**
     * Register shortcodes
     */
    private function register_shortcodes() {
        add_shortcode( 'edd_customer_dashboard_pro', array( $this, 'render_dashboard' ) );
        add_shortcode( 'edd_dashboard_pro', array( $this, 'render_dashboard' ) );
        add_shortcode( 'edd_customer_stats', array( $this, 'render_customer_stats' ) );
        add_shortcode( 'edd_customer_purchases', array( $this, 'render_customer_purchases' ) );
        add_shortcode( 'edd_customer_downloads', array( $this, 'render_customer_downloads' ) );
        add_shortcode( 'edd_customer_wishlist', array( $this, 'render_customer_wishlist' ) );
    }

    /**
     * Render main dashboard shortcode
     *
     * @param array $atts Shortcode attributes
     * @param string $content Shortcode content
     * @return string
     */
    public function render_dashboard( $atts, $content = '' ) {
        // Parse shortcode attributes
        $atts = shortcode_atts( array(
            'template' => '',
            'section' => '',
            'user_id' => 0,
            'show_header' => 'true',
            'show_stats' => 'true',
            'show_navigation' => 'true',
            'items_per_page' => 10,
            'redirect_login' => 'true'
        ), $atts, 'edd_customer_dashboard_pro' );

        // Check if user is logged in
        if ( ! is_user_logged_in() ) {
            return $this->render_login_message( $atts );
        }

        // Check dashboard access
        if ( ! EDD_Dashboard_Pro_Security::can_access_dashboard() ) {
            return $this->render_no_access_message();
        }

        // Sanitize attributes
        $atts = $this->sanitize_shortcode_atts( $atts );

        // Get template loader
        $template_loader = new EDD_Dashboard_Pro_Template_Loader();

        // Override template if specified
        if ( ! empty( $atts['template'] ) && $template_loader->get_template_info( $atts['template'] ) ) {
            $original_template = $template_loader->get_current_template();
            $template_loader->set_current_template( $atts['template'] );
        }

        // Prepare template arguments
        $template_args = array(
            'shortcode_atts' => $atts,
            'show_header' => $this->parse_bool( $atts['show_header'] ),
            'show_stats' => $this->parse_bool( $atts['show_stats'] ),
            'show_navigation' => $this->parse_bool( $atts['show_navigation'] ),
            'items_per_page' => (int) $atts['items_per_page'],
            'specific_section' => $atts['section']
        );

        // Load specific section or full dashboard
        if ( ! empty( $atts['section'] ) ) {
            $output = $template_loader->load_section( $atts['section'], $template_args );
        } else {
            $output = $template_loader->load_dashboard( $template_args );
        }

        // Restore original template if it was overridden
        if ( isset( $original_template ) ) {
            $template_loader->set_current_template( $original_template['name'] );
        }

        // Wrap output in container
        return $this->wrap_dashboard_output( $output, $atts );
    }

    /**
     * Render customer stats shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_customer_stats( $atts ) {
        if ( ! is_user_logged_in() ) {
            return $this->render_login_message( $atts );
        }

        $atts = shortcode_atts( array(
            'show_purchases' => 'true',
            'show_downloads' => 'true',
            'show_licenses' => 'true',
            'show_wishlist' => 'true',
            'show_spent' => 'true',
            'layout' => 'grid', // grid, list, inline
            'columns' => '4'
        ), $atts, 'edd_customer_stats' );

        $customer_data = edd_dashboard_pro_get_current_customer();
        if ( ! $customer_data ) {
            return '<p>' . esc_html__( 'No customer data found.', 'edd-customer-dashboard-pro' ) . '</p>';
        }

        $stats = edd_dashboard_pro_get_customer_stats( $customer_data['id'] );
        
        $output = '<div class="edd-dashboard-stats edd-stats-' . esc_attr( $atts['layout'] ) . ' edd-stats-cols-' . esc_attr( $atts['columns'] ) . '">';

        if ( $this->parse_bool( $atts['show_purchases'] ) ) {
            $output .= $this->render_stat_item( 'purchases', $stats['total_purchases'], __( 'Total Purchases', 'edd-customer-dashboard-pro' ), '📦' );
        }

        if ( $this->parse_bool( $atts['show_downloads'] ) ) {
            $output .= $this->render_stat_item( 'downloads', $stats['total_downloads'], __( 'Downloads', 'edd-customer-dashboard-pro' ), '⬇️' );
        }

        if ( $this->parse_bool( $atts['show_licenses'] ) ) {
            $output .= $this->render_stat_item( 'licenses', $stats['active_licenses'], __( 'Active Licenses', 'edd-customer-dashboard-pro' ), '🔑' );
        }

        if ( $this->parse_bool( $atts['show_wishlist'] ) ) {
            $output .= $this->render_stat_item( 'wishlist', $stats['wishlist_items'], __( 'Wishlist Items', 'edd-customer-dashboard-pro' ), '❤️' );
        }

        if ( $this->parse_bool( $atts['show_spent'] ) ) {
            $formatted_amount = edd_dashboard_pro_format_price( $stats['total_spent'] );
            $output .= $this->render_stat_item( 'spent', $formatted_amount, __( 'Total Spent', 'edd-customer-dashboard-pro' ), '💰' );
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render customer purchases shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_customer_purchases( $atts ) {
        if ( ! is_user_logged_in() ) {
            return $this->render_login_message( $atts );
        }

        $atts = shortcode_atts( array(
            'number' => '5',
            'show_details' => 'true',
            'show_download_links' => 'true',
            'show_receipts' => 'true',
            'layout' => 'list' // list, table, cards
        ), $atts, 'edd_customer_purchases' );

        $customer_data = edd_dashboard_pro_get_current_customer();
        if ( ! $customer_data ) {
            return '<p>' . esc_html__( 'No purchases found.', 'edd-customer-dashboard-pro' ) . '</p>';
        }

        $purchases = edd_dashboard_pro_get_customer_purchases( $customer_data['id'], (int) $atts['number'] );

        if ( empty( $purchases ) ) {
            return '<div class="edd-no-purchases"><p>' . esc_html__( 'You haven\'t made any purchases yet.', 'edd-customer-dashboard-pro' ) . '</p></div>';
        }

        $output = '<div class="edd-customer-purchases edd-purchases-' . esc_attr( $atts['layout'] ) . '">';

        foreach ( $purchases as $purchase ) {
            $output .= $this->render_purchase_item( $purchase, $atts );
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render customer downloads shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_customer_downloads( $atts ) {
        if ( ! is_user_logged_in() ) {
            return $this->render_login_message( $atts );
        }

        $atts = shortcode_atts( array(
            'number' => '10',
            'show_limits' => 'true',
            'show_dates' => 'true'
        ), $atts, 'edd_customer_downloads' );

        $download_history = edd_dashboard_pro_get_download_history( 0, (int) $atts['number'] );

        if ( empty( $download_history ) ) {
            return '<div class="edd-no-downloads"><p>' . esc_html__( 'No downloads yet.', 'edd-customer-dashboard-pro' ) . '</p></div>';
        }

        $output = '<div class="edd-customer-downloads">';
        $output .= '<h3>' . esc_html__( 'Recent Downloads', 'edd-customer-dashboard-pro' ) . '</h3>';
        $output .= '<div class="edd-downloads-list">';

        foreach ( $download_history as $download ) {
            $output .= '<div class="edd-download-item">';
            $output .= '<div class="edd-download-name">' . esc_html( $download['product_name'] ) . '</div>';
            $output .= '<div class="edd-download-file">' . esc_html( $download['file_name'] ) . '</div>';
            
            if ( $this->parse_bool( $atts['show_dates'] ) ) {
                $output .= '<div class="edd-download-date">' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $download['date'] ) ) ) . '</div>';
            }
            
            $output .= '</div>';
        }

        $output .= '</div></div>';

        return $output;
    }

    /**
     * Render customer wishlist shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_customer_wishlist( $atts ) {
        if ( ! is_user_logged_in() ) {
            return $this->render_login_message( $atts );
        }

        $atts = shortcode_atts( array(
            'show_prices' => 'true',
            'show_images' => 'true',
            'show_add_to_cart' => 'true',
            'layout' => 'grid', // grid, list
            'columns' => '3'
        ), $atts, 'edd_customer_wishlist' );

        $wishlist = edd_dashboard_pro_get_wishlist();

        if ( empty( $wishlist ) ) {
            return '<div class="edd-empty-wishlist"><p>' . esc_html__( 'Your wishlist is empty.', 'edd-customer-dashboard-pro' ) . '</p></div>';
        }

        $output = '<div class="edd-customer-wishlist edd-wishlist-' . esc_attr( $atts['layout'] ) . ' edd-wishlist-cols-' . esc_attr( $atts['columns'] ) . '">';

        foreach ( $wishlist as $item ) {
            $output .= $this->render_wishlist_item( $item, $atts );
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render a single stat item
     *
     * @param string $type Stat type
     * @param mixed $value Stat value
     * @param string $label Stat label
     * @param string $icon Stat icon
     * @return string
     */
    private function render_stat_item( $type, $value, $label, $icon ) {
        $output = '<div class="edd-stat-item edd-stat-' . esc_attr( $type ) . '">';
        $output .= '<div class="edd-stat-icon">' . esc_html( $icon ) . '</div>';
        $output .= '<div class="edd-stat-value">' . esc_html( $value ) . '</div>';
        $output .= '<div class="edd-stat-label">' . esc_html( $label ) . '</div>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render a single purchase item
     *
     * @param array $purchase Purchase data
     * @param array $atts Shortcode attributes
     * @return string
     */
    private function render_purchase_item( $purchase, $atts ) {
        $output = '<div class="edd-purchase-item">';
        $output .= '<div class="edd-purchase-header">';
        $output .= '<div class="edd-purchase-id">' . sprintf( esc_html__( 'Order #%s', 'edd-customer-dashboard-pro' ), $purchase['id'] ) . '</div>';
        $output .= '<div class="edd-purchase-date">' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $purchase['date'] ) ) ) . '</div>';
        $output .= '<div class="edd-purchase-total">' . esc_html( edd_dashboard_pro_format_price( $purchase['total'], $purchase['currency'] ) ) . '</div>';
        $output .= '</div>';

        if ( $this->parse_bool( $atts['show_details'] ) ) {
            $output .= '<div class="edd-purchase-products">';
            
            foreach ( $purchase['products'] as $product ) {
                $output .= '<div class="edd-purchase-product">';
                $output .= '<div class="edd-product-name">' . esc_html( $product['name'] ) . '</div>';
                
                if ( $this->parse_bool( $atts['show_download_links'] ) && ! empty( $product['download_files'] ) ) {
                    $output .= '<div class="edd-product-downloads">';
                    
                    foreach ( $product['download_files'] as $file ) {
                        $download_url = $this->get_secure_download_url( $purchase['id'], $product['id'], $file['id'] );
                        $output .= '<a href="' . esc_url( $download_url ) . '" class="edd-download-link">';
                        $output .= '⬇️ ' . esc_html( $file['name'] );
                        $output .= '</a>';
                    }
                    
                    $output .= '</div>';
                }
                
                $output .= '</div>';
            }
            
            $output .= '</div>';
        }

        if ( $this->parse_bool( $atts['show_receipts'] ) ) {
            $output .= '<div class="edd-purchase-actions">';
            $output .= '<a href="' . esc_url( $purchase['receipt_url'] ) . '" class="edd-receipt-link">';
            $output .= esc_html__( 'View Receipt', 'edd-customer-dashboard-pro' );
            $output .= '</a>';
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Render a single wishlist item
     *
     * @param array $item Wishlist item data
     * @param array $atts Shortcode attributes
     * @return string
     */
    private function render_wishlist_item( $item, $atts ) {
        $output = '<div class="edd-wishlist-item">';

        if ( $this->parse_bool( $atts['show_images'] ) && $item['thumbnail'] ) {
            $output .= '<div class="edd-wishlist-image">';
            $output .= '<img src="' . esc_url( $item['thumbnail'] ) . '" alt="' . esc_attr( $item['title'] ) . '">';
            $output .= '</div>';
        }

        $output .= '<div class="edd-wishlist-details">';
        $output .= '<h4 class="edd-wishlist-title">';
        $output .= '<a href="' . esc_url( $item['permalink'] ) . '">' . esc_html( $item['title'] ) . '</a>';
        $output .= '</h4>';

        if ( $this->parse_bool( $atts['show_prices'] ) ) {
            $output .= '<div class="edd-wishlist-price">' . esc_html( edd_dashboard_pro_format_price( $item['price'] ) ) . '</div>';
        }

        if ( $this->parse_bool( $atts['show_add_to_cart'] ) ) {
            $output .= '<div class="edd-wishlist-actions">';
            $output .= '<a href="' . esc_url( add_query_arg( 'edd_action', 'add_to_cart', add_query_arg( 'download_id', $item['id'], edd_get_checkout_uri() ) ) ) . '" class="edd-add-to-cart">';
            $output .= esc_html__( 'Add to Cart', 'edd-customer-dashboard-pro' );
            $output .= '</a>';
            $output .= '</div>';
        }

        $output .= '</div></div>';

        return $output;
    }

    /**
     * Render login message for non-logged-in users
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    private function render_login_message( $atts ) {
        if ( isset( $atts['redirect_login'] ) && $this->parse_bool( $atts['redirect_login'] ) ) {
            // Redirect to login page
            $login_url = wp_login_url( get_permalink() );
            wp_safe_redirect( $login_url );
            exit;
        }

        $output = '<div class="edd-login-required">';
        $output .= '<p>' . esc_html__( 'Please log in to view your dashboard.', 'edd-customer-dashboard-pro' ) . '</p>';
        $output .= '<p><a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">' . esc_html__( 'Log In', 'edd-customer-dashboard-pro' ) . '</a></p>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Render no access message
     *
     * @return string
     */
    private function render_no_access_message() {
        $output = '<div class="edd-no-access">';
        $output .= '<p>' . esc_html__( 'You need to make at least one purchase to access the customer dashboard.', 'edd-customer-dashboard-pro' ) . '</p>';
        $output .= '<p><a href="' . esc_url( edd_get_checkout_uri() ) . '">' . esc_html__( 'Browse Products', 'edd-customer-dashboard-pro' ) . '</a></p>';
        $output .= '</div>';

        return $output;
    }

    /**
     * Wrap dashboard output in container
     *
     * @param string $output Dashboard output
     * @param array $atts Shortcode attributes
     * @return string
     */
    private function wrap_dashboard_output( $output, $atts ) {
        $classes = array( 'edd-customer-dashboard-pro' );
        
        if ( ! empty( $atts['template'] ) ) {
            $classes[] = 'edd-template-' . sanitize_html_class( $atts['template'] );
        }
        
        if ( ! empty( $atts['section'] ) ) {
            $classes[] = 'edd-section-' . sanitize_html_class( $atts['section'] );
        }

        $wrapper = '<div class="' . esc_attr( implode( ' ', $classes ) ) . '">';
        $wrapper .= $output;
        $wrapper .= '</div>';

        return $wrapper;
    }

    /**
     * Get secure download URL
     *
     * @param int $payment_id Payment ID
     * @param int $download_id Download ID
     * @param string $file_key File key
     * @return string
     */
    private function get_secure_download_url( $payment_id, $download_id, $file_key ) {
        $args = array(
            'payment_id' => $payment_id,
            'download_id' => $download_id,
            'file_key' => $file_key,
            'edd_action' => 'download_file',
            '_wpnonce' => EDD_Dashboard_Pro_Security::create_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_DOWNLOAD )
        );

        return add_query_arg( $args, home_url() );
    }

    /**
     * Sanitize shortcode attributes
     *
     * @param array $atts Shortcode attributes
     * @return array
     */
    private function sanitize_shortcode_atts( $atts ) {
        return array(
            'template' => sanitize_key( $atts['template'] ),
            'section' => sanitize_key( $atts['section'] ),
            'user_id' => (int) $atts['user_id'],
            'show_header' => sanitize_text_field( $atts['show_header'] ),
            'show_stats' => sanitize_text_field( $atts['show_stats'] ),
            'show_navigation' => sanitize_text_field( $atts['show_navigation'] ),
            'items_per_page' => max( 1, min( 100, (int) $atts['items_per_page'] ) ),
            'redirect_login' => sanitize_text_field( $atts['redirect_login'] )
        );
    }

    /**
     * Parse boolean values from shortcode attributes
     *
     * @param string $value Attribute value
     * @return bool
     */
    private function parse_bool( $value ) {
        return in_array( strtolower( $value ), array( 'true', '1', 'yes', 'on' ) );
    }

    /**
     * Maybe enqueue assets for shortcode
     */
    public function maybe_enqueue_assets() {
        global $post;
        
        if ( ! $post ) {
            return;
        }

        // Check if any dashboard shortcodes are present
        $shortcodes = array(
            'edd_customer_dashboard_pro',
            'edd_dashboard_pro',
            'edd_customer_stats',
            'edd_customer_purchases',
            'edd_customer_downloads',
            'edd_customer_wishlist'
        );

        $has_shortcode = false;
        foreach ( $shortcodes as $shortcode ) {
            if ( has_shortcode( $post->post_content, $shortcode ) ) {
                $has_shortcode = true;
                break;
            }
        }

        if ( $has_shortcode ) {
            // Enqueue base styles
            wp_enqueue_style(
                'edd-dashboard-pro-shortcodes',
                EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/css/shortcodes.css',
                array(),
                EDD_DASHBOARD_PRO_VERSION
            );

            // Enqueue shortcode scripts
            wp_enqueue_script(
                'edd-dashboard-pro-shortcodes',
                EDD_DASHBOARD_PRO_PLUGIN_URL . 'assets/js/shortcodes.js',
                array( 'jquery' ),
                EDD_DASHBOARD_PRO_VERSION,
                true
            );

            // Localize script
            wp_localize_script(
                'edd-dashboard-pro-shortcodes',
                'eddDashboardShortcodes',
                array(
                    'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                    'nonce' => EDD_Dashboard_Pro_Security::create_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_AJAX ),
                    'strings' => array(
                        'loading' => esc_html__( 'Loading...', 'edd-customer-dashboard-pro' ),
                        'error' => esc_html__( 'An error occurred.', 'edd-customer-dashboard-pro' ),
                        'removeFromWishlist' => esc_html__( 'Remove from Wishlist', 'edd-customer-dashboard-pro' ),
                        'addToWishlist' => esc_html__( 'Add to Wishlist', 'edd-customer-dashboard-pro' )
                    )
                )
            );
        }
    }

    /**
     * Maybe redirect to login if content contains dashboard shortcode
     *
     * @param string $content Post content
     * @return string
     */
    public function maybe_redirect_to_login( $content ) {
        if ( is_user_logged_in() || is_admin() || wp_doing_ajax() ) {
            return $content;
        }

        // Check if content has dashboard shortcode with redirect_login="true"
        if ( has_shortcode( $content, 'edd_customer_dashboard_pro' ) || has_shortcode( $content, 'edd_dashboard_pro' ) ) {
            global $shortcode_tags;
            
            // Temporarily store original shortcode handlers
            $original_handlers = $shortcode_tags;
            
            // Replace with dummy handlers to extract attributes
            $shortcode_tags = array(
                'edd_customer_dashboard_pro' => array( $this, 'extract_shortcode_atts' ),
                'edd_dashboard_pro' => array( $this, 'extract_shortcode_atts' )
            );
            
            // Process shortcodes to check redirect_login attribute
            do_shortcode( $content );
            
            // Restore original handlers
            $shortcode_tags = $original_handlers;
        }

        return $content;
    }

    /**
     * Extract shortcode attributes (used for redirect checking)
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function extract_shortcode_atts( $atts ) {
        $atts = shortcode_atts( array(
            'redirect_login' => 'true'
        ), $atts );

        // If redirect_login is true, redirect to login
        if ( $this->parse_bool( $atts['redirect_login'] ) && ! is_user_logged_in() ) {
            wp_safe_redirect( wp_login_url( get_permalink() ) );
            exit;
        }

        return '';
    }
}
<?php
/**
 * Helper Functions for EDD Customer Dashboard Pro
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get current customer data
 *
 * @return array|false Customer data or false if not logged in
 */
function edd_dashboard_pro_get_current_customer() {
    if ( ! is_user_logged_in() ) {
        return false;
    }

    $user_id = get_current_user_id();
    $customer = new EDD_Customer( $user_id, true );

    if ( ! $customer->id ) {
        return false;
    }

    return array(
        'id' => $customer->id,
        'user_id' => $customer->user_id,
        'name' => $customer->name,
        'email' => $customer->email,
        'purchase_count' => $customer->purchase_count,
        'purchase_value' => $customer->purchase_value,
        'date_created' => $customer->date_created
    );
}

/**
 * Get customer purchase statistics
 *
 * @param int $customer_id Customer ID
 * @return array
 */
function edd_dashboard_pro_get_customer_stats( $customer_id = 0 ) {
    if ( ! $customer_id ) {
        $customer_data = edd_dashboard_pro_get_current_customer();
        $customer_id = $customer_data ? $customer_data['id'] : 0;
    }

    if ( ! $customer_id ) {
        return array(
            'total_purchases' => 0,
            'total_downloads' => 0,
            'active_licenses' => 0,
            'wishlist_items' => 0,
            'total_spent' => 0
        );
    }

    $customer = new EDD_Customer( $customer_id );
    
    // Get download count from user meta or calculate
    $download_count = get_user_meta( $customer->user_id, '_edd_dashboard_pro_download_count', true );
    if ( ! $download_count ) {
        $download_count = edd_dashboard_pro_calculate_download_count( $customer->user_id );
    }

    // Get active licenses count
    $active_licenses = edd_dashboard_pro_get_active_licenses_count( $customer->user_id );

    // Get wishlist count
    $wishlist_count = edd_dashboard_pro_get_wishlist_count( $customer->user_id );

    return array(
        'total_purchases' => (int) $customer->purchase_count,
        'total_downloads' => (int) $download_count,
        'active_licenses' => (int) $active_licenses,
        'wishlist_items' => (int) $wishlist_count,
        'total_spent' => (float) $customer->purchase_value
    );
}

/**
 * Get customer purchases with enhanced data
 *
 * @param int $customer_id Customer ID
 * @param int $number Number of purchases to retrieve
 * @param int $offset Offset for pagination
 * @return array
 */
function edd_dashboard_pro_get_customer_purchases( $customer_id = 0, $number = 20, $offset = 0 ) {
    if ( ! $customer_id ) {
        $customer_data = edd_dashboard_pro_get_current_customer();
        $customer_id = $customer_data ? $customer_data['id'] : 0;
    }

    if ( ! $customer_id ) {
        return array();
    }

    $payments = edd_get_payments( array(
        'customer' => $customer_id,
        'number' => $number,
        'offset' => $offset,
        'status' => array( 'publish', 'complete' )
    ) );

    $purchases = array();

    foreach ( $payments as $payment ) {
        $payment_meta = edd_get_payment_meta( $payment->ID );
        $cart_details = edd_get_payment_meta_cart_details( $payment->ID );
        
        $products = array();
        if ( is_array( $cart_details ) ) {
            foreach ( $cart_details as $item ) {
                $download_id = $item['id'];
                $price_id = isset( $item['item_number']['options']['price_id'] ) ? $item['item_number']['options']['price_id'] : null;
                
                $products[] = array(
                    'id' => $download_id,
                    'name' => get_the_title( $download_id ),
                    'price_id' => $price_id,
                    'item_price' => $item['item_price'],
                    'quantity' => $item['quantity'],
                    'download_files' => edd_dashboard_pro_get_download_files( $download_id, $price_id ),
                    'license_key' => edd_dashboard_pro_get_license_key( $payment->ID, $download_id ),
                    'version' => edd_dashboard_pro_get_product_version( $download_id )
                );
            }
        }

        $purchases[] = array(
            'id' => $payment->ID,
            'date' => $payment->post_date,
            'status' => $payment->post_status,
            'total' => edd_get_payment_amount( $payment->ID ),
            'currency' => edd_get_payment_currency_code( $payment->ID ),
            'payment_method' => edd_get_payment_gateway( $payment->ID ),
            'products' => $products,
            'receipt_url' => edd_get_success_page_uri( '?payment_key=' . edd_get_payment_key( $payment->ID ) )
        );
    }

    return $purchases;
}

/**
 * Get download files for a product
 *
 * @param int $download_id Download ID
 * @param int|null $price_id Price ID
 * @return array
 */
function edd_dashboard_pro_get_download_files( $download_id, $price_id = null ) {
    $files = edd_get_download_files( $download_id, $price_id );
    
    if ( ! $files ) {
        return array();
    }

    $formatted_files = array();
    foreach ( $files as $key => $file ) {
        $formatted_files[] = array(
            'id' => $key,
            'name' => $file['name'],
            'file' => $file['file'],
            'condition' => isset( $file['condition'] ) ? $file['condition'] : 'all'
        );
    }

    return $formatted_files;
}

/**
 * Get license key for a purchase
 *
 * @param int $payment_id Payment ID
 * @param int $download_id Download ID
 * @return string|null
 */
function edd_dashboard_pro_get_license_key( $payment_id, $download_id ) {
    // Check if EDD Software Licensing is active
    if ( ! class_exists( 'EDD_Software_Licensing' ) ) {
        return null;
    }

    $license_key = edd_software_licensing()->get_license_key( $download_id, $payment_id );
    return $license_key ? $license_key : null;
}

/**
 * Get product version
 *
 * @param int $download_id Download ID
 * @return string
 */
function edd_dashboard_pro_get_product_version( $download_id ) {
    $version = get_post_meta( $download_id, '_edd_sl_version', true );
    return $version ? $version : '1.0.0';
}

/**
 * Calculate total download count for user
 *
 * @param int $user_id User ID
 * @return int
 */
function edd_dashboard_pro_calculate_download_count( $user_id ) {
    global $wpdb;

    $count = $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}edd_logs 
         WHERE type = 'file_download' 
         AND user_id = %d",
        $user_id
    ) );

    // Cache the result
    update_user_meta( $user_id, '_edd_dashboard_pro_download_count', (int) $count );

    return (int) $count;
}

/**
 * Get active licenses count for user
 *
 * @param int $user_id User ID
 * @return int
 */
function edd_dashboard_pro_get_active_licenses_count( $user_id ) {
    if ( ! class_exists( 'EDD_Software_Licensing' ) ) {
        return 0;
    }

    $licenses = edd_software_licensing()->get_licenses_of_user( $user_id );
    $active_count = 0;

    if ( $licenses ) {
        foreach ( $licenses as $license ) {
            if ( 'active' === $license->status ) {
                $active_count++;
            }
        }
    }

    return $active_count;
}

/**
 * Get wishlist count for user
 *
 * @param int $user_id User ID
 * @return int
 */
function edd_dashboard_pro_get_wishlist_count( $user_id ) {
    $wishlist = get_user_meta( $user_id, '_edd_dashboard_pro_wishlist', true );
    return is_array( $wishlist ) ? count( $wishlist ) : 0;
}

/**
 * Get user wishlist
 *
 * @param int $user_id User ID
 * @return array
 */
function edd_dashboard_pro_get_wishlist( $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    if ( ! $user_id ) {
        return array();
    }

    $wishlist_ids = get_user_meta( $user_id, '_edd_dashboard_pro_wishlist', true );
    
    if ( ! is_array( $wishlist_ids ) || empty( $wishlist_ids ) ) {
        return array();
    }

    $wishlist_items = array();
    foreach ( $wishlist_ids as $download_id ) {
        $download = get_post( $download_id );
        if ( $download && 'download' === $download->post_type ) {
            $wishlist_items[] = array(
                'id' => $download_id,
                'title' => $download->post_title,
                'price' => edd_get_download_price( $download_id ),
                'permalink' => get_permalink( $download_id ),
                'thumbnail' => get_the_post_thumbnail_url( $download_id, 'thumbnail' ),
                'date_added' => get_user_meta( $user_id, "_edd_wishlist_date_{$download_id}", true )
            );
        }
    }

    return $wishlist_items;
}

/**
 * Add item to wishlist
 *
 * @param int $download_id Download ID
 * @param int $user_id User ID
 * @return bool
 */
function edd_dashboard_pro_add_to_wishlist( $download_id, $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    if ( ! $user_id || ! $download_id ) {
        return false;
    }

    $wishlist = get_user_meta( $user_id, '_edd_dashboard_pro_wishlist', true );
    if ( ! is_array( $wishlist ) ) {
        $wishlist = array();
    }

    if ( ! in_array( $download_id, $wishlist ) ) {
        $wishlist[] = $download_id;
        update_user_meta( $user_id, '_edd_dashboard_pro_wishlist', $wishlist );
        update_user_meta( $user_id, "_edd_wishlist_date_{$download_id}", current_time( 'mysql' ) );
        return true;
    }

    return false;
}

/**
 * Remove item from wishlist
 *
 * @param int $download_id Download ID
 * @param int $user_id User ID
 * @return bool
 */
function edd_dashboard_pro_remove_from_wishlist( $download_id, $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    if ( ! $user_id || ! $download_id ) {
        return false;
    }

    $wishlist = get_user_meta( $user_id, '_edd_dashboard_pro_wishlist', true );
    if ( ! is_array( $wishlist ) ) {
        return false;
    }

    $key = array_search( $download_id, $wishlist );
    if ( false !== $key ) {
        unset( $wishlist[ $key ] );
        update_user_meta( $user_id, '_edd_dashboard_pro_wishlist', array_values( $wishlist ) );
        delete_user_meta( $user_id, "_edd_wishlist_date_{$download_id}" );
        return true;
    }

    return false;
}

/**
 * Check if item is in wishlist
 *
 * @param int $download_id Download ID
 * @param int $user_id User ID
 * @return bool
 */
function edd_dashboard_pro_is_in_wishlist( $download_id, $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    if ( ! $user_id || ! $download_id ) {
        return false;
    }

    $wishlist = get_user_meta( $user_id, '_edd_dashboard_pro_wishlist', true );
    return is_array( $wishlist ) && in_array( $download_id, $wishlist );
}

/**
 * Format price with currency
 *
 * @param float $amount Amount
 * @param string $currency Currency code
 * @return string
 */
function edd_dashboard_pro_format_price( $amount, $currency = '' ) {
    if ( ! $currency ) {
        $currency = edd_get_currency();
    }
    
    return edd_currency_filter( edd_format_amount( $amount ), $currency );
}

/**
 * Get download limits for user purchase
 *
 * @param int $payment_id Payment ID
 * @param int $download_id Download ID
 * @param int $user_id User ID
 * @return array
 */
function edd_dashboard_pro_get_download_limits( $payment_id, $download_id, $user_id ) {
    $limit = edd_get_file_download_limit( $download_id );
    
    if ( empty( $limit ) ) {
        return array(
            'limit' => 0,
            'used' => 0,
            'remaining' => __( 'Unlimited', 'edd-customer-dashboard-pro' )
        );
    }

    $used = edd_count_file_downloads( array(
        'download_id' => $download_id,
        'user_id' => $user_id,
        'payment_id' => $payment_id
    ) );

    return array(
        'limit' => (int) $limit,
        'used' => (int) $used,
        'remaining' => max( 0, $limit - $used )
    );
}

/**
 * Get recent download history
 *
 * @param int $user_id User ID
 * @param int $limit Number of downloads to retrieve
 * @return array
 */
function edd_dashboard_pro_get_download_history( $user_id = 0, $limit = 10 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    if ( ! $user_id ) {
        return array();
    }

    $downloads = edd_get_file_download_logs( array(
        'user_id' => $user_id,
        'number' => $limit,
        'orderby' => 'date',
        'order' => 'DESC'
    ) );

    $history = array();
    foreach ( $downloads as $download ) {
        $history[] = array(
            'id' => $download->ID,
            'download_id' => $download->product_id,
            'product_name' => get_the_title( $download->product_id ),
            'file_name' => basename( $download->file_id ),
            'date' => $download->date,
            'ip' => $download->ip
        );
    }

    return $history;
}

/**
 * Check if user can download file
 *
 * @param int $user_id User ID
 * @param int $payment_id Payment ID
 * @param int $download_id Download ID
 * @param string $file_key File key
 * @return bool
 */
function edd_dashboard_pro_can_download_file( $user_id, $payment_id, $download_id, $file_key ) {
    // Check if user owns the purchase
    $payment = edd_get_payment( $payment_id );
    if ( ! $payment || (int) $payment->user_id !== (int) $user_id ) {
        return false;
    }

    // Check if purchase contains the download
    $cart_details = edd_get_payment_meta_cart_details( $payment_id );
    $has_download = false;
    
    if ( is_array( $cart_details ) ) {
        foreach ( $cart_details as $item ) {
            if ( (int) $item['id'] === (int) $download_id ) {
                $has_download = true;
                break;
            }
        }
    }

    if ( ! $has_download ) {
        return false;
    }

    // Check download limits
    $limits = edd_dashboard_pro_get_download_limits( $payment_id, $download_id, $user_id );
    if ( $limits['limit'] > 0 && $limits['used'] >= $limits['limit'] ) {
        return false;
    }

    return true;
}

/**
 * Get purchase analytics data
 *
 * @param int $user_id User ID
 * @return array
 */
function edd_dashboard_pro_get_purchase_analytics( $user_id = 0 ) {
    if ( ! $user_id ) {
        $user_id = get_current_user_id();
    }

    if ( ! $user_id ) {
        return array();
    }

    $customer_data = edd_dashboard_pro_get_current_customer();
    if ( ! $customer_data ) {
        return array();
    }

    $stats = edd_dashboard_pro_get_customer_stats( $customer_data['id'] );
    
    return array(
        'total_spent' => $stats['total_spent'],
        'total_purchases' => $stats['total_purchases'],
        'avg_order_value' => $stats['total_purchases'] > 0 ? $stats['total_spent'] / $stats['total_purchases'] : 0,
        'downloads_per_purchase' => $stats['total_purchases'] > 0 ? $stats['total_downloads'] / $stats['total_purchases'] : 0,
        'first_purchase_date' => $customer_data['date_created'],
        'customer_since_days' => floor( ( time() - strtotime( $customer_data['date_created'] ) ) / DAY_IN_SECONDS )
    );
}

/**
 * Sanitize and validate nonce
 *
 * @param string $nonce_value Nonce value
 * @param string $action Nonce action
 * @return bool
 */
function edd_dashboard_pro_verify_nonce( $nonce_value, $action ) {
    return wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce_value ) ), $action );
}

/**
 * Get plugin template directory
 *
 * @return string
 */
function edd_dashboard_pro_get_template_dir() {
    return EDD_DASHBOARD_PRO_PLUGIN_DIR . 'templates/';
}

/**
 * Get current template name
 *
 * @return string
 */
function edd_dashboard_pro_get_current_template() {
    return EDD_Dashboard_Pro()->get_option( 'template', 'default' );
}
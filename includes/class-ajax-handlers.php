<?php
/**
 * AJAX Handlers Class for EDD Customer Dashboard Pro
 * Handles all AJAX requests for dashboard functionality
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EDD Dashboard Pro AJAX Handlers Class
 */
class EDD_Dashboard_Pro_Ajax_Handlers {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Dashboard AJAX actions
        add_action( 'wp_ajax_edd_dashboard_download_file', array( $this, 'handle_download_file' ) );
        add_action( 'wp_ajax_edd_dashboard_add_to_wishlist', array( $this, 'handle_add_to_wishlist' ) );
        add_action( 'wp_ajax_edd_dashboard_remove_from_wishlist', array( $this, 'handle_remove_from_wishlist' ) );
        add_action( 'wp_ajax_edd_dashboard_get_purchase_details', array( $this, 'handle_get_purchase_details' ) );
        add_action( 'wp_ajax_edd_dashboard_activate_license', array( $this, 'handle_activate_license' ) );
        add_action( 'wp_ajax_edd_dashboard_deactivate_license', array( $this, 'handle_deactivate_license' ) );
        add_action( 'wp_ajax_edd_dashboard_refresh_data', array( $this, 'handle_refresh_data' ) );
        add_action( 'wp_ajax_edd_dashboard_load_more_purchases', array( $this, 'handle_load_more_purchases' ) );
        add_action( 'wp_ajax_edd_dashboard_get_download_history', array( $this, 'handle_get_download_history' ) );

        // File download handling (non-AJAX)
        add_action( 'init', array( $this, 'handle_file_download_request' ) );
    }

    /**
     * Handle file download AJAX request
     */
    public function handle_download_file() {
        // Verify nonce
        if ( ! EDD_Dashboard_Pro_Security::verify_ajax_nonce() ) {
            wp_die( esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ), 403 );
        }

        // Check rate limiting
        if ( ! EDD_Dashboard_Pro_Security::check_rate_limit( 'download_file', 10, 300 ) ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Too many download requests. Please wait a moment.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Get and sanitize parameters
        $payment_id = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['payment_id'] ?? '', 'int' );
        $download_id = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['download_id'] ?? '', 'int' );
        $file_key = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['file_key'] ?? '', 'key' );
        $user_id = get_current_user_id();

        // Validate inputs
        if ( ! $payment_id || ! $download_id || ! $file_key || ! $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Invalid download parameters.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Validate download access
        $validation = EDD_Dashboard_Pro_Security::validate_download_request( array(
            'payment_id' => $payment_id,
            'download_id' => $download_id,
            'file_key' => $file_key,
            'user_id' => $user_id
        ) );

        if ( is_wp_error( $validation ) ) {
            wp_send_json_error( array(
                'message' => $validation->get_error_message()
            ) );
        }

        // Generate secure download URL
        $download_url = $this->generate_secure_download_url( $payment_id, $download_id, $file_key );

        wp_send_json_success( array(
            'download_url' => $download_url,
            'message' => esc_html__( 'Download prepared successfully.', 'edd-customer-dashboard-pro' )
        ) );
    }

    /**
     * Handle add to wishlist AJAX request
     */
    public function handle_add_to_wishlist() {
        // Verify nonce
        if ( ! EDD_Dashboard_Pro_Security::verify_ajax_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_WISHLIST ) ) {
            wp_die( esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ), 403 );
        }

        // Check rate limiting
        if ( ! EDD_Dashboard_Pro_Security::check_rate_limit( 'wishlist_action', 20, 3600 ) ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Too many wishlist requests.', 'edd-customer-dashboard-pro' )
            ) );
        }

        $download_id = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['download_id'] ?? '', 'int' );
        $user_id = get_current_user_id();

        if ( ! $download_id || ! $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Invalid request parameters.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Check if download exists
        $download = get_post( $download_id );
        if ( ! $download || 'download' !== $download->post_type ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Product not found.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Add to wishlist
        $result = edd_dashboard_pro_add_to_wishlist( $download_id, $user_id );

        if ( $result ) {
            $wishlist_count = edd_dashboard_pro_get_wishlist_count( $user_id );
            
            wp_send_json_success( array(
                'message' => esc_html__( 'Added to wishlist!', 'edd-customer-dashboard-pro' ),
                'wishlist_count' => $wishlist_count,
                'button_text' => esc_html__( 'Remove from Wishlist', 'edd-customer-dashboard-pro' ),
                'action' => 'remove'
            ) );
        } else {
            wp_send_json_error( array(
                'message' => esc_html__( 'Item already in wishlist.', 'edd-customer-dashboard-pro' )
            ) );
        }
    }

    /**
     * Handle remove from wishlist AJAX request
     */
    public function handle_remove_from_wishlist() {
        // Verify nonce
        if ( ! EDD_Dashboard_Pro_Security::verify_ajax_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_WISHLIST ) ) {
            wp_die( esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ), 403 );
        }

        $download_id = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['download_id'] ?? '', 'int' );
        $user_id = get_current_user_id();

        if ( ! $download_id || ! $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Invalid request parameters.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Remove from wishlist
        $result = edd_dashboard_pro_remove_from_wishlist( $download_id, $user_id );

        if ( $result ) {
            $wishlist_count = edd_dashboard_pro_get_wishlist_count( $user_id );
            
            wp_send_json_success( array(
                'message' => esc_html__( 'Removed from wishlist!', 'edd-customer-dashboard-pro' ),
                'wishlist_count' => $wishlist_count,
                'button_text' => esc_html__( 'Add to Wishlist', 'edd-customer-dashboard-pro' ),
                'action' => 'add'
            ) );
        } else {
            wp_send_json_error( array(
                'message' => esc_html__( 'Failed to remove from wishlist.', 'edd-customer-dashboard-pro' )
            ) );
        }
    }

    /**
     * Handle get purchase details AJAX request
     */
    public function handle_get_purchase_details() {
        // Verify nonce
        if ( ! EDD_Dashboard_Pro_Security::verify_ajax_nonce() ) {
            wp_die( esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ), 403 );
        }

        $payment_id = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['payment_id'] ?? '', 'int' );
        $user_id = get_current_user_id();

        if ( ! $payment_id || ! $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Invalid request parameters.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Verify payment belongs to user
        $payment = edd_get_payment( $payment_id );
        if ( ! $payment || (int) $payment->user_id !== $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Access denied.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Get payment details
        $payment_meta = edd_get_payment_meta( $payment_id );
        $cart_details = edd_get_payment_meta_cart_details( $payment_id );
        
        $details = array(
            'id' => $payment_id,
            'status' => $payment->post_status,
            'date' => $payment->post_date,
            'total' => edd_get_payment_amount( $payment_id ),
            'currency' => edd_get_payment_currency_code( $payment_id ),
            'gateway' => edd_get_payment_gateway( $payment_id ),
            'key' => edd_get_payment_key( $payment_id ),
            'customer_info' => $payment_meta['user_info'] ?? array(),
            'billing_address' => $payment_meta['address'] ?? array(),
            'products' => array()
        );

        // Add product details
        if ( is_array( $cart_details ) ) {
            foreach ( $cart_details as $item ) {
                $download_id = $item['id'];
                $details['products'][] = array(
                    'id' => $download_id,
                    'name' => $item['name'],
                    'price' => $item['item_price'],
                    'quantity' => $item['quantity'],
                    'files' => edd_dashboard_pro_get_download_files( $download_id, $item['item_number']['options']['price_id'] ?? null ),
                    'license_key' => edd_dashboard_pro_get_license_key( $payment_id, $download_id )
                );
            }
        }

        wp_send_json_success( array(
            'details' => $details
        ) );
    }

    /**
     * Handle license activation AJAX request
     */
    public function handle_activate_license() {
        // Check if EDD Software Licensing is active
        if ( ! class_exists( 'EDD_Software_Licensing' ) ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'License management is not available.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Verify nonce
        if ( ! EDD_Dashboard_Pro_Security::verify_ajax_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_LICENSE ) ) {
            wp_die( esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ), 403 );
        }

        // Check rate limiting
        if ( ! EDD_Dashboard_Pro_Security::check_rate_limit( 'license_activation', 5, 3600 ) ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Too many license activation attempts.', 'edd-customer-dashboard-pro' )
            ) );
        }

        $license_id = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['license_id'] ?? '', 'int' );
        $site_url = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['site_url'] ?? '', 'url' );
        $user_id = get_current_user_id();

        if ( ! $license_id || ! $site_url || ! $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Invalid request parameters.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Validate site URL
        if ( ! filter_var( $site_url, FILTER_VALIDATE_URL ) ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Please enter a valid URL.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Get license
        $license = edd_software_licensing()->get_license( $license_id );
        
        if ( ! $license || (int) $license->user_id !== $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'License not found or access denied.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Attempt activation
        $activated = edd_software_licensing()->insert_site( $license_id, $site_url );

        if ( $activated ) {
            // Get updated activation count
            $activation_count = edd_software_licensing()->get_site_count( $license_id );
            $activation_limit = edd_software_licensing()->get_license_limit( $license->download_id, $license_id );
            
            wp_send_json_success( array(
                'message' => esc_html__( 'License activated successfully!', 'edd-customer-dashboard-pro' ),
                'activation_count' => $activation_count,
                'activation_limit' => $activation_limit,
                'site_url' => $site_url
            ) );
        } else {
            wp_send_json_error( array(
                'message' => esc_html__( 'License activation failed. The license may have reached its activation limit or the site is already activated.', 'edd-customer-dashboard-pro' )
            ) );
        }
    }

    /**
     * Handle license deactivation AJAX request
     */
    public function handle_deactivate_license() {
        // Check if EDD Software Licensing is active
        if ( ! class_exists( 'EDD_Software_Licensing' ) ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'License management is not available.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Verify nonce
        if ( ! EDD_Dashboard_Pro_Security::verify_ajax_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_LICENSE ) ) {
            wp_die( esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ), 403 );
        }

        $license_id = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['license_id'] ?? '', 'int' );
        $site_url = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['site_url'] ?? '', 'url' );
        $user_id = get_current_user_id();

        if ( ! $license_id || ! $site_url || ! $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Invalid request parameters.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Get license
        $license = edd_software_licensing()->get_license( $license_id );
        
        if ( ! $license || (int) $license->user_id !== $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'License not found or access denied.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Attempt deactivation
        $deactivated = edd_software_licensing()->delete_site( $license_id, $site_url );

        if ( $deactivated ) {
            // Get updated activation count
            $activation_count = edd_software_licensing()->get_site_count( $license_id );
            $activation_limit = edd_software_licensing()->get_license_limit( $license->download_id, $license_id );
            
            wp_send_json_success( array(
                'message' => esc_html__( 'License deactivated successfully!', 'edd-customer-dashboard-pro' ),
                'activation_count' => $activation_count,
                'activation_limit' => $activation_limit
            ) );
        } else {
            wp_send_json_error( array(
                'message' => esc_html__( 'License deactivation failed.', 'edd-customer-dashboard-pro' )
            ) );
        }
    }

    /**
     * Handle refresh dashboard data AJAX request
     */
    public function handle_refresh_data() {
        // Verify nonce
        if ( ! EDD_Dashboard_Pro_Security::verify_ajax_nonce() ) {
            wp_die( esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ), 403 );
        }

        $user_id = get_current_user_id();
        
        if ( ! $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'User not logged in.', 'edd-customer-dashboard-pro' )
            ) );
        }

        // Clear cached download count
        delete_user_meta( $user_id, '_edd_dashboard_pro_download_count' );

        // Get fresh data
        $customer_data = edd_dashboard_pro_get_current_customer();
        
        if ( ! $customer_data ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Customer data not found.', 'edd-customer-dashboard-pro' )
            ) );
        }

        $stats = edd_dashboard_pro_get_customer_stats( $customer_data['id'] );
        $recent_purchases = edd_dashboard_pro_get_customer_purchases( $customer_data['id'], 5 );
        $recent_downloads = edd_dashboard_pro_get_download_history( $user_id, 5 );

        wp_send_json_success( array(
            'stats' => $stats,
            'recent_purchases' => $recent_purchases,
            'recent_downloads' => $recent_downloads,
            'message' => esc_html__( 'Dashboard data refreshed successfully!', 'edd-customer-dashboard-pro' )
        ) );
    }

    /**
     * Handle load more purchases AJAX request
     */
    public function handle_load_more_purchases() {
        // Verify nonce
        if ( ! EDD_Dashboard_Pro_Security::verify_ajax_nonce() ) {
            wp_die( esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ), 403 );
        }

        $offset = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['offset'] ?? 0, 'int' );
        $number = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['number'] ?? 10, 'int' );
        $user_id = get_current_user_id();

        // Limit number to prevent abuse
        $number = min( $number, 50 );

        if ( ! $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'User not logged in.', 'edd-customer-dashboard-pro' )
            ) );
        }

        $customer_data = edd_dashboard_pro_get_current_customer();
        
        if ( ! $customer_data ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'Customer data not found.', 'edd-customer-dashboard-pro' )
            ) );
        }

        $purchases = edd_dashboard_pro_get_customer_purchases( $customer_data['id'], $number, $offset );
        $has_more = count( $purchases ) === $number;

        wp_send_json_success( array(
            'purchases' => $purchases,
            'has_more' => $has_more,
            'next_offset' => $offset + $number
        ) );
    }

    /**
     * Handle get download history AJAX request
     */
    public function handle_get_download_history() {
        // Verify nonce
        if ( ! EDD_Dashboard_Pro_Security::verify_ajax_nonce() ) {
            wp_die( esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ), 403 );
        }

        $limit = EDD_Dashboard_Pro_Security::sanitize_input( $_POST['limit'] ?? 20, 'int' );
        $user_id = get_current_user_id();

        // Limit to prevent abuse
        $limit = min( $limit, 100 );

        if ( ! $user_id ) {
            wp_send_json_error( array(
                'message' => esc_html__( 'User not logged in.', 'edd-customer-dashboard-pro' )
            ) );
        }

        $download_history = edd_dashboard_pro_get_download_history( $user_id, $limit );

        wp_send_json_success( array(
            'downloads' => $download_history
        ) );
    }

    /**
     * Handle file download request (non-AJAX)
     */
    public function handle_file_download_request() {
        // Check if this is a dashboard download request
        if ( ! isset( $_GET['edd_action'] ) || 'download_file' !== $_GET['edd_action'] ) {
            return;
        }

        // Check required parameters
        if ( ! isset( $_GET['payment_id'], $_GET['download_id'], $_GET['file_key'], $_GET['_wpnonce'] ) ) {
            wp_die( esc_html__( 'Invalid download request.', 'edd-customer-dashboard-pro' ), 400 );
        }

        // Verify nonce
        if ( ! EDD_Dashboard_Pro_Security::verify_request_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_DOWNLOAD ) ) {
            wp_die( esc_html__( 'Security check failed.', 'edd-customer-dashboard-pro' ), 403 );
        }

        // Check rate limiting
        if ( ! EDD_Dashboard_Pro_Security::check_rate_limit( 'file_download', 30, 3600 ) ) {
            wp_die( esc_html__( 'Too many download requests. Please wait before trying again.', 'edd-customer-dashboard-pro' ), 429 );
        }

        // Get and sanitize parameters
        $payment_id = EDD_Dashboard_Pro_Security::sanitize_input( $_GET['payment_id'], 'int' );
        $download_id = EDD_Dashboard_Pro_Security::sanitize_input( $_GET['download_id'], 'int' );
        $file_key = EDD_Dashboard_Pro_Security::sanitize_input( $_GET['file_key'], 'key' );
        $user_id = get_current_user_id();

        // Validate download access
        $validation = EDD_Dashboard_Pro_Security::validate_download_request( array(
            'payment_id' => $payment_id,
            'download_id' => $download_id,
            'file_key' => $file_key,
            'user_id' => $user_id
        ) );

        if ( is_wp_error( $validation ) ) {
            wp_die( esc_html( $validation->get_error_message() ), 403 );
        }

        // Get download files
        $files = edd_get_download_files( $download_id );
        
        if ( ! isset( $files[ $file_key ] ) ) {
            wp_die( esc_html__( 'File not found.', 'edd-customer-dashboard-pro' ), 404 );
        }

        $file = $files[ $file_key ];
        $file_url = $file['file'];

        // Process the download through EDD's system
        $this->process_file_download( $payment_id, $download_id, $file_key, $file_url, $user_id );
    }

    /**
     * Process file download
     *
     * @param int $payment_id Payment ID
     * @param int $download_id Download ID
     * @param string $file_key File key
     * @param string $file_url File URL
     * @param int $user_id User ID
     */
    private function process_file_download( $payment_id, $download_id, $file_key, $file_url, $user_id ) {
        // Log the download
        edd_record_download_in_log( $download_id, $file_key, array(), $payment_id, null );

        // Update download count cache
        $current_count = get_user_meta( $user_id, '_edd_dashboard_pro_download_count', true );
        update_user_meta( $user_id, '_edd_dashboard_pro_download_count', (int) $current_count + 1 );

        // Determine if file is external or local
        if ( filter_var( $file_url, FILTER_VALIDATE_URL ) && ! $this->is_local_file( $file_url ) ) {
            // External file - redirect
            wp_redirect( $file_url );
            exit;
        } else {
            // Local file - serve directly
            $this->serve_local_file( $file_url );
        }
    }

    /**
     * Check if file URL is local
     *
     * @param string $file_url File URL
     * @return bool
     */
    private function is_local_file( $file_url ) {
        $site_url = home_url();
        $upload_url = wp_upload_dir()['baseurl'];
        
        return strpos( $file_url, $site_url ) === 0 || strpos( $file_url, $upload_url ) === 0;
    }

    /**
     * Serve local file
     *
     * @param string $file_url File URL
     */
    private function serve_local_file( $file_url ) {
        // Convert URL to local path
        $upload_dir = wp_upload_dir();
        $file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $file_url );
        $file_path = str_replace( home_url(), ABSPATH, $file_path );

        // Validate file exists and is within allowed directories
        if ( ! file_exists( $file_path ) || ! $this->is_file_in_allowed_directory( $file_path ) ) {
            wp_die( esc_html__( 'File not found or access denied.', 'edd-customer-dashboard-pro' ), 404 );
        }

        // Get file info
        $file_name = basename( $file_path );
        $file_size = filesize( $file_path );
        $mime_type = wp_check_filetype( $file_name )['type'] ?: 'application/octet-stream';

        // Set headers for download
        header( 'Content-Type: ' . $mime_type );
        header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
        header( 'Content-Length: ' . $file_size );
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Expires: 0' );

        // Output file
        readfile( $file_path );
        exit;
    }

    /**
     * Check if file is in allowed directory
     *
     * @param string $file_path File path
     * @return bool
     */
    private function is_file_in_allowed_directory( $file_path ) {
        $upload_dir = wp_upload_dir()['basedir'];
        $real_file_path = realpath( $file_path );
        $real_upload_dir = realpath( $upload_dir );
        
        return $real_file_path && $real_upload_dir && strpos( $real_file_path, $real_upload_dir ) === 0;
    }

    /**
     * Generate secure download URL
     *
     * @param int $payment_id Payment ID
     * @param int $download_id Download ID
     * @param string $file_key File key
     * @return string
     */
    private function generate_secure_download_url( $payment_id, $download_id, $file_key ) {
        $args = array(
            'edd_action' => 'download_file',
            'payment_id' => $payment_id,
            'download_id' => $download_id,
            'file_key' => $file_key,
            '_wpnonce' => EDD_Dashboard_Pro_Security::create_nonce( EDD_Dashboard_Pro_Security::NONCE_ACTION_DOWNLOAD ),
            'expires' => time() + 3600 // 1 hour expiry
        );

        return add_query_arg( $args, home_url() );
    }

    /**
     * Sanitize and escape AJAX response data
     *
     * @param array $data Response data
     * @return array
     */
    private function sanitize_response_data( $data ) {
        if ( is_array( $data ) ) {
            return array_map( array( $this, 'sanitize_response_data' ), $data );
        }
        
        return is_string( $data ) ? esc_html( $data ) : $data;
    }

    /**
     * Log AJAX request for debugging
     *
     * @param string $action AJAX action
     * @param array $data Request data
     * @param string $result Request result
     */
    private function log_ajax_request( $action, $data = array(), $result = 'success' ) {
        if ( ! EDD_Dashboard_Pro()->get_option( 'enable_ajax_logging', false ) ) {
            return;
        }

        $log_entry = array(
            'timestamp' => current_time( 'mysql' ),
            'action' => $action,
            'user_id' => get_current_user_id(),
            'ip_address' => EDD_Dashboard_Pro_Security::get_client_ip(),
            'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
            'result' => $result,
            'data' => wp_json_encode( $data )
        );

        // Store in transient (consider using custom table for high-traffic sites)
        $logs = get_transient( 'edd_dashboard_pro_ajax_logs' ) ?: array();
        
        // Keep only last 100 entries
        if ( count( $logs ) >= 100 ) {
            $logs = array_slice( $logs, -90 );
        }
        
        $logs[] = $log_entry;
        set_transient( 'edd_dashboard_pro_ajax_logs', $logs, 7 * DAY_IN_SECONDS );
    }

    /**
     * Handle AJAX request wrapper with error handling
     *
     * @param callable $callback Callback function
     * @param string $action Action name
     */
    private function handle_ajax_request( $callback, $action ) {
        try {
            // Log request start
            $this->log_ajax_request( $action, $_POST, 'started' );
            
            // Execute callback
            call_user_func( $callback );
            
            // Log success
            $this->log_ajax_request( $action, $_POST, 'success' );
            
        } catch ( Exception $e ) {
            // Log error
            $this->log_ajax_request( $action, $_POST, 'error: ' . $e->getMessage() );
            
            wp_send_json_error( array(
                'message' => esc_html__( 'An unexpected error occurred. Please try again.', 'edd-customer-dashboard-pro' )
            ) );
        }
    }

    /**
     * Validate required AJAX parameters
     *
     * @param array $required_params Required parameter names
     * @return bool|WP_Error
     */
    private function validate_ajax_params( $required_params ) {
        foreach ( $required_params as $param ) {
            if ( ! isset( $_POST[ $param ] ) || empty( $_POST[ $param ] ) ) {
                return new WP_Error( 
                    'missing_param', 
                    sprintf( 
                        /* translators: %s: Parameter name */
                        esc_html__( 'Missing required parameter: %s', 'edd-customer-dashboard-pro' ), 
                        $param 
                    ) 
                );
            }
        }
        return true;
    }

    /**
     * Get formatted error response
     *
     * @param string $message Error message
     * @param string $code Error code
     * @return array
     */
    private function get_error_response( $message, $code = 'error' ) {
        return array(
            'success' => false,
            'data' => array(
                'message' => $message,
                'code' => $code
            )
        );
    }

    /**
     * Get formatted success response
     *
     * @param array $data Response data
     * @param string $message Success message
     * @return array
     */
    private function get_success_response( $data = array(), $message = '' ) {
        $response = array(
            'success' => true,
            'data' => $data
        );

        if ( $message ) {
            $response['data']['message'] = $message;
        }

        return $response;
    }

    /**
     * Check if user has access to specific payment
     *
     * @param int $payment_id Payment ID
     * @param int $user_id User ID
     * @return bool
     */
    private function user_can_access_payment( $payment_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        $payment = edd_get_payment( $payment_id );
        
        return $payment && (int) $payment->user_id === (int) $user_id;
    }

    /**
     * Format purchase data for AJAX response
     *
     * @param array $purchases Purchase data
     * @return array
     */
    private function format_purchases_for_response( $purchases ) {
        $formatted = array();

        foreach ( $purchases as $purchase ) {
            $formatted_purchase = array(
                'id' => (int) $purchase['id'],
                'date' => esc_html( date_i18n( get_option( 'date_format' ), strtotime( $purchase['date'] ) ) ),
                'status' => esc_html( $purchase['status'] ),
                'total' => esc_html( edd_dashboard_pro_format_price( $purchase['total'], $purchase['currency'] ) ),
                'products' => array()
            );

            foreach ( $purchase['products'] as $product ) {
                $formatted_purchase['products'][] = array(
                    'id' => (int) $product['id'],
                    'name' => esc_html( $product['name'] ),
                    'price' => esc_html( edd_dashboard_pro_format_price( $product['item_price'] ) ),
                    'download_files' => $this->format_download_files( $product['download_files'] ),
                    'license_key' => $product['license_key'] ? esc_html( $product['license_key'] ) : null
                );
            }

            $formatted[] = $formatted_purchase;
        }

        return $formatted;
    }

    /**
     * Format download files for response
     *
     * @param array $files Download files
     * @return array
     */
    private function format_download_files( $files ) {
        $formatted = array();

        foreach ( $files as $file ) {
            $formatted[] = array(
                'id' => esc_attr( $file['id'] ),
                'name' => esc_html( $file['name'] ),
                'download_url' => '' // Will be generated on demand for security
            );
        }

        return $formatted;
    }

    /**
     * Clean up expired download URLs
     */
    public static function cleanup_expired_download_urls() {
        // This would be used if we stored temporary download URLs
        // For now, we generate them on-demand for better security
        delete_expired_transients();
    }
}
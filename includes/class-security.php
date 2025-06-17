<?php
/**
 * Security Class for EDD Customer Dashboard Pro
 * Handles nonce verification, sanitization, and security validation
 *
 * @package EDD_Customer_Dashboard_Pro
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * EDD Dashboard Pro Security Class
 */
class EDD_Dashboard_Pro_Security {

    /**
     * Nonce actions
     */
    const NONCE_ACTION_DASHBOARD = 'edd_dashboard_pro_dashboard';
    const NONCE_ACTION_DOWNLOAD = 'edd_dashboard_pro_download';
    const NONCE_ACTION_WISHLIST = 'edd_dashboard_pro_wishlist';
    const NONCE_ACTION_LICENSE = 'edd_dashboard_pro_license';
    const NONCE_ACTION_AJAX = 'edd_dashboard_pro_ajax';

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
        add_action( 'init', array( $this, 'init_session_security' ) );
        add_action( 'wp_login', array( $this, 'handle_user_login' ), 10, 2 );
        add_action( 'wp_logout', array( $this, 'handle_user_logout' ) );
    }

    /**
     * Initialize session security
     */
    public function init_session_security() {
        // Regenerate session ID on login
        if ( is_user_logged_in() && ! wp_doing_ajax() ) {
            $this->secure_session();
        }
    }

    /**
     * Handle user login security
     *
     * @param string $user_login Username
     * @param WP_User $user User object
     */
    public function handle_user_login( $user_login, $user ) {
        // Log successful login for security audit
        $this->log_security_event( 'user_login', array(
            'user_id' => $user->ID,
            'user_login' => $user_login,
            'ip_address' => $this->get_client_ip()
        ) );
    }

    /**
     * Handle user logout
     */
    public function handle_user_logout() {
        $user_id = get_current_user_id();
        
        // Log logout event
        $this->log_security_event( 'user_logout', array(
            'user_id' => $user_id,
            'ip_address' => $this->get_client_ip()
        ) );
    }

    /**
     * Generate nonce for specific action
     *
     * @param string $action Nonce action
     * @return string
     */
    public static function create_nonce( $action ) {
        return wp_create_nonce( $action );
    }

    /**
     * Verify nonce with proper sanitization
     *
     * @param string $nonce_value Nonce value from request
     * @param string $action Nonce action
     * @param string $query_arg Query argument name (default: '_wpnonce')
     * @return bool
     */
    public static function verify_nonce( $nonce_value, $action, $query_arg = '_wpnonce' ) {
        if ( empty( $nonce_value ) ) {
            return false;
        }

        // Sanitize nonce value with proper unslashing
        $sanitized_nonce = sanitize_text_field( wp_unslash( $nonce_value ) );
        
        return wp_verify_nonce( $sanitized_nonce, $action );
    }

    /**
     * Verify nonce from REQUEST data
     *
     * @param string $action Nonce action
     * @param string $query_arg Query argument name
     * @return bool
     */
    public static function verify_request_nonce( $action, $query_arg = '_wpnonce' ) {
        $nonce_value = '';
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( isset( $_REQUEST[ $query_arg ] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $nonce_value = sanitize_text_field( wp_unslash( $_REQUEST[ $query_arg ] ) );
        }

        return self::verify_nonce( $nonce_value, $action, $query_arg );
    }

    /**
     * Verify AJAX nonce
     *
     * @param string $action Nonce action (optional, defaults to AJAX action)
     * @return bool
     */
    public static function verify_ajax_nonce( $action = null ) {
        if ( ! $action ) {
            $action = self::NONCE_ACTION_AJAX;
        }

        // Check nonce from various possible locations
        $nonce_value = '';
        
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( isset( $_POST['_wpnonce'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Missing
            $nonce_value = sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) );
        } 
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        elseif ( isset( $_GET['_wpnonce'] ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $nonce_value = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );
        }
        // Check for nonce in headers (for modern AJAX)
        elseif ( function_exists( 'getallheaders' ) ) {
            $headers = getallheaders();
            if ( isset( $headers['X-WP-Nonce'] ) ) {
                $nonce_value = sanitize_text_field( $headers['X-WP-Nonce'] );
            }
        }

        return self::verify_nonce( $nonce_value, $action );
    }

    /**
     * Sanitize input data
     *
     * @param mixed $data Input data
     * @param string $type Sanitization type
     * @return mixed
     */
    public static function sanitize_input( $data, $type = 'text' ) {
        if ( is_array( $data ) ) {
            return array_map( function( $item ) use ( $type ) {
                return self::sanitize_input( $item, $type );
            }, $data );
        }

        switch ( $type ) {
            case 'email':
                return sanitize_email( $data );
            
            case 'url':
                return esc_url_raw( $data );
            
            case 'int':
            case 'integer':
                return (int) $data;
            
            case 'float':
                return (float) $data;
            
            case 'bool':
            case 'boolean':
                return (bool) $data;
            
            case 'textarea':
                return sanitize_textarea_field( $data );
            
            case 'html':
                return wp_kses_post( $data );
            
            case 'key':
                return sanitize_key( $data );
            
            case 'slug':
                return sanitize_title( $data );
            
            case 'text':
            default:
                return sanitize_text_field( $data );
        }
    }

    /**
     * Validate user permissions for dashboard access
     *
     * @param int $user_id User ID (optional, defaults to current user)
     * @return bool
     */
    public static function can_access_dashboard( $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id ) {
            return false;
        }

        // Check if user has made at least one purchase
        $customer = new EDD_Customer( $user_id, true );
        
        return $customer && $customer->id > 0;
    }

    /**
     * Validate download access
     *
     * @param int $payment_id Payment ID
     * @param int $download_id Download ID
     * @param int $user_id User ID
     * @return bool
     */
    public static function can_download_file( $payment_id, $download_id, $user_id = 0 ) {
        if ( ! $user_id ) {
            $user_id = get_current_user_id();
        }

        if ( ! $user_id || ! $payment_id || ! $download_id ) {
            return false;
        }

        // Verify payment belongs to user
        $payment = edd_get_payment( $payment_id );
        if ( ! $payment || (int) $payment->user_id !== (int) $user_id ) {
            return false;
        }

        // Check if payment status is complete
        if ( 'publish' !== $payment->post_status && 'complete' !== $payment->post_status ) {
            return false;
        }

        // Verify download is in the payment
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

        return $has_download;
    }

    /**
     * Rate limiting for AJAX requests
     *
     * @param string $action Action name
     * @param int $limit Number of requests allowed
     * @param int $time_window Time window in seconds
     * @return bool
     */
    public static function check_rate_limit( $action, $limit = 60, $time_window = 3600 ) {
        $user_id = get_current_user_id();
        $ip_address = self::get_client_ip();
        
        // Create unique key for this user/IP and action
        $key = 'edd_dashboard_rate_limit_' . md5( $user_id . $ip_address . $action );
        
        $requests = get_transient( $key );
        
        if ( false === $requests ) {
            // First request in time window
            set_transient( $key, 1, $time_window );
            return true;
        }
        
        if ( $requests >= $limit ) {
            return false;
        }
        
        // Increment counter
        set_transient( $key, $requests + 1, $time_window );
        return true;
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    public static function get_client_ip() {
        $ip_keys = array(
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ( $ip_keys as $key ) {
            if ( array_key_exists( $key, $_SERVER ) === true ) {
                $ip_list = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
                $ip = trim( $ip_list[0] );
                
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                    return $ip;
                }
            }
        }

        return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    }

    /**
     * Secure current session
     */
    private function secure_session() {
        if ( ! session_id() ) {
            return;
        }

        // Check if session needs regeneration
        $last_regeneration = get_user_meta( get_current_user_id(), '_edd_dashboard_session_regenerated', true );
        
        if ( ! $last_regeneration || ( time() - $last_regeneration ) > 1800 ) { // 30 minutes
            session_regenerate_id( true );
            update_user_meta( get_current_user_id(), '_edd_dashboard_session_regenerated', time() );
        }
    }

    /**
     * Log security events
     *
     * @param string $event Event type
     * @param array $data Event data
     */
    private function log_security_event( $event, $data = array() ) {
        if ( ! EDD_Dashboard_Pro()->get_option( 'enable_security_logging', true ) ) {
            return;
        }

        $log_data = array_merge( array(
            'event' => $event,
            'timestamp' => current_time( 'mysql' ),
            'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
            'request_uri' => isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : ''
        ), $data );

        // Store in WordPress options (consider using custom table for high-traffic sites)
        $security_logs = get_option( 'edd_dashboard_pro_security_logs', array() );
        
        // Keep only last 1000 entries
        if ( count( $security_logs ) >= 1000 ) {
            $security_logs = array_slice( $security_logs, -900 );
        }
        
        $security_logs[] = $log_data;
        update_option( 'edd_dashboard_pro_security_logs', $security_logs );
    }

    /**
     * Validate file download request
     *
     * @param array $args Download arguments
     * @return bool|WP_Error
     */
    public static function validate_download_request( $args ) {
        $required_args = array( 'payment_id', 'download_id', 'file_key', 'user_id' );
        
        foreach ( $required_args as $arg ) {
            if ( empty( $args[ $arg ] ) ) {
                return new WP_Error( 'missing_arg', sprintf( 
                    /* translators: %s: Required argument name */
                    __( 'Missing required argument: %s', 'edd-customer-dashboard-pro' ), 
                    $arg 
                ) );
            }
        }

        // Sanitize arguments
        $args['payment_id'] = (int) $args['payment_id'];
        $args['download_id'] = (int) $args['download_id'];
        $args['user_id'] = (int) $args['user_id'];
        $args['file_key'] = sanitize_key( $args['file_key'] );

        // Validate payment and download access
        if ( ! self::can_download_file( $args['payment_id'], $args['download_id'], $args['user_id'] ) ) {
            return new WP_Error( 'access_denied', __( 'Access denied for this download.', 'edd-customer-dashboard-pro' ) );
        }

        // Check download limits
        $limits = edd_dashboard_pro_get_download_limits( $args['payment_id'], $args['download_id'], $args['user_id'] );
        if ( $limits['limit'] > 0 && $limits['used'] >= $limits['limit'] ) {
            return new WP_Error( 'download_limit_exceeded', __( 'Download limit exceeded for this file.', 'edd-customer-dashboard-pro' ) );
        }

        return true;
    }

    /**
     * Escape output for display
     *
     * @param mixed $data Data to escape
     * @param string $context Context for escaping
     * @return mixed
     */
    public static function escape_output( $data, $context = 'html' ) {
        if ( is_array( $data ) ) {
            return array_map( function( $item ) use ( $context ) {
                return self::escape_output( $item, $context );
            }, $data );
        }

        switch ( $context ) {
            case 'attr':
            case 'attribute':
                return esc_attr( $data );
            
            case 'url':
                return esc_url( $data );
            
            case 'js':
            case 'javascript':
                return esc_js( $data );
            
            case 'textarea':
                return esc_textarea( $data );
            
            case 'html':
            default:
                return esc_html( $data );
        }
    }

    /**
     * Clean up expired security logs
     */
    public static function cleanup_security_logs() {
        $logs = get_option( 'edd_dashboard_pro_security_logs', array() );
        $cutoff_time = strtotime( '-30 days' );
        
        $filtered_logs = array_filter( $logs, function( $log ) use ( $cutoff_time ) {
            return strtotime( $log['timestamp'] ) > $cutoff_time;
        } );
        
        update_option( 'edd_dashboard_pro_security_logs', array_values( $filtered_logs ) );
    }
}

// Schedule cleanup of security logs
if ( ! wp_next_scheduled( 'edd_dashboard_pro_cleanup_security_logs' ) ) {
    wp_schedule_event( time(), 'daily', 'edd_dashboard_pro_cleanup_security_logs' );
}

add_action( 'edd_dashboard_pro_cleanup_security_logs', array( 'EDD_Dashboard_Pro_Security', 'cleanup_security_logs' ) );
<?php
/**
 * Support Section Template
 * 
 * Displays customer support options, help resources, and ticket management
 *
 * @package EDD_Customer_Dashboard_Pro
 * @template default
 * @section support
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Check if support is enabled
if ( ! ( $settings['enable_support'] ?? true ) ) {
    ?>
    <div class="edd-content-section" id="edd-section-support">
        <div class="edd-empty-state">
            <div class="edd-empty-icon">💬</div>
            <h3><?php esc_html_e( 'Support Disabled', 'edd-customer-dashboard-pro' ); ?></h3>
            <p><?php esc_html_e( 'Customer support features are currently disabled.', 'edd-customer-dashboard-pro' ); ?></p>
        </div>
    </div>
    <?php
    return;
}
?>

<!-- Support Section -->
<div class="edd-content-section" id="edd-section-support" role="tabpanel" aria-labelledby="edd-tab-support">
    
    <div class="edd-section-header">
        <h2 class="edd-section-title"><?php esc_html_e( 'Support Center', 'edd-customer-dashboard-pro' ); ?></h2>
        <p class="edd-section-description">
            <?php esc_html_e( 'Get help, browse documentation, and manage your support requests.', 'edd-customer-dashboard-pro' ); ?>
        </p>
    </div>

    <!-- Quick Help Options -->
    <div class="edd-support-quick-help">
        <h3 class="edd-quick-help-title"><?php esc_html_e( 'Quick Help', 'edd-customer-dashboard-pro' ); ?></h3>
        
        <div class="edd-support-grid">
            
            <!-- Documentation -->
            <div class="edd-support-card">
                <div class="edd-support-icon">📚</div>
                <div class="edd-support-content">
                    <h4><?php esc_html_e( 'Documentation', 'edd-customer-dashboard-pro' ); ?></h4>
                    <p><?php esc_html_e( 'Browse our comprehensive documentation and user guides to find answers to common questions.', 'edd-customer-dashboard-pro' ); ?></p>
                    <div class="edd-support-actions">
                        <?php if ( ! empty( $support_urls['documentation'] ) ) : ?>
                            <a href="<?php echo esc_url( $support_urls['documentation'] ); ?>" 
                               class="edd-btn edd-btn-primary" 
                               target="_blank">
                                <?php esc_html_e( 'Browse Docs', 'edd-customer-dashboard-pro' ); ?>
                            </a>
                        <?php else : ?>
                            <button type="button" class="edd-btn edd-btn-secondary" disabled>
                                <?php esc_html_e( 'Coming Soon', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Video Tutorials -->
            <div class="edd-support-card">
                <div class="edd-support-icon">🎥</div>
                <div class="edd-support-content">
                    <h4><?php esc_html_e( 'Video Tutorials', 'edd-customer-dashboard-pro' ); ?></h4>
                    <p><?php esc_html_e( 'Watch step-by-step video tutorials and walkthroughs to learn how to use your products effectively.', 'edd-customer-dashboard-pro' ); ?></p>
                    <div class="edd-support-actions">
                        <?php if ( ! empty( $support_urls['videos'] ) ) : ?>
                            <a href="<?php echo esc_url( $support_urls['videos'] ); ?>" 
                               class="edd-btn edd-btn-primary" 
                               target="_blank">
                                <?php esc_html_e( 'Watch Videos', 'edd-customer-dashboard-pro' ); ?>
                            </a>
                        <?php else : ?>
                            <button type="button" class="edd-btn edd-btn-secondary" disabled>
                                <?php esc_html_e( 'Coming Soon', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- FAQ -->
            <div class="edd-support-card">
                <div class="edd-support-icon">❓</div>
                <div class="edd-support-content">
                    <h4><?php esc_html_e( 'Frequently Asked Questions', 'edd-customer-dashboard-pro' ); ?></h4>
                    <p><?php esc_html_e( 'Find quick answers to the most commonly asked questions from our community.', 'edd-customer-dashboard-pro' ); ?></p>
                    <div class="edd-support-actions">
                        <?php if ( ! empty( $support_urls['faq'] ) ) : ?>
                            <a href="<?php echo esc_url( $support_urls['faq'] ); ?>" 
                               class="edd-btn edd-btn-primary" 
                               target="_blank">
                                <?php esc_html_e( 'View FAQ', 'edd-customer-dashboard-pro' ); ?>
                            </a>
                        <?php else : ?>
                            <button type="button" class="edd-btn edd-btn-primary edd-show-faq">
                                <?php esc_html_e( 'View FAQ', 'edd-customer-dashboard-pro' ); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Contact Support -->
            <div class="edd-support-card">
                <div class="edd-support-icon">💬</div>
                <div class="edd-support-content">
                    <h4><?php esc_html_e( 'Contact Support', 'edd-customer-dashboard-pro' ); ?></h4>
                    <p><?php esc_html_e( 'Can\'t find what you\'re looking for? Contact our support team for personalized assistance.', 'edd-customer-dashboard-pro' ); ?></p>
                    <div class="edd-support-actions">
                        <button type="button" class="edd-btn edd-btn-primary edd-contact-support">
                            <?php esc_html_e( 'Create Ticket', 'edd-customer-dashboard-pro' ); ?>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Support Tickets -->
    <?php
    $recent_tickets = apply_filters( 'edd_dashboard_pro_recent_support_tickets', array(), $current_user->ID );
    ?>
    <div class="edd-support-tickets-section">
        <div class="edd-tickets-header">
            <h3 class="edd-tickets-title"><?php esc_html_e( 'Your Support Tickets', 'edd-customer-dashboard-pro' ); ?></h3>
            
            <?php if ( $settings['show_ticket_filters'] ?? true ) : ?>
                <div class="edd-ticket-filters">
                    <select id="ticket-status-filter" class="edd-filter-select">
                        <option value=""><?php esc_html_e( 'All Tickets', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="open"><?php esc_html_e( 'Open', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="pending"><?php esc_html_e( 'Pending', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="resolved"><?php esc_html_e( 'Resolved', 'edd-customer-dashboard-pro' ); ?></option>
                        <option value="closed"><?php esc_html_e( 'Closed', 'edd-customer-dashboard-pro' ); ?></option>
                    </select>
                </div>
            <?php endif; ?>
        </div>

        <?php if ( ! empty( $recent_tickets ) ) : ?>
            <div class="edd-ticket-list">
                <?php foreach ( $recent_tickets as $ticket ) : ?>
                    <div class="edd-ticket-item" data-ticket-id="<?php echo esc_attr( $ticket['id'] ?? '' ); ?>">
                        
                        <!-- Ticket Header -->
                        <div class="edd-ticket-header">
                            <div class="edd-ticket-info">
                                <h4 class="edd-ticket-subject">
                                    <?php if ( ! empty( $ticket['url'] ) ) : ?>
                                        <a href="<?php echo esc_url( $ticket['url'] ); ?>" target="_blank">
                                            <?php echo esc_html( $ticket['subject'] ); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo esc_html( $ticket['subject'] ); ?>
                                    <?php endif; ?>
                                </h4>
                                
                                <div class="edd-ticket-meta">
                                    <span class="edd-ticket-id">
                                        <?php 
                                        printf(
                                            /* translators: %s: Ticket ID */
                                            esc_html__( 'Ticket #%s', 'edd-customer-dashboard-pro' ),
                                            esc_html( $ticket['id'] ?? 'N/A' )
                                        );
                                        ?>
                                    </span>
                                    
                                    <span class="edd-ticket-date">
                                        📅 <?php echo esc_html( date_i18n( $settings['date_format'], strtotime( $ticket['date'] ) ) ); ?>
                                    </span>
                                    
                                    <span class="edd-ticket-category">
                                        🏷️ <?php echo esc_html( $ticket['category'] ?? __( 'General', 'edd-customer-dashboard-pro' ) ); ?>
                                    </span>
                                    
                                    <span class="edd-ticket-priority">
                                        <?php
                                        $priority_icons = array(
                                            'low' => '🔵',
                                            'normal' => '🟡',
                                            'high' => '🟠',
                                            'urgent' => '🔴'
                                        );
                                        $priority = $ticket['priority'] ?? 'normal';
                                        echo esc_html( $priority_icons[ $priority ] ?? '🟡' );
                                        echo ' ' . esc_html( ucfirst( $priority ) );
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="edd-ticket-status-wrapper">
                                <span class="edd-status-badge edd-ticket-status-<?php echo esc_attr( sanitize_html_class( $ticket['status'] ) ); ?>">
                                    <?php echo esc_html( ucfirst( $ticket['status'] ) ); ?>
                                </span>
                                
                                <?php if ( $ticket['status'] === 'open' || $ticket['status'] === 'pending' ) : ?>
                                    <span class="edd-status-indicator active" title="<?php esc_attr_e( 'Active ticket', 'edd-customer-dashboard-pro' ); ?>">●</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Ticket Preview -->
                        <div class="edd-ticket-preview">
                            <?php if ( ! empty( $ticket['excerpt'] ) ) : ?>
                                <p class="edd-ticket-excerpt">
                                    <?php echo esc_html( wp_trim_words( $ticket['excerpt'], 25 ) ); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Ticket Stats -->
                        <div class="edd-ticket-stats">
                            <?php if ( ! empty( $ticket['reply_count'] ) ) : ?>
                                <span class="edd-ticket-replies">
                                    💬 <?php 
                                    printf(
                                        /* translators: %d: Number of replies */
                                        esc_html__( '%d replies', 'edd-customer-dashboard-pro' ),
                                        esc_html( $ticket['reply_count'] )
                                    );
                                    ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ( ! empty( $ticket['last_reply'] ) ) : ?>
                                <span class="edd-ticket-last-reply">
                                    🕒 <?php 
                                    printf(
                                        /* translators: %s: Time since last reply */
                                        esc_html__( 'Last reply: %s ago', 'edd-customer-dashboard-pro' ),
                                        esc_html( human_time_diff( strtotime( $ticket['last_reply'] ), current_time( 'timestamp' ) ) )
                                    );
                                    ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ( ! empty( $ticket['assigned_to'] ) ) : ?>
                                <span class="edd-ticket-assigned">
                                    👤 <?php 
                                    printf(
                                        /* translators: %s: Support agent name */
                                        esc_html__( 'Assigned to: %s', 'edd-customer-dashboard-pro' ),
                                        esc_html( $ticket['assigned_to'] )
                                    );
                                    ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Ticket Actions -->
                        <div class="edd-ticket-actions">
                            <?php if ( ! empty( $ticket['url'] ) ) : ?>
                                <a href="<?php echo esc_url( $ticket['url'] ); ?>" 
                                   class="edd-btn edd-btn-primary edd-btn-small" 
                                   target="_blank">
                                    👁️ <?php esc_html_e( 'View Ticket', 'edd-customer-dashboard-pro' ); ?>
                                </a>
                            <?php endif; ?>
                            
                            <?php if ( $ticket['status'] === 'open' || $ticket['status'] === 'pending' ) : ?>
                                <button type="button" 
                                        class="edd-btn edd-btn-secondary edd-btn-small edd-reply-ticket"
                                        data-ticket-id="<?php echo esc_attr( $ticket['id'] ?? '' ); ?>">
                                    ↩️ <?php esc_html_e( 'Reply', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                            <?php endif; ?>
                            
                            <?php if ( $ticket['status'] === 'resolved' ) : ?>
                                <button type="button" 
                                        class="edd-btn edd-btn-success edd-btn-small edd-close-ticket"
                                        data-ticket-id="<?php echo esc_attr( $ticket['id'] ?? '' ); ?>"
                                        data-nonce="<?php echo esc_attr( $nonces['ajax'] ); ?>">
                                    ✅ <?php esc_html_e( 'Mark Closed', 'edd-customer-dashboard-pro' ); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Load More Tickets -->
            <?php if ( count( $recent_tickets ) >= 5 ) : ?>
                <div class="edd-load-more-wrapper">
                    <button type="button" 
                            class="edd-btn edd-btn-secondary edd-load-more-tickets" 
                            data-offset="<?php echo esc_attr( count( $recent_tickets ) ); ?>"
                            data-nonce="<?php echo esc_attr( $nonces['ajax'] ); ?>">
                        <?php esc_html_e( 'Load More Tickets', 'edd-customer-dashboard-pro' ); ?>
                    </button>
                </div>
            <?php endif; ?>
            
        <?php else : ?>
            <!-- No Tickets -->
            <div class="edd-no-tickets">
                <div class="edd-no-tickets-icon">🎫</div>
                <h4><?php esc_html_e( 'No support tickets yet', 'edd-customer-dashboard-pro' ); ?></h4>
                <p><?php esc_html_e( 'When you create support tickets, they\'ll appear here for easy tracking.', 'edd-customer-dashboard-pro' ); ?></p>
                <button type="button" class="edd-btn edd-btn-primary edd-contact-support">
                    💬 <?php esc_html_e( 'Create Your First Ticket', 'edd-customer-dashboard-pro' ); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Built-in FAQ Section -->
    <div class="edd-support-faq" style="<?php echo empty( $support_urls['faq'] ) ? '' : 'display: none;'; ?>">
        <h3 class="edd-faq-title"><?php esc_html_e( 'Frequently Asked Questions', 'edd-customer-dashboard-pro' ); ?></h3>
        
        <div class="edd-faq-list">
            
            <!-- Account & Login -->
            <div class="edd-faq-category">
                <h4 class="edd-faq-category-title"><?php esc_html_e( 'Account & Login', 'edd-customer-dashboard-pro' ); ?></h4>
                
                <details class="edd-faq-item">
                    <summary><?php esc_html_e( 'How do I reset my password?', 'edd-customer-dashboard-pro' ); ?></summary>
                    <div class="edd-faq-answer">
                        <p><?php esc_html_e( 'You can reset your password by clicking the "Lost Password?" link on the login page. Enter your email address and we\'ll send you a reset link.', 'edd-customer-dashboard-pro' ); ?></p>
                    </div>
                </details>
                
                <details class="edd-faq-item">
                    <summary><?php esc_html_e( 'Can I change my email address?', 'edd-customer-dashboard-pro' ); ?></summary>
                    <div class="edd-faq-answer">
                        <p><?php esc_html_e( 'Yes, you can update your email address in your account settings. After changing it, you\'ll need to verify the new email address.', 'edd-customer-dashboard-pro' ); ?></p>
                    </div>
                </details>
            </div>

            <!-- Downloads & Files -->
            <div class="edd-faq-category">
                <h4 class="edd-faq-category-title"><?php esc_html_e( 'Downloads & Files', 'edd-customer-dashboard-pro' ); ?></h4>
                
                <details class="edd-faq-item">
                    <summary><?php esc_html_e( 'How do I download my purchased files?', 'edd-customer-dashboard-pro' ); ?></summary>
                    <div class="edd-faq-answer">
                        <p><?php esc_html_e( 'After completing your purchase, go to the Downloads section of your dashboard. You\'ll find download buttons for all your purchased files.', 'edd-customer-dashboard-pro' ); ?></p>
                    </div>
                </details>
                
                <details class="edd-faq-item">
                    <summary><?php esc_html_e( 'Is there a download limit?', 'edd-customer-dashboard-pro' ); ?></summary>
                    <div class="edd-faq-answer">
                        <p><?php esc_html_e( 'Download limits vary by product. Most products allow unlimited downloads, but some may have restrictions. Check the product page or your purchase details for specific limits.', 'edd-customer-dashboard-pro' ); ?></p>
                    </div>
                </details>
                
                <details class="edd-faq-item">
                    <summary><?php esc_html_e( 'My download link isn\'t working. What should I do?', 'edd-customer-dashboard-pro' ); ?></summary>
                    <div class="edd-faq-answer">
                        <p><?php esc_html_e( 'Try refreshing your browser or clearing your cache. If the problem persists, please contact our support team with your order details.', 'edd-customer-dashboard-pro' ); ?></p>
                    </div>
                </details>
            </div>

            <!-- Licensing -->
            <?php if ( class_exists( 'EDD_Software_Licensing' ) ) : ?>
                <div class="edd-faq-category">
                    <h4 class="edd-faq-category-title"><?php esc_html_e( 'License Management', 'edd-customer-dashboard-pro' ); ?></h4>
                    
                    <details class="edd-faq-item">
                        <summary><?php esc_html_e( 'How do I activate my license?', 'edd-customer-dashboard-pro' ); ?></summary>
                        <div class="edd-faq-answer">
                            <p><?php esc_html_e( 'Go to the Licenses section, copy your license key, and enter it in your software\'s license activation area along with your site URL.', 'edd-customer-dashboard-pro' ); ?></p>
                        </div>
                    </details>
                    
                    <details class="edd-faq-item">
                        <summary><?php esc_html_e( 'Can I use my license on multiple sites?', 'edd-customer-dashboard-pro' ); ?></summary>
                        <div class="edd-faq-answer">
                            <p><?php esc_html_e( 'License activation limits depend on your purchase. Single-site licenses work on one site, while multi-site licenses allow multiple activations. Check your license details for specific limits.', 'edd-customer-dashboard-pro' ); ?></p>
                        </div>
                    </details>
                    
                    <details class="edd-faq-item">
                        <summary><?php esc_html_e( 'What happens when my license expires?', 'edd-customer-dashboard-pro' ); ?></summary>
                        <div class="edd-faq-answer">
                            <p><?php esc_html_e( 'When your license expires, you\'ll still be able to use the software, but you won\'t receive updates or support. Renew your license to continue getting updates.', 'edd-customer-dashboard-pro' ); ?></p>
                        </div>
                    </details>
                </div>
            <?php endif; ?>

            <!-- Orders & Payments -->
            <div class="edd-faq-category">
                <h4 class="edd-faq-category-title"><?php esc_html_e( 'Orders & Payments', 'edd-customer-dashboard-pro' ); ?></h4>
                
                <details class="edd-faq-item">
                    <summary><?php esc_html_e( 'Can I get a refund?', 'edd-customer-dashboard-pro' ); ?></summary>
                    <div class="edd-faq-answer">
                        <p><?php esc_html_e( 'We offer refunds within 30 days of purchase for most products. Please contact our support team with your order details to request a refund.', 'edd-customer-dashboard-pro' ); ?></p>
                    </div>
                </details>
                
                <details class="edd-faq-item">
                    <summary><?php esc_html_e( 'I haven\'t received my purchase confirmation email', 'edd-customer-dashboard-pro' ); ?></summary>
                    <div class="edd-faq-answer">
                        <p><?php esc_html_e( 'Check your spam folder first. If you still don\'t see it, contact support with your payment details and we\'ll resend your confirmation.', 'edd-customer-dashboard-pro' ); ?></p>
                    </div>
                </details>
                
                <details class="edd-faq-item">
                    <summary><?php esc_html_e( 'Can I change my payment method for renewals?', 'edd-customer-dashboard-pro' ); ?></summary>
                    <div class="edd-faq-answer">
                        <p><?php esc_html_e( 'Yes, you can update your payment method in your account settings. This will be used for future renewals and purchases.', 'edd-customer-dashboard-pro' ); ?></p>
                    </div>
                </details>
            </div>

        </div>
    </div>

    <!-- Support Statistics -->
    <?php if ( $settings['show_support_stats'] ?? false ) : ?>
        <div class="edd-support-stats">
            <h3 class="edd-support-stats-title"><?php esc_html_e( 'Support Statistics', 'edd-customer-dashboard-pro' ); ?></h3>
            
            <div class="edd-support-stats-grid">
                <div class="edd-support-stat">
                    <div class="edd-stat-number"><?php echo esc_html( $support_stats['avg_response_time'] ?? '< 24 hours' ); ?></div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Avg Response Time', 'edd-customer-dashboard-pro' ); ?></div>
                </div>
                
                <div class="edd-support-stat">
                    <div class="edd-stat-number"><?php echo esc_html( $support_stats['satisfaction_rate'] ?? '98%' ); ?></div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Satisfaction Rate', 'edd-customer-dashboard-pro' ); ?></div>
                </div>
                
                <div class="edd-support-stat">
                    <div class="edd-stat-number"><?php echo esc_html( $support_stats['tickets_resolved'] ?? '1,247' ); ?></div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Tickets Resolved', 'edd-customer-dashboard-pro' ); ?></div>
                </div>
                
                <div class="edd-support-stat">
                    <div class="edd-stat-number"><?php echo esc_html( $support_stats['support_hours'] ?? '24/7' ); ?></div>
                    <div class="edd-stat-label"><?php esc_html_e( 'Support Hours', 'edd-customer-dashboard-pro' ); ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Contact Information -->
    <div class="edd-support-contact">
        <h3 class="edd-contact-title"><?php esc_html_e( 'Other Ways to Reach Us', 'edd-customer-dashboard-pro' ); ?></h3>
        
        <div class="edd-contact-methods">
            
            <?php if ( ! empty( $support_urls['email'] ) ) : ?>
                <div class="edd-contact-method">
                    <div class="edd-contact-icon">📧</div>
                    <div class="edd-contact-info">
                        <h4><?php esc_html_e( 'Email Support', 'edd-customer-dashboard-pro' ); ?></h4>
                        <p>
                            <a href="mailto:<?php echo esc_attr( $support_urls['email'] ); ?>">
                                <?php echo esc_html( $support_urls['email'] ); ?>
                            </a>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ( ! empty( $support_urls['phone'] ) ) : ?>
                <div class="edd-contact-method">
                    <div class="edd-contact-icon">📞</div>
                    <div class="edd-contact-info">
                        <h4><?php esc_html_e( 'Phone Support', 'edd-customer-dashboard-pro' ); ?></h4>
                        <p>
                            <a href="tel:<?php echo esc_attr( $support_urls['phone'] ); ?>">
                                <?php echo esc_html( $support_urls['phone'] ); ?>
                            </a>
                        </p>
                        <small><?php esc_html_e( 'Monday - Friday, 9AM - 5PM EST', 'edd-customer-dashboard-pro' ); ?></small>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ( ! empty( $support_urls['chat'] ) ) : ?>
                <div class="edd-contact-method">
                    <div class="edd-contact-icon">💬</div>
                    <div class="edd-contact-info">
                        <h4><?php esc_html_e( 'Live Chat', 'edd-customer-dashboard-pro' ); ?></h4>
                        <p>
                            <a href="<?php echo esc_url( $support_urls['chat'] ); ?>" target="_blank">
                                <?php esc_html_e( 'Start Live Chat', 'edd-customer-dashboard-pro' ); ?>
                            </a>
                        </p>
                        <small><?php esc_html_e( 'Available during business hours', 'edd-customer-dashboard-pro' ); ?></small>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ( ! empty( $support_urls['community'] ) ) : ?>
                <div class="edd-contact-method">
                    <div class="edd-contact-icon">👥</div>
                    <div class="edd-contact-info">
                        <h4><?php esc_html_e( 'Community Forum', 'edd-customer-dashboard-pro' ); ?></h4>
                        <p>
                            <a href="<?php echo esc_url( $support_urls['community'] ); ?>" target="_blank">
                                <?php esc_html_e( 'Join the Discussion', 'edd-customer-dashboard-pro' ); ?>
                            </a>
                        </p>
                        <small><?php esc_html_e( 'Connect with other users', 'edd-customer-dashboard-pro' ); ?></small>
                    </div>
                </div>
            <?php endif; ?>
            
        </div>
    </div>

</div>

<?php
// Hook for additional support content
do_action( 'edd_dashboard_pro_after_support_section', $recent_tickets, $settings );
?>
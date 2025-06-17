/**
 * Default Template JavaScript for EDD Customer Dashboard Pro
 * 
 * Handles all interactive functionality including tab navigation,
 * AJAX requests, modals, and dynamic content loading
 *
 * @package EDD_Customer_Dashboard_Pro
 * @template default
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Main Dashboard object
     */
    window.eddDashboardPro = {
        
        // Configuration
        config: {
            ajaxUrl: '',
            nonces: {},
            currentUser: 0,
            itemsPerPage: 10,
            loadingClass: 'loading',
            activeClass: 'active',
            hiddenClass: 'edd-hidden'
        },

        // Cache DOM elements
        cache: {
            $body: null,
            $wrapper: null,
            $navTabs: null,
            $contentSections: null,
            $loadingOverlay: null,
            $modals: {}
        },

        // Current state
        state: {
            currentSection: 'purchases',
            isLoading: false,
            loadedData: {}
        },

        /**
         * Initialize the dashboard
         */
        init: function() {
            this.setupConfig();
            this.cacheElements();
            this.bindEvents();
            this.setupInitialState();
            this.loadInitialData();
            
            console.log('EDD Dashboard Pro initialized');
        },

        /**
         * Setup configuration from DOM
         */
        setupConfig: function() {
            this.config.ajaxUrl = $('#edd-ajax-url').val() || window.eddDashboardPro?.ajaxUrl || ajaxurl;
            this.config.currentUser = $('#edd-current-user-id').val() || 0;
            this.config.itemsPerPage = parseInt($('#edd-items-per-page').val()) || 10;
            
            // Setup nonces
            this.config.nonces = {
                ajax: $('#edd-ajax-nonce').val() || window.eddDashboardPro?.nonce || '',
                download: $('#edd-download-nonce').val() || '',
                wishlist: $('#edd-wishlist-nonce').val() || '',
                license: $('#edd-license-nonce').val() || ''
            };

            // Merge with global config if available
            if (window.eddDashboardPro && window.eddDashboardPro.settings) {
                $.extend(this.config, window.eddDashboardPro.settings);
            }
        },

        /**
         * Cache DOM elements
         */
        cacheElements: function() {
            this.cache.$body = $('body');
            this.cache.$wrapper = $('.edd-customer-dashboard-pro-wrapper');
            this.cache.$navTabs = $('.edd-nav-tab');
            this.cache.$contentSections = $('.edd-content-section');
            this.cache.$loadingOverlay = $('.edd-loading-overlay');
            
            // Cache modals
            this.cache.$modals = {
                purchaseDetails: $('#edd-purchase-details-modal'),
                support: $('#edd-support-modal')
            };
        },

        /**
         * Bind all event handlers
         */
        bindEvents: function() {
            // Tab navigation
            this.cache.$navTabs.on('click', this.handleTabClick.bind(this));
            
            // Download buttons
            $(document).on('click', '.edd-btn-download', this.handleDownloadClick.bind(this));
            
            // Wishlist actions
            $(document).on('click', '.edd-add-to-wishlist', this.handleAddToWishlist.bind(this));
            $(document).on('click', '.edd-remove-from-wishlist', this.handleRemoveFromWishlist.bind(this));
            
            // License management
            $(document).on('click', '.edd-activate-license', this.handleLicenseActivation.bind(this));
            $(document).on('click', '.edd-deactivate-license', this.handleLicenseDeactivation.bind(this));
            
            // Load more functionality
            $(document).on('click', '.edd-load-more-purchases', this.handleLoadMorePurchases.bind(this));
            
            // Modal functionality
            $(document).on('click', '.edd-view-receipt', this.handleViewReceipt.bind(this));
            $(document).on('click', '.edd-contact-support', this.handleContactSupport.bind(this));
            $(document).on('click', '.edd-modal-close', this.handleModalClose.bind(this));
            $(document).on('click', '.edd-modal', this.handleModalBackdropClick.bind(this));
            
            // Support form
            $(document).on('submit', '#edd-support-form', this.handleSupportFormSubmit.bind(this));
            
            // License key copying
            $(document).on('click', '.edd-license-key', this.handleLicenseKeyCopy.bind(this));
            
            // Refresh data
            $(document).on('click', '.edd-refresh-data', this.handleRefreshData.bind(this));
            
            // Keyboard shortcuts
            $(document).on('keydown', this.handleKeyboardShortcuts.bind(this));
            
            // Window events
            $(window).on('resize', this.debounce(this.handleWindowResize.bind(this), 250));
            $(window).on('scroll', this.throttle(this.handleWindowScroll.bind(this), 100));
        },

        /**
         * Setup initial dashboard state
         */
        setupInitialState: function() {
            // Set active tab based on URL hash or default
            const hash = window.location.hash.substring(1);
            const validSections = ['purchases', 'downloads', 'licenses', 'wishlist', 'analytics', 'support'];
            
            if (hash && validSections.includes(hash)) {
                this.switchToSection(hash);
            } else {
                this.switchToSection('purchases');
            }

            // Setup mobile optimizations
            this.setupMobileOptimizations();
            
            // Setup accessibility features
            this.setupAccessibilityFeatures();
        },

        /**
         * Load initial dashboard data
         */
        loadInitialData: function() {
            // Preload critical sections
            this.preloadSection('purchases');
            
            // Setup auto-refresh for dynamic content
            this.setupAutoRefresh();
        },

        /**
         * Handle tab navigation clicks
         */
        handleTabClick: function(e) {
            e.preventDefault();
            
            const $tab = $(e.currentTarget);
            const section = $tab.data('section');
            
            if (section && section !== this.state.currentSection) {
                this.switchToSection(section);
                
                // Update URL hash
                window.history.replaceState(null, null, '#' + section);
            }
        },

        /**
         * Switch to a different section
         */
        switchToSection: function(section) {
            // Update navigation
            this.cache.$navTabs.removeClass(this.config.activeClass);
            this.cache.$navTabs.filter('[data-section="' + section + '"]').addClass(this.config.activeClass);
            
            // Update content sections
            this.cache.$contentSections.removeClass(this.config.activeClass);
            $('#edd-section-' + section).addClass(this.config.activeClass);
            
            // Update state
            this.state.currentSection = section;
            
            // Load section data if needed
            this.loadSectionData(section);
            
            // Scroll to top of content
            this.scrollToTop();
            
            // Fire event
            this.trigger('sectionChanged', { section: section });
        },

        /**
         * Load data for a specific section
         */
        loadSectionData: function(section) {
            // Check if data is already loaded
            if (this.state.loadedData[section]) {
                return;
            }

            switch (section) {
                case 'downloads':
                    this.loadDownloadHistory();
                    break;
                case 'analytics':
                    this.loadAnalyticsData();
                    break;
                case 'support':
                    this.loadSupportData();
                    break;
            }
        },

        /**
         * Handle download button clicks
         */
        handleDownloadClick: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const paymentId = $button.data('payment-id');
            const downloadId = $button.data('download-id');
            const fileKey = $button.data('file-key');
            const nonce = $button.data('nonce') || this.config.nonces.download;
            
            if (!paymentId || !downloadId || !fileKey) {
                this.showNotice('error', this.getString('invalidDownloadParams'));
                return;
            }

            this.processDownload($button, {
                payment_id: paymentId,
                download_id: downloadId,
                file_key: fileKey,
                nonce: nonce
            });
        },

        /**
         * Process file download
         */
        processDownload: function($button, data) {
            // Set loading state
            this.setButtonLoading($button, true);
            
            // Make AJAX request
            this.makeRequest('edd_dashboard_download_file', data)
                .done((response) => {
                    if (response.success && response.data.download_url) {
                        // Create temporary download link
                        const $link = $('<a>', {
                            href: response.data.download_url,
                            download: '',
                            style: 'display: none;'
                        }).appendTo('body');
                        
                        // Trigger download
                        $link[0].click();
                        $link.remove();
                        
                        // Show success feedback
                        this.showButtonSuccess($button);
                        this.showNotice('success', response.data.message || this.getString('downloadSuccess'));
                        
                    } else {
                        this.showNotice('error', response.data?.message || this.getString('downloadError'));
                    }
                })
                .fail(() => {
                    this.showNotice('error', this.getString('downloadError'));
                })
                .always(() => {
                    this.setButtonLoading($button, false);
                });
        },

        /**
         * Handle add to wishlist
         */
        handleAddToWishlist: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const downloadId = $button.data('download-id');
            const nonce = $button.data('nonce') || this.config.nonces.wishlist;
            
            if (!downloadId) {
                this.showNotice('error', this.getString('invalidRequest'));
                return;
            }

            this.setButtonLoading($button, true);
            
            this.makeRequest('edd_dashboard_add_to_wishlist', {
                download_id: downloadId,
                nonce: nonce
            })
                .done((response) => {
                    if (response.success) {
                        $button.text('❤️ ' + this.getString('removeFromWishlist'))
                               .removeClass('edd-add-to-wishlist')
                               .addClass('edd-remove-from-wishlist');
                        
                        this.updateWishlistCount(response.data.wishlist_count);
                        this.showNotice('success', response.data.message);
                    } else {
                        this.showNotice('error', response.data?.message || this.getString('wishlistError'));
                    }
                })
                .fail(() => {
                    this.showNotice('error', this.getString('wishlistError'));
                })
                .always(() => {
                    this.setButtonLoading($button, false);
                });
        },

        /**
         * Handle remove from wishlist
         */
        handleRemoveFromWishlist: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const downloadId = $button.data('download-id');
            const nonce = $button.data('nonce') || this.config.nonces.wishlist;
            
            this.setButtonLoading($button, true);
            
            this.makeRequest('edd_dashboard_remove_from_wishlist', {
                download_id: downloadId,
                nonce: nonce
            })
                .done((response) => {
                    if (response.success) {
                        // Remove from wishlist page or update button
                        const $wishlistItem = $button.closest('.edd-wishlist-item');
                        if ($wishlistItem.length) {
                            $wishlistItem.fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            $button.text('❤️ ' + this.getString('addToWishlist'))
                                   .removeClass('edd-remove-from-wishlist')
                                   .addClass('edd-add-to-wishlist');
                        }
                        
                        this.updateWishlistCount(response.data.wishlist_count);
                        this.showNotice('success', response.data.message);
                    } else {
                        this.showNotice('error', response.data?.message || this.getString('wishlistError'));
                    }
                })
                .fail(() => {
                    this.showNotice('error', this.getString('wishlistError'));
                })
                .always(() => {
                    this.setButtonLoading($button, false);
                });
        },

        /**
         * Handle license activation
         */
        handleLicenseActivation: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const licenseId = $button.data('license-id');
            const $siteInput = $button.siblings('.edd-site-url-input');
            const siteUrl = $siteInput.val().trim();
            const nonce = $button.data('nonce') || this.config.nonces.license;
            
            if (!siteUrl) {
                this.showNotice('error', this.getString('enterSiteUrl'));
                $siteInput.focus();
                return;
            }

            if (!this.isValidUrl(siteUrl)) {
                this.showNotice('error', this.getString('invalidSiteUrl'));
                $siteInput.focus();
                return;
            }

            this.setButtonLoading($button, true);
            
            this.makeRequest('edd_dashboard_activate_license', {
                license_id: licenseId,
                site_url: siteUrl,
                nonce: nonce
            })
                .done((response) => {
                    if (response.success) {
                        // Add new site to the list
                        this.addActivatedSite($button, siteUrl, licenseId);
                        
                        // Clear input
                        $siteInput.val('');
                        
                        // Update activation count
                        this.updateActivationCount($button, response.data);
                        
                        this.showNotice('success', response.data.message);
                    } else {
                        this.showNotice('error', response.data?.message || this.getString('licenseActivationError'));
                    }
                })
                .fail(() => {
                    this.showNotice('error', this.getString('licenseActivationError'));
                })
                .always(() => {
                    this.setButtonLoading($button, false);
                });
        },

        /**
         * Handle license deactivation
         */
        handleLicenseDeactivation: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const licenseId = $button.data('license-id');
            const siteUrl = $button.data('site-url');
            const nonce = $button.data('nonce') || this.config.nonces.license;
            
            if (!confirm(this.getString('confirmLicenseDeactivation'))) {
                return;
            }

            this.setButtonLoading($button, true);
            
            this.makeRequest('edd_dashboard_deactivate_license', {
                license_id: licenseId,
                site_url: siteUrl,
                nonce: nonce
            })
                .done((response) => {
                    if (response.success) {
                        // Remove site from list
                        $button.closest('.edd-site-item').fadeOut(300, function() {
                            $(this).remove();
                        });
                        
                        this.showNotice('success', response.data.message);
                    } else {
                        this.showNotice('error', response.data?.message || this.getString('licenseDeactivationError'));
                    }
                })
                .fail(() => {
                    this.showNotice('error', this.getString('licenseDeactivationError'));
                })
                .always(() => {
                    this.setButtonLoading($button, false);
                });
        },

        /**
         * Handle load more purchases
         */
        handleLoadMorePurchases: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const offset = parseInt($button.data('offset')) || 0;
            const nonce = $button.data('nonce') || this.config.nonces.ajax;
            
            this.setButtonLoading($button, true);
            
            this.makeRequest('edd_dashboard_load_more_purchases', {
                offset: offset,
                number: this.config.itemsPerPage,
                nonce: nonce
            })
                .done((response) => {
                    if (response.success && response.data.purchases) {
                        // Append new purchases
                        const $purchaseList = $('.edd-purchase-list');
                        response.data.purchases.forEach(purchase => {
                            $purchaseList.append(this.renderPurchaseItem(purchase));
                        });
                        
                        // Update offset
                        if (response.data.has_more) {
                            $button.data('offset', response.data.next_offset);
                        } else {
                            $button.hide();
                        }
                        
                        // Animate new items
                        $purchaseList.find('.edd-purchase-item').slice(-response.data.purchases.length)
                                    .css('opacity', 0)
                                    .animate({ opacity: 1 }, 300);
                    } else {
                        $button.hide();
                    }
                })
                .fail(() => {
                    this.showNotice('error', this.getString('loadMoreError'));
                })
                .always(() => {
                    this.setButtonLoading($button, false);
                });
        },

        /**
         * Handle view receipt modal
         */
        handleViewReceipt: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const paymentId = $button.data('payment-id');
            
            if (!paymentId) {
                this.showNotice('error', this.getString('invalidRequest'));
                return;
            }

            this.loadPurchaseDetails(paymentId);
        },

        /**
         * Load purchase details into modal
         */
        loadPurchaseDetails: function(paymentId) {
            const $modal = this.cache.$modals.purchaseDetails;
            const $modalBody = $modal.find('.edd-modal-body');
            
            // Show loading in modal
            $modalBody.html('<div class="text-center"><div class="edd-spinner"></div><p>' + this.getString('loading') + '</p></div>');
            this.showModal('purchaseDetails');
            
            this.makeRequest('edd_dashboard_get_purchase_details', {
                payment_id: paymentId,
                nonce: this.config.nonces.ajax
            })
                .done((response) => {
                    if (response.success && response.data.details) {
                        $modalBody.html(this.renderPurchaseDetails(response.data.details));
                    } else {
                        $modalBody.html('<p class="text-center">' + this.getString('purchaseDetailsError') + '</p>');
                    }
                })
                .fail(() => {
                    $modalBody.html('<p class="text-center">' + this.getString('purchaseDetailsError') + '</p>');
                });
        },

        /**
         * Handle contact support
         */
        handleContactSupport: function(e) {
            e.preventDefault();
            this.showModal('support');
        },

        /**
         * Handle support form submission
         */
        handleSupportFormSubmit: function(e) {
            e.preventDefault();
            
            const $form = $(e.currentTarget);
            const $submitBtn = $form.find('button[type="submit"]');
            const formData = this.getFormData($form);
            
            // Validate form
            if (!this.validateSupportForm(formData)) {
                return;
            }

            this.setButtonLoading($submitBtn, true);
            
            // Add AJAX action
            formData.action = 'edd_dashboard_submit_support_ticket';
            
            this.makeRequest('edd_dashboard_submit_support_ticket', formData)
                .done((response) => {
                    if (response.success) {
                        this.showNotice('success', response.data.message || this.getString('supportTicketSubmitted'));
                        this.hideModal('support');
                        $form[0].reset();
                    } else {
                        this.showNotice('error', response.data?.message || this.getString('supportTicketError'));
                    }
                })
                .fail(() => {
                    this.showNotice('error', this.getString('supportTicketError'));
                })
                .always(() => {
                    this.setButtonLoading($submitBtn, false);
                });
        },

        /**
         * Handle modal close
         */
        handleModalClose: function(e) {
            e.preventDefault();
            const $modal = $(e.currentTarget).closest('.edd-modal');
            this.hideModal($modal);
        },

        /**
         * Handle modal backdrop clicks
         */
        handleModalBackdropClick: function(e) {
            if (e.target === e.currentTarget) {
                this.hideModal($(e.currentTarget));
            }
        },

        /**
         * Handle license key copying
         */
        handleLicenseKeyCopy: function(e) {
            e.preventDefault();
            
            const $licenseKey = $(e.currentTarget);
            const text = $licenseKey.text().trim();
            
            if (this.copyToClipboard(text)) {
                const originalBg = $licenseKey.css('background-color');
                $licenseKey.css('background-color', 'rgba(67, 233, 123, 0.2)')
                          .animate({ backgroundColor: originalBg }, 1000);
                
                this.showNotice('success', this.getString('copied'), 2000);
            } else {
                this.showNotice('error', this.getString('copyError'));
            }
        },

        /**
         * Handle refresh data
         */
        handleRefreshData: function(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            this.setButtonLoading($button, true);
            
            this.makeRequest('edd_dashboard_refresh_data', {
                nonce: this.config.nonces.ajax
            })
                .done((response) => {
                    if (response.success) {
                        // Update stats
                        if (response.data.stats) {
                            this.updateStats(response.data.stats);
                        }
                        
                        // Clear loaded data cache
                        this.state.loadedData = {};
                        
                        // Reload current section
                        this.loadSectionData(this.state.currentSection);
                        
                        this.showNotice('success', response.data.message || this.getString('dataRefreshed'));
                    } else {
                        this.showNotice('error', response.data?.message || this.getString('refreshError'));
                    }
                })
                .fail(() => {
                    this.showNotice('error', this.getString('refreshError'));
                })
                .always(() => {
                    this.setButtonLoading($button, false);
                });
        },

        /**
         * Handle keyboard shortcuts
         */
        handleKeyboardShortcuts: function(e) {
            // ESC to close modals
            if (e.keyCode === 27) {
                $('.edd-modal:visible').each((index, modal) => {
                    this.hideModal($(modal));
                });
            }
            
            // Number keys to switch sections (when not in input)
            if (!$(e.target).is('input, textarea, select')) {
                const sectionKeys = {
                    49: 'purchases',  // 1
                    50: 'downloads',  // 2
                    51: 'licenses',   // 3
                    52: 'wishlist',   // 4
                    53: 'analytics',  // 5
                    54: 'support'     // 6
                };
                
                if (sectionKeys[e.keyCode]) {
                    e.preventDefault();
                    this.switchToSection(sectionKeys[e.keyCode]);
                }
            }
        },

        /**
         * Handle window resize
         */
        handleWindowResize: function() {
            // Update mobile optimizations
            this.setupMobileOptimizations();
        },

        /**
         * Handle window scroll
         */
        handleWindowScroll: function() {
            // Add scroll-based effects if needed
        },

        /**
         * Utility Functions
         */

        /**
         * Make AJAX request
         */
        makeRequest: function(action, data = {}) {
            data.action = action;
            data._wpnonce = data.nonce || this.config.nonces.ajax;
            
            return $.ajax({
                url: this.config.ajaxUrl,
                type: 'POST',
                data: data,
                timeout: 30000
            });
        },

        /**
         * Show modal
         */
        showModal: function(modalKey) {
            const $modal = typeof modalKey === 'string' ? this.cache.$modals[modalKey] : modalKey;
            
            if ($modal && $modal.length) {
                $modal.fadeIn(300);
                this.cache.$body.addClass('modal-open');
                
                // Focus first input
                setTimeout(() => {
                    $modal.find('input, textarea, select').first().focus();
                }, 300);
            }
        },

        /**
         * Hide modal
         */
        hideModal: function(modalKey) {
            const $modal = typeof modalKey === 'string' ? this.cache.$modals[modalKey] : modalKey;
            
            if ($modal && $modal.length) {
                $modal.fadeOut(300);
                this.cache.$body.removeClass('modal-open');
            }
        },

        /**
         * Show loading overlay
         */
        showLoading: function() {
            if (!this.state.isLoading) {
                this.state.isLoading = true;
                this.cache.$loadingOverlay.fadeIn(300);
            }
        },

        /**
         * Hide loading overlay
         */
        hideLoading: function() {
            if (this.state.isLoading) {
                this.state.isLoading = false;
                this.cache.$loadingOverlay.fadeOut(300);
            }
        },

        /**
         * Set button loading state
         */
        setButtonLoading: function($button, loading) {
            if (loading) {
                $button.addClass(this.config.loadingClass)
                       .prop('disabled', true)
                       .data('original-text', $button.text())
                       .text(this.getString('loading'));
            } else {
                $button.removeClass(this.config.loadingClass)
                       .prop('disabled', false)
                       .text($button.data('original-text') || $button.text());
            }
        },

        /**
         * Show button success state
         */
        showButtonSuccess: function($button) {
            const originalText = $button.text();
            $button.text('✅ ' + this.getString('downloaded'));
            
            setTimeout(() => {
                $button.text(originalText);
            }, 2000);
        },

        /**
         * Show notification
         */
        showNotice: function(type, message, duration = 5000) {
            const $notice = $('<div>', {
                class: 'edd-notice edd-notice-' + type,
                html: '<span class="dashicons dashicons-' + this.getNoticeIcon(type) + '"></span>' + message
            });
            
            // Remove existing notices
            $('.edd-notice').remove();
            
            // Add new notice
            $notice.prependTo(this.cache.$wrapper)
                   .hide()
                   .slideDown(300);
            
            // Auto remove
            if (duration > 0) {
                setTimeout(() => {
                    $notice.slideUp(300, function() {
                        $(this).remove();
                    });
                }, duration);
            }
        },

        /**
         * Get notice icon
         */
        getNoticeIcon: function(type) {
            const icons = {
                success: 'yes-alt',
                error: 'warning',
                warning: 'warning',
                info: 'info'
            };
            return icons[type] || 'info';
        },

        /**
         * Copy text to clipboard
         */
        copyToClipboard: function(text) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text);
                return true;
            } else {
                // Fallback
                const $temp = $('<textarea>').val(text).appendTo('body').select();
                const success = document.execCommand('copy');
                $temp.remove();
                return success;
            }
        },

        /**
         * Validate URL
         */
        isValidUrl: function(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        },

        /**
         * Get form data
         */
        getFormData: function($form) {
            const data = {};
            $form.serializeArray().forEach(item => {
                data[item.name] = item.value;
            });
            return data;
        },

        /**
         * Validate support form
         */
        validateSupportForm: function(data) {
            if (!data.subject || !data.message) {
                this.showNotice('error', this.getString('fillAllFields'));
                return false;
            }
            return true;
        },

        /**
         * Update wishlist count
         */
        updateWishlistCount: function(count) {
            $('.edd-stat-wishlist').closest('.edd-stat-card')
                                   .find('.edd-stat-number')
                                   .text(count);
        },

        /**
         * Update stats
         */
        updateStats: function(stats) {
            Object.keys(stats).forEach(key => {
                const $stat = $('.edd-stat-' + key.replace('_', '-'));
                if ($stat.length) {
                    $stat.closest('.edd-stat-card')
                         .find('.edd-stat-number')
                         .text(stats[key]);
                }
            });
        },

        /**
         * Add activated site to license
         */
        addActivatedSite: function($button, siteUrl, licenseId) {
            const $sitesList = $button.closest('.edd-site-management').find('.edd-activated-sites');
            
            if (!$sitesList.length) {
                $button.closest('.edd-site-management').prepend('<div class="edd-activated-sites"></div>');
            }
            
            const $newSite = $('<div>', {
                class: 'edd-site-item',
                html: `
                    <span class="edd-site-url">${siteUrl}</span>
                    <button type="button" class="edd-btn edd-btn-secondary edd-btn-small edd-deactivate-license"
                            data-license-id="${licenseId}" data-site-url="${siteUrl}" 
                            data-nonce="${this.config.nonces.license}">
                        🔓 ${this.getString('deactivate')}
                    </button>
                `
            });
            
            $sitesList.append($newSite);
            $newSite.hide().slideDown(300);
        },

        /**
         * Update activation count
         */
        updateActivationCount: function($button, data) {
            const $licenseItem = $button.closest('.edd-license-item');
            const $activationText = $licenseItem.find('.edd-license-meta-item').filter(function() {
                return $(this).text().includes('Activations:');
            });
            
            if ($activationText.length && data.activation_count !== undefined) {
                let text = `Activations: ${data.activation_count}`;
                if (data.activation_limit > 0) {
                    text += ` of ${data.activation_limit} sites`;
                } else {
                    text += ' of unlimited sites';
                }
                $activationText.html(`<strong>Activations:</strong> ${text}`);
            }
        },

        /**
         * Render purchase item
         */
        renderPurchaseItem: function(purchase) {
            return `
                <div class="edd-purchase-item" data-payment-id="${purchase.id}">
                    <div class="edd-purchase-header">
                        <div class="edd-order-info">
                            <div class="edd-order-meta">
                                <span class="edd-order-number">Order #${purchase.id}</span>
                                <span class="edd-order-date">${purchase.date}</span>
                                <span class="edd-order-total">${purchase.total}</span>
                            </div>
                        </div>
                        <span class="edd-status-badge edd-status-${purchase.status}">${purchase.status}</span>
                    </div>
                    ${this.renderPurchaseProducts(purchase.products)}
                    ${this.renderPurchaseActions(purchase)}
                </div>
            `;
        },

        /**
         * Render purchase products
         */
        renderPurchaseProducts: function(products) {
            if (!products || !products.length) return '';
            
            const productsHtml = products.map(product => `
                <div class="edd-product-row">
                    <div class="edd-product-details">
                        <strong class="edd-product-name">${product.name}</strong>
                        ${product.version ? `<div class="edd-product-meta">Version: ${product.version}</div>` : ''}
                    </div>
                    <div class="edd-product-actions">
                        ${this.renderDownloadButtons(product)}
                    </div>
                </div>
            `).join('');
            
            return `<div class="edd-order-products">${productsHtml}</div>`;
        },

        /**
         * Render download buttons
         */
        renderDownloadButtons: function(product) {
            if (!product.download_files || !product.download_files.length) return '';
            
            return product.download_files.map(file => `
                <button type="button" class="edd-btn edd-btn-download" 
                        data-payment-id="${product.payment_id}" 
                        data-download-id="${product.id}" 
                        data-file-key="${file.id}"
                        data-nonce="${this.config.nonces.download}">
                    🔽 ${file.name}
                </button>
            `).join('');
        },

        /**
         * Render purchase actions
         */
        renderPurchaseActions: function(purchase) {
            return `
                <div class="edd-order-actions">
                    <button type="button" class="edd-btn edd-btn-secondary edd-view-receipt" 
                            data-payment-id="${purchase.id}">
                        📄 ${this.getString('viewReceipt')}
                    </button>
                    ${purchase.receipt_url ? `
                        <a href="${purchase.receipt_url}" class="edd-btn edd-btn-secondary" target="_blank">
                            📋 ${this.getString('orderDetails')}
                        </a>
                    ` : ''}
                    <button type="button" class="edd-btn edd-btn-secondary edd-contact-support" 
                            data-payment-id="${purchase.id}">
                        💬 ${this.getString('support')}
                    </button>
                </div>
            `;
        },

        /**
         * Render purchase details modal content
         */
        renderPurchaseDetails: function(details) {
            return `
                <div class="edd-purchase-details">
                    <div class="edd-detail-row">
                        <strong>${this.getString('orderNumber')}:</strong> #${details.id}
                    </div>
                    <div class="edd-detail-row">
                        <strong>${this.getString('orderDate')}:</strong> ${details.date}
                    </div>
                    <div class="edd-detail-row">
                        <strong>${this.getString('orderStatus')}:</strong> 
                        <span class="edd-status-badge edd-status-${details.status}">${details.status}</span>
                    </div>
                    <div class="edd-detail-row">
                        <strong>${this.getString('orderTotal')}:</strong> ${details.total} ${details.currency}
                    </div>
                    <div class="edd-detail-row">
                        <strong>${this.getString('paymentMethod')}:</strong> ${details.gateway}
                    </div>
                    ${this.renderCustomerInfo(details.customer_info)}
                    ${this.renderProductsList(details.products)}
                </div>
            `;
        },

        /**
         * Render customer info
         */
        renderCustomerInfo: function(customerInfo) {
            if (!customerInfo) return '';
            
            return `
                <div class="edd-customer-info">
                    <h4>${this.getString('customerInformation')}</h4>
                    <div class="edd-detail-row">
                        <strong>${this.getString('name')}:</strong> ${customerInfo.first_name} ${customerInfo.last_name}
                    </div>
                    <div class="edd-detail-row">
                        <strong>${this.getString('email')}:</strong> ${customerInfo.email}
                    </div>
                </div>
            `;
        },

        /**
         * Render products list
         */
        renderProductsList: function(products) {
            if (!products || !products.length) return '';
            
            const productsHtml = products.map(product => `
                <div class="edd-product-detail">
                    <strong>${product.name}</strong>
                    <div>Price: ${product.price}</div>
                    ${product.license_key ? `<div>License: ${product.license_key}</div>` : ''}
                </div>
            `).join('');
            
            return `
                <div class="edd-products-list">
                    <h4>${this.getString('products')}</h4>
                    ${productsHtml}
                </div>
            `;
        },

        /**
         * Load download history
         */
        loadDownloadHistory: function() {
            this.makeRequest('edd_dashboard_get_download_history', {
                limit: 20,
                nonce: this.config.nonces.ajax
            })
                .done((response) => {
                    if (response.success && response.data.downloads) {
                        this.updateDownloadHistory(response.data.downloads);
                        this.state.loadedData.downloads = true;
                    }
                })
                .fail(() => {
                    console.error('Failed to load download history');
                });
        },

        /**
         * Update download history
         */
        updateDownloadHistory: function(downloads) {
            const $downloadsList = $('#edd-section-downloads .edd-download-list');
            
            if (!downloads.length) {
                $downloadsList.html(this.renderEmptyState('downloads'));
                return;
            }
            
            const downloadsHtml = downloads.map(download => `
                <div class="edd-download-item">
                    <div class="edd-download-header">
                        <div class="edd-download-name">${download.product_name}</div>
                        <div class="edd-download-date">Downloaded: ${download.date}</div>
                    </div>
                </div>
            `).join('');
            
            $downloadsList.html(downloadsHtml);
        },

        /**
         * Load analytics data
         */
        loadAnalyticsData: function() {
            // Analytics data is usually loaded on page load
            this.state.loadedData.analytics = true;
        },

        /**
         * Load support data
         */
        loadSupportData: function() {
            // Support data is static, just mark as loaded
            this.state.loadedData.support = true;
        },

        /**
         * Render empty state
         */
        renderEmptyState: function(section) {
            const emptyStates = {
                downloads: {
                    icon: '⬇️',
                    title: this.getString('noDownloads'),
                    message: this.getString('noDownloadsMessage')
                },
                purchases: {
                    icon: '📦',
                    title: this.getString('noPurchases'),
                    message: this.getString('noPurchasesMessage')
                },
                wishlist: {
                    icon: '❤️',
                    title: this.getString('emptyWishlist'),
                    message: this.getString('emptyWishlistMessage')
                }
            };
            
            const state = emptyStates[section] || emptyStates.purchases;
            
            return `
                <div class="edd-empty-state">
                    <div class="edd-empty-icon">${state.icon}</div>
                    <h3>${state.title}</h3>
                    <p>${state.message}</p>
                </div>
            `;
        },

        /**
         * Preload section data
         */
        preloadSection: function(section) {
            // Preload critical section data
            if (section === 'purchases') {
                // Purchases are usually loaded on page load
                this.state.loadedData.purchases = true;
            }
        },

        /**
         * Setup auto-refresh
         */
        setupAutoRefresh: function() {
            // Auto-refresh every 5 minutes for dynamic content
            setInterval(() => {
                if (!this.state.isLoading && document.visibilityState === 'visible') {
                    this.refreshStats();
                }
            }, 300000); // 5 minutes
        },

        /**
         * Refresh stats quietly
         */
        refreshStats: function() {
            this.makeRequest('edd_dashboard_refresh_data', {
                nonce: this.config.nonces.ajax
            })
                .done((response) => {
                    if (response.success && response.data.stats) {
                        this.updateStats(response.data.stats);
                    }
                })
                .fail(() => {
                    // Fail silently for background refresh
                });
        },

        /**
         * Setup mobile optimizations
         */
        setupMobileOptimizations: function() {
            const isMobile = window.innerWidth <= 768;
            
            if (isMobile) {
                // Enable touch-friendly interactions
                this.cache.$wrapper.addClass('mobile-optimized');
                
                // Adjust scroll behavior
                this.setupMobileScroll();
            } else {
                this.cache.$wrapper.removeClass('mobile-optimized');
            }
        },

        /**
         * Setup mobile scroll
         */
        setupMobileScroll: function() {
            // Smooth scroll to sections on mobile
            $('.edd-nav-tab').off('click.mobile').on('click.mobile', (e) => {
                if (window.innerWidth <= 768) {
                    setTimeout(() => {
                        this.scrollToTop(true);
                    }, 100);
                }
            });
        },

        /**
         * Setup accessibility features
         */
        setupAccessibilityFeatures: function() {
            // Add ARIA labels and roles
            this.cache.$navTabs.attr('role', 'tab');
            this.cache.$contentSections.attr('role', 'tabpanel');
            
            // Keyboard navigation
            this.cache.$navTabs.on('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(e.currentTarget).click();
                }
            });
            
            // Focus management
            this.setupFocusManagement();
        },

        /**
         * Setup focus management
         */
        setupFocusManagement: function() {
            // Trap focus in modals
            $('.edd-modal').on('keydown', (e) => {
                if (e.key === 'Tab') {
                    const $modal = $(e.currentTarget);
                    const $focusable = $modal.find('button, input, textarea, select, a[href]');
                    
                    if ($focusable.length) {
                        const first = $focusable.first()[0];
                        const last = $focusable.last()[0];
                        
                        if (e.shiftKey && e.target === first) {
                            e.preventDefault();
                            last.focus();
                        } else if (!e.shiftKey && e.target === last) {
                            e.preventDefault();
                            first.focus();
                        }
                    }
                }
            });
        },

        /**
         * Scroll to top
         */
        scrollToTop: function(smooth = false) {
            const target = this.cache.$wrapper.offset().top - 50;
            
            if (smooth) {
                $('html, body').animate({ scrollTop: target }, 300);
            } else {
                window.scrollTo(0, target);
            }
        },

        /**
         * Get localized string
         */
        getString: function(key) {
            const strings = window.eddDashboardPro?.strings || {};
            
            const defaultStrings = {
                loading: 'Loading...',
                error: 'An error occurred. Please try again.',
                success: 'Success!',
                confirm: 'Are you sure?',
                copied: 'Copied to clipboard!',
                downloadSuccess: 'Download ready!',
                downloadError: 'Download failed. Please try again.',
                wishlistError: 'Wishlist operation failed.',
                licenseActivationError: 'License activation failed.',
                licenseDeactivationError: 'License deactivation failed.',
                loadMoreError: 'Failed to load more items.',
                purchaseDetailsError: 'Failed to load purchase details.',
                supportTicketSubmitted: 'Support ticket submitted successfully!',
                supportTicketError: 'Failed to submit support ticket.',
                refreshError: 'Failed to refresh data.',
                dataRefreshed: 'Data refreshed successfully!',
                copyError: 'Failed to copy to clipboard.',
                invalidRequest: 'Invalid request.',
                invalidDownloadParams: 'Invalid download parameters.',
                enterSiteUrl: 'Please enter a site URL.',
                invalidSiteUrl: 'Please enter a valid URL.',
                confirmLicenseDeactivation: 'Are you sure you want to deactivate this license?',
                fillAllFields: 'Please fill in all required fields.',
                downloaded: 'Downloaded',
                addToWishlist: 'Add to Wishlist',
                removeFromWishlist: 'Remove from Wishlist',
                viewReceipt: 'View Receipt',
                orderDetails: 'Order Details',
                support: 'Support',
                deactivate: 'Deactivate',
                orderNumber: 'Order Number',
                orderDate: 'Order Date',
                orderStatus: 'Status',
                orderTotal: 'Total',
                paymentMethod: 'Payment Method',
                customerInformation: 'Customer Information',
                name: 'Name',
                email: 'Email',
                products: 'Products',
                noDownloads: 'No downloads yet',
                noDownloadsMessage: 'Your download history will appear here.',
                noPurchases: 'No purchases yet',
                noPurchasesMessage: 'You haven\'t made any purchases yet.',
                emptyWishlist: 'Your wishlist is empty',
                emptyWishlistMessage: 'Add products to your wishlist to save them for later.'
            };
            
            return strings[key] || defaultStrings[key] || key;
        },

        /**
         * Trigger custom event
         */
        trigger: function(eventName, data = {}) {
            $(document).trigger('eddDashboard:' + eventName, [data]);
        },

        /**
         * Debounce function
         */
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        /**
         * Throttle function
         */
        throttle: function(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        // Only initialize if we're on the dashboard page
        if ($('.edd-customer-dashboard-pro-wrapper').length) {
            window.eddDashboardPro.init();
        }
    });

    // Expose utility functions globally
    window.eddDashboardUtils = {
        showNotice: window.eddDashboardPro.showNotice.bind(window.eddDashboardPro),
        showModal: window.eddDashboardPro.showModal.bind(window.eddDashboardPro),
        hideModal: window.eddDashboardPro.hideModal.bind(window.eddDashboardPro),
        copyToClipboard: window.eddDashboardPro.copyToClipboard.bind(window.eddDashboardPro),
        switchSection: window.eddDashboardPro.switchToSection.bind(window.eddDashboardPro)
    };

})(jQuery);
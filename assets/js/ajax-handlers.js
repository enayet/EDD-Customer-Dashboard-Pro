/**
 * AJAX Handlers for EDD Customer Dashboard Pro
 * 
 * Handles specific AJAX requests and responses for enhanced
 * user experience and real-time data updates.
 *
 * @package EDD_Customer_Dashboard_Pro
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // AJAX Handler Class
    class EDDAjaxHandlers {
        constructor() {
            this.init();
            this.bindEvents();
        }

        init() {
            this.cache = {
                $document: $(document),
                $body: $('body'),
                $dashboard: $('.edd-customer-dashboard-pro')
            };

            this.settings = {
                ajaxTimeout: 30000,
                retryAttempts: 3,
                retryDelay: 1000,
                batchSize: 10
            };

            // Request queue for batching
            this.requestQueue = [];
            this.processing = false;

            // Merge with localized settings
            if (typeof eddDashboardPro !== 'undefined') {
                this.settings = $.extend(this.settings, eddDashboardPro.ajaxSettings || {});
            }
        }

        bindEvents() {
            // Bind to dashboard events
            this.cache.$dashboard.on('sectionChange', this.handleSectionChange.bind(this));
            this.cache.$dashboard.on('actionSuccess', this.handleActionSuccess.bind(this));
            this.cache.$dashboard.on('contentInitialized', this.handleContentInitialized.bind(this));

            // Global AJAX events
            this.cache.$document.ajaxStart(this.handleAjaxStart.bind(this));
            this.cache.$document.ajaxStop(this.handleAjaxStop.bind(this));
            this.cache.$document.ajaxError(this.handleAjaxError.bind(this));
            this.cache.$document.ajaxSuccess(this.handleAjaxSuccess.bind(this));

            // Specific AJAX handlers
            this.cache.$document.on('click', '.edd-ajax-load', this.handleAjaxLoad.bind(this));
            this.cache.$document.on('click', '.edd-quick-action', this.handleQuickAction.bind(this));
            this.cache.$document.on('change', '.edd-auto-save', this.handleAutoSave.bind(this));
            this.cache.$document.on('click', '.edd-batch-action', this.handleBatchAction.bind(this));
        }

        // Core AJAX Methods
        makeRequest(options) {
            const defaults = {
                url: this.getAjaxUrl(),
                type: 'POST',
                dataType: 'json',
                timeout: this.settings.ajaxTimeout,
                cache: false,
                data: {
                    nonce: this.getNonce()
                }
            };

            const settings = $.extend(true, {}, defaults, options);
            
            // Add request ID for tracking
            settings.data._request_id = this.generateRequestId();

            return this.executeRequest(settings);
        }

        executeRequest(settings, attempt = 1) {
            return $.ajax(settings).fail((xhr, status, error) => {
                if (attempt < this.settings.retryAttempts && this.shouldRetry(xhr, status)) {
                    setTimeout(() => {
                        this.executeRequest(settings, attempt + 1);
                    }, this.settings.retryDelay * attempt);
                } else {
                    this.handleRequestFailure(xhr, status, error, settings);
                }
            });
        }

        shouldRetry(xhr, status) {
            // Retry on network errors and server errors (5xx)
            return status === 'timeout' || 
                   status === 'error' || 
                   (xhr.status >= 500 && xhr.status < 600);
        }

        handleRequestFailure(xhr, status, error, settings) {
            console.error('AJAX Request Failed:', {
                url: settings.url,
                status: status,
                error: error,
                data: settings.data
            });

            // Show user-friendly error message
            this.showErrorNotification(this.getErrorMessage(xhr, status, error));
        }

        getErrorMessage(xhr, status, error) {
            if (status === 'timeout') {
                return 'Request timed out. Please try again.';
            } else if (status === 'parsererror') {
                return 'Invalid response from server.';
            } else if (xhr.status === 403) {
                return 'Access denied. Please refresh the page and try again.';
            } else if (xhr.status === 404) {
                return 'Requested resource not found.';
            } else if (xhr.status >= 500) {
                return 'Server error. Please try again later.';
            } else {
                return 'An unexpected error occurred. Please try again.';
            }
        }

        // Section Loading Methods
        loadSection(section, options = {}) {
            const data = {
                action: 'edd_dashboard_pro_load_section',
                section: section,
                ...options
            };

            return this.makeRequest({
                data: data,
                beforeSend: () => {
                    this.showSectionLoading(section);
                },
                success: (response) => {
                    this.handleSectionLoadSuccess(section, response);
                },
                error: () => {
                    this.handleSectionLoadError(section);
                }
            });
        }

        showSectionLoading(section) {
            const $section = $(`#edd-section-${section}`);
            $section.addClass('edd-section-loading');
            
            if (!$section.find('.edd-section-loader').length) {
                $section.prepend('<div class="edd-section-loader"><div class="edd-spinner"></div></div>');
            }
        }

        hideSectionLoading(section) {
            const $section = $(`#edd-section-${section}`);
            $section.removeClass('edd-section-loading');
            $section.find('.edd-section-loader').remove();
        }

        handleSectionLoadSuccess(section, response) {
            this.hideSectionLoading(section);

            if (response.success) {
                this.renderSectionContent(section, response.data);
                this.cache.$dashboard.trigger('sectionLoaded', [section, response.data]);
            } else {
                this.showErrorNotification(response.data.message || 'Failed to load section');
            }
        }

        handleSectionLoadError(section) {
            this.hideSectionLoading(section);
            this.showErrorNotification('Failed to load section content');
        }

        renderSectionContent(section, data) {
            const $container = $(`.edd-content-section[id="edd-section-${section}"]`);
            
            if (data.html) {
                $container.html(data.html);
            }

            // Initialize new content
            if (window.eddDashboard && typeof window.eddDashboard.initializeNewContent === 'function') {
                window.eddDashboard.initializeNewContent($container);
            }
        }

        // Table Loading Methods
        loadTable(tableType, options = {}) {
            const data = {
                action: 'edd_dashboard_pro_load_table',
                table_type: tableType,
                page: options.page || 1,
                per_page: options.per_page || 10,
                sort_by: options.sort_by || 'date',
                sort_order: options.sort_order || 'desc',
                filters: options.filters || {},
                search: options.search || ''
            };

            return this.makeRequest({
                data: data,
                beforeSend: () => {
                    this.showTableLoading(tableType);
                },
                success: (response) => {
                    this.handleTableLoadSuccess(tableType, response);
                },
                error: () => {
                    this.handleTableLoadError(tableType);
                }
            });
        }

        showTableLoading(tableType) {
            const $table = $(`.edd-table[data-table-type="${tableType}"]`);
            $table.addClass('edd-table-loading');
            
            const $overlay = $('<div class="edd-table-overlay"><div class="edd-spinner"></div></div>');
            $table.closest('.edd-table-container').append($overlay);
        }

        hideTableLoading(tableType) {
            const $table = $(`.edd-table[data-table-type="${tableType}"]`);
            $table.removeClass('edd-table-loading');
            $table.closest('.edd-table-container').find('.edd-table-overlay').remove();
        }

        handleTableLoadSuccess(tableType, response) {
            this.hideTableLoading(tableType);

            if (response.success) {
                this.renderTableContent(tableType, response.data);
                this.cache.$dashboard.trigger('tableLoaded', [tableType, response.data]);
            } else {
                this.showErrorNotification(response.data.message || 'Failed to load table data');
            }
        }

        handleTableLoadError(tableType) {
            this.hideTableLoading(tableType);
            this.showErrorNotification('Failed to load table data');
        }

        renderTableContent(tableType, data) {
            const $container = $(`.edd-table[data-table-type="${tableType}"]`).closest('.edd-table-container');
            
            if (data.html) {
                $container.html(data.html);
            }

            // Initialize new table content
            if (window.eddDashboard && typeof window.eddDashboard.initializeNewContent === 'function') {
                window.eddDashboard.initializeNewContent($container);
            }
        }

        // Stats Methods
        refreshStats(statTypes = []) {
            const data = {
                action: 'edd_dashboard_pro_refresh_stats',
                stat_types: statTypes
            };

            return this.makeRequest({
                data: data,
                success: (response) => {
                    this.handleStatsRefreshSuccess(response);
                },
                error: () => {
                    this.showErrorNotification('Failed to refresh statistics');
                }
            });
        }

        handleStatsRefreshSuccess(response) {
            if (response.success && response.data.stats) {
                this.updateStatCards(response.data.stats);
                this.cache.$dashboard.trigger('statsRefreshed', [response.data.stats]);
            }
        }

        updateStatCards(stats) {
            Object.keys(stats).forEach(statKey => {
                const $statCard = $(`.edd-stat-card[data-stat="${statKey}"]`);
                const statData = stats[statKey];

                if ($statCard.length && statData) {
                    $statCard.find('.edd-stat-number').text(statData.value);
                    
                    if (statData.change !== undefined) {
                        const $change = $statCard.find('.edd-stat-change');
                        $change.removeClass('positive negative')
                               .addClass(statData.change >= 0 ? 'positive' : 'negative')
                               .text(statData.change_text || '');
                    }

                    if (statData.percentage !== undefined) {
                        const $percentage = $statCard.find('.edd-stat-percentage');
                        $percentage.text(statData.percentage + '%');
                    }
                }
            });
        }

        // Download Methods
        trackDownload(downloadId, fileId, purchaseId) {
            const data = {
                action: 'edd_dashboard_pro_track_download',
                download_id: downloadId,
                file_id: fileId,
                purchase_id: purchaseId,
                timestamp: Date.now()
            };

            // Fire and forget - don't wait for response
            this.makeRequest({
                data: data,
                timeout: 5000
            }).catch(() => {
                // Silently handle tracking failures
                console.warn('Download tracking failed');
            });
        }

        generateDownloadUrl(downloadId, fileId, purchaseId) {
            const data = {
                action: 'edd_dashboard_pro_generate_download_url',
                download_id: downloadId,
                file_id: fileId,
                purchase_id: purchaseId
            };

            return this.makeRequest({
                data: data
            });
        }

        // Wishlist Methods
        addToWishlist(downloadId) {
            const data = {
                action: 'edd_dashboard_pro_add_to_wishlist',
                download_id: downloadId
            };

            return this.makeRequest({
                data: data,
                success: (response) => {
                    if (response.success) {
                        this.updateWishlistUI(downloadId, true, response.data.count);
                        this.showSuccessNotification(response.data.message || 'Added to wishlist');
                    }
                }
            });
        }

        removeFromWishlist(downloadId) {
            const data = {
                action: 'edd_dashboard_pro_remove_from_wishlist',
                download_id: downloadId
            };

            return this.makeRequest({
                data: data,
                success: (response) => {
                    if (response.success) {
                        this.updateWishlistUI(downloadId, false, response.data.count);
                        this.showSuccessNotification(response.data.message || 'Removed from wishlist');
                    }
                }
            });
        }

        updateWishlistUI(downloadId, inWishlist, count) {
            // Update wishlist buttons
            const $buttons = $(`.edd-wishlist-toggle[data-download-id="${downloadId}"]`);
            $buttons.toggleClass('edd-in-wishlist', inWishlist);
            $buttons.find('.edd-wishlist-text').text(inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist');

            // Update wishlist count
            $('.edd-wishlist-count').text(count);
            $(`.edd-nav-tab[data-section="wishlist"] .edd-tab-count`).text(count);

            // Remove item from wishlist section if currently viewing
            if (!inWishlist && $('.edd-content-section[id="edd-section-wishlist"]').hasClass('active')) {
                $(`.edd-wishlist-item[data-download-id="${downloadId}"]`).fadeOut(() => {
                    $(this).remove();
                });
            }
        }

        // License Methods
        activateLicense(licenseKey, downloadId, siteUrl = '') {
            const data = {
                action: 'edd_dashboard_pro_activate_license',
                license_key: licenseKey,
                download_id: downloadId,
                site_url: siteUrl
            };

            return this.makeRequest({
                data: data,
                timeout: 15000, // Longer timeout for license operations
                success: (response) => {
                    if (response.success) {
                        this.updateLicenseUI(licenseKey, 'active', response.data);
                        this.showSuccessNotification(response.data.message || 'License activated successfully');
                    }
                }
            });
        }

        deactivateLicense(licenseKey, downloadId, siteUrl = '') {
            const data = {
                action: 'edd_dashboard_pro_deactivate_license',
                license_key: licenseKey,
                download_id: downloadId,
                site_url: siteUrl
            };

            return this.makeRequest({
                data: data,
                timeout: 15000,
                success: (response) => {
                    if (response.success) {
                        this.updateLicenseUI(licenseKey, 'inactive', response.data);
                        this.showSuccessNotification(response.data.message || 'License deactivated successfully');
                    }
                }
            });
        }

        checkLicenseStatus(licenseKey, downloadId) {
            const data = {
                action: 'edd_dashboard_pro_check_license',
                license_key: licenseKey,
                download_id: downloadId
            };

            return this.makeRequest({
                data: data,
                success: (response) => {
                    if (response.success) {
                        this.updateLicenseUI(licenseKey, response.data.status, response.data);
                    }
                }
            });
        }

        updateLicenseUI(licenseKey, status, data = {}) {
            const $row = $(`.edd-license-row[data-license-key="${licenseKey}"]`);
            
            if (!$row.length) return;

            // Update status badge
            const $statusBadge = $row.find('.edd-license-status');
            $statusBadge.removeClass('edd-badge-success edd-badge-warning edd-badge-error edd-badge-info');
            
            switch (status) {
                case 'active':
                case 'valid':
                    $statusBadge.addClass('edd-badge-success').text('Active');
                    break;
                case 'inactive':
                case 'deactivated':
                    $statusBadge.addClass('edd-badge-warning').text('Inactive');
                    break;
                case 'expired':
                    $statusBadge.addClass('edd-badge-error').text('Expired');
                    break;
                case 'invalid':
                    $statusBadge.addClass('edd-badge-error').text('Invalid');
                    break;
                default:
                    $statusBadge.addClass('edd-badge-info').text(status);
            }

            // Update action button
            const $actionBtn = $row.find('.edd-license-action');
            if (status === 'active' || status === 'valid') {
                $actionBtn.removeClass('edd-license-activate')
                         .addClass('edd-license-deactivate')
                         .text('Deactivate');
            } else {
                $actionBtn.removeClass('edd-license-deactivate')
                         .addClass('edd-license-activate')
                         .text('Activate');
            }

            // Update additional info
            if (data.expires) {
                $row.find('.edd-license-expires').text(data.expires);
            }
            if (data.activations_left !== undefined) {
                $row.find('.edd-license-activations').text(data.activations_left);
            }
            if (data.sites) {
                this.updateLicenseSites($row, data.sites);
            }
        }

        updateLicenseSites($row, sites) {
            const $sitesContainer = $row.find('.edd-license-sites');
            
            if (sites.length === 0) {
                $sitesContainer.html('<span class="edd-no-sites">No active sites</span>');
                return;
            }

            let sitesHtml = '<ul class="edd-license-sites-list">';
            sites.forEach(site => {
                sitesHtml += `<li class="edd-license-site">${site}</li>`;
            });
            sitesHtml += '</ul>';
            
            $sitesContainer.html(sitesHtml);
        }

        // Support Methods
        submitSupportTicket(formData) {
            const data = {
                action: 'edd_dashboard_pro_submit_support_ticket',
                ...formData
            };

            return this.makeRequest({
                data: data,
                timeout: 20000,
                success: (response) => {
                    if (response.success) {
                        this.showSuccessNotification(response.data.message || 'Support ticket submitted successfully');
                        this.cache.$dashboard.trigger('supportTicketSubmitted', [response.data]);
                    }
                }
            });
        }

        loadSupportTickets(page = 1, status = 'all') {
            const data = {
                action: 'edd_dashboard_pro_load_support_tickets',
                page: page,
                status: status
            };

            return this.makeRequest({
                data: data,
                success: (response) => {
                    if (response.success) {
                        this.renderSupportTickets(response.data);
                    }
                }
            });
        }

        renderSupportTickets(data) {
            const $container = $('.edd-support-tickets-container');
            
            if (data.html) {
                $container.html(data.html);
            }

            // Initialize new content
            if (window.eddDashboard && typeof window.eddDashboard.initializeNewContent === 'function') {
                window.eddDashboard.initializeNewContent($container);
            }
        }

        // Batch Operations
        addToBatch(action, item) {
            if (!this.requestQueue.find(req => req.action === action)) {
                this.requestQueue.push({
                    action: action,
                    items: []
                });
            }

            const batchRequest = this.requestQueue.find(req => req.action === action);
            batchRequest.items.push(item);

            // Process batch if it reaches the limit
            if (batchRequest.items.length >= this.settings.batchSize) {
                this.processBatch(action);
            }
        }

        processBatch(action) {
            if (this.processing) return;

            const batchIndex = this.requestQueue.findIndex(req => req.action === action);
            if (batchIndex === -1) return;

            const batch = this.requestQueue[batchIndex];
            this.requestQueue.splice(batchIndex, 1);

            this.processing = true;

            const data = {
                action: `edd_dashboard_pro_batch_${action}`,
                items: batch.items
            };

            this.makeRequest({
                data: data,
                timeout: 30000,
                success: (response) => {
                    this.handleBatchSuccess(action, response);
                },
                error: () => {
                    this.handleBatchError(action);
                },
                complete: () => {
                    this.processing = false;
                }
            });
        }

        processAllBatches() {
            this.requestQueue.forEach(batch => {
                this.processBatch(batch.action);
            });
        }

        handleBatchSuccess(action, response) {
            if (response.success) {
                this.showSuccessNotification(`Batch ${action} completed successfully`);
                this.cache.$dashboard.trigger('batchCompleted', [action, response.data]);
            } else {
                this.showErrorNotification(response.data.message || `Batch ${action} failed`);
            }
        }

        handleBatchError(action) {
            this.showErrorNotification(`Batch ${action} failed`);
        }

        // Event Handlers
        handleSectionChange(e, section) {
            // Pre-load data for section if needed
            this.preloadSectionData(section);
        }

        preloadSectionData(section) {
            // Define which sections need pre-loading
            const preloadSections = ['analytics', 'support'];
            
            if (preloadSections.includes(section)) {
                this.loadSection(section);
            }
        }

        handleActionSuccess(e, action, data, $button) {
            // Handle specific action success events
            switch (action) {
                case 'refresh_downloads':
                    this.loadTable('downloads');
                    break;
                case 'sync_licenses':
                    this.refreshLicenseData();
                    break;
                case 'clear_cache':
                    this.refreshAllData();
                    break;
            }
        }

        handleContentInitialized(e, $container) {
            // Set up any new AJAX elements in the container
            this.initializeAjaxElements($container);
        }

        initializeAjaxElements($container) {
            // Initialize any new AJAX-enabled elements
            $container.find('.edd-ajax-load').each((index, element) => {
                this.setupAjaxElement($(element));
            });
        }

        setupAjaxElement($element) {
            // Set up individual AJAX element
            const loadType = $element.data('load-type');
            const loadData = $element.data('load-data');

            if (loadType && loadData) {
                this.loadElementContent($element, loadType, loadData);
            }
        }

        loadElementContent($element, type, data) {
            const requestData = {
                action: 'edd_dashboard_pro_load_element',
                element_type: type,
                element_data: data
            };

            this.makeRequest({
                data: requestData,
                success: (response) => {
                    if (response.success) {
                        $element.html(response.data.html).removeClass('edd-ajax-load');
                        this.initializeAjaxElements($element);
                    }
                }
            });
        }

        handleAjaxLoad(e) {
            e.preventDefault();
            
            const $trigger = $(e.currentTarget);
            const loadType = $trigger.data('load-type');
            const loadData = $trigger.data('load-data');
            const $target = $($trigger.data('target') || $trigger);

            this.loadElementContent($target, loadType, loadData);
        }

        handleQuickAction(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const action = $button.data('quick-action');
            const actionData = $button.data('action-data') || {};

            this.performQuickAction(action, actionData, $button);
        }

        performQuickAction(action, data, $button) {
            const requestData = {
                action: `edd_dashboard_pro_quick_${action}`,
                ...data
            };

            $button.prop('disabled', true).addClass('edd-loading');

            this.makeRequest({
                data: requestData,
                success: (response) => {
                    if (response.success) {
                        this.showSuccessNotification(response.data.message || 'Action completed');
                        this.handleQuickActionSuccess(action, response.data, $button);
                    } else {
                        this.showErrorNotification(response.data.message || 'Action failed');
                    }
                },
                complete: () => {
                    $button.prop('disabled', false).removeClass('edd-loading');
                }
            });
        }

        handleQuickActionSuccess(action, data, $button) {
            // Handle specific quick action success
            switch (action) {
                case 'mark_read':
                    $button.closest('.edd-notification-item').addClass('edd-read');
                    break;
                case 'toggle_favorite':
                    $button.toggleClass('edd-favorited', data.is_favorite);
                    break;
                case 'quick_download':
                    if (data.download_url) {
                        window.location.href = data.download_url;
                    }
                    break;
            }

            this.cache.$dashboard.trigger('quickActionCompleted', [action, data, $button]);
        }

        handleAutoSave(e) {
            const $field = $(e.currentTarget);
            const saveData = this.collectAutoSaveData($field);

            clearTimeout($field.data('autosave-timeout'));
            
            const timeout = setTimeout(() => {
                this.performAutoSave(saveData, $field);
            }, 1000);

            $field.data('autosave-timeout', timeout);
        }

        collectAutoSaveData($field) {
            const $form = $field.closest('form');
            const data = {};

            $form.find('.edd-auto-save').each((index, field) => {
                const $f = $(field);
                data[$f.attr('name')] = $f.val();
            });

            return data;
        }

        performAutoSave(data, $field) {
            const requestData = {
                action: 'edd_dashboard_pro_auto_save',
                form_data: data
            };

            this.makeRequest({
                data: requestData,
                success: (response) => {
                    if (response.success) {
                        this.showAutoSaveIndicator($field, 'saved');
                    } else {
                        this.showAutoSaveIndicator($field, 'error');
                    }
                },
                error: () => {
                    this.showAutoSaveIndicator($field, 'error');
                }
            });
        }

        showAutoSaveIndicator($field, status) {
            const $indicator = $field.siblings('.edd-autosave-indicator');
            
            $indicator.removeClass('edd-saving edd-saved edd-error')
                     .addClass(`edd-${status}`)
                     .fadeIn();

            setTimeout(() => {
                $indicator.fadeOut();
            }, 2000);
        }

        handleBatchAction(e) {
            e.preventDefault();
            
            const $button = $(e.currentTarget);
            const action = $button.data('batch-action');
            const items = this.collectBatchItems($button);

            if (items.length === 0) {
                this.showErrorNotification('No items selected for batch action');
                return;
            }

            if (confirm(`Are you sure you want to ${action} ${items.length} items?`)) {
                this.performBatchAction(action, items, $button);
            }
        }

        collectBatchItems($button) {
            const $container = $button.closest('.edd-batch-container');
            const items = [];

            $container.find('.edd-batch-checkbox:checked').each((index, checkbox) => {
                items.push($(checkbox).val());
            });

            return items;
        }

        performBatchAction(action, items, $button) {
            const data = {
                action: `edd_dashboard_pro_batch_${action}`,
                items: items
            };

            $button.prop('disabled', true).addClass('edd-loading');

            this.makeRequest({
                data: data,
                timeout: 60000, // Longer timeout for batch operations
                success: (response) => {
                    if (response.success) {
                        this.showSuccessNotification(`Batch ${action} completed successfully`);
                        this.handleBatchActionSuccess(action, response.data, $button);
                    } else {
                        this.showErrorNotification(response.data.message || `Batch ${action} failed`);
                    }
                },
                complete: () => {
                    $button.prop('disabled', false).removeClass('edd-loading');
                }
            });
        }

        handleBatchActionSuccess(action, data, $button) {
            // Refresh the current table/section
            const $container = $button.closest('.edd-batch-container');
            const $table = $container.find('.edd-table');
            
            if ($table.length) {
                const tableType = $table.data('table-type');
                this.loadTable(tableType);
            }

            // Clear selections
            $container.find('.edd-batch-checkbox').prop('checked', false);
            
            this.cache.$dashboard.trigger('batchActionCompleted', [action, data, $button]);
        }

        // Global AJAX Event Handlers
        handleAjaxStart() {
            this.cache.$body.addClass('edd-ajax-loading');
        }

        handleAjaxStop() {
            this.cache.$body.removeClass('edd-ajax-loading');
        }

        handleAjaxError(event, xhr, settings, error) {
            // Only handle our plugin's AJAX requests
            if (settings.data && typeof settings.data === 'string' && settings.data.includes('edd_dashboard_pro_')) {
                console.error('EDD Dashboard AJAX Error:', error);
            }
        }

        handleAjaxSuccess(event, xhr, settings) {
            // Handle successful AJAX responses
            if (settings.data && typeof settings.data === 'string' && settings.data.includes('edd_dashboard_pro_')) {
                this.trackAjaxSuccess(settings);
            }
        }

        trackAjaxSuccess(settings) {
            // Track successful AJAX requests for analytics
            if (window.eddDashboardAnalytics) {
                window.eddDashboardAnalytics.trackAjaxRequest(settings);
            }
        }

        // Utility Methods
        refreshAllData() {
            this.refreshStats();
            this.refreshCurrentSection();
        }

        refreshCurrentSection() {
            const currentSection = this.getCurrentSection();
            if (currentSection) {
                this.loadSection(currentSection);
            }
        }

        refreshLicenseData() {
            if ($('.edd-content-section[id="edd-section-licenses"]').hasClass('active')) {
                this.loadSection('licenses');
            }
        }

        getCurrentSection() {
            return $('.edd-nav-tab.active').data('section') || 'purchases';
        }

        generateRequestId() {
            return 'req_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
        }

        getAjaxUrl() {
            return (typeof eddDashboardPro !== 'undefined' && eddDashboardPro.ajaxUrl) || '/wp-admin/admin-ajax.php';
        }

        getNonce() {
            return (typeof eddDashboardPro !== 'undefined' && eddDashboardPro.nonce) || '';
        }

        showSuccessNotification(message) {
            if (window.eddDashboard && typeof window.eddDashboard.showNotification === 'function') {
                window.eddDashboard.showNotification(message, 'success');
            }
        }

        showErrorNotification(message) {
            if (window.eddDashboard && typeof window.eddDashboard.showNotification === 'function') {
                window.eddDashboard.showNotification(message, 'error');
            }
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        if ($('.edd-customer-dashboard-pro').length) {
            window.eddAjaxHandlers = new EDDAjaxHandlers();
        }
    });

    // Expose class globally
    window.EDDAjaxHandlers = EDDAjaxHandlers;

})(jQuery);
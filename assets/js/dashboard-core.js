/**
 * Core Dashboard JavaScript for EDD Customer Dashboard Pro
 * 
 * Handles main dashboard functionality, navigation, and interactions
 *
 * @package EDD_Customer_Dashboard_Pro
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // Main Dashboard Class
    class EDDDashboardPro {
        constructor() {
            this.init();
            this.bindEvents();
            this.initializeComponents();
        }

        init() {
            this.cache = {
                $window: $(window),
                $document: $(document),
                $body: $('body'),
                $dashboard: $('.edd-customer-dashboard-pro'),
                $navTabs: $('.edd-nav-tab'),
                $contentSections: $('.edd-content-section'),
                $statCards: $('.edd-stat-card'),
                $loadingElements: $('.edd-loading'),
                $tables: $('.edd-table'),
                $forms: $('.edd-dashboard-form')
            };

            this.settings = {
                animationSpeed: 300,
                fadeSpeed: 200,
                ajaxTimeout: 10000,
                debounceDelay: 300,
                autoRefreshInterval: 300000, // 5 minutes
                storagePrefix: 'edd_dashboard_pro_'
            };

            this.state = {
                currentSection: this.getCurrentSection(),
                isLoading: false,
                lastRefresh: Date.now(),
                activeFilters: {},
                sortBy: 'date',
                sortOrder: 'desc'
            };

            // Merge with localized settings if available
            if (typeof eddDashboardPro !== 'undefined') {
                this.settings = $.extend(this.settings, eddDashboardPro.settings || {});
            }
        }

        bindEvents() {
            // Navigation events
            this.cache.$navTabs.on('click', this.handleTabClick.bind(this));
            this.cache.$window.on('popstate', this.handlePopState.bind(this));

            // Table events
            this.cache.$tables.on('click', '.edd-table-sort', this.handleTableSort.bind(this));
            this.cache.$tables.on('click', '.edd-pagination a', this.handlePagination.bind(this));

            // Form events
            this.cache.$forms.on('submit', this.handleFormSubmit.bind(this));
            this.cache.$document.on('click', '.edd-btn[data-action]', this.handleActionButton.bind(this));

            // Download events
            this.cache.$document.on('click', '.edd-download-btn', this.handleDownload.bind(this));
            this.cache.$document.on('click', '.edd-redownload-btn', this.handleRedownload.bind(this));

            // Wishlist events
            this.cache.$document.on('click', '.edd-wishlist-toggle', this.handleWishlistToggle.bind(this));

            // License events
            this.cache.$document.on('click', '.edd-license-activate', this.handleLicenseActivate.bind(this));
            this.cache.$document.on('click', '.edd-license-deactivate', this.handleLicenseDeactivate.bind(this));

            // Copy to clipboard events
            this.cache.$document.on('click', '.edd-copy-btn', this.handleCopyToClipboard.bind(this));

            // Search and filter events
            this.cache.$document.on('input', '.edd-search-input', this.debounce(this.handleSearch.bind(this), this.settings.debounceDelay));
            this.cache.$document.on('change', '.edd-filter-select', this.handleFilterChange.bind(this));

            // Window events
            this.cache.$window.on('resize', this.debounce(this.handleResize.bind(this), this.settings.debounceDelay));
            this.cache.$window.on('beforeunload', this.handleBeforeUnload.bind(this));

            // Keyboard events
            this.cache.$document.on('keydown', this.handleKeyboard.bind(this));

            // Auto-refresh
            if (this.settings.autoRefresh) {
                setInterval(() => {
                    this.refreshData();
                }, this.settings.autoRefreshInterval);
            }
        }

        initializeComponents() {
            this.initializeNavigation();
            this.initializeTables();
            this.initializeCharts();
            this.initializeTooltips();
            this.initializeModals();
            this.initializeLazyLoading();
            this.restoreUserPreferences();
        }

        // Navigation Methods
        handleTabClick(e) {
            e.preventDefault();
            
            const $tab = $(e.currentTarget);
            const section = $tab.data('section');
            
            if (!section || $tab.hasClass('active')) {
                return;
            }

            this.switchToSection(section);
        }

        switchToSection(section) {
            if (this.state.isLoading) {
                return;
            }

            // Update navigation
            this.cache.$navTabs.removeClass('active').attr('aria-selected', 'false');
            $(`.edd-nav-tab[data-section="${section}"]`).addClass('active').attr('aria-selected', 'true');

            // Update content
            this.cache.$contentSections.removeClass('active').hide();
            const $targetSection = $(`#edd-section-${section}`);
            
            if ($targetSection.length) {
                $targetSection.addClass('active').fadeIn(this.settings.fadeSpeed);
            } else {
                this.loadSectionContent(section);
            }

            // Update state and URL
            this.state.currentSection = section;
            this.updateURL(section);
            this.saveUserPreference('currentSection', section);

            // Trigger section change event
            this.cache.$dashboard.trigger('sectionChange', [section]);
        }

        loadSectionContent(section) {
            this.setLoading(true);

            const data = {
                action: 'edd_dashboard_pro_load_section',
                section: section,
                nonce: this.getNonce()
            };

            $.ajax({
                url: this.getAjaxUrl(),
                type: 'POST',
                data: data,
                timeout: this.settings.ajaxTimeout,
                success: (response) => {
                    if (response.success) {
                        this.renderSectionContent(section, response.data);
                    } else {
                        this.showNotification(response.data.message || 'Error loading section', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Failed to load section content', 'error');
                },
                complete: () => {
                    this.setLoading(false);
                }
            });
        }

        renderSectionContent(section, data) {
            const $container = $('.edd-dashboard-content');
            const $existingSection = $(`#edd-section-${section}`);

            if ($existingSection.length) {
                $existingSection.html(data.html);
            } else {
                const $newSection = $(`<div class="edd-content-section active" id="edd-section-${section}">${data.html}</div>`);
                $container.append($newSection);
            }

            // Hide other sections and show current
            this.cache.$contentSections.removeClass('active').hide();
            $(`#edd-section-${section}`).addClass('active').fadeIn(this.settings.fadeSpeed);

            // Reinitialize components for new content
            this.initializeNewContent($(`#edd-section-${section}`));
        }

        getCurrentSection() {
            const urlParams = new URLSearchParams(window.location.search);
            const section = urlParams.get('section') || this.getUserPreference('currentSection') || 'purchases';
            return section;
        }

        updateURL(section) {
            const url = new URL(window.location);
            url.searchParams.set('section', section);
            window.history.pushState({section: section}, '', url);
        }

        handlePopState(e) {
            const section = e.originalEvent.state?.section || this.getCurrentSection();
            this.switchToSection(section);
        }

        // Table Methods
        initializeTables() {
            this.cache.$tables.each((index, table) => {
                const $table = $(table);
                
                // Add responsive wrapper if not present
                if (!$table.parent('.edd-table-responsive').length) {
                    $table.wrap('<div class="edd-table-responsive"></div>');
                }

                // Initialize sorting
                this.initializeTableSorting($table);
            });
        }

        initializeTableSorting($table) {
            const $headers = $table.find('th[data-sortable]');
            
            $headers.each((index, header) => {
                const $header = $(header);
                const field = $header.data('sortable');
                
                $header.addClass('edd-table-sort')
                       .attr('role', 'button')
                       .attr('tabindex', '0')
                       .append('<span class="edd-sort-indicator"></span>');
            });
        }

        handleTableSort(e) {
            e.preventDefault();
            
            const $header = $(e.currentTarget);
            const field = $header.data('sortable');
            const currentOrder = $header.hasClass('edd-sort-asc') ? 'asc' : 'desc';
            const newOrder = currentOrder === 'asc' ? 'desc' : 'asc';

            // Update state
            this.state.sortBy = field;
            this.state.sortOrder = newOrder;

            // Update UI
            $header.siblings().removeClass('edd-sort-asc edd-sort-desc');
            $header.removeClass('edd-sort-asc edd-sort-desc').addClass(`edd-sort-${newOrder}`);

            // Reload table data
            this.reloadTableData($header.closest('.edd-table'));
        }

        reloadTableData($table) {
            const section = this.state.currentSection;
            const tableType = $table.data('table-type') || section;

            this.setLoading(true);

            const data = {
                action: 'edd_dashboard_pro_load_table',
                table_type: tableType,
                sort_by: this.state.sortBy,
                sort_order: this.state.sortOrder,
                filters: this.state.activeFilters,
                nonce: this.getNonce()
            };

            $.ajax({
                url: this.getAjaxUrl(),
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        $table.closest('.edd-table-container').html(response.data.html);
                        this.initializeNewContent($table.closest('.edd-table-container'));
                    } else {
                        this.showNotification(response.data.message || 'Error loading table data', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Failed to load table data', 'error');
                },
                complete: () => {
                    this.setLoading(false);
                }
            });
        }

        handlePagination(e) {
            e.preventDefault();
            
            const $link = $(e.currentTarget);
            const page = $link.data('page');
            const $table = $link.closest('.edd-table-container').find('.edd-table');

            this.loadTablePage($table, page);
        }

        loadTablePage($table, page) {
            const section = this.state.currentSection;
            const tableType = $table.data('table-type') || section;

            this.setLoading(true);

            const data = {
                action: 'edd_dashboard_pro_load_table',
                table_type: tableType,
                page: page,
                sort_by: this.state.sortBy,
                sort_order: this.state.sortOrder,
                filters: this.state.activeFilters,
                nonce: this.getNonce()
            };

            $.ajax({
                url: this.getAjaxUrl(),
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        $table.closest('.edd-table-container').html(response.data.html);
                        this.initializeNewContent($table.closest('.edd-table-container'));
                    }
                },
                complete: () => {
                    this.setLoading(false);
                }
            });
        }

        // Action Button Methods
        handleActionButton(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const action = $btn.data('action');
            const confirm = $btn.data('confirm');

            if (confirm && !window.confirm(confirm)) {
                return;
            }

            this.performAction(action, $btn);
        }

        performAction(action, $btn) {
            const data = {
                action: `edd_dashboard_pro_${action}`,
                nonce: this.getNonce()
            };

            // Add button-specific data
            $.each($btn.data(), (key, value) => {
                if (key !== 'action' && key !== 'confirm') {
                    data[key] = value;
                }
            });

            $btn.prop('disabled', true).addClass('edd-loading');

            $.ajax({
                url: this.getAjaxUrl(),
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        this.showNotification(response.data.message || 'Action completed successfully', 'success');
                        
                        // Handle specific actions
                        this.handleActionSuccess(action, response.data, $btn);
                    } else {
                        this.showNotification(response.data.message || 'Action failed', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Request failed', 'error');
                },
                complete: () => {
                    $btn.prop('disabled', false).removeClass('edd-loading');
                }
            });
        }

        handleActionSuccess(action, data, $btn) {
            switch (action) {
                case 'refresh_stats':
                    this.refreshStatCards();
                    break;
                case 'clear_cache':
                    this.refreshCurrentSection();
                    break;
                case 'export_data':
                    if (data.download_url) {
                        window.location.href = data.download_url;
                    }
                    break;
                default:
                    // Trigger custom event for other actions
                    this.cache.$dashboard.trigger('actionSuccess', [action, data, $btn]);
                    break;
            }
        }

        // Download Methods
        handleDownload(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const downloadId = $btn.data('download-id');
            const fileId = $btn.data('file-id');

            this.trackDownload(downloadId, fileId);
            
            // Let the browser handle the actual download
            window.location.href = $btn.attr('href');
        }

        handleRedownload(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const purchaseId = $btn.data('purchase-id');
            const downloadId = $btn.data('download-id');

            this.performRedownload(purchaseId, downloadId, $btn);
        }

        performRedownload(purchaseId, downloadId, $btn) {
            const data = {
                action: 'edd_dashboard_pro_redownload',
                purchase_id: purchaseId,
                download_id: downloadId,
                nonce: this.getNonce()
            };

            $btn.prop('disabled', true).addClass('edd-loading');

            $.ajax({
                url: this.getAjaxUrl(),
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        window.location.href = response.data.download_url;
                        this.showNotification('Download started', 'success');
                    } else {
                        this.showNotification(response.data.message || 'Download failed', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Download request failed', 'error');
                },
                complete: () => {
                    $btn.prop('disabled', false).removeClass('edd-loading');
                }
            });
        }

        trackDownload(downloadId, fileId) {
            const data = {
                action: 'edd_dashboard_pro_track_download',
                download_id: downloadId,
                file_id: fileId,
                nonce: this.getNonce()
            };

            // Send tracking request (don't wait for response)
            $.post(this.getAjaxUrl(), data);
        }

        // Wishlist Methods
        handleWishlistToggle(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const downloadId = $btn.data('download-id');
            const isInWishlist = $btn.hasClass('edd-in-wishlist');

            this.toggleWishlist(downloadId, !isInWishlist, $btn);
        }

        toggleWishlist(downloadId, add, $btn) {
            const action = add ? 'add_to_wishlist' : 'remove_from_wishlist';
            
            const data = {
                action: `edd_dashboard_pro_${action}`,
                download_id: downloadId,
                nonce: this.getNonce()
            };

            $btn.prop('disabled', true);

            $.ajax({
                url: this.getAjaxUrl(),
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        $btn.toggleClass('edd-in-wishlist', add);
                        $btn.find('.edd-wishlist-text').text(add ? 'Remove from Wishlist' : 'Add to Wishlist');
                        
                        this.showNotification(response.data.message, 'success');
                        this.updateWishlistCount(response.data.count);
                    } else {
                        this.showNotification(response.data.message || 'Wishlist action failed', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Wishlist request failed', 'error');
                },
                complete: () => {
                    $btn.prop('disabled', false);
                }
            });
        }

        updateWishlistCount(count) {
            $('.edd-wishlist-count').text(count);
            $(`.edd-nav-tab[data-section="wishlist"] .edd-tab-count`).text(count);
        }

        // License Methods
        handleLicenseActivate(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const licenseKey = $btn.data('license-key');
            const downloadId = $btn.data('download-id');

            this.activateLicense(licenseKey, downloadId, $btn);
        }

        handleLicenseDeactivate(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const licenseKey = $btn.data('license-key');
            const downloadId = $btn.data('download-id');

            this.deactivateLicense(licenseKey, downloadId, $btn);
        }

        activateLicense(licenseKey, downloadId, $btn) {
            const data = {
                action: 'edd_dashboard_pro_activate_license',
                license_key: licenseKey,
                download_id: downloadId,
                nonce: this.getNonce()
            };

            $btn.prop('disabled', true).addClass('edd-loading');

            $.ajax({
                url: this.getAjaxUrl(),
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        this.updateLicenseStatus($btn, 'active', response.data);
                        this.showNotification('License activated successfully', 'success');
                    } else {
                        this.showNotification(response.data.message || 'License activation failed', 'error');
                    }
                },
                error: () => {
                    this.showNotification('License activation request failed', 'error');
                },
                complete: () => {
                    $btn.prop('disabled', false).removeClass('edd-loading');
                }
            });
        }

        deactivateLicense(licenseKey, downloadId, $btn) {
            const data = {
                action: 'edd_dashboard_pro_deactivate_license',
                license_key: licenseKey,
                download_id: downloadId,
                nonce: this.getNonce()
            };

            $btn.prop('disabled', true).addClass('edd-loading');

            $.ajax({
                url: this.getAjaxUrl(),
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        this.updateLicenseStatus($btn, 'inactive', response.data);
                        this.showNotification('License deactivated successfully', 'success');
                    } else {
                        this.showNotification(response.data.message || 'License deactivation failed', 'error');
                    }
                },
                error: () => {
                    this.showNotification('License deactivation request failed', 'error');
                },
                complete: () => {
                    $btn.prop('disabled', false).removeClass('edd-loading');
                }
            });
        }

        updateLicenseStatus($btn, status, data) {
            const $row = $btn.closest('.edd-license-row');
            const $statusBadge = $row.find('.edd-license-status');
            const $actionBtn = $row.find('.edd-license-action');

            // Update status badge
            $statusBadge.removeClass('edd-badge-success edd-badge-warning edd-badge-error')
                       .addClass(status === 'active' ? 'edd-badge-success' : 'edd-badge-warning')
                       .text(status === 'active' ? 'Active' : 'Inactive');

            // Update action button
            if (status === 'active') {
                $actionBtn.removeClass('edd-license-activate')
                         .addClass('edd-license-deactivate')
                         .text('Deactivate');
            } else {
                $actionBtn.removeClass('edd-license-deactivate')
                         .addClass('edd-license-activate')
                         .text('Activate');
            }

            // Update expiration date if provided
            if (data.expires) {
                $row.find('.edd-license-expires').text(data.expires);
            }
        }

        // Copy to Clipboard Methods
        handleCopyToClipboard(e) {
            e.preventDefault();
            
            const $btn = $(e.currentTarget);
            const text = $btn.data('copy-text') || $btn.closest('.edd-copy-container').find('.edd-copy-target').text();

            this.copyToClipboard(text, $btn);
        }

        copyToClipboard(text, $btn) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    this.showCopySuccess($btn);
                }).catch(() => {
                    this.fallbackCopyToClipboard(text, $btn);
                });
            } else {
                this.fallbackCopyToClipboard(text, $btn);
            }
        }

        fallbackCopyToClipboard(text, $btn) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                document.execCommand('copy');
                this.showCopySuccess($btn);
            } catch (err) {
                this.showNotification('Failed to copy to clipboard', 'error');
            } finally {
                document.body.removeChild(textArea);
            }
        }

        showCopySuccess($btn) {
            const originalText = $btn.text();
            $btn.text('Copied!').addClass('edd-copied');
            
            setTimeout(() => {
                $btn.text(originalText).removeClass('edd-copied');
            }, 2000);
        }

        // Search and Filter Methods
        handleSearch(e) {
            const $input = $(e.currentTarget);
            const query = $input.val().trim();
            const section = $input.data('section') || this.state.currentSection;

            this.performSearch(query, section);
        }

        performSearch(query, section) {
            this.state.activeFilters.search = query;
            
            const $table = $(`.edd-content-section[id="edd-section-${section}"] .edd-table`);
            if ($table.length) {
                this.reloadTableData($table);
            }
        }

        handleFilterChange(e) {
            const $select = $(e.currentTarget);
            const filterKey = $select.data('filter');
            const filterValue = $select.val();

            this.state.activeFilters[filterKey] = filterValue;
            
            const section = $select.data('section') || this.state.currentSection;
            const $table = $(`.edd-content-section[id="edd-section-${section}"] .edd-table`);
            
            if ($table.length) {
                this.reloadTableData($table);
            }
        }

        // Chart Methods
        initializeCharts() {
            const $charts = $('.edd-chart');
            
            $charts.each((index, element) => {
                this.initializeChart($(element));
            });
        }

        initializeChart($chart) {
            const chartType = $chart.data('chart-type');
            const chartData = $chart.data('chart-data');

            if (!chartType || !chartData) {
                return;
            }

            // Initialize chart based on type
            switch (chartType) {
                case 'line':
                    this.createLineChart($chart, chartData);
                    break;
                case 'bar':
                    this.createBarChart($chart, chartData);
                    break;
                case 'doughnut':
                    this.createDoughnutChart($chart, chartData);
                    break;
                default:
                    console.warn('Unknown chart type:', chartType);
            }
        }

        createLineChart($chart, data) {
            // Placeholder for Chart.js integration
            // This would be implemented based on the charting library chosen
            $chart.html('<div class="edd-chart-placeholder">Line Chart: ' + data.title + '</div>');
        }

        createBarChart($chart, data) {
            // Placeholder for Chart.js integration
            $chart.html('<div class="edd-chart-placeholder">Bar Chart: ' + data.title + '</div>');
        }

        createDoughnutChart($chart, data) {
            // Placeholder for Chart.js integration
            $chart.html('<div class="edd-chart-placeholder">Doughnut Chart: ' + data.title + '</div>');
        }

        // Modal Methods
        initializeModals() {
            // Handle modal triggers
            this.cache.$document.on('click', '[data-modal]', this.handleModalTrigger.bind(this));
            this.cache.$document.on('click', '.edd-modal-close, .edd-modal-backdrop', this.handleModalClose.bind(this));
            this.cache.$document.on('keydown', this.handleModalKeydown.bind(this));
        }

        handleModalTrigger(e) {
            e.preventDefault();
            
            const $trigger = $(e.currentTarget);
            const modalId = $trigger.data('modal');
            
            this.openModal(modalId);
        }

        openModal(modalId) {
            const $modal = $(`#${modalId}`);
            
            if (!$modal.length) {
                return;
            }

            $modal.addClass('edd-modal-open');
            this.cache.$body.addClass('edd-modal-open');
            
            // Focus management
            $modal.find('.edd-modal-close').focus();
            
            // Trap focus within modal
            this.trapFocus($modal);
        }

        handleModalClose(e) {
            if ($(e.target).hasClass('edd-modal-backdrop') || $(e.target).hasClass('edd-modal-close')) {
                this.closeModal();
            }
        }

        handleModalKeydown(e) {
            if (e.keyCode === 27) { // Escape key
                this.closeModal();
            }
        }

        closeModal() {
            $('.edd-modal').removeClass('edd-modal-open');
            this.cache.$body.removeClass('edd-modal-open');
        }

        trapFocus($modal) {
            const focusableElements = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            const firstElement = focusableElements.first();
            const lastElement = focusableElements.last();

            $modal.on('keydown.trapfocus', (e) => {
                if (e.keyCode === 9) { // Tab key
                    if (e.shiftKey) {
                        if (document.activeElement === firstElement[0]) {
                            e.preventDefault();
                            lastElement.focus();
                        }
                    } else {
                        if (document.activeElement === lastElement[0]) {
                            e.preventDefault();
                            firstElement.focus();
                        }
                    }
                }
            });
        }

        // Tooltip Methods
        initializeTooltips() {
            const $tooltips = $('[data-tooltip]');
            
            $tooltips.each((index, element) => {
                this.initializeTooltip($(element));
            });
        }

        initializeTooltip($element) {
            const text = $element.data('tooltip');
            const position = $element.data('tooltip-position') || 'top';

            $element.on('mouseenter focus', () => {
                this.showTooltip($element, text, position);
            });

            $element.on('mouseleave blur', () => {
                this.hideTooltip($element);
            });
        }

        showTooltip($element, text, position) {
            const $tooltip = $('<div class="edd-tooltip">' + text + '</div>');
            this.cache.$body.append($tooltip);

            const elementPos = $element.offset();
            const elementWidth = $element.outerWidth();
            const elementHeight = $element.outerHeight();
            const tooltipWidth = $tooltip.outerWidth();
            const tooltipHeight = $tooltip.outerHeight();

            let top, left;

            switch (position) {
                case 'top':
                    top = elementPos.top - tooltipHeight - 10;
                    left = elementPos.left + (elementWidth / 2) - (tooltipWidth / 2);
                    break;
                case 'bottom':
                    top = elementPos.top + elementHeight + 10;
                    left = elementPos.left + (elementWidth / 2) - (tooltipWidth / 2);
                    break;
                case 'left':
                    top = elementPos.top + (elementHeight / 2) - (tooltipHeight / 2);
                    left = elementPos.left - tooltipWidth - 10;
                    break;
                case 'right':
                    top = elementPos.top + (elementHeight / 2) - (tooltipHeight / 2);
                    left = elementPos.left + elementWidth + 10;
                    break;
            }

            $tooltip.css({
                top: top,
                left: left,
                position: 'absolute',
                zIndex: 1000
            }).addClass(`edd-tooltip-${position}`);

            $element.data('tooltip-element', $tooltip);
        }

        hideTooltip($element) {
            const $tooltip = $element.data('tooltip-element');
            if ($tooltip) {
                $tooltip.remove();
                $element.removeData('tooltip-element');
            }
        }

        // Lazy Loading Methods
        initializeLazyLoading() {
            const $lazyElements = $('.edd-lazy-load');
            
            if ('IntersectionObserver' in window) {
                this.setupIntersectionObserver($lazyElements);
            } else {
                // Fallback for older browsers
                this.loadAllLazyElements($lazyElements);
            }
        }

        setupIntersectionObserver($elements) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        this.loadLazyElement($(entry.target));
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '100px'
            });

            $elements.each((index, element) => {
                observer.observe(element);
            });
        }

        loadLazyElement($element) {
            const src = $element.data('src');
            const content = $element.data('content');

            if (src && $element.is('img')) {
                $element.attr('src', src).removeClass('edd-lazy-load');
            } else if (content) {
                this.loadLazyContent($element, content);
            }
        }

        loadLazyContent($element, contentType) {
            const data = {
                action: 'edd_dashboard_pro_load_lazy_content',
                content_type: contentType,
                element_id: $element.attr('id'),
                nonce: this.getNonce()
            };

            $.ajax({
                url: this.getAjaxUrl(),
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        $element.html(response.data.html).removeClass('edd-lazy-load');
                        this.initializeNewContent($element);
                    }
                }
            });
        }

        loadAllLazyElements($elements) {
            $elements.each((index, element) => {
                this.loadLazyElement($(element));
            });
        }

        // Utility Methods
        initializeNewContent($container) {
            // Reinitialize components for dynamically loaded content
            this.initializeTooltips();
            this.initializeCharts();
            
            // Initialize any tables in the new content
            $container.find('.edd-table').each((index, table) => {
                this.initializeTableSorting($(table));
            });

            // Initialize any lazy loading elements
            const $lazyElements = $container.find('.edd-lazy-load');
            if ($lazyElements.length) {
                this.initializeLazyLoading();
            }

            // Trigger event for custom initialization
            this.cache.$dashboard.trigger('contentInitialized', [$container]);
        }

        refreshData() {
            if (this.state.isLoading || Date.now() - this.state.lastRefresh < 60000) {
                return; // Don't refresh too frequently
            }

            this.refreshStatCards();
            this.refreshCurrentSection();
            this.state.lastRefresh = Date.now();
        }

        refreshStatCards() {
            const $statCards = $('.edd-stats-grid');
            
            if (!$statCards.length) {
                return;
            }

            const data = {
                action: 'edd_dashboard_pro_refresh_stats',
                nonce: this.getNonce()
            };

            $.ajax({
                url: this.getAjaxUrl(),
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        $statCards.html(response.data.html);
                        this.initializeNewContent($statCards);
                    }
                }
            });
        }

        refreshCurrentSection() {
            const $currentSection = $('.edd-content-section.active');
            if ($currentSection.length) {
                this.loadSectionContent(this.state.currentSection);
            }
        }

        setLoading(isLoading) {
            this.state.isLoading = isLoading;
            this.cache.$dashboard.toggleClass('edd-loading', isLoading);
            
            if (isLoading) {
                this.cache.$loadingElements.show();
            } else {
                this.cache.$loadingElements.hide();
            }
        }

        showNotification(message, type = 'info', duration = 5000) {
            const $notification = $(`
                <div class="edd-notification edd-notification-${type}">
                    <span class="edd-notification-message">${message}</span>
                    <button class="edd-notification-close">&times;</button>
                </div>
            `);

            $('.edd-notifications').prepend($notification);

            // Auto-dismiss after duration
            setTimeout(() => {
                $notification.fadeOut(() => {
                    $notification.remove();
                });
            }, duration);

            // Manual dismiss
            $notification.find('.edd-notification-close').on('click', () => {
                $notification.fadeOut(() => {
                    $notification.remove();
                });
            });
        }

        handleResize() {
            // Handle responsive adjustments
            this.adjustForScreenSize();
            this.repositionTooltips();
        }

        adjustForScreenSize() {
            const width = this.cache.$window.width();
            
            // Adjust navigation for mobile
            if (width < 768) {
                this.cache.$navTabs.addClass('edd-nav-mobile');
            } else {
                this.cache.$navTabs.removeClass('edd-nav-mobile');
            }

            // Adjust tables for mobile
            this.cache.$tables.each((index, table) => {
                const $table = $(table);
                if (width < 768) {
                    $table.addClass('edd-table-mobile');
                } else {
                    $table.removeClass('edd-table-mobile');
                }
            });
        }

        repositionTooltips() {
            // Reposition any visible tooltips
            $('[data-tooltip-element]').each((index, element) => {
                const $element = $(element);
                const $tooltip = $element.data('tooltip-element');
                if ($tooltip && $tooltip.is(':visible')) {
                    this.hideTooltip($element);
                    this.showTooltip($element, $tooltip.text(), $tooltip.data('position'));
                }
            });
        }

        handleKeyboard(e) {
            // Handle keyboard navigation
            if (e.altKey && e.keyCode >= 49 && e.keyCode <= 57) {
                // Alt + 1-9 to switch between sections
                e.preventDefault();
                const sectionIndex = e.keyCode - 49;
                const $tab = this.cache.$navTabs.eq(sectionIndex);
                if ($tab.length) {
                    $tab.trigger('click');
                }
            }
        }

        handleBeforeUnload(e) {
            // Save user preferences before leaving
            this.saveAllUserPreferences();
        }

        handleFormSubmit(e) {
            const $form = $(e.currentTarget);
            const action = $form.data('action');

            if (!action) {
                return;
            }

            e.preventDefault();
            this.submitForm($form, action);
        }

        submitForm($form, action) {
            const formData = new FormData($form[0]);
            formData.append('action', action);
            formData.append('nonce', this.getNonce());

            $form.find('[type="submit"]').prop('disabled', true).addClass('edd-loading');

            $.ajax({
                url: this.getAjaxUrl(),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.showNotification(response.data.message || 'Form submitted successfully', 'success');
                        this.handleFormSuccess($form, response.data);
                    } else {
                        this.showNotification(response.data.message || 'Form submission failed', 'error');
                        this.handleFormError($form, response.data);
                    }
                },
                error: () => {
                    this.showNotification('Form submission failed', 'error');
                },
                complete: () => {
                    $form.find('[type="submit"]').prop('disabled', false).removeClass('edd-loading');
                }
            });
        }

        handleFormSuccess($form, data) {
            // Reset form if successful
            if (data.reset_form) {
                $form[0].reset();
            }

            // Redirect if specified
            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            }

            // Refresh section if needed
            if (data.refresh_section) {
                this.refreshCurrentSection();
            }
        }

        handleFormError($form, data) {
            // Highlight error fields
            if (data.error_fields) {
                data.error_fields.forEach((field) => {
                    $form.find(`[name="${field}"]`).addClass('edd-field-error');
                });
            }
        }

        // User Preference Methods
        saveUserPreference(key, value) {
            if (typeof Storage !== 'undefined') {
                localStorage.setItem(this.settings.storagePrefix + key, JSON.stringify(value));
            }
        }

        getUserPreference(key, defaultValue = null) {
            if (typeof Storage !== 'undefined') {
                const stored = localStorage.getItem(this.settings.storagePrefix + key);
                return stored ? JSON.parse(stored) : defaultValue;
            }
            return defaultValue;
        }

        saveAllUserPreferences() {
            this.saveUserPreference('currentSection', this.state.currentSection);
            this.saveUserPreference('sortBy', this.state.sortBy);
            this.saveUserPreference('sortOrder', this.state.sortOrder);
            this.saveUserPreference('activeFilters', this.state.activeFilters);
        }

        restoreUserPreferences() {
            this.state.sortBy = this.getUserPreference('sortBy', 'date');
            this.state.sortOrder = this.getUserPreference('sortOrder', 'desc');
            this.state.activeFilters = this.getUserPreference('activeFilters', {});
        }

        // Helper Methods
        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        getAjaxUrl() {
            return (typeof eddDashboardPro !== 'undefined' && eddDashboardPro.ajaxUrl) || '/wp-admin/admin-ajax.php';
        }

        getNonce() {
            return (typeof eddDashboardPro !== 'undefined' && eddDashboardPro.nonce) || '';
        }
    }

    // Initialize when document is ready
    $(document).ready(() => {
        // Only initialize if dashboard wrapper exists
        if ($('.edd-customer-dashboard-pro').length) {
            window.eddDashboard = new EDDDashboardPro();
        }
    });

    // Expose class globally for extensibility
    window.EDDDashboardPro = EDDDashboardPro;

})(jQuery);
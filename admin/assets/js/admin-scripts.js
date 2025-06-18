/**
 * Admin Scripts for EDD Customer Dashboard Pro
 * JavaScript functionality for the admin panel
 *
 * @package EDD_Customer_Dashboard_Pro
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Main Admin object
     */
    const EDD_Dashboard_Pro_Admin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.bindEvents();
            this.initializeComponents();
            this.setupFormValidation();
            this.handleTabNavigation();
            this.initializeTooltips();
            this.setupAutoSave();
            this.handleTemplatePreview();
            this.initializeColorPickers();
            this.setupProgressTracking();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Settings form submission
            $('.settings-form').on('submit', this.handleFormSubmit.bind(this));
            
            // Template selection
            $(document).on('change', 'input[name="template"]', this.handleTemplateChange.bind(this));
            
            // Cache management
            $('.clear-cache-btn').on('click', this.clearCache.bind(this));
            $('.manual-cleanup').on('click', this.runManualCleanup.bind(this));
            
            // Debug tools
            $('.view-debug-log').on('click', this.viewDebugLog.bind(this));
            $('.clear-debug-log').on('click', this.clearDebugLog.bind(this));
            $('.download-debug-log').on('click', this.downloadDebugLog.bind(this));
            
            // System info tools
            $('.copy-system-info').on('click', this.copySystemInfo.bind(this));
            $('.export-settings').on('click', this.exportSettings.bind(this));
            $('.import-settings-btn').on('click', this.triggerImport.bind(this));
            $('#import-file').on('change', this.handleImportFile.bind(this));
            
            // Settings validation
            $('.validate-css').on('click', this.validateCSS.bind(this));
            $('.reset-section').on('click', this.resetSection.bind(this));
            $('.reset-template-settings').on('click', this.resetTemplateSettings.bind(this));
            
            // Color picker synchronization
            $('.color-picker').on('change', this.syncColorPicker.bind(this));
            $('.color-text').on('input', this.syncColorText.bind(this));
            
            // Range slider updates
            $('.border-radius-slider').on('input', this.updateRangeValue.bind(this));
            
            // CSS helper tools
            $('.toggle-css-help').on('click', this.toggleCSSHelp.bind(this));
            $('.css-tab-btn').on('click', this.switchCSSTab.bind(this));
            $('.insert-css').on('click', this.insertCSS.bind(this));
            
            // Conditional field toggles
            $('#cache_customer_data').on('change', this.toggleCacheDuration.bind(this));
            $('#debug_mode').on('change', this.toggleDebugOptions.bind(this));
            $('#show_welcome_message').on('change', this.toggleWelcomeOptions.bind(this));
            
            // Dashboard page validation
            $('#dashboard_page').on('change', this.validateDashboardPage.bind(this));
            
            // Welcome message preview
            $('#welcome_message_text').on('input', this.updateWelcomePreview.bind(this));
            
            // Navigation confirmation
            $(window).on('beforeunload', this.handlePageUnload.bind(this));
        },

        /**
         * Initialize components
         */
        initializeComponents: function() {
            // Initialize tabs
            this.initializeTabs();
            
            // Setup notices
            this.setupNotices();
            
            // Initialize collapsible sections
            this.initializeCollapsibles();
            
            // Setup keyboard shortcuts
            this.setupKeyboardShortcuts();
            
            // Initialize drag and drop for file imports
            this.initializeDragDrop();
            
            // Setup real-time validation
            this.setupRealtimeValidation();
        },

        /**
         * Initialize tab functionality
         */
        initializeTabs: function() {
            const $tabs = $('.settings-nav-link');
            const $tabContents = $('.tab-content > div');
            
            $tabs.on('click', function(e) {
                e.preventDefault();
                
                const targetTab = $(this).attr('href').replace('#', '');
                
                // Update active tab
                $tabs.removeClass('active');
                $(this).addClass('active');
                
                // Show target content
                $tabContents.hide();
                $('#' + targetTab).show();
                
                // Update URL hash
                if (history.pushState) {
                    history.pushState(null, null, '#' + targetTab);
                }
            });
            
            // Handle initial hash
            const hash = window.location.hash.replace('#', '');
            if (hash && $('#' + hash).length) {
                $('a[href="#' + hash + '"]').click();
            }
        },

        /**
         * Setup form validation
         */
        setupFormValidation: function() {
            const self = this;
            
            // Real-time validation for required fields
            $('input[required], select[required], textarea[required]').on('blur', function() {
                self.validateField($(this));
            });
            
            // Email validation
            $('input[type="email"]').on('blur', function() {
                self.validateEmail($(this));
            });
            
            // URL validation
            $('input[type="url"]').on('blur', function() {
                self.validateURL($(this));
            });
            
            // Number validation
            $('input[type="number"]').on('input', function() {
                self.validateNumber($(this));
            });
        },

        /**
         * Handle form submission
         */
        handleFormSubmit: function(e) {
            const $form = $(e.target);
            const $submitBtn = $form.find('input[type="submit"], button[type="submit"]');
            
            // Validate form
            // Validate form
            if (!this.validateForm($form)) {
                e.preventDefault();
                this.showNotice('Please fix the validation errors before submitting.', 'error');
                return false;
            }
            
            // Show loading state
            const originalText = $submitBtn.val() || $submitBtn.text();
            $submitBtn.prop('disabled', true)
                     .val('Saving...')
                     .text('Saving...')
                     .addClass('loading');
            
            // Mark form as submitted to prevent unload warning
            this.formSubmitted = true;
            
            // Reset button after delay if form doesn't actually submit
            setTimeout(function() {
                if ($submitBtn.prop('disabled')) {
                    $submitBtn.prop('disabled', false)
                             .val(originalText)
                             .text(originalText)
                             .removeClass('loading');
                }
            }, 10000);
        },

        /**
         * Validate entire form
         */
        validateForm: function($form) {
            let isValid = true;
            const self = this;
            
            // Clear previous errors
            $form.find('.validation-error').remove();
            $form.find('.form-invalid').removeClass('form-invalid');
            
            // Validate each field
            $form.find('input, select, textarea').each(function() {
                if (!self.validateField($(this))) {
                    isValid = false;
                }
            });
            
            return isValid;
        },

        /**
         * Validate individual field
         */
        validateField: function($field) {
            const value = $field.val();
            const fieldType = $field.attr('type');
            const isRequired = $field.attr('required');
            let isValid = true;
            let errorMessage = '';
            
            // Clear previous error state
            $field.removeClass('form-invalid');
            $field.next('.validation-error').remove();
            
            // Required field validation
            if (isRequired && (!value || value.trim() === '')) {
                isValid = false;
                errorMessage = 'This field is required.';
            }
            
            // Type-specific validation
            if (value && isValid) {
                switch (fieldType) {
                    case 'email':
                        isValid = this.validateEmail($field);
                        break;
                    case 'url':
                        isValid = this.validateURL($field);
                        break;
                    case 'number':
                        isValid = this.validateNumber($field);
                        break;
                }
            }
            
            // Show error if invalid
            if (!isValid && errorMessage) {
                $field.addClass('form-invalid');
                $field.after('<div class="validation-error">' + errorMessage + '</div>');
            }
            
            return isValid;
        },

        /**
         * Validate email field
         */
        validateEmail: function($field) {
            const email = $field.val();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                $field.addClass('form-invalid');
                $field.next('.validation-error').remove();
                $field.after('<div class="validation-error">Please enter a valid email address.</div>');
                return false;
            }
            
            return true;
        },

        /**
         * Validate URL field
         */
        validateURL: function($field) {
            const url = $field.val();
            
            if (url) {
                try {
                    new URL(url);
                } catch (e) {
                    $field.addClass('form-invalid');
                    $field.next('.validation-error').remove();
                    $field.after('<div class="validation-error">Please enter a valid URL.</div>');
                    return false;
                }
            }
            
            return true;
        },

        /**
         * Validate number field
         */
        validateNumber: function($field) {
            const value = $field.val();
            const min = parseFloat($field.attr('min'));
            const max = parseFloat($field.attr('max'));
            
            if (value) {
                const numValue = parseFloat(value);
                
                if (isNaN(numValue)) {
                    $field.addClass('form-invalid');
                    return false;
                }
                
                if (!isNaN(min) && numValue < min) {
                    $field.addClass('form-invalid');
                    $field.next('.validation-error').remove();
                    $field.after('<div class="validation-error">Value must be at least ' + min + '.</div>');
                    return false;
                }
                
                if (!isNaN(max) && numValue > max) {
                    $field.addClass('form-invalid');
                    $field.next('.validation-error').remove();
                    $field.after('<div class="validation-error">Value must be no more than ' + max + '.</div>');
                    return false;
                }
            }
            
            $field.removeClass('form-invalid');
            $field.next('.validation-error').remove();
            return true;
        },

        /**
         * Handle template change
         */
        handleTemplateChange: function(e) {
            const $radio = $(e.target);
            const templateKey = $radio.val();
            
            // Update visual selection
            $('.template-option-card').removeClass('selected');
            $radio.closest('.template-option-card').addClass('selected');
            
            // Show preview button
            $('.preview-template').show();
            $radio.closest('.template-option-card').find('.preview-template').focus();
            
            // Mark form as changed
            this.markFormChanged();
            
            // Update template info in sidebar if exists
            this.updateTemplateInfo(templateKey);
        },

        /**
         * Update template info display
         */
        updateTemplateInfo: function(templateKey) {
            // This would typically make an AJAX call to get template info
            // For now, we'll just update based on available data
            const $templateCard = $('input[value="' + templateKey + '"]').closest('.template-option-card');
            const templateName = $templateCard.find('.template-name').text();
            const templateVersion = $templateCard.find('.template-version').text();
            
            // Update sidebar info if it exists
            $('.current-template-info .template-name').text(templateName);
            $('.current-template-info .template-version').text(templateVersion);
        },

        /**
         * Handle template preview
         */
        handleTemplatePreview: function() {
            $(document).on('click', '.preview-template', function(e) {
                e.preventDefault();
                
                const $button = $(this);
                const template = $button.data('template') || $('input[name="template"]:checked').val();
                const originalText = $button.text();
                
                if (!template) {
                    alert('Please select a template first.');
                    return;
                }
                
                $button.text('Loading Preview...').prop('disabled', true);
                
                $.post(ajaxurl, {
                    action: 'edd_dashboard_pro_preview_template',
                    template: template,
                    nonce: eddDashboardProAdmin.nonce
                })
                .done(function(response) {
                    if (response.success && response.data.preview_url) {
                        window.open(response.data.preview_url, '_blank', 'width=1200,height=800');
                    } else {
                        alert(response.data.message || 'Preview failed. Please try again.');
                    }
                })
                .fail(function() {
                    alert('Preview failed. Please check your connection and try again.');
                })
                .always(function() {
                    $button.text(originalText).prop('disabled', false);
                });
            });
        },

        /**
         * Initialize color pickers
         */
        initializeColorPickers: function() {
            // Sync color picker with text input
            $('.color-picker').each(function() {
                const $picker = $(this);
                const $text = $picker.siblings('.color-text');
                
                // Initialize with current value
                if ($text.val()) {
                    $picker.val($text.val());
                }
            });
            
            // Update preview when colors change
            $('.color-picker, .color-text').on('change input', this.updateColorPreview.bind(this));
        },

        /**
         * Sync color picker with text input
         */
        syncColorPicker: function(e) {
            const $picker = $(e.target);
            const $text = $picker.siblings('.color-text');
            const color = $picker.val();
            
            $text.val(color);
            this.updateColorPreview();
            this.markFormChanged();
        },

        /**
         * Sync text input with color picker
         */
        syncColorText: function(e) {
            const $text = $(e.target);
            const $picker = $text.siblings('.color-picker');
            const color = $text.val();
            
            // Validate hex color format
            if (/^#[0-9A-F]{6}$/i.test(color)) {
                $picker.val(color);
                $text.removeClass('form-invalid');
                this.updateColorPreview();
                this.markFormChanged();
            } else if (color.length === 7) {
                $text.addClass('form-invalid');
            }
        },

        /**
         * Update color preview
         */
        updateColorPreview: function() {
            const primaryColor = $('#primary_color').val() || '#667eea';
            const secondaryColor = $('#secondary_color').val() || '#f8f9fa';
            
            // Remove existing preview styles
            $('#color-preview-styles').remove();
            
            // Add new preview styles
            $('head').append(`
                <style id="color-preview-styles">
                    .template-option-card.selected { 
                        border-color: ${primaryColor} !important; 
                        box-shadow: 0 4px 20px ${primaryColor}33 !important;
                    }
                    .template-active-badge { 
                        background: ${primaryColor} !important; 
                    }
                    .settings-nav-link.active::before {
                        background: linear-gradient(135deg, ${primaryColor}, ${secondaryColor}) !important;
                    }
                </style>
            `);
        },

        /**
         * Update range slider value display
         */
        updateRangeValue: function(e) {
            const $slider = $(e.target);
            const value = $slider.val();
            const $valueDisplay = $slider.siblings('.range-value');
            
            $valueDisplay.text(value + 'px');
            this.updateBorderRadiusPreview(value);
            this.markFormChanged();
        },

        /**
         * Update border radius preview
         */
        updateBorderRadiusPreview: function(radius) {
            $('#radius-preview-styles').remove();
            
            $('head').append(`
                <style id="radius-preview-styles">
                    .template-preview-card { 
                        border-radius: ${radius}px !important; 
                    }
                    .template-option-card {
                        border-radius: ${radius}px !important;
                    }
                </style>
            `);
        },

        /**
         * Toggle CSS help visibility
         */
        toggleCSSHelp: function(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const $examples = $('.css-examples');
            
            $examples.slideToggle(300, function() {
                const isVisible = $examples.is(':visible');
                $button.text(isVisible ? 'Hide CSS Examples' : 'Show CSS Examples');
            });
        },

        /**
         * Switch CSS example tabs
         */
        switchCSSTab: function(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const tab = $button.data('tab');
            
            // Update active tab
            $('.css-tab-btn').removeClass('active');
            $button.addClass('active');
            
            // Show target content
            $('.css-tab-content').removeClass('active').hide();
            $('#css-tab-' + tab).addClass('active').show();
        },

        /**
         * Insert CSS example
         */
        insertCSS: function(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const cssType = $button.data('css');
            const $textarea = $('#custom_css');
            const cssContent = $('#css-tab-' + cssType + ' code').text();
            
            if (!cssContent) return;
            
            const currentCSS = $textarea.val();
            const newCSS = currentCSS + (currentCSS ? '\n\n' : '') + cssContent;
            
            $textarea.val(newCSS);
            $textarea.focus();
            
            // Scroll to bottom
            $textarea.scrollTop($textarea[0].scrollHeight);
            
            // Show feedback
            const originalText = $button.text();
            $button.text('Inserted!').addClass('button-primary');
            
            setTimeout(function() {
                $button.text(originalText).removeClass('button-primary');
            }, 1500);
            
            this.markFormChanged();
        },

        /**
         * Validate CSS
         */
        validateCSS: function(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const $result = $('.css-validation-result');
            const css = $('#custom_css').val();
            const originalText = $button.text();
            
            if (!css.trim()) {
                $result.html('<div class="notice notice-info inline"><p>No CSS to validate.</p></div>');
                return;
            }
            
            $button.text('Validating...').prop('disabled', true);
            
            // Simple CSS validation
            const validation = this.performCSSValidation(css);
            
            setTimeout(function() {
                if (validation.valid) {
                    $result.html('<div class="notice notice-success inline"><p><span class="dashicons dashicons-yes-alt"></span> CSS is valid!</p></div>');
                } else {
                    $result.html('<div class="notice notice-error inline"><p><span class="dashicons dashicons-warning"></span> ' + validation.error + '</p></div>');
                }
                
                $button.text(originalText).prop('disabled', false);
            }, 1000);
        },

        /**
         * Perform CSS validation
         */
        performCSSValidation: function(css) {
            // Basic CSS validation
            const openBraces = (css.match(/{/g) || []).length;
            const closeBraces = (css.match(/}/g) || []).length;
            
            if (openBraces !== closeBraces) {
                return {
                    valid: false,
                    error: 'Mismatched braces in CSS. Found ' + openBraces + ' opening and ' + closeBraces + ' closing braces.'
                };
            }
            
            // Check for double semicolons
            if (css.includes(';;')) {
                return {
                    valid: false,
                    error: 'Double semicolons found in CSS.'
                };
            }
            
            // Check for basic syntax errors
            const lines = css.split('\n');
            for (let i = 0; i < lines.length; i++) {
                const line = lines[i].trim();
                if (line && !line.startsWith('/*') && !line.endsWith('*/')) {
                    // Check for missing semicolons in property declarations
                    if (line.includes(':') && !line.includes('{') && !line.includes('}') && !line.endsWith(';') && !line.endsWith('{')) {
                        return {
                            valid: false,
                            error: 'Missing semicolon on line ' + (i + 1) + ': ' + line
                        };
                    }
                }
            }
            
            return { valid: true };
        },

        /**
         * Clear cache
         */
        clearCache: function(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const originalText = $button.text();
            
            if (!confirm('Are you sure you want to clear all caches?')) {
                return;
            }
            
            $button.text('Clearing...').prop('disabled', true);
            
            $.post(ajaxurl, {
                action: 'edd_dashboard_pro_clear_cache',
                nonce: eddDashboardProAdmin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    $button.text('Cache Cleared!');
                    EDD_Dashboard_Pro_Admin.showNotice('Cache cleared successfully!', 'success');
                    
                    // Update cache stats if visible
                    $('.cache-stat-item .stat-value').first().text('0 B');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $button.text(originalText).prop('disabled', false);
                    EDD_Dashboard_Pro_Admin.showNotice(response.data.message || 'Failed to clear cache.', 'error');
                }
            })
            .fail(function() {
                $button.text(originalText).prop('disabled', false);
                EDD_Dashboard_Pro_Admin.showNotice('Failed to clear cache. Please try again.', 'error');
            });
        },

        /**
         * Run manual cleanup
         */
        runManualCleanup: function(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const $status = $('.cleanup-status');
            const originalText = $button.text();
            
            $button.text('Running Cleanup...').prop('disabled', true);
            $status.empty();
            
            $.post(ajaxurl, {
                action: 'edd_dashboard_pro_manual_cleanup',
                nonce: eddDashboardProAdmin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    $status.html('<span class="success">Cleanup completed successfully.</span>');
                    EDD_Dashboard_Pro_Admin.showNotice('Database cleanup completed!', 'success');
                } else {
                    $status.html('<span class="error">' + (response.data.message || 'Cleanup failed.') + '</span>');
                }
            })
            .fail(function() {
                $status.html('<span class="error">Cleanup failed. Please try again.</span>');
            })
            .always(function() {
                $button.text(originalText).prop('disabled', false);
            });
        },

        /**
         * Copy system info
         */
        copySystemInfo: function(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const $textarea = $('#system-info-textarea');
            const originalText = $button.text();
            
            // Show textarea temporarily
            $textarea.show().select();
            
            try {
                const successful = document.execCommand('copy');
                if (successful) {
                    $button.text('Copied!');
                    this.showNotice('System information copied to clipboard!', 'success');
                } else {
                    throw new Error('Copy command failed');
                }
            } catch (err) {
                // Fallback for modern browsers
                if (navigator.clipboard) {
                    navigator.clipboard.writeText($textarea.val()).then(function() {
                        $button.text('Copied!');
                        EDD_Dashboard_Pro_Admin.showNotice('System information copied to clipboard!', 'success');
                    }).catch(function() {
                        EDD_Dashboard_Pro_Admin.showNotice('Failed to copy. Please copy manually.', 'error');
                    });
                } else {
                    this.showNotice('Please copy the text manually from the textarea.', 'info');
                    $textarea.focus();
                    return; // Don't hide textarea
                }
            }
            
            $textarea.hide();
            
            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        },

        /**
         * Export settings
         */
        exportSettings: function(e) {
            e.preventDefault();
            window.location.href = ajaxurl + '?action=edd_dashboard_pro_export_settings&nonce=' + eddDashboardProAdmin.nonce;
        },

        /**
         * Trigger import file dialog
         */
        triggerImport: function(e) {
            e.preventDefault();
            $('#import-file').click();
        },

        /**
         * Handle import file selection
         */
        handleImportFile: function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Validate file type
            if (file.type !== 'application/json' && !file.name.endsWith('.json')) {
                alert('Please select a valid JSON file.');
                $(e.target).val('');
                return;
            }
            
            // Validate file size (max 1MB)
            if (file.size > 1024 * 1024) {
                alert('File is too large. Please select a file smaller than 1MB.');
                $(e.target).val('');
                return;
            }
            
            if (!confirm('Are you sure you want to import these settings? This will overwrite your current configuration.')) {
                $(e.target).val('');
                return;
            }
            
            this.performImport(file);
        },

        /**
         * Perform settings import
         */
        performImport: function(file) {
            const $button = $('.import-settings-btn');
            const originalText = $button.text();
            
            $button.text('Importing...').prop('disabled', true);
            
            const formData = new FormData();
            formData.append('action', 'edd_dashboard_pro_import_settings');
            formData.append('nonce', eddDashboardProAdmin.nonce);
            formData.append('settings_file', file);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        EDD_Dashboard_Pro_Admin.showNotice('Settings imported successfully!', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        EDD_Dashboard_Pro_Admin.showNotice(response.data.message || 'Failed to import settings.', 'error');
                    }
                },
                error: function() {
                    EDD_Dashboard_Pro_Admin.showNotice('Import failed. Please try again.', 'error');
                },
                complete: function() {
                    $button.text(originalText).prop('disabled', false);
                    $('#import-file').val('');
                }
            });
        },

        /**
         * Reset section to defaults
         */
        resetSection: function(e) {
            e.preventDefault();
            
            const $button = $(e.target);
            const section = $button.data('section');
            
            if (!confirm('Are you sure you want to reset all settings in this section to defaults?')) {
                return;
            }
            
            const originalText = $button.text();
            $button.text('Resetting...').prop('disabled', true);
            
            $.post(ajaxurl, {
                action: 'edd_dashboard_pro_reset_section',
                section: section,
                nonce: eddDashboardProAdmin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    EDD_Dashboard_Pro_Admin.showNotice('Section reset successfully!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    EDD_Dashboard_Pro_Admin.showNotice(response.data.message || 'Failed to reset section.', 'error');
                }
            })
            .fail(function() {
                EDD_Dashboard_Pro_Admin.showNotice('Reset failed. Please try again.', 'error');
            })
            .always(function() {
                $button.text(originalText).prop('disabled', false);
            });
        },

        /**
         * Reset template settings
         */
        resetTemplateSettings: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to reset all template settings to defaults?')) {
                return;
            }
            
            // Reset form fields
            $('#primary_color, #primary_color_text').val('#667eea');
            $('#secondary_color, #secondary_color_text').val('#f8f9fa');
            $('#border_radius').val(8);
            $('.range-value').text('8px');
            $('#custom_css').val('');
            $('#card_style').val('modern');
            $('#animation_speed').val('normal');
            
            // Reset checkboxes
            $('#show_customer_avatar, #show_breadcrumbs, #responsive_tables').prop('checked', true);
            $('#enable_dark_mode').prop('checked', false);
            
            // Update previews
            this.updateColorPreview();
            this.updateBorderRadiusPreview(8);
            
            this.markFormChanged();
            this.showNotice('Template settings reset to defaults.', 'success');
        },

        /**
         * Toggle conditional fields
         */
        toggleCacheDuration: function(e) {
            $('.cache-duration-row').toggle(e.target.checked);
        },

        toggleDebugOptions: function(e) {
            $('.debug-options').toggle(e.target.checked);
            $('.debug-warning').toggle(e.target.checked);
        },

        toggleWelcomeOptions: function(e) {
            $('.welcome-message-options').toggle(e.target.checked);
        },

        /**
         * Validate dashboard page
         */
        validateDashboardPage: function(e) {
            const pageId = $(e.target).val();
            
            if (!pageId) return;
            
            // Remove existing notices
            $('.settings-notice').remove();
            
            // Add notice about shortcode
            const notice = $('<div class="settings-notice notice-info">' +
                           '<p><span class="dashicons dashicons-info"></span> ' +
                           'Make sure to add the [edd_customer_dashboard_pro] shortcode to the selected page.</p>' +
                           '</div>');
            
            $(e.target).closest('td').append(notice);
        },

        /**
         * Update welcome message preview
         */
        updateWelcomePreview: function(e) {
            const text = $(e.target).val();
            const preview = text.replace('{customer_name}', 'John Doe');
            $('.preview-content').text(preview);
        },

        /**
         * Setup notices
         */
        setupNotices: function() {
            // Auto-dismiss notices
            $('.notice.is-dismissible').each(function() {
                const $notice = $(this);
                setTimeout(function() {
                    $notice.fadeOut();
                }, 5000);
            });
            
            // Handle notice dismissal
            $(document).on('click', '.notice-dismiss', function() {
                $(this).closest('.notice').fadeOut();
            });
        },

        /**
         * Show notice
         */
        showNotice: function(message, type) {
            type = type || 'info';
            
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible fade-in">' +
                            '<p>' + message + '</p>' +
                            '<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button>' +
                            '</div>');
            
            // Insert after page header or at top
            const $target = $('.settings-page-header').length ? $('.settings-page-header') : $('.wrap').first();
            $target.after($notice);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $notice.fadeOut();
            }, 5000);
        },

        /**
         * Setup auto-save functionality
         */
        setupAutoSave: function() {
            let autoSaveTimer;
            let formChanged = false;
            
            // Track form changes
            $('input, select, textarea').on('change input', function() {
                formChanged = true;
                clearTimeout(autoSaveTimer);
                
                // Auto-save after 30 seconds of inactivity
                autoSaveTimer = setTimeout(function() {
                    if (formChanged) {
                        EDD_Dashboard_Pro_Admin.performAutoSave();
                    }
                }, 30000);
            });
            
            this.formChanged = false;
            this.formSubmitted = false;
        },

        /**
         * Perform auto-save
         */
        performAutoSave: function() {
            const $form = $('.settings-form');
            if (!$form.length) return;
            
            const formData = $form.serialize() + '&action=edd_dashboard_pro_autosave&nonce=' + eddDashboardProAdmin.nonce;
            
            $.post(ajaxurl, formData)
            .done(function(response) {
                if (response.success) {
                    EDD_Dashboard_Pro_Admin.showNotice('Settings auto-saved.', 'info');
                    EDD_Dashboard_Pro_Admin.formChanged = false;
                }
            });
        },

        /**
         * Mark form as changed
         */
        markFormChanged: function() {
            this.formChanged = true;
        },

        /**
         * Handle page unload
         */
        handlePageUnload: function(e) {
            if (this.formChanged && !this.formSubmitted) {
                const message = 'You have unsaved changes. Are you sure you want to leave?';
                e.returnValue = message;
                return message;
            }
        },

        /**
         * Setup keyboard shortcuts
         */
        setupKeyboardShortcuts: function() {
            $(document).on('keydown', function(e) {
                // Ctrl/Cmd + S to save
                if ((e.ctrlKey || e.metaKey) && e.which === 83) {
                    e.preventDefault();
                    $('.settings-form').submit();
                }
                
                // Ctrl/Cmd + Shift + P to preview
                if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.which === 80) {
                    e.preventDefault();
                    $('.preview-template').first().click();
                }
            });
        },

        /**
         * Initialize tooltips
         */
        initializeTooltips: function() {
            // Simple tooltip implementation
            $('[title]').each(function() {
                const $element = $(this);
                const title = $element.attr('title');
                
                $element.removeAttr('title').on('mouseenter', function(e) {
                    const $tooltip = $('<div class="admin-tooltip">' + title + '</div>');
                    $('body').append($tooltip);
                    
                    const offset = $element.offset();
                    $tooltip.css({
                        top: offset.top - $tooltip.outerHeight() - 5,
                        left: offset.left + ($element.outerWidth() / 2) - ($tooltip.outerWidth() / 2)
                    });
                }).on('mouseleave', function() {
                    $('.admin-tooltip').remove();
                });
            });
        },

        /**
         * Initialize drag and drop
         */
        initializeDragDrop: function() {
            const $importArea = $('.import-settings');
            
            if (!$importArea.length) return;
            
            // Add visual feedback for drag and drop
            $importArea.on('dragover dragenter', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).addClass('drag-over');
            });
            
            $importArea.on('dragleave dragend', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
            });
            
            $importArea.on('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $(this).removeClass('drag-over');
                
                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    $('#import-file')[0].files = files;
                    $('#import-file').trigger('change');
                }
            });
        },

        /**
         * Setup real-time validation
         */
        setupRealtimeValidation: function() {
            // Validate as user types with debouncing
            let validationTimer;
            
            $('input, textarea').on('input', function() {
                const $field = $(this);
                
                clearTimeout(validationTimer);
                validationTimer = setTimeout(function() {
                    EDD_Dashboard_Pro_Admin.validateField($field);
                }, 500);
            });
        },

        /**
         * Setup progress tracking
         */
        setupProgressTracking: function() {
            // Track setup progress
            this.updateSetupProgress();
            
            // Update progress when relevant fields change
            $('#dashboard_page, input[name="template"]').on('change', function() {
                setTimeout(function() {
                    EDD_Dashboard_Pro_Admin.updateSetupProgress();
                }, 100);
            });
        },

        /**
         * Update setup progress
         */
        updateSetupProgress: function() {
            const $steps = $('.setup-steps li');
            let completedSteps = 0;
            
            $steps.each(function() {
                if ($(this).hasClass('completed')) {
                    completedSteps++;
                }
            });
            
            const progress = Math.round((completedSteps / $steps.length) * 100);
            
            // Update progress bar if exists
            $('.progress-fill').css('width', progress + '%');
            
            // Show completion message
            if (progress === 100) {
                $('.setup-complete').show();
            }
        },

        /**
         * Handle tab navigation
         */
        handleTabNavigation: function() {
            // Handle hash changes
            $(window).on('hashchange', function() {
                const hash = window.location.hash.replace('#', '');
                if (hash) {
                    $('.settings-nav-link[href="#' + hash + '"]').click();
                }
            });
            
            // Smooth scrolling for anchor links
            $('.settings-nav-link').on('click', function(e) {
                const target = $(this).attr('href');
                if (target.startsWith('#') && $(target).length) {
                    e.preventDefault();
                    $('html, body').animate({
                        scrollTop: $(target).offset().top - 100
                    }, 500);
                }
            });
        },

        /**
         * Initialize collapsible sections
         */
        initializeCollapsibles: function() {
            $('.collapsible-header').on('click', function() {
                const $header = $(this);
                const $content = $header.next('.collapsible-content');
                
                $header.toggleClass('collapsed');
                $content.slideToggle(300);
            });
        },

        /**
         * Debug log functions
         */
        viewDebugLog: function(e) {
            e.preventDefault();
            window.open(ajaxurl + '?action=edd_dashboard_pro_view_debug_log&nonce=' + eddDashboardProAdmin.nonce, '_blank');
        },

        clearDebugLog: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to clear the debug log?')) {
                return;
            }
            
            const $button = $(e.target);
            const originalText = $button.text();
            
            $button.text('Clearing...').prop('disabled', true);
            
            $.post(ajaxurl, {
                action: 'edd_dashboard_pro_clear_debug_log',
                nonce: eddDashboardProAdmin.nonce
            })
            .done(function(response) {
                if (response.success) {
                    EDD_Dashboard_Pro_Admin.showNotice('Debug log cleared successfully.', 'success');
                } else {
                    EDD_Dashboard_Pro_Admin.showNotice(response.data.message || 'Failed to clear debug log.', 'error');
                }
            })
            .fail(function() {
                EDD_Dashboard_Pro_Admin.showNotice('Failed to clear debug log.', 'error');
            })
            .always(function() {
                $button.text(originalText).prop('disabled', false);
            });
        },

        downloadDebugLog: function(e) {
            e.preventDefault();
            window.location.href = ajaxurl + '?action=edd_dashboard_pro_download_debug_log&nonce=' + eddDashboardProAdmin.nonce;
        },

        /**
         * Utility functions
         */
        
        /**
         * Debounce function calls
         */
        debounce: function(func, wait, immediate) {
            let timeout;
            return function() {
                const context = this, args = arguments;
                const later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },

        /**
         * Throttle function calls
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
        },

        /**
         * Format file size
         */
        formatFileSize: function(bytes) {
            if (bytes === 0) return '0 Bytes';
            
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },

        /**
         * Generate random ID
         */
        generateId: function(length) {
            length = length || 8;
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            let result = '';
            for (let i = 0; i < length; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            return result;
        },

        /**
         * Check if element is in viewport
         */
        isInViewport: function(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },

        /**
         * Smooth scroll to element
         */
        scrollTo: function(target, offset) {
            offset = offset || 0;
            const $target = $(target);
            
            if ($target.length) {
                $('html, body').animate({
                    scrollTop: $target.offset().top - offset
                }, 500);
            }
        },

        /**
         * Get URL parameter
         */
        getUrlParameter: function(name) {
            name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
            const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
            const results = regex.exec(location.search);
            return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
        },

        /**
         * Set URL parameter
         */
        setUrlParameter: function(key, value) {
            if (history.pushState) {
                const url = new URL(window.location);
                url.searchParams.set(key, value);
                window.history.pushState({path: url.href}, '', url.href);
            }
        },

        /**
         * Local storage wrapper with fallback
         */
        storage: {
            set: function(key, value) {
                try {
                    localStorage.setItem('edd_dashboard_pro_' + key, JSON.stringify(value));
                } catch (e) {
                    // Fallback to cookies or memory storage
                    this.fallbackStorage = this.fallbackStorage || {};
                    this.fallbackStorage[key] = value;
                }
            },
            
            get: function(key) {
                try {
                    const value = localStorage.getItem('edd_dashboard_pro_' + key);
                    return value ? JSON.parse(value) : null;
                } catch (e) {
                    return this.fallbackStorage ? this.fallbackStorage[key] : null;
                }
            },
            
            remove: function(key) {
                try {
                    localStorage.removeItem('edd_dashboard_pro_' + key);
                } catch (e) {
                    if (this.fallbackStorage) {
                        delete this.fallbackStorage[key];
                    }
                }
            }
        }
    };

    /**
     * Template Preview Modal
     */
    const TemplatePreviewModal = {
        init: function() {
            this.createModal();
            this.bindEvents();
        },

        createModal: function() {
            if ($('#template-preview-modal').length) return;
            
            const modal = `
                <div id="template-preview-modal" class="template-modal" style="display: none;">
                    <div class="template-modal-backdrop"></div>
                    <div class="template-modal-content">
                        <div class="template-modal-header">
                            <h3>Template Preview</h3>
                            <button class="template-modal-close">&times;</button>
                        </div>
                        <div class="template-modal-body">
                            <iframe id="template-preview-frame" src="" width="100%" height="500"></iframe>
                        </div>
                        <div class="template-modal-footer">
                            <button class="button button-secondary template-modal-close">Close</button>
                            <button class="button button-primary select-template">Select This Template</button>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modal);
        },

        bindEvents: function() {
            $(document).on('click', '.template-modal-close, .template-modal-backdrop', this.close);
            $(document).on('click', '.select-template', this.selectTemplate);
            $(document).on('keydown', this.handleKeydown);
        },

        open: function(templateKey, previewUrl) {
            this.currentTemplate = templateKey;
            $('#template-preview-frame').attr('src', previewUrl);
            $('#template-preview-modal').fadeIn(300);
            $('body').addClass('modal-open');
        },

        close: function() {
            $('#template-preview-modal').fadeOut(300);
            $('body').removeClass('modal-open');
            $('#template-preview-frame').attr('src', '');
        },

        selectTemplate: function() {
            if (TemplatePreviewModal.currentTemplate) {
                $('input[name="template"][value="' + TemplatePreviewModal.currentTemplate + '"]').prop('checked', true).trigger('change');
                TemplatePreviewModal.close();
                EDD_Dashboard_Pro_Admin.showNotice('Template selected!', 'success');
            }
        },

        handleKeydown: function(e) {
            if (e.keyCode === 27) { // Escape key
                TemplatePreviewModal.close();
            }
        }
    };

    /**
     * Settings Wizard
     */
    const SettingsWizard = {
        currentStep: 0,
        totalSteps: 0,

        init: function() {
            this.$wizard = $('.settings-wizard');
            if (!this.$wizard.length) return;

            this.totalSteps = this.$wizard.find('.wizard-step').length;
            this.bindEvents();
            this.updateProgress();
        },

        bindEvents: function() {
            $(document).on('click', '.wizard-next', this.nextStep.bind(this));
            $(document).on('click', '.wizard-prev', this.prevStep.bind(this));
            $(document).on('click', '.wizard-finish', this.finishWizard.bind(this));
        },

        nextStep: function() {
            if (this.currentStep < this.totalSteps - 1) {
                this.currentStep++;
                this.showStep(this.currentStep);
                this.updateProgress();
            }
        },

        prevStep: function() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.showStep(this.currentStep);
                this.updateProgress();
            }
        },

        showStep: function(step) {
            this.$wizard.find('.wizard-step').removeClass('active').eq(step).addClass('active');
        },

        updateProgress: function() {
            const progress = ((this.currentStep + 1) / this.totalSteps) * 100;
            $('.wizard-progress-fill').css('width', progress + '%');
            $('.wizard-step-counter').text((this.currentStep + 1) + ' of ' + this.totalSteps);
        },

        finishWizard: function() {
            // Submit form or perform final actions
            $('.settings-form').submit();
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        EDD_Dashboard_Pro_Admin.init();
        TemplatePreviewModal.init();
        SettingsWizard.init();
    });

    /**
     * Handle window resize
     */
    $(window).on('resize', EDD_Dashboard_Pro_Admin.debounce(function() {
        // Handle responsive adjustments
        EDD_Dashboard_Pro_Admin.handleResize();
    }, 250));

    /**
     * Handle window scroll
     */
    $(window).on('scroll', EDD_Dashboard_Pro_Admin.throttle(function() {
        // Handle scroll-related functionality
        EDD_Dashboard_Pro_Admin.handleScroll();
    }, 100));

    /**
     * Additional resize handler
     */
    EDD_Dashboard_Pro_Admin.handleResize = function() {
        // Adjust modal sizes
        const $modal = $('#template-preview-modal');
        if ($modal.is(':visible')) {
            const modalContent = $modal.find('.template-modal-content');
            const maxHeight = $(window).height() - 100;
            modalContent.css('max-height', maxHeight + 'px');
        }
    };

    /**
     * Additional scroll handler
     */
    EDD_Dashboard_Pro_Admin.handleScroll = function() {
        // Sticky navigation
        const $nav = $('.settings-nav-wrapper');
        const scrollTop = $(window).scrollTop();
        
        if (scrollTop > 100) {
            $nav.addClass('sticky');
        } else {
            $nav.removeClass('sticky');
        }
    };

    /**
     * Export to global scope for external access
     */
    window.EDD_Dashboard_Pro_Admin = EDD_Dashboard_Pro_Admin;
    window.TemplatePreviewModal = TemplatePreviewModal;
    window.SettingsWizard = SettingsWizard;

})(jQuery);

/**
 * Additional styles for modal and other dynamic elements
 */
const additionalCSS = `
    <style>
    .template-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
    }
    
    .template-modal-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }
    
    .template-modal-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        max-width: 90%;
        max-height: 90%;
        width: 1000px;
        overflow: hidden;
    }
    
    .template-modal-header {
        padding: 20px;
        border-bottom: 1px solid #e1e1e1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .template-modal-header h3 {
        margin: 0;
    }
    
    .template-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
    }
    
    .template-modal-body {
        padding: 0;
    }
    
    .template-modal-footer {
        padding: 20px;
        border-top: 1px solid #e1e1e1;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .admin-tooltip {
        position: absolute;
        background: #333;
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        z-index: 9999;
        white-space: nowrap;
    }
    
    .admin-tooltip::before {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #333 transparent transparent transparent;
    }
    
    .drag-over {
        border: 2px dashed #667eea !important;
        background: rgba(102, 126, 234, 0.1) !important;
    }
    
    .sticky {
        position: fixed;
        top: 32px;
        left: 0;
        right: 0;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    body.modal-open {
        overflow: hidden;
    }
    
    .fade-in {
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .wizard-progress {
        height: 4px;
        background: #e1e1e1;
        border-radius: 2px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    
    .wizard-progress-fill {
        height: 100%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        transition: width 0.3s ease;
    }
    
    .wizard-step {
        display: none;
    }
    
    .wizard-step.active {
        display: block;
        animation: slideIn 0.3s ease;
    }
    
    @keyframes slideIn {
        from { opacity: 0; transform: translateX(20px); }
        to { opacity: 1; transform: translateX(0); }
    }
    </style>
`;

// Inject additional CSS
document.head.insertAdjacentHTML('beforeend', additionalCSS);/**
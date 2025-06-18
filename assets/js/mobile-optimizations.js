/**
 * Mobile Optimizations for EDD Customer Dashboard Pro
 * 
 * Handles mobile-specific functionality, touch interactions,
 * and responsive behavior enhancements.
 *
 * @package EDD_Customer_Dashboard_Pro
 * @version 1.0.0
 */

(function($) {
    'use strict';

    // Mobile Optimizations Class
    class EDDMobileOptimizations {
        constructor() {
            this.init();
            this.bindEvents();
            this.setupTouchGestures();
        }

        init() {
            this.cache = {
                $window: $(window),
                $document: $(document),
                $body: $('body'),
                $dashboard: $('.edd-customer-dashboard-pro'),
                $navTabs: $('.edd-nav-tabs'),
                $tables: $('.edd-table-responsive'),
                $modals: $('.edd-modal')
            };

            this.settings = {
                breakpoints: {
                    mobile: 768,
                    tablet: 1024
                },
                touchDelay: 300,
                swipeThreshold: 50,
                scrollThreshold: 10,
                debounceDelay: 250
            };

            this.state = {
                isMobile: false,
                isTablet: false,
                isTouch: false,
                orientation: 'portrait',
                viewport: {
                    width: 0,
                    height: 0
                },
                lastTouch: null,
                activeSwipe: null
            };

            this.detectDeviceCapabilities();
            this.initializeViewport();
        }

        detectDeviceCapabilities() {
            // Touch detection
            this.state.isTouch = 'ontouchstart' in window || 
                               navigator.maxTouchPoints > 0 || 
                               navigator.msMaxTouchPoints > 0;

            // Device orientation
            this.state.orientation = window.innerHeight > window.innerWidth ? 'portrait' : 'landscape';

            // Screen size detection
            this.updateScreenSizeState();

            // Add device classes
            this.cache.$body.toggleClass('edd-touch-device', this.state.isTouch);
            this.cache.$body.toggleClass('edd-mobile', this.state.isMobile);
            this.cache.$body.toggleClass('edd-tablet', this.state.isTablet);
        }

        updateScreenSizeState() {
            const width = window.innerWidth;
            
            this.state.isMobile = width < this.settings.breakpoints.mobile;
            this.state.isTablet = width >= this.settings.breakpoints.mobile && width < this.settings.breakpoints.tablet;
            
            this.state.viewport.width = width;
            this.state.viewport.height = window.innerHeight;
        }

        initializeViewport() {
            // Set viewport meta tag for mobile
            if (this.state.isMobile && !$('meta[name="viewport"]').length) {
                $('head').append('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">');
            }

            // Handle iOS viewport units bug
            if (this.isIOS()) {
                this.fixIOSViewportUnits();
            }
        }

        bindEvents() {
            // Resize and orientation events
            this.cache.$window.on('resize', this.debounce(this.handleResize.bind(this), this.settings.debounceDelay));
            this.cache.$window.on('orientationchange', this.handleOrientationChange.bind(this));

            // Touch events
            if (this.state.isTouch) {
                this.bindTouchEvents();
            }

            // Mobile navigation events
            this.cache.$document.on('click', '.edd-mobile-menu-toggle', this.handleMobileMenuToggle.bind(this));
            this.cache.$document.on('click', '.edd-mobile-nav-close', this.handleMobileNavClose.bind(this));

            // Scroll events for mobile optimizations
            this.cache.$window.on('scroll', this.debounce(this.handleScroll.bind(this), 100));

            // Focus events for virtual keyboard
            this.cache.$document.on('focus', 'input, textarea, select', this.handleInputFocus.bind(this));
            this.cache.$document.on('blur', 'input, textarea, select', this.handleInputBlur.bind(this));

            // Pull-to-refresh
            if (this.state.isMobile) {
                this.setupPullToRefresh();
            }
        }

        bindTouchEvents() {
            // Touch event handlers
            this.cache.$document.on('touchstart', this.handleTouchStart.bind(this));
            this.cache.$document.on('touchmove', this.handleTouchMove.bind(this));
            this.cache.$document.on('touchend', this.handleTouchEnd.bind(this));
            this.cache.$document.on('touchcancel', this.handleTouchCancel.bind(this));

            // Prevent double-tap zoom on buttons
            this.cache.$document.on('touchend', '.edd-btn, .edd-nav-tab', this.preventDoubleZoom.bind(this));
        }

        setupTouchGestures() {
            // Swipe navigation for tabs
            this.setupTabSwipeNavigation();
            
            // Swipe actions for table rows
            this.setupTableRowSwipe();
            
            // Pinch-to-zoom prevention
            this.preventPinchZoom();
        }

        // Touch Event Handlers
        handleTouchStart(e) {
            const touch = e.originalEvent.touches[0];
            
            this.state.lastTouch = {
                x: touch.clientX,
                y: touch.clientY,
                time: Date.now(),
                target: e.target
            };

            // Add touch feedback
            this.addTouchFeedback($(e.target));
        }

        handleTouchMove(e) {
            if (!this.state.lastTouch) return;

            const touch = e.originalEvent.touches[0];
            const deltaX = touch.clientX - this.state.lastTouch.x;
            const deltaY = touch.clientY - this.state.lastTouch.y;

            // Detect swipe direction
            if (Math.abs(deltaX) > this.settings.swipeThreshold || Math.abs(deltaY) > this.settings.swipeThreshold) {
                const direction = this.getSwipeDirection(deltaX, deltaY);
                this.handleSwipe(direction, this.state.lastTouch.target);
            }

            // Remove touch feedback during movement
            this.removeTouchFeedback();
        }

        handleTouchEnd(e) {
            setTimeout(() => {
                this.removeTouchFeedback();
            }, 100);

            this.state.lastTouch = null;
        }

        handleTouchCancel(e) {
            this.removeTouchFeedback();
            this.state.lastTouch = null;
        }

        getSwipeDirection(deltaX, deltaY) {
            if (Math.abs(deltaX) > Math.abs(deltaY)) {
                return deltaX > 0 ? 'right' : 'left';
            } else {
                return deltaY > 0 ? 'down' : 'up';
            }
        }

        handleSwipe(direction, target) {
            const $target = $(target);
            
            // Handle tab navigation swipes
            if ($target.closest('.edd-nav-tabs').length) {
                this.handleTabSwipe(direction);
                return;
            }

            // Handle table row swipes
            if ($target.closest('.edd-table tbody tr').length) {
                this.handleTableRowSwipe(direction, $target.closest('tr'));
                return;
            }

            // Handle modal swipes
            if ($target.closest('.edd-modal').length) {
                this.handleModalSwipe(direction, $target.closest('.edd-modal'));
                return;
            }
        }

        addTouchFeedback($element) {
            if ($element.hasClass('edd-btn') || $element.hasClass('edd-nav-tab') || $element.hasClass('edd-table-row')) {
                $element.addClass('edd-touch-active');
            }
        }

        removeTouchFeedback() {
            $('.edd-touch-active').removeClass('edd-touch-active');
        }

        preventDoubleZoom(e) {
            const $target = $(e.target);
            if ($target.hasClass('edd-btn') || $target.hasClass('edd-nav-tab')) {
                e.preventDefault();
                $target.trigger('click');
            }
        }

        // Complete implementation continues...
    }

    // Initialize when document is ready
    $(document).ready(() => {
        if ($('.edd-customer-dashboard-pro').length) {
            window.eddMobileOptimizations = new EDDMobileOptimizations();
        }
    });

    // Expose class globally
    window.EDDMobileOptimizations = EDDMobileOptimizations;

})(jQuery);
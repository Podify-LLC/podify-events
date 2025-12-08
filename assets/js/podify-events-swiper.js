/**
 * Podify Events - Swiper Initialization (FIXED)
 * Handles carousel initialization for both frontend and Elementor editor
 */

(function($) {
    'use strict';

    /**
     * Initialize Swiper for a widget
     */
    function initSwiper($widget) {
        if (!$widget || !$widget.length) return;

        const $wrapper = $widget.find('.podify-events-wrapper');
        if (!$wrapper.length) return;

        const enabled = $wrapper.attr('data-swiper-enabled') === 'yes';
        if (!enabled) return;

        const $swiper = $wrapper.find('.podify-events-swiper');
        if (!$swiper.length) return;

        // Prevent double initialization
        if ($swiper.hasClass('swiper-initialized')) {
            return;
        }

        // Get settings from data attributes
        const slidesDesktop = parseInt($wrapper.attr('data-slides-desktop')) || 1;
        const slidesTablet = parseInt($wrapper.attr('data-slides-tablet')) || 1;
        const slidesMobile = parseInt($wrapper.attr('data-slides-mobile')) || 1;
        const spaceBetween = parseInt($wrapper.attr('data-space-between')) || 30;
        const autoplayEnabled = $wrapper.attr('data-autoplay') === 'yes';
        const autoplayDelay = parseInt($wrapper.attr('data-autoplay-delay')) || 5000;
        const loopEnabled = $wrapper.attr('data-loop') === 'yes';
        const showArrows = $wrapper.attr('data-show-arrows') === 'yes';
        const showPagination = $wrapper.attr('data-show-pagination') === 'yes';

        // Swiper configuration
        const config = {
            slidesPerView: slidesMobile,
            spaceBetween: spaceBetween,
            loop: loopEnabled,
            speed: 600,
            autoHeight: false,
            watchOverflow: true,
            breakpoints: {
                640: {
                    slidesPerView: slidesMobile,
                    spaceBetween: spaceBetween
                },
                768: {
                    slidesPerView: slidesTablet,
                    spaceBetween: spaceBetween
                },
                1024: {
                    slidesPerView: slidesDesktop,
                    spaceBetween: spaceBetween
                }
            }
        };

        // Add autoplay if enabled
        if (autoplayEnabled) {
            config.autoplay = {
                delay: autoplayDelay,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            };
        }

        // Add pagination if enabled
        if (showPagination) {
            const $pagination = $wrapper.find('.swiper-pagination');
            if ($pagination.length) {
                config.pagination = {
                    el: $pagination[0],
                    clickable: true,
                    dynamicBullets: false
                };
            }
        }

        // Add navigation if enabled
        if (showArrows) {
            const $prevBtn = $wrapper.find('.podify-nav-prev');
            const $nextBtn = $wrapper.find('.podify-nav-next');
            
            if ($prevBtn.length && $nextBtn.length) {
                config.navigation = {
                    prevEl: $prevBtn[0],
                    nextEl: $nextBtn[0],
                    disabledClass: 'swiper-button-disabled'
                };
            }
        }

        // Initialize Swiper
        try {
            if (typeof Swiper !== 'undefined') {
                const swiperInstance = new Swiper($swiper[0], config);
                
                // Store instance for potential cleanup
                $swiper.data('swiper-instance', swiperInstance);
                
                // Mark as initialized
                $swiper.addClass('swiper-initialized');
                
                console.log('Podify Events: Swiper initialized', swiperInstance);
                
                // Refresh on window resize
                $(window).on('resize', function() {
                    if (swiperInstance && swiperInstance.update) {
                        swiperInstance.update();
                    }
                });
            } else {
                console.error('Podify Events: Swiper library not loaded');
            }
        } catch (error) {
            console.error('Podify Events: Swiper initialization error', error);
        }
    }

    /**
     * Destroy Swiper instance
     */
    function destroySwiper($widget) {
        if (!$widget || !$widget.length) return;

        const $swiper = $widget.find('.podify-events-swiper');
        const instance = $swiper.data('swiper-instance');
        
        if (instance && instance.destroy) {
            instance.destroy(true, true);
            $swiper.removeClass('swiper-initialized');
            $swiper.removeData('swiper-instance');
            console.log('Podify Events: Swiper destroyed');
        }
    }

    /**
     * Initialize all carousels on page
     */
    function initAllSwipers() {
        $('.podify-events-carousel-enabled').each(function() {
            initSwiper($(this).closest('.elementor-widget-podify-events, .podify-events-wrapper').parent());
        });
    }

    /**
     * Frontend initialization
     */
    $(window).on('load', function() {
        initAllSwipers();
    });

    /**
     * Elementor Frontend handlers
     */
    if (typeof elementorFrontend !== 'undefined') {
        // Widget initialization
        $(window).on('elementor/frontend/init', function() {
            elementorFrontend.hooks.addAction(
                'frontend/element_ready/podify-events.default',
                function($scope) {
                    // Small delay to ensure DOM is ready
                    setTimeout(function() {
                        initSwiper($scope);
                    }, 100);
                }
            );
        });
    }

    /**
     * Elementor Editor handlers
     */
    if (typeof elementor !== 'undefined') {
        // Reinitialize on settings change
        elementor.channels.editor.on('change', function(view) {
            if (!view || !view.model) return;
            
            const widgetType = view.model.get('widgetType');
            if (widgetType !== 'podify-events') return;

            const $widget = view.$el;
            
            // Destroy existing instance
            destroySwiper($widget);
            
            // Reinitialize after brief delay
            setTimeout(function() {
                initSwiper($widget);
            }, 200);
        });

        // Initialize on panel open
        elementor.hooks.addAction('panel/open_editor/widget/podify-events', function(panel, model, view) {
            setTimeout(function() {
                initSwiper(view.$el);
            }, 300);
        });
    }

    /**
     * Cleanup on page unload
     */
    $(window).on('beforeunload', function() {
        $('.podify-events-carousel-enabled').each(function() {
            destroySwiper($(this).closest('.elementor-widget-podify-events, .podify-events-wrapper').parent());
        });
    });

})(jQuery);
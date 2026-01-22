(function (window, document) {
    'use strict';
    var inEditor = !!(document.body && (document.body.classList.contains('elementor-editor-active') || document.body.classList.contains('elementor-edit-mode')));

    function applyGrid(container) {
        // We rely on CSS variables for grid layout now.
        // Just clear any inline styles that might have been added by Swiper or previous JS.
        container.removeAttribute('style');
    }

    function destroySwiper(container) {
        var outer = container.querySelector('.swiper');
        if (!outer || outer.parentNode !== container) return;
        var wrapper = outer.querySelector('.swiper-wrapper');
        if (wrapper) {
            var slides = wrapper.querySelectorAll('.swiper-slide');
            var frag = document.createDocumentFragment();
            slides.forEach(function (slide) {
                while (slide.firstChild) { frag.appendChild(slide.firstChild); }
            });
            container.innerHTML = '';
            container.appendChild(frag);
        }

        var section = container.closest('.podify-events-wrapper');
        if (section) {
            var navRow = section.querySelector('.podify-nav-row');
            var pagination = section.querySelector('.swiper-pagination');
            if (navRow) navRow.remove();
            if (pagination) pagination.remove();
        }

        container._podifyInitialized = false;
        applyGrid(container);
    }

    window.PodifyEventsInit = function (cfg) {
        try {
            if (!cfg || !cfg.container) return;
            var container = document.querySelector(cfg.container);
            if (!container) return;
            var swiperAvailable = typeof Swiper !== 'undefined';
            function initSwiper() {
                if (typeof Swiper === 'undefined') return;
                var existing = container.querySelector('.swiper');
                if (existing) {
                    if (container._podifySwiper && typeof container._podifySwiper.destroy === 'function') {
                        try { container._podifySwiper.destroy(true, true); } catch (e) { }
                    }
                    destroySwiper(container);
                }
                if (!container.querySelector('.swiper')) {
                    container.removeAttribute('style');
                    var wrapper = document.createElement('div');
                    wrapper.className = 'swiper-wrapper';
                    while (container.firstChild) {
                        var child = container.firstChild;
                        var slide = document.createElement('div');
                        slide.className = 'swiper-slide';
                        slide.appendChild(child);
                        wrapper.appendChild(slide);
                    }
                    var outer = document.createElement('div');
                    outer.className = 'podify-events-swiper swiper';
                    outer.appendChild(wrapper);
                    if (cfg.dots) {
                        var pag = document.createElement('div');
                        pag.className = 'swiper-pagination';
                        outer.appendChild(pag);
                    }
                    if (cfg.arrows) {
                        var mode = cfg.arrowMode || 'both';
                        if (mode === 'prev' || mode === 'both') {
                            var prev = document.createElement('button');
                            prev.type = 'button';
                            prev.className = 'podify-nav podify-nav-prev';
                            prev.innerHTML = (cfg.prev && String(cfg.prev).trim()) ? cfg.prev : '';
                            outer.appendChild(prev);
                        }
                        if (mode === 'next' || mode === 'both') {
                            var next = document.createElement('button');
                            next.type = 'button';
                            next.className = 'podify-nav podify-nav-next';
                            next.innerHTML = (cfg.next && String(cfg.next).trim()) ? cfg.next : '';
                            outer.appendChild(next);
                        }
                    }
                    container.innerHTML = '';
                    container.appendChild(outer);
                    var ov = cfg.overflow || 'none';
                    if (ov === 'visible' || ov === 'visible_right' || ov === 'visible_left') {
                        outer.style.overflow = 'visible';
                    } else if (ov === 'hidden') {
                        outer.style.overflow = 'hidden';
                    }
                    var sec = container.closest('.podify-events-wrapper');
                    if (sec) {
                        sec.classList.remove('podify-overflow-right', 'podify-overflow-left');
                        if (ov === 'visible_right') sec.classList.add('podify-overflow-right');
                        if (ov === 'visible_left') sec.classList.add('podify-overflow-left');
                    }
                    var instance = new Swiper(outer, {
                        slidesPerView: (cfg.slidesPerView && parseInt(cfg.slidesPerView, 10)) ? parseInt(cfg.slidesPerView, 10) : 1,
                        spaceBetween: cfg.spaceBetween || 20,
                        loop: !!cfg.loop,
                        centeredSlides: !!cfg.center,
                        speed: cfg.speed || 600,
                        effect: 'slide',
                        grabCursor: true,
                        resistanceRatio: 0.85,
                        touchRatio: 1,
                        threshold: 5,
                        slidesPerGroup: 1,
                        roundLengths: true,
                        allowTouchMove: cfg.touch !== false,
                        autoplay: cfg.autoplay ? {
                            delay: cfg.autoplaySpeed || 5000,
                            disableOnInteraction: !!cfg.pauseInteraction,
                            pauseOnMouseEnter: !!cfg.pauseHover,
                            reverseDirection: !!cfg.reverse
                        } : false,
                        pagination: cfg.dots ? { el: outer.querySelector('.swiper-pagination'), clickable: true } : false,
                        navigation: (function () {
                            if (!cfg.arrows) return false;
                            var n = {};
                            var nextEl = outer.querySelector('.podify-nav-next') || outer.querySelector('.swiper-button-next');
                            var prevEl = outer.querySelector('.podify-nav-prev') || outer.querySelector('.swiper-button-prev');
                            if (nextEl) n.nextEl = nextEl;
                            if (prevEl) n.prevEl = prevEl;
                            return (n.nextEl || n.prevEl) ? n : false;
                        })(),
                        observer: true,
                        observeParents: true,
                        observeSlideChildren: true,
                        updateOnWindowResize: true
                    });
                    container._podifyInitialized = true;
                    container._podifySwiper = instance;
                }
            }
            if (swiperAvailable) { initSwiper(); }
        } catch (e) {
            if (window.console && console.warn) console.warn('PodifyEventsInit error', e);
        }
    };

    function initSwiperForWrapper(section) {
        var wrapper = section;
        var container = wrapper.querySelector('.podify-events-swiper');
        if (!container) return;
        if (container.swiper || wrapper._podifySwiper) return;
        var slidesDesktop = parseInt(wrapper.getAttribute('data-slides-desktop') || '1', 10);
        var slidesTablet = parseInt(wrapper.getAttribute('data-slides-tablet') || slidesDesktop, 10);
        var slidesMobile = parseInt(wrapper.getAttribute('data-slides-mobile') || '1', 10);
        var spaceBetween = parseInt(wrapper.getAttribute('data-space-between') || wrapper.getAttribute('data-col-gap') || '0', 10);
        var autoplayOn = ((wrapper.getAttribute('data-autoplay') || 'no') === 'yes');
        var autoplayDelay = parseInt(wrapper.getAttribute('data-autoplay-delay') || '5000', 10);
        var loopOn = ((wrapper.getAttribute('data-loop') || 'no') === 'yes');
        var showArrows = ((wrapper.getAttribute('data-show-arrows') || 'no') === 'yes');
        var showPagination = ((wrapper.getAttribute('data-show-pagination') || 'no') === 'yes');
        var arrowMode = (wrapper.getAttribute('data-arrow-mode') || 'both');

        var opts = {
            loop: !!loopOn,
            spaceBetween: Math.max(0, spaceBetween),
            slidesPerView: Math.max(1, slidesDesktop),
            watchOverflow: false,
            observer: true,
            observeParents: true,
            observeSlideChildren: true,
            speed: 600,
            effect: 'slide',
            grabCursor: true,
            resistanceRatio: 0.85,
            touchRatio: 1,
            threshold: 5,
            slidesPerGroup: 1,
            roundLengths: true,
            updateOnWindowResize: true,
            breakpoints: {
                0: { slidesPerView: Math.max(1, slidesMobile) },
                768: { slidesPerView: Math.max(1, slidesTablet) },
                1025: { slidesPerView: Math.max(1, slidesDesktop) }
            }
        };
        try {
            var totalSlides = container.querySelectorAll('.swiper-slide').length;
            if (totalSlides && slidesDesktop < totalSlides) { opts.loop = true; }
        } catch (_) { }
        if (autoplayOn) { opts.autoplay = { delay: Math.max(100, autoplayDelay), disableOnInteraction: false }; }
        if (showPagination) { opts.pagination = { el: section.querySelector('.swiper-pagination'), clickable: true }; }
        if (showArrows) {
            if (arrowMode === 'next') { var prev = section.querySelector('.podify-nav-prev'); if (prev) prev.remove(); }
            if (arrowMode === 'prev') { var next = section.querySelector('.podify-nav-next'); if (next) next.remove(); }
            var nextEl = section.querySelector('.podify-nav-next') || section.querySelector('.swiper-button-next');
            var prevEl = section.querySelector('.podify-nav-prev') || section.querySelector('.swiper-button-prev');
            if (nextEl || prevEl) {
                opts.navigation = {};
                if (nextEl) opts.navigation.nextEl = nextEl;
                if (prevEl) opts.navigation.prevEl = prevEl;
            }
        }
        try { wrapper._podifySwiper = new Swiper(container, opts); } catch (_) { }
    }

    function applyNavigationStyles(section) {
        var navRow = section.querySelector('.podify-nav-row');
        var pagination = section.querySelector('.swiper-pagination');
        var layoutGrid = section.classList.contains('podify-events-layout-grid');
        var layoutList = section.classList.contains('podify-events-layout-list');
        var isCarousel = section.classList.contains('podify-events-carousel-enabled');

        // Always reset styles first
        if (navRow) {
            navRow.style.cssText = '';
        }
        if (pagination) {
            pagination.style.cssText = '';
        }

        // Apply styles based on layout and carousel state
        if (isCarousel) {
            // Carousel enabled: show navigation with absolute positioning
            if (navRow) {
                navRow.style.position = 'absolute';
                navRow.style.top = '50%';
                navRow.style.left = '0';
                navRow.style.right = '0';
                navRow.style.transform = 'translateY(-50%)';
                navRow.style.zIndex = '10';
                navRow.style.display = 'flex';
                navRow.style.justifyContent = 'space-between';
                navRow.style.alignItems = 'center';
                navRow.style.width = '100%';
                navRow.style.padding = '0 12px';
                navRow.style.pointerEvents = 'none';
                navRow.style.margin = '0';
                navRow.style.height = 'auto';
                navRow.style.opacity = '1';
                navRow.style.visibility = 'visible';
            }
            if (pagination) {
                pagination.style.position = 'absolute';
                pagination.style.bottom = '10px';
                pagination.style.left = '0';
                pagination.style.right = '0';
                pagination.style.textAlign = 'center';
                pagination.style.zIndex = '5';
                pagination.style.display = 'block';
                pagination.style.opacity = '1';
                pagination.style.visibility = 'visible';
            }
        } else if (layoutList) {
            // List layout without carousel: show navigation at bottom
            if (navRow) {
                navRow.style.position = 'relative';
                navRow.style.top = 'auto';
                navRow.style.bottom = 'auto';
                navRow.style.transform = 'none';
                navRow.style.marginTop = '20px';
                navRow.style.display = 'flex';
                navRow.style.justifyContent = 'center';
                navRow.style.alignItems = 'center';
                navRow.style.gap = '10px';
                navRow.style.width = '100%';
                navRow.style.pointerEvents = 'auto';
                navRow.style.opacity = '1';
                navRow.style.visibility = 'visible';
                navRow.style.zIndex = 'auto';
                navRow.style.padding = '0';
            }
            if (pagination) {
                pagination.style.position = 'relative';
                pagination.style.bottom = 'auto';
                pagination.style.marginTop = '20px';
                pagination.style.textAlign = 'center';
                pagination.style.display = 'block';
                pagination.style.opacity = '1';
                pagination.style.visibility = 'visible';
                pagination.style.zIndex = 'auto';
            }
        } else if (layoutGrid) {
            // Grid layout without carousel: hide navigation
            if (navRow) {
                navRow.style.display = 'none';
                navRow.style.visibility = 'hidden';
                navRow.style.opacity = '0';
                navRow.style.pointerEvents = 'none';
                navRow.style.height = '0';
                navRow.style.width = '0';
                navRow.style.overflow = 'hidden';
            }
            if (pagination) {
                pagination.style.display = 'none';
                pagination.style.visibility = 'hidden';
                pagination.style.opacity = '0';
                pagination.style.pointerEvents = 'none';
                pagination.style.height = '0';
                pagination.style.width = '0';
                pagination.style.overflow = 'hidden';
            }
        }

        // Force styles in Elementor editor
        if (inEditor) {
            if (navRow) {
                navRow.style.cssText += '!important';
                navRow.style.opacity = '1 !important';
                navRow.style.visibility = 'visible !important';
            }
            if (pagination) {
                pagination.style.cssText += '!important';
                pagination.style.opacity = '1 !important';
                pagination.style.visibility = 'visible !important';
            }
        }
    }

    function initOrGrid(section) {
        if (section.classList.contains('podify-events-wrapper--swiper')) {
            initSwiperForWrapper(section);
            return;
        }
        var grid = section.querySelector('.podify-events-grid, .podify-events-flex-grid');
        if (!grid) return;

        var widgetRoot = section.closest('.elementor-widget-podify-events') || section.closest('.elementor-widget');
        var rootClass = widgetRoot ? widgetRoot.className : '';
        var styleGrid = rootClass.indexOf('podify-events-style-grid') !== -1;
        var styleList = rootClass.indexOf('podify-events-style-list') !== -1;
        var layoutGrid = section.classList.contains('podify-events-layout-grid');
        var layoutList = section.classList.contains('podify-events-layout-list');
        var isCarousel = section.classList.contains('podify-events-carousel-enabled');

        // Apply navigation styles
        applyNavigationStyles(section);

        if (isCarousel) {
            var existsSwiper = !!section.querySelector('.swiper');
            if (!existsSwiper) {
                window.PodifyEventsInit({
                    container: '#' + section.id + ' .podify-events-grid',
                    slidesPerView: Math.max(1, parseInt(section.getAttribute('data-columns') || '1', 10)),
                    spaceBetween: Math.max(0, parseInt(section.getAttribute('data-space-between') || section.getAttribute('data-col-gap') || '20', 10)),
                    arrows: true,
                    arrowMode: (section.getAttribute('data-arrow-mode') || 'both'),
                    dots: true,
                    loop: ((section.getAttribute('data-loop') || 'no') === 'yes'),
                    center: false,
                    speed: 600
                });
            }
        } else {
            destroySwiper(grid);
            applyGrid(grid);
        }

        try {
            var cols = parseInt(section.getAttribute('data-columns') || '4', 10);
            var gap = parseInt(section.getAttribute('data-col-gap') || '30', 10);
            var current = { layout: layoutGrid ? 'grid' : (layoutList ? 'list' : 'unknown'), columns: cols, gap: gap, carousel: !!isCarousel };
            var prev = section._podifyState;
            var changed = !prev || prev.layout !== current.layout || prev.columns !== current.columns || prev.gap !== current.gap || prev.carousel !== current.carousel;
            if (changed) {
                section._podifyState = current;
                section.style.transition = 'all 0.3s ease';
                setTimeout(function () { section.style.transition = ''; }, 300);
            }
        } catch (e) { }
    }

    function autoInit() {
        var wrappers = document.querySelectorAll('.podify-events-wrapper');
        wrappers.forEach(function (section) {
            initOrGrid(section);
            if (inEditor) {
                var obs = new MutationObserver(function () {
                    initOrGrid(section);
                    applyNavigationStyles(section);
                });
                obs.observe(section, { attributes: true, childList: true, subtree: true });
                var root = section.closest('.elementor-widget-podify-events') || section.closest('.elementor-widget');
                if (root) {
                    obs.observe(root, { attributes: true, childList: true, subtree: true });
                }
            }

            // Handle clicks - check if using modal or direct link
            section.addEventListener('click', function (e) {
                var link = e.target.closest('.podify-open-modal');
                if (!link) return; // If not a modal link, let default behavior happen

                e.preventDefault();
                e.stopPropagation();

                if (section._podifyModalLoading) return;
                section._podifyModalLoading = true;

                var postId = link.getAttribute('data-event-id');
                var modal = section.querySelector('.podify-modal');
                if (!modal) { section._podifyModalLoading = false; return; }
                var content = modal.querySelector('.podify-modal__content');

                try {
                    var card = link.closest('article.podify-event-card');
                    var imgSrc = '';
                    var imgEl = card ? card.querySelector('.event-image img') : null;
                    if (imgEl && imgEl.getAttribute('src')) { imgSrc = imgEl.getAttribute('src'); }
                    var titleEl = card ? card.querySelector('.event-title') : null;
                    var titleText = titleEl ? (titleEl.textContent || '') : '';
                    var metaEl = card ? card.querySelector('.podify-event-meta') : null;
                    var metaHTML = metaEl ? metaEl.innerHTML : '';
                    var excerptEl = card ? card.querySelector('.event-excerpt') : null;
                    var excerptHTML = excerptEl ? excerptEl.innerHTML : '';

                    var html = '<div class="podify-popover">';
                    html += '<div class="podify-popover__left">';
                    if (imgSrc) { html += '<img src="' + imgSrc + '" alt="" />'; }
                    html += '<div class="podify-popover__map" style="min-height:12px"></div>';
                    html += '</div>';
                    html += '<div class="podify-popover__right">';
                    html += '<h2 class="podify-popover__title">' + (titleText || '') + '</h2>';
                    if (metaHTML) { html += '<div class="podify-event-meta">' + metaHTML + '</div>'; }
                    if (excerptHTML) { html += '<div class="event-excerpt">' + excerptHTML + '</div>'; }
                    html += '</div>';
                    html += '</div>';
                    content.innerHTML = html;
                } catch (_) { content.innerHTML = '<div class="podify-modal__loading">Loading…</div>'; }

                modal.removeAttribute('hidden');
                try { document.body.style.overflow = 'hidden'; document.body.classList.add('podify-modal-open'); } catch (_) { }

                if (section._podifyModalController) { try { section._podifyModalController.abort(); } catch (_) { } }
                var ctrl = new AbortController();
                section._podifyModalController = ctrl;

                var url = (window.PodifyEventsConfig && PodifyEventsConfig.ajaxUrl) ? PodifyEventsConfig.ajaxUrl : '/wp-admin/admin-ajax.php';
                var body = 'action=podify_event_details&id=' + encodeURIComponent(postId || '');

                var timeoutId = setTimeout(function () { try { ctrl.abort(); } catch (_) { } }, 10000);
                fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body, signal: ctrl.signal })
                    .then(function (r) { return r.json().catch(function () { return null; }); })
                    .then(function (obj) {
                        if (obj && obj.success) { content.innerHTML = obj.data; }
                        else { content.innerHTML = '<div class="podify-modal__error">Failed to load.</div>'; }
                    })
                    .catch(function (err) { if (err && err.name === 'AbortError') return; content.innerHTML = '<div class="podify-modal__error">Failed to load.</div>'; })
                    .finally(function () { clearTimeout(timeoutId); section._podifyModalLoading = false; });

                if (!section._podifyEscBound) {
                    section._podifyEscBound = true;
                    document.addEventListener('keydown', function (ev) {
                        if (ev.key === 'Escape') {
                            var m = section.querySelector('.podify-modal');
                            if (m && !m.hasAttribute('hidden')) {
                                m.setAttribute('hidden', '');
                                try {
                                    section._podifyModalController && section._podifyModalController.abort();
                                } catch (_) { }
                                try {
                                    document.body.style.overflow = '';
                                    document.body.classList.remove('podify-modal-open'); // Remove class
                                } catch (_) { }
                            }
                        }
                    });
                }
            });

            section.addEventListener('click', function (e) {
                if (e.target && e.target.getAttribute('data-close') === 'true') {
                    var modal = section.querySelector('.podify-modal');
                    if (modal) {
                        modal.setAttribute('hidden', '');
                        try {
                            section._podifyModalController && section._podifyModalController.abort();
                        } catch (_) { }
                        var content = modal.querySelector('.podify-modal__content');
                        if (content) content.innerHTML = '';
                        try {
                            document.body.style.overflow = '';
                            document.body.classList.remove('podify-modal-open'); // Remove class
                        } catch (_) { }
                    }
                }
            });
        });

        section.addEventListener('keydown', function (e) {
            var isTriggerKey = (e.key === 'Enter' || e.key === ' ' || e.code === 'Space');
            if (!isTriggerKey) return;
            var link = e.target.closest('.podify-read-more, .podify-open-modal');
            if (!link) return;
            e.preventDefault();
            e.stopPropagation();
            try { link.click(); } catch (_) { }
        });
    }

    function applyBadgePositioning() {
        var badges = document.querySelectorAll('.podify-badge[data-position-v], .podify-badge[data-position-h]');
        badges.forEach(function (badge) {
            var posV = badge.getAttribute('data-position-v') || 'bottom';
            var posH = badge.getAttribute('data-position-h') || 'center';

            // Reset all positioning
            badge.style.top = '';
            badge.style.bottom = '';
            badge.style.left = '';
            badge.style.right = '';
            badge.style.transform = '';

            // Apply vertical positioning
            if (posV === 'top') {
                badge.style.top = '12px';
                badge.style.bottom = 'auto';
            } else {
                badge.style.bottom = '12px';
                badge.style.top = 'auto';
            }

            // Apply horizontal positioning
            if (posH === 'left') {
                badge.style.left = '12px';
                badge.style.right = 'auto';
                badge.style.transform = 'translateX(0)';
            } else if (posH === 'right') {
                badge.style.right = '12px';
                badge.style.left = 'auto';
                badge.style.transform = 'translateX(0)';
            } else {
                badge.style.left = '50%';
                badge.style.right = 'auto';
                badge.style.transform = 'translateX(-50%)';
            }
        });
    }

    var schedule;
    function refreshAll() {
        if (!inEditor) return;
        if (schedule) { clearTimeout(schedule); }
        schedule = setTimeout(function () {
            var wrappers = document.querySelectorAll('.podify-events-wrapper');
            wrappers.forEach(function (section) {
                section.classList.add('podify-transitioning');
                initOrGrid(section);
                applyNavigationStyles(section);
                setTimeout(function () {
                    section.classList.remove('podify-transitioning');
                }, 300);
            });
        }, 100);
    }

    // Force apply navigation styles on load
    function forceApplyNavigationStyles() {
        var wrappers = document.querySelectorAll('.podify-events-wrapper');
        wrappers.forEach(function (section) {
            applyNavigationStyles(section);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            autoInit();
            refreshAll();
            setTimeout(forceApplyNavigationStyles, 500);
            applyBadgePositioning();
        });
    } else {
        autoInit();
        refreshAll();
        setTimeout(forceApplyNavigationStyles, 500);
        applyBadgePositioning();
    }

    if (window.elementorFrontend && elementorFrontend.hooks) {
        elementorFrontend.hooks.addAction('frontend/element_ready/podify-events.default', function ($el) {
            autoInit();
            refreshAll();
            setTimeout(forceApplyNavigationStyles, 500);
            applyBadgePositioning();
        });
    }

    try {
        if (window.elementor && elementor.channels && elementor.channels.editor) {
            elementor.channels.editor.on('change', function () {
                refreshAll();
                setTimeout(forceApplyNavigationStyles, 1000);
                applyBadgePositioning();
            });
        }
    } catch (e) { }

    try {
        if (inEditor) {
            var globalObserver = new MutationObserver(function (m) {
                for (var i = 0; i < m.length; i++) {
                    var t = m[i];
                    if (t.type === 'attributes' || t.type === 'childList') {
                        refreshAll();
                        setTimeout(forceApplyNavigationStyles, 500);
                        break;
                    }
                }
            });
            globalObserver.observe(document.body, { attributes: true, childList: true, subtree: true });
        }
    } catch (e) { }
})(window, document);
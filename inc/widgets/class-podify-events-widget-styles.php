<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Podify_Events_Widget_Styles {

    protected static $registered = false;
    protected static $handle     = 'podify-events-css';

    /**
     * Called from Elementor Widget render()
     */
    public static function enqueue() {
        if ( self::$registered ) {
            return;
        }

        self::$registered = true;

        // Immediately enqueue for frontend render to avoid missing the hook timing
        self::register_assets();

        // Ensure assets also load in admin/editor contexts
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'register_assets' ], 20 );
        add_action( 'elementor/editor/after_enqueue_styles', [ __CLASS__, 'enqueue_editor_assets' ] );
        add_action( 'elementor/editor/after_enqueue_scripts', [ __CLASS__, 'enqueue_editor_assets' ] );
        add_action( 'elementor/frontend/after_enqueue_styles', [ __CLASS__, 'register_assets' ] );
        add_action( 'elementor/frontend/after_enqueue_scripts', [ __CLASS__, 'register_assets' ] );
        add_action( 'elementor/preview/enqueue_styles', [ __CLASS__, 'enqueue_editor_assets' ] );
        add_action( 'elementor/preview/enqueue_scripts', [ __CLASS__, 'enqueue_editor_assets' ] );
    }

    /**
     * Load CSS + JS for frontend and admin preview
     */
    public static function register_assets() {

        /* -------------------------------------------------------
         * 1. MAIN WIDGET CSS
         * ------------------------------------------------------- */

        $css_path = PODIFY_EVENTS_PATH . 'assets/css/podify-events.css';
        $css_url  = PODIFY_EVENTS_URL  . 'assets/css/podify-events.css';

        if ( file_exists( $css_path ) ) {
            wp_register_style( self::$handle, $css_url, [], PODIFY_EVENTS_VERSION );
            wp_enqueue_style( self::$handle );
            if ( wp_style_is( 'dashicons', 'registered' ) || function_exists( 'wp_enqueue_style' ) ) {
                wp_enqueue_style( 'dashicons' );
            }
        } else {
            // fallback
            wp_register_style( self::$handle, false, [], null );
            wp_enqueue_style( self::$handle );
            wp_add_inline_style( self::$handle, self::fallback_css() );
        }

        // Layout specific CSS (grid, list, carousel) – register only, enqueue per widget
        $grid_css_path = PODIFY_EVENTS_PATH . 'assets/css/podify-events-grid.css';
        $list_css_path = PODIFY_EVENTS_PATH . 'assets/css/podify-events-list.css';
        $car_css_path  = PODIFY_EVENTS_PATH . 'assets/css/podify-events-carousel.css';
        if ( file_exists( $grid_css_path ) ) {
            wp_register_style( 'podify-events-grid', PODIFY_EVENTS_URL . 'assets/css/podify-events-grid.css', [ self::$handle ], PODIFY_EVENTS_VERSION );
        }
        if ( file_exists( $list_css_path ) ) {
            wp_register_style( 'podify-events-list', PODIFY_EVENTS_URL . 'assets/css/podify-events-list.css', [ self::$handle ], PODIFY_EVENTS_VERSION );
        }
        if ( file_exists( $car_css_path ) ) {
            wp_register_style( 'podify-events-carousel', PODIFY_EVENTS_URL . 'assets/css/podify-events-carousel.css', [ self::$handle ], PODIFY_EVENTS_VERSION );
        }

        /* -------------------------------------------------------
         * 2. SWIPER CSS (LOCAL)
         * ------------------------------------------------------- */

        $swiper_css_path = PODIFY_EVENTS_PATH . 'assets/css/swiper-bundle.min.css';
        $swiper_css_url  = PODIFY_EVENTS_URL  . 'assets/css/swiper-bundle.min.css';

        if ( file_exists( $swiper_css_path ) ) {
            wp_register_style( 'podify-swiper-css', $swiper_css_url, [], PODIFY_EVENTS_VERSION );
        }

        /* -------------------------------------------------------
         * 3. SWIPER JS (LOCAL)
         * ------------------------------------------------------- */

        $swiper_js_path = PODIFY_EVENTS_PATH . 'assets/js/swiper-bundle.min.js';
        $swiper_js_url  = PODIFY_EVENTS_URL  . 'assets/js/swiper-bundle.min.js';

        if ( file_exists( $swiper_js_path ) ) {
            wp_register_script( 'podify-swiper-js', $swiper_js_url, [], PODIFY_EVENTS_VERSION, true );
            // Also register WordPress-standard 'swiper' handle for compatibility
            wp_register_script( 'swiper', $swiper_js_url, [], PODIFY_EVENTS_VERSION, true );
        }

        /* -------------------------------------------------------
         * 4. PLUGIN JS (carousel initializer)
         * ------------------------------------------------------- */

        $events_js_path = PODIFY_EVENTS_PATH . 'assets/js/podify-events.js';
        $events_js_url  = PODIFY_EVENTS_URL  . 'assets/js/podify-events.js';

        if ( file_exists( $events_js_path ) ) {
            wp_register_script( 'podify-events-js', $events_js_url, [ 'jquery' ], PODIFY_EVENTS_VERSION, true );
            wp_enqueue_script( 'podify-events-js' );
            wp_localize_script( 'podify-events-js', 'PodifyEventsConfig', [ 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ] );
        }

        $swiper_init_path = PODIFY_EVENTS_PATH . 'assets/js/podify-events-swiper.js';
        $swiper_init_url  = PODIFY_EVENTS_URL  . 'assets/js/podify-events-swiper.js';
        if ( file_exists( $swiper_init_path ) ) {
            wp_register_script( 'podify-events-swiper', $swiper_init_url, [ 'jquery', 'elementor-frontend', 'swiper' ], PODIFY_EVENTS_VERSION, true );
            wp_enqueue_script( 'podify-events-swiper' );
        }
}

    /**
     * Elementor Editor – loads CSS & Swiper to preview correctly
     */
    public static function enqueue_editor_assets() {

        // Ensure base CSS
        if ( ! wp_style_is( self::$handle, 'enqueued' ) ) {
            $css_path = PODIFY_EVENTS_PATH . 'assets/css/podify-events.css';
            $css_url  = PODIFY_EVENTS_URL  . 'assets/css/podify-events.css';
            if ( file_exists( $css_path ) ) {
                wp_register_style( self::$handle, $css_url, [], filemtime( $css_path ) );
                wp_enqueue_style( self::$handle );
                if ( wp_style_is( 'dashicons', 'registered' ) || function_exists( 'wp_enqueue_style' ) ) {
                    wp_enqueue_style( 'dashicons' );
                }
            } else {
                wp_register_style( self::$handle, false, [], null );
                wp_enqueue_style( self::$handle );
                wp_add_inline_style( self::$handle, self::fallback_css() );
            }
        }

        // Ensure Swiper is available in editor
        if ( file_exists( PODIFY_EVENTS_PATH . 'assets/css/swiper-bundle.min.css' ) ) {
            wp_register_style( 'podify-swiper-css', PODIFY_EVENTS_URL . 'assets/css/swiper-bundle.min.css', [], PODIFY_EVENTS_VERSION );
        }

        // Register layout specific CSS for editor; enqueue carousel to reflect nav placement in preview
        $grid_css_path = PODIFY_EVENTS_PATH . 'assets/css/podify-events-grid.css';
        $list_css_path = PODIFY_EVENTS_PATH . 'assets/css/podify-events-list.css';
        $car_css_path  = PODIFY_EVENTS_PATH . 'assets/css/podify-events-carousel.css';
        if ( file_exists( $grid_css_path ) ) {
            wp_register_style( 'podify-events-grid', PODIFY_EVENTS_URL . 'assets/css/podify-events-grid.css', [ self::$handle ], PODIFY_EVENTS_VERSION );
            wp_enqueue_style( 'podify-events-grid' );
        }
        if ( file_exists( $list_css_path ) ) {
            wp_register_style( 'podify-events-list', PODIFY_EVENTS_URL . 'assets/css/podify-events-list.css', [ self::$handle ], PODIFY_EVENTS_VERSION );
            wp_enqueue_style( 'podify-events-list' );
        }
        if ( file_exists( $car_css_path ) ) {
            wp_register_style( 'podify-events-carousel', PODIFY_EVENTS_URL . 'assets/css/podify-events-carousel.css', [ self::$handle ], PODIFY_EVENTS_VERSION );
            wp_enqueue_style( 'podify-events-carousel' );
        }

        if ( file_exists( PODIFY_EVENTS_PATH . 'assets/js/swiper-bundle.min.js' ) ) {
            wp_register_script( 'podify-swiper-js', PODIFY_EVENTS_URL . 'assets/js/swiper-bundle.min.js', [], PODIFY_EVENTS_VERSION, true );
            wp_register_script( 'swiper', PODIFY_EVENTS_URL . 'assets/js/swiper-bundle.min.js', [], PODIFY_EVENTS_VERSION, true );
            if ( wp_script_is( 'swiper', 'registered' ) ) { wp_enqueue_script( 'swiper' ); }
        }

        if ( file_exists( PODIFY_EVENTS_PATH . 'assets/js/podify-events-swiper.js' ) ) {
            wp_register_script( 'podify-events-swiper', PODIFY_EVENTS_URL . 'assets/js/podify-events-swiper.js', [ 'jquery', 'elementor-frontend', 'swiper' ], PODIFY_EVENTS_VERSION, true );
            wp_enqueue_script( 'podify-events-swiper' );
        }

        // Ensure widget controller JS is available in the editor
        if ( file_exists( PODIFY_EVENTS_PATH . 'assets/js/podify-events.js' ) ) {
            wp_register_script( 'podify-events-js', PODIFY_EVENTS_URL . 'assets/js/podify-events.js', [ 'jquery' ], PODIFY_EVENTS_VERSION, true );
            wp_enqueue_script( 'podify-events-js' );
            wp_localize_script( 'podify-events-js', 'PodifyEventsConfig', [ 'ajaxUrl' => admin_url( 'admin-ajax.php' ) ] );
        }
    }

    /**
     * Enqueue CSS for a specific widget instance layout
     */
    public static function enqueue_layout( $layout, $carousel = false ) {
        if ( $layout === 'grid' ) {
            if ( wp_style_is( 'podify-events-list', 'enqueued' ) ) wp_dequeue_style( 'podify-events-list' );
            if ( wp_style_is( 'podify-events-grid', 'registered' ) ) wp_enqueue_style( 'podify-events-grid' );
        } elseif ( $layout === 'list' ) {
            if ( wp_style_is( 'podify-events-grid', 'enqueued' ) ) wp_dequeue_style( 'podify-events-grid' );
            if ( wp_style_is( 'podify-events-list', 'registered' ) ) wp_enqueue_style( 'podify-events-list' );
        }
        if ( $carousel ) {
            if ( wp_style_is( 'podify-events-carousel', 'registered' ) ) wp_enqueue_style( 'podify-events-carousel' );
            if ( wp_script_is( 'podify-swiper-js', 'registered' ) ) wp_enqueue_script( 'podify-swiper-js' );
        }
    }

    /**
     * Register handles early so Elementor can load via get_*_depends
     */
    public static function register_handles() {
        $css_path = PODIFY_EVENTS_PATH . 'assets/css/podify-events.css';
        $css_url  = PODIFY_EVENTS_URL  . 'assets/css/podify-events.css';
        if ( file_exists( $css_path ) ) {
            wp_register_style( self::$handle, $css_url, [], PODIFY_EVENTS_VERSION );
        }
        $swiper_css_path = PODIFY_EVENTS_PATH . 'assets/css/swiper-bundle.min.css';
        $swiper_css_url  = PODIFY_EVENTS_URL  . 'assets/css/swiper-bundle.min.css';
        if ( file_exists( $swiper_css_path ) ) {
            wp_register_style( 'podify-swiper-css', $swiper_css_url, [], PODIFY_EVENTS_VERSION );
        }
        $swiper_js_path = PODIFY_EVENTS_PATH . 'assets/js/swiper-bundle.min.js';
        $swiper_js_url  = PODIFY_EVENTS_URL  . 'assets/js/swiper-bundle.min.js';
        if ( file_exists( $swiper_js_path ) ) {
            wp_register_script( 'podify-swiper-js', $swiper_js_url, [], PODIFY_EVENTS_VERSION, true );
            wp_register_script( 'swiper', $swiper_js_url, [], PODIFY_EVENTS_VERSION, true );
        }
        $events_js_path = PODIFY_EVENTS_PATH . 'assets/js/podify-events.js';
        $events_js_url  = PODIFY_EVENTS_URL  . 'assets/js/podify-events.js';
        if ( file_exists( $events_js_path ) ) {
            wp_register_script( 'podify-events-js', $events_js_url, [ 'jquery', 'swiper' ], PODIFY_EVENTS_VERSION, true );
        }

        $swiper_init_path = PODIFY_EVENTS_PATH . 'assets/js/podify-events-swiper.js';
        $swiper_init_url  = PODIFY_EVENTS_URL  . 'assets/js/podify-events-swiper.js';
        if ( file_exists( $swiper_init_path ) ) {
            wp_register_script( 'podify-events-swiper', $swiper_init_url, [ 'jquery', 'elementor-frontend', 'swiper' ], PODIFY_EVENTS_VERSION, true );
        }
    }

    /**
     * Fallback base CSS
     */
    protected static function fallback_css() {

        return <<<'CSS'
/* ================================
   PODIFY EVENTS – FALLBACK CSS
================================ */

/* GRID */
.podify-events-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 30px;
}



@media (max-width: 1024px) {
    .podify-events-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 640px) {
    .podify-events-grid {
        grid-template-columns: 1fr;
    }
}

/* CARD */
.podify-event-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #eee;
    display: flex;
    flex-direction: column;
    transition: all .2s ease;
}
.podify-event-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.14);
}

/* IMAGE */
.event-image {
    width: 100%;
    height: 277px;
    position: relative;
    overflow: hidden;
}
.event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* BADGE */
.podify-badge {
    position: absolute;
    bottom: 12px;
    left: 12px;
    background: #ffd200;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
}

/* CONTENT */
.event-content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* TITLE */
.event-title { overflow:hidden; }
.event-title a {
    text-decoration: none;
    color: #222;
    font-size: 20px;
    font-weight: 600;
    display:block; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}
.event-title a:hover {
    color: #0073aa;
}

/* META */
.podify-event-meta {
    font-size: 14px;
    color: #666;
}

/* EXCERPT */
.event-excerpt {
    font-size: 15px;
    color: #555;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
/* no multi-line clamp for excerpt in fallback */

/* BUTTON */
.podify-read-more {
    font-weight: 600;
    color: #000;
    text-decoration: none;
    display: inline-flex;
    gap: 10px;
    background: #ffd200;
    border-radius: 28px;
    padding: 10px 16px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.podify-read-more::after {
    content: "→";
    font-size: 18px;
}

/* CAROUSEL MODE */
.podify-events-carousel-enabled .podify-events-grid {
    display: flex;
    gap: 30px;
    overflow: visible;
}

/* Carousel Block Style helper */
.podify-events-style-carousel .podify-events-grid { display:flex; }

        /* minimal controls styling for carousel */
        .podify-events-carousel-enabled .swiper-button-prev,
        .podify-events-carousel-enabled .swiper-button-next{ color:#1a1a1a; background:#fff; border:1px solid #e5e5e5; width:36px; height:36px; border-radius:50%; box-shadow:0 2px 6px rgba(0,0,0,0.08); }
        .podify-events-carousel-enabled .swiper-button-prev::after,
        .podify-events-carousel-enabled .swiper-button-next::after{ display:none; content:none; }
        .podify-events-carousel-enabled .swiper-button-prev::before,
        .podify-events-carousel-enabled .swiper-button-next::before{ display:none; content:none; }
        .podify-events-carousel-enabled .swiper-pagination-bullet{ background:#c7c7c7; opacity:1; }
        .podify-events-carousel-enabled .swiper-pagination-bullet-active{ background:#1a1a1a; }

CSS;
    }
}

add_action( 'init', [ 'Podify_Events_Widget_Styles', 'register_handles' ] );

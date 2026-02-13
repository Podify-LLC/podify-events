<?php
/**
 * Podify Events – Admin Enhancements
 *
 * Handles:
 * - Admin columns for event date, time, location
 * - Sorting by event date
 * - Quick view helpers
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Podify_Events_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        add_filter( 'manage_podify_event_posts_columns', [ $this, 'add_columns' ] );
        add_action( 'manage_podify_event_posts_custom_column', [ $this, 'render_columns' ], 10, 2 );

        add_filter( 'manage_edit-podify_event_sortable_columns', [ $this, 'sortable_columns' ] );
        add_action( 'pre_get_posts', [ $this, 'sort_by_meta' ] );

        add_action( 'admin_head', [ $this, 'admin_style' ] );
    }

    /**
     * Add admin columns
     */
    public function add_columns( $cols ) {

        $new_cols = [];

        // Keep checkbox + add thumbnail + title first
        if ( isset( $cols['cb'] ) ) $new_cols['cb'] = $cols['cb'];
        $new_cols['thumbnail'] = __( 'Thumbnail', 'podify-events' );
        $new_cols['title'] = __( 'Event Title', 'podify-events' );

        // Add our custom columns
        $new_cols['event_date']    = __( 'Date', 'podify-events' );
        $new_cols['event_time']    = __( 'Time', 'podify-events' );
        $new_cols['event_address'] = __( 'Location', 'podify-events' );

        // Keep date column last
        if ( isset( $cols['date'] ) ) $new_cols['date'] = $cols['date'];

        return $new_cols;
    }

    /**
     * Render admin column values
     */
    public function render_columns( $col, $post_id ) {

        switch ( $col ) {

            case 'thumbnail':
                if ( has_post_thumbnail( $post_id ) ) {
                    $img = get_the_post_thumbnail( $post_id, 'thumbnail', [ 'style' => 'width:44px;height:44px;object-fit:cover;border-radius:6px;display:block;' ] );
                    echo $img ? $img : '—';
                } else {
                    echo '—';
                }
                break;

            case 'event_date':
                $start = get_post_meta( $post_id, '_podify_event_date_start', true );
                $end   = get_post_meta( $post_id, '_podify_event_date_end', true );
                $date  = $start ? $start : get_post_meta( $post_id, '_podify_event_date', true );
                if ( $date ) {
                    if ( $end && $end !== $date ) {
                        echo '<strong>' . esc_html( $date . ' – ' . $end ) . '</strong>';
                    } else {
                        echo '<strong>' . esc_html( $date ) . '</strong>';
                    }
                } else {
                    echo '—';
                }
                break;

            case 'event_time':
                $time = get_post_meta( $post_id, '_podify_event_time', true );
                echo $time ? esc_html( $time ) : '—';
                break;

            case 'event_address':
                $addr = get_post_meta( $post_id, '_podify_event_address', true );
                echo $addr ? esc_html( $addr ) : '—';
                break;
        }
    }

    /**
     * Make event date sortable
     */
    public function sortable_columns( $cols ) {
        $cols['event_date'] = 'event_date';
        return $cols;
    }

    /**
     * Sort query by event date
     */
    public function sort_by_meta( $query ) {

        if ( ! is_admin() || ! $query->is_main_query() ) return;
        if ( $query->get( 'post_type' ) !== 'podify_event' ) return;

        $orderby = $query->get( 'orderby' );

        if ( $orderby === 'event_date' ) {
            $query->set( 'meta_key', '_podify_event_date' );
            $query->set( 'orderby', 'meta_value' );
        }
    }

    /**
     * Admin CSS for better readability
     */
    public function admin_style() {
        global $current_screen;

        if ( ! $current_screen || $current_screen->post_type !== 'podify_event' ) {
            return;
        }

        echo '<style>
            /* Layout Fixes & Modernization */
            .post-type-podify_event .wp-header-end { display: none; }
            .post-type-podify_event .wrap {
                margin: 25px 20px 0 20px !important;
                max-width: none !important;
            }

            /* Header Area Flex Layout */
            .post-type-podify_event .wp-heading-inline {
                margin: 0 15px 15px 0 !important;
                display: inline-block !important;
                vertical-align: middle;
            }
            
            .post-type-podify_event .page-title-action {
                vertical-align: middle;
                margin-top: -12px !important;
            }
            .post-type-podify_event .page-title-action::before {
                content: "\f132";
                font-family: dashicons;
                font-size: 16px;
                margin-right: 5px;
            }

            /* Status Links (All | Published | Trash) */
            .post-type-podify_event .subsubsub {
                margin: 0 !important;
                padding: 15px 0 !important;
                float: left !important;
                display: block;
                line-height: 28px;
            }
            .post-type-podify_event .subsubsub li {
                font-size: 13px;
                color: #718096;
            }
            .post-type-podify_event .subsubsub li a {
                color: #4a5568;
                padding: 4px 8px;
                border-radius: 4px;
                transition: all 0.2s;
            }
            .post-type-podify_event .subsubsub li a.current {
                background: #edf2ff;
                color: #4f46e5;
                font-weight: 600;
            }

            /* Search Box - Aligned with status links */
            .post-type-podify_event .search-box {
                float: right !important;
                margin: 12px 0 !important;
                position: relative;
            }
            .post-type-podify_event .search-box input[type="search"] {
                width: 280px !important;
                padding: 10px 16px 10px 40px !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 10px !important;
                background: #fff !important;
                font-size: 14px !important;
                transition: all 0.2s ease !important;
                box-shadow: 0 2px 4px rgba(0,0,0,0.02) !important;
            }
            .post-type-podify_event .search-box input[type="search"]:focus {
                border-color: #4f46e5 !important;
                box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1) !important;
                outline: none !important;
            }
            .post-type-podify_event #search-submit {
                display: none !important;
            }
            .post-type-podify_event .search-box::before {
                content: "\f179";
                font-family: dashicons;
                position: absolute;
                left: 14px;
                top: 50%;
                transform: translateY(-50%);
                color: #a0aec0;
                font-size: 18px;
                z-index: 1;
            }

            /* Table Navigation (Filters) */
            .post-type-podify_event .tablenav.top {
                clear: both;
                background: #f8fafc;
                padding: 15px !important;
                border-radius: 12px 12px 0 0;
                border: 1px solid #edf2f7;
                border-bottom: none;
                height: auto !important;
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 10px;
            }
            .post-type-podify_event .tablenav .actions {
                padding: 0 !important;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .post-type-podify_event .tablenav .actions select {
                border-radius: 8px !important;
                border: 1px solid #e2e8f0 !important;
                padding: 4px 10px !important;
                background: #fff !important;
                font-size: 13px !important;
                color: #4a5568 !important;
                height: 36px !important;
                min-width: 140px;
            }
            .post-type-podify_event .tablenav .button {
                border-radius: 8px !important;
                border: 1px solid #e2e8f0 !important;
                background: #fff !important;
                padding: 0 15px !important;
                height: 36px !important;
                line-height: 34px !important;
                font-weight: 600 !important;
                color: #4a5568 !important;
                transition: all 0.2s !important;
            }
            .post-type-podify_event .tablenav .button:hover {
                background: #f1f5f9 !important;
                border-color: #cbd5e0 !important;
            }

            /* List Table Overhaul */
            .wp-list-table {
                border: 1px solid #edf2f7 !important;
                box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
                border-radius: 0 0 12px 12px !important;
                overflow: hidden !important;
                background: #fff !important;
                border-collapse: separate !important;
                border-spacing: 0 !important;
            }
            .wp-list-table thead th {
                background: #fdfdfd !important;
                padding: 16px 12px !important;
                border-bottom: 1px solid #edf2f7 !important;
                font-weight: 600 !important;
                color: #4a5568 !important;
                text-transform: uppercase !important;
                font-size: 11px !important;
                letter-spacing: 0.05em !important;
            }
            .wp-list-table tbody td {
                padding: 20px 12px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #f7fafc !important;
                color: #2d3748 !important;
            }
            .wp-list-table tbody tr:hover {
                background-color: #fcfdfe !important;
            }

            /* Column Specific Styles */
            .column-thumbnail { width: 80px !important; }
            .column-thumbnail img {
                width: 48px !important;
                height: 48px !important;
                object-fit: cover;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            
            .column-title {
                min-width: 250px;
            }
            .column-title strong a {
                font-size: 15px !important;
                font-weight: 700 !important;
                color: #1a202c !important;
                display: block;
                margin-bottom: 4px;
            }
            .column-title .row-actions {
                visibility: visible !important;
                opacity: 0.5;
            }
            tr:hover .column-title .row-actions {
                opacity: 1;
            }

            .column-event_date { width: 160px; }
            .column-event_date strong {
                color: #4f46e5;
                background: #eef2ff;
                padding: 5px 10px;
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                white-space: nowrap;
            }

            .column-event_time { width: 100px; font-weight: 500; }
            .column-event_address { width: 220px; color: #718096; font-size: 13px; }

            /* Buttons Global */
            /* .wp-core-ui .button-primary {
                background: #4f46e5 !important;
                border: none !important;
                box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2) !important;
                height: 36px !important;
                line-height: 36px !important;
                padding: 0 20px !important;
                border-radius: 8px !important;
                font-weight: 600 !important;
            } */
        </style>';
    }

}

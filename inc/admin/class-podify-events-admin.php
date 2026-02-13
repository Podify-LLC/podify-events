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
            /* Global Admin Buttons & Layout Modernization */
            .wp-core-ui .button-primary {
                background: #4f46e5 !important;
                border: none !important;
                border-radius: 6px !important;
                box-shadow: 0 2px 4px rgba(79, 70, 229, 0.2) !important;
                padding: 4px 16px !important;
                height: auto !important;
                line-height: 2 !important;
                font-weight: 500 !important;
                transition: all 0.2s ease !important;
            }
            .wp-core-ui .button-primary:hover {
                background: #4338ca !important;
                transform: translateY(-1px) !important;
                box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3) !important;
            }
            .wp-core-ui .button-secondary {
                border-radius: 6px !important;
                border: 1px solid #e2e8f0 !important;
                color: #4a5568 !important;
                padding: 4px 16px !important;
                height: auto !important;
                line-height: 2 !important;
                font-weight: 500 !important;
                transition: all 0.2s ease !important;
            }
            .wp-core-ui .button-secondary:hover {
                background: #f8f9fa !important;
                border-color: #cbd5e0 !important;
                color: #2d3748 !important;
            }

            /* Admin List Table Modernization */
            .wp-list-table {
                border: none !important;
                box-shadow: 0 4px 20px rgba(0,0,0,0.04) !important;
                border-radius: 12px !important;
                overflow: hidden !important;
                background: #fff !important;
                margin-top: 25px !important;
                border-collapse: separate !important;
                border-spacing: 0 !important;
            }
            .wp-list-table thead th {
                background: #fdfdfd !important;
                padding: 16px 12px !important;
                border-bottom: 1px solid #edf2f7 !important;
                font-weight: 600 !important;
                color: #1a202c !important;
                text-transform: uppercase !important;
                font-size: 11px !important;
                letter-spacing: 0.05em !important;
            }
            .wp-list-table tbody td {
                padding: 18px 12px !important;
                vertical-align: middle !important;
                border-bottom: 1px solid #f7fafc !important;
                color: #4a5568 !important;
                font-size: 14px !important;
            }
            .wp-list-table tbody tr:last-child td {
                border-bottom: none !important;
            }
            .wp-list-table tbody tr:hover {
                background-color: #fcfdfe !important;
            }
            .column-thumbnail { width: 70px !important; }
            .column-thumbnail img {
                box-shadow: 0 4px 8px rgba(0,0,0,0.08);
                border: 2px solid #fff;
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                border-radius: 8px !important;
            }
            .column-thumbnail img:hover {
                transform: scale(1.15) rotate(2deg);
            }
            .column-title strong a {
                color: #1a202c !important;
                font-size: 16px !important;
                font-weight: 700 !important;
                text-decoration: none !important;
                transition: color 0.2s !important;
            }
            .column-title strong a:hover {
                color: #4f46e5 !important;
            }
            .column-title .row-actions {
                margin-top: 6px !important;
                visibility: visible !important;
                opacity: 0.4;
                transition: opacity 0.2s;
            }
            tr:hover .column-title .row-actions {
                opacity: 1;
            }
            .column-title .row-actions span a {
                color: #718096 !important;
                font-size: 12px !important;
            }
            .column-title .row-actions span.trash a {
                color: #e53e3e !important;
            }
            
            .column-event_date strong {
                color: #4f46e5;
                background: #eef2ff;
                padding: 6px 10px;
                border-radius: 6px;
                font-size: 13px;
                display: inline-block;
                font-weight: 600;
            }
            .column-event_time, .column-event_address {
                font-size: 14px;
                color: #4a5568;
            }
            .column-event_time {
                font-weight: 500;
            }
            
            /* Badges for status */
            .column-date {
                font-size: 12px !important;
                color: #a0aec0 !important;
            }

            /* Search Box & Filters */
            .search-box input[type="search"] {
                border-radius: 8px !important;
                border: 1px solid #e2e8f0 !important;
                padding: 6px 12px !important;
                box-shadow: none !important;
            }
            .tablenav .actions select {
                border-radius: 6px !important;
                border: 1px solid #e2e8f0 !important;
                height: 32px !important;
            }
            
            /* Alignments */
            .column-event_date { width: 180px; }
            .column-event_time { width: 120px; }
            .column-event_address { width: 200px; }
        </style>';
    }

}

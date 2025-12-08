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
            .column-thumbnail { width: 70px; }
            .column-event_date,
            .column-event_time,
            .column-event_address {
                width: 150px;
            }
            .column-event_address {
                width: 220px;
            }
        </style>';
    }

}

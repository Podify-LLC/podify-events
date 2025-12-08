<?php
/**
 * Podify Events – Custom Post Type
 *
 * Registers the "podify_event" CPT with proper labels,
 * Elementor compatibility, and rewrite structure.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Podify_Events_CPT {

    public function __construct() {
        add_action( 'init', [ $this, 'register_cpt' ] );
        add_action( 'init', [ $this, 'register_taxonomies' ] );
    }

    /**
     * Register Custom Post Type
     */
    public function register_cpt() {

        $labels = [
            'name'                  => __( 'Events', 'podify-events' ),
            'singular_name'         => __( 'Event', 'podify-events' ),
            'menu_name'             => __( 'Events', 'podify-events' ),
            'name_admin_bar'        => __( 'Event', 'podify-events' ),
            'add_new'               => __( 'Add New', 'podify-events' ),
            'add_new_item'          => __( 'Add New Event', 'podify-events' ),
            'edit_item'             => __( 'Edit Event', 'podify-events' ),
            'new_item'              => __( 'New Event', 'podify-events' ),
            'view_item'             => __( 'View Event', 'podify-events' ),
            'search_items'          => __( 'Search Events', 'podify-events' ),
            'not_found'             => __( 'No events found.', 'podify-events' ),
            'not_found_in_trash'    => __( 'No events found in trash.', 'podify-events' ),
            'all_items'             => __( 'All Events', 'podify-events' ),
            'archives'              => __( 'Event Archives', 'podify-events' ),
        ];

        $args = [
            'label'               => __( 'Events', 'podify-events' ),
            'labels'              => $labels,
            'description'         => __( 'Events managed by the Podify Events plugin', 'podify-events' ),
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-calendar-alt',
            'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
            'has_archive'         => true,
            'rewrite'             => [ 'slug' => 'events' ],
            'show_in_rest'        => true, // Gutenberg + Elementor compatibility
            'publicly_queryable'  => true,
            'exclude_from_search' => false,
            'capability_type'     => 'post',
            'taxonomies'          => [ 'podify_event_category', 'podify_event_tag' ],
        ];

        register_post_type( 'podify_event', $args );
    }

    public function register_taxonomies() {
        register_taxonomy( 'podify_event_category', [ 'podify_event' ], [
            'labels' => [
                'name' => __( 'Event Categories', 'podify-events' ),
                'singular_name' => __( 'Event Category', 'podify-events' ),
            ],
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [ 'slug' => 'event-category' ],
            'show_in_rest' => true,
        ] );

        register_taxonomy( 'podify_event_tag', [ 'podify_event' ], [
            'labels' => [
                'name' => __( 'Event Tags', 'podify-events' ),
                'singular_name' => __( 'Event Tag', 'podify-events' ),
            ],
            'hierarchical' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => [ 'slug' => 'event-tag' ],
            'show_in_rest' => true,
        ] );
    }
}

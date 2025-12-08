<?php

// ---- GitHub Auto-updater Initialization ----
if ( is_admin() ) {
    if ( file_exists( plugin_dir_path( __FILE__ ) . 'includes/github-updater.php' ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/github-updater.php';
        // Replace with your actual repo: owner/repo
        if ( class_exists( 'Podify_GitHub_Updater' ) ) {
            $podify_updater = new Podify_GitHub_Updater( __FILE__, 'yourusername/your-repo', 'main' );
        }
    }
}
// ---- end updater init ----

/**
 * Plugin Name: Podify Events
 * Description: A custom event management system with database table + Elementor widget integration
 * Version: 1.0.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Author: Podify Inc.
 * Text Domain: podify-events
 */

if (! defined('ABSPATH')) {
    exit;
}

if (! defined('PODIFY_EVENTS_PATH')) {
    define('PODIFY_EVENTS_PATH', plugin_dir_path(__FILE__));
}
if (! defined('PODIFY_EVENTS_URL')) {
    define('PODIFY_EVENTS_URL', plugin_dir_url(__FILE__));
}
if (! defined('PODIFY_EVENTS_VERSION')) {
    $base = '1.0.0';
    if (function_exists('get_file_data')) {
        $plugin_data = get_file_data(__FILE__, ['Version' => 'Version'], 'plugin');
        if (! empty($plugin_data['Version'])) {
            $base = $plugin_data['Version'];
        }
    }
    define('PODIFY_EVENTS_VERSION', $base);
}

if (! function_exists('podify_events_auto_update_header_version')) {
    function podify_events_auto_update_header_version()
    {
        if (! defined('PODIFY_EVENTS_VERSION')) return;
        $stamp = substr(PODIFY_EVENTS_VERSION, strrpos(PODIFY_EVENTS_VERSION, '.') + 1);
        $prev  = get_transient('podify_events_version_written');
        if ($prev && $prev === $stamp) return;
        $file = __FILE__;
        if (! is_readable($file) || ! is_writable($file)) {
            set_transient('podify_events_version_written', $stamp, HOUR_IN_SECONDS);
            return;
        }
        $data = @file_get_contents($file);
        if (! $data) {
            set_transient('podify_events_version_written', $stamp, HOUR_IN_SECONDS);
            return;
        }
        $pattern = '/(\n \* Version:\s*)([^\r\n]+)/';
        $has = preg_match($pattern, $data, $m);
        if (! $has) {
            set_transient('podify_events_version_written', $stamp, HOUR_IN_SECONDS);
            return;
        }
        $current = $m[2];
        if ($current === PODIFY_EVENTS_VERSION) {
            set_transient('podify_events_version_written', $stamp, HOUR_IN_SECONDS);
            return;
        }
        $updated = preg_replace($pattern, '\1' . PODIFY_EVENTS_VERSION, $data, 1);
        if ($updated && $updated !== $data) {
            @file_put_contents($file, $updated);
            set_transient('podify_events_version_written', $stamp, HOUR_IN_SECONDS);
        }
    }
    add_action('init', 'podify_events_auto_update_header_version', 5);
}


function podify_events_load_textdomain()
{
    load_plugin_textdomain(
        'podify-events',
        false,
        trailingslashit(dirname(plugin_basename(__FILE__))) . 'languages/'
    );
}
add_action('init', 'podify_events_load_textdomain', 20);

function podify_events_register_image_sizes()
{
    add_image_size('podify_events_card', 442, 277, true);
}
add_action('after_setup_theme', 'podify_events_register_image_sizes');

$inc_files = [
    PODIFY_EVENTS_PATH . 'inc/post-types/class-podify-events-cpt.php',
    PODIFY_EVENTS_PATH . 'inc/admin/class-podify-events-admin.php',
    PODIFY_EVENTS_PATH . 'inc/admin/class-podify-events-meta.php',
    PODIFY_EVENTS_PATH . 'inc/widgets/class-podify-events-widget-styles.php',
    PODIFY_EVENTS_PATH . 'inc/updater/class-podify-events-github-updater.php',
];

foreach ($inc_files as $inc) {
    if (file_exists($inc)) {
        require_once $inc;
    } else {
        error_log('[Podify Events] Missing include: ' . $inc);
    }
}

function podify_events_activate()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'podify_events';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        post_id bigint(20) unsigned NOT NULL,
        event_date datetime DEFAULT NULL,
        event_location varchar(255) DEFAULT '',
        organizer varchar(255) DEFAULT '',
        created_at timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY post_id (post_id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    // Register CPT if class file exists
    if (file_exists(PODIFY_EVENTS_PATH . 'inc/post-types/class-podify-events-cpt.php')) {
        if (! class_exists('Podify_Events_CPT')) {
            require_once PODIFY_EVENTS_PATH . 'inc/post-types/class-podify-events-cpt.php';
        }
        if (class_exists('Podify_Events_CPT')) {
            new Podify_Events_CPT();
        }
    }

    flush_rewrite_rules();
    update_option('podify_events_version', '1.0.0');
    update_option('podify_events_db_version', '1.0');
}
register_activation_hook(__FILE__, 'podify_events_activate');

function podify_events_deactivate()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'podify_events_deactivate');

function podify_events_plugins_loaded()
{
    if (class_exists('Podify_Events_CPT')) {
        try {
            new Podify_Events_CPT();
        } catch (Exception $e) {
            error_log('[Podify Events] Error initializing CPT: ' . $e->getMessage());
        }
    }
    if (class_exists('Podify_Events_Admin')) {
        new Podify_Events_Admin();
    }
    if (class_exists('Podify_Events_Meta')) {
        new Podify_Events_Meta();
    }
}
add_action('plugins_loaded', 'podify_events_plugins_loaded', 20);

function podify_events_register_elementor_widget($widgets_manager)
{
    if (! defined('ELEMENTOR_VERSION') || ! class_exists('\Elementor\Widget_Base')) {
        error_log('[Podify Events] Elementor not present; skipping widget registration.');
        return;
    }

    $widget_file = PODIFY_EVENTS_PATH . 'inc/widgets/class-podify-events-elementor-widget.php';

    if (! file_exists($widget_file)) {
        error_log('[Podify Events] Widget file not found: ' . $widget_file);
        return;
    }

    require_once $widget_file;

    if (class_exists('Podify_Events_Elementor_Widget')) {
        try {
            $widgets_manager->register(new Podify_Events_Elementor_Widget());
            error_log('[Podify Events] Widget registered successfully.');
        } catch (Throwable $t) {
            error_log('[Podify Events] Widget registration failed: ' . $t->getMessage());
        }
    } else {
        error_log('[Podify Events] Class Podify_Events_Elementor_Widget not found after include.');
    }
}
add_action('elementor/widgets/register', 'podify_events_register_elementor_widget');

function podify_events_template_include($template)
{

    if (is_singular('podify_event')) {
        $theme_template  = locate_template('podify-events/single-podify_event.php');
        $plugin_template = PODIFY_EVENTS_PATH . 'templates/single-podify_event.php';

        if ($theme_template) {
            return $theme_template;
        } elseif (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    if (is_post_type_archive('podify_event')) {
        $theme_template  = locate_template('podify-events/archive-podify_event.php');
        $plugin_template = PODIFY_EVENTS_PATH . 'templates/archive-podify_event.php';

        if ($theme_template) {
            return $theme_template;
        } elseif (file_exists($plugin_template)) {
            return $plugin_template;
        }
    }

    return $template;
}
add_filter('template_include', 'podify_events_template_include');

function podify_events_enqueue_front_styles()
{
    if (is_singular('podify_event') || is_post_type_archive('podify_event')) {
        $css_path = PODIFY_EVENTS_PATH . 'assets/css/podify-events.css';
        $css_url  = PODIFY_EVENTS_URL  . 'assets/css/podify-events.css';
        if (file_exists($css_path)) {
            if (! wp_style_is('podify-events-css', 'enqueued')) {
                wp_enqueue_style('podify-events-css', $css_url, [], filemtime($css_path));
            }
        }
    }
}
add_action('wp_enqueue_scripts', 'podify_events_enqueue_front_styles');

add_action('wp_enqueue_scripts', function () {
    wp_deregister_style('podify-events-css');
}, 1);

function podify_events_admin_notices()
{
    if (! class_exists('\\Elementor\\Plugin')) {
        echo '<div class="notice notice-warning is-dismissible">
            <p><strong>Podify Events:</strong> Elementor is not active. The events widget will not be available.</p>
        </div>';
    }
}
add_action('admin_notices', 'podify_events_admin_notices');
// AJAX: Event Details Popover
add_action('wp_ajax_podify_event_details', 'podify_events_ajax_event_details');
add_action('wp_ajax_nopriv_podify_event_details', 'podify_events_ajax_event_details');
function podify_events_ajax_event_details()
{
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    if (! $id) {
        wp_send_json_error('Invalid ID');
    }
    $post = get_post($id);
    if (! $post || $post->post_type !== 'podify_event') {
        wp_send_json_error('Not found');
    }
    $date_start = get_post_meta($id, '_podify_event_date_start', true);
    $date_end   = get_post_meta($id, '_podify_event_date_end', true);
    $date = $date_start ? $date_start : get_post_meta($id, '_podify_event_date', true);
    $time = get_post_meta($id, '_podify_event_time', true);
    $address = get_post_meta($id, '_podify_event_address', true);
    $map_ifr = get_post_meta($id, '_podify_event_map_iframe', true);
    $btn_on  = get_post_meta($id, '_podify_event_button_enabled', true);
    $btn_url = get_post_meta($id, '_podify_event_button_url', true);
    $btn_lbl = get_post_meta($id, '_podify_event_button_label', true);
    $thumb = has_post_thumbnail($id) ? get_the_post_thumbnail_url($id, 'large') : (defined('PODIFY_EVENTS_URL') ? PODIFY_EVENTS_URL . 'assets/img/event-placeholder.png' : '');
    ob_start();
    echo '<div class="podify-popover">';
    echo '<div class="podify-popover__left">';
    if ($thumb) echo '<img src="' . esc_url($thumb) . '" alt="" />';
    if ($map_ifr) {
        $allowed = ['iframe' => ['src' => true, 'width' => true, 'height' => true, 'style' => true, 'loading' => true, 'referrerpolicy' => true, 'allowfullscreen' => true]];
        echo '<div class="podify-popover__map">' . wp_kses($map_ifr, $allowed) . '</div>';
    }
    echo '</div>';
    echo '<div class="podify-popover__right">';
    echo '<h2 class="podify-popover__title">' . esc_html(get_the_title($id)) . '</h2>';
    echo '<div class="podify-event-meta">';
    if ($date) {
        $human = '';
        if ($date_end && $date_end !== $date) {
            $human = date_i18n('F j', strtotime($date)) . '–' . date_i18n('j Y', strtotime($date_end));
        } else {
            $human = date_i18n('F j Y', strtotime($date));
        }
        echo '<div class="meta-item"><span class="dashicons dashicons-calendar"></span><span class="meta-text">' . esc_html($human) . '</span></div>';
    } else {
        echo '<div class="meta-item"><span class="dashicons dashicons-calendar"></span><span class="meta-text">' . esc_html__('TBA', 'podify-events') . '</span></div>';
    }
    if ($time) echo '<div class="meta-item"><span class="dashicons dashicons-clock"></span><span class="meta-text">' . esc_html(date_i18n('g:i a', strtotime($time))) . '</span></div>';
    if ($address) echo '<div class="meta-item"><span class="dashicons dashicons-location"></span><span class="meta-text">' . esc_html($address) . '</span></div>';
    echo '</div>';
    $excerpt = get_post_field('post_excerpt', $id);
    $content = apply_filters('the_content', $post->post_content);
    if ($excerpt) echo '<div class="event-excerpt">' . wp_kses_post($excerpt) . '</div>';
    echo '<div class="event-content-full">' . $content . '</div>';
    if ($btn_on && $btn_url) {
        $label = $btn_lbl ? $btn_lbl : __('Learn more', 'podify-events');
        echo '<div class="podify-popover__actions"><a class="podify-read-more" href="' . esc_url($btn_url) . '" target="_blank" rel="noopener">' . esc_html($label) . '</a></div>';
    }
    echo '</div>';
    echo '</div>';
    $html = ob_get_clean();
    wp_send_json_success($html);
}

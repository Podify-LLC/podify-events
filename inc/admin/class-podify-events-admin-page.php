<?php
/**
 * Podify Events Admin Dashboard
 */

if (!defined('ABSPATH')) exit;

class Podify_Events_Admin_Page {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu_page']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function register_menu_page() {
        // Main Menu
        add_menu_page(
            __('Podify Events', 'podify-events'),
            __('Podify Events', 'podify-events'),
            'manage_options',
            'podify-events',
            [$this, 'render_admin_page'],
            'dashicons-calendar-alt',
            25
        );

        // Submenu: General
        add_submenu_page(
            'podify-events',
            __('General', 'podify-events'),
            __('General', 'podify-events'),
            'manage_options',
            'podify-events',
            [$this, 'render_admin_page']
        );

        // Submenu: All Events (Points to CPT list)
        add_submenu_page(
            'podify-events',
            __('All Events', 'podify-events'),
            __('All Events', 'podify-events'),
            'manage_options',
            'edit.php?post_type=podify_event'
        );

        // Submenu: Add New Event (Points to CPT add new)
        add_submenu_page(
            'podify-events',
            __('Add New Event', 'podify-events'),
            __('Add New Event', 'podify-events'),
            'manage_options',
            'post-new.php?post_type=podify_event'
        );

        // Submenu: Settings
        add_submenu_page(
            'podify-events',
            __('Settings', 'podify-events'),
            __('Settings', 'podify-events'),
            'manage_options',
            'podify-events#settings',
            [$this, 'render_admin_page']
        );
    }

    public function enqueue_admin_assets($hook) {
        if ('toplevel_page_podify-events' !== $hook) {
            return;
        }

        wp_enqueue_style(
            'podify-events-admin-css',
            PODIFY_EVENTS_URL . 'assets/css/admin.css',
            ['dashicons'],
            PODIFY_EVENTS_VERSION
        );

        wp_enqueue_script(
            'podify-events-admin-js',
            PODIFY_EVENTS_URL . 'assets/js/admin.js',
            ['jquery'],
            PODIFY_EVENTS_VERSION,
            true
        );

        wp_localize_script(
            'podify-events-admin-js',
            'podifyEventsAdmin',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('podify_events_admin'),
            ]
        );
    }

    public function render_admin_page() {
        $version = PODIFY_EVENTS_VERSION;
        
        // Query Upcoming Events (Start date in the future)
        $upcoming_query = new WP_Query([
            'post_type'      => 'podify_event',
            'posts_per_page' => 3,
            'meta_key'       => '_podify_event_date_start',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => [
                [
                    'key'     => '_podify_event_date_start',
                    'value'   => date('Y-m-d'),
                    'compare' => '>',
                    'type'    => 'DATE',
                ],
            ],
        ]);

        // Query Current Events (Today's date between start and end)
        $today = date('Y-m-d');
        $current_query = new WP_Query([
            'post_type'      => 'podify_event',
            'posts_per_page' => 3,
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => '_podify_event_date_start',
                    'value'   => $today,
                    'compare' => '<=',
                    'type'    => 'DATE',
                ],
                [
                    'key'     => '_podify_event_date_end',
                    'value'   => $today,
                    'compare' => '>=',
                    'type'    => 'DATE',
                ],
            ],
        ]);
        ?>
        <div class="seic-admin-wrapper sidebar-layout">
            <aside class="seic-sidebar">
                <div class="seic-sidebar-header">
                    <div class="seic-logo-container">
                        <img src="<?php echo PODIFY_EVENTS_URL . 'assets/images/logo_cropped.png'; ?>" alt="Podify Events">
                    </div>
                    <div class="seic-unified-badge">
                        <span class="seic-v-text">v<?php echo esc_html($version); ?></span>
                        <span class="seic-f-text">PRO</span>
                    </div>
                </div>
                
                <nav class="seic-nav-vertical">
                    <a href="#dashboard" class="seic-nav-item active" data-tab="dashboard">
                        <span class="dashicons dashicons-dashboard"></span>
                        <span class="seic-nav-text">Dashboard</span>
                    </a>
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=podify_event')); ?>" class="seic-nav-item">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <span class="seic-nav-text">All Events</span>
                    </a>
                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=podify_event')); ?>" class="seic-nav-item">
                        <span class="dashicons dashicons-plus"></span>
                        <span class="seic-nav-text">Add New</span>
                    </a>
                    <a href="#features" class="seic-nav-item" data-tab="features">
                        <span class="dashicons dashicons-star-filled"></span>
                        <span class="seic-nav-text">Features</span>
                    </a>
                    <a href="#settings" class="seic-nav-item" data-tab="settings">
                        <span class="dashicons dashicons-admin-settings"></span>
                        <span class="seic-nav-text">Settings</span>
                    </a>
                    <a href="#changelog" class="seic-nav-item" data-tab="changelog">
                        <span class="dashicons dashicons-format-aside"></span>
                        <span class="seic-nav-text">Changelog</span>
                    </a>
                </nav>
            </aside>

            <main class="seic-main-content">
                <!-- Dashboard Tab -->
                <div id="seic-tab-dashboard" class="seic-tab-content active">
                    <div class="seic-banner">
                        <h1 class="seic-banner-title">
                            Welcome to Podify Events Pro
                        </h1>
                        <p class="seic-banner-text">The professional solution for managing and displaying events in WordPress. Create, organize, and showcase your events with a modern Elementor-powered experience.</p>
                        <div class="seic-banner-actions">
                            <a href="<?php echo esc_url(admin_url('post-new.php?post_type=podify_event')); ?>" class="seic-btn seic-btn-primary">Create Your First Event</a>
                        </div>
                    </div>

                    <div class="seic-grid">
                        <!-- Updater Status Card -->
                        <div class="seic-card">
                            <div class="seic-card-header">
                                <span class="dashicons dashicons-update"></span>
                                <h3>Updater Status</h3>
                            </div>
                            <div class="seic-card-body centered">
                                <div class="seic-status-badge success">UP TO DATE</div>
                                <p>Currently running v<?php echo esc_html($version); ?></p>
                                <a href="#" class="seic-btn seic-btn-outline icon-btn seic-btn-check-update">
                                    <span class="dashicons dashicons-update"></span>
                                    Check Now
                                </a>
                            </div>
                        </div>

                        <!-- Key Features Card -->
                        <div class="seic-card">
                            <div class="seic-card-header">
                                <span class="dashicons dashicons-yes"></span>
                                <h3>Key Features</h3>
                            </div>
                            <div class="seic-card-body">
                                <ul class="seic-feature-list green-checks">
                                    <li><span class="dashicons dashicons-yes"></span> Elementor Widget Integration</li>
                                    <li><span class="dashicons dashicons-yes"></span> Touch-Enabled Carousels</li>
                                    <li><span class="dashicons dashicons-yes"></span> Fully Responsive Layouts</li>
                                    <li><span class="dashicons dashicons-yes"></span> Multi-day Event Support</li>
                                    <li><span class="dashicons dashicons-yes"></span> Automated GitHub Updates</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Quick Actions Card -->
                        <div class="seic-card">
                            <div class="seic-card-header">
                                <span class="dashicons dashicons-admin-tools"></span>
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="seic-card-body">
                                <p class="small-text">Get started quickly with these common tasks:</p>
                                <div class="seic-quick-actions-grid">
                                    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=podify_event')); ?>" class="seic-action-box">
                                        <span class="dashicons dashicons-plus"></span>
                                        <span>Add New</span>
                                    </a>
                                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=podify_event')); ?>" class="seic-action-box">
                                        <span class="dashicons dashicons-calendar-alt"></span>
                                        <span>All Events</span>
                                    </a>
                                    <a href="#features" class="seic-action-box" data-tab="features">
                                        <span class="dashicons dashicons-star-filled"></span>
                                        <span>Features</span>
                                    </a>
                                    <a href="#settings" class="seic-action-box" data-tab="settings">
                                        <span class="dashicons dashicons-admin-settings"></span>
                                        <span>Settings</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Current Events Card -->
                        <div class="seic-card">
                            <div class="seic-card-header">
                                <span class="dashicons dashicons-clock"></span>
                                <h3>Current Events</h3>
                            </div>
                            <div class="seic-card-body">
                                <?php if ($current_query->have_posts()) : ?>
                                    <ul class="seic-event-mini-list">
                                        <?php while ($current_query->have_posts()) : $current_query->the_post(); 
                                            $custom_link = get_post_meta(get_the_ID(), '_podify_event_button_url', true);
                                            $event_link = !empty($custom_link) ? $custom_link : get_permalink();
                                        ?>
                                            <li>
                                                <a href="<?php echo esc_url($event_link); ?>" class="seic-event-item-link" <?php echo !empty($custom_link) ? 'target="_blank" rel="noopener"' : ''; ?>>
                                                    <span class="seic-event-title"><?php the_title(); ?></span>
                                                    <span class="seic-event-date"><?php echo esc_html(get_post_meta(get_the_ID(), '_podify_event_date_start', true)); ?></span>
                                                </a>
                                            </li>
                                        <?php endwhile; wp_reset_postdata(); ?>
                                    </ul>
                                <?php else : ?>
                                    <p class="seic-no-events">No events happening today.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Upcoming Events Card -->
                        <div class="seic-card">
                            <div class="seic-card-header">
                                <span class="dashicons dashicons-calendar-alt"></span>
                                <h3>Upcoming Events</h3>
                            </div>
                            <div class="seic-card-body">
                                <?php if ($upcoming_query->have_posts()) : ?>
                                    <ul class="seic-event-mini-list">
                                        <?php while ($upcoming_query->have_posts()) : $upcoming_query->the_post(); 
                                            $custom_link = get_post_meta(get_the_ID(), '_podify_event_button_url', true);
                                            $event_link = !empty($custom_link) ? $custom_link : get_permalink();
                                        ?>
                                            <li>
                                                <a href="<?php echo esc_url($event_link); ?>" class="seic-event-item-link" <?php echo !empty($custom_link) ? 'target="_blank" rel="noopener"' : ''; ?>>
                                                    <span class="seic-event-title"><?php the_title(); ?></span>
                                                    <span class="seic-event-date"><?php echo esc_html(get_post_meta(get_the_ID(), '_podify_event_date_start', true)); ?></span>
                                                </a>
                                            </li>
                                        <?php endwhile; wp_reset_postdata(); ?>
                                    </ul>
                                <?php else : ?>
                                    <p class="seic-no-events">No upcoming events scheduled.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Plugin Details Card -->
                        <div class="seic-card">
                            <div class="seic-card-header">
                                <span class="dashicons dashicons-info"></span>
                                <h3>Plugin Details</h3>
                            </div>
                            <div class="seic-card-body">
                                <div class="seic-details-list">
                                     <p><strong>Version:</strong> <?php echo esc_html($version); ?></p>
                                     <p><strong>Author:</strong> Podify LLC</p>
                                     <p><strong>License:</strong> Pro</p>
                                 </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features Tab -->
                <div id="seic-tab-features" class="seic-tab-content">
                    <div class="seic-section-header">
                        <h2>Podify Events Features</h2>
                        <p>Discover the powerful tools included with Podify Events.</p>
                    </div>
                    <div class="seic-grid">
                        <div class="seic-card">
                            <div class="seic-card-header">
                                <span class="dashicons dashicons-calendar-alt"></span>
                                <h3>Event Management</h3>
                            </div>
                            <div class="seic-card-body">
                                <p>Full control over event dates, times, locations, and organizers. Includes support for multi-day events.</p>
                            </div>
                        </div>
                        <div class="seic-card">
                            <div class="seic-card-header">
                                <span class="dashicons dashicons-layout"></span>
                                <h3>Elementor Widget</h3>
                            </div>
                            <div class="seic-card-body">
                                <p>Highly customizable widget with Carousel, Grid, and List layouts. Touch-enabled and mobile-first.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Tab -->
                <div id="seic-tab-settings" class="seic-tab-content">
                    <div class="seic-section-header">
                        <h2>Plugin Settings</h2>
                        <p>Configure how Podify Events behaves on your site.</p>
                    </div>
                    <div class="seic-card" style="max-width: 600px;">
                        <div class="seic-card-body">
                            <p>Most styling and layout options are available directly within the **Podify Events** Elementor widget controls for maximum flexibility.</p>
                        </div>
                    </div>
                </div>

                <!-- Changelog Tab -->
                <div id="seic-tab-changelog" class="seic-tab-content">
                    <div class="seic-section-header">
                        <h2>Changelog</h2>
                        <p>Track all the latest updates and improvements.</p>
                    </div>
                    <div class="seic-card">
                        <div class="seic-card-body">
                            <?php
                            $changelog_file = PODIFY_EVENTS_PATH . 'CHANGELOG.md';
                            if (file_exists($changelog_file)) {
                                $changelog = file_get_contents($changelog_file);
                                echo '<div class="seic-changelog-content">' . wp_kses_post(nl2br($changelog)) . '</div>';
                            } else {
                                echo '<p>Changelog file not found.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <?php
    }
}

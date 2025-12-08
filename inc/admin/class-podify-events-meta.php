<?php

/**
 * Podify Events – Meta Fields Handler
 *
 * Handles:
 * - Adding meta boxes to event CPT
 * - Saving event date, time, and address
 * - Sanitizing input
 * - Providing helper methods
 */

if (! defined('ABSPATH')) exit;

class Podify_Events_Meta
{

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'register_meta_box']);
        add_action('save_post_podify_event', [$this, 'save_meta']);
    }

    /**
     * Register the meta box
     */
    public function register_meta_box()
    {
        add_meta_box(
            'podify_event_details',
            __('Event Details', 'podify-events'),
            [$this, 'render_meta_box'],
            'podify_event',
            'normal',
            'default'
        );
    }

    /**
     * Render meta fields
     */
    public function render_meta_box($post)
    {
        wp_nonce_field('podify_event_meta_nonce', 'podify_event_meta_nonce_field');

        $date     = get_post_meta($post->ID, '_podify_event_date', true);
        $date_start = get_post_meta($post->ID, '_podify_event_date_start', true);
        $date_end   = get_post_meta($post->ID, '_podify_event_date_end', true);
        $time     = get_post_meta($post->ID, '_podify_event_time', true);
        $address  = get_post_meta($post->ID, '_podify_event_address', true);
        $map_ifr  = get_post_meta($post->ID, '_podify_event_map_iframe', true);
        $btn_on   = get_post_meta($post->ID, '_podify_event_button_enabled', true);
        $btn_url  = get_post_meta($post->ID, '_podify_event_button_url', true);
        $btn_lbl  = get_post_meta($post->ID, '_podify_event_button_label', true);
        $excerpt  = get_post_field('post_excerpt', $post->ID);

?>
        <style>
            .podify-field {
                margin-bottom: 16px;
                position: relative;
            }

            .podify-row {
                display: flex;
                gap: 16px;
                align-items: flex-end;
            }

            .podify-col {
                flex: 1 1 0;
            }

            .podify-label {
                font-weight: 600;
                margin-bottom: 6px;
                display: block;
            }

            .podify-input {
                width: 100%;
                padding: 10px 12px;
                border: 1px solid #ccd0d4;
                border-radius: 6px;
                box-shadow: 0 1px 0 rgba(0, 0, 0, 0.04) inset;
            }

            .podify-input:focus {
                outline: none;
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
            }

            .podify-help {
                font-size: 12px;
                color: #646970;
                margin-top: 6px;
                display: block;
            }
        </style>

        <div class="podify-row">
            <div class="podify-field podify-col">
                <label class="podify-label"><?php _e('Start Date', 'podify-events'); ?></label>
                <input type="date" id="podify_event_date_start" name="podify_event_date_start" class="podify-input"
                    value="<?php echo esc_attr($date_start ? $date_start : $date); ?>" placeholder="<?php esc_attr_e('YYYY-MM-DD', 'podify-events'); ?>" title="<?php esc_attr_e('YYYY-MM-DD', 'podify-events'); ?>">
            </div>
            <div class="podify-field podify-col">
                <label class="podify-label"><?php _e('End Date', 'podify-events'); ?></label>
                <input type="date" id="podify_event_date_end" name="podify_event_date_end" class="podify-input"
                    value="<?php echo esc_attr($date_end); ?>" placeholder="<?php esc_attr_e('YYYY-MM-DD', 'podify-events'); ?>" title="<?php esc_attr_e('YYYY-MM-DD', 'podify-events'); ?>">
            </div>
            <div class="podify-field podify-col">
                <label class="podify-label"><?php _e('Event Time', 'podify-events'); ?></label>
                <input type="time" id="podify_event_time" name="podify_event_time" class="podify-input"
                    value="<?php echo esc_attr($time); ?>" placeholder="<?php esc_attr_e('HH:MM', 'podify-events'); ?>" title="<?php esc_attr_e('HH:MM', 'podify-events'); ?>">
            </div>
        </div>

        <div class="podify-field">
            <label class="podify-label"><?php _e('Event Location / Address', 'podify-events'); ?></label>
            <input type="text" name="podify_event_address" class="podify-input"
                value="<?php echo esc_attr($address); ?>" placeholder="<?php _e('Enter event address', 'podify-events'); ?>">
            <span class="podify-help"><?php _e('Example: 123 Main St, City, Country', 'podify-events'); ?></span>
        </div>

        <div class="podify-field">
            <label class="podify-label"><?php _e('Map Embed (Google Maps iframe)', 'podify-events'); ?></label>
            <textarea name="podify_event_map_iframe" class="podify-input" rows="4" placeholder="<?php esc_attr_e('Paste Google Maps iframe code here', 'podify-events'); ?>"><?php echo esc_textarea($map_ifr); ?></textarea>
            <span class="podify-help"><?php _e('To get an iframe from Google Maps: open the location, click Share → Embed a map → copy the iframe code.', 'podify-events'); ?></span>
            <div class="podify-map-preview" style="margin-top:10px; background:#f0f2f4; border:1px dashed #ccd0d4; border-radius:6px; overflow:hidden;">
                <?php if (! empty($map_ifr)) {
                    $allowed = ['iframe' => ['src' => true, 'width' => true, 'height' => true, 'style' => true, 'loading' => true, 'referrerpolicy' => true, 'allowfullscreen' => true]];
                    echo wp_kses($map_ifr, $allowed);
                } else { ?>
                    <div style="padding:24px; text-align:center; color:#6b7280;">
                        <?php _e('Map preview will appear here after you paste an iframe.', 'podify-events'); ?>
                    </div>
                <?php } ?>
            </div>
            <script>
                (function() {
                    var t = document.querySelector('textarea[name="podify_event_map_iframe"]');
                    var p = t ? t.parentElement.querySelector('.podify-map-preview') : null;
                    if (!t || !p) return;

                    function copyAttr(srcEl, dstEl, name) {
                        var v = srcEl.getAttribute(name);
                        if (v !== null) dstEl.setAttribute(name, v);
                    }

                    function update() {
                        var html = t.value || '';
                        var temp = document.createElement('div');
                        temp.innerHTML = html;
                        var ifr = temp.querySelector('iframe');
                        p.innerHTML = '';
                        if (ifr) {
                            var el = document.createElement('iframe');
                            ['src', 'width', 'height', 'style', 'loading', 'referrerpolicy', 'allowfullscreen'].forEach(function(a) {
                                copyAttr(ifr, el, a);
                            });
                            el.style.display = 'block';
                            el.style.width = el.getAttribute('width') ? el.style.width : '100%';
                            el.setAttribute('height', el.getAttribute('height') || '300');
                            p.appendChild(el);
                        } else {
                            var msg = document.createElement('div');
                            msg.style.padding = '24px';
                            msg.style.textAlign = 'center';
                            msg.style.color = '#6b7280';
                            msg.textContent = '<?php echo esc_js(__('Map preview will appear here after you paste an iframe.', 'podify-events')); ?>';
                            p.appendChild(msg);
                        }
                    }
                    t.addEventListener('input', update);
                    t.addEventListener('change', update);
                })();
            </script>
        </div>

        <div class="podify-row">
            <div class="podify-field podify-col">
                <label class="podify-label"><?php _e('Action Button Link (URL)', 'podify-events'); ?></label>
                <input type="url" name="podify_event_button_url" class="podify-input" value="<?php echo esc_attr($btn_url); ?>" placeholder="https://example.com">
                <span class="podify-help"><?php _e('Provide a full URL for the action button', 'podify-events'); ?></span>
            </div>
            <div class="podify-field podify-col">
                <label class="podify-label"><?php _e('Action Button Label', 'podify-events'); ?></label>
                <input type="text" name="podify_event_button_label" class="podify-input" value="<?php echo esc_attr($btn_lbl); ?>" placeholder="<?php esc_attr_e('Learn more', 'podify-events'); ?>">
                <span class="podify-help"><?php _e('Optional. Defaults to "Learn more".', 'podify-events'); ?></span>
            </div>
        </div>

        <div class="podify-field">
            <label class="podify-label"><?php _e('Excerpt', 'podify-events'); ?></label>
            <textarea name="podify_event_excerpt" class="podify-input" rows="3" placeholder="<?php esc_attr_e('Short summary for this event', 'podify-events'); ?>"><?php echo esc_textarea($excerpt); ?></textarea>
        </div>

<?php
    }

    /**
     * Save meta fields
     */
    public function save_meta($post_id)
    {
        // Nonce check
        if (
            ! isset($_POST['podify_event_meta_nonce_field']) ||
            ! wp_verify_nonce($_POST['podify_event_meta_nonce_field'], 'podify_event_meta_nonce')
        ) {
            return;
        }

        // Autosave? Bail.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        // Permissions?
        if (! current_user_can('edit_post', $post_id)) return;

        // Sanitize + Save
        $date_start = isset($_POST['podify_event_date_start']) ? sanitize_text_field($_POST['podify_event_date_start']) : '';
        $date_end   = isset($_POST['podify_event_date_end']) ? sanitize_text_field($_POST['podify_event_date_end']) : '';
        $time     = isset($_POST['podify_event_time']) ? sanitize_text_field($_POST['podify_event_time']) : '';
        $address  = isset($_POST['podify_event_address']) ? sanitize_text_field($_POST['podify_event_address']) : '';
        $map_ifr  = isset($_POST['podify_event_map_iframe']) ? wp_kses_post($_POST['podify_event_map_iframe']) : '';
        $btn_on   = isset($_POST['podify_event_button_enabled']) ? '1' : '';
        $btn_url  = isset($_POST['podify_event_button_url']) ? esc_url_raw($_POST['podify_event_button_url']) : '';
        $btn_lbl  = isset($_POST['podify_event_button_label']) ? sanitize_text_field($_POST['podify_event_button_label']) : '';
        $use_modal = isset($_POST['podify_event_use_modal']) ? '1' : '';
        $excerpt  = isset($_POST['podify_event_excerpt']) ? sanitize_textarea_field($_POST['podify_event_excerpt']) : '';

        if ($date_start && $date_end && strtotime($date_end) < strtotime($date_start)) {
            $date_end = $date_start;
        }
        update_post_meta($post_id, '_podify_event_date_start', $date_start);
        update_post_meta($post_id, '_podify_event_date_end', $date_end);
        update_post_meta($post_id, '_podify_event_date', $date_start);
        update_post_meta($post_id, '_podify_event_time', $time);
        update_post_meta($post_id, '_podify_event_address', $address);
        update_post_meta($post_id, '_podify_event_map_iframe', $map_ifr);
        update_post_meta($post_id, '_podify_event_button_enabled', $btn_on);
        update_post_meta($post_id, '_podify_event_button_url', $btn_url);
        update_post_meta($post_id, '_podify_event_button_label', $btn_lbl);
        update_post_meta($post_id, '_podify_event_use_modal', $use_modal);

        if ('' !== $excerpt) {
            remove_action('save_post_podify_event', [$this, 'save_meta']);
            wp_update_post(['ID' => $post_id, 'post_excerpt' => $excerpt]);
            add_action('save_post_podify_event', [$this, 'save_meta']);
        }
    }

    /**
     * Helper: Get full formatted datetime
     */
    public static function get_event_datetime($post_id)
    {
        $date_start = get_post_meta($post_id, '_podify_event_date_start', true);
        $date_end   = get_post_meta($post_id, '_podify_event_date_end', true);
        $date = $date_start ? $date_start : get_post_meta($post_id, '_podify_event_date', true);
        $time = get_post_meta($post_id, '_podify_event_time', true);

        if (! $date) return '';

        if ($time) {
            return date_i18n('F j Y', strtotime($date . ' ' . $time)) . ' • ' . date_i18n('g:i a', strtotime($date . ' ' . $time));
        }

        if ($date_start && $date_end) {
            if ($date_start === $date_end) return date_i18n('F j Y', strtotime($date_start));
            $fmt_start = date_i18n('F j', strtotime($date_start));
            $fmt_end   = date_i18n('j Y', strtotime($date_end));
            return $fmt_start . '—' . $fmt_end;
        }
        return $date ? date_i18n('F j Y', strtotime($date)) : '';
    }

    /**
     * Helper: Check if button is enabled for event
     */
    public static function is_button_enabled($post_id)
    {
        return get_post_meta($post_id, '_podify_event_button_enabled', true) === '1';
    }

    /**
     * Helper: Get button URL
     */
    public static function get_button_url($post_id)
    {
        return get_post_meta($post_id, '_podify_event_button_url', true);
    }

    /**
     * Helper: Get button label
     */
    public static function get_button_label($post_id)
    {
        $label = get_post_meta($post_id, '_podify_event_button_label', true);
        return !empty($label) ? $label : __('Learn more', 'podify-events');
    }

    /**
     * Helper: Check if modal should be used
     */
    public static function should_use_modal($post_id)
    {
        return get_post_meta($post_id, '_podify_event_use_modal', true) === '1';
    }
}

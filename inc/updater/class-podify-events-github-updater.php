<?php
/**
 * GitHub Updater for Podify Events
 * 
 * Turns a GitHub repository into a WordPress update server.
 * Supports both public and private repositories.
 */

namespace Podify;

if (!defined('ABSPATH')) exit;

class Github_Updater {

    private $file;
    private $user;
    private $repo;
    private $token_constant;
    private $slug;
    private $basename;

    /**
     * Constructor
     */
    public function __construct($file, $user, $repo, $token_constant = '') {
        $this->file = $file;
        $this->user = $user;
        $this->repo = $repo;
        $this->token_constant = $token_constant;
        $this->basename = plugin_basename($file); // e.g., podify-events/podify-events.php
        $this->slug = dirname($this->basename);   // e.g., podify-events

        // Hook into WP Updates
        add_filter('pre_set_site_transient_update_plugins', [$this, 'check_for_update']);
        
        // Add plugin info (for "View details" link)
        add_filter('plugins_api', [$this, 'plugin_popup_info'], 10, 3);

        // Private Repo Support: Intercept outgoing HTTP requests to inject Bearer token
        add_filter('http_request_args', [$this, 'inject_github_token'], 10, 2);

        // Folder Normalization: Ensure extracted folder matches plugin slug
        add_filter('upgrader_source_selection', [$this, 'normalize_folder_name'], 10, 4);

        // Manual Update Check AJAX
        add_action('wp_ajax_podify_check_update', [$this, 'ajax_check_update']);
    }

    /**
     * AJAX handler for manual update check
     */
    public function ajax_check_update() {
        check_ajax_referer('podify_events_admin', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized']);
        }

        $latest_release = $this->get_latest_release();
        if (!$latest_release) {
            $token = $this->get_token();
            $msg = 'Could not connect to GitHub.';
            if (!$token) {
                $msg .= ' If the repository is private, please set your GitHub token in the plugin settings or README instructions.';
            }
            wp_send_json_error(['message' => $msg]);
        }

        $new_version = ltrim($latest_release['tag_name'], 'v');
        $current_version = PODIFY_EVENTS_VERSION;
        $is_update_available = version_compare($new_version, $current_version, '>');

        wp_send_json_success([
            'latest' => $new_version,
            'current' => $current_version,
            'is_available' => $is_update_available,
            'message' => $is_update_available ? 'New version available!' : 'You are up to date!'
        ]);
    }

    /**
     * Check for updates from GitHub
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }

        $latest_release = $this->get_latest_release();
        if (!$latest_release) {
            return $transient;
        }

        $current_version = $transient->checked[$this->basename];
        $new_version = ltrim($latest_release['tag_name'], 'v');

        if (version_compare($new_version, $current_version, '>')) {
            $obj = new \stdClass();
            $obj->slug = $this->slug;
            $obj->plugin = $this->basename;
            $obj->new_version = $new_version;
            $obj->url = "https://github.com/{$this->user}/{$this->repo}";
            $obj->package = $latest_release['zipball_url'];
            
            $transient->response[$this->basename] = $obj;
        }

        return $transient;
    }

    /**
     * Get latest release data from GitHub API
     */
    private function get_latest_release() {
        $url = "https://api.github.com/repos/{$this->user}/{$this->repo}/releases/latest";
        
        $args = [
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url('/')
            ]
        ];

        $token = $this->get_token();
        if ($token) {
            $args['headers']['Authorization'] = "Bearer $token";
        }

        $response = wp_remote_get($url, $args);
        if (is_wp_error($response)) {
            error_log('[Podify Events] GitHub API Error: ' . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code !== 200) {
            error_log('[Podify Events] GitHub API Error Code: ' . $code . ' for URL: ' . $url);
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return (isset($data['tag_name'])) ? $data : false;
    }

    /**
     * Plugin popup info (View details)
     */
    public function plugin_popup_info($result, $action, $args) {
        if ($action !== 'plugin_information') return $result;
        if ($args->slug !== $this->slug) return $result;

        $latest_release = $this->get_latest_release();
        if (!$latest_release) return $result;

        $res = new \stdClass();
        $res->name = 'Podify Events';
        $res->slug = $this->slug;
        $res->version = ltrim($latest_release['tag_name'], 'v');
        $res->author = '<a href="https://github.com/'.$this->user.'">'.$this->user.'</a>';
        $res->homepage = "https://github.com/{$this->user}/{$this->repo}";
        $res->download_link = $latest_release['zipball_url'];
        $res->sections = [
            'description' => $latest_release['body'] ?? 'Podify Events custom event management system.',
            'changelog'   => 'Check the repository for latest changes.'
        ];

        return $res;
    }

    /**
     * Inject GitHub token for private repo downloads
     */
    public function inject_github_token($args, $url) {
        // Only inject if it's a GitHub request to our repo
        if (strpos($url, 'api.github.com') !== false && strpos($url, "{$this->user}/{$this->repo}") !== false) {
            $token = $this->get_token();
            if ($token) {
                $args['headers']['Authorization'] = "Bearer $token";
            }
        }
        return $args;
    }

    /**
     * Normalize the folder name after extraction
     */
    public function normalize_folder_name($source, $remote_source, $upgrader, $hook_extra) {
        // GitHub zipballs usually have names like user-repo-hash or repo-tag
        // We look for the repo name in the source folder
        if (strpos(basename($source), $this->repo) !== false) {
            $new_source = trailingslashit(dirname($source)) . $this->slug . '/';
            if (rename($source, $new_source)) {
                return $new_source;
            }
        }
        return $source;
    }

    /**
     * Get the token from constant or option
     */
    private function get_token() {
        if ($this->token_constant && defined($this->token_constant)) {
            return constant($this->token_constant);
        }
        
        // Also check for option as fallback (as mentioned in README)
        $token = get_option('podify_github_token');
        if ($token) {
            return $token;
        }

        return false;
    }
}

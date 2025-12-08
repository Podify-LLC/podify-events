<?php
if (! defined('ABSPATH')) { exit; }

class Podify_Events_GitHub_Updater {
    public static function bootstrap() {
        add_filter('pre_set_site_transient_update_plugins', [__CLASS__, 'check'], 10, 1);
        add_filter('plugins_api', [__CLASS__, 'info'], 10, 3);
        add_filter('upgrader_source_selection', [__CLASS__, 'normalize_source'], 10, 4);
    }

    protected static function get_repo() {
        $repo = defined('PODIFY_EVENTS_GITHUB_REPO') ? PODIFY_EVENTS_GITHUB_REPO : '';
        $repo = apply_filters('podify_events_github_repo', $repo);
        return is_string($repo) ? trim($repo) : '';
    }

    protected static function get_token() {
        $token = defined('PODIFY_EVENTS_GITHUB_TOKEN') ? PODIFY_EVENTS_GITHUB_TOKEN : '';
        $token = apply_filters('podify_events_github_token', $token);
        return is_string($token) ? trim($token) : '';
    }

    protected static function get_branch() {
        $branch = defined('PODIFY_EVENTS_GITHUB_BRANCH') ? PODIFY_EVENTS_GITHUB_BRANCH : 'main';
        $branch = apply_filters('podify_events_github_branch', $branch);
        return is_string($branch) ? trim($branch) : 'main';
    }

    protected static function api_get($url) {
        $args = [
            'timeout' => 15,
            'headers' => [
                'User-Agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url('/'),
                'Accept' => 'application/vnd.github+json',
            ],
        ];
        $token = self::get_token();
        if ($token) { $args['headers']['Authorization'] = 'Bearer ' . $token; }
        $res = wp_remote_get($url, $args);
        if (is_wp_error($res)) { return null; }
        $code = wp_remote_retrieve_response_code($res);
        if ($code !== 200) { return null; }
        $body = wp_remote_retrieve_body($res);
        if (! $body) { return null; }
        $json = json_decode($body, true);
        return is_array($json) ? $json : null;
    }

    protected static function latest_release() {
        $repo = self::get_repo();
        if (! $repo) { return null; }
        $release = self::api_get('https://api.github.com/repos/' . rawurlencode($repo) . '/releases/latest');
        if (is_array($release) && ! empty($release['tag_name'])) {
            $ver = ltrim($release['tag_name'], 'v');
            $zip = isset($release['zipball_url']) ? $release['zipball_url'] : '';
            return ['version' => $ver, 'zip' => $zip, 'url' => 'https://github.com/' . $repo . '/releases/latest'];
        }
        $branch = self::get_branch();
        $tags = self::api_get('https://api.github.com/repos/' . rawurlencode($repo) . '/tags');
        if (is_array($tags) && ! empty($tags[0]['name'])) {
            $ver = ltrim($tags[0]['name'], 'v');
            $zip = 'https://api.github.com/repos/' . $repo . '/zipball/' . urlencode($tags[0]['name']);
            return ['version' => $ver, 'zip' => $zip, 'url' => 'https://github.com/' . $repo . '/tags'];
        }
        $zip = 'https://api.github.com/repos/' . $repo . '/zipball/' . urlencode($branch);
        return ['version' => date('Y.m.d'), 'zip' => $zip, 'url' => 'https://github.com/' . $repo];
    }

    protected static function compare_versions($a, $b) {
        $a = trim((string)$a); $b = trim((string)$b);
        if ($a === $b) { return 0; }
        if (function_exists('version_compare')) { return version_compare($a, $b); }
        return strcmp($a, $b);
    }

    public static function check($transient) {
        if (! is_object($transient)) { $transient = (object) []; }
        $repo = self::get_repo();
        if (! $repo) { return $transient; }
        $latest = self::latest_release();
        if (! $latest || empty($latest['version']) || empty($latest['zip'])) { return $transient; }
        $current = defined('PODIFY_EVENTS_VERSION') ? PODIFY_EVENTS_VERSION : '0.0.0';
        if (self::compare_versions($latest['version'], $current) > 0) {
            $plugin = plugin_basename(PODIFY_EVENTS_PATH . 'podify-events.php');
            $item = new stdClass();
            $item->slug = 'podify-events';
            $item->plugin = $plugin;
            $item->new_version = $latest['version'];
            $item->url = $latest['url'];
            $item->package = $latest['zip'];
            if (! isset($transient->response)) { $transient->response = []; }
            $transient->response[$plugin] = $item;
        }
        return $transient;
    }

    public static function info($res, $action, $args) {
        if ($action !== 'plugin_information') { return $res; }
        if (! isset($args->slug) || $args->slug !== 'podify-events') { return $res; }
        $repo = self::get_repo();
        if (! $repo) { return $res; }
        $latest = self::latest_release();
        $obj = new stdClass();
        $obj->name = 'Podify Events';
        $obj->slug = 'podify-events';
        $obj->version = $latest && ! empty($latest['version']) ? $latest['version'] : (defined('PODIFY_EVENTS_VERSION') ? PODIFY_EVENTS_VERSION : '1.0.0');
        $obj->author = 'Podify';
        $obj->homepage = 'https://github.com/' . $repo;
        $obj->download_link = $latest && ! empty($latest['zip']) ? $latest['zip'] : '';
        $obj->sections = [ 'description' => 'Podify Events' ];
        return $obj;
    }

    public static function normalize_source($source, $remote_source, $upgrader, $hook_extra) {
        $repo = self::get_repo();
        if (! $repo) { return $source; }
        $plugin_dir = dirname(plugin_basename(PODIFY_EVENTS_PATH . 'podify-events.php'));
        $basename = basename($source);
        if (strpos($basename, $plugin_dir) !== false) { return $source; }
        $target = trailingslashit(dirname($source)) . $plugin_dir;
        if (! @rename($source, $target)) { return $source; }
        return $target;
    }
}

add_action('plugins_loaded', ['Podify_Events_GitHub_Updater', 'bootstrap'], 9);


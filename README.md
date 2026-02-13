# Podify Events

A lightweight and modern events management plugin designed for Podify, including GitHub-powered auto-updates for seamless version management.

## Metadata
- Contributors: `johnrodney`, `Aljune`
- Tags: `events`, `booking`, `scheduler`, `calendar`, `podify`
- Requires at least: `5.0`
- Tested up to: `6.7`
- Requires PHP: `7.4`
- Stable tag: `1.0.9`
- License: `GPLv2 or later`
- License URI: `https://www.gnu.org/licenses/gpl-2.0.html`

## Description
Podify Events Pro is a professional event management solution for WordPress, featuring a modern Elementor-powered display and a sleek admin dashboard. It includes a built-in GitHub auto-update mechanism for seamless version management.

### Key Features
- **Modern Pro Dashboard**: A responsive "General" menu with professional layout and branding.
- **Elementor Integration**: Dedicated widget for Carousel, Grid, and List event displays.
- **Comprehensive Management**: Easy control over event dates, times, locations, and organizers.
- **GitHub Auto-Update**: Seamless updates from GitHub (Public or Private repositories).
- **Touch-Enabled**: Mobile-first design using Swiper.js for event carousels.
- **Developer Friendly**: Clean, modular codebase following WordPress best practices.

## GitHub Auto-Update
The plugin uses a self-contained updater located at:

`inc/updater/class-podify-events-github-updater.php`

To enable updates, configure your repository in the main plugin file:

```php
new Podify_Events_GitHub_Updater( __FILE__, 'your-repo-slug', 'main' );
```

## Installation
1. Upload the plugin folder to `wp-content/plugins/`
2. Activate **Podify Events Pro** from the Plugins menu.
3. Go to the **Podify Events Pro > General** menu to explore the dashboard.
4. (Optional) Set your GitHub token:

```php
update_option('podify_github_token', 'ghp_xxxxxxxxx');
```

## Changelog

### 1.0.9
- Upgraded to **Pro Version** branding and features.
- New professional vertical sidebar dashboard layout.
- Refined Elementor widget layouts and responsiveness.
- Enhanced GitHub Updater implementation.
- Modernized UI with unified Pro branding and badges.

### 1.0.0
- Initial stable release.
- Core event management features.
- GitHub auto-update support.

## Upgrade Notice

### 1.0.9
Significant UI update with a new professional Pro dashboard and improved Elementor widget support.

### 1.0.0
First public release.
# podify-events

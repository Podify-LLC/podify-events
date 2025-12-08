# Podify Events

A lightweight and modern events management plugin designed for Podify, including GitHub-powered auto-updates for seamless version management.

## Metadata
- Contributors: `johnrodney`, `Aljune`
- Tags: `events`, `booking`, `scheduler`, `calendar`, `podify`
- Requires at least: `5.0`
- Tested up to: `6.7`
- Requires PHP: `7.4`
- Stable tag: `1.0.0`
- License: `GPLv2 or later`
- License URI: `https://www.gnu.org/licenses/gpl-2.0.html`

## Description
Podify Events lets site owners create, manage, and display events with ease. It includes a built-in GitHub auto-update mechanism so you can push updates to your GitHub repository and deliver them to all WordPress installations using the plugin.

### Key Features
- Add and manage custom events
- Auto-update from GitHub Releases or Tags
- Lightweight GitHub update checker (no external dependencies)
- Optional GitHub token support for private repos
- Simple and clean codebase
- Developer-friendly structure

## GitHub Auto-Update
The plugin can use a lightweight updater located at:

`/includes/github-updater.php`

To enable updates, set your GitHub repository in the main plugin file:

```php
new Podify_GitHub_Updater( __FILE__, 'yourusername/your-repo', 'main' );
```

Alternatively, the built-in updater class is available at:

`inc/updater/class-podify-events-github-updater.php`

Configure via constants or filters if preferred.

## Installation
1. Upload the plugin folder to `wp-content/plugins/`
2. Activate Podify Events from the Plugins menu
3. (Optional) Add your GitHub token in WordPress:

```php
update_option('podify_github_token', 'ghp_xxxxxxxxx');
```

4. Create a GitHub Release (`v1.0.1`, `v1.0.2`, etc.) to trigger updates

## Frequently Asked Questions

### How do auto-updates work?
The plugin checks GitHub for the latest release or tag. If the tag version is higher than the installed version, WordPress will show an update notification.

### Does it work with private repositories?
Yes. Add a GitHub personal access token to WordPress:

```php
update_option('podify_github_token', 'ghp_xxxxxxxx');
```

### Where do I change the GitHub repository?
In `podify-events.php`, update this line:

```php
new Podify_GitHub_Updater( __FILE__, 'yourusername/your-repo', 'main' );
```

### Do I need Git Updater or Plugin Update Checker?
No. This plugin includes its own self-contained updater.

## Changelog

### 1.0.0
- Initial release
- Added GitHub auto-update class
- Core event management structure created

## Upgrade Notice

### 1.0.0
First public release with GitHub auto-update support.
# podify-events

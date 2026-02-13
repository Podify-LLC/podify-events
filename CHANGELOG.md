# Changelog

All notable changes to this project will be documented in this file.

## 1.0.9 – 2026-02-14
- **New Admin Dashboard**: Implemented a modern, responsive "General" dashboard with a horizontal header and unified branding.
- **GitHub Updater Integration**: Added support for automated updates directly from GitHub repositories (Public/Private).
- **Elementor Widget Enhancements**: Fixed carousel layout issues and improved responsiveness across breakpoints.
- **Improved UI/UX**: Added professional badges, linear gradient banners, and streamlined navigation in the admin panel.

## 1.0.8 – 2026-01-29
- Fixed persistent Elementor editor rendering issues by renaming the grid container class to `.podify-events-flex-grid` to avoid conflicts with base styles.
- Updated JS initialization to support the new grid class.
- Ensured backend editor preview matches frontend layout perfectly.

## 1.0.6 – 2026-01-22
- Fixed layout conflicts in Elementor Editor by enforcing Flexbox grid with high-specificity selectors.
- Fixed alignment issues where cards would not center/right align due to CSS Grid override.

## 1.0.5 – 2026-01-22
- Updated Elementor widget layout to use CSS variables for better control and consistency.
- Added Layout Alignment control (Left, Center, Right) for event cards.
- Fixed layout consistency between Editor and Frontend.

## 1.0.4 – 2026-01-22
- Fixed carousel layout: forced equal height for all event cards.
- Fixed button alignment: buttons now consistently align to the bottom of the card, even with varying content length.

## 1.0.3 – 2026-01-22
- Added "TBD" placeholder for events missing Date, Time, or Address information.

## 1.0.2 – 2026-01-22
- Improved CSS layout consistency: cards now have uniform height in grid/list views.
- Fixed action button alignment: buttons are now pushed to the bottom of the card.
- Prevented layout shifts with better meta item sizing.

## 1.0.1 – 2026-01-22
- Added "Enable Custom Button" setting to event meta box.
- Updated Elementor widget to support per-event button labels and URLs (custom button support).

## 1.0.0 – 2025-12-08
- Initial stable version.
- Swiper navigation replaced with custom buttons and API control.
- Editor iframe CSS loaded; duplicate Elementor arrows hidden.
- Single Swiper initialization guard in Elementor edit mode.
- Arrow buttons aligned left/right, vertically centered with overlay row.
- GitHub updater support added.

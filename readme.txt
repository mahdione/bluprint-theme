=== Bluprint Theme ===
Contributors: bluprint
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.3.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom WooCommerce theme for the Bluprint storefront.

== Requirements ==
* WordPress 6.0+
* PHP 7.4+
* WooCommerce
* Rank Math SEO (recommended; breadcrumbs and SEO fallbacks integrate with it)

== Installation ==
1. Back up the current site.
2. Upload the new theme ZIP from Appearance → Themes → Add New → Upload Theme.
3. Replace the existing Bluprint Theme when prompted, or delete the old copy and install the new one. If you see leftover folders named bluprint-theme-xxxx in wp-content/themes, back up the site, switch to another theme temporarily, then delete those leftover folders and keep only bluprint-theme.
4. Keep WooCommerce active. Rank Math is optional; the theme provides SEO fallbacks when no SEO plugin is active.

== GitHub updates ==
1. Push this theme to a GitHub repository (repository root = this theme folder).
2. Open Bluprint → به‌روزرسانی قالب and enter the repository as owner/repository.
3. For a public repository the token is optional. For a private repository, add a fine-grained token with repository read access, or define BLUPRINT_THEME_GITHUB_TOKEN and BLUPRINT_THEME_GITHUB_REPO in wp-config.php.
4. When a new version is ready, update the Version header and push a matching tag, for example:
   git tag v1.2.1
   git push origin main --tags
5. WordPress checks the newest GitHub tag on the Themes screen and in Bluprint → به‌روزرسانی قالب. If a Release contains bluprint-theme.zip it is preferred; otherwise the tag archive is used.

== Changelog ==
= 1.3.3 =
* Skinned every WooCommerce-generated button so no default purple appears in hover, focus, active, disabled or notice states.
* Redesigned the pack metadata into bordered, high-contrast boxes with bold values and responsive columns.
* Rebuilt the core stylesheet bundle and the installation package.

= 1.3.2 =
* Fixed the product add-to-cart button reverting to WooCommerce purple on focus/active.
* Redesigned pack metadata into clear bordered boxes with stronger value weights.
* Added responsive pack metadata columns.
* Rebuilt the installation package.

= 1.3.1 =
* Improved accessible naming for category cards.
* Hardened the shop and product-category templates against old/partial deployments.
* Added SEO/performance fallbacks and a bundled core stylesheet to 1.3.x.
* Rebuilt the installation package.

= 1.3.0 =
* Added theme-level SEO fallbacks: indexability, meta descriptions, Open Graph/Twitter tags, JSON-LD, breadcrumbs and sitemap enablement.
* Fixed the shop and product-category 500 error when WooCommerce helper files are not loaded.
* Added central self-healing includes for partial or stale theme deployments.
* Improved accessibility: color contrast, category link names and minimum touch targets.
* Bundled core CSS, preloaded the local font and skipped unused WooCommerce/block assets on custom pages.
* Rebuilt the installation package.

= 1.2.2 =
* Hardened front-page sections, the footer, single posts and pack pages against partial or renamed theme folders.
* Added self-loading guards for section helpers so a missing include cannot fatal the site.
* Added a front-page fallback loader for top-level inc files.
* Rebuilt the installation package.

= 1.2.1 =
* Switched automatic updates to the newest GitHub tag, with an optional release asset and archive fallback.
* Prevented GitHub API checks on ordinary front-end requests.
* Added ZIP folder normalization for GitHub tag archives.

= 1.2.0 =
* Added GitHub tag/release-based automatic theme updates.
* Added the "به‌روزرسانی قالب" settings screen under the Bluprint menu.
* Added native WordPress theme update integration and version details.
* Added .github/workflows/release.yml to publish an installable ZIP on version tags.
* Added GitHub repository/token settings with transient caching and manual checks.

= 1.1.1 =
* Fixed the "headers already sent" warning caused by leading whitespace in inc/blog-section.php.
* Removed trailing whitespace from PHP templates to keep update installs clean.
* Rebuilt the installation package.

= 1.1.0 =
* Added pack archives, pack category archives, and a richer single pack experience.
* Added pack category and product taxonomy rich-content editors.
* Added single post layout with table of contents and reading controls.
* Added blog archive, contact inbox, About and Contact page templates.
* Improved footer width alignment, cart actions, and English-number formatting.
* Added update-safe loading for banner and content helpers.
* Added missing banner/data helpers to prevent front-end fatal errors after partial uploads.

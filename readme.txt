=== Bluprint Theme ===
Contributors: bluprint
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
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
3. Replace the existing Bluprint Theme when prompted, or delete the old copy and install the new one.
4. Keep WooCommerce and Rank Math active.

== GitHub updates ==
1. Push this theme to a GitHub repository (repository root = this theme folder).
2. Open Bluprint → به‌روزرسانی قالب and enter the repository as owner/repository.
3. For a public repository the token is optional. For a private repository, add a fine-grained token with repository read access, or define BLUPRINT_THEME_GITHUB_TOKEN and BLUPRINT_THEME_GITHUB_REPO in wp-config.php.
4. When a new version is ready, update the Version header and push a matching tag, for example:
   git tag v1.2.0
   git push origin main --tags
5. The GitHub Actions workflow builds bluprint-theme.zip and attaches it to the Release. WordPress will then show the update on the Themes screen or in Bluprint → به‌روزرسانی قالب.

== Changelog ==
= 1.2.0 =
* Added GitHub Releases-based automatic theme updates.
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

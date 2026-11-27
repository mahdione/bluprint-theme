=== Bluprint Theme ===
Contributors: bluprint
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.3.12
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
= 1.3.12 =
* Added a visible fallback link when the eNamad badge image cannot load, so the footer never stays empty.
* The exact eNamad snippet is still printed unchanged above the copyright; the fallback is hidden automatically when the image loads.
* Rebuilt the core stylesheet bundle.

= 1.3.11 =
* Restored the eNamad badge when the saved option is empty by falling back to the required snippet.
* The settings screen now shows the badge code even if an earlier empty save removed it.
* The snippet is still printed exactly as entered, without lazy-loading, resizing or markup changes.

= 1.3.10 =
* The eNamad footer badge now prints the exact snippet entered in settings, without markup rewriting, lazy-loading or dimension changes.
* Removed the fixed badge container so the provider's own markup and scripts control the badge size.
* Rebuilt the core stylesheet bundle.

= 1.3.9 =
* Removed the counter and price strip from the top of pack cards.
* Moved the pack total price into the category row, right-aligned and clearly readable.
* Softened the card tint and applied each pack's own colour to the visit button, button shadow and hover shadow.
* Kept the category chip a single consistent brand colour across every pack.
* Added WCAG-based contrast selection for pack button text, including white icons on dark colours.
* Raised description contrast on tinted card backgrounds.
* Added an eNamad settings section and a lazy-loaded, fixed-size trust badge above the footer copyright.
* Rebuilt the installation package.

= 1.3.8 =
* Kept WooCommerce's screen-reader price text visually hidden even when WooCommerce styles are skipped on a page.
* Hardened the compact card price fallback so hidden sale descriptions can never leak into the card.
* Rebuilt the core stylesheet bundle.

= 1.3.7 =
* Simplified product-card prices: variable products show only the starting price, prefixed with «از».
* Removed the long crossed-out range from card prices so nothing is clipped or unreadable.
* Added a compact discount badge in the top-left corner of discounted product cards, showing the exact or maximum saving.
* Rebuilt the installation package.

= 1.3.6 =
* Removed decorative English eyebrows from the home hero, assurance and pack showcase sections.
* Replaced the About page eyebrow with «درباره ما» and removed numbering from the story and values headings.
* Removed the contact hero eyebrow plus the RESPONSE and C—02 / CONTACT labels.
* Translated the pack archive eyebrow to Persian.
* Made pack and product archive grids step down to two columns at 1180px and one column on very small phones.
* Added a single-column layout for the product cards inside a pack on narrow phones.
* Rebuilt the installation package.

= 1.3.5 =
* Linked the out-of-stock pack warning to the add-to-cart buttons for screen readers.
* Rebuilt the installation package.

= 1.3.4 =
* Added a minimal "view product" action to every product card inside a single pack.
* Out-of-stock pack products now show a clear X mark, a dimmed image and a "ناموجود" badge.
* Added a visible warning when some pack products are unavailable while still allowing the pack to be added with the available items.
* Clarified unavailable item labels and the add-to-cart success message.
* Rebuilt the installation package.

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

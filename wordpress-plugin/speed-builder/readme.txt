=== Speed Builder ===
Contributors: speedbuilder
Tags: page builder, visual editor, drag and drop, landing pages
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A fast, focused WordPress-native visual page builder.

== Description ==

Speed Builder Phase 1 is a small, stable foundation for building WordPress pages visually. It uses normal WordPress pages and stores the JSON document in post meta. The frontend renderer outputs only page content — never editor controls.

== Phase 1 includes ==

* Modern Speed Builder dashboard.
* WordPress page management and direct editor links.
* A full-screen visual editor with desktop, tablet, and mobile canvas modes.
* Section structures with one to four columns.
* Heading, Text, Button, Image, Spacer, and Divider widgets.
* Native WordPress Media Library selection.
* Basic drag and drop, reordering, selection, duplicate, and delete actions.
* Undo/redo, debounced autosave, manual save, preview, and publish.
* Saved JSON templates.
* Secure REST routes using WordPress nonces and capability checks.
* Lightweight frontend renderer and styles.

== Installation ==

1. Upload `speed-builder-phase-1.zip` from the WordPress Plugins > Add New > Upload Plugin screen.
2. Activate Speed Builder.
3. Visit Speed Builder > Pages.
4. Create a page and open it in Speed Builder.

== Data ==

Page documents are stored in `_speed_builder_data` and enabled with `_speed_builder_enabled` post meta. Templates are stored in the `speed_builder_templates` option.

== Changelog ==

= 0.1.0 =
* Initial Phase 1 release.

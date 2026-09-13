<?php
/**
 * Plugin Name: Speed Builder
 * Plugin URI:  https://speedbuilder.local
 * Description: A fast, WordPress-native visual page builder. Phase 1 provides a focused drag-and-drop editor for pages.
 * Version:     0.1.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author:      Speed Builder
 * Text Domain: speed-builder
 * License:     GPL-2.0-or-later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SPEED_BUILDER_VERSION', '0.1.0' );
define( 'SPEED_BUILDER_FILE', __FILE__ );
define( 'SPEED_BUILDER_PATH', plugin_dir_path( __FILE__ ) );
define( 'SPEED_BUILDER_URL', plugin_dir_url( __FILE__ ) );

require_once SPEED_BUILDER_PATH . 'includes/class-assets.php';
require_once SPEED_BUILDER_PATH . 'includes/class-settings.php';
require_once SPEED_BUILDER_PATH . 'includes/class-admin.php';
require_once SPEED_BUILDER_PATH . 'includes/class-editor.php';
require_once SPEED_BUILDER_PATH . 'includes/class-rest-api.php';
require_once SPEED_BUILDER_PATH . 'includes/class-renderer.php';
require_once SPEED_BUILDER_PATH . 'includes/widgets/class-widget.php';
require_once SPEED_BUILDER_PATH . 'includes/widgets/class-heading.php';
require_once SPEED_BUILDER_PATH . 'includes/widgets/class-text.php';
require_once SPEED_BUILDER_PATH . 'includes/widgets/class-button.php';
require_once SPEED_BUILDER_PATH . 'includes/widgets/class-image.php';
require_once SPEED_BUILDER_PATH . 'includes/widgets/class-spacer.php';
require_once SPEED_BUILDER_PATH . 'includes/widgets/class-divider.php';
require_once SPEED_BUILDER_PATH . 'includes/class-plugin.php';

register_activation_hook( SPEED_BUILDER_FILE, array( 'Speed_Builder_Plugin', 'activate' ) );
register_deactivation_hook( SPEED_BUILDER_FILE, array( 'Speed_Builder_Plugin', 'deactivate' ) );

function speed_builder() {
	return Speed_Builder_Plugin::instance();
}

speed_builder();

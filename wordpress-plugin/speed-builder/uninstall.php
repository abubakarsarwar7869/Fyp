<?php
/**
 * Fired when the plugin is deleted through WordPress.
 *
 * Speed Builder deliberately preserves page-level builder data so a site owner
 * can reinstall without losing content. Delete this data manually only when
 * permanently removing the builder from a site.
 *
 * @package SpeedBuilder
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'speed_builder_options' );
delete_option( 'speed_builder_templates' );

<?php
/**
 * Plugin settings registration and sanitization.
 *
 * @package SpeedBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Speed_Builder_Settings {
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		register_setting(
			'speed_builder_settings',
			'speed_builder_options',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * @param mixed $input Raw settings input.
	 * @return array<string,mixed>
	 */
	public function sanitize_options( $input ) {
		$input = is_array( $input ) ? $input : array();
		return array(
			'enabled'           => empty( $input['enabled'] ) ? 0 : 1,
			'editor_width'      => (string) min( 1600, max( 720, absint( $input['editor_width'] ?? 1200 ) ) ),
			'canvas_background' => sanitize_hex_color( $input['canvas_background'] ?? '#f2f5f8' ) ?: '#f2f5f8',
			'autosave_interval' => (string) min( 5000, max( 750, absint( $input['autosave_interval'] ?? 1500 ) ) ),
			'conditional_css'   => empty( $input['conditional_css'] ) ? 0 : 1,
			'debug_mode'        => empty( $input['debug_mode'] ) ? 0 : 1,
		);
	}

	/**
	 * @param string $key Option key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get_option( $key, $default = null ) {
		$options = get_option( 'speed_builder_options', array() );
		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}
}

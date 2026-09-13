<?php
/**
 * Main plugin coordinator.
 *
 * @package SpeedBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Speed_Builder_Plugin {
	/** @var Speed_Builder_Plugin|null */
	private static $instance = null;

	/** @var array<string,Speed_Builder_Widget> */
	private $widgets = array();

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->register_widgets();
		new Speed_Builder_Assets();
		new Speed_Builder_Settings();
		new Speed_Builder_Admin();
		new Speed_Builder_Editor();
		new Speed_Builder_REST_API();
		new Speed_Builder_Renderer( $this );
	}

	public static function activate() {
		$defaults = array(
			'enabled'           => 1,
			'editor_width'      => '1200',
			'canvas_background' => '#f2f5f8',
			'autosave_interval' => '1500',
			'conditional_css'   => 1,
			'debug_mode'        => 0,
		);
		if ( false === get_option( 'speed_builder_options', false ) ) {
			add_option( 'speed_builder_options', $defaults );
		}
	}

	public static function deactivate() {
		// Data is intentionally retained. It belongs to the WordPress pages using the builder.
	}

	private function register_widgets() {
		$widget_classes = array(
			'Speed_Builder_Heading_Widget',
			'Speed_Builder_Text_Widget',
			'Speed_Builder_Button_Widget',
			'Speed_Builder_Image_Widget',
			'Speed_Builder_Spacer_Widget',
			'Speed_Builder_Divider_Widget',
		);

		foreach ( $widget_classes as $class_name ) {
			$widget = new $class_name();
			$this->widgets[ $widget->get_type() ] = $widget;
		}
	}

	/**
	 * @return array<string,Speed_Builder_Widget>
	 */
	public function get_widgets() {
		return $this->widgets;
	}

	/**
	 * @param string $type Widget type.
	 * @return Speed_Builder_Widget|null
	 */
	public function get_widget( $type ) {
		return isset( $this->widgets[ $type ] ) ? $this->widgets[ $type ] : null;
	}

	/**
	 * Empty but valid document for a newly created page.
	 *
	 * @return array<string,mixed>
	 */
	public function empty_document() {
		return array(
			'version'  => '1.0',
			'elements' => array(),
		);
	}
}

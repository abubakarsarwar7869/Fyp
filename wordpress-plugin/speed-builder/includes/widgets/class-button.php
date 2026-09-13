<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Speed_Builder_Button_Widget extends Speed_Builder_Widget {
	public function get_type() { return 'button'; }
	public function render( $element ) {
		$settings = $element['settings'];
		$text     = isset( $settings['text'] ) ? $settings['text'] : __( 'Learn more', 'speed-builder' );
		$url      = ! empty( $settings['url'] ) ? $settings['url'] : '#';
		$target   = ! empty( $settings['newTab'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
		$align    = isset( $settings['alignment'] ) ? $settings['alignment'] : 'left';
		return '<div id="' . esc_attr( $element['id'] ) . '" class="speed-builder-widget speed-builder-button speed-builder-align-' . esc_attr( $align ) . '"><a class="speed-builder-button-link" href="' . esc_url( $url ) . '"' . $target . $this->style_attribute( $element ) . '>' . esc_html( $text ) . '</a></div>';
	}
}

<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Speed_Builder_Heading_Widget extends Speed_Builder_Widget {
	public function get_type() { return 'heading'; }
	public function render( $element ) {
		$settings = $element['settings'];
		$tag      = isset( $settings['tag'] ) ? $settings['tag'] : 'h2';
		$text     = isset( $settings['text'] ) ? $settings['text'] : '';
		return $this->wrapper_start( $element ) . '<' . esc_attr( $tag ) . $this->style_attribute( $element ) . '>' . wp_kses_post( $text ) . '</' . esc_attr( $tag ) . '></div>';
	}
}

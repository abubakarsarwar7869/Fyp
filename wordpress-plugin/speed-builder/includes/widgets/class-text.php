<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Speed_Builder_Text_Widget extends Speed_Builder_Widget {
	public function get_type() { return 'text'; }
	public function render( $element ) {
		$text = isset( $element['settings']['text'] ) ? $element['settings']['text'] : '';
		return $this->wrapper_start( $element ) . '<div class="speed-builder-text-content"' . $this->style_attribute( $element ) . '>' . wp_kses_post( wpautop( $text ) ) . '</div></div>';
	}
}

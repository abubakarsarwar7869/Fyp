<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Speed_Builder_Spacer_Widget extends Speed_Builder_Widget {
	public function get_type() { return 'spacer'; }
	public function render( $element ) {
		$height = isset( $element['settings']['height'] ) ? absint( $element['settings']['height'] ) : 48;
		return '<div id="' . esc_attr( $element['id'] ) . '" class="speed-builder-widget speed-builder-spacer" style="height:' . esc_attr( $height ) . 'px" aria-hidden="true"></div>';
	}
}

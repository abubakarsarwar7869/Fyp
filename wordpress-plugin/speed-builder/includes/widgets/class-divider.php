<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Speed_Builder_Divider_Widget extends Speed_Builder_Widget {
	public function get_type() { return 'divider'; }
	public function render( $element ) {
		$s = $element['settings'];
		$style = sprintf(
			'border-top:%1$dpx %2$s %3$s;width:%4$d%%',
			isset( $s['thickness'] ) ? absint( $s['thickness'] ) : 1,
			isset( $s['style'] ) ? esc_attr( $s['style'] ) : 'solid',
			isset( $s['color'] ) ? esc_attr( $s['color'] ) : '#d0d5dd',
			isset( $s['width'] ) ? absint( $s['width'] ) : 100
		);
		$align = isset( $s['alignment'] ) ? $s['alignment'] : 'left';
		return '<div id="' . esc_attr( $element['id'] ) . '" class="speed-builder-widget speed-builder-divider speed-builder-align-' . esc_attr( $align ) . '"><hr style="' . esc_attr( $style ) . '" /></div>';
	}
}

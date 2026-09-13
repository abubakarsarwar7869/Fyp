<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Speed_Builder_Image_Widget extends Speed_Builder_Widget {
	public function get_type() { return 'image'; }
	public function render( $element ) {
		$settings = $element['settings'];
		$image_id = ! empty( $settings['attachmentId'] ) ? absint( $settings['attachmentId'] ) : 0;
		$url      = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : ( $settings['url'] ?? '' );
		$alt      = $image_id ? get_post_meta( $image_id, '_wp_attachment_image_alt', true ) : ( $settings['alt'] ?? '' );
		$align    = isset( $settings['alignment'] ) ? $settings['alignment'] : 'left';
		if ( ! $url ) { return ''; }
		$image = '<img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '"' . $this->style_attribute( $element ) . ' />';
		if ( ! empty( $settings['link'] ) ) { $image = '<a href="' . esc_url( $settings['link'] ) . '">' . $image . '</a>'; }
		return '<div id="' . esc_attr( $element['id'] ) . '" class="speed-builder-widget speed-builder-image speed-builder-align-' . esc_attr( $align ) . '">' . $image . '</div>';
	}
}

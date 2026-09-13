<?php
/**
 * Editor helpers and document normalization.
 *
 * @package SpeedBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Speed_Builder_Editor {
	/** @var array<int,string> */
	private $allowed_types = array( 'section', 'container', 'column', 'heading', 'text', 'button', 'image', 'spacer', 'divider' );

	/**
	 * Return a valid document for a page.
	 *
	 * @param int $post_id WordPress page ID.
	 * @return array<string,mixed>
	 */
	public function get_document( $post_id ) {
		$raw = get_post_meta( $post_id, '_speed_builder_data', true );
		$data = is_string( $raw ) ? json_decode( $raw, true ) : $raw;
		return $this->sanitize_document( $data );
	}

	/**
	 * Strictly sanitize the JSON document before storage.
	 *
	 * @param mixed $document Incoming document.
	 * @return array<string,mixed>
	 */
	public function sanitize_document( $document ) {
		if ( ! is_array( $document ) ) {
			return speed_builder()->empty_document();
		}

		$elements = isset( $document['elements'] ) && is_array( $document['elements'] ) ? $document['elements'] : array();
		$clean    = array();
		foreach ( $elements as $element ) {
			$sanitized = $this->sanitize_element( $element );
			if ( $sanitized ) {
				$clean[] = $sanitized;
			}
		}

		return array(
			'version'  => '1.0',
			'elements' => $clean,
		);
	}

	/**
	 * @param mixed $element Incoming element.
	 * @return array<string,mixed>|null
	 */
	private function sanitize_element( $element ) {
		if ( ! is_array( $element ) || empty( $element['type'] ) || ! in_array( $element['type'], $this->allowed_types, true ) ) {
			return null;
		}

		$type     = sanitize_key( $element['type'] );
		$settings = isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : array();
		$styles   = isset( $element['styles'] ) && is_array( $element['styles'] ) ? $element['styles'] : array();
		$children = isset( $element['children'] ) && is_array( $element['children'] ) ? $element['children'] : array();

		$clean_children = array();
		foreach ( $children as $child ) {
			$clean_child = $this->sanitize_element( $child );
			if ( $clean_child ) {
				$clean_children[] = $clean_child;
			}
		}

		$clean = array(
			'id'         => $this->sanitize_id( $element['id'] ?? '' ),
			'type'       => $type,
			'settings'   => $this->sanitize_settings( $type, $settings ),
			'styles'     => $this->sanitize_styles( $styles ),
			'responsive' => $this->sanitize_responsive( $element['responsive'] ?? array() ),
		);

		if ( in_array( $type, array( 'section', 'container', 'column' ), true ) ) {
			$clean['children'] = $clean_children;
		}
		return $clean;
	}

	private function sanitize_id( $id ) {
		$id = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $id );
		return $id ? substr( $id, 0, 80 ) : 'sb_' . wp_generate_uuid4();
	}

	/** @param array<string,mixed> $settings */
	private function sanitize_settings( $type, $settings ) {
		$clean = array();
		switch ( $type ) {
			case 'heading':
				$clean['text'] = wp_kses_post( $settings['text'] ?? '' );
				$clean['tag']  = in_array( $settings['tag'] ?? 'h2', array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ), true ) ? $settings['tag'] : 'h2';
				break;
			case 'text':
				$clean['text'] = wp_kses_post( $settings['text'] ?? '' );
				break;
			case 'button':
				$clean['text']        = sanitize_text_field( $settings['text'] ?? '' );
				$clean['url']         = esc_url_raw( $settings['url'] ?? '' );
				$clean['newTab']      = ! empty( $settings['newTab'] );
				$clean['alignment']   = in_array( $settings['alignment'] ?? 'left', array( 'left', 'center', 'right' ), true ) ? $settings['alignment'] : 'left';
				break;
			case 'image':
				$clean['attachmentId'] = absint( $settings['attachmentId'] ?? 0 );
				$clean['url']          = esc_url_raw( $settings['url'] ?? '' );
				$clean['alt']          = sanitize_text_field( $settings['alt'] ?? '' );
				$clean['link']         = esc_url_raw( $settings['link'] ?? '' );
				$clean['alignment']    = in_array( $settings['alignment'] ?? 'left', array( 'left', 'center', 'right' ), true ) ? $settings['alignment'] : 'left';
				break;
			case 'spacer':
				$clean['height'] = min( 800, max( 0, absint( $settings['height'] ?? 48 ) ) );
				break;
			case 'divider':
				$clean['style']     = in_array( $settings['style'] ?? 'solid', array( 'solid', 'dashed', 'dotted' ), true ) ? $settings['style'] : 'solid';
				$clean['thickness'] = min( 20, max( 1, absint( $settings['thickness'] ?? 1 ) ) );
				$clean['width']     = min( 100, max( 1, absint( $settings['width'] ?? 100 ) ) );
				$clean['color']     = sanitize_hex_color( $settings['color'] ?? '#d0d5dd' ) ?: '#d0d5dd';
				$clean['alignment'] = in_array( $settings['alignment'] ?? 'left', array( 'left', 'center', 'right' ), true ) ? $settings['alignment'] : 'left';
				break;
		}
		return $clean;
	}

	/** @param array<string,mixed> $styles */
	private function sanitize_styles( $styles ) {
		$allowed = array( 'color', 'backgroundColor', 'fontSize', 'fontWeight', 'textAlign', 'lineHeight', 'margin', 'padding', 'borderRadius', 'width' );
		$clean   = array();
		foreach ( $allowed as $key ) {
			if ( isset( $styles[ $key ] ) ) {
				$clean[ $key ] = $this->sanitize_css_value( $styles[ $key ] );
			}
		}
		return $clean;
	}

	/** @param mixed $responsive */
	private function sanitize_responsive( $responsive ) {
		$responsive = is_array( $responsive ) ? $responsive : array();
		$clean      = array();
		foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
			$clean[ $device ] = isset( $responsive[ $device ] ) && is_array( $responsive[ $device ] ) ? $this->sanitize_styles( $responsive[ $device ] ) : array();
		}
		return $clean;
	}

	/** @param mixed $value */
	private function sanitize_css_value( $value ) {
		$value = trim( wp_strip_all_tags( (string) $value ) );
		return preg_match( '/^[#(),.%\-\s\w]+$/', $value ) ? substr( $value, 0, 80 ) : '';
	}
}

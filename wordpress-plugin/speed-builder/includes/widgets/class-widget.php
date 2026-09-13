<?php
/**
 * Base widget contract.
 *
 * @package SpeedBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Speed_Builder_Widget {
	abstract public function get_type();

	/**
	 * @param array<string,mixed> $element Sanitized element.
	 * @return string
	 */
	abstract public function render( $element );

	/**
	 * @param array<string,mixed> $element Sanitized element.
	 * @return string
	 */
	protected function style_attribute( $element ) {
		$styles = isset( $element['styles'] ) && is_array( $element['styles'] ) ? $element['styles'] : array();
		$map    = array(
			'color'           => 'color',
			'backgroundColor' => 'background-color',
			'fontSize'        => 'font-size',
			'fontWeight'      => 'font-weight',
			'textAlign'       => 'text-align',
			'lineHeight'      => 'line-height',
			'margin'          => 'margin',
			'padding'         => 'padding',
			'borderRadius'    => 'border-radius',
			'width'           => 'width',
		);
		$declarations = array();
		foreach ( $map as $key => $property ) {
			if ( ! empty( $styles[ $key ] ) ) {
				$declarations[] = $property . ':' . esc_attr( $styles[ $key ] );
			}
		}
		return $declarations ? ' style="' . esc_attr( implode( ';', $declarations ) ) . '"' : '';
	}

	/**
	 * @param array<string,mixed> $element Sanitized element.
	 */
	protected function wrapper_start( $element, $class = '' ) {
		$id = isset( $element['id'] ) ? $element['id'] : '';
		return '<div id="' . esc_attr( $id ) . '" class="speed-builder-widget speed-builder-' . esc_attr( $this->get_type() ) . ' ' . esc_attr( $class ) . '">';
	}
}

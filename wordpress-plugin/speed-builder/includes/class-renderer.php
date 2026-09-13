<?php
/**
 * Frontend-only document renderer.
 *
 * @package SpeedBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Speed_Builder_Renderer {
	/** @var Speed_Builder_Plugin */
	private $plugin;

	/** @var Speed_Builder_Editor */
	private $editor;

	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->editor = new Speed_Builder_Editor();
		add_filter( 'the_content', array( $this, 'render_page_content' ), 20 );
	}

	/**
	 * Replace normal page content only for Speed Builder enabled pages.
	 *
	 * @param string $content Default page content.
	 * @return string
	 */
	public function render_page_content( $content ) {
		if ( is_admin() || ! is_singular( 'page' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}
		$post_id = get_the_ID();
		if ( ! $post_id || ! get_post_meta( $post_id, '_speed_builder_enabled', true ) ) {
			return $content;
		}
		return $this->render_page( $post_id );
	}

	/**
	 * @param int $post_id Page ID.
	 * @return string
	 */
	public function render_page( $post_id ) {
		$document = $this->editor->get_document( $post_id );
		$html     = '<div class="speed-builder-page">';
		$css      = '';
		foreach ( $document['elements'] as $element ) {
			$html .= $this->render_element( $element, $css );
		}
		$html .= '</div>';
		return $css ? '<style id="speed-builder-page-styles">' . $css . '</style>' . $html : $html;
	}

	/**
	 * @param array<string,mixed> $element Element to render.
	 * @param string              $css Generated responsive CSS.
	 * @return string
	 */
	private function render_element( $element, &$css ) {
		if ( empty( $element['type'] ) || empty( $element['id'] ) ) {
			return '';
		}
		$css .= $this->responsive_styles( $element );
		$type = $element['type'];
		if ( 'section' === $type ) {
			return '<section id="' . esc_attr( $element['id'] ) . '" class="speed-builder-section">' . $this->render_children( $element, $css ) . '</section>';
		}
		if ( 'container' === $type ) {
			return '<div id="' . esc_attr( $element['id'] ) . '" class="speed-builder-container">' . $this->render_children( $element, $css ) . '</div>';
		}
		if ( 'column' === $type ) {
			return '<div id="' . esc_attr( $element['id'] ) . '" class="speed-builder-column">' . $this->render_children( $element, $css ) . '</div>';
		}
		$widget = $this->plugin->get_widget( $type );
		return $widget ? $widget->render( $element ) : '';
	}

	/**
	 * @param array<string,mixed> $element Element with children.
	 * @param string              $css Generated responsive CSS.
	 * @return string
	 */
	private function render_children( $element, &$css ) {
		$html = '';
		foreach ( $element['children'] ?? array() as $child ) {
			$html .= $this->render_element( $child, $css );
		}
		return $html;
	}

	/**
	 * @param array<string,mixed> $element Element.
	 * @return string
	 */
	private function responsive_styles( $element ) {
		$id         = '#' . $element['id'];
		$responsive = $element['responsive'] ?? array();
		$css        = '';
		$map        = array(
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
		foreach ( array( 'tablet' => '1024px', 'mobile' => '767px' ) as $device => $breakpoint ) {
			if ( empty( $responsive[ $device ] ) || ! is_array( $responsive[ $device ] ) ) {
				continue;
			}
			$declarations = array();
			foreach ( $map as $key => $property ) {
				if ( ! empty( $responsive[ $device ][ $key ] ) ) {
					$declarations[] = $property . ':' . esc_attr( $responsive[ $device ][ $key ] );
				}
			}
			if ( $declarations ) {
				$css .= '@media(max-width:' . $breakpoint . '){' . $id . '{' . implode( ';', $declarations ) . '}}';
			}
		}
		return $css;
	}
}

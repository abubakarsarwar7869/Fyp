<?php
/**
 * Secure REST persistence layer for the editor.
 *
 * @package SpeedBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Speed_Builder_REST_API {
	/** @var Speed_Builder_Editor */
	private $editor;

	public function __construct() {
		$this->editor = new Speed_Builder_Editor();
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			'speed-builder/v1',
			'/pages/(?P<id>\d+)/document',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_document' ),
					'permission_callback' => array( $this, 'can_edit_page' ),
				),
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'save_document' ),
					'permission_callback' => array( $this, 'can_edit_page' ),
				),
			)
		);
		register_rest_route(
			'speed-builder/v1',
			'/pages/(?P<id>\d+)/publish',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'publish_page' ),
				'permission_callback' => array( $this, 'can_edit_page' ),
			)
		);
		register_rest_route(
			'speed-builder/v1',
			'/templates',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_templates' ),
					'permission_callback' => array( $this, 'can_manage_templates' ),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_template' ),
					'permission_callback' => array( $this, 'can_manage_templates' ),
				),
			)
		);
	}

	public function can_edit_page( $request ) {
		$post_id = absint( $request['id'] );
		return $post_id && in_array( get_post_type( $post_id ), array( 'page', 'post' ), true ) && current_user_can( 'edit_post', $post_id );
	}

	public function can_manage_templates() {
		return current_user_can( 'edit_pages' );
	}

	public function get_document( $request ) {
		$post_id = absint( $request['id'] );
		return rest_ensure_response(
			array(
				'document' => $this->editor->get_document( $post_id ),
				'post'     => array(
					'id'     => $post_id,
					'title'  => get_the_title( $post_id ),
					'status' => get_post_status( $post_id ),
				),
			)
		);
	}

	public function save_document( $request ) {
		$post_id  = absint( $request['id'] );
		$document = $request->get_param( 'document' );
		if ( ! is_array( $document ) ) {
			return new WP_Error( 'speed_builder_invalid_document', __( 'The builder document is invalid.', 'speed-builder' ), array( 'status' => 400 ) );
		}
		$document = $this->editor->sanitize_document( $document );
		$saved    = update_post_meta( $post_id, '_speed_builder_data', wp_json_encode( $document ) );
		update_post_meta( $post_id, '_speed_builder_enabled', '1' );
		return rest_ensure_response(
			array(
				'success'   => false !== $saved || (bool) get_post_meta( $post_id, '_speed_builder_data', true ),
				'document'  => $document,
				'modified'  => current_time( 'mysql' ),
			)
		);
	}

	public function publish_page( $request ) {
		$post_id = absint( $request['id'] );
		$result  = wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			),
			true
		);
		if ( is_wp_error( $result ) ) {
			return new WP_Error( 'speed_builder_publish_failed', __( 'Unable to publish this page.', 'speed-builder' ), array( 'status' => 500 ) );
		}
		return rest_ensure_response( array( 'success' => true, 'url' => get_permalink( $post_id ) ) );
	}

	public function get_templates() {
		$templates = get_option( 'speed_builder_templates', array() );
		return rest_ensure_response( array_values( is_array( $templates ) ? $templates : array() ) );
	}

	public function save_template( $request ) {
		$name     = sanitize_text_field( $request->get_param( 'name' ) );
		$document = $request->get_param( 'document' );
		if ( '' === $name || ! is_array( $document ) ) {
			return new WP_Error( 'speed_builder_invalid_template', __( 'A template name and document are required.', 'speed-builder' ), array( 'status' => 400 ) );
		}
		$template_id = 'template_' . wp_generate_uuid4();
		$templates   = get_option( 'speed_builder_templates', array() );
		$templates[ $template_id ] = array(
			'id'       => $template_id,
			'name'     => $name,
			'document' => $this->editor->sanitize_document( $document ),
			'created'  => current_time( 'mysql' ),
		);
		update_option( 'speed_builder_templates', $templates, false );
		return rest_ensure_response( $templates[ $template_id ] );
	}
}

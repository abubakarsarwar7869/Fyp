<?php
/**
 * Registers and conditionally loads plugin assets.
 *
 * @package SpeedBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Speed_Builder_Assets {
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
	}

	/**
	 * @param string $hook Current WordPress admin hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 0 !== strpos( $page, 'speed-builder' ) ) {
			return;
		}

		if ( 'speed-builder-editor' === $page ) {
			wp_enqueue_media();
			wp_enqueue_style( 'speed-builder-editor', SPEED_BUILDER_URL . 'editor/assets/editor.css', array(), SPEED_BUILDER_VERSION );
			wp_enqueue_script( 'speed-builder-state', SPEED_BUILDER_URL . 'editor/assets/state.js', array(), SPEED_BUILDER_VERSION, true );
			wp_enqueue_script( 'speed-builder-history', SPEED_BUILDER_URL . 'editor/assets/history.js', array( 'speed-builder-state' ), SPEED_BUILDER_VERSION, true );
			wp_enqueue_script( 'speed-builder-responsive', SPEED_BUILDER_URL . 'editor/assets/responsive.js', array( 'speed-builder-state' ), SPEED_BUILDER_VERSION, true );
			wp_enqueue_script( 'speed-builder-drag-drop', SPEED_BUILDER_URL . 'editor/assets/drag-drop.js', array( 'speed-builder-state' ), SPEED_BUILDER_VERSION, true );
			wp_enqueue_script( 'speed-builder-editor', SPEED_BUILDER_URL . 'editor/assets/editor.js', array( 'speed-builder-history', 'speed-builder-responsive', 'speed-builder-drag-drop', 'wp-api-fetch' ), SPEED_BUILDER_VERSION, true );

			$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_localize_script(
				'speed-builder-editor',
				'SpeedBuilderConfig',
				array(
					'restUrl'          => esc_url_raw( rest_url( 'speed-builder/v1/' ) ),
					'nonce'            => wp_create_nonce( 'wp_rest' ),
					'postId'           => $post_id,
					'adminUrl'         => admin_url(),
					'previewUrl'       => get_permalink( $post_id ),
					'editorUrl'        => admin_url( 'admin.php?page=speed-builder-editor&post_id=' . $post_id ),
					'logoUrl'          => SPEED_BUILDER_URL . 'assets/images/speed-builder-mark.svg',
					'autosaveInterval' => (int) Speed_Builder_Settings::get_option( 'autosave_interval', 1500 ),
					'i18n'             => array(
						'saved'      => __( 'Saved', 'speed-builder' ),
						'saveFailed' => __( 'Unable to save your changes.', 'speed-builder' ),
						'loading'    => __( 'Loading editor…', 'speed-builder' ),
					),
				)
			);
			return;
		}

		wp_enqueue_style( 'speed-builder-admin', SPEED_BUILDER_URL . 'admin/assets/admin.css', array(), SPEED_BUILDER_VERSION );
		wp_enqueue_script( 'speed-builder-admin', SPEED_BUILDER_URL . 'admin/assets/admin.js', array(), SPEED_BUILDER_VERSION, true );
	}

	public function enqueue_frontend_assets() {
		if ( is_admin() ) {
			return;
		}

		$post_id     = get_queried_object_id();
		$conditional = (bool) Speed_Builder_Settings::get_option( 'conditional_css', 1 );
		if ( $conditional && ( ! is_singular( 'page' ) || ! $post_id || ! get_post_meta( $post_id, '_speed_builder_enabled', true ) ) ) {
			return;
		}

		wp_enqueue_style( 'speed-builder-frontend', SPEED_BUILDER_URL . 'frontend/frontend.css', array(), SPEED_BUILDER_VERSION );
	}
}

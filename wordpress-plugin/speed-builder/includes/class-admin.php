<?php
/**
 * WordPress admin menus and page actions.
 *
 * @package SpeedBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Speed_Builder_Admin {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_post_speed_builder_create_page', array( $this, 'create_page' ) );
		add_action( 'admin_post_speed_builder_insert_template', array( $this, 'insert_template' ) );
	}

	public function register_menu() {
		$capability = 'edit_pages';
		add_menu_page(
			__( 'Speed Builder', 'speed-builder' ),
			__( 'Speed Builder', 'speed-builder' ),
			$capability,
			'speed-builder',
			array( $this, 'render_dashboard' ),
			'dashicons-admin-customizer',
			58
		);
		add_submenu_page( 'speed-builder', __( 'Dashboard', 'speed-builder' ), __( 'Dashboard', 'speed-builder' ), $capability, 'speed-builder', array( $this, 'render_dashboard' ) );
		add_submenu_page( 'speed-builder', __( 'Pages', 'speed-builder' ), __( 'Pages', 'speed-builder' ), $capability, 'speed-builder-pages', array( $this, 'render_pages' ) );
		add_submenu_page( 'speed-builder', __( 'Templates', 'speed-builder' ), __( 'Templates', 'speed-builder' ), $capability, 'speed-builder-templates', array( $this, 'render_templates' ) );
		add_submenu_page( 'speed-builder', __( 'Settings', 'speed-builder' ), __( 'Settings', 'speed-builder' ), 'manage_options', 'speed-builder-settings', array( $this, 'render_settings' ) );
		add_submenu_page( null, __( 'Speed Builder Editor', 'speed-builder' ), __( 'Speed Builder Editor', 'speed-builder' ), $capability, 'speed-builder-editor', array( $this, 'render_editor' ) );
	}

	public function render_dashboard() {
		$this->render_view( 'dashboard' );
	}

	public function render_pages() {
		$this->render_view( 'pages' );
	}

	public function render_templates() {
		$this->render_view( 'templates' );
	}

	public function render_settings() {
		$this->render_view( 'settings' );
	}

	public function render_editor() {
		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $post_id || 'page' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You do not have permission to edit this page with Speed Builder.', 'speed-builder' ) );
		}
		include SPEED_BUILDER_PATH . 'editor/editor.php';
	}

	/**
	 * @param string $view View filename without .php.
	 */
	private function render_view( $view ) {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to access Speed Builder.', 'speed-builder' ) );
		}
		include SPEED_BUILDER_PATH . 'admin/views/' . $view . '.php';
	}

	public function create_page() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to create pages.', 'speed-builder' ) );
		}
		check_admin_referer( 'speed_builder_create_page' );

		$title = isset( $_POST['speed_builder_page_title'] ) ? sanitize_text_field( wp_unslash( $_POST['speed_builder_page_title'] ) ) : __( 'Untitled Page', 'speed-builder' );
		if ( '' === $title ) {
			$title = __( 'Untitled Page', 'speed-builder' );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_title'  => $title,
				'post_status' => 'draft',
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=speed-builder-pages&speed_builder_error=create' ) );
			exit;
		}

		update_post_meta( $post_id, '_speed_builder_enabled', '1' );
		update_post_meta( $post_id, '_speed_builder_data', wp_json_encode( speed_builder()->empty_document() ) );
		wp_safe_redirect( admin_url( 'admin.php?page=speed-builder-editor&post_id=' . absint( $post_id ) ) );
		exit;
	}

	public function insert_template() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to insert templates.', 'speed-builder' ) );
		}
		check_admin_referer( 'speed_builder_insert_template' );

		$template_id = isset( $_POST['template_id'] ) ? sanitize_key( wp_unslash( $_POST['template_id'] ) ) : '';
		$page_id     = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0;
		$templates   = get_option( 'speed_builder_templates', array() );

		if ( ! $page_id || ! current_user_can( 'edit_post', $page_id ) || empty( $templates[ $template_id ]['document'] ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=speed-builder-templates&speed_builder_error=insert' ) );
			exit;
		}

		update_post_meta( $page_id, '_speed_builder_enabled', '1' );
		update_post_meta( $page_id, '_speed_builder_data', wp_json_encode( $templates[ $template_id ]['document'] ) );
		wp_safe_redirect( admin_url( 'admin.php?page=speed-builder-editor&post_id=' . $page_id ) );
		exit;
	}
}

<?php
/**
 * WordPress admin integration, menus, native post actions, and template actions.
 *
 * @package SpeedBuilder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Speed_Builder_Admin {
	/** @var array<int,string> */
	private $supported_post_types = array( 'page', 'post' );

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_builder_meta_box' ), 10, 2 );
		add_filter( 'page_row_actions', array( $this, 'add_builder_row_action' ), 10, 2 );
		add_filter( 'post_row_actions', array( $this, 'add_builder_row_action' ), 10, 2 );
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
		add_submenu_page( 'speed-builder', __( 'Templates', 'speed-builder' ), __( 'Templates', 'speed-builder' ), $capability, 'speed-builder-templates', array( $this, 'render_templates' ) );
		add_submenu_page( 'speed-builder', __( 'Settings', 'speed-builder' ), __( 'Settings', 'speed-builder' ), 'manage_options', 'speed-builder-settings', array( $this, 'render_settings' ) );
		add_submenu_page( null, __( 'Speed Builder Editor', 'speed-builder' ), __( 'Speed Builder Editor', 'speed-builder' ), 'edit_posts', 'speed-builder-editor', array( $this, 'render_editor' ) );
	}

	public function render_dashboard() { $this->render_view( 'dashboard' ); }
	public function render_templates() { $this->render_view( 'templates' ); }
	public function render_settings() { $this->render_view( 'settings' ); }

	/**
	 * Adds a WordPress-native launch point to Pages and Posts.
	 *
	 * @param string  $post_type Registered post type.
	 * @param WP_Post $post Current post.
	 */
	public function register_builder_meta_box( $post_type, $post ) {
		if ( ! in_array( $post_type, $this->supported_post_types, true ) || ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}
		add_meta_box(
			'speed-builder-launch',
			__( 'Speed Builder', 'speed-builder' ),
			array( $this, 'render_builder_meta_box' ),
			$post_type,
			'side',
			'high'
		);
	}

	/** @param WP_Post $post */
	public function render_builder_meta_box( $post ) {
		$enabled    = (bool) get_post_meta( $post->ID, '_speed_builder_enabled', true );
		$editor_url = $this->get_editor_url( $post->ID );
		?>
		<div class="sb-native-launch">
			<div class="sb-native-launch-mark"><img src="<?php echo esc_url( SPEED_BUILDER_URL . 'assets/images/speed-builder-mark.svg' ); ?>" alt="" /></div>
			<div class="sb-native-launch-copy"><strong><?php esc_html_e( 'Build this content visually', 'speed-builder' ); ?></strong><span><?php echo $enabled ? esc_html__( 'This item is using Speed Builder.', 'speed-builder' ) : esc_html__( 'Open the focused visual editor for this page or post.', 'speed-builder' ); ?></span></div>
			<a class="button button-primary sb-native-launch-button" href="<?php echo esc_url( $editor_url ); ?>"><?php echo $enabled ? esc_html__( 'Edit with Speed Builder', 'speed-builder' ) : esc_html__( 'Open Speed Builder', 'speed-builder' ); ?> <span>→</span></a>
			<p><?php esc_html_e( 'Your regular WordPress title, status, permalink, and revisions remain intact.', 'speed-builder' ); ?></p>
		</div>
		<?php
	}

	/**
	 * @param array<string,string> $actions Existing row actions.
	 * @param WP_Post              $post Post object.
	 * @return array<string,string>
	 */
	public function add_builder_row_action( $actions, $post ) {
		if ( ! in_array( $post->post_type, $this->supported_post_types, true ) || ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}
		$label = get_post_meta( $post->ID, '_speed_builder_enabled', true ) ? __( 'Edit with Speed Builder', 'speed-builder' ) : __( 'Open Speed Builder', 'speed-builder' );
		$actions['speed_builder'] = '<a href="' . esc_url( $this->get_editor_url( $post->ID ) ) . '" class="speed-builder-row-action">' . esc_html( $label ) . '</a>';
		return $actions;
	}

	public function render_editor() {
		$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $post_id || ! in_array( get_post_type( $post_id ), $this->supported_post_types, true ) || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You do not have permission to edit this content with Speed Builder.', 'speed-builder' ) );
		}
		include SPEED_BUILDER_PATH . 'editor/editor.php';
	}

	/** @param string $view View filename without .php. */
	private function render_view( $view ) {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to access Speed Builder.', 'speed-builder' ) );
		}
		include SPEED_BUILDER_PATH . 'admin/views/' . $view . '.php';
	}

	/**
	 * @param int $post_id Post ID.
	 * @return string
	 */
	private function get_editor_url( $post_id ) {
		return admin_url( 'admin.php?page=speed-builder-editor&post_id=' . absint( $post_id ) );
	}

	public function insert_template() {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to insert templates.', 'speed-builder' ) );
		}
		check_admin_referer( 'speed_builder_insert_template' );

		$template_id = isset( $_POST['template_id'] ) ? sanitize_key( wp_unslash( $_POST['template_id'] ) ) : '';
		$post_id     = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$templates   = get_option( 'speed_builder_templates', array() );

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) || empty( $templates[ $template_id ]['document'] ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=speed-builder-templates&speed_builder_error=insert' ) );
			exit;
		}

		update_post_meta( $post_id, '_speed_builder_enabled', '1' );
		update_post_meta( $post_id, '_speed_builder_data', wp_json_encode( $templates[ $template_id ]['document'] ) );
		wp_safe_redirect( $this->get_editor_url( $post_id ) );
		exit;
	}
}

<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$templates = get_option( 'speed_builder_templates', array() );
$pages     = get_posts( array( 'post_type' => array( 'page', 'post' ), 'post_status' => array( 'draft', 'publish', 'pending', 'private' ), 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
?>
<div class="speed-builder-admin">
	<header class="sb-admin-header">
		<div class="sb-admin-brand"><img src="<?php echo esc_url( SPEED_BUILDER_URL . 'assets/images/speed-builder-mark.svg' ); ?>" alt="" /><span>speed<span>builder</span></span></div>
		<nav><a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder' ) ); ?>">Dashboard</a><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">WordPress Pages</a><a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>">Posts</a><a class="is-active" href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-templates' ) ); ?>">Templates</a><a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-settings' ) ); ?>">Settings</a></nav>
		<div class="sb-header-meta"><a href="https://wordpress.org/support/" target="_blank" rel="noopener noreferrer">View documentation ↗</a><span>v<?php echo esc_html( SPEED_BUILDER_VERSION ); ?></span></div>
	</header>
	<main class="sb-admin-main sb-templates-main">
		<section class="sb-page-heading"><div><span class="sb-eyebrow">YOUR LIBRARY</span><h1>Saved templates</h1><p>Save your visual structure in the editor and reuse it on another WordPress Page or Post.</p></div></section>
		<?php if ( $templates ) : ?><div class="sb-template-grid"><?php foreach ( $templates as $template ) : ?><article class="sb-template-card"><div class="sb-template-preview"><span>SECTION TEMPLATE</span><b><?php echo esc_html( $template['name'] ); ?></b><i></i><i></i><i></i></div><div class="sb-template-content"><small>Saved <?php echo esc_html( mysql2date( get_option( 'date_format' ), $template['created'] ) ); ?></small><h2><?php echo esc_html( $template['name'] ); ?></h2><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="speed_builder_insert_template" /><input type="hidden" name="template_id" value="<?php echo esc_attr( $template['id'] ); ?>" /><?php wp_nonce_field( 'speed_builder_insert_template' ); ?><select name="post_id" required><option value="">Choose Page or Post…</option><?php foreach ( $pages as $page ) : ?><option value="<?php echo esc_attr( $page->ID ); ?>"><?php echo esc_html( $page->post_title ); ?></option><?php endforeach; ?></select><button class="sb-button sb-button-secondary" type="submit">Insert and edit →</button></form></div></article><?php endforeach; ?></div><?php else : ?><section class="sb-template-empty"><span>▦</span><h2>Your template library is waiting.</h2><p>Open any WordPress Page or Post and use “Save as template” to preserve a useful page structure.</p><a class="sb-button sb-button-primary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>">Open WordPress Pages</a></section><?php endif; ?>
	</main>
</div>

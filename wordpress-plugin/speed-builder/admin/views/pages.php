<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$pages = get_pages( array( 'sort_column' => 'post_modified', 'sort_order' => 'DESC' ) );
?>
<div class="speed-builder-admin">
	<header class="sb-admin-header">
		<div class="sb-admin-brand"><img src="<?php echo esc_url( SPEED_BUILDER_URL . 'assets/images/speed-builder-mark.svg' ); ?>" alt="" /><span>speed<span>builder</span></span></div>
		<nav><a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder' ) ); ?>">Dashboard</a><a class="is-active" href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-pages' ) ); ?>">Pages</a><a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-templates' ) ); ?>">Templates</a><a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-settings' ) ); ?>">Settings</a></nav>
		<div class="sb-header-meta"><a href="https://wordpress.org/support/" target="_blank" rel="noopener noreferrer">View documentation ↗</a><span>v<?php echo esc_html( SPEED_BUILDER_VERSION ); ?></span></div>
	</header>
	<main class="sb-admin-main sb-pages-main">
		<section class="sb-page-heading"><div><span class="sb-eyebrow">WORDPRESS PAGES</span><h1>Pages</h1><p>Use WordPress pages as your foundation, then bring them to life visually.</p></div><a class="sb-button sb-button-primary" href="#speed-builder-create-page">+ Create new page</a></section>
		<section class="sb-pages-table-wrap">
			<table class="sb-pages-table"><thead><tr><th>Page title</th><th>Status</th><th>Builder status</th><th>Modified</th><th><span class="screen-reader-text">Actions</span></th></tr></thead><tbody>
			<?php if ( $pages ) : foreach ( $pages as $page ) : $editor_url = admin_url( 'admin.php?page=speed-builder-editor&post_id=' . $page->ID ); ?>
			<tr><td><strong><?php echo esc_html( $page->post_title ? $page->post_title : __( '(no title)', 'speed-builder' ) ); ?></strong><small><?php echo esc_html( get_permalink( $page->ID ) ? wp_parse_url( get_permalink( $page->ID ), PHP_URL_PATH ) : '/' ); ?></small></td><td><span class="sb-status <?php echo 'publish' === $page->post_status ? 'published' : 'draft'; ?>"><i></i><?php echo esc_html( ucfirst( $page->post_status ) ); ?></span></td><td><?php if ( get_post_meta( $page->ID, '_speed_builder_enabled', true ) ) : ?><span class="sb-builder-enabled">● Built with Speed Builder</span><?php else : ?><span class="sb-builder-off">— Not enabled</span><?php endif; ?></td><td><time datetime="<?php echo esc_attr( get_post_modified_time( 'c', true, $page ) ); ?>"><?php echo esc_html( get_post_modified_time( get_option( 'date_format' ), false, $page ) ); ?></time></td><td class="sb-row-actions"><a href="<?php echo esc_url( $editor_url ); ?>">Edit with Speed Builder</a><a href="<?php echo esc_url( get_edit_post_link( $page->ID ) ); ?>">WordPress</a><a href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>" target="_blank" rel="noopener noreferrer">View</a><a class="sb-delete-page" href="<?php echo esc_url( get_delete_post_link( $page->ID, '', false ) ); ?>">Delete</a></td></tr>
			<?php endforeach; else : ?><tr><td colspan="5" class="sb-empty-row">No pages yet. Create one to start building.</td></tr><?php endif; ?>
			</tbody></table>
		</section>
		<section class="sb-create-page" id="speed-builder-create-page"><div><span class="sb-eyebrow">NEW WORDPRESS PAGE</span><h2>Start a fresh page</h2><p>We’ll create a regular WordPress draft, then take you straight to the builder.</p></div><form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="speed_builder_create_page" /><?php wp_nonce_field( 'speed_builder_create_page' ); ?><label for="speed-builder-page-title" class="screen-reader-text">Page title</label><input id="speed-builder-page-title" type="text" name="speed_builder_page_title" placeholder="e.g. About us" required /><button class="sb-button sb-button-primary" type="submit">Create and open builder <span>→</span></button></form></section>
	</main>
</div>

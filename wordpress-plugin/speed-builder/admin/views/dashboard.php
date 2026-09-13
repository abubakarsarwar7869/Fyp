<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$page_count     = new WP_Query( array( 'post_type' => 'page', 'meta_key' => '_speed_builder_enabled', 'meta_value' => '1', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$template_count = get_option( 'speed_builder_templates', array() );
$create_url     = admin_url( 'admin.php?page=speed-builder-pages#speed-builder-create-page' );
?>
<div class="speed-builder-admin">
	<header class="sb-admin-header">
		<div class="sb-admin-brand"><img src="<?php echo esc_url( SPEED_BUILDER_URL . 'assets/images/speed-builder-mark.svg' ); ?>" alt="" /><span>speed<span>builder</span></span></div>
		<nav>
			<a class="is-active" href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder' ) ); ?>">Dashboard</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-pages' ) ); ?>">Pages</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-templates' ) ); ?>">Templates</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-settings' ) ); ?>">Settings</a>
		</nav>
		<div class="sb-header-meta"><a href="https://wordpress.org/support/" target="_blank" rel="noopener noreferrer">View documentation ↗</a><span>v<?php echo esc_html( SPEED_BUILDER_VERSION ); ?></span></div>
	</header>
	<main class="sb-admin-main">
		<section class="sb-welcome">
			<div><span class="sb-eyebrow">YOUR WORKSPACE</span><h1>Build with less friction.</h1><p>A focused, WordPress-native space for bringing your next page to life.</p><a class="sb-button sb-button-primary" href="<?php echo esc_url( $create_url ); ?>">Create a new page <span>→</span></a></div>
			<div class="sb-welcome-art" aria-hidden="true"><i></i><i></i><div><b>Build ideas<br />that move.</b><span></span><em></em></div></div>
		</section>
		<section class="sb-stat-grid">
			<article><span class="sb-stat-icon blue">▣</span><div><p>Your pages</p><b><?php echo esc_html( (string) $page_count->post_count ); ?></b><small>Built with Speed Builder</small></div></article>
			<article><span class="sb-stat-icon mint">◫</span><div><p>Templates</p><b><?php echo esc_html( (string) count( $template_count ) ); ?></b><small>Saved building blocks</small></div></article>
			<article><span class="sb-stat-icon violet">↗</span><div><p>Editor status</p><b>Ready</b><small>Everything is up to date</small></div></article>
		</section>
		<section class="sb-getting-started">
			<div class="sb-section-title"><div><span class="sb-eyebrow">A SIMPLE START</span><h2>Getting started</h2><p>Make your first WordPress page in a few clear steps.</p></div></div>
			<div class="sb-step-grid">
				<?php foreach ( array( array( '01', 'Create a page', 'Start with a new WordPress page. It stays part of your normal site.' ), array( '02', 'Open Speed Builder', 'Choose the focused visual workspace instead of a blank editor.' ), array( '03', 'Add your sections', 'Pick a one to four column structure to frame the page.' ), array( '04', 'Add and publish', 'Drop in widgets, refine your page, then preview and publish.' ) ) as $step ) : ?>
				<article><span><?php echo esc_html( $step[0] ); ?></span><h3><?php echo esc_html( $step[1] ); ?></h3><p><?php echo esc_html( $step[2] ); ?></p></article>
				<?php endforeach; ?>
			</div>
		</section>
	</main>
</div>

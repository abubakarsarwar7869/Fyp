<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$builder_pages = new WP_Query( array( 'post_type' => array( 'page', 'post' ), 'meta_key' => '_speed_builder_enabled', 'meta_value' => '1', 'posts_per_page' => -1, 'fields' => 'ids' ) );
$template_count = get_option( 'speed_builder_templates', array() );
$new_page_url   = admin_url( 'post-new.php?post_type=page' );
$native_pages   = admin_url( 'edit.php?post_type=page' );
$native_posts   = admin_url( 'edit.php' );
?>
<div class="speed-builder-admin sb-future-admin">
	<header class="sb-admin-header">
		<div class="sb-admin-brand"><img src="<?php echo esc_url( SPEED_BUILDER_URL . 'assets/images/speed-builder-mark.svg' ); ?>" alt="" /><span>speed<span>builder</span></span></div>
		<nav>
			<a class="is-active" href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder' ) ); ?>">Dashboard</a>
			<a href="<?php echo esc_url( $native_pages ); ?>">WordPress Pages</a>
			<a href="<?php echo esc_url( $native_posts ); ?>">Posts</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-templates' ) ); ?>">Templates</a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-settings' ) ); ?>">Settings</a>
		</nav>
		<div class="sb-header-meta"><a href="https://wordpress.org/support/" target="_blank" rel="noopener noreferrer">Documentation ↗</a><span>PHASE 01 · v<?php echo esc_html( SPEED_BUILDER_VERSION ); ?></span></div>
	</header>
	<main class="sb-admin-main">
		<section class="sb-welcome sb-future-welcome">
			<div><span class="sb-eyebrow">WORDPRESS-NATIVE VISUAL BUILDING</span><h1>Move at the speed of <em>thought.</em></h1><p>Create your page in WordPress, then use Speed Builder to shape the entire content area with a focused visual workspace.</p><div class="sb-welcome-actions"><a class="sb-button sb-button-primary" href="<?php echo esc_url( $new_page_url ); ?>">Create WordPress page <span>→</span></a><a class="sb-button sb-button-ghost" href="<?php echo esc_url( $native_pages ); ?>">Open your pages</a></div><small class="sb-native-note">✦ Open any standard WordPress Page or Post, then choose <b>Open Speed Builder</b>.</small></div>
			<div class="sb-welcome-art" aria-hidden="true"><div class="sb-orbit-grid"></div><i></i><i></i><div><small>LIVE CANVAS</small><b>Build ideas<br />that <em>move.</em></b><span></span><em></em><u></u></div></div>
		</section>
		<section class="sb-stat-grid sb-stat-grid-future">
			<article><span class="sb-stat-icon blue">▣</span><div><p>Builder content</p><b><?php echo esc_html( (string) $builder_pages->post_count ); ?></b><small>Pages and posts in motion</small></div><i>↗</i></article>
			<article><span class="sb-stat-icon mint">◫</span><div><p>Saved templates</p><b><?php echo esc_html( (string) count( $template_count ) ); ?></b><small>Reusable page structures</small></div><i>↗</i></article>
			<article><span class="sb-stat-icon violet">◌</span><div><p>Editor system</p><b>Ready</b><small>Fast, local, and up to date</small></div><i>●</i></article>
		</section>
		<section class="sb-native-workflow">
			<div class="sb-section-title"><div><span class="sb-eyebrow">ONE CONTENT SYSTEM</span><h2>Built into WordPress, not beside it.</h2><p>Speed Builder works from the Pages and Posts you already know. Titles, permalinks, revisions, publishing, and permissions stay entirely WordPress-native.</p></div><a href="<?php echo esc_url( $native_pages ); ?>">View Pages →</a></div>
			<div class="sb-step-grid sb-native-steps">
				<article><span>01</span><i>▤</i><h3>Create in WordPress</h3><p>Create a regular Page or Post. There is no separate CMS and no duplicate content.</p></article>
				<article><span>02</span><i>◌</i><h3>Open Speed Builder</h3><p>Use the new action in the post list or the Speed Builder card inside the editor.</p></article>
				<article><span>03</span><i>▥</i><h3>Shape your canvas</h3><p>Add a section, choose columns, and layer in focused visual components.</p></article>
				<article><span>04</span><i>↗</i><h3>Preview and publish</h3><p>Save safely, preview the real frontend, and publish through WordPress.</p></article>
			</div>
		</section>
	</main>
</div>

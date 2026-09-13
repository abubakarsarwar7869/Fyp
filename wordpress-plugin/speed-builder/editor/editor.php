<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$post    = get_post( $post_id );
?>
<div id="speed-builder-editor" class="speed-builder-editor" data-post-id="<?php echo esc_attr( $post_id ); ?>">
	<div class="sb-editor-loading" id="sb-editor-loading"><img src="<?php echo esc_url( SPEED_BUILDER_URL . 'assets/images/speed-builder-mark.svg' ); ?>" alt="Speed Builder" /><b>speed<span>builder</span></b><p>Loading editor…</p></div>
	<header class="sb-editor-topbar">
		<div class="sb-editor-project"><a class="sb-editor-back" href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-pages' ) ); ?>" title="Back to WordPress pages">←</a><img src="<?php echo esc_url( SPEED_BUILDER_URL . 'assets/images/speed-builder-mark.svg' ); ?>" alt="" /><span class="sb-editor-wordmark">speed<span>builder</span></span><i></i><b><?php echo esc_html( $post->post_title ); ?></b></div>
		<div class="sb-editor-devices" role="group" aria-label="Responsive preview"><button class="is-active" data-sb-device="desktop" title="Desktop preview">▱ <span>Desktop</span></button><button data-sb-device="tablet" title="Tablet preview">▯ <span>Tablet</span></button><button data-sb-device="mobile" title="Mobile preview">▯ <span>Mobile</span></button></div>
		<div class="sb-editor-actions"><button data-sb-action="undo" title="Undo (Ctrl/Cmd + Z)">↶</button><button data-sb-action="redo" title="Redo (Ctrl/Cmd + Shift + Z)">↷</button><i></i><button data-sb-action="save-template" title="Save this page as a template">▦ <span>Template</span></button><button data-sb-action="preview" title="Preview page">◉ <span>Preview</span></button><button class="sb-save-button" data-sb-action="save">Save</button><button class="sb-publish-button" data-sb-action="publish">Publish ↗</button></div>
	</header>
	<div class="sb-editor-body">
		<aside class="sb-elements-panel">
			<div class="sb-panel-title"><span>▦</span><b>Elements</b></div>
			<div class="sb-element-search"><span>⌕</span><input type="search" placeholder="Search elements" data-sb-search /></div>
			<div class="sb-widget-library">
				<p>BASIC</p>
				<?php foreach ( array( array( 'heading', 'H', 'Heading', 'Add a title or heading' ), array( 'text', '¶', 'Text', 'Add paragraph text' ), array( 'button', '↗', 'Button', 'Add a call to action' ), array( 'image', '▧', 'Image', 'Use WordPress media' ), array( 'spacer', '↕', 'Spacer', 'Add breathing room' ), array( 'divider', '—', 'Divider', 'Separate content' ) ) as $widget ) : ?>
				<button class="sb-widget-button" draggable="true" data-sb-widget="<?php echo esc_attr( $widget[0] ); ?>"><span><?php echo esc_html( $widget[1] ); ?></span><strong><?php echo esc_html( $widget[2] ); ?><small><?php echo esc_html( $widget[3] ); ?></small></strong><i>⠿</i></button>
				<?php endforeach; ?>
				<p>LAYOUT</p>
				<?php foreach ( array( array( 'section', '▤', 'Section', 'A full-width row' ), array( 'container', '□', 'Container', 'Group content together' ), array( 'columns', '▥', 'Columns', 'Start with a structure' ) ) as $widget ) : ?>
				<button class="sb-widget-button" draggable="true" data-sb-widget="<?php echo esc_attr( $widget[0] ); ?>"><span><?php echo esc_html( $widget[1] ); ?></span><strong><?php echo esc_html( $widget[2] ); ?><small><?php echo esc_html( $widget[3] ); ?></small></strong><i>⠿</i></button>
				<?php endforeach; ?>
			</div>
			<div class="sb-panel-bottom"><button data-sb-action="add-section">＋ Add section</button><button data-sb-action="toggle-settings">⚙ Page settings</button></div>
		</aside>
		<main class="sb-canvas-workspace"><div class="sb-workspace-toolbar"><span>Live canvas</span><span>Changes save automatically</span><div><button data-sb-action="zoom-out">−</button><b>100%</b><button data-sb-action="zoom-in">+</button></div></div><div class="sb-canvas-scroll"><div id="sb-canvas" class="sb-canvas sb-device-desktop" aria-live="polite"></div></div></main>
		<aside class="sb-settings-panel"><div class="sb-panel-title"><span>◌</span><b id="sb-settings-title">Element settings</b></div><div class="sb-settings-tabs"><button class="is-active" data-sb-tab="content">Content</button><button data-sb-tab="style">Style</button><button data-sb-tab="advanced">Advanced</button></div><div id="sb-settings-content" class="sb-settings-content"><div class="sb-no-selection"><span>⌖</span><b>Select an element</b><p>Choose something on the canvas to start editing it.</p></div></div></aside>
	</div>
	<div class="sb-floating-toolbar" id="sb-floating-toolbar" hidden><button data-sb-action="move-up" title="Move up">↑</button><button data-sb-action="move-down" title="Move down">↓</button><i></i><button data-sb-action="edit" title="Edit">✎</button><button data-sb-action="duplicate" title="Duplicate">⧉</button><button data-sb-action="delete" class="is-delete" title="Delete">×</button></div>
	<div class="sb-section-picker" id="sb-section-picker" hidden><div><button class="sb-modal-close" data-sb-action="close-section-picker">×</button><span class="sb-modal-icon">▥</span><h2>Choose a section structure</h2><p>Start with a simple number of columns. You can add widgets to each column next.</p><div class="sb-column-options"><button data-sb-columns="1"><i><span></span></i><b>1 column</b></button><button data-sb-columns="2"><i><span></span><span></span></i><b>2 columns</b></button><button data-sb-columns="3"><i><span></span><span></span><span></span></i><b>3 columns</b></button><button data-sb-columns="4"><i><span></span><span></span><span></span><span></span></i><b>4 columns</b></button></div></div></div>
	<div id="sb-toast" class="sb-toast" role="status" aria-live="polite"></div>
</div>

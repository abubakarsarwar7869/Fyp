<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
$options = get_option( 'speed_builder_options', array() );
?>
<div class="speed-builder-admin">
	<header class="sb-admin-header">
		<div class="sb-admin-brand"><img src="<?php echo esc_url( SPEED_BUILDER_URL . 'assets/images/speed-builder-mark.svg' ); ?>" alt="" /><span>speed<span>builder</span></span></div>
		<nav><a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder' ) ); ?>">Dashboard</a><a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-pages' ) ); ?>">Pages</a><a href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-templates' ) ); ?>">Templates</a><a class="is-active" href="<?php echo esc_url( admin_url( 'admin.php?page=speed-builder-settings' ) ); ?>">Settings</a></nav>
		<div class="sb-header-meta"><a href="https://wordpress.org/support/" target="_blank" rel="noopener noreferrer">View documentation ↗</a><span>v<?php echo esc_html( SPEED_BUILDER_VERSION ); ?></span></div>
	</header>
	<main class="sb-admin-main sb-settings-main">
		<section class="sb-page-heading"><div><span class="sb-eyebrow">PLUGIN PREFERENCES</span><h1>Settings</h1><p>Keep the editor focused around the way you like to work.</p></div></section>
		<form action="options.php" method="post" class="sb-settings-form"><?php settings_fields( 'speed_builder_settings' ); ?>
			<section class="sb-setting-card"><div><span class="sb-setting-icon">◉</span><div><h2>General</h2><p>Control access to the Speed Builder editor.</p></div></div><label class="sb-switch-row"><span><b>Enable Speed Builder</b><small>Show the visual editor for WordPress pages.</small></span><input type="checkbox" name="speed_builder_options[enabled]" value="1" <?php checked( ! empty( $options['enabled'] ) ); ?> /><i></i></label></section>
			<section class="sb-setting-card"><div><span class="sb-setting-icon">⌑</span><div><h2>Editor</h2><p>Set a few comfortable workspace defaults.</p></div></div><div class="sb-settings-fields"><label>Maximum canvas width <span><input type="number" min="720" max="1600" name="speed_builder_options[editor_width]" value="<?php echo esc_attr( $options['editor_width'] ?? 1200 ); ?>" /> px</span></label><label>Canvas background <span><input type="color" name="speed_builder_options[canvas_background]" value="<?php echo esc_attr( $options['canvas_background'] ?? '#f2f5f8' ); ?>" /><input type="text" value="<?php echo esc_attr( $options['canvas_background'] ?? '#f2f5f8' ); ?>" readonly /></span></label><label>Autosave delay <span><input type="number" min="750" max="5000" step="250" name="speed_builder_options[autosave_interval]" value="<?php echo esc_attr( $options['autosave_interval'] ?? 1500 ); ?>" /> ms</span></label></div></section>
			<section class="sb-setting-card"><div><span class="sb-setting-icon">ϟ</span><div><h2>Performance</h2><p>Load only the assets a builder page needs.</p></div></div><label class="sb-switch-row"><span><b>Load CSS conditionally</b><small>Only enqueue frontend styles on a page created with Speed Builder.</small></span><input type="checkbox" name="speed_builder_options[conditional_css]" value="1" <?php checked( ! empty( $options['conditional_css'] ) ); ?> /><i></i></label></section>
			<section class="sb-setting-card"><div><span class="sb-setting-icon">&lt;/&gt;</span><div><h2>Advanced</h2><p>Useful information when you are developing or troubleshooting.</p></div></div><label class="sb-switch-row"><span><b>Debug mode</b><small>Enable extra diagnostic details in browser console.</small></span><input type="checkbox" name="speed_builder_options[debug_mode]" value="1" <?php checked( ! empty( $options['debug_mode'] ) ); ?> /><i></i></label></section>
			<div class="sb-settings-submit"><?php submit_button( __( 'Save settings', 'speed-builder' ), 'primary sb-button sb-button-primary', 'submit', false ); ?></div>
		</form>
	</main>
</div>

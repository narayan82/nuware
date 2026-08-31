<?php
/**
 * Theme assets.
 */

function nuware_enqueue_assets() {
	$is_backup = is_page( 'backup-home' );
	// The contact form is rendered in the footer, so load its assets before wp_head.
	if ( ! $is_backup && function_exists( 'gravity_form_enqueue_scripts' ) ) {
		gravity_form_enqueue_scripts( 1, true );
	}
	if ( is_front_page() || $is_backup ) {
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script( 'nuware-particles', get_template_directory_uri() . '/assets/js/vendor/particles.js', array(), '2.0.0', true );
	}
	$theme_version = wp_get_theme()->get( 'Version' );
	$css_asset     = $is_backup ? '/assets/css/backup-home.css' : '/assets/css/main.css';
	$js_asset      = $is_backup ? '/assets/js/backup-home/main.js' : '/assets/js/main.js';
	$css_path      = get_template_directory() . $css_asset;
	$js_path       = get_template_directory() . $js_asset;

	wp_enqueue_style(
		'nuware-main',
		get_template_directory_uri() . $css_asset,
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : $theme_version
	);

	wp_enqueue_script(
		'nuware-main',
		get_template_directory_uri() . $js_asset,
		( is_front_page() || $is_backup ) ? array( 'nuware-particles' ) : array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : $theme_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'nuware_enqueue_assets' );

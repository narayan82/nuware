<?php
/**
 * Theme assets.
 */

function nuware_enqueue_assets() {
	$is_backup = is_page( 'backup-home' );
	$current_parent_id = is_page() ? wp_get_post_parent_id( get_queried_object_id() ) : 0;
	$is_world_page = $current_parent_id && 'industries' === get_post_field( 'post_name', $current_parent_id );
	// The contact form is rendered in the footer, so load its assets before wp_head.
	if ( ! $is_backup && function_exists( 'gravity_form_enqueue_scripts' ) ) {
		gravity_form_enqueue_scripts( 1, true );

		if ( is_page( 'careers' ) ) {
			gravity_form_enqueue_scripts( 2, true );
		}
	}
	if ( is_front_page() || $is_backup || is_page( 'ai' ) ) {
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_script( 'nuware-particles', get_template_directory_uri() . '/assets/js/vendor/particles.js', array(), '2.0.0', true );
	}
	$theme_version = wp_get_theme()->get( 'Version' );
	$css_asset     = $is_backup ? '/assets/css/backup-home.css' : '/assets/css/main.css';
	$js_asset      = $is_backup ? '/assets/js/backup-home/main.js' : '/assets/js/main.js';
	$css_path      = get_template_directory() . $css_asset;
	$js_path       = get_template_directory() . $js_asset;
	$main_dependencies = ( is_front_page() || $is_backup || is_page( 'ai' ) ) ? array( 'nuware-particles' ) : array();

	if ( $is_world_page ) {
		wp_enqueue_script(
			'nuware-three',
			get_template_directory_uri() . '/assets/js/vendor/three.min.js',
			array(),
			'0.160.1',
			true
		);
		$main_dependencies[] = 'nuware-three';
	}

	wp_enqueue_style(
		'nuware-main',
		get_template_directory_uri() . $css_asset,
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : $theme_version
	);

	wp_enqueue_script(
		'nuware-main',
		get_template_directory_uri() . $js_asset,
		$main_dependencies,
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : $theme_version,
		true
	);

	if ( is_front_page() ) {
		wp_localize_script(
			'nuware-main',
			'nuwareAi',
			array(
				'endpoint' => esc_url_raw( rest_url( 'nuware/v1/ask' ) ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'nuware_enqueue_assets' );

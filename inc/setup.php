<?php
/**
 * Theme setup.
 */

function nuware_setup() {
	// Use the supplied brand favicon throughout the public site.
	remove_action( 'wp_head', 'wp_site_icon', 99 );
	add_action( 'wp_head', 'nuware_favicon', 99 );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'script',
			'style',
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary Menu', 'nuware' ),
		)
	);
}
add_action( 'after_setup_theme', 'nuware_setup' );

/**
 * Output the theme's SVG favicon.
 */
function nuware_favicon() {
	$favicon_url = get_template_directory_uri() . '/assets/images/nu_favicon.svg';
	printf( '<link rel="icon" type="image/svg+xml" sizes="any" href="%s">' . "\n", esc_url( $favicon_url ) );
}

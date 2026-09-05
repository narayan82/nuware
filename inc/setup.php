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

/**
 * Output the Google Analytics tag on every public page.
 */
function nuware_google_analytics_tag() {
	?>
	<script>
		(function () {
			let loaded = false;
			function loadNuwareAnalytics() {
				if (loaded) return;
				loaded = true;
				window.dataLayer = window.dataLayer || [];
				window.gtag = function () { window.dataLayer.push(arguments); };
				window.gtag('js', new Date());
				window.gtag('config', 'G-P9PPMMJ2QQ');
				const script = document.createElement('script');
				script.async = true;
				script.src = 'https://www.googletagmanager.com/gtag/js?id=G-P9PPMMJ2QQ';
				document.head.appendChild(script);
			}

			try {
				if (localStorage.getItem('nuware-cookie-consent') === 'accepted') loadNuwareAnalytics();
			} catch (error) {}
			window.addEventListener('nuware-cookie-consent', loadNuwareAnalytics, { once: true });
		})();
	</script>
	<?php
}
add_action( 'wp_head', 'nuware_google_analytics_tag', 20 );

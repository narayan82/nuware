<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>
		// Set the local-time theme before styles paint to avoid a light-mode flash.
		const nuwareLocalHour = new Date().getHours();
		document.documentElement.classList.toggle('theme-dark', nuwareLocalHour >= 19 || nuwareLocalHour < 7);
	</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
	<div class="site-header__inner">
		<a class="site-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<img
				class="site-header__logo site-header__logo--light"
				src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/nuware-logo.svg' ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				width="106"
				height="40"
			>
			<img
				class="site-header__logo site-header__logo--dark"
				src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/nuware-logo-dark.svg' ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				width="106"
				height="40"
			>
		</a>

		<nav class="site-header__navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'nuware' ); ?>">
			<?php
			$nuware_primary_menu_classes = static function ( $classes, $menu_item, $args ) {
				if ( isset( $args->theme_location ) && 'primary' === $args->theme_location ) {
					$menu_title = trim( wp_strip_all_tags( $menu_item->title ) );

					if ( 0 === strcasecmp( $menu_title, 'Get in Touch' ) ) {
						$classes[] = 'menu-item--contact';
					}

					if ( 0 === strcasecmp( $menu_title, 'AI' ) ) {
						$classes[] = 'menu-item--ai';
					}

					if ( false !== stripos( $menu_item->url, 'linkedin.com' ) ) {
						$classes[] = 'menu-item--linkedin';
					}
				}

				return $classes;
			};
			$nuware_primary_menu_attributes = static function ( $attributes, $menu_item, $args ) {
				if ( isset( $args->theme_location ) && 'primary' === $args->theme_location && '_blank' === ( $attributes['target'] ?? '' ) ) {
					$attributes['rel'] = trim( ( $attributes['rel'] ?? '' ) . ' noopener noreferrer' );
				}

				return $attributes;
			};

			add_filter( 'nav_menu_css_class', $nuware_primary_menu_classes, 10, 3 );
			add_filter( 'nav_menu_link_attributes', $nuware_primary_menu_attributes, 10, 3 );

			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_id'        => 'primary-menu',
					'menu_class'     => 'site-header__menu',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);

			remove_filter( 'nav_menu_css_class', $nuware_primary_menu_classes, 10 );
			remove_filter( 'nav_menu_link_attributes', $nuware_primary_menu_attributes, 10 );
			?>
		</nav>

		<div class="site-header__controls">
			<button
				class="site-header__contact-trigger"
				type="button"
				data-contact-trigger
				aria-label="<?php esc_attr_e( 'Get in Touch', 'nuware' ); ?>"
			>
				<span class="screen-reader-text"><?php esc_html_e( 'Get in Touch', 'nuware' ); ?></span>
			</button>

			<button
				class="site-header__theme-toggle"
				type="button"
				aria-label="<?php esc_attr_e( 'Switch to dark mode', 'nuware' ); ?>"
				aria-pressed="false"
			>
				<svg class="site-header__theme-icon site-header__theme-icon--moon" viewBox="0 0 24 24" aria-hidden="true">
					<path d="M20.4 15.2A8.5 8.5 0 0 1 8.8 3.6 8.5 8.5 0 1 0 20.4 15.2Z"></path>
				</svg>
				<svg class="site-header__theme-icon site-header__theme-icon--sun" viewBox="0 0 24 24" aria-hidden="true">
					<circle cx="12" cy="12" r="4"></circle>
					<path d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"></path>
				</svg>
			</button>

			<button
				class="site-header__toggle"
				type="button"
				aria-controls="primary-menu"
				aria-expanded="false"
			>
				<span class="site-header__toggle-line"></span>
				<span class="site-header__toggle-line"></span>
				<span class="site-header__toggle-line"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Toggle navigation', 'nuware' ); ?></span>
			</button>
		</div>
	</div>
</header>

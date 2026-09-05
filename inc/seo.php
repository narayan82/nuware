<?php
/**
 * Lightweight SEO fallbacks used only when a dedicated SEO plugin is absent.
 */

function nuware_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' )
		|| defined( 'RANK_MATH_VERSION' )
		|| defined( 'AIOSEO_VERSION' )
		|| defined( 'SEOPRESS_VERSION' )
		|| class_exists( 'WPSEO_Frontend' )
		|| class_exists( 'RankMath' )
		|| class_exists( 'AIOSEO\\Plugin\\AIOSEO' );
}

function nuware_document_title_separator( $separator ) {
	return nuware_has_seo_plugin() ? $separator : '|';
}

function nuware_document_title_parts( $parts ) {
	if ( nuware_has_seo_plugin() ) {
		return $parts;
	}

	if ( is_front_page() ) {
		$parts['title'] = 'NuWare';
		$parts['site']  = 'Tech. Fundamentally Understood.';
	} else {
		$parts['site'] = 'NuWare';

		if ( is_page( 'ai' ) ) {
			$parts['title'] = 'AI';
		}
	}

	return $parts;
}
add_filter( 'document_title_separator', 'nuware_document_title_separator' );
add_filter( 'document_title_parts', 'nuware_document_title_parts', 20 );

function nuware_meta_description() {
	if ( is_front_page() ) {
		return 'NuWare helps businesses turn technology, data and AI into secure, scalable outcomes.';
	}

	if ( is_singular( 'case-studies' ) ) {
		return wp_trim_words( wp_strip_all_tags( get_the_excerpt() ?: get_the_content() ), 28, '' );
	}

	if ( is_page() ) {
		$descriptions = array(
			'ai'             => 'Explore NuWare AI advisory, strategy, incubation, infrastructure and security services.',
			'solutions'      => 'Explore NuWare solutions across applications, cloud, data and infrastructure.',
			'case-studies'   => 'See how NuWare helps organizations solve complex technology and business challenges.',
			'about'          => 'Learn about NuWare, our story, leadership and three decades of technology expertise.',
			'careers'        => 'Explore careers and current opportunities at NuWare.',
			'industries'     => 'Explore NuWare technology expertise across retail, banking, capital and healthcare.',
			'privacy-policy' => 'Read the NuWare Privacy Policy.',
			'terms'          => 'Read the NuWare Terms of Service.',
		);
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		if ( isset( $descriptions[ $slug ] ) ) {
			return $descriptions[ $slug ];
		}

		$parent_id = wp_get_post_parent_id( get_queried_object_id() );
		if ( $parent_id && 'industries' === get_post_field( 'post_name', $parent_id ) ) {
			return sprintf( 'Explore NuWare technology solutions and expertise for the %s industry.', get_the_title() );
		}

		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			return wp_trim_words( wp_strip_all_tags( $excerpt ), 28, '' );
		}
	}

	return '';
}

function nuware_social_url() {
	if ( is_singular() ) {
		return get_permalink();
	}
	return home_url( wp_parse_url( add_query_arg( array() ), PHP_URL_PATH ) ?: '/' );
}

function nuware_seo_meta() {
	if ( nuware_has_seo_plugin() || is_admin() || is_feed() || is_404() ) {
		return;
	}

	$description = nuware_meta_description();
	$title       = wp_get_document_title();
	$url         = nuware_social_url();
	$image       = get_template_directory_uri() . '/assets/images/about/brand-evolution/2026_Logo.jpg';
	if ( is_singular() && has_post_thumbnail() ) {
		$featured = wp_get_attachment_image_url( get_post_thumbnail_id(), 'full' );
		if ( $featured ) {
			$image = $featured;
		}
	}
	?>
	<?php if ( $description ) : ?><meta name="description" content="<?php echo esc_attr( $description ); ?>"><?php endif; ?>
	<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
	<?php if ( $description ) : ?><meta property="og:description" content="<?php echo esc_attr( $description ); ?>"><?php endif; ?>
	<meta property="og:url" content="<?php echo esc_url( $url ); ?>">
	<meta property="og:type" content="<?php echo is_singular( 'case-studies' ) ? 'article' : 'website'; ?>">
	<meta property="og:image" content="<?php echo esc_url( $image ); ?>">
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="<?php echo esc_attr( $title ); ?>">
	<?php if ( $description ) : ?><meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>"><?php endif; ?>
	<meta name="twitter:image" content="<?php echo esc_url( $image ); ?>">
	<?php
}
add_action( 'wp_head', 'nuware_seo_meta', 5 );

function nuware_schema() {
	if ( nuware_has_seo_plugin() || ! is_front_page() ) {
		return;
	}

	$home = home_url( '/' );
	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => array(
			array(
				'@type' => 'Organization',
				'@id'   => $home . '#organization',
				'name'  => 'NuWare',
				'url'   => $home,
				'logo'  => get_template_directory_uri() . '/assets/images/about/brand-evolution/2026_Logo.jpg',
				'sameAs' => array( 'https://www.linkedin.com/company/nuware' ),
			),
			array(
				'@type'     => 'WebSite',
				'@id'       => $home . '#website',
				'url'       => $home,
				'name'      => 'NuWare',
				'publisher' => array( '@id' => $home . '#organization' ),
			),
		),
	);
	printf( '<script type="application/ld+json">%s</script>' . "\n", wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
}
add_action( 'wp_head', 'nuware_schema', 6 );

<?php get_header(); ?>

<main id="primary" class="site-main">
	<section class="particle-hero" data-particle-text="Technology" data-hero-particles>
		<div class="particle-hero__canvas" id="hero-binary-particles" aria-hidden="true"></div>
		<h1 class="particle-hero__sr"><?php esc_html_e( 'Technology', 'nuware' ); ?></h1>
		<div class="particle-hero__text" aria-hidden="true"></div>

		<div class="particle-hero__content">
			<p class="particle-hero__subtitle"><?php esc_html_e( 'Fundamentally understood.', 'nuware' ); ?></p>
			<p class="particle-hero__description"><?php esc_html_e( 'From Assembler to AI, technology has changed dramatically. The fundamentals haven’t: logic, data, systems and sound engineering. That’s where NuWare’s strength has always been.', 'nuware' ); ?></p>
			<div class="particle-hero__prompt">
				<p class="particle-hero__question"><?php esc_html_e( 'What can we do for your business?', 'nuware' ); ?></p>
				<form class="particle-hero__form" action="" method="get">
					<label class="particle-hero__label" for="nuware-particle-ai-question"><?php esc_html_e( 'Ask NuWare AI a question', 'nuware' ); ?></label>
					<input
						class="particle-hero__input"
						id="nuware-particle-ai-question"
						name="nuware-ai-question"
						type="text"
						placeholder="<?php esc_attr_e( 'E.g Can you help us move from on-prem to cloud?', 'nuware' ); ?>"
					>
					<button class="particle-hero__submit" type="submit" aria-label="<?php esc_attr_e( 'Send question', 'nuware' ); ?>">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/send.svg' ); ?>" alt="" width="18" height="18">
					</button>
				</form>
			</div>
		</div>
	</section>

	<section class="careers-cta">
		<div class="careers-cta__media">
			<img
				class="careers-cta__image"
				src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/careers-team.png' ); ?>"
				alt="<?php esc_attr_e( 'NuWare team members working together in the office', 'nuware' ); ?>"
				width="1366"
				height="925"
				loading="lazy"
			>
		</div>

		<div class="careers-cta__content">
			<h2 class="careers-cta__title">
				<?php esc_html_e( 'Build what’s next.', 'nuware' ); ?><br>
				<span class="careers-cta__title-accent"><?php esc_html_e( 'With us.', 'nuware' ); ?></span>
			</h2>
			<p class="careers-cta__description"><?php esc_html_e( 'Join a team where your ideas matter, your work has impact, and there’s always something new to solve.', 'nuware' ); ?></p>
			<div class="careers-cta__actions">
				<a class="careers-cta__link" href="<?php echo esc_url( home_url( '/careers/' ) ); ?>">
					<?php esc_html_e( 'Explore Open Positions', 'nuware' ); ?>
				</a>
				<a class="careers-cta__link careers-cta__link--secondary" href="<?php echo esc_url( home_url( '/about/#our-story' ) ); ?>">
					<?php esc_html_e( 'Read our Story', 'nuware' ); ?>
				</a>
			</div>
		</div>
	</section>

	<?php
	// Edit the three AI carousel states here.
	$nuware_ai_carousel_slides = array(
		array(
			'title'       => __( 'Everything starts as abstract data.', 'nuware' ),
			'description' => __( 'A vast, unstructured universe of signals, systems and information waiting to be understood.', 'nuware' ),
			'link_label'  => __( 'Find the signal', 'nuware' ),
		),
		array(
			'title'       => __( 'AI gives data a form', 'nuware' ),
			'description' => __( 'We apply models, context and reasoning to uncover patterns, structure complexity and create usable intelligence.', 'nuware' ),
			'link_label'  => __( 'Put it in motion →', 'nuware' ),
		),
		array(
			'title'       => __( 'Intelligence scales into action.', 'nuware' ),
			'description' => __( 'We embed AI into products, workflows and operations—creating unique outcomes that can adapt, automate and scale.', 'nuware' ),
			'link_label'  => __( 'Explore our AI capabilities →', 'nuware' ),
			'link_url'    => '/ai/advisory/', // Replace with the final destination URL.
		),
	);
	?>

	<section class="ai-carousel" data-ai-carousel aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'From AI to Action', 'nuware' ); ?>">
		<div class="ai-carousel__background" id="ai-carousel-particles" data-ai-particles aria-hidden="true"></div>
		<div class="ai-carousel__inner">
			<header class="ai-carousel__intro">
				<h2 class="ai-carousel__eyebrow"><?php esc_html_e( 'From AI to Agentic Action', 'nuware' ); ?></h2>
				<p class="ai-carousel__summary"><?php esc_html_e( 'We turn enterprise data into intelligence—and intelligence into outcomes that move the business forward.', 'nuware' ); ?></p>
			</header>

			<div class="ai-carousel__globe-space" data-ai-globe-space aria-hidden="true"></div>
			<div class="ai-carousel__body">
				<div class="ai-carousel__controls" role="tablist" aria-label="<?php esc_attr_e( 'Choose carousel slide', 'nuware' ); ?>">
					<?php foreach ( $nuware_ai_carousel_slides as $nuware_slide_index => $nuware_slide ) : ?>
						<button
							class="ai-carousel__dot<?php echo 0 === $nuware_slide_index ? ' ai-carousel__dot--active' : ''; ?>"
							type="button"
							role="tab"
							aria-selected="<?php echo 0 === $nuware_slide_index ? 'true' : 'false'; ?>"
							aria-controls="ai-carousel-slide-<?php echo esc_attr( $nuware_slide_index + 1 ); ?>"
							data-ai-carousel-dot="<?php echo esc_attr( $nuware_slide_index ); ?>"
						>
							<span class="screen-reader-text"><?php printf( esc_html__( 'Show slide %d', 'nuware' ), esc_html( $nuware_slide_index + 1 ) ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="ai-carousel__slides">
					<?php foreach ( $nuware_ai_carousel_slides as $nuware_slide_index => $nuware_slide ) : ?>
						<article
							class="ai-carousel__slide"
							id="ai-carousel-slide-<?php echo esc_attr( $nuware_slide_index + 1 ); ?>"
							role="tabpanel"
							aria-hidden="<?php echo 0 === $nuware_slide_index ? 'false' : 'true'; ?>"
							<?php echo 0 === $nuware_slide_index ? '' : 'hidden'; ?>
						>
							<h3 class="ai-carousel__title"><?php echo esc_html( $nuware_slide['title'] ); ?></h3>
							<p class="ai-carousel__description"><?php echo esc_html( $nuware_slide['description'] ); ?></p>
							<?php if ( $nuware_slide_index < count( $nuware_ai_carousel_slides ) - 1 ) : ?>
								<button class="ai-carousel__link ai-carousel__link--outline" type="button" data-ai-carousel-next><?php echo esc_html( preg_replace( '/\s*→\s*$/u', '', $nuware_slide['link_label'] ) ); ?> <span aria-hidden="true">→</span></button>
							<?php else : ?>
								<a class="ai-carousel__link" href="<?php echo esc_url( $nuware_slide['link_url'] ); ?>"><?php echo esc_html( $nuware_slide['link_label'] ); ?></a>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</section>

	<?php
	$nuware_homepage_page_id = get_queried_object_id();

	if ( ! $nuware_homepage_page_id ) {
		$nuware_homepage_page    = get_page_by_path( 'homepage' );
		$nuware_homepage_page_id = $nuware_homepage_page instanceof WP_Post ? $nuware_homepage_page->ID : 0;
	}

	$nuware_case_study_terms = get_terms(
		array(
			'taxonomy'   => 'industry',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);
	$nuware_home_case_studies = array();
	$nuware_home_case_ids     = array();

	if ( ! is_wp_error( $nuware_case_study_terms ) ) {
		foreach ( $nuware_case_study_terms as $nuware_case_study_term ) {
			$nuware_term_query = array(
				'post_type'      => 'case-studies',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'tax_query'      => array(
					array(
						'taxonomy' => 'industry',
						'field'    => 'term_id',
						'terms'    => $nuware_case_study_term->term_id,
					),
				),
			);

			if ( $nuware_home_case_ids ) {
				$nuware_term_query['post__not_in'] = $nuware_home_case_ids;
			}

			$nuware_term_studies = get_posts( $nuware_term_query );

			// A study may belong to several industries. Reuse it only when that is
			// the sole way to ensure every industry is represented in the carousel.
			if ( ! $nuware_term_studies && $nuware_home_case_ids ) {
				unset( $nuware_term_query['post__not_in'] );
				$nuware_term_studies = get_posts( $nuware_term_query );
			}

			if ( $nuware_term_studies ) {
				$nuware_home_case_studies[] = $nuware_term_studies[0];
				$nuware_home_case_ids[]     = $nuware_term_studies[0]->ID;
			}
		}
	}
	?>

	<?php if ( $nuware_home_case_studies ) : ?>
		<section class="homepage-case-studies" data-homepage-case-studies aria-labelledby="homepage-case-studies-title">
			<div class="homepage-case-studies__inner">
				<header class="homepage-case-studies__header">
					<h2 id="homepage-case-studies-title"><?php esc_html_e( 'Case Studies', 'nuware' ); ?></h2>
					<a class="homepage-case-studies__view-all" href="<?php echo esc_url( home_url( '/case-studies/' ) ); ?>"><?php esc_html_e( 'View All', 'nuware' ); ?> <span aria-hidden="true">→</span></a>
				</header>
				<div id="homepage-case-studies-track" class="homepage-case-studies__track" tabindex="0" aria-label="<?php esc_attr_e( 'Featured case studies', 'nuware' ); ?>">
					<?php foreach ( $nuware_home_case_studies as $post ) : ?>
						<?php setup_postdata( $post ); ?>
						<?php get_template_part( 'template-parts/case-studies/card' ); ?>
					<?php endforeach; ?>
					<?php wp_reset_postdata(); ?>
				</div>
				<div class="homepage-case-studies__controls">
					<button type="button" data-home-case-previous aria-label="<?php esc_attr_e( 'Previous case study', 'nuware' ); ?>" aria-controls="homepage-case-studies-track">←</button>
					<span data-home-case-position role="status" aria-live="polite"></span>
					<button type="button" data-home-case-next aria-label="<?php esc_attr_e( 'Next case study', 'nuware' ); ?>" aria-controls="homepage-case-studies-track">→</button>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	$nuware_industries_page = get_page_by_path( 'industries' );
	$nuware_world_pages     = array();

	if ( $nuware_industries_page instanceof WP_Post ) {
		$nuware_menu_locations = get_nav_menu_locations();
		$nuware_primary_items  = ! empty( $nuware_menu_locations['primary'] )
			? wp_get_nav_menu_items( $nuware_menu_locations['primary'] )
			: array();
		$nuware_industries_menu_id = 0;

		foreach ( $nuware_primary_items ?: array() as $nuware_menu_item ) {
			if ( 'page' === $nuware_menu_item->object && (int) $nuware_menu_item->object_id === $nuware_industries_page->ID ) {
				$nuware_industries_menu_id = (int) $nuware_menu_item->ID;
				break;
			}
		}

		if ( $nuware_industries_menu_id ) {
			foreach ( $nuware_primary_items as $nuware_menu_item ) {
				if ( (int) $nuware_menu_item->menu_item_parent !== $nuware_industries_menu_id || 'page' !== $nuware_menu_item->object ) {
					continue;
				}

				$nuware_world_page = get_post( (int) $nuware_menu_item->object_id );

				if ( $nuware_world_page instanceof WP_Post && 'publish' === $nuware_world_page->post_status ) {
					$nuware_world_pages[] = $nuware_world_page;
				}
			}
		}

		if ( ! $nuware_world_pages ) {
			$nuware_world_pages = get_children(
				array(
					'post_parent' => $nuware_industries_page->ID,
					'post_type'   => 'page',
					'post_status' => 'publish',
					'numberposts' => 4,
					'orderby'     => 'ID',
					'order'       => 'ASC',
				)
			);
			$nuware_world_pages = array_values( $nuware_world_pages );
		}
	}

	$nuware_world_order = array(
		'capital'    => 0,
		'retail'     => 1,
		'banking'    => 2,
		'healthcare' => 3,
	);

	usort(
		$nuware_world_pages,
		static function ( $nuware_world_a, $nuware_world_b ) use ( $nuware_world_order ) {
			$nuware_world_a_order = $nuware_world_order[ $nuware_world_a->post_name ] ?? PHP_INT_MAX;
			$nuware_world_b_order = $nuware_world_order[ $nuware_world_b->post_name ] ?? PHP_INT_MAX;

			return $nuware_world_a_order <=> $nuware_world_b_order;
		}
	);

	$nuware_world_pages = array_slice( $nuware_world_pages, 0, 4 );
	$nuware_first_paragraph = static function ( $nuware_content ) use ( &$nuware_first_paragraph ) {
		foreach ( parse_blocks( $nuware_content ) as $nuware_block ) {
			if ( 'core/paragraph' === $nuware_block['blockName'] ) {
				$nuware_paragraph = trim( wp_strip_all_tags( $nuware_block['innerHTML'] ?? '' ) );

				if ( $nuware_paragraph ) {
					return $nuware_paragraph;
				}
			}

			if ( ! empty( $nuware_block['innerBlocks'] ) ) {
				$nuware_nested_paragraph = $nuware_first_paragraph( serialize_blocks( $nuware_block['innerBlocks'] ) );

				if ( $nuware_nested_paragraph ) {
					return $nuware_nested_paragraph;
				}
			}
		}

		return '';
	};
	?>

	<?php if ( $nuware_world_pages ) : ?>
		<section class="our-worlds" data-our-worlds-carousel aria-labelledby="our-worlds-title">
			<div class="our-worlds__inner">
				<header class="our-worlds__header">
					<h2 class="our-worlds__title" id="our-worlds-title"><?php esc_html_e( 'Four worlds.', 'nuware' ); ?> <span><?php esc_html_e( 'One Core.', 'nuware' ); ?></span></h2>
					<p class="our-worlds__intro"><?php esc_html_e( 'Our understanding of technology runs deep enough to work across industries—adapting the same fundamentals to very different challenges.', 'nuware' ); ?></p>
				</header>

				<div class="our-worlds__grid">
					<?php foreach ( $nuware_world_pages as $nuware_world_index => $nuware_world_page ) : ?>
						<?php
						// Read the image attachment from this industry's own ACF field.
						$nuware_world_icon = function_exists( 'get_field' ) ? get_field( 'world_icon', $nuware_world_page->ID, false ) : '';
						$nuware_world_icon_url = is_numeric( $nuware_world_icon )
							? wp_get_attachment_url( (int) $nuware_world_icon )
							: ( is_string( $nuware_world_icon ) ? $nuware_world_icon : '' );
						$nuware_world_intro_title = function_exists( 'get_field' ) ? trim( (string) get_field( 'title', $nuware_world_page->ID ) ) : '';
						$nuware_world_card_description = $nuware_world_intro_title ?: $nuware_first_paragraph( $nuware_world_page->post_content );
						?>
						<article class="our-worlds__card<?php echo 0 === $nuware_world_index ? ' our-worlds__card--active' : ''; ?>" data-world-card="<?php echo esc_attr( $nuware_world_index ); ?>">
							<div class="our-worlds__visual" aria-hidden="true">
								<img class="our-worlds__network" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/our-worlds-network.png' ); ?>" alt="" width="1254" height="1254" loading="lazy">
								<?php if ( $nuware_world_icon_url ) : ?>
									<img class="our-worlds__icon" src="<?php echo esc_url( $nuware_world_icon_url ); ?>" alt="" width="128" height="128" loading="lazy">
								<?php endif; ?>
							</div>
							<div class="our-worlds__content">
								<h3 class="our-worlds__card-title"><?php echo esc_html( get_the_title( $nuware_world_page ) ); ?></h3>
								<p class="our-worlds__description"><?php echo esc_html( $nuware_world_card_description ); ?></p>
								<a class="our-worlds__link" href="<?php echo esc_url( get_permalink( $nuware_world_page ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Explore %s', 'nuware' ), get_the_title( $nuware_world_page ) ) ); ?>">→</a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>

				<div class="our-worlds__carousel-controls" aria-label="<?php esc_attr_e( 'Navigate industry worlds', 'nuware' ); ?>">
					<button class="our-worlds__carousel-button" type="button" data-world-previous aria-label="<?php esc_attr_e( 'Show previous world', 'nuware' ); ?>">‹</button>
					<div class="our-worlds__pagination" aria-hidden="true">
						<?php foreach ( $nuware_world_pages as $nuware_world_index => $nuware_world_page ) : ?>
							<span class="our-worlds__pagination-dot<?php echo 0 === $nuware_world_index ? ' our-worlds__pagination-dot--active' : ''; ?>" data-world-dot="<?php echo esc_attr( $nuware_world_index ); ?>"></span>
						<?php endforeach; ?>
					</div>
					<button class="our-worlds__carousel-button" type="button" data-world-next aria-label="<?php esc_attr_e( 'Show next world', 'nuware' ); ?>">›</button>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	// ACF field group: Quote Homepage.
	$nuware_homepage_quote = function_exists( 'get_field' ) && $nuware_homepage_page_id
		? get_field( 'quote', $nuware_homepage_page_id )
		: '';
	$nuware_homepage_quote_author = function_exists( 'get_field' ) && $nuware_homepage_page_id
		? get_field( 'quote_author', $nuware_homepage_page_id )
		: '';
	?>

	<?php if ( is_string( $nuware_homepage_quote ) && trim( $nuware_homepage_quote ) !== '' ) : ?>
		<section class="homepage-quote" aria-label="<?php esc_attr_e( 'A word from NuWare', 'nuware' ); ?>">
			<figure class="homepage-quote__inner">
				<blockquote class="homepage-quote__text"><?php echo nl2br( esc_html( $nuware_homepage_quote ) ); ?></blockquote>
				<?php if ( is_string( $nuware_homepage_quote_author ) && trim( $nuware_homepage_quote_author ) !== '' ) : ?>
					<figcaption class="homepage-quote__author"><?php echo esc_html( $nuware_homepage_quote_author ); ?></figcaption>
				<?php endif; ?>
			</figure>
		</section>
	<?php endif; ?>
	<?php get_template_part( 'template-parts/sections/homepage-solutions' ); ?>
</main>

<?php get_template_part( 'template-parts/sections/hero-answer' ); ?>
<?php get_footer(); ?>

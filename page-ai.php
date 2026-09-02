<?php
/** AI landing page. Hero copy is fixed; tab copy stays in Gutenberg. */
get_header();
$ai_slugs = array( 'advisory', 'ai-incubation', 'ai-infra-security', 'ai-strategy-value' );
$ai_tab = sanitize_title( (string) get_query_var( 'ai_tab', 'advisory' ) );
$ai_index = array_search( $ai_tab, $ai_slugs, true );
$ai_index = false === $ai_index ? 0 : $ai_index;

// Retain rendered Gutenberg markup/content but give this page one URL-aware
// tab controller. Remove only core/tabs directives, not nested block behavior.
$ai_tab_renderer = static function ( $html ) use ( $ai_slugs, $ai_index ) {
	$tags = new WP_HTML_Tag_Processor( $html );
	$button = 0;
	$panel = 0;
	while ( $tags->next_tag() ) {
		$role = $tags->get_attribute( 'role' );
		$is_root = $tags->has_class( 'wp-block-tabs' );
		if ( ! $is_root && ! in_array( $role, array( 'tab', 'tabpanel' ), true ) ) { continue; }
		foreach ( array( 'data-wp-context', 'data-wp-interactive', 'data-wp-init', 'data-wp-on--keydown', 'data-wp-on--click', 'data-wp-bind--aria-selected', 'data-wp-bind--tabindex', 'data-wp-bind--hidden' ) as $attribute ) {
			$tags->remove_attribute( $attribute );
		}
		if ( $is_root ) { $tags->set_attribute( 'data-ai-tabs', '' ); }
		if ( 'tab' === $role ) {
			$tags->set_attribute( 'data-ai-tab', $ai_slugs[ $button ] ?? 'tab-' . ( $button + 1 ) );
			$tags->set_attribute( 'aria-selected', $button === $ai_index ? 'true' : 'false' );
			$tags->set_attribute( 'tabindex', $button === $ai_index ? '0' : '-1' );
			++$button;
		}
		if ( 'tabpanel' === $role ) {
			if ( $panel === $ai_index ) { $tags->remove_attribute( 'hidden' ); } else { $tags->set_attribute( 'hidden', true ); }
			++$panel;
		}
	}
	return $tags->get_updated_html();
};
?>
<main id="primary" class="ai-page" data-ai-base-url="<?php echo esc_url( home_url( '/ai/' ) ); ?>">
	<section class="ai-page__hero" aria-labelledby="ai-title" data-ai-hero>
		<div id="ai-page-particles" class="ai-page__particles" data-ai-particles aria-hidden="true"></div>
		<div class="ai-page__container ai-page__hero-content">
			<h1 id="ai-title" class="ai-page__title">We empower businesses through Intelligent AI Transformation</h1>
			<p class="ai-page__description">AI creates value when it moves from idea to impact. NuWare utilizes Ai to transform business through its own process. NuWare brings strategy, engineering and infrastructure together to turn AI into measurable business outcomes.</p>
			<a class="ai-page__cta" href="<?php echo esc_url( home_url( '/get-in-touch/' ) ); ?>" data-contact-trigger>LET’S COLLABORATE <span aria-hidden="true">→</span></a>
		</div>
	</section>
	<section class="ai-page__services" aria-label="AI services">
		<div class="ai-page__container ai-page__content">
			<?php
			while ( have_posts() ) : the_post();
				add_filter( 'render_block_core/tabs', $ai_tab_renderer );
				the_content();
				remove_filter( 'render_block_core/tabs', $ai_tab_renderer );
			endwhile;
			?>
		</div>
	</section>
	<?php
	$ai_studies = new WP_Query( array(
		'post_type' => 'case-studies', 'post_status' => 'publish', 'posts_per_page' => -1,
		'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true,
		'tax_query' => array( array( 'taxonomy' => 'industry', 'field' => 'slug', 'terms' => 'ai' ) ),
	) );
	if ( $ai_studies->have_posts() ) : ?>
		<section class="ai-page__studies case-library" aria-labelledby="ai-studies-title" data-ai-studies>
			<div class="ai-page__container">
				<div class="ai-page__studies-header"><h2 id="ai-studies-title">AI in action</h2><a href="<?php echo esc_url( home_url( '/case-studies/?cs_category=ai' ) ); ?>">View all AI case studies <span aria-hidden="true">→</span></a></div>
				<div id="ai-studies-track" class="ai-page__track" tabindex="0" aria-label="AI case studies carousel">
					<?php while ( $ai_studies->have_posts() ) : $ai_studies->the_post(); get_template_part( 'template-parts/case-studies/card' ); endwhile; ?>
				</div>
				<div class="ai-page__controls" hidden>
					<button type="button" data-ai-previous aria-label="Previous case study" aria-controls="ai-studies-track">←</button>
					<span data-ai-position role="status" aria-live="polite"></span>
					<button type="button" data-ai-next aria-label="Next case study" aria-controls="ai-studies-track">→</button>
				</div>
			</div>
		</section>
	<?php endif; wp_reset_postdata(); ?>
</main>
<?php get_footer(); ?>

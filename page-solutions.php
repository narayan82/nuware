<?php
/**
 * Solutions landing page. Intro and tabs are managed in Gutenberg.
 */

get_header();

$solution_slugs = array( 'applications', 'cloud', 'data', 'infrastructure' );
$solution_tab   = sanitize_title( (string) get_query_var( 'solution_tab', 'applications' ) );
$solution_index = array_search( $solution_tab, $solution_slugs, true );
$solution_index = false === $solution_index ? 0 : $solution_index;
$solution_blocks = array();
$solution_intro  = array();

while ( have_posts() ) {
	the_post();
	$blocks = parse_blocks( get_the_content() );
	$tabs_started = false;

	foreach ( $blocks as $block ) {
		if ( 'core/tabs' === $block['blockName'] ) {
			$tabs_started = true;
		}

		if ( $tabs_started ) {
			$solution_blocks[] = $block;
		} elseif ( in_array( $block['blockName'], array( 'core/heading', 'core/paragraph' ), true ) ) {
			$solution_intro[] = $block;
		}
	}
}

$solution_tab_renderer = static function ( $html ) use ( $solution_slugs, $solution_index ) {
	$tags   = new WP_HTML_Tag_Processor( $html );
	$button = 0;
	$panel  = 0;

	while ( $tags->next_tag() ) {
		$role    = $tags->get_attribute( 'role' );
		$is_root = $tags->has_class( 'wp-block-tabs' );

		if ( ! $is_root && ! in_array( $role, array( 'tab', 'tabpanel' ), true ) ) {
			continue;
		}

		foreach ( array( 'data-wp-context', 'data-wp-interactive', 'data-wp-init', 'data-wp-on--keydown', 'data-wp-on--click', 'data-wp-bind--aria-selected', 'data-wp-bind--tabindex', 'data-wp-bind--hidden' ) as $attribute ) {
			$tags->remove_attribute( $attribute );
		}

		if ( $is_root ) {
			$tags->set_attribute( 'data-solutions-tabs', '' );
		}

		if ( 'tab' === $role ) {
			$tags->set_attribute( 'data-solutions-tab', $solution_slugs[ $button ] ?? 'tab-' . ( $button + 1 ) );
			$tags->set_attribute( 'aria-selected', $solution_index === $button ? 'true' : 'false' );
			$tags->set_attribute( 'tabindex', $solution_index === $button ? '0' : '-1' );
			++$button;
		}

		if ( 'tabpanel' === $role ) {
			if ( $solution_index === $panel ) {
				$tags->remove_attribute( 'hidden' );
			} else {
				$tags->set_attribute( 'hidden', true );
			}
			++$panel;
		}
	}

	return $tags->get_updated_html();
};
?>
<main id="primary" class="solutions-page" data-solutions-base-url="<?php echo esc_url( home_url( '/solutions/' ) ); ?>">
	<section class="solutions-page__hero" aria-labelledby="solutions-title">
		<div class="solutions-page__hero-shade" aria-hidden="true"></div>
		<div class="solutions-page__container solutions-page__hero-content">
			<?php foreach ( $solution_intro as $index => $block ) : ?>
				<?php if ( 0 === $index && 'core/heading' === $block['blockName'] ) : ?>
					<h1 id="solutions-title" class="solutions-page__title"><?php echo esc_html( wp_strip_all_tags( render_block( $block ) ) ); ?></h1>
				<?php else : ?>
					<div class="solutions-page__intro-copy"><?php echo wp_kses_post( render_block( $block ) ); ?></div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</section>

	<section class="solutions-page__services" aria-label="NuWare solutions">
		<div class="solutions-page__container solutions-page__content">
			<?php
			add_filter( 'render_block_core/tabs', $solution_tab_renderer );
			echo do_blocks( serialize_blocks( $solution_blocks ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			remove_filter( 'render_block_core/tabs', $solution_tab_renderer );
			?>
		</div>
	</section>

	<?php
	$solution_labels = array(
		'applications'   => 'Applications',
		'cloud'          => 'Cloud',
		'data'           => 'Data',
		'infrastructure' => 'Infrastructure',
	);

	foreach ( $solution_labels as $study_slug => $study_label ) :
		$solution_studies = new WP_Query(
			array(
				'post_type'      => 'case-studies',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'tax_query'      => array(
					array(
						'taxonomy' => 'industry',
						'field'    => 'slug',
						'terms'    => $study_slug,
					),
				),
			)
		);

		if ( ! $solution_studies->have_posts() ) {
			wp_reset_postdata();
			continue;
		}
		?>
		<section
			class="solutions-page__studies case-library"
			aria-labelledby="solutions-studies-<?php echo esc_attr( $study_slug ); ?>"
			data-solutions-studies-panel="<?php echo esc_attr( $study_slug ); ?>"
			<?php echo $study_slug === $solution_tab ? '' : ' hidden'; ?>
		>
			<div class="solutions-page__container">
				<div class="solutions-page__studies-header">
					<h2 id="solutions-studies-<?php echo esc_attr( $study_slug ); ?>"><?php echo esc_html( $study_label ); ?> in action</h2>
					<a href="<?php echo esc_url( home_url( '/case-studies/?cs_category=' . $study_slug ) ); ?>">View all <?php echo esc_html( $study_label ); ?> case studies <span aria-hidden="true">→</span></a>
				</div>
				<div id="solutions-studies-track-<?php echo esc_attr( $study_slug ); ?>" class="solutions-page__track" tabindex="0" aria-label="<?php echo esc_attr( $study_label ); ?> case studies carousel">
					<?php
					while ( $solution_studies->have_posts() ) {
						$solution_studies->the_post();
						get_template_part( 'template-parts/case-studies/card' );
					}
					?>
				</div>
				<div class="solutions-page__controls" hidden>
					<button type="button" data-solutions-previous aria-label="Previous case study" aria-controls="solutions-studies-track-<?php echo esc_attr( $study_slug ); ?>">←</button>
					<span data-solutions-position role="status" aria-live="polite"></span>
					<button type="button" data-solutions-next aria-label="Next case study" aria-controls="solutions-studies-track-<?php echo esc_attr( $study_slug ); ?>">→</button>
				</div>
			</div>
		</section>
		<?php wp_reset_postdata(); ?>
	<?php endforeach; ?>
</main>
<?php get_footer(); ?>

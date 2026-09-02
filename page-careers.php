<?php
/**
 * Careers page hero.
 */

get_header();

$career_lines = array(
	'We coded when Undo went only one step back.',
	'We coded when a hard drive held less than today’s RAM.',
	'We debugged before instant answers existed.',
);

$career_hero_blocks    = array();
$career_process_blocks = array();
$career_benefits_blocks = array();

while ( have_posts() ) {
	the_post();

	foreach ( parse_blocks( get_the_content() ) as $career_block ) {
		$block_class = isset( $career_block['attrs']['className'] ) ? (string) $career_block['attrs']['className'] : '';

		if ( str_contains( $block_class, 'careers-benefits-content' ) ) {
			$career_benefits_blocks[] = $career_block;
		} elseif ( str_contains( $block_class, 'careers-process-content' ) ) {
			$career_process_blocks[] = $career_block;
		} else {
			$career_hero_blocks[] = $career_block;
		}
	}
}

$career_hero_content = apply_filters( 'the_content', serialize_blocks( $career_hero_blocks ) );
$career_process      = $career_process_blocks
	? apply_filters( 'the_content', serialize_blocks( $career_process_blocks ) )
	: '';
$career_benefits     = $career_benefits_blocks
	? apply_filters( 'the_content', serialize_blocks( $career_benefits_blocks ) )
	: '';

$career_positions = new WP_Query(
	array(
		'post_type'      => 'position',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array(
			'menu_order' => 'ASC',
			'title'      => 'ASC',
		),
	)
);

$career_get_field = static function ( string $field, int $post_id ) {
	return function_exists( 'get_field' ) ? get_field( $field, $post_id ) : get_post_meta( $post_id, $field, true );
};
?>
<main id="primary" class="careers-page">
	<section class="careers-page__hero" aria-labelledby="careers-title">
		<div class="careers-page__container">
				<h1 id="careers-title" class="careers-page__animated-title">
					<span class="screen-reader-text"><?php esc_html_e( 'Careers at NuWare.', 'nuware' ); ?></span>
					<span
						class="careers-page__typing"
						data-careers-typing
						data-lines="<?php echo esc_attr( wp_json_encode( $career_lines ) ); ?>"
						aria-hidden="true"
					>
						<span class="careers-page__typing-text" data-careers-typing-text></span><span class="careers-page__cursor" aria-hidden="true"></span>
					</span>
				</h1>

				<div class="careers-page__content">
					<?php echo wp_kses_post( $career_hero_content ); ?>
				</div>
		</div>
	</section>

	<?php if ( $career_process ) : ?>
		<section id="hiring-process" class="careers-page__process">
			<div class="careers-page__container">
				<?php echo wp_kses_post( $career_process ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $career_benefits ) : ?>
		<section class="careers-page__benefits">
			<div class="careers-page__container">
				<?php echo wp_kses_post( $career_benefits ); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $career_positions->have_posts() ) : ?>
		<section id="open-positions" class="careers-page__positions" aria-labelledby="open-positions-title" data-careers-positions>
			<div class="careers-page__container">
				<header class="careers-positions__header">
					<h2 id="open-positions-title"><?php esc_html_e( 'Open positions', 'nuware' ); ?></h2>
					<p><?php esc_html_e( 'Explore current opportunities and apply directly.', 'nuware' ); ?></p>
				</header>

				<div class="careers-positions__grid">
					<?php while ( $career_positions->have_posts() ) : ?>
						<?php
						$career_positions->the_post();
						$position_id          = get_the_ID();
						$position_name        = $career_get_field( 'position_name', $position_id ) ?: get_the_title();
						$position_job_code    = $career_get_field( 'job_code', $position_id );
						$position_mode        = $career_get_field( 'mode', $position_id );
						$position_min         = $career_get_field( 'min_experience', $position_id );
						$position_max         = $career_get_field( 'max_experience', $position_id );
						$position_location    = $career_get_field( 'location', $position_id );
						$position_description = $career_get_field( 'description', $position_id );
						$position_dialog_id   = 'position-details-' . $position_id;
						$position_experience  = $position_min;

						if ( '' !== (string) $position_max && (string) $position_max !== (string) $position_min ) {
							$position_experience .= ' – ' . $position_max;
						}
						?>
						<article class="careers-position-card">
							<button class="careers-position-card__button" type="button" data-position-open data-position-template="<?php echo esc_attr( $position_dialog_id ); ?>" aria-haspopup="dialog">
								<span class="careers-position-card__title"><?php echo esc_html( $position_name ); ?></span>
								<span class="careers-position-card__meta">
									<?php if ( '' !== (string) $position_experience ) : ?>
										<span><?php echo esc_html( $position_experience ); ?> <?php esc_html_e( 'Years Experience', 'nuware' ); ?></span>
									<?php endif; ?>
									<?php if ( $position_location ) : ?>
										<span><?php esc_html_e( 'Location:', 'nuware' ); ?> <?php echo esc_html( $position_location ); ?></span>
									<?php endif; ?>
								</span>
								<span class="careers-position-card__link"><?php esc_html_e( 'Find out More', 'nuware' ); ?> <span aria-hidden="true">→</span></span>
							</button>

							<template id="<?php echo esc_attr( $position_dialog_id ); ?>">
								<div class="position-drawer__job">
									<p class="position-drawer__eyebrow"><?php esc_html_e( 'Open position', 'nuware' ); ?></p>
									<h2 class="position-drawer__title" data-position-title><?php echo esc_html( $position_name ); ?></h2>
									<dl class="position-drawer__meta">
										<?php if ( $position_job_code ) : ?><div><dt><?php esc_html_e( 'Job code', 'nuware' ); ?></dt><dd><?php echo esc_html( $position_job_code ); ?></dd></div><?php endif; ?>
										<?php if ( $position_mode ) : ?><div><dt><?php esc_html_e( 'Mode', 'nuware' ); ?></dt><dd><?php echo esc_html( $position_mode ); ?></dd></div><?php endif; ?>
										<?php if ( '' !== (string) $position_experience ) : ?><div><dt><?php esc_html_e( 'Experience', 'nuware' ); ?></dt><dd><?php echo esc_html( $position_experience ); ?> <?php esc_html_e( 'years', 'nuware' ); ?></dd></div><?php endif; ?>
										<?php if ( $position_location ) : ?><div><dt><?php esc_html_e( 'Location', 'nuware' ); ?></dt><dd><?php echo esc_html( $position_location ); ?></dd></div><?php endif; ?>
									</dl>
									<div class="position-drawer__description"><?php echo wp_kses_post( $position_description ); ?></div>
					<a class="position-drawer__apply" href="#nuware-application-overlay" data-application-trigger><?php esc_html_e( 'Apply Now', 'nuware' ); ?> <span aria-hidden="true">→</span></a>
								</div>
							</template>
						</article>
					<?php endwhile; ?>
				</div>
			</div>
		</section>

		<dialog class="position-drawer" id="position-drawer" data-position-drawer aria-labelledby="position-drawer-title">
			<div class="position-drawer__inner">
				<button class="position-drawer__close" type="button" data-position-close aria-label="<?php esc_attr_e( 'Close position details', 'nuware' ); ?>">
					<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
				</button>
				<div class="position-drawer__content" data-position-content></div>
			</div>
		</dialog>
		<?php wp_reset_postdata(); ?>
	<?php endif; ?>
</main>
<?php get_footer(); ?>

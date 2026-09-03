<?php
/**
 * Shared template for pages directly beneath the Industries / Our Worlds page.
 */

$nuware_world_page_id   = get_the_ID();
$nuware_world_parent_id = wp_get_post_parent_id( $nuware_world_page_id );
$nuware_world_parent    = $nuware_world_parent_id ? get_post( $nuware_world_parent_id ) : null;
$nuware_world_title       = function_exists( 'get_field' ) ? trim( (string) get_field( 'title', $nuware_world_page_id ) ) : '';
$nuware_world_description = function_exists( 'get_field' ) ? trim( (string) get_field( 'description', $nuware_world_page_id ) ) : '';
$nuware_world_body        = apply_filters( 'the_content', (string) get_post_field( 'post_content', $nuware_world_page_id ) );

$nuware_timeline = function_exists( 'get_field' ) ? get_field( 'timeline', $nuware_world_page_id ) : array();
$nuware_timeline = is_array( $nuware_timeline ) ? array_values( array_filter( $nuware_timeline, 'is_array' ) ) : array();

get_header();
?>

<main id="primary" class="world-page">
	<section class="world-page__hero" aria-labelledby="world-page-title">
		<div class="world-page__timeline-light" data-world-timeline-light aria-hidden="true"></div>
		<div class="world-page__container world-page__hero-inner">
			<p class="world-page__eyebrow">
				<?php if ( $nuware_world_parent instanceof WP_Post ) : ?>
					<span><?php echo esc_html( get_the_title( $nuware_world_parent ) ); ?></span>
					<span aria-hidden="true"> / </span>
				<?php endif; ?>
				<span><?php the_title(); ?></span>
			</p>
			<h1 id="world-page-title" class="world-page__statement">
				<?php echo esc_html( $nuware_world_title ?: get_the_title() ); ?>
			</h1>

			<?php if ( $nuware_world_description ) : ?>
				<div class="world-page__support"><?php echo wp_kses_post( $nuware_world_description ); ?></div>
			<?php endif; ?>

			<?php if ( $nuware_timeline ) : ?>
				<div class="world-page__timeline" aria-label="<?php esc_attr_e( 'Industry timeline', 'nuware' ); ?>">
					<div class="world-page__timeline-scroll">
						<ol class="world-page__timeline-track" style="--world-timeline-items: <?php echo esc_attr( count( $nuware_timeline ) ); ?>;">
							<?php foreach ( $nuware_timeline as $nuware_milestone ) : ?>
								<li class="world-page__milestone">
									<span class="world-page__year"><?php echo esc_html( $nuware_milestone['date'] ?? '' ); ?></span>
									<span class="world-page__marker" aria-hidden="true"></span>
									<?php if ( ! empty( $nuware_milestone['title'] ) ) : ?>
										<h2 class="world-page__milestone-title"><?php echo esc_html( $nuware_milestone['title'] ); ?></h2>
									<?php endif; ?>
									<?php if ( ! empty( $nuware_milestone['description'] ) ) : ?>
										<div class="world-page__milestone-description"><?php echo wp_kses_post( wpautop( $nuware_milestone['description'] ) ); ?></div>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ol>
					</div>
					<div class="world-page__timeline-nav" aria-label="<?php esc_attr_e( 'Timeline navigation', 'nuware' ); ?>">
						<button class="world-page__timeline-arrow" type="button" data-timeline-previous aria-label="<?php esc_attr_e( 'Scroll timeline left', 'nuware' ); ?>">
							<span aria-hidden="true">‹</span>
						</button>
						<button class="world-page__timeline-arrow" type="button" data-timeline-next aria-label="<?php esc_attr_e( 'Scroll timeline right', 'nuware' ); ?>">
							<span aria-hidden="true">›</span>
						</button>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( trim( $nuware_world_body ) ) : ?>
		<section class="world-page__body">
			<div class="world-page__container world-page__content">
				<?php echo wp_kses_post( $nuware_world_body ); ?>
			</div>
		</section>
	<?php endif; ?>
</main>

<?php get_footer(); ?>

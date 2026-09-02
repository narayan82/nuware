<?php
/**
 * About page — opening section only.
 */

get_header();

while ( have_posts() ) {
	the_post();
	$about_body_blocks  = array();
	$about_quote_blocks = array();

	foreach ( parse_blocks( get_the_content() ) as $about_block ) {
		if ( 'core/quote' === $about_block['blockName'] ) {
			$about_quote_blocks[] = $about_block;
		} else {
			$about_body_blocks[] = $about_block;
		}
	}

	$about_content = apply_filters( 'the_content', serialize_blocks( $about_body_blocks ) );
	$about_quote   = $about_quote_blocks
		? apply_filters( 'the_content', serialize_blocks( $about_quote_blocks ) )
		: '';
	$about_tags    = new WP_HTML_Tag_Processor( $about_content );

	while ( $about_tags->next_tag( 'a' ) ) {
		if ( $about_tags->has_class( 'wp-block-button__link' ) ) {
			$about_tags->set_attribute( 'href', home_url( '/get-in-touch/' ) );
			$about_tags->set_attribute( 'data-contact-trigger', '' );
		}
	}

	$about_content = $about_tags->get_updated_html();
}

$technologies = function_exists( 'get_field' ) ? get_field( 'technologies' ) : array();
$technologies = is_array( $technologies ) ? $technologies : array();
$leadership   = function_exists( 'get_field' ) ? get_field( 'leadership' ) : array();
$leadership   = is_array( $leadership ) ? $leadership : array();

$render_technologies = static function ( $items ) {
	foreach ( $items as $technology ) {
		$name       = isset( $technology['name'] ) ? trim( (string) $technology['name'] ) : '';
		$icon       = $technology['tech_icon'] ?? null;
		$attachment = is_array( $icon ) ? (int) ( $icon['ID'] ?? $icon['id'] ?? 0 ) : (int) $icon;
		$link        = $technology['link'] ?? '';
		$link_url    = is_array( $link ) ? (string) ( $link['url'] ?? '' ) : (string) $link;
		$link_target = is_array( $link ) ? (string) ( $link['target'] ?? '' ) : '';

		if ( '' === $name && ! $attachment ) {
			continue;
		}
		?>
		<?php if ( $link_url ) : ?>
			<a
				class="about-page__technology about-page__technology--linked"
				href="<?php echo esc_url( $link_url ); ?>"
				target="_blank"
				<?php if ( $link_target ) : ?>target="<?php echo esc_attr( $link_target ); ?>"<?php endif; ?>
				<?php if ( '_blank' === $link_target ) : ?>rel="noopener noreferrer"<?php endif; ?>
			>
		<?php else : ?>
			<div class="about-page__technology">
		<?php endif; ?>
			<?php
			if ( $attachment ) {
				echo wp_get_attachment_image(
					$attachment,
					'thumbnail',
					false,
					array(
						'class'   => 'about-page__technology-icon',
						'loading' => 'eager',
						'alt'     => '',
					)
				); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
			<?php if ( '' !== $name ) : ?>
				<span class="about-page__technology-name"><?php echo esc_html( $name ); ?></span>
			<?php endif; ?>
		<?php if ( $link_url ) : ?>
			</a>
		<?php else : ?>
			</div>
		<?php endif; ?>
		<?php
	}
};
?>
<main id="primary" class="about-page">
	<section class="about-page__hero" aria-labelledby="about-title">
		<div class="about-page__container">
			<div class="about-page__intro">
				<h1 id="about-title" class="about-page__title"><?php the_title(); ?></h1>
				<div class="about-page__copy">
					<?php echo wp_kses_post( $about_content ); ?>
				</div>
			</div>

			<?php if ( $technologies ) : ?>
				<div class="about-page__technologies">
					<h2 class="about-page__technologies-title">Technologies we work with</h2>
					<div class="about-page__technology-viewport" aria-label="Technologies we work with">
						<div class="about-page__technology-track">
							<div class="about-page__technology-group">
								<?php $render_technologies( $technologies ); ?>
							</div>
							<div class="about-page__technology-group" aria-hidden="true">
								<?php $render_technologies( $technologies ); ?>
							</div>
						</div>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php if ( $leadership ) : ?>
		<section class="about-page__leadership" aria-labelledby="leadership-title">
			<div class="about-page__container">
				<header class="about-page__leadership-header">
					<h2 id="leadership-title" class="about-page__leadership-title">Leadership</h2>
					<p class="about-page__leadership-intro">True leaders of adapting, evolving and building what comes next.</p>
				</header>

				<div class="about-page__leadership-viewport" aria-label="NuWare leadership team" tabindex="0">
					<div class="about-page__leadership-grid">
						<?php foreach ( $leadership as $leader ) : ?>
							<?php
							$name        = isset( $leader['name'] ) ? trim( (string) $leader['name'] ) : '';
							$designation = isset( $leader['designation'] ) ? trim( (string) $leader['designation'] ) : '';
							$photo       = $leader['photo'] ?? null;
							$photo_id    = is_array( $photo ) ? (int) ( $photo['ID'] ?? $photo['id'] ?? 0 ) : (int) $photo;

							if ( '' === $name && '' === $designation && ! $photo_id ) {
								continue;
							}
							?>
							<article class="about-page__leader">
								<div class="about-page__leader-photo-wrap">
									<?php if ( $photo_id ) : ?>
										<?php
										echo wp_get_attachment_image(
											$photo_id,
											'medium_large',
											false,
											array(
												'class'   => 'about-page__leader-photo',
												'alt'     => $name,
												'loading' => 'lazy',
											)
										);
										?>
									<?php endif; ?>
								</div>
								<div class="about-page__leader-details">
									<?php if ( $name ) : ?>
										<h3 class="about-page__leader-name"><?php echo esc_html( $name ); ?></h3>
									<?php endif; ?>
									<?php if ( $designation ) : ?>
										<p class="about-page__leader-designation"><?php echo esc_html( $designation ); ?></p>
									<?php endif; ?>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $about_quote ) : ?>
		<section class="about-page__quote" aria-label="NuWare quote">
			<div class="about-page__quote-content">
				<?php echo wp_kses_post( $about_quote ); ?>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php get_footer(); ?>

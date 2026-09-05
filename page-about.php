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
$our_story   = function_exists( 'get_field' ) ? get_field( 'our_story' ) : array();
$our_story   = is_array( $our_story ) ? $our_story : array();
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
				target="<?php echo esc_attr( $link_target ?: '_blank' ); ?>"
				<?php if ( ! $link_target || '_blank' === $link_target ) : ?>rel="noopener noreferrer"<?php endif; ?>
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

	<?php if ( $our_story ) : ?>
		<section class="about-page__story" id="our-story" aria-labelledby="our-story-title" data-about-story>
			<div class="about-page__container">
				<header class="about-page__story-header">
					<h2 id="our-story-title" class="about-page__story-title">Our Story</h2>
					<p class="about-page__story-intro">Three decades of adapting, evolving and building what comes next.</p>
				</header>
				<div class="about-page__story-slides" aria-live="polite">
					<?php foreach ( $our_story as $story_index => $story ) : ?>
						<?php
						$story_year    = trim( (string) ( $story['year'] ?? '' ) );
						$story_title   = trim( (string) ( $story['title'] ?? '' ) );
						$story_caption = (string) ( $story['caption'] ?? '' );
						$story_image   = $story['image'] ?? null;
						$story_image_id = is_array( $story_image ) ? (int) ( $story_image['ID'] ?? $story_image['id'] ?? 0 ) : (int) $story_image;
						?>
						<article class="about-page__story-slide" data-story-slide <?php echo 0 !== $story_index ? 'hidden' : ''; ?>>
							<?php if ( $story_image_id ) : ?>
								<?php echo wp_get_attachment_image( $story_image_id, 'full', false, array( 'class' => 'about-page__story-image', 'alt' => $story_title, 'loading' => 0 === $story_index ? 'eager' : 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php endif; ?>
							<div class="about-page__story-overlay">
								<?php if ( $story_title ) : ?><h3><?php echo esc_html( $story_title ); ?></h3><?php endif; ?>
								<?php if ( $story_caption ) : ?><div><?php echo wp_kses_post( wpautop( $story_caption ) ); ?></div><?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
				<div class="about-page__story-nav" aria-label="Our Story timeline">
					<button type="button" class="about-page__story-arrow" data-story-prev aria-label="Previous story">←</button>
					<div class="about-page__story-years">
						<?php foreach ( $our_story as $story_index => $story ) : ?>
							<button type="button" data-story-year aria-pressed="<?php echo 0 === $story_index ? 'true' : 'false'; ?>"><?php echo esc_html( $story['year'] ?? '' ); ?></button>
						<?php endforeach; ?>
					</div>
					<button type="button" class="about-page__story-arrow" data-story-next aria-label="Next story">→</button>
				</div>
			</div>
		</section>
	<?php endif; ?>

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
							$linkedin    = $leader['linkedin'] ?? '';
							$linkedin_url = is_array( $linkedin ) ? trim( (string) ( $linkedin['url'] ?? '' ) ) : trim( (string) $linkedin );

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
									<?php if ( $linkedin_url ) : ?>
										<a class="about-page__leader-linkedin" href="<?php echo esc_url( $linkedin_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( sprintf( 'View %s on LinkedIn', $name ?: 'this leader' ) ); ?>">
											<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/about/linkedin.png' ); ?>" alt="" width="128" height="128" aria-hidden="true">
										</a>
									<?php endif; ?>
								</div>
								<div class="about-page__leader-details">
									<?php if ( $name ) : ?>
										<h3 class="about-page__leader-name"><?php echo esc_html( $name ); ?></h3>
									<?php endif; ?>
									<div class="about-page__leader-meta">
										<?php if ( $designation ) : ?>
											<p class="about-page__leader-designation"><?php echo esc_html( $designation ); ?></p>
										<?php endif; ?>
									</div>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<section class="about-page__brand-evolution" aria-labelledby="brand-evolution-title">
		<div class="about-page__container">
			<h2 id="brand-evolution-title" class="about-page__brand-evolution-title">Evolution of the NuWare Brand</h2>
			<div class="about-page__brand-evolution-grid">
				<?php
				$brand_logos = array(
					1994 => array( 'extension' => 'png', 'width' => 971, 'height' => 346 ),
					2006 => array( 'extension' => 'png', 'width' => 366, 'height' => 94 ),
					2013 => array( 'extension' => 'png', 'width' => 459, 'height' => 106 ),
					2019 => array( 'extension' => 'png', 'width' => 1754, 'height' => 1241 ),
					2026 => array( 'extension' => 'jpg', 'width' => 3509, 'height' => 2482 ),
				);
				foreach ( $brand_logos as $brand_year => $brand_logo ) :
				?>
					<figure class="about-page__brand-card">
						<div class="about-page__brand-logo-wrap">
							<img class="about-page__brand-logo about-page__brand-logo--<?php echo esc_attr( $brand_year ); ?>" src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/about/brand-evolution/' . $brand_year . '_Logo.' . $brand_logo['extension'] ); ?>" alt="NuWare logo from <?php echo esc_attr( $brand_year ); ?>" width="<?php echo esc_attr( $brand_logo['width'] ); ?>" height="<?php echo esc_attr( $brand_logo['height'] ); ?>" loading="lazy">
						</div>
						<figcaption><?php echo esc_html( $brand_year ); ?></figcaption>
					</figure>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php if ( $about_quote ) : ?>
		<section class="about-page__quote" aria-label="NuWare quote">
			<div class="about-page__quote-content">
				<?php echo wp_kses_post( $about_quote ); ?>
			</div>
		</section>
	<?php endif; ?>
</main>
<?php get_footer(); ?>

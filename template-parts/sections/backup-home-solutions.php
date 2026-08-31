<?php
/** Solutions cards: primary-menu order, page titles and page-level ACF fields. */
$locations = get_nav_menu_locations();
$items = ! empty( $locations['primary'] ) ? wp_get_nav_menu_items( $locations['primary'] ) : array();
$solutions_page = get_page_by_path( 'solutions' );
$parent_id = 0;
$pages = array();
foreach ( $items ?: array() as $item ) {
	if ( ( $solutions_page && 'page' === $item->object && (int) $item->object_id === $solutions_page->ID ) || 'solutions' === sanitize_title( $item->title ) ) {
		$parent_id = (int) $item->ID;
		break;
	}
}
foreach ( $items ?: array() as $item ) {
	if ( $parent_id && (int) $item->menu_item_parent === $parent_id && 'page' === $item->object ) {
		$page = get_post( (int) $item->object_id );
		if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
			$pages[] = $page;
		}
	}
}
if ( ! $pages ) {
	return;
}
// Short supporting headings from the design; descriptions and icons are edited on each page.
$headlines = array(
	'application' => __( 'Building Smarter, Scalable, and Future-Ready', 'nuware' ),
	'cloud' => __( 'Cloud Without Compromise', 'nuware' ),
	'data' => __( 'Turning Data into Intelligence', 'nuware' ),
	'infrastructure' => __( 'Building Smarter, Scalable, and Future-Ready', 'nuware' ),
);
?>
<section class="homepage-solutions" data-solutions aria-labelledby="homepage-solutions-title">
	<div class="homepage-solutions__inner">
		<header class="homepage-solutions__header">
			<h2 class="homepage-solutions__title" id="homepage-solutions-title"><?php esc_html_e( 'Solutions, built from the core.', 'nuware' ); ?></h2>
			<p class="homepage-solutions__intro"><?php esc_html_e( 'From applications and cloud to data and infrastructure, we bring the same fundamental understanding of technology to every challenge.', 'nuware' ); ?></p>
		</header>
		<div class="homepage-solutions__cards">
			<?php foreach ( array_slice( $pages, 0, 4 ) as $index => $page ) :
				$description = function_exists( 'get_field' ) ? get_field( 'description', $page->ID ) : '';
				// Raw ACF icon-picker data preserves media IDs regardless of return-format settings.
				$icon = function_exists( 'get_field' ) ? get_field( 'icon', $page->ID, false ) : array();
				$icon_type = is_array( $icon ) ? ( $icon['type'] ?? '' ) : '';
				$icon_value = is_array( $icon ) ? ( $icon['value'] ?? '' ) : '';
				$icon_url = 'media_library' === $icon_type ? wp_get_attachment_url( (int) $icon_value ) : ( 'url' === $icon_type ? $icon_value : '' );
				$panel_id = 'solution-panel-' . $page->ID;
				?>
				<article class="homepage-solutions__card" data-solution-card>
					<h3 class="homepage-solutions__name">
						<button class="homepage-solutions__trigger" id="solution-trigger-<?php echo esc_attr( $page->ID ); ?>" type="button" aria-expanded="true" aria-controls="<?php echo esc_attr( $panel_id ); ?>">
							<span class="homepage-solutions__icon" aria-hidden="true">
								<?php if ( $icon_url ) : ?>
									<img src="<?php echo esc_url( $icon_url ); ?>" alt="" width="20" height="20" loading="lazy">
								<?php elseif ( 'dashicons' === $icon_type ) : ?>
									<span class="dashicons <?php echo esc_attr( sanitize_html_class( $icon_value ) ); ?>"></span>
								<?php endif; ?>
							</span>
							<span class="homepage-solutions__name-text"><?php echo esc_html( get_the_title( $page ) ); ?></span>
							<span class="homepage-solutions__indicator" aria-hidden="true">+</span>
						</button>
					</h3>
					<div class="homepage-solutions__content">
						<h4 class="homepage-solutions__headline"><?php echo esc_html( $headlines[ $page->post_name ] ?? get_the_title( $page ) ); ?></h4>
						<div class="homepage-solutions__details" id="<?php echo esc_attr( $panel_id ); ?>" role="region" aria-labelledby="solution-trigger-<?php echo esc_attr( $page->ID ); ?>">
							<?php if ( is_string( $description ) && '' !== trim( $description ) ) : ?>
								<p class="homepage-solutions__description"><?php echo esc_html( $description ); ?></p>
							<?php endif; ?>
							<a class="homepage-solutions__link" href="<?php echo esc_url( get_permalink( $page ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Read more about %s', 'nuware' ), get_the_title( $page ) ) ); ?>"><span aria-hidden="true">→</span> <?php esc_html_e( 'Read More', 'nuware' ); ?></a>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

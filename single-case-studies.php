<?php
/** Single template for the existing case-studies CPT. */
get_header();
?>
<main id="primary" class="site-main case-study">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'case-study__article' ); ?> aria-labelledby="case-study-title">
			<header class="case-study__hero<?php echo has_post_thumbnail() ? ' case-study__hero--image' : ''; ?>">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="case-study__hero-art" aria-hidden="true">
						<?php the_post_thumbnail( 'full', array( 'class' => 'case-study__hero-image', 'alt' => '', 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
					</div>
				<?php endif; ?>
				<div class="case-study__container">
					<nav class="case-study__breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nuware' ); ?>">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nuware' ); ?></a>
						<span aria-hidden="true">/</span>
						<?php $nuware_case_studies_page = get_page_by_path( 'case-studies' ); ?>
						<?php if ( $nuware_case_studies_page instanceof WP_Post && 'publish' === $nuware_case_studies_page->post_status ) : ?>
							<a href="<?php echo esc_url( get_permalink( $nuware_case_studies_page ) ); ?>"><?php esc_html_e( 'Case Studies', 'nuware' ); ?></a>
						<?php else : ?>
							<span><?php esc_html_e( 'Case Studies', 'nuware' ); ?></span>
						<?php endif; ?>
					</nav>
					<p class="case-study__eyebrow"><?php esc_html_e( 'Case Study', 'nuware' ); ?></p>
					<h1 class="case-study__title" id="case-study-title"><?php the_title(); ?></h1>
				</div>
			</header>
			<div class="case-study__body">
				<div class="case-study__content">
					<?php the_content(); ?>
					<?php wp_link_pages( array( 'before' => '<nav class="case-study__pages" aria-label="' . esc_attr__( 'Article pages', 'nuware' ) . '">', 'after' => '</nav>' ) ); ?>
				</div>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>

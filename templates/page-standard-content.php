<?php
/**
 * Template Name: Standard Content Page
 * Template Post Type: page
 *
 * A restrained Gutenberg content template for legal and informational pages.
 */

get_header();
?>
<main id="primary" class="standard-content-page">
	<?php while ( have_posts() ) : ?>
		<?php the_post(); ?>
		<article <?php post_class( 'standard-content-page__article' ); ?>>
			<div class="standard-content-page__inner">
				<h1 class="standard-content-page__title"><?php the_title(); ?></h1>
				<div class="standard-content-page__body">
					<?php the_content(); ?>
				</div>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();

<?php get_header(); ?>
<main id="primary" class="site-main">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<h1><?php echo esc_html( get_the_archive_title() ?: get_bloginfo( 'name' ) ); ?></h1>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class(); ?>>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>
			<?php the_posts_navigation(); ?>
		<?php else : ?>
			<h1><?php esc_html_e( 'Nothing found', 'nuware' ); ?></h1>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>

<?php
$nuware_page_parent_id   = wp_get_post_parent_id( get_the_ID() );
$nuware_page_parent_slug = $nuware_page_parent_id ? get_post_field( 'post_name', $nuware_page_parent_id ) : '';

if ( 'industries' === $nuware_page_parent_slug ) {
	get_template_part( 'template-parts/pages/world' );
	return;
}

get_header();
?>
<main id="primary" class="site-main">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'container' ); ?>>
			<h1><?php the_title(); ?></h1>
			<div class="entry-content"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>

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
	<h1><?php esc_html_e( 'Page', 'nuware' ); ?></h1>
</main>

<?php get_footer(); ?>

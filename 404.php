<?php
/** The template for requests that cannot be found. */
get_header();
?>
<main id="primary" class="site-main error-404">
	<div class="container">
		<h1><?php esc_html_e( 'Page not found', 'nuware' ); ?></h1>
		<p><?php esc_html_e( 'The page you requested could not be found.', 'nuware' ); ?></p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return to the homepage', 'nuware' ); ?> <span aria-hidden="true">→</span></a>
	</div>
</main>
<?php get_footer(); ?>

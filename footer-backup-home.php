<?php
/** Site footer. Contact CTA uses a published contact page, or email until one exists. */
$nuware_contact_url = 'mailto:info@nuware.com';
foreach ( array( 'contact', 'contact-us', 'get-in-touch' ) as $nuware_contact_slug ) {
	$nuware_contact_page = get_page_by_path( $nuware_contact_slug );
	if ( $nuware_contact_page instanceof WP_Post && 'publish' === $nuware_contact_page->post_status ) {
		$nuware_contact_url = get_permalink( $nuware_contact_page );
		break;
	}
}
$nuware_privacy_url = home_url( '/privacy-policy/' );
$nuware_terms_url   = home_url( '/terms/' );
?>
<footer class="site-footer">
	<div class="site-footer__inner">
		<div class="site-footer__columns">
			<div class="site-footer__invitation">
				<h2 class="site-footer__title"><?php esc_html_e( 'Interested?', 'nuware' ); ?></h2>
				<p class="site-footer__tagline"><?php esc_html_e( 'Transform Your Business With Future-Ready Tech', 'nuware' ); ?></p>
				<a class="site-footer__cta" href="<?php echo esc_url( $nuware_contact_url ); ?>"><?php esc_html_e( 'Let’s collaborate', 'nuware' ); ?> <span aria-hidden="true">→</span></a>
			</div>

			<div class="site-footer__column">
				<nav class="site-footer__socials" aria-label="<?php esc_attr_e( 'Social media', 'nuware' ); ?>">
					<a class="site-footer__social" href="https://www.linkedin.com/company/nuware" target="_blank" rel="noopener noreferrer">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/footer-linkedin.svg' ); ?>" width="14" height="14" alt="" loading="lazy"> LinkedIn
					</a>
					<a class="site-footer__social" href="https://www.facebook.com/NuWareCo/">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/footer-facebook.svg' ); ?>" width="14" height="14" alt="" loading="lazy"> Facebook
					</a>
				</nav>
				<div class="site-footer__office">
					<h3 class="site-footer__office-title">NuWare Tech Corp</h3>
					<address class="site-footer__address">100 Wood Ave South, Suite 116<br>Iselin, New Jersey 08830-2716</address>
					<div class="site-footer__contact">
						<p><strong><?php esc_html_e( 'Tel:', 'nuware' ); ?></strong> <a href="tel:+17324940550">(732) 494-0550</a></p>
						<a class="site-footer__email" href="mailto:info@nuware.com">info@nuware.com</a>
					</div>
				</div>
			</div>

			<div class="site-footer__column site-footer__column--india">
				<div class="site-footer__office">
					<h3 class="site-footer__office-title">NuWare Systems LLP</h3>
					<address class="site-footer__address">2/2, 1st Floor, Embassy Icon<br>Annexe, Infantry Road<br>Opposite Coffee Board<br>Bangalore – 560001</address>
					<address class="site-footer__address">1st Floor, 60, 1st Cross 4th Main, HAL III Stage,<br>Bengaluru, Karnataka, India - 560075</address>
					<div class="site-footer__contact">
						<p><strong><?php esc_html_e( 'Tel:', 'nuware' ); ?></strong> <a href="tel:+918067166300">+91 - 80671 66300</a>/<a href="tel:+918067166301" aria-label="<?php esc_attr_e( 'Call +91 80671 66301', 'nuware' ); ?>">301</a></p>
					</div>
				</div>
			</div>
		</div>

		<div class="site-footer__bottom">
			<p class="site-footer__copyright">© <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php esc_html_e( 'NuWare. All rights reserved. Built with fundamentally understood tech.', 'nuware' ); ?></p>
			<div class="site-footer__legal">
				<?php if ( $nuware_privacy_url ) : ?>
					<a href="<?php echo esc_url( $nuware_privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'nuware' ); ?></a>
				<?php else : ?>
					<span><?php esc_html_e( 'Privacy Policy', 'nuware' ); ?></span>
				<?php endif; ?>
				<?php if ( $nuware_terms_url ) : ?>
					<a href="<?php echo esc_url( $nuware_terms_url ); ?>"><?php esc_html_e( 'Terms of Service', 'nuware' ); ?></a>
				<?php else : ?>
					<span><?php esc_html_e( 'Terms of Service', 'nuware' ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
</footer>
<?php get_template_part( 'template-parts/sections/cookie-notice' ); ?>
<?php wp_footer(); ?>
</body>
</html>

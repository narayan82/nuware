<?php
/** Placeholder answer drawer; no AI service is connected yet. */
$nuware_answer_contact_url = 'mailto:info@nuware.com';
foreach ( array( 'contact', 'contact-us', 'get-in-touch' ) as $nuware_answer_slug ) {
	$nuware_answer_page = get_page_by_path( $nuware_answer_slug );
	if ( $nuware_answer_page instanceof WP_Post && 'publish' === $nuware_answer_page->post_status ) {
		$nuware_answer_contact_url = get_permalink( $nuware_answer_page );
		break;
	}
}
?>
<dialog class="hero-answer" aria-label="<?php esc_attr_e( 'Answer to your question', 'nuware' ); ?>" data-hero-answer>
	<div class="hero-answer__inner">
		<button class="hero-answer__close" type="button" aria-label="<?php esc_attr_e( 'Close answer', 'nuware' ); ?>" data-hero-answer-close autofocus>
			<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
		</button>
		<div class="hero-answer__content">
			<p class="hero-answer__question" data-hero-answer-question hidden></p>
			<p class="hero-answer__notice"><?php esc_html_e( 'Preview answer — AI integration coming soon.', 'nuware' ); ?></p>
			<p class="hero-answer__text"><?php esc_html_e( 'NuWare can help you turn your technology challenges into practical business outcomes. We start by understanding your existing systems, data and priorities, then work with your team to shape a clear plan across applications, cloud, data and infrastructure. From modernising core platforms to building new digital capabilities, our focus is on secure, scalable solutions that support your next stage of growth.', 'nuware' ); ?></p>
		</div>
		<div class="hero-answer__invitation">
			<h2 class="hero-answer__invitation-title"><?php esc_html_e( 'Interested?', 'nuware' ); ?></h2>
			<p class="hero-answer__tagline"><?php esc_html_e( 'Transform Your Business With Future-Ready Tech', 'nuware' ); ?></p>
			<a class="hero-answer__cta" href="<?php echo esc_url( $nuware_answer_contact_url ); ?>"><?php esc_html_e( 'Let’s collaborate', 'nuware' ); ?> <span aria-hidden="true">→</span></a>
		</div>
	</div>
</dialog>

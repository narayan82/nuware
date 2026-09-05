<?php
/** Live homepage AI answer drawer. */
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
		<div class="hero-answer__content" aria-live="polite" aria-atomic="true" data-hero-answer-live>
			<p class="hero-answer__status" data-hero-answer-status></p>
			<p class="hero-answer__question" data-hero-answer-question hidden></p>
			<p class="hero-answer__text" data-hero-answer-text hidden></p>
			<p class="hero-answer__remaining" data-hero-answer-remaining hidden></p>
		</div>
		<div class="hero-answer__limit" data-hero-answer-limit hidden>
			<p class="hero-answer__limit-text"><?php esc_html_e( 'You’ve reached today’s question limit. Please check back tomorrow.', 'nuware' ); ?></p>
			<a class="hero-answer__cta" href="<?php echo esc_url( $nuware_answer_contact_url ); ?>" data-contact-trigger><?php esc_html_e( 'Talk to NuWare', 'nuware' ); ?> <span aria-hidden="true">→</span></a>
		</div>
	</div>
</dialog>

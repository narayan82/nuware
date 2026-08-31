<?php
/** Site-wide contact drawer. Edit its fields in Gravity Forms, form ID 1. */
if ( ! function_exists( 'gravity_form' ) || ! class_exists( 'GFAPI' ) ) {
	return;
}
$nuware_contact_form = GFAPI::get_form( 1 );
if ( ! $nuware_contact_form || empty( $nuware_contact_form['is_active'] ) || ! empty( $nuware_contact_form['is_trash'] ) ) {
	return;
}
?>
<dialog class="contact-overlay" id="nuware-contact-overlay" data-contact-overlay aria-labelledby="contact-overlay-title">
	<div class="contact-overlay__inner">
		<div class="contact-overlay__header">
		<h2 class="contact-overlay__title" id="contact-overlay-title"><?php esc_html_e( 'Get in Touch', 'nuware' ); ?></h2>
		<button class="contact-overlay__close" type="button" data-contact-close aria-label="<?php esc_attr_e( 'Close contact form', 'nuware' ); ?>" autofocus>
			<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
		</button>
		</div>
		<div class="contact-overlay__form">
			<?php gravity_form( 1, false, true, false, null, true ); ?>
		</div>
	</div>
</dialog>

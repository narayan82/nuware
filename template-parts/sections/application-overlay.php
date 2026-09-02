<?php
/** Careers application drawer. Edit its fields in Gravity Forms, form ID 2. */
if ( ! is_page( 'careers' ) || ! function_exists( 'gravity_form' ) || ! class_exists( 'GFAPI' ) ) {
	return;
}

$nuware_application_form = GFAPI::get_form( 2 );

if ( ! $nuware_application_form || empty( $nuware_application_form['is_active'] ) || ! empty( $nuware_application_form['is_trash'] ) ) {
	return;
}
?>
<dialog class="contact-overlay application-overlay" id="nuware-application-overlay" data-application-overlay aria-labelledby="application-overlay-title">
	<div class="contact-overlay__inner">
		<div class="contact-overlay__header">
			<h2 class="contact-overlay__title" id="application-overlay-title"><?php esc_html_e( 'Apply Now', 'nuware' ); ?></h2>
			<button class="contact-overlay__close" type="button" data-application-close aria-label="<?php esc_attr_e( 'Close application form', 'nuware' ); ?>" autofocus>
				<svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
			</button>
		</div>
		<div class="contact-overlay__form">
			<?php gravity_form( 2, false, true, false, null, true ); ?>
		</div>
	</div>
</dialog>

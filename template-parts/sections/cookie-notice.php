<?php
/** Site-wide cookie notice. */
?>
<aside class="cookie-notice" data-cookie-notice hidden aria-label="<?php esc_attr_e( 'Cookie notice', 'nuware' ); ?>">
	<p class="cookie-notice__message">
		<?php esc_html_e( 'We use cookies to understand site usage and improve your experience.', 'nuware' ); ?>
		<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'nuware' ); ?></a>
	</p>
	<button class="cookie-notice__accept" type="button" data-cookie-accept><?php esc_html_e( 'Accept', 'nuware' ); ?></button>
</aside>
<script>
	(function () {
		const notice = document.querySelector('[data-cookie-notice]');
		const accept = notice?.querySelector('[data-cookie-accept]');
		if (!notice || !accept) return;

		try {
			if (localStorage.getItem('nuware-cookie-consent') === 'accepted') return;
		} catch (error) {}

		notice.hidden = false;
		accept.addEventListener('click', function () {
			try {
				localStorage.setItem('nuware-cookie-consent', 'accepted');
			} catch (error) {}
			notice.hidden = true;
			window.dispatchEvent(new CustomEvent('nuware-cookie-consent'));
		});
	})();
</script>

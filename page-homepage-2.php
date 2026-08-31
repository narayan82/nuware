<?php get_header(); ?>

<main id="primary" class="site-main">
	<section class="home-hero">
		<div class="home-hero__heading">
			<h1 class="home-hero__title" aria-label="<?php esc_attr_e( 'Technology', 'nuware' ); ?>"><span class="home-hero__sequence" aria-hidden="true">0110101011</span></h1>
			<p class="home-hero__subtitle"><?php esc_html_e( 'Fundamentally understood.', 'nuware' ); ?></p>
		</div>

		<div class="home-hero__content">
			<p class="home-hero__copy"><?php esc_html_e( 'From Assembler to AI, technology has changed dramatically. The fundamentals haven’t: logic, data, systems and sound engineering. That’s where NuWare’s strength has always been.', 'nuware' ); ?></p>

			<div class="home-hero__prompt">
				<p class="home-hero__question"><?php esc_html_e( 'What can we do for your business?', 'nuware' ); ?></p>

				<form class="home-hero__ai-form" action="" method="get">
					<label class="home-hero__ai-label" for="nuware-ai-question"><?php esc_html_e( 'Ask NuWare AI a question', 'nuware' ); ?></label>
					<input
						class="home-hero__ai-input"
						id="nuware-ai-question"
						name="nuware-ai-question"
						type="text"
						placeholder="<?php esc_attr_e( 'Ask NuWare AI a question...', 'nuware' ); ?>"
					>

					<div class="home-hero__ai-actions">
						<button class="home-hero__ai-button home-hero__ai-button--mic" type="button" aria-label="<?php esc_attr_e( 'Use microphone', 'nuware' ); ?>">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/mic.svg' ); ?>" alt="" width="18" height="18">
						</button>
						<button class="home-hero__ai-button home-hero__ai-button--send" type="submit" aria-label="<?php esc_attr_e( 'Send question', 'nuware' ); ?>">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/send.svg' ); ?>" alt="" width="18" height="18">
						</button>
					</div>
				</form>
			</div>
		</div>
	</section>
</main>

<?php get_footer(); ?>

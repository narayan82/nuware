<?php
/** Case study library: searchable, filterable and alphabetically sortable. */
get_header();
$search = isset( $_GET['cs_search'] ) && is_string( $_GET['cs_search'] ) ? sanitize_text_field( wp_unslash( $_GET['cs_search'] ) ) : '';
$category = isset( $_GET['cs_category'] ) && is_string( $_GET['cs_category'] ) ? sanitize_title( wp_unslash( $_GET['cs_category'] ) ) : '';
$sort = isset( $_GET['cs_sort'] ) && 'desc' === $_GET['cs_sort'] ? 'desc' : 'asc';
$terms = get_terms( array( 'taxonomy' => 'industry', 'hide_empty' => false ) );
$terms = is_wp_error( $terms ) ? array() : $terms;
$args = array( 'post_type' => 'case-studies', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => array( 'title' => strtoupper( $sort ), 'ID' => 'ASC' ), 's' => $search );
if ( $category ) {
	$args['tax_query'] = array( array( 'taxonomy' => 'industry', 'field' => 'slug', 'terms' => $category ) );
}
$studies = new WP_Query( $args );
$page_url = get_permalink( get_queried_object_id() );
?>
<main id="primary" class="case-library">
	<div class="case-library__container">
		<header class="case-library__header">
			<h1 class="case-library__title"><?php echo esc_html( get_the_title( get_queried_object_id() ) ); ?></h1>
		</header>
		<form class="case-library__filters" method="get" action="<?php echo esc_url( $page_url ); ?>" role="search" aria-label="Search case studies">
			<div class="case-library__field case-library__field--search">
				<label for="cs-search">Search case studies</label>
				<input id="cs-search" type="search" name="cs_search" placeholder="Search by keyword…" value="<?php echo esc_attr( $search ); ?>">
			</div>
			<div class="case-library__field">
				<label for="cs-category">Category</label>
				<select id="cs-category" name="cs_category">
					<option value="">All categories</option>
					<?php foreach ( $terms as $term ) : ?>
						<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $category, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="case-library__field">
				<label for="cs-sort">Sort by</label>
				<select id="cs-sort" name="cs_sort">
					<option value="asc" <?php selected( $sort, 'asc' ); ?>>Title: A–Z</option>
					<option value="desc" <?php selected( $sort, 'desc' ); ?>>Title: Z–A</option>
				</select>
			</div>
		</form>
		<div data-case-library-results>
		<div class="case-library__results">
			<p role="status"><?php echo esc_html( sprintf( _n( '%s case study', '%s case studies', $studies->post_count, 'nuware' ), number_format_i18n( $studies->post_count ) ) ); ?></p>
			<?php if ( $search || $category || 'desc' === $sort ) : ?><a href="<?php echo esc_url( $page_url ); ?>">Reset filters</a><?php endif; ?>
		</div>
		<?php if ( $studies->have_posts() ) : ?>
			<div class="case-library__grid">
				<?php while ( $studies->have_posts() ) : $studies->the_post(); get_template_part( 'template-parts/case-studies/card' ); endwhile; ?>
			</div>
		<?php else : ?>
			<div class="case-library__empty"><h2>No case studies found</h2><p>Try a different keyword or category.</p><a href="<?php echo esc_url( $page_url ); ?>">View all case studies →</a></div>
		<?php endif; wp_reset_postdata(); ?>
		</div>
	</div>
</main>
<?php get_footer(); ?>

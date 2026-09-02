<?php
/** Shared case-study card. Uses the current WordPress loop post. */
$categories = get_the_terms( get_the_ID(), 'industry' );
$excerpt = get_post_field( 'post_excerpt', get_the_ID() );
if ( ! $excerpt && preg_match( '/<p\b[^>]*>(.*?)<\/p>/is', get_the_content(), $match ) ) { $excerpt = $match[1]; }
if ( ! $excerpt ) { $excerpt = get_the_content(); }
?>
<article class="case-library__card">
	<div class="case-library__image">
		<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) ); } ?>
		<p class="case-library__categories"><?php echo esc_html( $categories && ! is_wp_error( $categories ) ? implode( ' / ', wp_list_pluck( $categories, 'name' ) ) : 'Case study' ); ?></p>
	</div>
	<div class="case-library__card-body">
		<h2 class="case-library__card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="case-library__excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $excerpt ), 14 ) ); ?></p>
	</div>
</article>

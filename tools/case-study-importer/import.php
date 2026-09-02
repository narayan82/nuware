<?php
/** CLI-only one-PDF importer. Not included by the theme or exposed over HTTP. */
if ( PHP_SAPI !== 'cli' ) {
	http_response_code( 403 );
	exit;
}
$options = getopt( '', array( 'file:', 'publish', 'source-dir:' ) );
$filename = $options['file'] ?? '';
$theme_root = dirname( __DIR__, 2 );
$source_relative = $options['source-dir'] ?? 'imports/case-studies';
if ( ! in_array( $source_relative, array( 'imports/case-studies', 'assets/pdfs' ), true ) ) {
	fwrite( STDERR, "Unsupported source directory.\n" );
	exit( 1 );
}
$source_dir = realpath( $theme_root . '/' . $source_relative );
$source = realpath( $source_dir . '/' . $filename );
if ( ! $source_dir || ! $filename || basename( $filename ) !== $filename || ! $source || dirname( $source ) !== $source_dir || strtolower( pathinfo( $source, PATHINFO_EXTENSION ) ) !== 'pdf' ) {
	fwrite( STDERR, "Use --file=one-filename.pdf from the selected source directory.\n" );
	exit( 1 );
}
$stem = pathinfo( $filename, PATHINFO_FILENAME );
$preview = $theme_root . '/imports/case-study-preview/' . $stem . '.json';
if ( ! is_file( $preview ) ) {
	fwrite( STDERR, "Run extract.py and review the preview first.\n" );
	exit( 1 );
}
$data = json_decode( file_get_contents( $preview ), true );
if ( ! is_array( $data ) || ( $data['schema'] ?? 0 ) !== 1 || ( $data['source'] ?? '' ) !== $filename || ( $data['sha256'] ?? '' ) !== hash_file( 'sha256', $source ) ) {
	fwrite( STDERR, "Preview does not match the PDF. Extract again.\n" );
	exit( 1 );
}
require_once dirname( $theme_root, 3 ) . '/wp-load.php';
$type = 'case-studies'; // ACF post type renamed from case-study; do not register a new CPT.
if ( ! post_type_exists( $type ) ) {
	fwrite( STDERR, "The existing case-studies CPT is unavailable.\n" );
	exit( 1 );
}
$title = trim( preg_replace( '/^Case\s+Study\s*:\s*/iu', '', sanitize_text_field( $data['title'] ?? '' ) ) );
$slug = sanitize_title( $stem );
$content = wp_kses_post( $data['content'] ?? '' );
if ( ! $title || ! $slug || strlen( wp_strip_all_tags( $content ) ) < 100 ) {
	fwrite( STDERR, "Empty or incomplete extracted content.\n" );
	exit( 1 );
}
// Serialize runs, including duplicate detection and insertion. Never overwrite posts.
$lock = '_nuware_case_study_import_lock';
if ( ! add_option( $lock, time(), '', false ) ) {
	fwrite( STDERR, "An importer lock exists; check for an active process before clearing it.\n" );
	exit( 1 );
}
$exit_code = 0;
try {
	global $wpdb;
	$existing = $wpdb->get_var( $wpdb->prepare(
		"SELECT p.ID FROM {$wpdb->posts} p LEFT JOIN {$wpdb->postmeta} m ON m.post_id=p.ID
		WHERE p.post_type IN (%s, 'case-study') AND (p.post_name=%s OR p.post_name=%s OR p.post_title=%s
		OR (m.meta_key='_nuware_pdf_sha256' AND m.meta_value=%s)
		OR (m.meta_key='_nuware_pdf_source' AND m.meta_value=%s)) LIMIT 1",
		$type, $slug, $slug . '__trashed', $title, $data['sha256'], $filename
	) );
	if ( $existing ) {
		$result = array( 'result' => 'skipped_duplicate', 'post_id' => (int) $existing, 'title' => get_the_title( $existing ), 'url' => get_permalink( $existing ) );
	} elseif ( ! isset( $options['publish'] ) ) {
		$result = array( 'result' => 'dry_run', 'source' => $filename, 'post_type' => $type, 'title' => $title, 'slug' => $slug, 'post_status' => 'publish' );
	} else {
		$id = wp_insert_post( wp_slash( array(
			'post_type' => $type,
			'post_status' => 'publish',
			'post_title' => $title,
			'post_name' => $slug,
			'post_content' => $content,
			'meta_input' => array(
				'_nuware_pdf_sha256' => $data['sha256'],
				'_nuware_pdf_source' => $filename,
				'_nuware_pdf_import_version' => '2',
			),
		) ), true );
		if ( is_wp_error( $id ) ) {
			throw new RuntimeException( $id->get_error_message() );
		}
		$result = array( 'result' => 'published', 'source' => $filename, 'post_id' => $id, 'title' => get_the_title( $id ), 'slug' => get_post_field( 'post_name', $id ), 'url' => get_permalink( $id ) );
	}
	echo wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
} catch ( Throwable $error ) {
	fwrite( STDERR, $error->getMessage() . PHP_EOL );
	$exit_code = 1;
} finally {
	delete_option( $lock );
}
exit( $exit_code );

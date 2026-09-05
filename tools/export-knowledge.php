<?php
/** Run the NuWare knowledge exporter without WP-CLI. */
if ( PHP_SAPI !== 'cli' ) {
	http_response_code( 403 );
	exit;
}

define( 'WP_USE_THEMES', false );
require_once dirname( __DIR__, 4 ) . '/wp-load.php';

$result = nuware_export_knowledge();
if ( is_wp_error( $result ) ) {
	fwrite( STDERR, $result->get_error_message() . PHP_EOL );
	exit( 1 );
}

echo wp_json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . PHP_EOL;

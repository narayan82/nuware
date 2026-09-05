<?php
/**
 * WP-CLI setup command for the private NuWare OpenAI vector store.
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

/**
 * Build the multipart body required by the Files API.
 */
function nuware_openai_file_body( $file_path, $boundary ) {
	$file_contents = file_get_contents( $file_path );
	if ( false === $file_contents ) {
		return new WP_Error( 'nuware_openai_file_read', 'Could not read the knowledge JSON file.' );
	}

	$filename = sanitize_file_name( basename( $file_path ) );
	$lines = array(
		'--' . $boundary,
		'Content-Disposition: form-data; name="purpose"',
		'',
		'assistants',
		'--' . $boundary,
		'Content-Disposition: form-data; name="file"; filename="' . $filename . '"',
		'Content-Type: application/json',
		'',
		$file_contents,
		'--' . $boundary . '--',
		'',
	);

	return implode( "\r\n", $lines );
}

/**
 * Create the vector store, upload and attach the knowledge file, then poll it.
 */
function nuware_openai_setup_knowledge( $file_path, $timeout_seconds = 300, $poll_seconds = 2 ) {
	$api_key = nuware_openai_api_key();
	if ( '' === $api_key ) {
		return new WP_Error(
			'nuware_openai_key_missing',
			'Define OPENAI_API_KEY in wp-config.php before running this command.'
		);
	}

	$file_path = wp_normalize_path( $file_path );
	if ( ! is_readable( $file_path ) || ! is_file( $file_path ) ) {
		return new WP_Error( 'nuware_openai_file_missing', 'Knowledge file is missing or unreadable: ' . $file_path );
	}

	$vector_store = nuware_openai_api_request(
		'POST',
		'/vector_stores',
		$api_key,
		wp_json_encode( array( 'name' => 'NuWare Website Knowledge' ) ),
		array( 'Content-Type' => 'application/json' )
	);
	if ( is_wp_error( $vector_store ) ) {
		return $vector_store;
	}

	$vector_store_id = isset( $vector_store['id'] ) ? (string) $vector_store['id'] : '';
	if ( '' === $vector_store_id ) {
		return new WP_Error( 'nuware_openai_vector_store', 'OpenAI did not return a Vector Store ID.' );
	}

	$boundary = 'nuware-' . wp_generate_password( 32, false, false );
	$file_body = nuware_openai_file_body( $file_path, $boundary );
	if ( is_wp_error( $file_body ) ) {
		return $file_body;
	}

	$file = nuware_openai_api_request(
		'POST',
		'/files',
		$api_key,
		$file_body,
		array( 'Content-Type' => 'multipart/form-data; boundary=' . $boundary ),
		120
	);
	if ( is_wp_error( $file ) ) {
		return $file;
	}

	$file_id = isset( $file['id'] ) ? (string) $file['id'] : '';
	if ( '' === $file_id ) {
		return new WP_Error( 'nuware_openai_file', 'OpenAI did not return a File ID.' );
	}

	$attachment_path = '/vector_stores/' . rawurlencode( $vector_store_id ) . '/files';
	$attachment = nuware_openai_api_request(
		'POST',
		$attachment_path,
		$api_key,
		wp_json_encode( array( 'file_id' => $file_id ) ),
		array( 'Content-Type' => 'application/json' )
	);
	if ( is_wp_error( $attachment ) ) {
		return $attachment;
	}

	$status = isset( $attachment['status'] ) ? (string) $attachment['status'] : 'in_progress';
	$deadline = time() + max( 1, (int) $timeout_seconds );
	$status_path = $attachment_path . '/' . rawurlencode( $file_id );

	while ( ! in_array( $status, array( 'completed', 'failed', 'cancelled' ), true ) && time() < $deadline ) {
		sleep( max( 1, (int) $poll_seconds ) );
		$attachment = nuware_openai_api_request( 'GET', $status_path, $api_key );
		if ( is_wp_error( $attachment ) ) {
			return $attachment;
		}
		$status = isset( $attachment['status'] ) ? (string) $attachment['status'] : 'unknown';
	}

	if ( ! in_array( $status, array( 'completed', 'failed', 'cancelled' ), true ) ) {
		$status = 'timeout';
	}

	return array(
		'vector_store_id' => $vector_store_id,
		'file_id'         => $file_id,
		'status'          => $status,
		'last_error'      => $attachment['last_error'] ?? null,
	);
}

WP_CLI::add_command(
	'nuware ai-setup',
	static function ( $args, $assoc_args ) {
		$default_file = dirname( ABSPATH ) . '/knowledge/nuware-knowledge.json';
		$file_path = isset( $assoc_args['file'] ) ? (string) $assoc_args['file'] : $default_file;
		$timeout = isset( $assoc_args['timeout'] ) ? max( 1, (int) $assoc_args['timeout'] ) : 300;
		$result = nuware_openai_setup_knowledge( $file_path, $timeout );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::line( 'Vector Store ID: ' . $result['vector_store_id'] );
		WP_CLI::line( 'File ID: ' . $result['file_id'] );
		WP_CLI::line( 'Final processing status: ' . $result['status'] );

		if ( 'completed' !== $result['status'] ) {
			$error_message = is_array( $result['last_error'] ) && isset( $result['last_error']['message'] )
				? ' ' . $result['last_error']['message']
				: '';
			WP_CLI::error( 'Vector Store file processing did not complete.' . $error_message );
		}

		WP_CLI::success( 'NuWare website knowledge is indexed and ready.' );
	}
);

WP_CLI::add_command(
	'nuware ai-ask',
	static function ( $args, $assoc_args ) {
		$question = trim( implode( ' ', $args ) );
		$model = isset( $assoc_args['model'] ) ? trim( (string) $assoc_args['model'] ) : 'gpt-4.1-mini';
		$classifier_model = isset( $assoc_args['classifier-model'] ) ? trim( (string) $assoc_args['classifier-model'] ) : 'gpt-4.1-nano';
		$result = nuware_openai_ask( $question, $model, $classifier_model );

		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}

		WP_CLI::line( 'Category: ' . $result['category'] );
		WP_CLI::line( 'Response: ' . $result['answer'] );
		WP_CLI::line( 'File Search invoked: ' . ( $result['file_search_invoked'] ? 'yes' : 'no' ) );
	}
);

<?php
/**
 * Pretty routes for the AI page tabs.
 */

add_action(
	'init',
	static function () {
		add_rewrite_rule(
			'^ai/(advisory|ai-incubation|ai-infra-security|ai-strategy-value)/?$',
			'index.php?pagename=ai&ai_tab=$matches[1]',
			'top'
		);

		if ( '1' !== get_option( 'nuware_ai_routes_version' ) ) {
			flush_rewrite_rules( false );
			update_option( 'nuware_ai_routes_version', '1' );
		}
	}
);

add_filter(
	'query_vars',
	static function ( $query_vars ) {
		$query_vars[] = 'ai_tab';
		return $query_vars;
	}
);

/**
 * Normalize a URL to the origin used by browser same-origin checks.
 */
function nuware_ai_url_origin( $url ) {
	$parts = wp_parse_url( $url );
	if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
		return '';
	}

	$scheme = strtolower( (string) $parts['scheme'] );
	$host = strtolower( (string) $parts['host'] );
	$port = isset( $parts['port'] ) ? (int) $parts['port'] : ( 'https' === $scheme ? 443 : 80 );

	return $scheme . '://' . $host . ':' . $port;
}

/**
 * Require site-origin context and a WordPress REST nonce.
 */
function nuware_ai_rest_permission( WP_REST_Request $request ) {
	$source_url = trim( (string) $request->get_header( 'origin' ) );
	if ( '' === $source_url ) {
		$source_url = trim( (string) $request->get_header( 'referer' ) );
	}

	$site_origin = nuware_ai_url_origin( home_url( '/' ) );
	$request_origin = nuware_ai_url_origin( $source_url );
	if ( '' === $request_origin || ! hash_equals( $site_origin, $request_origin ) ) {
		return new WP_Error(
			'nuware_ai_invalid_origin',
			'This endpoint accepts requests only from the NuWare website.',
			array( 'status' => 403 )
		);
	}

	$nonce = trim( (string) $request->get_header( 'X-WP-Nonce' ) );
	if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'wp_rest' ) ) {
		return new WP_Error(
			'nuware_ai_invalid_nonce',
			'A valid WordPress REST nonce is required.',
			array( 'status' => 403 )
		);
	}

	return true;
}

/**
 * Convert internal/OpenAI failures to safe REST errors.
 */
function nuware_ai_rest_service_error( WP_Error $error ) {
	if ( 'nuware_openai_key_missing' === $error->get_error_code() ) {
		return new WP_Error(
			'nuware_ai_not_configured',
			'The NuWare assistant is not configured.',
			array( 'status' => 500 )
		);
	}

	if ( 'http_request_failed' === $error->get_error_code() && preg_match( '/timed?\s*out|curl error 28/i', $error->get_error_message() ) ) {
		return new WP_Error(
			'nuware_ai_timeout',
			'The NuWare assistant request timed out.',
			array( 'status' => 504 )
		);
	}

	return new WP_Error(
		'nuware_ai_service_error',
		'The NuWare assistant is temporarily unavailable.',
		array( 'status' => 502 )
	);
}

/**
 * Answer a sanitized question through the shared NuWare AI service.
 */
function nuware_ai_rest_ask( WP_REST_Request $request ) {
	$params = $request->get_json_params();
	if ( ! is_array( $params ) || ! array_key_exists( 'question', $params ) || ! is_string( $params['question'] ) ) {
		return new WP_Error(
			'nuware_ai_invalid_question',
			'Question must be provided as a string.',
			array( 'status' => 400 )
		);
	}

	$question = trim( sanitize_text_field( $params['question'] ) );
	if ( '' === $question ) {
		return new WP_Error(
			'nuware_ai_empty_question',
			'Question cannot be empty.',
			array( 'status' => 400 )
		);
	}

	$question_length = function_exists( 'mb_strlen' ) ? mb_strlen( $question, 'UTF-8' ) : strlen( $question );
	if ( $question_length > 500 ) {
		return new WP_Error(
			'nuware_ai_question_too_long',
			'Question must be 500 characters or fewer.',
			array( 'status' => 400 )
		);
	}

	$visitor_id = nuware_ai_limiter_visitor_id();
	if ( '' === $visitor_id ) {
		$visitor_id = nuware_ai_limiter_new_visitor_id();
	}

	$limit = nuware_ai_limiter_consume( $visitor_id );
	$reset_at = nuware_ai_limiter_reset_at( $limit['reset_at'] );
	if ( ! $limit['allowed'] ) {
		return new WP_Error(
			'nuware_ai_limit_reached',
			"You've reached today's question limit. Please check back later.",
			array(
				'status'              => 429,
				'questions_remaining' => 0,
				'reset_at'            => $reset_at,
			)
		);
	}

	nuware_ai_limiter_set_cookie( $visitor_id, $limit['reset_at'] );

	$result = nuware_openai_ask( $question, 'gpt-4.1-mini', 'gpt-4.1-nano', 45 );
	if ( is_wp_error( $result ) ) {
		return nuware_ai_rest_service_error( $result );
	}

	return rest_ensure_response(
		array(
			'category'            => $result['category'],
			'answer'              => $result['answer'],
			'questions_remaining' => $limit['questions_remaining'],
			'reset_at'            => $reset_at,
		)
	);
}

add_action(
	'rest_api_init',
	static function () {
		register_rest_route(
			'nuware/v1',
			'/ask',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'nuware_ai_rest_ask',
				'permission_callback' => 'nuware_ai_rest_permission',
			)
		);
	}
);

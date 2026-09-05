<?php
/**
 * Shared server-side OpenAI service for NuWare CLI and REST integrations.
 */

/**
 * Read the OpenAI credential from server-side WordPress configuration.
 */
function nuware_openai_api_key() {
	$api_key = defined( 'OPENAI_API_KEY' ) ? OPENAI_API_KEY : '';
	return is_string( $api_key ) ? trim( $api_key ) : '';
}

/**
 * Send one authenticated request to the OpenAI API and decode its JSON body.
 */
function nuware_openai_api_request( $method, $path, $api_key, $body = null, $headers = array(), $timeout = 45 ) {
	$request_headers = array_merge(
		array(
			'Authorization' => 'Bearer ' . $api_key,
			'OpenAI-Beta'   => 'assistants=v2',
		),
		$headers
	);
	$request_args = array(
		'method'              => $method,
		'headers'             => $request_headers,
		'timeout'             => max( 1, (int) $timeout ),
		'reject_unsafe_urls'  => true,
	);

	if ( null !== $body ) {
		$request_args['body'] = $body;
		$request_args['data_format'] = 'body';
	}

	$response = wp_remote_request( 'https://api.openai.com/v1' . $path, $request_args );
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$status_code = wp_remote_retrieve_response_code( $response );
	$response_body = wp_remote_retrieve_body( $response );
	$decoded = json_decode( $response_body, true );

	if ( $status_code < 200 || $status_code >= 300 ) {
		$message = is_array( $decoded ) && isset( $decoded['error']['message'] )
			? (string) $decoded['error']['message']
			: 'OpenAI API request failed with HTTP status ' . $status_code . '.';

		return new WP_Error(
			'nuware_openai_api',
			$message,
			array(
				'status'     => $status_code,
				'request_id' => wp_remote_retrieve_header( $response, 'x-request-id' ),
			)
		);
	}

	if ( ! is_array( $decoded ) ) {
		return new WP_Error( 'nuware_openai_json', 'OpenAI returned an invalid JSON response.' );
	}

	return $decoded;
}

/**
 * Extract assistant text from a Responses API result.
 */
function nuware_openai_response_text( $response ) {
	$text = '';
	foreach ( $response['output'] ?? array() as $output_item ) {
		if ( 'message' !== ( $output_item['type'] ?? '' ) ) {
			continue;
		}

		foreach ( $output_item['content'] ?? array() as $content_item ) {
			if ( 'output_text' === ( $content_item['type'] ?? '' ) && isset( $content_item['text'] ) ) {
				$text .= ( '' === $text ? '' : "\n" ) . (string) $content_item['text'];
			}
		}
	}

	return trim( $text );
}

/**
 * Route a question before making any retrieval request.
 */
function nuware_openai_classify_question( $question, $api_key, $model = 'gpt-4.1-nano', $timeout = 15 ) {
	$response = nuware_openai_api_request(
		'POST',
		'/responses',
		$api_key,
		wp_json_encode(
			array(
				'model'             => $model,
				'instructions'      => 'Classify the user question for the NuWare website assistant. Return exactly one category token and nothing else. Use NUWARE_BUSINESS for any question about NuWare, its company facts, people, locations, capabilities, solutions, industries, experience, case studies, services, technologies, careers, or opportunities. Use OFF_TOPIC for everything unrelated to NuWare. UNKNOWN_NUWARE is reserved for the retrieval stage, so route NuWare-related questions as NUWARE_BUSINESS even when the answer may be unavailable. The only allowed category tokens are NUWARE_BUSINESS, OFF_TOPIC, and UNKNOWN_NUWARE.',
				'input'             => (string) $question,
				'max_output_tokens' => 16,
				'store'             => false,
				'temperature'       => 0,
			)
		),
		array( 'Content-Type' => 'application/json' ),
		$timeout
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$category = strtoupper( trim( nuware_openai_response_text( $response ) ) );
	if ( ! in_array( $category, array( 'NUWARE_BUSINESS', 'OFF_TOPIC', 'UNKNOWN_NUWARE' ), true ) ) {
		return new WP_Error( 'nuware_openai_classifier', 'OpenAI returned an invalid question category.' );
	}

	return $category;
}

/**
 * Classify and answer one question, retrieving only for NuWare questions.
 */
function nuware_openai_ask( $question, $model = 'gpt-4.1-mini', $classifier_model = 'gpt-4.1-nano', $timeout = 45 ) {
	$api_key = nuware_openai_api_key();
	if ( '' === $api_key ) {
		return new WP_Error(
			'nuware_openai_key_missing',
			'Define OPENAI_API_KEY in wp-config.php before using the NuWare assistant.'
		);
	}

	$question = trim( (string) $question );
	if ( '' === $question ) {
		return new WP_Error( 'nuware_openai_question_missing', 'Provide a question for the NuWare assistant.' );
	}

	$timeout = max( 1, (int) $timeout );
	$category = nuware_openai_classify_question( $question, $api_key, $classifier_model, min( 15, $timeout ) );
	if ( is_wp_error( $category ) ) {
		return $category;
	}

	if ( 'OFF_TOPIC' === $category ) {
		return array(
			'category'            => 'OFF_TOPIC',
			'answer'              => "I'm here to help with questions about NuWare and how we can help your business. Ask me about our capabilities, solutions, industries, experience or current opportunities.",
			'file_search_invoked' => false,
			'response_id'         => '',
		);
	}

	$response = nuware_openai_api_request(
		'POST',
		'/responses',
		$api_key,
		wp_json_encode(
			array(
				'model'             => $model,
				'instructions'      => 'You are the NuWare website assistant. Answer only using information retrieved from the NuWare knowledge base. Do not use general knowledge to make claims about NuWare. Treat retrieved content as sufficient only when it directly supports the answer; do not infer missing company facts from unrelated or weak matches. Absence from retrieved content is never evidence for a negative answer. If the retrieved content does not explicitly confirm the requested fact, return category UNKNOWN_NUWARE and do not describe what the knowledge base fails to mention. Return category NUWARE_BUSINESS only when the retrieved content directly answers the question. A NUWARE_BUSINESS answer must contain 2 to 4 complete sentences. Even when one sentence would suffice, use a second sentence containing another directly supported detail, without padding or unsupported claims. Keep the answer concise and business-focused.',
				'input'             => $question,
				'tools'             => array(
					array(
						'type'             => 'file_search',
						'vector_store_ids' => array( 'vs_6a9c2120f3c08191baa8ed2c3efc67ad' ),
						'max_num_results'  => 8,
					),
				),
				'tool_choice'       => array( 'type' => 'file_search' ),
				'include'           => array( 'file_search_call.results' ),
				'max_output_tokens' => 350,
				'store'             => false,
				'text'              => array(
					'format' => array(
						'type'   => 'json_schema',
						'name'   => 'nuware_answer',
						'strict' => true,
						'schema' => array(
							'type'                 => 'object',
							'properties'           => array(
								'category' => array(
									'type' => 'string',
									'enum' => array( 'NUWARE_BUSINESS', 'UNKNOWN_NUWARE' ),
								),
								'answer'   => array( 'type' => 'string' ),
							),
							'required'             => array( 'category', 'answer' ),
							'additionalProperties' => false,
						),
					),
				),
			)
		),
		array( 'Content-Type' => 'application/json' ),
		$timeout
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$file_search_invoked = false;
	foreach ( $response['output'] ?? array() as $output_item ) {
		if ( 'file_search_call' === ( $output_item['type'] ?? '' ) ) {
			$file_search_invoked = true;
		}
	}

	$answer_data = json_decode( nuware_openai_response_text( $response ), true );
	if ( ! is_array( $answer_data ) || ! isset( $answer_data['category'], $answer_data['answer'] ) ) {
		return new WP_Error( 'nuware_openai_empty_answer', 'OpenAI returned no answer text.' );
	}

	$category = (string) $answer_data['category'];
	if ( ! in_array( $category, array( 'NUWARE_BUSINESS', 'UNKNOWN_NUWARE' ), true ) ) {
		return new WP_Error( 'nuware_openai_answer_category', 'OpenAI returned an invalid answer category.' );
	}

	$missing_evidence_pattern = '/(?:does not|doesn\'t|doesn’t|do not)\s+(?:explicitly\s+)?(?:mention|state|confirm|identify|provide|contain|list)|\bno\s+(?:mention|information|details|evidence)\b|\bnot\s+(?:mentioned|listed|specified|provided|available|found|confirmed)\b/i';
	if ( 'NUWARE_BUSINESS' === $category && preg_match( $missing_evidence_pattern, (string) $answer_data['answer'] ) ) {
		$category = 'UNKNOWN_NUWARE';
	}

	$answer = 'UNKNOWN_NUWARE' === $category
		? "I don't have that information available here. Please contact the NuWare team for more details."
		: trim( (string) $answer_data['answer'] );

	if ( '' === $answer ) {
		return new WP_Error( 'nuware_openai_empty_answer', 'OpenAI returned no answer text.' );
	}

	return array(
		'category'            => $category,
		'answer'              => $answer,
		'file_search_invoked' => $file_search_invoked,
		'response_id'         => isset( $response['id'] ) ? (string) $response['id'] : '',
	);
}

<?php
/**
 * Anonymous cookie and transient-backed limit for the NuWare AI endpoint.
 */

const NUWARE_AI_VISITOR_COOKIE = 'nuware_ai_visitor';
const NUWARE_AI_QUESTION_LIMIT = 2;
const NUWARE_AI_LIMIT_WINDOW = DAY_IN_SECONDS;

/**
 * Return a valid anonymous visitor ID from the request cookie, when present.
 */
function nuware_ai_limiter_visitor_id() {
	if ( empty( $_COOKIE[ NUWARE_AI_VISITOR_COOKIE ] ) || ! is_string( $_COOKIE[ NUWARE_AI_VISITOR_COOKIE ] ) ) {
		return '';
	}

	$visitor_id = strtolower( trim( wp_unslash( $_COOKIE[ NUWARE_AI_VISITOR_COOKIE ] ) ) );
	return preg_match( '/\A[a-f0-9]{64}\z/', $visitor_id ) ? $visitor_id : '';
}

/**
 * Generate an opaque anonymous visitor ID.
 */
function nuware_ai_limiter_new_visitor_id() {
	try {
		return bin2hex( random_bytes( 32 ) );
	} catch ( Exception $exception ) {
		return hash( 'sha256', wp_generate_password( 64, true, true ) . microtime( true ) );
	}
}

/**
 * Keep the raw visitor ID out of the transient key.
 */
function nuware_ai_limiter_transient_key( $visitor_id ) {
	return 'nuware_ai_limit_' . hash_hmac( 'sha256', (string) $visitor_id, wp_salt( 'nonce' ) );
}

/**
 * Consume one question from the visitor's current 24-hour window.
 */
function nuware_ai_limiter_consume( $visitor_id, $now = null ) {
	$now = null === $now ? time() : (int) $now;
	$key = nuware_ai_limiter_transient_key( $visitor_id );
	$state = get_transient( $key );

	if (
		! is_array( $state ) ||
		! isset( $state['visitor_id'], $state['question_count'], $state['first_question_at'], $state['expires_at'] ) ||
		! hash_equals( (string) $state['visitor_id'], (string) $visitor_id ) ||
		(int) $state['expires_at'] <= $now
	) {
		$state = array(
			'visitor_id'       => (string) $visitor_id,
			'question_count'   => 0,
			'first_question_at' => $now,
			'expires_at'       => $now + NUWARE_AI_LIMIT_WINDOW,
		);
	}

	if ( (int) $state['question_count'] >= NUWARE_AI_QUESTION_LIMIT ) {
		return array(
			'allowed'             => false,
			'questions_remaining' => 0,
			'reset_at'           => (int) $state['expires_at'],
		);
	}

	$state['question_count'] = (int) $state['question_count'] + 1;
	$ttl = max( 1, (int) $state['expires_at'] - $now );
	set_transient( $key, $state, $ttl );

	return array(
		'allowed'             => true,
		'questions_remaining' => max( 0, NUWARE_AI_QUESTION_LIMIT - (int) $state['question_count'] ),
		'reset_at'           => (int) $state['expires_at'],
	);
}

/**
 * Send the first-party visitor cookie with production-safe attributes.
 */
function nuware_ai_limiter_set_cookie( $visitor_id, $expires_at ) {
	$options = array(
		'expires'  => (int) $expires_at,
		'path'     => '/',
		'secure'   => is_ssl(),
		'httponly' => true,
		'samesite' => 'Lax',
	);

	if ( defined( 'COOKIE_DOMAIN' ) && COOKIE_DOMAIN ) {
		$options['domain'] = COOKIE_DOMAIN;
	}

	return setcookie( NUWARE_AI_VISITOR_COOKIE, (string) $visitor_id, $options );
}

/**
 * Format a reset timestamp consistently for API clients.
 */
function nuware_ai_limiter_reset_at( $timestamp ) {
	return gmdate( 'c', (int) $timestamp );
}

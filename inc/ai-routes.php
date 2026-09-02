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

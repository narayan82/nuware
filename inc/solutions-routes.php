<?php
/**
 * Pretty routes for the Solutions page tabs.
 */

add_action(
	'init',
	static function () {
		add_rewrite_rule(
			'^solutions/(applications|cloud|data|infrastructure)/?$',
			'index.php?pagename=solutions&solution_tab=$matches[1]',
			'top'
		);

		if ( '1' !== get_option( 'nuware_solutions_routes_version' ) ) {
			flush_rewrite_rules( false );
			update_option( 'nuware_solutions_routes_version', '1' );
		}
	}
);

add_filter(
	'query_vars',
	static function ( $query_vars ) {
		$query_vars[] = 'solution_tab';
		return $query_vars;
	}
);

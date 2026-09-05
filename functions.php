<?php
/**
 * NuWare theme functions.
 */

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/seo.php';
require_once get_template_directory() . '/inc/knowledge-export.php';
require_once get_template_directory() . '/inc/post-types.php';
require_once get_template_directory() . '/inc/ai-service.php';
require_once get_template_directory() . '/inc/ai-limiter.php';
require_once get_template_directory() . '/inc/ai-routes.php';
require_once get_template_directory() . '/inc/solutions-routes.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once get_template_directory() . '/inc/ai-setup.php';
}

<?php
/**
 * Export authoritative public NuWare knowledge for downstream AI systems.
 */

/**
 * Convert editor HTML and block markup to readable plain text.
 */
function nuware_knowledge_clean_text( $content ) {
	$content = is_string( $content ) ? $content : '';
	if ( '' === trim( $content ) ) {
		return '';
	}

	$content = strip_shortcodes( $content );
	$content = do_blocks( $content );
	$content = preg_replace_callback(
		'/<img\b[^>]*\balt=(?:"([^"]*)"|\'([^\']*)\')[^>]*>/i',
		static function ( $match ) {
			$alt = trim( html_entity_decode( ( $match[1] ?? '' ) ?: ( $match[2] ?? '' ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
			return strlen( $alt ) > 3 ? "\n[Image: {$alt}]\n" : '';
		},
		$content
	);
	$content = preg_replace( '/<li\b[^>]*>/i', "\n- ", $content );
	$content = preg_replace( '/<\/(?:th|td)>/i', ' | ', $content );
	$content = preg_replace( '/<br\s*\/?\s*>/i', "\n", $content );
	$content = preg_replace( '/<\/(?:p|h[1-6]|li|ul|ol|blockquote|section|article|div|figure|figcaption|table|tr)>/i', "\n", $content );
	$content = wp_strip_all_tags( $content );
	$content = html_entity_decode( $content, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$content = preg_replace( '/[ \t]+/', ' ', $content );
	$content = preg_replace( '/ *\n */', "\n", $content );
	$content = preg_replace( '/\n{3,}/', "\n\n", $content );

	return trim( $content );
}

/**
 * Return the canonical public URL used in exported links.
 */
function nuware_knowledge_production_url() {
	if ( defined( 'NUWARE_PRODUCTION_URL' ) && NUWARE_PRODUCTION_URL ) {
		$production_url = NUWARE_PRODUCTION_URL;
	} else {
		$current_url = untrailingslashit( home_url() );
		$current_host = (string) wp_parse_url( $current_url, PHP_URL_HOST );
		$production_url = $current_host && ! str_ends_with( $current_host, '.local' ) && ! in_array( $current_host, array( 'localhost', '127.0.0.1' ), true )
			? $current_url
			: 'https://www.nuware.com';
	}

	return untrailingslashit( (string) apply_filters( 'nuware_knowledge_production_url', $production_url ) );
}

/**
 * Normalize one exported string without changing its editorial wording.
 */
function nuware_knowledge_normalize_string( $value, &$local_urls_replaced ) {
	for ( $iteration = 0; $iteration < 3; $iteration++ ) {
		$decoded = html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
		if ( $decoded === $value ) {
			break;
		}
		$value = $decoded;
	}

	$value = preg_replace_callback(
		'#https?://(?:www\.)?nuware\.local(?=/|$)#i',
		static function () use ( &$local_urls_replaced ) {
			++$local_urls_replaced;
			return nuware_knowledge_production_url();
		},
		$value
	);
	$value = str_replace( array( "\r\n", "\r", "\u{00A0}" ), array( "\n", "\n", ' ' ), $value );
	$value = preg_replace( '/[ \t]+$/m', '', $value );
	$value = preg_replace( '/[ \t]*\|[ \t]*/', ' | ', $value );
	$value = preg_replace( '/(?: \|)+$/m', '', $value );
	$value = preg_replace( '/\n{3,}/', "\n\n", $value );

	return trim( $value );
}

/**
 * Remove only byte-for-byte duplicate passages from one text value.
 */
function nuware_knowledge_remove_duplicate_passages( $value, &$duplicates_removed ) {
	if ( ! str_contains( $value, "\n\n" ) ) {
		return $value;
	}

	$passages = preg_split( '/\n{2,}/u', $value );
	$seen     = array();
	$unique   = array();

	foreach ( $passages as $passage ) {
		if ( isset( $seen[ $passage ] ) ) {
			++$duplicates_removed;
			continue;
		}

		$seen[ $passage ] = true;
		$unique[]         = $passage;
	}

	return implode( "\n\n", $unique );
}

/**
 * Apply URL and text cleanup recursively while preserving the export schema.
 */
function nuware_knowledge_normalize_value( $value, &$cleanup_stats ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $key => $child ) {
			$value[ $key ] = nuware_knowledge_normalize_value( $child, $cleanup_stats );
		}
		return $value;
	}

	if ( ! is_string( $value ) ) {
		return $value;
	}

	$value = nuware_knowledge_normalize_string( $value, $cleanup_stats['local_urls_replaced'] );
	return nuware_knowledge_remove_duplicate_passages( $value, $cleanup_stats['duplicates_removed'] );
}

/**
 * Copy that is intentionally authored in templates rather than the editor.
 */
function nuware_knowledge_hardcoded_documents() {
	return array(
		array(
			'type'    => 'template-copy',
			'title'   => 'Homepage public copy',
			'section' => 'Homepage',
			'source'  => 'front-page.php',
			'content' => implode( "\n\n", array(
				'Technology. Fundamentally understood.',
				'From Assembler to AI, technology has changed dramatically. The fundamentals haven’t: logic, data, systems and sound engineering. That’s where NuWare’s strength has always been.',
				'Build what’s next. With us. Join a team where your ideas matter, your work has impact, and there’s always something new to solve.',
				'From AI to Agentic Action. We turn enterprise data into intelligence—and intelligence into outcomes that move the business forward.',
				'Everything starts as abstract data. A vast, unstructured universe of signals, systems and information waiting to be understood.',
				'AI gives data a form. We apply models, context and reasoning to uncover patterns, structure complexity and create usable intelligence.',
				'Intelligence scales into action. We embed AI into products, workflows and operations—creating unique outcomes that can adapt, automate and scale.',
				'Four worlds. One Core. Our understanding of technology runs deep enough to work across industries—adapting the same fundamentals to very different challenges.',
				'Solutions, built from the core. From applications and cloud to data and infrastructure, we bring the same fundamental understanding of technology to every challenge.',
			) ),
		),
		array(
			'type'    => 'template-copy',
			'title'   => 'AI introduction',
			'section' => 'AI',
			'source'  => 'page-ai.php',
			'content' => 'We empower businesses through Intelligent AI Transformation. AI creates value when it moves from idea to impact. NuWare utilizes AI to transform business through its own process. NuWare brings strategy, engineering and infrastructure together to turn AI into measurable business outcomes.',
		),
		array(
			'type'    => 'template-copy',
			'title'   => 'About supporting copy',
			'section' => 'About',
			'source'  => 'page-about.php',
			'content' => implode( "\n\n", array(
				'Technologies we work with.',
				'Our Story. Three decades of adapting, evolving and building what comes next.',
				'Leadership. True leaders of adapting, evolving and building what comes next.',
				'Evolution of the NuWare Brand: 1994, 2006, 2013, 2019 and 2026.',
			) ),
		),
		array(
			'type'    => 'template-copy',
			'title'   => 'Careers supporting copy',
			'section' => 'Careers',
			'source'  => 'page-careers.php',
			'content' => implode( "\n\n", array(
				'Careers at NuWare.',
				'We coded when Undo went only one step back. We coded when the internet ran at 56 Kbps. We shipped entire applications on a 512KB floppy disk. We built software when 1MB of RAM felt generous.',
				'Open positions. Explore current opportunities and apply directly.',
			) ),
		),
		array(
			'type'    => 'template-copy',
			'title'   => 'Company contact information',
			'section' => 'Contact',
			'source'  => 'footer.php',
			'content' => implode( "\n\n", array(
				'Interested? Transform Your Business With Future-Ready Tech. Let’s collaborate.',
				'NuWare Tech Corp. Global Headquarters: 100 Wood Ave South, Suite 116, Iselin, New Jersey 08830-2716. Telephone: (732) 494-0550. Email: info@nuware.com.',
				'NuWare Systems LLP. Development Center: 2/2, 1st Floor, Embassy Icon, Annexe, Infantry Road, Opposite Coffee Board, Bangalore – 560001.',
				'NuWare Systems LLP. Registered Office: 1st Floor, 60, 1st Cross 4th Main, HAL III Stage, Bengaluru, Karnataka, India - 560075. Telephone: +91 80671 66300 / +91 80671 66301.',
			) ),
		),
	);
}

/**
 * Build the complete knowledge export in memory.
 */
function nuware_build_knowledge_export( &$cleanup_stats = null ) {
	$documents = array();
	$excluded_page_slugs = array( 'sample-page', 'backup-home' );
	$pages = get_posts( array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'order'          => 'ASC',
	) );

	foreach ( $pages as $page ) {
		if ( in_array( $page->post_name, $excluded_page_slugs, true ) ) {
			continue;
		}
		$content = nuware_knowledge_clean_text( $page->post_content );
		$documents[] = array(
			'type'    => 'page',
			'title'   => get_the_title( $page ),
			'slug'    => $page->post_name,
			'url'     => get_permalink( $page ),
			'section' => $page->post_parent ? get_the_title( $page->post_parent ) : get_the_title( $page ),
			'content' => $content,
		);
	}

	$case_studies = get_posts( array(
		'post_type'      => 'case-studies',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );
	foreach ( $case_studies as $case_study ) {
		$terms = get_the_terms( $case_study->ID, 'industry' );
		$documents[] = array(
			'type'       => 'case-study',
			'title'      => get_the_title( $case_study ),
			'slug'       => $case_study->post_name,
			'url'        => get_permalink( $case_study ),
			'section'    => 'Case Studies',
			'industries' => $terms && ! is_wp_error( $terms ) ? array_values( wp_list_pluck( $terms, 'name' ) ) : array(),
			'excerpt'    => nuware_knowledge_clean_text( get_the_excerpt( $case_study ) ),
			'content'    => nuware_knowledge_clean_text( $case_study->post_content ),
		);
	}

	$positions = get_posts( array(
		'post_type'      => 'position',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	) );
	foreach ( $positions as $position ) {
		$field = static function ( $name ) use ( $position ) {
			return function_exists( 'get_field' ) ? get_field( $name, $position->ID ) : get_post_meta( $position->ID, $name, true );
		};
		$description = nuware_knowledge_clean_text( $field( 'description' ) );
		$documents[] = array(
			'type'           => 'position',
			'title'          => (string) ( $field( 'position_name' ) ?: get_the_title( $position ) ),
			'slug'           => $position->post_name,
			'url'            => get_permalink( $position ),
			'section'        => 'Careers',
			'job_code'       => (string) $field( 'job_code' ),
			'mode'           => (string) $field( 'mode' ),
			'min_experience' => $field( 'min_experience' ),
			'max_experience' => $field( 'max_experience' ),
			'location'       => (string) $field( 'location' ),
			'description'    => $description,
			'content'        => $description,
		);
	}

	$homepage_id = (int) get_option( 'page_on_front' );
	$homepage    = $homepage_id ? get_post( $homepage_id ) : get_page_by_path( 'homepage' );
	if ( ! $homepage instanceof WP_Post || 'page' !== $homepage->post_type || 'publish' !== $homepage->post_status ) {
		$homepage = get_page_by_path( 'homepage' );
	}
	if ( $homepage instanceof WP_Post && function_exists( 'get_field' ) ) {
		$counters = array();
		foreach ( (array) get_field( 'counter', $homepage->ID ) as $row ) {
			$counters[] = array(
				'value'       => $row['value'] ?? '',
				'suffix'      => (string) ( $row['suffix'] ?? '' ),
				'description' => nuware_knowledge_clean_text( $row['description'] ?? '' ),
			);
		}
		$documents[] = array(
			'type'      => 'acf',
			'title'     => 'NuWare in numbers',
			'slug'      => 'homepage-statistics',
			'url'       => home_url( '/' ),
			'section'   => 'Homepage',
			'counters'  => $counters,
			'content'   => implode( "\n", array_map( static function ( $row ) { return trim( $row['value'] . $row['suffix'] . ' ' . $row['description'] ); }, $counters ) ),
		);
		$quote = nuware_knowledge_clean_text( get_field( 'quote', $homepage->ID ) );
		if ( $quote ) {
			$documents[] = array(
				'type'         => 'acf',
				'title'        => 'Homepage quote',
				'slug'         => 'homepage-quote',
				'url'          => home_url( '/' ),
				'section'      => 'Homepage',
				'quote_author' => nuware_knowledge_clean_text( get_field( 'quote_author', $homepage->ID ) ),
				'content'      => $quote,
			);
		}
	}

	$about = get_page_by_path( 'about' );
	if ( $about instanceof WP_Post && function_exists( 'get_field' ) ) {
		$about_groups = array(
			'technologies' => array( 'name', 'link' ),
			'our_story'    => array( 'year', 'title', 'caption' ),
			'leadership'   => array( 'name', 'designation', 'linkedin' ),
		);
		foreach ( $about_groups as $group_name => $field_names ) {
			$items = array();
			foreach ( (array) get_field( $group_name, $about->ID ) as $row ) {
				$item = array();
				foreach ( $field_names as $field_name ) {
					$value = $row[ $field_name ] ?? '';
					if ( is_array( $value ) && isset( $value['url'] ) ) {
						$value = $value['url'];
					}
					$item[ $field_name ] = 'year' === $field_name ? $value : nuware_knowledge_clean_text( $value );
				}
				$items[] = $item;
			}
			$documents[] = array(
				'type'    => 'acf',
				'title'   => ucwords( str_replace( '_', ' ', $group_name ) ),
				'slug'    => 'about-' . str_replace( '_', '-', $group_name ),
				'url'     => get_permalink( $about ),
				'section' => 'About',
				'items'   => $items,
				'content' => implode( "\n", array_map( static function ( $item ) { return implode( ' — ', array_filter( array_map( 'strval', $item ) ) ); }, $items ) ),
			);
		}
	}

	$worlds = get_page_by_path( 'industries' );
	if ( $worlds instanceof WP_Post && function_exists( 'get_field' ) ) {
		$world_pages = get_pages( array( 'parent' => $worlds->ID, 'post_status' => 'publish', 'sort_column' => 'menu_order,post_title' ) );
		foreach ( $world_pages as $world ) {
			$timeline = array();
			foreach ( (array) get_field( 'timeline', $world->ID ) as $row ) {
				$timeline[] = array(
					'date'        => $row['date'] ?? '',
					'title'       => nuware_knowledge_clean_text( $row['title'] ?? '' ),
					'description' => nuware_knowledge_clean_text( $row['description'] ?? '' ),
				);
			}
			$intro_title = nuware_knowledge_clean_text( get_field( 'title', $world->ID ) );
			$intro_description = nuware_knowledge_clean_text( get_field( 'description', $world->ID ) );
			$documents[] = array(
				'type'              => 'acf',
				'title'             => get_the_title( $world ) . ' overview and timeline',
				'slug'              => $world->post_name . '-overview-timeline',
				'url'               => get_permalink( $world ),
				'section'           => 'Our Worlds',
				'intro_title'       => $intro_title,
				'intro_description' => $intro_description,
				'timeline'          => $timeline,
				'content'           => trim( $intro_title . "\n\n" . $intro_description . "\n\n" . implode( "\n", array_map( static function ( $row ) { return trim( $row['date'] . ' — ' . $row['title'] . ' — ' . $row['description'], " —" ); }, $timeline ) ) ),
			);
		}
	}

	$documents = array_merge( $documents, nuware_knowledge_hardcoded_documents() );
	$cleanup_stats = array(
		'duplicates_removed' => 0,
		'local_urls_replaced' => 0,
	);
	$documents = nuware_knowledge_normalize_value( $documents, $cleanup_stats );

	return array(
		'generated_at' => gmdate( 'c' ),
		'site'         => 'NuWare',
		'documents'    => array_values( $documents ),
	);
}

/**
 * Write the export atomically outside the public document root.
 */
function nuware_export_knowledge( $output_path = '' ) {
	$output_path = $output_path ?: dirname( ABSPATH ) . '/knowledge/nuware-knowledge.json';
	$directory   = dirname( $output_path );
	if ( ! wp_mkdir_p( $directory ) ) {
		return new WP_Error( 'knowledge_directory', 'Could not create the private knowledge directory.' );
	}

	$cleanup_stats = array();
	$export = nuware_build_knowledge_export( $cleanup_stats );
	$json   = wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( false === $json ) {
		return new WP_Error( 'knowledge_json', 'Could not encode the knowledge export.' );
	}

	$temporary = $output_path . '.tmp';
	if ( false === file_put_contents( $temporary, $json . "\n", LOCK_EX ) || ! rename( $temporary, $output_path ) ) {
		@unlink( $temporary );
		return new WP_Error( 'knowledge_write', 'Could not write the knowledge export.' );
	}
	@chmod( $output_path, 0600 );

	return array(
		'path'                => $output_path,
		'documents'           => count( $export['documents'] ),
		'bytes'               => filesize( $output_path ),
		'duplicates_removed'  => $cleanup_stats['duplicates_removed'],
		'local_urls_replaced' => $cleanup_stats['local_urls_replaced'],
	);
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command(
		'nuware export-knowledge',
		static function ( $args, $assoc_args ) {
			$result = nuware_export_knowledge( $assoc_args['output'] ?? '' );
			if ( is_wp_error( $result ) ) {
				WP_CLI::error( $result->get_error_message() );
			}
			WP_CLI::success( sprintf( 'Exported %d documents to %s (%d bytes); removed %d duplicate passages and replaced %d local URLs.', $result['documents'], $result['path'], $result['bytes'], $result['duplicates_removed'], $result['local_urls_replaced'] ) );
		}
	);
}

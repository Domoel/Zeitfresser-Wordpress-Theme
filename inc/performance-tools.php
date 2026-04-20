<?php
/**
 * Performance tools for existing media and webfonts.
 *
 * @package zeitfresser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the performance tools admin page.
 *
 * @return void
 */
function zeitfresser_register_performance_tools_page() {
	add_theme_page(
		esc_html__( 'Performance Tools', 'zeitfresser' ),
		esc_html__( 'Performance Tools', 'zeitfresser' ),
		'manage_options',
		'zeitfresser-performance-tools',
		'zeitfresser_render_performance_tools_page'
	);
}
add_action( 'admin_menu', 'zeitfresser_register_performance_tools_page' );

/**
 * Count attachments still waiting for one-time optimization.
 *
 * @return int
 */
function zeitfresser_get_pending_legacy_images_count() {
	$query = new WP_Query(
		array(
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'post_mime_type'         => 'image',
			'fields'                 => 'ids',
			'posts_per_page'         => 1,
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_zeitfresser_media_optimized',
					'compare' => 'NOT EXISTS',
				),
			),
			'no_found_rows'          => false,
			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	return (int) $query->found_posts;
}

/**
 * Process a batch of legacy images with current thumbnail/webp rules.
 *
 * @param int $batch_size Number of images to process.
 * @return array
 */
function zeitfresser_process_legacy_images_batch( $batch_size = 20 ) {
	$results = array(
		'processed' => 0,
		'updated'   => 0,
		'skipped'   => 0,
		'errors'    => 0,
	);

	$query = new WP_Query(
		array(
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'post_mime_type'         => 'image',
			'fields'                 => 'ids',
			'posts_per_page'         => absint( $batch_size ),
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'meta_query'             => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_zeitfresser_media_optimized',
					'compare' => 'NOT EXISTS',
				),
			),
			'no_found_rows'          => true,
			'cache_results'          => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	if ( empty( $query->posts ) ) {
		return $results;
	}

	foreach ( $query->posts as $attachment_id ) {
		$results['processed']++;

		$file = get_attached_file( $attachment_id );

		if ( empty( $file ) || ! file_exists( $file ) ) {
			update_post_meta( $attachment_id, '_zeitfresser_media_optimized', 'missing' );
			$results['skipped']++;
			continue;
		}

		$metadata = wp_generate_attachment_metadata( $attachment_id, $file );

		if ( is_wp_error( $metadata ) || empty( $metadata ) ) {
			$results['errors']++;
			continue;
		}

		wp_update_attachment_metadata( $attachment_id, $metadata );
		update_post_meta( $attachment_id, '_zeitfresser_media_optimized', current_time( 'mysql' ) );
		$results['updated']++;
	}

	return $results;
}

/**
 * Handle admin actions for performance tools.
 *
 * @return void
 */
function zeitfresser_handle_performance_tools_actions() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( empty( $_GET['page'] ) || 'zeitfresser-performance-tools' !== $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	if ( empty( $_GET['zeitfresser_action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	check_admin_referer( 'zeitfresser_performance_tools' );

	$action = sanitize_key( wp_unslash( $_GET['zeitfresser_action'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( 'optimize_legacy_images' === $action ) {
		$results = zeitfresser_process_legacy_images_batch( 25 );
		$args    = array(
			'page'      => 'zeitfresser-performance-tools',
			'processed' => $results['processed'],
			'updated'   => $results['updated'],
			'skipped'   => $results['skipped'],
			'errors'    => $results['errors'],
		);

		wp_safe_redirect( add_query_arg( $args, admin_url( 'themes.php' ) ) );
		exit;
	}

	if ( 'reset_legacy_images' === $action ) {
		$query = new WP_Query(
			array(
				'post_type'              => 'attachment',
				'post_status'            => 'inherit',
				'post_mime_type'         => 'image',
				'fields'                 => 'ids',
				'posts_per_page'         => -1,
				'meta_key'               => '_zeitfresser_media_optimized', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'no_found_rows'          => true,
				'cache_results'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		foreach ( $query->posts as $attachment_id ) {
			delete_post_meta( $attachment_id, '_zeitfresser_media_optimized' );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'  => 'zeitfresser-performance-tools',
					'reset' => count( $query->posts ),
				),
				admin_url( 'themes.php' )
			)
		);
		exit;
	}
}
add_action( 'admin_init', 'zeitfresser_handle_performance_tools_actions' );

/**
 * Render the performance tools page.
 *
 * @return void
 */
function zeitfresser_render_performance_tools_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$pending = zeitfresser_get_pending_legacy_images_count();
	$local   = function_exists( 'zeitfresser_get_local_webfonts_css' ) ? zeitfresser_get_local_webfont_urls( zeitfresser_get_local_webfonts_css() ) : array();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Zeitfresser Performance Tools', 'zeitfresser' ); ?></h1>
		<p><?php esc_html_e( 'Use these tools after major performance updates to warm local fonts and reprocess older uploads with the current image rules.', 'zeitfresser' ); ?></p>

		<?php if ( isset( $_GET['updated'] ) || isset( $_GET['reset'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="notice notice-success is-dismissible">
				<p>
					<?php
					if ( isset( $_GET['reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						printf(
							esc_html__( 'Reset complete. %d legacy image markers removed.', 'zeitfresser' ),
							absint( $_GET['reset'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						);
					} else {
						printf(
							esc_html__( 'Batch finished. Processed: %1$d, updated: %2$d, skipped: %3$d, errors: %4$d.', 'zeitfresser' ),
							absint( $_GET['processed'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
							absint( $_GET['updated'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
							absint( $_GET['skipped'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
							absint( $_GET['errors'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						);
					}
					?>
				</p>
			</div>
		<?php endif; ?>

		<div class="card" style="max-width: 880px; padding: 20px;">
			<h2><?php esc_html_e( 'Legacy Image Optimization', 'zeitfresser' ); ?></h2>
			<p>
				<?php
				printf(
					esc_html__( '%d image attachments have not been reprocessed with the current size, quality and WebP rules yet.', 'zeitfresser' ),
					(int) $pending
				);
				?>
			</p>
			<p><?php esc_html_e( 'Run the batch button repeatedly until the counter reaches zero. This keeps each request small and safe on shared hosting.', 'zeitfresser' ); ?></p>
			<p>
				<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'zeitfresser-performance-tools', 'zeitfresser_action' => 'optimize_legacy_images' ), admin_url( 'themes.php' ) ), 'zeitfresser_performance_tools' ) ); ?>">
					<?php esc_html_e( 'Process Next 25 Images', 'zeitfresser' ); ?>
				</a>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'zeitfresser-performance-tools', 'zeitfresser_action' => 'reset_legacy_images' ), admin_url( 'themes.php' ) ), 'zeitfresser_performance_tools' ) ); ?>">
					<?php esc_html_e( 'Reset Progress', 'zeitfresser' ); ?>
				</a>
			</p>
		</div>

		<div class="card" style="max-width: 880px; padding: 20px; margin-top: 20px;">
			<h2><?php esc_html_e( 'Local Font Warmup', 'zeitfresser' ); ?></h2>
			<p><?php esc_html_e( 'Zeitfresser now prefers locally cached Google font files for the currently selected font families. When the cache is available, the theme preloads the local files and no longer needs the external stylesheet request.', 'zeitfresser' ); ?></p>
			<p>
				<?php
				if ( empty( $local ) ) {
					esc_html_e( 'Local font files are not cached yet. Visit the front-end once after saving your font settings to warm the cache.', 'zeitfresser' );
				} else {
					printf(
						esc_html__( 'Local font files ready: %d preloadable assets detected.', 'zeitfresser' ),
						count( $local )
					);
				}
				?>
			</p>
		</div>
	</div>
	<?php
}

<?php
/**
 * Image Optimizer
 *
 * @package zeitfresser
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register admin page
 */
function zeitfresser_register_image_optimizer_page() {
    add_theme_page(
        'Image Optimizer',
        'Image Optimizer',
        'manage_options',
        'zeitfresser-image-optimizer',
        'zeitfresser_render_image_optimizer_page'
    );
}
add_action( 'admin_menu', 'zeitfresser_register_image_optimizer_page' );

/**
 * Count pending images
 */
function zeitfresser_get_pending_legacy_images_count() {
    $query = new WP_Query([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'fields'         => 'ids',
        'posts_per_page' => 1,
        'meta_query'     => [
            'relation' => 'OR',
            [
                'key'     => '_zeitfresser_media_optimized_version',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key'     => '_zeitfresser_media_optimized_version',
                'value'   => ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION,
                'compare' => '!=',
            ],
        ],
        'no_found_rows' => false,
    ]);

    return (int) $query->found_posts;
}

/**
 * Count total images
 */
function zeitfresser_get_total_images_count() {
    $query = new WP_Query([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'fields'         => 'ids',
        'posts_per_page' => 1,
        'no_found_rows'  => false,
    ]);

    return (int) $query->found_posts;
}

/**
 * NEW: Cleanup counters (ONLY ADDITIVE)
 */
function zeitfresser_get_total_originals_count() {
    $query = new WP_Query([
        'post_type'=>'attachment',
        'post_status'=>'inherit',
        'post_mime_type'=>'image',
        'posts_per_page'=>1,
        'fields'=>'ids',
        'meta_query'=>[
            ['key'=>'_zeitfresser_original_file','compare'=>'EXISTS']
        ],
        'no_found_rows'=>false
    ]);
    return (int) $query->found_posts;
}

function zeitfresser_get_remaining_originals_count() {
    $query = new WP_Query([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => '_zeitfresser_original_file',
                'compare' => 'EXISTS',
            ],
            [
                'key'     => '_zeitfresser_original_deleted',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key'     => '_zeitfresser_media_optimized_version',
                'value'   => ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION,
                'compare' => '=',
            ],
        ],
        'no_found_rows' => false,
    ]);

    return (int) $query->found_posts;
}

function zeitfresser_get_unoptimized_originals_count() {
    $query = new WP_Query([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'posts_per_page' => 1,
        'fields'         => 'ids',
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => '_zeitfresser_original_file',
                'compare' => 'EXISTS',
            ],
            [
                'key'     => '_zeitfresser_original_deleted',
                'compare' => 'NOT EXISTS',
            ],
            [
                'relation' => 'OR',
                [
                    'key'     => '_zeitfresser_media_optimized_version',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => '_zeitfresser_media_optimized_version',
                    'value'   => ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION,
                    'compare' => '!=',
                ],
            ],
        ],
        'no_found_rows' => false,
    ]);

    return (int) $query->found_posts;
}

/**
 * Build a list of original-format files belonging to one attachment.
 *
 * This includes:
 * - the original uploaded file
 * - the original-format main generated file (e.g. scaled JPG)
 * - original-format sub-size files derived from attachment metadata
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $original      Absolute path to the original uploaded file.
 * @return array
 */
function zeitfresser_get_original_family_files( $attachment_id, $original ) {

    $files = [];

    if ( empty( $original ) ) {
        return $files;
    }

    $original_ext = strtolower( pathinfo( $original, PATHINFO_EXTENSION ) );
    $base_name    = pathinfo( $original, PATHINFO_FILENAME );
    $dir          = dirname( $original );

    if ( empty( $original_ext ) ) {
        return $files;
    }

    // Original
    $files[] = $original;

    // --- ORIGINAL LOGIC (metadata-based) ---
    $metadata   = wp_get_attachment_metadata( $attachment_id );
    $upload_dir = wp_get_upload_dir();

    if ( ! empty( $metadata['file'] ) ) {

        $current_main_absolute = trailingslashit( $upload_dir['basedir'] ) . $metadata['file'];

        // scaled/main
        $files[] = preg_replace(
            '/\.[^.]+$/',
            '.' . $original_ext,
            $current_main_absolute
        );

        $current_dir = dirname( $current_main_absolute );

        if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
            foreach ( $metadata['sizes'] as $size ) {
                if ( empty( $size['file'] ) ) {
                    continue;
                }

                $files[] = $current_dir . '/' . preg_replace(
                    '/\.[^.]+$/',
                    '.' . $original_ext,
                    $size['file']
                );
            }
        }
    }

    // --- NEW: FILESYSTEM FALLBACK (CRITICAL FIX) ---
    foreach ( glob( $dir . '/' . $base_name . '*.' . $original_ext ) as $file ) {
        $files[] = $file;
    }

    return array_values( array_unique( array_filter( $files ) ) );
}

/**
 * Check whether any original-format family files still exist.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $original      Absolute path to the original uploaded file.
 * @return bool
 */
function zeitfresser_original_family_exists( $attachment_id, $original ) {

    $files = zeitfresser_get_original_family_files( $attachment_id, $original );

    foreach ( $files as $file ) {
        if ( file_exists( $file ) ) {
            return true;
        }
    }

    return false;
}

/**
 * Delete all original-format files belonging to one attachment.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $original      Absolute path to the original uploaded file.
 * @return int Number of deleted files.
 */
function zeitfresser_delete_original_family_files( $attachment_id, $original ) {

    $deleted_files = 0;
    $files         = zeitfresser_get_original_family_files( $attachment_id, $original );

    foreach ( $files as $file ) {
        if ( ! file_exists( $file ) ) {
            continue;
        }

        if ( ! is_writable( $file ) ) {
            continue;
        }

        if ( unlink( $file ) ) {
            $deleted_files++;
        }
    }

    return $deleted_files;
}

/**
 * DELETE ORIGINALS (manual batch)
 */
function zeitfresser_delete_originals_batch( $batch_size = 10 ) {

    $deleted = 0;

    $query = new WP_Query([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'fields'         => 'ids',
        'posts_per_page' => $batch_size,
        'meta_query'     => [
            'relation' => 'AND',
            [
                'key'     => '_zeitfresser_original_file',
                'compare' => 'EXISTS',
            ],
            [
                'key'     => '_zeitfresser_original_deleted',
                'compare' => 'NOT EXISTS',
            ],
        ],
    ]);

    foreach ( $query->posts as $attachment_id ) {

        $original = get_post_meta( $attachment_id, '_zeitfresser_original_file', true );

        if ( ! $original ) {
            continue;
        }

        $optimized_version = get_post_meta(
            $attachment_id,
            '_zeitfresser_media_optimized_version',
            true
        );

        if ( ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION !== $optimized_version ) {
            continue;
        }

        $ext = strtolower( pathinfo( $original, PATHINFO_EXTENSION ) );

        if ( in_array( $ext, [ 'webp', 'avif' ], true ) ) {
            update_post_meta( $attachment_id, '_zeitfresser_original_deleted', 1 );
            continue;
        }

        // Nothing left to delete -> mark as done.
        if ( ! zeitfresser_original_family_exists( $attachment_id, $original ) ) {
            update_post_meta( $attachment_id, '_zeitfresser_original_deleted', 1 );
            continue;
        }

        zeitfresser_delete_original_family_files( $attachment_id, $original );

        // Only mark as deleted when the full original family is gone.
        if ( ! zeitfresser_original_family_exists( $attachment_id, $original ) ) {
            $deleted++;
            update_post_meta( $attachment_id, '_zeitfresser_original_deleted', 1 );
        }
    }

    return $deleted;
}

/**
 * Optimizer batch for manual processing.
 *
 * Manual optimization must work independently of the auto-optimize upload toggle.
 *
 * @param int $batch_size Number of images per batch.
 * @return array
 */
function zeitfresser_process_legacy_images_batch( $batch_size = 25 ) {

    $results = [
        'processed' => 0,
        'updated'   => 0,
        'skipped'   => 0,
        'errors'    => 0,
    ];

    $query = new WP_Query([
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'post_mime_type' => 'image',
        'fields'         => 'ids',
        'posts_per_page' => $batch_size,
        'meta_query'     => [
            'relation' => 'OR',
            [
                'key'     => '_zeitfresser_media_optimized_version',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key'     => '_zeitfresser_media_optimized_version',
                'value'   => ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION,
                'compare' => '!=',
            ],
        ],
    ]);

    // Force optimization for manual tool runs, regardless of upload automation setting.
    $GLOBALS['zeitfresser_force_image_optimization'] = true;

    foreach ( $query->posts as $attachment_id ) {

        $results['processed']++;

        $file = get_attached_file( $attachment_id );

        if ( empty( $file ) || ! file_exists( $file ) ) {
            update_post_meta( $attachment_id, '_zeitfresser_media_optimized_version', 'missing' );
            $results['skipped']++;
            continue;
        }

        if ( ! get_post_meta( $attachment_id, '_zeitfresser_original_file', true ) ) {
            update_post_meta( $attachment_id, '_zeitfresser_original_file', $file );
        }

        $metadata = wp_generate_attachment_metadata( $attachment_id, $file );

        if ( is_wp_error( $metadata ) || empty( $metadata ) ) {
            $results['errors']++;
            continue;
        }

        wp_update_attachment_metadata( $attachment_id, $metadata );

        update_post_meta(
            $attachment_id,
            '_zeitfresser_media_optimized_version',
            ZEITFRESSER_IMAGE_OPTIMIZATION_VERSION
        );

        $results['updated']++;
    }

    unset( $GLOBALS['zeitfresser_force_image_optimization'] );

    return $results;
}

/**
 * AJAX: Optimizer (extended output only)
 */
function zeitfresser_ajax_optimize_images() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error();
    }

    check_ajax_referer( 'zeitfresser_image_optimizer', 'nonce' );

    $results = zeitfresser_process_legacy_images_batch( 25 );

    $pending             = zeitfresser_get_pending_legacy_images_count();
    $total               = zeitfresser_get_total_images_count();

    $cleanup_total       = zeitfresser_get_total_originals_count();
    $cleanup_remaining   = zeitfresser_get_remaining_originals_count();
    $cleanup_unoptimized = zeitfresser_get_unoptimized_originals_count();
    $cleanup_deleted     = $cleanup_total - ( $cleanup_remaining + $cleanup_unoptimized );
    $cleanup_progress    = $cleanup_total > 0
        ? round( ( $cleanup_deleted / $cleanup_total ) * 100 )
        : 0;

    wp_send_json_success([
        'processed'            => $results['processed'],
        'updated'              => $results['updated'],
        'pending'              => $pending,
        'total'                => $total,
        'cleanup_total'        => $cleanup_total,
        'cleanup_remaining'    => $cleanup_remaining,
        'cleanup_unoptimized'  => $cleanup_unoptimized,
        'cleanup_deleted'      => $cleanup_deleted,
        'cleanup_progress'     => $cleanup_progress,
    ]);
}
add_action( 'wp_ajax_zeitfresser_optimize_images', 'zeitfresser_ajax_optimize_images' );

/**
 * AJAX: Delete (extended ONLY)
 */
function zeitfresser_ajax_delete_originals() {

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error();
    }

    check_ajax_referer( 'zeitfresser_image_optimizer', 'nonce' );

    $deleted       = zeitfresser_delete_originals_batch( 10 );
    $total         = zeitfresser_get_total_originals_count();
    $remaining     = zeitfresser_get_remaining_originals_count();
    $unoptimized   = zeitfresser_get_unoptimized_originals_count();
    $deleted_total = $total - ( $remaining + $unoptimized );

    wp_send_json_success([
        'deleted'       => $deleted,
        'total'         => $total,
        'remaining'     => $remaining,
        'unoptimized'   => $unoptimized,
        'deleted_total' => $deleted_total,
    ]);
}
add_action( 'wp_ajax_zeitfresser_delete_originals', 'zeitfresser_ajax_delete_originals' );

/**
 * Render UI
 */
function zeitfresser_render_image_optimizer_page() {

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $pending   = zeitfresser_get_pending_legacy_images_count();
    $total     = zeitfresser_get_total_images_count();
    $optimized = $total - $pending;
    $progress  = $total > 0 ? round(($optimized / $total) * 100) : 0;

    // 🔥 NEW: Cleanup counters
    $cleanup_total       = zeitfresser_get_total_originals_count();
    $cleanup_remaining   = zeitfresser_get_remaining_originals_count();
    $cleanup_unoptimized = zeitfresser_get_unoptimized_originals_count();
    $cleanup_deleted     = $cleanup_total - ( $cleanup_remaining + $cleanup_unoptimized );
    $cleanup_progress    = $cleanup_total > 0 ? round(($cleanup_deleted / $cleanup_total) * 100) : 0;
    
    $cleanup_nothing_to_do = (
        0 === $cleanup_remaining &&
        (
            $cleanup_unoptimized > 0 ||
            $cleanup_deleted >= $cleanup_total
        )
    );

    $cleanup_button_disabled = $cleanup_nothing_to_do;
    $cleanup_button_label    = $cleanup_nothing_to_do
        ? '🧹 Nothing to clean yet'
        : '🧹 Delete Originals';
?>

<div class="wrap">
    <h1>Zeitfresser Image Optimizer</h1>
    
    <div class="notice notice-info" style="max-width:800px;margin-top:20px;">
        <p>
            <strong>How this tool works</strong><br><br>

            This tool helps you optimize your existing media library for better performance.<br><br>

            • Images are converted to modern formats (AVIF/WebP) for smaller file sizes.<br>
            • The original file path is safely stored before optimization.<br>
            • Once optimized, original images can be deleted to save disk space.<br><br>

            <strong>Automation:</strong><br>
            • You can enable automatic optimization on upload in the Customizer under <em>Image Optimizer Settings</em>.<br>
            • Optionally, original images can also be deleted automatically after successful optimization.<br><br>

            <strong>Safety:</strong><br>
            • Images are only processed once per version.<br>
            • Original files are only deleted when safe.<br>
            • The tool can be run multiple times without side effects.<br><br>

            <em><strong>Tip:</strong> You can either automate the process via the Customizer or use this tool manually for full control.</em>
        </p>
    </div>

    <!-- OPTIMIZATION -->
    <div class="card" style="max-width:800px;padding:24px;margin-bottom:20px;">

        <h2 style="margin-top:0;">🚀 Image Optimization</h2>

        <div style="margin-bottom:20px;">
            <p><strong>Total Images:</strong> <span id="total"><?php echo $total; ?></span></p>
            <p><strong>Optimized:</strong> <span id="optimized"><?php echo $optimized; ?></span></p>
            <p><strong>Pending:</strong> <span id="remaining"><?php echo $pending; ?></span></p>
        </div>

        <div style="margin-bottom:20px;">
            <div style="background:#e0e0e0;border-radius:6px;height:12px;">
                <div id="progress-bar" style="width:<?php echo $progress; ?>%;background:#4caf50;height:100%;"></div>
            </div>
            <p><strong>Progress:</strong> <span id="progress"><?php echo $progress; ?></span>%</p>
        </div>

        <button id="start-btn" class="button button-primary">🚀 Optimize Images</button>
    </div>

    <!-- CLEANUP -->
    <div class="card" style="max-width:800px;padding:24px;">

        <h2>🧹 Original Cleanup</h2>

        <div style="margin-bottom:20px;">
            <p><strong>Total Originals:</strong> <span id="cleanup-total"><?php echo $cleanup_total; ?></span></p>
            <p><strong>Deleted:</strong> <span id="cleanup-deleted"><?php echo $cleanup_deleted; ?></span></p>
            <p><strong>Ready for Cleanup:</strong> <span id="cleanup-remaining"><?php echo $cleanup_remaining; ?></span></p>
            <p><strong>Not Optimized Yet:</strong> <span id="cleanup-unoptimized"><?php echo $cleanup_unoptimized; ?></span></p>
        </div>

        <div style="margin-bottom:20px;">
            <div style="background:#e0e0e0;border-radius:6px;height:12px;">
                <div id="cleanup-bar" style="width:<?php echo $cleanup_progress; ?>%;background:<?php echo $cleanup_progress === 100 ? '#4caf50' : '#ff9800'; ?>;height:100%;"></div>
            </div>
            <p><strong>Cleanup Progress:</strong> <span id="cleanup-progress"><?php echo $cleanup_progress; ?></span>%</p>
        </div>

        <button id="delete-btn" class="button" <?php disabled( $cleanup_button_disabled ); ?>>
            <?php echo esc_html( $cleanup_button_label ); ?>
        </button>
    </div>

    <!-- STATUS -->
    <div style="padding:12px;background:#f6f7f7;border-radius:6px;">
        <p id="status-opt">🚀 Optimizer: Idle</p>
        <p id="status-clean">🧹 Cleanup: Idle</p>
    </div>

</div>

<style>
#progress-bar,
#cleanup-bar {
    transition: width 0.35s ease, background 0.3s ease;
}
</style>

<script>
let running = false;
let deleting = false;

(function initCleanupBar() {
    const bar = document.getElementById('cleanup-bar');
    const progress = parseInt(document.getElementById('cleanup-progress').innerText, 10);

    if (!bar || isNaN(progress)) return;

    if (progress >= 100) {
        bar.style.background = '#4caf50';
    } else if (progress > 0) {
        bar.style.background = '#ff9800';
    }
})();

document.getElementById('start-btn').onclick = () => {
    running = true;

    document.getElementById('start-btn').disabled = true;
    document.getElementById('delete-btn').disabled = true;

    document.getElementById('start-btn').innerText = '⏳ Running...';
    document.getElementById('status-opt').innerText = '🚀 Optimizing images...';

    processBatch();
};

document.getElementById('delete-btn').onclick = () => {
    deleting = true;

    document.getElementById('start-btn').disabled = true;
    document.getElementById('delete-btn').disabled = true;

    document.getElementById('delete-btn').innerText = '⏳ Running...';
    document.getElementById('status-clean').innerText = '🧹 Cleaning originals...';

    deleteBatch();
};

function deleteBatch() {
    if (!deleting) return;

    fetch(ajaxurl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'zeitfresser_delete_originals',
            nonce: '<?php echo wp_create_nonce('zeitfresser_image_optimizer'); ?>'
        })
    })
    .then(res => res.json())
    .then(data => {

        let total       = data.data.total;
        let remaining   = data.data.remaining;
        let deleted     = data.data.deleted_total;
        let batch       = data.data.deleted;
        let unoptimized = data.data.unoptimized;

        let progress = total > 0 ? Math.round((deleted / total) * 100) : 0;

        // Update Cleanup UI
        document.getElementById('cleanup-total').innerText = total;
        document.getElementById('cleanup-deleted').innerText = deleted;
        document.getElementById('cleanup-remaining').innerText = remaining;
        document.getElementById('cleanup-unoptimized').innerText = unoptimized;
        document.getElementById('cleanup-progress').innerText = progress;

        let bar = document.getElementById('cleanup-bar');
        bar.style.width = progress + '%';

        if (progress >= 100) {
            bar.style.background = '#4caf50';
        } else if (progress > 0) {
            bar.style.background = '#ff9800';
        }

        if (batch > 0) {
            document.getElementById('status-clean').innerText = '🧹 Deleted: ' + batch;
        }

        // Nothing cleanup-eligible remains, but originals still exist and are not optimized
        if (remaining === 0 && unoptimized > 0) {
            deleting = false;

            document.getElementById('start-btn').disabled = false;
            document.getElementById('delete-btn').disabled = true;
            document.getElementById('delete-btn').innerText = '🧹 Nothing to clean yet';

            document.getElementById('status-clean').innerText =
                '⚠ Available images are not optimized yet. Please optimize them before starting cleanup.';

            return;
        }

        if (remaining > 0) {
            setTimeout(deleteBatch, 400);
        } else {
            deleting = false;

            document.getElementById('start-btn').disabled = false;

            if (remaining === 0 && unoptimized > 0) {
                document.getElementById('delete-btn').disabled = true;
                document.getElementById('delete-btn').innerText = '🧹 Nothing to clean yet';
            } else if (remaining === 0 && total === deleted) {
                document.getElementById('delete-btn').disabled = true;
                document.getElementById('delete-btn').innerText = '🧹 Nothing to clean yet';
            } else {
                document.getElementById('delete-btn').disabled = false;
                document.getElementById('delete-btn').innerText = '🧹 Delete Originals';
            }

            document.getElementById('status-clean').innerText = '✔ Cleanup complete';
        }
    });
}

function processBatch() {
    if (!running) return;

    fetch(ajaxurl, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'zeitfresser_optimize_images',
            nonce: '<?php echo wp_create_nonce('zeitfresser_image_optimizer'); ?>'
        })
    })
    .then(res => res.json())
    .then(data => {

        let pending   = data.data.pending;
        let total     = data.data.total;
        let optimized = total - pending;
        let progress  = total > 0 ? Math.round((optimized / total) * 100) : 0;

        // Live update optimization counters
        document.getElementById('total').innerText = total;
        document.getElementById('optimized').innerText = optimized;
        document.getElementById('remaining').innerText = pending;

        // Live update optimization progress
        document.getElementById('progress').innerText = progress;
        document.getElementById('progress-bar').style.width = progress + '%';

        // Live update cleanup counters
        document.getElementById('cleanup-total').innerText = data.data.cleanup_total;
        document.getElementById('cleanup-deleted').innerText = data.data.cleanup_deleted;
        document.getElementById('cleanup-remaining').innerText = data.data.cleanup_remaining;
        document.getElementById('cleanup-unoptimized').innerText = data.data.cleanup_unoptimized;
        document.getElementById('cleanup-progress').innerText = data.data.cleanup_progress;

        const cleanupBar = document.getElementById('cleanup-bar');
        cleanupBar.style.width = data.data.cleanup_progress + '%';

        if (data.data.cleanup_progress >= 100) {
            cleanupBar.style.background = '#4caf50';
        } else if (data.data.cleanup_progress > 0) {
            cleanupBar.style.background = '#ff9800';
        }

        // Update cleanup button state based on fresh server data
        if (data.data.cleanup_remaining === 0 && data.data.cleanup_unoptimized > 0) {
            document.getElementById('delete-btn').disabled = true;
            document.getElementById('delete-btn').innerText = '🧹 Nothing to clean yet';
        } else {
            document.getElementById('delete-btn').disabled = false;
            document.getElementById('delete-btn').innerText = '🧹 Delete Originals';
        }

        document.getElementById('status-opt').innerText =
            '🚀 Processed: ' + data.data.processed +
            ' | Updated: ' + data.data.updated;

        if (pending > 0) {
            setTimeout(processBatch, 400);
        } else {
            running = false;

            document.getElementById('start-btn').disabled = false;
            document.getElementById('delete-btn').disabled =
                (data.data.cleanup_remaining === 0 && data.data.cleanup_unoptimized > 0);

            document.getElementById('start-btn').innerText = '🚀 Optimize Images';
            document.getElementById('status-opt').innerText = '✔ Optimization complete';
        }
    });
}
</script>

<?php
}

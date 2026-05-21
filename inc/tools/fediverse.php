<?php
/**
 * Fediverse & ActivityPub Integration Logic
 * @package zeitfresser
 */

// Sicherheits-Check: Verhindert direkten Aufruf der Datei über die URL
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Holt die Anzahl der Fediverse-Interaktionen
 */
function zeitfresser_get_fediverse_metrics( $post_id ) {
    $likes = get_comments( array(
        'post_id'      => $post_id,
        'status'       => 'approve',
        'type'         => 'like', 
        'count'        => true
    ) );

    $boosts = get_comments( array(
        'post_id'      => $post_id,
        'status'       => 'approve',
        'type__in'     => array( 'announce', 'repost' ),
        'count'        => true
    ) );

    return array(
        'likes'  => (int) $likes,
        'boosts' => (int) $boosts,
        'total'  => (int) ($likes + $boosts)
    );
}

/**
 * Generiert das HTML für die getrennten Fediverse-Icons
 */
function zeitfresser_render_fediverse_meta() {
    $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
    if ( ! in_array( 'activitypub/activitypub.php', $active_plugins, true ) ) {
        return '';
    }

    $post_id = get_the_ID();
    $metrics = zeitfresser_get_fediverse_metrics( $post_id );
    $ap_permalink = get_post_meta( $post_id, 'activitypub_canonical_url', true ) ?: get_permalink( $post_id );

    $js_onclick = "event.preventDefault(); let inst = prompt('Enter your Fediverse Instance here! (e.g. mastodon.social):'); if(inst) { inst = inst.replace(/^https?:\/\//, '').split('/')[0]; window.open('https://' + inst + '/authorize_interaction?uri=" . esc_url( $ap_permalink ) . "', '_blank'); }";

    $tooltip = sprintf(
        'Klick, to interact with the Fediverse! (%d Likes, %d Boosts)',
        $metrics['likes'],
        $metrics['boosts']
    );

    $html = '<span class="fediverse-meta-wrapper" style="display:inline-flex; align-items:center; gap:12px; margin-left:8px; vertical-align:middle;">';

    // 1. LIKES-ICON
    $html .= '<a href="#" onclick="' . esc_attr( $js_onclick ) . '" style="text-decoration:none; color:inherit; display:inline-flex; align-items:center; gap:4px; vertical-align:middle;" title="' . esc_attr( $tooltip ) . '">';
    $html .= '<svg style="display:block;" width="18px" height="18px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
    $html .= '<span>' . esc_html( $metrics['likes'] ) . '</span>';
    $html .= '</a>';

    // 2. BOOSTS-ICON
    $html .= '<a href="#" onclick="' . esc_attr( $js_onclick ) . '" style="text-decoration:none; color:inherit; display:inline-flex; align-items:center; gap:4px; vertical-align:middle;" title="' . esc_attr( $tooltip ) . '">';
    $html .= '<svg style="display:block;" width="18px" height="18px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>';
    $html .= '<span>' . esc_html( $metrics['boosts'] ) . '</span>';
    $html .= '</a>';

    $html .= '</span>';

    return $html;
}

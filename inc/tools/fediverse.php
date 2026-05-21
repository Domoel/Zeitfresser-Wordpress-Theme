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
    // Nutzt native WP-Funktionen. Stürzt nicht ab, wenn das Plugin fehlt.
    $likes = get_comments( array(
        'post_id'      => $post_id,
        'status'       => 'approve',
        'comment_type' => 'like',
        'count'        => true
    ) );

    $boosts = get_comments( array(
        'post_id'      => $post_id,
        'status'       => 'approve',
        'comment_type' => 'announce',
        'count'        => true
    ) );

    return array(
        'likes'  => (int) $likes,
        'boosts' => (int) $boosts,
        'total'  => (int) ($likes + $boosts)
    );
}

/**
 * Generiert das HTML für das Fediverse-Icon in der Meta-Zeile
 */
function zeitfresser_render_fediverse_meta() {
    
    // Failsafe
    $active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
    if ( ! in_array( 'activitypub/activitypub.php', $active_plugins ) ) {
        return ''; 
    }

    $post_id = get_the_ID();
    $metrics = zeitfresser_get_fediverse_metrics( $post_id );
    
    // ActivityPub-URL für die Zwischenablage holen
    $ap_permalink = get_post_meta( $post_id, 'activitypub_canonical_url', true );
    if ( ! $ap_permalink ) {
        $ap_permalink = get_permalink( $post_id );
    }

    // Tooltip anpassen, um die Aktion zu erklären
    $tooltip = sprintf( 'Klicke, um den Link zu kopieren und im Fediverse zu interagieren (%d Likes, %d Boosts)', $metrics['likes'], $metrics['boosts'] );

    // Der interaktive Mastodon/Fediverse Intent-Aufruf
    $js_onclick = "
        event.preventDefault();
        let inst = prompt('Um zu interagieren, gib deine Fediverse-Instanz ein (z. B. mastodon.social):');
        if (inst) {
            /* Bereinigt die Eingabe (entfernt https:// und Pfade) */
            inst = inst.replace(/^https?:\/\//, '').split('/')[0];
            /* Leitet zur standardisierten Mastodon-Interaktions-Seite weiter */
            window.open('https://' + inst + '/authorize_interaction?uri=' + encodeURIComponent('" . esc_url( $ap_permalink ) . "'), '_blank');
        }
    ";

    // HTML-Aufbau
    $html  = '<span class="comments fediverse-reactions" title="' . esc_attr( $tooltip ) . '">';
    $html .= '  <a href="#" onclick="' . esc_attr( $js_onclick ) . '" style="text-decoration: none; color: inherit; display: inline-flex; align-items: center; gap: 4px;">';
    
    // Stern-SVG
    $html .= '    <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">';
    $html .= '      <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>';
    $html .= '    </svg>';
    
    $html .= '    <span class="count">' . $metrics['total'] . '</span>';
    $html .= '  </a>';
    $html .= '</span>';

    return $html;
}

<?php
/**
 * Fediverse RSS Module - Settings, Widget & Shortcode
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// =========================================================================
// 1. HILFSFUNKTIONEN
// =========================================================================

function gts_fediverse_default_avatar() {
    return 'data:image/svg+xml;utf8,' . rawurlencode(
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#8b949e"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/></svg>'
    );
}

function gts_fediverse_escape_img_src( $src ) {
    if ( strpos( $src, 'data:image/svg+xml' ) === 0 ) {
        return esc_attr( $src );
    }

    return esc_url( $src );
}

function gts_fediverse_absolute_url( $url, $base_url ) {
    if ( empty( $url ) ) return '';
    if ( preg_match( '/^https?:\/\//i', $url ) ) return $url;

    $base = wp_parse_url( $base_url );
    if ( empty( $base['scheme'] ) || empty( $base['host'] ) ) return $url;

    if ( strpos( $url, '/' ) === 0 ) {
        return $base['scheme'] . '://' . $base['host'] . $url;
    }

    return $base['scheme'] . '://' . $base['host'] . '/' . ltrim( $url, '/' );
}

function gts_fediverse_get_avatar( $feed_url, $profile_url ) {
    $fallback_avatar = gts_fediverse_default_avatar();
    $cache_key       = 'gts_fediverse_avatar_raw_' . md5( $feed_url );
    $cached          = get_transient( $cache_key );

    if ( is_string( $cached ) && $cached !== '' ) {
        return $cached;
    }

    $avatar_url = '';

    $rss_response = wp_remote_get( $feed_url, array(
        'timeout' => 8,
        'headers' => array(
            'User-Agent' => 'Mozilla/5.0 Zeitfresser-Fediverse-Widget/1.0',
        ),
    ) );

    if ( ! is_wp_error( $rss_response ) && wp_remote_retrieve_response_code( $rss_response ) === 200 ) {
        $xml = wp_remote_retrieve_body( $rss_response );

        if ( preg_match( '/<image>.*?<url>\s*(http[^<]+)\s*<\/url>.*?<\/image>/is', $xml, $matches ) ) {
            $avatar_url = trim( $matches[1] );
        }
    }

    if ( empty( $avatar_url ) ) {
        $html_response = wp_remote_get( $profile_url, array(
            'timeout' => 8,
            'headers' => array(
                'User-Agent'      => 'Mozilla/5.0 Zeitfresser-Fediverse-Widget/1.0',
                'Accept'          => 'text/html,application/xhtml+xml,application/xml',
                'Accept-Language' => 'de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7',
            ),
        ) );

        if ( ! is_wp_error( $html_response ) && wp_remote_retrieve_response_code( $html_response ) === 200 ) {
            $html = wp_remote_retrieve_body( $html_response );

            if ( preg_match( '/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $html, $matches ) ) {
                $avatar_url = html_entity_decode( $matches[1] );
            } elseif ( preg_match( '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i', $html, $matches ) ) {
                $avatar_url = html_entity_decode( $matches[1] );
            } elseif ( preg_match( '/<img[^>]+class=["\'][^"\']*(?:u-photo|avatar)[^"\']*["\'][^>]+src=["\']([^"\']+)["\']/i', $html, $matches ) ) {
                $avatar_url = html_entity_decode( $matches[1] );
            }
        }
    }

    if ( ! empty( $avatar_url ) ) {
        $avatar_url = esc_url_raw( gts_fediverse_absolute_url( $avatar_url, $profile_url ) );

        if ( ! empty( $avatar_url ) ) {
            set_transient( $cache_key, $avatar_url, 6 * HOUR_IN_SECONDS );
            return $avatar_url;
        }
    }

    return $fallback_avatar;
}

// =========================================================================
// 2. SHORTCODE RENDERING
// =========================================================================

function gts_fediverse_style_feed() {
    $feed_url            = get_theme_mod( 'ztfr_fediverse_feed_url', 'https://social.ztfr.eu/@dome/feed.rss' );
    $custom_display_name = get_theme_mod( 'ztfr_fediverse_display_name', '' );
    $max_posts           = (int) get_theme_mod( 'ztfr_fediverse_max_posts', 3 );
    $word_limit          = (int) get_theme_mod( 'ztfr_fediverse_word_limit', 30 );
    $cache_time          = (int) get_theme_mod( 'ztfr_fediverse_cache_time', 1800 );

    if ( empty( $feed_url ) ) {
        return '';
    }

    $profile_url = str_replace( '/feed.rss', '', $feed_url );

    $handle = '@user@domain.com';

    if ( preg_match( '/https?:\/\/([^\/]+)\/@([^\/]+)/i', $feed_url, $matches ) ) {
        $handle = '@' . $matches[2] . '@' . $matches[1];
    }

    if ( ! function_exists( 'fetch_feed' ) ) {
        require_once ABSPATH . WPINC . '/feed.php';
    }

    $set_cache_lifetime = function() use ( $cache_time ) {
        return max( 300, $cache_time );
    };

    add_filter( 'wp_feed_cache_transient_lifetime', $set_cache_lifetime );

    $rss = fetch_feed( $feed_url );

    remove_filter( 'wp_feed_cache_transient_lifetime', $set_cache_lifetime );

    if ( is_wp_error( $rss ) || empty( $rss ) ) {
        return '';
    }

    $maxitems     = $rss->get_item_quantity( $max_posts );
    $rss_items    = $rss->get_items( 0, $maxitems );
    $avatar_url   = gts_fediverse_get_avatar( $feed_url, $profile_url );
    $display_name = ! empty( $custom_display_name ) ? $custom_display_name : '';

    if ( $maxitems > 0 ) {
        $first_item = $rss_items[0];

        if ( empty( $display_name ) ) {
            if ( $author = $first_item->get_author() ) {
                $display_name = $author->get_name();
            }

            if ( ! empty( $display_name ) ) {
                $display_name = trim( preg_replace( '/Posts from/i', '', $display_name ) );

                if ( strpos( $display_name, '@' ) === 0 ) {
                    $parts        = explode( '@', $display_name );
                    $display_name = ! empty( $parts[1] ) ? $parts[1] : $display_name;
                }
            }

            if ( empty( $display_name ) && isset( $matches[2] ) ) {
                $display_name = $matches[2];
            }
        }
    }

    ob_start();
    ?>
    <div class="fediverse-rss-widget">
        <?php if ( $maxitems == 0 ) : ?>
            <p class="fediverse-rss-no-posts">No Posts found!.</p>
        <?php else : ?>
            <?php
            $current_item = 0;

            foreach ( $rss_items as $item ) :
                $current_item++;

                $raw_content = $item->get_content();

                $clean_content = wp_kses( $raw_content, array(
                    'p'  => array(),
                    'br' => array(),
                    'a'  => array(
                        'href'   => array(),
                        'target' => array(),
                        'rel'    => array(),
                    ),
                ) );

                $clean_content = str_replace(
                    '<a ',
                    '<a style="color: #bd93f9; text-decoration: underline;" ',
                    $clean_content
                );

                $hashtag_pattern = '/(?:\s*<p>\s*|(?:\s|<br\s*\/?>)*)(?:(?:<a[^>]*>)?#\w+(?:<\/a>)?(?:\s|<br\s*\/?>|&nbsp;)*)+(?:\s*<\/p>\s*)?$/u';
                $clean_content  = preg_replace( $hashtag_pattern, '', rtrim( $clean_content ) );

                $text_only = wp_strip_all_tags( $clean_content );
                $words     = preg_split( '/\s+/', trim( $text_only ) );

                $permalink = esc_url( $item->get_permalink() );
                $date      = $item->get_date( 'j. M.' );

                if ( count( $words ) > $word_limit ) {
                    $content   = wp_html_excerpt( $clean_content, $word_limit * 7, '...' );
                    $link_text = '⇢ Read More';
                } else {
                    $content   = $clean_content;
                    $link_text = '⇢ Read More';
                }

                $classes = 'fediverse-rss-item' . ( $current_item === $maxitems ? ' fediverse-rss-item-last' : '' );
                ?>
                <div class="<?php echo esc_attr( $classes ); ?>">

                    <div class="fediverse-rss-header-row">

                        <div class="fediverse-rss-avatar">
                            <a href="<?php echo esc_url( $profile_url ); ?>" target="_blank" rel="noopener">
                                <img src="<?php echo gts_fediverse_escape_img_src( $avatar_url ); ?>" alt="<?php echo esc_attr( $display_name ); ?>">
                            </a>
                        </div>

                        <div class="fediverse-rss-header-details">
                            <div class="fediverse-rss-name-line">
                                <a href="<?php echo esc_url( $profile_url ); ?>" target="_blank" rel="noopener">
                                    <strong><?php echo esc_html( $display_name ); ?></strong>
                                </a>
                            </div>

                            <div class="fediverse-rss-handle-line">
                                <a href="<?php echo esc_url( $profile_url ); ?>" target="_blank" rel="noopener">
                                    <span><?php echo esc_html( $handle ); ?></span>
                                </a>
                            </div>

                            <div class="fediverse-rss-date-line">
                                <a href="<?php echo $permalink; ?>" target="_blank" rel="noopener">
                                    <?php echo esc_html( $date ); ?>
                                </a>
                            </div>
                        </div>

                    </div>

                    <div class="fediverse-rss-text">
                        <?php echo $content; ?>

                        <div class="fediverse-rss-link-container">
                            <a href="<?php echo $permalink; ?>" target="_blank" rel="noopener">
                                <?php echo esc_html( $link_text ); ?>
                            </a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php

    return ob_get_clean();
}

add_shortcode( 'fediverse_feed', 'gts_fediverse_style_feed' );

// =========================================================================
// 3. WIDGET KLASSE
// =========================================================================

class Zeitfresser_Fediverse_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'ztfr_fediverse_widget',
            __( 'Zeitfresser Fediverse Social Feed Widget', 'zeitfresser' ),
            array(
                'description' => __( 'Shows your Fediverse Posts as RSS Feed.', 'zeitfresser' ),
            )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title']
                . apply_filters( 'widget_title', $instance['title'] )
                . $args['after_title'];
        }

        echo gts_fediverse_style_feed();

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $title = ! empty( $instance['title'] )
            ? $instance['title']
            : __( 'Social Feed', 'zeitfresser' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php esc_html_e( 'Titel:', 'zeitfresser' ); ?>
            </label>
            <input
                class="widefat"
                id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
                name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
                type="text"
                value="<?php echo esc_attr( $title ); ?>"
            >
        </p>
        <p>
            <em><?php esc_html_e( 'Configure global via Customizer.', 'zeitfresser' ); ?></em>
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance          = array();
        $instance['title'] = ! empty( $new_instance['title'] )
            ? sanitize_text_field( $new_instance['title'] )
            : '';

        return $instance;
    }
}

function register_zeitfresser_fediverse_widget() {
    register_widget( 'Zeitfresser_Fediverse_Widget' );
}

add_action( 'widgets_init', 'register_zeitfresser_fediverse_widget' );

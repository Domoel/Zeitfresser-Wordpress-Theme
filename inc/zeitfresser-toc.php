<?php
/**
 * Floating table of contents helpers.
 *
 * @package zeitfresser
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Return whether article TOC output is enabled.
 *
 * @return bool
 */
function zeitfresser_show_article_toc() {
	return (bool) get_theme_mod( 'show_article_toc', true );
}

/**
 * Return the minimum heading count required before showing the TOC.
 *
 * @return int
 */
function zeitfresser_get_article_toc_min_headlines() {
	$threshold = absint( get_theme_mod( 'article_toc_min_headlines', 3 ) );
	return max( 1, $threshold );
}

/**
 * Build a processed single post content payload with TOC metadata.
 *
 * @param int $post_id Post ID.
 * @return array{content:string,items:array<int,array<string,mixed>>}
 */
function zeitfresser_build_toc_payload( $post_id ) {

	static $cache = array();
	$post_id = (int) $post_id;

	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	$payload = array(
		'content' => apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ),
		'items'   => array(),
	);

	// Early exit conditions
	if ( ! $post_id || ! is_singular( 'post' ) || ! zeitfresser_show_article_toc() ) {
		return $cache[ $post_id ] = $payload;
	}

	$content = trim( (string) $payload['content'] );

	if ( '' === $content || ! class_exists( 'DOMDocument' ) ) {
		return $cache[ $post_id ] = $payload;
	}

	libxml_use_internal_errors( true );

	$dom = new DOMDocument();
	$loaded = $dom->loadHTML(
		'<?xml encoding="utf-8" ?><div id="zeitfresser-toc-root">' . $content . '</div>',
		LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
	);

	if ( ! $loaded ) {
		libxml_clear_errors();
		return $cache[ $post_id ] = $payload;
	}

	$container = $dom->getElementById( 'zeitfresser-toc-root' );

	if ( ! $container ) {
		libxml_clear_errors();
		return $cache[ $post_id ] = $payload;
	}

	$index     = 1;
	$toc_items = array();
	$xpath     = new DOMXPath( $dom );
	$headings  = $xpath->query( './/h2 | .//h3 | .//h4', $container );

	if ( $headings instanceof DOMNodeList ) {
		foreach ( $headings as $heading ) {

			$text = trim( wp_strip_all_tags( $heading->textContent ) );
			if ( '' === $text ) {
				continue;
			}

			$tag_name = strtolower( $heading->nodeName );
			$id       = $heading->getAttribute( 'id' );

			if ( '' === $id ) {
				$base_id = sanitize_title( $text );
				$id      = $base_id ? $base_id : 'section-' . $index;

				while ( $dom->getElementById( $id ) ) {
					$id = $base_id . '-' . $index;
					$index++;
				}

				$heading->setAttribute( 'id', $id );
			}

			$toc_items[] = array(
				'id'    => $id,
				'text'  => $text,
				'level' => (int) substr( $tag_name, 1 ),
			);

			$index++;
		}
	}

	libxml_clear_errors();

	// Respect minimum threshold
	if ( count( $toc_items ) < zeitfresser_get_article_toc_min_headlines() ) {
		return $cache[ $post_id ] = array(
			'content' => zeitfresser_extract_toc_inner_html( $container ),
			'items'   => array(),
		);
	}

	return $cache[ $post_id ] = array(
		'content' => zeitfresser_extract_toc_inner_html( $container ),
		'items'   => $toc_items,
	);
}

/**
 * Extract container inner HTML.
 *
 * @param DOMNode $node Source node.
 * @return string
 */
function zeitfresser_extract_toc_inner_html( $node ) {

	$html = '';

	if ( ! $node || ! $node->hasChildNodes() ) {
		return $html;
	}

	foreach ( $node->childNodes as $child_node ) {
		$html .= $node->ownerDocument->saveHTML( $child_node );
	}

	return $html;
}

/**
 * Return whether the current singular post has a TOC.
 *
 * @param int|null $post_id Optional post ID.
 * @return bool
 */
function zeitfresser_has_floating_toc( $post_id = null ) {

	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! $post_id ) {
		return false;
	}

	$payload = zeitfresser_build_toc_payload( $post_id );

	return ! empty( $payload['items'] );
}

/**
 * Render floating TOC markup.
 *
 * @param int|null $post_id Optional post ID.
 * @return void
 */
function zeitfresser_render_floating_toc( $post_id = null ) {

	$post_id = $post_id ? (int) $post_id : get_the_ID();

	if ( ! $post_id ) {
		return;
	}

	$payload = zeitfresser_build_toc_payload( $post_id );

	if ( empty( $payload['items'] ) ) {
		return;
	}
	?>

	<aside class="zeitfresser-floating-toc" id="zeitfresser-floating-toc" aria-label="<?php echo esc_attr__( 'Table of contents', 'zeitfresser' ); ?>">
		<div class="zeitfresser-floating-toc__header">
			<span class="zeitfresser-floating-toc__title"><?php echo esc_html__( 'Content', 'zeitfresser' ); ?></span>
		</div>

		<div class="zeitfresser-floating-toc__progress" aria-hidden="true">
			<span class="zeitfresser-floating-toc__progress-bar" id="zeitfresser-floating-toc-progress"></span>
		</div>

		<nav class="zeitfresser-floating-toc__nav">
			<ol class="zeitfresser-floating-toc__list">
				<?php foreach ( $payload['items'] as $item ) : ?>
					<li class="zeitfresser-floating-toc__item level-<?php echo (int) $item['level']; ?>">
						<a href="#<?php echo esc_attr( $item['id'] ); ?>" data-target="<?php echo esc_attr( $item['id'] ); ?>">
							<?php echo esc_html( $item['text'] ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ol>
		</nav>
	</aside>

	<?php
}

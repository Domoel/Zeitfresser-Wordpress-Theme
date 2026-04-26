<?php
/**
 * Template part for displaying results in search pages
 *
 * @package zeitfresser
 */
?>

<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="news-snippet">

        <?php if ( zeitfresser_show_post_card_featured_image() && has_post_thumbnail() ) : ?>
            <a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark" class="featured-image">
                <?php the_post_thumbnail( zeitfresser_get_post_card_thumbnail_size() ); ?>
            </a>
        <?php endif; ?>

        <div class="summary">

            <h3 class="news-title">
                <a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark">
                    <?php the_title(); ?>
                </a>
            </h3>

            <div class="excerpt">
                <?php echo esc_html(
                    wp_trim_words(
                        get_the_excerpt(),
                        zeitfresser_get_post_card_excerpt_length()
                    )
                ); ?>
            </div>

        </div>
    </div>
</div>

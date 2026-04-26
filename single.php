<?php
/**
 * The template for displaying all single posts
 *
 * @package zeitfresser
 */

get_header();

// CLEAN: no default function anymore
$show_hide_related_posts = get_theme_mod(
    'post_detail_hide_show_related_articles',
    true
);
?>

<div id="primary" class="inside-page content-area">
    <div class="container">
        <div class="main-wrapper">

            <section class="page-section full-width-view">
                <div class="detail-content">

                    <?php while ( have_posts() ) : the_post(); ?>
                        <?php get_template_part( 'template-parts/content', 'single' ); ?>
                    <?php endwhile; ?>

                    <?php the_post_navigation(); ?>
                    <?php comments_template(); ?>

                </div>

                <?php if ( $show_hide_related_posts ) : ?>
                    <?php get_template_part( 'template-parts/related', 'articles' ); ?>
                <?php endif; ?>

            </section>

            <div class="sidebar">
                <?php get_sidebar(); ?>
            </div>

        </div>
    </div>
</div>

<?php get_footer(); ?>

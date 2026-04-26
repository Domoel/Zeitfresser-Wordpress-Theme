<?php
$page_template = get_page_template_slug( get_queried_object_id() );
$post_count = 3;
?>

<div class="related-posts">
    <?php
    $args = array(
        'posts_per_page'         => $post_count,
        'post_type'              => 'post',
        'category__in'           => wp_get_post_categories( $post->ID ),
        'post__not_in'           => array( $post->ID ),
        'ignore_sticky_posts'    => true,
        'no_found_rows'          => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    );

    $query = new WP_Query( $args ); 

    if ( $query->have_posts() ) :
    ?>
        <h2 class="main-title">Related Posts</h2>

        <div class="post-holder">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <div class="news-snippet">

                    <?php if ( zeitfresser_show_post_card_featured_image() && has_post_thumbnail() ) : ?>
                        <a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark" class="featured-image">
                            <?php the_post_thumbnail( zeitfresser_get_post_card_thumbnail_size() ); ?>
                        </a>
                    <?php endif; ?>

                    <div class="summary">

                        <h5 class="news-title">
                            <a href="<?php echo esc_url( get_permalink() ); ?>" rel="bookmark">
                                <?php the_title(); ?>
                            </a>
                        </h5>

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
            <?php endwhile; ?>
            <?php wp_reset_postdata(); ?>
        </div>

    <?php endif; ?>
</div>

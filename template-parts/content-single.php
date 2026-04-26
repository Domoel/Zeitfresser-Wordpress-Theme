<?php
/**
 * Template part for displaying single posts.
 *
 * @package zeitfresser
 */

$toc_payload = zeitfresser_build_toc_payload( get_the_ID() );
$has_floating_toc = ! empty( $toc_payload['items'] );
?>

<div class="zeitfresser-article-heading">
    <h1 class="page-title"><?php the_title(); ?></h1>
</div>

<?php if ( $has_floating_toc ) : ?>
    <?php zeitfresser_render_floating_toc( get_the_ID() ); ?>
<?php endif; ?>

<div class="single-post">

    <div class="post-content">

        <!-- Meta Info -->
        <div class="ihead info">
            <ul class="list-inline">

                <!-- Date (TRUE) -->
                <?php
                $archive_year  = get_the_time('Y');
                $archive_month = get_the_time('m');
                $archive_day   = get_the_time('d');
                ?>
                <li class="post-date">
                    <i class="icon-calendar"></i>
                    <a href="<?php echo esc_url( get_day_link( $archive_year, $archive_month, $archive_day ) ); ?>">
                        <?php echo esc_html( get_the_date() ); ?>
                    </a>
                </li>

            </ul>

            <!-- Comments (TRUE) -->
            <span class="comments">
                <svg width="20px" height="20px" viewBox="0 0 24 24">
                    <g>
                        <path d="M17,3.25H7A4.756,4.756,0,0,0,2.25,8V21a.75.75,0,0,0,1.28.53l2.414-2.414a1.246,1.246,0,0,1,.885-.366H17A4.756,4.756,0,0,0,21.75,14V8A4.756,4.756,0,0,0,17,3.25Z"/>
                    </g>
                </svg>
                <?php comments_popup_link( __( '0', 'zeitfresser' ), __( '1', 'zeitfresser' ), __( '%', 'zeitfresser' ) ); ?>
            </span>
        </div>

        <!-- Featured Image (FALSE → entfernt) -->

        <!-- Article Content -->
        <article>
            <div class="inner-article-content">
                <?php echo $toc_payload['content']; // phpcs:ignore ?>
            </div>

            <?php
            wp_link_pages( array(
                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'zeitfresser' ),
                'after'  => '</div>',
            ) );
            ?>
        </article>

    </div>

    <!-- Author Block (FALSE → entfernt) -->
    <!-- Categories (FALSE → entfernt) -->
    <!-- Tags (FALSE → entfernt) -->
    <!-- Social Share (FALSE → entfernt) -->

</div>

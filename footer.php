<?php
/**
 * The template for displaying the footer.
 *
 * @package zeitfresser
 */

$copyright = get_theme_mod( 'footer_copyright_text', zeitfresser_get_default_footer_copyright() );
?>

<footer id="colophon" class="site-footer">
    <div class="container">
        <?php get_template_part( 'template-parts/social', 'links' ); ?>

        <div class="site-info">
            <div class="copyright"><?php echo wp_kses_post( $copyright ); ?></div>
        </div>
    </div>
</footer>

<a class="scroll-to-top" href="javascript:void(0)">
<svg id="Layer_1" version="1.1" viewBox="0 0 64 64" xml:space="preserve" xmlns="http://www.w3.org/2000/svg">
    <g><g id="Icon-Chevron-Left" transform="translate(237.000000, 335.000000)"><polyline class="st0" id="Fill-35" points="-191.3,-296.9 -193.3,-294.9 -205,-306.6 -216.7,-294.9 -218.7,-296.9 -205,-310.6 -191.3,-296.9"/></g></g>
</svg>
</a>

<?php wp_footer(); ?>
</body>
</html>

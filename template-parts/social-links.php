<?php
/**
 * Social links template part.
 *
 * @package zeitfresser
 */

$social_links          = zeitfresser_get_social_links();
$social_links_settings = zeitfresser_get_social_links_settings();

if ( ! array_filter( $social_links_settings ) ) {
    return;
}
?>

<div class="social-links">
    <ul class="list-group list-group-horizontal list-inline">
        <?php foreach ( $social_links as $social_key => $social_label ) : ?>
            <?php if ( empty( $social_links_settings[ $social_key ] ) ) { continue; } ?>
            <li class="social-share-list list-group-item <?php echo esc_attr( $social_key ); ?>-svg">
                <a target="_blank" rel="noopener noreferrer me" href="<?php echo esc_url( $social_links_settings[ $social_key ] ); ?>" aria-label="<?php echo esc_attr( $social_label ); ?>">
                    <?php echo zeitfresser_social_icon_svg( $social_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

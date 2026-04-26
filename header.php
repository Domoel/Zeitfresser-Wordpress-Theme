<?php
/**
 * The header for our theme
 *
 * @package zeitfresser
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary">
    <?php esc_html_e( 'Skip to content', 'zeitfresser' ); ?>
</a>

<header id="masthead" class="site-header">

    <div class="header-wrapper">
        <div class="container">
            <div class="site-header-wrapper">

                <div class="site-branding">

                    <?php the_custom_logo(); ?>

                    <div class="site-identity">

                        <?php if ( get_theme_mod( 'show_hide_site_title', true ) ) : ?>
                            <div class="site-title">
                                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="logo">
                                    <?php bloginfo( 'name' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ( get_theme_mod( 'show_hide_site_tagline', true ) ) : ?>
                            <div class="site-description">
                                <?php bloginfo( 'description' ); ?>
                            </div>
                        <?php endif; ?>

                    </div>

                </div><!-- .site-branding -->

                <div class="nav-social-links">

                    <nav id="site-navigation" class="main-navigation">
                        <button id="nav-icon3" class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>

                        <?php
                        wp_nav_menu(
                            array(
                                'theme_location' => 'menu-1',
                                'menu_id'        => 'primary-menu',
                            )
                        );
                        ?>
                    </nav>

                    <?php get_template_part( 'template-parts/social', 'links' ); ?>

                </div>

            </div>
        </div>
    </div>

</header><!-- #masthead -->

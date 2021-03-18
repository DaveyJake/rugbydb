<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Rugby_Database
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<?php
    rdb_head_open();
    echo '<meta charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '">';
    echo '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
    echo '<link rel="profile" href="https://gmpg.org/xfn/11">';
    wp_head();
    rdb_head_close();
?>
</head>
<body <?php body_class(); ?>>
<?php // phpcs:disable Generic.WhiteSpace.ScopeIndent
    rdb_body_open();

    echo '<div id="page" class="site">';

        echo '<a class="skip-link screen-reader-text" href="#primary">' . esc_html__( 'Skip to content', 'rugby-database' ) . '</a>';

        echo '<header id="masthead" class="site-header Fixed" itemscope itemtype="http://schema.org/WPHeader">';

            echo '<div class="wpcm-row">';

                echo '<div class="main-navigation">';
                    echo '<div class="site-branding" itemscope itemtype="http://schema.org/Brand">';
                        echo '<a href="' . esc_url( home_url( '/' ) ) . '" rel="bookmark">' . wp_kses_post( rdb_site_logo() ) . '<sub>BETA</sub></a>';
                    echo '</div><!-- .site-branding -->';

                    rdb_nav_menu(
                        array(
                            'container_id'   => 'primary-menu',
                            'menu'           => 'main-menu',
                            'theme_location' => 'main-menu',
                        )
                    );

                echo '</div><!-- .main-navigation -->';

            echo '</div>';

        echo '</header><!-- #masthead -->';

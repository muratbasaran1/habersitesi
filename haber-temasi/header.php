<?php
/**
 * Genel üst bilgi dosyası.
 *
 * @package Haber_Sitesi
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
$show_breaking_news = get_theme_mod( 'haber_show_breaking_news', true );

if ( $show_breaking_news ) {
    $breaking_category = absint( get_theme_mod( 'haber_breaking_news_category', 0 ) );
    $breaking_args     = [
        'posts_per_page'      => 5,
        'ignore_sticky_posts' => 1,
    ];

    if ( $breaking_category > 0 ) {
        $breaking_args['cat'] = $breaking_category;
    }

    $breaking_query = new WP_Query( $breaking_args );

    if ( $breaking_query->have_posts() ) :
        ?>
        <div class="breaking-news" aria-label="<?php esc_attr_e( 'Son dakika haberleri', 'haber-sitesi' ); ?>">
            <div class="container breaking-news__inner">
                <span class="breaking-news__label"><?php esc_html_e( 'Son Dakika', 'haber-sitesi' ); ?></span>
                <div class="breaking-news__items" role="list">
                    <?php
                    $breaking_index = 0;
                    while ( $breaking_query->have_posts() ) :
                        $breaking_query->the_post();
                        $is_active = 0 === $breaking_index ? ' is-active' : '';
                        $hidden    = 0 === $breaking_index ? 'false' : 'true';
                        $tabindex  = 0 === $breaking_index ? '0' : '-1';
                        ?>
                        <a role="listitem" class="breaking-news__item<?php echo esc_attr( $is_active ); ?>" aria-hidden="<?php echo esc_attr( $hidden ); ?>" tabindex="<?php echo esc_attr( $tabindex ); ?>" href="<?php echo esc_url( get_permalink() ); ?>">
                            <?php the_title(); ?>
                        </a>
                        <?php
                        $breaking_index++;
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </div>
            </div>
        </div>
        <?php
    endif;
}
?>
<header class="site-header">
    <div class="container">
        <div class="navbar">
            <div class="site-branding">
                <?php
                if ( has_custom_logo() ) {
                    the_custom_logo();
                } else {
                    echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="site-title">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
                }
                ?>
                <p class="site-description"><?php bloginfo( 'description' ); ?></p>
            </div>
            <nav class="primary-navigation" aria-label="<?php esc_attr_e( 'Ana Menü', 'haber-sitesi' ); ?>">
                <?php
                wp_nav_menu( [
                    'theme_location' => 'primary',
                    'menu_class'     => 'primary-menu',
                    'container'      => 'ul',
                    'fallback_cb'    => '__return_false',
                ] );
                ?>
            </nav>
        </div>
    </div>
</header>
<main id="primary" class="site-main">

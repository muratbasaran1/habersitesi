<?php
/**
 * Genel üst bilgi dosyası.
 *
 * @package Haber_Sitesi
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="mobile-header">
    <?php
    $weather_location   = get_theme_mod( 'haber_weather_location', __( 'İstanbul', 'haber-sitesi' ) );
    $weather_temperature = get_theme_mod( 'haber_weather_temperature', '15°C' );
    $weather_condition  = get_theme_mod( 'haber_weather_condition', __( 'Güneşli', 'haber-sitesi' ) );
    ?>
    <div class="mobile-top-meta">
        <div class="mobile-shell mobile-top-meta__inner">
            <span class="mobile-top-meta__date" aria-label="<?php esc_attr_e( 'Bugünün tarihi', 'haber-sitesi' ); ?>">📅 <?php echo esc_html( wp_date( get_option( 'date_format' ) ) ); ?></span>
            <div class="mobile-top-meta__weather" role="status" aria-live="polite">
                <?php if ( $weather_location || $weather_temperature ) : ?>
                    <span class="mobile-top-meta__weather-city">🌡️ <?php echo esc_html( trim( $weather_location . ' ' . $weather_temperature ) ); ?></span>
                <?php endif; ?>
                <?php if ( $weather_condition ) : ?>
                    <span class="mobile-top-meta__weather-condition"><?php echo esc_html( $weather_condition ); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="mobile-shell mobile-header__bar">
        <div class="mobile-header__branding">
            <?php
            if ( has_custom_logo() ) {
                the_custom_logo();
            } else {
                echo '<a href="' . esc_url( home_url( '/' ) ) . '" class="mobile-header__title">' . esc_html( get_bloginfo( 'name' ) ) . '</a>';
            }
            ?>
        </div>
        <button class="mobile-header__search-toggle" type="button" aria-expanded="false" aria-controls="mobile-search">
            <span class="screen-reader-text"><?php esc_html_e( 'Arama alanını aç', 'haber-sitesi' ); ?></span>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                <path d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 105.25 5.25a7.5 7.5 0 0011.4 11.4z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>
    </div>
    <form id="mobile-search" class="mobile-search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <label class="screen-reader-text" for="mobile-search-field"><?php esc_html_e( 'Sitede ara', 'haber-sitesi' ); ?></label>
        <input id="mobile-search-field" type="search" name="s" placeholder="<?php echo esc_attr__( 'Haberlerde ara...', 'haber-sitesi' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" />
        <button type="submit"><?php esc_html_e( 'Ara', 'haber-sitesi' ); ?></button>
    </form>
    <nav id="mobile-categories" class="mobile-category-nav" aria-label="<?php esc_attr_e( 'Hızlı kategoriler', 'haber-sitesi' ); ?>">
        <div class="mobile-shell mobile-category-nav__scroll">
            <?php
            wp_nav_menu(
                [
                    'theme_location' => 'primary',
                    'menu_class'     => 'mobile-category-nav__list',
                    'container'      => false,
                    'fallback_cb'    => 'haber_sitesi_primary_menu_fallback',
                    'depth'          => 1,
                ]
            );
            ?>
        </div>
    </nav>
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
            <div id="mobile-breaking-news" class="mobile-breaking-news" aria-label="<?php esc_attr_e( 'Son dakika haberleri', 'haber-sitesi' ); ?>">
                <div class="mobile-shell mobile-breaking-news__inner">
                    <span class="mobile-breaking-news__label"><?php esc_html_e( 'Son Dakika', 'haber-sitesi' ); ?></span>
                    <div class="mobile-breaking-news__ticker" role="list">
                        <?php
                        $breaking_index = 0;
                        while ( $breaking_query->have_posts() ) :
                            $breaking_query->the_post();
                            $is_active = 0 === $breaking_index ? ' is-active' : '';
                            $hidden    = 0 === $breaking_index ? 'false' : 'true';
                            $tabindex  = 0 === $breaking_index ? '0' : '-1';
                            ?>
                            <a role="listitem" class="mobile-breaking-news__item<?php echo esc_attr( $is_active ); ?>" aria-hidden="<?php echo esc_attr( $hidden ); ?>" tabindex="<?php echo esc_attr( $tabindex ); ?>" href="<?php echo esc_url( get_permalink() ); ?>">
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
</header>
<main id="primary" class="site-main">

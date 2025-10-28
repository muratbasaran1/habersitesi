<?php
/**
 * Haber Sitesi - WordPress Customizer ayarları.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function haber_sitesi_sanitize_checkbox( $checked ) {
    return ( isset( $checked ) && ( true === $checked || '1' === $checked ) );
}

function haber_sitesi_sanitize_text( $value ) {
    return sanitize_text_field( $value );
}

function haber_sitesi_sanitize_category( $input ) {
    return absint( $input );
}

function haber_sitesi_get_category_choices() {
    $choices = [
        0 => __( 'En son yazıları kullan', 'haber-sitesi' ),
    ];

    $categories = get_categories( [
        'hide_empty' => false,
    ] );

    foreach ( $categories as $category ) {
        $choices[ $category->term_id ] = $category->name;
    }

    return $choices;
}

function haber_sitesi_customize_register( $wp_customize ) {
    $wp_customize->add_section( 'haber_sitesi_homepage', [
        'title'    => __( 'Anasayfa', 'haber-sitesi' ),
        'priority' => 25,
    ] );

    $wp_customize->add_section( 'haber_sitesi_header_meta', [
        'title'       => __( 'Üst Bilgi', 'haber-sitesi' ),
        'priority'    => 20,
        'description' => __( 'Üst bilgi satırındaki hava durumu bilgilerini düzenleyin.', 'haber-sitesi' ),
    ] );

    $wp_customize->add_setting( 'haber_weather_location', [
        'default'           => __( 'İstanbul', 'haber-sitesi' ),
        'sanitize_callback' => 'haber_sitesi_sanitize_text',
        'transport'         => 'postMessage',
    ] );

    $wp_customize->add_control( 'haber_weather_location', [
        'label'   => __( 'Şehir', 'haber-sitesi' ),
        'section' => 'haber_sitesi_header_meta',
        'type'    => 'text',
    ] );

    $wp_customize->add_setting( 'haber_weather_temperature', [
        'default'           => '15°C',
        'sanitize_callback' => 'haber_sitesi_sanitize_text',
        'transport'         => 'postMessage',
    ] );

    $wp_customize->add_control( 'haber_weather_temperature', [
        'label'   => __( 'Sıcaklık', 'haber-sitesi' ),
        'section' => 'haber_sitesi_header_meta',
        'type'    => 'text',
    ] );

    $wp_customize->add_setting( 'haber_weather_condition', [
        'default'           => __( 'Güneşli', 'haber-sitesi' ),
        'sanitize_callback' => 'haber_sitesi_sanitize_text',
        'transport'         => 'postMessage',
    ] );

    $wp_customize->add_control( 'haber_weather_condition', [
        'label'   => __( 'Hava Durumu', 'haber-sitesi' ),
        'section' => 'haber_sitesi_header_meta',
        'type'    => 'text',
    ] );

    $wp_customize->add_setting( 'haber_show_breaking_news', [
        'default'           => true,
        'sanitize_callback' => 'haber_sitesi_sanitize_checkbox',
    ] );

    $wp_customize->add_control( 'haber_show_breaking_news', [
        'label'   => __( 'Son dakika bandını göster', 'haber-sitesi' ),
        'section' => 'haber_sitesi_homepage',
        'type'    => 'checkbox',
    ] );

    $wp_customize->add_setting( 'haber_breaking_news_category', [
        'default'           => 0,
        'sanitize_callback' => 'haber_sitesi_sanitize_category',
    ] );

    $wp_customize->add_control( 'haber_breaking_news_category', [
        'label'       => __( 'Son dakika kategorisi', 'haber-sitesi' ),
        'description' => __( 'Belirli bir kategori seçin veya en son yazıları kullanın.', 'haber-sitesi' ),
        'section'     => 'haber_sitesi_homepage',
        'type'        => 'select',
        'choices'     => haber_sitesi_get_category_choices(),
    ] );

    $wp_customize->add_section( 'haber_sitesi_colors', [
        'title'       => __( 'Tema Renkleri', 'haber-sitesi' ),
        'priority'    => 30,
        'description' => __( 'Vurgu ve arkaplan renklerini özelleştirin.', 'haber-sitesi' ),
    ] );

    $wp_customize->add_setting( 'haber_primary_color', [
        'default'           => '#c70000',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'haber_primary_color', [
        'label'   => __( 'Vurgu Rengi', 'haber-sitesi' ),
        'section' => 'haber_sitesi_colors',
    ] ) );

    $wp_customize->add_setting( 'haber_dark_color', [
        'default'           => '#121212',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'haber_dark_color', [
        'label'   => __( 'Koyu Arkaplan', 'haber-sitesi' ),
        'section' => 'haber_sitesi_colors',
    ] ) );
}
add_action( 'customize_register', 'haber_sitesi_customize_register' );

function haber_sitesi_customizer_css() {
    $primary = get_theme_mod( 'haber_primary_color', '#c70000' );
    $dark    = get_theme_mod( 'haber_dark_color', '#121212' );
    ?>
    <style type="text/css">
        :root {
            --haber-primary: <?php echo esc_html( $primary ); ?>;
            --haber-dark: <?php echo esc_html( $dark ); ?>;
        }

        .mobile-breaking-news__label,
        .pagination .current,
        .button,
        .mobile-hero__actions .button,
        .mobile-load-more__button {
            background-color: var(--haber-primary);
        }

        .mobile-hero,
        .mobile-secondary__item,
        .related-post,
        .single-article {
            border-top-color: var(--haber-primary);
        }

        .mobile-most-read__title {
            border-top-color: var(--haber-dark);
        }

        a,
        .mobile-category-nav__link,
        .mobile-secondary__title a,
        .mobile-most-read__link,
        .tags a {
            color: var(--haber-primary);
        }

        .pagination .current,
        .button {
            color: #ffffff;
        }

        .site-footer {
            background-color: var(--haber-dark);
        }
    </style>
    <?php
}
add_action( 'wp_head', 'haber_sitesi_customizer_css' );

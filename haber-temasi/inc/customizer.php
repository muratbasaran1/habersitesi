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

function haber_sitesi_sanitize_url_setting( $value ) {
    return esc_url_raw( $value );
}

function haber_sitesi_sanitize_rich_text( $value ) {
    return wp_kses_post( $value );
}

function haber_sitesi_sanitize_embed( $value ) {
    $allowed = [
        'iframe' => [
            'src'             => [],
            'title'           => [],
            'width'           => [],
            'height'          => [],
            'frameborder'     => [],
            'allow'           => [],
            'allowfullscreen' => [],
            'loading'         => [],
            'referrerpolicy'  => [],
        ],
        'div'    => [
            'class' => [],
            'style' => [],
            'id'    => [],
        ],
        'span'   => [
            'class' => [],
            'style' => [],
        ],
        'p'      => [
            'class' => [],
            'style' => [],
        ],
        'strong' => [],
        'em'     => [],
        'a'      => [
            'href'   => [],
            'target' => [],
            'rel'    => [],
            'class'  => [],
            'style'  => [],
        ],
        'video'  => [
            'src'        => [],
            'controls'   => [],
            'muted'      => [],
            'autoplay'   => [],
            'loop'       => [],
            'playsinline'=> [],
            'poster'     => [],
            'width'      => [],
            'height'     => [],
        ],
        'source' => [
            'src'  => [],
            'type' => [],
        ],
    ];

    return wp_kses( $value, $allowed );
}

function haber_sitesi_sanitize_market_direction( $value ) {
    $value    = sanitize_key( $value );
    $allowed  = [ 'inherit', 'up', 'down', 'flat' ];
    $fallback = 'inherit';

    if ( ! in_array( $value, $allowed, true ) ) {
        return $fallback;
    }

    return $value;
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

    $wp_customize->add_section( 'haber_sitesi_market', [
        'title'       => __( 'Piyasa Panosu', 'haber-sitesi' ),
        'priority'    => 22,
        'description' => __( 'Anasayfadaki piyasa göstergelerini ve güncelleme bilgisini düzenleyin.', 'haber-sitesi' ),
    ] );

    $wp_customize->add_section( 'haber_sitesi_live', [
        'title'       => __( 'Canlı Yayın Merkezi', 'haber-sitesi' ),
        'priority'    => 23,
        'description' => __( 'Anasayfadaki canlı yayın sahnesinin içerik ve görsellerini yönetin.', 'haber-sitesi' ),
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

    $wp_customize->add_setting( 'haber_market_update_label', [
        'default'           => '',
        'sanitize_callback' => 'haber_sitesi_sanitize_text',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_market_update_label', [
        'label'       => __( 'Piyasa güncelleme etiketi', 'haber-sitesi' ),
        'description' => __( 'Örn: "Güncelleme: 12.45"', 'haber-sitesi' ),
        'section'     => 'haber_sitesi_market',
        'type'        => 'text',
    ] );

    $market_items = [
        'dolar'      => __( 'Dolar', 'haber-sitesi' ),
        'euro'       => __( 'Euro', 'haber-sitesi' ),
        'gram-altin' => __( 'Gram Altın', 'haber-sitesi' ),
        'bist-100'   => __( 'BIST 100', 'haber-sitesi' ),
    ];

    foreach ( $market_items as $slug => $label ) {
        $wp_customize->add_setting( "haber_market_{$slug}_value", [
            'default'           => '',
            'sanitize_callback' => 'haber_sitesi_sanitize_text',
            'transport'         => 'refresh',
        ] );

        $wp_customize->add_control( "haber_market_{$slug}_value", [
            'label'   => sprintf( __( '%s değeri', 'haber-sitesi' ), $label ),
            'section' => 'haber_sitesi_market',
            'type'    => 'text',
        ] );

        $wp_customize->add_setting( "haber_market_{$slug}_direction", [
            'default'           => 'inherit',
            'sanitize_callback' => 'haber_sitesi_sanitize_market_direction',
            'transport'         => 'refresh',
        ] );

        $wp_customize->add_control( "haber_market_{$slug}_direction", [
            'label'   => sprintf( __( '%s yönü', 'haber-sitesi' ), $label ),
            'section' => 'haber_sitesi_market',
            'type'    => 'select',
            'choices' => [
                'inherit' => __( 'Varsayılanı kullan', 'haber-sitesi' ),
                'up'      => __( 'Yükseliş', 'haber-sitesi' ),
                'down'    => __( 'Düşüş', 'haber-sitesi' ),
                'flat'    => __( 'Sabit', 'haber-sitesi' ),
            ],
        ] );
    }

    $wp_customize->add_setting( 'haber_live_manual_mode', [
        'default'           => false,
        'sanitize_callback' => 'haber_sitesi_sanitize_checkbox',
    ] );

    $wp_customize->add_control( 'haber_live_manual_mode', [
        'label'       => __( 'Manuel canlı yayın kartını kullan', 'haber-sitesi' ),
        'description' => __( 'Etkinleştirildiğinde canlı yayın kartı aşağıdaki içerik ile doldurulur.', 'haber-sitesi' ),
        'section'     => 'haber_sitesi_live',
        'type'        => 'checkbox',
    ] );

    $wp_customize->add_setting( 'haber_live_title', [
        'default'           => '',
        'sanitize_callback' => 'haber_sitesi_sanitize_text',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_title', [
        'label'   => __( 'Canlı yayın başlığı', 'haber-sitesi' ),
        'section' => 'haber_sitesi_live',
        'type'    => 'text',
    ] );

    $wp_customize->add_setting( 'haber_live_description', [
        'default'           => '',
        'sanitize_callback' => 'haber_sitesi_sanitize_rich_text',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_description', [
        'label'   => __( 'Kısa özet', 'haber-sitesi' ),
        'section' => 'haber_sitesi_live',
        'type'    => 'textarea',
    ] );

    $wp_customize->add_setting( 'haber_live_category', [
        'default'           => '',
        'sanitize_callback' => 'haber_sitesi_sanitize_text',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_category', [
        'label'       => __( 'Kategori etiketi', 'haber-sitesi' ),
        'description' => __( 'Örn: Canlı Yayın', 'haber-sitesi' ),
        'section'     => 'haber_sitesi_live',
        'type'        => 'text',
    ] );

    $wp_customize->add_setting( 'haber_live_presenter', [
        'default'           => '',
        'sanitize_callback' => 'haber_sitesi_sanitize_text',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_presenter', [
        'label'   => __( 'Sunucu / Muhabir', 'haber-sitesi' ),
        'section' => 'haber_sitesi_live',
        'type'    => 'text',
    ] );

    $wp_customize->add_setting( 'haber_live_time', [
        'default'           => '',
        'sanitize_callback' => 'haber_sitesi_sanitize_text',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_time', [
        'label'       => __( 'Yayın saati', 'haber-sitesi' ),
        'description' => __( 'Örn: 21:30 • Şimdi Canlı', 'haber-sitesi' ),
        'section'     => 'haber_sitesi_live',
        'type'        => 'text',
    ] );

    $wp_customize->add_setting( 'haber_live_cta_label', [
        'default'           => __( 'Yayını Aç', 'haber-sitesi' ),
        'sanitize_callback' => 'haber_sitesi_sanitize_text',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_cta_label', [
        'label'   => __( 'CTA etiketi', 'haber-sitesi' ),
        'section' => 'haber_sitesi_live',
        'type'    => 'text',
    ] );

    $wp_customize->add_setting( 'haber_live_cta_url', [
        'default'           => '',
        'sanitize_callback' => 'haber_sitesi_sanitize_url_setting',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_cta_url', [
        'label'       => __( 'CTA bağlantısı', 'haber-sitesi' ),
        'description' => __( 'Yayın bağlantısı veya özel sayfa adresi.', 'haber-sitesi' ),
        'section'     => 'haber_sitesi_live',
        'type'        => 'url',
    ] );

    $wp_customize->add_setting( 'haber_live_views', [
        'default'           => 0,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_views', [
        'label'       => __( 'İzlenme sayısı', 'haber-sitesi' ),
        'description' => __( 'Manuel modda canlı yayın kartındaki izlenme sayısı.', 'haber-sitesi' ),
        'section'     => 'haber_sitesi_live',
        'type'        => 'number',
        'input_attrs' => [
            'min'  => 0,
            'step' => 1,
        ],
    ] );

    $wp_customize->add_setting( 'haber_live_comments', [
        'default'           => 0,
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_comments', [
        'label'       => __( 'Yorum sayısı', 'haber-sitesi' ),
        'section'     => 'haber_sitesi_live',
        'type'        => 'number',
        'input_attrs' => [
            'min'  => 0,
            'step' => 1,
        ],
    ] );

    $wp_customize->add_setting( 'haber_live_reading_time', [
        'default'           => '',
        'sanitize_callback' => 'haber_sitesi_sanitize_text',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_reading_time', [
        'label'       => __( 'Yayın süresi / okuma', 'haber-sitesi' ),
        'description' => __( 'Örn: 45 dk canlı yayın', 'haber-sitesi' ),
        'section'     => 'haber_sitesi_live',
        'type'        => 'text',
    ] );

    $wp_customize->add_setting( 'haber_live_schedule_title', [
        'default'           => '',
        'sanitize_callback' => 'haber_sitesi_sanitize_text',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_schedule_title', [
        'label'       => __( 'Program başlığı', 'haber-sitesi' ),
        'description' => __( 'Yan sütundaki yayın akışı başlığı. Boş bırakılırsa varsayılan kullanılır.', 'haber-sitesi' ),
        'section'     => 'haber_sitesi_live',
        'type'        => 'text',
    ] );

    $wp_customize->add_setting( 'haber_live_embed', [
        'default'           => '',
        'sanitize_callback' => 'haber_sitesi_sanitize_embed',
        'transport'         => 'refresh',
    ] );

    $wp_customize->add_control( 'haber_live_embed', [
        'label'       => __( 'Yerleşik canlı yayın kodu', 'haber-sitesi' ),
        'description' => __( 'YouTube, Vimeo veya kendi canlı yayın iframe/video kodunuzu ekleyin.', 'haber-sitesi' ),
        'section'     => 'haber_sitesi_live',
        'type'        => 'textarea',
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

<?php
/**
 * Haber Sitesi Teması ana fonksiyon dosyası.
 *
 * @package Haber_Sitesi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Doğrudan erişim engellensin.
}

if ( ! function_exists( 'haber_sitesi_setup' ) ) {
    /**
     * Tema varsayılanlarını ve desteklediği özellikleri tanımlar.
     */
    function haber_sitesi_setup() {
        load_theme_textdomain( 'haber-sitesi', get_template_directory() . '/languages' );

        add_theme_support( 'automatic-feed-links' );
        add_theme_support( 'title-tag' );
        add_theme_support( 'post-thumbnails' );
        add_theme_support( 'custom-logo', [
            'height'      => 64,
            'width'       => 240,
            'flex-height' => true,
            'flex-width'  => true,
        ] );

        add_theme_support( 'html5', [
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ] );

        add_theme_support( 'align-wide' );
        add_theme_support( 'editor-styles' );
        add_editor_style( 'assets/css/main.css' );

        register_nav_menus( [
            'primary'   => __( 'Ana Menü', 'haber-sitesi' ),
            'secondary' => __( 'Üst Bilgi Menüsü', 'haber-sitesi' ),
        ] );
    }
}
add_action( 'after_setup_theme', 'haber_sitesi_setup' );

/**
 * Script ve stil dosyalarını yükler.
 */
function haber_sitesi_enqueue_assets() {
    $version = wp_get_theme()->get( 'Version' );

    wp_enqueue_style( 'haber-sitesi-style', get_stylesheet_uri(), [], $version );
    wp_enqueue_style( 'haber-sitesi-main', get_template_directory_uri() . '/assets/css/main.css', [], $version );

    wp_enqueue_script( 'haber-sitesi-navigation', get_template_directory_uri() . '/assets/js/main.js', [ 'jquery' ], $version, true );
}
add_action( 'wp_enqueue_scripts', 'haber_sitesi_enqueue_assets' );

/**
 * Widget alanlarını kaydeder.
 */
function haber_sitesi_widgets_init() {
    register_sidebar( [
        'name'          => __( 'Ana Sayfa Yan Alan', 'haber-sitesi' ),
        'id'            => 'sidebar-home',
        'description'   => __( 'Anasayfa için yan alan.', 'haber-sitesi' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );

    register_sidebar( [
        'name'          => __( 'Alt Bilgi Alanı', 'haber-sitesi' ),
        'id'            => 'footer-widgets',
        'description'   => __( 'Alt bilgi bileşen alanı.', 'haber-sitesi' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ] );
}
add_action( 'widgets_init', 'haber_sitesi_widgets_init' );

require get_template_directory() . '/inc/customizer.php';

if ( is_admin() ) {
    require get_template_directory() . '/inc/admin-panel.php';
}

/**
 * Yazı için tahmini okuma süresini döndürür.
 *
 * @param int $post_id Yazı kimliği.
 *
 * @return string
 */
function haber_sitesi_get_reading_time( $post_id = 0 ) {
    $post_id = $post_id ? $post_id : get_the_ID();

    if ( ! $post_id ) {
        return '';
    }

    $content    = get_post_field( 'post_content', $post_id );
    $word_count = str_word_count( wp_strip_all_tags( $content ) );

    if ( 0 === $word_count ) {
        return __( '1 dakikalık okuma', 'haber-sitesi' );
    }

    $minutes = max( 1, (int) ceil( $word_count / 200 ) );

    return sprintf(
        _n( '%d dakikalık okuma', '%d dakikalık okuma', $minutes, 'haber-sitesi' ),
        $minutes
    );
}

/**
 * İlgili yazılar için WP_Query örneği döndürür.
 *
 * @param int $post_id Yazı kimliği.
 * @param int $limit   Yazı sayısı.
 *
 * @return WP_Query
 */
function haber_sitesi_get_related_posts( $post_id = 0, $limit = 3 ) {
    $post_id = $post_id ? $post_id : get_the_ID();

    if ( ! $post_id ) {
        return new WP_Query();
    }

    $categories = wp_get_post_categories( $post_id );

    $args = [
        'post__not_in'        => [ $post_id ],
        'posts_per_page'      => absint( $limit ),
        'ignore_sticky_posts' => 1,
        'no_found_rows'       => true,
    ];

    if ( ! empty( $categories ) ) {
        $args['category__in'] = $categories;
    }

    return new WP_Query( $args );
}

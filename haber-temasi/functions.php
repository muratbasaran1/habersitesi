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
            'mobile'    => __( 'Mobil Alt Menü', 'haber-sitesi' ),
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

    wp_localize_script(
        'haber-sitesi-navigation',
        'haberSiteiInteract',
        [
            'shareCopied'       => __( 'Bağlantı panoya kopyalandı.', 'haber-sitesi' ),
            'shareCopyFallback' => __( 'Bağlantı kopyalanamadı. Lütfen paylaşım bağlantısını manuel olarak açın.', 'haber-sitesi' ),
            'saveLabel'         => __( 'Kaydet', 'haber-sitesi' ),
            'savedLabel'        => __( 'Kaydedildi', 'haber-sitesi' ),
        ]
    );
}
add_action( 'wp_enqueue_scripts', 'haber_sitesi_enqueue_assets' );

/**
 * Yönetim portalı için özel varlıkları yükler.
 */
function haber_sitesi_enqueue_portal_assets() {
    if ( ! is_page_template( 'page-templates/portal-haber-yonetimi.php' ) ) {
        return;
    }

    if ( ! is_user_logged_in() || ! current_user_can( 'edit_others_posts' ) ) {
        return;
    }

    $version = wp_get_theme()->get( 'Version' );

    wp_enqueue_style(
        'haber-sitesi-portal',
        get_template_directory_uri() . '/assets/css/portal.css',
        [],
        $version
    );

    wp_enqueue_script(
        'haber-sitesi-portal',
        get_template_directory_uri() . '/assets/js/portal.js',
        [],
        $version,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'haber_sitesi_enqueue_portal_assets' );

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

if ( ! function_exists( 'haber_sitesi_collect_post_data' ) ) {
    /**
     * Haber kartlarında kullanılmak üzere standartlaştırılmış içerik verisi döndürür.
     *
     * @param int $post_id        Yazı kimliği.
     * @param int $excerpt_words  Özet için kelime sınırı.
     *
     * @return array<string, mixed>
     */
    function haber_sitesi_collect_post_data( $post_id, $excerpt_words = 24 ) {
        $post_id = absint( $post_id );

        if ( ! $post_id ) {
            return [];
        }

        $categories    = get_the_category( $post_id );
        $category_name = ! empty( $categories ) ? $categories[0]->name : __( 'Güncel', 'haber-sitesi' );
        $time_diff     = human_time_diff( get_the_time( 'U', $post_id ), current_time( 'timestamp' ) );
        $author_id     = (int) get_post_field( 'post_author', $post_id );

        return [
            'id'           => $post_id,
            'title'        => get_the_title( $post_id ),
            'permalink'    => get_permalink( $post_id ),
            'excerpt'      => wp_trim_words( get_the_excerpt( $post_id ), $excerpt_words ),
            'image'        => get_the_post_thumbnail_url( $post_id, 'full' ),
            'thumb'        => get_the_post_thumbnail_url( $post_id, 'medium_large' ),
            'category'     => $category_name,
            'time'         => sprintf( __( '%s önce', 'haber-sitesi' ), $time_diff ),
            'views'        => haber_sitesi_get_post_views( $post_id ),
            'comments'     => get_comments_number( $post_id ),
            'reading_time' => haber_sitesi_get_reading_time( $post_id ),
            'author'       => $author_id ? get_the_author_meta( 'display_name', $author_id ) : '',
            'date'         => get_the_date( '', $post_id ),
        ];
    }
}

if ( ! function_exists( 'haber_sitesi_get_trending_posts' ) ) {
    /**
     * Görüntülenme sayılarına göre trend olan haberleri döndürür.
     *
     * @param int   $limit        Listelenecek içerik sayısı.
     * @param array $exclude_ids  Hariç tutulacak yazı kimlikleri.
     *
     * @return array<int, array<string, mixed>>
     */
    function haber_sitesi_get_trending_posts( $limit = 5, $exclude_ids = [] ) {
        $limit       = max( 1, (int) $limit );
        $exclude_ids = array_filter( array_map( 'absint', (array) $exclude_ids ) );

        $query_args = [
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $limit,
            'ignore_sticky_posts' => 1,
            'meta_key'            => 'haber_view_count',
            'orderby'             => 'meta_value_num',
            'order'               => 'DESC',
            'post__not_in'        => $exclude_ids,
            'no_found_rows'       => true,
        ];

        $query  = new WP_Query( $query_args );
        $items  = [];

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $items[] = haber_sitesi_collect_post_data( get_the_ID(), 18 );
            }
            wp_reset_postdata();
        }

        if ( empty( $items ) ) {
            $fallback_args = [
                'post_type'           => 'post',
                'post_status'         => 'publish',
                'posts_per_page'      => $limit,
                'ignore_sticky_posts' => 1,
                'orderby'             => 'comment_count',
                'order'               => 'DESC',
                'post__not_in'        => $exclude_ids,
                'no_found_rows'       => true,
            ];

            $fallback_query = new WP_Query( $fallback_args );

            if ( $fallback_query->have_posts() ) {
                while ( $fallback_query->have_posts() ) {
                    $fallback_query->the_post();
                    $items[] = haber_sitesi_collect_post_data( get_the_ID(), 18 );
                }
                wp_reset_postdata();
            }
        }

        return $items;
    }
}

if ( ! function_exists( 'haber_sitesi_get_category_overview' ) ) {
    /**
     * Kategori istatistiklerini özetler.
     *
     * @param int $limit Kaç kategori döneceği.
     *
     * @return array<int, array<string, mixed>>
     */
    function haber_sitesi_get_category_overview( $limit = 6 ) {
        $terms = get_terms(
            [
                'taxonomy'   => 'category',
                'hide_empty' => false,
                'orderby'    => 'count',
                'order'      => 'DESC',
                'number'     => max( 1, (int) $limit ),
            ]
        );

        if ( empty( $terms ) || is_wp_error( $terms ) ) {
            return [];
        }

        $items = [];

        foreach ( $terms as $term ) {
            $items[] = [
                'id'    => $term->term_id,
                'name'  => $term->name,
                'count' => (int) $term->count,
                'link'  => get_term_link( $term ),
            ];
        }

        return $items;
    }
}

if ( ! function_exists( 'haber_sitesi_get_monthly_activity' ) ) {
    /**
     * Haber yayın aktivitesini aylık olarak özetler.
     *
     * @param int $months Geriye dönük kaç ayın hesaba katılacağı.
     *
     * @return array<string, mixed>
     */
    function haber_sitesi_get_monthly_activity( $months = 6 ) {
        global $wpdb;

        $months = max( 1, (int) $months );

        $current_gmt = current_time( 'timestamp', true );
        $start_point = strtotime( sprintf( '-%d months', $months - 1 ), $current_gmt );

        if ( false === $start_point ) {
            $start_point = $current_gmt;
        }

        $start_date = gmdate( 'Y-m-01 00:00:00', $start_point );

        $query = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT DATE_FORMAT(post_date_gmt, '%%Y-%%m') AS period, COUNT(ID) AS total FROM {$wpdb->posts} WHERE post_type = %s AND post_status NOT IN ('auto-draft','trash') AND post_date_gmt >= %s GROUP BY period ORDER BY period ASC",
                'post',
                $start_date
            ),
            ARRAY_A
        );

        $timeline = [];

        for ( $offset = $months - 1; $offset >= 0; $offset-- ) {
            $period_key             = gmdate( 'Y-m', strtotime( sprintf( '-%d months', $offset ), $current_gmt ) );
            $timeline[ $period_key ] = 0;
        }

        if ( ! empty( $query ) ) {
            foreach ( $query as $row ) {
                $period = $row['period'];
                $total  = isset( $row['total'] ) ? (int) $row['total'] : 0;

                if ( isset( $timeline[ $period ] ) ) {
                    $timeline[ $period ] = $total;
                }
            }
        }

        $max_value      = ! empty( $timeline ) ? max( $timeline ) : 0;
        $total_activity = array_sum( $timeline );
        $points         = [];

        foreach ( $timeline as $period => $value ) {
            $timestamp = strtotime( $period . '-01 00:00:00' );
            $label     = $timestamp ? wp_date( _x( 'M \’y', 'Admin activity chart month label', 'haber-sitesi' ), $timestamp ) : $period;
            $ratio     = $max_value > 0 ? $value / $max_value : 0;
            $ratio     = max( 0, min( 1, $ratio ) );
            $points[]  = [
                'period' => $period,
                'label'  => $label,
                'value'  => $value,
                'ratio'  => $max_value > 0 ? max( 0.12, min( 1, $ratio ) ) : 0,
            ];
        }

        $average = ! empty( $timeline ) ? (int) round( $total_activity / count( $timeline ) ) : 0;

        $peak_period = '';
        if ( $max_value > 0 ) {
            foreach ( $timeline as $period => $value ) {
                if ( $value === $max_value ) {
                    $peak_period = $period;
                    break;
                }
            }
        }

        $peak_label = '';
        if ( $peak_period ) {
            $peak_timestamp = strtotime( $peak_period . '-01 00:00:00' );
            $peak_label     = $peak_timestamp ? wp_date( _x( 'F Y', 'Admin activity chart peak month label', 'haber-sitesi' ), $peak_timestamp ) : $peak_period;
        }

        return [
            'points' => $points,
            'total'  => $total_activity,
            'average'=> $average,
            'peak'   => [
                'label' => $peak_label,
                'value' => $max_value,
            ],
        ];
    }
}

/**
 * Tarama sonucunu geçici olarak saklayan anahtar.
 */
if ( ! defined( 'HABER_SITESI_CONFLICT_TRANSIENT' ) ) {
    define( 'HABER_SITESI_CONFLICT_TRANSIENT', 'haber_sitesi_conflict_scan' );
}

/**
 * Tema dosyalarında birleştirme işaretleri olup olmadığını döndürür.
 *
 * @return array<int, string>
 */
function haber_sitesi_get_conflict_marker_files() {
    static $cached = null;

    if ( null !== $cached ) {
        return $cached;
    }

    $cached = get_transient( HABER_SITESI_CONFLICT_TRANSIENT );

    if ( false !== $cached && is_array( $cached ) ) {
        return $cached;
    }

    $marker_start = str_repeat( '<', 7 ) . ' ';
    $marker_end   = str_repeat( '>', 7 ) . ' ';
    $middle_line  = str_repeat( '=', 7 );
    $patterns     = [ $marker_start, $marker_end, $middle_line ];
    $directory = get_template_directory();
    $files     = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator( $directory, RecursiveDirectoryIterator::SKIP_DOTS )
    );

    /** @var SplFileInfo $file */
    foreach ( $iterator as $file ) {
        if ( ! $file->isFile() ) {
            continue;
        }

        $extension = strtolower( $file->getExtension() );

        if ( ! in_array( $extension, [ 'php', 'css', 'js', 'html', 'md' ], true ) ) {
            continue;
        }

        $contents = @file_get_contents( $file->getPathname() ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

        if ( false === $contents ) {
            continue;
        }

        foreach ( $patterns as $pattern ) {
            if ( false !== strpos( $contents, $pattern ) ) {
                $relative = str_replace( $directory . '/', '', $file->getPathname() );
                $files[]  = $relative;
                break;
            }
        }
    }

    $files = array_values( array_unique( $files ) );

    set_transient( HABER_SITESI_CONFLICT_TRANSIENT, $files, HOUR_IN_SECONDS );

    $cached = $files;

    return $files;
}

/**
 * Yönetim panelinde yeniden tarama isteğini işler.
 */
function haber_sitesi_maybe_rescan_conflicts() {
    if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( empty( $_GET['haber_conflict_rescan'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ?? '' ), 'haber_conflict_rescan' ) ) {
        return;
    }

    delete_transient( HABER_SITESI_CONFLICT_TRANSIENT );

    $files = haber_sitesi_get_conflict_marker_files();

    set_transient( 'haber_sitesi_conflict_rescan_message', empty( $files ) ? 'clean' : 'remaining', MINUTE_IN_SECONDS );

    wp_safe_redirect( remove_query_arg( [ 'haber_conflict_rescan', '_wpnonce' ] ) );
    exit;
}
add_action( 'admin_init', 'haber_sitesi_maybe_rescan_conflicts' );

/**
 * Yeniden tarama sonrasında bilgi mesajını gösterir.
 */
function haber_sitesi_conflict_rescan_feedback() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $status = get_transient( 'haber_sitesi_conflict_rescan_message' );

    if ( false === $status ) {
        return;
    }

    delete_transient( 'haber_sitesi_conflict_rescan_message' );

    if ( 'clean' === $status ) {
        $class   = 'notice notice-success';
        $message = __( 'Çakışma taraması yenilendi, işaret bulunamadı.', 'haber-sitesi' );
    } else {
        $class   = 'notice notice-warning';
        $message = __( 'Çakışma taraması yenilendi, bazı dosyalar hâlâ işaret içeriyor.', 'haber-sitesi' );
    }

    echo '<div class="' . esc_attr( $class ) . '"><p>' . esc_html( $message ) . '</p></div>';
}
add_action( 'admin_notices', 'haber_sitesi_conflict_rescan_feedback', 4 );

/**
 * Çakışma işaretleri bulunduğunda yönetim panelinde uyarı gösterir.
 */
function haber_sitesi_conflict_marker_notice() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $files = haber_sitesi_get_conflict_marker_files();

    if ( empty( $files ) ) {
        return;
    }

    $rescan_url = wp_nonce_url(
        add_query_arg(
            [
                'page'                  => 'haber-sitesi-staff',
                'haber_conflict_rescan' => '1',
            ],
            admin_url( 'admin.php' )
        ),
        'haber_conflict_rescan'
    );

    echo '<div class="notice notice-error haber-conflict-notice">';
    echo '<p>' . esc_html__( 'Temada çözülmemiş birleştirme işaretleri bulundu. Lütfen aşağıdaki dosyaları temizleyin:', 'haber-sitesi' ) . '</p>';
    echo '<ul class="haber-conflict-notice__list">';
    foreach ( $files as $file ) {
        echo '<li>' . esc_html( $file ) . '</li>';
    }
    echo '</ul>';
    echo '<p><a class="button button-secondary" href="' . esc_url( $rescan_url ) . '">' . esc_html__( 'Tarama sonucunu yenile', 'haber-sitesi' ) . '</a></p>';
    echo '</div>';
}
add_action( 'admin_notices', 'haber_sitesi_conflict_marker_notice' );

require get_template_directory() . '/inc/customizer.php';

if ( is_admin() ) {
    require get_template_directory() . '/inc/admin-panel.php';
}

if ( ! function_exists( 'haber_sitesi_primary_menu_fallback' ) ) {
    /**
     * Ana menü için kategori tabanlı yedek gezinme çıktısı.
     */
    function haber_sitesi_primary_menu_fallback() {
        $categories = get_categories( [
            'number'     => 8,
            'hide_empty' => true,
        ] );

        if ( empty( $categories ) ) {
            echo '<ul class="mobile-category-nav__list"><li class="mobile-category-nav__item"><a class="mobile-category-nav__link" href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Ana Sayfa', 'haber-sitesi' ) . '</a></li></ul>';
            return;
        }

        echo '<ul class="mobile-category-nav__list">';
        foreach ( $categories as $category ) {
            echo '<li class="mobile-category-nav__item"><a class="mobile-category-nav__link" href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a></li>';
        }
        echo '</ul>';
    }
}

if ( ! function_exists( 'haber_sitesi_secondary_menu_fallback' ) ) {
    /**
     * Üst bağlantı menüsü için yedek liste.
     */
    function haber_sitesi_secondary_menu_fallback() {
        $home_url   = home_url( '/' );
        $login_link = wp_login_url( $home_url );

        echo '<ul class="desktop-header__utility-list">';
        echo '<li class="desktop-header__utility-item"><a href="' . esc_url( $home_url ) . '">' . esc_html__( 'Ana Sayfa', 'haber-sitesi' ) . '</a></li>';
        echo '<li class="desktop-header__utility-item"><a href="' . esc_url( $login_link ) . '">' . esc_html__( 'Giriş', 'haber-sitesi' ) . '</a></li>';
        echo '</ul>';
    }
}

if ( ! function_exists( 'haber_sitesi_desktop_menu_fallback' ) ) {
    /**
     * Masaüstü ana menüsü için kategori tabanlı yedek gezinme çıktısı.
     */
    function haber_sitesi_desktop_menu_fallback() {
        $categories = get_categories( [
            'number'     => 10,
            'hide_empty' => true,
        ] );

        echo '<ul class="desktop-header__menu">';

        if ( empty( $categories ) ) {
            echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Ana Sayfa', 'haber-sitesi' ) . '</a></li>';
        } else {
            foreach ( $categories as $category ) {
                echo '<li><a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a></li>';
            }
        }

        echo '</ul>';
    }
}

if ( ! function_exists( 'haber_sitesi_mobile_menu_fallback' ) ) {
    /**
     * Mobil alt menü için varsayılan bağlantılar.
     */
    function haber_sitesi_mobile_menu_fallback() {
        $home_url            = home_url( '/' );
        $home_anchor         = $home_url;
        $breaking_category   = absint( get_theme_mod( 'haber_breaking_news_category', 0 ) );
        $breaking_category   = $breaking_category > 0 ? $breaking_category : 0;
        $breaking_target     = $home_anchor . '#mobile-breaking-news';

        if ( $breaking_category ) {
            $maybe_link = get_category_link( $breaking_category );

            if ( ! is_wp_error( $maybe_link ) ) {
                $breaking_target = $maybe_link;
            }
        }
        $categories_target   = $home_anchor . '#mobile-categories';
        $most_read_target    = $home_anchor . '#mobile-most-read';
        $profile_target      = wp_login_url( $home_url );

        echo '<ul class="mobile-bottom-nav__list">';
        echo '<li class="mobile-bottom-nav__item"><a class="mobile-bottom-nav__link" href="' . esc_url( $home_url ) . '"><span class="mobile-bottom-nav__icon" aria-hidden="true">🏠</span><span class="mobile-bottom-nav__label">' . esc_html__( 'Ana Sayfa', 'haber-sitesi' ) . '</span></a></li>';
        echo '<li class="mobile-bottom-nav__item"><a class="mobile-bottom-nav__link" href="' . esc_url( $breaking_target ) . '"><span class="mobile-bottom-nav__icon" aria-hidden="true">⚡</span><span class="mobile-bottom-nav__label">' . esc_html__( 'Son Dakika', 'haber-sitesi' ) . '</span></a></li>';
        echo '<li class="mobile-bottom-nav__item"><a class="mobile-bottom-nav__link" href="' . esc_url( $categories_target ) . '"><span class="mobile-bottom-nav__icon" aria-hidden="true">🗂️</span><span class="mobile-bottom-nav__label">' . esc_html__( 'Kategoriler', 'haber-sitesi' ) . '</span></a></li>';
        echo '<li class="mobile-bottom-nav__item"><a class="mobile-bottom-nav__link" href="' . esc_url( $most_read_target ) . '"><span class="mobile-bottom-nav__icon" aria-hidden="true">❤️</span><span class="mobile-bottom-nav__label">' . esc_html__( 'Favoriler', 'haber-sitesi' ) . '</span></a></li>';
        echo '<li class="mobile-bottom-nav__item"><a class="mobile-bottom-nav__link" href="' . esc_url( $profile_target ) . '"><span class="mobile-bottom-nav__icon" aria-hidden="true">👤</span><span class="mobile-bottom-nav__label">' . esc_html__( 'Profil', 'haber-sitesi' ) . '</span></a></li>';
        echo '</ul>';
    }
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
 * Büyük sayı metriklerini kısa biçimde biçimlendirir.
 *
 * @param int $number Gösterilecek sayı.
 *
 * @return string
 */
function haber_sitesi_format_count( $number ) {
    $number = (int) $number;

    if ( $number >= 1000000 ) {
        $value      = $number / 1000000;
        $precision  = $value >= 10 ? 0 : 1;
        $formatted  = number_format_i18n( $value, $precision );

        return sprintf( _x( '%sM', 'count in millions', 'haber-sitesi' ), $formatted );
    }

    if ( $number >= 1000 ) {
        $value      = $number / 1000;
        $precision  = $value >= 10 ? 0 : 1;
        $formatted  = number_format_i18n( $value, $precision );

        return sprintf( _x( '%sK', 'count in thousands', 'haber-sitesi' ), $formatted );
    }

    return number_format_i18n( max( 0, $number ) );
}

/**
 * Yazıya ait görüntülenme sayısını döndürür.
 *
 * @param int $post_id Yazı kimliği.
 *
 * @return int
 */
function haber_sitesi_get_post_views( $post_id = 0 ) {
    $post_id = $post_id ? (int) $post_id : get_the_ID();

    if ( ! $post_id ) {
        return 0;
    }

    $views = get_post_meta( $post_id, 'haber_view_count', true );

    return max( 0, (int) $views );
}

/**
 * Tekil haber görüntülendiğinde görüntülenme sayısını artırır.
 */
function haber_sitesi_track_post_views() {
    if ( ! is_singular( 'post' ) || is_preview() ) {
        return;
    }

    $post_id = get_queried_object_id();

    if ( ! $post_id ) {
        return;
    }

    $views = haber_sitesi_get_post_views( $post_id );
    $views++;

    update_post_meta( $post_id, 'haber_view_count', $views );
}
add_action( 'template_redirect', 'haber_sitesi_track_post_views' );

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

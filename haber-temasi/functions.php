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

if ( ! function_exists( 'haber_sitesi_get_staff_role_label' ) ) {
    /**
     * Haber ekibi rollerini okunabilir metne dönüştürür.
     *
     * @param WP_User $user Kullanıcı nesnesi.
     *
     * @return string
     */
    function haber_sitesi_get_staff_role_label( $user ) {
        if ( ! ( $user instanceof WP_User ) ) {
            return __( 'Haber Ekibi', 'haber-sitesi' );
        }

        $role_map = apply_filters(
            'haber_sitesi_staff_role_labels',
            [
                'administrator'  => __( 'Yönetici', 'haber-sitesi' ),
                'editor'         => __( 'Editör', 'haber-sitesi' ),
                'author'         => __( 'Yazar', 'haber-sitesi' ),
                'contributor'    => __( 'Muhabir', 'haber-sitesi' ),
                'haber_editoru'  => __( 'Editör', 'haber-sitesi' ),
                'haber_yazari'   => __( 'Yazar', 'haber-sitesi' ),
                'haber_muhabiri' => __( 'Muhabir', 'haber-sitesi' ),
            ],
            $user
        );

        foreach ( (array) $user->roles as $role ) {
            if ( isset( $role_map[ $role ] ) ) {
                return apply_filters( 'haber_sitesi_staff_role_label', $role_map[ $role ], $role, $user );
            }

            $role_object = get_role( $role );

            if ( $role_object && isset( $role_object->name ) ) {
                $translated = translate_user_role( $role_object->name );

                if ( $translated ) {
                    return apply_filters( 'haber_sitesi_staff_role_label', $translated, $role, $user );
                }
            }
        }

        $fallback = __( 'Haber Ekibi', 'haber-sitesi' );

        if ( ! empty( $user->roles ) ) {
            $primary_role = (string) reset( $user->roles );

            if ( $primary_role ) {
                $readable = ucwords( str_replace( [ '_', '-' ], ' ', $primary_role ) );

                if ( $readable ) {
                    return apply_filters( 'haber_sitesi_staff_role_label', $readable, $primary_role, $user );
                }
            }
        }

        return apply_filters( 'haber_sitesi_default_staff_role_label', $fallback, $user );
    }
}

if ( ! function_exists( 'haber_sitesi_collect_author_profile' ) ) {
    /**
     * Ön yüzde gösterilecek yazar bilgilerini derler.
     *
     * @param WP_User|int $user Kullanıcı nesnesi ya da kimliği.
     *
     * @return array<string, mixed>
     */
    function haber_sitesi_collect_author_profile( $user ) {
        if ( ! ( $user instanceof WP_User ) ) {
            $user = get_user_by( 'id', (int) $user );
        }

        if ( ! $user instanceof WP_User ) {
            return [];
        }

        $post_count = count_user_posts( $user->ID, 'post', true );

        $latest_posts = get_posts(
            [
                'author'         => $user->ID,
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'no_found_rows'  => true,
            ]
        );

        $latest    = [];
        $latest_id = ! empty( $latest_posts ) ? (int) $latest_posts[0] : 0;

        if ( $latest_id ) {
            $latest_timestamp = get_post_time( 'U', true, $latest_id );
            $latest_title     = get_the_title( $latest_id );

            if ( $latest_timestamp ) {
                $latest = [
                    'title'     => $latest_title,
                    'permalink' => get_permalink( $latest_id ),
                    'time'      => sprintf(
                        __( '%s önce', 'haber-sitesi' ),
                        human_time_diff( $latest_timestamp, current_time( 'timestamp', true ) )
                    ),
                ];
            } else {
                $latest = [
                    'title'     => $latest_title,
                    'permalink' => get_permalink( $latest_id ),
                    'time'      => get_the_date( '', $latest_id ),
                ];
            }
        }

        $display_name = $user->display_name ? $user->display_name : $user->user_login;
        $bio           = get_user_meta( $user->ID, 'description', true );

        return [
            'id'         => $user->ID,
            'name'       => $display_name,
            'role'       => haber_sitesi_get_staff_role_label( $user ),
            'bio'        => $bio ? wp_trim_words( wp_strip_all_tags( $bio ), 28, '…' ) : '',
            'avatar'     => get_avatar_url( $user->ID, [ 'size' => 160 ] ),
            'profile'    => get_author_posts_url( $user->ID ),
            'post_count' => $post_count,
            'latest'     => $latest,
        ];
    }
}

if ( ! function_exists( 'haber_sitesi_get_live_center_defaults' ) ) {
    /**
     * Canlı yayın sahnesi için varsayılanları döndürür.
     *
     * @return array<string, mixed>
     */
    function haber_sitesi_get_live_center_defaults() {
        return [
            'manual'         => false,
            'title'          => '',
            'description'    => '',
            'category'       => '',
            'presenter'      => '',
            'time'           => '',
            'cta_label'      => __( 'Yayını Aç', 'haber-sitesi' ),
            'cta_url'        => '',
            'views'          => 0,
            'comments'       => 0,
            'reading_time'   => '',
            'schedule_title' => '',
            'embed'          => '',
        ];
    }
}

if ( ! function_exists( 'haber_sitesi_filter_live_embed' ) ) {
    /**
     * Canlı yayın embed çıktısını temizler.
     *
     * @param string $embed Embed kodu.
     *
     * @return string
     */
    function haber_sitesi_filter_live_embed( $embed ) {
        if ( empty( $embed ) ) {
            return '';
        }

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

        return wp_kses( $embed, $allowed );
    }
}

if ( ! function_exists( 'haber_sitesi_get_live_center_settings' ) ) {
    /**
     * Canlı yayın sahnesi ayarlarını döndürür.
     *
     * @return array<string, mixed>
     */
    function haber_sitesi_get_live_center_settings() {
        $defaults = haber_sitesi_get_live_center_defaults();

        return [
            'manual'         => (bool) get_theme_mod( 'haber_live_manual_mode', $defaults['manual'] ),
            'title'          => get_theme_mod( 'haber_live_title', $defaults['title'] ),
            'description'    => get_theme_mod( 'haber_live_description', $defaults['description'] ),
            'category'       => get_theme_mod( 'haber_live_category', $defaults['category'] ),
            'presenter'      => get_theme_mod( 'haber_live_presenter', $defaults['presenter'] ),
            'time'           => get_theme_mod( 'haber_live_time', $defaults['time'] ),
            'cta_label'      => get_theme_mod( 'haber_live_cta_label', $defaults['cta_label'] ) ?: $defaults['cta_label'],
            'cta_url'        => get_theme_mod( 'haber_live_cta_url', $defaults['cta_url'] ),
            'views'          => absint( get_theme_mod( 'haber_live_views', $defaults['views'] ) ),
            'comments'       => absint( get_theme_mod( 'haber_live_comments', $defaults['comments'] ) ),
            'reading_time'   => get_theme_mod( 'haber_live_reading_time', $defaults['reading_time'] ),
            'schedule_title' => get_theme_mod( 'haber_live_schedule_title', $defaults['schedule_title'] ),
            'embed'          => haber_sitesi_filter_live_embed( get_theme_mod( 'haber_live_embed', $defaults['embed'] ) ),
        ];
    }
}

if ( ! function_exists( 'haber_sitesi_update_live_center_settings' ) ) {
    /**
     * Canlı yayın sahnesi ayarlarını günceller.
     *
     * @param array<string, mixed> $settings Ayar değerleri.
     */
    function haber_sitesi_update_live_center_settings( array $settings ) {
        $defaults = haber_sitesi_get_live_center_defaults();

        $manual = ! empty( $settings['manual'] );
        set_theme_mod( 'haber_live_manual_mode', $manual );

        $text_fields = [
            'haber_live_title'          => isset( $settings['title'] ) ? sanitize_text_field( $settings['title'] ) : $defaults['title'],
            'haber_live_category'       => isset( $settings['category'] ) ? sanitize_text_field( $settings['category'] ) : $defaults['category'],
            'haber_live_presenter'      => isset( $settings['presenter'] ) ? sanitize_text_field( $settings['presenter'] ) : $defaults['presenter'],
            'haber_live_time'           => isset( $settings['time'] ) ? sanitize_text_field( $settings['time'] ) : $defaults['time'],
            'haber_live_cta_label'      => isset( $settings['cta_label'] ) ? sanitize_text_field( $settings['cta_label'] ) : $defaults['cta_label'],
            'haber_live_reading_time'   => isset( $settings['reading_time'] ) ? sanitize_text_field( $settings['reading_time'] ) : $defaults['reading_time'],
            'haber_live_schedule_title' => isset( $settings['schedule_title'] ) ? sanitize_text_field( $settings['schedule_title'] ) : $defaults['schedule_title'],
        ];

        foreach ( $text_fields as $mod => $value ) {
            set_theme_mod( $mod, $value );
        }

        $description = isset( $settings['description'] ) ? haber_sitesi_filter_conflict_markers( wp_kses_post( $settings['description'] ) ) : $defaults['description'];
        set_theme_mod( 'haber_live_description', $description );

        $cta_url = isset( $settings['cta_url'] ) ? esc_url_raw( $settings['cta_url'] ) : $defaults['cta_url'];
        set_theme_mod( 'haber_live_cta_url', $cta_url );

        $views    = isset( $settings['views'] ) ? absint( $settings['views'] ) : $defaults['views'];
        $comments = isset( $settings['comments'] ) ? absint( $settings['comments'] ) : $defaults['comments'];

        set_theme_mod( 'haber_live_views', $views );
        set_theme_mod( 'haber_live_comments', $comments );

        $embed = isset( $settings['embed'] ) ? haber_sitesi_filter_live_embed( $settings['embed'] ) : $defaults['embed'];
        set_theme_mod( 'haber_live_embed', $embed );
    }
}

add_action( 'after_setup_theme', 'haber_sitesi_setup' );

/**
 * Script ve stil dosyalarını yükler.
 */
function haber_sitesi_enqueue_assets() {
    $version = wp_get_theme()->get( 'Version' );

    wp_register_style(
        'haber-sitesi-main',
        get_template_directory_uri() . '/assets/css/main.css',
        [],
        $version
    );

    wp_enqueue_style( 'haber-sitesi-main' );

    wp_enqueue_style( 'haber-sitesi-style', get_stylesheet_uri(), [ 'haber-sitesi-main' ], $version );

    wp_enqueue_script( 'haber-sitesi-navigation', get_template_directory_uri() . '/assets/js/main.js', [ 'jquery' ], $version, true );

    wp_localize_script(
        'haber-sitesi-navigation',
        'haberSitesiInteract',
        [
            'shareCopied'       => __( 'Bağlantı panoya kopyalandı.', 'haber-sitesi' ),
            'shareCopyFallback' => __( 'Bağlantı kopyalanamadı. Lütfen paylaşım bağlantısını manuel olarak açın.', 'haber-sitesi' ),
            'saveLabel'         => __( 'Kaydet', 'haber-sitesi' ),
            'savedLabel'        => __( 'Kaydedildi', 'haber-sitesi' ),
            'liveUpdated'       => __( 'Canlı yayın güncellendi: %s', 'haber-sitesi' ),
        ]
    );
}
add_action( 'wp_enqueue_scripts', 'haber_sitesi_enqueue_assets' );

/**
 * Yönetim portalının yüklenip yüklenmediğini tespit eder.
 *
 * @return bool
 */
function haber_sitesi_is_portal_request() {
    if ( is_page_template( 'page-templates/portal-haber-yonetimi.php' ) ) {
        return true;
    }

    $portal_flag = get_query_var( 'haber_portal', '' );

    return ! empty( $portal_flag );
}

/**
 * Yönetim portalı için özel varlıkları yükler.
 */
function haber_sitesi_enqueue_portal_assets() {
    if ( ! haber_sitesi_is_portal_request() ) {
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

if ( ! function_exists( 'haber_sitesi_get_briefing_panels' ) ) {
    /**
     * Gündem ajandası için kategorilere göre içerik panelleri oluşturur.
     *
     * @param array<string, mixed> $args Argümanlar.
     *
     * @return array{panels: array<int, array<string, mixed>>, used_ids: array<int, int>}
     */
    function haber_sitesi_get_briefing_panels( $args = [] ) {
        $defaults = [
            'limit'           => 4,
            'posts_per_panel' => 4,
            'exclude'         => [],
        ];

        $args       = wp_parse_args( $args, $defaults );
        $limit      = max( 1, (int) $args['limit'] );
        $per_panel  = max( 1, (int) $args['posts_per_panel'] );
        $exclude    = array_filter( array_map( 'absint', (array) $args['exclude'] ) );
        $used_ids   = [];
        $panels     = [];
        $candidates = [];

        $preferred_slugs = apply_filters(
            'haber_sitesi_briefing_preferred_slugs',
            [ 'gundem', 'ekonomi', 'spor', 'dunya' ]
        );

        foreach ( (array) $preferred_slugs as $slug ) {
            $slug = sanitize_title( $slug );

            if ( ! $slug ) {
                continue;
            }

            $term = get_category_by_slug( $slug );

            if ( $term && ! is_wp_error( $term ) ) {
                $candidates[ $term->term_id ] = $term;
            }
        }

        if ( count( $candidates ) < $limit ) {
            $fallback_terms = get_terms(
                [
                    'taxonomy'   => 'category',
                    'hide_empty' => true,
                    'orderby'    => 'count',
                    'order'      => 'DESC',
                    'number'     => $limit * 2,
                    'exclude'    => array_keys( $candidates ),
                ]
            );

            if ( ! empty( $fallback_terms ) && ! is_wp_error( $fallback_terms ) ) {
                foreach ( $fallback_terms as $term ) {
                    if ( count( $candidates ) >= $limit ) {
                        break;
                    }

                    $candidates[ $term->term_id ] = $term;
                }
            }
        }

        if ( empty( $candidates ) ) {
            return [
                'panels'   => [],
                'used_ids' => [],
            ];
        }

        $terms = array_slice( array_values( $candidates ), 0, $limit );

        foreach ( $terms as $term ) {
            $query = new WP_Query(
                [
                    'post_type'           => 'post',
                    'post_status'         => 'publish',
                    'posts_per_page'      => $per_panel,
                    'ignore_sticky_posts' => 1,
                    'no_found_rows'       => true,
                    'cat'                 => $term->term_id,
                    'post__not_in'        => array_unique( array_merge( $exclude, $used_ids ) ),
                ]
            );

            if ( ! $query->have_posts() ) {
                wp_reset_postdata();
                continue;
            }

            $items = [];

            while ( $query->have_posts() ) {
                $query->the_post();

                $collected = haber_sitesi_collect_post_data( get_the_ID(), 22 );

                if ( ! empty( $collected ) ) {
                    $items[]   = $collected;
                    $used_ids[] = $collected['id'];
                }
            }

            wp_reset_postdata();

            if ( empty( $items ) ) {
                continue;
            }

            $panels[] = [
                'term'  => $term,
                'posts' => $items,
                'slug'  => $term->slug ? sanitize_title( $term->slug ) : 'term-' . $term->term_id,
            ];
        }

        return [
            'panels'   => $panels,
            'used_ids' => array_values( array_unique( $used_ids ) ),
        ];
    }
}

if ( ! function_exists( 'haber_sitesi_customize_market_snapshot' ) ) {
    /**
     * Özelleştirici ayarlarını piyasa panosu verilerine uygular.
     *
     * @param array<int, array<string, mixed>> $snapshot Varsayılan piyasa verisi.
     *
     * @return array<int, array<string, mixed>>
     */
    function haber_sitesi_customize_market_snapshot( $snapshot ) {
        $snapshot      = is_array( $snapshot ) ? $snapshot : [];
        $ordered_items = [];
        $order         = [];

        foreach ( $snapshot as $item ) {
            if ( empty( $item['label'] ) ) {
                continue;
            }

            $label = wp_strip_all_tags( $item['label'] );
            $slug  = sanitize_title( $label );

            if ( ! $slug ) {
                $slug = 'market-' . md5( $label );
            }

            if ( ! in_array( $slug, $order, true ) ) {
                $order[] = $slug;
            }

            $direction = isset( $item['direction'] ) ? sanitize_key( $item['direction'] ) : 'flat';

            if ( ! in_array( $direction, [ 'up', 'down', 'flat' ], true ) ) {
                $direction = 'flat';
            }

            $ordered_items[ $slug ] = [
                'label'     => $label,
                'value'     => isset( $item['value'] ) ? wp_strip_all_tags( $item['value'] ) : '',
                'direction' => $direction,
            ];
        }

        $market_items = [
            'dolar'      => [
                'label'     => __( 'Dolar', 'haber-sitesi' ),
                'value_mod' => 'haber_market_dolar_value',
                'dir_mod'   => 'haber_market_dolar_direction',
            ],
            'euro'       => [
                'label'     => __( 'Euro', 'haber-sitesi' ),
                'value_mod' => 'haber_market_euro_value',
                'dir_mod'   => 'haber_market_euro_direction',
            ],
            'gram-altin' => [
                'label'     => __( 'Gram Altın', 'haber-sitesi' ),
                'value_mod' => 'haber_market_gram-altin_value',
                'dir_mod'   => 'haber_market_gram-altin_direction',
            ],
            'bist-100'   => [
                'label'     => __( 'BIST 100', 'haber-sitesi' ),
                'value_mod' => 'haber_market_bist-100_value',
                'dir_mod'   => 'haber_market_bist-100_direction',
            ],
        ];

        foreach ( $market_items as $slug => $data ) {
            $value     = trim( (string) get_theme_mod( $data['value_mod'], '' ) );
            $direction = get_theme_mod( $data['dir_mod'], 'inherit' );
            $existing  = isset( $ordered_items[ $slug ] ) ? $ordered_items[ $slug ] : null;

            if ( '' === $value && ( 'inherit' === $direction || '' === $direction ) ) {
                continue;
            }

            if ( '' === $value && $existing && ! empty( $existing['value'] ) ) {
                $value = $existing['value'];
            }

            if ( '' === $value ) {
                continue;
            }

            $direction = sanitize_key( $direction );

            if ( 'inherit' === $direction ) {
                $direction = $existing && ! empty( $existing['direction'] ) ? $existing['direction'] : 'flat';
            }

            if ( ! in_array( $direction, [ 'up', 'down', 'flat' ], true ) ) {
                $direction = 'flat';
            }

            $ordered_items[ $slug ] = [
                'label'     => $data['label'],
                'value'     => $value,
                'direction' => $direction,
            ];

            if ( ! in_array( $slug, $order, true ) ) {
                $order[] = $slug;
            }
        }

        $result = [];

        foreach ( array_keys( $market_items ) as $slug ) {
            if ( isset( $ordered_items[ $slug ] ) ) {
                $result[] = $ordered_items[ $slug ];
                unset( $ordered_items[ $slug ] );
                $index = array_search( $slug, $order, true );

                if ( false !== $index ) {
                    unset( $order[ $index ] );
                }
            }
        }

        foreach ( $order as $slug ) {
            if ( isset( $ordered_items[ $slug ] ) ) {
                $result[] = $ordered_items[ $slug ];
                unset( $ordered_items[ $slug ] );
            }
        }

        if ( ! empty( $ordered_items ) ) {
            foreach ( $ordered_items as $item ) {
                $result[] = $item;
            }
        }

        return $result;
    }
}
add_filter( 'haber_sitesi_market_snapshot', 'haber_sitesi_customize_market_snapshot', 5 );

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
 * Birleştirme işareti belirteçlerini döndürür.
 *
 * @return array<string, string>
 */
function haber_sitesi_get_conflict_marker_tokens() {
    static $tokens = null;

    if ( null === $tokens ) {
        $tokens = [
            'start'  => str_repeat( '<', 7 ),
            'middle' => str_repeat( '=', 7 ),
            'end'    => str_repeat( '>', 7 ),
        ];
    }

    return $tokens;
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

    $tokens   = haber_sitesi_get_conflict_marker_tokens();
    $patterns = [ $tokens['start'], $tokens['middle'], $tokens['end'] ];
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

/**
 * Birleştirme işareti bloklarını temizleyerek önceki veya sonraki sürümü seçer.
 *
 * @param string $content Temizlenecek içerik.
 *
 * @return string
 */
function haber_sitesi_strip_conflict_markers_from_string( $content ) {
    if ( ! is_string( $content ) || '' === $content ) {
        return $content;
    }

    $tokens = haber_sitesi_get_conflict_marker_tokens();
    $needle = false;

    foreach ( $tokens as $token ) {
        if ( false !== strpos( $content, $token ) ) {
            $needle = true;
            break;
        }
    }

    if ( ! $needle ) {
        return $content;
    }

    $pattern = sprintf(
        '/%1$s[^\r\n]*\r?\n([\s\S]*?)\r?\n%2$s\r?\n([\s\S]*?)\r?\n%3$s[^\r\n]*(?:\r?\n|$)/',
        preg_quote( $tokens['start'], '/' ),
        preg_quote( $tokens['middle'], '/' ),
        preg_quote( $tokens['end'], '/' )
    );

    $content = preg_replace_callback(
        $pattern,
        function ( $matches ) {
            $current   = $matches[1];
            $incoming  = $matches[2];
            $score_one = strlen( preg_replace( '/\s+/u', '', $current ) );
            $score_two = strlen( preg_replace( '/\s+/u', '', $incoming ) );

            return $score_one >= $score_two ? $current : $incoming;
        },
        $content
    );

    $content = preg_replace(
        sprintf( '/%s[^\r\n]*(?:\r?\n|$)/', preg_quote( $tokens['start'], '/' ) ),
        '',
        $content
    );

    $content = preg_replace(
        sprintf( '/%s(?:\r?\n|$)/', preg_quote( $tokens['middle'], '/' ) ),
        '',
        $content
    );

    $content = preg_replace(
        sprintf( '/%s[^\r\n]*(?:\r?\n|$)/', preg_quote( $tokens['end'], '/' ) ),
        '',
        $content
    );

    return $content;
}

/**
 * Diziler için birleştirme işaretlerini derinlemesine temizler.
 *
 * @param mixed $value Filtrelenecek değer.
 *
 * @return mixed
 */
function haber_sitesi_strip_conflict_markers_deep( $value ) {
    if ( is_string( $value ) ) {
        return haber_sitesi_strip_conflict_markers_from_string( $value );
    }

    if ( is_array( $value ) ) {
        foreach ( $value as $key => $item ) {
            $value[ $key ] = haber_sitesi_strip_conflict_markers_deep( $item );
        }
    }

    return $value;
}

/**
 * Birleştirme işaretlerini seçenek güncellemelerinde temizler.
 *
 * @param mixed  $value     Yeni değer.
 * @param mixed  $old_value Eski değer.
 * @param string $option    Seçenek adı.
 *
 * @return mixed
 */
function haber_sitesi_pre_update_option_conflict_markers( $value, $old_value = null, $option = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    unset( $old_value, $option );

    return haber_sitesi_strip_conflict_markers_deep( $value );
}

/**
 * Yorum kaydedilirken birleştirme işaretlerini temizler.
 *
 * @param array $comment_data Yorum verileri.
 *
 * @return array
 */
function haber_sitesi_preprocess_comment_conflicts( $comment_data ) {
    return haber_sitesi_strip_conflict_markers_deep( $comment_data );
}

/**
 * İçerik filtrelerinde çakışma işaretlerini temizler.
 *
 * @param string $value Filtrelenecek değer.
 *
 * @return string
 */
function haber_sitesi_filter_conflict_markers( $value ) {
    return haber_sitesi_strip_conflict_markers_from_string( $value );
}

add_filter( 'the_content', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'get_the_excerpt', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'the_title', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'nav_menu_item_title', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'widget_text_content', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'comment_text', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'title_save_pre', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'content_save_pre', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'excerpt_save_pre', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'pre_term_name', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'pre_term_slug', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'pre_term_description', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'pre_user_display_name', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'pre_user_description', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'pre_user_first_name', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'pre_user_last_name', 'haber_sitesi_filter_conflict_markers', 0 );
add_filter( 'pre_user_nickname', 'haber_sitesi_filter_conflict_markers', 0 );

add_filter( 'pre_update_option_blogname', 'haber_sitesi_pre_update_option_conflict_markers', 0, 3 );
add_filter( 'pre_update_option_blogdescription', 'haber_sitesi_pre_update_option_conflict_markers', 0, 3 );
add_filter( 'preprocess_comment', 'haber_sitesi_preprocess_comment_conflicts', 0 );

add_filter(
    'document_title_parts',
    function ( $parts ) {
        foreach ( $parts as $key => $value ) {
            if ( is_string( $value ) ) {
                $parts[ $key ] = haber_sitesi_strip_conflict_markers_from_string( $value );
            }
        }

        return $parts;
    },
    0
);

/**
 * Ön yüzde tamponlama yaparak olası birleştirme işaretlerini temizler.
 */
function haber_sitesi_buffer_conflict_markers() {
    if ( is_admin() || wp_doing_ajax() || wp_is_json_request() || is_feed() ) {
        return;
    }

    ob_start( 'haber_sitesi_strip_conflict_markers_from_string' );
}
add_action( 'template_redirect', 'haber_sitesi_buffer_conflict_markers', 0 );

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

/**
 * Portal için özel sorgu değişkenini kaydeder.
 *
 * @param array $vars Sorgu değişkenleri.
 *
 * @return array
 */
function haber_sitesi_maybe_ensure_portal_page() {
    if ( defined( 'WP_INSTALLING' ) && WP_INSTALLING ) {
        return;
    }

    static $processed = false;

    if ( $processed ) {
        return;
    }

    $processed = true;

    $stored_id   = (int) get_option( 'haber_sitesi_portal_page_id', 0 );
    $stored_post = $stored_id ? get_post( $stored_id ) : null;
    $needs_flush = false;
    $page_id     = 0;

    if ( $stored_post && 'trash' !== $stored_post->post_status ) {
        $page_id = $stored_post->ID;
    } else {
        $existing = get_page_by_path( 'yonet' );

        if ( $existing && 'trash' !== $existing->post_status ) {
            $page_id = $existing->ID;
        } else {
            $page_id = wp_insert_post(
                [
                    'post_title'   => __( 'Haber Yönetim Portalı', 'haber-sitesi' ),
                    'post_name'    => 'yonet',
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                    'post_content' => __( 'Bu sayfa haber yönetim portalına ayrılmıştır.', 'haber-sitesi' ),
                ],
                true
            );

            if ( ! is_wp_error( $page_id ) ) {
                $needs_flush = true;
            }
        }
    }

    if ( empty( $page_id ) || is_wp_error( $page_id ) ) {
        return;
    }

    if ( 'publish' !== get_post_status( $page_id ) ) {
        wp_update_post(
            [
                'ID'          => $page_id,
                'post_status' => 'publish',
            ]
        );
    }

    if ( 'page-templates/portal-haber-yonetimi.php' !== get_page_template_slug( $page_id ) ) {
        update_post_meta( $page_id, '_wp_page_template', 'page-templates/portal-haber-yonetimi.php' );
    }

    update_option( 'haber_sitesi_portal_page_id', $page_id );

    if ( $needs_flush ) {
        haber_sitesi_register_portal_rewrite();
        flush_rewrite_rules( false );
    }
}
add_action( 'after_setup_theme', 'haber_sitesi_maybe_ensure_portal_page', 20 );

function haber_sitesi_register_portal_query_var( $vars ) {
    $vars[] = 'haber_portal';

    return $vars;
}
add_filter( 'query_vars', 'haber_sitesi_register_portal_query_var' );

/**
 * /yonet rotasını kaydeder.
 */
function haber_sitesi_register_portal_rewrite() {
    add_rewrite_rule( '^yonet/?$', 'index.php?haber_portal=1', 'top' );
}
add_action( 'init', 'haber_sitesi_register_portal_rewrite' );

/**
 * Portal şablonunu yükler.
 *
 * @param string $template Geçerli şablon yolu.
 *
 * @return string
 */
function haber_sitesi_load_portal_template( $template ) {
    if ( empty( get_query_var( 'haber_portal' ) ) ) {
        return $template;
    }

    if ( ! is_user_logged_in() ) {
        auth_redirect();
    }

    if ( ! current_user_can( 'edit_others_posts' ) ) {
        wp_die( esc_html__( 'Bu alana erişim yetkiniz bulunmuyor.', 'haber-sitesi' ), '', [ 'response' => 403 ] );
    }

    $portal_template = get_template_directory() . '/page-templates/portal-haber-yonetimi.php';

    if ( file_exists( $portal_template ) ) {
        return $portal_template;
    }

    return $template;
}
add_filter( 'template_include', 'haber_sitesi_load_portal_template' );

/**
 * Tema etkinleştirildiğinde /yonet rotası için kalıcı bağlantıları yeniler.
 */
function haber_sitesi_flush_rewrite_on_activation() {
    haber_sitesi_register_portal_rewrite();
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'haber_sitesi_flush_rewrite_on_activation' );

/**
 * Hızlı erişim panelinde gösterilecek aksiyonları döndürür.
 *
 * @return array<int, array<string, string>>
 */
function haber_sitesi_get_quick_dock_items() {
    $defaults = [
        'contact'   => [
            'label'       => __( 'İletişim Merkezi', 'haber-sitesi' ),
            'description' => __( 'Okur ilişkileri ve redaksiyon hattı.', 'haber-sitesi' ),
            'url'         => home_url( '/iletisim/' ),
            'icon'        => 'chat',
        ],
        'live'      => [
            'label'       => __( 'Canlı Yayın', 'haber-sitesi' ),
            'description' => __( 'Stüdyodan anlık yayın akışı.', 'haber-sitesi' ),
            'url'         => home_url( '/canli-yayin/' ),
            'icon'        => 'live',
        ],
        'advertise' => [
            'label'       => __( 'Reklam & Medya', 'haber-sitesi' ),
            'description' => __( 'Medya kiti ve iş birlikleri.', 'haber-sitesi' ),
            'url'         => home_url( '/medya-kiti/' ),
            'icon'        => 'advertise',
        ],
        'tip'       => [
            'label'       => __( 'Haber İhbarı', 'haber-sitesi' ),
            'description' => __( 'Güvenli ihbar kanalı.', 'haber-sitesi' ),
            'url'         => home_url( '/haber-ihbari/' ),
            'icon'        => 'tip',
        ],
    ];

    $items = [];

    foreach ( $defaults as $slug => $data ) {
        $enabled = get_theme_mod( "haber_quick_dock_{$slug}_enable", true );

        if ( ! $enabled ) {
            continue;
        }

        $label       = get_theme_mod( "haber_quick_dock_{$slug}_label", $data['label'] );
        $description = get_theme_mod( "haber_quick_dock_{$slug}_description", $data['description'] );
        $url         = get_theme_mod( "haber_quick_dock_{$slug}_url", $data['url'] );

        if ( empty( $url ) ) {
            continue;
        }

        $items[] = [
            'slug'        => $slug,
            'label'       => $label ? $label : $data['label'],
            'description' => $description,
            'url'         => $url,
            'icon'        => $data['icon'],
        ];
    }

    return apply_filters( 'haber_sitesi_quick_dock_items', $items, $defaults );
}

/**
 * Belirtilen ikon anahtarına göre SVG döndürür.
 *
 * @param string $icon Icon anahtarı.
 *
 * @return string
 */
function haber_sitesi_get_quick_dock_icon_svg( $icon ) {
    $svg_open  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
    $svg_close = '</svg>';

    switch ( $icon ) {
        case 'live':
            $paths = '<circle cx="12" cy="12" r="8.5"></circle><path fill="currentColor" stroke="currentColor" stroke-width="0" d="M11 8.75l4.75 3.25L11 15.25z"></path>';
            break;
        case 'advertise':
            $paths = '<path d="M3.5 11.25l13-4.25v10l-13-4.25V11.25z"></path><path d="M9.5 13.5v4.25a2.25 2.25 0 004.5 0v-2.5"></path>';
            break;
        case 'tip':
            $paths = '<path d="M12 3.5a6 6 0 00-6 6c0 2.1 1 3.62 2.4 4.7.56.44.85 1.15.77 1.86l-.14 1.27h6.02l-.14-1.27c-.08-.71.21-1.42.77-1.86 1.4-1.08 2.4-2.6 2.4-4.7a6 6 0 00-6-6z"></path><path d="M10 21h4"></path>';
            break;
        case 'chat':
        default:
            $paths = '<path d="M5 6.75A2.75 2.75 0 017.75 4h8.5A2.75 2.75 0 0119 6.75v5.5A2.75 2.75 0 0116.25 15h-4.5L8.5 18.5V15H7.75A2.75 2.75 0 015 12.25v-5.5z"></path><path d="M8.75 9.5h6.5"></path><path d="M8.75 12h4"></path>';
            break;
    }

    return $svg_open . $paths . $svg_close;
}

/**
 * Hızlı erişim panelini önyüze ekler.
 */
function haber_sitesi_render_quick_dock() {
    if ( is_admin() && ! is_customize_preview() ) {
        return;
    }

    if ( is_page_template( 'page-templates/portal-haber-yonetimi.php' ) || (int) get_query_var( 'haber_portal' ) === 1 ) {
        return;
    }

    $enabled = get_theme_mod( 'haber_quick_dock_enable', true );

    if ( ! $enabled ) {
        return;
    }

    $items = haber_sitesi_get_quick_dock_items();

    if ( empty( $items ) ) {
        return;
    }

    $title  = get_theme_mod( 'haber_quick_dock_title', __( 'Hızlı Erişim', 'haber-sitesi' ) );
    $panel_id = 'haber-quick-dock-panel';

    ?>
    <aside class="quick-dock" data-quick-dock>
        <button class="quick-dock__toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel_id ); ?>" data-quick-dock-toggle>
            <span class="screen-reader-text"><?php esc_html_e( 'Hızlı erişim panelini aç', 'haber-sitesi' ); ?></span>
            <span class="quick-dock__toggle-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 12h12"></path>
                    <path d="M12 6v12"></path>
                </svg>
            </span>
        </button>
        <div class="quick-dock__panel" id="<?php echo esc_attr( $panel_id ); ?>" data-quick-dock-panel aria-hidden="true">
            <?php if ( $title ) : ?>
                <span class="quick-dock__title"><?php echo esc_html( $title ); ?></span>
            <?php endif; ?>
            <ul class="quick-dock__list">
                <?php foreach ( $items as $item ) : ?>
                    <li class="quick-dock__item">
                        <a class="quick-dock__link quick-dock__link--<?php echo esc_attr( $item['slug'] ); ?>" href="<?php echo esc_url( $item['url'] ); ?>">
                            <span class="quick-dock__icon" aria-hidden="true">
                                <?php echo haber_sitesi_get_quick_dock_icon_svg( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </span>
                            <span class="quick-dock__text">
                                <span class="quick-dock__label"><?php echo esc_html( $item['label'] ); ?></span>
                                <?php if ( ! empty( $item['description'] ) ) : ?>
                                    <span class="quick-dock__meta"><?php echo esc_html( $item['description'] ); ?></span>
                                <?php endif; ?>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>
    <?php
}
add_action( 'wp_footer', 'haber_sitesi_render_quick_dock', 15 );

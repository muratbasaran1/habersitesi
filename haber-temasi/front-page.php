<?php
/**
 * Anasayfa şablonu.
 *
 * @package Haber_Sitesi
 */

global $post;

get_header();

$collect_post = static function ( $post_id, $excerpt_words = 24 ) {
    $post_id = (int) $post_id;

    if ( ! $post_id ) {
        return [];
    }

    $categories     = get_the_category( $post_id );
    $category_name  = ! empty( $categories ) ? $categories[0]->name : __( 'Güncel', 'haber-sitesi' );
    $time_diff      = human_time_diff( get_the_time( 'U', $post_id ), current_time( 'timestamp' ) );
    $hero_image     = get_the_post_thumbnail_url( $post_id, 'large' );
    $thumb_image    = get_the_post_thumbnail_url( $post_id, 'medium_large' );

    return [
        'id'           => $post_id,
        'title'        => get_the_title( $post_id ),
        'permalink'    => get_permalink( $post_id ),
        'excerpt'      => wp_trim_words( get_the_excerpt( $post_id ), $excerpt_words ),
        'image'        => $hero_image,
        'thumb'        => $thumb_image,
        'category'     => $category_name,
        'time'         => sprintf( __( '%s önce', 'haber-sitesi' ), $time_diff ),
        'views'        => haber_sitesi_get_post_views( $post_id ),
        'comments'     => get_comments_number( $post_id ),
        'reading_time' => haber_sitesi_get_reading_time( $post_id ),
    ];
};

$excluded_ids = [];

$hero_query = new WP_Query(
    [
        'post_type'           => 'post',
        'posts_per_page'      => 7,
        'ignore_sticky_posts' => false,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
    ]
);

$hero_items = [];

if ( $hero_query->have_posts() ) {
    while ( $hero_query->have_posts() ) {
        $hero_query->the_post();
        $hero_items[]   = $collect_post( get_the_ID(), 36 );
        $excluded_ids[] = get_the_ID();
    }
    wp_reset_postdata();
}

$top_categories = get_terms(
    [
        'taxonomy'   => 'category',
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 3,
        'hide_empty' => true,
    ]
);

$category_panels = [];

if ( ! empty( $top_categories ) && ! is_wp_error( $top_categories ) ) {
    foreach ( $top_categories as $term ) {
        $panel_query = new WP_Query(
            [
                'post_type'           => 'post',
                'posts_per_page'      => 3,
                'ignore_sticky_posts' => 1,
                'post_status'         => 'publish',
                'no_found_rows'       => true,
                'post__not_in'        => $excluded_ids,
                'cat'                 => $term->term_id,
            ]
        );

        $panel_posts = [];

        if ( $panel_query->have_posts() ) {
            while ( $panel_query->have_posts() ) {
                $panel_query->the_post();
                $panel_posts[]  = $collect_post( get_the_ID(), 20 );
                $excluded_ids[] = get_the_ID();
            }
            wp_reset_postdata();
        }

        if ( ! empty( $panel_posts ) ) {
            $category_panels[] = [
                'term'  => $term,
                'posts' => $panel_posts,
            ];
        }
    }
}

$digest_query = new WP_Query(
    [
        'post_type'           => 'post',
        'posts_per_page'      => 6,
        'ignore_sticky_posts' => 1,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
        'post__not_in'        => $excluded_ids,
    ]
);

$digest_items = [];

if ( $digest_query->have_posts() ) {
    while ( $digest_query->have_posts() ) {
        $digest_query->the_post();
        $digest_items[] = $collect_post( get_the_ID(), 22 );
        $excluded_ids[] = get_the_ID();
    }
    wp_reset_postdata();
}

$commentary_after = gmdate( 'Y-m-d', current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );

$voices_query = new WP_Query(
    [
        'post_type'           => 'post',
        'posts_per_page'      => 4,
        'ignore_sticky_posts' => 1,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
        'post__not_in'        => $excluded_ids,
        'orderby'             => 'comment_count',
        'order'               => 'DESC',
        'date_query'          => [
            [
                'after'     => $commentary_after,
                'inclusive' => true,
            ],
        ],
    ]
);

$voices_items = [];

if ( $voices_query->have_posts() ) {
    while ( $voices_query->have_posts() ) {
        $voices_query->the_post();
        $voices_items[] = $collect_post( get_the_ID(), 18 );
        $excluded_ids[] = get_the_ID();
    }
    wp_reset_postdata();
}

$stream_query = new WP_Query(
    [
        'post_type'           => 'post',
        'posts_per_page'      => 8,
        'ignore_sticky_posts' => 1,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
        'post__not_in'        => $excluded_ids,
    ]
);

$stream_items = [];

if ( $stream_query->have_posts() ) {
    while ( $stream_query->have_posts() ) {
        $stream_query->the_post();
        $stream_items[] = $collect_post( get_the_ID(), 18 );
    }
    wp_reset_postdata();
}

$trending_query = new WP_Query(
    [
        'post_type'           => 'post',
        'posts_per_page'      => 6,
        'ignore_sticky_posts' => 1,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
        'meta_key'            => 'haber_view_count',
        'orderby'             => 'meta_value_num',
        'order'               => 'DESC',
    ]
);

$trending_items = [];

if ( $trending_query->have_posts() ) {
    while ( $trending_query->have_posts() ) {
        $trending_query->the_post();
        $trending_items[] = $collect_post( get_the_ID(), 12 );
    }
    wp_reset_postdata();
}

$archive_link = get_post_type_archive_link( 'post' );

$weather_location    = get_theme_mod( 'haber_weather_location', __( 'İstanbul', 'haber-sitesi' ) );
$weather_temperature = get_theme_mod( 'haber_weather_temperature', '15°C' );
$weather_condition   = get_theme_mod( 'haber_weather_condition', __( 'Güneşli', 'haber-sitesi' ) );

$market_snapshot = apply_filters(
    'haber_sitesi_market_snapshot',
    [
        [
            'label'     => __( 'Dolar', 'haber-sitesi' ),
            'value'     => '41,9613',
            'direction' => 'up',
        ],
        [
            'label'     => __( 'Euro', 'haber-sitesi' ),
            'value'     => '48,9260',
            'direction' => 'up',
        ],
        [
            'label'     => __( 'Gram Altın', 'haber-sitesi' ),
            'value'     => '5.335,97',
            'direction' => 'down',
        ],
        [
            'label'     => __( 'BIST 100', 'haber-sitesi' ),
            'value'     => '10.871,08',
            'direction' => 'up',
        ],
        [
            'label'     => __( 'Bitcoin', 'haber-sitesi' ),
            'value'     => '$114.750',
            'direction' => 'down',
        ],
        [
            'label'     => __( 'Ethereum', 'haber-sitesi' ),
            'value'     => '$4.111',
            'direction' => 'down',
        ],
    ]
);

$market_snapshot = array_filter(
    array_map(
        static function ( $item ) {
            $label = isset( $item['label'] ) ? sanitize_text_field( wp_strip_all_tags( $item['label'] ) ) : '';
            $value = isset( $item['value'] ) ? sanitize_text_field( wp_strip_all_tags( $item['value'] ) ) : '';
            $dir   = isset( $item['direction'] ) ? sanitize_key( $item['direction'] ) : 'flat';

            if ( ! $label || ! $value ) {
                return null;
            }

            if ( ! in_array( $dir, [ 'up', 'down', 'flat' ], true ) ) {
                $dir = 'flat';
            }

            return [
                'label'     => $label,
                'value'     => $value,
                'direction' => $dir,
            ];
        },
        (array) $market_snapshot
    )
);

$direction_labels = [
    'up'   => __( 'yükselişte', 'haber-sitesi' ),
    'down' => __( 'düşüşte', 'haber-sitesi' ),
    'flat' => __( 'sabit', 'haber-sitesi' ),
];
?>

<div class="front-screen">
    <?php if ( ! empty( $market_snapshot ) ) : ?>
        <section class="front-market" aria-label="<?php esc_attr_e( 'Piyasa özeti', 'haber-sitesi' ); ?>">
            <div class="front-shell front-market__shell">
                <div class="front-market__items" role="list">
                    <?php foreach ( $market_snapshot as $item ) : ?>
                        <div class="front-market__item front-market__item--<?php echo esc_attr( $item['direction'] ); ?>" role="listitem">
                            <span class="front-market__label"><?php echo esc_html( $item['label'] ); ?></span>
                            <span class="front-market__value"><?php echo esc_html( $item['value'] ); ?></span>
                            <span class="front-market__trend front-market__trend--<?php echo esc_attr( $item['direction'] ); ?>" aria-hidden="true"></span>
                            <span class="screen-reader-text">
                                <?php
                                $direction_key = $item['direction'];
                                echo esc_html( isset( $direction_labels[ $direction_key ] ) ? $direction_labels[ $direction_key ] : $direction_labels['flat'] );
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="front-market__weather" role="status" aria-live="polite">
                    <?php if ( $weather_location || $weather_temperature ) : ?>
                        <span class="front-market__weather-city"><?php echo esc_html( trim( $weather_location . ' ' . $weather_temperature ) ); ?></span>
                    <?php endif; ?>
                    <?php if ( $weather_condition ) : ?>
                        <span class="front-market__weather-condition"><?php echo esc_html( $weather_condition ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="front-slider" id="front-manset" aria-label="<?php esc_attr_e( 'Manşet', 'haber-sitesi' ); ?>">
        <div class="front-shell front-slider__shell" data-front-slider>
            <?php if ( ! empty( $hero_items ) ) : ?>
                <div class="front-slider__stage">
                    <?php foreach ( $hero_items as $index => $item ) :
                        $active    = 0 === $index ? ' is-active' : '';
                        $hidden    = 0 === $index ? 'false' : 'true';
                        $tabindex  = 0 === $index ? '0' : '-1';
                        $panel_id  = 'front-slider-panel-' . ( $index + 1 );
                        $share_url = $item['permalink'];
                        ?>
                        <article
                            id="<?php echo esc_attr( $panel_id ); ?>"
                            class="front-slider__panel<?php echo esc_attr( $active ); ?>"
                            data-index="<?php echo esc_attr( $index ); ?>"
                            role="tabpanel"
                            aria-hidden="<?php echo esc_attr( $hidden ); ?>"
                            tabindex="<?php echo esc_attr( $tabindex ); ?>"
                        >
                            <a class="front-slider__media" href="<?php echo esc_url( $item['permalink'] ); ?>">
                                <?php if ( $item['image'] ) : ?>
                                    <img src="<?php echo esc_url( $item['image'] ); ?>" alt="" />
                                <?php else : ?>
                                    <span class="front-slider__placeholder" aria-hidden="true">🗞️</span>
                                <?php endif; ?>
                            </a>
                            <div class="front-slider__content">
                                <div class="front-slider__meta">
                                    <span class="front-slider__badge"><?php echo esc_html( $item['category'] ); ?></span>
                                    <span class="front-slider__time"><?php echo esc_html( $item['time'] ); ?></span>
                                </div>
                                <h2 class="front-slider__title"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h2>
                                <p class="front-slider__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                <div class="front-slider__footer">
                                    <div class="front-slider__stats">
                                        <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                        <span>💬 <?php echo esc_html( number_format_i18n( $item['comments'] ) ); ?></span>
                                        <span>⏱️ <?php echo esc_html( $item['reading_time'] ); ?></span>
                                    </div>
                                    <div class="front-slider__actions">
                                        <a class="front-slider__action front-slider__action--primary" href="<?php echo esc_url( $item['permalink'] ); ?>"><?php esc_html_e( 'Haberi Oku', 'haber-sitesi' ); ?></a>
                                        <button
                                            type="button"
                                            class="front-slider__action front-slider__action--ghost js-share-button"
                                            data-share-url="<?php echo esc_url( $share_url ); ?>"
                                            data-share-title="<?php echo esc_attr( wp_strip_all_tags( $item['title'] ) ); ?>"
                                            aria-label="<?php echo esc_attr( sprintf( __( '“%s” haberini paylaş', 'haber-sitesi' ), $item['title'] ) ); ?>"
                                        >
                                            <?php esc_html_e( 'Paylaş', 'haber-sitesi' ); ?>
                                        </button>
                                        <button
                                            type="button"
                                            class="front-slider__action front-slider__action--save js-save-button"
                                            data-post-id="<?php echo esc_attr( $item['id'] ); ?>"
                                            data-label-save="<?php esc_attr_e( 'Kaydet', 'haber-sitesi' ); ?>"
                                            data-label-saved="<?php esc_attr_e( 'Kaydedildi', 'haber-sitesi' ); ?>"
                                            aria-pressed="false"
                                            aria-label="<?php echo esc_attr( sprintf( __( '“%s” haberini kaydet', 'haber-sitesi' ), $item['title'] ) ); ?>"
                                        >
                                            <?php esc_html_e( 'Kaydet', 'haber-sitesi' ); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="front-slider__rail" role="tablist" aria-label="<?php esc_attr_e( 'Manşet listesi', 'haber-sitesi' ); ?>">
                    <?php foreach ( $hero_items as $index => $item ) :
                        $active   = 0 === $index ? ' is-active' : '';
                        $selected = 0 === $index ? 'true' : 'false';
                        $panel_id = 'front-slider-panel-' . ( $index + 1 );
                        ?>
                        <button
                            type="button"
                            class="front-slider__thumb<?php echo esc_attr( $active ); ?>"
                            role="tab"
                            aria-selected="<?php echo esc_attr( $selected ); ?>"
                            aria-controls="<?php echo esc_attr( $panel_id ); ?>"
                            data-index="<?php echo esc_attr( $index ); ?>"
                        >
                            <span class="front-slider__thumb-media">
                                <?php if ( $item['thumb'] ) : ?>
                                    <img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="" />
                                <?php else : ?>
                                    <span class="front-slider__thumb-placeholder" aria-hidden="true">🗞️</span>
                                <?php endif; ?>
                            </span>
                            <span class="front-slider__thumb-body">
                                <span class="front-slider__thumb-category"><?php echo esc_html( $item['category'] ); ?></span>
                                <span class="front-slider__thumb-title"><?php echo esc_html( $item['title'] ); ?></span>
                                <span class="front-slider__thumb-time"><?php echo esc_html( $item['time'] ); ?></span>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="front-empty"><?php esc_html_e( 'Henüz içerik eklenmemiş.', 'haber-sitesi' ); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <div class="front-shell front-layout">
        <div class="front-layout__main">
            <section class="front-block" aria-label="<?php esc_attr_e( 'Günün başlıkları', 'haber-sitesi' ); ?>">
                <div class="front-block__header">
                    <h2 class="front-block__title"><?php esc_html_e( 'Günün Öne Çıkanları', 'haber-sitesi' ); ?></h2>
                    <p class="front-block__subtitle"><?php esc_html_e( 'Editör masamızdan seçilen kategoriler', 'haber-sitesi' ); ?></p>
                </div>
                <?php if ( ! empty( $category_panels ) ) : ?>
                    <div class="front-panels">
                        <?php foreach ( $category_panels as $panel ) : ?>
                            <article class="front-panel">
                                <header class="front-panel__header">
                                    <h3 class="front-panel__title"><a href="<?php echo esc_url( get_category_link( $panel['term']->term_id ) ); ?>"><?php echo esc_html( $panel['term']->name ); ?></a></h3>
                                    <?php if ( ! empty( $panel['term']->description ) ) : ?>
                                        <p class="front-panel__description"><?php echo esc_html( wp_trim_words( $panel['term']->description, 18 ) ); ?></p>
                                    <?php endif; ?>
                                </header>
                                <?php
                                $lead    = $panel['posts'][0];
                                $support = array_slice( $panel['posts'], 1 );
                                ?>
                                <div class="front-panel__body">
                                    <div class="front-panel__lead">
                                        <a class="front-panel__media" href="<?php echo esc_url( $lead['permalink'] ); ?>">
                                            <?php if ( $lead['image'] ) : ?>
                                                <img src="<?php echo esc_url( $lead['image'] ); ?>" alt="" />
                                            <?php else : ?>
                                                <span class="front-panel__placeholder" aria-hidden="true">📰</span>
                                            <?php endif; ?>
                                        </a>
                                        <div class="front-panel__content">
                                            <span class="front-panel__meta"><?php echo esc_html( $lead['time'] ); ?></span>
                                            <h4 class="front-panel__headline"><a href="<?php echo esc_url( $lead['permalink'] ); ?>"><?php echo esc_html( $lead['title'] ); ?></a></h4>
                                            <p class="front-panel__excerpt"><?php echo wp_kses_post( $lead['excerpt'] ); ?></p>
                                        </div>
                                    </div>
                                    <?php if ( ! empty( $support ) ) : ?>
                                        <ul class="front-panel__list">
                                            <?php foreach ( $support as $item ) : ?>
                                                <li class="front-panel__item">
                                                    <a class="front-panel__link" href="<?php echo esc_url( $item['permalink'] ); ?>">
                                                        <span class="front-panel__item-title"><?php echo esc_html( $item['title'] ); ?></span>
                                                        <span class="front-panel__item-meta"><?php echo esc_html( $item['time'] ); ?></span>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Öne çıkarılacak kategori bulunamadı.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="front-block front-block--digest" aria-label="<?php esc_attr_e( 'Haber merkezi', 'haber-sitesi' ); ?>">
                <div class="front-block__header">
                    <h2 class="front-block__title"><?php esc_html_e( 'Haber Merkezi', 'haber-sitesi' ); ?></h2>
                    <p class="front-block__subtitle"><?php esc_html_e( 'Günün farklı noktalarından manşet seçkileri', 'haber-sitesi' ); ?></p>
                </div>
                <?php if ( ! empty( $digest_items ) ) : ?>
                    <div class="front-digest">
                        <?php foreach ( $digest_items as $item ) : ?>
                            <article class="front-digest__card">
                                <a class="front-digest__media" href="<?php echo esc_url( $item['permalink'] ); ?>">
                                    <?php if ( $item['thumb'] ) : ?>
                                        <img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="" />
                                    <?php else : ?>
                                        <span class="front-digest__placeholder" aria-hidden="true">🗞️</span>
                                    <?php endif; ?>
                                </a>
                                <div class="front-digest__content">
                                    <div class="front-digest__meta">
                                        <span><?php echo esc_html( $item['category'] ); ?></span>
                                        <span><?php echo esc_html( $item['time'] ); ?></span>
                                    </div>
                                    <h3 class="front-digest__title"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h3>
                                    <p class="front-digest__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                    <div class="front-digest__stats">
                                        <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                        <span>💬 <?php echo esc_html( number_format_i18n( $item['comments'] ) ); ?></span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Haber merkezi için içerik bekleniyor.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="front-block front-block--voices" aria-label="<?php esc_attr_e( 'En çok tartışılanlar', 'haber-sitesi' ); ?>">
                <div class="front-block__header">
                    <h2 class="front-block__title"><?php esc_html_e( 'Editör Masası & Yorumlar', 'haber-sitesi' ); ?></h2>
                    <p class="front-block__subtitle"><?php esc_html_e( 'Okurların gündeme taşıdığı başlıklar', 'haber-sitesi' ); ?></p>
                </div>
                <?php if ( ! empty( $voices_items ) ) : ?>
                    <div class="front-voices">
                        <?php foreach ( $voices_items as $item ) : ?>
                            <article class="front-voices__card">
                                <div class="front-voices__header">
                                    <span class="front-voices__category"><?php echo esc_html( $item['category'] ); ?></span>
                                    <span class="front-voices__time"><?php echo esc_html( $item['time'] ); ?></span>
                                </div>
                                <h3 class="front-voices__title"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h3>
                                <p class="front-voices__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                <div class="front-voices__footer">
                                    <span class="front-voices__stat front-voices__stat--comments">💬 <?php echo esc_html( number_format_i18n( $item['comments'] ) ); ?></span>
                                    <span class="front-voices__stat">👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                    <span class="front-voices__stat">⏱️ <?php echo esc_html( $item['reading_time'] ); ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Henüz öne çıkan yorumlu içerik bulunmuyor.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="front-block front-block--stream" id="front-stream" aria-label="<?php esc_attr_e( 'Canlı haber akışı', 'haber-sitesi' ); ?>">
                <div class="front-block__header">
                    <h2 class="front-block__title"><?php esc_html_e( 'Canlı Haber Akışı', 'haber-sitesi' ); ?></h2>
                    <p class="front-block__subtitle"><?php esc_html_e( 'Gelişmeleri dakika dakika takip edin', 'haber-sitesi' ); ?></p>
                </div>
                <?php if ( ! empty( $stream_items ) ) : ?>
                    <ol class="front-stream">
                        <?php foreach ( $stream_items as $item ) : ?>
                            <li class="front-stream__item">
                                <div class="front-stream__time"><?php echo esc_html( $item['time'] ); ?></div>
                                <div class="front-stream__content">
                                    <h3 class="front-stream__headline"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h3>
                                    <p class="front-stream__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                    <div class="front-stream__meta">
                                        <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                        <span>💬 <?php echo esc_html( number_format_i18n( $item['comments'] ) ); ?></span>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <div class="front-stream__footer">
                        <a class="front-stream__more" href="<?php echo esc_url( $archive_link ? $archive_link : home_url( '/' ) ); ?>"><?php esc_html_e( 'Tüm haberleri görüntüle', 'haber-sitesi' ); ?></a>
                    </div>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Henüz canlı akış bulunmuyor.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>
        </div>

        <aside class="front-layout__sidebar" aria-label="<?php esc_attr_e( 'Yan sütun', 'haber-sitesi' ); ?>">
            <section id="mobile-most-read" class="front-sidebar-card" aria-label="<?php esc_attr_e( 'En çok okunanlar', 'haber-sitesi' ); ?>">
                <div class="front-sidebar-card__header">
                    <h2 class="front-sidebar-card__title"><?php esc_html_e( 'En Çok Okunanlar', 'haber-sitesi' ); ?></h2>
                    <p class="front-sidebar-card__subtitle"><?php esc_html_e( 'Okurlarımızın tercih ettiği başlıklar', 'haber-sitesi' ); ?></p>
                </div>
                <?php if ( ! empty( $trending_items ) ) : ?>
                    <ol class="front-trending">
                        <?php foreach ( $trending_items as $index => $item ) : ?>
                            <li class="front-trending__item">
                                <span class="front-trending__rank"><?php echo esc_html( $index + 1 ); ?></span>
                                <div class="front-trending__body">
                                    <a class="front-trending__link" href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                                    <span class="front-trending__meta">👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Henüz okunma verisi bulunmuyor.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="front-sidebar-card front-sidebar-card--weather" aria-label="<?php esc_attr_e( 'Hava durumu özeti', 'haber-sitesi' ); ?>">
                <div class="front-sidebar-card__header">
                    <h2 class="front-sidebar-card__title"><?php esc_html_e( 'Hava Durumu', 'haber-sitesi' ); ?></h2>
                </div>
                <div class="front-weather">
                    <?php if ( $weather_location ) : ?>
                        <div class="front-weather__city"><?php echo esc_html( $weather_location ); ?></div>
                    <?php endif; ?>
                    <?php if ( $weather_temperature ) : ?>
                        <div class="front-weather__temperature"><?php echo esc_html( $weather_temperature ); ?></div>
                    <?php endif; ?>
                    <?php if ( $weather_condition ) : ?>
                        <div class="front-weather__condition"><?php echo esc_html( $weather_condition ); ?></div>
                    <?php endif; ?>
                    <a class="front-weather__link" href="<?php echo esc_url( home_url( '/hava-durumu' ) ); ?>"><?php esc_html_e( 'Detaylı hava raporu', 'haber-sitesi' ); ?></a>
                </div>
            </section>

            <section class="front-sidebar-card front-sidebar-card--newsletter" aria-label="<?php esc_attr_e( 'Bülten', 'haber-sitesi' ); ?>">
                <div class="front-sidebar-card__header">
                    <h2 class="front-sidebar-card__title"><?php esc_html_e( 'Bültenimize Katılın', 'haber-sitesi' ); ?></h2>
                </div>
                <p class="front-sidebar-card__text"><?php esc_html_e( 'Günün en önemli başlıklarını her sabah e-posta kutunuza gönderelim.', 'haber-sitesi' ); ?></p>
                <form class="front-newsletter" action="<?php echo esc_url( home_url( '/bulten' ) ); ?>" method="get">
                    <label class="screen-reader-text" for="newsletter-email"><?php esc_html_e( 'E-posta adresiniz', 'haber-sitesi' ); ?></label>
                    <input id="newsletter-email" class="front-newsletter__field" type="email" name="email" placeholder="<?php echo esc_attr__( 'ornek@haber.com', 'haber-sitesi' ); ?>" required>
                    <button class="front-newsletter__submit" type="submit"><?php esc_html_e( 'Abone Ol', 'haber-sitesi' ); ?></button>
                </form>
            </section>
        </aside>
    </div>
</div>

<?php
get_footer();

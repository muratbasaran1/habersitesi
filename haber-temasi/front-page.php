<?php
/**
 * Anasayfa şablonu.
 *
 * @package Haber_Sitesi
 */

global $post;

get_header();

$excluded_ids = [];

$hero_query = new WP_Query(
    [
        'post_type'           => 'post',
        'posts_per_page'      => 1,
        'ignore_sticky_posts' => false,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
    ]
);

$weather_location    = get_theme_mod( 'haber_weather_location', __( 'İstanbul', 'haber-sitesi' ) );
$weather_temperature = get_theme_mod( 'haber_weather_temperature', '15°C' );
$weather_condition   = get_theme_mod( 'haber_weather_condition', __( 'Güneşli', 'haber-sitesi' ) );

$top_categories = get_terms(
    [
        'taxonomy'   => 'category',
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 3,
        'hide_empty' => true,
    ]
);

?>
<div class="front-page front-page--pro">
    <section class="front-hero front-section" aria-label="<?php esc_attr_e( 'Manşet haberleri', 'haber-sitesi' ); ?>">
        <?php if ( $hero_query->have_posts() ) : ?>
            <?php
            while ( $hero_query->have_posts() ) :
                $hero_query->the_post();
                $excluded_ids[] = get_the_ID();
                $hero_category  = get_the_category();
                $hero_kicker    = ! empty( $hero_category ) ? $hero_category[0]->name : '';
                $hero_image     = get_the_post_thumbnail_url( get_the_ID(), 'full' );
                $hero_style     = $hero_image ? sprintf( ' style="--front-hero-image: url(%s);"', esc_url( $hero_image ) ) : '';
                ?>
                <article <?php post_class( 'front-hero__lead' ); ?><?php echo $hero_style; ?>>
                    <div class="front-hero__overlay"></div>
                    <div class="front-hero__content">
                        <div class="front-hero__meta">
                            <?php if ( $hero_kicker ) : ?>
                                <span class="front-hero__badge"><?php echo esc_html( $hero_kicker ); ?></span>
                            <?php endif; ?>
                            <span class="front-hero__time"><?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?> <?php esc_html_e( 'önce', 'haber-sitesi' ); ?></span>
                        </div>
                        <h1 class="front-hero__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                        <p class="front-hero__excerpt"><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 38 ) ); ?></p>
                        <div class="front-hero__footer">
                            <?php $hero_views = haber_sitesi_get_post_views( get_the_ID() ); ?>
                            <div class="front-hero__stats">
                                <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $hero_views ) ); ?></span>
                                <span>💬 <?php echo esc_html( number_format_i18n( get_comments_number() ) ); ?></span>
                                <span>⏱️ <?php echo esc_html( haber_sitesi_get_reading_time( get_the_ID() ) ); ?></span>
                            </div>
                            <?php
                            $share_url   = get_permalink();
                            $share_title = wp_strip_all_tags( get_the_title() );
                            ?>
                            <div class="front-hero__actions">
                                <a class="front-hero__action front-hero__action--primary" href="<?php the_permalink(); ?>">
                                    <?php esc_html_e( 'Haberi Oku', 'haber-sitesi' ); ?>
                                </a>
                                <button
                                    type="button"
                                    class="front-hero__action front-hero__action--ghost js-share-button"
                                    data-share-url="<?php echo esc_url( $share_url ); ?>"
                                    data-share-title="<?php echo esc_attr( $share_title ); ?>"
                                    aria-label="<?php echo esc_attr( sprintf( __( '“%s” haberini paylaş', 'haber-sitesi' ), $share_title ) ); ?>"
                                >
                                    <?php esc_html_e( 'Paylaş', 'haber-sitesi' ); ?>
                                </button>
                                <button
                                    type="button"
                                    class="front-hero__action front-hero__action--save js-save-button"
                                    data-post-id="<?php echo esc_attr( get_the_ID() ); ?>"
                                    data-label-save="<?php esc_attr_e( 'Kaydet', 'haber-sitesi' ); ?>"
                                    data-label-saved="<?php esc_attr_e( 'Kaydedildi', 'haber-sitesi' ); ?>"
                                    aria-pressed="false"
                                    aria-label="<?php echo esc_attr( sprintf( __( '“%s” haberini kaydet', 'haber-sitesi' ), $share_title ) ); ?>"
                                >
                                    <?php esc_html_e( 'Kaydet', 'haber-sitesi' ); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
                <?php
            endwhile;
            wp_reset_postdata();
            ?>
        <?php else : ?>
            <p class="front-empty"><?php esc_html_e( 'Henüz içerik eklenmemiş.', 'haber-sitesi' ); ?></p>
        <?php endif; ?>

        <?php
        $rail_query = new WP_Query(
            [
                'post_type'           => 'post',
                'posts_per_page'      => 3,
                'ignore_sticky_posts' => 1,
                'post_status'         => 'publish',
                'no_found_rows'       => true,
                'post__not_in'        => $excluded_ids,
            ]
        );
        ?>

        <div class="front-hero__rail">
            <?php if ( $rail_query->have_posts() ) : ?>
                <?php
                while ( $rail_query->have_posts() ) :
                    $rail_query->the_post();
                    $excluded_ids[] = get_the_ID();
                    $rail_category  = get_the_category();
                    $rail_kicker    = ! empty( $rail_category ) ? $rail_category[0]->name : '';
                    ?>
                    <article <?php post_class( 'front-rail-card' ); ?>>
                        <a class="front-rail-card__media" href="<?php the_permalink(); ?>">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <?php the_post_thumbnail( 'medium_large' ); ?>
                            <?php else : ?>
                                <span class="front-rail-card__placeholder" aria-hidden="true">🗞️</span>
                            <?php endif; ?>
                        </a>
                        <div class="front-rail-card__body">
                            <div class="front-rail-card__meta">
                                <?php if ( $rail_kicker ) : ?>
                                    <span class="front-rail-card__badge"><?php echo esc_html( $rail_kicker ); ?></span>
                                <?php endif; ?>
                                <span class="front-rail-card__time"><?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?> <?php esc_html_e( 'önce', 'haber-sitesi' ); ?></span>
                            </div>
                            <h2 class="front-rail-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <?php $rail_views = haber_sitesi_get_post_views( get_the_ID() ); ?>
                            <div class="front-rail-card__stats">👁️ <?php echo esc_html( haber_sitesi_format_count( $rail_views ) ); ?></div>
                        </div>
                    </article>
                    <?php
                endwhile;
                wp_reset_postdata();
                ?>
            <?php else : ?>
                <p class="front-empty front-empty--rail"><?php esc_html_e( 'Manşet yanında gösterilecek haber bulunamadı.', 'haber-sitesi' ); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <div class="front-layout">
        <div class="front-layout__main">
            <section class="front-section front-section--spotlight" aria-label="<?php esc_attr_e( 'Editörden öne çıkanlar', 'haber-sitesi' ); ?>">
                <div class="front-section__header">
                    <h2 class="front-section__title"><?php esc_html_e( 'Editörden Öne Çıkanlar', 'haber-sitesi' ); ?></h2>
                    <p class="front-section__subtitle"><?php esc_html_e( 'Günün ajandasını belirleyen analiz ve dosyalar', 'haber-sitesi' ); ?></p>
                </div>
                <?php
                $spotlight_query = new WP_Query(
                    [
                        'post_type'           => 'post',
                        'posts_per_page'      => 6,
                        'ignore_sticky_posts' => 1,
                        'post_status'         => 'publish',
                        'no_found_rows'       => true,
                        'post__not_in'        => $excluded_ids,
                    ]
                );
                ?>
                <?php if ( $spotlight_query->have_posts() ) : ?>
                    <div class="front-grid front-grid--three">
                        <?php
                        while ( $spotlight_query->have_posts() ) :
                            $spotlight_query->the_post();
                            $excluded_ids[]    = get_the_ID();
                            $spot_categories    = get_the_category();
                            $spot_category_name = ! empty( $spot_categories ) ? $spot_categories[0]->name : __( 'Genel', 'haber-sitesi' );
                            ?>
                            <article <?php post_class( 'front-card front-card--spotlight' ); ?>>
                                <a class="front-card__media" href="<?php the_permalink(); ?>">
                                    <?php if ( has_post_thumbnail() ) : ?>
                                        <?php the_post_thumbnail( 'medium_large' ); ?>
                                    <?php else : ?>
                                        <span class="front-card__placeholder" aria-hidden="true">📰</span>
                                    <?php endif; ?>
                                </a>
                                <div class="front-card__body">
                                    <span class="front-card__category"><?php echo esc_html( $spot_category_name ); ?></span>
                                    <h3 class="front-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p class="front-card__excerpt"><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
                                    <?php $spotlight_views = haber_sitesi_get_post_views( get_the_ID() ); ?>
                                    <div class="front-card__meta">
                                        <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $spotlight_views ) ); ?></span>
                                        <span>⏱️ <?php echo esc_html( haber_sitesi_get_reading_time( get_the_ID() ) ); ?></span>
                                    </div>
                                </div>
                            </article>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Şu anda öne çıkan editör seçkisi bulunmuyor.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="front-section front-section--insight" aria-label="<?php esc_attr_e( 'Yorum alan haberler', 'haber-sitesi' ); ?>">
                <div class="front-section__header">
                    <h2 class="front-section__title"><?php esc_html_e( 'Gündem Nabzı', 'haber-sitesi' ); ?></h2>
                    <p class="front-section__subtitle"><?php esc_html_e( 'Okurların konuştuğu sıcak başlıklar', 'haber-sitesi' ); ?></p>
                </div>
                <?php
                $insight_query = new WP_Query(
                    [
                        'post_type'           => 'post',
                        'posts_per_page'      => 4,
                        'ignore_sticky_posts' => 1,
                        'orderby'             => 'comment_count',
                        'order'               => 'DESC',
                        'post_status'         => 'publish',
                        'no_found_rows'       => true,
                        'post__not_in'        => $excluded_ids,
                    ]
                );
                ?>
                <?php if ( $insight_query->have_posts() ) : ?>
                    <div class="front-grid front-grid--two">
                        <?php
                        while ( $insight_query->have_posts() ) :
                            $insight_query->the_post();
                            $excluded_ids[]   = get_the_ID();
                            $insight_cats     = get_the_category();
                            $insight_category = ! empty( $insight_cats ) ? $insight_cats[0]->name : __( 'Genel', 'haber-sitesi' );
                            ?>
                            <article <?php post_class( 'front-card front-card--insight' ); ?>>
                                <div class="front-card__body">
                                    <span class="front-card__category"><?php echo esc_html( $insight_category ); ?></span>
                                    <h3 class="front-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p class="front-card__excerpt"><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 26 ) ); ?></p>
                                    <div class="front-card__meta">
                                        <span>💬 <?php echo esc_html( number_format_i18n( get_comments_number() ) ); ?></span>
                                        <span>📅 <?php echo esc_html( get_the_date() ); ?></span>
                                    </div>
                                </div>
                            </article>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </div>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Henüz yorum hareketi yüksek içerik bulunmuyor.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section id="mobile-categories" class="front-section front-section--categories" aria-label="<?php esc_attr_e( 'Kategori panelleri', 'haber-sitesi' ); ?>">
                <div class="front-section__header">
                    <h2 class="front-section__title"><?php esc_html_e( 'Masadaki Başlıklar', 'haber-sitesi' ); ?></h2>
                    <p class="front-section__subtitle"><?php esc_html_e( 'Sık okunan kategorilerden seçilmiş gündemler', 'haber-sitesi' ); ?></p>
                </div>
                <?php if ( ! empty( $top_categories ) && ! is_wp_error( $top_categories ) ) : ?>
                    <div class="front-grid front-grid--three">
                        <?php
                        foreach ( $top_categories as $category ) {
                            $category_query = new WP_Query(
                                [
                                    'post_type'           => 'post',
                                    'posts_per_page'      => 1,
                                    'cat'                 => $category->term_id,
                                    'post_status'         => 'publish',
                                    'ignore_sticky_posts' => 1,
                                    'no_found_rows'       => true,
                                    'post__not_in'        => $excluded_ids,
                                ]
                            );

                            if ( $category_query->have_posts() ) {
                                while ( $category_query->have_posts() ) {
                                    $category_query->the_post();
                                    $excluded_ids[] = get_the_ID();
                                    ?>
                                    <article <?php post_class( 'front-card front-card--category' ); ?>>
                                        <div class="front-card__header">
                                            <span class="front-card__category">#<?php echo esc_html( $category->name ); ?></span>
                                            <span class="front-card__count"><?php echo esc_html( number_format_i18n( $category->count ) ); ?> <?php esc_html_e( 'haber', 'haber-sitesi' ); ?></span>
                                        </div>
                                        <h3 class="front-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                        <p class="front-card__excerpt"><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
                                        <a class="front-card__cta" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"><?php esc_html_e( 'Tüm başlıklar', 'haber-sitesi' ); ?></a>
                                    </article>
                                    <?php
                                }
                                wp_reset_postdata();
                            }
                        }
                        ?>
                    </div>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Gösterilecek kategori bulunamadı.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="front-section front-section--stream" aria-label="<?php esc_attr_e( 'Canlı haber akışı', 'haber-sitesi' ); ?>">
                <div class="front-section__header">
                    <h2 class="front-section__title"><?php esc_html_e( 'Haber Akışı', 'haber-sitesi' ); ?></h2>
                    <p class="front-section__subtitle"><?php esc_html_e( 'Dakika dakika güncellenen son haberler', 'haber-sitesi' ); ?></p>
                </div>
                <?php
                $latest_query = new WP_Query(
                    [
                        'post_type'           => 'post',
                        'posts_per_page'      => 8,
                        'ignore_sticky_posts' => 1,
                        'post_status'         => 'publish',
                        'no_found_rows'       => true,
                        'post__not_in'        => $excluded_ids,
                    ]
                );
                ?>
                <?php if ( $latest_query->have_posts() ) : ?>
                    <ol class="front-stream">
                        <?php
                        while ( $latest_query->have_posts() ) :
                            $latest_query->the_post();
                            $excluded_ids[] = get_the_ID();
                            ?>
                            <li class="front-stream__item">
                                <article <?php post_class( 'front-stream__article' ); ?>>
                                    <div class="front-stream__time"><?php echo esc_html( get_the_time() ); ?></div>
                                    <div class="front-stream__content">
                                        <h3 class="front-stream__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                        <p class="front-stream__excerpt"><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
                                        <?php $latest_views = haber_sitesi_get_post_views( get_the_ID() ); ?>
                                        <div class="front-stream__meta">
                                            <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $latest_views ) ); ?></span>
                                            <span>💬 <?php echo esc_html( number_format_i18n( get_comments_number() ) ); ?></span>
                                        </div>
                                    </div>
                                </article>
                            </li>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </ol>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Akışta görüntülenecek haber bulunmuyor.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>
        </div>

        <aside class="front-layout__aside" aria-label="<?php esc_attr_e( 'Trendler ve servisler', 'haber-sitesi' ); ?>">
            <?php
            $popular_query = new WP_Query(
                [
                    'posts_per_page'      => 5,
                    'meta_key'            => 'haber_view_count',
                    'orderby'             => 'meta_value_num',
                    'meta_type'           => 'NUMERIC',
                    'order'               => 'DESC',
                    'ignore_sticky_posts' => 1,
                    'no_found_rows'       => true,
                    'post__not_in'        => $excluded_ids,
                    'meta_query'          => [
                        'relation' => 'OR',
                        [
                            'key'     => 'haber_view_count',
                            'compare' => 'EXISTS',
                        ],
                        [
                            'key'     => 'haber_view_count',
                            'compare' => 'NOT EXISTS',
                        ],
                    ],
                ]
            );
            ?>
            <section id="mobile-most-read" class="front-side-card front-side-card--trending">
                <div class="front-side-card__header">
                    <h2 class="front-side-card__title"><?php esc_html_e( 'En Çok Okunanlar', 'haber-sitesi' ); ?></h2>
                    <span class="front-side-card__tag"><?php esc_html_e( '24 saat', 'haber-sitesi' ); ?></span>
                </div>
                <?php if ( $popular_query->have_posts() ) : ?>
                    <ol class="front-trending">
                        <?php
                        $position = 1;
                        while ( $popular_query->have_posts() ) :
                            $popular_query->the_post();
                            ?>
                            <li class="front-trending__item">
                                <span class="front-trending__index"><?php echo esc_html( $position ); ?></span>
                                <div class="front-trending__content">
                                    <a class="front-trending__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    <?php $popular_views = haber_sitesi_get_post_views( get_the_ID() ); ?>
                                    <span class="front-trending__meta">👁️ <?php echo esc_html( haber_sitesi_format_count( $popular_views ) ); ?></span>
                                </div>
                            </li>
                            <?php
                            $position++;
                        endwhile;
                        wp_reset_postdata();
                        ?>
                    </ol>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Şu anda trend veri bulunamadı.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="front-side-card front-side-card--weather" aria-label="<?php esc_attr_e( 'Hava durumu', 'haber-sitesi' ); ?>">
                <div class="front-side-card__header">
                    <h2 class="front-side-card__title"><?php esc_html_e( 'Hava Durumu', 'haber-sitesi' ); ?></h2>
                </div>
                <div class="front-weather">
                    <div class="front-weather__main">
                        <span class="front-weather__temp"><?php echo esc_html( $weather_temperature ); ?></span>
                        <div class="front-weather__details">
                            <span class="front-weather__location"><?php echo esc_html( $weather_location ); ?></span>
                            <span class="front-weather__condition"><?php echo esc_html( $weather_condition ); ?></span>
                        </div>
                    </div>
                    <ul class="front-weather__meta">
                        <li><span>💨</span><span>12 km/h</span></li>
                        <li><span>💧</span><span>%65</span></li>
                        <li><span>📊</span><span>1013 mb</span></li>
                    </ul>
                </div>
            </section>

            <section class="front-side-card front-side-card--cta">
                <div class="front-side-card__header">
                    <h2 class="front-side-card__title"><?php esc_html_e( 'Bültene Abone Ol', 'haber-sitesi' ); ?></h2>
                </div>
                <p class="front-side-card__text"><?php esc_html_e( 'Her sabah editörlerimizin seçtiği manşetleri e-posta kutuna al.', 'haber-sitesi' ); ?></p>
                <a class="front-side-card__button" href="<?php echo esc_url( wp_login_url() ); ?>"><?php esc_html_e( 'Hemen Kaydol', 'haber-sitesi' ); ?></a>
            </section>
        </aside>
    </div>

    <div class="front-load-more">
        <?php
        $posts_page = (int) get_option( 'page_for_posts' );
        $posts_url  = $posts_page ? get_permalink( $posts_page ) : get_post_type_archive_link( 'post' );
        $posts_url  = $posts_url ? $posts_url : get_home_url();
        ?>
        <a class="front-load-more__button" href="<?php echo esc_url( $posts_url ); ?>"><?php esc_html_e( 'Tüm Haberleri Gör', 'haber-sitesi' ); ?></a>
    </div>
</div>
<?php get_footer(); ?>

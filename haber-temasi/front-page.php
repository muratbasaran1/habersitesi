<?php
/**
 * Anasayfa şablonu.
 *
 * @package Haber_Sitesi
 */

global $post;

get_header();

$lead_query = new WP_Query(
    [
        'posts_per_page'      => 1,
        'ignore_sticky_posts' => false,
    ]
);

$top_query = new WP_Query(
    [
        'posts_per_page'      => 4,
        'offset'              => 1,
        'ignore_sticky_posts' => 1,
    ]
);

$popular_query = new WP_Query(
    [
        'posts_per_page'      => 5,
        'orderby'             => 'comment_count',
        'ignore_sticky_posts' => 1,
        'no_found_rows'       => true,
    ]
);

$weather_location    = get_theme_mod( 'haber_weather_location', __( 'İstanbul', 'haber-sitesi' ) );
$weather_temperature = get_theme_mod( 'haber_weather_temperature', '15°C' );
$weather_condition   = get_theme_mod( 'haber_weather_condition', __( 'Güneşli', 'haber-sitesi' ) );

?>
<div class="mobile-shell mobile-main">
    <section class="mobile-hero" aria-label="<?php esc_attr_e( 'Manşet haber', 'haber-sitesi' ); ?>">
        <?php if ( $lead_query->have_posts() ) : ?>
            <?php
            while ( $lead_query->have_posts() ) :
                $lead_query->the_post();
                $lead_category = get_the_category();
                $lead_kicker   = ! empty( $lead_category ) ? $lead_category[0]->name : '';
                ?>
                <article <?php post_class( 'mobile-hero__article' ); ?>>
                    <div class="mobile-hero__media">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a class="mobile-hero__thumb" href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'large' ); ?>
                            </a>
                        <?php else : ?>
                            <a class="mobile-hero__thumb mobile-hero__thumb--placeholder" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">📰</a>
                        <?php endif; ?>
                    </div>
                    <div class="mobile-hero__body">
                        <div class="mobile-hero__meta">
                            <?php if ( $lead_kicker ) : ?>
                                <span class="mobile-hero__badge"><?php echo esc_html( $lead_kicker ); ?></span>
                            <?php endif; ?>
                            <span class="mobile-hero__time"><?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?> <?php esc_html_e( 'önce', 'haber-sitesi' ); ?></span>
                        </div>
                        <h1 class="mobile-hero__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                        <p class="mobile-hero__excerpt"><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 36 ) ); ?></p>
                        <div class="mobile-hero__actions">
                            <span class="mobile-hero__stat">💬 <?php echo esc_html( get_comments_number() ); ?></span>
                            <span class="mobile-hero__stat">⏱️ <?php echo esc_html( haber_sitesi_get_reading_time() ); ?></span>
                            <a class="button" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Haberi Oku', 'haber-sitesi' ); ?></a>
                        </div>
                    </div>
                </article>
                <?php
            endwhile;
            wp_reset_postdata();
            ?>
        <?php else : ?>
            <p class="mobile-empty"><?php esc_html_e( 'Henüz içerik eklenmemiş.', 'haber-sitesi' ); ?></p>
        <?php endif; ?>
    </section>

    <?php if ( $top_query->have_posts() ) : ?>
        <section class="mobile-secondary" aria-label="<?php esc_attr_e( 'Günün diğer haberleri', 'haber-sitesi' ); ?>">
            <ul class="mobile-secondary__list">
                <?php
                while ( $top_query->have_posts() ) :
                    $top_query->the_post();
                    $categories = get_the_category();
                    $section    = ! empty( $categories ) ? $categories[0]->name : '';
                    ?>
                    <li class="mobile-secondary__item">
                        <article <?php post_class( 'mobile-secondary__article' ); ?>>
                            <a class="mobile-secondary__thumb" href="<?php the_permalink(); ?>">
                                <?php
                                if ( has_post_thumbnail() ) {
                                    the_post_thumbnail( 'thumbnail' );
                                } else {
                                    echo '<span class="mobile-secondary__emoji" aria-hidden="true">🗞️</span>';
                                }
                                ?>
                            </a>
                            <div class="mobile-secondary__content">
                                <div class="mobile-secondary__meta">
                                    <?php if ( $section ) : ?>
                                        <span class="mobile-secondary__badge"><?php echo esc_html( $section ); ?></span>
                                    <?php endif; ?>
                                </div>
                                <h2 class="mobile-secondary__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                <div class="mobile-secondary__footer">
                                    <span><?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?> <?php esc_html_e( 'önce', 'haber-sitesi' ); ?></span>
                                    <span>💬 <?php echo esc_html( get_comments_number() ); ?></span>
                                </div>
                            </div>
                        </article>
                    </li>
                    <?php
                endwhile;
                wp_reset_postdata();
                ?>
            </ul>
        </section>
    <?php endif; ?>

    <section class="mobile-most-read" aria-label="<?php esc_attr_e( 'En çok okunan haberler', 'haber-sitesi' ); ?>">
        <div class="mobile-most-read__card">
            <h2 class="mobile-most-read__title"><?php esc_html_e( 'En Çok Okunanlar', 'haber-sitesi' ); ?></h2>
            <?php if ( $popular_query->have_posts() ) : ?>
                <ol class="mobile-most-read__list">
                    <?php
                    $position = 1;
                    while ( $popular_query->have_posts() ) :
                        $popular_query->the_post();
                        ?>
                        <li class="mobile-most-read__item">
                            <span class="mobile-most-read__index"><?php echo esc_html( $position ); ?></span>
                            <div class="mobile-most-read__body">
                                <a class="mobile-most-read__link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                <span class="mobile-most-read__meta">💬 <?php echo esc_html( get_comments_number() ); ?></span>
                            </div>
                        </li>
                        <?php
                        $position++;
                    endwhile;
                    wp_reset_postdata();
                    ?>
                </ol>
            <?php else : ?>
                <p class="mobile-empty"><?php esc_html_e( 'Henüz öne çıkan içerik bulunmuyor.', 'haber-sitesi' ); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <section class="mobile-weather" aria-label="<?php esc_attr_e( 'Hava durumu', 'haber-sitesi' ); ?>">
        <div class="mobile-weather__card">
            <div class="mobile-weather__header">
                <h2><?php esc_html_e( 'Hava Durumu', 'haber-sitesi' ); ?></h2>
                <span class="mobile-weather__emoji" aria-hidden="true">☀️</span>
            </div>
            <div class="mobile-weather__content">
                <div class="mobile-weather__temp"><?php echo esc_html( $weather_temperature ); ?></div>
                <div class="mobile-weather__details">
                    <span class="mobile-weather__location"><?php echo esc_html( $weather_location ); ?></span>
                    <span class="mobile-weather__condition"><?php echo esc_html( $weather_condition ); ?></span>
                </div>
            </div>
            <div class="mobile-weather__meta">
                <span>💨 12 km/h</span>
                <span>💧 %65</span>
                <span>📊 1013 mb</span>
            </div>
        </div>
    </section>

    <div class="mobile-load-more">
        <?php
        $posts_page = (int) get_option( 'page_for_posts' );
        $posts_url  = $posts_page ? get_permalink( $posts_page ) : get_post_type_archive_link( 'post' );
        $posts_url  = $posts_url ? $posts_url : get_home_url();
        ?>
        <a class="mobile-load-more__button" href="<?php echo esc_url( $posts_url ); ?>"><?php esc_html_e( 'Daha Fazla Haber Yükle', 'haber-sitesi' ); ?></a>
    </div>
</div>
<?php get_footer(); ?>

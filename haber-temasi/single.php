<?php
/**
 * Tekil yazı şablonu.
 *
 * @package Haber_Sitesi
 */

get_header();

$current_post_id    = get_queried_object_id();
$sidebar_trending   = haber_sitesi_get_trending_posts( 5, $current_post_id ? [ $current_post_id ] : [] );
$sidebar_categories = haber_sitesi_get_category_overview( 6 );

$latest_items = [];

$latest_query = new WP_Query(
    [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 5,
        'ignore_sticky_posts' => 1,
        'post__not_in'        => $current_post_id ? [ $current_post_id ] : [],
        'no_found_rows'       => true,
    ]
);

if ( $latest_query->have_posts() ) {
    while ( $latest_query->have_posts() ) {
        $latest_query->the_post();
        $latest_items[] = haber_sitesi_collect_post_data( get_the_ID(), 14 );
    }
    wp_reset_postdata();
}
?>
<div class="page-progress" aria-hidden="true">
    <span
        class="page-progress__bar"
        data-progress-bar
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="0"
        aria-valuetext="0%"
    ></span>
</div>
<div class="page-screen page-screen--single">
    <div class="page-shell page-shell--single">
        <div class="page-layout page-layout--single">
            <div class="page-layout__main">
                <?php if ( have_posts() ) : ?>
                    <?php
                    while ( have_posts() ) :
                        the_post();
                        get_template_part( 'template-parts/content', 'single' );
                        ?>
                        <nav class="single-navigation" aria-label="<?php esc_attr_e( 'Haberler arası dolaşım', 'haber-sitesi' ); ?>">
                            <?php
                            the_post_navigation(
                                [
                                    'prev_text' => '<span class="single-navigation__label">' . esc_html__( 'Önceki haber', 'haber-sitesi' ) . '</span><span class="single-navigation__title">%title</span>',
                                    'next_text' => '<span class="single-navigation__label">' . esc_html__( 'Sonraki haber', 'haber-sitesi' ) . '</span><span class="single-navigation__title">%title</span>',
                                ]
                            );
                            ?>
                        </nav>
                        <?php if ( comments_open() || get_comments_number() ) : ?>
                            <div class="single-comments">
                                <?php comments_template(); ?>
                            </div>
                        <?php endif; ?>
                        <?php
                    endwhile;
                    ?>
                <?php endif; ?>
            </div>
            <aside class="page-layout__sidebar" aria-label="<?php esc_attr_e( 'Haber odası yan sütunu', 'haber-sitesi' ); ?>">
                <?php if ( ! empty( $sidebar_trending ) ) : ?>
                    <section class="page-card page-card--trending">
                        <div class="page-card__header">
                            <h2 class="page-card__title"><?php esc_html_e( 'Gündemdeki Başlıklar', 'haber-sitesi' ); ?></h2>
                            <p class="page-card__subtitle"><?php esc_html_e( 'Okurların şu an takip ettiği haberler', 'haber-sitesi' ); ?></p>
                        </div>
                        <ol class="page-card__list page-card__list--ranked">
                            <?php foreach ( $sidebar_trending as $index => $item ) : ?>
                                <li class="page-card__list-item">
                                    <span class="page-card__rank"><?php echo esc_html( $index + 1 ); ?></span>
                                    <div class="page-card__body">
                                        <a class="page-card__link" href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                                        <span class="page-card__meta">👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </section>
                <?php endif; ?>

                <?php if ( ! empty( $latest_items ) ) : ?>
                    <section class="page-card page-card--latest">
                        <div class="page-card__header">
                            <h2 class="page-card__title"><?php esc_html_e( 'Masadan Son Dakika', 'haber-sitesi' ); ?></h2>
                            <p class="page-card__subtitle"><?php esc_html_e( 'Yayınlanan en yeni hikayeler', 'haber-sitesi' ); ?></p>
                        </div>
                        <ul class="page-card__list">
                            <?php foreach ( $latest_items as $item ) : ?>
                                <li class="page-card__list-item">
                                    <a class="page-card__link" href="<?php echo esc_url( $item['permalink'] ); ?>">
                                        <span class="page-card__name"><?php echo esc_html( $item['title'] ); ?></span>
                                        <span class="page-card__meta"><?php echo esc_html( $item['time'] ); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <?php if ( ! empty( $sidebar_categories ) ) : ?>
                    <section class="page-card page-card--categories">
                        <div class="page-card__header">
                            <h2 class="page-card__title"><?php esc_html_e( 'Kapsam Alanları', 'haber-sitesi' ); ?></h2>
                            <p class="page-card__subtitle"><?php esc_html_e( 'Ajans masalarındaki dosya yoğunluğu', 'haber-sitesi' ); ?></p>
                        </div>
                        <ul class="page-card__list">
                            <?php foreach ( $sidebar_categories as $category ) : ?>
                                <li class="page-card__list-item">
                                    <a class="page-card__link" href="<?php echo esc_url( $category['link'] ); ?>">
                                        <span class="page-card__name"><?php echo esc_html( $category['name'] ); ?></span>
                                        <span class="page-card__meta"><?php echo esc_html( number_format_i18n( $category['count'] ) ); ?> <?php esc_html_e( 'haber', 'haber-sitesi' ); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endif; ?>

                <section class="page-card page-card--newsletter">
                    <div class="page-card__header">
                        <h2 class="page-card__title"><?php esc_html_e( 'Bültenimize Katılın', 'haber-sitesi' ); ?></h2>
                        <p class="page-card__subtitle"><?php esc_html_e( 'Günün son değerlendirmesini doğrudan mail kutunuza alın.', 'haber-sitesi' ); ?></p>
                    </div>
                    <form class="page-newsletter" action="<?php echo esc_url( home_url( '/bulten' ) ); ?>" method="get">
                        <label class="screen-reader-text" for="single-newsletter-email"><?php esc_html_e( 'E-posta adresiniz', 'haber-sitesi' ); ?></label>
                        <input id="single-newsletter-email" class="page-newsletter__field" type="email" name="email" placeholder="<?php echo esc_attr__( 'ornek@haber.com', 'haber-sitesi' ); ?>" required>
                        <button class="page-newsletter__button" type="submit"><?php esc_html_e( 'Gelişmeleri Gönder', 'haber-sitesi' ); ?></button>
                    </form>
                </section>
            </aside>
        </div>
    </div>
</div>
<?php get_footer(); ?>

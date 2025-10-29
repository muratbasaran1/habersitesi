<?php
/**
 * 404 sayfa şablonu.
 *
 * @package Haber_Sitesi
 */

get_header();

$trending_items    = haber_sitesi_get_trending_posts( 4 );
$category_overview = haber_sitesi_get_category_overview( 6 );
$posts_page_id     = (int) get_option( 'page_for_posts' );
$posts_page_url    = $posts_page_id ? get_permalink( $posts_page_id ) : get_post_type_archive_link( 'post' );
?>
<div class="page-screen page-screen--error">
    <div class="page-shell page-shell--error">
        <section class="error-hero" aria-labelledby="error-title">
            <span class="error-hero__code" aria-hidden="true">404</span>
            <div class="error-hero__body">
                <h1 id="error-title" class="error-hero__title"><?php esc_html_e( 'Aradığınız sayfaya ulaşılamıyor', 'haber-sitesi' ); ?></h1>
                <p class="error-hero__text"><?php esc_html_e( 'Bağlantı değişmiş veya kaldırılmış olabilir. Aşağıdaki araçlarla aradığınız haberi saniyeler içinde bulun.', 'haber-sitesi' ); ?></p>
                <form class="error-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <label class="screen-reader-text" for="error-search-field"><?php esc_html_e( 'Sitede ara', 'haber-sitesi' ); ?></label>
                    <input id="error-search-field" class="error-search__field" type="search" name="s" placeholder="<?php echo esc_attr__( 'Örneğin: ekonomi, seçim, transfer', 'haber-sitesi' ); ?>" required>
                    <button class="error-search__button" type="submit"><?php esc_html_e( 'Ara', 'haber-sitesi' ); ?></button>
                </form>
                <div class="error-actions">
                    <a class="error-actions__link" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Anasayfaya dön', 'haber-sitesi' ); ?></a>
                    <?php if ( $posts_page_url ) : ?>
                        <a class="error-actions__link" href="<?php echo esc_url( $posts_page_url ); ?>"><?php esc_html_e( 'Son dakika akışını aç', 'haber-sitesi' ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div class="error-panels">
            <?php if ( ! empty( $trending_items ) ) : ?>
                <section class="error-panel">
                    <div class="error-panel__header">
                        <h2 class="error-panel__title"><?php esc_html_e( 'Şu an çok okunanlar', 'haber-sitesi' ); ?></h2>
                        <p class="error-panel__subtitle"><?php esc_html_e( 'Manşetlere hızlıca göz atın', 'haber-sitesi' ); ?></p>
                    </div>
                    <ol class="error-panel__list error-panel__list--ranked">
                        <?php foreach ( $trending_items as $index => $item ) : ?>
                            <li class="error-panel__item">
                                <span class="error-panel__rank"><?php echo esc_html( $index + 1 ); ?></span>
                                <div class="error-panel__body">
                                    <a class="error-panel__link" href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                                    <span class="error-panel__meta"><?php echo esc_html( $item['category'] ); ?> • <?php echo esc_html( $item['time'] ); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </section>
            <?php endif; ?>

            <?php if ( ! empty( $category_overview ) ) : ?>
                <section class="error-panel">
                    <div class="error-panel__header">
                        <h2 class="error-panel__title"><?php esc_html_e( 'Kapsam Alanları', 'haber-sitesi' ); ?></h2>
                        <p class="error-panel__subtitle"><?php esc_html_e( 'Hızlı kategori kısayolları', 'haber-sitesi' ); ?></p>
                    </div>
                    <ul class="error-panel__list">
                        <?php foreach ( $category_overview as $category ) : ?>
                            <li class="error-panel__item">
                                <a class="error-panel__link" href="<?php echo esc_url( $category['link'] ); ?>">
                                    <span class="error-panel__name"><?php echo esc_html( $category['name'] ); ?></span>
                                    <span class="error-panel__meta"><?php echo esc_html( number_format_i18n( $category['count'] ) ); ?> <?php esc_html_e( 'haber', 'haber-sitesi' ); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php get_footer(); ?>

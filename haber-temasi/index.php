<?php
/**
 * Varsayılan şablon.
 *
 * @package Haber_Sitesi
 */

get_header();

global $wp_query;

$page_title         = single_post_title( '', false );
$sidebar_trending   = haber_sitesi_get_trending_posts( 5 );
$sidebar_categories = haber_sitesi_get_category_overview( 6 );
$total_posts        = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
?>
<div class="page-screen page-screen--index">
    <div class="page-shell">
        <header class="page-header-block">
            <div class="page-header-block__titles">
                <h1 class="page-header-block__title"><?php echo esc_html( $page_title ? $page_title : get_bloginfo( 'name' ) ); ?></h1>
                <div class="page-header-block__description">
                    <?php esc_html_e( 'Gün boyu güncellenen haber akışına hoş geldiniz.', 'haber-sitesi' ); ?>
                </div>
            </div>
            <div class="page-header-block__meta">
                <span class="page-badge">
                    <?php
                    printf(
                        _n( '%s içerik yayında', '%s içerik yayında', $total_posts, 'haber-sitesi' ),
                        number_format_i18n( $total_posts )
                    );
                    ?>
                </span>
            </div>
        </header>
        <div class="page-layout">
            <div class="page-layout__main">
                <?php if ( have_posts() ) : ?>
                    <div class="page-list">
                        <?php
                        while ( have_posts() ) :
                            the_post();
                            get_template_part( 'template-parts/content', 'excerpt' );
                        endwhile;
                        ?>
                    </div>
                    <nav class="pagination" aria-label="<?php esc_attr_e( 'Sayfalandırma', 'haber-sitesi' ); ?>">
                        <?php the_posts_pagination(); ?>
                    </nav>
                <?php else : ?>
                    <p class="page-empty"><?php esc_html_e( 'İçerik bulunamadı.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </div>
            <aside class="page-layout__sidebar" aria-label="<?php esc_attr_e( 'Yan sütun', 'haber-sitesi' ); ?>">
                <?php if ( ! empty( $sidebar_trending ) ) : ?>
                    <section class="page-card page-card--trending">
                        <div class="page-card__header">
                            <h2 class="page-card__title"><?php esc_html_e( 'Editörün Seçtikleri', 'haber-sitesi' ); ?></h2>
                            <p class="page-card__subtitle"><?php esc_html_e( 'Anlık olarak yükselen hikayeler', 'haber-sitesi' ); ?></p>
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

                <?php if ( ! empty( $sidebar_categories ) ) : ?>
                    <section class="page-card page-card--categories">
                        <div class="page-card__header">
                            <h2 class="page-card__title"><?php esc_html_e( 'Masadaki Dosyalar', 'haber-sitesi' ); ?></h2>
                            <p class="page-card__subtitle"><?php esc_html_e( 'Okurlarımızın takip ettiği temalar', 'haber-sitesi' ); ?></p>
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
                        <h2 class="page-card__title"><?php esc_html_e( 'Akşam Özeti', 'haber-sitesi' ); ?></h2>
                        <p class="page-card__subtitle"><?php esc_html_e( 'Günün öne çıkan 5 başlığını akşam saatlerinde gönderiyoruz.', 'haber-sitesi' ); ?></p>
                    </div>
                    <form class="page-newsletter" action="<?php echo esc_url( home_url( '/bulten' ) ); ?>" method="get">
                        <label class="screen-reader-text" for="index-newsletter-email"><?php esc_html_e( 'E-posta adresiniz', 'haber-sitesi' ); ?></label>
                        <input id="index-newsletter-email" class="page-newsletter__field" type="email" name="email" placeholder="<?php echo esc_attr__( 'ornek@haber.com', 'haber-sitesi' ); ?>" required>
                        <button class="page-newsletter__button" type="submit"><?php esc_html_e( 'Günün Özeti Gelsin', 'haber-sitesi' ); ?></button>
                    </form>
                </section>
            </aside>
        </div>
    </div>
</div>
<?php get_footer(); ?>

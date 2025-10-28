<?php
/**
 * Arama sonuçları şablonu.
 *
 * @package Haber_Sitesi
 */

get_header();
?>
<div class="mobile-shell mobile-archive">
    <?php if ( have_posts() ) : ?>
        <header class="mobile-archive__header">
            <h1 class="mobile-archive__title"><?php printf( esc_html__( '"%s" için arama sonuçları', 'haber-sitesi' ), esc_html( get_search_query() ) ); ?></h1>
        </header>
        <div class="mobile-archive__list">
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
        <p class="mobile-empty"><?php esc_html_e( 'Eşleşen sonuç bulunamadı.', 'haber-sitesi' ); ?></p>
    <?php endif; ?>
</div>
<?php get_footer(); ?>

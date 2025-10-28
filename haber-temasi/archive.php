<?php
/**
 * Arşiv sayfaları için şablon.
 *
 * @package Haber_Sitesi
 */

get_header();
?>
<div class="mobile-shell mobile-archive">
    <?php if ( have_posts() ) : ?>
        <header class="mobile-archive__header">
            <?php the_archive_title( '<h1 class="mobile-archive__title">', '</h1>' ); ?>
            <?php the_archive_description( '<p class="mobile-archive__description">', '</p>' ); ?>
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
        <p class="mobile-empty"><?php esc_html_e( 'İçerik bulunamadı.', 'haber-sitesi' ); ?></p>
    <?php endif; ?>
</div>
<?php get_footer(); ?>

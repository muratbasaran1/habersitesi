<?php
/**
 * Arama sonuçları için şablon.
 *
 * @package Haber_Sitesi
 */

get_header();
?>
<div class="container">
    <header class="section-title">
        <h1><?php printf( esc_html__( '"%s" için arama sonuçları', 'haber-sitesi' ), get_search_query() ); ?></h1>
    </header>
    <?php if ( have_posts() ) : ?>
        <div class="card-grid">
            <?php
            while ( have_posts() ) :
                the_post();
                get_template_part( 'template-parts/content', 'excerpt' );
            endwhile;
            ?>
        </div>
        <nav class="pagination">
            <?php the_posts_pagination(); ?>
        </nav>
    <?php else : ?>
        <h2><?php esc_html_e( 'Eşleşen içerik bulunamadı.', 'haber-sitesi' ); ?></h2>
    <?php endif; ?>
</div>
<?php get_footer(); ?>

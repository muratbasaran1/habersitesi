<?php
/**
 * Varsayılan şablon.
 *
 * @package Haber_Sitesi
 */

get_header();
?>
<div class="container">
    <?php if ( have_posts() ) : ?>
        <header class="section-title">
            <h1><?php single_post_title(); ?></h1>
        </header>
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
        <h2><?php esc_html_e( 'İçerik bulunamadı.', 'haber-sitesi' ); ?></h2>
    <?php endif; ?>
</div>
<?php get_footer(); ?>

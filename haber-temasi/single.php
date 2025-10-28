<?php
/**
 * Tekil yazı şablonu.
 *
 * @package Haber_Sitesi
 */

get_header();
?>
<div class="mobile-shell mobile-single">
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            get_template_part( 'template-parts/content', 'single' );
            the_post_navigation();
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }
        endwhile;
    endif;
    ?>
</div>
<?php get_footer(); ?>

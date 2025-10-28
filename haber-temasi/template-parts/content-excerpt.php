<?php
/**
 * Haber kartı listeleri için özet şablonu.
 *
 * @package Haber_Sitesi
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'card' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>" class="card-thumbnail">
            <?php the_post_thumbnail( 'medium_large' ); ?>
        </a>
    <?php endif; ?>
    <div class="card-meta">
        <span><?php echo esc_html( get_the_date() ); ?></span>
        <span><?php the_category( ', ' ); ?></span>
    </div>
    <h3 class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    <p><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
</article>

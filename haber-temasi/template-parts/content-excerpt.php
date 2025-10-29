<?php
/**
 * Haber kartı listeleri için özet şablonu.
 *
 * @package Haber_Sitesi
 */
$categories = get_the_category();
$section    = ! empty( $categories ) ? $categories[0]->name : '';
$views      = haber_sitesi_get_post_views( get_the_ID() );
$time_diff  = human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-teaser' ); ?>>
    <a class="page-teaser__media" href="<?php the_permalink(); ?>">
        <?php
        if ( has_post_thumbnail() ) {
            the_post_thumbnail( 'medium_large' );
        } else {
            echo '<span class="page-teaser__placeholder" aria-hidden="true">🗞️</span>';
        }
        ?>
    </a>
    <div class="page-teaser__body">
        <div class="page-teaser__meta">
            <?php if ( $section ) : ?>
                <span class="page-teaser__badge"><?php echo esc_html( $section ); ?></span>
            <?php endif; ?>
            <span class="page-teaser__time"><?php printf( esc_html__( '%s önce', 'haber-sitesi' ), esc_html( $time_diff ) ); ?></span>
        </div>
        <h3 class="page-teaser__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="page-teaser__excerpt"><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
        <div class="page-teaser__stats">
            <span class="page-teaser__stat">👁️ <?php echo esc_html( haber_sitesi_format_count( $views ) ); ?></span>
            <span class="page-teaser__stat">⏱️ <?php echo esc_html( haber_sitesi_get_reading_time() ); ?></span>
        </div>
    </div>
</article>

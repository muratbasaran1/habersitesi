<?php
/**
 * Haber kartı listeleri için özet şablonu.
 *
 * @package Haber_Sitesi
 */
$categories = get_the_category();
$section    = ! empty( $categories ) ? $categories[0]->name : '';
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'mobile-listing' ); ?>>
    <a class="mobile-listing__thumb" href="<?php the_permalink(); ?>">
        <?php
        if ( has_post_thumbnail() ) {
            the_post_thumbnail( 'medium' );
        } else {
            echo '<span class="mobile-listing__emoji" aria-hidden="true">📰</span>';
        }
        ?>
    </a>
    <div class="mobile-listing__body">
        <div class="mobile-listing__meta">
            <?php if ( $section ) : ?>
                <span class="mobile-listing__badge"><?php echo esc_html( $section ); ?></span>
            <?php endif; ?>
            <span class="mobile-listing__time"><?php echo esc_html( human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?> <?php esc_html_e( 'önce', 'haber-sitesi' ); ?></span>
        </div>
        <h3 class="mobile-listing__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        <p class="mobile-listing__excerpt"><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
        <div class="mobile-listing__footer">
            <?php $listing_views = haber_sitesi_get_post_views( get_the_ID() ); ?>
            <span class="mobile-listing__stat">👁️ <?php echo esc_html( haber_sitesi_format_count( $listing_views ) ); ?></span>
            <span class="mobile-listing__stat">⏱️ <?php echo esc_html( haber_sitesi_get_reading_time() ); ?></span>
        </div>
    </div>
</article>

<?php
/**
 * Statik sayfa içeriği şablonu.
 *
 * @package Haber_Sitesi
 */

$summary_excerpt = has_excerpt() ? wp_trim_words( get_the_excerpt(), 42 ) : '';
$modified_diff   = get_the_modified_time( 'U' ) !== get_the_time( 'U' ) ? human_time_diff( get_the_modified_time( 'U' ), current_time( 'timestamp' ) ) : '';
$reading_time    = haber_sitesi_get_reading_time( get_the_ID() );
$permalink       = get_permalink();
$share_title     = rawurlencode( wp_strip_all_tags( get_the_title() ) );
$share_url       = rawurlencode( $permalink );
$twitter_share   = sprintf( 'https://twitter.com/intent/tweet?url=%s&text=%s', $share_url, $share_title );
$facebook_url    = sprintf( 'https://www.facebook.com/sharer/sharer.php?u=%s', $share_url );
$whatsapp_url    = sprintf( 'https://api.whatsapp.com/send?text=%s%%20-%%20%s', $share_title, $share_url );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'page-article' ); ?>>
    <header class="page-article__header">
        <div class="page-article__eyebrow">
            <span class="page-badge page-badge--page">📁 <?php esc_html_e( 'Dosya', 'haber-sitesi' ); ?></span>
            <div class="page-article__meta">
                <span class="page-article__meta-item">📅 <?php echo esc_html( get_the_date() ); ?></span>
                <?php if ( $reading_time ) : ?>
                    <span class="page-article__meta-item">⏱️ <?php echo esc_html( $reading_time ); ?></span>
                <?php endif; ?>
                <?php if ( $modified_diff ) : ?>
                    <span class="page-article__meta-item">♻️ <?php printf( esc_html__( 'Güncellendi %s önce', 'haber-sitesi' ), esc_html( $modified_diff ) ); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <?php the_title( '<h1 class="page-article__title">', '</h1>' ); ?>
        <?php if ( $summary_excerpt ) : ?>
            <p class="page-article__lead"><?php echo esc_html( $summary_excerpt ); ?></p>
        <?php endif; ?>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
        <figure class="page-article__media">
            <?php the_post_thumbnail( 'large' ); ?>
            <?php if ( get_the_post_thumbnail_caption() ) : ?>
                <figcaption class="page-article__caption"><?php echo esc_html( get_the_post_thumbnail_caption() ); ?></figcaption>
            <?php endif; ?>
        </figure>
    <?php endif; ?>

    <div class="page-article__content single-content">
        <?php the_content(); ?>
        <?php
        wp_link_pages(
            [
                'before' => '<nav class="page-article__pagination" aria-label="' . esc_attr__( 'Sayfa gezintisi', 'haber-sitesi' ) . '">',
                'after'  => '</nav>',
            ]
        );
        ?>
    </div>

    <footer class="page-article__footer">
        <div class="page-share" aria-label="<?php esc_attr_e( 'Sayfayı paylaş', 'haber-sitesi' ); ?>">
            <span class="page-share__label"><?php esc_html_e( 'Paylaş', 'haber-sitesi' ); ?></span>
            <div class="page-share__links">
                <button
                    type="button"
                    class="page-share__link page-share__link--primary js-share-button"
                    data-share-url="<?php echo esc_url( $permalink ); ?>"
                    data-share-title="<?php echo esc_attr( wp_strip_all_tags( get_the_title() ) ); ?>"
                    aria-label="<?php echo esc_attr( sprintf( __( '“%s” sayfasını paylaş', 'haber-sitesi' ), wp_strip_all_tags( get_the_title() ) ) ); ?>"
                >
                    <span class="page-share__link-text"><?php esc_html_e( 'Cihazda Paylaş', 'haber-sitesi' ); ?></span>
                </button>
                <button
                    type="button"
                    class="page-share__link page-share__link--save js-save-button"
                    data-post-id="<?php echo esc_attr( get_the_ID() ); ?>"
                    data-label-save="<?php esc_attr_e( 'Kaydet', 'haber-sitesi' ); ?>"
                    data-label-saved="<?php esc_attr_e( 'Kaydedildi', 'haber-sitesi' ); ?>"
                    aria-pressed="false"
                    aria-label="<?php echo esc_attr( sprintf( __( '“%s” sayfasını kaydet', 'haber-sitesi' ), wp_strip_all_tags( get_the_title() ) ) ); ?>"
                >
                    <span class="page-share__link-text"><?php esc_html_e( 'Kaydet', 'haber-sitesi' ); ?></span>
                </button>
                <a class="page-share__link page-share__link--twitter" href="<?php echo esc_url( $twitter_share ); ?>" target="_blank" rel="noopener noreferrer">Twitter</a>
                <a class="page-share__link page-share__link--facebook" href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
                <a class="page-share__link page-share__link--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
            </div>
        </div>
    </footer>
</article>

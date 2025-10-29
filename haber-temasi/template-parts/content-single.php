<?php
/**
 * Tekil haber içeriği şablonu.
 *
 * @package Haber_Sitesi
 */

$categories        = get_the_category();
$primary_category  = ! empty( $categories ) ? $categories[0]->name : '';
$reading_time      = haber_sitesi_get_reading_time();
$views             = haber_sitesi_get_post_views( get_the_ID() );
$comments_total    = get_comments_number();
$summary_excerpt   = wp_trim_words( get_the_excerpt(), 48 );
$modified_diff     = get_the_modified_time( 'U' ) !== get_the_time( 'U' ) ? human_time_diff( get_the_modified_time( 'U' ), current_time( 'timestamp' ) ) : '';
$permalink         = get_permalink();
$share_url         = rawurlencode( $permalink );
$share_title       = rawurlencode( wp_strip_all_tags( get_the_title() ) );
$twitter_share     = sprintf( 'https://twitter.com/intent/tweet?url=%s&text=%s', $share_url, $share_title );
$facebook_url      = sprintf( 'https://www.facebook.com/sharer/sharer.php?u=%s', $share_url );
$whatsapp_url      = sprintf( 'https://api.whatsapp.com/send?text=%s%%20-%%20%s', $share_title, $share_url );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-article' ); ?>>
    <header class="single-article__header">
        <div class="single-article__eyebrow">
            <?php if ( $primary_category ) : ?>
                <span class="single-article__badge"><?php echo esc_html( $primary_category ); ?></span>
            <?php endif; ?>
            <span class="single-article__timestamp">📅 <?php echo esc_html( get_the_date() ); ?></span>
            <?php if ( $modified_diff ) : ?>
                <span class="single-article__timestamp single-article__timestamp--update">♻️ <?php printf( esc_html__( 'Son güncelleme %s önce', 'haber-sitesi' ), esc_html( $modified_diff ) ); ?></span>
            <?php endif; ?>
        </div>
        <?php the_title( '<h1 class="single-article__title">', '</h1>' ); ?>
        <div class="single-article__meta">
            <div class="single-article__author">
                <span class="single-article__author-icon" aria-hidden="true">✍️</span>
                <div class="single-article__author-body">
                    <span class="single-article__meta-label"><?php esc_html_e( 'Haber Merkezi', 'haber-sitesi' ); ?></span>
                    <span class="single-article__meta-value"><?php the_author(); ?></span>
                </div>
            </div>
            <ul class="single-article__stats">
                <?php if ( $reading_time ) : ?>
                    <li>⏱️ <?php echo esc_html( $reading_time ); ?></li>
                <?php endif; ?>
                <li>👁️ <?php echo esc_html( haber_sitesi_format_count( $views ) ); ?> <?php esc_html_e( 'görüntüleme', 'haber-sitesi' ); ?></li>
                <li>💬 <?php echo esc_html( number_format_i18n( $comments_total ) ); ?> <?php esc_html_e( 'yorum', 'haber-sitesi' ); ?></li>
            </ul>
        </div>
    </header>

    <?php if ( has_post_thumbnail() ) : ?>
        <figure class="single-article__media">
            <?php the_post_thumbnail( 'large' ); ?>
            <?php if ( get_the_post_thumbnail_caption() ) : ?>
                <figcaption class="single-article__caption"><?php echo esc_html( get_the_post_thumbnail_caption() ); ?></figcaption>
            <?php endif; ?>
        </figure>
    <?php endif; ?>

    <?php if ( $summary_excerpt ) : ?>
        <section class="single-article__summary" aria-label="<?php esc_attr_e( 'Haberde öne çıkanlar', 'haber-sitesi' ); ?>">
            <h2 class="single-article__summary-title"><?php esc_html_e( 'Öne Çıkanlar', 'haber-sitesi' ); ?></h2>
            <p class="single-article__summary-text"><?php echo esc_html( $summary_excerpt ); ?></p>
        </section>
    <?php endif; ?>

    <div class="single-article__body single-content">
        <?php the_content(); ?>
    </div>

    <footer class="single-article__footer">
        <div class="single-share" aria-label="<?php esc_attr_e( 'Haberi paylaş', 'haber-sitesi' ); ?>">
            <span class="single-share__label"><?php esc_html_e( 'Haberi Paylaş', 'haber-sitesi' ); ?></span>
            <div class="single-share__links">
                <button
                    type="button"
                    class="single-share__link single-share__link--primary js-share-button"
                    data-share-url="<?php echo esc_url( $permalink ); ?>"
                    data-share-title="<?php echo esc_attr( wp_strip_all_tags( get_the_title() ) ); ?>"
                    aria-label="<?php echo esc_attr( sprintf( __( '“%s” haberini paylaş', 'haber-sitesi' ), wp_strip_all_tags( get_the_title() ) ) ); ?>"
                >
                    <span class="single-share__link-text"><?php esc_html_e( 'Cihazda Paylaş', 'haber-sitesi' ); ?></span>
                </button>
                <button
                    type="button"
                    class="single-share__link single-share__link--save js-save-button"
                    data-post-id="<?php echo esc_attr( get_the_ID() ); ?>"
                    data-label-save="<?php esc_attr_e( 'Kaydet', 'haber-sitesi' ); ?>"
                    data-label-saved="<?php esc_attr_e( 'Kaydedildi', 'haber-sitesi' ); ?>"
                    aria-pressed="false"
                    aria-label="<?php echo esc_attr( sprintf( __( '“%s” haberini kaydet', 'haber-sitesi' ), wp_strip_all_tags( get_the_title() ) ) ); ?>"
                >
                    <span class="single-share__link-text"><?php esc_html_e( 'Kaydet', 'haber-sitesi' ); ?></span>
                </button>
                <a class="single-share__link single-share__link--twitter" href="<?php echo esc_url( $twitter_share ); ?>" target="_blank" rel="noopener noreferrer">Twitter</a>
                <a class="single-share__link single-share__link--facebook" href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer">Facebook</a>
                <a class="single-share__link single-share__link--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">WhatsApp</a>
            </div>
        </div>

        <?php $tags_list = get_the_tag_list( '', '' ); ?>
        <?php if ( $tags_list ) : ?>
            <div class="single-article__tags" aria-label="<?php esc_attr_e( 'Etiketler', 'haber-sitesi' ); ?>">
                <span class="single-article__tags-label"><?php esc_html_e( 'Dosya Etiketleri:', 'haber-sitesi' ); ?></span>
                <div class="single-article__tags-list"><?php echo wp_kses_post( $tags_list ); ?></div>
            </div>
        <?php endif; ?>
    </footer>

    <?php
    $related_query = haber_sitesi_get_related_posts( get_the_ID(), 3 );
    if ( $related_query->have_posts() ) :
        ?>
        <section class="single-related" aria-labelledby="single-related-title">
            <div class="single-related__header">
                <h2 id="single-related-title" class="single-related__title"><?php esc_html_e( 'İlgili Haberler', 'haber-sitesi' ); ?></h2>
                <p class="single-related__subtitle"><?php esc_html_e( 'Gündemi tamamlayan başlıklar', 'haber-sitesi' ); ?></p>
            </div>
            <div class="single-related__grid">
                <?php
                while ( $related_query->have_posts() ) :
                    $related_query->the_post();
                    ?>
                    <article <?php post_class( 'single-related__card' ); ?>>
                        <a class="single-related__media" href="<?php the_permalink(); ?>">
                            <?php
                            if ( has_post_thumbnail() ) {
                                the_post_thumbnail( 'medium_large' );
                            } else {
                                echo '<span class="single-related__placeholder" aria-hidden="true">🗞️</span>';
                            }
                            ?>
                        </a>
                        <div class="single-related__body">
                            <span class="single-related__meta"><?php echo esc_html( get_the_date() ); ?></span>
                            <h3 class="single-related__headline"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        </div>
                    </article>
                    <?php
                endwhile;
                ?>
            </div>
        </section>
        <?php
        wp_reset_postdata();
    endif;
    ?>
</article>

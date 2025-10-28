<?php
/**
 * Tekil haber içeriği şablonu.
 *
 * @package Haber_Sitesi
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-article mobile-single__article' ); ?>>
    <header class="mobile-single__header">
        <?php the_title( '<h1 class="mobile-single__title">', '</h1>' ); ?>
        <div class="mobile-single__meta">
            <span>📅 <?php echo esc_html( get_the_date() ); ?></span>
            <span>✍️ <?php the_author(); ?></span>
            <span>🗂️ <?php the_category( ', ' ); ?></span>
            <?php
            $reading_time = haber_sitesi_get_reading_time();
            if ( $reading_time ) :
                ?>
                <span>⏱️ <?php echo esc_html( $reading_time ); ?></span>
                <?php
            endif;
            ?>
            <span>👁️ <?php echo esc_html( haber_sitesi_format_count( haber_sitesi_get_post_views( get_the_ID() ) ) ); ?> <?php esc_html_e( 'görüntüleme', 'haber-sitesi' ); ?></span>
        </div>
    </header>
    <?php if ( has_post_thumbnail() ) : ?>
        <figure class="single-thumb">
            <?php the_post_thumbnail( 'large' ); ?>
        </figure>
    <?php endif; ?>
    <div class="single-content">
        <?php the_content(); ?>
    </div>
    <footer class="single-footer mobile-single__footer">
        <div class="single-share" aria-label="<?php esc_attr_e( 'Haberi paylaş', 'haber-sitesi' ); ?>">
            <span class="single-share__label"><?php esc_html_e( 'Paylaş', 'haber-sitesi' ); ?></span>
            <?php
            $permalink     = get_permalink();
            $share_url     = rawurlencode( $permalink );
            $share_title   = rawurlencode( wp_strip_all_tags( get_the_title() ) );
            $twitter_share = sprintf( 'https://twitter.com/intent/tweet?url=%s&text=%s', $share_url, $share_title );
            $facebook_url  = sprintf( 'https://www.facebook.com/sharer/sharer.php?u=%s', $share_url );
            $whatsapp_url  = sprintf( 'https://api.whatsapp.com/send?text=%s%%20-%%20%s', $share_title, $share_url );
            ?>
            <div class="single-share__links">
                <?php
                /* translators: %s: post title. */
                ?>
                <button
                    type="button"
                    class="single-share__link single-share__link--native js-share-button"
                    data-share-url="<?php echo esc_url( $permalink ); ?>"
                    data-share-title="<?php echo esc_attr( wp_strip_all_tags( get_the_title() ) ); ?>"
                    aria-label="<?php echo esc_attr( sprintf( __( '“%s” haberini paylaş', 'haber-sitesi' ), wp_strip_all_tags( get_the_title() ) ) ); ?>"
                >
                    <span class="single-share__link-text"><?php esc_html_e( 'Cihazda Paylaş', 'haber-sitesi' ); ?></span>
                </button>
                <?php
                /* translators: %s: post title. */
                ?>
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
                <a class="single-share__link single-share__link--twitter" href="<?php echo esc_url( $twitter_share ); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e( 'Twitter', 'haber-sitesi' ); ?>
                </a>
                <a class="single-share__link single-share__link--facebook" href="<?php echo esc_url( $facebook_url ); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e( 'Facebook', 'haber-sitesi' ); ?>
                </a>
                <a class="single-share__link single-share__link--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer">
                    <?php esc_html_e( 'WhatsApp', 'haber-sitesi' ); ?>
                </a>
            </div>
        </div>
        <?php
        $tags_list = get_the_tag_list( '', '' );
        if ( $tags_list ) :
            ?>
            <div class="tags" aria-label="<?php esc_attr_e( 'Etiketler', 'haber-sitesi' ); ?>">
                <?php echo wp_kses_post( $tags_list ); ?>
            </div>
            <?php
        endif;
        ?>
    </footer>
    <?php
    $related_query = haber_sitesi_get_related_posts( get_the_ID(), 3 );
    if ( $related_query->have_posts() ) :
        ?>
        <section class="related-posts" aria-labelledby="related-posts-title">
            <h2 id="related-posts-title" class="related-posts__title"><?php esc_html_e( 'İlgili Haberler', 'haber-sitesi' ); ?></h2>
            <div class="related-posts__grid">
                <?php
                while ( $related_query->have_posts() ) :
                    $related_query->the_post();
                    ?>
                    <article <?php post_class( 'related-post' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <a class="related-post__thumb" href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'medium_large' ); ?>
                            </a>
                        <?php endif; ?>
                        <div class="related-post__content">
                            <p class="card-meta"><?php echo esc_html( get_the_date() ); ?></p>
                            <h3 class="related-post__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
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

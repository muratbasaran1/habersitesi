<?php
/**
 * Site alt bilgi dosyası.
 *
 * @package Haber_Sitesi
 */
?>
    </main><!-- #primary -->
    <aside class="widget-area">
        <div class="container widgets">
            <?php if ( is_active_sidebar( 'footer-widgets' ) ) : ?>
                <?php dynamic_sidebar( 'footer-widgets' ); ?>
            <?php else : ?>
                <section class="widget">
                    <h3 class="widget-title"><?php esc_html_e( 'Hakkımızda', 'haber-sitesi' ); ?></h3>
                    <p><?php esc_html_e( 'Güncel haberleri tarafsız bir şekilde okuyucularımıza sunuyoruz.', 'haber-sitesi' ); ?></p>
                </section>
                <section class="widget">
                    <h3 class="widget-title"><?php esc_html_e( 'Bülten', 'haber-sitesi' ); ?></h3>
                    <p><?php esc_html_e( 'Haftalık öne çıkan haberler için bültenimize katılın.', 'haber-sitesi' ); ?></p>
                </section>
            <?php endif; ?>
        </div>
    </aside>
    <footer class="site-footer">
        <div class="container">
            <p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Tüm hakları saklıdır.', 'haber-sitesi' ); ?></p>
        </div>
    </footer>
    <?php wp_footer(); ?>
</body>
</html>

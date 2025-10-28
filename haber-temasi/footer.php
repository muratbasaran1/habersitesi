<?php
/**
 * Site alt bilgi dosyası.
 *
 * @package Haber_Sitesi
 */
?>
    </main><!-- #primary -->
    <nav class="mobile-bottom-nav" aria-label="<?php esc_attr_e( 'Mobil alt gezinme', 'haber-sitesi' ); ?>">
        <div class="mobile-bottom-nav__inner">
            <?php
            wp_nav_menu(
                [
                    'theme_location' => 'mobile',
                    'menu_class'     => 'mobile-bottom-nav__list',
                    'container'      => false,
                    'fallback_cb'    => 'haber_sitesi_mobile_menu_fallback',
                    'depth'          => 1,
                    'link_before'    => '<span class="mobile-bottom-nav__icon" aria-hidden="true"></span><span class="mobile-bottom-nav__label">',
                    'link_after'     => '</span>',
                ]
            );
            ?>
        </div>
    </nav>
    <footer class="site-footer">
        <div class="mobile-shell site-footer__inner">
            <div class="footer-widgets">
                <?php if ( is_active_sidebar( 'footer-widgets' ) ) : ?>
                    <?php dynamic_sidebar( 'footer-widgets' ); ?>
                <?php else : ?>
                    <section class="widget">
                        <h3 class="widget-title"><?php esc_html_e( 'Haber Merkezi', 'haber-sitesi' ); ?></h3>
                        <p><?php esc_html_e( 'Günün öne çıkan gelişmelerini mobil odaklı düzenle sunan haber temasına hoş geldiniz.', 'haber-sitesi' ); ?></p>
                    </section>
                <?php endif; ?>
            </div>
            <div class="site-footer__meta">
                <p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>.</p>
                <?php
                if ( has_nav_menu( 'secondary' ) ) {
                    wp_nav_menu( [
                        'theme_location' => 'secondary',
                        'menu_class'     => 'footer-menu',
                        'container'      => 'nav',
                        'container_class'=> 'footer-navigation',
                        'fallback_cb'    => '__return_false',
                    ] );
                }
                ?>
            </div>
        </div>
    </footer>
    <?php wp_footer(); ?>
</body>
</html>

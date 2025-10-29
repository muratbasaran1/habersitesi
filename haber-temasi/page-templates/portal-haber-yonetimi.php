<?php
/**
 * Template Name: Haber Yönetim Portalı
 * Description: Haber ekibini ve içerik akışını ön yüzde modern bir yönetim deneyimiyle sunar.
 *
 * @package Haber_Sitesi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

if ( ! is_user_logged_in() ) {
    $login_url = wp_login_url( get_permalink() );
    ?>
    <main class="haber-portal haber-portal--guard">
        <div class="haber-portal__guard-card">
            <h1 class="haber-portal__guard-title"><?php esc_html_e( 'Haber Merkezi Portalı', 'haber-sitesi' ); ?></h1>
            <p class="haber-portal__guard-text"><?php esc_html_e( 'Lütfen yönetim portalına erişmek için giriş yapın.', 'haber-sitesi' ); ?></p>
            <a class="haber-portal__guard-button" href="<?php echo esc_url( $login_url ); ?>"><?php esc_html_e( 'Giriş Yap', 'haber-sitesi' ); ?></a>
        </div>
    </main>
    <?php
    get_footer();
    return;
}

if ( ! current_user_can( 'edit_others_posts' ) ) {
    ?>
    <main class="haber-portal haber-portal--guard">
        <div class="haber-portal__guard-card">
            <h1 class="haber-portal__guard-title"><?php esc_html_e( 'Yetersiz Yetki', 'haber-sitesi' ); ?></h1>
            <p class="haber-portal__guard-text"><?php esc_html_e( 'Bu portal yalnızca haber editörleri ve üstü için ayrılmıştır.', 'haber-sitesi' ); ?></p>
            <a class="haber-portal__guard-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Siteye Dön', 'haber-sitesi' ); ?></a>
        </div>
    </main>
    <?php
    get_footer();
    return;
}

$notice_key = isset( $_GET['haber_sitesi_notice'] ) ? sanitize_key( wp_unslash( $_GET['haber_sitesi_notice'] ) ) : '';

$notice_messages = [
    'success'              => __( 'Yeni ekip üyesi başarıyla eklendi.', 'haber-sitesi' ),
    'user_exists'          => __( 'Kullanıcı adı veya e-posta zaten kullanılıyor.', 'haber-sitesi' ),
    'missing_fields'       => __( 'Lütfen tüm zorunlu alanları doldurun.', 'haber-sitesi' ),
    'error'                => __( 'İşlem sırasında bir hata oluştu.', 'haber-sitesi' ),
    'category_created'     => __( 'Kategori başarıyla oluşturuldu.', 'haber-sitesi' ),
    'category_missing_name'=> __( 'Kategori adı boş olamaz.', 'haber-sitesi' ),
    'term_exists'          => __( 'Bu isim veya slaş zaten kullanımda.', 'haber-sitesi' ),
    'invalid_term_name'    => __( 'Kategori adı geçersiz karakterler içeriyor.', 'haber-sitesi' ),
    'category_error'       => __( 'Kategori oluşturulamadı. Lütfen tekrar deneyin.', 'haber-sitesi' ),
    'live_saved'           => __( 'Canlı yayın ayarları güncellendi.', 'haber-sitesi' ),
];

$snapshot                = haber_sitesi_get_management_snapshot();
$staff_lists             = $snapshot['staff_lists'];
$role_totals             = $snapshot['role_totals'];
$total_staff             = $snapshot['total_staff'];
$pending_posts           = $snapshot['pending_posts'];
$draft_posts             = $snapshot['draft_posts'];
$scheduled_posts         = $snapshot['scheduled_posts'];
$published_today         = $snapshot['published_today'];
$total_views             = $snapshot['total_views'];
$top_view_posts          = $snapshot['top_view_posts'];
$top_categories          = $snapshot['top_categories'];
$recent_posts            = $snapshot['recent_posts'];
$activity_data           = $snapshot['activity'];
$conflict_files          = $snapshot['conflict_files'];
$activity_points         = $activity_data['points'] ?? [];
$activity_total          = $activity_data['total'] ?? 0;
$activity_average        = $activity_data['average'] ?? 0;
$activity_peak_label     = $activity_data['peak']['label'] ?? '';
$activity_peak_value     = $activity_data['peak']['value'] ?? 0;
$category_parent_select  = $snapshot['category_parent_select'];

$portal_redirect = get_permalink();
$live_settings   = haber_sitesi_get_live_center_settings();
?>

<main class="haber-portal">
    <aside class="haber-portal__sidebar">
        <div class="haber-portal__brand">
            <span class="haber-portal__brand-kicker"><?php esc_html_e( 'Profesyonel', 'haber-sitesi' ); ?></span>
            <h1 class="haber-portal__brand-title"><?php esc_html_e( 'Haber Merkezi Portalı', 'haber-sitesi' ); ?></h1>
            <p class="haber-portal__brand-desc"><?php esc_html_e( 'Ekip, içerik ve kategorileri tek ekrandan kontrol edin.', 'haber-sitesi' ); ?></p>
        </div>

        <div class="haber-portal__stats-grid">
            <div class="haber-portal__stat-card">
                <span class="haber-portal__stat-label"><?php esc_html_e( 'Toplam Ekip', 'haber-sitesi' ); ?></span>
                <span class="haber-portal__stat-value"><?php echo esc_html( number_format_i18n( $total_staff ) ); ?></span>
            </div>
            <div class="haber-portal__stat-card">
                <span class="haber-portal__stat-label"><?php esc_html_e( 'Bugün Yayınlanan', 'haber-sitesi' ); ?></span>
                <span class="haber-portal__stat-value"><?php echo esc_html( number_format_i18n( $published_today ) ); ?></span>
            </div>
            <div class="haber-portal__stat-card">
                <span class="haber-portal__stat-label"><?php esc_html_e( 'Bekleyen İnceleme', 'haber-sitesi' ); ?></span>
                <span class="haber-portal__stat-value"><?php echo esc_html( number_format_i18n( $pending_posts ) ); ?></span>
            </div>
            <div class="haber-portal__stat-card">
                <span class="haber-portal__stat-label"><?php esc_html_e( 'Toplam Okunma', 'haber-sitesi' ); ?></span>
                <span class="haber-portal__stat-value"><?php echo esc_html( number_format_i18n( $total_views ) ); ?></span>
            </div>
        </div>

        <nav class="haber-portal__quick-nav" aria-label="<?php esc_attr_e( 'Portal kısayolları', 'haber-sitesi' ); ?>">
            <button class="haber-portal__quick-link" data-target="staff"><?php esc_html_e( 'Ekip Yönetimi', 'haber-sitesi' ); ?></button>
            <button class="haber-portal__quick-link" data-target="content"><?php esc_html_e( 'İçerik Akışı', 'haber-sitesi' ); ?></button>
            <button class="haber-portal__quick-link" data-target="live"><?php esc_html_e( 'Canlı Yayın', 'haber-sitesi' ); ?></button>
            <button class="haber-portal__quick-link" data-target="categories"><?php esc_html_e( 'Kategori Kontrolü', 'haber-sitesi' ); ?></button>
            <button class="haber-portal__quick-link" data-target="activity"><?php esc_html_e( 'Performans', 'haber-sitesi' ); ?></button>
            <a class="haber-portal__quick-link haber-portal__quick-link--external" href="<?php echo esc_url( admin_url() ); ?>" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'WordPress Yönetimine Git', 'haber-sitesi' ); ?>
            </a>
        </nav>

        <div class="haber-portal__conflicts" aria-live="polite">
            <h2 class="haber-portal__section-title"><?php esc_html_e( 'Birleştirme Durumu', 'haber-sitesi' ); ?></h2>
            <?php if ( empty( $conflict_files ) ) : ?>
                <p class="haber-portal__muted"><?php esc_html_e( 'Tüm tema dosyaları temiz görünüyor.', 'haber-sitesi' ); ?></p>
            <?php else : ?>
                <p class="haber-portal__muted"><?php esc_html_e( 'Lütfen aşağıdaki dosyalardaki birleştirme işaretlerini temizleyin:', 'haber-sitesi' ); ?></p>
                <ul class="haber-portal__conflict-list">
                    <?php foreach ( $conflict_files as $conflict_file ) : ?>
                        <li><?php echo esc_html( $conflict_file ); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </aside>

    <section class="haber-portal__main" data-portal-scroll-container>
        <?php if ( $notice_key && isset( $notice_messages[ $notice_key ] ) ) : ?>
            <div class="haber-portal__notice" role="status">
                <span class="haber-portal__notice-text"><?php echo esc_html( $notice_messages[ $notice_key ] ); ?></span>
            </div>
        <?php endif; ?>

        <article id="staff" class="haber-portal__panel haber-portal__panel--accent">
            <header class="haber-portal__panel-header">
                <h2 class="haber-portal__panel-title"><?php esc_html_e( 'Ekip Yönetimi', 'haber-sitesi' ); ?></h2>
                <p class="haber-portal__panel-subtitle"><?php esc_html_e( 'Rollere göre ayrılmış ekip üyelerini görüntüleyin ve yeni profil ekleyin.', 'haber-sitesi' ); ?></p>
            </header>

            <div class="haber-portal__panel-body">
                <div class="haber-portal__roles">
                    <?php foreach ( $staff_lists as $role_key => $data ) : ?>
                        <section class="haber-portal__role">
                            <div class="haber-portal__role-header">
                                <h3 class="haber-portal__role-title"><?php echo esc_html( $data['label'] ); ?></h3>
                                <span class="haber-portal__role-count"><?php echo esc_html( number_format_i18n( $role_totals[ $role_key ] ?? 0 ) ); ?></span>
                            </div>
                            <?php if ( ! empty( $data['users'] ) ) : ?>
                                <ul class="haber-portal__role-list">
                                    <?php foreach ( $data['users'] as $user ) : ?>
                                        <li class="haber-portal__role-item">
                                            <span class="haber-portal__role-name"><?php echo esc_html( $user->display_name ); ?></span>
                                            <span class="haber-portal__role-meta"><?php echo esc_html( $user->user_email ); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <p class="haber-portal__muted"><?php esc_html_e( 'Bu rolde henüz ekip üyesi yok.', 'haber-sitesi' ); ?></p>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                </div>

                <?php if ( current_user_can( 'create_users' ) ) : ?>
                    <div class="haber-portal__form-card">
                        <h3 class="haber-portal__form-title"><?php esc_html_e( 'Hızlı Ekip Üyesi Ekle', 'haber-sitesi' ); ?></h3>
                        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="haber-portal__form">
                            <?php wp_nonce_field( 'haber_sitesi_add_staff' ); ?>
                            <input type="hidden" name="action" value="haber_sitesi_portal" />
                            <input type="hidden" name="haber_sitesi_action" value="add_staff" />
                            <input type="hidden" name="redirect_to" value="<?php echo esc_url( $portal_redirect ); ?>" />

                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-portal-login"><?php esc_html_e( 'Kullanıcı Adı', 'haber-sitesi' ); ?></label>
                                <input class="haber-portal__input" id="haber-portal-login" type="text" name="user_login" required />
                            </div>
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-portal-email"><?php esc_html_e( 'E-posta', 'haber-sitesi' ); ?></label>
                                <input class="haber-portal__input" id="haber-portal-email" type="email" name="user_email" required />
                            </div>
                            <div class="haber-portal__form-row haber-portal__form-row--split">
                                <span>
                                    <label class="haber-portal__label" for="haber-portal-first"><?php esc_html_e( 'Ad', 'haber-sitesi' ); ?></label>
                                    <input class="haber-portal__input" id="haber-portal-first" type="text" name="first_name" />
                                </span>
                                <span>
                                    <label class="haber-portal__label" for="haber-portal-last"><?php esc_html_e( 'Soyad', 'haber-sitesi' ); ?></label>
                                    <input class="haber-portal__input" id="haber-portal-last" type="text" name="last_name" />
                                </span>
                            </div>
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-portal-pass"><?php esc_html_e( 'Geçici Şifre', 'haber-sitesi' ); ?></label>
                                <input class="haber-portal__input" id="haber-portal-pass" type="password" name="user_pass" required />
                            </div>
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-portal-role"><?php esc_html_e( 'Rol', 'haber-sitesi' ); ?></label>
                                <select class="haber-portal__input" id="haber-portal-role" name="role">
                                    <option value="haber_editoru"><?php esc_html_e( 'Editör', 'haber-sitesi' ); ?></option>
                                    <option value="haber_yazari"><?php esc_html_e( 'Yazar', 'haber-sitesi' ); ?></option>
                                    <option value="haber_muhabiri"><?php esc_html_e( 'Muhabir', 'haber-sitesi' ); ?></option>
                                </select>
                            </div>
                            <div class="haber-portal__form-actions">
                                <button type="submit" class="haber-portal__button haber-portal__button--primary"><?php esc_html_e( 'Ekip Üyesi Oluştur', 'haber-sitesi' ); ?></button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </article>

        <article id="live" class="haber-portal__panel haber-portal__panel--glass">
            <header class="haber-portal__panel-header">
                <h2 class="haber-portal__panel-title"><?php esc_html_e( 'Canlı Yayın Merkezi', 'haber-sitesi' ); ?></h2>
                <p class="haber-portal__panel-subtitle"><?php esc_html_e( 'Anasayfadaki canlı yayın sahnesini manuel olarak yönetin ve embed kodunu güncelleyin.', 'haber-sitesi' ); ?></p>
            </header>
            <div class="haber-portal__panel-body">
                <div class="haber-portal__form-card">
                    <h3 class="haber-portal__form-title"><?php esc_html_e( 'Manuel canlı yayın kartı', 'haber-sitesi' ); ?></h3>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="haber-portal__form">
                        <?php wp_nonce_field( 'haber_sitesi_update_live' ); ?>
                        <input type="hidden" name="action" value="haber_sitesi_portal" />
                        <input type="hidden" name="haber_sitesi_action" value="update_live" />
                        <input type="hidden" name="redirect_to" value="<?php echo esc_url( $portal_redirect ); ?>" />

                        <div class="haber-portal__form-row">
                            <label class="haber-portal__label" for="haber-live-manual"><?php esc_html_e( 'Manuel mod', 'haber-sitesi' ); ?></label>
                            <label class="haber-portal__checkbox" for="haber-live-manual">
                                <input id="haber-live-manual" type="checkbox" name="haber_live_manual_mode" value="1" <?php checked( $live_settings['manual'] ); ?> />
                                <span><?php esc_html_e( 'Canlı yayın sahnesini aşağıdaki içerikle sabitle', 'haber-sitesi' ); ?></span>
                            </label>
                            <p class="haber-portal__muted"><?php esc_html_e( 'Manuel mod kapalıyken canlı yayın kartı otomatik olarak canlı yayın kategorisinden doldurulur.', 'haber-sitesi' ); ?></p>
                        </div>

                        <div class="haber-portal__form-row">
                            <label class="haber-portal__label" for="haber-live-title"><?php esc_html_e( 'Başlık', 'haber-sitesi' ); ?></label>
                            <input class="haber-portal__input" id="haber-live-title" type="text" name="haber_live_title" value="<?php echo esc_attr( $live_settings['title'] ); ?>" />
                        </div>

                        <div class="haber-portal__form-row">
                            <label class="haber-portal__label" for="haber-live-description"><?php esc_html_e( 'Kısa özet', 'haber-sitesi' ); ?></label>
                            <textarea class="haber-portal__textarea" id="haber-live-description" name="haber_live_description" rows="4"><?php echo esc_textarea( $live_settings['description'] ); ?></textarea>
                        </div>

                        <div class="haber-portal__form-grid">
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-live-category"><?php esc_html_e( 'Kategori etiketi', 'haber-sitesi' ); ?></label>
                                <input class="haber-portal__input" id="haber-live-category" type="text" name="haber_live_category" value="<?php echo esc_attr( $live_settings['category'] ); ?>" />
                            </div>
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-live-presenter"><?php esc_html_e( 'Sunucu / muhabir', 'haber-sitesi' ); ?></label>
                                <input class="haber-portal__input" id="haber-live-presenter" type="text" name="haber_live_presenter" value="<?php echo esc_attr( $live_settings['presenter'] ); ?>" />
                            </div>
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-live-time"><?php esc_html_e( 'Yayın saati', 'haber-sitesi' ); ?></label>
                                <input class="haber-portal__input" id="haber-live-time" type="text" name="haber_live_time" value="<?php echo esc_attr( $live_settings['time'] ); ?>" />
                            </div>
                        </div>

                        <div class="haber-portal__form-grid">
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-live-cta-label"><?php esc_html_e( 'CTA etiketi', 'haber-sitesi' ); ?></label>
                                <input class="haber-portal__input" id="haber-live-cta-label" type="text" name="haber_live_cta_label" value="<?php echo esc_attr( $live_settings['cta_label'] ); ?>" />
                            </div>
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-live-cta-url"><?php esc_html_e( 'CTA bağlantısı', 'haber-sitesi' ); ?></label>
                                <input class="haber-portal__input" id="haber-live-cta-url" type="url" name="haber_live_cta_url" value="<?php echo esc_attr( $live_settings['cta_url'] ); ?>" placeholder="https://" />
                            </div>
                        </div>

                        <div class="haber-portal__form-grid">
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-live-views"><?php esc_html_e( 'İzlenme sayısı', 'haber-sitesi' ); ?></label>
                                <input class="haber-portal__input" id="haber-live-views" type="number" min="0" step="1" name="haber_live_views" value="<?php echo esc_attr( $live_settings['views'] ); ?>" />
                            </div>
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-live-comments"><?php esc_html_e( 'Yorum sayısı', 'haber-sitesi' ); ?></label>
                                <input class="haber-portal__input" id="haber-live-comments" type="number" min="0" step="1" name="haber_live_comments" value="<?php echo esc_attr( $live_settings['comments'] ); ?>" />
                            </div>
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-live-reading"><?php esc_html_e( 'Yayın süresi / okuma', 'haber-sitesi' ); ?></label>
                                <input class="haber-portal__input" id="haber-live-reading" type="text" name="haber_live_reading_time" value="<?php echo esc_attr( $live_settings['reading_time'] ); ?>" />
                            </div>
                        </div>

                        <div class="haber-portal__form-row">
                            <label class="haber-portal__label" for="haber-live-schedule"><?php esc_html_e( 'Program başlığı', 'haber-sitesi' ); ?></label>
                            <input class="haber-portal__input" id="haber-live-schedule" type="text" name="haber_live_schedule_title" value="<?php echo esc_attr( $live_settings['schedule_title'] ); ?>" />
                        </div>

                        <div class="haber-portal__form-row">
                            <label class="haber-portal__label" for="haber-live-embed"><?php esc_html_e( 'Yerleşik yayın kodu', 'haber-sitesi' ); ?></label>
                            <textarea class="haber-portal__textarea haber-portal__textarea--code" id="haber-live-embed" name="haber_live_embed" rows="4"><?php echo esc_textarea( $live_settings['embed'] ); ?></textarea>
                            <p class="haber-portal__muted"><?php esc_html_e( 'YouTube veya özel canlı yayın iframe kodunu buraya ekleyebilirsiniz.', 'haber-sitesi' ); ?></p>
                        </div>

                        <div class="haber-portal__form-actions">
                            <button type="submit" class="haber-portal__button haber-portal__button--primary"><?php esc_html_e( 'Ayarları Kaydet', 'haber-sitesi' ); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </article>

        <article id="content" class="haber-portal__panel">
            <header class="haber-portal__panel-header">
                <h2 class="haber-portal__panel-title"><?php esc_html_e( 'İçerik Akışı', 'haber-sitesi' ); ?></h2>
                <p class="haber-portal__panel-subtitle"><?php esc_html_e( 'En güncel haberler ve durum rozetleri tek bakışta.', 'haber-sitesi' ); ?></p>
            </header>
            <div class="haber-portal__panel-body">
                <?php if ( ! empty( $recent_posts ) ) : ?>
                    <ul class="haber-portal__timeline">
                        <?php foreach ( $recent_posts as $recent_post ) :
                            $status = get_post_status_object( get_post_status( $recent_post ) );
                            ?>
                            <li class="haber-portal__timeline-item">
                                <span class="haber-portal__timeline-dot" aria-hidden="true"></span>
                                <div class="haber-portal__timeline-content">
                                    <a class="haber-portal__timeline-link" href="<?php echo esc_url( get_edit_post_link( $recent_post->ID ) ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html( get_the_title( $recent_post ) ); ?>
                                    </a>
                                    <div class="haber-portal__timeline-meta">
                                        <span><?php echo esc_html( get_post_time( get_option( 'date_format' ), false, $recent_post ) ); ?></span>
                                        <?php if ( $status ) : ?>
                                            <span class="haber-portal__badge haber-portal__badge--<?php echo esc_attr( $status->name ); ?>"><?php echo esc_html( $status->label ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p class="haber-portal__muted"><?php esc_html_e( 'Son dönemde içerik eklenmemiş.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </div>
        </article>

        <article id="categories" class="haber-portal__panel haber-portal__panel--split">
            <div class="haber-portal__panel-side">
                <header class="haber-portal__panel-header">
                    <h2 class="haber-portal__panel-title"><?php esc_html_e( 'Kategori Özeti', 'haber-sitesi' ); ?></h2>
                    <p class="haber-portal__panel-subtitle"><?php esc_html_e( 'En çok okunan kategoriler ve içerik hacmi.', 'haber-sitesi' ); ?></p>
                </header>
                <?php if ( ! empty( $top_categories ) && ! is_wp_error( $top_categories ) ) : ?>
                    <ul class="haber-portal__category-list">
                        <?php foreach ( $top_categories as $cat ) : ?>
                            <li class="haber-portal__category-item">
                                <div>
                                    <span class="haber-portal__category-name"><?php echo esc_html( $cat->name ); ?></span>
                                    <span class="haber-portal__category-meta"><?php echo esc_html( number_format_i18n( $cat->count ) ); ?> <?php esc_html_e( 'haber', 'haber-sitesi' ); ?></span>
                                </div>
                                <a class="haber-portal__badge-link" href="<?php echo esc_url( get_edit_term_link( $cat ) ); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php esc_html_e( 'Düzenle', 'haber-sitesi' ); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p class="haber-portal__muted"><?php esc_html_e( 'Henüz kategori bulunamadı.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </div>

            <?php if ( current_user_can( 'manage_categories' ) ) : ?>
                <div class="haber-portal__panel-side haber-portal__panel-side--form">
                    <h3 class="haber-portal__form-title"><?php esc_html_e( 'Yeni Kategori Oluştur', 'haber-sitesi' ); ?></h3>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="haber-portal__form">
                        <?php wp_nonce_field( 'haber_sitesi_add_category' ); ?>
                        <input type="hidden" name="action" value="haber_sitesi_portal" />
                        <input type="hidden" name="haber_sitesi_action" value="add_category" />
                        <input type="hidden" name="redirect_to" value="<?php echo esc_url( $portal_redirect ); ?>" />

                        <div class="haber-portal__form-row">
                            <label class="haber-portal__label" for="haber-portal-category-name"><?php esc_html_e( 'Kategori Adı', 'haber-sitesi' ); ?></label>
                            <input class="haber-portal__input" id="haber-portal-category-name" type="text" name="category_name" required />
                        </div>
                        <div class="haber-portal__form-row">
                            <label class="haber-portal__label" for="haber-portal-category-slug"><?php esc_html_e( 'Slaş (isteğe bağlı)', 'haber-sitesi' ); ?></label>
                            <input class="haber-portal__input" id="haber-portal-category-slug" type="text" name="category_slug" />
                        </div>
                        <?php if ( $category_parent_select ) : ?>
                            <div class="haber-portal__form-row">
                                <label class="haber-portal__label" for="haber-category-parent"><?php esc_html_e( 'Üst Kategori', 'haber-sitesi' ); ?></label>
                                <?php echo $category_parent_select; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                            </div>
                        <?php endif; ?>
                        <div class="haber-portal__form-actions">
                            <button type="submit" class="haber-portal__button haber-portal__button--secondary"><?php esc_html_e( 'Kategori Ekle', 'haber-sitesi' ); ?></button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </article>

        <article id="activity" class="haber-portal__panel haber-portal__panel--dark">
            <header class="haber-portal__panel-header">
                <h2 class="haber-portal__panel-title"><?php esc_html_e( 'Performans ve Trendler', 'haber-sitesi' ); ?></h2>
                <p class="haber-portal__panel-subtitle"><?php esc_html_e( 'Son altı ayın yayın grafiği ve öne çıkan haberler.', 'haber-sitesi' ); ?></p>
            </header>
            <div class="haber-portal__panel-body haber-portal__panel-body--grid">
                <section class="haber-portal__chart-card" data-chart-points='<?php echo wp_json_encode( $activity_points ); ?>'>
                    <div class="haber-portal__chart-heading">
                        <span class="haber-portal__chart-total"><?php echo esc_html( number_format_i18n( $activity_total ) ); ?> <?php esc_html_e( 'haber', 'haber-sitesi' ); ?></span>
                        <span class="haber-portal__chart-meta"><?php printf( esc_html__( 'Aylık ortalama %s içerik', 'haber-sitesi' ), esc_html( number_format_i18n( $activity_average ) ) ); ?></span>
                    </div>
                    <canvas class="haber-portal__chart" height="180" aria-hidden="true"></canvas>
                    <?php if ( $activity_peak_label ) : ?>
                        <p class="haber-portal__chart-peak"><?php printf( esc_html__( '%1$s ayında %2$s içerik ile zirve', 'haber-sitesi' ), esc_html( $activity_peak_label ), esc_html( number_format_i18n( $activity_peak_value ) ) ); ?></p>
                    <?php endif; ?>
                </section>

                <section class="haber-portal__top-news">
                    <h3 class="haber-portal__form-title"><?php esc_html_e( 'En Çok Okunanlar', 'haber-sitesi' ); ?></h3>
                    <?php if ( ! empty( $top_view_posts ) ) : ?>
                        <ul class="haber-portal__top-news-list">
                            <?php foreach ( $top_view_posts as $index => $post_item ) :
                                $views = get_post_meta( $post_item->ID, 'haber_view_count', true );
                                ?>
                                <li class="haber-portal__top-news-item">
                                    <span class="haber-portal__top-news-rank"><?php echo esc_html( $index + 1 ); ?></span>
                                    <div>
                                        <a class="haber-portal__top-news-link" href="<?php echo esc_url( get_edit_post_link( $post_item->ID ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( get_the_title( $post_item->ID ) ); ?></a>
                                        <span class="haber-portal__top-news-meta"><?php echo esc_html( number_format_i18n( (int) $views ) ); ?> <?php esc_html_e( 'okuma', 'haber-sitesi' ); ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p class="haber-portal__muted"><?php esc_html_e( 'Henüz okunma verisi bulunmuyor.', 'haber-sitesi' ); ?></p>
                    <?php endif; ?>
                </section>
            </div>
        </article>
    </section>

    <div class="haber-portal__command" data-portal-command hidden>
        <div class="haber-portal__command-backdrop" data-command-dismiss></div>
        <div class="haber-portal__command-dialog" role="dialog" aria-modal="true" aria-labelledby="haber-portal-command-title">
            <header class="haber-portal__command-header">
                <div>
                    <h2 id="haber-portal-command-title" class="haber-portal__command-title"><?php esc_html_e( 'Hızlı Komutlar', 'haber-sitesi' ); ?></h2>
                    <p class="haber-portal__command-subtitle"><?php esc_html_e( 'Portal içinde aramak için yazın veya oklarla gezin.', 'haber-sitesi' ); ?></p>
                </div>
                <button type="button" class="haber-portal__command-close" data-command-dismiss aria-label="<?php esc_attr_e( 'Komut paletini kapat', 'haber-sitesi' ); ?>">×</button>
            </header>
            <div class="haber-portal__command-search">
                <label class="screen-reader-text" for="haber-portal-command-input"><?php esc_html_e( 'Komut ara', 'haber-sitesi' ); ?></label>
                <input id="haber-portal-command-input" class="haber-portal__command-input" type="search" placeholder="<?php esc_attr_e( 'Ekip, içerik veya ayar ara…', 'haber-sitesi' ); ?>" autocomplete="off" spellcheck="false" />
                <span class="haber-portal__command-shortcut" aria-hidden="true">⌘K</span>
            </div>
            <ul class="haber-portal__command-list" role="listbox" aria-label="<?php esc_attr_e( 'Kullanılabilir portal komutları', 'haber-sitesi' ); ?>">
                <li>
                    <button type="button" class="haber-portal__command-item" role="option" data-command-target="staff">
                        <span class="haber-portal__command-label"><?php esc_html_e( 'Ekip yönetimine git', 'haber-sitesi' ); ?></span>
                        <span class="haber-portal__command-meta"><?php esc_html_e( 'Yazar, editör ve muhabir listesi', 'haber-sitesi' ); ?></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="haber-portal__command-item" role="option" data-command-target="content">
                        <span class="haber-portal__command-label"><?php esc_html_e( 'İçerik akışını aç', 'haber-sitesi' ); ?></span>
                        <span class="haber-portal__command-meta"><?php esc_html_e( 'Güncel dosyalar ve yayın sırası', 'haber-sitesi' ); ?></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="haber-portal__command-item" role="option" data-command-target="live">
                        <span class="haber-portal__command-label"><?php esc_html_e( 'Canlı yayın merkezine git', 'haber-sitesi' ); ?></span>
                        <span class="haber-portal__command-meta"><?php esc_html_e( 'Yayın kartları ve stüdyo programı', 'haber-sitesi' ); ?></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="haber-portal__command-item" role="option" data-command-target="categories">
                        <span class="haber-portal__command-label"><?php esc_html_e( 'Kategori formunu aç', 'haber-sitesi' ); ?></span>
                        <span class="haber-portal__command-meta"><?php esc_html_e( 'Yeni kategori oluşturma ekranı', 'haber-sitesi' ); ?></span>
                    </button>
                </li>
                <li>
                    <button type="button" class="haber-portal__command-item" role="option" data-command-target="activity">
                        <span class="haber-portal__command-label"><?php esc_html_e( 'Performans kartlarına git', 'haber-sitesi' ); ?></span>
                        <span class="haber-portal__command-meta"><?php esc_html_e( 'Yayın grafiği ve trend özetleri', 'haber-sitesi' ); ?></span>
                    </button>
                </li>
                <li>
                    <a class="haber-portal__command-item haber-portal__command-item--link" role="option" href="<?php echo esc_url( admin_url() ); ?>" target="_blank" rel="noopener noreferrer" data-command-link>
                        <span class="haber-portal__command-label"><?php esc_html_e( 'WordPress yönetimine geç', 'haber-sitesi' ); ?></span>
                        <span class="haber-portal__command-meta"><?php esc_html_e( 'Tam panel yeni sekmede açılır', 'haber-sitesi' ); ?></span>
                    </a>
                </li>
            </ul>
            <footer class="haber-portal__command-footer">
                <p class="haber-portal__command-hint"><?php esc_html_e( 'Komut paletini açmak için Ctrl+K ya da ⌘K kısayolunu kullanabilirsiniz.', 'haber-sitesi' ); ?></p>
            </footer>
        </div>
    </div>
</main>

<?php
get_footer();

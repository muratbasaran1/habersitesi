<?php
/**
 * Tema için özel yönetim paneli tanımları.
 *
 * @package Haber_Sitesi
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Haber ekibi rollerini kaydeder.
 */
function haber_sitesi_register_staff_roles() {
    $roles = [
        'haber_muhabiri' => [
            'name'         => __( 'Muhabir', 'haber-sitesi' ),
            'capabilities' => get_role( 'contributor' ) ? get_role( 'contributor' )->capabilities : [],
        ],
        'haber_yazari'   => [
            'name'         => __( 'Yazar', 'haber-sitesi' ),
            'capabilities' => get_role( 'author' ) ? get_role( 'author' )->capabilities : [],
        ],
        'haber_editoru'  => [
            'name'         => __( 'Editör', 'haber-sitesi' ),
            'capabilities' => get_role( 'editor' ) ? get_role( 'editor' )->capabilities : [],
        ],
    ];

    foreach ( $roles as $role_key => $role_data ) {
        if ( ! get_role( $role_key ) ) {
            add_role( $role_key, $role_data['name'], $role_data['capabilities'] );
        }
    }
}
add_action( 'after_setup_theme', 'haber_sitesi_register_staff_roles' );

/**
 * Yönetim menüsüne Haber Yönetimi sayfasını ekler.
 */
function haber_sitesi_register_admin_page() {
    $hook = add_menu_page(
        __( 'Haber Yönetimi', 'haber-sitesi' ),
        __( 'Haber Yönetimi', 'haber-sitesi' ),
        'edit_others_posts',
        'haber-sitesi-staff',
        'haber_sitesi_render_admin_page',
        'dashicons-microphone',
        3
    );

    add_action( 'load-' . $hook, 'haber_sitesi_handle_admin_actions' );
}
add_action( 'admin_menu', 'haber_sitesi_register_admin_page' );

/**
 * Yönetim paneli stil dosyalarını yükler.
 *
 * @param string $hook_suffix Mevcut yönetim sayfası.
 */
function haber_sitesi_enqueue_admin_assets( $hook_suffix ) {
    if ( 'toplevel_page_haber-sitesi-staff' !== $hook_suffix ) {
        return;
    }

    $version = wp_get_theme()->get( 'Version' );

    wp_enqueue_style(
        'haber-sitesi-admin',
        get_template_directory_uri() . '/assets/css/admin.css',
        [],
        $version
    );

    wp_enqueue_script(
        'haber-sitesi-admin',
        get_template_directory_uri() . '/assets/js/admin.js',
        [],
        $version,
        true
    );
}
add_action( 'admin_enqueue_scripts', 'haber_sitesi_enqueue_admin_assets' );

/**
 * Yönetim panelindeki işlemleri yönetir.
 */
function haber_sitesi_handle_admin_actions() {
    if ( ! current_user_can( 'create_users' ) ) {
        return;
    }

    if ( isset( $_POST['haber_sitesi_action'] ) && 'add_staff' === $_POST['haber_sitesi_action'] ) {
        check_admin_referer( 'haber_sitesi_add_staff' );

        $redirect_url = add_query_arg( 'page', 'haber-sitesi-staff', admin_url( 'admin.php' ) );

        $first_name = sanitize_text_field( wp_unslash( $_POST['first_name'] ?? '' ) );
        $last_name  = sanitize_text_field( wp_unslash( $_POST['last_name'] ?? '' ) );

        $user_data = [
            'user_login'   => sanitize_user( wp_unslash( $_POST['user_login'] ?? '' ) ),
            'user_email'   => sanitize_email( wp_unslash( $_POST['user_email'] ?? '' ) ),
            'first_name'   => $first_name,
            'last_name'    => $last_name,
            'display_name' => trim( $first_name . ' ' . $last_name ),
            'user_pass'    => wp_unslash( $_POST['user_pass'] ?? '' ),
            'role'         => sanitize_key( wp_unslash( $_POST['role'] ?? '' ) ),
        ];

        if ( empty( $user_data['display_name'] ) ) {
            $user_data['display_name'] = $user_data['user_login'];
        }

        if ( empty( $user_data['user_login'] ) || empty( $user_data['user_email'] ) || empty( $user_data['user_pass'] ) ) {
            wp_safe_redirect( add_query_arg( 'haber_sitesi_notice', 'missing_fields', $redirect_url ) );
            exit;
        }

        if ( username_exists( $user_data['user_login'] ) || email_exists( $user_data['user_email'] ) ) {
            wp_safe_redirect( add_query_arg( 'haber_sitesi_notice', 'user_exists', $redirect_url ) );
            exit;
        }

        $allowed_roles = [ 'haber_muhabiri', 'haber_yazari', 'haber_editoru' ];

        if ( ! in_array( $user_data['role'], $allowed_roles, true ) ) {
            $user_data['role'] = 'haber_yazari';
        }

        $user_id = wp_insert_user( $user_data );

        if ( is_wp_error( $user_id ) ) {
            wp_safe_redirect( add_query_arg( 'haber_sitesi_notice', 'error', $redirect_url ) );
            exit;
        }

        wp_safe_redirect( add_query_arg( 'haber_sitesi_notice', 'success', $redirect_url ) );
        exit;
    }
}

/**
 * Yönetim sayfasını render eder.
 */
function haber_sitesi_render_admin_page() {
    if ( ! current_user_can( 'edit_others_posts' ) ) {
        wp_die( esc_html__( 'Bu sayfayı görüntüleme yetkiniz yok.', 'haber-sitesi' ) );
    }

    $notice_key = isset( $_GET['haber_sitesi_notice'] ) ? sanitize_key( wp_unslash( $_GET['haber_sitesi_notice'] ) ) : '';

    $notices = [
        'success'        => [ 'class' => 'updated', 'message' => __( 'Yeni ekip üyesi başarıyla oluşturuldu.', 'haber-sitesi' ) ],
        'user_exists'    => [ 'class' => 'error', 'message' => __( 'Kullanıcı adı veya e-posta zaten kayıtlı.', 'haber-sitesi' ) ],
        'missing_fields' => [ 'class' => 'error', 'message' => __( 'Lütfen tüm zorunlu alanları doldurun.', 'haber-sitesi' ) ],
        'error'          => [ 'class' => 'error', 'message' => __( 'Kullanıcı oluşturulurken bir hata oluştu.', 'haber-sitesi' ) ],
    ];

    $staff_roles = [
        'haber_editoru'  => __( 'Editörler', 'haber-sitesi' ),
        'haber_yazari'   => __( 'Yazarlar', 'haber-sitesi' ),
        'haber_muhabiri' => __( 'Muhabirler', 'haber-sitesi' ),
    ];

    $staff_lists = [];
    foreach ( $staff_roles as $role_key => $label ) {
        $query = new WP_User_Query(
            [
                'role'    => $role_key,
                'orderby' => 'display_name',
                'order'   => 'ASC',
            ]
        );
        $staff_lists[ $role_key ] = [
            'label' => $label,
            'users' => $query->get_results(),
        ];
    }

    $role_totals = [];
    $total_staff = 0;

    foreach ( $staff_lists as $role_key => $data ) {
        $role_totals[ $role_key ] = is_array( $data['users'] ) ? count( $data['users'] ) : 0;
        $total_staff             += $role_totals[ $role_key ];
    }

    $post_counts     = wp_count_posts( 'post' );
    $pending_posts   = isset( $post_counts->pending ) ? (int) $post_counts->pending : 0;
    $draft_posts     = isset( $post_counts->draft ) ? (int) $post_counts->draft : 0;
    $scheduled_posts = isset( $post_counts->future ) ? (int) $post_counts->future : 0;

    $published_today = 0;
    $today_query     = new WP_Query(
        [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'date_query'     => [
                [
                    'after'     => 'midnight',
                    'inclusive' => true,
                ],
            ],
            'fields'         => 'ids',
            'posts_per_page' => 1,
        ]
    );

    if ( $today_query->have_posts() ) {
        $published_today = (int) $today_query->found_posts;
    }

    wp_reset_postdata();
    ?>
    <div class="wrap haber-sitesi-admin">
        <h1><?php echo esc_html__( 'Haber Yönetim Paneli', 'haber-sitesi' ); ?></h1>
        <p class="description">
            <?php echo esc_html__( 'Muhabir, yazar ve editör ekibini bu panel üzerinden yönetin.', 'haber-sitesi' ); ?>
        </p>

        <?php if ( $notice_key && isset( $notices[ $notice_key ] ) ) : ?>
            <div class="notice <?php echo esc_attr( $notices[ $notice_key ]['class'] ); ?> is-dismissible">
                <p><?php echo esc_html( $notices[ $notice_key ]['message'] ); ?></p>
            </div>
        <?php endif; ?>

        <div class="haber-sitesi-admin__card haber-sitesi-admin__card--summary">
            <h2><?php echo esc_html__( 'Ekip Özeti', 'haber-sitesi' ); ?></h2>
            <ul class="haber-sitesi-admin__metrics">
                <li>
                    <span class="haber-sitesi-admin__metric-label"><?php esc_html_e( 'Toplam Ekip Üyesi', 'haber-sitesi' ); ?></span>
                    <span class="haber-sitesi-admin__metric-value"><?php echo esc_html( number_format_i18n( $total_staff ) ); ?></span>
                </li>
                <li>
                    <span class="haber-sitesi-admin__metric-label"><?php esc_html_e( 'Bugün Yayınlanan Haber', 'haber-sitesi' ); ?></span>
                    <span class="haber-sitesi-admin__metric-value"><?php echo esc_html( number_format_i18n( $published_today ) ); ?></span>
                </li>
                <li>
                    <span class="haber-sitesi-admin__metric-label"><?php esc_html_e( 'Bekleyen İnceleme', 'haber-sitesi' ); ?></span>
                    <span class="haber-sitesi-admin__metric-value"><?php echo esc_html( number_format_i18n( $pending_posts ) ); ?></span>
                </li>
                <li>
                    <span class="haber-sitesi-admin__metric-label"><?php esc_html_e( 'Taslak Haberler', 'haber-sitesi' ); ?></span>
                    <span class="haber-sitesi-admin__metric-value"><?php echo esc_html( number_format_i18n( $draft_posts ) ); ?></span>
                </li>
                <li>
                    <span class="haber-sitesi-admin__metric-label"><?php esc_html_e( 'Zamanlanmış Yayın', 'haber-sitesi' ); ?></span>
                    <span class="haber-sitesi-admin__metric-value"><?php echo esc_html( number_format_i18n( $scheduled_posts ) ); ?></span>
                </li>
            </ul>
            <div class="haber-sitesi-admin__metric-tags" role="list">
                <?php foreach ( $staff_roles as $role_key => $label ) : ?>
                    <span role="listitem" class="haber-sitesi-tag">
                        <?php
                        printf(
                            /* translators: 1: role label, 2: staff count */
                            esc_html__( '%1$s: %2$s', 'haber-sitesi' ),
                            esc_html( $label ),
                            esc_html( number_format_i18n( $role_totals[ $role_key ] ?? 0 ) )
                        );
                        ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ( current_user_can( 'create_users' ) ) : ?>
        <div class="haber-sitesi-admin__card">
            <h2><?php echo esc_html__( 'Yeni Ekip Üyesi Oluştur', 'haber-sitesi' ); ?></h2>
            <form method="post">
                <?php wp_nonce_field( 'haber_sitesi_add_staff' ); ?>
                <input type="hidden" name="haber_sitesi_action" value="add_staff" />

                <div class="haber-sitesi-grid">
                    <p>
                        <label for="haber-user-login" class="haber-sitesi-label"><?php esc_html_e( 'Kullanıcı Adı', 'haber-sitesi' ); ?></label>
                        <input type="text" id="haber-user-login" name="user_login" class="regular-text" required />
                    </p>
                    <p>
                        <label for="haber-user-email" class="haber-sitesi-label"><?php esc_html_e( 'E-posta', 'haber-sitesi' ); ?></label>
                        <input type="email" id="haber-user-email" name="user_email" class="regular-text" required />
                    </p>
                    <p>
                        <label for="haber-first-name" class="haber-sitesi-label"><?php esc_html_e( 'Ad', 'haber-sitesi' ); ?></label>
                        <input type="text" id="haber-first-name" name="first_name" class="regular-text" />
                    </p>
                    <p>
                        <label for="haber-last-name" class="haber-sitesi-label"><?php esc_html_e( 'Soyad', 'haber-sitesi' ); ?></label>
                        <input type="text" id="haber-last-name" name="last_name" class="regular-text" />
                    </p>
                    <p>
                        <label for="haber-role" class="haber-sitesi-label"><?php esc_html_e( 'Rol', 'haber-sitesi' ); ?></label>
                        <select id="haber-role" name="role">
                            <option value="haber_muhabiri"><?php esc_html_e( 'Muhabir', 'haber-sitesi' ); ?></option>
                            <option value="haber_yazari" selected><?php esc_html_e( 'Yazar', 'haber-sitesi' ); ?></option>
                            <option value="haber_editoru"><?php esc_html_e( 'Editör', 'haber-sitesi' ); ?></option>
                        </select>
                    </p>
                    <p>
                        <label for="haber-user-pass" class="haber-sitesi-label"><?php esc_html_e( 'Şifre', 'haber-sitesi' ); ?></label>
                        <input type="password" id="haber-user-pass" name="user_pass" class="regular-text" required />
                    </p>
                </div>

                <?php submit_button( __( 'Ekip Üyesi Ekle', 'haber-sitesi' ) ); ?>
            </form>
        </div>
        <?php endif; ?>

        <div class="haber-sitesi-admin__card">
            <h2><?php echo esc_html__( 'Ekip Listesi', 'haber-sitesi' ); ?></h2>
            <div class="haber-sitesi-admin__controls" role="region" aria-label="<?php esc_attr_e( 'Ekip filtreleri', 'haber-sitesi' ); ?>">
                <div class="haber-sitesi-admin__control">
                    <label for="haber-sitesi-staff-search" class="haber-sitesi-label"><?php esc_html_e( 'Ekipte Ara', 'haber-sitesi' ); ?></label>
                    <input type="search" id="haber-sitesi-staff-search" class="regular-text" placeholder="<?php esc_attr_e( 'İsim, kullanıcı adı veya e-posta', 'haber-sitesi' ); ?>" data-staff-search />
                </div>
                <div class="haber-sitesi-admin__control haber-sitesi-role-filter" role="group" aria-label="<?php esc_attr_e( 'Rol filtreleri', 'haber-sitesi' ); ?>">
                    <button type="button" class="button button-secondary is-active" data-role="all"><?php esc_html_e( 'Tümü', 'haber-sitesi' ); ?></button>
                    <?php foreach ( $staff_roles as $role_key => $label ) : ?>
                        <button type="button" class="button button-secondary" data-role="<?php echo esc_attr( $role_key ); ?>"><?php echo esc_html( $label ); ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <p
                class="haber-sitesi-admin__results"
                aria-live="polite"
                data-staff-results
                data-template-singular="<?php echo esc_attr__( '%s ekip üyesi listeleniyor.', 'haber-sitesi' ); ?>"
                data-template-plural="<?php echo esc_attr__( '%s ekip üyesi listeleniyor.', 'haber-sitesi' ); ?>"
            >
                <?php
                $results_text = _n( '%s ekip üyesi listeleniyor.', '%s ekip üyesi listeleniyor.', absint( $total_staff ), 'haber-sitesi' );
                printf(
                    /* translators: %s: filtered staff count. */
                    esc_html( $results_text ),
                    esc_html( number_format_i18n( $total_staff ) )
                );
                ?>
            </p>
            <div class="haber-sitesi-grid haber-sitesi-grid--columns" data-staff-container>
                <?php foreach ( $staff_lists as $role_key => $data ) : ?>
                    <section class="haber-sitesi-admin__section" data-role-section="<?php echo esc_attr( $role_key ); ?>">
                        <header class="haber-sitesi-admin__section-header">
                            <h3><?php echo esc_html( $data['label'] ); ?></h3>
                            <span class="haber-sitesi-badge" data-role-count="<?php echo esc_attr( $role_key ); ?>"><?php echo esc_html( number_format_i18n( $role_totals[ $role_key ] ?? 0 ) ); ?></span>
                        </header>
                        <?php if ( ! empty( $data['users'] ) ) : ?>
                            <ul class="haber-sitesi-admin__list" data-role-list="<?php echo esc_attr( $role_key ); ?>">
                                <?php foreach ( $data['users'] as $user ) :
                                    $display_name = $user->display_name ? $user->display_name : $user->user_login;
                                    $search_value = $display_name . ' ' . $user->user_email . ' ' . $user->user_login;
                                    $search_value = remove_accents( wp_strip_all_tags( $search_value ) );
                                    if ( function_exists( 'mb_strtolower' ) ) {
                                        $search_value = mb_strtolower( $search_value, 'UTF-8' );
                                    } else {
                                        $search_value = strtolower( $search_value );
                                    }
                                    ?>
                                    <li data-role="<?php echo esc_attr( $role_key ); ?>" data-search="<?php echo esc_attr( $search_value ); ?>">
                                        <strong><?php echo esc_html( $display_name ); ?></strong>
                                        <span><?php echo esc_html( $user->user_email ); ?></span>
                                        <a class="haber-sitesi-admin__link" href="<?php echo esc_url( get_edit_user_link( $user->ID ) ); ?>">
                                            <?php esc_html_e( 'Profili Düzenle', 'haber-sitesi' ); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <p class="haber-sitesi-admin__empty" hidden><?php esc_html_e( 'Filtrelere uygun ekip üyesi bulunamadı.', 'haber-sitesi' ); ?></p>
                        <?php else : ?>
                            <p class="haber-sitesi-admin__empty"><?php esc_html_e( 'Henüz ekip üyesi bulunmuyor.', 'haber-sitesi' ); ?></p>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}

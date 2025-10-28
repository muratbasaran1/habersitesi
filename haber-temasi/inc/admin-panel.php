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
    } elseif ( isset( $_POST['haber_sitesi_action'] ) && 'add_category' === $_POST['haber_sitesi_action'] ) {
        if ( ! current_user_can( 'manage_categories' ) ) {
            return;
        }

        check_admin_referer( 'haber_sitesi_add_category' );

        $redirect_url = add_query_arg( 'page', 'haber-sitesi-staff', admin_url( 'admin.php' ) );

        $category_name   = sanitize_text_field( wp_unslash( $_POST['category_name'] ?? '' ) );
        $category_slug   = sanitize_title( wp_unslash( $_POST['category_slug'] ?? '' ) );
        $category_parent = isset( $_POST['category_parent'] ) ? absint( $_POST['category_parent'] ) : 0;

        if ( empty( $category_name ) ) {
            wp_safe_redirect( add_query_arg( 'haber_sitesi_notice', 'category_missing_name', $redirect_url ) );
            exit;
        }

        $args = [];

        if ( ! empty( $category_slug ) ) {
            $args['slug'] = $category_slug;
        }

        if ( $category_parent > 0 ) {
            $args['parent'] = $category_parent;
        }

        $result = wp_insert_term( $category_name, 'category', $args );

        if ( is_wp_error( $result ) ) {
            $code = $result->get_error_code();
            $code = $code ? sanitize_key( $code ) : 'category_error';

            if ( ! in_array( $code, [ 'term_exists', 'invalid_term_name' ], true ) ) {
                $code = 'category_error';
            }

            wp_safe_redirect( add_query_arg( 'haber_sitesi_notice', $code, $redirect_url ) );
            exit;
        }

        wp_safe_redirect( add_query_arg( 'haber_sitesi_notice', 'category_created', $redirect_url ) );
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
        'category_created'      => [ 'class' => 'updated', 'message' => __( 'Yeni kategori başarıyla oluşturuldu.', 'haber-sitesi' ) ],
        'category_missing_name' => [ 'class' => 'error', 'message' => __( 'Kategori adı boş bırakılamaz.', 'haber-sitesi' ) ],
        'term_exists'           => [ 'class' => 'error', 'message' => __( 'Bu isim veya slaş ile eşleşen bir kategori zaten var.', 'haber-sitesi' ) ],
        'invalid_term_name'     => [ 'class' => 'error', 'message' => __( 'Kategori adı geçersiz karakterler içeriyor.', 'haber-sitesi' ) ],
        'category_error'        => [ 'class' => 'error', 'message' => __( 'Kategori oluşturulurken bir hata oluştu.', 'haber-sitesi' ) ],
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
    $top_categories = get_terms(
        [
            'taxonomy'   => 'category',
            'hide_empty' => false,
            'orderby'    => 'count',
            'order'      => 'DESC',
            'number'     => 6,
        ]
    );

    $category_parent_select = '';
    if ( current_user_can( 'manage_categories' ) ) {
        $category_parent_select = wp_dropdown_categories(
            [
                'taxonomy'          => 'category',
                'hide_empty'        => false,
                'name'              => 'category_parent',
                'id'                => 'haber-category-parent',
                'orderby'           => 'name',
                'hierarchical'      => true,
                'show_option_none'  => __( 'Ana kategori yok', 'haber-sitesi' ),
                'option_none_value' => 0,
                'echo'              => false,
            ]
        );
    }

    $recent_posts = get_posts(
        [
            'post_type'      => 'post',
            'post_status'    => [ 'publish', 'pending', 'draft', 'future' ],
            'numberposts'    => 8,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'suppress_filters' => false,
        ]
    );

    ?>
    <div class="wrap haber-sitesi-admin">
        <h1><?php echo esc_html__( 'Haber Yönetim Paneli', 'haber-sitesi' ); ?></h1>
        <p class="description">
            <?php echo esc_html__( 'Muhabir, yazar ve editör ekibini ve haber içerik akışını bu panel üzerinden yönetin.', 'haber-sitesi' ); ?>
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

        <div class="haber-sitesi-admin__card haber-sitesi-admin__card--actions">
            <h2><?php echo esc_html__( 'Site Yönetimi Kısayolları', 'haber-sitesi' ); ?></h2>
            <p class="haber-sitesi-admin__intro"><?php echo esc_html__( 'Yayın akışını hızlandırmak için sık kullanılan içerik ve ayar ekranlarına tek tıkla ulaşın.', 'haber-sitesi' ); ?></p>
            <div class="haber-sitesi-quick-actions">
                <a class="button button-primary" href="<?php echo esc_url( admin_url( 'post-new.php' ) ); ?>"><?php esc_html_e( 'Yeni Haber Oluştur', 'haber-sitesi' ); ?></a>
                <a class="button" href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>"><?php esc_html_e( 'Tüm Haberler', 'haber-sitesi' ); ?></a>
                <a class="button" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) ); ?>"><?php esc_html_e( 'Kategorileri Yönet', 'haber-sitesi' ); ?></a>
                <a class="button" href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=post_tag' ) ); ?>"><?php esc_html_e( 'Etiketleri Yönet', 'haber-sitesi' ); ?></a>
                <a class="button" href="<?php echo esc_url( admin_url( 'upload.php' ) ); ?>"><?php esc_html_e( 'Medya Kütüphanesi', 'haber-sitesi' ); ?></a>
                <a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Menü Ayarları', 'haber-sitesi' ); ?></a>
                <a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Özelleştiriciyi Aç', 'haber-sitesi' ); ?></a>
                <a class="button" href="<?php echo esc_url( admin_url( 'options-general.php' ) ); ?>"><?php esc_html_e( 'Genel Ayarlar', 'haber-sitesi' ); ?></a>
            </div>
        </div>

        <?php if ( current_user_can( 'manage_categories' ) ) : ?>
        <div class="haber-sitesi-admin__card haber-sitesi-admin__card--split">
            <h2><?php echo esc_html__( 'Kategori Yönetimi', 'haber-sitesi' ); ?></h2>
            <div class="haber-sitesi-grid haber-sitesi-grid--equal">
                <div>
                    <h3 class="haber-sitesi-admin__subheading"><?php echo esc_html__( 'Yeni Kategori Oluştur', 'haber-sitesi' ); ?></h3>
                    <form method="post" class="haber-sitesi-form">
                        <?php wp_nonce_field( 'haber_sitesi_add_category' ); ?>
                        <input type="hidden" name="haber_sitesi_action" value="add_category" />
                        <p>
                            <label for="haber-category-name" class="haber-sitesi-label"><?php esc_html_e( 'Kategori Adı', 'haber-sitesi' ); ?></label>
                            <input type="text" id="haber-category-name" name="category_name" class="regular-text" required />
                        </p>
                        <p>
                            <label for="haber-category-slug" class="haber-sitesi-label"><?php esc_html_e( 'Kısa İsim (Slug)', 'haber-sitesi' ); ?></label>
                            <input type="text" id="haber-category-slug" name="category_slug" class="regular-text" placeholder="<?php esc_attr_e( 'Opsiyonel', 'haber-sitesi' ); ?>" />
                        </p>
                        <p>
                            <label for="haber-category-parent" class="haber-sitesi-label"><?php esc_html_e( 'Üst Kategori', 'haber-sitesi' ); ?></label>
                            <select id="haber-category-parent" name="category_parent" class="regular-text">
                                <?php
                                if ( $category_parent_select ) {
                                    echo wp_kses( $category_parent_select, [
                                        'option' => [
                                            'class'    => true,
                                            'value'    => true,
                                            'selected' => true,
                                        ],
                                    ] );
                                } else {
                                    printf( '<option value="0">%s</option>', esc_html__( 'Ana kategori yok', 'haber-sitesi' ) );
                                }
                                ?>
                            </select>
                        </p>
                        <?php submit_button( __( 'Kategori Oluştur', 'haber-sitesi' ) ); ?>
                    </form>
                </div>
                <div>
                    <h3 class="haber-sitesi-admin__subheading"><?php echo esc_html__( 'En Çok Kullanılan Kategoriler', 'haber-sitesi' ); ?></h3>
                    <?php if ( ! empty( $top_categories ) && ! is_wp_error( $top_categories ) ) : ?>
                        <ul class="haber-sitesi-admin__category-list">
                            <?php foreach ( $top_categories as $term ) : ?>
                                <li>
                                    <div>
                                        <strong><?php echo esc_html( $term->name ); ?></strong>
                                        <span><?php printf( /* translators: %s: post count */ esc_html__( '%s içerik', 'haber-sitesi' ), esc_html( number_format_i18n( $term->count ) ) ); ?></span>
                                    </div>
                                    <div class="haber-sitesi-admin__links">
                                        <a href="<?php echo esc_url( get_edit_term_link( $term, 'category' ) ); ?>" class="haber-sitesi-admin__link"><?php esc_html_e( 'Düzenle', 'haber-sitesi' ); ?></a>
                                        <a href="<?php echo esc_url( add_query_arg( [ 'category_name' => $term->slug ], admin_url( 'edit.php' ) ) ); ?>" class="haber-sitesi-admin__link"><?php esc_html_e( 'Haberleri Gör', 'haber-sitesi' ); ?></a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p class="haber-sitesi-admin__empty"><?php esc_html_e( 'Henüz kategori bulunmuyor.', 'haber-sitesi' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="haber-sitesi-admin__card haber-sitesi-admin__card--timeline">
            <h2><?php echo esc_html__( 'Güncel Haber Akışı', 'haber-sitesi' ); ?></h2>
            <p class="haber-sitesi-admin__intro"><?php echo esc_html__( 'Son eklenen içerikleri durum rozetleriyle birlikte takip edin.', 'haber-sitesi' ); ?></p>
            <?php if ( ! empty( $recent_posts ) ) : ?>
                <ul class="haber-sitesi-admin__recent-list">
                    <?php
                    foreach ( $recent_posts as $post ) {
                        $status        = get_post_status( $post );
                        $status_labels = [
                            'publish' => [ 'label' => __( 'Yayında', 'haber-sitesi' ), 'class' => 'is-live' ],
                            'pending' => [ 'label' => __( 'İncelemede', 'haber-sitesi' ), 'class' => 'is-review' ],
                            'draft'   => [ 'label' => __( 'Taslak', 'haber-sitesi' ), 'class' => 'is-draft' ],
                            'future'  => [ 'label' => __( 'Zamanlandı', 'haber-sitesi' ), 'class' => 'is-scheduled' ],
                        ];

                        $status_data = $status_labels[ $status ] ?? [ 'label' => __( 'Diğer', 'haber-sitesi' ), 'class' => 'is-other' ];
                        ?>
                        <li>
                            <span class="haber-sitesi-status <?php echo esc_attr( $status_data['class'] ); ?>"><?php echo esc_html( $status_data['label'] ); ?></span>
                            <a href="<?php echo esc_url( get_edit_post_link( $post ) ); ?>" class="haber-sitesi-admin__recent-title"><?php echo esc_html( get_the_title( $post ) ); ?></a>
                            <span class="haber-sitesi-admin__recent-meta">
                                <?php
                                printf(
                                    /* translators: 1: author name, 2: post date */
                                    esc_html__( '%1$s • %2$s', 'haber-sitesi' ),
                                    esc_html( get_the_author_meta( 'display_name', $post->post_author ) ),
                                    esc_html( get_the_time( get_option( 'date_format' ), $post ) )
                                );
                                ?>
                            </span>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <div class="haber-sitesi-admin__recent-actions">
                    <a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_status=pending' ) ); ?>"><?php esc_html_e( 'İnceleme Bekleyen Haberler', 'haber-sitesi' ); ?></a>
                    <a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_status=draft' ) ); ?>"><?php esc_html_e( 'Taslaklar', 'haber-sitesi' ); ?></a>
                    <a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_status=future' ) ); ?>"><?php esc_html_e( 'Zamanlanmış Yayınlar', 'haber-sitesi' ); ?></a>
                </div>
            <?php else : ?>
                <p class="haber-sitesi-admin__empty"><?php esc_html_e( 'Henüz haber eklenmemiş.', 'haber-sitesi' ); ?></p>
            <?php endif; ?>
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

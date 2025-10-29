<?php
/**
 * Anasayfa şablonu.
 *
 * @package Haber_Sitesi
 */

global $post;

get_header();

$excluded_ids = [];

$live_settings       = haber_sitesi_get_live_center_settings();
$manual_live_enabled = $live_settings['manual'] && '' !== trim( (string) $live_settings['title'] );
$live_cta_label      = $live_settings['cta_label'] ? $live_settings['cta_label'] : __( 'Yayını Aç', 'haber-sitesi' );
$live_embed_html     = $live_settings['embed'];
$live_schedule_title = $live_settings['schedule_title'];

$hero_query = new WP_Query(
    [
        'post_type'           => 'post',
        'posts_per_page'      => 7,
        'ignore_sticky_posts' => false,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
    ]
);

$hero_items = [];

if ( $hero_query->have_posts() ) {
    while ( $hero_query->have_posts() ) {
        $hero_query->the_post();
        $post_id = get_the_ID();
        $item    = haber_sitesi_collect_post_data( $post_id, 36 );

        if ( ! empty( $item ) ) {
            $hero_items[]   = $item;
            $excluded_ids[] = $post_id;
        }
    }
    wp_reset_postdata();
}

$live_primary = [];
$live_lineup  = [];

if ( $manual_live_enabled ) {
    $manual_permalink = $live_settings['cta_url'] ? $live_settings['cta_url'] : '';
    $manual_excerpt   = $live_settings['description'];

    $live_primary = [
        'title'         => trim( (string) $live_settings['title'] ),
        'permalink'     => $manual_permalink,
        'excerpt'       => $manual_excerpt,
        'excerpt_plain' => wp_strip_all_tags( $manual_excerpt ),
        'category'      => $live_settings['category'],
        'clock_time'    => $live_settings['time'],
        'time'          => $live_settings['time'],
        'author'        => $live_settings['presenter'],
        'views'         => absint( $live_settings['views'] ),
        'comments'      => absint( $live_settings['comments'] ),
        'reading_time'  => $live_settings['reading_time'],
        'image'         => '',
        'thumb'         => '',
    ];
} else {
    $live_query_args = [
        'post_type'           => 'post',
        'posts_per_page'      => 4,
        'ignore_sticky_posts' => 1,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
        'post__not_in'        => $excluded_ids,
    ];

    $live_term = get_category_by_slug( 'canli-yayin' );

    if ( $live_term && ! is_wp_error( $live_term ) ) {
        $live_query_args['cat'] = $live_term->term_id;
    } else {
        $live_query_args['tax_query'] = [
            [
                'taxonomy' => 'post_format',
                'field'    => 'slug',
                'terms'    => [ 'post-format-video', 'post-format-audio' ],
            ],
        ];
    }

    $live_query = new WP_Query( $live_query_args );

    if ( $live_query->have_posts() ) {
        while ( $live_query->have_posts() ) {
            $live_query->the_post();
            $post_id = get_the_ID();
            $item    = haber_sitesi_collect_post_data( $post_id, 26 );

            if ( ! empty( $item ) ) {
                $item['clock_time']    = get_post_time( get_option( 'time_format' ), false, $post_id );
                $item['timestamp']     = (int) get_post_time( 'U', true, $post_id );
                $item['excerpt_plain'] = wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post_id ) ), 28, '…' );

                $live_lineup[]  = $item;
                $excluded_ids[] = $post_id;
            }
        }
        wp_reset_postdata();
    }

    if ( ! empty( $live_lineup ) ) {
        $live_primary = array_shift( $live_lineup );
    }

    if ( ! empty( $live_lineup ) ) {
        foreach ( $live_lineup as $index => $lineup_item ) {
            if ( empty( $lineup_item['clock_time'] ) && ! empty( $lineup_item['time'] ) ) {
                $live_lineup[ $index ]['clock_time'] = $lineup_item['time'];
            }
        }
    }
}

if ( ! empty( $live_primary ) ) {
    $live_primary['clock_time'] = isset( $live_primary['clock_time'] ) && $live_primary['clock_time'] ? $live_primary['clock_time'] : $live_primary['time'];

    if ( empty( $live_primary['permalink'] ) ) {
        $live_primary['permalink'] = '#';
    }
}

$live_primary_views_value    = ! empty( $live_primary['views'] ) ? haber_sitesi_format_count( $live_primary['views'] ) : '';
$live_primary_comments_value = ! empty( $live_primary['comments'] ) ? number_format_i18n( $live_primary['comments'] ) : '';
$live_primary_reading_value  = ! empty( $live_primary['reading_time'] ) ? $live_primary['reading_time'] : '';

$live_schedule_heading = $live_schedule_title ? $live_schedule_title : __( 'Stüdyo Programı', 'haber-sitesi' );

$top_categories = get_terms(
    [
        'taxonomy'   => 'category',
        'orderby'    => 'count',
        'order'      => 'DESC',
        'number'     => 3,
        'hide_empty' => true,
    ]
);

$category_panels = [];

if ( ! empty( $top_categories ) && ! is_wp_error( $top_categories ) ) {
    foreach ( $top_categories as $term ) {
        $panel_query = new WP_Query(
            [
                'post_type'           => 'post',
                'posts_per_page'      => 3,
                'ignore_sticky_posts' => 1,
                'post_status'         => 'publish',
                'no_found_rows'       => true,
                'post__not_in'        => $excluded_ids,
                'cat'                 => $term->term_id,
            ]
        );

        $panel_posts = [];

        if ( $panel_query->have_posts() ) {
            while ( $panel_query->have_posts() ) {
                $panel_query->the_post();
                $post_id = get_the_ID();
                $item    = haber_sitesi_collect_post_data( $post_id, 20 );

                if ( ! empty( $item ) ) {
                    $panel_posts[]  = $item;
                    $excluded_ids[] = $post_id;
                }
            }
            wp_reset_postdata();
        }

        if ( ! empty( $panel_posts ) ) {
            $category_panels[] = [
                'term'  => $term,
                'posts' => $panel_posts,
            ];
        }
    }
}

$digest_query = new WP_Query(
    [
        'post_type'           => 'post',
        'posts_per_page'      => 6,
        'ignore_sticky_posts' => 1,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
        'post__not_in'        => $excluded_ids,
    ]
);

$digest_items = [];

if ( $digest_query->have_posts() ) {
    while ( $digest_query->have_posts() ) {
        $digest_query->the_post();
        $post_id = get_the_ID();
        $item    = haber_sitesi_collect_post_data( $post_id, 22 );

        if ( ! empty( $item ) ) {
            $digest_items[] = $item;
            $excluded_ids[] = $post_id;
        }
    }
    wp_reset_postdata();
}

$commentary_after = gmdate( 'Y-m-d', current_time( 'timestamp' ) - ( 30 * DAY_IN_SECONDS ) );

$voices_query = new WP_Query(
    [
        'post_type'           => 'post',
        'posts_per_page'      => 4,
        'ignore_sticky_posts' => 1,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
        'post__not_in'        => $excluded_ids,
        'orderby'             => 'comment_count',
        'order'               => 'DESC',
        'date_query'          => [
            [
                'after'     => $commentary_after,
                'inclusive' => true,
            ],
        ],
    ]
);

$voices_items = [];

if ( $voices_query->have_posts() ) {
    while ( $voices_query->have_posts() ) {
        $voices_query->the_post();
        $post_id = get_the_ID();
        $item    = haber_sitesi_collect_post_data( $post_id, 18 );

        if ( ! empty( $item ) ) {
            $voices_items[] = $item;
            $excluded_ids[] = $post_id;
        }
    }
    wp_reset_postdata();
}

$video_items = [];

$video_query_args = [
    'post_type'           => 'post',
    'posts_per_page'      => 6,
    'ignore_sticky_posts' => 1,
    'post_status'         => 'publish',
    'no_found_rows'       => true,
    'post__not_in'        => $excluded_ids,
];

$video_term = get_category_by_slug( 'video' );

if ( $video_term && ! is_wp_error( $video_term ) ) {
    $video_query_args['cat'] = $video_term->term_id;
} else {
    $video_query_args['tax_query'] = [
        [
            'taxonomy' => 'post_format',
            'field'    => 'slug',
            'terms'    => [ 'post-format-video' ],
        ],
    ];
}

$video_query = new WP_Query( $video_query_args );

if ( $video_query->have_posts() ) {
    while ( $video_query->have_posts() ) {
        $video_query->the_post();
        $post_id = get_the_ID();
        $item    = haber_sitesi_collect_post_data( $post_id, 16 );

        if ( ! empty( $item ) ) {
            $video_items[]  = $item;
            $excluded_ids[] = $post_id;
        }
    }
    wp_reset_postdata();
}

$gallery_items = [];

$gallery_query_args = [
    'post_type'           => 'post',
    'posts_per_page'      => 6,
    'ignore_sticky_posts' => 1,
    'post_status'         => 'publish',
    'no_found_rows'       => true,
    'post__not_in'        => $excluded_ids,
];

$gallery_term = get_category_by_slug( 'galeri' );

if ( $gallery_term && ! is_wp_error( $gallery_term ) ) {
    $gallery_query_args['cat'] = $gallery_term->term_id;
} else {
    $gallery_query_args['tax_query'] = [
        [
            'taxonomy' => 'post_format',
            'field'    => 'slug',
            'terms'    => [ 'post-format-gallery', 'post-format-image' ],
        ],
    ];
}

$gallery_query = new WP_Query( $gallery_query_args );

if ( $gallery_query->have_posts() ) {
    while ( $gallery_query->have_posts() ) {
        $gallery_query->the_post();
        $post_id = get_the_ID();
        $item    = haber_sitesi_collect_post_data( $post_id, 16 );

        if ( ! empty( $item ) ) {
            $gallery_items[] = $item;
            $excluded_ids[]  = $post_id;
        }
    }
    wp_reset_postdata();
}

$stream_query = new WP_Query(
    [
        'post_type'           => 'post',
        'posts_per_page'      => 8,
        'ignore_sticky_posts' => 1,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
        'post__not_in'        => $excluded_ids,
    ]
);

$stream_items = [];

if ( $stream_query->have_posts() ) {
    while ( $stream_query->have_posts() ) {
        $stream_query->the_post();
        $post_id = get_the_ID();
        $item    = haber_sitesi_collect_post_data( $post_id, 18 );

        if ( ! empty( $item ) ) {
            $stream_items[] = $item;
        }
    }
    wp_reset_postdata();
}

$trending_query = new WP_Query(
    [
        'post_type'           => 'post',
        'posts_per_page'      => 6,
        'ignore_sticky_posts' => 1,
        'post_status'         => 'publish',
        'no_found_rows'       => true,
        'meta_key'            => 'haber_view_count',
        'orderby'             => 'meta_value_num',
        'order'               => 'DESC',
    ]
);

$trending_items = [];

if ( $trending_query->have_posts() ) {
    while ( $trending_query->have_posts() ) {
        $trending_query->the_post();
        $post_id = get_the_ID();
        $item    = haber_sitesi_collect_post_data( $post_id, 12 );

        if ( ! empty( $item ) ) {
            $trending_items[] = $item;
        }
    }
    wp_reset_postdata();
}

$investigation_items = [];

$investigation_query_args = [
    'post_type'           => 'post',
    'posts_per_page'      => 5,
    'ignore_sticky_posts' => 1,
    'post_status'         => 'publish',
    'no_found_rows'       => true,
    'post__not_in'        => $excluded_ids,
];

$investigation_term = get_category_by_slug( 'ozel-dosya' );

if ( $investigation_term && ! is_wp_error( $investigation_term ) ) {
    $investigation_query_args['cat'] = $investigation_term->term_id;
} else {
    $investigation_query_args['tax_query'] = [
        [
            'taxonomy' => 'post_tag',
            'field'    => 'slug',
            'terms'    => [ 'ozel-dosya', 'analiz', 'arastirma' ],
        ],
    ];
}

$investigation_query = new WP_Query( $investigation_query_args );

if ( $investigation_query->have_posts() ) {
    while ( $investigation_query->have_posts() ) {
        $investigation_query->the_post();
        $post_id = get_the_ID();
        $item    = haber_sitesi_collect_post_data( $post_id, 26 );

        if ( ! empty( $item ) ) {
            $investigation_items[] = $item;
            $excluded_ids[]        = $post_id;
        }
    }
    wp_reset_postdata();
}

$podcast_items = [];

$podcast_query_args = [
    'post_type'           => 'post',
    'posts_per_page'      => 5,
    'ignore_sticky_posts' => 1,
    'post_status'         => 'publish',
    'no_found_rows'       => true,
    'post__not_in'        => $excluded_ids,
    'tax_query'           => [
        'relation' => 'OR',
        [
            'taxonomy' => 'post_format',
            'field'    => 'slug',
            'terms'    => [ 'post-format-audio' ],
        ],
        [
            'taxonomy' => 'category',
            'field'    => 'slug',
            'terms'    => [ 'podcast', 'radyo', 'canli-yayin' ],
        ],
    ],
];

$podcast_query = new WP_Query( $podcast_query_args );

if ( $podcast_query->have_posts() ) {
    while ( $podcast_query->have_posts() ) {
        $podcast_query->the_post();
        $post_id = get_the_ID();
        $item    = haber_sitesi_collect_post_data( $post_id, 20 );

        if ( ! empty( $item ) ) {
            $podcast_items[] = $item;
            $excluded_ids[]  = $post_id;
        }
    }
    wp_reset_postdata();
}

$activity_snapshot = function_exists( 'haber_sitesi_get_monthly_activity' ) ? haber_sitesi_get_monthly_activity( 6 ) : [];
$activity_total    = isset( $activity_snapshot['total'] ) ? (int) $activity_snapshot['total'] : 0;
$activity_average  = isset( $activity_snapshot['average'] ) ? (int) $activity_snapshot['average'] : 0;
$activity_peak     = isset( $activity_snapshot['peak'] ) && is_array( $activity_snapshot['peak'] ) ? $activity_snapshot['peak'] : [];
$activity_peak_lbl = isset( $activity_peak['label'] ) ? $activity_peak['label'] : '';
$activity_peak_val = isset( $activity_peak['value'] ) ? (int) $activity_peak['value'] : 0;

$week_posts_query = new WP_Query(
    [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'ignore_sticky_posts' => 1,
        'no_found_rows'       => false,
        'posts_per_page'      => 1,
        'fields'              => 'ids',
        'date_query'          => [
            [
                'after'     => '1 week ago',
                'inclusive' => true,
            ],
        ],
    ]
);

$week_post_count = (int) $week_posts_query->found_posts;
wp_reset_postdata();

$today_posts_query = new WP_Query(
    [
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'ignore_sticky_posts' => 1,
        'no_found_rows'       => false,
        'posts_per_page'      => 1,
        'fields'              => 'ids',
        'date_query'          => [
            [
                'after'     => 'today',
                'inclusive' => true,
            ],
        ],
    ]
);

$today_post_count = (int) $today_posts_query->found_posts;
wp_reset_postdata();

$post_counts      = wp_count_posts();
$published_posts  = $post_counts && isset( $post_counts->publish ) ? (int) $post_counts->publish : 0;
$category_total   = wp_count_terms( 'category', [ 'hide_empty' => false ] );
$category_total   = ! is_wp_error( $category_total ) ? (int) $category_total : 0;
$comment_counts   = wp_count_comments();
$approved_comments = $comment_counts ? (int) $comment_counts->approved : 0;
$recent_comments   = get_comments(
    [
        'status'     => 'approve',
        'count'      => true,
        'date_query' => [
            [
                'after'     => '1 week ago',
                'inclusive' => true,
            ],
        ],
    ]
);

if ( is_wp_error( $recent_comments ) ) {
    $recent_comments = 0;
}

$user_counts          = count_users();
$editorial_role_keys  = apply_filters(
    'haber_sitesi_editorial_metric_roles',
    [ 'administrator', 'editor', 'author', 'contributor', 'haber_editoru', 'haber_yazari', 'haber_muhabiri' ]
);
$editorial_roles      = 0;
$total_staff          = isset( $user_counts['total_users'] ) ? (int) $user_counts['total_users'] : 0;
$available_role_counts = isset( $user_counts['avail_roles'] ) && is_array( $user_counts['avail_roles'] ) ? $user_counts['avail_roles'] : [];

foreach ( (array) $editorial_role_keys as $role_key ) {
    $role_key = sanitize_key( $role_key );

    if ( isset( $available_role_counts[ $role_key ] ) ) {
        $editorial_roles += (int) $available_role_counts[ $role_key ];
    }
}

$today_meta = $today_post_count
    ? sprintf( __( 'Bugün %s yeni başlık', 'haber-sitesi' ), number_format_i18n( $today_post_count ) )
    : __( 'Bugün yeni başlık planlanmıyor', 'haber-sitesi' );

$activity_meta = '';

if ( $activity_peak_lbl && $activity_peak_val ) {
    $activity_meta = sprintf(
        __( 'Zirve: %1$s (%2$s içerik)', 'haber-sitesi' ),
        $activity_peak_lbl,
        number_format_i18n( $activity_peak_val )
    );
} elseif ( $activity_average ) {
    $activity_meta = sprintf(
        __( 'Aylık ortalama %s içerik', 'haber-sitesi' ),
        number_format_i18n( $activity_average )
    );
} else {
    $activity_meta = __( 'Aktif yayın akışı izleniyor', 'haber-sitesi' );
}

$insight_metrics = [
    [
        'icon' => '🗓️',
        'label' => __( 'Son 7 Gün', 'haber-sitesi' ),
        'value' => number_format_i18n( $week_post_count ),
        'meta'  => $today_meta,
    ],
    [
        'icon' => '📈',
        'label' => __( 'Aylık Yayın Toplamı', 'haber-sitesi' ),
        'value' => number_format_i18n( $activity_total ),
        'meta'  => $activity_meta,
    ],
    [
        'icon' => '📰',
        'label' => __( 'Yayındaki Haberler', 'haber-sitesi' ),
        'value' => number_format_i18n( $published_posts ),
        'meta'  => sprintf( __( '%s aktif kategori', 'haber-sitesi' ), number_format_i18n( $category_total ) ),
    ],
    [
        'icon' => '💬',
        'label' => __( 'Onaylı Yorumlar', 'haber-sitesi' ),
        'value' => number_format_i18n( $approved_comments ),
        'meta'  => sprintf( __( 'Son 7 günde %s yorum', 'haber-sitesi' ), number_format_i18n( (int) $recent_comments ) ),
    ],
    [
        'icon' => '🧑‍💼',
        'label' => __( 'Aktif Editör Ekibi', 'haber-sitesi' ),
        'value' => number_format_i18n( $editorial_roles ),
        'meta'  => sprintf( __( 'Toplam ekip: %s kişi', 'haber-sitesi' ), number_format_i18n( $total_staff ) ),
    ],
];

$has_pro_modules = ! empty( $investigation_items ) || ! empty( $podcast_items );

$author_roles = apply_filters(
    'haber_sitesi_front_author_roles',
    [ 'haber_editoru', 'editor', 'haber_yazari', 'author', 'haber_muhabiri', 'contributor' ]
);

$author_args = [
    'orderby'             => 'post_count',
    'order'               => 'DESC',
    'number'              => 6,
    'fields'              => 'ids',
    'has_published_posts' => [ 'post' ],
];

if ( ! empty( $author_roles ) ) {
    $author_args['role__in'] = array_map( 'sanitize_key', (array) $author_roles );
} else {
    $author_args['who'] = 'authors';
}

$author_ids = get_users( $author_args );

if ( empty( $author_ids ) ) {
    $author_ids = get_users(
        [
            'who'                 => 'authors',
            'number'              => 6,
            'orderby'             => 'post_count',
            'order'               => 'DESC',
            'fields'              => 'ids',
            'has_published_posts' => [ 'post' ],
        ]
    );
}

$author_profiles = [];

if ( ! empty( $author_ids ) ) {
    foreach ( $author_ids as $author_id ) {
        $profile = haber_sitesi_collect_author_profile( $author_id );

        if ( ! empty( $profile ) ) {
            $author_profiles[] = $profile;
        }
    }
}

$team_directory_link = apply_filters( 'haber_sitesi_team_page_link', '' );

$archive_link = get_post_type_archive_link( 'post' );

$weather_location    = get_theme_mod( 'haber_weather_location', __( 'İstanbul', 'haber-sitesi' ) );
$weather_temperature = get_theme_mod( 'haber_weather_temperature', '15°C' );
$weather_condition   = get_theme_mod( 'haber_weather_condition', __( 'Güneşli', 'haber-sitesi' ) );
$market_update_label = trim( (string) get_theme_mod( 'haber_market_update_label', '' ) );

$market_snapshot = apply_filters(
    'haber_sitesi_market_snapshot',
    [
        [
            'label'     => __( 'Dolar', 'haber-sitesi' ),
            'value'     => '41,9613',
            'direction' => 'up',
        ],
        [
            'label'     => __( 'Euro', 'haber-sitesi' ),
            'value'     => '48,9260',
            'direction' => 'up',
        ],
        [
            'label'     => __( 'Gram Altın', 'haber-sitesi' ),
            'value'     => '5.335,97',
            'direction' => 'down',
        ],
        [
            'label'     => __( 'BIST 100', 'haber-sitesi' ),
            'value'     => '10.871,08',
            'direction' => 'up',
        ],
        [
            'label'     => __( 'Bitcoin', 'haber-sitesi' ),
            'value'     => '$114.750',
            'direction' => 'down',
        ],
        [
            'label'     => __( 'Ethereum', 'haber-sitesi' ),
            'value'     => '$4.111',
            'direction' => 'down',
        ],
    ]
);

$market_snapshot = array_filter(
    array_map(
        static function ( $item ) {
            $label = isset( $item['label'] ) ? sanitize_text_field( wp_strip_all_tags( $item['label'] ) ) : '';
            $value = isset( $item['value'] ) ? sanitize_text_field( wp_strip_all_tags( $item['value'] ) ) : '';
            $dir   = isset( $item['direction'] ) ? sanitize_key( $item['direction'] ) : 'flat';

            if ( ! $label || ! $value ) {
                return null;
            }

            if ( ! in_array( $dir, [ 'up', 'down', 'flat' ], true ) ) {
                $dir = 'flat';
            }

            return [
                'label'     => $label,
                'value'     => $value,
                'direction' => $dir,
            ];
        },
        (array) $market_snapshot
    )
);

$direction_labels = [
    'up'   => __( 'yükselişte', 'haber-sitesi' ),
    'down' => __( 'düşüşte', 'haber-sitesi' ),
    'flat' => __( 'sabit', 'haber-sitesi' ),
];
?>

<div class="front-screen">
    <?php if ( ! empty( $market_snapshot ) ) : ?>
        <section class="front-market" aria-label="<?php esc_attr_e( 'Piyasa özeti', 'haber-sitesi' ); ?>">
            <div class="front-shell front-market__shell">
                <div class="front-market__items" role="list">
                    <?php foreach ( $market_snapshot as $item ) : ?>
                        <div class="front-market__item front-market__item--<?php echo esc_attr( $item['direction'] ); ?>" role="listitem">
                            <span class="front-market__label"><?php echo esc_html( $item['label'] ); ?></span>
                            <span class="front-market__value"><?php echo esc_html( $item['value'] ); ?></span>
                            <span class="front-market__trend front-market__trend--<?php echo esc_attr( $item['direction'] ); ?>" aria-hidden="true"></span>
                            <span class="screen-reader-text">
                                <?php
                                $direction_key = $item['direction'];
                                echo esc_html( isset( $direction_labels[ $direction_key ] ) ? $direction_labels[ $direction_key ] : $direction_labels['flat'] );
                                ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="front-market__weather" role="status" aria-live="polite">
                    <?php if ( $weather_location || $weather_temperature ) : ?>
                        <span class="front-market__weather-city"><?php echo esc_html( trim( $weather_location . ' ' . $weather_temperature ) ); ?></span>
                    <?php endif; ?>
                    <?php if ( $weather_condition ) : ?>
                        <span class="front-market__weather-condition"><?php echo esc_html( $weather_condition ); ?></span>
                    <?php endif; ?>
                    <?php if ( $market_update_label ) : ?>
                        <span class="front-market__update"><?php echo esc_html( $market_update_label ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="front-slider" id="front-manset" aria-label="<?php esc_attr_e( 'Manşet', 'haber-sitesi' ); ?>">
        <div class="front-shell front-slider__shell" data-front-slider>
            <?php if ( ! empty( $hero_items ) ) : ?>
                <div class="front-slider__stage">
                    <?php foreach ( $hero_items as $index => $item ) :
                        $active    = 0 === $index ? ' is-active' : '';
                        $hidden    = 0 === $index ? 'false' : 'true';
                        $tabindex  = 0 === $index ? '0' : '-1';
                        $panel_id  = 'front-slider-panel-' . ( $index + 1 );
                        $share_url = $item['permalink'];
                        ?>
                        <article
                            id="<?php echo esc_attr( $panel_id ); ?>"
                            class="front-slider__panel<?php echo esc_attr( $active ); ?>"
                            data-index="<?php echo esc_attr( $index ); ?>"
                            role="tabpanel"
                            aria-hidden="<?php echo esc_attr( $hidden ); ?>"
                            tabindex="<?php echo esc_attr( $tabindex ); ?>"
                        >
                            <a class="front-slider__media" href="<?php echo esc_url( $item['permalink'] ); ?>">
                                <?php if ( $item['image'] ) : ?>
                                    <img src="<?php echo esc_url( $item['image'] ); ?>" alt="" />
                                <?php else : ?>
                                    <span class="front-slider__placeholder" aria-hidden="true">🗞️</span>
                                <?php endif; ?>
                            </a>
                            <div class="front-slider__content">
                                <div class="front-slider__meta">
                                    <span class="front-slider__badge"><?php echo esc_html( $item['category'] ); ?></span>
                                    <span class="front-slider__time"><?php echo esc_html( $item['time'] ); ?></span>
                                </div>
                                <h2 class="front-slider__title"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h2>
                                <p class="front-slider__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                <div class="front-slider__footer">
                                    <div class="front-slider__stats">
                                        <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                        <span>💬 <?php echo esc_html( number_format_i18n( $item['comments'] ) ); ?></span>
                                        <span>⏱️ <?php echo esc_html( $item['reading_time'] ); ?></span>
                                    </div>
                                    <div class="front-slider__actions">
                                        <a class="front-slider__action front-slider__action--primary" href="<?php echo esc_url( $item['permalink'] ); ?>"><?php esc_html_e( 'Haberi Oku', 'haber-sitesi' ); ?></a>
                                        <button
                                            type="button"
                                            class="front-slider__action front-slider__action--ghost js-share-button"
                                            data-share-url="<?php echo esc_url( $share_url ); ?>"
                                            data-share-title="<?php echo esc_attr( wp_strip_all_tags( $item['title'] ) ); ?>"
                                            aria-label="<?php echo esc_attr( sprintf( __( '“%s” haberini paylaş', 'haber-sitesi' ), $item['title'] ) ); ?>"
                                        >
                                            <?php esc_html_e( 'Paylaş', 'haber-sitesi' ); ?>
                                        </button>
                                        <button
                                            type="button"
                                            class="front-slider__action front-slider__action--save js-save-button"
                                            data-post-id="<?php echo esc_attr( $item['id'] ); ?>"
                                            data-label-save="<?php esc_attr_e( 'Kaydet', 'haber-sitesi' ); ?>"
                                            data-label-saved="<?php esc_attr_e( 'Kaydedildi', 'haber-sitesi' ); ?>"
                                            aria-pressed="false"
                                            aria-label="<?php echo esc_attr( sprintf( __( '“%s” haberini kaydet', 'haber-sitesi' ), $item['title'] ) ); ?>"
                                        >
                                            <?php esc_html_e( 'Kaydet', 'haber-sitesi' ); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <div class="front-slider__rail" role="tablist" aria-label="<?php esc_attr_e( 'Manşet listesi', 'haber-sitesi' ); ?>">
                    <?php foreach ( $hero_items as $index => $item ) :
                        $active   = 0 === $index ? ' is-active' : '';
                        $selected = 0 === $index ? 'true' : 'false';
                        $panel_id = 'front-slider-panel-' . ( $index + 1 );
                        ?>
                        <button
                            type="button"
                            class="front-slider__thumb<?php echo esc_attr( $active ); ?>"
                            role="tab"
                            aria-selected="<?php echo esc_attr( $selected ); ?>"
                            aria-controls="<?php echo esc_attr( $panel_id ); ?>"
                            data-index="<?php echo esc_attr( $index ); ?>"
                        >
                            <span class="front-slider__thumb-media">
                                <?php if ( $item['thumb'] ) : ?>
                                    <img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="" />
                                <?php else : ?>
                                    <span class="front-slider__thumb-placeholder" aria-hidden="true">🗞️</span>
                                <?php endif; ?>
                            </span>
                            <span class="front-slider__thumb-body">
                                <span class="front-slider__thumb-category"><?php echo esc_html( $item['category'] ); ?></span>
                                <span class="front-slider__thumb-title"><?php echo esc_html( $item['title'] ); ?></span>
                                <span class="front-slider__thumb-time"><?php echo esc_html( $item['time'] ); ?></span>
                            </span>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="front-empty"><?php esc_html_e( 'Henüz içerik eklenmemiş.', 'haber-sitesi' ); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <?php if ( ! empty( $video_items ) || ! empty( $gallery_items ) ) : ?>
        <section class="front-spotlights" aria-label="<?php esc_attr_e( 'Vitrin içerikleri', 'haber-sitesi' ); ?>">
            <div class="front-shell front-spotlights__shell">
                <?php if ( ! empty( $video_items ) ) :
                    $video_list_id = 'front-spotlights-video';
                    ?>
                    <div class="front-spotlights__block front-spotlights__block--video" data-spotlight-scroll>
                        <header class="front-spotlights__header">
                            <div>
                                <h2 class="front-spotlights__title"><?php esc_html_e( 'Video Haberler', 'haber-sitesi' ); ?></h2>
                                <p class="front-spotlights__subtitle"><?php esc_html_e( 'Canlı yayınlar ve röportajlar için hızlı vitrin', 'haber-sitesi' ); ?></p>
                            </div>
                            <div class="front-spotlights__controls">
                                <button
                                    type="button"
                                    class="front-spotlights__nav front-spotlights__nav--prev"
                                    data-spotlight-prev
                                    aria-controls="<?php echo esc_attr( $video_list_id ); ?>"
                                    aria-label="<?php esc_attr_e( 'Önceki video', 'haber-sitesi' ); ?>"
                                >
                                    <span aria-hidden="true">‹</span>
                                </button>
                                <button
                                    type="button"
                                    class="front-spotlights__nav front-spotlights__nav--next"
                                    data-spotlight-next
                                    aria-controls="<?php echo esc_attr( $video_list_id ); ?>"
                                    aria-label="<?php esc_attr_e( 'Sonraki video', 'haber-sitesi' ); ?>"
                                >
                                    <span aria-hidden="true">›</span>
                                </button>
                            </div>
                        </header>
                        <div class="front-spotlights__scroller">
                            <div
                                class="front-spotlights__list"
                                id="<?php echo esc_attr( $video_list_id ); ?>"
                                data-spotlight-list
                                role="list"
                            >
                                <?php foreach ( $video_items as $item ) : ?>
                                    <article class="front-spotlights__card" role="listitem">
                                        <a class="front-spotlights__media" href="<?php echo esc_url( $item['permalink'] ); ?>">
                                            <?php if ( $item['thumb'] ) : ?>
                                                <img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="" />
                                            <?php else : ?>
                                                <span class="front-spotlights__placeholder" aria-hidden="true">▶</span>
                                            <?php endif; ?>
                                        </a>
                                        <div class="front-spotlights__body">
                                            <div class="front-spotlights__meta">
                                                <span><?php echo esc_html( $item['category'] ); ?></span>
                                                <span><?php echo esc_html( $item['time'] ); ?></span>
                                            </div>
                                            <h3 class="front-spotlights__headline"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h3>
                                            <p class="front-spotlights__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                            <div class="front-spotlights__stats">
                                                <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                                <span>💬 <?php echo esc_html( number_format_i18n( $item['comments'] ) ); ?></span>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $gallery_items ) ) :
                    $gallery_list_id = 'front-spotlights-gallery';
                    ?>
                    <div class="front-spotlights__block front-spotlights__block--gallery" data-spotlight-scroll>
                        <header class="front-spotlights__header">
                            <div>
                                <h2 class="front-spotlights__title"><?php esc_html_e( 'Foto Galeri', 'haber-sitesi' ); ?></h2>
                                <p class="front-spotlights__subtitle"><?php esc_html_e( 'Günün kareleri ve sahadan yansımalar', 'haber-sitesi' ); ?></p>
                            </div>
                            <div class="front-spotlights__controls">
                                <button
                                    type="button"
                                    class="front-spotlights__nav front-spotlights__nav--prev"
                                    data-spotlight-prev
                                    aria-controls="<?php echo esc_attr( $gallery_list_id ); ?>"
                                    aria-label="<?php esc_attr_e( 'Önceki galeri', 'haber-sitesi' ); ?>"
                                >
                                    <span aria-hidden="true">‹</span>
                                </button>
                                <button
                                    type="button"
                                    class="front-spotlights__nav front-spotlights__nav--next"
                                    data-spotlight-next
                                    aria-controls="<?php echo esc_attr( $gallery_list_id ); ?>"
                                    aria-label="<?php esc_attr_e( 'Sonraki galeri', 'haber-sitesi' ); ?>"
                                >
                                    <span aria-hidden="true">›</span>
                                </button>
                            </div>
                        </header>
                        <div class="front-spotlights__scroller">
                            <div
                                class="front-spotlights__list"
                                id="<?php echo esc_attr( $gallery_list_id ); ?>"
                                data-spotlight-list
                                role="list"
                            >
                                <?php foreach ( $gallery_items as $item ) : ?>
                                    <article class="front-spotlights__card" role="listitem">
                                        <a class="front-spotlights__media" href="<?php echo esc_url( $item['permalink'] ); ?>">
                                            <?php if ( $item['thumb'] ) : ?>
                                                <img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="" />
                                            <?php else : ?>
                                                <span class="front-spotlights__placeholder" aria-hidden="true">🖼️</span>
                                            <?php endif; ?>
                                        </a>
                                        <div class="front-spotlights__body">
                                            <div class="front-spotlights__meta">
                                                <span><?php echo esc_html( $item['category'] ); ?></span>
                                                <span><?php echo esc_html( $item['time'] ); ?></span>
                                            </div>
                                            <h3 class="front-spotlights__headline"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h3>
                                            <p class="front-spotlights__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                            <div class="front-spotlights__stats">
                                                <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                                <span>💬 <?php echo esc_html( number_format_i18n( $item['comments'] ) ); ?></span>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ( ! empty( $live_primary ) ) : ?>
        <section class="front-live" id="front-canli" aria-label="<?php esc_attr_e( 'Canlı yayın merkezi', 'haber-sitesi' ); ?>">
            <div class="front-shell">
                <div class="front-live__stage" data-live-center>
                    <article class="front-live__primary">
                        <div class="front-live__visual">
                            <span class="front-live__badge" aria-hidden="true"><?php esc_html_e( 'CANLI', 'haber-sitesi' ); ?></span>
                            <?php if ( $live_embed_html ) : ?>
                                <div class="front-live__embed">
                                    <?php echo $live_embed_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                </div>
                            <?php endif; ?>
                            <img
                                class="front-live__image<?php echo empty( $live_primary['image'] ) || $live_embed_html ? ' is-hidden' : ''; ?>"
                                data-live-target="visual"
                                src="<?php echo ! empty( $live_primary['image'] ) ? esc_url( $live_primary['image'] ) : ''; ?>"
                                alt=""
                            />
                            <div class="front-live__placeholder<?php echo ( empty( $live_primary['image'] ) && ! $live_embed_html ) ? '' : ' is-hidden'; ?>" data-live-target="placeholder" aria-hidden="true">📡</div>
                        </div>
                        <div class="front-live__body" aria-live="polite">
                            <div class="front-live__meta">
                                <span class="front-live__meta-item<?php echo empty( $live_primary['category'] ) ? ' is-hidden' : ''; ?>" data-live-target="category"><?php echo esc_html( $live_primary['category'] ); ?></span>
                                <span class="front-live__meta-item<?php echo empty( $live_primary['clock_time'] ) ? ' is-hidden' : ''; ?>" data-live-target="clock"><?php echo esc_html( $live_primary['clock_time'] ); ?></span>
                                <span class="front-live__meta-item<?php echo empty( $live_primary['author'] ) ? ' is-hidden' : ''; ?>" data-live-target="author"><?php echo esc_html( $live_primary['author'] ); ?></span>
                            </div>
                            <h2 class="front-live__title">
                                <a data-live-target="headline" href="<?php echo esc_url( $live_primary['permalink'] ); ?>"><?php echo esc_html( $live_primary['title'] ); ?></a>
                            </h2>
                            <p class="front-live__excerpt" data-live-target="excerpt"><?php echo wp_kses_post( $live_primary['excerpt'] ); ?></p>
                            <div class="front-live__stats">
                                <span <?php echo $live_primary_views_value ? '' : 'class="is-hidden" '; ?>data-live-target="views">👁️ <?php echo esc_html( $live_primary_views_value ); ?></span>
                                <span <?php echo $live_primary_comments_value ? '' : 'class="is-hidden" '; ?>data-live-target="comments">💬 <?php echo esc_html( $live_primary_comments_value ); ?></span>
                                <span <?php echo $live_primary_reading_value ? '' : 'class="is-hidden" '; ?>data-live-target="reading">⏱️ <?php echo esc_html( $live_primary_reading_value ); ?></span>
                            </div>
                            <div class="front-live__actions">
                                <a class="front-live__cta<?php echo empty( $live_primary['permalink'] ) || '#' === $live_primary['permalink'] ? ' is-hidden' : ''; ?>" data-live-target="cta" href="<?php echo esc_url( $live_primary['permalink'] ); ?>"><?php echo esc_html( $live_cta_label ); ?></a>
                                <a class="front-live__more" href="<?php echo esc_url( $archive_link ? $archive_link : home_url( '/' ) ); ?>#front-stream"><?php esc_html_e( 'Canlı akışı izle', 'haber-sitesi' ); ?></a>
                            </div>
                        </div>
                    </article>
                    <?php if ( ! empty( $live_lineup ) ) : ?>
                        <aside class="front-live__schedule" aria-label="<?php esc_attr_e( 'Sıradaki canlı yayınlar', 'haber-sitesi' ); ?>">
                            <h3 class="front-live__schedule-title"><?php echo esc_html( $live_schedule_heading ); ?></h3>
                            <ul class="front-live__schedule-list">
                                <?php foreach ( $live_lineup as $index => $item ) : ?>
                                    <li class="front-live__schedule-item">
                                        <button
                                            type="button"
                                            class="front-live__schedule-button<?php echo 0 === $index ? ' is-active' : ''; ?>"
                                            data-live-trigger
                                            data-live-title="<?php echo esc_attr( $item['title'] ); ?>"
                                            data-live-url="<?php echo esc_url( $item['permalink'] ); ?>"
                                            data-live-excerpt="<?php echo esc_attr( $item['excerpt_plain'] ); ?>"
                                            data-live-category="<?php echo esc_attr( $item['category'] ); ?>"
                                            data-live-clock="<?php echo esc_attr( $item['clock_time'] ); ?>"
                                            data-live-author="<?php echo esc_attr( $item['author'] ); ?>"
                                            data-live-views="<?php echo esc_attr( haber_sitesi_format_count( $item['views'] ) ); ?>"
                                            data-live-comments="<?php echo esc_attr( number_format_i18n( $item['comments'] ) ); ?>"
                                            data-live-reading="<?php echo esc_attr( $item['reading_time'] ); ?>"
                                            data-live-thumb="<?php echo esc_url( $item['image'] ? $item['image'] : $item['thumb'] ); ?>"
                                            data-live-cta="<?php echo esc_attr( $live_cta_label ); ?>"
                                            aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"
                                        >
                                            <span class="front-live__schedule-time"><?php echo esc_html( $item['clock_time'] ); ?></span>
                                            <span class="front-live__schedule-headline"><?php echo esc_html( $item['title'] ); ?></span>
                                            <span class="front-live__schedule-meta"><?php echo esc_html( $item['category'] ); ?> · <?php echo esc_html( $item['author'] ); ?></span>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </aside>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <div class="front-shell front-layout">
        <div class="front-layout__main">
            <section class="front-block" aria-label="<?php esc_attr_e( 'Günün başlıkları', 'haber-sitesi' ); ?>">
                <div class="front-block__header">
                    <h2 class="front-block__title"><?php esc_html_e( 'Günün Öne Çıkanları', 'haber-sitesi' ); ?></h2>
                    <p class="front-block__subtitle"><?php esc_html_e( 'Editör masamızdan seçilen kategoriler', 'haber-sitesi' ); ?></p>
                </div>
                <?php if ( ! empty( $category_panels ) ) : ?>
                    <div class="front-panels">
                        <?php foreach ( $category_panels as $panel ) : ?>
                            <article class="front-panel">
                                <header class="front-panel__header">
                                    <h3 class="front-panel__title"><a href="<?php echo esc_url( get_category_link( $panel['term']->term_id ) ); ?>"><?php echo esc_html( $panel['term']->name ); ?></a></h3>
                                    <?php if ( ! empty( $panel['term']->description ) ) : ?>
                                        <p class="front-panel__description"><?php echo esc_html( wp_trim_words( $panel['term']->description, 18 ) ); ?></p>
                                    <?php endif; ?>
                                </header>
                                <?php
                                $lead    = $panel['posts'][0];
                                $support = array_slice( $panel['posts'], 1 );
                                ?>
                                <div class="front-panel__body">
                                    <div class="front-panel__lead">
                                        <a class="front-panel__media" href="<?php echo esc_url( $lead['permalink'] ); ?>">
                                            <?php if ( $lead['image'] ) : ?>
                                                <img src="<?php echo esc_url( $lead['image'] ); ?>" alt="" />
                                            <?php else : ?>
                                                <span class="front-panel__placeholder" aria-hidden="true">📰</span>
                                            <?php endif; ?>
                                        </a>
                                        <div class="front-panel__content">
                                            <span class="front-panel__meta"><?php echo esc_html( $lead['time'] ); ?></span>
                                            <h4 class="front-panel__headline"><a href="<?php echo esc_url( $lead['permalink'] ); ?>"><?php echo esc_html( $lead['title'] ); ?></a></h4>
                                            <p class="front-panel__excerpt"><?php echo wp_kses_post( $lead['excerpt'] ); ?></p>
                                        </div>
                                    </div>
                                    <?php if ( ! empty( $support ) ) : ?>
                                        <ul class="front-panel__list">
                                            <?php foreach ( $support as $item ) : ?>
                                                <li class="front-panel__item">
                                                    <a class="front-panel__link" href="<?php echo esc_url( $item['permalink'] ); ?>">
                                                        <span class="front-panel__item-title"><?php echo esc_html( $item['title'] ); ?></span>
                                                        <span class="front-panel__item-meta"><?php echo esc_html( $item['time'] ); ?></span>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Öne çıkarılacak kategori bulunamadı.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="front-block front-block--digest" aria-label="<?php esc_attr_e( 'Haber merkezi', 'haber-sitesi' ); ?>">
                <div class="front-block__header">
                    <h2 class="front-block__title"><?php esc_html_e( 'Haber Merkezi', 'haber-sitesi' ); ?></h2>
                    <p class="front-block__subtitle"><?php esc_html_e( 'Günün farklı noktalarından manşet seçkileri', 'haber-sitesi' ); ?></p>
                </div>
                <?php if ( ! empty( $digest_items ) ) : ?>
                    <div class="front-digest">
                        <?php foreach ( $digest_items as $item ) : ?>
                            <article class="front-digest__card">
                                <a class="front-digest__media" href="<?php echo esc_url( $item['permalink'] ); ?>">
                                    <?php if ( $item['thumb'] ) : ?>
                                        <img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="" />
                                    <?php else : ?>
                                        <span class="front-digest__placeholder" aria-hidden="true">🗞️</span>
                                    <?php endif; ?>
                                </a>
                                <div class="front-digest__content">
                                    <div class="front-digest__meta">
                                        <span><?php echo esc_html( $item['category'] ); ?></span>
                                        <span><?php echo esc_html( $item['time'] ); ?></span>
                                    </div>
                                    <h3 class="front-digest__title"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h3>
                                    <p class="front-digest__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                    <div class="front-digest__stats">
                                        <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                        <span>💬 <?php echo esc_html( number_format_i18n( $item['comments'] ) ); ?></span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Haber merkezi için içerik bekleniyor.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <?php if ( ! empty( $author_profiles ) ) : ?>
                <section class="front-authors" id="front-yazarlar" aria-label="<?php esc_attr_e( 'Yazarlar ve yorumcular', 'haber-sitesi' ); ?>">
                    <div class="front-authors__header">
                        <div>
                            <h2 class="front-authors__title"><?php esc_html_e( 'Canlı Yayın Ekibi', 'haber-sitesi' ); ?></h2>
                            <p class="front-authors__subtitle"><?php esc_html_e( 'Stüdyo yorumcuları ve sahadaki muhabirler', 'haber-sitesi' ); ?></p>
                        </div>
                        <?php if ( $team_directory_link ) : ?>
                            <a class="front-authors__all" href="<?php echo esc_url( $team_directory_link ); ?>"><?php esc_html_e( 'Tüm ekip', 'haber-sitesi' ); ?></a>
                        <?php endif; ?>
                    </div>
                    <div class="front-authors__grid">
                        <?php foreach ( $author_profiles as $profile ) :
                            $name      = isset( $profile['name'] ) ? $profile['name'] : '';
                            $safe_name = trim( wp_strip_all_tags( $name ) );
                            $initials  = '';

                            if ( $safe_name ) {
                                $parts = preg_split( '/\s+/', $safe_name );
                                if ( ! empty( $parts ) ) {
                                    $first       = $parts[0];
                                    $last        = count( $parts ) > 1 ? $parts[ count( $parts ) - 1 ] : '';
                                    $first_char  = function_exists( 'mb_substr' ) ? mb_substr( $first, 0, 1 ) : substr( $first, 0, 1 );
                                    $second_char = $last ? ( function_exists( 'mb_substr' ) ? mb_substr( $last, 0, 1 ) : substr( $last, 0, 1 ) ) : '';
                                    $initials    = strtoupper( $first_char . $second_char );
                                }
                            }
                            ?>
                            <article class="front-authors__card">
                                <div class="front-authors__avatar">
                                    <?php if ( ! empty( $profile['avatar'] ) ) : ?>
                                        <img src="<?php echo esc_url( $profile['avatar'] ); ?>" alt="" />
                                    <?php elseif ( $initials ) : ?>
                                        <span class="front-authors__initials" aria-hidden="true"><?php echo esc_html( $initials ); ?></span>
                                    <?php else : ?>
                                        <span class="front-authors__initials" aria-hidden="true">✍️</span>
                                    <?php endif; ?>
                                </div>
                                <div class="front-authors__body">
                                    <h3 class="front-authors__name"><a href="<?php echo esc_url( $profile['profile'] ); ?>"><?php echo esc_html( $name ); ?></a></h3>
                                    <?php if ( ! empty( $profile['role'] ) ) : ?>
                                        <span class="front-authors__role"><?php echo esc_html( $profile['role'] ); ?></span>
                                    <?php endif; ?>
                                    <?php if ( ! empty( $profile['bio'] ) ) : ?>
                                        <p class="front-authors__bio"><?php echo esc_html( $profile['bio'] ); ?></p>
                                    <?php endif; ?>
                                    <div class="front-authors__footer">
                                        <span class="front-authors__count">📝 <?php echo esc_html( number_format_i18n( (int) $profile['post_count'] ) ); ?></span>
                                        <?php if ( ! empty( $profile['latest'] ) ) : ?>
                                            <a class="front-authors__latest" href="<?php echo esc_url( $profile['latest']['permalink'] ); ?>">
                                                <span class="front-authors__latest-title"><?php echo esc_html( $profile['latest']['title'] ); ?></span>
                                                <span class="front-authors__latest-time"><?php echo esc_html( $profile['latest']['time'] ); ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="front-block front-block--voices" aria-label="<?php esc_attr_e( 'En çok tartışılanlar', 'haber-sitesi' ); ?>">
                <div class="front-block__header">
                    <h2 class="front-block__title"><?php esc_html_e( 'Editör Masası & Yorumlar', 'haber-sitesi' ); ?></h2>
                    <p class="front-block__subtitle"><?php esc_html_e( 'Okurların gündeme taşıdığı başlıklar', 'haber-sitesi' ); ?></p>
                </div>
                <?php if ( ! empty( $voices_items ) ) : ?>
                    <div class="front-voices">
                        <?php foreach ( $voices_items as $item ) : ?>
                            <article class="front-voices__card">
                                <div class="front-voices__header">
                                    <span class="front-voices__category"><?php echo esc_html( $item['category'] ); ?></span>
                                    <span class="front-voices__time"><?php echo esc_html( $item['time'] ); ?></span>
                                </div>
                                <h3 class="front-voices__title"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h3>
                                <p class="front-voices__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                <div class="front-voices__footer">
                                    <span class="front-voices__stat front-voices__stat--comments">💬 <?php echo esc_html( number_format_i18n( $item['comments'] ) ); ?></span>
                                    <span class="front-voices__stat">👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                    <span class="front-voices__stat">⏱️ <?php echo esc_html( $item['reading_time'] ); ?></span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Henüz öne çıkan yorumlu içerik bulunmuyor.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="front-block front-block--stream" id="front-stream" aria-label="<?php esc_attr_e( 'Canlı haber akışı', 'haber-sitesi' ); ?>">
                <div class="front-block__header">
                    <h2 class="front-block__title"><?php esc_html_e( 'Canlı Haber Akışı', 'haber-sitesi' ); ?></h2>
                    <p class="front-block__subtitle"><?php esc_html_e( 'Gelişmeleri dakika dakika takip edin', 'haber-sitesi' ); ?></p>
                </div>
                <?php if ( ! empty( $stream_items ) ) : ?>
                    <ol class="front-stream">
                        <?php foreach ( $stream_items as $item ) : ?>
                            <li class="front-stream__item">
                                <div class="front-stream__time"><?php echo esc_html( $item['time'] ); ?></div>
                                <div class="front-stream__content">
                                    <h3 class="front-stream__headline"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h3>
                                    <p class="front-stream__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                    <div class="front-stream__meta">
                                        <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                        <span>💬 <?php echo esc_html( number_format_i18n( $item['comments'] ) ); ?></span>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <div class="front-stream__footer">
                        <a class="front-stream__more" href="<?php echo esc_url( $archive_link ? $archive_link : home_url( '/' ) ); ?>"><?php esc_html_e( 'Tüm haberleri görüntüle', 'haber-sitesi' ); ?></a>
                    </div>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Henüz canlı akış bulunmuyor.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="front-block front-block--insights" aria-label="<?php esc_attr_e( 'Profesyonel haber merkezi özeti', 'haber-sitesi' ); ?>">
                <div class="front-block__header">
                    <h2 class="front-block__title"><?php esc_html_e( 'Haber Merkezi Panosu', 'haber-sitesi' ); ?></h2>
                    <p class="front-block__subtitle"><?php esc_html_e( 'Canlı metrikler, özel dosyalar ve podcast sahnesi', 'haber-sitesi' ); ?></p>
                </div>
                <div class="front-pro__grid">
                    <div class="front-pro__column front-pro__column--metrics">
                        <div class="front-insights" role="list">
                            <?php foreach ( $insight_metrics as $metric ) : ?>
                                <article class="front-insights__card" role="listitem">
                                    <span class="front-insights__icon" aria-hidden="true"><?php echo esc_html( $metric['icon'] ); ?></span>
                                    <div class="front-insights__meta">
                                        <h3 class="front-insights__label"><?php echo esc_html( $metric['label'] ); ?></h3>
                                        <div class="front-insights__value"><?php echo esc_html( $metric['value'] ); ?></div>
                                        <p class="front-insights__note"><?php echo esc_html( $metric['meta'] ); ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="front-pro__column front-pro__column--modules">
                        <?php if ( $has_pro_modules ) : ?>
                            <?php if ( ! empty( $investigation_items ) ) : ?>
                                <div class="front-pro__module" data-pro-carousel>
                                    <div class="front-pro__module-header">
                                        <h3 class="front-pro__module-title"><?php esc_html_e( 'Özel Dosyalar', 'haber-sitesi' ); ?></h3>
                                        <div class="front-pro__controls">
                                            <button class="front-pro__control" type="button" data-pro-prev aria-label="<?php esc_attr_e( 'Önceki dosya', 'haber-sitesi' ); ?>" title="<?php esc_attr_e( 'Önceki dosya', 'haber-sitesi' ); ?>">&#x2039;</button>
                                            <button class="front-pro__control" type="button" data-pro-next aria-label="<?php esc_attr_e( 'Sonraki dosya', 'haber-sitesi' ); ?>" title="<?php esc_attr_e( 'Sonraki dosya', 'haber-sitesi' ); ?>">&#x203a;</button>
                                        </div>
                                    </div>
                                    <div class="front-pro__track" data-pro-track role="list" tabindex="0" aria-label="<?php esc_attr_e( 'Özel dosya vitrinini kaydır', 'haber-sitesi' ); ?>">
                                        <?php foreach ( $investigation_items as $item ) : ?>
                                            <article class="front-investigation" role="listitem">
                                                <a class="front-investigation__media" href="<?php echo esc_url( $item['permalink'] ); ?>">
                                                    <?php if ( $item['image'] ) : ?>
                                                        <img src="<?php echo esc_url( $item['image'] ); ?>" alt="" />
                                                    <?php else : ?>
                                                        <span class="front-investigation__placeholder" aria-hidden="true">🕵️</span>
                                                    <?php endif; ?>
                                                </a>
                                                <div class="front-investigation__body">
                                                    <span class="front-investigation__badge"><?php echo esc_html( $item['category'] ? $item['category'] : __( 'Özel Dosya', 'haber-sitesi' ) ); ?></span>
                                                    <h4 class="front-investigation__title"><a href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></h4>
                                                    <p class="front-investigation__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                                    <div class="front-investigation__meta">
                                                        <span>👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                                        <span>⏱️ <?php echo esc_html( $item['reading_time'] ); ?></span>
                                                    </div>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $podcast_items ) ) : ?>
                                <div class="front-pro__module front-pro__module--audio">
                                    <div class="front-pro__module-header">
                                        <h3 class="front-pro__module-title"><?php esc_html_e( 'Podcast Stüdyosu', 'haber-sitesi' ); ?></h3>
                                        <a class="front-pro__module-link" href="<?php echo esc_url( home_url( '/podcast' ) ); ?>"><?php esc_html_e( 'Tüm bölümler', 'haber-sitesi' ); ?></a>
                                    </div>
                                    <ul class="front-audio" role="list">
                                        <?php foreach ( $podcast_items as $item ) : ?>
                                            <li class="front-audio__item" role="listitem">
                                                <span class="front-audio__time"><?php echo esc_html( $item['time'] ); ?></span>
                                                <div class="front-audio__content">
                                                    <a class="front-audio__title" href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                                                    <p class="front-audio__excerpt"><?php echo wp_kses_post( $item['excerpt'] ); ?></p>
                                                </div>
                                                <a class="front-audio__cta" href="<?php echo esc_url( $item['permalink'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( '%s bölümünü dinle', 'haber-sitesi' ), $item['title'] ) ); ?>">▶</a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        <?php else : ?>
                            <p class="front-empty front-empty--pro"><?php esc_html_e( 'Henüz özel dosya ya da podcast eklenmedi.', 'haber-sitesi' ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </div>

        <aside class="front-layout__sidebar" aria-label="<?php esc_attr_e( 'Yan sütun', 'haber-sitesi' ); ?>">
            <section id="mobile-most-read" class="front-sidebar-card" aria-label="<?php esc_attr_e( 'En çok okunanlar', 'haber-sitesi' ); ?>">
                <div class="front-sidebar-card__header">
                    <h2 class="front-sidebar-card__title"><?php esc_html_e( 'En Çok Okunanlar', 'haber-sitesi' ); ?></h2>
                    <p class="front-sidebar-card__subtitle"><?php esc_html_e( 'Okurlarımızın tercih ettiği başlıklar', 'haber-sitesi' ); ?></p>
                </div>
                <?php if ( ! empty( $trending_items ) ) : ?>
                    <ol class="front-trending">
                        <?php foreach ( $trending_items as $index => $item ) : ?>
                            <li class="front-trending__item">
                                <span class="front-trending__rank"><?php echo esc_html( $index + 1 ); ?></span>
                                <div class="front-trending__body">
                                    <a class="front-trending__link" href="<?php echo esc_url( $item['permalink'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
                                    <span class="front-trending__meta">👁️ <?php echo esc_html( haber_sitesi_format_count( $item['views'] ) ); ?></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                <?php else : ?>
                    <p class="front-empty"><?php esc_html_e( 'Henüz okunma verisi bulunmuyor.', 'haber-sitesi' ); ?></p>
                <?php endif; ?>
            </section>

            <section class="front-sidebar-card front-sidebar-card--weather" aria-label="<?php esc_attr_e( 'Hava durumu özeti', 'haber-sitesi' ); ?>">
                <div class="front-sidebar-card__header">
                    <h2 class="front-sidebar-card__title"><?php esc_html_e( 'Hava Durumu', 'haber-sitesi' ); ?></h2>
                </div>
                <div class="front-weather">
                    <?php if ( $weather_location ) : ?>
                        <div class="front-weather__city"><?php echo esc_html( $weather_location ); ?></div>
                    <?php endif; ?>
                    <?php if ( $weather_temperature ) : ?>
                        <div class="front-weather__temperature"><?php echo esc_html( $weather_temperature ); ?></div>
                    <?php endif; ?>
                    <?php if ( $weather_condition ) : ?>
                        <div class="front-weather__condition"><?php echo esc_html( $weather_condition ); ?></div>
                    <?php endif; ?>
                    <a class="front-weather__link" href="<?php echo esc_url( home_url( '/hava-durumu' ) ); ?>"><?php esc_html_e( 'Detaylı hava raporu', 'haber-sitesi' ); ?></a>
                </div>
            </section>

            <section class="front-sidebar-card front-sidebar-card--newsletter" aria-label="<?php esc_attr_e( 'Bülten', 'haber-sitesi' ); ?>">
                <div class="front-sidebar-card__header">
                    <h2 class="front-sidebar-card__title"><?php esc_html_e( 'Bültenimize Katılın', 'haber-sitesi' ); ?></h2>
                </div>
                <p class="front-sidebar-card__text"><?php esc_html_e( 'Günün en önemli başlıklarını her sabah e-posta kutunuza gönderelim.', 'haber-sitesi' ); ?></p>
                <form class="front-newsletter" action="<?php echo esc_url( home_url( '/bulten' ) ); ?>" method="get">
                    <label class="screen-reader-text" for="newsletter-email"><?php esc_html_e( 'E-posta adresiniz', 'haber-sitesi' ); ?></label>
                    <input id="newsletter-email" class="front-newsletter__field" type="email" name="email" placeholder="<?php echo esc_attr__( 'ornek@haber.com', 'haber-sitesi' ); ?>" required>
                    <button class="front-newsletter__submit" type="submit"><?php esc_html_e( 'Abone Ol', 'haber-sitesi' ); ?></button>
                </form>
            </section>
        </aside>
    </div>
</div>

<?php
get_footer();

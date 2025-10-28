<?php
/**
 * Anasayfa şablonu.
 *
 * @package Haber_Sitesi
 */

global $post;

get_header();

$featured_query = new WP_Query(
    [
        'posts_per_page' => 1,
        'meta_key'       => '_thumbnail_id',
        'ignore_sticky_posts' => false,
    ]
);
?>
<div class="container">
    <section class="hero">
        <div class="hero__featured">
            <?php if ( $featured_query->have_posts() ) : ?>
                <?php while ( $featured_query->have_posts() ) : $featured_query->the_post(); ?>
                    <article <?php post_class(); ?>>
                        <p class="card-meta"><?php echo esc_html( get_the_date() ); ?> · <?php echo wp_kses_post( get_the_category_list( ', ' ) ); ?></p>
                        <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                        <p><?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
                        <a class="button" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Haberi Oku', 'haber-sitesi' ); ?></a>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <h1><?php esc_html_e( 'Henüz içerik eklenmemiş.', 'haber-sitesi' ); ?></h1>
            <?php endif; ?>
        </div>
        <div class="hero__list">
            <?php
            $latest_query = new WP_Query(
                [
                    'posts_per_page' => 4,
                    'offset'         => 1,
                    'ignore_sticky_posts' => 1,
                ]
            );

            if ( $latest_query->have_posts() ) :
                while ( $latest_query->have_posts() ) :
                    $latest_query->the_post();
                    get_template_part( 'template-parts/content', 'excerpt' );
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </section>
</div>
<?php
$categories = get_categories( [ 'number' => 3 ] );
foreach ( $categories as $category ) :
    $category_query = new WP_Query(
        [
            'cat'            => $category->term_id,
            'posts_per_page' => 4,
        ]
    );
    if ( ! $category_query->have_posts() ) {
        continue;
    }
    ?>
    <section class="category-block">
        <div class="container">
            <div class="section-title">
                <h2><?php echo esc_html( $category->name ); ?></h2>
                <a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>"><?php esc_html_e( 'Tümünü Gör', 'haber-sitesi' ); ?></a>
            </div>
            <div class="card-grid">
                <?php
                while ( $category_query->have_posts() ) :
                    $category_query->the_post();
                    get_template_part( 'template-parts/content', 'excerpt' );
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </section>
<?php endforeach; ?>
<?php get_footer(); ?>

<?php get_header(); ?>

<main class="site-page">
    <?php
    if (have_posts()):
        while (have_posts()):
            the_post();
            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

                <!-- Layout riêng cho Trang Chủ -->
                <section class="home">
                    <!-- Gọi slider -->
                    <?php get_template_part('template-parts/slider'); ?>

                    <!-- Nội dung khác của trang chủ -->
                    <div class="home-content">
                        <!-- Shortcode slider -->
                        <?php echo do_shortcode('[smartslider3 slider="2"]'); ?>

                        <!-- Category ở trang chủ -->
                        <section class="products-section">
                            <div class="container">
                                <!-- Tiêu đề -->
                                <?php
                                $h2 = get_field('h2');
                                $p = get_field('p');

                                if ($h2) {
                                    echo '<h2 class="reveal">' . esc_html($h2) . '</h2>';
                                }
                                if ($p) {
                                    echo '<p class="reveal">' . esc_html($p) . '</p>';
                                }
                                ?>

                                <!-- Lưới sản phẩm -->
                                <div class="product-grid">
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <?php
                                        $image = get_field('image_' . $i);
                                        $name = get_field('name_' . $i);
                                        $link = get_field('link_' . $i);
                                        ?>

                                        <?php if ($image || $name): ?>
                                            <div class="product-item">
                                                <?php if ($link): ?><a href="<?php echo esc_url($link); ?>"><?php endif; ?>
                                                    <div class="product-image">
                                                        <?php
                                                        if ($image) {
                                                            if (is_numeric($image)) {
                                                                echo wp_get_attachment_image($image, 'full');
                                                            } elseif (is_array($image) && isset($image['url'])) {
                                                                echo '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($image['alt']) . '">';
                                                            } else {
                                                                echo '<img src="' . esc_url($image) . '" alt="">';
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="product-title"><?php echo esc_html($name); ?></div>
                                                    <?php if ($link): ?>
                                                    </a><?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>

                                <!-- Nút xem thêm -->
                                <?php
                                $link_button = get_field('link_button');
                                $name_button = get_field('name_button');
                                if ($link_button && $name_button): ?>
                                    <a href="<?php echo esc_url($link_button); ?>" class="btn btn-primary">
                                        <?php echo esc_html($name_button); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </section>
                    </div>
                </section>

            </article>
            <?php
        endwhile;
    endif;
    ?>
</main>

<?php get_footer(); ?>

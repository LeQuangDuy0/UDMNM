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
                                    <?php
                                    // nếu taxonomy của bạn có slug khác, thêm vào đây
                                    $PREFERRED_TAX = ['orion_cat', 'product_cat', 'danh_muc_san_pham', 'category'];

                                    for ($i = 1; $i <= 6; $i++):
                                        $image = get_field('image_' . $i);   // ID | array | URL
                                        $name = get_field('name_' . $i);   // text
                                        $link = get_field('link_' . $i);   // có thể là array/ID/term/slug
                                        $link_url = '';

                                        // --- Chuẩn hoá giá trị $link về 1 WP_Term (nếu có) ---
                                        $term_obj = null;

                                        // ACF taxonomy (multiple) có thể trả mảng nhiều phần tử → lấy phần tử đầu
                                        if (is_array($link) && isset($link[0])) {
                                            $link = $link[0];
                                        }

                                        if ($link instanceof WP_Term) {
                                            $term_obj = $link;
                                        } elseif (is_array($link)) {
                                            // một số cấu hình ACF trả array có term_id/ID/value
                                            if (isset($link['term_id'])) {
                                                $term_obj = get_term((int) $link['term_id']);
                                            } elseif (isset($link['ID'])) {
                                                $term_obj = get_term((int) $link['ID']);
                                            } elseif (isset($link['value'])) {
                                                $term_obj = get_term((int) $link['value']);
                                            }
                                        } elseif (is_numeric($link)) {
                                            $term_obj = get_term((int) $link);
                                        } elseif (is_string($link) && $link !== '') {
                                            // Trường hợp bạn dùng TEXT để nhập slug
                                            foreach ($PREFERRED_TAX as $tx) {
                                                $t = get_term_by('slug', $link, $tx);
                                                if ($t && !is_wp_error($t)) {
                                                    $term_obj = $t;
                                                    break;
                                                }
                                            }
                                        }

                                        if ($term_obj && !is_wp_error($term_obj)) {
                                            $u = get_term_link($term_obj);
                                            if (!is_wp_error($u))
                                                $link_url = $u;
                                        }
                                        // --- Hết chuẩn hoá link ---
                            
                                        // Nếu không có ảnh & không có tên thì bỏ qua item
                                        if (!$image && !$name)
                                            continue;
                                        ?>
                                        <div class="product-item">
                                            <?php if ($link_url): ?><a href="<?php echo esc_url($link_url); ?>"><?php endif; ?>

                                                <div class="product-image">
                                                    <?php
                                                    if ($image) {
                                                        if (is_numeric($image)) {
                                                            echo wp_get_attachment_image((int) $image, 'full');
                                                        } elseif (is_array($image) && !empty($image['url'])) {
                                                            $alt = isset($image['alt']) ? $image['alt'] : '';
                                                            echo '<img src="' . esc_url($image['url']) . '" alt="' . esc_attr($alt) . '">';
                                                        } else {
                                                            echo '<img src="' . esc_url($image) . '" alt="">';
                                                        }
                                                    } else {
                                                        // placeholder nhẹ
                                                        echo '<div style="width:160px;height:160px;background:#f3f4f6;border-radius:50%"></div>';
                                                    }
                                                    ?>
                                                </div>

                                                <?php if ($name): ?>
                                                    <div class="product-title"><?php echo esc_html($name); ?></div>
                                                <?php endif; ?>

                                                <?php if ($link_url): ?>
                                                </a><?php endif; ?>
                                        </div>
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

                <?php
                // Chèn slider Tin tức 
                echo do_shortcode('[home_news_swiper]');
                ?>
                <section class="fanpage-section">
                    <div class="container">
                        <h2 class="fanpage-title">LIÊN HỆ FANPAGE</h2>
                        <p class="fanpage-subtitle">Hãy kết nối với chúng tôi !</p>

                        <div class="fanpage-grid">
                            <?php
                            // ===== Facebook =====
                            $fb_img = get_field('fanpage_fb_image'); // ID ảnh
                            $fb_url = get_field('fanpage_fb_url');
                            $fb_img_url = $fb_img ? wp_get_attachment_url($fb_img) : '';

                            if ($fb_img_url && $fb_url): ?>
                                <a href="<?php echo esc_url($fb_url); ?>" class="fanpage-item" target="_blank">
                                    <div class="fanpage-img" style="background-image:url('<?php echo esc_url($fb_img_url); ?>')">
                                        <span class="fanpage-label">Facebook</span>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <?php
                            // ===== YouTube =====
                            $yt_img = get_field('fanpage_yt_image');
                            $yt_url = get_field('fanpage_yt_url');
                            $yt_img_url = $yt_img ? wp_get_attachment_url($yt_img) : '';

                            if ($yt_img_url && $yt_url): ?>
                                <a href="<?php echo esc_url($yt_url); ?>" class="fanpage-item" target="_blank">
                                    <div class="fanpage-img" style="background-image:url('<?php echo esc_url($yt_img_url); ?>')">
                                        <span class="fanpage-label">YouTube</span>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <?php
                            // ===== Instagram =====
                            $ig_img = get_field('fanpage_ig_image');
                            $ig_url = get_field('fanpage_ig_url');
                            $ig_img_url = $ig_img ? wp_get_attachment_url($ig_img) : '';

                            if ($ig_img_url && $ig_url): ?>
                                <a href="<?php echo esc_url($ig_url); ?>" class="fanpage-item" target="_blank">
                                    <div class="fanpage-img" style="background-image:url('<?php echo esc_url($ig_img_url); ?>')">
                                        <span class="fanpage-label">Instagram</span>
                                    </div>
                                </a>
                            <?php endif; ?>

                            <?php
                            // ===== Zalo =====
                            $zalo_img = get_field('fanpage_zalo_image');
                            $zalo_url = get_field('fanpage_zalo_url');
                            $zalo_img_url = $zalo_img ? wp_get_attachment_url($zalo_img) : '';

                            if ($zalo_img_url && $zalo_url): ?>
                                <a href="<?php echo esc_url($zalo_url); ?>" class="fanpage-item" target="_blank">
                                    <div class="fanpage-img" style="background-image:url('<?php echo esc_url($zalo_img_url); ?>')">
                                        <span class="fanpage-label">Zalo</span>
                                    </div>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </article>
            <?php
        endwhile;
    endif;
    ?>
</main>

<?php get_footer(); ?>
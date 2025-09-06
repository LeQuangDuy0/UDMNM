<?php get_header(); ?>
<!-- Gọi header -->

<main class="site-page">
    <?php
    if (have_posts()):
        while (have_posts()):
            the_post();
            ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <!-- Tiêu đề trang -->
                <h1 class="page-title"><?php the_title(); ?></h1>

                <!-- Nội dung trang (nhập từ admin) -->
                <div class="page-content">
                    <?php the_content(); ?>
                </div>

                <!-- ============================= -->
                <!-- PHẦN CUSTOM CHO TỪNG TRANG   -->
                <!-- ============================= -->



                <?php if (is_page('trang-chu')): ?>
                    <!-- Layout riêng cho Trang Chủ -->
                    <section class="home">
                        <!-- Gọi slider -->
                        <?php get_template_part('template-parts/slider'); ?>

                        <!-- Nội dung khác của trang chủ -->
                        <div class="home-content">
                            <h2>Chào mừng đến với website!</h2>
                            <p>Đây là phần nội dung giới thiệu dưới slide.</p>
                        </div>
                    </section>

                <?php elseif (is_page('ve-chung-toi')): ?>
                    <!-- Layout riêng cho Trang Về Chúng Tôi -->
                    <section class="about-us">

                    </section>

                <?php elseif (is_page('gioi-thieu')): ?>
                    <!-- Layout riêng cho Trang Giới Thiệu -->
                    <section class="introduction">

                    </section>

                    </section>
                <?php elseif (is_page('lich-su')): ?>
                    <!-- Layout riêng cho Trang Lịch sử -->
                    <section class="history">

                    </section>

                <?php elseif (is_page('san-pham')): ?>
                    <!-- Layout riêng cho Trang Sản Phẩm -->
                    <section class="products">

                    </section>

                <?php elseif (is_page('tin-tuc')): ?>
                    <!-- Layout riêng cho Trang Tin Tức -->
                    <section class="news">

                    </section>

                <?php elseif (is_page('thong-cao-bao-chi')): ?>
                    <!-- Layout riêng cho Trang Thông Cáo Báo Chí -->
                    <section class="Press-release">

                    </section>

                <?php elseif (is_page('thong-tin-san-pham')): ?>
                    <!-- Layout riêng cho Trang Thông Tin Sản Phẩm -->
                    <section class="Product-Information">

                    </section>

                <?php elseif (is_page('quan-he-cong-dong')): ?>
                    <!-- Layout riêng cho Trang Quan Hệ Cộng Đồng -->
                    <section class="Community-Relations">

                    </section>

                <?php elseif (is_page('dao-duc-kinh-doanh')): ?>
                    <!-- Layout riêng cho Trang Đạo Đức Kinh Doanh -->
                    <section class="Business-Ethics">

                    </section>

                <?php elseif (is_page('hoat-dong-xa-hoi')): ?>
                    <!-- Layout riêng cho Trang Hoạt Động Xã Hội -->
                    <section class="Social-Activities">

                    </section>

                <?php elseif (is_page('lien-he')): ?>
                    <!-- Layout riêng cho Trang Liên Hệ -->
                    <section class="Contact">

                    </section>

                <?php elseif (is_page('lien-he-chung')): ?>
                    <!-- Layout riêng cho Trang Liên Hệ Chung -->
                    <section class="General-Contact">

                    </section>

                <?php elseif (is_page('lien-he-nha-may')): ?>
                    <!-- Layout riêng cho Trang Liên Hệ Nhà Máy -->
                    <section class="Factory-Contact">

                    </section>

                <?php elseif (is_page('yeu-cau-hop-tac')): ?>
                    <!-- Layout riêng cho Trang Yêu Cầu Hợp Tác -->
                    <section class="Cooperation-Request">

                    </section>

                <?php elseif (is_page('tuyen-dung')): ?>
                    <!-- Layout riêng cho Trang Tuyển Dụng -->
                    <section class="    Recruitment">

                    </section>

                <?php elseif (is_page('quy-trinh-tuyen-dung')): ?>
                    <!-- Layout riêng cho Trang Quy Trình Tuyển Dụng -->
                    <section class="Recruitment-Process">

                    </section>

                <?php elseif (is_page('viec-lam')): ?>
                    <!-- Layout riêng cho Trang VIệc Làm -->
                    <section class="Recruitment-Job">

                    </section>

                <?php elseif (is_page('global-business')): ?>
                    <!-- Layout riêng cho Trang GLOBAL BUSINESS -->
                    <section class="Global-Business">

                    </section>

                <?php elseif (is_page('orion-global')): ?>
                    <!-- Layout riêng cho Trang ORION GLOBAL -->
                    <section class="Orion-Global">

                    </section>

                <?php elseif (is_page('orion-products')): ?>
                    <!-- Layout riêng cho Trang ORION PRODUCTS -->
                    <section class="Orion-Products">

                    </section>

                <?php elseif (is_page('orion-partnership')): ?>
                    <!-- Layout riêng cho Trang ORION PASTNERSHIP -->
                    <section class="Orion-Partnership">

                    </section>

                <?php elseif (is_page('contact-us')): ?>
                    <!-- Layout riêng cho Trang CONTACT US -->
                    <section class="Contact-Us">

                    </section>


                <?php endif; ?>
            </article>
            <?php
        endwhile;
    endif;
    ?>
</main>

<?php get_footer(); ?>
<!-- Gọi footer -->
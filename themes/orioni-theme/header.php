<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header class="site-header">
        <div class="top-header">
            <!-- Logo -->
            <div class="site-logo">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/img/logo1a.jpg"
                        alt="<?php bloginfo('name'); ?>">
                </a>
            </div>

            <!-- Ngôn ngữ + nút menu khi ở mobile-->
            <div class="header-right"></div>
            <div class="lang-switcher">
                <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/vn.jpg" alt="VN"> VN</a>
                <span class="separator">|</span>
                <a href="#"><img src="<?php echo get_template_directory_uri(); ?>/assets/img/uk.jpg" alt="EN"> EN</a>
            </div>
            <button class="menu-toggle" aria-label="Mở menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
        </div>
        <!-- Thanh menu -->
        <div class="menu-bar">
            <nav class="main-navigation" id="site-navigation">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'menu',
                    'fallback_cb' => false
                ));
                ?>
            </nav>
        </div>
    </header>
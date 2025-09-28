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
<div class="header-right">
  <?php if ( function_exists('pll_the_languages') ) : 
    $langs = pll_the_languages([
      'raw'           => 1,   // lấy mảng để tự render
      'hide_current'  => 0,   // vẫn hiện ngôn ngữ hiện tại
      'hide_if_empty' => 0,
      'force_home'    => 1,   // nếu trang chưa có bản dịch -> trỏ về trang chủ của ngôn ngữ đó
    ]);
    $current = function_exists('pll_current_language') ? pll_current_language('slug') : '';
    // map slug -> tên file cờ trong /assets/img/
    $flag_map = [
      'vi' => 'vn.jpg',
      'en' => 'uk.jpg',
    ];
  ?>
    <nav class="lang-switcher" aria-label="Language switcher">
      <?php foreach ($langs as $slug => $lang) :
        $flag_file = isset($flag_map[$slug]) ? $flag_map[$slug] : '';
        $flag_src  = $flag_file ? get_template_directory_uri() . '/assets/img/' . $flag_file : '';
        $active    = $slug === $current ? ' is-active' : '';
      ?>
        <a class="lang-item <?php echo esc_attr($slug . $active); ?>"
           href="<?php echo esc_url($lang['url']); ?>"
           lang="<?php echo esc_attr($slug); ?>" hreflang="<?php echo esc_attr($slug); ?>">
          <?php if ($flag_src): ?>
            <img src="<?php echo esc_url($flag_src); ?>" alt="<?php echo esc_attr($lang['name']); ?>">
          <?php endif; ?>
          <span><?php echo esc_html(strtoupper($slug)); ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  <?php else: ?>
    <?php pll_the_languages(['show_flags'=>1,'show_names'=>1,'hide_current'=>0]); ?>
  <?php endif; ?>
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
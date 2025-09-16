<?php
if (!defined('ABSPATH')) {
    exit; // Không cho truy cập trực tiếp
}

// Kích hoạt hỗ trợ theme
function orioni_theme_setup()
{
    // Hỗ trợ logo tùy chỉnh
    add_theme_support('custom-logo', array(
        'height' => 60,
        'width' => 200,
        'flex-width' => true,
        'flex-height' => true,
    ));

    // Hỗ trợ title-tag
    add_theme_support('title-tag');

    // Hỗ trợ post thumbnail
    add_theme_support('post-thumbnails');

    // Đăng ký menu header và fooer
    register_nav_menus(array(
        'primary' => __('Main Menu', 'orioni-theme'),
        'footer' => __('Footer Menu', 'orioni-theme'),

    ));
}
add_action('after_setup_theme', 'orioni_theme_setup');

// Nạp CSS và JS
// Nạp CSS và JS của theme
function orioni_enqueue_assets()
{
    // CSS chính của theme (style.css ở root theme)
    wp_enqueue_style(
        'orioni-style',
        get_stylesheet_uri(),
        array(),
        wp_get_theme()->get('Version')
    );

    // CSS custom (main.css trong assets/css)
    wp_enqueue_style(
        'orioni-main',
        get_template_directory_uri() . '/assets/css/main.css',
        array('orioni-style'), // load sau style.css
        wp_get_theme()->get('Version')
    );

    // JS custom (main.js trong assets/js)
   wp_enqueue_script(
    'orioni-main',
    get_template_directory_uri() . '/assets/js/main.js',
    array('jquery', 'swiper-js'), 
    wp_get_theme()->get('Version'),
    true
);


    // Swiper CSS
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css');
    // Swiper JS
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), null, true);


    
}
add_action('wp_enqueue_scripts', 'orioni_enqueue_assets');



// Tạo Options Page cho ACF nếu có
add_action('init', function () {
  if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
      'page_title' => 'Cài đặt giao diện',
      'menu_title' => 'Cài đặt giao diện',
      'menu_slug'  => 'theme-settings',
      'capability' => 'edit_posts',
      'redirect'   => false,
      'position'   => 61,
      'icon_url'   => 'dashicons-admin-customizer',
    ]);
  }
});


add_action('init', function () {
  // CPT: Sản phẩm trưng bày (không có archive)
  register_post_type('orion_product', [
    'labels' => ['name'=>'Sản phẩm', 'singular_name'=>'Sản phẩm'],
    'public' => true,
    'has_archive' => false,                  // quan trọng: để không tranh URL với page /san-pham
    'rewrite' => ['slug' => 'san-pham'],     // single: /san-pham/ten-bai
    'menu_icon' => 'dashicons-products',
    'supports' => ['title','editor','thumbnail','excerpt'],
    'show_in_rest' => true
  ]);

  // Taxonomy: Danh mục
  register_taxonomy('orion_cat', ['orion_product'], [
    'labels' => ['name'=>'Danh mục sản phẩm','singular_name'=>'Danh mục'],
    'hierarchical' => true,
    'rewrite' => ['slug' => 'san-pham/danh-muc'], // để có URL đẹp nếu cần
    'show_in_rest' => true
  ]);
});

// Enqueue CSS của bạn
add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style(
    'orioni-main',
    get_template_directory_uri() . '/assets/css/main.css',
    [],
    filemtime(get_template_directory() . '/assets/css/main.css')
  );
});

// Fix canonical khi phân trang cho Page (có ?cat)
add_filter('redirect_canonical', function($redirect, $request){
  if (is_page() && (get_query_var('paged')>1 || get_query_var('page')>1)) return false;
  return $redirect;
}, 10, 2);

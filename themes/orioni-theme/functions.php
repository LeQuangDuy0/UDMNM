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
function orioni_enqueue_assets()
{
    wp_enqueue_style('orioni-style', get_stylesheet_uri(), array(), '1.0');
    wp_enqueue_style('orioni-main', get_template_directory_uri() . '/assets/css/main.css', array(), '1.0');
    wp_enqueue_script('orioni-main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'orioni_enqueue_assets');



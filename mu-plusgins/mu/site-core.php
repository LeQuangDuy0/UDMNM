<?php
if (!defined('ABSPATH')) exit;

/**
 * Đăng ký Custom Post Type ví dụ: "Notes"
 * – Đặt trong MU để không mất khi đổi theme.
 */
add_action('init', function () {
    register_post_type('note', [
        'labels' => [
            'name'          => 'Notes',
            'singular_name' => 'Note',
        ],
        'public'       => true,
        'show_in_rest' => true,   // bật Gutenberg/REST
        'menu_icon'    => 'dashicons-welcome-write-blog',
        'supports'     => ['title', 'editor', 'author', 'revisions'],
        'has_archive'  => false,
        'rewrite'      => ['slug' => 'notes'],
    ]);
});

/**
 * Enqueue style/script dùng toàn site (nhẹ, cần thiết)
 */
add_action('wp_enqueue_scripts', function () {
    // Ví dụ: CSS nhỏ cho block
    wp_register_style('mu-core', content_url('/mu-plugins/assets/mu-core.css'), [], '1.0');
    wp_enqueue_style('mu-core');
});


/**
 * Đăng ký CPT trước (nếu bạn cần gắn taxonomy vào CPT này)
 */
add_action('init', function () {
    register_post_type('note', [
        'labels' => ['name' => 'Notes', 'singular_name' => 'Note'],
        'public' => true,
        'show_in_rest' => true,
        'supports' => ['title','editor','author','revisions'],
        'has_archive' => false,
        'rewrite' => ['slug' => 'notes'],
        'menu_icon' => 'dashicons-welcome-write-blog',
    ]);
}, 5); // Ưu tiên thấp (chạy SỚM) để taxonomy có thể gắn vào CPT này ở bước sau

/**
 * Đăng ký Taxonomy
 */
add_action('init', function () {

    // Ví dụ 1: taxonomy phân cấp cho post (giống Category)
    register_taxonomy('press_release', ['post'], [
        'labels' => [
            'name'          => 'Thông cáo Báo chí',
            'singular_name' => 'Thông cáo Báo chí',
        ],
        'public'            => true,
        'hierarchical'      => true,           // true = dạng cây
        'show_ui'           => true,
        'show_in_rest'      => true,           // để dùng Gutenberg/REST
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'thong-cao', 'with_front' => false],
    ]);

    // Ví dụ 2: taxonomy không phân cấp (giống Tag) gắn vào CPT 'note'
    register_taxonomy('note_tag', ['note'], [
        'labels' => [
            'name'          => 'Note Tags',
            'singular_name' => 'Note Tag',
        ],
        'public'            => true,
        'hierarchical'      => false,          // false = dạng tag
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'note-tag', 'with_front' => false],
    ]);

}, 10); // c
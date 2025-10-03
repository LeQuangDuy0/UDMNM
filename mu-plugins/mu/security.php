<?php
// Nạp sớm, không echo/HTML ở đây.
if (!defined('ABSPATH')) exit;

/**
 * Tắt trình sửa file trong WP-Admin (Appearance > Theme/Plugin Editor)
 */
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

/**
 * Buộc dùng HTTPS ở front-end (nếu site có SSL)
 * – Tránh redirect loop: chỉ áp dụng khi không phải CLI và không phải admin-ajax
 */
add_action('template_redirect', function () {
    if (!is_ssl() && !is_admin() && php_sapi_name() !== 'cli') {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        wp_safe_redirect('https://' . $host . $uri, 301);
        exit;
    }
});

/**
 * Chặn XML-RPC nếu không dùng (giảm tấn công brute force)
 */
add_filter('xmlrpc_enabled', '__return_false');

/**
 * Ẩn phiên bản WordPress ở front-end
 */
remove_action('wp_head', 'wp_generator');

/**
 * Giới hạn phiên bản REST cho khách (ví dụ chặn /wp-json/ liệt kê user)
 */
add_filter('rest_endpoints', function ($endpoints) {
    if (is_user_logged_in()) return $endpoints;
    unset($endpoints['/wp/v2/users']);
    unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    return $endpoints;
});

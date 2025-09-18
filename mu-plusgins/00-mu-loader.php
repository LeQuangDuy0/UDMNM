<?php
/**
 * Plugin Name: MU Loader
 * Description: Loader cho các MU modules trong /mu-plugins/mu/.
 * Author: Your Name
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

$base = __DIR__ . '/mu';

// Tự động require tất cả file .php trong /mu theo thứ tự tên file
foreach (glob($base . '/*.php') as $file) {
    require_once $file;
}

<?php
/* Template Name: About Page */
$first = get_page_by_path('ve-chung-toi/gioi-thieu');
if ($first && !is_wp_error($first)) {
  wp_redirect( get_permalink($first->ID), 301 );
  exit;
}

//làm trang cha cho 2 trang con có thể chung trang và chuyển hướng về trang con đầu tiên
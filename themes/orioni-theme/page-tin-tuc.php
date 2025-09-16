<?php
/* Template Name: News */
$first = get_page_by_path('tin-tuc/thong-cao-bao-chi');
if ($first && !is_wp_error($first)) {
  wp_redirect( get_permalink($first->ID), 301 );
  exit;
}

//làm trang cha cho 2 trang con có thể chung trang và chuyển hướng về trang con đầu tiên
<?php
if (!defined('ABSPATH')) exit;

/* =========================================================
 * THEME SETUP
 * =======================================================*/
function orioni_theme_setup() {
  add_theme_support('custom-logo', [
    'height' => 60, 'width' => 200, 'flex-width' => true, 'flex-height' => true,
  ]);
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');

  register_nav_menus([
    'primary' => __('Main Menu', 'orioni-theme'),
    'footer'  => __('Footer Menu', 'orioni-theme'),
  ]);
}
add_action('after_setup_theme', 'orioni_theme_setup');


/* =========================================================
 * ENQUEUE ASSETS (CSS/JS)
 *  - Gộp fontawesome vào cùng hook, chỉ load Swiper ở trang chủ
 * =======================================================*/
function orioni_enqueue_assets() {
  // style.css
  wp_enqueue_style(
    'orioni-style',
    get_stylesheet_uri(),
    [],
    wp_get_theme()->get('Version')
  );

  // CSS chính
  wp_enqueue_style(
    'orioni-main',
    get_template_directory_uri() . '/assets/css/main.css',
    ['orioni-style'],
    filemtime(get_template_directory() . '/assets/css/main.css')
  );

  // Font Awesome
  wp_enqueue_style(
    'font-awesome',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    [],
    '6.4.0'
  );

  // Chỉ trang chủ mới cần Swiper
  if (is_front_page()) {
    wp_register_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11');
    wp_register_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11', true);
    wp_enqueue_style('swiper');
    wp_enqueue_script('swiper');
  }

  // JS của theme
  $deps = ['jquery'];
  if (is_front_page()) $deps[] = 'swiper';

  wp_enqueue_script(
    'orioni-main',
    get_template_directory_uri() . '/assets/js/main.js',
    $deps,
    filemtime(get_template_directory() . '/assets/js/main.js'),
    true
  );
}
add_action('wp_enqueue_scripts', 'orioni_enqueue_assets');


/* =========================================================
 * ACF Options Page (nếu có ACF)
 * =======================================================*/
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


/* =========================================================
 * CPT SẢN PHẨM TRƯNG BÀY (tránh trùng URL với trang /san-pham)
 *  - CHỈ đăng ký 1 lần
 * =======================================================*/
add_action('init', function () {
  register_post_type('orion_product', [
    'labels' => ['name' => 'Sản phẩm', 'singular_name' => 'Sản phẩm'],
    'public' => true,
    'has_archive' => false, // dùng Page để list
    'rewrite' => ['slug' => 'san-pham-item', 'with_front' => false], // slug riêng
    'menu_icon' => 'dashicons-products',
    'supports' => ['title','editor','thumbnail','excerpt'],
    'show_in_rest' => true,
  ]);

  register_taxonomy('orion_cat', ['orion_product'], [
    'labels' => ['name' => 'Danh mục sản phẩm', 'singular_name' => 'Danh mục'],
    'hierarchical' => true,
    'rewrite' => ['slug' => 'san-pham/danh-muc'],
    'show_in_rest' => true,
  ]);
});


/* =========================================================
 * Fix canonical khi phân trang Page (tránh redirect sai)
 * =======================================================*/
add_filter('redirect_canonical', function ($redirect) {
  if (is_page() && (get_query_var('paged') > 1 || get_query_var('page') > 1)) return false;
  return $redirect;
}, 10);


/* =========================================================
 * TAXONOMY CHO TIN TỨC: Thông cáo Báo chí / Thông tin Sản phẩm
 * =======================================================*/
add_action('init', function () {
  register_taxonomy('press_release', ['post'], [
    'labels' => [
      'name' => 'Thông cáo Báo chí', 'singular_name' => 'Thông cáo Báo chí', 'menu_name' => 'Thông cáo Báo chí',
    ],
    'public' => true, 'hierarchical' => true, 'show_ui' => true,
    'show_in_rest' => true, 'show_admin_column' => true,
    'rewrite' => false, // tránh đụng URL với Page
  ]);

  register_taxonomy('product_info', ['post'], [
    'labels' => [
      'name' => 'Thông tin Sản phẩm', 'singular_name' => 'Thông tin Sản phẩm', 'menu_name' => 'Thông tin Sản phẩm',
    ],
    'public' => true, 'hierarchical' => true, 'show_ui' => true,
    'show_in_rest' => true, 'show_admin_column' => true,
    'rewrite' => ['slug' => 'thong-tin-san-pham', 'with_front' => false],
  ]);
});

// tạo term mặc định
add_action('init', function () {
  $defs = [
    'press_release' => ['Tất cả Thông cáo', 'press-all'],
    'product_info'  => ['Tất cả Sản phẩm', 'product-all'],
  ];
  foreach ($defs as $tax => [$name, $slug]) {
    if (!term_exists($slug, $tax)) wp_insert_term($name, $tax, ['slug' => $slug]);
  }
});


/* =========================================================
 * HELPER: Ảnh card cho bài post
 * =======================================================*/
function orioni_get_post_card_image_html($post_id = 0, $size = 'large') {
  $post_id = $post_id ?: get_the_ID();

  // 1) ACF các key phổ biến (ID/array/URL)
  if (function_exists('get_field')) {
    $keys = ['card_image','thumbnail','thumbnail_image','news_image','banner','hero_image','image'];
    foreach ($keys as $k) {
      $v = get_field($k, $post_id);
      if (empty($v)) continue;

      if (is_numeric($v)) return wp_get_attachment_image((int)$v, $size, false, ['loading' => 'lazy']);
      if (is_array($v)) {
        if (!empty($v['ID'])) return wp_get_attachment_image((int)$v['ID'], $size, false, ['loading' => 'lazy']);
        if (!empty($v['id'])) return wp_get_attachment_image((int)$v['id'], $size, false, ['loading' => 'lazy']);
        if (!empty($v['url'])) {
          $alt = !empty($v['alt']) ? $v['alt'] : get_the_title($post_id);
          return '<img src="'.esc_url($v['url']).'" alt="'.esc_attr($alt).'" loading="lazy">';
        }
      }
      if (is_string($v) && filter_var($v, FILTER_VALIDATE_URL)) {
        return '<img src="'.esc_url($v).'" alt="'.esc_attr(get_the_title($post_id)).'" loading="lazy">';
      }
    }
  }

  // 2) Featured image
  if (has_post_thumbnail($post_id)) {
    return get_the_post_thumbnail($post_id, $size, ['loading' => 'lazy']);
  }

  // 3) Attachment đầu tiên
  $media = get_attached_media('image', $post_id);
  if (!empty($media)) {
    $first = reset($media);
    return wp_get_attachment_image($first->ID, $size, false, ['loading' => 'lazy']);
  }

  // 4) Ảnh đầu tiên trong nội dung
  $content = get_post_field('post_content', $post_id);
  if ($content && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) {
    return '<img src="'.esc_url($m[1]).'" alt="'.esc_attr(get_the_title($post_id)).'" loading="lazy">';
  }

  // 5) Placeholder
  $fallback = get_template_directory_uri().'/assets/img/news-placeholder.jpg';
  return '<img class="hn-ph" src="'.esc_url($fallback).'" alt="" loading="lazy">';
}


/* =========================================================
 * RENDER GRID DÙNG CHUNG
 * =======================================================*/
function _or_render_posts_grid(WP_Query $q) {
  ob_start(); ?>
  <div class="or-news">
    <div class="or-news-grid">
      <?php if ($q->have_posts()): while ($q->have_posts()): $q->the_post(); ?>
        <article class="or-news-card">
          <a class="thumb" href="<?php the_permalink(); ?>">
            <?php echo orioni_get_post_card_image_html(get_the_ID(), 'large'); ?>
          </a>
          <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
          <?php
          $subtitle = function_exists('get_field') ? trim((string)get_field('subtitle')) : '';
          if ($subtitle === '') {
            $ex = wp_strip_all_tags(get_the_excerpt());
            if ($ex) $subtitle = wp_trim_words($ex, 22);
          }
          if ($subtitle !== '') echo '<p class="subtitle">'.esc_html($subtitle).'</p>';
          ?>
          <time class="date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
            <?php echo esc_html(get_the_date()); ?>
          </time>
        </article>
      <?php endwhile; wp_reset_postdata(); else: ?>
        <p>Chưa có bài viết.</p>
      <?php endif; ?>
    </div>
  </div>
  <?php return ob_get_clean();
}


/* =========================================================
 * SHORTCODES GRID
 *  - [press_grid per_page="6"]
 *  - [productinfo_grid per_page="6"]
 * =======================================================*/
add_shortcode('press_grid', function ($atts) {
  $a = shortcode_atts(['per_page'=>6, 'orderby'=>'date', 'order'=>'DESC'], $atts);
  $q = new WP_Query([
    'post_type'=>'post','posts_per_page'=>(int)$a['per_page'],
    'orderby'=>sanitize_text_field($a['orderby']),'order'=>sanitize_text_field($a['order']),
    'tax_query'=>[['taxonomy'=>'press_release','operator'=>'EXISTS']],
  ]);
  return _or_render_posts_grid($q);
});

add_shortcode('productinfo_grid', function ($atts) {
  $a = shortcode_atts(['per_page'=>6, 'orderby'=>'date', 'order'=>'DESC'], $atts);
  $q = new WP_Query([
    'post_type'=>'post','posts_per_page'=>(int)$a['per_page'],
    'orderby'=>sanitize_text_field($a['orderby']),'order'=>sanitize_text_field($a['order']),
    'tax_query'=>[['taxonomy'=>'product_info','operator'=>'EXISTS']],
  ]);
  return _or_render_posts_grid($q);
});


/* =========================================================
 * SHORTCODE: [home_news_swiper]
 * =======================================================*/
add_shortcode('home_news_swiper', function () {
  $q = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => 10,
    'orderby' => 'date',
    'order' => 'DESC',
  ]);

  $news_page = get_page_by_path('tin-tuc');
  $all_url = $news_page ? get_permalink($news_page) : get_permalink(get_option('page_for_posts'));

  ob_start(); ?>
  <section class="home-news">
    <div class="hn-head">
      <h2 class="hn-title">TIN TỨC</h2>
      <p class="hn-sub">Cập nhật những tin tức mới nhất cùng Orion</p>
    </div>

    <div class="news-swiper swiper">
      <div class="swiper-wrapper">
        <?php if ($q->have_posts()): while ($q->have_posts()): $q->the_post(); ?>
          <div class="swiper-slide">
            <article class="hn-card">
              <a class="hn-link" href="<?php the_permalink(); ?>">
                <div class="hn-thumb"><?php echo orioni_get_post_card_image_html(get_the_ID(), 'large'); ?></div>
                <h3 class="hn-name"><?php the_title(); ?></h3>
                <time class="hn-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                  <?php echo esc_html(get_the_date('d M, Y')); ?>
                </time>
              </a>
            </article>
          </div>
        <?php endwhile; wp_reset_postdata(); endif; ?>
      </div>
      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
      <div class="swiper-pagination"></div>
    </div>

    <?php if ($all_url): ?>
      <div class="hn-more"><a class="hn-morebtn" href="<?php echo esc_url($all_url); ?>">XEM TẤT CẢ</a></div>
    <?php endif; ?>
  </section>
  <?php return ob_get_clean();
});


/* =========================================================
 * QUAN HỆ CỘNG ĐỒNG (CPT + TAXONOMY)
 *  - Bạn render lưới bằng 2 Page: dao-duc-kinh-doanh / hoat-dong-xa-hoi
 *  - Tắt archive & URL term để không đụng slug Page
 *  - Thêm submenu + filter trong Admin
 * =======================================================*/

/** 1) CPT & Taxonomy (tắt archive & URL term) */
add_action('init', function () {
  register_post_type('orioni_comm', [
    'labels' => [
      'name'=>'Quan hệ cộng đồng','singular_name'=>'Bài cộng đồng','menu_name'=>'Quan hệ cộng đồng',
      'add_new_item'=>'Thêm bài cộng đồng','edit_item'=>'Sửa bài cộng đồng','all_items'=>'Tất cả bài cộng đồng',
    ],
    'public'=>true,'show_ui'=>true,'show_in_menu'=>'edit.php',
    'supports'=>['title','editor','thumbnail','excerpt','revisions'],
    'has_archive'=>false, // không dùng /quan-he-cong-dong/
    'rewrite'=>['slug'=>'orioni-comm','with_front'=>false], // single slug riêng
    'show_in_rest'=>true,
  ]);

  register_taxonomy('community_topic', ['orioni_comm'], [
    'hierarchical'=>true,
    'labels'=>['name'=>'Chủ đề','singular_name'=>'Chủ đề','menu_name'=>'Chủ đề'],
    'show_ui'=>true,'show_admin_column'=>true,
    'rewrite'=>false, // không có URL term
    'show_in_rest'=>true,
  ]);
});

/** 2) Seed 2 term mặc định */
add_action('admin_init', function () {
  if (!get_option('orioni_comm_terms_seeded')) {
    foreach ([['Đạo đức kinh doanh','dao-duc-kinh-doanh'],['Hoạt động xã hội','hoat-dong-xa-hoi']] as $p) {
      if (!term_exists($p[1],'community_topic')) wp_insert_term($p[0],'community_topic',['slug'=>$p[1]]);
    }
    update_option('orioni_comm_terms_seeded', 1);
  }
});

/** 3) Submenu dưới Posts */
add_action('admin_menu', function () {
  $cap='edit_posts';
  add_submenu_page('edit.php','Quan hệ cộng đồng','Quan hệ cộng đồng',$cap,'edit.php?post_type=orioni_comm');
  if ($t=get_term_by('slug','dao-duc-kinh-doanh','community_topic')) {
    add_submenu_page('edit.php','Đạo đức kinh doanh','— Đạo đức kinh doanh',$cap,'edit.php?post_type=orioni_comm&community_topic='.$t->term_id);
  }
  if ($t=get_term_by('slug','hoat-dong-xa-hoi','community_topic')) {
    add_submenu_page('edit.php','Hoạt động xã hội','— Hoạt động xã hội',$cap,'edit.php?post_type=orioni_comm&community_topic='.$t->term_id);
  }
});

/** 4) Dropdown + filter theo Chủ đề trong list */
add_action('restrict_manage_posts', function($ptype){
  if ($ptype!=='orioni_comm') return;
  $sel = isset($_GET['community_topic']) ? (int)$_GET['community_topic'] : 0;
  wp_dropdown_categories([
    'show_option_all'=>'Tất cả chủ đề','taxonomy'=>'community_topic','name'=>'community_topic',
    'orderby'=>'name','selected'=>$sel,'hierarchical'=>true,'hide_empty'=>false,
  ]);
});
add_filter('parse_query', function($q){
  if (!is_admin()) return;
  if ($q->get('post_type')==='orioni_comm' && !empty($_GET['community_topic'])) {
    $q->set('tax_query', [[
      'taxonomy'=>'community_topic','field'=>'term_id','terms'=>[(int)$_GET['community_topic']]
    ]]);
  }
});

/** 5) Nếu quên tick Chủ đề, tự gán "Đạo đức kinh doanh" */
add_action('save_post_orioni_comm', function($post_id){
  if (wp_is_post_revision($post_id)) return;
  $terms = wp_get_post_terms($post_id,'community_topic',['fields'=>'ids']);
  if (empty($terms)) {
    $d = get_term_by('slug','dao-duc-kinh-doanh','community_topic');
    if ($d && !is_wp_error($d)) wp_set_object_terms($post_id, [$d->term_id], 'community_topic', false);
  }
});

/** 6) Ẩn CPT khỏi Home/Search (vì render bằng Page) */
add_action('pre_get_posts', function($q){
  if (is_admin() || !$q->is_main_query()) return;
  if (is_singular('orioni_comm')) return; // cho phép single
  $pt = (array) ($q->get('post_type') ?: ['post']);
  $q->set('post_type', array_diff($pt, ['orioni_comm']));
});

/** 7) Polylang (nếu dùng) */
add_filter('pll_get_post_types', function($t){ $t['orioni_comm']='orioni_comm'; return $t; });
add_filter('pll_get_taxonomies', function($t){ $t['community_topic']='community_topic'; return $t; });

/** 8) Flush rewrite khi đổi theme (chỉ 1 lần) */
add_action('after_switch_theme', function(){ flush_rewrite_rules(); });

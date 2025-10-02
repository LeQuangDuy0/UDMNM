<?php
if (!defined('ABSPATH')) {
  exit;
}

/* ------------------------------------
 * THEME SETUP
 * ------------------------------------ */
function orioni_theme_setup()
{
  add_theme_support('custom-logo', [
    'height' => 60,
    'width' => 200,
    'flex-width' => true,
    'flex-height' => true,
  ]);
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');

  register_nav_menus([
    'primary' => __('Main Menu', 'orioni-theme'),
    'footer' => __('Footer Menu', 'orioni-theme'),
  ]);
}
add_action('after_setup_theme', 'orioni_theme_setup');


/* ------------------------------------
 * ENQUEUE CSS/JS (đã gộp – không còn trùng)
 * ------------------------------------ */
function orioni_enqueue_assets()
{
  // style.css (root theme)
  wp_enqueue_style(
    'orioni-style',
    get_stylesheet_uri(),
    [],
    wp_get_theme()->get('Version')
  );

  // CSS chính của theme
  wp_enqueue_style(
    'orioni-main',
    get_template_directory_uri() . '/assets/css/main.css',
    ['orioni-style'],
    filemtime(get_template_directory() . '/assets/css/main.css')
  );

  // Chỉ trang chủ mới cần Swiper
  if (is_front_page()) {
    // Swiper v11 – Duy nhất một phiên bản
    wp_register_style(
      'swiper',
      'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
      [],
      '11'
    );
    wp_register_script(
      'swiper',
      'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
      [],
      '11',
      true
    );
    wp_enqueue_style('swiper');
    wp_enqueue_script('swiper');
  }

  // JS của theme
  $deps = ['jquery'];
  if (is_front_page()) {
    $deps[] = 'swiper';
  }
  wp_enqueue_script(
    'orioni-main',
    get_template_directory_uri() . '/assets/js/main.js',
    $deps,
    filemtime(get_template_directory() . '/assets/js/main.js'),
    true
  );
}
add_action('wp_enqueue_scripts', 'orioni_enqueue_assets');

// FontAwesome 6.4.0
function load_fontawesome()
{
  wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'load_fontawesome');
// Dashicons (nếu cần)
register_post_type('orion_product', [
  'labels' => ['name' => 'Sản phẩm', 'singular_name' => 'Sản phẩm'],
  'public' => true,
  'has_archive' => false, // giữ false vì bạn dùng Page để list
  'rewrite' => [
    'slug' => 'san-pham-item',   // <-- đổi KHÁC 'san-pham'
    'with_front' => false
  ],
  'menu_icon' => 'dashicons-products',
  'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
  'show_in_rest' => true,
]);
add_action('init', function () {
  flush_rewrite_rules(); }, 99);

/* ------------------------------------
 * ACF Options Page (nếu có ACF)
 * ------------------------------------ */
add_action('init', function () {
  if (function_exists('acf_add_options_page')) {
    acf_add_options_page([
      'page_title' => 'Cài đặt giao diện',
      'menu_title' => 'Cài đặt giao diện',
      'menu_slug' => 'theme-settings',
      'capability' => 'edit_posts',
      'redirect' => false,
      'position' => 61,
      'icon_url' => 'dashicons-admin-customizer',
    ]);
  }
});


/* ------------------------------------
 * CPT & Taxonomy (ví dụ sản phẩm trưng bày)
 * ------------------------------------ */
add_action('init', function () {
  // CPT: Sản phẩm trưng bày (không archive để tránh đụng page /san-pham)
  register_post_type('orion_product', [
    'labels' => ['name' => 'Sản phẩm', 'singular_name' => 'Sản phẩm'],
    'public' => true,
    'has_archive' => false,
    'rewrite' => ['slug' => 'san-pham'],
    'menu_icon' => 'dashicons-products',
    'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
    'show_in_rest' => true,
  ]);

  // Taxonomy: Danh mục cho orion_product
  register_taxonomy('orion_cat', ['orion_product'], [
    'labels' => ['name' => 'Danh mục sản phẩm', 'singular_name' => 'Danh mục'],
    'hierarchical' => true,
    'rewrite' => ['slug' => 'san-pham/danh-muc'],
    'show_in_rest' => true,
  ]);
});


/* ------------------------------------
 * Fix canonical khi phân trang Page (có ?cat)
 * ------------------------------------ */
add_filter('redirect_canonical', function ($redirect, $request) {
  if (is_page() && (get_query_var('paged') > 1 || get_query_var('page') > 1))
    return false;
  return $redirect;
}, 10, 2);


/* ------------------------------------
 * Taxonomy quản lý bài viết Tin tức
 * ------------------------------------ */
add_action('init', function () {
  // Thông cáo Báo chí
  register_taxonomy('press_release', ['post'], [
    'labels' => [
      'name' => 'Thông cáo Báo chí',
      'singular_name' => 'Thông cáo Báo chí',
      'menu_name' => 'Thông cáo Báo chí',
    ],
    'public' => true,
    'hierarchical' => true,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    // tránh đụng URL với trang, bạn có thể bật lại nếu cần permalink riêng
    'rewrite' => false,
  ]);

  // Thông tin Sản phẩm
  register_taxonomy('product_info', ['post'], [
    'labels' => [
      'name' => 'Thông tin Sản phẩm',
      'singular_name' => 'Thông tin Sản phẩm',
      'menu_name' => 'Thông tin Sản phẩm',
    ],
    'public' => true,
    'hierarchical' => true,
    'show_ui' => true,
    'show_in_rest' => true,
    'show_admin_column' => true,
    'rewrite' => ['slug' => 'thong-tin-san-pham', 'with_front' => false],
  ]);
});

// Tạo sẵn 1 term mặc định cho mỗi taxonomy
add_action('init', function () {
  $defs = [
    'press_release' => ['Tất cả Thông cáo', 'press-all'],
    'product_info' => ['Tất cả Sản phẩm', 'product-all'],
  ];
  foreach ($defs as $tax => [$name, $slug]) {
    if (!term_exists($slug, $tax)) {
      wp_insert_term($name, $tax, ['slug' => $slug]);
    }
  }
});


/* ------------------------------------
 * HÀM RENDER GRID DÙNG CHUNG
 * ------------------------------------ */
function _or_render_posts_grid(WP_Query $q)
{
  ob_start(); ?>
  <div class="or-news">
    <div class="or-news-grid">
      <?php if ($q->have_posts()):
        while ($q->have_posts()):
          $q->the_post(); ?>
          <article class="or-news-card">
            <a class="thumb" href="<?php the_permalink(); ?>">
              <?php
              // 1) ACF 'card_image'
              $img_id = function_exists('get_field') ? (int) get_field('card_image') : 0;

              // 2) Featured Image
              if (!$img_id && has_post_thumbnail()) {
                $img_id = get_post_thumbnail_id();
              }

              if ($img_id) {
                echo wp_get_attachment_image($img_id, 'large', false, ['loading' => 'lazy']);
              } else {
                // 3) Ảnh đầu tiên trong nội dung
                $img_url = '';
                $content = get_the_content(null, false);
                if ($content && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) {
                  $img_url = $m[1];
                }
                if ($img_url) {
                  echo '<img src="' . esc_url($img_url) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy">';
                } else {
                  echo '<span class="ph" aria-hidden="true"></span>';
                }
              }
              ?>
            </a>

            <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

            <?php
            // subtitle ACF (nếu có) – fallback excerpt
            $subtitle = function_exists('get_field') ? trim((string) get_field('subtitle')) : '';
            if ($subtitle === '') {
              $excerpt = wp_strip_all_tags(get_the_excerpt());
              if ($excerpt)
                $subtitle = wp_trim_words($excerpt, 22);
            }
            if ($subtitle !== '') {
              echo '<p class="subtitle">' . esc_html($subtitle) . '</p>';
            }
            ?>

            <time class="date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
              <?php echo esc_html(get_the_date()); ?>
            </time>
          </article>
        <?php endwhile;
        wp_reset_postdata(); else: ?>
        <p>Chưa có bài viết.</p>
      <?php endif; ?>
    </div>
  </div>
  <?php
  return ob_get_clean();
}


/* ------------------------------------
 * SHORTCODE GRID THEO TAXONOMY
 * ------------------------------------ */
// [press_grid per_page="6"]
add_shortcode('press_grid', function ($atts) {
  $a = shortcode_atts([
    'per_page' => 6,
    'orderby' => 'date',
    'order' => 'DESC',
  ], $atts);

  $q = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => (int) $a['per_page'],
    'orderby' => sanitize_text_field($a['orderby']),
    'order' => sanitize_text_field($a['order']),
    'tax_query' => [['taxonomy' => 'press_release', 'operator' => 'EXISTS']],
  ]);

  return _or_render_posts_grid($q);
});

// [productinfo_grid per_page="6"]
add_shortcode('productinfo_grid', function ($atts) {
  $a = shortcode_atts([
    'per_page' => 6,
    'orderby' => 'date',
    'order' => 'DESC',
  ], $atts);

  $q = new WP_Query([
    'post_type' => 'post',
    'posts_per_page' => (int) $a['per_page'],
    'orderby' => sanitize_text_field($a['orderby']),
    'order' => sanitize_text_field($a['order']),
    'tax_query' => [['taxonomy' => 'product_info', 'operator' => 'EXISTS']],
  ]);

  return _or_render_posts_grid($q);
});

// Trả về HTML ảnh card cho bài post: ACF (banner, card_image, hero_image...) -> thumbnail -> ảnh trong nội dung -> placeholder
function orioni_get_post_card_image_html($post_id, $size = 'large')
{
  $img_id = 0;

  // 1) Ưu tiên ACF nhiều key
  if (function_exists('get_field')) {
    $acf_keys = ['banner', 'card_image', 'hero_image', 'news_image', 'thumbnail_image'];
    foreach ($acf_keys as $key) {
      $acf = get_field($key, $post_id);
      if (is_numeric($acf)) {
        $img_id = (int) $acf;
        break;
      }
      if (is_array($acf)) {
        if (!empty($acf['ID'])) {
          $img_id = (int) $acf['ID'];
          break;
        }
        if (!empty($acf['id'])) {
          $img_id = (int) $acf['id'];
          break;
        }
      }
    }
  }

  // 2) Ảnh đại diện
  if (!$img_id)
    $img_id = get_post_thumbnail_id($post_id);

  // 3) In theo ID nếu có
  if ($img_id) {
    return wp_get_attachment_image($img_id, $size, false, ['loading' => 'lazy']);
  }

  // 4) Ảnh đầu tiên trong nội dung
  $content = get_post_field('post_content', $post_id);
  if ($content && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) {
    return '<img src="' . esc_url($m[1]) . '" alt="' . esc_attr(get_the_title($post_id)) . '" loading="lazy">';
  }

  // 5) Placeholder
  return '<span class="hn-ph" aria-hidden="true"></span>';
}


// SHORTCODE: [home_news_swiper]
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
        <?php if ($q->have_posts()):
          while ($q->have_posts()):
            $q->the_post(); ?>
            <div class="swiper-slide">
              <article class="hn-card">
                <a class="hn-link" href="<?php the_permalink(); ?>">
                  <div class="hn-thumb">
                    <?php echo orioni_get_post_card_image_html(get_the_ID(), 'large'); ?>
                  </div>
                  <h3 class="hn-name"><?php the_title(); ?></h3>
                  <time class="hn-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                    <?php echo esc_html(get_the_date('d M, Y')); ?>
                  </time>
                </a>
              </article>
            </div>
          <?php endwhile;
          wp_reset_postdata(); endif; ?>
      </div>

      <div class="swiper-button-prev"></div>
      <div class="swiper-button-next"></div>
      <div class="swiper-pagination"></div>
    </div>

    <?php if ($all_url): ?>
      <div class="hn-more">
        <a class="hn-morebtn" href="<?php echo esc_url($all_url); ?>">XEM TẤT CẢ</a>
      </div>
    <?php endif; ?>
  </section>
  <?php
  return ob_get_clean();
});

// ================== ẢNH THUMB CHO TIN TỨC ==================
if (!function_exists('orioni_get_post_card_image_html')) {
  function orioni_get_post_card_image_html($post_id = 0, $size = 'large')
  {
    $post_id = $post_id ?: get_the_ID();

    // 1) ACF các key hay gặp (mọi return type: ID / array / URL)
    if (function_exists('get_field')) {
      $keys = [
        'card_image',
        'thumbnail',
        'thumbnail_image',
        'news_image',
        'banner',
        'hero_image',
        'image'
      ];
      foreach ($keys as $k) {
        $v = get_field($k, $post_id);
        if (empty($v))
          continue;

        // ID
        if (is_numeric($v)) {
          return wp_get_attachment_image((int) $v, $size, false, ['loading' => 'lazy']);
        }
        // Array
        if (is_array($v)) {
          if (!empty($v['ID']))
            return wp_get_attachment_image((int) $v['ID'], $size, false, ['loading' => 'lazy']);
          if (!empty($v['id']))
            return wp_get_attachment_image((int) $v['id'], $size, false, ['loading' => 'lazy']);
          if (!empty($v['url'])) {
            $alt = !empty($v['alt']) ? $v['alt'] : get_the_title($post_id);
            return '<img src="' . esc_url($v['url']) . '" alt="' . esc_attr($alt) . '" loading="lazy">';
          }
        }
        // URL string
        if (is_string($v) && filter_var($v, FILTER_VALIDATE_URL)) {
          return '<img src="' . esc_url($v) . '" alt="' . esc_attr(get_the_title($post_id)) . '" loading="lazy">';
        }
      }
    }

    // 2) Featured image
    if (has_post_thumbnail($post_id)) {
      return get_the_post_thumbnail($post_id, $size, ['loading' => 'lazy']);
    }

    // 3) Ảnh đính kèm (attachment) đầu tiên
    $media = get_attached_media('image', $post_id);
    if (!empty($media)) {
      $first = reset($media);
      return wp_get_attachment_image($first->ID, $size, false, ['loading' => 'lazy']);
    }

    // 4) Ảnh đầu tiên trong nội dung
    $content = get_post_field('post_content', $post_id);
    if ($content && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) {
      return '<img src="' . esc_url($m[1]) . '" alt="' . esc_attr(get_the_title($post_id)) . '" loading="lazy">';
    }

    // 5) Ảnh mặc định của theme (tạo file nếu muốn)
    $fallback = get_template_directory_uri() . '/assets/img/news-placeholder.jpg';
    return '<img class="hn-ph" src="' . esc_url($fallback) . '" alt="" loading="lazy">';
  }
}


/** QUAN HỆ CỘNG ĐỒNG: CPT + TAXONOMY (ẩn menu tổng) */
add_action('init', function () {
  // CPT
  register_post_type('orioni_comm', [
    'labels' => [
      'name'          => 'Quan hệ cộng đồng',
      'singular_name' => 'Bài cộng đồng',
      'add_new_item'  => 'Thêm bài cộng đồng',
      'edit_item'     => 'Sửa bài cộng đồng',
      'all_items'     => 'Tất cả bài cộng đồng',
    ],
    'public'        => true,
    'show_ui'       => true,
    'show_in_menu'  => false,     // ẩn mục tổng, chỉ hiển thị 2 submenu lọc
    'supports'      => ['title','editor','thumbnail','excerpt','revisions'],
    'has_archive'   => false,
    'rewrite'       => ['slug'=>'orioni-comm','with_front'=>false],
    'show_in_rest'  => true,
  ]);

  // Taxonomy
  register_taxonomy('community_topic', ['orioni_comm'], [
    'hierarchical'      => true,
    'labels'            => ['name'=>'Chủ đề','singular_name'=>'Chủ đề'],
    'show_ui'           => true,
    'show_admin_column' => true,
    'rewrite'           => false, // không tạo URL /quan-he-cong-dong/{term}
    'show_in_rest'      => true,
  ]);
});

// Tạo sẵn 2 term nếu chưa có
add_action('admin_init', function () {
  if (!get_option('orioni_comm_terms_seeded')) {
    foreach ([['Đạo đức kinh doanh','dao-duc-kinh-doanh'],['Hoạt động xã hội','hoat-dong-xa-hoi']] as $p) {
      if (!term_exists($p[1],'community_topic')) wp_insert_term($p[0],'community_topic',['slug'=>$p[1]]);
    }
    update_option('orioni_comm_terms_seeded', 1);
  }
});

// Chỉ thêm 2 submenu con dưới Posts
add_action('admin_menu', function () {
  $cap='edit_posts';
  if ($t=get_term_by('slug','dao-duc-kinh-doanh','community_topic')) {
    add_submenu_page('edit.php','Đạo đức kinh doanh','— Đạo đức kinh doanh',
      $cap,'edit.php?post_type=orioni_comm&community_topic='.$t->term_id);
  }
  if ($t=get_term_by('slug','hoat-dong-xa-hoi','community_topic')) {
    add_submenu_page('edit.php','Hoạt động xã hội','— Hoạt động xã hội',
      $cap,'edit.php?post_type=orioni_comm&community_topic='.$t->term_id);
  }
});

// Dropdown filter theo Chủ đề ở màn list
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

// Polylang (nếu dùng)
add_filter('pll_get_post_types', function($t){ $t['orioni_comm']='orioni_comm'; return $t; });
add_filter('pll_get_taxonomies', function($t){ $t['community_topic']='community_topic'; return $t; });
/** Helper ảnh (fallback gọn) */
function orioni_comm_card_image($post_id=0,$size='large'){
  $post_id = $post_id ?: get_the_ID();
  if (has_post_thumbnail($post_id)) return get_the_post_thumbnail($post_id,$size,['loading'=>'lazy']);
  $content = get_post_field('post_content',$post_id);
  if ($content && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i',$content,$m)) {
    return '<img src="'.esc_url($m[1]).'" alt="'.esc_attr(get_the_title($post_id)).'" loading="lazy">';
  }
  return '<span class="comm-ph" aria-hidden="true"></span>';
}

/** [community_grid topic="dao-duc-kinh-doanh" per_page="12" columns="2" gap="28" pagination="false"] */
add_shortcode('community_grid', function($atts){
  $a = shortcode_atts([
    'topic'      => '',         // slug hoặc ID của term
    'per_page'   => -1,         // -1 = tất cả
    'columns'    => 2,          // 1/2/3/4
    'gap'        => 28,         // khoảng cách (px)
    'orderby'    => 'date',
    'order'      => 'DESC',
    'pagination' => 'false'
  ], $atts);

  $tax = [];
  if ($a['topic']!=='') {
    $field = ctype_digit((string)$a['topic']) ? 'term_id' : 'slug';
    $tax = [[ 'taxonomy'=>'community_topic','field'=>$field,'terms'=> $field==='slug'?sanitize_title($a['topic']):(int)$a['topic'] ]];
  }

  $paged = max(1, (int)get_query_var('paged'));
  $q = new WP_Query([
    'post_type'      => 'orioni_comm',
    'posts_per_page' => (int)$a['per_page'],
    'orderby'        => sanitize_text_field($a['orderby']),
    'order'          => sanitize_text_field($a['order']),
    'tax_query'      => $tax ?: null,
    'paged'          => ($a['pagination']==='true') ? $paged : 1,
  ]);

  $cols = max(1, min(4, (int)$a['columns']));
  $gap  = max(0, (int)$a['gap']);

  ob_start(); ?>
  <div class="comm-wrap" style="--comm-cols:<?php echo $cols; ?>; --comm-gap:<?php echo $gap; ?>px;">
    <div class="comm-grid">
      <?php if ($q->have_posts()): while($q->have_posts()): $q->the_post(); ?>
        <article class="comm-card">
          <a class="thumb" href="<?php the_permalink(); ?>">
            <?php echo orioni_comm_card_image(get_the_ID(),'large'); ?>
          </a>
          <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
        </article>
      <?php endwhile; wp_reset_postdata(); else: ?>
        <p>Chưa có bài viết.</p>
      <?php endif; ?>
    </div>

    <?php if ($a['pagination']==='true') : ?>
      <nav class="comm-pager">
        <?php echo paginate_links([
          'total'   => $q->max_num_pages,
          'current' => $paged,
        ]); ?>
      </nav>
    <?php endif; ?>
  </div>
  <?php
  return ob_get_clean();
});

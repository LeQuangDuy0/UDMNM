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



// Đăng ký 2 taxonomy cho post
add_action('init', function () {
  // Thông cáo Báo chí
  register_taxonomy('press_release', ['post'], [
    'labels' => [
      'name'                       => 'Thông cáo Báo chí',
      'singular_name'              => 'Thông cáo Báo chí',
      'search_items'               => 'Tìm nhóm TCBC',
      'all_items'                  => 'Tất cả nhóm TCBC',
      'edit_item'                  => 'Sửa nhóm',
      'update_item'                => 'Cập nhật nhóm',
      'add_new_item'               => 'Thêm nhóm mới',
      'new_item_name'              => 'Tên nhóm mới',
      'menu_name'                  => 'Thông cáo Báo chí',
    ],
    'public'            => true,
    'hierarchical'      => true,      // cho phép tạo nhóm con
    'show_ui'           => true,
    'show_in_rest'      => true,      // hỗ trợ Gutenberg/REST
    'show_admin_column' => true,
    'rewrite'           => false
  ]);

  // Thông tin Sản phẩm
  register_taxonomy('product_info', ['post'], [
    'labels' => [
      'name'                       => 'Thông tin Sản phẩm',
      'singular_name'              => 'Thông tin Sản phẩm',
      'search_items'               => 'Tìm nhóm TTSP',
      'all_items'                  => 'Tất cả nhóm TTSP',
      'edit_item'                  => 'Sửa nhóm',
      'update_item'                => 'Cập nhật nhóm',
      'add_new_item'               => 'Thêm nhóm mới',
      'new_item_name'              => 'Tên nhóm mới',
      'menu_name'                  => 'Thông tin Sản phẩm',
    ],
    'public'            => true,
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_in_rest'      => true,
    'show_admin_column' => true,
    'rewrite'           => ['slug' => 'thong-tin-san-pham', 'with_front' => false],
  ]);
});

// Tạo sẵn 1 term mặc định cho mỗi taxonomy (để bạn dễ gán)
add_action('init', function () {
  $defs = [
    'press_release' => ['Tất cả Thông cáo', 'press-all'],
    'product_info'  => ['Tất cả Sản phẩm', 'product-all'],
  ];
  foreach ($defs as $tax => [$name, $slug]) {
    if (!term_exists($slug, $tax)) {
      wp_insert_term($name, $tax, ['slug' => $slug]);
    }
  }
});

// ========== HÀM DÙNG CHUNG ĐỂ RENDER GRID ==========
function _or_render_posts_grid(WP_Query $q) {
  ob_start(); ?>
  <div class="or-news"><!-- container để căn giữa -->
    <div class="or-news-grid">
      <?php if ($q->have_posts()): while ($q->have_posts()): $q->the_post(); ?>
        <article class="or-news-card">
          <a class="thumb" href="<?php the_permalink(); ?>">
            <?php
            // 1) ACF card_image (nếu có)
            $img_id = function_exists('get_field') ? (int) get_field('card_image') : 0;

            // 2) Featured image
            if (!$img_id && has_post_thumbnail()) {
              $img_id = get_post_thumbnail_id();
            }

            if ($img_id) {
              echo wp_get_attachment_image($img_id, 'large', false, ['loading' => 'lazy']);
            } else {
              // 3) Lấy ảnh đầu tiên trong nội dung
              $img_url = '';
              $content = get_the_content(null, false);
              if ($content && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) {
                $img_url = $m[1];
              }
              if ($img_url) {
                echo '<img src="'.esc_url($img_url).'" alt="'.esc_attr(get_the_title()).'" loading="lazy">';
              } else {
                // 4) Placeholder
                echo '<span class="ph" aria-hidden="true"></span>';
              }
            }
            ?>
          </a>

          <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

          <?php
          // subtitle từ ACF (nếu có), fallback excerpt
          $subtitle = function_exists('get_field') ? trim((string) get_field('subtitle')) : '';
          if ($subtitle === '') {
            $excerpt = wp_strip_all_tags(get_the_excerpt());
            if ($excerpt) $subtitle = wp_trim_words($excerpt, 22);
          }
          if ($subtitle !== '') {
            echo '<p class="subtitle">'.esc_html($subtitle).'</p>';
          }
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
  <?php
  return ob_get_clean();
}

// ========== SHORTCODE 1: Thông cáo Báo chí ==========
add_shortcode('press_grid', function($atts) {
  $a = shortcode_atts([
    'per_page' => 6,
    'orderby'  => 'date',
    'order'    => 'DESC',
  ], $atts);

  $q = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => (int) $a['per_page'],
    'orderby'        => sanitize_text_field($a['orderby']),
    'order'          => sanitize_text_field($a['order']),
    // Lấy tất cả post có GẮN taxonomy press_release (bất kỳ term nào)
    'tax_query' => [[
      'taxonomy' => 'press_release',
      'operator' => 'EXISTS',
    ]],
  ]);

  return _or_render_posts_grid($q);
});

// ========== SHORTCODE 2: Thông tin Sản phẩm ==========
add_shortcode('productinfo_grid', function($atts) {
  $a = shortcode_atts([
    'per_page' => 6,
    'orderby'  => 'date',
    'order'    => 'DESC',
  ], $atts);

  $q = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => (int) $a['per_page'],
    'orderby'        => sanitize_text_field($a['orderby']),
    'order'          => sanitize_text_field($a['order']),
    'tax_query' => [[
      'taxonomy' => 'product_info',
      'operator' => 'EXISTS',
    ]],
  ]);

  return _or_render_posts_grid($q);
});

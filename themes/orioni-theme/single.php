<?php
/* Template Name: single */

get_header();

/* ===== Helpers lấy ACF (ưu tiên page, fallback options) ===== */
$prefer = function ($key) {
  $v = function_exists('get_field') ? get_field($key) : null;
  if (!empty($v)) return $v;
  return function_exists('get_field') ? get_field($key, 'option') : null;
};

$hero_img   = $prefer('hero_image');                         // Image (Array)
$hero_title = $prefer('hero_title') ?: get_the_title();      // Text
$overlay    = $prefer('hero_overlay_opacity');
$overlay    = is_numeric($overlay) ? max(0, min(90, (int)$overlay)) : 55; // %
$height_vh  = (int)($prefer('hero_height_vh') ?: 70);

/* Ảnh nền: ưu tiên ACF image, nếu trống dùng Featured Image */
$bg_url = '';
if (is_array($hero_img) && !empty($hero_img['url'])) {
  $bg_url = $hero_img['url'];
} elseif (has_post_thumbnail()) {
  $bg_url = get_the_post_thumbnail_url(null, 'full');
}

/* === Xác định trang hiện tại là TCBC hay TTSP theo slug === */
$page_slug  = basename( parse_url( get_permalink(), PHP_URL_PATH ) );
$grid_tax   = ($page_slug === 'thong-tin-san-pham') ? 'product_info' : 'press_release';
$grid_title = 'Tin tức mới';

/* === Hàm trả về <img> cho card: ưu tiên ACF (card_image/banner) -> Featured -> ảnh đầu tiên trong content === */
function or_get_card_img_html($size = 'large') {
  $img_id = 0;
  if (function_exists('get_field')) {
    $img_id = (int) get_field('card_image');
    if (!$img_id) $img_id = (int) get_field('banner');
  }
  if ($img_id) {
    return wp_get_attachment_image($img_id, $size, false, ['loading' => 'lazy']);
  }
  // Featured Image
  $fid = get_post_thumbnail_id();
  if ($fid) {
    return wp_get_attachment_image($fid, $size, false, ['loading' => 'lazy']);
  }
  // Ảnh đầu tiên trong nội dung
  $content = get_post_field('post_content', get_the_ID());
  if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) {
    $src = esc_url($m[1]);
    $alt = esc_attr(get_the_title());
    return '<img src="'.$src.'" alt="'.$alt.'" loading="lazy" />';
  }
  // Placeholder
  return '<div class="ph" style="height:220px;background:#f3f4f6"></div>';
}
?>

<section class="about-hero"
  style="--h:<?php echo $height_vh; ?>vh; --ov:<?php echo $overlay / 100; ?>; <?php if ($bg_url) echo 'background-image:url('.esc_url($bg_url).');'; ?>">
  <div class="about-hero__overlay"></div>
  <div class="container">
    <div class="about-hero__box">
      <h1 class="about-hero__title"><?php echo esc_html($hero_title); ?></h1>
    </div>
  </div>
</section>

<?php
// (Tùy chọn) hỗ trợ lấy Primary Category của Yoast
if (!function_exists('yoast_get_primary_term_id')) {
  function yoast_get_primary_term_id($taxonomy, $post_id) {
    if (class_exists('WPSEO_Primary_Term')) {
      $primary = new WPSEO_Primary_Term($taxonomy, $post_id);
      $term_id = (int) $primary->get_primary_term();
      return $term_id > 0 ? $term_id : 0;
    }
    return 0;
  }
}

/* Breadcrumb fallback khi không dùng Yoast */
function orioni_breadcrumbs() {
  $sep = '<span class="sep">|</span>';
  echo '<div class="crumbs">';
  echo '<a href="'. esc_url( home_url('/') ) .'">Trang chủ</a>';

  if ( is_front_page() ) { echo '</div>'; return; }

  // PAGE (có phân cấp cha/con)
  if ( is_page() ) {
    global $post;
    $ancestors = array_reverse( get_post_ancestors( $post->ID ) );
    foreach ( $ancestors as $ancestor_id ) {
      echo ' '. $sep .' <a href="'. esc_url( get_permalink($ancestor_id) ) .'">'. esc_html( get_the_title($ancestor_id) ) .'</a>';
    }
    echo ' '. $sep .' <span>'. esc_html( get_the_title() ) .'</span>';
    echo '</div>';
    return;
  }

  // SINGLE (bài viết thường)
  if ( is_singular('post') ) {
    $post_id   = get_the_ID();
    $mid_label = '';
    $mid_link  = '';

    // Ưu tiên custom taxonomy press_release / product_info
    if ( has_term('', 'press_release', $post_id) ) {
      $mid_label = 'Thông cáo Báo chí';
      $page = get_page_by_path('tin-tuc/thong-cao-bao-chi'); // sửa slug nếu khác
      if ( $page ) {
        $mid_link = get_permalink($page);
      } else {
        $terms = wp_get_post_terms($post_id, 'press_release');
        if ( !is_wp_error($terms) && !empty($terms) ) $mid_link = get_term_link($terms[0]);
      }
    } elseif ( has_term('', 'product_info', $post_id) ) {
      $mid_label = 'Thông tin Sản phẩm';
      $page = get_page_by_path('tin-tuc/thong-tin-san-pham'); // sửa slug nếu khác
      if ( $page ) {
        $mid_link = get_permalink($page);
      } else {
        $terms = wp_get_post_terms($post_id, 'product_info');
        if ( !is_wp_error($terms) && !empty($terms) ) $mid_link = get_term_link($terms[0]);
      }
    } else {
      // Fallback: category đầu tiên nếu không gắn 2 taxonomy trên
      $cats = get_the_category($post_id);
      if ( !empty($cats) ) {
        $mid_label = $cats[0]->name;
        $mid_link  = get_category_link($cats[0]);
      }
    }

    if ( $mid_label ) {
      echo ' '. $sep .' ';
      if ( $mid_link ) {
        echo '<a href="'. esc_url($mid_link) .'">'. esc_html($mid_label) .'</a>';
      } else {
        echo '<span>'. esc_html($mid_label) .'</span>';
      }
    }

    echo ' '. $sep .' <span>'. esc_html( get_the_title() ) .'</span>';
    echo '</div>';
    return;
  }

  // CATEGORY / TAXONOMY archive
  if ( is_category() || is_tax() ) {
    $term = get_queried_object();
    if ( $term && $term->parent ) {
      $parents = array_reverse( get_ancestors( $term->term_id, $term->taxonomy ) );
      foreach ( $parents as $pid ) {
        $p = get_term( $pid, $term->taxonomy );
        echo ' '. $sep .' <a href="'. esc_url( get_term_link($p) ) .'">'. esc_html( $p->name ) .'</a>';
      }
    }
    echo ' '. $sep .' <span>'. esc_html( single_term_title('', false) ) .'</span>';
    echo '</div>';
    return;
  }

  echo '</div>';
}
?>

<nav class="about-breadcrumbs">
  <div class="container">
    <?php
    if (function_exists('yoast_breadcrumb')) {
      yoast_breadcrumb('<div class="crumbs">','</div>');
    } else {
      orioni_breadcrumbs();
    }
    ?>
  </div>
</nav>

<main class="container" style="padding:24px 16px">
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <article <?php post_class('or-press-intro'); ?>>
      <!-- Chỉ hiển thị content để không trùng tiêu đề -->
      <div class="entry-content">
        <?php the_content(); ?>
      </div>
    </article>
  <?php endwhile; endif; ?>

  <?php
  /* ===== Lưới bài (desktop/tablet) theo taxonomy đã xác định ===== */
  $paged = max(1, get_query_var('paged'));
  $q = new WP_Query([
    'post_type'      => 'post',
    'posts_per_page' => 9,     // 3 cột x 3 hàng / trang
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'tax_query'      => [[
      'taxonomy' => $grid_tax,   // press_release hoặc product_info
      'operator' => 'EXISTS',
    ]],
  ]);
  ?>

  <!-- DESKTOP/TABLET: GRID -->
  <section class="or-news-wrap or-desktop-only">
    <h2 class="or-stit" style="text-align:center;margin:10px 0 22px"><?php echo esc_html($grid_title); ?></h2>

    <div class="or-news-grid">
      <?php if ($q->have_posts()): while ($q->have_posts()): $q->the_post(); ?>
        <article class="or-news-card">
          <a class="thumb" href="<?php the_permalink(); ?>">
            <?php echo or_get_card_img_html('large'); ?>
          </a>

          <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>

          <?php
          $subtitle = function_exists('get_field') ? trim((string) get_field('subtitle')) : '';
          if ($subtitle === '') {
            $excerpt = wp_strip_all_tags(get_the_excerpt());
            if ($excerpt) $subtitle = wp_trim_words($excerpt, 22);
          }
          if ($subtitle !== '') {
            echo '<p class="subtitle">'. esc_html($subtitle) .'</p>';
          }
          ?>

          <time class="date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
            <?php echo esc_html(get_the_date('d/m/Y')); ?>
          </time>
        </article>
      <?php endwhile; wp_reset_postdata(); else: ?>
        <p>Chưa có bài viết.</p>
      <?php endif; ?>
    </div>

    <?php
    // Phân trang đẹp
    $links = paginate_links([
      'total'   => $q->max_num_pages,
      'current' => $paged,
      'type'    => 'list',
      'prev_text' => '« Trước',
      'next_text' => 'Sau »',
    ]);
    if ($links) {
      echo '<nav class="or-pagination">'.$links.'</nav>';
    }
    ?>
  </section>

  <!-- MOBILE: Danh sách 8 bài mới nhất cùng taxonomy -->
  <section class="or-mobile-latest or-mobile-only">
    <h3 class="mnews-heading">Tin Tức Mới</h3>
    <?php
    $latest = new WP_Query([
      'post_type'      => 'post',
      'posts_per_page' => 8,
      'orderby'        => 'date',
      'order'          => 'DESC',
      'tax_query'      => [[
        'taxonomy' => $grid_tax,
        'operator' => 'EXISTS',
      ]],
    ]);
    ?>
    <ul class="or-mnews">
      <?php if ($latest->have_posts()): while ($latest->have_posts()): $latest->the_post(); ?>
        <li class="mnews-item">
          <a class="mnews-link" href="<?php the_permalink(); ?>">
            <span class="mnews-thumb"><?php echo or_get_card_img_html('medium'); ?></span>
            <span class="mnews-title"><?php the_title(); ?></span>
          </a>
        </li>
      <?php endwhile; wp_reset_postdata(); else: ?>
        <li>Chưa có bài viết.</li>
      <?php endif; ?>
    </ul>
  </section>

  <?php
  // (Tùy chọn) ảnh/alt của trang sau lưới
  $page_img_id = function_exists('get_field') ? (int) get_field('card_image') : 0;
  $page_sub    = function_exists('get_field') ? trim((string) get_field('subtitle')) : '';
  if ($page_sub) echo '<p class="post-subtitle">'.esc_html($page_sub).'</p>';
  if ($page_img_id) {
    echo wp_get_attachment_image($page_img_id, 'full', false, ['class'=>'hero-img','loading'=>'lazy']);
  } elseif (has_post_thumbnail()) {
    the_post_thumbnail('full', ['class'=>'hero-img','loading'=>'lazy']);
  }
  ?>
</main>

<?php get_footer(); ?>

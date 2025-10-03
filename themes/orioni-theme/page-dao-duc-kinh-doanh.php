<?php
/* Template Name: dao-duc-kinh-doanh */
get_header(); ?>
<?PHP
/* ===== Lấy dữ liệu ACF (ưu tiên trên trang; nếu có Options Page thì fallback sang 'option') ===== */
$prefer = function ($key) {
    $v = function_exists('get_field') ? get_field($key) : null;
    if (!empty($v))
        return $v;
    return function_exists('get_field') ? get_field($key, 'option') : null;
};
$hero_img = $prefer('hero_image');                       // Image (Array)
$hero_title = $prefer('hero_title') ?: get_the_title();    // Text
$overlay = $prefer('hero_overlay_opacity');
$overlay = is_numeric($overlay) ? max(0, min(90, (int) $overlay)) : 55; // % (default 55)
$height_vh = $prefer('hero_height_vh');
$height_vh = (int) ($height_vh ?: 70); // default 70vh
/* Ảnh nền: ưu tiên ACF image, nếu trống dùng Featured Image */
$bg_url = '';
if (is_array($hero_img) && !empty($hero_img['url'])) {
    $bg_url = $hero_img['url'];
} elseif (has_post_thumbnail()) {
    $bg_url = get_the_post_thumbnail_url(null, 'full');
}
?>
<section class="about-hero" style="--h:<?php echo $height_vh; ?>vh; --ov:<?php echo $overlay / 100; ?>; <?php if ($bg_url)
            echo 'background-image:url(' . esc_url($bg_url) . ');'; ?>">
    <div class="about-hero__overlay"></div>
    <div class="container">
        <div class="about-hero__box">
            <h1 class="about-hero__title"><?php echo esc_html($hero_title); ?></h1>
        </div>
    </div>
</section>
<!-- Breadcrumb dưới hero -->
<?php
// (Tuỳ chọn) Hỗ trợ lấy Primary Category của Yoast nếu có
if (!function_exists('yoast_get_primary_term_id')) {
    function yoast_get_primary_term_id($taxonomy, $post_id)
    {
        if (class_exists('WPSEO_Primary_Term')) {
            $primary = new WPSEO_Primary_Term($taxonomy, $post_id);
            $term_id = (int) $primary->get_primary_term();
            return $term_id > 0 ? $term_id : 0;
        }
        return 0;
    }
}
/**
 * Breadcrumbs linh hoạt
 */
function orioni_breadcrumbs()
{
    $sep = '<span class="sep">|</span>';
    echo '<div class="crumbs">';
    echo '<a href="' . esc_url(home_url('/')) . '">Trang chủ</a>';
    if (is_front_page()) {
        echo '</div>';
        return;
    }
    // PAGE (có phân cấp cha/con)
    if (is_page()) {
        global $post;
        $ancestors = array_reverse(get_post_ancestors($post->ID));
        foreach ($ancestors as $ancestor_id) {
            echo ' ' . $sep . ' <a href="' . esc_url(get_permalink($ancestor_id)) . '">' . esc_html(get_the_title($ancestor_id)) . '</a>';
        }
        echo ' ' . $sep . ' <span>' . esc_html(get_the_title()) . '</span>';
        echo '</div>';
        return;
    }
    // SINGLE (bài viết thường)
    if (is_singular('post')) {
        global $post;
        // Yoast primary category trước, sau đó đến category đầu tiên
        $cat_id = yoast_get_primary_term_id('category', $post->ID);
        if (!$cat_id) {
            $cats = get_the_category($post->ID);
            if (!empty($cats))
                $cat_id = $cats[0]->term_id;
        }
        if ($cat_id) {
            // Chuỗi cha của category
            $chain = [];
            $term = get_term($cat_id, 'category');
            while ($term && !is_wp_error($term)) {
                $chain[] = $term;
                if ($term->parent)
                    $term = get_term($term->parent, 'category');
                else
                    break;
            }
            $chain = array_reverse($chain);
            foreach ($chain as $t) {
                echo ' ' . $sep . ' <a href="' . esc_url(get_term_link($t)) . '">' . esc_html($t->name) . '</a>';
            }
        }
        echo ' ' . $sep . ' <span>' . esc_html(get_the_title()) . '</span>';
        echo '</div>';
        return;
    }
    // SINGLE (CPT)
    if (is_singular()) {
        $pt = get_post_type();
        if ($pt && $pt !== 'post') {
            $obj = get_post_type_object($pt);
            if ($obj && !empty($obj->has_archive)) {
                echo ' ' . $sep . ' <a href="' . esc_url(get_post_type_archive_link($pt)) . '">' . esc_html($obj->labels->name) . '</a>';
            }
        }
        echo ' ' . $sep . ' <span>' . esc_html(get_the_title()) . '</span>';
        echo '</div>';
        return;
    }
    // CATEGORY / TAXONOMY
    if (is_category() || is_tax()) {
        $term = get_queried_object();
        if ($term && $term->parent) {
            $parents = array_reverse(get_ancestors($term->term_id, $term->taxonomy));
            foreach ($parents as $pid) {
                $p = get_term($pid, $term->taxonomy);
                echo ' ' . $sep . ' <a href="' . esc_url(get_term_link($p)) . '">' . esc_html($p->name) . '</a>';
            }
        }
        echo ' ' . $sep . ' <span>' . esc_html(single_term_title('', false)) . '</span>';
        echo '</div>';
        return;
    }
    // ARCHIVES
    if (is_post_type_archive()) {
        $obj = get_post_type_object(get_post_type());
        echo ' ' . $sep . ' <span>' . esc_html($obj ? $obj->labels->name : 'Lưu trữ') . '</span>';
        echo '</div>';
        return;
    }
    if (is_day()) {
        echo ' ' . $sep . ' <span>' . esc_html(get_the_date()) . '</span>';
        echo '</div>';
        return;
    }
    if (is_month()) {
        echo ' ' . $sep . ' <span>' . esc_html(get_the_date('F Y')) . '</span>';
        echo '</div>';
        return;
    }
    if (is_year()) {
        echo ' ' . $sep . ' <span>' . esc_html(get_the_date('Y')) . '</span>';
        echo '</div>';
        return;
    }

    // SEARCH / 404 / TAG / AUTHOR
    if (is_search()) {
        echo ' ' . $sep . ' <span>Tìm kiếm: “' . esc_html(get_search_query()) . '”</span>';
        echo '</div>';
        return;
    }
    if (is_tag()) {
        echo ' ' . $sep . ' <span>Thẻ: ' . esc_html(single_tag_title('', false)) . '</span>';
        echo '</div>';
        return;
    }
    if (is_author()) {
        $au = get_queried_object();
        echo ' ' . $sep . ' <span>Tác giả: ' . esc_html($au->display_name) . '</span>';
        echo '</div>';
        return;
    }
    if (is_404()) {
        echo ' ' . $sep . ' <span>Không tìm thấy trang</span>';
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
            yoast_breadcrumb('<div class="crumbs">', '</div>');
        } else {
            orioni_breadcrumbs(); // fallback tuỳ biến
        }
        ?>
    </div>
</nav>
<!-- Breadcrumb dưới hero - end -->
<?php
// Lấy 2 trang con theo đường dẫn (đổi nếu slug khác)
$dao_duc_kinh_doanh = get_page_by_path('quan-he-cong-dong/dao-duc-kinh-doanh');
$hoat_dong_xa_hoi = get_page_by_path('quan-he-cong-dong/hoat-dong-xa-hoi');
$current_id = get_queried_object_id();
?>
<div class="about-switch" aria-label="About tabs">
    <div class="about-switch__wrap">
        <?php if ($dao_duc_kinh_doanh): ?>
            <a class="about-switch__item <?php echo ($current_id === $dao_duc_kinh_doanh->ID) ? 'is-active' : ''; ?>"
                href="<?php echo esc_url(get_permalink($dao_duc_kinh_doanh->ID)); ?>">
                Đạo đức kinh doanh
            </a>
        <?php endif; ?>
        <?php if ($hoat_dong_xa_hoi): ?>
            <a class="about-switch__item <?php echo ($current_id === $hoat_dong_xa_hoi->ID) ? 'is-active' : ''; ?>"
                href="<?php echo esc_url(get_permalink($hoat_dong_xa_hoi->ID)); ?>">
                Hoạt động xã hội
            </a>
        <?php endif; ?>
    </div>
</div>

<?php
/* === GRID bài từ CPT orioni_comm, lọc theo taxonomy 'community_topic' === */
$term_slug = 'dao-duc-kinh-doanh';                // 👈 trang này
$paged = max(1, get_query_var('paged'), get_query_var('page'));

$q = new WP_Query([
  'post_type'      => 'orioni_comm',
  'posts_per_page' => 6,
  'paged'          => $paged,
  'tax_query'      => [[
    'taxonomy' => 'community_topic',
    'field'    => 'slug',
    'terms'    => [$term_slug],
  ]],
]);
?>
<main class="orioni-wrap">
  <div class="orioni-grid">
    <?php if ($q->have_posts()): while ($q->have_posts()): $q->the_post(); ?>
      <article class="orioni-card">
        <a class="orioni-card__link" href="<?php the_permalink(); ?>">
          <div class="orioni-card__thumb">
            <?php if (has_post_thumbnail()) {
              the_post_thumbnail('large',['loading'=>'lazy']);
            } else {
              echo '<img src="'.esc_url(get_template_directory_uri().'/assets/img/placeholder-16x9.jpg').'" alt="">';
            } ?>
          </div>
          <h3 class="orioni-card__title"><?php the_title(); ?></h3>
        </a>
      </article>
    <?php endwhile; else: ?>
      <p>Chưa có bài trong mục này.</p>
    <?php endif; wp_reset_postdata(); ?>
  </div>

  <nav class="pagination">
    <?php
    echo paginate_links([
      'current' => $paged,
      'total'   => max(1, $q->max_num_pages),
      // 2 dòng dưới giúp phân trang đúng trên Page:
      'base'    => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
      'format'  => 'page/%#%/',
    ]);
    ?>
  </nav>
</main>


<?php
get_footer();
?>
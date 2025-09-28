<?php
/* Template Name: lien-he-chung */
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
// Lấy 3 trang con theo đường dẫn (đổi nếu slug khác)
$lien_he_chung = get_page_by_path('lien-he/lien-he-chung');
$lien_he_nha_may = get_page_by_path('lien-he/lien-he-nha-may');
$yeu_cau_hop_tac = get_page_by_path('lien-he/yeu-cau-hop-tac');
$current_id = get_queried_object_id();
?>
<div id="contact-tabs" class="about-switch" aria-label="About tabs">
    <div class="about-switch__wrap">
        <?php if ($lien_he_chung): ?>
            <a class="about-switch__item <?php echo ($current_id === $lien_he_chung->ID) ? 'is-active' : ''; ?>"
                href="<?php echo esc_url(get_permalink($lien_he_chung->ID)); ?>">
                Liên hệ chung
            </a>
        <?php endif; ?>
        <?php if ($lien_he_nha_may): ?>
            <a class="about-switch__item <?php echo ($current_id === $lien_he_nha_may->ID) ? 'is-active' : ''; ?>"
                href="<?php echo esc_url(get_permalink($lien_he_nha_may->ID)); ?>">
                Liên hệ nhà máy
            </a>
        <?php endif; ?>
        <?php if ($yeu_cau_hop_tac): ?>
            <a class="about-switch__item <?php echo ($current_id === $yeu_cau_hop_tac->ID) ? 'is-active' : ''; ?>"
                href="<?php echo esc_url(get_permalink($yeu_cau_hop_tac->ID)); ?>">
                Yêu cầu hợp tác
            </a>
        <?php endif; ?>
    </div>
</div>


<style>
  /* Wrapper */
  #contact-tabs{ margin:20px auto 28px; }

  /* 3 item chia đều 1 hàng */
  #contact-tabs .about-switch__wrap{
    display:flex;
    gap:12px;
  }

  /* Nút */
  #contact-tabs .about-switch__item{
    flex:1 1 0%;               /* chia đều */
    display:block;
    text-align:center;
    padding:12px 16px;
    border: solid #e5e7eb;

    background:#f8fafc;
    font-weight:600;
    line-height:1.25;
    text-decoration:none;
    transition:background .2s ease, color .2s ease, box-shadow .2s ease;
    white-space:nowrap;        /* tránh xuống dòng khi màn hình hẹp */
  }
  #contact-tabs .about-switch__item:hover{
    background:#ffffff;
    box-shadow:0 2px 12px rgba(0,0,0,.06);
  }
  #contact-tabs .about-switch__item.is-active{
    background:#e31e25;
    color:#fff;
    
  }

  /* Viền ngăn cách nhẹ khi nằm sát nhau (tùy thích) */
  #contact-tabs .about-switch__item + .about-switch__item{ }

  /* Tối ưu mobile cực nhỏ: thu nhỏ font để vẫn 1 hàng 3 nút */
  @media (max-width:480px){
    #contact-tabs .about-switch__item{ padding:10px 8px; font-size:14px; }
  }

  /* Nếu bạn muốn mobile xuống 1 cột, bỏ comment khối dưới:
  @media (max-width:640px){
    #contact-tabs .about-switch__wrap{ display:grid; grid-template-columns:1fr; }
    #contact-tabs .about-switch__item{ white-space:normal; }
  }
  */
</style>

<?php
get_footer();
?>
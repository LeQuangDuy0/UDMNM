<?php
/* Template Name: san pham */

get_header();

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

<main class="container page-content">
    <?php the_content(); ?>
</main>

<?php
$page_id = get_queried_object_id();
$all_label = get_field('all_label', $page_id) ?: 'Toàn bộ';
$all_icon_id = get_field('all_icon', $page_id);
$topbar_limit = (int) get_field('topbar_limit', $page_id) ?: 9;
$products_per_page = (int) get_field('products_per_page', $page_id) ?: 12;

// --- Đang lọc theo danh mục nào? (?cat=ID) ---
$current_term_id = isset($_GET['cat']) ? max(0, (int) $_GET['cat']) : 0;

// --- Lấy danh mục phải hiện trên thanh icon (do admin tick) ---
$terms = get_terms([
    'taxonomy' => 'orion_cat',
    'hide_empty' => false,
    'meta_query' => [['key' => 'show_on_topbar', 'value' => 1]], // ACF true/false -> 1
    'orderby' => 'meta_value_num',
    'meta_key' => 'topbar_order',
    'order' => 'ASC',
    'number' => $topbar_limit > 0 ? $topbar_limit : 0,
]);
?>

<div class="container">

    <!-- Thanh category có nút trái/phải (nút chỉ hiện ở tablet/mobile & khi có thể cuộn) -->
    <div class="orioni-catwrap" data-catwrap>
        <button class="orioni-catbtn prev" type="button" aria-label="Danh mục trước" data-prev>
            <span>‹</span>
        </button>

        <div class="orioni-catbar" data-catbar>
            <?php
            // Mục "Toàn bộ" (link về Page /san-pham, active khi không lọc)
            $all_img_html = $all_icon_id
                ? wp_get_attachment_image($all_icon_id, 'thumbnail', false, ['class' => 'circle'])
                : '<span class="orioni-catbar__ph"></span>';

            $all_cls = $current_term_id ? '' : ' is-active';
            echo '<a class="orioni-catbar__item' . $all_cls . '" href="' . esc_url(get_permalink($page_id)) . '">
              <span class="orioni-catbar__img">' . $all_img_html . '</span>
              <span class="orioni-catbar__name">' . esc_html($all_label) . '</span>
            </a>';

            // Các danh mục do admin chọn (link về chính page với ?cat=ID)
            if (!empty($terms) && !is_wp_error($terms)) {
                foreach ($terms as $t) {
                    $active = ($current_term_id === $t->term_id) ? ' is-active' : '';
                    $icon_id = get_field('icon', 'orion_cat_' . $t->term_id);
                    $img = $icon_id ? wp_get_attachment_image($icon_id, 'thumbnail', false, ['class' => 'circle'])
                        : '<span class="orioni-catbar__ph"></span>';
                    $url = add_query_arg(['cat' => $t->term_id], get_permalink($page_id));
                    echo '<a class="orioni-catbar__item' . $active . '" href="' . esc_url($url) . '">
                  <span class="orioni-catbar__img">' . $img . '</span>
                  <span class="orioni-catbar__name">' . esc_html($t->name) . '</span>
                </a>';
                }
            }
            ?>
        </div>

        <button class="orioni-catbtn next" type="button" aria-label="Danh mục tiếp" data-next>
            <span>›</span>
        </button>
    </div>
    <!-- /Thanh category -->

    <?php
    // --- Query sản phẩm (lọc theo ?cat nếu có) ---
    $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
    $args = [
        'post_type' => 'orion_product',
        'posts_per_page' => $products_per_page,
        'paged' => $paged,
        'orderby' => 'date',
        'order' => 'DESC',
    ];
    if ($current_term_id) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'orion_cat',
                'field' => 'term_id',
                'terms' => $current_term_id
            ]
        ];
    }
    $q = new WP_Query($args);
    ?>

    <p class="orioni-count">
        Tìm thấy <span class="orioni-count__num"><?php echo (int) $q->found_posts; ?></span> sản phẩm
    </p>

    <ul class="products orioni-grid">
        <?php if ($q->have_posts()):
            while ($q->have_posts()):
                $q->the_post(); ?>
                <?php
                // Ảnh: ưu tiên ACF product_image, nếu không có dùng Featured Image
                $img_id = get_field('product_image') ?: get_post_thumbnail_id();
                // Tiêu đề chính: dùng post title
                $title_main = get_the_title();
                // Tiêu đề phụ: ACF title_sub (nếu có)
                $title_sub = trim((string) get_field('title_sub'));
                // Mô tả ngắn: ACF short_desc → fallback Excerpt
                $desc = get_field('short_desc') ?: wp_trim_words(get_the_excerpt(), 18);
                ?>
                <li class="orioni-card">
                    <a href="<?php the_permalink(); ?>" class="orioni-card__link">
                        <div class="orioni-card__frame">
                            <?php if ($img_id)
                                echo wp_get_attachment_image($img_id, 'large', false, ['loading' => 'lazy']); ?>
                        </div>

                        <h3 class="orioni-card__title"><?= esc_html($title_main); ?></h3>

                        <?php if ($title_sub !== ''): ?>
                            <div class="orioni-card__subtitle"><?= esc_html($title_sub); ?></div>
                        <?php endif; ?>

                        <p class="orioni-card__desc"><?= esc_html($desc); ?></p>
                    </a>
                </li>
            <?php endwhile; else: ?>
            <li>Chưa có sản phẩm.</li>
        <?php endif;
        wp_reset_postdata(); ?>
    </ul>

    <?php
    // Phân trang (giữ tham số ?cat)
    echo '<div class="pagination">' . paginate_links([
        'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
        'format' => '/page/%#%',
        'current' => $paged,
        'total' => $q->max_num_pages,
        'add_args' => $current_term_id ? ['cat' => $current_term_id] : [],
    ]) . '</div>';
    ?>

</div>





<?php get_footer(); ?>
<?php
/* Template Name: gioi thieu */

get_header();

/* ===== Lấy dữ liệu ACF (ưu tiên trên trang; nếu có Options Page thì fallback sang 'option') ===== */
$prefer = function($key) {
  $v = function_exists('get_field') ? get_field($key) : null;
  if (!empty($v)) return $v;
  return function_exists('get_field') ? get_field($key, 'option') : null;
};

$hero_img   = $prefer('hero_image');                       // Image (Array)
$hero_title = $prefer('hero_title') ?: get_the_title();    // Text

$overlay    = $prefer('hero_overlay_opacity');
$overlay    = is_numeric($overlay) ? max(0, min(90, (int)$overlay)) : 55; // % (default 55)

$height_vh  = $prefer('hero_height_vh');
$height_vh  = (int)($height_vh ?: 70); // default 70vh

/* Ảnh nền: ưu tiên ACF image, nếu trống dùng Featured Image */
$bg_url = '';
if (is_array($hero_img) && !empty($hero_img['url'])) {
  $bg_url = $hero_img['url'];
} elseif (has_post_thumbnail()) {
  $bg_url = get_the_post_thumbnail_url(null, 'full');
}
?>

<section class="about-hero"
  style="--h:<?php echo $height_vh; ?>vh; --ov:<?php echo $overlay/100; ?>; <?php if($bg_url) echo 'background-image:url('.esc_url($bg_url).');'; ?>">
  <div class="about-hero__overlay"></div>
  <div class="container">
    <div class="about-hero__box">
      <h1 class="about-hero__title"><?php echo esc_html($hero_title); ?></h1>
    </div>
  </div>
</section>

<!-- Breadcrumb dưới hero -->
 
<nav class="about-breadcrumbs">
  <div class="container">
    <?php
    if (function_exists('yoast_breadcrumb')) {
      yoast_breadcrumb('<div class="crumbs">','</div>');
    } else {
      echo '<div class="crumbs"><a href="'.esc_url(home_url('/')).'">Trang chủ</a> <span class="sep">|</span> ';
      echo '<span>'.esc_html(get_the_title()).'</span></div>';
    }
    ?>
  </div>
</nav>

<?php
  // Lấy 2 trang con theo đường dẫn (đổi nếu slug khác)
  $gioi_thieu = get_page_by_path('ve-chung-toi/gioi-thieu');
  $lich_su    = get_page_by_path('ve-chung-toi/lich-su');
  $current_id = get_queried_object_id();
?>
<div class="about-switch" aria-label="About tabs">
  <div class="about-switch__wrap">
    <?php if ($gioi_thieu): ?>
      <a class="about-switch__item <?php echo ($current_id === $gioi_thieu->ID) ? 'is-active' : ''; ?>"
         href="<?php echo esc_url(get_permalink($gioi_thieu->ID)); ?>">
        Giới thiệu
      </a>
    <?php endif; ?>

    <?php if ($lich_su): ?>
      <a class="about-switch__item <?php echo ($current_id === $lich_su->ID) ? 'is-active' : ''; ?>"
         href="<?php echo esc_url(get_permalink($lich_su->ID)); ?>">
        Lịch sử
      </a>
    <?php endif; ?>
  </div>
    </div>  

<main class="container page-content">
  <?php the_content(); ?>
</main>

<?php
// Hiển thị tối đa 6 section (tăng/giảm tuỳ ý)
for ($i = 1; $i <= 6; $i++):
  $title    = get_field("about_sec_{$i}_title");
  $subtitle = get_field("about_sec_{$i}_subtitle");
  $content  = get_field("about_sec_{$i}_content");   // WYSIWYG
  $image    = get_field("about_sec_{$i}_image");     // array
  $layout   = get_field("about_sec_{$i}_layout") ?: 'text-left';

  // Bỏ qua nếu rỗng cả text & image
  if (empty($title) && empty($content) && empty($image)) continue;
?>
  <section class="about-block <?php echo esc_attr($layout); ?>">
    <div class="about-block__text">
      <?php if ($title): ?>
        <h2 class="about-block__title"><?php echo esc_html($title); ?></h2>
      <?php endif; ?>

      <?php if ($subtitle): ?>
        <h3 class="about-block__subtitle"><?php echo esc_html($subtitle); ?></h3>
      <?php endif; ?>

      <?php if ($content): ?>
        <div class="about-block__content">
          <?php echo wp_kses_post($content); ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if (is_array($image) && !empty($image['url'])): ?>
      <div class="about-block__image">
        <img src="<?php echo esc_url($image['url']); ?>"
             alt="<?php echo esc_attr($image['alt'] ?? ''); ?>">
      </div>
    <?php endif; ?>
  </section>
<?php endfor; ?>


<?php get_footer(); ?>

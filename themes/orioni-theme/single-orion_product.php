<?php
/** Single – Orion Product (FREE gallery) */
get_header();
the_post();
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
$breadcrumb_page = get_page_by_path('san-pham'); // trang tổng

// Ảnh chính (ACF product_image) -> fallback Featured Image
$img_id = get_field('product_image') ?: get_post_thumbnail_id();

// ======= Lấy danh sách ảnh gallery theo thứ tự ưu tiên =======
$gallery_ids = [];

// 1) ACF Free: các field ảnh rời gallery_1..gallery_8
for ($i = 1; $i <= 8; $i++) {
  $gid = (int) get_field('gallery_' . $i);
  if ($gid)
    $gallery_ids[] = $gid;
}

// 2) Gallery chèn trong nội dung (shortcode/block)
if (!$gallery_ids) {
  // 2a. Shortcode [gallery ids="..."]
  $gal = get_post_gallery(get_the_ID(), false);
  if (!empty($gal['ids'])) {
    $gallery_ids = array_map('intval', explode(',', $gal['ids']));
  }
}
if (!$gallery_ids) {
  // 2b. Gutenberg gallery (trả về URL), map về attachment ID
  $gals = get_post_galleries_images(get_the_ID()); // mảng các gallery -> mảng URL
  if (!empty($gals)) {
    foreach ($gals[0] as $url) {
      $aid = attachment_url_to_postid($url);
      if ($aid)
        $gallery_ids[] = $aid;
    }
  }
}

// 3) Fallback: lấy ảnh đính kèm của bài (trừ ảnh chính)
if (!$gallery_ids) {
  $attachments = get_children([
    'post_parent' => get_the_ID(),
    'post_type' => 'attachment',
    'post_mime_type' => 'image',
    'exclude' => $img_id ? [$img_id] : [],
    'orderby' => 'menu_order',
    'order' => 'ASC',
  ]);
  foreach ($attachments as $att)
    $gallery_ids[] = (int) $att->ID;
}

// Gom ảnh chính + gallery (loại trùng)
$thumb_ids = array_values(array_unique(array_filter(array_merge([$img_id], $gallery_ids))));

// ACF khác
$packaging = (array) get_field('packaging');
$weights = (array) get_field('weights');
$short = (string) get_field('short_desc');
?>

<main class="container orioni-single">

  <!-- Breadcrumb -->
  <nav class="orioni-bc">
    <a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a>
    <span>›</span>
    <?php if ($breadcrumb_page): ?>
      <a href="<?php echo esc_url(get_permalink($breadcrumb_page)); ?>">Sản phẩm</a>
      <span>›</span>
    <?php endif; ?>
    <span class="current"><?php the_title(); ?></span>
  </nav>

  <?php
  // === LẤY ẢNH BIẾN THỂ 180/90 TỪ ACF ===
  $label180 = trim((string) get_field('label_180ml')) ?: '180ml';
  $label90 = trim((string) get_field('label_90ml')) ?: '90ml';
  $img180 = (int) get_field('img_180ml');
  $img90 = (int) get_field('img_90ml');

  $variants = [];
  if ($img180)
    $variants[] = ['key' => '180', 'label' => $label180, 'img' => $img180];
  if ($img90)
    $variants[] = ['key' => '90', 'label' => $label90, 'img' => $img90];
  ?>

  <div class="orioni-single__grid">
    <!-- Cột trái: Gallery -->
    <section class="orioni-gallery" data-gallery>

      <?php if (!empty($variants)): ?>
        <!-- ====== GALLERY THEO BIẾN THỂ (180/90) ====== -->
        <div class="g-stage" data-stage data-variants>
          <?php foreach ($variants as $i => $v): ?>
            <?php echo wp_get_attachment_image(
              $v['img'],
              'xlarge',
              false,
              [
                'class' => 'g-main vimg' . ($i === 0 ? ' is-show' : ''),
                'data-variant' => $v['key'],
                'loading' => $i === 0 ? 'eager' : 'lazy'
              ]
            ); ?>
          <?php endforeach; ?>
        </div>
        <!-- Không cần prev/next & thumbnails khi dùng biến thể -->

      <?php else: ?>

        <button class="g-nav prev" type="button" aria-label="Ảnh trước" data-prev>‹</button>

        <div class="g-stage" data-stage>
          <?php
          if ($thumb_ids) {
            foreach ($thumb_ids as $idx => $id) {
              echo wp_get_attachment_image($id, 'xlarge', false, [
                'class' => 'g-main' . ($idx === 0 ? ' is-show' : ''),
                'loading' => $idx === 0 ? 'eager' : 'lazy'
              ]);
            }
          }
          ?>
        </div>

        <button class="g-nav next" type="button" aria-label="Ảnh tiếp" data-next>›</button>

        <?php if ($thumb_ids): ?>
          <ul class="g-thumbs" data-thumbs>
            <?php foreach ($thumb_ids as $i => $tid): ?>
              <li class="g-thumb<?php echo $i === 0 ? ' is-active' : ''; ?>" data-idx="<?php echo $i; ?>">
                <?php echo wp_get_attachment_image($tid, 'medium', false, ['loading' => 'lazy']); ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      <?php endif; ?>

    </section>

    <!-- Cột phải: Thông tin -->
    <section class="orioni-info">
      <h1 class="orioni-title"><?php the_title(); ?></h1>

      <?php if ($short): ?>
        <p class="orioni-lead"><?php echo esc_html($short); ?></p>
      <?php endif; ?>

      <div class="orioni-content"><?php the_content(); ?></div>

      <div class="orioni-options">
        <?php if ($packaging): ?>
          <div class="o-group">
            <div class="o-label">Quy cách</div>
            <div class="o-values">
              <?php foreach ($packaging as $i => $text): ?>
                <span class="o-chip<?php echo $i === 0 ? ' is-active' : ''; ?>">
                  <?php echo esc_html($text); ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if (!empty($variants)): ?>
          <!-- NÚT TRỌNG LƯỢNG (LIÊN KẾT VỚI GALLERY BIẾN THỂ) -->
          <div class="o-group">
            <div class="o-label">Trọng lượng</div>
            <div class="o-values">
              <?php foreach ($variants as $i => $v): ?>
                <button type="button" class="o-chip js-variant<?php echo $i === 0 ? ' is-active' : ''; ?>"
                  data-variant="<?php echo esc_attr($v['key']); ?>">
                  <?php echo esc_html($v['label']); ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php elseif ($weights): ?>
          <!-- Fallback: dùng mảng $weights sẵn có của bạn -->
          <div class="o-group">
            <div class="o-label">Trọng lượng</div>
            <div class="o-values">
              <?php foreach ($weights as $i => $text): ?>
                <span class="o-chip<?php echo (!$packaging && $i === 0) ? ' is-active' : ''; ?>">
                  <?php echo esc_html($text); ?>
                </span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <?php
      // Mạng xã hội (giữ nguyên của bạn)
      $catalog = get_page_by_path('san-pham');
      $fb = $ins = $ytb = '';
      if ($catalog) {
        $pid = $catalog->ID;
        $fb = trim((string) get_field('fb_url', $pid));
        $ins = trim((string) get_field('ins_url', $pid));
        $ytb = trim((string) get_field('ytb_url', $pid));
      }
      ?>
      <div class="orioni-share">
        <span>Theo dõi chúng tôi</span>
        <?php if (!empty($fb)): ?><a href="<?php echo esc_url($fb); ?>" target="_blank" rel="noopener noreferrer"
            aria-label="Facebook">🔵</a><?php endif; ?>
        <?php if (!empty($ins)): ?><a href="<?php echo esc_url($ins); ?>" target="_blank" rel="noopener noreferrer"
            aria-label="Instagram">🟣</a><?php endif; ?>
        <?php if (!empty($ytb)): ?><a href="<?php echo esc_url($ytb); ?>" target="_blank" rel="noopener noreferrer"
            aria-label="YouTube">🔴</a><?php endif; ?>
      </div>
    </section>
  </div>

  <?php
  $current_id = get_the_ID();
  $term_ids = wp_get_post_terms($current_id, 'orion_cat', ['fields' => 'ids']);

  if (!empty($term_ids) && !is_wp_error($term_ids)) {
    // Lấy tất cả hương vị cùng danh mục để mobile/tablet còn trượt được
    $rel = new WP_Query([
      'post_type' => 'orion_product',
      'post__not_in' => [$current_id],
      'posts_per_page' => -1, // lấy hết, CSS sẽ ẩn bớt trên desktop
      'tax_query' => [
        [
          'taxonomy' => 'orion_cat',
          'field' => 'term_id',
          'terms' => $term_ids,
        ]
      ],
      'orderby' => 'date',
      'order' => 'DESC',
    ]);

    if ($rel->have_posts()): ?>
      <section class="orioni-related">
        <h2 class="orioni-related__title">HƯƠNG VỊ</h2>

        <div class="rel-viewport" data-relviewport>
          <!-- Nút nav (chỉ hiện ở tablet & mobile qua CSS) -->
          <button class="rel-nav prev" type="button" aria-label="Trước" data-relprev>‹</button>

          <ul class="rel-track" data-reltrack>
            <?php while ($rel->have_posts()):
              $rel->the_post();
              $img_id = get_field('product_image') ?: get_post_thumbnail_id();
              $desc = get_field('short_desc') ?: wp_trim_words(get_the_excerpt(), 18);
              ?>
              <li class="orioni-card">
                <a class="orioni-card__link" href="<?php the_permalink(); ?>">
                  <div class="orioni-card__frame">
                    <?php if ($img_id)
                      echo wp_get_attachment_image($img_id, 'large', false, ['loading' => 'lazy']); ?>
                  </div>
                  <h3 class="orioni-card__title"><?php the_title(); ?></h3>
                  <p class="orioni-card__desc"><?php echo esc_html($desc); ?></p>
                </a>
              </li>
            <?php endwhile; ?>
          </ul>

          <button class="rel-nav next" type="button" aria-label="Tiếp" data-relnext>›</button>
        </div>
      </section>
      <?php
    endif;
    wp_reset_postdata();
  }
  ?>

  <?php
  // ===== Block: Thông tin chi tiết =====
  $title = trim((string) get_field('detail_title'));
  $desc1 = get_field('detail_desc_top');     // WYSIWYG -> in an toàn bằng wp_kses_post
  $img_id = (int) get_field('detail_image');  // Image ID
  $desc2 = get_field('detail_desc_bottom');

  if ($title || $desc1 || $img_id || $desc2): ?>
    <section class="orioni-spec">
      <div class="container">

        <h2 class="orioni-spec__title">
          <?php echo esc_html($title ?: 'Thông tin chi tiết'); ?>
        </h2>

        <?php if ($desc1): ?>
          <div class="orioni-spec__text">
            <?php echo wp_kses_post($desc1); ?>
          </div>
        <?php endif; ?>

        <?php if ($img_id): ?>
          <figure class="orioni-spec__media">
            <?php
            // Lấy alt/caption để SEO tốt hơn
            $alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
            echo wp_get_attachment_image($img_id, 'full', false, [
              'class' => 'orioni-spec__img',
              'alt' => $alt ?: get_the_title($img_id),
              'loading' => 'lazy'
            ]);
            ?>
          </figure>
        <?php endif; ?>

        <?php if ($desc2): ?>
          <div class="orioni-spec__text">
            <?php echo wp_kses_post($desc2); ?>
          </div>
        <?php endif; ?>

      </div>
    </section>
  <?php endif; ?>


  <?php
// ====== BỘ SƯU TẬP (lấy Gallery block/shortcode trong nội dung bài) ======
$gallery_images = get_post_galleries_images(get_the_ID()); // mảng các gallery (mỗi gallery là 1 mảng URL ảnh)
$collection = [];
if (!empty($gallery_images) && !empty($gallery_images[0])) {
  // Chỉ lấy gallery đầu tiên
  foreach ($gallery_images[0] as $url) {
    // Cố gắng đổi URL -> ID để lấy size chuẩn, alt, caption
    $aid = attachment_url_to_postid($url);
    if ($aid) {
      // thumb dùng size 'large' (nhanh), full để xem phóng to
      $thumb = wp_get_attachment_image_src($aid, 'large');
      $full  = wp_get_attachment_image_src($aid, 'full');
      $alt   = get_post_meta($aid, '_wp_attachment_image_alt', true);
      $caption = wp_get_attachment_caption($aid);

      $collection[] = [
        'id'     => $aid,
        'thumb'  => $thumb ? $thumb[0] : $url,
        'full'   => $full  ? $full[0]  : $url,
        'alt'    => $alt ?: get_the_title($aid),
        'caption'=> $caption ?: '',
      ];
    } else {
      // Không đổi được sang ID thì dùng URL gốc
      $collection[] = [
        'id'     => 0,
        'thumb'  => $url,
        'full'   => $url,
        'alt'    => '',
        'caption'=> '',
      ];
    }
  }
}

if (!empty($collection)) : ?>
  <section class="orioni-collection" data-collection>
    <h2 class="orioni-stit">BỘ SƯU TẬP</h2>

    <ul class="oc-grid">
      <?php foreach ($collection as $i => $img): ?>
        <li class="oc-item">
          <a href="<?php echo esc_url($img['full']); ?>"
             class="oc-link"
             data-zoom
             data-idx="<?php echo (int)$i; ?>"
             data-full="<?php echo esc_url($img['full']); ?>"
             data-caption="<?php echo esc_attr($img['caption']); ?>">
            <img
              src="<?php echo esc_url($img['thumb']); ?>"
              alt="<?php echo esc_attr($img['alt']); ?>"
              loading="lazy">
          </a>
          <?php if ($img['caption']): ?>
            <figcaption class="oc-cap"><?php echo esc_html($img['caption']); ?></figcaption>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>

    <!-- Lightbox -->
    <div class="oc-lightbox" data-ocbox aria-hidden="true">
      <button class="oc-close" type="button" aria-label="Đóng" data-occlose>×</button>
      <button class="oc-nav prev" type="button" aria-label="Ảnh trước" data-ocprev>‹</button>
      <figure class="oc-view">
        <img src="" alt="" data-ocimg>
        <figcaption class="oc-viewcap" data-occap></figcaption>
      </figure>
      <button class="oc-nav next" type="button" aria-label="Ảnh tiếp" data-ocnext>›</button>
    </div>
  </section>
<?php endif; ?>

<?php
// ====== BỘ SƯU TẬP (ACF FREE: image_1...image_10) ======
$title = trim((string) get_field('collection_title'));
if ($title === '') $title = 'Bộ sưu tập';

// Tự quét toàn bộ field col_img_*
$ids = [];
$all = (array) get_fields(get_the_ID());
foreach ($all as $key => $val) {
  if (preg_match('/^col_img_(\d+)$/', $key, $m) && !empty($val)) {
    $ids[(int)$m[1]] = (int)$val; // lưu theo số thứ tự để còn sort
  }
}
ksort($ids);
$ids = array_values($ids);

if (!empty($ids)) :
  // ... render grid + lightbox như đã gửi ...
endif;


if (!empty($ids)) : ?>
  <section class="orioni-collection" data-collection>
    <h2 class="orioni-stit"><?php echo esc_html($title); ?></h2>

    <ul class="oc-grid">
      <?php foreach ($ids as $idx => $aid):
        $thumb = wp_get_attachment_image_src($aid, 'large');
        $full  = wp_get_attachment_image_src($aid, 'full');
        $alt   = get_post_meta($aid, '_wp_attachment_image_alt', true) ?: get_the_title($aid);
        $cap   = wp_get_attachment_caption($aid) ?: '';
      ?>
        <li class="oc-item">
          <a href="<?php echo esc_url($full ? $full[0] : wp_get_attachment_url($aid)); ?>"
             class="oc-link"
             data-zoom
             data-idx="<?php echo (int)$idx; ?>"
             data-full="<?php echo esc_url($full ? $full[0] : wp_get_attachment_url($aid)); ?>"
             data-caption="<?php echo esc_attr($cap); ?>">
            <?php echo wp_get_attachment_image($aid, 'large', false, ['alt' => $alt, 'loading' => 'lazy']); ?>
          </a>
          <?php if ($cap): ?><figcaption class="oc-cap"><?php echo esc_html($cap); ?></figcaption><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ul>

    <!-- Lightbox -->
    <div class="oc-lightbox" data-ocbox aria-hidden="true">
      <button class="oc-close" type="button" aria-label="Đóng" data-occlose>×</button>
      <button class="oc-nav prev" type="button" aria-label="Ảnh trước" data-ocprev>‹</button>
      <figure class="oc-view">
        <img src="" alt="" data-ocimg>
        <figcaption class="oc-viewcap" data-occap></figcaption>
      </figure>
      <button class="oc-nav next" type="button" aria-label="Ảnh tiếp" data-ocnext>›</button>
    </div>
  </section>
<?php endif; ?>

  </section>
  </div>
</main>

<?php get_footer(); ?>
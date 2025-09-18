<?php
/** Single – Orion Product (FREE gallery) */
get_header();
the_post();

/* ===== Helpers (lấy từ trang hiện tại, fallback Options nếu có) ===== */
$prefer = function ($key) {
  $v = function_exists('get_field') ? get_field($key) : null;
  if (!empty($v)) return $v;
  return function_exists('get_field') ? get_field($key, 'option') : null;
};

/* ===== HERO ===== */
$hero_img   = $prefer('hero_image');
$hero_title = $prefer('hero_title') ?: get_the_title();
$overlay    = is_numeric($prefer('hero_overlay_opacity')) ? max(0, min(90, (int)$prefer('hero_overlay_opacity'))) : 55;
$height_vh  = (int)($prefer('hero_height_vh') ?: 70);
$bg_url     = '';
if (is_array($hero_img) && !empty($hero_img['url']))       $bg_url = $hero_img['url'];
elseif (has_post_thumbnail())                               $bg_url = get_the_post_thumbnail_url(null, 'full');
?>

<section class="about-hero" style="--h:<?php echo $height_vh; ?>vh; --ov:<?php echo $overlay/100; ?>; <?php echo $bg_url ? 'background-image:url('.esc_url($bg_url).');' : ''; ?>">
  <div class="about-hero__overlay"></div>
  <div class="container">
    <div class="about-hero__box">
      <h1 class="about-hero__title"><?php echo esc_html($hero_title); ?></h1>
    </div>
  </div>
</section>

<?php
/* ===== Breadcrumb base ===== */
$breadcrumb_page = get_page_by_path('san-pham');

/* ===== Ảnh chính + gallery ID (ưu tiên ACF rời, rồi shortcode/block, rồi attachment con) ===== */
$img_id      = get_field('product_image') ?: get_post_thumbnail_id();
$gallery_ids = [];

// ACF free: gallery_1..gallery_8
for ($i = 1; $i <= 8; $i++) {
  $gid = (int) get_field('gallery_'.$i);
  if ($gid) $gallery_ids[] = $gid;
}

// Shortcode [gallery ids=""]
if (!$gallery_ids) {
  $gal = get_post_gallery(get_the_ID(), false);
  if (!empty($gal['ids'])) $gallery_ids = array_map('intval', explode(',', $gal['ids']));
}

// Gutenberg gallery (map URL -> ID)
if (!$gallery_ids) {
  $gals = get_post_galleries_images(get_the_ID());
  if (!empty($gals)) {
    foreach ($gals[0] as $url) {
      $aid = attachment_url_to_postid($url);
      if ($aid) $gallery_ids[] = $aid;
    }
  }
}

// Attachment con (fallback)
if (!$gallery_ids) {
  $attachments = get_children([
    'post_parent'    => get_the_ID(),
    'post_type'      => 'attachment',
    'post_mime_type' => 'image',
    'exclude'        => $img_id ? [$img_id] : [],
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
  ]);
  foreach ($attachments as $att) $gallery_ids[] = (int) $att->ID;
}

// Mảng ảnh dùng cho fallback slider
$thumb_ids  = array_values(array_unique(array_filter(array_merge([$img_id], $gallery_ids))));
$thumb_urls = []; // để sẵn, tránh warning khi foreach

/* ===== ACF khác ===== */
$packaging = (array) get_field('packaging');
$weights   = (array) get_field('weights');
$short     = (string) get_field('short_desc');

/* ===== BIẾN THỂ DẠNG HỘP (2P/6P/…): pack_{i}_label/weight/img ===== */
$pack_variants = [];
$MAX_PACK = 8;
for ($i = 1; $i <= $MAX_PACK; $i++) {
  $lbl  = trim((string) get_field("pack_{$i}_label"));
  $w    = trim((string) get_field("pack_{$i}_weight"));
  $imgV = (int) get_field("pack_{$i}_img");
  if ($lbl !== '' && $imgV) {
    $pack_variants[] = [
      'key'    => "p{$i}",
      'label'  => $lbl,
      'weight' => $w,
      'img'    => $imgV,
    ];
  }
}

/* ===== BIẾN THỂ SỮA 180/90 (tuỳ sản phẩm) ===== */
$milk_variants = [];
$img180   = (int) get_field('img_180ml');
$label180 = trim((string) get_field('label_180ml')) ?: '180ml';
if ($img180) $milk_variants[] = ['key' => '180', 'label' => $label180, 'img' => $img180];

$img90   = (int) get_field('img_90ml');
$label90 = trim((string) get_field('label_90ml')) ?: '90ml';
if ($img90)  $milk_variants[] = ['key' => '90', 'label' => $label90,  'img' => $img90];
?>

<main class="container orioni-single">

  <!-- Breadcrumb -->
  <nav class="orioni-bc">
    <a href="<?php echo esc_url(home_url('/')); ?>">Trang chủ</a><span>›</span>
    <?php if ($breadcrumb_page): ?>
      <a href="<?php echo esc_url(get_permalink($breadcrumb_page)); ?>">Sản phẩm</a><span>›</span>
    <?php endif; ?>
    <span class="current"><?php the_title(); ?></span>
  </nav>

  <div class="orioni-single__grid">
    <!-- ===== Cột trái: GALLERY ===== -->
    <section class="orioni-gallery" data-gallery>

      <?php if (!empty($pack_variants)): ?>
        <!-- Gallery theo DẠNG HỘP -->
        <div class="g-stage" data-stage data-packs>
          <?php foreach ($pack_variants as $i => $p): ?>
            <?php echo wp_get_attachment_image(
              $p['img'], 'xlarge', false,
              ['class' => 'g-main pimg'.($i===0?' is-show':''), 'data-pack'=>$p['key'], 'loading'=>$i===0?'eager':'lazy', 'decoding'=>'async', 'fetchpriority'=>$i===0?'high':'low']
            ); ?>
          <?php endforeach; ?>
        </div>

      <?php elseif (!empty($milk_variants)): ?>
        <!-- Gallery theo 180/90 -->
        <div class="g-stage" data-stage data-variants>
          <?php foreach ($milk_variants as $i => $v): ?>
            <?php echo wp_get_attachment_image(
              $v['img'], 'xlarge', false,
              ['class' => 'g-main vimg'.($i===0?' is-show':''), 'data-variant'=>$v['key'], 'loading'=>$i===0?'eager':'lazy', 'decoding'=>'async', 'fetchpriority'=>$i===0?'high':'low']
            ); ?>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        <!-- Fallback slider nhiều ảnh -->
        <button class="g-nav prev" type="button" aria-label="Ảnh trước" data-prev>‹</button>
        <div class="g-stage" data-stage>
          <?php
          $slideIndex = 0;
          foreach ($thumb_ids as $id) {
            echo wp_get_attachment_image($id, 'xlarge', false, [
              'class'        => 'g-main'.($slideIndex===0?' is-show':''), 
              'loading'      => $slideIndex===0?'eager':'lazy',
              'decoding'     => 'async',
              'fetchpriority'=> $slideIndex===0?'high':'low'
            ]);
            $slideIndex++;
          }
          foreach ($thumb_urls as $url) {
            $u = esc_url($url);
            echo '<img class="g-main'.($slideIndex===0?' is-show':'').'" src="'.$u.'" alt="" loading="'.($slideIndex===0?'eager':'lazy').'" decoding="async">';
            $slideIndex++;
          }
          if ($slideIndex === 0) {
            the_post_thumbnail('xlarge', ['class'=>'g-main is-show','loading'=>'eager','decoding'=>'async']);
          }
          ?>
        </div>
        <?php if ((count($thumb_ids) + count($thumb_urls)) > 1): ?>
          <ul class="g-thumbs" data-thumbs>
            <?php $t=0; foreach ($thumb_ids as $id): ?>
              <li class="g-thumb<?php echo $t===0?' is-active':''; ?>" data-idx="<?php echo $t; ?>">
                <?php echo wp_get_attachment_image($id, 'medium', false, ['loading'=>'lazy','decoding'=>'async']); ?>
              </li>
            <?php $t++; endforeach; foreach ($thumb_urls as $url): ?>
              <li class="g-thumb<?php echo $t===0?' is-active':''; ?>" data-idx="<?php echo $t; ?>">
                <img src="<?php echo esc_url($url); ?>" alt="" loading="lazy" decoding="async">
              </li>
            <?php $t++; endforeach; ?>
          </ul>
        <?php endif; ?>
        <button class="g-nav next" type="button" aria-label="Ảnh tiếp" data-next>›</button>
      <?php endif; ?>

    </section>

    <!-- ===== Cột phải: THÔNG TIN + NÚT CHỌN ===== -->
    <section class="orioni-info">
      <h1 class="orioni-title"><?php the_title(); ?></h1>
      <?php if ($short): ?><p class="orioni-lead"><?php echo esc_html($short); ?></p><?php endif; ?>
      <div class="orioni-content"><?php the_content(); ?></div>

      <div class="orioni-options">
        <?php if (!empty($pack_variants)): ?>
          <div class="o-group">
            <div class="o-label">Dạng sản phẩm</div>
            <div class="o-values">
              <?php foreach ($pack_variants as $i => $p): ?>
                <button type="button" class="o-chip js-pack<?php echo $i===0?' is-active':''; ?>"
                        data-pack="<?php echo esc_attr($p['key']); ?>" data-weight="<?php echo esc_attr($p['weight']); ?>">
                  <?php echo esc_html($p['label']); ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="o-group">
            <div class="o-label">Trọng lượng</div>
            <div class="o-values">
              <span class="o-chip is-active js-pack-weight"><?php echo esc_html($pack_variants[0]['weight'] ?: '—'); ?></span>
            </div>
          </div>
        <?php elseif (!empty($milk_variants)): ?>
          <div class="o-group">
            <div class="o-label">Trọng lượng</div>
            <div class="o-values">
              <?php foreach ($milk_variants as $i => $v): ?>
                <button type="button" class="o-chip js-variant<?php echo $i===0?' is-active':''; ?>" data-variant="<?php echo esc_attr($v['key']); ?>">
                  <?php echo esc_html($v['label']); ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <?php
      // Social từ Page "SẢN PHẨM"
      $fb=$ins=$ytb=''; if ($catalog = get_page_by_path('san-pham')) {
        $pid=$catalog->ID; $fb=trim((string)get_field('fb_url',$pid)); $ins=trim((string)get_field('ins_url',$pid)); $ytb=trim((string)get_field('ytb_url',$pid));
      } ?>
      <div class="orioni-share">
        <span>Theo dõi chúng tôi</span>
        <?php if ($fb):  ?><a href="<?php echo esc_url($fb); ?>"  target="_blank" rel="noopener noreferrer" aria-label="Facebook">🔵</a><?php endif; ?>
        <?php if ($ins): ?><a href="<?php echo esc_url($ins); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram">🟣</a><?php endif; ?>
        <?php if ($ytb): ?><a href="<?php echo esc_url($ytb); ?>" target="_blank" rel="noopener noreferrer" aria-label="YouTube">🔴</a><?php endif; ?>
      </div>
    </section>
  </div>

  <?php
  /* ===== HƯƠNG VỊ (sản phẩm cùng danh mục) ===== */
  $current_id = get_the_ID();
  $term_ids   = wp_get_post_terms($current_id, 'orion_cat', ['fields'=>'ids']);
  if (!empty($term_ids) && !is_wp_error($term_ids)) {
    $rel = new WP_Query([
      'post_type'      => 'orion_product',
      'post__not_in'   => [$current_id],
      'posts_per_page' => -1,
      'tax_query'      => [[ 'taxonomy'=>'orion_cat','field'=>'term_id','terms'=>$term_ids ]],
      'orderby'        => 'date',
      'order'          => 'DESC',
    ]);
    if ($rel->have_posts()): ?>
      <section class="orioni-related">
        <h2 class="orioni-related__title">HƯƠNG VỊ</h2>
        <div class="rel-viewport" data-relviewport>
          <button class="rel-nav prev" type="button" aria-label="Trước" data-relprev>‹</button>
          <ul class="rel-track" data-reltrack>
            <?php while ($rel->have_posts()): $rel->the_post();
              $img = get_field('product_image') ?: get_post_thumbnail_id();
              $desc= get_field('short_desc') ?: wp_trim_words(get_the_excerpt(),18); ?>
              <li class="orioni-card">
                <a class="orioni-card__link" href="<?php the_permalink(); ?>">
                  <div class="orioni-card__frame">
                    <?php if ($img) echo wp_get_attachment_image($img,'large',false,['loading'=>'lazy']); ?>
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
    <?php endif; wp_reset_postdata(); } ?>

<?php
/* ===== Thông tin chi tiết: dùng 2 field duy nhất ===== */
$d_title = trim((string) get_field('detail_title')) ?: '';
$d_html  = get_field('detail_content'); // WYSIWYG tự do

// Nếu bạn muốn: khi trống cả title + content thì ẩn luôn section
if ($d_title || !empty($d_html)) : ?>
  <section class="orioni-spec">
    <div class="container">
      <?php if ($d_title): ?>
        <h2 class="orioni-spec__title"><?php echo esc_html($d_title); ?></h2>
      <?php endif; ?>

      <?php if (!empty($d_html)): ?>
        <div class="orioni-spec__rich">
          <?php echo apply_filters('the_content', $d_html); ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
<?php endif; ?>


 <?php
/* ===== Bộ sưu tập từ ACF Free: col_img_1..n ===== */
$col_title = trim((string) get_field('collection_title')) ?: 'Bộ sưu tập';
$ids = [];
if (function_exists('get_fields')) {
  $all = (array) get_fields(get_the_ID());
  foreach ($all as $k => $v) {
    if (preg_match('/^col_img_(\d+)$/', $k, $m) && !empty($v)) {
      $ids[(int)$m[1]] = (int)$v;
    }
  }
  ksort($ids);
  $ids = array_values($ids);
}
if (!empty($ids)): ?>
  <section class="orioni-collection" data-collection>
    <h2 class="orioni-stit"><?php echo esc_html($col_title); ?></h2>

    <ul class="oc-grid" data-collection-grid data-total="<?php echo (int) count($ids); ?>">
      <?php foreach ($ids as $idx => $aid):
        $full = wp_get_attachment_image_src($aid,'full');
        $alt  = get_post_meta($aid,'_wp_attachment_image_alt',true) ?: get_the_title($aid);
        $cap  = wp_get_attachment_caption($aid) ?: ''; ?>
        <li class="oc-item oc-it" data-idx="<?php echo (int)$idx; ?>">
          <a href="<?php echo esc_url($full ? $full[0] : wp_get_attachment_url($aid)); ?>"
             class="oc-link"
             data-zoom
             data-idx="<?php echo (int)$idx; ?>"
             data-full="<?php echo esc_url($full ? $full[0] : wp_get_attachment_url($aid)); ?>"
             data-caption="<?php echo esc_attr($cap); ?>">
            <?php echo wp_get_attachment_image($aid,'large',false,['alt'=>$alt,'loading'=>'lazy']); ?>
          </a>
          <?php if ($cap): ?>
            <figcaption class="oc-cap"><?php echo esc_html($cap); ?></figcaption>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>

      <!-- Tile +N -->
      <li class="oc-item oc-more" data-more-tile style="display:none">
        <a href="#" class="oc-link oc-more__link" data-more>
          <img class="oc-more__img" src="" alt="" loading="lazy">
          <span class="oc-more__badge" data-moretext></span>
        </a>
      </li>
    </ul>

    <!-- Lightbox giữ nguyên -->
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

</main>

<?php get_footer(); ?>

<?php
/**
 * Footer template: 1 menu => nhiều cột theo mục cha
 */
?>

<footer class="site-footer">
  <div class="footer-top">
    <div class="orioni-container">
      <div class="footer-menus">
        <?php
        $locations = get_nav_menu_locations();
        if ( isset($locations['footer']) && $locations['footer'] ) {
          $menu_obj   = wp_get_nav_menu_object( $locations['footer'] );
          $menu_items = wp_get_nav_menu_items( $menu_obj->term_id );

          if ( $menu_items ) {
            usort($menu_items, function($a, $b){
              return (int)$a->menu_order - (int)$b->menu_order;
            });

            $cols = [];
            foreach ($menu_items as $it) {
              if ((int)$it->menu_item_parent === 0) {
                $cols[$it->ID] = [
                  'title'    => $it->title,
                  'url'      => $it->url,
                  'children' => [],
                ];
              }
            }
            foreach ($menu_items as $it) {
              $pid = (int)$it->menu_item_parent;
              if ($pid !== 0 && isset($cols[$pid])) {
                $cols[$pid]['children'][] = $it; // chỉ lấy tới cấp 2
              }
            }

            foreach ($cols as $col) {
              echo '<div class="footer-col">';
                echo '<h4>' . esc_html($col['title']) . '</h4>';
                if (!empty($col['children'])) {
                  echo '<ul>';
                  foreach ($col['children'] as $child) {
                    $url   = esc_url($child->url);
                    $label = esc_html($child->title);
                    echo "<li><a href=\"{$url}\">{$label}</a></li>";
                  }
                  echo '</ul>';
                }
              echo '</div>';
            }
          }
        } else {
          echo '<em>Hãy gán menu cho vị trí Footer trong Giao diện → Menu → Quản lý vị trí.</em>';
        }
        ?>
      </div><!--/.footer-menus-->
    </div><!--/.orioni-container-->
  </div><!--/.footer-top-->

  <div class="footer-bottom">
    <div class="orioni-container"><!-- ⬅️ canh giữa giống header -->
      <p>Tầng 2, Tòa nhà cao nhì thế giới xếp từ dưới lên tại Trái Đất | SĐT : 0366778386</p>
      <p>© <?php echo date('Y'); ?> . All Rights Reserved</p>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

<?php wp_footer(); ?>
</body>
</html>

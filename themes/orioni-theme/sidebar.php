<aside class="sidebar">
    <?php if (is_active_sidebar('main-sidebar')): ?>
        <?php dynamic_sidebar('main-sidebar'); ?>
    <?php else: ?>
        <p>Thêm widget tại Giao diện → Widget.</p>
    <?php endif; ?>
</aside>
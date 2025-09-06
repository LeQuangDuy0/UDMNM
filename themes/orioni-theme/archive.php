<?php get_header(); ?>

<h1><?php the_archive_title(); ?></h1>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class(); ?>>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <div class="entry-summary">
                <?php the_excerpt(); ?>
            </div>
        </article>
    <?php endwhile; ?>
<?php else : ?>
    <p><?php _e('Không có bài viết nào.', 'orioni-theme'); ?></p>
<?php endif; ?>

<?php get_footer(); ?>

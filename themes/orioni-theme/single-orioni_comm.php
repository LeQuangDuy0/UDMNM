<?php get_header(); ?>
<main class="orioni-wrap">
  <?php if (have_posts()): while (have_posts()): the_post(); ?>
    <article class="orioni-single">
      <h1 class="orioni-single__title"><?php the_title(); ?></h1>
      <div class="orioni-single__thumb"><?php the_post_thumbnail('large'); ?></div>
      <div class="orioni-single__content"><?php the_content(); ?></div>
    </article>
  <?php endwhile; endif; ?>
</main>
<?php get_footer(); ?>

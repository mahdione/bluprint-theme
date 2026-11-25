<?php
defined('ABSPATH')||exit;

$archive_title=trim(wp_strip_all_tags(get_the_archive_title()));
if($archive_title==='')$archive_title='مقالات و راهنماها';

get_header();
?>
<main id="main-content" class="bp-blog-archive" dir="rtl">
    <div class="bp-blog-archive__container">
        <?php get_template_part('template-parts/components/breadcrumbs/breadcrumbs',null,[
            'variant'=>'compact',
            'label'=>'مسیر صفحه',
            'class'=>'bp-blog-archive__breadcrumb',
        ]); ?>

        <header class="bp-blog-archive__header">
            <h1 class="bp-blog-archive__title"><?php echo esc_html($archive_title); ?></h1>
        </header>

        <?php if(have_posts()): ?>
            <div class="bp-blog-archive__grid">
                <?php while(have_posts()):the_post(); ?>
                    <?php get_template_part('template-parts/components/cards/article-card',null,[
                        'post_id'=>get_the_ID(),
                        'heading_level'=>2,
                    ]); ?>
                <?php endwhile; ?>
            </div>

            <?php the_posts_pagination([
                'mid_size'=>1,
                'end_size'=>1,
                'prev_text'=>'<span aria-hidden="true">→</span><span>قبلی</span>',
                'next_text'=>'<span>بعدی</span><span aria-hidden="true">←</span>',
                'screen_reader_text'=>'صفحه‌بندی نوشته‌ها',
                'aria_label'=>'صفحه‌بندی نوشته‌ها',
                'class'=>'bp-blog-archive__pagination',
            ]); ?>
        <?php else: ?>
            <p class="bp-blog-archive__empty"><?php esc_html_e('محتوایی پیدا نشد.','bluprint-theme'); ?></p>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
<?php
defined('ABSPATH')||exit;

$show_cta=false;
get_header();
?>
<main id="main-content" class="bp-post-page" dir="rtl">
    <div class="bp-post-page__container">
        <?php while(have_posts()):the_post(); ?>
            <?php
            $post_id=get_the_ID();
            $categories=get_the_category($post_id);
            $author_id=(int)get_post_field('post_author',$post_id);
            $author_name=get_the_author_meta('display_name',$author_id);
            $published_date=get_the_date(DATE_W3C,$post_id);
            $modified_date=get_the_modified_date(DATE_W3C,$post_id);
            $has_updated=$modified_date!==$published_date;
            $excerpt=trim((string)get_post_field('post_excerpt',$post_id));
            $image_id=get_post_thumbnail_id($post_id);
            $recent_posts=get_posts([
                'post_type'=>'post',
                'post_status'=>'publish',
                'posts_per_page'=>4,
                'post__not_in'=>[$post_id],
                'orderby'=>'date',
                'order'=>'DESC',
                'ignore_sticky_posts'=>true,
                'no_found_rows'=>true,
            ]);
            $cta_banner=function_exists('bluprint_get_banner_data')?bluprint_get_banner_data(1):['enabled'=>0];
            $show_cta=!empty($cta_banner['enabled'])&&(
                trim($cta_banner['title'])!==''||
                trim($cta_banner['eyebrow'])!==''||
                trim($cta_banner['description'])!==''||
                trim($cta_banner['button_text'])!==''
            );
            $post_content=apply_filters('the_content',get_the_content());
            $toc_data=bluprint_post_content_toc($post_content);
            ?>
            <article <?php post_class('bp-post'); ?>>
                <?php get_template_part('template-parts/components/breadcrumbs/breadcrumbs',null,[
                    'variant'=>'compact',
                    'label'=>'مسیر مقاله',
                    'class'=>'bp-post__breadcrumb',
                ]); ?>

                <header class="bp-post__hero<?php echo $image_id?' has-featured-image':' bp-post__hero--no-image'; ?>">
                    <div class="bp-post__hero-copy">
                        <?php if($categories): ?>
                            <nav class="bp-post__categories" aria-label="دسته‌بندی مقاله">
                                <?php foreach($categories as $category): ?>
                                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>">
                                        <?php echo esc_html($category->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>

                        <h1 class="bp-post__title"><?php echo esc_html(get_the_title($post_id)); ?></h1>

                        <?php if($excerpt!==''): ?>
                            <p class="bp-post__summary"><?php echo esc_html(get_the_excerpt($post_id)); ?></p>
                        <?php endif; ?>

                        <div class="bp-post__meta">
                            <?php if($author_name!==''): ?>
                                <span class="bp-post__meta-item">
                                    <span class="bp-post__meta-label">نویسنده</span>
                                    <a href="<?php echo esc_url(get_author_posts_url($author_id)); ?>"><?php echo esc_html($author_name); ?></a>
                                </span>
                            <?php endif; ?>

                            <span class="bp-post__meta-item">
                                <span class="bp-post__meta-label">انتشار</span>
                                <time datetime="<?php echo esc_attr($published_date); ?>"><?php echo esc_html(get_the_date('j F Y',$post_id)); ?></time>
                            </span>

                            <?php if($has_updated): ?>
                                <span class="bp-post__meta-item">
                                    <span class="bp-post__meta-label">به‌روزرسانی</span>
                                    <time datetime="<?php echo esc_attr($modified_date); ?>"><?php echo esc_html(get_the_modified_date('j F Y',$post_id)); ?></time>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if($image_id): ?>
                        <figure class="bp-post__featured">
                            <?php echo wp_get_attachment_image($image_id,'full',false,[
                                'class'=>'bp-post__featured-image',
                                'alt'=>get_post_meta($image_id,'_wp_attachment_image_alt',true)?:get_the_title(),
                                'loading'=>'eager',
                                'fetchpriority'=>'high',
                                'decoding'=>'async',
                            ]); ?>
                            <?php if($caption=get_the_post_thumbnail_caption($post_id)): ?>
                                <figcaption><?php echo esc_html($caption); ?></figcaption>
                            <?php endif; ?>
                        </figure>
                    <?php endif; ?>
                </header>

                <div class="bp-post__layout">
                    <aside class="bp-post__sidebar" aria-label="راهنمای مطالعه مقاله">
                        <div class="bp-post__reading-controls" role="group" aria-label="کنترل اندازه متن مقاله">
                            <span class="bp-post__reading-label">اندازه متن</span>
                            <div class="bp-post__reading-control-buttons">
                                <button type="button" data-post-font-step="-5" aria-label="کوچک‌تر کردن متن" title="کوچک‌تر کردن متن">
                                    <span aria-hidden="true">−</span>
                                </button>
                                <output data-post-font-value aria-live="polite">100%</output>
                                <button type="button" data-post-font-step="5" aria-label="بزرگ‌تر کردن متن" title="بزرگ‌تر کردن متن">
                                    <span aria-hidden="true">+</span>
                                </button>
                            </div>
                        </div>

                        <details class="bp-post__toc" data-post-toc <?php echo wp_is_mobile()?'':'open'; ?>>
                            <summary class="bp-post__toc-summary">
                                <span>فهرست مطالب</span>
                                <span class="bp-post__toc-count"><?php echo esc_html(count($toc_data['items'])); ?> عنوان</span>
                            </summary>

                            <nav class="bp-post__toc-nav" aria-label="فهرست مطالب مقاله">
                                <?php if($toc_data['items']): ?>
                                    <ol class="bp-post__toc-list">
                                        <?php foreach($toc_data['items'] as $item): ?>
                                            <li class="bp-post__toc-item bp-post__toc-item--level-<?php echo esc_attr($item['level']); ?>">
                                                <a href="<?php echo esc_url('#'.$item['id']); ?>" title="<?php echo esc_attr($item['title']); ?>">
                                                    <?php echo esc_html($item['title']); ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ol>
                                <?php else: ?>
                                    <p class="bp-post__toc-empty">برای این مقاله عنوانی ثبت نشده است.</p>
                                <?php endif; ?>
                            </nav>
                        </details>
                    </aside>

                    <div class="bp-post__content entry-content" data-post-content style="--bp-post-text-scale:1">
                        <?php echo $toc_data['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WordPress's the_content filters render trusted post content. ?>
                        <?php wp_link_pages([
                            'before'=>'<nav class="bp-post__page-links" aria-label="صفحه‌های مقاله"><span>صفحه:</span>',
                            'after'=>'</nav>',
                            'link_before'=>'<span>',
                            'link_after'=>'</span>',
                        ]); ?>
                    </div>
                </div>
            </article>

            <?php if($recent_posts): ?>
                <section class="bp-post__recent" aria-labelledby="bp-post-recent-title">
                    <header class="bp-post__recent-header">
                        <p class="bp-post__recent-eyebrow">ادامه مطالعه</p>
                        <h2 class="bp-post__recent-title" id="bp-post-recent-title">تازه‌ترین مطالب</h2>
                    </header>
                    <div class="bp-post__recent-grid">
                        <?php foreach($recent_posts as $recent_post): ?>
                            <?php get_template_part('template-parts/components/cards/article-card',null,[
                                'post_id'=>$recent_post->ID,
                                'heading_level'=>3,
                            ]); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endwhile; ?>

        <?php if($show_cta): ?>
            <div class="bp-post__cta">
                <?php get_template_part('template-parts/components/banners/banner',null,['banner_id'=>1]); ?>
            </div>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
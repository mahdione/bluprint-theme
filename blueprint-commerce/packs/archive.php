<?php
defined('ABSPATH')||exit;

get_header();

$is_category=is_tax('bp_pack_category');
$term=$is_category?get_queried_object():null;
$title=$is_category&&$term instanceof WP_Term?$term->name:post_type_archive_title('',false);
$title=trim((string)$title);
if($title==='')$title='پک‌ها';

$description=$is_category&&$term instanceof WP_Term?term_description($term):'';
$term_content=$is_category&&$term instanceof WP_Term&&function_exists('bluprint_get_term_content')?bluprint_get_term_content($term->term_id):'';
$has_term_content=trim(wp_strip_all_tags($term_content))!=='';
$active_category=$is_category&&$term instanceof WP_Term?(int)$term->term_id:0;
$archive_url=get_post_type_archive_link('bp_pack');
$archive_url=$archive_url?:home_url('/');
$shop_url=function_exists('wc_get_page_permalink')?wc_get_page_permalink('shop'):home_url('/');
$total_packs=(int)wp_count_posts('bp_pack')->publish;
$visible_count=(int)$GLOBALS['wp_query']->found_posts;
$terms=get_terms([
    'taxonomy'=>'bp_pack_category',
    'hide_empty'=>true,
    'orderby'=>'name',
    'order'=>'ASC',
]);
if(is_wp_error($terms))$terms=[];
?>
<main id="main-content" class="bp-archive bp-pack-archive" dir="rtl">
    <header class="bp-pack-archive-hero">
        <?php get_template_part('template-parts/components/breadcrumbs/breadcrumbs',null,[
            'variant'=>'compact',
            'label'=>'مسیر پک‌ها',
            'class'=>'bp-pack-archive__breadcrumb',
        ]); ?>

        <p class="bp-pack-archive-hero__eyebrow">پک‌ها / <?php echo $is_category?'دسته‌بندی':'همه'; ?></p>
        <h1 class="bp-pack-archive-hero__title" id="bp-pack-archive-title"><?php echo esc_html($title); ?></h1>

        <?php if(trim(wp_strip_all_tags($description))!==''): ?>
            <div class="bp-pack-archive-hero__description"><?php echo wp_kses_post(wpautop($description)); ?></div>
        <?php endif; ?>

        <p class="bp-pack-archive-hero__count">
            <strong><?php echo esc_html(number_format_i18n($visible_count)); ?></strong>
            پک در این بخش
        </p>
    </header>

    <div class="bp-archive-layout">
        <div class="bp-archive-main">
            <div class="bp-archive-toolbar">
                <p>
                    نمایش <?php echo esc_html(number_format_i18n($visible_count)); ?>
                    از <?php echo esc_html(number_format_i18n($total_packs)); ?> پک
                </p>
                <button class="bp-filter-open" type="button" aria-expanded="false" aria-controls="bp-archive-filter-panel">
                    <svg class="bp-filter-open__icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M4 7h16M7 12h10m-7 5h4"/>
                    </svg>
                    <span>دسته‌بندی پک‌ها</span>
                </button>
            </div>

            <?php if(have_posts()): ?>
                <div class="bp-pack-archive-grid" aria-labelledby="bp-pack-archive-title">
                    <?php $index=0; ?>
                    <?php while(have_posts()):the_post(); ?>
                        <?php
                        $index++;
                        $pack_id=get_the_ID();
                        $pack_terms=wp_get_post_terms($pack_id,'bp_pack_category');
                        $pack_terms=$pack_terms&&!is_wp_error($pack_terms)?$pack_terms:[];
                        $pack_items=get_post_meta($pack_id,'_bp_pack_items',true);
                        $pack_items=is_array($pack_items)?$pack_items:[];
                        $description_text=get_the_excerpt($pack_id);
                        if(!$description_text)$description_text=wp_strip_all_tags(get_post_field('post_content',$pack_id));
                        ?>
                        <?php get_template_part('template-parts/components/cards/pack-card',null,[
                            'number'=>sprintf('%02d',$index),
                            'image_id'=>get_post_thumbnail_id($pack_id),
                            'badge'=>$pack_terms?$pack_terms[0]->name:'',
                            'title'=>get_the_title($pack_id),
                            'description'=>$description_text,
                            'meta'=>sprintf('%s محصول',number_format_i18n(count($pack_items))),
                            'button_text'=>'مشاهده پک',
                            'button_url'=>get_permalink($pack_id),
                            'pack_id'=>$pack_id,
                            'pack_items'=>$pack_items,
                            'color'=>get_post_meta($pack_id,'_bp_pack_color',true),
                        ]); ?>
                    <?php endwhile; ?>
                </div>

                <?php the_posts_pagination([
                    'mid_size'=>1,
                    'end_size'=>1,
                    'prev_text'=>'<span aria-hidden="true">→</span><span>قبلی</span>',
                    'next_text'=>'<span>بعدی</span><span aria-hidden="true">←</span>',
                    'screen_reader_text'=>'صفحه‌بندی پک‌ها',
                    'aria_label'=>'صفحه‌بندی پک‌ها',
                    'class'=>'bp-pack-archive__pagination',
                ]); ?>
            <?php else: ?>
                <p class="bp-pack-archive__empty">در این بخش پکی پیدا نشد.</p>
            <?php endif; ?>
        </div>

        <aside class="bp-archive-sidebar" id="bp-archive-filter-panel" aria-label="دسته‌بندی پک‌ها">
            <div class="bp-archive-filters">
                <div class="bp-archive-filters__head">
                    <h2>دسته‌بندی پک‌ها</h2>
                    <button class="bp-filter-close" type="button" aria-label="بستن دسته‌بندی‌ها">×</button>
                </div>

                <nav class="bp-pack-archive__categories-nav" aria-label="فیلتر دسته‌بندی پک‌ها">
                    <ul class="bp-pack-archive__categories">
                        <li>
                            <a class="<?php echo $active_category===0?'is-active':''; ?>" href="<?php echo esc_url($archive_url); ?>" <?php echo $active_category===0?'aria-current="page"':''; ?>>
                                <span>همه پک‌ها</span>
                                <span class="bp-pack-archive__count"><?php echo esc_html(number_format_i18n($total_packs)); ?></span>
                            </a>
                        </li>
                        <?php foreach($terms as $category): ?>
                            <?php
                            $depth=count(get_ancestors($category->term_id,'bp_pack_category'));
                            $is_active=$active_category===(int)$category->term_id;
                            $category_url=get_term_link($category);
                            if(is_wp_error($category_url))continue;
                            ?>
                            <li style="--term-depth:<?php echo esc_attr((string)$depth); ?>">
                                <a class="<?php echo $is_active?'is-active':''; ?>" href="<?php echo esc_url($category_url); ?>" <?php echo $is_active?'aria-current="page"':''; ?>>
                                    <span><?php echo esc_html($category->name); ?></span>
                                    <span class="bp-pack-archive__count"><?php echo esc_html(number_format_i18n($category->count)); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <a class="bp-pack-archive__shop-link" href="<?php echo esc_url($shop_url); ?>">
                    <span>مشاهده همه محصولات</span>
                    <span aria-hidden="true">←</span>
                </a>
            </div>
        </aside>
    </div>

    <?php if($has_term_content): ?>
        <section class="bp-pack-archive-content bp-archive-content--rich" aria-labelledby="bp-pack-archive-content-title">
            <h2 class="bp-pack-archive-content__title" id="bp-pack-archive-content-title">درباره این دسته</h2>
            <div class="bp-archive-content__body bp-post__content entry-content" data-archive-content>
                <?php echo wp_kses_post($term_content); ?>
            </div>
            <button class="bp-archive-content__toggle" type="button" data-archive-toggle aria-expanded="false">ادامه مطلب</button>
        </section>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
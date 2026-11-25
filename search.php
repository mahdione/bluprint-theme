<?php
defined('ABSPATH')||exit;

get_header();

$search_query=get_search_query();
$has_query=trim((string)$search_query)!=='';
$active_type=bluprint_search_type();
$types=bluprint_search_types();
$counts=bluprint_search_counts();
$total=$counts['all'];
$type_count=$active_type?($counts[$active_type]??0):$total;
$active_meta=$active_type&&isset($types[$active_type])?$types[$active_type]:null;
$category_count=0;
foreach($types as $key=>$meta){
    if(!empty($counts[$key]))$category_count++;
}

$shop_url=function_exists('wc_get_page_permalink')?wc_get_page_permalink('shop'):home_url('/shop/');
$packs_url=post_type_exists('bp_pack')?get_post_type_archive_link('bp_pack'):$shop_url;
$packs_url=$packs_url?:$shop_url;
$guides_url=home_url('/#bp-blog-title');
$faq_url=home_url('/#bp-faq-title');

$grouped=['product'=>[],'bp_pack'=>[],'post'=>[]];
if(have_posts()){
    foreach($GLOBALS['wp_query']->posts as $search_post){
        $post_type=get_post_type($search_post);
        if(isset($grouped[$post_type]))$grouped[$post_type][]=$search_post;
    }
}

$visible_types=$active_type?[$active_type]:array_keys($types);
$visible_types=array_values(array_filter($visible_types,static fn($type)=>!empty($grouped[$type])&&!empty($counts[$type])));
$has_results=$has_query&&$total>0&&!empty($visible_types);
$active_total=$active_type?($counts[$active_type]??0):$total;
$out_of_range=$has_query&&$active_total>0&&!have_posts()&&max(1,(int)get_query_var('paged'))>1;
$group_index=0;

$arrow='<svg viewBox="0 0 24 24" width="16" height="16" fill="none" aria-hidden="true"><path d="M19 12H5m0 0 7-7m-7 7 7 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$search_icon='<svg viewBox="0 0 24 24" width="22" height="22" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
$clear_icon='<svg viewBox="0 0 24 24" width="16" height="16" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
?>
<main id="main-content" class="bp-search-page" data-search-page>
    <section class="bp-search-hero" aria-labelledby="bp-search-title">
        <div class="bp-search-hero__grid" aria-hidden="true"></div>
        <div class="bp-search-page__container">
            <nav class="bp-search-breadcrumb" aria-label="مسیر صفحه">
                <a href="<?php echo esc_url(home_url('/')); ?>">خانه</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">جست‌وجو</span>
            </nav>

            <div class="bp-search-hero__top">
                <div class="bp-search-hero__intro">
                    <p class="bp-search-hero__eyebrow"><span class="bp-search-hero__index">S—01</span> / SEARCH INDEX</p>
                    <h1 class="bp-search-hero__title" id="bp-search-title">نتایج جست‌وجو</h1>
                    <p class="bp-search-hero__query">
                        <?php if($has_query): ?>
                            برای <strong>«<?php echo esc_html($search_query); ?>»</strong>
                            <?php if($active_meta): ?>
                                <em>در <?php echo esc_html($active_meta['label']); ?></em>
                            <?php endif; ?>
                        <?php else: ?>
                            <em>عبارت موردنظرت را بنویس تا میان محصولات، پک‌ها و مقالات بگردیم.</em>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="bp-search-hero__summary">
                    <span
                        class="bp-search-hero__count"
                        data-search-count="<?php echo esc_attr($type_count); ?>"
                        data-search-count-label="<?php echo esc_attr(number_format_i18n($type_count)); ?>"
                    ><?php echo esc_html(number_format_i18n($type_count)); ?></span>
                    <span class="bp-search-hero__count-label">
                        <?php if(!$has_query): ?>
                            آماده جست‌وجو
                        <?php elseif($type_count===0): ?>
                            نتیجه‌ای پیدا نشد
                        <?php elseif($active_meta): ?>
                            <?php echo esc_html($active_meta['singular']); ?> در این جست‌وجو
                        <?php else: ?>
                            نتیجه در <?php echo esc_html(number_format_i18n($category_count)); ?> دسته
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="bp-search-hero__form">
                <form
                    class="bp-page-search"
                    role="search"
                    method="get"
                    action="<?php echo esc_url(home_url('/')); ?>"
                    data-page-search
                >
                    <label class="sr-only" for="bp-search-input">جست‌وجو در فروشگاه</label>
                    <span class="bp-page-search__icon" aria-hidden="true"><?php echo $search_icon; ?></span>
                    <input
                        class="bp-page-search__input"
                        id="bp-search-input"
                        type="search"
                        name="s"
                        value="<?php echo esc_attr($search_query); ?>"
                        placeholder="محصول، پک یا مقاله…"
                        autocomplete="off"
                    >
                    <button
                        class="bp-page-search__clear"
                        type="button"
                        aria-label="پاک کردن جست‌وجو"
                        data-page-search-clear
                        <?php echo $has_query?'':'hidden'; ?>
                    ><?php echo $clear_icon; ?></button>
                    <button class="bp-page-search__submit" type="submit">
                        <span>جست‌وجو</span>
                        <?php echo $arrow; ?>
                    </button>
                </form>
                <p class="bp-search-hero__hint">جست‌وجو در محصولات، پک‌های آماده و مقالات بلوپرینت</p>
            </div>
        </div>
    </section>

    <?php if(!$has_query): ?>
        <section class="bp-search-results" aria-label="شروع جست‌وجو">
            <div class="bp-search-page__container">
                <div class="bp-search-empty">
                    <div class="bp-search-empty__panel">
                        <div class="bp-search-empty__intro">
                            <p class="bp-search-empty__eyebrow">START HERE / TYPE A QUERY</p>
                            <h2 class="bp-search-empty__title">دنبال چه چیزی می‌گردی؟</h2>
                            <p class="bp-search-empty__text">نام یک محصول، یک پک آماده یا موضوعی که می‌خواهی درباره‌اش بخوانی را بنویس.</p>
                            <div class="bp-search-empty__links">
                                <a class="bp-search-empty__link" href="<?php echo esc_url($shop_url); ?>">همه محصولات <?php echo $arrow; ?></a>
                                <a class="bp-search-empty__link" href="<?php echo esc_url($packs_url); ?>">پک‌های آماده <?php echo $arrow; ?></a>
                                <a class="bp-search-empty__link" href="<?php echo esc_url($guides_url); ?>">راهنمای انتخاب <?php echo $arrow; ?></a>
                            </div>
                        </div>
                        <div class="bp-search-empty__visual" aria-hidden="true">
                            <span class="bp-search-empty__code">?</span>
                            <span class="bp-search-empty__crosshair"></span>
                        </div>
                    </div>

                    <?php $suggested=bluprint_search_suggested_packs(3); ?>
                    <?php if($suggested): ?>
                        <div class="bp-search-empty__suggestions">
                            <header class="bp-search-empty__suggestions-head">
                                <h2 class="bp-search-empty__suggestions-title">پک‌های پیشنهادی</h2>
                                <a class="bp-search-group__all" href="<?php echo esc_url($packs_url); ?>">همه پک‌ها <?php echo $arrow; ?></a>
                            </header>
                            <div class="bp-search-grid bp-search-grid--packs">
                                <?php foreach($suggested as $index=>$pack): ?>
                                    <div class="bp-search-item" style="--i:<?php echo esc_attr(min($index,8)); ?>">
                                        <?php get_template_part('template-parts/components/cards/pack-card',null,array_merge($pack,['number'=>sprintf('%02d',$index+1)])); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php elseif(!$has_results): ?>
        <section class="bp-search-results" aria-label="نتیجه‌ای پیدا نشد">
            <div class="bp-search-page__container">
                <div class="bp-search-empty">
                    <div class="bp-search-empty__panel">
                        <div class="bp-search-empty__intro">
                            <p class="bp-search-empty__eyebrow"><?php echo $out_of_range?'END OF RESULTS / OUT OF RANGE':'NO MATCH / 0 RESULTS'; ?></p>
                            <h2 class="bp-search-empty__title"><?php echo $out_of_range?'این صفحه خالی است':'چیزی پیدا نشد'; ?></h2>
                            <p class="bp-search-empty__text">
                                <?php if($out_of_range): ?>
                                    نتایج بیشتری در صفحات قبل وجود دارد. از اولین صفحه ادامه بده.
                                <?php else: ?>
                                    برای <strong>«<?php echo esc_html($search_query); ?>»</strong> نتیجه‌ای نداریم. املای عبارت را بررسی کن یا با کلمه کوتاه‌تری امتحان کن.
                                <?php endif; ?>
                            </p>
                            <div class="bp-search-empty__links">
                                <?php if($out_of_range||$active_type!==''): ?>
                                    <a class="bp-search-empty__link" href="<?php echo esc_url(bluprint_search_url($out_of_range?$active_type:'')); ?>"><?php echo $out_of_range?'اولین صفحه نتایج':'جست‌وجوی همه نتایج'; ?> <?php echo $arrow; ?></a>
                                <?php endif; ?>
                                <a class="bp-search-empty__link" href="<?php echo esc_url($shop_url); ?>">همه محصولات <?php echo $arrow; ?></a>
                                <a class="bp-search-empty__link" href="<?php echo esc_url($packs_url); ?>">پک‌های آماده <?php echo $arrow; ?></a>
                                <a class="bp-search-empty__link" href="<?php echo esc_url($guides_url); ?>">راهنمای انتخاب <?php echo $arrow; ?></a>
                            </div>
                        </div>
                        <div class="bp-search-empty__visual" aria-hidden="true">
                            <span class="bp-search-empty__code">0</span>
                            <span class="bp-search-empty__crosshair"></span>
                        </div>
                    </div>

                    <?php $suggested=bluprint_search_suggested_packs(3); ?>
                    <?php if($suggested): ?>
                        <div class="bp-search-empty__suggestions">
                            <header class="bp-search-empty__suggestions-head">
                                <h2 class="bp-search-empty__suggestions-title">پک‌های پیشنهادی</h2>
                                <a class="bp-search-group__all" href="<?php echo esc_url($packs_url); ?>">همه پک‌ها <?php echo $arrow; ?></a>
                            </header>
                            <div class="bp-search-grid bp-search-grid--packs">
                                <?php foreach($suggested as $index=>$pack): ?>
                                    <div class="bp-search-item" style="--i:<?php echo esc_attr(min($index,8)); ?>">
                                        <?php get_template_part('template-parts/components/cards/pack-card',null,array_merge($pack,['number'=>sprintf('%02d',$index+1)])); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    <?php else: ?>
        <section class="bp-search-results" aria-label="نتایج جست‌وجو">
            <div class="bp-search-page__container">
                <nav class="bp-search-tabs" aria-label="فیلتر نوع نتایج">
                    <a
                        class="bp-search-tab<?php echo $active_type===''?' is-active':''; ?>"
                        href="<?php echo esc_url(bluprint_search_url('')); ?>"
                        <?php echo $active_type===''?'aria-current="page"':''; ?>
                    >
                        <span>همه نتایج</span>
                        <span class="bp-search-tab__count"><?php echo esc_html(number_format_i18n($counts['all'])); ?></span>
                    </a>
                    <?php foreach($types as $key=>$meta): ?>
                        <?php
                        $is_active=$active_type===$key;
                        $is_empty=empty($counts[$key])&&!$is_active;
                        ?>
                        <?php if($is_empty): ?>
                            <span class="bp-search-tab is-empty" aria-disabled="true">
                                <span><?php echo esc_html($meta['label']); ?></span>
                                <span class="bp-search-tab__count"><?php echo esc_html(number_format_i18n(0)); ?></span>
                            </span>
                        <?php else: ?>
                            <a
                                class="bp-search-tab<?php echo $is_active?' is-active':''; ?>"
                                href="<?php echo esc_url(bluprint_search_url($key)); ?>"
                                <?php echo $is_active?'aria-current="page"':''; ?>
                            >
                                <span><?php echo esc_html($meta['label']); ?></span>
                                <span class="bp-search-tab__count"><?php echo esc_html(number_format_i18n($counts[$key])); ?></span>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </nav>

                <div class="bp-search-groups">
                    <?php foreach($visible_types as $type_key): ?>
                        <?php
                        $group_index++;
                        $group_posts=$grouped[$type_key];
                        $type_meta=$types[$type_key];
                        $grid_modifier=$type_key==='bp_pack'?'packs':($type_key==='product'?'products':'articles');
                        ?>
                        <section
                            class="bp-search-group"
                            id="bp-search-<?php echo esc_attr($type_key); ?>"
                            aria-labelledby="bp-search-<?php echo esc_attr($type_key); ?>-title"
                        >
                            <header class="bp-search-group__head">
                                <div class="bp-search-group__heading">
                                    <p class="bp-search-group__eyebrow"><?php echo esc_html(sprintf('%02d / %s',$group_index,$type_meta['eyebrow'])); ?></p>
                                    <h2 class="bp-search-group__title" id="bp-search-<?php echo esc_attr($type_key); ?>-title">
                                        <?php echo esc_html($type_meta['label']); ?>
                                        <span><?php echo esc_html(number_format_i18n($counts[$type_key])); ?></span>
                                    </h2>
                                </div>
                                <?php if(!$active_type): ?>
                                    <a class="bp-search-group__all" href="<?php echo esc_url(bluprint_search_url($type_key)); ?>">
                                        همه <?php echo esc_html($type_meta['label']); ?>
                                        <?php echo $arrow; ?>
                                    </a>
                                <?php endif; ?>
                            </header>

                            <div class="bp-search-grid bp-search-grid--<?php echo esc_attr($grid_modifier); ?>">
                                <?php foreach($group_posts as $index=>$search_post): ?>
                                    <div class="bp-search-item" style="--i:<?php echo esc_attr(min($index,8)); ?>">
                                        <?php
                                        if($type_key==='product'){
                                            if(function_exists('wc_get_product')){
                                                $product=wc_get_product($search_post->ID);
                                                if($product instanceof WC_Product){
                                                    get_template_part('template-parts/components/cards/product-card',null,['product'=>$product]);
                                                }
                                            }
                                        }elseif($type_key==='bp_pack'){
                                            $pack_args=bluprint_search_pack_args($search_post);
                                            if($pack_args){
                                                get_template_part('template-parts/components/cards/pack-card',null,array_merge($pack_args,['number'=>sprintf('%02d',$index+1)]));
                                            }
                                        }else{
                                            get_template_part('template-parts/components/cards/article-card',null,['post_id'=>$search_post->ID]);
                                        }
                                        ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>

                <?php if($GLOBALS['wp_query']->max_num_pages>1): ?>
                    <div class="bp-search-pagination">
                        <?php
                        the_posts_pagination([
                            'mid_size'=>1,
                            'prev_text'=>'<span aria-hidden="true">→</span><span class="sr-only">صفحه قبل</span>',
                            'next_text'=>'<span aria-hidden="true">←</span><span class="sr-only">صفحه بعد</span>',
                            'screen_reader_text'=>'صفحه‌بندی نتایج جست‌وجو',
                        ]);
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php get_footer(); ?>
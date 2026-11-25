<?php
defined('ABSPATH')||exit;

get_header();

$shop_url=function_exists('wc_get_page_permalink')?wc_get_page_permalink('shop'):home_url('/shop/');
$packs_url=post_type_exists('bp_pack')?get_post_type_archive_link('bp_pack'):$shop_url;
$packs_url=$packs_url?:$shop_url;
$guides_url=home_url('/#bp-blog-title');
$faq_url=home_url('/#bp-faq-title');

$arrow='<svg viewBox="0 0 24 24" width="16" height="16" fill="none" aria-hidden="true"><path d="M19 12H5m0 0 7-7m-7 7 7 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>';
$search_icon='<svg viewBox="0 0 24 24" width="22" height="22" fill="none" aria-hidden="true"><circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/><path d="m20 20-3.2-3.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>';
$clear_icon='<svg viewBox="0 0 24 24" width="16" height="16" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
?>
<main id="main-content" class="bp-error-page" data-error-page>
    <section class="bp-error" aria-labelledby="bp-error-title">
        <div class="bp-error__grid" aria-hidden="true"></div>
        <div class="bp-error__container">
            <nav class="bp-error__breadcrumb" aria-label="مسیر صفحه">
                <a href="<?php echo esc_url(home_url('/')); ?>">خانه</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">صفحه پیدا نشد</span>
            </nav>

            <div class="bp-error__layout">
                <div class="bp-error__content">
                    <p class="bp-error__eyebrow">E—404 / OFF THE BLUEPRINT</p>
                    <h1 class="bp-error__title" id="bp-error-title">این صفحه روی نقشه نیست</h1>
                    <p class="bp-error__description">
                        نشانی‌ای که دنبالش بودی حذف شده، جابه‌جا شده یا هیچ‌وقت روی نقشه نبوده.
                        نگران نباش؛ از همین‌جا می‌توانی مسیرت را دوباره پیدا کنی.
                    </p>

                    <div class="bp-error__actions">
                        <?php
                        get_template_part('template-parts/components/buttons/button',null,[
                            'label'=>'بازگشت به خانه',
                            'url'=>home_url('/'),
                            'variant'=>'primary',
                            'icon'=>'arrow-left',
                            'show_icon'=>true,
                        ]);
                        get_template_part('template-parts/components/buttons/button',null,[
                            'label'=>'مشاهده محصولات',
                            'url'=>$shop_url,
                            'variant'=>'outline',
                            'icon'=>'arrow-left',
                            'show_icon'=>true,
                        ]);
                        ?>
                    </div>

                    <div class="bp-error__search">
                        <form
                            class="bp-page-search"
                            role="search"
                            method="get"
                            action="<?php echo esc_url(home_url('/')); ?>"
                            data-page-search
                        >
                            <label class="sr-only" for="bp-error-search-input">جست‌وجو در سایت</label>
                            <span class="bp-page-search__icon" aria-hidden="true"><?php echo $search_icon; ?></span>
                            <input
                                class="bp-page-search__input"
                                id="bp-error-search-input"
                                type="search"
                                name="s"
                                value=""
                                placeholder="یا چیزی را جست‌وجو کن…"
                                autocomplete="off"
                            >
                            <button
                                class="bp-page-search__clear"
                                type="button"
                                aria-label="پاک کردن جست‌وجو"
                                data-page-search-clear
                                hidden
                            ><?php echo $clear_icon; ?></button>
                            <button class="bp-page-search__submit" type="submit">
                                <span>جست‌وجو</span>
                                <?php echo $arrow; ?>
                            </button>
                        </form>
                    </div>

                    <div class="bp-error__links">
                        <a class="bp-error__link" href="<?php echo esc_url($shop_url); ?>">
                            <span>همه محصولات</span>
                            <?php echo $arrow; ?>
                        </a>
                        <a class="bp-error__link" href="<?php echo esc_url($packs_url); ?>">
                            <span>پک‌های آماده</span>
                            <?php echo $arrow; ?>
                        </a>
                        <a class="bp-error__link" href="<?php echo esc_url($guides_url); ?>">
                            <span>راهنمای انتخاب</span>
                            <?php echo $arrow; ?>
                        </a>
                        <a class="bp-error__link" href="<?php echo esc_url($faq_url); ?>">
                            <span>سؤالات متداول</span>
                            <?php echo $arrow; ?>
                        </a>
                    </div>
                </div>

                <div class="bp-error__visual" data-error-visual aria-hidden="true">
                    <span class="bp-error__corner bp-error__corner--tl"></span>
                    <span class="bp-error__corner bp-error__corner--tr"></span>
                    <span class="bp-error__corner bp-error__corner--bl"></span>
                    <span class="bp-error__corner bp-error__corner--br"></span>

                    <span class="bp-error__coord bp-error__coord--top" data-error-coord>X: 0.00 / Y: 0.00</span>
                    <span class="bp-error__coord bp-error__coord--bottom">SIGNAL LOST</span>

                    <span class="bp-error__scan"></span>

                    <span class="bp-error__dim bp-error__dim--h"><span>404 / OFF GRID</span></span>
                    <span class="bp-error__dim bp-error__dim--v"><span>BLUEPRINT SHEET 04</span></span>

                    <div class="bp-error__code">
                        <span class="bp-error__digit">4</span>
                        <span class="bp-error__zero">
                            <span class="bp-error__zero-ring"></span>
                            <span class="bp-error__zero-cross"></span>
                            <span class="bp-error__zero-dot"></span>
                        </span>
                        <span class="bp-error__digit">4</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php get_footer(); ?>
<?php
defined('ABSPATH')||exit;

if(!function_exists('bluprint_english_digits')){
    $bluprint_inc=get_theme_file_path('inc/english-numbers.php');
    if(file_exists($bluprint_inc))require_once $bluprint_inc;
}
if(!function_exists('bluprint_english_digits')){
    function bluprint_english_digits($value){ return $value; }
}

get_header();

while(have_posts()):
    the_post();

    $pack_id=get_the_ID();
    $color=sanitize_hex_color(get_post_meta($pack_id,'_bp_pack_color',true))?:'#F4A340';
    $products=bp_commerce_get_pack_products($pack_id);
    $terms=get_the_terms($pack_id,'bp_pack_category');
    $terms=$terms&&!is_wp_error($terms)?$terms:[];
    $excerpt=trim((string)get_the_excerpt($pack_id));
    $content=trim((string)get_the_content());
    $image_id=get_post_thumbnail_id($pack_id);
    $total=0.0;
    $units=0;
    $product_count=count($products);
    $available_products=0;

    foreach($products as $item){
        $product=$item['product'];
        $purchasable=$product->is_purchasable()&&$product->is_in_stock();
        $unit_price=(float)wc_get_price_to_display($product);
        $qty=$purchasable?$item['default_qty']:0;

        if($purchasable)$available_products++;
        $total+=$unit_price*$qty;
        $units+=$qty;
    }

    $banner=function_exists('bluprint_get_banner_data')?bluprint_get_banner_data(1):['enabled'=>0];
    $show_banner=!empty($banner['enabled'])&&(
        trim($banner['title'])!==''||
        trim($banner['eyebrow'])!==''||
        trim($banner['description'])!==''||
        trim($banner['button_text'])!==''
    );
    ?>
    <main id="main-content" class="bp-pack-single" style="--bp-pack-color:<?php echo esc_attr($color); ?>" dir="rtl">
        <div class="bp-pack-single__container">
            <?php get_template_part('template-parts/components/breadcrumbs/breadcrumbs',null,[
                'variant'=>'compact',
                'label'=>'مسیر پک',
                'class'=>'bp-pack-single__breadcrumb',
            ]); ?>

            <article <?php post_class('bp-pack'); ?>>
                <header class="bp-pack-hero">
                    <figure class="bp-pack-hero__media<?php echo $image_id?'':' bp-pack-hero__media--empty'; ?>">
                        <?php if($image_id): ?>
                            <?php echo wp_get_attachment_image($image_id,'large',false,[
                                'class'=>'bp-pack-hero__image',
                                'alt'=>get_post_meta($image_id,'_wp_attachment_image_alt',true)?:get_the_title($pack_id),
                                'loading'=>'eager',
                                'fetchpriority'=>'high',
                                'decoding'=>'async',
                            ]); ?>
                        <?php else: ?>
                            <span class="bp-pack-hero__placeholder" aria-hidden="true">PACK<br><?php echo esc_html(sprintf('%02d',$product_count)); ?> ITEMS</span>
                        <?php endif; ?>
                        <figcaption class="bp-pack-hero__badge">
                            <?php echo esc_html(sprintf('%s محصول',$product_count)); ?>
                        </figcaption>
                    </figure>

                    <div class="bp-pack-hero__summary">
                        <?php if($terms): ?>
                            <nav class="bp-pack-hero__categories" aria-label="دسته‌بندی پک">
                                <?php foreach($terms as $term): ?>
                                    <a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a>
                                <?php endforeach; ?>
                            </nav>
                        <?php endif; ?>

                        <h1 class="bp-pack-hero__title"><?php echo esc_html(get_the_title($pack_id)); ?></h1>

                        <?php if($excerpt!==''): ?>
                            <p class="bp-pack-hero__excerpt"><?php echo esc_html(wp_trim_words($excerpt,45,'…')); ?></p>
                        <?php endif; ?>

                        <div class="bp-pack-hero__meta">
                            <span class="bp-pack-hero__meta-item">
                                <span class="bp-pack-hero__meta-label">محصولات پک</span>
                                <strong><?php echo esc_html(bluprint_english_digits(number_format_i18n($product_count))); ?> محصول</strong>
                            </span>
                            <span class="bp-pack-hero__meta-item">
                                <span class="bp-pack-hero__meta-label">تعداد انتخاب‌شده</span>
                                <strong><span data-pack-selected><?php echo esc_html(bluprint_english_digits(number_format_i18n($units))); ?></span> واحد</strong>
                            </span>
                            <?php if($terms): ?>
                                <span class="bp-pack-hero__meta-item">
                                    <span class="bp-pack-hero__meta-label">دسته‌بندی</span>
                                    <strong><?php echo esc_html($terms[0]->name); ?></strong>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="bp-pack-hero__buy">
                            <div class="bp-pack-hero__price">
                                <span class="bp-pack-hero__price-label">جمع این پک</span>
                                <strong class="bp-pack-hero__total" data-pack-total><?php echo wp_kses_post(bluprint_english_digits(wc_price($total))); ?></strong>
                                <span class="bp-pack-hero__price-note">قیمت با تغییر تعداد محصولات به‌روزرسانی می‌شود.</span>
                            </div>

                            <button class="bp-button bp-button--primary bp-pack-hero__button" type="submit" form="bp-pack-form" data-pack-submit <?php disabled($available_products<1); ?>>
                                <img class="bp-button__icon" src="<?php echo esc_url(get_theme_file_uri('assets/icons/arrow-left.svg')); ?>" alt="" aria-hidden="true" width="16" height="16" decoding="async">
                                <span class="bp-button__label">افزودن پک به سبد خرید</span>
                            </button>
                        </div>

                        <p class="bp-pack-hero__message" data-pack-message></p>
                    </div>
                </header>

                <section class="bp-pack-products" id="bp-pack-products" aria-labelledby="bp-pack-products-title">
                    <header class="bp-pack-products__header">
                        <p class="bp-pack-section-eyebrow">محتوای پک</p>
                        <h2 class="bp-pack-products__title" id="bp-pack-products-title">محصولات این پک</h2>
                        <p class="bp-pack-products__description">تعداد هر محصول را مشخص کن؛ محصولات بدون نیاز را می‌توانی حذف کنی.</p>
                    </header>

                    <?php if($products): ?>
                        <form id="bp-pack-form" class="bp-pack-form" data-pack-form method="post" action="<?php echo esc_url(WC_AJAX::get_endpoint('bp_add_pack_to_cart')); ?>">
                            <input type="hidden" name="bp_pack_id" value="<?php echo esc_attr($pack_id); ?>">
                            <input type="hidden" name="bp_pack_nonce" value="<?php echo esc_attr(wp_create_nonce('bp_add_pack_to_cart')); ?>">

                            <div class="bp-pack-items">
                                <?php foreach($products as $index=>$item): ?>
                                    <?php
                                    $product=$item['product'];
                                    $product_id=$product->get_id();
                                    $purchasable=$product->is_purchasable()&&$product->is_in_stock();
                                    $qty=$purchasable?$item['default_qty']:0;
                                    $unit_price=(float)wc_get_price_to_display($product);
                                    $description=trim(wp_strip_all_tags($product->get_short_description()));
                                    $description=$description!==''?wp_trim_words($description,18,'…'):'';
                                    ?>
                                    <article class="bp-pack-item<?php echo $qty<1?' is-removed':''; ?><?php echo $purchasable?'':' is-unavailable'; ?>" data-pack-row data-product-id="<?php echo esc_attr($product_id); ?>" data-unit-price="<?php echo esc_attr((string)$unit_price); ?>" data-last-qty="<?php echo esc_attr((string)max(1,$qty)); ?>">
                                        <span class="bp-pack-item__index" aria-hidden="true"><?php echo esc_html(sprintf('%02d',$index+1)); ?></span>

                                        <a class="bp-pack-item__media" href="<?php echo esc_url($product->get_permalink()); ?>" tabindex="-1" aria-hidden="true">
                                            <?php echo wp_kses_post($product->get_image('woocommerce_thumbnail',['loading'=>'lazy','decoding'=>'async'])); ?>
                                        </a>

                                        <div class="bp-pack-item__info">
                                            <h3 class="bp-pack-item__title">
                                                <a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a>
                                            </h3>
                                            <?php if($description!==''): ?>
                                                <p class="bp-pack-item__description"><?php echo esc_html($description); ?></p>
                                            <?php endif; ?>
                                            <div class="bp-pack-item__meta">
                                                <span class="bp-pack-item__unit">
                                                    <span class="bp-pack-item__label">قیمت واحد</span>
                                                    <strong data-unit-price-display><?php echo wp_kses_post(bluprint_english_digits(wc_price($unit_price))); ?></strong>
                                                </span>
                                                <span class="bp-pack-item__stock<?php echo $purchasable?'':' is-out'; ?>">
                                                    <?php echo $purchasable?'موجود':'ناموجود'; ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="bp-pack-item__purchase">
                                            <div class="bp-pack-item__subtotal">
                                                <span class="bp-pack-item__label">جمع کل این محصول</span>
                                                <strong data-line-total><?php echo wp_kses_post(bluprint_english_digits(wc_price($unit_price*$qty))); ?></strong>
                                            </div>

                                            <div class="bp-pack-qty" role="group" aria-label="<?php echo esc_attr('تعداد '.$product->get_name()); ?>">
                                                <button type="button" data-qty-action="minus" aria-label="کاهش تعداد" <?php disabled(!$purchasable||$qty<=0); ?>>−</button>
                                                <input type="number" dir="ltr" lang="en" name="bp_pack_quantities[<?php echo esc_attr($product_id); ?>]" value="<?php echo esc_attr((string)(int)$qty); ?>" min="0" max="99" step="1" inputmode="numeric" aria-label="<?php echo esc_attr('تعداد '.$product->get_name()); ?>" <?php disabled(!$purchasable); ?>>
                                                <button type="button" data-qty-action="plus" aria-label="افزایش تعداد" <?php disabled(!$purchasable||$qty>=99); ?>>+</button>
                                            </div>

                                            <button class="bp-pack-item__remove" type="button" data-pack-remove aria-label="<?php echo esc_attr(($qty>0?'حذف ':'افزودن ').$product->get_name()); ?>" <?php disabled(!$purchasable); ?>>
                                                <?php echo $qty>0?'حذف':'افزودن'; ?>
                                            </button>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>

                            <div class="bp-pack-checkout">
                                <div class="bp-pack-checkout__summary">
                                    <span class="bp-pack-checkout__label">جمع نهایی پک</span>
                                    <strong class="bp-pack-checkout__total" data-pack-total aria-live="polite"><?php echo wp_kses_post(bluprint_english_digits(wc_price($total))); ?></strong>
                                    <span class="bp-pack-checkout__units"><b data-pack-selected><?php echo esc_html(bluprint_english_digits(number_format_i18n($units))); ?></b> واحد انتخاب شده</span>
                                </div>

                                <button class="bp-button bp-button--primary bp-pack-checkout__button" type="submit" data-pack-submit <?php disabled($available_products<1); ?>>
                                    <img class="bp-button__icon" src="<?php echo esc_url(get_theme_file_uri('assets/icons/arrow-left.svg')); ?>" alt="" aria-hidden="true" width="16" height="16" decoding="async">
                                    <span class="bp-button__label">افزودن پک به سبد خرید</span>
                                </button>
                            </div>

                            <p class="bp-pack-form__message" data-pack-message role="status" aria-live="polite"></p>
                        </form>
                    <?php else: ?>
                        <p class="bp-pack-products__empty">محصولی برای این پک ثبت نشده است.</p>
                    <?php endif; ?>
                </section>

                <?php if($content!==''): ?>
                    <section class="bp-pack-about" aria-labelledby="bp-pack-about-title">
                        <p class="bp-pack-section-eyebrow">درباره پک</p>
                        <h2 class="bp-pack-about__title" id="bp-pack-about-title">این پک چه چیزی در اختیار تو می‌گذارد؟</h2>
                        <div class="bp-pack-content bp-post__content entry-content">
                            <?php the_content(); ?>
                        </div>
                    </section>
                <?php endif; ?>
            </article>

            <?php if($show_banner): ?>
                <div class="bp-pack-single__cta">
                    <?php get_template_part('template-parts/components/banners/banner',null,['banner_id'=>1]); ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
    <?php
endwhile;

get_footer();
<?php
defined('ABSPATH')||exit;

$shop_url=function_exists('wc_get_page_permalink')?wc_get_page_permalink('shop'):home_url('/shop/');

$blog_data=function_exists('bluprint_get_blog_data')?bluprint_get_blog_data():[];
$has_guides=!empty($blog_data['enabled'])&&!empty($blog_data['title'])&&function_exists('bluprint_get_blog_posts')&&bluprint_get_blog_posts(1);
$cta_url=$has_guides?'#bp-blog-title':$shop_url;
$cta_label=$has_guides?'راهنمای انتخاب ابزار':'شروع انتخاب';

$items=[
    [
        'number'=>'01',
        'icon'=>'curated',
        'title'=>'انتخاب تخصصی',
        'text'=>'هر ابزار را از فیلتر کیفیت، کاربرد و دوام عبور می‌دهیم.',
    ],
    [
        'number'=>'02',
        'icon'=>'bundle',
        'title'=>'پک‌های هماهنگ',
        'text'=>'ابزارهای یک مسیر، یک‌جا و بدون خرید تکراری یا جاافتاده.',
    ],
    [
        'number'=>'03',
        'icon'=>'shipping',
        'title'=>'ارسال محافظت‌شده',
        'text'=>'بسته‌بندی ایمن و پیگیری سفارش تا لحظه تحویل.',
    ],
    [
        'number'=>'04',
        'icon'=>'support',
        'title'=>'همراهی تخصصی',
        'text'=>'قبل و بعد از خرید، برای انتخاب درست راهنمایی می‌گیری.',
    ],
];
?>
<section class="bp-assurance" aria-labelledby="bp-assurance-title">
    <div class="bp-assurance__container">
        <div class="bp-assurance__header">
            <div class="bp-assurance__intro">
                <h2 class="bp-assurance__title" id="bp-assurance-title">خرید مطمئن، شروع راحت</h2>
                <p class="bp-assurance__description">ابزار درست، نیمی از راه است؛ ما انتخاب را ساده می‌کنیم تا با خیال راحت شروع کنی.</p>
            </div>
            <a class="bp-assurance__link" href="<?php echo esc_url($cta_url); ?>">
                <span><?php echo esc_html($cta_label); ?></span>
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true">
                    <path d="M19 12H5m0 0 7-7m-7 7 7 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>

        <ul class="bp-assurance__grid">
            <?php foreach($items as $item): ?>
                <li class="bp-assurance__item">
                    <span class="bp-assurance__number" aria-hidden="true"><?php echo esc_html($item['number']); ?></span>
                    <span class="bp-assurance__icon" aria-hidden="true">
                        <?php if($item['icon']==='curated'): ?>
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true">
                                <path d="M12 3 4.5 6v5.3c0 4.3 3 7.6 7.5 9.7 4.5-2.1 7.5-5.4 7.5-9.7V6L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                <path d="m8.6 12.1 2.2 2.2 4.6-4.6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        <?php elseif($item['icon']==='bundle'): ?>
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true">
                                <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                <path d="m4 7.5 8 4.5 8-4.5M12 12v9" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                            </svg>
                        <?php elseif($item['icon']==='shipping'): ?>
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true">
                                <path d="M3 6.5h10.5V16H3V6.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                <path d="M13.5 10h3.7l3.3 3.2V16h-7v-6Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                <circle cx="7" cy="18" r="1.6" stroke="currentColor" stroke-width="1.6"/>
                                <circle cx="17.2" cy="18" r="1.6" stroke="currentColor" stroke-width="1.6"/>
                            </svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" aria-hidden="true">
                                <path d="M20.5 11.8c0 3.6-3.8 6.5-8.5 6.5-1.1 0-2.2-.2-3.2-.5L4 20l1.4-3.6C4.2 15.1 3.5 13.5 3.5 11.8c0-3.6 3.8-6.5 8.5-6.5s8.5 2.9 8.5 6.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                                <path d="M8.5 11.8h.01M12 11.8h.01M15.5 11.8h.01" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
                            </svg>
                        <?php endif; ?>
                    </span>
                    <div class="bp-assurance__body">
                        <h3 class="bp-assurance__item-title"><?php echo esc_html($item['title']); ?></h3>
                        <p class="bp-assurance__item-text"><?php echo esc_html($item['text']); ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
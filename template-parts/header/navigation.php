<?php
defined('ABSPATH')||exit;

$cart_url=function_exists('wc_get_cart_url')?wc_get_cart_url():home_url('/');
$cart=function_exists('WC')&&WC()->cart?WC()->cart:null;
$count=$cart?$cart->get_cart_contents_count():0;
?>

<div class="header-inner">

    <button
        class="mobile-menu-toggle"
        type="button"
        aria-expanded="false"
        aria-controls="primary-navigation"
        aria-label="<?php esc_attr_e('باز کردن منو','bluprint-theme'); ?>"
    >
        <span></span>
        <span></span>
    </button>

    <a
        class="site-brand"
        href="<?php echo esc_url(home_url('/')); ?>"
        aria-label="<?php esc_attr_e('بلوپرینت، صفحه اصلی','bluprint-theme'); ?>"
    >
        <span>بلوپرینت</span><sup>+</sup>
    </a>

    <nav
        class="site-navigation"
        id="primary-navigation"
        aria-label="<?php esc_attr_e('منوی اصلی','bluprint-theme'); ?>"
    >

        <form
            class="header-search header-search--mobile"
            role="search"
            method="get"
            action="<?php echo esc_url(home_url('/')); ?>"
        >
            <label class="sr-only" for="mobile-header-search-input">
                <?php esc_html_e('جست‌وجوی محصولات','bluprint-theme'); ?>
            </label>

            <svg
                class="header-search__icon"
                xmlns="http://www.w3.org/2000/svg"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
                focusable="false"
            >
                <circle cx="11" cy="11" r="7"></circle>
                <path d="m20 20-3.2-3.2"></path>
            </svg>

            <input
                id="mobile-header-search-input"
                type="search"
                name="s"
                value="<?php echo esc_attr(get_search_query()); ?>"
                placeholder="<?php esc_attr_e('جست‌وجوی محصولات…','bluprint-theme'); ?>"
            >
        </form>

        <?php
        wp_nav_menu([
            'theme_location'=>'primary',
            'container'=>false,
            'menu_class'=>'primary-menu',
            'fallback_cb'=>false
        ]);
        ?>

    </nav>

    <div class="header-actions">

        <form
            class="header-search header-search--desktop"
            role="search"
            method="get"
            action="<?php echo esc_url(home_url('/')); ?>"
        >
            <label class="sr-only" for="header-search-input">
                <?php esc_html_e('جست‌وجوی محصولات','bluprint-theme'); ?>
            </label>

            <svg
                class="header-search__icon"
                xmlns="http://www.w3.org/2000/svg"
                width="18"
                height="18"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
                aria-hidden="true"
                focusable="false"
            >
                <circle cx="11" cy="11" r="7"></circle>
                <path d="m20 20-3.2-3.2"></path>
            </svg>

            <input
                id="header-search-input"
                type="search"
                name="s"
                value="<?php echo esc_attr(get_search_query()); ?>"
                placeholder="<?php esc_attr_e('جست‌وجوی محصولات…','bluprint-theme'); ?>"
            >
        </form>

        <a
            class="header-cart"
            href="<?php echo esc_url($cart_url); ?>"
            aria-label="<?php echo esc_attr(sprintf('سبد خرید، %d محصول',$count)); ?>"
            aria-controls="bp-cart-drawer"
            aria-expanded="false"
            aria-haspopup="dialog"
        >
            <img
                src="<?php echo esc_url(get_theme_file_uri('assets/icons/cart.svg')); ?>"
                width="24"
                height="24"
                alt=""
                aria-hidden="true"
            >
            <span class="header-cart__label">
                <?php esc_html_e('سبد خرید','bluprint-theme'); ?>
            </span>
            <span
                class="header-cart__count"
                aria-label="<?php echo esc_attr($count.' محصول'); ?>"
                <?php echo $count===0?'hidden':''; ?>
            >
                <?php echo esc_html($count); ?>
            </span>
        </a>

    </div>

    <div
        class="site-navigation-backdrop"
        data-navigation-backdrop
        aria-hidden="true"
    ></div>

</div>
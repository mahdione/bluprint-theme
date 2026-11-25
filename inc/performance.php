<?php
defined('ABSPATH')||exit;

/**
 * Small, low-risk performance improvements.
 *
 * - preload the local variable font
 * - avoid loading WooCommerce and block assets on pages that do not use them
 * - remove block/editor assets from the custom front page
 */
function bluprint_perf_is_woo_context(){
    if(!function_exists('is_shop'))return false;

    return is_shop()
        ||is_product()
        ||is_product_taxonomy()
        ||is_cart()
        ||is_checkout()
        ||(function_exists('is_account_page')&&is_account_page())
        ||(function_exists('is_wc_endpoint_url')&&is_wc_endpoint_url());
}

function bluprint_perf_skip_woo_assets(){
    if(is_front_page()||is_home())return true;
    if(is_post_type_archive('bp_pack')||is_tax('bp_pack_category')||is_singular('bp_pack'))return true;
    if(is_page_template('templates/page-about.php')||is_page_template('templates/page-contact.php'))return true;

    return false;
}

function bluprint_perf_dequeue_assets(){
    if(bluprint_perf_is_woo_context()||!bluprint_perf_skip_woo_assets())return;

    foreach([
        'woocommerce-general',
        'woocommerce-layout',
        'woocommerce-smallscreen',
        'photoswipe',
        'photoswipe-default-skin',
        'wc-blocks-style',
        'wc-blocks-style-rtl',
    ] as $handle){
        wp_dequeue_style($handle);
    }

    foreach([
        'wc-add-to-cart',
        'wc-single-product',
        'flexslider',
        'photoswipe',
        'photoswipe-ui-default',
        'js-cookie',
        'woocommerce',
        'sourcebuster-js',
        'wc-order-attribution',
        'jquery-blockui',
    ] as $handle){
        wp_dequeue_script($handle);
    }

    if(is_front_page()){
        foreach(['wp-block-library','global-styles','classic-theme-styles','wp-emoji-styles'] as $handle){
            wp_dequeue_style($handle);
        }

        wp_dequeue_script('wp-emoji-release');
    }
}
add_action('wp_enqueue_scripts','bluprint_perf_dequeue_assets',100);

function bluprint_perf_preload_font(){
    $font=get_theme_file_uri('assets/fonts/vazirmatn/Vazirmatn-VariableFont_wght.ttf');

    echo '<link rel="preload" href="'.esc_url($font).'" as="font" type="font/ttf" crossorigin="anonymous">'."\n";
}
add_action('wp_head','bluprint_perf_preload_font',1);

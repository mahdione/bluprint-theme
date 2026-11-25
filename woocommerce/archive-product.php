<?php
defined('ABSPATH')||exit;

/*
 * The product archive depends on the WooCommerce theme helpers. Load them
 * here as a fallback when a stale functions.php did not include the folder.
 */
$bluprint_woo_inc=get_theme_file_path('inc/woocommerce');

foreach(['functions.php','setup.php','hooks.php','archive-template.php'] as $bluprint_woo_file){
    $bluprint_woo_path=$bluprint_woo_inc.'/'.$bluprint_woo_file;
    if(file_exists($bluprint_woo_path))require_once $bluprint_woo_path;
}

if(function_exists('bluprint_render_product_archive')){
    bluprint_render_product_archive();
}else{
    get_header('shop');
    echo '<main id="main-content" class="bp-archive container">';
    if(function_exists('woocommerce_content'))woocommerce_content();
    echo '</main>';
    get_footer('shop');
}

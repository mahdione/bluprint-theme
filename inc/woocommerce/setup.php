<?php
defined('ABSPATH')||exit;
add_filter('loop_shop_per_page',function(){return 24;},20);
add_filter('woocommerce_show_page_title','__return_false');
remove_action('woocommerce_before_main_content','woocommerce_breadcrumb',20);
remove_action('woocommerce_before_shop_loop','woocommerce_result_count',20);
remove_action('woocommerce_before_shop_loop','woocommerce_catalog_ordering',30);
remove_action('woocommerce_sidebar','woocommerce_get_sidebar',10);
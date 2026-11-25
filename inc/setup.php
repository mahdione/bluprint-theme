<?php
defined('ABSPATH')||exit;
function bluprint_setup(){
load_theme_textdomain('bluprint-theme',get_theme_file_path('/languages'));
add_theme_support('title-tag');
add_theme_support('post-thumbnails');
add_theme_support('automatic-feed-links');
add_theme_support('responsive-embeds');
add_theme_support('align-wide');
add_theme_support('custom-logo',['height'=>100,'width'=>300,'flex-height'=>true,'flex-width'=>true]);
add_theme_support('html5',['search-form','comment-form','comment-list','gallery','caption','style','script']);
add_theme_support('woocommerce');
add_theme_support('wc-product-gallery-slider');
add_theme_support('wc-product-gallery-lightbox');
register_nav_menus([
'primary'=>'منوی اصلی',
'footer_primary'=>'منوی اول فوتر',
'footer_secondary'=>'منوی دوم فوتر'
]);
}
add_action('after_setup_theme','bluprint_setup');
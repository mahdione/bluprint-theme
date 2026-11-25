<?php
defined('ABSPATH')||exit;

/*
 * Last-resort loader for a partial or stale theme deployment. On a normal
 * install functions.php has already loaded these files, so require_once is a
 * no-op and this block costs nothing.
 */
if(!function_exists('bluprint_get_blog_data')||!function_exists('bluprint_shop_archive_settings')){
    foreach([
        get_theme_file_path('inc/*.php'),
        get_theme_file_path('inc/woocommerce/*.php'),
    ] as $bluprint_pattern){
        $bluprint_files=glob($bluprint_pattern);
        if(!is_array($bluprint_files))continue;

        foreach($bluprint_files as $bluprint_file){
            if(is_readable($bluprint_file))require_once $bluprint_file;
        }
    }
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
    <?php get_template_part('template-parts/header/navigation'); ?>
</header>

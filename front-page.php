<?php
defined('ABSPATH')||exit;

/*
 * If WordPress is running a theme folder left behind by an interrupted update,
 * functions.php may be older than the templates. Load any available top-level
 * include files before rendering the front page instead of fataling.
 */
if(!function_exists('bluprint_get_blog_data')){
    $bluprint_inc_files=glob(get_theme_file_path('inc/*.php'));

    if(is_array($bluprint_inc_files)){
        foreach($bluprint_inc_files as $bluprint_inc_file){
            if(is_readable($bluprint_inc_file))require_once $bluprint_inc_file;
        }
    }
}

get_header();
get_template_part('template-parts/components/sections/hero');
get_template_part('template-parts/components/sections/assurance');
get_template_part('template-parts/components/sections/pack-showcase');
get_template_part('template-parts/components/sections/category-showcase');
get_template_part('template-parts/components/sections/editorial');
get_template_part('template-parts/components/sections/product-showcase');
get_template_part('template-parts/components/banners/banner',null,['banner_id'=>1]);
get_template_part('template-parts/components/sections/blog-section');
get_template_part('template-parts/components/sections/faq-section');
get_footer();

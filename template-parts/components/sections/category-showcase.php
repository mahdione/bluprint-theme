<?php
defined('ABSPATH')||exit;

if(!function_exists('bluprint_get_category_showcase_terms')){
    $bluprint_inc=get_theme_file_path('inc/category-showcase.php');
    if(file_exists($bluprint_inc))require_once $bluprint_inc;
}
if(!function_exists('bluprint_get_category_showcase_terms'))return;

$terms=bluprint_get_category_showcase_terms(); if(!$terms)return; ?> <section class="bp-category-showcase" aria-labelledby="bp-category-showcase-title"> <div class="bp-category-showcase__container"> <h2 class="bp-category-showcase__title" id="bp-category-showcase-title">دسته‌بندی محصولات</h2> <div class="bp-category-showcase__grid"> <?php foreach($terms as $index=>$term): ?> <?php get_template_part('template-parts/components/cards/category-card',null,[ 'term'=>$term, 'number'=>sprintf('%02d',$index+1) ]); ?> <?php endforeach; ?> </div> </div> </section>
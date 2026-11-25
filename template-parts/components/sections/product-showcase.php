<?php
defined('ABSPATH')||exit;

if(!function_exists('bluprint_get_product_showcase_data')){
    $bluprint_inc=get_theme_file_path('inc/product-showcase.php');
    if(file_exists($bluprint_inc))require_once $bluprint_inc;
}
if(!function_exists('bluprint_get_product_showcase_data'))return;

$d=bluprint_get_product_showcase_data(); if(empty($d['enabled']))return; $products=bluprint_get_product_showcase_products(); if(!$products)return; $items=[]; foreach($products as $product){ if(!$product instanceof WC_Product)continue; $items[]=['product'=>$product]; }
if(!$items)return; ?> <section class="bp-product-showcase" aria-labelledby="bp-product-showcase-title"> <div class="bp-product-showcase__container"> <header class="bp-product-showcase__header"> <h2 class="bp-product-showcase__title" id="bp-product-showcase-title"><?php echo esc_html($d['title']); ?></h2> </header> <?php get_template_part('template-parts/components/carousel/carousel',null,[ 'id'=>'bp-product-carousel', 'class'=>'bp-product-showcase__carousel', 'label'=>$d['title'], 'items'=>$items, 'render_item'=>static function($item){ get_template_part('template-parts/components/cards/product-card',null,[ 'product'=>$item['product'] ]); }
]); ?> </div> </section>
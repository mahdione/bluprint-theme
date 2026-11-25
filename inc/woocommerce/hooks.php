<?php
defined('ABSPATH')||exit;

add_action('wp_enqueue_scripts',function(){
if(!function_exists('is_shop')||!(is_shop()||is_product_taxonomy()))return;
$scripts=[
'bluprint-archive'=>'assets/js/components/archive/filters.js',
'bluprint-archive-content'=>'assets/js/components/archive/content-toggle.js'
];
foreach($scripts as $handle=>$file){
if(file_exists(get_theme_file_path($file))){
wp_enqueue_script($handle,get_theme_file_uri($file),[],bluprint_asset_version($file),['in_footer'=>true,'strategy'=>'defer']);
}
}
});

function bluprint_render_product_recommendations($ids,$title,$class){
if(empty($ids)||!is_array($ids))return;
$items=[];
foreach(array_slice(array_unique(array_map('absint',$ids)),0,4) as $id){
$item=wc_get_product($id);
if($item&&$item->is_visible())$items[]=$item;
}
if(!$items)return;
?>
<section class="<?php echo esc_attr('bp-product-recommendations '.$class); ?>">
<h2><?php echo esc_html($title); ?></h2>
<div class="bp-product-recommendations__grid">
<?php foreach($items as $item): ?>
<?php get_template_part('template-parts/components/cards/product-card',null,['product'=>$item]); ?>
<?php endforeach; ?>
</div>
</section>
<?php
}

function bluprint_render_single_product_recommendations(){
if(!is_product())return;
global $product;
if(!$product instanceof WC_Product)return;
$upsells=$product->get_upsell_ids();
$related=wc_get_related_products($product->get_id(),4,$upsells);
bluprint_render_product_recommendations($upsells,'پیشنهادهای ویژه','upsells');
bluprint_render_product_recommendations($related,'محصولات مرتبط','related');
}

add_action('wp',function(){
if(!function_exists('is_product')||!is_product())return;
remove_action('woocommerce_after_single_product_summary','woocommerce_upsell_display',15);
remove_action('woocommerce_after_single_product_summary','woocommerce_output_related_products',20);
add_action('woocommerce_after_single_product_summary','bluprint_render_single_product_recommendations',15);
},20);
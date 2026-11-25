<?php
defined('ABSPATH')||exit;

/**
 * A descriptive Rank Math meta description when the pack has no custom one.
 */
function bluprint_pack_meta_description($description){
    if(!is_singular('bp_pack')||trim((string)$description)!=='')return $description;

    $pack_id=absint(get_queried_object_id());
    if(!$pack_id)return $description;

    $text=trim((string)get_the_excerpt($pack_id));
    if($text===''){
        $text=wp_strip_all_tags(strip_shortcodes(get_post_field('post_content',$pack_id)));
    }

    $text=trim(wp_strip_all_tags($text));

    return $text!==''?wp_trim_words($text,32,'…'):$description;
}
add_filter('rank_math/frontend/description','bluprint_pack_meta_description');

/**
 * Describe the pack contents as an ItemList for search engines.
 *
 * The pack is a collection rather than a purchasable product, so a product
 * schema would be misleading; ItemList reflects what the page actually offers.
 */
function bluprint_pack_item_list_json_ld($data){
    if(!is_array($data)||!is_singular('bp_pack')||!function_exists('bp_commerce_get_pack_products'))return $data;

    $pack_id=absint(get_queried_object_id());
    $products=$pack_id?bp_commerce_get_pack_products($pack_id):[];

    if(!$products)return $data;

    $items=[];

    foreach($products as $index=>$item){
        $product=$item['product'];
        $items[]=[
            '@type'=>'ListItem',
            'position'=>$index+1,
            'url'=>get_permalink($product->get_id()),
            'name'=>$product->get_name(),
        ];
    }

    if(!isset($data['@graph'])||!is_array($data['@graph']))$data['@graph']=[];

    $data['@graph'][]=[
        '@type'=>'ItemList',
        '@id'=>get_permalink($pack_id).'#itemlist',
        'name'=>get_the_title($pack_id),
        'description'=>wp_strip_all_tags(get_the_excerpt($pack_id)),
        'url'=>get_permalink($pack_id),
        'numberOfItems'=>count($items),
        'itemListOrder'=>'https://schema.org/ItemListOrderAscending',
        'itemListElement'=>$items,
    ];

    return $data;
}
add_filter('rank_math/json_ld','bluprint_pack_item_list_json_ld',99);

/**
 * Archive query: keep the pack grid to nine items per page.
 */
function bluprint_pack_archive_query($query){
    if(is_admin()||!$query->is_main_query())return;
    if(!$query->is_post_type_archive('bp_pack')&&!$query->is_tax('bp_pack_category'))return;

    $query->set('posts_per_page',9);
    $query->set('orderby','date');
    $query->set('order','DESC');
}
add_action('pre_get_posts','bluprint_pack_archive_query');

/**
 * Give pack archives a description when Rank Math has none.
 */
function bluprint_pack_archive_meta_description($description){
    if(!is_post_type_archive('bp_pack')&&!is_tax('bp_pack_category'))return $description;
    if(trim((string)$description)!=='')return $description;

    if(is_tax('bp_pack_category')){
        $term=get_queried_object();
        $text=$term instanceof WP_Term?term_description($term):'';
        if(trim(wp_strip_all_tags($text))===''&&$term instanceof WP_Term){
            $text=sprintf('پک‌های دسته %s؛ مجموعه‌ای از محصولات هماهنگ برای شروع سریع‌تر.',$term->name);
        }
    }else{
        $text='پک‌های آماده بلوپرینت؛ مجموعه‌هایی از محصولات هماهنگ که با یک کلیک به سبد خرید اضافه می‌شوند.';
    }

    $text=trim(wp_strip_all_tags($text));

    return $text!==''?wp_trim_words($text,32,'…'):$description;
}
add_filter('rank_math/frontend/description','bluprint_pack_archive_meta_description');

/**
 * Describe the visible pack collection as an ItemList on archive pages.
 */
function bluprint_pack_archive_item_list_json_ld($data){
    if(!is_array($data))return $data;
    if(!is_post_type_archive('bp_pack')&&!is_tax('bp_pack_category'))return $data;

    global $wp_query;

    if(empty($wp_query->posts))return $data;

    $items=[];

    foreach($wp_query->posts as $index=>$post){
        if(!$post instanceof WP_Post)continue;

        $items[]=[
            '@type'=>'ListItem',
            'position'=>$index+1,
            'url'=>get_permalink($post),
            'name'=>get_the_title($post),
        ];
    }

    if(!$items)return $data;

    if(!isset($data['@graph'])||!is_array($data['@graph']))$data['@graph']=[];

    $data['@graph'][]=[
        '@type'=>'ItemList',
        '@id'=>get_pagenum_link(max(1,(int)get_query_var('paged'))).'#pack-list',
        'name'=>is_tax('bp_pack_category')?single_term_title('',false):post_type_archive_title('',false),
        'numberOfItems'=>count($items),
        'itemListOrder'=>'https://schema.org/ItemListOrderAscending',
        'itemListElement'=>$items,
    ];

    return $data;
}
add_filter('rank_math/json_ld','bluprint_pack_archive_item_list_json_ld',99);
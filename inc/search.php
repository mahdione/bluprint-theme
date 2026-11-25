<?php
defined('ABSPATH')||exit;

/**
 * The result types the storefront search understands.
 * Order is intentional: products first, then the curated packs, then journal posts.
 */
function bluprint_search_types(){
    return [
        'product'=>[
            'label'=>'محصولات',
            'singular'=>'محصول',
            'eyebrow'=>'PRODUCTS',
        ],
        'bp_pack'=>[
            'label'=>'پک‌ها',
            'singular'=>'پک',
            'eyebrow'=>'PACKS',
        ],
        'post'=>[
            'label'=>'مقالات',
            'singular'=>'مقاله',
            'eyebrow'=>'JOURNAL',
        ],
    ];
}

function bluprint_search_type(){
    if(!isset($_GET['bp_type'])||!is_string($_GET['bp_type']))return '';
    $type=sanitize_key(wp_unslash($_GET['bp_type']));
    return array_key_exists($type,bluprint_search_types())?$type:'';
}

function bluprint_search_url($type=''){
    $args=['s'=>get_search_query()];
    if($type!=='')$args['bp_type']=$type;
    return add_query_arg($args,home_url('/'));
}

/**
 * Restrict the main search query to the storefront result types and allow
 * filtering by a single type through ?bp_type=product|bp_pack|post.
 */
function bluprint_search_query($query){
    if(is_admin()||!$query instanceof WP_Query||!$query->is_main_query()||!$query->is_search())return;

    $types=array_keys(bluprint_search_types());
    $types=array_values(array_filter($types,'post_type_exists'));
    if(!$types)return;

    $active=bluprint_search_type();
    $query->set('post_type',$active?:$types);
    $query->set('posts_per_page',24);
    $query->set('orderby','relevance');
    $query->set('ignore_sticky_posts',true);
}
add_action('pre_get_posts','bluprint_search_query');

/**
 * Total result counts per type for the current search term. The queries are
 * intentionally tiny (one row each) so the tab counters stay accurate even
 * when the visible result page is paginated.
 */
function bluprint_search_counts(){
    $counts=['all'=>0,'product'=>0,'bp_pack'=>0,'post'=>0];
    $term=trim((string)get_search_query());
    if($term==='')return $counts;

    foreach(['product','bp_pack','post'] as $post_type){
        if(!post_type_exists($post_type))continue;

        $query=new WP_Query([
            's'=>$term,
            'post_type'=>$post_type,
            'post_status'=>'publish',
            'posts_per_page'=>1,
            'fields'=>'ids',
            'no_found_rows'=>false,
            'ignore_sticky_posts'=>true,
        ]);

        $counts[$post_type]=(int)$query->found_posts;
        $counts['all']+=$counts[$post_type];
    }

    return $counts;
}

/**
 * Build the argument bag expected by template-parts/components/cards/pack-card.php
 * from a bp_pack post.
 */
function bluprint_search_pack_args($post){
    $post=get_post($post);
    if(!$post||$post->post_type!=='bp_pack')return [];

    $pack_items=get_post_meta($post->ID,'_bp_pack_items',true);
    $pack_items=is_array($pack_items)?$pack_items:[];
    $pack_items=array_values(array_filter($pack_items,static fn($item)=>is_array($item)&&absint($item['product_id']??0)>0));

    $terms=wp_get_post_terms($post->ID,'bp_pack_category');
    $badge=($terms&&!is_wp_error($terms))?$terms[0]->name:'';

    $description=get_the_excerpt($post);
    if(!$description)$description=wp_strip_all_tags($post->post_content);
    $description=wp_trim_words(wp_strip_all_tags($description),26,'…');

    return [
        'image_id'=>get_post_thumbnail_id($post->ID),
        'badge'=>$badge,
        'title'=>get_the_title($post),
        'description'=>$description,
        'meta'=>sprintf('%s محصول',number_format_i18n(count($pack_items))),
        'button_text'=>'مشاهده پک',
        'button_url'=>get_permalink($post),
        'pack_id'=>$post->ID,
        'pack_items'=>$pack_items,
    ];
}

/**
 * Persian document titles for the search and 404 templates.
 */
function bluprint_search_document_title($parts){
    if(is_404()){
        $parts['title']='صفحه پیدا نشد';
    }elseif(is_search()&&trim((string)get_search_query())===''){
        $parts['title']='جست‌وجو';
    }

    return $parts;
}
add_filter('document_title_parts','bluprint_search_document_title');

/**
 * Rank Math builds the front-end title itself, so mirror the same labels there.
 */
function bluprint_search_rank_math_title($title){
    if(is_404()){
        return 'صفحه پیدا نشد - '.get_bloginfo('name');
    }

    if(is_search()&&trim((string)get_search_query())===''){
        return 'جست‌وجو - '.get_bloginfo('name');
    }

    return $title;
}
add_filter('rank_math/frontend/title','bluprint_search_rank_math_title');

/**
 * A small set of packs used by the empty search state.
 */
function bluprint_search_suggested_packs($limit=3){
    if(!post_type_exists('bp_pack'))return [];

    $posts=get_posts([
        'post_type'=>'bp_pack',
        'post_status'=>'publish',
        'posts_per_page'=>max(1,absint($limit)),
        'orderby'=>'date',
        'order'=>'DESC',
        'no_found_rows'=>true,
        'ignore_sticky_posts'=>true,
    ]);

    return array_values(array_filter(array_map('bluprint_search_pack_args',$posts)));
}